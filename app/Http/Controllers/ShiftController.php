<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ShiftController extends Controller
{
    /**
     * Display the Shift Master list.
     */
    public function index(Request $request): View
    {
        $query = Shift::query();

        if ($request->filled('search')) {
            $search = trim(
                $request->string('search')->toString()
            );

            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('crosses_midnight')) {
            if ($request->crosses_midnight === 'yes') {
                $query->where('crosses_midnight', true);
            }

            if ($request->crosses_midnight === 'no') {
                $query->where('crosses_midnight', false);
            }
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            }

            if ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $shifts = $query
            ->ordered()
            ->paginate(
                config('rds.pagination.per_page', 15)
            )
            ->withQueryString();

        return view(
            'shifts.index',
            compact('shifts')
        );
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view('shifts.create');
    }

    /**
     * Store a newly created shift.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $this->validateShift(
            $request
        );

        $validated = $this->prepareValidatedData(
            $request,
            $validated
        );

        $validated['is_system'] = false;

        $shift = Shift::create($validated);

        AuditHelper::log(
            'Shift Master',
            'Created',
            Shift::class,
            $shift->id,
            "Shift '{$shift->name}' was created.",
            null,
            $shift->fresh()->toArray()
        );

        return redirect()
            ->route('shifts.index')
            ->with(
                'success',
                'Shift created successfully.'
            );
    }

    /**
     * Display the specified shift.
     */
    public function show(
        Shift $shift
    ): View {
        return view(
            'shifts.show',
            compact('shift')
        );
    }

    /**
     * Show the edit form.
     *
     * System shifts are editable, but their system-record flag
     * remains protected and cannot be changed from the form.
     */
    public function edit(
        Shift $shift
    ): View {
        return view(
            'shifts.edit',
            compact('shift')
        );
    }

    /**
     * Update the specified shift.
     *
     * System shifts may be edited, but:
     * - is_system cannot be changed;
     * - system shifts cannot be made inactive;
     * - system shift codes remain protected.
     */
    public function update(
        Request $request,
        Shift $shift
    ): RedirectResponse {
        $oldValues = $shift->toArray();

        $validated = $this->validateShift(
            $request,
            $shift
        );

        $validated = $this->prepareValidatedData(
            $request,
            $validated
        );

        unset($validated['is_system']);

        if ($shift->is_system) {
            /*
             * Protect the permanent system identity.
             * Timings, name, hours, overnight behaviour,
             * sort order and remarks remain editable.
             */
            $validated['code'] = $shift->code;
            $validated['is_active'] = true;
        }

        $shift->update($validated);
        $shift->refresh();

        AuditHelper::log(
            'Shift Master',
            'Updated',
            Shift::class,
            $shift->id,
            "Shift '{$shift->name}' was updated.",
            $oldValues,
            $shift->toArray()
        );

        return redirect()
            ->route('shifts.show', $shift)
            ->with(
                'success',
                $shift->is_system
                    ? 'System shift timings updated successfully.'
                    : 'Shift updated successfully.'
            );
    }

    /**
     * Activate or deactivate a shift.
     *
     * System shifts must always remain active.
     */
    public function toggleStatus(
        Shift $shift
    ): RedirectResponse {
        if (
            $shift->is_active
            && ! $shift->canBeDeactivated()
        ) {
            return back()->with(
                'error',
                'System shift records cannot be deactivated.'
            );
        }

        $oldValues = $shift->toArray();

        $shift->update([
            'is_active' => ! $shift->is_active,
        ]);

        $shift->refresh();

        $action = $shift->is_active
            ? 'Activated'
            : 'Deactivated';

        AuditHelper::log(
            'Shift Master',
            $action,
            Shift::class,
            $shift->id,
            "Shift '{$shift->name}' was {$action}.",
            $oldValues,
            $shift->toArray()
        );

        return back()->with(
            'success',
            $shift->is_active
                ? 'Shift activated successfully.'
                : 'Shift deactivated successfully.'
        );
    }

    /**
     * Validate Shift Master input.
     */
    private function validateShift(
        Request $request,
        ?Shift $shift = null
    ): array {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',

                Rule::unique('shifts', 'code')
                    ->ignore($shift?->id),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
            ],

            'end_time' => [
                'required',
                'date_format:H:i',
            ],

            'normal_hours' => [
                'required',
                'numeric',
                'min:0.25',
                'max:24',
            ],

            'grace_in_minutes' => [
                'required',
                'integer',
                'min:0',
                'max:240',
            ],

            'grace_out_minutes' => [
                'required',
                'integer',
                'min:0',
                'max:240',
            ],

            'ot_start_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'crosses_midnight' => [
                'nullable',
                'boolean',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
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

        $this->validateShiftTiming(
            request: $request,
            validated: $validated
        );

        return $validated;
    }

    /**
     * Validate the time range against overnight behaviour.
     *
     * @throws ValidationException
     */
    private function validateShiftTiming(
        Request $request,
        array $validated
    ): void {
        $startTime = $validated['start_time'];
        $endTime = $validated['end_time'];

        $crossesMidnight = $request->boolean(
            'crosses_midnight'
        );

        if (
            ! $crossesMidnight
            && $endTime <= $startTime
        ) {
            throw ValidationException::withMessages([
                'end_time' => [
                    'End Time must be later than Start Time for a same-day shift.',
                ],
            ]);
        }

        if (
            $crossesMidnight
            && $endTime > $startTime
        ) {
            throw ValidationException::withMessages([
                'end_time' => [
                    'For an overnight shift, End Time should be on the next day and normally earlier than Start Time.',
                ],
            ]);
        }

        $otStartTime = $validated['ot_start_time'] ?? null;

        if (
            ! $crossesMidnight
            && filled($otStartTime)
            && $otStartTime < $startTime
        ) {
            throw ValidationException::withMessages([
                'ot_start_time' => [
                    'OT Start Time cannot be earlier than the Shift Start Time for a same-day shift.',
                ],
            ]);
        }
    }

    /**
     * Normalize validated input before persistence.
     */
    private function prepareValidatedData(
        Request $request,
        array $validated
    ): array {
        $validated['code'] = strtoupper(
            trim($validated['code'])
        );

        $validated['name'] = trim(
            $validated['name']
        );

        $validated['start_time'] =
            $request->input('start_time');

        $validated['end_time'] =
            $request->input('end_time');

        $validated['normal_hours'] =
            (float) $validated['normal_hours'];

        $validated['grace_in_minutes'] =
            (int) $validated['grace_in_minutes'];

        $validated['grace_out_minutes'] =
            (int) $validated['grace_out_minutes'];

        $validated['ot_start_time'] =
            $request->filled('ot_start_time')
                ? $request->input('ot_start_time')
                : null;

        $validated['crosses_midnight'] =
            $request->boolean(
                'crosses_midnight'
            );

        $validated['sort_order'] =
            (int) $validated['sort_order'];

        $validated['is_active'] =
            $request->boolean('is_active');

        $validated['remarks'] =
            $this->nullableTrim(
                $validated['remarks'] ?? null
            );

        return $validated;
    }

    /**
     * Trim nullable strings and convert empty values to null.
     */
    private function nullableTrim(
        ?string $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === ''
            ? null
            : $value;
    }
}
