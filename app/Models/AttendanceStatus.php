<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'short_name',
        'counts_as_present',
        'counts_as_absent',
        'payable_factor',
        'allows_normal_hours',
        'allows_ot_hours',
        'requires_working_status',
        'is_system',
        'sort_order',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'counts_as_present'      => 'boolean',
        'counts_as_absent'       => 'boolean',
        'payable_factor'         => 'decimal:2',
        'allows_normal_hours'    => 'boolean',
        'allows_ot_hours'        => 'boolean',
        'requires_working_status'=> 'boolean',
        'is_system'              => 'boolean',
        'sort_order'             => 'integer',
        'is_active'              => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function attendanceDetails(): HasMany
    {
        return $this->hasMany(
            LabourAttendanceDetail::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Active attendance statuses only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Statuses that count towards present manpower.
     */
    public function scopeCountsAsPresent(Builder $query): Builder
    {
        return $query->where('counts_as_present', true);
    }

    /**
     * Statuses that count towards absent manpower.
     */
    public function scopeCountsAsAbsent(Builder $query): Builder
    {
        return $query->where('counts_as_absent', true);
    }

    /**
     * Default ordering.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    /*
    |--------------------------------------------------------------------------
    | Business Rules
    |--------------------------------------------------------------------------
    */

    public function canBeDeactivated(): bool
    {
        return ! $this->is_system;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function isPresentStatus(): bool
    {
        return $this->counts_as_present;
    }

    public function isAbsentStatus(): bool
    {
        return $this->counts_as_absent;
    }

    public function allowsHours(): bool
    {
        return $this->allows_normal_hours;
    }

    public function allowsOvertime(): bool
    {
        return $this->allows_ot_hours;
    }

    /*
    |--------------------------------------------------------------------------
    | Display Helpers
    |--------------------------------------------------------------------------
    */

    public function getDisplayNameAttribute(): string
    {
        return "{$this->code} - {$this->name}";
    }
}