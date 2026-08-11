@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
    $labelClass = 'mb-1 block text-sm font-semibold text-gray-700';

    $summaryItems = [
        ['key' => 'activities', 'label' => 'Activities', 'value' => 1],
        ['key' => 'materials', 'label' => 'Materials', 'value' => 0],
        ['key' => 'photos', 'label' => 'Photos', 'value' => 0],
    ];

    $oldWorks = collect(old('works', [
        [
            'work_stage_id' => '',
            'activity_division_id' => '',
            'activity_id' => '',
            'activity_mapping_id' => '',
            'contractor_id' => '',
            'project_block_id' => '',
            'project_floor_id' => '',
            'project_unit_id' => '',
            'project_room_id' => '',
            'project_subspace_id' => '',
            'quantity_completed' => '',
            'unit' => '',
            'progress_percentage' => '',
            'execution_status' => 'In Progress',
            'remarks' => '',
            'material_consumed_ids' => [],
            'photos' => [],
        ],
    ]))->values();
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Daily Work Execution
            </h1>

            <p class="mt-1 text-gray-500">
                Record multiple construction activities for the selected Project and Date. These activities can be linked to the DPR later.
            </p>
        </div>

        <a href="{{ route('work-done.index') }}"
           class="inline-flex items-center justify-center rounded-lg bg-gray-600 px-5 py-2.5 font-semibold text-white hover:bg-gray-700">
            Back
        </a>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

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
          action="{{ route('work-done.store') }}"
          enctype="multipart/form-data"
          data-ref-work-done-form
          data-ref-materials-url="{{ route('work-done.available-materials') }}">

        @csrf

        <x-rds.summary-bar
            title="Today's Execution"
            subtitle="Live summary updates as Work Activities are added."
            :items="$summaryItems"
        />

        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <x-rds.section-title
                title="Project & Date"
                subtitle="The Engineer is captured automatically from the logged-in user."
                icon="🏗️"
            />

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

                <div class="xl:col-span-2">
                    <label class="{{ $labelClass }}">
                        Project <span class="text-red-500">*</span>
                    </label>

                    <select name="project_id"
                            class="{{ $inputClass }}"
                            data-ref-project-field
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
                        Work Date <span class="text-red-500">*</span>
                    </label>

                    <input type="date"
                           name="work_date"
                           value="{{ old('work_date', now()->format('Y-m-d')) }}"
                           class="{{ $inputClass }}"
                           data-ref-work-date-field
                           required>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Engineer
                    </label>

                    <input type="text"
                           value="{{ auth()->user()->name }}"
                           class="{{ $inputClass }} bg-gray-100"
                           readonly>
                </div>

                <div class="md:col-span-2 xl:col-span-4">
                    <label class="{{ $labelClass }}">
                        Daily Remarks
                    </label>

                    <textarea name="remarks"
                              rows="2"
                              maxlength="3000"
                              class="{{ $inputClass }}"
                              placeholder="Optional overall remarks for today's work execution">{{ old('remarks') }}</textarea>
                </div>

            </div>
        </div>

        <div class="mt-6">

            <x-rds.repeater
                title="Work Activities"
                subtitle="Each card represents one activity and can have its own location, material consumption, remarks and photos."
                add-label="+ Add Another Work Activity"
                container-id="work-activity-container"
                template-id="work-activity-template"
            >

                @foreach($oldWorks as $workIndex => $work)

                    <x-rds.activity-card
                        :index="$workIndex"
                        :status="$work['execution_status'] ?? 'In Progress'"
                    >
                        <div class="space-y-5 p-5">

                            <x-rds.location-selector
                                :index="$workIndex"
                                :blocks="$projectBlocks"
                                :floors="$projectFloors"
                                :units="$projectUnits"
                                :rooms="$projectRooms"
                                :subspaces="$projectSubspaces"
                                :values="$work"
                            />

                            <div class="rounded-lg border border-gray-200 bg-white p-4">

                                <x-rds.section-title
                                    title="Work Activity"
                                    subtitle="Define the construction activity, output and execution status."
                                    icon="⚙️"
                                />

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

                                    <div>
                                        <label class="{{ $labelClass }}">
                                            Work Stage
                                        </label>

                                        <select name="works[{{ $workIndex }}][work_stage_id]"
                                                class="{{ $inputClass }}">
                                            <option value="">Select Work Stage</option>

                                            @foreach($workStages as $stage)
                                                <option value="{{ $stage->id }}"
                                                    {{ (string) ($work['work_stage_id'] ?? '') === (string) $stage->id ? 'selected' : '' }}>
                                                    {{ $stage->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="{{ $labelClass }}">
                                            Activity Division
                                        </label>

                                        <select name="works[{{ $workIndex }}][activity_division_id]"
                                                class="{{ $inputClass }}"
                                                data-ref-activity-division-field>
                                            <option value="">Select Division</option>

                                            @foreach($activityDivisions as $division)
                                                <option value="{{ $division->id }}"
                                                    {{ (string) ($work['activity_division_id'] ?? '') === (string) $division->id ? 'selected' : '' }}>
                                                    {{ $division->name ?? $division->division_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="{{ $labelClass }}">
                                            Activity Mapping
                                        </label>

                                        <select name="works[{{ $workIndex }}][activity_mapping_id]"
                                                class="{{ $inputClass }}"
                                                data-ref-activity-mapping-field>

                                            <option value="">Select Mapping (Optional)</option>

                                            @foreach($activityMappings as $mapping)
                                                <option value="{{ $mapping->id }}"
                                                        data-division="{{ $mapping->activity_division_id }}"
                                                        data-activity="{{ $mapping->activity_id }}"
                                                        data-unit="{{ $mapping->unit }}"
                                                    {{ (string) ($work['activity_mapping_id'] ?? '') === (string) $mapping->id ? 'selected' : '' }}>
                                                    {{ $mapping->activity_name }}
                                                    @if($mapping->division)
                                                        — {{ $mapping->division->name ?? $mapping->division->division_name }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="{{ $labelClass }}">
                                            Activity <span class="text-red-500">*</span>
                                        </label>

                                        <select name="works[{{ $workIndex }}][activity_id]"
                                                class="{{ $inputClass }}"
                                                data-ref-activity-field
                                                required>

                                            <option value="">Select Activity</option>

                                            @foreach($activities as $activity)
                                                <option value="{{ $activity->id }}"
                                                        data-unit="{{ $activity->unit }}"
                                                    {{ (string) ($work['activity_id'] ?? '') === (string) $activity->id ? 'selected' : '' }}>
                                                    {{ $activity->activity_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="{{ $labelClass }}">
                                            Quantity Completed <span class="text-red-500">*</span>
                                        </label>

                                        <input type="number"
                                               min="0.001"
                                               step="0.001"
                                               name="works[{{ $workIndex }}][quantity_completed]"
                                               value="{{ $work['quantity_completed'] ?? '' }}"
                                               class="{{ $inputClass }}"
                                               required>
                                    </div>

                                    <div>
                                        <label class="{{ $labelClass }}">
                                            Unit
                                        </label>

                                        <input type="text"
                                               name="works[{{ $workIndex }}][unit]"
                                               value="{{ $work['unit'] ?? '' }}"
                                               class="{{ $inputClass }} bg-gray-100"
                                               data-ref-unit-field
                                               readonly>
                                    </div>

                                    <div>
                                        <label class="{{ $labelClass }}">
                                            Contractor
                                        </label>

                                        <select name="works[{{ $workIndex }}][contractor_id]"
                                                class="{{ $inputClass }}">
                                            <option value="">Select Contractor</option>

                                            @foreach($contractors as $contractor)
                                                <option value="{{ $contractor->id }}"
                                                    {{ (string) ($work['contractor_id'] ?? '') === (string) $contractor->id ? 'selected' : '' }}>
                                                    {{ $contractor->contractor_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="{{ $labelClass }}">
                                            Execution Status <span class="text-red-500">*</span>
                                        </label>

                                        <select name="works[{{ $workIndex }}][execution_status]"
                                                class="{{ $inputClass }}"
                                                data-ref-execution-status-field
                                                required>

                                            @foreach($executionStatuses as $status)
                                                <option value="{{ $status }}"
                                                    {{ ($work['execution_status'] ?? 'In Progress') === $status ? 'selected' : '' }}>
                                                    {{ $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="{{ $labelClass }}">
                                            Overall Progress %
                                        </label>

                                        <input type="number"
                                               min="0"
                                               max="100"
                                               step="0.01"
                                               name="works[{{ $workIndex }}][progress_percentage]"
                                               value="{{ $work['progress_percentage'] ?? '' }}"
                                               class="{{ $inputClass }}"
                                               placeholder="Optional">
                                    </div>

                                </div>
                            </div>

                            <x-rds.material-selector
                                :index="$workIndex"
                                :selected-ids="$work['material_consumed_ids'] ?? []"
                            />

                            <div class="rounded-lg border border-gray-200 bg-white p-4">

                                <x-rds.section-title
                                    title="Work Done Remarks"
                                    subtitle="Add measurement notes, constraints, observations or execution comments."
                                    icon="📝"
                                />

                                <textarea name="works[{{ $workIndex }}][remarks]"
                                          rows="3"
                                          maxlength="3000"
                                          class="{{ $inputClass }}"
                                          placeholder="Optional remarks">{{ $work['remarks'] ?? '' }}</textarea>
                            </div>

                            <x-rds.photo-uploader
                                :index="$workIndex"
                                :photo-types="$photoTypes"
                                :rows="$work['photos'] ?? []"
                            />

                        </div>
                    </x-rds.activity-card>

                @endforeach

                <x-slot:template>
                    <x-rds.activity-card
                        index="__INDEX__"
                        status="In Progress"
                    >
                        <div class="space-y-5 p-5">

                            <x-rds.location-selector
                                index="__INDEX__"
                                :blocks="$projectBlocks"
                                :floors="$projectFloors"
                                :units="$projectUnits"
                                :rooms="$projectRooms"
                                :subspaces="$projectSubspaces"
                            />

                            <div class="rounded-lg border border-gray-200 bg-white p-4">

                                <x-rds.section-title
                                    title="Work Activity"
                                    subtitle="Define the construction activity, output and execution status."
                                    icon="⚙️"
                                />

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

                                    <div>
                                        <label class="{{ $labelClass }}">Work Stage</label>

                                        <select name="works[__INDEX__][work_stage_id]"
                                                class="{{ $inputClass }}">
                                            <option value="">Select Work Stage</option>
                                            @foreach($workStages as $stage)
                                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="{{ $labelClass }}">Activity Division</label>

                                        <select name="works[__INDEX__][activity_division_id]"
                                                class="{{ $inputClass }}"
                                                data-ref-activity-division-field>
                                            <option value="">Select Division</option>
                                            @foreach($activityDivisions as $division)
                                                <option value="{{ $division->id }}">
                                                    {{ $division->name ?? $division->division_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="{{ $labelClass }}">Activity Mapping</label>

                                        <select name="works[__INDEX__][activity_mapping_id]"
                                                class="{{ $inputClass }}"
                                                data-ref-activity-mapping-field>
                                            <option value="">Select Mapping (Optional)</option>
                                            @foreach($activityMappings as $mapping)
                                                <option value="{{ $mapping->id }}"
                                                        data-division="{{ $mapping->activity_division_id }}"
                                                        data-activity="{{ $mapping->activity_id }}"
                                                        data-unit="{{ $mapping->unit }}">
                                                    {{ $mapping->activity_name }}
                                                    @if($mapping->division)
                                                        — {{ $mapping->division->name ?? $mapping->division->division_name }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="{{ $labelClass }}">
                                            Activity <span class="text-red-500">*</span>
                                        </label>

                                        <select name="works[__INDEX__][activity_id]"
                                                class="{{ $inputClass }}"
                                                data-ref-activity-field
                                                required>
                                            <option value="">Select Activity</option>
                                            @foreach($activities as $activity)
                                                <option value="{{ $activity->id }}"
                                                        data-unit="{{ $activity->unit }}">
                                                    {{ $activity->activity_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="{{ $labelClass }}">
                                            Quantity Completed <span class="text-red-500">*</span>
                                        </label>

                                        <input type="number"
                                               min="0.001"
                                               step="0.001"
                                               name="works[__INDEX__][quantity_completed]"
                                               class="{{ $inputClass }}"
                                               required>
                                    </div>

                                    <div>
                                        <label class="{{ $labelClass }}">Unit</label>

                                        <input type="text"
                                               name="works[__INDEX__][unit]"
                                               class="{{ $inputClass }} bg-gray-100"
                                               data-ref-unit-field
                                               readonly>
                                    </div>

                                    <div>
                                        <label class="{{ $labelClass }}">Contractor</label>

                                        <select name="works[__INDEX__][contractor_id]"
                                                class="{{ $inputClass }}">
                                            <option value="">Select Contractor</option>
                                            @foreach($contractors as $contractor)
                                                <option value="{{ $contractor->id }}">
                                                    {{ $contractor->contractor_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="{{ $labelClass }}">
                                            Execution Status <span class="text-red-500">*</span>
                                        </label>

                                        <select name="works[__INDEX__][execution_status]"
                                                class="{{ $inputClass }}"
                                                data-ref-execution-status-field
                                                required>
                                            @foreach($executionStatuses as $status)
                                                <option value="{{ $status }}"
                                                    {{ $status === 'In Progress' ? 'selected' : '' }}>
                                                    {{ $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="{{ $labelClass }}">Overall Progress %</label>

                                        <input type="number"
                                               min="0"
                                               max="100"
                                               step="0.01"
                                               name="works[__INDEX__][progress_percentage]"
                                               class="{{ $inputClass }}"
                                               placeholder="Optional">
                                    </div>

                                </div>
                            </div>

                            <x-rds.material-selector
                                index="__INDEX__"
                            />

                            <div class="rounded-lg border border-gray-200 bg-white p-4">

                                <x-rds.section-title
                                    title="Work Done Remarks"
                                    subtitle="Add measurement notes, constraints, observations or execution comments."
                                    icon="📝"
                                />

                                <textarea name="works[__INDEX__][remarks]"
                                          rows="3"
                                          maxlength="3000"
                                          class="{{ $inputClass }}"
                                          placeholder="Optional remarks"></textarea>
                            </div>

                            <x-rds.photo-uploader
                                index="__INDEX__"
                                :photo-types="$photoTypes"
                            />

                        </div>
                    </x-rds.activity-card>
                </x-slot:template>

            </x-rds.repeater>

        </div>

        <div class="mt-6 flex flex-wrap gap-3">

            <button type="submit"
                    class="rounded-lg bg-blue-600 px-7 py-3 font-semibold text-white hover:bg-blue-700">
                Save Daily Work Execution
            </button>

            <a href="{{ route('work-done.index') }}"
               class="rounded-lg bg-gray-500 px-7 py-3 font-semibold text-white hover:bg-gray-600">
                Cancel
            </a>

        </div>
    </form>
</div>

<script src="{{ asset('js/ravion-execution.js') }}"></script>

@endsection
