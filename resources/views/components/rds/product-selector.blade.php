@props([
    'name' => 'material_type_id',
    'label' => 'Product',
    'value' => null,
    'selectedProduct' => null,
    'placeholder' => 'Search Product name, alias or code...',
    'required' => false,
    'disabled' => false,
    'help' => null,
    'endpoint' => null,
])

@php
    $endpoint = $endpoint ?: route('materials.product-search');
@endphp

<div
    class="relative min-w-0"
    x-data="ravionProductSelector({
        endpoint: @js($endpoint),
        value: @js($value),
        selectedProduct: @js($selectedProduct),
    })"
    @click.outside="closeResults()"
>
    @if($label)
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">
            {{ $label }}
            @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    <input type="hidden"
           name="{{ $name }}"
           x-model="selectedId"
           @if($required) required @endif>

    <div class="relative">
        <input
            x-ref="searchInput"
            type="text"
            x-model="query"
            @input="queueSearch()"
            @focus="if (query.trim().length >= 2 && !selectedId) { search(); }"
            @keydown.escape="open = false"
            placeholder="{{ $placeholder }}"
            @disabled($disabled)
            autocomplete="off"
            class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-3 pr-28 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
        >

        <button
            x-show="selectedId"
            x-cloak
            type="button"
            @click="clearProduct()"
            class="absolute inset-y-1.5 right-1.5 rounded-md border border-red-200 bg-white px-3 text-xs font-semibold text-red-700 hover:bg-red-50"
        >
            × Clear
        </button>

        <div x-show="!selectedId"
             class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                <circle cx="8.5" cy="8.5" r="5.5" stroke-width="1.6"></circle>
                <path d="M12.5 12.5L17 17" stroke-width="1.6" stroke-linecap="round"></path>
            </svg>
        </div>
    </div>

    <div x-show="selectedId"
         x-cloak
         class="mt-1 text-[11px] text-gray-500">
        <span x-text="groupName(selectedProduct)"></span>
        <span> · </span>
        <span x-text="typeName(selectedProduct)"></span>
        <span x-show="selectedProduct?.unit">
            <span> · Unit: </span>
            <span x-text="unitLabel(selectedProduct)"></span>
        </span>
    </div>

    <div
        x-show="open && !selectedId"
        x-cloak
        class="absolute left-0 z-[80] mt-2 w-[min(720px,calc(100vw-3rem))] overflow-hidden rounded-xl border border-gray-200 bg-white shadow-2xl"
    >
        <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50 px-4 py-2.5">
            <span class="text-xs font-semibold text-gray-600">Product Search Results</span>
            <span class="text-xs text-gray-400"
                  x-show="!loading"
                  x-text="results.length ? `${results.length} shown` : ''"></span>
        </div>

        <div x-show="loading" class="px-4 py-5 text-sm text-gray-500">
            Searching Products...
        </div>

        <div x-show="error" class="px-4 py-4 text-sm text-red-600" x-text="error"></div>

        <div x-show="!loading && !error" class="max-h-[420px] overflow-y-auto">
            <template x-for="product in results" :key="product.id">
                <button type="button"
                        @mousedown.prevent="selectProduct(product)"
                        class="block w-full border-b border-gray-100 px-4 py-3 text-left hover:bg-blue-50 focus:bg-blue-50">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-900" x-text="product.name"></div>

                            <div class="mt-1 text-xs text-gray-500">
                                <span x-text="groupName(product)"></span>
                                <span> → </span>
                                <span x-text="typeName(product)"></span>
                            </div>

                            <div class="mt-1 font-mono text-[11px] text-gray-400"
                                 x-text="product.catalogue_code || product.code || ''"></div>
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            <span class="rounded-md border border-gray-200 bg-white px-2 py-1 text-[11px] font-semibold text-gray-600"
                                  x-text="unitLabel(product)"></span>

                            <span class="rounded-md bg-gray-100 px-2 py-1 text-[11px] text-gray-600"
                                  x-text="product.inventory_type || ''"></span>
                        </div>
                    </div>
                </button>
            </template>

            <div x-show="results.length === 0 && query.trim().length >= 2"
                 class="px-4 py-6 text-center text-sm text-gray-500">
                No matching Product found.
            </div>
        </div>
    </div>

    @if($help)
        <p class="mt-1 text-xs text-gray-500">{{ $help }}</p>
    @endif
</div>
