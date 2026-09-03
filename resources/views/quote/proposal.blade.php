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

        /* الجداول الكثيفة (بنود، جداول زمنية، دفعات) لها عرض أدنى ثابت بالمليمتر؛ على الجوّال
           تصير قابلة للتمرير أفقياً داخل حدودها (أدناه) بدل أن تُخرج الصفحة كلها عن عرضها.
           التفعيل يبقى مقصوراً على الجوّال لأنه ينشئ سياق تنسيق جديداً يمنع دمج الهوامش
           المتجاورة (margin collapsing)، فتفعيله دائماً يزيد ارتفاع الصفحة المطبوعة بلا داع */
        .tbl-wrap { max-width: 100%; }

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
        .note-list { margin: 0; padding-inline-start: 4.5mm; }
        .note-list li { font-size: 8.2pt; color: var(--muted); line-height: 1.75; padding-inline-start: 1mm; }
        .note-list li::marker { color: var(--blue); font-weight: 700; }
        .opt-note { margin-top: 2.4mm; font-size: 8pt; color: var(--muted); line-height: 1.7; }
        .opt-totals { margin-top: 2.4mm; display: flex; align-items: center; gap: 3mm; flex-wrap: wrap; }
        .opt-totals table { width: 78mm; border-collapse: collapse; }
        .opt-totals td { padding: 1.4mm 4mm; font-size: 8.4pt; color: var(--muted); border-bottom: 1px solid var(--line-soft); }
        .opt-totals td:last-child { text-align: start; font-weight: 700; color: var(--ink); font-variant-numeric: tabular-nums; white-space: nowrap; }
        .opt-totals tr.opt-grand td { border-bottom: 0; background: #f2f5fc; font-weight: 700; color: var(--ink); }
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

        /* صندوق قرار العميل */
        .decide {
            width: 210mm; max-width: 100%; margin: 16px auto 0; background: #fff;
            border: 1px solid var(--line); border-radius: 16px; padding: 9mm 10mm;
            box-shadow: 0 18px 44px -30px rgba(13, 24, 48, .45);
        }
        .decide-title { font-size: 1.05rem; font-weight: 700; }
        .decide-lead { font-size: .84rem; color: var(--muted); margin-top: .3rem; }
        .decide-lead b { color: var(--ink); }
        .decide-opts { display: grid; grid-template-columns: repeat(3, 1fr); gap: .7rem; margin-top: 1rem; }
        /* لون كل خيار يتبع حالته: اعتماد أخضر، تخفيض كهرماني، اعتذار محايد — بألوان صندوق التأكيد نفسها */
        .d-opt {
            --d: #047857; --d-bg: #ecfdf5; --d-line: #a7f3d0; --d-ring: rgba(4, 120, 87, .14);
            position: relative; display: block; cursor: pointer; overflow: hidden;
            padding: .85rem .95rem; padding-inline-start: 1.15rem;
            border: 1.5px solid var(--line); border-radius: 12px; background: #fff; transition: .15s;
        }
        .d-opt[data-opt="discount"] { --d: #b45309; --d-bg: #fffbeb; --d-line: #fde68a; --d-ring: rgba(180, 83, 9, .14); }
        .d-opt[data-opt="declined"] { --d: #475569; --d-bg: #f8fafc; --d-line: #cbd5e1; --d-ring: rgba(71, 85, 105, .14); }
        /* شريط جانبي بلون الحالة يميّزها قبل الاختيار، ويكتمل لونه عند اختيارها */
        .d-opt::before { content: ""; position: absolute; inset-block: 0; inset-inline-start: 0; width: 4px; background: var(--d); opacity: .4; }
        .d-opt:hover { border-color: var(--d-line); background: var(--d-bg); }
        .d-opt.on { border-color: var(--d); background: var(--d-bg); box-shadow: 0 0 0 3px var(--d-ring); }
        .d-opt.on::before { opacity: 1; }
        .d-opt input { position: absolute; opacity: 0; pointer-events: none; }
        .d-name { display: block; font-size: .92rem; font-weight: 700; color: var(--d); }
        .d-lead { display: block; font-size: .76rem; color: var(--muted); margin-top: .2rem; line-height: 1.65; }
        .decide-note { margin-top: .9rem; }
        .decide-note label { display: block; font-size: .78rem; color: var(--muted); margin-bottom: .3rem; }
        .decide-note textarea {
            width: 100%; padding: .6rem .8rem; border-radius: 10px; border: 1px solid var(--line);
            font: inherit; font-size: .86rem; color: var(--ink); background: #fff; resize: vertical;
        }
        .decide-note textarea:focus { outline: 2px solid rgba(37, 99, 235, .3); border-color: var(--blue); }
        .decide-send {
            margin-top: 1rem; display: inline-flex; align-items: center; gap: .45rem; cursor: pointer;
            border: 0; border-radius: 10px; padding: .7rem 1.6rem; font: inherit; font-size: .9rem;
            font-weight: 700; color: #fff; background: var(--grad);
        }
        .decide-send:disabled { opacity: .45; cursor: not-allowed; }
        .decide-send .ic { width: 18px; height: 18px; }
        .decide-err { margin-top: .5rem; font-size: .8rem; color: #b91c1c; }
        /* لون التأكيد يتبع نوع القرار: اعتماد أخضر، تخفيض كهرماني، اعتذار محايد */
        .decide-done {
            --dd: #047857; --dd-soft: #d1fae5; --dd-bg: #ecfdf5; --dd-line: #a7f3d0;
            margin-bottom: 1.1rem; padding: .9rem 1rem; border-radius: 12px;
            background: var(--dd-bg); border: 1px solid var(--dd-line);
        }
        .decide-done.is-discount { --dd: #b45309; --dd-soft: #fef3c7; --dd-bg: #fffbeb; --dd-line: #fde68a; }
        .decide-done.is-declined { --dd: #475569; --dd-soft: #e2e8f0; --dd-bg: #f8fafc; --dd-line: #cbd5e1; }
        .dd-badge {
            display: inline-block; font-size: .74rem; font-weight: 700; color: var(--dd);
            background: var(--dd-soft); border-radius: 99px; padding: .15rem .7rem;
        }
        .dd-msg { font-size: .88rem; color: var(--dd); margin-top: .4rem; line-height: 1.8; font-weight: 600; }
        .dd-note { font-size: .82rem; color: var(--dd); margin-top: .35rem; }
        .dd-meta { font-size: .76rem; color: var(--dd); opacity: .8; margin-top: .4rem; }
        @media (max-width: 640px) { .decide-opts { grid-template-columns: 1fr; } }

        /* صندوق رفع متطلبات المشروع — يظهر بعد اعتماد العرض فقط */
        .reqs {
            width: 210mm; max-width: 100%; margin: 16px auto 0; background: #fff;
            border: 1px solid var(--line); border-radius: 16px; padding: 9mm 10mm;
            box-shadow: 0 18px 44px -30px rgba(13, 24, 48, .45);
        }
        .reqs-title { font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: .5rem; }
        .reqs-title .ic { width: 20px; height: 20px; color: var(--blue); }
        .reqs-lead { font-size: .84rem; color: var(--muted); margin-top: .3rem; line-height: 1.75; }
        .reqs-lead b { color: var(--ink); }

        .reqs-list { margin-top: 1rem; display: flex; flex-direction: column; gap: .5rem; }
        .reqs-item {
            display: flex; align-items: center; gap: .7rem; padding: .6rem .8rem;
            border: 1px solid var(--line); border-radius: 10px; background: #fafcff;
        }
        .reqs-item .ic { width: 18px; height: 18px; color: var(--blue); flex: none; }
        .reqs-item .ri-name { font-size: .86rem; font-weight: 700; color: var(--ink); overflow-wrap: anywhere; }
        .reqs-item .ri-desc { font-size: .78rem; color: var(--muted); margin-top: .15rem; overflow-wrap: anywhere; }
        .reqs-item .ri-meta { margin-inline-start: auto; font-size: .72rem; color: var(--faint); white-space: nowrap; text-align: end; }

        .reqs-form { margin-top: 1.1rem; padding-top: 1rem; border-top: 1px solid var(--line-soft); }
        .reqs-form-title { font-size: .82rem; font-weight: 700; color: var(--ink); }
        .reqs-rows { margin-top: .7rem; display: flex; flex-direction: column; gap: .6rem; }
        .reqs-row {
            display: grid; grid-template-columns: 1fr 1fr auto; gap: .6rem; align-items: center;
            padding: .55rem; border: 1.5px dashed var(--line); border-radius: 10px;
        }
        .reqs-row input[type="file"] {
            font: inherit; font-size: .8rem; color: var(--muted); width: 100%;
        }
        .reqs-row input[type="text"] {
            width: 100%; padding: .5rem .7rem; border-radius: 8px; border: 1px solid var(--line);
            font: inherit; font-size: .82rem; color: var(--ink); background: #fff;
        }
        .reqs-row input[type="text"]:focus { outline: 2px solid rgba(37, 99, 235, .3); border-color: var(--blue); }
        .reqs-rm {
            cursor: pointer; border: 1px solid var(--line); background: #fff; border-radius: 8px;
            width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; color: #b91c1c;
        }
        .reqs-rm .ic { width: 15px; height: 15px; }
        .reqs-add {
            margin-top: .7rem; display: inline-flex; align-items: center; gap: .4rem; cursor: pointer;
            border: 1.5px dashed var(--line); border-radius: 10px; background: #fff; color: var(--blue);
            padding: .5rem 1rem; font: inherit; font-size: .82rem; font-weight: 700;
        }
        .reqs-add .ic { width: 16px; height: 16px; }
        .reqs-hint { margin-top: .6rem; font-size: .74rem; color: var(--faint); }
        .reqs-send {
            margin-top: 1rem; display: inline-flex; align-items: center; gap: .45rem; cursor: pointer;
            border: 0; border-radius: 10px; padding: .7rem 1.6rem; font: inherit; font-size: .9rem;
            font-weight: 700; color: #fff; background: var(--grad);
        }
        .reqs-send .ic { width: 18px; height: 18px; }
        .reqs-err { margin-top: .5rem; font-size: .8rem; color: #b91c1c; }
        .reqs-ok { margin-top: .5rem; font-size: .8rem; color: #047857; font-weight: 600; }
        @media (max-width: 640px) { .reqs-row { grid-template-columns: 1fr; } }

        /* عمود حالة المرحلة داخل الجدول الزمني — يظهر بعد اعتماد العرض */
        .sc-badge {
            display: inline-flex; align-items: center; gap: .25rem; padding: .1rem .55rem; border-radius: 99px;
            font-size: 6.8pt; font-weight: 700; white-space: nowrap;
        }
        .sc-badge.done { color: #047857; background: rgba(4, 120, 87, .1); }
        .sc-badge.now { color: #b45309; background: rgba(180, 83, 9, .1); }
        .sc-badge.next { color: #55638a; background: rgba(85, 99, 138, .08); }

        /* ── تقسيم الطباعة: كل صفحة صندوق A4 مستقل بترويسته وتذييله ── */
        .pg { display: none; }
        .pg-foot {
            display: none; align-items: center; justify-content: space-between; gap: 4mm;
            padding-top: 2.5mm; border-top: 1px solid var(--line-soft);
            font-size: 7pt; color: var(--faint);
        }
        .pg-foot b { color: var(--muted); font-weight: 600; letter-spacing: .4px; }
        .pg-foot .no { font-weight: 700; color: var(--muted); font-variant-numeric: tabular-nums; }
        .pg-head {
            display: none; align-items: center; justify-content: space-between; gap: 4mm;
            padding-bottom: 2.5mm; margin-bottom: 4mm; border-bottom: 1px solid var(--line-soft);
        }
        .pg-head .who { display: flex; align-items: center; gap: 2.5mm; font-size: 9pt; font-weight: 700; }
        .pg-head .mark { width: 7mm; height: 7mm; }
        .pg-head .ref { font-size: 7.6pt; color: var(--faint); direction: ltr; }

        @media print {
            @page { size: A4; margin: 0; }

            /* الصفحات المبنية بالسكربت تحلّ محلّ التدفّق الواحد */
            body.paged .sheet { display: none !important; }
            body.paged .pg {
                display: flex !important; flex-direction: column;
                width: 210mm; height: 297mm; padding: 11mm 12mm 8mm;
                background: #fff; overflow: hidden; break-after: page; position: relative;
            }
            body.paged .pg:last-of-type { break-after: auto; }
            body.paged .pg::before {
                content: ""; position: absolute; inset-inline: 0; top: 0; height: 4mm; background: var(--grad);
            }
            body.paged .pg > .pg-body { flex: 1; min-height: 0; overflow: hidden; }
            body.paged .pg > .pg-head { display: flex !important; }
            body.paged .pg > .pg-foot { display: flex !important; margin-top: auto; }
            /* الصفحة الأولى تحمل ترويسة المستند الكاملة لا المصغّرة */
            body.paged .pg:first-of-type > .pg-head { display: none !important; }
            html, body { background: #fff; padding: 0; margin: 0; }
            .toolbar, .decide, .reqs { display: none !important; }
            .sheet { width: 210mm; min-height: 0; box-shadow: none; margin: 0; padding: 11mm 12mm 8mm; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

            /* المستند يمتد على ما يلزم من الصفحات — والانتقال بينها يبقى مرتّباً */
            table.items thead, table.pay-table thead { display: table-header-group; }
            table.items tr, table.pay-table tr { break-inside: avoid; }
            .section-title { break-after: avoid; }
            .totals, .bank, .term-box, .refbar, .party, footer.foot { break-inside: avoid; }
            .parties, section.pay, .terms { break-inside: auto; }
        }
        @media screen and (max-width: 230mm) {
            /* شبكة أمان: أي عنصر يفلت بعرضه (نادراً، بعد تمرير الجداول الكثيفة أدناه)
               يبقى قابلاً للتمرير داخل حدوده بدل أن يُخرج الصفحة كلها عن عرض الشاشة ويصغّرها */
            html, body { overflow-x: hidden; }
            body { padding: 14px 8px 40px; }
            .sheet { width: 100%; min-height: auto; padding: 9mm 7mm; }
            .parties, .terms, .pay { grid-template-columns: 1fr; }
            .refbar { flex-wrap: wrap; }
            .refbar-meta, .refbar-qr { border-inline-start: 0; border-top: 1px solid var(--line); }
            .totals table { width: 100%; }
            .tbl-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        }
        /* الجوّال تحديداً: نصوص العرض والطباعة بمقاس المليمتر مريحة على A4 لكنها صغيرة على شاشة
           بهذا الحجم، فتُكبَّر أهم العناصر التي يقرؤها العميل فعلياً — البنود والإجماليات والعناوين */
        @media screen and (max-width: 480px) {
            body { font-size: 15px; }
            .head-title h1 { font-size: 19pt; }
            .section-title { font-size: 12pt; }
            .party-name { font-size: 12.5pt; }
            .party-row { font-size: 9.6pt; }
            table.items th, table.pay-table th { font-size: 9.4pt; padding: 2.8mm 3mm; }
            table.items td, table.pay-table td { font-size: 10pt; padding: 3mm 3mm; }
            td.it small { font-size: 8.8pt; }
            table.pay-table td small { font-size: 8.4pt; }
            tr.phase .ph-name { font-size: 9.6pt; }
            tr.phase .ph-sum { font-size: 9pt; }
            .totals td { font-size: 10.5pt; padding: 2.6mm 4mm; }
            .totals tr.grand td { font-size: 13pt; }
            .opt-totals td { font-size: 9.6pt; }
            .note-list li, .term-box p, .opt-note { font-size: 9.4pt; }
            .bank .brow { font-size: 9.2pt; }
            .refbar-num { font-size: 14pt; }
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
    <header class="head" data-pg-unit="head">
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
                <div>البريد الإلكتروني: <span dir="ltr">{{ $contactEmail }}</span></div>
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

    <section class="refbar" data-pg-unit="refbar">
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

    <section class="parties" data-pg-unit="parties">
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

    <div class="section-title" data-pg-unit="items-title">
        بنود عرض السعر
        <span class="en">QUOTATION ITEMS</span>
    </div>
    <div class="tbl-wrap">
    <table class="items" data-pg-unit="items-table">
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
    </div>

    <div class="totals" data-pg-unit="totals">
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
        <div class="pg-unit" data-pg-unit="extras">
        <div class="section-title">
            خدمات إضافية اختيارية
            <span class="en">OPTIONAL ADD-ONS</span>
        </div>
        <p class="opt-note">
            بنود معروضة للاطلاع فقط ولم تُحتسب ضمن الإجمالي المستحق أعلاه.
            يمكن إضافة أيٍّ منها لاحقاً بطلب منكم، ويُصدَر لها ملحق مستقل للعرض.
        </p>
        <div class="tbl-wrap">
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
        </div>
        <div class="opt-totals">
            <table>
                <tr>
                    <td>الإجمالي قبل الخصم</td>
                    <td>{{ $money($quote['extras_subtotal']) }} {{ $cur }}</td>
                </tr>
                @if ($quote['extras_discount'] > 0)
                    <tr>
                        <td>الخصم ({{ $pct($quote['extras_discount_percent']) }}%)</td>
                        <td>− {{ $money($quote['extras_discount']) }} {{ $cur }}</td>
                    </tr>
                @endif
                @if ($quote['extras_vat_percent'] > 0)
                    <tr>
                        <td>ضريبة القيمة المضافة ({{ $pct($quote['extras_vat_percent']) }}%)</td>
                        <td>{{ $money($quote['extras_vat']) }} {{ $cur }}</td>
                    </tr>
                @endif
                <tr class="opt-grand">
                    <td>الإجمالي لو طُلبت جميعها</td>
                    <td>{{ $money($quote['extras_total']) }} {{ $cur }}</td>
                </tr>
            </table>
            <span class="opt-flag">غير مشمولة في الإجمالي المستحق</span>
        </div>
        </div>
    @endif

    @if ($quote['schedule'])
        <div class="pg-unit" data-pg-unit="schedule">
        <div class="section-title">
            الجدول الزمني للتسليم
            <span class="en">DELIVERY SCHEDULE</span>
        </div>
        @php $showScheduleStatus = ($decision['choice'] ?? null) === 'approved'; @endphp
        <div class="tbl-wrap">
        <table class="pay-table sched">
            <thead>
                <tr>
                    <th style="width:9mm">م</th>
                    <th>المرحلة</th>
                    <th style="width:34mm">تاريخ البدء</th>
                    <th style="width:34mm">تاريخ النهاية</th>
                    @if ($showScheduleStatus)<th style="width:24mm">الحالة</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach ($quote['schedule'] as $stageNo => $stage)
                    @php
                        $scState = null;
                        if ($showScheduleStatus) {
                            $scState = match (true) {
                                $flow['stage'] === 'delivered' => 'done',
                                $stage['end'] && now()->gt($stage['end']) => 'done',
                                $stage['start'] && now()->gte($stage['start']) => 'now',
                                default => 'next',
                            };
                        }
                    @endphp
                    <tr>
                        <td class="n">{{ $stageNo + 1 }}</td>
                        <td><b>{{ $stage['phase'] ?: 'مرحلة' }}</b></td>
                        <td class="due">{{ $stage['start'] ? $fmtShort($stage['start']) : '—' }}</td>
                        <td class="due">{{ $stage['end'] ? $fmtShort($stage['end']) : 'يُحدَّد لاحقاً' }}</td>
                        @if ($showScheduleStatus)
                            <td>
                                <span class="sc-badge {{ $scState }}">
                                    {{ match ($scState) { 'done' => 'مكتملة', 'now' => 'جارية الآن', default => 'قادمة' } }}
                                </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        </div>
    @endif

    @if ($quote['payments'] || $bank['has'])
        <div class="pg-unit" data-pg-unit="payments">
        <div class="section-title">
            الدفعات وطريقة السداد
            <span class="en">PAYMENT TERMS</span>
        </div>
        @php $hasDue = collect($quote['payments'])->contains(fn ($p) => (bool) $p['due']); @endphp
        <section @class(['pay', 'with-due' => $hasDue])>
            @if ($quote['payments'])
                <div class="tbl-wrap">
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
                </div>
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
        </div>
    @endif

    <section class="terms" data-pg-unit="terms">
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
                    <ol class="note-list">
                        @foreach ($quote['notes'] as $noteLine)
                            <li>{{ $noteLine }}</li>
                        @endforeach
                    </ol>
                @else
                    لاعتماد العرض والبدء في التنفيذ يرجى التواصل معنا عبر البريد الإلكتروني أو الهاتف الموضّحين أعلاه،
                    مع ذكر الرقم المرجعي للطلب.
                @endif
            </p>
        </div>
    </section>

    <footer class="foot" data-pg-unit="footer">
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

{{-- قرار العميل على العرض — للشاشة فقط، لا يظهر في النسخة المطبوعة --}}
<section class="decide" id="decide">
    @if (session('decision_saved') && $decision)
        <div @class(['decide-done', 'is-'.$decision['choice']])>
            <div class="dd-badge">{{ $decision['label'] }}</div>
            <p class="dd-msg">{{ $decision['done'] }}</p>
            @if ($decision['note'])
                <p class="dd-note">ملاحظتك: {{ $decision['note'] }}</p>
            @endif
            <p class="dd-meta">
                سُجّل قرارك في {{ $decision['at']?->format('Y/m/d — H:i') }}.
                لتغييره، اختر خياراً آخر من الأسفل.
            </p>
        </div>
    @endif

    <h2 class="decide-title">قرارك على هذا العرض</h2>
    <p class="decide-lead">
        اختر ما يناسبك وسيصل قرارك لفريق وريد فوراً.
        @if ($decision && ! session('decision_saved'))
            <b>قرارك المسجّل حالياً: {{ $decision['label'] }}</b> — يمكنك تغييره.
        @endif
    </p>

    <form method="POST" action="{{ $decisionUrl }}" class="decide-form" data-decide>
        @csrf

        <div class="decide-opts">
            @foreach (\App\Http\Controllers\QuoteController::DECISIONS as $key => $option)
                <label @class(['d-opt', 'on' => ($decision['choice'] ?? null) === $key])
                       data-opt="{{ $key }}" data-note-label="{{ $option['note_label'] }}">
                    <input type="radio" name="choice" value="{{ $key }}" required
                           @checked(($decision['choice'] ?? null) === $key)>
                    <span class="d-name">{{ $option['label'] }}</span>
                    <span class="d-lead">{{ $option['lead'] }}</span>
                </label>
            @endforeach
        </div>

        {{-- حقل ملاحظة واحد يتغيّر عنوانه حسب الاختيار، فيعمل النموذج حتى بلا جافاسكربت --}}
        <div class="decide-note">
            <label for="decide-note" data-note-target>ملاحظة تودّ إضافتها (اختياري)</label>
            <textarea id="decide-note" name="note" rows="3"
                      placeholder="اكتب هنا…">{{ old('note', $decision['note'] ?? '') }}</textarea>
        </div>

        <button type="submit" class="decide-send">
            <svg class="ic"><use href="#i-check"/></svg> إرسال قراري
        </button>

        @error('choice')<p class="decide-err">{{ $message }}</p>@enderror
        @error('note')<p class="decide-err">{{ $message }}</p>@enderror
    </form>
</section>

@if (($decision['choice'] ?? null) === 'approved')
    {{-- متطلبات المشروع: ملفات الهوية البصرية وبيانات المنتجات وغيرها — تظهر فقط بعد اعتماد العرض --}}
    <section class="reqs" id="requirements">
        <h2 class="reqs-title"><svg class="ic"><use href="#i-box"/></svg> متطلبات المشروع</h2>
        <p class="reqs-lead">
            ارفع ملفات هويتك البصرية أو شعار المتجر، وبيانات منتجاتك (أسماء وأوصاف وأسعار وصور إن توفّرت)،
            وأي ملفات أخرى يحتاجها فريق وريد، مع وصف مختصر لكل ملف.
            @if ($flow['stage'] === 'awaiting_requirements')
                <b>لم يبدأ التنفيذ الفعلي بعد — يبدأ فور رفعك لأوّل ملف.</b>
            @endif
        </p>

        @if (session('requirements_saved'))
            <p class="reqs-ok">
                <svg class="ic" style="width:16px;height:16px;display:inline-block;vertical-align:-3px"><use href="#i-check"/></svg>
                استلمنا ملفاتك بنجاح — شكراً لتعاونك.
            </p>
        @endif

        @if (count($requirements))
            <div class="reqs-list">
                @foreach ($requirements as $file)
                    <div class="reqs-item">
                        <svg class="ic"><use href="#i-document"/></svg>
                        <div>
                            <div class="ri-name">{{ $file['name'] }}</div>
                            @if ($file['desc'])<div class="ri-desc">{{ $file['desc'] }}</div>@endif
                        </div>
                        <span class="ri-meta">
                            {{ $file['size_h'] }}
                            @if ($file['uploaded_at']) · {{ $fmtShort($file['uploaded_at']) }} @endif
                        </span>
                    </div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ $requirementsUrl }}" enctype="multipart/form-data" class="reqs-form" data-reqs>
            @csrf
            <div class="reqs-form-title">{{ count($requirements) ? 'إضافة ملفات أخرى' : 'رفع الملفات' }}</div>

            <div class="reqs-rows" data-reqs-rows>
                <div class="reqs-row" data-reqs-row>
                    <input type="file" name="files[0][file]" required>
                    <input type="text" name="files[0][desc]" placeholder="وصف الملف (اختياري) — مثال: شعار المتجر">
                    <button type="button" class="reqs-rm" data-reqs-rm aria-label="إزالة الملف" hidden>
                        <svg class="ic"><use href="#i-trash"/></svg>
                    </button>
                </div>
            </div>

            <button type="button" class="reqs-add" data-reqs-add>
                <svg class="ic"><use href="#i-plus"/></svg> إضافة ملف آخر
            </button>
            <p class="reqs-hint">حتى 10 ملفات في الرفعة الواحدة، وحجم أقصى 10 ميجابايت لكل ملف.</p>

            <button type="submit" class="reqs-send">
                <svg class="ic"><use href="#i-check"/></svg> رفع الملفات
            </button>

            @error('files')<p class="reqs-err">{{ $message }}</p>@enderror
            @error('files.0.file')<p class="reqs-err">{{ $message }}</p>@enderror
        </form>
    </section>
@endif

<script>
/* إبراز الخيار المحدَّد وتغيير عنوان حقل الملاحظة — النموذج يعمل بدونه أيضاً */
(function () {
    'use strict';
    var form = document.querySelector('[data-decide]');
    if (!form) return;

    var options = form.querySelectorAll('[data-opt]');
    var noteLabel = form.querySelector('[data-note-target]');

    function sync() {
        options.forEach(function (option) {
            var picked = option.querySelector('input').checked;
            option.classList.toggle('on', picked);
            if (picked && noteLabel) noteLabel.textContent = option.dataset.noteLabel;
        });
    }

    form.addEventListener('change', sync);
    sync();
})();
</script>

<script>
/* إضافة/حذف صفوف رفع الملفات في نموذج متطلبات المشروع — النموذج يعمل بصفٍّ واحد بدونه أيضاً */
(function () {
    'use strict';
    var wrap = document.querySelector('[data-reqs-rows]');
    if (!wrap) return;

    var addBtn = document.querySelector('[data-reqs-add]');
    var MAX_ROWS = 10;

    function refreshRemoveButtons() {
        var rows = wrap.querySelectorAll('[data-reqs-row]');
        rows.forEach(function (row) {
            var rm = row.querySelector('[data-reqs-rm]');
            if (rm) rm.hidden = rows.length <= 1;
        });
        if (addBtn) addBtn.hidden = rows.length >= MAX_ROWS;
    }

    function addRow() {
        var rows = wrap.querySelectorAll('[data-reqs-row]');
        if (rows.length >= MAX_ROWS) return;

        var index = rows.length;
        var row = document.createElement('div');
        row.className = 'reqs-row';
        row.setAttribute('data-reqs-row', '');
        row.innerHTML =
            '<input type="file" name="files[' + index + '][file]" required>' +
            '<input type="text" name="files[' + index + '][desc]" placeholder="وصف الملف (اختياري)">' +
            '<button type="button" class="reqs-rm" data-reqs-rm aria-label="إزالة الملف">' +
            '<svg class="ic"><use href="#i-trash"/></svg></button>';

        wrap.appendChild(row);
        refreshRemoveButtons();
    }

    if (addBtn) addBtn.addEventListener('click', addRow);

    wrap.addEventListener('click', function (e) {
        var rm = e.target.closest('[data-reqs-rm]');
        if (!rm) return;

        var rows = wrap.querySelectorAll('[data-reqs-row]');
        if (rows.length <= 1) return;

        rm.closest('[data-reqs-row]').remove();
        // إعادة ترقيم الأسماء كي تبقى مصفوفة متتالية files[0], files[1]...
        wrap.querySelectorAll('[data-reqs-row]').forEach(function (row, i) {
            row.querySelectorAll('input').forEach(function (input) {
                input.name = input.name.replace(/files\[\d+\]/, 'files[' + i + ']');
            });
        });
        refreshRemoveButtons();
    });

    refreshRemoveButtons();
})();
</script>

<script>
/*
 * تقسيم الطباعة إلى صفحات A4 مستقلة، كل واحدة بترويستها المصغّرة (من الصفحة الثانية)
 * وتذييلها المرقّم «صفحة X من Y»، دون قصّ أي محتوى مهما طال جدول البنود.
 *
 * يعمل بقياس فعلي: يُستنسخ كل عنصر (رأس المستند، جدول البنود صفاً صفاً، الإجماليات،
 * الباقات الاختيارية، الجدول الزمني، الدفعات، الشروط، التذييل القانوني) داخل صفحة
 * جارية، فإن فاض بها المحتوى (scrollHeight أكبر من المساحة المتاحة) يُنقل العنصر
 * لصفحة جديدة. يُبنى فقط عند الطباعة الفعلية (beforeprint) ويُزال بعدها (afterprint)
 * فتبقى الصفحة التفاعلية على الشاشة كما هي دون أي أثر.
 *
 * الأصل يبقى دون تعديل (نسخ لا نقل)، فإن تعطّل هذا السكربت لأي سبب يطبع المتصفح
 * التدفّق الواحد العادي بقواعد الطباعة الأساسية (تكرار رأس الجدول، منع القطع
 * منتصف صفّ) — نتيجة سليمة أيضاً وإن بلا ترقيم صفحات.
 */
(function () {
    'use strict';

    var REFERENCE = @json($sr->reference);
    var CONTACT_EMAIL = @json($contactEmail);

    var UNITS_BEFORE_ITEMS = ['head', 'refbar', 'parties', 'items-title'];
    var UNITS_AFTER_ITEMS = ['totals', 'extras', 'schedule', 'payments', 'terms', 'footer'];

    function fits(body) {
        return body.scrollHeight <= body.clientHeight + 1;
    }

    function markMarkup() {
        var mark = document.querySelector('[data-pg-unit="head"] .head-org .mark');
        return mark ? mark.outerHTML : '';
    }

    function makePage(isFirst) {
        var pg = document.createElement('div');
        pg.className = 'pg';

        var head = document.createElement('div');
        head.className = 'pg-head';
        if (!isFirst) {
            head.innerHTML =
                '<span class="who">' + markMarkup() + ' وريد</span>' +
                '<span class="ref" dir="ltr">' + REFERENCE + '</span>';
        }

        var body = document.createElement('div');
        body.className = 'pg-body';

        var foot = document.createElement('div');
        foot.className = 'pg-foot';
        foot.innerHTML =
            '<span>وريد لتقنية المعلومات · ' + CONTACT_EMAIL + '</span>' +
            '<span>عرض سعر · <b dir="ltr">' + REFERENCE + '</b> — صفحة ' +
            '<span class="no" data-pg-cur></span> من <span class="no" data-pg-total></span></span>';

        pg.appendChild(head);
        pg.appendChild(body);
        pg.appendChild(foot);
        document.body.appendChild(pg);

        return {el: pg, body: body, foot: foot};
    }

    /* يستنسخ عنصراً في متن الصفحة الجارية؛ فإن فاض بها ولم يكن أول محتوى فيها ينتقل لصفحة جديدة */
    function place(pages, original) {
        var page = pages[pages.length - 1];
        var clone = original.cloneNode(true);
        page.body.appendChild(clone);

        if (page.body.children.length === 1 || fits(page.body)) {
            return;
        }

        page.body.removeChild(clone);
        page = makePage(false);
        pages.push(page);
        page.body.appendChild(clone);
    }

    function newItemsTable(pages, originalThead) {
        var table = document.createElement('table');
        table.className = 'items';
        table.appendChild(originalThead.cloneNode(true));
        var tbody = document.createElement('tbody');
        table.appendChild(tbody);
        pages[pages.length - 1].body.appendChild(table);

        return tbody;
    }

    function paginate() {
        var sheet = document.querySelector('.sheet');
        var table = sheet && sheet.querySelector('[data-pg-unit="items-table"]');
        if (!sheet || !table) return;

        var originalThead = table.querySelector('thead');
        var rows = Array.prototype.slice.call(table.querySelectorAll('tbody tr'));

        document.body.classList.add('paged');
        var pages = [makePage(true)];

        UNITS_BEFORE_ITEMS.forEach(function (key) {
            var node = sheet.querySelector('[data-pg-unit="' + key + '"]');
            if (node) place(pages, node);
        });

        var currentTbody = newItemsTable(pages, originalThead);
        rows.forEach(function (row) {
            var clone = row.cloneNode(true);
            currentTbody.appendChild(clone);

            var page = pages[pages.length - 1];
            if (currentTbody.children.length === 1 || fits(page.body)) {
                return;
            }

            currentTbody.removeChild(clone);
            page = makePage(false);
            pages.push(page);
            currentTbody = newItemsTable(pages, originalThead);
            currentTbody.appendChild(clone);
        });

        UNITS_AFTER_ITEMS.forEach(function (key) {
            var node = sheet.querySelector('[data-pg-unit="' + key + '"]');
            if (node) place(pages, node);
        });

        pages.forEach(function (page, i) {
            page.foot.querySelector('[data-pg-cur]').textContent = i + 1;
            page.foot.querySelector('[data-pg-total]').textContent = pages.length;
        });
    }

    function unpaginate() {
        document.querySelectorAll('.pg').forEach(function (pg) { pg.remove(); });
        document.body.classList.remove('paged');
    }

    window.addEventListener('beforeprint', function () {
        unpaginate();
        paginate();
    });
    window.addEventListener('afterprint', unpaginate);
})();
</script>

</body>
</html>
