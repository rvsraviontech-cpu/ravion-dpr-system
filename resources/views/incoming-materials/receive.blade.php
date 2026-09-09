@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-[1600px] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-semibold text-slate-900">Receive Incoming Materials</h1>
                <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-200">
                    {{ $materialDispatch->status }}
                </span>
            </div>
            <p class="mt-1 text-sm text-slate-500">
                {{ $materialDispatch->dispatch_number }} · {{ $materialDispatch->project_name }}
            </p>
        </div>

        <a href="{{ route('incoming-materials.index') }}"
           class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
            Back to Incoming Materials
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <div class="font-semibold">Please correct the following:</div>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST"
          action="{{ route('incoming-materials.store', $materialDispatch) }}"
          id="receiptForm">
        @csrf

        <div class="mb-4 grid grid-cols-1 gap-4 xl:grid-cols-12">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm xl:col-span-8">
                <div class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Dispatch Information
                </div>

                <div class="grid grid-cols-2 gap-x-5 gap-y-3 md:grid-cols-4">
                    <div>
                        <div class="text-xs text-slate-500">Dispatch No.</div>
                        <div class="mt-0.5 font-semibold text-slate-900">{{ $materialDispatch->dispatch_number }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">Dispatch Date</div>
                        <div class="mt-0.5 font-medium text-slate-800">
                            {{ optional($materialDispatch->dispatch_date)->format('d/m/Y') ?? $materialDispatch->dispatch_date }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">Dispatch From</div>
                        <div class="mt-0.5 font-medium text-slate-800">{{ $materialDispatch->dispatch_from ?: '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">Project</div>
                        <div class="mt-0.5 font-medium text-slate-800">{{ $materialDispatch->project_name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">Challan</div>
                        <div class="mt-0.5 font-medium text-slate-800">{{ $materialDispatch->challan_number ?: '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">Vehicle</div>
                        <div class="mt-0.5 font-medium text-slate-800">{{ $materialDispatch->vehicle_number ?: '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">Driver</div>
                        <div class="mt-0.5 font-medium text-slate-800">{{ $materialDispatch->driver_name ?: '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">Expected Delivery</div>
                        <div class="mt-0.5 font-medium text-slate-800">
                            {{ optional($materialDispatch->expected_delivery_date)->format('d/m/Y') ?? ($materialDispatch->expected_delivery_date ?: '—') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm xl:col-span-4">
                <div class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Receipt Details
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Receipt Date <span class="text-red-500">*</span></label>
                        <input type="date"
                               name="receipt_date"
                               value="{{ old('receipt_date', now()->toDateString()) }}"
                               required
                               class="w-full rounded-lg border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Challan Number</label>
                        <input type="text"
                               name="challan_number"
                               value="{{ old('challan_number', $materialDispatch->challan_number) }}"
                               class="w-full rounded-lg border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Vehicle Number</label>
                        <input type="text"
                               name="vehicle_number"
                               value="{{ old('vehicle_number', $materialDispatch->vehicle_number) }}"
                               class="w-full rounded-lg border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Driver Name</label>
                        <input type="text"
                               name="driver_name"
                               value="{{ old('driver_name', $materialDispatch->driver_name) }}"
                               class="w-full rounded-lg border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-4 py-3">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-semibold text-slate-900">Material Receipt Quantities</h2>
                        <p class="text-xs text-slate-500">
                            Receive Now must equal Accepted + Damaged + Rejected. Short is entered separately.
                        </p>
                    </div>
                    <div class="text-xs text-slate-500">
                        Qty up to 3 decimals
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1450px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-3">#</th>
                            <th class="px-3 py-3 min-w-[260px]">Product</th>
                            <th class="px-3 py-3 min-w-[150px]">Specification</th>
                            <th class="px-3 py-3 min-w-[120px]">Brand</th>
                            <th class="px-3 py-3 text-right">Sent</th>
                            <th class="px-3 py-3 text-right">Previously Received</th>
                            <th class="px-3 py-3 text-right">Pending</th>
                            <th class="px-3 py-3 w-[125px]">Receive Now</th>
                            <th class="px-3 py-3 w-[125px]">Accepted</th>
                            <th class="px-3 py-3 w-[110px]">Short</th>
                            <th class="px-3 py-3 w-[110px]">Damaged</th>
                            <th class="px-3 py-3 w-[110px]">Rejected</th>
                            <th class="px-3 py-3 min-w-[180px]">Remarks</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($receiptRows as $i => $row)
                            @php
                                $oldReceived = old("items.$i.received_quantity", '');
                                $oldAccepted = old("items.$i.accepted_quantity", '');
                                $oldShort = old("items.$i.short_quantity", 0);
                                $oldDamaged = old("items.$i.damaged_quantity", 0);
                                $oldRejected = old("items.$i.rejected_quantity", 0);
                            @endphp

                            <tr data-receipt-row
                                data-pending="{{ number_format($row['remaining_quantity'], 3, '.', '') }}"
                                class="align-top hover:bg-slate-50/50">
                                <td class="px-3 py-3 text-slate-500">{{ $loop->iteration }}</td>

                                <td class="px-3 py-3">
                                    <input type="hidden"
                                           name="items[{{ $i }}][material_dispatch_item_id]"
                                           value="{{ $row['material_dispatch_item_id'] }}">

                                    <div class="font-semibold text-slate-900">{{ $row['product_name'] }}</div>
                                    @if ($row['product_code'])
                                        <div class="mt-0.5 text-xs text-slate-500">{{ $row['product_code'] }}</div>
                                    @endif
                                </td>

                                <td class="px-3 py-3 text-slate-700">
                                    {{ $row['specification_text'] ?: '—' }}
                                </td>

                                <td class="px-3 py-3 text-slate-700">
                                    {{ $row['brand_name'] ?: '—' }}
                                </td>

                                <td class="px-3 py-3 text-right font-medium text-slate-800">
                                    {{ number_format($row['dispatched_quantity'], 3) }}
                                    <div class="text-[11px] font-normal text-slate-500">{{ $row['unit_code'] ?: $row['unit_name'] }}</div>
                                </td>

                                <td class="px-3 py-3 text-right text-slate-700">
                                    {{ number_format($row['previously_received_quantity'], 3) }}
                                    @if (($row['previously_short_quantity'] ?? 0) > 0)
                                        <div class="text-[11px] text-amber-600">
                                            + {{ number_format($row['previously_short_quantity'], 3) }} short
                                        </div>
                                    @endif
                                </td>

                                <td class="px-3 py-3 text-right">
                                    <span class="font-semibold text-[#10212F]">
                                        {{ number_format($row['remaining_quantity'], 3) }}
                                    </span>
                                </td>

                                <td class="px-3 py-3">
                                    <input type="number"
                                           step="0.001"
                                           min="0"
                                           max="{{ number_format($row['remaining_quantity'], 3, '.', '') }}"
                                           name="items[{{ $i }}][received_quantity]"
                                           value="{{ $oldReceived }}"
                                           data-received
                                           class="w-full rounded-lg border-slate-300 text-right text-sm focus:border-slate-500 focus:ring-slate-500"
                                           placeholder="0">
                                </td>

                                <td class="px-3 py-3">
                                    <input type="number"
                                           step="0.001"
                                           min="0"
                                           name="items[{{ $i }}][accepted_quantity]"
                                           value="{{ $oldAccepted }}"
                                           data-accepted
                                           class="w-full rounded-lg border-slate-300 text-right text-sm focus:border-slate-500 focus:ring-slate-500"
                                           placeholder="0">
                                </td>

                                <td class="px-3 py-3">
                                    <input type="number"
                                           step="0.001"
                                           min="0"
                                           name="items[{{ $i }}][short_quantity]"
                                           value="{{ $oldShort }}"
                                           data-short
                                           class="w-full rounded-lg border-slate-300 text-right text-sm focus:border-slate-500 focus:ring-slate-500">
                                </td>

                                <td class="px-3 py-3">
                                    <input type="number"
                                           step="0.001"
                                           min="0"
                                           name="items[{{ $i }}][damaged_quantity]"
                                           value="{{ $oldDamaged }}"
                                           data-damaged
                                           class="w-full rounded-lg border-slate-300 text-right text-sm focus:border-slate-500 focus:ring-slate-500">
                                </td>

                                <td class="px-3 py-3">
                                    <input type="number"
                                           step="0.001"
                                           min="0"
                                           name="items[{{ $i }}][rejected_quantity]"
                                           value="{{ $oldRejected }}"
                                           data-rejected
                                           class="w-full rounded-lg border-slate-300 text-right text-sm focus:border-slate-500 focus:ring-slate-500">
                                </td>

                                <td class="px-3 py-3">
                                    <input type="text"
                                           name="items[{{ $i }}][remarks]"
                                           value="{{ old("items.$i.remarks") }}"
                                           class="w-full rounded-lg border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500"
                                           placeholder="Optional">
                                    <div data-row-message class="mt-1 hidden text-[11px] font-medium"></div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <label class="mb-1 block text-xs font-medium text-slate-600">Receipt Remarks</label>
                <textarea name="receipt_remarks"
                          rows="3"
                          class="w-full rounded-lg border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500"
                          placeholder="Delivery condition, packing observations, site notes...">{{ old('receipt_remarks') }}</textarea>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <label class="mb-1 block text-xs font-medium text-slate-600">Internal Remarks</label>
                <textarea name="internal_remarks"
                          rows="3"
                          class="w-full rounded-lg border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500"
                          placeholder="Internal team notes...">{{ old('internal_remarks') }}</textarea>
            </div>
        </div>

        <div class="sticky bottom-0 z-20 mt-5 rounded-xl border border-slate-200 bg-white/95 px-4 py-3 shadow-lg backdrop-blur">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-sm font-semibold text-slate-900">Save Receipt Draft</div>
                    <div class="text-xs text-slate-500">
                        Quantities are posted to the dispatch only after you confirm the saved receipt.
                    </div>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('incoming-materials.index') }}"
                       class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Cancel
                    </a>

                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-[#10212F] px-5 py-2.5 text-sm font-semibold text-white hover:opacity-95">
                        Save Receipt Draft
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-receipt-row]').forEach(function (row) {
        const received = row.querySelector('[data-received]');
        const accepted = row.querySelector('[data-accepted]');
        const shortQty = row.querySelector('[data-short]');
        const damaged = row.querySelector('[data-damaged]');
        const rejected = row.querySelector('[data-rejected]');
        const message = row.querySelector('[data-row-message]');
        const pending = Number(row.dataset.pending || 0);

        let acceptedTouched = accepted.value !== '';

        accepted.addEventListener('input', function () {
            acceptedTouched = true;
            validateRow();
        });

        received.addEventListener('input', function () {
            const rv = Number(received.value || 0);

            if (!acceptedTouched) {
                accepted.value = received.value;
            }

            validateRow();
        });

        [shortQty, damaged, rejected].forEach(function (input) {
            input.addEventListener('input', validateRow);
        });

        function validateRow() {
            const r = Number(received.value || 0);
            const a = Number(accepted.value || 0);
            const s = Number(shortQty.value || 0);
            const d = Number(damaged.value || 0);
            const rej = Number(rejected.value || 0);

            const physical = a + d + rej;
            const accounted = r + s;

            received.classList.remove('border-red-400', 'border-emerald-400');
            message.classList.add('hidden');
            message.classList.remove('text-red-600', 'text-emerald-600');

            if (Math.abs(r - physical) > 0.0005) {
                received.classList.add('border-red-400');
                message.textContent = 'Receive Now must equal Accepted + Damaged + Rejected.';
                message.classList.remove('hidden');
                message.classList.add('text-red-600');
                return;
            }

            if (accounted > pending + 0.0005) {
                received.classList.add('border-red-400');
                message.textContent = 'Receive Now + Short exceeds the pending dispatch quantity.';
                message.classList.remove('hidden');
                message.classList.add('text-red-600');
                return;
            }

            if (accounted > 0) {
                received.classList.add('border-emerald-400');
                message.textContent = 'Quantity balance is valid.';
                message.classList.remove('hidden');
                message.classList.add('text-emerald-600');
            }
        }

        validateRow();
    });
});
</script>
@endsection
