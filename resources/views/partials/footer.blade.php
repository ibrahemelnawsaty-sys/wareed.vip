@php
    $siteName = setting('site_name', 'وريد');
    $footServices = \App\Models\Service::query()->where('is_active', true)->orderBy('sort_order')->get(['name', 'slug']);
@endphp
<footer class="relative mt-24 border-t border-white/5 bg-ink-950">
    <div class="mx-auto max-w-7xl px-5 py-16">
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
            {{-- العلامة --}}
            <div class="lg:col-span-1 foot-brand">
                <a href="{{ url('/') }}" class="brand">
                    <svg class="mark" viewBox="0 0 40 40" fill="none" aria-hidden="true">
                        <defs><linearGradient id="wmF" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0" stop-color="#3B82F6"/><stop offset=".5" stop-color="#8B5CF6"/><stop offset="1" stop-color="#2DD4BF"/>
                        </linearGradient></defs>
                        <rect x="2.5" y="2.5" width="35" height="35" rx="11" stroke="url(#wmF)" stroke-width="2" opacity=".5"/>
                        <path d="M11 27 L18 14 L24 23 L29 13" stroke="url(#wmF)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="11" cy="27" r="2.5" fill="#2DD4BF"/>
                        <circle cx="18" cy="14" r="2.5" fill="#3B82F6"/>
                        <circle cx="24" cy="23" r="2.5" fill="#8B5CF6"/>
                        <circle cx="29" cy="13" r="2.5" fill="#60A5FA"/>
                    </svg>
                    <span class="text-gradient-gold">{{ $siteName }}</span>
                </a>
                <p class="mt-4 text-sm leading-7 text-cloud-400">
                    {{ setting('site_description', 'شريان الحياة التقني لمصر — متاجر إلكترونية، حلول تقنية، وتدريب احترافي.') }}
                </p>
                <div class="mt-5 flex gap-3">
                    @foreach(['social_facebook'=>'Facebook','social_instagram'=>'Instagram','social_linkedin'=>'LinkedIn','social_tiktok'=>'TikTok'] as $key=>$label)
                        @if(setting($key))
                            <a href="{{ setting($key) }}" target="_blank" rel="noopener" class="flex h-9 w-9 items-center justify-center rounded-lg glass text-cloud-300 transition hover:text-gold-300" aria-label="{{ $label }}">
                                <span class="text-xs">{{ mb_substr($label,0,2) }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- خدماتنا --}}
            <div>
                <h4 class="mb-4 text-sm font-bold text-cloud-100">{{ __('خدماتنا') }}</h4>
                <ul class="space-y-2.5 text-sm text-cloud-400">
                    @foreach($footServices as $s)
                        <li><a href="{{ url('/services/'.$s->slug) }}" class="transition hover:text-gold-300">{{ $s->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- روابط --}}
            <div>
                <h4 class="mb-4 text-sm font-bold text-cloud-100">{{ __('روابط سريعة') }}</h4>
                <ul class="space-y-2.5 text-sm text-cloud-400">
                    <li><a href="{{ url('/about') }}" class="transition hover:text-gold-300">{{ __('من نحن') }}</a></li>
                    <li><a href="{{ url('/stores') }}" class="transition hover:text-gold-300">{{ __('المتاجر') }}</a></li>
                    <li><a href="{{ url('/contact') }}" class="transition hover:text-gold-300">{{ __('تواصل معنا') }}</a></li>
                    <li><a href="{{ url('/sitemap.xml') }}" class="transition hover:text-gold-300">{{ __('خريطة الموقع') }}</a></li>
                </ul>
            </div>

            {{-- تواصل --}}
            <div>
                <h4 class="mb-4 text-sm font-bold text-cloud-100">{{ __('تواصل معنا') }}</h4>
                <ul class="space-y-2.5 text-sm text-cloud-400">
                    <li>📞 {{ setting('contact_phone', '+20 100 000 0000') }}</li>
                    <li>✉️ {{ setting('contact_email', 'info@wareed.vip') }}</li>
                    <li>📍 {{ setting('contact_address', 'القاهرة، مصر') }}</li>
                </ul>
                <a href="{{ url('/contact') }}" class="btn btn-ghost mt-5 text-sm">{{ __('احجز استشارة مجانية') }}</a>
            </div>
        </div>

        <div class="divider-glow my-10"></div>
        <div class="flex flex-col items-center justify-between gap-3 text-xs text-cloud-500 sm:flex-row">
            <p>© {{ date('Y') }} {{ $siteName }}. {{ __('جميع الحقوق محفوظة.') }}</p>
            <p>{{ __('صُنع بشغف وإتقان') }}</p>
        </div>
    </div>
</footer>
