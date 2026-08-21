<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\WeeklyLabourPaymentDetail;
use App\Models\WeeklyLabourPaymentRegister;
use App\Services\WeeklyLabourPaymentCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class WeeklyLabourPaymentController extends Controller
{
    public function __construct(
        private readonly WeeklyLabourPaymentCalculationService $calculationService
    ) {
    }

    public function index(Request $request): View
    {
        $query = WeeklyLabourPaymentRegister::query()
            ->with([
                'generatedBy:id,name',
                'submittedBy:id,name',
                'approvedBy:id,name',
                'paidBy:id,name',
            ]);

        if ($request->filled('search')) {
            $search = trim(
                $request->string('search')->toString()
            );

            $query->where(
                'register_number',
                'like',
                "%{$search}%"
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

        $registers = $query
            ->orderByDesc('week_start_date')
            ->orderByDesc('id')
            ->paginate(
                config('rds.pagination.per_page', 15)
            )
            ->withQueryString();

        return view(
            'weekly-labour-payments.index',
            [
                'registers' => $registers,
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

    public function create(): View
    {
        return view(
            'weekly-labour-payments.create',
            [
                'defaultWeekStart' => now()
                    ->startOfWeek(Carbon::SUNDAY)
                    ->toDateString(),
            ]
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCreateRequest($request);

        $weekStart = Carbon::parse(
            $validated['week_start_date']
        )
            ->startOfWeek(Carbon::SUNDAY)
            ->startOfDay();

        $weekEnd = $weekStart
            ->copy()
            ->addDays(6)
            ->endOfDay();

        $duplicateExists = WeeklyLabourPaymentRegister::query()
            ->whereDate(
                'week_start_date',
                $weekStart->toDateString()
            )
            ->where('is_active', true)
            ->exists();

        if ($duplicateExists) {
            throw ValidationException::withMessages([
                'week_start_date' => [
                    'An active Weekly Labour Payment Register already exists for this week.',
                ],
            ]);
        }

        try {
            $register = DB::transaction(
                function () use (
                    $validated,
                    $weekStart,
                    $weekEnd
                ): WeeklyLabourPaymentRegister {
                    $register = WeeklyLabourPaymentRegister::create([
                        'register_number' =>
                            $this->generateRegisterNumber(),

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
                        'Weekly Labour Payments',
                        'Created',
                        WeeklyLabourPaymentRegister::class,
                        $register->id,
                        "Weekly Labour Payment Register '{$register->register_number}' was created as Draft.",
                        null,
                        $register->fresh()->toArray()
                    );

                    return $register;
                }
            );

            return redirect()
                ->route(
                    'weekly-labour-payments.show',
                    $register
                )
                ->with(
                    'success',
                    'Weekly Labour Payment Register created successfully as Draft.'
                );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to create the Weekly Labour Payment Register.'
                );
        }
    }

    public function show(
        WeeklyLabourPaymentRegister $weeklyLabourPayment
    ): View {
        $weeklyLabourPayment->load(
            $this->relationships()
        );

        return view(
            'weekly-labour-payments.show',
            [
                'register' => $weeklyLabourPayment,
            ]
        );
    }

    public function generate(
        WeeklyLabourPaymentRegister $weeklyLabourPayment
    ): RedirectResponse {
        $oldValues = $weeklyLabourPayment->toArray();

        try {
            $calculated = $this
                ->calculationService
                ->generate(
                    $weeklyLabourPayment
                );

            AuditHelper::log(
                'Weekly Labour Payments',
                'Calculated',
                WeeklyLabourPaymentRegister::class,
                $calculated->id,
                "Weekly Labour Payment Register '{$calculated->register_number}' was calculated from approved attendance across all projects.",
                $oldValues,
                $calculated->toArray()
            );

            return redirect()
                ->route(
                    'weekly-labour-payments.show',
                    $calculated
                )
                ->with(
                    'success',
                    'Weekly labour payments calculated successfully across all approved project attendance.'
                );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'Unable to calculate the Weekly Labour Payment Register. Check the Laravel log for details.'
            );
        }
    }

    public function updateAdjustments(
        Request $request,
        WeeklyLabourPaymentRegister $weeklyLabourPayment
    ): RedirectResponse {
        if (! in_array(
            $weeklyLabourPayment->status,
            ['draft', 'calculated', 'rejected'],
            true
        )) {
            return back()->with(
                'error',
                'Labour payment adjustments can be changed only before submission.'
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
                'exists:weekly_labour_payment_details,id',
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
                    $weeklyLabourPayment
                ): void {
                    foreach (
                        $validated['details']
                        as $rowIndex => $row
                    ) {
                        $detail = WeeklyLabourPaymentDetail::query()
                            ->whereKey((int) $row['id'])
                            ->where(
                                'weekly_labour_payment_register_id',
                                $weeklyLabourPayment->id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();

                        $additions = (float) $row['additions'];
                        $deductions = (float) $row['deductions'];

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

                    $this
                        ->calculationService
                        ->recalculateRegisterTotals(
                            $weeklyLabourPayment
                        );
                }
            );

            return back()->with(
                'success',
                'Labour payment additions and deductions updated successfully.'
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'Unable to update labour payment adjustments.'
            );
        }
    }

    public function submit(
        WeeklyLabourPaymentRegister $weeklyLabourPayment
    ): RedirectResponse {
        if (! in_array(
            $weeklyLabourPayment->status,
            ['calculated', 'rejected'],
            true
        )) {
            return back()->with(
                'error',
                'Only a Calculated or Rejected Labour Payment Register can be submitted.'
            );
        }

        if (
            ! $weeklyLabourPayment
                ->details()
                ->where('is_active', true)
                ->exists()
        ) {
            return back()->with(
                'error',
                'Calculate the Labour Payment Register before submitting it.'
            );
        }

        $oldValues = $weeklyLabourPayment->toArray();

        $weeklyLabourPayment->update([
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
            'Weekly Labour Payments',
            'Submitted',
            WeeklyLabourPaymentRegister::class,
            $weeklyLabourPayment->id,
            "Weekly Labour Payment Register '{$weeklyLabourPayment->register_number}' was submitted for approval.",
            $oldValues,
            $weeklyLabourPayment->fresh()->toArray()
        );

        return back()->with(
            'success',
            'Weekly Labour Payment Register submitted for approval.'
        );
    }

    public function approve(
        WeeklyLabourPaymentRegister $weeklyLabourPayment
    ): RedirectResponse {
        if ($weeklyLabourPayment->status !== 'submitted') {
            return back()->with(
                'error',
                'Only a Submitted Labour Payment Register can be approved.'
            );
        }

        $oldValues = $weeklyLabourPayment->toArray();

        $weeklyLabourPayment->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        AuditHelper::log(
            'Weekly Labour Payments',
            'Approved',
            WeeklyLabourPaymentRegister::class,
            $weeklyLabourPayment->id,
            "Weekly Labour Payment Register '{$weeklyLabourPayment->register_number}' was approved.",
            $oldValues,
            $weeklyLabourPayment->fresh()->toArray()
        );

        return back()->with(
            'success',
            'Weekly Labour Payment Register approved successfully.'
        );
    }

    public function reject(
        Request $request,
        WeeklyLabourPaymentRegister $weeklyLabourPayment
    ): RedirectResponse {
        if ($weeklyLabourPayment->status !== 'submitted') {
            return back()->with(
                'error',
                'Only a Submitted Labour Payment Register can be rejected.'
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

        $oldValues = $weeklyLabourPayment->toArray();

        $weeklyLabourPayment->update([
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
            'Weekly Labour Payments',
            'Rejected',
            WeeklyLabourPaymentRegister::class,
            $weeklyLabourPayment->id,
            "Weekly Labour Payment Register '{$weeklyLabourPayment->register_number}' was rejected.",
            $oldValues,
            $weeklyLabourPayment->fresh()->toArray()
        );

        return back()->with(
            'success',
            'Weekly Labour Payment Register rejected successfully.'
        );
    }

    public function markPaid(
        Request $request,
        WeeklyLabourPaymentRegister $weeklyLabourPayment
    ): RedirectResponse {
        if ($weeklyLabourPayment->status !== 'approved') {
            return back()->with(
                'error',
                'Only an Approved Labour Payment Register can be marked as Paid.'
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

        $oldValues = $weeklyLabourPayment->toArray();

        $weeklyLabourPayment->update([
            'status' => 'paid',
            'paid_by' => auth()->id(),
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'payment_reference' =>
                $this->nullableTrim(
                    $validated['payment_reference'] ?? null
                ),
            'paid_at' => now(),
        ]);

        AuditHelper::log(
            'Weekly Labour Payments',
            'Paid',
            WeeklyLabourPaymentRegister::class,
            $weeklyLabourPayment->id,
            "Weekly Labour Payment Register '{$weeklyLabourPayment->register_number}' was marked as Paid.",
            $oldValues,
            $weeklyLabourPayment->fresh()->toArray()
        );

        return back()->with(
            'success',
            'Weekly Labour Payment Register marked as Paid successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PDF Export
    |--------------------------------------------------------------------------
    */

    public function exportPdf(
        WeeklyLabourPaymentRegister $weeklyLabourPayment
    ) {
        $weeklyLabourPayment->load(
            $this->relationships()
        );

        if ($weeklyLabourPayment->details->isEmpty()) {
            return back()->with(
                'error',
                'Calculate the Weekly Labour Payment Register before downloading the PDF.'
            );
        }

        $fileName = sprintf(
            '%s_%s_to_%s.pdf',
            $weeklyLabourPayment->register_number,
            $weeklyLabourPayment->week_start_date?->format('Y-m-d'),
            $weeklyLabourPayment->week_end_date?->format('Y-m-d')
        );

        $pdf = Pdf::loadView(
            'weekly-labour-payments.pdf',
            [
                'register' => $weeklyLabourPayment,
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

    private function validateCreateRequest(
        Request $request
    ): array {
        $validated = $request->validate([
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

    private function generateRegisterNumber(): string
    {
        $prefix = 'WLPR-' . now()->format('Ymd') . '-';

        $latestNumber = WeeklyLabourPaymentRegister::query()
            ->withTrashed()
            ->where(
                'register_number',
                'like',
                "{$prefix}%"
            )
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('register_number');

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

            $exists = WeeklyLabourPaymentRegister::withTrashed()
                ->where(
                    'register_number',
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
            'details' => function ($query): void {
                $query
                    ->where('is_active', true)
                    ->orderBy('labour_group_id')
                    ->orderBy('labour_id');
            },

            'details.labour:id,labour_code,full_name,labour_group_id',
            'details.labour.labourGroup:id,code,name,sort_order',
            'details.labourGroup:id,code,name,sort_order',
            'details.designationRole:id,name',
            'details.labourCategory:id,category_name',
            'details.contractor:id,contractor_name',

            'details.allocations' => function ($query): void {
                $query
                    ->where('is_active', true)
                    ->orderBy('project_name');
            },

            'details.allocations.project:id,project_name,project_code',

            'generatedBy:id,name',
            'submittedBy:id,name',
            'approvedBy:id,name',
            'rejectedBy:id,name',
            'paidBy:id,name',
        ];
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
