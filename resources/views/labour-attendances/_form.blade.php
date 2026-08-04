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
            ? 'Project, attendance date, shift and labour composition are locked.'
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
                Labour cannot be added or removed while editing. Use Attendance Corrections to change labour composition.
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
            ? 'Update attendance values for the existing labour rows.'
            : 'Assigned labour is shown first, followed by unassigned labour.' }}"
    >
        <div id="labour-pool-summary" class="mb-5 hidden grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-green-700">Assigned Labour</p>
                <p id="pool-assigned-count" class="mt-2 text-2xl font-bold text-green-800">0</p>
            </div>
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Unassigned Labour</p>
                <p id="pool-unassigned-count" class="mt-2 text-2xl font-bold text-blue-800">0</p>
            </div>
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
                <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
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
                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" id="mark-filtered-present" class="inline-flex items-center justify-center rounded-lg border border-green-300 bg-green-50 px-4 py-2 text-sm font-semibold text-green-700 shadow-sm transition hover:bg-green-100">
                    Mark Filtered Present
                </button>
                <button type="button" id="mark-filtered-absent" class="inline-flex items-center justify-center rounded-lg border border-red-300 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 shadow-sm transition hover:bg-red-100">
                    Mark Filtered Absent
                </button>
                <button type="button" id="logout-filtered-present" class="inline-flex items-center justify-center rounded-lg border border-blue-300 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm transition hover:bg-blue-100">
                    Logout Filtered Present
                </button>
            </div>
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
            ] as $group)
                <div id="{{ $group['id'] }}-section" class="overflow-hidden rounded-xl border {{ $group['border'] }}">
                    <div class="border-b px-4 py-3 {{ $group['head'] }}">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h3 class="font-semibold {{ $group['text'] }}">{{ $group['title'] }} (<span id="{{ $group['id'] }}-visible-count">0</span>)</h3>
                                <p class="text-xs text-gray-600">{{ $group['description'] }}</p>
                            </div>

                            @if($group['id'] === 'assigned')
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" id="mark-assigned-present" class="rounded-lg border border-green-300 bg-white px-3 py-2 text-xs font-semibold text-green-700 hover:bg-green-50">
                                        Mark Assigned Present
                                    </button>
                                    <button type="button" id="mark-assigned-absent" class="rounded-lg border border-red-300 bg-white px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">
                                        Mark Assigned Absent
                                    </button>
                                    <button type="button" id="logout-assigned-present" class="rounded-lg border border-blue-300 bg-white px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50">
                                        Logout Assigned Present
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="overflow-x-auto {{ $group['id'] === 'unassigned' ? 'max-h-[680px] overflow-y-auto' : '' }}">
                        <table class="w-full min-w-[1500px] table-fixed">
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

                    @if($group['id'] === 'assigned')
                        <div class="flex justify-end border-t border-blue-200 bg-blue-50 px-4 py-3">
                            <button
                                type="button"
                                data-attendance-submit
                                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            >
                                {{ $editingAttendance ? 'Update Attendance' : 'Save Attendance' }}
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-5 flex justify-end">
            <button type="button" data-attendance-submit class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                {{ $editingAttendance ? 'Update Attendance' : 'Save Attendance' }}
            </button>
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
            let html = '<option value="">Select</option>';
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

            const group = labour.assignment_group === 'unassigned' ? 'unassigned' : (editing ? 'existing' : 'assigned');

            return `<tr class="attendance-row" data-group="${esc(group)}" data-search="${esc([labour.full_name, labour.designation_role_name].filter(Boolean).join(' ').toLowerCase())}">
                <td class="px-3 py-3"><input type="hidden" name="details[${index}][labour_id]" value="${esc(id)}"><input class="status-id" type="hidden" name="details[${index}][attendance_status_id]" value="${esc(statusId)}"><input type="hidden" name="details[${index}][attendance_source]" value="${esc(source)}"><p class="truncate text-sm font-semibold text-gray-900">${esc(labour.full_name || 'Unavailable Labour')}</p></td>
                <td class="px-3 py-3"><p class="truncate text-sm text-gray-700">${esc(labour.designation_role_name || '—')}</p></td>
                <td class="px-2 py-3 text-center"><button type="button" class="present-btn inline-flex h-9 w-10 items-center justify-center rounded-md border text-sm font-bold transition">P</button></td>
                <td class="px-2 py-3 text-center"><button type="button" class="absent-btn inline-flex h-9 w-10 items-center justify-center rounded-md border text-sm font-bold transition">A</button></td>
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
            row.querySelector('.present-btn').className = 'present-btn inline-flex h-9 w-10 items-center justify-center rounded-md border text-sm font-bold transition ' + (isPresent(status) ? 'border-green-600 bg-green-600 text-white' : 'border-green-300 bg-white text-green-700 hover:bg-green-50');
            row.querySelector('.absent-btn').className = 'absent-btn inline-flex h-9 w-10 items-center justify-center rounded-md border text-sm font-bold transition ' + (isAbsent(status) ? 'border-red-600 bg-red-600 text-white' : 'border-red-300 bg-white text-red-700 hover:bg-red-50');
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

        function render(assigned, unassigned) {
            assigned = Array.isArray(assigned) ? assigned : [];
            unassigned = Array.isArray(unassigned) ? unassigned : [];
            let index = 0;
            assignedRows.innerHTML = assigned.map((labour) => rowHtml(labour, index++)).join('');
            unassignedRows.innerHTML = unassigned.map((labour) => rowHtml(labour, index++)).join('');
            rows = Array.from(document.querySelectorAll('.attendance-row'));
            rows.forEach(bind);
            $('pool-assigned-count').textContent = assigned.length;
            $('pool-unassigned-count').textContent = unassigned.length;
            $('pool-total-count').textContent = assigned.length + unassigned.length;
            $('labour-pool-summary').classList.remove('hidden');
            $('attendance-groups').classList.toggle('hidden', rows.length === 0);
            $('labour-empty-message').classList.toggle('hidden', rows.length > 0);
            filterRows();
            updateSummary();
        }

        function visibleRows() {
            return rows.filter((row) => !row.classList.contains('hidden'));
        }

        function visibleAssignedRows() {
            return Array.from(assignedRows.children)
                .filter((row) => !row.classList.contains('hidden'));
        }

        function selectedAttendanceRows() {
            return rows.filter((row) => {
                return Boolean(
                    row.querySelector('.status-id').value
                );
            });
        }

        function filterRows() {
            const term = String($('labour-table-search').value || '').trim().toLowerCase();
            rows.forEach((row) => row.classList.toggle('hidden', Boolean(term) && !row.dataset.search.includes(term)));
            $('assigned-visible-count').textContent = Array.from(assignedRows.children).filter((row) => !row.classList.contains('hidden')).length;
            $('unassigned-visible-count').textContent = Array.from(unassignedRows.children).filter((row) => !row.classList.contains('hidden')).length;
            $('assigned-section').classList.toggle('hidden', assignedRows.children.length === 0);
            $('unassigned-section').classList.toggle('hidden', unassignedRows.children.length === 0);
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

            if (editing) {
                const neutralRows = rows.filter((row) => {
                    return !row.querySelector('.status-id').value;
                });

                if (neutralRows.length > 0) {
                    showClientError(
                        'Every existing labour row must have an attendance value before updating. Select P, A, or another Attendance Status for the neutral rows.'
                    );
                    return false;
                }
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
                    if (!editing && !selected) {
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
                render([], []);
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
                if (editing) render(payload.existing_labours || payload.labours || [], []);
                else render(payload.assigned_labours || [], payload.unassigned_labours || []);
            } catch (error) {
                render([], []);
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
