<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialCatalogItem extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_EXACT_MATCH = 'exact_match';
    public const STATUS_PROBABLE_VARIANT = 'probable_variant';
    public const STATUS_NEW_CANDIDATE = 'new_candidate';
    public const STATUS_REVIEW = 'review';

    protected $fillable = [
        'material_catalog_category_id',
        'material_catalog_subcategory_id',
        'source_key',
        'source_item_name',
        'normalized_name',
        'suggested_base_name',
        'variant_text',
        'suggested_unit_master_id',
        'matched_material_type_id',
        'match_status',
        'match_confidence',
        'analysis_notes',
        'source_sequence',
        'is_active',
    ];

    protected $casts = [
        'material_catalog_category_id' => 'integer',
        'material_catalog_subcategory_id' => 'integer',
        'suggested_unit_master_id' => 'integer',
        'matched_material_type_id' => 'integer',
        'match_confidence' => 'decimal:2',
        'source_sequence' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            MaterialCatalogCategory::class,
            'material_catalog_category_id'
        );
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(
            MaterialCatalogSubcategory::class,
            'material_catalog_subcategory_id'
        );
    }

    public function matchedMaterialType(): BelongsTo
    {
        return $this->belongsTo(
            MaterialType::class,
            'matched_material_type_id'
        );
    }

    public function suggestedUnit(): BelongsTo
    {
        return $this->belongsTo(
            UnitMaster::class,
            'suggested_unit_master_id'
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('match_status', self::STATUS_PENDING);
    }

    public function scopeNeedsReview(Builder $query): Builder
    {
        return $query->whereIn('match_status', [
            self::STATUS_PROBABLE_VARIANT,
            self::STATUS_REVIEW,
        ]);
    }
}
