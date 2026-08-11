<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialReceivedItem extends Model
{
    protected $fillable = [
        'material_received_id',
        'activity_division_id',
        'activity_id',
        'material_type_id',
        'brand_master_id',
        'material_specification_id',
        'material_grade_id',
        'quantity_received',
        'unit_master_id',
        'accepted_quantity',
        'short_quantity',
        'damaged_quantity',
        'rejected_quantity',
        'material_condition',
        'sort_order',
        'remarks',
    ];

    protected $casts = [
        'quantity_received' => 'decimal:3',
        'accepted_quantity' => 'decimal:3',
        'short_quantity' => 'decimal:3',
        'damaged_quantity' => 'decimal:3',
        'rejected_quantity' => 'decimal:3',
        'sort_order' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Parent Receipt
    |--------------------------------------------------------------------------
    */

    public function materialReceived(): BelongsTo
    {
        return $this->belongsTo(
            MaterialReceived::class,
            'material_received_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Activity Relationships
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Material Variant Relationships
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Item-level Photos
    |--------------------------------------------------------------------------
    */

    public function photos(): HasMany
    {
        return $this->hasMany(
            MaterialReceivedPhoto::class,
            'material_received_item_id'
        )
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

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

    public function getHasPhotosAttribute(): bool
    {
        if ($this->relationLoaded('photos')) {
            return $this->photos->isNotEmpty();
        }

        return $this->photos()->exists();
    }

    public function getPhotoCountAttribute(): int
    {
        if ($this->relationLoaded('photos')) {
            return $this->photos->count();
        }

        return $this->photos()->count();
    }
}