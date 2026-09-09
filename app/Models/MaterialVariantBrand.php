<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialVariantBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_variant_id',
        'brand_master_id',
        'is_preferred',
        'sort_order',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'is_preferred' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(
            MaterialVariant::class,
            'material_variant_id'
        );
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(
            BrandMaster::class,
            'brand_master_id'
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByDesc('is_preferred')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
