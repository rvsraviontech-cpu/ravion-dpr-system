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

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

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
                    {{ $attendanceCorrection->attendance_date?->format('d M Y') ?? '—' }}
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Total Changes
                </div>

                <div class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $attendanceCorrection->details->count() }}
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
                                No correction details were found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </x-rds.card>

</div>

@endsection