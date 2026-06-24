@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">

    <div>

        <h1 class="text-3xl font-bold">
            Activity Divisions
        </h1>

        <p class="text-gray-500">
            Organize activities into logical construction divisions.
        </p>

    </div>

    <a href="{{ route('activity-divisions.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded">

        + New Division

    </a>

</div>

@if(session('success'))

<div class="bg-green-100 border border-green-300 text-green-700 p-3 rounded mb-5">

    {{ session('success') }}

</div>

@endif

<div class="bg-white rounded-lg shadow">

    <div class="p-5 border-b">

        <form>

            <div class="grid grid-cols-3 gap-4">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search Division..."
                    class="border rounded px-4 py-2">

                <select
                    name="status"
                    class="border rounded px-4 py-2">

                    <option value="">All Status</option>

                    <option value="1"
                        {{ request('status')=='1'?'selected':'' }}>
                        Active
                    </option>

                    <option value="0"
                        {{ request('status')=='0'?'selected':'' }}>
                        Inactive
                    </option>

                </select>

                <button class="bg-blue-600 text-white rounded">

                    Filter

                </button>

            </div>

        </form>

    </div>

    <table class="w-full">

        <thead class="bg-gray-100">

        <tr>

            <th class="p-4 text-left">Code</th>

            <th class="p-4 text-left">Division</th>

            <th class="p-4 text-left">Sequence</th>

            <th class="p-4 text-left">Status</th>

            <th class="p-4 text-center">Action</th>

        </tr>

        </thead>

        <tbody>

        @forelse($activityDivisions as $division)

            <tr class="border-t hover:bg-gray-50">

                <td class="p-4">
                    {{ $division->code }}
                </td>

                <td class="p-4 font-semibold">
                    {{ $division->name }}
                </td>

                <td class="p-4">
                    {{ $division->sequence }}
                </td>

                <td class="p-4">

                    @if($division->is_active)

                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">

                            Active

                        </span>

                    @else

                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">

                            Inactive

                        </span>

                    @endif

                </td>

                <td class="p-4 text-center">

                    <a href="{{ route('activity-divisions.edit',$division) }}"
                       class="bg-yellow-500 text-white px-3 py-1 rounded">

                        Edit

                    </a>

                    <form
                        action="{{ route('activity-divisions.destroy',$division) }}"
                        method="POST"
                        class="inline">

                        @csrf
                        @method('DELETE')

                        <button
                            onclick="return confirm('Change Status?')"
                            class="bg-red-600 text-white px-3 py-1 rounded">

                            {{ $division->is_active?'Deactivate':'Activate' }}

                        </button>

                    </form>

                </td>

            </tr>

        @empty

            <tr>

                <td colspan="5"
                    class="text-center p-8 text-gray-500">

                    No Activity Divisions Found

                </td>

            </tr>

        @endforelse

        </tbody>

    </table>

</div>

@endsection