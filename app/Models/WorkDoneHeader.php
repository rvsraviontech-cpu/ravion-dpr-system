<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkDoneHeader extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'work_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'work_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function engineer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            WorkDoneItem::class,
            'work_done_header_id'
        )
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function getWorkCountAttribute(): int
    {
        if ($this->relationLoaded('items')) {
            return $this->items->count();
        }

        return $this->items()->count();
    }

    public function getLinkedWorkCountAttribute(): int
    {
        if ($this->relationLoaded('items')) {
            return $this->items
                ->whereNotNull('dpr_id')
                ->count();
        }

        return $this->items()
            ->whereNotNull('dpr_id')
            ->count();
    }

    public function getUnlinkedWorkCountAttribute(): int
    {
        if ($this->relationLoaded('items')) {
            return $this->items
                ->whereNull('dpr_id')
                ->count();
        }

        return $this->items()
            ->whereNull('dpr_id')
            ->count();
    }

    public function getIsFullyLinkedAttribute(): bool
    {
        $count = $this->work_count;

        return $count > 0
            && $this->linked_work_count === $count;
    }
}