<?php

namespace App\Http\Requests;

use App\Models\AttendanceStatus;
use App\Models\Labour;
use App\Models\WorkingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class BaseLabourAttendanceRequest extends FormRequest
{
    /**
     * Route middleware controls Labour Attendance permissions.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rules shared by Store and Update requests.
     */
    protected function commonRules(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | Attendance Header
            |--------------------------------------------------------------------------
            */

            'project_id' => [
                'required',
                'integer',
                'exists:projects,id',
            ],

            'attendance_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'shift_id' => [
                'nullable',
                'integer',
                'exists:shifts,id',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:3000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Attendance Detail Rows
            |--------------------------------------------------------------------------
            |
            | Rows without both Labour and Attendance Status are removed in
            | prepareForValidation(). This allows the UI to show the complete
            | available labour pool while saving only marked labour rows.
            |
            */

            'details' => [
                'required',
                'array',
                'min:1',
            ],

            'details.*.labour_id' => [
                'required',
                'integer',
                'distinct',
                'exists:labours,id',
            ],

            'details.*.attendance_status_id' => [
                'required',
                'integer',
                'exists:attendance_statuses,id',
            ],

            'details.*.working_status_id' => [
                'nullable',
                'integer',
                'exists:working_statuses,id',
            ],

            'details.*.check_in_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'details.*.check_out_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'details.*.normal_hours' => [
                'nullable',
                'numeric',
                'min:0',
                'max:24',
            ],

            'details.*.ot_hours' => [
                'nullable',
                'numeric',
                'min:0',
                'max:24',
            ],

            'details.*.attendance_source' => [
                'nullable',
                Rule::in([
                    'manual',
                    'mobile',
                    'biometric',
                    'import',
                    'system',
                ]),
            ],

            'details.*.remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Run validation shared by Store and Update requests.
     */
    protected function validateCommonBusinessRules(
        Validator $validator
    ): void {
        $this->validateActiveProject($validator);
        $this->validateActiveShift($validator);
        $this->validateAttendanceRows($validator);
        $this->validateDailyLabourAvailability($validator);
    }

    /**
     * Normalize submitted values before validation.
     *
     * Unmarked labour rows are discarded. Therefore, loading the complete
     * labour pool in the browser does not force the Engineer to submit an
     * attendance status for every company labourer.
     */
    protected function prepareForValidation(): void
    {
        $details = collect($this->input('details', []))
            ->filter(function (mixed $detail): bool {
                if (! is_array($detail)) {
                    return false;
                }

                return filled($detail['labour_id'] ?? null)
                    && filled(
                        $detail['attendance_status_id'] ?? null
                    );
            })
            ->map(function (array $detail): array {
                return [
                    ...$detail,

                    'working_status_id' =>
                        filled($detail['working_status_id'] ?? null)
                            ? (int) $detail['working_status_id']
                            : null,

                    'attendance_source' =>
                        $detail['attendance_source']
                        ?? 'manual',

                    'normal_hours' =>
                        $this->normalizeDecimal(
                            $detail['normal_hours'] ?? 0
                        ),

                    'ot_hours' =>
                        $this->normalizeDecimal(
                            $detail['ot_hours'] ?? 0
                        ),

                    'check_in_time' =>
                        $this->normalizeText(
                            $detail['check_in_time'] ?? null
                        ),

                    'check_out_time' =>
                        $this->normalizeText(
                            $detail['check_out_time'] ?? null
                        ),

                    'remarks' =>
                        $this->normalizeText(
                            $detail['remarks'] ?? null
                        ),
                ];
            })
            ->values()
            ->all();

        $this->merge([
            'shift_id' => $this->filled('shift_id')
                ? $this->input('shift_id')
                : null,

            'remarks' => $this->normalizeText(
                $this->input('remarks')
            ),

            'details' => $details,
        ]);
    }

    /**
     * Confirm that the selected project is active.
     */
    protected function validateActiveProject(
        Validator $validator
    ): void {
        $projectId = $this->integer('project_id');

        if (! $projectId) {
            return;
        }

        $projectIsActive = DB::table('projects')
            ->where('id', $projectId)
            ->where('status', 'Active')
            ->exists();

        if (! $projectIsActive) {
            $validator->errors()->add(
                'project_id',
                'The selected project is inactive.'
            );
        }
    }

    /**
     * Confirm that the selected shift is active.
     */
    protected function validateActiveShift(
        Validator $validator
    ): void {
        if (! $this->filled('shift_id')) {
            return;
        }

        $shiftIsActive = DB::table('shifts')
            ->where('id', $this->integer('shift_id'))
            ->where('is_active', true)
            ->exists();

        if (! $shiftIsActive) {
            $validator->errors()->add(
                'shift_id',
                'The selected shift is inactive.'
            );
        }
    }

    /**
     * Validate submitted labour rows in batches.
     */
    protected function validateAttendanceRows(
        Validator $validator
    ): void {
        $details = $this->input('details', []);

        if (! is_array($details)) {
            return;
        }

        $labourIds = collect($details)
            ->pluck('labour_id')
            ->filter()
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        $attendanceStatusIds = collect($details)
            ->pluck('attendance_status_id')
            ->filter()
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        $workingStatusIds = collect($details)
            ->pluck('working_status_id')
            ->filter()
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        $labours = Labour::query()
            ->withTrashed()
            ->whereIn('id', $labourIds)
            ->get()
            ->keyBy('id');

        $attendanceStatuses = AttendanceStatus::query()
            ->whereIn('id', $attendanceStatusIds)
            ->get()
            ->keyBy('id');

        $workingStatuses = WorkingStatus::query()
            ->whereIn('id', $workingStatusIds)
            ->get()
            ->keyBy('id');

        foreach ($details as $index => $detail) {
            if (! is_array($detail)) {
                continue;
            }

            $labourId = (int) (
                $detail['labour_id'] ?? 0
            );

            $attendanceStatusId = (int) (
                $detail['attendance_status_id'] ?? 0
            );

            $workingStatusId = filled(
                $detail['working_status_id'] ?? null
            )
                ? (int) $detail['working_status_id']
                : null;

            if (! $labourId || ! $attendanceStatusId) {
                continue;
            }

            /** @var Labour|null $labour */
            $labour = $labours->get($labourId);

            /** @var AttendanceStatus|null $attendanceStatus */
            $attendanceStatus =
                $attendanceStatuses->get(
                    $attendanceStatusId
                );

            /** @var WorkingStatus|null $workingStatus */
            $workingStatus = $workingStatusId
                ? $workingStatuses->get($workingStatusId)
                : null;

            $this->validateLabourRecord(
                $validator,
                $labour,
                $index
            );

            $this->validateAttendanceStatusRecord(
                $validator,
                $attendanceStatus,
                $index
            );

            $this->validateWorkingStatusRecord(
                $validator,
                $workingStatus,
                $workingStatusId,
                $index
            );

            if (! $labour || ! $attendanceStatus) {
                continue;
            }

            $this->validateHoursAgainstStatus(
                $validator,
                $attendanceStatus,
                $detail,
                $index
            );

            $this->validateAttendanceTimes(
                $validator,
                $attendanceStatus,
                $detail,
                $index
            );

            $this->validateWorkingStatusAgainstAttendance(
                $validator,
                $attendanceStatus,
                $workingStatus,
                $detail,
                $index
            );
        }
    }

    /**
     * Confirm that the labour profile is available for attendance.
     *
     * Permanent current_project_id assignment is intentionally not checked.
     * Daily project allocation is controlled by Labour Attendance.
     */
    protected function validateLabourRecord(
        Validator $validator,
        ?Labour $labour,
        int|string $index
    ): void {
        $field = "details.{$index}.labour_id";

        if (! $labour || $labour->trashed()) {
            $validator->errors()->add(
                $field,
                'The selected labour profile is unavailable.'
            );

            return;
        }

        if (! $labour->is_active) {
            $validator->errors()->add(
                $field,
                "The labour profile '{$labour->full_name}' is inactive."
            );
        }

        if (
            in_array(
                $labour->employment_status,
                ['exited', 'suspended'],
                true
            )
        ) {
            $displayStatus = ucfirst(
                str_replace(
                    '_',
                    ' ',
                    (string) $labour->employment_status
                )
            );

            $validator->errors()->add(
                $field,
                "Attendance cannot be recorded for '{$labour->full_name}' because the employment status is {$displayStatus}."
            );
        }
    }

    /**
     * Confirm that the Attendance Status record is active.
     */
    protected function validateAttendanceStatusRecord(
        Validator $validator,
        ?AttendanceStatus $attendanceStatus,
        int|string $index
    ): void {
        if (! $attendanceStatus) {
            return;
        }

        if (! $attendanceStatus->is_active) {
            $validator->errors()->add(
                "details.{$index}.attendance_status_id",
                "The attendance status '{$attendanceStatus->name}' is inactive."
            );
        }
    }

    /**
     * Confirm that the selected Working Status exists and is active.
     */
    protected function validateWorkingStatusRecord(
        Validator $validator,
        ?WorkingStatus $workingStatus,
        ?int $workingStatusId,
        int|string $index
    ): void {
        if (! $workingStatusId || ! $workingStatus) {
            return;
        }

        if (! $workingStatus->is_active) {
            $validator->errors()->add(
                "details.{$index}.working_status_id",
                "The working status '{$workingStatus->name}' is inactive."
            );
        }
    }

    /**
     * Validate Normal and OT hours against the master configuration.
     */
    protected function validateHoursAgainstStatus(
        Validator $validator,
        AttendanceStatus $attendanceStatus,
        array $detail,
        int|string $index
    ): void {
        $normalHours = (float) (
            $detail['normal_hours'] ?? 0
        );

        $otHours = (float) (
            $detail['ot_hours'] ?? 0
        );

        if (
            ! $attendanceStatus->allows_normal_hours
            && $normalHours > 0
        ) {
            $validator->errors()->add(
                "details.{$index}.normal_hours",
                "Normal hours are not allowed for the attendance status '{$attendanceStatus->name}'."
            );
        }

        if (
            ! $attendanceStatus->allows_ot_hours
            && $otHours > 0
        ) {
            $validator->errors()->add(
                "details.{$index}.ot_hours",
                "OT hours are not allowed for the attendance status '{$attendanceStatus->name}'."
            );
        }

        if (
            ! $this->statusAllocatesLabour(
                $attendanceStatus
            )
            && ($normalHours > 0 || $otHours > 0)
        ) {
            $validator->errors()->add(
                "details.{$index}.normal_hours",
                "Normal and OT hours must be zero for '{$attendanceStatus->name}'."
            );
        }

        if (($normalHours + $otHours) > 24) {
            $validator->errors()->add(
                "details.{$index}.ot_hours",
                'The combined Normal and OT hours cannot exceed 24 hours.'
            );
        }
    }

    /**
     * Validate check-in and check-out behaviour.
     */
    protected function validateAttendanceTimes(
        Validator $validator,
        AttendanceStatus $attendanceStatus,
        array $detail,
        int|string $index
    ): void {
        $checkInTime = $detail['check_in_time'] ?? null;
        $checkOutTime = $detail['check_out_time'] ?? null;

        if (
            ! $this->statusAllocatesLabour(
                $attendanceStatus
            )
            && ($checkInTime || $checkOutTime)
        ) {
            $validator->errors()->add(
                "details.{$index}.check_in_time",
                "Check-in and check-out times are not allowed for '{$attendanceStatus->name}'."
            );
        }

        if (
            filled($checkOutTime)
            && blank($checkInTime)
        ) {
            $validator->errors()->add(
                "details.{$index}.check_in_time",
                'Check-in time is required when Check-out time is entered.'
            );
        }
    }

    /**
     * Keep Attendance Status and Working Status separate but compatible.
     */
    protected function validateWorkingStatusAgainstAttendance(
        Validator $validator,
        AttendanceStatus $attendanceStatus,
        ?WorkingStatus $workingStatus,
        array $detail,
        int|string $index
    ): void {
        $workingStatusId = $detail['working_status_id'] ?? null;
        $requiresWorkingStatus = (bool) (
            $attendanceStatus->requires_working_status
            ?? false
        );

        if ($requiresWorkingStatus && blank($workingStatusId)) {
            $validator->errors()->add(
                "details.{$index}.working_status_id",
                "Working Status is required for '{$attendanceStatus->name}'."
            );

            return;
        }

        if (
            ! $this->statusAllocatesLabour($attendanceStatus)
            && filled($workingStatusId)
        ) {
            $validator->errors()->add(
                "details.{$index}.working_status_id",
                "Working Status is not allowed for '{$attendanceStatus->name}'."
            );

            return;
        }

        if (
            $workingStatus
            && $workingStatus->requires_reason
            && blank($detail['remarks'] ?? null)
        ) {
            $validator->errors()->add(
                "details.{$index}.remarks",
                "Remarks are required when Working Status is '{$workingStatus->name}'."
            );
        }
    }

    /**
     * Prevent a working labourer from being allocated to more than
     * one project on the same attendance date.
     *
     * The Attendance Status Master is the source of truth:
     * - counts_as_present = true, or
     * - allows_normal_hours = true, or
     * - allows_ot_hours = true
     *
     * Any status matching one of those conditions reserves labour.
     */
    protected function validateDailyLabourAvailability(
        Validator $validator
    ): void {
        if (
            ! $this->filled('project_id')
            || ! $this->filled('attendance_date')
        ) {
            return;
        }

        $details = collect(
            $this->input('details', [])
        );

        if ($details->isEmpty()) {
            return;
        }

        $attendanceStatusIds = $details
            ->pluck('attendance_status_id')
            ->filter()
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        $attendanceStatuses = AttendanceStatus::query()
            ->whereIn('id', $attendanceStatusIds)
            ->get()
            ->keyBy('id');

        $workingRows = $details
            ->filter(function (
                mixed $detail
            ) use ($attendanceStatuses): bool {
                if (! is_array($detail)) {
                    return false;
                }

                $attendanceStatus =
                    $attendanceStatuses->get(
                        (int) (
                            $detail['attendance_status_id']
                            ?? 0
                        )
                    );

                return $attendanceStatus
                    && $this->statusAllocatesLabour(
                        $attendanceStatus
                    );
            })
            ->values();

        if ($workingRows->isEmpty()) {
            return;
        }

        $workingLabourIds = $workingRows
            ->pluck('labour_id')
            ->filter()
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        $conflictQuery = DB::table(
            'labour_attendance_details as detail'
        )
            ->join(
                'labour_attendances as attendance',
                'attendance.id',
                '=',
                'detail.labour_attendance_id'
            )
            ->join(
                'attendance_statuses as attendance_status',
                'attendance_status.id',
                '=',
                'detail.attendance_status_id'
            )
            ->join(
                'labours as labour',
                'labour.id',
                '=',
                'detail.labour_id'
            )
            ->join(
                'projects as project',
                'project.id',
                '=',
                'attendance.project_id'
            )
            ->whereNull('detail.deleted_at')
            ->whereNull('attendance.deleted_at')
            ->where('detail.is_active', true)
            ->where('attendance.is_active', true)
            ->whereDate(
                'attendance.attendance_date',
                $this->input('attendance_date')
            )
            ->where(
                'attendance.project_id',
                '!=',
                $this->integer('project_id')
            )
            ->whereIn(
                'detail.labour_id',
                $workingLabourIds
            )
            ->where(function ($query): void {
                $query
                    ->where(
                        'attendance_status.counts_as_present',
                        true
                    )
                    ->orWhere(
                        'attendance_status.allows_normal_hours',
                        true
                    )
                    ->orWhere(
                        'attendance_status.allows_ot_hours',
                        true
                    );
            });

        $currentAttendanceId =
            $this->currentAttendanceId();

        if ($currentAttendanceId) {
            $conflictQuery->where(
                'attendance.id',
                '!=',
                $currentAttendanceId
            );
        }

        $conflicts = $conflictQuery
            ->select([
                'detail.labour_id',
                'labour.full_name as labour_name',
                'project.project_name',
            ])
            ->get()
            ->keyBy('labour_id');

        if ($conflicts->isEmpty()) {
            return;
        }

        foreach ($workingRows as $index => $detail) {
            $labourId = (int) (
                $detail['labour_id'] ?? 0
            );

            $conflict = $conflicts->get(
                $labourId
            );

            if (! $conflict) {
                continue;
            }

            $validator->errors()->add(
                "details.{$index}.labour_id",
                "'{$conflict->labour_name}' is already marked as working at '{$conflict->project_name}' for the selected date."
            );
        }
    }

    /**
     * Determine whether an Attendance Status reserves labour
     * for a project on the selected date.
     */
    protected function statusAllocatesLabour(
        AttendanceStatus $attendanceStatus
    ): bool {
        return (bool) $attendanceStatus->counts_as_present
            || (bool) $attendanceStatus->allows_normal_hours
            || (bool) $attendanceStatus->allows_ot_hours;
    }

    /**
     * Store requests return null. Update requests override this method.
     */
    protected function currentAttendanceId(): ?int
    {
        return null;
    }

    /**
     * Normalize nullable text.
     */
    protected function normalizeText(
        mixed $value
    ): mixed {
        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);

        return $value === ''
            ? null
            : $value;
    }

    /**
     * Normalize decimal input.
     */
    protected function normalizeDecimal(
        mixed $value
    ): mixed {
        if ($value === null || $value === '') {
            return 0;
        }

        return $value;
    }

    /**
     * Custom validation messages shared by Store and Update.
     */
    public function messages(): array
    {
        return [
            'project_id.required' =>
                'Project is required.',

            'attendance_date.required' =>
                'Attendance Date is required.',

            'attendance_date.before_or_equal' =>
                'Attendance cannot be recorded for a future date.',

            'details.required' =>
                'Mark attendance for at least one labourer.',

            'details.min' =>
                'Mark attendance for at least one labourer.',

            'details.*.labour_id.required' =>
                'Select a labour profile for every marked attendance row.',

            'details.*.labour_id.distinct' =>
                'The same labour profile cannot appear more than once in an attendance sheet.',

            'details.*.attendance_status_id.required' =>
                'Select an Attendance Status for every marked labour row.',

            'details.*.working_status_id.exists' =>
                'Select a valid Working Status.',

            'details.*.check_in_time.date_format' =>
                'Check-in time must use the HH:MM format.',

            'details.*.check_out_time.date_format' =>
                'Check-out time must use the HH:MM format.',
        ];
    }
}
