@extends('layouts.app')

@section('content')
<section class="bg-aurora relative overflow-hidden pt-36 pb-20">
    <div class="dot-grid absolute inset-0 opacity-40"></div>
    <div class="relative mx-auto max-w-7xl px-5">
        <div class="grid gap-12 lg:grid-cols-2">
            <div data-reveal>
                <h1 class="text-4xl font-black sm:text-5xl">{{ __('لنبدأ') }} <span class="text-gradient-gold">{{ __('مشروعك') }}</span></h1>
                <p class="mt-5 max-w-lg text-lg leading-8 text-cloud-300">{{ __('فريق وريد جاهز لمساعدتك. تواصل معنا الآن واحصل على استشارة مجانية لمشروعك التقني.') }}</p>

                <div class="mt-10 space-y-5">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl glass text-xl">📞</div>
                        <div>
                            <div class="text-sm text-cloud-400">{{ __('اتصل بنا') }}</div>
                            <div class="font-bold text-cloud-100" dir="ltr">{{ setting('contact_phone', '+20 100 000 0000') }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl glass text-xl">✉️</div>
                        <div>
                            <div class="text-sm text-cloud-400">{{ __('راسلنا') }}</div>
                            <div class="font-bold text-cloud-100" dir="ltr">{{ setting('contact_email', 'info@wareed.vip') }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl glass text-xl">📍</div>
                        <div>
                            <div class="text-sm text-cloud-400">{{ __('العنوان') }}</div>
                            <div class="font-bold text-cloud-100">{{ setting('contact_address', 'القاهرة، مصر') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="glass rounded-[2rem] p-8" data-reveal>
                @if(session('success'))
                    <div class="mb-6 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-5 py-4 text-center text-emerald-300">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="mb-6 rounded-xl border border-vein-500/40 bg-vein-500/10 px-5 py-4 text-sm text-red-300">
                        <ul class="list-inside list-disc">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('contact.submit') }}" class="grid gap-5">
                    @csrf
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-cloud-200">{{ __('الاسم') }} *</label>
                        <input name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-white/10 bg-ink-850 px-4 py-3 text-cloud-100 outline-none transition focus:border-gold-500">
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-cloud-200">{{ __('الموبايل / واتساب') }} *</label>
                            <input name="phone" value="{{ old('phone') }}" required class="w-full rounded-xl border border-white/10 bg-ink-850 px-4 py-3 text-cloud-100 outline-none transition focus:border-gold-500">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-cloud-200">{{ __('البريد الإلكتروني') }}</label>
                            <input name="email" type="email" value="{{ old('email') }}" class="w-full rounded-xl border border-white/10 bg-ink-850 px-4 py-3 text-cloud-100 outline-none transition focus:border-gold-500">
                        </div>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-cloud-200">{{ __('الخدمة المطلوبة') }}</label>
                        <select name="service_type" class="w-full rounded-xl border border-white/10 bg-ink-850 px-4 py-3 text-cloud-100 outline-none transition focus:border-gold-500">
                            <option value="general">{{ __('استفسار عام') }}</option>
                            @foreach($services as $s)<option value="{{ $s->key }}">{{ $s->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-cloud-200">{{ __('رسالتك') }}</label>
                        <textarea name="message" rows="4" class="w-full rounded-xl border border-white/10 bg-ink-850 px-4 py-3 text-cloud-100 outline-none transition focus:border-gold-500">{{ old('message') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-gold w-full justify-center text-base">{{ __('إرسال') }}</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
