@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8">
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-6">
        <h1 class="text-lg font-semibold text-gray-900">Edit {{ $purchaseOrder->po_number }}</h1>
        <p class="mt-2 text-sm text-gray-700">
            Draft PO editing is intentionally being enabled in the next Purchase Order phase so quantity allocation
            recalculation remains safe. The current Draft can be viewed and deleted if it needs to be recreated.
        </p>
        <div class="mt-4">
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}"
               class="rounded-lg bg-[#10212F] px-4 py-2 text-sm font-medium text-white">
                Back to Purchase Order
            </a>
        </div>
    </div>
</div>
@endsection
