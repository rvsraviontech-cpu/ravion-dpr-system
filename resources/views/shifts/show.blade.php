@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Shift Details"
    subtitle="View shift timings, normal working hours, overnight behaviour, and record status."
>
    <x-slot:actions>

        @if(auth()->user()->hasPermission('labour_master_data.manage'))

            <x-rds.button
                href="{{ route('shifts.edit', $shift) }}"
                variant="primary"
            >
                Edit Shift
            </x-rds.button>
        @endif

        <x-rds.button
            href="{{ route('shifts.index') }}"
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
                    Shift Code
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $shift->code }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Shift Name
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $shift->name }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Start Time
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $shift->start_time
                        ? \Carbon\Carbon::createFromFormat('H:i:s', $shift->start_time)->format('h:i A')
                        : 'Not Defined' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    End Time
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $shift->end_time
                        ? \Carbon\Carbon::createFromFormat('H:i:s', $shift->end_time)->format('h:i A')
                        : 'Not Defined' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Normal Working Hours
                </p>

                <p class="mt-1 text-sm font-medium text-gray-800">
                    {{ number_format((float) $shift->normal_hours, 2) }} Hours
                </p>
            </div>

            <div>
    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
        OT Start Time
    </p>

    <p class="mt-1 text-sm text-gray-700">
        {{ $shift->formatted_ot_start_time ?? 'Uses Shift End Time' }}
    </p>
</div>

<div>
    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
        Check-In Grace
    </p>

    <p class="mt-1 text-sm text-gray-700">
        {{ $shift->grace_in_minutes }} Minutes
    </p>
</div>

<div>
    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
        Check-Out Grace
    </p>

    <p class="mt-1 text-sm text-gray-700">
        {{ $shift->grace_out_minutes }} Minutes
    </p>
</div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Crosses Midnight
                </p>

                <div class="mt-2">
                    @if($shift->crosses_midnight)
                        <x-rds.badge variant="warning">
                            Yes — Ends Next Day
                        </x-rds.badge>
                    @else
                        <x-rds.badge variant="secondary">
                            No — Same Day Shift
                        </x-rds.badge>
                    @endif
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Sort Order
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $shift->sort_order }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Record Type
                </p>

                <div class="mt-2">
                    @if($shift->is_system)
                        <x-rds.badge variant="warning">
                            System Record
                        </x-rds.badge>
                    @else
                        <x-rds.badge variant="secondary">
                            Custom Record
                        </x-rds.badge>
                    @endif
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Status
                </p>

                <div class="mt-2">
                    @if($shift->is_active)
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

            <div class="md:col-span-2 xl:col-span-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Remarks
                </p>

                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-700">
                    {{ $shift->remarks ?: 'No remarks available.' }}
                </p>
            </div>

        </div>

    </x-rds.card>

    @if(
        auth()->user()->hasPermission('labour_master_data.manage')
        && ! $shift->is_system
    )

        <x-rds.card>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="text-base font-semibold text-gray-900">
                        Status Control
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Activate or deactivate this custom shift.
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route('shifts.toggle-status', $shift) }}"
                >
                    @csrf
                    @method('PATCH')

                    <x-rds.button
                        type="submit"
                        variant="{{ $shift->is_active ? 'danger' : 'success' }}"
                    >
                        {{ $shift->is_active
                            ? 'Deactivate Shift'
                            : 'Activate Shift' }}
                    </x-rds.button>
                </form>

            </div>

        </x-rds.card>

    @endif

</div>

@endsection