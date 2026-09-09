@extends('layouts.app')

@section('content')
    @php
        $canManage = auth()->user()?->hasPermission(
            'construction_work_packages.manage'
        ) ?? false;

        $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';

        $labelClass = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500';

        $activeMappedIds = collect($mappedMaterialTypeIds ?? [])
            ->map(fn ($id) => (int) $id)
            ->values();

        $availableMaterialTypes = collect($materialTypes ?? [])
            ->reject(
                fn ($materialType) =>
                    $activeMappedIds->contains((int) $materialType->id)
            );

        $availableGroups = $availableMaterialTypes
            ->pluck('material_group')
            ->filter()
            ->unique()
            ->sort()
            ->values();
    @endphp

    <div
        class="space-y-4"
        x-data="{
            addPanelOpen: false,
            selectedMaterials: [],
            materialSearch: '',
            materialGroup: '',
            submitting: false,

            toggleMaterial(id) {
                id = Number(id);

                if (this.selectedMaterials.includes(id)) {
                    this.selectedMaterials =
                        this.selectedMaterials.filter(item => item !== id);
                } else {
                    this.selectedMaterials.push(id);
                }
            },

            isSelected(id) {
                return this.selectedMaterials.includes(Number(id));
            },

            clearSelection() {
                this.selectedMaterials = [];
            }
        }"
    >

        {{-- ============================================================
             PAGE HEADER
        ============================================================ --}}
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-lg font-bold text-gray-900">
                            Material ↔ Work Package Mapping
                        </h1>

                        <span class="rounded-md bg-blue-50 px-2 py-1 text-[11px] font-semibold text-blue-700">
                            Phase 2
                        </span>
                    </div>

                    <p class="mt-1 text-sm text-gray-500">
                        Define which Material Types are valid for each Construction Work Package.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a
                        href="{{ route('construction-work-packages.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                    >
                        Work Package Master
                    </a>

                    @if($selectedWorkPackage)
                        <a
                            href="{{ route('construction-work-packages.show', $selectedWorkPackage) }}"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                        >
                            View Work Package
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- ============================================================
             FLASH MESSAGES
        ============================================================ --}}
        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                <div class="text-sm font-semibold text-red-800">
                    Please correct the following:
                </div>

                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ============================================================
             SUMMARY BAR
        ============================================================ --}}
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">

            <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                    Total Mappings
                </div>
                <div class="mt-1 text-xl font-bold text-gray-900">
                    {{ number_format($summary['total_mappings'] ?? 0) }}
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                    Active
                </div>
                <div class="mt-1 text-xl font-bold text-gray-900">
                    {{ number_format($summary['active_mappings'] ?? 0) }}
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                    Preferred
                </div>
                <div class="mt-1 text-xl font-bold text-gray-900">
                    {{ number_format($summary['preferred_mappings'] ?? 0) }}
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                    Material Types
                </div>
                <div class="mt-1 text-xl font-bold text-gray-900">
                    {{ number_format($summary['mapped_material_types'] ?? 0) }}
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                    Package Mappings
                </div>
                <div class="mt-1 text-xl font-bold text-gray-900">
                    {{ number_format($summary['selected_total'] ?? 0) }}
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                    Package Active
                </div>
                <div class="mt-1 text-xl font-bold text-gray-900">
                    {{ number_format($summary['selected_active'] ?? 0) }}
                </div>
            </div>
        </div>

        {{-- ============================================================
             WORK PACKAGE SELECTOR
        ============================================================ --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">

            <form
                method="GET"
                action="{{ route('construction-work-package-materials.index') }}"
                class="grid grid-cols-1 gap-3 lg:grid-cols-12"
            >

                {{-- Construction Group --}}
                <div class="lg:col-span-4">
                    <label class="{{ $labelClass }}">
                        Construction Group
                    </label>

                    <select
                        name="root_id"
                        class="{{ $inputClass }}"
                        onchange="
                            this.form.elements['work_package_id'].value = '';
                            this.form.submit();
                        "
                    >
                        <option value="">
                            Select Construction Group
                        </option>

                        @foreach($rootPackages as $rootPackage)
                            <option
                                value="{{ $rootPackage->id }}"
                                {{ (string) $rootId === (string) $rootPackage->id ? 'selected' : '' }}
                            >
                                {{ $rootPackage->code }}
                                —
                                {{ $rootPackage->name }}
                                ({{ $rootPackage->active_children_count ?? 0 }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Work Package --}}
                <div class="lg:col-span-5">
                    <label class="{{ $labelClass }}">
                        Work Package
                    </label>

                    <select
                        name="work_package_id"
                        class="{{ $inputClass }}"
                        onchange="this.form.submit()"
                        {{ ! $rootId ? 'disabled' : '' }}
                    >
                        <option value="">
                            Select Work Package
                        </option>

                        @foreach($workPackages as $package)
                            <option
                                value="{{ $package->id }}"
                                {{ (string) $workPackageId === (string) $package->id ? 'selected' : '' }}
                            >
                                {{ $package->code }}
                                —
                                {{ $package->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2 lg:col-span-3">
                    <button
                        type="submit"
                        class="inline-flex h-[38px] flex-1 items-center justify-center rounded-lg bg-slate-800 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700"
                    >
                        Load Mapping
                    </button>

                    <a
                        href="{{ route('construction-work-package-materials.index') }}"
                        class="inline-flex h-[38px] items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- ============================================================
             NO PACKAGE SELECTED
        ============================================================ --}}
        @if(! $selectedWorkPackage)

            <div class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center shadow-sm">
                <div class="mx-auto max-w-xl">

                    <div class="text-base font-bold text-gray-900">
                        Select a Construction Work Package
                    </div>

                    <p class="mt-2 text-sm leading-6 text-gray-500">
                        Select a Construction Group and one of its child Work Packages
                        to view and maintain Material Type mappings.
                    </p>

                    <p class="mt-2 text-xs text-gray-400">
                        Materials are mapped only to selectable child Work Packages,
                        never directly to root Construction Groups.
                    </p>
                </div>
            </div>

        @else

            {{-- ========================================================
                 SELECTED WORK PACKAGE HEADER
            ======================================================== --}}
            <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

                    <div class="min-w-0">

                        <div class="flex flex-wrap items-center gap-2">

                            <span class="rounded-md bg-slate-100 px-2 py-1 text-xs font-bold text-slate-700">
                                {{ $selectedWorkPackage->code }}
                            </span>

                            <span class="text-base font-bold text-gray-900">
                                {{ $selectedWorkPackage->name }}
                            </span>

                            @if($selectedWorkPackage->is_active)
                                <span class="rounded-full bg-green-50 px-2 py-1 text-[11px] font-semibold text-green-700">
                                    Active
                                </span>
                            @else
                                <span class="rounded-full bg-red-50 px-2 py-1 text-[11px] font-semibold text-red-700">
                                    Inactive
                                </span>
                            @endif
                        </div>

                        <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">

                            <span>
                                Group:
                                <strong class="font-semibold text-gray-700">
                                    {{ $selectedWorkPackage->parent?->name ?? '—' }}
                                </strong>
                            </span>

                            <span>
                                Activity Division:
                                <strong class="font-semibold text-gray-700">
                                    {{ $selectedWorkPackage->activityDivision?->name ?? 'Not directly mapped' }}
                                </strong>
                            </span>

                            <span>
                                Active Materials:
                                <strong class="font-semibold text-gray-700">
                                    {{ number_format($summary['selected_active'] ?? 0) }}
                                </strong>
                            </span>
                        </div>
                    </div>

                    @if($canManage)
                        <button
                            type="button"
                            @click="addPanelOpen = ! addPanelOpen"
                            class="inline-flex items-center justify-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700"
                        >
                            <span x-text="addPanelOpen ? 'Close Material Picker' : '+ Add Materials'"></span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- ========================================================
                 ADD MATERIALS PANEL
            ======================================================== --}}
            @if($canManage)
                <div
                    x-cloak
                    x-show="addPanelOpen"
                    x-transition
                    class="rounded-xl border border-blue-200 bg-white shadow-sm"
                >
                    <form
                        method="POST"
                        action="{{ route('construction-work-package-materials.store') }}"
                        @submit="
                            if (selectedMaterials.length === 0) {
                                $event.preventDefault();
                                return;
                            }

                            submitting = true;
                        "
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="construction_work_package_id"
                            value="{{ $selectedWorkPackage->id }}"
                        >

                        {{-- Picker Header --}}
                        <div class="border-b border-gray-200 px-4 py-3">
                            <div class="flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">

                                <div>
                                    <div class="text-sm font-bold text-gray-900">
                                        Add Material Types
                                    </div>

                                    <p class="mt-1 text-xs text-gray-500">
                                        Select one or more materials relevant to
                                        {{ $selectedWorkPackage->name }}.
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 gap-2 md:grid-cols-2 xl:w-[560px]">

                                    <div>
                                        <label class="{{ $labelClass }}">
                                            Find Material
                                        </label>

                                        <input
                                            type="text"
                                            x-model="materialSearch"
                                            class="{{ $inputClass }}"
                                            placeholder="Search name, code or group..."
                                        >
                                    </div>

                                    <div>
                                        <label class="{{ $labelClass }}">
                                            Material Group
                                        </label>

                                        <select
                                            x-model="materialGroup"
                                            class="{{ $inputClass }}"
                                        >
                                            <option value="">
                                                All Material Groups
                                            </option>

                                            @foreach($availableGroups as $group)
                                                <option value="{{ $group }}">
                                                    {{ $group }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Selected Counter --}}
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 bg-gray-50 px-4 py-2">

                            <div class="text-xs text-gray-600">
                                Selected:
                                <span
                                    class="font-bold text-gray-900"
                                    x-text="selectedMaterials.length"
                                ></span>
                                material(s)
                            </div>

                            <button
                                type="button"
                                @click="clearSelection()"
                                x-show="selectedMaterials.length > 0"
                                class="text-xs font-semibold text-red-600 hover:text-red-700"
                            >
                                Clear Selection
                            </button>
                        </div>

                        {{-- Materials Picker Table --}}
                        <div class="max-h-[420px] overflow-auto">

                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="sticky top-0 z-10 bg-gray-50">
                                    <tr>
                                        <th class="w-12 px-3 py-2 text-center text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                            Add
                                        </th>

                                        <th class="px-3 py-2 text-left text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                            Material Group
                                        </th>

                                        <th class="px-3 py-2 text-left text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                            Material Type
                                        </th>

                                        <th class="px-3 py-2 text-left text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                            Code
                                        </th>

                                        <th class="px-3 py-2 text-left text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                            Unit
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100 bg-white">

                                    @forelse($availableMaterialTypes as $materialType)

                                        @php
                                            $searchText = strtolower(
                                                trim(
                                                    ($materialType->material_group ?? '') . ' ' .
                                                    ($materialType->material_type_name ?? '') . ' ' .
                                                    ($materialType->material_type_code ?? '')
                                                )
                                            );
                                        @endphp

                                        <tr
                                            x-show="
                                                (
                                                    materialSearch === ''
                                                    ||
                                                    @js($searchText).includes(
                                                        materialSearch.toLowerCase()
                                                    )
                                                )
                                                &&
                                                (
                                                    materialGroup === ''
                                                    ||
                                                    materialGroup === @js($materialType->material_group ?? '')
                                                )
                                            "
                                            class="cursor-pointer transition hover:bg-blue-50"
                                            :class="isSelected({{ $materialType->id }}) ? 'bg-blue-50' : ''"
                                            @click="toggleMaterial({{ $materialType->id }})"
                                        >
                                            <td class="px-3 py-2 text-center">
                                                <input
                                                    type="checkbox"
                                                    name="material_type_ids[]"
                                                    value="{{ $materialType->id }}"
                                                    :checked="isSelected({{ $materialType->id }})"
                                                    @click.stop="toggleMaterial({{ $materialType->id }})"
                                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                >
                                            </td>

                                            <td class="whitespace-nowrap px-3 py-2 text-xs font-medium text-gray-600">
                                                {{ $materialType->material_group ?? '—' }}
                                            </td>

                                            <td class="px-3 py-2">
                                                <div class="font-semibold text-gray-900">
                                                    {{ $materialType->material_type_name }}
                                                </div>
                                            </td>

                                            <td class="whitespace-nowrap px-3 py-2 font-mono text-xs text-gray-500">
                                                {{ $materialType->material_type_code ?: '—' }}
                                            </td>

                                            <td class="whitespace-nowrap px-3 py-2 text-xs text-gray-600">
                                                {{ $materialType->unit?->unit_name
                                                    ?? $materialType->unit?->name
                                                    ?? '—' }}
                                            </td>
                                        </tr>

                                    @empty

                                        <tr>
                                            <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">
                                                All active Material Types are already mapped to this Work Package.
                                            </td>
                                        </tr>

                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Picker Footer --}}
                        <div class="flex flex-col gap-3 border-t border-gray-200 bg-gray-50 px-4 py-3 md:flex-row md:items-center md:justify-between">

                            <label class="inline-flex items-center gap-2 text-xs font-medium text-gray-600">
                                <input
                                    type="checkbox"
                                    name="make_first_preferred"
                                    value="1"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >

                                Make first selected material preferred for this Work Package
                            </label>

                            <div class="flex items-center gap-2">

                                <button
                                    type="button"
                                    @click="
                                        addPanelOpen = false;
                                        clearSelection();
                                    "
                                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                                >
                                    Cancel
                                </button>

                                <button
                                    type="submit"
                                    :disabled="selectedMaterials.length === 0 || submitting"
                                    class="inline-flex items-center justify-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <span x-show="! submitting">
                                        Add Selected Materials
                                    </span>

                                    <span x-show="submitting" x-cloak>
                                        Saving...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif

            {{-- ========================================================
                 FILTER BAR
            ======================================================== --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">

                <form
                    method="GET"
                    action="{{ route('construction-work-package-materials.index') }}"
                    class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-12"
                >
                    <input
                        type="hidden"
                        name="root_id"
                        value="{{ $selectedWorkPackage->parent_id }}"
                    >

                    <input
                        type="hidden"
                        name="work_package_id"
                        value="{{ $selectedWorkPackage->id }}"
                    >

                    <div class="xl:col-span-5">
                        <label class="{{ $labelClass }}">
                            Search Mapping
                        </label>

                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            class="{{ $inputClass }}"
                            placeholder="Search material name, code or group..."
                        >
                    </div>

                    <div class="xl:col-span-3">
                        <label class="{{ $labelClass }}">
                            Material Group
                        </label>

                        <select
                            name="material_group"
                            class="{{ $inputClass }}"
                        >
                            <option value="">
                                All Material Groups
                            </option>

                            @foreach($materialGroups as $group)
                                <option
                                    value="{{ $group }}"
                                    {{ $materialGroup === $group ? 'selected' : '' }}
                                >
                                    {{ $group }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="xl:col-span-2">
                        <label class="{{ $labelClass }}">
                            Status
                        </label>

                        <select
                            name="status"
                            class="{{ $inputClass }}"
                        >
                            <option
                                value="active"
                                {{ $status === 'active' ? 'selected' : '' }}
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                {{ $status === 'inactive' ? 'selected' : '' }}
                            >
                                Inactive
                            </option>

                            <option
                                value="all"
                                {{ $status === 'all' ? 'selected' : '' }}
                            >
                                All
                            </option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2 xl:col-span-2">
                        <button
                            type="submit"
                            class="inline-flex h-[38px] flex-1 items-center justify-center rounded-lg bg-slate-800 px-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700"
                        >
                            Filter
                        </button>

                        <a
                            href="{{ route(
                                'construction-work-package-materials.index',
                                [
                                    'root_id' => $selectedWorkPackage->parent_id,
                                    'work_package_id' => $selectedWorkPackage->id,
                                ]
                            ) }}"
                            class="inline-flex h-[38px] items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                        >
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            {{-- ========================================================
                 MAPPINGS TABLE
            ======================================================== --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

                @if($mappings && $mappings->count())

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">

                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="w-14 px-3 py-2 text-center text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                        #
                                    </th>

                                    <th class="px-3 py-2 text-left text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                        Material Group
                                    </th>

                                    <th class="px-3 py-2 text-left text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                        Material Type
                                    </th>

                                    <th class="px-3 py-2 text-left text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                        Unit
                                    </th>

                                    <th class="w-28 px-3 py-2 text-center text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                        Preferred
                                    </th>

                                    <th class="w-32 px-3 py-2 text-center text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                        Order
                                    </th>

                                    <th class="w-24 px-3 py-2 text-center text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                        Status
                                    </th>

                                    @if($canManage)
                                        <th class="w-48 px-3 py-2 text-right text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                            Actions
                                        </th>
                                    @endif
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white">

                                @foreach($mappings as $mapping)

                                    <tr class="transition hover:bg-gray-50">

                                        <td class="px-3 py-2 text-center text-xs text-gray-500">
                                            {{ $mappings->firstItem() + $loop->index }}
                                        </td>

                                        <td class="whitespace-nowrap px-3 py-2">
                                            <span class="text-xs font-medium text-gray-600">
                                                {{ $mapping->materialType?->material_group ?? '—' }}
                                            </span>
                                        </td>

                                        <td class="px-3 py-2">
                                            <div class="font-semibold text-gray-900">
                                                {{ $mapping->materialType?->material_type_name ?? 'Material unavailable' }}
                                            </div>

                                            @if($mapping->materialType?->material_type_code)
                                                <div class="mt-0.5 font-mono text-[11px] text-gray-400">
                                                    {{ $mapping->materialType->material_type_code }}
                                                </div>
                                            @endif
                                        </td>

                                        <td class="whitespace-nowrap px-3 py-2 text-xs text-gray-600">
                                            {{ $mapping->materialType?->unit?->unit_name
                                                ?? $mapping->materialType?->unit?->name
                                                ?? '—' }}
                                        </td>

                                        <td class="px-3 py-2 text-center">

                                            @if($mapping->is_preferred)
                                                <span
                                                    title="Preferred Work Package for this Material Type"
                                                    class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-1 text-[11px] font-bold text-amber-700"
                                                >
                                                    ★ Preferred
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400">
                                                    —
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-3 py-2 text-center">

                                            @if($canManage)
                                                <form
                                                    method="POST"
                                                    action="{{ route(
                                                        'construction-work-package-materials.update',
                                                        $mapping
                                                    ) }}"
                                                    class="inline-flex items-center gap-1"
                                                >
                                                    @csrf
                                                    @method('PUT')

                                                    <input
                                                        type="number"
                                                        name="sort_order"
                                                        value="{{ $mapping->sort_order }}"
                                                        min="0"
                                                        max="999999"
                                                        class="w-16 rounded-md border border-gray-300 bg-white px-2 py-1 text-center text-xs text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                    >

                                                    <button
                                                        type="submit"
                                                        title="Save Order"
                                                        class="rounded-md border border-gray-300 bg-white px-2 py-1 text-[11px] font-semibold text-gray-600 transition hover:bg-gray-50"
                                                    >
                                                        Save
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-gray-600">
                                                    {{ $mapping->sort_order }}
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-3 py-2 text-center">

                                            @if($mapping->is_active)
                                                <span class="inline-flex rounded-full bg-green-50 px-2 py-1 text-[11px] font-semibold text-green-700">
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex rounded-full bg-red-50 px-2 py-1 text-[11px] font-semibold text-red-700">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>

                                        @if($canManage)
                                            <td class="px-3 py-2">

                                                <div class="flex flex-wrap items-center justify-end gap-1.5">

                                                    {{-- Preferred --}}
                                                    @if(
                                                        $mapping->is_active
                                                        && ! $mapping->is_preferred
                                                    )
                                                        <form
                                                            method="POST"
                                                            action="{{ route(
                                                                'construction-work-package-materials.make-preferred',
                                                                $mapping
                                                            ) }}"
                                                        >
                                                            @csrf
                                                            @method('PATCH')

                                                            <button
                                                                type="submit"
                                                                class="rounded-md border border-amber-200 bg-amber-50 px-2 py-1 text-[11px] font-semibold text-amber-700 transition hover:bg-amber-100"
                                                                onclick="return confirm(
                                                                    'Make this the preferred Work Package for {{ addslashes($mapping->materialType?->material_type_name ?? 'this material') }}?'
                                                                )"
                                                            >
                                                                Preferred
                                                            </button>
                                                        </form>
                                                    @endif

                                                    {{-- Status --}}
                                                    <form
                                                        method="POST"
                                                        action="{{ route(
                                                            'construction-work-package-materials.toggle-status',
                                                            $mapping
                                                        ) }}"
                                                    >
                                                        @csrf
                                                        @method('PATCH')

                                                        <button
                                                            type="submit"
                                                            class="
                                                                rounded-md border px-2 py-1 text-[11px] font-semibold transition
                                                                {{ $mapping->is_active
                                                                    ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100'
                                                                    : 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100'
                                                                }}
                                                            "
                                                            onclick="return confirm(
                                                                '{{ $mapping->is_active
                                                                    ? 'Deactivate this material mapping?'
                                                                    : 'Reactivate this material mapping?'
                                                                }}'
                                                            )"
                                                        >
                                                            {{ $mapping->is_active
                                                                ? 'Deactivate'
                                                                : 'Activate'
                                                            }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($mappings->hasPages())
                        <div class="border-t border-gray-200 px-4 py-3">
                            {{ $mappings->links() }}
                        </div>
                    @endif

                @else

                    <div class="px-6 py-12 text-center">

                        <div class="text-sm font-bold text-gray-900">
                            No material mappings found
                        </div>

                        <p class="mt-2 text-sm text-gray-500">
                            @if($search || $materialGroup || $status !== 'active')
                                No mappings match the current filters.
                            @else
                                This Work Package does not currently have active Material Types mapped to it.
                            @endif
                        </p>

                        @if($canManage)
                            <button
                                type="button"
                                @click="addPanelOpen = true"
                                class="mt-4 inline-flex items-center justify-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700"
                            >
                                + Add Materials
                            </button>
                        @endif
                    </div>

                @endif
            </div>

        @endif
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endsection