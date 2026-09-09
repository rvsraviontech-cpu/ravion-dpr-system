<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialDispatchReceiptItem extends Model
{
    protected $fillable = [
        'material_dispatch_receipt_id',
        'material_dispatch_item_id',

        'material_type_id',
        'brand_master_id',
        'unit_master_id',

        'product_name',
        'product_code',
        'specification_text',
        'brand_name',
        'unit_name',
        'unit_code',

        'dispatched_quantity',
        'previously_received_quantity',

        'received_quantity',
        'accepted_quantity',
        'short_quantity',
        'damaged_quantity',
        'rejected_quantity',

        'remarks',
        'sort_order',
    ];

    protected $casts = [
        'dispatched_quantity' => 'decimal:3',
        'previously_received_quantity' => 'decimal:3',

        'received_quantity' => 'decimal:3',
        'accepted_quantity' => 'decimal:3',
        'short_quantity' => 'decimal:3',
        'damaged_quantity' => 'decimal:3',
        'rejected_quantity' => 'decimal:3',
    ];

    public function materialDispatchReceipt(): BelongsTo
    {
        return $this->belongsTo(MaterialDispatchReceipt::class);
    }

    public function materialDispatchItem(): BelongsTo
    {
        return $this->belongsTo(MaterialDispatchItem::class);
    }

    public function materialType(): BelongsTo
    {
        return $this->belongsTo(MaterialType::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(
            BrandMaster::class,
            'brand_master_id'
        );
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(
            UnitMaster::class,
            'unit_master_id'
        );
    }

    public function getPendingBeforeReceiptAttribute(): float
    {
        return max(
            0,
            (float) $this->dispatched_quantity
            - (float) $this->previously_received_quantity
        );
    }

    public function quantitiesBalance(): bool
    {
        $received = round((float) $this->received_quantity, 3);

        $accounted = round(
            (float) $this->accepted_quantity
            + (float) $this->damaged_quantity
            + (float) $this->rejected_quantity,
            3
        );

        return $received === $accounted;
    }
}