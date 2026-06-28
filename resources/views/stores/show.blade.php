@extends('layouts.app')

@section('content')
@php $accent = $store->primary_color ?: '#00c896'; @endphp

{{-- ترويسة المتجر --}}
<section class="relative overflow-hidden pt-28">
    <div class="h-48 w-full" style="background: linear-gradient(120deg, {{ $accent }}40, #0a0e1a 70%)"></div>
    <div class="mx-auto -mt-16 max-w-7xl px-5">
        <div class="flex flex-col items-center gap-4 text-center sm:flex-row sm:items-end sm:text-right">
            <div class="flex h-28 w-28 items-center justify-center rounded-3xl bg-ink-800 text-4xl font-black ring-4 ring-ink-900" style="color: {{ $accent }}">
                @if($store->logoUrl())<img src="{{ $store->logoUrl() }}" alt="{{ $store->name }}" class="h-full w-full rounded-3xl object-cover">@else{{ mb_substr($store->name,0,1) }}@endif
            </div>
            <div class="flex-1 pb-2">
                <h1 class="text-3xl font-black text-cloud-50">{{ $store->name }}</h1>
                <p class="mt-1 text-cloud-400">{{ $store->tagline }}</p>
            </div>
            @if($store->whatsapp)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$store->whatsapp) }}" target="_blank" rel="noopener" class="btn text-ink-950" style="background: {{ $accent }}">{{ __('تواصل مع المتجر') }}</a>
            @endif
        </div>
        @if($store->description)<p class="mt-6 max-w-2xl leading-7 text-cloud-300">{{ $store->description }}</p>@endif
    </div>
</section>

@if(session('success'))
    <div class="mx-auto mt-8 max-w-3xl px-5">
        <div class="rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-5 py-4 text-center text-emerald-300">{{ session('success') }}</div>
    </div>
@endif

{{-- المنتجات --}}
<section class="py-14">
    <div class="mx-auto max-w-7xl px-5">
        @if($categories->count())
            <div class="mb-8 flex flex-wrap gap-2">
                <span class="rounded-full px-4 py-1.5 text-sm font-bold text-ink-950" style="background: {{ $accent }}">{{ __('الكل') }}</span>
                @foreach($categories as $cat)
                    <span class="rounded-full glass px-4 py-1.5 text-sm text-cloud-300">{{ $cat->name }}</span>
                @endforeach
            </div>
        @endif

        @if($products->count())
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($products as $product)
                    <a href="{{ route('store.product', [$store, $product]) }}" class="glass card-hover overflow-hidden rounded-3xl" data-reveal>
                        <div class="aspect-square overflow-hidden bg-ink-800">
                            @if($product->firstImageUrl())
                                <img src="{{ $product->firstImageUrl() }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-500 hover:scale-105" loading="lazy">
                            @else
                                <div class="flex h-full items-center justify-center text-5xl opacity-30">🛍️</div>
                            @endif
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-cloud-50">{{ $product->name }}</h3>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-lg font-black" style="color: {{ $accent }}">{{ money($product->price) }}</span>
                                @if($product->compare_price)<span class="text-sm text-cloud-500 line-through">{{ money($product->compare_price) }}</span>@endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-10">{{ $products->links() }}</div>
        @else
            <p class="text-center text-cloud-400">{{ __('لا توجد منتجات في هذا المتجر بعد.') }}</p>
        @endif
    </div>
</section>
@endsection
