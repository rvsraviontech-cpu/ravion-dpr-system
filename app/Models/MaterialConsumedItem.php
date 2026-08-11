<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialConsumedItem extends Model
{
    protected $fillable = [
        'material_consumed_id',
        'activity_division_id',
        'activity_id',
        'material_type_id',
        'brand_master_id',
        'material_specification_id',
        'material_grade_id',
        'quantity_consumed',
        'wastage_quantity',
        'unit_master_id',
        'sort_order',
        'wastage_reason',
        'remarks',
    ];

    protected $casts = [
        'quantity_consumed' => 'decimal:3',
        'wastage_quantity' => 'decimal:3',
        'sort_order' => 'integer',
    ];

    public function materialConsumed(): BelongsTo
    {
        return $this->belongsTo(
            MaterialConsumed::class,
            'material_consumed_id'
        );
    }

    public function activityDivision(): BelongsTo
    {
        return $this->belongsTo(
            ActivityDivision::class,
            'activity_division_id'
        );
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(
            Activity::class,
            'activity_id'
        );
    }

    public function materialType(): BelongsTo
    {
        return $this->belongsTo(
            MaterialType::class,
            'material_type_id'
        );
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(
            BrandMaster::class,
            'brand_master_id'
        );
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

    public function unit(): BelongsTo
    {
        return $this->belongsTo(
            UnitMaster::class,
            'unit_master_id'
        );
    }

    public function getTotalIssuedQuantityAttribute(): float
    {
        return (float) $this->quantity_consumed
            + (float) $this->wastage_quantity;
    }

    public function getDisplayNameAttribute(): string
    {
        return collect([
            $this->brand?->brand_name,
            $this->specification?->specification_name,
            $this->grade?->grade_name,
            $this->materialType?->material_type_name,
        ])
            ->filter()
            ->implode(' ');
    }
}