@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Working Status Details"
    subtitle="View working condition behaviour, idle classification, and record status."
>
    <x-slot:actions>

        @if(
            auth()->user()->hasPermission('labour_master_data.manage')
            && ! $workingStatus->is_system
        )
            <x-rds.button
                href="{{ route('working-statuses.edit', $workingStatus) }}"
                variant="primary"
            >
                Edit Working Status
            </x-rds.button>
        @endif

        <x-rds.button
            href="{{ route('working-statuses.index') }}"
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
                    Working Status Code
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $workingStatus->code }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Working Status Name
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $workingStatus->name }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Sort Order
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $workingStatus->sort_order }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Counts as Idle
                </p>

                <div class="mt-2">
                    @if($workingStatus->counts_as_idle)
                        <x-rds.badge variant="warning">
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
                    Reason Required
                </p>

                <div class="mt-2">
                    @if($workingStatus->requires_reason)
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

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Record Type
                </p>

                <div class="mt-2">
                    @if($workingStatus->is_system)
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
                    @if($workingStatus->is_active)
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
                    {{ $workingStatus->remarks ?: 'No remarks available.' }}
                </p>
            </div>

        </div>

    </x-rds.card>

    @if(
        auth()->user()->hasPermission('labour_master_data.manage')
        && ! $workingStatus->is_system
    )

        <x-rds.card>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="text-base font-semibold text-gray-900">
                        Status Control
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Activate or deactivate this custom working status.
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route('working-statuses.toggle-status', $workingStatus) }}"
                >
                    @csrf
                    @method('PATCH')

                    <x-rds.button
                        type="submit"
                        variant="{{ $workingStatus->is_active ? 'danger' : 'success' }}"
                    >
                        {{ $workingStatus->is_active
                            ? 'Deactivate Working Status'
                            : 'Activate Working Status' }}
                    </x-rds.button>
                </form>

            </div>

        </x-rds.card>

    @endif

</div>

@endsection