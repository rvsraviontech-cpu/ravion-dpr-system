@php
    $selectedWageBasis = old(
        'default_wage_basis',
        $designationRole->default_wage_basis ?? 'daily'
    );

    $selectedOtType = old(
        'default_ot_calculation_type',
        $designationRole->default_ot_calculation_type ?? 'fixed_rate'
    );
@endphp

<x-rds.section
    title="Designation Role Information"
    description="Map the designation to its Trade / Manpower Category, Skill Category, and default operational settings."
>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

        <x-rds.input
            name="code"
            label="Designation Code"
            value="{{ old('code', $designationRole->code ?? '') }}"
            placeholder="Example: ELECTRICIAN"
            maxlength="30"
            required
        />

        <x-rds.input
            name="name"
            label="Designation Role"
            value="{{ old('name', $designationRole->name ?? '') }}"
            placeholder="Example: Electrician"
            maxlength="150"
            required
        />

        <x-rds.select
            name="labour_type_id"
            label="Trade / Manpower Category"
            required
        >
            <option value="">Select Trade / Manpower Category</option>

            @foreach($labourTypes as $labourType)
                <option
                    value="{{ $labourType->id }}"
                    @selected(
                        (string) old(
                            'labour_type_id',
                            $designationRole->labour_type_id ?? ''
                        ) === (string) $labourType->id
                    )
                >
                    {{ $labourType->labour_type_name }}
                </option>
            @endforeach
        </x-rds.select>

        <x-rds.select
            name="skill_category_id"
            label="Skill Category"
            required
        >
            <option value="">Select Skill Category</option>

            @foreach($skillCategories as $skillCategory)
                <option
                    value="{{ $skillCategory->id }}"
                    @selected(
                        (string) old(
                            'skill_category_id',
                            $designationRole->skill_category_id ?? ''
                        ) === (string) $skillCategory->id
                    )
                >
                    {{ $skillCategory->name }}
                </option>
            @endforeach
        </x-rds.select>

        <x-rds.select
            name="default_shift_id"
            label="Default Shift"
        >
            <option value="">No Default Shift</option>

            @foreach($shifts as $shift)
                <option
                    value="{{ $shift->id }}"
                    @selected(
                        (string) old(
                            'default_shift_id',
                            $designationRole->default_shift_id ?? ''
                        ) === (string) $shift->id
                    )
                >
                    {{ $shift->name }}

                    @if($shift->start_time && $shift->end_time)
                        —
                        {{ \Carbon\Carbon::createFromFormat(
                            'H:i:s',
                            $shift->start_time
                        )->format('h:i A') }}

                        to

                        {{ \Carbon\Carbon::createFromFormat(
                            'H:i:s',
                            $shift->end_time
                        )->format('h:i A') }}
                    @endif
                </option>
            @endforeach
        </x-rds.select>

        <x-rds.input
            name="default_normal_shift_hours"
            label="Default Normal Shift Hours"
            type="number"
            value="{{ old(
                'default_normal_shift_hours',
                $designationRole->default_normal_shift_hours ?? '8.00'
            ) }}"
            min="0.25"
            max="24"
            step="0.25"
            required
        />

        <x-rds.input
            name="sort_order"
            label="Sort Order"
            type="number"
            value="{{ old('sort_order', $designationRole->sort_order ?? 0) }}"
            min="0"
            step="1"
            required
        />

        <div class="md:col-span-2 xl:col-span-3">
            <x-rds.textarea
                name="remarks"
                label="Remarks"
                rows="3"
                value="{{ old('remarks', $designationRole->remarks ?? '') }}"
                placeholder="Add notes about this designation role."
            />
        </div>

    </div>
</x-rds.section>

@if($canManageFinancial)

    <x-rds.section
        title="Restricted Financial Defaults"
        description="Default wage and overtime values applied when a labour profile is created. These values are hidden from Engineer users."
    >
        <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 p-4">
            <p class="text-sm font-semibold text-amber-900">
                Confidential internal settings
            </p>

            <p class="mt-1 text-xs leading-5 text-amber-800">
                These are default values only. Authorized users may override them in the Labour Master.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

            <x-rds.select
                name="default_wage_basis"
                label="Default Wage Basis"
                id="default_wage_basis"
                required
            >
                <option
                    value="daily"
                    @selected($selectedWageBasis === 'daily')
                >
                    Daily
                </option>

                <option
                    value="hourly"
                    @selected($selectedWageBasis === 'hourly')
                >
                    Hourly
                </option>

                <option
                    value="monthly"
                    @selected($selectedWageBasis === 'monthly')
                >
                    Monthly
                </option>

                <option
                    value="contractor_managed"
                    @selected($selectedWageBasis === 'contractor_managed')
                >
                    Contractor Managed
                </option>
            </x-rds.select>

            <div id="default-daily-rate-wrapper">
                <x-rds.input
                    name="default_daily_rate"
                    label="Default Daily Rate"
                    type="number"
                    value="{{ old(
                        'default_daily_rate',
                        isset($designationRole)
                            ? $designationRole->getRawOriginal('default_daily_rate')
                            : ''
                    ) }}"
                    min="0"
                    step="0.01"
                />
            </div>

            <div id="default-hourly-rate-wrapper">
                <x-rds.input
                    name="default_hourly_rate"
                    label="Default Hourly Rate"
                    type="number"
                    value="{{ old(
                        'default_hourly_rate',
                        isset($designationRole)
                            ? $designationRole->getRawOriginal('default_hourly_rate')
                            : ''
                    ) }}"
                    min="0"
                    step="0.01"
                />
            </div>

            <div id="default-monthly-rate-wrapper">
                <x-rds.input
                    name="default_monthly_rate"
                    label="Default Monthly Rate"
                    type="number"
                    value="{{ old(
                        'default_monthly_rate',
                        isset($designationRole)
                            ? $designationRole->getRawOriginal('default_monthly_rate')
                            : ''
                    ) }}"
                    min="0"
                    step="0.01"
                />
            </div>

            <x-rds.select
                name="default_ot_calculation_type"
                label="Default OT Calculation"
                id="default_ot_calculation_type"
                required
            >
                <option
                    value="fixed_rate"
                    @selected($selectedOtType === 'fixed_rate')
                >
                    Fixed Hourly OT Rate
                </option>

                <option
                    value="multiplier"
                    @selected($selectedOtType === 'multiplier')
                >
                    Multiplier of Normal Rate
                </option>

                <option
                    value="not_applicable"
                    @selected($selectedOtType === 'not_applicable')
                >
                    Not Applicable
                </option>
            </x-rds.select>

            <div id="default-ot-rate-wrapper">
                <x-rds.input
                    name="default_ot_rate"
                    label="Default OT Rate"
                    type="number"
                    value="{{ old(
                        'default_ot_rate',
                        isset($designationRole)
                            ? $designationRole->getRawOriginal('default_ot_rate')
                            : ''
                    ) }}"
                    min="0"
                    step="0.01"
                />
            </div>

            <div id="default-ot-multiplier-wrapper">
                <x-rds.input
                    name="default_ot_multiplier"
                    label="Default OT Multiplier"
                    type="number"
                    value="{{ old(
                        'default_ot_multiplier',
                        isset($designationRole)
                            ? $designationRole->getRawOriginal('default_ot_multiplier')
                            : ''
                    ) }}"
                    min="0"
                    max="10"
                    step="0.01"
                    placeholder="Example: 1.50"
                />
            </div>

        </div>
    </x-rds.section>

@else

    <input
        type="hidden"
        name="default_wage_basis"
        value="{{ old(
            'default_wage_basis',
            $designationRole->default_wage_basis ?? 'contractor_managed'
        ) }}"
    >

    <input
        type="hidden"
        name="default_ot_calculation_type"
        value="{{ old(
            'default_ot_calculation_type',
            $designationRole->default_ot_calculation_type ?? 'not_applicable'
        ) }}"
    >

@endif

<x-rds.section
    title="Record Status"
    description="Control whether this designation role is available in Labour Master and attendance workflows."
>
    <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4">
        <input
            type="checkbox"
            name="is_active"
            value="1"
            class="mt-1 rounded border-gray-300"
            @checked(old(
                'is_active',
                $designationRole->is_active ?? true
            ))
        >

        <span>
            <span class="block text-sm font-semibold text-gray-800">
                Active
            </span>

            <span class="mt-1 block text-xs text-gray-500">
                Active designation roles can be selected while registering or updating labour profiles.
            </span>
        </span>
    </label>
</x-rds.section>

@if(isset($designationRole) && $designationRole->is_system)
    <x-rds.alert type="warning">
        This is a protected system designation role and is read-only.
    </x-rds.alert>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const wageBasisSelect =
        document.getElementById('default_wage_basis');

    const dailyRateWrapper =
        document.getElementById('default-daily-rate-wrapper');

    const hourlyRateWrapper =
        document.getElementById('default-hourly-rate-wrapper');

    const monthlyRateWrapper =
        document.getElementById('default-monthly-rate-wrapper');

    const otTypeSelect =
        document.getElementById('default_ot_calculation_type');

    const otRateWrapper =
        document.getElementById('default-ot-rate-wrapper');

    const otMultiplierWrapper =
        document.getElementById('default-ot-multiplier-wrapper');

    function updateWageFields() {
        if (!wageBasisSelect) {
            return;
        }

        const wageBasis = wageBasisSelect.value;

        dailyRateWrapper?.classList.toggle(
            'hidden',
            wageBasis !== 'daily'
        );

        hourlyRateWrapper?.classList.toggle(
            'hidden',
            wageBasis !== 'hourly'
        );

        monthlyRateWrapper?.classList.toggle(
            'hidden',
            wageBasis !== 'monthly'
        );
    }

    function updateOtFields() {
        if (!otTypeSelect) {
            return;
        }

        const otType = otTypeSelect.value;

        otRateWrapper?.classList.toggle(
            'hidden',
            otType !== 'fixed_rate'
        );

        otMultiplierWrapper?.classList.toggle(
            'hidden',
            otType !== 'multiplier'
        );
    }

    wageBasisSelect?.addEventListener(
        'change',
        updateWageFields
    );

    otTypeSelect?.addEventListener(
        'change',
        updateOtFields
    );

    updateWageFields();
    updateOtFields();
});
</script>
@endpush