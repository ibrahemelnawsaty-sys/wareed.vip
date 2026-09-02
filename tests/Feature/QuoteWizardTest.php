<?php

use App\Http\Controllers\QuoteController;
use App\Mail\ServiceRequestReceived;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

/** إجابات صالحة لأسئلة طبيعة المتجر المشتركة بين الوضعين */
function validQuoteAnswers(): array
{
    return [
        'store_status' => 'مشروع جديد أبدؤه من الصفر',
        'store_field' => 'عطور ومستحضرات تجميل',
        'products_count' => 'من 50 إلى 200 منتج',
        'branding' => 'أحتاج تصميم هوية كاملة من وريد',
        'features' => ['تجهيز المتجر ورفع المنتجات', 'ربط بوابات الدفع الإلكتروني'],
        'budget' => 'أفضّل أن يقترح فريق وريد الأنسب',
        'launch_time' => 'بأسرع وقت ممكن',
        'notes' => 'أرغب في تصميم أنيق وبسيط.',
    ];
}

function submitInvite(array $overrides = []): TestResponse
{
    return test()->postJson('/quote/hajar-salama', array_merge(validQuoteAnswers(), $overrides));
}

it('shows the personalized form without contact questions', function () {
    $this->get('/quote/hajar-salama')
        ->assertOk()
        ->assertSee('هاجر سلامة')
        ->assertSee('نموذج مخصّص')
        // بيانات العميلة معروفة مسبقاً فلا تُطلب في النموذج
        ->assertDontSee('ما الاسم الكريم؟')
        ->assertDontSee('ما البريد الإلكتروني؟')
        ->assertDontSee('ما اسم المتجر أو المشروع؟')
        ->assertDontSee('ما رقم الجوال المناسب للتواصل؟');
});

it('uses the formal icon system instead of emoji', function () {
    $html = $this->get('/quote/hajar-salama')->assertOk()->getContent();

    expect($html)->toContain('#i-storefront')
        ->and($html)->toContain('<symbol id="i-cart"')
        // لا إيموجي في الواجهة
        ->and(preg_match('/[\x{1F300}-\x{1FAFF}\x{2700}-\x{27BF}\x{2B00}-\x{2BFF}]/u', $html))->toBe(0);
});

it('redirects the short personal link to the personalized form', function () {
    $this->get('/hajar-salama')->assertRedirect('/quote/hajar-salama');
});

it('returns 404 for unknown invite links', function () {
    $this->get('/quote/unknown-client')->assertNotFound();
});

it('shows the generic form with name, email and store name questions', function () {
    $this->get('/quote')
        ->assertOk()
        ->assertSee('ما الاسم الكريم؟')
        ->assertSee('ما البريد الإلكتروني؟')
        ->assertSee('ما اسم المتجر أو المشروع؟');
});

it('stores a personalized submission and returns an official reference', function () {
    Mail::fake();
    $service = Service::create(['key' => 'ecommerce', 'slug' => 'ecommerce', 'name' => ['ar' => 'المتاجر الإلكترونية']]);

    $response = submitInvite()->assertOk()->assertJson(['ok' => true]);

    $sr = ServiceRequest::sole();
    expect($sr->name)->toBe('أ. هاجر سلامة')
        ->and($sr->service_id)->toBe($service->id)
        ->and($sr->source)->toBe('quote_link:hajar-salama')
        ->and($sr->payload['مجال المتجر'])->toBe('عطور ومستحضرات تجميل')
        ->and($response->json('reference'))->toBe($sr->reference)
        ->and($response->json('documentUrl'))->toContain('/quote/hajar-salama/document');

    Mail::assertSent(ServiceRequestReceived::class);
});

it('formats the reference as WRD-YYYY-MM-DD-NNNNN', function () {
    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'عميل', 'phone' => '—', 'status' => 'new', 'source' => 'quote_form',
    ]);

    expect($sr->reference)
        ->toMatch('/^WRD-\d{4}-\d{2}-\d{2}-\d{5}$/')
        ->toBe('WRD-'.$sr->created_at->format('Y-m-d').'-'.str_pad((string) ($sr->id + 100), 5, '0', STR_PAD_LEFT));
});

it('accepts only one submission per personalized link', function () {
    Mail::fake();

    submitInvite()->assertOk();

    submitInvite()
        ->assertStatus(409)
        ->assertJson(['ok' => false, 'duplicate' => true])
        ->assertJsonPath('reference', ServiceRequest::sole()->reference);

    expect(ServiceRequest::count())->toBe(1);
});

it('shows the status page with the countdown once a request exists', function () {
    Mail::fake();
    submitInvite()->assertOk();
    $sr = ServiceRequest::sole();

    $this->get('/quote/hajar-salama')
        ->assertOk()
        ->assertSee('تم استلام طلبك')
        ->assertSee($sr->reference)
        ->assertSee('data-deadline', false)
        // النموذج نفسه لم يعد يُعرض
        ->assertDontSee('ما وضع المتجر حالياً؟');
});

it('renders the official document with the reference and a QR code', function () {
    Mail::fake();
    submitInvite()->assertOk();
    $sr = ServiceRequest::sole();

    $this->get('/quote/hajar-salama/document')
        ->assertOk()
        ->assertSee('طلب متجر إلكتروني')
        ->assertSee('E-COMMERCE STORE REQUEST')
        ->assertSee('نسخة العميل')
        ->assertSee('مقدَّم إلى')
        ->assertSee('أ. هاجر سلامة')
        ->assertSee($sr->reference)
        ->assertSee('عطور ومستحضرات تجميل')
        ->assertSee('<svg', false);
});

it('returns 404 for a document with no request yet', function () {
    $this->get('/quote/hajar-salama/document')->assertNotFound();
});

it('serves the generic document only through a signed url', function () {
    Mail::fake();
    $this->postJson('/quote', array_merge(validQuoteAnswers(), [
        'name' => 'محمد أحمد', 'email' => 'client@example.com', 'store_name' => 'متجر لمسة',
    ]))->assertOk();

    $sr = ServiceRequest::sole();
    $this->get(URL::signedRoute('quote.document.signed', ['serviceRequest' => $sr->id]))
        ->assertOk()
        ->assertSee('متجر لمسة');

    $this->get('/quote/document/'.$sr->id)->assertForbidden();
});

it('stores a generic submission with the visitor contact details', function () {
    Mail::fake();

    $this->postJson('/quote', array_merge(validQuoteAnswers(), [
        'name' => 'محمد أحمد',
        'email' => 'client@example.com',
        'store_name' => 'متجر لمسة',
        'store_field' => 'أخرى',
        'store_field_other' => 'مستلزمات أطفال',
    ]))->assertOk();

    $sr = ServiceRequest::sole();
    expect($sr->name)->toBe('محمد أحمد')
        ->and($sr->email)->toBe('client@example.com')
        ->and($sr->company)->toBe('متجر لمسة')
        ->and($sr->source)->toBe('quote_form')
        ->and($sr->payload['مجال المتجر'])->toBe('أخرى: مستلزمات أطفال');
});

it('allows several submissions on the generic form', function () {
    Mail::fake();
    $extra = ['name' => 'محمد', 'email' => 'a@example.com'];

    $this->postJson('/quote', array_merge(validQuoteAnswers(), $extra))->assertOk();
    $this->postJson('/quote', array_merge(validQuoteAnswers(), $extra))->assertOk();

    expect(ServiceRequest::count())->toBe(2);
});

it('requires name and email on the generic form only', function () {
    $this->postJson('/quote', validQuoteAnswers())
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email']);
});

it('validates the store questions', function () {
    $this->postJson('/quote/hajar-salama', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['store_status', 'store_field', 'products_count', 'branding', 'features', 'budget', 'launch_time']);
});

it('rejects answers outside the allowed options', function () {
    submitInvite(['budget' => 'مليون دولار'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['budget']);
});

it('silently ignores honeypot submissions without storing anything', function () {
    Mail::fake();

    submitInvite(['website' => 'spam-bot'])->assertOk()->assertJson(['ok' => true]);

    expect(ServiceRequest::count())->toBe(0);
    Mail::assertNothingSent();
});

it('reopens the personalized link after the request is deleted', function () {
    Mail::fake();
    submitInvite()->assertOk();

    $this->artisan('quote:reset', ['invite' => 'hajar-salama', '--force' => true])->assertSuccessful();

    expect(ServiceRequest::count())->toBe(0);
    $this->get('/quote/hajar-salama')->assertOk()->assertSee('ما وضع المتجر حالياً؟');
});

it('carries the invited client contact details into the request and document', function () {
    Mail::fake();
    submitInvite()->assertOk();

    $sr = ServiceRequest::sole();
    expect($sr->phone)->toBe('00201016031031')
        ->and($sr->email)->toBe('hagersalma89@gmail.com')
        ->and($sr->company)->toBe('متجر حواديت');

    $this->get('/quote/hajar-salama/document')
        ->assertOk()
        ->assertSee('00201016031031')
        ->assertSee('hagersalma89@gmail.com')
        ->assertSee('متجر حواديت');
});

it('normalises phone numbers for whatsapp links', function () {
    expect(QuoteController::waNumber('00201016031031'))->toBe('201016031031')
        ->and(QuoteController::waNumber('+20 101 603 1031'))->toBe('201016031031')
        ->and(QuoteController::waNumber('—'))->toBeNull()
        ->and(QuoteController::waNumber(null))->toBeNull();
});

it('welcomes the invited client with her name and store name', function () {
    $this->get('/quote/hajar-salama')
        ->assertOk()
        ->assertSee('أ. هاجر سلامة')
        ->assertSee('متجر حواديت');
});

it('promises three business days and skips the weekend', function () {
    // الأحد 2026-08-30 + 3 أيام عمل = الأربعاء 2026-09-02
    expect(QuoteController::deadlineFor(Carbon::parse('2026-08-30 10:00'))->toDateString())
        ->toBe('2026-09-02');

    // الأربعاء 2026-09-02: يتخطّى الجمعة والسبت فينتهي الاثنين 2026-09-07
    expect(QuoteController::deadlineFor(Carbon::parse('2026-09-02 10:00'))->toDateString())
        ->toBe('2026-09-07');

    $this->get('/quote/hajar-salama')->assertOk()->assertSee('أيام عمل');
});

it('renders a scannable QR with dark modules only', function () {
    Mail::fake();
    submitInvite()->assertOk();

    $html = $this->get('/quote/hajar-salama/document')->assertOk()->getContent();

    // الوحدات الفاتحة لا تُرسم إطلاقاً، وإلا ظهر الرمز ككتلة سوداء
    expect($html)->toContain('qr-data-dark')
        ->and($html)->not->toContain('qr-data light');
});

it('shows the client contact details in the document with the mobile label', function () {
    Mail::fake();
    submitInvite()->assertOk();

    $this->get('/quote/hajar-salama/document')
        ->assertOk()
        ->assertSee('رقم الموبايل')
        ->assertDontSee('رقم الجوال')
        ->assertSee('00201016031031')
        ->assertSee('hagersalma89@gmail.com')
        ->assertSee('متجر حواديت');
});

it('falls back to the invite details for older requests with no contact stored', function () {
    Mail::fake();
    // سجل قديم أُنشئ قبل إضافة بيانات العميلة
    ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'أ. هاجر سلامة', 'phone' => '—',
        'status' => 'new', 'source' => 'quote_link:hajar-salama',
        'payload' => ['مجال المتجر' => 'هدايا وإكسسوارات'],
    ]);

    $this->get('/quote/hajar-salama/document')
        ->assertOk()
        ->assertSee('00201016031031')
        ->assertSee('متجر حواديت');
});

it('has no quote until one is issued', function () {
    Mail::fake();
    submitInvite()->assertOk();

    expect(QuoteController::quoteOf(ServiceRequest::sole()))->toBeNull();
    $this->get('/quote/hajar-salama/proposal')->assertNotFound();
});

it('computes quote totals with a percentage discount and vat', function () {
    Mail::fake();
    submitInvite()->assertOk();
    $sr = ServiceRequest::sole();

    $sr->update(['payload' => array_merge((array) $sr->payload, ['_quote' => [
        'items' => [
            ['name' => 'تجهيز المتجر', 'qty' => 1, 'price' => 20000],
            ['name' => 'تصوير المنتجات', 'qty' => 3, 'price' => 1000],
        ],
        'discount_percent' => 10, 'vat_percent' => 14, 'currency' => 'ج.م',
        'valid_days' => 30, 'issued_at' => now()->toIso8601String(),
    ]])]);

    $quote = QuoteController::quoteOf($sr->fresh());

    // 10% من 23,000 = 2,300 ← الوعاء الضريبي 20,700 ← ضريبة 2,898
    expect($quote['subtotal'])->toBe(23000.0)
        ->and($quote['discount_percent'])->toBe(10.0)
        ->and($quote['discount'])->toBe(2300.0)
        ->and($quote['vat'])->toBe(2898.0)
        ->and($quote['total'])->toBe(23598.0);

    $this->get('/quote/hajar-salama/proposal')->assertOk()->assertSee('الخصم (10%)');
});

it('keeps quotes issued before the percentage discount working', function () {
    Mail::fake();
    submitInvite()->assertOk();
    $sr = ServiceRequest::sole();

    $sr->update(['payload' => array_merge((array) $sr->payload, ['_quote' => [
        'items' => [
            ['name' => 'تجهيز المتجر', 'qty' => 1, 'price' => 20000],
            ['name' => 'تصوير المنتجات', 'qty' => 3, 'price' => 1000],
        ],
        'discount' => 3000, 'vat_percent' => 14, 'currency' => 'ج.م',
        'valid_days' => 30, 'issued_at' => now()->toIso8601String(),
    ]])]);

    $quote = QuoteController::quoteOf($sr->fresh());

    // العرض القديم يحمل قيمة خصم مباشرة — تبقى كما هي وتُشتقّ نسبتها للعرض فقط
    expect($quote['subtotal'])->toBe(23000.0)
        ->and($quote['discount'])->toBe(3000.0)
        ->and($quote['discount_percent'])->toBe(13.04)
        ->and($quote['vat'])->toBe(2800.0)
        ->and($quote['total'])->toBe(22800.0)
        ->and($quote['items'][1]['total'])->toBe(3000.0);
});

it('hides internal payload keys from the request details', function () {
    Mail::fake();
    submitInvite()->assertOk();
    $sr = ServiceRequest::sole();
    $sr->update(['payload' => array_merge((array) $sr->payload, ['_quote' => [
        'items' => [['name' => 'بند داخلي لا يظهر', 'qty' => 1, 'price' => 100]],
        'issued_at' => now()->toIso8601String(),
    ]])]);

    $this->get('/quote/hajar-salama/document')
        ->assertOk()
        ->assertDontSee('بند داخلي لا يظهر');
});

it('shows the issued quote to the client and serves the proposal document', function () {
    Mail::fake();
    submitInvite()->assertOk();
    $sr = ServiceRequest::sole();
    $sr->update(['status' => 'proposal', 'payload' => array_merge((array) $sr->payload, [
        '_quote' => [
            'items' => [['name' => 'تجهيز المتجر ورفع المنتجات', 'desc' => 'حتى 200 منتج', 'qty' => 1, 'price' => 18000]],
            'discount' => 0, 'vat_percent' => 0, 'currency' => 'ج.م',
            'valid_days' => 30, 'timeline' => '3 أسابيع', 'issued_at' => now()->toIso8601String(),
        ],
        // إصدار العرض ينقل المسار إلى مرحلة اعتماد العميل
        '_flow' => ['stage' => 'awaiting_approval'],
    ])]);

    // صفحة العميلة تعرض العرض بدل العدّاد
    $this->get('/quote/hajar-salama')
        ->assertOk()
        ->assertSee('عرض سعر متجرك جاهز')
        ->assertSee('18,000');

    $this->get('/quote/hajar-salama/proposal')
        ->assertOk()
        ->assertSee('عرض سعر')
        ->assertSee('PRICE QUOTATION')
        ->assertSee('تجهيز المتجر ورفع المنتجات')
        ->assertSee('حتى 200 منتج')
        ->assertSee('3 أسابيع')
        ->assertSee($sr->reference);
});

it('shows the awaiting-meeting stage first with no countdown', function () {
    Mail::fake();
    submitInvite()->assertOk();

    $this->get('/quote/hajar-salama')
        ->assertOk()
        ->assertSee('تحديد موعد الاجتماع')
        ->assertSee('بانتظار تحديد موعد اجتماع')
        // العدّاد لا يعمل قبل الاجتماع
        ->assertSee('data-counting="0"', false);
});

it('runs the countdown after the meeting and again during execution', function () {
    Mail::fake();
    submitInvite()->assertOk();
    $sr = ServiceRequest::sole();

    $setFlow = function (array $flow) use ($sr) {
        $sr->update(['payload' => array_merge((array) $sr->payload, ['_flow' => $flow])]);
    };

    // بعد الاجتماع: العدّاد يعمل حتى تسليم العرض
    $setFlow(['stage' => 'quote_due', 'meeting_done_at' => now()->toIso8601String()]);
    $this->get('/quote/hajar-salama')
        ->assertOk()
        ->assertSee('الوقت المتبقي لتسليم عرض السعر')
        ->assertSee('data-counting="1"', false);

    // بانتظار الاعتماد: العدّاد متوقّف
    $setFlow(['stage' => 'awaiting_approval']);
    $this->get('/quote/hajar-salama')->assertOk()->assertSee('data-counting="0"', false);

    // أثناء التنفيذ: العدّاد يعمل حتى موعد التسليم
    $setFlow([
        'stage' => 'in_progress',
        'started_at' => now()->toIso8601String(),
        'due_at' => now()->addDays(20)->toIso8601String(),
    ]);
    $this->get('/quote/hajar-salama')
        ->assertOk()
        ->assertSee('الوقت المتبقي لتسليم المتجر')
        ->assertSee('data-counting="1"', false);

    // بعد التسليم: يتوقّف العدّاد نهائياً
    $setFlow(['stage' => 'delivered', 'delivered_at' => now()->toIso8601String()]);
    $this->get('/quote/hajar-salama')
        ->assertOk()
        ->assertSee('تم تسليم متجرك')
        ->assertSee('data-counting="0"', false);
});

it('shows the scheduled meeting date to the client', function () {
    Mail::fake();
    submitInvite()->assertOk();
    $sr = ServiceRequest::sole();
    $sr->update(['payload' => array_merge((array) $sr->payload, ['_flow' => [
        'stage' => 'meeting_scheduled',
        'meeting_at' => Carbon::parse('2026-09-01 11:00')->toIso8601String(),
    ]])]);

    $this->get('/quote/hajar-salama')
        ->assertOk()
        ->assertSee('موعد الاجتماع التعريفي')
        ->assertSee('1 سبتمبر 2026')
        ->assertSee('data-counting="0"', false);
});

it('groups quote items into named phases with per-phase totals', function () {
    Mail::fake();
    submitInvite()->assertOk();
    $sr = ServiceRequest::sole();

    $sr->update(['payload' => array_merge((array) $sr->payload, [
        '_flow' => ['stage' => 'awaiting_approval'],
        '_quote' => [
            'items' => [
                ['phase' => 'المرحلة الأولى', 'name' => 'تجهيز المتجر', 'qty' => 1, 'price' => 15000],
                ['phase' => 'المرحلة الأولى', 'name' => 'رفع المنتجات', 'qty' => 1, 'price' => 3000],
                ['phase' => 'المرحلة الثانية', 'name' => 'تسويق وإعلانات', 'qty' => 2, 'price' => 2500],
            ],
            'discount' => 0, 'vat_percent' => 0, 'currency' => 'ج.م',
            'valid_days' => 30, 'issued_at' => now()->toIso8601String(),
        ],
    ])]);

    $quote = QuoteController::quoteOf($sr->fresh());

    expect($quote['has_phases'])->toBeTrue()
        ->and($quote['phases'])->toHaveCount(2)
        ->and($quote['phases'][0]['name'])->toBe('المرحلة الأولى')
        ->and($quote['phases'][0]['total'])->toBe(18000.0)
        ->and($quote['phases'][1]['total'])->toBe(5000.0)
        ->and($quote['subtotal'])->toBe(23000.0);

    $this->get('/quote/hajar-salama/proposal')
        ->assertOk()
        ->assertSee('المرحلة الأولى')
        ->assertSee('المرحلة الثانية');
});

it('renders free items as مجاناً and excludes them from the total', function () {
    Mail::fake();
    submitInvite()->assertOk();
    $sr = ServiceRequest::sole();

    $sr->update(['payload' => array_merge((array) $sr->payload, [
        '_flow' => ['stage' => 'awaiting_approval'],
        '_quote' => [
            'items' => [
                ['name' => 'تجهيز المتجر', 'qty' => 1, 'price' => 20000],
                ['name' => 'استضافة السنة الأولى', 'qty' => 1, 'price' => 4000, 'free' => true],
                ['name' => 'تدريب على اللوحة', 'qty' => 1, 'price' => 0],
            ],
            'discount' => 0, 'vat_percent' => 0, 'currency' => 'ج.م',
            'valid_days' => 30, 'issued_at' => now()->toIso8601String(),
        ],
    ])]);

    $quote = QuoteController::quoteOf($sr->fresh());

    expect($quote['items'][1]['free'])->toBeTrue()
        ->and($quote['items'][1]['total'])->toBe(0.0)
        // السعر صفر يُعدّ مجانياً حتى دون تعليم الخانة
        ->and($quote['items'][2]['free'])->toBeTrue()
        ->and($quote['total'])->toBe(20000.0);

    $this->get('/quote/hajar-salama/proposal')
        ->assertOk()
        ->assertSee('مجاناً')
        ->assertSee('استضافة السنة الأولى');
});

it('shows the item note as a badge in the proposal', function () {
    Mail::fake();
    submitInvite()->assertOk();
    $sr = ServiceRequest::sole();

    $sr->update(['payload' => array_merge((array) $sr->payload, [
        '_flow' => ['stage' => 'awaiting_approval'],
        '_quote' => [
            'items' => [['name' => 'استضافة وسيرفر', 'note' => 'اشتراك سنوي', 'qty' => 1, 'price' => 6000]],
            'discount' => 0, 'vat_percent' => 0, 'currency' => 'ج.م',
            'valid_days' => 30, 'issued_at' => now()->toIso8601String(),
        ],
    ])]);

    expect(QuoteController::quoteOf($sr->fresh())['items'][0]['note'])->toBe('اشتراك سنوي');

    $this->get('/quote/hajar-salama/proposal')->assertOk()->assertSee('اشتراك سنوي');
});

it('shows the unit next to the quantity in the proposal', function () {
    Mail::fake();
    submitInvite()->assertOk();
    $sr = ServiceRequest::sole();

    $sr->update(['payload' => array_merge((array) $sr->payload, [
        '_flow' => ['stage' => 'awaiting_approval'],
        '_quote' => [
            'items' => [
                ['name' => 'استضافة وسيرفر', 'qty' => 12, 'unit' => 'شهر', 'price' => 500],
                ['name' => 'تجهيز المتجر', 'qty' => 1, 'price' => 18000],
            ],
            'discount' => 0, 'vat_percent' => 0, 'currency' => 'ج.م',
            'valid_days' => 30, 'issued_at' => now()->toIso8601String(),
        ],
    ])]);

    $items = QuoteController::quoteOf($sr->fresh())['items'];
    expect($items[0]['unit'])->toBe('شهر')
        // بند بلا وحدة يبقى بالكمية وحدها
        ->and($items[1]['unit'])->toBe('');

    $this->get('/quote/hajar-salama/proposal')
        ->assertOk()
        ->assertSee('<small>شهر</small>', false);
});

it('prints the legal identifiers on the proposal header', function () {
    Mail::fake();
    Setting::set('tax_number', '774-094-117', 'legal');
    Setting::set('commercial_register', '295283', 'legal');
    Setting::set('legal_name', 'وريد لتقنية المعلومات', 'legal');

    submitInvite()->assertOk();
    $sr = ServiceRequest::sole();
    $sr->update(['payload' => array_merge((array) $sr->payload, ['_quote' => [
        'items' => [['name' => 'تجهيز المتجر', 'qty' => 1, 'price' => 10000]],
        'valid_days' => 30, 'issued_at' => now()->toIso8601String(),
    ]])]);

    $this->get('/quote/hajar-salama/proposal')
        ->assertOk()
        ->assertSee('الرقم الضريبي')
        ->assertSee('774-094-117')
        ->assertSee('السجل التجاري')
        ->assertSee('295283')
        ->assertSee('وريد لتقنية المعلومات');
});

it('computes payment amounts as a percentage of the total', function () {
    Mail::fake();
    submitInvite()->assertOk();
    $sr = ServiceRequest::sole();

    $sr->update(['payload' => array_merge((array) $sr->payload, [
        '_flow' => ['stage' => 'awaiting_approval'],
        '_quote' => [
            'items' => [['name' => 'تجهيز المتجر', 'qty' => 1, 'price' => 20000]],
            'discount' => 0, 'vat_percent' => 14, 'currency' => 'ج.م', 'valid_days' => 30,
            'payments' => [
                ['label' => 'دفعة مقدّمة', 'percent' => 50],
                ['label' => 'دفعة عند التسليم', 'note' => 'قبل رفع المتجر', 'percent' => 50],
                // دفعة بلا اسم أو بنسبة صفر تُستبعد
                ['label' => '', 'percent' => 30],
                ['label' => 'بلا نسبة', 'percent' => 0],
            ],
            'issued_at' => now()->toIso8601String(),
        ],
    ])]);

    $quote = QuoteController::quoteOf($sr->fresh());

    expect($quote['total'])->toBe(22800.0)
        ->and($quote['payments'])->toHaveCount(2)
        ->and($quote['payments'][0]['amount'])->toBe(11400.0)
        ->and($quote['payments'][1]['amount'])->toBe(11400.0)
        ->and($quote['payments'][1]['note'])->toBe('قبل رفع المتجر')
        ->and($quote['payments_percent'])->toBe(100.0);

    $this->get('/quote/hajar-salama/proposal')
        ->assertOk()
        ->assertSee('الدفعات وطريقة السداد')
        ->assertSee('دفعة مقدّمة')
        ->assertSee('11,400');
});

it('prints the bank transfer details when configured', function () {
    Mail::fake();
    Setting::set('bank_name', 'بنك مصر', 'legal');
    Setting::set('bank_account_name', 'وريد لتقنية المعلومات', 'legal');
    Setting::set('bank_account_number', '1234567890123', 'legal');
    Setting::set('bank_iban', 'EG380003000123456789012345', 'legal');

    submitInvite()->assertOk();
    $sr = ServiceRequest::sole();
    $sr->update(['payload' => array_merge((array) $sr->payload, ['_quote' => [
        'items' => [['name' => 'تجهيز المتجر', 'qty' => 1, 'price' => 10000]],
        'valid_days' => 30, 'issued_at' => now()->toIso8601String(),
    ]])]);

    $this->get('/quote/hajar-salama/proposal')
        ->assertOk()
        ->assertSee('بيانات التحويل البنكي')
        ->assertSee('بنك مصر')
        ->assertSee('1234567890123')
        ->assertSee('EG380003000123456789012345');
});

it('hides the payments section when nothing is configured', function () {
    Mail::fake();
    submitInvite()->assertOk();
    $sr = ServiceRequest::sole();
    $sr->update(['payload' => array_merge((array) $sr->payload, ['_quote' => [
        'items' => [['name' => 'تجهيز المتجر', 'qty' => 1, 'price' => 10000]],
        'valid_days' => 30, 'issued_at' => now()->toIso8601String(),
    ]])]);

    $this->get('/quote/hajar-salama/proposal')
        ->assertOk()
        ->assertDontSee('الدفعات وطريقة السداد')
        ->assertDontSee('بيانات التحويل البنكي');
});
