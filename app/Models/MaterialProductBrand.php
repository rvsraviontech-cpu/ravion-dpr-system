<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialProductBrand extends Model
{
    protected $table='material_product_brand';
    protected $fillable=['material_type_id','brand_master_id','is_preferred','sort_order','is_active','remarks'];
    protected $casts=['material_type_id'=>'integer','brand_master_id'=>'integer','is_preferred'=>'boolean','sort_order'=>'integer','is_active'=>'boolean'];
    public function product(): BelongsTo { return $this->belongsTo(MaterialType::class,'material_type_id'); }
    public function materialType(): BelongsTo { return $this->product(); }
    public function brand(): BelongsTo { return $this->belongsTo(BrandMaster::class,'brand_master_id'); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active',true); }
    public function scopeOrdered(Builder $query): Builder { return $query->orderByDesc('is_preferred')->orderBy('sort_order')->orderBy('id'); }
}
