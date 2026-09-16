@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-md border border-gray-300 bg-white px-2.5 py-2 text-sm text-gray-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
    $labelClass = 'mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500';

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

@endphp

<div class="mx-auto max-w-full">

    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Add Material Consumption
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Record one site consumption entry containing one or more material items.
            </p>
        </div>

        <a href="{{ route('material-consumed.index') }}"
           class="inline-flex w-full items-center justify-center rounded-md bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 sm:w-auto">
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

        <div class="mb-4 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-200 px-4 py-3">
                <h2 class="text-sm font-bold text-gray-800">Consumption Information</h2>
                <p class="mt-0.5 text-xs text-gray-500">Select the site location, contractor and consumption date.</p>
            </div>

            <div class="grid grid-cols-1 gap-x-4 gap-y-3 p-4 md:grid-cols-2 xl:grid-cols-4">

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
                              rows="2"
                              class="{{ $inputClass }}"
                              placeholder="General notes for this material consumption">{{ old('remarks') }}</textarea>
                </div>

            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="flex flex-col gap-3 border-b border-gray-200 px-4 py-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-sm font-bold text-gray-800">
                        Material Items
                    </h2>

                    <p class="mt-0.5 text-xs text-gray-500">
                        Add every material consumed under this entry.
                    </p>
                </div>

                <button type="button"
                        id="add-item-row"
                        class="w-full rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-700 md:w-auto">
                    + Add Material Row
                </button>
            </div>

            <div class="overflow-visible lg:overflow-x-auto">

                <table class="block w-full text-sm lg:table lg:min-w-[1280px]">

                    <thead class="hidden bg-gray-100 text-xs uppercase tracking-wide text-gray-600 lg:table-header-group">
                        <tr>
                            <th class="w-14 px-3 py-3 text-center">#</th>
                            <th class="min-w-36 px-2.5 py-2.5 text-left">Activity Division</th>
                            <th class="min-w-40 px-2.5 py-2.5 text-left">Activity</th>
                            <th class="min-w-[360px] px-2.5 py-2.5 text-left">Available Project Stock</th>
                            <th class="min-w-28 px-2.5 py-2.5 text-left">Available</th>
                            <th class="min-w-28 px-2.5 py-2.5 text-left">Consumed</th>
                            <th class="min-w-28 px-2.5 py-2.5 text-left">Wastage</th>
                            <th class="min-w-44 px-2.5 py-2.5 text-left">Wastage Reason</th>
                            <th class="min-w-40 px-2.5 py-2.5 text-left">Remarks</th>
                            <th class="w-20 px-2.5 py-2.5 text-left">Action</th>
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
                                    </select>
                                </td>

                                <td data-mobile-label="Available Project Stock" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                                    <select class="{{ $inputClass }} stock-identity-select" required>
                                        <option value="">Select Project first</option>
                                    </select>

                                    <input type="hidden" name="items[{{ $rowIndex }}][material_type_id]"
                                           value="{{ $oldItem['material_type_id'] ?? '' }}" class="material-type-id-input">
                                    <input type="hidden" name="items[{{ $rowIndex }}][brand_master_id]"
                                           value="{{ $oldItem['brand_master_id'] ?? '' }}" class="brand-id-input">
                                    <input type="hidden" name="items[{{ $rowIndex }}][material_specification_id]"
                                           value="{{ $oldItem['material_specification_id'] ?? '' }}" class="specification-id-input">
                                    <input type="hidden" name="items[{{ $rowIndex }}][material_grade_id]"
                                           value="{{ $oldItem['material_grade_id'] ?? '' }}" class="grade-id-input">
                                    <input type="hidden" name="items[{{ $rowIndex }}][unit_master_id]"
                                           value="{{ $oldItem['unit_master_id'] ?? '' }}" class="unit-id-input">
                                </td>

                                <td data-mobile-label="Available Qty" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                                    <input type="text"
                                           class="{{ $inputClass }} available-quantity-input bg-gray-100"
                                           readonly
                                           value=""
                                           placeholder="—">
                                </td>

                                <td data-mobile-label="Consumed Qty" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                                    <input type="number" step="0.001" min="0.001"
                                           name="items[{{ $rowIndex }}][quantity_consumed]"
                                           value="{{ $oldItem['quantity_consumed'] ?? '' }}"
                                           class="{{ $inputClass }} consumed-quantity-input" required>
                                </td>

                                <td data-mobile-label="Wastage Qty" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                                    <input type="number" step="0.001" min="0"
                                           name="items[{{ $rowIndex }}][wastage_quantity]"
                                           value="{{ $oldItem['wastage_quantity'] ?? 0 }}"
                                           class="{{ $inputClass }} wastage-quantity-input">
                                </td>

                                <td data-mobile-label="Wastage Reason" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                                    <input type="text"
                                           name="items[{{ $rowIndex }}][wastage_reason]"
                                           value="{{ $oldItem['wastage_reason'] ?? '' }}"
                                           class="{{ $inputClass }} wastage-reason-input"
                                           placeholder="Required when wastage > 0">
                                </td>

                                <td data-mobile-label="Remarks" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                                    <input type="text"
                                           name="items[{{ $rowIndex }}][remarks]"
                                           value="{{ $oldItem['remarks'] ?? '' }}"
                                           class="{{ $inputClass }}"
                                           placeholder="Optional">
                                </td>

                                <td data-mobile-label="Action" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:text-left lg:before:hidden">
                                    <button type="button"
                                            class="remove-item-row w-full rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-xs font-semibold text-red-700 hover:bg-red-100 lg:w-auto lg:rounded-md lg:border-0 lg:bg-red-600 lg:px-2.5 lg:py-2 lg:text-white lg:hover:bg-red-700">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 bg-gray-50 px-4 py-3 text-xs leading-5 text-gray-500">
                Only stock currently available for the selected Project can be chosen. Brand, Specification, Grade/Rating and Unit come directly from the received stock identity. Available quantity already accounts for submitted consumption reservations. Wastage reason becomes mandatory when wastage quantity is greater than zero.
            </div>
        </div>

        <div class="mt-4 flex flex-col gap-2 sm:flex-row">
            <button type="submit"
                    class="w-full rounded-md bg-blue-600 px-5 py-2.5 text-center text-sm font-semibold text-white hover:bg-blue-700 sm:w-auto">
                Save Material Consumption
            </button>

            <a href="{{ route('material-consumed.index') }}"
               class="w-full rounded-md bg-slate-600 px-5 py-2.5 text-center text-sm font-semibold text-white hover:bg-slate-700 sm:w-auto">
                Cancel
            </a>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.getElementById('material-items-body');
    const addRowButton = document.getElementById('add-item-row');
    const form = document.getElementById('material-consumption-form');

    const projectSelect = document.getElementById('project_id');
    const blockSelect = document.getElementById('project_block_id');
    const floorSelect = document.getElementById('project_floor_id');
    const unitSelect = document.getElementById('project_unit_id');
    const roomSelect = document.getElementById('project_room_id');
    const subspaceSelect = document.getElementById('project_subspace_id');

    const activityOptions = @json($activityOptionsForJs);
    const inventoryUrlTemplate = @json(route('material-consumed.project-inventory', ['project' => '__PROJECT__']));

    let rowIndex = body.querySelectorAll('.material-item-row').length;
    let projectInventory = [];
    let inventoryRequestSequence = 0;

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

    function stockLabel(item) {
        const parts = [
            item.material_type_name,
            item.brand_name,
            item.specification_name,
            item.grade_name,
        ].filter(Boolean);

        return `${parts.join(' — ')} | Available: ${formatQuantity(item.available_qty)} ${item.unit_name || ''}`.trim();
    }

    function formatQuantity(value) {
        const number = Number(value || 0);

        return Number.isInteger(number)
            ? String(number)
            : number.toFixed(3).replace(/0+$/, '').replace(/\.$/, '');
    }

    function identityMatchesRow(item, row) {
        return String(item.material_type_id ?? '') === String(row.querySelector('.material-type-id-input').value ?? '')
            && String(item.brand_master_id ?? '') === String(row.querySelector('.brand-id-input').value ?? '')
            && String(item.material_specification_id ?? '') === String(row.querySelector('.specification-id-input').value ?? '')
            && String(item.material_grade_id ?? '') === String(row.querySelector('.grade-id-input').value ?? '')
            && String(item.unit_master_id ?? '') === String(row.querySelector('.unit-id-input').value ?? '');
    }

    function clearStockIdentity(row) {
        row.querySelector('.material-type-id-input').value = '';
        row.querySelector('.brand-id-input').value = '';
        row.querySelector('.specification-id-input').value = '';
        row.querySelector('.grade-id-input').value = '';
        row.querySelector('.unit-id-input').value = '';
        row.querySelector('.available-quantity-input').value = '';
        row.dataset.availableQty = '';
    }

    function applyStockIdentity(row, item) {
        row.querySelector('.material-type-id-input').value = item.material_type_id ?? '';
        row.querySelector('.brand-id-input').value = item.brand_master_id ?? '';
        row.querySelector('.specification-id-input').value = item.material_specification_id ?? '';
        row.querySelector('.grade-id-input').value = item.material_grade_id ?? '';
        row.querySelector('.unit-id-input').value = item.unit_master_id ?? '';
        row.querySelector('.available-quantity-input').value =
            `${formatQuantity(item.available_qty)} ${item.unit_name || ''}`.trim();
        row.dataset.availableQty = String(item.available_qty ?? 0);
    }

    function populateStockSelect(row, preserveExisting = false) {
        const stockSelect = row.querySelector('.stock-identity-select');
        let selectedKey = '';

        if (preserveExisting) {
            const matchingItem = projectInventory.find(function (item) {
                return identityMatchesRow(item, row);
            });

            selectedKey = matchingItem?.stock_key || '';
        } else {
            clearStockIdentity(row);
        }

        stockSelect.innerHTML = '';

        if (!projectSelect.value) {
            stockSelect.add(makeOption('', 'Select Project first'));
            stockSelect.disabled = true;
            return;
        }

        if (projectInventory.length === 0) {
            stockSelect.add(makeOption('', 'No available stock for this Project'));
            stockSelect.disabled = true;
            return;
        }

        stockSelect.disabled = false;
        stockSelect.add(makeOption('', 'Select Available Stock'));

        projectInventory.forEach(function (item) {
            stockSelect.add(
                makeOption(
                    item.stock_key,
                    stockLabel(item),
                    item.stock_key === selectedKey
                )
            );
        });

        if (selectedKey) {
            const selectedItem = projectInventory.find(item => item.stock_key === selectedKey);

            if (selectedItem) {
                applyStockIdentity(row, selectedItem);
            }
        } else if (preserveExisting) {
            clearStockIdentity(row);
        }
    }

    async function loadProjectInventory(preserveExisting = false) {
        const projectId = projectSelect.value;
        const requestSequence = ++inventoryRequestSequence;

        projectInventory = [];

        body.querySelectorAll('.material-item-row').forEach(function (row) {
            const stockSelect = row.querySelector('.stock-identity-select');
            stockSelect.innerHTML = '';
            stockSelect.add(makeOption('', projectId ? 'Loading available stock...' : 'Select Project first'));
            stockSelect.disabled = true;

            if (!preserveExisting) {
                clearStockIdentity(row);
            }
        });

        if (!projectId) {
            return;
        }

        try {
            const url = inventoryUrlTemplate.replace('__PROJECT__', encodeURIComponent(projectId));
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`Inventory request failed with status ${response.status}.`);
            }

            const payload = await response.json();

            if (requestSequence !== inventoryRequestSequence) {
                return;
            }

            projectInventory = Array.isArray(payload.inventory) ? payload.inventory : [];

            body.querySelectorAll('.material-item-row').forEach(function (row) {
                populateStockSelect(row, preserveExisting);
            });
        } catch (error) {
            console.error(error);

            if (requestSequence !== inventoryRequestSequence) {
                return;
            }

            body.querySelectorAll('.material-item-row').forEach(function (row) {
                const stockSelect = row.querySelector('.stock-identity-select');
                stockSelect.innerHTML = '';
                stockSelect.add(makeOption('', 'Unable to load Project stock'));
                stockSelect.disabled = true;
                clearStockIdentity(row);
            });
        }
    }

    function initializeRow(row) {
        const divisionSelect = row.querySelector('.activity-division-select');
        const activitySelect = row.querySelector('.activity-select');
        const stockSelect = row.querySelector('.stock-identity-select');
        const wastageQuantityInput = row.querySelector('.wastage-quantity-input');
        const wastageReasonInput = row.querySelector('.wastage-reason-input');

        const preservedActivityId = activitySelect.value;

        function filterActivities(selectedValue = '') {
            const divisionId = divisionSelect.value;

            const filtered = activityOptions.filter(function (activity) {
                return divisionId === ''
                    || String(activity.division_id) === String(divisionId);
            });

            rebuildSelect(activitySelect, 'Select Activity', filtered, selectedValue);
        }

        function updateWastageRequirement() {
            const wastage = Number(wastageQuantityInput.value || 0);
            wastageReasonInput.required = wastage > 0;
        }

        divisionSelect.addEventListener('change', function () {
            filterActivities('');
        });

        stockSelect.addEventListener('change', function () {
            const selectedItem = projectInventory.find(function (item) {
                return item.stock_key === stockSelect.value;
            });

            if (selectedItem) {
                applyStockIdentity(row, selectedItem);
            } else {
                clearStockIdentity(row);
            }
        });

        wastageQuantityInput.addEventListener('input', updateWastageRequirement);

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
        updateWastageRequirement();
        populateStockSelect(row, true);
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
                <select name="items[${index}][activity_division_id]" class="{{ $inputClass }} activity-division-select">
                    <option value="">Select Division</option>
                    @foreach($activityDivisions as $division)
                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                    @endforeach
                </select>
            </td>

            <td data-mobile-label="Activity" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <select name="items[${index}][activity_id]" class="{{ $inputClass }} activity-select">
                    <option value="">Select Activity</option>
                </select>
            </td>

            <td data-mobile-label="Available Project Stock" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <select class="{{ $inputClass }} stock-identity-select" required>
                    <option value="">Select Available Stock</option>
                </select>
                <input type="hidden" name="items[${index}][material_type_id]" class="material-type-id-input">
                <input type="hidden" name="items[${index}][brand_master_id]" class="brand-id-input">
                <input type="hidden" name="items[${index}][material_specification_id]" class="specification-id-input">
                <input type="hidden" name="items[${index}][material_grade_id]" class="grade-id-input">
                <input type="hidden" name="items[${index}][unit_master_id]" class="unit-id-input">
            </td>

            <td data-mobile-label="Available Qty" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <input type="text" class="{{ $inputClass }} available-quantity-input bg-gray-100" readonly placeholder="—">
            </td>

            <td data-mobile-label="Consumed Qty" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <input type="number" step="0.001" min="0.001" name="items[${index}][quantity_consumed]" class="{{ $inputClass }} consumed-quantity-input" required>
            </td>

            <td data-mobile-label="Wastage Qty" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <input type="number" step="0.001" min="0" value="0" name="items[${index}][wastage_quantity]" class="{{ $inputClass }} wastage-quantity-input">
            </td>

            <td data-mobile-label="Wastage Reason" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <input type="text" name="items[${index}][wastage_reason]" class="{{ $inputClass }} wastage-reason-input" placeholder="Required when wastage > 0">
            </td>

            <td data-mobile-label="Remarks" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <input type="text" name="items[${index}][remarks]" class="{{ $inputClass }}" placeholder="Optional">
            </td>

            <td data-mobile-label="Action" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:text-left lg:before:hidden">
                <button type="button" class="remove-item-row w-full rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-xs font-semibold text-red-700 hover:bg-red-100 lg:w-auto lg:rounded-md lg:border-0 lg:bg-red-600 lg:px-2.5 lg:py-2 lg:text-white lg:hover:bg-red-700">
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
            option => projectId === '' || String(option.dataset.project) === String(projectId),
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
                const projectMatch = projectId === '' || String(option.dataset.project) === String(projectId);
                const blockMatch = blockId === '' || String(option.dataset.block) === String(blockId);
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
                const projectMatch = projectId === '' || String(option.dataset.project) === String(projectId);
                const blockMatch = blockId === '' || String(option.dataset.block) === String(blockId);
                const floorMatch = floorId === '' || String(option.dataset.floor) === String(floorId);
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
                const projectMatch = projectId === '' || String(option.dataset.project) === String(projectId);
                const blockMatch = blockId === '' || String(option.dataset.block) === String(blockId);
                const floorMatch = floorId === '' || String(option.dataset.floor) === String(floorId);
                const unitMatch = unitId === '' || String(option.dataset.unit) === String(unitId);
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
                const projectMatch = projectId === '' || String(option.dataset.project) === String(projectId);
                const blockMatch = blockId === '' || String(option.dataset.block) === String(blockId);
                const floorMatch = floorId === '' || String(option.dataset.floor) === String(floorId);
                const unitMatch = unitId === '' || String(option.dataset.unit) === String(unitId);
                const roomMatch = roomId === '' || String(option.dataset.room) === String(roomId);

                return projectMatch && blockMatch && floorMatch && unitMatch && roomMatch;
            },
            'Select Sub-space'
        );
    }

    addRowButton.addEventListener('click', function () {
        if (!projectSelect.value) {
            alert('Select the Project before adding material rows.');
            return;
        }

        const newRow = buildNewRow(rowIndex++);
        body.appendChild(newRow);
        initializeRow(newRow);
        populateStockSelect(newRow, false);
        refreshRowNumbers();
    });

    projectSelect.addEventListener('change', function () {
        blockSelect.value = '';
        floorSelect.value = '';
        unitSelect.value = '';
        roomSelect.value = '';
        subspaceSelect.value = '';
        filterProjectLocations();
        loadProjectInventory(false);
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

    form.addEventListener('submit', function (event) {
        const totalsByStockKey = new Map();
        let message = '';

        body.querySelectorAll('.material-item-row').forEach(function (row, index) {
            if (message) {
                return;
            }

            const stockSelect = row.querySelector('.stock-identity-select');
            const consumed = Number(row.querySelector('.consumed-quantity-input').value || 0);
            const wastage = Number(row.querySelector('.wastage-quantity-input').value || 0);
            const available = Number(row.dataset.availableQty || 0);
            const requested = consumed + wastage;

            if (!stockSelect.value) {
                message = `Row ${index + 1}: select an available Project stock item.`;
                return;
            }

            const currentTotal = totalsByStockKey.get(stockSelect.value) || 0;
            const combinedTotal = currentTotal + requested;
            totalsByStockKey.set(stockSelect.value, combinedTotal);

            if (combinedTotal > available + 0.000001) {
                message = `Row ${index + 1}: total consumption plus wastage for this stock item exceeds the available quantity of ${formatQuantity(available)}.`;
            }
        });

        if (message) {
            event.preventDefault();
            alert(message);
        }
    });

    body.querySelectorAll('.material-item-row').forEach(initializeRow);

    refreshRowNumbers();
    filterProjectLocations();

    if (projectSelect.value) {
        loadProjectInventory(true);
    }
});
</script>

@endsection
