{{-- RAVION PRODUCT MASTER V2.3 - MANUAL SEARCH --}}
@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
@endphp

<div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Product Master</h1>
        <p class="mt-1 text-sm text-gray-500">
            Ravion Material Bible product catalogue for Requirements, Procurement, Dispatch, Receipt and Consumption.
        </p>
    </div>

    <a href="{{ route('material-types.create') }}"
       class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
        + Add Product
    </a>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        {{ session('error') }}
    </div>
@endif

{{-- Summary cards --}}
<div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Canonical Products</p>
        <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($totalCanonicalProducts) }}</p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Search / Filter Results</p>
        <p class="mt-1 text-2xl font-bold text-blue-700">{{ number_format($filteredProducts) }}</p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Active in Results</p>
        <p class="mt-1 text-2xl font-bold text-green-700">{{ number_format($filteredActiveProducts) }}</p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Bible-Mapped Products in Results</p>
        <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($filteredBibleProducts) }}</p>
    </div>
</div>

{{-- Filters --}}
<div class="mb-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
    <form id="product-filter-form"
          method="GET"
          action="{{ route('material-types.index') }}"
          class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-8">

        <div class="xl:col-span-2">
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Search</label>
            <input id="product-search"
                   type="search"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Product, code, Bible category or alias..."
                   autocomplete="off"
                   class="{{ $inputClass }}">
        </div>

        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Trade Category</label>
            <select id="filter_bible_trade" name="material_bible_trade_id" class="{{ $inputClass }}">
                <option value="">All Trades</option>
                @foreach($bibleTrades as $trade)
                    <option value="{{ $trade->id }}" @selected((string) request('material_bible_trade_id') === (string) $trade->id)>{{ $trade->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Main Category</label>
            <select id="filter_bible_main" name="material_bible_main_category_id" class="{{ $inputClass }}">
                <option value="">All Main Categories</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Sub Category</label>
            <select id="filter_bible_sub" name="material_bible_sub_category_id" class="{{ $inputClass }}">
                <option value="">All Sub Categories</option>
            </select>
        </div>

        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Inventory</label>
            <select name="inventory_type" class="{{ $inputClass }}">
                <option value="">All</option>
                @foreach($inventoryTypes as $inventoryType)
                    <option value="{{ $inventoryType }}"
                        {{ request('inventory_type') === $inventoryType ? 'selected' : '' }}>
                        {{ $inventoryType }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Master Status</label>
            <select name="master_status" class="{{ $inputClass }}">
                <option value="">All</option>
                @foreach($masterStatuses as $status)
                    <option value="{{ $status }}"
                        {{ request('master_status') === $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Catalogue</label>
            <select name="catalogue_scope" class="{{ $inputClass }}">
                <option value="canonical" {{ request('catalogue_scope', 'canonical') === 'canonical' ? 'selected' : '' }}>Canonical</option>
                <option value="catalogue" {{ request('catalogue_scope') === 'catalogue' ? 'selected' : '' }}>Imported Catalogue</option>
                <option value="manual" {{ request('catalogue_scope') === 'manual' ? 'selected' : '' }}>Manual Products</option>
                <option value="legacy" {{ request('catalogue_scope') === 'legacy' ? 'selected' : '' }}>Legacy</option>
                <option value="all" {{ request('catalogue_scope') === 'all' ? 'selected' : '' }}>All</option>
            </select>
        </div>

        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Active</label>
            <select name="status" class="{{ $inputClass }}">
                <option value="">All</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div class="flex flex-wrap items-end gap-2 xl:col-span-8">
            <button type="submit"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Filter / Search
            </button>

            <a href="{{ route('material-types.index') }}"
               class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Clear Filters
            </a>

            <span class="text-xs text-gray-400">
                Type your search and press Enter, or click Filter / Search.
            </span>

            <div class="ml-auto text-sm font-medium text-gray-600">
                Showing {{ number_format($filteredProducts) }} result{{ $filteredProducts === 1 ? '' : 's' }}
            </div>
        </div>
    </form>
</div>

{{-- Results --}}
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-3 py-3 text-left">#</th>
                    <th class="px-3 py-3 text-left">Product</th>
                    <th class="px-3 py-3 text-left">Trade Category</th>
                    <th class="px-3 py-3 text-left">Main Category</th>
                    <th class="px-3 py-3 text-left">Sub Category</th>
                    <th class="px-3 py-3 text-center">Unit</th>
                    <th class="px-3 py-3 text-left">Inventory</th>
                    <th class="px-3 py-3 text-center">Master</th>
                    <th class="px-3 py-3 text-center">Source</th>
                    <th class="px-3 py-3 text-center">Active</th>
                    <th class="px-3 py-3 text-center">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($materialTypes as $index => $type)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-3 text-gray-500">{{ $materialTypes->firstItem() + $index }}</td>

                        <td class="px-3 py-3">
                            <div class="font-semibold text-gray-900">{{ $type->material_type_name }}</div>
                            <div class="mt-0.5 flex flex-wrap gap-x-3 text-xs text-gray-500">
                                @if($type->material_type_code)
                                    <span>Code: {{ $type->material_type_code }}</span>
                                @endif
                                @if($type->catalogue_source_code)
                                    <span>{{ $type->catalogue_source_code }}</span>
                                @endif
                            </div>
                        </td>

                        @php
                            $paths = $biblePaths->get($type->id, collect());
                            $firstPath = $paths->first();
                        @endphp
                        <td class="px-3 py-3 text-gray-700">{{ $firstPath?->trade_name ?? '-' }}</td>
                        <td class="px-3 py-3 text-gray-700">{{ $firstPath?->main_name ?? '-' }}</td>
                        <td class="px-3 py-3 text-gray-700">
                            {{ $firstPath?->sub_name ?? '-' }}
                            @if($paths->count() > 1)
                                <details class="mt-1 text-xs text-gray-600">
                                    <summary class="cursor-pointer font-semibold text-blue-700">{{ $paths->count() - 1 }} more path(s)</summary>
                                    <ul class="mt-2 list-disc space-y-1 pl-4">
                                        @foreach($paths->skip(1) as $path)
                                            <li>{{ $path->trade_name }} → {{ $path->main_name }} → {{ $path->sub_name }}</li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-center">
                            @if($type->unit)
                                <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                    {{ $type->unit->unit_code ?? $type->unit->unit_name }}
                                </span>
                            @else
                                <span class="text-xs font-semibold text-red-600">Missing</span>
                            @endif
                        </td>

                        <td class="px-3 py-3">{{ $type->inventory_type ?? '-' }}</td>

                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $type->master_status === 'Approved' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $type->master_status ?? '-' }}
                            </span>
                        </td>

                        <td class="px-3 py-3 text-center">
                            @if($type->is_legacy)
                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">Legacy</span>
                            @elseif($type->catalogue_source_code)
                                <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">Catalogue</span>
                            @else
                                <span class="inline-flex rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700">Manual</span>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-center">
                            @if($type->is_active)
                                <span class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">Inactive</span>
                            @endif
                        </td>

                        <td class="px-3 py-3">
                            <div class="flex flex-wrap justify-center gap-2">
                                <a href="{{ route('material-types.show', $type) }}"
                                   class="rounded border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                    View
                                </a>

                                <a href="{{ route('material-types.edit', $type) }}"
                                   class="rounded bg-amber-500 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-amber-600">
                                    Edit
                                </a>

                                <form method="POST"
                                      action="{{ route('material-types.destroy', $type) }}"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('Change this Product status?')"
                                            class="rounded px-2.5 py-1.5 text-xs font-semibold text-white {{ $type->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">
                                        {{ $type->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-6 py-12 text-center text-gray-500">
                            No Products found for the selected filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($materialTypes->hasPages())
        <div class="border-t border-gray-200 px-4 py-4">
            {{ $materialTypes->links() }}
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const trade = document.getElementById('filter_bible_trade');
    const main = document.getElementById('filter_bible_main');
    const sub = document.getElementById('filter_bible_sub');
    const mains = @json($bibleMainCategories);
    const subs = @json($bibleSubCategories);
    const initialMain = @json((string) request('material_bible_main_category_id', ''));
    const initialSub = @json((string) request('material_bible_sub_category_id', ''));

    function populate(select, rows, placeholder, selected) {
        select.replaceChildren(new Option(placeholder, ''));
        rows.forEach(row => {
            const option = new Option(row.name, String(row.id));
            option.selected = String(row.id) === String(selected);
            select.add(option);
        });
    }
    function refreshSub(selected = '') {
        populate(sub, subs.filter(row => (!main.value || String(row.material_bible_main_category_id) === main.value) && Number(row.is_active) === 1), 'All Sub Categories', selected);
    }
    function refreshMain(selectedMain = '', selectedSub = '') {
        populate(main, mains.filter(row => (!trade.value || String(row.material_bible_trade_id) === trade.value) && Number(row.is_active) === 1), 'All Main Categories', selectedMain);
        refreshSub(selectedSub);
    }
    trade.addEventListener('change', () => refreshMain());
    main.addEventListener('change', () => refreshSub());
    refreshMain(initialMain, initialSub);
});
</script>

@endsection
