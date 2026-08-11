@props([
    'title',
    'subtitle' => null,
    'icon' => null,
])

<div {{ $attributes->merge([
    'class' => 'mb-4 flex flex-col gap-2 border-b border-gray-200 pb-3 sm:flex-row sm:items-center sm:justify-between'
]) }}>
    <div class="flex items-start gap-3">
        @if($icon)
            <div class="mt-0.5 text-lg">
                {{ $icon }}
            </div>
        @endif

        <div>
            <h3 class="text-base font-bold text-gray-800">
                {{ $title }}
            </h3>

            @if($subtitle)
                <p class="mt-1 text-sm text-gray-500">
                    {{ $subtitle }}
                </p>
            @endif
        </div>
    </div>

    @isset($actions)
        <div class="flex flex-wrap gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
