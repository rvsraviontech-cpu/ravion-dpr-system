<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkProjectType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_general',
        'sort_order',
        'is_system',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'is_general' => 'boolean',
        'sort_order' => 'integer',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function activityLinks(): HasMany
    {
        return $this->hasMany(
            WorkActivityProjectType::class,
            'work_project_type_id'
        );
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(
            WorkActivity::class,
            'work_activity_project_types',
            'work_project_type_id',
            'work_activity_id'
        )
            ->withTimestamps();
    }
}