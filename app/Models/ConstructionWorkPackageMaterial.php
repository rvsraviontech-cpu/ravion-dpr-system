<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConstructionWorkPackageMaterial extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    |
    | This model represents the enriched many-to-many mapping between
    | Construction Work Packages and reusable Material Types.
    |
    | The table predates this dedicated model and intentionally retains
    | its existing name for backward compatibility.
    |
    */

    protected $table = 'construction_work_package_material_type';

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'construction_work_package_id',
        'material_type_id',
        'is_preferred',
        'sort_order',
        'is_active',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'construction_work_package_id' => 'integer',
        'material_type_id' => 'integer',
        'is_preferred' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function workPackage(): BelongsTo
    {
        return $this->belongsTo(
            ConstructionWorkPackage::class,
            'construction_work_package_id'
        );
    }

    public function materialType(): BelongsTo
    {
        return $this->belongsTo(
            MaterialType::class,
            'material_type_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function scopePreferred(Builder $query): Builder
    {
        return $query->where('is_preferred', true);
    }

    public function scopeForWorkPackage(
        Builder $query,
        int $workPackageId
    ): Builder {
        return $query->where(
            'construction_work_package_id',
            $workPackageId
        );
    }

    public function scopeForMaterialType(
        Builder $query,
        int $materialTypeId
    ): Builder {
        return $query->where(
            'material_type_id',
            $materialTypeId
        );
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByDesc('is_active')
            ->orderByDesc('is_preferred')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getDisplayNameAttribute(): string
    {
        $packageName = $this->workPackage?->name
            ?? 'Work Package #' . $this->construction_work_package_id;

        $materialName = $this->materialType?->material_type_name
            ?? 'Material Type #' . $this->material_type_id;

        return $packageName . ' — ' . $materialName;
    }

    public function getIsAvailableForTransactionsAttribute(): bool
    {
        return $this->is_active
            && (bool) $this->workPackage?->is_active
            && (bool) $this->materialType?->is_active;
    }
}