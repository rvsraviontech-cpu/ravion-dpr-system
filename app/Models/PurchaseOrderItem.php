<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'material_type_id',
        'material_specification_id',
        'material_grade_id',
        'brand_master_id',
        'unit_master_id',
        'product_name',
        'product_code',
        'specification_text',
        'brand_name',
        'unit_name',
        'unit_code',
        'ordered_quantity',
        'received_quantity',
        'accepted_quantity',
        'short_quantity',
        'damaged_quantity',
        'rejected_quantity',
        'rate',
        'discount_percent',
        'discount_amount',
        'taxable_amount',
        'tax_percent',
        'tax_amount',
        'line_amount',
        'sort_order',
        'remarks',
    ];

    protected $casts = [
        'ordered_quantity' => 'decimal:3',
        'received_quantity' => 'decimal:3',
        'accepted_quantity' => 'decimal:3',
        'short_quantity' => 'decimal:3',
        'damaged_quantity' => 'decimal:3',
        'rejected_quantity' => 'decimal:3',
        'rate' => 'decimal:4',
        'discount_percent' => 'decimal:4',
        'discount_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'tax_percent' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'line_amount' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function materialType(): BelongsTo
    {
        return $this->belongsTo(MaterialType::class);
    }

    public function materialSpecification(): BelongsTo
    {
        return $this->belongsTo(MaterialSpecification::class);
    }

    public function materialGrade(): BelongsTo
    {
        return $this->belongsTo(MaterialGrade::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(BrandMaster::class, 'brand_master_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitMaster::class, 'unit_master_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PurchaseOrderItemAllocation::class);
    }

    /**
     * Quantity physically received at site.
     *
     * received = accepted + damaged + rejected
     */
    public function getPhysicalReceivedQuantityAttribute(): float
    {
        return (float) $this->received_quantity;
    }

    /**
     * Total quantity accounted against the PO.
     *
     * Short quantity is accounted against the order but was not
     * physically received.
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
     * Quantity still pending against the PO.
     */
    public function getPendingReceiptQuantityAttribute(): float
    {
        return max(
            0,
            round(
                (float) $this->ordered_quantity
                - $this->accounted_quantity,
                3
            )
        );
    }

    /**
     * Percentage of the ordered quantity that has been accounted.
     */
    public function getReceiptPercentageAttribute(): float
    {
        $ordered = (float) $this->ordered_quantity;

        if ($ordered <= 0) {
            return 0;
        }

        return min(
            100,
            round(($this->accounted_quantity / $ordered) * 100, 2)
        );
    }

    /**
     * True when the complete ordered quantity has been accounted.
     */
    public function getIsFullyAccountedAttribute(): bool
    {
        return $this->pending_receipt_quantity <= 0.0005;
    }

    /**
     * Defensive consistency check for cumulative physical quantities.
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