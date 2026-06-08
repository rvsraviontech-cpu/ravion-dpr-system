@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Create DPR
</h1>

@if ($errors->any())
    <div class="bg-red-100 text-red-700 p-4 rounded mb-6">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('dprs.store') }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf

    <div class="bg-white rounded shadow p-6 mb-6">

        <h2 class="text-2xl font-bold mb-4">
            Basic Details
        </h2>

        <div class="mb-4">
            <label class="block mb-2 font-bold">DPR Date</label>
            <input type="date"
                   name="dpr_date"
                   class="border p-2 rounded w-full"
                   required>
        </div>

        <div class="mb-4">
            <label class="block mb-2 font-bold">Project</label>
            <select name="project_id"
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

        <div class="mb-4">
            <label class="block mb-2 font-bold">Weather</label>
            <input type="text"
                   name="weather"
                   class="border p-2 rounded w-full">
        </div>

        <div class="mb-4">
            <label class="block mb-2 font-bold">General Remarks</label>
            <textarea name="remarks"
                      class="border p-2 rounded w-full"></textarea>
        </div>

    </div>

    <div class="bg-white rounded shadow p-6 mb-6">

        <h2 class="text-2xl font-bold mb-4">
            Work Completed Today
        </h2>

        <div id="work-items">

            <div class="work-item border rounded p-4 mb-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    
                        <div>
    <label class="block mb-2 font-bold">Activity Division</label>
    <select name="activity_division_id[]"
            class="activity-division-select border p-2 rounded w-full">
        <option value="">Select Activity Division</option>
        @foreach($activityDivisions as $division)
            <option value="{{ $division->id }}">
                {{ $division->name }}
            </option>
        @endforeach
    </select>
</div>
                        <div>
    <label class="block mb-2 font-bold">Activity</label>
    <select name="activity_mapping_id[]"
            class="activity-mapping-select border p-2 rounded w-full">
        <option value="">Select Activity</option>

        @foreach($activityMappings as $mapping)
            <option value="{{ $mapping->id }}"
                    data-division="{{ $mapping->activity_division_id }}">
                {{ $mapping->activity_name }} ({{ $mapping->unit }})
            </option>
        @endforeach
    </select>
</div>

                    <div>
                        <label class="block mb-2 font-bold">Old Activity</label>
                        <select name="activity_id[]"
                                class="border p-2 rounded w-full"
                                required>
                            <option value="">Select Activity</option>
                            @foreach($activities as $activity)
                                <option value="{{ $activity->id }}">
                                    {{ $activity->activity_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Block / Building</label>
                        <select name="project_block_id[]"
                                class="border p-2 rounded w-full">
                            <option value="">Select Block / Building</option>
                            @foreach($projectBlocks as $block)
                                <option value="{{ $block->id }}">
                                    {{ $block->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Floor</label>
                        <select name="project_floor_id[]"
                                class="border p-2 rounded w-full">
                            <option value="">Select Floor</option>
                            @foreach($projectFloors as $floor)
                                <option value="{{ $floor->id }}">
                                    {{ $floor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Unit / Flat / Villa</label>
                        <select name="project_unit_id[]"
                                class="border p-2 rounded w-full">
                            <option value="">Select Unit</option>
                            @foreach($projectUnits as $unit)
                                <option value="{{ $unit->id }}">
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Room / Space</label>
                        <select name="project_room_id[]"
                                class="border p-2 rounded w-full">
                            <option value="">Select Room</option>
                            @foreach($projectRooms as $room)
                                <option value="{{ $room->id }}">
                                    {{ $room->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Sub-space / Element</label>
                        <select name="project_subspace_id[]"
                                class="border p-2 rounded w-full">
                            <option value="">Select Sub-space</option>
                            @foreach($projectSubspaces as $subspace)
                                <option value="{{ $subspace->id }}">
                                    {{ $subspace->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Contractor</label>
                        <select name="contractor_id[]"
                                class="border p-2 rounded w-full"
                                required>
                            <option value="">Select Contractor</option>
                            @foreach($contractors as $contractor)
                                <option value="{{ $contractor->id }}">
                                    {{ $contractor->contractor_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Quantity Completed</label>
                        <input type="number"
                               step="0.01"
                               name="quantity_completed[]"
                               class="border p-2 rounded w-full"
                               required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block mb-2 font-bold">Work Remarks</label>
                        <textarea name="work_remarks[]"
                                  class="border p-2 rounded w-full"></textarea>
                    </div>

                </div>

            </div>

        </div>

        <button type="button"
                onclick="addWorkItem()"
                class="bg-green-600 text-white px-4 py-2 rounded">
            Add More Work
        </button>

    </div>

    <div class="bg-white rounded shadow p-6 mb-6">

        <h2 class="text-2xl font-bold mb-4">
            Labour Details
        </h2>

        <div id="labour-items">

            <div class="labour-item border rounded p-4 mb-4">

                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

                    <div>
                        <label class="block mb-2 font-bold">Labour Type</label>
                        <select name="labour_type[]"
                                class="border p-2 rounded w-full">
                            <option value="">Select Labour Type</option>
                            @foreach($labourTypes as $labourType)
                                <option value="{{ $labourType->id }}">
                                    {{ $labourType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Male</label>
                        <input type="number"
                               name="male_count[]"
                               class="border p-2 rounded w-full"
                               value="0">
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Female</label>
                        <input type="number"
                               name="female_count[]"
                               class="border p-2 rounded w-full"
                               value="0">
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Local</label>
                        <input type="number"
                               name="local_count[]"
                               class="border p-2 rounded w-full"
                               value="0">
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Non-local</label>
                        <input type="number"
                               name="non_local_count[]"
                               class="border p-2 rounded w-full"
                               value="0">
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="bg-white rounded shadow p-6 mb-6">

        <h2 class="text-2xl font-bold mb-4">
            Material Used
        </h2>

        <div id="material-used-items">

            <div class="material-used-item border rounded p-4 mb-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block mb-2 font-bold">Material</label>
                        <select name="material_id[]"
                                class="border p-2 rounded w-full">
                            <option value="">Select Material</option>
                            @foreach($materials as $material)
                                <option value="{{ $material->id }}">
                                    {{ $material->material_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Quantity Used</label>
                        <input type="number"
                               step="0.01"
                               name="quantity_used[]"
                               class="border p-2 rounded w-full">
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="bg-white rounded shadow p-6 mb-6">

        <h2 class="text-2xl font-bold mb-4">
            Material Received
        </h2>

        <div id="material-received-items">

            <div class="material-received-item border rounded p-4 mb-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block mb-2 font-bold">Material</label>
                        <select name="received_material_id[]"
                                class="border p-2 rounded w-full">
                            <option value="">Select Material</option>
                            @foreach($materials as $material)
                                <option value="{{ $material->id }}">
                                    {{ $material->material_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Vendor</label>
                        <select name="vendor_id[]"
                                class="border p-2 rounded w-full">
                            <option value="">Select Vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">
                                    {{ $vendor->vendor_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Quantity Received</label>
                        <input type="number"
                               step="0.01"
                               name="quantity_received[]"
                               class="border p-2 rounded w-full">
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Challan Number</label>
                        <input type="text"
                               name="challan_number[]"
                               class="border p-2 rounded w-full">
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Bill Number</label>
                        <input type="text"
                               name="bill_number[]"
                               class="border p-2 rounded w-full">
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="bg-white rounded shadow p-6 mb-6">

        <h2 class="text-2xl font-bold mb-4">
            Material Required
        </h2>

        <div id="material-required-items">

            <div class="material-required-item border rounded p-4 mb-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block mb-2 font-bold">Material</label>
                        <select name="required_material_id[]"
                                class="border p-2 rounded w-full">
                            <option value="">Select Material</option>
                            @foreach($materials as $material)
                                <option value="{{ $material->id }}">
                                    {{ $material->material_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Required Quantity</label>
                        <input type="number"
                               step="0.01"
                               name="required_quantity[]"
                               class="border p-2 rounded w-full">
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Required Date</label>
                        <input type="date"
                               name="required_date[]"
                               class="border p-2 rounded w-full">
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Priority</label>
                        <select name="priority[]"
                                class="border p-2 rounded w-full">
                            <option value="Normal">Normal</option>
                            <option value="Urgent">Urgent</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Reason</label>
                        <input type="text"
                               name="reason[]"
                               class="border p-2 rounded w-full">
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Remarks</label>
                        <input type="text"
                               name="required_remarks[]"
                               class="border p-2 rounded w-full">
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="bg-white rounded shadow p-6 mb-6">

        <h2 class="text-2xl font-bold mb-4">
            Machinery / Tools Used
        </h2>

        @php
            $machineList = $machineryTools ?? $machineries ?? collect();
        @endphp

        <div id="machinery-items">

            <div class="machinery-item border rounded p-4 mb-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block mb-2 font-bold">Machinery / Tool</label>
                        <select name="machinery_tool_id[]"
                                class="border p-2 rounded w-full">
                            <option value="">Select Machinery / Tool</option>
                            @foreach($machineList as $machine)
                                <option value="{{ $machine->id }}">
                                    {{ $machine->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Quantity</label>
                        <input type="number"
                               name="machine_quantity[]"
                               class="border p-2 rounded w-full"
                               value="1">
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Usage Hours</label>
                        <input type="number"
                               step="0.01"
                               name="usage_hours[]"
                               class="border p-2 rounded w-full">
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Working Condition</label>
                        <select name="working_condition[]"
                                class="border p-2 rounded w-full">
                            <option value="Working">Working</option>
                            <option value="Breakdown">Breakdown</option>
                            <option value="Idle">Idle</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block mb-2 font-bold">Remarks</label>
                        <textarea name="machine_remarks[]"
                                  class="border p-2 rounded w-full"></textarea>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="bg-white rounded shadow p-6 mb-6">

        <h2 class="text-2xl font-bold mb-4">
            Issues / Delays
        </h2>

        <div id="issue-items">

            <div class="issue-item border rounded p-4 mb-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block mb-2 font-bold">Issue Type</label>
                        <input type="text"
                               name="issue_type[]"
                               class="border p-2 rounded w-full">
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Related Activity</label>
                        <input type="text"
                               name="related_activity[]"
                               class="border p-2 rounded w-full">
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Responsible Person</label>
                        <input type="text"
                               name="responsible_person[]"
                               class="border p-2 rounded w-full">
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Priority</label>
                        <select name="issue_priority[]"
                                class="border p-2 rounded w-full">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Status</label>
                        <select name="issue_status[]"
                                class="border p-2 rounded w-full">
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block mb-2 font-bold">Description</label>
                        <textarea name="issue_description[]"
                                  class="border p-2 rounded w-full"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block mb-2 font-bold">Remarks</label>
                        <textarea name="issue_remarks[]"
                                  class="border p-2 rounded w-full"></textarea>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="bg-white rounded shadow p-6 mb-6">

        <h2 class="text-2xl font-bold mb-4">
            Tomorrow Plan
        </h2>

        <div id="tomorrow-plan-items">

            <div class="tomorrow-plan-item border rounded p-4 mb-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block mb-2 font-bold">Planned Activity</label>
                        <select name="plan_activity_id[]"
                                class="border p-2 rounded w-full">
                            <option value="">Select Activity</option>
                            @foreach($activities as $activity)
                                <option value="{{ $activity->id }}">
                                    {{ $activity->activity_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Planned Quantity</label>
                        <input type="number"
                               step="0.01"
                               name="planned_quantity[]"
                               class="border p-2 rounded w-full">
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Unit</label>
                        <input type="text"
                               name="planned_unit[]"
                               class="border p-2 rounded w-full">
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Planned Labour</label>
                        <input type="number"
                               name="planned_labour[]"
                               class="border p-2 rounded w-full">
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Materials Required</label>
                        <input type="text"
                               name="planned_materials[]"
                               class="border p-2 rounded w-full">
                    </div>

                    <div>
                        <label class="block mb-2 font-bold">Machinery Required</label>
                        <input type="text"
                               name="planned_machinery[]"
                               class="border p-2 rounded w-full">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block mb-2 font-bold">Risks / Constraints</label>
                        <textarea name="planned_risks[]"
                                  class="border p-2 rounded w-full"></textarea>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="bg-white rounded shadow p-6 mb-6">

        <h2 class="text-2xl font-bold mb-4">
            Photos
        </h2>

        <input type="file"
               name="photos[]"
               multiple
               accept="image/*"
               class="border p-2 rounded w-full">

    </div>

    <button type="submit"
            class="bg-blue-600 text-white px-6 py-3 rounded">
        Submit DPR
    </button>

</form>

<script>
function addWorkItem() {
    const container = document.getElementById('work-items');
    const item = container.querySelector('.work-item');
    const clone = item.cloneNode(true);

    clone.querySelectorAll('input, textarea, select').forEach(function(field) {
        if (field.tagName === 'SELECT') {
            field.selectedIndex = 0;
        } else {
            field.value = '';
        }
    });

    container.appendChild(clone);

    bindActivityDivisionFilter(clone);
    bindLocationDropdowns(clone);
}
</script>
<script>
    function bindActivityDivisionFilter(scope) {
    const divisionSelect = scope.querySelector('.activity-division-select');
    const activitySelect = scope.querySelector('.activity-mapping-select');

    if (!divisionSelect || !activitySelect) {
        return;
    }

    const allOptions = Array.from(activitySelect.options);

    divisionSelect.addEventListener('change', function () {
        const selectedDivision = this.value;

        activitySelect.innerHTML = '';

        allOptions.forEach(function (option) {
            if (
                option.value === '' ||
                option.dataset.division === selectedDivision
            ) {
                activitySelect.appendChild(option.cloneNode(true));
            }
        });

        activitySelect.value = '';
    });
}

document.querySelectorAll('.work-item').forEach(function (item) {
    bindActivityDivisionFilter(item);
});

function bindLocationDropdowns(scope) {
    const blockSelect = scope.querySelector('select[name="project_block_id[]"]');
    const floorSelect = scope.querySelector('select[name="project_floor_id[]"]');
    const unitSelect = scope.querySelector('select[name="project_unit_id[]"]');
    const roomSelect = scope.querySelector('select[name="project_room_id[]"]');
    const subspaceSelect = scope.querySelector('select[name="project_subspace_id[]"]');

    if (!blockSelect || !floorSelect || !unitSelect || !roomSelect || !subspaceSelect) {
        return;
    }

    blockSelect.addEventListener('change', function () {
        resetSelect(floorSelect, 'Select Floor');
        resetSelect(unitSelect, 'Select Unit');
        resetSelect(roomSelect, 'Select Room');
        resetSelect(subspaceSelect, 'Select Sub-space');

        if (!this.value) return;

        fetch(`/location/floors/${this.value}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    floorSelect.add(new Option(item.name, item.id));
                });
            });
    });

    floorSelect.addEventListener('change', function () {
        resetSelect(unitSelect, 'Select Unit');
        resetSelect(roomSelect, 'Select Room');
        resetSelect(subspaceSelect, 'Select Sub-space');

        if (!this.value) return;

        fetch(`/location/units/${this.value}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    unitSelect.add(new Option(item.name, item.id));
                });
            });
    });

    unitSelect.addEventListener('change', function () {
        resetSelect(roomSelect, 'Select Room');
        resetSelect(subspaceSelect, 'Select Sub-space');

        if (!this.value) return;

        fetch(`/location/rooms/${this.value}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    roomSelect.add(new Option(item.name, item.id));
                });
            });
    });

    roomSelect.addEventListener('change', function () {
        resetSelect(subspaceSelect, 'Select Sub-space');

        if (!this.value) return;

        fetch(`/location/subspaces/${this.value}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    subspaceSelect.add(new Option(item.name, item.id));
                });
            });
    });
}

function resetSelect(select, placeholder) {
    select.innerHTML = '';
    select.add(new Option(placeholder, ''));
}

document.querySelectorAll('.work-item').forEach(function (item) {
    bindLocationDropdowns(item);
});
</script>

@endsection