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
    | New Standalone Execution Relationships
    |--------------------------------------------------------------------------
    |
    | These are the source-of-truth relationships used by the new DPR
    | orchestration layer. The DPR no longer creates duplicate execution data;
    | it links existing standalone transactions.
    |
    */

    public function workDoneItems(): HasMany
    {
        return $this->hasMany(
            WorkDoneItem::class,
            'dpr_id'
        )
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function materialReceipts(): HasMany
    {
        return $this->hasMany(
            MaterialReceived::class,
            'dpr_id'
        )
            ->orderBy('received_date')
            ->orderBy('id');
    }

    public function materialConsumptions(): HasMany
    {
        return $this->hasMany(
            MaterialConsumed::class,
            'dpr_id'
        )
            ->orderBy('consumed_date')
            ->orderBy('id');
    }

    public function materialRequirements(): HasMany
    {
        return $this->hasMany(
            MaterialRequirement::class,
            'dpr_id'
        )
            ->orderBy('id');
    }

    public function siteIssues(): HasMany
    {
        return $this->hasMany(
            SiteIssue::class,
            'dpr_id'
        )
            ->orderBy('issue_date')
            ->orderBy('id');
    }

    public function tomorrowPlans(): HasMany
    {
        return $this->hasMany(
            TomorrowPlan::class,
            'dpr_id'
        )
            ->orderBy('planned_date')
            ->orderBy('id');
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

    /*
    |--------------------------------------------------------------------------
    | Legacy DPR Relationships
    |--------------------------------------------------------------------------
    |
    | DO NOT remove these yet.
    |
    | Old DPR records may still use DPR-owned child tables. They remain
    | available during the migration period so historical DPRs continue to
    | render while Show/PDF/Edit are converted to the orchestration layer.
    |
    */

    public function workItems(): HasMany
    {
        return $this->hasMany(
            DprWorkItem::class,
            'dpr_id'
        );
    }

    public function photos(): HasMany
    {
        return $this->hasMany(
            DprPhoto::class,
            'dpr_id'
        );
    }

    public function labours(): HasMany
    {
        return $this->hasMany(
            DprLabour::class,
            'dpr_id'
        );
    }

    public function manualLabours(): HasMany
    {
        return $this->hasMany(
            DprManualLabour::class,
            'dpr_id'
        );
    }

    public function materials(): HasMany
    {
        return $this->hasMany(
            DprMaterial::class,
            'dpr_id'
        );
    }

    public function materialReceived(): HasMany
    {
        return $this->hasMany(
            DprMaterialReceived::class,
            'dpr_id'
        );
    }

    public function materialRequired(): HasMany
    {
        return $this->hasMany(
            DprMaterialRequired::class,
            'dpr_id'
        );
    }

    public function machineryTools(): HasMany
    {
        return $this->hasMany(
            DprMachineryTool::class,
            'dpr_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Integration Helpers
    |--------------------------------------------------------------------------
    */

    public function hasLinkedLabourAttendance(): bool
    {
        if ($this->relationLoaded('labourAttendances')) {
            return $this->labourAttendances->isNotEmpty();
        }

        return $this->labourAttendances()->exists();
    }

    public function hasStandaloneExecutionData(): bool
    {
        return $this->workDoneItems()->exists()
            || $this->materialReceipts()->exists()
            || $this->materialConsumptions()->exists()
            || $this->materialRequirements()->exists()
            || $this->siteIssues()->exists()
            || $this->tomorrowPlans()->exists()
            || $this->labourAttendances()->exists();
    }

    public function linkedAttendanceIds(): array
    {
        return $this->labourAttendances()
            ->pluck('labour_attendances.id')
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
    }

    public function linkedAttendanceDetails(): Collection
    {
        $ids = $this->linkedAttendanceIds();

        if ($ids === []) {
            return new Collection();
        }

        return LabourAttendanceDetail::query()
            ->whereIn(
                'labour_attendance_id',
                $ids
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
            ->map(fn (mixed $labourId): int => (int) $labourId)
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

        return round(
            (float) $total,
            2
        );
    }

    public function totalManualOtHours(): float
    {
        $total = $this->relationLoaded('manualLabours')
            ? $this->manualLabours->sum('ot_hours')
            : $this->manualLabours()->sum('ot_hours');

        return round(
            (float) $total,
            2
        );
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

    public function getStandaloneExecutionCountAttribute(): int
    {
        $workDone = $this->relationLoaded('workDoneItems')
            ? $this->workDoneItems->count()
            : $this->workDoneItems()->count();

        $received = $this->relationLoaded('materialReceipts')
            ? $this->materialReceipts->count()
            : $this->materialReceipts()->count();

        $consumed = $this->relationLoaded('materialConsumptions')
            ? $this->materialConsumptions->count()
            : $this->materialConsumptions()->count();

        $required = $this->relationLoaded('materialRequirements')
            ? $this->materialRequirements->count()
            : $this->materialRequirements()->count();

        $issues = $this->relationLoaded('siteIssues')
            ? $this->siteIssues->count()
            : $this->siteIssues()->count();

        $plans = $this->relationLoaded('tomorrowPlans')
            ? $this->tomorrowPlans->count()
            : $this->tomorrowPlans()->count();

        $attendance = $this->relationLoaded('labourAttendances')
            ? $this->labourAttendances->count()
            : $this->labourAttendances()->count();

        return $workDone
            + $received
            + $consumed
            + $required
            + $issues
            + $plans
            + $attendance;
    }
}
