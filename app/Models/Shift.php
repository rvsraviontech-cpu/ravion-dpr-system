<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'start_time',
        'end_time',
        'normal_hours',
        'grace_in_minutes',
        'grace_out_minutes',
        'ot_start_time',
        'crosses_midnight',
        'is_system',
        'sort_order',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'normal_hours' => 'decimal:2',
        'grace_in_minutes' => 'integer',
        'grace_out_minutes' => 'integer',
        'crosses_midnight' => 'boolean',
        'is_system' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function labourAttendances(): HasMany
    {
        return $this->hasMany(
            LabourAttendance::class
        );
    }

    public function defaultLabours(): HasMany
    {
        return $this->hasMany(
            Labour::class,
            'default_shift_id'
        );
    }

    public function designationRoleDefaults(): HasMany
    {
        return $this->hasMany(
            DesignationRole::class,
            'default_shift_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(
        Builder $query
    ): Builder {
        return $query->where(
            'is_active',
            true
        );
    }

    public function scopeOrdered(
        Builder $query
    ): Builder {
        return $query
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function scopeCrossesMidnight(
        Builder $query
    ): Builder {
        return $query->where(
            'crosses_midnight',
            true
        );
    }

    public function scopeSystem(
        Builder $query
    ): Builder {
        return $query->where(
            'is_system',
            true
        );
    }

    public function scopeCustom(
        Builder $query
    ): Builder {
        return $query->where(
            'is_system',
            false
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Record Rules
    |--------------------------------------------------------------------------
    */

    public function canBeDeactivated(): bool
    {
        return ! $this->is_system;
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

    public function getTimingLabelAttribute(): string
    {
        if (! $this->start_time || ! $this->end_time) {
            return 'Timing not defined';
        }

        $label = $this->formatted_start_time
            . ' - '
            . $this->formatted_end_time;

        if ($this->crosses_midnight) {
            $label .= ' (Next Day)';
        }

        return $label;
    }

    public function getFormattedStartTimeAttribute(): ?string
    {
        return $this->formatTime(
            $this->start_time
        );
    }

    public function getFormattedEndTimeAttribute(): ?string
    {
        return $this->formatTime(
            $this->end_time
        );
    }

    public function getFormattedOtStartTimeAttribute(): ?string
    {
        return $this->formatTime(
            $this->effective_ot_start_time
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Attendance Rule Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * OT starts at the configured OT time.
     * If OT time is not configured, use shift end time.
     */
    public function getEffectiveOtStartTimeAttribute(): ?string
    {
        return $this->ot_start_time
            ?: $this->end_time;
    }

    /**
     * Return the shift start time in HH:MM format.
     */
    public function getStartTimeValueAttribute(): ?string
    {
        return $this->normaliseTime(
            $this->start_time
        );
    }

    /**
     * Return the shift end time in HH:MM format.
     */
    public function getEndTimeValueAttribute(): ?string
    {
        return $this->normaliseTime(
            $this->end_time
        );
    }

    /**
     * Return the effective OT start time in HH:MM format.
     */
    public function getOtStartTimeValueAttribute(): ?string
    {
        return $this->normaliseTime(
            $this->effective_ot_start_time
        );
    }

    /**
     * Values suitable for Blade and Alpine attendance forms.
     */
    public function attendanceRules(): array
    {
        return [
            'shift_id' => $this->id,
            'shift_code' => $this->code,
            'shift_name' => $this->name,

            'start_time' =>
                $this->start_time_value,

            'end_time' =>
                $this->end_time_value,

            'normal_hours' =>
                (float) $this->normal_hours,

            'grace_in_minutes' =>
                (int) $this->grace_in_minutes,

            'grace_out_minutes' =>
                (int) $this->grace_out_minutes,

            'ot_start_time' =>
                $this->ot_start_time_value,

            'crosses_midnight' =>
                (bool) $this->crosses_midnight,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Internal Time Helpers
    |--------------------------------------------------------------------------
    */

    private function normaliseTime(
        mixed $value
    ): ?string {
        if (blank($value)) {
            return null;
        }

        $value = (string) $value;

        return strlen($value) >= 5
            ? substr($value, 0, 5)
            : $value;
    }

    private function formatTime(
        mixed $value
    ): ?string {
        $value = $this->normaliseTime(
            $value
        );

        if ($value === null) {
            return null;
        }

        [$hour, $minute] = array_map(
            'intval',
            explode(':', $value)
        );

        $suffix = $hour >= 12
            ? 'PM'
            : 'AM';

        $displayHour = $hour % 12;

        if ($displayHour === 0) {
            $displayHour = 12;
        }

        return sprintf(
            '%02d:%02d %s',
            $displayHour,
            $minute,
            $suffix
        );
    }
}
