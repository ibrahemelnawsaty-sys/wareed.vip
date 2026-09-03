{{--
    عرض السعر الرسمي — نسخة العميل (A4، صفحة واحدة، جاهزة للطباعة/حفظ PDF).
--}}
@php
    $issued = $quote['issued_at'];
    $months = [1=>'يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
    $days = ['Saturday'=>'السبت','Sunday'=>'الأحد','Monday'=>'الإثنين','Tuesday'=>'الثلاثاء','Wednesday'=>'الأربعاء','Thursday'=>'الخميس','Friday'=>'الجمعة'];
    $fmt = fn ($d) => $days[$d->format('l')].'، '.$d->day.' '.$months[(int) $d->month].' '.$d->year.'م';
    $fmtShort = fn ($d) => $d->day.' '.$months[(int) $d->month].' '.$d->year.'م';
    $cur = $quote['currency'];
    $money = fn ($n) => number_format((float) $n, ((float) $n == (int) $n) ? 0 : 2);
    $pct = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
    $contactEmail = setting('contact_email', 'info@wareed.vip');
    $contactPhone = setting('contact_phone', '+201055789056');
    $legalName = setting('legal_name');
    $taxNumber = setting('tax_number');
    $commercialRegister = setting('commercial_register');
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>عرض سعر {{ $sr->reference }} — وريد</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=ibm-plex-sans-arabic:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #0d1830; --muted: #55638a; --faint: #8493b5;
            --line: #dde5f4; --line-soft: #eef2fa;
            --blue: #2563eb; --violet: #7c3aed;
            --grad: linear-gradient(120deg, #3b82f6 0%, #8b5cf6 48%, #2dd4bf 100%);
            --band: #0d1830;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'IBM Plex Sans Arabic', 'Tajawal', ui-sans-serif, system-ui, sans-serif;
            background: #eef2f9; color: var(--ink); line-height: 1.6;
            -webkit-font-smoothing: antialiased; padding: 26px 14px 60px;
        }
        .sheet {
            width: 210mm; min-height: 297mm; margin: 0 auto; background: #fff; position: relative;
            padding: 11mm 12mm 9mm; display: flex; flex-direction: column;
            box-shadow: 0 20px 60px -24px rgba(13, 24, 48, .35);
        }
        .sheet::before { content: ""; position: absolute; inset-inline: 0; top: 0; height: 4mm; background: var(--grad); }

        .head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10mm; padding-top: 2mm; }
        .head-title h1 { font-size: 20pt; font-weight: 700; letter-spacing: -.5px; line-height: 1.15; }
        .head-title .en { font-size: 8pt; font-weight: 600; letter-spacing: 3.4px; color: var(--faint); margin-top: 2mm; }
        .head-title .copy {
            display: inline-block; margin-top: 2.6mm; padding: 1.4mm 5mm; border-radius: 99px;
            font-size: 8pt; font-weight: 700; color: var(--blue);
            background: rgba(37, 99, 235, .07); border: 1px solid rgba(37, 99, 235, .28);
        }
        .head-org { display: flex; align-items: flex-start; gap: 4mm; }
        .head-org .mark { width: 15mm; height: 15mm; flex: 0 0 15mm; }
        .org-lines { font-size: 8.2pt; color: var(--muted); line-height: 1.7; }
        .org-lines .name { font-size: 13pt; font-weight: 700; color: var(--ink); line-height: 1.3; }
        .org-lines .tag { font-size: 8pt; color: var(--faint); margin-bottom: 1.5mm; }

        .refbar { margin-top: 5mm; display: flex; align-items: stretch; border: 1px solid var(--line); border-radius: 3mm; overflow: hidden; }
        .refbar-main { flex: 1; padding: 3.2mm 6mm; display: flex; flex-direction: column; justify-content: center; gap: 1mm; }
        .refbar-label { font-size: 7.6pt; font-weight: 600; color: var(--faint); letter-spacing: .5px; }
        .refbar-num { font-size: 15pt; font-weight: 700; direction: ltr; letter-spacing: 1.6px; font-variant-numeric: tabular-nums; }
        .refbar-meta { flex: 0 0 auto; padding: 3.2mm 6mm; border-inline-start: 1px solid var(--line); display: flex; flex-direction: column; justify-content: center; gap: 1.6mm; }
        .refbar-meta div { font-size: 8pt; color: var(--muted); white-space: nowrap; }
        .refbar-meta b { color: var(--ink); font-weight: 600; }
        .refbar-qr { flex: 0 0 auto; padding: 2.8mm; border-inline-start: 1px solid var(--line); background: #fbfcfe; display: flex; flex-direction: column; align-items: center; gap: 1mm; }
        .refbar-qr .qr { width: 19mm; height: 19mm; display: block; }
        .refbar-qr .cap { font-size: 6.2pt; color: var(--faint); }

        .parties { margin-top: 5mm; display: grid; grid-template-columns: 1fr 1fr; gap: 4mm; }
        .party { border: 1px solid var(--line); border-radius: 3mm; overflow: hidden; }
        .party-head { padding: 2mm 5mm; font-size: 8pt; font-weight: 700; color: #fff; background: var(--band); letter-spacing: .4px; }
        .party.to .party-head { background: linear-gradient(100deg, #2563eb, #7c3aed); }
        .party-body { padding: 3.2mm 5mm; display: flex; flex-direction: column; gap: 1.3mm; }
        .party-name { font-size: 11pt; font-weight: 700; }
        .party-row { font-size: 8.6pt; color: var(--muted); display: flex; gap: 2mm; }
        .party-row span:first-child { color: var(--faint); flex: 0 0 23mm; }
        .party-row b { font-weight: 600; color: var(--ink); }
        .party-row .ltr { direction: ltr; display: inline-block; }

        .section-title { margin-top: 5.5mm; display: flex; align-items: center; gap: 3mm; font-size: 10.5pt; font-weight: 700; }
        .section-title::before { content: ""; width: 1.2mm; height: 5mm; border-radius: 2px; background: var(--grad); }
        .section-title .en { font-size: 7.4pt; font-weight: 600; letter-spacing: 2px; color: var(--faint); margin-inline-start: auto; }

        table.items { width: 100%; border-collapse: collapse; margin-top: 3mm; }
        table.items th {
            background: var(--band); color: #fff; font-size: 8.4pt; font-weight: 600;
            padding: 2.2mm 4mm; text-align: start; letter-spacing: .3px;
        }
        table.items td { padding: 2.4mm 4mm; font-size: 8.8pt; border-bottom: 1px solid var(--line-soft); vertical-align: top; }
        table.items tr:nth-child(even) td { background: #fafbfe; }
        td.n { width: 9mm; color: var(--faint); font-size: 8pt; }
        td.it b { font-weight: 700; }
        td.it small { display: block; color: var(--muted); font-size: 7.8pt; line-height: 1.5; margin-top: .6mm; }
        td.num { width: 20mm; text-align: center; font-variant-numeric: tabular-nums; }
        td.num small { display: block; color: var(--muted); font-size: 7.4pt; font-weight: 600; margin-top: .4mm; }
        td.amt { width: 26mm; text-align: start; font-weight: 700; font-variant-numeric: tabular-nums; white-space: nowrap; }
        td.amt .free { color: #0d9488; font-weight: 700; }
        tr.phase td { background: #eef2fb !important; padding: 1.8mm 4mm; border-bottom: 1px solid var(--line); }
        tr.phase .ph-row { display: flex; align-items: center; justify-content: space-between; gap: 4mm; }
        tr.phase .ph-name { font-size: 8.6pt; font-weight: 700; color: var(--ink); position: relative; padding-inline-start: 3mm; }
        tr.phase .ph-name::before { content: ""; position: absolute; inset-inline-start: 0; top: .6mm; width: 1mm; height: 3.4mm; border-radius: 1px; background: var(--grad); }
        tr.phase .ph-sum { font-size: 8pt; font-weight: 700; color: var(--muted); font-variant-numeric: tabular-nums; }
        .tag-note {
            display: inline-block; margin-inline-start: 2mm; padding: .3mm 2.2mm; border-radius: 99px;
            font-size: 6.8pt; font-weight: 700; color: var(--blue);
            background: rgba(37, 99, 235, .09); border: 1px solid rgba(37, 99, 235, .25);
        }

        .totals { margin-top: 4mm; display: flex; justify-content: flex-start; }
        .totals table { width: 88mm; border-collapse: collapse; }
        .totals td { padding: 2mm 5mm; font-size: 9pt; border-bottom: 1px solid var(--line-soft); }
        .totals td:first-child { color: var(--muted); }
        .totals td:last-child { text-align: start; font-weight: 600; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .totals tr.grand td {
            background: var(--band); color: #fff; font-size: 11pt; font-weight: 700; border: none;
            padding: 3mm 5mm;
        }
        .totals tr.grand td:first-child { color: #fff; border-radius: 0 2mm 2mm 0; }
        .totals tr.grand td:last-child { border-radius: 2mm 0 0 2mm; }

        .pay { margin-top: 3mm; display: grid; grid-template-columns: 1.15fr 1fr; gap: 4mm; align-items: start; }
        /* عمود الاستحقاق يحتاج عرضاً أكبر — نضع الجدول بعرض كامل والبيانات البنكية تحته */
        .pay.with-due { grid-template-columns: 1fr; gap: 3mm; }
        .pay.with-due .bank { max-width: 92mm; }
        table.pay-table { width: 100%; border-collapse: collapse; }
        table.pay-table th {
            background: var(--band); color: #fff; font-size: 8pt; font-weight: 600;
            padding: 1.8mm 4mm; text-align: start;
        }
        table.pay-table td { padding: 2mm 4mm; font-size: 8.6pt; border-bottom: 1px solid var(--line-soft); }
        table.pay-table tr:nth-child(even) td { background: #fafbfe; }
        table.pay-table td b { font-weight: 700; }
        table.pay-table td small { display: block; color: var(--muted); font-size: 7.6pt; margin-top: .4mm; }
        table.pay-table td.num { text-align: center; font-weight: 700; font-variant-numeric: tabular-nums; }
        table.pay-table td.amt { text-align: start; font-weight: 700; font-variant-numeric: tabular-nums; white-space: nowrap; }
        table.pay-table.sched { margin-top: 3mm; }
        table.pay-table.opt th { background: #55638a; }
        table.pay-table.opt td { color: var(--muted); }
        table.pay-table.opt td b { color: var(--ink); }
        table.pay-table.opt td small { display: block; color: var(--faint); font-size: 7.6pt; margin-top: .4mm; }
        .opt-note { margin-top: 2.4mm; font-size: 8pt; color: var(--muted); line-height: 1.7; }
        .opt-sum { margin-top: 2.4mm; font-size: 8.6pt; color: var(--muted); }
        .opt-sum b { color: var(--ink); font-variant-numeric: tabular-nums; }
        .opt-flag {
            display: inline-block; margin-inline-start: 2.5mm; padding: .3mm 2.4mm; border-radius: 99px;
            font-size: 6.8pt; font-weight: 700; color: #b45309;
            background: rgba(245, 158, 11, .12); border: 1px solid rgba(245, 158, 11, .35);
        }
        table.pay-table td.due { font-size: 8pt; font-weight: 600; color: var(--muted); white-space: nowrap; }
        .bank { border: 1px solid rgba(37, 99, 235, .3); border-radius: 3mm; padding: 3mm 5mm; background: linear-gradient(150deg, rgba(59,130,246,.06), rgba(45,212,191,.04)); }
        .bank h4 { font-size: 8.4pt; font-weight: 700; margin-bottom: 1.6mm; }
        .bank .brow { display: flex; gap: 2mm; font-size: 8pt; color: var(--muted); line-height: 1.75; }
        .bank .brow span { flex: 0 0 18mm; color: var(--faint); }
        .bank .brow b { color: var(--ink); font-weight: 600; overflow-wrap: anywhere; }

        .terms { margin-top: 5mm; display: grid; grid-template-columns: 1fr 1fr; gap: 4mm; }
        .term-box { border: 1px solid var(--line); border-radius: 3mm; padding: 3mm 5mm; }
        .term-box h4 { font-size: 8.4pt; font-weight: 700; margin-bottom: 1.4mm; }
        .term-box p { font-size: 8pt; color: var(--muted); line-height: 1.65; }

        .foot { margin-top: auto; padding-top: 4.5mm; }
        .foot-note { font-size: 7.2pt; color: var(--faint); line-height: 1.65; border-top: 1px solid var(--line); padding-top: 3mm; }
        .run-foot {
            display: none; position: fixed; inset-inline: 0; bottom: 0; height: 9mm;
            align-items: center; justify-content: space-between; gap: 4mm;
            padding: 0 12mm; font-size: 7pt; color: var(--faint);
            border-top: 1px solid var(--line-soft); background: #fff;
        }
        .run-foot b { color: var(--muted); font-weight: 600; letter-spacing: .4px; }
        .foot-bar {
            margin-top: 3mm; padding-top: 2.6mm; border-top: 2px solid var(--band);
            display: flex; align-items: center; justify-content: space-between; gap: 6mm; font-size: 7.8pt; color: var(--muted);
        }
        .foot-bar .brand { font-weight: 700; color: var(--ink); }
        .foot-bar .ltr { direction: ltr; }

        .toolbar {
            position: sticky; top: 12px; z-index: 10; width: 210mm; max-width: 100%; margin: 0 auto 16px;
            display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
            background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 11px 18px;
            box-shadow: 0 12px 30px -18px rgba(13, 24, 48, .4);
        }
        .toolbar p { font-size: .82rem; color: var(--muted); }
        .toolbar b { color: var(--ink); }
        .tb-actions { display: flex; gap: 9px; flex-wrap: wrap; }
        .tb-btn {
            display: inline-flex; align-items: center; gap: 7px; cursor: pointer;
            padding: 9px 20px; border-radius: 11px; border: 1px solid transparent;
            font: inherit; font-size: .88rem; font-weight: 700;
        }
        .tb-primary { background: var(--grad); color: #fff; }
        .tb-ghost { background: #fff; color: var(--muted); border-color: var(--line); }
        .tb-btn .ic { width: 17px; height: 17px; }

        @media print {
            /* هامش سفلي لكل صفحة يسكنه الشريط الجاري أسفلها */
            @page { size: A4; margin: 0 0 13mm; }
            html, body { background: #fff; padding: 0; margin: 0; }
            .toolbar { display: none !important; }
            .sheet { width: 210mm; min-height: 0; box-shadow: none; margin: 0; padding-bottom: 4mm; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

            /* المستند يمتد على ما يلزم من الصفحات — والانتقال بينها يبقى مرتّباً */
            table.items thead, table.pay-table thead { display: table-header-group; }
            table.items tr, table.pay-table tr { break-inside: avoid; }
            .section-title { break-after: avoid; }
            .totals, .bank, .term-box, .refbar, .party, footer.foot { break-inside: avoid; }
            .parties, section.pay, .terms { break-inside: auto; }

            /* شريط جارٍ يتكرر أسفل كل صفحة مطبوعة يحمل الرقم المرجعي */
            .run-foot { display: flex !important; }
        }
        @media screen and (max-width: 230mm) {
            body { padding: 14px 8px 40px; }
            .sheet { width: 100%; min-height: auto; padding: 9mm 7mm; }
            .parties, .terms, .pay { grid-template-columns: 1fr; }
            .refbar { flex-wrap: wrap; }
            .refbar-meta, .refbar-qr { border-inline-start: 0; border-top: 1px solid var(--line); }
            .totals table { width: 100%; }
        }
    </style>
</head>
<body>
@include('quote._icons')

<div class="toolbar">
    <p>عرض سعر رسمي جاهز للحفظ — اختر <b>«حفظ بصيغة PDF»</b> من نافذة الطباعة.</p>
    <div class="tb-actions">
        <button type="button" class="tb-btn tb-primary" onclick="window.print()">
            <svg class="ic"><use href="#i-download"/></svg> تحميل عرض السعر PDF
        </button>
        <a class="tb-btn tb-ghost" href="{{ url('/') }}">
            <svg class="ic"><use href="#i-external"/></svg> موقع وريد
        </a>
    </div>
</div>

<article class="sheet">
    <header class="head">
        <div class="head-title">
            <h1>عرض سعر</h1>
            <div class="en">PRICE QUOTATION</div>
            <span class="copy">نسخة العميل</span>
        </div>
        <div class="head-org">
            <div class="org-lines">
                <div class="name">وريد</div>
                <div class="tag">{{ $legalName ?: 'منصتك التقنية المتكاملة' }}</div>
                @if ($taxNumber)
                    <div>الرقم الضريبي: <span dir="ltr">{{ $taxNumber }}</span></div>
                @endif
                @if ($commercialRegister)
                    <div>السجل التجاري: <span dir="ltr">{{ $commercialRegister }}</span></div>
                @endif
                <div>البريد: <span dir="ltr">{{ $contactEmail }}</span></div>
                <div>الهاتف: <span dir="ltr">{{ $contactPhone }}</span></div>
            </div>
            <svg class="mark" viewBox="0 0 40 40" fill="none" aria-hidden="true">
                <defs><linearGradient id="pm" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0" stop-color="#3B82F6"/><stop offset=".5" stop-color="#8B5CF6"/><stop offset="1" stop-color="#2DD4BF"/>
                </linearGradient></defs>
                <rect x="2.5" y="2.5" width="35" height="35" rx="11" stroke="url(#pm)" stroke-width="2" opacity=".55"/>
                <path d="M11 27 L18 14 L24 23 L29 13" stroke="url(#pm)" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="11" cy="27" r="2.5" fill="#2DD4BF"/><circle cx="18" cy="14" r="2.5" fill="#3B82F6"/>
                <circle cx="24" cy="23" r="2.5" fill="#8B5CF6"/><circle cx="29" cy="13" r="2.5" fill="#60A5FA"/>
            </svg>
        </div>
    </header>

    <section class="refbar">
        <div class="refbar-main">
            <div class="refbar-label">الرقم المرجعي · REFERENCE No.</div>
            <div class="refbar-num">{{ $sr->reference }}</div>
        </div>
        <div class="refbar-meta">
            <div>تاريخ العرض: <b>{{ $fmt($issued) }}</b></div>
            <div>صالح حتى: <b>{{ $fmt($quote['valid_until']) }}</b></div>
            <div>مدة الصلاحية: <b>{{ $quote['valid_days'] }} يوماً</b></div>
        </div>
        <div class="refbar-qr">
            {!! $qr !!}
            <span class="cap">امسح للتحقق</span>
        </div>
    </section>

    <section class="parties">
        <div class="party to">
            <div class="party-head">مقدَّم إلى</div>
            <div class="party-body">
                <div class="party-name">{{ $contact['name'] }}</div>
                <div class="party-row"><span>اسم المتجر</span><b>{{ $contact['store'] ?: '—' }}</b></div>
                <div class="party-row"><span>رقم الموبايل</span><b class="ltr">{{ $contact['phone'] ?: '—' }}</b></div>
                <div class="party-row"><span>البريد الإلكتروني</span><b class="ltr">{{ $contact['email'] ?: '—' }}</b></div>
            </div>
        </div>
        <div class="party from">
            <div class="party-head">مقدَّم من</div>
            <div class="party-body">
                <div class="party-name">منصة وريد</div>
                <div class="party-row"><span>الخدمة</span><b>المتاجر الإلكترونية</b></div>
                <div class="party-row"><span>نوع المستند</span><b>عرض سعر</b></div>
                @if ($quote['delivery_at'])
                    <div class="party-row"><span>موعد التسليم</span><b>{{ $fmt($quote['delivery_at']) }}</b></div>
                @elseif ($quote['timeline'])
                    <div class="party-row"><span>مدة التنفيذ</span><b>{{ $quote['timeline'] }}</b></div>
                @endif
            </div>
        </div>
    </section>

    <div class="section-title">
        بنود عرض السعر
        <span class="en">QUOTATION ITEMS</span>
    </div>
    <table class="items">
        <thead>
            <tr>
                <th style="width:9mm">م</th>
                <th>البند</th>
                <th style="width:20mm;text-align:center">الكمية</th>
                <th style="width:26mm">سعر الوحدة</th>
                <th style="width:26mm">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @php $n = 0; @endphp
            @foreach ($quote['phases'] as $phase)
                @if ($quote['has_phases'])
                    <tr class="phase">
                        <td colspan="5">
                            <div class="ph-row">
                                <span class="ph-name">{{ $phase['name'] ?: 'بنود إضافية' }}</span>
                                <span class="ph-sum">
                                    @if ($phase['total'] > 0)
                                        {{ $money($phase['total']) }} {{ $cur }}
                                    @else
                                        مجاناً
                                    @endif
                                </span>
                            </div>
                        </td>
                    </tr>
                @endif

                @foreach ($phase['items'] as $item)
                    <tr>
                        <td class="n">{{ ++$n }}</td>
                        <td class="it">
                            <b>{{ $item['name'] }}</b>
                            @if ($item['note'])<span class="tag-note">{{ $item['note'] }}</span>@endif
                            @if ($item['desc'])<small>{{ $item['desc'] }}</small>@endif
                        </td>
                        <td class="num">
                            {{ $item['qty'] }}
                            @if ($item['unit'])<small>{{ $item['unit'] }}</small>@endif
                        </td>
                        <td class="amt">
                            @if ($item['free'])<span class="free">مجاناً</span>@else{{ $money($item['price']) }} {{ $cur }}@endif
                        </td>
                        <td class="amt">
                            @if ($item['free'])<span class="free">مجاناً</span>@else{{ $money($item['total']) }} {{ $cur }}@endif
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr><td>الإجمالي قبل الخصم</td><td>{{ $money($quote['subtotal']) }} {{ $cur }}</td></tr>
            @if ($quote['discount'] > 0)
                <tr><td>الخصم ({{ $pct($quote['discount_percent']) }}%)</td><td>− {{ $money($quote['discount']) }} {{ $cur }}</td></tr>
            @endif
            @if ($quote['vat_percent'] > 0)
                <tr><td>ضريبة القيمة المضافة ({{ $pct($quote['vat_percent']) }}%)</td><td>{{ $money($quote['vat']) }} {{ $cur }}</td></tr>
            @endif
            <tr class="grand"><td>الإجمالي المستحق</td><td>{{ $money($quote['total']) }} {{ $cur }}</td></tr>
        </table>
    </div>

    @if ($quote['extras'])
        <div class="section-title">
            خدمات إضافية اختيارية
            <span class="en">OPTIONAL ADD-ONS</span>
        </div>
        <p class="opt-note">
            بنود معروضة للاطلاع فقط ولم تُحتسب ضمن الإجمالي المستحق أعلاه.
            يمكن إضافة أيٍّ منها لاحقاً بطلب منكم، ويُصدَر لها ملحق مستقل للعرض.
        </p>
        <table class="pay-table sched opt">
            <thead>
                <tr>
                    <th style="width:9mm">م</th>
                    <th>البند</th>
                    <th style="width:20mm;text-align:center">الكمية</th>
                    <th style="width:26mm">سعر الوحدة</th>
                    <th style="width:26mm">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quote['extras'] as $extraNo => $extra)
                    <tr>
                        <td class="n">{{ $extraNo + 1 }}</td>
                        <td>
                            <b>{{ $extra['name'] }}</b>
                            @if ($extra['note'])<span class="tag-note">{{ $extra['note'] }}</span>@endif
                            @if ($extra['desc'])<small>{{ $extra['desc'] }}</small>@endif
                        </td>
                        <td class="num">
                            {{ $extra['qty'] }}
                            @if ($extra['unit'])<small>{{ $extra['unit'] }}</small>@endif
                        </td>
                        <td class="amt">{{ $money($extra['price']) }} {{ $cur }}</td>
                        <td class="amt">{{ $money($extra['total']) }} {{ $cur }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="opt-sum">
            إجمالي الخدمات الاختيارية لو طُلبت جميعها:
            <b>{{ $money($quote['extras_total']) }} {{ $cur }}</b>
            <span class="opt-flag">غير مشمولة في الإجمالي المستحق</span>
        </p>
    @endif

    @if ($quote['schedule'])
        <div class="section-title">
            الجدول الزمني للتسليم
            <span class="en">DELIVERY SCHEDULE</span>
        </div>
        <table class="pay-table sched">
            <thead>
                <tr>
                    <th style="width:9mm">م</th>
                    <th>المرحلة</th>
                    <th style="width:34mm">تاريخ البدء</th>
                    <th style="width:34mm">تاريخ النهاية</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quote['schedule'] as $stageNo => $stage)
                    <tr>
                        <td class="n">{{ $stageNo + 1 }}</td>
                        <td><b>{{ $stage['phase'] ?: 'مرحلة' }}</b></td>
                        <td class="due">{{ $stage['start'] ? $fmtShort($stage['start']) : '—' }}</td>
                        <td class="due">{{ $stage['end'] ? $fmtShort($stage['end']) : 'يُحدَّد لاحقاً' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($quote['payments'] || $bank['has'])
        <div class="section-title">
            الدفعات وطريقة السداد
            <span class="en">PAYMENT TERMS</span>
        </div>
        @php $hasDue = collect($quote['payments'])->contains(fn ($p) => (bool) $p['due']); @endphp
        <section @class(['pay', 'with-due' => $hasDue])>
            @if ($quote['payments'])
                <table class="pay-table">
                    <thead>
                        <tr>
                            <th>الدفعة</th>
                            @if ($hasDue)<th style="width:24mm">الاستحقاق</th>@endif
                            <th style="width:16mm;text-align:center">النسبة</th>
                            <th style="width:26mm">القيمة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quote['payments'] as $pay)
                            <tr>
                                <td>
                                    <b>{{ $pay['label'] }}</b>
                                    @if ($pay['note'])<small>{{ $pay['note'] }}</small>@endif
                                </td>
                                @if ($hasDue)
                                    <td class="due">{{ $pay['due'] ? $fmtShort($pay['due']) : '—' }}</td>
                                @endif
                                <td class="num">{{ $pct($pay['percent']) }}%</td>
                                <td class="amt">{{ $money($pay['amount']) }} {{ $cur }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if ($bank['has'])
                <div class="bank">
                    <h4>بيانات التحويل البنكي</h4>
                    @if ($bank['bank'])<div class="brow"><span>البنك</span><b>{{ $bank['bank'] }}</b></div>@endif
                    @if ($bank['holder'])<div class="brow"><span>اسم الحساب</span><b>{{ $bank['holder'] }}</b></div>@endif
                    @if ($bank['account'])<div class="brow"><span>رقم الحساب</span><b dir="ltr">{{ $bank['account'] }}</b></div>@endif
                    @if ($bank['iban'])<div class="brow"><span>IBAN</span><b dir="ltr">{{ $bank['iban'] }}</b></div>@endif
                    @if ($bank['swift'])<div class="brow"><span>SWIFT</span><b dir="ltr">{{ $bank['swift'] }}</b></div>@endif
                </div>
            @endif
        </section>
    @endif

    <section class="terms">
        <div class="term-box">
            <h4>شروط العرض</h4>
            <p>
                هذا العرض صالح لمدة {{ $quote['valid_days'] }} يوماً من تاريخ إصداره.
                @if ($quote['schedule'])
                    مواعيد بدء المراحل وتسليمها موضّحة في الجدول الزمني أعلاه.
                @elseif ($quote['timeline'])
                    تبدأ مدة التنفيذ ({{ $quote['timeline'] }}) من تاريخ اعتماد العرض.
                @endif
                الأسعار المذكورة شاملة لما ورد في البنود أعلاه فقط.
            </p>
        </div>
        <div class="term-box">
            <h4>{{ $quote['notes'] ? 'ملاحظات' : 'الخطوة التالية' }}</h4>
            <p>
                @if ($quote['notes'])
                    {{ $quote['notes'] }}
                @else
                    لاعتماد العرض والبدء في التنفيذ يرجى التواصل معنا عبر البريد أو الهاتف الموضّحين أعلاه،
                    مع ذكر الرقم المرجعي للطلب.
                @endif
            </p>
        </div>
    </section>

    <footer class="foot">
        <p class="foot-note">
            هذا المستند نسخة العميل من عرض سعر صادر إلكترونياً عن منصة وريد، ويُعدّ ساري المفعول خلال مدة صلاحيته المذكورة أعلاه.
            يمكن التحقق من العرض بالرقم المرجعي أو رمز QR. أي تعديل على نطاق العمل قد يستوجب مراجعة الأسعار.
        </p>
        <div class="foot-bar">
            <span class="brand">وريد — منصتك التقنية المتكاملة</span>
            <span class="ltr">{{ $contactEmail }} · wareed.vip</span>
        </div>
    </footer>
</article>

{{-- شريط جارٍ يتكرر أسفل كل صفحة عند الطباعة (مخفي على الشاشة) --}}
<div class="run-foot" aria-hidden="true">
    <span>وريد لتقنية المعلومات · {{ $contactEmail }}</span>
    <b>عرض سعر · {{ $sr->reference }}</b>
</div>

</body>
</html>
