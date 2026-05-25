@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Create User
</h1>

<form action="/users/store"
      method="POST"
      class="bg-white p-6 rounded shadow max-w-2xl">

    @csrf

    <div class="mb-4">

        <label class="block mb-2 font-semibold">
            Name
        </label>

        <input type="text"
               name="name"
               class="w-full border rounded px-4 py-2">

    </div>

    <div class="mb-4">

        <label class="block mb-2 font-semibold">
            Email
        </label>

        <input type="email"
               name="email"
               class="w-full border rounded px-4 py-2">

    </div>

    <div class="mb-4">

        <label class="block mb-2 font-semibold">
            Password
        </label>

        <input type="password"
               name="password"
               class="w-full border rounded px-4 py-2">

    </div>

    <div class="mb-6">

        <label class="block mb-2 font-semibold">
            Role
        </label>

        <select name="role_id"
                class="w-full border rounded px-4 py-2">

            @foreach($roles as $role)

                <option value="{{ $role->id }}">
                    {{ $role->name }}
                </option>

            @endforeach

        </select>

    </div>

    <button type="submit"
            class="bg-blue-500 text-white px-6 py-3 rounded">

        Save User

    </button>

</form>

@endsection