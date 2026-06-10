@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Create Site Issue</h1>
        <p class="text-gray-500 mt-1">
            Raise site issues, delays, risks, dependencies and escalation items.
        </p>
    </div>

    <a href="{{ route('site-issues.index') }}"
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

<form method="POST" action="{{ route('site-issues.store') }}">
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
            <label class="block text-sm font-semibold mb-1">Issue Date</label>
            <input type="date"
                   name="issue_date"
                   value="{{ old('issue_date', now()->format('Y-m-d')) }}"
                   class="border p-2 rounded w-full"
                   required>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Issue Type</label>
            <select name="issue_type" class="border p-2 rounded w-full" required>
                <option value="">Select Type</option>
                <option value="Material Shortage">Material Shortage</option>
                <option value="Drawing Pending">Drawing Pending</option>
                <option value="Client Approval Pending">Client Approval Pending</option>
                <option value="Labour Shortage">Labour Shortage</option>
                <option value="Contractor Delay">Contractor Delay</option>
                <option value="Machinery Breakdown">Machinery Breakdown</option>
                <option value="Safety Issue">Safety Issue</option>
                <option value="Quality Issue">Quality Issue</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Priority</label>
            <select name="priority" class="border p-2 rounded w-full" required>
                <option value="Low">Low</option>
                <option value="Medium" selected>Medium</option>
                <option value="High">High</option>
                <option value="Critical">Critical</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Status</label>
            <select name="status" class="border p-2 rounded w-full" required>
                <option value="Open" selected>Open</option>
                <option value="In Progress">In Progress</option>
                <option value="Resolved">Resolved</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Target Closure Date</label>
            <input type="date"
                   name="target_closure_date"
                   value="{{ old('target_closure_date') }}"
                   class="border p-2 rounded w-full">
        </div>

    </div>

    <h2 class="text-xl font-bold text-gray-700 mb-4">Location</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

        <div>
            <label class="block text-sm font-semibold mb-1">Block</label>
            <select name="project_block_id" class="border p-2 rounded w-full">
                <option value="">Select Block</option>
                @foreach($projectBlocks as $block)
                    <option value="{{ $block->id }}">{{ $block->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Floor</label>
            <select name="project_floor_id" class="border p-2 rounded w-full">
                <option value="">Select Floor</option>
                @foreach($projectFloors as $floor)
                    <option value="{{ $floor->id }}">{{ $floor->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Unit</label>
            <select name="project_unit_id" class="border p-2 rounded w-full">
                <option value="">Select Unit</option>
                @foreach($projectUnits as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Room / Space</label>
            <select name="project_room_id" class="border p-2 rounded w-full">
                <option value="">Select Room</option>
                @foreach($projectRooms as $room)
                    <option value="{{ $room->id }}">{{ $room->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Sub-Space / Element</label>
            <select name="project_subspace_id" class="border p-2 rounded w-full">
                <option value="">Select Sub-Space</option>
                @foreach($projectSubspaces as $subspace)
                    <option value="{{ $subspace->id }}">{{ $subspace->name }}</option>
                @endforeach
            </select>
        </div>

    </div>

    <h2 class="text-xl font-bold text-gray-700 mb-4">Related Activity</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

        <div>
            <label class="block text-sm font-semibold mb-1">Activity Division</label>
            <select id="activity_division_id" class="border p-2 rounded w-full">
                <option value="">Select Activity Division</option>
                @foreach($activityDivisions as $division)
                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Activity</label>
            <select name="activity_id" id="activity_id" class="border p-2 rounded w-full">
                <option value="">Select Activity</option>
                @foreach($activities as $activity)
                    <option value="{{ $activity->id }}"
                            data-division="{{ $activity->activity_division_id }}">
                        {{ $activity->activity_name }}
                    </option>
                @endforeach
            </select>
        </div>

    </div>

    <h2 class="text-xl font-bold text-gray-700 mb-4">Issue Details</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

        <div>
            <label class="block text-sm font-semibold mb-1">Issue Title</label>
            <input type="text"
                   name="title"
                   value="{{ old('title') }}"
                   class="border p-2 rounded w-full"
                   required>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Responsible Person</label>
            <input type="text"
                   name="responsible_person"
                   value="{{ old('responsible_person') }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Root Cause</label>
            <input type="text"
                   name="root_cause"
                   value="{{ old('root_cause') }}"
                   class="border p-2 rounded w-full">
        </div>

    </div>

    <div class="mb-6">
        <label class="block text-sm font-semibold mb-1">Description</label>
        <textarea name="description"
                  rows="4"
                  class="border p-2 rounded w-full"
                  required>{{ old('description') }}</textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="escalated_to_pmo" value="1">
            <span>Escalate to PMO</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" name="escalated_to_management" value="1">
            <span>Escalate to Management / CEO</span>
        </label>
    </div>

    <div class="mb-6">
        <label class="block text-sm font-semibold mb-1">Remarks</label>
        <textarea name="remarks"
                  rows="3"
                  class="border p-2 rounded w-full">{{ old('remarks') }}</textarea>
    </div>

    <div>
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
            Save Issue
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
            if (option.value === '' || option.dataset.division === selectedDivision) {
                activityDropdown.appendChild(option.cloneNode(true));
            }
        });
    }

    divisionDropdown.addEventListener('change', filterActivities);
    filterActivities();
});
</script>

@endsection
