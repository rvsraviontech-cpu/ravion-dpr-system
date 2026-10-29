@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-5xl px-4 py-6">
    <a href="{{ route('rfqs.comparison', $rfqId) }}" class="text-sm text-blue-700">← Back to comparison</a>
    <h1 class="mt-3 text-xl font-semibold text-slate-900">Review RFQ Awards</h1>
    <p class="mt-1 text-sm text-slate-500">{{ $rfqNumber }} · {{ $projectName }} · All orders will be saved as Draft.</p>
    <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">Vendor-level Auto, Hamali, Loading and Unloading charges are excluded from Draft POs. Review these separately before placing any PO. The original quotations remain unchanged.</div>
    @if($errors->any())<div class="mt-3 rounded bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    @foreach($groups as $group)
        <section class="mt-5 overflow-hidden rounded-xl border border-slate-200 bg-white">
            <h2 class="border-b bg-slate-50 px-4 py-3 font-semibold">{{ $group['vendorName'] }}</h2>
            <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-50 text-xs"><tr><th class="px-3 py-2 text-left">Material</th><th class="px-3 py-2 text-right">Qty</th><th class="px-3 py-2 text-right">Rate</th><th class="px-3 py-2 text-right">Discount</th><th class="px-3 py-2 text-right">GST</th><th class="px-3 py-2 text-right">Total</th></tr></thead>
            <tbody>@foreach($group['lines'] as $line)<tr class="border-t"><td class="px-3 py-2">{{ $line['item']->product_name }}</td><td class="px-3 py-2 text-right">{{ $line['qty'] }}</td><td class="px-3 py-2 text-right">₹{{ number_format($line['rate'],2) }}</td><td class="px-3 py-2 text-right">₹{{ number_format($line['discount'],2) }}</td><td class="px-3 py-2 text-right">₹{{ number_format($line['tax'],2) }}</td><td class="px-3 py-2 text-right font-semibold">₹{{ number_format($line['total'],2) }}</td></tr>@endforeach</tbody>
            </table></div>
            <div class="border-t px-4 py-3 text-right font-semibold">Draft PO total (excluding vendor-level charges): ₹{{ number_format(collect($group['lines'])->sum('total'), 2) }}</div>
        </section>
    @endforeach
    <form method="POST" action="{{ route('rfqs.awards.store', $rfqId) }}" class="mt-5 flex justify-end">@csrf
        @foreach($selection as $itemId => $award)
            <input type="hidden" name="awards[{{ $itemId }}][quotation_item_id]" value="{{ $award['quotation_item_id'] }}">
            <input type="hidden" name="awards[{{ $itemId }}][quantity]" value="{{ $award['quantity'] }}">
        @endforeach
        <button class="rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white" type="submit">Confirm and Create Draft Purchase Orders</button>
    </form>
</div>
@endsection
