@extends('layouts.app')

@section('content')

@php
    $inputClass = 'border border-gray-300 p-2 rounded w-full text-sm';
    $labelClass = 'block mb-1 font-semibold text-sm text-gray-800';
    $cardClass = 'bg-white rounded-lg shadow-sm p-4 mb-4';
@endphp

<h1 class="text-2xl font-bold mb-4">Create DPR</h1>

@if ($errors->any())
    <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
        <ul class="list-disc ml-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('dprs.store') }}"
      method="POST"
      enctype="multipart/form-data"
      class="pb-24">

    @csrf

    {{-- BASIC DETAILS --}}
    <div class="{{ $cardClass }}">
        <h2 class="text-xl font-bold mb-4">Basic Details</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

            <div>
                <label class="{{ $labelClass }}">DPR Date</label>
                <input type="date"
                       name="dpr_date"
                       value="{{ date('Y-m-d') }}"
                       class="{{ $inputClass }}"
                       required>
            </div>

            <div>
                <label class="{{ $labelClass }}">Project</label>
                <select name="project_id"
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
                <label class="{{ $labelClass }}">Weather</label>
                <input type="text"
                       name="weather"
                       class="{{ $inputClass }}"
                       placeholder="Sunny / Rainy / Cloudy">
            </div>

            <div>
                <label class="{{ $labelClass }}">General Remarks</label>
                <input type="text"
                       name="remarks"
                       class="{{ $inputClass }}"
                       placeholder="Optional remarks">
            </div>

        </div>
    </div>

    {{-- WORK COMPLETED --}}
    <div class="{{ $cardClass }}">

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Work Completed Today</h2>
            <span id="work-count"
                  class="text-sm bg-blue-100 text-blue-700 px-3 py-1 rounded-full">
                1 Work Item
            </span>
        </div>

        <div id="work-items">

            <div class="work-item border rounded-lg p-3 mb-3 bg-gray-50">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                    <div>
                        <label class="{{ $labelClass }}">Activity Division</label>
                        <select name="activity_division_id[]"
                                class="{{ $inputClass }} activity-division-select">
                            <option value="">Select Division</option>
                            @foreach($activityDivisions as $division)
                                <option value="{{ $division->id }}">
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Activity Mapping</label>
                        <select name="activity_mapping_id[]"
                                class="{{ $inputClass }} activity-mapping-select">
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
                        <label class="{{ $labelClass }}">Old Activity</label>
                        <select name="activity_id[]"
                                class="{{ $inputClass }}"
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
                        <label class="{{ $labelClass }}">Contractor</label>
                        <select name="contractor_id[]"
                                class="{{ $inputClass }}"
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
                        <label class="{{ $labelClass }}">Quantity Completed</label>
                        <input type="number"
                               step="0.01"
                               name="quantity_completed[]"
                               class="{{ $inputClass }}"
                               required>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Work Remarks</label>
                        <input type="text"
                               name="work_remarks[]"
                               class="{{ $inputClass }}"
                               placeholder="Optional">
                    </div>

                    <div class="md:col-span-2">
                        <details class="bg-white border rounded p-3">
                            <summary class="cursor-pointer font-semibold text-blue-700 text-sm">
                                Detailed Location Optional
                            </summary>

                            <div class="grid grid-cols-1 md:grid-cols-5 gap-3 mt-3">

                                <div>
                                    <label class="{{ $labelClass }}">Block</label>
                                    <select name="project_block_id[]"
                                            class="{{ $inputClass }}">
                                        <option value="">Block</option>
                                        @foreach($projectBlocks as $block)
                                            <option value="{{ $block->id }}">
                                                {{ $block->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">Floor</label>
                                    <select name="project_floor_id[]"
                                            class="{{ $inputClass }}">
                                        <option value="">Floor</option>
                                        @foreach($projectFloors as $floor)
                                            <option value="{{ $floor->id }}">
                                                {{ $floor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">Unit</label>
                                    <select name="project_unit_id[]"
                                            class="{{ $inputClass }}">
                                        <option value="">Unit</option>
                                        @foreach($projectUnits as $unit)
                                            <option value="{{ $unit->id }}">
                                                {{ $unit->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">Room</label>
                                    <select name="project_room_id[]"
                                            class="{{ $inputClass }}">
                                        <option value="">Room</option>
                                        @foreach($projectRooms as $room)
                                            <option value="{{ $room->id }}">
                                                {{ $room->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">Sub-space</label>
                                    <select name="project_subspace_id[]"
                                            class="{{ $inputClass }}">
                                        <option value="">Sub-space</option>
                                        @foreach($projectSubspaces as $subspace)
                                            <option value="{{ $subspace->id }}">
                                                {{ $subspace->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                        </details>
                    </div>

                </div>

                <div class="mt-3 flex justify-end">
                    <button type="button"
                            onclick="removeWorkItem(this)"
                            class="hidden remove-work-btn bg-red-100 text-red-700 px-3 py-1 rounded text-sm">
                        Remove
                    </button>
                </div>

            </div>

        </div>

        <button type="button"
                onclick="addWorkItem()"
                class="bg-green-600 text-white px-4 py-2 rounded text-sm">
            + Add More Work
        </button>

    </div>

   {{-- LABOUR --}}
<details class="{{ $cardClass }}">
    <summary class="text-xl font-bold cursor-pointer">
        Labour Details
    </summary>

    <div class="mt-4" id="labour-items">

        <div class="labour-item border rounded-lg p-3 mb-3 bg-gray-50">

            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">

                <div>
                    <label class="{{ $labelClass }}">Labour Category</label>
                    <select class="{{ $inputClass }} labour-category-select">
                        <option value="">Select Category</option>
                        @foreach($labourCategories as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Labour Type</label>
                    <select name="labour_type[]"
                            class="{{ $inputClass }} labour-type-select">
                        <option value="">Select Labour Type</option>
                        @foreach($labourTypes as $labourType)
                            <option value="{{ $labourType->id }}"
                                    data-category="{{ $labourType->labour_category_id }}">
                                {{ $labourType->labour_type_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Male</label>
                    <input type="number"
                           name="male_count[]"
                           value="0"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Female</label>
                    <input type="number"
                           name="female_count[]"
                           value="0"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Local</label>
                    <input type="number"
                           name="local_count[]"
                           value="0"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Non-local</label>
                    <input type="number"
                           name="non_local_count[]"
                           value="0"
                           class="{{ $inputClass }}">
                </div>

            </div>

        </div>

    </div>

    <button type="button"
            onclick="addLabourItem()"
            class="bg-green-600 text-white px-4 py-2 rounded text-sm">
        + Add More Labour
    </button>
</details>

    {{-- MATERIALS --}}
    <details class="{{ $cardClass }}">
        <summary class="text-xl font-bold cursor-pointer">
            Materials
        </summary>

        <div class="mt-4 space-y-4">

            {{-- MATERIAL USED --}}
            <div class="border rounded-lg p-3 bg-gray-50">
                <h3 class="font-bold mb-3">Material Used</h3>

                <div id="material-used-items">
                    <div class="material-used-item border rounded p-3 mb-3 bg-white">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

                            <div>
                                <label class="{{ $labelClass }}">Category</label>
                                <select name="used_material_category_id[]"
                                        class="{{ $inputClass }} material-category-select">
                                    <option value="">Select Category</option>
                                    @foreach($materialCategories as $category)
                                        <option value="{{ $category->id }}">
                                            {{ $category->category_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">Material</label>
                                <select name="material_id[]"
                                        class="{{ $inputClass }} material-select">
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
                                <label class="{{ $labelClass }}">Quantity Used</label>
                                <input type="number"
                                       step="0.01"
                                       name="quantity_used[]"
                                       class="{{ $inputClass }}">
                            </div>

                        </div>
                    </div>
                </div>

                <button type="button"
                        onclick="addMaterialUsedItem()"
                        class="bg-green-600 text-white px-4 py-2 rounded text-sm">
                    + Add Material Used
                </button>
            </div>

            {{-- MATERIAL RECEIVED --}}
            <div class="border rounded-lg p-3 bg-gray-50">
                <h3 class="font-bold mb-3">Material Received</h3>

                <div id="material-received-items">
                    <div class="material-received-item border rounded p-3 mb-3 bg-white">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                            <div>
                                <label class="{{ $labelClass }}">Category</label>
                                <select name="received_material_category_id[]"
                                        class="{{ $inputClass }} material-category-select">
                                    <option value="">Select Category</option>
                                    @foreach($materialCategories as $category)
                                        <option value="{{ $category->id }}">
                                            {{ $category->category_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">Material</label>
                                <select name="received_material_id[]"
                                        class="{{ $inputClass }} material-select">
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
                                <label class="{{ $labelClass }}">Vendor</label>
                                <select name="vendor_id[]"
                                        class="{{ $inputClass }}">
                                    <option value="">Select Vendor</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">
                                            {{ $vendor->vendor_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">Quantity Received</label>
                                <input type="number"
                                       step="0.01"
                                       name="quantity_received[]"
                                       class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">Challan Number</label>
                                <input type="text"
                                       name="challan_number[]"
                                       class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">Bill Number</label>
                                <input type="text"
                                       name="bill_number[]"
                                       class="{{ $inputClass }}">
                            </div>

                        </div>
                    </div>
                </div>

                <button type="button"
                        onclick="addMaterialReceivedItem()"
                        class="bg-green-600 text-white px-4 py-2 rounded text-sm">
                    + Add Material Received
                </button>
            </div>

            {{-- MATERIAL REQUIRED --}}
            <div class="border rounded-lg p-3 bg-gray-50">
                <h3 class="font-bold mb-3">Material Required</h3>

                <div id="material-required-items">
                    <div class="material-required-item border rounded p-3 mb-3 bg-white">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                            <div>
                                <label class="{{ $labelClass }}">Category</label>
                                <select name="required_material_category_id[]"
                                        class="{{ $inputClass }} material-category-select">
                                    <option value="">Select Category</option>
                                    @foreach($materialCategories as $category)
                                        <option value="{{ $category->id }}">
                                            {{ $category->category_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">Material</label>
                                <select name="required_material_id[]"
                                        class="{{ $inputClass }} material-select">
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
                                <label class="{{ $labelClass }}">Required Quantity</label>
                                <input type="number"
                                       step="0.01"
                                       name="required_quantity[]"
                                       class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">Required Date</label>
                                <input type="date"
                                       name="required_date[]"
                                       class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">Priority</label>
                                <select name="priority[]"
                                        class="{{ $inputClass }}">
                                    <option value="Normal">Normal</option>
                                    <option value="Urgent">Urgent</option>
                                    <option value="Critical">Critical</option>
                                </select>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">Reason</label>
                                <input type="text"
                                       name="reason[]"
                                       class="{{ $inputClass }}">
                            </div>

                            <div class="md:col-span-2">
                                <label class="{{ $labelClass }}">Remarks</label>
                                <input type="text"
                                       name="required_remarks[]"
                                       class="{{ $inputClass }}">
                            </div>

                        </div>
                    </div>
                </div>

                <button type="button"
                        onclick="addMaterialRequiredItem()"
                        class="bg-green-600 text-white px-4 py-2 rounded text-sm">
                    + Add Material Required
                </button>
            </div>

        </div>
    </details>

    {{-- MACHINERY --}}
    <details class="{{ $cardClass }}">
        <summary class="text-xl font-bold cursor-pointer">
            Machinery / Tools Used
        </summary>

        @php
            $machineList = $machineries ?? $machineryTools ?? collect();
        @endphp

        <div class="mt-4 border rounded-lg p-3 bg-gray-50">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                <div>
                    <label class="{{ $labelClass }}">Machinery / Tool</label>
                    <select name="machinery_tool_id[]"
                            class="{{ $inputClass }}">
                        <option value="">Select Machinery / Tool</option>
                        @foreach($machineList as $machine)
                            <option value="{{ $machine->id }}">
                                {{ $machine->machine_name ?? $machine->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Quantity</label>
                    <input type="number"
                           name="machine_quantity[]"
                           value="1"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Usage Hours</label>
                    <input type="number"
                           step="0.01"
                           name="usage_hours[]"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Working Condition</label>
                    <select name="working_condition[]"
                            class="{{ $inputClass }}">
                        <option value="Working">Working</option>
                        <option value="Breakdown">Breakdown</option>
                        <option value="Idle">Idle</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">Remarks</label>
                    <textarea name="machine_remarks[]"
                              rows="2"
                              class="{{ $inputClass }}"></textarea>
                </div>

            </div>
        </div>
    </details>

    {{-- ISSUES --}}
    <details class="{{ $cardClass }}">
        <summary class="text-xl font-bold cursor-pointer">
            Issues / Delays
        </summary>

        <div class="mt-4 border rounded-lg p-3 bg-gray-50">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                <div>
                    <label class="{{ $labelClass }}">Issue Type</label>
                    <input type="text"
                           name="issue_type[]"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Related Activity</label>
                    <input type="text"
                           name="related_activity[]"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Responsible Person</label>
                    <input type="text"
                           name="responsible_person[]"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Priority</label>
                    <select name="issue_priority[]"
                            class="{{ $inputClass }}">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Status</label>
                    <select name="issue_status[]"
                            class="{{ $inputClass }}">
                        <option value="Open">Open</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Resolved">Resolved</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">Description</label>
                    <textarea name="issue_description[]"
                              rows="2"
                              class="{{ $inputClass }}"></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">Remarks</label>
                    <textarea name="issue_remarks[]"
                              rows="2"
                              class="{{ $inputClass }}"></textarea>
                </div>

            </div>
        </div>
    </details>

    {{-- TOMORROW PLAN --}}
    <details class="{{ $cardClass }}">
        <summary class="text-xl font-bold cursor-pointer">
            Tomorrow Plan
        </summary>

        <div class="mt-4 border rounded-lg p-3 bg-gray-50">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                <div>
                    <label class="{{ $labelClass }}">Planned Activity</label>
                    <select name="plan_activity_id[]"
                            class="{{ $inputClass }}">
                        <option value="">Select Activity</option>
                        @foreach($activities as $activity)
                            <option value="{{ $activity->id }}">
                                {{ $activity->activity_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Planned Quantity</label>
                    <input type="number"
                           step="0.01"
                           name="planned_quantity[]"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Unit</label>
                    <input type="text"
                           name="planned_unit[]"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Planned Labour</label>
                    <input type="number"
                           name="planned_labour[]"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Materials Required</label>
                    <input type="text"
                           name="planned_materials[]"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Machinery Required</label>
                    <input type="text"
                           name="planned_machinery[]"
                           class="{{ $inputClass }}">
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">Risks / Constraints</label>
                    <textarea name="planned_risks[]"
                              rows="2"
                              class="{{ $inputClass }}"></textarea>
                </div>

            </div>
        </div>
    </details>

    {{-- PHOTOS --}}
    <details class="{{ $cardClass }}" open>
        <summary class="text-xl font-bold cursor-pointer">
            Photos
        </summary>

        <div class="mt-4">
            <input type="file"
                   name="photos[]"
                   multiple
                   accept="image/*"
                   class="{{ $inputClass }}">
        </div>
    </details>

    {{-- STICKY SUBMIT --}}
    <div class="sticky bottom-0 bg-white border-t p-3 mt-4 z-20">
        <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold">
            Submit DPR
        </button>
    </div>

</form>

<script>
    function resetFields(container) {
        container.querySelectorAll('input, textarea').forEach(function (field) {
            if (field.type === 'number') {
                field.value = field.defaultValue || '';
            } else {
                field.value = '';
            }
        });

        container.querySelectorAll('select').forEach(function (field) {
            field.selectedIndex = 0;
        });
    }

    function cloneItem(wrapperId, itemClass) {
        const wrapper = document.getElementById(wrapperId);
        const firstItem = wrapper.querySelector('.' + itemClass);
        const clone = firstItem.cloneNode(true);

        resetFields(clone);
        wrapper.appendChild(clone);

        bindActivityFiltering();
        bindMaterialCategoryFiltering();
        bindLabourCategoryFiltering();
        updateWorkButtons();
    }

    function addWorkItem() {
        cloneItem('work-items', 'work-item');
    }

    function removeWorkItem(button) {
        const item = button.closest('.work-item');
        item.remove();
        updateWorkButtons();
    }

    function updateWorkButtons() {
        const items = document.querySelectorAll('.work-item');
        const count = items.length;

        document.getElementById('work-count').innerText =
            count + (count === 1 ? ' Work Item' : ' Work Items');

        document.querySelectorAll('.remove-work-btn').forEach(function (button) {
            button.classList.toggle('hidden', count <= 1);
        });
    }

    function addLabourItem() {
        cloneItem('labour-items', 'labour-item');
    }

    function addMaterialUsedItem() {
        cloneItem('material-used-items', 'material-used-item');
    }

    function addMaterialReceivedItem() {
        cloneItem('material-received-items', 'material-received-item');
    }

    function addMaterialRequiredItem() {
        cloneItem('material-required-items', 'material-required-item');
    }

    function bindActivityFiltering() {
        document.querySelectorAll('.work-item').forEach(function (item) {
            const divisionSelect = item.querySelector('.activity-division-select');
            const mappingSelect = item.querySelector('.activity-mapping-select');

            if (!divisionSelect || !mappingSelect) {
                return;
            }

            divisionSelect.onchange = function () {
                const selectedDivision = this.value;

                mappingSelect.querySelectorAll('option').forEach(function (option) {
                    if (!option.value) {
                        option.hidden = false;
                        return;
                    }

                    option.hidden = selectedDivision &&
                        option.dataset.division !== selectedDivision;
                });

                mappingSelect.value = '';
            };
        });
    }

    function bindMaterialCategoryFiltering() {
        document.querySelectorAll('.material-category-select').forEach(function (categorySelect) {
            categorySelect.onchange = function () {
                const parent = categorySelect.closest('.border');
                const materialSelect = parent.querySelector('.material-select');
                const selectedCategory = categorySelect.value;

                if (!materialSelect) {
                    return;
                }

                materialSelect.querySelectorAll('option').forEach(function (option) {
                    if (!option.value) {
                        option.hidden = false;
                        return;
                    }

                    option.hidden = selectedCategory &&
                        option.dataset.category !== selectedCategory;
                });

                materialSelect.value = '';
            };
        });
    }

    bindActivityFiltering();
    bindMaterialCategoryFiltering();
    bindLabourCategoryFiltering();
    updateWorkButtons();

    const labourTypeOptions =
    Array.from(document.querySelectorAll('.labour-type-select option'))
        .map(option => option.cloneNode(true));

function bindLabourCategoryFiltering() {
    document.querySelectorAll('.labour-item').forEach(function(item) {
        const categorySelect = item.querySelector('.labour-category-select');
        const typeSelect = item.querySelector('.labour-type-select');

        if (!categorySelect || !typeSelect) {
            return;
        }

        categorySelect.onchange = function () {
            const selectedCategory = this.value;

            typeSelect.innerHTML = '';
            typeSelect.add(new Option('Select Labour Type', ''));

            labourTypeOptions.forEach(function(option) {
                if (
                    option.value !== '' &&
                    option.dataset.category === selectedCategory
                ) {
                    typeSelect.add(option.cloneNode(true));
                }
            });
        };
    });
}
</script>

@endsection