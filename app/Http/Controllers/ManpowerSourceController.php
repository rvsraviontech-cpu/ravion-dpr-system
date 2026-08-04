<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\ManpowerSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ManpowerSourceController extends Controller
{
    /**
     * Display the manpower source master list.
     */
    public function index(Request $request): View
    {
        $query = ManpowerSource::query();

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('contractor_requirement')) {
            if ($request->contractor_requirement === 'required') {
                $query->where('requires_contractor', true);
            }

            if ($request->contractor_requirement === 'not_required') {
                $query->where('requires_contractor', false);
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

        $manpowerSources = $query
            ->ordered()
            ->paginate(config('rds.pagination.per_page', 15))
            ->withQueryString();

        return view(
            'manpower-sources.index',
            compact('manpowerSources')
        );
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view('manpower-sources.create');
    }

    /**
     * Store a newly created manpower source.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateManpowerSource($request);

        $validated = $this->prepareValidatedData(
            $request,
            $validated
        );

        /*
         * Records created through the application interface
         * are always custom records.
         */
        $validated['is_system'] = false;

        $manpowerSource = ManpowerSource::create($validated);

        AuditHelper::log(
            'Manpower Source Master',
            'Created',
            ManpowerSource::class,
            $manpowerSource->id,
            "Manpower source '{$manpowerSource->name}' was created.",
            null,
            $manpowerSource->fresh()->toArray()
        );

        return redirect()
            ->route('manpower-sources.index')
            ->with(
                'success',
                'Manpower source created successfully.'
            );
    }

    /**
     * Display the specified manpower source.
     */
    public function show(
        ManpowerSource $manpowerSource
    ): View {
        return view(
            'manpower-sources.show',
            compact('manpowerSource')
        );
    }

    /**
     * Show the edit form.
     */
    public function edit(
    ManpowerSource $manpowerSource
): View|RedirectResponse {
    if ($manpowerSource->is_system) {
        return redirect()
            ->route('manpower-sources.show', $manpowerSource)
            ->with(
                'error',
                'System manpower source records are read-only.'
            );
    }

    return view(
        'manpower-sources.edit',
        compact('manpowerSource')
    );
}

    /**
     * Update the specified manpower source.
     */
    public function update(

    
        Request $request,
        ManpowerSource $manpowerSource
    ): RedirectResponse {
        if ($manpowerSource->is_system) {
    return redirect()
        ->route('manpower-sources.show', $manpowerSource)
        ->with(
            'error',
            'System manpower source records cannot be updated.'
        );
}
        $oldValues = $manpowerSource->toArray();

        $validated = $this->validateManpowerSource(
            $request,
            $manpowerSource
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

        $manpowerSource->update($validated);

        $manpowerSource->refresh();

        AuditHelper::log(
            'Manpower Source Master',
            'Updated',
            ManpowerSource::class,
            $manpowerSource->id,
            "Manpower source '{$manpowerSource->name}' was updated.",
            $oldValues,
            $manpowerSource->toArray()
        );

        return redirect()
            ->route('manpower-sources.show', $manpowerSource)
            ->with(
                'success',
                'Manpower source updated successfully.'
            );
    }

    /**
     * Activate or deactivate a manpower source.
     */
    public function toggleStatus(
        ManpowerSource $manpowerSource
    ): RedirectResponse {
        if (
            $manpowerSource->is_active
            && ! $manpowerSource->canBeDeactivated()
        ) {
            return back()->with(
                'error',
                'System manpower source records cannot be deactivated.'
            );
        }

        $oldValues = $manpowerSource->toArray();

        $manpowerSource->update([
            'is_active' => ! $manpowerSource->is_active,
        ]);

        $manpowerSource->refresh();

        $action = $manpowerSource->is_active
            ? 'Activated'
            : 'Deactivated';

        AuditHelper::log(
            'Manpower Source Master',
            $action,
            ManpowerSource::class,
            $manpowerSource->id,
            "Manpower source '{$manpowerSource->name}' was {$action}.",
            $oldValues,
            $manpowerSource->toArray()
        );

        return back()->with(
            'success',
            $manpowerSource->is_active
                ? 'Manpower source activated successfully.'
                : 'Manpower source deactivated successfully.'
        );
    }

    /**
     * Validate manpower source input.
     */
    private function validateManpowerSource(
        Request $request,
        ?ManpowerSource $manpowerSource = null
    ): array {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('manpower_sources', 'code')
                    ->ignore($manpowerSource?->id),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'requires_contractor' => [
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

        $validated['remarks'] = $this->nullableTrim(
            $validated['remarks'] ?? null
        );

        $validated['requires_contractor'] = $request->boolean(
            'requires_contractor'
        );

        $validated['sort_order'] = (int) $validated['sort_order'];

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