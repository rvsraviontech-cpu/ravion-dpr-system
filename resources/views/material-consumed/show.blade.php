@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            Material Consumption Details
        </h1>
        <p class="text-gray-500 mt-1">
            Complete material consumption entry details.
        </p>
    </div>

    <a href="{{ route('material-consumed.index') }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
        Back
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Status</p>
        <p class="mt-2">
            <span class="px-3 py-1 rounded text-sm
                {{ $materialConsumed->status === 'Draft' ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $materialConsumed->status === 'Submitted' ? 'bg-orange-100 text-orange-800' : '' }}
                {{ $materialConsumed->status === 'Approved' ? 'bg-green-100 text-green-800' : '' }}">
                {{ $materialConsumed->status }}
            </span>
        </p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Consumed Date</p>
        <p class="text-xl font-bold mt-2">
            {{ $materialConsumed->consumed_date }}
        </p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Consumed Quantity</p>
        <p class="text-2xl font-bold text-blue-700 mt-2">
            {{ $materialConsumed->quantity_consumed }}
        </p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Wastage</p>
        <p class="text-2xl font-bold text-red-700 mt-2">
            {{ $materialConsumed->wastage_quantity }}
        </p>
    </div>

</div>

<div class="bg-white rounded shadow p-6 mb-6">

    <h2 class="text-xl font-bold mb-4">
        Project & Location
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div>
            <p class="text-sm text-gray-500">Project</p>
            <p class="font-semibold">
                {{ $materialConsumed->project?->project_name ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Block</p>
            <p class="font-semibold">
                {{ $materialConsumed->block?->name ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Floor</p>
            <p class="font-semibold">
                {{ $materialConsumed->floor?->name ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Unit</p>
            <p class="font-semibold">
                {{ $materialConsumed->unit?->name ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Room</p>
            <p class="font-semibold">
                {{ $materialConsumed->room?->name ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Sub-space</p>
            <p class="font-semibold">
                {{ $materialConsumed->subspace?->name ?? '-' }}
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
            <p class="text-sm text-gray-500">Activity Division</p>
            <p class="font-semibold">
                {{ $materialConsumed->activityDivision?->name ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Activity</p>
            <p class="font-semibold">
                {{ $materialConsumed->activity?->activity_name ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Contractor</p>
            <p class="font-semibold">
                {{ $materialConsumed->contractor?->contractor_name ?? '-' }}
            </p>
        </div>

    </div>

</div>

<div class="bg-white rounded shadow p-6 mb-6">

    <h2 class="text-xl font-bold mb-4">
        Material Details
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div>
            <p class="text-sm text-gray-500">Category</p>
            <p class="font-semibold">
                {{ $materialConsumed->materialCategory?->category_name ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Material</p>
            <p class="font-semibold">
                {{ $materialConsumed->material?->material_name ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Quantity Consumed</p>
            <p class="font-semibold">
                {{ $materialConsumed->quantity_consumed }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Unit</p>
            <p class="font-semibold">
                {{ $materialConsumed->unit ?? '-' }}
            </p>
        </div>

    </div>

</div>

<div class="bg-white rounded shadow p-6 mb-6">

    <h2 class="text-xl font-bold mb-4">
        Productivity & Wastage
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div>
            <p class="text-sm text-gray-500">
                Work Output Quantity
            </p>

            <p class="font-semibold">
                {{ $materialConsumed->related_work_output_quantity }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">
                Wastage Quantity
            </p>

            <p class="font-semibold">
                {{ $materialConsumed->wastage_quantity }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">
                Wastage Reason
            </p>

            <p class="font-semibold">
                {{ $materialConsumed->wastage_reason ?? '-' }}
            </p>
        </div>

    </div>

</div>

@if($materialConsumed->remarks)

<div class="bg-white rounded shadow p-6">

    <h2 class="text-xl font-bold mb-4">
        Remarks
    </h2>

    <p class="whitespace-pre-line">
        {{ $materialConsumed->remarks }}
    </p>

</div>

@endif

@endsection