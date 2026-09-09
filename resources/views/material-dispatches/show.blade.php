@extends('layouts.app')

@section('content')
@php
    $statusClass = match($materialDispatch->status) {
        'Draft' => 'bg-gray-100 text-gray-700',
        'Dispatched' => 'bg-blue-100 text-blue-700',
        'Partially Received' => 'bg-amber-100 text-amber-700',
        'Received' => 'bg-emerald-100 text-emerald-700',
        'Closed' => 'bg-slate-200 text-slate-700',
        'Cancelled' => 'bg-red-100 text-red-700',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<div class="mx-auto max-w-[1600px] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900">{{ $materialDispatch->dispatch_number }}</h1>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                    {{ $materialDispatch->status }}
                </span>
            </div>
            <p class="mt-1 text-sm text-gray-500">Head Office Material Dispatch</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('material-dispatches.index') }}"
               class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Back
            </a>

            @if($materialDispatch->status === 'Draft')
                <a href="{{ route('material-dispatches.edit', $materialDispatch) }}"
                   class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-700 hover:bg-amber-100">
                    Edit Draft
                </a>

                <form method="POST"
                      action="{{ route('material-dispatches.dispatch', $materialDispatch) }}"
                      onsubmit="return confirm('Mark these materials as dispatched to site?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="rounded-lg bg-[#10212F] px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                        Dispatch Materials
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="mb-5 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm xl:col-span-2">
            <h2 class="mb-4 border-b border-gray-100 pb-3 text-lg font-bold text-gray-800">Dispatch Information</h2>

            <div class="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Project</div>
                    <div class="mt-1 font-semibold text-gray-800">{{ $materialDispatch->project_name }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Dispatch Date</div>
                    <div class="mt-1 text-gray-800">{{ $materialDispatch->dispatch_date?->format('d/m/Y') }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Dispatch From</div>
                    <div class="mt-1 text-gray-800">{{ $materialDispatch->dispatch_from }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Expected Delivery</div>
                    <div class="mt-1 text-gray-800">
                        {{ $materialDispatch->expected_delivery_date?->format('d/m/Y') ?: '-' }}
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Delivery Address</div>
                    <div class="mt-1 whitespace-pre-line text-gray-800">
                        {{ $materialDispatch->delivery_address ?: '-' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 border-b border-gray-100 pb-3 text-lg font-bold text-gray-800">Dispatch Summary</h2>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Items</span>
                    <span class="font-bold text-gray-800">{{ $materialDispatch->items->count() }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Dispatched Qty</span>
                    <span class="font-bold text-blue-700">
                        {{ number_format((float) $materialDispatch->total_dispatched_quantity, 3) }}
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Accepted Qty</span>
                    <span class="font-bold text-emerald-700">
                        {{ number_format((float) $materialDispatch->total_accepted_quantity, 3) }}
                    </span>
                </div>

                @if($materialDispatch->dispatched_at)
                    <div class="border-t border-gray-100 pt-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Dispatched At</div>
                        <div class="mt-1 text-sm text-gray-700">
                            {{ $materialDispatch->dispatched_at->format('d/m/Y h:i A') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="mb-5 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="text-lg font-bold text-gray-800">Material List</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1200px] text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="w-12 px-3 py-3 text-center">#</th>
                        <th class="min-w-[260px] px-3 py-3 text-left">Product</th>
                        <th class="min-w-[180px] px-3 py-3 text-left">Specification</th>
                        <th class="min-w-[140px] px-3 py-3 text-left">Brand</th>
                        <th class="w-28 px-3 py-3 text-right">Dispatched</th>
                        <th class="w-28 px-3 py-3 text-left">Unit</th>
                        <th class="min-w-[170px] px-3 py-3 text-left">Source</th>
                        <th class="min-w-[200px] px-3 py-3 text-left">Remarks</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @foreach($materialDispatch->items as $item)
                        @php
                            $allocation = $item->allocations->first();
                            $requirementId = $allocation?->materialRequirementItem?->material_requirement_id;
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-3 text-center text-gray-500">{{ $loop->iteration }}</td>

                            <td class="px-3 py-3">
                                <div class="font-semibold text-gray-800">{{ $item->product_name }}</div>
                                @if($item->product_code)
                                    <div class="mt-0.5 text-xs text-gray-400">{{ $item->product_code }}</div>
                                @endif
                            </td>

                            <td class="px-3 py-3">{{ $item->specification_text ?: '-' }}</td>
                            <td class="px-3 py-3">{{ $item->brand_name ?: '-' }}</td>

                            <td class="px-3 py-3 text-right font-bold text-blue-700">
                                {{ number_format((float) $item->dispatched_quantity, 3) }}
                            </td>

                            <td class="px-3 py-3">{{ $item->unit_name }}</td>

                            <td class="px-3 py-3">
                                @if($requirementId)
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        MR-{{ str_pad((string) $requirementId, 4, '0', STR_PAD_LEFT) }}
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                        Direct HO Dispatch
                                    </span>
                                @endif
                            </td>

                            <td class="px-3 py-3">{{ $item->remarks ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 border-b border-gray-100 pb-3 text-lg font-bold text-gray-800">Transport / Challan</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach([
                    'Transport Mode' => $materialDispatch->transport_mode,
                    'Vehicle Number' => $materialDispatch->vehicle_number,
                    'Driver Name' => $materialDispatch->driver_name,
                    'Driver Mobile' => $materialDispatch->driver_mobile,
                    'Transporter' => $materialDispatch->transporter_name,
                    'Challan Number' => $materialDispatch->challan_number,
                    'Reference Number' => $materialDispatch->reference_number,
                ] as $label => $value)
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $label }}</div>
                        <div class="mt-1 text-sm text-gray-800">{{ $value ?: '-' }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 border-b border-gray-100 pb-3 text-lg font-bold text-gray-800">Notes</h2>

            <div class="space-y-4">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Dispatch Notes</div>
                    <div class="mt-1 whitespace-pre-line text-sm text-gray-800">
                        {{ $materialDispatch->dispatch_notes ?: '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Internal Remarks</div>
                    <div class="mt-1 whitespace-pre-line text-sm text-gray-800">
                        {{ $materialDispatch->internal_remarks ?: '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
