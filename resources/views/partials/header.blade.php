@php
    $navServices = \App\Models\Service::query()->where('is_active', true)->orderBy('sort_order')->get(['key', 'name', 'slug', 'icon']);
    $siteName = setting('site_name', 'وريد');
    $loc = $currentLocale ?? app()->getLocale();
    $otherLoc = $loc === 'ar' ? 'en' : 'ar';
    $switchUrl = request()->fullUrlWithQuery(['hl' => $otherLoc]);
    // أيقونة كل خدمة تُترجم من قيمتها المخزّنة، ولها بديل ثابت حسب مفتاح الخدمة
    $navIcon = fn ($s) => \App\Support\Icons::name($s->icon, \App\Support\Icons::forServiceKey($s->key));
@endphp
<header data-header class="fixed inset-x-0 top-0 z-40">
    <nav class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-3.5">
        {{-- الشعار --}}
        <a href="{{ url('/') }}" class="brand" aria-label="{{ $siteName }}">
            <svg class="mark" viewBox="0 0 40 40" fill="none" aria-hidden="true">
                <defs><linearGradient id="wmH" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0" stop-color="#3B82F6"/><stop offset=".5" stop-color="#8B5CF6"/><stop offset="1" stop-color="#2DD4BF"/>
                </linearGradient></defs>
                <rect x="2.5" y="2.5" width="35" height="35" rx="12" stroke="url(#wmH)" stroke-width="2" opacity=".45"/>
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
            <a href="{{ url('/') }}" class="nav-link">{{ __('الرئيسية') }}</a>
            <div class="group relative">
                <button class="nav-link flex items-center gap-1.5">
                    {{ __('خدماتنا') }}
                    <x-w-icon name="chevron-down" class="ic h-4 w-4" />
                </button>
                <div class="invisible absolute {{ ($isRtl ?? true) ? 'right-0' : 'left-0' }} top-full w-64 translate-y-2 pt-3 opacity-0 transition-all group-hover:visible group-hover:translate-y-0 group-hover:opacity-100">
                    <div class="nav-pop">
                        @foreach($navServices as $s)
                            <a href="{{ url('/services/'.$s->slug) }}">
                                <x-w-icon :name="$navIcon($s)" />{{ $s->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            <a href="{{ url('/stores') }}" class="nav-link">{{ __('المتاجر') }}</a>
            <a href="{{ url('/about') }}" class="nav-link">{{ __('من نحن') }}</a>
            <a href="{{ url('/contact') }}" class="nav-link">{{ __('تواصل معنا') }}</a>
        </div>

        <div class="flex items-center gap-3">
            {{-- مبدّل اللغة --}}
            <a href="{{ $switchUrl }}" class="lang-btn" aria-label="{{ __('اللغة') }}">
                <x-w-icon name="globe" />
                {{ $otherLoc === 'en' ? 'EN' : 'ع' }}
            </a>
            <a href="{{ url('/services/ecommerce') }}" class="btn btn-gold btn-nav text-sm">{{ __('أنشئ متجرك') }}</a>
            {{-- زر قائمة الموبايل --}}
            <button data-nav-toggle class="nav-toggle lg:hidden" aria-label="{{ __('القائمة') }}">
                <x-w-icon name="menu" />
            </button>
        </div>
    </nav>

    {{-- قائمة الموبايل --}}
    <div data-nav-menu class="nav-sheet hidden lg:hidden">
        <div class="flex flex-col gap-1">
            <a href="{{ url('/') }}"><x-w-icon name="spark" />{{ __('الرئيسية') }}</a>
            @foreach($navServices as $s)
                <a href="{{ url('/services/'.$s->slug) }}"><x-w-icon :name="$navIcon($s)" />{{ $s->name }}</a>
            @endforeach
            <a href="{{ url('/stores') }}"><x-w-icon name="store" />{{ __('المتاجر') }}</a>
            <a href="{{ url('/about') }}"><x-w-icon name="users" />{{ __('من نحن') }}</a>
            <a href="{{ url('/contact') }}"><x-w-icon name="mail" />{{ __('تواصل معنا') }}</a>
            <a href="{{ url('/services/ecommerce') }}" class="btn btn-gold mt-3 justify-center">{{ __('أنشئ متجرك الآن') }}</a>
        </div>
    </div>
</header>
