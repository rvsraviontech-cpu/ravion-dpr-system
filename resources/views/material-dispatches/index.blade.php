@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-[1600px] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Head Office Dispatch</h1>
            <p class="mt-1 text-sm text-gray-500">
                Materials sent from Head Office / central store to project sites.
            </p>
        </div>

        <a href="{{ route('material-dispatches.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-[#10212F] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
            + New Dispatch
        </a>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET"
          action="{{ route('material-dispatches.index') }}"
          class="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">Search</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Dispatch no., project, challan..."
                       class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">Project</label>
                <select name="project_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="">All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}"
                            {{ (string) request('project_id') === (string) $project->id ? 'selected' : '' }}>
                            {{ $project->project_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                <select name="status"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="flex-1 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('material-dispatches.index') }}"
                   class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="w-14 px-4 py-3 text-center">#</th>
                        <th class="min-w-[160px] px-4 py-3 text-left">Dispatch No.</th>
                        <th class="min-w-[250px] px-4 py-3 text-left">Project</th>
                        <th class="w-32 px-4 py-3 text-left">Date</th>
                        <th class="min-w-[160px] px-4 py-3 text-left">Vehicle / Challan</th>
                        <th class="w-24 px-4 py-3 text-center">Items</th>
                        <th class="w-40 px-4 py-3 text-left">Status</th>
                        <th class="w-44 px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse($dispatches as $dispatch)
                        @php
                            $statusClass = match($dispatch->status) {
                                'Draft' => 'bg-gray-100 text-gray-700',
                                'Dispatched' => 'bg-blue-100 text-blue-700',
                                'Partially Received' => 'bg-amber-100 text-amber-700',
                                'Received' => 'bg-emerald-100 text-emerald-700',
                                'Closed' => 'bg-slate-200 text-slate-700',
                                'Cancelled' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-center text-gray-500">
                                {{ $dispatches->firstItem() + $loop->index }}
                            </td>

                            <td class="px-4 py-3">
                                <a href="{{ route('material-dispatches.show', $dispatch) }}"
                                   class="font-bold text-blue-700 hover:underline">
                                    {{ $dispatch->dispatch_number }}
                                </a>
                            </td>

                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-800">{{ $dispatch->project_name }}</div>
                                @if($dispatch->project?->project_code)
                                    <div class="mt-0.5 text-xs text-gray-400">{{ $dispatch->project->project_code }}</div>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-gray-700">
                                {{ $dispatch->dispatch_date?->format('d/m/Y') }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="text-gray-700">{{ $dispatch->vehicle_number ?: '-' }}</div>
                                @if($dispatch->challan_number)
                                    <div class="mt-0.5 text-xs text-gray-400">Challan: {{ $dispatch->challan_number }}</div>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-center font-semibold text-gray-800">
                                {{ $dispatch->items_count }}
                            </td>

                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $dispatch->status }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('material-dispatches.show', $dispatch) }}"
                                       class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                        View
                                    </a>

                                    @if($dispatch->status === 'Draft')
                                        <a href="{{ route('material-dispatches.edit', $dispatch) }}"
                                           class="rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100">
                                            Edit
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">
                                No Head Office Dispatch records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dispatches->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $dispatches->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
