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

    {{-- Compact PME action queue: one row per approved MR with pending PO allocation. --}}
    <section class="mb-4 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 px-3 py-2">
            <h2 class="text-sm font-semibold text-gray-900">Pending Purchase Orders</h2>
            <span class="text-xs text-gray-500">{{ $pendingRequirements->count() }} MRs · {{ $pendingItemCount }} pending items</span>
        </div>
        <div class="max-h-[220px] overflow-y-auto overscroll-contain">
            <table class="w-full table-fixed text-xs sm:text-sm">
                <thead class="sticky top-0 z-10 bg-gray-50 text-[11px] uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="w-[105px] px-3 py-2 text-left">MR</th>
                        <th class="w-[19%] px-3 py-2 text-left">Project</th>
                        <th class="w-[108px] px-2 py-2 text-left">MR Date</th>
                        <th class="px-3 py-2 text-left">Material</th>
                        <th class="w-[65px] px-2 py-2 text-center">Items</th>
                        <th class="w-[116px] px-3 py-2 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($pendingRequirements as $mrId => $mrItems)
                        @php
                            $mr = $mrItems->first()?->materialRequirement;
                            $firstPending = $mrItems->first();
                            $firstMaterial = $firstPending?->materialType?->material_type_name ?? 'Material';
                            $firstVariant = $firstPending?->materialVariant?->variant_name;
                            $firstSize = $firstPending?->materialVariant?->size_dimension;
                            $firstLabel = collect([$firstMaterial, $firstVariant, $firstSize])->filter()->unique()->implode(' · ');
                            $remaining = $mrItems->count() - 1;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-3 py-2 font-semibold text-gray-900">MR-{{ str_pad((string) $mrId, 4, '0', STR_PAD_LEFT) }}</td>
                            <td class="truncate px-3 py-2 text-gray-700" title="{{ $mr?->project?->project_name ?? '-' }}">{{ $mr?->project?->project_name ?? '-' }}</td>
                            <td class="whitespace-nowrap px-2 py-2 text-gray-600">{{ $mr?->required_date ? \Illuminate\Support\Carbon::parse($mr->required_date)->format('d/m/Y') : '-' }}</td>
                            <td class="max-w-0 px-3 py-2 text-gray-700">
                                <div class="truncate" title="{{ $firstLabel }}{{ $remaining > 0 ? ' +'.$remaining.' more' : '' }}">
                                    {{ $firstLabel }}@if($remaining > 0) <span class="font-medium text-gray-500">+{{ $remaining }} more</span>@endif
                                </div>
                            </td>
                            <td class="px-2 py-2 text-center tabular-nums text-gray-700">{{ $mrItems->count() }}</td>
                            <td class="px-3 py-1.5 text-right">
                                <a href="{{ route('purchase-orders.create', ['project_id' => $mr?->project_id, 'material_requirement_id' => $mrId]) }}"
                                   class="inline-flex whitespace-nowrap rounded-md bg-[#10212F] px-2.5 py-1.5 text-xs font-medium text-white hover:opacity-90">Create PO →</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">No approved MRs pending purchase orders.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-3 py-2">
            <h2 class="text-sm font-semibold text-gray-900">Existing Purchase Orders</h2>
        </div>
        <div class="max-h-[470px] overflow-auto">
            <table class="w-full table-fixed text-xs sm:text-sm">
                <thead class="sticky top-0 bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="w-[115px] px-2 py-2 text-left">PO</th>
                        <th class="w-[85px] px-2 py-2 text-left">Date</th>
                        <th class="w-[15%] px-2 py-2 text-left">Project</th>
                        <th class="w-[15%] px-2 py-2 text-left">Vendor</th>
                        <th class="px-2 py-2 text-left">Material Items</th>
                        <th class="w-[95px] px-2 py-2 text-right">Amount</th>
                        <th class="w-[105px] px-2 py-2 text-left">Status</th>
                        <th class="w-[55px] px-2 py-2 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($purchaseOrders as $po)
                        @php
                            $itemNames = $po->items->map(function ($item) {
                                $name = trim((string) ($item->product_name ?: $item->materialType?->material_type_name ?: 'Material'));
                                $spec = trim((string) ($item->specification_text ?? ''));
                                return $spec !== '' ? $name . ' · ' . $spec : $name;
                            })->filter()->values();
                            $itemsLabel = $itemNames->implode(', ');
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-2 py-2 font-semibold text-gray-900">{{ $po->po_number }}</td>
                            <td class="whitespace-nowrap px-2 py-2 text-gray-700">{{ optional($po->po_date)->format('d/m/Y') }}</td>
                            <td class="max-w-0 px-2 py-2 text-gray-700"><div class="truncate" title="{{ $po->project_name }}">{{ $po->project_name }}</div></td>
                            <td class="max-w-0 px-2 py-2 text-gray-700"><div class="truncate" title="{{ $po->vendor_name }}">{{ $po->vendor_name }}</div></td>
                            <td class="max-w-0 px-2 py-2 text-gray-700">
                                <div class="truncate" title="{{ $itemsLabel }}">
                                    {{ $itemNames->first() ?? '-' }}@if($itemNames->count() > 1) <span class="font-medium text-gray-500">+{{ $itemNames->count() - 1 }} more</span>@endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-2 py-2 text-right font-medium">₹ {{ number_format((float) $po->grand_total, 2) }}</td>
                            <td class="px-2 py-2"><span class="inline-block max-w-full truncate rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-700">{{ $po->status }}</span></td>
                            <td class="px-2 py-2 text-right"><a href="{{ route('purchase-orders.show', $po) }}" class="font-medium text-blue-700 hover:underline">View</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-gray-500">No Purchase Orders found.</td>
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
    </section>
</div>
@endsection
