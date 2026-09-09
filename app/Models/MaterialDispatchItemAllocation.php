<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialDispatchItemAllocation extends Model
{
    protected $fillable = [
        'material_dispatch_item_id',
        'material_requirement_item_id',
        'allocated_quantity',
        'received_quantity',
    ];

    protected $casts = [
        'allocated_quantity' => 'decimal:3',
        'received_quantity' => 'decimal:3',
    ];

    public function materialDispatchItem(): BelongsTo
    {
        return $this->belongsTo(MaterialDispatchItem::class);
    }

    public function materialRequirementItem(): BelongsTo
    {
        return $this->belongsTo(MaterialRequirementItem::class);
    }

    public function getPendingQuantityAttribute(): float
    {
        return max(
            0,
            (float) $this->allocated_quantity
            - (float) $this->received_quantity
        );
    }
}