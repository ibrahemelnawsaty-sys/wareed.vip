<?php

use App\Mail\ServiceRequestReceived;
use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

/** إجابات صالحة لأسئلة طبيعة المتجر المشتركة بين الوضعين */
function validQuoteAnswers(): array
{
    return [
        'phone' => '01055789056',
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

it('shows the personalized form for a known invite link', function () {
    $this->get('/quote/hajar-salama')
        ->assertOk()
        ->assertSee('هاجر سلامة')
        ->assertSee('نموذج مخصّص')
        // في الرابط المخصّص لا تُطلب بيانات معروفة مسبقاً
        ->assertDontSee('ما الاسم الكريم؟')
        ->assertDontSee('ما البريد الإلكتروني؟')
        ->assertDontSee('ما اسم المتجر أو المشروع؟');
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

it('stores a personalized submission with the invited client name and no email', function () {
    Mail::fake();
    $service = Service::create(['key' => 'ecommerce', 'slug' => 'ecommerce', 'name' => ['ar' => 'المتاجر الإلكترونية']]);

    $this->postJson('/quote/hajar-salama', validQuoteAnswers())
        ->assertOk()
        ->assertJson(['ok' => true]);

    $sr = ServiceRequest::sole();
    expect($sr->name)->toBe('أ. هاجر سلامة')
        ->and($sr->email)->toBeNull()
        ->and($sr->service_type)->toBe('ecommerce')
        ->and($sr->service_id)->toBe($service->id)
        ->and($sr->source)->toBe('quote_link:hajar-salama')
        ->and($sr->budget)->toBe('أفضّل أن يقترح فريق وريد الأنسب')
        ->and($sr->message)->toBe('أرغب في تصميم أنيق وبسيط.')
        ->and($sr->payload['مجال المتجر'])->toBe('عطور ومستحضرات تجميل')
        ->and($sr->payload['الخدمات المطلوبة'])->toHaveCount(2);

    Mail::assertSent(ServiceRequestReceived::class);
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

it('requires name and email on the generic form only', function () {
    $this->postJson('/quote', validQuoteAnswers())
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email']);
});

it('validates the store questions', function () {
    $this->postJson('/quote/hajar-salama', ['phone' => '01055789056'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['store_status', 'store_field', 'products_count', 'branding', 'features', 'budget', 'launch_time']);
});

it('rejects answers outside the allowed options', function () {
    $this->postJson('/quote/hajar-salama', array_merge(validQuoteAnswers(), ['budget' => 'مليون دولار']))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['budget']);
});

it('rejects phone numbers without enough digits', function () {
    $this->postJson('/quote/hajar-salama', array_merge(validQuoteAnswers(), ['phone' => '123']))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['phone']);
});

it('silently ignores honeypot submissions without storing anything', function () {
    Mail::fake();

    $this->postJson('/quote/hajar-salama', validQuoteAnswers() + ['website' => 'spam-bot'])
        ->assertOk()
        ->assertJson(['ok' => true]);

    expect(ServiceRequest::count())->toBe(0);
    Mail::assertNothingSent();
});
