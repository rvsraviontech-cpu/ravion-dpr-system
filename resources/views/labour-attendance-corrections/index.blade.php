@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-full space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                Attendance Corrections
            </h1>

            <p class="mt-1 max-w-3xl text-sm text-gray-600">
                Review and correct existing Labour Attendance sheets through the
                controlled reopen, edit, resubmit, and approval workflow.
            </p>
        </div>

        <a
            href="{{ route('labour-attendances.index') }}"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
            Labour Attendance
        </a>
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

    {{-- Information Card --}}
    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
        <div class="flex items-start gap-3">
            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-700">
                i
            </div>

            <div>
                <h2 class="text-sm font-semibold text-blue-900">
                    Controlled correction workflow
                </h2>

                <p class="mt-1 text-sm leading-6 text-blue-800">
                    Approved attendance must be reopened with a reason before it
                    can be edited. After correction, the attendance must be
                    submitted and approved again. All actions remain available
                    in the Audit Trail.
                </p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <form
            method="GET"
            action="{{ route('labour-attendance-corrections.index') }}"
            class="space-y-4"
        >
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">

                {{-- Search --}}
                <div class="md:col-span-2">
                    <label
                        for="search"
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Search
                    </label>

                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Attendance number, project code or project name"
                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                </div>

                {{-- Project --}}
                <div>
                    <label
                        for="project_id"
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Project
                    </label>

                    <select
                        id="project_id"
                        name="project_id"
                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">All Projects</option>

                        @foreach ($projects as $project)
                            <option
                                value="{{ $project->id }}"
                                @selected(
                                    (string) request('project_id')
                                    === (string) $project->id
                                )
                            >
                                {{ $project->project_code }}
                                -
                                {{ $project->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Shift --}}
                <div>
                    <label
                        for="shift_id"
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Shift
                    </label>

                    <select
                        id="shift_id"
                        name="shift_id"
                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">All Shifts</option>

                        @foreach ($shifts as $shift)
                            <option
                                value="{{ $shift->id }}"
                                @selected(
                                    (string) request('shift_id')
                                    === (string) $shift->id
                                )
                            >
                                {{ $shift->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label
                        for="status"
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Status
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">All Correction Statuses</option>

                        @foreach ($statuses as $statusValue => $statusLabel)
                            <option
                                value="{{ $statusValue }}"
                                @selected(
                                    request('status') === $statusValue
                                )
                            >
                                {{ $statusLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Attendance Date --}}
                <div>
                    <label
                        for="attendance_date"
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Attendance Date
                    </label>

                    <input
                        type="date"
                        id="attendance_date"
                        name="attendance_date"
                        value="{{ request('attendance_date') }}"
                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                </div>

                {{-- Date From --}}
                <div>
                    <label
                        for="date_from"
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Date From
                    </label>

                    <input
                        type="date"
                        id="date_from"
                        name="date_from"
                        value="{{ request('date_from') }}"
                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                </div>

                {{-- Date To --}}
                <div>
                    <label
                        for="date_to"
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Date To
                    </label>

                    <input
                        type="date"
                        id="date_to"
                        name="date_to"
                        value="{{ request('date_to') }}"
                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 border-t border-gray-100 pt-4">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Apply Filters
                </button>

                <a
                    href="{{ route('labour-attendance-corrections.index') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                >
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Attendance Corrections Queue --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        {{-- Card Header --}}
        <div class="flex flex-col gap-2 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-gray-900">
                    Correction Queue
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $labourAttendances->total() }}
                    attendance sheet{{ $labourAttendances->total() === 1 ? '' : 's' }}
                    found
                </p>
            </div>

            <div class="text-xs text-gray-500">
                Approved, reopened, rejected, and submitted sheets
            </div>
        </div>

        {{-- Desktop Table --}}
        <div class="hidden overflow-x-auto lg:block">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Attendance
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Project
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Date
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Shift
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Labour
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Status
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Revision
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($labourAttendances as $labourAttendance)
                        @php
                            $statusClasses = match ($labourAttendance->status) {
                                'approved' =>
                                    'border-green-200 bg-green-50 text-green-700',

                                'submitted' =>
                                    'border-blue-200 bg-blue-50 text-blue-700',

                                'rejected' =>
                                    'border-red-200 bg-red-50 text-red-700',

                                'reopened' =>
                                    'border-amber-200 bg-amber-50 text-amber-700',

                                default =>
                                    'border-gray-200 bg-gray-50 text-gray-700',
                            };

                            $canEdit = in_array(
                                $labourAttendance->status,
                                ['reopened', 'rejected', 'draft'],
                                true
                            );

                            $canReopen =
                                $labourAttendance->status === 'approved'
                                && $labourAttendance->is_active;
                        @endphp

                        <tr class="align-top hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-4">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $labourAttendance->attendance_number }}
                                </div>

                                <div class="mt-1 text-xs text-gray-500">
                                    ID: {{ $labourAttendance->id }}
                                </div>
                            </td>

                            <td class="min-w-64 px-4 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $labourAttendance->project?->project_name ?? '—' }}
                                </div>

                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $labourAttendance->project?->project_code ?? 'No project code' }}
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700">
                                {{ $labourAttendance->attendance_date?->format('d M Y') ?? '—' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700">
                                {{ $labourAttendance->shift?->name ?? 'General' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ number_format($labourAttendance->details_count) }}
                                </div>

                                <div class="text-xs text-gray-500">
                                    labour rows
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">
                                    {{ $labourAttendance->display_status }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                @if ((int) $labourAttendance->revision_number > 0)
                                    <span class="inline-flex rounded-full border border-purple-200 bg-purple-50 px-2.5 py-1 text-xs font-semibold text-purple-700">
                                        Revision {{ $labourAttendance->revision_number }}
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                        Original
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex flex-wrap items-start justify-end gap-2">
                                    <a
                                        href="{{ route('labour-attendances.show', $labourAttendance) }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                                    >
                                        View
                                    </a>

                                    @if ($canEdit)
                                        <a
                                            href="{{ route('labour-attendances.edit', $labourAttendance) }}"
                                            class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-600"
                                        >
                                            Edit Correction
                                        </a>
                                    @endif
                                </div>

                                @if ($canReopen)
                                    <details class="mt-3 rounded-lg border border-amber-200 bg-amber-50">
                                        <summary class="cursor-pointer list-none px-3 py-2 text-right text-xs font-semibold text-amber-800">
                                            Reopen Attendance
                                        </summary>

                                        <form
                                            method="POST"
                                            action="{{ route('labour-attendances.reopen', $labourAttendance) }}"
                                            class="space-y-3 border-t border-amber-200 p-3 text-left"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <div>
                                                <label
                                                    for="reopen_reason_{{ $labourAttendance->id }}"
                                                    class="mb-1 block text-xs font-semibold text-gray-700"
                                                >
                                                    Reason for correction
                                                    <span class="text-red-600">*</span>
                                                </label>

                                                <textarea
                                                    id="reopen_reason_{{ $labourAttendance->id }}"
                                                    name="reopen_reason"
                                                    rows="3"
                                                    required
                                                    maxlength="2000"
                                                    placeholder="Explain why this approved attendance must be corrected."
                                                    class="block w-full min-w-72 rounded-lg border-gray-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                                ></textarea>
                                            </div>

                                            <div class="flex justify-end">
                                                <button
                                                    type="submit"
                                                    onclick="return confirm('Reopen this approved attendance sheet for correction?');"
                                                    class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-700"
                                                >
                                                    Confirm Reopen
                                                </button>
                                            </div>
                                        </form>
                                    </details>
                                @endif

                                @if ($labourAttendance->status === 'submitted')
                                    <p class="mt-2 text-right text-xs text-blue-600">
                                        Awaiting approval
                                    </p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-14 text-center">
                                <div class="mx-auto max-w-md">
                                    <div class="text-base font-semibold text-gray-900">
                                        No attendance corrections found
                                    </div>

                                    <p class="mt-2 text-sm leading-6 text-gray-500">
                                        No approved, reopened, rejected, or submitted
                                        attendance sheets match the selected filters.
                                    </p>

                                    <a
                                        href="{{ route('labour-attendance-corrections.index') }}"
                                        class="mt-4 inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                                    >
                                        Clear Filters
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile and Tablet Cards --}}
        <div class="divide-y divide-gray-200 lg:hidden">
            @forelse ($labourAttendances as $labourAttendance)
                @php
                    $statusClasses = match ($labourAttendance->status) {
                        'approved' =>
                            'border-green-200 bg-green-50 text-green-700',

                        'submitted' =>
                            'border-blue-200 bg-blue-50 text-blue-700',

                        'rejected' =>
                            'border-red-200 bg-red-50 text-red-700',

                        'reopened' =>
                            'border-amber-200 bg-amber-50 text-amber-700',

                        default =>
                            'border-gray-200 bg-gray-50 text-gray-700',
                    };

                    $canEdit = in_array(
                        $labourAttendance->status,
                        ['reopened', 'rejected', 'draft'],
                        true
                    );

                    $canReopen =
                        $labourAttendance->status === 'approved'
                        && $labourAttendance->is_active;
                @endphp

                <div class="space-y-4 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-bold text-gray-900">
                                {{ $labourAttendance->attendance_number }}
                            </div>

                            <div class="mt-1 text-sm text-gray-600">
                                {{ $labourAttendance->project?->project_name ?? '—' }}
                            </div>

                            <div class="mt-1 text-xs text-gray-500">
                                {{ $labourAttendance->project?->project_code ?? 'No project code' }}
                            </div>
                        </div>

                        <span class="inline-flex shrink-0 rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">
                            {{ $labourAttendance->display_status }}
                        </span>
                    </div>

                    <dl class="grid grid-cols-2 gap-4 rounded-lg bg-gray-50 p-4 text-sm">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Date
                            </dt>

                            <dd class="mt-1 font-semibold text-gray-900">
                                {{ $labourAttendance->attendance_date?->format('d M Y') ?? '—' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Shift
                            </dt>

                            <dd class="mt-1 font-semibold text-gray-900">
                                {{ $labourAttendance->shift?->name ?? 'General' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Labour
                            </dt>

                            <dd class="mt-1 font-semibold text-gray-900">
                                {{ number_format($labourAttendance->details_count) }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Revision
                            </dt>

                            <dd class="mt-1 font-semibold text-gray-900">
                                @if ((int) $labourAttendance->revision_number > 0)
                                    Revision {{ $labourAttendance->revision_number }}
                                @else
                                    Original
                                @endif
                            </dd>
                        </div>
                    </dl>

                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('labour-attendances.show', $labourAttendance) }}"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                        >
                            View
                        </a>

                        @if ($canEdit)
                            <a
                                href="{{ route('labour-attendances.edit', $labourAttendance) }}"
                                class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600"
                            >
                                Edit Correction
                            </a>
                        @endif
                    </div>

                    @if ($canReopen)
                        <details class="rounded-lg border border-amber-200 bg-amber-50">
                            <summary class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-amber-800">
                                Reopen Approved Attendance
                            </summary>

                            <form
                                method="POST"
                                action="{{ route('labour-attendances.reopen', $labourAttendance) }}"
                                class="space-y-3 border-t border-amber-200 p-4"
                            >
                                @csrf
                                @method('PATCH')

                                <div>
                                    <label
                                        for="mobile_reopen_reason_{{ $labourAttendance->id }}"
                                        class="mb-1 block text-sm font-semibold text-gray-700"
                                    >
                                        Reason for correction
                                        <span class="text-red-600">*</span>
                                    </label>

                                    <textarea
                                        id="mobile_reopen_reason_{{ $labourAttendance->id }}"
                                        name="reopen_reason"
                                        rows="4"
                                        required
                                        maxlength="2000"
                                        placeholder="Explain why this approved attendance must be corrected."
                                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                    ></textarea>
                                </div>

                                <button
                                    type="submit"
                                    onclick="return confirm('Reopen this approved attendance sheet for correction?');"
                                    class="inline-flex w-full items-center justify-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700"
                                >
                                    Confirm Reopen
                                </button>
                            </form>
                        </details>
                    @endif

                    @if ($labourAttendance->status === 'submitted')
                        <p class="text-sm font-medium text-blue-600">
                            Correction submitted and awaiting approval.
                        </p>
                    @endif
                </div>
            @empty
                <div class="px-5 py-14 text-center">
                    <div class="text-base font-semibold text-gray-900">
                        No attendance corrections found
                    </div>

                    <p class="mt-2 text-sm text-gray-500">
                        Try changing or clearing the selected filters.
                    </p>

                    <a
                        href="{{ route('labour-attendance-corrections.index') }}"
                        class="mt-4 inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                    >
                        Clear Filters
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($labourAttendances->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $labourAttendances->links() }}
            </div>
        @endif
    </div>
</div>
@endsection