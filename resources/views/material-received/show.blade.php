@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            Material Received Details
        </h1>
        <p class="text-gray-500 mt-1">
            Complete inward material entry details.
        </p>
    </div>

    <a href="{{ route('material-received.index') }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
        Back
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Status</p>
        <p class="mt-2">
            <span class="px-3 py-1 rounded text-sm
                {{ $materialReceived->status === 'Draft' ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $materialReceived->status === 'Submitted' ? 'bg-orange-100 text-orange-800' : '' }}
                {{ $materialReceived->status === 'Approved' ? 'bg-green-100 text-green-800' : '' }}">
                {{ $materialReceived->status }}
            </span>
        </p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Received Date</p>
        <p class="text-xl font-bold mt-2">
            {{ $materialReceived->received_date }}
        </p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Quantity Received</p>
        <p class="text-2xl font-bold text-blue-700 mt-2">
            {{ $materialReceived->quantity_received }} {{ $materialReceived->unit }}
        </p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Condition</p>
        <p class="text-xl font-bold mt-2">
            {{ $materialReceived->material_condition }}
        </p>
    </div>

</div>

<div class="bg-white rounded shadow p-6 mb-6">
    <h2 class="text-xl font-bold mb-4">Project & Location</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <p class="text-sm text-gray-500">Project</p>
            <p class="font-semibold">{{ $materialReceived->project?->project_name ?? '-' }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Block</p>
            <p class="font-semibold">{{ $materialReceived->block?->name ?? '-' }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Floor</p>
            <p class="font-semibold">{{ $materialReceived->floor?->name ?? '-' }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Unit</p>
            <p class="font-semibold">{{ $materialReceived->unit?->name ?? '-' }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Storage Location</p>
            <p class="font-semibold">{{ $materialReceived->storage_location ?? '-' }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Engineer</p>
            <p class="font-semibold">{{ $materialReceived->engineer?->name ?? '-' }}</p>
        </div>
    </div>
</div>

<div class="bg-white rounded shadow p-6 mb-6">
    <h2 class="text-xl font-bold mb-4">Material Details</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <p class="text-sm text-gray-500">Category</p>
            <p class="font-semibold">{{ $materialReceived->materialCategory?->category_name ?? '-' }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Material</p>
            <p class="font-semibold">
                {{ $materialReceived->material?->material_name ?? $materialReceived->material_name }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Specification</p>
            <p class="font-semibold">
                {{ $materialReceived->material?->specification ?? $materialReceived->specification ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Brand</p>
            <p class="font-semibold">
                {{ $materialReceived->material?->brand ?? $materialReceived->brand ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Unit</p>
            <p class="font-semibold">{{ $materialReceived->unit ?? '-' }}</p>
        </div>
    </div>
</div>

<div class="bg-white rounded shadow p-6 mb-6">
    <h2 class="text-xl font-bold mb-4">Vendor & Transport</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <p class="text-sm text-gray-500">Vendor</p>
            <p class="font-semibold">{{ $materialReceived->vendor_name ?? '-' }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Supplied By Contractor</p>
            <p class="font-semibold">{{ $materialReceived->supplied_by_contractor ? 'Yes' : 'No' }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Contractor</p>
            <p class="font-semibold">{{ $materialReceived->contractor?->contractor_name ?? '-' }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Vehicle Number</p>
            <p class="font-semibold">{{ $materialReceived->vehicle_number ?? '-' }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Driver Name</p>
            <p class="font-semibold">{{ $materialReceived->driver_name ?? '-' }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Received Time</p>
            <p class="font-semibold">{{ $materialReceived->received_time ?? '-' }}</p>
        </div>
    </div>
</div>

<div class="bg-white rounded shadow p-6 mb-6">
    <h2 class="text-xl font-bold mb-4">Documents</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p class="text-sm text-gray-500">Challan Number</p>
            <p class="font-semibold">{{ $materialReceived->challan_number ?? '-' }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Bill Number</p>
            <p class="font-semibold">{{ $materialReceived->bill_number ?? '-' }}</p>
        </div>
    </div>
</div>

<div class="bg-white rounded shadow p-6 mb-6">
    <h2 class="text-xl font-bold mb-4">Verification Quantities</h2>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div>
            <p class="text-sm text-gray-500">Accepted Qty</p>
            <p class="text-xl font-bold">{{ $materialReceived->accepted_quantity }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Short Qty</p>
            <p class="text-xl font-bold">{{ $materialReceived->short_quantity }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Damaged Qty</p>
            <p class="text-xl font-bold">{{ $materialReceived->damaged_quantity }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Rejected Qty</p>
            <p class="text-xl font-bold">{{ $materialReceived->rejected_quantity }}</p>
        </div>
    </div>
</div>

<div class="bg-white rounded shadow p-6 mb-6">
    <h2 class="text-xl font-bold mb-4">Verification Status</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <p class="text-sm text-gray-500">Site Engineer</p>
            <p class="font-semibold">{{ $materialReceived->site_engineer_verification_status }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">PMO</p>
            <p class="font-semibold">{{ $materialReceived->pmo_verification_status }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Accountant</p>
            <p class="font-semibold">{{ $materialReceived->accountant_verification_status }}</p>
        </div>
    </div>
</div>

@if($materialReceived->remarks)
    <div class="bg-white rounded shadow p-6">
        <h2 class="text-xl font-bold mb-4">Remarks</h2>
        <p class="text-gray-700 whitespace-pre-line">{{ $materialReceived->remarks }}</p>
    </div>
@endif

@endsection