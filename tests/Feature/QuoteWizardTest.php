<?php

use App\Http\Controllers\QuoteController;
use App\Mail\ServiceRequestReceived;
use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
