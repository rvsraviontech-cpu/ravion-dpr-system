<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Activity;
use App\Models\ActivityDivision;
use App\Models\BrandMaster;
use App\Models\Contractor;
use App\Models\MaterialConsumed;
use App\Models\MaterialGrade;
use App\Models\MaterialSpecification;
use App\Models\MaterialType;
use App\Models\Project;
use App\Models\ProjectBlock;
use App\Models\ProjectFloor;
use App\Models\ProjectRoom;
use App\Models\ProjectSubspace;
use App\Models\ProjectUnit;
use App\Models\UnitMaster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class MaterialConsumedController extends Controller
{
    public function index(Request $request): View
    {
        $query = MaterialConsumed::query()
            ->with($this->consumptionRelationships());

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->integer('project_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('consumed_date')) {
            $query->whereDate('consumed_date', $request->input('consumed_date'));
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('remarks', 'like', "%{$search}%")
                    ->orWhereHas('project', fn (Builder $projectQuery) =>
                        $projectQuery
                            ->where('project_name', 'like', "%{$search}%")
                            ->orWhere('project_code', 'like', "%{$search}%")
                    )
                    ->orWhereHas('contractor', fn (Builder $contractorQuery) =>
                        $contractorQuery->where('contractor_name', 'like', "%{$search}%")
                    )
                    ->orWhereHas('items.materialType', fn (Builder $typeQuery) =>
                        $typeQuery->where('material_type_name', 'like', "%{$search}%")
                    )
                    ->orWhereHas('items.brand', fn (Builder $brandQuery) =>
                        $brandQuery->where('brand_name', 'like', "%{$search}%")
                    )
                    ->orWhereHas('items.specification', fn (Builder $specificationQuery) =>
                        $specificationQuery->where('specification_name', 'like', "%{$search}%")
                    )
                    ->orWhereHas('items.grade', fn (Builder $gradeQuery) =>
                        $gradeQuery->where('grade_name', 'like', "%{$search}%")
                    );
            });
        }

        $materialConsumeds = $query
            ->orderByDesc('consumed_date')
            ->orderByDesc('id')
            ->paginate(7)
            ->withQueryString();

        $projects = $this->availableProjects();

        $todayConsumptions = MaterialConsumed::query()
            ->with('items')
            ->whereDate('consumed_date', today())
            ->get();

        $totalConsumedToday = $todayConsumptions->sum(
            fn (MaterialConsumed $consumption) => $consumption->total_quantity_consumed
        );

        $totalWastageToday = $todayConsumptions->sum(
            fn (MaterialConsumed $consumption) => $consumption->total_wastage_quantity
        );

        $draftCount = MaterialConsumed::query()->where('status', 'Draft')->count();
        $submittedCount = MaterialConsumed::query()->where('status', 'Submitted')->count();
        $approvedCount = MaterialConsumed::query()->where('status', 'Approved')->count();

        return view('material-consumed.index', compact(
            'materialConsumeds',
            'projects',
            'totalConsumedToday',
            'totalWastageToday',
            'draftCount',
            'submittedCount',
            'approvedCount'
        ));
    }

    public function create(): View
    {
        return view('material-consumed.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateConsumption($request);
        $this->validateItemRelationships($validated['items']);

        try {
            $materialConsumed = DB::transaction(function () use ($validated): MaterialConsumed {
                $materialConsumed = MaterialConsumed::create([
                    'project_id' => (int) $validated['project_id'],
                    'user_id' => auth()->id(),
                    'project_block_id' => $validated['project_block_id'] ?? null,
                    'project_floor_id' => $validated['project_floor_id'] ?? null,
                    'project_unit_id' => $validated['project_unit_id'] ?? null,
                    'project_room_id' => $validated['project_room_id'] ?? null,
                    'project_subspace_id' => $validated['project_subspace_id'] ?? null,
                    'contractor_id' => $validated['contractor_id'] ?? null,
                    'consumed_date' => $validated['consumed_date'],
                    'consumed_time' => now()->format('H:i:s'),
                    'related_work_output_quantity' => $validated['related_work_output_quantity'] ?? 0,
                    'status' => 'Draft',
                    'remarks' => $this->nullableTrim($validated['remarks'] ?? null),
                ]);

                foreach (array_values($validated['items']) as $index => $item) {
                    $materialConsumed->items()->create([
                        'activity_division_id' => $item['activity_division_id'] ?? null,
                        'activity_id' => $item['activity_id'] ?? null,
                        'material_type_id' => (int) $item['material_type_id'],
                        'brand_master_id' => $item['brand_master_id'] ?? null,
                        'material_specification_id' => $item['material_specification_id'] ?? null,
                        'material_grade_id' => $item['material_grade_id'] ?? null,
                        'quantity_consumed' => $item['quantity_consumed'],
                        'wastage_quantity' => $item['wastage_quantity'] ?? 0,
                        'unit_master_id' => (int) $item['unit_master_id'],
                        'sort_order' => $index + 1,
                        'wastage_reason' => $this->nullableTrim($item['wastage_reason'] ?? null),
                        'remarks' => $this->nullableTrim($item['remarks'] ?? null),
                    ]);
                }

                $materialConsumed->load($this->consumptionRelationships());

                AuditHelper::log(
                    'Material Consumed',
                    'Created',
                    'MaterialConsumed',
                    $materialConsumed->id,
                    'Material consumption created with '
                        . $materialConsumed->items->count()
                        . ' material item(s).',
                    null,
                    $this->auditValues($materialConsumed)
                );

                return $materialConsumed;
            });

            return redirect()
                ->route('material-consumed.show', $materialConsumed)
                ->with('success', 'Material consumption created successfully as Draft.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Unable to create the material consumption entry.');
        }
    }

    public function show(MaterialConsumed $materialConsumed): View
    {
        $materialConsumed->load($this->consumptionRelationships());

        return view('material-consumed.show', compact('materialConsumed'));
    }

    public function edit(MaterialConsumed $materialConsumed): View
    {
        if ($materialConsumed->status !== 'Draft') {
            abort(403, 'Only Draft material consumption entries can be edited.');
        }

        $materialConsumed->load($this->consumptionRelationships());

        return view(
            'material-consumed.edit',
            array_merge(compact('materialConsumed'), $this->formData())
        );
    }

    public function update(
        Request $request,
        MaterialConsumed $materialConsumed
    ): RedirectResponse {
        if ($materialConsumed->status !== 'Draft') {
            abort(403, 'Only Draft material consumption entries can be updated.');
        }

        $validated = $this->validateConsumption($request);
        $this->validateItemRelationships($validated['items']);

        try {
            DB::transaction(function () use ($validated, $materialConsumed): void {
                $materialConsumed->load($this->consumptionRelationships());
                $oldValues = $this->auditValues($materialConsumed);

                $materialConsumed->update([
                    'project_id' => (int) $validated['project_id'],
                    'project_block_id' => $validated['project_block_id'] ?? null,
                    'project_floor_id' => $validated['project_floor_id'] ?? null,
                    'project_unit_id' => $validated['project_unit_id'] ?? null,
                    'project_room_id' => $validated['project_room_id'] ?? null,
                    'project_subspace_id' => $validated['project_subspace_id'] ?? null,
                    'contractor_id' => $validated['contractor_id'] ?? null,
                    'consumed_date' => $validated['consumed_date'],
                    'related_work_output_quantity' => $validated['related_work_output_quantity'] ?? 0,
                    'remarks' => $this->nullableTrim($validated['remarks'] ?? null),
                ]);

                $materialConsumed->items()->delete();

                foreach (array_values($validated['items']) as $index => $item) {
                    $materialConsumed->items()->create([
                        'activity_division_id' => $item['activity_division_id'] ?? null,
                        'activity_id' => $item['activity_id'] ?? null,
                        'material_type_id' => (int) $item['material_type_id'],
                        'brand_master_id' => $item['brand_master_id'] ?? null,
                        'material_specification_id' => $item['material_specification_id'] ?? null,
                        'material_grade_id' => $item['material_grade_id'] ?? null,
                        'quantity_consumed' => $item['quantity_consumed'],
                        'wastage_quantity' => $item['wastage_quantity'] ?? 0,
                        'unit_master_id' => (int) $item['unit_master_id'],
                        'sort_order' => $index + 1,
                        'wastage_reason' => $this->nullableTrim($item['wastage_reason'] ?? null),
                        'remarks' => $this->nullableTrim($item['remarks'] ?? null),
                    ]);
                }

                $materialConsumed->load($this->consumptionRelationships());

                AuditHelper::log(
                    'Material Consumed',
                    'Updated',
                    'MaterialConsumed',
                    $materialConsumed->id,
                    'Material consumption entry updated.',
                    $oldValues,
                    $this->auditValues($materialConsumed)
                );
            });

            return redirect()
                ->route('material-consumed.show', $materialConsumed)
                ->with('success', 'Material consumption updated successfully.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Unable to update the material consumption entry.');
        }
    }

    public function submit(MaterialConsumed $materialConsumed): RedirectResponse
    {
        if ($materialConsumed->status !== 'Draft') {
            return back()->with(
                'error',
                'Only Draft material consumption entries can be submitted.'
            );
        }

        if (! $materialConsumed->items()->exists() && empty($materialConsumed->material_id)) {
            return back()->with('error', 'Add at least one material before submission.');
        }

        $oldValues = ['status' => $materialConsumed->status];

        $materialConsumed->update(['status' => 'Submitted']);
        $materialConsumed->refresh();

        AuditHelper::log(
            'Material Consumed',
            'Submitted',
            'MaterialConsumed',
            $materialConsumed->id,
            'Material consumption submitted for approval.',
            $oldValues,
            ['status' => $materialConsumed->status]
        );

        return back()->with(
            'success',
            'Material consumption submitted successfully.'
        );
    }

    public function approve(MaterialConsumed $materialConsumed): RedirectResponse
    {
        if ($materialConsumed->status !== 'Submitted') {
            return back()->with(
                'error',
                'Only Submitted material consumption entries can be approved.'
            );
        }

        $oldValues = ['status' => $materialConsumed->status];

        $materialConsumed->update(['status' => 'Approved']);
        $materialConsumed->refresh();

        AuditHelper::log(
            'Material Consumed',
            'Approved',
            'MaterialConsumed',
            $materialConsumed->id,
            'Material consumption approved.',
            $oldValues,
            ['status' => $materialConsumed->status]
        );

        return back()->with(
            'success',
            'Material consumption approved successfully.'
        );
    }

    public function destroy(MaterialConsumed $materialConsumed): RedirectResponse
    {
        if ($materialConsumed->status !== 'Draft') {
            return back()->with(
                'error',
                'Only Draft material consumption entries can be deleted.'
            );
        }

        $materialConsumed->load($this->consumptionRelationships());
        $oldValues = $this->auditValues($materialConsumed);

        DB::transaction(function () use ($materialConsumed, $oldValues): void {
            $entryId = $materialConsumed->id;
            $materialConsumed->delete();

            AuditHelper::log(
                'Material Consumed',
                'Deleted',
                'MaterialConsumed',
                $entryId,
                'Draft material consumption entry deleted.',
                $oldValues,
                null
            );
        });

        return redirect()
            ->route('material-consumed.index')
            ->with('success', 'Draft material consumption entry deleted successfully.');
    }

    private function validateConsumption(Request $request): array
    {
        return $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'project_block_id' => ['nullable', 'integer', 'exists:project_blocks,id'],
            'project_floor_id' => ['nullable', 'integer', 'exists:project_floors,id'],
            'project_unit_id' => ['nullable', 'integer', 'exists:project_units,id'],
            'project_room_id' => ['nullable', 'integer', 'exists:project_rooms,id'],
            'project_subspace_id' => ['nullable', 'integer', 'exists:project_subspaces,id'],
            'contractor_id' => ['nullable', 'integer', 'exists:contractors,id'],
            'consumed_date' => ['required', 'date'],
            'related_work_output_quantity' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:3000'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.activity_division_id' => ['nullable', 'integer', 'exists:activity_divisions,id'],
            'items.*.activity_id' => ['nullable', 'integer', 'exists:activities,id'],
            'items.*.material_type_id' => ['required', 'integer', 'exists:material_types,id'],
            'items.*.brand_master_id' => ['nullable', 'integer', 'exists:brand_masters,id'],
            'items.*.material_specification_id' => ['nullable', 'integer', 'exists:material_specifications,id'],
            'items.*.material_grade_id' => ['nullable', 'integer', 'exists:material_grades,id'],
            'items.*.quantity_consumed' => ['required', 'numeric', 'gt:0'],
            'items.*.wastage_quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit_master_id' => ['required', 'integer', 'exists:unit_masters,id'],
            'items.*.wastage_reason' => ['nullable', 'string', 'max:1000'],
            'items.*.remarks' => ['nullable', 'string', 'max:1000'],
        ], [
            'items.required' => 'Add at least one material item.',
            'items.min' => 'Add at least one material item.',
            'items.*.material_type_id.required' => 'Select a Material Type for every row.',
            'items.*.quantity_consumed.gt' => 'Quantity consumed must be greater than zero.',
            'items.*.unit_master_id.required' => 'Every material row must have a unit.',
        ]);
    }

    private function validateItemRelationships(array $items): void
    {
        $errors = [];

        foreach (array_values($items) as $index => $item) {
            $rowNumber = $index + 1;
            $materialTypeId = (int) $item['material_type_id'];
            $materialType = MaterialType::query()->find($materialTypeId);

            if (! $materialType) {
                continue;
            }

            if ((int) $item['unit_master_id'] !== (int) $materialType->unit_master_id) {
                $errors["items.{$index}.unit_master_id"][] =
                    "Row {$rowNumber}: the unit does not match the selected Material Type.";
            }

            if (! empty($item['brand_master_id'])) {
                $brandValid = BrandMaster::query()
                    ->whereKey($item['brand_master_id'])
                    ->where('material_type_id', $materialTypeId)
                    ->where('is_active', true)
                    ->exists();

                if (! $brandValid) {
                    $errors["items.{$index}.brand_master_id"][] =
                        "Row {$rowNumber}: the selected Brand does not belong to the selected Material Type.";
                }
            }

            if (! empty($item['material_specification_id'])) {
                $specificationValid = MaterialSpecification::query()
                    ->whereKey($item['material_specification_id'])
                    ->where('material_type_id', $materialTypeId)
                    ->where('is_active', true)
                    ->exists();

                if (! $specificationValid) {
                    $errors["items.{$index}.material_specification_id"][] =
                        "Row {$rowNumber}: the selected Specification does not belong to the selected Material Type.";
                }
            }

            if (! empty($item['material_grade_id'])) {
                $gradeValid = MaterialGrade::query()
                    ->whereKey($item['material_grade_id'])
                    ->where('material_type_id', $materialTypeId)
                    ->where('is_active', true)
                    ->exists();

                if (! $gradeValid) {
                    $errors["items.{$index}.material_grade_id"][] =
                        "Row {$rowNumber}: the selected Grade/Rating does not belong to the selected Material Type.";
                }
            }

            if (! empty($item['activity_id']) && ! empty($item['activity_division_id'])) {
                $activityValid = Activity::query()
                    ->whereKey($item['activity_id'])
                    ->where('activity_division_id', $item['activity_division_id'])
                    ->exists();

                if (! $activityValid) {
                    $errors["items.{$index}.activity_id"][] =
                        "Row {$rowNumber}: the selected Activity does not belong to the selected Activity Division.";
                }
            }

            $wastageQuantity = (float) ($item['wastage_quantity'] ?? 0);

            if (
                $wastageQuantity > 0
                && trim((string) ($item['wastage_reason'] ?? '')) === ''
            ) {
                $errors["items.{$index}.wastage_reason"][] =
                    "Row {$rowNumber}: enter a wastage reason when wastage quantity is greater than zero.";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

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
            'projects' => $this->availableProjects(),
            'projectBlocks' => ProjectBlock::query()->where('is_active', true)->orderBy('name')->get(),
            'projectFloors' => ProjectFloor::query()->where('is_active', true)->orderBy('sequence')->orderBy('name')->get(),
            'projectUnits' => ProjectUnit::query()->where('is_active', true)->orderBy('name')->get(),
            'projectRooms' => ProjectRoom::query()->where('is_active', true)->orderBy('name')->get(),
            'projectSubspaces' => ProjectSubspace::query()->where('is_active', true)->orderBy('name')->get(),
            'contractors' => Contractor::query()->where('status', 1)->orderBy('contractor_name')->get(),
            'activityDivisions' => ActivityDivision::query()->where('is_active', true)->orderBy('sequence')->orderBy('name')->get(),
            'activities' => Activity::query()->where('is_active', true)->orderBy('activity_division_id')->orderBy('activity_name')->get(),
            'materialTypes' => $materialTypes,
            'materialGroups' => $materialTypes->pluck('material_group')->filter()->unique()->sort()->values(),
            'brands' => BrandMaster::query()->where('is_active', true)->whereNotNull('material_type_id')->orderBy('material_type_id')->orderBy('sequence')->orderBy('brand_name')->get(),
            'specifications' => MaterialSpecification::query()->where('is_active', true)->whereNotNull('material_type_id')->orderBy('material_type_id')->orderBy('sequence')->orderBy('specification_name')->get(),
            'grades' => MaterialGrade::query()->where('is_active', true)->orderBy('material_type_id')->orderBy('sequence')->orderBy('grade_name')->get(),
            'units' => UnitMaster::query()->where('is_active', true)->orderBy('unit_name')->get(),
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

    private function consumptionRelationships(): array
    {
        return [
            'project',
            'engineer',
            'block',
            'floor',
            'unit',
            'room',
            'subspace',
            'contractor',
            'items.activityDivision',
            'items.activity',
            'items.materialType.unit',
            'items.brand',
            'items.specification',
            'items.grade',
            'items.unit',
            'activityDivision',
            'activity',
            'materialCategory',
            'material',
        ];
    }

    private function auditValues(MaterialConsumed $materialConsumed): array
    {
        return [
            'id' => $materialConsumed->id,
            'project_id' => $materialConsumed->project_id,
            'user_id' => $materialConsumed->user_id,
            'project_block_id' => $materialConsumed->project_block_id,
            'project_floor_id' => $materialConsumed->project_floor_id,
            'project_unit_id' => $materialConsumed->project_unit_id,
            'project_room_id' => $materialConsumed->project_room_id,
            'project_subspace_id' => $materialConsumed->project_subspace_id,
            'contractor_id' => $materialConsumed->contractor_id,
            'consumed_date' => $materialConsumed->consumed_date?->format('Y-m-d'),
            'related_work_output_quantity' => $materialConsumed->related_work_output_quantity,
            'status' => $materialConsumed->status,
            'remarks' => $materialConsumed->remarks,
            'items' => $materialConsumed->items
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'activity_division_id' => $item->activity_division_id,
                    'activity_id' => $item->activity_id,
                    'material_type_id' => $item->material_type_id,
                    'material_type_name' => $item->materialType?->material_type_name,
                    'brand_master_id' => $item->brand_master_id,
                    'brand_name' => $item->brand?->brand_name,
                    'material_specification_id' => $item->material_specification_id,
                    'specification_name' => $item->specification?->specification_name,
                    'material_grade_id' => $item->material_grade_id,
                    'grade_name' => $item->grade?->grade_name,
                    'quantity_consumed' => $item->quantity_consumed,
                    'wastage_quantity' => $item->wastage_quantity,
                    'unit_master_id' => $item->unit_master_id,
                    'unit_name' => $item->unit?->unit_name,
                    'sort_order' => $item->sort_order,
                    'wastage_reason' => $item->wastage_reason,
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

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
