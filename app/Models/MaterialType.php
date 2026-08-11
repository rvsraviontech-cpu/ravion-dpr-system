<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class MaterialType extends Model
{
    protected $fillable = [
        'activity_division_id',
        'activity_id',
        'material_type_name',
        'material_type_code',
        'unit_master_id',
        'sequence',
        'is_active',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sequence' => 'integer',
    ];

    /**
     * Material Category
     */
    public function activityDivision(): BelongsTo
    {
        return $this->belongsTo(
            ActivityDivision::class,
            'activity_division_id'
        );
    }

    /**
     * Material
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(
            Activity::class,
            'activity_id'
        );
    }

    /**
     * Default Unit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(
            UnitMaster::class,
            'unit_master_id'
        );
    }

    /**
     * Creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /**
     * Specifications
     */
    public function specifications(): HasMany
    {
        return $this->hasMany(
            MaterialSpecification::class
        );
    }

    /**
     * Brands
     */
    public function brands(): HasMany
    {
        return $this->hasMany(
            BrandMaster::class
        );
    }

    /**
     * Grades
     */
   public function grades(): HasMany
{
    return $this->hasMany(
        MaterialGrade::class,
        'material_type_id'
    );
}

    /**
     * Display Name
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->material_type_name;
    }
}