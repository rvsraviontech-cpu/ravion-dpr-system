<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabourAttendanceDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'labour_attendance_id',
        'labour_id',
        'attendance_status_id',
        'working_status_id',

        'labour_category_id',
        'labour_type_id',
        'designation_role_id',
        'skill_category_id',
        'contractor_id',

        'check_in_time',
        'check_out_time',
        'normal_hours',
        'ot_hours',
        'ot_amount',

        'attendance_source',
        'remarks',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'normal_hours' => 'decimal:2',
        'ot_hours' => 'decimal:2',
        'ot_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(
            LabourAttendance::class,
            'labour_attendance_id'
        );
    }

    public function labour(): BelongsTo
    {
        return $this->belongsTo(
            Labour::class
        );
    }

    public function attendanceStatus(): BelongsTo
    {
        return $this->belongsTo(
            AttendanceStatus::class
        );
    }

    public function workingStatus(): BelongsTo
    {
        return $this->belongsTo(
            WorkingStatus::class
        );
    }

    public function labourCategory(): BelongsTo
    {
        return $this->belongsTo(
            LabourCategory::class
        );
    }

    public function labourType(): BelongsTo
    {
        return $this->belongsTo(
            LabourType::class
        );
    }

    public function designationRole(): BelongsTo
    {
        return $this->belongsTo(
            DesignationRole::class
        );
    }

    public function skillCategory(): BelongsTo
    {
        return $this->belongsTo(
            SkillCategory::class
        );
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(
            Contractor::class
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

    public function scopeForLabour(
        Builder $query,
        ?int $labourId
    ): Builder {
        if ($labourId === null) {
            return $query;
        }

        return $query->where(
            'labour_id',
            $labourId
        );
    }

    public function scopeForStatus(
        Builder $query,
        ?int $attendanceStatusId
    ): Builder {
        if ($attendanceStatusId === null) {
            return $query;
        }

        return $query->where(
            'attendance_status_id',
            $attendanceStatusId
        );
    }

    public function scopeForWorkingStatus(
        Builder $query,
        ?int $workingStatusId
    ): Builder {
        if ($workingStatusId === null) {
            return $query;
        }

        return $query->where(
            'working_status_id',
            $workingStatusId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Snapshot Helpers
    |--------------------------------------------------------------------------
    */

    public static function snapshotFromLabour(
        Labour $labour
    ): array {
        return [
            'labour_category_id' =>
                $labour->labour_category_id,

            'labour_type_id' =>
                $labour->labour_type_id,

            'designation_role_id' =>
                $labour->designation_role_id,

            'skill_category_id' =>
                $labour->skill_category_id,

            'contractor_id' =>
                $labour->contractor_id,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Time Helpers
    |--------------------------------------------------------------------------
    */

    public function hasCheckInAndCheckOut(): bool
    {
        return filled($this->check_in_time)
            && filled($this->check_out_time);
    }

    public function getTotalHoursAttribute(): float
    {
        return round(
            (float) $this->normal_hours
            + (float) $this->ot_hours,
            2
        );
    }
}