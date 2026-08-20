<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Department;
use App\Models\EmployeeDesignation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeDesignationController extends Controller
{
    /**
     * Display the Employee Designation Master.
     */
    public function index(Request $request): View
    {
        $query = EmployeeDesignation::query()
            ->with('department')
            ->withCount('users');

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhereHas('department', function ($departmentQuery) use ($search) {
                        $departmentQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('department_id')) {
            $query->where(
                'department_id',
                $request->integer('department_id')
            );
        }

        if ($request->filled('status')) {
            if ($request->string('status')->toString() === 'active') {
                $query->where('is_active', true);
            }

            if ($request->string('status')->toString() === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $designations = $query
            ->orderBy('department_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $departments = Department::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view(
            'employee-designations.index',
            compact('designations', 'departments')
        );
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        $departments = Department::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view(
            'employee-designations.create',
            compact('departments')
        );
    }

    /**
     * Store a new designation.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                'unique:employee_designations,code',
            ],
            'name' => [
                'required',
                'string',
                'max:150',
                'unique:employee_designations,name',
            ],
            'department_id' => [
                'required',
                Rule::exists('departments', 'id')->where(
                    fn ($query) => $query->where('is_active', true)
                ),
            ],
            'sort_order' => [
                'nullable',
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
                'max:5000',
            ],
        ]);

        $designation = EmployeeDesignation::create([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'department_id' => $validated['department_id'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'remarks' => $validated['remarks'] ?? null,
        ]);

        $designation->load('department');

        AuditHelper::log(
            'Employee Designations',
            'Created',
            'EmployeeDesignation',
            $designation->id,
            'Created employee designation: ' . $designation->name,
            null,
            [
                'code' => $designation->code,
                'name' => $designation->name,
                'department_id' => $designation->department_id,
                'department_name' => $designation->department?->name,
                'sort_order' => $designation->sort_order,
                'is_active' => $designation->is_active,
                'remarks' => $designation->remarks,
            ]
        );

        return redirect()
            ->route('employee-designations.show', $designation->id)
            ->with('success', 'Employee designation created successfully.');
    }

    /**
     * Display a designation.
     */
    public function show(int $id): View
    {
        $designation = EmployeeDesignation::query()
            ->with([
                'department',
                'users' => function ($query) {
                    $query->orderBy('name');
                },
            ])
            ->findOrFail($id);

        return view(
            'employee-designations.show',
            compact('designation')
        );
    }

    /**
     * Show the edit form.
     */
    public function edit(int $id): View
    {
        $designation = EmployeeDesignation::findOrFail($id);

        $departments = Department::query()
            ->where(function ($query) use ($designation) {
                $query
                    ->where('is_active', true)
                    ->orWhere('id', $designation->department_id);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view(
            'employee-designations.edit',
            compact('designation', 'departments')
        );
    }

    /**
     * Update a designation.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $designation = EmployeeDesignation::query()
            ->with('department')
            ->findOrFail($id);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('employee_designations', 'code')
                    ->ignore($designation->id),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('employee_designations', 'name')
                    ->ignore($designation->id),
            ],
            'department_id' => [
                'required',
                'exists:departments,id',
            ],
            'sort_order' => [
                'nullable',
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
                'max:5000',
            ],
        ]);

        $oldValues = [
            'code' => $designation->code,
            'name' => $designation->name,
            'department_id' => $designation->department_id,
            'department_name' => $designation->department?->name,
            'sort_order' => $designation->sort_order,
            'is_active' => $designation->is_active,
            'remarks' => $designation->remarks,
        ];

        $designation->update([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'department_id' => $validated['department_id'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'remarks' => $validated['remarks'] ?? null,
        ]);

        $designation->load('department');

        /*
        |--------------------------------------------------------------------------
        | Keep legacy user text fields synchronized during transition
        |--------------------------------------------------------------------------
        */
        $departmentName = $designation->department?->name;

        $designation->users()->update([
            'department_id' => $designation->department_id,
            'department' => $departmentName,
            'designation' => $designation->name,
        ]);

        AuditHelper::log(
            'Employee Designations',
            'Updated',
            'EmployeeDesignation',
            $designation->id,
            'Updated employee designation: ' . $designation->name,
            $oldValues,
            [
                'code' => $designation->code,
                'name' => $designation->name,
                'department_id' => $designation->department_id,
                'department_name' => $designation->department?->name,
                'sort_order' => $designation->sort_order,
                'is_active' => $designation->is_active,
                'remarks' => $designation->remarks,
            ]
        );

        return redirect()
            ->route('employee-designations.show', $designation->id)
            ->with('success', 'Employee designation updated successfully.');
    }

    /**
     * Activate a designation.
     */
    public function activate(int $id): RedirectResponse
    {
        $designation = EmployeeDesignation::findOrFail($id);

        if ($designation->is_active) {
            return back()->with(
                'success',
                'Employee designation is already active.'
            );
        }

        $designation->update([
            'is_active' => true,
        ]);

        AuditHelper::log(
            'Employee Designations',
            'Activated',
            'EmployeeDesignation',
            $designation->id,
            'Activated employee designation: ' . $designation->name,
            ['is_active' => false],
            ['is_active' => true]
        );

        return back()->with(
            'success',
            'Employee designation activated successfully.'
        );
    }

    /**
     * Deactivate a designation.
     *
     * Existing users remain linked, but the designation no longer appears
     * for new user assignment.
     */
    public function deactivate(int $id): RedirectResponse
    {
        $designation = EmployeeDesignation::findOrFail($id);

        if (! $designation->is_active) {
            return back()->with(
                'success',
                'Employee designation is already inactive.'
            );
        }

        $designation->update([
            'is_active' => false,
        ]);

        AuditHelper::log(
            'Employee Designations',
            'Deactivated',
            'EmployeeDesignation',
            $designation->id,
            'Deactivated employee designation: ' . $designation->name,
            ['is_active' => true],
            ['is_active' => false]
        );

        return back()->with(
            'success',
            'Employee designation deactivated successfully. Existing user links were preserved.'
        );
    }
}
