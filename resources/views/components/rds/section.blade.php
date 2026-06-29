@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'border-b border-gray-200 pb-6 last:border-b-0 last:pb-0']) }}>

    <div class="mb-5">
        <h3 class="text-lg font-semibold text-gray-900">
            {{ $title }}
        </h3>

        @if($description)
            <p class="mt-1 text-sm text-gray-500">
                {{ $description }}
            </p>
        @endif
    </div>

    {{ $slot }}

</div>