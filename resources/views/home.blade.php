@extends('layouts.app')

@section('content')
@php $siteName = setting('site_name', 'وريد'); @endphp

{{-- ===== HERO ===== --}}
<section class="hero">
    <div class="wrap hero-grid">
        <div data-reveal>
            <span class="eyebrow"><span class="pulse-dot"></span> {{ __('منصة تقنية متكاملة') }}</span>
            <h1>{{ setting('hero_title', 'منصتك التقنية') }} <span class="accent">{{ setting('hero_title_accent', 'المتكاملة') }}</span></h1>
            <p class="lead">{{ setting('hero_subtitle', 'متجرك الإلكتروني من ضغطة زر، حلول تقنية للشركات والجهات، وتدريب احترافي في البرمجة والذكاء الاصطناعي — كل ذلك في منصة واحدة.') }}</p>
            <div class="hero-cta">
                <a href="{{ url('/services/ecommerce') }}" class="btn btn-gold">
                    {{ __('أنشئ متجرك مجاناً') }}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7-7 7 7 7"/></svg>
                </a>
                <a href="{{ url('/contact') }}" class="btn btn-ghost">{{ __('احجز استشارة مجانية') }}</a>
            </div>
            <div class="flex items-center gap-2 text-sm text-cloud-400">
                <span>{{ __('اكتشف خدماتنا') }}</span>
                <svg class="h-5 w-5 animate-bounce" fill="none" stroke="var(--teal)" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M6 13l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
        </div>

        {{-- شبكة تقنية متفرّعة (Network) --}}
        <div data-reveal class="hero-visual">
            <div class="net-stage">
                <svg class="net-svg" viewBox="0 0 460 400" fill="none" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                    <defs>
                        <linearGradient id="gNet" x1="1" y1="0" x2="0" y2="1">
                            <stop offset="0" stop-color="#3B82F6"/><stop offset=".5" stop-color="#8B5CF6"/><stop offset="1" stop-color="#2DD4BF"/>
                        </linearGradient>
                        <radialGradient id="gNode2" cx="50%" cy="50%" r="50%">
                            <stop offset="0" stop-color="#93b4fd"/><stop offset="1" stop-color="#3B82F6"/>
                        </radialGradient>
                        <filter id="glow2" x="-50%" y="-50%" width="200%" height="200%">
                            <feGaussianBlur stdDeviation="4" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge>
                        </filter>
                    </defs>
                    {{-- شبكة سداسية خافتة --}}
                    <g stroke="#6366f1" stroke-opacity=".12" stroke-width="1" fill="none">
                        <path d="M250 120 l52 30 0 60 -52 30 -52 -30 0 -60 z"/>
                        <circle cx="250" cy="200" r="120"/>
                    </g>
                    {{-- الروابط --}}
                    <path d="M430 70 C 360 110, 320 160, 250 200" stroke="url(#gNet)" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M250 200 C 190 175, 150 135, 92 120" stroke="url(#gNet)" stroke-width="1.6"/>
                    <path d="M250 200 C 190 213, 150 220, 80 222" stroke="url(#gNet)" stroke-width="1.6"/>
                    <path d="M250 200 C 190 250, 150 300, 92 320" stroke="url(#gNet)" stroke-width="1.6"/>
                    {{-- تدفّق البيانات --}}
                    <path class="net-flow" d="M430 70 C 360 110, 320 160, 250 200 C 190 175, 150 135, 92 120" stroke="#cfe0ff" stroke-width="2.4" stroke-linecap="round"/>
                    <path class="net-flow f2" d="M250 200 C 190 213, 150 220, 80 222" stroke="#cfe0ff" stroke-width="2" stroke-linecap="round"/>
                    <path class="net-flow f3" d="M250 200 C 190 250, 150 300, 92 320" stroke="#cfe0ff" stroke-width="2" stroke-linecap="round"/>
                    {{-- العقدة المصدر --}}
                    <circle cx="430" cy="70" r="6" fill="url(#gNode2)" filter="url(#glow2)"/>
                    <circle class="origin-pulse" cx="430" cy="70" r="6" fill="none" stroke="#2DD4BF" stroke-width="2"/>
                    {{-- العقدة المركزية --}}
                    <circle cx="250" cy="200" r="13" fill="url(#gNode2)" filter="url(#glow2)"/>
                    <circle cx="250" cy="200" r="22" fill="none" stroke="#8B5CF6" stroke-opacity=".5" stroke-width="1.5"/>
                    {{-- عقد الخدمات --}}
                    <circle cx="92" cy="120" r="10" fill="url(#gNode2)" filter="url(#glow2)"/>
                    <circle cx="80" cy="222" r="10" fill="url(#gNode2)" filter="url(#glow2)"/>
                    <circle cx="92" cy="320" r="10" fill="url(#gNode2)" filter="url(#glow2)"/>
                    <g font-family="'El Messiri','IBM Plex Sans Arabic',sans-serif" font-weight="600" font-size="16" fill="#eaf0fb" text-anchor="end">
                        <text x="74" y="116">{{ __('متاجر') }}</text>
                        <text x="62" y="218">{{ __('حلول') }}</text>
                        <text x="74" y="316">{{ __('تدريب') }}</text>
                    </g>
                </svg>

                <div class="stats-card">
                    <div class="text-center"><div class="sv"><span data-counter="3">3</span></div><div class="sl">{{ __('خدمات متكاملة') }}</div></div>
                    <div class="sep"></div>
                    <div class="text-center"><div class="sv"><span data-counter="99" data-suffix="%">99%</span></div><div class="sl">{{ __('جاهزية التشغيل') }}</div></div>
                    <div class="sep"></div>
                    <div class="text-center"><div class="sv">24/7</div><div class="sl">{{ __('دعم متواصل') }}</div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="divider wrap" aria-hidden="true">
    <svg viewBox="0 0 420 30" fill="none" class="w-80">
        <line x1="0" y1="15" x2="170" y2="15" stroke="#6366f1" stroke-opacity=".35"/>
        <path d="M210 4 l8 11 -8 11 -8-11z" stroke="#8B5CF6" stroke-opacity=".7" fill="none"/>
        <circle cx="210" cy="15" r="3" fill="#2DD4BF"/>
        <line x1="250" y1="15" x2="420" y2="15" stroke="#6366f1" stroke-opacity=".35"/>
    </svg>
</div>

{{-- ===== الخدمات ===== --}}
<section class="sec" id="services">
    <div class="wrap">
        <div class="sec-head" data-reveal>
            <span class="kicker">{{ __('خدماتنا') }}</span>
            <h2>{{ __('منظومة تقنية متكاملة لنموّ أعمالك') }}</h2>
            <p>{{ __('من الفكرة إلى الإطلاق والنمو — وريد شريكك التقني المتكامل.') }}</p>
        </div>

        <div class="services">
            @foreach($services as $service)
                <article class="card" data-reveal>
                    <span class="num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <div class="icon">{{ $service->icon ?? '◆' }}</div>
                    <h3>{{ $service->name }}</h3>
                    <p>{{ $service->summary }}</p>
                    <ul>
                        @foreach(array_slice((array) $service->features, 0, 3) as $f)
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                {{ tleaf($f['title']) }}
                            </li>
                        @endforeach
                    </ul>
                    <a class="more" href="{{ url('/services/'.$service->slug) }}">
                        {{ __('اعرف المزيد') }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== لماذا وريد ===== --}}
<section class="sec" id="why">
    <div class="wrap">
        <div class="sec-head" data-reveal>
            <span class="kicker">{{ __('لماذا وريد') }}</span>
            <h2>{{ __('فخامة عالمية بمعايير تقنية') }}</h2>
            <p>{{ __('نجمع بين الأناقة والأداء والأمان، لتكون تجربتك الرقمية كنزاً يليق بطموحك.') }}</p>
        </div>
        <div class="why">
            @php $feats = [
                ['⚡','سرعة فائقة','أداء خفيف محسَّن يرفع تجربتك وترتيبك في محركات البحث.'],
                ['🛡️','أمان وعزل','عزل بنيوي صارم لبيانات كل متجر وكل عميل، بمعايير موثوقة.'],
                ['🤝','دعم بشري حقيقي','فريق متخصص يفهم احتياجك ويرافقك خطوة بخطوة، لا روبوتات فقط.'],
                ['📈','نموّ مستدام','حلول قابلة للتوسّع تكبر مع أعمالك من أول متجر إلى منظومة كاملة.'],
            ]; @endphp
            @foreach($feats as $f)
                <div class="feat" data-reveal>
                    <div class="fi">{{ $f[0] }}</div>
                    <h3>{{ __($f[1]) }}</h3>
                    <p>{{ __($f[2]) }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== كيف نعمل ===== --}}
<section class="sec" id="how">
    <div class="wrap">
        <div class="sec-head" data-reveal>
            <span class="kicker">{{ __('كيف نعمل') }}</span>
            <h2>{{ __('أربع خطوات حتى الانطلاق') }}</h2>
            <p>{{ __('رحلة واضحة وسلسة من أول تواصل إلى إطلاقٍ ناجح ونموٍّ مستمر.') }}</p>
        </div>
        <div class="steps">
            @php $steps = [
                ['1','نستمع لرؤيتك','جلسة تواصل نفهم فيها هدفك وجمهورك ومتطلباتك بدقّة.'],
                ['2','نُصمّم الحل','نخطّط الخدمة المناسبة ونرسم خارطة التنفيذ.'],
                ['3','نُنفّذ ونُطلق','نبني بسرعة وجودة عالية، ونطلق حلّك جاهزاً للعمل.'],
                ['4','ندعم وننمّي','متابعة وصيانة وتطوير مستمر لنضمن نموّك.'],
            ]; @endphp
            @foreach($steps as $s)
                <div class="step" data-reveal>
                    <div class="sn">{{ $s[0] }}</div>
                    <h3>{{ __($s[1]) }}</h3>
                    <p>{{ __($s[2]) }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== CTA ===== --}}
<section class="sec" id="cta">
    <div class="wrap">
        <div class="cta-band" data-reveal>
            <span class="kicker" style="justify-content:center">{{ __('ابدأ رحلتك') }}</span>
            <h2>{{ __('أطلق حضورك الرقمي اليوم') }}</h2>
            <p>{{ __('سواء أردت متجراً إلكترونياً، أو حلاً تقنياً متكاملاً، أو مساراً تدريبياً احترافياً — وريد جاهزة لتحويل فكرتك إلى واقعٍ يتنفّس النجاح.') }}</p>
            <div class="cta-actions">
                <a href="{{ url('/services/ecommerce') }}" class="btn btn-gold">{{ __('ابدأ الآن مجاناً') }}</a>
                <a href="{{ url('/contact') }}" class="btn btn-ghost">{{ __('تحدث مع خبير') }}</a>
            </div>
        </div>
    </div>
</section>
@endsection
