<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Gender;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GenderController extends Controller
{
    /**
     * Display the gender master list.
     */
    public function index(Request $request): View
    {
        $query = Gender::query();

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
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

        $genders = $query
            ->ordered()
            ->paginate(config('rds.pagination.per_page', 15))
            ->withQueryString();

        return view('genders.index', compact('genders'));
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view('genders.create');
    }

    /**
     * Store a newly created gender.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateGender($request);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['name'] = trim($validated['name']);
        $validated['remarks'] = $this->nullableTrim(
            $validated['remarks'] ?? null
        );

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_system'] = false;

        $gender = Gender::create($validated);

        AuditHelper::log(
            'Gender Master',
            'Created',
            Gender::class,
            $gender->id,
            "Gender '{$gender->name}' was created.",
            null,
            $gender->fresh()->toArray()
        );

        return redirect()
            ->route('genders.index')
            ->with('success', 'Gender created successfully.');
    }

    /**
     * Display the specified gender.
     */
    public function show(Gender $gender): View
    {
        return view('genders.show', compact('gender'));
    }

    /**
     * Show the edit form.
     */
    public function edit(Gender $gender): View
    {
        return view('genders.edit', compact('gender'));
    }

    /**
     * Update the specified gender.
     */
    public function update(
        Request $request,
        Gender $gender
    ): RedirectResponse {
        $oldValues = $gender->toArray();

        $validated = $this->validateGender($request, $gender);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['name'] = trim($validated['name']);
        $validated['remarks'] = $this->nullableTrim(
            $validated['remarks'] ?? null
        );

        $validated['is_active'] = $request->boolean('is_active');

        unset($validated['is_system']);

        $gender->update($validated);

        $gender->refresh();

        AuditHelper::log(
            'Gender Master',
            'Updated',
            Gender::class,
            $gender->id,
            "Gender '{$gender->name}' was updated.",
            $oldValues,
            $gender->toArray()
        );

        return redirect()
            ->route('genders.show', $gender)
            ->with('success', 'Gender updated successfully.');
    }

    /**
     * Activate or deactivate a gender.
     */
    public function toggleStatus(Gender $gender): RedirectResponse
    {
        if ($gender->is_active && ! $gender->canBeDeactivated()) {
            return back()->with(
                'error',
                'System gender records cannot be deactivated.'
            );
        }

        $oldValues = $gender->toArray();

        $gender->update([
            'is_active' => ! $gender->is_active,
        ]);

        $gender->refresh();

        $action = $gender->is_active
            ? 'Activated'
            : 'Deactivated';

        AuditHelper::log(
            'Gender Master',
            $action,
            Gender::class,
            $gender->id,
            "Gender '{$gender->name}' was {$action}.",
            $oldValues,
            $gender->toArray()
        );

        return back()->with(
            'success',
            $gender->is_active
                ? 'Gender activated successfully.'
                : 'Gender deactivated successfully.'
        );
    }

    /**
     * Validate gender master input.
     */
    private function validateGender(
        Request $request,
        ?Gender $gender = null
    ): array {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('genders', 'code')
                    ->ignore($gender?->id),
            ],

            'name' => [
                'required',
                'string',
                'max:100',
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
     * Trim nullable values and convert empty strings to null.
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