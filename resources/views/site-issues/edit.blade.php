@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
    $labelClass = 'mb-1 block text-sm font-semibold text-gray-700';

    $selectedActivityId = old('activity_id', $siteIssue->activity_id);

    $existingPhotoIds = $siteIssue->photos
        ->pluck('id')
        ->map(fn ($id) => (string) $id)
        ->values()
        ->all();

    $oldNewPhotos = collect(old('photos', []))->values();
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Edit Site Issue
            </h1>

            <p class="mt-1 text-gray-500">
                Update responsibility, status, escalation, resolution and photographic evidence.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('site-issues.show', $siteIssue) }}"
               class="rounded-lg bg-slate-700 px-5 py-2.5 font-semibold text-white hover:bg-slate-800">
                View
            </a>

            <a href="{{ route('site-issues.index') }}"
               class="rounded-lg bg-gray-600 px-5 py-2.5 font-semibold text-white hover:bg-gray-700">
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
          action="{{ route('site-issues.update', $siteIssue) }}"
          enctype="multipart/form-data"
          id="siteIssueEditForm">

        @csrf
        @method('PUT')

        {{-- Project & Issue Date --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <x-rds.section-title
                title="Project & Issue Date"
                subtitle="Core issue record information."
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
                                {{ (string) old('project_id', $siteIssue->project_id) === (string) $project->id ? 'selected' : '' }}>
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
                           value="{{ old('issue_date', $siteIssue->issue_date?->format('Y-m-d')) }}"
                           class="{{ $inputClass }}"
                           required>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Reported By
                    </label>

                    <input type="text"
                           value="{{ $siteIssue->creator?->name ?? '-' }}"
                           class="{{ $inputClass }} bg-gray-100"
                           readonly>
                </div>

            </div>
        </div>

        {{-- Location --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <x-rds.section-title
                title="Issue Location"
                subtitle="Update the most specific project location."
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
                                {{ (string) old('project_block_id', $siteIssue->project_block_id) === (string) $block->id ? 'selected' : '' }}>
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
                                {{ (string) old('project_floor_id', $siteIssue->project_floor_id) === (string) $floor->id ? 'selected' : '' }}>
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
                                {{ (string) old('project_unit_id', $siteIssue->project_unit_id) === (string) $unit->id ? 'selected' : '' }}>
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
                                {{ (string) old('project_room_id', $siteIssue->project_room_id) === (string) $room->id ? 'selected' : '' }}>
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
                                {{ (string) old('project_subspace_id', $siteIssue->project_subspace_id) === (string) $subspace->id ? 'selected' : '' }}>
                                {{ $subspace->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        {{-- Related Activity --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <x-rds.section-title
                title="Related Activity"
                subtitle="Optional construction activity associated with this issue."
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
                                {{ (string) $selectedActivityId === (string) $activity->id ? 'selected' : '' }}>
                                {{ $activity->activity_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        {{-- Issue Details --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <x-rds.section-title
                title="Issue Details"
                subtitle="Update issue classification, responsibility and closure information."
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
                        @foreach($issueTypes as $type)
                            <option value="{{ $type }}"
                                {{ old('issue_type', $siteIssue->issue_type) === $type ? 'selected' : '' }}>
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
                                {{ old('priority', $siteIssue->priority) === $priority ? 'selected' : '' }}>
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
                                {{ old('status', $siteIssue->status) === $status ? 'selected' : '' }}>
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
                           value="{{ old('target_closure_date', $siteIssue->target_closure_date?->format('Y-m-d')) }}"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Actual Closure Date
                    </label>

                    <input type="date"
                           name="actual_closure_date"
                           value="{{ old('actual_closure_date', $siteIssue->actual_closure_date?->format('Y-m-d')) }}"
                           class="{{ $inputClass }}">
                </div>

                <div class="md:col-span-2 xl:col-span-3">
                    <label class="{{ $labelClass }}">
                        Responsible Person / Team
                    </label>

                    <input type="text"
                           name="responsible_person"
                           value="{{ old('responsible_person', $siteIssue->responsible_person) }}"
                           maxlength="255"
                           class="{{ $inputClass }}"
                           placeholder="Optional">
                </div>

                <div class="md:col-span-2 xl:col-span-4">
                    <label class="{{ $labelClass }}">
                        Issue Title <span class="text-red-500">*</span>
                    </label>

                    <input type="text"
                           name="title"
                           value="{{ old('title', $siteIssue->title) }}"
                           maxlength="255"
                           class="{{ $inputClass }}"
                           required>
                </div>

                <div class="md:col-span-2 xl:col-span-4">
                    <label class="{{ $labelClass }}">
                        Description <span class="text-red-500">*</span>
                    </label>

                    <textarea name="description"
                              rows="4"
                              maxlength="10000"
                              class="{{ $inputClass }}"
                              required>{{ old('description', $siteIssue->description) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">
                        Root Cause
                    </label>

                    <textarea name="root_cause"
                              rows="3"
                              maxlength="2000"
                              class="{{ $inputClass }}">{{ old('root_cause', $siteIssue->root_cause) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">
                        Related Activity / Reference
                    </label>

                    <textarea name="related_activity"
                              rows="3"
                              maxlength="255"
                              class="{{ $inputClass }}">{{ old('related_activity', $siteIssue->related_activity) }}</textarea>
                </div>

            </div>
        </div>

        {{-- Escalation --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <x-rds.section-title
                title="Escalation"
                subtitle="Update escalation level where higher-level intervention is required."
                icon="📢"
            />

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-4">
                    <input type="checkbox"
                           name="escalated_to_pmo"
                           value="1"
                           class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                           {{ old('escalated_to_pmo', $siteIssue->escalated_to_pmo) ? 'checked' : '' }}>

                    <div>
                        <div class="font-semibold text-gray-800">
                            Escalate to PMO
                        </div>

                        <div class="mt-1 text-sm text-gray-500">
                            Planning, coordination, contractor, material or execution issue requiring PMO intervention.
                        </div>
                    </div>
                </label>

                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-4">
                    <input type="checkbox"
                           name="escalated_to_management"
                           value="1"
                           class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                           {{ old('escalated_to_management', $siteIssue->escalated_to_management) ? 'checked' : '' }}>

                    <div>
                        <div class="font-semibold text-gray-800">
                            Escalate to Management
                        </div>

                        <div class="mt-1 text-sm text-gray-500">
                            Critical issue requiring management intervention.
                        </div>
                    </div>
                </label>

            </div>
        </div>

        {{-- Resolution --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <x-rds.section-title
                title="Resolution"
                subtitle="Record corrective action and closure details."
                icon="✅"
            />

            <textarea name="resolution"
                      rows="4"
                      maxlength="10000"
                      class="{{ $inputClass }}"
                      placeholder="Resolution / corrective action">{{ old('resolution', $siteIssue->resolution) }}</textarea>
        </div>

        {{-- Existing Photos --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <x-rds.section-title
                title="Existing Photos"
                subtitle="Keep or remove photos already attached to this issue."
                icon="🖼️"
            >
                <x-slot:actions>
                    <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-800">
                        {{ $siteIssue->photos->count() }} Photo{{ $siteIssue->photos->count() === 1 ? '' : 's' }}
                    </span>
                </x-slot:actions>
            </x-rds.section-title>

            @if($siteIssue->photos->isNotEmpty())

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                    @foreach($siteIssue->photos as $photo)

                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

                            <a href="{{ $photo->file_url }}"
                               target="_blank"
                               rel="noopener">
                                <img src="{{ $photo->file_url }}"
                                     alt="{{ $photo->display_caption }}"
                                     class="h-52 w-full bg-gray-100 object-cover">
                            </a>

                            <div class="p-4">

                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                        {{ $photo->photo_type }}
                                    </span>

                                    @if($photo->file_size)
                                        <span class="text-xs text-gray-500">
                                            {{ $photo->formatted_file_size }}
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-3 text-sm font-medium text-gray-800">
                                    {{ $photo->display_caption }}
                                </div>

                                <label class="mt-4 flex cursor-pointer items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                    <input type="checkbox"
                                           name="remove_photo_ids[]"
                                           value="{{ $photo->id }}"
                                           class="h-4 w-4 rounded border-red-300 text-red-600 focus:ring-red-500"
                                           {{ in_array((string) $photo->id, collect(old('remove_photo_ids', []))->map(fn ($id) => (string) $id)->all(), true) ? 'checked' : '' }}>

                                    <span class="font-semibold">
                                        Remove this photo
                                    </span>
                                </label>

                            </div>

                        </div>

                    @endforeach

                </div>

            @else

                <div class="rounded-lg border border-dashed border-gray-300 px-5 py-10 text-center text-sm text-gray-500">
                    No existing photos.
                </div>

            @endif
        </div>

        {{-- Add Photos --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <x-rds.section-title
                title="Add More Photos"
                subtitle="Upload additional photographic evidence."
                icon="📷"
            >
                <x-slot:actions>
                    <button type="button"
                            id="addPhotoButton"
                            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                        + Add Photo
                    </button>
                </x-slot:actions>
            </x-rds.section-title>

            <div id="photoRows"
                 class="space-y-4">

                @foreach($oldNewPhotos as $photoIndex => $photo)

                    <div class="rounded-lg border border-gray-200 p-4 photo-row">

                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-start">

                            <div class="lg:col-span-3">
                                <label class="{{ $labelClass }}">Photo Type</label>

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
                                <label class="{{ $labelClass }}">Caption</label>

                                <input type="text"
                                       name="photos[{{ $photoIndex }}][caption]"
                                       value="{{ $photo['caption'] ?? '' }}"
                                       maxlength="500"
                                       class="{{ $inputClass }}"
                                       placeholder="Optional description">
                            </div>

                            <div class="lg:col-span-4">
                                <label class="{{ $labelClass }}">Image</label>

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
                                    <p class="font-semibold text-gray-800">Preview</p>
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
                            <label class="{{ $labelClass }}">Photo Type</label>

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
                            <label class="{{ $labelClass }}">Caption</label>

                            <input type="text"
                                   name="photos[__PHOTO_INDEX__][caption]"
                                   maxlength="500"
                                   class="{{ $inputClass }}"
                                   placeholder="Optional description">
                        </div>

                        <div class="lg:col-span-4">
                            <label class="{{ $labelClass }}">Image</label>

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
                                <p class="font-semibold text-gray-800">Preview</p>
                                <p class="mt-1 photo-name"></p>
                                <p class="mt-1 text-xs text-gray-500 photo-size"></p>
                            </div>
                        </div>
                    </div>

                </div>

            </template>

            @if($oldNewPhotos->isEmpty())
                <div id="noNewPhotoMessage"
                     class="rounded-lg border border-dashed border-gray-300 px-5 py-8 text-center text-sm text-gray-500">
                    No new photos selected. Click “Add Photo” to upload additional evidence.
                </div>
            @endif
        </div>

        {{-- Remarks --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <x-rds.section-title
                title="Remarks"
                subtitle="Additional notes and observations."
                icon="💬"
            />

            <textarea name="remarks"
                      rows="3"
                      maxlength="5000"
                      class="{{ $inputClass }}">{{ old('remarks', $siteIssue->remarks) }}</textarea>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">

            <button type="submit"
                    class="rounded-lg bg-blue-600 px-7 py-3 font-semibold text-white hover:bg-blue-700">
                Update Site Issue
            </button>

            <a href="{{ route('site-issues.show', $siteIssue) }}"
               class="rounded-lg bg-gray-600 px-7 py-3 font-semibold text-white hover:bg-gray-700">
                Cancel
            </a>

        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Cascading Project Location
    |--------------------------------------------------------------------------
    */

    const project = document.getElementById('project_id');
    const block = document.getElementById('project_block_id');
    const floor = document.getElementById('project_floor_id');
    const unit = document.getElementById('project_unit_id');
    const room = document.getElementById('project_room_id');
    const subspace = document.getElementById('project_subspace_id');

    function filterOptions(select, predicate) {
        if (!select) return;

        Array.from(select.options).forEach(option => {
            const visible = predicate(option);

            option.hidden = !visible;
            option.disabled = !visible && option.value !== '';

            if (!visible && option.selected) {
                select.value = '';
            }
        });
    }

    function filterLocations() {
        const projectId = project?.value || '';
        const blockId = block?.value || '';
        const floorId = floor?.value || '';
        const unitId = unit?.value || '';
        const roomId = room?.value || '';

        filterOptions(block, option => {
            return option.value === ''
                || (
                    projectId
                    && String(option.dataset.project) === String(projectId)
                );
        });

        filterOptions(floor, option => {
            if (option.value === '') return true;

            return projectId
                && String(option.dataset.project) === String(projectId)
                && (
                    !blockId
                    || String(option.dataset.block) === String(blockId)
                );
        });

        filterOptions(unit, option => {
            if (option.value === '') return true;

            return projectId
                && String(option.dataset.project) === String(projectId)
                && (
                    !blockId
                    || String(option.dataset.block) === String(blockId)
                )
                && (
                    !floorId
                    || String(option.dataset.floor) === String(floorId)
                );
        });

        filterOptions(room, option => {
            return option.value === ''
                || (
                    unitId
                    && String(option.dataset.unit) === String(unitId)
                );
        });

        filterOptions(subspace, option => {
            return option.value === ''
                || (
                    roomId
                    && String(option.dataset.room) === String(roomId)
                );
        });
    }

    project?.addEventListener('change', function () {
        block.value = '';
        floor.value = '';
        unit.value = '';
        room.value = '';
        subspace.value = '';

        filterLocations();
    });

    block?.addEventListener('change', function () {
        floor.value = '';
        unit.value = '';
        room.value = '';
        subspace.value = '';

        filterLocations();
    });

    floor?.addEventListener('change', function () {
        unit.value = '';
        room.value = '';
        subspace.value = '';

        filterLocations();
    });

    unit?.addEventListener('change', function () {
        room.value = '';
        subspace.value = '';

        filterLocations();
    });

    room?.addEventListener('change', function () {
        subspace.value = '';

        filterLocations();
    });

    filterLocations();

    /*
    |--------------------------------------------------------------------------
    | Activity Division -> Activity
    |--------------------------------------------------------------------------
    */

    const division = document.getElementById('activity_division_id');
    const activity = document.getElementById('activity_id');

    const selectedActivityId = @json($selectedActivityId);

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
            activity.value = '';
            filterActivities();
        }
    );

    filterActivities();

    /*
    |--------------------------------------------------------------------------
    | Add New Photos
    |--------------------------------------------------------------------------
    */

    const photoRows = document.getElementById('photoRows');
    const photoTemplate = document.getElementById('photoRowTemplate');
    const addPhotoButton = document.getElementById('addPhotoButton');
    const noNewPhotoMessage = document.getElementById('noNewPhotoMessage');

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
            alert(
                'Each Site Issue photo must be 10 MB or smaller.'
            );

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
                    row.remove();

                    renumberPhotos();

                    if (
                        document.querySelectorAll('.photo-row').length === 0
                    ) {
                        noNewPhotoMessage?.classList.remove('hidden');
                    }
                }
            );
    }

    document.querySelectorAll('.photo-row')
        .forEach(bindPhotoRow);

    addPhotoButton?.addEventListener(
        'click',
        function () {
            const index =
                document.querySelectorAll('.photo-row').length;

            const html =
                photoTemplate.innerHTML.replaceAll(
                    '__PHOTO_INDEX__',
                    String(index)
                );

            photoRows.insertAdjacentHTML(
                'beforeend',
                html
            );

            noNewPhotoMessage?.classList.add('hidden');

            const rows =
                document.querySelectorAll('.photo-row');

            bindPhotoRow(
                rows[rows.length - 1]
            );

            renumberPhotos();
        }
    );
});
</script>

@endsection
