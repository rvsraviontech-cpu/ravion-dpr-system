@extends('layouts.app')

@section('content')
@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
    $labelClass = 'mb-1 block text-sm font-semibold text-gray-700';
@endphp
<div class="mx-auto max-w-5xl" id="product-master-create">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add Product</h1>
            <p class="mt-1 text-sm text-gray-500">Search first to reuse an existing product, or create a new Bible-classified product.</p>
        </div>
        <a href="{{ route('material-types.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">Back to Products</a>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 p-4 text-sm text-red-700">
            <ul class="ml-5 list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="mb-5 rounded-xl border border-blue-200 bg-white p-5 shadow-sm" aria-labelledby="lookup-title">
        <h2 id="lookup-title" class="text-lg font-bold text-gray-900">1. Search Existing Products</h2>
        <p class="mb-3 mt-1 text-sm text-gray-500">Search by product name, code, alias, variant or size. Select a result to load its existing details.</p>
        <label for="product_lookup" class="{{ $labelClass }}">Find a product</label>
        <input id="product_lookup" type="search" autocomplete="off" class="{{ $inputClass }}" placeholder="Example: 20mm jelly, cement PPC, TMT 12mm" aria-controls="product_lookup_results" aria-expanded="false">
        <div id="product_lookup_status" class="mt-2 text-xs text-gray-500" role="status" aria-live="polite">Enter at least 2 characters to search.</div>
        <div id="product_lookup_results" class="mt-2 hidden max-h-64 overflow-y-auto rounded-lg border border-gray-200" role="listbox" aria-label="Matching products"></div>
        <div id="existing_product_panel" class="mt-4 hidden rounded-lg border border-emerald-200 bg-emerald-50 p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-800">Existing product selected — duplicate creation disabled</p>
                    <p id="existing_product_name" class="mt-1 text-base font-bold text-gray-900"></p>
                    <p id="existing_product_code" class="text-xs text-gray-600"></p>
                </div>
                <button id="clear_existing_product" type="button" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700">Create a different product</button>
            </div>
            <div class="mt-3">
                <label for="existing_path" class="{{ $labelClass }}">Existing Bible classification paths</label>
                <select id="existing_path" class="{{ $inputClass }}"><option value="">No Bible path recorded</option></select>
            </div>
            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div><label for="existing_variant" class="{{ $labelClass }}">Available Variants</label><select id="existing_variant" class="{{ $inputClass }}"></select></div>
                <div><label for="existing_specification" class="{{ $labelClass }}">Available Specifications</label><select id="existing_specification" class="{{ $inputClass }}"></select></div>
                <div><label for="existing_grade" class="{{ $labelClass }}">Available Grades</label><select id="existing_grade" class="{{ $inputClass }}"></select></div>
                <div><label for="existing_brand" class="{{ $labelClass }}">Associated Brands</label><select id="existing_brand" class="{{ $inputClass }}"></select></div>
            </div>
            <p class="mt-3 text-xs text-gray-600">These fields display existing records; they do not create new variants, specifications, grades or brands. Use the existing product's management page for changes.</p>
            <a id="existing_product_link" href="#" class="mt-3 inline-flex rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Open Existing Product</a>
        </div>
    </section>

    <form id="new_product_form" method="POST" action="{{ route('material-types.store') }}">
        @csrf
        <fieldset id="new_product_fields" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-gray-900">2. Create New Product</h2>
                <p class="mt-1 text-sm text-gray-500">Only use this section when the product is not already in the catalogue. Trade → Main → Sub Category is the authoritative classification.</p>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div><label for="material_bible_trade_id" class="{{ $labelClass }}">Trade Category <span class="text-red-600">*</span></label>
                    <select id="material_bible_trade_id" name="material_bible_trade_id" class="{{ $inputClass }}" required><option value="">Select Trade Category</option>
                        @foreach($bibleTrades as $trade)<option value="{{ $trade->id }}" @selected((string) old('material_bible_trade_id') === (string) $trade->id)>{{ $trade->name }}</option>@endforeach
                    </select>
                </div>
                <div><label for="material_bible_main_category_id" class="{{ $labelClass }}">Main Category <span class="text-red-600">*</span></label>
                    <select id="material_bible_main_category_id" name="material_bible_main_category_id" class="{{ $inputClass }}" required><option value="">Select Main Category</option></select>
                </div>
                <div><label for="material_bible_sub_category_id" class="{{ $labelClass }}">Sub Category <span class="text-red-600">*</span></label>
                    <select id="material_bible_sub_category_id" name="material_bible_sub_category_id" class="{{ $inputClass }}" required><option value="">Select Sub Category</option></select>
                </div>
            </div>
            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div><label for="material_type_name" class="{{ $labelClass }}">Product Name <span class="text-red-600">*</span></label><input id="material_type_name" name="material_type_name" type="text" maxlength="255" value="{{ old('material_type_name') }}" class="{{ $inputClass }}" required placeholder="Example: Portland Pozzolana Cement"></div>
                <div><label for="material_type_code" class="{{ $labelClass }}">Internal Code</label><input id="material_type_code" name="material_type_code" type="text" maxlength="100" value="{{ old('material_type_code') }}" class="{{ $inputClass }}" placeholder="Optional"></div>
                <div><label for="unit_master_id" class="{{ $labelClass }}">Default Unit <span class="text-red-600">*</span></label><select id="unit_master_id" name="unit_master_id" class="{{ $inputClass }}" required><option value="">Select Default Unit</option>@foreach($units as $unit)<option value="{{ $unit->id }}" @selected((string) old('unit_master_id') === (string) $unit->id)>{{ $unit->unit_name }} @if(!empty($unit->unit_code))({{ $unit->unit_code }})@endif</option>@endforeach</select></div>
                <div><label for="inventory_type" class="{{ $labelClass }}">Inventory Type <span class="text-red-600">*</span></label><input id="inventory_type" name="inventory_type" type="text" list="inventory-type-options" value="{{ old('inventory_type', 'Stock Material') }}" class="{{ $inputClass }}" required><datalist id="inventory-type-options">@foreach($inventoryTypes as $inventoryType)<option value="{{ $inventoryType }}">@endforeach</datalist></div>
                <div><label for="master_status" class="{{ $labelClass }}">Master Status <span class="text-red-600">*</span></label><select id="master_status" name="master_status" class="{{ $inputClass }}" required>@foreach($masterStatuses as $status)<option value="{{ $status }}" @selected(old('master_status', 'Approved') === $status)>{{ $status }}</option>@endforeach</select></div>
                <div><label for="sequence" class="{{ $labelClass }}">Display Sequence</label><input id="sequence" name="sequence" type="number" min="0" value="{{ old('sequence', 0) }}" class="{{ $inputClass }}"></div>
                <div class="md:col-span-2"><label for="remarks" class="{{ $labelClass }}">Remarks</label><textarea id="remarks" name="remarks" rows="3" class="{{ $inputClass }}">{{ old('remarks') }}</textarea></div>
            </div>
            <p class="mt-4 text-xs text-amber-800">Variant, specification, grade and brand creation will be enabled after the related backend save actions are integrated. This form currently saves the product and its Bible classification only.</p>
        </fieldset>
        <div class="mt-5 flex flex-wrap gap-3"><button id="save_new_product" type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Save New Product</button><a href="{{ route('material-types.index') }}" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700">Cancel</a></div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const mainCategories = @json($bibleMainCategories);
    const subCategories = @json($bibleSubCategories);
    const oldMain = @json((string) old('material_bible_main_category_id', ''));
    const oldSub = @json((string) old('material_bible_sub_category_id', ''));
    const trade = document.getElementById('material_bible_trade_id');
    const main = document.getElementById('material_bible_main_category_id');
    const sub = document.getElementById('material_bible_sub_category_id');
    const search = document.getElementById('product_lookup');
    const results = document.getElementById('product_lookup_results');
    const status = document.getElementById('product_lookup_status');
    const panel = document.getElementById('existing_product_panel');
    const fields = document.getElementById('new_product_fields');
    const save = document.getElementById('save_new_product');
    const pathSelect = document.getElementById('existing_path');
    const searchUrl = @json(route('materials.product-search'));
    const showUrlTemplate = @json(route('material-types.show', ['material_type' => '__PRODUCT_ID__']));
    let timer, controller, selectedProduct = null, latestRequest = 0;

    function options(select, items, label, chosen = '') {
        select.replaceChildren(new Option(label, ''));
        items.forEach(item => {
            const option = new Option(item.name, String(item.id));
            option.selected = String(item.id) === String(chosen);
            select.add(option);
        });
    }
    function refreshSub(chosen = '') {
        options(sub, subCategories.filter(x => String(x.material_bible_main_category_id) === main.value && Number(x.is_active) === 1), 'Select Sub Category', chosen);
        sub.disabled = !main.value;
    }
    function refreshMain(chosenMain = '', chosenSub = '') {
        options(main, mainCategories.filter(x => String(x.material_bible_trade_id) === trade.value && Number(x.is_active) === 1), 'Select Main Category', chosenMain);
        main.disabled = !trade.value;
        refreshSub(chosenSub);
    }
    trade.addEventListener('change', () => refreshMain());
    main.addEventListener('change', () => refreshSub());
    refreshMain(oldMain, oldSub);

    function setExisting(product) {
        selectedProduct = product;
        panel.classList.remove('hidden');
        fields.disabled = true;
        save.disabled = true;
        save.classList.add('opacity-50', 'cursor-not-allowed');
        document.getElementById('existing_product_name').textContent = product.name;
        document.getElementById('existing_product_code').textContent = [product.code, product.unit?.name].filter(Boolean).join(' · ');
        document.getElementById('existing_product_link').href = showUrlTemplate.replace('__PRODUCT_ID__', encodeURIComponent(product.id));
        const paths = product.bible_paths || [];
        pathSelect.replaceChildren(new Option(paths.length ? 'Choose classification path' : 'No Bible path recorded', ''));
        paths.forEach((p, i) => pathSelect.add(new Option([p.trade_name, p.main_name, p.sub_name].join(' → '), String(i))));
        options(document.getElementById('existing_variant'), product.available_variants || [], 'Select existing variant');
        options(document.getElementById('existing_specification'), product.available_specifications || [], 'Select existing specification');
        options(document.getElementById('existing_grade'), product.available_grades || [], 'Select existing grade');
        options(document.getElementById('existing_brand'), product.available_brands || [], 'Select existing brand');
        if (paths.length === 1) { pathSelect.value = '0'; applyPath(); }
        results.classList.add('hidden');
        search.setAttribute('aria-expanded', 'false');
        status.textContent = 'Existing product selected. Open it to manage its details.';
    }
    function applyPath() {
        if (!selectedProduct) return;
        const p = (selectedProduct.bible_paths || [])[Number(pathSelect.value)];
        if (!p || pathSelect.value === '') return;
        trade.value = String(p.trade_id);
        refreshMain(String(p.main_id), String(p.sub_id));
    }
    pathSelect.addEventListener('change', applyPath);
    document.getElementById('clear_existing_product').addEventListener('click', () => {
        selectedProduct = null;
        panel.classList.add('hidden');
        fields.disabled = false;
        save.disabled = false;
        save.classList.remove('opacity-50', 'cursor-not-allowed');
        search.value = '';
        results.replaceChildren();
        results.classList.add('hidden');
        search.setAttribute('aria-expanded', 'false');
        status.textContent = 'Enter at least 2 characters to search.';
        search.focus();
    });
    function renderResults(items) {
        results.replaceChildren();
        if (!items.length) { results.classList.add('hidden'); status.textContent = 'No matching products. You may create a new product after checking the name.'; return; }
        items.forEach(product => {
            const button = document.createElement('button');
            button.type = 'button';
            button.setAttribute('role', 'option');
            button.className = 'block w-full border-b border-gray-100 px-4 py-3 text-left hover:bg-blue-50 focus:bg-blue-50';
            const name = document.createElement('span');
            name.className = 'block text-sm font-semibold text-gray-900';
            name.textContent = product.name;
            const detail = document.createElement('span');
            detail.className = 'block text-xs text-gray-500';
            detail.textContent = [product.code, product.unit?.name, (product.bible_paths || []).length + ' Bible path(s)'].filter(Boolean).join(' · ');
            button.append(name, detail);
            button.addEventListener('click', () => setExisting(product));
            results.append(button);
        });
        results.classList.remove('hidden');
        search.setAttribute('aria-expanded', 'true');
        status.textContent = `${items.length} matching product(s). Select one to load details.`;
    }
    search.addEventListener('input', () => {
        clearTimeout(timer);
        if (controller) controller.abort();
        const requestId = ++latestRequest;
        const q = search.value.trim();
        if (selectedProduct) return;
        if (q.length < 2) { results.classList.add('hidden'); search.setAttribute('aria-expanded', 'false'); status.textContent = 'Enter at least 2 characters to search.'; return; }
        status.textContent = 'Searching products…';
        timer = setTimeout(async () => {
            controller = new AbortController();
            try {
                const url = new URL(searchUrl, window.location.origin);
                url.searchParams.set('q', q);
                url.searchParams.set('limit', '20');
                url.searchParams.set('product_master', '1');
                const response = await fetch(url.toString(), {signal: controller.signal, headers: {'Accept': 'application/json'}});
                if (!response.ok) throw new Error(`Search returned HTTP ${response.status}`);
                const data = await response.json();
                if (requestId !== latestRequest || selectedProduct) return;
                renderResults(Array.isArray(data.data) ? data.data : []);
            } catch (error) {
                if (error.name === 'AbortError' || requestId !== latestRequest) return;
                results.classList.add('hidden');
                search.setAttribute('aria-expanded', 'false');
                status.textContent = 'Search is unavailable. Please try again or contact the ERP administrator.';
            }
        }, 350);
    });
    document.getElementById('new_product_form').addEventListener('submit', event => {
        if (selectedProduct) { event.preventDefault(); return; }
        const typed = search.value.trim().toLowerCase();
        const name = document.getElementById('material_type_name').value.trim().toLowerCase();
        if (typed && typed === name && !window.confirm('Confirm that this product does not already exist in the search results. Continue creating a new product?')) event.preventDefault();
    });
});
</script>
@endsection
