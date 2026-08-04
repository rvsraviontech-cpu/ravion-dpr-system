<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DprManualLabour extends Model
{
    use HasFactory;
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Manual Entry Reasons
    |--------------------------------------------------------------------------
    */

    public const REASON_ATTENDANCE_MISSED = 'attendance_missed';

    public const REASON_EMERGENCY_LABOUR = 'emergency_labour';

    public const REASON_LATE_REPORTING = 'late_reporting';

    public const REASON_TEMPORARY_ASSIGNMENT = 'temporary_assignment';

    public const REASON_ATTENDANCE_CORRECTION = 'attendance_correction';

    public const REASON_OTHER = 'other';

    protected $fillable = [
        'dpr_id',
        'labour_id',
        'attendance_status_id',
        'shift_id',
        'normal_hours',
        'ot_hours',
        'reason',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'dpr_id' => 'integer',
        'labour_id' => 'integer',
        'attendance_status_id' => 'integer',
        'shift_id' => 'integer',
        'normal_hours' => 'decimal:2',
        'ot_hours' => 'decimal:2',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function dpr(): BelongsTo
    {
        return $this->belongsTo(Dpr::class);
    }

    public function labour(): BelongsTo
    {
        return $this->belongsTo(Labour::class);
    }

    public function attendanceStatus(): BelongsTo
    {
        return $this->belongsTo(AttendanceStatus::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
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

    public function scopeForDpr(
        Builder $query,
        ?int $dprId
    ): Builder {
        if ($dprId === null) {
            return $query;
        }

        return $query->where(
            'dpr_id',
            $dprId
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

    public function scopeForShift(
        Builder $query,
        ?int $shiftId
    ): Builder {
        if ($shiftId === null) {
            return $query;
        }

        return $query->where(
            'shift_id',
            $shiftId
        );
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('shift_id')
            ->orderBy('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Reason Options
    |--------------------------------------------------------------------------
    */

    public static function reasonOptions(): array
    {
        return [
            self::REASON_ATTENDANCE_MISSED =>
                'Attendance Missed',

            self::REASON_EMERGENCY_LABOUR =>
                'Emergency Labour',

            self::REASON_LATE_REPORTING =>
                'Late Reporting',

            self::REASON_TEMPORARY_ASSIGNMENT =>
                'Temporary Assignment',

            self::REASON_ATTENDANCE_CORRECTION =>
                'Attendance Correction',

            self::REASON_OTHER =>
                'Other',
        ];
    }

    public static function validReasons(): array
    {
        return array_keys(
            self::reasonOptions()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Display Helpers
    |--------------------------------------------------------------------------
    */

    public function getReasonLabelAttribute(): string
    {
        return self::reasonOptions()[$this->reason]
            ?? ucfirst(
                str_replace(
                    '_',
                    ' ',
                    (string) $this->reason
                )
            );
    }

    public function getTotalHoursAttribute(): float
    {
        return round(
            (float) $this->normal_hours
            + (float) $this->ot_hours,
            2
        );
    }

    public function getSourceLabelAttribute(): string
    {
        return 'Manual DPR Exception';
    }
}