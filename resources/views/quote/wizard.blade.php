{{--
    نموذج عرض السعر التفاعلي — سؤال واحد في كل شاشة (وضع أبيض بهوية وريد، خط IBM Plex Sans Arabic).
    يُبنى من مصفوفة الأسئلة في QuoteController (مصدر الحقيقة الوحيد).
--}}
@php
    $qCount = count($questions);
    $qWord = $qCount >= 3 && $qCount <= 10 ? 'أسئلة' : 'سؤالاً';
    $f = ($client['gender'] ?? null) === 'f';
    // إعدادات الواجهة تُبنى هنا وتُحقن كسطر واحد (وسائط الموجهات متعددة الأسطر غير موثوقة في Blade)
    $wizardConfig = json_encode([
        'postUrl' => $inviteSlug ? route('quote.invite.submit', $inviteSlug) : route('quote.submit'),
        'csrf' => csrf_token(),
        'personalized' => $client !== null,
        'clientName' => $client['name'] ?? null,
        'whatsapp' => $whatsapp,
        'storageKey' => 'wareed-quote:'.($inviteSlug ?? 'general'),
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <title>{{ $client ? 'نموذج '.$client['name'] : 'طلب عرض سعر متجر إلكتروني' }} — وريد</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=ibm-plex-sans-arabic:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue: #3b82f6; --blue-deep: #2563eb; --violet: #8b5cf6; --teal: #14b8a6;
            --grad: linear-gradient(120deg, #3b82f6 0%, #8b5cf6 48%, #2dd4bf 100%);
            --bg: #f6f8fd; --card: #ffffff;
            --ink: #0d1830; --muted: #5b6a8c; --faint: #9aa9c7;
            --line: #e6ebf7; --line-strong: #cfdaef;
            --err: #dc2626;
            --shadow-card: 0 1px 2px rgba(13, 24, 48, .04), 0 12px 28px -14px rgba(13, 24, 48, .07), 0 36px 90px -42px rgba(37, 99, 235, .25);
            --shadow-btn: 0 12px 30px -12px rgba(99, 102, 241, .55);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; -webkit-text-size-adjust: 100%; }
        body {
            font-family: 'IBM Plex Sans Arabic', 'Tajawal', ui-sans-serif, system-ui, sans-serif;
            background: var(--bg); color: var(--ink);
            line-height: 1.85; min-height: 100svh;
            -webkit-font-smoothing: antialiased; text-rendering: optimizeLegibility;
            overflow-x: hidden;
        }
        h1, h2, h3 { font-weight: 700; line-height: 1.4; }
        button, input, textarea { font: inherit; color: inherit; }
        a { color: inherit; text-decoration: none; }
        ::selection { background: rgba(139, 92, 246, .22); }
        :focus-visible { outline: 3px solid rgba(59, 130, 246, .4); outline-offset: 2px; border-radius: 6px; }

        /* خلفية تقنية فاتحة: شبكة خفيفة + هالات بألوان الهوية */
        body::before {
            content: ""; position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background-image:
                linear-gradient(rgba(37, 99, 235, .05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(20, 184, 166, .045) 1px, transparent 1px);
            background-size: 44px 44px;
            -webkit-mask-image: radial-gradient(130% 105% at 50% 0%, #000 30%, transparent 82%);
            mask-image: radial-gradient(130% 105% at 50% 0%, #000 30%, transparent 82%);
        }
        body::after {
            content: ""; position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background-image:
                radial-gradient(900px 560px at 88% -8%, rgba(59, 130, 246, .10), transparent 60%),
                radial-gradient(820px 620px at -4% 18%, rgba(139, 92, 246, .09), transparent 55%),
                radial-gradient(760px 560px at 50% 112%, rgba(45, 212, 191, .09), transparent 60%);
        }

        .grad-text {
            background: var(--grad);
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent; color: transparent;
        }

        /* ===== الشريط العلوي ===== */
        .topbar {
            position: fixed; inset-inline: 0; top: 0; z-index: 40;
            display: flex; align-items: center; justify-content: space-between; gap: 14px;
            padding: 12px clamp(16px, 4vw, 34px);
            background: rgba(255, 255, 255, .84);
            backdrop-filter: blur(16px) saturate(160%); -webkit-backdrop-filter: blur(16px) saturate(160%);
            border-bottom: 1px solid var(--line);
        }
        .brand { display: flex; align-items: center; gap: 10px; font-size: 1.3rem; font-weight: 700; letter-spacing: -.01em; }
        .brand svg { width: 34px; height: 34px; flex: 0 0 34px; }
        .top-tag {
            display: inline-flex; align-items: center; gap: 7px;
            font-size: .8rem; font-weight: 600; color: var(--muted);
            background: #f1f5fd; border: 1px solid var(--line); border-radius: 999px;
            padding: 6px 15px; white-space: nowrap;
        }
        .top-tag i { font-style: normal; background: var(--grad); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }

        /* شريط التقدم */
        .rail { position: fixed; inset-inline: 0; top: 59px; z-index: 40; height: 4px; background: #e9eefa; }
        .rail-fill {
            height: 100%; width: 0%; background: var(--grad);
            border-radius: 99px 0 0 99px;
            box-shadow: 0 0 14px rgba(99, 102, 241, .5);
            transition: width .55s cubic-bezier(.22, 1, .36, 1);
        }
        .pm { position: fixed; inset-inline: 0; top: 75px; z-index: 39; display: none; justify-content: center; pointer-events: none; padding: 0 16px; }
        body.in-progress .pm { display: flex; }
        .pm-pill {
            display: flex; align-items: center; gap: 10px;
            font-size: .8rem; font-weight: 600; color: var(--muted);
            font-variant-numeric: tabular-nums;
            background: rgba(255, 255, 255, .92); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--line); border-radius: 999px; padding: 6px 18px;
            box-shadow: 0 10px 26px -16px rgba(13, 24, 48, .25);
            animation: pm-in .45s cubic-bezier(.22, 1, .36, 1) both;
        }
        @keyframes pm-in { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: none; } }
        .pm b { color: var(--blue-deep); font-weight: 700; }
        .pm .pct { font-weight: 700; background: var(--grad); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
        .pm .dot { opacity: .45; }

        /* ===== المسرح والشاشات ===== */
        .stage { position: relative; z-index: 1; display: flex; flex-direction: column; min-height: 100svh; padding: 132px clamp(14px, 4vw, 28px) 54px; }
        .screen { display: none; width: 100%; max-width: 690px; margin: auto; }
        .screen.is-active { display: block; animation: rise .55s cubic-bezier(.22, 1, .36, 1) both; }
        @keyframes rise { from { opacity: 0; transform: translateY(24px) scale(.985); } to { opacity: 1; transform: none; } }

        .card {
            background: var(--card); border: 1px solid var(--line); border-radius: 28px;
            box-shadow: var(--shadow-card);
            padding: clamp(26px, 5.5vw, 48px);
            position: relative; overflow: hidden;
        }
        .card::before { content: ""; position: absolute; inset-inline: 0; top: 0; height: 3px; background: var(--grad); opacity: .95; }
        .card.is-shake { animation: shake .45s; }
        @keyframes shake { 0%, 100% { transform: none; } 25% { transform: translateX(7px); } 50% { transform: translateX(-6px); } 75% { transform: translateX(4px); } }

        /* ===== شاشة الترحيب والشكر ===== */
        .welcome, .thanks { text-align: center; }
        .w-mark { position: relative; display: flex; justify-content: center; margin-bottom: 20px; }
        .w-mark::before {
            content: ""; position: absolute; top: 50%; inset-inline-start: 50%; transform: translate(50%, -50%);
            width: 130px; height: 130px; border-radius: 50%; pointer-events: none;
            background: radial-gradient(circle, rgba(139, 92, 246, .14), rgba(59, 130, 246, .07) 55%, transparent 72%);
        }
        .w-mark svg { position: relative; width: 70px; height: 70px; filter: drop-shadow(0 14px 28px rgba(99, 102, 241, .35)); }
        .w-chip {
            display: inline-flex; align-items: center; gap: 8px; margin-bottom: 18px;
            font-size: .85rem; font-weight: 700; color: #0d9488;
            background: rgba(45, 212, 191, .1); border: 1px solid rgba(20, 184, 166, .32);
            border-radius: 999px; padding: 6px 17px;
        }
        .w-chip .pulse { width: 8px; height: 8px; border-radius: 50%; background: var(--teal); animation: pulse 2.2s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(45, 212, 191, .5); } 70% { box-shadow: 0 0 0 10px rgba(45, 212, 191, 0); } 100% { box-shadow: 0 0 0 0 rgba(45, 212, 191, 0); } }
        .welcome h1 { font-size: clamp(1.65rem, 5.2vw, 2.35rem); margin-bottom: 14px; letter-spacing: -.02em; }
        .wave { display: inline-block; animation: wave 2s ease-in-out 1 .6s; transform-origin: 72% 72%; }
        @keyframes wave { 0%, 100% { transform: none; } 15% { transform: rotate(16deg); } 30% { transform: rotate(-9deg); } 45% { transform: rotate(14deg); } 60% { transform: rotate(-5deg); } 75% { transform: rotate(8deg); } }
        .w-lead { color: var(--muted); font-size: clamp(.98rem, 2.6vw, 1.08rem); max-width: 52ch; margin: 0 auto 24px; }
        .w-meta { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-bottom: 30px; }
        .w-meta span {
            display: inline-flex; align-items: center; gap: 7px;
            font-size: .84rem; font-weight: 600; color: var(--muted);
            background: #f3f7fe; border: 1px solid var(--line); border-radius: 999px; padding: 7px 16px;
        }

        /* ===== الأسئلة ===== */
        .q-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 18px; }
        .q-chip {
            font-size: .78rem; font-weight: 700; color: var(--blue-deep);
            font-variant-numeric: tabular-nums;
            background: rgba(59, 130, 246, .09); border: 1px solid rgba(59, 130, 246, .22);
            border-radius: 999px; padding: 4px 14px; white-space: nowrap;
        }
        .q-hello { font-size: .92rem; font-weight: 700; color: #0d9488; }
        .q-title { font-size: clamp(1.3rem, 4.2vw, 1.7rem); margin-bottom: 8px; letter-spacing: -.015em; }
        .q-hint { color: var(--muted); font-size: .95rem; margin-bottom: 24px; }
        .q-title + .q-nav, .q-title + .field, .q-title + .opts { margin-top: 24px; }

        .field {
            width: 100%; padding: 16px 19px; border-radius: 16px;
            border: 1.6px solid var(--line-strong); background: #fbfcff;
            font-size: 1.06rem; transition: border-color .25s, box-shadow .25s, background .25s; outline: none;
        }
        .field::placeholder { color: var(--faint); }
        .field:focus { border-color: var(--blue); background: #fff; box-shadow: 0 0 0 4px rgba(59, 130, 246, .13); }
        .field.ltr { direction: ltr; text-align: start; font-variant-numeric: tabular-nums; }
        textarea.field { resize: vertical; min-height: 132px; line-height: 1.8; }

        .opts { display: flex; flex-direction: column; gap: 11px; }
        .opts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 11px; }
        .opt {
            position: relative; display: flex; align-items: center; gap: 12px;
            border: 1.6px solid var(--line-strong); border-radius: 16px;
            padding: 14px 17px; cursor: pointer; background: #fbfcff; user-select: none;
            transition: border-color .2s, background .2s, transform .2s, box-shadow .2s;
        }
        .opt:hover { border-color: #93b4fd; background: #f6f9ff; transform: translateY(-2px); box-shadow: 0 12px 26px -18px rgba(37, 99, 235, .35); }
        .opt input { position: absolute; opacity: 0; pointer-events: none; }
        .ic { width: 20px; height: 20px; flex: 0 0 auto; display: inline-block; vertical-align: -.22em; }
        .opt-ic {
            flex: 0 0 38px; width: 38px; height: 38px; border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            background: #eef3fd; border: 1px solid var(--line); color: var(--blue-deep);
            transition: background .2s, border-color .2s, color .2s;
        }
        .opt-ic .ic { width: 21px; height: 21px; }
        .opt:hover .opt-ic { background: #e4edfd; border-color: #b9cef8; }
        .opt-tx { font-size: .98rem; font-weight: 600; flex: 1; }
        .opt-check {
            flex: 0 0 22px; width: 22px; height: 22px; border-radius: 50%;
            border: 1.8px solid var(--line-strong); display: flex; align-items: center; justify-content: center;
            transition: border-color .2s, background .2s; color: transparent;
        }
        .opt-check .ic { width: 12px; height: 12px; }
        .opt:has(input:checked) {
            border-color: transparent; box-shadow: 0 12px 28px -16px rgba(99, 102, 241, .45);
            background: linear-gradient(#f6f9ff, #f6f9ff) padding-box, var(--grad) border-box;
        }
        .opt:has(input:checked) .opt-check { border-color: transparent; background: var(--grad); color: #fff; animation: check-pop .28s cubic-bezier(.22, 1.4, .36, 1); }
        .opt:has(input:checked) .opt-ic { background: var(--grad); border-color: transparent; color: #fff; }
        @keyframes check-pop { 0% { transform: scale(.55); } 70% { transform: scale(1.12); } 100% { transform: scale(1); } }
        .opt:has(input:focus-visible) { outline: 3px solid rgba(59, 130, 246, .35); outline-offset: 2px; }
        .other-wrap { margin-top: 12px; }

        .q-err { display: flex; align-items: center; gap: 8px; color: var(--err); font-size: .88rem; font-weight: 600; margin-top: 12px; }
        .q-err[hidden] { display: none; }

        /* ===== شاشة المراجعة ===== */
        .review { display: flex; flex-direction: column; gap: 9px; margin-top: 24px; }
        .rv-row {
            display: flex; align-items: center; gap: 13px; width: 100%; text-align: start;
            background: #fbfcff; border: 1.6px solid var(--line); border-radius: 15px;
            padding: 12px 17px; cursor: pointer;
            transition: border-color .2s, background .2s, transform .2s;
        }
        .rv-row:hover { border-color: #93b4fd; background: #f6f9ff; transform: translateY(-1px); }
        .rv-q { flex: 0 0 auto; min-width: 108px; font-size: .8rem; font-weight: 600; color: var(--muted); }
        .rv-a { flex: 1; font-size: .93rem; font-weight: 600; color: var(--ink); overflow-wrap: anywhere; line-height: 1.65; }
        .rv-edit { flex: 0 0 auto; display: inline-flex; align-items: center; gap: 4px; font-size: .78rem; font-weight: 700; color: var(--blue-deep); opacity: .55; transition: opacity .2s; }
        .rv-edit .ic { width: 12px; height: 12px; }
        .rv-row:hover .rv-edit { opacity: 1; }
        @media (max-width: 560px) { .rv-q { min-width: 88px; } .rv-edit .ic { display: none; } }

        /* ===== الأزرار ===== */
        .q-nav { display: flex; align-items: center; flex-wrap: wrap; gap: 12px; margin-top: 28px; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 9px;
            padding: 13px 30px; border-radius: 15px; border: 1px solid transparent;
            font-weight: 700; font-size: 1rem; cursor: pointer;
            transition: transform .22s, box-shadow .3s, background-position .4s, border-color .25s, opacity .2s;
        }
        .btn:disabled { opacity: .65; cursor: wait; transform: none !important; }
        .btn-primary { background: var(--grad); background-size: 150% 100%; background-position: 0% 0; color: #fff; box-shadow: var(--shadow-btn); }
        .btn-primary:hover { transform: translateY(-2px); background-position: 90% 0; box-shadow: 0 20px 44px -16px rgba(99, 102, 241, .65); }
        .btn-lg { padding: 15px 46px; font-size: 1.08rem; }
        .btn-skip { background: transparent; color: var(--muted); border-color: var(--line-strong); }
        .btn-skip:hover { border-color: var(--blue); color: var(--blue-deep); }
        .btn-back { margin-inline-start: auto; display: inline-flex; align-items: center; gap: 6px; background: none; border: none; color: var(--muted); font-size: .9rem; font-weight: 600; cursor: pointer; padding: 8px 4px; transition: color .2s; }
        .btn-back:hover { color: var(--blue-deep); }
        .btn-back .ic { width: 15px; height: 15px; }
        .key-hint { font-size: .78rem; color: var(--faint); font-weight: 500; }
        .key-hint b { color: var(--muted); }
        @media (hover: none) { .key-hint { display: none; } }

        .spinner { width: 17px; height: 17px; border: 2.4px solid rgba(255, 255, 255, .4); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; display: none; }
        .btn.is-loading .spinner { display: inline-block; }
        .btn.is-loading .btn-label { opacity: .85; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ===== شاشة الشكر ===== */
        .check {
            position: relative; width: 88px; height: 88px; margin: 4px auto 22px; border-radius: 50%;
            background: var(--grad); display: flex; align-items: center; justify-content: center;
            box-shadow: 0 20px 46px -16px rgba(99, 102, 241, .6);
            animation: pop .6s cubic-bezier(.22, 1.4, .36, 1) both .15s;
        }
        .check::after {
            content: ""; position: absolute; inset: -9px; border-radius: 50%;
            border: 2px solid rgba(139, 92, 246, .3);
            animation: ring 1.7s ease-out .6s 3;
        }
        @keyframes ring { 0% { transform: scale(.9); opacity: 1; } 100% { transform: scale(1.25); opacity: 0; } }
        .check svg { width: 40px; height: 40px; color: #fff; }
        .check svg path { stroke-dasharray: 60; stroke-dashoffset: 60; animation: draw .55s ease-out forwards .5s; }
        @keyframes pop { from { transform: scale(.4); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        @keyframes draw { to { stroke-dashoffset: 0; } }
        .thanks h2 { font-size: clamp(1.55rem, 4.8vw, 2.1rem); margin-bottom: 10px; letter-spacing: -.02em; }
        .ref-box {
            display: flex; flex-direction: column; align-items: center; gap: 3px;
            margin: 0 auto 22px; padding: 15px 26px; max-width: 380px;
            background: #f7f9fe; border: 1.5px dashed rgba(59, 130, 246, .4); border-radius: 16px;
        }
        .ref-box[hidden] { display: none; }
        .ref-label { font-size: .76rem; font-weight: 600; color: var(--muted); letter-spacing: .04em; }
        .ref-num {
            font-size: 1.2rem; font-weight: 700; letter-spacing: .06em; direction: ltr;
            font-variant-numeric: tabular-nums;
            background: var(--grad); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
        }
        .ref-note { font-size: .74rem; color: var(--faint); }
        .t-lead { color: var(--muted); max-width: 46ch; margin: 0 auto 22px; }
        .promise {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            background: linear-gradient(150deg, rgba(59, 130, 246, .08), rgba(45, 212, 191, .07));
            border: 1px solid rgba(59, 130, 246, .24); border-radius: 18px;
            padding: 16px 22px; font-size: 1rem; margin-bottom: 28px; text-align: start;
        }
        .promise .ic { color: var(--blue-deep); }
        .promise b { color: var(--blue-deep); }
        .t-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; }
        .btn-wa { background: linear-gradient(135deg, #25d366, #128c7e); color: #fff; box-shadow: 0 14px 30px -12px rgba(37, 211, 102, .55); }
        .btn-wa:hover { transform: translateY(-2px); }
        .btn-wa .ic { width: 19px; height: 19px; }

        .send-err {
            margin-top: 16px; padding: 13px 16px; border-radius: 14px; font-size: .92rem; font-weight: 600;
            color: #991b1b; background: #fef2f2; border: 1px solid #fecaca;
        }
        .send-err a { color: #0d9488; text-decoration: underline; }
        .send-err[hidden] { display: none; }

        .toast {
            position: fixed; bottom: 26px; inset-inline-start: 50%; transform: translateX(50%);
            z-index: 60; background: var(--ink); color: #fff; font-size: .88rem; font-weight: 600;
            padding: 11px 22px; border-radius: 999px; box-shadow: 0 18px 40px -14px rgba(13, 24, 48, .5);
            opacity: 0; pointer-events: none; transition: opacity .4s, transform .4s;
        }
        .toast.is-on { opacity: 1; transform: translateX(50%) translateY(-4px); }

        .minifoot { position: relative; z-index: 1; text-align: center; padding: 0 16px 26px; color: var(--faint); font-size: .8rem; }
        .minifoot a { color: var(--muted); font-weight: 600; }

        .hp { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }

        @media (max-width: 560px) {
            .opts-grid { grid-template-columns: 1fr; }
            .pm .hide-sm { display: none; }
            .top-tag { display: none; }
            .brand { font-size: 1.18rem; }
            .stage { padding-top: 124px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after { animation: none !important; transition: none !important; }
        }
    </style>
</head>
<body>
@include('quote._icons')

    {{-- الشريط العلوي: الشعار + وسم النموذج --}}
    <header class="topbar">
        <a class="brand" href="{{ url('/') }}" aria-label="وريد">
            <svg viewBox="0 0 40 40" fill="none" aria-hidden="true">
                <defs><linearGradient id="wm" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0" stop-color="#3B82F6"/><stop offset=".5" stop-color="#8B5CF6"/><stop offset="1" stop-color="#2DD4BF"/>
                </linearGradient></defs>
                <rect x="2.5" y="2.5" width="35" height="35" rx="11" stroke="url(#wm)" stroke-width="2" opacity=".5"/>
                <path d="M11 27 L18 14 L24 23 L29 13" stroke="url(#wm)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="11" cy="27" r="2.5" fill="#2DD4BF"/>
                <circle cx="18" cy="14" r="2.5" fill="#3B82F6"/>
                <circle cx="24" cy="23" r="2.5" fill="#8B5CF6"/>
                <circle cx="29" cy="13" r="2.5" fill="#60A5FA"/>
            </svg>
            <span class="grad-text">وريد</span>
        </a>
        <span class="top-tag"><svg class="ic" style="width:15px;height:15px"><use href="#i-document"/></svg> طلب متجر إلكتروني · عرض سعر</span>
    </header>
    <div class="rail"><div class="rail-fill" data-rail></div></div>
    <div class="pm" aria-live="polite">
        <div class="pm-pill">
            <span>السؤال <b data-pm-step>1</b> من {{ $qCount }}</span>
            <span class="dot">·</span><span class="hide-sm" data-pm-left></span>
            <span class="dot hide-sm">·</span><span class="pct" data-pm-pct>0%</span>
        </div>
    </div>

    <main class="stage">

        {{-- شاشة الترحيب --}}
        <section class="screen is-active" data-welcome>
            <div class="card welcome">
                <div class="w-mark">
                    <svg viewBox="0 0 40 40" fill="none" aria-hidden="true">
                        <defs><linearGradient id="wmw" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0" stop-color="#3B82F6"/><stop offset=".5" stop-color="#8B5CF6"/><stop offset="1" stop-color="#2DD4BF"/>
                        </linearGradient></defs>
                        <rect x="2.5" y="2.5" width="35" height="35" rx="11" stroke="url(#wmw)" stroke-width="2" opacity=".5"/>
                        <path d="M11 27 L18 14 L24 23 L29 13" stroke="url(#wmw)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="11" cy="27" r="2.5" fill="#2DD4BF"/>
                        <circle cx="18" cy="14" r="2.5" fill="#3B82F6"/>
                        <circle cx="24" cy="23" r="2.5" fill="#8B5CF6"/>
                        <circle cx="29" cy="13" r="2.5" fill="#60A5FA"/>
                    </svg>
                </div>
                @if($client)
                    <span class="w-chip"><span class="pulse"></span> نموذج مخصّص {{ $f ? 'لكِ' : 'لك' }}</span>
                    <h1>أهلاً {{ $f ? 'بكِ' : 'بك' }} <span class="grad-text">{{ $client['name'] }}</span></h1>
                    <p class="w-lead">يسعدنا اهتمامك بإطلاق متجرك الإلكتروني مع وريد. أعددنا هذا النموذج خصيصاً {{ $f ? 'لكِ' : 'لك' }} — {{ $qCount }} {{ $qWord }} سريعة، وبناءً على إجاباتك سنجهّز عرض سعر دقيقاً يناسب المتجر تماماً.</p>
                @else
                    <span class="w-chip"><span class="pulse"></span> طلبات المتاجر الإلكترونية</span>
                    <h1>أهلاً بك في <span class="grad-text">وريد</span></h1>
                    <p class="w-lead">دقائق قليلة تفصلنا عن فهم متجرك: {{ $qCount }} {{ $qWord }} سريعة، وبناءً على إجاباتك يصلك عرض سعر مخصّص خلال 24 ساعة.</p>
                @endif
                <div class="w-meta">
                    <span><svg class="ic"><use href="#i-clock"/></svg> أقل من 3 دقائق</span>
                    <span><svg class="ic"><use href="#i-list"/></svg> {{ $qCount }} {{ $qWord }}</span>
                    <span><svg class="ic"><use href="#i-shield"/></svg> بياناتك سرّية بالكامل</span>
                </div>
                <button type="button" class="btn btn-primary btn-lg" data-start>
                    ابدأ الطلب <svg class="ic"><use href="#i-arrow-left"/></svg>
                </button>
            </div>
        </section>

        {{-- شاشات الأسئلة (سؤال واحد لكل شاشة) --}}
        @foreach($questions as $q)
            <section class="screen" data-q data-key="{{ $q['key'] }}" data-type="{{ $q['type'] }}"
                     @if($q['optional'] ?? false) data-optional @endif
                     @if($q['has_other'] ?? false) data-has-other @endif>
                <div class="card">
                    <div class="q-top">
                        <span class="q-chip">سؤال {{ $loop->iteration }} / {{ $qCount }}</span>
                        @if(! $client && $q['key'] === 'email')
                            <span class="q-hello" data-hello hidden></span>
                        @endif
                    </div>
                    <h2 class="q-title">{{ $q['title'] }}</h2>
                    @if(! empty($q['hint']))<p class="q-hint">{{ $q['hint'] }}</p>@endif

                    @if(in_array($q['type'], ['text', 'tel', 'email']))
                        <input class="field {{ in_array($q['type'], ['tel', 'email']) ? 'ltr' : '' }}"
                               type="{{ $q['type'] }}" name="{{ $q['key'] }}"
                               placeholder="{{ $q['placeholder'] ?? '' }}"
                               autocomplete="{{ $q['autocomplete'] ?? 'off' }}"
                               @if(! empty($q['maxlength'])) maxlength="{{ $q['maxlength'] }}" @endif
                               @if($q['type'] === 'tel') inputmode="tel" @endif
                               @if($q['type'] === 'email') inputmode="email" @endif>
                    @elseif($q['type'] === 'textarea')
                        <textarea class="field" name="{{ $q['key'] }}" rows="5" maxlength="2000"
                                  placeholder="{{ $q['placeholder'] ?? '' }}"></textarea>
                    @else
                        <div class="opts {{ ($q['grid'] ?? false) ? 'opts-grid' : '' }}" role="{{ $q['type'] === 'multi' ? 'group' : 'radiogroup' }}">
                            @foreach($q['options'] as $opt)
                                <label class="opt">
                                    <input type="{{ $q['type'] === 'multi' ? 'checkbox' : 'radio' }}"
                                           name="{{ $q['key'] }}" value="{{ $opt['label'] }}">
                                    @if(! empty($opt['icon']))
                                        <span class="opt-ic"><svg class="ic" aria-hidden="true"><use href="#i-{{ $opt['icon'] }}"/></svg></span>
                                    @endif
                                    <span class="opt-tx">{{ $opt['label'] }}</span>
                                    <span class="opt-check" aria-hidden="true"><svg class="ic"><use href="#i-check"/></svg></span>
                                </label>
                            @endforeach
                        </div>
                        @if($q['has_other'] ?? false)
                            <div class="other-wrap" data-other hidden>
                                <input class="field" type="text" name="{{ $q['key'] }}_other" maxlength="300"
                                       placeholder="مثال: مستلزمات أطفال، أدوات مكتبية…">
                            </div>
                        @endif
                    @endif

                    <p class="q-err" data-err hidden role="alert">
                        <svg class="ic"><use href="#i-alert"/></svg><span data-err-text></span>
                    </p>

                    <div class="q-nav">
                        <button type="button" class="btn btn-primary" data-next>
                            <span class="spinner" aria-hidden="true"></span>
                            <span class="btn-label">التالي</span>
                        </button>
                        @if($q['optional'] ?? false)
                            <button type="button" class="btn btn-skip" data-skip>تخطّي</button>
                        @endif
                        <span class="key-hint">اضغط <b>{{ $q['type'] === 'textarea' ? 'Ctrl + Enter' : 'Enter ↵' }}</b></span>
                        <button type="button" class="btn-back" data-back>
                            <svg class="ic"><use href="#i-arrow-up"/></svg>
                            السؤال السابق
                        </button>
                    </div>
                </div>
            </section>
        @endforeach

        {{-- شاشة المراجعة قبل الإرسال --}}
        <section class="screen" data-review>
            <div class="card">
                <div class="q-top">
                    <span class="q-chip">لحظة مراجعة أخيرة</span>
                </div>
                <h2 class="q-title">مراجعة أخيرة قبل الإرسال</h2>
                <p class="q-hint">تأكيد سريع للإجابات قبل الإرسال — يمكن تعديل أي إجابة بالضغط عليها</p>
                <div class="review">
                    @foreach($questions as $i => $q)
                        <button type="button" class="rv-row" data-goto="{{ $i }}">
                            <span class="rv-q">{{ $q['short'] }}</span>
                            <span class="rv-a" data-rv="{{ $q['key'] }}">—</span>
                            <span class="rv-edit">تعديل <svg class="ic"><use href="#i-edit"/></svg></span>
                        </button>
                    @endforeach
                </div>
                <p class="q-err" data-err hidden role="alert">
                    <svg class="ic"><use href="#i-alert"/></svg><span data-err-text></span>
                </p>
                <div class="q-nav">
                    <button type="button" class="btn btn-primary btn-lg" data-submit>
                        <span class="spinner" aria-hidden="true"></span>
                        <svg class="ic"><use href="#i-check"/></svg>
                        <span class="btn-label">اعتماد وإرسال الطلب</span>
                    </button>
                    <span class="key-hint">اضغط <b>Enter ↵</b></span>
                </div>
            </div>
        </section>

        {{-- شاشة الشكر --}}
        <section class="screen" data-thanks>
            <div class="card thanks">
                <div class="check" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </div>
                @if($client)
                    <h2>شكراً {{ $f ? 'لكِ' : 'لك' }} <span class="grad-text">{{ $client['short_name'] }}</span></h2>
                @else
                    <h2>شكراً <span data-thanks-name>لك</span></h2>
                @endif
                <p class="t-lead">استلمنا طلبك بنجاح، وبدأ فريق وريد دراسة الإجابات بعناية لتجهيز أفضل عرض ممكن.</p>

                <div class="ref-box" data-req hidden>
                    <span class="ref-label">الرقم المرجعي للطلب</span>
                    <b class="ref-num" data-req-num></b>
                    <span class="ref-note">يرجى الاحتفاظ بهذا الرقم للرجوع إليه في أي مراسلة</span>
                </div>

                <div class="promise">
                    <svg class="ic"><use href="#i-mail"/></svg>
                    <span>سيصلك <b>عرض السعر المخصّص</b> خلال <b>{{ $slaHours }} ساعة</b> بإذن الله.</span>
                </div>

                <div class="t-actions">
                    <a class="btn btn-primary" data-doc href="#" target="_blank" rel="noopener" hidden>
                        <svg class="ic"><use href="#i-download"/></svg>
                        تحميل مستند الطلب (PDF)
                    </a>
                    <a class="btn btn-wa" data-wa href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener">
                        <svg class="ic"><use href="#i-whatsapp"/></svg>
                        التواصل عبر واتساب
                    </a>
                    <a class="btn btn-skip" href="{{ url('/') }}">زيارة موقع وريد</a>
                </div>
            </div>
        </section>

        <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true" data-hp>
    </main>

    <div class="toast" data-toast role="status"></div>

    <footer class="minifoot">© {{ date('Y') }} <a href="{{ url('/') }}">وريد</a> — منصتك التقنية المتكاملة · wareed.vip</footer>

    <script>
        (function () {
            'use strict';

            var CFG = {!! $wizardConfig !!};

            var screens = Array.prototype.slice.call(document.querySelectorAll('[data-q]'));
            var welcome = document.querySelector('[data-welcome]');
            var review = document.querySelector('[data-review]');
            var thanks = document.querySelector('[data-thanks]');
            var rail = document.querySelector('[data-rail]');
            var pmStep = document.querySelector('[data-pm-step]');
            var pmLeft = document.querySelector('[data-pm-left]');
            var pmPct = document.querySelector('[data-pm-pct]');
            var toast = document.querySelector('[data-toast]');
            var hp = document.querySelector('[data-hp]');
            var total = screens.length;
            var current = -1; // -1 ترحيب · 0..n-1 أسئلة · n مراجعة · n+1 شكر
            var answers = {};
            var submitting = false;
            var reviewReturn = false; // عند التعديل من شاشة المراجعة نعود إليها مباشرة

            function remainLabel(n) {
                if (n === 0) return 'آخر سؤال';
                if (n === 1) return 'بقي سؤال واحد';
                if (n === 2) return 'بقي سؤالان';
                if (n <= 10) return 'بقيت ' + n + ' أسئلة';
                return 'بقي ' + n + ' سؤالاً';
            }

            function setProgress() {
                var inQuestions = current >= 0 && current < total;
                document.body.classList.toggle('in-progress', inQuestions);
                if (current < 0) { rail.style.width = '0%'; return; }
                var pct = current >= total ? 100 : Math.round(((current + 1) / total) * 100);
                rail.style.width = pct + '%';
                if (inQuestions) {
                    pmStep.textContent = current + 1;
                    pmLeft.textContent = remainLabel(total - current - 1);
                    pmPct.textContent = pct + '%';
                }
            }

            function activeScreen() {
                if (current < 0) return welcome;
                if (current === total) return review;
                if (current > total) return thanks;
                return screens[current];
            }

            function show(i) {
                current = i;
                [welcome, review, thanks].concat(screens).forEach(function (s) { s.classList.remove('is-active'); });
                if (current === total) fillReview();
                var sc = activeScreen();
                sc.classList.add('is-active');
                setProgress();
                window.scrollTo({ top: 0, behavior: 'smooth' });

                if (current >= 0 && current < total) {
                    var back = sc.querySelector('[data-back]');
                    if (back) back.style.visibility = current === 0 ? 'hidden' : 'visible';
                    var field = sc.querySelector('input.field, textarea.field');
                    if (field && sc.querySelector('.opts') === null) {
                        setTimeout(function () { field.focus({ preventScroll: true }); }, 380);
                    }
                }
            }

            // يملأ صفوف شاشة المراجعة من الإجابات الحالية
            function fillReview() {
                screens.forEach(function (sc) {
                    var key = sc.dataset.key;
                    var cell = review.querySelector('[data-rv="' + key + '"]');
                    if (!cell) return;
                    var v = answers[key];
                    if (v === undefined || v === null || v === '' || (Array.isArray(v) && !v.length)) {
                        cell.textContent = '—';
                        return;
                    }
                    if (Array.isArray(v)) { cell.textContent = v.join('، '); return; }
                    if (key === 'store_field' && v === 'أخرى' && answers.store_field_other) {
                        cell.textContent = 'أخرى: ' + answers.store_field_other;
                        return;
                    }
                    cell.textContent = v;
                });
            }

            function showErr(sc, msg) {
                var err = sc.querySelector('[data-err]');
                err.querySelector('[data-err-text]').textContent = msg;
                err.hidden = false;
                var card = sc.querySelector('.card');
                card.classList.remove('is-shake');
                void card.offsetWidth;
                card.classList.add('is-shake');
            }

            function clearErr(sc) {
                var err = sc.querySelector('[data-err]');
                if (err) err.hidden = true;
            }

            function digits(v) { return (v || '').replace(/\D/g, ''); }

            // يتحقق من إجابة الشاشة ويخزنها؛ يعيد false مع رسالة خطأ عند النقص
            function collect(sc, skipValidation) {
                var key = sc.dataset.key;
                var type = sc.dataset.type;
                var optional = sc.hasAttribute('data-optional') || skipValidation;

                if (type === 'choice') {
                    var picked = sc.querySelector('input[type=radio]:checked');
                    if (!picked) { showErr(sc, 'يرجى اختيار أحد الخيارات للمتابعة.'); return false; }
                    answers[key] = picked.value;
                    if (sc.hasAttribute('data-has-other')) {
                        var other = sc.querySelector('[data-other] input');
                        if (picked.value === 'أخرى' && !other.value.trim()) {
                            showErr(sc, 'يرجى توضيح المجال في الحقل الظاهر.');
                            return false;
                        }
                        answers[key + '_other'] = picked.value === 'أخرى' ? other.value.trim() : null;
                    }
                    return true;
                }

                if (type === 'multi') {
                    var checked = Array.prototype.slice.call(sc.querySelectorAll('input[type=checkbox]:checked'));
                    if (!checked.length) { showErr(sc, 'يرجى اختيار خيار واحد على الأقل.'); return false; }
                    answers[key] = checked.map(function (c) { return c.value; });
                    return true;
                }

                var field = sc.querySelector('input.field, textarea.field');
                var v = (field.value || '').trim();
                if (!v) {
                    if (optional) { answers[key] = null; return true; }
                    showErr(sc, 'هذا الحقل مطلوب للمتابعة.');
                    return false;
                }
                if (type === 'tel' && !skipValidation) {
                    var d = digits(v);
                    if (d.length < 9 || d.length > 15) { showErr(sc, 'يرجى إدخال رقم جوال صحيح.'); return false; }
                }
                if (type === 'email' && !skipValidation && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) {
                    showErr(sc, 'يرجى إدخال بريد إلكتروني صحيح.');
                    return false;
                }
                if (key === 'name' && !skipValidation && v.length < 2) {
                    showErr(sc, 'يرجى كتابة الاسم كاملاً.');
                    return false;
                }
                answers[key] = v;
                return true;
            }

            function firstName() {
                return ((answers.name || '').trim().split(/\s+/)[0]) || '';
            }

            function personalize() {
                if (CFG.personalized) return;
                var hello = document.querySelector('[data-hello]');
                if (hello && answers.name) {
                    hello.textContent = 'أهلاً ' + firstName() + ' — تشرّفنا!';
                    hello.hidden = false;
                }
            }

            function saveDraft() {
                try {
                    localStorage.setItem(CFG.storageKey, JSON.stringify({ answers: answers, current: current, t: Date.now() }));
                } catch (e) { /* التخزين المحلي غير متاح — نكمل دون حفظ */ }
            }

            function clearDraft() {
                try { localStorage.removeItem(CFG.storageKey); } catch (e) { /* لا شيء */ }
            }

            function restoreDraft() {
                var raw = null;
                try { raw = localStorage.getItem(CFG.storageKey); } catch (e) { return; }
                if (!raw) return;
                var d;
                try { d = JSON.parse(raw); } catch (e) { return; }
                if (!d || !d.answers || typeof d.current !== 'number') return;
                if (!d.t || Date.now() - d.t > 24 * 3600 * 1000) { clearDraft(); return; }
                if (d.current < 0) return;

                answers = d.answers;
                screens.forEach(function (sc) {
                    var key = sc.dataset.key;
                    var v = answers[key];
                    if (v === undefined || v === null) return;
                    if (sc.dataset.type === 'choice') {
                        var radio = sc.querySelector('input[type=radio][value="' + CSS.escape(String(v)) + '"]');
                        if (radio) radio.checked = true;
                        if (sc.hasAttribute('data-has-other') && v === 'أخرى') {
                            var wrap = sc.querySelector('[data-other]');
                            wrap.hidden = false;
                            wrap.querySelector('input').value = answers[key + '_other'] || '';
                        }
                    } else if (sc.dataset.type === 'multi') {
                        (Array.isArray(v) ? v : []).forEach(function (val) {
                            var box = sc.querySelector('input[type=checkbox][value="' + CSS.escape(String(val)) + '"]');
                            if (box) box.checked = true;
                        });
                    } else {
                        sc.querySelector('input.field, textarea.field').value = v;
                    }
                });
                personalize();
                show(Math.min(d.current, total - 1));
                showToast('تمت استعادة إجاباتك السابقة');
            }

            function showToast(msg) {
                toast.textContent = msg;
                toast.classList.add('is-on');
                setTimeout(function () { toast.classList.remove('is-on'); }, 2800);
            }

            function advance(skipValidation) {
                if (current < 0 || current >= total) return;
                var sc = screens[current];
                clearErr(sc);
                if (!collect(sc, skipValidation)) return;
                if (sc.dataset.key === 'name') personalize();
                // بعد آخر سؤال — أو بعد تعديل قادم من المراجعة — ننتقل لشاشة المراجعة
                var target = (reviewReturn || current === total - 1) ? total : current + 1;
                reviewReturn = false;
                show(target);
                saveDraft();
            }

            function goBack() {
                if (current > 0) { show(current - 1); saveDraft(); }
            }

            function failBanner(sc, msg) {
                var box = sc.querySelector('.send-err');
                if (!box) {
                    box = document.createElement('div');
                    box.className = 'send-err';
                    sc.querySelector('.q-nav').insertAdjacentElement('beforebegin', box);
                }
                box.innerHTML = '';
                box.append(msg + ' ');
                var wa = document.createElement('a');
                wa.href = 'https://wa.me/{{ $whatsapp }}';
                wa.target = '_blank';
                wa.rel = 'noopener';
                wa.textContent = 'أو راسلنا عبر واتساب';
                box.appendChild(wa);
                box.hidden = false;
            }

            function showThanks(payload) {
                clearDraft();
                var name = document.querySelector('[data-thanks-name]');
                if (name && firstName()) name.textContent = 'لك يا ' + firstName();
                if (payload && payload.reference) {
                    var box = document.querySelector('[data-req]');
                    box.hidden = false;
                    box.querySelector('[data-req-num]').textContent = payload.reference;
                }
                var doc = document.querySelector('[data-doc]');
                if (doc && payload && payload.documentUrl) {
                    doc.href = payload.documentUrl;
                    doc.hidden = false;
                }
                // رسالة واتساب جاهزة باسم العميل ورقم الطلب لمتابعة فورية سلسة
                var wa = document.querySelector('[data-wa]');
                if (wa) {
                    var who = CFG.personalized ? CFG.clientName : (answers.name || '');
                    var txt = 'مرحباً فريق وريد،' + (who ? ' أنا ' + who + '،' : '') +
                        ' أرسلت للتو طلب متجر إلكتروني' +
                        (payload && payload.reference ? ' — الرقم المرجعي: ' + payload.reference : '') + '.';
                    wa.href = 'https://wa.me/' + CFG.whatsapp + '?text=' + encodeURIComponent(txt);
                }
                show(total + 1);
            }

            function submitForm() {
                if (submitting) return;
                submitting = true;
                var sc = review;
                var btn = review.querySelector('[data-submit]');
                btn.disabled = true;
                btn.classList.add('is-loading');

                var body = {};
                Object.keys(answers).forEach(function (k) { if (answers[k] !== null) body[k] = answers[k]; });
                body.website = hp.value; // مصيدة السبام

                fetch(CFG.postUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CFG.csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(body)
                }).then(function (res) {
                    if (res.ok) {
                        return res.json().catch(function () { return {}; }).then(showThanks);
                    }
                    if (res.status === 409) {
                        return res.json().then(function (j) {
                            clearDraft();
                            failBanner(sc, j.message || 'سبق استلام طلبك.');
                            setTimeout(function () { window.location.reload(); }, 2200);
                        });
                    }
                    if (res.status === 422) {
                        return res.json().then(function (j) {
                            var keys = Object.keys(j.errors || {});
                            var first = keys.length ? keys[0].split('.')[0] : null;
                            var idx = screens.findIndex(function (s) { return s.dataset.key === first || first === s.dataset.key + '_other'; });
                            if (idx > -1) {
                                reviewReturn = true; // بعد تصحيح الإجابة يعود مباشرة لشاشة المراجعة
                                show(idx);
                                showErr(screens[idx], j.errors[keys[0]][0]);
                            } else {
                                failBanner(sc, 'تعذّر التحقق من البيانات — يرجى مراجعة الإجابات والمحاولة مرة أخرى.');
                            }
                        });
                    }
                    failBanner(sc, res.status === 419
                        ? 'انتهت صلاحية الجلسة — يرجى تحديث الصفحة ثم إعادة الإرسال (إجاباتك محفوظة).'
                        : 'تعذّر إرسال الطلب حالياً — يرجى المحاولة بعد قليل.');
                }).catch(function () {
                    failBanner(sc, 'يبدو أن الاتصال بالإنترنت متقطع — يرجى المحاولة مرة أخرى.');
                }).finally(function () {
                    submitting = false;
                    btn.disabled = false;
                    btn.classList.remove('is-loading');
                });
            }

            // ربط الأحداث
            document.querySelector('[data-start]').addEventListener('click', function () { show(0); saveDraft(); });
            review.querySelector('[data-submit]').addEventListener('click', submitForm);
            review.querySelectorAll('[data-goto]').forEach(function (row) {
                row.addEventListener('click', function () {
                    reviewReturn = true;
                    show(parseInt(row.dataset.goto, 10));
                });
            });

            screens.forEach(function (sc) {
                sc.querySelector('[data-next]').addEventListener('click', function () { advance(false); });
                var skip = sc.querySelector('[data-skip]');
                if (skip) skip.addEventListener('click', function () { advance(true); });
                sc.querySelector('[data-back]').addEventListener('click', goBack);

                if (sc.dataset.type === 'choice') {
                    sc.querySelectorAll('input[type=radio]').forEach(function (radio) {
                        radio.addEventListener('change', function () {
                            clearErr(sc);
                            var wrap = sc.querySelector('[data-other]');
                            if (wrap) {
                                var isOther = radio.value === 'أخرى';
                                wrap.hidden = !isOther;
                                if (isOther) { wrap.querySelector('input').focus(); return; } // لا تقدّم تلقائي مع «أخرى»
                            }
                            var at = current;
                            setTimeout(function () { if (current === at) advance(false); }, 320);
                        });
                    });
                }
                sc.querySelectorAll('input.field, textarea.field').forEach(function (f) {
                    f.addEventListener('input', function () { clearErr(sc); });
                });
            });

            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Enter' || e.isComposing) return;
                if (e.target && e.target.closest('a, button')) return;
                if (current === -1 && welcome.classList.contains('is-active')) { e.preventDefault(); show(0); return; }
                if (current === total) { e.preventDefault(); submitForm(); return; } // من المراجعة: Enter يرسل
                if (current < 0 || current > total) return;
                var sc = screens[current];
                if (sc.dataset.type === 'textarea' && !(e.ctrlKey || e.metaKey)) return; // داخل الملاحظات: Enter سطر جديد
                e.preventDefault();
                advance(false);
            });

            restoreDraft();
            setProgress();
        })();
    </script>
</body>
</html>
