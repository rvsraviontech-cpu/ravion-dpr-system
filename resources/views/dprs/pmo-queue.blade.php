@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    PMO DPR Review Queue
</h1>

<div class="bg-white rounded shadow overflow-hidden">

    <table class="w-full">

        <thead class="bg-gray-200">

            <tr>

                <th class="p-4 text-left">
                    Project
                </th>

                <th class="p-4 text-left">
                    Engineer
                </th>

                <th class="p-4 text-left">
                    Date
                </th>

                <th class="p-4 text-left">
                    Status
                </th>

                <th class="p-4 text-left">
                    Action
                </th>

            </tr>

        </thead>

        <tbody>

            @foreach($dprs as $dpr)

            <tr class="border-t">

                <td class="p-4">
                    {{ $dpr->project->project_name }}
                </td>

                <td class="p-4">
                    {{ $dpr->user->name }}
                </td>

                <td class="p-4">
                    {{ $dpr->dpr_date }}
                </td>

                <td class="p-4">

                    <span class="bg-yellow-200 text-yellow-800 px-3 py-1 rounded">
                        Pending
                    </span>

                </td>

                <td class="p-4">

                    <a href="/dprs/{{ $dpr->id }}"
                       class="text-blue-500">

                        <div class="flex gap-3">

    <a href="/dprs/{{ $dpr->id }}"
       class="text-blue-500">

        View

    </a>

    <form action="/dprs/{{ $dpr->id }}/approve"
      method="POST"
      class="mb-2">

    @csrf

    <textarea name="pmo_remarks"
              placeholder="Approval remarks..."
              class="border rounded w-full p-2 mb-2"></textarea>

    <button type="submit"
            class="bg-green-600 text-white px-4 py-2 rounded">

        Approve

    </button>

</form>

<form action="/dprs/{{ $dpr->id }}/reject"
      method="POST">

    @csrf

    <textarea name="pmo_remarks"
              placeholder="Rejection remarks..."
              class="border rounded w-full p-2 mb-2"></textarea>

    <button type="submit"
            class="bg-red-600 text-white px-4 py-2 rounded">

        Reject

    </button>

</form>

</div>

                    </a>

                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endsection