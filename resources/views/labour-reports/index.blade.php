@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Labour Reports
</h1>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
        {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Total Labour Today</p>
        <p class="text-2xl font-bold text-blue-700">
            {{ $totalLabourToday }}
        </p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Draft Reports</p>
        <p class="text-2xl font-bold text-yellow-700">
            {{ $draftCount }}
        </p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Submitted Reports</p>
        <p class="text-2xl font-bold text-orange-700">
            {{ $submittedCount }}
        </p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Approved Reports</p>
        <p class="text-2xl font-bold text-green-700">
            {{ $approvedCount }}
        </p>
    </div>

</div>

<div class="bg-white p-4 rounded shadow mb-6">

    <form method="GET"
          action="{{ route('labour-reports.index') }}"
          class="grid grid-cols-1 md:grid-cols-6 gap-4">

        <div>
            <label class="block text-sm font-semibold mb-1">Date</label>
            <input type="date"
                   name="entry_date"
                   value="{{ request('entry_date') }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Project</label>
            <select name="project_id"
                    class="border p-2 rounded w-full">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}"
                        {{ request('project_id') == $project->id ? 'selected' : '' }}>
                        {{ $project->project_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Contractor</label>
            <select name="contractor_id"
                    class="border p-2 rounded w-full">
                <option value="">All Contractors</option>
                @foreach($contractors as $contractor)
                    <option value="{{ $contractor->id }}"
                        {{ request('contractor_id') == $contractor->id ? 'selected' : '' }}>
                        {{ $contractor->contractor_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Activity</label>
            <select name="activity_id"
                    class="border p-2 rounded w-full">
                <option value="">All Activities</option>
                @foreach($activities as $activity)
                    <option value="{{ $activity->id }}"
                        {{ request('activity_id') == $activity->id ? 'selected' : '' }}>
                        {{ $activity->activity_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Status</label>
            <select name="status"
                    class="border p-2 rounded w-full">
                <option value="">All Status</option>
                <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                <option value="Submitted" {{ request('status') == 'Submitted' ? 'selected' : '' }}>Submitted</option>
                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Filter
            </button>

            <a href="{{ route('labour-reports.index') }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                Clear
            </a>
        </div>

    </form>

</div>

<a href="{{ route('labour-reports.create') }}"
   class="bg-blue-600 text-white px-4 py-2 rounded inline-block mb-4">
    Add Labour Report
</a>

<div class="bg-white rounded shadow overflow-x-auto">

    <table class="w-full text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-3 text-left">#</th>
                <th class="p-3 text-left">Date</th>
                <th class="p-3 text-left">Project</th>
                <th class="p-3 text-left">Block</th>
                <th class="p-3 text-left">Floor</th>
                <th class="p-3 text-left">Unit</th>
                <th class="p-3 text-left">Room</th>
                <th class="p-3 text-left">Sub-space</th>
                <th class="p-3 text-left">Activity</th>
                <th class="p-3 text-left">Contractor</th>
                <th class="p-3 text-left">Total</th>
                <th class="p-3 text-left">Shift</th>
                <th class="p-3 text-left">Status</th>
                <th class="p-3 text-left">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($labourReports as $index => $report)
                <tr class="border-t">
                    <td class="p-3">
                        {{ $labourReports->firstItem() + $index }}
                    </td>

                    <td class="p-3">
                        {{ $report->entry_date }}
                    </td>

                    <td class="p-3">
                        {{ $report->project?->project_name ?? '-' }}
                    </td>

                    <td class="p-3">
                        {{ $report->block?->name ?? '-' }}
                    </td>

                    <td class="p-3">
                        {{ $report->floor?->name ?? '-' }}
                    </td>

                    <td class="p-3">
                        {{ $report->unit?->name ?? '-' }}
                    </td>

                    <td class="p-3">
                        {{ $report->room?->name ?? '-' }}
                    </td>

                    <td class="p-3">
                        {{ $report->subspace?->name ?? '-' }}
                    </td>

                    <td class="p-3">
                        {{ $report->activity?->activity_name ?? '-' }}
                    </td>

                    <td class="p-3">
                        {{ $report->contractor?->contractor_name ?? '-' }}
                    </td>

                    <td class="p-3 font-bold">
                        {{ $report->total_labour }}
                    </td>

                    <td class="p-3">
                        {{ $report->shift ?? '-' }}
                    </td>

                    <td class="p-3">
                        {{ $report->status }}
                    </td>
                    <td class="p-3 whitespace-nowrap">

    <div class="flex gap-2">

    <a href="{{ route('labour-reports.show', $report) }}"
   class="bg-gray-700 hover:bg-gray-800 text-white px-3 py-1 rounded">
    View
</a>

        {{-- Draft --}}
        @if($report->status === 'Draft')

            <a href="{{ route('labour-reports.edit', $report) }}"
               class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">
                Edit
            </a>

            <form method="POST"
                  action="{{ route('labour-reports.submit', $report) }}">
                @csrf
                @method('PATCH')

                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded">
                    Submit
                </button>
            </form>

        {{-- Submitted --}}
        @elseif($report->status === 'Submitted')

            @if(in_array(auth()->user()->role->name, ['Admin', 'PMO', 'DGM']))

                <form method="POST"
                      action="{{ route('labour-reports.approve', $report) }}">
                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                        Approve
                    </button>
                </form>

            @else

                <span class="text-orange-600 font-semibold">
                    Pending Approval
                </span>

            @endif

        {{-- Approved --}}
        @elseif($report->status === 'Approved')

            <span class="text-green-700 font-semibold">
                Approved
            </span>

        @endif

    </div>

</td>
                </tr>
            @empty
                <tr>
                    <td colspan="14"
                        class="p-4 text-center text-gray-500">
                        No labour reports found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>

<div class="mt-4">
    {{ $labourReports->links() }}
</div>

@endsection