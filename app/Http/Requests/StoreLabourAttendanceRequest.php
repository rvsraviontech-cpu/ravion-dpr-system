<?php

namespace App\Http\Requests;

use App\Models\AttendanceStatus;
use App\Models\Labour;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreLabourAttendanceRequest extends FormRequest
{
    /**
     * Statuses that do not allocate a labourer to a project for the day.
     *
     * These records do not remove the labourer from the availability pool.
     */
    private const NON_WORKING_STATUS_CODES = [
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
        'NOT_WORKED',
        'NOT-WORKED',
        'NW',
    ];

    /**
     * Route middleware controls Labour Attendance permissions.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a Labour Attendance sheet.
     */
    public function rules(): array
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
            | Unmarked labour rows are removed in prepareForValidation().
            | Only rows on which the Engineer selected a status are validated
            | and persisted.
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
     * Perform relational and business-rule validation.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $this->validateActiveProject($validator);
                $this->validateActiveShift($validator);
                $this->validateDuplicateAttendanceSheet($validator);
                $this->validateAttendanceRows($validator);
                $this->validateDailyLabourAvailability($validator);
            },
        ];
    }

    /**
     * Normalize submitted data before validation.
     *
     * Labour rows that the Engineer did not mark are intentionally removed.
     * This allows the screen to display the full available labour pool while
     * saving only the labour actually marked for this project.
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
    private function validateActiveProject(
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
    private function validateActiveShift(
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
     * Prevent duplicate attendance sheets for the same
     * Project, Attendance Date, and Shift.
     */
    private function validateDuplicateAttendanceSheet(
        Validator $validator
    ): void {
        if (
            ! $this->filled('project_id')
            || ! $this->filled('attendance_date')
        ) {
            return;
        }

        $query = DB::table('labour_attendances')
            ->whereNull('deleted_at')
            ->where(
                'project_id',
                $this->integer('project_id')
            )
            ->whereDate(
                'attendance_date',
                $this->input('attendance_date')
            );

        if ($this->filled('shift_id')) {
            $query->where(
                'shift_id',
                $this->integer('shift_id')
            );
        } else {
            $query->whereNull('shift_id');
        }

        if ($query->exists()) {
            $validator->errors()->add(
                'attendance_date',
                'A Labour Attendance sheet already exists for the selected project, date, and shift.'
            );
        }
    }

    /**
     * Validate each submitted labour attendance row.
     */
    private function validateAttendanceRows(
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

        $statusIds = collect($details)
            ->pluck('attendance_status_id')
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
            ->whereIn('id', $statusIds)
            ->get()
            ->keyBy('id');

        foreach ($details as $index => $detail) {
            if (! is_array($detail)) {
                continue;
            }

            $labourId = isset($detail['labour_id'])
                ? (int) $detail['labour_id']
                : 0;

            $attendanceStatusId =
                isset($detail['attendance_status_id'])
                    ? (int) $detail['attendance_status_id']
                    : 0;

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
        }
    }

    /**
     * Confirm that the labour profile can be used.
     *
     * A labourer is no longer required to have a permanent project
     * assignment. Daily project allocation is created by attendance.
     */
    private function validateLabourRecord(
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
            $validator->errors()->add(
                $field,
                "Attendance cannot be recorded for '{$labour->full_name}' because the employment status is "
                . ucfirst(
                    str_replace(
                        '_',
                        ' ',
                        $labour->employment_status
                    )
                )
                . '.'
            );
        }
    }

    /**
     * Confirm that the Attendance Status is active.
     */
    private function validateAttendanceStatusRecord(
        Validator $validator,
        ?AttendanceStatus $attendanceStatus,
        int|string $index
    ): void {
        $field = "details.{$index}.attendance_status_id";

        if (! $attendanceStatus) {
            return;
        }

        if (! $attendanceStatus->is_active) {
            $validator->errors()->add(
                $field,
                "The attendance status '{$attendanceStatus->name}' is inactive."
            );
        }
    }

    /**
     * Validate Normal and OT hours according to the selected
     * Attendance Status Master rules.
     */
    private function validateHoursAgainstStatus(
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
            $this->isNonWorkingStatus($attendanceStatus)
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
     * Validate time-entry behaviour according to the
     * selected Attendance Status.
     */
    private function validateAttendanceTimes(
        Validator $validator,
        AttendanceStatus $attendanceStatus,
        array $detail,
        int|string $index
    ): void {
        $checkInTime = $detail['check_in_time'] ?? null;
        $checkOutTime = $detail['check_out_time'] ?? null;

        if (
            $this->isNonWorkingStatus($attendanceStatus)
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
     * Prevent a working labourer from being allocated to
     * another project on the same attendance date.
     *
     * Non-working statuses such as Absent, Leave, Weekly Off,
     * Holiday, and Transferred do not reserve the labourer.
     */
    private function validateDailyLabourAvailability(
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

        $statusIds = $details
            ->pluck('attendance_status_id')
            ->filter()
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        $statuses = AttendanceStatus::query()
            ->whereIn('id', $statusIds)
            ->get()
            ->keyBy('id');

        $workingRows = $details
            ->filter(function (
                mixed $detail
            ) use ($statuses): bool {
                if (! is_array($detail)) {
                    return false;
                }

                $status = $statuses->get(
                    (int) (
                        $detail['attendance_status_id']
                        ?? 0
                    )
                );

                return $status
                    && ! $this->isNonWorkingStatus(
                        $status
                    );
            })
            ->values();

        if ($workingRows->isEmpty()) {
            return;
        }

        $workingLabourIds = $workingRows
            ->pluck('labour_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        $existingRows = DB::table(
            'labour_attendance_details as details'
        )
            ->join(
                'labour_attendances as attendance',
                'attendance.id',
                '=',
                'details.labour_attendance_id'
            )
            ->join(
                'attendance_statuses as status',
                'status.id',
                '=',
                'details.attendance_status_id'
            )
            ->join(
                'labours as labour',
                'labour.id',
                '=',
                'details.labour_id'
            )
            ->join(
                'projects as project',
                'project.id',
                '=',
                'attendance.project_id'
            )
            ->whereNull('details.deleted_at')
            ->whereNull('attendance.deleted_at')
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
                'details.labour_id',
                $workingLabourIds
            )
            ->select([
                'details.labour_id',
                'labour.full_name as labour_name',
                'project.project_name',
                'status.code as status_code',
                'status.counts_as_absent',
            ])
            ->get()
            ->filter(function (object $row): bool {
                return ! $this->isNonWorkingStatusCode(
                    $row->status_code,
                    (bool) $row->counts_as_absent
                );
            })
            ->keyBy('labour_id');

        if ($existingRows->isEmpty()) {
            return;
        }

        foreach ($workingRows as $index => $detail) {
            $labourId = (int) (
                $detail['labour_id'] ?? 0
            );

            $conflict = $existingRows->get(
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
     * Determine whether a status does not allocate labour
     * to a project for the day.
     */
    private function isNonWorkingStatus(
        AttendanceStatus $status
    ): bool {
        return $this->isNonWorkingStatusCode(
            $status->code,
            (bool) $status->counts_as_absent
        );
    }

    /**
     * Determine non-working behaviour from the master code.
     */
    private function isNonWorkingStatusCode(
        mixed $code,
        bool $countsAsAbsent = false
    ): bool {
        if ($countsAsAbsent) {
            return true;
        }

        return in_array(
            $this->normalizeStatusCode($code),
            self::NON_WORKING_STATUS_CODES,
            true
        );
    }

    /**
     * Normalize a status code for reliable comparisons.
     */
    private function normalizeStatusCode(
        mixed $code
    ): string {
        return strtoupper(
            trim((string) $code)
        );
    }

    /**
     * Normalize a nullable text field.
     */
    private function normalizeText(
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
     * Normalize a nullable decimal field.
     */
    private function normalizeDecimal(
        mixed $value
    ): mixed {
        if ($value === null || $value === '') {
            return 0;
        }

        return $value;
    }

    /**
     * Custom validation messages.
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
                'Select a labour profile for every attendance row.',

            'details.*.labour_id.distinct' =>
                'The same labour profile cannot appear more than once in an attendance sheet.',

            'details.*.attendance_status_id.required' =>
                'Select an Attendance Status for every marked labour row.',

            'details.*.check_in_time.date_format' =>
                'Check-in time must use the HH:MM format.',

            'details.*.check_out_time.date_format' =>
                'Check-out time must use the HH:MM format.',
        ];
    }
}
