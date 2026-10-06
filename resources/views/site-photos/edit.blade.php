@extends('layouts.app')

@section('content')
@php
    $entry = $sitePhotoEntry;

    $selectedProject = old('project_id', $entry->project_id);
    $selectedBlock = old('project_block_id', $entry->project_block_id);
    $selectedFloor = old('project_floor_id', $entry->project_floor_id);
    $selectedUnit = old('project_unit_id', $entry->project_unit_id);
    $selectedRoom = old('project_room_id', $entry->project_room_id);
    $selectedSubspace = old('project_subspace_id', $entry->project_subspace_id);

    $selectedPackage = old('work_package_id', $entry->work_package_id);
    $selectedSection = old('work_section_id', $entry->work_section_id);
    $selectedActivity = old('work_activity_id', $entry->work_activity_id);

    $selectedCategory = old('category', $entry->category);
    $selectedContractor = old('contractor_id', $entry->contractor_id);
    $selectedStatus = old('status', $entry->status);

    $existingPhotos = $entry->photos ?? collect();
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Edit Site Photos #{{ $entry->id }}</h1>
                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                    {{ $existingPhotos->count() }} {{ Str::plural('Photo', $existingPhotos->count()) }}
                </span>
            </div>
            <p class="mt-2 text-sm text-slate-600">
                Update the site evidence details, remove existing photographs or add additional photographs.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('site-photos.show', $entry) }}"
               class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                View Entry
            </a>
            <a href="{{ route('site-photos.index') }}"
               class="inline-flex items-center justify-center rounded-lg bg-slate-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">
                Back to Site Photos
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4">
            <div class="font-semibold text-red-800">Please correct the following:</div>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="sitePhotoEditForm"
          method="POST"
          action="{{ route('site-photos.update', $entry) }}"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Project / date --}}
        <section class="mb-5 rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Project & Photo Date</h2>
                <p class="mt-1 text-sm text-slate-500">Update where and when the photographs were recorded.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">
                <div>
                    <label for="project_id" class="block text-sm font-medium text-slate-700">
                        Project <span class="text-red-500">*</span>
                    </label>
                    <select id="project_id" name="project_id" required
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" @selected((string)$selectedProject === (string)$project->id)>
                                {{ $project->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="photo_date" class="block text-sm font-medium text-slate-700">
                        Photo Date <span class="text-red-500">*</span>
                    </label>
                    <input id="photo_date"
                           name="photo_date"
                           type="date"
                           required
                           value="{{ old('photo_date', $entry->photo_date?->format('Y-m-d')) }}"
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Reported By</label>
                    <input type="text"
                           value="{{ $entry->reporter?->name ?? auth()->user()?->name ?? '—' }}"
                           readonly
                           class="mt-1 block w-full rounded-lg border-slate-300 bg-slate-50 text-sm text-slate-600">
                </div>
            </div>
        </section>

        {{-- Location --}}
        <section class="mb-5 rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Photo Location</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Optional. Select the most specific location available. Rooms may belong directly to a floor or to a unit.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 xl:grid-cols-5">
                <div>
                    <label for="project_block_id" class="block text-sm font-medium text-slate-700">Block</label>
                    <select id="project_block_id" name="project_block_id"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Block</option>
                        @foreach($projectBlocks as $block)
                            <option value="{{ $block->id }}"
                                    data-project="{{ $block->project_id }}"
                                    @selected((string)$selectedBlock === (string)$block->id)>
                                {{ $block->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="project_floor_id" class="block text-sm font-medium text-slate-700">Floor</label>
                    <select id="project_floor_id" name="project_floor_id"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Floor</option>
                        @foreach($projectFloors as $floor)
                            <option value="{{ $floor->id }}"
                                    data-project="{{ $floor->project_id }}"
                                    data-block="{{ $floor->project_block_id }}"
                                    @selected((string)$selectedFloor === (string)$floor->id)>
                                {{ $floor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="project_unit_id" class="block text-sm font-medium text-slate-700">Unit / Flat</label>
                    <select id="project_unit_id" name="project_unit_id"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Unit</option>
                        @foreach($projectUnits as $unit)
                            <option value="{{ $unit->id }}"
                                    data-project="{{ $unit->project_id }}"
                                    data-block="{{ $unit->project_block_id }}"
                                    data-floor="{{ $unit->project_floor_id }}"
                                    @selected((string)$selectedUnit === (string)$unit->id)>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Leave blank for rooms directly under the floor.</p>
                </div>

                <div>
                    <label for="project_room_id" class="block text-sm font-medium text-slate-700">Room / Space</label>
                    <select id="project_room_id" name="project_room_id"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Room</option>
                        @foreach($projectRooms as $room)
                            <option value="{{ $room->id }}"
                                    data-project="{{ $room->project_id }}"
                                    data-block="{{ $room->project_block_id }}"
                                    data-floor="{{ $room->project_floor_id }}"
                                    data-unit="{{ $room->project_unit_id }}"
                                    @selected((string)$selectedRoom === (string)$room->id)>
                                {{ $room->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="project_subspace_id" class="block text-sm font-medium text-slate-700">Sub-space / Element</label>
                    <select id="project_subspace_id" name="project_subspace_id"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Sub-space</option>
                        @foreach($projectSubspaces as $subspace)
                            <option value="{{ $subspace->id }}"
                                    data-project="{{ $subspace->project_id }}"
                                    data-block="{{ $subspace->project_block_id }}"
                                    data-floor="{{ $subspace->project_floor_id }}"
                                    data-unit="{{ $subspace->project_unit_id }}"
                                    data-room="{{ $subspace->project_room_id }}"
                                    @selected((string)$selectedSubspace === (string)$subspace->id)>
                                {{ $subspace->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        {{-- Work classification --}}
        <section class="mb-5 rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Work Classification</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Optional. Classify the photographs against the canonical Work Execution Catalogue.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">
                <div>
                    <label for="work_package_id" class="block text-sm font-medium text-slate-700">Work Package</label>
                    <select id="work_package_id" name="work_package_id"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Work Package</option>
                        @foreach($workPackages as $package)
                            <option value="{{ $package->id }}" @selected((string)$selectedPackage === (string)$package->id)>
                                {{ $package->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="work_section_id" class="block text-sm font-medium text-slate-700">Work Section</label>
                    <select id="work_section_id" name="work_section_id"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Work Section</option>
                        @foreach($workSections as $section)
                            <option value="{{ $section->id }}"
                                    data-package="{{ $section->work_package_id }}"
                                    @selected((string)$selectedSection === (string)$section->id)>
                                {{ $section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="work_activity_id" class="block text-sm font-medium text-slate-700">Work Activity</label>
                    <select id="work_activity_id" name="work_activity_id"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Work Activity</option>
                        @foreach($workActivities as $activity)
                            <option value="{{ $activity->id }}"
                                    data-package="{{ $activity->work_package_id }}"
                                    data-section="{{ $activity->work_section_id }}"
                                    @selected((string)$selectedActivity === (string)$activity->id)>
                                {{ $activity->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        {{-- Entry details --}}
        <section class="mb-5 rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Photo Details</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-12">
                <div class="md:col-span-3">
                    <label for="category" class="block text-sm font-medium text-slate-700">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select id="category" name="category" required
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($categories as $category)
                            <option value="{{ $category }}" @selected($selectedCategory === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label for="contractor_id" class="block text-sm font-medium text-slate-700">Contractor</label>
                    <select id="contractor_id" name="contractor_id"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">No Contractor / Not Applicable</option>
                        @foreach($contractors as $contractor)
                            <option value="{{ $contractor->id }}" @selected((string)$selectedContractor === (string)$contractor->id)>
                                {{ $contractor->contractor_name ?? $contractor->name ?? ('Contractor #' . $contractor->id) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-6">
                    <label for="title" class="block text-sm font-medium text-slate-700">Title</label>
                    <input id="title"
                           name="title"
                           type="text"
                           maxlength="255"
                           value="{{ old('title', $entry->title) }}"
                           placeholder="Optional short description"
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="md:col-span-12">
                    <label for="remarks" class="block text-sm font-medium text-slate-700">Remarks</label>
                    <textarea id="remarks"
                              name="remarks"
                              rows="3"
                              maxlength="5000"
                              placeholder="Optional observations or notes"
                              class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('remarks', $entry->remarks) }}</textarea>
                </div>
            </div>
        </section>

        {{-- Existing photos --}}
        <section class="mb-5 rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-semibold text-slate-900">Existing Photographs</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Mark photographs for removal if required. At least one photograph must remain after the update.
                    </p>
                </div>
                <span id="existingPhotoCount"
                      class="self-start rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700">
                    {{ $existingPhotos->count() }} {{ Str::plural('Photo', $existingPhotos->count()) }}
                </span>
            </div>

            <div class="p-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($existingPhotos as $photo)
                        <div class="existing-photo-card overflow-hidden rounded-xl border border-slate-200"
                             data-photo-id="{{ $photo->id }}">
                            <a href="{{ asset('storage/' . $photo->file_path) }}"
                               target="_blank"
                               rel="noopener"
                               class="block bg-slate-100">
                                <img src="{{ asset('storage/' . $photo->file_path) }}"
                                     alt="{{ $photo->display_caption }}"
                                     class="h-48 w-full object-cover">
                            </a>

                            <div class="p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-xs font-semibold text-blue-700">{{ $photo->photo_type }}</div>
                                        <div class="mt-1 text-sm font-semibold text-slate-900">
                                            {{ $photo->display_caption }}
                                        </div>
                                    </div>
                                    <span class="shrink-0 text-xs text-slate-400">#{{ $photo->id }}</span>
                                </div>

                                <div class="mt-3 text-xs text-slate-500">
                                    @if($photo->captured_at)
                                        Captured {{ $photo->captured_at->format('d M Y, h:i A') }}
                                    @else
                                        Capture time not recorded
                                    @endif
                                </div>

                                <label class="mt-4 flex cursor-pointer items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700">
                                    <input type="checkbox"
                                           name="remove_photo_ids[]"
                                           value="{{ $photo->id }}"
                                           class="remove-existing-photo rounded border-red-300 text-red-600 focus:ring-red-500"
                                           @checked(in_array((string)$photo->id, array_map('strval', old('remove_photo_ids', [])), true))>
                                    Remove this photograph
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- New photos --}}
        <section class="mb-5 rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="font-semibold text-slate-900">Add More Photographs</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Optional. Take new photographs or choose one or more images from the gallery.
                    </p>
                </div>
                <span id="newPhotoCount"
                      class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                    0 New
                </span>
            </div>

            <div class="p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <button type="button"
                            id="takePhotoButton"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800">
                        <span aria-hidden="true">📷</span>
                        Take Photo
                    </button>

                    <button type="button"
                            id="galleryButton"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-800 hover:bg-slate-50">
                        <span aria-hidden="true">🖼️</span>
                        Choose From Gallery
                    </button>
                </div>

                <input id="cameraInput" type="file" accept="image/*" capture="environment" class="hidden">
                <input id="galleryInput" type="file" accept="image/*" multiple class="hidden">

                <div class="mt-3 rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-xs text-blue-700">
                    New photographs are added to the existing entry. You can add up to the controller limit for this update.
                </div>

                <div id="newPhotoPreviewGrid" class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"></div>
            </div>
        </section>

        {{-- Status --}}
        <section class="mb-5 rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Submission</h2>
                <p class="mt-1 text-sm text-slate-500">Submitted is recommended for normal Engineer reporting.</p>
            </div>

            <div class="p-5">
                <div class="max-w-sm">
                    <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
                    <select id="status" name="status"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row">
            <a href="{{ route('site-photos.show', $entry) }}"
               class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                Cancel
            </a>

            <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                Update Site Photos
            </button>
        </div>
    </form>
</div>

<template id="newPhotoTemplate">
    <div class="new-photo-card overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="relative bg-slate-100">
            <img class="photo-preview h-48 w-full object-cover" alt="New photograph preview">
            <button type="button"
                    class="remove-new-photo absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-red-600 text-sm font-bold text-white shadow hover:bg-red-700"
                    aria-label="Remove new photograph">
                ×
            </button>
        </div>

        <div class="space-y-3 p-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">Photo Type</label>
                <select data-field="photo_type"
                        class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @foreach($photoTypes as $photoType)
                        <option value="{{ $photoType }}">{{ $photoType }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Caption</label>
                <input type="text"
                       data-field="caption"
                       maxlength="500"
                       placeholder="Optional photo description"
                       class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Captured At</label>
                <input type="datetime-local"
                       data-field="captured_at"
                       class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="photo-file-info truncate text-xs text-slate-500"></div>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('sitePhotoEditForm');

    const project = document.getElementById('project_id');
    const block = document.getElementById('project_block_id');
    const floor = document.getElementById('project_floor_id');
    const unit = document.getElementById('project_unit_id');
    const room = document.getElementById('project_room_id');
    const subspace = document.getElementById('project_subspace_id');

    const workPackage = document.getElementById('work_package_id');
    const workSection = document.getElementById('work_section_id');
    const workActivity = document.getElementById('work_activity_id');

    function optionMatches(option, requirements) {
        if (!option.value) return true;

        return Object.entries(requirements).every(([key, value]) => {
            if (!value) return true;
            return String(option.dataset[key] || '') === String(value);
        });
    }

    function filterSelect(select, requirements, preserveValue = true) {
        const currentValue = preserveValue ? select.value : '';

        Array.from(select.options).forEach(option => {
            if (!option.value) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const visible = optionMatches(option, requirements);
            option.hidden = !visible;
            option.disabled = !visible;
        });

        if (currentValue && Array.from(select.options).some(o => o.value === currentValue && !o.disabled)) {
            select.value = currentValue;
        } else {
            select.value = '';
        }
    }

    function refreshLocation(preserve = true) {
        const projectId = project.value;

        filterSelect(block, { project: projectId }, preserve);

        const blockId = block.value;
        filterSelect(floor, { project: projectId, block: blockId }, preserve);

        const floorId = floor.value;
        filterSelect(unit, { project: projectId, block: blockId, floor: floorId }, preserve);

        const unitId = unit.value;
        filterSelect(room, {
            project: projectId,
            block: blockId,
            floor: floorId,
            unit: unitId
        }, preserve);

        const roomId = room.value;
        filterSelect(subspace, {
            project: projectId,
            block: blockId,
            floor: floorId,
            unit: unitId,
            room: roomId
        }, preserve);
    }

    project.addEventListener('change', function () {
        block.value = '';
        floor.value = '';
        unit.value = '';
        room.value = '';
        subspace.value = '';
        refreshLocation(false);
    });

    block.addEventListener('change', function () {
        floor.value = '';
        unit.value = '';
        room.value = '';
        subspace.value = '';
        refreshLocation(false);
    });

    floor.addEventListener('change', function () {
        unit.value = '';
        room.value = '';
        subspace.value = '';
        refreshLocation(false);
    });

    unit.addEventListener('change', function () {
        room.value = '';
        subspace.value = '';
        refreshLocation(false);
    });

    room.addEventListener('change', function () {
        subspace.value = '';
        refreshLocation(false);
    });

    function refreshWork(preserve = true) {
        const packageId = workPackage.value;
        filterSelect(workSection, { package: packageId }, preserve);

        const sectionId = workSection.value;
        filterSelect(workActivity, { package: packageId, section: sectionId }, preserve);
    }

    workPackage.addEventListener('change', function () {
        workSection.value = '';
        workActivity.value = '';
        refreshWork(false);
    });

    workSection.addEventListener('change', function () {
        workActivity.value = '';
        refreshWork(false);
    });

    refreshLocation(true);
    refreshWork(true);

    const cameraInput = document.getElementById('cameraInput');
    const galleryInput = document.getElementById('galleryInput');
    const takePhotoButton = document.getElementById('takePhotoButton');
    const galleryButton = document.getElementById('galleryButton');
    const previewGrid = document.getElementById('newPhotoPreviewGrid');
    const template = document.getElementById('newPhotoTemplate');
    const newPhotoCount = document.getElementById('newPhotoCount');

    let selectedFiles = [];
    let nextPhotoKey = 0;

    takePhotoButton.addEventListener('click', () => cameraInput.click());
    galleryButton.addEventListener('click', () => galleryInput.click());

    cameraInput.addEventListener('change', function () {
        addFiles(Array.from(this.files || []));
        this.value = '';
    });

    galleryInput.addEventListener('change', function () {
        addFiles(Array.from(this.files || []));
        this.value = '';
    });

    function addFiles(files) {
        files.filter(file => file.type.startsWith('image/')).forEach(file => {
            const item = {
                key: nextPhotoKey++,
                file: file
            };

            selectedFiles.push(item);
            renderNewPhoto(item);
        });

        updateNewPhotoCount();
    }

    function renderNewPhoto(item) {
        const fragment = template.content.cloneNode(true);
        const card = fragment.querySelector('.new-photo-card');
        const image = fragment.querySelector('.photo-preview');
        const removeButton = fragment.querySelector('.remove-new-photo');
        const typeSelect = fragment.querySelector('[data-field="photo_type"]');
        const captionInput = fragment.querySelector('[data-field="caption"]');
        const capturedInput = fragment.querySelector('[data-field="captured_at"]');
        const info = fragment.querySelector('.photo-file-info');

        card.dataset.key = item.key;
        image.src = URL.createObjectURL(item.file);

        typeSelect.name = `photos[${item.key}][photo_type]`;
        captionInput.name = `photos[${item.key}][caption]`;
        capturedInput.name = `photos[${item.key}][captured_at]`;

        const now = new Date();
        const local = new Date(now.getTime() - now.getTimezoneOffset() * 60000);
        capturedInput.value = local.toISOString().slice(0, 16);

        const mb = (item.file.size / 1024 / 1024).toFixed(2);
        info.textContent = `${item.file.name} · ${mb} MB`;

        removeButton.addEventListener('click', function () {
            URL.revokeObjectURL(image.src);
            selectedFiles = selectedFiles.filter(fileItem => fileItem.key !== item.key);
            card.remove();
            updateNewPhotoCount();
        });

        previewGrid.appendChild(fragment);
    }

    function updateNewPhotoCount() {
        newPhotoCount.textContent = `${selectedFiles.length} New`;
    }

    const existingCheckboxes = Array.from(document.querySelectorAll('.remove-existing-photo'));

    form.addEventListener('submit', function (event) {
        const removedCount = existingCheckboxes.filter(checkbox => checkbox.checked).length;
        const remainingExisting = existingCheckboxes.length - removedCount;

        if ((remainingExisting + selectedFiles.length) < 1) {
            event.preventDefault();
            alert('At least one photograph must remain in the Site Photo entry.');
            return;
        }

        selectedFiles.forEach(item => {
            const transfer = new DataTransfer();
            transfer.items.add(item.file);

            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.name = `photos[${item.key}][file]`;
            fileInput.files = transfer.files;
            fileInput.hidden = true;

            form.appendChild(fileInput);
        });
    });
});
</script>
@endsection
