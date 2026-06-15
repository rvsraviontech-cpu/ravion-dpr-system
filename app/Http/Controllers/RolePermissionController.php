<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    public function index()
{
    $roles = Role::withCount('permissions')
        ->orderBy('name')
        ->get();

    $totalPermissions = Permission::where('is_active', true)->count();

    return view(
        'role-permissions.index',
        compact('roles', 'totalPermissions')
    );
}

    public function edit(Role $role)
    {
        $permissions = Permission::where('is_active', true)
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        $assignedPermissions = $role->permissions()
            ->pluck('permissions.id')
            ->toArray();

        return view(
            'role-permissions.edit',
            compact('role', 'permissions', 'assignedPermissions')
        );
    }

    public function update(Request $request, Role $role)
    {
        $permissionIds = $request->input('permissions', []);

        $role->permissions()->sync($permissionIds);

        return redirect()
            ->route('role-permissions.index')
            ->with('success', 'Role permissions updated successfully.');
    }
}