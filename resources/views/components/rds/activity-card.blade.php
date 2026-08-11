@props([
    'index' => 0,
    'title' => null,
    'location' => null,
    'status' => null,
    'collapsible' => true,
    'removable' => true,
    'collapsed' => false,
])

@php
    $numericIndex = is_numeric($index) ? (int) $index : null;
    $displayNumber = $numericIndex !== null
        ? $numericIndex + 1
        : '__NUMBER__';

    $cardTitle = $title
        ?: (
            $numericIndex !== null
                ? 'Work Activity ' . $displayNumber
                : 'Work Activity __NUMBER__'
        );
@endphp

<article
    {{ $attributes->merge([
        'class' => 'ref-activity-card overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm'
    ]) }}
    data-ref-activity-card
    data-ref-index="{{ $index }}"
>
    <div class="flex flex-col gap-3 border-b border-gray-200 bg-gray-50 px-5 py-4 md:flex-row md:items-center md:justify-between">

        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-slate-800 px-2 text-xs font-bold text-white"
                      data-ref-activity-number>
                    {{ $displayNumber }}
                </span>

                <h2 class="truncate text-lg font-bold text-gray-800"
                    data-ref-activity-title>
                    {{ $cardTitle }}
                </h2>

                @if($status)
                    <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800"
                          data-ref-activity-status>
                        {{ $status }}
                    </span>
                @else
                    <span class="hidden rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800"
                          data-ref-activity-status></span>
                @endif
            </div>

            <p class="mt-1 truncate text-sm text-gray-500"
               data-ref-activity-location>
                {{ $location ?: 'Location not selected' }}
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            @if($collapsible)
                <button type="button"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100"
                        data-ref-toggle-activity>
                    {{ $collapsed ? 'Expand' : 'Collapse' }}
                </button>
            @endif

            @if($removable)
                <button type="button"
                        class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700"
                        data-ref-remove-activity>
                    Remove Activity
                </button>
            @endif
        </div>
    </div>

    <div class="{{ $collapsed ? 'hidden' : '' }}"
         data-ref-activity-body>
        {{ $slot }}
    </div>
</article>
