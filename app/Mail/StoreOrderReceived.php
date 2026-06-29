<?php

namespace App\Mail;

use App\Models\StoreOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StoreOrderReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public StoreOrder $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'طلب جديد من المنصة — '.$this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.store-order');
    }
}
