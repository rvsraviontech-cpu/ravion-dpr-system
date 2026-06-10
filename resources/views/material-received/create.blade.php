@extends('layouts.app')

@section('content')

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
      class="bg-white p-6 rounded shadow">

    @csrf

    <h2 class="text-xl font-bold mb-4">
        Project & Location Details
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

        <div>
            <label class="block font-semibold mb-1">Project</label>
            <select name="project_id"
                    id="project_id"
                    class="border p-2 rounded w-full"
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
            <label class="block font-semibold mb-1">Block</label>
            <select name="project_block_id"
                    id="project_block_id"
                    class="border p-2 rounded w-full">
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
            <label class="block font-semibold mb-1">Floor</label>
            <select name="project_floor_id"
                    id="project_floor_id"
                    class="border p-2 rounded w-full">
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
            <label class="block font-semibold mb-1">Unit</label>
            <select name="project_unit_id"
                    id="project_unit_id"
                    class="border p-2 rounded w-full">
                <option value="">Select Unit</option>

                @foreach($projectUnits as $unit)
                    <option value="{{ $unit->id }}"
                            data-floor="{{ $unit->project_floor_id }}">
                        {{ $unit->name }}
                    </option>
                @endforeach
            </select>
        </div>

    </div>

    <div class="mb-6">
        <label class="block font-semibold mb-1">
            Storage Location
        </label>

        <input type="text"
               name="storage_location"
               class="border p-2 rounded w-full">
    </div>

    <h2 class="text-xl font-bold mb-4">
        Material Details
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

        <div>
            <label class="block font-semibold mb-1">
                Material Category
            </label>

            <select id="material_category_id"
                    name="material_category_id"
                    class="border p-2 rounded w-full">

                <option value="">Select Category</option>

                @foreach($materialCategories as $category)
                    <option value="{{ $category->id }}">
                        {{ $category->category_name }}
                    </option>
                @endforeach

            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">
                Material
            </label>

            <select id="material_id"
                    name="material_id"
                    class="border p-2 rounded w-full">

                <option value="">Select Material</option>

                @foreach($materials as $material)
                    <option value="{{ $material->id }}"
                            data-category="{{ $material->material_category_id }}">
                        {{ $material->material_name }}
                    </option>
                @endforeach

            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">
                Quantity Received
            </label>

            <input type="number"
                   step="0.01"
                   name="quantity_received"
                   value="0"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">
                Unit
            </label>

            <input type="text"
                   name="unit"
                   class="border p-2 rounded w-full">
        </div>

    </div>

    <h2 class="text-xl font-bold mb-4">
        Vendor & Transport Details
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

        <div>
            <label class="block font-semibold mb-1">
                Vendor Name
            </label>

            <input type="text"
                   name="vendor_name"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">
                Vehicle Number
            </label>

            <input type="text"
                   name="vehicle_number"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">
                Driver Name
            </label>

            <input type="text"
                   name="driver_name"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">
                Challan Number
            </label>

            <input type="text"
                   name="challan_number"
                   class="border p-2 rounded w-full">
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        <div>
            <label class="block font-semibold mb-1">
                Bill Number
            </label>

            <input type="text"
                   name="bill_number"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">
                Received Date
            </label>

            <input type="date"
                   name="received_date"
                   value="{{ date('Y-m-d') }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">
                Material Condition
            </label>

            <select name="material_condition"
                    class="border p-2 rounded w-full">
                <option value="Good">Good</option>
                <option value="Damaged">Damaged</option>
                <option value="Pending verification">Pending Verification</option>
            </select>
        </div>

    </div>

    <h2 class="text-xl font-bold mb-4">
        Verification Quantities
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

        <div>
            <label class="block font-semibold mb-1">Accepted Qty</label>
            <input type="number" step="0.01" name="accepted_quantity" value="0" class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">Short Qty</label>
            <input type="number" step="0.01" name="short_quantity" value="0" class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">Damaged Qty</label>
            <input type="number" step="0.01" name="damaged_quantity" value="0" class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">Rejected Qty</label>
            <input type="number" step="0.01" name="rejected_quantity" value="0" class="border p-2 rounded w-full">
        </div>

    </div>

    <div class="mb-6">
        <label class="block font-semibold mb-1">
            Remarks
        </label>

        <textarea name="remarks"
                  rows="3"
                  class="border p-2 rounded w-full"></textarea>
    </div>

    <div class="flex gap-3">

        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded">
            Save Material Entry
        </button>

        <a href="{{ route('material-received.index') }}"
           class="bg-gray-500 text-white px-4 py-2 rounded">
            Back
        </a>

    </div>

</form>

<script>

const categorySelect = document.getElementById('material_category_id');
const materialSelect = document.getElementById('material_id');

const originalMaterials = Array.from(
    materialSelect.querySelectorAll('option')
);

categorySelect.addEventListener('change', function () {

    const categoryId = this.value;

    materialSelect.innerHTML = '';
    materialSelect.add(new Option('Select Material', ''));

    originalMaterials.forEach(function(option) {

        if (
            option.value !== '' &&
            option.dataset.category === categoryId
        ) {
            materialSelect.add(option.cloneNode(true));
        }

    });

});

</script>

@endsection