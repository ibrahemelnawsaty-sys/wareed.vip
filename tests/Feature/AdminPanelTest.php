<?php

use App\Filament\Pages\QuoteRequests;
use App\Http\Controllers\QuoteController;
use App\Mail\QuoteProposalIssued;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function adminUser(): User
{
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    return $user;
}

it('يحمّل صفحة دخول الأدمن', function () {
    $this->get('/admin/login')->assertSuccessful();
});

it('يحمّل كل صفحات لوحة الأدمن دون أخطاء', function () {
    $this->actingAs(adminUser());

    $pages = [
        '/admin',
        '/admin/services',
        '/admin/services/create',
        '/admin/pages',
        '/admin/pages/create',
        '/admin/service-requests',
        '/admin/stores',
        '/admin/stores/create',
        '/admin/products',
        '/admin/products/create',
        '/admin/categories',
        '/admin/store-orders',
        '/admin/manage-settings',
        '/admin/quote-requests',
    ];

    foreach ($pages as $url) {
        $this->get($url)->assertSuccessful();
    }
});

it('يحمّل الصفحات العامة', function () {
    $this->get('/')->assertSuccessful();
    $this->get('/sitemap.xml')->assertSuccessful();
    $this->get('/robots.txt')->assertSuccessful();
});

it('يعرض طلبات نموذج المتاجر في لوحة المتابعة ويسمح بحذفها', function () {
    $this->actingAs(adminUser());

    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'أ. هاجر سلامة', 'phone' => '—',
        'budget' => 'أكثر من 100 ألف جنيه', 'status' => 'new', 'source' => 'quote_link:hajar-salama',
        'payload' => ['مجال المتجر' => 'عطور ومستحضرات تجميل'],
    ]);

    // طلب من خارج النموذج لا يظهر في اللوحة
    ServiceRequest::create([
        'service_type' => 'training', 'name' => 'طلب تدريب', 'phone' => '0100', 'status' => 'new', 'source' => 'service_training',
    ]);

    Livewire\Livewire::test(QuoteRequests::class)
        ->assertSee($sr->reference)
        ->assertSee('أ. هاجر سلامة')
        ->assertSee('عطور ومستحضرات تجميل')
        ->assertDontSee('طلب تدريب')
        ->call('markStatus', $sr->id, 'proposal')
        ->call('deleteRequest', $sr->id);

    expect(ServiceRequest::find($sr->id))->toBeNull()
        // الطلبات خارج النموذج لا تتأثر
        ->and(ServiceRequest::count())->toBe(1);
});

it('يُصدر عرض السعر من اللوحة ويرسله للعميل بالبريد', function () {
    Mail::fake();
    $this->actingAs(adminUser());

    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'أ. هاجر سلامة',
        'phone' => '00201016031031', 'email' => 'hagersalma89@gmail.com', 'company' => 'متجر حواديت',
        'status' => 'new', 'source' => 'quote_link:hajar-salama',
        'payload' => ['الخدمات المطلوبة' => ['تجهيز المتجر ورفع المنتجات', 'تصوير المنتجات']],
    ]);

    Livewire\Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        // البنود تُقترح تلقائياً من الخدمات التي اختارها العميل
        ->assertSet('draft.items.0.name', 'تجهيز المتجر ورفع المنتجات')
        ->assertSet('draft.items.1.name', 'تصوير المنتجات')
        ->set('draft.items.0.price', 18000)
        ->set('draft.items.1.price', 2500)
        ->set('draft.items.1.qty', 2)
        ->set('draft.vat_percent', 14)
        ->set('draft.timeline', '3 أسابيع')
        ->call('issueQuote', true)
        ->assertSet('editingId', null);

    $sr->refresh();
    $quote = QuoteController::quoteOf($sr);

    expect($sr->status)->toBe('proposal')
        ->and($quote['subtotal'])->toBe(23000.0)
        ->and($quote['vat'])->toBe(3220.0)
        ->and($quote['total'])->toBe(26220.0)
        ->and($quote['timeline'])->toBe('3 أسابيع');

    Mail::assertSent(
        QuoteProposalIssued::class,
        fn ($mail) => $mail->hasTo('hagersalma89@gmail.com')
            && $mail->hasFrom('info@wareed.vip')
    );
});

it('يحفظ عرض السعر دون إرسال بريد عند الطلب', function () {
    Mail::fake();
    $this->actingAs(adminUser());

    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'عميل', 'phone' => '—', 'email' => 'c@example.com',
        'status' => 'new', 'source' => 'quote_form', 'payload' => [],
    ]);

    Livewire\Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        // الضريبة 14% افتراضياً فلا يحتاج المستخدم لكتابتها كل مرة
        ->assertSet('draft.vat_percent', (float) QuoteController::DEFAULT_VAT_PERCENT)
        ->set('draft.items.0.name', 'تجهيز المتجر')
        ->set('draft.items.0.price', 9000)
        ->call('issueQuote', false);

    $quote = QuoteController::quoteOf($sr->fresh());
    expect($quote['subtotal'])->toBe(9000.0)
        ->and($quote['vat'])->toBe(1260.0)
        ->and($quote['total'])->toBe(10260.0);
    // إشعارات استلام الطلب تُرسل عند إنشائه؛ المهم ألّا يُرسل بريد عرض السعر
    Mail::assertNotSent(QuoteProposalIssued::class);
});

it('يرفض إصدار عرض بلا بنود ويسمح بحذف العرض', function () {
    Mail::fake();
    $this->actingAs(adminUser());

    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'عميل', 'phone' => '—',
        'status' => 'new', 'source' => 'quote_form', 'payload' => [],
    ]);

    Livewire\Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->set('draft.items.0.name', '')
        ->call('issueQuote', true);

    expect(QuoteController::quoteOf($sr->fresh()))->toBeNull();

    Livewire\Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->set('draft.items.0.name', 'بند')
        ->set('draft.items.0.price', 500)
        ->call('issueQuote', false);

    expect(QuoteController::quoteOf($sr->fresh()))->not->toBeNull();

    Livewire\Livewire::test(QuoteRequests::class)->call('deleteQuote', $sr->id);

    expect(QuoteController::quoteOf($sr->fresh()))->toBeNull();
});

it('ينقل الطلب عبر مراحل المسار ويشغّل العدّاد في المرحلتين الصحيحتين', function () {
    Mail::fake();
    $this->actingAs(adminUser());

    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'أ. هاجر سلامة', 'phone' => '00201016031031',
        'email' => 'hagersalma89@gmail.com', 'status' => 'new', 'source' => 'quote_link:hajar-salama',
        'payload' => ['الخدمات المطلوبة' => ['تجهيز المتجر ورفع المنتجات']],
    ]);

    $flow = fn () => QuoteController::flowOf($sr->fresh());

    // البداية: بانتظار تحديد موعد الاجتماع — بلا عدّاد
    expect($flow()['stage'])->toBe('awaiting_meeting')
        ->and($flow()['counting'])->toBeFalse();

    $page = Livewire\Livewire::test(QuoteRequests::class);

    // تثبيت موعد الاجتماع — ما زال بلا عدّاد
    $page->set("flowInput.{$sr->id}.meeting_at", '2026-09-01T11:00')->call('setMeeting', $sr->id);
    expect($flow()['stage'])->toBe('meeting_scheduled')
        ->and($flow()['counting'])->toBeFalse()
        ->and($flow()['meeting_at']->format('Y-m-d H:i'))->toBe('2026-09-01 11:00');

    // بعد الاجتماع: يعمل العدّاد حتى تسليم العرض (3 أيام عمل)
    $page->call('meetingDone', $sr->id);
    expect($flow()['stage'])->toBe('quote_due')
        ->and($flow()['counting'])->toBeTrue()
        ->and($flow()['count_to']->toDateString())
        ->toBe(QuoteController::deadlineFor($flow()['meeting_done_at'])->toDateString());

    // إصدار العرض ينقل تلقائياً إلى اعتماد العميل — ويتوقف العدّاد
    $page->call('openQuote', $sr->id)
        ->set('draft.items.0.price', 20000)
        ->call('issueQuote', false);
    expect($flow()['stage'])->toBe('awaiting_approval')
        ->and($flow()['counting'])->toBeFalse();

    // الاعتماد وبدء التنفيذ: يعمل العدّاد حتى موعد التسليم
    $page->set("flowInput.{$sr->id}.due_at", '2026-10-15')->call('startExecution', $sr->id);
    expect($flow()['stage'])->toBe('in_progress')
        ->and($flow()['counting'])->toBeTrue()
        ->and($flow()['due_at']->toDateString())->toBe('2026-10-15')
        ->and($sr->fresh()->status)->toBe('won');

    // التسليم: نهاية المسار بلا عدّاد
    $page->call('markDelivered', $sr->id);
    expect($flow()['stage'])->toBe('delivered')
        ->and($flow()['counting'])->toBeFalse()
        ->and($flow()['delivered_at'])->not->toBeNull();
});

it('يرفض تثبيت الاجتماع أو بدء التنفيذ بلا تاريخ', function () {
    $this->actingAs(adminUser());

    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'عميل', 'phone' => '—',
        'status' => 'new', 'source' => 'quote_form', 'payload' => [],
    ]);

    Livewire\Livewire::test(QuoteRequests::class)->call('setMeeting', $sr->id);
    expect(QuoteController::flowOf($sr->fresh())['stage'])->toBe('awaiting_meeting');

    Livewire\Livewire::test(QuoteRequests::class)->call('startExecution', $sr->id);
    expect(QuoteController::flowOf($sr->fresh())['stage'])->toBe('awaiting_meeting');
});

it('يبدأ محرّر العرض بدفعتين افتراضيتين ويحسب قيمتيهما تلقائياً', function () {
    Mail::fake();
    $this->actingAs(adminUser());

    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'عميل', 'phone' => '—', 'email' => 'c@example.com',
        'status' => 'new', 'source' => 'quote_form', 'payload' => [],
    ]);

    Livewire\Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->assertSet('draft.payments.0.percent', 50.0)
        ->assertSet('draft.payments.1.percent', 50.0)
        ->set('draft.items.0.name', 'تجهيز المتجر')
        ->set('draft.items.0.price', 10000)
        ->set('draft.vat_percent', 0)
        ->set('draft.payments.0.percent', 40)
        ->set('draft.payments.1.percent', 60)
        ->call('issueQuote', false);

    $quote = QuoteController::quoteOf($sr->fresh());

    expect($quote['payments'][0]['amount'])->toBe(4000.0)
        ->and($quote['payments'][1]['amount'])->toBe(6000.0)
        ->and($quote['payments_percent'])->toBe(100.0);
});

it('يدعم البنود المجانية والمراحل وملاحظات الاشتراك في المحرّر', function () {
    Mail::fake();
    $this->actingAs(adminUser());

    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'عميل', 'phone' => '—',
        'status' => 'new', 'source' => 'quote_form',
        'payload' => ['الخدمات المطلوبة' => ['تجهيز المتجر', 'تدريب']],
    ]);

    Livewire\Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->set('draft.items.0.phase', 'المرحلة الأولى')
        ->set('draft.items.0.price', 15000)
        ->set('draft.items.0.note', 'اشتراك سنوي')
        ->set('draft.items.1.phase', 'المرحلة الأولى')
        ->set('draft.items.1.price', 2000)
        // تعليم البند مجانياً يصفّر سعره
        ->call('toggleFree', 1)
        ->assertSet('draft.items.1.price', 0)
        ->set('draft.vat_percent', 0)
        ->call('issueQuote', false);

    $quote = QuoteController::quoteOf($sr->fresh());

    expect($quote['total'])->toBe(15000.0)
        ->and($quote['items'][0]['note'])->toBe('اشتراك سنوي')
        ->and($quote['items'][1]['free'])->toBeTrue()
        ->and($quote['phases'])->toHaveCount(1)
        ->and($quote['phases'][0]['name'])->toBe('المرحلة الأولى');
});
