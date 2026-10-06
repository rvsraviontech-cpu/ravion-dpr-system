@extends('layouts.app')

@section('content')
@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-base text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:py-2.5 sm:text-sm';
    $labelClass = 'mb-1.5 block text-sm font-semibold text-gray-700';

    $oldPhotoRows = collect(old('photos', []))->values();

    $locationData = [
        'blocks' => $projectBlocks->map(fn ($row) => [
            'id' => $row->id,
            'name' => $row->name,
            'project_id' => $row->project_id,
        ])->values(),
        'floors' => $projectFloors->map(fn ($row) => [
            'id' => $row->id,
            'name' => $row->name,
            'project_id' => $row->project_id,
            'project_block_id' => $row->project_block_id,
        ])->values(),
        'units' => $projectUnits->map(fn ($row) => [
            'id' => $row->id,
            'name' => $row->name,
            'project_id' => $row->project_id,
            'project_block_id' => $row->project_block_id,
            'project_floor_id' => $row->project_floor_id,
        ])->values(),
        'rooms' => $projectRooms->map(fn ($row) => [
            'id' => $row->id,
            'name' => $row->name,
            'project_id' => $row->project_id,
            'project_block_id' => $row->project_block_id,
            'project_floor_id' => $row->project_floor_id,
            'project_unit_id' => $row->project_unit_id,
        ])->values(),
        'subspaces' => $projectSubspaces->map(fn ($row) => [
            'id' => $row->id,
            'name' => $row->name,
            'project_id' => $row->project_id,
            'project_block_id' => $row->project_block_id,
            'project_floor_id' => $row->project_floor_id,
            'project_unit_id' => $row->project_unit_id,
            'project_room_id' => $row->project_room_id,
        ])->values(),
    ];

    $workData = [
        'packages' => $workPackages->map(fn ($row) => [
            'id' => $row->id,
            'name' => $row->name,
        ])->values(),
        'sections' => $workSections->map(fn ($row) => [
            'id' => $row->id,
            'name' => $row->name,
            'work_package_id' => $row->work_package_id,
        ])->values(),
        'activities' => $workActivities->map(fn ($row) => [
            'id' => $row->id,
            'name' => $row->name,
            'work_package_id' => $row->work_package_id,
            'work_section_id' => $row->work_section_id,
        ])->values(),
    ];
@endphp

<div class="mx-auto max-w-full pb-28 sm:pb-8">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">Add Site Photos</h1>
            <p class="mt-1 text-gray-500">
                Capture daily site evidence independently. These photos can be included in the DPR later.
            </p>
        </div>

        <a href="{{ route('site-photos.index') }}"
           class="inline-flex w-full items-center justify-center rounded-lg bg-gray-600 px-5 py-3 font-semibold text-white hover:bg-gray-700 sm:w-auto sm:py-2.5">
            Back to Site Photos
        </a>
    </div>

    @if(session('error'))
        <div class="mb-5 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-lg border border-red-300 bg-red-50 p-4 text-red-700">
            <p class="mb-2 font-semibold">Please correct the following:</p>
            <ul class="ml-5 list-disc space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('site-photos.store') }}"
          enctype="multipart/form-data"
          id="sitePhotoForm">
        @csrf

        {{-- Project & Date --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
            <x-rds.section-title
                title="Project & Photo Date"
                subtitle="Select where and when these photographs were recorded."
                icon="🏗️"
            />

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="xl:col-span-2">
                    <label class="{{ $labelClass }}">Project <span class="text-red-500">*</span></label>
                    <select name="project_id" id="project_id" class="{{ $inputClass }}" required>
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
                    <label class="{{ $labelClass }}">Photo Date <span class="text-red-500">*</span></label>
                    <input type="date"
                           name="photo_date"
                           value="{{ old('photo_date', now()->format('Y-m-d')) }}"
                           class="{{ $inputClass }}"
                           required>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Reported By</label>
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
                title="Photo Location"
                subtitle="Optional. Select the most specific site location available. Rooms may belong directly to a floor or to a unit."
                icon="📍"
            />

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div>
                    <label class="{{ $labelClass }}">Block</label>
                    <select name="project_block_id" id="project_block_id" class="{{ $inputClass }}">
                        <option value="">Select Block</option>
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Floor</label>
                    <select name="project_floor_id" id="project_floor_id" class="{{ $inputClass }}">
                        <option value="">Select Floor</option>
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Unit / Flat</label>
                    <select name="project_unit_id" id="project_unit_id" class="{{ $inputClass }}">
                        <option value="">Select Unit</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Leave blank for rooms directly under the floor.</p>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Room / Space</label>
                    <select name="project_room_id" id="project_room_id" class="{{ $inputClass }}">
                        <option value="">Select Room</option>
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Sub-space / Element</label>
                    <select name="project_subspace_id" id="project_subspace_id" class="{{ $inputClass }}">
                        <option value="">Select Sub-space</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Work Classification --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
            <x-rds.section-title
                title="Work Classification"
                subtitle="Optional. Classify the photographs against the Work Execution Catalogue when they relate to a specific activity."
                icon="⚙️"
            />

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="{{ $labelClass }}">Work Package</label>
                    <select name="work_package_id" id="work_package_id" class="{{ $inputClass }}">
                        <option value="">Select Work Package</option>
                        @foreach($workPackages as $package)
                            <option value="{{ $package->id }}"
                                {{ (string) old('work_package_id') === (string) $package->id ? 'selected' : '' }}>
                                {{ $package->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Work Section</label>
                    <select name="work_section_id" id="work_section_id" class="{{ $inputClass }}">
                        <option value="">Select Work Section</option>
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Work Activity</label>
                    <select name="work_activity_id" id="work_activity_id" class="{{ $inputClass }}">
                        <option value="">Select Work Activity</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Entry Details --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
            <x-rds.section-title
                title="Photo Details"
                subtitle="Category is required. Title, contractor and remarks are optional."
                icon="📝"
            />

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="{{ $labelClass }}">Category <span class="text-red-500">*</span></label>
                    <select name="category" class="{{ $inputClass }}" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}"
                                {{ old('category', 'Work Progress') === $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Contractor</label>
                    <select name="contractor_id" class="{{ $inputClass }}">
                        <option value="">No Contractor / Not Applicable</option>
                        @foreach($contractors as $contractor)
                            <option value="{{ $contractor->id }}"
                                {{ (string) old('contractor_id') === (string) $contractor->id ? 'selected' : '' }}>
                                {{ $contractor->contractor_name ?? $contractor->name ?? ('Contractor #' . $contractor->id) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">Title</label>
                    <input type="text"
                           name="title"
                           value="{{ old('title') }}"
                           maxlength="255"
                           class="{{ $inputClass }}"
                           placeholder="Optional short description, e.g. First-floor slab reinforcement">
                </div>

                <div class="md:col-span-2 xl:col-span-4">
                    <label class="{{ $labelClass }}">Remarks</label>
                    <textarea name="remarks"
                              rows="3"
                              maxlength="5000"
                              class="{{ $inputClass }}"
                              placeholder="Optional observations or notes">{{ old('remarks') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Camera-first uploader --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6" id="sitePhotoUploader">
            <x-rds.section-title
                title="Site Photographs"
                subtitle="At least one photograph is required. Add up to 20 photos in one entry."
                icon="📷"
            >
                <x-slot:actions>
                    <span id="photoCountBadge"
                          class="inline-flex rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-800">
                        0 Photos
                    </span>
                </x-slot:actions>
            </x-rds.section-title>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <button type="button"
                        id="takePhotoButton"
                        class="flex min-h-16 items-center justify-center gap-3 rounded-xl bg-[#0F2A52] px-5 py-4 text-base font-bold text-white shadow-sm hover:bg-[#173a70]">
                    <span class="text-2xl">📷</span>
                    <span>Take Photo</span>
                </button>

                <button type="button"
                        id="choosePhotosButton"
                        class="flex min-h-16 items-center justify-center gap-3 rounded-xl border-2 border-[#0F2A52] bg-white px-5 py-4 text-base font-bold text-[#0F2A52] shadow-sm hover:bg-slate-50">
                    <span class="text-2xl">🖼️</span>
                    <span>Choose From Gallery</span>
                </button>
            </div>

            <input type="file"
                   id="cameraInput"
                   class="hidden"
                   accept="image/jpeg,image/png,image/webp,image/*"
                   capture="environment">

            <input type="file"
                   id="galleryInput"
                   class="hidden"
                   accept="image/jpeg,image/png,image/webp,image/*"
                   multiple>

            <div class="mt-3 rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                You can take photographs one after another or select several from the gallery. New selections are added to the existing list.
            </div>

            <div id="photoEmptyState"
                 class="mt-5 rounded-xl border-2 border-dashed border-gray-300 px-5 py-10 text-center">
                <div class="text-4xl">📸</div>
                <div class="mt-3 font-semibold text-gray-700">No photographs selected yet</div>
                <p class="mt-1 text-sm text-gray-500">Take a site photo or choose existing images from the device.</p>
            </div>

            <div id="photoCards" class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"></div>

            {{-- Actual multipart fields are generated here by JavaScript. --}}
            <div id="photoFileInputs" class="hidden"></div>
        </div>

        {{-- Status --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
            <x-rds.section-title
                title="Submission"
                subtitle="Submitted is recommended for normal Engineer reporting."
                icon="✅"
            />

            <div class="max-w-md">
                <label class="{{ $labelClass }}">Status</label>
                <select name="status" class="{{ $inputClass }}">
                    @foreach($statuses as $status)
                        <option value="{{ $status }}"
                            {{ old('status', 'Submitted') === $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Desktop actions --}}
        <div class="mt-6 hidden gap-3 sm:flex sm:flex-wrap">
            <button type="submit"
                    id="desktopSubmitButton"
                    class="rounded-xl bg-blue-600 px-8 py-3 font-semibold text-white shadow-sm hover:bg-blue-700">
                Save Site Photos
            </button>

            <a href="{{ route('site-photos.index') }}"
               class="rounded-xl bg-gray-500 px-8 py-3 font-semibold text-white hover:bg-gray-600">
                Cancel
            </a>
        </div>

        {{-- Mobile sticky action --}}
        <div class="fixed inset-x-0 bottom-0 z-40 border-t border-gray-200 bg-white/95 p-3 shadow-[0_-4px_16px_rgba(0,0,0,0.08)] backdrop-blur sm:hidden">
            <button type="submit"
                    id="mobileSubmitButton"
                    class="w-full rounded-xl bg-blue-600 px-6 py-3.5 text-base font-bold text-white shadow-sm hover:bg-blue-700">
                Save Site Photos
            </button>
        </div>
    </form>
</div>

<template id="photoCardTemplate">
    <article class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm photo-card">
        <div class="relative bg-gray-100">
            <img src="" alt="Selected site photo" class="h-52 w-full object-cover photo-preview">
            <button type="button"
                    class="remove-photo absolute right-2 top-2 inline-flex h-9 w-9 items-center justify-center rounded-full bg-red-600 text-lg font-bold text-white shadow hover:bg-red-700"
                    aria-label="Remove photo">×</button>
            <div class="absolute bottom-2 left-2 rounded-full bg-black/70 px-2.5 py-1 text-xs font-semibold text-white photo-number"></div>
        </div>

        <div class="space-y-3 p-4">
            <div>
                <label class="{{ $labelClass }}">Photo Type</label>
                <select class="{{ $inputClass }} photo-type">
                    @foreach($photoTypes as $type)
                        <option value="{{ $type }}" {{ $type === 'Progress Photo' ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="{{ $labelClass }}">Caption</label>
                <input type="text"
                       maxlength="500"
                       class="{{ $inputClass }} photo-caption"
                       placeholder="Optional photo description">
            </div>

            <div>
                <label class="{{ $labelClass }}">Captured At</label>
                <input type="datetime-local" class="{{ $inputClass }} photo-captured-at">
            </div>

            <div class="truncate text-xs text-gray-500 photo-file-info"></div>
        </div>
    </article>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const locationData = @json($locationData);
    const workData = @json($workData);

    const oldValues = {
        project: @json(old('project_id')),
        block: @json(old('project_block_id')),
        floor: @json(old('project_floor_id')),
        unit: @json(old('project_unit_id')),
        room: @json(old('project_room_id')),
        subspace: @json(old('project_subspace_id')),
        workPackage: @json(old('work_package_id')),
        workSection: @json(old('work_section_id')),
        workActivity: @json(old('work_activity_id')),
    };

    const projectSelect = document.getElementById('project_id');
    const blockSelect = document.getElementById('project_block_id');
    const floorSelect = document.getElementById('project_floor_id');
    const unitSelect = document.getElementById('project_unit_id');
    const roomSelect = document.getElementById('project_room_id');
    const subspaceSelect = document.getElementById('project_subspace_id');

    function resetSelect(select, placeholder) {
        if (!select) return;
        select.innerHTML = '';
        const option = document.createElement('option');
        option.value = '';
        option.textContent = placeholder;
        select.appendChild(option);
    }

    function fillSelect(select, rows, selectedValue, placeholder) {
        resetSelect(select, placeholder);
        rows.forEach(row => {
            const option = document.createElement('option');
            option.value = String(row.id);
            option.textContent = row.name || `#${row.id}`;
            option.selected = String(row.id) === String(selectedValue || '');
            select.appendChild(option);
        });
    }

    function selectedId(select) {
        return select?.value ? String(select.value) : '';
    }

    function rowsForProject(rows, projectId) {
        return rows.filter(row => String(row.project_id || '') === String(projectId || ''));
    }

    function refreshBlocks(selected = '') {
        const projectId = selectedId(projectSelect);
        const rows = projectId ? rowsForProject(locationData.blocks, projectId) : [];
        fillSelect(blockSelect, rows, selected, 'Select Block');
        refreshFloors();
    }

    function refreshFloors(selected = '') {
        const projectId = selectedId(projectSelect);
        const blockId = selectedId(blockSelect);

        let rows = projectId ? rowsForProject(locationData.floors, projectId) : [];
        if (blockId) {
            rows = rows.filter(row => String(row.project_block_id || '') === blockId);
        } else {
            rows = [];
        }

        fillSelect(floorSelect, rows, selected, 'Select Floor');
        refreshUnits();
    }

    function refreshUnits(selected = '') {
        const projectId = selectedId(projectSelect);
        const blockId = selectedId(blockSelect);
        const floorId = selectedId(floorSelect);

        let rows = floorId ? rowsForProject(locationData.units, projectId) : [];
        rows = rows.filter(row => String(row.project_floor_id || '') === floorId);
        if (blockId) {
            rows = rows.filter(row => String(row.project_block_id || '') === blockId);
        }

        fillSelect(unitSelect, rows, selected, 'Select Unit');
        refreshRooms();
    }

    function refreshRooms(selected = '') {
        const projectId = selectedId(projectSelect);
        const blockId = selectedId(blockSelect);
        const floorId = selectedId(floorSelect);
        const unitId = selectedId(unitSelect);

        let rows = floorId ? rowsForProject(locationData.rooms, projectId) : [];
        rows = rows.filter(row => String(row.project_floor_id || '') === floorId);

        if (blockId) {
            rows = rows.filter(row => !row.project_block_id || String(row.project_block_id) === blockId);
        }

        if (unitId) {
            rows = rows.filter(row => String(row.project_unit_id || '') === unitId);
        } else {
            // Flexible hierarchy: when no unit is selected, expose only rooms directly under the floor.
            rows = rows.filter(row => !row.project_unit_id);
        }

        fillSelect(roomSelect, rows, selected, 'Select Room');
        refreshSubspaces();
    }

    function refreshSubspaces(selected = '') {
        const projectId = selectedId(projectSelect);
        const blockId = selectedId(blockSelect);
        const floorId = selectedId(floorSelect);
        const unitId = selectedId(unitSelect);
        const roomId = selectedId(roomSelect);

        let rows = roomId ? rowsForProject(locationData.subspaces, projectId) : [];
        rows = rows.filter(row => String(row.project_room_id || '') === roomId);

        if (floorId) {
            rows = rows.filter(row => !row.project_floor_id || String(row.project_floor_id) === floorId);
        }
        if (blockId) {
            rows = rows.filter(row => !row.project_block_id || String(row.project_block_id) === blockId);
        }
        if (unitId) {
            rows = rows.filter(row => !row.project_unit_id || String(row.project_unit_id) === unitId);
        } else {
            rows = rows.filter(row => !row.project_unit_id);
        }

        fillSelect(subspaceSelect, rows, selected, 'Select Sub-space');
    }

    projectSelect?.addEventListener('change', () => refreshBlocks());
    blockSelect?.addEventListener('change', () => refreshFloors());
    floorSelect?.addEventListener('change', () => refreshUnits());
    unitSelect?.addEventListener('change', () => refreshRooms());
    roomSelect?.addEventListener('change', () => refreshSubspaces());

    // Restore old location values after validation errors.
    if (projectSelect?.value) {
        refreshBlocks(oldValues.block);
        if (oldValues.block) {
            refreshFloors(oldValues.floor);
        }
        if (oldValues.floor) {
            refreshUnits(oldValues.unit);
            refreshRooms(oldValues.room);
        }
        if (oldValues.room) {
            refreshSubspaces(oldValues.subspace);
        }
    } else {
        refreshBlocks();
    }

    /* Work Package -> Section -> Activity */
    const packageSelect = document.getElementById('work_package_id');
    const sectionSelect = document.getElementById('work_section_id');
    const activitySelect = document.getElementById('work_activity_id');

    function refreshSections(selected = '') {
        const packageId = selectedId(packageSelect);
        const rows = packageId
            ? workData.sections.filter(row => String(row.work_package_id) === packageId)
            : [];

        fillSelect(sectionSelect, rows, selected, 'Select Work Section');
        refreshActivities();
    }

    function refreshActivities(selected = '') {
        const packageId = selectedId(packageSelect);
        const sectionId = selectedId(sectionSelect);
        const rows = packageId && sectionId
            ? workData.activities.filter(row =>
                String(row.work_package_id) === packageId
                && String(row.work_section_id) === sectionId
            )
            : [];

        fillSelect(activitySelect, rows, selected, 'Select Work Activity');
    }

    packageSelect?.addEventListener('change', () => refreshSections());
    sectionSelect?.addEventListener('change', () => refreshActivities());

    if (packageSelect?.value) {
        refreshSections(oldValues.workSection);
        if (oldValues.workSection) {
            refreshActivities(oldValues.workActivity);
        }
    } else {
        refreshSections();
    }

    /* Camera / Gallery accumulator */
    const MAX_PHOTOS = 20;
    const MAX_BYTES = 10 * 1024 * 1024;
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    const cameraInput = document.getElementById('cameraInput');
    const galleryInput = document.getElementById('galleryInput');
    const takePhotoButton = document.getElementById('takePhotoButton');
    const choosePhotosButton = document.getElementById('choosePhotosButton');
    const photoCards = document.getElementById('photoCards');
    const photoFileInputs = document.getElementById('photoFileInputs');
    const photoTemplate = document.getElementById('photoCardTemplate');
    const photoCountBadge = document.getElementById('photoCountBadge');
    const photoEmptyState = document.getElementById('photoEmptyState');
    const form = document.getElementById('sitePhotoForm');

    let selectedPhotos = [];
    let nextPhotoId = 1;

    function formatBytes(bytes) {
        if (!bytes) return '0 MB';
        return (bytes / 1024 / 1024).toFixed(2) + ' MB';
    }

    function localDateTimeValue(date = new Date()) {
        const pad = value => String(value).padStart(2, '0');
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
    }

    function validateFile(file) {
        if (!file) return false;

        if (file.size > MAX_BYTES) {
            alert(`${file.name} is larger than 10 MB and was not added.`);
            return false;
        }

        if (file.type && !allowedTypes.includes(file.type)) {
            alert(`${file.name} is not a supported JPG, PNG or WEBP image.`);
            return false;
        }

        return true;
    }

    function addFiles(fileList) {
        const files = Array.from(fileList || []);
        if (!files.length) return;

        for (const file of files) {
            if (selectedPhotos.length >= MAX_PHOTOS) {
                alert(`A maximum of ${MAX_PHOTOS} photos can be added to one Site Photo entry.`);
                break;
            }

            if (!validateFile(file)) continue;

            selectedPhotos.push({
                id: nextPhotoId++,
                file,
                photoType: 'Progress Photo',
                caption: '',
                capturedAt: localDateTimeValue(),
                objectUrl: URL.createObjectURL(file),
            });
        }

        renderPhotos();
    }

    function renderPhotos() {
        photoCards.innerHTML = '';
        photoFileInputs.innerHTML = '';

        selectedPhotos.forEach((photo, index) => {
            const fragment = photoTemplate.content.cloneNode(true);
            const card = fragment.querySelector('.photo-card');
            const preview = fragment.querySelector('.photo-preview');
            const number = fragment.querySelector('.photo-number');
            const type = fragment.querySelector('.photo-type');
            const caption = fragment.querySelector('.photo-caption');
            const capturedAt = fragment.querySelector('.photo-captured-at');
            const info = fragment.querySelector('.photo-file-info');
            const remove = fragment.querySelector('.remove-photo');

            card.dataset.photoId = String(photo.id);
            preview.src = photo.objectUrl;
            number.textContent = `Photo ${index + 1}`;
            type.value = photo.photoType;
            caption.value = photo.caption;
            capturedAt.value = photo.capturedAt;
            info.textContent = `${photo.file.name} · ${formatBytes(photo.file.size)}`;

            type.addEventListener('change', event => {
                photo.photoType = event.target.value;
            });

            caption.addEventListener('input', event => {
                photo.caption = event.target.value;
            });

            capturedAt.addEventListener('change', event => {
                photo.capturedAt = event.target.value;
            });

            remove.addEventListener('click', () => {
                URL.revokeObjectURL(photo.objectUrl);
                selectedPhotos = selectedPhotos.filter(item => item.id !== photo.id);
                renderPhotos();
            });

            photoCards.appendChild(fragment);

            // A dedicated hidden file input is required so Laravel receives photos[index][file].
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.name = `photos[${index}][file]`;

            const transfer = new DataTransfer();
            transfer.items.add(photo.file);
            fileInput.files = transfer.files;

            const typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = `photos[${index}][photo_type]`;
            typeInput.value = photo.photoType;
            typeInput.dataset.photoId = String(photo.id);
            typeInput.dataset.kind = 'type';

            const captionInput = document.createElement('input');
            captionInput.type = 'hidden';
            captionInput.name = `photos[${index}][caption]`;
            captionInput.value = photo.caption;
            captionInput.dataset.photoId = String(photo.id);
            captionInput.dataset.kind = 'caption';

            const capturedInput = document.createElement('input');
            capturedInput.type = 'hidden';
            capturedInput.name = `photos[${index}][captured_at]`;
            capturedInput.value = photo.capturedAt;
            capturedInput.dataset.photoId = String(photo.id);
            capturedInput.dataset.kind = 'captured';

            photoFileInputs.append(fileInput, typeInput, captionInput, capturedInput);
        });

        const count = selectedPhotos.length;
        photoCountBadge.textContent = `${count} Photo${count === 1 ? '' : 's'}`;
        photoEmptyState.classList.toggle('hidden', count > 0);
    }

    function syncMetadataToHiddenInputs() {
        selectedPhotos.forEach(photo => {
            const selectorBase = `[data-photo-id="${photo.id}"]`;
            const typeInput = photoFileInputs.querySelector(`${selectorBase}[data-kind="type"]`);
            const captionInput = photoFileInputs.querySelector(`${selectorBase}[data-kind="caption"]`);
            const capturedInput = photoFileInputs.querySelector(`${selectorBase}[data-kind="captured"]`);

            if (typeInput) typeInput.value = photo.photoType;
            if (captionInput) captionInput.value = photo.caption;
            if (capturedInput) capturedInput.value = photo.capturedAt;
        });
    }

    takePhotoButton?.addEventListener('click', () => cameraInput?.click());
    choosePhotosButton?.addEventListener('click', () => galleryInput?.click());

    cameraInput?.addEventListener('change', function () {
        addFiles(this.files);
        this.value = '';
    });

    galleryInput?.addEventListener('change', function () {
        addFiles(this.files);
        this.value = '';
    });

    form?.addEventListener('submit', function (event) {
        if (selectedPhotos.length < 1) {
            event.preventDefault();
            alert('Please add at least one Site Photo before saving.');
            document.getElementById('sitePhotoUploader')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }

        syncMetadataToHiddenInputs();

        const buttons = [
            document.getElementById('desktopSubmitButton'),
            document.getElementById('mobileSubmitButton'),
        ];

        buttons.forEach(button => {
            if (!button) return;
            button.disabled = true;
            button.classList.add('opacity-60', 'cursor-not-allowed');
            button.textContent = 'Saving Site Photos...';
        });
    });

    // Browser security does not allow restoring old uploaded files after validation failure.
    // Restore only the metadata rows as a reminder; the Engineer must reselect the actual files.
    const oldPhotoRows = @json($oldPhotoRows);
    if (oldPhotoRows.length > 0 && @json($errors->any())) {
        const warning = document.createElement('div');
        warning.className = 'mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800';
        warning.textContent = 'The form was returned with validation errors. For security, your browser cannot restore previously selected image files. Please select the photos again.';
        document.getElementById('sitePhotoUploader')?.appendChild(warning);
    }
});
</script>
@endsection
