@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-base text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:py-2.5 sm:text-sm';
    $labelClass = 'mb-1.5 block text-sm font-semibold text-gray-700';

    $activityOptionsForJs = $activities
        ->map(fn ($activity) => [
            'id' => $activity->id,
            'name' => $activity->activity_name,
            'division_id' => $activity->activity_division_id,
            'unit' => $activity->unit,
        ])
        ->values();
@endphp

<div class="mx-auto max-w-full">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">
                Create Tomorrow Plan
            </h1>

            <p class="mt-1 text-gray-500">
                Plan tomorrow's work with labour, materials, machinery and constraints.
            </p>
        </div>

        <a href="{{ route('tomorrow-plans.index') }}"
           class="inline-flex w-full items-center justify-center rounded-lg bg-gray-600 px-5 py-3 font-semibold text-white hover:bg-gray-700 sm:w-auto sm:py-2.5">
            Back
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-300 bg-red-50 p-4 text-red-700">
            <p class="mb-2 font-semibold">Please correct the following:</p>

            <ul class="ml-5 list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('tomorrow-plans.store') }}"
          id="tomorrow-plan-form">

        @csrf

        {{-- Basic Details --}}
        <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="mb-5 rounded-xl bg-[#0F2A52] px-4 py-3 text-white">
                <h2 class="text-lg font-bold sm:text-xl">Basic Details</h2>
                <p class="mt-1 text-xs text-blue-100">
                    Select project, date and planning priority.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
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
                    <label class="{{ $labelClass }}">
                        Planned Date <span class="text-red-500">*</span>
                    </label>

                    <input type="date"
                           name="planned_date"
                           value="{{ old('planned_date', now()->addDay()->format('Y-m-d')) }}"
                           class="{{ $inputClass }}"
                           required>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Priority <span class="text-red-500">*</span>
                    </label>

                    <select name="priority"
                            class="{{ $inputClass }}"
                            required>
                        <option value="Normal" {{ old('priority', 'Normal') === 'Normal' ? 'selected' : '' }}>Normal</option>
                        <option value="Urgent" {{ old('priority') === 'Urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="Critical" {{ old('priority') === 'Critical' ? 'selected' : '' }}>Critical</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Location --}}
        <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="mb-5 rounded-xl bg-[#0F2A52] px-4 py-3 text-white">
                <h2 class="text-lg font-bold sm:text-xl">Location</h2>
                <p class="mt-1 text-xs text-blue-100">
                    Location options are filtered from the selected project.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div>
                    <label class="{{ $labelClass }}">Block</label>
                    <select name="project_block_id"
                            id="project_block_id"
                            class="{{ $inputClass }}">
                        <option value="">Select Block</option>
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Floor</label>
                    <select name="project_floor_id"
                            id="project_floor_id"
                            class="{{ $inputClass }}">
                        <option value="">Select Floor</option>
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Unit</label>
                    <select name="project_unit_id"
                            id="project_unit_id"
                            class="{{ $inputClass }}">
                        <option value="">Select Unit</option>
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Room / Space</label>
                    <select name="project_room_id"
                            id="project_room_id"
                            class="{{ $inputClass }}">
                        <option value="">Select Room</option>
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Sub-Space / Element</label>
                    <select name="project_subspace_id"
                            id="project_subspace_id"
                            class="{{ $inputClass }}">
                        <option value="">Select Sub-Space</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Planned Work --}}
        <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="mb-5 rounded-xl bg-[#0F2A52] px-4 py-3 text-white">
                <h2 class="text-lg font-bold sm:text-xl">Planned Work</h2>
                <p class="mt-1 text-xs text-blue-100">
                    Choose the Activity Division first to filter the Activity list.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div>
                    <label class="{{ $labelClass }}">Activity Division</label>

                    <select id="activity_division_id"
                            class="{{ $inputClass }}">
                        <option value="">Select Activity Division</option>

                        @foreach($activityDivisions as $division)
                            <option value="{{ $division->id }}">
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Activity <span class="text-red-500">*</span>
                    </label>

                    <select name="activity_id"
                            id="activity_id"
                            class="{{ $inputClass }}"
                            required>
                        <option value="">Select Activity</option>
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Planned Quantity <span class="text-red-500">*</span>
                    </label>

                    <input type="number"
                           step="0.01"
                           min="0.01"
                           name="planned_quantity"
                           value="{{ old('planned_quantity') }}"
                           class="{{ $inputClass }}"
                           required>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Unit</label>

                    <input type="text"
                           name="unit"
                           id="unit"
                           value="{{ old('unit') }}"
                           class="{{ $inputClass }}"
                           placeholder="Auto from activity / enter manually">
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
            </div>
        </div>

        {{-- Labour Requirement --}}
        <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="mb-5 rounded-xl bg-[#0F2A52] px-4 py-3 text-white">
                <h2 class="text-lg font-bold sm:text-xl">Labour Requirement</h2>
                <p class="mt-1 text-xs text-blue-100">
                    Estimate manpower required for tomorrow's planned activity.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-4 md:gap-4">
                <div>
                    <label class="{{ $labelClass }}">Total Planned Labour</label>
                    <input type="number"
                           min="0"
                           name="planned_labour"
                           value="{{ old('planned_labour', 0) }}"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Skilled Labour</label>
                    <input type="number"
                           min="0"
                           name="required_skilled_labour"
                           value="{{ old('required_skilled_labour', 0) }}"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Semi-Skilled</label>
                    <input type="number"
                           min="0"
                           name="required_semiskilled_labour"
                           value="{{ old('required_semiskilled_labour', 0) }}"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Helpers</label>
                    <input type="number"
                           min="0"
                           name="required_helpers"
                           value="{{ old('required_helpers', 0) }}"
                           class="{{ $inputClass }}">
                </div>
            </div>
        </div>

        {{-- Resources --}}
        <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="mb-5 rounded-xl bg-[#0F2A52] px-4 py-3 text-white">
                <h2 class="text-lg font-bold sm:text-xl">Resources & Dependencies</h2>
                <p class="mt-1 text-xs text-blue-100">
                    Record materials, machinery and approvals needed before work begins.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="{{ $labelClass }}">Materials Required</label>
                    <textarea name="materials_required"
                              rows="3"
                              class="{{ $inputClass }}">{{ old('materials_required') }}</textarea>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Machinery Required</label>
                    <textarea name="machinery_required"
                              rows="3"
                              class="{{ $inputClass }}">{{ old('machinery_required') }}</textarea>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Drawing Required?</label>
                    <select name="drawing_required"
                            class="{{ $inputClass }}"
                            required>
                        <option value="0" {{ old('drawing_required', '0') == '0' ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('drawing_required') == '1' ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Client Approval Required?</label>
                    <select name="client_approval_required"
                            class="{{ $inputClass }}"
                            required>
                        <option value="0" {{ old('client_approval_required', '0') == '0' ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('client_approval_required') == '1' ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">Responsible Person</label>
                    <input type="text"
                           name="responsible_person"
                           value="{{ old('responsible_person') }}"
                           class="{{ $inputClass }}">
                </div>
            </div>
        </div>

        {{-- Risk / Remarks --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="mb-5 rounded-xl bg-[#0F2A52] px-4 py-3 text-white">
                <h2 class="text-lg font-bold sm:text-xl">Risk / Remarks</h2>
                <p class="mt-1 text-xs text-blue-100">
                    Highlight constraints that could affect tomorrow's execution.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="{{ $labelClass }}">Risks / Constraints</label>
                    <textarea name="risks_constraints"
                              rows="4"
                              class="{{ $inputClass }}">{{ old('risks_constraints') }}</textarea>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Remarks</label>
                    <textarea name="remarks"
                              rows="4"
                              class="{{ $inputClass }}">{{ old('remarks') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="sticky bottom-[68px] z-30 mt-6 grid grid-cols-1 gap-3 border-t border-gray-200 bg-white/95 py-3 backdrop-blur sm:flex sm:flex-wrap lg:static lg:border-0 lg:bg-transparent lg:py-0">
            <button type="submit"
                    class="w-full rounded-xl bg-blue-600 px-7 py-3.5 font-semibold text-white shadow-sm hover:bg-blue-700 sm:w-auto sm:py-3">
                Save Tomorrow Plan
            </button>

            <a href="{{ route('tomorrow-plans.index') }}"
               class="w-full rounded-xl bg-gray-500 px-7 py-3.5 text-center font-semibold text-white hover:bg-gray-600 sm:w-auto sm:py-3">
                Cancel
            </a>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function () {
    /*
    |--------------------------------------------------------------------------
    | Project Location Cascade
    |--------------------------------------------------------------------------
    */
    const project = document.getElementById('project_id');
    const block = document.getElementById('project_block_id');
    const floor = document.getElementById('project_floor_id');
    const unit = document.getElementById('project_unit_id');
    const room = document.getElementById('project_room_id');
    const subspace = document.getElementById('project_subspace_id');

    const oldLocation = {
        block: @json(old('project_block_id')),
        floor: @json(old('project_floor_id')),
        unit: @json(old('project_unit_id')),
        room: @json(old('project_room_id')),
        subspace: @json(old('project_subspace_id')),
    };

    function resetSelect(select, placeholder) {
        if (!select) return;

        select.innerHTML = '';

        const option = document.createElement('option');
        option.value = '';
        option.textContent = placeholder;

        select.appendChild(option);
    }

    function populateSelect(select, rows, selectedValue, placeholder) {
        resetSelect(select, placeholder);

        rows.forEach(row => {
            const option = document.createElement('option');
            option.value = String(row.id);
            option.textContent = row.name || row.label || `#${row.id}`;
            option.selected = String(row.id) === String(selectedValue || '');
            select.appendChild(option);
        });
    }

    async function fetchRows(url) {
        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`Location lookup failed (${response.status})`);
            }

            const payload = await response.json();

            if (Array.isArray(payload)) return payload;
            if (Array.isArray(payload.data)) return payload.data;

            return [];
        } catch (error) {
            console.error(error);
            alert('Unable to load project locations. Please try again.');
            return [];
        }
    }

    function resetAfterProject() {
        resetSelect(block, 'Select Block');
        resetSelect(floor, 'Select Floor');
        resetSelect(unit, 'Select Unit');
        resetSelect(room, 'Select Room');
        resetSelect(subspace, 'Select Sub-Space');
    }

    function resetAfterBlock() {
        resetSelect(floor, 'Select Floor');
        resetSelect(unit, 'Select Unit');
        resetSelect(room, 'Select Room');
        resetSelect(subspace, 'Select Sub-Space');
    }

    function resetAfterFloor() {
        resetSelect(unit, 'Select Unit');
        resetSelect(room, 'Select Room');
        resetSelect(subspace, 'Select Sub-Space');
    }

    function resetAfterUnit() {
        resetSelect(room, 'Select Room');
        resetSelect(subspace, 'Select Sub-Space');
    }

    function resetAfterRoom() {
        resetSelect(subspace, 'Select Sub-Space');
    }

    async function loadBlocks(selected = '') {
        resetAfterProject();
        if (!project?.value) return;

        populateSelect(
            block,
            await fetchRows(`/ajax/projects/${encodeURIComponent(project.value)}/blocks`),
            selected,
            'Select Block'
        );
    }

    async function loadFloors(selected = '') {
        resetAfterBlock();
        if (!block?.value) return;

        populateSelect(
            floor,
            await fetchRows(`/ajax/blocks/${encodeURIComponent(block.value)}/floors`),
            selected,
            'Select Floor'
        );
    }

    async function loadUnits(selected = '') {
        resetAfterFloor();
        if (!floor?.value) return;

        populateSelect(
            unit,
            await fetchRows(`/ajax/floors/${encodeURIComponent(floor.value)}/units`),
            selected,
            'Select Unit'
        );
    }

    async function loadRooms(selected = '') {
        resetAfterUnit();
        if (!unit?.value) return;

        populateSelect(
            room,
            await fetchRows(`/ajax/units/${encodeURIComponent(unit.value)}/rooms`),
            selected,
            'Select Room'
        );
    }

    async function loadSubspaces(selected = '') {
        resetAfterRoom();
        if (!room?.value) return;

        populateSelect(
            subspace,
            await fetchRows(`/ajax/rooms/${encodeURIComponent(room.value)}/subspaces`),
            selected,
            'Select Sub-Space'
        );
    }

    project?.addEventListener('change', () => loadBlocks());
    block?.addEventListener('change', () => loadFloors());
    floor?.addEventListener('change', () => loadUnits());
    unit?.addEventListener('change', () => loadRooms());
    room?.addEventListener('change', () => loadSubspaces());

    async function initializeLocationCascade() {
        if (!project?.value) {
            resetAfterProject();
            return;
        }

        await loadBlocks(oldLocation.block);

        if (oldLocation.block) {
            await loadFloors(oldLocation.floor);
        }

        if (oldLocation.floor) {
            await loadUnits(oldLocation.unit);
        }

        if (oldLocation.unit) {
            await loadRooms(oldLocation.room);
        }

        if (oldLocation.room) {
            await loadSubspaces(oldLocation.subspace);
        }
    }

    await initializeLocationCascade();

    /*
    |--------------------------------------------------------------------------
    | Activity Division -> Activity
    |--------------------------------------------------------------------------
    */
    const division = document.getElementById('activity_division_id');
    const activity = document.getElementById('activity_id');
    const unitInput = document.getElementById('unit');

    const activities = @json($activityOptionsForJs);
    const oldActivityId = @json(old('activity_id'));

    function rebuildActivities(selectedId = '') {
        const divisionId = division?.value || '';

        const filtered = activities.filter(item => {
            return divisionId === ''
                || String(item.division_id) === String(divisionId);
        });

        activity.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select Activity';
        activity.appendChild(placeholder);

        filtered.forEach(item => {
            const option = document.createElement('option');
            option.value = String(item.id);
            option.textContent = item.name;
            option.selected = String(item.id) === String(selectedId || '');
            activity.appendChild(option);
        });
    }

    function syncActivityUnit() {
        const selected = activities.find(item => {
            return String(item.id) === String(activity?.value || '');
        });

        if (selected?.unit && !unitInput.value) {
            unitInput.value = selected.unit;
        }
    }

    if (oldActivityId) {
        const selected = activities.find(item => {
            return String(item.id) === String(oldActivityId);
        });

        if (selected?.division_id) {
            division.value = String(selected.division_id);
        }
    }

    rebuildActivities(oldActivityId);
    syncActivityUnit();

    division?.addEventListener('change', function () {
        rebuildActivities('');
        if (unitInput) {
            unitInput.value = '';
        }
    });

    activity?.addEventListener('change', function () {
        if (unitInput) {
            unitInput.value = '';
        }

        syncActivityUnit();
    });
});
</script>

@endsection
