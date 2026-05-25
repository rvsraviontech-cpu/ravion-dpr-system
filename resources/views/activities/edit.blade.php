@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Edit Activity
</h1>

<div class="bg-white rounded shadow p-6">

<form action="/activities/{{ $activity->id }}"
      method="POST">

    @csrf
    @method('PUT')

    <div class="grid grid-cols-2 gap-6">

        <div>

            <label class="block mb-2">
                Activity Name
            </label>

            <input type="text"
                   name="activity_name"
                   value="{{ $activity->activity_name }}"
                   class="w-full border rounded px-4 py-2">

        </div>

        <div>

            <label class="block mb-2">
                Unit
            </label>

            <input type="text"
                   name="unit"
                   value="{{ $activity->unit }}"
                   class="w-full border rounded px-4 py-2">

        </div>

        <div>

            <label class="block mb-2">
                Work Stage
            </label>

            <input type="text"
                   name="work_stage"
                   value="{{ $activity->work_stage }}"
                   class="w-full border rounded px-4 py-2">

        </div>

    </div>

    <button type="submit"
            class="mt-6 bg-blue-600 text-white px-6 py-2 rounded">

        Update Activity

    </button>

</form>

</div>

@endsection