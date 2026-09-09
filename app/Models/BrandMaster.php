<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrandMaster extends Model
{
    protected $fillable=['material_category_id','activity_id','material_type_id','brand_name','brand_code','sequence','is_active','remarks'];
    protected $casts=['material_category_id'=>'integer','activity_id'=>'integer','material_type_id'=>'integer','is_active'=>'boolean','sequence'=>'integer'];
    public function category(): BelongsTo { return $this->belongsTo(MaterialCategory::class,'material_category_id'); }
    public function activity(): BelongsTo { return $this->belongsTo(Activity::class,'activity_id'); }
    public function materialType(): BelongsTo { return $this->belongsTo(MaterialType::class,'material_type_id'); }
    public function productMappings(): HasMany { return $this->hasMany(MaterialProductBrand::class,'brand_master_id'); }
    public function products(): BelongsToMany { return $this->belongsToMany(MaterialType::class,'material_product_brand','brand_master_id','material_type_id')->withPivot(['is_preferred','sort_order','is_active','remarks'])->withTimestamps()->orderByPivot('sort_order')->orderBy('material_types.material_type_name'); }
    public function activeProducts(): BelongsToMany { return $this->products()->wherePivot('is_active',true); }
    public function variantMappings(): HasMany { return $this->hasMany(MaterialVariantBrand::class,'brand_master_id'); }
    public function variants(): BelongsToMany { return $this->belongsToMany(MaterialVariant::class,'material_variant_brands','brand_master_id','material_variant_id')->withPivot(['is_preferred','sort_order','is_active','remarks'])->withTimestamps()->orderByPivot('sort_order')->orderBy('material_variants.variant_name'); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active',true); }
    public function scopeOrdered(Builder $query): Builder { return $query->orderBy('sequence')->orderBy('brand_name'); }
    public function getDisplayNameAttribute(): string { return $this->brand_name; }
}
