<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Activity;
use App\Models\ActivityDivision;
use App\Models\BrandMaster;
use App\Models\MaterialGrade;
use App\Models\MaterialRequirement;
use App\Models\MaterialSpecification;
use App\Models\MaterialType;
use App\Models\Project;
use App\Models\ProjectBlock;
use App\Models\UnitMaster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class MaterialRequirementController extends Controller
{
    /**
     * Display material requirement headers.
     */
    public function index(Request $request): View
    {
        $query = MaterialRequirement::query()
            ->with($this->requirementRelationships());

        if ($request->filled('project_id')) {
            $query->where(
                'project_id',
                $request->integer('project_id')
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->string('status')->toString()
            );
        }

        if ($request->filled('priority')) {
            $query->where(
                'priority',
                $request->string('priority')->toString()
            );
        }

        if ($request->filled('required_date')) {
            $query->whereDate(
                'required_date',
                $request->input('required_date')
            );
        }

        if ($request->filled('search')) {
            $search = trim(
                $request->string('search')->toString()
            );

            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('remarks', 'like', "%{$search}%")
                    ->orWhereHas(
                        'project',
                        fn (Builder $projectQuery) =>
                            $projectQuery
                                ->where(
                                    'project_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'project_code',
                                    'like',
                                    "%{$search}%"
                                )
                    )
                    ->orWhereHas(
                        'items.materialType',
                        fn (Builder $typeQuery) =>
                            $typeQuery->where(
                                'material_type_name',
                                'like',
                                "%{$search}%"
                            )
                    )
                    ->orWhereHas(
                        'items.brand',
                        fn (Builder $brandQuery) =>
                            $brandQuery->where(
                                'brand_name',
                                'like',
                                "%{$search}%"
                            )
                    )
                    ->orWhereHas(
                        'items.specification',
                        fn (Builder $specificationQuery) =>
                            $specificationQuery->where(
                                'specification_name',
                                'like',
                                "%{$search}%"
                            )
                    )
                    ->orWhereHas(
                        'items.grade',
                        fn (Builder $gradeQuery) =>
                            $gradeQuery->where(
                                'grade_name',
                                'like',
                                "%{$search}%"
                            )
                    )
                    ->orWhereHas(
                        'material',
                        fn (Builder $materialQuery) =>
                            $materialQuery->where(
                                'material_name',
                                'like',
                                "%{$search}%"
                            )
                    );
            });
        }

        $requirements = $query
            ->orderByDesc('required_date')
            ->orderByDesc('id')
            ->paginate(7)
            ->withQueryString();

        $projects = $this->availableProjects();

        $draftCount = MaterialRequirement::query()
            ->where('status', 'Draft')
            ->count();

        $submittedCount = MaterialRequirement::query()
            ->where('status', 'Submitted')
            ->count();

        $approvedCount = MaterialRequirement::query()
            ->where('status', 'Approved')
            ->count();

        $urgentCount = MaterialRequirement::query()
            ->where('priority', 'Urgent')
            ->whereIn('status', ['Draft', 'Submitted', 'Approved'])
            ->count();

        return view(
            'material-requirements.index',
            compact(
                'requirements',
                'projects',
                'draftCount',
                'submittedCount',
                'approvedCount',
                'urgentCount'
            )
        );
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view(
            'material-requirements.create',
            $this->formData()
        );
    }

    /**
     * Store one header with multiple requirement items.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequirement($request);

        $this->validateItemRelationships(
            $validated['items']
        );

        try {
            $materialRequirement = DB::transaction(
                function () use ($validated): MaterialRequirement {
                    $materialRequirement = MaterialRequirement::create([
                        'project_id' =>
                            (int) $validated['project_id'],

                        'project_block_id' =>
                            $validated['project_block_id'] ?? null,

                        'required_date' =>
                            $validated['required_date'] ?? null,

                        'priority' =>
                            $validated['priority'],

                        'status' =>
                            'Draft',

                        'remarks' =>
                            $this->nullableTrim(
                                $validated['remarks'] ?? null
                            ),

                        'created_by' =>
                            auth()->id(),

                        /*
                         * Legacy single-material fields remain null
                         * for all new multi-item requirements.
                         */
                        'material_category_id' => null,
                        'material_id' => null,
                        'required_quantity' => null,
                        'fulfilled_quantity' => 0,
                        'unit' => null,
                    ]);

                    foreach (
                        array_values($validated['items'])
                        as $index => $item
                    ) {
                        $materialRequirement->items()->create([
                            'activity_division_id' =>
                                $item['activity_division_id']
                                ?? null,

                            'activity_id' =>
                                $item['activity_id']
                                ?? null,

                            'material_type_id' =>
                                (int) $item['material_type_id'],

                            'brand_master_id' =>
                                $item['brand_master_id']
                                ?? null,

                            'material_specification_id' =>
                                $item['material_specification_id']
                                ?? null,

                            'material_grade_id' =>
                                $item['material_grade_id']
                                ?? null,

                            'required_quantity' =>
                                $item['required_quantity'],

                            'fulfilled_quantity' =>
                                0,

                            'unit_master_id' =>
                                (int) $item['unit_master_id'],

                            'sort_order' =>
                                $index + 1,

                            'remarks' =>
                                $this->nullableTrim(
                                    $item['remarks'] ?? null
                                ),
                        ]);
                    }

                    $materialRequirement->load(
                        $this->requirementRelationships()
                    );

                    AuditHelper::log(
                        'Material Requirements',
                        'Created',
                        'MaterialRequirement',
                        $materialRequirement->id,
                        'Material requirement created with '
                            . $materialRequirement->items->count()
                            . ' material item(s).',
                        null,
                        $this->auditValues($materialRequirement)
                    );

                    return $materialRequirement;
                }
            );

            return redirect()
                ->route(
                    'material-requirements.show',
                    $materialRequirement
                )
                ->with(
                    'success',
                    'Material requirement created successfully as Draft.'
                );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to create the material requirement.'
                );
        }
    }

    /**
     * Display one requirement.
     */
    public function show(
        MaterialRequirement $materialRequirement
    ): View {
        $materialRequirement->load(
            $this->requirementRelationships()
        );

        return view(
            'material-requirements.show',
            compact('materialRequirement')
        );
    }

    /**
     * Show the Draft edit form.
     */
    public function edit(
        MaterialRequirement $materialRequirement
    ): View {
        if ($materialRequirement->status !== 'Draft') {
            abort(
                403,
                'Only Draft material requirements can be edited.'
            );
        }

        $materialRequirement->load(
            $this->requirementRelationships()
        );

        return view(
            'material-requirements.edit',
            array_merge(
                compact('materialRequirement'),
                $this->formData()
            )
        );
    }

    /**
     * Update a Draft header and replace its item rows.
     */
    public function update(
        Request $request,
        MaterialRequirement $materialRequirement
    ): RedirectResponse {
        if ($materialRequirement->status !== 'Draft') {
            abort(
                403,
                'Only Draft material requirements can be updated.'
            );
        }

        $validated = $this->validateRequirement($request);

        $this->validateItemRelationships(
            $validated['items']
        );

        try {
            DB::transaction(
                function () use (
                    $validated,
                    $materialRequirement
                ): void {
                    $materialRequirement->load(
                        $this->requirementRelationships()
                    );

                    $oldValues = $this->auditValues(
                        $materialRequirement
                    );

                    $materialRequirement->update([
                        'project_id' =>
                            (int) $validated['project_id'],

                        'project_block_id' =>
                            $validated['project_block_id'] ?? null,

                        'required_date' =>
                            $validated['required_date'] ?? null,

                        'priority' =>
                            $validated['priority'],

                        'remarks' =>
                            $this->nullableTrim(
                                $validated['remarks'] ?? null
                            ),
                    ]);

                    $materialRequirement->items()->delete();

                    foreach (
                        array_values($validated['items'])
                        as $index => $item
                    ) {
                        $materialRequirement->items()->create([
                            'activity_division_id' =>
                                $item['activity_division_id']
                                ?? null,

                            'activity_id' =>
                                $item['activity_id']
                                ?? null,

                            'material_type_id' =>
                                (int) $item['material_type_id'],

                            'brand_master_id' =>
                                $item['brand_master_id']
                                ?? null,

                            'material_specification_id' =>
                                $item['material_specification_id']
                                ?? null,

                            'material_grade_id' =>
                                $item['material_grade_id']
                                ?? null,

                            'required_quantity' =>
                                $item['required_quantity'],

                            'fulfilled_quantity' =>
                                $item['fulfilled_quantity']
                                ?? 0,

                            'unit_master_id' =>
                                (int) $item['unit_master_id'],

                            'sort_order' =>
                                $index + 1,

                            'remarks' =>
                                $this->nullableTrim(
                                    $item['remarks'] ?? null
                                ),
                        ]);
                    }

                    $materialRequirement->load(
                        $this->requirementRelationships()
                    );

                    AuditHelper::log(
                        'Material Requirements',
                        'Updated',
                        'MaterialRequirement',
                        $materialRequirement->id,
                        'Material requirement updated.',
                        $oldValues,
                        $this->auditValues($materialRequirement)
                    );
                }
            );

            return redirect()
                ->route(
                    'material-requirements.show',
                    $materialRequirement
                )
                ->with(
                    'success',
                    'Material requirement updated successfully.'
                );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to update the material requirement.'
                );
        }
    }

    /**
     * Submit a Draft requirement.
     */
    public function submit(
        MaterialRequirement $materialRequirement
    ): RedirectResponse {
        if ($materialRequirement->status !== 'Draft') {
            return back()->with(
                'error',
                'Only Draft material requirements can be submitted.'
            );
        }

        if (
            ! $materialRequirement->items()->exists()
            && empty($materialRequirement->material_id)
        ) {
            return back()->with(
                'error',
                'Add at least one material before submission.'
            );
        }

        $oldValues = [
            'status' =>
                $materialRequirement->status,
        ];

        $materialRequirement->update([
            'status' =>
                'Submitted',
        ]);

        $materialRequirement->refresh();

        AuditHelper::log(
            'Material Requirements',
            'Submitted',
            'MaterialRequirement',
            $materialRequirement->id,
            'Material requirement submitted for approval.',
            $oldValues,
            [
                'status' =>
                    $materialRequirement->status,
            ]
        );

        return back()->with(
            'success',
            'Material requirement submitted successfully.'
        );
    }

    /**
     * Approve a Submitted requirement.
     */
    public function approve(
        MaterialRequirement $materialRequirement
    ): RedirectResponse {
        if ($materialRequirement->status !== 'Submitted') {
            return back()->with(
                'error',
                'Only Submitted material requirements can be approved.'
            );
        }

        $roleName = auth()->user()->role?->name;

        if (! in_array($roleName, ['Admin', 'PMO', 'DGM'], true)) {
            abort(403);
        }

        $oldValues = [
            'status' =>
                $materialRequirement->status,

            'approved_by' =>
                $materialRequirement->approved_by,

            'approved_at' =>
                $materialRequirement->approved_at,
        ];

        $materialRequirement->update([
            'status' =>
                'Approved',

            'approved_by' =>
                auth()->id(),

            'approved_at' =>
                now(),
        ]);

        $materialRequirement->refresh();

        AuditHelper::log(
            'Material Requirements',
            'Approved',
            'MaterialRequirement',
            $materialRequirement->id,
            'Material requirement approved.',
            $oldValues,
            [
                'status' =>
                    $materialRequirement->status,

                'approved_by' =>
                    $materialRequirement->approved_by,

                'approved_at' =>
                    $materialRequirement
                        ->approved_at
                        ?->toDateTimeString(),
            ]
        );

        return back()->with(
            'success',
            'Material requirement approved successfully.'
        );
    }

    /**
     * Delete a Draft requirement.
     */
    public function destroy(
        MaterialRequirement $materialRequirement
    ): RedirectResponse {
        if ($materialRequirement->status !== 'Draft') {
            return back()->with(
                'error',
                'Only Draft material requirements can be deleted.'
            );
        }

        $materialRequirement->load(
            $this->requirementRelationships()
        );

        $oldValues = $this->auditValues(
            $materialRequirement
        );

        DB::transaction(
            function () use (
                $materialRequirement,
                $oldValues
            ): void {
                $requirementId = $materialRequirement->id;

                $materialRequirement->delete();

                AuditHelper::log(
                    'Material Requirements',
                    'Deleted',
                    'MaterialRequirement',
                    $requirementId,
                    'Draft material requirement deleted.',
                    $oldValues,
                    null
                );
            }
        );

        return redirect()
            ->route('material-requirements.index')
            ->with(
                'success',
                'Draft material requirement deleted successfully.'
            );
    }

    /**
     * Validate header and item rows.
     */
    private function validateRequirement(
        Request $request
    ): array {
        return $request->validate([
            'project_id' => [
                'required',
                'integer',
                'exists:projects,id',
            ],

            'project_block_id' => [
                'nullable',
                'integer',
                'exists:project_blocks,id',
            ],

            'required_date' => [
                'nullable',
                'date',
            ],

            'priority' => [
                'required',
                'in:Low,Normal,High,Urgent',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],

            'items.*.activity_division_id' => [
                'nullable',
                'integer',
                'exists:activity_divisions,id',
            ],

            'items.*.activity_id' => [
                'nullable',
                'integer',
                'exists:activities,id',
            ],

            'items.*.material_type_id' => [
                'required',
                'integer',
                'exists:material_types,id',
            ],

            'items.*.brand_master_id' => [
                'nullable',
                'integer',
                'exists:brand_masters,id',
            ],

            'items.*.material_specification_id' => [
                'nullable',
                'integer',
                'exists:material_specifications,id',
            ],

            'items.*.material_grade_id' => [
                'nullable',
                'integer',
                'exists:material_grades,id',
            ],

            'items.*.required_quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'items.*.fulfilled_quantity' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'items.*.unit_master_id' => [
                'required',
                'integer',
                'exists:unit_masters,id',
            ],

            'items.*.remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'items.required' =>
                'Add at least one material item.',

            'items.min' =>
                'Add at least one material item.',

            'items.*.material_type_id.required' =>
                'Select a Material Type for every row.',

            'items.*.required_quantity.gt' =>
                'Required quantity must be greater than zero.',

            'items.*.unit_master_id.required' =>
                'Every material row must have a unit.',
        ]);
    }

    /**
     * Confirm dependent row selections belong to Material Type.
     */
    private function validateItemRelationships(
        array $items
    ): void {
        $errors = [];

        foreach (array_values($items) as $index => $item) {
            $rowNumber = $index + 1;
            $materialTypeId = (int) $item['material_type_id'];

            $materialType = MaterialType::query()
                ->find($materialTypeId);

            if (! $materialType) {
                continue;
            }

            if (
                (int) $item['unit_master_id']
                !== (int) $materialType->unit_master_id
            ) {
                $errors["items.{$index}.unit_master_id"][] =
                    "Row {$rowNumber}: the unit does not match the selected Material Type.";
            }

            if (! empty($item['brand_master_id'])) {
                $brandValid = BrandMaster::query()
                    ->whereKey($item['brand_master_id'])
                    ->where(
                        'material_type_id',
                        $materialTypeId
                    )
                    ->where('is_active', true)
                    ->exists();

                if (! $brandValid) {
                    $errors[
                        "items.{$index}.brand_master_id"
                    ][] =
                        "Row {$rowNumber}: the selected Brand does not belong to the selected Material Type.";
                }
            }

            if (
                ! empty(
                    $item['material_specification_id']
                )
            ) {
                $specificationValid =
                    MaterialSpecification::query()
                        ->whereKey(
                            $item[
                                'material_specification_id'
                            ]
                        )
                        ->where(
                            'material_type_id',
                            $materialTypeId
                        )
                        ->where('is_active', true)
                        ->exists();

                if (! $specificationValid) {
                    $errors[
                        "items.{$index}.material_specification_id"
                    ][] =
                        "Row {$rowNumber}: the selected Specification does not belong to the selected Material Type.";
                }
            }

            if (! empty($item['material_grade_id'])) {
                $gradeValid = MaterialGrade::query()
                    ->whereKey($item['material_grade_id'])
                    ->where(
                        'material_type_id',
                        $materialTypeId
                    )
                    ->where('is_active', true)
                    ->exists();

                if (! $gradeValid) {
                    $errors[
                        "items.{$index}.material_grade_id"
                    ][] =
                        "Row {$rowNumber}: the selected Grade does not belong to the selected Material Type.";
                }
            }

            if (
                ! empty($item['activity_id'])
                && ! empty($item['activity_division_id'])
            ) {
                $activityValid = Activity::query()
                    ->whereKey($item['activity_id'])
                    ->where(
                        'activity_division_id',
                        $item['activity_division_id']
                    )
                    ->exists();

                if (! $activityValid) {
                    $errors[
                        "items.{$index}.activity_id"
                    ][] =
                        "Row {$rowNumber}: the selected Activity does not belong to the selected Activity Division.";
                }
            }

            $requiredQuantity = (float) (
                $item['required_quantity'] ?? 0
            );

            $fulfilledQuantity = (float) (
                $item['fulfilled_quantity'] ?? 0
            );

            if ($fulfilledQuantity > $requiredQuantity) {
                $errors[
                    "items.{$index}.fulfilled_quantity"
                ][] =
                    "Row {$rowNumber}: fulfilled quantity cannot exceed required quantity.";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(
                $errors
            );
        }
    }

    /**
     * Data required by create and edit views.
     */
    private function formData(): array
    {
        $materialTypes = MaterialType::query()
            ->with('unit')
            ->where('is_active', true)
            ->orderBy('material_group')
            ->orderBy('sequence')
            ->orderBy('material_type_name')
            ->get();

        return [
            'projects' =>
                $this->availableProjects(),

            'projectBlocks' => ProjectBlock::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),

            'activityDivisions' =>
                ActivityDivision::query()
                    ->where('is_active', true)
                    ->orderBy('sequence')
                    ->orderBy('name')
                    ->get(),

            'activities' => Activity::query()
                ->where('is_active', true)
                ->orderBy('activity_division_id')
                ->orderBy('activity_name')
                ->get(),

            'materialTypes' =>
                $materialTypes,

            'materialGroups' => $materialTypes
                ->pluck('material_group')
                ->filter()
                ->unique()
                ->sort()
                ->values(),

            'brands' => BrandMaster::query()
                ->where('is_active', true)
                ->whereNotNull('material_type_id')
                ->orderBy('material_type_id')
                ->orderBy('sequence')
                ->orderBy('brand_name')
                ->get(),

            'specifications' =>
                MaterialSpecification::query()
                    ->where('is_active', true)
                    ->whereNotNull('material_type_id')
                    ->orderBy('material_type_id')
                    ->orderBy('sequence')
                    ->orderBy('specification_name')
                    ->get(),

            'grades' => MaterialGrade::query()
                ->where('is_active', true)
                ->orderBy('material_type_id')
                ->orderBy('sequence')
                ->orderBy('grade_name')
                ->get(),

            'units' => UnitMaster::query()
                ->where('is_active', true)
                ->orderBy('unit_name')
                ->get(),
        ];
    }

    /**
     * Projects available to the logged-in user.
     */
    private function availableProjects()
    {
        $user = auth()->user();

        if (
            in_array(
                $user->role?->name,
                ['Admin', 'PMO', 'DGM'],
                true
            )
        ) {
            return Project::query()
                ->where('status', 'Active')
                ->orderBy('project_name')
                ->get();
        }

        return $user->projects()
            ->where('status', 'Active')
            ->orderBy('project_name')
            ->get();
    }

    /**
     * Relationships used by list, show, edit and audit.
     */
    private function requirementRelationships(): array
    {
        return [
            'project',
            'block',
            'creator',
            'approver',

            'items.activityDivision',
            'items.activity',
            'items.materialType.unit',
            'items.brand',
            'items.specification',
            'items.grade',
            'items.unit',

            // Legacy support.
            'materialCategory',
            'material',
        ];
    }

    /**
     * Values written to audit trail.
     */
    private function auditValues(
        MaterialRequirement $materialRequirement
    ): array {
        return [
            'id' =>
                $materialRequirement->id,

            'project_id' =>
                $materialRequirement->project_id,

            'project_block_id' =>
                $materialRequirement->project_block_id,

            'required_date' =>
                $materialRequirement
                    ->required_date
                    ?->format('Y-m-d'),

            'priority' =>
                $materialRequirement->priority,

            'status' =>
                $materialRequirement->status,

            'remarks' =>
                $materialRequirement->remarks,

            'created_by' =>
                $materialRequirement->created_by,

            'approved_by' =>
                $materialRequirement->approved_by,

            'approved_at' =>
                $materialRequirement
                    ->approved_at
                    ?->toDateTimeString(),

            'items' => $materialRequirement->items
                ->map(
                    fn ($item) => [
                        'id' =>
                            $item->id,

                        'activity_division_id' =>
                            $item->activity_division_id,

                        'activity_id' =>
                            $item->activity_id,

                        'material_type_id' =>
                            $item->material_type_id,

                        'material_type_name' =>
                            $item->materialType
                                ?->material_type_name,

                        'brand_master_id' =>
                            $item->brand_master_id,

                        'brand_name' =>
                            $item->brand?->brand_name,

                        'material_specification_id' =>
                            $item->material_specification_id,

                        'specification_name' =>
                            $item->specification
                                ?->specification_name,

                        'material_grade_id' =>
                            $item->material_grade_id,

                        'grade_name' =>
                            $item->grade?->grade_name,

                        'required_quantity' =>
                            $item->required_quantity,

                        'fulfilled_quantity' =>
                            $item->fulfilled_quantity,

                        'unit_master_id' =>
                            $item->unit_master_id,

                        'unit_name' =>
                            $item->unit?->unit_name,

                        'sort_order' =>
                            $item->sort_order,

                        'remarks' =>
                            $item->remarks,
                    ]
                )
                ->values()
                ->all(),
        ];
    }

    private function nullableTrim(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === ''
            ? null
            : $trimmed;
    }
}
