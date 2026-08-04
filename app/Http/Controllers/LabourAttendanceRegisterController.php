<?php

namespace App\Http\Controllers;

use App\Exports\LabourAttendanceRegisterExport;
use App\Models\Contractor;
use App\Models\DesignationRole;
use App\Models\LabourAttendanceDetail;
use App\Models\LabourCategory;
use App\Models\Shift;
use App\Support\ProjectAccess;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class LabourAttendanceRegisterController extends Controller
{
    /**
     * Display the Labour Attendance Register.
     */
    public function index(Request $request): View
    {
        $filters = $this->resolveFilters($request);

        $register = $this->buildRegister($filters);

        return view(
            'labour-attendance-register.index',
            [
                ...$register,

                'filters' => $filters,

                'projects' =>
                    ProjectAccess::availableProjects(),

                'shifts' =>
                    Shift::query()
                        ->active()
                        ->ordered()
                        ->get(),

                'contractors' =>
                    Contractor::query()
                        ->where('status', 1)
                        ->orderBy('contractor_name')
                        ->get(),

                'labourCategories' =>
                    LabourCategory::query()
                        ->where('is_active', true)
                        ->orderBy('category_name')
                        ->get(),

                'designationRoles' =>
                    DesignationRole::query()
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get(),

                'availableYears' =>
                    $this->availableYears(),
            ]
        );
    }


    /**
     * Download the current register as Excel.
     */
    public function exportExcel(
        Request $request
    ): BinaryFileResponse {
        $filters = $this->resolveFilters(
            $request
        );

        $register = $this->buildRegister(
            $filters
        );

        $fileName =
            $this->exportFileName(
                filters: $filters,
                extension: 'xlsx'
            );

        return Excel::download(
            new LabourAttendanceRegisterExport(
                projectGroups:
                    $register['projectGroups'],

                dateColumns:
                    $register['dateColumns'],

                periodLabel:
                    $register['periodLabel'],

                summary:
                    $register['summary'],

                filters:
                    $filters
            ),
            $fileName
        );
    }

    /**
     * Download the current register as PDF.
     */
    public function exportPdf(
        Request $request
    ) {
        $filters = $this->resolveFilters(
            $request
        );

        $register = $this->buildRegister(
            $filters
        );

        $fileName =
            $this->exportFileName(
                filters: $filters,
                extension: 'pdf'
            );

        $pdf = Pdf::loadView(
            'labour-attendance-register.pdf',
            [
                ...$register,
                'filters' => $filters,
            ]
        )
            ->setPaper(
                'a4',
                'landscape'
            );

        return $pdf->download(
            $fileName
        );
    }

    /**
     * Resolve and normalize register filters.
     */
    private function resolveFilters(
        Request $request
    ): array {
        $periodType = strtolower(
            trim(
                (string) $request->input(
                    'period_type',
                    'monthly'
                )
            )
        );

        if (! in_array(
            $periodType,
            ['monthly', 'weekly'],
            true
        )) {
            $periodType = 'monthly';
        }

        $month = $request->integer('month');

        if ($month < 1 || $month > 12) {
            $month = (int) now()->format('n');
        }

        $year = $request->integer('year');

        if ($year < 2020 || $year > 2100) {
            $year = (int) now()->format('Y');
        }

        $weekStart = $request->filled('week_start')
            ? Carbon::parse(
                $request->input('week_start')
            )->startOfDay()
            : now()
                ->startOfWeek(Carbon::SUNDAY)
                ->startOfDay();

        /*
         * Always normalize weekly periods to Sunday.
         */
        if (! $weekStart->isSunday()) {
            $weekStart = $weekStart
                ->copy()
                ->startOfWeek(Carbon::SUNDAY);
        }

        return [
            'period_type' => $periodType,

            'month' => $month,
            'year' => $year,

            'week_start' =>
                $weekStart->toDateString(),

            'project_id' =>
                $request->filled('project_id')
                    ? $request->integer('project_id')
                    : null,

            'shift_id' =>
                $request->filled('shift_id')
                    ? $request->integer('shift_id')
                    : null,

            'contractor_id' =>
                $request->filled('contractor_id')
                    ? $request->integer('contractor_id')
                    : null,

            'labour_category_id' =>
                $request->filled('labour_category_id')
                    ? $request->integer(
                        'labour_category_id'
                    )
                    : null,

            'designation_role_id' =>
                $request->filled('designation_role_id')
                    ? $request->integer(
                        'designation_role_id'
                    )
                    : null,

            'approved_only' =>
                $request->has('approved_only')
                    ? $request->boolean(
                        'approved_only'
                    )
                    : true,
        ];
    }

    /**
     * Build the reusable register dataset.
     */
    private function buildRegister(
        array $filters
    ): array {
        [
            $periodStart,
            $periodEnd,
        ] = $this->resolvePeriodDates(
            $filters
        );

        $dateColumns = collect();

        $cursor = $periodStart->copy();

        while ($cursor->lte($periodEnd)) {
            $dateColumns->push([
                'key' =>
                    $cursor->toDateString(),

                'date' =>
                    $cursor->copy(),

                'day_number' =>
                    (int) $cursor->format('j'),

                'weekday_short' =>
                    strtoupper(
                        $cursor->format('D')
                    ),

                'is_sunday' =>
                    $cursor->isSunday(),
            ]);

            $cursor->addDay();
        }

        $details = LabourAttendanceDetail::query()
            ->where('is_active', true)
            ->whereNull('deleted_at')

            ->whereHas(
                'attendance',
                function (
                    Builder $query
                ) use (
                    $filters,
                    $periodStart,
                    $periodEnd
                ): void {
                    $query
                        ->where('is_active', true)
                        ->whereNull('deleted_at')

                        ->whereIn(
                            'project_id',
                            ProjectAccess::allowedProjectIds()
                        )

                        ->whereBetween(
                            'attendance_date',
                            [
                                $periodStart->toDateString(),
                                $periodEnd->toDateString(),
                            ]
                        );

                    if ($filters['approved_only']) {
                        $query->where(
                            'status',
                            'approved'
                        );
                    }

                    if ($filters['project_id']) {
                        $query->where(
                            'project_id',
                            $filters['project_id']
                        );
                    }

                    if ($filters['shift_id']) {
                        $query->where(
                            'shift_id',
                            $filters['shift_id']
                        );
                    }
                }
            )

            ->when(
                $filters['contractor_id'],
                fn (
                    Builder $query,
                    int $contractorId
                ): Builder =>
                    $query->where(
                        'contractor_id',
                        $contractorId
                    )
            )

            ->when(
                $filters['labour_category_id'],
                fn (
                    Builder $query,
                    int $categoryId
                ): Builder =>
                    $query->where(
                        'labour_category_id',
                        $categoryId
                    )
            )

            ->when(
                $filters['designation_role_id'],
                fn (
                    Builder $query,
                    int $designationId
                ): Builder =>
                    $query->where(
                        'designation_role_id',
                        $designationId
                    )
            )

            ->with([
                'attendance:id,attendance_number,project_id,attendance_date,shift_id,status,is_active',

                'attendance.project:id,project_name,project_code',

                'attendance.shift:id,code,name',

                'labour:id,labour_code,full_name',

                'attendanceStatus:id,code,name,short_name,counts_as_present,counts_as_absent',

                'workingStatus:id,code,name',

                'contractor:id,contractor_name',

                'labourCategory:id,category_name',

                'designationRole:id,name',
            ])

            ->orderBy('labour_id')
            ->orderBy('labour_attendance_id')
            ->get();

        $registerRows = $this
            ->groupRegisterRows(
                details: $details,
                dateColumns: $dateColumns
            );

        $projectGroups = $registerRows
            ->groupBy(
                fn (array $row): string =>
                    (string) $row['project_id']
            )
            ->map(function (
                Collection $rows
            ): array {
                $first = $rows->first();

                return [
                    'project_id' =>
                        $first['project_id'],

                    'project_name' =>
                        $first['project_name'],

                    'project_code' =>
                        $first['project_code'],

                    'rows' =>
                        $rows->values(),

                    'summary' =>
                        $this->buildSummary(
                            $rows
                        ),
                ];
            })
            ->sortBy(
                'project_name'
            )
            ->values();

        return [
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,

            'periodLabel' =>
                $this->periodLabel(
                    filters: $filters,
                    periodStart: $periodStart,
                    periodEnd: $periodEnd
                ),

            'dateColumns' => $dateColumns,

            'registerRows' =>
                $registerRows,

            'projectGroups' =>
                $projectGroups,

            'summary' =>
                $this->buildSummary(
                    $registerRows
                ),
        ];
    }

    /**
     * Resolve monthly or weekly date boundaries.
     */
    private function resolvePeriodDates(
        array $filters
    ): array {
        if (
            $filters['period_type']
            === 'weekly'
        ) {
            $start = Carbon::parse(
                $filters['week_start']
            )
                ->startOfWeek(Carbon::SUNDAY)
                ->startOfDay();

            $end = $start
                ->copy()
                ->addDays(6)
                ->endOfDay();

            return [$start, $end];
        }

        $start = Carbon::create(
            $filters['year'],
            $filters['month'],
            1
        )->startOfDay();

        $end = $start
            ->copy()
            ->endOfMonth()
            ->endOfDay();

        return [$start, $end];
    }

    /**
     * Build a human-readable period label.
     */
    private function periodLabel(
        array $filters,
        Carbon $periodStart,
        Carbon $periodEnd
    ): string {
        if (
            $filters['period_type']
            === 'weekly'
        ) {
            return $periodStart->format('d M Y')
                . ' - '
                . $periodEnd->format('d M Y');
        }

        return $periodStart->format('F Y');
    }

    /**
     * Group daily details by labour and project.
     */
    private function groupRegisterRows(
        Collection $details,
        Collection $dateColumns
    ): Collection {
        $emptyDates = $dateColumns
            ->pluck('key')
            ->mapWithKeys(
                fn (string $date): array => [
                    $date => null,
                ]
            )
            ->all();

        return $details
            ->groupBy(function (
                LabourAttendanceDetail $detail
            ): string {
                return implode(':', [
                    (int) $detail->labour_id,
                    (int) $detail
                        ->attendance
                        ?->project_id,
                ]);
            })

            ->map(function (
                Collection $labourDetails
            ) use ($emptyDates): array {
                /** @var LabourAttendanceDetail $first */
                $first = $labourDetails->first();

                $days = $emptyDates;

                $totals = [
                    'present' => 0,
                    'absent' => 0,
                    'half_day' => 0,
                    'leave' => 0,
                    'weekly_off' => 0,
                    'holiday' => 0,
                    'other' => 0,
                    'normal_hours' => 0.0,
                    'ot_hours' => 0.0,
                ];

                foreach (
                    $labourDetails as $detail
                ) {
                    $attendanceDate =
                        $detail
                            ->attendance
                            ?->attendance_date;

                    if (! $attendanceDate) {
                        continue;
                    }

                    $date = $attendanceDate
                        instanceof Carbon
                            ? $attendanceDate
                            : Carbon::parse(
                                $attendanceDate
                            );

                    $dateKey =
                        $date->toDateString();

                    if (
                        ! array_key_exists(
                            $dateKey,
                            $days
                        )
                    ) {
                        continue;
                    }

                    $statusCode =
                        $this->statusCode(
                            $detail
                        );

                    $days[$dateKey] = [
                        'code' =>
                            $statusCode,

                        'label' =>
                            $detail
                                ->attendanceStatus
                                ?->name
                            ?? 'Unknown',

                        'working_status' =>
                            $detail
                                ->workingStatus
                                ?->name,

                        'check_in' =>
                            $this->formatTime(
                                $detail
                                    ->check_in_time
                            ),

                        'check_out' =>
                            $this->formatTime(
                                $detail
                                    ->check_out_time
                            ),

                        'normal_hours' =>
                            (float) $detail
                                ->normal_hours,

                        'ot_hours' =>
                            (float) $detail
                                ->ot_hours,

                        'attendance_number' =>
                            $detail
                                ->attendance
                                ?->attendance_number,
                    ];

                    $bucket =
                        $this->summaryBucket(
                            $statusCode
                        );

                    $totals[$bucket]++;

                    $totals['normal_hours'] +=
                        (float) $detail
                            ->normal_hours;

                    $totals['ot_hours'] +=
                        (float) $detail
                            ->ot_hours;
                }

                $totals['normal_hours'] =
                    round(
                        $totals['normal_hours'],
                        2
                    );

                $totals['ot_hours'] =
                    round(
                        $totals['ot_hours'],
                        2
                    );

                return [
                    'labour_id' =>
                        $first->labour_id,

                    'labour_name' =>
                        $first
                            ->labour
                            ?->full_name
                        ?? 'Unavailable Labour',

                    'designation' =>
                        $first
                            ->designationRole
                            ?->name
                        ?? '—',

                    'project_id' =>
                        $first
                            ->attendance
                            ?->project_id,

                    'project_name' =>
                        $first
                            ->attendance
                            ?->project
                            ?->project_name
                        ?? '—',

                    'project_code' =>
                        $first
                            ->attendance
                            ?->project
                            ?->project_code,

                    /*
                     * Retained for future export use,
                     * but not shown on the register screen.
                     */
                    'shift_name' =>
                        $first
                            ->attendance
                            ?->shift
                            ?->name
                        ?? 'No Shift',

                    'days' => $days,
                    'totals' => $totals,
                ];
            })

            ->sortBy([
                ['project_name', 'asc'],
                ['labour_name', 'asc'],
            ])

            ->values();
    }

    /**
     * Build register totals.
     */
    private function buildSummary(
        Collection $rows
    ): array {
        return [
            'total_labour' =>
                $rows->count(),

            'present' =>
                (int) $rows->sum(
                    fn (array $row): int =>
                        (int) $row[
                            'totals'
                        ]['present']
                ),

            'absent' =>
                (int) $rows->sum(
                    fn (array $row): int =>
                        (int) $row[
                            'totals'
                        ]['absent']
                ),

            'half_day' =>
                (int) $rows->sum(
                    fn (array $row): int =>
                        (int) $row[
                            'totals'
                        ]['half_day']
                ),

            'leave' =>
                (int) $rows->sum(
                    fn (array $row): int =>
                        (int) $row[
                            'totals'
                        ]['leave']
                ),

            'normal_hours' =>
                round(
                    (float) $rows->sum(
                        fn (array $row): float =>
                            (float) $row[
                                'totals'
                            ]['normal_hours']
                    ),
                    2
                ),

            'ot_hours' =>
                round(
                    (float) $rows->sum(
                        fn (array $row): float =>
                            (float) $row[
                                'totals'
                            ]['ot_hours']
                    ),
                    2
                ),
        ];
    }

    /**
     * Normalize the displayed status code.
     */
    private function statusCode(
        LabourAttendanceDetail $detail
    ): string {
        $code = strtoupper(
            trim(
                (string) (
                    $detail
                        ->attendanceStatus
                        ?->short_name
                    ?: $detail
                        ->attendanceStatus
                        ?->code
                    ?: $detail
                        ->attendanceStatus
                        ?->name
                )
            )
        );

        return match ($code) {
            'PRESENT' => 'P',
            'ABSENT' => 'A',

            'HALF DAY',
            'HALF-DAY',
            'HALF_DAY',
            'HALFDAY' => 'HD',

            'LEAVE' => 'L',

            'WEEKLY OFF',
            'WEEKLY-OFF',
            'WEEKLY_OFF' => 'WO',

            'HOLIDAY' => 'H',

            default =>
                $code !== ''
                    ? mb_substr(
                        $code,
                        0,
                        3
                    )
                    : '—',
        };
    }

    /**
     * Select the appropriate summary counter.
     */
    private function summaryBucket(
        string $statusCode
    ): string {
        return match ($statusCode) {
            'P' => 'present',
            'A' => 'absent',
            'HD' => 'half_day',
            'L' => 'leave',
            'WO' => 'weekly_off',
            'H' => 'holiday',
            default => 'other',
        };
    }


    /**
     * Build a safe export file name.
     */
    private function exportFileName(
        array $filters,
        string $extension
    ): string {
        if (
            $filters['period_type']
            === 'weekly'
        ) {
            $weekStart = Carbon::parse(
                $filters['week_start']
            )
                ->startOfWeek(Carbon::SUNDAY);

            $weekEnd = $weekStart
                ->copy()
                ->addDays(6);

            $period = $weekStart->format('Y-m-d')
                . '_to_'
                . $weekEnd->format('Y-m-d');
        } else {
            $period = sprintf(
                '%04d-%02d',
                (int) $filters['year'],
                (int) $filters['month']
            );
        }

        return "labour-attendance-register-{$period}.{$extension}";
    }

    /**
     * Useful year values for monthly filters.
     */
    private function availableYears(): array
    {
        $currentYear =
            (int) now()->format('Y');

        return range(
            $currentYear - 3,
            $currentYear + 1
        );
    }

    /**
     * Format a database time for tooltips.
     */
    private function formatTime(
        mixed $value
    ): ?string {
        if (blank($value)) {
            return null;
        }

        return substr(
            (string) $value,
            0,
            5
        );
    }
}
