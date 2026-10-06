@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto space-y-5">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $machineryEquipment->equipment_code }}
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                {{ $machineryEquipment->display_name }}
            </p>
        </div>

        <div class="flex gap-2">
            <a
                href="{{ route('machinery-equipment.index') }}"
                class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50"
            >
                Back
            </a>

            <a
                href="{{ route('machinery-equipment.edit', $machineryEquipment) }}"
                class="px-4 py-2 rounded-lg bg-[#0F2A52] text-white text-sm font-semibold hover:opacity-90"
            >
                Edit Equipment
            </a>
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
            <div class="text-xs font-bold uppercase tracking-wide text-gray-400">
                Equipment Type
            </div>

            <div class="mt-2 font-semibold text-gray-900">
                {{ $machineryEquipment->machineryTool?->machine_name ?? '—' }}
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
            <div class="text-xs font-bold uppercase tracking-wide text-gray-400">
                Ownership
            </div>

            <div class="mt-2 font-semibold text-gray-900">
                {{ $machineryEquipment->ownership_label }}
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
            <div class="text-xs font-bold uppercase tracking-wide text-gray-400">
                Current Project
            </div>

            <div class="mt-2 font-semibold text-gray-900">
                {{ $machineryEquipment->currentProject?->project_name ?? 'Not Allocated' }}
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
            <div class="text-xs font-bold uppercase tracking-wide text-gray-400">
                Status
            </div>

            <div class="mt-2">
                <span
                    @class([
                        'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                        'bg-green-100 text-green-700' => in_array($machineryEquipment->status, ['available', 'working']),
                        'bg-blue-100 text-blue-700' => $machineryEquipment->status === 'allocated',
                        'bg-yellow-100 text-yellow-700' => in_array($machineryEquipment->status, ['idle', 'maintenance']),
                        'bg-red-100 text-red-700' => $machineryEquipment->status === 'breakdown',
                        'bg-gray-100 text-gray-600' => in_array($machineryEquipment->status, ['returned', 'inactive']),
                    ])
                >
                    {{ $machineryEquipment->status_label }}
                </span>
            </div>
        </div>

    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">

        <h2 class="text-lg font-bold text-gray-900 mb-5">
            Equipment Details
        </h2>

        <dl class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-x-8 gap-y-6">

            <div>
                <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">
                    Equipment Code
                </dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $machineryEquipment->equipment_code }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">
                    Asset Number
                </dt>
                <dd class="mt-1 text-sm text-gray-700">
                    {{ $machineryEquipment->asset_number ?: '—' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">
                    Tracking
                </dt>
                <dd class="mt-1 text-sm text-gray-700">
                    {{ $machineryEquipment->tracking_mode === 'pooled' ? 'Pooled' : 'Individual' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">
                    Quantity
                </dt>
                <dd class="mt-1 text-sm text-gray-700">
                    {{ rtrim(rtrim(number_format((float) $machineryEquipment->quantity, 3), '0'), '.') }}
                    {{ $machineryEquipment->unit }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">
                    Make
                </dt>
                <dd class="mt-1 text-sm text-gray-700">
                    {{ $machineryEquipment->make ?: '—' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">
                    Model
                </dt>
                <dd class="mt-1 text-sm text-gray-700">
                    {{ $machineryEquipment->model ?: '—' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">
                    Serial Number
                </dt>
                <dd class="mt-1 text-sm text-gray-700">
                    {{ $machineryEquipment->serial_number ?: '—' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">
                    Registration Number
                </dt>
                <dd class="mt-1 text-sm text-gray-700">
                    {{ $machineryEquipment->registration_number ?: '—' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">
                    Manufacture Year
                </dt>
                <dd class="mt-1 text-sm text-gray-700">
                    {{ $machineryEquipment->manufacture_year ?: '—' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">
                    Capacity
                </dt>
                <dd class="mt-1 text-sm text-gray-700">
                    @if($machineryEquipment->capacity)
                        {{ rtrim(rtrim(number_format((float) $machineryEquipment->capacity, 3), '0'), '.') }}
                        {{ $machineryEquipment->capacity_unit }}
                    @else
                        —
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">
                    Meter Type
                </dt>
                <dd class="mt-1 text-sm text-gray-700">
                    {{ ucwords(str_replace('_', ' ', $machineryEquipment->meter_type)) }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">
                    Current Meter
                </dt>
                <dd class="mt-1 text-sm text-gray-700">
                    {{ $machineryEquipment->current_meter_reading ?? '—' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">
                    Fuel / Power
                </dt>
                <dd class="mt-1 text-sm text-gray-700">
                    {{ ucfirst($machineryEquipment->fuel_type) }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">
                    Commissioned
                </dt>
                <dd class="mt-1 text-sm text-gray-700">
                    {{ $machineryEquipment->commissioned_date?->format('d M Y') ?? '—' }}
                </dd>
            </div>

        </dl>

    </div>

    @if($machineryEquipment->isRented() || $machineryEquipment->isContractorProvided())

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">

            <h2 class="text-lg font-bold text-gray-900 mb-4">
                Equipment Source
            </h2>

            @if($machineryEquipment->isRented())
                <div class="text-sm text-gray-700">
                    <span class="font-semibold">Vendor:</span>
                    {{ $machineryEquipment->vendor?->vendor_name ?? '—' }}
                </div>
            @else
                <div class="text-sm text-gray-700">
                    <span class="font-semibold">Contractor:</span>
                    {{ $machineryEquipment->contractor?->contractor_name ?? '—' }}
                </div>
            @endif

        </div>

    @endif

    @if($machineryEquipment->remarks)

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">

            <h2 class="text-lg font-bold text-gray-900 mb-3">
                Remarks
            </h2>

            <p class="whitespace-pre-line text-sm text-gray-700">
                {{ $machineryEquipment->remarks }}
            </p>

        </div>

    @endif

</div>

@endsection