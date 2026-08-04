@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Attendance Status Details"
    subtitle="View attendance behaviour, payable settings, and system status."
>
    <x-slot:actions>

        @if(auth()->user()->hasPermission('labour_master_data.manage'))
            <x-rds.button
                href="{{ route('attendance-statuses.edit', $attendanceStatus) }}"
                variant="primary"
            >
                Edit Attendance Status
            </x-rds.button>
        @endif

        <x-rds.button
            href="{{ route('attendance-statuses.index') }}"
            variant="secondary"
        >
            Back to List
        </x-rds.button>

    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<div class="space-y-6">

    <x-rds.card>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Status Code
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $attendanceStatus->code }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Status Name
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $attendanceStatus->name }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Short Name
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $attendanceStatus->short_name ?: '—' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Payable Factor
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ number_format((float) $attendanceStatus->payable_factor, 2) }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Sort Order
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $attendanceStatus->sort_order }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Record Type
                </p>

                <div class="mt-2">
                    @if($attendanceStatus->is_system)
                        <x-rds.badge variant="warning">
                            System Status
                        </x-rds.badge>
                    @else
                        <x-rds.badge variant="secondary">
                            Custom Status
                        </x-rds.badge>
                    @endif
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Active Status
                </p>

                <div class="mt-2">
                    @if($attendanceStatus->is_active)
                        <x-rds.badge variant="success">
                            Active
                        </x-rds.badge>
                    @else
                        <x-rds.badge variant="danger">
                            Inactive
                        </x-rds.badge>
                    @endif
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Counts as Present
                </p>

                <div class="mt-2">
                    @if($attendanceStatus->counts_as_present)
                        <x-rds.badge variant="success">
                            Yes
                        </x-rds.badge>
                    @else
                        <x-rds.badge variant="secondary">
                            No
                        </x-rds.badge>
                    @endif
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Counts as Absent
                </p>

                <div class="mt-2">
                    @if($attendanceStatus->counts_as_absent)
                        <x-rds.badge variant="danger">
                            Yes
                        </x-rds.badge>
                    @else
                        <x-rds.badge variant="secondary">
                            No
                        </x-rds.badge>
                    @endif
                </div>
            </div>

        </div>

    </x-rds.card>

    <x-rds.card>

        <div class="mb-5">
            <h2 class="text-base font-semibold text-gray-900">
                Attendance Behaviour
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                These settings control validation and attendance summary behaviour.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-sm font-semibold text-gray-800">
                    Normal Hours
                </p>

                <div class="mt-2">
                    @if($attendanceStatus->allows_normal_hours)
                        <x-rds.badge variant="success">
                            Allowed
                        </x-rds.badge>
                    @else
                        <x-rds.badge variant="secondary">
                            Not Allowed
                        </x-rds.badge>
                    @endif
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-sm font-semibold text-gray-800">
                    OT Hours
                </p>

                <div class="mt-2">
                    @if($attendanceStatus->allows_ot_hours)
                        <x-rds.badge variant="success">
                            Allowed
                        </x-rds.badge>
                    @else
                        <x-rds.badge variant="secondary">
                            Not Allowed
                        </x-rds.badge>
                    @endif
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-sm font-semibold text-gray-800">
                    Working Status
                </p>

                <div class="mt-2">
                    @if($attendanceStatus->requires_working_status)
                        <x-rds.badge variant="warning">
                            Required
                        </x-rds.badge>
                    @else
                        <x-rds.badge variant="secondary">
                            Not Required
                        </x-rds.badge>
                    @endif
                </div>
            </div>

        </div>

    </x-rds.card>

    <x-rds.card>

        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                Remarks
            </p>

            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-700">
                {{ $attendanceStatus->remarks ?: 'No remarks available.' }}
            </p>
        </div>

    </x-rds.card>

    @if(auth()->user()->hasPermission('labour_master_data.manage'))
        <x-rds.card>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="text-base font-semibold text-gray-900">
                        Status Control
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Activate or deactivate custom attendance statuses.
                    </p>
                </div>

                @if($attendanceStatus->canBeDeactivated() || ! $attendanceStatus->is_active)

                    <form
                        method="POST"
                        action="{{ route('attendance-statuses.toggle-status', $attendanceStatus) }}"
                    >
                        @csrf
                        @method('PATCH')

                        <x-rds.button
                            type="submit"
                            variant="{{ $attendanceStatus->is_active ? 'danger' : 'success' }}"
                        >
                            {{ $attendanceStatus->is_active ? 'Deactivate Status' : 'Activate Status' }}
                        </x-rds.button>
                    </form>

                @else

                    <x-rds.badge variant="warning">
                        Protected System Status
                    </x-rds.badge>

                @endif

            </div>

        </x-rds.card>
    @endif

</div>

@endsection