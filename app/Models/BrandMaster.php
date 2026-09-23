<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrandMaster extends Model
{
    protected $fillable = [
        'brand_segment_id',
        'material_category_id',
        'activity_id',
        'material_type_id',
        'brand_name',
        'brand_code',
        'sequence',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'brand_segment_id' => 'integer',
        'material_category_id' => 'integer',
        'activity_id' => 'integer',
        'material_type_id' => 'integer',
        'is_active' => 'boolean',
        'sequence' => 'integer',
    ];

    /**
     * Canonical Brand Segment.
     */
    public function segment(): BelongsTo
    {
        return $this->belongsTo(
            BrandSegment::class,
            'brand_segment_id'
        );
    }

    /**
     * Legacy Material Category relationship.
     *
     * Retained for historical compatibility.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(
            MaterialCategory::class,
            'material_category_id'
        );
    }

    /**
     * Legacy Activity relationship.
     *
     * Retained for historical compatibility.
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(
            Activity::class,
            'activity_id'
        );
    }

    /**
     * Legacy Material Type relationship.
     *
     * New Brand records should use Brand Segment for identity
     * and Product associations through material_product_brand.
     */
    public function materialType(): BelongsTo
    {
        return $this->belongsTo(
            MaterialType::class,
            'material_type_id'
        );
    }

    /**
     * Canonical Product ↔ Brand mappings.
     */
    public function productMappings(): HasMany
    {
        return $this->hasMany(
            MaterialProductBrand::class,
            'brand_master_id'
        );
    }

    /**
     * Products associated with this Brand.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            MaterialType::class,
            'material_product_brand',
            'brand_master_id',
            'material_type_id'
        )
            ->withPivot([
                'is_preferred',
                'sort_order',
                'is_active',
                'remarks',
            ])
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->orderBy('material_types.material_type_name');
    }

    /**
     * Active Product associations only.
     */
    public function activeProducts(): BelongsToMany
    {
        return $this->products()
            ->wherePivot('is_active', true);
    }

    /**
     * Product Variant ↔ Brand mappings.
     */
    public function variantMappings(): HasMany
    {
        return $this->hasMany(
            MaterialVariantBrand::class,
            'brand_master_id'
        );
    }

    /**
     * Product Variants associated with this Brand.
     */
    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(
            MaterialVariant::class,
            'material_variant_brands',
            'brand_master_id',
            'material_variant_id'
        )
            ->withPivot([
                'is_preferred',
                'sort_order',
                'is_active',
                'remarks',
            ])
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->orderBy('material_variants.variant_name');
    }

    /**
     * Active Brands scope.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Standard Brand ordering.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sequence')
            ->orderBy('brand_name');
    }

    /**
     * Default Brand display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->brand_name;
    }
}