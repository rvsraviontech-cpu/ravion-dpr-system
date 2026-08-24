@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Attendance Correction"
    subtitle="Review the attendance correction request and its proposed changes."
>
    <x-slot:actions>
        <x-rds.button
            href="{{ route('attendance-corrections.index') }}"
            variant="secondary"
        >
            Back to Corrections
        </x-rds.button>

        @if($attendanceCorrection->canBeEdited())
            <x-rds.button
                href="{{ route(
                    'attendance-corrections.edit',
                    $attendanceCorrection
                ) }}"
                variant="primary"
            >
                Edit Correction
            </x-rds.button>
        @endif
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

@php
    $hasDateChange =
        $attendanceCorrection->old_attendance_date
        && $attendanceCorrection->new_attendance_date
        && ! $attendanceCorrection->new_attendance_date->isSameDay(
            $attendanceCorrection->old_attendance_date
        );

    $labourChangeCount = $attendanceCorrection->details->count();

    $totalChangeCount =
        $labourChangeCount
        + ($hasDateChange ? 1 : 0);
@endphp

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4">
        <div class="text-sm font-semibold text-red-800">
            Attendance Correction could not be applied.
        </div>

        <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-6">

    <x-rds.card>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Correction Number
                </div>

                <div class="mt-1 text-xl font-bold text-gray-900">
                    {{ $attendanceCorrection->correction_number }}
                </div>
            </div>

            @php
                $statusVariant = match ($attendanceCorrection->status) {
                    'submitted' => 'warning',
                    'approved', 'applied' => 'success',
                    'rejected' => 'danger',
                    default => 'secondary',
                };
            @endphp

            <x-rds.badge :variant="$statusVariant">
                {{ $attendanceCorrection->display_status }}
            </x-rds.badge>

        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Attendance Number
                </div>

                <div class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $attendanceCorrection->labourAttendance?->attendance_number ?? '—' }}
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Project
                </div>

                <div class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $attendanceCorrection->project?->project_name ?? '—' }}
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Attendance Date
                </div>

                <div class="mt-1 text-sm font-semibold text-gray-900">
                    @if($hasDateChange)
                        <span class="text-gray-500 line-through">{{ $attendanceCorrection->old_attendance_date->format('d M Y') }}</span>
                        <span class="mx-1 text-gray-400">→</span>
                        <span class="text-blue-700">{{ $attendanceCorrection->new_attendance_date->format('d M Y') }}</span>
                    @else
                        {{ ($attendanceCorrection->new_attendance_date ?? $attendanceCorrection->attendance_date)?->format('d M Y') ?? '—' }}
                    @endif
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Attendance Type
                </div>

                <div class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $attendanceCorrection->labourAttendance?->display_attendance_type ?? 'Regular Attendance' }}
                    @if($attendanceCorrection->labourAttendance?->isAdditionalWork() && $attendanceCorrection->labourAttendance?->work_session_name)
                        <span class="block text-xs font-medium text-blue-700">
                            {{ $attendanceCorrection->labourAttendance->work_session_name }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Total Changes
                </div>

                <div class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $totalChangeCount }}
                </div>

                <div class="mt-1 text-xs text-gray-500">
                    @if($hasDateChange)
                        1 Date Change
                        @if($labourChangeCount > 0)
                            ·
                        @endif
                    @endif

                    @if($labourChangeCount > 0)
                        {{ $labourChangeCount }}
                        Labour {{ $labourChangeCount === 1 ? 'Change' : 'Changes' }}
                    @elseif(!$hasDateChange)
                        No changes
                    @endif
                </div>
            </div>

        </div>
    </x-rds.card>

    <x-rds.card>
        <h2 class="text-base font-semibold text-gray-900">
            Correction Reason
        </h2>

        <div class="mt-3 whitespace-pre-line text-sm text-gray-700">
            {{ $attendanceCorrection->correction_reason }}
        </div>
    </x-rds.card>

    @if($hasDateChange)
        <x-rds.card>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">
                        Attendance Date Change
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        This is a header-level correction to the approved attendance sheet.
                    </p>
                </div>

                <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold">
                    <span class="text-gray-500 line-through">
                        {{ $attendanceCorrection->old_attendance_date->format('d M Y') }}
                    </span>

                    <span class="mx-2 text-gray-400">→</span>

                    <span class="text-blue-700">
                        {{ $attendanceCorrection->new_attendance_date->format('d M Y') }}
                    </span>
                </div>
            </div>
        </x-rds.card>
    @endif

    <x-rds.card :padding="false">

        <div class="border-b border-gray-200 px-4 py-4">
            <h2 class="text-base font-semibold text-gray-900">
                Proposed Attendance Changes
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Review labour additions and changes made to the approved attendance sheet.
            </p>
        </div>

        <div class="w-full overflow-x-auto">

            <table class="w-full min-w-[1300px] divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Labour
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Action
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Attendance
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Working Status
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Check In
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Check Out
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Normal
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            OT Hours
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            OT Amount
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Reason
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">

                    @forelse($attendanceCorrection->details as $detail)

                        <tr>
                            <td class="px-4 py-3">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $detail->labour?->full_name ?? 'Unknown Labour' }}
                                </div>
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $detail->action_label }}
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $detail->newAttendanceStatus?->name
                                    ?? $detail->oldAttendanceStatus?->name
                                    ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $detail->newWorkingStatus?->name
                                    ?? $detail->oldWorkingStatus?->name
                                    ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $detail->new_check_in_time
                                    ? substr($detail->new_check_in_time, 0, 5)
                                    : '—' }}
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $detail->new_check_out_time
                                    ? substr($detail->new_check_out_time, 0, 5)
                                    : '—' }}
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $detail->new_normal_hours !== null
                                    ? number_format((float) $detail->new_normal_hours, 2)
                                    : '—' }}
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $detail->new_ot_hours !== null
                                    ? number_format((float) $detail->new_ot_hours, 2)
                                    : '—' }}
                            </td>

                            <td class="px-4 py-3 text-sm font-semibold text-gray-700">
                                {{ $detail->new_ot_amount !== null
                                    ? '₹' . number_format((float) $detail->new_ot_amount, 2)
                                    : '—' }}
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $detail->line_reason }}
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="10"
                                class="px-4 py-10 text-center text-sm text-gray-500"
                            >
                                {{ $hasDateChange
                                    ? 'No labour-row changes were included in this correction.'
                                    : 'No correction details were found.' }}
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </x-rds.card>

</div>

@endsection