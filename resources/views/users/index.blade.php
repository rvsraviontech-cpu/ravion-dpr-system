@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">

    <h1 class="text-3xl font-bold">
        Users
    </h1>

    <a href="/users/create"
       class="bg-blue-500 text-white px-5 py-3 rounded">

        Create User

    </a>

</div>

<div class="bg-white rounded shadow">

    <table class="w-full">

        <thead class="bg-gray-200">

            <tr>

    <th class="p-4 text-left">Name</th>

    <th class="p-4 text-left">Email</th>

    <th class="p-4 text-left">Role</th>

    <th class="p-4 text-left">Action</th>

</tr>

        </thead>

        <tbody>

            @foreach($users as $user)

            <tr class="border-t">

                <td class="p-4">
                    {{ $user->name }}
                </td>

                <td class="p-4">
                    {{ $user->email }}
                </td>

                <td class="p-4">
                    {{ $user->role->name }}
                </td>
                <td class="p-4">

    <a href="/users/{{ $user->id }}/edit"
       class="text-blue-500">

        Edit

    </a>

</td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endsection