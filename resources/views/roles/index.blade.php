@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Roles
</h1>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
        {{ session('error') }}
    </div>
@endif

<div class="mb-4">
    <a href="{{ route('roles.create') }}"
       class="bg-blue-600 text-white px-4 py-2 rounded">
        Add Role
    </a>
</div>

<div class="bg-white rounded shadow overflow-hidden">

    <table class="w-full">

        <thead class="bg-gray-100">
            <tr>
                <th class="p-4 text-left">Role Name</th>
                <th class="p-4 text-left">Users Assigned</th>
                <th class="p-4 text-left">Actions</th>
            </tr>
        </thead>

        <tbody>

            @forelse($roles as $role)

                <tr class="border-t">
                    <td class="p-4">
                        {{ $role->name }}
                    </td>

                    <td class="p-4">
                        {{ $role->users_count }}
                    </td>

                    <td class="p-4 flex gap-2">

                        <a href="{{ route('roles.edit', $role) }}"
                           class="bg-yellow-500 text-white px-3 py-1 rounded">
                            Edit
                        </a>

                        <form method="POST"
                              action="{{ route('roles.destroy', $role) }}"
                              onsubmit="return confirm('Are you sure you want to delete this role?');">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="bg-red-600 text-white px-3 py-1 rounded">
                                Delete
                            </button>
                        </form>

                    </td>
                </tr>

            @empty

                <tr>
                    <td colspan="3" class="p-4 text-center">
                        No Roles Found
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>

@endsection