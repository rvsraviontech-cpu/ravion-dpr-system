@props([
    'title' => null,
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white border border-gray-200 rounded-xl shadow-sm']) }}>
    @if($title || $subtitle)
        <div class="px-5 py-4 border-b border-gray-200">
            @if($title)
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ $title }}
                </h2>
            @endif

            @if($subtitle)
                <p class="text-sm text-gray-500 mt-1">
                    {{ $subtitle }}
                </p>
            @endif
        </div>
    @endif

    <div class="p-5">
        {{ $slot }}
    </div>
</div>