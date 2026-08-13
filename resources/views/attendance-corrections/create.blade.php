@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="New Attendance Correction"
    subtitle="Correct existing attendance or add labour omitted from the approved attendance sheet."
>
    <x-slot:actions>
        <x-rds.button
            href="{{ route('attendance-corrections.index') }}"
            variant="secondary"
        >
            Back to Corrections
        </x-rds.button>
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4">
        <div class="text-sm font-semibold text-red-800">
            Please correct the following errors:
        </div>

        <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<x-rds.card class="mb-4">
    <div class="mb-4">
        <h2 class="text-base font-semibold text-gray-900">
            Select Approved Attendance
        </h2>

        <p class="mt-1 text-sm text-gray-500">
            Select the approved attendance sheet that requires correction.
        </p>
    </div>

    <form method="GET" action="{{ route('attendance-corrections.create') }}">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_auto]">
            <x-rds.select
                name="labour_attendance_id"
                label="Approved Attendance Sheet"
                required
            >
                <option value="">Select Attendance Sheet</option>

                @foreach($approvedAttendances as $attendance)
                    <option
                        value="{{ $attendance->id }}"
                        @selected(
                            (string) request('labour_attendance_id')
                            === (string) $attendance->id
                        )
                    >
                        {{ $attendance->attendance_number }}
                        —
                        {{ $attendance->project?->project_name ?? 'No Project' }}
                        —
                        {{ $attendance->attendance_date?->format('d M Y') ?? 'No Date' }}

                        @if($attendance->shift?->name)
                            — {{ $attendance->shift->name }}
                        @endif
                    </option>
                @endforeach
            </x-rds.select>

            <div class="flex items-end">
                <x-rds.button
                    type="submit"
                    variant="primary"
                    class="w-full justify-center lg:w-auto"
                >
                    Load Attendance
                </x-rds.button>
            </div>
        </div>
    </form>
</x-rds.card>

@if($selectedAttendance)

    @php
        $presentStatus = $attendanceStatuses->first(
            fn ($status) => in_array(
                strtoupper(trim((string) $status->code)),
                ['P', 'PRESENT'],
                true
            )
        );

        $absentStatus = $attendanceStatuses->first(
            fn ($status) => in_array(
                strtoupper(trim((string) $status->code)),
                ['A', 'ABSENT'],
                true
            )
        );

        $workingStatus = $workingStatuses->first(
            fn ($status) => strtoupper(trim((string) $status->code))
                === 'WORKING'
        );

        $moreAttendanceStatuses = $attendanceStatuses->reject(
            fn ($status) => in_array(
                strtoupper(trim((string) $status->code)),
                ['P', 'PRESENT', 'A', 'ABSENT'],
                true
            )
        );

        $referenceDetail = $selectedAttendance->details->first();

        $shiftRules = $selectedAttendance->shift
            ? $selectedAttendance->shift->attendanceRules()
            : [
                'start_time' => $referenceDetail?->check_in_time
                    ? substr((string) $referenceDetail->check_in_time, 0, 5)
                    : null,

                'end_time' => $referenceDetail?->check_out_time
                    ? substr((string) $referenceDetail->check_out_time, 0, 5)
                    : null,

                'normal_hours' => (float) (
                    $referenceDetail?->normal_hours
                    ?? 0
                ),

                'grace_in_minutes' => 0,
                'grace_out_minutes' => 0,

                'ot_start_time' => $referenceDetail?->check_out_time
                    ? substr((string) $referenceDetail->check_out_time, 0, 5)
                    : null,

                'crosses_midnight' => false,
            ];

        $defaultCheckIn = $shiftRules['start_time'] ?? '';
        $defaultCheckOut = $shiftRules['end_time'] ?? '';

        $defaultNormalHours = number_format(
            (float) ($shiftRules['normal_hours'] ?? 0),
            2,
            '.',
            ''
        );

        $defaultOtStart = $shiftRules['ot_start_time']
            ?? $defaultCheckOut;

        $defaultGraceIn = (int) (
            $shiftRules['grace_in_minutes']
            ?? 0
        );

        $defaultGraceOut = (int) (
            $shiftRules['grace_out_minutes']
            ?? 0
        );

        $defaultCrossesMidnight = (bool) (
            $shiftRules['crosses_midnight']
            ?? false
        );

        $oldAddRows = collect(old('details', []))
            ->filter(fn ($row) => ($row['action_type'] ?? null) === 'add')
            ->values()
            ->all();
    @endphp

    <form
        method="POST"
        action="{{ route('attendance-corrections.store') }}"
        x-data="attendanceCorrectionForm({
            initialAddRows: @js($oldAddRows),
            presentStatusId: @js($presentStatus?->id),
            absentStatusId: @js($absentStatus?->id),
            workingStatusId: @js($workingStatus?->id),
            defaultCheckIn: @js($defaultCheckIn),
            defaultCheckOut: @js($defaultCheckOut),
            defaultNormalHours: @js($defaultNormalHours),
            defaultOtStart: @js($defaultOtStart),
            defaultGraceIn: @js($defaultGraceIn),
            defaultGraceOut: @js($defaultGraceOut),
            defaultCrossesMidnight: @js($defaultCrossesMidnight),
            availableLabours: @js(
                $availableLabours->map(
                    fn ($labour) => [
                        'id' => (string) $labour->id,
                        'name' => $labour->full_name,
                        'designation' => $labour->designationRole?->name,
                        'mobile' => $labour->mobile,
                        'group_name' => $labour->labourGroup?->name
                            ?? 'Un-grouped Labour',
                        'assignment_type' => $labour->current_project_id === null
                            ? 'unassigned'
                            : 'assigned',
                        'assignment' => $labour->current_project_id === null
                            ? 'Unassigned'
                            : 'Assigned to Project',
                    ]
                )->values()
            )
        })"
        x-on:submit="prepareSubmission"
    >
        @csrf

        <input
            type="hidden"
            name="labour_attendance_id"
            value="{{ $selectedAttendance->id }}"
        >

        <x-rds.card class="mb-4">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">
                        Original Attendance Summary
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Read-only information from the approved attendance sheet.
                    </p>
                </div>

                <x-rds.badge variant="success">
                    {{ $selectedAttendance->display_status }}
                </x-rds.badge>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Attendance Number
                    </div>
                    <div class="mt-1 text-sm font-semibold text-gray-900">
                        {{ $selectedAttendance->attendance_number }}
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Project
                    </div>
                    <div class="mt-1 text-sm font-semibold text-gray-900">
                        {{ $selectedAttendance->project?->project_name ?? '—' }}
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Attendance Date
                    </div>
                    <div class="mt-1 text-sm font-semibold text-gray-900">
                        {{ $selectedAttendance->attendance_date?->format('d M Y') ?? '—' }}
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Shift
                    </div>
                    <div class="mt-1 text-sm font-semibold text-gray-900">
                        {{ $selectedAttendance->shift?->name ?? 'No Shift' }}
                    </div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-7">
                @foreach([
                    ['Total', $selectedAttendance->total_labours, 'text-gray-900'],
                    ['Present', $selectedAttendance->present_count, 'text-green-700'],
                    ['Absent', $selectedAttendance->absent_count, 'text-red-700'],
                    ['Leave', $selectedAttendance->leave_count, 'text-amber-700'],
                    ['Half Day', $selectedAttendance->half_day_count, 'text-gray-700'],
                    ['Normal Hours', number_format((float) $selectedAttendance->total_normal_hours, 2), 'text-gray-900'],
                    ['OT Hours', number_format((float) $selectedAttendance->total_ot_hours, 2), 'text-gray-900'],
                ] as [$label, $value, $colour])
                    <div class="rounded-lg border border-gray-200 p-3 text-center">
                        <div class="text-xs text-gray-500">{{ $label }}</div>
                        <div class="mt-1 text-lg font-bold {{ $colour }}">
                            {{ $value }}
                        </div>
                    </div>
                @endforeach
            </div>
        </x-rds.card>

        <x-rds.card class="mb-4">
            <div class="mb-4">
                <h2 class="text-base font-semibold text-gray-900">
                    Correction Information
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Explain why this attendance correction request is required.
                </p>
            </div>

            <label
                for="correction_reason"
                class="mb-1 block text-sm font-medium text-gray-700"
            >
                Overall Correction Reason
                <span class="text-red-600">*</span>
            </label>

            <textarea
                id="correction_reason"
                name="correction_reason"
                rows="3"
                required
                maxlength="3000"
                placeholder="Enter the overall reason for this attendance correction..."
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
            >{{ old('correction_reason') }}</textarea>
        </x-rds.card>

        {{-- Existing attendance rows --}}
        <x-rds.card :padding="false" class="mb-4">
            <div class="border-b border-gray-200 px-4 py-4">
                <h2 class="text-base font-semibold text-gray-900">
                    Correct Existing Attendance
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Every row is editable. Rows that remain unchanged are ignored automatically.
                </p>
            </div>

            <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-end">
                    <div>
                        <label
                            for="logout_all_time"
                            class="mb-1 block text-xs font-medium text-gray-600"
                        >
                            Logout Time
                        </label>

                        <input
                            type="time"
                            id="logout_all_time"
                            x-ref="logoutAllTime"
                            value="{{ $defaultCheckOut }}"
                            class="block rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>

                    <button
                        type="button"
                        x-on:click="
                            if (!$refs.logoutAllTime.value) {
                                alert('Please select logout time.');
                                return;
                            }

                            window.dispatchEvent(
                                new CustomEvent('logout-all-present', {
                                    detail: {
                                        logoutTime: $refs.logoutAllTime.value
                                    }
                                })
                            );
                        "
                        class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300"
                    >
                        Logout All Present Labour
                    </button>
                </div>
            </div>

            <div class="max-h-[650px] w-full overflow-auto">
                <table class="w-full min-w-[1680px] table-fixed divide-y divide-gray-200">
                    <colgroup>
                        <col class="w-[210px]">
                        <col class="w-[58px]">
                        <col class="w-[58px]">
                        <col class="w-[180px]">
                        <col class="w-[190px]">
                        <col class="w-[130px]">
                        <col class="w-[130px]">
                        <col class="w-[100px]">
                        <col class="w-[105px]">
                        <col class="w-[95px]">
                        <col class="w-[300px]">
                    </colgroup>

                    <thead class="sticky top-0 z-20 bg-gray-50 shadow-sm">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                Labour
                            </th>
                            <th class="px-2 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">
                                P
                            </th>
                            <th class="px-2 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">
                                A
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                More Status
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                Working Status
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                Check In
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                Check Out
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                Normal
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                OT
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                Reason for Correction
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($selectedAttendance->details as $detail)
                            @php
                                $rowIndex = $loop->index;
                                $oldRow = old("details.{$rowIndex}", []);

                                $initialStatusId = (string) (
                                    $oldRow['new_attendance_status_id']
                                    ?? $detail->attendance_status_id
                                    ?? ''
                                );

                                $checkIn = $detail->check_in_time
                                    ? substr((string) $detail->check_in_time, 0, 5)
                                    : '';

                                $checkOut = $detail->check_out_time
                                    ? substr((string) $detail->check_out_time, 0, 5)
                                    : '';
                            @endphp

                            <tr
                                x-data="attendanceExistingRow({
                                    initialStatusId: @js($initialStatusId),
                                    presentStatusId: @js((string) $presentStatus?->id),
                                    absentStatusId: @js((string) $absentStatus?->id),

                                    shiftStart: @js($defaultCheckIn),
                                    shiftEnd: @js($defaultCheckOut),
                                    normalHoursLimit: @js($defaultNormalHours),
                                    otStart: @js($defaultOtStart),
                                    graceInMinutes: @js($defaultGraceIn),
                                    graceOutMinutes: @js($defaultGraceOut),
                                    crossesMidnight: @js($defaultCrossesMidnight),

                                    checkIn: @js(
                                        $oldRow['new_check_in_time']
                                        ?? $checkIn
                                    ),

                                    checkOut: @js(
                                        $oldRow['new_check_out_time']
                                        ?? $checkOut
                                    ),

                                    normalHours: @js(
                                        $oldRow['new_normal_hours']
                                        ?? number_format(
                                            (float) $detail->normal_hours,
                                            2,
                                            '.',
                                            ''
                                        )
                                    ),

                                    otHours: @js(
                                        $oldRow['new_ot_hours']
                                        ?? number_format(
                                            (float) $detail->ot_hours,
                                            2,
                                            '.',
                                            ''
                                        )
                                    ),

                                    lineReason: @js(
                                        $oldRow['line_reason']
                                        ?? ''
                                    )
                                })"
                                x-on:logout-all-present.window="
                                    logoutAllPresent(
                                        $event.detail.logoutTime
                                    )
                                "
                                class="align-top hover:bg-gray-50"
                            >
                                <td class="px-3 py-4">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $detail->labour?->full_name ?? 'Unknown Labour' }}
                                    </div>

                                    @if($detail->labour?->designationRole?->name)
                                        <div class="mt-1 text-xs text-gray-500">
                                            {{ $detail->labour->designationRole->name }}
                                        </div>
                                    @endif

                                    <input
                                        type="hidden"
                                        name="details[{{ $rowIndex }}][action_type]"
                                        value="modify"
                                    >

                                    <input
                                        type="hidden"
                                        name="details[{{ $rowIndex }}][labour_attendance_detail_id]"
                                        value="{{ $detail->id }}"
                                    >

                                    <input
                                        type="hidden"
                                        name="details[{{ $rowIndex }}][labour_id]"
                                        value="{{ $detail->labour_id }}"
                                    >

                                    <input
                                        type="hidden"
                                        name="details[{{ $rowIndex }}][new_attendance_status_id]"
                                        x-model="statusId"
                                    >

                                    <input
                                        type="hidden"
                                        name="details[{{ $rowIndex }}][new_remarks]"
                                        value="{{ $oldRow['new_remarks'] ?? $detail->remarks }}"
                                    >
                                </td>

                                <td class="px-2 py-4 text-center">
                                    <button
                                        type="button"
                                        x-on:click="setPresent"
                                        x-bind:class="isPresent
                                            ? 'border-green-600 bg-green-600 text-white'
                                            : 'border-green-300 bg-white text-green-700 hover:bg-green-50'"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border text-sm font-bold transition"
                                    >
                                        P
                                    </button>
                                </td>

                                <td class="px-2 py-4 text-center">
                                    <button
                                        type="button"
                                        x-on:click="setAbsent"
                                        x-bind:class="isAbsent
                                            ? 'border-red-600 bg-red-600 text-white'
                                            : 'border-red-300 bg-white text-red-700 hover:bg-red-50'"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border text-sm font-bold transition"
                                    >
                                        A
                                    </button>
                                </td>

                                <td class="px-3 py-4">
                                    <select
                                        x-model="moreStatusId"
                                        x-on:change="applyMoreStatus"
                                        class="block w-full rounded-lg border border-gray-300 px-2 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">Select</option>

                                        @foreach($moreAttendanceStatuses as $attendanceStatus)
                                            <option value="{{ $attendanceStatus->id }}">
                                                {{ $attendanceStatus->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-4">
                                    <select
                                        name="details[{{ $rowIndex }}][new_working_status_id]"
                                        class="block w-full rounded-lg border border-gray-300 px-2 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">Select Status</option>

                                        @foreach($workingStatuses as $status)
                                            <option
                                                value="{{ $status->id }}"
                                                @selected(
                                                    (string) (
                                                        $oldRow['new_working_status_id']
                                                        ?? $detail->working_status_id
                                                    )
                                                    === (string) $status->id
                                                )
                                            >
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-4">
                                    <input
                                        type="time"
                                        name="details[{{ $rowIndex }}][new_check_in_time]"
                                        x-model="checkIn"
                                        class="block w-full rounded-lg border border-gray-300 px-2 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </td>

                                <td class="px-3 py-4">
                                    <input
                                        type="time"
                                        name="details[{{ $rowIndex }}][new_check_out_time]"
                                        x-model="checkOut"
                                        class="block w-full rounded-lg border border-gray-300 px-2 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </td>

                                <td class="px-3 py-4">
                                    <input
                                        type="number"
                                        name="details[{{ $rowIndex }}][new_normal_hours]"
                                        x-model="normalHours"
                                        min="0"
                                        max="24"
                                        step="0.25"
                                        class="block w-full rounded-lg border border-gray-300 px-2 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </td>

                                <td class="px-3 py-4">
                                    <input
                                        type="number"
                                        name="details[{{ $rowIndex }}][new_ot_hours]"
                                        x-model="otHours"
                                        min="0"
                                        max="24"
                                        step="0.25"
                                        class="block w-full rounded-lg border border-gray-300 px-2 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </td>

                                <td class="px-3 py-4">
                                    <textarea
                                        name="details[{{ $rowIndex }}][line_reason]"
                                        x-model="lineReason"
                                        rows="2"
                                        maxlength="2000"
                                        placeholder="Required only when this row is changed"
                                        class="block w-full resize-y rounded-lg border border-gray-300 px-2 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    ></textarea>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-12 text-center text-sm text-gray-600">
                                    No active attendance rows were found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-rds.card>

        {{-- Add Labour --}}
        <x-rds.card :padding="false" class="mb-4">
            <div class="border-b border-gray-200 px-4 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">
                            Add Labour to Attendance
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Add labour omitted from the original attendance. Only project-assigned and unassigned labour are shown.
                        </p>
                    </div>

                    <button
                        type="button"
                        x-on:click="addLabourRow()"
                        class="inline-flex items-center justify-center rounded-lg border border-blue-600 bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300"
                    >
                        Add Labour Row
                    </button>
                </div>
            </div>

            <div
                x-show="addRows.length === 0"
                class="px-4 py-10 text-center"
            >
                <div class="text-sm font-medium text-gray-700">
                    No labour is currently being added.
                </div>

                <div class="mt-1 text-xs text-gray-500">
                    Click “Add Labour Row” to add missing labour.
                </div>
            </div>

            <div x-show="addRows.length > 0" class="w-full overflow-x-auto">
                <table class="w-full min-w-[1840px] table-fixed divide-y divide-gray-200">
                    <colgroup>
                        <col class="w-[245px]">
                        <col class="w-[58px]">
                        <col class="w-[58px]">
                        <col class="w-[180px]">
                        <col class="w-[190px]">
                        <col class="w-[130px]">
                        <col class="w-[130px]">
                        <col class="w-[105px]">
                        <col class="w-[95px]">
                        <col class="w-[280px]">
                        <col class="w-[90px]">
                    </colgroup>

                    <thead class="bg-green-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                Labour
                            </th>
                            <th class="px-2 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">P</th>
                            <th class="px-2 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">A</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">More Status</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Working Status</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Check In</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Check Out</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">Logout</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Normal</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">OT</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Reason for Addition</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">Remove</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        <template x-for="(row, position) in addRows" :key="row.key">
                            <tr class="align-top bg-green-50/30">
                                <td class="px-3 py-4">
                                    <input
                                        type="hidden"
                                        x-bind:name="`details[${row.key}][action_type]`"
                                        value="add"
                                    >

                                    <input
                                        type="hidden"
                                        x-bind:name="`details[${row.key}][new_attendance_status_id]`"
                                        x-model="row.status_id"
                                    >

                                    <input
                                        type="hidden"
                                        x-bind:name="`details[${row.key}][labour_id]`"
                                        x-model="row.labour_id"
                                    >

                                    <div x-show="row.labour_id === ''">
                                        <label
                                            x-bind:for="`labour-search-${row.key}`"
                                            class="sr-only"
                                        >
                                            Search Labour
                                        </label>

                                        <input
                                            type="search"
                                            x-bind:id="`labour-search-${row.key}`"
                                            x-model.debounce.250ms="row.search"
                                            autocomplete="off"
                                            placeholder="Search name, labour group, designation or mobile"
                                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        >

                                        <div
                                            x-show="row.search.trim().length > 0 && row.search.trim().length < 2"
                                            class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"
                                        >
                                            Type at least 2 characters.
                                        </div>

                                        <div
                                            x-show="row.search.trim().length >= 2"
                                            class="mt-2 max-h-48 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-sm"
                                        >
                                            <template
                                                x-for="labour in filteredLabours(row)"
                                                :key="labour.id"
                                            >
                                                <button
                                                    type="button"
                                                    x-on:click="selectLabour(row, labour)"
                                                    class="block w-full border-b border-gray-100 px-3 py-2 text-left last:border-b-0 hover:bg-blue-50"
                                                >
                                                    <div
                                                        class="text-sm font-semibold text-gray-900"
                                                        x-text="labour.name"
                                                    ></div>

                                                    <div
                                                        class="mt-1 inline-flex rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700"
                                                        x-text="labour.group_name || 'Un-grouped Labour'"
                                                    ></div>

                                                    <div class="mt-0.5 text-xs text-gray-500">
                                                        <span
                                                            x-show="labour.designation"
                                                            x-text="labour.designation"
                                                        ></span>

                                                        <span
                                                            x-show="labour.designation && labour.mobile"
                                                        >
                                                            ·
                                                        </span>

                                                        <span
                                                            x-show="labour.mobile"
                                                            x-text="labour.mobile"
                                                        ></span>
                                                    </div>

                                                    <div
                                                        class="mt-0.5 text-xs font-medium text-blue-700"
                                                        x-text="labour.assignment"
                                                    ></div>
                                                </button>
                                            </template>

                                            <div
                                                x-show="filteredLabours(row).length === 0"
                                                class="px-3 py-4 text-center text-xs text-gray-500"
                                            >
                                                No eligible labour matched the search.
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        x-show="row.labour_id !== ''"
                                        class="rounded-lg border border-green-200 bg-green-50 px-3 py-2"
                                    >
                                        <div
                                            class="text-sm font-semibold text-gray-900"
                                            x-text="row.selected_name"
                                        ></div>

                                        <div
                                            x-show="row.selected_group"
                                            class="mt-1 text-xs font-semibold text-slate-700"
                                            x-text="row.selected_group"
                                        ></div>

                                        <div
                                            x-show="row.selected_designation"
                                            class="mt-0.5 text-xs text-gray-500"
                                            x-text="row.selected_designation"
                                        ></div>

                                        <div class="mt-2 flex items-center justify-between gap-2">
                                            <span
                                                class="text-xs font-medium text-green-700"
                                                x-text="row.selected_assignment"
                                            ></span>

                                            <button
                                                type="button"
                                                x-on:click="clearSelectedLabour(row)"
                                                class="text-xs font-semibold text-red-600 hover:text-red-700"
                                            >
                                                Change
                                            </button>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-2 py-4 text-center">
                                    <button
                                        type="button"
                                        x-on:click="markAddPresent(row)"
                                        x-bind:class="String(row.status_id) === String(config.presentStatusId)
                                            ? 'border-green-600 bg-green-600 text-white'
                                            : 'border-green-300 bg-white text-green-700'"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border text-sm font-bold"
                                    >
                                        P
                                    </button>
                                </td>

                                <td class="px-2 py-4 text-center">
                                    <button
                                        type="button"
                                        x-on:click="markAddAbsent(row)"
                                        x-bind:class="String(row.status_id) === String(config.absentStatusId)
                                            ? 'border-red-600 bg-red-600 text-white'
                                            : 'border-red-300 bg-white text-red-700'"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border text-sm font-bold"
                                    >
                                        A
                                    </button>
                                </td>

                                <td class="px-3 py-4">
                                    <select
                                        x-model="row.more_status_id"
                                        x-on:change="if (row.more_status_id) row.status_id = row.more_status_id"
                                        class="block w-full rounded-lg border border-gray-300 px-2 py-2 text-sm shadow-sm"
                                    >
                                        <option value="">Select</option>
                                        @foreach($moreAttendanceStatuses as $attendanceStatus)
                                            <option value="{{ $attendanceStatus->id }}">
                                                {{ $attendanceStatus->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-4">
                                    <select
                                        x-model="row.working_status_id"
                                        x-bind:name="`details[${row.key}][new_working_status_id]`"
                                        class="block w-full rounded-lg border border-gray-300 px-2 py-2 text-sm shadow-sm"
                                    >
                                        <option value="">Select Status</option>
                                        @foreach($workingStatuses as $status)
                                            <option value="{{ $status->id }}">
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-4">
                                    <input
                                        type="time"
                                        x-model="row.check_in_time"
                                        x-bind:name="`details[${row.key}][new_check_in_time]`"
                                        class="block w-full rounded-lg border border-gray-300 px-2 py-2 text-sm shadow-sm"
                                    >
                                </td>

                                <td class="px-3 py-4">
                                    <input
                                        type="time"
                                        x-model="row.check_out_time"
                                        x-bind:name="`details[${row.key}][new_check_out_time]`"
                                        class="block w-full rounded-lg border border-gray-300 px-2 py-2 text-sm shadow-sm"
                                    >
                                </td>

                                <td class="px-3 py-4 text-center">
                                    <button
                                        type="button"
                                        x-on:click="logoutAddedLabour(row)"
                                        x-bind:disabled="String(row.status_id) !== String(config.presentStatusId)"
                                        class="rounded-lg border border-blue-300 bg-white px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50 disabled:cursor-not-allowed disabled:border-gray-200 disabled:bg-gray-100 disabled:text-gray-400"
                                    >
                                        Logout
                                    </button>
                                </td>

                                <td class="px-3 py-4">
                                    <input
                                        type="number"
                                        x-model="row.normal_hours"
                                        x-bind:name="`details[${row.key}][new_normal_hours]`"
                                        min="0"
                                        max="24"
                                        step="0.25"
                                        class="block w-full rounded-lg border border-gray-300 px-2 py-2 text-sm shadow-sm"
                                    >
                                </td>

                                <td class="px-3 py-4">
                                    <input
                                        type="number"
                                        x-model="row.ot_hours"
                                        x-bind:name="`details[${row.key}][new_ot_hours]`"
                                        min="0"
                                        max="24"
                                        step="0.25"
                                        class="block w-full rounded-lg border border-gray-300 px-2 py-2 text-sm shadow-sm"
                                    >
                                </td>

                                <td class="px-3 py-4">
                                    <input
                                        type="hidden"
                                        x-bind:name="`details[${row.key}][new_remarks]`"
                                        value=""
                                    >

                                    <textarea
                                        x-model="row.line_reason"
                                        x-bind:name="`details[${row.key}][line_reason]`"
                                        rows="2"
                                        maxlength="2000"
                                        required
                                        placeholder="Why is this labour being added?"
                                        class="block w-full resize-y rounded-lg border border-gray-300 px-2 py-2 text-sm shadow-sm"
                                    ></textarea>
                                </td>

                                <td class="px-3 py-4 text-center">
                                    <button
                                        type="button"
                                        x-on:click="removeLabourRow(position)"
                                        class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100"
                                    >
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </x-rds.card>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-rds.button
                href="{{ route('attendance-corrections.index') }}"
                variant="secondary"
            >
                Cancel
            </x-rds.button>

            <x-rds.button type="submit" variant="primary">
                Save as Draft
            </x-rds.button>
        </div>
    </form>

@else
    <x-rds.card>
        <div class="py-8 text-center">
            <div class="text-sm font-semibold text-gray-800">
                Select an approved attendance sheet to begin.
            </div>
        </div>
    </x-rds.card>
@endif

<script>
    function attendanceExistingRow(config) {
        return {
            statusId: String(
                config.initialStatusId ?? ''
            ),

            moreStatusId: '',

            checkIn: String(
                config.checkIn ?? ''
            ),

            checkOut: String(
                config.checkOut ?? ''
            ),

            normalHours: String(
                config.normalHours ?? '0.00'
            ),

            otHours: String(
                config.otHours ?? '0.00'
            ),

            lineReason: String(
                config.lineReason ?? ''
            ),

            get isPresent() {
                return this.statusId
                    === String(
                        config.presentStatusId ?? ''
                    );
            },

            get isAbsent() {
                return this.statusId
                    === String(
                        config.absentStatusId ?? ''
                    );
            },

            setPresent() {
                this.statusId = String(
                    config.presentStatusId ?? ''
                );

                this.moreStatusId = '';
            },

            setAbsent() {
                this.statusId = String(
                    config.absentStatusId ?? ''
                );

                this.moreStatusId = '';
                this.checkOut = '';
                this.normalHours = '0.00';
                this.otHours = '0.00';
            },

            applyMoreStatus() {
                if (this.moreStatusId !== '') {
                    this.statusId =
                        this.moreStatusId;
                }
            },

            logoutAllPresent(logoutTime) {
                if (!this.isPresent || !this.checkIn) {
                    return;
                }

                this.checkOut = logoutTime;

                const result = this.calculateHours(
                    this.checkIn,
                    this.checkOut
                );

                if (!result) {
                    return;
                }

                this.normalHours =
                    result.normalHours.toFixed(2);

                this.otHours =
                    result.otHours.toFixed(2);

                if (!this.lineReason.trim()) {
                    this.lineReason =
                        'Bulk logout time correction for present labour.';
                }
            },

            calculateHours(checkIn, checkOut) {
                let checkInMinutes =
                    this.timeToMinutes(checkIn);

                let checkOutMinutes =
                    this.timeToMinutes(checkOut);

                let shiftStartMinutes =
                    this.timeToMinutes(
                        config.shiftStart
                    );

                let shiftEndMinutes =
                    this.timeToMinutes(
                        config.shiftEnd
                    );

                let otStartMinutes =
                    this.timeToMinutes(
                        config.otStart
                        || config.shiftEnd
                    );

                if (
                    checkInMinutes === null
                    || checkOutMinutes === null
                    || shiftStartMinutes === null
                    || shiftEndMinutes === null
                ) {
                    return null;
                }

                const crossesMidnight =
                    Boolean(config.crossesMidnight)
                    || shiftEndMinutes < shiftStartMinutes;

                if (crossesMidnight) {
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
                } else if (
                    checkOutMinutes < checkInMinutes
                ) {
                    checkOutMinutes += 1440;
                }

                const workedMinutes =
                    Math.max(
                        0,
                        checkOutMinutes - checkInMinutes
                    );

                const normalLimit =
                    Number(
                        config.normalHoursLimit
                        || 0
                    );

                const normalHours =
                    Math.min(
                        workedMinutes / 60,
                        normalLimit
                    );

                const effectiveOtStart =
                    otStartMinutes
                    ?? shiftEndMinutes;

                const graceOutMinutes =
                    Number(
                        config.graceOutMinutes
                        || 0
                    );

                const overtimeMinutes =
                    Math.max(
                        0,
                        checkOutMinutes
                        - effectiveOtStart
                        - graceOutMinutes
                    );

                return {
                    normalHours,
                    otHours:
                        overtimeMinutes / 60,
                };
            },

            timeToMinutes(time) {
                if (!time) {
                    return null;
                }

                const parts =
                    String(time).split(':');

                if (parts.length < 2) {
                    return null;
                }

                const hours =
                    Number(parts[0]);

                const minutes =
                    Number(parts[1]);

                if (
                    Number.isNaN(hours)
                    || Number.isNaN(minutes)
                ) {
                    return null;
                }

                return (
                    hours * 60
                    + minutes
                );
            }
        };
    }

    function attendanceCorrectionForm(config) {
        return {
            config,
            availableLabours: config.availableLabours ?? [],
            addRows: [],
            nextAddKey: 1000,

            init() {
                this.addRows = (config.initialAddRows ?? []).map((row) => {
                    const labourId = String(row.labour_id ?? '');
                    const labour = this.availableLabours.find(
                        (item) => String(item.id) === labourId
                    );

                    return {
                        key: this.nextAddKey++,
                        labour_id: labourId,
                        search: '',
                        selected_name: labour?.name ?? '',
                        selected_designation: labour?.designation ?? '',
                        selected_assignment: labour?.assignment ?? '',
                        status_id: String(
                            row.new_attendance_status_id
                            ?? ''
                        ),
                        more_status_id: '',
                        working_status_id: String(
                            row.new_working_status_id
                            ?? ''
                        ),
                        check_in_time: row.new_check_in_time ?? '',
                        check_out_time: row.new_check_out_time ?? '',
                        normal_hours: row.new_normal_hours ?? '0.00',
                        ot_hours: row.new_ot_hours ?? '0.00',
                        line_reason: row.line_reason ?? '',
                    };
                });
            },

            newLabourRow() {
                return {
                    key: this.nextAddKey++,
                    labour_id: '',
                    search: '',
                    selected_name: '',
                    selected_group: '',
                    selected_designation: '',
                    selected_assignment: '',
                    status_id: '',
                    more_status_id: '',
                    working_status_id: '',
                    check_in_time: '',
                    check_out_time: '',
                    normal_hours: '0.00',
                    ot_hours: '0.00',
                    line_reason: '',
                };
            },

            addLabourRow() {
                this.addRows.push(this.newLabourRow());
            },

            removeLabourRow(position) {
                this.addRows.splice(position, 1);
            },

            markAddPresent(row) {
                row.status_id = String(config.presentStatusId ?? '');
                row.more_status_id = '';

                if (!row.working_status_id) {
                    row.working_status_id = String(config.workingStatusId ?? '');
                }

                if (!row.check_in_time) {
                    row.check_in_time = config.defaultCheckIn ?? '';
                }

                row.check_out_time = '';
                row.normal_hours = '0.00';
                row.ot_hours = row.ot_hours || '0.00';
            },

            markAddAbsent(row) {
                row.status_id = String(config.absentStatusId ?? '');
                row.more_status_id = '';
                row.working_status_id = '';
                row.check_in_time = '';
                row.check_out_time = '';
                row.normal_hours = '0.00';
                row.ot_hours = '0.00';
            },

            logoutAddedLabour(row) {
                if (
                    String(row.status_id)
                    !== String(config.presentStatusId ?? '')
                ) {
                    return;
                }

                if (!row.check_in_time) {
                    row.check_in_time =
                        config.defaultCheckIn
                        ?? '';
                }

                row.check_out_time =
                    config.defaultCheckOut
                    ?? '';

                const result =
                    this.calculateAddedLabourHours(
                        row.check_in_time,
                        row.check_out_time
                    );

                if (!result) {
                    return;
                }

                row.normal_hours =
                    result.normalHours.toFixed(2);

                row.ot_hours =
                    result.otHours.toFixed(2);
            },

            calculateAddedLabourHours(
                checkIn,
                checkOut
            ) {
                let checkInMinutes =
                    this.timeToMinutes(checkIn);

                let checkOutMinutes =
                    this.timeToMinutes(checkOut);

                let shiftStartMinutes =
                    this.timeToMinutes(
                        config.defaultCheckIn
                    );

                let shiftEndMinutes =
                    this.timeToMinutes(
                        config.defaultCheckOut
                    );

                let otStartMinutes =
                    this.timeToMinutes(
                        config.defaultOtStart
                        || config.defaultCheckOut
                    );

                if (
                    checkInMinutes === null
                    || checkOutMinutes === null
                    || shiftStartMinutes === null
                    || shiftEndMinutes === null
                ) {
                    return null;
                }

                const crossesMidnight =
                    Boolean(
                        config.defaultCrossesMidnight
                    )
                    || shiftEndMinutes
                        < shiftStartMinutes;

                if (crossesMidnight) {
                    shiftEndMinutes += 1440;

                    if (
                        otStartMinutes !== null
                        && otStartMinutes
                            < shiftStartMinutes
                    ) {
                        otStartMinutes += 1440;
                    }

                    if (
                        checkOutMinutes
                        < checkInMinutes
                    ) {
                        checkOutMinutes += 1440;
                    }
                } else if (
                    checkOutMinutes
                    < checkInMinutes
                ) {
                    checkOutMinutes += 1440;
                }

                const workedMinutes =
                    Math.max(
                        0,
                        checkOutMinutes
                        - checkInMinutes
                    );

                const normalLimit =
                    Number(
                        config.defaultNormalHours
                        || 0
                    );

                const normalHours =
                    Math.min(
                        workedMinutes / 60,
                        normalLimit
                    );

                const effectiveOtStart =
                    otStartMinutes
                    ?? shiftEndMinutes;

                const graceOutMinutes =
                    Number(
                        config.defaultGraceOut
                        || 0
                    );

                const overtimeMinutes =
                    Math.max(
                        0,
                        checkOutMinutes
                        - effectiveOtStart
                        - graceOutMinutes
                    );

                return {
                    normalHours,
                    otHours:
                        overtimeMinutes / 60,
                };
            },

            timeToMinutes(time) {
                if (
                    !time
                    || !String(time).includes(':')
                ) {
                    return null;
                }

                const parts =
                    String(time)
                        .split(':')
                        .map(Number);

                if (
                    parts.some(
                        Number.isNaN
                    )
                ) {
                    return null;
                }

                return (
                    parts[0] * 60
                    + parts[1]
                );
            },

            normaliseSearch(value) {
                return String(value ?? '')
                    .trim()
                    .toLowerCase();
            },

            selectedLabourIdsExcept(row) {
                return this.addRows
                    .filter((item) => item.key !== row.key)
                    .map((item) => String(item.labour_id))
                    .filter((id) => id !== '');
            },

            filteredLabours(row) {
                const term = this.normaliseSearch(row.search);

                if (term.length < 2) {
                    return [];
                }

                const selectedIds = this.selectedLabourIdsExcept(row);

                return this.availableLabours
                    .filter((labour) => {
                        if (selectedIds.includes(String(labour.id))) {
                            return false;
                        }

                        const searchableText = [
                            labour.name,
                            labour.group_name,
                            labour.designation,
                            labour.mobile,
                            labour.assignment,
                        ]
                            .filter(Boolean)
                            .join(' ')
                            .toLowerCase();

                        return searchableText.includes(term);
                    })
                    .sort((a, b) => {
                        const assignmentRank = (value) =>
                            value === 'assigned' ? 0 : 1;

                        const assignmentDifference =
                            assignmentRank(a.assignment_type)
                            - assignmentRank(b.assignment_type);

                        if (assignmentDifference !== 0) {
                            return assignmentDifference;
                        }

                        const groupDifference = String(
                            a.group_name || ''
                        ).localeCompare(
                            String(b.group_name || '')
                        );

                        if (groupDifference !== 0) {
                            return groupDifference;
                        }

                        return String(a.name || '').localeCompare(
                            String(b.name || '')
                        );
                    })
                    .slice(0, 12);
            },

            selectLabour(row, labour) {
                row.labour_id = String(labour.id);
                row.selected_name = labour.name ?? '';
                row.selected_group = labour.group_name ?? 'Un-grouped Labour';
                row.selected_designation = labour.designation ?? '';
                row.selected_assignment = labour.assignment ?? '';
                row.search = '';
                row.status_id = '';
                row.more_status_id = '';
                row.working_status_id = '';
                row.check_in_time = '';
                row.check_out_time = '';
                row.normal_hours = '0.00';
                row.ot_hours = '0.00';
            },

            clearSelectedLabour(row) {
                row.labour_id = '';
                row.selected_name = '';
                row.selected_group = '';
                row.selected_designation = '';
                row.selected_assignment = '';
                row.search = '';
            },

            prepareSubmission(event) {
                const incompleteRow = this.addRows.find(
                    (row) => String(row.labour_id) === ''
                );

                if (incompleteRow) {
                    event.preventDefault();
                    alert(
                        'Search and select a labour for every Add Labour row, or remove the unused row.'
                    );
                    return false;
                }

                const selectedLabourIds = this.addRows
                    .map((row) => String(row.labour_id))
                    .filter((value) => value !== '');

                if (
                    selectedLabourIds.length
                    !== new Set(selectedLabourIds).size
                ) {
                    event.preventDefault();
                    alert('The same labour cannot be added more than once.');
                    return false;
                }

                return true;
            }
        };
    }
</script>

@endsection
