<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;



class UserController extends Controller
{
    public function index()
{
    $users = \App\Models\User::with('role')->get();

    return view('users.index', compact('users'));
}

public function create()
{
    $roles = \App\Models\Role::all();

    return view('users.create', compact('roles'));
}

public function store(Request $request)
{
    \App\Models\User::create([

        'name' => $request->name,

        'email' => $request->email,

        'password' => bcrypt($request->password),

        'role_id' => $request->role_id
    ]);

    return redirect('/users')
        ->with('success', 'User created successfully.');
}
public function edit($id)
{
    $user = \App\Models\User::findOrFail($id);

    $roles = \App\Models\Role::all();

    return view('users.edit', compact('user', 'roles'));
}

public function update(Request $request, $id)
{
    $user = \App\Models\User::findOrFail($id);

    $user->name = $request->name;

    $user->email = $request->email;

    $user->role_id = $request->role_id;

    $user->save();

    return redirect('/users')
        ->with('success', 'User updated successfully.');
}
}
