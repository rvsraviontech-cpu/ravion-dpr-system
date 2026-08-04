@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Attendance Corrections"
    subtitle="Manage Attendance Change Requests, approvals, and attendance adjustments."
>
    <x-slot:actions>

        @if(auth()->user()->hasPermission('attendance_corrections.create'))

            <x-rds.button
                href="{{ route('attendance-corrections.create') }}"
                variant="primary"
            >
                New Attendance Correction
            </x-rds.button>

        @endif

    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

@include('attendance-corrections.partials.filters')

@include('attendance-corrections.partials.statistics')

@include('attendance-corrections.partials.table')

@endsection