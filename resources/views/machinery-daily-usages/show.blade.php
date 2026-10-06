@extends('layouts.app')

@section('content')
@php
    $equipmentName =
        $usage->equipment?->equipment_name
        ?: $usage->equipment?->machineryTool?->machine_name
        ?: 'Equipment';

    $equipmentCode =
        $usage->equipment?->equipment_code ?? '—';

    $statusLabel = match ($usage->status) {
    'draft' => 'Draft',
    'submitted' => 'Submitted',
    'verified' => 'Verified',
    'cancelled' => 'Cancelled',
    default => ucfirst(str_replace('_', ' ', $usage->status ?? '')),
};

    $conditionLabel = match ($usage->working_condition) {
        'working' => 'Working',
        'idle' => 'Idle',
        'breakdown' => 'Breakdown',
        'maintenance' => 'Under Maintenance',
        default => ucfirst(str_replace('_', ' ', $usage->working_condition ?? '')),
    };

    $shiftLabel = ucfirst(str_replace('_', ' ', $usage->shift ?? ''));

    $formatHours = function ($value) {
        return $value !== null
            ? number_format((float) $value, 2) . ' hrs'
            : '—';
    };

    $formatQty = function ($value, $decimals = 3) {
        if ($value === null) {
            return '—';
        }

        return rtrim(
            rtrim(number_format((float) $value, $decimals, '.', ''), '0'),
            '.'
        );
    };
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- ============================================================
         HEADER
    ============================================================ --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-bold text-gray-900">
                    Daily Machinery Log #{{ $usage->id }}
                </h1>

                @if ($usage->status === 'verified')
    <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
        Verified
    </span>
@elseif ($usage->status === 'submitted')
    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
        Submitted
    </span>
@elseif ($usage->status === 'draft')
    <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
        Draft
    </span>
@elseif ($usage->status === 'cancelled')
    <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
        Cancelled
    </span>
@else
    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
        {{ $statusLabel }}
    </span>
@endif
            </div>

            <p class="mt-1 text-sm text-gray-600">
                {{ $usage->project?->project_code }}
                @if ($usage->project?->project_name)
                    — {{ $usage->project->project_name }}
                @endif
            </p>
        </div>

        <div class="flex flex-wrap gap-2">

            <a href="{{ route('machinery-daily-usages.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Back to Logs
            </a>

            @if (
    !$usage->isVerified()
    && !$usage->isCancelled()
    && auth()->user()?->hasPermission('machinery_daily_usages.edit')
)
                <a href="{{ route('machinery-daily-usages.edit', $usage) }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Edit
                </a>
            @endif

            @if (auth()->user()?->hasPermission('machinery_daily_usages.create'))
    <a href="{{ route('machinery-daily-usages.create') }}"
       class="inline-flex items-center justify-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
        + New Daily Log
    </a>
@endif

        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- ============================================================
         EQUIPMENT SUMMARY
    ============================================================ --}}
    <section class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-5 py-4">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">

                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        Machinery / Equipment
                    </div>

                    <h2 class="mt-1 text-xl font-bold text-gray-900">
                        {{ $equipmentName }}
                    </h2>

                    <div class="mt-1 text-sm text-gray-500">
                        {{ $equipmentCode }}
                    </div>
                </div>

                <div class="text-left md:text-right">
                    <div class="text-xs text-gray-500">
                        Reporting Date
                    </div>

                    <div class="mt-1 text-lg font-semibold text-gray-900">
                        {{ $usage->usage_date?->format('d M Y') ?? '—' }}
                    </div>

                    <div class="mt-1 text-sm text-gray-500">
                        {{ $shiftLabel }} Shift
                    </div>
                </div>

            </div>
        </div>

        <div class="grid grid-cols-2 gap-px bg-gray-200 md:grid-cols-3 lg:grid-cols-6">

            <div class="bg-white p-4">
                <div class="text-xs text-gray-500">Quantity Used</div>
                <div class="mt-1 font-semibold text-gray-900">
                    {{ $formatQty($usage->quantity_used) }}
                    {{ $usage->unit }}
                </div>
            </div>

            <div class="bg-white p-4">
                <div class="text-xs text-gray-500">Ownership</div>
                <div class="mt-1 font-semibold text-gray-900">
                    {{ ucwords(str_replace('_', ' ', $usage->equipment?->ownership_type ?? '—')) }}
                </div>
            </div>

            <div class="bg-white p-4">
                <div class="text-xs text-gray-500">Tracking</div>
                <div class="mt-1 font-semibold text-gray-900">
                    {{ ucwords(str_replace('_', ' ', $usage->equipment?->tracking_mode ?? '—')) }}
                </div>
            </div>

            <div class="bg-white p-4">
                <div class="text-xs text-gray-500">Meter Type</div>
                <div class="mt-1 font-semibold text-gray-900">
                    {{ ucwords(str_replace('_', ' ', $usage->meter_type ?? 'None')) }}
                </div>
            </div>

            <div class="bg-white p-4">
                <div class="text-xs text-gray-500">Condition</div>
                <div class="mt-1 font-semibold text-gray-900">
                    {{ $conditionLabel }}
                </div>
            </div>

            <div class="bg-white p-4">
                <div class="text-xs text-gray-500">Status</div>
                <div class="mt-1 font-semibold text-gray-900">
                    {{ $statusLabel }}
                </div>
            </div>

        </div>
    </section>

    {{-- ============================================================
         TIME / UTILISATION
    ============================================================ --}}
    <section class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="font-semibold text-gray-900">
                Usage & Operating Hours
            </h2>
        </div>

        <div class="grid grid-cols-2 gap-4 p-5 md:grid-cols-3 lg:grid-cols-6">

            <div>
                <div class="text-xs text-gray-500">Start Time</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $usage->start_time ? \Carbon\Carbon::parse($usage->start_time)->format('h:i A') : '—' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500">End Time</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $usage->end_time ? \Carbon\Carbon::parse($usage->end_time)->format('h:i A') : '—' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500">Site Duration</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $formatHours($usage->site_hours) }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500">Operating Hours</div>
                <div class="mt-1 text-lg font-bold text-gray-900">
                    {{ $formatHours($usage->final_operating_hours) }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500">Idle Hours</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $formatHours($usage->idle_hours) }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500">Breakdown Hours</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $formatHours($usage->breakdown_hours) }}
                </div>
            </div>

        </div>

        @if ($usage->meter_type && $usage->meter_type !== 'none')
            <div class="border-t border-gray-100 px-5 py-4">

                <div class="mb-3 text-sm font-semibold text-gray-900">
                    Meter Readings
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">

                    <div class="rounded-lg bg-gray-50 p-4">
                        <div class="text-xs text-gray-500">
                            Opening Reading
                        </div>
                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $formatQty($usage->opening_meter_reading) }}
                        </div>
                    </div>

                    <div class="rounded-lg bg-gray-50 p-4">
                        <div class="text-xs text-gray-500">
                            Closing Reading
                        </div>
                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $formatQty($usage->closing_meter_reading) }}
                        </div>
                    </div>

                    <div class="rounded-lg bg-gray-50 p-4">
                        <div class="text-xs text-gray-500">
                            Meter Operating
                        </div>
                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $formatHours($usage->meter_operating_hours) }}
                        </div>
                    </div>

                    <div class="rounded-lg bg-gray-50 p-4">
                        <div class="text-xs text-gray-500">
                            Manual Override
                        </div>
                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $formatHours($usage->manual_operating_hours) }}
                        </div>
                    </div>

                </div>

                @if ($usage->manual_hours_reason)
                    <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4">
                        <div class="text-xs font-medium text-amber-700">
                            Manual Hours / Override Reason
                        </div>

                        <div class="mt-1 text-sm text-amber-900">
                            {{ $usage->manual_hours_reason }}
                        </div>
                    </div>
                @endif

            </div>
        @endif
    </section>

    {{-- ============================================================
         WORK / LOCATION
    ============================================================ --}}
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="font-semibold text-gray-900">
                    Work Performed
                </h2>
            </div>

            <div class="space-y-4 p-5">

                <div>
                    <div class="text-xs text-gray-500">
                        Work Activity
                    </div>

                    <div class="mt-1 text-sm font-medium text-gray-900">
                        @if ($usage->workActivity)
                            {{ $usage->workActivity->code }}
                            — {{ $usage->workActivity->name }}
                        @else
                            —
                        @endif
                    </div>
                </div>

                <div>
                    <div class="text-xs text-gray-500">
                        Work Done Entry
                    </div>

                    <div class="mt-1 text-sm font-medium text-gray-900">
                        {{ $usage->work_done_item_id
                            ? 'Work Item #' . $usage->work_done_item_id
                            : 'Not Linked' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-gray-500">
                        Work Description
                    </div>

                    <div class="mt-1 whitespace-pre-line text-sm text-gray-900">
                        {{ $usage->work_description ?: '—' }}
                    </div>
                </div>

            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="font-semibold text-gray-900">
                    Work Location
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">

                <div>
                    <div class="text-xs text-gray-500">Block</div>
                    <div class="mt-1 text-sm font-medium text-gray-900">
                        {{ $usage->projectBlock?->name ?: '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-gray-500">Floor</div>
                    <div class="mt-1 text-sm font-medium text-gray-900">
                        {{ $usage->projectFloor?->name ?: '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-gray-500">Unit</div>
                    <div class="mt-1 text-sm font-medium text-gray-900">
                        {{ $usage->projectUnit?->name ?: '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-gray-500">Room</div>
                    <div class="mt-1 text-sm font-medium text-gray-900">
                        {{ $usage->projectRoom?->name ?: $usage->projectRoom?->room_type ?: '—' }}
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <div class="text-xs text-gray-500">Sub-space</div>
                    <div class="mt-1 text-sm font-medium text-gray-900">
                        {{ $usage->projectSubspace?->name ?: '—' }}
                    </div>
                </div>

            </div>
        </section>

    </div>

    {{-- ============================================================
         OPERATOR / FUEL
    ============================================================ --}}
    <section class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="font-semibold text-gray-900">
                Operator & Fuel / Energy
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2 lg:grid-cols-4">

            <div>
                <div class="text-xs text-gray-500">ERP Operator</div>
                <div class="mt-1 text-sm font-medium text-gray-900">
                   {{ $usage->operatorUser?->name ?: '—' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500">Operator / Driver</div>
                <div class="mt-1 text-sm font-medium text-gray-900">
                    {{ $usage->operator_name ?: '—' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500">Operator Mobile</div>
                <div class="mt-1 text-sm font-medium text-gray-900">
                    {{ $usage->operator_mobile ?: '—' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500">Fuel / Energy Used</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">
                    @if ($usage->fuel_energy_quantity !== null)
                        {{ $formatQty($usage->fuel_energy_quantity) }}
                        {{ $usage->fuel_energy_unit }}
                    @else
                        —
                    @endif
                </div>
            </div>

        </div>

        @if ($usage->remarks)
            <div class="border-t border-gray-100 px-5 py-4">
                <div class="text-xs text-gray-500">
                    Remarks
                </div>

                <div class="mt-1 whitespace-pre-line text-sm text-gray-900">
                    {{ $usage->remarks }}
                </div>
            </div>
        @endif

    </section>

    {{-- ============================================================
     WORKFLOW / PMO VERIFICATION
============================================================ --}}

@if ($usage->isSubmitted() || $usage->isVerified() || $usage->isCancelled())

    <section class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-5 py-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="font-semibold text-gray-900">
                        Workflow & PMO Verification
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Submission and verification history for this machinery usage record.
                    </p>
                </div>

                @if ($usage->isVerified())
                    <span class="inline-flex w-fit rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                        PMO Verified
                    </span>
                @elseif ($usage->isCancelled())
                    <span class="inline-flex w-fit rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                        Cancelled
                    </span>
                @else
                    <span class="inline-flex w-fit rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                        Awaiting PMO Verification
                    </span>
                @endif

            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 lg:grid-cols-4">

            <div>
                <div class="text-xs text-gray-500">
                    Submitted By
                </div>

                <div class="mt-1 text-sm font-medium text-gray-900">
                    {{ $usage->submittedBy?->name ?: '—' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500">
                    Submitted At
                </div>

                <div class="mt-1 text-sm font-medium text-gray-900">
                    {{ $usage->submitted_at?->format('d M Y, h:i A') ?? '—' }}
                </div>
            </div>

            @if ($usage->isVerified())

                <div>
                    <div class="text-xs text-gray-500">
                        Verified By
                    </div>

                    <div class="mt-1 text-sm font-medium text-gray-900">
                        {{ $usage->verifiedBy?->name ?: '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-gray-500">
                        Verified At
                    </div>

                    <div class="mt-1 text-sm font-medium text-gray-900">
                        {{ $usage->verified_at?->format('d M Y, h:i A') ?? '—' }}
                    </div>
                </div>

            @endif

        </div>

        @if ($usage->isVerified() && $usage->verification_remarks)

            <div class="border-t border-gray-100 bg-blue-50 px-5 py-4">

                <div class="text-xs font-medium text-blue-700">
                    PMO Verification Remarks
                </div>

                <div class="mt-1 whitespace-pre-line text-sm text-blue-900">
                    {{ $usage->verification_remarks }}
                </div>

            </div>

        @endif

        @if ($usage->isCancelled())

            <div class="border-t border-red-100 bg-red-50 px-5 py-4">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                    <div>
                        <div class="text-xs font-medium text-red-700">
                            Cancelled By
                        </div>

                        <div class="mt-1 text-sm text-red-900">
                            {{ $usage->cancelledBy?->name ?: '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium text-red-700">
                            Cancelled At
                        </div>

                        <div class="mt-1 text-sm text-red-900">
                            {{ $usage->cancelled_at?->format('d M Y, h:i A') ?? '—' }}
                        </div>
                    </div>

                </div>

                @if ($usage->cancellation_reason)
                    <div class="mt-4">
                        <div class="text-xs font-medium text-red-700">
                            Cancellation Reason
                        </div>

                        <div class="mt-1 whitespace-pre-line text-sm text-red-900">
                            {{ $usage->cancellation_reason }}
                        </div>
                    </div>
                @endif

            </div>

        @endif

        @if (
            $usage->isSubmitted()
            && auth()->user()?->hasPermission('machinery_daily_usages.verify')
        )

            <div class="border-t border-gray-200 bg-gray-50 px-5 py-5">

                <form method="POST"
                      action="{{ route('machinery-daily-usages.verify', $usage) }}">

                    @csrf

                    <label for="verification_remarks"
                           class="block text-sm font-medium text-gray-700">
                        PMO Verification Remarks
                        <span class="font-normal text-gray-500">
                            (Optional)
                        </span>
                    </label>

                    <textarea
                        id="verification_remarks"
                        name="verification_remarks"
                        rows="3"
                        maxlength="2000"
                        class="mt-2 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                        placeholder="Enter verification observations or remarks, if any.">{{ old('verification_remarks') }}</textarea>

                    @error('verification_remarks')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">

                        <p class="text-xs text-gray-500">
                            Verification will lock this record from further editing.
                        </p>

                        <button
                            type="submit"
                            onclick="return confirm('Verify this machinery usage record? Once verified, it will be locked from editing.')"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">
                            Verify Usage
                        </button>

                    </div>

                </form>

            </div>

        @endif

        @if (
            !$usage->isVerified()
            && !$usage->isCancelled()
            && auth()->user()?->hasPermission('machinery_daily_usages.cancel')
        )

            <div class="border-t border-gray-200 px-5 py-5">

                <details>
                    <summary class="cursor-pointer text-sm font-medium text-red-700">
                        Cancel this machinery usage record
                    </summary>

                    <form method="POST"
                          action="{{ route('machinery-daily-usages.cancel', $usage) }}"
                          class="mt-4">

                        @csrf

                        <label for="cancellation_reason"
                               class="block text-sm font-medium text-gray-700">
                            Cancellation Reason
                            <span class="text-red-600">*</span>
                        </label>

                        <textarea
                            id="cancellation_reason"
                            name="cancellation_reason"
                            rows="3"
                            maxlength="1000"
                            required
                            class="mt-2 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500"
                            placeholder="Explain why this record is being cancelled.">{{ old('cancellation_reason') }}</textarea>

                        @error('cancellation_reason')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                        <div class="mt-3 flex justify-end">

                            <button
                                type="submit"
                                onclick="return confirm('Cancel this machinery usage record?')"
                                class="inline-flex items-center justify-center rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">
                                Cancel Record
                            </button>

                        </div>

                    </form>
                </details>

            </div>

        @endif

    </section>

@endif

    {{-- ============================================================
         RECORD INFORMATION
    ============================================================ --}}
    <section class="rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="font-semibold text-gray-900">
                Record Information
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-3">

            <div>
                <div class="text-xs text-gray-500">
                    Created By
                </div>
                <div class="mt-1 text-sm font-medium text-gray-900">
                    {{ $usage->createdBy?->name ?: '—' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500">
                    Created At
                </div>
                <div class="mt-1 text-sm font-medium text-gray-900">
                    {{ $usage->created_at?->format('d M Y, h:i A') ?? '—' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500">
                    Last Updated
                </div>
                <div class="mt-1 text-sm font-medium text-gray-900">
                    {{ $usage->updated_at?->format('d M Y, h:i A') ?? '—' }}
                </div>
            </div>

        </div>
    </section>

</div>
@endsection