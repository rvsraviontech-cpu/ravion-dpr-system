<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialRequirementItem extends Model
{
    protected $fillable = [
        'material_requirement_id',
        'activity_division_id',
        'activity_id',
        'material_type_id',
        'brand_master_id',
        'material_specification_id',
        'material_grade_id',
        'required_quantity',
        'fulfilled_quantity',
        'unit_master_id',
        'sort_order',
        'remarks',
    ];

    protected $casts = [
        'required_quantity' => 'decimal:3',
        'fulfilled_quantity' => 'decimal:3',
        'sort_order' => 'integer',
    ];

    public function materialRequirement(): BelongsTo
    {
        return $this->belongsTo(
            MaterialRequirement::class,
            'material_requirement_id'
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

    public function getPendingQuantityAttribute(): float
    {
        return max(
            0,
            (float) $this->required_quantity
                - (float) $this->fulfilled_quantity
        );
    }

    public function getFulfilmentPercentageAttribute(): float
    {
        $requiredQuantity = (float) $this->required_quantity;

        if ($requiredQuantity <= 0) {
            return 0;
        }

        return min(
            100,
            round(
                (
                    (float) $this->fulfilled_quantity
                    / $requiredQuantity
                ) * 100,
                2
            )
        );
    }

    public function getIsFulfilledAttribute(): bool
    {
        return $this->pending_quantity <= 0;
    }

    public function getDisplayNameAttribute(): string
    {
        return collect([
            $this->materialType?->material_type_name,
            $this->brand?->brand_name,
            $this->specification?->specification_name,
            $this->grade?->grade_name,
        ])
            ->filter()
            ->implode(' — ');
    }
}