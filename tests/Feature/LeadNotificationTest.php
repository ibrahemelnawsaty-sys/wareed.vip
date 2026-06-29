<?php

use App\Mail\ServiceRequestConfirmation;
use App\Mail\ServiceRequestReceived;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('notifies admin and customer by email when a service request is created', function () {
    Mail::fake();

    ServiceRequest::create([
        'service_type' => 'ecommerce',
        'name' => 'عميل تجربة',
        'phone' => '01055789056',
        'email' => 'customer@example.com',
        'message' => 'أريد متجراً',
        'status' => 'new',
        'source' => 'test',
    ]);

    // إشعار الأدمن إلى البريد المعتمد
    Mail::assertSent(ServiceRequestReceived::class, fn ($mail) => $mail->hasTo('info@wareed.vip'));
    // تأكيد للعميل
    Mail::assertSent(ServiceRequestConfirmation::class, fn ($mail) => $mail->hasTo('customer@example.com'));
});

it('does not send a customer confirmation when no email is provided', function () {
    Mail::fake();

    ServiceRequest::create([
        'service_type' => 'general',
        'name' => 'بلا بريد',
        'phone' => '01000000000',
        'status' => 'new',
    ]);

    Mail::assertSent(ServiceRequestReceived::class);
    Mail::assertNotSent(ServiceRequestConfirmation::class);
});
