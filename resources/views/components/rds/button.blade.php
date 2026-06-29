@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'primary',
    'icon' => null,
])

@php

$variants = [

    'primary' => 'text-white',
    'secondary' => 'bg-gray-100 text-gray-700 hover:bg-gray-200',
    'success' => 'bg-green-600 text-white hover:bg-green-700',
    'warning' => 'bg-yellow-500 text-white hover:bg-yellow-600',
    'danger' => 'bg-red-600 text-white hover:bg-red-700',

];

$style = '';

if($variant == 'primary'){
    $style = '
        background: '.config('rds.theme.button_primary').';
    ';
}

@endphp

@if($href)

<a
    href="{{ $href }}"
    class="inline-flex items-center gap-2 px-5 py-2 rounded-lg transition shadow-sm {{ $variants[$variant] }}"
    style="{{ $style }}"
>

    @if($icon)

        <i class="{{ $icon }}"></i>

    @endif

    {{ $slot }}

</a>

@else

<button
    type="{{ $type }}"
    class="inline-flex items-center gap-2 px-5 py-2 rounded-lg transition shadow-sm {{ $variants[$variant] }}"
    style="{{ $style }}"
>

    @if($icon)

        <i class="{{ $icon }}"></i>

    @endif

    {{ $slot }}

</button>

@endif