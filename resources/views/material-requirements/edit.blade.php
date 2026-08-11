@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
    $labelClass = 'mb-1 block text-sm font-semibold text-gray-700';

    $existingItems = $materialRequirement->items->map(function ($item) {
        return [
            'activity_division_id' => $item->activity_division_id,
            'activity_id' => $item->activity_id,
            'material_type_id' => $item->material_type_id,
            'brand_master_id' => $item->brand_master_id,
            'material_specification_id' => $item->material_specification_id,
            'material_grade_id' => $item->material_grade_id,
            'required_quantity' => $item->required_quantity,
            'fulfilled_quantity' => $item->fulfilled_quantity,
            'unit_master_id' => $item->unit_master_id,
            'remarks' => $item->remarks,
        ];
    })->values()->all();

    if (empty($existingItems)) {
        $existingItems = [[
            'activity_division_id' => '',
            'activity_id' => '',
            'material_type_id' => '',
            'brand_master_id' => '',
            'material_specification_id' => '',
            'material_grade_id' => '',
            'required_quantity' => '',
            'fulfilled_quantity' => 0,
            'unit_master_id' => '',
            'remarks' => '',
        ]];
    }

    $formItems = old('items', $existingItems);

    $activityOptionsForJs = $activities
        ->map(fn ($activity) => [
            'id' => $activity->id,
            'name' => $activity->activity_name,
            'division_id' => $activity->activity_division_id,
        ])
        ->values();

    $materialTypeOptionsForJs = $materialTypes
        ->map(fn ($type) => [
            'id' => $type->id,
            'name' => $type->material_type_name,
            'group' => $type->material_group,
            'unit_id' => $type->unit_master_id,
            'unit_name' => optional($type->unit)->unit_name,
        ])
        ->values();

    $brandOptionsForJs = $brands
        ->map(fn ($brand) => [
            'id' => $brand->id,
            'name' => $brand->brand_name,
            'material_type_id' => $brand->material_type_id,
        ])
        ->values();

    $specificationOptionsForJs = $specifications
        ->map(fn ($specification) => [
            'id' => $specification->id,
            'name' => $specification->specification_name,
            'material_type_id' => $specification->material_type_id,
        ])
        ->values();

    $gradeOptionsForJs = $grades
        ->map(fn ($grade) => [
            'id' => $grade->id,
            'name' => $grade->grade_name,
            'material_type_id' => $grade->material_type_id,
        ])
        ->values();

    $materialGroupsForJs = $materialGroups->values();
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Edit Material Requirement #{{ $materialRequirement->id }}
            </h1>

            <p class="mt-1 text-gray-500">
                Update requirement details and material rows while this entry is in Draft status.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('material-requirements.show', $materialRequirement) }}"
               class="inline-flex items-center justify-center rounded-lg bg-slate-700 px-5 py-2.5 font-semibold text-white hover:bg-slate-800">
                View
            </a>

            <a href="{{ route('material-requirements.index') }}"
               class="inline-flex items-center justify-center rounded-lg bg-gray-600 px-5 py-2.5 font-semibold text-white hover:bg-gray-700">
                Back
            </a>
        </div>
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
          action="{{ route('material-requirements.update', $materialRequirement) }}"
          id="material-requirement-form">

        @csrf
        @method('PUT')

        <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-xl font-bold text-gray-800">
                Requirement Information
            </h2>

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
                                {{ (string) old('project_id', $materialRequirement->project_id) === (string) $project->id ? 'selected' : '' }}>
                                {{ $project->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Project Block
                    </label>

                    <select name="project_block_id"
                            id="project_block_id"
                            class="{{ $inputClass }}">

                        <option value="">Select Block</option>

                        @foreach($projectBlocks as $block)
                            <option value="{{ $block->id }}"
                                    data-project="{{ $block->project_id }}"
                                {{ (string) old('project_block_id', $materialRequirement->project_block_id) === (string) $block->id ? 'selected' : '' }}>
                                {{ $block->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Required Date
                    </label>

                    <input type="date"
                           name="required_date"
                           value="{{ old('required_date', $materialRequirement->required_date?->format('Y-m-d')) }}"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Priority <span class="text-red-500">*</span>
                    </label>

                    <select name="priority"
                            class="{{ $inputClass }}"
                            required>

                        @foreach(['Low', 'Normal', 'High', 'Urgent'] as $priority)
                            <option value="{{ $priority }}"
                                {{ old('priority', $materialRequirement->priority) === $priority ? 'selected' : '' }}>
                                {{ $priority }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2 xl:col-span-4">
                    <label class="{{ $labelClass }}">
                        General Remarks
                    </label>

                    <textarea name="remarks"
                              rows="3"
                              class="{{ $inputClass }}"
                              placeholder="General notes for this material requirement">{{ old('remarks', $materialRequirement->remarks) }}</textarea>
                </div>

            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="flex flex-col gap-3 border-b border-gray-200 p-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">
                        Requirement Items
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Edit existing materials or add more rows.
                    </p>
                </div>

                <button type="button"
                        id="add-item-row"
                        class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white hover:bg-green-700">
                    + Add Material Row
                </button>
            </div>

            <div class="overflow-x-auto">

                <table class="min-w-[1850px] w-full text-sm">

                    <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="w-14 px-3 py-3 text-center">#</th>
                            <th class="min-w-44 px-3 py-3 text-left">Activity Division</th>
                            <th class="min-w-52 px-3 py-3 text-left">Activity</th>
                            <th class="min-w-48 px-3 py-3 text-left">Material Group</th>
                            <th class="min-w-52 px-3 py-3 text-left">Material</th>
                            <th class="min-w-44 px-3 py-3 text-left">Brand</th>
                            <th class="min-w-44 px-3 py-3 text-left">Specification</th>
                            <th class="min-w-44 px-3 py-3 text-left">Grade / Rating</th>
                            <th class="min-w-36 px-3 py-3 text-left">Required Qty</th>
                            <th class="min-w-36 px-3 py-3 text-left">Fulfilled Qty</th>
                            <th class="min-w-36 px-3 py-3 text-left">Unit</th>
                            <th class="min-w-52 px-3 py-3 text-left">Remarks</th>
                            <th class="w-24 px-3 py-3 text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody id="material-items-body"
                           class="divide-y divide-gray-200">

                        @foreach($formItems as $rowIndex => $formItem)

                            <tr class="material-item-row align-top"
                                data-row-index="{{ $rowIndex }}">

                                <td class="row-number px-3 py-3 text-center font-semibold">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="px-3 py-3">
                                    <select name="items[{{ $rowIndex }}][activity_division_id]"
                                            class="{{ $inputClass }} activity-division-select">
                                        <option value="">Select Division</option>

                                        @foreach($activityDivisions as $division)
                                            <option value="{{ $division->id }}"
                                                {{ (string) ($formItem['activity_division_id'] ?? '') === (string) $division->id ? 'selected' : '' }}>
                                                {{ $division->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-3">
                                    <select name="items[{{ $rowIndex }}][activity_id]"
                                            class="{{ $inputClass }} activity-select">
                                        <option value="">Select Activity</option>

                                        @foreach($activities as $activity)
                                            <option value="{{ $activity->id }}"
                                                    data-division="{{ $activity->activity_division_id }}"
                                                {{ (string) ($formItem['activity_id'] ?? '') === (string) $activity->id ? 'selected' : '' }}>
                                                {{ $activity->activity_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-3">
                                    <select class="{{ $inputClass }} material-group-select">
                                        <option value="">Select Group</option>

                                        @foreach($materialGroups as $group)
                                            <option value="{{ $group }}">
                                                {{ $group }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-3">
                                    <select name="items[{{ $rowIndex }}][material_type_id]"
                                            class="{{ $inputClass }} material-type-select"
                                            required>

                                        <option value="">Select Material</option>

                                        @foreach($materialTypes as $materialType)
                                            <option value="{{ $materialType->id }}"
                                                    data-group="{{ $materialType->material_group }}"
                                                    data-unit-id="{{ $materialType->unit_master_id }}"
                                                    data-unit-name="{{ optional($materialType->unit)->unit_name }}"
                                                {{ (string) ($formItem['material_type_id'] ?? '') === (string) $materialType->id ? 'selected' : '' }}>
                                                {{ $materialType->material_type_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-3">
                                    <select name="items[{{ $rowIndex }}][brand_master_id]"
                                            class="{{ $inputClass }} brand-select">
                                        <option value="">Select Brand</option>

                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}"
                                                    data-material-type="{{ $brand->material_type_id }}"
                                                {{ (string) ($formItem['brand_master_id'] ?? '') === (string) $brand->id ? 'selected' : '' }}>
                                                {{ $brand->brand_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-3">
                                    <select name="items[{{ $rowIndex }}][material_specification_id]"
                                            class="{{ $inputClass }} specification-select">
                                        <option value="">Select Specification</option>

                                        @foreach($specifications as $specification)
                                            <option value="{{ $specification->id }}"
                                                    data-material-type="{{ $specification->material_type_id }}"
                                                {{ (string) ($formItem['material_specification_id'] ?? '') === (string) $specification->id ? 'selected' : '' }}>
                                                {{ $specification->specification_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-3">
                                    <select name="items[{{ $rowIndex }}][material_grade_id]"
                                            class="{{ $inputClass }} grade-select">
                                        <option value="">Select Grade / Rating</option>

                                        @foreach($grades as $grade)
                                            <option value="{{ $grade->id }}"
                                                    data-material-type="{{ $grade->material_type_id }}"
                                                {{ (string) ($formItem['material_grade_id'] ?? '') === (string) $grade->id ? 'selected' : '' }}>
                                                {{ $grade->grade_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-3">
                                    <input type="number"
                                           step="0.001"
                                           min="0.001"
                                           name="items[{{ $rowIndex }}][required_quantity]"
                                           value="{{ $formItem['required_quantity'] ?? '' }}"
                                           class="{{ $inputClass }}"
                                           required>
                                </td>

                                <td class="px-3 py-3">
                                    <input type="number"
                                           step="0.001"
                                           min="0"
                                           name="items[{{ $rowIndex }}][fulfilled_quantity]"
                                           value="{{ $formItem['fulfilled_quantity'] ?? 0 }}"
                                           class="{{ $inputClass }}">
                                </td>

                                <td class="px-3 py-3">
                                    <input type="hidden"
                                           name="items[{{ $rowIndex }}][unit_master_id]"
                                           value="{{ $formItem['unit_master_id'] ?? '' }}"
                                           class="unit-id-input">

                                    <input type="text"
                                           class="{{ $inputClass }} unit-name-input bg-gray-100"
                                           readonly
                                           placeholder="Auto">
                                </td>

                                <td class="px-3 py-3">
                                    <input type="text"
                                           name="items[{{ $rowIndex }}][remarks]"
                                           value="{{ $formItem['remarks'] ?? '' }}"
                                           class="{{ $inputClass }}"
                                           placeholder="Optional">
                                </td>

                                <td class="px-3 py-3 text-center">
                                    <button type="button"
                                            class="remove-item-row rounded bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">
                                        Remove
                                    </button>
                                </td>

                            </tr>

                        @endforeach

                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 p-5 text-sm text-gray-500">
                Material, Brand, Specification, Grade/Rating and Unit are filtered automatically from the selected Material Group and Material.
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <button type="submit"
                    class="rounded-lg bg-blue-600 px-7 py-3 font-semibold text-white hover:bg-blue-700">
                Update Material Requirement
            </button>

            <a href="{{ route('material-requirements.show', $materialRequirement) }}"
               class="rounded-lg bg-gray-500 px-7 py-3 font-semibold text-white hover:bg-gray-600">
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

            rebuildSelect(
                activitySelect,
                'Select Activity',
                activityOptions.filter(function (activity) {
                    return divisionId === ''
                        || String(activity.division_id) === String(divisionId);
                }),
                selectedValue
            );
        }

        function filterMaterialTypes(selectedValue = '') {
            const group = groupSelect.value;

            rebuildSelect(
                typeSelect,
                'Select Material',
                materialTypeOptions.filter(function (type) {
                    return group === '' || type.group === group;
                }),
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

            rebuildSelect(
                brandSelect,
                'Select Brand',
                brandOptions.filter(function (brand) {
                    return String(brand.material_type_id) === String(materialTypeId);
                }),
                options.brandId || ''
            );

            rebuildSelect(
                specificationSelect,
                'Select Specification',
                specificationOptions.filter(function (specification) {
                    return String(specification.material_type_id) === String(materialTypeId);
                }),
                options.specificationId || ''
            );

            rebuildSelect(
                gradeSelect,
                'Select Grade / Rating',
                gradeOptions.filter(function (grade) {
                    return String(grade.material_type_id) === String(materialTypeId);
                }),
                options.gradeId || ''
            );
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

        row.querySelector('.remove-item-row')
            .addEventListener('click', function () {
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
    }

    function buildNewRow(index) {
        const row = document.createElement('tr');

        row.className = 'material-item-row align-top';
        row.dataset.rowIndex = index;

        row.innerHTML = `
            <td class="row-number px-3 py-3 text-center font-semibold"></td>

            <td class="px-3 py-3">
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

            <td class="px-3 py-3">
                <select name="items[${index}][activity_id]"
                        class="{{ $inputClass }} activity-select">
                    <option value="">Select Activity</option>
                </select>
            </td>

            <td class="px-3 py-3">
                <select class="{{ $inputClass }} material-group-select">
                    <option value="">Select Group</option>

                    ${materialGroups.map(function (group) {
                        return `<option value="${escapeHtml(group)}">${escapeHtml(group)}</option>`;
                    }).join('')}
                </select>
            </td>

            <td class="px-3 py-3">
                <select name="items[${index}][material_type_id]"
                        class="{{ $inputClass }} material-type-select"
                        required>
                    <option value="">Select Material</option>
                </select>
            </td>

            <td class="px-3 py-3">
                <select name="items[${index}][brand_master_id]"
                        class="{{ $inputClass }} brand-select">
                    <option value="">Select Brand</option>
                </select>
            </td>

            <td class="px-3 py-3">
                <select name="items[${index}][material_specification_id]"
                        class="{{ $inputClass }} specification-select">
                    <option value="">Select Specification</option>
                </select>
            </td>

            <td class="px-3 py-3">
                <select name="items[${index}][material_grade_id]"
                        class="{{ $inputClass }} grade-select">
                    <option value="">Select Grade / Rating</option>
                </select>
            </td>

            <td class="px-3 py-3">
                <input type="number"
                       step="0.001"
                       min="0.001"
                       name="items[${index}][required_quantity]"
                       class="{{ $inputClass }}"
                       required>
            </td>

            <td class="px-3 py-3">
                <input type="number"
                       step="0.001"
                       min="0"
                       value="0"
                       name="items[${index}][fulfilled_quantity]"
                       class="{{ $inputClass }}">
            </td>

            <td class="px-3 py-3">
                <input type="hidden"
                       name="items[${index}][unit_master_id]"
                       class="unit-id-input">

                <input type="text"
                       class="{{ $inputClass }} unit-name-input bg-gray-100"
                       readonly
                       placeholder="Auto">
            </td>

            <td class="px-3 py-3">
                <input type="text"
                       name="items[${index}][remarks]"
                       class="{{ $inputClass }}"
                       placeholder="Optional">
            </td>

            <td class="px-3 py-3 text-center">
                <button type="button"
                        class="remove-item-row rounded bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">
                    Remove
                </button>
            </td>
        `;

        return row;
    }

    function refreshRowNumbers() {
        body.querySelectorAll('.material-item-row')
            .forEach(function (row, index) {
                row.querySelector('.row-number').textContent = index + 1;
            });
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    if (projectSelect && blockSelect) {
        const originalBlockOptions = Array.from(blockSelect.options)
            .map(function (option) {
                return option.cloneNode(true);
            });

        function filterBlocks() {
            const selectedProject = projectSelect.value;
            const currentBlock = blockSelect.value;

            blockSelect.innerHTML = '';
            blockSelect.add(new Option('Select Block', ''));

            originalBlockOptions.forEach(function (option) {
                if (option.value === '') {
                    return;
                }

                if (
                    selectedProject === ''
                    || String(option.dataset.project) === String(selectedProject)
                ) {
                    const cloned = option.cloneNode(true);

                    if (String(cloned.value) === String(currentBlock)) {
                        cloned.selected = true;
                    }

                    blockSelect.add(cloned);
                }
            });
        }

        projectSelect.addEventListener('change', function () {
            blockSelect.value = '';
            filterBlocks();
        });

        filterBlocks();
    }

    addRowButton.addEventListener('click', function () {
        const newRow = buildNewRow(rowIndex++);
        body.appendChild(newRow);
        initializeRow(newRow);
        refreshRowNumbers();
    });

    body.querySelectorAll('.material-item-row')
        .forEach(initializeRow);

    refreshRowNumbers();
});
</script>

@endsection
