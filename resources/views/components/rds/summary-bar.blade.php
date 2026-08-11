@props([
    'title' => "Today's Execution",
    'subtitle' => null,
    'items' => [],
])

<div {{ $attributes->merge([
    'class' => 'rounded-xl border border-gray-200 bg-white shadow-sm'
]) }}>
    <div class="flex flex-col gap-2 border-b border-gray-200 px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-bold text-gray-800">
                {{ $title }}
            </h2>

            @if($subtitle)
                <p class="mt-1 text-sm text-gray-500">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        @isset($actions)
            <div class="flex flex-wrap gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>

    <div class="grid grid-cols-2 divide-x divide-y divide-gray-200 sm:grid-cols-3 lg:grid-cols-5 lg:divide-y-0">
        @foreach($items as $item)
            <div class="px-5 py-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    {{ $item['label'] ?? '-' }}
                </div>

                <div
                    @if(!empty($item['key']))
                        data-ref-summary="{{ $item['key'] }}"
                    @endif
                    class="mt-1 text-2xl font-bold text-gray-800"
                >
                    {{ $item['value'] ?? 0 }}
                </div>

                @if(!empty($item['suffix']))
                    <div class="mt-1 text-xs text-gray-500">
                        {{ $item['suffix'] }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
