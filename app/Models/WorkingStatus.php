<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkingStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'counts_as_idle',
        'requires_reason',
        'is_system',
        'sort_order',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'counts_as_idle' => 'boolean',
        'requires_reason' => 'boolean',
        'is_system' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Scope: active working statuses only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: default ordering for lists and dropdowns.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    /**
     * Scope: statuses that should count as idle manpower.
     */
    public function scopeCountsAsIdle(Builder $query): Builder
    {
        return $query->where('counts_as_idle', true);
    }

    /**
     * Scope: statuses that require a reason or remarks.
     */
    public function scopeRequiresReason(Builder $query): Builder
    {
        return $query->where('requires_reason', true);
    }

    /**
     * Prevent protected system records from being deactivated.
     */
    public function canBeDeactivated(): bool
    {
        return ! $this->is_system;
    }

    /**
     * Human-readable dropdown label.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->code} - {$this->name}";
    }
}