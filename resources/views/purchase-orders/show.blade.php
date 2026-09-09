@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-[1500px] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-xl font-semibold text-gray-900">{{ $purchaseOrder->po_number }}</h1>

                <span class="rounded-full px-2.5 py-1 text-xs font-medium
                    @if($purchaseOrder->status === \App\Models\PurchaseOrder::STATUS_DRAFT)
                        bg-amber-100 text-amber-700
                    @elseif(in_array($purchaseOrder->status, [
                        \App\Models\PurchaseOrder::STATUS_ISSUED,
                        \App\Models\PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
                        \App\Models\PurchaseOrder::STATUS_RECEIVED,
                        \App\Models\PurchaseOrder::STATUS_CLOSED,
                    ], true))
                        bg-green-100 text-green-700
                    @else
                        bg-gray-100 text-gray-700
                    @endif
                ">
                    {{ $purchaseOrder->status }}
                </span>
            </div>
            <p class="mt-1 text-sm text-gray-500">Purchase Order</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('purchase-orders.index') }}"
               class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Back
            </a>

            @if ($purchaseOrder->status === \App\Models\PurchaseOrder::STATUS_DRAFT)
                <form method="POST"
                      action="{{ route('purchase-orders.place-order', $purchaseOrder) }}"
                      onsubmit="return confirm('Place {{ $purchaseOrder->po_number }} with the vendor? Once placed, this PO will be treated as ordered.');">
                    @csrf
                    <button type="submit"
                            class="rounded-lg bg-[#10212F] px-4 py-2 text-sm font-medium text-white hover:opacity-90">
                        Place Order
                    </button>
                </form>
            @endif

            @if (in_array($purchaseOrder->status, [
                \App\Models\PurchaseOrder::STATUS_ISSUED,
                \App\Models\PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
                \App\Models\PurchaseOrder::STATUS_RECEIVED,
                \App\Models\PurchaseOrder::STATUS_CLOSED,
            ], true))
                <a href="{{ route('purchase-orders.pdf', $purchaseOrder) }}"
                   class="rounded-lg border border-[#10212F] bg-white px-4 py-2 text-sm font-medium text-[#10212F] hover:bg-gray-50">
                    Download Vendor PDF
                </a>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-xs uppercase tracking-wide text-gray-400">Project</div>
            <div class="mt-1 font-semibold text-gray-900">{{ $purchaseOrder->project_name }}</div>

            <div class="mt-3 text-xs uppercase tracking-wide text-gray-400">PO Date</div>
            <div class="mt-1 text-sm text-gray-700">{{ optional($purchaseOrder->po_date)->format('d/m/Y') }}</div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-xs uppercase tracking-wide text-gray-400">Vendor</div>
            <div class="mt-1 font-semibold text-gray-900">{{ $purchaseOrder->vendor_name }}</div>
            <div class="mt-1 text-sm text-gray-500">{{ $purchaseOrder->vendor_gst_number ?: 'GST not recorded' }}</div>

            @if ($purchaseOrder->vendor_reference)
                <div class="mt-3 text-xs uppercase tracking-wide text-gray-400">Vendor Reference</div>
                <div class="mt-1 text-sm text-gray-700">{{ $purchaseOrder->vendor_reference }}</div>
            @endif
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-xs uppercase tracking-wide text-gray-400">Grand Total</div>
            <div class="mt-1 text-xl font-semibold text-gray-900">
                ₹ {{ number_format((float) $purchaseOrder->grand_total, 2) }}
            </div>

            <div class="mt-3 text-xs uppercase tracking-wide text-gray-400">Expected Delivery</div>
            <div class="mt-1 text-sm text-gray-700">
                {{ $purchaseOrder->expected_delivery_date
                    ? $purchaseOrder->expected_delivery_date->format('d/m/Y')
                    : '-' }}
            </div>
        </div>
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-900">Ordered Items</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[1350px] w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-3 py-2 text-left">#</th>
                        <th class="px-3 py-2 text-left">Product</th>
                        <th class="px-3 py-2 text-left">Specification / Size</th>
                        <th class="px-3 py-2 text-left">Brand</th>
                        <th class="px-3 py-2 text-right">Qty</th>
                        <th class="px-3 py-2 text-left">Unit</th>
                        <th class="px-3 py-2 text-right">Rate</th>
                        <th class="px-3 py-2 text-right">Tax</th>
                        <th class="px-3 py-2 text-right">Amount</th>
                        <th class="px-3 py-2 text-left">Remarks</th>
                        <th class="px-3 py-2 text-left">MR</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @foreach ($purchaseOrder->items as $index => $item)
                        <tr>
                            <td class="px-3 py-3">{{ $index + 1 }}</td>
                            <td class="px-3 py-3 font-medium text-gray-900">{{ $item->product_name }}</td>
                            <td class="px-3 py-3 text-gray-700">{{ $item->specification_text ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-700">{{ $item->brand_name ?: '-' }}</td>
                            <td class="px-3 py-3 text-right">
                                {{ rtrim(rtrim(number_format((float) $item->ordered_quantity, 3, '.', ''), '0'), '.') }}
                            </td>
                            <td class="px-3 py-3 text-gray-700">{{ $item->unit_code ?: $item->unit_name ?: '-' }}</td>
                            <td class="px-3 py-3 text-right">₹ {{ number_format((float) $item->rate, 2) }}</td>
                            <td class="px-3 py-3 text-right">
                                {{ rtrim(rtrim(number_format((float) $item->tax_percent, 2, '.', ''), '0'), '.') }}%
                            </td>
                            <td class="px-3 py-3 text-right font-medium">₹ {{ number_format((float) $item->line_amount, 2) }}</td>
                            <td class="max-w-xs px-3 py-3 text-gray-700">{{ $item->remarks ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-700">
                                @foreach ($item->allocations as $allocation)
                                    MR-{{ str_pad(
                                        $allocation->materialRequirementItem->material_requirement_id,
                                        4,
                                        '0',
                                        STR_PAD_LEFT
                                    ) }}
                                    @if (!$loop->last), @endif
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 bg-gray-50 px-4 py-4">
            <div class="ml-auto max-w-sm space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Subtotal</span>
                    <span>₹ {{ number_format((float) $purchaseOrder->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Discount</span>
                    <span>₹ {{ number_format((float) $purchaseOrder->discount_amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Tax</span>
                    <span>₹ {{ number_format((float) $purchaseOrder->tax_amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Other Charges</span>
                    <span>₹ {{ number_format((float) $purchaseOrder->other_charges, 2) }}</span>
                </div>
                <div class="flex justify-between border-t border-gray-300 pt-2 text-base font-semibold">
                    <span>Grand Total</span>
                    <span>₹ {{ number_format((float) $purchaseOrder->grand_total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    @if (
        $purchaseOrder->delivery_address
        || $purchaseOrder->payment_terms
        || $purchaseOrder->delivery_terms
        || $purchaseOrder->freight_terms
        || $purchaseOrder->vendor_notes
        || $purchaseOrder->terms_conditions
    )
        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Delivery & Commercial Terms</h2>

                <dl class="mt-3 space-y-3 text-sm">
                    @if ($purchaseOrder->delivery_address)
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Delivery Address</dt>
                            <dd class="mt-1 text-gray-700">{{ $purchaseOrder->delivery_address }}</dd>
                        </div>
                    @endif

                    @if ($purchaseOrder->payment_terms)
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Payment Terms</dt>
                            <dd class="mt-1 text-gray-700">{{ $purchaseOrder->payment_terms }}</dd>
                        </div>
                    @endif

                    @if ($purchaseOrder->delivery_terms)
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Delivery Terms</dt>
                            <dd class="mt-1 text-gray-700">{{ $purchaseOrder->delivery_terms }}</dd>
                        </div>
                    @endif

                    @if ($purchaseOrder->freight_terms)
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Freight / Transport</dt>
                            <dd class="mt-1 text-gray-700">{{ $purchaseOrder->freight_terms }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Vendor Notes / Terms</h2>

                @if ($purchaseOrder->vendor_notes)
                    <div class="mt-3 whitespace-pre-line text-sm text-gray-700">{{ $purchaseOrder->vendor_notes }}</div>
                @endif

                @if ($purchaseOrder->terms_conditions)
                    <div class="mt-3 whitespace-pre-line text-sm text-gray-700">{{ $purchaseOrder->terms_conditions }}</div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
