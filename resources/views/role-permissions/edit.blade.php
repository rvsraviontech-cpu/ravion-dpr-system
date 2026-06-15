@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Assign Permissions - {{ $role->name }}
</h1>

<div class="bg-white rounded shadow p-4 mb-6">
    <label class="flex items-center gap-2 font-semibold">
        <input type="checkbox" id="select-all-permissions">
        Select All Permissions
    </label>
</div>

<form method="POST" action="{{ route('role-permissions.update', $role) }}">

    @csrf
    @method('PUT')

    <div class="mb-6 flex gap-2">

        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded">
            Save Permissions
        </button>

        <a href="{{ route('role-permissions.index') }}"
           class="bg-gray-500 text-white px-4 py-2 rounded">
            Back
        </a>

    </div>

    @foreach($permissions as $module => $modulePermissions)

        <div class="bg-white rounded shadow mb-6 overflow-hidden">

            <div class="bg-gray-100 p-4 font-bold">
                {{ $module ?? 'General' }}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">

                @foreach($modulePermissions as $permission)

                    <label class="flex items-center gap-2 border rounded p-3">

                        <input
                            type="checkbox"
                            name="permissions[]"
                            value="{{ $permission->id }}"
                            @checked(in_array($permission->id, $assignedPermissions))
                        >

                        <span>
                            {{ $permission->name }}
                        </span>

                    </label>

                @endforeach

            </div>

        </div>

    @endforeach

</form>
<script>
document.getElementById('select-all-permissions').addEventListener('change', function () {
    document.querySelectorAll('input[name="permissions[]"]').forEach(function (checkbox) {
        checkbox.checked = this.checked;
    }, this);
});
</script>
@endsection