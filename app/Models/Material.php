<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $fillable = [
        'material_category_id',
        'material_code',
        'material_name',
        'specification',
        'brand',
        'unit',
        'minimum_stock_level',
        'is_active',
        'remarks',
        'brand_master_id',
    ];

    protected $casts = [
        'minimum_stock_level' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(
            MaterialCategory::class,
            'material_category_id'
        );
    }

    public function brandMaster()
    {
        return $this->belongsTo(
            BrandMaster::class,
            'brand_master_id'
        );
    }

    public function activityMaterialMappings(): HasMany
    {
        return $this->hasMany(
            ActivityMaterialMapping::class,
            'material_id'
        );
    }

    public function activeActivityMaterialMappings(): HasMany
    {
        return $this->activityMaterialMappings()
            ->where('is_active', true);
    }
}