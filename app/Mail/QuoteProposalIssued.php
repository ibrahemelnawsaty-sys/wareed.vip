<?php

namespace App\Mail;

use App\Http\Controllers\QuoteController;
use App\Models\ServiceRequest;
use App\Support\MailTemplates;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * بريد عرض السعر المُرسَل للعميل فور إصداره من لوحة المتابعة.
 * المرسِل هو بريد وريد المعتمد في الإعدادات (info@wareed.vip).
 */
class QuoteProposalIssued extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ServiceRequest $sr) {}

    public function envelope(): Envelope
    {
        $from = (string) setting('contact_email', 'info@wareed.vip');

        return new Envelope(
            from: new Address($from, 'وريد لتقنية المعلومات'),
            replyTo: [new Address($from, 'وريد لتقنية المعلومات')],
            // العنوان قابل للتعديل من: لوحة التحكم ← قوالب البريد الإلكتروني ← إرسال عرض السعر
            subject: MailTemplates::render(
                MailTemplates::subject('proposal_sent'),
                MailTemplates::variables($this->sr),
            ),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.quote-proposal', with: [
            'quote' => QuoteController::quoteOf($this->sr),
            'proposalUrl' => QuoteController::proposalUrl($this->sr),
        ]);
    }
}
