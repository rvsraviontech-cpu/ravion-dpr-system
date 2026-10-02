<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkSection extends Model
{
    protected $fillable = [
        'work_package_id',
        'code',
        'name',
        'description',
        'sort_order',
        'is_system',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'work_package_id' => 'integer',
        'sort_order' => 'integer',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(
            WorkPackage::class,
            'work_package_id'
        );
    }

    public function activities(): HasMany
    {
        return $this->hasMany(
            WorkActivity::class,
            'work_section_id'
        )
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function activeActivities(): HasMany
    {
        return $this->activities()
            ->where('is_active', true)
            ->where('is_selectable', true);
    }
}