@extends('layouts.app')

@section('content')

@php
    $inputClass = 'border border-gray-300 p-2 rounded w-full text-sm';
    $labelClass = 'block mb-1 font-semibold text-sm text-gray-800';
    $cardClass = 'bg-white rounded-lg shadow-sm p-4 mb-4';
@endphp

<h1 class="text-2xl font-bold mb-4">Edit DPR</h1>

@if ($errors->any())
    <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
        <ul class="list-disc ml-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('dprs.update', $dpr->id) }}"
      method="POST"
      enctype="multipart/form-data"
      class="pb-24">

    @csrf
    @method('PUT')

    {{-- BASIC DETAILS --}}
    <div class="{{ $cardClass }}">
        <h2 class="text-xl font-bold mb-4">Basic Details</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

            <div>
                <label class="{{ $labelClass }}">DPR Date</label>
                <input type="date"
                       name="dpr_date"
                       value="{{ old('dpr_date', $dpr->dpr_date) }}"
                       class="{{ $inputClass }}"
                       required>
            </div>

            <div>
                <label class="{{ $labelClass }}">Project</label>
                <select name="project_id" class="{{ $inputClass }}" required>
                    <option value="">Select Project</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}"
                            {{ old('project_id', $dpr->project_id) == $project->id ? 'selected' : '' }}>
                            {{ $project->project_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="{{ $labelClass }}">Weather</label>
                <input type="text"
                       name="weather"
                       value="{{ old('weather', $dpr->weather) }}"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="{{ $labelClass }}">General Remarks</label>
                <input type="text"
                       name="remarks"
                       value="{{ old('remarks', $dpr->remarks) }}"
                       class="{{ $inputClass }}">
            </div>

        </div>

        @if($dpr->pmo_remarks)
            <div class="mt-4 bg-yellow-100 text-yellow-800 p-3 rounded">
                <strong>PMO Remarks:</strong> {{ $dpr->pmo_remarks }}
            </div>
        @endif
    </div>

    {{-- WORK COMPLETED --}}
    <div class="{{ $cardClass }}">

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Work Completed Today</h2>
            <span id="work-count" class="text-sm bg-blue-100 text-blue-700 px-3 py-1 rounded-full">
                {{ max($dpr->workItems->count(), 1) }} Work Item
            </span>
        </div>

        <div id="work-items">

            @forelse($dpr->workItems as $item)
                <div class="work-item border rounded-lg p-3 mb-3 bg-gray-50">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                        <div>
                            <label class="{{ $labelClass }}">Activity Division</label>
                            <select name="activity_division_id[]" class="{{ $inputClass }} activity-division-select">
                                <option value="">Select Division</option>
                                @foreach($activityDivisions as $division)
                                    <option value="{{ $division->id }}"
                                        {{ optional($item->activityMapping)->activity_division_id == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Activity Mapping</label>
                            <select name="activity_mapping_id[]" class="{{ $inputClass }} activity-mapping-select">
                                <option value="">Select Activity</option>
                                @foreach($activityMappings as $mapping)
                                    <option value="{{ $mapping->id }}"
                                            data-division="{{ $mapping->activity_division_id }}"
                                            {{ $item->activity_mapping_id == $mapping->id ? 'selected' : '' }}>
                                        {{ $mapping->activity_name }} ({{ $mapping->unit }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Old Activity</label>
                            <select name="activity_id[]" class="{{ $inputClass }}" required>
                                <option value="">Select Activity</option>
                                @foreach($activities as $activity)
                                    <option value="{{ $activity->id }}"
                                        {{ $item->activity_id == $activity->id ? 'selected' : '' }}>
                                        {{ $activity->activity_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Contractor</label>
                            <select name="contractor_id[]" class="{{ $inputClass }}">
                                <option value="">Company Workers / No Contractor</option>
                                @foreach($contractors as $contractor)
                                    <option value="{{ $contractor->id }}"
                                        {{ $item->contractor_id == $contractor->id ? 'selected' : '' }}>
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
                                   value="{{ $item->quantity_completed }}"
                                   class="{{ $inputClass }}"
                                   required>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Work Remarks</label>
                            <input type="text"
                                   name="work_remarks[]"
                                   value="{{ $item->remarks }}"
                                   class="{{ $inputClass }}">
                        </div>

                        <div class="md:col-span-2">
                            <details class="bg-white border rounded p-3" open>
                                <summary class="cursor-pointer font-semibold text-blue-700 text-sm">
                                    Detailed Location Optional
                                </summary>

                                <div class="grid grid-cols-1 md:grid-cols-5 gap-3 mt-3">

                                    <div>
                                        <label class="{{ $labelClass }}">Block</label>
                                        <select name="project_block_id[]" class="{{ $inputClass }}">
                                            <option value="">Block</option>
                                            @foreach($projectBlocks as $block)
                                                <option value="{{ $block->id }}"
                                                    {{ $item->project_block_id == $block->id ? 'selected' : '' }}>
                                                    {{ $block->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="{{ $labelClass }}">Floor</label>
                                        <select name="project_floor_id[]" class="{{ $inputClass }}">
                                            <option value="">Floor</option>
                                            @foreach($projectFloors as $floor)
                                                <option value="{{ $floor->id }}"
                                                    {{ $item->project_floor_id == $floor->id ? 'selected' : '' }}>
                                                    {{ $floor->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="{{ $labelClass }}">Unit</label>
                                        <select name="project_unit_id[]" class="{{ $inputClass }}">
                                            <option value="">Unit</option>
                                            @foreach($projectUnits as $unit)
                                                <option value="{{ $unit->id }}"
                                                    {{ $item->project_unit_id == $unit->id ? 'selected' : '' }}>
                                                    {{ $unit->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="{{ $labelClass }}">Room</label>
                                        <select name="project_room_id[]" class="{{ $inputClass }}">
                                            <option value="">Room</option>
                                            @foreach($projectRooms as $room)
                                                <option value="{{ $room->id }}"
                                                    {{ $item->project_room_id == $room->id ? 'selected' : '' }}>
                                                    {{ $room->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="{{ $labelClass }}">Sub-space</label>
                                        <select name="project_subspace_id[]" class="{{ $inputClass }}">
                                            <option value="">Sub-space</option>
                                            @foreach($projectSubspaces as $subspace)
                                                <option value="{{ $subspace->id }}"
                                                    {{ $item->project_subspace_id == $subspace->id ? 'selected' : '' }}>
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
                                class="remove-work-btn bg-red-100 text-red-700 px-3 py-1 rounded text-sm">
                            Remove
                        </button>
                    </div>

                </div>
            @empty
                <div class="work-item border rounded-lg p-3 mb-3 bg-gray-50">
                    <p class="text-sm text-red-600">No work item found. Please add work item from Create DPR if needed.</p>
                </div>
            @endforelse

        </div>

        <button type="button"
                onclick="addWorkItem()"
                class="bg-green-600 text-white px-4 py-2 rounded text-sm">
            + Add More Work
        </button>

    </div>

    {{-- LABOUR --}}
    <details class="{{ $cardClass }}" open>
        <summary class="text-xl font-bold cursor-pointer">Labour Details</summary>

        <div class="mt-4" id="labour-items">
            @forelse($dpr->labours as $labour)
                <div class="labour-item border rounded-lg p-3 mb-3 bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-3">

                        <div>
                            <label class="{{ $labelClass }}">Labour Category</label>
                            <select class="{{ $inputClass }} labour-category-select">
                                <option value="">Select Category</option>
                                @foreach($labourCategories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ optional($labour->labourType)->labour_category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Labour Type</label>
                            <select name="labour_type[]" class="{{ $inputClass }} labour-type-select">
                                <option value="">Select Labour Type</option>
                                @foreach($labourTypes as $labourType)
                                    <option value="{{ $labourType->id }}"
                                            data-category="{{ $labourType->labour_category_id }}"
                                            {{ $labour->labour_type_id == $labourType->id ? 'selected' : '' }}>
                                        {{ $labourType->labour_type_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Male</label>
                            <input type="number" name="male_count[]" value="{{ $labour->male_count }}" class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Female</label>
                            <input type="number" name="female_count[]" value="{{ $labour->female_count }}" class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Local</label>
                            <input type="number" name="local_count[]" value="{{ $labour->local_count }}" class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Non-local</label>
                            <input type="number" name="non_local_count[]" value="{{ $labour->non_local_count }}" class="{{ $inputClass }}">
                        </div>

                    </div>
                </div>
            @empty
                <div class="labour-item border rounded-lg p-3 mb-3 bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                        <div>
                            <label class="{{ $labelClass }}">Labour Category</label>
                            <select class="{{ $inputClass }} labour-category-select">
                                <option value="">Select Category</option>
                                @foreach($labourCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Labour Type</label>
                            <select name="labour_type[]" class="{{ $inputClass }} labour-type-select">
                                <option value="">Select Labour Type</option>
                                @foreach($labourTypes as $labourType)
                                    <option value="{{ $labourType->id }}" data-category="{{ $labourType->labour_category_id }}">
                                        {{ $labourType->labour_type_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div><label class="{{ $labelClass }}">Male</label><input type="number" name="male_count[]" value="0" class="{{ $inputClass }}"></div>
                        <div><label class="{{ $labelClass }}">Female</label><input type="number" name="female_count[]" value="0" class="{{ $inputClass }}"></div>
                        <div><label class="{{ $labelClass }}">Local</label><input type="number" name="local_count[]" value="0" class="{{ $inputClass }}"></div>
                        <div><label class="{{ $labelClass }}">Non-local</label><input type="number" name="non_local_count[]" value="0" class="{{ $inputClass }}"></div>
                    </div>
                </div>
            @endforelse
        </div>

        <button type="button" onclick="addLabourItem()" class="bg-green-600 text-white px-4 py-2 rounded text-sm">
            + Add More Labour
        </button>
    </details>

    {{-- MATERIALS --}}
    <details class="{{ $cardClass }}" open>
        <summary class="text-xl font-bold cursor-pointer">Materials</summary>

        <div class="mt-4 space-y-4">

            {{-- MATERIAL USED --}}
            <div class="border rounded-lg p-3 bg-gray-50">
                <h3 class="font-bold mb-3">Material Used</h3>

                <div id="material-used-items">
                    @forelse($dpr->materials as $used)
                        <div class="material-used-item border rounded p-3 mb-3 bg-white">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

                                <div>
                                    <label class="{{ $labelClass }}">Category</label>
                                    <select name="used_material_category_id[]" class="{{ $inputClass }} material-category-select">
                                        <option value="">Select Category</option>
                                        @foreach($materialCategories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ optional($used->material)->material_category_id == $category->id ? 'selected' : '' }}>
                                                {{ $category->category_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">Material</label>
                                    <select name="material_id[]" class="{{ $inputClass }} material-select">
                                        <option value="">Select Material</option>
                                        @foreach($materials as $material)
                                            <option value="{{ $material->id }}"
                                                    data-category="{{ $material->material_category_id }}"
                                                    {{ $used->material_id == $material->id ? 'selected' : '' }}>
                                                {{ $material->material_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">Quantity Used</label>
                                    <input type="number" step="0.01" name="quantity_used[]" value="{{ $used->quantity_used }}" class="{{ $inputClass }}">
                                </div>

                            </div>
                        </div>
                    @empty
                        <div class="material-used-item border rounded p-3 mb-3 bg-white">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="{{ $labelClass }}">Category</label>
                                    <select name="used_material_category_id[]" class="{{ $inputClass }} material-category-select">
                                        <option value="">Select Category</option>
                                        @foreach($materialCategories as $category)
                                            <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">Material</label>
                                    <select name="material_id[]" class="{{ $inputClass }} material-select">
                                        <option value="">Select Material</option>
                                        @foreach($materials as $material)
                                            <option value="{{ $material->id }}" data-category="{{ $material->material_category_id }}">
                                                {{ $material->material_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">Quantity Used</label>
                                    <input type="number" step="0.01" name="quantity_used[]" class="{{ $inputClass }}">
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>

                <button type="button" onclick="addMaterialUsedItem()" class="bg-green-600 text-white px-4 py-2 rounded text-sm">
                    + Add Material Used
                </button>
            </div>

            {{-- MATERIAL RECEIVED --}}
            <div class="border rounded-lg p-3 bg-gray-50">
                <h3 class="font-bold mb-3">Material Received</h3>

                <div id="material-received-items">
                    @forelse($dpr->materialReceived as $received)
                        <div class="material-received-item border rounded p-3 mb-3 bg-white">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                                <div>
                                    <label class="{{ $labelClass }}">Category</label>
                                    <select name="received_material_category_id[]" class="{{ $inputClass }} material-category-select">
                                        <option value="">Select Category</option>
                                        @foreach($materialCategories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ optional($received->material)->material_category_id == $category->id ? 'selected' : '' }}>
                                                {{ $category->category_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">Material</label>
                                    <select name="received_material_id[]" class="{{ $inputClass }} material-select">
                                        <option value="">Select Material</option>
                                        @foreach($materials as $material)
                                            <option value="{{ $material->id }}"
                                                    data-category="{{ $material->material_category_id }}"
                                                    {{ $received->material_id == $material->id ? 'selected' : '' }}>
                                                {{ $material->material_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">Vendor</label>
                                    <select name="vendor_id[]" class="{{ $inputClass }}">
                                        <option value="">Select Vendor</option>
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}"
                                                {{ $received->vendor_id == $vendor->id ? 'selected' : '' }}>
                                                {{ $vendor->vendor_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">Quantity Received</label>
                                    <input type="number" step="0.01" name="quantity_received[]" value="{{ $received->quantity_received }}" class="{{ $inputClass }}">
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">Challan Number</label>
                                    <input type="text" name="challan_number[]" value="{{ $received->challan_number }}" class="{{ $inputClass }}">
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">Bill Number</label>
                                    <input type="text" name="bill_number[]" value="{{ $received->bill_number }}" class="{{ $inputClass }}">
                                </div>

                            </div>
                        </div>
                    @empty
                        <div class="material-received-item border rounded p-3 mb-3 bg-white">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="{{ $labelClass }}">Category</label>
                                    <select name="received_material_category_id[]" class="{{ $inputClass }} material-category-select">
                                        <option value="">Select Category</option>
                                        @foreach($materialCategories as $category)
                                            <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">Material</label>
                                    <select name="received_material_id[]" class="{{ $inputClass }} material-select">
                                        <option value="">Select Material</option>
                                        @foreach($materials as $material)
                                            <option value="{{ $material->id }}" data-category="{{ $material->material_category_id }}">{{ $material->material_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">Vendor</label>
                                    <select name="vendor_id[]" class="{{ $inputClass }}">
                                        <option value="">Select Vendor</option>
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}">{{ $vendor->vendor_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div><label class="{{ $labelClass }}">Quantity Received</label><input type="number" step="0.01" name="quantity_received[]" class="{{ $inputClass }}"></div>
                                <div><label class="{{ $labelClass }}">Challan Number</label><input type="text" name="challan_number[]" class="{{ $inputClass }}"></div>
                                <div><label class="{{ $labelClass }}">Bill Number</label><input type="text" name="bill_number[]" class="{{ $inputClass }}"></div>
                            </div>
                        </div>
                    @endforelse
                </div>

                <button type="button" onclick="addMaterialReceivedItem()" class="bg-green-600 text-white px-4 py-2 rounded text-sm">
                    + Add Material Received
                </button>
            </div>

        </div>
    </details>

    {{-- PHOTOS --}}
    <details class="{{ $cardClass }}" open>
        <summary class="text-xl font-bold cursor-pointer">Photos</summary>

        @if($dpr->photos->count())
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4 mb-4">
                @foreach($dpr->photos as $photo)
                    <img src="{{ asset('storage/' . $photo->photo_path) }}"
                         class="h-32 w-full object-cover rounded border">
                @endforeach
            </div>
        @endif

        <div class="mt-4">
            <label class="{{ $labelClass }}">Upload More Photos</label>
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
            Update DPR
        </button>
    </div>

</form>

<script>
    function resetFields(container) {
        container.querySelectorAll('input, textarea').forEach(function (field) {
            if (field.type === 'number') {
                field.value = field.defaultValue || '';
            } else if (field.type !== 'file') {
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

        if (!firstItem) {
            return;
        }

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

        const workCount = document.getElementById('work-count');
        if (workCount) {
            workCount.innerText = count + (count === 1 ? ' Work Item' : ' Work Items');
        }

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

    function bindActivityFiltering() {
        document.querySelectorAll('.work-item').forEach(function (item) {
            const divisionSelect = item.querySelector('.activity-division-select');
            const mappingSelect = item.querySelector('.activity-mapping-select');

            if (!divisionSelect || !mappingSelect) return;

            divisionSelect.onchange = function () {
                const selectedDivision = this.value;

                mappingSelect.querySelectorAll('option').forEach(function (option) {
                    if (!option.value) {
                        option.hidden = false;
                        return;
                    }

                    option.hidden = selectedDivision && option.dataset.division !== selectedDivision;
                });
            };
        });
    }

    function bindMaterialCategoryFiltering() {
        document.querySelectorAll('.material-category-select').forEach(function (categorySelect) {
            categorySelect.onchange = function () {
                const parent = categorySelect.closest('.border');
                const materialSelect = parent.querySelector('.material-select');
                const selectedCategory = categorySelect.value;

                if (!materialSelect) return;

                materialSelect.querySelectorAll('option').forEach(function (option) {
                    if (!option.value) {
                        option.hidden = false;
                        return;
                    }

                    option.hidden = selectedCategory && option.dataset.category !== selectedCategory;
                });
            };
        });
    }

    const labourTypeOptions =
        Array.from(document.querySelectorAll('.labour-type-select option'))
            .map(option => option.cloneNode(true));

    function bindLabourCategoryFiltering() {
        document.querySelectorAll('.labour-item').forEach(function(item) {
            const categorySelect = item.querySelector('.labour-category-select');
            const typeSelect = item.querySelector('.labour-type-select');

            if (!categorySelect || !typeSelect) return;

            categorySelect.onchange = function () {
                const selectedCategory = this.value;

                typeSelect.innerHTML = '';
                typeSelect.add(new Option('Select Labour Type', ''));

                labourTypeOptions.forEach(function(option) {
                    if (option.value !== '' && option.dataset.category === selectedCategory) {
                        typeSelect.add(option.cloneNode(true));
                    }
                });
            };
        });
    }

    bindActivityFiltering();
    bindMaterialCategoryFiltering();
    bindLabourCategoryFiltering();
    updateWorkButtons();
</script>

@endsection