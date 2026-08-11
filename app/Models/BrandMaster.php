<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrandMaster extends Model
{
    protected $fillable = [
        'material_category_id',   // Legacy (V1 migration)
        'activity_id',            // Legacy (V1 migration)
        'material_type_id',       // New architecture
        'brand_name',
        'brand_code',
        'sequence',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sequence'  => 'integer',
    ];

    /**
     * Legacy Material Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(
            MaterialCategory::class,
            'material_category_id'
        );
    }

    /**
     * Legacy Activity
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(
            Activity::class,
            'activity_id'
        );
    }

    /**
     * New Material Type
     */
    public function materialType(): BelongsTo
    {
        return $this->belongsTo(
            MaterialType::class,
            'material_type_id'
        );
    }

    /**
     * Display Name
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->brand_name;
    }
}