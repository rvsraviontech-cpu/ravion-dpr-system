@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-[1600px] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Purchase Orders</h1>
            <p class="mt-1 text-sm text-gray-500">Procurement orders created from approved Material Requirements.</p>
        </div>

        <a href="{{ route('purchase-orders.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-[#10212F] px-4 py-2 text-sm font-medium text-white hover:opacity-90">
            + Create Purchase Order
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" class="mb-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Search PO, Project or Vendor..."
                   class="rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">

            <select name="status"
                    class="rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                <option value="">All Statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <button class="rounded-lg bg-[#10212F] px-4 py-2 text-sm font-medium text-white">Filter</button>
                <a href="{{ route('purchase-orders.index') }}"
                   class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="max-h-[470px] overflow-auto">
            <table class="min-w-[1000px] w-full text-sm">
                <thead class="sticky top-0 bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-left">PO</th>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Project</th>
                        <th class="px-4 py-3 text-left">Vendor</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($purchaseOrders as $po)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $po->po_number }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ optional($po->po_date)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $po->project_name }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $po->vendor_name }}</td>
                            <td class="px-4 py-3 text-right font-medium">₹ {{ number_format((float) $po->grand_total, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                    {{ $po->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('purchase-orders.show', $po) }}"
                                   class="text-sm font-medium text-blue-700 hover:underline">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-500">No Purchase Orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($purchaseOrders->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $purchaseOrders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
