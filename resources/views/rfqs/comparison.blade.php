@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6">
    <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
        <div>
            <a href="{{ route('rfqs.show', $header->id) }}" class="text-xs text-blue-700 hover:underline">← Back to RFQ</a>
            <h1 class="mt-2 text-xl font-semibold text-slate-900">Quotation Comparison</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $header->rfq_number }} · {{ $header->project_name ?? 'Project' }}</p>
        </div>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">Submitted quotations only</span>
    </div>

    @if($submitted->isEmpty())
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
            No final vendor quotations have been submitted for this RFQ. Draft quotations are excluded from comparison.
        </div>
    @else
        <div class="mb-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($submitted as $quote)
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">{{ $quote->vendor_name }}</div>
                    <div class="mt-1 text-xs text-slate-500">Submitted: {{ $quote->submitted_at ? \Carbon\Carbon::parse($quote->submitted_at)->format('d M Y, h:i A') : '—' }}</div>
                    <div class="mt-3 flex justify-between text-xs"><span>Taxable products</span><span>₹{{ number_format((float)$quote->product_amount, 2) }}</span></div>
                    <div class="mt-1 flex justify-between text-xs"><span>GST</span><span>₹{{ number_format((float)$quote->tax_amount, 2) }}</span></div>
                    @foreach(['auto_charges'=>'Auto', 'hamali_charges'=>'Hamali', 'loading_charges'=>'Loading', 'unloading_charges'=>'Unloading'] as $column => $label)
                        <div class="mt-1 flex justify-between text-xs text-slate-600"><span>{{ $label }} charges</span><span>₹{{ number_format((float)($quote->{$column} ?? 0), 2) }}</span></div>
                    @endforeach
                    <div class="mt-3 flex justify-between border-t border-slate-200 pt-3 text-sm font-semibold"><span>Quotation total</span><span>₹{{ number_format((float)$quote->net_total_amount, 2) }}</span></div>
                </div>
            @endforeach
        </div>
    @endif

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</div>
    @endif
    @php
        // Calculate orderable balances from actual non-cancelled/non-rejected PO allocations.
        // This is only for UI guidance: RfqAwardController revalidates on the server.
        $rfqItemIds = $comparison->pluck('item.material_requirement_item_id')->filter()->unique()->values();
        $orderedByMr = $rfqItemIds->isEmpty() ? collect() : \Illuminate\Support\Facades\DB::table('purchase_order_item_allocations as a')
            ->join('purchase_order_items as poi', 'poi.id', '=', 'a.purchase_order_item_id')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->whereIn('a.material_requirement_item_id', $rfqItemIds)
            ->whereNotIn('po.status', [\App\Models\PurchaseOrder::STATUS_CANCELLED, \App\Models\PurchaseOrder::STATUS_REJECTED])
            ->groupBy('a.material_requirement_item_id')
            ->select('a.material_requirement_item_id', \Illuminate\Support\Facades\DB::raw('SUM(a.allocated_quantity) as ordered_qty'))
            ->pluck('ordered_qty', 'material_requirement_item_id');
        $requiredByMr = $rfqItemIds->isEmpty() ? collect() : \Illuminate\Support\Facades\DB::table('material_requirement_items')
            ->whereIn('id', $rfqItemIds)->pluck('required_quantity', 'id');
        $awardedByRfqItem = \Illuminate\Support\Facades\DB::table('rfq_award_items')
            ->whereIn('rfq_item_id', $comparison->pluck('item.id')->all())
            ->groupBy('rfq_item_id')
            ->select('rfq_item_id', \Illuminate\Support\Facades\DB::raw('SUM(awarded_quantity) as awarded_qty'))
            ->pluck('awarded_qty', 'rfq_item_id');
    @endphp
    <form action="{{ route('rfqs.awards.review', $header->id) }}" method="POST" id="rfq-award-form">
    @csrf
    @if($submitted->isNotEmpty())
        <div class="mb-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-3">
                <h2 class="text-sm font-semibold text-slate-900">Award vendor selection</h2>
                <p class="mt-1 text-xs text-slate-500">Assign one vendor to all eligible materials in one click, or continue choosing vendors separately for each material.</p>
            </div>
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[210px] flex-1 sm:max-w-sm">
                    <label for="bulk-award-vendor" class="mb-1 block text-xs font-medium text-slate-700">Vendor for all materials</label>
                    <select id="bulk-award-vendor" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                        <option value="">Select vendor</option>
                        @foreach($submitted->unique('vendor_id')->sortBy('vendor_name') as $quote)
                            <option value="{{ $quote->vendor_id }}">{{ $quote->vendor_name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" id="bulk-award-apply" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Apply to all materials</button>
                <button type="button" id="bulk-award-clear" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Clear selections</button>
            </div>
            <p id="bulk-award-feedback" class="mt-3 text-xs text-slate-600" role="status" aria-live="polite">Individual material-wise awards remain available below.</p>
        </div>
    @endif
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-4 py-3 text-sm font-semibold text-slate-800">Material-wise comparison</div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-xs">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-600">
                    <tr><th class="px-4 py-3">Award vendor / Qty</th><th class="px-4 py-3">Material</th><th class="px-4 py-3">Requested Qty</th><th class="px-4 py-3">Vendor</th><th class="px-4 py-3 text-right">MRP / Unit</th><th class="px-4 py-3 text-right">Discount %</th><th class="px-4 py-3 text-right">Taxable</th><th class="px-4 py-3 text-right">GST %</th><th class="px-4 py-3 text-right">Amount with GST</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($comparison as $row)
                    @php
                        $mrId = $row->item->material_requirement_item_id;
                        $remaining = max(0, round(min(
                            (float) $row->item->requested_quantity - (float) $awardedByRfqItem->get($row->item->id, 0),
                            (float) $requiredByMr->get($mrId, 0) - (float) $orderedByMr->get($mrId, 0)
                        ), 3));
                        $canAward = $remaining > 0.0005;
                    @endphp
                    @if($row->offers->isEmpty())
                        <tr><td class="px-4 py-3 text-slate-400">—</td><td class="px-4 py-3 font-medium">{{ $row->item->product_name }}<div class="font-normal text-slate-500">{{ $row->item->specification_text }}</div></td><td class="px-4 py-3">{{ (float)$row->item->requested_quantity }}</td><td colspan="6" class="px-4 py-3 text-slate-500">Awaiting submitted vendor quotations</td></tr>
                    @else
                        @foreach($row->offers as $offer)
                            <tr class="{{ $row->lowest !== null && abs($offer->total - $row->lowest) < 0.005 ? 'bg-emerald-50/60' : '' }}">
                                @if($loop->first)
                                    <td rowspan="{{ $row->offers->count() }}" class="px-4 py-3 align-top">
                                        @if(!$canAward)
                                            <div class="mb-1 rounded bg-amber-50 px-2 py-1 text-[11px] text-amber-800">Already ordered / awarded · 0 remaining</div>
                                        @elseif($remaining + 0.0005 < (float)$row->item->requested_quantity)
                                            <div class="mb-1 text-[11px] text-amber-700">{{ (float)$remaining }} remaining</div>
                                        @endif
                                        <select name="awards[{{ $row->item->id }}][quotation_item_id]" data-rfq-item-id="{{ $row->item->id }}" @disabled(!$canAward) class="rfq-item-award-select w-40 rounded border border-slate-300 p-1 text-xs" aria-label="Award vendor for {{ $row->item->product_name }}">
                                            <option value="">Do not award</option>
                                            @foreach($row->offers as $choice)
                                                <option value="{{ $choice->quotation_item_id }}" data-vendor-id="{{ $choice->vendor_id }}">{{ $choice->vendor_name }} · ₹{{ number_format($choice->total, 2) }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" step="0.001" min="0.001" name="awards[{{ $row->item->id }}][quantity]" value="{{ (float)$remaining }}" max="{{ (float)$remaining }}" @disabled(!$canAward) class="rfq-award-quantity mt-1 w-24 rounded border border-slate-300 p-1 text-xs" aria-label="Award quantity">
                                    </td>
                                    <td rowspan="{{ $row->offers->count() }}" class="px-4 py-3 align-top font-medium text-slate-900">{{ $row->item->product_name }}<div class="mt-1 font-normal text-slate-500">{{ $row->item->specification_text }}</div></td>
                                    <td rowspan="{{ $row->offers->count() }}" class="px-4 py-3 align-top">{{ (float)$row->item->requested_quantity }}</td>
                                @endif
                                <td class="px-4 py-3">{{ $offer->vendor_name }} @if($row->lowest !== null && abs($offer->total - $row->lowest) < 0.005)<span class="ml-1 text-[10px] font-semibold text-emerald-700">Lowest line total</span>@endif</td>
                                <td class="px-4 py-3 text-right">₹{{ number_format($offer->rate, 2) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($offer->discount_percent, 2) }}%</td>
                                <td class="px-4 py-3 text-right">₹{{ number_format($offer->taxable, 2) }}</td>
                                <td class="px-4 py-3 text-right">{{ (float)$offer->tax_percent }}%</td>
                                <td class="px-4 py-3 text-right font-semibold">₹{{ number_format($offer->total, 2) }}</td>
                            </tr>
                        @endforeach
                    @endif
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-slate-500">No materials found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <p class="mt-3 text-xs text-slate-500">Select one submitted vendor quote per material. Review before creating Draft POs. Vendor-level transport and handling charges are not copied automatically.</p>
    @if($submitted->isNotEmpty())
        <div class="mt-4 flex justify-end"><button type="submit" class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-semibold text-white">Review selected awards</button></div>
    @endif
    </form>
    <script>
    (function () {
        const form = document.getElementById('rfq-award-form');
        if (!form) return;

        const vendorPicker = document.getElementById('bulk-award-vendor');
        const applyButton = document.getElementById('bulk-award-apply');
        const clearButton = document.getElementById('bulk-award-clear');
        const feedback = document.getElementById('bulk-award-feedback');
        const awardSelects = Array.from(form.querySelectorAll('.rfq-item-award-select:not(:disabled)'));

        function report(message) {
            if (feedback) feedback.textContent = message;
        }

        if (applyButton) applyButton.addEventListener('click', function () {
            const vendorId = vendorPicker ? vendorPicker.value : '';
            if (!vendorId) {
                report('Please select a vendor first.');
                if (vendorPicker) vendorPicker.focus();
                return;
            }

            let assigned = 0;
            let unavailable = 0;
            awardSelects.forEach(function (select) {
                const matchingOption = Array.from(select.options).find(function (option) {
                    return option.dataset.vendorId === vendorId;
                });
                if (matchingOption) {
                    select.value = matchingOption.value;
                    assigned++;
                } else {
                    // Never retain an earlier vendor award after applying a different bulk vendor.
                    select.value = '';
                    unavailable++;
                }
                select.dispatchEvent(new Event('change', { bubbles: true }));
            });

            const vendorName = vendorPicker.options[vendorPicker.selectedIndex].text;
            report(vendorName + ' selected for ' + assigned + ' material(s).' +
                (unavailable ? ' ' + unavailable + ' material(s) have no submitted quotation from this vendor and remain unselected.' : '') +
                ' Review quantities and prices below before continuing.');
        });

        if (clearButton) clearButton.addEventListener('click', function () {
            awardSelects.forEach(function (select) {
                select.value = '';
                select.dispatchEvent(new Event('change', { bubbles: true }));
            });
            if (vendorPicker) vendorPicker.value = '';
            report('All vendor award selections cleared. You can select vendors individually below.');
        });

        form.addEventListener('submit', function (event) {
            const selected = awardSelects.filter(function (select) { return !!select.value; });
            if (!selected.length) {
                event.preventDefault();
                report('Select at least one vendor award before reviewing.');
                return;
            }

            // Omit unselected lines from the POST, preserving the existing review route.
            awardSelects.forEach(function (select) {
                if (!select.value) {
                    select.disabled = true;
                    const quantity = select.parentElement.querySelector('.rfq-award-quantity');
                    if (quantity) quantity.disabled = true;
                }
            });
        });
    })();
    </script>
</div>
@endsection
