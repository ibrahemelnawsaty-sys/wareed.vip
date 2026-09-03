<?php

namespace App\Support;

use App\Http\Controllers\QuoteController;
use App\Mail\StageMessage;
use App\Models\ServiceRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

/**
 * قوالب البريد الإلكتروني المرسَلة للعميل عبر مراحل الطلب — من الاستلام حتى التسليم.
 *
 * النصوص المقترحة أدناه هي الافتراضي، وأي تعديل يحفظه المستخدم من لوحة التحكم
 * يُخزَّن في الإعدادات (بلا هجرة قاعدة بيانات) ويحلّ محلّ الافتراضي.
 */
class MailTemplates
{
    /** المتغيّرات التي تُستبدل بقيمها داخل العنوان والنص. */
    public const VARIABLES = [
        '{العميل}' => 'اسم العميل',
        '{المتجر}' => 'اسم المتجر',
        '{الرقم_المرجعي}' => 'رقم الطلب المرجعي',
        '{رابط_الطلب}' => 'رابط صفحة متابعة الطلب',
        '{موعد_الاجتماع}' => 'تاريخ ووقت الاجتماع',
        '{مهلة_العرض}' => 'آخر موعد لتسليم عرض السعر',
        '{الإجمالي}' => 'إجمالي عرض السعر',
        '{موعد_التسليم}' => 'موعد تسليم المشروع',
    ];

    /**
     * مراحل الطلب بالترتيب، ولكل مرحلة عنوان ونص مقترح.
     *
     * @var array<string, array<string, string>>
     */
    public const TEMPLATES = [
        'received' => [
            'label' => 'استلام الطلب',
            'hint' => 'يُرسل فور وصول الطلب من النموذج.',
            'subject' => 'استلمنا طلبك — {الرقم_المرجعي}',
            'body' => <<<'TXT'
            مرحباً {العميل}،

            يسعدنا في وريد أن نؤكّد استلام طلب تجهيز متجر {المتجر}، وقد سُجّل لدينا تحت الرقم المرجعي {الرقم_المرجعي}.

            فريقنا يراجع تفاصيل الطلب الآن، وسنتواصل معك خلال وقت قصير لتحديد موعد اجتماع تعريفي نستمع فيه لتفاصيل مشروعك ونجيب على أسئلتك.

            يمكنك متابعة حالة طلبك في أي وقت من الرابط المرفق أدناه.
            TXT,
        ],

        'awaiting_meeting' => [
            'label' => 'تحديد موعد الاجتماع',
            'hint' => 'دعوة العميل لاختيار موعد يناسبه.',
            'subject' => 'موعد اجتماعنا التعريفي — {الرقم_المرجعي}',
            'body' => <<<'TXT'
            مرحباً {العميل}،

            لنبدأ العمل على متجر {المتجر} بالشكل الصحيح، نودّ ترتيب اجتماع تعريفي قصير (٣٠ دقيقة تقريباً) نناقش فيه:

            نطاق المتجر والمنتجات المطلوب رفعها، والهوية البصرية وطريقة العرض، وبوابات الدفع والشحن المناسبة لك، والجدول الزمني المتوقّع.

            أخبرنا بالموعد الذي يناسبك وسنؤكّده لك فوراً.
            TXT,
        ],

        'meeting_scheduled' => [
            'label' => 'تأكيد موعد الاجتماع',
            'hint' => 'يُرسل بعد تثبيت موعد الاجتماع في اللوحة.',
            'subject' => 'تأكيد موعد الاجتماع — {موعد_الاجتماع}',
            'body' => <<<'TXT'
            مرحباً {العميل}،

            نؤكّد لك موعد اجتماعنا التعريفي بخصوص متجر {المتجر} يوم {موعد_الاجتماع}.

            لتحقيق أقصى استفادة من الاجتماع، يفيدنا لو كانت لديك: نماذج من منتجاتك، وأمثلة لمتاجر يعجبك أسلوبها، وأي ملفات للهوية البصرية إن وُجدت.

            في انتظار لقائك، وسنكون على تواصل قبل الموعد للتذكير.
            TXT,
        ],

        'quote_due' => [
            'label' => 'تجهيز عرض السعر',
            'hint' => 'يُرسل بعد انتهاء الاجتماع أثناء إعداد العرض.',
            'subject' => 'عرض سعرك قيد التجهيز — {الرقم_المرجعي}',
            'body' => <<<'TXT'
            مرحباً {العميل}،

            شكراً لوقتك في اجتماع اليوم. فريقنا بدأ إعداد عرض السعر التفصيلي لمتجر {المتجر} بناءً على ما اتفقنا عليه.

            سيصلك العرض كاملاً بمراحله وبنوده وجدوله الزمني في موعد أقصاه {مهلة_العرض}.

            إن استجدّ لديك أي متطلّب تودّ إضافته قبل إصدار العرض، أخبرنا وسنضمّنه.
            TXT,
        ],

        'proposal_sent' => [
            'label' => 'إرسال عرض السعر',
            'hint' => 'مقدّمة بريد عرض السعر — يُضاف تحتها ملخّص البنود والإجمالي تلقائياً.',
            'subject' => 'عرض سعر متجرك الإلكتروني — {الرقم_المرجعي}',
            'body' => <<<'TXT'
            مرحباً {العميل}،

            يسعدنا موافاتكم بعرض السعر المخصّص لمتجر {المتجر} بناءً على ما ناقشناه في اجتماعنا.

            العرض يشمل البنود والمراحل والجدول الزمني وطريقة السداد، وتجدون تفاصيله كاملة في المستند المرفق أدناه.
            TXT,
        ],

        'awaiting_approval' => [
            'label' => 'متابعة اعتماد العرض',
            'hint' => 'تذكير لطيف إن لم يصل ردّ على العرض.',
            'subject' => 'بخصوص عرض السعر — {الرقم_المرجعي}',
            'body' => <<<'TXT'
            مرحباً {العميل}،

            نتابع معك بخصوص عرض السعر الخاص بمتجر {المتجر} بإجمالي {الإجمالي}.

            إن كان لديك أي استفسار حول البنود أو ترغب في تعديل النطاق أو طريقة السداد، فنحن مستعدّون لمناقشة ذلك ومواءمة العرض مع ما يناسبك.

            بمجرد اعتماد العرض نبدأ التنفيذ مباشرة وفق الجدول الزمني المتفق عليه.
            TXT,
        ],

        'in_progress' => [
            'label' => 'بدء التنفيذ',
            'hint' => 'يُرسل عند اعتماد العرض وانطلاق العمل.',
            'subject' => 'بدأنا تنفيذ متجرك — {الرقم_المرجعي}',
            'body' => <<<'TXT'
            مرحباً {العميل}،

            شكراً لثقتك. اعتُمد العرض وبدأ فريقنا فعلياً في تنفيذ متجر {المتجر}.

            موعد التسليم المستهدف هو {موعد_التسليم}، وسنوافيك بتحديث عند إنجاز كل مرحلة من مراحل المشروع.

            يمكنك متابعة حالة المشروع في أي وقت من رابط الطلب أدناه، وفريقنا متاح للردّ على أي استفسار خلال فترة التنفيذ.
            TXT,
        ],

        'delivered' => [
            'label' => 'تسليم المشروع',
            'hint' => 'يُرسل عند اكتمال التسليم النهائي.',
            'subject' => 'تسليم متجرك — {المتجر}',
            'body' => <<<'TXT'
            مرحباً {العميل}،

            يسعدنا إبلاغك باكتمال تنفيذ متجر {المتجر} وتسليمه بالكامل وفق ما اتُّفق عليه.

            سلّمناك بيانات الدخول والصلاحيات الكاملة، وأنجزنا التدريب على لوحة التحكم بحيث تتمكّن من إدارة المنتجات والطلبات باستقلالية.

            فريق وريد يبقى إلى جانبك بعد التسليم لأي دعم فنّي أو تطوير مستقبلي. نتمنّى لمتجرك انطلاقة موفّقة ومبيعات مباركة.
            TXT,
        ],
    ];

    /** مفتاح تخزين القالب في الإعدادات. */
    private static function key(string $stage, string $part): string
    {
        return "mail_tpl_{$stage}_{$part}";
    }

    public static function exists(string $stage): bool
    {
        return isset(self::TEMPLATES[$stage]);
    }

    /** العنوان المحفوظ أو المقترح. */
    public static function subject(string $stage): string
    {
        return (string) Setting::get(self::key($stage, 'subject'), self::TEMPLATES[$stage]['subject'] ?? '');
    }

    /** النص المحفوظ أو المقترح. */
    public static function body(string $stage): string
    {
        return (string) Setting::get(self::key($stage, 'body'), self::TEMPLATES[$stage]['body'] ?? '');
    }

    /** هل عُدِّل القالب عن النص المقترح؟ */
    public static function isCustomised(string $stage): bool
    {
        return self::subject($stage) !== (self::TEMPLATES[$stage]['subject'] ?? '')
            || self::body($stage) !== (self::TEMPLATES[$stage]['body'] ?? '');
    }

    public static function save(string $stage, string $subject, string $body): void
    {
        Setting::set(self::key($stage, 'subject'), trim($subject), 'mail', 'text');
        Setting::set(self::key($stage, 'body'), trim($body), 'mail', 'textarea');
    }

    /** استعادة النص المقترح بحذف المحفوظ. */
    public static function reset(string $stage): void
    {
        Setting::set(self::key($stage, 'subject'), self::TEMPLATES[$stage]['subject'], 'mail', 'text');
        Setting::set(self::key($stage, 'body'), self::TEMPLATES[$stage]['body'], 'mail', 'textarea');
    }

    /**
     * قيم المتغيّرات لطلب بعينه، أو قيم تجريبية عند غيابه (للمعاينة).
     *
     * @return array<string, string>
     */
    public static function variables(?ServiceRequest $sr = null): array
    {
        if (! $sr) {
            return [
                '{العميل}' => 'أ. هاجر سلامة',
                '{المتجر}' => 'متجر حواديت',
                '{الرقم_المرجعي}' => 'WRD-'.now()->format('Y-m-d').'-00100',
                '{رابط_الطلب}' => url('/quote'),
                '{موعد_الاجتماع}' => now()->addDays(2)->format('Y/m/d — H:i'),
                '{مهلة_العرض}' => QuoteController::deadlineFor(now())->format('Y/m/d'),
                '{الإجمالي}' => '42,750 ج.م',
                '{موعد_التسليم}' => now()->addWeeks(6)->format('Y/m/d'),
            ];
        }

        $flow = QuoteController::flowOf($sr);
        $quote = QuoteController::quoteOf($sr);
        $money = fn ($n, $cur) => number_format((float) $n, ((float) $n == (int) $n) ? 0 : 2).' '.$cur;

        return [
            '{العميل}' => (string) $sr->name,
            '{المتجر}' => (string) ($sr->company ?: 'متجرك'),
            '{الرقم_المرجعي}' => $sr->reference,
            '{رابط_الطلب}' => QuoteController::statusUrl($sr),
            '{موعد_الاجتماع}' => $flow['meeting_at']?->format('Y/m/d — H:i') ?? 'سيُحدَّد لاحقاً',
            '{مهلة_العرض}' => ($flow['count_to'] ?? QuoteController::deadlineFor($sr->created_at ?? now()))->format('Y/m/d'),
            '{الإجمالي}' => $quote ? $money($quote['total'], $quote['currency']) : '—',
            '{موعد_التسليم}' => $flow['due_at']?->format('Y/m/d')
                ?? ($quote['delivery_at'] ?? null)?->format('Y/m/d')
                ?? 'سيُحدَّد لاحقاً',
        ];
    }

    /**
     * إرسال بريد المرحلة إلى العميل — يُستدعى مع كل إجراء ينقل الطلب مرحلةً.
     * يعيد false إن لم يكن للعميل بريد أو تعذّر الإرسال؛ والفشل لا يُعطّل الإجراء نفسه.
     */
    public static function sendStage(ServiceRequest $sr, string $stage, bool $withSummary = false): bool
    {
        if (! self::exists($stage) || ! filter_var((string) $sr->email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $variables = self::variables($sr);

        try {
            Mail::to($sr->email)->send(new StageMessage(
                subjectLine: self::render(self::subject($stage), $variables),
                bodyText: self::render(self::body($stage), $variables),
                link: $variables['{رابط_الطلب}'],
                summaryOf: $withSummary ? $sr : null,
            ));

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    /** استبدال المتغيّرات داخل نص. */
    public static function render(string $text, array $variables): string
    {
        return strtr($text, $variables);
    }

    /**
     * تحويل النص المكتوب إلى فقرات HTML آمنة — الأسطر الفارغة تفصل الفقرات.
     */
    public static function html(string $text): string
    {
        $paragraphs = preg_split('/\n\s*\n/u', trim($text)) ?: [];

        return collect($paragraphs)
            ->map(fn ($p) => trim($p))
            ->filter()
            ->map(fn ($p) => '<p style="margin:0 0 14px;color:#55638a;">'.nl2br(e($p)).'</p>')
            ->implode("\n");
    }
}
