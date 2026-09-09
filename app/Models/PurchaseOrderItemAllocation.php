<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItemAllocation extends Model
{
    protected $fillable = [
        'purchase_order_item_id',
        'material_requirement_item_id',
        'allocated_quantity',
        'received_quantity',
        'accepted_quantity',
        'short_quantity',
        'damaged_quantity',
        'rejected_quantity',
    ];

    protected $casts = [
        'allocated_quantity' => 'decimal:3',
        'received_quantity' => 'decimal:3',
        'accepted_quantity' => 'decimal:3',
        'short_quantity' => 'decimal:3',
        'damaged_quantity' => 'decimal:3',
        'rejected_quantity' => 'decimal:3',
    ];

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function materialRequirementItem(): BelongsTo
    {
        return $this->belongsTo(MaterialRequirementItem::class);
    }

    /**
     * Quantity physically received against this requirement allocation.
     */
    public function getPhysicalReceivedQuantityAttribute(): float
    {
        return (float) $this->received_quantity;
    }

    /**
     * Total quantity accounted against this allocation.
     *
     * Short quantity closes part of the delivery obligation but does
     * not represent physical receipt or stock.
     */
    public function getAccountedQuantityAttribute(): float
    {
        return round(
            (float) $this->received_quantity
            + (float) $this->short_quantity,
            3
        );
    }

    /**
     * Quantity still pending against this allocation.
     */
    public function getPendingQuantityAttribute(): float
    {
        return max(
            0,
            round(
                (float) $this->allocated_quantity
                - $this->accounted_quantity,
                3
            )
        );
    }

    /**
     * Percentage of the allocation that has been accounted.
     */
    public function getReceiptPercentageAttribute(): float
    {
        $allocated = (float) $this->allocated_quantity;

        if ($allocated <= 0) {
            return 0;
        }

        return min(
            100,
            round(($this->accounted_quantity / $allocated) * 100, 2)
        );
    }

    /**
     * True once the complete allocation has been accounted.
     */
    public function getIsFullyAccountedAttribute(): bool
    {
        return $this->pending_quantity <= 0.0005;
    }

    /**
     * Defensive consistency check.
     *
     * received = accepted + damaged + rejected
     */
    public function quantitiesBalance(): bool
    {
        $received = round((float) $this->received_quantity, 3);

        $classified = round(
            (float) $this->accepted_quantity
            + (float) $this->damaged_quantity
            + (float) $this->rejected_quantity,
            3
        );

        return abs($received - $classified) <= 0.0005;
    }
}