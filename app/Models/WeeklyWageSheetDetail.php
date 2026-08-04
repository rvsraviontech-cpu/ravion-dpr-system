<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyWageSheetDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'weekly_wage_sheet_id',

        'labour_id',
        'designation_role_id',
        'labour_category_id',
        'contractor_id',

        'full_days',
        'half_days',
        'payable_days',
        'absent_days',
        'leave_days',
        'weekly_off_days',
        'holiday_days',

        'normal_hours',
        'ot_hours',

        'wage_basis',
        'daily_wage_rate',
        'standard_hours_per_day',
        'ot_hourly_rate',

        'normal_wage',
        'ot_wage',
        'gross_wage',

        'additions',
        'deductions',
        'net_payable',

        'adjustment_reason',
        'remarks',

        'is_active',
    ];

    protected $casts = [
        'full_days' => 'decimal:2',
        'half_days' => 'decimal:2',
        'payable_days' => 'decimal:2',
        'absent_days' => 'decimal:2',
        'leave_days' => 'decimal:2',
        'weekly_off_days' => 'decimal:2',
        'holiday_days' => 'decimal:2',

        'normal_hours' => 'decimal:2',
        'ot_hours' => 'decimal:2',

        'daily_wage_rate' => 'decimal:2',
        'standard_hours_per_day' => 'decimal:2',
        'ot_hourly_rate' => 'decimal:4',

        'normal_wage' => 'decimal:2',
        'ot_wage' => 'decimal:2',
        'gross_wage' => 'decimal:2',

        'additions' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_payable' => 'decimal:2',

        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function weeklyWageSheet(): BelongsTo
    {
        return $this->belongsTo(
            WeeklyWageSheet::class
        );
    }

    public function labour(): BelongsTo
    {
        return $this->belongsTo(
            Labour::class
        );
    }

    public function designationRole(): BelongsTo
    {
        return $this->belongsTo(
            DesignationRole::class
        );
    }

    public function labourCategory(): BelongsTo
    {
        return $this->belongsTo(
            LabourCategory::class
        );
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(
            Contractor::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Calculation Helpers
    |--------------------------------------------------------------------------
    */

    public function calculatePayableDays(): float
    {
        return round(
            (float) $this->full_days
            + ((float) $this->half_days * 0.5),
            2
        );
    }

    public function calculateOtHourlyRate(): float
    {
        $standardHours = (float) $this->standard_hours_per_day;

        if ($standardHours <= 0) {
            return 0.0;
        }

        return round(
            (float) $this->daily_wage_rate
            / $standardHours,
            4
        );
    }

    public function calculateNormalWage(): float
    {
        return round(
            (float) $this->payable_days
            * (float) $this->daily_wage_rate,
            2
        );
    }

    public function calculateOtWage(): float
    {
        return round(
            (float) $this->ot_hours
            * (float) $this->ot_hourly_rate,
            2
        );
    }

    public function calculateGrossWage(): float
    {
        return round(
            (float) $this->normal_wage
            + (float) $this->ot_wage
            + (float) $this->additions,
            2
        );
    }

    public function calculateNetPayable(): float
    {
        return round(
            (float) $this->gross_wage
            - (float) $this->deductions,
            2
        );
    }

    /**
     * Recalculate all derived wage values in memory.
     */
    public function recalculateValues(): void
    {
        $this->payable_days =
            $this->calculatePayableDays();

        $this->ot_hourly_rate =
            $this->calculateOtHourlyRate();

        $this->normal_wage =
            $this->calculateNormalWage();

        $this->ot_wage =
            $this->calculateOtWage();

        $this->gross_wage =
            $this->calculateGrossWage();

        $this->net_payable =
            $this->calculateNetPayable();
    }
}