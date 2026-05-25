@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">

    <h1 class="text-3xl font-bold">
        Activities
    </h1>

    <a href="/activities/create"
       class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">

        Create Activity

    </a>

</div>

<div class="bg-white rounded shadow overflow-hidden">

    <table class="w-full">

        <thead class="bg-gray-200">

            <tr>

                <th class="p-4 text-left">ID</th>

                <th class="p-4 text-left">Activity Name</th>

                <th class="p-4 text-left">Unit</th>

                <th class="p-4 text-left">Work Stage</th>
                <th class="p-4 text-left">Actions</th>

            </tr>

        </thead>

        <tbody>

            @foreach($activities as $activity)

            <tr class="border-t hover:bg-gray-50">

                <td class="p-4">
                    {{ $activity->id }}
                </td>

                <td class="p-4">
                    {{ $activity->activity_name }}
                </td>

                <td class="p-4">
                    {{ $activity->unit }}
                </td>

                <td class="p-4">
                    {{ $activity->work_stage }}
                </td>
                <td class="p-4 flex gap-2">

    <a href="/activities/{{ $activity->id }}/edit"
       class="bg-yellow-500 text-white px-3 py-1 rounded">

        Edit

    </a>

    <form action="/activities/{{ $activity->id }}"
          method="POST">

        @csrf
        @method('DELETE')

        <button type="submit"
                class="bg-red-600 text-white px-3 py-1 rounded"
                onclick="return confirm('Delete this activity?')">

            Delete

        </button>

    </form>

</td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endsection