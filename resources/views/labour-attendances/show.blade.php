@extends('layouts.app')

@section('content')
<div class="mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                    Labour Attendance
                </h1>

                @php
                    $statusClasses = match ($labourAttendance->status) {
                        'approved' => 'border-green-200 bg-green-50 text-green-700',
                        'submitted' => 'border-blue-200 bg-blue-50 text-blue-700',
                        'rejected' => 'border-red-200 bg-red-50 text-red-700',
                        'reopened' => 'border-amber-200 bg-amber-50 text-amber-700',
                        default => 'border-gray-200 bg-gray-50 text-gray-700',
                    };
                @endphp

                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                    {{ $labourAttendance->display_status }}
                </span>

                @if ((int) $labourAttendance->revision_number > 0)
                    <span class="inline-flex rounded-full border border-purple-200 bg-purple-50 px-3 py-1 text-xs font-semibold text-purple-700">
                        Revision {{ $labourAttendance->revision_number }}
                    </span>
                @else
                    <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-600">
                        Original
                    </span>
                @endif
            </div>

            <p class="mt-2 text-sm text-gray-600">
                Attendance Number:
                <span class="font-semibold text-gray-900">
                    {{ $labourAttendance->attendance_number }}
                </span>
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a
                href="{{ route('labour-attendances.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
            >
                Back to Attendance
            </a>

            @if ($labourAttendance->canBeEdited())
                <a
                    href="{{ route('labour-attendances.edit', $labourAttendance) }}"
                    class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600"
                >
                    Edit Attendance
                </a>
            @endif
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3">
            <p class="text-sm font-semibold text-red-800">
                Please correct the following:
            </p>

            <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Attendance Information --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="text-base font-semibold text-gray-900">
                Attendance Information
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-x-6 gap-y-5 p-5 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                    Project
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $labourAttendance->project?->project_name ?? '—' }}
                </p>

                <p class="mt-1 text-xs text-gray-500">
                    {{ $labourAttendance->project?->project_code ?? '—' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                    Attendance Date
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $labourAttendance->attendance_date?->format('d M Y') ?? '—' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                    Shift
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $labourAttendance->shift?->name ?? 'General' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                    Recorded By
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $labourAttendance->recordedBy?->name ?? '—' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                    Status
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $labourAttendance->display_status }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                    Revision
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $labourAttendance->revision_label }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                    Active Status
                </p>

                <p class="mt-1 text-sm font-semibold {{ $labourAttendance->is_active ? 'text-green-700' : 'text-red-700' }}">
                    {{ $labourAttendance->is_active ? 'Active' : 'Inactive' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                    Created At
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $labourAttendance->created_at?->format('d M Y, h:i A') ?? '—' }}
                </p>
            </div>

            @if ($labourAttendance->remarks)
                <div class="sm:col-span-2 lg:col-span-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        Remarks
                    </p>

                    <p class="mt-1 whitespace-pre-line text-sm leading-6 text-gray-700">
                        {{ $labourAttendance->remarks }}
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Attendance Summary --}}
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-7">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                Total Labour
            </p>

            <p class="mt-2 text-2xl font-bold text-gray-900">
                {{ number_format($labourAttendance->total_labours) }}
            </p>
        </div>

        <div class="rounded-xl border border-green-200 bg-green-50 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-green-700">
                Present
            </p>

            <p class="mt-2 text-2xl font-bold text-green-800">
                {{ number_format($labourAttendance->present_count) }}
            </p>
        </div>

        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-red-700">
                Absent
            </p>

            <p class="mt-2 text-2xl font-bold text-red-800">
                {{ number_format($labourAttendance->absent_count) }}
            </p>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-amber-700">
                Half Day
            </p>

            <p class="mt-2 text-2xl font-bold text-amber-800">
                {{ number_format($labourAttendance->half_day_count) }}
            </p>
        </div>

        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-blue-700">
                Leave
            </p>

            <p class="mt-2 text-2xl font-bold text-blue-800">
                {{ number_format($labourAttendance->leave_count) }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                Normal Hours
            </p>

            <p class="mt-2 text-2xl font-bold text-gray-900">
                {{ number_format((float) $labourAttendance->total_normal_hours, 2) }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                OT Hours
            </p>

            <p class="mt-2 text-2xl font-bold text-gray-900">
                {{ number_format((float) $labourAttendance->total_ot_hours, 2) }}
            </p>
        </div>
    </div>

    {{-- Workflow Information --}}
    @if (
        $labourAttendance->submitted_at
        || $labourAttendance->approved_at
        || $labourAttendance->rejected_at
        || $labourAttendance->reopened_at
    )
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="text-base font-semibold text-gray-900">
                    Workflow History
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2 xl:grid-cols-4">
                @if ($labourAttendance->submitted_at)
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Submitted
                        </p>

                        <p class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $labourAttendance->submittedBy?->name ?? '—' }}
                        </p>

                        <p class="mt-1 text-xs text-gray-500">
                            {{ $labourAttendance->submitted_at->format('d M Y, h:i A') }}
                        </p>
                    </div>
                @endif

                @if ($labourAttendance->approved_at)
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Approved
                        </p>

                        <p class="mt-1 text-sm font-semibold text-green-700">
                            {{ $labourAttendance->approvedBy?->name ?? '—' }}
                        </p>

                        <p class="mt-1 text-xs text-gray-500">
                            {{ $labourAttendance->approved_at->format('d M Y, h:i A') }}
                        </p>
                    </div>
                @endif

                @if ($labourAttendance->rejected_at)
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Rejected
                        </p>

                        <p class="mt-1 text-sm font-semibold text-red-700">
                            {{ $labourAttendance->rejectedBy?->name ?? '—' }}
                        </p>

                        <p class="mt-1 text-xs text-gray-500">
                            {{ $labourAttendance->rejected_at->format('d M Y, h:i A') }}
                        </p>
                    </div>
                @endif

                @if ($labourAttendance->reopened_at)
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Reopened
                        </p>

                        <p class="mt-1 text-sm font-semibold text-amber-700">
                            {{ $labourAttendance->reopenedBy?->name ?? '—' }}
                        </p>

                        <p class="mt-1 text-xs text-gray-500">
                            {{ $labourAttendance->reopened_at->format('d M Y, h:i A') }}
                        </p>
                    </div>
                @endif
            </div>

            @if ($labourAttendance->rejection_reason)
                <div class="border-t border-red-200 bg-red-50 px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-700">
                        Rejection Reason
                    </p>

                    <p class="mt-2 whitespace-pre-line text-sm text-red-800">
                        {{ $labourAttendance->rejection_reason }}
                    </p>
                </div>
            @endif

            @if ($labourAttendance->reopen_reason)
                <div class="border-t border-amber-200 bg-amber-50 px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">
                        Reopen Reason
                    </p>

                    <p class="mt-2 whitespace-pre-line text-sm text-amber-800">
                        {{ $labourAttendance->reopen_reason }}
                    </p>
                </div>
            @endif
        </div>
    @endif

    {{-- Labour Attendance Details --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-gray-900">
                    Labour Attendance Details
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $labourAttendance->details->count() }}
                    labour record{{ $labourAttendance->details->count() === 1 ? '' : 's' }}
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            #
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Labour
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Category
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Trade / Type
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Designation
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Contractor
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Status
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Normal Hours
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                            OT Hours
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                            OT Amount
                        </th>

                        <th class="min-w-52 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Remarks
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($labourAttendance->details as $detail)
                        @php
                            $statusCode = strtoupper(
                                trim((string) ($detail->attendanceStatus?->code ?? ''))
                            );

                            $detailStatusClasses = match ($statusCode) {
                                'P', 'PRESENT' =>
                                    'border-green-200 bg-green-50 text-green-700',

                                'A', 'ABSENT' =>
                                    'border-red-200 bg-red-50 text-red-700',

                                'HD', 'HALF_DAY', 'HALFDAY' =>
                                    'border-amber-200 bg-amber-50 text-amber-700',

                                'L', 'LEAVE' =>
                                    'border-blue-200 bg-blue-50 text-blue-700',

                                default =>
                                    'border-gray-200 bg-gray-50 text-gray-700',
                            };
                        @endphp

                        <tr class="align-top hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-500">
                                {{ $loop->iteration }}
                            </td>

                            <td class="min-w-52 px-4 py-4">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $detail->labour?->full_name ?? '—' }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $detail->labour?->labour_code ?? '—' }}
                                </p>
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700">
                                {{ $detail->labourCategory?->category_name ?? '—' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700">
                                {{ $detail->labourType?->labour_type_name ?? '—' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700">
                                {{ $detail->designationRole?->name ?? '—' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700">
                                {{ $detail->contractor?->contractor_name ?? 'Direct Labour' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $detailStatusClasses }}">
                                    {{ $detail->attendanceStatus?->name
                                        ?? $detail->attendanceStatus?->short_name
                                        ?? '—' }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-medium text-gray-900">
                                {{ number_format((float) $detail->normal_hours, 2) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-medium text-gray-900">
                                {{ number_format((float) $detail->ot_hours, 2) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold text-gray-900">
                                ₹{{ number_format((float) ($detail->ot_amount ?? 0), 2) }}
                            </td>

                            <td class="px-4 py-4 text-sm text-gray-600">
                                {{ $detail->remarks ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-12 text-center">
                                <p class="text-sm font-semibold text-gray-900">
                                    No labour attendance details found.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if ($labourAttendance->details->isNotEmpty())
                    <tfoot class="border-t border-gray-200 bg-gray-50">
                        <tr>
                            <td colspan="7" class="px-4 py-3 text-right text-sm font-semibold text-gray-700">
                                Totals
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-gray-900">
                                {{ number_format((float) $labourAttendance->total_normal_hours, 2) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-gray-900">
                                {{ number_format((float) $labourAttendance->total_ot_hours, 2) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-gray-900">
                                ₹{{ number_format((float) $labourAttendance->details->sum('ot_amount'), 2) }}
                            </td>

                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Workflow Actions --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="text-base font-semibold text-gray-900">
            Workflow Actions
        </h2>

        <div class="mt-4 flex flex-wrap gap-3">
            @if ($labourAttendance->canBeSubmitted())
                <form
                    method="POST"
                    action="{{ route('labour-attendances.submit', $labourAttendance) }}"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        onclick="return confirm('Submit this attendance sheet for approval?');"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
                    >
                        Submit for Approval
                    </button>
                </form>
            @endif

            @if ($labourAttendance->canBeApproved())
                <form
                    method="POST"
                    action="{{ route('labour-attendances.approve', $labourAttendance) }}"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        onclick="return confirm('Approve this attendance sheet?');"
                        class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700"
                    >
                        Approve Attendance
                    </button>
                </form>
            @endif
        </div>

        @if ($labourAttendance->canBeRejected())
            <div class="mt-5 rounded-lg border border-red-200 bg-red-50 p-4">
                <form
                    method="POST"
                    action="{{ route('labour-attendances.reject', $labourAttendance) }}"
                    class="space-y-3"
                >
                    @csrf
                    @method('PATCH')

                    <div>
                        <label
                            for="rejection_reason"
                            class="mb-1 block text-sm font-semibold text-red-800"
                        >
                            Rejection Reason
                            <span class="text-red-600">*</span>
                        </label>

                        <textarea
                            id="rejection_reason"
                            name="rejection_reason"
                            rows="3"
                            required
                            maxlength="2000"
                            placeholder="Enter the reason for rejecting this attendance sheet."
                            class="block w-full rounded-lg border-red-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500"
                        >{{ old('rejection_reason') }}</textarea>
                    </div>

                    <button
                        type="submit"
                        onclick="return confirm('Reject this attendance sheet?');"
                        class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700"
                    >
                        Reject Attendance
                    </button>
                </form>
            </div>
        @endif

        @if ($labourAttendance->canBeReopened())
            <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4">
                <form
                    method="POST"
                    action="{{ route('labour-attendances.reopen', $labourAttendance) }}"
                    class="space-y-3"
                >
                    @csrf
                    @method('PATCH')

                    <div>
                        <label
                            for="reopen_reason"
                            class="mb-1 block text-sm font-semibold text-amber-800"
                        >
                            Reason for Reopening
                            <span class="text-red-600">*</span>
                        </label>

                        <textarea
                            id="reopen_reason"
                            name="reopen_reason"
                            rows="3"
                            required
                            maxlength="2000"
                            placeholder="Explain why this approved attendance must be corrected."
                            class="block w-full rounded-lg border-amber-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                        >{{ old('reopen_reason') }}</textarea>
                    </div>

                    <button
                        type="submit"
                        onclick="return confirm('Reopen this approved attendance sheet for correction?');"
                        class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700"
                    >
                        Reopen for Correction
                    </button>
                </form>
            </div>
        @endif

        @if (
            ! $labourAttendance->canBeSubmitted()
            && ! $labourAttendance->canBeApproved()
            && ! $labourAttendance->canBeRejected()
            && ! $labourAttendance->canBeReopened()
        )
            <p class="mt-4 text-sm text-gray-500">
                No workflow actions are currently available for this attendance sheet.
            </p>
        @endif
    </div>

</div>
@endsection