@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-base text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:py-2 sm:text-sm';

    $roleName = auth()->user()->role?->name;

    $hasPermission = function (string $permission) use ($roleName): bool {
        if ($roleName === 'Admin') {
            return true;
        }

        return auth()->user()
            ->role
            ?->permissions()
            ->where('name', $permission)
            ->where('is_active', true)
            ->exists() ?? false;
    };

    $canCreate = $hasPermission('material_consumed.create');
    $canEdit = $hasPermission('material_consumed.edit');
    $canApprove = $hasPermission('material_consumed.approve')
        || in_array($roleName, ['PMO', 'DGM'], true);
@endphp

<div class="mx-auto max-w-full">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">
                Material Consumed
            </h1>

            <p class="mt-1 text-gray-500">
                Track material consumption, wastage and approval status by project and activity.
            </p>
        </div>

        @if($canCreate)
            <a href="{{ route('material-consumed.create') }}"
               class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white shadow-sm hover:bg-blue-700 sm:w-auto sm:py-2.5">
                + Add Material Consumed
            </a>
        @endif

    </div>

    @if(session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-2 xl:grid-cols-5 md:gap-4">

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
            <p class="text-sm text-gray-500">
                Qty Consumed Today
            </p>

            <p class="mt-2 text-2xl font-bold text-blue-700">
                {{ formatQuantity($totalConsumedToday) }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
            <p class="text-sm text-gray-500">
                Wastage Today
            </p>

            <p class="mt-2 text-2xl font-bold text-red-700">
                {{ formatQuantity($totalWastageToday) }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
            <p class="text-sm text-gray-500">
                Draft Entries
            </p>

            <p class="mt-2 text-2xl font-bold text-yellow-700">
                {{ $draftCount }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
            <p class="text-sm text-gray-500">
                Submitted Entries
            </p>

            <p class="mt-2 text-2xl font-bold text-orange-700">
                {{ $submittedCount }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
            <p class="text-sm text-gray-500">
                Approved Entries
            </p>

            <p class="mt-2 text-2xl font-bold text-green-700">
                {{ $approvedCount }}
            </p>
        </div>

    </div>

    {{-- Filters --}}
    <div class="mb-6" x-data="{ filtersOpen: false }">
        <button type="button"
                @click="filtersOpen = !filtersOpen"
                class="mb-3 flex w-full items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 text-left shadow-sm lg:hidden">
            <div>
                <div class="text-sm font-bold text-gray-800">Filters</div>
                <div class="text-xs text-gray-500">Date, project, status and search</div>
            </div>
            <svg class="h-5 w-5 text-gray-500 transition-transform"
                 :class="filtersOpen ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="filtersOpen" x-cloak class="lg:hidden">
<form method="GET"
          action="{{ route('material-consumed.index') }}"
          class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Date
                </label>

                <input type="date"
                       name="consumed_date"
                       value="{{ request('consumed_date') }}"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Project
                </label>

                <select name="project_id"
                        class="{{ $inputClass }}">

                    <option value="">All Projects</option>

                    @foreach($projects as $project)
                        <option value="{{ $project->id }}"
                            {{ (string) request('project_id') === (string) $project->id ? 'selected' : '' }}>
                            {{ $project->project_name }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Status
                </label>

                <select name="status"
                        class="{{ $inputClass }}">

                    <option value="">All Statuses</option>

                    @foreach(['Draft', 'Submitted', 'Approved', 'Rejected'] as $status)
                        <option value="{{ $status }}"
                            {{ request('status') === $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Search
                </label>

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="{{ $inputClass }}"
                       placeholder="Material, brand, activity, contractor">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="w-full rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 sm:w-auto sm:py-2.5">
                    Filter
                </button>

                <a href="{{ route('material-consumed.index') }}"
                   class="w-full rounded-lg bg-gray-600 px-5 py-3 text-center text-sm font-semibold text-white hover:bg-gray-700 sm:w-auto sm:py-2.5">
                    Clear
                </a>
            </div>

        </div>

    </form>
        </div>

        <div class="hidden lg:block">
<form method="GET"
          action="{{ route('material-consumed.index') }}"
          class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Date
                </label>

                <input type="date"
                       name="consumed_date"
                       value="{{ request('consumed_date') }}"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Project
                </label>

                <select name="project_id"
                        class="{{ $inputClass }}">

                    <option value="">All Projects</option>

                    @foreach($projects as $project)
                        <option value="{{ $project->id }}"
                            {{ (string) request('project_id') === (string) $project->id ? 'selected' : '' }}>
                            {{ $project->project_name }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Status
                </label>

                <select name="status"
                        class="{{ $inputClass }}">

                    <option value="">All Statuses</option>

                    @foreach(['Draft', 'Submitted', 'Approved', 'Rejected'] as $status)
                        <option value="{{ $status }}"
                            {{ request('status') === $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Search
                </label>

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="{{ $inputClass }}"
                       placeholder="Material, brand, activity, contractor">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('material-consumed.index') }}"
                   class="rounded-lg bg-gray-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700">
                    Clear
                </a>
            </div>

        </div>

    </form>
        </div>
    </div>

    {{-- Mobile Material Consumption Register --}}
    <div class="space-y-4 lg:hidden">
        <div class="flex items-end justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Material Consumption</h2>
                <p class="text-xs text-gray-500">
                    {{ $materialConsumeds->total() }} matching entr{{ $materialConsumeds->total() === 1 ? 'y' : 'ies' }}
                </p>
            </div>
            <div class="text-xs text-gray-500">
                {{ $materialConsumeds->firstItem() ?? 0 }}–{{ $materialConsumeds->lastItem() ?? 0 }}
            </div>
        </div>

        @forelse($materialConsumeds as $index => $materialConsumed)
            @php
                $hasNewItems = $materialConsumed->items->isNotEmpty();

                $statusClasses = match($materialConsumed->status) {
                    'Approved' => 'bg-green-100 text-green-800',
                    'Submitted' => 'bg-blue-100 text-blue-800',
                    'Rejected' => 'bg-red-100 text-red-800',
                    default => 'bg-yellow-100 text-yellow-800',
                };

                $itemCount = $hasNewItems ? $materialConsumed->items->count() : 1;
                $primaryItem = $hasNewItems ? $materialConsumed->items->first() : null;

                $primaryMaterial = $hasNewItems
                    ? ($primaryItem?->materialType?->material_type_name ?? '-')
                    : ($materialConsumed->material?->material_name ?? '-');

                $primaryActivity = $hasNewItems
                    ? ($primaryItem?->activity?->activity_name ?? '-')
                    : ($materialConsumed->activity?->activity_name ?? '-');

                $primaryConsumed = $hasNewItems
                    ? formatQuantity($primaryItem?->quantity_consumed)
                    : formatQuantity($materialConsumed->quantity_consumed);

                $primaryWastage = $hasNewItems
                    ? formatQuantity($primaryItem?->wastage_quantity)
                    : formatQuantity($materialConsumed->wastage_quantity);

                $primaryUnit = $hasNewItems
                    ? ($primaryItem?->unit?->unit_name ?? '')
                    : ($materialConsumed->unit ?? '');
            @endphp

            <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="bg-[#0F2A52] px-4 py-4 text-white">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-medium text-blue-100">
                                {{ $materialConsumed->consumed_date?->format('d M Y') ?? '-' }}
                            </div>
                            <h3 class="mt-1 truncate text-base font-bold">
                                {{ $materialConsumed->project?->project_name ?? '-' }}
                            </h3>
                            @if($materialConsumed->block)
                                <div class="mt-1 truncate text-xs text-blue-100">
                                    {{ $materialConsumed->block->name }}
                                </div>
                            @endif
                        </div>

                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusClasses }}">
                            {{ $materialConsumed->status }}
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    <div class="grid grid-cols-3 gap-2">
                        <div class="rounded-xl bg-blue-50 px-2 py-3 text-center">
                            <div class="text-lg font-bold text-blue-800">{{ $itemCount }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-wide text-blue-700">Materials</div>
                        </div>

                        <div class="rounded-xl bg-green-50 px-2 py-3 text-center">
                            <div class="text-lg font-bold text-green-800">
                                {{ formatQuantity($materialConsumed->total_quantity_consumed) }}
                            </div>
                            <div class="text-[10px] font-bold uppercase tracking-wide text-green-700">Consumed</div>
                        </div>

                        <div class="rounded-xl bg-red-50 px-2 py-3 text-center">
                            <div class="text-lg font-bold text-red-800">
                                {{ formatQuantity($materialConsumed->total_wastage_quantity) }}
                            </div>
                            <div class="text-[10px] font-bold uppercase tracking-wide text-red-700">Wastage</div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 px-3 py-3">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Primary Material</div>
                        <div class="mt-1 font-semibold text-gray-800">{{ $primaryMaterial }}</div>
                        <div class="mt-1 text-xs text-gray-500">{{ $primaryActivity }}</div>
                        <div class="mt-2 flex items-center justify-between gap-3">
                            <div class="text-sm font-bold text-[#0F2A52]">
                                {{ $primaryConsumed }} {{ $primaryUnit }}
                            </div>
                            <div class="text-xs font-semibold text-red-700">
                                Wastage {{ $primaryWastage }}
                            </div>
                        </div>

                        @if($hasNewItems && $materialConsumed->items->count() > 1)
                            <div class="mt-1 text-xs text-gray-500">
                                +{{ $materialConsumed->items->count() - 1 }} more material item{{ $materialConsumed->items->count() - 1 === 1 ? '' : 's' }}
                            </div>
                        @endif
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-gray-200 px-3 py-3">
                            <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Contractor</div>
                            <div class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $materialConsumed->contractor?->contractor_name ?? '-' }}
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 px-3 py-3">
                            <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Stock Issue</div>
                            <div class="mt-1 text-sm font-bold text-purple-700">
                                {{ formatQuantity($materialConsumed->total_issued_quantity) }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a href="{{ route('material-consumed.show', $materialConsumed) }}"
                           class="rounded-xl bg-[#0F2A52] px-4 py-3 text-center text-sm font-semibold text-white">
                            View
                        </a>

                        @if($materialConsumed->status === 'Draft' && $canEdit)
                            <a href="{{ route('material-consumed.edit', $materialConsumed) }}"
                               class="rounded-xl bg-amber-500 px-4 py-3 text-center text-sm font-semibold text-white">
                                Edit
                            </a>
                        @else
                            <div class="flex items-center justify-center rounded-xl bg-gray-100 px-4 py-3 text-center text-xs font-semibold text-gray-500">
                                {{ $materialConsumed->status }}
                            </div>
                        @endif
                    </div>

                    @if($materialConsumed->status === 'Submitted' && $canApprove)
                        <form method="POST"
                              action="{{ route('material-consumed.approve', $materialConsumed) }}"
                              class="mt-3">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    onclick="return confirm('Approve this material consumption entry?')"
                                    class="w-full rounded-xl bg-green-600 px-4 py-3 text-sm font-semibold text-white">
                                Approve Consumption
                            </button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white px-5 py-10 text-center shadow-sm">
                <div class="text-base font-semibold text-gray-700">No material consumption entries found</div>
                <p class="mt-2 text-sm text-gray-500">Adjust the filters or create the first consumption entry.</p>

                @if($canCreate)
                    <a href="{{ route('material-consumed.create') }}"
                       class="mt-4 inline-flex rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white">
                        + Add Material Consumed
                    </a>
                @endif
            </div>
        @endforelse

        @if($materialConsumeds->hasPages())
            <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 shadow-sm">
                {{ $materialConsumeds->links() }}
            </div>
        @endif
    </div>

    {{-- Desktop Material Consumption Register --}}
    <div class="hidden overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm lg:block">

        <div class="flex items-center justify-between border-b border-gray-200 bg-white px-4 py-3">
            <div>
                <h2 class="text-sm font-bold text-gray-800">Material Consumption Register</h2>
                <p class="mt-0.5 text-xs text-gray-500">
                    Compact consumption register. Open View for complete transaction details.
                </p>
            </div>

            <div class="text-xs font-medium text-gray-500">
                Showing {{ $materialConsumeds->firstItem() ?? 0 }}–{{ $materialConsumeds->lastItem() ?? 0 }}
                of {{ $materialConsumeds->total() }} entries
            </div>
        </div>

        <div class="max-h-[470px] overflow-auto overscroll-contain">
            <table class="w-full min-w-[940px] table-fixed text-[13px] text-gray-700">

                <colgroup>
                    <col class="w-[48px]">
                    <col class="w-[100px]">
                    <col class="w-[175px]">
                    <col class="w-[205px]">
                    <col class="w-[90px]">
                    <col class="w-[78px]">
                    <col class="w-[90px]">
                    <col class="w-[105px]">
                    <col class="w-[150px]">
                </colgroup>

                <thead class="sticky top-0 z-20 bg-slate-100 text-[11px] font-bold uppercase tracking-wide text-slate-600 shadow-[0_1px_0_rgba(0,0,0,0.08)]">
                    <tr>
                        <th class="px-3 py-3 text-center">#</th>
                        <th class="px-3 py-3 text-left">Date</th>
                        <th class="px-3 py-3 text-left">Project</th>
                        <th class="px-3 py-3 text-left">Material (Spec / Grade)</th>
                        <th class="px-3 py-3 text-right">Consumed</th>
                        <th class="px-3 py-3 text-left">Unit</th>
                        <th class="px-3 py-3 text-right">Wastage</th>
                        <th class="px-3 py-3 text-center">Status</th>
                        <th class="px-3 py-3 text-left">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($materialConsumeds as $index => $materialConsumed)
                        @php
                            $hasNewItems = $materialConsumed->items->isNotEmpty();

                            $statusClasses = match($materialConsumed->status) {
                                'Approved' => 'bg-green-100 text-green-800',
                                'Submitted' => 'bg-blue-100 text-blue-800',
                                'Rejected' => 'bg-red-100 text-red-800',
                                default => 'bg-yellow-100 text-yellow-800',
                            };

                            $registerItems = $hasNewItems
                                ? $materialConsumed->items
                                : collect([null]);

                            $entryNumber = ($materialConsumeds->firstItem() ?? 1) + $index;

                        @endphp

                        @foreach($registerItems as $itemIndex => $item)
                            @php
                                $isFirstItem = $itemIndex === 0;

                                $materialName = $hasNewItems
                                    ? ($item?->materialType?->material_type_name ?? '-')
                                    : ($materialConsumed->material?->material_name ?? '-');

                                $specificationName = $hasNewItems
                                    ? ($item?->specification?->specification_name ?? null)
                                    : null;

                                $gradeName = $hasNewItems
                                    ? ($item?->grade?->grade_name ?? null)
                                    : null;

                                $consumedQty = $hasNewItems
                                    ? $item?->quantity_consumed
                                    : $materialConsumed->quantity_consumed;

                                $wastageQty = $hasNewItems
                                    ? $item?->wastage_quantity
                                    : $materialConsumed->wastage_quantity;

                                $unitName = $hasNewItems
                                    ? ($item?->unit?->unit_name ?? '-')
                                    : ($materialConsumed->unit ?? '-');

                                $materialDetails = collect([
                                    $specificationName,
                                    $gradeName,
                                ])->filter()->implode(' / ');
                            @endphp

                            <tr class="hover:bg-slate-50 {{ ! $isFirstItem ? 'bg-gray-50/40' : '' }}">
                                <td class="px-3 py-2.5 text-center font-semibold text-gray-500">
                                    {{ $isFirstItem ? $entryNumber : '↳' }}
                                </td>

                                <td class="whitespace-nowrap px-3 py-2.5">
                                    @if($isFirstItem)
                                        <div class="font-medium text-gray-800">
                                            {{ $materialConsumed->consumed_date?->format('d/m/Y') ?? '-' }}
                                        </div>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>

                                <td class="px-3 py-2.5">
                                    @if($isFirstItem)
                                        <div class="truncate font-semibold text-gray-900"
                                             title="{{ $materialConsumed->project?->project_name ?? '-' }}">
                                            {{ $materialConsumed->project?->project_name ?? '-' }}
                                        </div>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>

                                <td class="px-3 py-2.5">
                                    <div class="truncate font-semibold text-gray-900"
                                         title="{{ $materialName }}{{ $materialDetails ? ' — ' . $materialDetails : '' }}">
                                        {{ $materialName }}
                                    </div>

                                    @if($materialDetails)
                                        <div class="mt-0.5 truncate text-[11px] text-gray-500"
                                             title="{{ $materialDetails }}">
                                            {{ $materialDetails }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-2 py-2.5 text-right font-semibold text-blue-700">
                                    {{ formatQuantity($consumedQty) }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2.5">
                                    {{ $unitName }}
                                </td>

                                <td class="px-2 py-2.5 text-right font-semibold {{ (float) $wastageQty > 0 ? 'text-red-700' : 'text-gray-600' }}">
                                    {{ formatQuantity($wastageQty) }}
                                </td>

                                <td class="px-3 py-2.5 text-center">
                                    @if($isFirstItem)
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusClasses }}">
                                            {{ $materialConsumed->status }}
                                        </span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>

                                <td class="px-3 py-2">
                                    @if($isFirstItem)
                                        <div class="flex items-center justify-start gap-1.5">
                                            <a href="{{ route('material-consumed.show', $materialConsumed) }}"
                                               class="inline-flex min-w-[46px] items-center justify-center rounded-md bg-slate-700 px-2.5 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800">
                                                View
                                            </a>

                                            @if($materialConsumed->status === 'Draft' && $canEdit)
                                                <a href="{{ route('material-consumed.edit', $materialConsumed) }}"
                                                   class="inline-flex min-w-[42px] items-center justify-center rounded-md bg-amber-500 px-2.5 py-1.5 text-[11px] font-semibold text-white hover:bg-amber-600">
                                                    Edit
                                                </a>
                                            @endif

                                            @if($materialConsumed->status === 'Submitted' && $canApprove)
                                                <form method="POST"
                                                      action="{{ route('material-consumed.approve', $materialConsumed) }}">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="submit"
                                                            onclick="return confirm('Approve this material consumption entry?')"
                                                            class="inline-flex min-w-[58px] items-center justify-center rounded-md bg-green-600 px-2.5 py-1.5 text-[11px] font-semibold text-white hover:bg-green-700">
                                                        Approve
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-center text-gray-300">—</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-14 text-center">
                                <div class="font-semibold text-gray-700">No material consumption entries found</div>
                                <div class="mt-1 text-sm text-gray-500">Adjust the filters or create the first consumption entry.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3">
            <div class="text-xs text-gray-500">
                {{ $materialConsumeds->total() }} matching entr{{ $materialConsumeds->total() === 1 ? 'y' : 'ies' }}
            </div>

            @if($materialConsumeds->hasPages())
                <div>
                    {{ $materialConsumeds->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
