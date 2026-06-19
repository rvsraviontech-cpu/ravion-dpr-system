<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Helpers\AuditHelper;

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
        $oldPermissionNames = $role->permissions()
    ->pluck('permissions.name')
    ->toArray();

$permissionIds = $request->input('permissions', []);

$role->permissions()->sync($permissionIds);

$newPermissionNames = $role->permissions()
    ->pluck('permissions.name')
    ->toArray();

AuditHelper::log(
    'Role Permissions',
    'Updated',
    'Role',
    $role->id,
    'Permissions updated for role: ' . $role->name,
    [
        'permissions' => $oldPermissionNames
    ],
    [
        'permissions' => $newPermissionNames
    ]
);

        return redirect()
            ->route('role-permissions.index')
            ->with('success', 'Role permissions updated successfully.');
    }
}