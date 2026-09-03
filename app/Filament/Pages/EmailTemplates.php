<?php

namespace App\Filament\Pages;

use App\Mail\StageMessage;
use App\Models\ServiceRequest;
use App\Support\MailTemplates;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;

/**
 * محرّر قوالب البريد الإلكتروني المرسَل للعميل عبر مراحل الطلب:
 * استعراض النص المقترح، تعديله، معاينته على طلب حقيقي، إرسال تجريبي، وإرساله للعميل.
 */
class EmailTemplates extends Page
{
    protected string $view = 'filament.pages.email-templates';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = 'إدارة الموقع';

    protected static ?string $navigationLabel = 'قوالب البريد الإلكتروني';

    protected static ?int $navigationSort = 2;

    /** القالب المفتوح حالياً. */
    public string $stage = 'received';

    public string $subject = '';

    public string $body = '';

    /** الطلب المستخدَم في المعاينة والإرسال (فارغ = بيانات تجريبية). */
    public ?int $requestId = null;

    public function getTitle(): string
    {
        return 'قوالب البريد الإلكتروني';
    }

    public function mount(): void
    {
        $this->load($this->stage);
    }

    /** فتح قالب مرحلة أخرى (يستبدل ما لم يُحفظ). */
    public function select(string $stage): void
    {
        if (MailTemplates::exists($stage)) {
            $this->load($stage);
        }
    }

    private function load(string $stage): void
    {
        $this->stage = $stage;
        $this->subject = MailTemplates::subject($stage);
        $this->body = MailTemplates::body($stage);
    }

    /** @return array<int, array<string, mixed>> */
    public function getStagesProperty(): array
    {
        return collect(MailTemplates::TEMPLATES)
            ->map(fn (array $t, string $key) => [
                'key' => $key,
                'label' => $t['label'],
                'hint' => $t['hint'],
                'customised' => MailTemplates::isCustomised($key),
            ])
            ->values()
            ->all();
    }

    /** طلبات المتاجر المتاحة للمعاينة والإرسال. */
    public function getRequestsProperty(): array
    {
        return ServiceRequest::query()
            ->where(fn ($q) => $q->where('source', 'quote_form')->orWhere('source', 'like', 'quote_link:%'))
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(fn (ServiceRequest $sr) => [
                'id' => $sr->id,
                'label' => $sr->reference.' — '.$sr->name,
                'email' => $sr->email,
            ])
            ->all();
    }

    public function getSelectedRequestProperty(): ?ServiceRequest
    {
        return $this->requestId ? ServiceRequest::find($this->requestId) : null;
    }

    /** @return array<string, string> */
    public function getPreviewProperty(): array
    {
        $sr = $this->selectedRequest;
        $variables = MailTemplates::variables($sr);

        return [
            'subject' => MailTemplates::render($this->subject, $variables),
            'html' => MailTemplates::html(MailTemplates::render($this->body, $variables)),
            'link' => $variables['{رابط_الطلب}'],
            'to' => $sr?->email,
        ];
    }

    public function save(): void
    {
        MailTemplates::save($this->stage, $this->subject, $this->body);

        Notification::make()->title('حُفظ القالب')->success()->send();
    }

    public function resetTemplate(): void
    {
        MailTemplates::reset($this->stage);
        $this->load($this->stage);

        Notification::make()->title('استُعيد النص المقترح')->success()->send();
    }

    /** إرسال نسخة تجريبية إلى بريد الشركة. */
    public function sendTest(): void
    {
        $this->send((string) setting('contact_email', 'info@wareed.vip'), 'نسخة تجريبية');
    }

    /** إرسال الرسالة إلى بريد العميل صاحب الطلب المختار. */
    public function sendToClient(): void
    {
        $sr = $this->selectedRequest;

        if (! $sr?->email) {
            Notification::make()
                ->title('اختر طلباً لعميل له بريد إلكتروني أولاً')
                ->warning()
                ->send();

            return;
        }

        $this->send($sr->email, 'العميل');
    }

    private function send(string $to, string $who): void
    {
        if (trim($this->subject) === '' || trim($this->body) === '') {
            Notification::make()->title('العنوان والنص مطلوبان قبل الإرسال')->danger()->send();

            return;
        }

        $variables = MailTemplates::variables($this->selectedRequest);

        try {
            Mail::to($to)->send(new StageMessage(
                subjectLine: MailTemplates::render($this->subject, $variables),
                bodyText: MailTemplates::render($this->body, $variables),
                link: $variables['{رابط_الطلب}'],
            ));

            Notification::make()
                ->title('تم الإرسال إلى '.$who)
                ->body('أُرسلت الرسالة إلى '.$to.' — تحقّق من الوارد وصندوق الرسائل غير المرغوبة.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('فشل الإرسال')
                ->body('راجع إعدادات MAIL في ملف .env. السبب: '.mb_substr($e->getMessage(), 0, 220))
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
