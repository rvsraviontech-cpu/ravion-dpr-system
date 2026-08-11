@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
    $labelClass = 'mb-1 block text-sm font-semibold text-gray-700';
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Create Daily Progress Report
            </h1>

            <p class="mt-1 text-gray-500">
                Select the Project and Date. Ravion will load the execution records already entered during the day.
            </p>
        </div>

        <a href="{{ route('dprs.index') }}"
           class="inline-flex items-center justify-center rounded-lg bg-gray-600 px-5 py-2.5 font-semibold text-white hover:bg-gray-700">
            Back
        </a>
    </div>

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
          action="{{ route('dprs.store') }}"
          enctype="multipart/form-data"
          id="dprReviewForm"
          data-execution-url="{{ route('dprs.execution-data') }}">

        @csrf

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <x-rds.section-title
                title="DPR Header"
                subtitle="Project, date, weather and overall remarks."
                icon="📋"
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
                        DPR Date <span class="text-red-500">*</span>
                    </label>

                    <input type="date"
                           name="dpr_date"
                           id="dpr_date"
                           value="{{ old('dpr_date', now()->format('Y-m-d')) }}"
                           class="{{ $inputClass }}"
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

                <div>
                    <label class="{{ $labelClass }}">
                        Weather
                    </label>

                    <input type="text"
                           name="weather"
                           value="{{ old('weather') }}"
                           maxlength="255"
                           class="{{ $inputClass }}"
                           placeholder="Sunny / Cloudy / Rainy">
                </div>

                <div class="md:col-span-2 xl:col-span-3">
                    <label class="{{ $labelClass }}">
                        General Remarks
                    </label>

                    <input type="text"
                           name="remarks"
                           value="{{ old('remarks') }}"
                           maxlength="5000"
                           class="{{ $inputClass }}"
                           placeholder="Optional overall DPR remarks">
                </div>

            </div>

            <div class="mt-5 flex flex-wrap items-center gap-3">
                <button type="button"
                        id="loadExecutionButton"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Load Daily Execution
                </button>

                <span id="loadStatus"
                      class="text-sm text-gray-500">
                    Select Project and Date to review today's recorded execution.
                </span>
            </div>
        </div>

        <div id="existingDprNotice"
             class="mt-6 hidden rounded-xl border border-amber-300 bg-amber-50 p-5 text-amber-900">
        </div>

        <div id="executionSummary"
             class="mt-6 hidden grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-7">
        </div>

        <div id="executionSections"
             class="mt-6 hidden space-y-6">

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm"
                 data-section="labour_attendances">
                <div class="flex items-center justify-between border-b border-gray-200 p-5">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Labour Attendance</h2>
                        <p class="mt-1 text-sm text-gray-500">Daily attendance sheets already recorded for the Project and Date.</p>
                    </div>
                    <button type="button" class="section-toggle text-sm font-semibold text-blue-700">Unselect All</button>
                </div>
                <div class="space-y-3 p-5" data-list="labour_attendances"></div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm"
                 data-section="work_done">
                <div class="flex items-center justify-between border-b border-gray-200 p-5">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Work Done</h2>
                        <p class="mt-1 text-sm text-gray-500">Physical work activities recorded during the day.</p>
                    </div>
                    <button type="button" class="section-toggle text-sm font-semibold text-blue-700">Unselect All</button>
                </div>
                <div class="space-y-3 p-5" data-list="work_done"></div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm"
                 data-section="material_received">
                <div class="flex items-center justify-between border-b border-gray-200 p-5">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Material Received</h2>
                        <p class="mt-1 text-sm text-gray-500">Material receipts entered for the day.</p>
                    </div>
                    <button type="button" class="section-toggle text-sm font-semibold text-blue-700">Unselect All</button>
                </div>
                <div class="space-y-3 p-5" data-list="material_received"></div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm"
                 data-section="material_consumed">
                <div class="flex items-center justify-between border-b border-gray-200 p-5">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Material Consumed</h2>
                        <p class="mt-1 text-sm text-gray-500">Material consumption already recorded for this Project and Date.</p>
                    </div>
                    <button type="button" class="section-toggle text-sm font-semibold text-blue-700">Unselect All</button>
                </div>
                <div class="space-y-3 p-5" data-list="material_consumed"></div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm"
                 data-section="material_required">
                <div class="flex items-center justify-between border-b border-gray-200 p-5">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Material Required</h2>
                        <p class="mt-1 text-sm text-gray-500">Requirements created during this DPR day, normally for upcoming work.</p>
                    </div>
                    <button type="button" class="section-toggle text-sm font-semibold text-blue-700">Unselect All</button>
                </div>
                <div class="space-y-3 p-5" data-list="material_required"></div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm"
                 data-section="site_issues">
                <div class="flex items-center justify-between border-b border-gray-200 p-5">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Site Issues</h2>
                        <p class="mt-1 text-sm text-gray-500">Issues and delays reported during the selected day.</p>
                    </div>
                    <button type="button" class="section-toggle text-sm font-semibold text-blue-700">Unselect All</button>
                </div>
                <div class="space-y-3 p-5" data-list="site_issues"></div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm"
                 data-section="tomorrow_plans">
                <div class="flex items-center justify-between border-b border-gray-200 p-5">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Tomorrow Plan</h2>
                        <p class="mt-1 text-sm text-gray-500">Planning entries created during the DPR day for upcoming execution.</p>
                    </div>
                    <button type="button" class="section-toggle text-sm font-semibold text-blue-700">Unselect All</button>
                </div>
                <div class="space-y-3 p-5" data-list="tomorrow_plans"></div>
            </div>

        </div>

        <div id="emptyExecution"
             class="mt-6 hidden rounded-xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center">
            <div class="text-lg font-semibold text-gray-700">
                No standalone execution records found
            </div>

            <p class="mt-2 text-sm text-gray-500">
                Record the day's activities in the respective modules first, then return here to compile the DPR.
            </p>
        </div>


        {{-- Machinery / Equipment --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="flex flex-col gap-3 border-b border-gray-200 p-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">
                        Machinery / Equipment Used
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Add machinery or equipment used during the day.
                    </p>
                </div>

                <button type="button"
                        id="addMachineryButton"
                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                    + Add Machinery / Equipment
                </button>
            </div>

            <div id="machineryRows" class="divide-y divide-gray-200">

                <div class="machinery-row p-5">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">

                        <div class="xl:col-span-2">
                            <label class="{{ $labelClass }}">Machinery / Equipment</label>

                            <select name="machinery[0][machinery_tool_id]"
                                    class="{{ $inputClass }} machinery-select">
                                <option value="">Select Machinery / Equipment</option>

                                @foreach($machineries as $machine)
                                    <option value="{{ $machine->id }}">
                                        {{ $machine->machine_name }}
                                        @if($machine->ownership_type)
                                            — {{ $machine->ownership_type }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Quantity</label>
                            <input type="number"
                                   name="machinery[0][quantity]"
                                   value="1"
                                   min="1"
                                   class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Usage Hours</label>
                            <input type="number"
                                   name="machinery[0][usage_hours]"
                                   step="0.01"
                                   min="0"
                                   max="24"
                                   class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Condition</label>
                            <select name="machinery[0][working_condition]"
                                    class="{{ $inputClass }}">
                                <option value="Working">Working</option>
                                <option value="Breakdown">Breakdown</option>
                                <option value="Idle">Idle</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="button"
                                    class="remove-machinery hidden w-full rounded-lg bg-red-600 px-3 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                                Remove
                            </button>
                        </div>

                        <div class="md:col-span-2 xl:col-span-6">
                            <label class="{{ $labelClass }}">Remarks</label>
                            <input type="text"
                                   name="machinery[0][remarks]"
                                   maxlength="1000"
                                   class="{{ $inputClass }}"
                                   placeholder="Optional">
                        </div>

                    </div>
                </div>

            </div>

            <template id="machineryRowTemplate">
                <div class="machinery-row p-5">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">

                        <div class="xl:col-span-2">
                            <label class="{{ $labelClass }}">Machinery / Equipment</label>

                            <select name="machinery[__INDEX__][machinery_tool_id]"
                                    class="{{ $inputClass }} machinery-select">
                                <option value="">Select Machinery / Equipment</option>

                                @foreach($machineries as $machine)
                                    <option value="{{ $machine->id }}">
                                        {{ $machine->machine_name }}
                                        @if($machine->ownership_type)
                                            — {{ $machine->ownership_type }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Quantity</label>
                            <input type="number"
                                   name="machinery[__INDEX__][quantity]"
                                   value="1"
                                   min="1"
                                   class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Usage Hours</label>
                            <input type="number"
                                   name="machinery[__INDEX__][usage_hours]"
                                   step="0.01"
                                   min="0"
                                   max="24"
                                   class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Condition</label>
                            <select name="machinery[__INDEX__][working_condition]"
                                    class="{{ $inputClass }}">
                                <option value="Working">Working</option>
                                <option value="Breakdown">Breakdown</option>
                                <option value="Idle">Idle</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="button"
                                    class="remove-machinery w-full rounded-lg bg-red-600 px-3 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                                Remove
                            </button>
                        </div>

                        <div class="md:col-span-2 xl:col-span-6">
                            <label class="{{ $labelClass }}">Remarks</label>
                            <input type="text"
                                   name="machinery[__INDEX__][remarks]"
                                   maxlength="1000"
                                   class="{{ $inputClass }}"
                                   placeholder="Optional">
                        </div>

                    </div>
                </div>
            </template>
        </div>

        {{-- DPR Photo Upload --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <x-rds.section-title
                title="DPR Photo Upload"
                subtitle="Upload additional site photos for this DPR."
                icon="📷"
            />

            <input type="file"
                   name="photos[]"
                   id="dprPhotos"
                   multiple
                   accept="image/jpeg,image/png,image/webp,image/*"
                   class="{{ $inputClass }}">

            <p class="mt-2 text-xs text-gray-500">
                JPG, PNG or WEBP. Maximum 10 MB per image.
            </p>

            <div id="dprPhotoPreview"
                 class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
            </div>
        </div>

        <div id="submitBar"
             class="sticky bottom-0 z-20 mt-6 hidden border-t border-gray-200 bg-white/95 p-4 shadow-lg backdrop-blur">

            <div class="mx-auto flex max-w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                <div class="text-sm text-gray-600">
                    Selected execution records:
                    <span id="selectedCount" class="font-bold text-gray-900">0</span>
                </div>

                <button type="submit"
                        id="submitDprButton"
                        class="rounded-lg bg-blue-600 px-8 py-3 font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                        disabled>
                    Submit DPR for PMO Review
                </button>

            </div>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('dprReviewForm');
    const project = document.getElementById('project_id');
    const dprDate = document.getElementById('dpr_date');
    const loadButton = document.getElementById('loadExecutionButton');
    const loadStatus = document.getElementById('loadStatus');
    const summary = document.getElementById('executionSummary');
    const sections = document.getElementById('executionSections');
    const emptyExecution = document.getElementById('emptyExecution');
    const submitBar = document.getElementById('submitBar');
    const selectedCount = document.getElementById('selectedCount');
    const submitButton = document.getElementById('submitDprButton');
    const existingNotice = document.getElementById('existingDprNotice');

    const config = {
        labour_attendances: {
            label: 'Labour',
            input: 'labour_attendance_ids[]',
        },
        work_done: {
            label: 'Work Done',
            input: 'work_done_item_ids[]',
        },
        material_received: {
            label: 'Received',
            input: 'material_received_ids[]',
        },
        material_consumed: {
            label: 'Consumed',
            input: 'material_consumed_ids[]',
        },
        material_required: {
            label: 'Required',
            input: 'material_requirement_ids[]',
        },
        site_issues: {
            label: 'Issues',
            input: 'site_issue_ids[]',
        },
        tomorrow_plans: {
            label: 'Plans',
            input: 'tomorrow_plan_ids[]',
        },
    };

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function itemLines(items) {
        if (!Array.isArray(items) || items.length === 0) {
            return '<span class="text-gray-500">No item details</span>';
        }

        return items.map(item => `
            <div class="text-sm text-gray-700">
                <span class="font-semibold">${escapeHtml(item.name)}</span>
                <span class="text-gray-500">
                    — ${escapeHtml(item.quantity)} ${escapeHtml(item.unit)}
                </span>
            </div>
        `).join('');
    }

    function card(inputName, id, title, meta, body = '') {
        return `
            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-white p-4 hover:bg-gray-50">
                <input
                    type="checkbox"
                    name="${inputName}"
                    value="${id}"
                    checked
                    class="execution-checkbox mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                >

                <div class="min-w-0 flex-1">
                    <div class="font-semibold text-gray-800">
                        ${escapeHtml(title)}
                    </div>

                    ${meta ? `
                        <div class="mt-1 text-xs text-gray-500">
                            ${escapeHtml(meta)}
                        </div>
                    ` : ''}

                    ${body ? `
                        <div class="mt-3">
                            ${body}
                        </div>
                    ` : ''}
                </div>
            </label>
        `;
    }

    function renderSection(key, records) {
        const list = document.querySelector(`[data-list="${key}"]`);
        const section = document.querySelector(`[data-section="${key}"]`);

        if (!list || !section) return;

        list.innerHTML = '';

        if (!records || records.length === 0) {
            list.innerHTML = `
                <div class="rounded-lg border border-dashed border-gray-300 px-4 py-7 text-center text-sm text-gray-500">
                    No records found.
                </div>
            `;
            return;
        }

        records.forEach(record => {
            let html = '';

            if (key === 'labour_attendances') {
                html = card(
                    config[key].input,
                    record.id,
                    record.attendance_number,
                    `${record.shift} • ${record.status}`,
                    `
                        <div class="grid grid-cols-3 gap-3 text-sm">
                            <div><span class="font-semibold">${record.total_labour}</span> Labour</div>
                            <div><span class="font-semibold">${record.normal_hours}</span> Normal Hrs</div>
                            <div><span class="font-semibold">${record.ot_hours}</span> OT Hrs</div>
                        </div>
                    `
                );
            }

            if (key === 'work_done') {
                html = card(
                    config[key].input,
                    record.id,
                    record.activity,
                    `${record.location} • ${record.status}`,
                    `
                        <div class="text-sm text-gray-700">
                            <span class="font-semibold">${escapeHtml(record.quantity)} ${escapeHtml(record.unit)}</span>
                            ${record.contractor ? ` • ${escapeHtml(record.contractor)}` : ''}
                            ${record.photo_count ? ` • ${record.photo_count} photo(s)` : ''}
                        </div>
                    `
                );
            }

            if (key === 'material_received') {
                html = card(
                    config[key].input,
                    record.id,
                    record.reference,
                    [record.vendor, record.challan ? `Challan ${record.challan}` : null, record.status]
                        .filter(Boolean)
                        .join(' • '),
                    itemLines(record.items)
                );
            }

            if (key === 'material_consumed') {
                html = card(
                    config[key].input,
                    record.id,
                    record.reference,
                    `${record.status}${Number(record.wastage) > 0 ? ` • Wastage ${record.wastage}` : ''}`,
                    itemLines(record.items)
                );
            }

            if (key === 'material_required') {
                html = card(
                    config[key].input,
                    record.id,
                    record.reference,
                    [record.required_date ? `Required ${record.required_date}` : null, record.priority, record.status]
                        .filter(Boolean)
                        .join(' • '),
                    itemLines(record.items)
                );
            }

            if (key === 'site_issues') {
                html = card(
                    config[key].input,
                    record.id,
                    record.title,
                    `${record.type} • ${record.priority} • ${record.status}`,
                    `
                        <div class="text-sm text-gray-700">
                            ${record.responsible ? `Responsible: ${escapeHtml(record.responsible)}` : 'Responsible: -'}
                            ${record.photo_count ? ` • ${record.photo_count} photo(s)` : ''}
                        </div>
                    `
                );
            }

            if (key === 'tomorrow_plans') {
                html = card(
                    config[key].input,
                    record.id,
                    record.activity,
                    [record.planned_date ? `Planned ${record.planned_date}` : null, record.priority, record.status]
                        .filter(Boolean)
                        .join(' • '),
                    `
                        <div class="text-sm text-gray-700">
                            ${escapeHtml(record.quantity)} ${escapeHtml(record.unit)}
                            ${record.planned_labour ? ` • Labour ${escapeHtml(record.planned_labour)}` : ''}
                        </div>
                    `
                );
            }

            list.insertAdjacentHTML('beforeend', html);
        });
    }

    function updateSelectedCount() {
        const count = document.querySelectorAll('.execution-checkbox:checked').length;

        const machineryCount = Array.from(
            document.querySelectorAll('.machinery-select')
        ).filter(select => select.value).length;

        const photoCount =
            document.getElementById('dprPhotos')?.files?.length || 0;

        selectedCount.textContent = String(
            count + machineryCount + photoCount
        );

        submitButton.disabled =
            (count + machineryCount + photoCount) === 0
            || !existingNotice.classList.contains('hidden');

        document.querySelectorAll('[data-section]').forEach(section => {
            const boxes = section.querySelectorAll('.execution-checkbox');
            const checked = section.querySelectorAll('.execution-checkbox:checked');
            const toggle = section.querySelector('.section-toggle');

            if (!toggle || boxes.length === 0) {
                if (toggle) toggle.classList.add('hidden');
                return;
            }

            toggle.classList.remove('hidden');
            toggle.textContent = checked.length === boxes.length
                ? 'Unselect All'
                : 'Select All';
        });
    }

    function renderSummary(counts) {
        summary.innerHTML = '';

        Object.entries(config).forEach(([key, item]) => {
            const value = Number(counts[key] || 0);

            summary.insertAdjacentHTML('beforeend', `
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        ${escapeHtml(item.label)}
                    </div>
                    <div class="mt-1 text-2xl font-bold text-gray-800">
                        ${value}
                    </div>
                </div>
            `);
        });

        summary.classList.remove('hidden');
    }

    async function loadExecution() {
        const projectId = project.value;
        const date = dprDate.value;

        if (!projectId || !date) {
            alert('Select Project and DPR Date first.');
            return;
        }

        loadButton.disabled = true;
        loadButton.textContent = 'Loading...';
        loadStatus.textContent = 'Loading standalone execution records...';

        existingNotice.classList.add('hidden');
        existingNotice.innerHTML = '';

        try {
            const url = new URL(
                form.dataset.executionUrl,
                window.location.origin
            );

            url.searchParams.set('project_id', projectId);
            url.searchParams.set('dpr_date', date);

            const response = await fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                const payload = await response.json().catch(() => ({}));
                throw new Error(
                    payload.message || `Unable to load execution (${response.status})`
                );
            }

            const payload = await response.json();

            renderSummary(payload.counts || {});

            Object.keys(config).forEach(key => {
                renderSection(key, payload[key] || []);
            });

            const total = Object.values(payload.counts || {})
                .reduce((sum, value) => sum + Number(value || 0), 0);

            sections.classList.toggle('hidden', total === 0);
            emptyExecution.classList.toggle('hidden', total !== 0);
            submitBar.classList.remove('hidden');

            if (payload.existing_dpr) {
                existingNotice.innerHTML = `
                    <div class="font-semibold">
                        A DPR already exists for this Project and Date.
                    </div>
                    <div class="mt-1 text-sm">
                        Status: ${escapeHtml(payload.existing_dpr.status)}.
                        Open the existing DPR instead of creating another one.
                    </div>
                    <div class="mt-3">
                        <a href="${escapeHtml(payload.existing_dpr.show_url)}"
                           class="inline-flex rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white">
                            Open Existing DPR
                        </a>
                    </div>
                `;

                existingNotice.classList.remove('hidden');
            }

            loadStatus.textContent = total > 0
                ? `${total} execution record(s) loaded. Review the selections below.`
                : 'No standalone execution records were found.';

            document.querySelectorAll('.execution-checkbox')
                .forEach(box => {
                    box.addEventListener('change', updateSelectedCount);
                });

            updateSelectedCount();

        } catch (error) {
            console.error(error);

            loadStatus.textContent = error.message;
            sections.classList.add('hidden');
            summary.classList.add('hidden');
            submitBar.classList.add('hidden');
            emptyExecution.classList.add('hidden');

        } finally {
            loadButton.disabled = false;
            loadButton.textContent = 'Load Daily Execution';
        }
    }

    loadButton.addEventListener('click', loadExecution);

    project.addEventListener('change', function () {
        sections.classList.add('hidden');
        summary.classList.add('hidden');
        submitBar.classList.add('hidden');
        emptyExecution.classList.add('hidden');
        existingNotice.classList.add('hidden');
        loadStatus.textContent = 'Project changed. Click “Load Daily Execution”.';
    });

    dprDate.addEventListener('change', function () {
        sections.classList.add('hidden');
        summary.classList.add('hidden');
        submitBar.classList.add('hidden');
        emptyExecution.classList.add('hidden');
        existingNotice.classList.add('hidden');
        loadStatus.textContent = 'Date changed. Click “Load Daily Execution”.';
    });

    document.querySelectorAll('.section-toggle')
        .forEach(button => {
            button.addEventListener('click', function () {
                const section = button.closest('[data-section]');
                const boxes = Array.from(
                    section.querySelectorAll('.execution-checkbox')
                );

                if (boxes.length === 0) return;

                const allChecked = boxes.every(box => box.checked);

                boxes.forEach(box => {
                    box.checked = !allChecked;
                });

                updateSelectedCount();
            });
        });


    /*
    |--------------------------------------------------------------------------
    | Machinery / Equipment
    |--------------------------------------------------------------------------
    */

    const machineryRows = document.getElementById('machineryRows');
    const machineryTemplate = document.getElementById('machineryRowTemplate');
    const addMachineryButton = document.getElementById('addMachineryButton');

    function renumberMachinery() {
        document.querySelectorAll('.machinery-row')
            .forEach((row, index) => {
                row.querySelectorAll('[name]')
                    .forEach(field => {
                        field.name = field.name.replace(
                            /machinery\[\d+\]/g,
                            `machinery[${index}]`
                        );
                    });
            });

        const rows = document.querySelectorAll('.machinery-row');

        rows.forEach(row => {
            const button = row.querySelector('.remove-machinery');

            if (button) {
                button.classList.toggle(
                    'hidden',
                    rows.length <= 1
                );
            }
        });

        updateSelectedCount();
    }

    function bindMachineryRow(row) {
        if (!row || row.dataset.bound === '1') return;

        row.dataset.bound = '1';

        row.querySelector('.machinery-select')
            ?.addEventListener(
                'change',
                updateSelectedCount
            );

        row.querySelector('.remove-machinery')
            ?.addEventListener('click', function () {
                const rows =
                    document.querySelectorAll('.machinery-row');

                if (rows.length <= 1) {
                    row.querySelectorAll('input, select')
                        .forEach(field => {
                            if (field.tagName === 'SELECT') {
                                field.selectedIndex = 0;
                            } else if (
                                field.type === 'number'
                                && field.name.includes('[quantity]')
                            ) {
                                field.value = '1';
                            } else {
                                field.value = '';
                            }
                        });

                    updateSelectedCount();
                    return;
                }

                row.remove();
                renumberMachinery();
            });
    }

    document.querySelectorAll('.machinery-row')
        .forEach(bindMachineryRow);

    addMachineryButton?.addEventListener('click', function () {
        const index =
            document.querySelectorAll('.machinery-row').length;

        machineryRows.insertAdjacentHTML(
            'beforeend',
            machineryTemplate.innerHTML.replaceAll(
                '__INDEX__',
                String(index)
            )
        );

        const rows =
            document.querySelectorAll('.machinery-row');

        bindMachineryRow(
            rows[rows.length - 1]
        );

        renumberMachinery();
    });

    renumberMachinery();

    /*
    |--------------------------------------------------------------------------
    | DPR Photo Preview
    |--------------------------------------------------------------------------
    */

    const dprPhotos = document.getElementById('dprPhotos');
    const dprPhotoPreview = document.getElementById('dprPhotoPreview');

    dprPhotos?.addEventListener('change', function () {
        dprPhotoPreview.innerHTML = '';

        const files =
            Array.from(this.files || []);

        const oversized =
            files.find(
                file =>
                    file.size > 10 * 1024 * 1024
            );

        if (oversized) {
            alert(
                `"${oversized.name}" is larger than 10 MB.`
            );

            this.value = '';
            updateSelectedCount();
            return;
        }

        files.forEach(file => {
            const url =
                URL.createObjectURL(file);

            const card =
                document.createElement('div');

            card.className =
                'overflow-hidden rounded-lg border border-gray-200 bg-white';

            const image =
                document.createElement('img');

            image.src = url;
            image.alt = file.name;
            image.className =
                'h-28 w-full object-cover';

            image.onload = function () {
                URL.revokeObjectURL(url);
            };

            const meta =
                document.createElement('div');

            meta.className =
                'p-2 text-xs text-gray-600';

            meta.textContent =
                `${file.name} • ${(file.size / 1024 / 1024).toFixed(2)} MB`;

            card.append(
                image,
                meta
            );

            dprPhotoPreview.appendChild(
                card
            );
        });

        submitBar.classList.remove('hidden');
        updateSelectedCount();
    });

    if (project.value && dprDate.value) {
        loadExecution();
    }
});
</script>

@endsection
