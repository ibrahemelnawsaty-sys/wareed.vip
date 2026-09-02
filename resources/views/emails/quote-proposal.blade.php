@extends('emails.layout', ['title' => 'عرض سعر متجرك الإلكتروني — وريد'])

@section('content')
    @php
        $money = fn ($n) => number_format((float) $n, ((float) $n == (int) $n) ? 0 : 2).' '.$quote['currency'];
    @endphp

    <h2 style="margin:0 0 14px;font-size:20px;color:#0d1830;">عرض سعر متجرك الإلكتروني</h2>

    <p style="margin:0 0 16px;color:#55638a;">
        مرحباً {{ $sr->name }}،<br>
        يسعدنا موافاتكم بعرض السعر المخصّص لمتجركم الإلكتروني بناءً على الطلب المسجّل لدينا.
    </p>

    <table role="presentation" width="100%" style="border-collapse:collapse;margin:0 0 18px;background:#f7f9fe;border:1px solid #e6ecf7;border-radius:12px;">
        <tr>
            <td style="padding:14px 18px;font-size:13px;color:#55638a;">الرقم المرجعي</td>
            <td style="padding:14px 18px;font-size:14px;font-weight:bold;color:#0d1830;" dir="ltr" align="left">{{ $sr->reference }}</td>
        </tr>
        <tr>
            <td style="padding:0 18px 14px;font-size:13px;color:#55638a;">إجمالي العرض</td>
            <td style="padding:0 18px 14px;font-size:16px;font-weight:bold;color:#2563eb;" align="left">{{ $money($quote['total']) }}</td>
        </tr>
        <tr>
            <td style="padding:0 18px 14px;font-size:13px;color:#55638a;">صالح حتى</td>
            <td style="padding:0 18px 14px;font-size:14px;font-weight:bold;color:#0d1830;" align="left">{{ $quote['valid_until']->format('Y/m/d') }}</td>
        </tr>
    </table>

    <table role="presentation" width="100%" style="border-collapse:collapse;margin:0 0 20px;">
        <tr>
            <th align="right" style="padding:9px 12px;background:#0d1830;color:#fff;font-size:13px;">البند</th>
            <th align="left" style="padding:9px 12px;background:#0d1830;color:#fff;font-size:13px;">القيمة</th>
        </tr>
        @foreach ($quote['items'] as $item)
            <tr>
                <td style="padding:9px 12px;border-bottom:1px solid #eef2fa;font-size:13px;color:#0d1830;">
                    {{ $item['name'] }}@if ($item['qty'] > 1 || $item['unit']) <span style="color:#8493b5;">× {{ trim($item['qty'].' '.$item['unit']) }}</span>@endif
                    @if ($item['note'])<span style="color:#2563eb;font-size:11px;"> — {{ $item['note'] }}</span>@endif
                </td>
                <td align="left" style="padding:9px 12px;border-bottom:1px solid #eef2fa;font-size:13px;font-weight:bold;color:{{ $item['free'] ? '#0d9488' : '#0d1830' }};">
                    {{ $item['free'] ? 'مجاناً' : $money($item['total']) }}
                </td>
            </tr>
        @endforeach
    </table>

    <p style="text-align:center;margin:0 0 20px;">
        <a href="{{ $proposalUrl }}"
           style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;font-weight:bold;font-size:15px;padding:13px 30px;border-radius:10px;">
            عرض السعر التفصيلي وتحميله PDF
        </a>
    </p>

    @if ($quote['timeline'])
        <p style="margin:0 0 10px;color:#55638a;font-size:13px;">
            <strong style="color:#0d1830;">مدة التنفيذ:</strong> {{ $quote['timeline'] }}
        </p>
    @endif

    <p style="margin:0;color:#8493b5;font-size:12px;">
        لأي استفسار يسعدنا تواصلكم معنا على {{ setting('contact_email', 'info@wareed.vip') }}
        أو هاتفياً على {{ setting('contact_phone', '+201055789056') }}.
    </p>
@endsection
