@extends('layouts.app')

@section('content')
@php
    $movementTypes = \App\Models\MachineryEquipmentAllocation::movementTypes();
    $statuses = \App\Models\MachineryEquipmentAllocation::statuses();

    $statusClasses = match ($allocation->status) {
        'received' => 'bg-green-100 text-green-800',
        'pending' => 'bg-yellow-100 text-yellow-800',
        'in_transit' => 'bg-blue-100 text-blue-800',
        'cancelled' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-700',
    };

    $formatQty = function ($value) {
        return rtrim(
            rtrim(
                number_format((float) $value, 3, '.', ''),
                '0'
            ),
            '.'
        );
    };

    $projectBalances = collect($balances)
        ->except('yard')
        ->filter(fn ($qty) => (float) $qty > 0);

    $projectIds = $projectBalances
        ->keys()
        ->map(fn ($id) => (int) $id)
        ->values();

    $balanceProjects = \App\Models\Project::query()
        ->whereIn('id', $projectIds)
        ->get(['id', 'project_code', 'project_name'])
        ->keyBy('id');
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $allocation->allocation_number }}
                </h1>

                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                    {{ $statuses[$allocation->status]
                        ?? ucwords(str_replace('_', ' ', $allocation->status)) }}
                </span>
            </div>

            <p class="mt-1 text-sm text-gray-600">
                Equipment Allocation / Transfer Movement
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('machinery-equipment-allocations.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Back to Movements
            </a>

            <a href="{{ route('machinery-equipment-allocations.create', [
                    'equipment_id' => $allocation->machinery_equipment_id
                ]) }}"
               class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                + New Movement
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
            <div class="font-semibold text-red-800">
                Please correct the following:
            </div>

            <ul class="mt-2 list-disc pl-5 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Workflow Actions --}}
    @if (in_array($allocation->status, ['pending', 'in_transit'], true))
        <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="font-semibold text-gray-900">
                        Movement Workflow
                    </h2>

                    <p class="mt-1 text-sm text-gray-600">
                        @if ($allocation->status === 'pending')
                            This movement is pending. It can be dispatched, received directly, or cancelled.
                        @else
                            This equipment is currently in transit and is awaiting receipt at the destination.
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if ($allocation->status === 'pending')
                        <form method="POST"
                              action="{{ route(
                                  'machinery-equipment-allocations.dispatch',
                                  $allocation
                              ) }}">
                            @csrf

                            <button type="submit"
                                    onclick="return confirm('Dispatch this equipment movement?')"
                                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                Dispatch
                            </button>
                        </form>
                    @endif

                    <form method="POST"
                          action="{{ route(
                              'machinery-equipment-allocations.receive',
                              $allocation
                          ) }}">
                        @csrf

                        <button type="submit"
                                onclick="return confirm('Confirm receipt of this equipment movement?')"
                                class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                            Receive
                        </button>
                    </form>

                    <button type="button"
                            onclick="document.getElementById('cancelMovementPanel').classList.toggle('hidden')"
                            class="rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">
                        Cancel Movement
                    </button>
                </div>
            </div>

            <div id="cancelMovementPanel"
                 class="mt-5 hidden border-t border-gray-200 pt-5">

                <form method="POST"
                      action="{{ route(
                          'machinery-equipment-allocations.cancel',
                          $allocation
                      ) }}">

                    @csrf

                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Cancellation Reason
                        <span class="text-red-600">*</span>
                    </label>

                    <textarea name="cancellation_reason"
                              rows="3"
                              required
                              maxlength="2000"
                              class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500"
                              placeholder="Enter the reason for cancelling this movement...">{{ old('cancellation_reason') }}</textarea>

                    <div class="mt-3">
                        <button type="submit"
                                onclick="return confirm('Cancel this equipment movement?')"
                                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                            Confirm Cancellation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- Main --}}
        <div class="space-y-6 xl:col-span-2">

            {{-- Equipment --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900">
                    Equipment
                </h2>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Equipment Code
                        </div>

                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $allocation->equipment?->equipment_code }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Equipment
                        </div>

                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $allocation->equipment?->display_name }}
                        </div>

                        <div class="text-xs text-gray-500">
                            {{ $allocation->equipment?->machineryTool?->machine_name }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Tracking
                        </div>

                        <div class="mt-1 text-sm text-gray-900">
                            {{ ucfirst($allocation->equipment?->tracking_mode ?? '-') }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Ownership
                        </div>

                        <div class="mt-1 text-sm text-gray-900">
                            {{ $allocation->equipment?->ownership_label ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Registered Quantity
                        </div>

                        <div class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $formatQty($allocation->equipment?->quantity ?? 0) }}
                            {{ $allocation->equipment?->unit }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Operational Status
                        </div>

                        <div class="mt-1 text-sm text-gray-900">
                            {{ $allocation->equipment?->status_label ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Movement --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900">
                    Movement Details
                </h2>

                <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Movement Type
                        </div>

                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $movementTypes[$allocation->movement_type]
                                ?? ucwords(str_replace('_', ' ', $allocation->movement_type)) }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Quantity
                        </div>

                        <div class="mt-1 text-lg font-bold text-gray-900">
                            {{ $formatQty($allocation->quantity) }}
                            {{ $allocation->unit }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            From
                        </div>

                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $allocation->fromProject?->project_name
                                ?? 'Company Yard / Unallocated' }}
                        </div>

                        @if ($allocation->fromProject?->project_code)
                            <div class="text-xs text-gray-500">
                                {{ $allocation->fromProject->project_code }}
                            </div>
                        @endif
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            To
                        </div>

                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $allocation->toProject?->project_name
                                ?? 'Company Yard / Unallocated' }}
                        </div>

                        @if ($allocation->toProject?->project_code)
                            <div class="text-xs text-gray-500">
                                {{ $allocation->toProject->project_code }}
                            </div>
                        @endif
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Movement Date
                        </div>

                        <div class="mt-1 text-sm text-gray-900">
                            {{ $allocation->movement_date?->format('d M Y') }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Movement Time
                        </div>

                        <div class="mt-1 text-sm text-gray-900">
                            {{ $allocation->movement_time
                                ? \Illuminate\Support\Carbon::parse($allocation->movement_time)->format('h:i A')
                                : '—' }}
                        </div>
                    </div>

                    @if ($allocation->expected_return_date)
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Expected Return
                            </div>

                            <div class="mt-1 text-sm text-gray-900">
                                {{ $allocation->expected_return_date->format('d M Y') }}
                            </div>
                        </div>
                    @endif

                    @if ($allocation->actual_return_date)
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Actual Return
                            </div>

                            <div class="mt-1 text-sm text-gray-900">
                                {{ $allocation->actual_return_date->format('d M Y') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Transport --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900">
                    Reference & Transport
                </h2>

                <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Reference Number
                        </div>
                        <div class="mt-1 text-sm text-gray-900">
                            {{ $allocation->reference_number ?: '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Challan Number
                        </div>
                        <div class="mt-1 text-sm text-gray-900">
                            {{ $allocation->challan_number ?: '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Vehicle Number
                        </div>
                        <div class="mt-1 text-sm text-gray-900">
                            {{ $allocation->vehicle_number ?: '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Driver
                        </div>

                        <div class="mt-1 text-sm text-gray-900">
                            {{ $allocation->driver_name ?: '—' }}
                        </div>

                        @if ($allocation->driver_mobile)
                            <div class="text-xs text-gray-500">
                                {{ $allocation->driver_mobile }}
                            </div>
                        @endif
                    </div>

                    <div class="sm:col-span-2">
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Remarks
                        </div>

                        <div class="mt-1 whitespace-pre-line text-sm text-gray-900">
                            {{ $allocation->remarks ?: '—' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">

            {{-- Current Distribution --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900">
                    Current Distribution
                </h2>

                <p class="mt-1 text-xs text-gray-500">
                    Current confirmed location balance for this equipment.
                </p>

                <div class="mt-4 space-y-3">
                    @php
                        $yardBalance = (float) ($balances['yard'] ?? 0);
                    @endphp

                    @if ($yardBalance > 0)
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                            <div class="text-sm font-semibold text-gray-900">
                                Company Yard / Unallocated
                            </div>

                            <div class="mt-1 text-lg font-bold text-gray-900">
                                {{ $formatQty($yardBalance) }}
                                {{ $allocation->equipment?->unit }}
                            </div>
                        </div>
                    @endif

                    @foreach ($projectBalances as $projectId => $quantity)
                        @php
                            $project = $balanceProjects->get((int) $projectId);
                        @endphp

                        @if ($project)
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $project->project_name }}
                                </div>

                                @if ($project->project_code)
                                    <div class="text-xs text-gray-500">
                                        {{ $project->project_code }}
                                    </div>
                                @endif

                                <div class="mt-1 text-lg font-bold text-gray-900">
                                    {{ $formatQty($quantity) }}
                                    {{ $allocation->equipment?->unit }}
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @if (
                        $yardBalance <= 0
                        && $projectBalances->isEmpty()
                    )
                        <div class="rounded-lg bg-gray-50 p-3 text-sm text-gray-500">
                            No confirmed allocation balance.
                        </div>
                    @endif
                </div>

                <div class="mt-4 border-t border-gray-200 pt-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-600">
                            Registered Total
                        </span>

                        <span class="font-bold text-gray-900">
                            {{ $formatQty($allocation->equipment?->quantity ?? 0) }}
                            {{ $allocation->equipment?->unit }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Workflow Audit --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900">
                    Workflow
                </h2>

                <div class="mt-4 space-y-4 text-sm">

                    <div>
                        <div class="text-xs uppercase tracking-wide text-gray-500">
                            Created
                        </div>

                        <div class="mt-1 text-gray-900">
                            {{ $allocation->created_at?->format('d M Y, h:i A') }}
                        </div>

                        @if ($allocation->createdBy)
                            <div class="text-xs text-gray-500">
                                {{ $allocation->createdBy->name }}
                            </div>
                        @endif
                    </div>

                    @if ($allocation->transferred_at)
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500">
                                Transferred / Dispatched
                            </div>

                            <div class="mt-1 text-gray-900">
                                {{ $allocation->transferred_at->format('d M Y, h:i A') }}
                            </div>

                            @if ($allocation->transferredBy)
                                <div class="text-xs text-gray-500">
                                    {{ $allocation->transferredBy->name }}
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($allocation->received_at)
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500">
                                Received
                            </div>

                            <div class="mt-1 text-gray-900">
                                {{ $allocation->received_at->format('d M Y, h:i A') }}
                            </div>

                            @if ($allocation->receivedBy)
                                <div class="text-xs text-gray-500">
                                    {{ $allocation->receivedBy->name }}
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($allocation->cancelled_at)
                        <div>
                            <div class="text-xs uppercase tracking-wide text-red-500">
                                Cancelled
                            </div>

                            <div class="mt-1 text-gray-900">
                                {{ $allocation->cancelled_at->format('d M Y, h:i A') }}
                            </div>

                            @if ($allocation->cancelledBy)
                                <div class="text-xs text-gray-500">
                                    {{ $allocation->cancelledBy->name }}
                                </div>
                            @endif

                            @if ($allocation->cancellation_reason)
                                <div class="mt-2 rounded-lg bg-red-50 p-2 text-xs text-red-700">
                                    {{ $allocation->cancellation_reason }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection