@extends('emails.layout')

@section('content')
@php
    $labels = [
        'ecommerce' => 'المتاجر الإلكترونية',
        'tech_solution' => 'الحلول التقنية',
        'training' => 'التدريب التقني',
        'general' => 'استفسار عام',
    ];
    $type = $labels[$sr->service_type] ?? $sr->service_type;
    $wa = preg_replace('/[^0-9]/', '', (string) $sr->phone);
    $rowLabel = 'style="padding:9px 12px;background:#f4f6fb;color:#65718f;font-size:13px;width:38%;border-bottom:1px solid #eef2fb;"';
    $rowVal = 'style="padding:9px 12px;color:#1a2547;font-size:14px;font-weight:600;border-bottom:1px solid #eef2fb;"';
@endphp

<h2 style="margin:0 0 6px;font-size:20px;color:#1a2547;">🔔 طلب جديد من المنصة</h2>
<p style="margin:0 0 18px;color:#65718f;font-size:14px;">وصلك طلب جديد عبر منصة وريد، تفاصيله كالتالي:</p>

<table style="width:100%;border-collapse:collapse;border:1px solid #eef2fb;border-radius:10px;overflow:hidden;">
    <tr><td {!! $rowLabel !!}>الخدمة</td><td {!! $rowVal !!}>{{ $type }}</td></tr>
    <tr><td {!! $rowLabel !!}>الاسم</td><td {!! $rowVal !!}>{{ $sr->name }}</td></tr>
    <tr><td {!! $rowLabel !!}>الموبايل / واتساب</td><td {!! $rowVal !!}><span dir="ltr">{{ $sr->phone }}</span></td></tr>
    @if($sr->email)<tr><td {!! $rowLabel !!}>البريد الإلكتروني</td><td {!! $rowVal !!}><span dir="ltr">{{ $sr->email }}</span></td></tr>@endif
    @if($sr->company)<tr><td {!! $rowLabel !!}>الشركة / النشاط</td><td {!! $rowVal !!}>{{ $sr->company }}</td></tr>@endif
    @if($sr->budget)<tr><td {!! $rowLabel !!}>الميزانية</td><td {!! $rowVal !!}>{{ $sr->budget }}</td></tr>@endif
    @foreach((array) $sr->payload as $k => $v)
        <tr><td {!! $rowLabel !!}>{{ $k }}</td><td {!! $rowVal !!}>{{ is_array($v) ? implode('، ', $v) : $v }}</td></tr>
    @endforeach
    @if($sr->message)<tr><td {!! $rowLabel !!}>الرسالة</td><td {!! $rowVal !!}>{{ $sr->message }}</td></tr>@endif
    <tr><td {!! $rowLabel !!}>المصدر</td><td {!! $rowVal !!}>{{ $sr->source ?? '—' }}</td></tr>
    <tr><td {!! $rowLabel !!}>التاريخ</td><td {!! $rowVal !!}>{{ $sr->created_at?->format('Y-m-d H:i') }}</td></tr>
</table>

<div style="margin-top:22px;text-align:center;">
    <a href="https://wa.me/{{ $wa }}" style="display:inline-block;background:#25d366;color:#fff;text-decoration:none;font-weight:bold;padding:11px 22px;border-radius:10px;font-size:14px;">💬 تواصل مع العميل عبر واتساب</a>
</div>
<p style="margin:18px 0 0;color:#8a99bb;font-size:12px;text-align:center;">يمكنك أيضاً متابعة الطلب من لوحة التحكم: <a href="https://wareed.vip/admin" style="color:#3b82f6;">wareed.vip/admin</a></p>
@endsection
