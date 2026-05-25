@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Edit Project
</h1>

<div class="bg-white rounded shadow p-6">

<form action="/projects/{{ $project->id }}"
      method="POST">

    @csrf
    @method('PUT')

    <div class="grid grid-cols-2 gap-6">

        <div>

            <label class="block mb-2">
                Project Code
            </label>

            <input type="text"
                   name="project_code"
                   value="{{ $project->project_code }}"
                   class="w-full border rounded px-4 py-2">

        </div>

        <div>

            <label class="block mb-2">
                Project Name
            </label>

            <input type="text"
                   name="project_name"
                   value="{{ $project->project_name }}"
                   class="w-full border rounded px-4 py-2">

        </div>

        <div>

            <label class="block mb-2">
                Client Name
            </label>

            <input type="text"
                   name="client_name"
                   value="{{ $project->client_name }}"
                   class="w-full border rounded px-4 py-2">

        </div>

        <div>

            <label class="block mb-2">
                Location
            </label>

            <input type="text"
                   name="location"
                   value="{{ $project->location }}"
                   class="w-full border rounded px-4 py-2">

        </div>

        <div>

            <label class="block mb-2">
                Start Date
            </label>

            <input type="date"
                   name="start_date"
                   value="{{ $project->start_date }}"
                   class="w-full border rounded px-4 py-2">

        </div>

        <div>

            <label class="block mb-2">
                Target Completion Date
            </label>

            <input type="date"
                   name="target_completion_date"
                   value="{{ $project->target_completion_date }}"
                   class="w-full border rounded px-4 py-2">

        </div>

    </div>
    <div class="mt-8">

    <h2 class="text-2xl font-bold mb-4">
        Assign Engineers
    </h2>

    <select name="engineers[]"
            multiple
            class="w-full border rounded px-4 py-3 h-48">

        @foreach($engineers as $engineer)

            <option value="{{ $engineer->id }}"

                {{ $project->users->contains($engineer->id)
                    ? 'selected'
                    : '' }}>

                {{ $engineer->name }}

            </option>

        @endforeach

    </select>

    <p class="text-sm text-gray-500 mt-2">
        Hold Ctrl (Windows) or Cmd (Mac) to select multiple engineers.
    </p>

</div>

    <button type="submit"
            class="mt-6 bg-blue-600 text-white px-6 py-2 rounded">

        Update Project

    </button>

</form>

</div>

@endsection