<?php

namespace App\Support;

use App\Http\Controllers\QuoteController;
use App\Models\ServiceRequest;

/**
 * مسار كل خدمة: المراحل الثماني نفسها بمفاتيحها وآلياتها (اجتماع ← عرض سعر ← اعتماد ←
 * عقد ← متطلبات ← تنفيذ ← تسليم)، وتختلف الخدمات في التسميات والمفردات فقط —
 * فلا ثلاث آلات حالة، ولا اختلاف في التخزين أو المسارات أو اللوحة.
 */
class ServiceFlow
{
    /** الخدمات التي تسلك المسار — طلبات «general» (نموذج التواصل) خارجه. */
    public const TYPES = ['ecommerce', 'tech_solution', 'training'];

    private const PROFILES = [
        'ecommerce' => [
            'label' => 'المتاجر الإلكترونية',
            'thing' => 'المتجر',
            'yours' => 'متجرك الإلكتروني',
            'company_label' => 'اسم المتجر',
            'document_title' => 'طلب متجر إلكتروني',
            'document_en' => 'E-COMMERCE STORE REQUEST',
            'contract_title' => 'عقد تنفيذ مشروع',
            'contract_en' => 'SERVICE AGREEMENT',
            'requirements' => [
                'title' => 'متطلبات المشروع',
                'lead' => 'ارفع ملفات هويتك البصرية أو شعار المتجر، وبيانات منتجاتك (أسماء وأوصاف وأسعار وصور إن توفّرت)، وأي ملفات أخرى يحتاجها فريق وريد، مع وصف مختصر لكل ملف.',
                'placeholder' => 'وصف الملف (اختياري) — مثال: شعار المتجر',
            ],
            'stages' => [],
        ],
        'tech_solution' => [
            'label' => 'الحلول التقنية',
            'thing' => 'المشروع',
            'yours' => 'مشروعك التقني',
            'company_label' => 'الجهة',
            'document_title' => 'طلب حل تقني',
            'document_en' => 'TECH SOLUTION REQUEST',
            'contract_title' => 'عقد تنفيذ مشروع',
            'contract_en' => 'SERVICE AGREEMENT',
            'requirements' => [
                'title' => 'متطلبات المشروع',
                'lead' => 'ارفع البيانات التقنية اللازمة للتنفيذ: وثائق النظام الحالي أو وصف طريقة العمل، وصلاحيات الوصول أو بيانات الاعتماد، وأي ملفات مرجعية (تقارير، نماذج، بيانات أولية) يحتاجها فريق وريد، مع وصف مختصر لكل ملف.',
                'placeholder' => 'وصف الملف (اختياري) — مثال: وثيقة النظام الحالي',
            ],
            'stages' => [
                'awaiting_meeting' => ['client' => 'بانتظار تحديد موعد اجتماع تعريفي مع فريق وريد لفهم احتياجكم التقني.'],
                'meeting_scheduled' => ['client' => 'الاجتماع محدّد — نلتقي لفهم الاحتياج التقني ونطاق العمل لدى جهتكم.'],
                'quote_due' => ['client' => 'بعد الاجتماع يجهّز الفريق المقترح التقني وعرض السعر خلال 3 أيام عمل.'],
                'awaiting_approval' => ['client' => 'عرض السعر جاهز — بانتظار اعتمادك للمضي في المشروع.'],
                'awaiting_requirements' => ['client' => 'اعتمدت العقد — ارفع الآن البيانات التقنية وصلاحيات الوصول ووثائق النظام الحالي ليبدأ فريق وريد التنفيذ.'],
                'in_progress' => ['label' => 'تنفيذ المشروع', 'client' => 'بدأ تنفيذ المشروع — العدّاد يوضّح المتبقي حتى موعد التسليم.'],
                'delivered' => ['client' => 'تم تسليم الحل وتدريب فريقكم عليه. سعدنا بالعمل معكم.'],
            ],
        ],
        'training' => [
            'label' => 'البرامج التدريبية',
            'thing' => 'البرنامج التدريبي',
            'yours' => 'برنامجك التدريبي',
            'company_label' => 'الجهة',
            'document_title' => 'طلب برنامج تدريبي',
            'document_en' => 'TRAINING PROGRAM REQUEST',
            'contract_title' => 'عقد برنامج تدريبي',
            'contract_en' => 'TRAINING AGREEMENT',
            'requirements' => [
                'title' => 'بيانات المتدربين والجدولة',
                'lead' => 'ارفع قائمة المتدربين (الأسماء والبريد الإلكتروني والمستوى إن توفّر)، وأي مواد أو متطلبات خاصة بالبرنامج، واذكر موعد الانطلاق المفضّل والأيام المناسبة في وصف الملف.',
                'placeholder' => 'وصف الملف (اختياري) — مثال: قائمة المتدربين — الانطلاق أول أكتوبر',
            ],
            'stages' => [
                'awaiting_meeting' => ['label' => 'تحديد موعد المكالمة', 'client' => 'بانتظار تحديد موعد مكالمة تعريفية مع فريق وريد.'],
                'meeting_scheduled' => ['label' => 'المكالمة التعريفية', 'client' => 'المكالمة محدّدة — نتعرّف فيها على الاحتياج التدريبي والمستوى المستهدف.'],
                'quote_due' => ['client' => 'بعد المكالمة يجهّز الفريق المنهج وعرض السعر خلال 3 أيام عمل.'],
                'awaiting_approval' => ['client' => 'عرض السعر جاهز — بانتظار اعتمادك لتثبيت البرنامج.'],
                'awaiting_requirements' => ['label' => 'بيانات المتدربين والجدولة', 'client' => 'اعتمدت العقد — ارفع الآن قائمة المتدربين وحدّد موعد الانطلاق ليبدأ البرنامج.'],
                'in_progress' => ['label' => 'التدريب جارٍ', 'client' => 'انطلق البرنامج التدريبي — العدّاد يوضّح المتبقي حتى ختامه.'],
                'delivered' => ['client' => 'اكتمل البرنامج وصدرت الشهادات. سعدنا بالعمل معكم.'],
            ],
        ],
    ];

    /** نوع خدمة الطلب داخل المسار — أي نوع آخر (أو قديم) يُعامل كمتجر إلكتروني حفاظاً على الطلبات السابقة. */
    public static function type(ServiceRequest|string|null $sr): string
    {
        $type = $sr instanceof ServiceRequest ? (string) $sr->service_type : (string) $sr;

        return in_array($type, self::TYPES, true) ? $type : 'ecommerce';
    }

    /** مفردات الخدمة وتسمياتها. */
    public static function profile(ServiceRequest|string|null $sr): array
    {
        $type = self::type($sr);

        return array_merge(self::PROFILES[$type], ['type' => $type]);
    }

    public static function label(ServiceRequest|string|null $sr): string
    {
        return self::PROFILES[self::type($sr)]['label'];
    }

    /**
     * مراحل الخدمة: المفاتيح والترتيب والعدّاد والأيقونات من QuoteController::STAGES،
     * والتسميات ونصوص العميل بحسب الخدمة.
     */
    public static function stages(ServiceRequest|string|null $sr): array
    {
        $stages = QuoteController::STAGES;

        foreach (self::PROFILES[self::type($sr)]['stages'] as $key => $override) {
            $stages[$key] = array_merge($stages[$key], $override);
        }

        return $stages;
    }

    /** اسم المشروع كما يُخاطَب به العميل: «متجر حواديت»، «مشروع شركة النور»، «البرنامج التدريبي لجامعة…». */
    public static function project(ServiceRequest $sr): string
    {
        $company = trim((string) $sr->company);

        return match (self::type($sr)) {
            'tech_solution' => $company !== '' ? 'مشروع '.$company : 'مشروعك',
            'training' => $company !== '' ? 'البرنامج التدريبي لـ'.$company : 'برنامجك التدريبي',
            default => $company === '' ? 'متجرك' : (str_starts_with($company, 'متجر') ? $company : 'متجر '.$company),
        };
    }

    /**
     * إجابات نموذج الطلب بعناوينها للعرض: طلبات المتاجر تحمل مفاتيح عربية أصلاً، وطلبات
     * الخدمات الأخرى تحمل أسماء حقول نموذج الخدمة فتُترجم إلى عناوينها كما عرّفها المدير.
     *
     * @return array<string, mixed>
     */
    public static function answers(ServiceRequest $sr): array
    {
        $payload = array_filter(
            (array) $sr->payload,
            fn ($key) => ! str_starts_with((string) $key, '_'),
            ARRAY_FILTER_USE_KEY
        );

        if (self::type($sr) === 'ecommerce') {
            return $payload;
        }

        $labels = collect((array) $sr->service?->form_fields)
            ->filter(fn ($f) => is_array($f) && filled($f['name'] ?? null))
            ->pluck('label', 'name')
            ->all();

        $answers = [];
        foreach ($payload as $key => $value) {
            $answers[(string) ($labels[$key] ?? $key)] = $value;
        }

        return $answers;
    }
}
