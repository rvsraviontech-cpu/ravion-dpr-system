<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\DesignationRole;
use App\Models\LabourType;
use App\Models\Shift;
use App\Models\SkillCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DesignationRoleController extends Controller
{
    public function index(Request $request): View
    {
        $query = DesignationRole::query()
            ->with([
                'labourType',
                'skillCategory',
                'defaultShift',
            ]);

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereHas(
                        'labourType',
                        fn (Builder $tradeQuery) => $tradeQuery
                            ->where(
                                'labour_type_name',
                                'like',
                                "%{$search}%"
                            )
                    )
                    ->orWhereHas(
                        'skillCategory',
                        fn (Builder $skillQuery) => $skillQuery
                            ->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                    )
                    ->orWhereHas(
                        'defaultShift',
                        fn (Builder $shiftQuery) => $shiftQuery
                            ->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                    );
            });
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

        if ($request->filled('default_shift_id')) {
            $query->where(
                'default_shift_id',
                $request->integer('default_shift_id')
            );
        }

        if ($request->filled('status')) {
            match ($request->input('status')) {
                'active' => $query->where('is_active', true),
                'inactive' => $query->where('is_active', false),
                default => null,
            };
        }

        $designationRoles = $query
            ->ordered()
            ->paginate(config('rds.pagination.per_page', 15))
            ->withQueryString();

        return view(
            'designation-roles.index',
            array_merge(
                [
                    'designationRoles' => $designationRoles,
                    'canViewFinancial' => $this->canViewFinancial(),
                ],
                $this->getFormOptions()
            )
        );
    }

    public function create(): View
    {
        return view(
            'designation-roles.create',
            array_merge(
                [
                    'canManageFinancial' => $this->canManageFinancial(),
                ],
                $this->getFormOptions()
            )
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDesignationRole($request);

        $data = $this->prepareDesignationRoleData(
            $request,
            $validated
        );

        $data['is_system'] = false;

        $designationRole = DesignationRole::create($data);

        $designationRole->load([
            'labourType',
            'skillCategory',
            'defaultShift',
        ]);

        AuditHelper::log(
            'Designation Role Master',
            'Created',
            DesignationRole::class,
            $designationRole->id,
            "Designation role '{$designationRole->name}' was created.",
            null,
            $this->auditValues($designationRole)
        );

        return redirect()
            ->route('designation-roles.index')
            ->with(
                'success',
                'Designation role created successfully.'
            );
    }

    public function show(
        DesignationRole $designationRole
    ): View {
        $designationRole->load([
            'labourType',
            'skillCategory',
            'defaultShift',
        ]);

        return view(
            'designation-roles.show',
            [
                'designationRole' => $designationRole,
                'canViewFinancial' => $this->canViewFinancial(),
            ]
        );
    }

    /**
 * Show the edit form.
 *
 * System designation roles may be edited by authorized users.
 * Their system-record identity remains unchanged.
 */
public function edit(
    DesignationRole $designationRole
): View {
    $designationRole->load([
        'labourType',
        'skillCategory',
        'defaultShift',
    ]);

    return view(
        'designation-roles.edit',
        array_merge(
            [
                'designationRole' => $designationRole,
                'canManageFinancial' => $this->canManageFinancial(),
            ],
            $this->getFormOptions()
        )
    );
}

    /**
 * Update the specified designation role.
 *
 * Both custom and system records may be corrected by authorized users.
 * The original record type is always preserved.
 */
public function update(
    Request $request,
    DesignationRole $designationRole
): RedirectResponse {
    $designationRole->load([
        'labourType',
        'skillCategory',
        'defaultShift',
    ]);

    $oldValues = $this->auditValues($designationRole);

    $validated = $this->validateDesignationRole(
        $request,
        $designationRole
    );

    $data = $this->prepareDesignationRoleData(
        $request,
        $validated,
        $designationRole
    );

    /*
     * Never allow an update request to change whether the
     * record is a system record or a custom record.
     */
    unset($data['is_system']);

    $designationRole->update($data);

    $designationRole->refresh();

    $designationRole->load([
        'labourType',
        'skillCategory',
        'defaultShift',
    ]);

    AuditHelper::log(
        'Designation Role Master',
        'Updated',
        DesignationRole::class,
        $designationRole->id,
        "Designation role '{$designationRole->name}' was updated.",
        $oldValues,
        $this->auditValues($designationRole)
    );

    return redirect()
        ->route('designation-roles.show', $designationRole)
        ->with(
            'success',
            'Designation role updated successfully.'
        );
}

    /**
 * Activate or deactivate a custom designation role.
 *
 * System records remain protected from status changes,
 * although their mappings and defaults may be edited.
 */
public function toggleStatus(
    DesignationRole $designationRole
): RedirectResponse {
    if ($designationRole->is_system) {
        return back()->with(
            'error',
            'System designation role records cannot be activated or deactivated.'
        );
    }

    $designationRole->load([
        'labourType',
        'skillCategory',
        'defaultShift',
    ]);

    $oldValues = $this->auditValues($designationRole);

    $designationRole->update([
        'is_active' => ! $designationRole->is_active,
    ]);

    $designationRole->refresh();

    $designationRole->load([
        'labourType',
        'skillCategory',
        'defaultShift',
    ]);

    $action = $designationRole->is_active
        ? 'Activated'
        : 'Deactivated';

    AuditHelper::log(
        'Designation Role Master',
        $action,
        DesignationRole::class,
        $designationRole->id,
        "Designation role '{$designationRole->name}' was {$action}.",
        $oldValues,
        $this->auditValues($designationRole)
    );

    return back()->with(
        'success',
        $designationRole->is_active
            ? 'Designation role activated successfully.'
            : 'Designation role deactivated successfully.'
    );
}

    private function validateDesignationRole(
        Request $request,
        ?DesignationRole $designationRole = null
    ): array {
        $labourTypeId = $request->filled('labour_type_id')
            ? $request->integer('labour_type_id')
            : null;

        $uniqueNameRule = Rule::unique(
            'designation_roles',
            'name'
        )
            ->where(function ($query) use ($labourTypeId) {
                return $labourTypeId
                    ? $query->where('labour_type_id', $labourTypeId)
                    : $query->whereNull('labour_type_id');
            })
            ->ignore($designationRole?->id);

        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('designation_roles', 'code')
                    ->ignore($designationRole?->id),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
                $uniqueNameRule,
            ],

            'labour_type_id' => [
                'required',
                'integer',
                'exists:labour_types,id',
            ],

            'skill_category_id' => [
                'required',
                'integer',
                'exists:skill_categories,id',
            ],

            'default_shift_id' => [
                'nullable',
                'integer',
                'exists:shifts,id',
            ],

            'default_normal_shift_hours' => [
                'required',
                'numeric',
                'min:0.25',
                'max:24',
            ],

            'default_wage_basis' => [
                'required',
                Rule::in([
                    'daily',
                    'hourly',
                    'monthly',
                    'contractor_managed',
                ]),
            ],

            'default_daily_rate' => [
                Rule::requiredIf(
                    fn (): bool => $request->input(
                        'default_wage_basis'
                    ) === 'daily'
                ),
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'default_hourly_rate' => [
                Rule::requiredIf(
                    fn (): bool => $request->input(
                        'default_wage_basis'
                    ) === 'hourly'
                ),
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'default_monthly_rate' => [
                Rule::requiredIf(
                    fn (): bool => $request->input(
                        'default_wage_basis'
                    ) === 'monthly'
                ),
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'default_ot_calculation_type' => [
                'required',
                Rule::in([
                    'fixed_rate',
                    'multiplier',
                    'not_applicable',
                ]),
            ],

            'default_ot_rate' => [
                Rule::requiredIf(
                    fn (): bool => $request->input(
                        'default_ot_calculation_type'
                    ) === 'fixed_rate'
                ),
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'default_ot_multiplier' => [
                Rule::requiredIf(
                    fn (): bool => $request->input(
                        'default_ot_calculation_type'
                    ) === 'multiplier'
                ),
                'nullable',
                'numeric',
                'min:0',
                'max:10',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'name.unique' =>
                'This designation role already exists for the selected Trade / Manpower Category.',
        ]);
    }

    private function prepareDesignationRoleData(
        Request $request,
        array $validated,
        ?DesignationRole $designationRole = null
    ): array {
        $data = $validated;

        $data['code'] = strtoupper(trim($data['code']));
        $data['name'] = trim($data['name']);

        $data['labour_type_id'] = (int) $data['labour_type_id'];
        $data['skill_category_id'] = (int) $data['skill_category_id'];

        $data['default_shift_id'] = $request->filled(
            'default_shift_id'
        )
            ? $request->integer('default_shift_id')
            : null;

        $data['default_normal_shift_hours'] = (float) (
            $data['default_normal_shift_hours'] ?? 8
        );

        $data['sort_order'] = (int) $data['sort_order'];
        $data['is_active'] = $request->boolean('is_active');
        $data['remarks'] = $this->nullableTrim(
            $data['remarks'] ?? null
        );

        return $this->prepareFinancialDefaults(
            $data,
            $designationRole
        );
    }

    private function prepareFinancialDefaults(
        array $data,
        ?DesignationRole $designationRole = null
    ): array {
        $financialFields = [
            'default_wage_basis',
            'default_daily_rate',
            'default_hourly_rate',
            'default_monthly_rate',
            'default_ot_calculation_type',
            'default_ot_rate',
            'default_ot_multiplier',
        ];

        if (! $this->canManageFinancial()) {
            foreach ($financialFields as $field) {
                unset($data[$field]);
            }

            if (! $designationRole) {
                $data['default_wage_basis'] = 'contractor_managed';
                $data['default_daily_rate'] = null;
                $data['default_hourly_rate'] = null;
                $data['default_monthly_rate'] = null;
                $data['default_ot_calculation_type'] = 'not_applicable';
                $data['default_ot_rate'] = null;
                $data['default_ot_multiplier'] = null;
            }

            return $data;
        }

        $wageBasis = $data['default_wage_basis'];

        if ($wageBasis !== 'daily') {
            $data['default_daily_rate'] = null;
        }

        if ($wageBasis !== 'hourly') {
            $data['default_hourly_rate'] = null;
        }

        if ($wageBasis !== 'monthly') {
            $data['default_monthly_rate'] = null;
        }

        if ($wageBasis === 'contractor_managed') {
            $data['default_daily_rate'] = null;
            $data['default_hourly_rate'] = null;
            $data['default_monthly_rate'] = null;
        }

        $otType = $data['default_ot_calculation_type'];

        if ($otType !== 'fixed_rate') {
            $data['default_ot_rate'] = null;
        }

        if ($otType !== 'multiplier') {
            $data['default_ot_multiplier'] = null;
        }

        if ($otType === 'not_applicable') {
            $data['default_ot_rate'] = null;
            $data['default_ot_multiplier'] = null;
        }

        return $data;
    }

    private function getFormOptions(): array
    {
        return [
            'labourTypes' => LabourType::query()
                ->where('status', true)
                ->orderBy('labour_type_name')
                ->get(),

            'skillCategories' => SkillCategory::query()
                ->active()
                ->ordered()
                ->get(),

            'shifts' => Shift::query()
                ->active()
                ->ordered()
                ->get(),
        ];
    }

    private function auditValues(
        DesignationRole $designationRole
    ): array {
        $values = [
            'id' => $designationRole->id,
            'code' => $designationRole->code,
            'name' => $designationRole->name,
            'labour_type' => $designationRole
                ->labourType?->labour_type_name,
            'skill_category' => $designationRole
                ->skillCategory?->name,
            'default_shift' => $designationRole
                ->defaultShift?->name,
            'default_normal_shift_hours' =>
                $designationRole->default_normal_shift_hours,
            'sort_order' => $designationRole->sort_order,
            'is_active' => $designationRole->is_active,
            'is_system' => $designationRole->is_system,
            'remarks' => $designationRole->remarks,
        ];

        if ($this->canViewFinancial()) {
            $values['default_wage_basis'] =
                $designationRole->default_wage_basis;

            $values['default_daily_rate'] =
                $designationRole->getRawOriginal(
                    'default_daily_rate'
                );

            $values['default_hourly_rate'] =
                $designationRole->getRawOriginal(
                    'default_hourly_rate'
                );

            $values['default_monthly_rate'] =
                $designationRole->getRawOriginal(
                    'default_monthly_rate'
                );

            $values['default_ot_calculation_type'] =
                $designationRole->default_ot_calculation_type;

            $values['default_ot_rate'] =
                $designationRole->getRawOriginal(
                    'default_ot_rate'
                );

            $values['default_ot_multiplier'] =
                $designationRole->getRawOriginal(
                    'default_ot_multiplier'
                );
        }

        return $values;
    }

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

    private function canManageFinancial(): bool
    {
        return auth()->user()?->hasPermission(
            'labour_masters.financial_manage'
        ) ?? false;
    }

    private function nullableTrim(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}