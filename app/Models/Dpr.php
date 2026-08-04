<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dpr extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'dpr_date',
        'weather',
        'remarks',
        'status',
        'pmo_remarks',
    ];

    protected $casts = [
        'project_id' => 'integer',
        'user_id' => 'integer',
        'dpr_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Primary Relationships
    |--------------------------------------------------------------------------
    */

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | DPR Work Relationships
    |--------------------------------------------------------------------------
    */

    public function workItems(): HasMany
    {
        return $this->hasMany(DprWorkItem::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(DprPhoto::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Legacy DPR Labour Relationship
    |--------------------------------------------------------------------------
    |
    | This relationship is retained temporarily so that existing DPR records
    | and screens continue working while the attendance-based labour workflow
    | is introduced.
    |
    | It can be retired only after the controller, create/edit screens,
    | show page and PDF have all been migrated successfully.
    |
    */

    public function labours(): HasMany
    {
        return $this->hasMany(DprLabour::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Labour Attendance Integration
    |--------------------------------------------------------------------------
    */

    public function labourAttendances(): BelongsToMany
    {
        return $this->belongsToMany(
            LabourAttendance::class,
            'dpr_labour_attendances',
            'dpr_id',
            'labour_attendance_id'
        )
            ->withPivot('created_by')
            ->withTimestamps();
    }

    public function manualLabours(): HasMany
    {
        return $this->hasMany(
            DprManualLabour::class,
            'dpr_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Material Relationships
    |--------------------------------------------------------------------------
    */

    public function materials(): HasMany
    {
        return $this->hasMany(DprMaterial::class);
    }

    public function materialReceived(): HasMany
    {
        return $this->hasMany(
            DprMaterialReceived::class
        );
    }

    public function materialRequired(): HasMany
    {
        return $this->hasMany(
            DprMaterialRequired::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Machinery, Issues and Planning
    |--------------------------------------------------------------------------
    */

    public function machineryTools(): HasMany
    {
        return $this->hasMany(
            DprMachineryTool::class
        );
    }

    public function siteIssues(): HasMany
    {
        return $this->hasMany(
            SiteIssue::class
        );
    }

    public function tomorrowPlans(): HasMany
    {
        return $this->hasMany(
            TomorrowPlan::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Labour Attendance Helpers
    |--------------------------------------------------------------------------
    */

    public function hasLinkedLabourAttendance(): bool
    {
        if ($this->relationLoaded('labourAttendances')) {
            return $this->labourAttendances->isNotEmpty();
        }

        return $this->labourAttendances()->exists();
    }

    public function hasManualLabourExceptions(): bool
    {
        if ($this->relationLoaded('manualLabours')) {
            return $this->manualLabours->isNotEmpty();
        }

        return $this->manualLabours()->exists();
    }

    public function linkedAttendanceIds(): array
    {
        return $this->labourAttendances()
            ->pluck('labour_attendances.id')
            ->map(
                fn (mixed $attendanceId): int =>
                    (int) $attendanceId
            )
            ->all();
    }

    public function linkedAttendanceDetails(): Collection
    {
        return LabourAttendanceDetail::query()
            ->whereIn(
                'labour_attendance_id',
                $this->linkedAttendanceIds()
            )
            ->with([
                'labour',
                'attendanceStatus',
                'labourCategory',
                'labourType',
                'designationRole',
                'skillCategory',
                'contractor',
                'attendance.shift',
            ])
            ->get();
    }

    public function linkedAttendanceLabourIds(): array
    {
        return $this->linkedAttendanceDetails()
            ->pluck('labour_id')
            ->filter()
            ->map(
                fn (mixed $labourId): int =>
                    (int) $labourId
            )
            ->unique()
            ->values()
            ->all();
    }

    public function attendanceLabourCount(): int
    {
        return $this->linkedAttendanceDetails()
            ->pluck('labour_id')
            ->filter()
            ->unique()
            ->count();
    }

    public function manualLabourCount(): int
    {
        if ($this->relationLoaded('manualLabours')) {
            return $this->manualLabours->count();
        }

        return $this->manualLabours()->count();
    }

    public function totalReportedLabourCount(): int
    {
        return $this->attendanceLabourCount()
            + $this->manualLabourCount();
    }

    public function totalAttendanceNormalHours(): float
    {
        return round(
            (float) $this->linkedAttendanceDetails()
                ->sum('normal_hours'),
            2
        );
    }

    public function totalAttendanceOtHours(): float
    {
        return round(
            (float) $this->linkedAttendanceDetails()
                ->sum('ot_hours'),
            2
        );
    }

    public function totalManualNormalHours(): float
    {
        $total = $this->relationLoaded('manualLabours')
            ? $this->manualLabours->sum('normal_hours')
            : $this->manualLabours()->sum('normal_hours');

        return round((float) $total, 2);
    }

    public function totalManualOtHours(): float
    {
        $total = $this->relationLoaded('manualLabours')
            ? $this->manualLabours->sum('ot_hours')
            : $this->manualLabours()->sum('ot_hours');

        return round((float) $total, 2);
    }

    public function totalReportedNormalHours(): float
    {
        return round(
            $this->totalAttendanceNormalHours()
            + $this->totalManualNormalHours(),
            2
        );
    }

    public function totalReportedOtHours(): float
    {
        return round(
            $this->totalAttendanceOtHours()
            + $this->totalManualOtHours(),
            2
        );
    }

    public function totalReportedHours(): float
    {
        return round(
            $this->totalReportedNormalHours()
            + $this->totalReportedOtHours(),
            2
        );
    }
}