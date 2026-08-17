@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-base text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:py-2.5 sm:text-sm';
    $labelClass = 'mb-1.5 block text-sm font-semibold text-gray-700';

    $oldPhotos = collect(old('photos', [
        [
            'photo_type' => 'Issue',
            'caption' => '',
        ],
    ]))->values();
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">
                Report Site Issue
            </h1>

            <p class="mt-1 text-gray-500">
                Record site issues independently. The issue can be linked to the DPR later without re-entering the information.
            </p>
        </div>

        <a href="{{ route('site-issues.index') }}"
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
          action="{{ route('site-issues.store') }}"
          enctype="multipart/form-data"
          id="siteIssueForm">

        @csrf

        {{-- Project & Issue Date --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

            <x-rds.section-title
                title="Project & Issue Date"
                subtitle="Select the project and date on which the issue was observed."
                icon="🏗️"
            />

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

                <div class="xl:col-span-2">
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
                        Issue Date <span class="text-red-500">*</span>
                    </label>

                    <input type="date"
                           name="issue_date"
                           value="{{ old('issue_date', now()->format('Y-m-d')) }}"
                           class="{{ $inputClass }}"
                           required>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Reported By
                    </label>

                    <input type="text"
                           value="{{ auth()->user()->name }}"
                           class="{{ $inputClass }} bg-gray-100"
                           readonly>
                </div>

            </div>
        </div>

        {{-- Location --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

            <x-rds.section-title
                title="Issue Location"
                subtitle="Select the most specific location available."
                icon="📍"
            />

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">

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
                    <label class="{{ $labelClass }}">Unit / Flat</label>

                    <select name="project_unit_id"
                            id="project_unit_id"
                            class="{{ $inputClass }}">
                        <option value="">Select Unit</option>

                        @foreach($projectUnits as $unit)
                            <option value="{{ $unit->id }}"
                                    data-project="{{ $unit->project_id }}"
                                    data-block="{{ $unit->project_block_id }}"
                                    data-floor="{{ $unit->project_floor_id }}"
                                {{ (string) old('project_unit_id') === (string) $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Room / Space</label>

                    <select name="project_room_id"
                            id="project_room_id"
                            class="{{ $inputClass }}">
                        <option value="">Select Room</option>

                        @foreach($projectRooms as $room)
                            <option value="{{ $room->id }}"
                                    data-unit="{{ $room->project_unit_id }}"
                                {{ (string) old('project_room_id') === (string) $room->id ? 'selected' : '' }}>
                                {{ $room->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Sub-space / Element</label>

                    <select name="project_subspace_id"
                            id="project_subspace_id"
                            class="{{ $inputClass }}">
                        <option value="">Select Sub-space</option>

                        @foreach($projectSubspaces as $subspace)
                            <option value="{{ $subspace->id }}"
                                    data-room="{{ $subspace->project_room_id }}"
                                {{ (string) old('project_subspace_id') === (string) $subspace->id ? 'selected' : '' }}>
                                {{ $subspace->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        {{-- Related Activity --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

            <x-rds.section-title
                title="Related Activity"
                subtitle="Optional. Link the issue to the construction activity affected."
                icon="⚙️"
            />

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                <div>
                    <label class="{{ $labelClass }}">
                        Activity Division
                    </label>

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
                        Activity
                    </label>

                    <select name="activity_id"
                            id="activity_id"
                            class="{{ $inputClass }}">
                        <option value="">Select Activity</option>

                        @foreach($activities as $activity)
                            <option value="{{ $activity->id }}"
                                    data-division="{{ $activity->activity_division_id }}"
                                {{ (string) old('activity_id') === (string) $activity->id ? 'selected' : '' }}>
                                {{ $activity->activity_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        {{-- Issue Details --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

            <x-rds.section-title
                title="Issue Details"
                subtitle="Describe the issue clearly so PMO and responsible teams can act without further clarification."
                icon="⚠️"
            />

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

                <div>
                    <label class="{{ $labelClass }}">
                        Issue Type <span class="text-red-500">*</span>
                    </label>

                    <select name="issue_type"
                            class="{{ $inputClass }}"
                            required>
                        <option value="">Select Issue Type</option>

                        @foreach($issueTypes as $type)
                            <option value="{{ $type }}"
                                {{ old('issue_type') === $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Priority <span class="text-red-500">*</span>
                    </label>

                    <select name="priority"
                            class="{{ $inputClass }}"
                            required>
                        @foreach($priorities as $priority)
                            <option value="{{ $priority }}"
                                {{ old('priority', 'Medium') === $priority ? 'selected' : '' }}>
                                {{ $priority }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Status <span class="text-red-500">*</span>
                    </label>

                    <select name="status"
                            class="{{ $inputClass }}"
                            required>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}"
                                {{ old('status', 'Open') === $status ? 'selected' : '' }}>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Target Closure Date
                    </label>

                    <input type="date"
                           name="target_closure_date"
                           value="{{ old('target_closure_date') }}"
                           class="{{ $inputClass }}">
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">
                        Issue Title <span class="text-red-500">*</span>
                    </label>

                    <input type="text"
                           name="title"
                           value="{{ old('title') }}"
                           maxlength="255"
                           class="{{ $inputClass }}"
                           placeholder="Short, clear issue title"
                           required>
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">
                        Responsible Person / Team
                    </label>

                    <input type="text"
                           name="responsible_person"
                           value="{{ old('responsible_person') }}"
                           maxlength="255"
                           class="{{ $inputClass }}"
                           placeholder="Optional">
                </div>

                <div class="md:col-span-2 xl:col-span-4">
                    <label class="{{ $labelClass }}">
                        Description <span class="text-red-500">*</span>
                    </label>

                    <textarea name="description"
                              rows="4"
                              maxlength="10000"
                              class="{{ $inputClass }}"
                              placeholder="What happened? What is affected? What action is required?"
                              required>{{ old('description') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">
                        Root Cause
                    </label>

                    <textarea name="root_cause"
                              rows="3"
                              maxlength="2000"
                              class="{{ $inputClass }}"
                              placeholder="Optional at reporting stage">{{ old('root_cause') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">
                        Related Activity / Reference
                    </label>

                    <textarea name="related_activity"
                              rows="3"
                              maxlength="255"
                              class="{{ $inputClass }}"
                              placeholder="Optional additional activity reference">{{ old('related_activity') }}</textarea>
                </div>

            </div>
        </div>

        {{-- Escalation --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

            <x-rds.section-title
                title="Escalation"
                subtitle="Use escalation only when the issue requires higher-level intervention."
                icon="📢"
            />

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-4">
                    <input type="checkbox"
                           name="escalated_to_pmo"
                           value="1"
                           class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                           {{ old('escalated_to_pmo') ? 'checked' : '' }}>

                    <div>
                        <div class="font-semibold text-gray-800">
                            Escalate to PMO
                        </div>

                        <div class="mt-1 text-sm text-gray-500">
                            Use for planning, coordination, contractor, material or execution issues requiring PMO action.
                        </div>
                    </div>
                </label>

                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-4">
                    <input type="checkbox"
                           name="escalated_to_management"
                           value="1"
                           class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                           {{ old('escalated_to_management') ? 'checked' : '' }}>

                    <div>
                        <div class="font-semibold text-gray-800">
                            Escalate to Management
                        </div>

                        <div class="mt-1 text-sm text-gray-500">
                            Use only for critical issues that require management intervention.
                        </div>
                    </div>
                </label>

            </div>
        </div>

        {{-- Photos --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6"
             id="photoUploader">

            <x-rds.section-title
                title="Site Issue Photos"
                subtitle="Upload photographic evidence. You can add multiple photos."
                icon="📷"
            >
                <x-slot:actions>
                    <button type="button"
                            id="addPhotoButton"
                            class="w-full rounded-lg bg-green-600 px-4 py-3 text-sm font-semibold text-white hover:bg-green-700 sm:w-auto sm:py-2">
                        + Add Photo
                    </button>
                </x-slot:actions>
            </x-rds.section-title>

            <div id="photoRows"
                 class="space-y-4">

                @foreach($oldPhotos as $photoIndex => $photo)
                    <div class="rounded-lg border border-gray-200 p-4 photo-row">

                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-start">

                            <div class="lg:col-span-3">
                                <label class="{{ $labelClass }}">
                                    Photo Type
                                </label>

                                <select name="photos[{{ $photoIndex }}][photo_type]"
                                        class="{{ $inputClass }}">
                                    @foreach($photoTypes as $type)
                                        <option value="{{ $type }}"
                                            {{ ($photo['photo_type'] ?? 'Issue') === $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="lg:col-span-4">
                                <label class="{{ $labelClass }}">
                                    Caption
                                </label>

                                <input type="text"
                                       name="photos[{{ $photoIndex }}][caption]"
                                       value="{{ $photo['caption'] ?? '' }}"
                                       maxlength="500"
                                       class="{{ $inputClass }}"
                                       placeholder="Optional description">
                            </div>

                            <div class="lg:col-span-4">
                                <label class="{{ $labelClass }}">
                                    Image
                                </label>

                                <input type="file"
                                       name="photos[{{ $photoIndex }}][file]"
                                       class="{{ $inputClass }} photo-file"
                                       accept="image/jpeg,image/png,image/webp,image/*">

                                <p class="mt-1 text-xs text-gray-500">
                                    JPG, PNG or WEBP. Maximum 10 MB.
                                </p>
                            </div>

                            <div class="lg:col-span-1 lg:pt-6">
                                <button type="button"
                                        class="w-full rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700 remove-photo">
                                    Remove
                                </button>
                            </div>

                        </div>

                        <div class="mt-4 hidden rounded-lg border border-gray-200 bg-gray-50 p-3 photo-preview-wrap">
                            <div class="flex flex-wrap items-start gap-4">
                                <img src=""
                                     alt="Photo preview"
                                     class="h-28 w-36 rounded-lg border border-gray-200 bg-white object-cover photo-preview">

                                <div class="text-sm text-gray-600">
                                    <p class="font-semibold text-gray-800">
                                        Preview
                                    </p>

                                    <p class="mt-1 photo-name"></p>
                                    <p class="mt-1 text-xs text-gray-500 photo-size"></p>
                                </div>
                            </div>
                        </div>

                    </div>
                @endforeach

            </div>

            <template id="photoRowTemplate">
                <div class="rounded-lg border border-gray-200 p-4 photo-row">

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-start">

                        <div class="lg:col-span-3">
                            <label class="{{ $labelClass }}">
                                Photo Type
                            </label>

                            <select name="photos[__PHOTO_INDEX__][photo_type]"
                                    class="{{ $inputClass }}">
                                @foreach($photoTypes as $type)
                                    <option value="{{ $type }}">
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="lg:col-span-4">
                            <label class="{{ $labelClass }}">
                                Caption
                            </label>

                            <input type="text"
                                   name="photos[__PHOTO_INDEX__][caption]"
                                   maxlength="500"
                                   class="{{ $inputClass }}"
                                   placeholder="Optional description">
                        </div>

                        <div class="lg:col-span-4">
                            <label class="{{ $labelClass }}">
                                Image
                            </label>

                            <input type="file"
                                   name="photos[__PHOTO_INDEX__][file]"
                                   class="{{ $inputClass }} photo-file"
                                   accept="image/jpeg,image/png,image/webp,image/*">

                            <p class="mt-1 text-xs text-gray-500">
                                JPG, PNG or WEBP. Maximum 10 MB.
                            </p>
                        </div>

                        <div class="lg:col-span-1 lg:pt-6">
                            <button type="button"
                                    class="w-full rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700 remove-photo">
                                Remove
                            </button>
                        </div>

                    </div>

                    <div class="mt-4 hidden rounded-lg border border-gray-200 bg-gray-50 p-3 photo-preview-wrap">
                        <div class="flex flex-wrap items-start gap-4">
                            <img src=""
                                 alt="Photo preview"
                                 class="h-28 w-36 rounded-lg border border-gray-200 bg-white object-cover photo-preview">

                            <div class="text-sm text-gray-600">
                                <p class="font-semibold text-gray-800">
                                    Preview
                                </p>

                                <p class="mt-1 photo-name"></p>
                                <p class="mt-1 text-xs text-gray-500 photo-size"></p>
                            </div>
                        </div>
                    </div>

                </div>
            </template>
        </div>

        {{-- Remarks --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

            <x-rds.section-title
                title="Remarks"
                subtitle="Optional additional notes."
                icon="📝"
            />

            <textarea name="remarks"
                      rows="3"
                      maxlength="5000"
                      class="{{ $inputClass }}"
                      placeholder="Additional remarks">{{ old('remarks') }}</textarea>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">

            <button type="submit"
                    class="rounded-lg bg-blue-600 px-7 py-3 font-semibold text-white hover:bg-blue-700">
                Save Site Issue
            </button>

            <a href="{{ route('site-issues.index') }}"
               class="rounded-lg bg-gray-500 px-7 py-3 font-semibold text-white hover:bg-gray-600">
                Cancel
            </a>

        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Project -> Block -> Floor -> Unit -> Room -> Sub-space
    | AJAX cascade (same endpoints used by Work Done)
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

    function populateSelect(
        select,
        rows,
        selectedValue,
        placeholder
    ) {
        resetSelect(select, placeholder);

        rows.forEach(row => {
            const option = document.createElement('option');

            option.value = String(row.id);
            option.textContent =
                row.name
                || row.label
                || `#${row.id}`;

            option.selected =
                String(row.id) === String(selectedValue || '');

            select.appendChild(option);
        });
    }

    async function fetchLocationRows(url) {
        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(
                    `Location lookup failed (${response.status})`
                );
            }

            const payload = await response.json();

            if (Array.isArray(payload)) {
                return payload;
            }

            if (Array.isArray(payload.data)) {
                return payload.data;
            }

            return [];
        } catch (error) {
            console.error(error);

            alert(
                'Unable to load project locations. Please try again.'
            );

            return [];
        }
    }

    function resetAfterProject() {
        resetSelect(block, 'Select Block');
        resetSelect(floor, 'Select Floor');
        resetSelect(unit, 'Select Unit');
        resetSelect(room, 'Select Room');
        resetSelect(subspace, 'Select Sub-space');
    }

    function resetAfterBlock() {
        resetSelect(floor, 'Select Floor');
        resetSelect(unit, 'Select Unit');
        resetSelect(room, 'Select Room');
        resetSelect(subspace, 'Select Sub-space');
    }

    function resetAfterFloor() {
        resetSelect(unit, 'Select Unit');
        resetSelect(room, 'Select Room');
        resetSelect(subspace, 'Select Sub-space');
    }

    function resetAfterUnit() {
        resetSelect(room, 'Select Room');
        resetSelect(subspace, 'Select Sub-space');
    }

    function resetAfterRoom() {
        resetSelect(subspace, 'Select Sub-space');
    }

    async function loadBlocks(selectedValue = '') {
        resetAfterProject();

        const projectId = project?.value || '';

        if (!projectId) return;

        const rows = await fetchLocationRows(
            `/ajax/projects/${encodeURIComponent(projectId)}/blocks`
        );

        populateSelect(
            block,
            rows,
            selectedValue,
            'Select Block'
        );
    }

    async function loadFloors(selectedValue = '') {
        resetAfterBlock();

        const blockId = block?.value || '';

        if (!blockId) return;

        const rows = await fetchLocationRows(
            `/ajax/blocks/${encodeURIComponent(blockId)}/floors`
        );

        populateSelect(
            floor,
            rows,
            selectedValue,
            'Select Floor'
        );
    }

    async function loadUnits(selectedValue = '') {
        resetAfterFloor();

        const floorId = floor?.value || '';

        if (!floorId) return;

        const rows = await fetchLocationRows(
            `/ajax/floors/${encodeURIComponent(floorId)}/units`
        );

        populateSelect(
            unit,
            rows,
            selectedValue,
            'Select Unit'
        );
    }

    async function loadRooms(selectedValue = '') {
        resetAfterUnit();

        const unitId = unit?.value || '';

        if (!unitId) return;

        const rows = await fetchLocationRows(
            `/ajax/units/${encodeURIComponent(unitId)}/rooms`
        );

        populateSelect(
            room,
            rows,
            selectedValue,
            'Select Room'
        );
    }

    async function loadSubspaces(selectedValue = '') {
        resetAfterRoom();

        const roomId = room?.value || '';

        if (!roomId) return;

        const rows = await fetchLocationRows(
            `/ajax/rooms/${encodeURIComponent(roomId)}/subspaces`
        );

        populateSelect(
            subspace,
            rows,
            selectedValue,
            'Select Sub-space'
        );
    }

    project?.addEventListener('change', async function () {
        await loadBlocks();
    });

    block?.addEventListener('change', async function () {
        await loadFloors();
    });

    floor?.addEventListener('change', async function () {
        await loadUnits();
    });

    unit?.addEventListener('change', async function () {
        await loadRooms();
    });

    room?.addEventListener('change', async function () {
        await loadSubspaces();
    });

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

    initializeLocationCascade();

    /*
    |--------------------------------------------------------------------------
    | Activity Division -> Activity
    |--------------------------------------------------------------------------
    */

    const division = document.getElementById('activity_division_id');
    const activity = document.getElementById('activity_id');
    const selectedActivityId = @json(old('activity_id'));

    const activityOptions = Array.from(
        activity?.options || []
    ).map(option => option.cloneNode(true));

    function filterActivities() {
        if (!activity) return;

        const divisionId = division?.value || '';

        activity.innerHTML =
            '<option value="">Select Activity</option>';

        activityOptions.forEach(option => {
            if (!option.value) return;

            if (
                !divisionId
                || String(option.dataset.division) === String(divisionId)
            ) {
                const cloned = option.cloneNode(true);

                if (
                    selectedActivityId
                    && String(cloned.value) === String(selectedActivityId)
                ) {
                    cloned.selected = true;
                }

                activity.appendChild(cloned);
            }
        });
    }

    if (selectedActivityId) {
        const selectedOption = activityOptions.find(
            option =>
                String(option.value) === String(selectedActivityId)
        );

        if (
            selectedOption
            && selectedOption.dataset.division
            && division
        ) {
            division.value =
                selectedOption.dataset.division;
        }
    }

    division?.addEventListener(
        'change',
        function () {
            if (activity) {
                activity.value = '';
            }

            filterActivities();

            if (activity) {
                activity.value = '';
            }
        }
    );

    filterActivities();

    /*
    |--------------------------------------------------------------------------
    | Photo Rows + Preview
    |--------------------------------------------------------------------------
    */

    const photoRows = document.getElementById('photoRows');
    const photoTemplate = document.getElementById('photoRowTemplate');
    const addPhotoButton = document.getElementById('addPhotoButton');

    function renumberPhotos() {
        document.querySelectorAll('.photo-row')
            .forEach((row, index) => {
                row.querySelectorAll('[name]')
                    .forEach(field => {
                        field.name = field.name.replace(
                            /photos\[\d+\]/g,
                            `photos[${index}]`
                        );
                    });
            });
    }

    function previewPhoto(row) {
        const fileInput =
            row.querySelector('.photo-file');

        const file =
            fileInput?.files?.[0];

        const wrap =
            row.querySelector('.photo-preview-wrap');

        const image =
            row.querySelector('.photo-preview');

        const name =
            row.querySelector('.photo-name');

        const size =
            row.querySelector('.photo-size');

        if (!file) {
            wrap?.classList.add('hidden');
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            alert('Each Site Issue photo must be 10 MB or smaller.');

            fileInput.value = '';
            wrap?.classList.add('hidden');

            return;
        }

        const url =
            URL.createObjectURL(file);

        if (image) {
            image.src = url;

            image.onload = function () {
                URL.revokeObjectURL(url);
            };
        }

        if (name) {
            name.textContent = file.name;
        }

        if (size) {
            size.textContent =
                (file.size / 1024 / 1024)
                    .toFixed(2)
                + ' MB';
        }

        wrap?.classList.remove('hidden');
    }

    function bindPhotoRow(row) {
        if (
            !row
            || row.dataset.bound === '1'
        ) {
            return;
        }

        row.dataset.bound = '1';

        row.querySelector('.photo-file')
            ?.addEventListener(
                'change',
                () => previewPhoto(row)
            );

        row.querySelector('.remove-photo')
            ?.addEventListener(
                'click',
                function () {
                    const rows =
                        document.querySelectorAll(
                            '.photo-row'
                        );

                    if (rows.length <= 1) {
                        row.querySelectorAll(
                            'input, select'
                        ).forEach(field => {
                            if (field.type === 'file') {
                                field.value = '';
                            } else if (
                                field.tagName === 'SELECT'
                            ) {
                                field.selectedIndex = 0;
                            } else {
                                field.value = '';
                            }
                        });

                        row.querySelector(
                            '.photo-preview-wrap'
                        )?.classList.add('hidden');

                        return;
                    }

                    row.remove();
                    renumberPhotos();
                }
            );
    }

    document.querySelectorAll('.photo-row')
        .forEach(bindPhotoRow);

    addPhotoButton?.addEventListener(
        'click',
        function () {
            const index =
                document.querySelectorAll(
                    '.photo-row'
                ).length;

            const html =
                photoTemplate.innerHTML.replaceAll(
                    '__PHOTO_INDEX__',
                    String(index)
                );

            photoRows.insertAdjacentHTML(
                'beforeend',
                html
            );

            const rows =
                document.querySelectorAll(
                    '.photo-row'
                );

            bindPhotoRow(
                rows[rows.length - 1]
            );

            renumberPhotos();
        }
    );
});
</script>

@endsection
