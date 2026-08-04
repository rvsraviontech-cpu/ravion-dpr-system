@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Designation Role Details"
    subtitle="View designation mapping, operational defaults, financial defaults, and record status."
>
    <x-slot:actions>

        @if(auth()->user()->hasPermission('labour_master_data.manage'))
            <x-rds.button
                href="{{ route('designation-roles.edit', $designationRole) }}"
                variant="primary"
            >
                Edit Designation Role
            </x-rds.button>
        @endif

        <x-rds.button
            href="{{ route('designation-roles.index') }}"
            variant="secondary"
        >
            Back to List
        </x-rds.button>

    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<div class="space-y-6">

    <x-rds.card>

        <div class="mb-5">
            <h2 class="text-base font-semibold text-gray-900">
                Designation Information
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Trade mapping, skill classification, and record details.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Designation Code
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $designationRole->code }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Designation Role
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $designationRole->name }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Trade / Manpower Category
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $designationRole->labourType?->labour_type_name ?? 'Not Mapped' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Skill Category
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $designationRole->skillCategory?->name ?? 'Not Mapped' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Sort Order
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $designationRole->sort_order }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Record Type
                </p>

                <div class="mt-2">
                    @if($designationRole->is_system)
                        <x-rds.badge variant="warning">
                            System Record
                        </x-rds.badge>
                    @else
                        <x-rds.badge variant="secondary">
                            Custom Record
                        </x-rds.badge>
                    @endif
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Status
                </p>

                <div class="mt-2">
                    @if($designationRole->is_active)
                        <x-rds.badge variant="success">
                            Active
                        </x-rds.badge>
                    @else
                        <x-rds.badge variant="danger">
                            Inactive
                        </x-rds.badge>
                    @endif
                </div>
            </div>

            <div class="md:col-span-2 xl:col-span-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Remarks
                </p>

                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-700">
                    {{ $designationRole->remarks ?: 'No remarks available.' }}
                </p>
            </div>

        </div>

    </x-rds.card>

    <x-rds.card>

        <div class="mb-5">
            <h2 class="text-base font-semibold text-gray-900">
                Operational Defaults
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Defaults automatically suggested when this designation is selected in Labour Master.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Default Shift
                </p>

                <p class="mt-1 text-sm text-gray-700">
                    {{ $designationRole->defaultShift?->name ?? 'No Default Shift' }}
                </p>

                @if(
                    $designationRole->defaultShift?->start_time
                    && $designationRole->defaultShift?->end_time
                )
                    <p class="mt-1 text-xs text-gray-500">
                        {{ \Carbon\Carbon::createFromFormat(
                            'H:i:s',
                            $designationRole->defaultShift->start_time
                        )->format('h:i A') }}

                        –

                        {{ \Carbon\Carbon::createFromFormat(
                            'H:i:s',
                            $designationRole->defaultShift->end_time
                        )->format('h:i A') }}

                        @if($designationRole->defaultShift->crosses_midnight)
                            · Ends next day
                        @endif
                    </p>
                @endif
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Default Normal Shift Hours
                </p>

                <p class="mt-1 text-sm font-medium text-gray-800">
                    {{ number_format(
                        (float) $designationRole->default_normal_shift_hours,
                        2
                    ) }}
                    Hours
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Skill Mapping Status
                </p>

                <div class="mt-2">
                    @if($designationRole->skill_category_id)
                        <x-rds.badge variant="success">
                            Skill Mapped
                        </x-rds.badge>
                    @else
                        <x-rds.badge variant="danger">
                            Skill Not Mapped
                        </x-rds.badge>
                    @endif
                </div>
            </div>

        </div>

    </x-rds.card>

    @if($canViewFinancial)

        <x-rds.card>

            <div class="mb-5">
                <h2 class="text-base font-semibold text-gray-900">
                    Restricted Financial Defaults
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Internal wage and overtime defaults. This section is hidden from unauthorized users.
                </p>
            </div>

            <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-900">
                    Confidential internal information
                </p>

                <p class="mt-1 text-xs leading-5 text-amber-800">
                    These values are used only as defaults when a labour profile is created and may be overridden by authorized users.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Default Wage Basis
                    </p>

                    <p class="mt-1 text-sm text-gray-800">
                        {{ match ($designationRole->default_wage_basis) {
                            'daily' => 'Daily',
                            'hourly' => 'Hourly',
                            'monthly' => 'Monthly',
                            'contractor_managed' => 'Contractor Managed',
                            default => ucfirst(
                                str_replace(
                                    '_',
                                    ' ',
                                    $designationRole->default_wage_basis
                                )
                            ),
                        } }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Applicable Default Rate
                    </p>

                    <p class="mt-1 text-sm font-semibold text-gray-900">
                        @switch($designationRole->default_wage_basis)

                            @case('daily')
                                @if(
                                    $designationRole->getRawOriginal(
                                        'default_daily_rate'
                                    ) !== null
                                )
                                    ₹{{ number_format(
                                        (float) $designationRole->getRawOriginal(
                                            'default_daily_rate'
                                        ),
                                        2
                                    ) }}
                                    / day
                                @else
                                    Not Defined
                                @endif
                                @break

                            @case('hourly')
                                @if(
                                    $designationRole->getRawOriginal(
                                        'default_hourly_rate'
                                    ) !== null
                                )
                                    ₹{{ number_format(
                                        (float) $designationRole->getRawOriginal(
                                            'default_hourly_rate'
                                        ),
                                        2
                                    ) }}
                                    / hour
                                @else
                                    Not Defined
                                @endif
                                @break

                            @case('monthly')
                                @if(
                                    $designationRole->getRawOriginal(
                                        'default_monthly_rate'
                                    ) !== null
                                )
                                    ₹{{ number_format(
                                        (float) $designationRole->getRawOriginal(
                                            'default_monthly_rate'
                                        ),
                                        2
                                    ) }}
                                    / month
                                @else
                                    Not Defined
                                @endif
                                @break

                            @default
                                Contractor Managed

                        @endswitch
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Default OT Calculation
                    </p>

                    <p class="mt-1 text-sm text-gray-800">
                        {{ match (
                            $designationRole->default_ot_calculation_type
                        ) {
                            'fixed_rate' => 'Fixed Hourly OT Rate',
                            'multiplier' => 'Multiplier of Normal Rate',
                            'not_applicable' => 'Not Applicable',
                            default => ucfirst(
                                str_replace(
                                    '_',
                                    ' ',
                                    $designationRole
                                        ->default_ot_calculation_type
                                )
                            ),
                        } }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Default OT Rate / Multiplier
                    </p>

                    <p class="mt-1 text-sm font-semibold text-gray-900">
                        @if(
                            $designationRole
                                ->default_ot_calculation_type
                            === 'fixed_rate'
                        )
                            @if(
                                $designationRole->getRawOriginal(
                                    'default_ot_rate'
                                ) !== null
                            )
                                ₹{{ number_format(
                                    (float) $designationRole->getRawOriginal(
                                        'default_ot_rate'
                                    ),
                                    2
                                ) }}
                                / hour
                            @else
                                Not Defined
                            @endif

                        @elseif(
                            $designationRole
                                ->default_ot_calculation_type
                            === 'multiplier'
                        )
                            @if(
                                $designationRole->getRawOriginal(
                                    'default_ot_multiplier'
                                ) !== null
                            )
                                {{ number_format(
                                    (float) $designationRole->getRawOriginal(
                                        'default_ot_multiplier'
                                    ),
                                    2
                                ) }}
                                ×
                            @else
                                Not Defined
                            @endif

                        @else
                            Not Applicable
                        @endif
                    </p>
                </div>

            </div>

        </x-rds.card>

    @endif

    @if(
        auth()->user()->hasPermission('labour_master_data.manage')
        && ! $designationRole->is_system
    )

        <x-rds.card>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="text-base font-semibold text-gray-900">
                        Status Control
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Activate or deactivate this custom designation role.
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route(
                        'designation-roles.toggle-status',
                        $designationRole
                    ) }}"
                >
                    @csrf
                    @method('PATCH')

                    <x-rds.button
                        type="submit"
                        variant="{{ $designationRole->is_active
                            ? 'danger'
                            : 'success' }}"
                    >
                        {{ $designationRole->is_active
                            ? 'Deactivate Designation Role'
                            : 'Activate Designation Role' }}
                    </x-rds.button>
                </form>

            </div>

        </x-rds.card>

    @endif

</div>

@endsection