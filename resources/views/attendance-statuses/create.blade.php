@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Create Attendance Status"
    subtitle="Add a new attendance status for the labour attendance module."
>
    <x-slot:actions>
        <x-rds.button
            href="{{ route('attendance-statuses.index') }}"
            variant="secondary"
        >
            Back to List
        </x-rds.button>
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<form
    method="POST"
    action="{{ route('attendance-statuses.store') }}"
>
    @csrf

    <div class="space-y-6">

        @include('attendance-statuses._form')

        <x-rds.card>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <x-rds.button
                    href="{{ route('attendance-statuses.index') }}"
                    variant="secondary"
                >
                    Cancel
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Save Attendance Status
                </x-rds.button>

            </div>
        </x-rds.card>

    </div>
</form>

@endsection