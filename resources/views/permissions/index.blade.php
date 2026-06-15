@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Permissions
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
    <a href="{{ route('permissions.create') }}"
       class="bg-blue-600 text-white px-4 py-2 rounded">
        Add Permission
    </a>
</div>

<div class="bg-white rounded shadow overflow-hidden">

    <table class="w-full">

        <thead class="bg-gray-100">
            <tr>
                <th class="p-4 text-left">Permission</th>
                <th class="p-4 text-left">Module</th>
                <th class="p-4 text-left">Description</th>
                <th class="p-4 text-left">Status</th>
                <th class="p-4 text-left">Actions</th>
            </tr>
        </thead>

        <tbody>

            @forelse($permissions as $permission)

                <tr class="border-t">

                    <td class="p-4">
                        {{ $permission->name }}
                    </td>

                    <td class="p-4">
                        {{ $permission->module ?? '-' }}
                    </td>

                    <td class="p-4">
                        {{ $permission->description ?? '-' }}
                    </td>

                    <td class="p-4">
                        @if($permission->is_active)
                            <span class="px-3 py-1 rounded bg-green-100 text-green-700">
                                Active
                            </span>
                        @else
                            <span class="px-3 py-1 rounded bg-red-100 text-red-700">
                                Inactive
                            </span>
                        @endif
                    </td>

                    <td class="p-4 flex gap-2">

                        <a href="{{ route('permissions.edit', $permission) }}"
                           class="bg-yellow-500 text-white px-3 py-1 rounded">
                            Edit
                        </a>

                        <form method="POST"
                              action="{{ route('permissions.destroy', $permission) }}"
                              onsubmit="return confirm('Are you sure you want to delete this permission?');">
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
                    <td colspan="5" class="p-4 text-center">
                        No Permissions Found
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>

@endsection