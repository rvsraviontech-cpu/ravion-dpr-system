<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteIssue extends Model
{
    protected $fillable = [
        'dpr_id',
        'project_id',
        'project_block_id',
        'project_floor_id',
        'project_unit_id',
        'project_room_id',
        'project_subspace_id',
        'activity_id',
        'issue_date',
        'issue_type',
        'title',
        'related_activity',
        'description',
        'root_cause',
        'responsible_person',
        'target_closure_date',
        'actual_closure_date',
        'priority',
        'status',
        'escalated_to_pmo',
        'escalated_to_management',
        'resolution',
        'created_by',
        'remarks',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'target_closure_date' => 'date',
        'actual_closure_date' => 'date',
        'escalated_to_pmo' => 'boolean',
        'escalated_to_management' => 'boolean',
    ];

    public function dpr(): BelongsTo
    {
        return $this->belongsTo(Dpr::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(
            ProjectBlock::class,
            'project_block_id'
        );
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(
            ProjectFloor::class,
            'project_floor_id'
        );
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(
            ProjectUnit::class,
            'project_unit_id'
        );
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(
            ProjectRoom::class,
            'project_room_id'
        );
    }

    public function subspace(): BelongsTo
    {
        return $this->belongsTo(
            ProjectSubspace::class,
            'project_subspace_id'
        );
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function photos(): HasMany
    {
        return $this->hasMany(
            SiteIssuePhoto::class,
            'site_issue_id'
        )
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function getLocationPathAttribute(): string
    {
        return collect([
            $this->block?->name,
            $this->floor?->name,
            $this->unit?->name,
            $this->room?->name,
            $this->subspace?->name,
        ])
            ->filter()
            ->implode(' → ');
    }

    public function getIsDprLinkedAttribute(): bool
    {
        return ! empty($this->dpr_id);
    }

    public function getPhotoCountAttribute(): int
    {
        if ($this->relationLoaded('photos')) {
            return $this->photos->count();
        }

        return $this->photos()->count();
    }
}
