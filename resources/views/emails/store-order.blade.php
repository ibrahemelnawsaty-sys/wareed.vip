@extends('emails.layout')

@section('content')
@php
    $rowLabel = 'style="padding:9px 12px;background:#f4f6fb;color:#65718f;font-size:13px;width:38%;border-bottom:1px solid #eef2fb;"';
    $rowVal = 'style="padding:9px 12px;color:#1a2547;font-size:14px;font-weight:600;border-bottom:1px solid #eef2fb;"';
    $wa = preg_replace('/[^0-9]/', '', (string) $order->customer_phone);
@endphp

<h2 style="margin:0 0 6px;font-size:20px;color:#1a2547;">🛒 طلب جديد في متجر {{ $order->store?->name }}</h2>
<p style="margin:0 0 18px;color:#65718f;font-size:14px;">رقم الطلب: <strong dir="ltr">{{ $order->order_number }}</strong></p>

<table style="width:100%;border-collapse:collapse;border:1px solid #eef2fb;border-radius:10px;overflow:hidden;">
    <tr><td {!! $rowLabel !!}>العميل</td><td {!! $rowVal !!}>{{ $order->customer_name }}</td></tr>
    <tr><td {!! $rowLabel !!}>الموبايل</td><td {!! $rowVal !!}><span dir="ltr">{{ $order->customer_phone }}</span></td></tr>
    @if($order->governorate)<tr><td {!! $rowLabel !!}>المنطقة</td><td {!! $rowVal !!}>{{ $order->governorate }}</td></tr>@endif
    <tr><td {!! $rowLabel !!}>العنوان</td><td {!! $rowVal !!}>{{ $order->shipping_address }}</td></tr>
    <tr><td {!! $rowLabel !!}>طريقة الدفع</td><td {!! $rowVal !!}>{{ $order->payment_method === 'cod' ? 'الدفع عند الاستلام' : 'إلكتروني' }}</td></tr>
    <tr><td {!! $rowLabel !!}>الإجمالي</td><td {!! $rowVal !!}>{{ money($order->total) }}</td></tr>
</table>

<div style="margin-top:16px;">
    <div style="color:#65718f;font-size:13px;margin-bottom:8px;">المنتجات</div>
    @foreach((array) $order->items as $item)
        <div style="display:flex;justify-content:space-between;padding:8px 12px;background:#f4f6fb;border-radius:8px;margin-bottom:6px;font-size:14px;">
            <span style="color:#1a2547;">{{ $item['name'] ?? '' }} × {{ $item['quantity'] ?? 1 }}</span>
            <span style="color:#3b82f6;font-weight:600;">{{ money($item['line_total'] ?? ($item['price'] ?? 0)) }}</span>
        </div>
    @endforeach
</div>

<div style="margin-top:20px;text-align:center;">
    <a href="https://wa.me/{{ $wa }}" style="display:inline-block;background:#25d366;color:#fff;text-decoration:none;font-weight:bold;padding:11px 22px;border-radius:10px;font-size:14px;">💬 تأكيد الطلب مع العميل</a>
</div>
@endsection
