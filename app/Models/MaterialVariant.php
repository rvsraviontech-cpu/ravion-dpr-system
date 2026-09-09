<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialVariant extends Model
{
    use HasFactory;
    protected $fillable=['material_type_id','variant_code','variant_name','material_specification_id','material_grade_id','unit_master_id','size_dimension','finish','colour_shade','search_aliases','sort_order','is_active','remarks'];
    protected $casts=['material_type_id'=>'integer','material_specification_id'=>'integer','material_grade_id'=>'integer','unit_master_id'=>'integer','sort_order'=>'integer','is_active'=>'boolean'];
    public function materialType(): BelongsTo { return $this->belongsTo(MaterialType::class,'material_type_id'); }
    public function specification(): BelongsTo { return $this->belongsTo(MaterialSpecification::class,'material_specification_id'); }
    public function grade(): BelongsTo { return $this->belongsTo(MaterialGrade::class,'material_grade_id'); }
    public function unit(): BelongsTo { return $this->belongsTo(UnitMaster::class,'unit_master_id'); }
    public function brandMappings(): HasMany { return $this->hasMany(MaterialVariantBrand::class,'material_variant_id'); }
    public function brands(): BelongsToMany { return $this->belongsToMany(BrandMaster::class,'material_variant_brands','material_variant_id','brand_master_id')->withPivot(['is_preferred','sort_order','is_active','remarks'])->withTimestamps()->orderByPivot('sort_order')->orderBy('brand_masters.brand_name'); }
    public function activeBrands(): BelongsToMany { return $this->brands()->wherePivot('is_active',true); }
    public function searchAliases(): HasMany { return $this->hasMany(MaterialSearchAlias::class,'material_variant_id'); }
    public function usageMappings(): HasMany { return $this->hasMany(MaterialProductUsageMapping::class,'material_variant_id'); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active',true); }
    public function scopeOrdered(Builder $query): Builder { return $query->orderBy('sort_order')->orderBy('variant_name'); }
    public function getDisplayNameAttribute(): string { $parts=array_filter([$this->variant_name,$this->specification?->specification_name,$this->size_dimension,$this->grade?->grade_name,$this->finish,$this->colour_shade]); return implode(' — ',array_values(array_unique($parts))); }
}
