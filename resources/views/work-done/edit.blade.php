@extends('layouts.app')

@section('content')
<style>
/* Work Done resource entries: explicit CSS because dynamic JS classes are not scanned by Vite/Tailwind. */
.rwd-entry-row{display:grid;grid-template-columns:minmax(0,1fr);align-items:end;gap:8px}
.rwd-entry-row>label{min-width:0;display:block}
.rwd-entry-row>label>input,.rwd-entry-row>label>select{display:block;width:100%;min-width:0;margin-top:4px}
.rwd-entry-row>button{min-height:38px;white-space:nowrap}
.rwd-machine-filters{display:grid;grid-template-columns:minmax(0,1fr);gap:8px}
.rwd-machine-filters>label{min-width:0}
.rwd-machine-filters>label>input,.rwd-machine-filters>label>select{display:block;width:100%;min-width:0;margin-top:4px}
@media(min-width:768px){
.rwd-entry-three{grid-template-columns:minmax(0,1fr) 155px 145px}
.rwd-entry-machinery{grid-template-columns:minmax(0,1fr) 100px 135px 155px}
.rwd-machine-filters{grid-template-columns:minmax(0,1.2fr) minmax(0,1fr) minmax(0,1fr) minmax(0,1.2fr)}
}
</style>


@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-base text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:py-2 sm:text-sm';
    $labelClass = 'mb-1 block text-sm font-semibold text-gray-700';

    $summaryItems = [
        ['key' => 'activities', 'label' => 'Activities', 'value' => 1],
        ['key' => 'materials', 'label' => 'Materials', 'value' => 0],
        ['key' => 'photos', 'label' => 'Photos', 'value' => 0],
    ];

    // Edit uses the same Create screen and its working canonical picker.
    $editableItems = $workDone->items->filter(fn ($item) => $item->dpr_id === null)->values();
    $lockedItems = $workDone->items->filter(fn ($item) => $item->dpr_id !== null)->values();
    $itemIds = $editableItems->pluck('id')->all();
    $savedMaterials = \Illuminate\Support\Facades\DB::table('work_done_reported_materials')->whereIn('work_done_item_id', $itemIds)->orderBy('sort_order')->get()->groupBy('work_done_item_id');
    $savedLabours = \Illuminate\Support\Facades\DB::table('work_done_item_labours')->whereIn('work_done_item_id', $itemIds)->orderBy('sort_order')->get()->groupBy('work_done_item_id');
    $savedMachinery = \Illuminate\Support\Facades\DB::table('work_done_item_machinery')->whereIn('work_done_item_id', $itemIds)->orderBy('sort_order')->get()->groupBy('work_done_item_id');
    $initialWorks = $editableItems->map(function ($item) use ($savedMaterials, $savedLabours, $savedMachinery) {
        return [
            'id' => $item->id,
            'work_stage_id' => $item->work_stage_id,
            'activity_division_id' => $item->activity_division_id,
            'activity_id' => $item->activity_id,
            'work_activity_id' => $item->work_activity_id,
            'activity_mapping_id' => $item->activity_mapping_id,
            'contractor_id' => $item->contractor_id,
            'project_block_id' => $item->project_block_id,
            'project_floor_id' => $item->project_floor_id,
            'project_unit_id' => $item->project_unit_id,
            'project_room_id' => $item->project_room_id,
            'project_subspace_id' => $item->project_subspace_id,
            'quantity_completed' => $item->quantity_completed,
            'unit' => $item->unit,
            'progress_percentage' => $item->progress_percentage,
            'execution_status' => $item->execution_status,
            'remarks' => $item->remarks,
            'materials_used' => ($savedMaterials[$item->id] ?? collect())->map(fn ($r) => ['stock_key' => $r->stock_key, 'quantity' => $r->quantity_reported])->values()->all(),
            'labours' => ($savedLabours[$item->id] ?? collect())->map(fn ($r) => ['labour_group_id' => $r->labour_group_id, 'quantity' => $r->quantity])->values()->all(),
            'machinery_used' => ($savedMachinery[$item->id] ?? collect())->map(fn ($r) => ['equipment_name' => $r->equipment_name, 'quantity' => $r->quantity, 'operating_hours' => $r->operating_hours])->values()->all(),
            'photos' => [],
        ];
    })->values()->all();
    $initialWorks = count($initialWorks) ? $initialWorks : [[
        'work_activity_id' => '', 'quantity_completed' => '', 'execution_status' => 'In Progress',
        'materials_used' => [], 'labours' => [], 'machinery_used' => [], 'photos' => [],
    ]];
    $oldWorks = collect(old('works', $initialWorks))->values();
    $initialResourceData = $oldWorks->map(fn ($work) => [
        'materials' => $work['materials_used'] ?? [],
        'labours' => $work['labours'] ?? [],
        'machinery' => $work['machinery_used'] ?? [],
    ])->values()->all();
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">
                Daily Work Execution
            </h1>

            <p class="mt-1 text-gray-500">
                Correct saved activities and reported resources. DPR-linked activities are protected.
            </p>
        </div>

        <a href="{{ route('work-done.show', $workDone) }}"
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
          action="{{ route('work-done.update', $workDone) }}"
          enctype="multipart/form-data"
          data-ref-work-done-form
          data-ref-materials-url="{{ route('work-done.available-materials') }}"
          data-ref-labour-url="{{ route('work-done.available-labour') }}">

        @csrf
        @method('PUT')

        <x-rds.summary-bar
            title="Edit Daily Execution"
            subtitle="Edit activities that have not been linked to a DPR."
            :items="$summaryItems"
        />

        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

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
                                {{ (string) old('project_id', $workDone->project_id) === (string) $project->id ? 'selected' : '' }}>
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
                           value="{{ old('work_date', $workDone->work_date?->format('Y-m-d')) }}"
                           class="{{ $inputClass }}"
                           data-ref-work-date-field
                           readonly required>
                </div>

                <div class="hidden sm:block">
                    <label class="{{ $labelClass }}">
                        Engineer
                    </label>

                    <input type="text"
                           value="{{ $workDone->engineer?->name ?? auth()->user()->name }}"
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
                              placeholder="Optional overall remarks for today's work execution">{{ old('remarks', $workDone->remarks) }}</textarea>
                </div>

            </div>

            <div class="mt-3 rounded-lg bg-blue-50 px-3 py-2 text-xs text-blue-800 sm:hidden">
                Engineer: <span class="font-semibold">{{ auth()->user()->name }}</span>
            </div>
        </div>

        <div class="mt-6">

            <x-rds.repeater
                title="Work Activities"
                subtitle="Each activity records location, quantity, labour, equipment, reported materials, remarks and photos."
                add-label="+ Add Another Work Activity"
                container-id="work-activity-container"
                template-id="work-activity-template"
            >

                @foreach($oldWorks as $workIndex => $work)

                    <x-rds.activity-card
                        :index="$workIndex"
                        :status="$work['execution_status'] ?? 'In Progress'"
                    >
                        <div class="space-y-4 p-3 sm:space-y-5 sm:p-5">
                            @if(!empty($work['id']))
                                <input type="hidden" name="works[{{ $workIndex }}][id]" value="{{ $work['id'] }}">
                            @endif

                            <x-rds.location-selector
                                :index="$workIndex"
                                :blocks="$projectBlocks"
                                :floors="$projectFloors"
                                :units="$projectUnits"
                                :rooms="$projectRooms"
                                :subspaces="$projectSubspaces"
                                :values="$work"
                            />

                            <div class="rounded-lg border border-gray-200 bg-white p-3 sm:p-4" x-data="{ moreDetails: false }">

                                <x-rds.section-title
                                    title="Work Execution"
                                    subtitle="Select the work activity, then enter completed quantity, unit, contractor and execution progress."
                                    icon="⚙️"
                                />

                                <div class="mb-4 rounded-lg border border-blue-100 bg-blue-50/50 p-3 sm:p-4" data-canonical-picker>
                                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                        <span class="text-sm font-bold text-slate-800">Work Execution Master <span class="text-red-500">*</span></span>
                                        <span class="text-xs text-slate-500">Search by activity name or browse categories</span>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                                        <div class="">
                                            <label class="{{ $labelClass }}">Search work activity</label>
                                            <input type="search" autocomplete="off" data-canonical-search class="{{ $inputClass }}" placeholder="e.g. Internal Material Shifting, brick masonry, slab concrete">
                                            <div data-canonical-results class="hidden mt-1 max-h-56 overflow-auto rounded-lg border border-slate-200 bg-white shadow-md" role="listbox"></div>
                                        </div>
                                        <div><label class="{{ $labelClass }}">Work Package</label><select data-canonical-package class="{{ $inputClass }}"><option value="">All packages</option></select></div>
                                        <div><label class="{{ $labelClass }}">Work Section</label><select data-canonical-section class="{{ $inputClass }}"><option value="">All sections</option></select></div>
                                        <div><label class="{{ $labelClass }}">Work Activity</label><select data-canonical-activity class="{{ $inputClass }}"><option value="">Choose an activity</option></select></div>
                                    </div>
                                    <input type="hidden" name="works[{{ $workIndex }}][work_activity_id]" value="{{ $work['work_activity_id'] ?? '' }}" data-canonical-id>
                                    <p data-canonical-selection class="mt-2 text-xs font-semibold text-slate-600">No canonical activity selected.</p>
                                </div>
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

                                    <div class="hidden" x-show="false">
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

                                    <div class="hidden">
                                        <label class="{{ $labelClass }}">
                                            Activity Division
                                        </label>

                                        <select name="works[{{ $workIndex }}][activity_division_id]"
                                                class="{{ $inputClass }}"
                                                data-ref-legacy-division-field>
                                            <option value="">Select Division</option>

                                            @foreach($activityDivisions as $division)
                                                <option value="{{ $division->id }}"
                                                    {{ (string) ($work['activity_division_id'] ?? '') === (string) $division->id ? 'selected' : '' }}>
                                                    {{ $division->name ?? $division->division_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="hidden" x-show="false">
                                        <label class="{{ $labelClass }}">
                                            Activity Mapping
                                        </label>

                                        <select name="works[{{ $workIndex }}][activity_mapping_id]"
                                                class="{{ $inputClass }}"
                                                data-ref-legacy-mapping-field>

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

                                    <div class="hidden">
                                        <label class="{{ $labelClass }}">
                                            Activity <span class="text-red-500">*</span>
                                        </label>

                                        <select name="works[{{ $workIndex }}][activity_id]"
                                                class="{{ $inputClass }}"
                                                data-ref-legacy-activity-field>

                                            <option value="">Select Activity</option>

                                            @foreach($activities as $activity)
                                                <option value="{{ $activity->id }}"
        data-division="{{ $activity->activity_division_id }}"
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

                                        <select name="works[{{ $workIndex }}][unit]" class="{{ $inputClass }}" data-ref-canonical-unit-field>
                                                <option value="">Select reporting unit</option>
                                                <option value="Sqft" {{ (string) ($work['unit'] ?? '') === 'Sqft' ? 'selected' : '' }}>Sqft</option>
                                                <option value="Sqm" {{ (string) ($work['unit'] ?? '') === 'Sqm' ? 'selected' : '' }}>Sqm</option>
                                                <option value="Cum" {{ (string) ($work['unit'] ?? '') === 'Cum' ? 'selected' : '' }}>Cum</option>
                                                <option value="Cuft" {{ (string) ($work['unit'] ?? '') === 'Cuft' ? 'selected' : '' }}>Cuft</option>
                                                <option value="Rft" {{ (string) ($work['unit'] ?? '') === 'Rft' ? 'selected' : '' }}>Rft</option>
                                                <option value="Rm" {{ (string) ($work['unit'] ?? '') === 'Rm' ? 'selected' : '' }}>Rm</option>
                                                <option value="Nos" {{ (string) ($work['unit'] ?? '') === 'Nos' ? 'selected' : '' }}>Nos</option>
                                                <option value="Bags" {{ (string) ($work['unit'] ?? '') === 'Bags' ? 'selected' : '' }}>Bags</option>
                                                <option value="Kg" {{ (string) ($work['unit'] ?? '') === 'Kg' ? 'selected' : '' }}>Kg</option>
                                                <option value="MT" {{ (string) ($work['unit'] ?? '') === 'MT' ? 'selected' : '' }}>MT</option>
                                                <option value="Ltr" {{ (string) ($work['unit'] ?? '') === 'Ltr' ? 'selected' : '' }}>Ltr</option>
                                                <option value="Load" {{ (string) ($work['unit'] ?? '') === 'Load' ? 'selected' : '' }}>Load</option>
                                                <option value="Hours" {{ (string) ($work['unit'] ?? '') === 'Hours' ? 'selected' : '' }}>Hours</option>
                                                <option value="Days" {{ (string) ($work['unit'] ?? '') === 'Days' ? 'selected' : '' }}>Days</option>
                                                <option value="Set" {{ (string) ($work['unit'] ?? '') === 'Set' ? 'selected' : '' }}>Set</option>
                                                <option value="Pair" {{ (string) ($work['unit'] ?? '') === 'Pair' ? 'selected' : '' }}>Pair</option>
                                                @if(!empty($work['unit']) && !in_array($work['unit'], ['Sqft','Sqm','Cum','Cuft','Rft','Rm','Nos','Bags','Kg','MT','Ltr','Load','Hours','Days','Set','Pair'], true))
                                                    <option value="{{ $work['unit'] }}" selected>{{ $work['unit'] }}</option>
                                                @endif
                                            </select>
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

                                    <div class="">
                                        <label class="{{ $labelClass }}">
                                            Execution Progress %
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

                                <button
                                    type="button"
                                    @click="moreDetails = !moreDetails"
                                    class="mt-4 inline-flex w-full items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-[#0F2A52] lg:hidden"
                                >
                                    <span x-text="moreDetails ? 'Hide More Details' : 'More Details'"></span>
                                </button>
                            </div>


                            <div class="rounded-lg border border-slate-200 bg-white p-3 sm:p-4" data-ref-reported-materials>
                                <div class="flex items-center justify-between gap-2"><div><h3 class="font-bold text-slate-800">📦 Materials Used</h3><p class="text-xs text-slate-500">Information only. Only positive-stock materials. Add entries to the table below; no stock deduction.</p></div></div>
                                <div data-ref-reported-material-rows class="mt-3 space-y-2"></div>
                                <p data-ref-reported-material-message class="mt-2 text-xs text-slate-500">Select a project to load available inventory.</p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-white p-3 sm:p-4" data-ref-resource-panel>
                                <div class="flex items-center justify-between gap-2"><div><h3 class="font-bold text-slate-800">👷 Labour Used</h3><p class="text-xs text-slate-500">Enter labour type and deployed count. If attendance is not yet marked, reporting is still allowed.</p></div></div>
                                <div data-ref-labour-rows class="mt-3 space-y-2"></div>
                                <p data-ref-labour-message class="mt-2 text-xs text-slate-500">Attendance is optional for reporting; present counts display when available.</p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-white p-3 sm:p-4" data-ref-machinery-panel>
                                <div><h3 class="font-bold text-slate-800">🚜 Machinery &amp; Equipment Used</h3><p class="text-xs text-slate-500">Record equipment used on this activity; no rates or costs.</p></div>
                                <div data-ref-machinery-rows class="mt-3"></div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-white p-3 sm:p-4">

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
                        <div class="space-y-4 p-3 sm:space-y-5 sm:p-5">

                            <x-rds.location-selector
                                index="__INDEX__"
                                :blocks="$projectBlocks"
                                :floors="$projectFloors"
                                :units="$projectUnits"
                                :rooms="$projectRooms"
                                :subspaces="$projectSubspaces"
                            />

                            <div class="rounded-lg border border-gray-200 bg-white p-3 sm:p-4" x-data="{ moreDetails: false }">

                                <x-rds.section-title
                                    title="Work Execution"
                                    subtitle="Select the work activity, then enter completed quantity, unit, contractor and execution progress."
                                    icon="⚙️"
                                />

                                <div class="mb-4 rounded-lg border border-blue-100 bg-blue-50/50 p-3 sm:p-4" data-canonical-picker>
                                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                        <span class="text-sm font-bold text-slate-800">Work Execution Master <span class="text-red-500">*</span></span>
                                        <span class="text-xs text-slate-500">Search by activity name or browse categories</span>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                                        <div class="">
                                            <label class="{{ $labelClass }}">Search work activity</label>
                                            <input type="search" autocomplete="off" data-canonical-search class="{{ $inputClass }}" placeholder="e.g. Internal Material Shifting, brick masonry, slab concrete">
                                            <div data-canonical-results class="hidden mt-1 max-h-56 overflow-auto rounded-lg border border-slate-200 bg-white shadow-md" role="listbox"></div>
                                        </div>
                                        <div><label class="{{ $labelClass }}">Work Package</label><select data-canonical-package class="{{ $inputClass }}"><option value="">All packages</option></select></div>
                                        <div><label class="{{ $labelClass }}">Work Section</label><select data-canonical-section class="{{ $inputClass }}"><option value="">All sections</option></select></div>
                                        <div><label class="{{ $labelClass }}">Work Activity</label><select data-canonical-activity class="{{ $inputClass }}"><option value="">Choose an activity</option></select></div>
                                    </div>
                                    <input type="hidden" name="works[__INDEX__][work_activity_id]" value="" data-canonical-id>
                                    <p data-canonical-selection class="mt-2 text-xs font-semibold text-slate-600">No canonical activity selected.</p>
                                </div>
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

                                    <div class="hidden" x-show="false">
                                        <label class="{{ $labelClass }}">Work Stage</label>

                                        <select name="works[__INDEX__][work_stage_id]"
                                                class="{{ $inputClass }}">
                                            <option value="">Select Work Stage</option>
                                            @foreach($workStages as $stage)
                                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="hidden">
                                        <label class="{{ $labelClass }}">Activity Division</label>

                                        <select name="works[__INDEX__][activity_division_id]"
                                                class="{{ $inputClass }}"
                                                data-ref-legacy-division-field>
                                            <option value="">Select Division</option>
                                            @foreach($activityDivisions as $division)
                                                <option value="{{ $division->id }}">
                                                    {{ $division->name ?? $division->division_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="hidden" x-show="false">
                                        <label class="{{ $labelClass }}">Activity Mapping</label>

                                        <select name="works[__INDEX__][activity_mapping_id]"
                                                class="{{ $inputClass }}"
                                                data-ref-legacy-mapping-field>
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

                                    <div class="hidden">
                                        <label class="{{ $labelClass }}">
                                            Activity <span class="text-red-500">*</span>
                                        </label>

                                        <select name="works[__INDEX__][activity_id]"
                                                class="{{ $inputClass }}"
                                                data-ref-legacy-activity-field>
                                            <option value="">Select Activity</option>
                                            @foreach($activities as $activity)
                                                <option value="{{ $activity->id }}"
        data-division="{{ $activity->activity_division_id }}"
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

                                        <select name="works[__INDEX__][unit]" class="{{ $inputClass }}" data-ref-canonical-unit-field>
                                                <option value="">Select reporting unit</option>
                                                <option value="Sqft">Sqft</option>
                                                <option value="Sqm">Sqm</option>
                                                <option value="Cum">Cum</option>
                                                <option value="Cuft">Cuft</option>
                                                <option value="Rft">Rft</option>
                                                <option value="Rm">Rm</option>
                                                <option value="Nos">Nos</option>
                                                <option value="Bags">Bags</option>
                                                <option value="Kg">Kg</option>
                                                <option value="MT">MT</option>
                                                <option value="Ltr">Ltr</option>
                                                <option value="Load">Load</option>
                                                <option value="Hours">Hours</option>
                                                <option value="Days">Days</option>
                                                <option value="Set">Set</option>
                                                <option value="Pair">Pair</option>
                                            </select>
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

                                    <div class="">
                                        <label class="{{ $labelClass }}">Execution Progress %</label>

                                        <input type="number"
                                               min="0"
                                               max="100"
                                               step="0.01"
                                               name="works[__INDEX__][progress_percentage]"
                                               class="{{ $inputClass }}"
                                               placeholder="Optional">
                                    </div>

                                </div>

                                <button
                                    type="button"
                                    @click="moreDetails = !moreDetails"
                                    class="mt-4 inline-flex w-full items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-[#0F2A52] lg:hidden"
                                >
                                    <span x-text="moreDetails ? 'Hide More Details' : 'More Details'"></span>
                                </button>
                            </div>


                            <div class="rounded-lg border border-slate-200 bg-white p-3 sm:p-4" data-ref-reported-materials>
                                <div class="flex items-center justify-between gap-2"><div><h3 class="font-bold text-slate-800">📦 Materials Used</h3><p class="text-xs text-slate-500">Information only. Only positive-stock materials. Add entries to the table below; no stock deduction.</p></div></div>
                                <div data-ref-reported-material-rows class="mt-3 space-y-2"></div>
                                <p data-ref-reported-material-message class="mt-2 text-xs text-slate-500">Select a project to load available inventory.</p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-white p-3 sm:p-4" data-ref-resource-panel>
                                <div class="flex items-center justify-between gap-2"><div><h3 class="font-bold text-slate-800">👷 Labour Used</h3><p class="text-xs text-slate-500">Enter labour type and deployed count. If attendance is not yet marked, reporting is still allowed.</p></div></div>
                                <div data-ref-labour-rows class="mt-3 space-y-2"></div>
                                <p data-ref-labour-message class="mt-2 text-xs text-slate-500">Attendance is optional for reporting; present counts display when available.</p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-white p-3 sm:p-4" data-ref-machinery-panel>
                                <div><h3 class="font-bold text-slate-800">🚜 Machinery &amp; Equipment Used</h3><p class="text-xs text-slate-500">Record equipment used on this activity; no rates or costs.</p></div>
                                <div data-ref-machinery-rows class="mt-3"></div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-white p-3 sm:p-4">

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

        <div class="sticky bottom-[68px] z-30 mt-6 flex flex-col gap-3 border-t border-gray-200 bg-white/95 py-3 backdrop-blur sm:flex-row lg:static lg:border-0 lg:bg-transparent lg:py-0">

            <button type="submit"
                    class="w-full rounded-lg bg-blue-600 px-7 py-3 font-semibold text-white hover:bg-blue-700 sm:w-auto">
                Save Changes
            </button>

            <a href="{{ route('work-done.show', $workDone) }}"
               class="w-full rounded-lg bg-gray-500 px-7 py-3 text-center font-semibold text-white hover:bg-gray-600 sm:w-auto">
                Cancel
            </a>

        </div>
    </form>
</div>

<script src="{{ asset('js/ravion-execution.js') }}?v=20260928-canonical-stage1-2"></script>
<script src="{{ asset('js/ravion-work-canonical.js') }}?v=20260928-canonical-stage1-2"></script>


<script>
// Create-screen collapse guard: runs independently of cached external scripts.
// The existing repeater still owns activity creation, indexing and materials.
(() => {
  const form = document.querySelector('[data-ref-work-done-form]');
  if (!form) return;
  const collapse = container => {
    const cards = [...container.querySelectorAll('[data-ref-activity-card]')];
    if (cards.length < 2) return;
    cards.forEach((card, i) => {
      const body = card.querySelector('[data-ref-activity-body]');
      const toggle = card.querySelector('[data-ref-toggle-activity]');
      if (!body) return;
      const open = i === cards.length - 1;
      body.classList.toggle('hidden', !open);
      if (toggle) toggle.textContent = open ? 'Collapse' : 'Expand';
    });
    cards[cards.length - 1].scrollIntoView({behavior:'smooth',block:'start'});
  };
  form.addEventListener('click', e => {
    const button = e.target.closest('[data-ref-add-activity]');
    if (!button) return;
    const container = document.getElementById(button.dataset.refContainerId);
    if (!container) return;
    const before = container.querySelectorAll('[data-ref-activity-card]').length;
    // Repeater and validation handlers execute first; only collapse if a card was actually added.
    setTimeout(() => {
      if (container.querySelectorAll('[data-ref-activity-card]').length > before) collapse(container);
    }, 50);
  }, true);
})();
</script>

@php
    $labourGroupOptions = \App\Models\LabourGroup::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
    $labourRoleOptions = \App\Models\DesignationRole::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
    $labourContractorOptions = $contractors->map(fn ($c) => ['id' => $c->id, 'name' => $c->contractor_name])->values();
@endphp
<script>
window.RAVION_WORK_LABOUR = {
    groups: @json($labourGroupOptions),
    roles: @json($labourRoleOptions),
    contractors: @json($labourContractorOptions),
};
</script>
<script src="{{ asset('js/ravion-work-resources.js') }}?v=20260929-v4"></script>

<script>
window.RAVION_WORK_EDIT_RESOURCES = @json($initialResourceData);
</script>
<script src="{{ asset('js/ravion-work-edit-hydration.js') }}?v=20260929-edit1"></script>

@endsection
