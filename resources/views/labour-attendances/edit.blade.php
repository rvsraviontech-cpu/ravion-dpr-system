@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Edit Labour Attendance"
    subtitle="Update project-wise labour attendance details while the sheet is editable."
>
    <x-slot:actions>

        <x-rds.button
            href="{{ route(
                'labour-attendances.show',
                $labourAttendance
            ) }}"
            variant="secondary"
        >
            View Attendance
        </x-rds.button>

        <x-rds.button
            href="{{ route('labour-attendances.index') }}"
            variant="secondary"
        >
            Back to List
        </x-rds.button>

    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

@if($errors->any())
    <div class="mb-6 rounded-xl border border-red-300 bg-red-50 p-4 text-red-800">
        <p class="font-semibold">
            Attendance could not be updated.
        </p>

        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form
    method="POST"
    action="{{ route(
        'labour-attendances.update',
        $labourAttendance
    ) }}"
    id="labour-attendance-update-form"
>
    @csrf
    @method('PUT')

    <div class="space-y-6">

        @include('labour-attendances._form', [
            'labourAttendance' => $labourAttendance,
            'projects' => $projects,
            'shifts' => $shifts,
            'attendanceStatuses' => $attendanceStatuses,
            'workingStatuses' => $workingStatuses,
        ])

        <x-rds.card>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <x-rds.button
                    href="{{ route(
                        'labour-attendances.show',
                        $labourAttendance
                    ) }}"
                    variant="secondary"
                >
                    Cancel
                </x-rds.button>

                <x-rds.button
                    type="submit"
                    variant="primary"
                >
                    Update Attendance
                </x-rds.button>

            </div>
        </x-rds.card>

    </div>
</form>

@endsection
