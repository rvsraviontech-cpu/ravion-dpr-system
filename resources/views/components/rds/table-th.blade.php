@props([
    'align' => 'left',
])

@php
    $alignClass = match($align) {
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left',
    };
@endphp

<th {{ $attributes->merge([
    'class' => "px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 {$alignClass}"
]) }}>
    {{ $slot }}
</th>