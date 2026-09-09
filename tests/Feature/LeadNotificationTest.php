<?php

use App\Mail\ServiceRequestConfirmation;
use App\Mail\ServiceRequestReceived;
use App\Mail\StageMessage;
use App\Models\ServiceRequest;
use App\Support\MailTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('notifies admin and sends the editable received template to the customer of a pipeline service', function () {
    Mail::fake();

    $sr = ServiceRequest::create([
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

    // العميل يصله قالب «استلام الطلب» القابل للتعديل: برقمه المرجعي ورابط متابعة طلبه
    Mail::assertSent(StageMessage::class, function (StageMessage $mail) use ($sr) {
        return $mail->hasTo('customer@example.com')
            && $mail->subjectLine === MailTemplates::render(MailTemplates::subject('received'), MailTemplates::variables($sr))
            && str_contains($mail->subjectLine, $sr->reference)
            && str_contains($mail->bodyText, 'عميل تجربة')
            && $mail->link === MailTemplates::variables($sr)['{رابط_الطلب}'];
    });
    Mail::assertNotSent(ServiceRequestConfirmation::class);
});

it('sends the training wording of the received template to a training customer', function () {
    Mail::fake();

    ServiceRequest::create([
        'service_type' => 'training', 'name' => 'د. سارة', 'phone' => '0100', 'email' => 'sara@example.com',
        'company' => 'جامعة النيل', 'status' => 'new', 'source' => 'service_training',
    ]);

    Mail::assertSent(StageMessage::class, fn (StageMessage $mail) => $mail->hasTo('sara@example.com')
        && str_contains($mail->bodyText, 'طلب البرنامج التدريبي لصالح جامعة النيل')
        && str_contains($mail->bodyText, 'مكالمة تعريفية'));
});

it('keeps the generic confirmation for general contact requests', function () {
    Mail::fake();

    ServiceRequest::create([
        'service_type' => 'general', 'name' => 'زائر', 'phone' => '0100', 'email' => 'visitor@example.com',
        'status' => 'new', 'source' => 'contact',
    ]);

    Mail::assertSent(ServiceRequestConfirmation::class, fn ($mail) => $mail->hasTo('visitor@example.com'));
    Mail::assertNotSent(StageMessage::class);
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
    Mail::assertNotSent(StageMessage::class);
});
