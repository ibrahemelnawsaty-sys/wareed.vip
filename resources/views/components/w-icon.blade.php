@props(['name' => 'spark', 'size' => null])
@php
    $icon = \App\Support\Icons::name($name);
@endphp
<svg {{ $attributes->class('ic') }} @if($size) style="width:{{ $size }};height:{{ $size }}" @endif aria-hidden="true" focusable="false"><use href="#ic-{{ $icon }}"/></svg>
