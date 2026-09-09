<?php

namespace App\Support;

use App\Http\Controllers\QuoteController;
use App\Mail\StageMessage;
use App\Models\ServiceRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * دورة حياة العقد: من المسوّدة المقترحة تلقائياً بعد اعتماد العميل لعرض السعر،
 * مروراً بجولات المراجعة بين الفريق والعميل، وصولاً إلى الاعتماد النهائي.
 *
 * تُخزَّن كلها في payload['_contract'] فلا تحتاج جدولاً ولا هجرة على الخادم،
 * والطلبات الصادرة قبل هذه الميزة تقرأ null (توافق خلفي كامل).
 *
 * قاعدة القفل بين الجولات: البند الذي اعتمده العميل (decision = approved) يبقى
 * مقفلاً لا يُطلب قراره مرة أخرى، وكل بند غير معتمد يُعاد صفر القرار عند إعادة
 * الإرسال ليقرّر فيه العميل من جديد — سواء عدّله الفريق أو أعاده كما هو.
 */
class Contracts
{
    /** حالة العقد: مسوّدة داخلية، مُرسَل للعميل، عادت بملاحظات، أو معتمد نهائياً. */
    public const STATUSES = [
        'draft' => 'مسوّدة',
        'sent' => 'بانتظار مراجعة العميل',
        'feedback' => 'وردت ملاحظات العميل',
        'approved' => 'معتمد من العميل',
    ];

    /** قرار العميل على البند الواحد. */
    public const DECISIONS = [
        'approved' => [
            'label' => 'أوافق على البند',
            'note_label' => '',
        ],
        'edited' => [
            'label' => 'أطلب تعديله',
            'note_label' => 'اكتب التعديل المطلوب على هذا البند',
        ],
        'deleted' => [
            'label' => 'أطلب حذفه',
            'note_label' => 'اكتب سبب طلب حذف هذا البند',
        ],
    ];

    /** أقصى طول لملاحظة العميل على البند الواحد. */
    public const NOTE_MAX = 2000;

    /** تحويل تاريخ مخزّن نصاً إلى Carbon، وتجاهل الفارغ أو غير الصالح بلا استثناء. */
    private static function parseDate(mixed $value): ?Carbon
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : rescue(fn () => Carbon::parse($value), null, false);
    }

    /**
     * عقد الطلب بحالته المحسوبة، أو null إن لم تُنشأ له مسوّدة بعد.
     *
     * @return array<string, mixed>|null
     */
    public static function of(ServiceRequest $sr): ?array
    {
        $c = ((array) $sr->payload)['_contract'] ?? null;

        if (! is_array($c) || empty($c['clauses'])) {
            return null;
        }

        $status = isset(self::STATUSES[$c['status'] ?? '']) ? $c['status'] : 'draft';
        $stored = array_values(array_filter((array) $c['clauses'], 'is_array'));

        $clauses = array_map(function (array $clause, int $i): array {
            $decision = (string) ($clause['decision'] ?? '');
            $decision = isset(self::DECISIONS[$decision]) ? $decision : null;

            return [
                'id' => (string) ($clause['id'] ?? ''),
                'section' => trim((string) ($clause['section'] ?? '')),
                'title' => trim((string) ($clause['title'] ?? '')),
                'body' => trim((string) ($clause['body'] ?? '')),
                'order' => $i + 1,
                'decision' => $decision,
                'decision_label' => $decision ? self::DECISIONS[$decision]['label'] : null,
                // البند المعتمد مقفل: لا يُعرض للقرار في الجولات التالية
                'locked' => $decision === 'approved',
                'note' => trim((string) ($clause['note'] ?? '')),
                'decided_at' => self::parseDate($clause['decided_at'] ?? null),
            ];
        }, $stored, array_keys($stored));

        $decided = array_values(array_filter($clauses, fn ($cl) => $cl['decision'] !== null));
        $objections = array_values(array_filter($clauses, fn ($cl) => in_array($cl['decision'], ['edited', 'deleted'], true)));
        $approved = array_values(array_filter($clauses, fn ($cl) => $cl['decision'] === 'approved'));

        return [
            'number' => (string) ($c['number'] ?? ''),
            'status' => $status,
            'status_label' => self::STATUSES[$status],
            'round' => max(0, (int) ($c['round'] ?? 0)),
            'clauses' => $clauses,
            'count' => count($clauses),
            'decided_count' => count($decided),
            'approved_count' => count($approved),
            'objections_count' => count($objections),
            'pending_count' => count($clauses) - count($decided),
            // كل البنود اتُّخذ فيها قرار صريح — شرط تفعيل زرّي الأسفل معاً
            'complete' => count($decided) === count($clauses),
            'all_approved' => count($approved) === count($clauses),
            'is_sent' => in_array($status, ['sent', 'feedback', 'approved'], true),
            'is_approved' => $status === 'approved',
            'created_at' => self::parseDate($c['created_at'] ?? null),
            'sent_at' => self::parseDate($c['sent_at'] ?? null),
            'feedback_at' => self::parseDate($c['feedback_at'] ?? null),
            'approved_at' => self::parseDate($c['approved_at'] ?? null),
            'rounds' => array_values(array_map(fn (array $r) => [
                'round' => max(1, (int) ($r['round'] ?? 1)),
                'sent_at' => self::parseDate($r['sent_at'] ?? null),
                'decided_at' => self::parseDate($r['decided_at'] ?? null),
                'outcome' => in_array($r['outcome'] ?? null, ['approved', 'feedback'], true) ? $r['outcome'] : null,
            ], array_filter((array) ($c['rounds'] ?? []), 'is_array'))),
        ];
    }

    /** العقد كما هو مخزَّن (بنية خام للتحرير والحفظ) — مصفوفة فارغة إن لم يوجد. */
    private static function raw(ServiceRequest $sr): array
    {
        $c = ((array) $sr->payload)['_contract'] ?? null;

        return is_array($c) ? $c : [];
    }

    /** حفظ بنية العقد الخام داخل payload دون المساس ببقية مفاتيحه. */
    private static function store(ServiceRequest $sr, array $contract): void
    {
        $payload = (array) $sr->payload;
        $payload['_contract'] = $contract;
        $sr->update(['payload' => $payload]);
    }

    /**
     * إنشاء مسوّدة العقد المقترحة للطلب — عملية آمنة التكرار: إن وُجد عقد بالفعل
     * يُعاد كما هو دون استهلاك رقم عقد جديد ودون المساس ببنوده أو قرارات العميل.
     */
    public static function createDraft(ServiceRequest $sr): array
    {
        if (self::raw($sr)) {
            return self::of($sr) ?? [];
        }

        self::store($sr, [
            'number' => ContractNumber::next(),
            'status' => 'draft',
            'round' => 0,
            'clauses' => array_map(fn (array $clause) => [
                'id' => $clause['id'],
                'section' => $clause['section'],
                'title' => $clause['title'],
                'body' => $clause['body'],
                'decision' => null,
                'note' => '',
                'decided_at' => null,
            ], ContractTemplate::draft($sr)),
            'created_at' => now()->toIso8601String(),
            'sent_at' => null,
            'feedback_at' => null,
            'approved_at' => null,
            'rounds' => [],
        ]);

        return self::of($sr->refresh()) ?? [];
    }

    /**
     * حفظ بنود العقد كما حرّرها الفريق (إضافة، تعديل، حذف، إعادة ترتيب).
     *
     * أي بند تغيّر عنوانه أو نصّه يفقد قرار العميل عليه ويعود بلا قرار، فيُطلب
     * قراره فيه من جديد في الجولة التالية — ولو كان قد اعتمده سابقاً — لأن ما
     * اعتمده لم يعد هو النص المعروض عليه.
     *
     * @param  array<int, array{id?: string, section?: string, title?: string, body?: string}>  $clauses
     */
    public static function saveClauses(ServiceRequest $sr, array $clauses): array
    {
        $contract = self::raw($sr);

        if (! $contract) {
            return [];
        }

        $previous = collect($contract['clauses'] ?? [])
            ->filter(fn ($c) => is_array($c) && filled($c['id'] ?? null))
            ->keyBy('id');

        $saved = [];

        foreach ($clauses as $clause) {
            $title = trim((string) ($clause['title'] ?? ''));
            $body = trim((string) ($clause['body'] ?? ''));

            if ($title === '' && $body === '') {
                continue;
            }

            $id = trim((string) ($clause['id'] ?? ''));
            $old = $id !== '' ? $previous->get($id) : null;
            $unchanged = $old
                && trim((string) ($old['title'] ?? '')) === $title
                && trim((string) ($old['body'] ?? '')) === $body;

            $saved[] = [
                'id' => $id !== '' ? $id : (string) Str::uuid(),
                'section' => trim((string) ($clause['section'] ?? '')),
                'title' => $title,
                'body' => $body,
                // تعديل نصّ البند يُسقط قرار العميل السابق عليه
                'decision' => $unchanged ? ($old['decision'] ?? null) : null,
                'note' => $unchanged ? (string) ($old['note'] ?? '') : '',
                'decided_at' => $unchanged ? ($old['decided_at'] ?? null) : null,
            ];
        }

        $contract['clauses'] = $saved;
        self::store($sr, $contract);

        return self::of($sr->refresh()) ?? [];
    }

    /**
     * إرسال العقد للعميل لمراجعته — أول إرسال أو إعادة إرسال بعد التعديل.
     *
     * يفتح جولة جديدة: البنود المعتمدة سابقاً تبقى مقفلة كما هي، وكل بند غير
     * معتمد يعود بلا قرار ليقرّر فيه العميل من جديد. يعيد true إن وصل البريد.
     */
    public static function send(ServiceRequest $sr): bool
    {
        $contract = self::raw($sr);

        if (! $contract || empty($contract['clauses'])) {
            return false;
        }

        $round = max(0, (int) ($contract['round'] ?? 0)) + 1;

        $contract['clauses'] = array_map(function (array $clause): array {
            if (($clause['decision'] ?? null) === 'approved') {
                return $clause;
            }

            return array_merge($clause, ['decision' => null, 'note' => '', 'decided_at' => null]);
        }, array_values($contract['clauses']));

        $contract['round'] = $round;
        $contract['status'] = 'sent';
        $contract['sent_at'] = now()->toIso8601String();
        $contract['rounds'] = array_merge((array) ($contract['rounds'] ?? []), [[
            'round' => $round,
            'sent_at' => now()->toIso8601String(),
            'decided_at' => null,
            'outcome' => null,
        ]]);

        self::store($sr, $contract);

        // إرسال العقد قبل تسجيل العميل قراره على العرض (بدء يدوي من الفريق) ينقل المسار لمرحلته
        $flow = QuoteController::flowOf($sr->refresh());
        if (! QuoteController::stageReached($flow['stage'], 'awaiting_contract')) {
            $payload = (array) $sr->payload;
            $payload['_flow'] = array_merge((array) ($payload['_flow'] ?? []), ['stage' => 'awaiting_contract']);
            $sr->update(['payload' => $payload]);
        }

        return MailTemplates::sendStage($sr->refresh(), $round > 1 ? 'contract_resent' : 'contract_draft_sent');
    }

    /**
     * تسجيل قرارات العميل على بنود العقد دفعةً واحدة.
     *
     * تُقبل الدفعة فقط إن حملت قراراً صريحاً لكل بند غير مقفل. فإن اجتمعت كل
     * البنود على الاعتماد صار العقد معتمداً نهائياً وانتقل الطلب لمرحلة رفع
     * المتطلبات، وإلا سُجّلت ملاحظات العميل وعاد العقد للفريق لمراجعتها.
     *
     * @param  array<string, array{decision?: string, note?: string}>  $decisions
     * @return array{ok: bool, outcome?: string, error?: string}
     */
    public static function applyDecisions(ServiceRequest $sr, array $decisions): array
    {
        $contract = self::raw($sr);

        if (! $contract || empty($contract['clauses'])) {
            return ['ok' => false, 'error' => 'لا يوجد عقد مُرسَل لتسجيل قرارك عليه.'];
        }

        if ($error = self::decisionBlocker((string) ($contract['status'] ?? ''))) {
            return ['ok' => false, 'error' => $error];
        }

        $clauses = [];

        foreach (array_values($contract['clauses']) as $clause) {
            $id = (string) ($clause['id'] ?? '');

            // البند المعتمد سابقاً مقفل: يبقى على اعتماده ولا يُقرأ له قرار جديد
            if (($clause['decision'] ?? null) === 'approved') {
                $clauses[] = $clause;

                continue;
            }

            $decision = (string) ($decisions[$id]['decision'] ?? '');
            $note = trim((string) ($decisions[$id]['note'] ?? ''));

            if (! isset(self::DECISIONS[$decision])) {
                return ['ok' => false, 'error' => 'يرجى اتخاذ قرار صريح لكل بند من بنود العقد قبل الإرسال.'];
            }

            if ($decision !== 'approved' && $note === '') {
                return ['ok' => false, 'error' => 'يرجى كتابة التعديل المطلوب أو سبب الحذف للبنود التي لم توافق عليها.'];
            }

            $clauses[] = array_merge($clause, [
                'decision' => $decision,
                'note' => $decision === 'approved' ? '' : mb_substr($note, 0, self::NOTE_MAX),
                'decided_at' => now()->toIso8601String(),
            ]);
        }

        $allApproved = collect($clauses)->every(fn ($c) => ($c['decision'] ?? null) === 'approved');
        $outcome = $allApproved ? 'approved' : 'feedback';

        $contract['clauses'] = $clauses;
        $contract['status'] = $outcome;
        $contract[$allApproved ? 'approved_at' : 'feedback_at'] = now()->toIso8601String();

        // ختم الجولة الجارية بنتيجتها في سجلّ الجولات
        $rounds = array_values((array) ($contract['rounds'] ?? []));
        if ($rounds) {
            $last = count($rounds) - 1;
            $rounds[$last] = array_merge((array) $rounds[$last], [
                'decided_at' => now()->toIso8601String(),
                'outcome' => $outcome,
            ]);
            $contract['rounds'] = $rounds;
        }

        self::store($sr, $contract);
        $sr = $sr->refresh();

        // الاعتماد الكامل للعقد ينقل الطلب لمرحلة رفع متطلبات المشروع
        if ($allApproved) {
            $payload = (array) $sr->payload;
            $payload['_flow'] = array_merge((array) ($payload['_flow'] ?? []), [
                'stage' => 'awaiting_requirements',
                'contract_approved_at' => now()->toIso8601String(),
            ]);
            $sr->update(['payload' => $payload]);
            $sr = $sr->refresh();
        }

        self::notifyTeam($sr, $outcome);

        if ($allApproved) {
            // شكر العميل وتوضيح الخطوة التالية (النسخة الموقّعة من الشركة ثم رفع المتطلبات)
            MailTemplates::sendStage($sr, 'contract_approved');
        }

        return ['ok' => true, 'outcome' => $outcome];
    }

    /**
     * لماذا لا تُقبل قرارات العميل الآن؟ null إن كانت الجولة مفتوحة له.
     * تُقبل القرارات مرة واحدة لكل جولة: بعد إرسال الملاحظات ينتظر العميل النسخة المعدَّلة.
     */
    public static function decisionBlocker(string $status): ?string
    {
        return match ($status) {
            'sent' => null,
            'approved' => 'اعتُمد هذا العقد بالفعل، ولا يمكن تعديل القرارات بعد اعتماده.',
            'feedback' => 'وصلت ملاحظاتك بالفعل وهي قيد المراجعة لدى فريق وريد — ستصلك النسخة المعدَّلة قريباً.',
            default => 'لم يُرسل هذا العقد للمراجعة بعد.',
        };
    }

    /** إشعار فريق وريد بقرار العميل على العقد — فشل الإرسال لا يُفقد القرار. */
    private static function notifyTeam(ServiceRequest $sr, string $outcome): void
    {
        $contract = self::of($sr);

        if (! $contract) {
            return;
        }

        try {
            $client = $sr->name.($sr->company ? ' — '.$sr->company : '')
                .($sr->email ? ' · '.$sr->email : '')
                .($sr->phone && $sr->phone !== '—' ? ' · '.$sr->phone : '');

            if ($outcome === 'approved') {
                $lines = [
                    'اعتمد العميل مسوّدة العقد '.$contract['number'].' بالكامل ('.$contract['count'].' بنداً).',
                    'العميل: '.$client,
                    'الطلب: '.$sr->reference.' — جولة المراجعة رقم '.$contract['round'].'.',
                    'نُقل الطلب تلقائياً إلى مرحلة «رفع متطلبات المشروع»، ووصل العميل بريد شكر يوضّح أن '
                        .'النسخة الموقّعة من الشركة سترسَل إليه للتوقيع.',
                ];
                $subject = 'اعتمد العميل العقد '.$contract['number'].' — '.$sr->reference;
            } else {
                $lines = [
                    'أرسل العميل ملاحظاته على مسوّدة العقد '.$contract['number'].'.',
                    'العميل: '.$client,
                    'الطلب: '.$sr->reference.' — جولة المراجعة رقم '.$contract['round'].'.',
                    'البنود المعتمدة: '.$contract['approved_count'].' من '.$contract['count'].'، '
                        .'والبنود التي طلب تعديلها أو حذفها: '.$contract['objections_count'].'.',
                ];

                foreach ($contract['clauses'] as $clause) {
                    if (in_array($clause['decision'], ['edited', 'deleted'], true)) {
                        $lines[] = $clause['title'].' — '.$clause['decision_label'].': '.$clause['note'];
                    }
                }

                $subject = 'ملاحظات العميل على العقد '.$contract['number'].' — '.$sr->reference;
            }

            Mail::to((string) setting('contact_email', 'info@wareed.vip'))->send(new StageMessage(
                subjectLine: $subject,
                bodyText: implode("\n\n", $lines),
                link: route('filament.admin.pages.contracts'),
                linkLabel: 'فتح صفحة العقود',
            ));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** رابط مراجعة العقد للعميل: مخصّص عبر الدعوة، أو موقّع للنموذج العام. */
    public static function reviewUrl(ServiceRequest $sr): string
    {
        $invite = str_starts_with((string) $sr->source, 'quote_link:')
            ? substr((string) $sr->source, strlen('quote_link:'))
            : null;

        return $invite !== null
            ? route('quote.contract', $invite)
            : URL::signedRoute('quote.contract.signed', ['serviceRequest' => $sr->id]);
    }

    /** رابط تسجيل قرارات العميل على بنود العقد. */
    public static function decisionUrl(ServiceRequest $sr): string
    {
        $invite = str_starts_with((string) $sr->source, 'quote_link:')
            ? substr((string) $sr->source, strlen('quote_link:'))
            : null;

        return $invite !== null
            ? route('quote.contract.decision', $invite)
            : URL::signedRoute('quote.contract.decision.signed', ['serviceRequest' => $sr->id]);
    }

    /**
     * بيانات التوقيع والختم: قالب دائم يُضبط مرة واحدة من إعدادات الموقع
     * ويظهر في كل عقد، ويُعدَّل أو يُستبدل في أي وقت.
     */
    public static function signature(): array
    {
        $stamp = trim((string) setting('contract_stamp', ''));

        return [
            'name' => trim((string) setting('signer_name', '')) ?: self::DEFAULT_SIGNER['name'],
            'title' => trim((string) setting('signer_title', '')) ?: self::DEFAULT_SIGNER['title'],
            'stamp' => $stamp,
            'stamp_url' => self::stampUrl($stamp),
        ];
    }

    /** الموقّع الافتراضي عن الشركة حتى يُضبط غيره من إعدادات الموقع. */
    public const DEFAULT_SIGNER = [
        'name' => 'م. أحمد سامي رجب محمد عبدالعزيز',
        'title' => 'المدير التنفيذي لشركة وريد لتقنية المعلومات',
    ];

    /**
     * رابط مؤقّت لصورة الختم المحفوظة على القرص الخاص (لا public، فلا اعتماد على
     * storage:link). صلاحيته تكفي جلسة مراجعة وطباعة كاملة، ويُولَّد جديداً مع كل عرض.
     */
    private static function stampUrl(string $path): ?string
    {
        if ($path === '' || ! Storage::disk('local')->exists($path)) {
            return null;
        }

        return rescue(fn () => Storage::disk('local')->temporaryUrl($path, now()->addHours(6)), null, false);
    }
}
