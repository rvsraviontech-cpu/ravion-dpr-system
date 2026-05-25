@extends('layouts.app')

@section('content')

<div class="flex justify-between mb-6">

    <h1 class="text-3xl font-bold">
        Projects
    </h1>

    <a href="/projects/create"
       class="bg-blue-500 text-white px-4 py-2 rounded">

        Create Project

    </a>

</div>

<table class="w-full bg-white shadow rounded">

    <thead class="bg-gray-200">

        <tr>

            <th class="p-3 text-left">Code</th>
            <th class="p-3 text-left">Project</th>
            <th class="p-3 text-left">Client</th>
            <th class="p-3 text-left">Location</th>
            <th class="p-4 text-left">Actions</th>

        </tr>

    </thead>

    <tbody>

        @foreach($projects as $project)

        <tr class="border-t">

            <td class="p-3">
                {{ $project->project_code }}
            </td>

            <td class="p-3">
                {{ $project->project_name }}
            </td>

            <td class="p-3">
                {{ $project->client_name }}
            </td>

            <td class="p-3">
                {{ $project->location }}
            </td>
            <td class="p-4 flex gap-2">
                <a href="/projects/{{ $project->id }}/edit"
                class="bg-yellow-500 text-white px-3 py-1 rounded">
                Edit
               </a>

    <form action="/projects/{{ $project->id }}"
          method="POST">

        @csrf
        @method('DELETE')

        <button type="submit"
                class="bg-red-600 text-white px-3 py-1 rounded"
                onclick="return confirm('Delete this project?')">

            Delete

        </button>

    </form>

</td>

        </tr>

        @endforeach

    </tbody>

</table>

@endsection