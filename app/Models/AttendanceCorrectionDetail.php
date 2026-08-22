<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceCorrectionDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const ACTION_ADD = 'add';
    public const ACTION_MODIFY = 'modify';
    public const ACTION_REMOVE = 'remove';

    protected $fillable = [
        'attendance_correction_id',
        'labour_attendance_detail_id',
        'labour_id',
        'action_type',
        'old_attendance_status_id',
        'new_attendance_status_id',
        'old_working_status_id',
        'new_working_status_id',
        'old_check_in_time',
        'new_check_in_time',
        'old_check_out_time',
        'new_check_out_time',
        'old_normal_hours',
        'new_normal_hours',
        'old_ot_hours',
        'old_ot_amount',
        'new_ot_hours',
        'new_ot_amount',
        'old_remarks',
        'new_remarks',
        'before_snapshot',
        'after_snapshot',
        'line_reason',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'old_normal_hours' => 'decimal:2',
        'new_normal_hours' => 'decimal:2',
        'old_ot_hours' => 'decimal:2',
        'old_ot_amount' => 'decimal:2',
        'new_ot_hours' => 'decimal:2',
        'new_ot_amount' => 'decimal:2',
        'before_snapshot' => 'array',
        'after_snapshot' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function correction(): BelongsTo
    {
        return $this->belongsTo(
            AttendanceCorrection::class,
            'attendance_correction_id'
        );
    }

    public function attendanceCorrection(): BelongsTo
    {
        return $this->correction();
    }

    public function labourAttendanceDetail(): BelongsTo
    {
        return $this->belongsTo(
            LabourAttendanceDetail::class,
            'labour_attendance_detail_id'
        );
    }

    public function labour(): BelongsTo
    {
        return $this->belongsTo(Labour::class);
    }

    public function oldAttendanceStatus(): BelongsTo
    {
        return $this->belongsTo(
            AttendanceStatus::class,
            'old_attendance_status_id'
        );
    }

    public function newAttendanceStatus(): BelongsTo
    {
        return $this->belongsTo(
            AttendanceStatus::class,
            'new_attendance_status_id'
        );
    }

    public function oldWorkingStatus(): BelongsTo
    {
        return $this->belongsTo(
            WorkingStatus::class,
            'old_working_status_id'
        );
    }

    public function newWorkingStatus(): BelongsTo
    {
        return $this->belongsTo(
            WorkingStatus::class,
            'new_working_status_id'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function scopeForAction(
        Builder $query,
        ?string $actionType
    ): Builder {
        if (blank($actionType)) {
            return $query;
        }

        return $query->where('action_type', $actionType);
    }

    public function scopeForLabour(
        Builder $query,
        ?int $labourId
    ): Builder {
        if ($labourId === null) {
            return $query;
        }

        return $query->where('labour_id', $labourId);
    }

    public static function actionTypes(): array
    {
        return [
            self::ACTION_ADD => 'Add Labour',
            self::ACTION_MODIFY => 'Modify Attendance',
            self::ACTION_REMOVE => 'Remove Labour',
        ];
    }

    public function isAdd(): bool
    {
        return $this->action_type === self::ACTION_ADD;
    }

    public function isModify(): bool
    {
        return $this->action_type === self::ACTION_MODIFY;
    }

    public function isRemove(): bool
    {
        return $this->action_type === self::ACTION_REMOVE;
    }

    public function getActionLabelAttribute(): string
    {
        return self::actionTypes()[$this->action_type]
            ?? ucfirst(str_replace('_', ' ', (string) $this->action_type));
    }

    public static function snapshotFromAttendanceDetail(
        LabourAttendanceDetail $detail
    ): array {
        return [
            'labour_attendance_detail_id' => $detail->id,
            'labour_attendance_id' => $detail->labour_attendance_id,
            'labour_id' => $detail->labour_id,
            'attendance_status_id' => $detail->attendance_status_id,
            'working_status_id' => $detail->working_status_id,
            'labour_category_id' => $detail->labour_category_id,
            'labour_type_id' => $detail->labour_type_id,
            'designation_role_id' => $detail->designation_role_id,
            'skill_category_id' => $detail->skill_category_id,
            'contractor_id' => $detail->contractor_id,
            'check_in_time' => self::formatTimeValue($detail->check_in_time),
            'check_out_time' => self::formatTimeValue($detail->check_out_time),
            'normal_hours' => number_format((float) $detail->normal_hours, 2, '.', ''),
            'ot_hours' => number_format((float) $detail->ot_hours, 2, '.', ''),
            'ot_amount' => $detail->ot_amount !== null
                ? number_format((float) $detail->ot_amount, 2, '.', '')
                : null,
            'attendance_source' => $detail->attendance_source,
            'remarks' => $detail->remarks,
            'is_active' => (bool) $detail->is_active,
        ];
    }

    private static function formatTimeValue(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = (string) $value;

        return strlen($value) >= 5
            ? substr($value, 0, 5)
            : $value;
    }
}
