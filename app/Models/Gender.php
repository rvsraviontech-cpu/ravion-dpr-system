<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gender extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
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

    /**
     * Scope: active genders only.
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