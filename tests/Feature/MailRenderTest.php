<?php

use App\Mail\QuoteProposalIssued;
use App\Mail\ServiceRequestConfirmation;
use App\Mail\ServiceRequestReceived;
use App\Mail\StoreOrderReceived;
use App\Models\ServiceRequest;
use App\Models\StoreOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;

// قالب البريد يقرأ بيانات الشركة من الإعدادات، فيحتاج قاعدة بيانات ولو كان النموذج غير محفوظ
uses(RefreshDatabase::class);

it('renders service request admin + confirmation emails without errors', function () {
    $sr = new ServiceRequest([
        'name' => 'عميل تجربة',
        'phone' => '01055789056',
        'email' => 'test@example.com',
        'service_type' => 'ecommerce',
        'company' => 'شركة وريد',
        'message' => 'أريد إنشاء متجر إلكتروني',
        'payload' => ['business_type' => 'ملابس وأزياء'],
    ]);

    expect((new ServiceRequestReceived($sr))->render())->toContain('طلب جديد');
    expect((new ServiceRequestConfirmation($sr))->render())->toContain('عميل تجربة');
});

it('embeds a hidden tracking pixel in the issued quote email', function () {
    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'عميل تجربة', 'phone' => '01000000000',
        'email' => 'test@example.com', 'status' => 'proposal', 'source' => 'quote_form',
        'payload' => ['_quote' => [
            'items' => [['name' => 'تجهيز المتجر', 'qty' => 1, 'price' => 20000]],
            'vat_percent' => 0, 'currency' => 'ج.م', 'valid_days' => 30,
            'issued_at' => now()->toIso8601String(),
        ]],
    ]);

    $html = (new QuoteProposalIssued($sr))->render();

    expect($html)->toContain('width="1" height="1"')
        ->and($html)->toContain('/quote/track/'.$sr->id);
});

it('renders store order email without errors', function () {
    $order = new StoreOrder([
        'order_number' => 'WRD-TEST01',
        'customer_name' => 'مشترٍ تجريبي',
        'customer_phone' => '01000000000',
        'shipping_address' => 'شارع التجربة',
        'items' => [['name' => 'منتج', 'quantity' => 2, 'line_total' => 90000]],
        'total' => 90000,
        'payment_method' => 'cod',
    ]);

    expect((new StoreOrderReceived($order))->render())->toContain('WRD-TEST01');
});
