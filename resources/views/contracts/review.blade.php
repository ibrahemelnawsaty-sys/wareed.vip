{{--
    عقد المشروع — نسخة العميل: مراجعة البنود بنداً بنداً واعتمادها على الشاشة،
    ونسخة A4 رسمية بالترويسة والتوقيعات والختم عند الطباعة/حفظ PDF.
--}}
@php
    $months = [1=>'يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
    $days = ['Saturday'=>'السبت','Sunday'=>'الأحد','Monday'=>'الإثنين','Tuesday'=>'الثلاثاء','Wednesday'=>'الأربعاء','Thursday'=>'الخميس','Friday'=>'الجمعة'];
    $fmt = fn ($d) => $days[$d->format('l')].'، '.$d->day.' '.$months[(int) $d->month].' '.$d->year.'م';
    $fmtShort = fn ($d) => $d->day.' '.$months[(int) $d->month].' '.$d->year.'م';
    $money = fn ($n) => number_format((float) $n, ((float) $n == (int) $n) ? 0 : 2);
    $contactEmail = setting('contact_email', 'info@wareed.vip');
    $contactPhone = setting('contact_phone', '+201055789056');
    $legalName = setting('legal_name');
    $taxNumber = setting('tax_number');
    $commercialRegister = setting('commercial_register');
    $docDate = $contract['approved_at'] ?? $contract['sent_at'] ?? $contract['created_at'] ?? now();
    $reviewing = $contract['status'] === 'sent';
    $open = array_values(array_filter($contract['clauses'], fn ($c) => ! $c['locked']));
    $decisions = \App\Support\Contracts::DECISIONS;
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>عقد {{ $contract['number'] }} — وريد</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
@include('quote._fonts')
    <style>
        :root {
            --ink: #0d1830; --muted: #55638a; --faint: #8493b5;
            --line: #dde5f4; --line-soft: #eef2fa;
            --blue: #2563eb; --violet: #7c3aed;
            --grad: linear-gradient(120deg, #3b82f6 0%, #8b5cf6 48%, #2dd4bf 100%);
            --band: #0d1830;
            --ease: cubic-bezier(.22,1,.36,1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        /* السمة hidden تتقدّم على أي display مُعلَن (الأزرار الكبسولية inline-flex مثلاً) */
        [hidden] { display: none !important; }
        body {
            font-family: 'Thmanyah', 'IBM Plex Sans Arabic', ui-sans-serif, system-ui, sans-serif;
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
        .head-title .copy.ok { color: #047857; background: rgba(4, 120, 87, .08); border-color: rgba(4, 120, 87, .3); }
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
        .refbar-ver { margin-top: 1.6mm; font-size: 7.6pt; font-weight: 700; color: var(--faint); }
        .refbar-ver b { color: var(--ink); font-variant-numeric: tabular-nums; }
        .refbar-ver .tag {
            display: inline-block; margin-inline-start: 1.4mm; padding: .5mm 2.4mm; border-radius: 99px;
            font-size: 6.8pt; font-weight: 700; color: var(--blue);
            background: rgba(37, 99, 235, .09); border: 1px solid rgba(37, 99, 235, .28);
        }

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

        .preamble { margin-top: 5mm; padding: 3.4mm 5mm; border-radius: 3mm; background: #f7f9fe; border: 1px solid var(--line); font-size: 8.8pt; line-height: 1.85; }
        .preamble b { font-weight: 700; }

        .sec-head { margin-top: 5.5mm; display: flex; align-items: center; gap: 3mm; font-size: 10.5pt; font-weight: 700; }
        .sec-head::before { content: ""; width: 1.2mm; height: 5mm; border-radius: 2px; background: var(--grad); }
        .sec-head .en { font-size: 7.4pt; font-weight: 600; letter-spacing: 2px; color: var(--faint); margin-inline-start: auto; }

        .art { margin-top: 3mm; border: 1px solid var(--line); border-radius: 3mm; overflow: hidden; background: #fff; }
        .art-head { display: flex; align-items: center; gap: 3mm; padding: 2.4mm 4.5mm; background: #f7f9fe; border-bottom: 1px solid var(--line-soft); }
        .art-no {
            flex: 0 0 auto; min-width: 7mm; height: 7mm; padding: 0 2mm; border-radius: 99px;
            background: var(--band); color: #fff; font-size: 8pt; font-weight: 700;
            display: inline-flex; align-items: center; justify-content: center; font-variant-numeric: tabular-nums;
        }
        .art-title { font-size: 9.6pt; font-weight: 700; }
        .art-state {
            margin-inline-start: auto; display: inline-flex; align-items: center; gap: 1.2mm; white-space: nowrap;
            padding: .6mm 2.6mm; border-radius: 99px; font-size: 6.9pt; font-weight: 700;
        }
        .art-state .ic { width: 3.2mm; height: 3.2mm; }
        .art-state.ok { color: #047857; background: rgba(4, 120, 87, .1); }
        .art-state.edit { color: #b45309; background: rgba(180, 83, 9, .12); }
        .art-state.del { color: #b91c1c; background: rgba(185, 28, 28, .1); }
        .art-state.wait { color: var(--muted); background: rgba(85, 99, 138, .1); }
        .art-body { padding: 3mm 4.5mm; font-size: 8.9pt; line-height: 1.85; color: var(--ink); white-space: pre-line; overflow-wrap: anywhere; }

        .bank { margin-top: 5mm; border: 1px solid rgba(37, 99, 235, .3); border-radius: 3mm; padding: 3mm 5mm; background: linear-gradient(150deg, rgba(59,130,246,.06), rgba(45,212,191,.04)); max-width: 100mm; }
        .bank h4 { font-size: 8.4pt; font-weight: 700; margin-bottom: 1.6mm; }
        .bank .brow { display: flex; gap: 2mm; font-size: 8pt; color: var(--muted); line-height: 1.75; }
        .bank .brow span { flex: 0 0 18mm; color: var(--faint); }
        .bank .brow b { color: var(--ink); font-weight: 600; overflow-wrap: anywhere; }

        /* التوقيعات: الشركة بختمها وتوقيعها، والعميل باعتماده الإلكتروني */
        .sign { margin-top: 6mm; display: grid; grid-template-columns: 1fr 1fr; gap: 5mm; }
        .sig { border: 1px solid var(--line); border-radius: 3mm; padding: 3.5mm 5mm 3mm; min-height: 62mm; display: flex; flex-direction: column; position: relative; }
        .sig-head { font-size: 7.8pt; font-weight: 700; color: var(--faint); letter-spacing: .4px; }
        .sig-party { font-size: 10pt; font-weight: 700; margin-top: 1mm; }
        .sig-sub { font-size: 7.8pt; color: var(--muted); }
        /* الختم في جهة البداية فوق سطر التوقيع مباشرة، والاسم والصفة في جهة النهاية — لا يتداخل مع النص */
        .sig-area { flex: 1; position: relative; margin-top: 2mm; min-height: 44mm; display: flex; align-items: flex-end; }
        .sig-stamp { position: absolute; inset-inline-start: 2mm; bottom: 12mm; width: 30mm; height: 30mm; object-fit: contain; }
        .sig-stamp-ph {
            position: absolute; inset-inline-start: 4mm; bottom: 13mm; width: 26mm; height: 26mm; border-radius: 50%;
            border: 1.5px dashed var(--line); color: var(--faint); font-size: 6.8pt; font-weight: 700;
            display: flex; align-items: center; justify-content: center; text-align: center; line-height: 1.4;
        }
        .sig-line { width: 100%; border-top: 1px solid var(--ink); padding-top: 1.6mm; }
        .sig-name { font-size: 9.4pt; font-weight: 700; }
        .sig-title { font-size: 7.8pt; color: var(--muted); line-height: 1.5; }
        .sig-meta { font-size: 7.2pt; color: var(--faint); margin-top: 1.2mm; line-height: 1.55; }
        .sig-meta b { color: #047857; font-weight: 700; }

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
            background: #fff; border: 1px solid var(--line); border-radius: 18px; padding: 11px 18px;
            box-shadow: 0 12px 30px -18px rgba(13, 24, 48, .4);
        }
        .toolbar p { font-size: .82rem; color: var(--muted); }
        .toolbar b { color: var(--ink); }
        .tb-actions { display: flex; gap: 9px; flex-wrap: wrap; }
        .tb-btn {
            display: inline-flex; align-items: center; gap: 7px; cursor: pointer; text-decoration: none;
            padding: 9px 22px; border-radius: 999px; border: 1px solid transparent;
            font: inherit; font-size: .88rem; font-weight: 700;
            transition: transform .32s var(--ease), box-shadow .32s var(--ease), border-color .28s;
        }
        .tb-primary { background: var(--grad); color: #fff; }
        .tb-ghost { background: #fff; color: var(--muted); border-color: var(--line); }
        .tb-btn .ic { width: 17px; height: 17px; }

        /* ── مراجعة البند (على الشاشة فقط) ── */
        .art-review { padding: .8rem 1rem 1rem; border-top: 1px dashed var(--line); background: #fbfcff; }
        .rv-lead { font-size: .8rem; color: var(--muted); margin-bottom: .55rem; }
        .rv-opts { display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; }
        .rv-opt {
            --d: #047857; --d-bg: #ecfdf5; --d-line: #a7f3d0; --d-ring: rgba(4, 120, 87, .14);
            position: relative; display: block; cursor: pointer; overflow: hidden;
            padding: .6rem .8rem; padding-inline-start: 1rem;
            border: 1.5px solid var(--line); border-radius: 14px; background: #fff; transition: .22s var(--ease);
        }
        .rv-opt[data-opt="edited"] { --d: #b45309; --d-bg: #fffbeb; --d-line: #fde68a; --d-ring: rgba(180, 83, 9, .14); }
        .rv-opt[data-opt="deleted"] { --d: #b91c1c; --d-bg: #fef2f2; --d-line: #fecaca; --d-ring: rgba(185, 28, 28, .12); }
        .rv-opt::before { content: ""; position: absolute; inset-block: 0; inset-inline-start: 0; width: 4px; background: var(--d); opacity: .4; }
        .rv-opt:hover { border-color: var(--d-line); background: var(--d-bg); }
        .rv-opt.on { border-color: var(--d); background: var(--d-bg); box-shadow: 0 0 0 3px var(--d-ring); }
        .rv-opt.on::before { opacity: 1; }
        .rv-opt input { position: absolute; opacity: 0; pointer-events: none; }
        .rv-name { display: flex; align-items: center; gap: .35rem; font-size: .84rem; font-weight: 700; color: var(--d); }
        .rv-name .ic { width: 15px; height: 15px; }
        .rv-note { margin-top: .6rem; }
        .rv-note label { display: block; font-size: .76rem; color: var(--muted); margin-bottom: .3rem; }
        .rv-note textarea {
            width: 100%; padding: .6rem .85rem; border-radius: 14px; border: 1px solid var(--line);
            font: inherit; font-size: .84rem; color: var(--ink); background: #fff; resize: vertical;
        }
        .rv-note textarea:focus { outline: 2px solid rgba(37, 99, 235, .3); border-color: var(--blue); }
        .rv-note.is-required textarea { border-color: #f59e0b; }
        .rv-prev { margin-top: .5rem; font-size: .78rem; color: var(--muted); }
        .rv-prev b { color: var(--ink); }
        .rv-locked { display: flex; align-items: center; gap: .5rem; font-size: .8rem; color: #047857; font-weight: 600; }
        .rv-locked .ic { width: 16px; height: 16px; }

        /* ── قرار العميل النهائي (على الشاشة فقط) ── */
        .decide {
            width: 210mm; max-width: 100%; margin: 16px auto 0; background: #fff;
            border: 1px solid var(--line); border-radius: 16px; padding: 9mm 10mm;
            box-shadow: 0 18px 44px -30px rgba(13, 24, 48, .45);
        }
        .decide-title { font-size: 1.05rem; font-weight: 700; }
        .decide-lead { font-size: .84rem; color: var(--muted); margin-top: .3rem; line-height: 1.8; }
        .decide-lead b { color: var(--ink); }
        .progress { margin-top: .9rem; display: flex; align-items: center; gap: .8rem; flex-wrap: wrap; }
        .progress .bar { flex: 1; min-width: 120px; height: 8px; border-radius: 99px; background: #eef2fa; overflow: hidden; }
        .progress .bar i { display: block; height: 100%; width: 0; background: var(--grad); transition: width .4s var(--ease); }
        .progress .txt { font-size: .8rem; font-weight: 700; color: var(--muted); font-variant-numeric: tabular-nums; }
        .decide-actions { margin-top: 1rem; display: flex; gap: .7rem; flex-wrap: wrap; align-items: center; }
        .decide-send {
            display: inline-flex; align-items: center; gap: .45rem; cursor: pointer;
            border: 0; border-radius: 999px; padding: .7rem 1.8rem; font: inherit; font-size: .9rem;
            font-weight: 700; color: #fff; background: var(--grad);
        }
        .decide-send.is-feedback { background: linear-gradient(120deg, #f59e0b, #d97706); }
        .decide-send:disabled { opacity: .45; cursor: not-allowed; }
        .decide-send .ic { width: 18px; height: 18px; }
        .decide-hint { font-size: .78rem; color: var(--muted); }
        .decide-err { margin-top: .6rem; font-size: .82rem; color: #b91c1c; font-weight: 600; }
        .decide-done {
            --dd: #047857; --dd-soft: #d1fae5; --dd-bg: #ecfdf5; --dd-line: #a7f3d0;
            margin-bottom: 1.1rem; padding: .9rem 1rem; border-radius: 12px;
            background: var(--dd-bg); border: 1px solid var(--dd-line);
        }
        .decide-done.is-feedback { --dd: #b45309; --dd-soft: #fef3c7; --dd-bg: #fffbeb; --dd-line: #fde68a; }
        .dd-badge { display: inline-block; font-size: .74rem; font-weight: 700; color: var(--dd); background: var(--dd-soft); border-radius: 99px; padding: .15rem .7rem; }
        .dd-msg { font-size: .88rem; color: var(--dd); margin-top: .4rem; line-height: 1.8; font-weight: 600; }
        .dd-meta { font-size: .76rem; color: var(--dd); opacity: .8; margin-top: .4rem; }

        /* ── النسخ الموقّعة بعد الاعتماد (على الشاشة فقط) ── */
        .signing { margin-top: 1rem; display: grid; grid-template-columns: 1fr 1fr; gap: .8rem; }
        .sc-box { border: 1px solid var(--line); border-radius: 14px; padding: .9rem 1rem; background: #fbfcff; }
        .sc-box.is-done { border-color: #a7f3d0; background: #f0fdf9; }
        .sc-t { display: flex; align-items: center; gap: .4rem; font-size: .84rem; font-weight: 700; }
        .sc-t .ic { width: 16px; height: 16px; color: var(--blue); }
        .sc-box.is-done .sc-t .ic { color: #047857; }
        .sc-file { margin-top: .45rem; font-size: .8rem; color: var(--muted); overflow-wrap: anywhere; line-height: 1.7; }
        .sc-file b { color: var(--ink); }
        .sc-muted { margin-top: .4rem; font-size: .78rem; color: var(--faint); line-height: 1.7; }
        .sc-form { margin-top: .6rem; display: flex; flex-direction: column; gap: .5rem; align-items: flex-start; }
        .sc-form input[type="file"] { font: inherit; font-size: .8rem; color: var(--muted); max-width: 100%; }
        .sc-hint { font-size: .74rem; color: var(--faint); }
        .sc-ok { margin: 0 0 .8rem; font-size: .84rem; font-weight: 700; color: #047857; }
        .sc-dl { margin-top: .6rem; }
        @media screen and (max-width: 640px) { .signing { grid-template-columns: 1fr; } }

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

        /* هندسة صفحات الطباعة تعيش خارج @media print عمداً (beforeprint يسبق أنماط الطباعة). */
        body.paged .pg {
            display: flex; flex-direction: column;
            width: 210mm; height: 297mm; padding: 11mm 12mm 8mm;
            background: #fff; overflow: hidden; position: relative;
        }
        body.paged .pg::before { content: ""; position: absolute; inset-inline: 0; top: 0; height: 4mm; background: var(--grad); }
        body.paged .pg > .pg-body { flex: 1; min-height: 0; overflow: hidden; }
        body.paged .pg > .pg-head { display: flex; }
        body.paged .pg > .pg-foot { display: flex; margin-top: auto; }
        body.paged .pg:first-of-type > .pg-head { display: none; }
        /* عناصر المراجعة التفاعلية لا تدخل الصفحات المقاسة ولا المطبوعة */
        body.paged .pg .art-review { display: none; }
        body.paged .pg .sec-head { margin-top: 0; }
        body.paged .pg .unit + .unit .sec-head { margin-top: 5.5mm; }
        body.paged .pg .foot { margin-top: 4mm; }

        @media screen {
            body.paged .pg { position: fixed; top: 0; inset-inline-start: -400vw; visibility: hidden; }
        }

        @media print {
            @page { size: A4; margin: 0; }
            body.paged .sheet { display: none !important; }
            body.paged .pg { break-after: page; }
            body.paged .pg:last-of-type { break-after: auto; }
            html, body { background: #fff; padding: 0; margin: 0; }
            .toolbar, .decide, .art-review { display: none !important; }
            .sheet { width: 210mm; min-height: 0; box-shadow: none; margin: 0; padding: 11mm 12mm 8mm; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .art, .refbar, .party, .bank, .sig, footer.foot { break-inside: avoid; }
            .sec-head { break-after: avoid; }
        }
        @media screen and (max-width: 230mm) {
            html, body { overflow-x: hidden; }
            body { padding: 14px 8px 40px; }
            .sheet { width: 100%; min-height: auto; padding: 9mm 7mm; }
            .parties, .sign { grid-template-columns: 1fr; }
            .refbar { flex-wrap: wrap; }
            .refbar-meta, .refbar-qr { border-inline-start: 0; border-top: 1px solid var(--line); }
            .bank { max-width: 100%; }
            .art-head { flex-wrap: wrap; }
        }
        @media screen and (max-width: 640px) { .rv-opts { grid-template-columns: 1fr; } }
        @media screen and (max-width: 480px) {
            body { font-size: 15px; }
            .head-title h1 { font-size: 19pt; }
            .sec-head { font-size: 12pt; }
            .party-name { font-size: 12.5pt; }
            .party-row { font-size: 9.6pt; }
            .art-title { font-size: 11pt; }
            .art-body { font-size: 10.4pt; }
            .preamble { font-size: 10pt; }
            .refbar-num { font-size: 14pt; }
            .sig-name { font-size: 10.5pt; }
            .sig-title, .sig-meta { font-size: 8.8pt; }
        }
    </style>
</head>
<body>
@include('quote._icons')

<div class="toolbar">
    <p>
        @if ($contract['is_approved'])
            عقد معتمد جاهز للحفظ — اختر <b>«حفظ بصيغة PDF»</b> من نافذة الطباعة.
        @else
            راجع بنود العقد أدناه ثم سجّل قرارك في نهاية الصفحة — ويمكنك حفظ نسخة PDF في أي وقت.
        @endif
    </p>
    <div class="tb-actions">
        <button type="button" class="tb-btn tb-primary" onclick="window.print()">
            <svg class="ic"><use href="#i-download"/></svg> تحميل العقد PDF
        </button>
        <a class="tb-btn tb-ghost" href="{{ $proposalUrl }}">
            <svg class="ic"><use href="#i-document"/></svg> عرض السعر
        </a>
    </div>
</div>

<article class="sheet">
    <header class="head" data-pg-unit="head">
        <div class="head-title">
            <h1>{{ $profile['contract_title'] }}</h1>
            <div class="en">{{ $profile['contract_en'] }}</div>
            <span @class(['copy', 'ok' => $contract['is_approved']])>
                {{ $contract['is_approved'] ? 'نسخة معتمدة من العميل' : 'مسوّدة للمراجعة — الجولة '.$contract['round'] }}
            </span>
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
            <div class="refbar-label">رقم العقد · CONTRACT No.</div>
            <div class="refbar-num">{{ $contract['number'] }}</div>
            <div class="refbar-ver">
                جولة المراجعة <b>{{ max(1, $contract['round']) }}</b>
                @if ($contract['round'] > 1)<span class="tag">نسخة معدَّلة</span>@endif
                @if ($quote)
                    · عرض السعر <b dir="ltr">{{ $sr->reference }}</b> (الإصدار {{ $quote['version'] }})
                @endif
            </div>
        </div>
        <div class="refbar-meta">
            <div>{{ $contract['is_approved'] ? 'تاريخ الاعتماد' : 'تاريخ الإصدار' }}: <b>{{ $fmt($docDate) }}</b></div>
            @if ($quote)
                <div>قيمة العقد: <b>{{ $money($quote['total']) }} {{ $quote['currency'] }}</b></div>
            @endif
            <div>عدد البنود: <b>{{ $contract['count'] }}</b></div>
        </div>
        <div class="refbar-qr">
            {!! $qr !!}
            <span class="cap">امسح للتحقق</span>
        </div>
    </section>

    <section class="parties" data-pg-unit="parties">
        <div class="party from">
            <div class="party-head">الطرف الأول — الشركة</div>
            <div class="party-body">
                <div class="party-name">{{ $legalName ?: 'وريد لتقنية المعلومات' }}</div>
                <div class="party-row"><span>يمثّلها</span><b>{{ $signature['name'] }}</b></div>
                <div class="party-row"><span>بصفته</span><b>{{ $signature['title'] }}</b></div>
                <div class="party-row"><span>البريد الإلكتروني</span><b class="ltr">{{ $contactEmail }}</b></div>
            </div>
        </div>
        <div class="party to">
            <div class="party-head">الطرف الثاني — العميل</div>
            <div class="party-body">
                <div class="party-name">{{ $contact['name'] }}</div>
                <div class="party-row"><span>{{ $profile['company_label'] }}</span><b>{{ $contact['store'] ?: '—' }}</b></div>
                <div class="party-row"><span>رقم الموبايل</span><b class="ltr">{{ $contact['phone'] ?: '—' }}</b></div>
                <div class="party-row"><span>البريد الإلكتروني</span><b class="ltr">{{ $contact['email'] ?: '—' }}</b></div>
            </div>
        </div>
    </section>

    <section class="preamble" data-pg-unit="preamble">
        إنه في يوم <b>{{ $fmt($docDate) }}</b> حُرِّر هذا العقد بين الطرفين المذكورين أعلاه،
        بناءً على عرض السعر رقم <b dir="ltr">{{ $sr->reference }}</b> المعتمد من الطرف الثاني،
        وقد اتفقا على البنود والمواد التالية:
    </section>

    @php $currentSection = null; @endphp
    @foreach ($contract['clauses'] as $clause)
        <div class="unit" data-pg-unit="clause-{{ $loop->iteration }}">
            @if ($clause['section'] !== '' && $clause['section'] !== $currentSection)
                @php $currentSection = $clause['section']; @endphp
                <div class="sec-head">{{ $clause['section'] }}</div>
            @endif

            <article class="art" id="clause-{{ $clause['id'] }}" data-clause="{{ $clause['id'] }}" data-locked="{{ $clause['locked'] ? '1' : '0' }}">
                <div class="art-head">
                    <span class="art-no">{{ $loop->iteration }}</span>
                    <span class="art-title">{{ $clause['title'] }}</span>
                    @if ($clause['locked'])
                        <span class="art-state ok"><svg class="ic"><use href="#i-check"/></svg> اعتمدته مسبقاً</span>
                    @elseif ($clause['decision'] === 'edited')
                        <span class="art-state edit"><svg class="ic"><use href="#i-edit"/></svg> طلبت تعديله</span>
                    @elseif ($clause['decision'] === 'deleted')
                        <span class="art-state del"><svg class="ic"><use href="#i-trash"/></svg> طلبت حذفه</span>
                    @elseif ($reviewing)
                        <span class="art-state wait">بانتظار قرارك</span>
                    @endif
                </div>
                <div class="art-body">{{ $clause['body'] }}</div>

                @if ($clause['locked'])
                    <div class="art-review">
                        <div class="rv-locked">
                            <svg class="ic"><use href="#i-verified"/></svg>
                            اعتمدت هذا البند{{ $clause['decided_at'] ? ' في '.$fmtShort($clause['decided_at']) : '' }} — لا يُطلب قرارك فيه مرة أخرى.
                        </div>
                    </div>
                @elseif ($reviewing)
                    @php
                        $oldDecision = old('clauses.'.$clause['id'].'.decision');
                        $oldNote = old('clauses.'.$clause['id'].'.note', '');
                    @endphp
                    <div class="art-review" data-review>
                        <p class="rv-lead">قرارك على هذا البند:</p>
                        <div class="rv-opts">
                            @foreach ($decisions as $key => $option)
                                <label @class(['rv-opt', 'on' => $oldDecision === $key]) data-opt="{{ $key }}" data-note-label="{{ $option['note_label'] }}">
                                    <input type="radio" name="clauses[{{ $clause['id'] }}][decision]" value="{{ $key }}" required @checked($oldDecision === $key)>
                                    <span class="rv-name">
                                        <svg class="ic"><use href="#i-{{ match ($key) { 'approved' => 'check', 'edited' => 'edit', default => 'trash' } }}"/></svg>
                                        {{ $option['label'] }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <div class="rv-note" data-note @if ($oldDecision === null || $oldDecision === 'approved') hidden @endif>
                            <label for="note-{{ $clause['id'] }}" data-note-label-target>اكتب التعديل المطلوب أو سبب الحذف</label>
                            <textarea id="note-{{ $clause['id'] }}" name="clauses[{{ $clause['id'] }}][note]" rows="3"
                                      maxlength="{{ \App\Support\Contracts::NOTE_MAX }}" placeholder="اكتب هنا…">{{ $oldNote }}</textarea>
                        </div>
                    </div>
                @elseif ($clause['decision'] && $clause['note'] !== '')
                    <div class="art-review">
                        <p class="rv-prev"><b>{{ $clause['decision_label'] }}:</b> {{ $clause['note'] }}</p>
                    </div>
                @endif
            </article>
        </div>
    @endforeach

    @if ($bank['has'])
        <div class="bank" data-pg-unit="bank">
            <h4>بيانات التحويل البنكي (تذييل العقد)</h4>
            @if ($bank['bank'])<div class="brow"><span>البنك</span><b>{{ $bank['bank'] }}</b></div>@endif
            @if ($bank['holder'])<div class="brow"><span>اسم الحساب</span><b>{{ $bank['holder'] }}</b></div>@endif
            @if ($bank['account'])<div class="brow"><span>رقم الحساب</span><b dir="ltr">{{ $bank['account'] }}</b></div>@endif
            @if ($bank['iban'])<div class="brow"><span>IBAN</span><b dir="ltr">{{ $bank['iban'] }}</b></div>@endif
            @if ($bank['swift'])<div class="brow"><span>SWIFT</span><b dir="ltr">{{ $bank['swift'] }}</b></div>@endif
        </div>
    @endif

    <section class="sign" data-pg-unit="signatures">
        <div class="sig">
            <div class="sig-head">عن الطرف الأول</div>
            <div class="sig-party">{{ $legalName ?: 'وريد لتقنية المعلومات' }}</div>
            <div class="sig-area">
                @if ($signature['stamp_url'])
                    <img class="sig-stamp" src="{{ $signature['stamp_url'] }}" alt="ختم الشركة">
                @else
                    <span class="sig-stamp-ph">ختم<br>الشركة</span>
                @endif
                <div class="sig-line">
                    <div class="sig-name">{{ $signature['name'] }}</div>
                    <div class="sig-title">{{ $signature['title'] }}</div>
                </div>
            </div>
            <div class="sig-meta">التوقيع والختم</div>
        </div>
        <div class="sig">
            <div class="sig-head">عن الطرف الثاني</div>
            <div class="sig-party">{{ $contact['name'] }}</div>
            @if ($contact['store'])<div class="sig-sub">{{ $contact['store'] }}</div>@endif
            <div class="sig-area">
                <div class="sig-line">
                    <div class="sig-name">{{ $contact['name'] }}</div>
                    <div class="sig-title">الطرف الثاني — العميل</div>
                </div>
            </div>
            <div class="sig-meta">
                @if ($contract['is_approved'])
                    <b>اعتُمدت بنود العقد إلكترونياً</b> عبر منصة وريد بتاريخ {{ $fmt($contract['approved_at']) }}
                    — جولة المراجعة {{ $contract['round'] }}.
                    @if ($contract['fully_signed'])
                        <b>ووُقّعت النسخة الموقّعة من الطرفين</b>{{ $contract['signed']['client']['uploaded_at'] ? ' بتاريخ '.$fmt($contract['signed']['client']['uploaded_at']) : '' }}.
                    @endif
                @else
                    التوقيع — بعد اعتماد بنود العقد إلكترونياً.
                @endif
            </div>
        </div>
    </section>

    <footer class="foot" data-pg-unit="footer">
        <p class="foot-note">
            هذا العقد صادر إلكترونياً عن منصة وريد برقم <span dir="ltr">{{ $contract['number'] }}</span>، ويمكن التحقق منه بهذا الرقم أو برمز QR.
            @if ($contract['is_approved'])
                اعتمد الطرف الثاني جميع بنوده إلكترونياً، وتُستكمل صيغته النهائية بتوقيع الطرفين على النسخة الموقّعة من الشركة.
            @else
                هذه مسوّدة للمراجعة، ولا تُعدّ ملزمة إلا بعد اعتماد الطرف الثاني لجميع بنودها وتوقيع الطرفين.
            @endif
        </p>
        <div class="foot-bar">
            <span class="brand">وريد — منصتك التقنية المتكاملة</span>
            <span class="ltr">{{ $contactEmail }} · wareed.vip</span>
        </div>
    </footer>
</article>

{{-- قرار العميل على العقد — للشاشة فقط، لا يظهر في النسخة المطبوعة --}}
<section class="decide" id="decide">
    @if (session('contract_saved') === 'approved')
        <div class="decide-done">
            <div class="dd-badge">اعتُمد العقد</div>
            <p class="dd-msg">شكراً لثقتك — اعتمدت جميع بنود العقد ووصل اعتمادك لفريق وريد. الخطوة التالية: سوف يتم إرسال نسخة من العقد موقّعة من الشركة وإعادة إرسالها لك للتوقيع.</p>
            <p class="dd-meta">يمكنك الآن رفع متطلبات مشروعك من صفحة عرض السعر، وتحميل نسخة PDF من العقد من الزر أعلاه.</p>
        </div>
    @elseif (session('contract_saved') === 'feedback')
        <div class="decide-done is-feedback">
            <div class="dd-badge">أُرسلت ملاحظاتك</div>
            <p class="dd-msg">وصلت ملاحظاتك لفريق وريد — سيراجعها ويوافيك بنسخة معدَّلة من العقد، وتبقى البنود التي اعتمدتها معتمدة كما هي.</p>
        </div>
    @endif

    @if ($contract['is_approved'])
        @php $company = $contract['signed']['company']; $mine = $contract['signed']['client']; @endphp
        <h2 class="decide-title">{{ $contract['fully_signed'] ? 'اكتمل توقيع العقد من الطرفين' : 'العقد معتمد' }}</h2>
        <p class="decide-lead">
            اعتمدت جميع بنود هذا العقد بتاريخ <b>{{ $fmt($contract['approved_at']) }}</b>.
            @if ($contract['fully_signed'])
                <b>وُقّع العقد من الطرفين</b> وأصبح نافذاً — النسختان محفوظتان لدى وريد.
            @elseif ($company)
                وصلتك النسخة الموقّعة من الشركة — <b>حمّلها ووقّعها ثم ارفع نسختك الموقّعة أدناه</b>.
            @else
                الخطوة التالية: سوف يتم إرسال نسخة من العقد موقّعة من الشركة وإعادة إرسالها لك للتوقيع.
            @endif
        </p>

        @if (session('signed_saved'))
            <p class="sc-ok">
                <svg class="ic" style="width:16px;height:16px;display:inline-block;vertical-align:-3px"><use href="#i-check"/></svg>
                استلمنا نسختك الموقّعة بنجاح — شكراً لك.
            </p>
        @endif

        <div class="signing">
            <div @class(['sc-box', 'is-done' => $company !== null])>
                <div class="sc-t"><svg class="ic"><use href="#i-{{ $company ? 'verified' : 'clock' }}"/></svg> النسخة الموقّعة من الشركة</div>
                @if ($company)
                    <div class="sc-file">
                        <b>{{ $company['name'] }}</b> · {{ $company['size_h'] }}
                        @if ($company['uploaded_at']) · {{ $fmtShort($company['uploaded_at']) }} @endif
                    </div>
                    @if ($company['url'])
                        <a class="tb-btn tb-primary sc-dl" href="{{ $company['url'] }}" target="_blank" rel="noopener">
                            <svg class="ic"><use href="#i-download"/></svg> تحميل النسخة الموقّعة من الشركة
                        </a>
                    @endif
                @else
                    <p class="sc-muted">لم تُرسل بعد — تصلك على بريدك الإلكتروني مرفقةً فور توقيعها وختمها.</p>
                @endif
            </div>

            <div @class(['sc-box', 'is-done' => $mine !== null])>
                <div class="sc-t"><svg class="ic"><use href="#i-{{ $mine ? 'verified' : 'edit' }}"/></svg> نسختك الموقّعة</div>
                @if ($mine)
                    <div class="sc-file">
                        <b>{{ $mine['name'] }}</b> · {{ $mine['size_h'] }}
                        @if ($mine['uploaded_at']) · رُفعت {{ $fmtShort($mine['uploaded_at']) }} @endif
                    </div>
                    <p class="sc-muted">لاستبدالها ارفع ملفاً جديداً.</p>
                @else
                    <p class="sc-muted">اطبع النسخة الموقّعة من الشركة، ووقّعها، ثم ارفعها هنا (PDF أو صورة).</p>
                @endif
                <form method="POST" action="{{ $signedCopyUrl }}" enctype="multipart/form-data" class="sc-form">
                    @csrf
                    <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required>
                    <button type="submit" class="tb-btn tb-primary">
                        <svg class="ic"><use href="#i-check"/></svg> {{ $mine ? 'استبدال نسختي الموقّعة' : 'رفع نسختي الموقّعة' }}
                    </button>
                    <span class="sc-hint">PDF أو صورة، بحجم أقصى 10 ميجابايت.</span>
                    @error('file')<p class="decide-err">{{ $message }}</p>@enderror
                </form>
            </div>
        </div>

        <div class="decide-actions">
            <a class="tb-btn tb-ghost" href="{{ $proposalUrl }}#requirements">
                <svg class="ic"><use href="#i-box"/></svg> رفع {{ $profile['requirements']['title'] }}
            </a>
        </div>
    @elseif ($contract['status'] === 'feedback')
        <h2 class="decide-title">ملاحظاتك قيد المراجعة</h2>
        <p class="decide-lead">
            أرسلت ملاحظاتك في الجولة {{ $contract['round'] }}{{ $contract['feedback_at'] ? ' بتاريخ '.$fmtShort($contract['feedback_at']) : '' }}،
            ويعمل فريق وريد على النسخة المعدَّلة. ستصلك على بريدك الإلكتروني فور جاهزيتها —
            <b>البنود التي اعتمدتها ({{ $contract['approved_count'] }} من {{ $contract['count'] }}) تبقى معتمدة</b> ولن يُطلب قرارك فيها مجدداً.
        </p>
    @else
        <h2 class="decide-title">قرارك على مسوّدة العقد</h2>
        <p class="decide-lead">
            يلزم قرار صريح في <b>كل بند</b> من البنود المفتوحة ({{ count($open) }} من {{ $contract['count'] }}
            @if ($contract['approved_count'] > 0) — و{{ $contract['approved_count'] }} اعتمدتها مسبقاً @endif).
            إن وافقت على جميع البنود يظهر زر <b>اعتماد مسوّدة العقد</b>، وإن طلبت تعديل أي بند أو حذفه يظهر زر <b>إرسال ملاحظات العقد</b>.
        </p>

        <form method="POST" action="{{ $decisionUrl }}" data-contract-form>
            @csrf
            {{-- الحقول داخل بطاقات البنود أعلاه تُلحق بهذا النموذج عبر السمة form --}}
            <div class="progress">
                <div class="bar"><i data-progress-bar></i></div>
                <span class="txt" data-progress-text>قرّرت في 0 من {{ count($open) }} بنود</span>
            </div>

            <div class="decide-actions">
                <button type="submit" class="decide-send" data-btn-approve>
                    <svg class="ic"><use href="#i-check"/></svg> اعتماد مسوّدة العقد
                </button>
                <button type="submit" class="decide-send is-feedback" data-btn-feedback>
                    <svg class="ic"><use href="#i-edit"/></svg> إرسال ملاحظات العقد
                </button>
                <span class="decide-hint" data-hint>اتخذ قرارك في كل البنود أولاً.</span>
            </div>

            @error('contract')<p class="decide-err">{{ $message }}</p>@enderror
            @error('clauses')<p class="decide-err">{{ $message }}</p>@enderror
            <p class="decide-err" data-client-err hidden></p>
        </form>
    @endif
</section>

@if ($reviewing)
<script>
/* ربط حقول البنود بنموذج القرار، وإظهار الزر المناسب حسب حالة القرارات — النموذج يعمل بدونه أيضاً */
(function () {
    'use strict';
    var form = document.querySelector('[data-contract-form]');
    if (!form) return;

    form.id = form.id || 'contract-form';
    // التحقق المحلي أدناه يحلّ محلّ تحقّق المتصفح الافتراضي كي تظهر رسالة واضحة ويُمرَّر للبند الناقص
    form.noValidate = true;
    var cards = Array.prototype.slice.call(document.querySelectorAll('[data-review]'));
    cards.forEach(function (card) {
        card.querySelectorAll('input, textarea').forEach(function (el) { el.setAttribute('form', form.id); });
    });

    var approveBtn = form.querySelector('[data-btn-approve]');
    var feedbackBtn = form.querySelector('[data-btn-feedback]');
    var hint = form.querySelector('[data-hint]');
    var bar = form.querySelector('[data-progress-bar]');
    var text = form.querySelector('[data-progress-text]');
    var clientErr = form.querySelector('[data-client-err]');

    function stateOf(card) {
        var picked = card.querySelector('input[type="radio"]:checked');
        return picked ? picked.value : null;
    }

    function syncCard(card) {
        var state = stateOf(card);
        card.querySelectorAll('[data-opt]').forEach(function (opt) {
            opt.classList.toggle('on', opt.dataset.opt === state);
        });
        var note = card.querySelector('[data-note]');
        if (note) {
            var needs = state === 'edited' || state === 'deleted';
            note.hidden = !needs;
            note.classList.toggle('is-required', needs);
            var label = card.querySelector('[data-opt="' + state + '"]');
            var target = note.querySelector('[data-note-label-target]');
            if (needs && label && target) target.textContent = label.dataset.noteLabel;
            var ta = note.querySelector('textarea');
            if (ta) ta.required = needs;
        }
    }

    function sync() {
        var decided = 0, objections = 0;
        cards.forEach(function (card) {
            syncCard(card);
            var s = stateOf(card);
            if (s) decided++;
            if (s === 'edited' || s === 'deleted') objections++;
        });

        var total = cards.length;
        var complete = decided === total;
        if (bar) bar.style.width = (total ? Math.round(decided / total * 100) : 100) + '%';
        if (text) text.textContent = 'قرّرت في ' + decided + ' من ' + total + ' بنود';

        approveBtn.hidden = complete && objections > 0;
        feedbackBtn.hidden = !(complete && objections > 0);
        approveBtn.disabled = !complete || objections > 0;
        feedbackBtn.disabled = !complete;
        hint.hidden = complete;
    }

    document.addEventListener('change', function (e) {
        if (e.target.closest('[data-review]')) sync();
    });

    form.addEventListener('submit', function (e) {
        var missing = cards.find(function (card) {
            var s = stateOf(card);
            if (!s) return true;
            if (s === 'approved') return false;
            var ta = card.querySelector('textarea');
            return !ta || ta.value.trim() === '';
        });
        if (!missing) return;
        e.preventDefault();
        clientErr.textContent = stateOf(missing)
            ? 'اكتب التعديل المطلوب أو سبب الحذف للبند رقم ' + missing.closest('.art').querySelector('.art-no').textContent + ' قبل الإرسال.'
            : 'يرجى اتخاذ قرار صريح في كل بند قبل الإرسال — البند رقم ' + missing.closest('.art').querySelector('.art-no').textContent + ' بلا قرار.';
        clientErr.hidden = false;
        missing.closest('.art').scrollIntoView({behavior: 'smooth', block: 'center'});
    });

    sync();
})();
</script>
@endif

<script>
/*
 * تقسيم الطباعة إلى صفحات A4 مستقلة بترويسة مصغّرة (من الصفحة الثانية) وتذييل مرقّم،
 * دون قصّ أي بند مهما طال العقد. الوحدات تُقرأ من السمة data-pg-unit بترتيبها في المستند
 * نفسه، فأي قسم جديد يحمل السمة يدخل الطباعة تلقائياً دون تسجيل يدوي.
 * يُبنى عند الطباعة الفعلية (beforeprint) ويُزال بعدها (afterprint)، والأصل لا يُمسّ.
 */
(function () {
    'use strict';

    var NUMBER = @json($contract['number']);
    var CONTACT_EMAIL = @json($contactEmail);

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
                '<span class="ref" dir="ltr">' + NUMBER + '</span>';
        }

        var body = document.createElement('div');
        body.className = 'pg-body';

        var foot = document.createElement('div');
        foot.className = 'pg-foot';
        foot.innerHTML =
            '<span>وريد لتقنية المعلومات · ' + CONTACT_EMAIL + '</span>' +
            '<span>عقد · <b dir="ltr">' + NUMBER + '</b> — صفحة ' +
            '<span class="no" data-pg-cur></span> من <span class="no" data-pg-total></span></span>';

        pg.appendChild(head);
        pg.appendChild(body);
        pg.appendChild(foot);
        document.body.appendChild(pg);

        return {el: pg, body: body, foot: foot};
    }

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

    function paginate() {
        var sheet = document.querySelector('.sheet');
        var units = sheet ? Array.prototype.slice.call(sheet.querySelectorAll('[data-pg-unit]')) : [];
        if (!units.length) return;

        document.body.classList.add('paged');
        var pages = [makePage(true)];

        units.forEach(function (node) { place(pages, node); });

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
