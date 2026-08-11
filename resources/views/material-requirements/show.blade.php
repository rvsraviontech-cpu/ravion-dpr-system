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

    $canEdit = $hasPermission('material_required.edit');

    $canApprove = $hasPermission('material_required.approve')
        || in_array($roleName, ['PMO', 'DGM'], true);

    $statusClasses = match($materialRequirement->status) {
        'Approved' => 'bg-green-100 text-green-800',
        'Submitted' => 'bg-blue-100 text-blue-800',
        'Rejected' => 'bg-red-100 text-red-800',
        default => 'bg-yellow-100 text-yellow-800',
    };

    $priorityClasses = match($materialRequirement->priority) {
        'Urgent' => 'bg-red-100 text-red-800',
        'High' => 'bg-orange-100 text-orange-800',
        'Normal' => 'bg-blue-100 text-blue-800',
        default => 'bg-gray-100 text-gray-800',
    };

    $hasNewItems = $materialRequirement->items->isNotEmpty();
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <div class="flex flex-wrap items-center gap-3">

                <h1 class="text-3xl font-bold text-gray-800">
                    Material Requirement #{{ $materialRequirement->id }}
                </h1>

                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                    {{ $materialRequirement->status }}
                </span>

                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $priorityClasses }}">
                    {{ $materialRequirement->priority }}
                </span>

            </div>

            <p class="mt-1 text-gray-500">
                Review requirement details, material quantities and approval status.
            </p>
        </div>

        <div class="flex flex-wrap gap-2 print:hidden">

            @if(
                $materialRequirement->status === 'Draft'
                && $canEdit
            )
                <a href="{{ route('material-requirements.edit', $materialRequirement) }}"
                   class="inline-flex items-center justify-center rounded-lg bg-yellow-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-yellow-600">
                    Edit
                </a>
            @endif

            @if($materialRequirement->status === 'Draft')
                <form method="POST"
                      action="{{ route('material-requirements.submit', $materialRequirement) }}">

                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            onclick="return confirm('Submit this material requirement for approval?')"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                        Submit
                    </button>
                </form>
            @endif

            @if(
                $materialRequirement->status === 'Submitted'
                && $canApprove
            )
                <form method="POST"
                      action="{{ route('material-requirements.approve', $materialRequirement) }}">

                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            onclick="return confirm('Approve this material requirement?')"
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

            <a href="{{ route('material-requirements.index') }}"
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

    <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-3">

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm xl:col-span-2">

            <h2 class="mb-5 text-xl font-bold text-gray-800">
                Requirement Information
            </h2>

            <dl class="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2 xl:grid-cols-3">

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Project
                    </dt>

                    <dd class="mt-1 font-semibold text-gray-800">
                        {{ $materialRequirement->project?->project_name ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Project Block
                    </dt>

                    <dd class="mt-1 text-gray-800">
                        {{ $materialRequirement->block?->name ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Required Date
                    </dt>

                    <dd class="mt-1 text-gray-800">
                        {{ $materialRequirement->required_date?->format('d/m/Y') ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Priority
                    </dt>

                    <dd class="mt-1">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $priorityClasses }}">
                            {{ $materialRequirement->priority }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Created By
                    </dt>

                    <dd class="mt-1 text-gray-800">
                        {{ $materialRequirement->creator?->name ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Approved By
                    </dt>

                    <dd class="mt-1 text-gray-800">
                        {{ $materialRequirement->approver?->name ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Approved At
                    </dt>

                    <dd class="mt-1 text-gray-800">
                        {{ $materialRequirement->approved_at?->format('d/m/Y h:i A') ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        DPR Reference
                    </dt>

                    <dd class="mt-1 text-gray-800">
                        {{ property_exists($materialRequirement, 'dpr_id') && $materialRequirement->dpr_id
                            ? '#' . $materialRequirement->dpr_id
                            : '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Status
                    </dt>

                    <dd class="mt-1">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                            {{ $materialRequirement->status }}
                        </span>
                    </dd>
                </div>

                <div class="md:col-span-2 xl:col-span-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Remarks
                    </dt>

                    <dd class="mt-1 whitespace-pre-line text-gray-800">
                        {{ $materialRequirement->remarks ?? '-' }}
                    </dd>
                </div>

            </dl>

        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <h2 class="mb-5 text-xl font-bold text-gray-800">
                Quantity Summary
            </h2>

            <div class="space-y-4">

                <div class="rounded-lg border border-gray-200 px-4 py-4">
                    <p class="text-sm text-gray-500">
                        Total Required
                    </p>

                    <p class="mt-1 text-2xl font-bold text-blue-700">
                        {{ formatQuantity($materialRequirement->total_required_quantity) }}
                    </p>
                </div>

                <div class="rounded-lg border border-gray-200 px-4 py-4">
                    <p class="text-sm text-gray-500">
                        Total Fulfilled
                    </p>

                    <p class="mt-1 text-2xl font-bold text-green-700">
                        {{ formatQuantity($materialRequirement->total_fulfilled_quantity) }}
                    </p>
                </div>

                <div class="rounded-lg border border-gray-200 px-4 py-4">
                    <p class="text-sm text-gray-500">
                        Total Pending
                    </p>

                    <p class="mt-1 text-2xl font-bold text-orange-700">
                        {{ formatQuantity($materialRequirement->total_pending_quantity) }}
                    </p>
                </div>

                <div class="rounded-lg border border-gray-200 px-4 py-4">
                    <div class="mb-2 flex items-center justify-between gap-4">

                        <span class="text-sm font-semibold text-gray-700">
                            Fulfilment
                        </span>

                        <span class="text-sm font-bold text-gray-800">
                            {{ formatQuantity($materialRequirement->fulfilment_percentage) }}%
                        </span>

                    </div>

                    <div class="h-2 overflow-hidden rounded-full bg-gray-200">
                        <div class="h-full rounded-full bg-green-600"
                             style="width: {{ min(100, max(0, $materialRequirement->fulfilment_percentage)) }}%">
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 p-5">

            <h2 class="text-xl font-bold text-gray-800">
                Requirement Items
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                {{ $hasNewItems
                    ? $materialRequirement->items->count() . ' item(s) recorded under this requirement.'
                    : 'Legacy single-material requirement.' }}
            </p>

        </div>

        <div class="overflow-x-auto">

            <table class="min-w-[1600px] w-full text-sm">

                <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-center">#</th>
                        <th class="px-4 py-3 text-left">Activity Division</th>
                        <th class="px-4 py-3 text-left">Activity</th>
                        <th class="px-4 py-3 text-left">Material</th>
                        <th class="px-4 py-3 text-left">Brand</th>
                        <th class="px-4 py-3 text-left">Specification</th>
                        <th class="px-4 py-3 text-left">Grade</th>
                        <th class="px-4 py-3 text-right">Required</th>
                        <th class="px-4 py-3 text-right">Fulfilled</th>
                        <th class="px-4 py-3 text-right">Pending</th>
                        <th class="px-4 py-3 text-left">Unit</th>
                        <th class="px-4 py-3 text-left">Remarks</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                    @if($hasNewItems)

                        @foreach($materialRequirement->items as $index => $item)

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

                                <td class="px-4 py-3 text-right font-semibold text-blue-700">
                                    {{ formatQuantity($item->required_quantity) }}
                                </td>

                                <td class="px-4 py-3 text-right font-semibold text-green-700">
                                    {{ formatQuantity($item->fulfilled_quantity) }}
                                </td>

                                <td class="px-4 py-3 text-right font-semibold text-orange-700">
                                    {{ formatQuantity($item->pending_quantity) }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $item->unit?->unit_name ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $item->remarks ?? '-' }}
                                </td>

                            </tr>

                        @endforeach

                    @else

                        <tr class="align-top">

                            <td class="px-4 py-3 text-center">1</td>
                            <td class="px-4 py-3">-</td>
                            <td class="px-4 py-3">-</td>

                            <td class="px-4 py-3 font-semibold text-gray-800">
                                {{ $materialRequirement->material?->material_name ?? '-' }}
                            </td>

                            <td class="px-4 py-3">-</td>
                            <td class="px-4 py-3">-</td>
                            <td class="px-4 py-3">-</td>

                            <td class="px-4 py-3 text-right font-semibold text-blue-700">
                                {{ formatQuantity($materialRequirement->required_quantity) }}
                            </td>

                            <td class="px-4 py-3 text-right font-semibold text-green-700">
                                {{ formatQuantity($materialRequirement->fulfilled_quantity) }}
                            </td>

                            <td class="px-4 py-3 text-right font-semibold text-orange-700">
                                {{ formatQuantity(
                                    max(
                                        0,
                                        (float) ($materialRequirement->required_quantity ?? 0)
                                        - (float) ($materialRequirement->fulfilled_quantity ?? 0)
                                    )
                                ) }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $materialRequirement->unit ?? '-' }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $materialRequirement->remarks ?? '-' }}
                            </td>

                        </tr>

                    @endif

                </tbody>

            </table>

        </div>

    </div>

    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

        <h2 class="mb-4 text-lg font-bold text-gray-800">
            Record Information
        </h2>

        <dl class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">

            <div>
                <dt class="text-gray-500">Created At</dt>

                <dd class="mt-1 font-medium text-gray-800">
                    {{ $materialRequirement->created_at?->format('d/m/Y h:i A') ?? '-' }}
                </dd>
            </div>

            <div>
                <dt class="text-gray-500">Last Updated</dt>

                <dd class="mt-1 font-medium text-gray-800">
                    {{ $materialRequirement->updated_at?->format('d/m/Y h:i A') ?? '-' }}
                </dd>
            </div>

            <div>
                <dt class="text-gray-500">Requirement ID</dt>

                <dd class="mt-1 font-medium text-gray-800">
                    #{{ $materialRequirement->id }}
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
