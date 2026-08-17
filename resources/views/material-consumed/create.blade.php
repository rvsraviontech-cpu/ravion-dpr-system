@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-base text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:py-2.5 sm:text-sm';
    $labelClass = 'mb-1.5 block text-sm font-semibold text-gray-700';

    $oldItems = old('items', [
        [
            'activity_division_id' => '',
            'activity_id' => '',
            'material_type_id' => '',
            'brand_master_id' => '',
            'material_specification_id' => '',
            'material_grade_id' => '',
            'quantity_consumed' => '',
            'wastage_quantity' => 0,
            'unit_master_id' => '',
            'wastage_reason' => '',
            'remarks' => '',
        ],
    ]);

    $activityOptionsForJs = $activities
        ->map(function ($activity) {
            return [
                'id' => $activity->id,
                'name' => $activity->activity_name,
                'division_id' => $activity->activity_division_id,
            ];
        })
        ->values();

    $materialTypeOptionsForJs = $materialTypes
        ->map(function ($type) {
            return [
                'id' => $type->id,
                'name' => $type->material_type_name,
                'group' => $type->material_group,
                'unit_id' => $type->unit_master_id,
                'unit_name' => optional($type->unit)->unit_name,
            ];
        })
        ->values();

    $brandOptionsForJs = $brands
        ->map(function ($brand) {
            return [
                'id' => $brand->id,
                'name' => $brand->brand_name,
                'material_type_id' => $brand->material_type_id,
            ];
        })
        ->values();

    $specificationOptionsForJs = $specifications
        ->map(function ($specification) {
            return [
                'id' => $specification->id,
                'name' => $specification->specification_name,
                'material_type_id' => $specification->material_type_id,
            ];
        })
        ->values();

    $gradeOptionsForJs = $grades
        ->map(function ($grade) {
            return [
                'id' => $grade->id,
                'name' => $grade->grade_name,
                'material_type_id' => $grade->material_type_id,
            ];
        })
        ->values();

    $materialGroupsForJs = $materialGroups->values();
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">
                Add Material Consumption
            </h1>

            <p class="mt-1 text-gray-500">
                Record one site consumption entry containing one or more material items.
            </p>
        </div>

        <a href="{{ route('material-consumed.index') }}"
           class="inline-flex w-full items-center justify-center rounded-lg bg-gray-600 px-5 py-3 font-semibold text-white hover:bg-gray-700 sm:w-auto sm:py-2.5">
            Back
        </a>
    </div>

    @if(session('error'))
        <div class="mb-5 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-lg border border-red-300 bg-red-50 p-4 text-red-700">
            <p class="mb-2 font-semibold">
                Please correct the following:
            </p>

            <ul class="ml-5 list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('material-consumed.store') }}"
          id="material-consumption-form">

        @csrf

        <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

            <div class="mb-5 rounded-xl bg-[#0F2A52] px-4 py-3 text-white">
                <h2 class="text-lg font-bold sm:text-xl">Consumption Information</h2>
                <p class="mt-1 text-xs text-blue-100">Select the site location, contractor and consumption date.</p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

                <div>
                    <label class="{{ $labelClass }}">
                        Project <span class="text-red-500">*</span>
                    </label>

                    <select name="project_id"
                            id="project_id"
                            class="{{ $inputClass }}"
                            required>

                        <option value="">Select Project</option>

                        @foreach($projects as $project)
                            <option value="{{ $project->id }}"
                                {{ (string) old('project_id') === (string) $project->id ? 'selected' : '' }}>
                                {{ $project->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Block</label>

                    <select name="project_block_id"
                            id="project_block_id"
                            class="{{ $inputClass }}">

                        <option value="">Select Block</option>

                        @foreach($projectBlocks as $block)
                            <option value="{{ $block->id }}"
                                    data-project="{{ $block->project_id }}"
                                {{ (string) old('project_block_id') === (string) $block->id ? 'selected' : '' }}>
                                {{ $block->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Floor</label>

                    <select name="project_floor_id"
                            id="project_floor_id"
                            class="{{ $inputClass }}">

                        <option value="">Select Floor</option>

                        @foreach($projectFloors as $floor)
                            <option value="{{ $floor->id }}"
                                    data-project="{{ $floor->project_id }}"
                                    data-block="{{ $floor->project_block_id }}"
                                {{ (string) old('project_floor_id') === (string) $floor->id ? 'selected' : '' }}>
                                {{ $floor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Unit</label>

                    <select name="project_unit_id"
                            id="project_unit_id"
                            class="{{ $inputClass }}">

                        <option value="">Select Unit</option>

                        @foreach($projectUnits as $projectUnit)
                            <option value="{{ $projectUnit->id }}"
                                    data-project="{{ $projectUnit->project_id }}"
                                    data-block="{{ $projectUnit->project_block_id }}"
                                    data-floor="{{ $projectUnit->project_floor_id }}"
                                {{ (string) old('project_unit_id') === (string) $projectUnit->id ? 'selected' : '' }}>
                                {{ $projectUnit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Room</label>

                    <select name="project_room_id"
                            id="project_room_id"
                            class="{{ $inputClass }}">

                        <option value="">Select Room</option>

                        @foreach($projectRooms as $room)
                            <option value="{{ $room->id }}"
                                    data-project="{{ $room->project_id }}"
                                    data-block="{{ $room->project_block_id }}"
                                    data-floor="{{ $room->project_floor_id }}"
                                    data-unit="{{ $room->project_unit_id }}"
                                {{ (string) old('project_room_id') === (string) $room->id ? 'selected' : '' }}>
                                {{ $room->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Sub-space</label>

                    <select name="project_subspace_id"
                            id="project_subspace_id"
                            class="{{ $inputClass }}">

                        <option value="">Select Sub-space</option>

                        @foreach($projectSubspaces as $subspace)
                            <option value="{{ $subspace->id }}"
                                    data-project="{{ $subspace->project_id }}"
                                    data-block="{{ $subspace->project_block_id }}"
                                    data-floor="{{ $subspace->project_floor_id }}"
                                    data-unit="{{ $subspace->project_unit_id }}"
                                    data-room="{{ $subspace->project_room_id }}"
                                {{ (string) old('project_subspace_id') === (string) $subspace->id ? 'selected' : '' }}>
                                {{ $subspace->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Contractor</label>

                    <select name="contractor_id"
                            class="{{ $inputClass }}">

                        <option value="">Select Contractor</option>

                        @foreach($contractors as $contractor)
                            <option value="{{ $contractor->id }}"
                                {{ (string) old('contractor_id') === (string) $contractor->id ? 'selected' : '' }}>
                                {{ $contractor->contractor_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Consumed Date <span class="text-red-500">*</span>
                    </label>

                    <input type="date"
                           name="consumed_date"
                           value="{{ old('consumed_date', now()->format('Y-m-d')) }}"
                           class="{{ $inputClass }}"
                           required>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Related Work Output Quantity
                    </label>

                    <input type="number"
                           step="0.001"
                           min="0"
                           name="related_work_output_quantity"
                           value="{{ old('related_work_output_quantity', 0) }}"
                           class="{{ $inputClass }}">
                </div>

                <div class="md:col-span-2 xl:col-span-3">
                    <label class="{{ $labelClass }}">General Remarks</label>

                    <textarea name="remarks"
                              rows="3"
                              class="{{ $inputClass }}"
                              placeholder="General notes for this material consumption">{{ old('remarks') }}</textarea>
                </div>

            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="flex flex-col gap-3 border-b border-gray-200 bg-[#0F2A52] p-4 text-white md:flex-row md:items-center md:justify-between md:p-5">
                <div>
                    <h2 class="text-lg font-bold text-white sm:text-xl">
                        Material Items
                    </h2>

                    <p class="mt-1 text-xs text-blue-100 sm:text-sm">
                        Add every material consumed under this entry.
                    </p>
                </div>

                <button type="button"
                        id="add-item-row"
                        class="w-full rounded-lg bg-green-600 px-4 py-3 font-semibold text-white hover:bg-green-700 md:w-auto md:py-2.5">
                    + Add Material Row
                </button>
            </div>

            <div class="overflow-visible lg:overflow-x-auto">

                <table class="block w-full text-sm lg:table lg:min-w-[2100px]">

                    <thead class="hidden bg-gray-100 text-xs uppercase tracking-wide text-gray-600 lg:table-header-group">
                        <tr>
                            <th class="w-14 px-3 py-3 text-center">#</th>
                            <th class="min-w-44 px-3 py-3 text-left">Activity Division</th>
                            <th class="min-w-52 px-3 py-3 text-left">Activity</th>
                            <th class="min-w-48 px-3 py-3 text-left">Material Group</th>
                            <th class="min-w-52 px-3 py-3 text-left">Material Type</th>
                            <th class="min-w-44 px-3 py-3 text-left">Brand</th>
                            <th class="min-w-44 px-3 py-3 text-left">Specification</th>
                            <th class="min-w-44 px-3 py-3 text-left">Grade / Rating</th>
                            <th class="min-w-36 px-3 py-3 text-left">Consumed Qty</th>
                            <th class="min-w-36 px-3 py-3 text-left">Wastage Qty</th>
                            <th class="min-w-36 px-3 py-3 text-left">Unit</th>
                            <th class="min-w-56 px-3 py-3 text-left">Wastage Reason</th>
                            <th class="min-w-52 px-3 py-3 text-left">Remarks</th>
                            <th class="w-24 px-3 py-3 text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody id="material-items-body"
                           class="block space-y-4 p-3 lg:table-row-group lg:space-y-0 lg:p-0 lg:divide-y lg:divide-gray-200">

                        @foreach($oldItems as $rowIndex => $oldItem)
                            <tr class="material-item-row block overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm lg:table-row lg:rounded-none lg:border-0 lg:shadow-none"
                                data-row-index="{{ $rowIndex }}">

                                <td class="block bg-slate-50 px-3 py-3 lg:table-cell lg:bg-transparent lg:text-center">
                                    <div class="flex items-center justify-between lg:block">
                                        <span class="text-xs font-bold uppercase tracking-wide text-gray-500 lg:hidden">Material Item</span>
                                        <span class="row-number inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-blue-100 px-2 text-xs font-bold text-blue-800 lg:bg-transparent lg:text-sm lg:text-inherit">
                                            {{ $loop->iteration }}
                                        </span>
                                    </div>
                                </td>

                                <td data-mobile-label="Activity Division" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                                    <select name="items[{{ $rowIndex }}][activity_division_id]"
                                            class="{{ $inputClass }} activity-division-select">

                                        <option value="">Select Division</option>

                                        @foreach($activityDivisions as $division)
                                            <option value="{{ $division->id }}"
                                                {{ (string) ($oldItem['activity_division_id'] ?? '') === (string) $division->id ? 'selected' : '' }}>
                                                {{ $division->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td data-mobile-label="Activity" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                                    <select name="items[{{ $rowIndex }}][activity_id]"
                                            class="{{ $inputClass }} activity-select">

                                        <option value="">Select Activity</option>

                                        @foreach($activities as $activity)
                                            <option value="{{ $activity->id }}"
                                                    data-division="{{ $activity->activity_division_id }}"
                                                {{ (string) ($oldItem['activity_id'] ?? '') === (string) $activity->id ? 'selected' : '' }}>
                                                {{ $activity->activity_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td data-mobile-label="Material Group" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                                    <select class="{{ $inputClass }} material-group-select">
                                        <option value="">Select Group</option>

                                        @foreach($materialGroups as $group)
                                            <option value="{{ $group }}">
                                                {{ $group }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td data-mobile-label="Material Type" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                                    <select name="items[{{ $rowIndex }}][material_type_id]"
                                            class="{{ $inputClass }} material-type-select"
                                            required>

                                        <option value="">Select Material Type</option>

                                        @foreach($materialTypes as $materialType)
                                            <option value="{{ $materialType->id }}"
                                                    data-group="{{ $materialType->material_group }}"
                                                    data-unit-id="{{ $materialType->unit_master_id }}"
                                                    data-unit-name="{{ optional($materialType->unit)->unit_name }}"
                                                {{ (string) ($oldItem['material_type_id'] ?? '') === (string) $materialType->id ? 'selected' : '' }}>
                                                {{ $materialType->material_type_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td data-mobile-label="Brand" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                                    <select name="items[{{ $rowIndex }}][brand_master_id]"
                                            class="{{ $inputClass }} brand-select">

                                        <option value="">Select Brand</option>

                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}"
                                                    data-material-type="{{ $brand->material_type_id }}"
                                                {{ (string) ($oldItem['brand_master_id'] ?? '') === (string) $brand->id ? 'selected' : '' }}>
                                                {{ $brand->brand_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td data-mobile-label="Specification" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                                    <select name="items[{{ $rowIndex }}][material_specification_id]"
                                            class="{{ $inputClass }} specification-select">

                                        <option value="">Select Specification</option>

                                        @foreach($specifications as $specification)
                                            <option value="{{ $specification->id }}"
                                                    data-material-type="{{ $specification->material_type_id }}"
                                                {{ (string) ($oldItem['material_specification_id'] ?? '') === (string) $specification->id ? 'selected' : '' }}>
                                                {{ $specification->specification_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td data-mobile-label="Grade / Rating" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                                    <select name="items[{{ $rowIndex }}][material_grade_id]"
                                            class="{{ $inputClass }} grade-select">

                                        <option value="">Select Grade / Rating</option>

                                        @foreach($grades as $grade)
                                            <option value="{{ $grade->id }}"
                                                    data-material-type="{{ $grade->material_type_id }}"
                                                {{ (string) ($oldItem['material_grade_id'] ?? '') === (string) $grade->id ? 'selected' : '' }}>
                                                {{ $grade->grade_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td data-mobile-label="Consumed Qty" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                                    <input type="number"
                                           step="0.001"
                                           min="0.001"
                                           name="items[{{ $rowIndex }}][quantity_consumed]"
                                           value="{{ $oldItem['quantity_consumed'] ?? '' }}"
                                           class="{{ $inputClass }}"
                                           required>
                                </td>

                                <td data-mobile-label="Wastage Qty" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                                    <input type="number"
                                           step="0.001"
                                           min="0"
                                           name="items[{{ $rowIndex }}][wastage_quantity]"
                                           value="{{ $oldItem['wastage_quantity'] ?? 0 }}"
                                           class="{{ $inputClass }} wastage-quantity-input">
                                </td>

                                <td data-mobile-label="Unit" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                                    <input type="hidden"
                                           name="items[{{ $rowIndex }}][unit_master_id]"
                                           value="{{ $oldItem['unit_master_id'] ?? '' }}"
                                           class="unit-id-input">

                                    <input type="text"
                                           class="{{ $inputClass }} unit-name-input bg-gray-100"
                                           readonly
                                           placeholder="Auto">
                                </td>

                                <td data-mobile-label="Wastage Reason" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                                    <input type="text"
                                           name="items[{{ $rowIndex }}][wastage_reason]"
                                           value="{{ $oldItem['wastage_reason'] ?? '' }}"
                                           class="{{ $inputClass }} wastage-reason-input"
                                           placeholder="Required when wastage &gt; 0">
                                </td>

                                <td data-mobile-label="Remarks" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                                    <input type="text"
                                           name="items[{{ $rowIndex }}][remarks]"
                                           value="{{ $oldItem['remarks'] ?? '' }}"
                                           class="{{ $inputClass }}"
                                           placeholder="Optional">
                                </td>

                                <td data-mobile-label="Action" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:text-center lg:before:hidden">
                                    <button type="button"
                                            class="remove-item-row w-full rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-xs font-semibold text-red-700 hover:bg-red-100 lg:w-auto lg:border-0 lg:bg-red-600 lg:text-white lg:hover:bg-red-700">
                                        Remove
                                    </button>
                                </td>

                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 bg-gray-50 p-4 text-xs leading-5 text-gray-600 sm:p-5 sm:text-sm">
                Brand, Specification, Grade/Rating and Unit are filtered automatically from the selected Material Type. Wastage reason becomes mandatory when wastage quantity is greater than zero.
            </div>
        </div>

        <div class="sticky bottom-[68px] z-30 mt-6 grid grid-cols-1 gap-3 border-t border-gray-200 bg-white/95 py-3 backdrop-blur sm:flex sm:flex-wrap lg:static lg:border-0 lg:bg-transparent lg:py-0">
            <button type="submit"
                    class="w-full rounded-xl bg-blue-600 px-7 py-3.5 text-center font-semibold text-white hover:bg-blue-700 sm:w-auto sm:py-3">
                Save Material Consumption
            </button>

            <a href="{{ route('material-consumed.index') }}"
               class="w-full rounded-xl bg-gray-500 px-7 py-3.5 text-center font-semibold text-white hover:bg-gray-600 sm:w-auto sm:py-3">
                Cancel
            </a>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.getElementById('material-items-body');
    const addRowButton = document.getElementById('add-item-row');

    const projectSelect = document.getElementById('project_id');
    const blockSelect = document.getElementById('project_block_id');
    const floorSelect = document.getElementById('project_floor_id');
    const unitSelect = document.getElementById('project_unit_id');
    const roomSelect = document.getElementById('project_room_id');
    const subspaceSelect = document.getElementById('project_subspace_id');

    let rowIndex = body.querySelectorAll('.material-item-row').length;

    const activityOptions = @json($activityOptionsForJs);
    const materialTypeOptions = @json($materialTypeOptionsForJs);
    const brandOptions = @json($brandOptionsForJs);
    const specificationOptions = @json($specificationOptionsForJs);
    const gradeOptions = @json($gradeOptionsForJs);
    const materialGroups = @json($materialGroupsForJs);

    function makeOption(value, label, selected = false) {
        return new Option(label, value, selected, selected);
    }

    function rebuildSelect(select, placeholder, values, selectedValue = '') {
        select.innerHTML = '';
        select.add(makeOption('', placeholder));

        values.forEach(function (item) {
            select.add(
                makeOption(
                    String(item.id),
                    item.name,
                    String(item.id) === String(selectedValue)
                )
            );
        });
    }

    function initializeRow(row) {
        const divisionSelect = row.querySelector('.activity-division-select');
        const activitySelect = row.querySelector('.activity-select');
        const groupSelect = row.querySelector('.material-group-select');
        const typeSelect = row.querySelector('.material-type-select');
        const brandSelect = row.querySelector('.brand-select');
        const specificationSelect = row.querySelector('.specification-select');
        const gradeSelect = row.querySelector('.grade-select');
        const unitIdInput = row.querySelector('.unit-id-input');
        const unitNameInput = row.querySelector('.unit-name-input');
        const wastageQuantityInput = row.querySelector('.wastage-quantity-input');
        const wastageReasonInput = row.querySelector('.wastage-reason-input');

        const preservedActivityId = activitySelect.value;
        const preservedTypeId = typeSelect.value;
        const preservedBrandId = brandSelect.value;
        const preservedSpecificationId = specificationSelect.value;
        const preservedGradeId = gradeSelect.value;

        const selectedType = materialTypeOptions.find(function (type) {
            return String(type.id) === String(preservedTypeId);
        });

        if (selectedType) {
            groupSelect.value = selectedType.group || '';
        }

        function filterActivities(selectedValue = '') {
            const divisionId = divisionSelect.value;

            const filtered = activityOptions.filter(function (activity) {
                return divisionId === ''
                    || String(activity.division_id) === String(divisionId);
            });

            rebuildSelect(
                activitySelect,
                'Select Activity',
                filtered,
                selectedValue
            );
        }

        function filterMaterialTypes(selectedValue = '') {
            const group = groupSelect.value;

            const filtered = materialTypeOptions.filter(function (type) {
                return group === '' || type.group === group;
            });

            rebuildSelect(
                typeSelect,
                'Select Material Type',
                filtered,
                selectedValue
            );
        }

        function updateMaterialDependencies(options = {}) {
            const materialTypeId = typeSelect.value;

            const selectedMaterialType = materialTypeOptions.find(function (type) {
                return String(type.id) === String(materialTypeId);
            });

            unitIdInput.value = selectedMaterialType?.unit_id || '';
            unitNameInput.value = selectedMaterialType?.unit_name || '';

            const filteredBrands = brandOptions.filter(function (brand) {
                return String(brand.material_type_id) === String(materialTypeId);
            });

            const filteredSpecifications = specificationOptions.filter(function (specification) {
                return String(specification.material_type_id) === String(materialTypeId);
            });

            const filteredGrades = gradeOptions.filter(function (grade) {
                return String(grade.material_type_id) === String(materialTypeId);
            });

            rebuildSelect(
                brandSelect,
                'Select Brand',
                filteredBrands,
                options.brandId || ''
            );

            rebuildSelect(
                specificationSelect,
                'Select Specification',
                filteredSpecifications,
                options.specificationId || ''
            );

            rebuildSelect(
                gradeSelect,
                'Select Grade / Rating',
                filteredGrades,
                options.gradeId || ''
            );
        }

        function updateWastageRequirement() {
            const wastage = Number(wastageQuantityInput.value || 0);
            wastageReasonInput.required = wastage > 0;
        }

        divisionSelect.addEventListener('change', function () {
            filterActivities('');
        });

        groupSelect.addEventListener('change', function () {
            filterMaterialTypes('');
            updateMaterialDependencies();
        });

        typeSelect.addEventListener('change', function () {
            updateMaterialDependencies();
        });

        wastageQuantityInput.addEventListener(
            'input',
            updateWastageRequirement
        );

        row.querySelector('.remove-item-row').addEventListener('click', function () {
            const rows = body.querySelectorAll('.material-item-row');

            if (rows.length <= 1) {
                alert('At least one material row is required.');
                return;
            }

            row.remove();
            refreshRowNumbers();
        });

        filterActivities(preservedActivityId);
        filterMaterialTypes(preservedTypeId);

        updateMaterialDependencies({
            brandId: preservedBrandId,
            specificationId: preservedSpecificationId,
            gradeId: preservedGradeId,
        });

        updateWastageRequirement();
    }

    function buildNewRow(index) {
        const row = document.createElement('tr');

        row.className = 'material-item-row block overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm lg:table-row lg:rounded-none lg:border-0 lg:shadow-none';
        row.dataset.rowIndex = index;

        row.innerHTML = `
            <td class="block bg-slate-50 px-3 py-3 lg:table-cell lg:bg-transparent lg:text-center">
                <div class="flex items-center justify-between lg:block">
                    <span class="text-xs font-bold uppercase tracking-wide text-gray-500 lg:hidden">Material Item</span>
                    <span class="row-number inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-blue-100 px-2 text-xs font-bold text-blue-800 lg:bg-transparent lg:text-sm lg:text-inherit"></span>
                </div>
            </td>

            <td data-mobile-label="Activity Division" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <select name="items[${index}][activity_division_id]"
                        class="{{ $inputClass }} activity-division-select">
                    <option value="">Select Division</option>
                    @foreach($activityDivisions as $division)
                        <option value="{{ $division->id }}">
                            {{ $division->name }}
                        </option>
                    @endforeach
                </select>
            </td>

            <td data-mobile-label="Activity" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <select name="items[${index}][activity_id]"
                        class="{{ $inputClass }} activity-select">
                    <option value="">Select Activity</option>
                </select>
            </td>

            <td data-mobile-label="Material Group" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <select class="{{ $inputClass }} material-group-select">
                    <option value="">Select Group</option>
                    ${materialGroups.map(function (group) {
                        return `<option value="${escapeHtml(group)}">${escapeHtml(group)}</option>`;
                    }).join('')}
                </select>
            </td>

            <td data-mobile-label="Material Type" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <select name="items[${index}][material_type_id]"
                        class="{{ $inputClass }} material-type-select"
                        required>
                    <option value="">Select Material Type</option>
                </select>
            </td>

            <td data-mobile-label="Brand" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <select name="items[${index}][brand_master_id]"
                        class="{{ $inputClass }} brand-select">
                    <option value="">Select Brand</option>
                </select>
            </td>

            <td data-mobile-label="Specification" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <select name="items[${index}][material_specification_id]"
                        class="{{ $inputClass }} specification-select">
                    <option value="">Select Specification</option>
                </select>
            </td>

            <td data-mobile-label="Grade / Rating" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <select name="items[${index}][material_grade_id]"
                        class="{{ $inputClass }} grade-select">
                    <option value="">Select Grade / Rating</option>
                </select>
            </td>

            <td data-mobile-label="Consumed Qty" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <input type="number"
                       step="0.001"
                       min="0.001"
                       name="items[${index}][quantity_consumed]"
                       class="{{ $inputClass }}"
                       required>
            </td>

            <td data-mobile-label="Wastage Qty" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <input type="number"
                       step="0.001"
                       min="0"
                       value="0"
                       name="items[${index}][wastage_quantity]"
                       class="{{ $inputClass }} wastage-quantity-input">
            </td>

            <td data-mobile-label="Unit" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <input type="hidden"
                       name="items[${index}][unit_master_id]"
                       class="unit-id-input">

                <input type="text"
                       class="{{ $inputClass }} unit-name-input bg-gray-100"
                       readonly
                       placeholder="Auto">
            </td>

            <td data-mobile-label="Wastage Reason" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <input type="text"
                       name="items[${index}][wastage_reason]"
                       class="{{ $inputClass }} wastage-reason-input"
                       placeholder="Required when wastage > 0">
            </td>

            <td data-mobile-label="Remarks" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <input type="text"
                       name="items[${index}][remarks]"
                       class="{{ $inputClass }}"
                       placeholder="Optional">
            </td>

            <td data-mobile-label="Action" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:text-center lg:before:hidden">
                <button type="button"
                        class="remove-item-row w-full rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-xs font-semibold text-red-700 hover:bg-red-100 lg:w-auto lg:border-0 lg:bg-red-600 lg:text-white lg:hover:bg-red-700">
                    Remove
                </button>
            </td>
        `;

        return row;
    }

    function refreshRowNumbers() {
        body.querySelectorAll('.material-item-row').forEach(function (row, index) {
            row.querySelector('.row-number').textContent = index + 1;
        });
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function cloneOptions(select) {
        return Array.from(select.options).map(function (option) {
            return option.cloneNode(true);
        });
    }

    const originalBlockOptions = cloneOptions(blockSelect);
    const originalFloorOptions = cloneOptions(floorSelect);
    const originalUnitOptions = cloneOptions(unitSelect);
    const originalRoomOptions = cloneOptions(roomSelect);
    const originalSubspaceOptions = cloneOptions(subspaceSelect);

    function filterLocationSelect(select, source, predicate, placeholder) {
        const currentValue = select.value;

        select.innerHTML = '';
        select.add(new Option(placeholder, ''));

        source.forEach(function (option) {
            if (option.value !== '' && predicate(option)) {
                const clonedOption = option.cloneNode(true);

                if (String(clonedOption.value) === String(currentValue)) {
                    clonedOption.selected = true;
                }

                select.add(clonedOption);
            }
        });
    }

    function filterProjectLocations() {
        const projectId = projectSelect.value;

        filterLocationSelect(
            blockSelect,
            originalBlockOptions,
            function (option) {
                return projectId === ''
                    || String(option.dataset.project) === String(projectId);
            },
            'Select Block'
        );

        filterFloors();
    }

    function filterFloors() {
        const projectId = projectSelect.value;
        const blockId = blockSelect.value;

        filterLocationSelect(
            floorSelect,
            originalFloorOptions,
            function (option) {
                const projectMatch = projectId === ''
                    || String(option.dataset.project) === String(projectId);

                const blockMatch = blockId === ''
                    || String(option.dataset.block) === String(blockId);

                return projectMatch && blockMatch;
            },
            'Select Floor'
        );

        filterUnits();
    }

    function filterUnits() {
        const projectId = projectSelect.value;
        const blockId = blockSelect.value;
        const floorId = floorSelect.value;

        filterLocationSelect(
            unitSelect,
            originalUnitOptions,
            function (option) {
                const projectMatch = projectId === ''
                    || String(option.dataset.project) === String(projectId);

                const blockMatch = blockId === ''
                    || String(option.dataset.block) === String(blockId);

                const floorMatch = floorId === ''
                    || String(option.dataset.floor) === String(floorId);

                return projectMatch && blockMatch && floorMatch;
            },
            'Select Unit'
        );

        filterRooms();
    }

    function filterRooms() {
        const projectId = projectSelect.value;
        const blockId = blockSelect.value;
        const floorId = floorSelect.value;
        const unitId = unitSelect.value;

        filterLocationSelect(
            roomSelect,
            originalRoomOptions,
            function (option) {
                const projectMatch = projectId === ''
                    || String(option.dataset.project) === String(projectId);

                const blockMatch = blockId === ''
                    || String(option.dataset.block) === String(blockId);

                const floorMatch = floorId === ''
                    || String(option.dataset.floor) === String(floorId);

                const unitMatch = unitId === ''
                    || String(option.dataset.unit) === String(unitId);

                return projectMatch && blockMatch && floorMatch && unitMatch;
            },
            'Select Room'
        );

        filterSubspaces();
    }

    function filterSubspaces() {
        const projectId = projectSelect.value;
        const blockId = blockSelect.value;
        const floorId = floorSelect.value;
        const unitId = unitSelect.value;
        const roomId = roomSelect.value;

        filterLocationSelect(
            subspaceSelect,
            originalSubspaceOptions,
            function (option) {
                const projectMatch = projectId === ''
                    || String(option.dataset.project) === String(projectId);

                const blockMatch = blockId === ''
                    || String(option.dataset.block) === String(blockId);

                const floorMatch = floorId === ''
                    || String(option.dataset.floor) === String(floorId);

                const unitMatch = unitId === ''
                    || String(option.dataset.unit) === String(unitId);

                const roomMatch = roomId === ''
                    || String(option.dataset.room) === String(roomId);

                return projectMatch
                    && blockMatch
                    && floorMatch
                    && unitMatch
                    && roomMatch;
            },
            'Select Sub-space'
        );
    }

    addRowButton.addEventListener('click', function () {
        const newRow = buildNewRow(rowIndex++);
        body.appendChild(newRow);
        initializeRow(newRow);
        refreshRowNumbers();
    });

    projectSelect.addEventListener('change', function () {
        blockSelect.value = '';
        floorSelect.value = '';
        unitSelect.value = '';
        roomSelect.value = '';
        subspaceSelect.value = '';
        filterProjectLocations();
    });

    blockSelect.addEventListener('change', function () {
        floorSelect.value = '';
        unitSelect.value = '';
        roomSelect.value = '';
        subspaceSelect.value = '';
        filterFloors();
    });

    floorSelect.addEventListener('change', function () {
        unitSelect.value = '';
        roomSelect.value = '';
        subspaceSelect.value = '';
        filterUnits();
    });

    unitSelect.addEventListener('change', function () {
        roomSelect.value = '';
        subspaceSelect.value = '';
        filterRooms();
    });

    roomSelect.addEventListener('change', function () {
        subspaceSelect.value = '';
        filterSubspaces();
    });

    body.querySelectorAll('.material-item-row').forEach(initializeRow);

    refreshRowNumbers();
    filterProjectLocations();
});
</script>

@endsection
