<?php

use App\Filament\Pages\EmailTemplates;
use App\Mail\QuoteProposalIssued;
use App\Mail\StageMessage;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\MailTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function mailAdmin(): User
{
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    return $user;
}

function quoteRequest(array $attributes = []): ServiceRequest
{
    return ServiceRequest::create(array_merge([
        'service_type' => 'ecommerce',
        'name' => 'أ. هاجر سلامة',
        'company' => 'متجر حواديت',
        'phone' => '00201016031031',
        'email' => 'hagersalma89@gmail.com',
        'status' => 'new',
        'source' => 'quote_link:hajar-salama',
        'payload' => [],
    ], $attributes));
}

it('يفتح محرّر قوالب البريد بالنص المقترح للمرحلة الأولى', function () {
    $this->actingAs(mailAdmin());

    Livewire\Livewire::test(EmailTemplates::class)
        ->assertSet('stage', 'received')
        ->assertSet('subject', MailTemplates::TEMPLATES['received']['subject'])
        ->assertSet('body', MailTemplates::TEMPLATES['received']['body']);
});

it('ينتقل بين قوالب المراحل ويتجاهل مرحلة غير موجودة', function () {
    $this->actingAs(mailAdmin());

    $page = Livewire\Livewire::test(EmailTemplates::class)
        ->call('select', 'delivered')
        ->assertSet('stage', 'delivered')
        ->assertSet('subject', MailTemplates::TEMPLATES['delivered']['subject']);

    $page->call('select', 'لا-وجود-لها')->assertSet('stage', 'delivered');
});

it('يحفظ القالب المعدَّل ثم يستعيد النص المقترح', function () {
    $this->actingAs(mailAdmin());

    expect(MailTemplates::isCustomised('in_progress'))->toBeFalse();

    Livewire\Livewire::test(EmailTemplates::class)
        ->call('select', 'in_progress')
        ->set('subject', 'انطلقنا — {الرقم_المرجعي}')
        ->set('body', 'مرحباً {العميل}، بدأنا العمل.')
        ->call('save');

    expect(MailTemplates::subject('in_progress'))->toBe('انطلقنا — {الرقم_المرجعي}')
        ->and(MailTemplates::isCustomised('in_progress'))->toBeTrue();

    Livewire\Livewire::test(EmailTemplates::class)
        ->call('select', 'in_progress')
        ->call('resetTemplate')
        ->assertSet('body', MailTemplates::TEMPLATES['in_progress']['body']);

    expect(MailTemplates::isCustomised('in_progress'))->toBeFalse();
});

it('يستبدل المتغيّرات ببيانات الطلب في المعاينة', function () {
    $this->actingAs(mailAdmin());
    $sr = quoteRequest();

    $variables = MailTemplates::variables($sr);

    expect($variables['{العميل}'])->toBe('أ. هاجر سلامة')
        ->and($variables['{المتجر}'])->toBe('متجر حواديت')
        ->and($variables['{الرقم_المرجعي}'])->toBe($sr->reference)
        ->and($variables['{رابط_الطلب}'])->toContain('/quote/hajar-salama');

    $rendered = MailTemplates::render('مرحباً {العميل}، طلبك {الرقم_المرجعي}', $variables);
    expect($rendered)->toBe('مرحباً أ. هاجر سلامة، طلبك '.$sr->reference);
});

it('يحوّل الأسطر الفارغة إلى فقرات ويهرّب وسوم HTML', function () {
    $html = MailTemplates::html("فقرة أولى\n\nفقرة <b>ثانية</b>");

    expect($html)->toContain('فقرة أولى')
        ->and($html)->toContain('&lt;b&gt;')
        ->and($html)->not->toContain('<b>ثانية</b>')
        ->and(substr_count($html, '<p style='))->toBe(2);
});

it('يرسل نسخة تجريبية إلى بريد الشركة من info@wareed.vip', function () {
    Mail::fake();
    $this->actingAs(mailAdmin());

    Livewire\Livewire::test(EmailTemplates::class)->call('sendTest');

    Mail::assertSent(
        StageMessage::class,
        fn ($mail) => $mail->hasTo(setting('contact_email', 'info@wareed.vip'))
            && $mail->hasFrom('info@wareed.vip')
    );
});

it('يرسل للعميل عند اختيار طلب، ويمتنع بلا طلب', function () {
    Mail::fake();
    $this->actingAs(mailAdmin());
    $sr = quoteRequest();

    // بلا طلب مختار لا يُرسل شيء
    Livewire\Livewire::test(EmailTemplates::class)->call('sendToClient');
    Mail::assertNotSent(StageMessage::class);

    Livewire\Livewire::test(EmailTemplates::class)
        ->set('requestId', $sr->id)
        ->call('select', 'delivered')
        ->call('sendToClient');

    Mail::assertSent(
        StageMessage::class,
        fn ($mail) => $mail->hasTo('hagersalma89@gmail.com')
            // العنوان وصل بمتغيّراته مستبدَلة
            && $mail->subjectLine === 'تسليم متجرك — متجر حواديت'
    );
});

it('لا يرسل قالباً فارغ العنوان أو النص', function () {
    Mail::fake();
    $this->actingAs(mailAdmin());

    Livewire\Livewire::test(EmailTemplates::class)
        ->set('body', '   ')
        ->call('sendTest');

    Mail::assertNotSent(StageMessage::class);
});

it('يأخذ بريد عرض السعر عنوانه ومقدّمته من القالب القابل للتعديل', function () {
    Mail::fake();
    $this->actingAs(mailAdmin());

    $sr = quoteRequest(['payload' => ['_quote' => [
        'items' => [['name' => 'تجهيز المتجر', 'qty' => 1, 'price' => 20000]],
        'vat_percent' => 0, 'currency' => 'ج.م', 'valid_days' => 30,
        'issued_at' => now()->toIso8601String(),
    ]]]);

    MailTemplates::save('proposal_sent', 'عرضك جاهز — {المتجر}', 'مرحباً {العميل}، تفضّل عرضك.');

    $rendered = (new QuoteProposalIssued($sr))->render();

    expect((new QuoteProposalIssued($sr))->envelope()->subject)->toBe('عرضك جاهز — متجر حواديت')
        ->and($rendered)->toContain('مرحباً أ. هاجر سلامة، تفضّل عرضك.');
});
