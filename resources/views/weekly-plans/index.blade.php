@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Weekly Plans</h1>
        <p class="text-gray-500 mt-1">
            Manage weekly project plans, targets, resources and progress status.
        </p>
    </div>

    <a href="{{ route('weekly-plans.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
        + Add Weekly Plan
    </a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white p-4 rounded shadow mb-6">
    <form method="GET"
          action="{{ route('weekly-plans.index') }}"
          class="grid grid-cols-1 md:grid-cols-5 gap-4">

        <div>
            <label class="block text-sm font-semibold mb-1">Week Start</label>
            <input type="date"
                   name="week_start_date"
                   value="{{ request('week_start_date') }}"
                   class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Week End</label>
            <input type="date"
                   name="week_end_date"
                   value="{{ request('week_end_date') }}"
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

        <div>
            <label class="block text-sm font-semibold mb-1">Status</label>
            <select name="status" class="border p-2 rounded w-full">
                <option value="">All Status</option>
                <option value="Planned" {{ request('status') == 'Planned' ? 'selected' : '' }}>Planned</option>
                <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                <option value="Delayed" {{ request('status') == 'Delayed' ? 'selected' : '' }}>Delayed</option>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Filter
            </button>

            <a href="{{ route('weekly-plans.index') }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                Clear
            </a>
        </div>

    </form>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">

        <table class="min-w-full text-sm">

            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="p-3 text-left">#</th>
                    <th class="p-3 text-left">Week</th>
                    <th class="p-3 text-left">Project</th>
                    <th class="p-3 text-left">Activity</th>
                    <th class="p-3 text-center">Planned Qty</th>
                    <th class="p-3 text-left">Unit</th>
                    <th class="p-3 text-center">Labour</th>
                    <th class="p-3 text-left">Engineer</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">

                @forelse($weeklyPlans as $index => $plan)

                    <tr class="hover:bg-gray-50">

                        <td class="p-3">
                            {{ $weeklyPlans->firstItem() + $index }}
                        </td>

                        <td class="p-3 whitespace-nowrap">
                            {{ $plan->week_start_date }}
                            <br>
                            <span class="text-xs text-gray-500">
                                to {{ $plan->week_end_date }}
                            </span>
                        </td>

                        <td class="p-3 whitespace-nowrap">
                            {{ $plan->project?->project_name ?? '-' }}
                        </td>

                        <td class="p-3 min-w-[180px]">
                            {{ $plan->activity?->activity_name ?? '-' }}
                        </td>

                        <td class="p-3 text-center font-bold">
                            {{ number_format($plan->planned_quantity, 2) }}
                        </td>

                        <td class="p-3">
                            {{ $plan->unit ?? '-' }}
                        </td>

                        <td class="p-3 text-center">
                            {{ $plan->planned_labour ?? 0 }}
                        </td>

                        <td class="p-3 whitespace-nowrap">
                            {{ $plan->user?->name ?? '-' }}
                        </td>

                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs
                                {{ $plan->status === 'Planned' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $plan->status === 'In Progress' ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $plan->status === 'Completed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $plan->status === 'Delayed' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ $plan->status }}
                            </span>
                        </td>

                        <td class="p-3 whitespace-nowrap">
                            <div class="flex gap-2">

                                <a href="{{ route('weekly-plans.show', $plan) }}"
                                   class="bg-gray-700 hover:bg-gray-800 text-white px-3 py-1 rounded">
                                    View
                                </a>

                                <a href="{{ route('weekly-plans.edit', $plan) }}"
                                   class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">
                                    Edit
                                </a>

                            </div>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="10" class="p-6 text-center text-gray-500">
                            No weekly plans found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>
</div>

<div class="mt-4">
    {{ $weeklyPlans->links() }}
</div>

@endsection