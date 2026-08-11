<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Activity extends Model
{
    protected $fillable = [
        'activity_division_id',
        'activity_name',
        'unit',
        'work_stage',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function division()
    {
        return $this->belongsTo(
            ActivityDivision::class,
            'activity_division_id'
        );
    }

    public function mapping(): HasOne
    {
        return $this->hasOne(ActivityMapping::class);
    }

    public function materialMappings(): HasMany
    {
        return $this->hasMany(
            ActivityMaterialMapping::class,
            'activity_id'
        );
    }

    public function activeMaterialMappings(): HasMany
    {
        return $this->materialMappings()
            ->where('is_active', true);
    }

    public function materialSpecifications(): HasMany
    {
        return $this->hasMany(
            MaterialSpecification::class,
            'activity_id'
        );
    }

    public function activeMaterialSpecifications(): HasMany
    {
        return $this->materialSpecifications()
            ->where('is_active', true)
            ->orderBy('sequence')
            ->orderBy('specification_name');
    }
}