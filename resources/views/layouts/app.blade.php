<!DOCTYPE html>
<html lang="{{ $currentLocale ?? app()->getLocale() }}" dir="{{ ($isRtl ?? true) ? 'rtl' : 'ltr' }}">
<head>
    <script>document.documentElement.classList.add('js');</script>
    @php
        $siteName = setting('site_name', 'وريد');
        $seo = $seo ?? [];
        $pageTitle = ($seo['title'] ?? null) ? $seo['title'] . ' — ' . $siteName : $siteName . ' — ' . setting('site_tagline', 'شريان الحياة التقني لمصر');
        $pageDesc = $seo['description'] ?? setting('site_description', 'منصة وريد: متاجر إلكترونية من ضغطة زر، حلول تقنية للشركات والجهات الحكومية، وتدريب تقني احترافي في مصر.');
        $ogImage = $seo['image'] ?? setting('default_og_image');
        $canonical = $seo['canonical'] ?? url()->current();
        $noindex = $seo['noindex'] ?? false;
        $keywords = $seo['keywords'] ?? setting('seo_keywords');

        // JSON-LD للمنظمة — يُبنى داخل @php لتفادي تعارض '@context' مع موجّه Blade
        $orgSchema = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => url('/'),
            'description' => setting('site_description', 'منصة تقنية مصرية متكاملة.'),
            'sameAs' => array_values(array_filter([
                setting('social_facebook'), setting('social_instagram'),
                setting('social_linkedin'), setting('social_tiktok'),
            ])),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDesc }}">
    @if($keywords)<meta name="keywords" content="{{ $keywords }}">@endif
    <link rel="canonical" href="{{ $canonical }}">
    {{-- hreflang: نسختان عربي/إنجليزي (دستور §11) --}}
    <link rel="alternate" hreflang="ar" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="en" href="{{ request()->fullUrlWithQuery(['hl' => 'en']) }}">
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">
    @if($noindex)<meta name="robots" content="noindex, nofollow">@else<meta name="robots" content="index, follow, max-image-preview:large">@endif
    <meta name="theme-color" content="#ffffff">
    <meta name="color-scheme" content="light">

    {{-- Open Graph / Twitter --}}
    <meta property="og:type" content="{{ $seo['og_type'] ?? 'website' }}">
    <meta property="og:locale" content="{{ ($currentLocale ?? 'ar') === 'ar' ? 'ar_EG' : 'en_US' }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDesc }}">
    <meta property="og:url" content="{{ $canonical }}">
    @if($ogImage)<meta property="og:image" content="{{ \Illuminate\Support\Str::startsWith($ogImage, 'http') ? $ogImage : asset('storage/'.$ogImage) }}">@endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDesc }}">

    {{-- خط ثمانية مستضاف ذاتياً: لا طلب لخدمة خارجية، ونحمّل الوزنين الأكثر ظهوراً مبكراً --}}
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/thmanyah/ThmanyahSans-Regular.woff2') }}" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/thmanyah/ThmanyahSans-Bold.woff2') }}" crossorigin>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    {{-- JSON-LD: المنظمة (دستور §11) --}}
    <script type="application/ld+json">{!! $orgSchema !!}</script>
    @stack('jsonld')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="antialiased">
    @include('partials.icons')
    @include('partials.header')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    {{-- زر واتساب عائم --}}
    @php $wa = preg_replace('/[^0-9]/', '', setting('contact_whatsapp', '201055789056')); @endphp
    <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="wa-float"
       aria-label="{{ __('تواصل عبر واتساب') }}">
        <x-w-icon name="whatsapp" />
    </a>

    @stack('scripts')
</body>
</html>
