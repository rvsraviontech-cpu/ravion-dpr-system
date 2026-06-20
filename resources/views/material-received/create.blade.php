@extends('layouts.app')

@section('content')

@php
    $inputClass = 'border border-gray-300 p-2 rounded w-full text-sm';
    $labelClass = 'block font-semibold mb-1 text-sm text-gray-800';
    $sectionClass = 'bg-white rounded shadow p-5 mb-5';
@endphp

<h1 class="text-3xl font-bold mb-6">
    Add Material Received
</h1>

@if($errors->any())
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<form method="POST"
      action="{{ route('material-received.store') }}"
      class="pb-20">

    @csrf

    <div class="{{ $sectionClass }}">
        <h2 class="text-xl font-bold mb-4">
            Project & Location Details
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <div>
                <label class="{{ $labelClass }}">Project *</label>
                <select name="project_id"
                        id="project_id"
                        class="{{ $inputClass }}"
                        required>
                    <option value="">Select Project</option>

                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">
                            {{ $project->project_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="{{ $labelClass }}">Block</label>
                <select name="project_block_id"
                        id="project_block_id"
                        class="{{ $inputClass }}">
                    <option value="">Select Block</option>

                    @foreach($projectBlocks as $block)
                        <option value="{{ $block->id }}"
                                data-project="{{ $block->project_id }}">
                            {{ $block->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="{{ $labelClass }}">Floor</label>
                <select name="project_floor_id"
                        id="project_floor_id"
                        class="{{ $inputClass }}">
                    <option value="">Select Floor</option>

                    @foreach($projectFloors as $floor)
                        <option value="{{ $floor->id }}"
                                data-block="{{ $floor->project_block_id }}">
                            {{ $floor->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="{{ $labelClass }}">Unit</label>
                <select name="project_unit_id"
                        id="project_unit_id"
                        class="{{ $inputClass }}">
                    <option value="">Select Unit</option>

                    @foreach($projectUnits as $unit)
                        <option value="{{ $unit->id }}"
                                data-floor="{{ $unit->project_floor_id }}">
                            {{ $unit->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-4">
                <label class="{{ $labelClass }}">Storage Location</label>
                <input type="text"
                       name="storage_location"
                       class="{{ $inputClass }}"
                       placeholder="Example: Site Store / Block A Store / Cement Room">
            </div>

        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <h2 class="text-xl font-bold mb-4">
            Material Details
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <div>
                <label class="{{ $labelClass }}">Material Category *</label>
                <select id="material_category_id"
                        name="material_category_id"
                        class="{{ $inputClass }}"
                        required>
                    <option value="">Select Category</option>

                    @foreach($materialCategories as $category)
                        <option value="{{ $category->id }}">
                            {{ $category->category_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="{{ $labelClass }}">Material *</label>
                <select id="material_id"
                        name="material_id"
                        class="{{ $inputClass }}"
                        required>
                    <option value="">Select Material</option>

                    @foreach($materials as $material)
                        <option value="{{ $material->id }}"
                                data-category="{{ $material->material_category_id }}"
                                data-unit="{{ $material->unit }}"
                                data-brand="{{ $material->brandMaster?->brand_name }}"
                                data-specification="{{ $material->specification }}">
                            {{ $material->material_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="{{ $labelClass }}">Quantity Received *</label>
                <input type="number"
                       step="0.01"
                       name="quantity_received"
                       value="0"
                       class="{{ $inputClass }}"
                       required>
            </div>

            <div>
                <label class="{{ $labelClass }}">Unit</label>
                <input type="text"
                       id="unit_display"
                       class="{{ $inputClass }} bg-gray-100"
                       readonly>
            </div>

            <div>
                <label class="{{ $labelClass }}">Brand</label>
                <input type="text"
                       id="brand_display"
                       class="{{ $inputClass }} bg-gray-100"
                       readonly>
            </div>

            <div>
                <label class="{{ $labelClass }}">Specification</label>
                <input type="text"
                       id="specification_display"
                       class="{{ $inputClass }} bg-gray-100"
                       readonly>
            </div>

        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <h2 class="text-xl font-bold mb-4">
            Vendor & Transport Details
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <div>
                <label class="{{ $labelClass }}">Vendor</label>
                <select id="vendor_id"
                        name="vendor_id"
                        class="{{ $inputClass }}">
                    <option value="">Select Vendor</option>

                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}"
                                data-category="{{ $vendor->material_category_id }}">
                            {{ $vendor->vendor_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="{{ $labelClass }}">Vehicle Number</label>
                <input type="text"
                       name="vehicle_number"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="{{ $labelClass }}">Driver Name</label>
                <input type="text"
                       name="driver_name"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="{{ $labelClass }}">Challan Number</label>
                <input type="text"
                       name="challan_number"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="{{ $labelClass }}">Bill Number</label>
                <input type="text"
                       name="bill_number"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="{{ $labelClass }}">Received Date *</label>
                <input type="date"
                       name="received_date"
                       value="{{ date('Y-m-d') }}"
                       class="{{ $inputClass }}"
                       required>
            </div>

            <div>
                <label class="{{ $labelClass }}">Material Condition</label>
                <select name="material_condition"
                        class="{{ $inputClass }}">
                    <option value="Good">Good</option>
                    <option value="Damaged">Damaged</option>
                    <option value="Pending verification">Pending Verification</option>
                </select>
            </div>

        </div>
    </div>

    <details class="{{ $sectionClass }}">
        <summary class="text-xl font-bold cursor-pointer">
            Verification Quantities
        </summary>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">

            <div>
                <label class="{{ $labelClass }}">Accepted Qty</label>
                <input type="number"
                       step="0.01"
                       name="accepted_quantity"
                       value="0"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="{{ $labelClass }}">Short Qty</label>
                <input type="number"
                       step="0.01"
                       name="short_quantity"
                       value="0"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="{{ $labelClass }}">Damaged Qty</label>
                <input type="number"
                       step="0.01"
                       name="damaged_quantity"
                       value="0"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="{{ $labelClass }}">Rejected Qty</label>
                <input type="number"
                       step="0.01"
                       name="rejected_quantity"
                       value="0"
                       class="{{ $inputClass }}">
            </div>

        </div>
    </details>

    <div class="{{ $sectionClass }}">
        <label class="{{ $labelClass }}">Remarks</label>
        <textarea name="remarks"
                  rows="3"
                  class="{{ $inputClass }}"></textarea>
    </div>

    <div class="sticky bottom-0 bg-white border-t p-3 z-20 flex gap-3">
        <button type="submit"
                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded font-semibold">
            Save Material Entry
        </button>

        <a href="{{ route('material-received.index') }}"
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-3 rounded">
            Back
        </a>
    </div>

</form>

<script>
const projectSelect = document.getElementById('project_id');
const blockSelect = document.getElementById('project_block_id');
const floorSelect = document.getElementById('project_floor_id');
const unitSelect = document.getElementById('project_unit_id');

const originalBlocks = Array.from(blockSelect.querySelectorAll('option')).map(option => option.cloneNode(true));
const originalFloors = Array.from(floorSelect.querySelectorAll('option')).map(option => option.cloneNode(true));
const originalUnits = Array.from(unitSelect.querySelectorAll('option')).map(option => option.cloneNode(true));

function resetDropdown(select, placeholder) {
    select.innerHTML = '';
    select.add(new Option(placeholder, ''));
}

projectSelect.addEventListener('change', function () {
    const projectId = this.value;

    resetDropdown(blockSelect, 'Select Block');
    resetDropdown(floorSelect, 'Select Floor');
    resetDropdown(unitSelect, 'Select Unit');

    originalBlocks.forEach(function(option) {
        if (option.value !== '' && option.dataset.project === projectId) {
            blockSelect.add(option.cloneNode(true));
        }
    });
});

blockSelect.addEventListener('change', function () {
    const blockId = this.value;

    resetDropdown(floorSelect, 'Select Floor');
    resetDropdown(unitSelect, 'Select Unit');

    originalFloors.forEach(function(option) {
        if (option.value !== '' && option.dataset.block === blockId) {
            floorSelect.add(option.cloneNode(true));
        }
    });
});

floorSelect.addEventListener('change', function () {
    const floorId = this.value;

    resetDropdown(unitSelect, 'Select Unit');

    originalUnits.forEach(function(option) {
        if (option.value !== '' && option.dataset.floor === floorId) {
            unitSelect.add(option.cloneNode(true));
        }
    });
});

const categorySelect = document.getElementById('material_category_id');
const materialSelect = document.getElementById('material_id');
const vendorSelect = document.getElementById('vendor_id');

const originalMaterials = Array.from(materialSelect.querySelectorAll('option')).map(option => option.cloneNode(true));
const originalVendors = Array.from(vendorSelect.querySelectorAll('option')).map(option => option.cloneNode(true));

const unitDisplay = document.getElementById('unit_display');
const brandDisplay = document.getElementById('brand_display');
const specificationDisplay = document.getElementById('specification_display');

function clearMaterialDetails() {
    unitDisplay.value = '';
    brandDisplay.value = '';
    specificationDisplay.value = '';
}

categorySelect.addEventListener('change', function () {
    const categoryId = this.value;

    resetDropdown(materialSelect, 'Select Material');
    resetDropdown(vendorSelect, 'Select Vendor');
    clearMaterialDetails();

    originalMaterials.forEach(function(option) {
        if (option.value !== '' && option.dataset.category === categoryId) {
            materialSelect.add(option.cloneNode(true));
        }
    });

    originalVendors.forEach(function(option) {
        if (option.value !== '' && option.dataset.category === categoryId) {
            vendorSelect.add(option.cloneNode(true));
        }
    });
});

materialSelect.addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];

    unitDisplay.value = selected?.dataset?.unit || '';
    brandDisplay.value = selected?.dataset?.brand || '';
    specificationDisplay.value = selected?.dataset?.specification || '';
});
</script>

@endsection