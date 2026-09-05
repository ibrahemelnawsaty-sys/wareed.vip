@extends('emails.layout', ['title' => 'عرض سعر متجرك الإلكتروني — وريد'])

@section('content')
    @php
        $money = fn ($n) => number_format((float) $n, ((float) $n == (int) $n) ? 0 : 2).' '.$quote['currency'];
    @endphp

    <h2 style="margin:0 0 14px;font-size:20px;color:#0d1830;">عرض سعر متجرك الإلكتروني</h2>

    @if ($quote['version'] > 1)
        @php $decision = \App\Http\Controllers\QuoteController::decisionOf($sr); @endphp
        <table role="presentation" width="100%" style="border-collapse:collapse;margin:0 0 18px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;">
            <tr>
                <td style="padding:14px 18px;">
                    <span style="display:inline-block;margin-bottom:8px;padding:3px 11px;border-radius:99px;font-size:11px;font-weight:bold;color:#ffffff;background:#2563eb;">
                        الإصدار {{ $quote['version'] }} — عرض مُحدَّث
                    </span>
                    <p style="margin:0;font-size:13px;color:#0d1830;line-height:1.8;">
                        @if (($decision['choice'] ?? null) === 'discount')
                            هذا عرضك المُحدَّث بعد طلبك تخفيضاً على السعر السابق، ويحلّ محلّه بالكامل.
                        @else
                            هذا عرضك المُحدَّث، ويحلّ محلّ أي عرض سابق وصلك على هذا الطلب.
                        @endif
                        راجع البنود والإجمالي الجديدين أدناه.
                    </p>
                </td>
            </tr>
        </table>
    @endif

    {{-- المقدّمة قابلة للتعديل من: لوحة التحكم ← قوالب البريد الإلكتروني ← إرسال عرض السعر --}}
    {!! \App\Support\MailTemplates::html(\App\Support\MailTemplates::render(
        \App\Support\MailTemplates::body('proposal_sent'),
        \App\Support\MailTemplates::variables($sr),
    )) !!}

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
            {{ $quote['version'] > 1 ? 'عرض السعر المُحدَّث وتحميله PDF' : 'عرض السعر التفصيلي وتحميله PDF' }}
        </a>
    </p>

    @if ($quote['delivery_at'])
        <p style="margin:0 0 10px;color:#55638a;font-size:13px;">
            <strong style="color:#0d1830;">موعد التسليم:</strong>
            {{ $quote['delivery_at']->format('Y/m/d') }} — والمراحل التفصيلية داخل العرض.
        </p>
    @elseif ($quote['timeline'])
        <p style="margin:0 0 10px;color:#55638a;font-size:13px;">
            <strong style="color:#0d1830;">مدة التنفيذ:</strong> {{ $quote['timeline'] }}
        </p>
    @endif

    <p style="margin:0;color:#8493b5;font-size:12px;">
        لأي استفسار يسعدنا تواصلكم معنا على {{ setting('contact_email', 'info@wareed.vip') }}
        أو هاتفياً على {{ setting('contact_phone', '+201055789056') }}.
    </p>
@endsection
