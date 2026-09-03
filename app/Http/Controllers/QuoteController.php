<?php

namespace App\Http\Controllers;

use App\Mail\StageMessage;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Support\MailTemplates;
use Carbon\CarbonInterface;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * نموذج طلب المتجر الإلكتروني (عرض السعر).
 *
 * يعمل بوضعين:
 *  - /quote          → النموذج العام (يسأل عن الاسم والبريد واسم المتجر).
 *  - /quote/{slug}   → رابط مخصّص لعميل بعينه: بياناته معروفة مسبقاً في INVITES،
 *                      ويُقبل منه طلب واحد فقط؛ بعده يعرض الرابط حالة الطلب.
 */
class QuoteController extends Controller
{
    /** مهلة تجهيز عرض السعر بأيام العمل — تُعرض في شاشة الشكر وصفحة الحالة والمستند. */
    public const SLA_BUSINESS_DAYS = 3;

    /** أيام العطلة الأسبوعية (الجمعة والسبت) — تُستثنى من حساب المهلة. */
    public const WEEKEND_DAYS = [CarbonInterface::FRIDAY, CarbonInterface::SATURDAY];

    /** نسبة ضريبة القيمة المضافة الافتراضية في عرض السعر. */
    public const DEFAULT_VAT_PERCENT = 14;

    /**
     * مراحل الطلب بالترتيب. العدّاد يعمل في مرحلتين فقط:
     * تجهيز عرض السعر (3 أيام عمل من الاجتماع) وتنفيذ المتجر (حتى موعد التسليم).
     */
    public const STAGES = [
        'awaiting_meeting' => [
            'label' => 'تحديد موعد الاجتماع',
            'icon' => 'calendar',
            'client' => 'بانتظار تحديد موعد اجتماع تعريفي مع فريق وريد.',
            'countdown' => false,
        ],
        'meeting_scheduled' => [
            'label' => 'الاجتماع التعريفي',
            'icon' => 'consult',
            'client' => 'الاجتماع محدّد — نلتقي لمناقشة تفاصيل المتجر.',
            'countdown' => false,
        ],
        'quote_due' => [
            'label' => 'تجهيز عرض السعر',
            'icon' => 'document',
            'client' => 'بعد الاجتماع يجهّز الفريق عرض السعر خلال 3 أيام عمل.',
            'countdown' => true,
        ],
        'awaiting_approval' => [
            'label' => 'اعتماد العرض',
            'icon' => 'verified',
            'client' => 'عرض السعر جاهز — بانتظار اعتمادك للبدء في التنفيذ.',
            'countdown' => false,
        ],
        'in_progress' => [
            'label' => 'تنفيذ المتجر',
            'icon' => 'bolt',
            'client' => 'بدأ تنفيذ المتجر — العدّاد يوضّح المتبقي حتى موعد التسليم.',
            'countdown' => true,
        ],
        'delivered' => [
            'label' => 'التسليم',
            'icon' => 'check',
            'client' => 'تم تسليم المتجر بنجاح. سعدنا بالعمل معك.',
            'countdown' => false,
        ],
    ];

    /**
     * حالة مسار الطلب: المرحلة الحالية وتواريخها والعدّاد الفعّال إن وُجد.
     * تُخزَّن في payload['_flow'] فلا تحتاج جدولاً ولا هجرة على الخادم.
     */
    public static function flowOf(ServiceRequest $sr): array
    {
        $saved = ((array) $sr->payload)['_flow'] ?? [];
        $stage = $saved['stage'] ?? 'awaiting_meeting';

        if (! array_key_exists($stage, self::STAGES)) {
            $stage = 'awaiting_meeting';
        }

        $at = function (string $key) use ($saved): ?Carbon {
            return filled($saved[$key] ?? null) ? Carbon::parse($saved[$key]) : null;
        };

        $meetingAt = $at('meeting_at');
        $meetingDoneAt = $at('meeting_done_at');
        $dueAt = $at('due_at');

        // العدّاد يعمل في مرحلتين فقط حسب مسار العمل المعتمد
        [$countFrom, $countTo] = match ($stage) {
            'quote_due' => [$meetingDoneAt, $meetingDoneAt ? self::deadlineFor($meetingDoneAt) : null],
            'in_progress' => [$at('started_at'), $dueAt],
            default => [null, null],
        };

        return [
            'stage' => $stage,
            'index' => array_search($stage, array_keys(self::STAGES), true),
            'meeting_at' => $meetingAt,
            'meeting_done_at' => $meetingDoneAt,
            'approved_at' => $at('approved_at'),
            'started_at' => $at('started_at'),
            'due_at' => $dueAt,
            'delivered_at' => $at('delivered_at'),
            'note' => (string) ($saved['note'] ?? ''),
            'count_from' => $countFrom,
            'count_to' => $countTo,
            'counting' => $countFrom !== null && $countTo !== null,
        ];
    }

    /** موعد تسليم عرض السعر: 3 أيام عمل من وقت الاستلام مع تخطّي العطلة. */
    public static function deadlineFor(CarbonInterface $start): Carbon
    {
        $date = Carbon::instance($start->toDateTime());

        for ($added = 0; $added < self::SLA_BUSINESS_DAYS;) {
            $date->addDay();

            if (! in_array($date->dayOfWeek, self::WEEKEND_DAYS, true)) {
                $added++;
            }
        }

        return $date;
    }

    /**
     * الروابط المخصّصة: أضف عميلاً هنا ثم شارك معه wareed.vip/quote/{slug}.
     *
     * gender: 'f' أو 'm' — تضبط صيغ المخاطبة.
     * phone / email / store_name: بيانات معروفة مسبقاً لا يُسأل عنها في النموذج،
     * وتظهر في مستند الطلب وفي لوحة المتابعة. اتركها فارغة إن لم تتوفر.
     */
    /**
     * قرار العميل على عرض السعر — يُسجَّل من صفحة العرض نفسها.
     * «الاعتذار عن المتابعة» هي الصيغة الرسمية المهذّبة لعدم الرغبة في المشروع.
     */
    public const DECISIONS = [
        'approved' => [
            'label' => 'اعتماد العرض',
            'lead' => 'أوافق على العرض ونبدأ التنفيذ',
            'note_label' => 'ملاحظة تودّ إضافتها (اختياري)',
            'done' => 'شكراً لثقتك — اعتُمد العرض ووصلنا قرارك. سيتواصل معك فريق وريد لبدء التنفيذ.',
        ],
        'discount' => [
            'label' => 'طلب تخفيض',
            'lead' => 'العرض مناسب لكن أرغب في مراجعة السعر',
            'note_label' => 'الميزانية المناسبة لك أو ملاحظاتك على البنود',
            'done' => 'وصلنا طلبك — سيراجع فريق وريد العرض ويوافيك بعرض محدَّث في أقرب وقت.',
        ],
        'declined' => [
            'label' => 'الاعتذار عن المتابعة',
            'lead' => 'أعتذر عن المضي في المشروع حالياً',
            'note_label' => 'سبب الاعتذار إن أحببت مشاركته (اختياري)',
            'done' => 'شكراً لوقتك ولصراحتك — سجّلنا اعتذارك، وباب وريد يبقى مفتوحاً لك في أي وقت.',
        ],
    ];

    public const INVITES = [
        'hajar-salama' => [
            'name' => 'أ. هاجر سلامة',
            'short_name' => 'أ. هاجر',
            'gender' => 'f',
            'phone' => '00201016031031',
            'email' => 'hagersalma89@gmail.com',
            'store_name' => 'متجر حواديت',
        ],
    ];

    public function show(?string $invite = null)
    {
        $client = $this->resolveInvite($invite);

        // الرابط المخصّص يقبل طلباً واحداً: بعده يعرض حالة الطلب بدل النموذج
        if ($client && $existing = $this->existingRequest($invite)) {
            return $this->statusView($existing, $invite, $client);
        }

        return view('quote.wizard', [
            'inviteSlug' => $invite,
            'client' => $client,
            'questions' => $this->questions(personalized: $client !== null),
            'whatsapp' => $this->whatsapp(),
            'slaDays' => self::SLA_BUSINESS_DAYS,
        ]);
    }

    public function submit(Request $request, ?string $invite = null): JsonResponse
    {
        $client = $this->resolveInvite($invite);
        $personalized = $client !== null;

        // قفل الطلب الواحد للرابط المخصّص — يحمي من الإرسال المكرر أو من تبويب قديم
        if ($personalized && $existing = $this->existingRequest($invite)) {
            return response()->json([
                'ok' => false,
                'duplicate' => true,
                'reference' => $existing->reference,
                'message' => 'سبق استلام طلبك، وهو الآن قيد التجهيز.',
            ], 409);
        }

        // مصيدة سبام: حقل مخفي لا يملؤه البشر — نتظاهر بالنجاح دون حفظ
        if ($request->filled('website')) {
            return response()->json(['ok' => true]);
        }

        $options = fn (string $key): array => array_column(
            collect($this->questions($personalized))->firstWhere('key', $key)['options'],
            'label'
        );

        // تحقق يدوي يعيد 422 JSON دائماً: معالج الأخطاء العام يحصر عرض JSON في مسارات api/*
        // بينما هذا النموذج يرسل عبر fetch ويعتمد على استجابة JSON صريحة.
        $validator = Validator::make($request->all(), array_filter([
            'name' => $personalized ? null : ['required', 'string', 'max:120'],
            'email' => $personalized ? null : ['required', 'email:rfc', 'max:190'],
            'store_name' => $personalized ? null : ['nullable', 'string', 'max:190'],
            'store_status' => ['required', Rule::in($options('store_status'))],
            'store_field' => ['required', Rule::in($options('store_field'))],
            'store_field_other' => ['nullable', 'string', 'max:300', 'required_if:store_field,أخرى'],
            'products_count' => ['required', Rule::in($options('products_count'))],
            'branding' => ['required', Rule::in($options('branding'))],
            'features' => ['required', 'array', 'min:1'],
            'features.*' => ['string', Rule::in($options('features'))],
            'budget' => ['required', Rule::in($options('budget'))],
            'launch_time' => ['required', Rule::in($options('launch_time'))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]), [
            'required' => 'هذا الحقل مطلوب.',
            'email' => 'يرجى إدخال بريد إلكتروني صحيح.',
            'in' => 'يرجى الاختيار من الخيارات المتاحة.',
            'max' => 'النص المدخل أطول من المسموح.',
            'features.required' => 'يرجى اختيار خدمة واحدة على الأقل.',
            'features.min' => 'يرجى اختيار خدمة واحدة على الأقل.',
            'store_field_other.required_if' => 'يرجى توضيح مجال المتجر.',
        ]);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $data = $validator->validated();

        $storeField = $data['store_field'] === 'أخرى' && filled($data['store_field_other'] ?? null)
            ? 'أخرى: '.$data['store_field_other']
            : $data['store_field'];

        $serviceRequest = ServiceRequest::create([
            'service_id' => Service::query()->where('key', 'ecommerce')->value('id'),
            'service_type' => 'ecommerce',
            'name' => $personalized ? $client['name'] : trim($data['name']),
            'phone' => $personalized ? ($client['phone'] ?: '—') : '—',
            'email' => $personalized ? ($client['email'] ?: null) : $data['email'],
            'company' => $personalized ? ($client['store_name'] ?: null) : ($data['store_name'] ?? null),
            'budget' => $data['budget'],
            'message' => $data['notes'] ?? null,
            // مفاتيح عربية لتظهر مقروءة في بريد الإشعار ولوحة التحكم مباشرة
            'payload' => [
                'وضع المتجر' => $data['store_status'],
                'مجال المتجر' => $storeField,
                'عدد المنتجات المتوقع' => $data['products_count'],
                'الهوية البصرية' => $data['branding'],
                'الخدمات المطلوبة' => array_values($data['features']),
                'موعد الإطلاق' => $data['launch_time'],
            ],
            'status' => 'new',
            'source' => $personalized ? 'quote_link:'.$invite : 'quote_form',
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return response()->json([
            'ok' => true,
            'reference' => $serviceRequest->reference,
            'documentUrl' => $this->documentUrl($serviceRequest, $invite),
            'statusUrl' => $personalized ? route('quote.invite', $invite) : null,
            'deadline' => self::deadlineFor($serviceRequest->created_at)->toIso8601String(),
        ]);
    }

    /**
     * مستند الطلب الرسمي (A4) عبر الرابط المخصّص — نسخة العميل للطباعة والحفظ PDF.
     */
    public function document(string $invite)
    {
        $client = $this->resolveInvite($invite);
        $serviceRequest = $this->existingRequest($invite) ?? abort(404);

        return $this->documentView($serviceRequest, $client);
    }

    /**
     * المستند نفسه للنموذج العام — عبر رابط موقّع يُسلَّم للعميل بعد الإرسال.
     */
    public function documentSigned(ServiceRequest $serviceRequest)
    {
        return $this->documentView($serviceRequest, null);
    }

    private function documentView(ServiceRequest $sr, ?array $client)
    {
        return view('quote.document', [
            'sr' => $sr,
            'client' => $client,
            'contact' => $this->contactOf($sr, $client),
            'rows' => $this->documentRows($sr),
            'qr' => $this->qrSvg($sr->reference),
            'slaDays' => self::SLA_BUSINESS_DAYS,
            'deadline' => self::deadlineFor($sr->created_at),
        ]);
    }

    /**
     * بيانات تواصل العميل للعرض: من السجل أولاً، ثم من بيانات الرابط المخصّص.
     * يضمن اكتمال المستند حتى للطلبات المسجّلة قبل إضافة بيانات العميل.
     */
    private function contactOf(ServiceRequest $sr, ?array $client): array
    {
        $pick = function (?string $stored, ?string $fallback): ?string {
            $stored = trim((string) $stored);

            return $stored !== '' && $stored !== '—' ? $stored : ($fallback ?: null);
        };

        return [
            'name' => $client['name'] ?? $sr->name,
            'phone' => $pick($sr->phone, $client['phone'] ?? null),
            'email' => $pick($sr->email, $client['email'] ?? null),
            'store' => $pick($sr->company, $client['store_name'] ?? null),
        ];
    }

    /** صفوف المستند: كل بيانات الطلب مرتّبة للعرض الرسمي. */
    private function documentRows(ServiceRequest $sr): array
    {
        // المفاتيح التي تبدأ بشرطة سفلية بيانات داخلية (مثل عرض السعر) ولا تُعرض كإجابات
        $payload = array_filter(
            (array) $sr->payload,
            fn ($key) => ! str_starts_with((string) $key, '_'),
            ARRAY_FILTER_USE_KEY
        );
        $features = $payload['الخدمات المطلوبة'] ?? [];

        return array_filter([
            ['وضع المتجر', $payload['وضع المتجر'] ?? null],
            ['مجال المتجر', $payload['مجال المتجر'] ?? null],
            ['عدد المنتجات المتوقع', $payload['عدد المنتجات المتوقع'] ?? null],
            ['الهوية البصرية', $payload['الهوية البصرية'] ?? null],
            ['الخدمات المطلوبة', is_array($features) ? implode(' • ', $features) : $features],
            ['الميزانية التقديرية', $sr->budget],
            ['موعد الإطلاق المستهدف', $payload['موعد الإطلاق'] ?? null],
            ['ملاحظات العميل', $sr->message],
        ], fn ($row) => filled($row[1]));
    }

    /** رمز QR للرقم المرجعي — SVG مضمّن دون أي اعتماد خارجي. */
    private function qrSvg(string $reference): string
    {
        $options = new QROptions([
            'outputType' => QROutputInterface::MARKUP_SVG,
            'eccLevel' => EccLevel::M,
            'version' => 3,
            'addQuietzone' => true,
            'quietzoneSize' => 1,
            'outputBase64' => false,
            'svgUseFillAttributes' => false,
            // الوحدات الفاتحة لا تُرسم إطلاقاً، وإلا ظهرت سوداء مثل الداكنة فيفسد الرمز
            'drawLightModules' => false,
            'connectPaths' => true,
            'cssClass' => 'qr',
        ]);

        // نزيل ترويسة XML لأن الـ SVG يُدرج داخل صفحة HTML
        return preg_replace('/<\?xml.*?\?>\s*/s', '', (new QRCode($options))->render($reference));
    }

    private function documentUrl(ServiceRequest $sr, ?string $invite): string
    {
        return $invite !== null
            ? route('quote.document', $invite)
            : URL::signedRoute('quote.document.signed', ['serviceRequest' => $sr->id]);
    }

    /** آخر طلب وارد من رابط مخصّص — أساس قفل الطلب الواحد وصفحة الحالة. */
    private function existingRequest(?string $invite): ?ServiceRequest
    {
        if ($invite === null) {
            return null;
        }

        return ServiceRequest::query()
            ->where('source', 'quote_link:'.$invite)
            ->latest('id')
            ->first();
    }

    private function statusView(ServiceRequest $sr, string $invite, array $client)
    {
        return response()->view('quote.status', [
            'sr' => $sr,
            'client' => $client,
            'inviteSlug' => $invite,
            'rows' => $this->documentRows($sr),
            'whatsapp' => $this->whatsapp(),
            'slaDays' => self::SLA_BUSINESS_DAYS,
            'quote' => self::quoteOf($sr),
            'flow' => self::flowOf($sr),
            'stages' => self::STAGES,
        ]);
    }

    /**
     * عرض السعر المُصدَر للطلب مع مجاميعه المحسوبة، أو null إن لم يُصدر بعد.
     * يُخزَّن داخل payload['_quote'] فلا يحتاج جدولاً ولا هجرة على الخادم.
     */
    /** تحويل تاريخ مخزّن نصاً إلى Carbon، وتجاهل الفارغ أو غير الصالح بلا استثناء. */
    private static function parseDate(mixed $value): ?Carbon
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : rescue(fn () => Carbon::parse($value), null, false);
    }

    public static function quoteOf(ServiceRequest $sr): ?array
    {
        $q = ((array) $sr->payload)['_quote'] ?? null;

        if (! is_array($q) || empty($q['items'])) {
            return null;
        }

        $items = array_map(function (array $i): array {
            $free = (bool) ($i['free'] ?? false);
            $qty = max(1, (int) ($i['qty'] ?? 1));
            $price = $free ? 0.0 : max(0, (float) ($i['price'] ?? 0));

            return [
                'phase' => trim((string) ($i['phase'] ?? '')),
                'name' => (string) ($i['name'] ?? ''),
                'desc' => (string) ($i['desc'] ?? ''),
                // ملاحظة البند: نوع الاشتراك أو أي توضيح يظهر بجانب الاسم
                'note' => trim((string) ($i['note'] ?? '')),
                'qty' => $qty,
                // وحدة القياس تظهر بجانب الكمية: شهر، سنة، صفحة، منتج…
                'unit' => trim((string) ($i['unit'] ?? '')),
                'price' => $price,
                // بند مجاني: يُعرض «مجاناً» ولا يضيف شيئاً للإجمالي
                'free' => $free || $price <= 0,
                'total' => $qty * $price,
            ];
        }, array_values($q['items']));

        // خدمات إضافية اختيارية: تُعرض للاطلاع ولا تدخل في أي إجمالي
        $extras = array_values(array_filter(
            array_map(function (array $e): array {
                $qty = max(1, (int) ($e['qty'] ?? 1));
                $price = max(0, (float) ($e['price'] ?? 0));

                return [
                    'name' => trim((string) ($e['name'] ?? '')),
                    'desc' => trim((string) ($e['desc'] ?? '')),
                    'note' => trim((string) ($e['note'] ?? '')),
                    'qty' => $qty,
                    'unit' => trim((string) ($e['unit'] ?? '')),
                    'price' => $price,
                    'total' => $qty * $price,
                ];
            }, (array) ($q['extras'] ?? [])),
            fn ($e) => $e['name'] !== '',
        ));

        // تجميع البنود في مراحل مسمّاة مع حفظ ترتيبها كما أدخلها المستخدم
        $phases = [];
        foreach ($items as $item) {
            $key = $item['phase'];
            $phases[$key] ??= ['name' => $key, 'items' => [], 'total' => 0.0];
            $phases[$key]['items'][] = $item;
            $phases[$key]['total'] += $item['total'];
        }
        $phases = array_values($phases);
        $hasPhases = count($phases) > 1 || ($phases[0]['name'] ?? '') !== '';

        $subtotal = array_sum(array_column($items, 'total'));

        // الخصم نسبة من الإجمالي تُحسب قيمتها تلقائياً.
        // العروض الصادرة قبل هذا التغيير تحمل قيمة مباشرة، فنشتقّ نسبتها كي تُعرض بالشكل نفسه.
        if (isset($q['discount_percent'])) {
            $discountPercent = max(0, min(100, (float) $q['discount_percent']));
            $discount = round($subtotal * $discountPercent / 100, 2);
        } else {
            $discount = min(max(0, (float) ($q['discount'] ?? 0)), $subtotal);
            $discountPercent = $subtotal > 0 ? round($discount / $subtotal * 100, 2) : 0.0;
        }

        $afterDiscount = $subtotal - $discount;
        $vatPercent = max(0, (float) ($q['vat_percent'] ?? 0));
        $vat = round($afterDiscount * $vatPercent / 100, 2);

        $total = $afterDiscount + $vat;

        // الدفعات: نسبة من الإجمالي تُحسب قيمتها تلقائياً
        $payments = array_values(array_map(function (array $p) use ($total): array {
            $percent = max(0, min(100, (float) ($p['percent'] ?? 0)));

            return [
                'label' => trim((string) ($p['label'] ?? '')),
                'note' => trim((string) ($p['note'] ?? '')),
                'due' => self::parseDate($p['due'] ?? null),
                'percent' => $percent,
                'amount' => round($total * $percent / 100, 2),
            ];
        }, array_filter(
            (array) ($q['payments'] ?? []),
            fn ($p) => trim((string) ($p['label'] ?? '')) !== '' && (float) ($p['percent'] ?? 0) > 0
        )));

        // الجدول الزمني: كل مرحلة وتاريخا بدئها ونهايتها — يحلّ محل نص «مدة التنفيذ» الحر.
        // العروض المحفوظة بتاريخ واحد (date) تُقرأ نهايةً للمرحلة.
        $schedule = array_values(array_filter(
            array_map(fn ($s) => [
                'phase' => trim((string) ($s['phase'] ?? '')),
                'start' => self::parseDate($s['start'] ?? null),
                'end' => self::parseDate($s['end'] ?? $s['date'] ?? null),
            ], (array) ($q['schedule'] ?? [])),
            fn ($s) => $s['phase'] !== '' || $s['start'] || $s['end'],
        ));

        $deliveryAt = collect($schedule)->pluck('end')->filter()->max();

        $issuedAt = isset($q['issued_at']) ? Carbon::parse($q['issued_at']) : now();

        return [
            'items' => $items,
            'payments' => $payments,
            'payments_percent' => array_sum(array_column($payments, 'percent')),
            'phases' => $phases,
            'has_phases' => $hasPhases,
            'extras' => $extras,
            'extras_total' => array_sum(array_column($extras, 'total')),
            'schedule' => $schedule,
            'delivery_at' => $deliveryAt,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'discount_percent' => $discountPercent,
            'vat_percent' => $vatPercent,
            'vat' => $vat,
            'total' => $total,
            'currency' => (string) ($q['currency'] ?? 'ج.م'),
            'timeline' => (string) ($q['timeline'] ?? ''),
            'notes' => (string) ($q['notes'] ?? ''),
            'valid_days' => max(1, (int) ($q['valid_days'] ?? 30)),
            'issued_at' => $issuedAt,
            'valid_until' => $issuedAt->copy()->addDays(max(1, (int) ($q['valid_days'] ?? 30))),
        ];
    }

    /**
     * عرض السعر الرسمي (A4) عبر الرابط المخصّص.
     */
    public function proposal(string $invite)
    {
        $client = $this->resolveInvite($invite);
        $sr = $this->existingRequest($invite) ?? abort(404);

        return $this->proposalView($sr, $client);
    }

    /** عرض السعر نفسه عبر رابط موقّع للنموذج العام. */
    public function proposalSigned(ServiceRequest $serviceRequest)
    {
        return $this->proposalView($serviceRequest, null);
    }

    private function proposalView(ServiceRequest $sr, ?array $client)
    {
        $quote = self::quoteOf($sr) ?? abort(404);

        return view('quote.proposal', [
            'sr' => $sr,
            'client' => $client,
            'contact' => $this->contactOf($sr, $client),
            'quote' => $quote,
            'bank' => self::bankDetails(),
            'qr' => $this->qrSvg($sr->reference),
            'decision' => self::decisionOf($sr),
            'decisionUrl' => self::decisionUrl($sr),
        ]);
    }

    /** تسجيل قرار العميل عبر رابطه المخصّص. */
    public function decision(Request $request, string $invite)
    {
        $this->resolveInvite($invite);
        $sr = $this->existingRequest($invite) ?? abort(404);

        return $this->storeDecision($request, $sr);
    }

    /** تسجيل القرار عبر رابط موقّع للنموذج العام. */
    public function decisionSigned(Request $request, ServiceRequest $serviceRequest)
    {
        return $this->storeDecision($request, $serviceRequest);
    }

    /**
     * حفظ القرار في الطلب وإشعار فريق وريد به.
     * العميل يستطيع تغيير قراره (طلب تخفيض ثم اعتماد مثلاً) فيُستبدل المسجَّل.
     */
    private function storeDecision(Request $request, ServiceRequest $sr)
    {
        self::quoteOf($sr) ?? abort(404);

        $data = $request->validate([
            'choice' => ['required', 'string', Rule::in(array_keys(self::DECISIONS))],
            'note' => ['nullable', 'string', 'max:2000'],
        ], [], ['choice' => 'القرار', 'note' => 'الملاحظة']);

        $payload = (array) $sr->payload;
        $payload['_decision'] = [
            'choice' => $data['choice'],
            'note' => trim((string) ($data['note'] ?? '')),
            'at' => now()->toIso8601String(),
        ];

        // الاعتماد ينقل الطلب تلقائياً إلى مرحلة التنفيذ،
        // وموعد التسليم يُؤخذ من آخر مرحلة في الجدول الزمني إن وُجد.
        if ($data['choice'] === 'approved') {
            $deliveryAt = self::quoteOf($sr)['delivery_at'] ?? null;

            $payload['_flow'] = array_merge($payload['_flow'] ?? [], array_filter([
                'stage' => 'in_progress',
                'approved_at' => now()->toIso8601String(),
                'started_at' => now()->toIso8601String(),
                'due_at' => $deliveryAt?->toIso8601String(),
            ]));
        }

        $sr->update(['payload' => $payload]);

        if ($data['choice'] === 'approved') {
            $sr->update(['status' => 'won']);
        }

        $sr = $sr->fresh();
        $decision = self::decisionOf($sr);

        $this->notifyDecision($sr, $decision);

        // العميل الذي اعتمد العرض يصله بريد بدء التنفيذ بكامل تفاصيله
        if ($data['choice'] === 'approved') {
            MailTemplates::sendStage($sr, 'in_progress', withSummary: true);
        }

        return back()->with('decision_saved', true);
    }

    /** إشعار فريق وريد بقرار العميل — فشل الإرسال لا يُفقد القرار. */
    private function notifyDecision(ServiceRequest $sr, ?array $decision): void
    {
        if (! $decision) {
            return;
        }

        try {
            $quote = self::quoteOf($sr);
            $money = fn ($n, $cur) => number_format((float) $n, ((float) $n == (int) $n) ? 0 : 2).' '.$cur;

            $lines = [
                'سجّل العميل قراره على عرض السعر '.$sr->reference.'.',
                'القرار: '.$decision['label'],
                'العميل: '.$sr->name.($sr->company ? ' — '.$sr->company : '')
                    .($sr->email ? ' · '.$sr->email : '').($sr->phone && $sr->phone !== '—' ? ' · '.$sr->phone : ''),
                'إجمالي العرض: '.($quote ? $money($quote['total'], $quote['currency']) : '—'),
            ];

            if ($decision['note'] !== '') {
                $lines[] = 'ملاحظة العميل: '.$decision['note'];
            }

            if ($decision['choice'] === 'approved') {
                $lines[] = 'نُقل الطلب تلقائياً إلى مرحلة «تنفيذ المتجر» وحالته صارت «مكسوب»، '
                    .'ووصل العميل بريد بدء التنفيذ بكامل التفاصيل.';
            }

            Mail::to((string) setting('contact_email', 'info@wareed.vip'))->send(new StageMessage(
                subjectLine: 'قرار العميل على عرض السعر — '.$decision['label'].' — '.$sr->reference,
                bodyText: implode("\n\n", $lines),
                link: route('filament.admin.pages.quote-requests'),
                linkLabel: 'فتح لوحة المتابعة',
                // كافة تفاصيل العرض والجدول والدفعات مع الإشعار
                summaryOf: $sr,
            ));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** قرار العميل المسجَّل على العرض، أو null إن لم يقرّر بعد. */
    public static function decisionOf(ServiceRequest $sr): ?array
    {
        $d = ((array) $sr->payload)['_decision'] ?? null;
        $choice = is_array($d) ? (string) ($d['choice'] ?? '') : '';

        if (! isset(self::DECISIONS[$choice])) {
            return null;
        }

        return [
            'choice' => $choice,
            'label' => self::DECISIONS[$choice]['label'],
            'done' => self::DECISIONS[$choice]['done'],
            'note' => trim((string) ($d['note'] ?? '')),
            'at' => self::parseDate($d['at'] ?? null),
        ];
    }

    /** رابط تسجيل القرار: مخصّص عبر الدعوة، أو موقّع للنموذج العام. */
    public static function decisionUrl(ServiceRequest $sr): string
    {
        $invite = str_starts_with((string) $sr->source, 'quote_link:')
            ? substr((string) $sr->source, strlen('quote_link:'))
            : null;

        return $invite !== null
            ? route('quote.decision', $invite)
            : URL::signedRoute('quote.decision.signed', ['serviceRequest' => $sr->id]);
    }

    /** بيانات التحويل البنكي من إعدادات الموقع — تظهر في عرض السعر. */
    public static function bankDetails(): array
    {
        $bank = [
            'bank' => (string) setting('bank_name', ''),
            'holder' => (string) setting('bank_account_name', ''),
            'account' => (string) setting('bank_account_number', ''),
            'iban' => (string) setting('bank_iban', ''),
            'swift' => (string) setting('bank_swift', ''),
        ];

        $bank['has'] = (bool) array_filter($bank, fn ($v) => trim((string) $v) !== '');

        return $bank;
    }

    /** الرابط الذي يتابع منه العميل طلبه: صفحته المخصّصة، أو مستند طلبه الموقّع. */
    public static function statusUrl(ServiceRequest $sr): string
    {
        $invite = str_starts_with((string) $sr->source, 'quote_link:')
            ? substr((string) $sr->source, strlen('quote_link:'))
            : null;

        return $invite !== null
            ? route('quote.invite', $invite)
            : URL::signedRoute('quote.document.signed', ['serviceRequest' => $sr->id]);
    }

    /** رابط عرض السعر: مخصّص عبر الدعوة، أو موقّع للنموذج العام. */
    public static function proposalUrl(ServiceRequest $sr): string
    {
        $invite = str_starts_with((string) $sr->source, 'quote_link:')
            ? substr((string) $sr->source, strlen('quote_link:'))
            : null;

        return $invite !== null
            ? route('quote.proposal', $invite)
            : URL::signedRoute('quote.proposal.signed', ['serviceRequest' => $sr->id]);
    }

    /**
     * توحيد رقم الجوال لرابط wa.me: أرقام فقط بلا بادئة 00 أو +.
     * يعيد null إن لم يكن الرقم صالحاً للمراسلة.
     */
    public static function waNumber(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        $digits = ltrim((string) $digits, '0');

        return strlen($digits) >= 9 ? $digits : null;
    }

    private function whatsapp(): string
    {
        return preg_replace('/[^0-9]/', '', (string) setting('contact_whatsapp', '201055789056'));
    }

    private function resolveInvite(?string $invite): ?array
    {
        if ($invite === null) {
            return null;
        }

        return self::INVITES[$invite] ?? abort(404);
    }

    /**
     * مصدر الحقيقة الوحيد للأسئلة: تُبنى منه الشاشات ويُتحقق منه عند الإرسال.
     * generic_only: يُسأل فقط في النموذج العام (بياناته معروفة في الروابط المخصّصة).
     * icon: اسم رمز من نظام الأيقونات الموحّد في resources/views/quote/_icons.blade.php
     */
    private function questions(bool $personalized): array
    {
        $questions = [
            [
                'key' => 'name', 'short' => 'الاسم', 'generic_only' => true, 'type' => 'text',
                'title' => 'ما الاسم الكريم؟',
                'hint' => 'يشرّفنا التعرف عليك قبل كل شيء',
                'placeholder' => 'الاسم الكامل',
                'autocomplete' => 'name',
                'maxlength' => 120,
            ],
            [
                'key' => 'email', 'short' => 'البريد الإلكتروني', 'generic_only' => true, 'type' => 'email',
                'title' => 'ما البريد الإلكتروني؟',
                'hint' => 'لتصلك نسخة من عرض السعر وتأكيد استلام الطلب',
                'placeholder' => 'name@example.com',
                'autocomplete' => 'email',
                'maxlength' => 190,
            ],
            [
                'key' => 'store_name', 'short' => 'اسم المتجر', 'generic_only' => true, 'type' => 'text', 'optional' => true,
                'title' => 'ما اسم المتجر أو المشروع؟',
                'hint' => 'إن لم يكن الاسم جاهزاً بعد فلا مشكلة — تكفي فكرة المشروع، أو تخطّي السؤال',
                'placeholder' => 'مثال: متجر لمسة، مشروع عطور…',
                'maxlength' => 190,
            ],
            [
                'key' => 'store_status', 'short' => 'وضع المتجر', 'type' => 'choice',
                'title' => 'ما وضع المتجر حالياً؟',
                'hint' => 'يساعدنا هذا على تحديد نقطة البداية الصحيحة',
                'options' => [
                    ['icon' => 'idea', 'label' => 'مشروع جديد أبدؤه من الصفر'],
                    ['icon' => 'storefront', 'label' => 'لديّ متجر قائم وأريد تطويره أو إعادة تصميمه'],
                    ['icon' => 'social', 'label' => 'أبيع عبر السوشيال ميديا وأريد متجراً احترافياً'],
                ],
            ],
            [
                'key' => 'store_field', 'short' => 'مجال المتجر', 'type' => 'choice', 'has_other' => true, 'grid' => true,
                'title' => 'ما مجال المتجر؟',
                'hint' => 'الأقرب إلى نشاط المتجر',
                'options' => [
                    ['icon' => 'apparel', 'label' => 'أزياء وموضة'],
                    ['icon' => 'beauty', 'label' => 'عطور ومستحضرات تجميل'],
                    ['icon' => 'food', 'label' => 'أغذية ومشروبات'],
                    ['icon' => 'electronics', 'label' => 'إلكترونيات وتقنية'],
                    ['icon' => 'furniture', 'label' => 'أثاث وديكور'],
                    ['icon' => 'health', 'label' => 'صحة ورياضة'],
                    ['icon' => 'gift', 'label' => 'هدايا وإكسسوارات'],
                    ['icon' => 'grid', 'label' => 'أخرى'],
                ],
            ],
            [
                'key' => 'products_count', 'short' => 'عدد المنتجات', 'type' => 'choice',
                'title' => 'كم عدد المنتجات المتوقع تقريباً؟',
                'hint' => 'تقدير مبدئي يكفي تماماً',
                'options' => [
                    ['icon' => 'box', 'label' => 'أقل من 50 منتجاً'],
                    ['icon' => 'boxes', 'label' => 'من 50 إلى 200 منتج'],
                    ['icon' => 'warehouse', 'label' => 'من 200 إلى 1000 منتج'],
                    ['icon' => 'stack', 'label' => 'أكثر من 1000 منتج'],
                ],
            ],
            [
                'key' => 'branding', 'short' => 'الهوية البصرية', 'type' => 'choice',
                'title' => 'هل توجد هوية بصرية للمتجر؟',
                'hint' => 'الشعار والألوان والخطوط الخاصة بالعلامة التجارية',
                'options' => [
                    ['icon' => 'verified', 'label' => 'نعم، لديّ هوية كاملة'],
                    ['icon' => 'logo', 'label' => 'لديّ شعار فقط'],
                    ['icon' => 'palette', 'label' => 'أحتاج تصميم هوية كاملة من وريد'],
                ],
            ],
            [
                'key' => 'features', 'short' => 'الخدمات المطلوبة', 'type' => 'multi',
                'title' => 'ما الخدمات المطلوبة مع المتجر؟',
                'hint' => 'يمكنك اختيار أكثر من خيار',
                'options' => [
                    ['icon' => 'cart', 'label' => 'تجهيز المتجر ورفع المنتجات'],
                    ['icon' => 'camera', 'label' => 'تصوير المنتجات'],
                    ['icon' => 'payment', 'label' => 'ربط بوابات الدفع الإلكتروني'],
                    ['icon' => 'shipping', 'label' => 'الربط مع شركات الشحن'],
                    ['icon' => 'marketing', 'label' => 'تسويق وإدارة إعلانات'],
                    ['icon' => 'seo', 'label' => 'تحسين الظهور في جوجل (SEO)'],
                    ['icon' => 'mobile', 'label' => 'تطبيق جوال للمتجر'],
                    ['icon' => 'consult', 'label' => 'أحتاج استشارة الفريق أولاً'],
                ],
            ],
            [
                'key' => 'budget', 'short' => 'الميزانية', 'type' => 'choice',
                'title' => 'ما الميزانية التقريبية المخصّصة للمشروع؟',
                'hint' => 'تساعدنا على اقتراح الحل الأنسب — وليست التزاماً نهائياً',
                'options' => [
                    ['icon' => 'coin', 'label' => 'أقل من 25 ألف جنيه'],
                    ['icon' => 'banknote', 'label' => 'من 25 إلى 50 ألف جنيه'],
                    ['icon' => 'wallet', 'label' => 'من 50 إلى 100 ألف جنيه'],
                    ['icon' => 'vault', 'label' => 'أكثر من 100 ألف جنيه'],
                    ['icon' => 'advisor', 'label' => 'أفضّل أن يقترح فريق وريد الأنسب'],
                ],
            ],
            [
                'key' => 'launch_time', 'short' => 'موعد الإطلاق', 'type' => 'choice',
                'title' => 'ما الموعد المستهدف لإطلاق المتجر؟',
                'hint' => '',
                'options' => [
                    ['icon' => 'bolt', 'label' => 'بأسرع وقت ممكن'],
                    ['icon' => 'calendar', 'label' => 'خلال شهر'],
                    ['icon' => 'timeline', 'label' => 'خلال شهر إلى ثلاثة أشهر'],
                    ['icon' => 'compass', 'label' => 'أستكشف الخيارات حالياً'],
                ],
            ],
            [
                'key' => 'notes', 'short' => 'ملاحظات إضافية', 'type' => 'textarea', 'optional' => true,
                'title' => 'هل من تفاصيل إضافية عن المتجر؟',
                'hint' => 'منتجات مميزة، متاجر ملهمة، ميزات خاصة — كل تفصيلة تساعدنا على فهم الرؤية بدقة',
                'placeholder' => 'اكتب هنا… (اختياري)',
            ],
        ];

        return array_values(array_filter(
            $questions,
            fn (array $q) => ! ($q['generic_only'] ?? false) || ! $personalized
        ));
    }
}
