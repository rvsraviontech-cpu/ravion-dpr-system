<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialProductGroup extends Model
{
    protected $fillable = ['group_code','group_name','sort_order','is_active','remarks'];
    protected $casts = ['sort_order'=>'integer','is_active'=>'boolean'];
    public function productTypes(): HasMany { return $this->hasMany(MaterialProductType::class,'material_product_group_id'); }
    public function products(): HasMany { return $this->hasMany(MaterialType::class,'material_product_group_id'); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active',true); }
    public function scopeOrdered(Builder $query): Builder { return $query->orderBy('sort_order')->orderBy('group_name'); }
    public function getDisplayNameAttribute(): string { return $this->group_name; }
}
