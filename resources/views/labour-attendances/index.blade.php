@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Labour Attendance"
    subtitle="Manage daily project-wise labour attendance and approval workflow."
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

        <x-rds.select name="project_id" label="Project">
            <option value="">All Projects</option>
            @foreach($projects as $project)
                <option
                    value="{{ $project->id }}"
                    @selected((string) request('project_id') === (string) $project->id)
                >
                    {{ $project->project_name }}
                </option>
            @endforeach
        </x-rds.select>

        <x-rds.select name="status" label="Workflow Status">
            <option value="">All Statuses</option>
            <option value="draft" @selected(request('status') === 'draft')>Draft</option>
            <option value="submitted" @selected(request('status') === 'submitted')>Submitted</option>
            <option value="approved" @selected(request('status') === 'approved')>Approved</option>
            <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
            <option value="reopened" @selected(request('status') === 'reopened')>Reopened</option>
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

    <div class="w-full overflow-hidden">
        <div class="max-h-[326px] overflow-y-auto overflow-x-hidden">

            <table class="w-full table-fixed border-collapse">

                <colgroup>
                    <col style="width: 6%;">
                    <col style="width: 28%;">
                    <col style="width: 14%;">
                    <col style="width: 9%;">
                    <col style="width: 9%;">
                    <col style="width: 9%;">
                    <col style="width: 11%;">
                    <col style="width: 14%;">
                </colgroup>

                <thead class="sticky top-0 z-10 bg-gray-50">
                    <tr>
                        <th class="border-b border-gray-200 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">#</th>
                        <th class="border-b border-gray-200 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Project</th>
                        <th class="border-b border-gray-200 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Date</th>
                        <th class="border-b border-gray-200 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">Total</th>
                        <th class="border-b border-gray-200 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">Present</th>
                        <th class="border-b border-gray-200 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">Absent</th>
                        <th class="border-b border-gray-200 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">Status</th>
                        <th class="border-b border-gray-200 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Actions</th>
                    </tr>
                </thead>

                <tbody class="bg-white">

                    @forelse($labourAttendances as $attendance)

                        @php
                            $statusVariant = match ($attendance->status) {
                                'draft' => 'secondary',
                                'submitted' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'reopened' => 'warning',
                                default => 'secondary',
                            };
                        @endphp

                        <tr class="h-[56px] align-middle transition hover:bg-gray-50">

                            <td class="border-b border-gray-100 px-4 py-3 text-sm text-gray-500">
                                {{ $labourAttendances->firstItem() + $loop->index }}
                            </td>

                            <td class="border-b border-gray-100 px-4 py-3">
                                <div class="truncate text-sm font-semibold text-gray-900">
                                    {{ $attendance->project?->project_name ?? '—' }}
                                </div>

                                @if($attendance->project?->project_code)
                                    <div class="mt-0.5 truncate text-xs text-gray-500">
                                        {{ $attendance->project->project_code }}
                                    </div>
                                @endif
                            </td>

                            <td class="border-b border-gray-100 px-4 py-3 text-sm font-medium text-gray-700">
                                {{ $attendance->attendance_date?->format('d M Y') ?? '—' }}
                            </td>

                            <td class="border-b border-gray-100 px-4 py-3 text-center text-sm font-semibold text-gray-900">
                                {{ $attendance->total_labours }}
                            </td>

                            <td class="border-b border-gray-100 px-4 py-3 text-center text-sm font-semibold text-green-700">
                                {{ $attendance->present_count }}
                            </td>

                            <td class="border-b border-gray-100 px-4 py-3 text-center text-sm font-semibold text-red-700">
                                {{ $attendance->absent_count }}
                            </td>

                            <td class="border-b border-gray-100 px-4 py-3 text-center">
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

                            <td class="border-b border-gray-100 px-4 py-3">
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
                                            onsubmit="return confirm('Submit this attendance sheet for approval?');"
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
                                            onsubmit="return confirm('Approve this attendance sheet?');"
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
                            <td colspan="8" class="px-4 py-12 text-center">
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
    </div>

    @if($labourAttendances->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">
            {{ $labourAttendances->links() }}
        </div>
    @endif

</x-rds.card>

@endsection
