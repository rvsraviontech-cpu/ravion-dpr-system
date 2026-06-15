<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::orderBy('module')
            ->orderBy('name')
            ->get();

        return view('permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('permissions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'module' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        Permission::create([
            'name' => $request->name,
            'module' => $request->module,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    public function edit(Permission $permission)
    {
        return view('permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'module' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $permission->update([
            'name' => $request->name,
            'module' => $request->module,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission)
    {
        if ($permission->roles()->count() > 0) {
            return redirect()
                ->route('permissions.index')
                ->with('error', 'Cannot delete permission because it is assigned to one or more roles.');
        }

        $permission->delete();

        return redirect()
            ->route('permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}