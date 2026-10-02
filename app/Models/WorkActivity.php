<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkActivity extends Model
{
    protected $fillable = [
        'work_package_id',
        'work_section_id',
        'legacy_activity_id',
        'code',
        'name',
        'description',
        'default_unit',
        'allow_materials',
        'allow_labour',
        'allow_equipment',
        'allow_photos',
        'material_calculation_enabled',
        'is_selectable',
        'is_system',
        'is_active',
        'sort_order',
        'remarks',
    ];

    protected $casts = [
        'work_package_id' => 'integer',
        'work_section_id' => 'integer',
        'legacy_activity_id' => 'integer',

        'allow_materials' => 'boolean',
        'allow_labour' => 'boolean',
        'allow_equipment' => 'boolean',
        'allow_photos' => 'boolean',

        'material_calculation_enabled' => 'boolean',
        'is_selectable' => 'boolean',
        'is_system' => 'boolean',
        'is_active' => 'boolean',

        'sort_order' => 'integer',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(
            WorkPackage::class,
            'work_package_id'
        );
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(
            WorkSection::class,
            'work_section_id'
        );
    }

    public function legacyActivity(): BelongsTo
    {
        return $this->belongsTo(
            Activity::class,
            'legacy_activity_id'
        );
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(
            WorkActivityAlias::class,
            'work_activity_id'
        )
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('alias');
    }

    public function projectTypeLinks(): HasMany
    {
        return $this->hasMany(
            WorkActivityProjectType::class,
            'work_activity_id'
        );
    }

    public function projectTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            WorkProjectType::class,
            'work_activity_project_types',
            'work_activity_id',
            'work_project_type_id'
        )
            ->withTimestamps();
    }

    public function getFullNameAttribute(): string
    {
        return collect([
            $this->package?->name,
            $this->section?->name,
            $this->name,
        ])
            ->filter()
            ->implode(' → ');
    }
}