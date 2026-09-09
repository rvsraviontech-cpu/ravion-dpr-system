@extends('layouts.app')

@section('content')

@php
    $canManage = auth()->user()?->hasPermission('construction_work_packages.manage') ?? false;

    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';

    $hasFilters = $search !== ''
        || !empty($rootId)
        || !empty($activityDivisionId)
        || $status !== 'active';
@endphp

<div class="space-y-4">

    {{-- ============================================================
         PAGE HEADER
    ============================================================ --}}
    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">

        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                    Construction Work Packages
                </h1>

                <span class="rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-600">
                    {{ $summary['total'] }} Packages
                </span>
            </div>

            <p class="mt-1 max-w-3xl text-sm text-gray-500">
                Manage the construction work hierarchy used to classify materials by their intended site work.
            </p>
        </div>

        @if($canManage)
            <a
                href="{{ route('construction-work-packages.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-[#10212F] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1a3448] focus:outline-none focus:ring-2 focus:ring-[#10212F]/20"
            >
                <svg
                    class="mr-2 h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M12 5v14M5 12h14"/>
                </svg>

                Add Work Package
            </a>
        @endif

    </div>


    {{-- ============================================================
         ALERTS
    ============================================================ --}}
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif


    {{-- ============================================================
         COMPACT SUMMARY BAR
    ============================================================ --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="grid grid-cols-2 divide-x divide-y divide-gray-200 sm:grid-cols-4 sm:divide-y-0 lg:grid-cols-7">

            <div class="px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                    Total
                </p>
                <p class="mt-1 text-xl font-bold text-gray-900">
                    {{ $summary['total'] }}
                </p>
            </div>

            <div class="px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                    Groups
                </p>
                <p class="mt-1 text-xl font-bold text-gray-900">
                    {{ $summary['roots'] }}
                </p>
            </div>

            <div class="px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                    Work Packages
                </p>
                <p class="mt-1 text-xl font-bold text-gray-900">
                    {{ $summary['children'] }}
                </p>
            </div>

            <div class="px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                    Active
                </p>
                <p class="mt-1 text-xl font-bold text-green-700">
                    {{ $summary['active'] }}
                </p>
            </div>

            <div class="px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                    Inactive
                </p>
                <p class="mt-1 text-xl font-bold text-gray-500">
                    {{ $summary['inactive'] }}
                </p>
            </div>

            <div class="px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                    Material Mapped
                </p>
                <p class="mt-1 text-xl font-bold text-blue-700">
                    {{ $summary['mapped_children'] }}
                </p>
            </div>

            <div class="col-span-2 px-4 py-3 sm:col-span-1">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                    Awaiting Materials
                </p>
                <p class="mt-1 text-xl font-bold text-amber-700">
                    {{ $summary['empty_children'] }}
                </p>
            </div>

        </div>

    </div>


    {{-- ============================================================
         FILTERS
    ============================================================ --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">

        <form
            method="GET"
            action="{{ route('construction-work-packages.index') }}"
            class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-12"
        >

            {{-- Search --}}
            <div class="xl:col-span-4">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Search
                </label>

                <div class="relative">

                    <svg
                        class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.3-4.3"/>
                    </svg>

                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Code, package or description..."
                        class="{{ $inputClass }} pl-9"
                    >

                </div>
            </div>


            {{-- Root Group --}}
            <div class="xl:col-span-3">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Construction Group
                </label>

                <select
                    name="root_id"
                    class="{{ $inputClass }}"
                >
                    <option value="">All Groups</option>

                    @foreach($rootOptions as $rootOption)
                        <option
                            value="{{ $rootOption->id }}"
                            {{ (string) $rootId === (string) $rootOption->id ? 'selected' : '' }}
                        >
                            {{ $rootOption->code }} — {{ $rootOption->name }}
                        </option>
                    @endforeach
                </select>
            </div>


            {{-- Activity Division --}}
            <div class="xl:col-span-2">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Activity Division
                </label>

                <select
                    name="activity_division_id"
                    class="{{ $inputClass }}"
                >
                    <option value="">All Divisions</option>

                    @foreach($activityDivisions as $division)
                        <option
                            value="{{ $division->id }}"
                            {{ (string) $activityDivisionId === (string) $division->id ? 'selected' : '' }}
                        >
                            {{ $division->name }}
                        </option>
                    @endforeach
                </select>
            </div>


            {{-- Status --}}
            <div class="xl:col-span-1">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Status
                </label>

                <select
                    name="status"
                    class="{{ $inputClass }}"
                >
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>
                        Active
                    </option>

                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>
                        Inactive
                    </option>

                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>
                        All
                    </option>
                </select>
            </div>


            {{-- Actions --}}
            <div class="flex items-end gap-2 xl:col-span-2">

                <button
                    type="submit"
                    class="inline-flex flex-1 items-center justify-center rounded-lg bg-[#10212F] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1a3448]"
                >
                    Filter
                </button>

                @if($hasFilters)
                    <a
                        href="{{ route('construction-work-packages.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                        title="Clear filters"
                    >
                        Clear
                    </a>
                @endif

            </div>

        </form>

    </div>


    {{-- ============================================================
         HIERARCHICAL TABLE
    ============================================================ --}}
    <div
        class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
        x-data="{ openGroups: {} }"
    >

        {{-- Table Heading --}}
        <div class="flex flex-col gap-2 border-b border-gray-200 bg-gray-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h2 class="text-sm font-bold text-gray-800">
                    Work Package Hierarchy
                </h2>

                <p class="mt-0.5 text-xs text-gray-500">
                    Construction groups contain the selectable Work Packages used during material receipt and consumption.
                </p>
            </div>

            <div class="flex items-center gap-3 text-xs text-gray-500">
                <span>
                    {{ $roots->count() }} groups displayed
                </span>
            </div>

        </div>


        <div class="overflow-x-auto">

            <table class="min-w-full table-fixed text-sm">

                <thead class="border-b border-gray-200 bg-white text-[11px] font-semibold uppercase tracking-wide text-gray-500">

                    <tr>
                        <th class="w-10 px-3 py-2.5 text-center"></th>

                        <th class="w-32 px-3 py-2.5 text-left">
                            Code
                        </th>

                        <th class="px-3 py-2.5 text-left">
                            Construction Group / Work Package
                        </th>

                        <th class="w-64 px-3 py-2.5 text-left">
                            Activity Division
                        </th>

                        <th class="w-28 px-3 py-2.5 text-center">
                            Materials
                        </th>

                        <th class="w-24 px-3 py-2.5 text-center">
                            Status
                        </th>

                        <th class="w-32 px-3 py-2.5 text-center">
                            Actions
                        </th>
                    </tr>

                </thead>


                @forelse($roots as $root)

                    @php
                        $groupKey = 'group_' . $root->id;

                        $forceOpen = $hasFilters;

                        $childCount = $root->children->count();
                    @endphp

                    <tbody
                        class="border-b border-gray-200"
                        x-data="{ forceOpen: @js($forceOpen) }"
                    >

                        {{-- ==========================================
                             ROOT GROUP ROW
                        =========================================== --}}
                        <tr class="bg-slate-50">

                            <td class="px-3 py-2.5 text-center">

                                <button
                                    type="button"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-md text-gray-500 transition hover:bg-white hover:text-gray-900"
                                    @click="openGroups['{{ $groupKey }}'] = !(openGroups['{{ $groupKey }}'] ?? forceOpen)"
                                    title="Expand / Collapse"
                                >
                                    <svg
                                        class="h-4 w-4 transition-transform"
                                        :class="{ 'rotate-90': (openGroups['{{ $groupKey }}'] ?? forceOpen) }"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path d="m9 18 6-6-6-6"/>
                                    </svg>
                                </button>

                            </td>

                            <td class="px-3 py-2.5">
                                <span class="font-mono text-xs font-bold text-[#10212F]">
                                    {{ $root->code }}
                                </span>
                            </td>

                            <td class="px-3 py-2.5">

                                <div class="flex flex-wrap items-center gap-2">

                                    <a
                                        href="{{ route('construction-work-packages.show', $root) }}"
                                        class="font-bold text-gray-900 hover:text-blue-700"
                                    >
                                        {{ $root->name }}
                                    </a>

                                    <span class="rounded-full border border-gray-200 bg-white px-2 py-0.5 text-[11px] font-semibold text-gray-500">
                                        {{ $root->children_count }} packages
                                    </span>

                                </div>

                            </td>

                            <td class="px-3 py-2.5 text-xs text-gray-600">
                                {{ $root->activityDivision?->name ?? '—' }}
                            </td>

                            <td class="px-3 py-2.5 text-center">

                                @if($root->material_types_count > 0)
                                    <span class="inline-flex min-w-8 justify-center rounded-full bg-blue-50 px-2 py-1 text-xs font-bold text-blue-700">
                                        {{ $root->material_types_count }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">
                                        —
                                    </span>
                                @endif

                            </td>

                            <td class="px-3 py-2.5 text-center">

                                @if($root->is_active)
                                    <span class="inline-flex rounded-full bg-green-50 px-2 py-1 text-[11px] font-semibold text-green-700">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-[11px] font-semibold text-gray-600">
                                        Inactive
                                    </span>
                                @endif

                            </td>

                            <td class="px-3 py-2.5">

                                <div class="flex items-center justify-center gap-1">

                                    <a
                                        href="{{ route('construction-work-packages.show', $root) }}"
                                        class="rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50"
                                    >
                                        View
                                    </a>

                                    @if($canManage)
                                        <a
                                            href="{{ route('construction-work-packages.create', ['parent_id' => $root->id]) }}"
                                            class="rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-50"
                                            title="Add Work Package under {{ $root->name }}"
                                        >
                                            + Add
                                        </a>

                                        <a
                                            href="{{ route('construction-work-packages.edit', $root) }}"
                                            class="rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50"
                                        >
                                            Edit
                                        </a>
                                    @endif

                                </div>

                            </td>

                        </tr>


                        {{-- ==========================================
                             CHILD WORK PACKAGE ROWS
                        =========================================== --}}
                        @forelse($root->children as $child)

                            <tr
                                x-show="openGroups['{{ $groupKey }}'] ?? forceOpen"
                                x-cloak
                                class="border-t border-gray-100 bg-white transition hover:bg-blue-50/30"
                            >

                                <td class="px-3 py-2"></td>

                                <td class="px-3 py-2">
                                    <span class="font-mono text-xs font-semibold text-gray-600">
                                        {{ $child->code }}
                                    </span>
                                </td>

                                <td class="px-3 py-2">

                                    <div class="flex items-center">

                                        <span class="mr-2 text-gray-300">
                                            └
                                        </span>

                                        <a
                                            href="{{ route('construction-work-packages.show', $child) }}"
                                            class="font-medium text-gray-800 hover:text-blue-700"
                                        >
                                            {{ $child->name }}
                                        </a>

                                    </div>

                                </td>

                                <td class="px-3 py-2 text-xs text-gray-600">
                                    {{ $child->activityDivision?->name ?? $root->activityDivision?->name ?? '—' }}
                                </td>

                                <td class="px-3 py-2 text-center">

                                    @if($child->active_material_types_count > 0)

                                        <span
                                            class="inline-flex min-w-8 justify-center rounded-full bg-blue-50 px-2 py-1 text-xs font-bold text-blue-700"
                                            title="{{ $child->active_material_types_count }} active mapped Material Types"
                                        >
                                            {{ $child->active_material_types_count }}
                                        </span>

                                    @else

                                        <span
                                            class="inline-flex rounded-full bg-amber-50 px-2 py-1 text-[11px] font-semibold text-amber-700"
                                            title="No Material Types currently mapped"
                                        >
                                            Empty
                                        </span>

                                    @endif

                                </td>

                                <td class="px-3 py-2 text-center">

                                    @if($child->is_active)
                                        <span class="inline-flex rounded-full bg-green-50 px-2 py-1 text-[11px] font-semibold text-green-700">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-[11px] font-semibold text-gray-600">
                                            Inactive
                                        </span>
                                    @endif

                                </td>

                                <td class="px-3 py-2">

                                    <div class="flex items-center justify-center gap-1">

                                        <a
                                            href="{{ route('construction-work-packages.show', $child) }}"
                                            class="rounded-md px-2 py-1 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
                                        >
                                            View
                                        </a>

                                        @if($canManage)
                                            <a
                                                href="{{ route('construction-work-packages.edit', $child) }}"
                                                class="rounded-md px-2 py-1 text-xs font-semibold text-blue-700 transition hover:bg-blue-50"
                                            >
                                                Edit
                                            </a>
                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr
                                x-show="openGroups['{{ $groupKey }}'] ?? forceOpen"
                                x-cloak
                            >
                                <td colspan="7" class="bg-white px-4 py-6 text-center text-sm text-gray-400">
                                    No Work Packages match the current filters in this group.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                @empty

                    <tbody>
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">

                                <div class="mx-auto max-w-md">

                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                                        <svg
                                            class="h-6 w-6 text-gray-400"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.7"
                                        >
                                            <path d="M3 21h18"/>
                                            <path d="M6 21V7l6-4 6 4v14"/>
                                            <path d="M9 9h6M9 13h6M9 17h6"/>
                                        </svg>
                                    </div>

                                    <h3 class="mt-4 font-semibold text-gray-800">
                                        No Work Packages found
                                    </h3>

                                    <p class="mt-1 text-sm text-gray-500">
                                        Try changing the search or filter criteria.
                                    </p>

                                    @if($hasFilters)
                                        <a
                                            href="{{ route('construction-work-packages.index') }}"
                                            class="mt-4 inline-flex rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                                        >
                                            Clear Filters
                                        </a>
                                    @endif

                                </div>

                            </td>
                        </tr>
                    </tbody>

                @endforelse

            </table>

        </div>

    </div>

</div>

@endsection