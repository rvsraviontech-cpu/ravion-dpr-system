@extends('layouts.app')

@section('content')
@php
    $receipt = $materialDispatchReceipt;
    $statusClass = match($receipt->status) {
        'Received' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'Cancelled' => 'bg-red-50 text-red-700 ring-red-200',
        default => 'bg-amber-50 text-amber-700 ring-amber-200',
    };
@endphp

<div class="mx-auto max-w-[1600px] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-semibold text-slate-900">Dispatch Receipt</h1>
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $statusClass }}">
                    {{ $receipt->status }}
                </span>
            </div>
            <p class="mt-1 text-sm text-slate-500">
                {{ $receipt->receipt_number }} · {{ $receipt->dispatch_number }} · {{ $receipt->project_name }}
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('incoming-materials.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                Incoming Materials
            </a>

            <a href="{{ route('material-dispatches.show', $receipt->materialDispatch) }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                View Dispatch
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mb-4 grid grid-cols-1 gap-4 xl:grid-cols-12">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm xl:col-span-8">
            <div class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                Receipt Information
            </div>

            <div class="grid grid-cols-2 gap-x-5 gap-y-3 md:grid-cols-4">
                <div>
                    <div class="text-xs text-slate-500">Receipt No.</div>
                    <div class="mt-0.5 font-semibold text-slate-900">{{ $receipt->receipt_number }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Receipt Date</div>
                    <div class="mt-0.5 font-medium text-slate-800">{{ optional($receipt->receipt_date)->format('d/m/Y') }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Dispatch No.</div>
                    <div class="mt-0.5 font-medium text-slate-800">{{ $receipt->dispatch_number }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Project</div>
                    <div class="mt-0.5 font-medium text-slate-800">{{ $receipt->project_name }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Dispatch From</div>
                    <div class="mt-0.5 font-medium text-slate-800">{{ $receipt->dispatch_from ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Challan</div>
                    <div class="mt-0.5 font-medium text-slate-800">{{ $receipt->challan_number ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Vehicle</div>
                    <div class="mt-0.5 font-medium text-slate-800">{{ $receipt->vehicle_number ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Driver</div>
                    <div class="mt-0.5 font-medium text-slate-800">{{ $receipt->driver_name ?: '—' }}</div>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm xl:col-span-4">
            <div class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                Receipt Totals
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-lg bg-slate-50 p-3">
                    <div class="text-xs text-slate-500">Received</div>
                    <div class="mt-1 text-lg font-semibold text-slate-900">{{ number_format($receipt->total_received_quantity, 3) }}</div>
                </div>
                <div class="rounded-lg bg-emerald-50 p-3">
                    <div class="text-xs text-emerald-700">Accepted</div>
                    <div class="mt-1 text-lg font-semibold text-emerald-800">{{ number_format($receipt->total_accepted_quantity, 3) }}</div>
                </div>
                <div class="rounded-lg bg-amber-50 p-3">
                    <div class="text-xs text-amber-700">Short</div>
                    <div class="mt-1 text-lg font-semibold text-amber-800">{{ number_format($receipt->total_short_quantity, 3) }}</div>
                </div>
                <div class="rounded-lg bg-red-50 p-3">
                    <div class="text-xs text-red-700">Damaged / Rejected</div>
                    <div class="mt-1 text-lg font-semibold text-red-800">
                        {{ number_format($receipt->total_damaged_quantity + $receipt->total_rejected_quantity, 3) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-4 py-3">
            <h2 class="font-semibold text-slate-900">Receipt Items</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[1200px] w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-3">#</th>
                        <th class="px-3 py-3 min-w-[240px]">Product</th>
                        <th class="px-3 py-3">Specification</th>
                        <th class="px-3 py-3">Brand</th>
                        <th class="px-3 py-3 text-right">Sent</th>
                        <th class="px-3 py-3 text-right">Previously Received</th>
                        <th class="px-3 py-3 text-right">Receive Now</th>
                        <th class="px-3 py-3 text-right">Accepted</th>
                        <th class="px-3 py-3 text-right">Short</th>
                        <th class="px-3 py-3 text-right">Damaged</th>
                        <th class="px-3 py-3 text-right">Rejected</th>
                        <th class="px-3 py-3 min-w-[180px]">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($receipt->items as $item)
                        <tr>
                            <td class="px-3 py-3 text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-3 py-3">
                                <div class="font-semibold text-slate-900">{{ $item->product_name }}</div>
                                @if ($item->product_code)
                                    <div class="mt-0.5 text-xs text-slate-500">{{ $item->product_code }}</div>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-slate-700">{{ $item->specification_text ?: '—' }}</td>
                            <td class="px-3 py-3 text-slate-700">{{ $item->brand_name ?: '—' }}</td>
                            <td class="px-3 py-3 text-right font-medium text-slate-800">
                                {{ number_format((float) $item->dispatched_quantity, 3) }}
                                <div class="text-[11px] font-normal text-slate-500">{{ $item->unit_code ?: $item->unit_name }}</div>
                            </td>
                            <td class="px-3 py-3 text-right text-slate-700">{{ number_format((float) $item->previously_received_quantity, 3) }}</td>
                            <td class="px-3 py-3 text-right font-medium text-slate-900">{{ number_format((float) $item->received_quantity, 3) }}</td>
                            <td class="px-3 py-3 text-right font-semibold text-emerald-700">{{ number_format((float) $item->accepted_quantity, 3) }}</td>
                            <td class="px-3 py-3 text-right text-amber-700">{{ number_format((float) $item->short_quantity, 3) }}</td>
                            <td class="px-3 py-3 text-right text-red-700">{{ number_format((float) $item->damaged_quantity, 3) }}</td>
                            <td class="px-3 py-3 text-right text-red-700">{{ number_format((float) $item->rejected_quantity, 3) }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ $item->remarks ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($receipt->receipt_remarks || $receipt->internal_remarks)
        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
            @if ($receipt->receipt_remarks)
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Receipt Remarks</div>
                    <div class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $receipt->receipt_remarks }}</div>
                </div>
            @endif

            @if ($receipt->internal_remarks)
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Internal Remarks</div>
                    <div class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $receipt->internal_remarks }}</div>
                </div>
            @endif
        </div>
    @endif

    @if ($receipt->isDraft())
        <div class="sticky bottom-0 z-20 mt-5 rounded-xl border border-amber-200 bg-white/95 px-4 py-3 shadow-lg backdrop-blur">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-sm font-semibold text-slate-900">Receipt is still Draft</div>
                    <div class="text-xs text-slate-500">
                        Confirming will post these quantities to the source dispatch. Accepted quantity becomes stock-eligible.
                    </div>
                </div>

                <form method="POST"
                      action="{{ route('dispatch-receipts.confirm', $receipt) }}"
                      onsubmit="return confirm('Confirm this material receipt? The quantities will be posted to the dispatch.');">
                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-[#10212F] px-5 py-2.5 text-sm font-semibold text-white hover:opacity-95">
                        Confirm Receipt
                    </button>
                </form>
            </div>
        </div>
    @else
        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
            <div class="text-sm font-semibold text-emerald-800">Receipt confirmed</div>
            <div class="mt-0.5 text-xs text-emerald-700">
                Confirmed by {{ $receipt->receivedBy?->name ?: 'System User' }}
                @if ($receipt->received_at)
                    on {{ $receipt->received_at->format('d/m/Y h:i A') }}
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
