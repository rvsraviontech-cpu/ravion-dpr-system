@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-base text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:py-2 sm:text-sm';
    $labelClass = 'mb-1 block text-sm font-semibold text-gray-700';
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">
                Create Daily Progress Report
            </h1>

            <p class="mt-1 text-gray-500">
                Select the Project and Date. Ravion will load the execution records already entered during the day.
            </p>
        </div>

        <a href="{{ route('dprs.index') }}"
           class="inline-flex w-full items-center justify-center rounded-lg bg-gray-600 px-5 py-3 font-semibold text-white hover:bg-gray-700 sm:w-auto sm:py-2.5">
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

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

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
                        class="w-full rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 sm:w-auto sm:py-2.5">
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
             class="mt-6 hidden grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-9">
        </div>

        <div id="executionSections"
             class="mt-6 hidden space-y-6">

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                 data-section="labour_attendances"
                 data-open="0">

                <div class="bg-[#0F2A52] px-4 py-3 text-white">
                    <div class="flex items-center justify-between gap-3">
                        <button type="button"
                                class="section-collapse flex min-w-0 flex-1 items-center justify-between gap-3 text-left"
                                aria-expanded="false">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h2 class="truncate text-base font-bold sm:text-lg">Labour Attendance</h2>
                                    <span class="section-count rounded-full bg-white/15 px-2 py-0.5 text-[11px] font-bold text-white">0</span>
                                </div>
                                <p class="mt-1 line-clamp-1 text-xs text-blue-100">Daily attendance sheets already recorded for the Project and Date.</p>
                            </div>

                            <span class="section-chevron shrink-0 text-lg transition-transform">⌄</span>
                        </button>
                    </div>
                </div>

                <div class="section-body hidden">
                    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-2.5">
                        <div class="text-xs font-semibold text-gray-600">
                            <span class="section-selected-count">0</span> selected
                        </div>

                        <button type="button"
                                class="section-toggle text-xs font-semibold text-blue-700">
                            Unselect All
                        </button>
                    </div>

                    <div class="space-y-3 p-3 sm:p-5" data-list="labour_attendances"></div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                 data-section="work_done"
                 data-open="0">

                <div class="bg-[#0F2A52] px-4 py-3 text-white">
                    <div class="flex items-center justify-between gap-3">
                        <button type="button"
                                class="section-collapse flex min-w-0 flex-1 items-center justify-between gap-3 text-left"
                                aria-expanded="false">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h2 class="truncate text-base font-bold sm:text-lg">Work Done</h2>
                                    <span class="section-count rounded-full bg-white/15 px-2 py-0.5 text-[11px] font-bold text-white">0</span>
                                </div>
                                <p class="mt-1 line-clamp-1 text-xs text-blue-100">Physical work activities recorded during the day.</p>
                            </div>

                            <span class="section-chevron shrink-0 text-lg transition-transform">⌄</span>
                        </button>
                    </div>
                </div>

                <div class="section-body hidden">
                    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-2.5">
                        <div class="text-xs font-semibold text-gray-600">
                            <span class="section-selected-count">0</span> selected
                        </div>

                        <button type="button"
                                class="section-toggle text-xs font-semibold text-blue-700">
                            Unselect All
                        </button>
                    </div>

                    <div class="space-y-3 p-3 sm:p-5" data-list="work_done"></div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                 data-section="material_received"
                 data-open="0">

                <div class="bg-[#0F2A52] px-4 py-3 text-white">
                    <div class="flex items-center justify-between gap-3">
                        <button type="button"
                                class="section-collapse flex min-w-0 flex-1 items-center justify-between gap-3 text-left"
                                aria-expanded="false">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h2 class="truncate text-base font-bold sm:text-lg">Material Received</h2>
                                    <span class="section-count rounded-full bg-white/15 px-2 py-0.5 text-[11px] font-bold text-white">0</span>
                                </div>
                                <p class="mt-1 line-clamp-1 text-xs text-blue-100">Material receipts entered for the day.</p>
                            </div>

                            <span class="section-chevron shrink-0 text-lg transition-transform">⌄</span>
                        </button>
                    </div>
                </div>

                <div class="section-body hidden">
                    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-2.5">
                        <div class="text-xs font-semibold text-gray-600">
                            <span class="section-selected-count">0</span> selected
                        </div>

                        <button type="button"
                                class="section-toggle text-xs font-semibold text-blue-700">
                            Unselect All
                        </button>
                    </div>

                    <div class="space-y-3 p-3 sm:p-5" data-list="material_received"></div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                 data-section="material_consumed"
                 data-open="0">

                <div class="bg-[#0F2A52] px-4 py-3 text-white">
                    <div class="flex items-center justify-between gap-3">
                        <button type="button"
                                class="section-collapse flex min-w-0 flex-1 items-center justify-between gap-3 text-left"
                                aria-expanded="false">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h2 class="truncate text-base font-bold sm:text-lg">Material Consumed</h2>
                                    <span class="section-count rounded-full bg-white/15 px-2 py-0.5 text-[11px] font-bold text-white">0</span>
                                </div>
                                <p class="mt-1 line-clamp-1 text-xs text-blue-100">Material consumption already recorded for this Project and Date.</p>
                            </div>

                            <span class="section-chevron shrink-0 text-lg transition-transform">⌄</span>
                        </button>
                    </div>
                </div>

                <div class="section-body hidden">
                    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-2.5">
                        <div class="text-xs font-semibold text-gray-600">
                            <span class="section-selected-count">0</span> selected
                        </div>

                        <button type="button"
                                class="section-toggle text-xs font-semibold text-blue-700">
                            Unselect All
                        </button>
                    </div>

                    <div class="space-y-3 p-3 sm:p-5" data-list="material_consumed"></div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                 data-section="material_required"
                 data-open="0">

                <div class="bg-[#0F2A52] px-4 py-3 text-white">
                    <div class="flex items-center justify-between gap-3">
                        <button type="button"
                                class="section-collapse flex min-w-0 flex-1 items-center justify-between gap-3 text-left"
                                aria-expanded="false">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h2 class="truncate text-base font-bold sm:text-lg">Material Required</h2>
                                    <span class="section-count rounded-full bg-white/15 px-2 py-0.5 text-[11px] font-bold text-white">0</span>
                                </div>
                                <p class="mt-1 line-clamp-1 text-xs text-blue-100">Requirements created during this DPR day, normally for upcoming work.</p>
                            </div>

                            <span class="section-chevron shrink-0 text-lg transition-transform">⌄</span>
                        </button>
                    </div>
                </div>

                <div class="section-body hidden">
                    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-2.5">
                        <div class="text-xs font-semibold text-gray-600">
                            <span class="section-selected-count">0</span> selected
                        </div>

                        <button type="button"
                                class="section-toggle text-xs font-semibold text-blue-700">
                            Unselect All
                        </button>
                    </div>

                    <div class="space-y-3 p-3 sm:p-5" data-list="material_required"></div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                 data-section="site_issues"
                 data-open="0">

                <div class="bg-[#0F2A52] px-4 py-3 text-white">
                    <div class="flex items-center justify-between gap-3">
                        <button type="button"
                                class="section-collapse flex min-w-0 flex-1 items-center justify-between gap-3 text-left"
                                aria-expanded="false">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h2 class="truncate text-base font-bold sm:text-lg">Site Issues</h2>
                                    <span class="section-count rounded-full bg-white/15 px-2 py-0.5 text-[11px] font-bold text-white">0</span>
                                </div>
                                <p class="mt-1 line-clamp-1 text-xs text-blue-100">Issues and delays reported during the selected day.</p>
                            </div>

                            <span class="section-chevron shrink-0 text-lg transition-transform">⌄</span>
                        </button>
                    </div>
                </div>

                <div class="section-body hidden">
                    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-2.5">
                        <div class="text-xs font-semibold text-gray-600">
                            <span class="section-selected-count">0</span> selected
                        </div>

                        <button type="button"
                                class="section-toggle text-xs font-semibold text-blue-700">
                            Unselect All
                        </button>
                    </div>

                    <div class="space-y-3 p-3 sm:p-5" data-list="site_issues"></div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                 data-section="tomorrow_plans"
                 data-open="0">

                <div class="bg-[#0F2A52] px-4 py-3 text-white">
                    <div class="flex items-center justify-between gap-3">
                        <button type="button"
                                class="section-collapse flex min-w-0 flex-1 items-center justify-between gap-3 text-left"
                                aria-expanded="false">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h2 class="truncate text-base font-bold sm:text-lg">Tomorrow Plan</h2>
                                    <span class="section-count rounded-full bg-white/15 px-2 py-0.5 text-[11px] font-bold text-white">0</span>
                                </div>
                                <p class="mt-1 line-clamp-1 text-xs text-blue-100">Planning entries created during the DPR day for upcoming execution.</p>
                            </div>

                            <span class="section-chevron shrink-0 text-lg transition-transform">⌄</span>
                        </button>
                    </div>
                </div>

                <div class="section-body hidden">
                    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-2.5">
                        <div class="text-xs font-semibold text-gray-600">
                            <span class="section-selected-count">0</span> selected
                        </div>

                        <button type="button"
                                class="section-toggle text-xs font-semibold text-blue-700">
                            Unselect All
                        </button>
                    </div>

                    <div class="space-y-3 p-3 sm:p-5" data-list="tomorrow_plans"></div>
                </div>
            </div>

            {{-- Machinery / Equipment Used --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                 data-section="machinery_daily_usages"
                 data-open="0">

                <div class="bg-[#0F2A52] px-4 py-3 text-white">
                    <div class="flex items-center justify-between gap-3">
                        <button type="button"
                                class="section-collapse flex min-w-0 flex-1 items-center justify-between gap-3 text-left"
                                aria-expanded="false">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h2 class="truncate text-base font-bold sm:text-lg">Machinery &amp; Equipment Used</h2>
                                    <span class="section-count rounded-full bg-white/15 px-2 py-0.5 text-[11px] font-bold text-white">0</span>
                                </div>
                                <p class="mt-1 line-clamp-1 text-xs text-blue-100">Machinery usage already recorded for the selected Project and Date.</p>
                            </div>

                            <span class="section-chevron shrink-0 text-lg transition-transform">⌄</span>
                        </button>
                    </div>
                </div>

                <div class="section-body hidden">
                    <div class="flex flex-col gap-3 border-b border-gray-200 bg-gray-50 px-4 py-2.5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-xs font-semibold text-gray-600">
                            <span class="section-selected-count">0</span> selected
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="button"
                                    class="section-toggle text-xs font-semibold text-blue-700">
                                Unselect All
                            </button>

                            <a href="{{ route('machinery-daily-usages.create') }}"
                               class="rounded-lg bg-green-600 px-3 py-2 text-xs font-semibold text-white hover:bg-green-700">
                                + Add Machinery Usage
                            </a>
                        </div>
                    </div>

                    <div class="space-y-3 p-3 sm:p-5" data-list="machinery_daily_usages"></div>
                </div>
            </div>

            {{-- Site Photos --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                 data-section="site_photo_entries"
                 data-open="0">

                <div class="bg-[#0F2A52] px-4 py-3 text-white">
                    <div class="flex items-center justify-between gap-3">
                        <button type="button"
                                class="section-collapse flex min-w-0 flex-1 items-center justify-between gap-3 text-left"
                                aria-expanded="false">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h2 class="truncate text-base font-bold sm:text-lg">Site Photos</h2>
                                    <span class="section-count rounded-full bg-white/15 px-2 py-0.5 text-[11px] font-bold text-white">0</span>
                                </div>
                                <p class="mt-1 line-clamp-1 text-xs text-blue-100">Site photos already recorded for the selected Project and Date.</p>
                            </div>

                            <span class="section-chevron shrink-0 text-lg transition-transform">⌄</span>
                        </button>
                    </div>
                </div>

                <div class="section-body hidden">
                    <div class="flex flex-col gap-3 border-b border-gray-200 bg-gray-50 px-4 py-2.5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-xs font-semibold text-gray-600">
                            <span class="section-selected-count">0</span> selected
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="button"
                                    class="section-toggle text-xs font-semibold text-blue-700">
                                Unselect All
                            </button>

                            <a href="{{ route('site-photos.create') }}"
                               class="rounded-lg bg-green-600 px-3 py-2 text-xs font-semibold text-white hover:bg-green-700">
                                + Add Site Photos
                            </a>
                        </div>
                    </div>

                    <div class="space-y-3 p-3 sm:p-5" data-list="site_photo_entries"></div>
                </div>
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


        <div id="submitBar"
             class="sticky bottom-[68px] z-30 mt-6 hidden border-t border-gray-200 bg-white/95 p-4 shadow-lg backdrop-blur lg:bottom-0">

            <div class="mx-auto flex max-w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                <div class="text-sm text-gray-600">
                    Selected DPR items:
                    <span id="selectedCount" class="font-bold text-gray-900">0</span>
                </div>

                <button type="submit"
                        id="submitDprButton"
                        class="w-full rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto sm:px-8"
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
        machinery_daily_usages: {
            label: 'Machinery',
            input: 'machinery_daily_usage_ids[]',
        },
        site_photo_entries: {
            label: 'Site Photos',
            input: 'site_photo_entry_ids[]',
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

        const countBadge = section.querySelector('.section-count');
        if (countBadge) {
            countBadge.textContent = String(records?.length || 0);
        }

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

            if (key === 'machinery_daily_usages') {
                const machineryMeta = [
                    record.location && record.location !== '-' ? record.location : null,
                    record.activity,
                    record.status,
                ].filter(Boolean).join(' • ');

                html = card(
                    config[key].input,
                    record.id,
                    record.equipment,
                    machineryMeta,
                    `
                        <div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-3 xl:grid-cols-6">
                            <div>
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Qty</div>
                                <div class="font-semibold text-gray-800">${escapeHtml(record.quantity)} ${escapeHtml(record.unit)}</div>
                            </div>
                            <div>
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Site Hrs</div>
                                <div class="font-semibold text-gray-800">${escapeHtml(record.site_hours ?? '-')}</div>
                            </div>
                            <div>
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Operating Hrs</div>
                                <div class="font-semibold text-gray-800">${escapeHtml(record.operating_hours ?? '-')}</div>
                            </div>
                            <div>
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Idle Hrs</div>
                                <div class="font-semibold text-gray-800">${escapeHtml(record.idle_hours ?? '-')}</div>
                            </div>
                            <div>
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Breakdown Hrs</div>
                                <div class="font-semibold text-gray-800">${escapeHtml(record.breakdown_hours ?? '-')}</div>
                            </div>
                            <div>
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Condition</div>
                                <div class="font-semibold text-gray-800">${escapeHtml(record.working_condition ?? '-')}</div>
                            </div>
                        </div>

                        ${(record.operator || record.remarks) ? `
                            <div class="mt-3 text-xs text-gray-600">
                                ${record.operator ? `<span class="font-semibold">Operator:</span> ${escapeHtml(record.operator)}` : ''}
                                ${record.operator && record.remarks ? ' • ' : ''}
                                ${record.remarks ? `<span class="font-semibold">Remarks:</span> ${escapeHtml(record.remarks)}` : ''}
                            </div>
                        ` : ''}
                    `
                );
            }

            if (key === 'site_photo_entries') {
                const photoMeta = [
                    record.category,
                    record.activity,
                    record.location && record.location !== '-' ? record.location : null,
                    record.status,
                ].filter(Boolean).join(' • ');

                const photos = Array.isArray(record.photos) ? record.photos : [];

                const photoGrid = photos.length > 0
                    ? `
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                            ${photos.map(photo => `
                                <div class="overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                                    <img src="${escapeHtml(photo.url)}"
                                         alt="${escapeHtml(photo.caption || photo.photo_type || 'Site photo')}"
                                         loading="lazy"
                                         class="h-28 w-full object-cover">
                                    <div class="p-2">
                                        <div class="text-[11px] font-semibold text-gray-700">
                                            ${escapeHtml(photo.photo_type || 'Photo')}
                                        </div>
                                        ${photo.caption ? `
                                            <div class="mt-1 line-clamp-2 text-[11px] text-gray-500">
                                                ${escapeHtml(photo.caption)}
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `
                    : '<div class="text-sm text-gray-500">No photo files found.</div>';

                html = card(
                    config[key].input,
                    record.id,
                    record.title,
                    photoMeta,
                    `
                        <div class="mb-3 text-sm text-gray-700">
                            <span class="font-semibold">${escapeHtml(record.photo_count || 0)}</span> photo(s)
                            ${record.remarks ? ` • ${escapeHtml(record.remarks)}` : ''}
                        </div>
                        ${photoGrid}
                    `
                );
            }

            list.insertAdjacentHTML('beforeend', html);
        });
    }

    function updateSelectedCount() {
        const count = document.querySelectorAll('.execution-checkbox:checked').length;

        selectedCount.textContent = String(count);

        submitButton.disabled =
            count === 0
            || !existingNotice.classList.contains('hidden');

        document.querySelectorAll('[data-section]').forEach(section => {
            const boxes = section.querySelectorAll('.execution-checkbox');
            const checked = section.querySelectorAll('.execution-checkbox:checked');
            const toggle = section.querySelector('.section-toggle');

            const selectedLabel = section.querySelector('.section-selected-count');

            if (selectedLabel) {
                selectedLabel.textContent = String(checked.length);
            }

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

            document.querySelectorAll('[data-section]').forEach(section => {
                const count = Number(section.querySelector('.section-count')?.textContent || 0);
                const body = section.querySelector('.section-body');
                const collapse = section.querySelector('.section-collapse');
                const chevron = section.querySelector('.section-chevron');

                // Keep sections collapsed by default on mobile.
                // On desktop, open sections that actually contain records.
                const shouldOpen = window.matchMedia('(min-width: 1024px)').matches && count > 0;

                section.dataset.open = shouldOpen ? '1' : '0';
                body?.classList.toggle('hidden', !shouldOpen);
                collapse?.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
                chevron?.classList.toggle('rotate-180', shouldOpen);
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

    document.querySelectorAll('.section-collapse')
        .forEach(button => {
            button.addEventListener('click', function () {
                const section = button.closest('[data-section]');
                const body = section?.querySelector('.section-body');
                const chevron = section?.querySelector('.section-chevron');

                if (!section || !body) return;

                const isOpen = section.dataset.open === '1';
                section.dataset.open = isOpen ? '0' : '1';
                body.classList.toggle('hidden', isOpen);
                button.setAttribute('aria-expanded', isOpen ? 'false' : 'true');

                if (chevron) {
                    chevron.classList.toggle('rotate-180', !isOpen);
                }
            });
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


    if (project.value && dprDate.value) {
        loadExecution();
    }
});
</script>

@endsection
