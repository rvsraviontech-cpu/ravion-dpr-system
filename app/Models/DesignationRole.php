<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignationRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'labour_type_id',
        'skill_category_id',
        'default_shift_id',
        'default_normal_shift_hours',
        'default_wage_basis',
        'default_daily_rate',
        'default_hourly_rate',
        'default_monthly_rate',
        'default_ot_calculation_type',
        'default_ot_rate',
        'default_ot_multiplier',
        'is_system',
        'sort_order',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'labour_type_id' => 'integer',
        'skill_category_id' => 'integer',
        'default_shift_id' => 'integer',

        'default_normal_shift_hours' => 'decimal:2',
        'default_daily_rate' => 'decimal:2',
        'default_hourly_rate' => 'decimal:2',
        'default_monthly_rate' => 'decimal:2',
        'default_ot_rate' => 'decimal:2',
        'default_ot_multiplier' => 'decimal:2',

        'is_system' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'default_daily_rate',
        'default_hourly_rate',
        'default_monthly_rate',
        'default_ot_rate',
        'default_ot_multiplier',
    ];

    /**
     * Trade / Manpower Category relationship.
     */
    public function labourType(): BelongsTo
    {
        return $this->belongsTo(LabourType::class);
    }

    /**
     * Skill Category relationship.
     */
    public function skillCategory(): BelongsTo
    {
        return $this->belongsTo(SkillCategory::class);
    }

    /**
     * Default Shift relationship.
     */
    public function defaultShift(): BelongsTo
    {
        return $this->belongsTo(
            Shift::class,
            'default_shift_id'
        );
    }

    /**
     * Scope: active designation roles only.
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
     * Scope: designation roles for a specific Labour Type.
     */
    public function scopeForLabourType(
        Builder $query,
        ?int $labourTypeId
    ): Builder {
        if ($labourTypeId === null) {
            return $query;
        }

        return $query->where(
            'labour_type_id',
            $labourTypeId
        );
    }

    /**
     * Scope: designation roles for a specific Skill Category.
     */
    public function scopeForSkillCategory(
        Builder $query,
        ?int $skillCategoryId
    ): Builder {
        if ($skillCategoryId === null) {
            return $query;
        }

        return $query->where(
            'skill_category_id',
            $skillCategoryId
        );
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

    /**
     * Return the currently applicable default wage rate.
     */
    public function getDefaultRateAttribute(): ?string
    {
        return match ($this->default_wage_basis) {
            'daily' => $this->default_daily_rate,
            'hourly' => $this->default_hourly_rate,
            'monthly' => $this->default_monthly_rate,
            default => null,
        };
    }

    /**
     * Determine whether the designation has a valid skill mapping.
     */
    public function hasSkillMapping(): bool
    {
        return $this->skill_category_id !== null;
    }

    /**
     * Determine whether the designation has a default shift.
     */
    public function hasDefaultShift(): bool
    {
        return $this->default_shift_id !== null;
    }
}