<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\WeeklyWageSheet;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LabourWageReportController extends Controller
{
    private const ELIGIBLE_STATUSES = [
        'calculated',
        'submitted',
        'approved',
        'paid',
    ];

    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        [$fromDate, $toDate, $periodLabel] = $this->resolvePeriod($filters);

        return view('reports.labour-wages.index', [
            'filters' => $filters,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'periodLabel' => $periodLabel,
            'projects' => $this->projectOptions(),
            'engineers' => $this->engineerOptions(),
            ...$this->buildReport($filters, $fromDate, $toDate),
        ]);
    }

    public function pdf(Request $request)
    {
        $filters = $this->validatedFilters($request);
        [$fromDate, $toDate, $periodLabel] = $this->resolvePeriod($filters);
        $report = $this->buildReport($filters, $fromDate, $toDate);

        if ($report['wageSheets']->isEmpty()) {
            throw ValidationException::withMessages([
                'report' => [
                    'No eligible Weekly Wage Sheets were found for the selected report filters.',
                ],
            ]);
        }

        $fileName = sprintf(
            'Labour-Wage-Report-%s-%s.pdf',
            $fromDate->format('Ymd'),
            $toDate->format('Ymd')
        );

        return Pdf::loadView('reports.labour-wages.pdf', [
            'filters' => $filters,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'periodLabel' => $periodLabel,
            ...$report,
        ])
            ->setPaper('a4', 'landscape')
            ->download($fileName);
    }

    private function validatedFilters(Request $request): array
    {
        $defaults = [
            'scope' => 'all_projects',
            'period' => 'weekly',
            'status' => 'all_eligible',
            'week_start' => now()->startOfWeek(Carbon::SUNDAY)->toDateString(),
            'date' => now()->toDateString(),
            'month' => now()->format('Y-m'),
            'quarter' => (string) now()->quarter,
            'half_year' => now()->month <= 6 ? '1' : '2',
            'year' => (string) now()->year,
            'from_date' => now()->startOfMonth()->toDateString(),
            'to_date' => now()->endOfMonth()->toDateString(),
            'project_id' => null,
            'engineer_id' => null,
        ];

        $input = array_merge($defaults, $request->only(array_keys($defaults)));

        $validated = validator($input, [
            'scope' => ['required', Rule::in([
                'all_projects',
                'specific_project',
                'all_engineers',
                'specific_engineer',
            ])],
            'period' => ['required', Rule::in([
                'daily',
                'weekly',
                'monthly',
                'quarterly',
                'half_yearly',
                'yearly',
                'custom',
            ])],
            'status' => ['required', Rule::in([
                'all_eligible',
                ...self::ELIGIBLE_STATUSES,
            ])],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'engineer_id' => ['nullable', 'integer', 'exists:users,id'],
            'date' => ['nullable', 'date'],
            'week_start' => ['nullable', 'date'],
            'month' => ['nullable', 'date_format:Y-m'],
            'quarter' => ['nullable', Rule::in(['1', '2', '3', '4'])],
            'half_year' => ['nullable', Rule::in(['1', '2'])],
            'year' => ['nullable', 'integer', 'min:2020', 'max:2100'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date'],
        ])->validate();

        if ($validated['scope'] === 'specific_project' && empty($validated['project_id'])) {
            throw ValidationException::withMessages([
                'project_id' => ['Select a project for the Specific Project report scope.'],
            ]);
        }

        if ($validated['scope'] === 'specific_engineer' && empty($validated['engineer_id'])) {
            throw ValidationException::withMessages([
                'engineer_id' => ['Select an engineer for the Specific Engineer report scope.'],
            ]);
        }

        return $validated;
    }

    private function resolvePeriod(array $filters): array
    {
        return match ($filters['period']) {
            'daily' => $this->dailyPeriod($filters),
            'weekly' => $this->weeklyPeriod($filters),
            'monthly' => $this->monthlyPeriod($filters),
            'quarterly' => $this->quarterPeriod($filters),
            'half_yearly' => $this->halfYearPeriod($filters),
            'yearly' => $this->yearPeriod($filters),
            default => $this->customPeriod($filters),
        };
    }

    private function dailyPeriod(array $filters): array
    {
        $date = Carbon::parse($filters['date'])->startOfDay();

        return [$date, $date->copy()->endOfDay(), $date->format('d M Y')];
    }

    private function weeklyPeriod(array $filters): array
    {
        $start = Carbon::parse($filters['week_start'])
            ->startOfWeek(Carbon::SUNDAY)
            ->startOfDay();

        $end = $start->copy()->addDays(6)->endOfDay();

        return [
            $start,
            $end,
            $start->format('d M Y') . ' - ' . $end->format('d M Y'),
        ];
    }

    private function monthlyPeriod(array $filters): array
    {
        $start = Carbon::createFromFormat('Y-m', $filters['month'])
            ->startOfMonth()
            ->startOfDay();

        return [
            $start,
            $start->copy()->endOfMonth()->endOfDay(),
            $start->format('F Y'),
        ];
    }

    private function quarterPeriod(array $filters): array
    {
        $year = (int) $filters['year'];
        $quarter = (int) $filters['quarter'];
        $startMonth = (($quarter - 1) * 3) + 1;
        $start = Carbon::create($year, $startMonth, 1)->startOfDay();

        return [
            $start,
            $start->copy()->addMonths(3)->subDay()->endOfDay(),
            "Q{$quarter} {$year}",
        ];
    }

    private function halfYearPeriod(array $filters): array
    {
        $year = (int) $filters['year'];
        $half = (int) $filters['half_year'];
        $start = Carbon::create($year, $half === 1 ? 1 : 7, 1)->startOfDay();

        return [
            $start,
            $start->copy()->addMonths(6)->subDay()->endOfDay(),
            "H{$half} {$year}",
        ];
    }

    private function yearPeriod(array $filters): array
    {
        $year = (int) $filters['year'];
        $start = Carbon::create($year, 1, 1)->startOfDay();

        return [$start, $start->copy()->endOfYear()->endOfDay(), (string) $year];
    }

    private function customPeriod(array $filters): array
    {
        $start = Carbon::parse($filters['from_date'])->startOfDay();
        $end = Carbon::parse($filters['to_date'])->endOfDay();

        if ($start->gt($end)) {
            throw ValidationException::withMessages([
                'to_date' => ['The report end date must be on or after the start date.'],
            ]);
        }

        return [
            $start,
            $end,
            $start->format('d M Y') . ' - ' . $end->format('d M Y'),
        ];
    }

    private function buildReport(
        array $filters,
        Carbon $fromDate,
        Carbon $toDate
    ): array {
        $assignments = $this->engineerAssignments();

        $query = WeeklyWageSheet::query()
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->with([
                'project:id,project_code,project_name,status',
                'details' => function ($query): void {
                    $query
                        ->where('is_active', true)
                        ->whereNull('deleted_at')
                        ->with([
                            'labour:id,labour_code,full_name,labour_group_id',
                            'labour.labourGroup:id,code,name,sort_order',
                            'designationRole:id,name',
                        ]);
                },
            ]);

        if ($filters['status'] === 'all_eligible') {
            $query->whereIn('status', self::ELIGIBLE_STATUSES);
        } else {
            $query->where('status', $filters['status']);
        }

        if ($filters['period'] === 'daily') {
            $date = $fromDate->toDateString();

            $query
                ->whereDate('week_start_date', '<=', $date)
                ->whereDate('week_end_date', '>=', $date);
        } else {
            /*
             * Longer reports classify a Weekly Wage Sheet by its Sunday
             * week_start_date. This keeps Reports read-only and prevents a
             * second payroll calculation engine from being introduced.
             */
            $query->whereBetween('week_start_date', [
                $fromDate->toDateString(),
                $toDate->toDateString(),
            ]);
        }

        if ($filters['scope'] === 'specific_project') {
            $query->where('project_id', (int) $filters['project_id']);
        }

        if (in_array($filters['scope'], ['all_engineers', 'specific_engineer'], true)) {
            $projectIds = $assignments->keys();

            if ($filters['scope'] === 'specific_engineer') {
                $engineerId = (int) $filters['engineer_id'];

                $projectIds = $assignments
                    ->filter(
                        fn (Collection $engineers): bool =>
                            $engineers->contains(
                                fn (array $engineer): bool =>
                                    (int) $engineer['id'] === $engineerId
                            )
                    )
                    ->keys();
            }

            $projectIds->isEmpty()
                ? $query->whereRaw('1 = 0')
                : $query->whereIn('project_id', $projectIds);
        }

        $wageSheets = $query
            ->orderBy('week_start_date')
            ->orderBy('project_id')
            ->get();

        $projectRows = $this->projectRows($wageSheets, $assignments);

        return [
            'wageSheets' => $wageSheets,
            'projectRows' => $projectRows,
            'engineerGroups' => $this->engineerGroups($projectRows, $filters),
            'totals' => $this->totals($wageSheets),
        ];
    }

    private function projectRows(
        Collection $wageSheets,
        Collection $assignments
    ): Collection {
        return $wageSheets
            ->groupBy('project_id')
            ->map(function (Collection $sheets, $projectId) use ($assignments): array {
                $first = $sheets->first();
                $engineers = $assignments->get((int) $projectId, collect());

                $labourIds = $sheets
                    ->flatMap(fn (WeeklyWageSheet $sheet) => $sheet->details->pluck('labour_id'))
                    ->filter()
                    ->unique()
                    ->values();

                return [
                    'project_id' => (int) $projectId,
                    'project_name' => $first?->project?->project_name ?? 'Unknown Project',
                    'project_code' => $first?->project?->project_code,
                    'engineers' => $engineers,
                    'engineer_names' => $engineers->pluck('name')->implode(', '),
                    'engineer_group_key' => $engineers->pluck('id')->sort()->implode('-'),
                    'engineer_group_label' => $engineers->isEmpty()
                        ? 'Unassigned Engineer'
                        : $engineers->pluck('name')->sort()->implode(' + '),
                    'wage_sheets' => $sheets->values(),
                    'wage_sheet_count' => $sheets->count(),
                    'labour_ids' => $labourIds,
                    'labour_count' => $labourIds->count(),
                    'payable_days' => round((float) $sheets->sum('total_payable_days'), 2),
                    'normal_wages' => round((float) $sheets->sum('total_normal_wages'), 2),
                    'ot_hours' => round((float) $sheets->sum('total_ot_hours'), 2),
                    'ot_amount' => round((float) $sheets->sum('total_ot_wages'), 2),
                    'additions' => round((float) $sheets->sum('total_labour_additions'), 2),
                    'deductions' => round((float) $sheets->sum('total_labour_deductions'), 2),
                    'net_payable' => round((float) $sheets->sum('net_labour_wages'), 2),
                    'project_payable' => round((float) $sheets->sum('total_project_payable'), 2),
                ];
            })
            ->sortBy('project_name')
            ->values();
    }

    private function engineerGroups(
        Collection $projectRows,
        array $filters
    ): Collection {
        if ($filters['scope'] === 'specific_engineer') {
            $engineer = $this->engineerOptions()
                ->firstWhere('id', (int) $filters['engineer_id']);

            return collect([[
                'key' => 'specific-engineer',
                'label' => $engineer['name'] ?? 'Selected Engineer',
                'projects' => $projectRows,
                ...$this->projectRowTotals($projectRows),
            ]]);
        }

        if ($filters['scope'] !== 'all_engineers') {
            return collect();
        }

        /*
         * Shared projects stay once under a combined Engineer Assignment
         * group. This prevents wage values from being counted twice.
         */
        return $projectRows
            ->groupBy(fn (array $row): string => $row['engineer_group_key'] ?: 'unassigned')
            ->map(function (Collection $projects): array {
                return [
                    'key' => $projects->first()['engineer_group_key'] ?: 'unassigned',
                    'label' => $projects->first()['engineer_group_label'],
                    'projects' => $projects->values(),
                    ...$this->projectRowTotals($projects),
                ];
            })
            ->sortBy('label')
            ->values();
    }

    private function projectRowTotals(Collection $rows): array
    {
        return [
            'project_count' => $rows->count(),
            'labour_count' => $rows
                ->flatMap(fn (array $row) => $row['labour_ids'])
                ->unique()
                ->count(),
            'payable_days' => round((float) $rows->sum('payable_days'), 2),
            'normal_wages' => round((float) $rows->sum('normal_wages'), 2),
            'ot_amount' => round((float) $rows->sum('ot_amount'), 2),
            'net_payable' => round((float) $rows->sum('net_payable'), 2),
        ];
    }

    private function totals(Collection $wageSheets): array
    {
        $labourIds = $wageSheets
            ->flatMap(fn (WeeklyWageSheet $sheet) => $sheet->details->pluck('labour_id'))
            ->filter()
            ->unique();

        return [
            'wage_sheet_count' => $wageSheets->count(),
            'project_count' => $wageSheets->pluck('project_id')->unique()->count(),
            'labour_count' => $labourIds->count(),
            'payable_days' => round((float) $wageSheets->sum('total_payable_days'), 2),
            'normal_wages' => round((float) $wageSheets->sum('total_normal_wages'), 2),
            'ot_hours' => round((float) $wageSheets->sum('total_ot_hours'), 2),
            'ot_amount' => round((float) $wageSheets->sum('total_ot_wages'), 2),
            'additions' => round((float) $wageSheets->sum('total_labour_additions'), 2),
            'deductions' => round((float) $wageSheets->sum('total_labour_deductions'), 2),
            'net_payable' => round((float) $wageSheets->sum('net_labour_wages'), 2),
            'project_payable' => round((float) $wageSheets->sum('total_project_payable'), 2),
        ];
    }

    private function engineerAssignments(): Collection
    {
        return DB::table('project_user')
            ->join('users', 'users.id', '=', 'project_user.user_id')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->where('roles.name', 'Engineer')
            ->select([
                'project_user.project_id',
                'users.id',
                'users.name',
                'users.employee_code',
            ])
            ->orderBy('users.name')
            ->get()
            ->groupBy('project_id')
            ->map(
                fn (Collection $rows): Collection =>
                    $rows
                        ->map(fn ($row): array => [
                            'id' => (int) $row->id,
                            'name' => $row->name,
                            'employee_code' => $row->employee_code,
                        ])
                        ->unique('id')
                        ->values()
            );
    }

    private function projectOptions(): Collection
    {
        return Project::query()
            ->orderBy('project_name')
            ->get(['id', 'project_code', 'project_name']);
    }

    private function engineerOptions(): Collection
    {
        return DB::table('users')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->where('roles.name', 'Engineer')
            ->select([
                'users.id',
                'users.employee_code',
                'users.name',
            ])
            ->orderBy('users.name')
            ->get()
            ->map(fn ($row): array => [
                'id' => (int) $row->id,
                'name' => $row->name,
                'employee_code' => $row->employee_code,
            ])
            ->values();
    }
}
