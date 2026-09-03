@extends('layouts.app')

@section('content')
<section class="svc-hero">
    <div class="wrap svc-grid">
        <div data-reveal>
            <span class="kicker">{{ __('تواصل معنا') }}</span>
            <h1>{{ __('لنبدأ') }} <span class="text-gradient-gold">{{ __('مشروعك') }}</span></h1>
            <p class="lead">{{ __('فريق وريد جاهز لمساعدتك. تواصل معنا الآن واحصل على استشارة مجانية لمشروعك التقني.') }}</p>

            <ul class="contact-list">
                <li>
                    <span class="ic-box ic-box-sm"><x-w-icon name="phone" /></span>
                    <div>
                        <span class="cl-label">{{ __('اتصل بنا') }}</span>
                        <b dir="ltr">{{ setting('contact_phone', '+20 100 000 0000') }}</b>
                    </div>
                </li>
                <li>
                    <span class="ic-box ic-box-sm"><x-w-icon name="mail" /></span>
                    <div>
                        <span class="cl-label">{{ __('راسلنا') }}</span>
                        <b dir="ltr">{{ setting('contact_email', 'info@wareed.vip') }}</b>
                    </div>
                </li>
                <li>
                    <span class="ic-box ic-box-sm"><x-w-icon name="pin" /></span>
                    <div>
                        <span class="cl-label">{{ __('العنوان') }}</span>
                        <b>{{ setting('contact_address', 'القاهرة، مصر') }}</b>
                    </div>
                </li>
            </ul>
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
            <form method="POST" action="{{ route('contact.submit') }}" class="f-row">
                @csrf
                <div>
                    <label class="f-label" for="c-name">{{ __('الاسم') }} *</label>
                    <input id="c-name" name="name" value="{{ old('name') }}" required class="f-input" placeholder="{{ __('اسمك الكامل') }}">
                </div>
                <div class="f-row" style="grid-template-columns: 1fr 1fr;">
                    <div>
                        <label class="f-label" for="c-phone">{{ __('الموبايل / واتساب') }} *</label>
                        <input id="c-phone" name="phone" value="{{ old('phone') }}" required class="f-input" dir="ltr" inputmode="tel" placeholder="01xxxxxxxxx">
                    </div>
                    <div>
                        <label class="f-label" for="c-email">{{ __('البريد الإلكتروني') }}</label>
                        <input id="c-email" name="email" type="email" value="{{ old('email') }}" class="f-input" dir="ltr" inputmode="email" placeholder="name@example.com">
                    </div>
                </div>
                <div>
                    <label class="f-label" for="c-service">{{ __('الخدمة المطلوبة') }}</label>
                    <div class="f-select-wrap">
                        <select id="c-service" name="service_type" class="f-select">
                            <option value="general">{{ __('استفسار عام') }}</option>
                            @foreach($services as $s)<option value="{{ $s->key }}" @selected(old('service_type') === $s->key)>{{ $s->name }}</option>@endforeach
                        </select>
                        <x-w-icon name="chevron-down" />
                    </div>
                </div>
                <div>
                    <label class="f-label" for="c-message">{{ __('رسالتك') }}</label>
                    <textarea id="c-message" name="message" rows="4" class="f-area" placeholder="{{ __('أخبرنا عن مشروعك…') }}">{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="btn btn-gold" style="width: 100%;">
                    {{ __('إرسال') }} <x-w-icon name="arrow-left" />
                </button>
                <p class="f-hint" style="text-align: center; margin-top: 0;">{{ __('بياناتك سرّية بالكامل ولا تُشارك مع أي طرف.') }}</p>
            </form>
        </div>
    </div>
</section>
@endsection
