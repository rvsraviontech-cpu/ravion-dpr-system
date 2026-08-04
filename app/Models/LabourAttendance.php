<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LabourAttendance extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'attendance_number',
        'project_id',
        'attendance_date',
        'shift_id',

        'total_labours',
        'present_count',
        'absent_count',
        'leave_count',
        'half_day_count',
        'total_normal_hours',
        'total_ot_hours',

        'status',
        'recorded_by',

        'submitted_by',
        'submitted_at',

        'approved_by',
        'approved_at',

        'rejected_by',
        'rejected_at',
        'rejection_reason',

        'reopened_by',
        'reopened_at',
        'reopen_reason',
        'revision_number',

        'remarks',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',

        'total_labours' => 'integer',
        'present_count' => 'integer',
        'absent_count' => 'integer',
        'leave_count' => 'integer',
        'half_day_count' => 'integer',

        'total_normal_hours' => 'decimal:2',
        'total_ot_hours' => 'decimal:2',

        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'reopened_at' => 'datetime',

        'revision_number' => 'integer',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(
            LabourAttendanceDetail::class
        );
    }

    public function attendanceCorrections(): HasMany
{
    return $this->hasMany(
        AttendanceCorrection::class,
        'labour_attendance_id'
    );
}

    /*
|--------------------------------------------------------------------------
| DPR Relationships
|--------------------------------------------------------------------------
*/

public function dprs(): BelongsToMany
{
    return $this->belongsToMany(
        Dpr::class,
        'dpr_labour_attendances',
        'labour_attendance_id',
        'dpr_id'
    )
    ->withPivot('created_by')
    ->withTimestamps();
}

/*
|--------------------------------------------------------------------------
| DPR Helpers
|--------------------------------------------------------------------------
*/

public function isLinkedToAnyDpr(): bool
{
    if ($this->relationLoaded('dprs')) {
        return $this->dprs->isNotEmpty();
    }

    return $this->dprs()->exists();
}

public function linkedDprCount(): int
{
    if ($this->relationLoaded('dprs')) {
        return $this->dprs->count();
    }

    return $this->dprs()->count();
}

public function linkedDprIds(): array
{
    return $this->dprs()
        ->pluck('dprs.id')
        ->map(
            fn ($id) => (int) $id
        )
        ->all();
}

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'recorded_by'
        );
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'submitted_by'
        );
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'rejected_by'
        );
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reopened_by'
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

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByDesc('attendance_date')
            ->orderByDesc('id');
    }

    public function scopeForProject(
        Builder $query,
        ?int $projectId
    ): Builder {
        if ($projectId === null) {
            return $query;
        }

        return $query->where(
            'project_id',
            $projectId
        );
    }

    public function scopeForDate(
        Builder $query,
        mixed $attendanceDate
    ): Builder {
        if (blank($attendanceDate)) {
            return $query;
        }

        return $query->whereDate(
            'attendance_date',
            $attendanceDate
        );
    }

    public function scopeWithStatus(
        Builder $query,
        ?string $status
    ): Builder {
        if (blank($status)) {
            return $query;
        }

        return $query->where(
            'status',
            $status
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Workflow Helpers
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isReopened(): bool
    {
        return $this->status === 'reopened';
    }

    public function canBeEdited(): bool
    {
        return in_array(
            $this->status,
            [
                'draft',
                'rejected',
                'reopened',
            ],
            true
        );
    }

    public function canBeSubmitted(): bool
    {
        return in_array(
            $this->status,
            [
                'draft',
                'rejected',
                'reopened',
            ],
            true
        )
            && $this->details()->exists();
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'submitted';
    }

    public function canBeRejected(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Only an approved attendance sheet may be reopened.
     */
    public function canBeReopened(): bool
    {
        return $this->status === 'approved'
            && $this->is_active;
    }

    /*
    |--------------------------------------------------------------------------
    | Revision Helpers
    |--------------------------------------------------------------------------
    */

    public function nextRevisionNumber(): int
    {
        return max(
            1,
            (int) $this->revision_number + 1
        );
    }

    public function incrementRevision(): void
    {
        $this->forceFill([
            'revision_number' =>
                $this->nextRevisionNumber(),
        ])->saveQuietly();
    }

    /*
    |--------------------------------------------------------------------------
    | Summary Recalculation
    |--------------------------------------------------------------------------
    */

    public function recalculateSummary(): void
    {
        $details = $this->details()
            ->with('attendanceStatus')
            ->get();

        $presentCount = 0;
        $absentCount = 0;
        $leaveCount = 0;
        $halfDayCount = 0;

        foreach ($details as $detail) {
            $code = strtoupper(
                trim(
                    (string) (
                        $detail->attendanceStatus?->code
                        ?? ''
                    )
                )
            );

            match ($code) {
                'P',
                'PRESENT' => $presentCount++,

                'A',
                'ABSENT' => $absentCount++,

                'L',
                'LEAVE' => $leaveCount++,

                'HD',
                'HALF_DAY',
                'HALFDAY' => $halfDayCount++,

                default => null,
            };
        }

        $this->forceFill([
            'total_labours' => $details->count(),
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'leave_count' => $leaveCount,
            'half_day_count' => $halfDayCount,

            'total_normal_hours' => $details->sum(
                fn (LabourAttendanceDetail $detail): float =>
                    (float) $detail->normal_hours
            ),

            'total_ot_hours' => $details->sum(
                fn (LabourAttendanceDetail $detail): float =>
                    (float) $detail->ot_hours
            ),
        ])->saveQuietly();
    }

    /*
    |--------------------------------------------------------------------------
    | Display Helpers
    |--------------------------------------------------------------------------
    */

    public function getDisplayStatusAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'reopened' => 'Reopened for Correction',

            default => ucfirst(
                str_replace(
                    '_',
                    ' ',
                    $this->status
                )
            ),
        };
    }

    public function getRevisionLabelAttribute(): string
    {
        return 'Revision '
            . max(
                0,
                (int) $this->revision_number
            );
    }
}