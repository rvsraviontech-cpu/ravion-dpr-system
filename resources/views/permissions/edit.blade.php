@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Edit Permission
</h1>

<div class="bg-white rounded shadow p-6">

    <form method="POST" action="{{ route('permissions.update', $permission) }}">

        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block mb-1 font-semibold">
                Permission Name
            </label>

            <input type="text"
                   name="name"
                   value="{{ old('name', $permission->name) }}"
                   class="w-full border rounded px-3 py-2"
                   required>

            @error('name')
                <div class="text-red-600 text-sm mt-1">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block mb-1 font-semibold">
                Module
            </label>

            <input type="text"
                   name="module"
                   value="{{ old('module', $permission->module) }}"
                   class="w-full border rounded px-3 py-2">

            @error('module')
                <div class="text-red-600 text-sm mt-1">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block mb-1 font-semibold">
                Description
            </label>

            <textarea name="description"
                      rows="4"
                      class="w-full border rounded px-3 py-2">{{ old('description', $permission->description) }}</textarea>

            @error('description')
                <div class="text-red-600 text-sm mt-1">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-4">
            <label class="inline-flex items-center">
                <input type="checkbox"
                       name="is_active"
                       value="1"
                       class="mr-2"
                       @checked($permission->is_active)>

                Active
            </label>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded">
                Update
            </button>

            <a href="{{ route('permissions.index') }}"
               class="bg-gray-500 text-white px-4 py-2 rounded">
                Back
            </a>
        </div>

    </form>

</div>

@endsection