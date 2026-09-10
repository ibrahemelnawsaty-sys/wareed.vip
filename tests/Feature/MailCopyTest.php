<?php

use App\Mail\StageMessage;
use App\Models\ServiceRequest;
use App\Models\Setting;
use App\Support\MailTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

/** الرسائل المرسلة فعلياً عبر ناقل الاختبار (array): الموضوع وعناوين «إلى» و«نسخة» لكل رسالة. */
function sentMessages(): array
{
    return collect(app('mailer')->getSymfonyTransport()->messages())->map(function ($sent) {
        $email = $sent->getOriginalMessage();

        return [
            'subject' => $email->getSubject(),
            'to' => array_map(fn ($a) => $a->getAddress(), $email->getTo()),
            'cc' => array_map(fn ($a) => $a->getAddress(), $email->getCc()),
            'cc_names' => array_map(fn ($a) => $a->getName(), $email->getCc()),
        ];
    })->values()->all();
}

it('كل بريد صادر لعميل يحمل نسخة CC إلى بريد وريد، والموجّه إلى وريد أصلاً لا يُنسخ إليه', function () {
    // إنشاء الطلب يرسل بريد الاستلام للعميل وإشعار الطلب للفريق
    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'عميل', 'phone' => '0100', 'email' => 'client@example.com',
        'company' => 'متجر لمسة', 'status' => 'new', 'source' => 'quote_form',
    ]);

    $messages = sentMessages();
    $client = collect($messages)->first(fn ($m) => in_array('client@example.com', $m['to'], true));
    $team = collect($messages)->first(fn ($m) => in_array('info@wareed.vip', $m['to'], true));

    expect($client)->not->toBeNull()
        ->and($client['cc'])->toBe(['info@wareed.vip'])
        ->and($client['cc_names'])->toBe(['وريد لتقنية المعلومات'])
        ->and($team)->not->toBeNull()
        ->and($team['cc'])->toBe([]);

    // بريد مرحلة لاحق يحمل النسخة أيضاً
    expect(MailTemplates::sendStage($sr, 'delivered'))->toBeTrue();
    $all = sentMessages();
    $last = $all[array_key_last($all)];

    expect($last['to'])->toBe(['client@example.com'])
        ->and($last['cc'])->toBe(['info@wareed.vip'])
        ->and($last['subject'])->toContain('تسليم متجرك');
});

it('نسخة CC تتبع إعداد بريد التواصل ولا تتكرّر إن كانت الرسالة تنسخ إليه أصلاً', function () {
    Setting::set('contact_email', 'office@wareed.vip');

    Mail::to('client@example.com')->cc('office@wareed.vip')->send(new StageMessage('عنوان', 'نص'));
    Mail::to('other@example.com')->send(new StageMessage('عنوان', 'نص'));
    Mail::to('office@wareed.vip')->send(new StageMessage('عنوان', 'نص'));

    $all = sentMessages();

    expect($all[0]['cc'])->toBe(['office@wareed.vip'])
        ->and($all[1]['cc'])->toBe(['office@wareed.vip'])
        ->and($all[2]['cc'])->toBe([]);
});
