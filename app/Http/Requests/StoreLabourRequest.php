<?php

namespace App\Http\Requests;

use App\Models\DesignationRole;
use App\Models\LabourType;
use App\Models\ManpowerSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreLabourRequest extends FormRequest
{
    /**
     * Route middleware controls Labour Master permissions.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a labour profile.
     */
    public function rules(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | Labour Identification
            |--------------------------------------------------------------------------
            */

            'labour_code' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('labours', 'labour_code')
                    ->whereNull('deleted_at'),
            ],

            'full_name' => [
                'required',
                'string',
                'max:150',
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/',
            ],

            'alternate_mobile' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/',
                'different:mobile',
            ],

            'gender_id' => [
                'nullable',
                'integer',
                'exists:genders,id',
            ],

            'date_of_birth' => [
                'nullable',
                'date',
                'before:today',
            ],

            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'identity_type' => [
                'nullable',
                'string',
                'max:50',
            ],

            'identity_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('labours', 'identity_number')
                    ->whereNull('deleted_at'),
            ],

            'address' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'emergency_contact_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'emergency_contact_mobile' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/',
            ],

            /*
            |--------------------------------------------------------------------------
            | Labour Classification
            |--------------------------------------------------------------------------
            |
            | Official dependency:
            |
            | Labour Category
            |      ↓
            | Trade / Manpower Category
            |      ↓
            | Designation Role
            |      ↓
            | Skill Category — derived automatically
            |
            */

            'manpower_source_id' => [
                'required',
                'integer',
                'exists:manpower_sources,id',
            ],

            'labour_category_id' => [
                'required',
                'integer',
                'exists:labour_categories,id',
            ],

            'labour_type_id' => [
                'required',
                'integer',
                'exists:labour_types,id',
            ],

            'designation_role_id' => [
                'required',
                'integer',
                'exists:designation_roles,id',
            ],

            /*
             * This field may be submitted by the read-only UI,
             * but the server will never trust it.
             *
             * The controller derives skill_category_id from the
             * selected Designation Role before saving.
             */
            'skill_category_id' => [
                'nullable',
                'integer',
                'exists:skill_categories,id',
            ],

            'default_shift_id' => [
                'nullable',
                'integer',
                'exists:shifts,id',
            ],

            'contractor_id' => [
                Rule::requiredIf(
                    fn (): bool => $this->selectedSourceRequiresContractor()
                ),
                'nullable',
                'integer',
                'exists:contractors,id',
            ],

            'current_project_id' => [
                'nullable',
                'integer',
                'exists:projects,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Employment Information
            |--------------------------------------------------------------------------
            */

            'joining_date' => [
                'nullable',
                'date',
            ],

            'exit_date' => [
                'nullable',
                'date',
                'after_or_equal:joining_date',
            ],

            'employment_status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                    'on_leave',
                    'exited',
                    'suspended',
                ]),
            ],

            'residency_status' => [
                'required',
                Rule::in([
                    'local',
                    'non_local',
                    'not_specified',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | Restricted Financial Information
            |--------------------------------------------------------------------------
            */

            'wage_basis' => [
                'required',
                Rule::in([
                    'daily',
                    'hourly',
                    'monthly',
                    'contractor_managed',
                ]),
            ],

            'current_daily_rate' => [
                Rule::requiredIf(
                    fn (): bool => $this->input('wage_basis') === 'daily'
                ),
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'current_hourly_rate' => [
                Rule::requiredIf(
                    fn (): bool => $this->input('wage_basis') === 'hourly'
                ),
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'current_monthly_rate' => [
                Rule::requiredIf(
                    fn (): bool => $this->input('wage_basis') === 'monthly'
                ),
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'ot_calculation_type' => [
                'required',
                Rule::in([
                    'fixed_rate',
                    'multiplier',
                    'not_applicable',
                ]),
            ],

            'current_ot_rate' => [
                Rule::requiredIf(
                    fn (): bool => $this->input('ot_calculation_type')
                        === 'fixed_rate'
                ),
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'ot_multiplier' => [
                Rule::requiredIf(
                    fn (): bool => $this->input('ot_calculation_type')
                        === 'multiplier'
                ),
                'nullable',
                'numeric',
                'min:0',
                'max:10',
            ],

            'normal_shift_hours' => [
                'required',
                'numeric',
                'min:0.25',
                'max:24',
            ],

            /*
            |--------------------------------------------------------------------------
            | Record Control
            |--------------------------------------------------------------------------
            */

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ];
    }

    /**
     * Perform relational validation after base rules pass.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->validateActiveMasterRecords($validator);
                $this->validateLabourTypeCategory($validator);
                $this->validateDesignationTrade($validator);
                $this->validateDesignationSkillMapping($validator);
                $this->validateEmploymentDates($validator);
            },
        ];
    }

    /**
     * Normalize submitted values before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'labour_code' => $this->normalizeUppercase(
                $this->input('labour_code')
            ),

            'full_name' => $this->normalizeText(
                $this->input('full_name')
            ),

            'mobile' => $this->normalizeText(
                $this->input('mobile')
            ),

            'alternate_mobile' => $this->normalizeText(
                $this->input('alternate_mobile')
            ),

            'identity_type' => $this->normalizeText(
                $this->input('identity_type')
            ),

            'identity_number' => $this->normalizeText(
                $this->input('identity_number')
            ),

            'emergency_contact_name' => $this->normalizeText(
                $this->input('emergency_contact_name')
            ),

            'emergency_contact_mobile' => $this->normalizeText(
                $this->input('emergency_contact_mobile')
            ),

            'employment_status' => $this->input(
                'employment_status',
                'active'
            ),

            'residency_status' => $this->input(
                'residency_status',
                'not_specified'
            ),

            'wage_basis' => $this->input(
                'wage_basis',
                'daily'
            ),

            'ot_calculation_type' => $this->input(
                'ot_calculation_type',
                'fixed_rate'
            ),

            'normal_shift_hours' => $this->input(
                'normal_shift_hours',
                8
            ),

            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Determine whether the selected manpower source
     * requires a Contractor / Agency.
     */
    private function selectedSourceRequiresContractor(): bool
    {
        $manpowerSourceId = $this->input('manpower_source_id');

        if (! $manpowerSourceId) {
            return false;
        }

        return ManpowerSource::query()
            ->whereKey($manpowerSourceId)
            ->where('requires_contractor', true)
            ->exists();
    }

    /**
     * Ensure selected supporting master records are active.
     */
    private function validateActiveMasterRecords(
        Validator $validator
    ): void {
        $checks = [
            [
                'field' => 'manpower_source_id',
                'table' => 'manpower_sources',
                'label' => 'manpower source',
                'status_column' => 'is_active',
            ],
            [
                'field' => 'gender_id',
                'table' => 'genders',
                'label' => 'gender',
                'status_column' => 'is_active',
            ],
            [
                'field' => 'designation_role_id',
                'table' => 'designation_roles',
                'label' => 'designation role',
                'status_column' => 'is_active',
            ],
            [
                'field' => 'default_shift_id',
                'table' => 'shifts',
                'label' => 'shift',
                'status_column' => 'is_active',
            ],
        ];

        foreach ($checks as $check) {
            $value = $this->input($check['field']);

            if (! $value) {
                continue;
            }

            $exists = DB::table($check['table'])
                ->where('id', $value)
                ->where($check['status_column'], true)
                ->exists();

            if (! $exists) {
                $validator->errors()->add(
                    $check['field'],
                    "The selected {$check['label']} is inactive."
                );
            }
        }

        if ($this->filled('labour_category_id')) {
            $activeCategoryExists = DB::table('labour_categories')
                ->where('id', $this->input('labour_category_id'))
                ->where('is_active', true)
                ->exists();

            if (! $activeCategoryExists) {
                $validator->errors()->add(
                    'labour_category_id',
                    'The selected Labour Category is inactive.'
                );
            }
        }

        if ($this->filled('labour_type_id')) {
            $activeTradeExists = DB::table('labour_types')
                ->where('id', $this->input('labour_type_id'))
                ->where('status', true)
                ->exists();

            if (! $activeTradeExists) {
                $validator->errors()->add(
                    'labour_type_id',
                    'The selected Trade / Manpower Category is inactive.'
                );
            }
        }
    }

    /**
     * Validate that the selected Trade belongs to
     * the selected Labour Category.
     */
    private function validateLabourTypeCategory(
        Validator $validator
    ): void {
        if (
            ! $this->filled('labour_category_id')
            || ! $this->filled('labour_type_id')
        ) {
            return;
        }

        $labourType = LabourType::query()
            ->find($this->input('labour_type_id'));

        if (! $labourType) {
            return;
        }

        if (
            (int) $labourType->labour_category_id
            !== (int) $this->input('labour_category_id')
        ) {
            $validator->errors()->add(
                'labour_type_id',
                'The selected Trade / Manpower Category does not belong to the selected Labour Category.'
            );
        }
    }

    /**
     * Validate that the Designation Role belongs to
     * the selected Trade / Manpower Category.
     */
    private function validateDesignationTrade(
        Validator $validator
    ): void {
        if (
            ! $this->filled('designation_role_id')
            || ! $this->filled('labour_type_id')
        ) {
            return;
        }

        $designationRole = DesignationRole::query()
            ->find($this->input('designation_role_id'));

        if (! $designationRole) {
            return;
        }

        if (
            (int) $designationRole->labour_type_id
            !== (int) $this->input('labour_type_id')
        ) {
            $validator->errors()->add(
                'designation_role_id',
                'The selected Designation Role does not belong to the selected Trade / Manpower Category.'
            );
        }
    }

    /**
     * Every selectable Designation Role must have a Skill Category.
     *
     * Skill is derived from the Designation Role and is not selected
     * independently by the user.
     */
    private function validateDesignationSkillMapping(
        Validator $validator
    ): void {
        if (! $this->filled('designation_role_id')) {
            return;
        }

        $designationRole = DesignationRole::query()
            ->find($this->input('designation_role_id'));

        if (! $designationRole) {
            return;
        }

        if (! $designationRole->skill_category_id) {
            $validator->errors()->add(
                'designation_role_id',
                'The selected Designation Role does not have a Skill Category mapping. Update the Designation Role Master before using it.'
            );
        }
    }

    /**
     * Validate employment date requirements.
     */
    private function validateEmploymentDates(
        Validator $validator
    ): void {
        if (
            $this->input('employment_status') === 'exited'
            && ! $this->filled('exit_date')
        ) {
            $validator->errors()->add(
                'exit_date',
                'Exit date is required when Employment Status is Exited.'
            );
        }
    }

    private function normalizeText(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function normalizeUppercase(mixed $value): mixed
    {
        $value = $this->normalizeText($value);

        return is_string($value)
            ? strtoupper($value)
            : $value;
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'labour_category_id.required' =>
                'Labour Category is required.',

            'labour_type_id.required' =>
                'Trade / Manpower Category is required.',

            'designation_role_id.required' =>
                'Designation Role is required.',

            'contractor_id.required' =>
                'Contractor is required for the selected Manpower Source.',

            'alternate_mobile.different' =>
                'Alternate mobile number must be different from the primary mobile number.',

            'identity_number.unique' =>
                'This identity number is already assigned to another labour profile.',

            'photo.max' =>
                'The labour photo must not exceed 5 MB.',

            'photo.mimes' =>
                'The labour photo must be a JPG, JPEG, PNG, or WEBP image.',

            'exit_date.after_or_equal' =>
                'Exit date must be on or after the Joining Date.',
        ];
    }
}