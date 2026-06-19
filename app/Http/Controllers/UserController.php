<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $request->role_id,
        ]);

        AuditHelper::log(
            'Users',
            'Created',
            'User',
            $user->id,
            'Created new user: ' . $user->name,
            null,
            $user->only([
                'id',
                'name',
                'email',
                'role_id'
            ])
        );

        return redirect('/users')
            ->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        $roles = Role::all();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
        ]);

        $oldValues = $user->only([
            'name',
            'email',
            'role_id'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
        ]);

        $newValues = $user->only([
            'name',
            'email',
            'role_id'
        ]);

        AuditHelper::log(
            'Users',
            'Updated',
            'User',
            $user->id,
            'Updated user: ' . $user->name,
            $oldValues,
            $newValues
        );

        if ($oldValues['role_id'] != $newValues['role_id']) {

    $oldRole = Role::find($oldValues['role_id']);
    $newRole = Role::find($newValues['role_id']);

    AuditHelper::log(
        'Users',
        'Role Changed',
        'User',
        $user->id,
        'Changed role for user: ' . $user->name,
        [
            'role_id' => $oldValues['role_id'],
            'role_name' => optional($oldRole)->name,
        ],
        [
            'role_id' => $newValues['role_id'],
            'role_name' => optional($newRole)->name,
        ]
    );
}

        return redirect('/users')
            ->with('success', 'User updated successfully.');
    }
}