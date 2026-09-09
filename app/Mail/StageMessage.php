<?php

namespace App\Mail;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * رسالة مرحلة من مراحل الطلب، نصّها من قوالب البريد الإلكتروني القابلة للتعديل من اللوحة.
 * المرسِل هو بريد وريد المعتمد في الإعدادات (info@wareed.vip).
 */
class StageMessage extends Mailable
{
    use Queueable, SerializesModels;

    // الخصائص العامة تتقدّم على بيانات with() في Laravel، فالقيمة الافتراضية تُضبط هنا لا هناك
    public function __construct(
        public string $subjectLine,
        public string $bodyText,
        public ?string $link = null,
        public string $linkLabel = 'متابعة الطلب',
        // إن مُرِّر طلب، أُلحق بالرسالة ملخّص عرضه وجدوله ودفعاته
        public ?ServiceRequest $summaryOf = null,
        // مرفق اختياري من قرص تخزين: ['disk' => 'local', 'path' => المسار, 'as' => اسم الملف كما يصل العميل]
        public ?array $attachment = null,
    ) {}

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        $path = trim((string) ($this->attachment['path'] ?? ''));

        if ($path === '') {
            return [];
        }

        return [
            Attachment::fromStorageDisk((string) ($this->attachment['disk'] ?? 'local'), $path)
                ->as((string) (($this->attachment['as'] ?? '') ?: basename($path))),
        ];
    }

    public function envelope(): Envelope
    {
        $from = (string) setting('contact_email', 'info@wareed.vip');

        return new Envelope(
            from: new Address($from, 'وريد لتقنية المعلومات'),
            replyTo: [new Address($from, 'وريد لتقنية المعلومات')],
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.stage', with: [
            'title' => $this->subjectLine,
        ]);
    }
}
