<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialProductType extends Model
{
    protected $fillable=['material_product_group_id','type_code','type_name','sort_order','is_active','remarks'];
    protected $casts=['material_product_group_id'=>'integer','sort_order'=>'integer','is_active'=>'boolean'];
    public function productGroup(): BelongsTo { return $this->belongsTo(MaterialProductGroup::class,'material_product_group_id'); }
    public function products(): HasMany { return $this->hasMany(MaterialType::class,'material_product_type_id'); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active',true); }
    public function scopeOrdered(Builder $query): Builder { return $query->orderBy('sort_order')->orderBy('type_name'); }
    public function getDisplayNameAttribute(): string { return $this->type_name; }
    public function getFullDisplayNameAttribute(): string { return $this->productGroup?->group_name ? $this->productGroup->group_name.' — '.$this->type_name : $this->type_name; }
}
