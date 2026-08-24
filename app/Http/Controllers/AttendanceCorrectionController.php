<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceCorrectionDetail;
use App\Models\AttendanceStatus;
use App\Models\Labour;
use App\Models\LabourAttendance;
use App\Models\LabourAttendanceDetail;
use App\Models\WorkingStatus;
use App\Support\ProjectAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class AttendanceCorrectionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $tableQuery = $this->buildFilteredQuery(
            request: $request,
            includeStatusFilter: true
        );

        $attendanceCorrections = $tableQuery
            ->with([
                'project:id,project_name,project_code',

                'labourAttendance' => function ($query): void {
                    $query->select([
                        'id',
                        'attendance_number',
                        'project_id',
                        'attendance_date',
                        'shift_id',
                        'status',
                    ]);
                },

                'labourAttendance.shift:id,name',

                'createdBy:id,name,email',
                'submittedBy:id,name,email',
                'approvedBy:id,name,email',
                'rejectedBy:id,name,email',
                'appliedBy:id,name,email',
            ])
            ->withCount([
                'details as details_count' => function ($query): void {
                    $query->where('is_active', true);
                },
            ])
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->paginate(
                config('rds.pagination.per_page', 15)
            )
            ->withQueryString();

        $statisticsQuery = $this->buildFilteredQuery(
            request: $request,
            includeStatusFilter: false
        );

        $statisticsResult = $statisticsQuery
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as draft_count',
                [AttendanceCorrection::STATUS_DRAFT]
            )
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as submitted_count',
                [AttendanceCorrection::STATUS_SUBMITTED]
            )
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as approved_count',
                [AttendanceCorrection::STATUS_APPROVED]
            )
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as applied_count',
                [AttendanceCorrection::STATUS_APPLIED]
            )
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rejected_count',
                [AttendanceCorrection::STATUS_REJECTED]
            )
            ->first();

        $statistics = [
            'total' => (int) ($statisticsResult?->total ?? 0),
            'draft' => (int) ($statisticsResult?->draft_count ?? 0),
            'submitted' => (int) ($statisticsResult?->submitted_count ?? 0),
            'approved' => (int) ($statisticsResult?->approved_count ?? 0),
            'applied' => (int) ($statisticsResult?->applied_count ?? 0),
            'rejected' => (int) ($statisticsResult?->rejected_count ?? 0),
        ];

        return view(
            'attendance-corrections.index',
            [
                'attendanceCorrections' => $attendanceCorrections,
                'projects' => ProjectAccess::availableProjects(),
                'statuses' => AttendanceCorrection::statuses(),
                'statistics' => $statistics,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(Request $request): View
    {
        $selectedAttendanceId = $request->filled(
            'labour_attendance_id'
        )
            ? $request->integer('labour_attendance_id')
            : null;

        $selectedAttendance = null;
        $availableLabours = collect();

        if ($selectedAttendanceId) {
            $selectedAttendance = $this
                ->approvedAttendanceQuery()
                ->whereKey($selectedAttendanceId)
                ->firstOrFail();

            ProjectAccess::authorize(
                (int) $selectedAttendance->project_id
            );

            $selectedAttendance->load([
                'project',
                'shift',
                'recordedBy',

                'details' => function ($query): void {
                    $query
                        ->where('is_active', true)
                        ->with([
                            'labour.designationRole',
                            'labour.labourGroup',
                            'attendanceStatus',
                            'workingStatus',
                        ])
                        ->orderBy('id');
                },
            ]);

            $existingLabourIds = $selectedAttendance
                ->details
                ->pluck('labour_id')
                ->map(
                    fn ($id): int => (int) $id
                )
                ->all();

            /*
             * Cross-project correction rule:
             * A labour may be added to this project when their attendance
             * elsewhere on the same date is non-working/non-payable
             * (Absent, Leave, Weekly Off, Holiday, etc.).
             *
             * Only another active attendance detail whose Attendance Status
             * has a positive payable factor is treated as a conflict.
             */
            $conflictingLabourIds = $selectedAttendance->isAdditionalWork()
                ? []
                : LabourAttendanceDetail::query()
                ->join(
                    'labour_attendances',
                    'labour_attendances.id',
                    '=',
                    'labour_attendance_details.labour_attendance_id'
                )
                ->join(
                    'attendance_statuses',
                    'attendance_statuses.id',
                    '=',
                    'labour_attendance_details.attendance_status_id'
                )
                ->whereDate(
                    'labour_attendances.attendance_date',
                    $selectedAttendance->attendance_date
                )
                ->where(
                    'labour_attendances.id',
                    '!=',
                    $selectedAttendance->id
                )
                ->where(
                    'labour_attendances.project_id',
                    '!=',
                    $selectedAttendance->project_id
                )
                ->where(
                    'labour_attendances.is_active',
                    true
                )
                ->whereNull('labour_attendances.deleted_at')
                ->where(
                    'labour_attendance_details.is_active',
                    true
                )
                ->whereNull('labour_attendance_details.deleted_at')
                ->where(
                    'attendance_statuses.is_active',
                    true
                )
                ->where(
                    'attendance_statuses.payable_factor',
                    '>',
                    0
                )
                ->pluck('labour_attendance_details.labour_id')
                ->map(fn ($id): int => (int) $id)
                ->unique()
                ->values()
                ->all();

            $availableLabours = Labour::query()
                ->active()
                ->when(
                    ! empty($existingLabourIds),
                    fn (Builder $query): Builder =>
                        $query->whereNotIn('id', $existingLabourIds)
                )
                ->when(
                    ! empty($conflictingLabourIds),
                    fn (Builder $query): Builder =>
                        $query->whereNotIn('id', $conflictingLabourIds)
                )
                ->with([
                    'designationRole',
                    'labourGroup',
                    'currentProject:id,project_name,project_code',
                ])
                ->orderByRaw(
                    '
                        CASE
                            WHEN current_project_id = ? THEN 1
                            WHEN current_project_id IS NULL THEN 2
                            ELSE 3
                        END
                    ',
                    [$selectedAttendance->project_id]
                )
                ->orderBy('labour_group_id')
                ->orderBy('full_name')
                ->get();
        }

        $approvedAttendances = $this
            ->approvedAttendanceQuery()
            ->with([
                'project:id,project_name,project_code',
                'shift:id,name',
            ])
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->get();

        return view(
            'attendance-corrections.create',
            [
                'approvedAttendances' => $approvedAttendances,
                'selectedAttendance' => $selectedAttendance,
                'availableLabours' => $availableLabours,

                'attendanceStatuses' =>
                    AttendanceStatus::query()
                        ->active()
                        ->ordered()
                        ->get(),

                'workingStatuses' =>
                    WorkingStatus::query()
                        ->active()
                        ->ordered()
                        ->get(),

                'actionTypes' =>
                    AttendanceCorrectionDetail::actionTypes(),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    /**
     * Store a correction request as Draft.
     *
     * @throws Throwable
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $this->validateStoreRequest(
            $request
        );

        $attendance = $this
            ->approvedAttendanceQuery()
            ->whereKey(
                (int) $validated['labour_attendance_id']
            )
            ->firstOrFail();

        ProjectAccess::authorize(
            (int) $attendance->project_id
        );

        /*
         * Primary target-date validation.
         *
         * Stop an impossible correction before the Draft is created.
         * The same validation is repeated in apply() as a race-condition
         * safeguard in case another attendance is created after this point.
         */
        $this->validateTargetDateAvailability(
            attendance: $attendance,
            targetDate: $validated['new_attendance_date']
        );

        try {
            $correction = DB::transaction(
                function () use (
                    $validated,
                    $attendance
                ): AttendanceCorrection {
                    $attendance->refresh();

                    if (
                        $attendance->status !== 'approved'
                        || ! $attendance->is_active
                    ) {
                        throw ValidationException::withMessages([
                            'labour_attendance_id' => [
                                'Only an active Approved attendance sheet can be corrected.',
                            ],
                        ]);
                    }

                    $correction = AttendanceCorrection::create([
                        'correction_number' =>
                            $this->generateCorrectionNumber(),

                        'labour_attendance_id' =>
                            $attendance->id,

                        'project_id' =>
                            $attendance->project_id,

                        'attendance_date' =>
                            $attendance->attendance_date,

                        'old_attendance_date' =>
                            $attendance->attendance_date,

                        'new_attendance_date' =>
                            $validated['new_attendance_date'] ?? $attendance->attendance_date,

                        'correction_reason' =>
                            trim(
                                $validated[
                                    'correction_reason'
                                ]
                            ),

                        'status' =>
                            AttendanceCorrection::STATUS_DRAFT,

                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);

                    $this->persistCorrectionDetails(
                        correction: $correction,
                        attendance: $attendance,
                        submittedDetails: $validated['details'] ?? []
                    );

                    $dateChanged = $correction->new_attendance_date
                        && $correction->old_attendance_date
                        && ! $correction->new_attendance_date->isSameDay(
                            $correction->old_attendance_date
                        );

                    $hasDetailChanges = $correction
                        ->details()
                        ->where('is_active', true)
                        ->exists();

                    if (! $dateChanged && ! $hasDetailChanges) {
                        throw ValidationException::withMessages([
                            'details' => [
                                'Change the attendance date or make at least one valid labour attendance correction.',
                            ],
                        ]);
                    }

                    $correction->load(
                        $this->correctionRelationships()
                    );

                    AuditHelper::log(
                        'Attendance Corrections',
                        'Created',
                        AttendanceCorrection::class,
                        $correction->id,
                        "Attendance Correction '{$correction->correction_number}' was created as Draft.",
                        null,
                        $this->auditValues($correction)
                    );

                    return $correction;
                }
            );

            return redirect()
                ->route(
                    'attendance-corrections.show',
                    $correction
                )
                ->with(
                    'success',
                    'Attendance Correction created successfully as Draft.'
                );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to create the Attendance Correction. Please review the entered information and try again.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        AttendanceCorrection $attendanceCorrection
    ): View {
        ProjectAccess::authorize(
            (int) $attendanceCorrection->project_id
        );

        $attendanceCorrection->load(
            $this->correctionRelationships()
        );

        return view(
            'attendance-corrections.show',
            compact('attendanceCorrection')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Submit for Approval
    |--------------------------------------------------------------------------
    */

    public function submit(
        AttendanceCorrection $attendanceCorrection
    ): RedirectResponse {
        ProjectAccess::authorize(
            (int) $attendanceCorrection->project_id
        );

        if (
            $attendanceCorrection->status
            !== AttendanceCorrection::STATUS_DRAFT
        ) {
            return redirect()
                ->route(
                    'attendance-corrections.show',
                    $attendanceCorrection
                )
                ->with(
                    'error',
                    'Only a Draft attendance correction can be submitted for approval.'
                );
        }

        $dateChanged = $attendanceCorrection->new_attendance_date
            && $attendanceCorrection->old_attendance_date
            && ! $attendanceCorrection->new_attendance_date->isSameDay(
                $attendanceCorrection->old_attendance_date
            );

        $hasDetailChanges = $attendanceCorrection
            ->details()
            ->where('is_active', true)
            ->exists();

        if (! $dateChanged && ! $hasDetailChanges) {
            return redirect()
                ->route(
                    'attendance-corrections.show',
                    $attendanceCorrection
                )
                ->with(
                    'error',
                    'This attendance correction has no date or labour changes to submit.'
                );
        }

        try {
            DB::transaction(
                function () use (
                    $attendanceCorrection
                ): void {
                    $attendanceCorrection->refresh();

                    if (
                        $attendanceCorrection->status
                        !== AttendanceCorrection::STATUS_DRAFT
                    ) {
                        throw ValidationException::withMessages([
                            'status' => [
                                'The attendance correction is no longer in Draft status.',
                            ],
                        ]);
                    }

                    $oldValues = [
                        'status' =>
                            $attendanceCorrection->status,

                        'submitted_by' =>
                            $attendanceCorrection->submitted_by,

                        'submitted_at' =>
                            $attendanceCorrection->submitted_at,
                    ];

                    $attendanceCorrection->update([
                        'status' =>
                            AttendanceCorrection::STATUS_SUBMITTED,

                        'submitted_by' => auth()->id(),
                        'submitted_at' => now(),
                        'updated_by' => auth()->id(),
                    ]);

                    $attendanceCorrection->load(
                        $this->correctionRelationships()
                    );

                    AuditHelper::log(
                        'Attendance Corrections',
                        'Submitted',
                        AttendanceCorrection::class,
                        $attendanceCorrection->id,
                        "Attendance Correction '{$attendanceCorrection->correction_number}' was submitted for approval.",
                        $oldValues,
                        $this->auditValues(
                            $attendanceCorrection
                        )
                    );
                }
            );

            return redirect()
                ->route(
                    'attendance-corrections.show',
                    $attendanceCorrection
                )
                ->with(
                    'success',
                    'Attendance Correction submitted for approval successfully.'
                );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route(
                    'attendance-corrections.show',
                    $attendanceCorrection
                )
                ->with(
                    'error',
                    'Unable to submit the Attendance Correction for approval. Please try again.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        AttendanceCorrection $attendanceCorrection
    ): RedirectResponse {
        ProjectAccess::authorize(
            (int) $attendanceCorrection->project_id
        );

        if (
            $attendanceCorrection->status
            !== AttendanceCorrection::STATUS_SUBMITTED
        ) {
            return redirect()
                ->route('attendance-corrections.index')
                ->with(
                    'error',
                    'Only a Submitted attendance correction can be approved.'
                );
        }

        try {
            DB::transaction(
                function () use ($attendanceCorrection): void {
                    $attendanceCorrection->refresh();

                    if (
                        $attendanceCorrection->status
                        !== AttendanceCorrection::STATUS_SUBMITTED
                    ) {
                        throw ValidationException::withMessages([
                            'status' => [
                                'The correction is no longer in Submitted status.',
                            ],
                        ]);
                    }

                    $oldValues = [
                        'status' => $attendanceCorrection->status,
                        'approved_by' => $attendanceCorrection->approved_by,
                        'approved_at' => $attendanceCorrection->approved_at,
                    ];

                    $attendanceCorrection->update([
                        'status' => AttendanceCorrection::STATUS_APPROVED,
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'rejected_by' => null,
                        'rejected_at' => null,
                        'rejection_reason' => null,
                        'updated_by' => auth()->id(),
                    ]);

                    $attendanceCorrection->load(
                        $this->correctionRelationships()
                    );

                    AuditHelper::log(
                        'Attendance Corrections',
                        'Approved',
                        AttendanceCorrection::class,
                        $attendanceCorrection->id,
                        "Attendance Correction '{$attendanceCorrection->correction_number}' was approved.",
                        $oldValues,
                        $this->auditValues($attendanceCorrection)
                    );
                }
            );

            return redirect()
                ->route('attendance-corrections.index')
                ->with(
                    'success',
                    'Attendance Correction approved successfully. Apply it to update the original attendance sheet.'
                );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('attendance-corrections.index')
                ->with(
                    'error',
                    'Unable to approve the Attendance Correction.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        Request $request,
        AttendanceCorrection $attendanceCorrection
    ): RedirectResponse {
        ProjectAccess::authorize(
            (int) $attendanceCorrection->project_id
        );

        if (
            $attendanceCorrection->status
            !== AttendanceCorrection::STATUS_SUBMITTED
        ) {
            return redirect()
                ->route('attendance-corrections.index')
                ->with(
                    'error',
                    'Only a Submitted attendance correction can be rejected.'
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

        try {
            DB::transaction(
                function () use (
                    $attendanceCorrection,
                    $validated
                ): void {
                    $attendanceCorrection->refresh();

                    if (
                        $attendanceCorrection->status
                        !== AttendanceCorrection::STATUS_SUBMITTED
                    ) {
                        throw ValidationException::withMessages([
                            'status' => [
                                'The correction is no longer in Submitted status.',
                            ],
                        ]);
                    }

                    $oldValues = [
                        'status' => $attendanceCorrection->status,
                        'rejected_by' => $attendanceCorrection->rejected_by,
                        'rejected_at' => $attendanceCorrection->rejected_at,
                        'rejection_reason' => $attendanceCorrection->rejection_reason,
                    ];

                    $attendanceCorrection->update([
                        'status' => AttendanceCorrection::STATUS_REJECTED,
                        'rejected_by' => auth()->id(),
                        'rejected_at' => now(),
                        'rejection_reason' => trim(
                            $validated['rejection_reason']
                        ),
                        'approved_by' => null,
                        'approved_at' => null,
                        'updated_by' => auth()->id(),
                    ]);

                    $attendanceCorrection->load(
                        $this->correctionRelationships()
                    );

                    AuditHelper::log(
                        'Attendance Corrections',
                        'Rejected',
                        AttendanceCorrection::class,
                        $attendanceCorrection->id,
                        "Attendance Correction '{$attendanceCorrection->correction_number}' was rejected.",
                        $oldValues,
                        $this->auditValues($attendanceCorrection)
                    );
                }
            );

            return redirect()
                ->route('attendance-corrections.index')
                ->with(
                    'success',
                    'Attendance Correction rejected successfully.'
                );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('attendance-corrections.index')
                ->with(
                    'error',
                    'Unable to reject the Attendance Correction.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Apply Approved Correction
    |--------------------------------------------------------------------------
    */

    public function apply(
        AttendanceCorrection $attendanceCorrection
    ): RedirectResponse {
        ProjectAccess::authorize(
            (int) $attendanceCorrection->project_id
        );

        if (
            $attendanceCorrection->status
            !== AttendanceCorrection::STATUS_APPROVED
        ) {
            return redirect()
                ->route('attendance-corrections.index')
                ->with(
                    'error',
                    'Only an Approved attendance correction can be applied.'
                );
        }

        try {
            DB::transaction(
                function () use ($attendanceCorrection): void {
                    $correction = AttendanceCorrection::query()
                        ->whereKey($attendanceCorrection->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (
                        $correction->status
                        !== AttendanceCorrection::STATUS_APPROVED
                    ) {
                        throw ValidationException::withMessages([
                            'status' => [
                                'The correction is no longer in Approved status.',
                            ],
                        ]);
                    }

                    $attendance = LabourAttendance::query()
                        ->whereKey($correction->labour_attendance_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (! $attendance->is_active) {
                        throw ValidationException::withMessages([
                            'attendance' => [
                                'The original attendance sheet is inactive.',
                            ],
                        ]);
                    }

                    $correctionDetails = $correction
                        ->details()
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get();

                    $dateChanged = $correction->new_attendance_date
                        && $correction->old_attendance_date
                        && ! $correction->new_attendance_date->isSameDay(
                            $correction->old_attendance_date
                        );

                    if ($correctionDetails->isEmpty() && ! $dateChanged) {
                        throw ValidationException::withMessages([
                            'details' => [
                                'No attendance changes are available to apply.',
                            ],
                        ]);
                    }

                    $targetDate = $correction->new_attendance_date
                        ? $correction->new_attendance_date->format('Y-m-d')
                        : $attendance->attendance_date->format('Y-m-d');

                    if (
                        $targetDate
                        !== $attendance->attendance_date->format('Y-m-d')
                    ) {
                        /*
                         * Final race-condition safeguard.
                         *
                         * Normally this conflict is already blocked when the
                         * correction Draft is saved. We validate again here
                         * because another attendance could have been created
                         * before this correction reached Apply.
                         */
                        $this->validateTargetDateAvailability(
                            attendance: $attendance,
                            targetDate: $targetDate,
                            applying: true
                        );

                        $attendance->update([
                            'attendance_date' => $targetDate,
                            'updated_by' => auth()->id(),
                        ]);
                    }

                    foreach ($correctionDetails as $correctionDetail) {
                        $this->applyCorrectionDetail(
                            attendance: $attendance,
                            correctionDetail: $correctionDetail
                        );
                    }

                    $this->recalculateAttendanceSummary(
                        $attendance
                    );

                    $oldValues = [
                        'status' => $correction->status,
                        'applied_by' => $correction->applied_by,
                        'applied_at' => $correction->applied_at,
                    ];

                    $correction->update([
                        'status' => AttendanceCorrection::STATUS_APPLIED,
                        'applied_by' => auth()->id(),
                        'applied_at' => now(),
                        'updated_by' => auth()->id(),
                    ]);

                    $correction->load(
                        $this->correctionRelationships()
                    );

                    AuditHelper::log(
                        'Attendance Corrections',
                        'Applied',
                        AttendanceCorrection::class,
                        $correction->id,
                        "Attendance Correction '{$correction->correction_number}' was applied to Attendance '{$attendance->attendance_number}'.",
                        $oldValues,
                        $this->auditValues($correction)
                    );
                }
            );

            return redirect()
                ->route('attendance-corrections.index')
                ->with(
                    'success',
                    'Attendance Correction applied successfully. Labour Attendance and DPR totals have been updated.'
                );
        } catch (ValidationException $exception) {
            return redirect()
                ->route(
                    'attendance-corrections.show',
                    $attendanceCorrection
                )
                ->withErrors(
                    $exception->errors()
                )
                ->with(
                    'error',
                    'Attendance Correction could not be applied. Please review the validation message below.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route(
                    'attendance-corrections.show',
                    $attendanceCorrection
                )
                ->with(
                    'error',
                    'Unable to apply the Attendance Correction. Please try again or check the Laravel log for details.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Apply Detail Helpers
    |--------------------------------------------------------------------------
    */

    private function applyCorrectionDetail(
        LabourAttendance $attendance,
        AttendanceCorrectionDetail $correctionDetail
    ): void {
        $actionType = $correctionDetail->action_type;

        if (
            $actionType
            === AttendanceCorrectionDetail::ACTION_ADD
        ) {
            $alreadyExists = LabourAttendanceDetail::query()
                ->where(
                    'labour_attendance_id',
                    $attendance->id
                )
                ->where(
                    'labour_id',
                    $correctionDetail->labour_id
                )
                ->where('is_active', true)
                ->exists();

            if ($alreadyExists) {
                throw ValidationException::withMessages([
                    'details' => [
                        "Labour ID {$correctionDetail->labour_id} already exists in this attendance sheet.",
                    ],
                ]);
            }

            $labour = Labour::query()
                ->whereKey($correctionDetail->labour_id)
                ->where('is_active', true)
                ->firstOrFail();

            LabourAttendanceDetail::create([
                'labour_attendance_id' => $attendance->id,
                'labour_id' => $labour->id,

                'attendance_status_id' =>
                    $correctionDetail->new_attendance_status_id,

                'working_status_id' =>
                    $correctionDetail->new_working_status_id,

                ...LabourAttendanceDetail::snapshotFromLabour(
                    $labour
                ),

                'check_in_time' =>
                    $this->nullableTime(
                        $correctionDetail->new_check_in_time
                    ),

                'check_out_time' =>
                    $this->nullableTime(
                        $correctionDetail->new_check_out_time
                    ),

                'normal_hours' =>
                    (float) (
                        $correctionDetail->new_normal_hours
                        ?? 0
                    ),

                'ot_hours' =>
                    (float) (
                        $correctionDetail->new_ot_hours
                        ?? 0
                    ),

                'ot_amount' =>
                    (float) (
                        $correctionDetail->new_ot_amount
                        ?? 0
                    ),

                'attendance_source' =>
                    'attendance_correction',

                'remarks' =>
                    $this->nullableTrim(
                        $correctionDetail->new_remarks
                    ),

                'is_active' => true,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            return;
        }

        $attendanceDetail = LabourAttendanceDetail::withTrashed()
            ->whereKey(
                $correctionDetail
                    ->labour_attendance_detail_id
            )
            ->where(
                'labour_attendance_id',
                $attendance->id
            )
            ->lockForUpdate()
            ->firstOrFail();

        if (
            $actionType
            === AttendanceCorrectionDetail::ACTION_REMOVE
        ) {
            $attendanceDetail->update([
                'is_active' => false,
                'updated_by' => auth()->id(),
            ]);

            if (! $attendanceDetail->trashed()) {
                $attendanceDetail->delete();
            }

            return;
        }

        if (
            $actionType
            === AttendanceCorrectionDetail::ACTION_MODIFY
        ) {
            if ($attendanceDetail->trashed()) {
                $attendanceDetail->restore();
            }

            $attendanceDetail->update([
                'attendance_status_id' =>
                    $correctionDetail->new_attendance_status_id,

                'working_status_id' =>
                    $correctionDetail->new_working_status_id,

                'check_in_time' =>
                    $this->nullableTime(
                        $correctionDetail->new_check_in_time
                    ),

                'check_out_time' =>
                    $this->nullableTime(
                        $correctionDetail->new_check_out_time
                    ),

                'normal_hours' =>
                    (float) (
                        $correctionDetail->new_normal_hours
                        ?? 0
                    ),

                'ot_hours' =>
                    (float) (
                        $correctionDetail->new_ot_hours
                        ?? 0
                    ),

                'ot_amount' =>
                    (float) (
                        $correctionDetail->new_ot_amount
                        ?? 0
                    ),

                'attendance_source' =>
                    'attendance_correction',

                'remarks' =>
                    $this->nullableTrim(
                        $correctionDetail->new_remarks
                    ),

                'is_active' => true,
                'updated_by' => auth()->id(),
            ]);
        }
    }

    private function recalculateAttendanceSummary(
        LabourAttendance $attendance
    ): void {
        $details = $attendance
            ->details()
            ->where('is_active', true)
            ->with('attendanceStatus')
            ->get();

        $presentCount = 0;
        $absentCount = 0;
        $leaveCount = 0;
        $halfDayCount = 0;

        foreach ($details as $detail) {
            $code = strtoupper(
                trim(
                    (string) (
                        $detail->attendanceStatus?->code
                        ?? ''
                    )
                )
            );

            $name = strtoupper(
                trim(
                    (string) (
                        $detail->attendanceStatus?->name
                        ?? ''
                    )
                )
            );

            if (
                in_array(
                    $code,
                    ['P', 'PRESENT'],
                    true
                )
                || $name === 'PRESENT'
            ) {
                $presentCount++;

                continue;
            }

            if (
                in_array(
                    $code,
                    ['A', 'ABSENT'],
                    true
                )
                || $name === 'ABSENT'
            ) {
                $absentCount++;

                continue;
            }

            if (
                in_array(
                    $code,
                    ['L', 'LEAVE'],
                    true
                )
                || str_contains($name, 'LEAVE')
            ) {
                $leaveCount++;

                continue;
            }

            if (
                in_array(
                    $code,
                    ['HD', 'HALF_DAY', 'HALFDAY'],
                    true
                )
                || str_contains($name, 'HALF')
            ) {
                $halfDayCount++;
            }
        }

        $attendance->update([
            'total_labours' => $details->count(),
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'leave_count' => $leaveCount,
            'half_day_count' => $halfDayCount,

            'total_normal_hours' => round(
                (float) $details->sum('normal_hours'),
                2
            ),

            'total_ot_hours' => round(
                (float) $details->sum('ot_hours'),
                2
            ),

            'updated_by' => auth()->id(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store Validation
    |--------------------------------------------------------------------------
    */

    private function validateStoreRequest(
        Request $request
    ): array {
        return $request->validate([
            'labour_attendance_id' => [
                'required',
                'integer',

                Rule::exists(
                    'labour_attendances',
                    'id'
                )->where(
                    function ($query): void {
                        $query
                            ->where('status', 'approved')
                            ->where('is_active', true)
                            ->whereNull('deleted_at');
                    }
                ),
            ],

            'new_attendance_date' => [
                'required',
                'date',
            ],

            'correction_reason' => [
                'required',
                'string',
                'min:5',
                'max:3000',
            ],

            'details' => [
                'nullable',
                'array',
            ],

            'details.*.action_type' => [
                'required',
                Rule::in([
                    AttendanceCorrectionDetail::ACTION_ADD,
                    AttendanceCorrectionDetail::ACTION_MODIFY,
                    AttendanceCorrectionDetail::ACTION_REMOVE,
                ]),
            ],

            'details.*.labour_attendance_detail_id' => [
                'nullable',
                'integer',
                'exists:labour_attendance_details,id',
            ],

            'details.*.labour_id' => [
                'required',
                'integer',
                'exists:labours,id',
            ],

            'details.*.new_attendance_status_id' => [
                'nullable',
                'integer',
                'exists:attendance_statuses,id',
            ],

            'details.*.new_working_status_id' => [
                'nullable',
                'integer',
                'exists:working_statuses,id',
            ],

            'details.*.new_check_in_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'details.*.new_check_out_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'details.*.new_normal_hours' => [
                'nullable',
                'numeric',
                'min:0',
                'max:24',
            ],

            'details.*.new_ot_hours' => [
                'nullable',
                'numeric',
                'min:0',
                'max:24',
            ],

            'details.*.new_ot_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99999999.99',
            ],

            'details.*.new_remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],

            /*
             * A line reason is required only after the controller confirms
             * that the row contains an actual correction. Unchanged Modify
             * rows are ignored and must not fail validation.
             */
            'details.*.line_reason' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Persist Correction Detail Rows
    |--------------------------------------------------------------------------
    */

    /**
     * @throws ValidationException
     */
    private function persistCorrectionDetails(
        AttendanceCorrection $correction,
        LabourAttendance $attendance,
        array $submittedDetails
    ): void {
        $existingAttendanceDetails = $attendance
            ->details()
            ->withTrashed()
            ->get()
            ->keyBy('id');

        $existingLabourIds = $attendance
            ->details()
            ->where('is_active', true)
            ->pluck('labour_id')
            ->map(
                fn ($id): int => (int) $id
            )
            ->all();

        $submittedLabourActions = [];
        $sortOrder = 0;

        foreach ($submittedDetails as $rowIndex => $row) {
            $actionType = $row['action_type'];
            $labourId = (int) $row['labour_id'];

            $submissionKey = "{$actionType}:{$labourId}";

            if (
                in_array(
                    $submissionKey,
                    $submittedLabourActions,
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    "details.{$rowIndex}.labour_id" => [
                        'The same labour and correction action cannot be submitted more than once.',
                    ],
                ]);
            }

            $submittedLabourActions[] = $submissionKey;

            $attendanceDetailId = ! empty(
                $row['labour_attendance_detail_id']
            )
                ? (int) $row[
                    'labour_attendance_detail_id'
                ]
                : null;

            $originalDetail = $attendanceDetailId
                ? $existingAttendanceDetails->get(
                    $attendanceDetailId
                )
                : null;

            if (
                in_array(
                    $actionType,
                    [
                        AttendanceCorrectionDetail::ACTION_MODIFY,
                        AttendanceCorrectionDetail::ACTION_REMOVE,
                    ],
                    true
                )
            ) {
                if (! $originalDetail) {
                    throw ValidationException::withMessages([
                        "details.{$rowIndex}.labour_attendance_detail_id" => [
                            'The selected attendance row does not belong to the attendance sheet.',
                        ],
                    ]);
                }

                if (
                    (int) $originalDetail
                        ->labour_attendance_id
                    !== (int) $attendance->id
                ) {
                    throw ValidationException::withMessages([
                        "details.{$rowIndex}.labour_attendance_detail_id" => [
                            'The selected attendance row belongs to another attendance sheet.',
                        ],
                    ]);
                }

                if (
                    (int) $originalDetail->labour_id
                    !== $labourId
                ) {
                    throw ValidationException::withMessages([
                        "details.{$rowIndex}.labour_id" => [
                            'The selected labour does not match the original attendance row.',
                        ],
                    ]);
                }
            }

            if (
                $actionType
                === AttendanceCorrectionDetail::ACTION_ADD
            ) {
                if ($originalDetail) {
                    throw ValidationException::withMessages([
                        "details.{$rowIndex}.labour_attendance_detail_id" => [
                            'An Add Labour correction must not reference an existing attendance row.',
                        ],
                    ]);
                }

                if (
                    in_array(
                        $labourId,
                        $existingLabourIds,
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        "details.{$rowIndex}.labour_id" => [
                            'This labour already exists in the attendance sheet. Use Modify instead of Add.',
                        ],
                    ]);
                }

                $labourToAdd = Labour::query()
                    ->whereKey($labourId)
                    ->where('is_active', true)
                    ->firstOrFail();

                /*
                 * Project assignment does not block temporary movement.
                 * A labour assigned to another project can be added here
                 * provided they do not already have payable attendance on
                 * another project for this date.
                 */
                $hasConflictingAttendance = $attendance->isAdditionalWork()
                    ? false
                    : LabourAttendanceDetail::query()
                    ->join(
                        'labour_attendances',
                        'labour_attendances.id',
                        '=',
                        'labour_attendance_details.labour_attendance_id'
                    )
                    ->join(
                        'attendance_statuses',
                        'attendance_statuses.id',
                        '=',
                        'labour_attendance_details.attendance_status_id'
                    )
                    ->where(
                        'labour_attendance_details.labour_id',
                        $labourId
                    )
                    ->where(
                        'labour_attendance_details.is_active',
                        true
                    )
                    ->whereNull('labour_attendance_details.deleted_at')
                    ->whereDate(
                        'labour_attendances.attendance_date',
                        $attendance->attendance_date
                    )
                    ->where(
                        'labour_attendances.id',
                        '!=',
                        $attendance->id
                    )
                    ->where(
                        'labour_attendances.project_id',
                        '!=',
                        $attendance->project_id
                    )
                    ->where(
                        'labour_attendances.is_active',
                        true
                    )
                    ->whereNull('labour_attendances.deleted_at')
                    ->where(
                        'attendance_statuses.is_active',
                        true
                    )
                    ->where(
                        'attendance_statuses.payable_factor',
                        '>',
                        0
                    )
                    ->exists();

                if ($hasConflictingAttendance) {
                    throw ValidationException::withMessages([
                        "details.{$rowIndex}.labour_id" => [
                            'This labour already has working/payable attendance recorded on another project for the same date.',
                        ],
                    ]);
                }
            }

            if (
                in_array(
                    $actionType,
                    [
                        AttendanceCorrectionDetail::ACTION_ADD,
                        AttendanceCorrectionDetail::ACTION_MODIFY,
                    ],
                    true
                )
                && empty(
                    $row['new_attendance_status_id']
                )
            ) {
                throw ValidationException::withMessages([
                    "details.{$rowIndex}.new_attendance_status_id" => [
                        'The new Attendance Status is required for Add and Modify corrections.',
                    ],
                ]);
            }

            $normalHours = $attendance->isAdditionalWork()
                ? 0.0
                : (float) (
                    $row['new_normal_hours'] ?? 0
                );

            $labourForOt = Labour::query()
                ->whereKey($labourId)
                ->firstOrFail();

            $otValues = $this->resolveCorrectionOtValues(
                $labourForOt,
                $row
            );

            $otHours = $otValues['ot_hours'];
            $otAmount = $otValues['ot_amount'];

            if (($normalHours + $otHours) > 24) {
                throw ValidationException::withMessages([
                    "details.{$rowIndex}.new_ot_hours" => [
                        'Combined Normal and OT hours cannot exceed 24 hours.',
                    ],
                ]);
            }

            if (
                filled($row['new_check_out_time'] ?? null)
                && blank($row['new_check_in_time'] ?? null)
            ) {
                throw ValidationException::withMessages([
                    "details.{$rowIndex}.new_check_in_time" => [
                        'Check In is required when Check Out is entered.',
                    ],
                ]);
            }

            $sortOrder++;

            $beforeSnapshot = $originalDetail
                ? AttendanceCorrectionDetail
                    ::snapshotFromAttendanceDetail(
                        $originalDetail
                    )
                : null;

            $afterSnapshot = $actionType
                === AttendanceCorrectionDetail::ACTION_REMOVE
                    ? null
                    : $this->buildAfterSnapshot(
                        row: $row,
                        labourId: $labourId,
                        originalDetail: $originalDetail,
                        attendance: $attendance
                    );

            /*
             * Existing rows that are identical to the approved attendance
             * are ignored completely. They do not create correction details
             * and do not require a row-level reason.
             */
            if (
                $actionType
                    === AttendanceCorrectionDetail::ACTION_MODIFY
                && ! $this->snapshotsDiffer(
                    before: $beforeSnapshot ?? [],
                    after: $afterSnapshot ?? []
                )
            ) {
                continue;
            }

            /*
             * Add, Remove, and genuinely changed Modify rows must explain
             * why the correction is required.
             */
            $lineReason = $this->nullableTrim(
                $row['line_reason'] ?? null
            );

            if ($lineReason === null || strlen($lineReason) < 3) {
                throw ValidationException::withMessages([
                    "details.{$rowIndex}.line_reason" => [
                        'Reason for correction is required for every changed, added, or removed labour row and must contain at least 3 characters.',
                    ],
                ]);
            }

            AttendanceCorrectionDetail::create([
                'attendance_correction_id' =>
                    $correction->id,

                'labour_attendance_detail_id' =>
                    $originalDetail?->id,

                'labour_id' => $labourId,

                'action_type' => $actionType,

                'old_attendance_status_id' =>
                    $originalDetail
                        ? $originalDetail
                            ->attendance_status_id
                        : null,

                'new_attendance_status_id' =>
                    $actionType
                        === AttendanceCorrectionDetail::ACTION_REMOVE
                            ? null
                            : $this->nullableInteger(
                                $row[
                                    'new_attendance_status_id'
                                ] ?? null
                            ),

                'old_working_status_id' =>
                    $originalDetail
                        ? $originalDetail
                            ->working_status_id
                        : null,

                'new_working_status_id' =>
                    $actionType
                        === AttendanceCorrectionDetail::ACTION_REMOVE
                            ? null
                            : $this->nullableInteger(
                                $row[
                                    'new_working_status_id'
                                ] ?? null
                            ),

                'old_check_in_time' =>
                    $this->nullableTime(
                        $originalDetail?->check_in_time
                    ),

                'new_check_in_time' =>
                    $actionType
                        === AttendanceCorrectionDetail::ACTION_REMOVE
                            ? null
                            : $this->nullableTime(
                                $row[
                                    'new_check_in_time'
                                ] ?? null
                            ),

                'old_check_out_time' =>
                    $this->nullableTime(
                        $originalDetail?->check_out_time
                    ),

                'new_check_out_time' =>
                    $actionType
                        === AttendanceCorrectionDetail::ACTION_REMOVE
                            ? null
                            : $this->nullableTime(
                                $row[
                                    'new_check_out_time'
                                ] ?? null
                            ),

                'old_normal_hours' =>
                    $originalDetail
                        ? (float) $originalDetail
                            ->normal_hours
                        : null,

                'new_normal_hours' =>
                    $actionType
                        === AttendanceCorrectionDetail::ACTION_REMOVE
                            ? null
                            : $normalHours,

                'old_ot_hours' =>
                    $originalDetail
                        ? (float) $originalDetail
                            ->ot_hours
                        : null,

                'old_ot_amount' =>
                    $originalDetail?->ot_amount !== null
                        ? (float) $originalDetail->ot_amount
                        : null,

                'new_ot_hours' =>
                    $actionType
                        === AttendanceCorrectionDetail::ACTION_REMOVE
                            ? null
                            : $otHours,

                'new_ot_amount' =>
                    $actionType
                        === AttendanceCorrectionDetail::ACTION_REMOVE
                            ? null
                            : $otAmount,

                'old_remarks' =>
                    $this->nullableTrim(
                        $originalDetail?->remarks
                    ),

                'new_remarks' =>
                    $actionType
                        === AttendanceCorrectionDetail::ACTION_REMOVE
                            ? null
                            : $this->nullableTrim(
                                $row[
                                    'new_remarks'
                                ] ?? null
                            ),

                'before_snapshot' => $beforeSnapshot,
                'after_snapshot' => $afterSnapshot,

                'line_reason' => $lineReason,

                'sort_order' => $sortOrder,
                'is_active' => true,

                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Snapshot Helpers
    |--------------------------------------------------------------------------
    */

    private function buildAfterSnapshot(
        array $row,
        int $labourId,
        ?LabourAttendanceDetail $originalDetail,
        LabourAttendance $attendance
    ): array {
        $labour = Labour::query()
            ->whereKey($labourId)
            ->firstOrFail();

        $otValues = $this->resolveCorrectionOtValues(
            $labour,
            $row
        );

        return [
            'labour_attendance_detail_id' =>
                $originalDetail?->id,

            'labour_id' => $labourId,

            'attendance_status_id' =>
                $this->nullableInteger(
                    $row[
                        'new_attendance_status_id'
                    ] ?? null
                ),

            'working_status_id' =>
                $this->nullableInteger(
                    $row[
                        'new_working_status_id'
                    ] ?? null
                ),

            'check_in_time' =>
                $this->nullableTime(
                    $row[
                        'new_check_in_time'
                    ] ?? null
                ),

            'check_out_time' =>
                $this->nullableTime(
                    $row[
                        'new_check_out_time'
                    ] ?? null
                ),

            'normal_hours' =>
                $attendance->isAdditionalWork()
                    ? 0.0
                    : (float) (
                        $row[
                            'new_normal_hours'
                        ] ?? 0
                    ),

            'ot_hours' =>
                $otValues['ot_hours'],

            'ot_amount' =>
                $otValues['ot_amount'],

            'remarks' =>
                $this->nullableTrim(
                    $row[
                        'new_remarks'
                    ] ?? null
                ),

            'is_active' => true,
        ];
    }

    private function snapshotsDiffer(
        array $before,
        array $after
    ): bool {
        $fields = [
            'attendance_status_id',
            'working_status_id',
            'check_in_time',
            'check_out_time',
            'normal_hours',
            'ot_hours',
            'ot_amount',
            'remarks',
            'is_active',
        ];

        foreach ($fields as $field) {
            $beforeValue = $before[$field] ?? null;
            $afterValue = $after[$field] ?? null;

            if (
                in_array(
                    $field,
                    [
                        'normal_hours',
                        'ot_hours',
                        'ot_amount',
                    ],
                    true
                )
            ) {
                if (
                    (float) ($beforeValue ?? 0)
                    !== (float) ($afterValue ?? 0)
                ) {
                    return true;
                }

                continue;
            }

            if (
                in_array(
                    $field,
                    [
                        'check_in_time',
                        'check_out_time',
                    ],
                    true
                )
            ) {
                if (
                    $this->nullableTime($beforeValue)
                    !== $this->nullableTime($afterValue)
                ) {
                    return true;
                }

                continue;
            }

            if (
                $this->normaliseComparableValue(
                    $beforeValue
                )
                !==
                $this->normaliseComparableValue(
                    $afterValue
                )
            ) {
                return true;
            }
        }

        return false;
    }

    private function normaliseComparableValue(
        mixed $value
    ): mixed {
        if (is_string($value)) {
            $value = trim($value);

            return $value === ''
                ? null
                : $value;
        }

        return $value;
    }


    /**
     * OT business rule for Attendance Corrections.
     * OT Rate = Daily Wage Rate / 8.
     *
     * A submitted OT Amount overrides hours and recalculates OT Hours.
     * If no amount is submitted, amount is calculated from OT Hours.
     */
    private function resolveCorrectionOtValues(
        Labour $labour,
        array $row
    ): array {
        $dailyRate = (float) ($labour->current_daily_rate ?? 0);
        $otRate = $dailyRate > 0
            ? $dailyRate / 8
            : 0.0;

        $submittedHours = max(
            0,
            (float) ($row['new_ot_hours'] ?? 0)
        );

        $hasAmount = array_key_exists('new_ot_amount', $row)
            && $row['new_ot_amount'] !== null
            && $row['new_ot_amount'] !== '';

        if ($hasAmount) {
            $otAmount = max(
                0,
                (float) $row['new_ot_amount']
            );

            $otHours = $otRate > 0
                ? round($otAmount / $otRate, 2)
                : $submittedHours;
        } else {
            $otHours = round($submittedHours, 2);
            $otAmount = $otRate > 0 && $otHours > 0
                ? round($otHours * $otRate, 2)
                : 0.0;
        }

        if ($otHours > 24) {
            throw ValidationException::withMessages([
                'details' => [
                    "Calculated OT hours for {$labour->full_name} exceed 24 hours.",
                ],
            ]);
        }

        return [
            'ot_rate' => round($otRate, 2),
            'ot_hours' => round($otHours, 2),
            'ot_amount' => round($otAmount, 2),
        ];
    }

    /**
     * Validate the corrected attendance date at both Save and Apply stages.
     *
     * Regular Attendance:
     * Only one Regular Attendance sheet may exist for a project/date.
     *
     * Additional Work:
     * Multiple Additional Work sessions may exist on a project/date, but
     * the same Work Session must not be duplicated.
     *
     * @throws ValidationException
     */
    private function validateTargetDateAvailability(
        LabourAttendance $attendance,
        mixed $targetDate,
        bool $applying = false
    ): void {
        if (blank($targetDate)) {
            return;
        }

        $targetDate = \Carbon\Carbon::parse(
            $targetDate
        )->format('Y-m-d');

        $currentDate = $attendance
            ->attendance_date
            ?->format('Y-m-d');

        if ($targetDate === $currentDate) {
            return;
        }

        $conflictQuery = LabourAttendance::query()
            ->where(
                'id',
                '!=',
                $attendance->id
            )
            ->where(
                'project_id',
                $attendance->project_id
            )
            ->whereDate(
                'attendance_date',
                $targetDate
            )
            ->where(
                'attendance_type',
                $attendance->attendance_type
                    ?? 'regular'
            )
            ->where('is_active', true)
            ->whereNull('deleted_at');

        if ($attendance->isAdditionalWork()) {
            $conflictQuery->where(
                'work_session_name',
                $attendance->work_session_name
            );
        }

        if (! $conflictQuery->exists()) {
            return;
        }

        $projectName = $attendance->project?->project_name
            ?? 'this project';

        $formattedDate = \Carbon\Carbon::parse(
            $targetDate
        )->format('d M Y');

        if ($attendance->isAdditionalWork()) {
            $sessionName = filled(
                $attendance->work_session_name
            )
                ? " '{$attendance->work_session_name}'"
                : '';

            $message = $applying
                ? "Additional Work{$sessionName} now already exists for {$projectName} on {$formattedDate}. The correction cannot be applied because the target date became unavailable after this request was created."
                : "Additional Work{$sessionName} already exists for {$projectName} on {$formattedDate}. This correction request cannot be created for that date.";
        } else {
            $message = $applying
                ? "Regular Attendance now already exists for {$projectName} on {$formattedDate}. The correction cannot be applied because the target date became unavailable after this request was created."
                : "Regular Attendance already exists for {$projectName} on {$formattedDate}. This correction request cannot be created for that date.";
        }

        throw ValidationException::withMessages([
            'new_attendance_date' => [
                $message,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Approved Attendance Query
    |--------------------------------------------------------------------------
    */

    private function approvedAttendanceQuery(): Builder
    {
        return LabourAttendance::query()
            ->whereIn(
                'project_id',
                ProjectAccess::allowedProjectIds()
            )
            ->where('status', 'approved')
            ->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Correction Number
    |--------------------------------------------------------------------------
    */

    private function generateCorrectionNumber(): string
    {
        $datePart = now()->format('Ymd');

        $prefix = "ACR-{$datePart}-";

        $latestNumber = AttendanceCorrection::query()
            ->withTrashed()
            ->where(
                'correction_number',
                'like',
                "{$prefix}%"
            )
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('correction_number');

        $nextSequence = 1;

        if ($latestNumber) {
            $lastSequence = (int) Str::afterLast(
                $latestNumber,
                '-'
            );

            $nextSequence = $lastSequence + 1;
        }

        do {
            $correctionNumber = $prefix
                . str_pad(
                    (string) $nextSequence,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

            $exists = AttendanceCorrection::withTrashed()
                ->where(
                    'correction_number',
                    $correctionNumber
                )
                ->exists();

            $nextSequence++;
        } while ($exists);

        return $correctionNumber;
    }

    /*
    |--------------------------------------------------------------------------
    | Filter Query
    |--------------------------------------------------------------------------
    */

    private function buildFilteredQuery(
        Request $request,
        bool $includeStatusFilter = true
    ): Builder {
        $query = AttendanceCorrection::query()
            ->whereIn(
                'project_id',
                ProjectAccess::allowedProjectIds()
            );

        $search = trim(
            (string) $request->input('search', '')
        );

        $projectId = $request->filled('project_id')
            ? $request->integer('project_id')
            : null;

        $status = trim(
            (string) $request->input('status', '')
        );

        $attendanceDate = $request->input(
            'attendance_date'
        );

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if ($search !== '') {
            $query->where(function (
                Builder $searchQuery
            ) use ($search): void {
                $searchQuery
                    ->where(
                        'correction_number',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'correction_reason',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'rejection_reason',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas(
                        'labourAttendance',
                        function (
                            Builder $attendanceQuery
                        ) use ($search): void {
                            $attendanceQuery->where(
                                'attendance_number',
                                'like',
                                "%{$search}%"
                            );
                        }
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
                    )
                    ->orWhereHas(
                        'createdBy',
                        function (
                            Builder $userQuery
                        ) use ($search): void {
                            $userQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
            });
        }

        if ($projectId) {
            $query->where(
                'project_id',
                $projectId
            );
        }

        if (filled($attendanceDate)) {
            $query->whereDate(
                'attendance_date',
                $attendanceDate
            );
        }

        if (filled($dateFrom)) {
            $query->whereDate(
                'attendance_date',
                '>=',
                $dateFrom
            );
        }

        if (filled($dateTo)) {
            $query->whereDate(
                'attendance_date',
                '<=',
                $dateTo
            );
        }

        if (
            $includeStatusFilter
            && array_key_exists(
                $status,
                AttendanceCorrection::statuses()
            )
        ) {
            $query->where('status', $status);
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    private function correctionRelationships(): array
    {
        return [
            'project',

            'labourAttendance',
            'labourAttendance.project',
            'labourAttendance.shift',
            'labourAttendance.recordedBy',

            'details' => function ($query): void {
                $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id');
            },

            'details.labour',
            'details.labourAttendanceDetail',

            'details.oldAttendanceStatus',
            'details.newAttendanceStatus',

            'details.oldWorkingStatus',
            'details.newWorkingStatus',

            'details.createdBy',
            'details.updatedBy',

            'createdBy',
            'updatedBy',

            'submittedBy',
            'approvedBy',
            'rejectedBy',
            'appliedBy',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Audit Values
    |--------------------------------------------------------------------------
    */

    private function auditValues(
        AttendanceCorrection $correction
    ): array {
        return [
            'id' => $correction->id,

            'correction_number' =>
                $correction->correction_number,

            'labour_attendance_id' =>
                $correction->labour_attendance_id,

            'attendance_number' =>
                $correction
                    ->labourAttendance
                    ?->attendance_number,

            'project_id' =>
                $correction->project_id,

            'project_name' =>
                $correction->project?->project_name,

            'attendance_date' =>
                $correction->attendance_date
                    ?->format('Y-m-d'),

            'old_attendance_date' =>
                $correction->old_attendance_date
                    ?->format('Y-m-d'),

            'new_attendance_date' =>
                $correction->new_attendance_date
                    ?->format('Y-m-d'),

            'correction_reason' =>
                $correction->correction_reason,

            'status' => $correction->status,

            'details_count' =>
                $correction
                    ->details
                    ->where('is_active', true)
                    ->count(),

            'created_by' =>
                $correction->created_by,

            'updated_by' =>
                $correction->updated_by,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Value Helpers
    |--------------------------------------------------------------------------
    */

    private function nullableInteger(
        mixed $value
    ): ?int {
        if (blank($value)) {
            return null;
        }

        return (int) $value;
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

    private function nullableTime(
        mixed $value
    ): ?string {
        if (blank($value)) {
            return null;
        }

        $value = (string) $value;

        return strlen($value) >= 5
            ? substr($value, 0, 5)
            : $value;
    }
}
