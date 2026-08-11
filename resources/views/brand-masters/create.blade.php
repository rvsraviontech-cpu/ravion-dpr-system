@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
    $labelClass = 'mb-1 block text-sm font-semibold text-gray-700';
@endphp

<div class="mx-auto max-w-5xl">

    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Add Material Brand
            </h1>

            <p class="mt-1 text-gray-500">
                Create a reusable brand for the selected Material Type.
            </p>
        </div>

        <a href="{{ route('brand-masters.index') }}"
           class="inline-flex items-center justify-center rounded-lg bg-gray-600 px-5 py-2.5 font-semibold text-white hover:bg-gray-700">
            Back
        </a>

    </div>

    @if($errors->any())
        <div class="mb-5 rounded-lg border border-red-300 bg-red-50 p-4 text-red-700">

            <ul class="ml-5 list-disc">

                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>

        </div>
    @endif

    <form method="POST"
          action="{{ route('brand-masters.store') }}">

        @csrf

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
                                {{ old('material_group') === $group ? 'selected' : '' }}>
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
                                {{ (string) old('material_type_id') === (string) $materialType->id ? 'selected' : '' }}>
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
                           value="{{ old('brand_name') }}"
                           class="{{ $inputClass }}"
                           placeholder="Example: Ambuja, UltraTech, JSW, Astral"
                           required>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Default Unit
                    </label>

                    <input type="text"
                           id="unit_display"
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
                           value="{{ old('sequence', 0) }}"
                           min="0"
                           class="{{ $inputClass }}">
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">
                        Remarks
                    </label>

                    <textarea name="remarks"
                              rows="4"
                              class="{{ $inputClass }}"
                              placeholder="Optional notes about this brand">{{ old('remarks') }}</textarea>
                </div>

            </div>

        </div>

        <div class="mt-6 flex flex-wrap gap-3">

            <button type="submit"
                    class="rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-700">
                Save Brand
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
    ).map(option => option.cloneNode(true));

    const oldTypeId = @json((string) old('material_type_id', ''));
    const oldGroup = @json((string) old('material_group', ''));

    function resetTypeSelect() {
        typeSelect.innerHTML = '';
        typeSelect.add(new Option('Select Material Type', ''));
    }

    function filterMaterialTypes(preserveSelection = false) {
        const selectedGroup = groupSelect.value;

        resetTypeSelect();

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
                    && clonedOption.value === oldTypeId
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
        filterMaterialTypes(false);
        typeSelect.value = '';
        updateUnit();
    });

    typeSelect.addEventListener('change', updateUnit);

    if (oldGroup !== '') {
        groupSelect.value = oldGroup;
    }

    filterMaterialTypes(true);
});
</script>

@endsection