<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\MachineryTool;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MachineryToolController extends Controller
{
    public function index(Request $request): View
    {
        $query = MachineryTool::query();

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($q) use ($search) {
                $q->where('machine_name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category')->toString());
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            }

            if ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $machineries = $query
            ->orderBy('sort_order')
            ->orderBy('category')
            ->orderBy('machine_name')
            ->paginate(25)
            ->withQueryString();

        $categories = MachineryTool::query()
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('machinery-tools.index', compact(
            'machineries',
            'categories'
        ));
    }

    public function create(): View
{
    $categories = MachineryTool::CATEGORIES;

    return view('machinery-tools.create', compact('categories'));
}

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateMachineryTool($request);

        // Ownership now belongs to the physical Equipment Register.
        // Keep the legacy column null for new master records.
        $validated['ownership_type'] = null;

        $machineryTool = MachineryTool::create($validated);

        AuditHelper::log(
            'Machinery Tools',
            'Created',
            $machineryTool->id,
            'Created machinery/equipment master: ' . $machineryTool->machine_name
        );

        return redirect()
            ->route('machinery-tools.index')
            ->with('success', 'Machinery / Equipment type created successfully.');
    }

    public function show(MachineryTool $machineryTool): View
    {
        return view('machinery-tools.show', compact('machineryTool'));
    }

    public function edit(MachineryTool $machineryTool): View
{
    $categories = MachineryTool::CATEGORIES;

    return view('machinery-tools.edit', compact(
        'machineryTool',
        'categories'
    ));
}

    public function update(
        Request $request,
        MachineryTool $machineryTool
    ): RedirectResponse {
        $validated = $this->validateMachineryTool(
            $request,
            $machineryTool
        );

        // Do not overwrite legacy ownership information if an old
        // production record happens to contain it.
        unset($validated['ownership_type']);

        $machineryTool->update($validated);

        AuditHelper::log(
            'Machinery Tools',
            'Updated',
            $machineryTool->id,
            'Updated machinery/equipment master: ' . $machineryTool->machine_name
        );

        return redirect()
            ->route('machinery-tools.index')
            ->with('success', 'Machinery / Equipment type updated successfully.');
    }

    public function destroy(
        MachineryTool $machineryTool
    ): RedirectResponse {
        /*
         * Do not physically delete master records.
         *
         * Existing DPR records may reference machinery_tools.id.
         * Deactivation preserves historical integrity.
         */
        $machineryTool->update([
            'is_active' => false,
        ]);

        AuditHelper::log(
            'Machinery Tools',
            'Deactivated',
            $machineryTool->id,
            'Deactivated machinery/equipment master: ' . $machineryTool->machine_name
        );

        return redirect()
            ->route('machinery-tools.index')
            ->with('success', 'Machinery / Equipment type deactivated successfully.');
    }

    private function validateMachineryTool(
    Request $request,
    ?MachineryTool $machineryTool = null
): array {
    $request->merge([
        'requires_operator' => $request->boolean('requires_operator'),
        'requires_meter_reading' => $request->boolean('requires_meter_reading'),
        'requires_fuel_tracking' => $request->boolean('requires_fuel_tracking'),
        'is_active' => $request->boolean('is_active'),
    ]);

    return $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('machinery_tools', 'code')
                    ->ignore($machineryTool?->id),
            ],

            'machine_name' => [
                'required',
                'string',
                'max:255',
            ],

            'category' => [
    'required',
    Rule::in(MachineryTool::CATEGORIES),
],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'unit' => [
                'required',
                'string',
                'max:50',
            ],

            'tracking_mode' => [
                'required',
                Rule::in([
                    'individual',
                    'pooled',
                ]),
            ],

            'meter_type' => [
                'required',
                Rule::in([
                    'none',
                    'hour_meter',
                    'odometer',
                ]),
            ],

            'fuel_type' => [
                'required',
                Rule::in([
                    'none',
                    'diesel',
                    'petrol',
                    'electric',
                    'battery',
                    'hybrid',
                    'other',
                ]),
            ],

            'requires_operator' => [
                'nullable',
                'boolean',
            ],

            'requires_meter_reading' => [
                'nullable',
                'boolean',
            ],

            'requires_fuel_tracking' => [
                'nullable',
                'boolean',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:999999',
            ],
        ]);
    }
}