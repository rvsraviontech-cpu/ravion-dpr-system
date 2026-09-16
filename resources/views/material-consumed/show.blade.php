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

    $locationParts = collect([
        $materialConsumed->block?->name,
        $materialConsumed->floor?->name,
        $materialConsumed->unit?->name,
        $materialConsumed->room?->name,
        $materialConsumed->subspace?->name,
    ])->filter()->values();

    $hasLocation = $locationParts->isNotEmpty();
@endphp

<div class="mx-auto max-w-full">

    {{-- Page Header --}}
    <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="text-2xl font-bold text-gray-900 sm:text-[28px]">
                    Material Consumption #{{ $materialConsumed->id }}
                </h1>

                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">
                    {{ $materialConsumed->status }}
                </span>
            </div>

            <p class="mt-1 text-sm text-gray-500">
                {{ $materialConsumed->project?->project_name ?? '-' }}
                <span class="mx-1 text-gray-300">•</span>
                {{ $materialConsumed->consumed_date?->format('d/m/Y') ?? '-' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 print:hidden">
            @if($materialConsumed->status === 'Draft' && $canEdit)
                <a href="{{ route('material-consumed.edit', $materialConsumed) }}"
                   class="inline-flex items-center justify-center rounded-md bg-amber-500 px-3.5 py-2 text-sm font-semibold text-white hover:bg-amber-600">
                    Edit
                </a>
            @endif

            @if($materialConsumed->status === 'Draft')
                <form method="POST" action="{{ route('material-consumed.submit', $materialConsumed) }}">
                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            onclick="return confirm('Submit this material consumption entry for approval?')"
                            class="inline-flex items-center justify-center rounded-md bg-blue-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        Submit
                    </button>
                </form>
            @endif

            @if($materialConsumed->status === 'Submitted' && $canApprove)
                <form method="POST" action="{{ route('material-consumed.approve', $materialConsumed) }}">
                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            onclick="return confirm('Approve this material consumption entry?')"
                            class="inline-flex items-center justify-center rounded-md bg-green-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-green-700">
                        Approve
                    </button>
                </form>
            @endif

            <button type="button"
                    onclick="window.print()"
                    class="inline-flex items-center justify-center rounded-md bg-slate-700 px-3.5 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                Print
            </button>

            <a href="{{ route('material-consumed.index') }}"
               class="inline-flex items-center justify-center rounded-md bg-gray-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Consumption Information + Compact Quantity Summary --}}
    <div class="mb-5 grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_310px]">

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-3.5">
                <h2 class="text-base font-bold text-gray-900">Consumption Information</h2>
                <p class="mt-0.5 text-xs text-gray-500">Transaction and site reference details.</p>
            </div>

            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 p-5 md:grid-cols-3 xl:grid-cols-4">
                <div class="col-span-2 md:col-span-1">
                    <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Project</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900">
                        {{ $materialConsumed->project?->project_name ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Consumed Date</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800">
                        {{ $materialConsumed->consumed_date?->format('d/m/Y') ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Consumed Time</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800">
                        {{ $materialConsumed->consumed_time ?? '-' }}
                    </dd>
                </div>

                @if($hasLocation)
                    <div class="col-span-2">
                        <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Site Location</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-800">
                            {{ $locationParts->implode(' / ') }}
                        </dd>
                    </div>
                @endif

                @if($materialConsumed->contractor)
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Contractor</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-800">
                            {{ $materialConsumed->contractor?->contractor_name }}
                        </dd>
                    </div>
                @endif

                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Related Work Output</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800">
                        {{ formatQuantity($materialConsumed->related_work_output_quantity) }}
                    </dd>
                </div>

                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Created By</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800">
                        {{ $materialConsumed->engineer?->name ?? '-' }}
                    </dd>
                </div>

                @if($materialConsumed->dpr_id)
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">DPR Reference</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-800">#{{ $materialConsumed->dpr_id }}</dd>
                    </div>
                @endif

                @if($materialConsumed->remarks)
                    <div class="col-span-2 md:col-span-3 xl:col-span-4 border-t border-gray-100 pt-3">
                        <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Remarks</dt>
                        <dd class="mt-1 whitespace-pre-line text-sm text-gray-700">{{ $materialConsumed->remarks }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-3.5">
                <h2 class="text-base font-bold text-gray-900">Quantity Summary</h2>
                <p class="mt-0.5 text-xs text-gray-500">Consumption and stock issue totals.</p>
            </div>

            <div class="divide-y divide-gray-100">
                <div class="flex items-center justify-between px-5 py-3.5">
                    <span class="text-sm font-medium text-gray-600">Consumed</span>
                    <span class="text-lg font-bold text-blue-700">
                        {{ formatQuantity($materialConsumed->total_quantity_consumed) }}
                    </span>
                </div>

                <div class="flex items-center justify-between px-5 py-3.5">
                    <span class="text-sm font-medium text-gray-600">Wastage</span>
                    <span class="text-lg font-bold {{ (float) $materialConsumed->total_wastage_quantity > 0 ? 'text-red-700' : 'text-gray-700' }}">
                        {{ formatQuantity($materialConsumed->total_wastage_quantity) }}
                    </span>
                </div>

                <div class="flex items-center justify-between px-5 py-3.5">
                    <div>
                        <div class="text-sm font-medium text-gray-600">Total Stock Issue</div>
                        <div class="text-[10px] text-gray-400">Consumed + wastage</div>
                    </div>
                    <span class="text-lg font-bold text-purple-700">
                        {{ formatQuantity($materialConsumed->total_issued_quantity) }}
                    </span>
                </div>

                <div class="flex items-center justify-between px-5 py-3.5">
                    <span class="text-sm font-medium text-gray-600">Status</span>
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">
                        {{ $materialConsumed->status }}
                    </span>
                </div>
            </div>
        </section>
    </div>

    {{-- Material Items --}}
    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-3.5">
            <div>
                <h2 class="text-base font-bold text-gray-900">Material Items</h2>
                <p class="mt-0.5 text-xs text-gray-500">
                    {{ $hasNewItems
                        ? $materialConsumed->items->count() . ' material item(s) recorded.'
                        : 'Legacy single-material consumption entry.' }}
                </p>
            </div>
        </div>

        {{-- Mobile --}}
        <div class="space-y-3 p-3 lg:hidden">
            @if($hasNewItems)
                @foreach($materialConsumed->items as $index => $item)
                    @php
                        $mobileSpecGrade = collect([
                            $item->specification?->specification_name,
                            $item->grade?->grade_name,
                        ])->filter()->implode(' / ');
                    @endphp

                    <article class="rounded-lg border border-gray-200 bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-[10px] font-bold uppercase tracking-wide text-gray-400">
                                    Material {{ $index + 1 }}
                                </div>
                                <div class="mt-1 font-bold text-gray-900">
                                    {{ $item->materialType?->material_type_name ?? '-' }}
                                </div>
                                @if($mobileSpecGrade)
                                    <div class="mt-0.5 text-xs text-gray-500">{{ $mobileSpecGrade }}</div>
                                @endif
                                @if($item->brand?->brand_name)
                                    <div class="mt-0.5 text-xs text-gray-500">{{ $item->brand->brand_name }}</div>
                                @endif
                            </div>

                            <div class="shrink-0 text-right">
                                <div class="text-lg font-bold text-blue-700">
                                    {{ formatQuantity($item->quantity_consumed) }}
                                </div>
                                <div class="text-xs font-medium text-gray-500">
                                    {{ $item->unit?->unit_name ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 border-t border-gray-100 pt-3 text-sm">
                            <div>
                                <span class="text-xs text-gray-500">Activity</span>
                                <div class="font-medium text-gray-800">{{ $item->activity?->activity_name ?? '-' }}</div>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Wastage</span>
                                <div class="font-semibold {{ (float) $item->wastage_quantity > 0 ? 'text-red-700' : 'text-gray-800' }}">
                                    {{ formatQuantity($item->wastage_quantity) }}
                                </div>
                            </div>
                        </div>

                        @if($item->wastage_reason || $item->remarks)
                            <div class="mt-3 border-t border-gray-100 pt-3 text-sm text-gray-700">
                                @if($item->wastage_reason)
                                    <div><span class="font-semibold">Wastage reason:</span> {{ $item->wastage_reason }}</div>
                                @endif
                                @if($item->remarks)
                                    <div class="{{ $item->wastage_reason ? 'mt-1' : '' }}">
                                        <span class="font-semibold">Remarks:</span> {{ $item->remarks }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    </article>
                @endforeach
            @else
                <article class="rounded-lg border border-gray-200 bg-white p-4">
                    <div class="font-bold text-gray-900">
                        {{ $materialConsumed->material?->material_name ?? '-' }}
                    </div>
                    <div class="mt-1 text-lg font-bold text-blue-700">
                        {{ formatQuantity($materialConsumed->quantity_consumed) }}
                        <span class="text-sm font-medium text-gray-500">{{ $materialConsumed->unit ?? '' }}</span>
                    </div>
                </article>
            @endif
        </div>

        {{-- Desktop --}}
        <div class="hidden max-h-[430px] overflow-auto lg:block">
            <table class="w-full min-w-[1280px] table-fixed text-[13px] text-gray-700">
                <colgroup>
                    <col class="w-[48px]">
                    <col class="w-[190px]">
                    <col class="w-[135px]">
                    <col class="w-[170px]">
                    <col class="w-[100px]">
                    <col class="w-[95px]">
                    <col class="w-[90px]">
                    <col class="w-[170px]">
                    <col class="w-[170px]">
                </colgroup>

                <thead class="sticky top-0 z-20 bg-slate-100 text-[11px] font-bold uppercase tracking-wide text-slate-600 shadow-[0_1px_0_rgba(0,0,0,0.08)]">
                    <tr>
                        <th class="px-3 py-3 text-center">#</th>
                        <th class="px-3 py-3 text-left">Material</th>
                        <th class="px-3 py-3 text-left">Brand</th>
                        <th class="px-3 py-3 text-left">Specification / Grade</th>
                        <th class="px-3 py-3 text-right">Consumed</th>
                        <th class="px-3 py-3 text-right">Wastage</th>
                        <th class="px-3 py-3 text-left">Unit</th>
                        <th class="px-3 py-3 text-left">Activity</th>
                        <th class="px-3 py-3 text-left">Wastage Reason / Remarks</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">
                    @if($hasNewItems)
                        @foreach($materialConsumed->items as $index => $item)
                            @php
                                $specGrade = collect([
                                    $item->specification?->specification_name,
                                    $item->grade?->grade_name,
                                ])->filter()->implode(' / ');

                                $notes = collect([
                                    $item->wastage_reason
                                        ? 'Wastage: ' . $item->wastage_reason
                                        : null,
                                    $item->remarks,
                                ])->filter()->implode(' • ');
                            @endphp

                            <tr class="align-top hover:bg-slate-50">
                                <td class="px-3 py-3 text-center font-semibold text-gray-500">
                                    {{ $index + 1 }}
                                </td>

                                <td class="px-3 py-3">
                                    <div class="font-semibold text-gray-900">
                                        {{ $item->materialType?->material_type_name ?? '-' }}
                                    </div>
                                </td>

                                <td class="px-3 py-3">
                                    {{ $item->brand?->brand_name ?? '-' }}
                                </td>

                                <td class="px-3 py-3">
                                    {{ $specGrade ?: '-' }}
                                </td>

                                <td class="px-3 py-3 text-right font-semibold text-blue-700">
                                    {{ formatQuantity($item->quantity_consumed) }}
                                </td>

                                <td class="px-3 py-3 text-right font-semibold {{ (float) $item->wastage_quantity > 0 ? 'text-red-700' : 'text-gray-600' }}">
                                    {{ formatQuantity($item->wastage_quantity) }}
                                </td>

                                <td class="whitespace-nowrap px-3 py-3">
                                    {{ $item->unit?->unit_name ?? '-' }}
                                </td>

                                <td class="px-3 py-3">
                                    <div class="font-medium text-gray-800">
                                        {{ $item->activity?->activity_name ?? '-' }}
                                    </div>
                                    @if($item->activityDivision?->name)
                                        <div class="mt-0.5 text-[10px] uppercase tracking-wide text-gray-400">
                                            {{ $item->activityDivision->name }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-3 py-3">
                                    <div class="line-clamp-2 text-gray-600" title="{{ $notes ?: '-' }}">
                                        {{ $notes ?: '-' }}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr class="align-top">
                            <td class="px-3 py-3 text-center font-semibold text-gray-500">1</td>
                            <td class="px-3 py-3 font-semibold text-gray-900">
                                {{ $materialConsumed->material?->material_name ?? '-' }}
                            </td>
                            <td class="px-3 py-3">-</td>
                            <td class="px-3 py-3">-</td>
                            <td class="px-3 py-3 text-right font-semibold text-blue-700">
                                {{ formatQuantity($materialConsumed->quantity_consumed) }}
                            </td>
                            <td class="px-3 py-3 text-right">
                                {{ formatQuantity($materialConsumed->wastage_quantity) }}
                            </td>
                            <td class="px-3 py-3">{{ $materialConsumed->unit ?? '-' }}</td>
                            <td class="px-3 py-3">
                                {{ $materialConsumed->activity?->activity_name ?? '-' }}
                            </td>
                            <td class="px-3 py-3">
                                {{ collect([$materialConsumed->wastage_reason, $materialConsumed->remarks])->filter()->implode(' • ') ?: '-' }}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </section>

    {{-- Record Information --}}
    <section class="mt-4 rounded-xl border border-gray-200 bg-white px-5 py-3.5 shadow-sm">
        <div class="grid grid-cols-1 gap-3 text-xs text-gray-500 sm:grid-cols-3">
            <div>
                Created
                <span class="ml-1 font-semibold text-gray-700">
                    {{ $materialConsumed->created_at?->format('d/m/Y h:i A') ?? '-' }}
                </span>
            </div>

            <div>
                Last Updated
                <span class="ml-1 font-semibold text-gray-700">
                    {{ $materialConsumed->updated_at?->format('d/m/Y h:i A') ?? '-' }}
                </span>
            </div>

            <div class="sm:text-right">
                Record ID
                <span class="ml-1 font-semibold text-gray-700">#{{ $materialConsumed->id }}</span>
            </div>
        </div>
    </section>
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
