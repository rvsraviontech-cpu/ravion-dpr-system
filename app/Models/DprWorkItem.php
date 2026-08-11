<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DprWorkItem extends Model
{
    protected $fillable = [
        'dpr_id',
        'project_id',
        'user_id',
        'work_date',
        'activity_id',
        'activity_mapping_id',
        'project_block_id',
        'project_floor_id',
        'project_unit_id',
        'project_room_id',
        'project_subspace_id',
        'contractor_id',
        'quantity_completed',
        'remarks',
        'status',
    ];

    protected $casts = [
        'work_date' => 'date',
        'quantity_completed' => 'decimal:3',
    ];

    public function dpr(): BelongsTo
    {
        return $this->belongsTo(Dpr::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function engineer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function activityMapping(): BelongsTo
    {
        return $this->belongsTo(ActivityMapping::class);
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(ProjectBlock::class, 'project_block_id');
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(ProjectFloor::class, 'project_floor_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProjectUnit::class, 'project_unit_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(ProjectRoom::class, 'project_room_id');
    }

    public function subspace(): BelongsTo
    {
        return $this->belongsTo(ProjectSubspace::class, 'project_subspace_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(DprWorkPhoto::class, 'dpr_work_item_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function getActivityDivisionNameAttribute(): ?string
    {
        return $this->activityMapping?->division?->name
            ?? $this->activityMapping?->division?->division_name;
    }

    public function getActivityNameAttribute(): ?string
    {
        return $this->activityMapping?->activity_name
            ?? $this->activity?->activity_name;
    }

    public function getUnitNameAttribute(): ?string
    {
        return $this->activityMapping?->unit
            ?? $this->activity?->unit;
    }

    public function getLocationPathAttribute(): string
    {
        return collect([
            $this->block?->name,
            $this->floor?->name,
            $this->unit?->name,
            $this->room?->name,
            $this->subspace?->name,
        ])->filter()->implode(' → ');
    }

    public function getIsDprLinkedAttribute(): bool
    {
        return ! empty($this->dpr_id);
    }
}
