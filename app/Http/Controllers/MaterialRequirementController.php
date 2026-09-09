<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\MaterialRequirement;
use App\Models\MaterialSpecification;
use App\Models\MaterialType;
use App\Models\Project;
use App\Models\ProjectBlock;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class MaterialRequirementController extends Controller
{
    public function index(Request $request): View
    {
        $query = MaterialRequirement::query()
            ->with($this->requirementRelationships());

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->integer('project_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->string('priority')->toString());
        }

        if ($request->filled('required_date')) {
            $query->whereDate('required_date', $request->input('required_date'));
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('remarks', 'like', "%{$search}%")
                    ->orWhereHas('project', fn (Builder $q) => $q
                        ->where('project_name', 'like', "%{$search}%")
                        ->orWhere('project_code', 'like', "%{$search}%"))
                    ->orWhereHas('items.materialType', fn (Builder $q) => $q
                        ->where('material_type_name', 'like', "%{$search}%")
                        ->orWhere('material_type_code', 'like', "%{$search}%")
                        ->orWhere('catalogue_source_code', 'like', "%{$search}%"))
                    ->orWhereHas('items', fn (Builder $q) => $q
                        ->where('specification_text', 'like', "%{$search}%"))
                    ->orWhereHas('items.brand', fn (Builder $q) => $q
                        ->where('brand_name', 'like', "%{$search}%"))
                    ->orWhereHas('items.specification', fn (Builder $q) => $q
                        ->where('specification_name', 'like', "%{$search}%"))
                    ->orWhereHas('material', fn (Builder $q) => $q
                        ->where('material_name', 'like', "%{$search}%"));
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

        return view('material-requirements.index', compact(
            'requirements',
            'projects',
            'draftCount',
            'submittedCount',
            'approvedCount',
            'urgentCount'
        ));
    }

    public function create(): View
    {
        return view('material-requirements.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequirement($request);
        $this->validateItemRelationships($validated['items']);

        try {
            $requirement = DB::transaction(function () use ($validated): MaterialRequirement {
                $requirement = MaterialRequirement::create([
                    'project_id' => (int) $validated['project_id'],
                    'project_block_id' => $validated['project_block_id'] ?? null,
                    'required_date' => $validated['required_date'] ?? null,
                    'priority' => $validated['priority'],
                    'status' => 'Draft',
                    'remarks' => $this->nullableTrim($validated['remarks'] ?? null),
                    'created_by' => auth()->id(),

                    // Legacy single-material fields are intentionally unused for V2.
                    'material_category_id' => null,
                    'material_id' => null,
                    'required_quantity' => null,
                    'fulfilled_quantity' => 0,
                    'unit' => null,
                ]);

                foreach (array_values($validated['items']) as $index => $item) {
                    $requirement->items()->create([
                        'activity_division_id' => null,
                        'activity_id' => null,
                        'material_type_id' => (int) $item['material_type_id'],
                        'brand_master_id' => $item['brand_master_id'] ?? null,
                        'material_specification_id' => $item['material_specification_id'] ?? null,
                        'specification_text' => $this->nullableTrim($item['specification_text'] ?? null),
                        'material_grade_id' => null,
                        'required_quantity' => $item['required_quantity'],
                        'fulfilled_quantity' => 0,
                        'unit_master_id' => (int) $item['unit_master_id'],
                        'sort_order' => $index + 1,
                        'remarks' => $this->nullableTrim($item['remarks'] ?? null),
                    ]);
                }

                $requirement->load($this->requirementRelationships());

                AuditHelper::log(
                    'Material Requirements',
                    'Created',
                    'MaterialRequirement',
                    $requirement->id,
                    'Material requirement created with '.$requirement->items->count().' product item(s).',
                    null,
                    $this->auditValues($requirement)
                );

                return $requirement;
            });

            return redirect()
                ->route('material-requirements.show', $requirement)
                ->with('success', 'Material requirement created successfully as Draft.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Unable to create the material requirement.');
        }
    }

    public function show(MaterialRequirement $materialRequirement): View
    {
        $materialRequirement->load($this->requirementRelationships());

        return view('material-requirements.show', compact('materialRequirement'));
    }

    public function exportPdf(MaterialRequirement $materialRequirement)
    {
        $materialRequirement->load($this->requirementRelationships());

        $projectCode = Str::slug(
            $materialRequirement->project?->project_code
                ?: $materialRequirement->project?->project_name
                ?: 'project'
        );

        $date = $materialRequirement->required_date?->format('Y-m-d')
            ?: $materialRequirement->created_at?->format('Y-m-d')
            ?: now()->format('Y-m-d');

        $fileName = sprintf(
            'Material-Requirement-%s-%s-MR-%04d.pdf',
            $projectCode,
            $date,
            $materialRequirement->id
        );

        return Pdf::loadView(
            'material-requirements.pdf',
            compact('materialRequirement')
        )
            ->setPaper('a4', 'portrait')
            ->download($fileName);
    }

    public function edit(MaterialRequirement $materialRequirement): View
    {
        if ($materialRequirement->status !== 'Draft') {
            abort(403, 'Only Draft material requirements can be edited.');
        }

        $materialRequirement->load($this->requirementRelationships());

        return view(
            'material-requirements.edit',
            array_merge(
                compact('materialRequirement'),
                $this->formData($materialRequirement)
            )
        );
    }

    public function update(
        Request $request,
        MaterialRequirement $materialRequirement
    ): RedirectResponse {
        if ($materialRequirement->status !== 'Draft') {
            abort(403, 'Only Draft material requirements can be updated.');
        }

        $validated = $this->validateRequirement($request);
        $this->validateItemRelationships($validated['items'], $materialRequirement);

        try {
            DB::transaction(function () use ($validated, $materialRequirement): void {
                $materialRequirement->load($this->requirementRelationships());

                $oldValues = $this->auditValues($materialRequirement);
                $existingItems = $materialRequirement->items->keyBy('id');

                $materialRequirement->update([
                    'project_id' => (int) $validated['project_id'],
                    'project_block_id' => $validated['project_block_id'] ?? null,
                    'required_date' => $validated['required_date'] ?? null,
                    'priority' => $validated['priority'],
                    'remarks' => $this->nullableTrim($validated['remarks'] ?? null),
                ]);

                $materialRequirement->items()->delete();

                foreach (array_values($validated['items']) as $index => $item) {
                    $existingItem = ! empty($item['id'])
                        ? $existingItems->get((int) $item['id'])
                        : null;

                    $materialRequirement->items()->create([
                        'activity_division_id' => $existingItem?->activity_division_id,
                        'activity_id' => $existingItem?->activity_id,
                        'material_type_id' => (int) $item['material_type_id'],
                        'brand_master_id' => $item['brand_master_id'] ?? null,
                        'material_specification_id' => $item['material_specification_id'] ?? null,
                        'specification_text' => $this->nullableTrim($item['specification_text'] ?? null),
                        'material_grade_id' => $existingItem?->material_grade_id,
                        'required_quantity' => $item['required_quantity'],

                        // Fulfilment is system-controlled.
                        'fulfilled_quantity' => $existingItem?->fulfilled_quantity ?? 0,

                        'unit_master_id' => (int) $item['unit_master_id'],
                        'sort_order' => $index + 1,
                        'remarks' => $this->nullableTrim($item['remarks'] ?? null),
                    ]);
                }

                $materialRequirement->load($this->requirementRelationships());

                AuditHelper::log(
                    'Material Requirements',
                    'Updated',
                    'MaterialRequirement',
                    $materialRequirement->id,
                    'Material requirement updated.',
                    $oldValues,
                    $this->auditValues($materialRequirement)
                );
            });

            return redirect()
                ->route('material-requirements.show', $materialRequirement)
                ->with('success', 'Material requirement updated successfully.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Unable to update the material requirement.');
        }
    }

    public function submit(MaterialRequirement $materialRequirement): RedirectResponse
    {
        if ($materialRequirement->status !== 'Draft') {
            return back()->with('error', 'Only Draft material requirements can be submitted.');
        }

        if (
            ! $materialRequirement->items()->exists()
            && empty($materialRequirement->material_id)
        ) {
            return back()->with('error', 'Add at least one Product before submission.');
        }

        $oldValues = ['status' => $materialRequirement->status];

        $materialRequirement->update(['status' => 'Submitted']);
        $materialRequirement->refresh();

        AuditHelper::log(
            'Material Requirements',
            'Submitted',
            'MaterialRequirement',
            $materialRequirement->id,
            'Material requirement submitted for approval.',
            $oldValues,
            ['status' => $materialRequirement->status]
        );

        return back()->with('success', 'Material requirement submitted successfully.');
    }

    public function approve(MaterialRequirement $materialRequirement): RedirectResponse
    {
        if ($materialRequirement->status !== 'Submitted') {
            return back()->with('error', 'Only Submitted material requirements can be approved.');
        }

        if (! in_array(auth()->user()->role?->name, ['Admin', 'PMO', 'DGM'], true)) {
            abort(403);
        }

        $oldValues = [
            'status' => $materialRequirement->status,
            'approved_by' => $materialRequirement->approved_by,
            'approved_at' => $materialRequirement->approved_at,
        ];

        $materialRequirement->update([
            'status' => 'Approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
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
                'status' => $materialRequirement->status,
                'approved_by' => $materialRequirement->approved_by,
                'approved_at' => $materialRequirement->approved_at?->toDateTimeString(),
            ]
        );

        return back()->with('success', 'Material requirement approved successfully.');
    }

    public function destroy(MaterialRequirement $materialRequirement): RedirectResponse
    {
        if ($materialRequirement->status !== 'Draft') {
            return back()->with('error', 'Only Draft material requirements can be deleted.');
        }

        $materialRequirement->load($this->requirementRelationships());
        $oldValues = $this->auditValues($materialRequirement);

        DB::transaction(function () use ($materialRequirement, $oldValues): void {
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
        });

        return redirect()
            ->route('material-requirements.index')
            ->with('success', 'Draft material requirement deleted successfully.');
    }

    private function validateRequirement(Request $request): array
    {
        return $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'project_block_id' => ['nullable', 'integer', 'exists:project_blocks,id'],
            'required_date' => ['nullable', 'date'],
            'priority' => ['required', 'in:Low,Normal,High,Urgent'],
            'remarks' => ['nullable', 'string', 'max:3000'],

            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.id' => ['nullable', 'integer', 'exists:material_requirement_items,id'],
            'items.*.material_type_id' => ['required', 'integer', 'exists:material_types,id'],
            'items.*.brand_master_id' => ['nullable', 'integer', 'exists:brand_masters,id'],
            'items.*.material_specification_id' => ['nullable', 'integer', 'exists:material_specifications,id'],
            'items.*.specification_text' => ['nullable', 'string', 'max:500'],
            'items.*.required_quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_master_id' => ['required', 'integer', 'exists:unit_masters,id'],
            'items.*.remarks' => ['nullable', 'string', 'max:1000'],
        ], [
            'items.required' => 'Add at least one Product.',
            'items.min' => 'Add at least one Product.',
            'items.*.material_type_id.required' => 'Select a Product for every row.',
            'items.*.required_quantity.gt' => 'Required quantity must be greater than zero.',
            'items.*.unit_master_id.required' => 'Every Product row must have a unit.',
        ]);
    }

    private function validateItemRelationships(
        array $items,
        ?MaterialRequirement $materialRequirement = null
    ): void {
        $errors = [];

        $existingItems = $materialRequirement
            ? $materialRequirement->items()->get()->keyBy('id')
            : collect();

        foreach (array_values($items) as $index => $item) {
            $rowNumber = $index + 1;
            $product = MaterialType::query()->find((int) $item['material_type_id']);

            if (! $product) {
                continue;
            }

            if (
                ! $product->is_active
                || $product->is_legacy
                || $product->master_status !== 'Approved'
            ) {
                $errors["items.{$index}.material_type_id"][] =
                    "Row {$rowNumber}: select an active Approved Product.";
            }

            if (! empty($item['material_specification_id'])) {
                $valid = MaterialSpecification::query()
                    ->whereKey($item['material_specification_id'])
                    ->where('material_type_id', $product->id)
                    ->where('is_active', true)
                    ->exists();

                if (! $valid) {
                    $errors["items.{$index}.material_specification_id"][] =
                        "Row {$rowNumber}: invalid Specification for selected Product.";
                }
            }

            if (! empty($item['brand_master_id'])) {
                $canonicalBrandValid = DB::table('material_product_brand')
                    ->where('material_type_id', $product->id)
                    ->where('brand_master_id', $item['brand_master_id'])
                    ->where('is_active', true)
                    ->exists();

                $existingItem = ! empty($item['id'])
                    ? $existingItems->get((int) $item['id'])
                    : null;

                $preservingHistoricalBrand = $existingItem
                    && (int) $existingItem->material_type_id === (int) $product->id
                    && (int) $existingItem->brand_master_id === (int) $item['brand_master_id'];

                if (! $canonicalBrandValid && ! $preservingHistoricalBrand) {
                    $errors["items.{$index}.brand_master_id"][] =
                        "Row {$rowNumber}: selected Brand is not approved for selected Product.";
                }
            }

            if (! empty($item['id']) && ! $existingItems->has((int) $item['id'])) {
                $errors["items.{$index}.id"][] =
                    "Row {$rowNumber}: item does not belong to this requirement.";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function formData(?MaterialRequirement $materialRequirement = null): array
    {
        $ids = collect(old('items', []))
            ->pluck('material_type_id')
            ->filter();

        if ($ids->isEmpty() && $materialRequirement) {
            $ids = $materialRequirement->items
                ->pluck('material_type_id')
                ->filter();
        }

        $selectedProducts = MaterialType::query()
            ->with(['productGroup', 'productType', 'unit'])
            ->whereIn('id', $ids->unique()->values())
            ->get()
            ->mapWithKeys(fn (MaterialType $product) => [
                $product->id => [
                    'id' => $product->id,
                    'name' => $product->material_type_name,
                    'code' => $product->material_type_code,
                    'catalogue_code' => $product->catalogue_source_code,
                    'inventory_type' => $product->inventory_type,
                    'product_group' => $product->productGroup ? [
                        'id' => $product->productGroup->id,
                        'name' => $product->productGroup->group_name,
                        'code' => $product->productGroup->group_code,
                    ] : null,
                    'product_type' => $product->productType ? [
                        'id' => $product->productType->id,
                        'name' => $product->productType->type_name,
                        'code' => $product->productType->type_code,
                    ] : null,
                    'unit' => $product->unit ? [
                        'id' => $product->unit->id,
                        'name' => $product->unit->unit_name,
                        'code' => $product->unit->unit_code,
                        'symbol' => $product->unit->symbol,
                    ] : null,
                ],
            ])
            ->all();

        return [
            'projects' => $this->availableProjects(),

            'projectBlocks' => ProjectBlock::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),

            'units' => \App\Models\UnitMaster::query()
                ->where('is_active', true)
                ->orderBy('unit_name')
                ->get(),

            'selectedProducts' => $selectedProducts,
        ];
    }

    private function availableProjects()
    {
        $user = auth()->user();

        if (in_array($user->role?->name, ['Admin', 'PMO', 'DGM'], true)) {
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

    private function requirementRelationships(): array
    {
        return [
            'project',
            'block',
            'creator',
            'approver',
            'items.activityDivision',
            'items.activity',
            'items.materialType.productGroup',
            'items.materialType.productType',
            'items.materialType.unit',
            'items.brand',
            'items.specification',
            'items.grade',
            'items.unit',
            'materialCategory',
            'material',
        ];
    }

    private function auditValues(MaterialRequirement $requirement): array
    {
        return [
            'id' => $requirement->id,
            'project_id' => $requirement->project_id,
            'project_block_id' => $requirement->project_block_id,
            'required_date' => $requirement->required_date?->format('Y-m-d'),
            'priority' => $requirement->priority,
            'status' => $requirement->status,
            'remarks' => $requirement->remarks,
            'created_by' => $requirement->created_by,
            'approved_by' => $requirement->approved_by,
            'approved_at' => $requirement->approved_at?->toDateTimeString(),

            'items' => $requirement->items
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'material_type_id' => $item->material_type_id,
                    'material_type_name' => $item->materialType?->material_type_name,
                    'brand_master_id' => $item->brand_master_id,
                    'brand_name' => $item->brand?->brand_name,
                    'material_specification_id' => $item->material_specification_id,
                    'specification_name' => $item->specification?->specification_name,
                    'specification_text' => $item->specification_text,
                    'required_quantity' => $item->required_quantity,
                    'fulfilled_quantity' => $item->fulfilled_quantity,
                    'unit_master_id' => $item->unit_master_id,
                    'unit_name' => $item->unit?->unit_name,
                    'sort_order' => $item->sort_order,
                    'remarks' => $item->remarks,
                ])
                ->values()
                ->all(),
        ];
    }

    private function nullableTrim(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
