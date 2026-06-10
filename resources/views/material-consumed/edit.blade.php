@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Edit Material Consumed
</h1>

@if($errors->any())
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<form method="POST"
      action="{{ route('material-consumed.update', $materialConsumed) }}"
      class="bg-white p-6 rounded shadow">

    @csrf
    @method('PUT')

    <h2 class="text-xl font-bold mb-4">Project & Location</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        <div>
            <label class="block font-semibold mb-1">Project</label>
            <select id="project_id" name="project_id" class="border p-2 rounded w-full" required>
                <option value="">Select Project</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}"
                        {{ $materialConsumed->project_id == $project->id ? 'selected' : '' }}>
                        {{ $project->project_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Block</label>
            <select id="project_block_id" name="project_block_id" class="border p-2 rounded w-full">
                <option value="">Select Block</option>
                @foreach($projectBlocks as $block)
                    <option value="{{ $block->id }}"
                            data-project="{{ $block->project_id }}"
                            {{ $materialConsumed->project_block_id == $block->id ? 'selected' : '' }}>
                        {{ $block->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Floor</label>
            <select id="project_floor_id" name="project_floor_id" class="border p-2 rounded w-full">
                <option value="">Select Floor</option>
                @foreach($projectFloors as $floor)
                    <option value="{{ $floor->id }}"
                            data-block="{{ $floor->project_block_id }}"
                            {{ $materialConsumed->project_floor_id == $floor->id ? 'selected' : '' }}>
                        {{ $floor->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Unit</label>
            <select id="project_unit_id" name="project_unit_id" class="border p-2 rounded w-full">
                <option value="">Select Unit</option>
                @foreach($projectUnits as $unit)
                    <option value="{{ $unit->id }}"
                            data-floor="{{ $unit->project_floor_id }}"
                            {{ $materialConsumed->project_unit_id == $unit->id ? 'selected' : '' }}>
                        {{ $unit->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Room</label>
            <select id="project_room_id" name="project_room_id" class="border p-2 rounded w-full">
                <option value="">Select Room</option>
                @foreach($projectRooms as $room)
                    <option value="{{ $room->id }}"
                            data-unit="{{ $room->project_unit_id }}"
                            {{ $materialConsumed->project_room_id == $room->id ? 'selected' : '' }}>
                        {{ $room->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Sub-space</label>
            <select id="project_subspace_id" name="project_subspace_id" class="border p-2 rounded w-full">
                <option value="">Select Sub-space</option>
                @foreach($projectSubspaces as $subspace)
                    <option value="{{ $subspace->id }}"
                            data-room="{{ $subspace->project_room_id }}"
                            {{ $materialConsumed->project_subspace_id == $subspace->id ? 'selected' : '' }}>
                        {{ $subspace->name }}
                    </option>
                @endforeach
            </select>
        </div>

    </div>

    <h2 class="text-xl font-bold mb-4">Activity Details</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        <div>
            <label class="block font-semibold mb-1">Activity Division</label>
            <select id="activity_division_id" name="activity_division_id" class="border p-2 rounded w-full">
                <option value="">Select Activity Division</option>
                @foreach($activityDivisions as $division)
                    <option value="{{ $division->id }}"
                        {{ $materialConsumed->activity_division_id == $division->id ? 'selected' : '' }}>
                        {{ $division->code }} - {{ $division->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Activity</label>
            <select id="activity_id" name="activity_id" class="border p-2 rounded w-full">
                <option value="">Select Activity</option>
                @foreach($activities as $activity)
                    <option value="{{ $activity->id }}"
                            data-division="{{ $activity->activity_division_id }}"
                            {{ $materialConsumed->activity_id == $activity->id ? 'selected' : '' }}>
                        {{ $activity->activity_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Contractor</label>
            <select name="contractor_id" class="border p-2 rounded w-full">
                <option value="">Select Contractor</option>
                @foreach($contractors as $contractor)
                    <option value="{{ $contractor->id }}"
                        {{ $materialConsumed->contractor_id == $contractor->id ? 'selected' : '' }}>
                        {{ $contractor->contractor_name }}
                    </option>
                @endforeach
            </select>
        </div>

    </div>

    <h2 class="text-xl font-bold mb-4">Material Details</h2>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

        <div>
            <label class="block font-semibold mb-1">Material Category</label>
            <select id="material_category_id" name="material_category_id" class="border p-2 rounded w-full" required>
                <option value="">Select Category</option>
                @foreach($materialCategories as $category)
                    <option value="{{ $category->id }}"
                        {{ $materialConsumed->material_category_id == $category->id ? 'selected' : '' }}>
                        {{ $category->category_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Material</label>
            <select id="material_id" name="material_id" class="border p-2 rounded w-full" required>
                <option value="">Select Material</option>
                @foreach($materials as $material)
                    <option value="{{ $material->id }}"
                            data-category="{{ $material->material_category_id }}"
                            {{ $materialConsumed->material_id == $material->id ? 'selected' : '' }}>
                        {{ $material->material_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Quantity Consumed</label>
            <input type="number"
                   step="0.01"
                   name="quantity_consumed"
                   value="{{ old('quantity_consumed', $materialConsumed->quantity_consumed) }}"
                   class="border p-2 rounded w-full"
                   required>
        </div>

        <div>
            <label class="block font-semibold mb-1">Unit</label>
            <input type="text"
                   name="unit"
                   value="{{ old('unit', $materialConsumed->unit) }}"
                   class="border p-2 rounded w-full">
        </div>

    </div>

    <h2 class="text-xl font-bold mb-4">Output & Wastage</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        <div>
            <label class="block font-semibold mb-1">Related Work Output Quantity</label>
            <input type="number"
                   step="0.01"
                   name="related_work_output_quantity"
                   value="{{ old('related_work_output_quantity', $materialConsumed->related_work_output_quantity) }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">Wastage Quantity</label>
            <input type="number"
                   step="0.01"
                   name="wastage_quantity"
                   value="{{ old('wastage_quantity', $materialConsumed->wastage_quantity) }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">Consumed Date</label>
            <input type="date"
                   name="consumed_date"
                   value="{{ old('consumed_date', $materialConsumed->consumed_date) }}"
                   class="border p-2 rounded w-full"
                   required>
        </div>

    </div>

    <div class="mb-6">
        <label class="block font-semibold mb-1">Wastage Reason</label>
        <input type="text"
               name="wastage_reason"
               value="{{ old('wastage_reason', $materialConsumed->wastage_reason) }}"
               class="border p-2 rounded w-full">
    </div>

    <div class="mb-6">
        <label class="block font-semibold mb-1">Remarks</label>
        <textarea name="remarks"
                  rows="3"
                  class="border p-2 rounded w-full">{{ old('remarks', $materialConsumed->remarks) }}</textarea>
    </div>

    <div class="flex gap-3">
        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded">
            Update Consumption Entry
        </button>

        <a href="{{ route('material-consumed.index') }}"
           class="bg-gray-500 text-white px-4 py-2 rounded">
            Back
        </a>
    </div>

</form>

@endsection