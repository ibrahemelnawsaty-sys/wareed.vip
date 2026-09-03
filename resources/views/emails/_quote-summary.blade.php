{{-- ملخّص العرض داخل الرسائل: البنود والإجماليات والجدول الزمني والدفعات --}}
@php
    $sum = \App\Http\Controllers\QuoteController::quoteOf($summaryOf);
    $flowSum = \App\Http\Controllers\QuoteController::flowOf($summaryOf);
    $cell = 'padding:7px 12px;font-size:12.5px;border-bottom:1px solid #eef2fa;';
    $head = 'padding:8px 12px;font-size:11.5px;font-weight:bold;color:#ffffff;background:#0d1830;text-align:right;';
    $cap = 'margin:22px 0 8px;font-size:14px;font-weight:bold;color:#0d1830;';
    $money = fn ($n) => number_format((float) $n, ((float) $n == (int) $n) ? 0 : 2).' '.($sum['currency'] ?? '');
@endphp

@if ($sum)
    <div style="border-top:1px solid #eef2fa;margin-top:22px;padding-top:4px;"></div>

    <p style="{{ $cap }}">بنود العرض</p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="border-collapse:collapse;border:1px solid #eef2fa;border-radius:8px;overflow:hidden;">
        <tr>
            <th style="{{ $head }}">البند</th>
            <th style="{{ $head }}width:60px;text-align:center;">الكمية</th>
            <th style="{{ $head }}width:110px;">الإجمالي</th>
        </tr>
        @foreach ($sum['items'] as $row)
            <tr>
                <td style="{{ $cell }}color:#0d1830;">
                    <b>{{ $row['name'] }}</b>
                    @if ($row['note'])
                        <span style="color:#2563eb;font-size:11px;"> · {{ $row['note'] }}</span>
                    @endif
                </td>
                <td style="{{ $cell }}text-align:center;color:#55638a;">
                    {{ $row['qty'] }}{{ $row['unit'] ? ' '.$row['unit'] : '' }}
                </td>
                <td style="{{ $cell }}color:#0d1830;font-weight:bold;">
                    @if ($row['free'])مجاناً@else{{ $money($row['total']) }}@endif
                </td>
            </tr>
        @endforeach
        <tr>
            <td colspan="2" style="{{ $cell }}background:#f7f9fd;color:#55638a;">الإجمالي المستحق</td>
            <td style="{{ $cell }}background:#f7f9fd;color:#2563eb;font-weight:bold;font-size:14px;">
                {{ $money($sum['total']) }}
            </td>
        </tr>
    </table>

    @if ($sum['schedule'])
        <p style="{{ $cap }}">الجدول الزمني للتسليم</p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
               style="border-collapse:collapse;border:1px solid #eef2fa;border-radius:8px;overflow:hidden;">
            <tr>
                <th style="{{ $head }}">المرحلة</th>
                <th style="{{ $head }}width:100px;">من</th>
                <th style="{{ $head }}width:100px;">إلى</th>
            </tr>
            @foreach ($sum['schedule'] as $stageRow)
                <tr>
                    <td style="{{ $cell }}color:#0d1830;">{{ $stageRow['phase'] ?: 'مرحلة' }}</td>
                    <td style="{{ $cell }}color:#55638a;">{{ $stageRow['start']?->format('Y/m/d') ?? '—' }}</td>
                    <td style="{{ $cell }}color:#55638a;">{{ $stageRow['end']?->format('Y/m/d') ?? 'يُحدَّد لاحقاً' }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if ($sum['payments'])
        <p style="{{ $cap }}">الدفعات وطريقة السداد</p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
               style="border-collapse:collapse;border:1px solid #eef2fa;border-radius:8px;overflow:hidden;">
            <tr>
                <th style="{{ $head }}">الدفعة</th>
                <th style="{{ $head }}width:70px;text-align:center;">النسبة</th>
                <th style="{{ $head }}width:110px;">القيمة</th>
            </tr>
            @foreach ($sum['payments'] as $pay)
                <tr>
                    <td style="{{ $cell }}color:#0d1830;">
                        {{ $pay['label'] }}
                        @if ($pay['due'])
                            <span style="color:#8493b5;font-size:11px;"> · تستحق {{ $pay['due']->format('Y/m/d') }}</span>
                        @endif
                    </td>
                    <td style="{{ $cell }}text-align:center;color:#55638a;">
                        {{ rtrim(rtrim(number_format($pay['percent'], 2), '0'), '.') }}%
                    </td>
                    <td style="{{ $cell }}color:#0d1830;font-weight:bold;">{{ $money($pay['amount']) }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if ($sum['extras'])
        <p style="{{ $cap }}">خدمات إضافية اختيارية</p>
        <p style="margin:0 0 8px;font-size:12px;color:#8493b5;">غير مشمولة في الإجمالي المستحق — للاطلاع فقط.</p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
               style="border-collapse:collapse;border:1px solid #eef2fa;border-radius:8px;overflow:hidden;">
            @foreach ($sum['extras'] as $extra)
                <tr>
                    <td style="{{ $cell }}color:#55638a;">{{ $extra['name'] }}</td>
                    <td style="{{ $cell }}width:110px;color:#55638a;">{{ $money($extra['total']) }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <p style="{{ $cap }}">الخطوات التالية</p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="border-collapse:collapse;border:1px solid #eef2fa;border-radius:8px;overflow:hidden;">
        @foreach (\App\Http\Controllers\QuoteController::STAGES as $key => $meta)
            @php $isNow = $flowSum['stage'] === $key; @endphp
            <tr>
                <td style="{{ $cell }}width:26px;color:{{ $isNow ? '#2563eb' : '#8493b5' }};">
                    {{ $loop->iteration }}
                </td>
                <td style="{{ $cell }}color:{{ $isNow ? '#0d1830' : '#8493b5' }};
                           font-weight:{{ $isNow ? 'bold' : 'normal' }};">
                    {{ $meta['label'] }}
                    @if ($isNow)<span style="color:#2563eb;font-size:11px;"> · المرحلة الحالية</span>@endif
                </td>
            </tr>
        @endforeach
    </table>
@endif
