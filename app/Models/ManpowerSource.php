<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManpowerSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'requires_contractor',
        'is_system',
        'sort_order',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'requires_contractor' => 'boolean',
        'is_system' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Scope: active manpower sources only.
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
     * Scope: sources that require contractor selection.
     */
    public function scopeRequiresContractor(Builder $query): Builder
    {
        return $query->where('requires_contractor', true);
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