<?php

use App\Filament\Pages\QuoteRequests;
use App\Http\Controllers\QuoteController;
use App\Mail\QuoteProposalIssued;
use App\Mail\StageMessage;
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
        ->set('draft.items.0.unit', 'باقة')
        ->set('draft.items.0.price', 9000)
        ->call('issueQuote', false);

    $quote = QuoteController::quoteOf($sr->fresh());
    expect($quote['subtotal'])->toBe(9000.0)
        ->and($quote['vat'])->toBe(1260.0)
        ->and($quote['total'])->toBe(10260.0)
        ->and($quote['items'][0]['unit'])->toBe('باقة');

    // الوحدة تعود كما هي عند إعادة فتح المحرّر
    Livewire\Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->assertSet('draft.items.0.unit', 'باقة');
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

it('يعيد ترتيب بنود عرض السعر بالسحب أو بالأسهم', function () {
    Mail::fake();
    $this->actingAs(adminUser());

    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'عميل', 'phone' => '—',
        'status' => 'new', 'source' => 'quote_form', 'payload' => [],
    ]);

    $item = fn (string $name) => [
        'phase' => '', 'name' => $name, 'desc' => '', 'note' => '',
        'qty' => 1, 'price' => 100, 'free' => false,
    ];

    $page = Livewire\Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->set('draft.items', [$item('أ'), $item('ب'), $item('ج')]);

    $names = fn () => array_column($page->get('draft.items'), 'name');

    // إفلات البند الأخير فوق أول بند = الفراغ رقم 0
    $page->call('moveItem', 2, 0);
    expect($names())->toBe(['ج', 'أ', 'ب']);

    // إفلات «ج» تحت «أ» = الفراغ رقم 2 — إدراج بين البندين لا تبديل معهما
    $page->call('moveItem', 0, 2);
    expect($names())->toBe(['أ', 'ج', 'ب']);

    // فراغ خارج النطاق يُقصَر على ما بعد آخر بند
    $page->call('moveItem', 0, 99);
    expect($names())->toBe(['ج', 'ب', 'أ']);

    // الفراغان الملاصقان للبند نفسه لا يحرّكانه، والمصدر غير الموجود يُتجاهل
    $page->call('moveItem', 1, 1)->call('moveItem', 1, 2)->call('moveItem', 7, 0);
    expect($names())->toBe(['ج', 'ب', 'أ']);

    // الترتيب الجديد هو ترتيب البنود في العرض الصادر
    $page->call('issueQuote', false);
    expect(array_column(QuoteController::quoteOf($sr->fresh())['items'], 'name'))
        ->toBe(['ج', 'ب', 'أ']);
});

it('يحسب الخصم كنسبة من الإجمالي ويشتقّها للعروض القديمة', function () {
    Mail::fake();
    $this->actingAs(adminUser());

    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'عميل', 'phone' => '—',
        'status' => 'new', 'source' => 'quote_form', 'payload' => [],
    ]);

    Livewire\Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->set('draft.items', [['phase' => '', 'name' => 'تجهيز المتجر', 'desc' => '', 'note' => '',
            'qty' => 1, 'unit' => '', 'price' => 20000, 'free' => false]])
        ->set('draft.discount_percent', 25)
        ->set('draft.vat_percent', 14)
        // القيمة تُحسب لحظياً في ملخّص المحرّر قبل الإصدار
        ->assertSet('draft.discount_percent', 25)
        ->call('issueQuote', false);

    $quote = QuoteController::quoteOf($sr->fresh());
    expect($quote['discount_percent'])->toBe(25.0)
        ->and($quote['discount'])->toBe(5000.0)
        ->and($quote['vat'])->toBe(2100.0)
        ->and($quote['total'])->toBe(17100.0);

    // النسبة تعود كما هي عند إعادة فتح المحرّر
    Livewire\Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->assertSet('draft.discount_percent', 25.0);

    // عرض قديم مخزّن بقيمة خصم مباشرة يُفتح بنسبته المكافئة
    $sr->update(['payload' => array_merge((array) $sr->payload, ['_quote' => [
        'items' => [['name' => 'تجهيز المتجر', 'qty' => 1, 'price' => 20000]],
        'discount' => 4000, 'vat_percent' => 14, 'currency' => 'ج.م',
        'valid_days' => 30, 'issued_at' => now()->toIso8601String(),
    ]])]);

    Livewire\Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->assertSet('draft.discount_percent', 20.0);
});

it('يجدول مراحل التسليم ويحفظ تواريخ استحقاق الدفعات', function () {
    Mail::fake();
    $this->actingAs(adminUser());

    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'عميل', 'phone' => '—',
        'status' => 'new', 'source' => 'quote_form', 'payload' => [],
    ]);

    $item = fn (string $phase, string $name) => [
        'phase' => $phase, 'name' => $name, 'desc' => '', 'note' => '',
        'qty' => 1, 'unit' => '', 'price' => 5000, 'free' => false,
    ];

    $page = Livewire\Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->set('draft.items', [
            $item('المرحلة الأولى', 'تجهيز المتجر'),
            $item('المرحلة الثانية', 'ربط بوابات الدفع'),
        ]);

    // كل ضغطة «إضافة مرحلة» ترث أول مرحلة لم تُجدوَل بعد
    $page->call('addStage')->call('addStage')->call('addStage');
    expect(array_column($page->get('draft.schedule'), 'phase'))
        ->toBe(['المرحلة الأولى', 'المرحلة الثانية', '']);

    $page->call('removeStage', 2);
    expect($page->get('draft.schedule'))->toHaveCount(2);

    $page->set('draft.schedule.0.start', '2026-09-10')
        ->set('draft.schedule.0.end', '2026-10-15')
        ->set('draft.schedule.1.start', '2026-10-16')
        ->set('draft.schedule.1.end', '2026-11-30')
        ->set('draft.payments.0.due', '2026-10-01')
        ->call('issueQuote', false);

    $quote = QuoteController::quoteOf($sr->fresh());
    expect($quote['schedule'])->toHaveCount(2)
        ->and($quote['schedule'][1]['phase'])->toBe('المرحلة الثانية')
        ->and($quote['schedule'][1]['start']->toDateString())->toBe('2026-10-16')
        ->and($quote['delivery_at']->toDateString())->toBe('2026-11-30')
        ->and($quote['payments'][0]['due']->toDateString())->toBe('2026-10-01');

    // الجدول والتواريخ تعود كما هي عند إعادة فتح المحرّر
    Livewire\Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->assertSet('draft.schedule.1.start', '2026-10-16')
        ->assertSet('draft.schedule.1.end', '2026-11-30')
        ->assertSet('draft.payments.0.due', '2026-10-01');
});

it('يضيف بنوداً اختيارية تُعرض للاطلاع ولا تدخل في الإجمالي', function () {
    Mail::fake();
    $this->actingAs(adminUser());

    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'عميل', 'phone' => '—',
        'status' => 'new', 'source' => 'quote_form', 'payload' => [],
    ]);

    $page = Livewire\Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->set('draft.items', [['phase' => '', 'name' => 'تجهيز المتجر', 'desc' => '', 'note' => '',
            'qty' => 1, 'unit' => '', 'price' => 20000, 'free' => false]])
        ->set('draft.discount_percent', 0)
        ->set('draft.vat_percent', 0)
        // خصم الباقات وضريبتها لهما اختبارهما الخاص؛ هنا نقيس العزل عن المستحق فقط
        ->set('draft.extras_vat_percent', 0);

    $page->call('addExtra')->call('addExtra')->call('addExtra');
    expect($page->get('draft.extras'))->toHaveCount(3);

    $page->call('removeExtra', 2)
        ->set('draft.extras.0.name', 'تطبيق جوال')
        ->set('draft.extras.0.price', 35000)
        ->set('draft.extras.1.name', 'إدارة الإعلانات')
        ->set('draft.extras.1.qty', 3)
        ->set('draft.extras.1.unit', 'شهر')
        ->set('draft.extras.1.price', 2500);

    // الإجماليات اللحظية: المستحق للبنود الأساسية، والاختيارية على حدة
    $totals = $page->get('draftTotals');
    expect($totals['total'])->toBe(20000.0)
        ->and($totals['extras_total'])->toBe(42500.0);

    $page->call('issueQuote', false);

    $quote = QuoteController::quoteOf($sr->fresh());
    expect($quote['total'])->toBe(20000.0)
        ->and($quote['extras'])->toHaveCount(2)
        ->and($quote['extras_total'])->toBe(42500.0);

    // البنود الاختيارية تعود كما هي عند إعادة فتح المحرّر
    Livewire\Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->assertSet('draft.extras.1.unit', 'شهر')
        ->assertSet('draft.extras.1.price', 2500.0);
});

it('يرسل بريد كل مرحلة للعميل مع الزر الذي ينقلها', function () {
    Mail::fake();
    $this->actingAs(adminUser());

    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'أ. هاجر سلامة', 'company' => 'متجر حواديت',
        'phone' => '00201016031031', 'email' => 'hagersalma89@gmail.com',
        'status' => 'new', 'source' => 'quote_link:hajar-salama',
        'payload' => ['_quote' => [
            'items' => [['name' => 'تجهيز المتجر', 'qty' => 1, 'price' => 20000]],
            'vat_percent' => 0, 'currency' => 'ج.م', 'valid_days' => 30,
            'issued_at' => now()->toIso8601String(),
        ]],
    ]);

    $sentSubjects = fn () => collect(Mail::sent(StageMessage::class))
        ->map(fn ($m) => $m->subjectLine)->all();

    $page = Livewire\Livewire::test(QuoteRequests::class);

    $page->set("flowInput.{$sr->id}.meeting_at", '2026-09-10T11:00')->call('setMeeting', $sr->id);
    $page->call('meetingDone', $sr->id);
    $page->set("flowInput.{$sr->id}.due_at", '2026-11-30')->call('startExecution', $sr->id);
    $page->call('markDelivered', $sr->id);

    $subjects = implode(' | ', $sentSubjects());
    expect($subjects)->toContain('تأكيد موعد الاجتماع')
        ->toContain('قيد التجهيز')
        ->toContain('بدأنا تنفيذ متجرك')
        ->toContain('تسليم متجرك');

    // بريد بدء التنفيذ وحده يحمل ملخّص العرض الكامل
    Mail::assertSent(
        StageMessage::class,
        fn ($mail) => str_contains($mail->subjectLine, 'بدأنا تنفيذ متجرك') && $mail->summaryOf !== null
    );
});

it('يضيف ملاحظات متعدّدة ويحذفها ويحفظها مرقّمة', function () {
    Mail::fake();
    $this->actingAs(adminUser());

    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'عميل', 'phone' => '—',
        'status' => 'new', 'source' => 'quote_form', 'payload' => [],
    ]);

    $page = Livewire\Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->set('draft.items.0.name', 'تجهيز المتجر')
        ->set('draft.items.0.price', 20000);

    $page->call('addNote')->call('addNote')->call('addNote');
    expect($page->get('draft.notes'))->toHaveCount(3);

    $page->set('draft.notes.0', 'الملاحظة الأولى')
        ->set('draft.notes.1', 'الملاحظة الثانية')
        ->set('draft.notes.2', 'الملاحظة الثالثة')
        ->call('removeNote', 1)
        ->call('issueQuote', false);

    // الملاحظة المحذوفة تختفي ويبقى الترتيب سليماً
    expect(QuoteController::quoteOf($sr->fresh())['notes'])
        ->toBe(['الملاحظة الأولى', 'الملاحظة الثالثة']);

    Livewire\Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->assertSet('draft.notes.1', 'الملاحظة الثالثة');
});

it('يحسب خصم الباقات الاختيارية وضريبتها بمعزل عن المستحق', function () {
    Mail::fake();
    $this->actingAs(adminUser());

    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'عميل', 'phone' => '—',
        'status' => 'new', 'source' => 'quote_form', 'payload' => [],
    ]);

    $page = Livewire\Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->set('draft.items', [['phase' => '', 'name' => 'تجهيز المتجر', 'desc' => '', 'note' => '',
            'qty' => 1, 'unit' => '', 'price' => 20000, 'free' => false]])
        ->set('draft.discount_percent', 0)
        ->set('draft.vat_percent', 10);

    // ضريبة الباقات تُملأ افتراضياً من ضريبة العرض عند أول فتح
    expect((float) $page->get('draft.extras_vat_percent'))
        ->toBe((float) QuoteController::DEFAULT_VAT_PERCENT);

    $page->call('addExtra')
        ->set('draft.extras.0.name', 'تطبيق جوال')
        ->set('draft.extras.0.price', 40000)
        ->set('draft.extras_discount_percent', 25)
        ->set('draft.extras_vat_percent', 14);

    $totals = $page->get('draftTotals');
    expect($totals['extras_subtotal'])->toBe(40000.0)
        ->and($totals['extras_discount'])->toBe(10000.0)
        ->and($totals['extras_vat'])->toBe(4200.0)
        ->and($totals['extras_total'])->toBe(34200.0)
        ->and($totals['total'])->toBe(22000.0);

    $page->call('issueQuote', false);

    $quote = QuoteController::quoteOf($sr->fresh());
    expect($quote['extras_total'])->toBe(34200.0)
        ->and($quote['total'])->toBe(22000.0);
});

it('لا يرسل بريد المرحلة لطلب بلا بريد إلكتروني', function () {
    Mail::fake();
    $this->actingAs(adminUser());

    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'عميل', 'phone' => '—', 'email' => null,
        'status' => 'new', 'source' => 'quote_form', 'payload' => [],
    ]);

    Livewire\Livewire::test(QuoteRequests::class)->call('markDelivered', $sr->id);

    Mail::assertNotSent(StageMessage::class);
    expect(QuoteController::flowOf($sr->fresh())['stage'])->toBe('delivered');
});
