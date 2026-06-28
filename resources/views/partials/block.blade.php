@php
    $type = $block['type'] ?? 'richtext';
    $data = $block['data'] ?? [];
@endphp

@switch($type)
    @case('hero')
        <section class="mb-12 text-center" data-reveal>
            <h2 class="text-3xl font-black sm:text-4xl">{{ $data['title'] ?? '' }}</h2>
            @if(!empty($data['subtitle']))<p class="mx-auto mt-4 max-w-2xl text-cloud-300">{{ $data['subtitle'] }}</p>@endif
            @if(!empty($data['cta_label']))
                <a href="{{ $data['cta_url'] ?? '#' }}" class="btn btn-gold mt-6">{{ $data['cta_label'] }}</a>
            @endif
        </section>
        @break

    @case('richtext')
        <div class="prose-invert mb-10 leading-8 text-cloud-200" data-reveal>
            {!! $data['html'] ?? '' !!}
        </div>
        @break

    @case('features')
        <section class="mb-14" data-reveal>
            @if(!empty($data['title']))<h2 class="mb-8 text-center text-3xl font-black">{{ $data['title'] }}</h2>@endif
            <div class="grid gap-6 md:grid-cols-3">
                @foreach(($data['items'] ?? []) as $item)
                    <div class="glass card-hover rounded-3xl p-7">
                        <div class="text-3xl">{{ $item['icon'] ?? '✦' }}</div>
                        <h3 class="mt-4 text-lg font-bold text-cloud-50">{{ $item['title'] ?? '' }}</h3>
                        <p class="mt-2 text-sm leading-7 text-cloud-400">{{ $item['desc'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </section>
        @break

    @case('stats')
        <section class="mb-14" data-reveal>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach(($data['items'] ?? []) as $item)
                    <div class="glass rounded-3xl p-7 text-center">
                        <div class="text-3xl font-black text-gold-400">{{ $item['value'] ?? '' }}</div>
                        <div class="mt-2 text-sm text-cloud-400">{{ $item['label'] ?? '' }}</div>
                    </div>
                @endforeach
            </div>
        </section>
        @break

    @case('cta')
        <section class="mb-14" data-reveal>
            <div class="glass-gold rounded-[2rem] px-8 py-12 text-center">
                <h2 class="text-3xl font-black">{{ $data['title'] ?? '' }}</h2>
                @if(!empty($data['subtitle']))<p class="mx-auto mt-3 max-w-xl text-cloud-300">{{ $data['subtitle'] }}</p>@endif
                @if(!empty($data['button_label']))
                    <a href="{{ $data['button_url'] ?? '#' }}" class="btn btn-gold mt-6">{{ $data['button_label'] }}</a>
                @endif
            </div>
        </section>
        @break

    @case('faq')
        <section class="mb-14" data-reveal>
            @if(!empty($data['title']))<h2 class="mb-8 text-center text-3xl font-black">{{ $data['title'] }}</h2>@endif
            <div class="space-y-4">
                @foreach(($data['items'] ?? []) as $item)
                    <details class="glass rounded-2xl p-6">
                        <summary class="cursor-pointer list-none text-lg font-bold text-cloud-100">{{ $item['q'] ?? '' }}</summary>
                        <p class="mt-3 leading-7 text-cloud-400">{{ $item['a'] ?? '' }}</p>
                    </details>
                @endforeach
            </div>
        </section>
        @break

    @default
        <div class="prose-invert mb-10 leading-8 text-cloud-200">{!! $data['html'] ?? '' !!}</div>
@endswitch
