@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Add Labour Report
</h1>

@if($errors->any())
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<form method="POST"
      action="{{ route('labour-reports.store') }}"
      class="bg-white p-6 rounded shadow">

    @csrf

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div>
            <label class="block font-semibold mb-1">Project</label>
            <select name="project_id" class="border p-2 rounded w-full" required>
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

    <select id="project_block_id"
            name="project_block_id"
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

    <select id="project_floor_id"
            name="project_floor_id"
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

    <select id="project_unit_id"
            name="project_unit_id"
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

<div>
    <label class="block font-semibold mb-1">Room</label>

    <select id="project_room_id"
            name="project_room_id"
            class="border p-2 rounded w-full">

        <option value="">Select Room</option>

        @foreach($projectRooms as $room)
            <option value="{{ $room->id }}"
                    data-unit="{{ $room->project_unit_id }}">
                {{ $room->name }}
            </option>
        @endforeach

    </select>
</div>

<div>
    <label class="block font-semibold mb-1">Sub-space</label>

    <select id="project_subspace_id"
            name="project_subspace_id"
            class="border p-2 rounded w-full">

        <option value="">Select Sub-space</option>

        @foreach($projectSubspaces as $subspace)
            <option value="{{ $subspace->id }}"
                    data-room="{{ $subspace->project_room_id }}">
                {{ $subspace->name }}
            </option>
        @endforeach

    </select>
</div>
        <div>
            <label class="block font-semibold mb-1">Date</label>
            <input type="date"
                   name="entry_date"
                   value="{{ date('Y-m-d') }}"
                   class="border p-2 rounded w-full"
                   required>
        </div>

        <div>
            <label class="block font-semibold mb-1">Shift</label>
            <select name="shift" class="border p-2 rounded w-full">
                <option value="">Select Shift</option>
                <option value="Day">Day</option>
                <option value="Night">Night</option>
                <option value="General">General</option>
            </select>
        </div>

        <div>
    <label class="block font-semibold mb-1">Activity Division</label>

    <select id="activity_division_id"
            class="border p-2 rounded w-full">
        <option value="">Select Activity Division</option>

        @foreach($activityDivisions as $division)
            <option value="{{ $division->id }}">
                {{ $division->code }} - {{ $division->name }}
            </option>
        @endforeach
    </select>
</div>

 

<div>
    <label class="block font-semibold mb-1">Activity</label>

    <select id="activity_id"
            name="activity_id"
            class="border p-2 rounded w-full">
        <option value="">Select Activity</option>

        @foreach($activities as $activity)
            <option value="{{ $activity->id }}"
                    data-division="{{ $activity->activity_division_id }}">
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
                    <option value="{{ $contractor->id }}">
                        {{ $contractor->contractor_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Work Output</label>
            <input type="number"
                   step="0.01"
                   name="work_output"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">Output Unit</label>
            <input type="text"
                   name="work_output_unit"
                   class="border p-2 rounded w-full">
        </div>

    </div>

   <h2 class="text-xl font-bold mt-6 mb-4">
    Labour Counts
</h2>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4">

    {{-- Actual Labour Categories --}}
    @foreach([
        'skilled_count' => 'Skilled',
        'semi_skilled_count' => 'Semi-Skilled',
        'helper_count' => 'Helper',
        'semi_helper_count' => 'Semi-Helper',
        'supervisor_count' => 'Supervisor',
        'technician_count' => 'Technician',
        'machine_operator_count' => 'Machine Operator',
    ] as $field => $label)

        <div>
            <label class="block font-semibold mb-1">
                {{ $label }}
            </label>

            <input type="number"
                   name="{{ $field }}"
                   value="0"
                   min="0"
                   class="border p-2 rounded w-full labour-count">
        </div>

    @endforeach

    {{-- Classification Fields --}}
    @foreach([
        'male_count' => 'Male',
        'female_count' => 'Female',
        'local_count' => 'Local',
        'non_local_count' => 'Non-Local',
    ] as $field => $label)

        <div>
            <label class="block font-semibold mb-1">
                {{ $label }}
            </label>

            <input type="number"
                   name="{{ $field }}"
                   value="0"
                   min="0"
                   class="border p-2 rounded w-full">
        </div>

    @endforeach

    <div>
        <label class="block font-semibold mb-1">
            Total Labour
        </label>

        <input type="number"
               id="total_labour_display"
               value="0"
               class="border p-2 rounded w-full bg-gray-100"
               readonly>
    </div>

</div>
    <div class="mt-6">
        <label class="block font-semibold mb-1">Remarks</label>
        <textarea name="remarks"
                  class="border p-2 rounded w-full"></textarea>
    </div>

    <div class="mt-6 flex gap-3">
        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded">
            Save Labour Report
        </button>

        <a href="{{ route('labour-reports.index') }}"
           class="bg-gray-500 text-white px-4 py-2 rounded">
            Back
        </a>
    </div>

</form>

<script>
    function calculateTotalLabour() {
        let total = 0;

        document.querySelectorAll('.labour-count').forEach(function(input) {
            total += parseInt(input.value || 0);
        });

        document.getElementById('total_labour_display').value = total;
    }

    document.querySelectorAll('.labour-count').forEach(function(input) {
        input.addEventListener('input', calculateTotalLabour);
    });

    function filterSelect(select, dataKey, parentValue, placeholder) {
    const allOptions = Array.from(select.querySelectorAll('option'));

    select.innerHTML = '';
    select.add(new Option(placeholder, ''));

    allOptions.forEach(function(option) {
        if (
            option.value !== '' &&
            option.dataset[dataKey] === parentValue
        ) {
            select.add(option.cloneNode(true));
        }
    });
}

const projectSelect = document.querySelector('select[name="project_id"]');
const blockSelect = document.getElementById('project_block_id');
const floorSelect = document.getElementById('project_floor_id');
const unitSelect = document.getElementById('project_unit_id');
const roomSelect = document.getElementById('project_room_id');
const subspaceSelect = document.getElementById('project_subspace_id');

const originalBlocks = Array.from(blockSelect.querySelectorAll('option'));
const originalFloors = Array.from(floorSelect.querySelectorAll('option'));
const originalUnits = Array.from(unitSelect.querySelectorAll('option'));
const originalRooms = Array.from(roomSelect.querySelectorAll('option'));
const originalSubspaces = Array.from(subspaceSelect.querySelectorAll('option'));

function resetDropdown(select, placeholder) {
    select.innerHTML = '';
    select.add(new Option(placeholder, ''));
}

projectSelect.addEventListener('change', function () {
    const projectId = this.value;

    resetDropdown(blockSelect, 'Select Block');
    resetDropdown(floorSelect, 'Select Floor');
    resetDropdown(unitSelect, 'Select Unit');
    resetDropdown(roomSelect, 'Select Room');
    resetDropdown(subspaceSelect, 'Select Sub-space');

    originalBlocks.forEach(function(option) {
        if (
            option.value !== '' &&
            option.dataset.project === projectId
        ) {
            blockSelect.add(option.cloneNode(true));
        }
    });
});

blockSelect.addEventListener('change', function () {
    const blockId = this.value;

    resetDropdown(floorSelect, 'Select Floor');
    resetDropdown(unitSelect, 'Select Unit');
    resetDropdown(roomSelect, 'Select Room');
    resetDropdown(subspaceSelect, 'Select Sub-space');

    originalFloors.forEach(function(option) {
        if (
            option.value !== '' &&
            option.dataset.block === blockId
        ) {
            floorSelect.add(option.cloneNode(true));
        }
    });
});

floorSelect.addEventListener('change', function () {
    const floorId = this.value;

    resetDropdown(unitSelect, 'Select Unit');
    resetDropdown(roomSelect, 'Select Room');
    resetDropdown(subspaceSelect, 'Select Sub-space');

    originalUnits.forEach(function(option) {
        if (
            option.value !== '' &&
            option.dataset.floor === floorId
        ) {
            unitSelect.add(option.cloneNode(true));
        }
    });
});

unitSelect.addEventListener('change', function () {
    const unitId = this.value;

    resetDropdown(roomSelect, 'Select Room');
    resetDropdown(subspaceSelect, 'Select Sub-space');

    originalRooms.forEach(function(option) {
        if (
            option.value !== '' &&
            option.dataset.unit === unitId
        ) {
            roomSelect.add(option.cloneNode(true));
        }
    });
});

roomSelect.addEventListener('change', function () {
    const roomId = this.value;

    resetDropdown(subspaceSelect, 'Select Sub-space');

    originalSubspaces.forEach(function(option) {
        if (
            option.value !== '' &&
            option.dataset.room === roomId
        ) {
            subspaceSelect.add(option.cloneNode(true));
        }
    });
});

const activityDivisionSelect = document.getElementById('activity_division_id');
const activitySelect = document.getElementById('activity_id');
const originalActivities = Array.from(activitySelect.querySelectorAll('option'));

activityDivisionSelect.addEventListener('change', function () {
    const divisionId = this.value;

    activitySelect.innerHTML = '';
    activitySelect.add(new Option('Select Activity', ''));

    originalActivities.forEach(function(option) {
        if (
            option.value !== '' &&
            option.dataset.division === divisionId
        ) {
            activitySelect.add(option.cloneNode(true));
        }
    });
});
</script>

@endsection