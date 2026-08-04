<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\WorkingStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkingStatusController extends Controller
{
    /**
     * Display the working status master list.
     */
    public function index(Request $request): View
    {
        $query = WorkingStatus::query();

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('idle_status')) {
            if ($request->idle_status === 'idle') {
                $query->where('counts_as_idle', true);
            }

            if ($request->idle_status === 'productive') {
                $query->where('counts_as_idle', false);
            }
        }

        if ($request->filled('reason_requirement')) {
            if ($request->reason_requirement === 'required') {
                $query->where('requires_reason', true);
            }

            if ($request->reason_requirement === 'not_required') {
                $query->where('requires_reason', false);
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

        $workingStatuses = $query
            ->ordered()
            ->paginate(config('rds.pagination.per_page', 15))
            ->withQueryString();

        return view(
            'working-statuses.index',
            compact('workingStatuses')
        );
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view('working-statuses.create');
    }

    /**
     * Store a newly created working status.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateWorkingStatus($request);

        $validated = $this->prepareValidatedData(
            $request,
            $validated
        );

        $validated['is_system'] = false;

        $workingStatus = WorkingStatus::create($validated);

        AuditHelper::log(
            'Working Status Master',
            'Created',
            WorkingStatus::class,
            $workingStatus->id,
            "Working status '{$workingStatus->name}' was created.",
            null,
            $workingStatus->fresh()->toArray()
        );

        return redirect()
            ->route('working-statuses.index')
            ->with(
                'success',
                'Working status created successfully.'
            );
    }

    /**
     * Display the specified working status.
     */
    public function show(
        WorkingStatus $workingStatus
    ): View {
        return view(
            'working-statuses.show',
            compact('workingStatus')
        );
    }

    /**
     * Show the edit form.
     */
    public function edit(
        WorkingStatus $workingStatus
    ): View|RedirectResponse {
        if ($workingStatus->is_system) {
            return redirect()
                ->route('working-statuses.show', $workingStatus)
                ->with(
                    'error',
                    'System working status records are read-only.'
                );
        }

        return view(
            'working-statuses.edit',
            compact('workingStatus')
        );
    }

    /**
     * Update the specified working status.
     */
    public function update(
        Request $request,
        WorkingStatus $workingStatus
    ): RedirectResponse {
        if ($workingStatus->is_system) {
            return redirect()
                ->route('working-statuses.show', $workingStatus)
                ->with(
                    'error',
                    'System working status records cannot be updated.'
                );
        }

        $oldValues = $workingStatus->toArray();

        $validated = $this->validateWorkingStatus(
            $request,
            $workingStatus
        );

        $validated = $this->prepareValidatedData(
            $request,
            $validated
        );

        unset($validated['is_system']);

        $workingStatus->update($validated);

        $workingStatus->refresh();

        AuditHelper::log(
            'Working Status Master',
            'Updated',
            WorkingStatus::class,
            $workingStatus->id,
            "Working status '{$workingStatus->name}' was updated.",
            $oldValues,
            $workingStatus->toArray()
        );

        return redirect()
            ->route('working-statuses.show', $workingStatus)
            ->with(
                'success',
                'Working status updated successfully.'
            );
    }

    /**
     * Activate or deactivate a working status.
     */
    public function toggleStatus(
        WorkingStatus $workingStatus
    ): RedirectResponse {
        if (
            $workingStatus->is_active
            && ! $workingStatus->canBeDeactivated()
        ) {
            return back()->with(
                'error',
                'System working status records cannot be deactivated.'
            );
        }

        $oldValues = $workingStatus->toArray();

        $workingStatus->update([
            'is_active' => ! $workingStatus->is_active,
        ]);

        $workingStatus->refresh();

        $action = $workingStatus->is_active
            ? 'Activated'
            : 'Deactivated';

        AuditHelper::log(
            'Working Status Master',
            $action,
            WorkingStatus::class,
            $workingStatus->id,
            "Working status '{$workingStatus->name}' was {$action}.",
            $oldValues,
            $workingStatus->toArray()
        );

        return back()->with(
            'success',
            $workingStatus->is_active
                ? 'Working status activated successfully.'
                : 'Working status deactivated successfully.'
        );
    }

    /**
     * Validate working status input.
     */
    private function validateWorkingStatus(
        Request $request,
        ?WorkingStatus $workingStatus = null
    ): array {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('working_statuses', 'code')
                    ->ignore($workingStatus?->id),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'counts_as_idle' => [
                'nullable',
                'boolean',
            ],

            'requires_reason' => [
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

        $validated['counts_as_idle'] = $request->boolean(
            'counts_as_idle'
        );

        $validated['requires_reason'] = $request->boolean(
            'requires_reason'
        );

        $validated['sort_order'] = (int) $validated['sort_order'];

        $validated['is_active'] = $request->boolean('is_active');

        $validated['remarks'] = $this->nullableTrim(
            $validated['remarks'] ?? null
        );

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