{{--
    صفحة حالة الطلب — تُعرض للعميل عند فتح رابطه المخصّص بعد إرسال طلبه.
    الرابط يقبل طلباً واحداً فقط، فتحلّ هذه الصفحة محل النموذج.
--}}
@php
    $f = ($client['gender'] ?? null) === 'f';
    $issued = $sr->created_at;
    $months = [1=>'يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
    $fmt = fn ($d) => $d->day.' '.$months[(int) $d->month].' '.$d->year.' — '.$d->format('H:i');
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#ffffff">
    <title>حالة الطلب {{ $sr->reference }} — وريد</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=ibm-plex-sans-arabic:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue: #3b82f6; --blue-deep: #2563eb; --teal: #14b8a6;
            --grad: linear-gradient(120deg, #3b82f6 0%, #8b5cf6 48%, #2dd4bf 100%);
            --bg: #f6f8fd; --card: #fff;
            --ink: #0d1830; --muted: #5b6a8c; --faint: #9aa9c7;
            --line: #e6ebf7; --line-strong: #cfdaef;
            --shadow-card: 0 1px 2px rgba(13,24,48,.04), 0 12px 28px -14px rgba(13,24,48,.07), 0 36px 90px -42px rgba(37,99,235,.25);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'IBM Plex Sans Arabic', 'Tajawal', ui-sans-serif, system-ui, sans-serif;
            background: var(--bg); color: var(--ink); line-height: 1.85; min-height: 100svh;
            -webkit-font-smoothing: antialiased;
        }
        h1, h2 { font-weight: 700; line-height: 1.4; }
        a { color: inherit; text-decoration: none; }
        button { font: inherit; }
        body::before {
            content: ""; position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background-image:
                linear-gradient(rgba(37,99,235,.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(20,184,166,.045) 1px, transparent 1px);
            background-size: 44px 44px;
            -webkit-mask-image: radial-gradient(130% 105% at 50% 0%, #000 30%, transparent 82%);
            mask-image: radial-gradient(130% 105% at 50% 0%, #000 30%, transparent 82%);
        }
        body::after {
            content: ""; position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background-image:
                radial-gradient(900px 560px at 88% -8%, rgba(59,130,246,.10), transparent 60%),
                radial-gradient(820px 620px at -4% 18%, rgba(139,92,246,.09), transparent 55%);
        }
        .grad-text { background: var(--grad); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
        .ic { width: 20px; height: 20px; flex: 0 0 auto; vertical-align: -.22em; }

        .topbar {
            position: sticky; top: 0; z-index: 20;
            display: flex; align-items: center; justify-content: space-between; gap: 14px;
            padding: 12px clamp(16px, 4vw, 34px);
            background: rgba(255,255,255,.85); backdrop-filter: blur(16px) saturate(160%);
            border-bottom: 1px solid var(--line);
        }
        .brand { display: flex; align-items: center; gap: 10px; font-size: 1.3rem; font-weight: 700; }
        .brand svg { width: 34px; height: 34px; }
        .top-tag {
            display: inline-flex; align-items: center; gap: 7px; font-size: .8rem; font-weight: 600; color: var(--muted);
            background: #f1f5fd; border: 1px solid var(--line); border-radius: 999px; padding: 6px 15px; white-space: nowrap;
        }

        .wrap { position: relative; z-index: 1; max-width: 700px; margin: 0 auto; padding: clamp(26px,5vw,54px) clamp(14px,4vw,28px) 54px; }
        .card {
            background: var(--card); border: 1px solid var(--line); border-radius: 28px;
            box-shadow: var(--shadow-card); padding: clamp(26px,5.5vw,46px); position: relative; overflow: hidden;
        }
        .card::before { content: ""; position: absolute; inset-inline: 0; top: 0; height: 3px; background: var(--grad); }

        .center { text-align: center; }
        .seal {
            width: 84px; height: 84px; margin: 0 auto 20px; border-radius: 50%;
            background: var(--grad); display: flex; align-items: center; justify-content: center;
            box-shadow: 0 20px 46px -16px rgba(99,102,241,.6);
        }
        .seal .ic { width: 38px; height: 38px; color: #fff; }
        .chip {
            display: inline-flex; align-items: center; gap: 8px; margin-bottom: 15px;
            font-size: .84rem; font-weight: 700; color: #0d9488;
            background: rgba(45,212,191,.1); border: 1px solid rgba(20,184,166,.32);
            border-radius: 999px; padding: 6px 17px;
        }
        .pulse { width: 8px; height: 8px; border-radius: 50%; background: var(--teal); animation: pulse 2.2s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(45,212,191,.5); } 70% { box-shadow: 0 0 0 10px rgba(45,212,191,0); } 100% { box-shadow: 0 0 0 0 rgba(45,212,191,0); } }
        h1 { font-size: clamp(1.5rem, 4.6vw, 2rem); margin-bottom: 12px; letter-spacing: -.02em; }
        .lead { color: var(--muted); max-width: 48ch; margin: 0 auto 24px; }

        .ref-box {
            display: flex; flex-direction: column; align-items: center; gap: 3px;
            margin: 0 auto 24px; padding: 15px 26px; max-width: 400px;
            background: #f7f9fe; border: 1.5px dashed rgba(59,130,246,.4); border-radius: 16px;
        }
        .ref-label { font-size: .76rem; font-weight: 600; color: var(--muted); letter-spacing: .04em; }
        .ref-num { font-size: 1.2rem; font-weight: 700; letter-spacing: .06em; direction: ltr; font-variant-numeric: tabular-nums;
            background: var(--grad); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
        .ref-date { font-size: .76rem; color: var(--faint); }

        .chip-done { color: #1d4ed8; background: rgba(59,130,246,.1); border-color: rgba(59,130,246,.35); }
        .meet-card {
            display: flex; align-items: center; gap: 14px; text-align: start;
            margin: 0 0 24px; padding: 16px 22px; border-radius: 18px;
            background: #f7f9fe; border: 1px solid var(--line-strong);
        }
        .meet-card .ic { width: 26px; height: 26px; color: var(--blue-deep); }
        .meet-card .ml { display: block; font-size: .78rem; font-weight: 600; color: var(--muted); }
        .meet-card .mv { font-size: 1.02rem; font-weight: 700; }
        .quote-card {
            display: flex; flex-wrap: wrap; align-items: center; gap: 16px; text-align: start;
            margin: 0 0 26px; padding: 20px 24px; border-radius: 20px;
            background: linear-gradient(150deg, rgba(59,130,246,.09), rgba(45,212,191,.07));
            border: 1px solid rgba(59,130,246,.3);
        }
        .quote-total { display: flex; flex-direction: column; gap: 2px; }
        .quote-total .ql { font-size: .78rem; font-weight: 600; color: var(--muted); }
        .quote-total .qv {
            font-size: 1.85rem; font-weight: 700; line-height: 1.15; font-variant-numeric: tabular-nums;
            background: var(--grad); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
        }
        .quote-total .qv small { font-size: 1rem; }
        .quote-total .qmeta { font-size: .76rem; color: var(--faint); }
        .quote-card .btn { margin-inline-start: auto; }
        .timer-wrap[hidden] { display: none; }

        /* المؤقّت */
        .timer-wrap { margin-bottom: 26px; }
        .timer-title { display: flex; align-items: center; justify-content: center; gap: 8px; font-size: .88rem; font-weight: 600; color: var(--muted); margin-bottom: 12px; }
        .timer-title .ic { color: var(--blue-deep); }
        .timer { display: flex; justify-content: center; gap: 10px; }
        .tcell {
            min-width: 74px; padding: 12px 8px 9px; border-radius: 15px;
            background: #f7f9fe; border: 1px solid var(--line-strong);
        }
        .tval {
            font-size: 1.6rem; font-weight: 700; line-height: 1.1; direction: ltr; font-variant-numeric: tabular-nums;
            background: var(--grad); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
        }
        .tlab { font-size: .72rem; font-weight: 600; color: var(--faint); }
        .bar { height: 7px; border-radius: 99px; background: #e9eefa; overflow: hidden; margin-top: 14px; }
        .bar > i { display: block; height: 100%; border-radius: 99px; background: var(--grad); transition: width .6s; }
        .bar-note { font-size: .76rem; color: var(--faint); margin-top: 8px; }
        .timer.is-done .tcell { background: rgba(45,212,191,.08); border-color: rgba(20,184,166,.35); }

        .steps { display: flex; flex-direction: column; gap: 0; margin: 0 0 26px; text-align: start; }
        .step { display: flex; gap: 13px; }
        .step-rail { display: flex; flex-direction: column; align-items: center; flex: 0 0 26px; }
        .step-dot {
            width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            border: 1.8px solid var(--line-strong); background: #fff; color: var(--faint); flex: 0 0 26px;
        }
        .step-dot .ic { width: 13px; height: 13px; }
        .step.done .step-dot { background: var(--grad); border-color: transparent; color: #fff; }
        .step.active .step-dot { border-color: var(--blue); color: var(--blue-deep); box-shadow: 0 0 0 4px rgba(59,130,246,.13); }
        .step-line { flex: 1; width: 2px; background: var(--line); margin: 3px 0; min-height: 18px; }
        .step:last-child .step-line { display: none; }
        .step-body { padding-bottom: 16px; }
        .step-t { font-size: .95rem; font-weight: 700; }
        .step-d { font-size: .82rem; color: var(--muted); }
        .step:not(.done):not(.active) .step-t { color: var(--muted); font-weight: 600; }

        details.summary-box { border: 1px solid var(--line); border-radius: 16px; text-align: start; margin-bottom: 24px; overflow: hidden; }
        details.summary-box > summary {
            cursor: pointer; padding: 13px 18px; font-size: .9rem; font-weight: 700; list-style: none;
            display: flex; align-items: center; gap: 9px; background: #fbfcff;
        }
        details.summary-box > summary::-webkit-details-marker { display: none; }
        details.summary-box > summary .ic { color: var(--blue-deep); }
        .rv-row { display: flex; gap: 13px; padding: 9px 18px; border-top: 1px solid var(--line); font-size: .88rem; }
        .rv-q { flex: 0 0 108px; color: var(--muted); font-size: .8rem; font-weight: 600; }
        .rv-a { flex: 1; font-weight: 600; overflow-wrap: anywhere; }

        .actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 11px; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 9px;
            padding: 13px 28px; border-radius: 14px; border: 1px solid transparent;
            font-weight: 700; font-size: .98rem; cursor: pointer; transition: transform .22s, box-shadow .3s;
        }
        .btn-primary { background: var(--grad); color: #fff; box-shadow: 0 12px 30px -12px rgba(99,102,241,.55); }
        .btn-primary:hover { transform: translateY(-2px); }
        .btn-wa { background: linear-gradient(135deg,#25d366,#128c7e); color: #fff; box-shadow: 0 14px 30px -12px rgba(37,211,102,.55); }
        .btn-wa:hover { transform: translateY(-2px); }
        .btn-ghost { background: transparent; color: var(--muted); border-color: var(--line-strong); }
        .btn-ghost:hover { border-color: var(--blue); color: var(--blue-deep); }

        .minifoot { position: relative; z-index: 1; text-align: center; padding: 0 16px 26px; color: var(--faint); font-size: .8rem; }
        @media (max-width: 560px) { .tcell { min-width: 62px; } .tval { font-size: 1.35rem; } .rv-q { flex-basis: 88px; } }
        @media (prefers-reduced-motion: reduce) { *, ::before, ::after { animation: none !important; transition: none !important; } }
    </style>
</head>
<body>
@include('quote._icons')

<header class="topbar">
    <a class="brand" href="{{ url('/') }}" aria-label="وريد">
        <svg viewBox="0 0 40 40" fill="none" aria-hidden="true">
            <defs><linearGradient id="sm" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#3B82F6"/><stop offset=".5" stop-color="#8B5CF6"/><stop offset="1" stop-color="#2DD4BF"/>
            </linearGradient></defs>
            <rect x="2.5" y="2.5" width="35" height="35" rx="11" stroke="url(#sm)" stroke-width="2" opacity=".5"/>
            <path d="M11 27 L18 14 L24 23 L29 13" stroke="url(#sm)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="11" cy="27" r="2.5" fill="#2DD4BF"/><circle cx="18" cy="14" r="2.5" fill="#3B82F6"/>
            <circle cx="24" cy="23" r="2.5" fill="#8B5CF6"/><circle cx="29" cy="13" r="2.5" fill="#60A5FA"/>
        </svg>
        <span class="grad-text">وريد</span>
    </a>
    <span class="top-tag"><svg class="ic" style="width:15px;height:15px"><use href="#i-document"/></svg> حالة طلب المتجر</span>
</header>

<main class="wrap">
    <div class="card center">
        @php
            $stage = $flow['stage'];
            $stageInfo = $stages[$stage];
            $headline = match ($stage) {
                'awaiting_meeting' => 'تم استلام طلبك يا',
                'meeting_scheduled' => 'اجتماعنا محدّد يا',
                'quote_due' => 'نجهّز عرض سعرك الآن يا',
                'awaiting_approval' => 'عرض سعر متجرك جاهز يا',
                'awaiting_requirements' => 'اعتُمد عرض متجرك يا',
                'in_progress' => 'بدأ تنفيذ متجرك يا',
                'delivered' => 'تم تسليم متجرك يا',
            };
        @endphp

        <div class="seal"><svg class="ic"><use href="#i-{{ $stageInfo['icon'] }}"/></svg></div>
        <span class="chip {{ $stageInfo['countdown'] ? '' : 'chip-done' }}">
            @if ($stageInfo['countdown'])<span class="pulse"></span>@else
                <svg class="ic" style="width:14px;height:14px"><use href="#i-check"/></svg>
            @endif
            {{ $stageInfo['label'] }}
        </span>
        <h1>{{ $headline }} <span class="grad-text">{{ $client['short_name'] }}</span></h1>
        <p class="lead">{{ $stageInfo['client'] }}</p>

        <div class="ref-box">
            <span class="ref-label">الرقم المرجعي للطلب</span>
            <b class="ref-num">{{ $sr->reference }}</b>
            <span class="ref-date">تاريخ الاستلام: {{ $fmt($issued) }}</span>
        </div>

        @if ($flow['stage'] === 'meeting_scheduled' && $flow['meeting_at'])
            <div class="meet-card">
                <svg class="ic"><use href="#i-calendar"/></svg>
                <div>
                    <span class="ml">موعد الاجتماع التعريفي</span>
                    <b class="mv">{{ $fmt($flow['meeting_at']) }}</b>
                </div>
            </div>
        @endif

        @if ($quote)
            @php $money = fn ($n) => number_format((float) $n, ((float) $n == (int) $n) ? 0 : 2); @endphp
            <div class="quote-card">
                <div class="quote-total">
                    <span class="ql">إجمالي عرض السعر</span>
                    <b class="qv">{{ $money($quote['total']) }} <small>{{ $quote['currency'] }}</small></b>
                    <span class="qmeta">
                        {{ count($quote['items']) }} بنود · صالح حتى {{ $quote['valid_until']->format('Y/m/d') }}
                        @if ($quote['timeline']) · مدة التنفيذ: {{ $quote['timeline'] }} @endif
                    </span>
                </div>
                <a class="btn btn-primary" href="{{ route('quote.proposal', $inviteSlug) }}" target="_blank" rel="noopener">
                    <svg class="ic"><use href="#i-download"/></svg> استعراض عرض السعر وتحميله
                </a>
            </div>
        @endif

        @if ($flow['stage'] === 'awaiting_requirements')
            <div class="quote-card">
                <div class="quote-total">
                    <span class="ql">الخطوة التالية</span>
                    <b class="qv" style="font-size:1.15rem">ارفع متطلبات مشروعك</b>
                    <span class="qmeta">
                        @if (count($requirements))
                            رُفع {{ count($requirements) }} ملف حتى الآن — يمكنك إضافة المزيد
                        @else
                            ملفات الهوية البصرية وبيانات المنتجات لتنطلق مرحلة التنفيذ
                        @endif
                    </span>
                </div>
                <a class="btn btn-primary" href="{{ $proposalUrl }}#requirements" target="_blank" rel="noopener">
                    <svg class="ic"><use href="#i-box"/></svg> رفع الملفات الآن
                </a>
            </div>
        @endif

        <div class="timer-wrap" data-counting="{{ $flow['counting'] ? '1' : '0' }}" @unless ($flow['counting']) hidden @endunless>
            <div class="timer-title">
                <svg class="ic"><use href="#i-clock"/></svg>
                <span data-timer-title>
                    {{ $flow['stage'] === 'in_progress' ? 'الوقت المتبقي لتسليم المتجر' : 'الوقت المتبقي لتسليم عرض السعر' }}
                </span>
            </div>
            <div class="timer" data-timer
                 data-deadline="{{ $flow['count_to']?->toIso8601String() }}"
                 data-start="{{ $flow['count_from']?->toIso8601String() }}">
                <div class="tcell"><div class="tval" data-d>--</div><div class="tlab">يوم</div></div>
                <div class="tcell"><div class="tval" data-h>--</div><div class="tlab">ساعة</div></div>
                <div class="tcell"><div class="tval" data-m>--</div><div class="tlab">دقيقة</div></div>
                <div class="tcell"><div class="tval" data-s>--</div><div class="tlab">ثانية</div></div>
            </div>
            <div class="bar"><i data-bar style="width:0%"></i></div>
            <p class="bar-note" data-bar-note>
                @if ($flow['stage'] === 'in_progress')
                    موعد التسليم المتوقّع: {{ $flow['due_at']?->format('Y/m/d') }}
                @else
                    مهلة تجهيز العرض {{ $slaDays }} أيام عمل من تاريخ الاجتماع
                @endif
            </p>
        </div>

        <div class="steps">
            <div class="step done">
                <div class="step-rail"><span class="step-dot"><svg class="ic"><use href="#i-check"/></svg></span><span class="step-line"></span></div>
                <div class="step-body">
                    <div class="step-t">استلام الطلب وتسجيله</div>
                    <div class="step-d">تم بنجاح — {{ $fmt($issued) }}</div>
                </div>
            </div>

            @foreach ($stages as $key => $info)
                @php
                    $i = $loop->index;
                    $state = $i < $flow['index'] ? 'done' : ($i === $flow['index'] ? 'active' : '');
                    $when = match ($key) {
                        'awaiting_meeting' => $flow['meeting_at'] ? 'تم التحديد — '.$fmt($flow['meeting_at']) : null,
                        'meeting_scheduled' => $flow['meeting_done_at']
                            ? 'تم الاجتماع — '.$fmt($flow['meeting_done_at'])
                            : ($flow['meeting_at'] ? $fmt($flow['meeting_at']) : null),
                        'quote_due' => $quote ? 'صدر العرض — '.$fmt($quote['issued_at']) : null,
                        'awaiting_approval' => $flow['approved_at'] ? 'اعتُمد — '.$fmt($flow['approved_at']) : null,
                        'awaiting_requirements' => count($requirements) ? 'رُفع '.count($requirements).' ملف' : null,
                        'in_progress' => $flow['due_at'] ? 'موعد التسليم: '.$flow['due_at']->format('Y/m/d') : null,
                        'delivered' => $flow['delivered_at'] ? $fmt($flow['delivered_at']) : null,
                    };
                @endphp
                <div class="step {{ $state }}">
                    <div class="step-rail">
                        <span class="step-dot">
                            <svg class="ic"><use href="#i-{{ $state === 'done' ? 'check' : $info['icon'] }}"/></svg>
                        </span>
                        <span class="step-line"></span>
                    </div>
                    <div class="step-body">
                        <div class="step-t">{{ $info['label'] }}</div>
                        <div class="step-d">{{ $when ?? ($state === 'active' ? $info['client'] : 'لم تبدأ بعد') }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <details class="summary-box">
            <summary><svg class="ic"><use href="#i-list"/></svg> عرض تفاصيل الطلب المُرسل</summary>
            @foreach($rows as [$key, $value])
                <div class="rv-row"><span class="rv-q">{{ $key }}</span><span class="rv-a">{{ $value }}</span></div>
            @endforeach
        </details>

        <div class="actions">
            <a class="btn btn-primary" href="{{ route('quote.document', $inviteSlug) }}" target="_blank" rel="noopener">
                <svg class="ic"><use href="#i-download"/></svg> تحميل مستند الطلب (PDF)
            </a>
            <a class="btn btn-wa" target="_blank" rel="noopener"
               href="https://wa.me/{{ $whatsapp }}?text={{ rawurlencode('مرحباً فريق وريد، بخصوص طلب المتجر رقم '.$sr->reference) }}">
                <svg class="ic"><use href="#i-whatsapp"/></svg> استفسار عبر واتساب
            </a>
            <a class="btn btn-ghost" href="{{ url('/') }}">موقع وريد</a>
        </div>
    </div>
</main>

<footer class="minifoot">© {{ date('Y') }} وريد — منصتك التقنية المتكاملة · wareed.vip</footer>

<script>
(function () {
    'use strict';
    var box = document.querySelector('[data-timer]');
    if (!box) return;
    var deadline = new Date(box.dataset.deadline).getTime();
    var start = new Date(box.dataset.start).getTime();
    var span = Math.max(deadline - start, 1);
    var els = {
        d: box.querySelector('[data-d]'),
        h: box.querySelector('[data-h]'), m: box.querySelector('[data-m]'), s: box.querySelector('[data-s]'),
        bar: document.querySelector('[data-bar]'), note: document.querySelector('[data-bar-note]'),
        title: document.querySelector('[data-timer-title]')
    };
    var pad = function (n) { return String(n).padStart(2, '0'); };

    function tick() {
        var left = deadline - Date.now();
        if (left <= 0) {
            box.classList.add('is-done');
            els.d.textContent = els.h.textContent = els.m.textContent = els.s.textContent = '00';
            els.bar.style.width = '100%';
            els.title.textContent = 'انتهت مهلة التجهيز المقدَّرة';
            els.note.textContent = 'عرض السعر في مراحله الأخيرة — يسعدنا تواصلك معنا للاطمئنان.';
            clearInterval(timer);
            return;
        }
        var totalS = Math.floor(left / 1000);
        els.d.textContent = pad(Math.floor(totalS / 86400));
        els.h.textContent = pad(Math.floor((totalS % 86400) / 3600));
        els.m.textContent = pad(Math.floor((totalS % 3600) / 60));
        els.s.textContent = pad(totalS % 60);
        els.bar.style.width = Math.min(100, Math.max(0, ((span - left) / span) * 100)).toFixed(1) + '%';
    }

    tick();
    var timer = setInterval(tick, 1000);
})();
</script>
</body>
</html>
