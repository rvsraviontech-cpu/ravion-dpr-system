@props([
    'variant' => 'default',
])

@php
    $classes = match($variant) {
        'success' => 'bg-green-100 text-green-700',
        'danger' => 'bg-red-100 text-red-700',
        'warning' => 'bg-yellow-100 text-yellow-700',
        'info' => 'bg-blue-50 text-blue-700',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<span {{ $attributes->merge([
    'class' => "inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold {$classes}"
]) }}>
    {{ $slot }}
</span>