@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Edit Working Status"
    subtitle="Update the working condition, idle classification, reason requirement, and availability."
>
    <x-slot:actions>
        <x-rds.button
            href="{{ route('working-statuses.show', $workingStatus) }}"
            variant="secondary"
        >
            View Working Status
        </x-rds.button>

        <x-rds.button
            href="{{ route('working-statuses.index') }}"
            variant="secondary"
        >
            Back to List
        </x-rds.button>
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<form
    method="POST"
    action="{{ route('working-statuses.update', $workingStatus) }}"
>
    @csrf
    @method('PUT')

    <div class="space-y-6">

        @include('working-statuses._form', [
            'workingStatus' => $workingStatus,
        ])

        <x-rds.card>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <x-rds.button
                    href="{{ route('working-statuses.show', $workingStatus) }}"
                    variant="secondary"
                >
                    Cancel
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Update Working Status
                </x-rds.button>

            </div>
        </x-rds.card>

    </div>
</form>

@endsection