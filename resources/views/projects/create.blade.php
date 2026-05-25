@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Create Project
</h1>

<form action="{{ route('projects.store') }}"
      method="POST"
      class="bg-white p-6 rounded shadow max-w-2xl">

    @csrf

    <div class="mb-4">

        <label class="block mb-2 font-semibold">
            Project Code
        </label>

        <input type="text"
               name="project_code"
               class="w-full border rounded px-4 py-2">

    </div>

    <div class="mb-4">

        <label class="block mb-2 font-semibold">
            Project Name
        </label>

        <input type="text"
               name="project_name"
               class="w-full border rounded px-4 py-2">

    </div>

    <div class="mb-4">

        <label class="block mb-2 font-semibold">
            Client Name
        </label>

        <input type="text"
               name="client_name"
               class="w-full border rounded px-4 py-2">

    </div>

    <div class="mb-4">

        <label class="block mb-2 font-semibold">
            Location
        </label>

        <input type="text"
               name="location"
               class="w-full border rounded px-4 py-2">

    </div>

    <div class="mb-4">

        <label class="block mb-2 font-semibold">
            Start Date
        </label>

        <input type="date"
               name="start_date"
               class="w-full border rounded px-4 py-2">

    </div>

    <div class="mb-6">

        <label class="block mb-2 font-semibold">
            Target Completion Date
        </label>

        <input type="date"
               name="target_completion_date"
               class="w-full border rounded px-4 py-2">

    </div>

    <button type="submit"
            class="bg-blue-500 text-white px-6 py-3 rounded">

        Save Project

    </button>

</form>

@endsection