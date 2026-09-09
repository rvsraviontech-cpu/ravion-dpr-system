@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
    $labelClass = 'mb-1 block text-sm font-semibold text-gray-700';
@endphp

<div class="mx-auto max-w-5xl">
    <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add Product</h1>
            <p class="mt-1 text-sm text-gray-500">Add an approved reusable Product to the Materials catalogue.</p>
        </div>

        <a href="{{ route('material-types.index') }}"
           class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Back
        </a>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 p-4 text-sm text-red-700">
            <ul class="ml-5 list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('material-types.store') }}">
        @csrf

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-gray-900">Product Classification</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Activity Division is only a helper to narrow relevant Product Groups. It is not stored as Product classification.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="{{ $labelClass }}">Activity Division Helper</label>
                    <select id="activity_division_helper" class="{{ $inputClass }}">
                        <option value="">All Product Groups</option>
                        @foreach($activityDivisions as $division)
                            <option value="{{ $division->id }}">{{ $division->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Optional. Use this to reduce the Product Group list.</p>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Search Product Group</label>
                    <input id="product_group_search"
                           type="search"
                           class="{{ $inputClass }}"
                           placeholder="Type part of a group name...">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Product Group <span class="text-red-500">*</span></label>
                    <select id="material_product_group_id"
                            name="material_product_group_id"
                            class="{{ $inputClass }}"
                            required>
                        <option value="">Select Product Group</option>
                        @foreach($productGroups as $group)
                            <option value="{{ $group->id }}"
                                {{ (string) old('material_product_group_id') === (string) $group->id ? 'selected' : '' }}>
                                {{ $group->group_name }}
                            </option>
                        @endforeach
                    </select>
                    <p id="group_count" class="mt-1 text-xs text-gray-500"></p>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Search Product Type</label>
                    <input id="product_type_search"
                           type="search"
                           class="{{ $inputClass }}"
                           placeholder="Type part of a type name...">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Product Type <span class="text-red-500">*</span></label>
                    <select id="material_product_type_id"
                            name="material_product_type_id"
                            class="{{ $inputClass }}"
                            required>
                        <option value="">Select Product Type</option>
                        @foreach($productTypes as $type)
                            <option value="{{ $type->id }}"
                                    data-group="{{ $type->material_product_group_id }}"
                                {{ (string) old('material_product_type_id') === (string) $type->id ? 'selected' : '' }}>
                                {{ $type->type_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Product Name <span class="text-red-500">*</span></label>
                    <input type="text"
                           name="material_type_name"
                           value="{{ old('material_type_name') }}"
                           class="{{ $inputClass }}"
                           placeholder="Example: Portland Pozzolana Cement"
                           required>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Internal Code</label>
                    <input type="text"
                           name="material_type_code"
                           value="{{ old('material_type_code') }}"
                           class="{{ $inputClass }}"
                           placeholder="Optional internal code">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Default Unit <span class="text-red-500">*</span></label>
                    <select name="unit_master_id" class="{{ $inputClass }}" required>
                        <option value="">Select Default Unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}"
                                {{ (string) old('unit_master_id') === (string) $unit->id ? 'selected' : '' }}>
                                {{ $unit->unit_name }}
                                @if(!empty($unit->unit_code)) ({{ $unit->unit_code }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Inventory Type <span class="text-red-500">*</span></label>
                    <input type="text"
                           name="inventory_type"
                           value="{{ old('inventory_type', 'Stock Material') }}"
                           list="inventory-type-options"
                           class="{{ $inputClass }}"
                           required>
                    <datalist id="inventory-type-options">
                        @foreach($inventoryTypes as $inventoryType)
                            <option value="{{ $inventoryType }}">
                        @endforeach
                    </datalist>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Master Status <span class="text-red-500">*</span></label>
                    <select name="master_status" class="{{ $inputClass }}" required>
                        @foreach($masterStatuses as $status)
                            <option value="{{ $status }}"
                                {{ old('master_status', 'Approved') === $status ? 'selected' : '' }}>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Display Sequence</label>
                    <input type="number"
                           name="sequence"
                           value="{{ old('sequence', 0) }}"
                           min="0"
                           class="{{ $inputClass }}">
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">Remarks</label>
                    <textarea name="remarks"
                              rows="3"
                              class="{{ $inputClass }}"
                              placeholder="Optional notes">{{ old('remarks') }}</textarea>
                </div>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap gap-3">
            <button type="submit"
                    class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                Save Product
            </button>

            <a href="{{ route('material-types.index') }}"
               class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const division = document.getElementById('activity_division_helper');
    const groupSearch = document.getElementById('product_group_search');
    const group = document.getElementById('material_product_group_id');
    const typeSearch = document.getElementById('product_type_search');
    const type = document.getElementById('material_product_type_id');
    const groupCount = document.getElementById('group_count');

    const divisionGroupMap = @json($divisionGroupMap);
    const selectedGroup = @json((string) old('material_product_group_id', ''));
    const selectedType = @json((string) old('material_product_type_id', ''));

    const allGroups = Array.from(group.options).filter(o => o.value).map(o => ({
        value: o.value,
        text: o.textContent.trim()
    }));

    const allTypes = Array.from(type.options).filter(o => o.value).map(o => ({
        value: o.value,
        text: o.textContent.trim(),
        group: o.dataset.group || ''
    }));

    function refreshGroups() {
        const divisionId = division.value;
        const term = groupSearch.value.trim().toLowerCase();
        const allowed = divisionId ? (divisionGroupMap[divisionId] || []).map(String) : null;
        const current = group.value || selectedGroup;

        group.innerHTML = '';
        group.add(new Option('Select Product Group', ''));

        const filtered = allGroups.filter(item => {
            const allowedByDivision = !allowed || allowed.includes(item.value);
            const allowedBySearch = !term || item.text.toLowerCase().includes(term);
            return allowedByDivision && allowedBySearch;
        });

        filtered.forEach(item => {
            const option = new Option(item.text, item.value);
            option.selected = item.value === current;
            group.add(option);
        });

        groupCount.textContent = filtered.length + ' matching Product Group' + (filtered.length === 1 ? '' : 's');

        if (current && !filtered.some(item => item.value === current)) {
            group.value = '';
        }

        refreshTypes();
    }

    function refreshTypes() {
        const groupId = group.value;
        const term = typeSearch.value.trim().toLowerCase();
        const current = type.value || selectedType;

        type.innerHTML = '';
        type.add(new Option('Select Product Type', ''));

        const filtered = allTypes.filter(item => {
            const allowedByGroup = groupId && item.group === groupId;
            const allowedBySearch = !term || item.text.toLowerCase().includes(term);
            return allowedByGroup && allowedBySearch;
        });

        filtered.forEach(item => {
            const option = new Option(item.text, item.value);
            option.dataset.group = item.group;
            option.selected = item.value === current;
            type.add(option);
        });

        if (current && !filtered.some(item => item.value === current)) {
            type.value = '';
        }
    }

    division.addEventListener('change', refreshGroups);
    groupSearch.addEventListener('input', refreshGroups);
    group.addEventListener('change', function () {
        type.value = '';
        refreshTypes();
    });
    typeSearch.addEventListener('input', refreshTypes);

    refreshGroups();
});
</script>

@endsection
