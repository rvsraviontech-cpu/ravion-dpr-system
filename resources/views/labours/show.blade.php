@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Labour Profile"
    subtitle="View labour identification, classification, employment, and assignment details."
>
    <x-slot:actions>

        @if(auth()->user()->hasPermission('labour_masters.edit'))
            <x-rds.button
                href="{{ route('labours.edit', $labour) }}"
                variant="primary"
            >
                Edit Labour
            </x-rds.button>
        @endif

        <x-rds.button
            href="{{ route('labours.index') }}"
            variant="secondary"
        >
            Back to List
        </x-rds.button>

    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<div class="space-y-6">

    {{-- Profile Summary --}}
    <x-rds.card>

        <div class="flex flex-col gap-6 md:flex-row md:items-start">

            <div class="shrink-0">

                @if($labour->photo_path)
                    <img
                        src="{{ asset('storage/' . $labour->photo_path) }}"
                        alt="{{ $labour->full_name }}"
                        class="h-28 w-28 rounded-xl border border-gray-200 object-cover"
                    >
                @else
                    <div class="flex h-28 w-28 items-center justify-center rounded-xl border border-gray-200 bg-gray-100 text-3xl font-semibold text-gray-600">
                        {{ strtoupper(mb_substr($labour->full_name, 0, 1)) }}
                    </div>
                @endif

            </div>

            <div class="min-w-0 flex-1">

                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">
                            {{ $labour->full_name }}
                        </h2>

                        <p class="mt-1 text-sm font-medium text-gray-600">
                            {{ $labour->labour_code }}
                        </p>

                        <p class="mt-2 text-sm text-gray-500">
                            {{ $labour->designationRole?->name ?? 'Designation not assigned' }}
                            @if($labour->labourType)
                                · {{ $labour->labourType->labour_type_name }}
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">

                        @if($labour->is_active)
                            <x-rds.badge variant="success">
                                Active Record
                            </x-rds.badge>
                        @else
                            <x-rds.badge variant="danger">
                                Inactive Record
                            </x-rds.badge>
                        @endif

                        @php
                            $employmentVariant = match ($labour->employment_status) {
                                'active' => 'success',
                                'on_leave' => 'warning',
                                'exited', 'suspended' => 'danger',
                                default => 'secondary',
                            };

                            $employmentLabel = match ($labour->employment_status) {
                                'active' => 'Employed',
                                'inactive' => 'Inactive',
                                'on_leave' => 'On Leave',
                                'exited' => 'Exited',
                                'suspended' => 'Suspended',
                                default => ucfirst(str_replace('_', ' ', $labour->employment_status)),
                            };
                        @endphp

                        <x-rds.badge :variant="$employmentVariant">
                            {{ $employmentLabel }}
                        </x-rds.badge>

                    </div>

                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Mobile
                        </p>

                        <p class="mt-1 text-sm text-gray-800">
                            {{ $labour->mobile ?: 'Not provided' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Manpower Source
                        </p>

                        <p class="mt-1 text-sm text-gray-800">
                            {{ $labour->manpowerSource?->name ?? 'Not assigned' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Current Project
                        </p>

                        <p class="mt-1 text-sm text-gray-800">
                            {{ $labour->currentProject?->project_name ?? 'Not assigned' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Default Shift
                        </p>

                        <p class="mt-1 text-sm text-gray-800">
                            {{ $labour->defaultShift?->name ?? 'Not assigned' }}
                        </p>
                    </div>

                </div>

            </div>

        </div>

    </x-rds.card>

    {{-- Identification --}}
    <x-rds.card>

        <div class="mb-5">
            <h2 class="text-base font-semibold text-gray-900">
                Identification & Contact
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Personal identity, contact, and emergency information.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Full Name
                </p>

                <p class="mt-1 text-sm font-medium text-gray-900">
                    {{ $labour->full_name }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Gender
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $labour->gender?->name ?? 'Not specified' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Date of Birth
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $labour->date_of_birth?->format('d M Y') ?? 'Not provided' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Primary Mobile
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $labour->mobile ?: 'Not provided' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Alternate Mobile
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $labour->alternate_mobile ?: 'Not provided' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Identity
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    @if($labour->identity_type || $labour->identity_number)
                        {{ $labour->identity_type ?: 'Identity Document' }}
                        @if($labour->identity_number)
                            — {{ $labour->identity_number }}
                        @endif
                    @else
                        Not provided
                    @endif
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Emergency Contact
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $labour->emergency_contact_name ?: 'Not provided' }}
                </p>

                @if($labour->emergency_contact_mobile)
                    <p class="mt-1 text-xs text-gray-500">
                        {{ $labour->emergency_contact_mobile }}
                    </p>
                @endif
            </div>

            <div class="md:col-span-2 xl:col-span-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Address
                </p>

                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-700">
                    {{ $labour->address ?: 'No address provided.' }}
                </p>
            </div>

        </div>

    </x-rds.card>

    {{-- Classification --}}
    <x-rds.card>

        <div class="mb-5">
            <h2 class="text-base font-semibold text-gray-900">
                Labour Classification
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Source, category, trade, skill, designation, contractor, shift, and project allocation.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Manpower Source
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $labour->manpowerSource?->name ?? 'Not assigned' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Labour Category
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $labour->labourCategory?->category_name ?? 'Not assigned' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Trade / Manpower Category
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $labour->labourType?->labour_type_name ?? 'Not assigned' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Skill Category
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $labour->skillCategory?->name ?? 'Not assigned' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Designation Role
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $labour->designationRole?->name ?? 'Not assigned' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Contractor / Agency
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $labour->contractor?->contractor_name ?? 'Company / Direct Labour' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Default Shift
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $labour->defaultShift?->name ?? 'Not assigned' }}
                </p>

                @if($labour->defaultShift?->start_time && $labour->defaultShift?->end_time)
                    <p class="mt-1 text-xs text-gray-500">
                        {{ \Carbon\Carbon::createFromFormat(
                            'H:i:s',
                            $labour->defaultShift->start_time
                        )->format('h:i A') }}
                        –
                        {{ \Carbon\Carbon::createFromFormat(
                            'H:i:s',
                            $labour->defaultShift->end_time
                        )->format('h:i A') }}
                    </p>
                @endif
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Current / Default Project
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $labour->currentProject?->project_name ?? 'Not assigned' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Residency Status
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ match ($labour->residency_status) {
                        'local' => 'Local',
                        'non_local' => 'Non-Local',
                        default => 'Not Specified',
                    } }}
                </p>
            </div>

        </div>

    </x-rds.card>

    {{-- Employment --}}
    <x-rds.card>

        <div class="mb-5">
            <h2 class="text-base font-semibold text-gray-900">
                Employment Information
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Joining, exit, employment status, and record availability.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Joining Date
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $labour->joining_date?->format('d M Y') ?? 'Not provided' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Exit Date
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $labour->exit_date?->format('d M Y') ?? 'Not applicable' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Employment Status
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $employmentLabel }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Record Status
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $labour->is_active ? 'Active' : 'Inactive' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Created By
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $labour->createdBy?->name ?? 'System / Unknown' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Last Updated By
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $labour->updatedBy?->name ?? 'System / Unknown' }}
                </p>
            </div>

        </div>

    </x-rds.card>

    {{-- Restricted Financial Information --}}
    @if($canViewFinancial)

        <x-rds.card>

            <div class="mb-5">
                <h2 class="text-base font-semibold text-gray-900">
                    Restricted Financial Information
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Internal wage and overtime settings. This section is restricted from Engineer users.
                </p>
            </div>

            <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-900">
                    Confidential internal information
                </p>

                <p class="mt-1 text-xs leading-5 text-amber-800">
                    Do not disclose wage rates, overtime rates, or labour-cost information to unauthorized users.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Wage Basis
                    </p>

                    <p class="mt-1 text-sm text-gray-800">
                        {{ match ($labour->wage_basis) {
                            'daily' => 'Daily',
                            'hourly' => 'Hourly',
                            'monthly' => 'Monthly',
                            'contractor_managed' => 'Contractor Managed',
                            default => ucfirst(str_replace('_', ' ', $labour->wage_basis)),
                        } }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Current Applicable Rate
                    </p>

                    <p class="mt-1 text-sm font-semibold text-gray-900">
                        @switch($labour->wage_basis)
                            @case('daily')
                                ₹{{ number_format((float) $labour->getRawOriginal('current_daily_rate'), 2) }} / day
                                @break

                            @case('hourly')
                                ₹{{ number_format((float) $labour->getRawOriginal('current_hourly_rate'), 2) }} / hour
                                @break

                            @case('monthly')
                                ₹{{ number_format((float) $labour->getRawOriginal('current_monthly_rate'), 2) }} / month
                                @break

                            @default
                                Contractor Managed
                        @endswitch
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Normal Shift Hours
                    </p>

                    <p class="mt-1 text-sm text-gray-800">
                        {{ number_format((float) $labour->normal_shift_hours, 2) }} hours
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        OT Calculation
                    </p>

                    <p class="mt-1 text-sm text-gray-800">
                        {{ match ($labour->ot_calculation_type) {
                            'fixed_rate' => 'Fixed Hourly OT Rate',
                            'multiplier' => 'Multiplier of Normal Rate',
                            'not_applicable' => 'Not Applicable',
                            default => ucfirst(str_replace('_', ' ', $labour->ot_calculation_type)),
                        } }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        OT Rate / Multiplier
                    </p>

                    <p class="mt-1 text-sm font-semibold text-gray-900">
                        @if($labour->ot_calculation_type === 'fixed_rate')
                            ₹{{ number_format((float) $labour->getRawOriginal('current_ot_rate'), 2) }} / hour
                        @elseif($labour->ot_calculation_type === 'multiplier')
                            {{ number_format((float) $labour->getRawOriginal('ot_multiplier'), 2) }} ×
                        @else
                            Not Applicable
                        @endif
                    </p>
                </div>

            </div>

        </x-rds.card>

    @endif

    {{-- Remarks --}}
    <x-rds.card>

        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                Remarks
            </p>

            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-700">
                {{ $labour->remarks ?: 'No remarks available.' }}
            </p>
        </div>

    </x-rds.card>

    {{-- Status Control --}}
    @if(auth()->user()->hasPermission('labour_masters.toggle_status'))

        <x-rds.card>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="text-base font-semibold text-gray-900">
                        Labour Record Status
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Inactive labour profiles will not be available for new attendance entries.
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route('labours.toggle-status', $labour) }}"
                >
                    @csrf
                    @method('PATCH')

                    <x-rds.button
                        type="submit"
                        variant="{{ $labour->is_active ? 'danger' : 'success' }}"
                    >
                        {{ $labour->is_active
                            ? 'Deactivate Labour'
                            : 'Activate Labour' }}
                    </x-rds.button>
                </form>

            </div>

        </x-rds.card>

    @endif

</div>

@endsection