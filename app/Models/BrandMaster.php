<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandMaster extends Model
{
    protected $fillable = [
        'material_category_id',
        'brand_name',
        'brand_code',
        'is_active',
        'remarks',
    ];

    public function category()
    {
        return $this->belongsTo(
            MaterialCategory::class,
            'material_category_id'
        );
    }
}