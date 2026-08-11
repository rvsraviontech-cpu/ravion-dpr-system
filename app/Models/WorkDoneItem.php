<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkDoneItem extends Model
{
    protected $fillable = [
        'work_done_header_id',
        'dpr_id',

        'work_stage_id',
        'activity_division_id',
        'activity_id',
        'activity_mapping_id',
        'contractor_id',

        'project_block_id',
        'project_floor_id',
        'project_unit_id',
        'project_room_id',
        'project_subspace_id',

        'quantity_completed',
        'unit',
        'progress_percentage',
        'execution_status',
        'remarks',
        'sort_order',
    ];

    protected $casts = [
        'quantity_completed' => 'decimal:3',
        'progress_percentage' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(
            WorkDoneHeader::class,
            'work_done_header_id'
        );
    }

    public function dpr(): BelongsTo
    {
        return $this->belongsTo(Dpr::class);
    }

    public function workStage(): BelongsTo
    {
        return $this->belongsTo(
            WorkStage::class,
            'work_stage_id'
        );
    }

    public function activityDivision(): BelongsTo
    {
        return $this->belongsTo(
            ActivityDivision::class,
            'activity_division_id'
        );
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function activityMapping(): BelongsTo
    {
        return $this->belongsTo(
            ActivityMapping::class,
            'activity_mapping_id'
        );
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(
            Contractor::class,
            'contractor_id'
        );
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

    public function unitLocation(): BelongsTo
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

    public function labours(): HasMany
    {
        return $this->hasMany(
            WorkDoneItemLabour::class,
            'work_done_item_id'
        )
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function materialConsumptions(): HasMany
    {
        return $this->hasMany(
            MaterialConsumed::class,
            'work_done_item_id'
        )
            ->orderBy('consumed_date')
            ->orderBy('id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(
            DprWorkPhoto::class,
            'work_done_item_id'
        )
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function getLocationPathAttribute(): string
    {
        return collect([
            $this->block?->name,
            $this->floor?->name,
            $this->unitLocation?->name,
            $this->room?->name,
            $this->subspace?->name,
        ])
            ->filter()
            ->implode(' → ');
    }

    public function getActivityNameAttribute(): ?string
    {
        return $this->activityMapping?->activity_name
            ?? $this->activity?->activity_name;
    }

    public function getDisplayQuantityAttribute(): string
    {
        $quantity = rtrim(
            rtrim(
                number_format(
                    (float) $this->quantity_completed,
                    3,
                    '.',
                    ''
                ),
                '0'
            ),
            '.'
        );

        return trim(
            $quantity . ' ' . ($this->unit ?? '')
        );
    }

    public function getLabourCountAttribute(): int
    {
        if ($this->relationLoaded('labours')) {
            return (int) $this->labours->sum('quantity');
        }

        return (int) $this->labours()->sum('quantity');
    }

    public function getMaterialCountAttribute(): int
    {
        if ($this->relationLoaded('materialConsumptions')) {
            return $this->materialConsumptions->count();
        }

        return $this->materialConsumptions()->count();
    }

    public function getPhotoCountAttribute(): int
    {
        if ($this->relationLoaded('photos')) {
            return $this->photos->count();
        }

        return $this->photos()->count();
    }

    public function getIsDprLinkedAttribute(): bool
    {
        return ! empty($this->dpr_id);
    }

    public function isEditable(): bool
    {
        return $this->dpr_id === null;
    }
}