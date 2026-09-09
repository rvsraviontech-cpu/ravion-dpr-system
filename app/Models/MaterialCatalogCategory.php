<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialCatalogCategory extends Model
{
    protected $fillable = [
        'code',
        'name',
        'sort_order',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function subcategories(): HasMany
    {
        return $this->hasMany(
            MaterialCatalogSubcategory::class,
            'material_catalog_category_id'
        )
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function activeSubcategories(): HasMany
    {
        return $this->subcategories()
            ->where('is_active', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('name');
    }
}
