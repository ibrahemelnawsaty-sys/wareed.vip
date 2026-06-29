<?php

namespace App\Mail;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceRequestReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ServiceRequest $sr) {}

    public function envelope(): Envelope
    {
        $replyTo = [];
        if ($this->sr->email) {
            $replyTo[] = new Address($this->sr->email, $this->sr->name);
        }

        return new Envelope(
            subject: 'طلب جديد من منصة وريد — '.$this->sr->name,
            replyTo: $replyTo,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.service-request');
    }
}
