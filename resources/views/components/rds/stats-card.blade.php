@props([
    'title',
    'value',
    'description' => null,
    'icon' => null,
])

<div {{ $attributes->merge([
    'class' => 'rounded-xl border border-gray-200 bg-white p-5 shadow-sm'
]) }}>
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-gray-500">
                {{ $title }}
            </p>

            <p class="mt-2 text-2xl font-semibold text-gray-900">
                {{ $value }}
            </p>

            @if($description)
                <p class="mt-1 text-sm text-gray-500">
                    {{ $description }}
                </p>
            @endif
        </div>

        @if($icon)
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-gray-500">
                {!! $icon !!}
            </div>
        @endif
    </div>
</div>