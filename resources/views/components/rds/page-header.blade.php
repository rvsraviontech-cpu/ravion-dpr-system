@props([
    'title',
    'description' => null,
    'subtitle' => null,
])

@php
    $finalDescription = $description ?? $subtitle;
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between']) }}>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            {{ $title }}
        </h1>

        @if($finalDescription)
            <p class="mt-1 text-sm text-gray-500">
                {{ $finalDescription }}
            </p>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>