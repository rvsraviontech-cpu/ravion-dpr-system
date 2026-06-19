<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Helpers\AuditHelper;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')
            ->orderBy('name')
            ->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
        ]);

        $role = Role::create([
    'name' => $request->name,
]);

AuditHelper::log(
    'Roles',
    'Created',
    'Role',
    $role->id,
    'Role created: ' . $role->name,
    null,
    $role->only([
        'id',
        'name'
    ])
);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
        ]);

        $oldValues = $role->only([
    'name'
]);

        $role->update([
            'name' => $request->name,
        ]);

        $newValues = $role->only([
    'name'
]);

AuditHelper::log(
    'Roles',
    'Updated',
    'Role',
    $role->id,
    'Role updated: ' . $role->name,
    $oldValues,
    $newValues
);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return redirect()
                ->route('roles.index')
                ->with('error', 'Cannot delete role because users are assigned to it.');
        }

        AuditHelper::log(
    'Roles',
    'Deleted',
    'Role',
    $role->id,
    'Role deleted: ' . $role->name,
    $role->only([
        'id',
        'name'
    ]),
    null
);

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}