@extends('layouts.app')

@section('content')
@php
    $siteName = setting('site_name', 'وريد');
    // الزر الأساسي ينقل إلى قسم الخدمات ليختار الزائر خدمته ثم يفتح نموذجها
    $primaryCta = __('ابدأ مشروعك');
@endphp

{{-- ===== HERO ===== --}}
<section class="hero">
    <div class="wrap hero-grid">
        <div data-reveal>
            <span class="eyebrow"><span class="pulse-dot"></span> {{ __('منصة تقنية متكاملة') }}</span>
            <h1>{{ setting('hero_title', 'منصتك التقنية') }} <span class="accent">{{ setting('hero_title_accent', 'المتكاملة') }}</span></h1>
            <p class="lead">{{ setting('hero_subtitle', 'متجرك الإلكتروني من ضغطة زر، حلول رقمية للشركات والجهات، وبرامج تدريبية تقنية باحترافية — كل ذلك من فريق واحد.') }}</p>
            <div class="hero-cta">
                <a href="#services" class="btn btn-gold">
                    {{ $primaryCta }}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M6 13l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
                <a href="{{ url('/contact') }}" class="btn btn-ghost">{{ __('تحدث مع خبير') }}</a>
            </div>
            <div class="hero-trust">
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    {{ __('استشارة أولى مجانية') }}
                </span>
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    {{ __('عرض سعر خلال 3 أيام عمل') }}
                </span>
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    {{ __('دعم بشري طوال المشروع') }}
                </span>
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
                            <stop offset="0" stop-color="#60A5FA"/><stop offset="1" stop-color="#2563EB"/>
                        </radialGradient>
                    </defs>
                    {{-- شبكة سداسية خافتة --}}
                    <g stroke="#2563eb" stroke-opacity=".14" stroke-width="1" fill="none">
                        <path d="M250 120 l52 30 0 60 -52 30 -52 -30 0 -60 z"/>
                        <circle cx="250" cy="200" r="120"/>
                    </g>
                    {{-- الروابط --}}
                    <path d="M430 70 C 360 110, 320 160, 250 200" stroke="url(#gNet)" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M250 200 C 190 175, 150 135, 92 120" stroke="url(#gNet)" stroke-width="1.6"/>
                    <path d="M250 200 C 190 213, 150 220, 80 222" stroke="url(#gNet)" stroke-width="1.6"/>
                    <path d="M250 200 C 190 250, 150 300, 92 320" stroke="url(#gNet)" stroke-width="1.6"/>
                    {{-- تدفّق البيانات --}}
                    <path class="net-flow" d="M430 70 C 360 110, 320 160, 250 200 C 190 175, 150 135, 92 120" stroke="#2563eb" stroke-width="2.6" stroke-linecap="round"/>
                    <path class="net-flow f2" d="M250 200 C 190 213, 150 220, 80 222" stroke="#0d9488" stroke-width="2.2" stroke-linecap="round"/>
                    <path class="net-flow f3" d="M250 200 C 190 250, 150 300, 92 320" stroke="#7c3aed" stroke-width="2.2" stroke-linecap="round"/>
                    {{-- العقدة المصدر --}}
                    <circle cx="430" cy="70" r="6" fill="url(#gNode2)"/>
                    <circle class="origin-pulse" cx="430" cy="70" r="6" fill="none" stroke="#0d9488" stroke-width="2"/>
                    {{-- العقدة المركزية --}}
                    <circle cx="250" cy="200" r="13" fill="url(#gNode2)"/>
                    <circle cx="250" cy="200" r="22" fill="none" stroke="#8B5CF6" stroke-opacity=".45" stroke-width="1.5"/>
                    {{-- عقد الخدمات --}}
                    <circle cx="92" cy="120" r="9" fill="url(#gNode2)"/>
                    <circle cx="80" cy="222" r="9" fill="url(#gNode2)"/>
                    <circle cx="92" cy="320" r="9" fill="url(#gNode2)"/>
                    <g font-family="'El Messiri','IBM Plex Sans Arabic',sans-serif" font-weight="700" font-size="16" fill="#0d1830" text-anchor="end">
                        <text x="74" y="116">{{ __('متاجر') }}</text>
                        <text x="62" y="218">{{ __('حلول') }}</text>
                        <text x="74" y="316">{{ __('تدريب') }}</text>
                    </g>
                </svg>
            </div>
        </div>
    </div>
</section>

{{-- ===== الخدمات — وجهة الزر الأساسي ===== --}}
<section class="sec sec-alt" id="services">
    <div class="wrap">
        <div class="sec-head" data-reveal>
            <span class="kicker">{{ __('خدماتنا') }}</span>
            <h2>{{ __('اختر خدمتك ولنبدأ') }}</h2>
            <p>{{ __('ثلاث خدمات نتقنها، لكل واحدة فريقها ومسارها الواضح من أول جلسة حتى التسليم.') }}</p>
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
                    {{-- دعوة خاصة بكل خدمة، نصّها من إعدادات الخدمة نفسها --}}
                    <a class="btn btn-gold card-cta" href="{{ url('/services/'.$service->slug) }}">
                        {{ $service->cta_label ?: __('اعرف المزيد') }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== أرقام تختصر الوعد ===== --}}
<section class="sec">
    <div class="wrap">
        <div class="proof" data-reveal>
            <div><div class="pv">3</div><div class="pl">{{ __('خدمات رئيسية') }}</div></div>
            <div><div class="pv">3</div><div class="pl">{{ __('أيام عمل لعرض السعر') }}</div></div>
            <div><div class="pv">100%</div><div class="pl">{{ __('ملكيتك لمتجرك وبياناتك') }}</div></div>
            <div><div class="pv">24/7</div><div class="pl">{{ __('متابعة ودعم') }}</div></div>
        </div>
    </div>
</section>

{{-- ===== كيف نعمل ===== --}}
<section class="sec sec-alt" id="how">
    <div class="wrap">
        <div class="sec-head" data-reveal>
            <span class="kicker">{{ __('كيف نعمل') }}</span>
            <h2>{{ __('أربع خطوات حتى الانطلاق') }}</h2>
            <p>{{ __('رحلة واضحة وسلسة من أول تواصل إلى إطلاقٍ ناجح ونموٍّ مستمر.') }}</p>
        </div>
        <div class="steps">
            @php $steps = [
                ['1','نستمع لرؤيتك','جلسة تواصل نفهم فيها هدفك وجمهورك ومتطلباتك بدقّة.'],
                ['2','نُصمّم الحل','نخطّط الخدمة المناسبة ونرسم خارطة التنفيذ وجدولها الزمني.'],
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

{{-- ===== لماذا وريد ===== --}}
<section class="sec" id="why">
    <div class="wrap">
        <div class="sec-head" data-reveal>
            <span class="kicker">{{ __('لماذا وريد') }}</span>
            <h2>{{ __('معايير تقنية بلا مساومة') }}</h2>
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

{{-- ===== أسئلة شائعة ===== --}}
<section class="sec sec-alt" id="faq">
    <div class="wrap">
        <div class="sec-head" data-reveal>
            <span class="kicker">{{ __('أسئلة شائعة') }}</span>
            <h2>{{ __('ما يسأل عنه عملاؤنا عادةً') }}</h2>
        </div>
        <div class="faq" data-reveal>
            @php $faqs = [
                ['كم يستغرق تجهيز المتجر الإلكتروني؟','يعتمد على عدد المنتجات ونطاق العمل. بعد جلسة التعريف نرسل عرض سعر خلال ثلاثة أيام عمل يتضمّن جدولاً زمنياً بمراحل واضحة وتواريخ محدّدة.'],
                ['هل أملك المتجر وبياناته بالكامل؟','نعم. المتجر وبياناته ملكك بالكامل، ونسلّمك صلاحيات الإدارة كاملة مع تدريب على لوحة التحكم.'],
                ['هل تقدّمون الدعم بعد التسليم؟','نعم، الدعم الفني متواصل بعد التسليم، مع خطط صيانة وتطوير اختيارية حسب حاجتك.'],
                ['كيف تبدأ رحلة العمل معكم؟','اختر خدمتك من الأعلى واملأ النموذج، ويتواصل معك الفريق لتحديد جلسة تعريفية قصيرة نفهم فيها مشروعك.'],
            ]; @endphp
            @foreach($faqs as $q)
                <details>
                    <summary>{{ __($q[0]) }}</summary>
                    <div class="fa">{{ __($q[1]) }}</div>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== CTA ===== --}}
<section class="sec" id="cta">
    <div class="wrap">
        <div class="cta-band" data-reveal>
            <span class="kicker">{{ __('ابدأ رحلتك') }}</span>
            <h2>{{ __('أطلق حضورك الرقمي اليوم') }}</h2>
            <p>{{ __('سواء أردت متجراً إلكترونياً، أو حلاً رقمياً متكاملاً، أو برنامجاً تدريبياً تقنياً — وريد جاهزة لتحويل فكرتك إلى واقعٍ يعمل.') }}</p>
            <div class="cta-actions">
                <a href="#services" class="btn btn-gold">{{ $primaryCta }}</a>
                <a href="{{ url('/contact') }}" class="btn btn-ghost">{{ __('تحدث مع خبير') }}</a>
            </div>
        </div>
    </div>
</section>
@endsection
