@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Projects</h1>
        <p class="text-xs text-gray-500">Project Master v2.0</p>
    </div>

    <a href="{{ route('projects.create') }}"
       class="bg-blue-600 text-white px-3 py-2 rounded text-xs">
        + Create Project
    </a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-700 p-3 rounded mb-3 text-sm">
        {{ session('success') }}
    </div>
@endif

<form method="GET" class="bg-white rounded-md shadow-sm p-3 mb-3">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="Search code, project, client, location"
               class="border rounded px-2 py-1.5 text-xs h-9 md:col-span-2">

        <select name="status" class="border rounded px-2 py-1.5 text-xs h-9">
            <option value="">All Status</option>
            @foreach($projectStatuses as $status)
                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                    {{ $status }}
                </option>
            @endforeach
        </select>

        <div class="flex gap-2">
            <button class="bg-gray-800 text-white px-3 py-1.5 rounded text-xs">
                Filter
            </button>

            <a href="{{ route('projects.index') }}"
               class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded text-xs">
                Reset
            </a>
        </div>
    </div>
</form>

<div class="bg-white rounded-md shadow-sm overflow-hidden">
    <table class="w-full text-xs">
        <thead class="bg-gray-100 text-gray-700">
            <tr>
                <th class="px-3 py-2 text-left">Code</th>
                <th class="px-3 py-2 text-left">Project</th>
                <th class="px-3 py-2 text-left">Client</th>
                <th class="px-3 py-2 text-left">Type</th>
                <th class="px-3 py-2 text-left">PMO/DGM</th>
                <th class="px-3 py-2 text-left">Engineers</th>
                <th class="px-3 py-2 text-left">Status</th>
                <th class="px-3 py-2 text-left">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($projects as $project)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-3 py-2 font-semibold">
                        {{ $project->project_code }}
                    </td>

                    <td class="px-3 py-2">
                        <div class="font-semibold text-gray-900">
                            {{ $project->project_name }}
                        </div>
                        <div class="text-gray-500">
                            {{ $project->location }}
                        </div>
                    </td>

                    <td class="px-3 py-2">
                        <div>{{ $project->client_name ?? '-' }}</div>
                        <div class="text-gray-500">{{ $project->client_mobile }}</div>
                    </td>

                    <td class="px-3 py-2">
                        <div>{{ $project->project_type ?? '-' }}</div>
                        <div class="text-gray-500">{{ $project->structure_type }}</div>
                    </td>

                    <td class="px-3 py-2">
                        {{ optional($project->assignedPmo)->name ?? '-' }}
                    </td>

                    <td class="px-3 py-2">
                        {{ $project->users->pluck('name')->join(', ') ?: '-' }}
                    </td>

                    <td class="px-3 py-2">
                        @include('projects.partials.status-badge', ['status' => $project->status])
                    </td>

                    <td class="px-3 py-2">
                        <div class="flex gap-2">
                            <a href="{{ route('projects.edit', $project->id) }}"
                               class="bg-yellow-500 text-white px-2 py-1 rounded">
                                Edit
                            </a>

                            <form action="{{ route('projects.destroy', $project->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Delete this project?')">
                                @csrf
                                @method('DELETE')

                                <button class="bg-red-600 text-white px-2 py-1 rounded">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-3 py-6 text-center text-gray-500">
                        No projects found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection