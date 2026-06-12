@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Plan vs Actual Report</h1>
        <p class="text-gray-500 mt-1">
            Compare approved tomorrow plans against actual DPR work completed.
        </p>
    </div>
</div>

<div class="bg-white p-4 rounded shadow mb-6">
    <form method="GET"
          action="{{ route('plan-vs-actual.index') }}"
          class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div>
            <label class="block text-sm font-semibold mb-1">From Date</label>
            <input type="date"
                   name="from_date"
                   value="{{ request('from_date') }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">To Date</label>
            <input type="date"
                   name="to_date"
                   value="{{ request('to_date') }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Project</label>
            <select name="project_id" class="border p-2 rounded w-full">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}"
                        {{ request('project_id') == $project->id ? 'selected' : '' }}>
                        {{ $project->project_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Filter
            </button>

            <a href="{{ route('plan-vs-actual.index') }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                Clear
            </a>
        </div>

    </form>
</div>

@php
    $totalPlanned = collect($reportRows)->sum('planned_qty');
    $totalActual = collect($reportRows)->sum('actual_qty');
    $totalVariance = $totalActual - $totalPlanned;
    $overallAchievement = $totalPlanned > 0 ? round(($totalActual / $totalPlanned) * 100, 2) : 0;
@endphp

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Total Planned Qty</p>
        <p class="text-2xl font-bold text-blue-700">
            {{ number_format($totalPlanned, 2) }}
        </p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Total Actual Qty</p>
        <p class="text-2xl font-bold text-green-700">
            {{ number_format($totalActual, 2) }}
        </p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Total Variance</p>
        <p class="text-2xl font-bold {{ $totalVariance < 0 ? 'text-red-700' : 'text-green-700' }}">
            {{ number_format($totalVariance, 2) }}
        </p>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500 text-sm">Achievement %</p>
        <p class="text-2xl font-bold {{ $overallAchievement < 80 ? 'text-red-700' : 'text-green-700' }}">
            {{ $overallAchievement }}%
        </p>
    </div>

</div>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">

        <table class="min-w-full text-sm">

            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="p-3 text-left">#</th>
                    <th class="p-3 text-left">Date</th>
                    <th class="p-3 text-left">Project</th>
                    <th class="p-3 text-left">Location</th>
                    <th class="p-3 text-left">Activity</th>
                    <th class="p-3 text-center">Planned</th>
                    <th class="p-3 text-center">Actual</th>
                    <th class="p-3 text-center">Variance</th>
                    <th class="p-3 text-center">Achievement</th>
                    <th class="p-3 text-center">Status</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">

            @forelse($reportRows as $index => $row)

                @php
                    $plan = $row['plan'];
                @endphp

                <tr class="hover:bg-gray-50">

                    <td class="p-3">
                        {{ $index + 1 }}
                    </td>

                    <td class="p-3 whitespace-nowrap">
                        {{ $plan->planned_date ?? '-' }}
                    </td>

                    <td class="p-3 whitespace-nowrap">
                        {{ $plan->project?->project_name ?? '-' }}
                    </td>

                    <td class="p-3 min-w-[220px]">
                        {{ $plan->block?->name ?? '-' }}
                        /
                        {{ $plan->floor?->name ?? '-' }}
                        /
                        {{ $plan->unit?->name ?? '-' }}
                        /
                        {{ $plan->room?->name ?? '-' }}
                    </td>

                    <td class="p-3 min-w-[180px]">
                        {{ $plan->activity?->activity_name ?? '-' }}
                    </td>

                    <td class="p-3 text-center font-bold text-blue-700">
                        {{ number_format($row['planned_qty'], 2) }}
                    </td>

                    <td class="p-3 text-center font-bold text-green-700">
                        {{ number_format($row['actual_qty'], 2) }}
                    </td>

                    <td class="p-3 text-center font-bold
                        {{ $row['variance_qty'] < 0 ? 'text-red-700' : 'text-green-700' }}">
                        {{ number_format($row['variance_qty'], 2) }}
                    </td>

                    <td class="p-3 text-center font-bold">
                        {{ $row['achievement_percent'] }}%
                    </td>

                    <td class="p-3 text-center">
                        @if($row['status'] === 'Not Started')
                            <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">Not Started</span>
                        @elseif($row['status'] === 'Behind')
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Behind</span>
                        @elseif($row['status'] === 'On Track')
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">On Track</span>
                        @else
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Ahead</span>
                        @endif
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="10" class="p-6 text-center text-gray-500">
                        No approved plans found for comparison.
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>
</div>

@endsection