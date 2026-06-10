@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Site Issues / Delays</h1>
        <p class="text-gray-500 mt-1">
            Track site issues, delays, risks, dependencies and escalations.
        </p>
    </div>

    <a href="{{ route('site-issues.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
        + Add Site Issue
    </a>
</div>

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

<div class="bg-white p-4 rounded shadow mb-6">
    <form method="GET"
          action="{{ route('site-issues.index') }}"
          class="grid grid-cols-1 md:grid-cols-5 gap-4">

        <div>
            <label class="block text-sm font-semibold mb-1">Issue Date</label>
            <input type="date"
                   name="issue_date"
                   value="{{ request('issue_date') }}"
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
            <label class="block text-sm font-semibold mb-1">Priority</label>
            <select name="priority" class="border p-2 rounded w-full">
                <option value="">All Priority</option>
                <option value="Low" {{ request('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                <option value="Medium" {{ request('priority') == 'Medium' ? 'selected' : '' }}>Medium</option>
                <option value="High" {{ request('priority') == 'High' ? 'selected' : '' }}>High</option>
                <option value="Critical" {{ request('priority') == 'Critical' ? 'selected' : '' }}>Critical</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Status</label>
            <select name="status" class="border p-2 rounded w-full">
                <option value="">All Status</option>
                <option value="Open" {{ request('status') == 'Open' ? 'selected' : '' }}>Open</option>
                <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Filter
            </button>

            <a href="{{ route('site-issues.index') }}"
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
                    <th class="p-3 text-left">Date</th>
                    <th class="p-3 text-left">Project</th>
                    <th class="p-3 text-left">Issue</th>
                    <th class="p-3 text-left">Type</th>
                    <th class="p-3 text-left">Priority</th>
                    <th class="p-3 text-left">Responsible</th>
                    <th class="p-3 text-left">Target Date</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">Escalation</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">

            @forelse($siteIssues as $index => $issue)

                <tr class="hover:bg-gray-50">

                    <td class="p-3">
                        {{ $siteIssues->firstItem() + $index }}
                    </td>

                    <td class="p-3 whitespace-nowrap">
                        {{ $issue->issue_date ?? '-' }}
                    </td>

                    <td class="p-3 whitespace-nowrap">
                        {{ $issue->project?->project_name ?? '-' }}
                    </td>

                    <td class="p-3 min-w-[220px] font-semibold">
                        {{ $issue->title ?? '-' }}
                    </td>

                    <td class="p-3 whitespace-nowrap">
                        {{ $issue->issue_type }}
                    </td>

                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-xs
                            {{ $issue->priority === 'Low' ? 'bg-gray-100 text-gray-800' : '' }}
                            {{ $issue->priority === 'Medium' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $issue->priority === 'High' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $issue->priority === 'Critical' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ $issue->priority }}
                        </span>
                    </td>

                    <td class="p-3 whitespace-nowrap">
                        {{ $issue->responsible_person ?? '-' }}
                    </td>

                    <td class="p-3 whitespace-nowrap">
                        {{ $issue->target_closure_date ?? '-' }}
                    </td>

                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-xs
                            {{ $issue->status === 'Open' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $issue->status === 'In Progress' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $issue->status === 'Resolved' ? 'bg-green-100 text-green-800' : '' }}">
                            {{ $issue->status }}
                        </span>
                    </td>

                    <td class="p-3 whitespace-nowrap">
                        @if($issue->escalated_to_management)
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Management</span>
                        @elseif($issue->escalated_to_pmo)
                            <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs">PMO</span>
                        @else
                            <span class="text-gray-500">-</span>
                        @endif
                    </td>

                    <td class="p-3 whitespace-nowrap">
                        <div class="flex gap-2">
                            <a href="{{ route('site-issues.show', $issue) }}"
                               class="bg-gray-700 hover:bg-gray-800 text-white px-3 py-1 rounded">
                                View
                            </a>

                            <a href="{{ route('site-issues.edit', $issue) }}"
                               class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">
                                Edit
                            </a>
                        </div>
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="11" class="p-6 text-center text-gray-500">
                        No site issues found.
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>
</div>

<div class="mt-4">
    {{ $siteIssues->links() }}
</div>

@endsection