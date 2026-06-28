@extends('layouts.app')

@section('content')
@php $accent = $store->primary_color ?: '#00c896'; @endphp

<section class="pt-32 pb-16">
    <div class="mx-auto max-w-6xl px-5">
        <a href="{{ route('store.show', $store) }}" class="mb-6 inline-flex items-center gap-2 text-sm text-cloud-400 hover:text-cloud-200">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m5 12 7 7M5 12l7-7M5 12h14" transform="scale(-1,1) translate(-24,0)"/></svg>
            {{ __('العودة إلى') }} {{ $store->name }}
        </a>

        <div class="grid gap-10 lg:grid-cols-2">
            {{-- صورة المنتج --}}
            <div class="glass overflow-hidden rounded-[2rem]">
                <div class="aspect-square bg-ink-800">
                    @if($product->firstImageUrl())
                        <img src="{{ $product->firstImageUrl() }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-7xl opacity-30">🛍️</div>
                    @endif
                </div>
            </div>

            {{-- التفاصيل والطلب --}}
            <div>
                <h1 class="text-3xl font-black text-cloud-50">{{ $product->name }}</h1>
                <div class="mt-4 flex items-center gap-3">
                    <span class="text-3xl font-black" style="color: {{ $accent }}">{{ money($product->price) }}</span>
                    @if($product->compare_price)<span class="text-lg text-cloud-500 line-through">{{ money($product->compare_price) }}</span>@endif
                </div>
                @if($product->description)<p class="mt-5 leading-8 text-cloud-300">{{ $product->description }}</p>@endif

                @if(session('success'))
                    <div class="mt-6 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-5 py-4 text-emerald-300">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="mt-6 rounded-xl border border-vein-500/40 bg-vein-500/10 px-5 py-4 text-sm text-red-300">
                        <ul class="list-inside list-disc">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('store.order', [$store, $product]) }}" class="mt-8 grid gap-4 rounded-3xl glass p-6">
                    @csrf
                    <h3 class="text-lg font-bold text-cloud-100">{{ __('اطلب الآن — الدفع عند الاستلام') }}</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <input name="customer_name" value="{{ old('customer_name') }}" required placeholder="{{ __('الاسم') }} *" class="rounded-xl border border-white/10 bg-ink-850 px-4 py-3 text-cloud-100 outline-none focus:border-gold-500">
                        <input name="customer_phone" value="{{ old('customer_phone') }}" required placeholder="{{ __('الموبايل') }} *" class="rounded-xl border border-white/10 bg-ink-850 px-4 py-3 text-cloud-100 outline-none focus:border-gold-500">
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <input name="governorate" value="{{ old('governorate') }}" placeholder="{{ __('المحافظة') }}" class="rounded-xl border border-white/10 bg-ink-850 px-4 py-3 text-cloud-100 outline-none focus:border-gold-500">
                        <input name="quantity" type="number" min="1" max="99" value="{{ old('quantity', 1) }}" placeholder="{{ __('الكمية') }}" class="rounded-xl border border-white/10 bg-ink-850 px-4 py-3 text-cloud-100 outline-none focus:border-gold-500">
                    </div>
                    <textarea name="shipping_address" required rows="2" placeholder="{{ __('العنوان بالتفصيل') }} *" class="rounded-xl border border-white/10 bg-ink-850 px-4 py-3 text-cloud-100 outline-none focus:border-gold-500">{{ old('shipping_address') }}</textarea>
                    <button type="submit" class="btn w-full justify-center text-ink-950" style="background: {{ $accent }}">{{ __('تأكيد الطلب') }}</button>
                    @if($store->whatsapp)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$store->whatsapp) }}?text={{ urlencode('أريد طلب: '.$product->name) }}" target="_blank" rel="noopener" class="btn btn-ghost w-full justify-center">{{ __('اطلب عبر واتساب') }}</a>
                    @endif
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
