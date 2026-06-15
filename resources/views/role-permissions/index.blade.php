@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Role Permissions
</h1>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded shadow overflow-hidden">

    <table class="w-full">

        <thead class="bg-gray-100">
            <tr>
                <th class="p-4 text-left">Role</th>
                <th class="p-4 text-left">Permissions Assigned</th>
                <th class="p-4 text-left">Action</th>
            </tr>
        </thead>

        <tbody>

            @forelse($roles as $role)

                <tr class="border-t">

                    <td class="p-4">
                        {{ $role->name }}
                    </td>

                    <td class="p-4">
                        {{ $role->permissions_count }} / {{ $totalPermissions }}
                    </td>

                    <td class="p-4">
                        <a href="{{ route('role-permissions.edit', $role) }}"
                           class="bg-blue-600 text-white px-4 py-2 rounded">
                            Assign Permissions
                        </a>
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
<script>
document.getElementById('select-all-permissions').addEventListener('change', function () {
    document.querySelectorAll('input[name="permissions[]"]').forEach(function (checkbox) {
        checkbox.checked = this.checked;
    }, this);
});
</script>
@endsection