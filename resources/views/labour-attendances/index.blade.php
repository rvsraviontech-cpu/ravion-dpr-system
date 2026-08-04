@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Labour Attendance"
    subtitle="Manage daily project-wise labour attendance, hours, overtime, and approval workflow."
>
    <x-slot:actions>
        @if(auth()->user()->hasPermission('labour_attendances.create'))
            <x-rds.button
                href="{{ route('labour-attendances.create') }}"
                variant="primary"
            >
                Record Attendance
            </x-rds.button>
        @endif
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<form
    method="GET"
    action="{{ route('labour-attendances.index') }}"
    class="mb-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

        <x-rds.input
            name="search"
            label="Search"
            placeholder="Attendance number or project"
            :value="request('search')"
        />

        <x-rds.select
            name="project_id"
            label="Project"
        >
            <option value="">All Projects</option>

            @foreach($projects as $project)
                <option
                    value="{{ $project->id }}"
                    @selected(
                        (string) request('project_id')
                        === (string) $project->id
                    )
                >
                    {{ $project->project_name }}
                </option>
            @endforeach
        </x-rds.select>

        <x-rds.select
            name="shift_id"
            label="Shift"
        >
            <option value="">All Shifts</option>

            @foreach($shifts as $shift)
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
        </x-rds.select>

        <x-rds.select
            name="status"
            label="Workflow Status"
        >
            <option value="">All Statuses</option>

            <option value="draft" @selected(request('status') === 'draft')>
                Draft
            </option>

            <option value="submitted" @selected(request('status') === 'submitted')>
                Submitted
            </option>

            <option value="approved" @selected(request('status') === 'approved')>
                Approved
            </option>

            <option value="rejected" @selected(request('status') === 'rejected')>
                Rejected
            </option>
        </x-rds.select>

        <x-rds.input
            name="attendance_date"
            label="Exact Date"
            type="date"
            :value="request('attendance_date')"
        />

        <x-rds.input
            name="date_from"
            label="Date From"
            type="date"
            :value="request('date_from')"
        />

        <x-rds.input
            name="date_to"
            label="Date To"
            type="date"
            :value="request('date_to')"
        />

    </div>

    <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-end">

        <x-rds.button
            href="{{ route('labour-attendances.index') }}"
            variant="secondary"
            class="!px-4 !py-2 !text-sm"
        >
            Reset
        </x-rds.button>

        <x-rds.button
            type="submit"
            variant="primary"
            class="!px-4 !py-2 !text-sm"
        >
            Filter
        </x-rds.button>

    </div>
</form>

<x-rds.card :padding="false">

    <div class="w-full overflow-x-auto">

        <table class="w-full min-w-[1450px] table-fixed divide-y divide-gray-200">

            <colgroup>
                <col class="w-[55px]">
                <col class="w-[170px]">
                <col class="w-[240px]">
                <col class="w-[130px]">
                <col class="w-[140px]">
                <col class="w-[90px]">
                <col class="w-[90px]">
                <col class="w-[90px]">
                <col class="w-[90px]">
                <col class="w-[110px]">
                <col class="w-[110px]">
                <col class="w-[120px]">
                <col class="w-[220px]">
            </colgroup>

            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        #
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Attendance No.
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Project
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Date
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Shift
                    </th>

                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Total
                    </th>

                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Present
                    </th>

                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Absent
                    </th>

                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Leave
                    </th>

                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Normal Hrs
                    </th>

                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">
                        OT Hrs
                    </th>

                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Status
                    </th>

                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Actions
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-white">

                @forelse($labourAttendances as $attendance)

                    @php
                        $statusVariant = match ($attendance->status) {
                            'draft' => 'secondary',
                            'submitted' => 'warning',
                            'approved' => 'success',
                            'rejected' => 'danger',
                            default => 'secondary',
                        };
                    @endphp

                    <tr class="align-middle hover:bg-gray-50">

                        <td class="px-3 py-3 text-sm text-gray-500">
                            {{ $labourAttendances->firstItem() + $loop->index }}
                        </td>

                        <td class="px-3 py-3 text-sm font-semibold text-gray-900">
                            {{ $attendance->attendance_number }}
                        </td>

                        <td class="px-3 py-3 text-sm text-gray-700">
                            <div class="font-medium text-gray-900">
                                {{ $attendance->project?->project_name ?? '—' }}
                            </div>

                            @if($attendance->project?->project_code)
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $attendance->project->project_code }}
                                </div>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-sm text-gray-700">
                            {{ $attendance->attendance_date?->format('d M Y') ?? '—' }}
                        </td>

                        <td class="px-3 py-3 text-sm text-gray-700">
                            {{ $attendance->shift?->name ?? 'No Shift' }}
                        </td>

                        <td class="px-3 py-3 text-center text-sm font-semibold text-gray-900">
                            {{ $attendance->total_labours }}
                        </td>

                        <td class="px-3 py-3 text-center text-sm font-semibold text-green-700">
                            {{ $attendance->present_count }}
                        </td>

                        <td class="px-3 py-3 text-center text-sm font-semibold text-red-700">
                            {{ $attendance->absent_count }}
                        </td>

                        <td class="px-3 py-3 text-center text-sm font-semibold text-amber-700">
                            {{ $attendance->leave_count }}
                        </td>

                        <td class="px-3 py-3 text-center text-sm text-gray-700">
                            {{ number_format((float) $attendance->total_normal_hours, 2) }}
                        </td>

                        <td class="px-3 py-3 text-center text-sm text-gray-700">
                            {{ number_format((float) $attendance->total_ot_hours, 2) }}
                        </td>

                        <td class="px-3 py-3 text-center">

                            <div class="flex flex-col items-center gap-1">

                                <x-rds.badge :variant="$statusVariant">
                                    {{ $attendance->display_status }}
                                </x-rds.badge>

                                @if(! $attendance->is_active)
                                    <x-rds.badge variant="danger">
                                        Inactive
                                    </x-rds.badge>
                                @endif

                            </div>

                        </td>

                        <td class="px-3 py-3 text-right">

                            <div class="flex flex-nowrap items-center justify-end gap-1.5">

                                <x-rds.button
                                    href="{{ route('labour-attendances.show', $attendance) }}"
                                    variant="secondary"
                                    size="sm"
                                    class="!px-3 !py-1.5 !text-xs"
                                >
                                    View
                                </x-rds.button>

                                @if(
                                    auth()->user()->hasPermission('labour_attendances.edit')
                                    && $attendance->canBeEdited()
                                )
                                    <x-rds.button
                                        href="{{ route('labour-attendances.edit', $attendance) }}"
                                        variant="secondary"
                                        size="sm"
                                        class="!px-3 !py-1.5 !text-xs"
                                    >
                                        Edit
                                    </x-rds.button>
                                @endif

                                @if(
                                    auth()->user()->hasPermission('labour_attendances.submit')
                                    && $attendance->canBeSubmitted()
                                )
                                    <form
                                        method="POST"
                                        action="{{ route('labour-attendances.submit', $attendance) }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <x-rds.button
                                            type="submit"
                                            variant="primary"
                                            size="sm"
                                            class="!px-3 !py-1.5 !text-xs"
                                        >
                                            Submit
                                        </x-rds.button>
                                    </form>
                                @endif

                                @if(
                                    auth()->user()->hasPermission('labour_attendances.approve')
                                    && $attendance->canBeApproved()
                                )
                                    <form
                                        method="POST"
                                        action="{{ route('labour-attendances.approve', $attendance) }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <x-rds.button
                                            type="submit"
                                            variant="success"
                                            size="sm"
                                            class="!px-3 !py-1.5 !text-xs"
                                        >
                                            Approve
                                        </x-rds.button>
                                    </form>
                                @endif

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td
                            colspan="13"
                            class="px-4 py-12 text-center"
                        >
                            <div class="text-sm font-medium text-gray-700">
                                No Labour Attendance sheets found.
                            </div>

                            <div class="mt-1 text-xs text-gray-500">
                                Adjust the filters or create the first attendance sheet.
                            </div>
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($labourAttendances->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">
            {{ $labourAttendances->links() }}
        </div>
    @endif

</x-rds.card>

@endsection