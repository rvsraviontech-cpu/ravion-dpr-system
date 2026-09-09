<?php

namespace App\Http\Controllers;

use App\Models\BrandMaster;
use App\Models\MaterialRequirementItem;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItemAllocation;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = PurchaseOrder::query()
            ->with(['project', 'vendor'])
            ->latest('po_date')
            ->latest('id');

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('po_number', 'like', "%{$search}%")
                    ->orWhere('vendor_name', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->integer('vendor_id'));
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->integer('project_id'));
        }

        return view('purchase-orders.index', [
            'purchaseOrders' => $query->paginate(10)->withQueryString(),
            'statuses' => PurchaseOrder::statuses(),
            'vendors' => Vendor::query()
                ->where('is_active', true)
                ->orderBy('vendor_name')
                ->get(['id', 'vendor_name']),
        ]);
    }

    public function create(Request $request): View
    {
        return view('purchase-orders.create', [
            'vendors' => Vendor::query()
                ->where('is_active', true)
                ->orderBy('vendor_name')
                ->get(),
            'projects' => Project::query()
                ->orderBy('project_name')
                ->get(['id', 'project_name', 'location']),
            'selectedProjectId' => $request->integer('project_id') ?: null,
        ]);
    }

    public function approvedRequirementItems(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $limit = (int) ($validated['limit'] ?? 100);

        $query = MaterialRequirementItem::query()
            ->select('material_requirement_items.*')
            ->with([
                'materialRequirement.project',
                'materialType',
                'brand',
                'unit',
            ])
            ->whereHas('materialRequirement', function (Builder $builder) use ($validated) {
                $builder->where('status', 'Approved');

                if (!empty($validated['project_id'])) {
                    $builder->where('project_id', $validated['project_id']);
                }
            })
            ->addSelect([
                'already_ordered_quantity' => PurchaseOrderItemAllocation::query()
                    ->selectRaw('COALESCE(SUM(purchase_order_item_allocations.allocated_quantity), 0)')
                    ->join('purchase_order_items', 'purchase_order_items.id', '=', 'purchase_order_item_allocations.purchase_order_item_id')
                    ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
                    ->whereColumn(
                        'purchase_order_item_allocations.material_requirement_item_id',
                        'material_requirement_items.id'
                    )
                    ->whereNotIn('purchase_orders.status', [
                        PurchaseOrder::STATUS_REJECTED,
                        PurchaseOrder::STATUS_CANCELLED,
                    ]),
            ])
            ->orderByDesc('material_requirement_items.material_requirement_id')
            ->orderBy('material_requirement_items.sort_order')
            ->orderBy('material_requirement_items.id');

        if (!empty($validated['search'])) {
            $search = trim($validated['search']);

            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('specification_text', 'like', "%{$search}%")
                    ->orWhereHas('materialType', function (Builder $material) use ($search) {
                        $material
                            ->where('material_type_name', 'like', "%{$search}%")
                            ->orWhere('material_type_code', 'like', "%{$search}%")
                            ->orWhere('catalogue_source_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('brand', fn (Builder $brand) =>
                        $brand->where('brand_name', 'like', "%{$search}%")
                    );
            });
        }

        $rows = $query
            ->limit($limit)
            ->get()
            ->map(function (MaterialRequirementItem $item) {
                $required = (float) $item->required_quantity;
                $ordered = (float) ($item->already_ordered_quantity ?? 0);
                $pending = max(0, $required - $ordered);

                $requiredDate = $item->materialRequirement?->required_date;
                $requiredDateFormatted = null;

                if ($requiredDate) {
                    try {
                        $requiredDateFormatted = \Carbon\Carbon::parse($requiredDate)->format('d/m/Y');
                    } catch (\Throwable) {
                        $requiredDateFormatted = (string) $requiredDate;
                    }
                }

                return [
                    'id' => $item->id,
                    'material_requirement_id' => $item->material_requirement_id,
                    'requirement_no' => 'MR-' . str_pad((string) $item->material_requirement_id, 4, '0', STR_PAD_LEFT),
                    'required_date' => $requiredDateFormatted,
                    'project_id' => $item->materialRequirement?->project_id,
                    'project_name' => $item->materialRequirement?->project?->project_name,
                    'material_type_id' => $item->material_type_id,
                    'product_name' => $item->materialType?->material_type_name,
                    'product_code' => $item->materialType?->material_type_code,
                    'catalogue_code' => $item->materialType?->catalogue_source_code,
                    'specification_text' => $item->specification_text,
                    'brand_master_id' => $item->brand_master_id,
                    'brand_name' => $item->brand?->brand_name,
                    'brands' => $this->relevantBrandsForProduct($item->material_type_id),
                    'unit_master_id' => $item->unit_master_id,
                    'unit_name' => $item->unit?->unit_name,
                    'unit_code' => $item->unit?->unit_code,
                    'required_quantity' => round($required, 3),
                    'already_ordered_quantity' => round($ordered, 3),
                    'pending_quantity' => round($pending, 3),
                    'remarks' => $item->remarks,
                ];
            })
            ->filter(fn (array $item) => $item['pending_quantity'] > 0)
            ->values();

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'vendor_id' => ['required', 'integer', 'exists:vendors,id'],
            'po_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date'],
            'vendor_reference' => ['nullable', 'string', 'max:150'],
            'delivery_address' => ['nullable', 'string'],
            'payment_terms' => ['nullable', 'string'],
            'delivery_terms' => ['nullable', 'string'],
            'freight_terms' => ['nullable', 'string'],
            'other_charges' => ['nullable', 'numeric', 'min:0'],
            'internal_remarks' => ['nullable', 'string'],
            'vendor_notes' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_requirement_item_id' => ['required', 'integer', 'exists:material_requirement_items,id'],
            'items.*.brand_master_id' => ['nullable', 'integer', 'exists:brand_masters,id'],
            'items.*.ordered_quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.rate' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.remarks' => ['nullable', 'string'],
        ])->validate();

        $purchaseOrder = DB::transaction(function () use ($validated) {
            $vendor = Vendor::query()->lockForUpdate()->findOrFail($validated['vendor_id']);

            $requirementItems = MaterialRequirementItem::query()
                ->with(['materialRequirement.project', 'materialType', 'unit'])
                ->whereIn('id', collect($validated['items'])->pluck('material_requirement_item_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($requirementItems->count() !== count($validated['items'])) {
                abort(422, 'One or more Material Requirement items could not be loaded.');
            }

            $projectIds = $requirementItems
                ->map(fn (MaterialRequirementItem $item) => $item->materialRequirement?->project_id)
                ->filter()
                ->unique();

            if ($projectIds->count() !== 1 || (int) $projectIds->first() !== (int) $validated['project_id']) {
                abort(422, 'All PO items must belong to the selected Project.');
            }

            foreach ($requirementItems as $requirementItem) {
                if ($requirementItem->materialRequirement?->status !== 'Approved') {
                    abort(422, 'Only Approved Material Requirement items can be ordered.');
                }
            }

            $project = $requirementItems->first()->materialRequirement->project;

            $po = PurchaseOrder::create([
                'po_number' => $this->nextPoNumber(),
                'project_id' => $validated['project_id'],
                'vendor_id' => $vendor->id,
                'po_date' => $validated['po_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'vendor_reference' => $validated['vendor_reference'] ?? null,
                'vendor_name' => $vendor->vendor_name,
                'vendor_gst_number' => $vendor->gst_number,
                'vendor_address' => $this->vendorAddressSnapshot($vendor),
                'vendor_contact_person' => $vendor->contact_person,
                'vendor_mobile' => $vendor->mobile,
                'vendor_email' => $vendor->email,
                'project_name' => $project?->project_name ?? '-',
                'delivery_address' => $validated['delivery_address'] ?? $project?->location,
                'payment_terms' => $validated['payment_terms'] ?? $vendor->payment_terms,
                'delivery_terms' => $validated['delivery_terms'] ?? null,
                'freight_terms' => $validated['freight_terms'] ?? null,
                'currency' => 'INR',
                'status' => PurchaseOrder::STATUS_DRAFT,
                'internal_remarks' => $validated['internal_remarks'] ?? null,
                'vendor_notes' => $validated['vendor_notes'] ?? null,
                'terms_conditions' => $validated['terms_conditions'] ?? null,
                'other_charges' => round((float) ($validated['other_charges'] ?? 0), 2),
                'created_by' => auth()->id(),
            ]);

            $subtotal = 0.0;
            $discountTotal = 0.0;
            $taxableTotal = 0.0;
            $taxTotal = 0.0;
            $grandTotal = 0.0;

            foreach (array_values($validated['items']) as $index => $payload) {
                $requirementItem = $requirementItems->get((int) $payload['material_requirement_item_id']);

                $alreadyOrdered = $this->alreadyOrderedQuantity($requirementItem->id);
                $pending = max(0, (float) $requirementItem->required_quantity - $alreadyOrdered);
                $orderedQty = round((float) $payload['ordered_quantity'], 3);

                if ($orderedQty > $pending + 0.0005) {
                    abort(
                        422,
                        sprintf(
                            '%s has only %s %s pending to order.',
                            $requirementItem->materialType?->material_type_name ?? 'Product',
                            rtrim(rtrim(number_format($pending, 3, '.', ''), '0'), '.'),
                            $requirementItem->unit?->unit_code ?? ''
                        )
                    );
                }

                $brand = null;

                if (!empty($payload['brand_master_id'])) {
                    $brand = BrandMaster::query()->findOrFail($payload['brand_master_id']);

                    $allowedBrandIds = collect(
                        $this->relevantBrandsForProduct($requirementItem->material_type_id)
                    )->pluck('id')->map(fn ($id) => (int) $id);

                    if ($allowedBrandIds->isNotEmpty() && !$allowedBrandIds->contains((int) $brand->id)) {
                        abort(
                            422,
                            "{$brand->brand_name} is not mapped to {$requirementItem->materialType?->material_type_name}."
                        );
                    }
                } elseif ($requirementItem->brand_master_id) {
                    $brand = BrandMaster::query()->find($requirementItem->brand_master_id);
                }

                $rate = round((float) ($payload['rate'] ?? 0), 4);
                $discountPercent = round((float) ($payload['discount_percent'] ?? 0), 4);
                $taxPercent = round((float) ($payload['tax_percent'] ?? 0), 4);

                $gross = round($orderedQty * $rate, 2);
                $discountAmount = round($gross * ($discountPercent / 100), 2);
                $taxable = round($gross - $discountAmount, 2);
                $taxAmount = round($taxable * ($taxPercent / 100), 2);
                $lineAmount = round($taxable + $taxAmount, 2);

                $itemRemarks = trim((string) ($payload['remarks'] ?? ''));

                if ($itemRemarks === '') {
                    $itemRemarks = $requirementItem->remarks;
                }

                $poItem = $po->items()->create([
                    'material_type_id' => $requirementItem->material_type_id,
                    'material_specification_id' => $requirementItem->material_specification_id,
                    'material_grade_id' => $requirementItem->material_grade_id,
                    'brand_master_id' => $brand?->id,
                    'unit_master_id' => $requirementItem->unit_master_id,
                    'product_name' => $requirementItem->materialType?->material_type_name ?? '-',
                    'product_code' => $requirementItem->materialType?->material_type_code,
                    'specification_text' => $requirementItem->specification_text,
                    'brand_name' => $brand?->brand_name,
                    'unit_name' => $requirementItem->unit?->unit_name,
                    'unit_code' => $requirementItem->unit?->unit_code,
                    'ordered_quantity' => $orderedQty,
                    'received_quantity' => 0,
                    'rate' => $rate,
                    'discount_percent' => $discountPercent,
                    'discount_amount' => $discountAmount,
                    'taxable_amount' => $taxable,
                    'tax_percent' => $taxPercent,
                    'tax_amount' => $taxAmount,
                    'line_amount' => $lineAmount,
                    'sort_order' => $index + 1,
                    'remarks' => $itemRemarks ?: null,
                ]);

                $poItem->allocations()->create([
                    'material_requirement_item_id' => $requirementItem->id,
                    'allocated_quantity' => $orderedQty,
                    'received_quantity' => 0,
                ]);

                $subtotal += $gross;
                $discountTotal += $discountAmount;
                $taxableTotal += $taxable;
                $taxTotal += $taxAmount;
                $grandTotal += $lineAmount;
            }

            $otherCharges = round((float) ($validated['other_charges'] ?? 0), 2);

            $po->update([
                'subtotal' => round($subtotal, 2),
                'discount_amount' => round($discountTotal, 2),
                'taxable_amount' => round($taxableTotal, 2),
                'tax_amount' => round($taxTotal, 2),
                'other_charges' => $otherCharges,
                'grand_total' => round($grandTotal + $otherCharges, 2),
            ]);

            return $po;
        });

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder)
            ->with('success', "Purchase Order {$purchaseOrder->po_number} created as Draft.");
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load([
            'project',
            'vendor',
            'items.allocations.materialRequirementItem.materialRequirement',
            'creator',
            'submitter',
            'approver',
            'issuer',
        ]);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder): View
    {
        abort_unless($purchaseOrder->isEditable(), 422, 'This Purchase Order can no longer be edited.');

        return view('purchase-orders.edit', compact('purchaseOrder'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        abort(501, 'Purchase Order editing will be enabled when the full procurement workflow is activated.');
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        abort_unless(
            $purchaseOrder->status === PurchaseOrder::STATUS_DRAFT,
            422,
            'Only Draft Purchase Orders can be deleted.'
        );

        $purchaseOrder->delete();

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'Draft Purchase Order deleted.');
    }

    public function placeOrder(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        abort_unless(
            $purchaseOrder->status === PurchaseOrder::STATUS_DRAFT,
            422,
            'Only a Draft Purchase Order can be placed.'
        );

        $purchaseOrder->load('items');

        abort_if(
            $purchaseOrder->items->isEmpty(),
            422,
            'A Purchase Order must contain at least one item before it can be placed.'
        );

        DB::transaction(function () use ($purchaseOrder) {
            $now = now();
            $userId = auth()->id();

            $purchaseOrder->update([
                'status' => PurchaseOrder::STATUS_ISSUED,
                'submitted_by' => $userId,
                'submitted_at' => $now,
                'approved_by' => $userId,
                'approved_at' => $now,
                'issued_by' => $userId,
                'issued_at' => $now,
            ]);
        });

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder)
            ->with('success', "Purchase Order {$purchaseOrder->po_number} has been placed and marked as Issued.");
    }

    public function exportPdf(PurchaseOrder $purchaseOrder): Response
    {
        abort_unless(
            in_array($purchaseOrder->status, [
                PurchaseOrder::STATUS_ISSUED,
                PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
                PurchaseOrder::STATUS_RECEIVED,
                PurchaseOrder::STATUS_CLOSED,
            ], true),
            422,
            'Place the Purchase Order before generating the vendor PDF.'
        );

        $purchaseOrder->load([
            'project',
            'vendor',
            'items.allocations.materialRequirementItem.materialRequirement',
        ]);

        $fileName = $purchaseOrder->po_number
            . '-'
            . str($purchaseOrder->vendor_name)->slug('-')
            . '.pdf';

        return Pdf::loadView('purchase-orders.pdf', compact('purchaseOrder'))
            ->setPaper('a4', 'portrait')
            ->download($fileName);
    }

    private function relevantBrandsForProduct(?int $materialTypeId): array
    {
        if (!$materialTypeId) {
            return [];
        }

        $brandIds = collect();

        if (
            Schema::hasTable('material_product_brand')
            && Schema::hasColumn('material_product_brand', 'material_type_id')
            && Schema::hasColumn('material_product_brand', 'brand_master_id')
        ) {
            $pivot = DB::table('material_product_brand')
                ->where('material_type_id', $materialTypeId);

            if (Schema::hasColumn('material_product_brand', 'is_active')) {
                $pivot->where('is_active', true);
            }

            $brandIds = $pivot->pluck('brand_master_id');
        }

        $legacyBrandIds = BrandMaster::query()
            ->where('material_type_id', $materialTypeId)
            ->where('is_active', true)
            ->pluck('id');

        $brandIds = $brandIds
            ->merge($legacyBrandIds)
            ->filter()
            ->unique()
            ->values();

        if ($brandIds->isEmpty()) {
            return [];
        }

        return BrandMaster::query()
            ->whereIn('id', $brandIds)
            ->where('is_active', true)
            ->orderBy('brand_name')
            ->get(['id', 'brand_name'])
            ->map(fn (BrandMaster $brand) => [
                'id' => $brand->id,
                'name' => $brand->brand_name,
            ])
            ->values()
            ->all();
    }

    private function alreadyOrderedQuantity(int $requirementItemId): float
    {
        return (float) PurchaseOrderItemAllocation::query()
            ->join('purchase_order_items', 'purchase_order_items.id', '=', 'purchase_order_item_allocations.purchase_order_item_id')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->where('purchase_order_item_allocations.material_requirement_item_id', $requirementItemId)
            ->whereNotIn('purchase_orders.status', [
                PurchaseOrder::STATUS_REJECTED,
                PurchaseOrder::STATUS_CANCELLED,
            ])
            ->sum('purchase_order_item_allocations.allocated_quantity');
    }

    private function nextPoNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "PO-{$year}-";

        $lastNumber = PurchaseOrder::query()
            ->where('po_number', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderByDesc('po_number')
            ->value('po_number');

        $sequence = 1;

        if ($lastNumber && preg_match('/(\d+)$/', $lastNumber, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function vendorAddressSnapshot(Vendor $vendor): ?string
    {
        $parts = array_filter([
            $vendor->address,
            $vendor->city,
            $vendor->state,
            $vendor->pincode,
        ], fn ($value) => filled($value));

        return $parts ? implode(', ', $parts) : null;
    }
}
