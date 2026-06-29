@props([
    'href' => null,
])

@if($href)

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => 'block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100'
    ]) }}
>
    {{ $slot }}
</a>

@else

<button
    type="button"
    {{ $attributes->merge([
        'class' => 'block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100'
    ]) }}
>
    {{ $slot }}
</button>

@endif