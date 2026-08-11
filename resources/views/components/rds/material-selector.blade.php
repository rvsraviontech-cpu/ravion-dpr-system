@props([
    'index',
    'selectedIds' => [],
])

@php
    $selectedIds = collect($selectedIds)
        ->map(fn ($id) => (string) $id)
        ->values()
        ->all();
@endphp

<div class="rounded-lg border border-gray-200 bg-white p-4"
     data-ref-material-selector
     data-ref-work-index="{{ $index }}"
     data-ref-selected-materials='@json($selectedIds)'>

    <x-rds.section-title
        title="Material Used"
        subtitle="Link existing Material Consumed transactions for this Project and Date."
        icon="🧱"
    />

    <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
        Material quantities are not entered again here. Link the Material Consumed transaction already recorded for this site so Stock Register, Material Ledger, Work Done and DPR remain synchronized.
    </div>

    <div class="mt-4 hidden rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600"
         data-ref-material-loading>
        Loading eligible Material Consumed records...
    </div>

    <div class="mt-4 space-y-3"
         data-ref-material-list>
    </div>

    <div class="mt-4 rounded-lg border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500"
         data-ref-material-empty>
        Select Project and Work Date to load Material Consumed records.
    </div>
</div>
