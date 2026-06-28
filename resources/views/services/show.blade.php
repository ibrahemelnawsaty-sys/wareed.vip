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
@endphp
@push('jsonld')
<script type="application/ld+json">{!! $serviceSchema !!}</script>
@endpush

@section('content')
{{-- HERO --}}
<section class="bg-aurora relative overflow-hidden pt-36 pb-20">
    <div class="dot-grid absolute inset-0 opacity-40"></div>
    <div class="relative mx-auto max-w-7xl px-5">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div data-reveal>
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl text-4xl"
                     style="background: {{ $service->color }}1a; color: {{ $service->color }}">{{ $service->icon ?? '◆' }}</div>
                <h1 class="mt-6 text-4xl font-black leading-tight sm:text-5xl">{{ $service->hero_title ?: $service->name }}</h1>
                <p class="mt-5 max-w-xl text-lg leading-8 text-cloud-300">{{ $service->hero_subtitle ?: $service->summary }}</p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="#request" class="btn btn-gold text-base">{{ $service->cta_label ?: __('اطلب الخدمة الآن') }}</a>
                    <a href="{{ url('/contact') }}" class="btn btn-ghost text-base">{{ __('استشارة مجانية') }}</a>
                </div>
            </div>
            <div data-reveal class="glass rounded-[1.75rem] p-8">
                @if($service->description)
                    <div class="prose-invert leading-8 text-cloud-200">{!! nl2br(e($service->description)) !!}</div>
                @else
                    <p class="leading-8 text-cloud-300">{{ $service->summary }}</p>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- المميزات --}}
@if(!empty($service->features))
<section class="py-20">
    <div class="mx-auto max-w-7xl px-5">
        <h2 class="text-center text-3xl font-black sm:text-4xl" data-reveal>{{ __('ماذا تتضمّن') }} <span class="text-gradient-gold">{{ $service->name }}</span>{{ __('؟') }}</h2>
        <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($service->features as $f)
                <div data-reveal class="glass card-hover rounded-3xl p-7">
                    <div class="text-3xl">{{ $f['icon'] ?? '✦' }}</div>
                    <h3 class="mt-4 text-lg font-bold text-cloud-50">{{ tleaf($f['title'] ?? '') }}</h3>
                    <p class="mt-2 text-sm leading-7 text-cloud-400">{{ tleaf($f['desc'] ?? '') }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- الأسعار --}}
@if(!empty($service->pricing))
<section class="py-20">
    <div class="mx-auto max-w-7xl px-5">
        <h2 class="text-center text-3xl font-black sm:text-4xl" data-reveal>{{ __('باقات') }} <span class="text-gradient-gold">{{ __('بأسعار واضحة') }}</span></h2>
        <div class="mt-12 grid gap-6 md:grid-cols-3">
            @foreach($service->pricing as $plan)
                <div data-reveal class="card-hover relative rounded-3xl p-8 {{ ($plan['featured'] ?? false) ? 'glass-gold' : 'glass' }}">
                    @if($plan['featured'] ?? false)
                        <span class="absolute -top-3 right-6 rounded-full bg-gold-500 px-3 py-1 text-xs font-bold text-ink-950">{{ __('الأكثر طلباً') }}</span>
                    @endif
                    <h3 class="text-xl font-bold text-cloud-50">{{ tleaf($plan['name'] ?? '') }}</h3>
                    <div class="mt-4 flex items-end gap-1">
                        <span class="text-4xl font-black text-gold-300">{{ tleaf($plan['price'] ?? '') }}</span>
                        <span class="mb-1 text-sm text-cloud-400">{{ tleaf($plan['period'] ?? '') }}</span>
                    </div>
                    <ul class="mt-6 space-y-3 text-sm text-cloud-300">
                        @foreach((array) tleaf($plan['features'] ?? []) as $feat)
                            <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span>{{ $feat }}</li>
                        @endforeach
                    </ul>
                    <a href="#request" class="btn {{ ($plan['featured'] ?? false) ? 'btn-gold' : 'btn-ghost' }} mt-8 w-full justify-center">{{ __('اختر الباقة') }}</a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- نموذج الطلب --}}
<section id="request" class="py-20">
    <div class="mx-auto max-w-3xl px-5">
        <div class="glass rounded-[2rem] p-8 sm:p-10" data-reveal>
            <h2 class="text-center text-3xl font-black">{{ __('اطلب') }} <span class="text-gradient-gold">{{ $service->name }}</span></h2>
            <p class="mt-3 text-center text-cloud-400">{{ __('املأ النموذج وسيتواصل معك فريقنا خلال ساعات.') }}</p>

            @if(session('success'))
                <div class="mt-6 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-5 py-4 text-center text-emerald-300">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="mt-6 rounded-xl border border-vein-500/40 bg-vein-500/10 px-5 py-4 text-sm text-red-300">
                    <ul class="list-inside list-disc">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('services.submit', $service) }}" class="mt-8 grid gap-5 sm:grid-cols-2">
                @csrf
                <div>
                    <label class="mb-2 block text-sm font-semibold text-cloud-200">{{ __('الاسم') }} *</label>
                    <input name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-white/10 bg-ink-850 px-4 py-3 text-cloud-100 outline-none transition focus:border-gold-500" placeholder="{{ __('اسمك الكامل') }}">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-cloud-200">{{ __('رقم الموبايل / واتساب') }} *</label>
                    <input name="phone" value="{{ old('phone') }}" required class="w-full rounded-xl border border-white/10 bg-ink-850 px-4 py-3 text-cloud-100 outline-none transition focus:border-gold-500" placeholder="01xxxxxxxxx">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-cloud-200">{{ __('البريد الإلكتروني') }}</label>
                    <input name="email" type="email" value="{{ old('email') }}" class="w-full rounded-xl border border-white/10 bg-ink-850 px-4 py-3 text-cloud-100 outline-none transition focus:border-gold-500" placeholder="email@example.com" dir="ltr">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-cloud-200">{{ __('اسم الشركة / النشاط') }}</label>
                    <input name="company" value="{{ old('company') }}" class="w-full rounded-xl border border-white/10 bg-ink-850 px-4 py-3 text-cloud-100 outline-none transition focus:border-gold-500" placeholder="{{ __('اختياري') }}">
                </div>

                {{-- حقول خاصة بالخدمة --}}
                @foreach((array) $service->form_fields as $field)
                    <div class="{{ ($field['full'] ?? false) ? 'sm:col-span-2' : '' }}">
                        <label class="mb-2 block text-sm font-semibold text-cloud-200">{{ $field['label'] ?? $field['name'] }}</label>
                        @if(($field['type'] ?? 'text') === 'select' && !empty($field['options']))
                            <select name="extra_{{ $field['name'] }}" class="w-full rounded-xl border border-white/10 bg-ink-850 px-4 py-3 text-cloud-100 outline-none transition focus:border-gold-500">
                                <option value="">اختر...</option>
                                @foreach($field['options'] as $opt)<option value="{{ $opt }}">{{ $opt }}</option>@endforeach
                            </select>
                        @elseif(($field['type'] ?? 'text') === 'textarea')
                            <textarea name="extra_{{ $field['name'] }}" rows="3" class="w-full rounded-xl border border-white/10 bg-ink-850 px-4 py-3 text-cloud-100 outline-none transition focus:border-gold-500"></textarea>
                        @else
                            <input name="extra_{{ $field['name'] }}" class="w-full rounded-xl border border-white/10 bg-ink-850 px-4 py-3 text-cloud-100 outline-none transition focus:border-gold-500">
                        @endif
                    </div>
                @endforeach

                <div class="sm:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-cloud-200">{{ __('تفاصيل إضافية') }}</label>
                    <textarea name="message" rows="4" class="w-full rounded-xl border border-white/10 bg-ink-850 px-4 py-3 text-cloud-100 outline-none transition focus:border-gold-500" placeholder="{{ __('أخبرنا عن احتياجك...') }}">{{ old('message') }}</textarea>
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="btn btn-gold w-full justify-center text-base">{{ __('إرسال الطلب') }}</button>
                </div>
            </form>
        </div>
    </div>
</section>

{{-- الأسئلة الشائعة --}}
@if(!empty($service->faqs))
<section class="py-20">
    <div class="mx-auto max-w-3xl px-5">
        <h2 class="text-center text-3xl font-black sm:text-4xl" data-reveal>{{ __('أسئلة شائعة') }}</h2>
        <div class="mt-10 space-y-4">
            @foreach($service->faqs as $faq)
                <details data-reveal class="glass group rounded-2xl p-6">
                    <summary class="cursor-pointer list-none text-lg font-bold text-cloud-100">{{ tleaf($faq['q'] ?? '') }}</summary>
                    <p class="mt-3 leading-7 text-cloud-400">{{ tleaf($faq['a'] ?? '') }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
