<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Labour extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'labour_code',
        'full_name',
        'mobile',
        'alternate_mobile',
        'gender_id',
        'date_of_birth',
        'photo_path',
        'identity_type',
        'identity_number',
        'address',
        'emergency_contact_name',
        'emergency_contact_mobile',
        'manpower_source_id',
        'labour_group_id',
        'labour_category_id',
        'labour_type_id',
        'skill_category_id',
        'designation_role_id',
        'default_shift_id',
        'contractor_id',
        'current_project_id',
        'joining_date',
        'exit_date',
        'employment_status',
        'residency_status',
        'wage_basis',
        'current_daily_rate',
        'current_hourly_rate',
        'current_monthly_rate',
        'ot_calculation_type',
        'current_ot_rate',
        'ot_multiplier',
        'normal_shift_hours',
        'is_active',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'gender_id' => 'integer',
        'manpower_source_id' => 'integer',
        'labour_group_id' => 'integer',
        'labour_category_id' => 'integer',
        'labour_type_id' => 'integer',
        'skill_category_id' => 'integer',
        'designation_role_id' => 'integer',
        'default_shift_id' => 'integer',
        'contractor_id' => 'integer',
        'current_project_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',

        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'exit_date' => 'date',

        'current_daily_rate' => 'decimal:2',
        'current_hourly_rate' => 'decimal:2',
        'current_monthly_rate' => 'decimal:2',
        'current_ot_rate' => 'decimal:2',
        'ot_multiplier' => 'decimal:2',
        'normal_shift_hours' => 'decimal:2',

        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'current_daily_rate',
        'current_hourly_rate',
        'current_monthly_rate',
        'current_ot_rate',
        'ot_multiplier',
    ];

    /*
    |--------------------------------------------------------------------------
    | Master Relationships
    |--------------------------------------------------------------------------
    */

    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class);
    }

    public function manpowerSource(): BelongsTo
    {
        return $this->belongsTo(ManpowerSource::class);
    }

    public function labourGroup(): BelongsTo
    {
        return $this->belongsTo(LabourGroup::class);
    }

    public function labourCategory(): BelongsTo
    {
        return $this->belongsTo(LabourCategory::class);
    }

    public function labourType(): BelongsTo
    {
        return $this->belongsTo(LabourType::class);
    }

    public function skillCategory(): BelongsTo
    {
        return $this->belongsTo(SkillCategory::class);
    }

    public function designationRole(): BelongsTo
    {
        return $this->belongsTo(DesignationRole::class);
    }

    public function defaultShift(): BelongsTo
    {
        return $this->belongsTo(
            Shift::class,
            'default_shift_id'
        );
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    public function currentProject(): BelongsTo
    {
        return $this->belongsTo(
            Project::class,
            'current_project_id'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Labour Attendance Relationships
    |--------------------------------------------------------------------------
    */

    public function attendanceDetails(): HasMany
    {
        return $this->hasMany(
            LabourAttendanceDetail::class
        );
    }

    public function activeAttendanceDetails(): HasMany
    {
        return $this->attendanceDetails()
            ->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('full_name')
            ->orderBy('labour_code');
    }

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        if (! $search) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($search) {
            $builder
                ->where('labour_code', 'like', "%{$search}%")
                ->orWhere('full_name', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")
                ->orWhere('identity_number', 'like', "%{$search}%");
        });
    }

    public function scopeForProject(
        Builder $query,
        ?int $projectId
    ): Builder {
        if ($projectId === null) {
            return $query;
        }

        return $query->where(
            'current_project_id',
            $projectId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Attendance Helpers
    |--------------------------------------------------------------------------
    */

    public function latestAttendanceDetail(): ?LabourAttendanceDetail
    {
        return $this->attendanceDetails()
            ->with([
                'attendance',
                'attendanceStatus',
            ])
            ->latest('id')
            ->first();
    }

    public function attendanceCount(): int
    {
        return $this->attendanceDetails()->count();
    }

    public function presentAttendanceCount(): int
    {
        return $this->attendanceDetails()
            ->whereHas(
                'attendanceStatus',
                function (Builder $query): void {
                    $query->whereIn(
                        'code',
                        [
                            'P',
                            'PRESENT',
                        ]
                    );
                }
            )
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Display Helpers
    |--------------------------------------------------------------------------
    */

    public function getDisplayNameAttribute(): string
    {
        return "{$this->labour_code} - {$this->full_name}";
    }

    public function getIsContractorManagedAttribute(): bool
    {
        return $this->manpowerSource?->requires_contractor === true;
    }

    public function getCurrentRateAttribute(): ?string
    {
        return match ($this->wage_basis) {
            'daily' => $this->current_daily_rate,
            'hourly' => $this->current_hourly_rate,
            'monthly' => $this->current_monthly_rate,
            default => null,
        };
    }

    public function canBeDeactivated(): bool
    {
        return true;
    }


}