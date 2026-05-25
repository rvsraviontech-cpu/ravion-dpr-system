@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">

    <h1 class="text-3xl font-bold">
        Contractors
    </h1>

    <a href="/contractors/create"
       class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">

        Create Contractor

    </a>

</div>

<div class="bg-white rounded shadow overflow-hidden">

    <table class="w-full">

        <thead class="bg-gray-200">

            <tr>

                <th class="p-4 text-left">ID</th>

                <th class="p-4 text-left">Contractor Name</th>

                <th class="p-4 text-left">Mobile</th>

                <th class="p-4 text-left">Work Category</th>
                <th class="p-4 text-left">Actions</th>

            </tr>

        </thead>

        <tbody>

            @foreach($contractors as $contractor)

            <tr class="border-t hover:bg-gray-50">

                <td class="p-4">
                    {{ $contractor->id }}
                </td>

                <td class="p-4">
                    {{ $contractor->contractor_name }}
                </td>

                <td class="p-4">
                    {{ $contractor->mobile }}
                </td>

                <td class="p-4">
                    {{ $contractor->work_category }}
                </td>
                <td class="p-4 flex gap-2">

    <a href="/contractors/{{ $contractor->id }}/edit"
       class="bg-yellow-500 text-white px-3 py-1 rounded">

        Edit

    </a>

    <form action="/contractors/{{ $contractor->id }}"
          method="POST">

        @csrf
        @method('DELETE')

        <button type="submit"
                class="bg-red-600 text-white px-3 py-1 rounded"
                onclick="return confirm('Delete this contractor?')">

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