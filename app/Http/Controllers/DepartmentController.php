<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    /**
     * Display the Department Master.
     */
    public function index(Request $request): View
    {
        $query = Department::query()
            ->withCount([
                'designations',
                'users',
            ]);

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->string('status')->toString() === 'active') {
                $query->where('is_active', true);
            }

            if ($request->string('status')->toString() === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $departments = $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('departments.index', compact('departments'));
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view('departments.create');
    }

    /**
     * Store a new department.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                'unique:departments,code',
            ],
            'name' => [
                'required',
                'string',
                'max:150',
                'unique:departments,name',
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

        $department = Department::create([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'remarks' => $validated['remarks'] ?? null,
        ]);

        AuditHelper::log(
            'Departments',
            'Created',
            'Department',
            $department->id,
            'Created department: ' . $department->name,
            null,
            [
                'code' => $department->code,
                'name' => $department->name,
                'sort_order' => $department->sort_order,
                'is_active' => $department->is_active,
                'remarks' => $department->remarks,
            ]
        );

        return redirect()
            ->route('departments.show', $department->id)
            ->with('success', 'Department created successfully.');
    }

    /**
     * Display a department.
     */
    public function show(int $id): View
    {
        $department = Department::query()
            ->with([
                'designations' => function ($query) {
                    $query
                        ->orderBy('sort_order')
                        ->orderBy('name');
                },
                'users' => function ($query) {
                    $query
                        ->orderBy('name');
                },
            ])
            ->findOrFail($id);

        return view('departments.show', compact('department'));
    }

    /**
     * Show the edit form.
     */
    public function edit(int $id): View
    {
        $department = Department::findOrFail($id);

        return view('departments.edit', compact('department'));
    }

    /**
     * Update a department.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $department = Department::findOrFail($id);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('departments', 'code')->ignore($department->id),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('departments', 'name')->ignore($department->id),
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
            'code' => $department->code,
            'name' => $department->name,
            'sort_order' => $department->sort_order,
            'is_active' => $department->is_active,
            'remarks' => $department->remarks,
        ];

        $department->update([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'remarks' => $validated['remarks'] ?? null,
        ]);

        AuditHelper::log(
            'Departments',
            'Updated',
            'Department',
            $department->id,
            'Updated department: ' . $department->name,
            $oldValues,
            [
                'code' => $department->code,
                'name' => $department->name,
                'sort_order' => $department->sort_order,
                'is_active' => $department->is_active,
                'remarks' => $department->remarks,
            ]
        );

        return redirect()
            ->route('departments.show', $department->id)
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Activate a department.
     */
    public function activate(int $id): RedirectResponse
    {
        $department = Department::findOrFail($id);

        if ($department->is_active) {
            return back()->with('success', 'Department is already active.');
        }

        $department->update([
            'is_active' => true,
        ]);

        AuditHelper::log(
            'Departments',
            'Activated',
            'Department',
            $department->id,
            'Activated department: ' . $department->name,
            ['is_active' => false],
            ['is_active' => true]
        );

        return back()->with('success', 'Department activated successfully.');
    }

    /**
     * Deactivate a department.
     *
     * Existing users/designations are preserved. The department simply
     * stops appearing in new user/designation dropdowns.
     */
    public function deactivate(int $id): RedirectResponse
    {
        $department = Department::findOrFail($id);

        if (! $department->is_active) {
            return back()->with('success', 'Department is already inactive.');
        }

        $department->update([
            'is_active' => false,
        ]);

        AuditHelper::log(
            'Departments',
            'Deactivated',
            'Department',
            $department->id,
            'Deactivated department: ' . $department->name,
            ['is_active' => true],
            ['is_active' => false]
        );

        return back()->with(
            'success',
            'Department deactivated successfully. Existing user and designation links were preserved.'
        );
    }
}
