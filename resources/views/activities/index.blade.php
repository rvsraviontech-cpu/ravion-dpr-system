@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto">

    <div class="flex justify-between items-center mb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Activity Master</h1>
            <p class="text-sm text-gray-500">
                Manage DPR activity dropdowns. Cost codes remain hidden from engineers.
            </p>
        </div>

        <a href="{{ route('activities.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-semibold">
            + Create Activity
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 p-3 rounded-md mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET"
          action="{{ route('activities.index') }}"
          class="bg-white border rounded-lg shadow-sm p-4 mb-4">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

            <div class="md:col-span-2">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search activity, work stage, unit..."
                       class="w-full border rounded-md px-3 py-2 text-sm">
            </div>

            <div class="flex gap-2">
                <select name="status"
                        class="w-full border rounded-md px-3 py-2 text-sm">
                    <option value="">All Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>

                <button class="bg-gray-900 text-white px-4 py-2 rounded-md text-sm">
                    Filter
                </button>

                <a href="{{ route('activities.index') }}"
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md text-sm">
                    Reset
                </a>
            </div>

        </div>

    </form>

    <div class="bg-white border rounded-lg shadow-sm overflow-hidden">

        <table class="w-full text-sm">

            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="p-3 text-left">Activity</th>
                    <th class="p-3 text-left">Work Stage</th>
                    <th class="p-3 text-left">Unit</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left w-40">Actions</th>
                </tr>
            </thead>

            <tbody>

                @forelse($activities as $activity)

                    <tr class="border-b hover:bg-gray-50">

                        <td class="p-3">
                            <div class="font-semibold text-gray-900">
                                {{ $activity->activity_name }}
                            </div>
                            <div class="text-xs text-gray-500">
                                ID: {{ $activity->id }}
                            </div>
                        </td>

                        <td class="p-3 text-gray-700">
                            {{ $activity->work_stage }}
                        </td>

                        <td class="p-3">
                            <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded text-xs font-semibold">
                                {{ $activity->unit }}
                            </span>
                        </td>

                        <td class="p-3">
                            @if($activity->is_active)
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold">
                                    Active
                                </span>
                            @else
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-semibold">
                                    Inactive
                                </span>
                            @endif
                        </td>

                        <td class="p-3">
                            <div class="flex gap-2">

                                <a href="{{ route('activities.edit', $activity->id) }}"
                                   class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs">
                                    Edit
                                </a>

                                <form action="{{ route('activities.destroy', $activity->id) }}"
                                      method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            onclick="return confirm('Change this activity status?')"
                                            class="{{ $activity->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-3 py-1 rounded text-xs">
                                        {{ $activity->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>

                            </div>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="p-6 text-center text-gray-500">
                            No activities found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection