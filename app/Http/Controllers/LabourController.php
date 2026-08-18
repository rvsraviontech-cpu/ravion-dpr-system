<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Http\Requests\StoreLabourRequest;
use App\Http\Requests\UpdateLabourRequest;
use App\Models\Contractor;
use App\Models\DesignationRole;
use App\Models\Gender;
use App\Models\Labour;
use App\Models\LabourCategory;
use App\Models\LabourType;
use App\Models\ManpowerSource;
use App\Models\Project;
use App\Models\Shift;
use App\Models\SkillCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class LabourController extends Controller
{
    /**
     * Display the Labour Master list.
     */
    public function index(Request $request): View
    {
        $query = Labour::query()
            ->with([
                'gender',
                'manpowerSource',
                'labourCategory',
                'labourType',
                'skillCategory',
                'designationRole',
                'defaultShift',
                'contractor',
                'currentProject',
            ]);

        $query->search(
            trim($request->string('search')->toString())
        );

        if ($request->filled('manpower_source_id')) {
            $query->where(
                'manpower_source_id',
                $request->integer('manpower_source_id')
            );
        }

        if ($request->filled('labour_category_id')) {
            $query->where(
                'labour_category_id',
                $request->integer('labour_category_id')
            );
        }

        if ($request->filled('labour_type_id')) {
            $query->where(
                'labour_type_id',
                $request->integer('labour_type_id')
            );
        }

        if ($request->filled('skill_category_id')) {
            $query->where(
                'skill_category_id',
                $request->integer('skill_category_id')
            );
        }

        if ($request->filled('designation_role_id')) {
            $query->where(
                'designation_role_id',
                $request->integer('designation_role_id')
            );
        }

        if ($request->filled('contractor_id')) {
            $query->where(
                'contractor_id',
                $request->integer('contractor_id')
            );
        }

        if ($request->filled('current_project_id')) {
            $query->where(
                'current_project_id',
                $request->integer('current_project_id')
            );
        }

        if ($request->filled('employment_status')) {
            $query->where(
                'employment_status',
                $request->input('employment_status')
            );
        }

        if ($request->filled('residency_status')) {
            $query->where(
                'residency_status',
                $request->input('residency_status')
            );
        }

        if ($request->filled('record_status')) {
            if ($request->record_status === 'active') {
                $query->where('is_active', true);
            }

            if ($request->record_status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $labours = $query
            ->ordered()
            ->paginate(config('rds.pagination.per_page', 15))
            ->withQueryString();

        $filters = $this->getFilterOptions();

        $canViewFinancial = $this->canViewFinancial();

        return view(
            'labours.index',
            array_merge(
                compact(
                    'labours',
                    'canViewFinancial'
                ),
                $filters
            )
        );
    }

    /**
     * Show the Labour Master creation form.
     */
    public function create(): View
    {
        $formOptions = $this->getFormOptions();

        $canManageFinancial = $this->canManageFinancial();

        return view(
            'labours.create',
            array_merge(
                compact('canManageFinancial'),
                $formOptions
            )
        );
    }

    /**
     * Store a newly created labour profile.
     *
     * @throws Throwable
     */
    public function store(
        StoreLabourRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        $photoPath = null;

        try {
            $labour = DB::transaction(function () use (
                $request,
                $validated,
                &$photoPath
            ): Labour {
                $data = $this->prepareLabourData(
                    $validated,
                    true
                );

                if (empty($data['labour_code'])) {
                    $data['labour_code'] = $this->generateLabourCode();
                }

                if ($request->hasFile('photo')) {
                    $photoPath = $request
                        ->file('photo')
                        ->store('labours/photos', 'public');

                    $data['photo_path'] = $photoPath;
                }

                $data['created_by'] = auth()->id();
                $data['updated_by'] = auth()->id();

                $labour = Labour::create($data);

                $labour->load($this->labourRelationships());

                AuditHelper::log(
                    'Labour Master',
                    'Created',
                    Labour::class,
                    $labour->id,
                    "Labour profile '{$labour->labour_code} - {$labour->full_name}' was created.",
                    null,
                    $this->auditValues($labour)
                );

                return $labour;
            });

            return redirect()
                ->route('labours.show', $labour)
                ->with(
                    'success',
                    'Labour profile created successfully.'
                );
        } catch (Throwable $exception) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to create the labour profile. Please review the entered information and try again.'
                );
        }
    }

    /**
     * Display a labour profile.
     */
    public function show(Labour $labour): View
    {
        $labour->load([
            ...$this->labourRelationships(),
            'createdBy',
            'updatedBy',
        ]);

        $canViewFinancial = $this->canViewFinancial();

        return view(
            'labours.show',
            compact(
                'labour',
                'canViewFinancial'
            )
        );
    }

    /**
     * Show the labour profile edit form.
     */
    public function edit(Labour $labour): View
    {
        $labour->load($this->labourRelationships());

        $formOptions = $this->getFormOptions();

        $canManageFinancial = $this->canManageFinancial();

        return view(
            'labours.edit',
            array_merge(
                compact(
                    'labour',
                    'canManageFinancial'
                ),
                $formOptions
            )
        );
    }

    /**
     * Update the specified labour profile.
     *
     * @throws Throwable
     */
    public function update(
        UpdateLabourRequest $request,
        Labour $labour
    ): RedirectResponse {
        $validated = $request->validated();

        $newPhotoPath = null;
        $oldPhotoPath = $labour->photo_path;

        try {
            DB::transaction(function () use (
                $request,
                $validated,
                $labour,
                &$newPhotoPath,
                $oldPhotoPath
            ): void {
                $labour->load($this->labourRelationships());

                $oldValues = $this->auditValues($labour);

                $data = $this->prepareLabourData(
                    $validated,
                    false,
                    $labour
                );

                if ($request->boolean('remove_photo')) {
                    $data['photo_path'] = null;
                }

                if ($request->hasFile('photo')) {
                    $newPhotoPath = $request
                        ->file('photo')
                        ->store('labours/photos', 'public');

                    $data['photo_path'] = $newPhotoPath;
                }

                $data['updated_by'] = auth()->id();

                $labour->update($data);

                $labour->refresh();
                $labour->load($this->labourRelationships());

                AuditHelper::log(
                    'Labour Master',
                    'Updated',
                    Labour::class,
                    $labour->id,
                    "Labour profile '{$labour->labour_code} - {$labour->full_name}' was updated.",
                    $oldValues,
                    $this->auditValues($labour)
                );

                if (
                    (
                        $request->boolean('remove_photo')
                        || $newPhotoPath
                    )
                    && $oldPhotoPath
                    && $oldPhotoPath !== $labour->photo_path
                ) {
                    Storage::disk('public')->delete($oldPhotoPath);
                }
            });

            return redirect()
                ->route('labours.show', $labour)
                ->with(
                    'success',
                    'Labour profile updated successfully.'
                );
        } catch (Throwable $exception) {
            if ($newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to update the labour profile. Please review the entered information and try again.'
                );
        }
    }

    /**
     * Activate or deactivate a labour profile.
     */
    public function toggleStatus(
        Labour $labour
    ): RedirectResponse {
        $labour->load($this->labourRelationships());

        $oldValues = $this->auditValues($labour);

        $labour->update([
            'is_active' => ! $labour->is_active,
            'updated_by' => auth()->id(),
        ]);

        $labour->refresh();
        $labour->load($this->labourRelationships());

        $action = $labour->is_active
            ? 'Activated'
            : 'Deactivated';

        AuditHelper::log(
            'Labour Master',
            $action,
            Labour::class,
            $labour->id,
            "Labour profile '{$labour->labour_code} - {$labour->full_name}' was {$action}.",
            $oldValues,
            $this->auditValues($labour)
        );

        return back()->with(
            'success',
            $labour->is_active
                ? 'Labour profile activated successfully.'
                : 'Labour profile deactivated successfully.'
        );
    }

    /**
     * Return Labour Types filtered by Labour Category.
     */
    public function labourTypes(
        LabourCategory $labourCategory
    ) {
        $labourTypes = LabourType::query()
            ->where(
                'labour_category_id',
                $labourCategory->id
            )
            ->where('status', true)
            ->orderBy('labour_type_name')
            ->get([
                'id',
                'labour_type_name',
                'labour_category_id',
            ]);

        return response()->json($labourTypes);
    }

    /**
     * Return active Designation Roles for the selected
     * Trade / Manpower Category.
     *
     * Dependency:
     * Labour Category -> Trade -> Designation -> Skill/defaults.
     */
    public function designationRoles(Request $request)
    {
        $validated = $request->validate([
            'labour_type_id' => [
                'required',
                'integer',
                'exists:labour_types,id',
            ],
        ]);

        $canManageFinancial = $this->canManageFinancial();

        $designationRoles = DesignationRole::query()
            ->with([
                'skillCategory:id,code,name',
                'defaultShift:id,code,name,start_time,end_time,normal_hours,crosses_midnight',
            ])
            ->where('is_active', true)
            ->where('labour_type_id', $validated['labour_type_id'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (DesignationRole $designationRole) use ($canManageFinancial): array {
                $values = [
                    'id' => $designationRole->id,
                    'code' => $designationRole->code,
                    'name' => $designationRole->name,
                    'labour_type_id' => $designationRole->labour_type_id,
                    'skill_category_id' => $designationRole->skill_category_id,
                    'skill_category_name' => $designationRole->skillCategory?->name,
                    'default_shift_id' => $designationRole->default_shift_id,
                    'default_shift_name' => $designationRole->defaultShift?->name,
                    'default_normal_shift_hours' => $designationRole->default_normal_shift_hours,
                ];

                if ($canManageFinancial) {
                    $values = array_merge($values, [
                        'default_wage_basis' => $designationRole->default_wage_basis,
                        'default_daily_rate' => $designationRole->getRawOriginal('default_daily_rate'),
                        'default_hourly_rate' => $designationRole->getRawOriginal('default_hourly_rate'),
                        'default_monthly_rate' => $designationRole->getRawOriginal('default_monthly_rate'),
                        'default_ot_calculation_type' => $designationRole->default_ot_calculation_type,
                        'default_ot_rate' => $designationRole->getRawOriginal('default_ot_rate'),
                        'default_ot_multiplier' => $designationRole->getRawOriginal('default_ot_multiplier'),
                    ]);
                }

                return $values;
            });

        return response()->json($designationRoles);
    }

    /**
     * Prepare validated data before persistence.
     */
    private function prepareLabourData(
        array $validated,
        bool $creating,
        ?Labour $labour = null
    ): array {
        $data = $validated;

        unset(
            $data['photo'],
            $data['remove_photo']
        );

        $nullableTextFields = [
            'mobile',
            'alternate_mobile',
            'identity_type',
            'identity_number',
            'address',
            'emergency_contact_name',
            'emergency_contact_mobile',
            'remarks',
        ];

        foreach ($nullableTextFields as $field) {
            $data[$field] = $this->nullableTrim(
                $data[$field] ?? null
            );
        }

        $data['full_name'] = trim(
            $data['full_name']
        );

        if (! empty($data['labour_code'])) {
            $data['labour_code'] = strtoupper(
                trim($data['labour_code'])
            );
        }

        $nullableForeignKeys = [
            'gender_id',
            'labour_category_id',
            'labour_type_id',
            'skill_category_id',
            'designation_role_id',
            'default_shift_id',
            'contractor_id',
            'current_project_id',
        ];

        foreach ($nullableForeignKeys as $field) {
            $data[$field] = ! empty($data[$field])
                ? (int) $data[$field]
                : null;
        }

        $data['manpower_source_id'] = (int) $data[
            'manpower_source_id'
        ];

        $data = $this->applyDesignationDefaults(
            $data,
            $creating,
            $labour
        );

        $data['normal_shift_hours'] = (float) (
            $data['normal_shift_hours'] ?? 8
        );

        $data['is_active'] = (bool) (
            $data['is_active'] ?? false
        );

        $data = $this->prepareFinancialData(
            $data,
            $creating,
            $labour
        );

        if ($data['employment_status'] !== 'exited') {
            $data['exit_date'] = null;
        }

        return $data;
    }

    /**
     * Derive Skill Category and operational/financial defaults from
     * the selected Designation Role.
     *
     * User-entered values remain valid overrides. Defaults are used only
     * when a corresponding value is empty, or while creating a record.
     */
    private function applyDesignationDefaults(
        array $data,
        bool $creating,
        ?Labour $labour = null
    ): array {
        $designationRoleId = $data['designation_role_id'] ?? null;

        if (! $designationRoleId) {
            return $data;
        }

        $designationRole = DesignationRole::query()
            ->where('is_active', true)
            ->findOrFail($designationRoleId);

        // Skill is always controlled by the Designation Role master.
        $data['skill_category_id'] = $designationRole->skill_category_id;

        if (empty($data['default_shift_id']) && $designationRole->default_shift_id) {
            $data['default_shift_id'] = $designationRole->default_shift_id;
        }

        if (
            empty($data['normal_shift_hours'])
            && $designationRole->default_normal_shift_hours !== null
        ) {
            $data['normal_shift_hours'] = $designationRole->default_normal_shift_hours;
        }

        if (! $this->canManageFinancial()) {
            return $data;
        }

        if (empty($data['wage_basis'])) {
            $data['wage_basis'] = $designationRole->default_wage_basis;
        }

        if (empty($data['ot_calculation_type'])) {
            $data['ot_calculation_type'] = $designationRole->default_ot_calculation_type;
        }

        $defaults = [
            'current_daily_rate' => $designationRole->getRawOriginal('default_daily_rate'),
            'current_hourly_rate' => $designationRole->getRawOriginal('default_hourly_rate'),
            'current_monthly_rate' => $designationRole->getRawOriginal('default_monthly_rate'),
            'current_ot_rate' => $designationRole->getRawOriginal('default_ot_rate'),
            'ot_multiplier' => $designationRole->getRawOriginal('default_ot_multiplier'),
        ];

        foreach ($defaults as $field => $defaultValue) {
            // Designation values are defaults only. Never overwrite an
            // explicit wage/OT value submitted from the Labour form.
            $hasSubmittedValue = array_key_exists($field, $data)
                && $data[$field] !== null
                && $data[$field] !== '';

            if (! $hasSubmittedValue && $defaultValue !== null) {
                $data[$field] = $defaultValue;
            }
        }

        return $data;
    }

    /**
     * Prepare restricted financial fields.
     */
    private function prepareFinancialData(
        array $data,
        bool $creating,
        ?Labour $labour = null
    ): array {
        $financialFields = [
            'wage_basis',
            'current_daily_rate',
            'current_hourly_rate',
            'current_monthly_rate',
            'ot_calculation_type',
            'current_ot_rate',
            'ot_multiplier',
            'normal_shift_hours',
        ];

        if (! $this->canManageFinancial()) {
            foreach ($financialFields as $field) {
                unset($data[$field]);
            }

            if ($creating) {
                $data['wage_basis'] = 'contractor_managed';
                $data['ot_calculation_type'] = 'not_applicable';
                $data['normal_shift_hours'] = 8.00;

                $data['current_daily_rate'] = null;
                $data['current_hourly_rate'] = null;
                $data['current_monthly_rate'] = null;
                $data['current_ot_rate'] = null;
                $data['ot_multiplier'] = null;
            }

            return $data;
        }

        $wageBasis = $data['wage_basis'] ?? 'daily';

        if ($wageBasis !== 'daily') {
            $data['current_daily_rate'] = null;
        }

        if ($wageBasis !== 'hourly') {
            $data['current_hourly_rate'] = null;
        }

        if ($wageBasis !== 'monthly') {
            $data['current_monthly_rate'] = null;
        }

        if ($wageBasis === 'contractor_managed') {
            $data['current_daily_rate'] = null;
            $data['current_hourly_rate'] = null;
            $data['current_monthly_rate'] = null;
        }

        $otCalculationType = $data[
            'ot_calculation_type'
        ] ?? 'not_applicable';

        if ($otCalculationType !== 'fixed_rate') {
            $data['current_ot_rate'] = null;
        }

        if ($otCalculationType !== 'multiplier') {
            $data['ot_multiplier'] = null;
        }

        if ($otCalculationType === 'not_applicable') {
            $data['current_ot_rate'] = null;
            $data['ot_multiplier'] = null;
        }

        return $data;
    }

    /**
     * Generate the next Labour Code.
     *
     * Example: LAB-2026-0001
     */
    private function generateLabourCode(): string
    {
        $year = now()->format('Y');
        $prefix = "LAB-{$year}-";

        $lastCode = Labour::withTrashed()
            ->where(
                'labour_code',
                'like',
                "{$prefix}%"
            )
            ->lockForUpdate()
            ->orderByDesc('labour_code')
            ->value('labour_code');

        $nextSequence = 1;

        if ($lastCode) {
            $lastSequence = (int) Str::afterLast(
                $lastCode,
                '-'
            );

            $nextSequence = $lastSequence + 1;
        }

        do {
            $code = $prefix
                . str_pad(
                    (string) $nextSequence,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

            $exists = Labour::withTrashed()
                ->where('labour_code', $code)
                ->exists();

            $nextSequence++;
        } while ($exists);

        return $code;
    }

    /**
     * Supporting options used by Create and Edit forms.
     */
    private function getFormOptions(): array
    {
        return [
            'genders' => Gender::query()
                ->active()
                ->ordered()
                ->get(),

            'manpowerSources' => ManpowerSource::query()
                ->active()
                ->ordered()
                ->get(),

            'labourCategories' => LabourCategory::query()
    ->where('is_active', true)
    ->orderBy('category_name')
    ->get(),

            'labourTypes' => LabourType::query()
                ->where('status', true)
                ->orderBy('labour_type_name')
                ->get(),

            'skillCategories' => SkillCategory::query()
                ->active()
                ->ordered()
                ->get(),

            'designationRoles' => DesignationRole::query()
                ->active()
                ->with([
                    'labourType',
                    'skillCategory',
                    'defaultShift',
                ])
                ->ordered()
                ->get(),

            'shifts' => Shift::query()
                ->active()
                ->ordered()
                ->get(),

            'contractors' => Contractor::query()
                ->where('status', true)
                ->orderBy('contractor_name')
                ->get(),

            'projects' => Project::query()
    ->active()
    ->orderBy('project_name')
    ->get()
        ];
    }

    /**
     * Supporting options used by the index filters.
     */
    private function getFilterOptions(): array
    {
        return [
            'manpowerSources' => ManpowerSource::query()
                ->active()
                ->ordered()
                ->get(),

            'labourCategories' => LabourCategory::query()
    ->where('is_active', true)
    ->orderBy('category_name')
    ->get(),

            'labourTypes' => LabourType::query()
                ->where('status', true)
                ->orderBy('labour_type_name')
                ->get(),

            'skillCategories' => SkillCategory::query()
                ->active()
                ->ordered()
                ->get(),

            'designationRoles' => DesignationRole::query()
                ->active()
                ->ordered()
                ->get(),

            'contractors' => Contractor::query()
                ->where('status', true)
                ->orderBy('contractor_name')
                ->get(),

            'projects' => Project::query()
    ->active()
    ->orderBy('project_name')
    ->get()
        ];
    }

    /**
     * Standard relationships loaded for Labour views and audit logs.
     */
    private function labourRelationships(): array
    {
        return [
            'gender',
            'manpowerSource',
            'labourCategory',
            'labourType',
            'skillCategory',
            'designationRole',
            'defaultShift',
            'contractor',
            'currentProject',
        ];
    }

    /**
     * Convert Labour data into audit-safe values.
     */
    private function auditValues(Labour $labour): array
    {
        $values = [
            'id' => $labour->id,
            'labour_code' => $labour->labour_code,
            'full_name' => $labour->full_name,
            'mobile' => $labour->mobile,
            'alternate_mobile' => $labour->alternate_mobile,
            'gender' => $labour->gender?->name,
            'date_of_birth' => $labour->date_of_birth?->format(
                'Y-m-d'
            ),
            'identity_type' => $labour->identity_type,
            'identity_number' => $labour->identity_number,
            'manpower_source' => $labour
                ->manpowerSource?->name,
            'labour_category' => $labour
                ->labourCategory?->category_name,
            'labour_type' => $labour
                ->labourType?->labour_type_name,
            'skill_category' => $labour
                ->skillCategory?->name,
            'designation_role' => $labour
                ->designationRole?->name,
            'default_shift' => $labour
                ->defaultShift?->name,
            'contractor' => $labour
                ->contractor?->contractor_name,
            'current_project' => $labour
                ->currentProject?->project_name,
            'joining_date' => $labour->joining_date?->format(
                'Y-m-d'
            ),
            'exit_date' => $labour->exit_date?->format(
                'Y-m-d'
            ),
            'employment_status' => $labour->employment_status,
            'residency_status' => $labour->residency_status,
            'is_active' => $labour->is_active,
            'remarks' => $labour->remarks,
        ];

        if ($this->canViewFinancial()) {
            $values['wage_basis'] = $labour->wage_basis;
            $values['current_daily_rate'] = $labour
                ->getRawOriginal('current_daily_rate');
            $values['current_hourly_rate'] = $labour
                ->getRawOriginal('current_hourly_rate');
            $values['current_monthly_rate'] = $labour
                ->getRawOriginal('current_monthly_rate');
            $values['ot_calculation_type'] = $labour
                ->ot_calculation_type;
            $values['current_ot_rate'] = $labour
                ->getRawOriginal('current_ot_rate');
            $values['ot_multiplier'] = $labour
                ->getRawOriginal('ot_multiplier');
            $values['normal_shift_hours'] = $labour
                ->normal_shift_hours;
        }

        return $values;
    }

    /**
     * Determine whether the current user may view labour financial data.
     */
    private function canViewFinancial(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasPermission(
            'labour_masters.financial_view'
        )
            || $user->hasPermission(
                'labour_masters.financial_manage'
            );
    }

    /**
     * Determine whether the current user may create or update labour rates.
     */
    private function canManageFinancial(): bool
    {
        return auth()->user()?->hasPermission(
            'labour_masters.financial_manage'
        ) ?? false;
    }

    /**
     * Trim nullable strings and convert empty values to null.
     */
    private function nullableTrim(
        mixed $value
    ): ?string {
        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);

        return $value === ''
            ? null
            : $value;
    }
}