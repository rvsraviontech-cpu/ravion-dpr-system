@extends('layouts.app')

@section('content')
<div
    class="mx-auto max-w-[1800px] px-4 py-5 sm:px-6 lg:px-8"
    x-data="materialCatalogueMapping()"
>
    {{-- Page Header --}}
    <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-900">
                Material Catalogue Mapping
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Search catalogue products, map them to an existing Material Type,
                or create a new Material Type.
            </p>
        </div>

        <div class="flex items-center gap-2 text-xs text-gray-500">
            <span class="rounded-md border border-gray-200 bg-white px-3 py-2">
                Catalogue:
                <strong class="ml-1 text-gray-900">
                    {{ number_format($summary['total']) }}
                </strong>
            </span>

            <span class="rounded-md border border-gray-200 bg-white px-3 py-2">
                Material Types:
                <strong class="ml-1 text-gray-900">
                    {{ number_format($summary['material_types']) }}
                </strong>
            </span>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if (session('info'))
        <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
            {{ session('info') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
            <div class="text-sm font-semibold text-red-800">
                Please correct the following:
            </div>

            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Summary --}}
    <div class="mb-4 grid grid-cols-2 gap-3 md:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white px-4 py-3">
            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                Catalogue Rows
            </div>

            <div class="mt-1 text-xl font-semibold text-gray-900">
                {{ number_format($summary['total']) }}
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white px-4 py-3">
            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                Mapped
            </div>

            <div class="mt-1 text-xl font-semibold text-green-700">
                {{ number_format($summary['mapped']) }}
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white px-4 py-3">
            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                Unmapped
            </div>

            <div class="mt-1 text-xl font-semibold text-amber-700">
                {{ number_format($summary['unmapped']) }}
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white px-4 py-3">
            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                Material Types
            </div>

            <div class="mt-1 text-xl font-semibold text-[#10212F]">
                {{ number_format($summary['material_types']) }}
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form
        method="GET"
        action="{{ route('material-catalog-mapping.index') }}"
        class="mb-4 rounded-lg border border-gray-200 bg-white p-3"
    >
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <label class="mb-1 block text-xs font-medium text-gray-600">
                    Search Product
                </label>

                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search cement, screw, PVC, diesel..."
                    class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[#10212F] focus:ring-[#10212F]"
                >
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-medium text-gray-600">
                    Category
                </label>

                <select
                    name="category_id"
                    class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[#10212F] focus:ring-[#10212F]"
                >
                    <option value="">All Categories</option>

                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected((string) $categoryId === (string) $category->id)
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-medium text-gray-600">
                    Subcategory
                </label>

                <select
                    name="subcategory_id"
                    class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[#10212F] focus:ring-[#10212F]"
                >
                    <option value="">All Subcategories</option>

                    @foreach ($subcategories as $subcategory)
                        <option
                            value="{{ $subcategory->id }}"
                            @selected((string) $subcategoryId === (string) $subcategory->id)
                        >
                            {{ $subcategory->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-1">
                <label class="mb-1 block text-xs font-medium text-gray-600">
                    Status
                </label>

                <select
                    name="mapping_status"
                    class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[#10212F] focus:ring-[#10212F]"
                >
                    <option value="all" @selected($mappingStatus === 'all')>
                        All
                    </option>

                    <option value="unmapped" @selected($mappingStatus === 'unmapped')>
                        Unmapped
                    </option>

                    <option value="mapped" @selected($mappingStatus === 'mapped')>
                        Mapped
                    </option>
                </select>
            </div>

            <div class="flex items-end gap-2 lg:col-span-2">
                <button
                    type="submit"
                    class="inline-flex h-[38px] flex-1 items-center justify-center rounded-md bg-[#10212F] px-4 text-sm font-medium text-white hover:bg-[#1b3447]"
                >
                    Filter
                </button>

                <a
                    href="{{ route('material-catalog-mapping.index') }}"
                    class="inline-flex h-[38px] items-center justify-center rounded-md border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Clear
                </a>
            </div>
        </div>
    </form>

    {{-- Catalogue Table --}}
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-[1200px] w-full border-collapse text-left">
                <thead class="bg-gray-50">
                    <tr class="border-b border-gray-200">
                        <th class="w-14 px-3 py-2.5 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            #
                        </th>

                        <th class="min-w-[260px] px-3 py-2.5 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Catalogue Product
                        </th>

                        <th class="min-w-[190px] px-3 py-2.5 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Category
                        </th>

                        <th class="min-w-[220px] px-3 py-2.5 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Subcategory
                        </th>

                        <th class="min-w-[260px] px-3 py-2.5 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Material Type
                        </th>

                        <th class="w-[150px] px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($items as $item)
                        <tr class="hover:bg-gray-50/80">
                            <td class="px-3 py-2.5 align-top text-sm text-gray-500">
                                {{ $items->firstItem() + $loop->index }}
                            </td>

                            <td class="px-3 py-2.5 align-top">
                                <div class="font-medium text-gray-900">
                                    {{ $item->source_item_name }}
                                </div>

                                @if ($item->variant_text)
                                    <div class="mt-0.5 text-xs text-gray-500">
                                        {{ $item->variant_text }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-3 py-2.5 align-top text-sm text-gray-700">
                                {{ $item->category?->name ?? '—' }}
                            </td>

                            <td class="px-3 py-2.5 align-top text-sm text-gray-700">
                                {{ $item->subcategory?->name ?? '—' }}
                            </td>

                            <td class="px-3 py-2.5 align-top">
                                @if ($item->matchedMaterialType)
                                    <div class="flex items-start gap-2">
                                        <span class="mt-0.5 inline-flex rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-semibold text-green-700 ring-1 ring-inset ring-green-200">
                                            Mapped
                                        </span>

                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $item->matchedMaterialType->material_type_name }}
                                            </div>

                                            <div class="mt-0.5 text-xs text-gray-500">
                                                {{ $item->matchedMaterialType->material_type_code }}

                                                @if ($item->matchedMaterialType->unit)
                                                    ·
                                                    {{ $item->matchedMaterialType->unit->symbol ?: $item->matchedMaterialType->unit->unit_code }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">
                                        Not Mapped
                                    </span>
                                @endif
                            </td>

                            <td class="px-3 py-2.5 align-top text-right">
                                @if ($item->matchedMaterialType)
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            @click="openMapping({{ $item->id }})"
                                            class="rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                                        >
                                            Change
                                        </button>

                                        <form
                                            method="POST"
                                            action="{{ route('material-catalog-mapping.unmap', $item) }}"
                                            onsubmit="return confirm('Remove this catalogue mapping? The Material Type itself will not be deleted.');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="rounded-md border border-red-200 bg-white px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50"
                                            >
                                                Unmap
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <button
                                        type="button"
                                        @click="openMapping({{ $item->id }})"
                                        class="rounded-md bg-[#10212F] px-3 py-1.5 text-xs font-medium text-white hover:bg-[#1b3447]"
                                    >
                                        Map / Create
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="6"
                                class="px-6 py-12 text-center"
                            >
                                <div class="text-sm font-medium text-gray-700">
                                    No catalogue products found.
                                </div>

                                <div class="mt-1 text-xs text-gray-500">
                                    Try changing the search or filters.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    {{-- Mapping Modal --}}
    <div
        x-show="modalOpen"
        x-cloak
        class="fixed inset-0 z-[100] overflow-y-auto"
        aria-modal="true"
        role="dialog"
    >
        <div class="flex min-h-screen items-center justify-center px-4 py-8">
            <div
                x-show="modalOpen"
                x-transition.opacity
                class="fixed inset-0 bg-gray-900/50"
                @click="closeModal()"
            ></div>

            <div
                x-show="modalOpen"
                x-transition
                class="relative z-10 w-full max-w-3xl overflow-hidden rounded-xl bg-white shadow-2xl"
            >
                {{-- Modal Header --}}
                <div class="flex items-start justify-between border-b border-gray-200 px-5 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            Map Catalogue Product
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Map to an existing Material Type or create a new one.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="closeModal()"
                        class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700"
                    >
                        <span class="text-xl leading-none">&times;</span>
                    </button>
                </div>

                {{-- Loading --}}
                <div
                    x-show="loading"
                    class="px-5 py-12 text-center text-sm text-gray-500"
                >
                    Loading catalogue product...
                </div>

                <div x-show="!loading && item">
                    {{-- Product Information --}}
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
                        <div class="grid gap-3 md:grid-cols-3">
                            <div>
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                                    Catalogue Product
                                </div>

                                <div
                                    class="mt-1 text-sm font-semibold text-gray-900"
                                    x-text="item?.name || '—'"
                                ></div>
                            </div>

                            <div>
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                                    Category
                                </div>

                                <div
                                    class="mt-1 text-sm text-gray-800"
                                    x-text="item?.category?.name || '—'"
                                ></div>
                            </div>

                            <div>
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                                    Subcategory
                                </div>

                                <div
                                    class="mt-1 text-sm text-gray-800"
                                    x-text="item?.subcategory?.name || '—'"
                                ></div>
                            </div>
                        </div>

                        <div
                            x-show="item?.variant_text"
                            class="mt-3 rounded-md border border-gray-200 bg-white px-3 py-2 text-xs text-gray-600"
                        >
                            Catalogue detail:
                            <strong x-text="item?.variant_text"></strong>
                        </div>
                    </div>

                    {{-- Tabs --}}
                    <div class="border-b border-gray-200 px-5 pt-4">
                        <div class="flex gap-5">
                            <button
                                type="button"
                                @click="mode = 'existing'"
                                class="border-b-2 pb-3 text-sm font-medium"
                                :class="mode === 'existing'
                                    ? 'border-[#10212F] text-[#10212F]'
                                    : 'border-transparent text-gray-500 hover:text-gray-800'"
                            >
                                Map Existing Material
                            </button>

                            <button
                                type="button"
                                @click="mode = 'create'"
                                class="border-b-2 pb-3 text-sm font-medium"
                                :class="mode === 'create'
                                    ? 'border-[#10212F] text-[#10212F]'
                                    : 'border-transparent text-gray-500 hover:text-gray-800'"
                            >
                                Create Material
                            </button>
                        </div>
                    </div>

                    {{-- Existing Material --}}
                    <div
                        x-show="mode === 'existing'"
                        class="px-5 py-5"
                    >
                        <form
                            method="POST"
                            :action="mapExistingUrl()"
                        >
                            @csrf

                            <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                Search Existing Material Type
                            </label>

                            <div class="relative">
                                <input
                                    type="text"
                                    x-model="materialSearch"
                                    @input.debounce.250ms="searchMaterials()"
                                    @focus="searchMaterials()"
                                    placeholder="Type screw, cement, PVC..."
                                    autocomplete="off"
                                    class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[#10212F] focus:ring-[#10212F]"
                                >

                                <div
                                    x-show="materialSearchOpen && materialResults.length"
                                    @click.outside="materialSearchOpen = false"
                                    class="absolute z-30 mt-1 max-h-64 w-full overflow-y-auto rounded-md border border-gray-200 bg-white shadow-lg"
                                >
                                    <template
                                        x-for="material in materialResults"
                                        :key="material.id"
                                    >
                                        <button
                                            type="button"
                                            @click="selectMaterial(material)"
                                            class="flex w-full items-start justify-between gap-3 border-b border-gray-100 px-3 py-2.5 text-left last:border-0 hover:bg-gray-50"
                                        >
                                            <div>
                                                <div
                                                    class="text-sm font-medium text-gray-900"
                                                    x-text="material.name"
                                                ></div>

                                                <div class="mt-0.5 text-xs text-gray-500">
                                                    <span x-text="material.code"></span>

                                                    <template x-if="material.material_group">
                                                        <span>
                                                            ·
                                                            <span x-text="material.material_group"></span>
                                                        </span>
                                                    </template>
                                                </div>
                                            </div>

                                            <div
                                                class="text-xs text-gray-500"
                                                x-text="material.unit?.symbol || material.unit?.code || ''"
                                            ></div>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <input
                                type="hidden"
                                name="material_type_id"
                                :value="selectedMaterial?.id || ''"
                            >

                            <div
                                x-show="selectedMaterial"
                                class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3"
                            >
                                <div class="text-xs font-semibold uppercase tracking-wide text-green-700">
                                    Selected Material
                                </div>

                                <div class="mt-1 flex items-center justify-between gap-4">
                                    <div>
                                        <div
                                            class="text-sm font-semibold text-gray-900"
                                            x-text="selectedMaterial?.name"
                                        ></div>

                                        <div class="mt-0.5 text-xs text-gray-600">
                                            <span x-text="selectedMaterial?.code"></span>

                                            <template x-if="selectedMaterial?.unit">
                                                <span>
                                                    ·
                                                    Unit:
                                                    <span
                                                        x-text="selectedMaterial?.unit?.symbol || selectedMaterial?.unit?.code"
                                                    ></span>
                                                </span>
                                            </template>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        @click="selectedMaterial = null"
                                        class="text-xs font-medium text-red-600 hover:text-red-800"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>

                            <div class="mt-5 flex justify-end gap-2">
                                <button
                                    type="button"
                                    @click="closeModal()"
                                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Cancel
                                </button>

                                <button
                                    type="submit"
                                    :disabled="!selectedMaterial"
                                    class="rounded-md bg-[#10212F] px-4 py-2 text-sm font-medium text-white hover:bg-[#1b3447] disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    Map Material
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Create Material --}}
                    <div
                        x-show="mode === 'create'"
                        class="px-5 py-5"
                    >
                        <form
                            method="POST"
                            :action="createMaterialUrl()"
                        >
                            @csrf

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        Product Name
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        name="material_type_name"
                                        x-model="createForm.name"
                                        required
                                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[#10212F] focus:ring-[#10212F]"
                                    >
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        Category
                                    </label>

                                    <input
                                        type="text"
                                        :value="item?.category?.name || '—'"
                                        disabled
                                        class="w-full rounded-md border-gray-200 bg-gray-50 text-sm text-gray-600"
                                    >
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        Subcategory
                                    </label>

                                    <input
                                        type="text"
                                        :value="item?.subcategory?.name || '—'"
                                        disabled
                                        class="w-full rounded-md border-gray-200 bg-gray-50 text-sm text-gray-600"
                                    >
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        Unit
                                    </label>

                                    <select
                                        name="unit_master_id"
                                        x-model="createForm.unitId"
                                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[#10212F] focus:ring-[#10212F]"
                                    >
                                        <option value="">
                                            Select Unit
                                        </option>

                                        <template
                                            x-for="unit in units"
                                            :key="unit.id"
                                        >
                                            <option
    :value="String(unit.id)"
    x-text="unit.unit_name + (unit.symbol ? ' (' + unit.symbol + ')' : '')"
></option>
                                        </template>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        Catalogue Detail
                                    </label>

                                    <input
                                        type="text"
                                        :value="item?.variant_text || '—'"
                                        disabled
                                        class="w-full rounded-md border-gray-200 bg-gray-50 text-sm text-gray-600"
                                    >
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        Remarks
                                    </label>

                                    <textarea
                                        name="remarks"
                                        rows="2"
                                        placeholder="Optional"
                                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[#10212F] focus:ring-[#10212F]"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="mt-5 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-xs text-blue-800">
                                This creates one reusable Material Type and maps it to
                                the catalogue subcategory. Size/specification variants
                                are not forced here.
                            </div>

                            <div class="mt-5 flex justify-end gap-2">
                                <button
                                    type="button"
                                    @click="closeModal()"
                                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Cancel
                                </button>

                                <button
                                    type="submit"
                                    class="rounded-md bg-[#10212F] px-4 py-2 text-sm font-medium text-white hover:bg-[#1b3447]"
                                >
                                    Create & Map Material
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function materialCatalogueMapping() {
        return {
            modalOpen: false,
            loading: false,

            itemId: null,
            item: null,
            units: [],

            mode: 'existing',

            materialSearch: '',
            materialResults: [],
            materialSearchOpen: false,
            selectedMaterial: null,

            createForm: {
                name: '',
                unitId: '',
            },

            async openMapping(itemId) {
                this.modalOpen = true;
                this.loading = true;

                this.itemId = itemId;
                this.item = null;
                this.units = [];

                this.mode = 'existing';

                this.materialSearch = '';
                this.materialResults = [];
                this.materialSearchOpen = false;
                this.selectedMaterial = null;

                this.createForm = {
                    name: '',
                    unitId: '',
                };

                try {
                    const url =
                        `{{ url('/material-catalog-mapping') }}/${itemId}/form-data`;

                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Unable to load catalogue item.');
                    }

                    const data = await response.json();

                    this.item = data.item;
                    this.units = data.units || [];

                    this.createForm.name =
                        data.item?.suggested_base_name ||
                        data.item?.name ||
                        '';

                    this.createForm.unitId =
                        data.item?.suggested_unit_id
                            ? String(data.item.suggested_unit_id)
                            : '';

                    if (data.item?.mapped_material_type) {
                        this.selectedMaterial = {
                            id: data.item.mapped_material_type.id,
                            name: data.item.mapped_material_type.name,
                            code: data.item.mapped_material_type.code,
                            material_group: null,
                            unit: null,
                        };

                        this.materialSearch =
                            data.item.mapped_material_type.name;
                    } else {
                        /*
                         * Start searching automatically using the catalogue
                         * product name. This makes plural/similar products
                         * easier to map without extra typing.
                         */
                        this.materialSearch =
                            data.item?.suggested_base_name ||
                            data.item?.name ||
                            '';

                        await this.searchMaterials();
                    }
                } catch (error) {
                    console.error(error);

                    alert(
                        'Unable to load the catalogue product. Please refresh and try again.'
                    );

                    this.closeModal();
                } finally {
                    this.loading = false;
                }
            },

            closeModal() {
                this.modalOpen = false;
                this.loading = false;

                this.itemId = null;
                this.item = null;
                this.units = [];

                this.materialSearch = '';
                this.materialResults = [];
                this.materialSearchOpen = false;
                this.selectedMaterial = null;
            },

            async searchMaterials() {
                const query = this.materialSearch.trim();

                if (!query) {
                    this.materialResults = [];
                    this.materialSearchOpen = false;
                    return;
                }

                try {
                    const url =
                        `{{ route('material-catalog-mapping.search-material-types') }}` +
                        `?q=${encodeURIComponent(query)}&limit=20`;

                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Material search failed.');
                    }

                    const data = await response.json();

                    this.materialResults = data.data || [];
                    this.materialSearchOpen =
                        this.materialResults.length > 0;
                } catch (error) {
                    console.error(error);

                    this.materialResults = [];
                    this.materialSearchOpen = false;
                }
            },

            selectMaterial(material) {
                this.selectedMaterial = material;
                this.materialSearch = material.name;
                this.materialSearchOpen = false;
            },

            mapExistingUrl() {
                if (!this.itemId) {
                    return '#';
                }

                return `{{ url('/material-catalog-mapping') }}/${this.itemId}/map-existing`;
            },

            createMaterialUrl() {
                if (!this.itemId) {
                    return '#';
                }

                return `{{ url('/material-catalog-mapping') }}/${this.itemId}/create-material`;
            },
        };
    }
</script>
@endsection