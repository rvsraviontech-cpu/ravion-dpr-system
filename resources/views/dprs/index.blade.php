@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">

    <h1 class="text-3xl font-bold">
        DPR List
    </h1>

    <a href="/dprs/create"
       class="bg-blue-500 text-white px-5 py-3 rounded">

        Create DPR

    </a>

</div>
<div class="bg-white p-6 rounded shadow mb-6">

    <form method="GET" action="/dprs">

        <div class="grid grid-cols-6 gap-4">

            <!-- Project -->

            <div>

                <label class="font-semibold">
                    Project
                </label>

                <select name="project_id"
                        class="w-full border rounded p-2 mt-2">

                    <option value="">
                        All Projects
                    </option>

                    @foreach($projects as $project)

                        <option value="{{ $project->id }}"
                            {{ request('project_id') == $project->id ? 'selected' : '' }}>

                            {{ $project->project_name }}

                        </option>

                    @endforeach

                </select>

            </div>

            <!-- Status -->

            <div>

                <label class="font-semibold">
                    Status
                </label>

                <select name="status"
                        class="w-full border rounded p-2 mt-2">

                    <option value="">
                        All Status
                    </option>

                    <option value="Pending"
                        {{ request('status') == 'Pending' ? 'selected' : '' }}>
                        Pending
                    </option>

                    <option value="Approved"
                        {{ request('status') == 'Approved' ? 'selected' : '' }}>
                        Approved
                    </option>

                    <option value="Rejected"
                        {{ request('status') == 'Rejected' ? 'selected' : '' }}>
                        Rejected
                    </option>

                </select>

            </div>

            <!-- Engineer -->

            <div>

                <label class="font-semibold">
                    Engineer
                </label>

                <select name="user_id"
                        class="w-full border rounded p-2 mt-2">

                    <option value="">
                        All Engineers
                    </option>

                    @foreach($engineers as $engineer)

                        <option value="{{ $engineer->id }}"
                            {{ request('user_id') == $engineer->id ? 'selected' : '' }}>

                            {{ $engineer->name }}

                        </option>

                    @endforeach

                </select>

            </div>
<!-- From Date -->

<div>

    <label class="font-semibold">
        From Date
    </label>

    <input type="date"
           name="from_date"
           value="{{ request('from_date') }}"
           class="w-full border rounded p-2 mt-2">

</div>
            <!-- To Date -->

<div>

    <label class="font-semibold">
        To Date
    </label>

    <input type="date"
           name="to_date"
           value="{{ request('to_date') }}"
           class="w-full border rounded p-2 mt-2">

</div>

            <!-- Button -->

            <div class="flex items-end">

                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded w-full">

                    Apply Filters

                </button>

            </div>

        </div>

    </form>

</div>
<div class="bg-white rounded shadow overflow-hidden">

    <table class="w-full">

        <thead class="bg-gray-200">

            <tr>

                <th class="p-4 text-left">
                    ID
                </th>

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
                    Weather
                </th>
                <th class="p-4 text-left">
                    Action
                </th>
                <th class="p-4 text-left">
                 Status
</th>
<th class="p-4 text-left">
    Actions
</th>

            </tr>

        </thead>

        <tbody>

            @foreach($dprs as $dpr)

            <tr class="border-t">

                <td class="p-4">
                    {{ $dpr->id }}
                </td>

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
                    {{ $dpr->weather }}
                </td>
                <td class="p-4">
                <a href="/dprs/{{ $dpr->id }}"
                 class="text-blue-500">
                 View
                </a>
                </td>

                <td class="p-4">

    @if($dpr->status == 'Pending')

        <span class="bg-yellow-200 text-yellow-800 px-3 py-1 rounded">
            Pending
        </span>

    @elseif($dpr->status == 'Approved')

        <span class="bg-green-200 text-green-800 px-3 py-1 rounded">
            Approved
        </span>

    @elseif($dpr->status == 'Rejected')

        <span class="bg-red-200 text-red-800 px-3 py-1 rounded">
            Rejected
        </span>

    @endif

</td>
<td class="p-4 flex gap-2">

    <a href="/dprs/{{ $dpr->id }}"
       class="bg-blue-600 text-white px-3 py-1 rounded">

        View

    </a>

    @if($dpr->status != 'Approved')

    <a href="/dprs/{{ $dpr->id }}/edit"
       class="bg-yellow-500 text-white px-3 py-1 rounded">

        Edit

    </a>

    <form action="/dprs/{{ $dpr->id }}"
          method="POST">

        @csrf
        @method('DELETE')

        <button type="submit"
                class="bg-red-600 text-white px-3 py-1 rounded"
                onclick="return confirm('Delete this DPR?')">

            Delete

        </button>

    </form>

    @endif

</td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endsection