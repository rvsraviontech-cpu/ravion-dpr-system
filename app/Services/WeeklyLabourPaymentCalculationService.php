<?php

namespace App\Services;

use App\Models\Labour;
use App\Models\LabourAttendanceDetail;
use App\Models\WeeklyLabourPaymentAllocation;
use App\Models\WeeklyLabourPaymentDetail;
use App\Models\WeeklyLabourPaymentRegister;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class WeeklyLabourPaymentCalculationService
{
    /**
     * Generate one consolidated payment row per labour across all projects.
     *
     * @throws Throwable
     */
    public function generate(
        WeeklyLabourPaymentRegister $register
    ): WeeklyLabourPaymentRegister {
        return DB::transaction(
            function () use ($register): WeeklyLabourPaymentRegister {
                $lockedRegister = WeeklyLabourPaymentRegister::query()
                    ->whereKey($register->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->ensureRegisterCanBeCalculated($lockedRegister);

                [$weekStart, $weekEnd] = $this->normaliseWeek($lockedRegister);

                $attendanceDetails = $this->approvedAttendanceDetails(
                    weekStart: $weekStart,
                    weekEnd: $weekEnd
                );

                if ($attendanceDetails->isEmpty()) {
                    throw ValidationException::withMessages([
                        'attendance' => [
                            'No active approved labour attendance was found for the selected week.',
                        ],
                    ]);
                }

                $existingAdjustments = $lockedRegister
                    ->details()
                    ->withTrashed()
                    ->get()
                    ->keyBy('labour_id')
                    ->map(
                        fn (WeeklyLabourPaymentDetail $detail): array => [
                            'additions' => (float) $detail->additions,
                            'deductions' => (float) $detail->deductions,
                            'adjustment_reason' => $detail->adjustment_reason,
                            'remarks' => $detail->remarks,
                        ]
                    );

                $lockedRegister->details()->withTrashed()->forceDelete();

                foreach (
                    $attendanceDetails->groupBy('labour_id')
                    as $labourId => $labourDetails
                ) {
                    $labour = $labourDetails->first()?->labour;

                    if (! $labour) {
                        continue;
                    }

                    $dailyDetails = $this->normaliseDailyAttendance(
                        labour: $labour,
                        attendanceDetails: $labourDetails
                    );

                    $adjustments = $existingAdjustments->get(
                        (int) $labourId,
                        [
                            'additions' => 0,
                            'deductions' => 0,
                            'adjustment_reason' => null,
                            'remarks' => null,
                        ]
                    );

                    $values = $this->calculateLabourValues(
                        labour: $labour,
                        dailyDetails: $dailyDetails,
                        additions: (float) $adjustments['additions'],
                        deductions: (float) $adjustments['deductions']
                    );

                    $detail = WeeklyLabourPaymentDetail::create([
                        'weekly_labour_payment_register_id' => $lockedRegister->id,
                        ...$values,
                        'adjustment_reason' => $adjustments['adjustment_reason'],
                        'remarks' => $adjustments['remarks'],
                        'is_active' => true,
                    ]);

                    $this->createProjectAllocations(
                        paymentDetail: $detail,
                        dailyDetails: $dailyDetails,
                        dailyRate: (float) $values['daily_wage_rate'],
                        otHourlyRate: (float) $values['ot_hourly_rate']
                    );
                }

                $lockedRegister->update([
                    'week_start_date' => $weekStart->toDateString(),
                    'week_end_date' => $weekEnd->toDateString(),
                    'status' => 'calculated',
                    'generated_by' => auth()->id(),
                    'generated_at' => now(),
                    'submitted_by' => null,
                    'submitted_at' => null,
                    'approved_by' => null,
                    'approved_at' => null,
                    'rejected_by' => null,
                    'rejected_at' => null,
                    'rejection_reason' => null,
                ]);

                $this->recalculateRegisterTotals($lockedRegister);

                return $lockedRegister->fresh([
                    'details.labour.labourGroup',
                    'details.allocations.project',
                ]);
            }
        );
    }

    public function recalculateDetail(
        WeeklyLabourPaymentDetail $detail
    ): WeeklyLabourPaymentDetail {
        /*
         * OT Amount is sourced from approved attendance and must not be
         * recalculated from OT Hours when labour additions/deductions change.
         */
        $detail->normal_wage = round(
            (float) $detail->payable_days
            * (float) $detail->daily_wage_rate,
            2
        );

        $detail->gross_wage = round(
            (float) $detail->normal_wage
            + (float) $detail->ot_wage
            + (float) $detail->additions,
            2
        );

        $detail->net_payable = round(
            (float) $detail->gross_wage
            - (float) $detail->deductions,
            2
        );

        $detail->save();

        $this->recalculateRegisterTotals($detail->register);

        return $detail->fresh();
    }

    public function recalculateRegisterTotals(
        WeeklyLabourPaymentRegister $register
    ): WeeklyLabourPaymentRegister {
        $details = $register
            ->details()
            ->where('is_active', true)
            ->get();

        $totalNormalWages = round((float) $details->sum('normal_wage'), 2);
        $totalOtWages = round((float) $details->sum('ot_wage'), 2);
        $totalAdditions = round((float) $details->sum('additions'), 2);
        $totalDeductions = round((float) $details->sum('deductions'), 2);

        $grossWages = round(
            $totalNormalWages + $totalOtWages + $totalAdditions,
            2
        );

        $netPayable = round(
            $grossWages - $totalDeductions,
            2
        );

        $register->update([
            'total_labours' => $details->count(),
            'total_full_days' => round((float) $details->sum('full_days'), 2),
            'total_half_days' => round((float) $details->sum('half_days'), 2),
            'total_payable_days' => round((float) $details->sum('payable_days'), 2),
            'total_normal_wages' => $totalNormalWages,
            'total_ot_hours' => round((float) $details->sum('ot_hours'), 2),
            'total_ot_wages' => $totalOtWages,
            'total_additions' => $totalAdditions,
            'total_deductions' => $totalDeductions,
            'gross_wages' => $grossWages,
            'net_payable' => $netPayable,
        ]);

        return $register->fresh();
    }

    /**
     * Reduce all project attendance to one authoritative row per labour/date.
     *
     * Absent at one project + Present at another project is valid.
     * Two positive-payable records for the same labour/date are blocked.
     */
    private function normaliseDailyAttendance(
        Labour $labour,
        Collection $attendanceDetails
    ): Collection {
        return $attendanceDetails
            ->groupBy(
                fn (LabourAttendanceDetail $detail): string =>
                    $detail->attendance?->attendance_date?->format('Y-m-d') ?? ''
            )
            ->filter(
                fn (Collection $details, string $date): bool =>
                    $date !== ''
            )
            ->flatMap(
                function (
                    Collection $details,
                    string $date
                ) use ($labour): Collection {
                    $regularDetails = $details
                        ->reject(
                            fn (LabourAttendanceDetail $detail): bool =>
                                $this->isAdditionalWork($detail)
                        )
                        ->values();

                    $additionalDetails = $details
                        ->filter(
                            fn (LabourAttendanceDetail $detail): bool =>
                                $this->isAdditionalWork($detail)
                        )
                        ->sortBy(
                            fn (LabourAttendanceDetail $detail): int =>
                                (int) $detail->labour_attendance_id
                        )
                        ->values();

                    /*
                     * Keep the existing duplicate-pay protection for Regular
                     * Attendance. A labourer may have only one positive-payable
                     * Regular attendance for a calendar date.
                     */
                    $payableRegular = $regularDetails
                        ->filter(
                            fn (LabourAttendanceDetail $detail): bool =>
                                $this->payableFactor($detail) > 0
                        )
                        ->values();

                    if ($payableRegular->count() > 1) {
                        $projects = $payableRegular
                            ->map(
                                fn (LabourAttendanceDetail $detail): string =>
                                    $detail->attendance?->project?->project_name
                                    ?? 'Unknown Project'
                            )
                            ->unique()
                            ->implode(', ');

                        throw ValidationException::withMessages([
                            'attendance' => [
                                "{$labour->full_name} has more than one payable Regular Attendance record on {$date}: {$projects}. Correct the attendance before generating the Labour Payment Register.",
                            ],
                        ]);
                    }

                    $result = collect();

                    /*
                     * Select one authoritative Regular row for the date.
                     * If there is no payable Regular row, retain the earliest
                     * non-payable Regular row for absence/leave reporting.
                     */
                    $regular = $payableRegular->first()
                        ?? $regularDetails
                            ->sortBy(
                                fn (LabourAttendanceDetail $detail): int =>
                                    (int) $detail->labour_attendance_id
                            )
                            ->first();

                    if ($regular) {
                        $result->push($regular);
                    }

                    /*
                     * Every Additional Work session is retained because each
                     * session can carry OT for a different project.
                     */
                    foreach ($additionalDetails as $additionalDetail) {
                        $result->push($additionalDetail);
                    }

                    return $result;
                }
            )
            ->values();
    }

    private function calculateLabourValues(
        Labour $labour,
        Collection $dailyDetails,
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
        $otAmount = 0.0;

        foreach ($dailyDetails as $detail) {
            $status = $detail->attendanceStatus;

            if (! $status) {
                continue;
            }

            $statusCode = $this->statusCode($detail);
            $payableFactor = $this->payableFactor($detail);

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

            if (
                ! $this->isAdditionalWork($detail)
                && (bool) $status->allows_normal_hours
            ) {
                $normalHours += (float) $detail->normal_hours;
            }

            if ((bool) $status->allows_ot_hours) {
                $otHours += (float) $detail->ot_hours;
                $otAmount += (float) ($detail->ot_amount ?? 0);
            }
        }

        $wageBasis = strtolower(
            trim((string) ($labour->wage_basis ?: 'daily'))
        );

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

        /*
         * Attendance OT Amount is authoritative. OT Hours and OT Rate are
         * retained for transparency, but payment uses the approved amount.
         */
        $otWage = round($otAmount, 2);

        $grossWage = round($normalWage + $otWage + $additions, 2);
        $netPayable = round($grossWage - $deductions, 2);

        return [
            'labour_id' => $labour->id,
            'labour_group_id' => $labour->labour_group_id,
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

    private function createProjectAllocations(
        WeeklyLabourPaymentDetail $paymentDetail,
        Collection $dailyDetails,
        float $dailyRate,
        float $otHourlyRate
    ): void {
        $payableDetails = $dailyDetails
            ->filter(
                fn (LabourAttendanceDetail $detail): bool =>
                    $this->payableFactor($detail) > 0
                    || (
                        $this->isAdditionalWork($detail)
                        && (
                            (float) $detail->ot_hours > 0
                            || (float) ($detail->ot_amount ?? 0) > 0
                        )
                    )
            )
            ->groupBy(
                fn (LabourAttendanceDetail $detail): int =>
                    (int) ($detail->attendance?->project_id ?? 0)
            );

        foreach ($payableDetails as $projectId => $details) {
            $first = $details->first();
            $project = $first?->attendance?->project;

            if (! $project) {
                continue;
            }

            $fullDays = 0.0;
            $halfDays = 0.0;
            $payableDays = 0.0;
            $normalHours = 0.0;
            $otHours = 0.0;
            $otAmount = 0.0;
            $dates = [];

            foreach ($details as $detail) {
                $factor = $this->payableFactor($detail);

                $payableDays += $factor;

                if ($factor >= 1) {
                    $fullDays += 1;
                } elseif ($factor > 0) {
                    $halfDays += 1;
                }

                if (
                    ! $this->isAdditionalWork($detail)
                    && (bool) $detail->attendanceStatus?->allows_normal_hours
                ) {
                    $normalHours += (float) $detail->normal_hours;
                }

                if ((bool) $detail->attendanceStatus?->allows_ot_hours) {
                    $otHours += (float) $detail->ot_hours;
                    $otAmount += (float) ($detail->ot_amount ?? 0);
                }

                $date = $detail->attendance?->attendance_date?->format('Y-m-d');

                if ($date) {
                    $dates[] = $date;
                }
            }

            $normalWage = round($payableDays * $dailyRate, 2);
            $otWage = round($otAmount, 2);

            WeeklyLabourPaymentAllocation::create([
                'weekly_labour_payment_detail_id' => $paymentDetail->id,
                'project_id' => (int) $projectId,
                'project_name' => $project->project_name,
                'project_code' => $project->project_code,
                'full_days' => round($fullDays, 2),
                'half_days' => round($halfDays, 2),
                'payable_days' => round($payableDays, 2),
                'normal_hours' => round($normalHours, 2),
                'ot_hours' => round($otHours, 2),
                'normal_wage' => $normalWage,
                'ot_wage' => $otWage,
                'total_wage' => round($normalWage + $otWage, 2),
                'attendance_dates' => array_values(array_unique($dates)),
                'is_active' => true,
            ]);
        }
    }

    private function approvedAttendanceDetails(
        Carbon $weekStart,
        Carbon $weekEnd
    ): Collection {
        return LabourAttendanceDetail::query()
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->whereHas(
                'attendance',
                function (
                    Builder $query
                ) use (
                    $weekStart,
                    $weekEnd
                ): void {
                    $query
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
                'attendance' => function ($query): void {
                    $query->select([
                        'id',
                        'project_id',
                        'attendance_date',
                        'attendance_type',
                        'work_session_name',
                        'status',
                        'is_active',
                    ])->with([
                        'project:id,project_name,project_code',
                    ]);
                },

                'labour:id,labour_code,full_name,labour_group_id,wage_basis,current_daily_rate,current_hourly_rate,current_monthly_rate,ot_calculation_type,current_ot_rate,ot_multiplier,normal_shift_hours,designation_role_id,labour_category_id,contractor_id',

                'attendanceStatus:id,code,name,short_name,counts_as_present,counts_as_absent,payable_factor,allows_normal_hours,allows_ot_hours',
            ])
            ->orderBy('labour_id')
            ->orderBy('labour_attendance_id')
            ->get();
    }

    private function isAdditionalWork(
        LabourAttendanceDetail $detail
    ): bool {
        return (
            $detail->attendance?->attendance_type
            ?? 'regular'
        ) === 'additional_work';
    }

    private function payableFactor(
        LabourAttendanceDetail $detail
    ): float {
        if ($this->isAdditionalWork($detail)) {
            return 0.0;
        }

        return round(
            (float) ($detail->attendanceStatus?->payable_factor ?? 0),
            2
        );
    }

    private function statusCode(
        LabourAttendanceDetail $detail
    ): string {
        $status = $detail->attendanceStatus;

        if (! $status) {
            return '';
        }

        return strtoupper(
            trim((string) (
                $status->code
                ?: $status->short_name
                ?: $status->name
            ))
        );
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

        $calculationType = strtolower(
            trim((string) (
                $labour->ot_calculation_type ?: 'standard'
            ))
        );

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
        WeeklyLabourPaymentRegister $register
    ): array {
        $weekStart = Carbon::parse($register->week_start_date)
            ->startOfWeek(Carbon::SUNDAY)
            ->startOfDay();

        $weekEnd = $weekStart
            ->copy()
            ->addDays(6)
            ->endOfDay();

        return [$weekStart, $weekEnd];
    }

    private function ensureRegisterCanBeCalculated(
        WeeklyLabourPaymentRegister $register
    ): void {
        if (! $register->is_active) {
            throw ValidationException::withMessages([
                'status' => [
                    'The Weekly Labour Payment Register is inactive.',
                ],
            ]);
        }

        if (in_array(
            $register->status,
            ['submitted', 'approved', 'paid'],
            true
        )) {
            throw ValidationException::withMessages([
                'status' => [
                    'Submitted, Approved, or Paid Labour Payment Registers cannot be recalculated.',
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
