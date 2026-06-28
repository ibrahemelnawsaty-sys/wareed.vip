@php
    $navServices = \App\Models\Service::query()->where('is_active', true)->orderBy('sort_order')->get(['name', 'slug', 'icon']);
    $siteName = setting('site_name', 'وريد');
    $loc = $currentLocale ?? app()->getLocale();
    $otherLoc = $loc === 'ar' ? 'en' : 'ar';
    $switchUrl = request()->fullUrlWithQuery(['hl' => $otherLoc]);
@endphp
<style>
    [data-header]{transition:background-color .3s,backdrop-filter .3s,border-color .3s;}
    [data-header].is-scrolled{background:rgba(10,14,26,.85);backdrop-filter:blur(14px);border-color:rgba(255,255,255,.07);}
</style>
<header data-header class="fixed inset-x-0 top-0 z-40 border-b border-transparent">
    <nav class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-4">
        {{-- الشعار --}}
        <a href="{{ url('/') }}" class="brand" aria-label="{{ $siteName }}">
            <svg class="mark" viewBox="0 0 40 40" fill="none" aria-hidden="true">
                <defs><linearGradient id="wmH" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0" stop-color="#3B82F6"/><stop offset=".5" stop-color="#8B5CF6"/><stop offset="1" stop-color="#2DD4BF"/>
                </linearGradient></defs>
                <rect x="2.5" y="2.5" width="35" height="35" rx="11" stroke="url(#wmH)" stroke-width="2" opacity=".5"/>
                <path d="M11 27 L18 14 L24 23 L29 13" stroke="url(#wmH)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="11" cy="27" r="2.5" fill="#2DD4BF"/>
                <circle cx="18" cy="14" r="2.5" fill="#3B82F6"/>
                <circle cx="24" cy="23" r="2.5" fill="#8B5CF6"/>
                <circle cx="29" cy="13" r="2.5" fill="#60A5FA"/>
            </svg>
            <span class="text-gradient-gold">{{ $siteName }}</span>
        </a>

        {{-- روابط سطح المكتب --}}
        <div class="hidden items-center gap-7 lg:flex">
            <a href="{{ url('/') }}" class="text-sm font-semibold text-cloud-200 transition hover:text-gold-300">{{ __('الرئيسية') }}</a>
            <div class="group relative">
                <button class="flex items-center gap-1 text-sm font-semibold text-cloud-200 transition hover:text-gold-300">
                    {{ __('خدماتنا') }}
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div class="invisible absolute {{ ($isRtl ?? true) ? 'right-0' : 'left-0' }} top-full w-64 translate-y-2 pt-3 opacity-0 transition-all group-hover:visible group-hover:translate-y-0 group-hover:opacity-100">
                    <div class="glass rounded-2xl p-2">
                        @foreach($navServices as $s)
                            <a href="{{ url('/services/'.$s->slug) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-cloud-200 transition hover:bg-white/5 hover:text-gold-300">
                                <span class="text-lg">{{ $s->icon ?? '◆' }}</span>{{ $s->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            <a href="{{ url('/stores') }}" class="text-sm font-semibold text-cloud-200 transition hover:text-gold-300">{{ __('المتاجر') }}</a>
            <a href="{{ url('/about') }}" class="text-sm font-semibold text-cloud-200 transition hover:text-gold-300">{{ __('من نحن') }}</a>
            <a href="{{ url('/contact') }}" class="text-sm font-semibold text-cloud-200 transition hover:text-gold-300">{{ __('تواصل معنا') }}</a>
        </div>

        <div class="flex items-center gap-3">
            {{-- مبدّل اللغة --}}
            <a href="{{ $switchUrl }}" class="flex items-center gap-1.5 rounded-lg border border-white/15 px-3 py-1.5 text-xs font-bold text-cloud-200 transition hover:border-gold-500 hover:text-gold-300" aria-label="{{ __('اللغة') }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/></svg>
                {{ $otherLoc === 'en' ? 'EN' : 'ع' }}
            </a>
            <a href="{{ url('/services/ecommerce') }}" class="btn btn-gold hidden text-sm sm:inline-flex">{{ __('أنشئ متجرك') }}</a>
            {{-- زر قائمة الموبايل --}}
            <button data-nav-toggle class="lg:hidden text-cloud-100" aria-label="{{ __('القائمة') }}">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
    </nav>

    {{-- قائمة الموبايل --}}
    <div data-nav-menu class="hidden border-t border-white/5 bg-ink-850/95 px-5 py-4 backdrop-blur lg:hidden">
        <div class="flex flex-col gap-1">
            <a href="{{ url('/') }}" class="rounded-lg px-3 py-2.5 text-cloud-200 hover:bg-white/5">{{ __('الرئيسية') }}</a>
            @foreach($navServices as $s)
                <a href="{{ url('/services/'.$s->slug) }}" class="rounded-lg px-3 py-2.5 text-cloud-200 hover:bg-white/5">{{ $s->icon ?? '◆' }} {{ $s->name }}</a>
            @endforeach
            <a href="{{ url('/stores') }}" class="rounded-lg px-3 py-2.5 text-cloud-200 hover:bg-white/5">{{ __('المتاجر') }}</a>
            <a href="{{ url('/about') }}" class="rounded-lg px-3 py-2.5 text-cloud-200 hover:bg-white/5">{{ __('من نحن') }}</a>
            <a href="{{ url('/contact') }}" class="rounded-lg px-3 py-2.5 text-cloud-200 hover:bg-white/5">{{ __('تواصل معنا') }}</a>
            <a href="{{ url('/services/ecommerce') }}" class="btn btn-gold mt-2 justify-center">{{ __('أنشئ متجرك الآن') }}</a>
        </div>
    </div>
</header>
