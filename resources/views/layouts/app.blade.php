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
    <meta name="theme-color" content="#0a0e1a">

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

    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=ibm-plex-sans-arabic:400,500,600,700|el-messiri:500,600,700|tajawal:400,500,700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    {{-- JSON-LD: المنظمة (دستور §11) --}}
    <script type="application/ld+json">{!! $orgSchema !!}</script>
    @stack('jsonld')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-ink-900 text-cloud-100 antialiased">
    @include('partials.header')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    {{-- زر واتساب عائم --}}
    @php $wa = preg_replace('/[^0-9]/', '', setting('contact_whatsapp', '201000000000')); @endphp
    <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener"
       class="fixed bottom-6 left-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500 text-ink-950 shadow-2xl transition hover:scale-110"
       aria-label="{{ __('تواصل عبر واتساب') }}">
        <svg viewBox="0 0 24 24" class="h-7 w-7" fill="currentColor"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 018.413 3.488 11.82 11.82 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 001.51 5.26l-.999 3.648 3.737-.97a9.86 9.86 0 00.241.263z"/></svg>
    </a>

    @stack('scripts')
</body>
</html>
