<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Department;
use App\Models\EmployeeDesignation;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display the users list.
     */
    public function index(Request $request): View
    {
        $query = User::query()
            ->with([
                'role',
                'projects',
                'departmentMaster',
                'employeeDesignation',
            ])
            ->withCount('projects');

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('employee_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhereHas('departmentMaster', function ($departmentQuery) use ($search) {
                        $departmentQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('employeeDesignation', function ($designationQuery) use ($search) {
                        $designationQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->integer('role_id'));
        }

        /*
        |--------------------------------------------------------------------------
        | Department Filter
        |--------------------------------------------------------------------------
        |
        | department_id is the new master-based filter.
        | The legacy department text filter is still supported temporarily so
        | the current Users index page continues working during transition.
        |
        */
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->integer('department_id'));
        } elseif ($request->filled('department')) {
            $department = $request->string('department')->toString();

            $query->where(function ($builder) use ($department) {
                $builder
                    ->where('department', $department)
                    ->orWhereHas('departmentMaster', function ($departmentQuery) use ($department) {
                        $departmentQuery->where('name', $department);
                    });
            });
        }

        if ($request->filled('account_status')) {
            $query->where(
                'account_status',
                $request->string('account_status')->toString()
            );
        }

        if ($request->filled('project_id')) {
            $projectId = $request->integer('project_id');

            $query->where(function ($builder) use ($projectId) {
                $builder
                    ->where('project_access_scope', 'all')
                    ->orWhereHas('projects', function ($projectQuery) use ($projectId) {
                        $projectQuery->where('projects.id', $projectId);
                    });
            });
        }

        $users = $query
            ->orderByRaw("
                CASE
                    WHEN account_status = 'Active' THEN 1
                    WHEN account_status = 'Suspended' THEN 2
                    WHEN account_status = 'Inactive' THEN 3
                    WHEN account_status = 'Exited' THEN 4
                    ELSE 5
                END
            ")
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $roles = Role::query()
            ->orderBy('name')
            ->get();

        $projects = Project::query()
            ->orderBy('project_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Keep current index Blade compatible
        |--------------------------------------------------------------------------
        |
        | The current index page expects a simple list of department names.
        | We can switch this to department_id when we replace the index Blade.
        |
        */
        $departments = Department::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name');

        return view('users.index', compact(
            'users',
            'roles',
            'projects',
            'departments'
        ));
    }

    /**
     * Show the create-user form.
     */
    public function create(): View
    {
        $roles = Role::query()
            ->orderBy('name')
            ->get();

        $projects = Project::query()
            ->orderBy('project_name')
            ->get();

        $departments = Department::query()
            ->where('is_active', true)
            ->with([
                'designations' => function ($query) {
                    $query
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('name');
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $employeeCode = User::generateEmployeeCode();

        return view('users.create', compact(
            'roles',
            'projects',
            'departments',
            'employeeCode'
        ));
    }

    /**
     * Create a new user.
     *
     * Role is required.
     * Department, designation and project assignment are optional.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'mobile' => [
                'nullable',
                'string',
                'max:30',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'department_id' => [
                'nullable',
                Rule::exists('departments', 'id')->where(
                    fn ($query) => $query->where('is_active', true)
                ),
            ],
            'employee_designation_id' => [
                'nullable',
                Rule::exists('employee_designations', 'id')->where(
                    function ($query) use ($request) {
                        $query->where('is_active', true);

                        if ($request->filled('department_id')) {
                            $query->where(
                                'department_id',
                                $request->integer('department_id')
                            );
                        } else {
                            /*
                             * A designation cannot be submitted without a
                             * department because the designation dropdown is
                             * department-dependent.
                             */
                            $query->whereRaw('1 = 0');
                        }
                    }
                ),
            ],
            'joining_date' => [
                'nullable',
                'date',
            ],
            'role_id' => [
                'required',
                'exists:roles,id',
            ],
            'project_access_scope' => [
                'nullable',
                Rule::in([
                    'all',
                    'selected',
                ]),
            ],
            'project_ids' => [
                'nullable',
                'array',
            ],
            'project_ids.*' => [
                'integer',
                'exists:projects,id',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'must_change_password' => [
                'nullable',
                'boolean',
            ],
            'remarks' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $projectAccessScope = $validated['project_access_scope'] ?? 'selected';

        $department = ! empty($validated['department_id'])
            ? Department::find($validated['department_id'])
            : null;

        $designation = ! empty($validated['employee_designation_id'])
            ? EmployeeDesignation::find($validated['employee_designation_id'])
            : null;

        $user = null;

        DB::transaction(function () use (
            $validated,
            $projectAccessScope,
            $department,
            $designation,
            &$user
        ) {
            $user = User::create([
                'employee_code' => User::generateEmployeeCode(),
                'name' => $validated['name'],
                'mobile' => $validated['mobile'] ?? null,

                /*
                |--------------------------------------------------------------------------
                | Controlled Department / Designation Masters
                |--------------------------------------------------------------------------
                |
                | IDs are now the source of truth.
                | Legacy text values are mirrored temporarily so existing reports
                | and screens continue working until the migration is complete.
                |
                */
                'department_id' => $department?->id,
                'employee_designation_id' => $designation?->id,
                'department' => $department?->name,
                'designation' => $designation?->name,

                'joining_date' => $validated['joining_date'] ?? null,
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role_id' => $validated['role_id'],
                'project_access_scope' => $projectAccessScope,
                'account_status' => 'Active',
                'password_changed_at' => now(),
                'must_change_password' => (bool) ($validated['must_change_password'] ?? false),
                'remarks' => $validated['remarks'] ?? null,
            ]);

            if ($projectAccessScope === 'selected') {
                $user->projects()->sync($validated['project_ids'] ?? []);
            } else {
                $user->projects()->detach();
            }

            AuditHelper::log(
                'Users',
                'Created',
                'User',
                $user->id,
                'Created new user: ' . $user->name,
                null,
                [
                    'id' => $user->id,
                    'employee_code' => $user->employee_code,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'department_id' => $user->department_id,
                    'department_name' => $department?->name,
                    'employee_designation_id' => $user->employee_designation_id,
                    'designation_name' => $designation?->name,
                    'role_id' => $user->role_id,
                    'project_access_scope' => $user->project_access_scope,
                    'project_ids' => $user->projects()
                        ->pluck('projects.id')
                        ->all(),
                    'account_status' => $user->account_status,
                ]
            );
        });

        return redirect()
            ->route('users.show', $user->id)
            ->with('success', 'User created successfully.');
    }

    /**
     * Display a user profile.
     */
    public function show(int $id): View
    {
        $user = User::query()
            ->with([
                'role',
                'projects',
                'departmentMaster',
                'employeeDesignation',
                'deactivatedBy',
            ])
            ->findOrFail($id);

        return view('users.show', compact('user'));
    }

    /**
     * Show the edit-user form.
     */
    public function edit(int $id): View
    {
        $user = User::query()
            ->with([
                'projects',
                'departmentMaster',
                'employeeDesignation',
            ])
            ->findOrFail($id);

        $roles = Role::query()
            ->orderBy('name')
            ->get();

        $projects = Project::query()
            ->orderBy('project_name')
            ->get();

        $departments = Department::query()
            ->where('is_active', true)
            ->with([
                'designations' => function ($query) {
                    $query
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('name');
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('users.edit', compact(
            'user',
            'roles',
            'projects',
            'departments'
        ));
    }

    /**
     * Update an existing user.
     *
     * Department, designation and project assignment remain optional.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $user = User::query()
            ->with([
                'role',
                'projects',
                'departmentMaster',
                'employeeDesignation',
            ])
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'mobile' => [
                'nullable',
                'string',
                'max:30',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'department_id' => [
                'nullable',
                Rule::exists('departments', 'id')->where(
                    fn ($query) => $query->where('is_active', true)
                ),
            ],
            'employee_designation_id' => [
                'nullable',
                Rule::exists('employee_designations', 'id')->where(
                    function ($query) use ($request) {
                        $query->where('is_active', true);

                        if ($request->filled('department_id')) {
                            $query->where(
                                'department_id',
                                $request->integer('department_id')
                            );
                        } else {
                            $query->whereRaw('1 = 0');
                        }
                    }
                ),
            ],
            'joining_date' => [
                'nullable',
                'date',
            ],
            'role_id' => [
                'required',
                'exists:roles,id',
            ],
            'project_access_scope' => [
                'nullable',
                Rule::in([
                    'all',
                    'selected',
                ]),
            ],
            'project_ids' => [
                'nullable',
                'array',
            ],
            'project_ids.*' => [
                'integer',
                'exists:projects,id',
            ],
            'remarks' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $projectAccessScope = $validated['project_access_scope']
            ?? $user->project_access_scope
            ?? 'selected';

        $department = ! empty($validated['department_id'])
            ? Department::find($validated['department_id'])
            : null;

        $designation = ! empty($validated['employee_designation_id'])
            ? EmployeeDesignation::find($validated['employee_designation_id'])
            : null;

        $oldValues = [
            'name' => $user->name,
            'mobile' => $user->mobile,
            'email' => $user->email,
            'department_id' => $user->department_id,
            'department_name' => $user->departmentMaster?->name ?? $user->department,
            'employee_designation_id' => $user->employee_designation_id,
            'designation_name' => $user->employeeDesignation?->name ?? $user->designation,
            'joining_date' => $user->joining_date?->format('Y-m-d'),
            'role_id' => $user->role_id,
            'project_access_scope' => $user->project_access_scope,
            'project_ids' => $user->projects
                ->pluck('id')
                ->sort()
                ->values()
                ->all(),
            'remarks' => $user->remarks,
        ];

        $oldRoleId = $user->role_id;
        $oldRole = $user->role;

        DB::transaction(function () use (
            $user,
            $validated,
            $projectAccessScope,
            $department,
            $designation
        ) {
            $user->update([
                'name' => $validated['name'],
                'mobile' => $validated['mobile'] ?? null,
                'email' => $validated['email'],

                /*
                 * IDs are the source of truth.
                 * Legacy text fields remain mirrored temporarily.
                 */
                'department_id' => $department?->id,
                'employee_designation_id' => $designation?->id,
                'department' => $department?->name,
                'designation' => $designation?->name,

                'joining_date' => $validated['joining_date'] ?? null,
                'role_id' => $validated['role_id'],
                'project_access_scope' => $projectAccessScope,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            if ($projectAccessScope === 'selected') {
                $user->projects()->sync($validated['project_ids'] ?? []);
            } else {
                $user->projects()->detach();
            }
        });

        $user->refresh();

        $user->load([
            'role',
            'projects',
            'departmentMaster',
            'employeeDesignation',
        ]);

        $newValues = [
            'name' => $user->name,
            'mobile' => $user->mobile,
            'email' => $user->email,
            'department_id' => $user->department_id,
            'department_name' => $user->departmentMaster?->name,
            'employee_designation_id' => $user->employee_designation_id,
            'designation_name' => $user->employeeDesignation?->name,
            'joining_date' => $user->joining_date?->format('Y-m-d'),
            'role_id' => $user->role_id,
            'project_access_scope' => $user->project_access_scope,
            'project_ids' => $user->projects
                ->pluck('id')
                ->sort()
                ->values()
                ->all(),
            'remarks' => $user->remarks,
        ];

        AuditHelper::log(
            'Users',
            'Updated',
            'User',
            $user->id,
            'Updated user: ' . $user->name,
            $oldValues,
            $newValues
        );

        if ((int) $oldRoleId !== (int) $user->role_id) {
            AuditHelper::log(
                'Users',
                'Role Changed',
                'User',
                $user->id,
                'Changed role for user: ' . $user->name,
                [
                    'role_id' => $oldRoleId,
                    'role_name' => optional($oldRole)->name,
                ],
                [
                    'role_id' => $user->role_id,
                    'role_name' => optional($user->role)->name,
                ]
            );
        }

        if (
            $oldValues['project_access_scope'] !== $newValues['project_access_scope']
            || $oldValues['project_ids'] !== $newValues['project_ids']
        ) {
            AuditHelper::log(
                'Users',
                'Project Access Changed',
                'User',
                $user->id,
                'Changed project access for user: ' . $user->name,
                [
                    'project_access_scope' => $oldValues['project_access_scope'],
                    'project_ids' => $oldValues['project_ids'],
                ],
                [
                    'project_access_scope' => $newValues['project_access_scope'],
                    'project_ids' => $newValues['project_ids'],
                ]
            );
        }

        return redirect()
            ->route('users.show', $user->id)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Display password management screen.
     */
    public function password(int $id): View
    {
        $user = User::query()
            ->with([
                'role',
                'departmentMaster',
                'employeeDesignation',
            ])
            ->findOrFail($id);

        return view('users.password', compact('user'));
    }

    /**
     * Reset/set a user's password.
     */
    public function updatePassword(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'must_change_password' => [
                'nullable',
                'boolean',
            ],
        ]);

        $oldPasswordChangedAt = $user->password_changed_at;
        $oldMustChangePassword = $user->must_change_password;

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'password_changed_at' => now(),
            'must_change_password' => (bool) ($validated['must_change_password'] ?? false),
            'remember_token' => null,
        ])->save();

        AuditHelper::log(
            'Users',
            'Password Reset',
            'User',
            $user->id,
            'Reset password for user: ' . $user->name,
            [
                'password_changed_at' => $oldPasswordChangedAt?->toDateTimeString(),
                'must_change_password' => $oldMustChangePassword,
            ],
            [
                'password_changed_at' => $user->password_changed_at?->toDateTimeString(),
                'must_change_password' => $user->must_change_password,
            ]
        );

        return redirect()
            ->route('users.show', $user->id)
            ->with('success', 'Password updated successfully.');
    }

    /**
     * Activate an account.
     */
    public function activate(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->account_status === 'Active') {
            return back()
                ->with('success', 'User account is already active.');
        }

        $oldValues = [
            'account_status' => $user->account_status,
            'exit_date' => $user->exit_date?->format('Y-m-d'),
        ];

        $user->update([
            'account_status' => 'Active',
            'deactivated_at' => null,
            'deactivated_by' => null,
            'exit_date' => null,
        ]);

        AuditHelper::log(
            'Users',
            'Activated',
            'User',
            $user->id,
            'Activated user account: ' . $user->name,
            $oldValues,
            [
                'account_status' => 'Active',
                'exit_date' => null,
            ]
        );

        return back()
            ->with('success', 'User account activated successfully.');
    }

    /**
     * Deactivate an account.
     */
    public function deactivate(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ((int) auth()->id() === (int) $user->id) {
            return back()->withErrors([
                'user' => 'You cannot deactivate your own account.',
            ]);
        }

        if ($user->account_status === 'Inactive') {
            return back()
                ->with('success', 'User account is already inactive.');
        }

        $oldStatus = $user->account_status;

        $user->update([
            'account_status' => 'Inactive',
            'deactivated_at' => now(),
            'deactivated_by' => auth()->id(),
            'remember_token' => null,
        ]);

        AuditHelper::log(
            'Users',
            'Deactivated',
            'User',
            $user->id,
            'Deactivated user account: ' . $user->name,
            [
                'account_status' => $oldStatus,
            ],
            [
                'account_status' => 'Inactive',
                'deactivated_by' => auth()->id(),
            ]
        );

        return back()
            ->with('success', 'User account deactivated successfully.');
    }

    /**
     * Suspend an account.
     */
    public function suspend(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ((int) auth()->id() === (int) $user->id) {
            return back()->withErrors([
                'user' => 'You cannot suspend your own account.',
            ]);
        }

        if ($user->account_status === 'Suspended') {
            return back()
                ->with('success', 'User account is already suspended.');
        }

        $oldStatus = $user->account_status;

        $user->update([
            'account_status' => 'Suspended',
            'deactivated_at' => now(),
            'deactivated_by' => auth()->id(),
            'remember_token' => null,
        ]);

        AuditHelper::log(
            'Users',
            'Suspended',
            'User',
            $user->id,
            'Suspended user account: ' . $user->name,
            [
                'account_status' => $oldStatus,
            ],
            [
                'account_status' => 'Suspended',
                'deactivated_by' => auth()->id(),
            ]
        );

        return back()
            ->with('success', 'User account suspended successfully.');
    }

    /**
     * Mark a user as exited.
     *
     * Historical ERP records and project_user mappings are preserved.
     * Only current PMO responsibility is cleared from projects.assigned_pmo_id.
     */
    public function exitUser(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ((int) auth()->id() === (int) $user->id) {
            return back()->withErrors([
                'user' => 'You cannot mark your own account as exited.',
            ]);
        }

        $validated = $request->validate([
            'exit_date' => [
                'required',
                'date',
            ],
        ]);

        $oldValues = [
            'account_status' => $user->account_status,
            'exit_date' => $user->exit_date?->format('Y-m-d'),
        ];

        $releasedProjects = [];

        DB::transaction(function () use (
            $user,
            $validated,
            &$releasedProjects
        ) {
            /*
            |--------------------------------------------------------------------------
            | Release current PMO responsibility
            |--------------------------------------------------------------------------
            |
            | assigned_pmo_id represents an active responsibility, not history.
            | We therefore clear it when the employee exits.
            |
            | project_user mappings are intentionally preserved because they may
            | be useful historically and existing transaction records must remain
            | linked to the original user.
            |
            */
            $releasedProjects = DB::table('projects')
                ->where('assigned_pmo_id', $user->id)
                ->orderBy('id')
                ->get([
                    'id',
                    'project_code',
                    'project_name',
                ])
                ->map(fn ($project) => [
                    'id' => $project->id,
                    'project_code' => $project->project_code,
                    'project_name' => $project->project_name,
                ])
                ->all();

            DB::table('projects')
                ->where('assigned_pmo_id', $user->id)
                ->update([
                    'assigned_pmo_id' => null,
                    'updated_at' => now(),
                ]);

            /*
            |--------------------------------------------------------------------------
            | Exit account
            |--------------------------------------------------------------------------
            */
            $user->update([
                'account_status' => 'Exited',
                'exit_date' => $validated['exit_date'],
                'deactivated_at' => now(),
                'deactivated_by' => auth()->id(),
                'remember_token' => null,
            ]);
        });

        AuditHelper::log(
            'Users',
            'Exited',
            'User',
            $user->id,
            'Marked user as exited: ' . $user->name,
            array_merge(
                $oldValues,
                [
                    'assigned_pmo_projects' => $releasedProjects,
                ]
            ),
            [
                'account_status' => 'Exited',
                'exit_date' => $user->exit_date?->format('Y-m-d'),
                'deactivated_by' => auth()->id(),
                'assigned_pmo_projects' => [],
            ]
        );

        $releasedCount = count($releasedProjects);

        $message = 'User marked as exited successfully.';

        if ($releasedCount > 0) {
            $message .= ' '
                . $releasedCount
                . ' active PMO '
                . ($releasedCount === 1 ? 'assignment was' : 'assignments were')
                . ' released. Historical records and project history were preserved.';
        }

        return back()->with('success', $message);
    }
}
