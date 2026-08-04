@php
    $selectedManpowerSourceId = old(
        'manpower_source_id',
        $labour->manpower_source_id ?? ''
    );

    $selectedLabourCategoryId = old(
        'labour_category_id',
        $labour->labour_category_id ?? ''
    );

    $selectedLabourTypeId = old(
        'labour_type_id',
        $labour->labour_type_id ?? ''
    );

    $selectedDesignationRoleId = old(
        'designation_role_id',
        $labour->designation_role_id ?? ''
    );

    $selectedSkillCategoryId = old(
        'skill_category_id',
        $labour->skill_category_id ?? ''
    );

    $selectedDefaultShiftId = old(
        'default_shift_id',
        $labour->default_shift_id ?? ''
    );

    $selectedContractorId = old(
        'contractor_id',
        $labour->contractor_id ?? ''
    );

    $selectedWageBasis = old(
        'wage_basis',
        $labour->wage_basis ?? 'daily'
    );

    $selectedOtCalculationType = old(
        'ot_calculation_type',
        $labour->ot_calculation_type ?? 'fixed_rate'
    );
@endphp

<div
    id="labour-master-form"
    data-selected-labour-type="{{ $selectedLabourTypeId }}"
    data-selected-designation-role="{{ $selectedDesignationRoleId }}"
    data-selected-skill-category="{{ $selectedSkillCategoryId }}"
    data-selected-default-shift="{{ $selectedDefaultShiftId }}"
    data-is-edit="{{ isset($labour) ? '1' : '0' }}"
>
    {{-- Labour Identification --}}
    <x-rds.section
        title="Labour Identification"
        description="Record the labourer's identity, contact information, photo, and emergency details."
    >
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

            @if(isset($labour))
                <x-rds.input
                    name="labour_code"
                    label="Labour Code"
                    value="{{ old('labour_code', $labour->labour_code) }}"
                    maxlength="30"
                    required
                    readonly
                />
            @else
                <x-rds.input
                    name="labour_code"
                    label="Labour Code"
                    value="{{ old('labour_code') }}"
                    placeholder="Leave blank for automatic generation"
                    maxlength="30"
                />
            @endif

            <x-rds.input
                name="full_name"
                label="Full Name"
                value="{{ old('full_name', $labour->full_name ?? '') }}"
                placeholder="Enter labour full name"
                maxlength="150"
                required
            />

            <x-rds.select
                name="gender_id"
                label="Gender"
            >
                <option value="">Select Gender</option>

                @foreach($genders as $gender)
                    <option
                        value="{{ $gender->id }}"
                        @selected(
                            (string) old(
                                'gender_id',
                                $labour->gender_id ?? ''
                            ) === (string) $gender->id
                        )
                    >
                        {{ $gender->name }}
                    </option>
                @endforeach
            </x-rds.select>

            <x-rds.input
                name="mobile"
                label="Mobile Number"
                value="{{ old('mobile', $labour->mobile ?? '') }}"
                placeholder="Example: 9876543210"
                maxlength="20"
            />

            <x-rds.input
                name="alternate_mobile"
                label="Alternate Mobile"
                value="{{ old(
                    'alternate_mobile',
                    $labour->alternate_mobile ?? ''
                ) }}"
                placeholder="Optional alternate number"
                maxlength="20"
            />

            <x-rds.input
                name="date_of_birth"
                label="Date of Birth"
                type="date"
                value="{{ old(
                    'date_of_birth',
                    isset($labour) && $labour->date_of_birth
                        ? $labour->date_of_birth->format('Y-m-d')
                        : ''
                ) }}"
            />

            <x-rds.select
                name="identity_type"
                label="Identity Type"
            >
                <option value="">Select Identity Type</option>

                @foreach([
                    'Aadhaar Card',
                    'Voter ID',
                    'Driving Licence',
                    'PAN Card',
                    'Passport',
                    'Labour Card',
                    'Other',
                ] as $identityType)
                    <option
                        value="{{ $identityType }}"
                        @selected(
                            old(
                                'identity_type',
                                $labour->identity_type ?? ''
                            ) === $identityType
                        )
                    >
                        {{ $identityType }}
                    </option>
                @endforeach
            </x-rds.select>

            <x-rds.input
                name="identity_number"
                label="Identity Number"
                value="{{ old(
                    'identity_number',
                    $labour->identity_number ?? ''
                ) }}"
                placeholder="Enter identity document number"
                maxlength="100"
            />

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Labour Photo
                </label>

                <input
                    type="file"
                    name="photo"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200"
                >

                <p class="mt-1 text-xs text-gray-500">
                    JPG, PNG, or WEBP. Maximum size: 5 MB.
                </p>

                @error('photo')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            @if(isset($labour) && $labour->photo_path)
                <div class="md:col-span-2 xl:col-span-3">
                    <div class="flex items-center gap-4 rounded-lg border border-gray-200 p-4">

                        <img
                            src="{{ asset('storage/' . $labour->photo_path) }}"
                            alt="{{ $labour->full_name }}"
                            class="h-20 w-20 rounded-lg border border-gray-200 object-cover"
                        >

                        <label class="flex items-start gap-3">
                            <input
                                type="checkbox"
                                name="remove_photo"
                                value="1"
                                class="mt-1 rounded border-gray-300"
                                @checked(old('remove_photo'))
                            >

                            <span>
                                <span class="block text-sm font-semibold text-gray-800">
                                    Remove Current Photo
                                </span>

                                <span class="mt-1 block text-xs text-gray-500">
                                    The existing photo will be removed when the profile is updated.
                                </span>
                            </span>
                        </label>

                    </div>
                </div>
            @endif

            <div class="md:col-span-2 xl:col-span-3">
                <x-rds.textarea
                    name="address"
                    label="Address"
                    rows="3"
                    value="{{ old('address', $labour->address ?? '') }}"
                    placeholder="Enter permanent or current address"
                />
            </div>

            <x-rds.input
                name="emergency_contact_name"
                label="Emergency Contact Name"
                value="{{ old(
                    'emergency_contact_name',
                    $labour->emergency_contact_name ?? ''
                ) }}"
                placeholder="Enter emergency contact person"
                maxlength="150"
            />

            <x-rds.input
                name="emergency_contact_mobile"
                label="Emergency Contact Mobile"
                value="{{ old(
                    'emergency_contact_mobile',
                    $labour->emergency_contact_mobile ?? ''
                ) }}"
                placeholder="Enter emergency mobile number"
                maxlength="20"
            />

        </div>
    </x-rds.section>

    {{-- Labour Classification --}}
    <x-rds.section
        title="Labour Classification"
        description="Select Labour Category, Trade, and Designation. Skill Category is derived automatically from the Designation Role."
    >
        <div class="mb-5 rounded-lg border border-blue-200 bg-blue-50 p-4">
            <p class="text-sm font-semibold text-blue-900">
                Classification dependency
            </p>

            <p class="mt-1 text-xs leading-5 text-blue-800">
                Labour Category → Trade / Manpower Category → Designation Role → Skill Category.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

            <x-rds.select
                name="labour_category_id"
                label="Labour Category"
                id="labour_category_id"
                required
            >
                <option value="">Select Labour Category</option>

                @foreach($labourCategories as $labourCategory)
                    <option
                        value="{{ $labourCategory->id }}"
                        @selected(
                            (string) $selectedLabourCategoryId
                            === (string) $labourCategory->id
                        )
                    >
                        {{ $labourCategory->category_name }}
                    </option>
                @endforeach
            </x-rds.select>

            <x-rds.select
                name="labour_type_id"
                label="Trade / Manpower Category"
                id="labour_type_id"
                required
            >
                <option value="">Select Labour Category First</option>

                @foreach($labourTypes as $labourType)
                    @if(
                        (string) $labourType->labour_category_id
                        === (string) $selectedLabourCategoryId
                    )
                        <option
                            value="{{ $labourType->id }}"
                            @selected(
                                (string) $selectedLabourTypeId
                                === (string) $labourType->id
                            )
                        >
                            {{ $labourType->labour_type_name }}
                        </option>
                    @endif
                @endforeach
            </x-rds.select>

            <x-rds.select
                name="designation_role_id"
                label="Designation Role"
                id="designation_role_id"
                required
            >
                <option value="">Select Trade First</option>

                @foreach($designationRoles as $designationRole)
                    @if(
                        (string) $designationRole->labour_type_id
                        === (string) $selectedLabourTypeId
                    )
                        <option
                            value="{{ $designationRole->id }}"
                            data-skill-category-id="{{ $designationRole->skill_category_id }}"
                            data-skill-category-name="{{ $designationRole->skillCategory?->name }}"
                            data-default-shift-id="{{ $designationRole->default_shift_id }}"
                            data-default-shift-name="{{ $designationRole->defaultShift?->name }}"
                            data-default-normal-shift-hours="{{ $designationRole->default_normal_shift_hours }}"
                            @if($canManageFinancial)
                                data-default-wage-basis="{{ $designationRole->default_wage_basis }}"
                                data-default-daily-rate="{{ $designationRole->getRawOriginal('default_daily_rate') }}"
                                data-default-hourly-rate="{{ $designationRole->getRawOriginal('default_hourly_rate') }}"
                                data-default-monthly-rate="{{ $designationRole->getRawOriginal('default_monthly_rate') }}"
                                data-default-ot-calculation-type="{{ $designationRole->default_ot_calculation_type }}"
                                data-default-ot-rate="{{ $designationRole->getRawOriginal('default_ot_rate') }}"
                                data-default-ot-multiplier="{{ $designationRole->getRawOriginal('default_ot_multiplier') }}"
                            @endif
                            @selected(
                                (string) $selectedDesignationRoleId
                                === (string) $designationRole->id
                            )
                        >
                            {{ $designationRole->name }}
                        </option>
                    @endif
                @endforeach
            </x-rds.select>

            <div>
                <label
                    for="skill_category_display"
                    class="mb-1 block text-sm font-medium text-gray-700"
                >
                    Skill Category
                </label>

                <input
                    type="text"
                    id="skill_category_display"
                    value="{{ old(
                        'skill_category_display',
                        isset($labour)
                            ? $labour->skillCategory?->name
                            : ''
                    ) }}"
                    placeholder="Auto-filled from Designation"
                    readonly
                    class="block w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-700 shadow-sm"
                >

                <input
                    type="hidden"
                    name="skill_category_id"
                    id="skill_category_id"
                    value="{{ $selectedSkillCategoryId }}"
                >

                <p class="mt-1 text-xs text-gray-500">
                    Automatically controlled by the selected Designation Role.
                </p>

                @error('skill_category_id')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

        </div>
    </x-rds.section>

    {{-- Source and Assignment --}}
    <x-rds.section
        title="Source & Assignment"
        description="Record the manpower source, contractor, current project, and operational shift."
    >
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

            <x-rds.select
                name="manpower_source_id"
                label="Manpower Source"
                id="manpower_source_id"
                required
            >
                <option value="">Select Manpower Source</option>

                @foreach($manpowerSources as $manpowerSource)
                    <option
                        value="{{ $manpowerSource->id }}"
                        data-requires-contractor="{{ $manpowerSource->requires_contractor ? '1' : '0' }}"
                        @selected(
                            (string) $selectedManpowerSourceId
                            === (string) $manpowerSource->id
                        )
                    >
                        {{ $manpowerSource->name }}
                    </option>
                @endforeach
            </x-rds.select>

            <div>
                <x-rds.select
                    name="contractor_id"
                    label="Contractor / Agency"
                    id="contractor_id"
                >
                    <option value="">Select Contractor / Agency</option>

                    @foreach($contractors as $contractor)
                        <option
                            value="{{ $contractor->id }}"
                            @selected(
                                (string) $selectedContractorId
                                === (string) $contractor->id
                            )
                        >
                            {{ $contractor->contractor_name }}
                        </option>
                    @endforeach
                </x-rds.select>

                <p
                    id="contractor-required-message"
                    class="mt-1 hidden text-xs text-amber-700"
                >
                    Contractor selection is mandatory for the selected manpower source.
                </p>
            </div>

            <x-rds.select
                name="current_project_id"
                label="Current / Default Project"
            >
                <option value="">Not Assigned</option>

                @foreach($projects as $project)
                    <option
                        value="{{ $project->id }}"
                        @selected(
                            (string) old(
                                'current_project_id',
                                $labour->current_project_id ?? ''
                            ) === (string) $project->id
                        )
                    >
                        {{ $project->project_name }}
                    </option>
                @endforeach
            </x-rds.select>

            <x-rds.select
                name="default_shift_id"
                label="Default Shift"
                id="default_shift_id"
            >
                <option value="">No Default Shift</option>

                @foreach($shifts as $shift)
                    <option
                        value="{{ $shift->id }}"
                        @selected(
                            (string) $selectedDefaultShiftId
                            === (string) $shift->id
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
                name="normal_shift_hours"
                label="Normal Shift Hours"
                id="normal_shift_hours"
                type="number"
                value="{{ old(
                    'normal_shift_hours',
                    $labour->normal_shift_hours ?? '8.00'
                ) }}"
                min="0.25"
                max="24"
                step="0.25"
                required
            />

            <div class="flex items-end">
                <div class="w-full rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <p class="text-sm font-semibold text-gray-800">
                        Operational defaults
                    </p>

                    <p class="mt-1 text-xs leading-5 text-gray-500">
                        Shift and hours are suggested from the Designation Role. Authorized users can override them.
                    </p>
                </div>
            </div>

        </div>
    </x-rds.section>

    {{-- Employment Information --}}
    <x-rds.section
        title="Employment Information"
        description="Record joining date, exit details, residency, and current employment status."
    >
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

            <x-rds.input
                name="joining_date"
                label="Joining Date"
                type="date"
                value="{{ old(
                    'joining_date',
                    isset($labour) && $labour->joining_date
                        ? $labour->joining_date->format('Y-m-d')
                        : ''
                ) }}"
            />

            <x-rds.input
                name="exit_date"
                label="Exit Date"
                type="date"
                id="exit_date"
                value="{{ old(
                    'exit_date',
                    isset($labour) && $labour->exit_date
                        ? $labour->exit_date->format('Y-m-d')
                        : ''
                ) }}"
            />

            <x-rds.select
                name="employment_status"
                label="Employment Status"
                id="employment_status"
                required
            >
                @foreach([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'on_leave' => 'On Leave',
                    'exited' => 'Exited',
                    'suspended' => 'Suspended',
                ] as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected(
                            old(
                                'employment_status',
                                $labour->employment_status ?? 'active'
                            ) === $value
                        )
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </x-rds.select>

            <x-rds.select
                name="residency_status"
                label="Residency Status"
                required
            >
                @foreach([
                    'not_specified' => 'Not Specified',
                    'local' => 'Local',
                    'non_local' => 'Non-Local',
                ] as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected(
                            old(
                                'residency_status',
                                $labour->residency_status ?? 'not_specified'
                            ) === $value
                        )
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </x-rds.select>

            <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4 md:col-span-2">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="mt-1 rounded border-gray-300"
                    @checked(old(
                        'is_active',
                        $labour->is_active ?? true
                    ))
                >

                <span>
                    <span class="block text-sm font-semibold text-gray-800">
                        Active Labour Record
                    </span>

                    <span class="mt-1 block text-xs text-gray-500">
                        Active labour profiles can be selected in attendance and project manpower interfaces.
                    </span>
                </span>
            </label>

        </div>
    </x-rds.section>

    {{-- Restricted Financial Information --}}
    @if($canManageFinancial)

        <x-rds.section
            title="Restricted Financial Information"
            description="Internal wage and overtime settings. Defaults are suggested from the selected Designation Role."
        >
            <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-900">
                    Confidential internal information
                </p>

                <p class="mt-1 text-xs leading-5 text-amber-800">
                    These values must never be disclosed to Site Engineer users.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

                <x-rds.select
                    name="wage_basis"
                    label="Wage Basis"
                    id="wage_basis"
                    required
                >
                    <option value="daily" @selected($selectedWageBasis === 'daily')>
                        Daily
                    </option>

                    <option value="hourly" @selected($selectedWageBasis === 'hourly')>
                        Hourly
                    </option>

                    <option value="monthly" @selected($selectedWageBasis === 'monthly')>
                        Monthly
                    </option>

                    <option
                        value="contractor_managed"
                        @selected($selectedWageBasis === 'contractor_managed')
                    >
                        Contractor Managed
                    </option>
                </x-rds.select>

                <div id="daily-rate-wrapper">
                    <x-rds.input
                        name="current_daily_rate"
                        label="Current Daily Rate"
                        id="current_daily_rate"
                        type="number"
                        value="{{ old(
                            'current_daily_rate',
                            isset($labour)
                                ? $labour->getRawOriginal('current_daily_rate')
                                : ''
                        ) }}"
                        min="0"
                        step="0.01"
                    />
                </div>

                <div id="hourly-rate-wrapper">
                    <x-rds.input
                        name="current_hourly_rate"
                        label="Current Hourly Rate"
                        id="current_hourly_rate"
                        type="number"
                        value="{{ old(
                            'current_hourly_rate',
                            isset($labour)
                                ? $labour->getRawOriginal('current_hourly_rate')
                                : ''
                        ) }}"
                        min="0"
                        step="0.01"
                    />
                </div>

                <div id="monthly-rate-wrapper">
                    <x-rds.input
                        name="current_monthly_rate"
                        label="Current Monthly Rate"
                        id="current_monthly_rate"
                        type="number"
                        value="{{ old(
                            'current_monthly_rate',
                            isset($labour)
                                ? $labour->getRawOriginal('current_monthly_rate')
                                : ''
                        ) }}"
                        min="0"
                        step="0.01"
                    />
                </div>

                <x-rds.select
                    name="ot_calculation_type"
                    label="OT Calculation Type"
                    id="ot_calculation_type"
                    required
                >
                    <option
                        value="fixed_rate"
                        @selected($selectedOtCalculationType === 'fixed_rate')
                    >
                        Fixed Hourly OT Rate
                    </option>

                    <option
                        value="multiplier"
                        @selected($selectedOtCalculationType === 'multiplier')
                    >
                        Multiplier of Normal Rate
                    </option>

                    <option
                        value="not_applicable"
                        @selected($selectedOtCalculationType === 'not_applicable')
                    >
                        Not Applicable
                    </option>
                </x-rds.select>

                <div id="ot-rate-wrapper">
                    <x-rds.input
                        name="current_ot_rate"
                        label="Current OT Rate"
                        id="current_ot_rate"
                        type="number"
                        value="{{ old(
                            'current_ot_rate',
                            isset($labour)
                                ? $labour->getRawOriginal('current_ot_rate')
                                : ''
                        ) }}"
                        min="0"
                        step="0.01"
                    />
                </div>

                <div id="ot-multiplier-wrapper">
                    <x-rds.input
                        name="ot_multiplier"
                        label="OT Multiplier"
                        id="ot_multiplier"
                        type="number"
                        value="{{ old(
                            'ot_multiplier',
                            isset($labour)
                                ? $labour->getRawOriginal('ot_multiplier')
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
            name="wage_basis"
            value="{{ old(
                'wage_basis',
                $labour->wage_basis ?? 'contractor_managed'
            ) }}"
        >

        <input
            type="hidden"
            name="ot_calculation_type"
            value="{{ old(
                'ot_calculation_type',
                $labour->ot_calculation_type ?? 'not_applicable'
            ) }}"
        >

    @endif

    {{-- Additional Information --}}
    <x-rds.section
        title="Additional Information"
        description="Add operational notes relevant to this labour profile."
    >
        <x-rds.textarea
            name="remarks"
            label="Remarks"
            rows="4"
            value="{{ old('remarks', $labour->remarks ?? '') }}"
            placeholder="Enter relevant notes about the labour profile"
        />
    </x-rds.section>
</div>

@push('scripts')
<script>
(function () {
    function initializeLabourMasterForm() {
        const root = document.getElementById('labour-master-form');

        if (!root || root.dataset.initialized === '1') {
            return;
        }

        root.dataset.initialized = '1';

        const initialLabourTypeId =
            root.dataset.selectedLabourType || '';

        const initialDesignationRoleId =
            root.dataset.selectedDesignationRole || '';

        const labourCategorySelect =
            document.getElementById('labour_category_id');

        const labourTypeSelect =
            document.getElementById('labour_type_id');

        const designationRoleSelect =
            document.getElementById('designation_role_id');

        const skillCategoryInput =
            document.getElementById('skill_category_id');

        const skillCategoryDisplay =
            document.getElementById('skill_category_display');

        const manpowerSourceSelect =
            document.getElementById('manpower_source_id');

        const contractorSelect =
            document.getElementById('contractor_id');

        const contractorMessage =
            document.getElementById('contractor-required-message');

        const defaultShiftSelect =
            document.getElementById('default_shift_id');

        const normalShiftHoursInput =
            document.getElementById('normal_shift_hours');

        const employmentStatusSelect =
            document.getElementById('employment_status');

        const exitDateInput =
            document.getElementById('exit_date');

        const wageBasisSelect =
            document.getElementById('wage_basis');

        const dailyRateInput =
            document.getElementById('current_daily_rate');

        const hourlyRateInput =
            document.getElementById('current_hourly_rate');

        const monthlyRateInput =
            document.getElementById('current_monthly_rate');

        const dailyRateWrapper =
            document.getElementById('daily-rate-wrapper');

        const hourlyRateWrapper =
            document.getElementById('hourly-rate-wrapper');

        const monthlyRateWrapper =
            document.getElementById('monthly-rate-wrapper');

        const otCalculationTypeSelect =
            document.getElementById('ot_calculation_type');

        const otRateInput =
            document.getElementById('current_ot_rate');

        const otMultiplierInput =
            document.getElementById('ot_multiplier');

        const otRateWrapper =
            document.getElementById('ot-rate-wrapper');

        const otMultiplierWrapper =
            document.getElementById('ot-multiplier-wrapper');

        function clearSkillCategory() {
            if (skillCategoryInput) {
                skillCategoryInput.value = '';
            }

            if (skillCategoryDisplay) {
                skillCategoryDisplay.value = '';
            }
        }

        function clearDesignationRoles() {
            if (!designationRoleSelect) {
                return;
            }

            designationRoleSelect.innerHTML =
                '<option value="">Select Trade First</option>';

            designationRoleSelect.disabled = true;

            clearSkillCategory();
        }

        function updateContractorRequirement() {
            if (!manpowerSourceSelect || !contractorSelect) {
                return;
            }

            const selectedOption =
                manpowerSourceSelect.options[
                    manpowerSourceSelect.selectedIndex
                ];

            const requiresContractor =
                selectedOption?.dataset.requiresContractor === '1';

            contractorSelect.required = requiresContractor;

            contractorMessage?.classList.toggle(
                'hidden',
                !requiresContractor
            );
        }

        async function loadLabourTypes(
            selectedLabourTypeId = ''
        ) {
            if (!labourCategorySelect || !labourTypeSelect) {
                return;
            }

            const categoryId = labourCategorySelect.value;

            labourTypeSelect.innerHTML =
                '<option value="">Loading Trades...</option>';

            labourTypeSelect.disabled = true;

            clearDesignationRoles();

            if (!categoryId) {
                labourTypeSelect.innerHTML =
                    '<option value="">Select Labour Category First</option>';

                return;
            }

            try {
                const response = await fetch(
                    `/ajax/labour-categories/${encodeURIComponent(categoryId)}/labour-types`,
                    {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    }
                );

                if (!response.ok) {
                    throw new Error(
                        `Unable to load Trades. HTTP ${response.status}`
                    );
                }

                const labourTypes = await response.json();

                labourTypeSelect.innerHTML =
                    '<option value="">Select Trade / Manpower Category</option>';

                labourTypes.forEach(function (labourType) {
                    const option = document.createElement('option');

                    option.value = labourType.id;
                    option.textContent =
                        labourType.labour_type_name;

                    if (
                        String(labourType.id) ===
                        String(selectedLabourTypeId)
                    ) {
                        option.selected = true;
                    }

                    labourTypeSelect.appendChild(option);
                });

                if (labourTypes.length === 0) {
                    labourTypeSelect.innerHTML =
                        '<option value="">No Trades mapped to this Labour Category</option>';
                }
            } catch (error) {
                console.error(error);

                labourTypeSelect.innerHTML =
                    '<option value="">Unable to load Trades</option>';
            } finally {
                labourTypeSelect.disabled = false;
            }

            if (labourTypeSelect.value) {
                await loadDesignationRoles(
                    initialDesignationRoleId
                );
            }
        }

        async function loadDesignationRoles(
            selectedDesignationRoleId = ''
        ) {
            if (!labourTypeSelect || !designationRoleSelect) {
                return;
            }

            const labourTypeId = labourTypeSelect.value;

            designationRoleSelect.innerHTML =
                '<option value="">Loading Designations...</option>';

            designationRoleSelect.disabled = true;

            clearSkillCategory();

            if (!labourTypeId) {
                designationRoleSelect.innerHTML =
                    '<option value="">Select Trade First</option>';

                return;
            }

            try {
                const params = new URLSearchParams({
                    labour_type_id: labourTypeId,
                });

                const response = await fetch(
                    `{{ route('ajax.designation-roles') }}?${params.toString()}`,
                    {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    }
                );

                if (!response.ok) {
                    throw new Error(
                        `Unable to load Designations. HTTP ${response.status}`
                    );
                }

                const designationRoles =
                    await response.json();

                designationRoleSelect.innerHTML =
                    '<option value="">Select Designation Role</option>';

                designationRoles.forEach(function (
                    designationRole
                ) {
                    const option =
                        document.createElement('option');

                    option.value = designationRole.id;
                    option.textContent =
                        designationRole.name;

                    option.dataset.skillCategoryId =
                        designationRole.skill_category_id || '';

                    option.dataset.skillCategoryName =
                        designationRole.skill_category_name || '';

                    option.dataset.defaultShiftId =
                        designationRole.default_shift_id || '';

                    option.dataset.defaultNormalShiftHours =
                        designationRole
                            .default_normal_shift_hours || '';

                    option.dataset.defaultWageBasis =
                        designationRole.default_wage_basis || '';

                    option.dataset.defaultDailyRate =
                        designationRole.default_daily_rate || '';

                    option.dataset.defaultHourlyRate =
                        designationRole.default_hourly_rate || '';

                    option.dataset.defaultMonthlyRate =
                        designationRole.default_monthly_rate || '';

                    option.dataset.defaultOtCalculationType =
                        designationRole
                            .default_ot_calculation_type || '';

                    option.dataset.defaultOtRate =
                        designationRole.default_ot_rate || '';

                    option.dataset.defaultOtMultiplier =
                        designationRole
                            .default_ot_multiplier || '';

                    if (
                        String(designationRole.id) ===
                        String(selectedDesignationRoleId)
                    ) {
                        option.selected = true;
                    }

                    designationRoleSelect.appendChild(option);
                });

                if (designationRoles.length === 0) {
                    designationRoleSelect.innerHTML =
                        '<option value="">No Designations mapped to this Trade</option>';
                }
            } catch (error) {
                console.error(error);

                designationRoleSelect.innerHTML =
                    '<option value="">Unable to load Designations</option>';
            } finally {
                designationRoleSelect.disabled = false;
            }

            if (designationRoleSelect.value) {
                applyDesignationDefaults(true);
            }
        }

        function applyDesignationDefaults(
            preserveExistingValues = false
        ) {
            if (!designationRoleSelect) {
                return;
            }

            const selectedOption =
                designationRoleSelect.options[
                    designationRoleSelect.selectedIndex
                ];

            if (!selectedOption?.value) {
                clearSkillCategory();

                return;
            }

            if (skillCategoryInput) {
                skillCategoryInput.value =
                    selectedOption.dataset.skillCategoryId || '';
            }

            if (skillCategoryDisplay) {
                skillCategoryDisplay.value =
                    selectedOption.dataset.skillCategoryName ||
                    'Skill Category not mapped';
            }

            if (preserveExistingValues) {
                return;
            }

            if (
                defaultShiftSelect &&
                selectedOption.dataset.defaultShiftId
            ) {
                defaultShiftSelect.value =
                    selectedOption.dataset.defaultShiftId;
            }

            if (
                normalShiftHoursInput &&
                selectedOption.dataset.defaultNormalShiftHours
            ) {
                normalShiftHoursInput.value =
                    selectedOption.dataset
                        .defaultNormalShiftHours;
            }

            if (
                wageBasisSelect &&
                selectedOption.dataset.defaultWageBasis
            ) {
                wageBasisSelect.value =
                    selectedOption.dataset.defaultWageBasis;
            }

            if (dailyRateInput) {
                dailyRateInput.value =
                    selectedOption.dataset.defaultDailyRate || '';
            }

            if (hourlyRateInput) {
                hourlyRateInput.value =
                    selectedOption.dataset.defaultHourlyRate || '';
            }

            if (monthlyRateInput) {
                monthlyRateInput.value =
                    selectedOption.dataset.defaultMonthlyRate || '';
            }

            if (
                otCalculationTypeSelect &&
                selectedOption.dataset
                    .defaultOtCalculationType
            ) {
                otCalculationTypeSelect.value =
                    selectedOption.dataset
                        .defaultOtCalculationType;
            }

            if (otRateInput) {
                otRateInput.value =
                    selectedOption.dataset.defaultOtRate || '';
            }

            if (otMultiplierInput) {
                otMultiplierInput.value =
                    selectedOption.dataset
                        .defaultOtMultiplier || '';
            }

            updateWageFields();
            updateOtFields();
        }

        function updateExitDateRequirement() {
            if (!employmentStatusSelect || !exitDateInput) {
                return;
            }

            exitDateInput.required =
                employmentStatusSelect.value === 'exited';
        }

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

            if (dailyRateInput) {
                dailyRateInput.required =
                    wageBasis === 'daily';
            }

            if (hourlyRateInput) {
                hourlyRateInput.required =
                    wageBasis === 'hourly';
            }

            if (monthlyRateInput) {
                monthlyRateInput.required =
                    wageBasis === 'monthly';
            }
        }

        function updateOtFields() {
            if (!otCalculationTypeSelect) {
                return;
            }

            const otType =
                otCalculationTypeSelect.value;

            otRateWrapper?.classList.toggle(
                'hidden',
                otType !== 'fixed_rate'
            );

            otMultiplierWrapper?.classList.toggle(
                'hidden',
                otType !== 'multiplier'
            );

            if (otRateInput) {
                otRateInput.required =
                    otType === 'fixed_rate';
            }

            if (otMultiplierInput) {
                otMultiplierInput.required =
                    otType === 'multiplier';
            }
        }

        labourCategorySelect?.addEventListener(
            'change',
            function () {
                loadLabourTypes('');
            }
        );

        labourTypeSelect?.addEventListener(
            'change',
            function () {
                loadDesignationRoles('');
            }
        );

        designationRoleSelect?.addEventListener(
            'change',
            function () {
                applyDesignationDefaults(false);
            }
        );

        manpowerSourceSelect?.addEventListener(
            'change',
            updateContractorRequirement
        );

        employmentStatusSelect?.addEventListener(
            'change',
            updateExitDateRequirement
        );

        wageBasisSelect?.addEventListener(
            'change',
            updateWageFields
        );

        otCalculationTypeSelect?.addEventListener(
            'change',
            updateOtFields
        );

        updateContractorRequirement();
        updateExitDateRequirement();
        updateWageFields();
        updateOtFields();

        if (labourCategorySelect?.value) {
            loadLabourTypes(initialLabourTypeId);
        } else {
            if (labourTypeSelect) {
                labourTypeSelect.disabled = true;
            }

            if (designationRoleSelect) {
                designationRoleSelect.disabled = true;
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initializeLabourMasterForm,
            { once: true }
        );
    } else {
        initializeLabourMasterForm();
    }
})();
</script>
@endpush