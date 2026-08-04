<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceCorrection extends Model
{
    use HasFactory;
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Workflow Statuses
    |--------------------------------------------------------------------------
    */

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_APPLIED = 'applied';

    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'correction_number',
        'labour_attendance_id',
        'project_id',
        'attendance_date',
        'correction_reason',
        'status',

        'submitted_by',
        'submitted_at',

        'approved_by',
        'approved_at',

        'rejected_by',
        'rejected_at',
        'rejection_reason',

        'applied_by',
        'applied_at',

        'created_by',
        'updated_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'attendance_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'applied_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function labourAttendance(): BelongsTo
    {
        return $this->belongsTo(
            LabourAttendance::class,
            'labour_attendance_id'
        );
    }

    /**
     * Alias retained for convenient view/controller access.
     */
    public function attendance(): BelongsTo
    {
        return $this->labourAttendance();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(
            AttendanceCorrectionDetail::class,
            'attendance_correction_id'
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

    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'applied_by'
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

    public function scopeForAttendanceDate(
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

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_DRAFT
        );
    }

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_SUBMITTED
        );
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_APPROVED
        );
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_REJECTED
        );
    }

    public function scopeApplied(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_APPLIED
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isApplied(): bool
    {
        return $this->status === self::STATUS_APPLIED;
    }

    /*
    |--------------------------------------------------------------------------
    | Workflow Helpers
    |--------------------------------------------------------------------------
    */

    public function canBeEdited(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_DRAFT,
                self::STATUS_REJECTED,
            ],
            true
        );
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === self::STATUS_DRAFT
            && $this->details()->exists();
    }

    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function canBeRejected(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function canBeApplied(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function canBeDeleted(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_DRAFT,
                self::STATUS_REJECTED,
            ],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Display Helpers
    |--------------------------------------------------------------------------
    */

    public function getDisplayStatusAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_APPLIED => 'Applied',

            default => ucfirst(
                str_replace(
                    '_',
                    ' ',
                    (string) $this->status
                )
            ),
        };
    }

    public function getTotalChangesAttribute(): int
    {
        if ($this->relationLoaded('details')) {
            return $this->details->count();
        }

        return $this->details()->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Static Options
    |--------------------------------------------------------------------------
    */

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_APPLIED => 'Applied',
        ];
    }
}