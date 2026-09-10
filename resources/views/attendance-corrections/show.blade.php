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

            <button
                type="button"
                @click="$dispatch('open-delete-attendance-correction')"
                class="inline-flex items-center justify-center rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 shadow-sm transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
            >
                Delete Correction
            </button>
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

    $hasTypeChange =
        ($attendanceCorrection->old_attendance_type ?: 'regular')
        !==
        ($attendanceCorrection->new_attendance_type
            ?: $attendanceCorrection->old_attendance_type
            ?: 'regular');

    $hasSessionChange =
        ($attendanceCorrection->old_work_session_name ?: null)
        !==
        ($attendanceCorrection->new_work_session_name ?: null);

    $labourChangeCount = $attendanceCorrection->details->count();

    $totalChangeCount =
        $labourChangeCount
        + ($hasDateChange ? 1 : 0)
        + ($hasTypeChange ? 1 : 0)
        + ($hasSessionChange ? 1 : 0);

    $attendanceTypeLabel = fn (?string $type): string =>
        ($type ?: 'regular') === 'additional_work'
            ? 'Additional Work'
            : 'Regular Attendance';
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
                    @if($hasTypeChange)
                        <span class="text-gray-500 line-through">
                            {{ $attendanceTypeLabel($attendanceCorrection->old_attendance_type) }}
                        </span>
                        <span class="mx-1 text-gray-400">→</span>
                        <span class="text-blue-700">
                            {{ $attendanceTypeLabel($attendanceCorrection->new_attendance_type) }}
                        </span>
                    @else
                        {{ $attendanceTypeLabel(
                            $attendanceCorrection->new_attendance_type
                            ?: $attendanceCorrection->old_attendance_type
                            ?: $attendanceCorrection->labourAttendance?->attendance_type
                        ) }}
                    @endif

                    @if(
                        ($attendanceCorrection->new_attendance_type ?: $attendanceCorrection->old_attendance_type) === 'additional_work'
                        && $attendanceCorrection->new_work_session_name
                    )
                        <span class="block text-xs font-medium text-blue-700">
                            {{ $attendanceCorrection->new_work_session_name }}
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
                    @php
                        $summaryParts = [];

                        if ($hasDateChange) {
                            $summaryParts[] = '1 Date Change';
                        }

                        if ($hasTypeChange) {
                            $summaryParts[] = '1 Type Change';
                        }

                        if ($hasSessionChange) {
                            $summaryParts[] = '1 Session Change';
                        }

                        if ($labourChangeCount > 0) {
                            $summaryParts[] =
                                $labourChangeCount
                                . ' Labour '
                                . ($labourChangeCount === 1 ? 'Change' : 'Changes');
                        }
                    @endphp

                    {{ ! empty($summaryParts)
                        ? implode(' · ', $summaryParts)
                        : 'No changes' }}
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

    @if($hasTypeChange || $hasSessionChange)
        <x-rds.card>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">
                        Attendance Type / Work Session Change
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Header-level reclassification of the approved attendance sheet.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Attendance Type
                        </div>

                        <div class="mt-1 font-semibold">
                            <span class="{{ $hasTypeChange ? 'text-gray-500 line-through' : 'text-gray-900' }}">
                                {{ $attendanceTypeLabel($attendanceCorrection->old_attendance_type) }}
                            </span>

                            @if($hasTypeChange)
                                <span class="mx-2 text-gray-400">→</span>
                                <span class="text-blue-700">
                                    {{ $attendanceTypeLabel($attendanceCorrection->new_attendance_type) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Work Session
                        </div>

                        <div class="mt-1 font-semibold">
                            @if($hasSessionChange)
                                <span class="text-gray-500 line-through">
                                    {{ $attendanceCorrection->old_work_session_name ?: '—' }}
                                </span>
                                <span class="mx-2 text-gray-400">→</span>
                                <span class="text-blue-700">
                                    {{ $attendanceCorrection->new_work_session_name ?: '—' }}
                                </span>
                            @else
                                {{ $attendanceCorrection->new_work_session_name
                                    ?: $attendanceCorrection->old_work_session_name
                                    ?: '—' }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </x-rds.card>
    @endif

    @if(
        $attendanceCorrection->status === 'rejected'
        && filled($attendanceCorrection->rejection_reason)
    )
        <x-rds.card>
            <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-red-700">Rejection Reason</div>
                <div class="mt-2 whitespace-pre-line text-sm font-medium text-red-900">
                    {{ $attendanceCorrection->rejection_reason }}
                </div>
                @if($attendanceCorrection->rejected_at)
                    <div class="mt-2 text-xs text-red-700">
                        Rejected on {{ $attendanceCorrection->rejected_at->format('d M Y, h:i A') }}
                    </div>
                @endif
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
                                {{ ($hasDateChange || $hasTypeChange || $hasSessionChange)
                                    ? 'No separate labour-row changes were included beyond the header-level correction.'
                                    : 'No correction details were found.' }}
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </x-rds.card>

    @if($attendanceCorrection->status === 'draft')
        <x-rds.card>
            <div class="flex justify-end">
                <form method="POST" action="{{ route('attendance-corrections.submit', $attendanceCorrection) }}">
                    @csrf
                    <x-rds.button type="submit" variant="primary">Submit for Approval</x-rds.button>
                </form>
            </div>
        </x-rds.card>
    @elseif($attendanceCorrection->status === 'submitted')
        <x-rds.card>
            <div x-data="{ rejectOpen: {{ $errors->has('rejection_reason') ? 'true' : 'false' }} }"
                 class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">

                <button type="button" @click="rejectOpen = true"
                    class="inline-flex items-center justify-center rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 shadow-sm hover:bg-red-50">
                    Reject Correction
                </button>

                <form method="POST" action="{{ route('attendance-corrections.approve', $attendanceCorrection) }}">
                    @csrf
                    <x-rds.button type="submit" variant="primary">Approve Correction</x-rds.button>
                </form>

                <div x-cloak x-show="rejectOpen" x-transition.opacity
                     @keydown.escape.window="rejectOpen = false"
                     class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4"
                     role="dialog" aria-modal="true">
                    <div @click.outside="rejectOpen = false"
                         class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">
                        <form method="POST" action="{{ route('attendance-corrections.reject', $attendanceCorrection) }}">
                            @csrf

                            <div class="border-b border-gray-200 px-6 py-5">
                                <h2 class="text-lg font-bold text-gray-900">Reject Attendance Correction</h2>
                                <p class="mt-1 text-sm text-gray-500">
                                    Enter the reason for rejection. The correction can then be edited and resubmitted.
                                </p>
                            </div>

                            <div class="px-6 py-5">
                                <label for="rejection_reason" class="block text-sm font-semibold text-gray-700">
                                    Rejection Reason <span class="text-red-600">*</span>
                                </label>
                                <textarea id="rejection_reason" name="rejection_reason" rows="5"
                                    minlength="3" maxlength="2000" required autofocus
                                    placeholder="Explain what must be corrected before this request can be approved."
                                    class="mt-2 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">{{ old('rejection_reason') }}</textarea>
                                @error('rejection_reason')
                                    <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-end gap-3 rounded-b-2xl border-t border-gray-200 bg-gray-50 px-6 py-4">
                                <button type="button" @click="rejectOpen = false"
                                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                                    Confirm Reject
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </x-rds.card>
    @elseif($attendanceCorrection->status === 'approved')
        <x-rds.card>
            <div class="flex justify-end">
                <form method="POST" action="{{ route('attendance-corrections.apply', $attendanceCorrection) }}">
                    @csrf
                    <x-rds.button type="submit" variant="primary">Apply Approved Correction</x-rds.button>
                </form>
            </div>
        </x-rds.card>
    @endif

    @if($attendanceCorrection->canBeEdited())
        <div
            x-data="{ open: false }"
            @open-delete-attendance-correction.window="open = true"
            @keydown.escape.window="open = false"
        >
            <div
                x-cloak
                x-show="open"
                x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4"
                role="dialog"
                aria-modal="true"
                aria-labelledby="delete-correction-title"
            >
                <div
                    @click.outside="open = false"
                    class="w-full max-w-lg rounded-2xl bg-white shadow-2xl"
                >
                    <div class="border-b border-gray-200 px-6 py-5">
                        <h2
                            id="delete-correction-title"
                            class="text-lg font-bold text-gray-900"
                        >
                            Delete Attendance Correction?
                        </h2>

                        <p class="mt-2 text-sm text-gray-600">
                            You are about to delete
                            <span class="font-semibold text-gray-900">
                                {{ $attendanceCorrection->correction_number }}
                            </span>
                            and its proposed correction changes.
                        </p>
                    </div>

                    <div class="px-6 py-5">
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <div class="text-sm font-semibold text-amber-900">
                                The original approved attendance will not be affected.
                            </div>

                            <div class="mt-1 text-sm text-amber-800">
                                Only the Draft/Rejected correction request and its proposal rows will be deleted.
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 rounded-b-2xl border-t border-gray-200 bg-gray-50 px-6 py-4">
                        <button
                            type="button"
                            @click="open = false"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                        >
                            Cancel
                        </button>

                        <form
                            method="POST"
                            action="{{ route('attendance-corrections.destroy', $attendanceCorrection) }}"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                            >
                                Delete Correction
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

@endsection