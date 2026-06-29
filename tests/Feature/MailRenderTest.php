<?php

use App\Mail\ServiceRequestConfirmation;
use App\Mail\ServiceRequestReceived;
use App\Mail\StoreOrderReceived;
use App\Models\ServiceRequest;
use App\Models\StoreOrder;

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
