@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Verify Material Received</h1>
        <p class="text-gray-500 mt-1">
            Review received material quantity, condition and verification status.
        </p>
    </div>

    <a href="{{ route('material-verifications.index') }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow">
        Back
    </a>
</div>

<div class="bg-white rounded shadow p-6 mb-6">

    <h2 class="text-xl font-bold text-gray-700 mb-4">Material Received Details</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div><strong>Project:</strong><br>{{ $materialReceived->project?->project_name ?? '-' }}</div>
        <div><strong>Block:</strong><br>{{ $materialReceived->block?->name ?? '-' }}</div>
        <div><strong>Floor:</strong><br>{{ $materialReceived->floor?->name ?? '-' }}</div>

        <div><strong>Unit:</strong><br>{{ $materialReceived->unit?->name ?? '-' }}</div>
        <div><strong>Material:</strong><br>{{ $materialReceived->material?->material_name ?? $materialReceived->material_name ?? '-' }}</div>
        <div><strong>Category:</strong><br>{{ $materialReceived->materialCategory?->category_name ?? '-' }}</div>

        <div><strong>Received Date:</strong><br>{{ $materialReceived->received_date ?? '-' }}</div>
        <div><strong>Received Qty:</strong><br>{{ number_format($materialReceived->quantity_received, 2) }} {{ $materialReceived->unit }}</div>
        <div><strong>Condition:</strong><br>{{ $materialReceived->material_condition ?? '-' }}</div>

        <div><strong>Vendor:</strong><br>{{ $materialReceived->vendor_name ?? '-' }}</div>
        <div><strong>Challan No:</strong><br>{{ $materialReceived->challan_number ?? '-' }}</div>
        <div><strong>Vehicle No:</strong><br>{{ $materialReceived->vehicle_number ?? '-' }}</div>
    </div>

</div>

<div class="bg-white rounded shadow p-6">

    <h2 class="text-xl font-bold text-gray-700 mb-4">PMO Verification</h2>

    <form method="POST"
          action="{{ route('material-verifications.verify', $materialReceived) }}">

        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

            <div>
                <label class="block text-sm font-semibold mb-1">Verification Status</label>
                <select name="verification_status"
                        class="border p-2 rounded w-full"
                        required>
                    <option value="Verified">Verified</option>
                    <option value="Rejected">Rejected</option>
                    <option value="Hold">Hold</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Accepted Quantity</label>
                <input type="number"
                       step="0.01"
                       name="accepted_quantity"
                       value="{{ old('accepted_quantity', $materialReceived->accepted_quantity ?? $materialReceived->quantity_received) }}"
                       class="border p-2 rounded w-full"
                       required>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Short Quantity</label>
                <input type="number"
                       step="0.01"
                       name="short_quantity"
                       value="{{ old('short_quantity', $materialReceived->short_quantity ?? 0) }}"
                       class="border p-2 rounded w-full">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Damaged Quantity</label>
                <input type="number"
                       step="0.01"
                       name="damaged_quantity"
                       value="{{ old('damaged_quantity', $materialReceived->damaged_quantity ?? 0) }}"
                       class="border p-2 rounded w-full">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Rejected Quantity</label>
                <input type="number"
                       step="0.01"
                       name="rejected_quantity"
                       value="{{ old('rejected_quantity', $materialReceived->rejected_quantity ?? 0) }}"
                       class="border p-2 rounded w-full">
            </div>

        </div>

        <div class="mb-6">
            <label class="block text-sm font-semibold mb-1">Verification Remarks</label>
            <textarea name="verification_remarks"
                      rows="4"
                      class="border p-2 rounded w-full">{{ old('verification_remarks') }}</textarea>
        </div>

        <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">
            Save Verification
        </button>

    </form>

</div>

@endsection