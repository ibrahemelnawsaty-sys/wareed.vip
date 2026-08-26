{{--
    نموذج عرض السعر التفاعلي — سؤال واحد في كل شاشة (وضع أبيض بهوية وريد).
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
    <link href="https://fonts.bunny.net/css?family=ibm-plex-sans-arabic:400,500,600,700|el-messiri:600,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue: #3b82f6; --blue-deep: #2563eb; --violet: #8b5cf6; --teal: #14b8a6;
            --grad: linear-gradient(120deg, #3b82f6 0%, #8b5cf6 48%, #2dd4bf 100%);
            --bg: #f5f8fe; --card: #ffffff;
            --ink: #0e1a36; --muted: #5a6a8d;
            --line: #e4eaf7; --line-strong: #ccd8ee;
            --err: #dc2626;
            --shadow-card: 0 26px 70px -34px rgba(37, 99, 235, .28), 0 4px 18px -8px rgba(14, 26, 54, .08);
            --shadow-btn: 0 14px 32px -14px rgba(99, 102, 241, .55);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; -webkit-text-size-adjust: 100%; }
        body {
            font-family: 'IBM Plex Sans Arabic', 'Tajawal', ui-sans-serif, system-ui, sans-serif;
            background: var(--bg); color: var(--ink);
            line-height: 1.8; min-height: 100svh;
            -webkit-font-smoothing: antialiased; text-rendering: optimizeLegibility;
            overflow-x: hidden;
        }
        h1, h2, h3 { font-family: 'El Messiri', 'IBM Plex Sans Arabic', sans-serif; line-height: 1.3; }
        button, input, textarea { font: inherit; color: inherit; }
        a { color: inherit; text-decoration: none; }

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
            background: rgba(255, 255, 255, .82);
            backdrop-filter: blur(14px) saturate(150%); -webkit-backdrop-filter: blur(14px) saturate(150%);
            border-bottom: 1px solid var(--line);
        }
        .brand { display: flex; align-items: center; gap: 10px; font-family: 'El Messiri', sans-serif; font-size: 1.35rem; font-weight: 700; }
        .brand svg { width: 34px; height: 34px; flex: 0 0 34px; }
        .top-tag { font-size: .8rem; font-weight: 600; color: var(--muted); background: #eef3fc; border: 1px solid var(--line); border-radius: 999px; padding: 5px 14px; white-space: nowrap; }

        /* شريط التقدم */
        .rail { position: fixed; inset-inline: 0; top: 59px; z-index: 40; height: 4px; background: #e7edf9; }
        .rail-fill { height: 100%; width: 0%; background: var(--grad); border-radius: 99px 0 0 99px; transition: width .5s cubic-bezier(.22, 1, .36, 1); }
        .pm {
            position: fixed; inset-inline: 0; top: 63px; z-index: 39;
            display: none; justify-content: center; align-items: center; gap: 10px;
            padding: 7px 16px; font-size: .82rem; font-weight: 600; color: var(--muted);
            background: rgba(245, 248, 254, .9); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(228, 234, 247, .7);
        }
        body.in-progress .pm { display: flex; }
        .pm b { color: var(--blue-deep); font-weight: 700; }
        .pm .pct { font-weight: 700; background: var(--grad); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
        .pm .dot { opacity: .5; }

        /* ===== المسرح والشاشات ===== */
        .stage { position: relative; z-index: 1; display: flex; flex-direction: column; min-height: 100svh; padding: 122px clamp(14px, 4vw, 28px) 54px; }
        .screen { display: none; width: 100%; max-width: 680px; margin: auto; }
        .screen.is-active { display: block; animation: rise .55s cubic-bezier(.22, 1, .36, 1) both; }
        @keyframes rise { from { opacity: 0; transform: translateY(26px); } to { opacity: 1; transform: none; } }

        .card {
            background: var(--card); border: 1px solid var(--line); border-radius: 26px;
            box-shadow: var(--shadow-card);
            padding: clamp(26px, 5.5vw, 46px);
            position: relative; overflow: hidden;
        }
        .card::before { content: ""; position: absolute; inset-inline: 0; top: 0; height: 4px; background: var(--grad); opacity: .9; }
        .card.is-shake { animation: shake .45s; }
        @keyframes shake { 0%, 100% { transform: none; } 25% { transform: translateX(7px); } 50% { transform: translateX(-6px); } 75% { transform: translateX(4px); } }

        /* ===== شاشة الترحيب والشكر ===== */
        .welcome, .thanks { text-align: center; }
        .w-mark { display: flex; justify-content: center; margin-bottom: 18px; }
        .w-mark svg { width: 68px; height: 68px; filter: drop-shadow(0 12px 26px rgba(99, 102, 241, .35)); }
        .w-chip {
            display: inline-flex; align-items: center; gap: 8px; margin-bottom: 16px;
            font-size: .85rem; font-weight: 700; color: #0d9488;
            background: rgba(45, 212, 191, .12); border: 1px solid rgba(20, 184, 166, .35);
            border-radius: 999px; padding: 6px 16px;
        }
        .w-chip .pulse { width: 8px; height: 8px; border-radius: 50%; background: var(--teal); animation: pulse 2.2s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(45, 212, 191, .5); } 70% { box-shadow: 0 0 0 10px rgba(45, 212, 191, 0); } 100% { box-shadow: 0 0 0 0 rgba(45, 212, 191, 0); } }
        .welcome h1 { font-size: clamp(1.7rem, 5.4vw, 2.5rem); margin-bottom: 14px; }
        .w-lead { color: var(--muted); font-size: clamp(.98rem, 2.6vw, 1.1rem); max-width: 52ch; margin: 0 auto 22px; }
        .w-meta { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-bottom: 28px; }
        .w-meta span { font-size: .84rem; font-weight: 600; color: var(--muted); background: #f2f6fd; border: 1px solid var(--line); border-radius: 999px; padding: 6px 14px; }

        /* ===== الأسئلة ===== */
        .q-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 16px; }
        .q-chip { font-size: .8rem; font-weight: 700; color: var(--blue-deep); background: rgba(59, 130, 246, .1); border: 1px solid rgba(59, 130, 246, .25); border-radius: 999px; padding: 4px 13px; white-space: nowrap; }
        .q-hello { font-size: .92rem; font-weight: 700; color: #0d9488; }
        .q-title { font-size: clamp(1.35rem, 4.4vw, 1.8rem); margin-bottom: 8px; }
        .q-hint { color: var(--muted); font-size: .95rem; margin-bottom: 22px; }
        .q-title + .q-nav, .q-title + .field, .q-title + .opts { margin-top: 22px; }

        .field {
            width: 100%; padding: 15px 18px; border-radius: 15px;
            border: 1.6px solid var(--line-strong); background: #fbfcff;
            font-size: 1.05rem; transition: border-color .25s, box-shadow .25s, background .25s; outline: none;
        }
        .field::placeholder { color: #9aa9c7; }
        .field:focus { border-color: var(--blue); background: #fff; box-shadow: 0 0 0 4px rgba(59, 130, 246, .14); }
        .field.ltr { direction: ltr; text-align: start; }
        textarea.field { resize: vertical; min-height: 130px; line-height: 1.75; }

        .opts { display: flex; flex-direction: column; gap: 11px; }
        .opts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 11px; }
        .opt {
            position: relative; display: flex; align-items: center; gap: 12px;
            border: 1.6px solid var(--line-strong); border-radius: 15px;
            padding: 13px 16px; cursor: pointer; background: #fbfcff; user-select: none;
            transition: border-color .2s, background .2s, transform .2s, box-shadow .2s;
        }
        .opt:hover { border-color: #93b4fd; background: #f6f9ff; transform: translateY(-1px); }
        .opt input { position: absolute; opacity: 0; pointer-events: none; }
        .opt-ic { font-size: 1.35rem; flex: 0 0 auto; line-height: 1; }
        .opt-tx { font-size: .98rem; font-weight: 600; flex: 1; }
        .opt-check {
            flex: 0 0 22px; width: 22px; height: 22px; border-radius: 50%;
            border: 1.8px solid var(--line-strong); display: flex; align-items: center; justify-content: center;
            transition: all .2s; color: transparent;
        }
        .opt-check svg { width: 12px; height: 12px; }
        .opt:has(input:checked) {
            border-color: transparent; box-shadow: 0 10px 26px -14px rgba(99, 102, 241, .45);
            background: linear-gradient(#f6f9ff, #f6f9ff) padding-box, var(--grad) border-box;
        }
        .opt:has(input:checked) .opt-check { border-color: transparent; background: var(--grad); color: #fff; }
        .opt:has(input:focus-visible) { outline: 3px solid rgba(59, 130, 246, .35); outline-offset: 2px; }
        .other-wrap { margin-top: 12px; }

        .q-err { display: flex; align-items: center; gap: 7px; color: var(--err); font-size: .88rem; font-weight: 600; margin-top: 12px; }
        .q-err[hidden] { display: none; }

        /* ===== الأزرار ===== */
        .q-nav { display: flex; align-items: center; flex-wrap: wrap; gap: 12px; margin-top: 26px; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 9px;
            padding: 13px 30px; border-radius: 14px; border: 1px solid transparent;
            font-weight: 700; font-size: 1rem; cursor: pointer;
            transition: transform .22s, box-shadow .3s, background .25s, border-color .25s, opacity .2s;
        }
        .btn:disabled { opacity: .65; cursor: wait; transform: none !important; }
        .btn-primary { background: var(--grad); color: #fff; box-shadow: var(--shadow-btn); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 20px 44px -16px rgba(99, 102, 241, .65); }
        .btn-lg { padding: 15px 44px; font-size: 1.1rem; }
        .btn-skip { background: transparent; color: var(--muted); border-color: var(--line-strong); }
        .btn-skip:hover { border-color: var(--blue); color: var(--blue-deep); }
        .btn-back { margin-inline-start: auto; display: inline-flex; align-items: center; gap: 6px; background: none; border: none; color: var(--muted); font-size: .9rem; font-weight: 600; cursor: pointer; padding: 8px 4px; }
        .btn-back:hover { color: var(--blue-deep); }
        .btn-back svg { width: 15px; height: 15px; }
        .key-hint { font-size: .78rem; color: #9aa9c7; font-weight: 500; }
        .key-hint b { color: var(--muted); }
        @media (hover: none) { .key-hint { display: none; } }

        .spinner { width: 17px; height: 17px; border: 2.4px solid rgba(255, 255, 255, .4); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; display: none; }
        .btn.is-loading .spinner { display: inline-block; }
        .btn.is-loading .btn-label { opacity: .85; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ===== شاشة الشكر ===== */
        .check {
            width: 86px; height: 86px; margin: 4px auto 20px; border-radius: 50%;
            background: var(--grad); display: flex; align-items: center; justify-content: center;
            box-shadow: 0 20px 46px -16px rgba(99, 102, 241, .6);
            animation: pop .6s cubic-bezier(.22, 1.4, .36, 1) both .15s;
        }
        .check svg { width: 40px; height: 40px; color: #fff; }
        .check svg path { stroke-dasharray: 60; stroke-dashoffset: 60; animation: draw .55s ease-out forwards .5s; }
        @keyframes pop { from { transform: scale(.4); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        @keyframes draw { to { stroke-dashoffset: 0; } }
        .thanks h2 { font-size: clamp(1.6rem, 5vw, 2.2rem); margin-bottom: 12px; }
        .t-lead { color: var(--muted); max-width: 46ch; margin: 0 auto 20px; }
        .promise {
            background: linear-gradient(150deg, rgba(59, 130, 246, .08), rgba(45, 212, 191, .07));
            border: 1px solid rgba(59, 130, 246, .25); border-radius: 16px;
            padding: 15px 20px; font-size: 1rem; margin-bottom: 26px;
        }
        .promise b { color: var(--blue-deep); }
        .t-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; }
        .btn-wa { background: linear-gradient(135deg, #25d366, #128c7e); color: #fff; box-shadow: 0 14px 30px -12px rgba(37, 211, 102, .55); }
        .btn-wa:hover { transform: translateY(-2px); }
        .btn-wa svg { width: 19px; height: 19px; }

        .send-err {
            margin-top: 16px; padding: 13px 16px; border-radius: 14px; font-size: .92rem; font-weight: 600;
            color: #991b1b; background: #fef2f2; border: 1px solid #fecaca;
        }
        .send-err a { color: #0d9488; text-decoration: underline; }
        .send-err[hidden] { display: none; }

        .toast {
            position: fixed; bottom: 26px; inset-inline-start: 50%; transform: translateX(50%);
            z-index: 60; background: var(--ink); color: #fff; font-size: .88rem; font-weight: 600;
            padding: 11px 22px; border-radius: 999px; box-shadow: 0 18px 40px -14px rgba(14, 26, 54, .5);
            opacity: 0; pointer-events: none; transition: opacity .4s, transform .4s;
        }
        .toast.is-on { opacity: 1; transform: translateX(50%) translateY(-4px); }

        .minifoot { position: relative; z-index: 1; text-align: center; padding: 0 16px 26px; color: #9aa9c7; font-size: .8rem; }
        .minifoot a { color: var(--muted); font-weight: 600; }

        .hp { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }

        @media (max-width: 560px) {
            .opts-grid { grid-template-columns: 1fr; }
            .pm .hide-sm { display: none; }
            .top-tag { display: none; }
            .brand { font-size: 1.2rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after { animation: none !important; transition: none !important; }
        }
    </style>
</head>
<body>

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
        <span class="top-tag">✦ نموذج عرض سعر — المتاجر الإلكترونية</span>
    </header>
    <div class="rail"><div class="rail-fill" data-rail></div></div>
    <div class="pm" aria-live="polite">
        <span>السؤال <b data-pm-step>1</b> من {{ $qCount }}</span>
        <span class="dot">·</span><span class="hide-sm" data-pm-left></span>
        <span class="dot hide-sm">·</span><span class="pct" data-pm-pct>0%</span>
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
                    <h1>أهلاً {{ $f ? 'بكِ' : 'بك' }} <span class="grad-text">{{ $client['name'] }}</span> 👋</h1>
                    <p class="w-lead">يسعدنا اهتمامك بإطلاق متجرك الإلكتروني مع وريد. أعددنا هذا النموذج خصيصاً {{ $f ? 'لكِ' : 'لك' }} — {{ $qCount }} {{ $qWord }} سريعة، وبناءً على إجاباتك سنجهّز عرض سعر دقيقاً يناسب المتجر تماماً.</p>
                @else
                    <span class="w-chip"><span class="pulse"></span> طلبات المتاجر الإلكترونية</span>
                    <h1>أهلاً بك في <span class="grad-text">وريد</span> 👋</h1>
                    <p class="w-lead">دقائق قليلة تفصلنا عن فهم متجرك: {{ $qCount }} {{ $qWord }} سريعة، وبناءً على إجاباتك يصلك عرض سعر مخصّص خلال 24 ساعة.</p>
                @endif
                <div class="w-meta">
                    <span>⏱️ أقل من 3 دقائق</span>
                    <span>📝 {{ $qCount }} {{ $qWord }}</span>
                    <span>🔒 بياناتك سرّية بالكامل</span>
                </div>
                <button type="button" class="btn btn-primary btn-lg" data-start>لنبدأ 🚀</button>
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
                        @if(! $client && $q['key'] === 'phone')
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
                                    @if(! empty($opt['icon']))<span class="opt-ic">{{ $opt['icon'] }}</span>@endif
                                    <span class="opt-tx">{{ $opt['label'] }}</span>
                                    <span class="opt-check" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    </span>
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

                    <p class="q-err" data-err hidden role="alert"></p>

                    <div class="q-nav">
                        <button type="button" class="btn btn-primary" data-next>
                            <span class="spinner" aria-hidden="true"></span>
                            <span class="btn-label">التالي</span>
                        </button>
                        @if($q['optional'] ?? false)
                            <button type="button" class="btn btn-skip" data-skip>تخطّي</button>
                        @endif
                        <span class="key-hint">اضغط <b>Enter ↵</b></span>
                        <button type="button" class="btn-back" data-back>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                            السؤال السابق
                        </button>
                    </div>
                </div>
            </section>
        @endforeach

        {{-- شاشة الشكر --}}
        <section class="screen" data-thanks>
            <div class="card thanks">
                <div class="check" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </div>
                @if($client)
                    <h2>شكراً {{ $f ? 'لكِ' : 'لك' }} <span class="grad-text">{{ $client['short_name'] }}</span> 💙</h2>
                @else
                    <h2>شكراً <span data-thanks-name>لك</span> 💙</h2>
                @endif
                <p class="t-lead">وصلنا الطلب بنجاح، وبدأ فريق وريد دراسة الإجابات بعناية لتجهيز أفضل عرض ممكن.</p>
                <div class="promise">📩 سيصلك <b>عرض السعر المخصّص</b> خلال <b>24 ساعة</b> بإذن الله.</div>
                <div class="t-actions">
                    <a class="btn btn-wa" href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 018.413 3.488 11.82 11.82 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 001.51 5.26l-.999 3.648 3.737-.97a9.86 9.86 0 00.241.263z"/></svg>
                        التواصل المباشر عبر واتساب
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
            var thanks = document.querySelector('[data-thanks]');
            var rail = document.querySelector('[data-rail]');
            var pmStep = document.querySelector('[data-pm-step]');
            var pmLeft = document.querySelector('[data-pm-left]');
            var pmPct = document.querySelector('[data-pm-pct]');
            var toast = document.querySelector('[data-toast]');
            var hp = document.querySelector('[data-hp]');
            var total = screens.length;
            var current = -1; // -1 ترحيب · 0..n-1 أسئلة · n شكر
            var answers = {};
            var submitting = false;

            function remainLabel(n) {
                if (n === 0) return 'آخر سؤال 🎉';
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
                if (current >= total) return thanks;
                return screens[current];
            }

            function show(i) {
                current = i;
                [welcome, thanks].concat(screens).forEach(function (s) { s.classList.remove('is-active'); });
                var sc = activeScreen();
                sc.classList.add('is-active');
                setProgress();
                window.scrollTo({ top: 0, behavior: 'smooth' });

                if (current >= 0 && current < total) {
                    var next = sc.querySelector('[data-next] .btn-label');
                    if (next) next.textContent = current === total - 1 ? 'إرسال الطلب ✨' : 'التالي';
                    var back = sc.querySelector('[data-back]');
                    if (back) back.style.visibility = current === 0 ? 'hidden' : 'visible';
                    var field = sc.querySelector('input.field, textarea.field');
                    if (field && sc.querySelector('.opts') === null) {
                        setTimeout(function () { field.focus({ preventScroll: true }); }, 380);
                    }
                }
            }

            function showErr(sc, msg) {
                var err = sc.querySelector('[data-err]');
                err.textContent = '⚠ ' + msg;
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
                    hello.textContent = 'أهلاً ' + firstName() + ' 👋 تشرّفنا!';
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
                showToast('تمت استعادة إجاباتك السابقة ✨');
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
                if (current === total - 1) { submitForm(); return; }
                show(current + 1);
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

            function submitForm() {
                if (submitting) return;
                submitting = true;
                var sc = screens[total - 1];
                var btn = sc.querySelector('[data-next]');
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
                        clearDraft();
                        var name = document.querySelector('[data-thanks-name]');
                        if (name && firstName()) name.textContent = 'لك يا ' + firstName();
                        show(total);
                        return;
                    }
                    if (res.status === 422) {
                        return res.json().then(function (j) {
                            var keys = Object.keys(j.errors || {});
                            var first = keys.length ? keys[0].split('.')[0] : null;
                            var idx = screens.findIndex(function (s) { return s.dataset.key === first || first === s.dataset.key + '_other'; });
                            if (idx > -1) {
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
                if (current === -1 && welcome.classList.contains('is-active')) { e.preventDefault(); show(0); return; }
                if (current < 0 || current >= total) return;
                var sc = screens[current];
                if (sc.dataset.type === 'textarea' && !(e.ctrlKey || e.metaKey)) return; // داخل الملاحظات: Enter سطر جديد
                if (e.target && e.target.closest('a, button')) return;
                e.preventDefault();
                advance(false);
            });

            restoreDraft();
            setProgress();
        })();
    </script>
</body>
</html>
