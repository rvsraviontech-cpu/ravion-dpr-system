@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Material Verification</h1>
        <p class="text-gray-500 mt-1">
            PMO verification queue for received materials.
        </p>
    </div>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">

        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="p-3 text-left">#</th>
                    <th class="p-3 text-left">Date</th>
                    <th class="p-3 text-left">Project</th>
                    <th class="p-3 text-left">Block</th>
                    <th class="p-3 text-left">Material</th>
                    <th class="p-3 text-center">Received Qty</th>
                    <th class="p-3 text-left">Unit</th>
                    <th class="p-3 text-left">PMO Status</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">

            @forelse($materialReceiveds as $index => $entry)

                <tr class="hover:bg-gray-50">
                    <td class="p-3">{{ $materialReceiveds->firstItem() + $index }}</td>
                    <td class="p-3">{{ $entry->received_date ?? '-' }}</td>
                    <td class="p-3">{{ $entry->project?->project_name ?? '-' }}</td>
                    <td class="p-3">{{ $entry->block?->name ?? '-' }}</td>
                    <td class="p-3">{{ $entry->material?->material_name ?? $entry->material_name ?? '-' }}</td>

                    <td class="p-3 text-center font-bold">
                        {{ number_format($entry->quantity_received, 2) }}
                    </td>

                    <td class="p-3">{{ $entry->unit ?? '-' }}</td>

                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-xs
                            {{ $entry->pmo_verification_status === 'Verified' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $entry->pmo_verification_status === 'Rejected' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $entry->pmo_verification_status === 'Hold' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ !$entry->pmo_verification_status ? 'bg-yellow-100 text-yellow-800' : '' }}">
                            {{ $entry->pmo_verification_status ?? 'Pending' }}
                        </span>
                    </td>

                    <td class="p-3">
                        <a href="{{ route('material-verifications.show', $entry) }}"
                           class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                            Verify
                        </a>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="9" class="p-6 text-center text-gray-500">
                        No material received records found.
                    </td>
                </tr>
            @endforelse

            </tbody>
        </table>

    </div>
</div>

<div class="mt-4">
    {{ $materialReceiveds->links() }}
</div>

@endsection