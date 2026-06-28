@extends('layouts.app')

@section('content')
<section class="bg-aurora relative overflow-hidden pt-36 pb-12">
    <div class="dot-grid absolute inset-0 opacity-40"></div>
    <div class="relative mx-auto max-w-4xl px-5 text-center" data-reveal>
        <h1 class="text-4xl font-black sm:text-5xl">{{ $page->title }}</h1>
        @if($page->excerpt)
            <p class="mx-auto mt-5 max-w-2xl text-lg leading-8 text-cloud-300">{{ $page->excerpt }}</p>
        @endif
    </div>
</section>

<div class="mx-auto max-w-5xl px-5 py-14">
    @forelse($page->content ?? [] as $block)
        @include('partials.block', ['block' => $block])
    @empty
        <p class="text-center text-cloud-400">هذه الصفحة قيد الإعداد.</p>
    @endforelse
</div>
@endsection
