@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Gender Details"
    subtitle="View gender classification details and record status."
>
    <x-slot:actions>

        @if(auth()->user()->hasPermission('labour_master_data.manage'))
            <x-rds.button
                href="{{ route('genders.edit', $gender) }}"
                variant="primary"
            >
                Edit Gender
            </x-rds.button>
        @endif

        <x-rds.button
            href="{{ route('genders.index') }}"
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
                    Gender Code
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $gender->code }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Gender Name
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $gender->name }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Sort Order
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $gender->sort_order }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Record Type
                </p>

                <div class="mt-2">
                    @if($gender->is_system)
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
                    @if($gender->is_active)
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
                    {{ $gender->remarks ?: 'No remarks available.' }}
                </p>
            </div>

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
                        Activate or deactivate custom gender records.
                    </p>
                </div>

                @if($gender->canBeDeactivated() || ! $gender->is_active)

                    <form
                        method="POST"
                        action="{{ route('genders.toggle-status', $gender) }}"
                    >
                        @csrf
                        @method('PATCH')

                        <x-rds.button
                            type="submit"
                            variant="{{ $gender->is_active ? 'danger' : 'success' }}"
                        >
                            {{ $gender->is_active ? 'Deactivate Gender' : 'Activate Gender' }}
                        </x-rds.button>
                    </form>

                @else

                    <x-rds.badge variant="warning">
                        Protected System Record
                    </x-rds.badge>

                @endif

            </div>

        </x-rds.card>

    @endif

</div>

@endsection