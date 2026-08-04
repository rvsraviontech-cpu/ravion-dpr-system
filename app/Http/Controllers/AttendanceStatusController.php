<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\AttendanceStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceStatusController extends Controller
{
    /**
     * Display the attendance status master list.
     */
    public function index(Request $request): View
    {
        $query = AttendanceStatus::query();

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('short_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            }

            if ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $attendanceStatuses = $query
            ->ordered()
            ->paginate(config('rds.pagination.per_page', 15))
            ->withQueryString();

        return view(
            'attendance-statuses.index',
            compact('attendanceStatuses')
        );
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view('attendance-statuses.create');
    }

    /**
     * Store a newly created attendance status.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAttendanceStatus($request);

        $validated = $this->prepareValidatedData(
            $request,
            $validated
        );

        /*
         * Records created through the UI are always custom records.
         * System records are created only through controlled seeders.
         */
        $validated['is_system'] = false;

        $attendanceStatus = AttendanceStatus::create($validated);

        AuditHelper::log(
            'Attendance Status Master',
            'Created',
            AttendanceStatus::class,
            $attendanceStatus->id,
            "Attendance status '{$attendanceStatus->name}' was created.",
            null,
            $attendanceStatus->fresh()->toArray()
        );

        return redirect()
            ->route('attendance-statuses.index')
            ->with(
                'success',
                'Attendance status created successfully.'
            );
    }

    /**
     * Display the specified attendance status.
     */
    public function show(
        AttendanceStatus $attendanceStatus
    ): View {
        return view(
            'attendance-statuses.show',
            compact('attendanceStatus')
        );
    }

    /**
     * Show the edit form.
     */
    public function edit(
        AttendanceStatus $attendanceStatus
    ): View {
        return view(
            'attendance-statuses.edit',
            compact('attendanceStatus')
        );
    }

    /**
     * Update the specified attendance status.
     */
    public function update(
        Request $request,
        AttendanceStatus $attendanceStatus
    ): RedirectResponse {
        $oldValues = $attendanceStatus->toArray();

        $validated = $this->validateAttendanceStatus(
            $request,
            $attendanceStatus
        );

        $validated = $this->prepareValidatedData(
            $request,
            $validated
        );

        /*
         * Never allow system/custom ownership to be changed
         * through the application form.
         */
        unset($validated['is_system']);

        $attendanceStatus->update($validated);

        $attendanceStatus->refresh();

        AuditHelper::log(
            'Attendance Status Master',
            'Updated',
            AttendanceStatus::class,
            $attendanceStatus->id,
            "Attendance status '{$attendanceStatus->name}' was updated.",
            $oldValues,
            $attendanceStatus->toArray()
        );

        return redirect()
            ->route('attendance-statuses.show', $attendanceStatus)
            ->with(
                'success',
                'Attendance status updated successfully.'
            );
    }

    /**
     * Activate or deactivate an attendance status.
     */
    public function toggleStatus(
        AttendanceStatus $attendanceStatus
    ): RedirectResponse {
        if (
            $attendanceStatus->is_active
            && ! $attendanceStatus->canBeDeactivated()
        ) {
            return back()->with(
                'error',
                'System attendance statuses cannot be deactivated.'
            );
        }

        $oldValues = $attendanceStatus->toArray();

        $attendanceStatus->update([
            'is_active' => ! $attendanceStatus->is_active,
        ]);

        $attendanceStatus->refresh();

        $action = $attendanceStatus->is_active
            ? 'Activated'
            : 'Deactivated';

        AuditHelper::log(
            'Attendance Status Master',
            $action,
            AttendanceStatus::class,
            $attendanceStatus->id,
            "Attendance status '{$attendanceStatus->name}' was {$action}.",
            $oldValues,
            $attendanceStatus->toArray()
        );

        return back()->with(
            'success',
            $attendanceStatus->is_active
                ? 'Attendance status activated successfully.'
                : 'Attendance status deactivated successfully.'
        );
    }

    /**
     * Validate attendance status input.
     */
    private function validateAttendanceStatus(
        Request $request,
        ?AttendanceStatus $attendanceStatus = null
    ): array {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('attendance_statuses', 'code')
                    ->ignore($attendanceStatus?->id),
            ],

            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'short_name' => [
                'nullable',
                'string',
                'max:50',
            ],

            'payable_factor' => [
                'required',
                'numeric',
                'min:0',
                'max:1',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
            ],

            'counts_as_present' => [
                'nullable',
                'boolean',
            ],

            'counts_as_absent' => [
                'nullable',
                'boolean',
            ],

            'allows_normal_hours' => [
                'nullable',
                'boolean',
            ],

            'allows_ot_hours' => [
                'nullable',
                'boolean',
            ],

            'requires_working_status' => [
                'nullable',
                'boolean',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);
    }

    /**
     * Normalize and prepare validated data before persistence.
     */
    private function prepareValidatedData(
        Request $request,
        array $validated
    ): array {
        $validated['code'] = strtoupper(
            trim($validated['code'])
        );

        $validated['name'] = trim($validated['name']);

        $validated['short_name'] = $this->nullableTrim(
            $validated['short_name'] ?? null
        );

        $validated['remarks'] = $this->nullableTrim(
            $validated['remarks'] ?? null
        );

        $validated['payable_factor'] = (float) $validated['payable_factor'];
        $validated['sort_order'] = (int) $validated['sort_order'];

        $validated['counts_as_present'] = $request->boolean(
            'counts_as_present'
        );

        $validated['counts_as_absent'] = $request->boolean(
            'counts_as_absent'
        );

        $validated['allows_normal_hours'] = $request->boolean(
            'allows_normal_hours'
        );

        $validated['allows_ot_hours'] = $request->boolean(
            'allows_ot_hours'
        );

        $validated['requires_working_status'] = $request->boolean(
            'requires_working_status'
        );

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    /**
     * Trim nullable strings and convert empty values to null.
     */
    private function nullableTrim(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}