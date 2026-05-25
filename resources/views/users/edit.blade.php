@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Edit User
</h1>

<form action="/users/{{ $user->id }}/update"
      method="POST"
      class="bg-white p-6 rounded shadow max-w-2xl">

    @csrf

    <div class="mb-4">

        <label class="block mb-2 font-semibold">
            Name
        </label>

        <input type="text"
               name="name"
               value="{{ $user->name }}"
               class="w-full border rounded px-4 py-2">

    </div>

    <div class="mb-4">

        <label class="block mb-2 font-semibold">
            Email
        </label>

        <input type="email"
               name="email"
               value="{{ $user->email }}"
               class="w-full border rounded px-4 py-2">

    </div>

    <div class="mb-6">

        <label class="block mb-2 font-semibold">
            Role
        </label>

        <select name="role_id"
                class="w-full border rounded px-4 py-2">

            @foreach($roles as $role)

                <option value="{{ $role->id }}"
                    {{ $user->role_id == $role->id ? 'selected' : '' }}>

                    {{ $role->name }}

                </option>

            @endforeach

        </select>

    </div>

    <button type="submit"
            class="bg-blue-500 text-white px-6 py-3 rounded">

        Update User

    </button>

</form>

@endsection