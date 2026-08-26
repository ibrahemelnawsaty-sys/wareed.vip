<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * نموذج عرض السعر التفاعلي للمتاجر الإلكترونية.
 *
 * يعمل بوضعين:
 *  - /quote          → النموذج العام (يسأل عن الاسم والجوال والبريد واسم المتجر).
 *  - /quote/{slug}   → رابط مخصّص لعميل بعينه: يرحّب به بالاسم ويتجاوز أسئلة
 *                      الاسم والبريد واسم المتجر لأنها معروفة مسبقاً.
 */
class QuoteController extends Controller
{
    /**
     * الروابط المخصّصة: أضف عميلاً هنا ثم شارك معه wareed.vip/quote/{slug}.
     * gender: 'f' أو 'm' — تضبط صيغ المخاطبة في الترحيب والشكر.
     */
    public const INVITES = [
        'hajar-salama' => [
            'name' => 'أ. هاجر سلامة',
            'short_name' => 'أ. هاجر',
            'gender' => 'f',
        ],
    ];

    public function show(?string $invite = null)
    {
        $client = $this->resolveInvite($invite);

        return view('quote.wizard', [
            'inviteSlug' => $invite,
            'client' => $client,
            'questions' => $this->questions(personalized: $client !== null),
            'whatsapp' => preg_replace('/[^0-9]/', '', (string) setting('contact_whatsapp', '201055789056')),
        ]);
    }

    public function submit(Request $request, ?string $invite = null): JsonResponse
    {
        $client = $this->resolveInvite($invite);
        $personalized = $client !== null;

        // مصيدة سبام: حقل مخفي لا يملؤه البشر — نتظاهر بالنجاح دون حفظ
        if ($request->filled('website')) {
            return response()->json(['ok' => true]);
        }

        $options = fn (string $key): array => array_column(
            collect($this->questions($personalized))->firstWhere('key', $key)['options'],
            'label'
        );

        $phoneRule = function (string $attribute, mixed $value, \Closure $fail) {
            $digits = strlen(preg_replace('/\D/', '', (string) $value));
            if ($digits < 9 || $digits > 15) {
                $fail('يرجى إدخال رقم جوال صحيح.');
            }
        };

        $data = $request->validate(array_filter([
            'name' => $personalized ? null : ['required', 'string', 'max:120'],
            'email' => $personalized ? null : ['required', 'email:rfc', 'max:190'],
            'store_name' => $personalized ? null : ['nullable', 'string', 'max:190'],
            'phone' => ['required', 'string', 'max:30', $phoneRule],
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

        $storeField = $data['store_field'] === 'أخرى' && filled($data['store_field_other'] ?? null)
            ? 'أخرى: '.$data['store_field_other']
            : $data['store_field'];

        ServiceRequest::create([
            'service_id' => Service::query()->where('key', 'ecommerce')->value('id'),
            'service_type' => 'ecommerce',
            'name' => $personalized ? $client['name'] : trim($data['name']),
            'phone' => trim($data['phone']),
            'email' => $personalized ? null : $data['email'],
            'company' => $personalized ? null : ($data['store_name'] ?? null),
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

        return response()->json(['ok' => true]);
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
     */
    private function questions(bool $personalized): array
    {
        $questions = [
            [
                'key' => 'name', 'generic_only' => true, 'type' => 'text',
                'title' => 'ما الاسم الكريم؟',
                'hint' => 'يشرّفنا التعرف عليك قبل كل شيء',
                'placeholder' => 'الاسم الكامل',
                'autocomplete' => 'name',
                'maxlength' => 120,
            ],
            [
                'key' => 'phone', 'type' => 'tel',
                'title' => 'ما رقم الجوال المناسب للتواصل؟',
                'hint' => 'يفضَّل رقم واتساب — عليه سنتواصل بخصوص عرض السعر',
                'placeholder' => '01xxxxxxxxx',
                'autocomplete' => 'tel',
                'maxlength' => 30,
            ],
            [
                'key' => 'email', 'generic_only' => true, 'type' => 'email',
                'title' => 'ما البريد الإلكتروني؟',
                'hint' => 'لتصلك نسخة من عرض السعر وتأكيد استلام الطلب',
                'placeholder' => 'name@example.com',
                'autocomplete' => 'email',
                'maxlength' => 190,
            ],
            [
                'key' => 'store_name', 'generic_only' => true, 'type' => 'text', 'optional' => true,
                'title' => 'ما اسم المتجر أو المشروع؟',
                'hint' => 'إن لم يكن الاسم جاهزاً بعد فلا مشكلة — تكفي فكرة المشروع، أو تخطّي السؤال',
                'placeholder' => 'مثال: متجر لمسة، مشروع عطور…',
                'maxlength' => 190,
            ],
            [
                'key' => 'store_status', 'type' => 'choice',
                'title' => 'ما وضع المتجر حالياً؟',
                'hint' => 'يساعدنا هذا على تحديد نقطة البداية الصحيحة',
                'options' => [
                    ['icon' => '🌱', 'label' => 'مشروع جديد أبدؤه من الصفر'],
                    ['icon' => '🏪', 'label' => 'لديّ متجر قائم وأريد تطويره أو إعادة تصميمه'],
                    ['icon' => '📱', 'label' => 'أبيع عبر السوشيال ميديا وأريد متجراً احترافياً'],
                ],
            ],
            [
                'key' => 'store_field', 'type' => 'choice', 'has_other' => true, 'grid' => true,
                'title' => 'ما مجال المتجر؟',
                'hint' => 'الأقرب إلى نشاط المتجر',
                'options' => [
                    ['icon' => '👗', 'label' => 'أزياء وموضة'],
                    ['icon' => '💄', 'label' => 'عطور ومستحضرات تجميل'],
                    ['icon' => '🍯', 'label' => 'أغذية ومشروبات'],
                    ['icon' => '📱', 'label' => 'إلكترونيات وتقنية'],
                    ['icon' => '🛋️', 'label' => 'أثاث وديكور'],
                    ['icon' => '💊', 'label' => 'صحة ورياضة'],
                    ['icon' => '🎁', 'label' => 'هدايا وإكسسوارات'],
                    ['icon' => '📦', 'label' => 'أخرى'],
                ],
            ],
            [
                'key' => 'products_count', 'type' => 'choice',
                'title' => 'كم عدد المنتجات المتوقع تقريباً؟',
                'hint' => 'تقدير مبدئي يكفي تماماً',
                'options' => [
                    ['label' => 'أقل من 50 منتجاً'],
                    ['label' => 'من 50 إلى 200 منتج'],
                    ['label' => 'من 200 إلى 1000 منتج'],
                    ['label' => 'أكثر من 1000 منتج'],
                ],
            ],
            [
                'key' => 'branding', 'type' => 'choice',
                'title' => 'هل توجد هوية بصرية للمتجر؟',
                'hint' => 'الشعار والألوان والخطوط الخاصة بالعلامة التجارية',
                'options' => [
                    ['icon' => '✅', 'label' => 'نعم، لديّ هوية كاملة'],
                    ['icon' => '🎨', 'label' => 'لديّ شعار فقط'],
                    ['icon' => '✨', 'label' => 'أحتاج تصميم هوية كاملة من وريد'],
                ],
            ],
            [
                'key' => 'features', 'type' => 'multi',
                'title' => 'ما الخدمات المطلوبة مع المتجر؟',
                'hint' => 'يمكنك اختيار أكثر من خيار',
                'options' => [
                    ['icon' => '🛒', 'label' => 'تجهيز المتجر ورفع المنتجات'],
                    ['icon' => '📸', 'label' => 'تصوير المنتجات'],
                    ['icon' => '💳', 'label' => 'ربط بوابات الدفع الإلكتروني'],
                    ['icon' => '🚚', 'label' => 'الربط مع شركات الشحن'],
                    ['icon' => '📣', 'label' => 'تسويق وإدارة إعلانات'],
                    ['icon' => '🔍', 'label' => 'تحسين الظهور في جوجل (SEO)'],
                    ['icon' => '📲', 'label' => 'تطبيق جوال للمتجر'],
                    ['icon' => '🤝', 'label' => 'أحتاج استشارة الفريق أولاً'],
                ],
            ],
            [
                'key' => 'budget', 'type' => 'choice',
                'title' => 'ما الميزانية التقريبية المخصّصة للمشروع؟',
                'hint' => 'تساعدنا على اقتراح الحل الأنسب — وليست التزاماً نهائياً',
                'options' => [
                    ['label' => 'أقل من 25 ألف جنيه'],
                    ['label' => 'من 25 إلى 50 ألف جنيه'],
                    ['label' => 'من 50 إلى 100 ألف جنيه'],
                    ['label' => 'أكثر من 100 ألف جنيه'],
                    ['label' => 'أفضّل أن يقترح فريق وريد الأنسب'],
                ],
            ],
            [
                'key' => 'launch_time', 'type' => 'choice',
                'title' => 'ما الموعد المستهدف لإطلاق المتجر؟',
                'hint' => '',
                'options' => [
                    ['icon' => '⚡', 'label' => 'بأسرع وقت ممكن'],
                    ['icon' => '🗓️', 'label' => 'خلال شهر'],
                    ['icon' => '🌿', 'label' => 'خلال شهر إلى ثلاثة أشهر'],
                    ['icon' => '🔭', 'label' => 'أستكشف الخيارات حالياً'],
                ],
            ],
            [
                'key' => 'notes', 'type' => 'textarea', 'optional' => true,
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
