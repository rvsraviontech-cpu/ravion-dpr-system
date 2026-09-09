<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialDispatchItem extends Model
{
    protected $fillable = [
        'material_dispatch_id',

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

        'dispatched_quantity',
        'received_quantity',
        'accepted_quantity',
        'short_quantity',
        'damaged_quantity',
        'rejected_quantity',

        'sort_order',
        'remarks',
    ];

    protected $casts = [
        'dispatched_quantity' => 'decimal:3',
        'received_quantity' => 'decimal:3',
        'accepted_quantity' => 'decimal:3',
        'short_quantity' => 'decimal:3',
        'damaged_quantity' => 'decimal:3',
        'rejected_quantity' => 'decimal:3',
    ];

    public function materialDispatch(): BelongsTo
    {
        return $this->belongsTo(MaterialDispatch::class);
    }

    public function materialType(): BelongsTo
    {
        return $this->belongsTo(MaterialType::class);
    }

    public function specification(): BelongsTo
    {
        return $this->belongsTo(
            MaterialSpecification::class,
            'material_specification_id'
        );
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(
            MaterialGrade::class,
            'material_grade_id'
        );
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

    public function allocations(): HasMany
    {
        return $this->hasMany(MaterialDispatchItemAllocation::class);
    }

    public function getPendingReceiptQuantityAttribute(): float
    {
        return max(
            0,
            (float) $this->dispatched_quantity
            - (float) $this->received_quantity
        );
    }

    public function getPendingAcceptanceQuantityAttribute(): float
    {
        return max(
            0,
            (float) $this->received_quantity
            - (
                (float) $this->accepted_quantity
                + (float) $this->damaged_quantity
                + (float) $this->rejected_quantity
            )
        );
    }

    public function getDisplayNameAttribute(): string
    {
        $parts = [
            $this->product_name,
            $this->specification_text,
            $this->brand_name,
        ];

        return collect($parts)
            ->filter(fn ($value) => filled($value))
            ->implode(' - ');
    }

    public function receiptItems(): HasMany
{
    return $this->hasMany(MaterialDispatchReceiptItem::class);
}
}