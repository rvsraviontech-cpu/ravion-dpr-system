@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Edit Site Issue</h1>
        <p class="text-gray-500 mt-1">
            Update issue status, responsibility, escalation and resolution.
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

<form method="POST" action="{{ route('site-issues.update', $siteIssue) }}">
    @csrf
    @method('PUT')

    <h2 class="text-xl font-bold text-gray-700 mb-4">Basic Details</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

        <div>
            <label class="block text-sm font-semibold mb-1">Project</label>
            <select name="project_id" class="border p-2 rounded w-full" required>
                <option value="">Select Project</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}"
                        {{ old('project_id', $siteIssue->project_id) == $project->id ? 'selected' : '' }}>
                        {{ $project->project_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Issue Date</label>
            <input type="date"
                   name="issue_date"
                   value="{{ old('issue_date', $siteIssue->issue_date) }}"
                   class="border p-2 rounded w-full"
                   required>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Issue Type</label>
            <select name="issue_type" class="border p-2 rounded w-full" required>
                @foreach([
                    'Material Shortage',
                    'Drawing Pending',
                    'Client Approval Pending',
                    'Labour Shortage',
                    'Contractor Delay',
                    'Machinery Breakdown',
                    'Safety Issue',
                    'Quality Issue',
                    'Other'
                ] as $type)
                    <option value="{{ $type }}"
                        {{ old('issue_type', $siteIssue->issue_type) == $type ? 'selected' : '' }}>
                        {{ $type }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Priority</label>
            <select name="priority" class="border p-2 rounded w-full" required>
                @foreach(['Low', 'Medium', 'High', 'Critical'] as $priority)
                    <option value="{{ $priority }}"
                        {{ old('priority', $siteIssue->priority) == $priority ? 'selected' : '' }}>
                        {{ $priority }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Status</label>
            <select name="status" class="border p-2 rounded w-full" required>
                @foreach(['Open', 'In Progress', 'Resolved'] as $status)
                    <option value="{{ $status }}"
                        {{ old('status', $siteIssue->status) == $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Target Closure Date</label>
            <input type="date"
                   name="target_closure_date"
                   value="{{ old('target_closure_date', $siteIssue->target_closure_date) }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Actual Closure Date</label>
            <input type="date"
                   name="actual_closure_date"
                   value="{{ old('actual_closure_date', $siteIssue->actual_closure_date) }}"
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
                    <option value="{{ $block->id }}"
                        {{ old('project_block_id', $siteIssue->project_block_id) == $block->id ? 'selected' : '' }}>
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
                    <option value="{{ $floor->id }}"
                        {{ old('project_floor_id', $siteIssue->project_floor_id) == $floor->id ? 'selected' : '' }}>
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
                    <option value="{{ $unit->id }}"
                        {{ old('project_unit_id', $siteIssue->project_unit_id) == $unit->id ? 'selected' : '' }}>
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
                    <option value="{{ $room->id }}"
                        {{ old('project_room_id', $siteIssue->project_room_id) == $room->id ? 'selected' : '' }}>
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
                    <option value="{{ $subspace->id }}"
                        {{ old('project_subspace_id', $siteIssue->project_subspace_id) == $subspace->id ? 'selected' : '' }}>
                        {{ $subspace->name }}
                    </option>
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
                    <option value="{{ $division->id }}">
                        {{ $division->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Activity</label>
            <select name="activity_id" id="activity_id" class="border p-2 rounded w-full">
                <option value="">Select Activity</option>
                @foreach($activities as $activity)
                    <option value="{{ $activity->id }}"
                            data-division="{{ $activity->activity_division_id }}"
                            {{ old('activity_id', $siteIssue->activity_id) == $activity->id ? 'selected' : '' }}>
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
                   value="{{ old('title', $siteIssue->title) }}"
                   class="border p-2 rounded w-full"
                   required>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Responsible Person</label>
            <input type="text"
                   name="responsible_person"
                   value="{{ old('responsible_person', $siteIssue->responsible_person) }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Root Cause</label>
            <input type="text"
                   name="root_cause"
                   value="{{ old('root_cause', $siteIssue->root_cause) }}"
                   class="border p-2 rounded w-full">
        </div>

    </div>

    <div class="mb-6">
        <label class="block text-sm font-semibold mb-1">Description</label>
        <textarea name="description"
                  rows="4"
                  class="border p-2 rounded w-full"
                  required>{{ old('description', $siteIssue->description) }}</textarea>
    </div>

    <div class="mb-6">
        <label class="block text-sm font-semibold mb-1">Resolution</label>
        <textarea name="resolution"
                  rows="4"
                  class="border p-2 rounded w-full">{{ old('resolution', $siteIssue->resolution) }}</textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <label class="flex items-center gap-2">
            <input type="checkbox"
                   name="escalated_to_pmo"
                   value="1"
                   {{ old('escalated_to_pmo', $siteIssue->escalated_to_pmo) ? 'checked' : '' }}>
            <span>Escalate to PMO</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox"
                   name="escalated_to_management"
                   value="1"
                   {{ old('escalated_to_management', $siteIssue->escalated_to_management) ? 'checked' : '' }}>
            <span>Escalate to Management / CEO</span>
        </label>
    </div>

    <div class="mb-6">
        <label class="block text-sm font-semibold mb-1">Remarks</label>
        <textarea name="remarks"
                  rows="3"
                  class="border p-2 rounded w-full">{{ old('remarks', $siteIssue->remarks) }}</textarea>
    </div>

    <div>
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
            Update Issue
        </button>
    </div>

</form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const divisionDropdown = document.getElementById('activity_division_id');
    const activityDropdown = document.getElementById('activity_id');
    const selectedActivityId = "{{ old('activity_id', $siteIssue->activity_id) }}";

    const allActivities = Array.from(activityDropdown.options);

    function filterActivities() {
        const selectedDivision = divisionDropdown.value;

        activityDropdown.innerHTML = '<option value="">Select Activity</option>';

        allActivities.forEach(option => {
            if (
                option.value === '' ||
                option.dataset.division === selectedDivision
            ) {
                const clonedOption = option.cloneNode(true);

                if (clonedOption.value === selectedActivityId) {
                    clonedOption.selected = true;
                }

                activityDropdown.appendChild(clonedOption);
            }
        });
    }

    const selectedOption = allActivities.find(option => option.value === selectedActivityId);

    if (selectedOption && selectedOption.dataset.division) {
        divisionDropdown.value = selectedOption.dataset.division;
    }

    divisionDropdown.addEventListener('change', filterActivities);

    filterActivities();
});
</script>

@endsection