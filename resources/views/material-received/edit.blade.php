@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Edit Material Received
</h1>

@if($errors->any())
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<form method="POST"
      action="{{ route('material-received.update', $materialReceived) }}"
      class="bg-white p-6 rounded shadow">

    @csrf
    @method('PUT')

    <h2 class="text-xl font-bold mb-4">
        Project & Location Details
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

        <div>
            <label class="block font-semibold mb-1">Project</label>

            <select name="project_id"
                    class="border p-2 rounded w-full"
                    required>

                @foreach($projects as $project)
                    <option value="{{ $project->id }}"
                        {{ $materialReceived->project_id == $project->id ? 'selected' : '' }}>
                        {{ $project->project_name }}
                    </option>
                @endforeach

            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Block</label>

            <select name="project_block_id"
                    class="border p-2 rounded w-full">

                <option value="">Select Block</option>

                @foreach($projectBlocks as $block)
                    <option value="{{ $block->id }}"
                        {{ $materialReceived->project_block_id == $block->id ? 'selected' : '' }}>
                        {{ $block->name }}
                    </option>
                @endforeach

            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Floor</label>

            <select name="project_floor_id"
                    class="border p-2 rounded w-full">

                <option value="">Select Floor</option>

                @foreach($projectFloors as $floor)
                    <option value="{{ $floor->id }}"
                        {{ $materialReceived->project_floor_id == $floor->id ? 'selected' : '' }}>
                        {{ $floor->name }}
                    </option>
                @endforeach

            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Unit</label>

            <select name="project_unit_id"
                    class="border p-2 rounded w-full">

                <option value="">Select Unit</option>

                @foreach($projectUnits as $unit)
                    <option value="{{ $unit->id }}"
                        {{ $materialReceived->project_unit_id == $unit->id ? 'selected' : '' }}>
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
               value="{{ old('storage_location', $materialReceived->storage_location) }}"
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
                    <option value="{{ $category->id }}"
                        {{ $materialReceived->material_category_id == $category->id ? 'selected' : '' }}>
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
                            data-category="{{ $material->material_category_id }}"
                        {{ $materialReceived->material_id == $material->id ? 'selected' : '' }}>
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
                   value="{{ old('quantity_received', $materialReceived->quantity_received) }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">
                Unit
            </label>

            <input type="text"
                   name="unit"
                   value="{{ old('unit', $materialReceived->unit) }}"
                   class="border p-2 rounded w-full">
        </div>

    </div>

    <h2 class="text-xl font-bold mb-4">
        Vendor & Transport Details
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

        <input type="text" name="vendor_name"
               value="{{ old('vendor_name', $materialReceived->vendor_name) }}"
               placeholder="Vendor Name"
               class="border p-2 rounded">

        <input type="text" name="vehicle_number"
               value="{{ old('vehicle_number', $materialReceived->vehicle_number) }}"
               placeholder="Vehicle Number"
               class="border p-2 rounded">

        <input type="text" name="driver_name"
               value="{{ old('driver_name', $materialReceived->driver_name) }}"
               placeholder="Driver Name"
               class="border p-2 rounded">

        <input type="text" name="challan_number"
               value="{{ old('challan_number', $materialReceived->challan_number) }}"
               placeholder="Challan Number"
               class="border p-2 rounded">

    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        <input type="text" name="bill_number"
               value="{{ old('bill_number', $materialReceived->bill_number) }}"
               placeholder="Bill Number"
               class="border p-2 rounded">

        <input type="date"
               name="received_date"
               value="{{ old('received_date', $materialReceived->received_date) }}"
               class="border p-2 rounded">

        <select name="material_condition"
                class="border p-2 rounded">

            <option value="Good" {{ $materialReceived->material_condition == 'Good' ? 'selected' : '' }}>Good</option>

            <option value="Damaged" {{ $materialReceived->material_condition == 'Damaged' ? 'selected' : '' }}>Damaged</option>

            <option value="Pending verification"
                {{ $materialReceived->material_condition == 'Pending verification' ? 'selected' : '' }}>
                Pending Verification
            </option>

        </select>

    </div>

    <div class="mb-6">
        <label class="block font-semibold mb-1">
            Remarks
        </label>

        <textarea name="remarks"
                  rows="3"
                  class="border p-2 rounded w-full">{{ old('remarks', $materialReceived->remarks) }}</textarea>
    </div>

    <div class="flex gap-3">

        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded">
            Update Entry
        </button>

        <a href="{{ route('material-received.index') }}"
           class="bg-gray-500 text-white px-4 py-2 rounded">
            Back
        </a>

    </div>

</form>

@endsection