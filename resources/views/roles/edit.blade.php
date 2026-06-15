@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Edit Role
</h1>

<div class="bg-white rounded shadow p-6">

    <form method="POST" action="{{ route('roles.update', $role) }}">

        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block mb-1 font-semibold">
                Role Name
            </label>

            <input type="text"
                   name="name"
                   value="{{ old('name', $role->name) }}"
                   class="w-full border rounded px-3 py-2"
                   required>

            @error('name')
                <div class="text-red-600 text-sm mt-1">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded">
                Update
            </button>

            <a href="{{ route('roles.index') }}"
               class="bg-gray-500 text-white px-4 py-2 rounded">
                Back
            </a>
        </div>

    </form>

</div>

@endsection