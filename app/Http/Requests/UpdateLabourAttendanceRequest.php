<?php

namespace App\Http\Requests;

use App\Models\LabourAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class UpdateLabourAttendanceRequest extends BaseLabourAttendanceRequest
{
    /**
     * Validation rules for updating a Labour Attendance sheet.
     */
    public function rules(): array
    {
        return $this->commonRules();
    }

    /**
     * Perform update-specific and shared business-rule validation.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $this->validateEditableAttendance(
                    $validator
                );

                $this->validateDuplicateAttendanceSheet(
                    $validator
                );

                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $this->validateCommonBusinessRules(
                    $validator
                );
            },
        ];
    }

    /**
     * Confirm that the current attendance sheet may be edited.
     */
    private function validateEditableAttendance(
        Validator $validator
    ): void {
        $attendance = $this->labourAttendance();

        if (! $attendance) {
            $validator->errors()->add(
                'attendance_date',
                'The Labour Attendance sheet could not be resolved.'
            );

            return;
        }

        if ($attendance->canBeEdited()) {
            return;
        }

        $validator->errors()->add(
            'attendance_date',
            "This attendance sheet cannot be edited because its current status is {$attendance->display_status}."
        );
    }

    /**
     * Prevent another attendance sheet from using the same
     * Project, Attendance Date, and Shift combination.
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

        $attendance = $this->labourAttendance();

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

        if (! $query->exists()) {
            return;
        }

        $validator->errors()->add(
            'attendance_date',
            'Another Labour Attendance sheet already exists for the selected project, date, and shift.'
        );
    }

    /**
     * Resolve the current attendance sheet from route model binding.
     */
    public function labourAttendance(): ?LabourAttendance
    {
        $attendance = $this->route(
            'labourAttendance'
        ) ?? $this->route(
            'labour_attendance'
        );

        return $attendance instanceof LabourAttendance
            ? $attendance
            : null;
    }

    /**
     * Exclude the attendance sheet currently being edited from
     * the inherited daily labour allocation conflict query.
     */
    protected function currentAttendanceId(): ?int
    {
        return $this->labourAttendance()?->id;
    }
}
