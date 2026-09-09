<?php

namespace App\Http\Controllers;

use App\Models\BrandMaster;
use App\Models\MaterialDispatch;
use App\Models\MaterialDispatchItem;
use App\Models\MaterialDispatchItemAllocation;
use App\Models\MaterialRequirement;
use App\Models\MaterialRequirementItem;
use App\Models\MaterialType;
use App\Models\Project;
use App\Models\UnitMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MaterialDispatchController extends Controller
{
    /**
     * Display dispatch register.
     */
    public function index(Request $request): View
    {
        $query = MaterialDispatch::query()
            ->with([
                'project',
                'createdBy',
                'dispatchedBy',
            ])
            ->withCount('items')
            ->orderByDesc('dispatch_date')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('dispatch_number', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%")
                    ->orWhere('challan_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhere('vehicle_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->integer('project_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $dispatches = $query->paginate(25)->withQueryString();

        $projects = Project::query()
            ->orderBy('project_name')
            ->get(['id', 'project_code', 'project_name']);

        $statuses = [
            MaterialDispatch::STATUS_DRAFT,
            MaterialDispatch::STATUS_DISPATCHED,
            MaterialDispatch::STATUS_PARTIALLY_RECEIVED,
            MaterialDispatch::STATUS_RECEIVED,
            MaterialDispatch::STATUS_CLOSED,
            MaterialDispatch::STATUS_CANCELLED,
        ];

        return view('material-dispatches.index', compact(
            'dispatches',
            'projects',
            'statuses'
        ));
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        return view(
            'material-dispatches.create',
            $this->formData()
        );
    }

    /**
     * Store dispatch.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDispatch($request);

        $this->validateItemRelationships($validated['items']);

        $dispatch = DB::transaction(function () use ($validated) {
            $project = Project::query()
                ->findOrFail($validated['project_id']);

            $dispatch = MaterialDispatch::create([
                'dispatch_number' => $this->nextDispatchNumber(),
                'project_id' => $project->id,
                'dispatch_date' => $validated['dispatch_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,

                'dispatch_from' => $validated['dispatch_from'] ?: 'Ravion Head Office',
                'dispatch_from_address' => $validated['dispatch_from_address'] ?? null,

                'project_name' => $project->project_name,
                'delivery_address' => $validated['delivery_address'] ?? $project->location,

                'transport_mode' => $validated['transport_mode'] ?? null,
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'driver_name' => $validated['driver_name'] ?? null,
                'driver_mobile' => $validated['driver_mobile'] ?? null,
                'transporter_name' => $validated['transporter_name'] ?? null,
                'challan_number' => $validated['challan_number'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,

                'status' => MaterialDispatch::STATUS_DRAFT,

                'dispatch_notes' => $validated['dispatch_notes'] ?? null,
                'internal_remarks' => $validated['internal_remarks'] ?? null,

                'created_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $index => $item) {
                $product = MaterialType::query()->findOrFail($item['material_type_id']);
                $unit = UnitMaster::query()->findOrFail($item['unit_master_id']);

                $brand = !empty($item['brand_master_id'])
                    ? BrandMaster::query()->find($item['brand_master_id'])
                    : null;

                $dispatchItem = MaterialDispatchItem::create([
                    'material_dispatch_id' => $dispatch->id,

                    'material_type_id' => $product->id,
                    'material_specification_id' => $item['material_specification_id'] ?? null,
                    'material_grade_id' => $item['material_grade_id'] ?? null,
                    'brand_master_id' => $brand?->id,
                    'unit_master_id' => $unit->id,

                    'product_name' => $this->productDisplayName($product),
                    'product_code' => $this->productCode($product),
                    'specification_text' => $item['specification_text'] ?? null,
                    'brand_name' => $brand?->brand_name,
                    'unit_name' => $this->unitDisplayName($unit),
                    'unit_code' => $this->unitCode($unit),

                    'dispatched_quantity' => $item['dispatched_quantity'],
                    'received_quantity' => 0,
                    'accepted_quantity' => 0,
                    'short_quantity' => 0,
                    'damaged_quantity' => 0,
                    'rejected_quantity' => 0,

                    'sort_order' => $index + 1,
                    'remarks' => $item['remarks'] ?? null,
                ]);

                if (!empty($item['material_requirement_item_id'])) {
                    MaterialDispatchItemAllocation::create([
                        'material_dispatch_item_id' => $dispatchItem->id,
                        'material_requirement_item_id' => $item['material_requirement_item_id'],
                        'allocated_quantity' => $item['dispatched_quantity'],
                        'received_quantity' => 0,
                    ]);
                }
            }

            return $dispatch;
        });

        return redirect()
            ->route('material-dispatches.show', $dispatch)
            ->with('success', 'Head Office Dispatch created successfully.');
    }

    /**
     * Display dispatch.
     */
    public function show(MaterialDispatch $materialDispatch): View
    {
        $materialDispatch->load([
            'project',
            'createdBy',
            'dispatchedBy',
            'cancelledBy',
            'items.materialType',
            'items.brand',
            'items.unit',
            'items.allocations.materialRequirementItem.materialRequirement',
        ]);

        return view('material-dispatches.show', compact('materialDispatch'));
    }

    /**
     * Show edit form.
     */
    public function edit(MaterialDispatch $materialDispatch): View
    {
        abort_unless(
            $materialDispatch->status === MaterialDispatch::STATUS_DRAFT,
            403,
            'Only Draft dispatches can be edited.'
        );

        $materialDispatch->load([
            'items.allocations',
        ]);

        return view(
            'material-dispatches.edit',
            array_merge(
                $this->formData(),
                ['materialDispatch' => $materialDispatch]
            )
        );
    }

    /**
     * Update dispatch.
     */
    public function update(
        Request $request,
        MaterialDispatch $materialDispatch
    ): RedirectResponse {
        abort_unless(
            $materialDispatch->status === MaterialDispatch::STATUS_DRAFT,
            403,
            'Only Draft dispatches can be edited.'
        );

        $validated = $this->validateDispatch($request);

        $this->validateItemRelationships(
            $validated['items'],
            $materialDispatch->id
        );

        DB::transaction(function () use (
            $validated,
            $materialDispatch
        ) {
            $project = Project::query()
                ->findOrFail($validated['project_id']);

            $materialDispatch->update([
                'project_id' => $project->id,
                'dispatch_date' => $validated['dispatch_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,

                'dispatch_from' => $validated['dispatch_from'] ?: 'Ravion Head Office',
                'dispatch_from_address' => $validated['dispatch_from_address'] ?? null,

                'project_name' => $project->project_name,
                'delivery_address' => $validated['delivery_address'] ?? $project->location,

                'transport_mode' => $validated['transport_mode'] ?? null,
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'driver_name' => $validated['driver_name'] ?? null,
                'driver_mobile' => $validated['driver_mobile'] ?? null,
                'transporter_name' => $validated['transporter_name'] ?? null,
                'challan_number' => $validated['challan_number'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,

                'dispatch_notes' => $validated['dispatch_notes'] ?? null,
                'internal_remarks' => $validated['internal_remarks'] ?? null,
            ]);

            $materialDispatch->items()->delete();

            foreach ($validated['items'] as $index => $item) {
                $product = MaterialType::query()
                    ->findOrFail($item['material_type_id']);

                $unit = UnitMaster::query()
                    ->findOrFail($item['unit_master_id']);

                $brand = !empty($item['brand_master_id'])
                    ? BrandMaster::query()->find($item['brand_master_id'])
                    : null;

                $dispatchItem = MaterialDispatchItem::create([
                    'material_dispatch_id' => $materialDispatch->id,

                    'material_type_id' => $product->id,
                    'material_specification_id' => $item['material_specification_id'] ?? null,
                    'material_grade_id' => $item['material_grade_id'] ?? null,
                    'brand_master_id' => $brand?->id,
                    'unit_master_id' => $unit->id,

                    'product_name' => $this->productDisplayName($product),
                    'product_code' => $this->productCode($product),
                    'specification_text' => $item['specification_text'] ?? null,
                    'brand_name' => $brand?->brand_name,
                    'unit_name' => $this->unitDisplayName($unit),
                    'unit_code' => $this->unitCode($unit),

                    'dispatched_quantity' => $item['dispatched_quantity'],
                    'received_quantity' => 0,
                    'accepted_quantity' => 0,
                    'short_quantity' => 0,
                    'damaged_quantity' => 0,
                    'rejected_quantity' => 0,

                    'sort_order' => $index + 1,
                    'remarks' => $item['remarks'] ?? null,
                ]);

                if (!empty($item['material_requirement_item_id'])) {
                    MaterialDispatchItemAllocation::create([
                        'material_dispatch_item_id' => $dispatchItem->id,
                        'material_requirement_item_id' => $item['material_requirement_item_id'],
                        'allocated_quantity' => $item['dispatched_quantity'],
                        'received_quantity' => 0,
                    ]);
                }
            }
        });

        return redirect()
            ->route('material-dispatches.show', $materialDispatch)
            ->with('success', 'Head Office Dispatch updated successfully.');
    }

    /**
     * Delete a draft dispatch.
     */
    public function destroy(
        MaterialDispatch $materialDispatch
    ): RedirectResponse {
        abort_unless(
            $materialDispatch->status === MaterialDispatch::STATUS_DRAFT,
            403,
            'Only Draft dispatches can be deleted.'
        );

        $materialDispatch->delete();

        return redirect()
            ->route('material-dispatches.index')
            ->with('success', 'Head Office Dispatch deleted successfully.');
    }

    /**
     * Mark draft dispatch as sent.
     */
    public function dispatch(
        MaterialDispatch $materialDispatch
    ): RedirectResponse {
        abort_unless(
            $materialDispatch->status === MaterialDispatch::STATUS_DRAFT,
            403,
            'Only Draft dispatches can be dispatched.'
        );

        if (!$materialDispatch->items()->exists()) {
            return back()->withErrors([
                'dispatch' => 'At least one material item is required before dispatch.',
            ]);
        }

        $materialDispatch->update([
            'status' => MaterialDispatch::STATUS_DISPATCHED,
            'dispatched_by' => Auth::id(),
            'dispatched_at' => now(),
        ]);

        return redirect()
            ->route('material-dispatches.show', $materialDispatch)
            ->with('success', 'Materials marked as dispatched.');
    }

    /**
     * Return approved MR items available for HO dispatch.
     */
    public function approvedRequirementItems(Request $request): JsonResponse
    {
        $projectId = $request->integer('project_id');

        if (!$projectId) {
            return response()->json([
                'requirements' => [],
            ]);
        }

        $requirements = MaterialRequirement::query()
            ->where('project_id', $projectId)
            ->where('status', 'Approved')
            ->with([
                'items.materialType',
                'items.brand',
                'items.unit',
            ])
            ->orderByDesc('required_date')
            ->orderByDesc('id')
            ->get();

        $result = [];

        foreach ($requirements as $requirement) {
            $items = [];

            foreach ($requirement->items as $item) {
                $requiredQty = (float) $item->required_quantity;
                $fulfilledQty = (float) ($item->fulfilled_quantity ?? 0);

                $poAllocated = $this->purchaseOrderAllocatedQuantity($item->id);

                $dispatchAllocated = $this->dispatchAllocatedQuantity(
                    $item->id
                );

                $available = max(
                    0,
                    $requiredQty
                    - $fulfilledQty
                    - $poAllocated
                    - $dispatchAllocated
                );

                if ($available <= 0) {
                    continue;
                }

                $items[] = [
                    'id' => $item->id,
                    'material_requirement_id' => $requirement->id,
                    'material_requirement_item_id' => $item->id,

                    'material_type_id' => $item->material_type_id,
                    'product_name' => $item->materialType
                        ? $this->productDisplayName($item->materialType)
                        : 'Product',

                    'specification_text' => $item->specification_text,

                    'brand_master_id' => $item->brand_master_id,
                    'brand_name' => $item->brand?->brand_name,

                    'unit_master_id' => $item->unit_master_id,
                    'unit_name' => $item->unit
                        ? $this->unitDisplayName($item->unit)
                        : null,

                    'required_quantity' => $requiredQty,
                    'fulfilled_quantity' => $fulfilledQty,
                    'po_allocated_quantity' => $poAllocated,
                    'dispatch_allocated_quantity' => $dispatchAllocated,
                    'available_quantity' => $available,

                    'remarks' => $item->remarks,
                ];
            }

            if (empty($items)) {
                continue;
            }

            $result[] = [
                'id' => $requirement->id,

                // No requirement_number column exists.
                // Display number is safely derived from primary key.
                'display_number' => 'MR-' . str_pad(
                    (string) $requirement->id,
                    4,
                    '0',
                    STR_PAD_LEFT
                ),

                'required_date' => optional($requirement->required_date)
                    ?->format('Y-m-d'),

                'priority' => $requirement->priority,
                'remarks' => $requirement->remarks,

                'items' => $items,
            ];
        }

        return response()->json([
            'requirements' => $result,
        ]);
    }

    /**
     * Form lookup data.
     */
    private function formData(): array
    {
        return [
            'projects' => Project::query()
                ->orderBy('project_name')
                ->get([
                    'id',
                    'project_code',
                    'project_name',
                    'location',
                ]),

            'units' => UnitMaster::query()
                ->where('is_active', true)
                ->orderBy('unit_name')
                ->get(),

            'defaultDispatchFrom' => 'Ravion Head Office',
        ];
    }

    /**
     * Validate header and item rows.
     */
    private function validateDispatch(Request $request): array
    {
        return $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'dispatch_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date'],

            'dispatch_from' => ['required', 'string', 'max:255'],
            'dispatch_from_address' => ['nullable', 'string', 'max:500'],

            'delivery_address' => ['nullable', 'string'],

            'transport_mode' => ['nullable', 'string', 'max:100'],
            'vehicle_number' => ['nullable', 'string', 'max:100'],
            'driver_name' => ['nullable', 'string', 'max:150'],
            'driver_mobile' => ['nullable', 'string', 'max:30'],
            'transporter_name' => ['nullable', 'string', 'max:255'],
            'challan_number' => ['nullable', 'string', 'max:100'],
            'reference_number' => ['nullable', 'string', 'max:100'],

            'dispatch_notes' => ['nullable', 'string'],
            'internal_remarks' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.material_requirement_item_id' => [
                'nullable',
                'integer',
                'exists:material_requirement_items,id',
            ],

            'items.*.material_type_id' => [
                'required',
                'integer',
                'exists:material_types,id',
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

            'items.*.brand_master_id' => [
                'nullable',
                'integer',
                'exists:brand_masters,id',
            ],

            'items.*.unit_master_id' => [
                'required',
                'integer',
                'exists:unit_masters,id',
            ],

            'items.*.specification_text' => [
                'nullable',
                'string',
                'max:500',
            ],

            'items.*.dispatched_quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'items.*.remarks' => [
                'nullable',
                'string',
            ],
        ]);
    }

    /**
     * Validate MR linkage and available quantities.
     */
    private function validateItemRelationships(
        array $items,
        ?int $currentDispatchId = null
    ): void {
        $errors = [];

        foreach ($items as $index => $item) {
            if (empty($item['material_requirement_item_id'])) {
                continue;
            }

            $row = $index + 1;

            $requirementItem = MaterialRequirementItem::query()
                ->with('materialRequirement')
                ->find($item['material_requirement_item_id']);

            if (!$requirementItem) {
                $errors["items.{$index}.material_requirement_item_id"][] =
                    "Row {$row}: Material Requirement item was not found.";

                continue;
            }

            if (
                (int) $requirementItem->material_type_id
                !== (int) $item['material_type_id']
            ) {
                $errors["items.{$index}.material_type_id"][] =
                    "Row {$row}: Product does not match the selected Material Requirement item.";
            }

            if (
                $requirementItem->materialRequirement?->status
                !== 'Approved'
            ) {
                $errors["items.{$index}.material_requirement_item_id"][] =
                    "Row {$row}: only Approved Material Requirements can be allocated.";
            }

            $requiredQty = (float) $requirementItem->required_quantity;
            $fulfilledQty = (float) ($requirementItem->fulfilled_quantity ?? 0);

            $poAllocated = $this->purchaseOrderAllocatedQuantity(
                $requirementItem->id
            );

            $dispatchAllocated = $this->dispatchAllocatedQuantity(
                $requirementItem->id,
                $currentDispatchId
            );

            $available = max(
                0,
                $requiredQty
                - $fulfilledQty
                - $poAllocated
                - $dispatchAllocated
            );

            if ((float) $item['dispatched_quantity'] > $available) {
                $errors["items.{$index}.dispatched_quantity"][] =
                    "Row {$row}: available quantity for HO Dispatch is {$available}.";
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Quantity already allocated through active Purchase Orders.
     */
    private function purchaseOrderAllocatedQuantity(
        int $requirementItemId
    ): float {
        if (
            !Schema::hasTable('purchase_order_item_allocations')
            || !Schema::hasTable('purchase_order_items')
            || !Schema::hasTable('purchase_orders')
        ) {
            return 0;
        }

        return (float) DB::table('purchase_order_item_allocations as poa')
            ->join(
                'purchase_order_items as poi',
                'poi.id',
                '=',
                'poa.purchase_order_item_id'
            )
            ->join(
                'purchase_orders as po',
                'po.id',
                '=',
                'poi.purchase_order_id'
            )
            ->where(
                'poa.material_requirement_item_id',
                $requirementItemId
            )
            ->whereNotIn('po.status', [
                'Cancelled',
                'Closed',
            ])
            ->sum('poa.allocated_quantity');
    }

    /**
     * Quantity already allocated through active HO Dispatches.
     */
    private function dispatchAllocatedQuantity(
        int $requirementItemId,
        ?int $excludeDispatchId = null
    ): float {
        $query = DB::table(
            'material_dispatch_item_allocations as mdia'
        )
            ->join(
                'material_dispatch_items as mdi',
                'mdi.id',
                '=',
                'mdia.material_dispatch_item_id'
            )
            ->join(
                'material_dispatches as md',
                'md.id',
                '=',
                'mdi.material_dispatch_id'
            )
            ->where(
                'mdia.material_requirement_item_id',
                $requirementItemId
            )
            ->whereNotIn('md.status', [
                MaterialDispatch::STATUS_CANCELLED,
                MaterialDispatch::STATUS_CLOSED,
            ]);

        if ($excludeDispatchId) {
            $query->where('md.id', '!=', $excludeDispatchId);
        }

        return (float) $query->sum('mdia.allocated_quantity');
    }

    /**
     * Generate next dispatch number.
     */
    private function nextDispatchNumber(): string
    {
        $year = now()->format('Y');

        $prefix = "MD-{$year}-";

        $last = MaterialDispatch::query()
            ->where('dispatch_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('dispatch_number');

        $next = 1;

        if ($last) {
            $parts = explode('-', $last);

            $sequence = (int) end($parts);

            $next = $sequence + 1;
        }

        return $prefix . str_pad(
            (string) $next,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Product display-name compatibility helper.
     */
    private function productDisplayName(MaterialType $product): string
    {
        return (string) (
            $product->name
            ?? $product->material_type_name
            ?? $product->product_name
            ?? 'Product'
        );
    }

    /**
     * Product code compatibility helper.
     */
    private function productCode(MaterialType $product): ?string
    {
        return $product->catalogue_source_code
            ?? $product->code
            ?? $product->material_type_code
            ?? null;
    }

    /**
     * Unit display-name compatibility helper.
     */
    private function unitDisplayName(UnitMaster $unit): string
    {
        return (string) (
            $unit->unit_name
            ?? $unit->name
            ?? $unit->unit
            ?? ''
        );
    }

    /**
     * Unit-code compatibility helper.
     */
    private function unitCode(UnitMaster $unit): ?string
    {
        return $unit->unit_code
            ?? $unit->code
            ?? $unit->short_name
            ?? null;
    }
}