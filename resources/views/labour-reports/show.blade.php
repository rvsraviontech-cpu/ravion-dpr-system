@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">

    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            Labour Report Details
        </h1>

        <p class="text-gray-500 mt-1">
            Complete labour execution details.
        </p>
    </div>

    <a href="{{ route('labour-reports.index') }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
        Back
    </a>

</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Status</p>

        <p class="mt-2">
            <span class="px-3 py-1 rounded text-sm
                {{ $labourReport->status === 'Draft' ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $labourReport->status === 'Submitted' ? 'bg-orange-100 text-orange-800' : '' }}
                {{ $labourReport->status === 'Approved' ? 'bg-green-100 text-green-800' : '' }}">
                {{ $labourReport->status }}
            </span>
        </p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Date</p>

        <p class="text-xl font-bold mt-2">
            {{ $labourReport->entry_date }}
        </p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Shift</p>

        <p class="text-xl font-bold mt-2">
            {{ $labourReport->shift ?? '-' }}
        </p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Total Labour</p>

        <p class="text-2xl font-bold text-blue-700 mt-2">
            {{ $labourReport->total_labour }}
        </p>
    </div>

</div>

<div class="bg-white rounded shadow p-6 mb-6">

    <h2 class="text-xl font-bold mb-4">
        Project & Location Details
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div>
            <p class="text-sm text-gray-500">Project</p>
            <p class="font-semibold">
                {{ $labourReport->project?->project_name ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Block</p>
            <p class="font-semibold">
                {{ $labourReport->block?->name ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Floor</p>
            <p class="font-semibold">
                {{ $labourReport->floor?->name ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Unit</p>
            <p class="font-semibold">
                {{ $labourReport->unit?->name ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Room</p>
            <p class="font-semibold">
                {{ $labourReport->room?->name ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Sub-space</p>
            <p class="font-semibold">
                {{ $labourReport->subspace?->name ?? '-' }}
            </p>
        </div>

    </div>

</div>

<div class="bg-white rounded shadow p-6 mb-6">

    <h2 class="text-xl font-bold mb-4">
        Activity Details
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div>
            <p class="text-sm text-gray-500">Activity</p>

            <p class="font-semibold">
                {{ $labourReport->activity?->activity_name ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Contractor</p>

            <p class="font-semibold">
                {{ $labourReport->contractor?->contractor_name ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Engineer</p>

            <p class="font-semibold">
                {{ $labourReport->engineer?->name ?? '-' }}
            </p>
        </div>

    </div>

</div>

<div class="bg-white rounded shadow p-6 mb-6">

    <h2 class="text-xl font-bold mb-4">
        Labour Classification
    </h2>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

        <div>
            <p class="text-sm text-gray-500">Skilled</p>
            <p class="text-xl font-bold">
                {{ $labourReport->skilled_count }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Semi Skilled</p>
            <p class="text-xl font-bold">
                {{ $labourReport->semi_skilled_count }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Helpers</p>
            <p class="text-xl font-bold">
                {{ $labourReport->helper_count }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Semi Helpers</p>
            <p class="text-xl font-bold">
                {{ $labourReport->semi_helper_count }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Supervisors</p>
            <p class="text-xl font-bold">
                {{ $labourReport->supervisor_count }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Technicians</p>
            <p class="text-xl font-bold">
                {{ $labourReport->technician_count }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Machine Operators</p>
            <p class="text-xl font-bold">
                {{ $labourReport->machine_operator_count }}
            </p>
        </div>

    </div>

</div>

<div class="bg-white rounded shadow p-6 mb-6">

    <h2 class="text-xl font-bold mb-4">
        Workforce Demographics
    </h2>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

        <div>
            <p class="text-sm text-gray-500">Male</p>
            <p class="text-xl font-bold">
                {{ $labourReport->male_count }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Female</p>
            <p class="text-xl font-bold">
                {{ $labourReport->female_count }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Local</p>
            <p class="text-xl font-bold">
                {{ $labourReport->local_count }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Non Local</p>
            <p class="text-xl font-bold">
                {{ $labourReport->non_local_count }}
            </p>
        </div>

    </div>

</div>

@if($labourReport->remarks)

<div class="bg-white rounded shadow p-6">

    <h2 class="text-xl font-bold mb-4">
        Remarks
    </h2>

    <p class="text-gray-700 whitespace-pre-line">
        {{ $labourReport->remarks }}
    </p>

</div>

@endif

@endsection