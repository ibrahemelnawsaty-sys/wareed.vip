@php
    $siteName = setting('site_name', 'وريد');
    $footServices = \App\Models\Service::query()->where('is_active', true)->orderBy('sort_order')->get(['name', 'slug']);
@endphp
<footer class="relative mt-24 border-t border-ink-600 bg-ink-800">
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
                <div class="socials">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/','', setting('contact_whatsapp', '201055789056')) }}" target="_blank" rel="noopener" aria-label="WhatsApp">
                        <x-w-icon name="whatsapp" />
                    </a>
                    @foreach([
                        'social_facebook' => ['Facebook', 'M13.5 21.9v-8.1h2.7l.4-3.2h-3.1V8.6c0-.9.3-1.5 1.6-1.5h1.6V4.2c-.3 0-1.3-.1-2.4-.1-2.4 0-4 1.4-4 4.1v2.3H7.6v3.2h2.7v8.1z'],
                        'social_instagram' => ['Instagram', 'M12 7.6a4.4 4.4 0 1 0 0 8.8 4.4 4.4 0 0 0 0-8.8zm0 7.2a2.8 2.8 0 1 1 0-5.6 2.8 2.8 0 0 1 0 5.6zM17.9 6a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM8.4 3.2h7.2a5.2 5.2 0 0 1 5.2 5.2v7.2a5.2 5.2 0 0 1-5.2 5.2H8.4a5.2 5.2 0 0 1-5.2-5.2V8.4a5.2 5.2 0 0 1 5.2-5.2z'],
                        'social_linkedin' => ['LinkedIn', 'M7.1 20.4H4V9.6h3.1zM5.5 8.2A1.8 1.8 0 1 1 5.5 4.6a1.8 1.8 0 0 1 0 3.6zM20.4 20.4h-3.1v-5.3c0-1.3 0-2.9-1.8-2.9s-2 1.4-2 2.8v5.4H10.4V9.6h3v1.5h.1a3.3 3.3 0 0 1 3-1.6c3.2 0 3.8 2.1 3.8 4.8z'],
                        'social_tiktok' => ['TikTok', 'M15.9 3.2h-2.8v11.6a2.6 2.6 0 1 1-2.6-2.6c.3 0 .5 0 .7.1V9.4a5.6 5.6 0 1 0 4.7 5.5V9.1a6.4 6.4 0 0 0 3.8 1.2V7.5a3.7 3.7 0 0 1-3.8-3.6z'],
                    ] as $key => [$label, $path])
                        @if(setting($key))
                            <a href="{{ setting($key) }}" target="_blank" rel="noopener" aria-label="{{ $label }}">
                                <svg class="ic" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" stroke="none" d="{{ $path }}"/></svg>
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
                        <li><a href="{{ url('/services/'.$s->slug) }}" class="transition hover:text-gold-500">{{ $s->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- روابط --}}
            <div>
                <h4 class="mb-4 text-sm font-bold text-cloud-100">{{ __('روابط سريعة') }}</h4>
                <ul class="space-y-2.5 text-sm text-cloud-400">
                    <li><a href="{{ url('/about') }}" class="transition hover:text-gold-500">{{ __('من نحن') }}</a></li>
                    <li><a href="{{ url('/stores') }}" class="transition hover:text-gold-500">{{ __('المتاجر') }}</a></li>
                    <li><a href="{{ url('/contact') }}" class="transition hover:text-gold-500">{{ __('تواصل معنا') }}</a></li>
                    <li><a href="{{ url('/sitemap.xml') }}" class="transition hover:text-gold-500">{{ __('خريطة الموقع') }}</a></li>
                </ul>
            </div>

            {{-- تواصل --}}
            <div>
                <h4 class="mb-4 text-sm font-bold text-cloud-100">{{ __('تواصل معنا') }}</h4>
                @php $waNum = preg_replace('/[^0-9]/', '', setting('contact_whatsapp', '201055789056')); @endphp
                <ul class="foot-contact">
                    <li><x-w-icon name="phone" /><span dir="ltr">{{ setting('contact_phone', '+201055789056') }}</span></li>
                    <li>
                        <a href="https://wa.me/{{ $waNum }}" target="_blank" rel="noopener">
                            <x-w-icon name="whatsapp" /><span dir="ltr">+{{ $waNum }}</span>
                        </a>
                    </li>
                    <li><x-w-icon name="mail" /><span dir="ltr">{{ setting('contact_email', 'info@wareed.vip') }}</span></li>
                    <li><x-w-icon name="pin" />{{ setting('contact_address', 'نعمل عن بُعد حول العالم') }}</li>
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
