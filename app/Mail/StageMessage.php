<?php

namespace App\Mail;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
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
    ) {}

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
