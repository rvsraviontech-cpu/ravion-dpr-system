<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MaterialCatalogSubcategory extends Model
{
    protected $fillable = [
        'material_catalog_category_id',
        'code',
        'name',
        'sort_order',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'material_catalog_category_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            MaterialCatalogCategory::class,
            'material_catalog_category_id'
        );
    }

    public function materialTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            MaterialType::class,
            'material_catalog_subcategory_material_type',
            'material_catalog_subcategory_id',
            'material_type_id'
        )
            ->withPivot([
                'is_preferred',
                'sort_order',
                'is_active',
            ])
            ->withTimestamps();
    }

    public function activeMaterialTypes(): BelongsToMany
    {
        return $this->materialTypes()
            ->wherePivot('is_active', true)
            ->where('material_types.is_active', true)
            ->orderByPivot('sort_order')
            ->orderBy('material_types.material_type_name');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForCategory(
        Builder $query,
        int $categoryId
    ): Builder {
        return $query->where(
            'material_catalog_category_id',
            $categoryId
        );
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('name');
    }
}
