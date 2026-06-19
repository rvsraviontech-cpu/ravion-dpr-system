<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function category()
    {
        return $this->belongsTo(MaterialCategory::class, 'material_category_id');
    }

    public function brandMaster()
{
    return $this->belongsTo(
        \App\Models\BrandMaster::class,
        'brand_master_id'
    );
}
}