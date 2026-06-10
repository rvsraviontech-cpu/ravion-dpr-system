@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Create Tomorrow Plan</h1>
        <p class="text-gray-500 mt-1">
            Plan tomorrow's work with labour, material, machinery and constraints.
        </p>
    </div>

    <a href="{{ route('tomorrow-plans.index') }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow">
        Back
    </a>
</div>

@if ($errors->any())
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
        <ul class="list-disc ml-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded shadow p-6">

<form method="POST" action="{{ route('tomorrow-plans.store') }}">
    @csrf

    <h2 class="text-xl font-bold text-gray-700 mb-4">Basic Details</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

        <div>
            <label class="block text-sm font-semibold mb-1">Project</label>
            <select name="project_id" class="border p-2 rounded w-full" required>
                <option value="">Select Project</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                        {{ $project->project_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Planned Date</label>
            <input type="date"
                   name="planned_date"
                   value="{{ old('planned_date', now()->addDay()->format('Y-m-d')) }}"
                   class="border p-2 rounded w-full"
                   required>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Priority</label>
            <select name="priority" class="border p-2 rounded w-full" required>
                <option value="Normal" {{ old('priority') == 'Normal' ? 'selected' : '' }}>Normal</option>
                <option value="Urgent" {{ old('priority') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                <option value="Critical" {{ old('priority') == 'Critical' ? 'selected' : '' }}>Critical</option>
            </select>
        </div>

    </div>

    <h2 class="text-xl font-bold text-gray-700 mb-4">Location</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

        <div>
            <label class="block text-sm font-semibold mb-1">Block</label>
            <select name="project_block_id" class="border p-2 rounded w-full">
                <option value="">Select Block</option>
                @foreach($projectBlocks as $block)
                    <option value="{{ $block->id }}" {{ old('project_block_id') == $block->id ? 'selected' : '' }}>
                        {{ $block->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Floor</label>
            <select name="project_floor_id" class="border p-2 rounded w-full">
                <option value="">Select Floor</option>
                @foreach($projectFloors as $floor)
                    <option value="{{ $floor->id }}" {{ old('project_floor_id') == $floor->id ? 'selected' : '' }}>
                        {{ $floor->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Unit</label>
            <select name="project_unit_id" class="border p-2 rounded w-full">
                <option value="">Select Unit</option>
                @foreach($projectUnits as $unit)
                    <option value="{{ $unit->id }}" {{ old('project_unit_id') == $unit->id ? 'selected' : '' }}>
                        {{ $unit->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Room / Space</label>
            <select name="project_room_id" class="border p-2 rounded w-full">
                <option value="">Select Room</option>
                @foreach($projectRooms as $room)
                    <option value="{{ $room->id }}" {{ old('project_room_id') == $room->id ? 'selected' : '' }}>
                        {{ $room->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Sub-Space / Element</label>
            <select name="project_subspace_id" class="border p-2 rounded w-full">
                <option value="">Select Sub-Space</option>
                @foreach($projectSubspaces as $subspace)
                    <option value="{{ $subspace->id }}" {{ old('project_subspace_id') == $subspace->id ? 'selected' : '' }}>
                        {{ $subspace->name }}
                    </option>
                @endforeach
            </select>
        </div>

    </div>

    <h2 class="text-xl font-bold text-gray-700 mb-4">Planned Work</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

        <div>
    <label class="block text-sm font-semibold mb-1">Activity Division</label>
    <select id="activity_division_id"
            class="border p-2 rounded w-full">
        <option value="">Select Activity Division</option>

        @foreach($activityDivisions as $division)
            <option value="{{ $division->id }}">
                {{ $division->name }}
            </option>
        @endforeach
    </select>
</div>

<div>
    <label class="block text-sm font-semibold mb-1">Activity</label>
    <select name="activity_id"
            id="activity_id"
            class="border p-2 rounded w-full"
            required>
        <option value="">Select Activity</option>

        @foreach($activities as $activity)
            <option value="{{ $activity->id }}"
                    data-division="{{ $activity->activity_division_id }}"
                    {{ old('activity_id') == $activity->id ? 'selected' : '' }}>
                {{ $activity->activity_name }}
            </option>
        @endforeach
    </select>
</div>

        <div>
            <label class="block text-sm font-semibold mb-1">Planned Quantity</label>
            <input type="number" step="0.01" name="planned_quantity"
                   value="{{ old('planned_quantity') }}"
                   class="border p-2 rounded w-full" required>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Unit</label>
            <input type="text" name="unit" value="{{ old('unit') }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Contractor</label>
            <select name="contractor_id" class="border p-2 rounded w-full">
                <option value="">Select Contractor</option>
                @foreach($contractors as $contractor)
                    <option value="{{ $contractor->id }}" {{ old('contractor_id') == $contractor->id ? 'selected' : '' }}>
                        {{ $contractor->contractor_name }}
                    </option>
                @endforeach
            </select>
        </div>

    </div>

    <h2 class="text-xl font-bold text-gray-700 mb-4">Labour Requirement</h2>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">

        <div>
            <label class="block text-sm font-semibold mb-1">Total Planned Labour</label>
            <input type="number" name="planned_labour" value="{{ old('planned_labour', 0) }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Skilled Labour</label>
            <input type="number" name="required_skilled_labour" value="{{ old('required_skilled_labour', 0) }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Semi-Skilled Labour</label>
            <input type="number" name="required_semiskilled_labour" value="{{ old('required_semiskilled_labour', 0) }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Helpers</label>
            <input type="number" name="required_helpers" value="{{ old('required_helpers', 0) }}"
                   class="border p-2 rounded w-full">
        </div>

    </div>

    <h2 class="text-xl font-bold text-gray-700 mb-4">Resources & Dependencies</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

        <div>
            <label class="block text-sm font-semibold mb-1">Materials Required</label>
            <textarea name="materials_required" rows="3"
                      class="border p-2 rounded w-full">{{ old('materials_required') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Machinery Required</label>
            <textarea name="machinery_required" rows="3"
                      class="border p-2 rounded w-full">{{ old('machinery_required') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Drawing Required?</label>
            <select name="drawing_required" class="border p-2 rounded w-full" required>
                <option value="0" {{ old('drawing_required') == '0' ? 'selected' : '' }}>No</option>
                <option value="1" {{ old('drawing_required') == '1' ? 'selected' : '' }}>Yes</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Client Approval Required?</label>
            <select name="client_approval_required" class="border p-2 rounded w-full" required>
                <option value="0" {{ old('client_approval_required') == '0' ? 'selected' : '' }}>No</option>
                <option value="1" {{ old('client_approval_required') == '1' ? 'selected' : '' }}>Yes</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Responsible Person</label>
            <input type="text" name="responsible_person"
                   value="{{ old('responsible_person') }}"
                   class="border p-2 rounded w-full">
        </div>

    </div>

    <h2 class="text-xl font-bold text-gray-700 mb-4">Risk / Remarks</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div>
            <label class="block text-sm font-semibold mb-1">Risks / Constraints</label>
            <textarea name="risks_constraints" rows="4"
                      class="border p-2 rounded w-full">{{ old('risks_constraints') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Remarks</label>
            <textarea name="remarks" rows="4"
                      class="border p-2 rounded w-full">{{ old('remarks') }}</textarea>
        </div>

    </div>

    <div class="mt-6">
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
            Save Tomorrow Plan
        </button>
    </div>

</form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const divisionDropdown = document.getElementById('activity_division_id');
    const activityDropdown = document.getElementById('activity_id');

    const allActivities = Array.from(activityDropdown.options);

    function filterActivities() {
        const selectedDivision = divisionDropdown.value;

        activityDropdown.innerHTML = '<option value="">Select Activity</option>';

        allActivities.forEach(option => {
            if (
                option.value === '' ||
                option.dataset.division === selectedDivision
            ) {
                activityDropdown.appendChild(option.cloneNode(true));
            }
        });
    }

    divisionDropdown.addEventListener('change', filterActivities);

    filterActivities();
});
</script>

@endsection