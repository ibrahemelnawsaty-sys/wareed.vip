@extends('layouts.app')

@section('content')
<section class="bg-aurora relative overflow-hidden pt-36 pb-16">
    <div class="dot-grid absolute inset-0 opacity-40"></div>
    <div class="relative mx-auto max-w-7xl px-5 text-center" data-reveal>
        <h1 class="text-4xl font-black sm:text-5xl">{{ __('المتاجر') }} <span class="text-gradient-gold">{{ setting('site_name', 'وريد') }}</span></h1>
        <p class="mx-auto mt-5 max-w-2xl text-lg text-cloud-300">{{ __('متاجر إلكترونية أُنشئت من ضغطة زر على منصة وريد. أنشئ متجرك أنت أيضاً.') }}</p>
        <a href="{{ url('/services/ecommerce') }}" class="btn btn-gold mt-8">{{ __('أنشئ متجرك مجاناً') }}</a>
    </div>
</section>

<section class="py-14">
    <div class="mx-auto max-w-7xl px-5">
        @if($stores->count())
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($stores as $store)
                    <a href="{{ route('store.show', $store) }}" class="glass card-hover overflow-hidden rounded-3xl" data-reveal>
                        <div class="h-28" style="background: linear-gradient(120deg, {{ $store->primary_color }}55, transparent)"></div>
                        <div class="-mt-10 px-6 pb-6">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-ink-800 text-2xl font-black ring-2 ring-white/10" style="color: {{ $store->primary_color }}">
                                @if($store->logoUrl())<img src="{{ $store->logoUrl() }}" alt="{{ $store->name }}" class="h-full w-full rounded-2xl object-cover">@else{{ mb_substr($store->name,0,1) }}@endif
                            </div>
                            <h3 class="mt-4 text-lg font-bold text-cloud-50">{{ $store->name }}</h3>
                            <p class="mt-1 text-sm text-cloud-400">{{ $store->tagline }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-10">{{ $stores->links() }}</div>
        @else
            <p class="text-center text-cloud-400">{{ __('لا توجد متاجر منشورة بعد.') }}</p>
        @endif
    </div>
</section>
@endsection
