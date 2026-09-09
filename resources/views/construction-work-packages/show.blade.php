@extends('layouts.app')

@section('content')

@php
    $canManage = auth()->user()?->hasPermission('construction_work_packages.manage') ?? false;

    $isRoot = is_null($constructionWorkPackage->parent_id);

    $materialCount = $constructionWorkPackage->materialTypes->count();

    $activeMaterialCount = $constructionWorkPackage->activeMaterialTypes->count();
@endphp

<div class="space-y-4">

    {{-- ============================================================
         PAGE HEADER
    ============================================================ --}}
    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">

        <div class="flex items-start gap-2">

            <a
                href="{{ route('construction-work-packages.index') }}"
                class="mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:bg-gray-50 hover:text-gray-900"
                title="Back to Work Packages"
            >
                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="m15 18-6-6 6-6"/>
                </svg>
            </a>

            <div>

                <div class="flex flex-wrap items-center gap-2">

                    <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                        {{ $constructionWorkPackage->name }}
                    </h1>

                    <span class="rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 font-mono text-xs font-bold text-[#10212F]">
                        {{ $constructionWorkPackage->code }}
                    </span>

                    @if($isRoot)
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                            Construction Group
                        </span>
                    @else
                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                            Work Package
                        </span>
                    @endif

                    @if($constructionWorkPackage->is_active)
                        <span class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">
                            Active
                        </span>
                    @else
                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                            Inactive
                        </span>
                    @endif

                </div>

                <p class="mt-1 text-sm text-gray-500">
                    Construction work classification and material mapping details.
                </p>

            </div>

        </div>


        @if($canManage)

            <div class="flex flex-wrap items-center gap-2">

                @if($isRoot)
                    <a
                        href="{{ route('construction-work-packages.create', ['parent_id' => $constructionWorkPackage->id]) }}"
                        class="inline-flex items-center justify-center rounded-lg border border-blue-200 bg-white px-4 py-2.5 text-sm font-semibold text-blue-700 transition hover:bg-blue-50"
                    >
                        + Add Work Package
                    </a>
                @endif

                <a
                    href="{{ route('construction-work-packages.edit', $constructionWorkPackage) }}"
                    class="inline-flex items-center justify-center rounded-lg bg-[#10212F] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1a3448]"
                >
                    Edit
                </a>

            </div>

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
         COMPACT SUMMARY
    ============================================================ --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="grid grid-cols-2 divide-x divide-y divide-gray-200 sm:grid-cols-4 sm:divide-y-0">

            <div class="px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                    Type
                </p>

                <p class="mt-1 text-sm font-bold text-gray-900">
                    {{ $isRoot ? 'Construction Group' : 'Work Package' }}
                </p>
            </div>


            <div class="px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                    Sort Order
                </p>

                <p class="mt-1 text-sm font-bold text-gray-900">
                    {{ $constructionWorkPackage->sort_order }}
                </p>
            </div>


            <div class="px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                    @if($isRoot)
                        Child Packages
                    @else
                        Material Mappings
                    @endif
                </p>

                <p class="mt-1 text-sm font-bold text-gray-900">
                    @if($isRoot)
                        {{ $constructionWorkPackage->children->count() }}
                    @else
                        {{ $materialCount }}
                    @endif
                </p>
            </div>


            <div class="px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                    Status
                </p>

                <p class="mt-1 text-sm font-bold {{ $constructionWorkPackage->is_active ? 'text-green-700' : 'text-gray-500' }}">
                    {{ $constructionWorkPackage->is_active ? 'Active' : 'Inactive' }}
                </p>
            </div>

        </div>

    </div>


    {{-- ============================================================
         BASIC INFORMATION
    ============================================================ --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">

            <h2 class="text-sm font-bold text-gray-900">
                Classification Details
            </h2>

        </div>


        <div class="grid grid-cols-1 divide-y divide-gray-100 md:grid-cols-2 md:divide-x md:divide-y-0">

            <div class="space-y-4 p-5">

                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                        Code
                    </p>

                    <p class="mt-1 font-mono text-sm font-bold text-gray-900">
                        {{ $constructionWorkPackage->code }}
                    </p>
                </div>


                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                        Name
                    </p>

                    <p class="mt-1 text-sm font-semibold text-gray-900">
                        {{ $constructionWorkPackage->name }}
                    </p>
                </div>


                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                        Parent Construction Group
                    </p>

                    @if($constructionWorkPackage->parent)

                        <a
                            href="{{ route('construction-work-packages.show', $constructionWorkPackage->parent) }}"
                            class="mt-1 inline-flex text-sm font-semibold text-blue-700 hover:text-blue-900"
                        >
                            {{ $constructionWorkPackage->parent->code }}
                            —
                            {{ $constructionWorkPackage->parent->name }}
                        </a>

                    @else

                        <p class="mt-1 text-sm text-gray-500">
                            Top-level Construction Group
                        </p>

                    @endif
                </div>

            </div>


            <div class="space-y-4 p-5">

                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                        Related Activity Division
                    </p>

                    @if($constructionWorkPackage->activityDivision)

                        <div class="mt-1">

                            <p class="text-sm font-semibold text-gray-900">
                                {{ $constructionWorkPackage->activityDivision->name }}
                            </p>

                            @if($constructionWorkPackage->activityDivision->code)
                                <p class="mt-0.5 font-mono text-xs text-gray-500">
                                    {{ $constructionWorkPackage->activityDivision->code }}
                                </p>
                            @endif

                        </div>

                    @else

                        <p class="mt-1 text-sm text-gray-500">
                            No direct Activity Division mapping
                        </p>

                    @endif
                </div>


                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                        Sort Order
                    </p>

                    <p class="mt-1 text-sm font-semibold text-gray-900">
                        {{ $constructionWorkPackage->sort_order }}
                    </p>
                </div>


                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                        Status
                    </p>

                    <div class="mt-1">
                        @if($constructionWorkPackage->is_active)
                            <span class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">
                                Active
                            </span>
                        @else
                            <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                Inactive
                            </span>
                        @endif
                    </div>

                </div>

            </div>

        </div>


        @if($constructionWorkPackage->remarks)

            <div class="border-t border-gray-200 px-5 py-4">

                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                    Remarks
                </p>

                <p class="mt-1 whitespace-pre-line text-sm leading-6 text-gray-700">
                    {{ $constructionWorkPackage->remarks }}
                </p>

            </div>

        @endif

    </div>


    {{-- ============================================================
         ROOT: CHILD WORK PACKAGES
    ============================================================ --}}
    @if($isRoot)

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="flex flex-col gap-2 border-b border-gray-200 bg-gray-50 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="text-sm font-bold text-gray-900">
                        Work Packages
                    </h2>

                    <p class="mt-0.5 text-xs text-gray-500">
                        Selectable packages contained in this Construction Group.
                    </p>
                </div>

                <span class="text-xs font-semibold text-gray-500">
                    {{ $constructionWorkPackage->children->count() }} packages
                </span>

            </div>


            @if($constructionWorkPackage->children->isNotEmpty())

                <div class="overflow-x-auto">

                    <table class="min-w-full text-sm">

                        <thead class="border-b border-gray-200 bg-white text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="w-36 px-4 py-2.5 text-left">
                                    Code
                                </th>

                                <th class="px-4 py-2.5 text-left">
                                    Work Package
                                </th>

                                <th class="w-64 px-4 py-2.5 text-left">
                                    Activity Division
                                </th>

                                <th class="w-28 px-4 py-2.5 text-center">
                                    Materials
                                </th>

                                <th class="w-24 px-4 py-2.5 text-center">
                                    Status
                                </th>

                                <th class="w-20 px-4 py-2.5 text-center">
                                    Action
                                </th>
                            </tr>
                        </thead>


                        <tbody class="divide-y divide-gray-100">

                            @foreach($constructionWorkPackage->children as $child)

                                <tr class="hover:bg-gray-50">

                                    <td class="px-4 py-2.5 font-mono text-xs font-semibold text-gray-600">
                                        {{ $child->code }}
                                    </td>

                                    <td class="px-4 py-2.5 font-medium text-gray-800">
                                        {{ $child->name }}
                                    </td>

                                    <td class="px-4 py-2.5 text-xs text-gray-600">
                                        {{ $child->activityDivision?->name ?? $constructionWorkPackage->activityDivision?->name ?? '—' }}
                                    </td>

                                    <td class="px-4 py-2.5 text-center">

                                        @if($child->active_material_types_count ?? 0)
                                            <span class="inline-flex min-w-8 justify-center rounded-full bg-blue-50 px-2 py-1 text-xs font-bold text-blue-700">
                                                {{ $child->active_material_types_count }}
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-amber-50 px-2 py-1 text-[11px] font-semibold text-amber-700">
                                                Empty
                                            </span>
                                        @endif

                                    </td>

                                    <td class="px-4 py-2.5 text-center">

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

                                    <td class="px-4 py-2.5 text-center">

                                        <a
                                            href="{{ route('construction-work-packages.show', $child) }}"
                                            class="text-xs font-semibold text-blue-700 hover:text-blue-900"
                                        >
                                            View
                                        </a>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="px-5 py-10 text-center text-sm text-gray-500">
                    No Work Packages have been created under this group.
                </div>

            @endif

        </div>

    @else

        {{-- ========================================================
             CHILD: MATERIAL MAPPINGS
        ========================================================= --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="flex flex-col gap-2 border-b border-gray-200 bg-gray-50 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="text-sm font-bold text-gray-900">
                        Mapped Material Types
                    </h2>

                    <p class="mt-0.5 text-xs text-gray-500">
                        Materials currently associated with this Work Package.
                    </p>
                </div>

                <div class="flex items-center gap-3 text-xs text-gray-500">

                    <span>
                        {{ $activeMaterialCount }} active
                    </span>

                    <span>
                        {{ $materialCount }} total
                    </span>

                </div>

            </div>


            @if($constructionWorkPackage->materialTypes->isNotEmpty())

                <div class="overflow-x-auto">

                    <table class="min-w-full text-sm">

                        <thead class="border-b border-gray-200 bg-white text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-2.5 text-left">
                                    Material Group
                                </th>

                                <th class="px-4 py-2.5 text-left">
                                    Material Type
                                </th>

                                <th class="w-32 px-4 py-2.5 text-center">
                                    Unit
                                </th>

                                <th class="w-28 px-4 py-2.5 text-center">
                                    Preferred
                                </th>

                                <th class="w-24 px-4 py-2.5 text-center">
                                    Status
                                </th>
                            </tr>
                        </thead>


                        <tbody class="divide-y divide-gray-100">

                            @foreach($constructionWorkPackage->materialTypes as $materialType)

                                <tr class="hover:bg-gray-50">

                                    <td class="px-4 py-2.5 text-xs font-semibold text-gray-600">
                                        {{ $materialType->material_group ?: '—' }}
                                    </td>

                                    <td class="px-4 py-2.5 font-medium text-gray-800">
                                        {{ $materialType->material_type_name }}
                                    </td>

                                    <td class="px-4 py-2.5 text-center text-xs text-gray-600">
                                        {{ $materialType->unit?->unit_name ?? $materialType->unit?->name ?? '—' }}
                                    </td>

                                    <td class="px-4 py-2.5 text-center">

                                        @if($materialType->pivot?->is_preferred)
                                            <span class="inline-flex rounded-full bg-blue-50 px-2 py-1 text-[11px] font-semibold text-blue-700">
                                                Preferred
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">
                                                —
                                            </span>
                                        @endif

                                    </td>

                                    <td class="px-4 py-2.5 text-center">

                                        @if($materialType->pivot?->is_active)
                                            <span class="inline-flex rounded-full bg-green-50 px-2 py-1 text-[11px] font-semibold text-green-700">
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-[11px] font-semibold text-gray-600">
                                                Inactive
                                            </span>
                                        @endif

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="px-5 py-10 text-center">

                    <div class="mx-auto max-w-md">

                        <p class="text-sm font-semibold text-gray-700">
                            No Material Types mapped
                        </p>

                        <p class="mt-1 text-xs leading-5 text-gray-500">
                            This is valid. Material mappings will be managed separately in the Material ↔ Work Package Mapping phase.
                        </p>

                    </div>

                </div>

            @endif

        </div>

    @endif


    {{-- ============================================================
         ADMINISTRATIVE ACTIONS
    ============================================================ --}}
    @if($canManage)

        <div class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">

            <div>
                <p class="text-sm font-bold text-gray-800">
                    Administrative Actions
                </p>

                <p class="mt-0.5 text-xs text-gray-500">
                    Delete only unused records. Established Work Packages should normally be deactivated instead.
                </p>
            </div>


            <form
                method="POST"
                action="{{ route('construction-work-packages.destroy', $constructionWorkPackage) }}"
                onsubmit="return confirm('Delete this Construction Work Package? This action cannot be undone.');"
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50"
                >
                    Delete
                </button>

            </form>

        </div>

    @endif

</div>

@endsection