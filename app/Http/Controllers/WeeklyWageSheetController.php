<?php

namespace App\Http\Controllers;

use App\Exports\WeeklyWageSheetExport;
use App\Helpers\AuditHelper;
use App\Models\Activity;
use App\Models\Contractor;
use App\Models\WeeklyWageSheet;
use App\Models\WeeklyWageSheetCharge;
use App\Models\WeeklyWageSheetDetail;
use App\Services\WeeklyWageCalculationService;
use App\Support\ProjectAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class WeeklyWageSheetController extends Controller
{
    public function __construct(
        private readonly WeeklyWageCalculationService $calculationService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $query = WeeklyWageSheet::query()
            ->whereIn(
                'project_id',
                ProjectAccess::allowedProjectIds()
            )
            ->with([
                'project:id,project_name,project_code',
                'generatedBy:id,name',
                'submittedBy:id,name',
                'approvedBy:id,name',
                'paidBy:id,name',
            ]);

        if ($request->filled('search')) {
            $search = trim(
                $request->string('search')->toString()
            );

            $query->where(function (
                Builder $builder
            ) use ($search): void {
                $builder
                    ->where(
                        'wage_sheet_number',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas(
                        'project',
                        function (
                            Builder $projectQuery
                        ) use ($search): void {
                            $projectQuery
                                ->where(
                                    'project_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'project_code',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
            });
        }

        if ($request->filled('project_id')) {
            $query->where(
                'project_id',
                $request->integer('project_id')
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->string('status')->toString()
            );
        }

        if ($request->filled('week_start_date')) {
            $query->whereDate(
                'week_start_date',
                $request->input('week_start_date')
            );
        }

        $weeklyWageSheets = $query
            ->orderByDesc('week_start_date')
            ->orderByDesc('id')
            ->paginate(
                config('rds.pagination.per_page', 15)
            )
            ->withQueryString();

        return view(
            'weekly-wage-sheets.index',
            [
                'weeklyWageSheets' => $weeklyWageSheets,
                'projects' => ProjectAccess::availableProjects(),
                'statuses' => [
                    'draft' => 'Draft',
                    'calculated' => 'Calculated',
                    'submitted' => 'Submitted',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'paid' => 'Paid',
                ],
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        return view(
            'weekly-wage-sheets.create',
            [
                'projects' => ProjectAccess::availableProjects(),
                'defaultWeekStart' => now()
                    ->startOfWeek(Carbon::SUNDAY)
                    ->toDateString(),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store Draft
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $this->validateCreateRequest(
            $request
        );

        $weekStart = Carbon::parse(
            $validated['week_start_date']
        )
            ->startOfWeek(Carbon::SUNDAY)
            ->startOfDay();

        $weekEnd = $weekStart
            ->copy()
            ->addDays(6)
            ->endOfDay();

        ProjectAccess::authorize(
            (int) $validated['project_id']
        );

        $duplicateExists = WeeklyWageSheet::query()
            ->where(
                'project_id',
                $validated['project_id']
            )
            ->whereDate(
                'week_start_date',
                $weekStart->toDateString()
            )
            ->where('is_active', true)
            ->exists();

        if ($duplicateExists) {
            throw ValidationException::withMessages([
                'week_start_date' => [
                    'An active weekly wage sheet already exists for this project and week.',
                ],
            ]);
        }

        try {
            $wageSheet = DB::transaction(
                function () use (
                    $validated,
                    $weekStart,
                    $weekEnd
                ): WeeklyWageSheet {
                    $wageSheet = WeeklyWageSheet::create([
                        'wage_sheet_number' =>
                            $this->generateWageSheetNumber(),

                        'project_id' =>
                            (int) $validated['project_id'],

                        'week_start_date' =>
                            $weekStart->toDateString(),

                        'week_end_date' =>
                            $weekEnd->toDateString(),

                        'status' => 'draft',

                        'remarks' =>
                            $this->nullableTrim(
                                $validated['remarks'] ?? null
                            ),

                        'is_active' => true,
                    ]);

                    AuditHelper::log(
                        'Weekly Wage Sheets',
                        'Created',
                        WeeklyWageSheet::class,
                        $wageSheet->id,
                        "Weekly Wage Sheet '{$wageSheet->wage_sheet_number}' was created as Draft.",
                        null,
                        $wageSheet->fresh()->toArray()
                    );

                    return $wageSheet;
                }
            );

            return redirect()
                ->route(
                    'weekly-wage-sheets.show',
                    $wageSheet
                )
                ->with(
                    'success',
                    'Weekly Wage Sheet created successfully as Draft.'
                );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to create the Weekly Wage Sheet.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        WeeklyWageSheet $weeklyWageSheet
    ): View {
        ProjectAccess::authorize(
            (int) $weeklyWageSheet->project_id
        );

        $weeklyWageSheet->load(
            $this->relationships()
        );

        return view(
            'weekly-wage-sheets.show',
            [
                'weeklyWageSheet' => $weeklyWageSheet,

                'activities' => Activity::query()
    ->where('is_active', true)
    ->orderBy('activity_name')
    ->get(),

                'contractors' => Contractor::query()
                    ->where('status', 1)
                    ->orderBy('contractor_name')
                    ->get(),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Generate / Recalculate
    |--------------------------------------------------------------------------
    */

    public function generate(
        WeeklyWageSheet $weeklyWageSheet
    ): RedirectResponse {
        ProjectAccess::authorize(
            (int) $weeklyWageSheet->project_id
        );

        $oldValues = $weeklyWageSheet->toArray();

        try {
            $calculatedSheet =
                $this->calculationService->generate(
                    $weeklyWageSheet
                );

            AuditHelper::log(
                'Weekly Wage Sheets',
                'Calculated',
                WeeklyWageSheet::class,
                $calculatedSheet->id,
                "Weekly Wage Sheet '{$calculatedSheet->wage_sheet_number}' was calculated from approved attendance.",
                $oldValues,
                $calculatedSheet->toArray()
            );

            return redirect()
                ->route(
                    'weekly-wage-sheets.show',
                    $calculatedSheet
                )
                ->with(
                    'success',
                    'Weekly wages calculated successfully from approved attendance.'
                );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route(
                    'weekly-wage-sheets.show',
                    $weeklyWageSheet
                )
                ->with(
                    'error',
                    'Unable to calculate the Weekly Wage Sheet. Check the Laravel log for details.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update Labour Adjustments
    |--------------------------------------------------------------------------
    */

    public function updateAdjustments(
        Request $request,
        WeeklyWageSheet $weeklyWageSheet
    ): RedirectResponse {
        ProjectAccess::authorize(
            (int) $weeklyWageSheet->project_id
        );

        if (! in_array(
            $weeklyWageSheet->status,
            ['draft', 'calculated', 'rejected'],
            true
        )) {
            return back()->with(
                'error',
                'Labour adjustments can be changed only before submission.'
            );
        }

        $validated = $request->validate([
            'details' => [
                'required',
                'array',
            ],

            'details.*.id' => [
                'required',
                'integer',
                'exists:weekly_wage_sheet_details,id',
            ],

            'details.*.additions' => [
                'required',
                'numeric',
                'min:0',
                'max:99999999.99',
            ],

            'details.*.deductions' => [
                'required',
                'numeric',
                'min:0',
                'max:99999999.99',
            ],

            'details.*.adjustment_reason' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'details.*.remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        try {
            DB::transaction(
                function () use (
                    $validated,
                    $weeklyWageSheet
                ): void {
                    foreach (
                        $validated['details']
                        as $rowIndex => $row
                    ) {
                        $detail = WeeklyWageSheetDetail::query()
                            ->whereKey((int) $row['id'])
                            ->where(
                                'weekly_wage_sheet_id',
                                $weeklyWageSheet->id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();

                        $additions =
                            (float) $row['additions'];

                        $deductions =
                            (float) $row['deductions'];

                        if (
                            ($additions > 0 || $deductions > 0)
                            && blank(
                                $row['adjustment_reason'] ?? null
                            )
                        ) {
                            throw ValidationException::withMessages([
                                "details.{$rowIndex}.adjustment_reason" => [
                                    'Adjustment reason is required when additions or deductions are entered.',
                                ],
                            ]);
                        }

                        $detail->additions = $additions;
                        $detail->deductions = $deductions;

                        $detail->adjustment_reason =
                            $this->nullableTrim(
                                $row['adjustment_reason'] ?? null
                            );

                        $detail->remarks =
                            $this->nullableTrim(
                                $row['remarks'] ?? null
                            );

                        $detail->recalculateValues();
                        $detail->save();
                    }

                    $this->calculationService
                        ->recalculateSheetTotals(
                            $weeklyWageSheet
                        );
                }
            );

            return back()->with(
                'success',
                'Labour additions and deductions updated successfully.'
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'Unable to update labour adjustments.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Site Charges
    |--------------------------------------------------------------------------
    */

    public function storeCharge(
        Request $request,
        WeeklyWageSheet $weeklyWageSheet
    ): RedirectResponse {
        ProjectAccess::authorize(
            (int) $weeklyWageSheet->project_id
        );

        if (! in_array(
            $weeklyWageSheet->status,
            ['draft', 'calculated', 'rejected'],
            true
        )) {
            return back()->with(
                'error',
                'Site charges can be changed only before submission.'
            );
        }

        $validated = $request->validate([
            'charge_type' => [
                'required',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:99999999.99',
            ],

            'activity_id' => [
                'nullable',
                'integer',
                'exists:activities,id',
            ],

            'contractor_id' => [
                'nullable',
                'integer',
                'exists:contractors,id',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        try {
            DB::transaction(
                function () use (
                    $validated,
                    $weeklyWageSheet
                ): void {
                    $nextSortOrder = (int) $weeklyWageSheet
                        ->charges()
                        ->max('sort_order') + 1;

                    WeeklyWageSheetCharge::create([
                        'weekly_wage_sheet_id' =>
                            $weeklyWageSheet->id,

                        'charge_type' =>
                            trim($validated['charge_type']),

                        'description' =>
                            $this->nullableTrim(
                                $validated['description'] ?? null
                            ),

                        'amount' =>
                            (float) $validated['amount'],

                        'activity_id' =>
                            $validated['activity_id'] ?? null,

                        'contractor_id' =>
                            $validated['contractor_id'] ?? null,

                        'remarks' =>
                            $this->nullableTrim(
                                $validated['remarks'] ?? null
                            ),

                        'sort_order' =>
                            $nextSortOrder,

                        'is_active' => true,
                    ]);

                    $this->calculationService
                        ->recalculateSheetTotals(
                            $weeklyWageSheet
                        );
                }
            );

            return back()->with(
                'success',
                'Site charge added successfully.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to add the site charge.'
                );
        }
    }

    public function destroyCharge(
        WeeklyWageSheet $weeklyWageSheet,
        WeeklyWageSheetCharge $charge
    ): RedirectResponse {
        ProjectAccess::authorize(
            (int) $weeklyWageSheet->project_id
        );

        if (
            (int) $charge->weekly_wage_sheet_id
            !== (int) $weeklyWageSheet->id
        ) {
            abort(404);
        }

        if (! in_array(
            $weeklyWageSheet->status,
            ['draft', 'calculated', 'rejected'],
            true
        )) {
            return back()->with(
                'error',
                'Site charges can be removed only before submission.'
            );
        }

        try {
            DB::transaction(
                function () use (
                    $weeklyWageSheet,
                    $charge
                ): void {
                    $charge->delete();

                    $this->calculationService
                        ->recalculateSheetTotals(
                            $weeklyWageSheet
                        );
                }
            );

            return back()->with(
                'success',
                'Site charge removed successfully.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'Unable to remove the site charge.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submit(
        WeeklyWageSheet $weeklyWageSheet
    ): RedirectResponse {
        ProjectAccess::authorize(
            (int) $weeklyWageSheet->project_id
        );

        if (! in_array(
            $weeklyWageSheet->status,
            ['calculated', 'rejected'],
            true
        )) {
            return back()->with(
                'error',
                'Only a Calculated or Rejected wage sheet can be submitted.'
            );
        }

        if (
            ! $weeklyWageSheet
                ->details()
                ->where('is_active', true)
                ->exists()
        ) {
            return back()->with(
                'error',
                'Calculate the wage sheet before submitting it.'
            );
        }

        $oldValues = $weeklyWageSheet->toArray();

        $weeklyWageSheet->update([
            'status' => 'submitted',
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        AuditHelper::log(
            'Weekly Wage Sheets',
            'Submitted',
            WeeklyWageSheet::class,
            $weeklyWageSheet->id,
            "Weekly Wage Sheet '{$weeklyWageSheet->wage_sheet_number}' was submitted for approval.",
            $oldValues,
            $weeklyWageSheet->fresh()->toArray()
        );

        return back()->with(
            'success',
            'Weekly Wage Sheet submitted for approval.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        WeeklyWageSheet $weeklyWageSheet
    ): RedirectResponse {
        ProjectAccess::authorize(
            (int) $weeklyWageSheet->project_id
        );

        if (
            $weeklyWageSheet->status
            !== 'submitted'
        ) {
            return back()->with(
                'error',
                'Only a Submitted wage sheet can be approved.'
            );
        }

        $oldValues = $weeklyWageSheet->toArray();

        $weeklyWageSheet->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        AuditHelper::log(
            'Weekly Wage Sheets',
            'Approved',
            WeeklyWageSheet::class,
            $weeklyWageSheet->id,
            "Weekly Wage Sheet '{$weeklyWageSheet->wage_sheet_number}' was approved.",
            $oldValues,
            $weeklyWageSheet->fresh()->toArray()
        );

        return back()->with(
            'success',
            'Weekly Wage Sheet approved successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        Request $request,
        WeeklyWageSheet $weeklyWageSheet
    ): RedirectResponse {
        ProjectAccess::authorize(
            (int) $weeklyWageSheet->project_id
        );

        if (
            $weeklyWageSheet->status
            !== 'submitted'
        ) {
            return back()->with(
                'error',
                'Only a Submitted wage sheet can be rejected.'
            );
        }

        $validated = $request->validate([
            'rejection_reason' => [
                'required',
                'string',
                'min:3',
                'max:2000',
            ],
        ]);

        $oldValues = $weeklyWageSheet->toArray();

        $weeklyWageSheet->update([
            'status' => 'rejected',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => trim(
                $validated['rejection_reason']
            ),
            'approved_by' => null,
            'approved_at' => null,
        ]);

        AuditHelper::log(
            'Weekly Wage Sheets',
            'Rejected',
            WeeklyWageSheet::class,
            $weeklyWageSheet->id,
            "Weekly Wage Sheet '{$weeklyWageSheet->wage_sheet_number}' was rejected.",
            $oldValues,
            $weeklyWageSheet->fresh()->toArray()
        );

        return back()->with(
            'success',
            'Weekly Wage Sheet rejected successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Mark Paid
    |--------------------------------------------------------------------------
    */

    public function markPaid(
        Request $request,
        WeeklyWageSheet $weeklyWageSheet
    ): RedirectResponse {
        ProjectAccess::authorize(
            (int) $weeklyWageSheet->project_id
        );

        if (
            $weeklyWageSheet->status
            !== 'approved'
        ) {
            return back()->with(
                'error',
                'Only an Approved wage sheet can be marked as Paid.'
            );
        }

        $validated = $request->validate([
            'payment_date' => [
                'required',
                'date',
            ],

            'payment_method' => [
                'required',
                Rule::in([
                    'cash',
                    'bank_transfer',
                    'upi',
                    'cheque',
                    'other',
                ]),
            ],

            'payment_reference' => [
                'nullable',
                'string',
                'max:150',
            ],
        ]);

        $oldValues = $weeklyWageSheet->toArray();

        $weeklyWageSheet->update([
            'status' => 'paid',
            'paid_by' => auth()->id(),
            'payment_date' =>
                $validated['payment_date'],
            'payment_method' =>
                $validated['payment_method'],
            'payment_reference' =>
                $this->nullableTrim(
                    $validated['payment_reference'] ?? null
                ),
            'paid_at' => now(),
        ]);

        AuditHelper::log(
            'Weekly Wage Sheets',
            'Paid',
            WeeklyWageSheet::class,
            $weeklyWageSheet->id,
            "Weekly Wage Sheet '{$weeklyWageSheet->wage_sheet_number}' was marked as Paid.",
            $oldValues,
            $weeklyWageSheet->fresh()->toArray()
        );

        return back()->with(
            'success',
            'Weekly Wage Sheet marked as Paid successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Excel Export
    |--------------------------------------------------------------------------
    */

    public function exportExcel(
        WeeklyWageSheet $weeklyWageSheet
    ): BinaryFileResponse {
        ProjectAccess::authorize(
            (int) $weeklyWageSheet->project_id
        );

        $weeklyWageSheet->load(
            $this->relationships()
        );

        $fileName =
            $this->exportFileName(
                weeklyWageSheet:
                    $weeklyWageSheet,

                extension:
                    'xlsx'
            );

        return Excel::download(
            new WeeklyWageSheetExport(
                $weeklyWageSheet
            ),
            $fileName
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PDF Export
    |--------------------------------------------------------------------------
    */

    public function exportPdf(
        WeeklyWageSheet $weeklyWageSheet
    ) {
        ProjectAccess::authorize(
            (int) $weeklyWageSheet->project_id
        );

        $weeklyWageSheet->load(
            $this->relationships()
        );

        $fileName =
            $this->exportFileName(
                weeklyWageSheet:
                    $weeklyWageSheet,

                extension:
                    'pdf'
            );

        $pdf = Pdf::loadView(
            'weekly-wage-sheets.pdf',
            [
                'weeklyWageSheet' =>
                    $weeklyWageSheet,
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

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function validateCreateRequest(
        Request $request
    ): array {
        $validated = $request->validate([
            'project_id' => [
                'required',
                'integer',
                Rule::in(
                    ProjectAccess::allowedProjectIds()
                ),
            ],

            'week_start_date' => [
                'required',
                'date',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $selectedDate = Carbon::parse(
            $validated['week_start_date']
        );

        if (! $selectedDate->isSunday()) {
            throw ValidationException::withMessages([
                'week_start_date' => [
                    'Week Start Date must be a Sunday.',
                ],
            ]);
        }

        return $validated;
    }

    private function generateWageSheetNumber(): string
    {
        $prefix = 'WWS-' . now()->format('Ymd') . '-';

        $latestNumber = WeeklyWageSheet::query()
            ->withTrashed()
            ->where(
                'wage_sheet_number',
                'like',
                "{$prefix}%"
            )
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('wage_sheet_number');

        $nextSequence = 1;

        if ($latestNumber) {
            $nextSequence =
                (int) Str::afterLast(
                    $latestNumber,
                    '-'
                ) + 1;
        }

        do {
            $number = $prefix . str_pad(
                (string) $nextSequence,
                4,
                '0',
                STR_PAD_LEFT
            );

            $exists = WeeklyWageSheet::withTrashed()
                ->where(
                    'wage_sheet_number',
                    $number
                )
                ->exists();

            $nextSequence++;
        } while ($exists);

        return $number;
    }

    private function relationships(): array
    {
        return [
            'project:id,project_name,project_code',

            'details' => function ($query): void {
                $query
                    ->where('is_active', true)
                    ->orderBy('id');
            },

            'details.labour:id,labour_code,full_name',
            'details.designationRole:id,name',
            'details.labourCategory:id,category_name',
            'details.contractor:id,contractor_name',

            'charges' => function ($query): void {
                $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id');
            },

            'charges.activity',
            'charges.contractor:id,contractor_name',

            'generatedBy:id,name',
            'submittedBy:id,name',
            'approvedBy:id,name',
            'rejectedBy:id,name',
            'paidBy:id,name',
        ];
    }


    private function exportFileName(
        WeeklyWageSheet $weeklyWageSheet,
        string $extension
    ): string {
        $projectCode = $weeklyWageSheet
            ->project
            ?->project_code
            ?: 'project';

        $projectCode = Str::slug(
            $projectCode
        );

        $weekStart = $weeklyWageSheet
            ->week_start_date
            ?->format('Y-m-d')
            ?? 'week';

        return sprintf(
            'weekly-wage-sheet-%s-%s.%s',
            $projectCode,
            $weekStart,
            $extension
        );
    }

    private function nullableTrim(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === ''
            ? null
            : $value;
    }
}
