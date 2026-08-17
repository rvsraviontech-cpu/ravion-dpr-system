@extends('layouts.app')

@section('content')

@php
    $statusClasses = match($materialReceived->status) {
        'Approved' => 'bg-green-100 text-green-800',
        'Submitted' => 'bg-blue-100 text-blue-800',
        'Rejected' => 'bg-red-100 text-red-800',
        default => 'bg-yellow-100 text-yellow-800',
    };

    $verificationClasses = function (?string $status): string {
        return match($status) {
            'Approved', 'Verified','Bill Verified' => 'bg-green-100 text-green-800',
            'Rejected' => 'bg-red-100 text-red-800',
            'Submitted', 'Pending' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-700',
        };
    };

    $hasNewItems = $materialReceived->items->isNotEmpty();

    $canVerifyAccounts = auth()->check()
        && (
            auth()->user()->role?->name === 'Admin'
            || auth()->user()->hasPermission('material_received.accountant_verify')
        );
@endphp

<div class="mx-auto max-w-full">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">
                    Material Receipt #{{ $materialReceived->id }}
                </h1>

                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                    {{ $materialReceived->status }}
                </span>
            </div>

            <p class="mt-1 text-gray-500">
                Review delivery information, material items and verification status.
            </p>
        </div>

        <div class="grid grid-cols-2 gap-2 print:hidden sm:flex sm:flex-wrap">

            @if($materialReceived->status === 'Draft')
                <a href="{{ route('material-received.edit', $materialReceived) }}"
                   class="inline-flex items-center justify-center rounded-lg bg-yellow-500 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-yellow-600 sm:py-2.5">
                    Edit
                </a>

                <form method="POST"
                      action="{{ route('material-received.submit', $materialReceived) }}">
                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            onclick="return confirm('Submit this material receipt for approval?')"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-blue-700 sm:py-2.5">
                        Submit
                    </button>
                </form>
            @endif

            @if(
    $materialReceived->status === 'Submitted'
    && auth()->check()
    && (
        in_array(
            auth()->user()->role?->name,
            ['Admin', 'PMO', 'DGM'],
            true
        )
        || auth()->user()
            ->role
            ?->permissions()
            ->where('name', 'material_received.approve')
            ->where('is_active', true)
            ->exists()
    )
)
    <form method="POST"
          action="{{ route('material-received.approve', $materialReceived) }}">

        @csrf
        @method('PATCH')

        <button type="submit"
                onclick="return confirm('Approve this material receipt?')"
                class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-green-700 sm:py-2.5">
            Approve
        </button>

    </form>
@endif

            @if(
                $materialReceived->status === 'Approved'
                && $materialReceived->accountant_verification_status !== 'Bill Verified'
                && $canVerifyAccounts
            )
                <form method="POST"
                      action="{{ route('material-received.accountant-verify', $materialReceived) }}">
                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            onclick="return confirm('Confirm that Accounts has verified this supplier bill?')"
                            class="inline-flex items-center justify-center rounded-lg bg-purple-600 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-purple-700 sm:py-2.5">
                        Verify Bill
                    </button>
                </form>
            @endif

            <button type="button"
                    onclick="window.print()"
                    class="inline-flex items-center justify-center rounded-lg bg-slate-700 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-slate-800 sm:py-2.5">
                Print
            </button>

            <a href="{{ route('material-received.index') }}"
               class="inline-flex items-center justify-center rounded-lg bg-gray-600 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-gray-700 sm:py-2.5">
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

    {{-- Receipt Information --}}
    <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-3">

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6 xl:col-span-2">

            <h2 class="mb-5 text-xl font-bold text-gray-800">
                Receipt Information
            </h2>

            <dl class="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2 xl:grid-cols-3">

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Project
                    </dt>
                    <dd class="mt-1 font-semibold text-gray-800">
                        {{ $materialReceived->project?->project_name ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Received Date
                    </dt>
                    <dd class="mt-1 text-gray-800">
                        {{ $materialReceived->received_date?->format('d/m/Y') ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Received Time
                    </dt>
                    <dd class="mt-1 text-gray-800">
                        {{ $materialReceived->received_time ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Block
                    </dt>
                    <dd class="mt-1 text-gray-800">
                        {{ $materialReceived->block?->name ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Floor
                    </dt>
                    <dd class="mt-1 text-gray-800">
                        {{ $materialReceived->floor?->name ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Unit
                    </dt>
                    <dd class="mt-1 text-gray-800">
                        {{ $materialReceived->unit?->name ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Storage Location
                    </dt>
                    <dd class="mt-1 text-gray-800">
                        {{ $materialReceived->storage_location ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Vendor
                    </dt>
                    <dd class="mt-1 text-gray-800">
                        {{ $materialReceived->vendor?->vendor_name
                            ?? $materialReceived->vendor_name
                            ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Contractor Supply
                    </dt>
                    <dd class="mt-1 text-gray-800">
                        @if($materialReceived->supplied_by_contractor)
                            Yes
                            @if($materialReceived->contractor)
                                — {{ $materialReceived->contractor->contractor_name }}
                            @endif
                        @else
                            No
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Vehicle Number
                    </dt>
                    <dd class="mt-1 text-gray-800">
                        {{ $materialReceived->vehicle_number ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Driver Name
                    </dt>
                    <dd class="mt-1 text-gray-800">
                        {{ $materialReceived->driver_name ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Challan Number
                    </dt>
                    <dd class="mt-1 text-gray-800">
                        {{ $materialReceived->challan_number ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Bill Number
                    </dt>
                    <dd class="mt-1 text-gray-800">
                        {{ $materialReceived->bill_number ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Material Condition
                    </dt>
                    <dd class="mt-1 text-gray-800">
                        {{ $materialReceived->material_condition ?? 'Pending Verification' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Created By
                    </dt>
                    <dd class="mt-1 text-gray-800">
                        {{ $materialReceived->engineer?->name ?? '-' }}
                    </dd>
                </div>

                <div class="md:col-span-2 xl:col-span-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Remarks
                    </dt>
                    <dd class="mt-1 whitespace-pre-line text-gray-800">
                        {{ $materialReceived->remarks ?? '-' }}
                    </dd>
                </div>

            </dl>
        </div>

        {{-- Verification Status --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

            <h2 class="mb-5 text-xl font-bold text-gray-800">
                Verification Status
            </h2>

            <div class="space-y-4">

                <div class="rounded-lg border border-gray-200 px-4 py-3">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm font-semibold text-gray-700">
                            Site Engineer
                        </span>

                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $verificationClasses($materialReceived->site_engineer_verification_status) }}">
                            {{ $materialReceived->site_engineer_verification_status ?? 'Pending' }}
                        </span>
                    </div>

                    @if($materialReceived->site_engineer_verification_status === 'Verified')
                        <div class="mt-2 text-right text-xs text-gray-500">
                            {{ $materialReceived->engineer?->name ?? '-' }}

                            @if($materialReceived->submitted_at)
                                <br>
                                {{ $materialReceived->submitted_at->format('d/m/Y h:i A') }}
                            @endif
                        </div>
                    @endif
                </div>

                <div class="rounded-lg border border-gray-200 px-4 py-3">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm font-semibold text-gray-700">
                            PMO
                        </span>

                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $verificationClasses($materialReceived->pmo_verification_status) }}">
                            {{ $materialReceived->pmo_verification_status ?? 'Pending' }}
                        </span>
                    </div>

                    @if($materialReceived->pmo_verification_status === 'Approved')
                        <div class="mt-2 text-right text-xs text-gray-500">
                            {{ $materialReceived->approver?->name ?? '-' }}

                            @if($materialReceived->approved_at)
                                <br>
                                {{ $materialReceived->approved_at->format('d/m/Y h:i A') }}
                            @endif
                        </div>
                    @endif
                </div>

                <div class="rounded-lg border border-gray-200 px-4 py-3">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm font-semibold text-gray-700">
                            Accountant
                        </span>

                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $verificationClasses($materialReceived->accountant_verification_status) }}">
                            {{ $materialReceived->accountant_verification_status ?? 'Pending' }}
                        </span>
                    </div>

                    @if($materialReceived->accountant_verification_status === 'Bill Verified')
                        <div class="mt-2 text-right text-xs text-gray-500">
                            {{ $materialReceived->accountantVerifier?->name ?? '-' }}

                            @if($materialReceived->accountant_verified_at)
                                <br>
                                {{ $materialReceived->accountant_verified_at->format('d/m/Y h:i A') }}
                            @endif
                        </div>
                    @endif
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <dl class="space-y-3 text-sm">

                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Submitted At</dt>
                            <dd class="text-right text-gray-800">
                                {{ $materialReceived->submitted_at?->format('d/m/Y h:i A') ?? '-' }}
                            </dd>
                        </div>

                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">PMO Approved At</dt>
                            <dd class="text-right text-gray-800">
                                {{ $materialReceived->approved_at?->format('d/m/Y h:i A') ?? '-' }}
                            </dd>
                        </div>

                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Accounts Verified At</dt>
                            <dd class="text-right text-gray-800">
                                {{ $materialReceived->accountant_verified_at?->format('d/m/Y h:i A') ?? '-' }}
                            </dd>
                        </div>

                    </dl>
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
                        ? $materialReceived->items->count() . ' item(s) recorded under this delivery.'
                        : 'Legacy single-material receipt.' }}
                </p>
            </div>

            <div class="text-sm font-semibold text-gray-700">
                Total Quantity:
                <span class="text-blue-700">
                    {{ formatQuantity($materialReceived->total_quantity_received) }}
                </span>
            </div>
        </div>

        {{-- Mobile Material Items --}}
        <div class="space-y-3 p-3 lg:hidden">
            @if($hasNewItems)
                @foreach($materialReceived->items as $index => $item)
                    <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">
                                    Material {{ $index + 1 }}
                                </div>
                                <div class="mt-1 text-base font-bold text-gray-800">
                                    {{ $item->materialType?->material_type_name ?? '-' }}
                                </div>
                                @if($item->activity)
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $item->activity->activity_name }}
                                    </div>
                                @endif
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-[#0F2A52]">
                                    {{ formatQuantity($item->quantity_received) }}
                                </div>
                                <div class="text-xs font-semibold text-gray-500">
                                    {{ $item->unit?->unit_name ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg bg-gray-50 px-3 py-2">
                                <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Brand</div>
                                <div class="mt-1 font-semibold text-gray-800">{{ $item->brand?->brand_name ?? '-' }}</div>
                            </div>
                            <div class="rounded-lg bg-gray-50 px-3 py-2">
                                <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Specification</div>
                                <div class="mt-1 font-semibold text-gray-800">{{ $item->specification?->specification_name ?? '-' }}</div>
                            </div>
                            <div class="rounded-lg bg-gray-50 px-3 py-2">
                                <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Grade</div>
                                <div class="mt-1 font-semibold text-gray-800">{{ $item->grade?->grade_name ?? '-' }}</div>
                            </div>
                            <div class="rounded-lg bg-gray-50 px-3 py-2">
                                <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Condition</div>
                                <div class="mt-1 font-semibold text-gray-800">{{ $item->material_condition ?? 'Pending Verification' }}</div>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-4 gap-2 text-center">
                            <div class="rounded-lg bg-green-50 px-2 py-2">
                                <div class="text-[10px] font-bold uppercase tracking-wide text-green-700">Accepted</div>
                                <div class="mt-1 text-sm font-bold text-green-800">{{ formatQuantity($item->accepted_quantity) }}</div>
                            </div>
                            <div class="rounded-lg bg-yellow-50 px-2 py-2">
                                <div class="text-[10px] font-bold uppercase tracking-wide text-yellow-700">Short</div>
                                <div class="mt-1 text-sm font-bold text-yellow-800">{{ formatQuantity($item->short_quantity) }}</div>
                            </div>
                            <div class="rounded-lg bg-orange-50 px-2 py-2">
                                <div class="text-[10px] font-bold uppercase tracking-wide text-orange-700">Damaged</div>
                                <div class="mt-1 text-sm font-bold text-orange-800">{{ formatQuantity($item->damaged_quantity) }}</div>
                            </div>
                            <div class="rounded-lg bg-red-50 px-2 py-2">
                                <div class="text-[10px] font-bold uppercase tracking-wide text-red-700">Rejected</div>
                                <div class="mt-1 text-sm font-bold text-red-800">{{ formatQuantity($item->rejected_quantity) }}</div>
                            </div>
                        </div>

                        @if($item->remarks)
                            <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                {{ $item->remarks }}
                            </div>
                        @endif
                    </article>
                @endforeach
            @else
                <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="text-base font-bold text-gray-800">
                        {{ $materialReceived->material_name
                            ?? $materialReceived->material?->material_name
                            ?? '-' }}
                    </div>
                    <div class="mt-2 text-lg font-bold text-[#0F2A52]">
                        {{ formatQuantity($materialReceived->quantity_received) }}
                        {{ $materialReceived->unit ?? '' }}
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg bg-gray-50 px-3 py-2">
                            <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Brand</div>
                            <div class="mt-1 font-semibold">{{ $materialReceived->brand ?? '-' }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 px-3 py-2">
                            <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Specification</div>
                            <div class="mt-1 font-semibold">{{ $materialReceived->specification ?? '-' }}</div>
                        </div>
                    </div>
                </article>
            @endif
        </div>

        <div class="hidden overflow-x-auto lg:block">

            <table class="min-w-[1500px] w-full text-sm">

                <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-center">#</th>
                        <th class="px-4 py-3 text-left">Activity Division</th>
                        <th class="px-4 py-3 text-left">Activity</th>
                        <th class="px-4 py-3 text-left">Material Type</th>
                        <th class="px-4 py-3 text-left">Brand</th>
                        <th class="px-4 py-3 text-left">Specification</th>
                        <th class="px-4 py-3 text-left">Grade / Rating</th>
                        <th class="px-4 py-3 text-right">Quantity</th>
                        <th class="px-4 py-3 text-left">Unit</th>
                        <th class="px-4 py-3 text-left">Condition</th>
                        <th class="px-4 py-3 text-right">Accepted</th>
                        <th class="px-4 py-3 text-right">Short</th>
                        <th class="px-4 py-3 text-right">Damaged</th>
                        <th class="px-4 py-3 text-right">Rejected</th>
                        <th class="px-4 py-3 text-left">Remarks</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                    @if($hasNewItems)

                        @foreach($materialReceived->items as $index => $item)

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
                                    {{ formatQuantity($item->quantity_received) }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $item->unit?->unit_name ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $item->material_condition ?? 'Pending Verification' }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ formatQuantity($item->accepted_quantity) }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ formatQuantity($item->short_quantity) }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ formatQuantity($item->damaged_quantity) }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ formatQuantity($item->rejected_quantity) }}
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
                                {{ $materialReceived->material_name
                                    ?? $materialReceived->material?->material_name
                                    ?? '-' }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $materialReceived->brand ?? '-' }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $materialReceived->specification ?? '-' }}
                            </td>

                            <td class="px-4 py-3">-</td>

                            <td class="px-4 py-3 text-right font-semibold">
                                {{ formatQuantity($materialReceived->quantity_received) }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $materialReceived->unit ?? '-' }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $materialReceived->material_condition ?? '-' }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ formatQuantity($materialReceived->accepted_quantity) }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ formatQuantity($materialReceived->short_quantity) }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ formatQuantity($materialReceived->damaged_quantity) }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ formatQuantity($materialReceived->rejected_quantity) }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $materialReceived->remarks ?? '-' }}
                            </td>

                        </tr>

                    @endif

                </tbody>

            </table>
        </div>
    </div>



    {{-- Material Receipt Photos --}}
    <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="flex flex-col gap-3 border-b border-gray-200 p-5 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800">
                    Material Receipt Photos
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Supporting site photos, challans, invoices, vehicle, unloading and material-condition images.
                </p>
            </div>

            <div class="text-sm font-semibold text-gray-700">
                Photos:
                <span class="text-blue-700">
                    {{ $materialReceived->photos->count() }}
                </span>
            </div>
        </div>

        @if($materialReceived->photos->isNotEmpty())

            <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">

                @foreach($materialReceived->photos as $photo)

                    @php
                        $relatedItem = $photo->materialReceivedItem;

                        $relatedMaterial = $relatedItem?->materialType?->material_type_name;

                        $relatedVariant = collect([
                            $relatedItem?->brand?->brand_name,
                            $relatedItem?->specification?->specification_name,
                            $relatedItem?->grade?->grade_name,
                        ])->filter()->implode(' • ');
                    @endphp

                    <article class="overflow-hidden rounded-xl border border-gray-200 bg-white">

                        <button type="button"
                                class="group block w-full bg-gray-100 text-left print:pointer-events-none"
                                onclick="openMaterialPhotoModal(
                                    @js($photo->file_url),
                                    @js($photo->display_caption)
                                )">

                            <img src="{{ $photo->file_url }}"
                                 alt="{{ $photo->display_caption }}"
                                 loading="lazy"
                                 class="h-56 w-full object-cover transition duration-200 group-hover:scale-[1.02]">
                        </button>

                        <div class="p-4">

                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                                    {{ $photo->photo_type }}
                                </span>

                                @if($photo->is_item_level)
                                    <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                        Item Specific
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                        Whole Receipt
                                    </span>
                                @endif
                            </div>

                            @if($photo->caption)
                                <p class="mb-3 font-semibold text-gray-800">
                                    {{ $photo->caption }}
                                </p>
                            @endif

                            <dl class="space-y-2 text-sm">

                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        Related Material
                                    </dt>

                                    <dd class="mt-1 text-gray-800">
                                        @if($relatedMaterial)
                                            <span class="font-semibold">
                                                {{ $relatedMaterial }}
                                            </span>

                                            @if($relatedVariant)
                                                <span class="text-gray-500">
                                                    — {{ $relatedVariant }}
                                                </span>
                                            @endif
                                        @else
                                            General / Whole Receipt
                                        @endif
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        Uploaded By
                                    </dt>

                                    <dd class="mt-1 text-gray-800">
                                        {{ $photo->uploader?->name ?? '-' }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        Uploaded At
                                    </dt>

                                    <dd class="mt-1 text-gray-800">
                                        {{ $photo->created_at?->format('d/m/Y h:i A') ?? '-' }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        Original File
                                    </dt>

                                    <dd class="mt-1 break-all text-gray-800">
                                        {{ $photo->original_name ?? basename($photo->file_path) }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        Size
                                    </dt>

                                    <dd class="mt-1 text-gray-800">
                                        {{ $photo->formatted_file_size }}
                                    </dd>
                                </div>

                            </dl>

                            <div class="mt-4 print:hidden">
                                <a href="{{ $photo->file_url }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex w-full items-center justify-center rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                                    Open Full Image
                                </a>
                            </div>

                        </div>

                    </article>

                @endforeach

            </div>

        @else

            <div class="p-8 text-center text-sm text-gray-500">
                No photos have been uploaded for this material receipt.
            </div>

        @endif

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
                    {{ $materialReceived->created_at?->format('d/m/Y h:i A') ?? '-' }}
                </dd>
            </div>

            <div>
                <dt class="text-gray-500">Last Updated</dt>
                <dd class="mt-1 font-medium text-gray-800">
                    {{ $materialReceived->updated_at?->format('d/m/Y h:i A') ?? '-' }}
                </dd>
            </div>

            <div>
                <dt class="text-gray-500">Receipt ID</dt>
                <dd class="mt-1 font-medium text-gray-800">
                    #{{ $materialReceived->id }}
                </dd>
            </div>

        </dl>
    </div>

</div>



{{-- Full-size photo modal --}}
<div id="material-photo-modal"
     class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/80 p-4 print:hidden"
     aria-hidden="true">

    <div class="relative flex max-h-[95vh] w-full max-w-6xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl">

        <div class="flex items-center justify-between gap-4 border-b border-gray-200 px-5 py-4">
            <div>
                <h3 class="text-lg font-bold text-gray-800">
                    Material Receipt Photo
                </h3>

                <p id="material-photo-modal-caption"
                   class="mt-1 text-sm text-gray-500">
                    -
                </p>
            </div>

            <button type="button"
                    onclick="closeMaterialPhotoModal()"
                    class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                Close
            </button>
        </div>

        <div class="overflow-auto bg-gray-100 p-4">
            <img id="material-photo-modal-image"
                 src=""
                 alt="Material receipt photo"
                 class="mx-auto max-h-[80vh] max-w-full rounded-lg bg-white object-contain">
        </div>

    </div>
</div>

<script>
    function openMaterialPhotoModal(url, caption) {
        const modal = document.getElementById('material-photo-modal');
        const image = document.getElementById('material-photo-modal-image');
        const captionElement = document.getElementById('material-photo-modal-caption');

        image.src = url;
        captionElement.textContent = caption || 'Material Receipt Photo';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');

        document.body.classList.add('overflow-hidden');
    }

    function closeMaterialPhotoModal() {
        const modal = document.getElementById('material-photo-modal');
        const image = document.getElementById('material-photo-modal-image');

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');

        image.src = '';

        document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMaterialPhotoModal();
        }
    });

    document.getElementById('material-photo-modal')
        ?.addEventListener('click', function (event) {
            if (event.target === this) {
                closeMaterialPhotoModal();
            }
        });
</script>


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
