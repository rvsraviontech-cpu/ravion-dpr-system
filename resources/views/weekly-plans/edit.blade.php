@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Edit Weekly Plan</h1>
        <p class="text-gray-500 mt-1">Update weekly planning target, resources and status.</p>
    </div>

    <a href="{{ route('weekly-plans.index') }}"
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

<form method="POST" action="{{ route('weekly-plans.update', $weeklyPlan) }}">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

        <div>
            <label class="block text-sm font-semibold mb-1">Project</label>
            <select name="project_id" class="border p-2 rounded w-full" required>
                <option value="">Select Project</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}"
                        {{ old('project_id', $weeklyPlan->project_id) == $project->id ? 'selected' : '' }}>
                        {{ $project->project_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Week Start Date</label>
            <input type="date"
                   name="week_start_date"
                   value="{{ old('week_start_date', $weeklyPlan->week_start_date) }}"
                   class="border p-2 rounded w-full"
                   required>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Week End Date</label>
            <input type="date"
                   name="week_end_date"
                   value="{{ old('week_end_date', $weeklyPlan->week_end_date) }}"
                   class="border p-2 rounded w-full"
                   required>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Assigned Engineer</label>
            <select name="user_id" class="border p-2 rounded w-full">
                <option value="">Select Engineer</option>
                @foreach($engineers as $engineer)
                    <option value="{{ $engineer->id }}"
                        {{ old('user_id', $weeklyPlan->user_id) == $engineer->id ? 'selected' : '' }}>
                        {{ $engineer->name }}
                    </option>
                @endforeach
            </select>
        </div>

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
            <select name="activity_id"
                    id="activity_id"
                    class="border p-2 rounded w-full"
                    required>
                <option value="">Select Activity</option>
                @foreach($activities as $activity)
                    <option value="{{ $activity->id }}"
                            data-division="{{ $activity->activity_division_id }}"
                            {{ old('activity_id', $weeklyPlan->activity_id) == $activity->id ? 'selected' : '' }}>
                        {{ $activity->activity_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Planned Quantity</label>
            <input type="number"
                   step="0.01"
                   name="planned_quantity"
                   value="{{ old('planned_quantity', $weeklyPlan->planned_quantity) }}"
                   class="border p-2 rounded w-full"
                   required>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Unit</label>
            <input type="text"
                   name="unit"
                   value="{{ old('unit', $weeklyPlan->unit) }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Planned Labour</label>
            <input type="number"
                   name="planned_labour"
                   value="{{ old('planned_labour', $weeklyPlan->planned_labour) }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Status</label>
            <select name="status" class="border p-2 rounded w-full" required>
                @foreach(['Planned', 'In Progress', 'Completed', 'Delayed'] as $status)
                    <option value="{{ $status }}"
                        {{ old('status', $weeklyPlan->status) == $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

        <div>
            <label class="block text-sm font-semibold mb-1">Materials Required</label>
            <textarea name="materials_required"
                      rows="3"
                      class="border p-2 rounded w-full">{{ old('materials_required', $weeklyPlan->materials_required) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Machinery Required</label>
            <textarea name="machinery_required"
                      rows="3"
                      class="border p-2 rounded w-full">{{ old('machinery_required', $weeklyPlan->machinery_required) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Risks / Constraints</label>
            <textarea name="risks_constraints"
                      rows="4"
                      class="border p-2 rounded w-full">{{ old('risks_constraints', $weeklyPlan->risks_constraints) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Remarks</label>
            <textarea name="remarks"
                      rows="4"
                      class="border p-2 rounded w-full">{{ old('remarks', $weeklyPlan->remarks) }}</textarea>
        </div>

    </div>

    <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
        Update Weekly Plan
    </button>

</form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const divisionDropdown = document.getElementById('activity_division_id');
    const activityDropdown = document.getElementById('activity_id');
    const selectedActivityId = "{{ old('activity_id', $weeklyPlan->activity_id) }}";

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