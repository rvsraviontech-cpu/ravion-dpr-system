<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Http\Requests\StoreLabourAttendanceRequest;
use App\Http\Requests\UpdateLabourAttendanceRequest;
use App\Models\AttendanceStatus;
use App\Models\Labour;
use App\Models\LabourAttendance;
use App\Models\LabourAttendanceDetail;
use App\Models\Project;
use App\Models\Shift;
use App\Models\WorkingStatus;
use App\Support\ProjectAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class LabourAttendanceController extends Controller
{
    /**
     * Display Labour Attendance sheets.
     */
    public function index(Request $request): View
    {
        $query = LabourAttendance::query()
            ->with([
                'project',
                'shift',
                'recordedBy',
                'submittedBy',
                'approvedBy',
                'rejectedBy',
            ]);

        $query->whereIn(
            'project_id',
            ProjectAccess::allowedProjectIds()
        );

        if ($request->filled('search')) {
            $search = trim(
                $request->string('search')->toString()
            );

            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where(
                        'attendance_number',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas(
                        'project',
                        function (Builder $projectQuery) use ($search): void {
                            $projectQuery
                                ->where(
                                    'project_code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'project_name',
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

        if ($request->filled('shift_id')) {
            $query->where(
                'shift_id',
                $request->integer('shift_id')
            );
        }

        if ($request->filled('attendance_date')) {
            $query->whereDate(
                'attendance_date',
                $request->input('attendance_date')
            );
        }

        if ($request->filled('date_from')) {
            $query->whereDate(
                'attendance_date',
                '>=',
                $request->input('date_from')
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'attendance_date',
                '<=',
                $request->input('date_to')
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->input('status')
            );
        }

        $labourAttendances = $query
            ->ordered()
            ->paginate(
                config('rds.pagination.per_page', 15)
            )
            ->withQueryString();

        return view(
            'labour-attendances.index',
            [
                'labourAttendances' => $labourAttendances,

                'projects' => ProjectAccess::availableProjects(),

                'shifts' => Shift::query()
                    ->active()
                    ->ordered()
                    ->get(),
            ]
        );
    }

    /**
     * Show the Labour Attendance creation page.
     */
    public function create(): View
    {
        return view(
            'labour-attendances.create',
            $this->getFormOptions()
        );
    }

    /**
     * Store a newly created Labour Attendance sheet.
     *
     * @throws Throwable
     */
    public function store(
        StoreLabourAttendanceRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        ProjectAccess::authorize(
            (int) $validated['project_id']
        );

        try {
            $attendance = DB::transaction(
                function () use ($validated): LabourAttendance {
                    $attendance = LabourAttendance::create([
                        'attendance_number' =>
                            $this->generateAttendanceNumber(
                                $validated['attendance_date']
                            ),

                        'project_id' =>
                            (int) $validated['project_id'],

                        'attendance_date' =>
                            $validated['attendance_date'],

                        'shift_id' =>
                            ! empty($validated['shift_id'])
                                ? (int) $validated['shift_id']
                                : null,

                        'attendance_type' =>
                            $validated['attendance_type'],

                        'work_session_name' =>
                            $validated['attendance_type'] === 'additional_work'
                                ? $this->nullableTrim(
                                    $validated['work_session_name'] ?? null
                                )
                                : null,

                        'status' => 'draft',

                        'recorded_by' => auth()->id(),

                        'remarks' =>
                            $this->nullableTrim(
                                $validated['remarks'] ?? null
                            ),

                        'is_active' => true,

                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);

                    $this->persistDetails(
                        $attendance,
                        $validated['details']
                    );

                    $attendance->recalculateSummary();

                    $attendance->load(
                        $this->attendanceRelationships()
                    );

                    AuditHelper::log(
                        'Labour Attendance',
                        'Created',
                        LabourAttendance::class,
                        $attendance->id,
                        "Labour Attendance '{$attendance->attendance_number}' was created.",
                        null,
                        $this->auditValues($attendance)
                    );

                    return $attendance;
                }
            );

            return redirect()
                ->route(
                    'labour-attendances.show',
                    $attendance
                )
                ->with(
                    'success',
                    'Labour Attendance created successfully.'
                );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to create the Labour Attendance sheet. Please review the information and try again.'
                );
        }
    }

    /**
     * Display the specified Labour Attendance sheet.
     */
    public function show(
        LabourAttendance $labourAttendance
    ): View {
        ProjectAccess::authorize(
            (int) $labourAttendance->project_id
        );

        $labourAttendance->load([
            ...$this->attendanceRelationships(),

            'details' => function ($query): void {
                $query
                    ->with([
                        'labour',
                        'attendanceStatus',
                        'workingStatus',
                        'labourCategory',
                        'labourType',
                        'designationRole',
                        'skillCategory',
                        'contractor',
                    ])
                    ->orderBy('id');
            },

            'createdBy',
            'updatedBy',
        ]);

        return view(
            'labour-attendances.show',
            compact('labourAttendance')
        );
    }

    /**
     * Show the Labour Attendance edit page.
     */
    public function edit(
    LabourAttendance $labourAttendance
): View|RedirectResponse {
    ProjectAccess::authorize(
        (int) $labourAttendance->project_id
    );

    if (! $labourAttendance->canBeEdited()) {
        return redirect()
            ->route(
                'labour-attendances.show',
                $labourAttendance
            )
            ->with(
                'error',
                "This attendance sheet cannot be edited because its status is {$labourAttendance->display_status}."
            );
    }

    $labourAttendance->load([
        'project',
        'shift',

        'details' => function ($query): void {
            $query
                ->where('is_active', true)
                ->with([
                    'labour',
                    'attendanceStatus',
                    'workingStatus',
                ])
                ->orderBy('id');
        },
    ]);

    return view(
        'labour-attendances.edit',
        array_merge(
            [
                'labourAttendance' =>
                    $labourAttendance,
            ],
            $this->getFormOptions()
        )
    );
}



    /**
     * Update the specified Labour Attendance sheet.
     *
     * @throws Throwable
     */
    public function update(
    UpdateLabourAttendanceRequest $request,
    LabourAttendance $labourAttendance
): RedirectResponse {
    ProjectAccess::authorize(
        (int) $labourAttendance->project_id
    );

    if (! $labourAttendance->canBeEdited()) {
        return redirect()
            ->route(
                'labour-attendances.show',
                $labourAttendance
            )
            ->with(
                'error',
                'This attendance sheet can no longer be edited.'
            );
    }

    $validated = $request->validated();

    ProjectAccess::authorize(
        (int) $validated['project_id']
    );

    try {
        DB::transaction(
            function () use (
                $validated,
                $labourAttendance
            ): void {
                $labourAttendance->load(
                    $this->attendanceRelationships()
                );

                $oldValues =
                    $this->auditValues(
                        $labourAttendance
                    );

                $labourAttendance->update([
                    'project_id' =>
                        (int) $validated['project_id'],

                    'attendance_date' =>
                        $validated['attendance_date'],

                    'shift_id' =>
                        ! empty($validated['shift_id'])
                            ? (int) $validated['shift_id']
                            : null,

                    'attendance_type' =>
                        $validated['attendance_type'],

                    'work_session_name' =>
                        $validated['attendance_type'] === 'additional_work'
                            ? $this->nullableTrim(
                                $validated['work_session_name'] ?? null
                            )
                            : null,

                    'remarks' =>
                        $this->nullableTrim(
                            $validated['remarks'] ?? null
                        ),

                    'status' =>
                        $labourAttendance->status,

                    'rejection_reason' =>
                        $labourAttendance->rejection_reason,

                    'rejected_by' =>
                        $labourAttendance->rejected_by,

                    'rejected_at' =>
                        $labourAttendance->rejected_at,

                    'reopen_reason' =>
                        $labourAttendance->reopen_reason,

                    'reopened_by' =>
                        $labourAttendance->reopened_by,

                    'reopened_at' =>
                        $labourAttendance->reopened_at,

                    'updated_by' =>
                        auth()->id(),
                ]);

                $this->syncEditableDraftDetails(
    $labourAttendance,
    $validated['details'] ?? []
);

                $labourAttendance->recalculateSummary();

                $labourAttendance->refresh();

                $labourAttendance->load(
                    $this->attendanceRelationships()
                );

                AuditHelper::log(
                    'Labour Attendance',
                    'Updated',
                    LabourAttendance::class,
                    $labourAttendance->id,
                    "Labour Attendance '{$labourAttendance->attendance_number}' was updated.",
                    $oldValues,
                    $this->auditValues(
                        $labourAttendance
                    )
                );
            }
        );

        return redirect()
            ->route(
                'labour-attendances.show',
                $labourAttendance
            )
            ->with(
                'success',
                'Labour Attendance updated successfully.'
            );
    } catch (ValidationException $exception) {
        throw $exception;
    } catch (Throwable $exception) {
        report($exception);

        return back()
            ->withInput()
            ->with(
                'error',
                'Unable to update the Labour Attendance sheet. Please review the information and try again.'
            );
    }
}
    /**
     * Submit a Draft or Rejected attendance sheet for approval.
     */
    public function submit(
        LabourAttendance $labourAttendance
    ): RedirectResponse {
        ProjectAccess::authorize(
            (int) $labourAttendance->project_id
        );
        if (! $labourAttendance->canBeSubmitted()) {
            return back()->with(
                'error',
                'Only a Draft or Rejected attendance sheet with labour rows can be submitted.'
            );
        }

        $labourAttendance->load(
            $this->attendanceRelationships()
        );

        $oldValues =
            $this->auditValues($labourAttendance);

        $labourAttendance->update([
            'status' => 'submitted',
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),

            'approved_by' => null,
            'approved_at' => null,

            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            'reopened_by' => $labourAttendance->reopened_by,
'reopened_at' => $labourAttendance->reopened_at,
'reopen_reason' => $labourAttendance->reopen_reason,

            'updated_by' => auth()->id(),
        ]);

        $labourAttendance->refresh();

        $labourAttendance->load(
            $this->attendanceRelationships()
        );

        AuditHelper::log(
            'Labour Attendance',
            'Submitted',
            LabourAttendance::class,
            $labourAttendance->id,
            "Labour Attendance '{$labourAttendance->attendance_number}' was submitted for approval.",
            $oldValues,
            $this->auditValues($labourAttendance)
        );

        return back()->with(
            'success',
            'Labour Attendance submitted for approval.'
        );
    }

    /**
     * Approve a submitted Labour Attendance sheet.
     */
    public function approve(
        LabourAttendance $labourAttendance
    ): RedirectResponse {
        ProjectAccess::authorize(
            (int) $labourAttendance->project_id
        );
        if (! $labourAttendance->canBeApproved()) {
            return back()->with(
                'error',
                'Only a Submitted attendance sheet can be approved.'
            );
        }

        $labourAttendance->load(
            $this->attendanceRelationships()
        );

        $oldValues =
            $this->auditValues($labourAttendance);

        $labourAttendance->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),

            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,

            'updated_by' => auth()->id(),
        ]);

        $labourAttendance->refresh();

        $labourAttendance->load(
            $this->attendanceRelationships()
        );

        AuditHelper::log(
            'Labour Attendance',
            'Approved',
            LabourAttendance::class,
            $labourAttendance->id,
            "Labour Attendance '{$labourAttendance->attendance_number}' was approved.",
            $oldValues,
            $this->auditValues($labourAttendance)
        );

        return back()->with(
            'success',
            'Labour Attendance approved successfully.'
        );
    }

    /**
     * Reject a submitted Labour Attendance sheet.
     */
    public function reject(
        Request $request,
        LabourAttendance $labourAttendance
    ): RedirectResponse {
        ProjectAccess::authorize(
            (int) $labourAttendance->project_id
        );
        if (! $labourAttendance->canBeRejected()) {
            return back()->with(
                'error',
                'Only a Submitted attendance sheet can be rejected.'
            );
        }

        $validated = $request->validate([
            'rejection_reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $labourAttendance->load(
            $this->attendanceRelationships()
        );

        $oldValues =
            $this->auditValues($labourAttendance);

        $labourAttendance->update([
            'status' => 'rejected',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' =>
                trim($validated['rejection_reason']),

            'approved_by' => null,
            'approved_at' => null,

            'updated_by' => auth()->id(),
        ]);

        $labourAttendance->refresh();

        $labourAttendance->load(
            $this->attendanceRelationships()
        );

        AuditHelper::log(
            'Labour Attendance',
            'Rejected',
            LabourAttendance::class,
            $labourAttendance->id,
            "Labour Attendance '{$labourAttendance->attendance_number}' was rejected.",
            $oldValues,
            $this->auditValues($labourAttendance)
        );

        return back()->with(
            'success',
            'Labour Attendance rejected successfully.'
        );
    }

    /**
 * Reopen an approved Labour Attendance sheet for correction.
 */
public function reopen(
    Request $request,
    LabourAttendance $labourAttendance
): RedirectResponse {
        ProjectAccess::authorize(
            (int) $labourAttendance->project_id
        );
    if (! $labourAttendance->canBeReopened()) {
        return back()->with(
            'error',
            'Only an active Approved attendance sheet can be reopened.'
        );
    }

    $validated = $request->validate([
        'reopen_reason' => [
            'required',
            'string',
            'max:2000',
        ],
    ]);

    $labourAttendance->load(
        $this->attendanceRelationships()
    );

    $oldValues = $this->auditValues(
        $labourAttendance
    );

    $labourAttendance->update([
        'status' => 'reopened',

        'reopened_by' => auth()->id(),
        'reopened_at' => now(),
        'reopen_reason' => trim(
            $validated['reopen_reason']
        ),

        'revision_number' =>
            $labourAttendance->nextRevisionNumber(),

        /*
         * Remove the previous approval lock.
         * The original approval remains available in the Audit Trail.
         */
        'approved_by' => null,
        'approved_at' => null,

        'submitted_by' => null,
        'submitted_at' => null,

        'rejected_by' => null,
        'rejected_at' => null,
        'rejection_reason' => null,

        'updated_by' => auth()->id(),
    ]);

    $labourAttendance->refresh();

    $labourAttendance->load(
        $this->attendanceRelationships()
    );

    AuditHelper::log(
        'Labour Attendance',
        'Reopened',
        LabourAttendance::class,
        $labourAttendance->id,
        "Labour Attendance '{$labourAttendance->attendance_number}' was reopened for correction. Reason: {$labourAttendance->reopen_reason}",
        $oldValues,
        $this->auditValues($labourAttendance)
    );

    return back()->with(
        'success',
        'Labour Attendance reopened for correction successfully.'
    );
}

    /**
     * Activate or deactivate an attendance sheet.
     */
    public function toggleStatus(
        LabourAttendance $labourAttendance
    ): RedirectResponse {
        ProjectAccess::authorize(
            (int) $labourAttendance->project_id
        );
        if ($labourAttendance->isApproved()) {
            return back()->with(
                'error',
                'An approved attendance sheet cannot be deactivated.'
            );
        }

        $labourAttendance->load(
            $this->attendanceRelationships()
        );

        $oldValues =
            $this->auditValues($labourAttendance);

        $labourAttendance->update([
            'is_active' => ! $labourAttendance->is_active,
            'updated_by' => auth()->id(),
        ]);

        $labourAttendance->refresh();

        $labourAttendance->load(
            $this->attendanceRelationships()
        );

        $action = $labourAttendance->is_active
            ? 'Activated'
            : 'Deactivated';

        AuditHelper::log(
            'Labour Attendance',
            $action,
            LabourAttendance::class,
            $labourAttendance->id,
            "Labour Attendance '{$labourAttendance->attendance_number}' was {$action}.",
            $oldValues,
            $this->auditValues($labourAttendance)
        );

        return back()->with(
            'success',
            $labourAttendance->is_active
                ? 'Labour Attendance activated successfully.'
                : 'Labour Attendance deactivated successfully.'
        );
    }

    /**
     * Return the active labour pool available for the selected date.
     *
     * The selected project is used only to prioritise and classify labour:
     * 1. Labour assigned to the selected project.
     * 2. Labour without a current/default project.
     * 3. Labour assigned to another project.
     *
     * Labour already marked under a working attendance status in another
     * active attendance sheet for the selected date is excluded. While
     * editing, rows belonging to the current attendance sheet remain visible.
     */
    public function projectLabours(
        Request $request,
        Project $project
    ): JsonResponse {
        ProjectAccess::authorize(
            (int) $project->id
        );

        $validated = $request->validate([
            'attendance_date' => [
                'required',
                'date',
            ],

            'attendance_id' => [
                'nullable',
                'integer',
                'exists:labour_attendances,id',
            ],

            'attendance_type' => [
                'nullable',
                'in:regular,additional_work',
            ],
        ]);

        $attendanceDate = $validated['attendance_date'];

        $attendanceId = ! empty($validated['attendance_id'])
            ? (int) $validated['attendance_id']
            : null;

        if ($attendanceId) {
            $attendance = LabourAttendance::query()
                ->whereKey($attendanceId)
                ->where('project_id', $project->id)
                ->firstOrFail();

            ProjectAccess::authorize((int) $attendance->project_id);

            $attendance->load([
                'details' => function ($query): void {
                    $query->where('is_active', true)
                        ->with([
                            'labour.labourGroup',
                            'labour.designationRole',
                            'labour.defaultShift',
                            'attendanceStatus',
                            'workingStatus',
                        ])
                        ->orderBy('id');
                },
            ]);

            $existingByLabour = $attendance->details->keyBy('labour_id');

            $workingStatusIds = $this->workingAttendanceStatusIds();

            $attendanceType = $validated['attendance_type']
                ?? $attendance->attendance_type
                ?? 'regular';

            $dayAttendanceByLabour = $this->regularDayAttendanceByLabour(
                attendanceDate: $attendanceDate,
                excludeAttendanceId: $attendance->id
            );

            $allocatedLabourIds = LabourAttendanceDetail::query()
                ->select('labour_attendance_details.labour_id')
                ->join('labour_attendances', 'labour_attendances.id', '=', 'labour_attendance_details.labour_attendance_id')
                ->whereDate('labour_attendances.attendance_date', $attendanceDate)
                ->where('labour_attendances.id', '!=', $attendance->id)
                ->where('labour_attendances.project_id', '!=', $project->id)
                ->where('labour_attendances.is_active', true)
                ->where('labour_attendance_details.is_active', true)
                ->whereNull('labour_attendance_details.deleted_at')
                ->whereNull('labour_attendances.deleted_at')
                ->whereIn('labour_attendance_details.attendance_status_id', $workingStatusIds);

            $poolQuery = Labour::query()
                ->active()
                ->whereNotIn('employment_status', ['exited', 'suspended']);

            if ($attendanceType === 'regular') {
                $poolQuery->where(function (Builder $query) use ($allocatedLabourIds, $existingByLabour): void {
                    $query
                        ->whereNotIn('id', $allocatedLabourIds)
                        ->orWhereIn('id', $existingByLabour->keys());
                });
            }

            $pool = $poolQuery
                ->with(['labourGroup', 'designationRole', 'defaultShift', 'currentProject'])
                ->ordered()
                ->get()
                ->map(function (Labour $labour) use (
                    $project,
                    $existingByLabour,
                    $dayAttendanceByLabour
                ): array {
                    $detail = $existingByLabour->get($labour->id);

                    $assignmentGroup = match (true) {
                        (int) $labour->current_project_id === (int) $project->id
                            => 'selected_project',
                        $labour->current_project_id === null
                            => 'unassigned',
                        default
                            => 'other_project',
                    };

                    return [
                        'detail_id' => $detail?->id,
                        'id' => $labour->id,
                        'labour_id' => $labour->id,
                        'full_name' => $labour->full_name,
                        'designation_role_id' => $detail?->designation_role_id ?? $labour->designation_role_id,
                        'designation_role_name' => $detail?->designationRole?->name ?? $labour->designationRole?->name,
                        'labour_group_id' => $labour->labour_group_id,
                        'labour_group_name' => $labour->labourGroup?->name ?? 'Un-grouped Labour',
                        'labour_group_sort_order' => $labour->labourGroup?->sort_order ?? 999999,
                        'default_shift_id' => $labour->default_shift_id,
                        'default_shift_name' => $labour->defaultShift?->name,
                        'normal_shift_hours' => (float) ($labour->normal_shift_hours ?? 0),
                        'current_daily_rate' => (float) ($labour->current_daily_rate ?? 0),
                        'ot_rate' => round(((float) ($labour->current_daily_rate ?? 0)) / 8, 2),
                        'attendance_status_id' => $detail?->attendance_status_id,
                        'attendance_status_name' => $detail?->attendanceStatus?->name,
                        'working_status_id' => $detail?->working_status_id,
                        'working_status_name' => $detail?->workingStatus?->name,
                        'check_in_time' => $this->formatAttendanceTime($detail?->check_in_time),
                        'check_out_time' => $this->formatAttendanceTime($detail?->check_out_time),
                        'normal_hours' => (float) ($detail?->normal_hours ?? 0),
                        'ot_hours' => (float) ($detail?->ot_hours ?? 0),
                        'ot_amount' => $detail?->ot_amount !== null
                            ? (float) $detail->ot_amount
                            : null,
                        'attendance_source' => $detail?->attendance_source ?? 'manual',
                        'remarks' => $detail?->remarks,
                        'has_saved_attendance' => (bool) $detail,
                        'assignment_group' => $assignmentGroup,
                        'assignment_label' => match ($assignmentGroup) {
                            'selected_project' => 'Assigned to Project',
                            'unassigned' => 'Unassigned',
                            default => 'Other Project Labour',
                        },
                        'home_project_id' => $labour->current_project_id,
                        'home_project_name' => $labour->currentProject?->project_name,

                        'day_attendance_project_id' =>
                            $dayAttendanceByLabour->get($labour->id)?->project_id,

                        'day_attendance_project_name' =>
                            $dayAttendanceByLabour->get($labour->id)?->project_name,
                    ];
                })
                ->sortBy([
                    ['labour_group_sort_order', 'asc'],
                    ['labour_group_name', 'asc'],
                    ['full_name', 'asc'],
                ])
                ->values();

            $assigned = $pool->where('assignment_group', 'selected_project')->values();
            $unassigned = $pool->where('assignment_group', 'unassigned')->values();
            $otherProject = $pool->where('assignment_group', 'other_project')->values();

            return response()->json([
                'mode' => 'edit',
                'project_id' => $project->id,
                'attendance_date' => $attendanceDate,
                'attendance_id' => $attendance->id,
                'attendance_type' => $attendanceType,
                'work_session_name' => $attendance->work_session_name,
                'available_count' => $pool->count(),
                'existing_count' => $existingByLabour->count(),
                'assigned_count' => $assigned->count(),
                'unassigned_count' => $unassigned->count(),
                'other_project_count' => $otherProject->count(),
                'assigned_labours' => $assigned,
                'unassigned_labours' => $unassigned,
                'other_project_labours' => $otherProject,
                'existing_labours' => $pool->where('has_saved_attendance', true)->values(),
                'labours' => $pool,
            ]);
        }

        $workingStatusIds =
            $this->workingAttendanceStatusIds();

        $attendanceType = $validated['attendance_type']
            ?? 'regular';

        $dayAttendanceByLabour = $this->regularDayAttendanceByLabour(
            attendanceDate: $attendanceDate
        );

        $allocatedLabourIds =
            LabourAttendanceDetail::query()
                ->select(
                    'labour_attendance_details.labour_id'
                )
                ->join(
                    'labour_attendances',
                    'labour_attendances.id',
                    '=',
                    'labour_attendance_details.labour_attendance_id'
                )
                ->whereDate(
                    'labour_attendances.attendance_date',
                    $attendanceDate
                )
                ->where(
                    'labour_attendances.project_id',
                    '!=',
                    $project->id
                )
                ->where(
                    'labour_attendances.is_active',
                    true
                )
                ->where(
                    'labour_attendance_details.is_active',
                    true
                )
                ->whereNull(
                    'labour_attendance_details.deleted_at'
                )
                ->whereNull(
                    'labour_attendances.deleted_at'
                )
                ->whereIn(
                    'labour_attendance_details.attendance_status_id',
                    $workingStatusIds
                );

        $labourQuery = Labour::query()
            ->active()
            ->whereNotIn(
                'employment_status',
                [
                    'exited',
                    'suspended',
                ]
            );

        if ($attendanceType === 'regular') {
            $labourQuery->whereNotIn(
                'id',
                $allocatedLabourIds
            );
        }

        $labours = $labourQuery
            ->with([
                'labourGroup',
                'designationRole',
                'defaultShift',
                'currentProject',
            ])
            ->ordered()
            ->get()
            ->map(function (
                Labour $labour
            ) use (
                $project,
                $dayAttendanceByLabour
            ): array {
                $assignmentGroup = match (true) {
                    (int) $labour->current_project_id === (int) $project->id
                        => 'selected_project',
                    $labour->current_project_id === null
                        => 'unassigned',
                    default
                        => 'other_project',
                };

                return [
                    'id' => $labour->id,
                    'labour_id' => $labour->id,
                    'full_name' => $labour->full_name,

                    'designation_role_id' =>
                        $labour->designation_role_id,

                    'designation_role_name' =>
                        $labour->designationRole?->name,

                    'labour_group_id' => $labour->labour_group_id,
                    'labour_group_name' => $labour->labourGroup?->name ?? 'Un-grouped Labour',
                    'labour_group_sort_order' => $labour->labourGroup?->sort_order ?? 999999,

                    'default_shift_id' =>
                        $labour->default_shift_id,

                    'default_shift_name' =>
                        $labour->defaultShift?->name,

                    'normal_shift_hours' =>
                        (float) (
                            $labour->normal_shift_hours
                            ?? 0
                        ),

                    'current_daily_rate' =>
                        (float) (
                            $labour->current_daily_rate
                            ?? 0
                        ),

                    'ot_rate' => round(
                        ((float) ($labour->current_daily_rate ?? 0)) / 8,
                        2
                    ),

                    'assignment_group' => $assignmentGroup,

                    'assignment_label' => match ($assignmentGroup) {
                        'selected_project' => 'Assigned to Project',
                        'unassigned' => 'Unassigned',
                        default => 'Other Project Labour',
                    },

                    'home_project_id' =>
                        $labour->current_project_id,

                    'home_project_name' =>
                        $labour->currentProject?->project_name,

                    'day_attendance_project_id' =>
                        $dayAttendanceByLabour->get($labour->id)?->project_id,

                    'day_attendance_project_name' =>
                        $dayAttendanceByLabour->get($labour->id)?->project_name,
                ];
            })
            ->values();

        $assignedLabours = $labours
            ->where(
                'assignment_group',
                'selected_project'
            )
            ->values();

        $unassignedLabours = $labours
            ->where(
                'assignment_group',
                'unassigned'
            )
            ->values();

        $otherProjectLabours = $labours
            ->where(
                'assignment_group',
                'other_project'
            )
            ->values();

        return response()->json([
            'mode' => 'create',
            'project_id' => $project->id,
            'attendance_date' => $attendanceDate,
            'attendance_id' => null,
            'attendance_type' => $attendanceType,
            'work_session_name' => null,
            'available_count' => $labours->count(),
            'assigned_count' => $assignedLabours->count(),
            'unassigned_count' => $unassignedLabours->count(),
            'other_project_count' => $otherProjectLabours->count(),
            'existing_count' => 0,
            'assigned_labours' => $assignedLabours,
            'unassigned_labours' => $unassignedLabours,
            'other_project_labours' => $otherProjectLabours,
            'existing_labours' => [],
            'labours' => $labours,
        ]);
    }

    /**
     * Return the regular daytime working attendance for each labour on a date.
     *
     * This is informational for Additional Work entry so the Engineer can see
     * where the labourer worked during the normal day.
     */
    private function regularDayAttendanceByLabour(
        string $attendanceDate,
        ?int $excludeAttendanceId = null
    ): \Illuminate\Support\Collection {
        $workingStatusIds =
            $this->workingAttendanceStatusIds();

        $query = LabourAttendanceDetail::query()
            ->select([
                'labour_attendance_details.labour_id',
                'labour_attendances.project_id',
                'projects.project_name',
            ])
            ->join(
                'labour_attendances',
                'labour_attendances.id',
                '=',
                'labour_attendance_details.labour_attendance_id'
            )
            ->join(
                'projects',
                'projects.id',
                '=',
                'labour_attendances.project_id'
            )
            ->whereDate(
                'labour_attendances.attendance_date',
                $attendanceDate
            )
            ->where(
                'labour_attendances.attendance_type',
                'regular'
            )
            ->where(
                'labour_attendances.is_active',
                true
            )
            ->where(
                'labour_attendance_details.is_active',
                true
            )
            ->whereNull(
                'labour_attendance_details.deleted_at'
            )
            ->whereNull(
                'labour_attendances.deleted_at'
            )
            ->whereIn(
                'labour_attendance_details.attendance_status_id',
                $workingStatusIds
            );

        if ($excludeAttendanceId) {
            $query->where(
                'labour_attendances.id',
                '!=',
                $excludeAttendanceId
            );
        }

        return $query
            ->get()
            ->keyBy('labour_id');
    }

    /**
     * Format an attendance time for an HTML time input.
     */
    private function formatAttendanceTime(
        mixed $value
    ): ?string {
        if (blank($value)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse(
                $value
            )->format('H:i');
        } catch (Throwable) {
            return is_string($value)
                ? substr($value, 0, 5)
                : null;
        }
    }

    /**
     * Return active Attendance Status records for the attendance grid.
     */
    public function attendanceStatuses(): JsonResponse
    {
        $statuses = AttendanceStatus::query()
            ->active()
            ->ordered()
            ->get()
            ->map(function (
                AttendanceStatus $status
            ): array {
                return [
                    'id' => $status->id,
                    'code' => $status->code,
                    'name' => $status->name,
                    'short_name' => $status->short_name,

                    'counts_as_present' =>
                        $status->counts_as_present,

                    'counts_as_absent' =>
                        $status->counts_as_absent,

                    'payable_factor' =>
                        (float) $status->payable_factor,

                    'allows_normal_hours' =>
                        $status->allows_normal_hours,

                    'allows_ot_hours' =>
                        $status->allows_ot_hours,

                    'requires_working_status' =>
                        $status->requires_working_status,
                ];
            })
            ->values();

        return response()->json($statuses);
    }

    /**
     * Prevent ordinary attendance editing from adding or removing labour.
     *
     * @throws ValidationException
     */
    private function assertAttendanceCompositionUnchanged(
        LabourAttendance $attendance,
        array $details
    ): void {
        $existingLabourIds = $attendance
            ->details()
            ->where('is_active', true)
            ->pluck('labour_id')
            ->map(fn ($id): int => (int) $id)
            ->sort()
            ->values();

        $submittedLabourIds = collect($details)
            ->pluck('labour_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->sort()
            ->values();

        if ($existingLabourIds->all() === $submittedLabourIds->all()) {
            return;
        }

        throw ValidationException::withMessages([
            'details' => [
                'Labour cannot be added to or removed from an existing attendance sheet. Use Attendance Corrections to change labour composition.',
            ],
        ]);
    }

    /**
     * Update existing attendance rows without changing labour composition.
     *
     * Normal attendance editing may change attendance values only.
     * Adding or removing labour belongs to Attendance Corrections.
     *
     * @throws ValidationException
     */
    private function updateExistingDetails(
        LabourAttendance $attendance,
        array $details
    ): void {
        $existingDetails = $attendance
            ->details()
            ->where('is_active', true)
            ->get()
            ->keyBy(
                fn (LabourAttendanceDetail $detail): int =>
                    (int) $detail->labour_id
            );

        foreach ($details as $detail) {
            $labourId = (int) $detail['labour_id'];

            /** @var LabourAttendanceDetail|null $existingDetail */
            $existingDetail = $existingDetails->get($labourId);

            if (! $existingDetail) {
                throw ValidationException::withMessages([
                    'details' => [
                        "Labour #{$labourId} does not belong to this attendance sheet. Use Attendance Corrections to change labour composition.",
                    ],
                ]);
            }

            $otValues = $this->resolveAttendanceOtValues(
                $existingDetail->labour ?? Labour::findOrFail($labourId),
                $detail
            );

            $existingDetail->update([
                'attendance_status_id' =>
                    (int) $detail['attendance_status_id'],

                'working_status_id' =>
                    ! empty($detail['working_status_id'])
                        ? (int) $detail['working_status_id']
                        : null,

                'check_in_time' =>
                    $this->nullableTrim(
                        $detail['check_in_time'] ?? null
                    ),

                'check_out_time' =>
                    $this->nullableTrim(
                        $detail['check_out_time'] ?? null
                    ),

                'normal_hours' =>
                    $attendance->isAdditionalWork()
                        ? 0
                        : (float) (
                            $detail['normal_hours'] ?? 0
                        ),

                'ot_hours' => $otValues['ot_hours'],
                'ot_amount' => $otValues['ot_amount'],

                'attendance_source' =>
                    $detail['attendance_source']
                    ?? $existingDetail->attendance_source
                    ?? 'manual',

                'remarks' =>
                    $this->nullableTrim(
                        $detail['remarks'] ?? null
                    ),

                'is_active' => true,
                'updated_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Save attendance detail rows and classification snapshots.
     */
    private function persistDetails(
        LabourAttendance $attendance,
        array $details
    ): void {
        $this->assertLaboursAvailableForAttendance(
            $attendance,
            $details
        );

        $labourIds = collect($details)
            ->pluck('labour_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $labours = Labour::query()
            ->whereIn('id', $labourIds)
            ->get()
            ->keyBy('id');

        foreach ($details as $detail) {
            $labourId = (int) $detail['labour_id'];

            /** @var Labour|null $labour */
            $labour = $labours->get($labourId);

            if (! $labour) {
                continue;
            }

            $otValues = $this->resolveAttendanceOtValues(
                $labour,
                $detail
            );

            $snapshot =
                LabourAttendanceDetail::snapshotFromLabour(
                    $labour
                );

            LabourAttendanceDetail::create([
                'labour_attendance_id' => $attendance->id,
                'labour_id' => $labour->id,

                'attendance_status_id' =>
                    (int) $detail['attendance_status_id'],

                'working_status_id' =>
                    ! empty($detail['working_status_id'])
                        ? (int) $detail['working_status_id']
                        : null,

                ...$snapshot,

                'check_in_time' =>
                    $this->nullableTrim(
                        $detail['check_in_time'] ?? null
                    ),

                'check_out_time' =>
                    $this->nullableTrim(
                        $detail['check_out_time'] ?? null
                    ),

                'normal_hours' =>
                    (float) (
                        $detail['normal_hours'] ?? 0
                    ),

                'ot_hours' => $otValues['ot_hours'],
                'ot_amount' => $otValues['ot_amount'],

                'attendance_source' =>
                    $detail['attendance_source']
                    ?? 'manual',

                'remarks' =>
                    $this->nullableTrim(
                        $detail['remarks'] ?? null
                    ),

                'is_active' => true,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Prevent the same labour from being allocated to two projects
     * on the same attendance date.
     *
     * @throws ValidationException
     */
    private function assertLaboursAvailableForAttendance(
        LabourAttendance $attendance,
        array $details
    ): void {
        if ($attendance->isAdditionalWork()) {
            return;
        }

        $workingStatusIds =
            $this->workingAttendanceStatusIds();

        $requestedWorkingLabourIds = collect($details)
            ->filter(function (array $detail) use (
                $workingStatusIds
            ): bool {
                return in_array(
                    (int) ($detail['attendance_status_id'] ?? 0),
                    $workingStatusIds,
                    true
                );
            })
            ->pluck('labour_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        if ($requestedWorkingLabourIds->isEmpty()) {
            return;
        }

        $conflicts = LabourAttendanceDetail::query()
            ->select([
                'labour_attendance_details.labour_id',
                'labour_attendances.project_id',
            ])
            ->join(
                'labour_attendances',
                'labour_attendances.id',
                '=',
                'labour_attendance_details.labour_attendance_id'
            )
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
                'labour_attendances.is_active',
                true
            )
            ->where(
                'labour_attendance_details.is_active',
                true
            )
            ->whereNull(
                'labour_attendance_details.deleted_at'
            )
            ->whereNull(
                'labour_attendances.deleted_at'
            )
            ->whereIn(
                'labour_attendance_details.labour_id',
                $requestedWorkingLabourIds
            )
            ->whereIn(
                'labour_attendance_details.attendance_status_id',
                $workingStatusIds
            )
            ->get();

        if ($conflicts->isEmpty()) {
            return;
        }

        $labourNames = Labour::query()
            ->whereIn(
                'id',
                $conflicts->pluck('labour_id')->unique()
            )
            ->get()
            ->mapWithKeys(fn (Labour $labour): array => [
                $labour->id => $labour->full_name
                    ?: $labour->labour_code,
            ]);

        $projectNames = Project::query()
            ->whereIn(
                'id',
                $conflicts->pluck('project_id')->unique()
            )
            ->pluck('project_name', 'id');

        $messages = $conflicts
            ->map(function ($conflict) use (
                $labourNames,
                $projectNames
            ): string {
                $labourName = $labourNames[$conflict->labour_id]
                    ?? "Labour #{$conflict->labour_id}";

                $projectName = $projectNames[$conflict->project_id]
                    ?? "Project #{$conflict->project_id}";

                return "{$labourName} is already marked as working at {$projectName} for this date.";
            })
            ->unique()
            ->values()
            ->all();

        throw ValidationException::withMessages([
            'details' => $messages,
        ]);
    }

    /**
     * Attendance statuses that reserve a labourer for a project.
     *
     * Leave, Absent, Weekly Off, Holiday and Transferred do not
     * reserve a labourer in the daily project allocation pool.
     */
    private function workingAttendanceStatusIds(): array
    {
        $nonWorkingCodes = [
            'A',
            'ABSENT',
            'L',
            'LEAVE',
            'WO',
            'WEEKLY_OFF',
            'WEEKLY-OFF',
            'H',
            'HOLIDAY',
            'TR',
            'TRANSFERRED',
        ];

        return AttendanceStatus::query()
            ->active()
            ->where(
                'counts_as_present',
                true
            )
            ->whereNotIn(
                DB::raw('UPPER(code)'),
                $nonWorkingCodes
            )
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * Generate a sequential attendance number.
     *
     * Example:
     * LAT-202607-0001
     */
    private function generateAttendanceNumber(
        string $attendanceDate
    ): string {
        $date = \Carbon\Carbon::parse(
            $attendanceDate
        );

        $prefix = 'LAT-'
            . $date->format('Ym')
            . '-';

        $lastNumber = LabourAttendance::withTrashed()
            ->where(
                'attendance_number',
                'like',
                "{$prefix}%"
            )
            ->lockForUpdate()
            ->orderByDesc('attendance_number')
            ->value('attendance_number');

        $nextSequence = 1;

        if ($lastNumber) {
            $lastSequence = (int) Str::afterLast(
                $lastNumber,
                '-'
            );

            $nextSequence = $lastSequence + 1;
        }

        do {
            $attendanceNumber = $prefix
                . str_pad(
                    (string) $nextSequence,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

            $exists = LabourAttendance::withTrashed()
                ->where(
                    'attendance_number',
                    $attendanceNumber
                )
                ->exists();

            $nextSequence++;
        } while ($exists);

        return $attendanceNumber;
    }

    /**
 * Standard form options.
 */
private function getFormOptions(): array
{
    return [
        'projects' => ProjectAccess::availableProjects(),

        'shifts' => Shift::query()
            ->active()
            ->ordered()
            ->get(),

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
    ];
}

    /**
     * Relationships required for show pages and audit logging.
     */
    private function attendanceRelationships(): array
{
    return [
        'project',
        'shift',
        'recordedBy',
        'submittedBy',
        'approvedBy',
        'rejectedBy',
        'reopenedBy',
    ];
}

    /**
     * Build an audit-safe attendance snapshot.
     */
    private function auditValues(
        LabourAttendance $attendance
    ): array {
        return [
            'id' => $attendance->id,

            'attendance_number' =>
                $attendance->attendance_number,

            'project' =>
                $attendance->project?->project_name,

            'attendance_date' =>
                $attendance->attendance_date?->format(
                    'Y-m-d'
                ),

            'shift' =>
                $attendance->shift?->name,

            'attendance_type' =>
                $attendance->attendance_type
                ?? 'regular',

            'work_session_name' =>
                $attendance->work_session_name,

            'total_labours' =>
                $attendance->total_labours,

            'present_count' =>
                $attendance->present_count,

            'absent_count' =>
                $attendance->absent_count,

            'leave_count' =>
                $attendance->leave_count,

            'half_day_count' =>
                $attendance->half_day_count,

            'total_normal_hours' =>
                $attendance->total_normal_hours,

            'total_ot_hours' =>
                $attendance->total_ot_hours,

            'status' =>
                $attendance->status,

            'recorded_by' =>
                $attendance->recordedBy?->name,

            'submitted_by' =>
                $attendance->submittedBy?->name,

            'submitted_at' =>
                $attendance->submitted_at?->format(
                    'Y-m-d H:i:s'
                ),

            'approved_by' =>
                $attendance->approvedBy?->name,

            'approved_at' =>
                $attendance->approved_at?->format(
                    'Y-m-d H:i:s'
                ),

            'rejected_by' =>
                $attendance->rejectedBy?->name,

            'rejected_at' =>
                $attendance->rejected_at?->format(
                    'Y-m-d H:i:s'
                ),

            'rejection_reason' =>
                $attendance->rejection_reason,

                'reopened_by' =>
    $attendance->reopenedBy?->name,

'reopened_at' =>
    $attendance->reopened_at?->format(
        'Y-m-d H:i:s'
    ),

'reopen_reason' =>
    $attendance->reopen_reason,

'revision_number' =>
    $attendance->revision_number,

            'remarks' =>
                $attendance->remarks,

            'is_active' =>
                $attendance->is_active,
        ];
    }


    /**
     * OT business rule:
     * OT Rate = Daily Wage Rate / 8.
     *
     * OT Amount is authoritative when supplied. Otherwise OT Amount is
     * calculated from OT Hours. Both values are persisted.
     */
    private function resolveAttendanceOtValues(
        Labour $labour,
        array $detail
    ): array {
        $dailyRate = (float) ($labour->current_daily_rate ?? 0);
        $otRate = $dailyRate > 0
            ? $dailyRate / 8
            : 0.0;

        $submittedHours = max(
            0,
            (float) ($detail['ot_hours'] ?? 0)
        );

        $hasAmount = array_key_exists('ot_amount', $detail)
            && $detail['ot_amount'] !== null
            && $detail['ot_amount'] !== '';

        if ($hasAmount) {
            $otAmount = max(
                0,
                (float) $detail['ot_amount']
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
     * Trim nullable text and convert empty strings to null.
     */
    private function nullableTrim(
        mixed $value
    ): ?string {
        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);

        return $value === ''
            ? null
            : $value;
    }

    private function syncEditableDraftDetails(
    LabourAttendance $attendance,
    array $details
): void {
    $this->assertLaboursAvailableForAttendance(
        $attendance,
        $details
    );

    $submittedDetails = collect($details)
        ->keyBy(
            fn (array $detail): int =>
                (int) $detail['labour_id']
        );

    $submittedLabourIds = $submittedDetails
        ->keys()
        ->map(fn ($id): int => (int) $id)
        ->values();

    $rowsToRemove = $attendance
        ->details()
        ->where('is_active', true)
        ->when(
            $submittedLabourIds->isNotEmpty(),
            fn ($query) =>
                $query->whereNotIn(
                    'labour_id',
                    $submittedLabourIds
                )
        )
        ->get();

    foreach ($rowsToRemove as $rowToRemove) {
        $rowToRemove->update([
            'is_active' => false,
            'updated_by' => auth()->id(),
        ]);

        $rowToRemove->delete();
    }

    if ($submittedDetails->isEmpty()) {
        return;
    }

    $labours = Labour::query()
        ->whereIn(
            'id',
            $submittedLabourIds
        )
        ->get()
        ->keyBy('id');

    foreach (
        $submittedDetails
        as $labourId => $detailData
    ) {
        $labourId = (int) $labourId;

        /** @var Labour|null $labour */
        $labour = $labours->get(
            $labourId
        );

        if (! $labour) {
            throw ValidationException::withMessages([
                'details' => [
                    "Labour #{$labourId} could not be found.",
                ],
            ]);
        }

        $attendanceDetail =
            LabourAttendanceDetail::withTrashed()
                ->where(
                    'labour_attendance_id',
                    $attendance->id
                )
                ->where(
                    'labour_id',
                    $labourId
                )
                ->first();

        $isNewOrRestored =
            ! $attendanceDetail
            || $attendanceDetail->trashed();

        if (! $attendanceDetail) {
            $attendanceDetail =
                new LabourAttendanceDetail();

            $attendanceDetail->labour_attendance_id =
                $attendance->id;

            $attendanceDetail->labour_id =
                $labourId;

            $attendanceDetail->created_by =
                auth()->id();
        } elseif ($attendanceDetail->trashed()) {
            $attendanceDetail->restore();
        }

        if ($isNewOrRestored) {
            $snapshot =
                LabourAttendanceDetail::snapshotFromLabour(
                    $labour
                );

            foreach (
                $snapshot
                as $column => $value
            ) {
                $attendanceDetail->{$column} =
                    $value;
            }
        }

        $otValues = $this->resolveAttendanceOtValues(
            $labour,
            $detailData
        );

        $attendanceDetail->attendance_status_id =
            (int) $detailData['attendance_status_id'];

        $attendanceDetail->working_status_id =
            ! empty($detailData['working_status_id'] ?? null)
                ? (int) $detailData['working_status_id']
                : null;

        $attendanceDetail->check_in_time =
            $this->nullableTrim(
                $detailData['check_in_time'] ?? null
            );

        $attendanceDetail->check_out_time =
            $this->nullableTrim(
                $detailData['check_out_time'] ?? null
            );

        $attendanceDetail->normal_hours =
            $attendance->isAdditionalWork()
                ? 0
                : (float) (
                    $detailData['normal_hours']
                    ?? 0
                );

        $attendanceDetail->ot_hours =
            $otValues['ot_hours'];

        $attendanceDetail->ot_amount =
            $otValues['ot_amount'];

        $attendanceDetail->attendance_source =
            $detailData['attendance_source']
            ?? $attendanceDetail->attendance_source
            ?? 'manual';

        $attendanceDetail->remarks =
            $this->nullableTrim(
                $detailData['remarks'] ?? null
            );

        $attendanceDetail->is_active =
            true;

        $attendanceDetail->updated_by =
            auth()->id();

        $attendanceDetail->save();
    }
}
}