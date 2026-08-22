<?php

namespace App\Http\Requests;

use App\Models\AttendanceStatus;
use App\Models\Labour;
use App\Models\LabourAttendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateLabourAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
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
             * Draft attendance may intentionally contain zero labour rows.
             * The minimum-labour rule belongs to Submit, not Draft saving.
             */
            'details' => [
                'nullable',
                'array',
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

            'details.*.ot_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99999999.99',
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

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $this->validateEditableAttendance($validator);
               // $this->validateActiveProject($validator);
                $this->validateActiveShift($validator);
                $this->validateDuplicateAttendanceSheet($validator);
                $this->validateAttendanceRows($validator);
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $details = collect($this->input('details', []))
            ->filter(
                fn (mixed $detail): bool =>
                    is_array($detail)
                    && ! empty($detail['labour_id'])
                    && ! empty($detail['attendance_status_id'])
            )
            ->map(function (array $detail): array {
                return [
                    ...$detail,

                    'attendance_source' =>
                        $detail['attendance_source']
                        ?? 'manual',

                    'working_status_id' =>
                        ! empty($detail['working_status_id'])
                            ? $detail['working_status_id']
                            : null,

                    'normal_hours' =>
                        $this->normalizeDecimal(
                            $detail['normal_hours'] ?? 0
                        ),

                    'ot_hours' =>
                        $this->normalizeDecimal(
                            $detail['ot_hours'] ?? 0
                        ),

                    'ot_amount' =>
                        $this->normalizeNullableDecimal(
                            $detail['ot_amount'] ?? null
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
            'shift_id' =>
                $this->filled('shift_id')
                    ? $this->input('shift_id')
                    : null,

            'remarks' =>
                $this->normalizeText(
                    $this->input('remarks')
                ),

            'details' => $details,
        ]);
    }

    private function validateEditableAttendance(
        Validator $validator
    ): void {
        $attendance =
            $this->labourAttendance();

        if (! $attendance) {
            return;
        }

        if (! $attendance->canBeEdited()) {
            $validator->errors()->add(
                'attendance_date',
                "This attendance sheet cannot be edited because its current status is {$attendance->display_status}."
            );
        }
    }

    private function validateActiveProject(
        Validator $validator
    ): void {
        $projectId =
            $this->integer('project_id');

        if (! $projectId) {
            return;
        }

        $projectIsActive = DB::table('projects')
            ->where('id', $projectId)
            ->where('status', true)
            ->exists();

        if (! $projectIsActive) {
            $validator->errors()->add(
                'project_id',
                'The selected project is inactive.'
            );
        }
    }

    private function validateActiveShift(
        Validator $validator
    ): void {
        if (! $this->filled('shift_id')) {
            return;
        }

        $shiftIsActive = DB::table('shifts')
            ->where(
                'id',
                $this->integer('shift_id')
            )
            ->where('is_active', true)
            ->exists();

        if (! $shiftIsActive) {
            $validator->errors()->add(
                'shift_id',
                'The selected shift is inactive.'
            );
        }
    }

    private function validateDuplicateAttendanceSheet(
        Validator $validator
    ): void {
        if (
            ! $this->filled('project_id')
            || ! $this->filled('attendance_date')
        ) {
            return;
        }

        $attendance =
            $this->labourAttendance();

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

        if ($attendance) {
            $query->where(
                'id',
                '!=',
                $attendance->id
            );
        }

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
                'Another Labour Attendance sheet already exists for the selected project, date, and shift.'
            );
        }
    }

    private function validateAttendanceRows(
        Validator $validator
    ): void {
        $details =
            $this->input('details', []);

        if (! is_array($details)) {
            return;
        }

        $projectId =
            $this->integer('project_id');

        foreach (
            $details as $index => $detail
        ) {
            if (! is_array($detail)) {
                continue;
            }

            $labourId =
                isset($detail['labour_id'])
                    ? (int) $detail['labour_id']
                    : null;

            $attendanceStatusId =
                isset($detail['attendance_status_id'])
                    ? (int) $detail['attendance_status_id']
                    : null;

            if (! $labourId || ! $attendanceStatusId) {
                continue;
            }

            $labour = Labour::query()
                ->withTrashed()
                ->find($labourId);

            $attendanceStatus =
                AttendanceStatus::query()
                    ->find($attendanceStatusId);

            $this->validateLabourRecord(
                $validator,
                $labour,
                $projectId,
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

    private function validateLabourRecord(
        Validator $validator,
        ?Labour $labour,
        int $projectId,
        int|string $index
    ): void {
        $field =
            "details.{$index}.labour_id";

        if (! $labour || $labour->trashed()) {
            $validator->errors()->add(
                $field,
                'The selected labour profile is unavailable.'
            );

            return;
        }

        if (
            in_array(
                $labour->employment_status,
                [
                    'exited',
                    'suspended',
                ],
                true
            )
            && ! $this->isExistingAttendanceLabour(
                $labour->id
            )
        ) {
            $validator->errors()->add(
                $field,
                "Attendance cannot be newly recorded for '{$labour->full_name}' because the employment status is "
                . ucfirst(
                    str_replace(
                        '_',
                        ' ',
                        (string) $labour->employment_status
                    )
                )
                . '.'
            );
        }
    }

    private function validateAttendanceStatusRecord(
        Validator $validator,
        ?AttendanceStatus $attendanceStatus,
        int|string $index
    ): void {
        $field =
            "details.{$index}.attendance_status_id";

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

    private function validateHoursAgainstStatus(
        Validator $validator,
        AttendanceStatus $attendanceStatus,
        array $detail,
        int|string $index
    ): void {
        $normalHours =
            (float) (
                $detail['normal_hours'] ?? 0
            );

        $otHours =
            (float) (
                $detail['ot_hours'] ?? 0
            );

        $otAmount =
            $detail['ot_amount'] !== null
                ? (float) $detail['ot_amount']
                : 0.0;

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
            && ($otHours > 0 || $otAmount > 0)
        ) {
            $validator->errors()->add(
                "details.{$index}.ot_hours",
                "OT hours are not allowed for the attendance status '{$attendanceStatus->name}'."
            );
        }

        if (
            $attendanceStatus->counts_as_absent
            && (
                $normalHours > 0
                || $otHours > 0
                || $otAmount > 0
            )
        ) {
            $validator->errors()->add(
                "details.{$index}.normal_hours",
                'Normal and OT hours must be zero for an absent attendance status.'
            );
        }

        if (
            ($normalHours + $otHours)
            > 24
        ) {
            $validator->errors()->add(
                "details.{$index}.ot_hours",
                'The combined Normal and OT hours cannot exceed 24 hours.'
            );
        }
    }

    private function validateAttendanceTimes(
        Validator $validator,
        AttendanceStatus $attendanceStatus,
        array $detail,
        int|string $index
    ): void {
        $checkInTime =
            $detail['check_in_time'] ?? null;

        $checkOutTime =
            $detail['check_out_time'] ?? null;

        if (
            $attendanceStatus->counts_as_absent
            && (
                $checkInTime
                || $checkOutTime
            )
        ) {
            $validator->errors()->add(
                "details.{$index}.check_in_time",
                'Check-in and check-out times are not allowed for an absent attendance status.'
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

    private function isExistingAttendanceLabour(
        int $labourId
    ): bool {
        $attendance =
            $this->labourAttendance();

        if (! $attendance) {
            return false;
        }

        return $attendance
            ->details()
            ->withTrashed()
            ->where(
                'labour_id',
                $labourId
            )
            ->exists();
    }

    public function labourAttendance(): ?LabourAttendance
    {
        $attendance =
            $this->route('labourAttendance')
            ?? $this->route('labour_attendance');

        return $attendance
            instanceof LabourAttendance
                ? $attendance
                : null;
    }

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

    private function normalizeDecimal(
        mixed $value
    ): mixed {
        if (
            $value === null
            || $value === ''
        ) {
            return 0;
        }

        return $value;
    }


    private function normalizeNullableDecimal(
        mixed $value
    ): mixed {
        if ($value === null || $value === '') {
            return null;
        }

        return $value;
    }

    public function messages(): array
    {
        return [
            'project_id.required' =>
                'Project is required.',

            'attendance_date.required' =>
                'Attendance Date is required.',

            'attendance_date.before_or_equal' =>
                'Attendance cannot be recorded for a future date.',

            'details.array' =>
                'Attendance labour rows are invalid.',

            'details.*.labour_id.required' =>
                'Select a labour profile for every attendance row.',

            'details.*.labour_id.distinct' =>
                'The same labour profile cannot appear more than once in an attendance sheet.',

            'details.*.attendance_status_id.required' =>
                'Select an Attendance Status for every retained labour row.',

            'details.*.check_in_time.date_format' =>
                'Check-in time must use the HH:MM format.',

            'details.*.check_out_time.date_format' =>
                'Check-out time must use the HH:MM format.',
        ];
    }
}
