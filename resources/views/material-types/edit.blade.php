@extends('layouts.app')

@section('content')
@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
    $labelClass = 'mb-1 block text-sm font-semibold text-gray-700';
@endphp
<div class="mx-auto max-w-5xl">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Product — {{ $materialType->material_type_name }}</h1>
            <p class="mt-1 text-sm text-gray-500">Product ID {{ $materialType->id }} · Existing Bible classifications are preserved.</p>
        </div>
        <a href="{{ route('material-types.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">Back to Products</a>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 p-4 text-sm text-red-700">
            <ul class="ml-5 list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('material-types.update', $materialType) }}">
        @csrf
        @method('PUT')
        <section class="mb-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">1. Ravion Material Bible Classification</h2>
            <p class="mt-1 text-sm text-gray-500">Current paths are shown below. Adding a path does not remove existing classifications or variant mappings.</p>
            <div class="mt-4 space-y-2">
                @forelse($existingBiblePaths as $path)
                    <div class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-gray-800">
                        <span class="font-semibold">{{ $path->trade_name }}</span>
                        <span class="mx-1 text-gray-400">→</span>{{ $path->main_name }}
                        <span class="mx-1 text-gray-400">→</span>{{ $path->sub_name }}
                    </div>
                @empty
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">This product has no active Bible classification. You can add one below.</div>
                @endforelse
            </div>
            <div class="mt-5 border-t border-gray-100 pt-4">
                <h3 class="mb-1 text-sm font-bold text-gray-900">Add another Bible classification (optional)</h3>
                <p class="mb-3 text-xs text-gray-500">Leave all three dropdowns empty to keep the current mappings unchanged.</p>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div><label class="{{ $labelClass }}" for="material_bible_trade_id">Trade Category</label>
                        <select class="{{ $inputClass }}" id="material_bible_trade_id" name="material_bible_trade_id"><option value="">No new classification</option>@foreach($bibleTrades as $trade)<option value="{{ $trade->id }}" @selected((string)old('material_bible_trade_id') === (string)$trade->id)>{{ $trade->name }}</option>@endforeach</select>
                    </div>
                    <div><label class="{{ $labelClass }}" for="material_bible_main_category_id">Main Category</label>
                        <select class="{{ $inputClass }}" id="material_bible_main_category_id" name="material_bible_main_category_id"><option value="">Select Main Category</option></select>
                    </div>
                    <div><label class="{{ $labelClass }}" for="material_bible_sub_category_id">Sub Category</label>
                        <select class="{{ $inputClass }}" id="material_bible_sub_category_id" name="material_bible_sub_category_id"><option value="">Select Sub Category</option></select>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-gray-900">2. Product Details</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div><label class="{{ $labelClass }}">Product Name <span class="text-red-500">*</span></label><input class="{{ $inputClass }}" type="text" name="material_type_name" value="{{ old('material_type_name', $materialType->material_type_name) }}" required></div>
                <div><label class="{{ $labelClass }}">Internal Code</label><input class="{{ $inputClass }}" type="text" name="material_type_code" value="{{ old('material_type_code', $materialType->material_type_code) }}"></div>
                <div><label class="{{ $labelClass }}">Default Unit <span class="text-red-500">*</span></label><select class="{{ $inputClass }}" name="unit_master_id" required><option value="">Select Unit</option>@foreach($units as $unit)<option value="{{ $unit->id }}" @selected((string)old('unit_master_id', $materialType->unit_master_id) === (string)$unit->id)>{{ $unit->unit_name }} @if($unit->unit_code)({{ $unit->unit_code }})@endif</option>@endforeach</select></div>
                <div><label class="{{ $labelClass }}">Inventory Type <span class="text-red-500">*</span></label><input class="{{ $inputClass }}" type="text" name="inventory_type" list="inventory-type-options" value="{{ old('inventory_type', $materialType->inventory_type) }}" required><datalist id="inventory-type-options">@foreach($inventoryTypes as $inventoryType)<option value="{{ $inventoryType }}">@endforeach</datalist></div>
                <div><label class="{{ $labelClass }}">Master Status <span class="text-red-500">*</span></label><select class="{{ $inputClass }}" name="master_status" required>@foreach($masterStatuses as $status)<option value="{{ $status }}" @selected(old('master_status', $materialType->master_status) === $status)>{{ $status }}</option>@endforeach</select></div>
                <div><label class="{{ $labelClass }}">Display Sequence</label><input class="{{ $inputClass }}" type="number" min="0" name="sequence" value="{{ old('sequence', $materialType->sequence) }}"></div>
                <div class="md:col-span-2"><label class="inline-flex items-center gap-2 text-sm font-semibold text-gray-700"><input type="checkbox" name="is_active" value="1" @checked((bool)old('is_active', $materialType->is_active))> Active</label></div>
                <div class="md:col-span-2"><label class="{{ $labelClass }}">Remarks</label><textarea class="{{ $inputClass }}" name="remarks" rows="3">{{ old('remarks', $materialType->remarks) }}</textarea></div>
            </div>
        </section>
        <div class="mb-5 rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">Variants, specifications, grades and brands will be managed in the next phase. This form currently updates product details and optionally adds a Bible classification only.</div>
        <div class="flex flex-wrap gap-3"><button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Update Product</button><a href="{{ route('material-types.index') }}" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700">Cancel</a></div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const trade = document.getElementById('material_bible_trade_id');
    const main = document.getElementById('material_bible_main_category_id');
    const sub = document.getElementById('material_bible_sub_category_id');
    const mains = @json($bibleMainCategories);
    const subs = @json($bibleSubCategories);
    const oldMain = @json((string)old('material_bible_main_category_id', ''));
    const oldSub = @json((string)old('material_bible_sub_category_id', ''));
    function fill(select, items, placeholder, selected = '') {
        select.replaceChildren(new Option(placeholder, ''));
        items.forEach(item => { const opt = new Option(item.name, String(item.id)); opt.selected = String(item.id) === String(selected); select.add(opt); });
    }
    function refreshSub(selected = '') {
        fill(sub, subs.filter(item => String(item.material_bible_main_category_id) === main.value && Number(item.is_active) === 1), 'Select Sub Category', selected);
        sub.disabled = !main.value;
    }
    function refreshMain(selectedMain = '', selectedSub = '') {
        fill(main, mains.filter(item => String(item.material_bible_trade_id) === trade.value && Number(item.is_active) === 1), 'Select Main Category', selectedMain);
        main.disabled = !trade.value;
        refreshSub(selectedSub);
    }
    trade.addEventListener('change', () => refreshMain());
    main.addEventListener('change', () => refreshSub());
    refreshMain(oldMain, oldSub);
    document.querySelector('form').addEventListener('submit', event => {
        const values = [trade.value, main.value, sub.value];
        if (values.some(Boolean) && !values.every(Boolean)) {
            event.preventDefault();
            alert('Select Trade, Main Category and Sub Category together, or leave all three empty.');
        }
    });
});
</script>
@endsection
