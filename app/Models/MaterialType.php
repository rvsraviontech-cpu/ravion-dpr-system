<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialType extends Model
{
    protected $fillable=['material_group','material_product_group_id','material_product_type_id','material_type_name','material_type_code','catalogue_source_code','inventory_type','master_status','is_legacy','unit_master_id','sequence','is_active','remarks','created_by'];
    protected $casts=['material_product_group_id'=>'integer','material_product_type_id'=>'integer','unit_master_id'=>'integer','sequence'=>'integer','is_active'=>'boolean','is_legacy'=>'boolean'];
    public function productGroup(): BelongsTo { return $this->belongsTo(MaterialProductGroup::class,'material_product_group_id'); }
    public function productType(): BelongsTo { return $this->belongsTo(MaterialProductType::class,'material_product_type_id'); }
    public function unit(): BelongsTo { return $this->belongsTo(UnitMaster::class,'unit_master_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
    public function specifications(): HasMany { return $this->hasMany(MaterialSpecification::class,'material_type_id'); }
    public function grades(): HasMany { return $this->hasMany(MaterialGrade::class,'material_type_id'); }
    public function variants(): HasMany { return $this->hasMany(MaterialVariant::class,'material_type_id'); }
    public function brands(): HasMany { return $this->hasMany(BrandMaster::class,'material_type_id'); }
    public function legacyBrands(): HasMany { return $this->brands(); }
    public function productBrandMappings(): HasMany { return $this->hasMany(MaterialProductBrand::class,'material_type_id'); }
    public function canonicalBrands(): BelongsToMany { return $this->belongsToMany(BrandMaster::class,'material_product_brand','material_type_id','brand_master_id')->withPivot(['is_preferred','sort_order','is_active','remarks'])->withTimestamps()->orderByPivot('sort_order')->orderBy('brand_masters.brand_name'); }
    public function activeCanonicalBrands(): BelongsToMany { return $this->canonicalBrands()->wherePivot('is_active',true); }
    public function searchAliases(): HasMany { return $this->hasMany(MaterialSearchAlias::class,'material_type_id'); }
    public function usageMappings(): HasMany { return $this->hasMany(MaterialProductUsageMapping::class,'material_type_id'); }
    public function activeUsageMappings(): HasMany { return $this->usageMappings()->where('is_active',true); }
    public function workPackages(): BelongsToMany { return $this->belongsToMany(ConstructionWorkPackage::class,'construction_work_package_material_type','material_type_id','construction_work_package_id')->withPivot(['is_preferred','sort_order','is_active'])->withTimestamps()->orderByPivot('sort_order'); }
    public function activeWorkPackages(): BelongsToMany { return $this->workPackages()->wherePivot('is_active',true); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active',true); }
    public function scopeApproved(Builder $query): Builder { return $query->where('master_status','Approved'); }
    public function scopeCanonical(Builder $query): Builder { return $query->where('is_legacy',false); }
    public function scopeLegacy(Builder $query): Builder { return $query->where('is_legacy',true); }
    public function scopeForMaterialGroup(Builder $query,string $materialGroup): Builder { return $query->where('material_group',$materialGroup); }
    public function scopeForProductGroup(Builder $query,int $productGroupId): Builder { return $query->where('material_product_group_id',$productGroupId); }
    public function scopeForProductType(Builder $query,int $productTypeId): Builder { return $query->where('material_product_type_id',$productTypeId); }
    public function scopeOrdered(Builder $query): Builder { return $query->orderBy('material_product_group_id')->orderBy('material_product_type_id')->orderBy('material_group')->orderBy('sequence')->orderBy('material_type_name'); }
    public function getDisplayNameAttribute(): string { return $this->material_type_name; }
    public function getFullDisplayNameAttribute(): string { $classification=$this->productType?->type_name ?? $this->productGroup?->group_name ?? $this->material_group; return $classification ? $classification.' — '.$this->material_type_name : $this->material_type_name; }
}
