<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkPackage extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'sort_order',
        'is_system',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(
            WorkSection::class,
            'work_package_id'
        )
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(
            WorkActivity::class,
            'work_package_id'
        )
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function activeSections(): HasMany
    {
        return $this->sections()
            ->where('is_active', true);
    }

    public function activeActivities(): HasMany
    {
        return $this->activities()
            ->where('is_active', true)
            ->where('is_selectable', true);
    }
}