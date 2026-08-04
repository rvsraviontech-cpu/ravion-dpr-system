@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Edit Attendance Status"
    subtitle="Update attendance status behaviour and display settings."
>
    <x-slot:actions>
        <x-rds.button
            href="{{ route('attendance-statuses.show', $attendanceStatus) }}"
            variant="secondary"
        >
            View Status
        </x-rds.button>

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
    action="{{ route('attendance-statuses.update', $attendanceStatus) }}"
>
    @csrf
    @method('PUT')

    <div class="space-y-6">

        @include('attendance-statuses._form', [
            'attendanceStatus' => $attendanceStatus,
        ])

        <x-rds.card>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <x-rds.button
                    href="{{ route('attendance-statuses.show', $attendanceStatus) }}"
                    variant="secondary"
                >
                    Cancel
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Update Attendance Status
                </x-rds.button>

            </div>
        </x-rds.card>

    </div>
</form>

@endsection