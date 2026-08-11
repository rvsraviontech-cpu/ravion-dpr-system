@extends('layouts.app')

@section('content')

@php
    $roleName = auth()->user()->role?->name;

    $hasPermission = function (string $permission) use ($roleName): bool {
        if ($roleName === 'Admin') {
            return true;
        }

        return auth()->user()
            ->role
            ?->permissions()
            ->where('name', $permission)
            ->where('is_active', true)
            ->exists() ?? false;
    };

    $canEdit = $hasPermission('material_consumed.edit');

    $canApprove = $hasPermission('material_consumed.approve')
        || in_array($roleName, ['PMO', 'DGM'], true);

    $statusClasses = match($materialConsumed->status) {
        'Approved' => 'bg-green-100 text-green-800',
        'Submitted' => 'bg-blue-100 text-blue-800',
        'Rejected' => 'bg-red-100 text-red-800',
        default => 'bg-yellow-100 text-yellow-800',
    };

    $hasNewItems = $materialConsumed->items->isNotEmpty();
@endphp

<div class="mx-auto max-w-full">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <div class="flex flex-wrap items-center gap-3">

                <h1 class="text-3xl font-bold text-gray-800">
                    Material Consumption #{{ $materialConsumed->id }}
                </h1>

                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                    {{ $materialConsumed->status }}
                </span>

            </div>

            <p class="mt-1 text-gray-500">
                Review consumption details, material quantities and wastage information.
            </p>
        </div>

        <div class="flex flex-wrap gap-2 print:hidden">

            @if(
                $materialConsumed->status === 'Draft'
                && $canEdit
            )
                <a href="{{ route('material-consumed.edit', $materialConsumed) }}"
                   class="inline-flex items-center justify-center rounded-lg bg-yellow-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-yellow-600">
                    Edit
                </a>
            @endif

            @if($materialConsumed->status === 'Draft')
                <form method="POST"
                      action="{{ route('material-consumed.submit', $materialConsumed) }}">

                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            onclick="return confirm('Submit this material consumption entry for approval?')"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                        Submit
                    </button>
                </form>
            @endif

            @if(
                $materialConsumed->status === 'Submitted'
                && $canApprove
            )
                <form method="POST"
                      action="{{ route('material-consumed.approve', $materialConsumed) }}">

                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            onclick="return confirm('Approve this material consumption entry?')"
                            class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
                        Approve
                    </button>
                </form>
            @endif

            <button type="button"
                    onclick="window.print()"
                    class="inline-flex items-center justify-center rounded-lg bg-slate-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                Print
            </button>

            <a href="{{ route('material-consumed.index') }}"
               class="inline-flex items-center justify-center rounded-lg bg-gray-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-700">
                Back
            </a>

        </div>

    </div>

    @if(session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Header Information --}}
    <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-3">

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm xl:col-span-2">

            <h2 class="mb-5 text-xl font-bold text-gray-800">
                Consumption Information
            </h2>

            <dl class="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2 xl:grid-cols-3">

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Project
                    </dt>

                    <dd class="mt-1 font-semibold text-gray-800">
                        {{ $materialConsumed->project?->project_name ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Consumed Date
                    </dt>

                    <dd class="mt-1 text-gray-800">
                        {{ $materialConsumed->consumed_date?->format('d/m/Y') ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Consumed Time
                    </dt>

                    <dd class="mt-1 text-gray-800">
                        {{ $materialConsumed->consumed_time ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Block
                    </dt>

                    <dd class="mt-1 text-gray-800">
                        {{ $materialConsumed->block?->name ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Floor
                    </dt>

                    <dd class="mt-1 text-gray-800">
                        {{ $materialConsumed->floor?->name ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Unit
                    </dt>

                    <dd class="mt-1 text-gray-800">
                        {{ $materialConsumed->unit?->name ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Room
                    </dt>

                    <dd class="mt-1 text-gray-800">
                        {{ $materialConsumed->room?->name ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Sub-space
                    </dt>

                    <dd class="mt-1 text-gray-800">
                        {{ $materialConsumed->subspace?->name ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Contractor
                    </dt>

                    <dd class="mt-1 text-gray-800">
                        {{ $materialConsumed->contractor?->contractor_name ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Related Work Output
                    </dt>

                    <dd class="mt-1 text-gray-800">
                        {{ formatQuantity($materialConsumed->related_work_output_quantity) }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Created By
                    </dt>

                    <dd class="mt-1 text-gray-800">
                        {{ $materialConsumed->engineer?->name ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        DPR Reference
                    </dt>

                    <dd class="mt-1 text-gray-800">
                        {{ $materialConsumed->dpr_id ? '#' . $materialConsumed->dpr_id : '-' }}
                    </dd>
                </div>

                <div class="md:col-span-2 xl:col-span-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Remarks
                    </dt>

                    <dd class="mt-1 whitespace-pre-line text-gray-800">
                        {{ $materialConsumed->remarks ?? '-' }}
                    </dd>
                </div>

            </dl>

        </div>

        {{-- Summary --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <h2 class="mb-5 text-xl font-bold text-gray-800">
                Quantity Summary
            </h2>

            <div class="space-y-4">

                <div class="rounded-lg border border-gray-200 px-4 py-4">
                    <p class="text-sm text-gray-500">
                        Total Consumed
                    </p>

                    <p class="mt-1 text-2xl font-bold text-blue-700">
                        {{ formatQuantity($materialConsumed->total_quantity_consumed) }}
                    </p>
                </div>

                <div class="rounded-lg border border-gray-200 px-4 py-4">
                    <p class="text-sm text-gray-500">
                        Total Wastage
                    </p>

                    <p class="mt-1 text-2xl font-bold text-red-700">
                        {{ formatQuantity($materialConsumed->total_wastage_quantity) }}
                    </p>
                </div>

                <div class="rounded-lg border border-gray-200 px-4 py-4">
                    <p class="text-sm text-gray-500">
                        Total Stock Issue
                    </p>

                    <p class="mt-1 text-2xl font-bold text-purple-700">
                        {{ formatQuantity($materialConsumed->total_issued_quantity) }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Consumed quantity plus wastage quantity.
                    </p>
                </div>

                <div class="rounded-lg border border-gray-200 px-4 py-4">
                    <div class="flex items-center justify-between gap-4">

                        <span class="text-sm font-semibold text-gray-700">
                            Status
                        </span>

                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                            {{ $materialConsumed->status }}
                        </span>

                    </div>
                </div>

            </div>

        </div>

    </div>

    {{-- Material Items --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="flex flex-col gap-3 border-b border-gray-200 p-5 md:flex-row md:items-center md:justify-between">

            <div>
                <h2 class="text-xl font-bold text-gray-800">
                    Material Items
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $hasNewItems
                        ? $materialConsumed->items->count() . ' item(s) recorded under this consumption entry.'
                        : 'Legacy single-material consumption entry.' }}
                </p>
            </div>

        </div>

        <div class="overflow-x-auto">

            <table class="min-w-[1750px] w-full text-sm">

                <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-center">#</th>
                        <th class="px-4 py-3 text-left">Activity Division</th>
                        <th class="px-4 py-3 text-left">Activity</th>
                        <th class="px-4 py-3 text-left">Material Type</th>
                        <th class="px-4 py-3 text-left">Brand</th>
                        <th class="px-4 py-3 text-left">Specification</th>
                        <th class="px-4 py-3 text-left">Grade / Rating</th>
                        <th class="px-4 py-3 text-right">Consumed</th>
                        <th class="px-4 py-3 text-right">Wastage</th>
                        <th class="px-4 py-3 text-right">Total Issue</th>
                        <th class="px-4 py-3 text-left">Unit</th>
                        <th class="px-4 py-3 text-left">Wastage Reason</th>
                        <th class="px-4 py-3 text-left">Remarks</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                    @if($hasNewItems)

                        @foreach($materialConsumed->items as $index => $item)

                            <tr class="align-top hover:bg-gray-50">

                                <td class="px-4 py-3 text-center">
                                    {{ $index + 1 }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $item->activityDivision?->name ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $item->activity?->activity_name ?? '-' }}
                                </td>

                                <td class="px-4 py-3 font-semibold text-gray-800">
                                    {{ $item->materialType?->material_type_name ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $item->brand?->brand_name ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $item->specification?->specification_name ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $item->grade?->grade_name ?? '-' }}
                                </td>

                                <td class="px-4 py-3 text-right font-semibold">
                                    {{ formatQuantity($item->quantity_consumed) }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ formatQuantity($item->wastage_quantity) }}
                                </td>

                                <td class="px-4 py-3 text-right font-semibold text-purple-700">
                                    {{ formatQuantity($item->total_issued_quantity) }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $item->unit?->unit_name ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $item->wastage_reason ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $item->remarks ?? '-' }}
                                </td>

                            </tr>

                        @endforeach

                    @else

                        <tr class="align-top">

                            <td class="px-4 py-3 text-center">1</td>

                            <td class="px-4 py-3">
                                {{ $materialConsumed->activityDivision?->name ?? '-' }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $materialConsumed->activity?->activity_name ?? '-' }}
                            </td>

                            <td class="px-4 py-3 font-semibold text-gray-800">
                                {{ $materialConsumed->material?->material_name ?? '-' }}
                            </td>

                            <td class="px-4 py-3">-</td>
                            <td class="px-4 py-3">-</td>
                            <td class="px-4 py-3">-</td>

                            <td class="px-4 py-3 text-right font-semibold">
                                {{ formatQuantity($materialConsumed->quantity_consumed) }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ formatQuantity($materialConsumed->wastage_quantity) }}
                            </td>

                            <td class="px-4 py-3 text-right font-semibold text-purple-700">
                                {{ formatQuantity(
                                    (float) ($materialConsumed->quantity_consumed ?? 0)
                                    + (float) ($materialConsumed->wastage_quantity ?? 0)
                                ) }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $materialConsumed->unit ?? '-' }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $materialConsumed->wastage_reason ?? '-' }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $materialConsumed->remarks ?? '-' }}
                            </td>

                        </tr>

                    @endif

                </tbody>

            </table>

        </div>

    </div>

    {{-- Record Information --}}
    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

        <h2 class="mb-4 text-lg font-bold text-gray-800">
            Record Information
        </h2>

        <dl class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">

            <div>
                <dt class="text-gray-500">Created At</dt>

                <dd class="mt-1 font-medium text-gray-800">
                    {{ $materialConsumed->created_at?->format('d/m/Y h:i A') ?? '-' }}
                </dd>
            </div>

            <div>
                <dt class="text-gray-500">Last Updated</dt>

                <dd class="mt-1 font-medium text-gray-800">
                    {{ $materialConsumed->updated_at?->format('d/m/Y h:i A') ?? '-' }}
                </dd>
            </div>

            <div>
                <dt class="text-gray-500">Consumption ID</dt>

                <dd class="mt-1 font-medium text-gray-800">
                    #{{ $materialConsumed->id }}
                </dd>
            </div>

        </dl>

    </div>

</div>

<style>
    @media print {
        nav,
        aside,
        header,
        .print\:hidden {
            display: none !important;
        }

        body {
            background: #ffffff !important;
        }

        .shadow-sm {
            box-shadow: none !important;
        }
    }
</style>

@endsection
