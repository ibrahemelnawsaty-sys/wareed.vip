@extends('layouts.app')

@php
    $serviceSchema = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $service->name,
        'description' => $service->summary,
        'provider' => ['@type' => 'Organization', 'name' => setting('site_name', 'وريد')],
        'areaServed' => 'EG',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $heroIcon = \App\Support\Icons::name($service->icon, \App\Support\Icons::forServiceKey($service->key));
    $ctaLabel = $service->cta_label ?: __('اطلب الخدمة الآن');
@endphp
@push('jsonld')
<script type="application/ld+json">{!! $serviceSchema !!}</script>
@endpush

@section('content')
{{-- ===== HERO ===== --}}
<section class="svc-hero">
    <div class="wrap svc-grid">
        <div data-reveal>
            <span class="ic-box"><x-w-icon :name="$heroIcon" /></span>
            <h1>{{ $service->hero_title ?: $service->name }}</h1>
            <p class="lead">{{ $service->hero_subtitle ?: $service->summary }}</p>
            <div class="svc-actions">
                <a href="#request" class="btn btn-gold">{{ $ctaLabel }} <x-w-icon name="arrow-down" /></a>
                <a href="{{ url('/contact') }}" class="btn btn-ghost">{{ __('استشارة مجانية') }}</a>
            </div>
        </div>

        <div data-reveal class="svc-panel">
            @if($service->description)
                <p>{!! nl2br(e($service->description)) !!}</p>
            @else
                <p>{{ $service->summary }}</p>
            @endif
            <ul class="svc-points">
                <li><x-w-icon name="check" />{{ __('استشارة أولى مجانية قبل أي التزام') }}</li>
                <li><x-w-icon name="check" />{{ __('عرض سعر مفصّل بجدول زمني واضح') }}</li>
                <li><x-w-icon name="check" />{{ __('متابعة ودعم بشري طوال المشروع') }}</li>
            </ul>
        </div>
    </div>
</section>

{{-- ===== المميزات ===== --}}
@if(!empty($service->features))
<section class="sec sec-alt">
    <div class="wrap">
        <div class="sec-head" data-reveal>
            <span class="kicker">{{ __('ماذا تتضمّن') }}</span>
            <h2>{{ $service->name }}</h2>
            <p>{{ __('كل ما تحتاجه في خدمة واحدة، ينفّذه فريق واحد بمعيار واحد.') }}</p>
        </div>
        <div class="svc-feats">
            @foreach($service->features as $f)
                <div data-reveal class="feat">
                    <div class="fi"><span class="ic-box ic-box-sm"><x-w-icon :name="\App\Support\Icons::name($f['icon'] ?? null)" /></span></div>
                    <h3>{{ tleaf($f['title'] ?? '') }}</h3>
                    <p>{{ tleaf($f['desc'] ?? '') }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ===== الأسعار ===== --}}
@if(!empty($service->pricing))
<section class="sec">
    <div class="wrap">
        <div class="sec-head" data-reveal>
            <span class="kicker">{{ __('الباقات') }}</span>
            <h2>{{ __('أسعار واضحة بلا مفاجآت') }}</h2>
            <p>{{ __('اختر ما يناسب مرحلتك الآن، ويمكنك الترقية في أي وقت.') }}</p>
        </div>
        <div class="plans">
            @foreach($service->pricing as $plan)
                <div data-reveal class="plan {{ ($plan['featured'] ?? false) ? 'plan-featured' : '' }}">
                    @if($plan['featured'] ?? false)
                        <span class="plan-tag">{{ __('الأكثر طلباً') }}</span>
                    @endif
                    <h3>{{ tleaf($plan['name'] ?? '') }}</h3>
                    <div class="plan-price">
                        <b>{{ tleaf($plan['price'] ?? '') }}</b>
                        <span>{{ tleaf($plan['period'] ?? '') }}</span>
                    </div>
                    <ul>
                        @foreach((array) tleaf($plan['features'] ?? []) as $feat)
                            <li><x-w-icon name="check" />{{ $feat }}</li>
                        @endforeach
                    </ul>
                    <a href="#request" class="btn {{ ($plan['featured'] ?? false) ? 'btn-gold' : 'btn-ghost' }}">{{ __('اختر الباقة') }}</a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ===== نموذج الطلب ===== --}}
<section id="request" class="sec sec-alt">
    <div class="wrap" style="max-width: 780px;">
        <div class="sec-head" data-reveal>
            <span class="kicker">{{ __('ابدأ الآن') }}</span>
            <h2>{{ $ctaLabel }}</h2>
            <p>{{ __('املأ النموذج وسيتواصل معك فريقنا خلال ساعات.') }}</p>
        </div>

        <div class="form-card" data-reveal>
            @if(session('success'))
                <div class="alert alert-ok" style="margin-bottom: 24px;">
                    <x-w-icon name="check" /><span>{{ session('success') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-err" style="margin-bottom: 24px;">
                    <x-w-icon name="spark" />
                    <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('services.submit', $service) }}" class="f-row" style="grid-template-columns: 1fr 1fr;">
                @csrf
                <div>
                    <label class="f-label" for="f-name">{{ __('الاسم') }} *</label>
                    <input id="f-name" name="name" value="{{ old('name') }}" required class="f-input" placeholder="{{ __('اسمك الكامل') }}">
                </div>
                <div>
                    <label class="f-label" for="f-phone">{{ __('رقم الموبايل / واتساب') }} *</label>
                    <input id="f-phone" name="phone" value="{{ old('phone') }}" required class="f-input" placeholder="01xxxxxxxxx" dir="ltr" inputmode="tel">
                </div>
                <div>
                    <label class="f-label" for="f-email">{{ __('البريد الإلكتروني') }}</label>
                    <input id="f-email" name="email" type="email" value="{{ old('email') }}" class="f-input" placeholder="name@example.com" dir="ltr" inputmode="email">
                </div>
                <div>
                    <label class="f-label" for="f-company">{{ __('اسم الشركة / النشاط') }}</label>
                    <input id="f-company" name="company" value="{{ old('company') }}" class="f-input" placeholder="{{ __('اختياري') }}">
                </div>

                {{-- حقول خاصة بالخدمة، مصدرها إعدادات الخدمة نفسها --}}
                @foreach((array) $service->form_fields as $field)
                    @php $fname = 'extra_'.($field['name'] ?? ''); $ftype = $field['type'] ?? 'text'; @endphp
                    <div style="{{ ($field['full'] ?? false) || $ftype === 'textarea' ? 'grid-column: 1 / -1;' : '' }}">
                        <label class="f-label" for="{{ $fname }}">{{ $field['label'] ?? $field['name'] }}</label>
                        @if($ftype === 'select' && !empty($field['options']))
                            <div class="f-select-wrap">
                                <select id="{{ $fname }}" name="{{ $fname }}" class="f-select">
                                    <option value="">{{ __('اختر…') }}</option>
                                    @foreach($field['options'] as $opt)
                                        <option value="{{ $opt }}" @selected(old($fname) === $opt)>{{ $opt }}</option>
                                    @endforeach
                                </select>
                                <x-w-icon name="chevron-down" />
                            </div>
                        @elseif($ftype === 'textarea')
                            <textarea id="{{ $fname }}" name="{{ $fname }}" rows="3" class="f-area">{{ old($fname) }}</textarea>
                        @else
                            <input id="{{ $fname }}" name="{{ $fname }}" value="{{ old($fname) }}" class="f-input">
                        @endif
                    </div>
                @endforeach

                <div style="grid-column: 1 / -1;">
                    <label class="f-label" for="f-message">{{ __('تفاصيل إضافية') }}</label>
                    <textarea id="f-message" name="message" rows="4" class="f-area" placeholder="{{ __('أخبرنا عن احتياجك...') }}">{{ old('message') }}</textarea>
                </div>
                <div style="grid-column: 1 / -1;">
                    <button type="submit" class="btn btn-gold" style="width: 100%;">
                        {{ __('إرسال الطلب') }} <x-w-icon name="arrow-left" />
                    </button>
                    <p class="f-hint" style="text-align: center;">{{ __('بياناتك سرّية بالكامل ولا تُشارك مع أي طرف.') }}</p>
                </div>
            </form>
        </div>
    </div>
</section>

{{-- ===== الأسئلة الشائعة ===== --}}
@if(!empty($service->faqs))
<section class="sec">
    <div class="wrap">
        <div class="sec-head" data-reveal>
            <span class="kicker">{{ __('أسئلة شائعة') }}</span>
            <h2>{{ __('ما يسأل عنه عملاؤنا عادةً') }}</h2>
        </div>
        <div class="faq" data-reveal>
            @foreach($service->faqs as $faq)
                <details>
                    <summary>{{ tleaf($faq['q'] ?? '') }}<x-w-icon name="chevron-down" /></summary>
                    <div class="fa">{{ tleaf($faq['a'] ?? '') }}</div>
                </details>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
