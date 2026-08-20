@php
    $editingAttendance = isset($labourAttendance);
    $selectedProjectId = old('project_id', $labourAttendance->project_id ?? '');
    $selectedShiftId = old('shift_id', $labourAttendance->shift_id ?? '');
    $attendanceDate = old(
        'attendance_date',
        $editingAttendance && $labourAttendance->attendance_date
            ? $labourAttendance->attendance_date->format('Y-m-d')
            : now()->format('Y-m-d')
    );
    $lockedShift = $editingAttendance
        ? $shifts->firstWhere('id', $labourAttendance->shift_id)
        : null;

    $existingDetails = collect(old('details', []));

    if ($existingDetails->isEmpty() && $editingAttendance && $labourAttendance->relationLoaded('details')) {
        $existingDetails = $labourAttendance->details->map(fn ($detail) => [
            'labour_id' => $detail->labour_id,
            'attendance_status_id' => $detail->attendance_status_id,
            'working_status_id' => $detail->working_status_id,
            'check_in_time' => $detail->check_in_time ? substr((string) $detail->check_in_time, 0, 5) : '',
            'check_out_time' => $detail->check_out_time ? substr((string) $detail->check_out_time, 0, 5) : '',
            'normal_hours' => $detail->normal_hours ?? 0,
            'ot_hours' => $detail->ot_hours ?? 0,
            'attendance_source' => $detail->attendance_source ?? 'manual',
            'remarks' => $detail->remarks ?? '',
        ]);
    }

    $attendanceStatusPayload = $attendanceStatuses->map(fn ($status) => [
        'id' => $status->id,
        'code' => strtoupper(trim((string) $status->code)),
        'name' => $status->name,
        'short_name' => $status->short_name,
        'counts_as_present' => (bool) $status->counts_as_present,
        'counts_as_absent' => (bool) $status->counts_as_absent,
        'allows_normal_hours' => (bool) $status->allows_normal_hours,
        'allows_ot_hours' => (bool) $status->allows_ot_hours,
        'requires_working_status' => (bool) ($status->requires_working_status ?? false),
    ])->values();



    $workingStatusPayload = $workingStatuses->map(fn ($status) => [
        'id' => $status->id,
        'code' => strtoupper(trim((string) ($status->code ?? ''))),
        'name' => $status->name,
        'requires_reason' => (bool) ($status->requires_reason ?? false),
    ])->values();

    $existingDetailPayload = $existingDetails->values()->map(fn ($detail) => [
        'labour_id' => (int) ($detail['labour_id'] ?? 0),
        'attendance_status_id' => (int) ($detail['attendance_status_id'] ?? 0),
        'working_status_id' => ! empty($detail['working_status_id']) ? (int) $detail['working_status_id'] : null,
        'check_in_time' => $detail['check_in_time'] ?? '',
        'check_out_time' => $detail['check_out_time'] ?? '',
        'normal_hours' => $detail['normal_hours'] ?? 0,
        'ot_hours' => $detail['ot_hours'] ?? 0,
        'attendance_source' => $detail['attendance_source'] ?? 'manual',
        'remarks' => $detail['remarks'] ?? '',
    ]);
@endphp


<style>
@media (max-width: 1023px) {
    .compact-attendance-wrap {
        overflow: visible !important;
    }

    .compact-attendance-wrap table,
    .compact-attendance-wrap tbody {
        display: block;
        width: 100%;
        min-width: 0 !important;
    }

    .compact-attendance-wrap thead,
    .compact-attendance-wrap colgroup {
        display: none;
    }

    .compact-attendance-wrap tr.labour-group-heading {
        display: block;
        margin: 12px 0 6px;
        background: transparent !important;
    }

    .compact-attendance-wrap tr.labour-group-heading td {
        display: block;
        width: 100%;
        border: 0 !important;
        border-radius: 10px;
        background: #0F2A52 !important;
        color: #fff !important;
        padding: 10px 12px !important;
        box-shadow: 0 1px 2px rgba(15, 42, 82, .18);
    }

    .compact-attendance-wrap tr.labour-group-heading td span {
        color: #dbeafe !important;
    }

    .compact-attendance-wrap tr.attendance-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 44px 44px 92px;
        align-items: center;
        gap: 6px;
        width: 100%;
        min-height: 54px;
        padding: 7px 8px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }

    .compact-attendance-wrap tr.attendance-row > td {
        display: block;
        width: auto;
        padding: 0 !important;
        border: 0 !important;
        min-width: 0;
    }

    .compact-attendance-wrap tr.attendance-row > td:nth-child(1) {
        grid-column: 1;
    }

    .compact-attendance-wrap tr.attendance-row > td:nth-child(2) {
        display: none;
    }

    .compact-attendance-wrap tr.attendance-row > td:nth-child(3) {
        grid-column: 2;
    }

    .compact-attendance-wrap tr.attendance-row > td:nth-child(4) {
        grid-column: 3;
    }

    .compact-attendance-wrap tr.attendance-row > td:nth-child(5) {
        grid-column: 4;
    }

    .compact-attendance-wrap tr.attendance-row > td:nth-child(n+6) {
        display: none;
        grid-column: 1 / -1;
        padding-top: 8px !important;
    }

    .compact-attendance-wrap tr.attendance-row.mobile-details-open > td:nth-child(n+6) {
        display: block;
    }

    .compact-attendance-wrap .present-btn,
    .compact-attendance-wrap .absent-btn {
        width: 44px !important;
        height: 40px !important;
        border-radius: 9px !important;
    }

    .compact-attendance-wrap .more-status {
        width: 92px !important;
        min-height: 40px;
        border-radius: 9px;
        padding: 0 6px;
        font-size: 12px;
        font-weight: 700;
    }

    .compact-attendance-wrap tr.attendance-row > td:nth-child(6)::before,
    .compact-attendance-wrap tr.attendance-row > td:nth-child(7)::before,
    .compact-attendance-wrap tr.attendance-row > td:nth-child(8)::before,
    .compact-attendance-wrap tr.attendance-row > td:nth-child(9)::before,
    .compact-attendance-wrap tr.attendance-row > td:nth-child(10)::before,
    .compact-attendance-wrap tr.attendance-row > td:nth-child(11)::before {
        display: block;
        margin-bottom: 4px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #6b7280;
    }

    .compact-attendance-wrap tr.attendance-row > td:nth-child(6)::before { content: "Working Status"; }
    .compact-attendance-wrap tr.attendance-row > td:nth-child(7)::before { content: "Check In"; }
    .compact-attendance-wrap tr.attendance-row > td:nth-child(8)::before { content: "Check Out"; }
    .compact-attendance-wrap tr.attendance-row > td:nth-child(9)::before { content: "Normal Hours"; }
    .compact-attendance-wrap tr.attendance-row > td:nth-child(10)::before { content: "OT Hours"; }
    .compact-attendance-wrap tr.attendance-row > td:nth-child(11)::before { content: "Remarks"; }

    .compact-attendance-wrap .working-status,
    .compact-attendance-wrap .check-in,
    .compact-attendance-wrap .check-out,
    .compact-attendance-wrap .normal,
    .compact-attendance-wrap .ot,
    .compact-attendance-wrap input[type="text"] {
        min-height: 42px;
        font-size: 16px;
    }

    .mobile-details-toggle {
        margin-top: 3px;
        font-size: 10px;
        line-height: 1;
        font-weight: 700;
        color: #0F2A52;
    }
}

@media (min-width: 1024px) {
    .mobile-details-toggle {
        display: none !important;
    }
}
</style>

<div
    id="labour-attendance-form"
    data-editing="{{ $editingAttendance ? '1' : '0' }}"
    data-attendance-id="{{ $labourAttendance->id ?? '' }}"
    data-existing-details='@json($existingDetailPayload)'
    data-attendance-statuses='@json($attendanceStatusPayload)'
    data-working-statuses='@json($workingStatusPayload)'
>
    @if($errors->any())
        <div id="attendance-validation-errors" class="mb-5 rounded-xl border border-red-300 bg-red-50 p-4 text-red-800">
            <p class="font-semibold">Attendance could not be saved. Please correct the following:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div id="attendance-client-error" class="mb-5 hidden rounded-xl border border-red-300 bg-red-50 p-4 text-sm font-medium text-red-800"></div>

    <x-rds.section
        title="Project Details"
        description="{{ $editingAttendance
            ? 'Project, attendance date and shift are locked. Draft/Rejected attendance may still add missed labour before submission.'
            : 'Select the project, attendance date and shift.' }}"
    >
        @if($editingAttendance)
            <input type="hidden" name="project_id" id="project_id" value="{{ $labourAttendance->project_id }}">
            <input type="hidden" name="attendance_date" id="attendance_date" value="{{ $labourAttendance->attendance_date->format('Y-m-d') }}">
            <input
                type="hidden"
                name="shift_id"
                id="shift_id"
                value="{{ $labourAttendance->shift_id ?? '' }}"
                data-start-time="{{ $lockedShift?->start_time ? substr((string) $lockedShift->start_time, 0, 5) : '09:00' }}"
                data-end-time="{{ $lockedShift?->end_time ? substr((string) $lockedShift->end_time, 0, 5) : '18:00' }}"
                data-normal-hours="{{ $lockedShift?->normal_hours ?: 8 }}"
            >

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Project</p>
                    <p class="mt-2 text-sm font-semibold text-gray-900">{{ $labourAttendance->project?->project_name ?? '—' }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Attendance Date</p>
                    <p class="mt-2 text-sm font-semibold text-gray-900">{{ $labourAttendance->attendance_date->format('d M Y') }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Shift</p>
                    <p class="mt-2 text-sm font-semibold text-gray-900">{{ $labourAttendance->shift?->name ?? 'No Common Shift' }}</p>
                </div>
            </div>

            <div class="mt-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                Draft/Rejected attendance can still add missed labour or remove an incorrectly added labour before submission. Submitted/Approved attendance remains locked.
            </div>
        @else
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <x-rds.select name="project_id" label="Project" id="project_id" required>
                    <option value="">Select Project</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" @selected((string) $selectedProjectId === (string) $project->id)>
                            {{ $project->project_name }}
                        </option>
                    @endforeach
                </x-rds.select>

                <x-rds.input
                    name="attendance_date"
                    label="Attendance Date"
                    id="attendance_date"
                    type="date"
                    value="{{ $attendanceDate }}"
                    max="{{ now()->format('Y-m-d') }}"
                    required
                />

                <x-rds.select name="shift_id" label="Shift" id="shift_id">
                    <option value="" data-start-time="09:00" data-end-time="18:00" data-normal-hours="8">
                        No Common Shift — Default 09:00 AM to 06:00 PM
                    </option>
                    @foreach($shifts as $shift)
                        <option
    value="{{ $shift->id }}"
    data-start-time="{{ $shift->start_time_value }}"
    data-end-time="{{ $shift->end_time_value }}"
    data-normal-hours="{{ $shift->normal_hours }}"
    data-ot-start="{{ $shift->ot_start_time_value }}"
    data-grace-in="{{ $shift->grace_in_minutes }}"
    data-grace-out="{{ $shift->grace_out_minutes }}"
    data-crosses-midnight="{{ $shift->crosses_midnight ? 1 : 0 }}"
>
                            {{ $shift->name }}
                        </option>
                    @endforeach
                </x-rds.select>
            </div>
        @endif

        <div class="mt-5">
            <x-rds.textarea
                name="remarks"
                label="Attendance Remarks"
                rows="2"
                value="{{ old('remarks', $labourAttendance->remarks ?? '') }}"
                placeholder="General notes about this attendance sheet"
            />
        </div>
    </x-rds.section>

    <x-rds.section
        title="Labour Attendance"
        description="{{ $editingAttendance
            ? 'Previously saved attendance is retained. Assigned, unassigned and eligible other-project labour are shown.'
            : 'Assigned labour is shown first. Unassigned and other-project labour can be opened when needed.' }}"
    >
        <div id="labour-pool-summary" class="mb-5 hidden grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-green-700">Assigned Labour</p>
                <p id="pool-assigned-count" class="mt-2 text-2xl font-bold text-green-800">0</p>
            </div>
            <button type="button" id="open-unassigned-labour"
                class="group rounded-xl border border-blue-200 bg-blue-50 p-4 text-left transition hover:border-blue-300 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                aria-controls="unassigned-section" aria-expanded="false">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Unassigned Labour</p>
                        <p id="pool-unassigned-count" class="mt-2 text-2xl font-bold text-blue-800">0</p>
                    </div>
                    <span class="inline-flex items-center rounded-lg border border-blue-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-blue-700 shadow-sm group-hover:bg-blue-50">
                        <span id="open-unassigned-labour-label">View Labour</span>
                        <svg id="open-unassigned-labour-icon" class="ml-1 h-4 w-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </span>
                </div>
                <p class="mt-2 text-xs text-blue-700">Click to view labour not currently assigned to a project.</p>
            </button>
            <button type="button" id="open-other-project-labour"
                class="group rounded-xl border border-amber-200 bg-amber-50 p-4 text-left transition hover:border-amber-300 hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                aria-controls="other-project-section" aria-expanded="false">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Other Project Labour</p>
                        <p id="pool-other-project-count" class="mt-2 text-2xl font-bold text-amber-800">0</p>
                    </div>
                    <span class="inline-flex items-center rounded-lg border border-amber-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-amber-700 shadow-sm group-hover:bg-amber-50">
                        <span id="open-other-project-labour-label">View Labour</span>
                        <svg id="open-other-project-labour-icon" class="ml-1 h-4 w-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </span>
                </div>
                <p class="mt-2 text-xs text-amber-700">Temporarily use labour normally assigned to another project.</p>
            </button>

            <div class="rounded-xl border border-violet-200 bg-violet-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-violet-700">Total Available</p>
                <p id="pool-total-count" class="mt-2 text-2xl font-bold text-violet-800">0</p>
            </div>
        </div>

        <div id="attendance-live-summary" class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-8">
            @foreach([
                ['id' => 'summary-present', 'label' => 'Present'],
                ['id' => 'summary-absent', 'label' => 'Absent'],
                ['id' => 'summary-half-day', 'label' => 'Half Day'],
                ['id' => 'summary-leave', 'label' => 'Leave'],
                ['id' => 'summary-working', 'label' => 'Working'],
                ['id' => 'summary-idle', 'label' => 'Idle'],
                ['id' => 'summary-normal', 'label' => 'Normal Hours'],
                ['id' => 'summary-ot', 'label' => 'OT Hours'],
            ] as $summary)
                <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm {{ in_array($summary['id'], ['summary-working', 'summary-idle', 'summary-normal', 'summary-ot'], true) ? 'hidden sm:block' : '' }}">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ $summary['label'] }}</p>
                    <p id="{{ $summary['id'] }}" class="mt-1 text-xl font-bold text-gray-900">0</p>
                </div>
            @endforeach
        </div>

        <div class="mb-5 flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
            <div class="w-full max-w-md">
                <label for="labour-table-search" class="mb-1 block text-sm font-medium text-gray-700">Search Labour</label>
                <input
                    type="text"
                    id="labour-table-search"
                    placeholder="Search by name or designation..."
                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-base shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:py-2 sm:text-sm"
                >
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" id="mark-filtered-present" class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg border border-green-300 bg-green-50 px-4 py-2 text-sm font-semibold text-green-700 shadow-sm transition hover:bg-green-100">
                    Mark Filtered Present
                </button>
                <button type="button" id="mark-filtered-absent" class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg border border-red-300 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 shadow-sm transition hover:bg-red-100">
                    Mark Filtered Absent
                </button>
                <button type="button" id="logout-filtered-present" class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg border border-blue-300 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm transition hover:bg-blue-100">
                    Logout Filtered Present
                </button>
            </div>
        </div>

        <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-xs text-blue-800 lg:hidden">
            Fast entry: use <strong>Mark Assigned Present</strong>, then change only absentees or special statuses. Tap <strong>Details</strong> only when hours, OT, check-in/out or remarks need adjustment.
        </div>

        <div id="labour-loading-message" class="hidden rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">Loading labour profiles...</div>
        <div id="labour-error-message" class="hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>
        <div id="labour-empty-message" class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-10 text-center">
            <p class="text-sm font-semibold text-gray-700">{{ $editingAttendance ? 'Loading existing attendance rows...' : 'Select a project and attendance date to load labour.' }}</p>
        </div>

        <div id="attendance-groups" class="hidden space-y-5">
            @foreach([
                ['id' => 'assigned', 'title' => 'Assigned Labour', 'description' => 'Labour currently assigned to this project', 'border' => 'border-blue-200', 'head' => 'border-blue-200 bg-blue-50', 'text' => 'text-blue-900'],
                ['id' => 'unassigned', 'title' => 'Unassigned Labour', 'description' => 'Labour not assigned to any project', 'border' => 'border-green-200', 'head' => 'border-green-200 bg-green-50', 'text' => 'text-green-900'],
                ['id' => 'other-project', 'title' => 'Other Project Labour', 'description' => 'Labour normally assigned to another project but available on this date', 'border' => 'border-amber-200', 'head' => 'border-amber-200 bg-amber-50', 'text' => 'text-amber-900'],
            ] as $group)
                <div
                    id="{{ $group['id'] }}-section"
                    class="overflow-hidden rounded-xl border {{ $group['border'] }}"
                >
                    <div class="border-b px-4 py-3 {{ $group['head'] }}">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <h3 class="font-semibold {{ $group['text'] }}">
                                            {{ $group['title'] }}
                                            (<span id="{{ $group['id'] }}-visible-count">0</span>)
                                        </h3>
                                        <p class="text-xs text-gray-600">{{ $group['description'] }}</p>
                                    </div>

                                    @if($group['id'] === 'unassigned')
                                        <button type="button" id="toggle-unassigned-labour"
                                            class="inline-flex items-center justify-center rounded-lg border border-green-300 bg-white px-3 py-2 text-xs font-semibold text-green-700 shadow-sm transition hover:bg-green-50"
                                            aria-controls="unassigned-labour-content" aria-expanded="false">
                                            <span id="toggle-unassigned-labour-label">Show Labour</span>
                                            <svg id="toggle-unassigned-labour-icon" class="ml-1 h-4 w-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                    @elseif($group['id'] === 'other-project')
                                        <button type="button" id="toggle-other-project-labour"
                                            class="inline-flex items-center justify-center rounded-lg border border-amber-300 bg-white px-3 py-2 text-xs font-semibold text-amber-700 shadow-sm transition hover:bg-amber-50"
                                            aria-controls="other-project-labour-content" aria-expanded="false">
                                            <span id="toggle-other-project-labour-label">Show Labour</span>
                                            <svg id="toggle-other-project-labour-icon" class="ml-1 h-4 w-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            @if($group['id'] === 'assigned')
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" id="mark-assigned-present" class="w-full sm:w-auto rounded-lg border border-green-300 bg-white px-3 py-2 text-xs font-semibold text-green-700 hover:bg-green-50">
                                        Mark Assigned Present
                                    </button>
                                    <button type="button" id="mark-assigned-absent" class="w-full sm:w-auto rounded-lg border border-red-300 bg-white px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">
                                        Mark Assigned Absent
                                    </button>
                                    <button type="button" id="logout-assigned-present" class="w-full sm:w-auto rounded-lg border border-blue-300 bg-white px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50">
                                        Logout Assigned Present
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                    @if($group['id'] === 'unassigned')
                        <div id="unassigned-labour-content"
                            class="compact-attendance-wrap hidden overflow-x-auto lg:max-h-[680px] lg:overflow-y-auto">
                    @elseif($group['id'] === 'other-project')
                        <div id="other-project-labour-content"
                            class="compact-attendance-wrap hidden overflow-x-auto lg:max-h-[680px] lg:overflow-y-auto">
                    @else
                        <div class="compact-attendance-wrap overflow-x-auto">
                    @endif
                        <table class="w-full min-w-0 table-fixed lg:min-w-[1500px]">
                            <colgroup>
                                <col class="w-[205px]"><col class="w-[170px]"><col class="w-[58px]"><col class="w-[58px]"><col class="w-[150px]">
                                <col class="w-[160px]"><col class="w-[130px]"><col class="w-[130px]"><col class="w-[100px]"><col class="w-[90px]"><col class="w-[220px]">
                            </colgroup>
                            <thead class="sticky top-0 z-10 bg-gray-50 shadow-sm">
                                <tr>
                                    @foreach(['Name', 'Designation', 'P', 'A', 'Status', 'Working Status', 'Check In', 'Check Out', 'Normal', 'OT', 'Remarks'] as $heading)
                                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">{{ $heading }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody id="{{ $group['id'] }}-rows" class="divide-y divide-gray-100 bg-white"></tbody>
                        </table>
                    </div>

                    @if($group['id'] === 'unassigned')
                        <div id="unassigned-labour-footer" class="hidden border-t border-green-200 bg-green-50 px-4 py-3">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-xs text-green-800">Mark only the unassigned labour actually working on this project.</p>
                                <button type="button" data-attendance-submit
                                    class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto sm:py-2.5">
                                    {{ $editingAttendance ? 'Update Attendance' : 'Save Attendance' }}
                                </button>
                            </div>
                        </div>
                    @endif

                    @if($group['id'] === 'other-project')
                        <div id="other-project-labour-footer" class="hidden border-t border-amber-200 bg-amber-50 px-4 py-3">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-xs text-amber-800">
                                    Temporary attendance here does not change the labourer's default project assignment.
                                </p>
                                <button type="button" data-attendance-submit
                                    class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto sm:py-2.5">
                                    {{ $editingAttendance ? 'Update Attendance' : 'Save Attendance' }}
                                </button>
                            </div>
                        </div>
                    @endif

                    @if($group['id'] === 'assigned')
                        <div class="flex justify-end border-t border-blue-200 bg-blue-50 px-4 py-3">
                            <button
                                type="button"
                                data-attendance-submit
                                class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto sm:py-2.5"
                            >
                                {{ $editingAttendance ? 'Update Attendance' : 'Save Attendance' }}
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-xs text-blue-800">
            P = Present, A = Absent. Status and Working Status are independent. Leave, Weekly Off, Holiday and Absent clear Working Status and hours.
        </div>

        @error('details')
            <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </x-rds.section>
</div>

@push('scripts')
<script>
(function () {
    function initAttendance() {
        const root = document.getElementById('labour-attendance-form');
        if (!root || root.dataset.initialized === '1') return;
        root.dataset.initialized = '1';

        const $ = (id) => document.getElementById(id);
        const project = $('project_id');
        const date = $('attendance_date');
        const shift = $('shift_id');
        const assignedRows = $('assigned-rows');
        const unassignedRows = $('unassigned-rows');
        const otherProjectRows = $('other-project-rows');
        const statuses = JSON.parse(root.dataset.attendanceStatuses || '[]');
        const workingStatuses = JSON.parse(root.dataset.workingStatuses || '[]');
        const existing = JSON.parse(root.dataset.existingDetails || '[]');
        const editing = root.dataset.editing === '1';
        const attendanceId = root.dataset.attendanceId || '';
        const attendanceForm =
            root.closest('form')
            || project?.form
            || Array.from(document.forms).find((form) => {
                const action = String(form.getAttribute('action') || '');
                return action.includes('labour-attendances');
            })
            || null;

        let rows = [];
        let requestNo = 0;

        const esc = (value) => String(value ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
        const code = (value) => String(value || '').trim().toUpperCase();
        const byId = (id) => statuses.find((status) => String(status.id) === String(id)) || null;
        const workingById = (id) => workingStatuses.find((status) => String(status.id) === String(id)) || null;
        const workingCode = (status) => code(status?.code || status?.name);
        const defaultWorking = () => workingStatuses.find((status) => ['WORKING', 'W'].includes(workingCode(status))) || workingStatuses.find((status) => workingCode(status).includes('WORKING') && !workingCode(status).includes('PART')) || null;
        const partialWorking = () => workingStatuses.find((status) => ['PARTIALLY_WORKING', 'PARTIALLY WORKING', 'PARTIAL'].includes(workingCode(status))) || workingStatuses.find((status) => workingCode(status).includes('PART')) || null;
        const present = () => statuses.find((status) => ['P', 'PRESENT'].includes(code(status.code))) || statuses.find((status) => status.counts_as_present) || null;
        const absent = () => statuses.find((status) => ['A', 'ABSENT'].includes(code(status.code))) || statuses.find((status) => status.counts_as_absent) || null;
        const isPresent = (status) => Boolean(status && (['P', 'PRESENT'].includes(code(status.code)) || status.counts_as_present));
        const isAbsent = (status) => Boolean(status && (['A', 'ABSENT'].includes(code(status.code)) || status.counts_as_absent));
        const isHalfDay = (status) => Boolean(status && ['HD', 'HALF_DAY', 'HALF-DAY', 'HALF DAY'].includes(code(status.code)));
        const isLeave = (status) => Boolean(status && ['L', 'LEAVE'].includes(code(status.code)));
        const isNonWorking = (status) => !status || isAbsent(status) || ['L', 'LEAVE', 'WO', 'WEEKLY_OFF', 'WEEKLY-OFF', 'H', 'HOLIDAY', 'TR', 'TRANSFERRED'].includes(code(status.code));
        const saved = (labourId) => existing.find((detail) => String(detail.labour_id) === String(labourId)) || null;

        function shiftData() {
    const option = shift?.options
        ? shift.options[shift.selectedIndex]
        : null;

    return {

        start:
            option?.dataset.startTime
            || shift?.dataset.startTime,

        end:
            option?.dataset.endTime
            || shift?.dataset.endTime,

        normal:
            Number(
                option?.dataset.normalHours
                || shift?.dataset.normalHours
            ),

        otStart:
            option?.dataset.otStart
            || shift?.dataset.otStart,

        graceIn:
            Number(
                option?.dataset.graceIn
                || shift?.dataset.graceIn
                || 0
            ),

        graceOut:
            Number(
                option?.dataset.graceOut
                || shift?.dataset.graceOut
                || 0
            ),

        crossesMidnight:
            Number(
                option?.dataset.crossesMidnight
                || shift?.dataset.crossesMidnight
                || 0
            ) === 1
    };
}

        function moreOptions(selectedId) {
            let html = '<option value="">More</option>';
            statuses.forEach((status) => {
                if (isPresent(status) || isAbsent(status)) return;
                html += `<option value="${esc(status.id)}"${String(status.id) === String(selectedId) ? ' selected' : ''}>${esc(status.short_name || status.name)}</option>`;
            });
            return html;
        }

        function workingOptions(selectedId) {
            let html = '<option value="">Select</option>';
            workingStatuses.forEach((status) => {
                html += `<option value="${esc(status.id)}"${String(status.id) === String(selectedId) ? ' selected' : ''}>${esc(status.name)}</option>`;
            });
            return html;
        }

        function rowHtml(labour, index) {
            const id = labour.labour_id || labour.id;
            const old = saved(id);
            const statusId = old?.attendance_status_id || labour.attendance_status_id || '';
            const status = byId(statusId);
            const workingStatusId = old?.working_status_id || labour.working_status_id || '';
            const moreId = status && !isPresent(status) && !isAbsent(status) ? statusId : '';
            const checkIn = old?.check_in_time || labour.check_in_time || '';
            const checkOut = old?.check_out_time || labour.check_out_time || '';
            const normal = old?.normal_hours ?? labour.normal_hours ?? 0;
            const ot = old?.ot_hours ?? labour.ot_hours ?? 0;
            const remarks = old?.remarks || labour.remarks || '';
            const source = old?.attendance_source || labour.attendance_source || 'manual';

            const group = labour.assignment_group === 'unassigned'
                ? 'unassigned'
                : (labour.assignment_group === 'other_project'
                    ? 'other-project'
                    : (editing ? 'existing' : 'assigned'));

            return `<tr class="attendance-row" data-existing="${old || labour.has_saved_attendance ? '1' : '0'}" data-group="${esc(group)}" data-search="${esc([labour.full_name, labour.designation_role_name, labour.labour_group_name, labour.home_project_name].filter(Boolean).join(' ').toLowerCase())}">
                <td class="px-3 py-3"><input type="hidden" name="details[${index}][labour_id]" value="${esc(id)}"><input class="status-id" type="hidden" name="details[${index}][attendance_status_id]" value="${esc(statusId)}"><input type="hidden" name="details[${index}][attendance_source]" value="${esc(source)}"><p class="truncate text-sm font-semibold text-gray-900">${esc(labour.full_name || 'Unavailable Labour')}</p><p class="mt-0.5 truncate text-[11px] text-gray-500 lg:hidden">${esc(labour.designation_role_name || '—')}</p><button type="button" class="mobile-details-toggle lg:hidden">Details</button></td>
                <td class="px-3 py-3">
                    <p class="truncate text-sm text-gray-700">${esc(labour.designation_role_name || '—')}</p>
                    <p class="mt-0.5 truncate text-[11px] font-medium text-gray-500">${esc(labour.labour_group_name || 'Un-grouped')}</p>
                    ${labour.assignment_group === 'other_project' && labour.home_project_name
                        ? `<p class="mt-0.5 truncate text-[10px] font-semibold text-amber-700">Home: ${esc(labour.home_project_name)}</p>`
                        : ''}
                </td>
                <td class="px-2 py-3 text-center"><button type="button" class="present-btn inline-flex h-10 w-11 items-center justify-center rounded-lg border text-sm font-bold transition lg:h-9 lg:w-10 lg:rounded-md">P</button></td>
                <td class="px-2 py-3 text-center"><button type="button" class="absent-btn inline-flex h-10 w-11 items-center justify-center rounded-lg border text-sm font-bold transition lg:h-9 lg:w-10 lg:rounded-md">A</button></td>
                <td class="px-3 py-3"><select class="more-status block w-full rounded-md border border-gray-300 bg-white px-2 py-2 text-sm">${moreOptions(moreId)}</select></td>
                <td class="px-3 py-3"><select class="working-status block w-full rounded-md border border-gray-300 bg-white px-2 py-2 text-sm" name="details[${index}][working_status_id]">${workingOptions(workingStatusId)}</select></td>
                <td class="px-3 py-3"><input type="time" class="check-in block w-full rounded-md border border-gray-300 px-2 py-2 text-sm" name="details[${index}][check_in_time]" value="${esc(checkIn)}"></td>
                <td class="px-3 py-3"><input type="time" class="check-out block w-full rounded-md border border-gray-300 px-2 py-2 text-sm" name="details[${index}][check_out_time]" value="${esc(checkOut)}"></td>
                <td class="px-3 py-3"><input type="number" class="normal block w-full rounded-md border border-gray-300 px-2 py-2 text-sm" name="details[${index}][normal_hours]" value="${Number(normal || 0).toFixed(2)}" min="0" max="24" step="0.25"></td>
                <td class="px-3 py-3"><input type="number" class="ot block w-full rounded-md border border-gray-300 px-2 py-2 text-sm" name="details[${index}][ot_hours]" value="${Number(ot || 0).toFixed(2)}" min="0" max="24" step="0.25"></td>
                <td class="px-3 py-3"><input type="text" class="block w-full rounded-md border border-gray-300 px-2 py-2 text-sm" name="details[${index}][remarks]" value="${esc(remarks)}" maxlength="1000" placeholder="Optional remarks"></td>
            </tr>`;
        }

        function buttonState(row) {
            const status = byId(row.querySelector('.status-id').value);
            row.querySelector('.present-btn').className = 'present-btn inline-flex h-10 w-11 items-center justify-center rounded-lg border text-sm font-bold transition lg:h-9 lg:w-10 lg:rounded-md ' + (isPresent(status) ? 'border-green-600 bg-green-600 text-white' : 'border-green-300 bg-white text-green-700 hover:bg-green-50');
            row.querySelector('.absent-btn').className = 'absent-btn inline-flex h-10 w-11 items-center justify-center rounded-lg border text-sm font-bold transition lg:h-9 lg:w-10 lg:rounded-md ' + (isAbsent(status) ? 'border-red-600 bg-red-600 text-white' : 'border-red-300 bg-white text-red-700 hover:bg-red-50');
        }

        function clearAttendance(row) {
            row.querySelector('.status-id').value = '';
            row.querySelector('.more-status').value = '';
            row.querySelector('.working-status').value = '';
            row.querySelector('.check-in').value = '';
            row.querySelector('.check-out').value = '';
            row.querySelector('.normal').value = '0.00';
            row.querySelector('.ot').value = '0.00';
            applyRules(row, false);
        }

        function applyRules(row, defaults = true) {
            const status = byId(row.querySelector('.status-id').value);
            const checkIn = row.querySelector('.check-in');
            const checkOut = row.querySelector('.check-out');
            const normal = row.querySelector('.normal');
            const ot = row.querySelector('.ot');
            const workingSelect = row.querySelector('.working-status');
            const hasAttendance = Boolean(status);
            const allocatesLabour = hasAttendance && !isNonWorking(status);

            checkIn.disabled = !allocatesLabour;
            checkOut.disabled = !allocatesLabour;
            normal.disabled = !allocatesLabour || !status?.allows_normal_hours;
            ot.disabled = !allocatesLabour || !status?.allows_ot_hours;
            workingSelect.disabled = !allocatesLabour;

            if (!hasAttendance) {
                workingSelect.value = '';
                checkIn.value = '';
                checkOut.value = '';
                normal.value = '0.00';
                ot.value = '0.00';
            } else if (!allocatesLabour) {
                workingSelect.value = '';
                checkIn.value = '';
                checkOut.value = '';
                normal.value = '0.00';
                ot.value = '0.00';
            } else if (defaults) {
                const data = shiftData();

                if (isHalfDay(status)) {
                    const partial = partialWorking();
                    workingSelect.value = partial ? String(partial.id) : '';
                    checkIn.value = data.start;
                    checkOut.value = '13:30';
                    normal.value = '4.00';
                    ot.value = '0.00';
                } else if (isPresent(status)) {
                    const workingStatus = defaultWorking();

                    /*
                     * Present always means Working by default.
                     * This is intentional even when another Working Status
                     * had previously been selected before clicking P.
                     */
                    workingSelect.value = workingStatus
                        ? String(workingStatus.id)
                        : '';

                    checkIn.value = data.start;
                    if (!checkOut.value) {
                        checkOut.value = '';
                    }
                    normal.value = data.normal.toFixed(2);
                    ot.value = '0.00';
                } else {
                    const workingStatus = defaultWorking();
                    if (!workingSelect.value && workingStatus) {
                        workingSelect.value = String(workingStatus.id);
                    }
                    if (!checkIn.value) checkIn.value = data.start;
                    if (!normal.value || Number(normal.value) === 0) {
                        normal.value = data.normal.toFixed(2);
                    }
                    if (!ot.value) ot.value = '0.00';
                }
            }

            buttonState(row);
            updateSummary();
        }

        function setStatus(row, status) {
            if (!status) return;

            const currentStatus = byId(
                row.querySelector('.status-id').value
            );

            /*
             * Clicking an already-selected P or A button toggles it off
             * and returns the row to the neutral state.
             */
            if (
                currentStatus
                && String(currentStatus.id) === String(status.id)
                && (isPresent(status) || isAbsent(status))
            ) {
                clearAttendance(row);
                return;
            }

            row.querySelector('.status-id').value = status.id;
            row.querySelector('.more-status').value =
                isPresent(status) || isAbsent(status)
                    ? ''
                    : String(status.id);

            applyRules(row, true);
        }

        function minutes(value) {
            if (!value || !String(value).includes(':')) return null;
            const parts = String(value).split(':').map(Number);
            return parts.some(Number.isNaN) ? null : parts[0] * 60 + parts[1];
        }

        function calculate(row) {
    const status = byId(
        row.querySelector('.status-id').value
    );

    if (!status || isNonWorking(status)) {
        return;
    }

    let checkInMinutes = minutes(
        row.querySelector('.check-in').value
    );

    let checkOutMinutes = minutes(
        row.querySelector('.check-out').value
    );

    if (
        checkInMinutes === null
        || checkOutMinutes === null
    ) {
        return;
    }

    const data = shiftData();

    let shiftStartMinutes = minutes(data.start);
    let shiftEndMinutes = minutes(data.end);
    let otStartMinutes = minutes(
        data.otStart || data.end
    );

    if (
        shiftStartMinutes === null
        || shiftEndMinutes === null
    ) {
        return;
    }

    /*
     * Overnight shifts:
     * Example 20:00 to 05:00.
     */
    if (
        data.crossesMidnight
        || shiftEndMinutes < shiftStartMinutes
    ) {
        shiftEndMinutes += 1440;

        if (
            otStartMinutes !== null
            && otStartMinutes < shiftStartMinutes
        ) {
            otStartMinutes += 1440;
        }

        if (checkOutMinutes < checkInMinutes) {
            checkOutMinutes += 1440;
        }
    } else if (checkOutMinutes < checkInMinutes) {
        checkOutMinutes += 1440;
    }

    const workedMinutes = Math.max(
        0,
        checkOutMinutes - checkInMinutes
    );

    /*
     * Normal hours are capped by Shift Master.
     */
    const normalHours = Math.min(
        workedMinutes / 60,
        Number(data.normal || 0)
    );

    /*
     * OT begins from configured OT Start Time.
     * If OT Start Time is blank, shift end is used.
     */
    const effectiveOtStart =
        otStartMinutes ?? shiftEndMinutes;

    const otMinutes = Math.max(
        0,
        checkOutMinutes - effectiveOtStart
    );

    row.querySelector('.normal').value =
        normalHours.toFixed(2);

    row.querySelector('.ot').value =
        (otMinutes / 60).toFixed(2);
}

        function bind(row) {
            row.querySelector('.mobile-details-toggle')?.addEventListener('click', function () {
                const open = row.classList.toggle('mobile-details-open');
                this.textContent = open ? 'Hide Details' : 'Details';
            });

            row.querySelector('.present-btn').addEventListener('click', () => setStatus(row, present()));
            row.querySelector('.absent-btn').addEventListener('click', () => setStatus(row, absent()));
            row.querySelector('.more-status').addEventListener('change', function () {
                if (!this.value) {
                    clearAttendance(row);
                    return;
                }
                setStatus(row, byId(this.value));
            });
            row.querySelector('.check-in').addEventListener('change', () => calculate(row));
            row.querySelector('.check-out').addEventListener('change', () => { calculate(row); updateSummary(); });
            row.querySelector('.working-status').addEventListener('change', updateSummary);
            row.querySelector('.normal').addEventListener('input', updateSummary);
            row.querySelector('.ot').addEventListener('input', updateSummary);
            applyRules(row, false);
        }

        function updateSummary() {
            let presentCount = 0;
            let absentCount = 0;
            let halfDayCount = 0;
            let leaveCount = 0;
            let workingCount = 0;
            let idleCount = 0;
            let normalHours = 0;
            let otHours = 0;

            rows.forEach((row) => {
                const status = byId(row.querySelector('.status-id').value);
                const workingStatus = workingById(row.querySelector('.working-status').value);
                if (isPresent(status)) presentCount++;
                if (isAbsent(status)) absentCount++;
                if (isHalfDay(status)) halfDayCount++;
                if (isLeave(status)) leaveCount++;
                if (workingCode(workingStatus).includes('IDLE')) idleCount++;
                else if (workingStatus) workingCount++;
                normalHours += Number(row.querySelector('.normal').value || 0);
                otHours += Number(row.querySelector('.ot').value || 0);
            });

            $('summary-present').textContent = presentCount;
            $('summary-absent').textContent = absentCount;
            $('summary-half-day').textContent = halfDayCount;
            $('summary-leave').textContent = leaveCount;
            $('summary-working').textContent = workingCount;
            $('summary-idle').textContent = idleCount;
            $('summary-normal').textContent = normalHours.toFixed(2);
            $('summary-ot').textContent = otHours.toFixed(2);
        }

        function groupHeadingHtml(groupName, labourCount) {
            return `
                <tr class="labour-group-heading bg-[#0F2A52]">
                    <td colspan="11" class="border-y border-[#0F2A52] bg-[#0F2A52] px-3 py-2 text-xs font-bold uppercase tracking-wide text-white">
                        ${esc(groupName || 'Un-grouped Labour')}
                        <span class="ml-2 font-medium normal-case tracking-normal text-blue-100">
                            ${labourCount} labour${labourCount === 1 ? '' : 's'}
                        </span>
                    </td>
                </tr>
            `;
        }

        function groupedRowsHtml(labours, startIndex) {
            const grouped = new Map();

            labours.forEach((labour) => {
                const key = String(
                    labour.labour_group_id
                    || 'ungrouped'
                );

                if (!grouped.has(key)) {
                    grouped.set(key, {
                        name:
                            labour.labour_group_name
                            || 'Un-grouped Labour',

                        sortOrder:
                            Number(
                                labour.labour_group_sort_order
                                ?? 999999
                            ),

                        labours: [],
                    });
                }

                grouped.get(key).labours.push(labour);
            });

            const orderedGroups = Array.from(
                grouped.values()
            ).sort((a, b) => {
                if (a.sortOrder !== b.sortOrder) {
                    return a.sortOrder - b.sortOrder;
                }

                return String(a.name).localeCompare(
                    String(b.name)
                );
            });

            let index = startIndex;
            let html = '';

            orderedGroups.forEach((group) => {
                group.labours.sort((a, b) =>
                    String(a.full_name || '').localeCompare(
                        String(b.full_name || '')
                    )
                );

                html += groupHeadingHtml(
                    group.name,
                    group.labours.length
                );

                group.labours.forEach((labour) => {
                    html += rowHtml(
                        labour,
                        index++
                    );
                });
            });

            return {
                html,
                nextIndex: index,
            };
        }

        function render(assigned, unassigned, otherProject) {
            assigned = Array.isArray(assigned)
                ? assigned
                : [];

            unassigned = Array.isArray(unassigned)
                ? unassigned
                : [];

            otherProject = Array.isArray(otherProject)
                ? otherProject
                : [];

            const assignedResult = groupedRowsHtml(
                assigned,
                0
            );

            const unassignedResult = groupedRowsHtml(
                unassigned,
                assignedResult.nextIndex
            );

            const otherProjectResult = groupedRowsHtml(
                otherProject,
                unassignedResult.nextIndex
            );

            assignedRows.innerHTML =
                assignedResult.html;

            unassignedRows.innerHTML =
                unassignedResult.html;

            otherProjectRows.innerHTML =
                otherProjectResult.html;

            rows = Array.from(
                document.querySelectorAll(
                    '.attendance-row'
                )
            );

            rows.forEach(bind);

            $('pool-assigned-count').textContent =
                assigned.length;

            $('pool-unassigned-count').textContent =
                unassigned.length;

            $('pool-other-project-count').textContent =
                otherProject.length;

            $('pool-total-count').textContent =
                assigned.length + unassigned.length + otherProject.length;

            $('labour-pool-summary')
                .classList.remove('hidden');

            $('attendance-groups').classList.toggle(
                'hidden',
                rows.length === 0
            );

            setUnassignedOpen(false);
            setOtherProjectOpen(false);

            $('labour-empty-message').classList.toggle(
                'hidden',
                rows.length > 0
            );

            filterRows();
            updateSummary();
        }

        function setUnassignedOpen(open, scrollToSection = false) {
            const section = $('unassigned-section');
            const content = $('unassigned-labour-content');
            const footer = $('unassigned-labour-footer');
            if (!section || !content) return;

            content.classList.toggle('hidden', !open);
            footer?.classList.toggle('hidden', !open);
            $('toggle-unassigned-labour')?.setAttribute('aria-expanded', open ? 'true' : 'false');
            $('open-unassigned-labour')?.setAttribute('aria-expanded', open ? 'true' : 'false');

            if ($('toggle-unassigned-labour-label')) $('toggle-unassigned-labour-label').textContent = open ? 'Hide Labour' : 'Show Labour';
            if ($('open-unassigned-labour-label')) $('open-unassigned-labour-label').textContent = open ? 'Hide Labour' : 'View Labour';

            $('toggle-unassigned-labour-icon')?.classList.toggle('rotate-180', open);
            $('open-unassigned-labour-icon')?.classList.toggle('rotate-180', open);

            if (scrollToSection && open) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function unassignedIsOpen() {
            const content = $('unassigned-labour-content');
            return Boolean(content && !content.classList.contains('hidden'));
        }

        function setOtherProjectOpen(open, scrollToSection = false) {
            const section = $('other-project-section');
            const content = $('other-project-labour-content');
            const footer = $('other-project-labour-footer');
            if (!section || !content) return;

            content.classList.toggle('hidden', !open);
            footer?.classList.toggle('hidden', !open);
            $('toggle-other-project-labour')?.setAttribute('aria-expanded', open ? 'true' : 'false');
            $('open-other-project-labour')?.setAttribute('aria-expanded', open ? 'true' : 'false');

            if ($('toggle-other-project-labour-label')) {
                $('toggle-other-project-labour-label').textContent = open ? 'Hide Labour' : 'Show Labour';
            }

            if ($('open-other-project-labour-label')) {
                $('open-other-project-labour-label').textContent = open ? 'Hide Labour' : 'View Labour';
            }

            $('toggle-other-project-labour-icon')?.classList.toggle('rotate-180', open);
            $('open-other-project-labour-icon')?.classList.toggle('rotate-180', open);

            if (scrollToSection && open) {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function otherProjectIsOpen() {
            const content = $('other-project-labour-content');
            return Boolean(content && !content.classList.contains('hidden'));
        }

        function visibleRows() {
            return rows.filter((row) => !row.classList.contains('hidden'));
        }

        function visibleAssignedRows() {
            return Array.from(
                assignedRows.querySelectorAll(
                    '.attendance-row'
                )
            ).filter(
                (row) =>
                    ! row.classList.contains('hidden')
            );
        }

        function selectedAttendanceRows() {
            return rows.filter((row) => {
                return Boolean(
                    row.querySelector('.status-id').value
                );
            });
        }

        function refreshGroupHeadingVisibility(
            tbody
        ) {
            const children = Array.from(
                tbody.children
            );

            let currentHeading = null;
            let currentRows = [];

            const flush = () => {
                if (!currentHeading) {
                    return;
                }

                const hasVisibleRows =
                    currentRows.some(
                        (row) =>
                            ! row.classList.contains(
                                'hidden'
                            )
                    );

                currentHeading.classList.toggle(
                    'hidden',
                    ! hasVisibleRows
                );
            };

            children.forEach((child) => {
                if (
                    child.classList.contains(
                        'labour-group-heading'
                    )
                ) {
                    flush();
                    currentHeading = child;
                    currentRows = [];
                    return;
                }

                if (
                    child.classList.contains(
                        'attendance-row'
                    )
                ) {
                    currentRows.push(child);
                }
            });

            flush();
        }

        function filterRows() {
            const term = String(
                $('labour-table-search').value || ''
            )
                .trim()
                .toLowerCase();

            rows.forEach((row) => {
                row.classList.toggle(
                    'hidden',
                    Boolean(term)
                    && ! row.dataset.search.includes(
                        term
                    )
                );
            });

            refreshGroupHeadingVisibility(
                assignedRows
            );

            refreshGroupHeadingVisibility(
                unassignedRows
            );

            refreshGroupHeadingVisibility(
                otherProjectRows
            );

            const visibleAssigned =
                Array.from(
                    assignedRows.querySelectorAll(
                        '.attendance-row'
                    )
                ).filter(
                    (row) =>
                        ! row.classList.contains(
                            'hidden'
                        )
                );

            const visibleUnassigned =
                Array.from(
                    unassignedRows.querySelectorAll(
                        '.attendance-row'
                    )
                ).filter(
                    (row) =>
                        ! row.classList.contains(
                            'hidden'
                        )
                );

            const visibleOtherProject =
                Array.from(
                    otherProjectRows.querySelectorAll(
                        '.attendance-row'
                    )
                ).filter(
                    (row) =>
                        ! row.classList.contains(
                            'hidden'
                        )
                );

            $('assigned-visible-count').textContent =
                visibleAssigned.length;

            $('unassigned-visible-count').textContent =
                visibleUnassigned.length;

            $('other-project-visible-count').textContent =
                visibleOtherProject.length;

            $('assigned-section').classList.toggle(
                'hidden',
                assignedRows.querySelectorAll(
                    '.attendance-row'
                ).length === 0
            );

            $('unassigned-section').classList.toggle(
                'hidden',
                unassignedRows.querySelectorAll(
                    '.attendance-row'
                ).length === 0
            );

            $('other-project-section').classList.toggle(
                'hidden',
                otherProjectRows.querySelectorAll(
                    '.attendance-row'
                ).length === 0
            );
        }

        function standardLogoutTime() {
    return shiftData().end || '';
}

        function showClientError(message) {
            const errorBox = $('attendance-client-error');

            if (!errorBox) {
                window.alert(message);
                return;
            }

            errorBox.textContent = message;
            errorBox.classList.remove('hidden');
            errorBox.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
            });
        }

        function prepareAttendanceFormForSubmit() {
            const selectedRows = selectedAttendanceRows();

            if (selectedRows.length === 0) {
                showClientError(
                    'Mark at least one labour as Present, Absent, or choose an Attendance Status before saving.'
                );
                return false;
            }

            rows.forEach((row) => {
                const selected = Boolean(
                    row.querySelector('.status-id').value
                );

                row.querySelectorAll('input, select, textarea').forEach((field) => {
                    /*
                     * During create, neutral labour rows are display-only and
                     * are excluded from the submitted details array.
                     */
                    if (!selected) {
                        field.disabled = true;
                    } else {
                        field.disabled = false;
                    }
                });
            });

            return true;
        }

        function submitAttendance(event) {
            event?.preventDefault();

            if (!attendanceForm) {
                showClientError(
                    'The attendance form could not be located. Please refresh the page and try again.'
                );
                return;
            }

            $('attendance-client-error')?.classList.add('hidden');

            if (!prepareAttendanceFormForSubmit()) {
                return;
            }

            const submitButtons = document.querySelectorAll(
                '[data-attendance-submit]'
            );

            submitButtons.forEach((button) => {
                button.disabled = true;
                button.classList.add(
                    'cursor-not-allowed',
                    'opacity-60'
                );
                button.dataset.originalText =
                    button.dataset.originalText
                    || button.textContent.trim();

                button.textContent = editing
                    ? 'Updating Attendance...'
                    : 'Saving Attendance...';
            });

            /*
             * Native form.submit() avoids requestSubmit() recursion and
             * guarantees that the PUT/POST form is sent once.
             */
            attendanceForm.submit();
        }

        async function load() {
            if (!project?.value || !date?.value) {
                render([], [], []);
                return;
            }

            const sequence = ++requestNo;
            $('labour-loading-message').classList.remove('hidden');
            $('labour-error-message').classList.add('hidden');
            const params = new URLSearchParams({ attendance_date: date.value });
            if (editing && attendanceId) params.set('attendance_id', attendanceId);

            try {
                const response = await fetch(`/ajax/projects/${encodeURIComponent(project.value)}/labours?${params}`, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (sequence !== requestNo) return;
                const payload = await response.json();
                if (!response.ok) throw new Error(payload.message || 'Unable to load labour profiles.');
                render(
                    payload.assigned_labours || [],
                    payload.unassigned_labours || [],
                    payload.other_project_labours || []
                );
            } catch (error) {
                render([], [], []);
                $('labour-error-message').textContent = error.message || 'Unable to load labour profiles.';
                $('labour-error-message').classList.remove('hidden');
            } finally {
                if (sequence === requestNo) $('labour-loading-message').classList.add('hidden');
            }
        }

        document.querySelectorAll('[data-attendance-submit]').forEach((button) => {
            button.addEventListener('click', submitAttendance);
        });

        attendanceForm?.addEventListener('submit', (event) => {
            if (!prepareAttendanceFormForSubmit()) {
                event.preventDefault();
            }
        });

        $('labour-table-search').addEventListener('input', filterRows);

        $('open-unassigned-labour')?.addEventListener('click', () => {
            if (unassignedRows.querySelectorAll('.attendance-row').length === 0) return;
            const nextOpen = !unassignedIsOpen();
            setUnassignedOpen(nextOpen, nextOpen);
        });

        $('toggle-unassigned-labour')?.addEventListener('click', () => {
            setUnassignedOpen(!unassignedIsOpen(), false);
        });

        $('open-other-project-labour')?.addEventListener('click', () => {
            if (otherProjectRows.querySelectorAll('.attendance-row').length === 0) return;
            const nextOpen = !otherProjectIsOpen();
            setOtherProjectOpen(nextOpen, nextOpen);
        });

        $('toggle-other-project-labour')?.addEventListener('click', () => {
            setOtherProjectOpen(!otherProjectIsOpen(), false);
        });

        $('mark-assigned-present')?.addEventListener('click', () => {
            visibleAssignedRows().forEach((row) => {
                setStatus(row, present());
            });
        });

        $('mark-assigned-absent')?.addEventListener('click', () => {
            visibleAssignedRows().forEach((row) => {
                setStatus(row, absent());
            });
        });

        $('logout-assigned-present')?.addEventListener('click', () => {
            const time = standardLogoutTime();

            visibleAssignedRows().forEach((row) => {
                const status = byId(
                    row.querySelector('.status-id').value
                );

                if (!isPresent(status)) return;

                row.querySelector('.check-out').value = time;
                calculate(row);
            });

            updateSummary();
        });

        $('mark-filtered-present').addEventListener('click', () => {
            visibleRows().forEach((row) => setStatus(row, present()));
        });

        $('mark-filtered-absent').addEventListener('click', () => {
            visibleRows().forEach((row) => setStatus(row, absent()));
        });
        $('logout-filtered-present').addEventListener('click', () => {
            const time = standardLogoutTime();
            visibleRows().forEach((row) => {
                const status = byId(row.querySelector('.status-id').value);
                if (!status || isNonWorking(status)) return;
                row.querySelector('.check-out').value = time;
                calculate(row);
            });
            updateSummary();
        });

        if (!editing) {
            project?.addEventListener('change', load);
            date?.addEventListener('change', load);
        }
        shift?.addEventListener('change', () => visibleRows().forEach((row) => applyRules(row, true)));
        load();
    }

    document.addEventListener('DOMContentLoaded', initAttendance);
    document.addEventListener('livewire:navigated', initAttendance);
})();
</script>
@endpush
