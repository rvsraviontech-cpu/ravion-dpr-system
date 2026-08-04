<?php

namespace App\Services;

use App\Models\Labour;
use App\Models\LabourAttendanceDetail;
use App\Models\WeeklyWageSheet;
use App\Models\WeeklyWageSheetDetail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class WeeklyWageCalculationService
{
    /**
     * Generate or regenerate all labour wage rows for a weekly wage sheet.
     *
     * @throws Throwable
     */
    public function generate(
        WeeklyWageSheet $wageSheet
    ): WeeklyWageSheet {
        return DB::transaction(function () use ($wageSheet): WeeklyWageSheet {
            $lockedSheet = WeeklyWageSheet::query()
                ->whereKey($wageSheet->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureSheetCanBeCalculated($lockedSheet);

            [$weekStart, $weekEnd] = $this->normaliseWeek($lockedSheet);

            $attendanceDetails = $this->approvedAttendanceDetails(
                projectId: (int) $lockedSheet->project_id,
                weekStart: $weekStart,
                weekEnd: $weekEnd
            );

            if ($attendanceDetails->isEmpty()) {
                throw ValidationException::withMessages([
                    'attendance' => [
                        'No active approved labour attendance was found for the selected project and week.',
                    ],
                ]);
            }

            $existingAdjustments = $lockedSheet
                ->details()
                ->withTrashed()
                ->get()
                ->keyBy('labour_id')
                ->map(fn (WeeklyWageSheetDetail $detail): array => [
                    'additions' => (float) $detail->additions,
                    'deductions' => (float) $detail->deductions,
                    'adjustment_reason' => $detail->adjustment_reason,
                    'remarks' => $detail->remarks,
                ]);

            $lockedSheet->details()->forceDelete();

            foreach ($attendanceDetails->groupBy('labour_id') as $labourId => $details) {
                $labour = $details->first()?->labour;

                if (! $labour) {
                    continue;
                }

                $adjustments = $existingAdjustments->get(
                    (int) $labourId,
                    [
                        'additions' => 0,
                        'deductions' => 0,
                        'adjustment_reason' => null,
                        'remarks' => null,
                    ]
                );

                WeeklyWageSheetDetail::create([
                    'weekly_wage_sheet_id' => $lockedSheet->id,
                    ...$this->calculateLabourValues(
                        labour: $labour,
                        attendanceDetails: $details,
                        additions: (float) $adjustments['additions'],
                        deductions: (float) $adjustments['deductions']
                    ),
                    'adjustment_reason' => $adjustments['adjustment_reason'],
                    'remarks' => $adjustments['remarks'],
                    'is_active' => true,
                ]);
            }

            $lockedSheet->update([
                'week_start_date' => $weekStart->toDateString(),
                'week_end_date' => $weekEnd->toDateString(),
                'status' => 'calculated',
                'generated_by' => auth()->id(),
                'generated_at' => now(),
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);

            $this->recalculateSheetTotals($lockedSheet);

            return $lockedSheet->fresh([
                'project',
                'details.labour',
                'charges',
            ]);
        });
    }

    public function recalculateDetail(
        WeeklyWageSheetDetail $detail
    ): WeeklyWageSheetDetail {
        $detail->recalculateValues();
        $detail->save();

        $this->recalculateSheetTotals($detail->weeklyWageSheet);

        return $detail->fresh();
    }

    public function recalculateSheetTotals(
        WeeklyWageSheet $wageSheet
    ): WeeklyWageSheet {
        $details = $wageSheet
            ->details()
            ->where('is_active', true)
            ->get();

        $siteCharges = (float) $wageSheet
            ->charges()
            ->where('is_active', true)
            ->sum('amount');

        $totalNormalWages = round((float) $details->sum('normal_wage'), 2);
        $totalOtWages = round((float) $details->sum('ot_wage'), 2);
        $totalAdditions = round((float) $details->sum('additions'), 2);
        $totalDeductions = round((float) $details->sum('deductions'), 2);

        $grossLabourWages = round(
            $totalNormalWages + $totalOtWages + $totalAdditions,
            2
        );

        $netLabourWages = round(
            $grossLabourWages - $totalDeductions,
            2
        );

        $totalProjectPayable = round(
            $netLabourWages + $siteCharges,
            2
        );

        $wageSheet->update([
            'total_labours' => $details->count(),
            'total_full_days' => round((float) $details->sum('full_days'), 2),
            'total_half_days' => round((float) $details->sum('half_days'), 2),
            'total_payable_days' => round((float) $details->sum('payable_days'), 2),
            'total_normal_wages' => $totalNormalWages,
            'total_ot_hours' => round((float) $details->sum('ot_hours'), 2),
            'total_ot_wages' => $totalOtWages,
            'total_labour_additions' => $totalAdditions,
            'total_labour_deductions' => $totalDeductions,
            'total_site_charges' => round($siteCharges, 2),
            'gross_labour_wages' => $grossLabourWages,
            'net_labour_wages' => $netLabourWages,
            'total_project_payable' => $totalProjectPayable,
        ]);

        return $wageSheet->fresh();
    }

    private function calculateLabourValues(
        Labour $labour,
        Collection $attendanceDetails,
        float $additions = 0,
        float $deductions = 0
    ): array {
        $fullDays = 0.0;
        $halfDays = 0.0;
        $payableDays = 0.0;
        $absentDays = 0.0;
        $leaveDays = 0.0;
        $weeklyOffDays = 0.0;
        $holidayDays = 0.0;
        $normalHours = 0.0;
        $otHours = 0.0;

        foreach ($attendanceDetails as $detail) {
            $status = $detail->attendanceStatus;

            if (! $status) {
                continue;
            }

            $statusCode = strtoupper(trim((string) (
                $status->short_name ?: $status->code ?: $status->name
            )));

            $payableFactor = round((float) ($status->payable_factor ?? 0), 2);
            $payableDays += $payableFactor;

            if ($payableFactor >= 1) {
                $fullDays += 1;
            } elseif ($payableFactor > 0) {
                $halfDays += 1;
            }

            if ((bool) $status->counts_as_absent) {
                $absentDays += 1;
            }

            if ($this->isLeaveStatus($statusCode)) {
                $leaveDays += 1;
            }

            if ($this->isWeeklyOffStatus($statusCode)) {
                $weeklyOffDays += 1;
            }

            if ($this->isHolidayStatus($statusCode)) {
                $holidayDays += 1;
            }

            if ((bool) $status->allows_normal_hours) {
                $normalHours += (float) $detail->normal_hours;
            }

            if ((bool) $status->allows_ot_hours) {
                $otHours += (float) $detail->ot_hours;
            }
        }

        $wageBasis = strtolower(trim((string) ($labour->wage_basis ?: 'daily')));
        $standardHours = (float) ($labour->normal_shift_hours ?: 8);

        if ($standardHours <= 0) {
            $standardHours = 8;
        }

        $dailyRate = $this->resolveDailyRate(
            labour: $labour,
            wageBasis: $wageBasis,
            standardHours: $standardHours
        );

        $otHourlyRate = $this->resolveOtHourlyRate(
            labour: $labour,
            dailyRate: $dailyRate,
            standardHours: $standardHours
        );

        $normalWage = round($payableDays * $dailyRate, 2);
        $otWage = round($otHours * $otHourlyRate, 2);
        $grossWage = round($normalWage + $otWage + $additions, 2);
        $netPayable = round($grossWage - $deductions, 2);

        return [
            'labour_id' => $labour->id,
            'designation_role_id' => $labour->designation_role_id,
            'labour_category_id' => $labour->labour_category_id,
            'contractor_id' => $labour->contractor_id,
            'full_days' => round($fullDays, 2),
            'half_days' => round($halfDays, 2),
            'payable_days' => round($payableDays, 2),
            'absent_days' => round($absentDays, 2),
            'leave_days' => round($leaveDays, 2),
            'weekly_off_days' => round($weeklyOffDays, 2),
            'holiday_days' => round($holidayDays, 2),
            'normal_hours' => round($normalHours, 2),
            'ot_hours' => round($otHours, 2),
            'wage_basis' => $wageBasis,
            'daily_wage_rate' => round($dailyRate, 2),
            'standard_hours_per_day' => round($standardHours, 2),
            'ot_hourly_rate' => round($otHourlyRate, 4),
            'normal_wage' => $normalWage,
            'ot_wage' => $otWage,
            'gross_wage' => $grossWage,
            'additions' => round($additions, 2),
            'deductions' => round($deductions, 2),
            'net_payable' => $netPayable,
        ];
    }

    private function approvedAttendanceDetails(
        int $projectId,
        Carbon $weekStart,
        Carbon $weekEnd
    ): Collection {
        return LabourAttendanceDetail::query()
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->whereHas(
                'attendance',
                function (Builder $query) use (
                    $projectId,
                    $weekStart,
                    $weekEnd
                ): void {
                    $query
                        ->where('project_id', $projectId)
                        ->where('status', 'approved')
                        ->where('is_active', true)
                        ->whereNull('deleted_at')
                        ->whereBetween('attendance_date', [
                            $weekStart->toDateString(),
                            $weekEnd->toDateString(),
                        ]);
                }
            )
            ->with([
                'attendance:id,project_id,attendance_date,status,is_active',
                'labour:id,labour_code,full_name,wage_basis,current_daily_rate,current_hourly_rate,current_monthly_rate,ot_calculation_type,current_ot_rate,ot_multiplier,normal_shift_hours,designation_role_id,labour_category_id,contractor_id',
                'attendanceStatus:id,code,name,short_name,counts_as_present,counts_as_absent,payable_factor,allows_normal_hours,allows_ot_hours',
            ])
            ->orderBy('labour_id')
            ->orderBy('labour_attendance_id')
            ->get();
    }

    private function resolveDailyRate(
        Labour $labour,
        string $wageBasis,
        float $standardHours
    ): float {
        return match ($wageBasis) {
            'hourly' => round(
                (float) $labour->current_hourly_rate * $standardHours,
                2
            ),

            'monthly' => round(
                (float) $labour->current_monthly_rate / 26,
                2
            ),

            default => round(
                (float) $labour->current_daily_rate,
                2
            ),
        };
    }

    private function resolveOtHourlyRate(
        Labour $labour,
        float $dailyRate,
        float $standardHours
    ): float {
        $baseHourlyRate = $standardHours > 0
            ? $dailyRate / $standardHours
            : 0;

        $calculationType = strtolower(trim((string) (
            $labour->ot_calculation_type ?: 'standard'
        )));

        return match ($calculationType) {
            'fixed',
            'fixed_rate',
            'hourly_rate' => round(
                (float) $labour->current_ot_rate,
                4
            ),

            'multiplier' => round(
                $baseHourlyRate * (
                    (float) $labour->ot_multiplier > 0
                        ? (float) $labour->ot_multiplier
                        : 1
                ),
                4
            ),

            default => round($baseHourlyRate, 4),
        };
    }

    private function normaliseWeek(
        WeeklyWageSheet $wageSheet
    ): array {
        $weekStart = Carbon::parse($wageSheet->week_start_date)
            ->startOfWeek(Carbon::SUNDAY)
            ->startOfDay();

        $weekEnd = $weekStart
            ->copy()
            ->addDays(6)
            ->endOfDay();

        return [$weekStart, $weekEnd];
    }

    private function ensureSheetCanBeCalculated(
        WeeklyWageSheet $wageSheet
    ): void {
        if (! $wageSheet->is_active) {
            throw ValidationException::withMessages([
                'status' => [
                    'The weekly wage sheet is inactive.',
                ],
            ]);
        }

        if (in_array(
            $wageSheet->status,
            ['submitted', 'approved', 'paid'],
            true
        )) {
            throw ValidationException::withMessages([
                'status' => [
                    'Submitted, Approved, or Paid wage sheets cannot be recalculated.',
                ],
            ]);
        }
    }

    private function isLeaveStatus(string $code): bool
    {
        return in_array(
            $code,
            ['L', 'LEAVE', 'CL', 'SL', 'PL'],
            true
        ) || str_contains($code, 'LEAVE');
    }

    private function isWeeklyOffStatus(string $code): bool
    {
        return in_array(
            $code,
            ['WO', 'WEEKLY OFF', 'WEEKLY_OFF', 'WEEKLY-OFF'],
            true
        );
    }

    private function isHolidayStatus(string $code): bool
    {
        return in_array(
            $code,
            ['H', 'HOLIDAY'],
            true
        );
    }
}
