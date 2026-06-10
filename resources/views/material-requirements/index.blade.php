@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            Material Requirements
        </h1>
        <p class="text-gray-500 mt-1">
            Manage project-wise material requirements and approvals.
        </p>
    </div>

    <a href="{{ route('material-requirements.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
        + Add Requirement
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
          action="{{ route('material-requirements.index') }}"
          class="grid grid-cols-1 md:grid-cols-3 gap-4">

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

            <a href="{{ route('material-requirements.index') }}"
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
                    <th class="p-3 text-left">Block</th>
                    <th class="p-3 text-left">Material</th>
                    <th class="p-3 text-center">Required Qty</th>
                    <th class="p-3 text-left">Unit</th>
                    <th class="p-3 text-left">Priority</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">

            @forelse($requirements as $index => $requirement)

                <tr class="hover:bg-gray-50">

                    <td class="p-3">
                        {{ $requirements->firstItem() + $index }}
                    </td>

                    <td class="p-3 whitespace-nowrap">
                        {{ $requirement->required_date ?? '-' }}
                    </td>

                    <td class="p-3 whitespace-nowrap">
                        {{ $requirement->project?->project_name ?? '-' }}
                    </td>

                    <td class="p-3 whitespace-nowrap">
                        {{ $requirement->block?->name ?? '-' }}
                    </td>

                    <td class="p-3">
                        {{ $requirement->material?->material_name ?? '-' }}
                    </td>

                    <td class="p-3 text-center font-bold">
                        {{ number_format($requirement->required_quantity, 2) }}
                    </td>

                    <td class="p-3">
                        {{ $requirement->unit ?? '-' }}
                    </td>

                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-xs
                            {{ $requirement->priority === 'Low' ? 'bg-gray-100 text-gray-800' : '' }}
                            {{ $requirement->priority === 'Normal' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $requirement->priority === 'High' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $requirement->priority === 'Urgent' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ $requirement->priority }}
                        </span>
                    </td>

                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-xs
                            {{ $requirement->status === 'Draft' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $requirement->status === 'Submitted' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $requirement->status === 'Approved' ? 'bg-green-100 text-green-800' : '' }}">
                            {{ $requirement->status }}
                        </span>
                    </td>

                    <td class="p-3 whitespace-nowrap">
                        <div class="flex gap-2">

                            <a href="{{ route('material-requirements.show', $requirement) }}"
                               class="bg-gray-700 hover:bg-gray-800 text-white px-3 py-1 rounded">
                                View
                            </a>

                            @if($requirement->status === 'Draft')
                                <a href="{{ route('material-requirements.edit', $requirement) }}"
                                   class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">
                                    Edit
                                </a>

                                <form method="POST"
                                      action="{{ route('material-requirements.submit', $requirement) }}"
                                      class="inline">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit"
                                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded">
                                        Submit
                                    </button>
                                </form>
                            @endif

                            @if($requirement->status === 'Submitted'
                                && in_array(auth()->user()->role->name, ['Admin','PMO','DGM']))

                                <form method="POST"
                                      action="{{ route('material-requirements.approve', $requirement) }}"
                                      class="inline">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                                        Approve
                                    </button>
                                </form>

                            @endif

                        </div>
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="10" class="p-6 text-center text-gray-500">
                        No material requirements found.
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>
</div>

<div class="mt-4">
    {{ $requirements->links() }}
</div>

@endsection