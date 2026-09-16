<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaterialReceivedPurchaseOrderController extends Controller
{
    /**
     * Return Purchase Orders that can still receive material.
     *
     * Used by Material Received -> Against Purchase Order.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => [
                'nullable',
                'integer',
                'exists:projects,id',
            ],

            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $query = PurchaseOrder::query()
            ->with([
                'project:id,project_name',
                'vendor:id,vendor_name',
            ])
            ->whereIn('status', [
                PurchaseOrder::STATUS_ISSUED,
                PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
            ])
            ->whereHas('items', function ($itemQuery) {
                $itemQuery->whereRaw(
                    'ordered_quantity > (received_quantity + short_quantity)'
                );
            });

        if (! empty($validated['project_id'])) {
            $query->where(
                'project_id',
                (int) $validated['project_id']
            );
        }

        if (! empty($validated['search'])) {
            $search = trim($validated['search']);

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where(
                        'po_number',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'vendor_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'project_name',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        $purchaseOrders = $query
            ->orderByDesc('po_date')
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(function (PurchaseOrder $purchaseOrder) {
                return [
                    'id' =>
                        $purchaseOrder->id,

                    'po_number' =>
                        $purchaseOrder->po_number,

                    'status' =>
                        $purchaseOrder->status,

                    'project_id' =>
                        $purchaseOrder->project_id,

                    'project_name' =>
                        $purchaseOrder->project?->project_name
                        ?? $purchaseOrder->project_name,

                    'vendor_id' =>
                        $purchaseOrder->vendor_id,

                    'vendor_name' =>
                        $purchaseOrder->vendor?->vendor_name
                        ?? $purchaseOrder->vendor_name,

                    'po_date' =>
                        $purchaseOrder->po_date?->format('Y-m-d'),

                    'expected_delivery_date' =>
                        $purchaseOrder
                            ->expected_delivery_date
                            ?->format('Y-m-d'),

                    'vendor_reference' =>
                        $purchaseOrder->vendor_reference,

                    'delivery_address' =>
                        $purchaseOrder->delivery_address,
                ];
            })
            ->values();

        return response()->json([
            'data' => $purchaseOrders,
        ]);
    }

    /**
     * Return one PO and only the quantities still pending receipt.
     */
    public function show(
        PurchaseOrder $purchaseOrder
    ): JsonResponse {
        abort_unless(
            in_array(
                $purchaseOrder->status,
                [
                    PurchaseOrder::STATUS_ISSUED,
                    PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
                ],
                true
            ),
            422,
            'Only Issued or Partially Received Purchase Orders can receive material.'
        );

        $purchaseOrder->load([
            'project',
            'vendor',

            'items.materialType',
            'items.brand',
            'items.unit',

            'items.allocations.materialRequirementItem.materialRequirement',
        ]);

        $items = $purchaseOrder->items
            ->map(
                fn (PurchaseOrderItem $item) =>
                    $this->transformItem($item)
            )
            ->filter(
                fn (array $item) =>
                    $item['pending_quantity'] > 0
            )
            ->values();

        abort_if(
            $items->isEmpty(),
            422,
            'This Purchase Order has no quantity pending for receipt.'
        );

        return response()->json([
            'data' => [
                'id' =>
                    $purchaseOrder->id,

                'po_number' =>
                    $purchaseOrder->po_number,

                'status' =>
                    $purchaseOrder->status,

                'project_id' =>
                    $purchaseOrder->project_id,

                'project_name' =>
                    $purchaseOrder->project?->project_name
                    ?? $purchaseOrder->project_name,

                'vendor_id' =>
                    $purchaseOrder->vendor_id,

                'vendor_name' =>
                    $purchaseOrder->vendor?->vendor_name
                    ?? $purchaseOrder->vendor_name,

                'vendor_reference' =>
                    $purchaseOrder->vendor_reference,

                'po_date' =>
                    $purchaseOrder->po_date?->format('Y-m-d'),

                'expected_delivery_date' =>
                    $purchaseOrder
                        ->expected_delivery_date
                        ?->format('Y-m-d'),

                'delivery_address' =>
                    $purchaseOrder->delivery_address,

                'items' =>
                    $items,
            ],
        ]);
    }

    /**
     * Convert a PO line to the Material Received receipt-entry format.
     */
    private function transformItem(
        PurchaseOrderItem $item
    ): array {
        $ordered = round(
            (float) $item->ordered_quantity,
            3
        );

        $physicallyReceived = round(
            (float) $item->received_quantity,
            3
        );

        $accepted = round(
            (float) $item->accepted_quantity,
            3
        );

        $short = round(
            (float) $item->short_quantity,
            3
        );

        $damaged = round(
            (float) $item->damaged_quantity,
            3
        );

        $rejected = round(
            (float) $item->rejected_quantity,
            3
        );

        $accounted = round(
            $physicallyReceived + $short,
            3
        );

        $pending = max(
            0,
            round(
                $ordered - $accounted,
                3
            )
        );

        $allocations = $item->allocations
            ->map(function ($allocation) {
                return [
                    'id' =>
                        $allocation->id,

                    'material_requirement_item_id' =>
                        $allocation->material_requirement_item_id,

                    'material_requirement_id' =>
                        $allocation
                            ->materialRequirementItem
                            ?->material_requirement_id,

                    'allocated_quantity' =>
                        round(
                            (float) $allocation->allocated_quantity,
                            3
                        ),

                    'received_quantity' =>
                        round(
                            (float) $allocation->received_quantity,
                            3
                        ),

                    'accepted_quantity' =>
                        round(
                            (float) $allocation->accepted_quantity,
                            3
                        ),

                    'short_quantity' =>
                        round(
                            (float) $allocation->short_quantity,
                            3
                        ),

                    'damaged_quantity' =>
                        round(
                            (float) $allocation->damaged_quantity,
                            3
                        ),

                    'rejected_quantity' =>
                        round(
                            (float) $allocation->rejected_quantity,
                            3
                        ),

                    'accounted_quantity' =>
                        $allocation->accounted_quantity,

                    'pending_quantity' =>
                        $allocation->pending_quantity,
                ];
            })
            ->filter(
                fn (array $allocation) =>
                    $allocation['pending_quantity'] > 0
            )
            ->values();

        return [
            'purchase_order_item_id' =>
                $item->id,

            /*
             * Current PO architecture creates one allocation per PO line.
             * We still return the allocations collection so this remains
             * future-safe when one PO item supports multiple requirements.
             */
            'purchase_order_item_allocation_id' =>
                $allocations->count() === 1
                    ? $allocations->first()['id']
                    : null,

            'material_type_id' =>
                $item->material_type_id,

            'product_name' =>
                $item->product_name
                ?? $item->materialType?->material_type_name,

            'product_code' =>
                $item->product_code,

            'material_specification_id' =>
                $item->material_specification_id,

            'specification_text' =>
                $item->specification_text,

            'material_grade_id' =>
                $item->material_grade_id,

            'brand_master_id' =>
                $item->brand_master_id,

            'brand_name' =>
                $item->brand_name
                ?? $item->brand?->brand_name,

            'unit_master_id' =>
                $item->unit_master_id,

            'unit_name' =>
                $item->unit_name
                ?? $item->unit?->unit_name,

            'unit_code' =>
                $item->unit_code
                ?? $item->unit?->unit_code,

            'ordered_quantity' =>
                $ordered,

            'previously_received_quantity' =>
                $physicallyReceived,

            'previously_accepted_quantity' =>
                $accepted,

            'previously_short_quantity' =>
                $short,

            'previously_damaged_quantity' =>
                $damaged,

            'previously_rejected_quantity' =>
                $rejected,

            'previously_accounted_quantity' =>
                $accounted,

            'pending_quantity' =>
                $pending,

            'rate' =>
                (float) $item->rate,

            'discount_percent' =>
                (float) $item->discount_percent,

            'tax_percent' =>
                (float) $item->tax_percent,

            'remarks' =>
                $item->remarks,

            'allocations' =>
                $allocations,
        ];
    }
}