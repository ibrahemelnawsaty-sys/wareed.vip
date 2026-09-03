{{--
    مستند طلب المتجر الإلكتروني — نسخة العميل الرسمية (A4، صفحة واحدة).
    مُهيّأ للطباعة/الحفظ PDF عبر المتصفح: @page A4 بهوامش صفرية والتصميم يتكفّل بالهوامش.
--}}
@php
    $payload = (array) $sr->payload;
    $issued = $sr->created_at;
    $months = [1=>'يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
    $days = ['Saturday'=>'السبت','Sunday'=>'الأحد','Monday'=>'الإثنين','Tuesday'=>'الثلاثاء','Wednesday'=>'الأربعاء','Thursday'=>'الخميس','Friday'=>'الجمعة'];
    $fmt = fn ($d) => $days[$d->format('l')].'، '.$d->day.' '.$months[(int) $d->month].' '.$d->year.'م';
    $contactEmail = setting('contact_email', 'info@wareed.vip');
    $contactPhone = setting('contact_phone', '+201055789056');
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $sr->reference }} — طلب متجر إلكتروني | وريد</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=ibm-plex-sans-arabic:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #0d1830; --muted: #55638a; --faint: #8493b5;
            --line: #dde5f4; --line-soft: #eef2fa;
            --blue: #2563eb; --violet: #7c3aed; --teal: #0d9488;
            --grad: linear-gradient(120deg, #3b82f6 0%, #8b5cf6 48%, #2dd4bf 100%);
            --band: #0d1830;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'IBM Plex Sans Arabic', 'Tajawal', ui-sans-serif, system-ui, sans-serif;
            background: #eef2f9; color: var(--ink); line-height: 1.65;
            -webkit-font-smoothing: antialiased; padding: 26px 14px 60px;
        }

        /* ===== ورقة A4 ===== */
        .sheet {
            width: 210mm; min-height: 297mm; margin: 0 auto; background: #fff; position: relative;
            padding: 11mm 12mm 9mm; display: flex; flex-direction: column;
            box-shadow: 0 20px 60px -24px rgba(13, 24, 48, .35);
        }
        .sheet::before { content: ""; position: absolute; inset-inline: 0; top: 0; height: 4mm; background: var(--grad); }

        /* ===== الترويسة ===== */
        .head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10mm; padding-top: 2mm; }
        .head-title h1 { font-size: 20pt; font-weight: 700; letter-spacing: -.5px; line-height: 1.15; }
        .head-title .en { font-size: 8pt; font-weight: 600; letter-spacing: 3.4px; color: var(--faint); margin-top: 2mm; }
        .head-title .copy {
            display: inline-block; margin-top: 2.6mm; padding: 1.4mm 5mm; border-radius: 99px;
            font-size: 8pt; font-weight: 700; color: var(--blue);
            background: rgba(37, 99, 235, .07); border: 1px solid rgba(37, 99, 235, .28);
        }
        .head-org { text-align: start; display: flex; align-items: flex-start; gap: 4mm; }
        .head-org .mark { width: 15mm; height: 15mm; flex: 0 0 15mm; }
        .org-lines { font-size: 8.2pt; color: var(--muted); line-height: 1.7; }
        .org-lines .name { font-size: 13pt; font-weight: 700; color: var(--ink); line-height: 1.3; }
        .org-lines .tag { font-size: 8pt; color: var(--faint); margin-bottom: 1.5mm; }

        /* ===== شريط الرقم المرجعي ===== */
        .refbar {
            margin-top: 5mm; display: flex; align-items: stretch; justify-content: space-between;
            border: 1px solid var(--line); border-radius: 3mm; overflow: hidden;
        }
        .refbar-main { flex: 1; padding: 3.2mm 6mm; display: flex; flex-direction: column; justify-content: center; gap: 1mm; }
        .refbar-label { font-size: 7.6pt; font-weight: 600; color: var(--faint); letter-spacing: .5px; }
        .refbar-num { font-size: 15pt; font-weight: 700; direction: ltr; letter-spacing: 1.6px; color: var(--ink); font-variant-numeric: tabular-nums; }
        .refbar-qr { flex: 0 0 auto; padding: 2.8mm; border-inline-start: 1px solid var(--line); background: #fbfcfe; display: flex; flex-direction: column; align-items: center; gap: 1mm; }
        .refbar-qr .qr { width: 19mm; height: 19mm; display: block; }
        .refbar-qr .cap { font-size: 6.2pt; color: var(--faint); letter-spacing: .3px; }
        .refbar-meta { flex: 0 0 auto; padding: 3.2mm 6mm; border-inline-start: 1px solid var(--line); display: flex; flex-direction: column; justify-content: center; gap: 1.6mm; }
        .refbar-meta div { font-size: 8pt; color: var(--muted); white-space: nowrap; }
        .refbar-meta b { color: var(--ink); font-weight: 600; }

        /* ===== جدول الأطراف ===== */
        .parties { margin-top: 5mm; display: grid; grid-template-columns: 1fr 1fr; gap: 4mm; }
        .party { border: 1px solid var(--line); border-radius: 3mm; overflow: hidden; }
        .party-head {
            padding: 2mm 5mm; font-size: 8pt; font-weight: 700; color: #fff;
            background: var(--band); letter-spacing: .4px;
        }
        .party.to .party-head { background: linear-gradient(100deg, #2563eb, #7c3aed); }
        .party-body { padding: 3.2mm 5mm; display: flex; flex-direction: column; gap: 1.3mm; }
        .party-name { font-size: 11pt; font-weight: 700; }
        .party-row { font-size: 8.6pt; color: var(--muted); display: flex; gap: 2mm; }
        .party-row span:first-child { color: var(--faint); flex: 0 0 23mm; }
        .party-row b { font-weight: 600; color: var(--ink); }
        .party-row .ltr { direction: ltr; display: inline-block; }

        /* ===== جدول تفاصيل الطلب ===== */
        .section-title {
            margin-top: 5.5mm; display: flex; align-items: center; gap: 3mm;
            font-size: 10.5pt; font-weight: 700;
        }
        .section-title::before { content: ""; width: 1.2mm; height: 5mm; border-radius: 2px; background: var(--grad); }
        .section-title .en { font-size: 7.4pt; font-weight: 600; letter-spacing: 2px; color: var(--faint); margin-inline-start: auto; }

        table.data { width: 100%; border-collapse: collapse; margin-top: 3mm; }
        table.data th {
            background: var(--band); color: #fff; font-size: 8.4pt; font-weight: 600;
            padding: 2.2mm 5mm; text-align: start; letter-spacing: .3px;
        }
        table.data th:last-child { text-align: start; }
        table.data td { padding: 2.3mm 5mm; font-size: 8.8pt; border-bottom: 1px solid var(--line-soft); vertical-align: top; }
        table.data tr:nth-child(even) td { background: #fafbfe; }
        table.data td.k { width: 50mm; color: var(--muted); font-weight: 600; font-size: 8.6pt; }
        table.data td.v { font-weight: 600; }
        table.data tr:last-child td { border-bottom: 1px solid var(--line); }

        /* ===== شريط التعهّد ===== */
        .pledge {
            margin-top: 5mm; border: 1px solid rgba(37, 99, 235, .3); border-radius: 3mm;
            background: linear-gradient(140deg, rgba(59, 130, 246, .07), rgba(45, 212, 191, .05));
            padding: 3.4mm 6mm; display: flex; align-items: center; gap: 4.5mm;
        }
        .pledge-ic { flex: 0 0 9mm; width: 9mm; height: 9mm; border-radius: 50%; background: var(--grad); display: flex; align-items: center; justify-content: center; }
        .pledge-ic svg { width: 5mm; height: 5mm; color: #fff; }
        .pledge-tx { font-size: 8.4pt; color: var(--muted); line-height: 1.7; }
        .pledge-tx b { color: var(--ink); font-weight: 700; }

        /* ===== التذييل ===== */
        .foot { margin-top: auto; padding-top: 4.5mm; }
        .foot-note { font-size: 7.2pt; color: var(--faint); line-height: 1.65; border-top: 1px solid var(--line); padding-top: 3mm; }
        .foot-bar {
            margin-top: 3mm; padding-top: 2.6mm; border-top: 2px solid var(--band);
            display: flex; align-items: center; justify-content: space-between; gap: 6mm; font-size: 7.8pt; color: var(--muted);
        }
        .foot-bar .brand { font-weight: 700; color: var(--ink); }
        .foot-bar .ltr { direction: ltr; }

        /* ===== شريط الأدوات (لا يُطبع) ===== */
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
        .tb-btn svg { width: 17px; height: 17px; }

        @media print {
            @page { size: A4; margin: 0; }
            html, body { background: #fff; padding: 0; margin: 0; }
            .toolbar { display: none !important; }
            .sheet { width: 210mm; min-height: 297mm; box-shadow: none; margin: 0; page-break-after: avoid; }
            .sheet::before { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
        /* ===== ضبط الكثافة: يُطبَّق تلقائياً عند طول المحتوى لضمان صفحة واحدة ===== */
        .fit-1 .refbar { margin-top: 4mm; }
        .fit-1 .parties, .fit-1 .pledge { margin-top: 4mm; }
        .fit-1 .section-title { margin-top: 4.5mm; }
        .fit-1 table.data td { padding: 1.9mm 5mm; font-size: 8.4pt; }
        .fit-1 .party-body { padding: 2.8mm 5mm; gap: 1.1mm; }
        .fit-1 .pledge { padding: 3mm 6mm; }
        .fit-1 .pledge-tx { font-size: 8.1pt; line-height: 1.6; }
        .fit-1 .foot { padding-top: 3.6mm; }

        .fit-2 .sheet { padding: 9mm 11mm 7.5mm; }
        .fit-2 .head-title h1 { font-size: 18pt; }
        .fit-2 .refbar { margin-top: 3.4mm; }
        .fit-2 .parties, .fit-2 .pledge { margin-top: 3.4mm; }
        .fit-2 .section-title { margin-top: 3.8mm; }
        .fit-2 table.data td { padding: 1.5mm 4.5mm; font-size: 8pt; line-height: 1.5; }
        .fit-2 table.data th { padding: 1.8mm 4.5mm; }
        .fit-2 .party-body { padding: 2.4mm 4.5mm; gap: .9mm; }
        .fit-2 .party-name { font-size: 10.2pt; }
        .fit-2 .party-row { font-size: 8.2pt; }
        .fit-2 .refbar-main { padding: 2.6mm 5mm; }
        .fit-2 .refbar-num { font-size: 13.5pt; }
        .fit-2 .refbar-qr { padding: 2.2mm; }
        .fit-2 .refbar-qr .qr { width: 16mm; height: 16mm; }
        .fit-2 .pledge { padding: 2.6mm 5mm; }
        .fit-2 .pledge-tx { font-size: 7.8pt; line-height: 1.55; }
        .fit-2 .pledge-ic { flex-basis: 7.5mm; width: 7.5mm; height: 7.5mm; }
        .fit-2 .foot { padding-top: 3mm; }
        .fit-2 .foot-note { font-size: 6.9pt; line-height: 1.55; padding-top: 2.4mm; }

        /* الاستجابة للشاشات الصغيرة فقط — الطباعة تبقى بمقاس A4 ثابت */
        @media screen and (max-width: 230mm) {
            body { padding: 14px 8px 40px; }
            .sheet { width: 100%; min-height: auto; padding: 9mm 7mm; }
            .parties { grid-template-columns: 1fr; }
            .refbar { flex-wrap: wrap; }
            .refbar-meta, .refbar-qr { border-inline-start: 0; border-top: 1px solid var(--line); }
        }
    </style>
</head>
<body>
@include('quote._icons')

<div class="toolbar">
    <p>مستند رسمي جاهز للحفظ — اختر <b>«حفظ بصيغة PDF»</b> من نافذة الطباعة.</p>
    <div class="tb-actions">
        <button type="button" class="tb-btn tb-primary" onclick="window.print()">
            <svg class="ic"><use href="#i-download"/></svg> تحميل المستند PDF
        </button>
        <a class="tb-btn tb-ghost" href="{{ url('/') }}">
            <svg class="ic"><use href="#i-external"/></svg> موقع وريد
        </a>
    </div>
</div>

<article class="sheet">

    {{-- الترويسة --}}
    <header class="head">
        <div class="head-title">
            <h1>طلب متجر إلكتروني</h1>
            <div class="en">E-COMMERCE STORE REQUEST</div>
            <span class="copy">نسخة العميل</span>
        </div>
        <div class="head-org">
            <div class="org-lines">
                <div class="name">وريد</div>
                <div class="tag">{{ setting('legal_name') ?: 'منصتك التقنية المتكاملة' }}</div>
                @if ($tax = setting('tax_number'))
                    <div>الرقم الضريبي: <span dir="ltr">{{ $tax }}</span></div>
                @endif
                <div>البريد الإلكتروني: <span dir="ltr">{{ $contactEmail }}</span></div>
                <div>الهاتف: <span dir="ltr">{{ $contactPhone }}</span></div>
                <div>الموقع: <span dir="ltr">wareed.vip</span></div>
            </div>
            <svg class="mark" viewBox="0 0 40 40" fill="none" aria-hidden="true">
                <defs><linearGradient id="dm" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0" stop-color="#3B82F6"/><stop offset=".5" stop-color="#8B5CF6"/><stop offset="1" stop-color="#2DD4BF"/>
                </linearGradient></defs>
                <rect x="2.5" y="2.5" width="35" height="35" rx="11" stroke="url(#dm)" stroke-width="2" opacity=".55"/>
                <path d="M11 27 L18 14 L24 23 L29 13" stroke="url(#dm)" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="11" cy="27" r="2.5" fill="#2DD4BF"/><circle cx="18" cy="14" r="2.5" fill="#3B82F6"/>
                <circle cx="24" cy="23" r="2.5" fill="#8B5CF6"/><circle cx="29" cy="13" r="2.5" fill="#60A5FA"/>
            </svg>
        </div>
    </header>

    {{-- الرقم المرجعي + QR + التواريخ --}}
    <section class="refbar">
        <div class="refbar-main">
            <div class="refbar-label">الرقم المرجعي للطلب · REFERENCE No.</div>
            <div class="refbar-num">{{ $sr->reference }}</div>
        </div>
        <div class="refbar-meta">
            <div>تاريخ الطلب: <b>{{ $fmt($issued) }}</b></div>
            <div>وقت الاستلام: <b dir="ltr">{{ $issued->format('H:i') }}</b></div>
            <div>موعد عرض السعر: <b>{{ $fmt($deadline) }}</b></div>
        </div>
        <div class="refbar-qr">
            {!! $qr !!}
            <span class="cap">امسح للتحقق</span>
        </div>
    </section>

    {{-- الأطراف --}}
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
                <div class="party-row"><span>حالة الطلب</span><b>قيد تجهيز عرض السعر</b></div>
                <div class="party-row"><span>قناة الاستلام</span><b>النموذج الإلكتروني</b></div>
            </div>
        </div>
    </section>

    {{-- تفاصيل الطلب --}}
    <div class="section-title">
        تفاصيل الطلب المقدَّم
        <span class="en">REQUEST DETAILS</span>
    </div>
    <table class="data">
        <thead>
            <tr><th style="width:50mm">البند</th><th>البيان</th></tr>
        </thead>
        <tbody>
            @foreach($rows as [$key, $value])
                <tr><td class="k">{{ $key }}</td><td class="v">{{ $value }}</td></tr>
            @endforeach
        </tbody>
    </table>

    {{-- التعهّد --}}
    <section class="pledge">
        <span class="pledge-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.6 4.6L19 7.4"/></svg></span>
        <p class="pledge-tx">
            تم استلام هذا الطلب وتسجيله رسمياً لدى منصة وريد تحت الرقم المرجعي أعلاه.
            يتولّى فريق وريد دراسة البيانات المقدَّمة وتجهيز <b>عرض سعر مخصّص</b> يُرسل إلى العميل
            خلال <b>{{ $slaDays }} أيام عمل</b> من تاريخ الاستلام.
        </p>
    </section>

    {{-- التذييل --}}
    <footer class="foot">
        <p class="foot-note">
            هذا المستند نسخة العميل من طلب مسجّل إلكترونياً، ويُعدّ إثباتاً لاستلام الطلب لا عرضاً مالياً ولا التزاماً تعاقدياً.
            تصدر الأسعار النهائية في عرض السعر الرسمي المرسل من وريد. يمكن التحقق من الطلب بالرقم المرجعي أو رمز QR أعلاه.
        </p>
        <div class="foot-bar">
            <span class="brand">وريد — منصتك التقنية المتكاملة</span>
            <span class="ltr">{{ $contactEmail }} · wareed.vip</span>
        </div>
    </footer>
</article>

<script>
/*
 * ضبط الكثافة تلقائياً: يقيس ارتفاع المحتوى الفعلي ويطبّق مستوى كثافة أعلى
 * إن تجاوز صفحة A4، فيبقى المستند صفحة واحدة مهما طالت إجابات العميل.
 */
(function () {
    'use strict';
    var PAGE_PX = 297 * (96 / 25.4);
    var MIN_ZOOM = 0.62; // أقل من ذلك يصبح النص غير مريح للقراءة
    var sheet = document.querySelector('.sheet');
    if (!sheet) return;

    function naturalHeight() {
        var prev = sheet.style.minHeight;
        sheet.style.minHeight = '0';
        var h = sheet.scrollHeight;
        sheet.style.minHeight = prev;
        return h;
    }

    function clearScale() {
        sheet.style.zoom = '';
        sheet.style.width = '';
        sheet.style.minHeight = '';
    }

    /** تصغير متناسب مع تعويض العرض والارتفاع ليبقى المقاس المطبوع A4 */
    function scaleToFit(height) {
        var z = Math.max(MIN_ZOOM, (PAGE_PX - 2) / height);
        sheet.style.zoom = z;
        sheet.style.width = 'calc(210mm / ' + z + ')';
        sheet.style.minHeight = 'calc(297mm / ' + z + ')';
    }

    function fit() {
        document.body.classList.remove('fit-1', 'fit-2');
        clearScale();
        if (naturalHeight() <= PAGE_PX) return;

        document.body.classList.add('fit-1');
        if (naturalHeight() <= PAGE_PX) return;

        document.body.classList.remove('fit-1');
        document.body.classList.add('fit-2');
        var h = naturalHeight();
        if (h <= PAGE_PX) return;

        // ما زال المحتوى أطول من الصفحة: تصغير متناسب كملاذ أخير
        scaleToFit(h);
    }

    if (document.fonts && document.fonts.ready) document.fonts.ready.then(fit);
    fit();
    window.addEventListener('beforeprint', fit);
    window.addEventListener('resize', fit);
})();
</script>

</body>
</html>
