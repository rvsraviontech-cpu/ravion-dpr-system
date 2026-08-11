@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
    $labelClass = 'mb-1 block text-sm font-semibold text-gray-700';

    $selectedMaterialTypeId = old(
        'material_type_id',
        $brandMaster->material_type_id
    );

    $selectedMaterialType = $materialTypes->firstWhere(
        'id',
        (int) $selectedMaterialTypeId
    );

    $selectedGroup = old(
        'material_group',
        $selectedMaterialType?->material_group
    );
@endphp

<div class="mx-auto max-w-5xl">

    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Edit Material Brand
            </h1>

            <p class="mt-1 text-gray-500">
                Update the reusable brand and its Material Type.
            </p>
        </div>

        <a href="{{ route('brand-masters.index') }}"
           class="inline-flex items-center justify-center rounded-lg bg-gray-600 px-5 py-2.5 font-semibold text-white hover:bg-gray-700">
            Back
        </a>

    </div>

    @if($errors->any())
        <div class="mb-5 rounded-lg border border-red-300 bg-red-50 p-4 text-red-700">

            <p class="mb-2 font-semibold">
                Please correct the following:
            </p>

            <ul class="ml-5 list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>
    @endif

    @if(!$brandMaster->material_type_id)
        <div class="mb-5 rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-3 text-yellow-800">
            This is a legacy Brand record. Please select its Material Type before saving.
        </div>
    @endif

    <form method="POST"
          action="{{ route('brand-masters.update', $brandMaster) }}">

        @csrf
        @method('PUT')

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <h2 class="mb-5 text-xl font-bold text-gray-800">
                Brand Details
            </h2>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                <div>
                    <label class="{{ $labelClass }}">
                        Material Group
                    </label>

                    <select id="material_group"
                            class="{{ $inputClass }}">

                        <option value="">
                            Select Material Group
                        </option>

                        @foreach($materialGroups as $group)
                            <option value="{{ $group }}"
                                {{ $selectedGroup === $group ? 'selected' : '' }}>
                                {{ $group }}
                            </option>
                        @endforeach

                    </select>

                    <p class="mt-1 text-xs text-gray-500">
                        Used only to filter the Material Type dropdown.
                    </p>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Material Type <span class="text-red-500">*</span>
                    </label>

                    <select id="material_type_id"
                            name="material_type_id"
                            class="{{ $inputClass }}"
                            required>

                        <option value="">
                            Select Material Type
                        </option>

                        @foreach($materialTypes as $materialType)
                            <option value="{{ $materialType->id }}"
                                    data-group="{{ $materialType->material_group }}"
                                    data-unit="{{ $materialType->unit?->unit_name }}"
                                {{ (string) $selectedMaterialTypeId === (string) $materialType->id ? 'selected' : '' }}>
                                {{ $materialType->material_type_name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Brand Name <span class="text-red-500">*</span>
                    </label>

                    <input type="text"
                           name="brand_name"
                           value="{{ old('brand_name', $brandMaster->brand_name) }}"
                           class="{{ $inputClass }}"
                           placeholder="Example: Ambuja, UltraTech, JSW"
                           required>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Default Unit
                    </label>

                    <input type="text"
                           id="unit_display"
                           value="{{ $selectedMaterialType?->unit?->unit_name }}"
                           class="{{ $inputClass }} bg-gray-100"
                           readonly
                           placeholder="Auto-filled from Material Type">
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Display Sequence
                    </label>

                    <input type="number"
                           name="sequence"
                           value="{{ old('sequence', $brandMaster->sequence) }}"
                           min="0"
                           class="{{ $inputClass }}">
                </div>

                <div class="flex items-center">
                    <label class="inline-flex items-center gap-3">

                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                               {{ old('is_active', $brandMaster->is_active) ? 'checked' : '' }}>

                        <span class="text-sm font-semibold text-gray-700">
                            Active
                        </span>

                    </label>
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">
                        Remarks
                    </label>

                    <textarea name="remarks"
                              rows="4"
                              class="{{ $inputClass }}"
                              placeholder="Optional notes about this brand">{{ old('remarks', $brandMaster->remarks) }}</textarea>
                </div>

            </div>

        </div>

        <div class="mt-6 flex flex-wrap gap-3">

            <button type="submit"
                    class="rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-700">
                Update Brand
            </button>

            <a href="{{ route('brand-masters.index') }}"
               class="rounded-lg bg-gray-500 px-6 py-3 font-semibold text-white hover:bg-gray-600">
                Cancel
            </a>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const groupSelect = document.getElementById('material_group');
    const typeSelect = document.getElementById('material_type_id');
    const unitDisplay = document.getElementById('unit_display');

    if (!groupSelect || !typeSelect || !unitDisplay) {
        return;
    }

    const originalOptions = Array.from(
        typeSelect.querySelectorAll('option')
    ).map(function (option) {
        return option.cloneNode(true);
    });

    const selectedTypeId =
        @json((string) $selectedMaterialTypeId);

    function rebuildMaterialTypes(preserveSelection = true) {
        const selectedGroup = groupSelect.value;

        typeSelect.innerHTML = '';
        typeSelect.add(new Option('Select Material Type', ''));

        originalOptions.forEach(function (option) {
            if (option.value === '') {
                return;
            }

            if (
                selectedGroup === ''
                || option.dataset.group === selectedGroup
            ) {
                const clonedOption = option.cloneNode(true);

                if (
                    preserveSelection
                    && clonedOption.value === selectedTypeId
                ) {
                    clonedOption.selected = true;
                }

                typeSelect.add(clonedOption);
            }
        });

        updateUnit();
    }

    function updateUnit() {
        const selectedOption =
            typeSelect.options[typeSelect.selectedIndex];

        unitDisplay.value =
            selectedOption?.dataset?.unit || '';
    }

    groupSelect.addEventListener('change', function () {
        rebuildMaterialTypes(false);
        typeSelect.value = '';
        updateUnit();
    });

    typeSelect.addEventListener('change', updateUnit);

    rebuildMaterialTypes(true);
});
</script>

@endsection