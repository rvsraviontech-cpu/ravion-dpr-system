<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConstructionWorkPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'activity_division_id',
        'sort_order',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Hierarchy
    |--------------------------------------------------------------------------
    */

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            ConstructionWorkPackage::class,
            'parent_id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            ConstructionWorkPackage::class,
            'parent_id'
        )->orderBy('sort_order')
            ->orderBy('name');
    }

    public function activeChildren(): HasMany
    {
        return $this->children()
            ->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Existing execution architecture
    |--------------------------------------------------------------------------
    */

    public function activityDivision(): BelongsTo
    {
        return $this->belongsTo(
            ActivityDivision::class,
            'activity_division_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Material mappings
    |--------------------------------------------------------------------------
    */

    public function materialTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            MaterialType::class,
            'construction_work_package_material_type',
            'construction_work_package_id',
            'material_type_id'
        )
            ->withPivot([
                'is_preferred',
                'sort_order',
                'is_active',
            ])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function activeMaterialTypes(): BelongsToMany
    {
        return $this->materialTypes()
            ->wherePivot('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Query scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeChild(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getIsRootAttribute(): bool
    {
        return is_null($this->parent_id);
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->name . ' — ' . $this->name;
        }

        return $this->name;
    }
}