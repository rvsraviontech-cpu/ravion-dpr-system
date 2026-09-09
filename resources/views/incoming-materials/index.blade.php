@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-[1600px] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Incoming Materials</h1>
            <p class="mt-1 text-sm text-slate-500">
                Head Office dispatches awaiting site receipt confirmation.
            </p>
        </div>

        <a href="{{ route('material-dispatches.index') }}"
           class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
            Dispatch Register
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('incoming-materials.index') }}"
              class="grid grid-cols-1 gap-3 md:grid-cols-12">
            <div class="md:col-span-5">
                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">
                    Search
                </label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Dispatch no., project, challan, vehicle..."
                       class="w-full rounded-lg border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">
            </div>

            <div class="md:col-span-4">
                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">
                    Project
                </label>
                <select name="project_id"
                        class="w-full rounded-lg border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="">All Projects</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}"
                            @selected((string) request('project_id') === (string) $project->id)>
                            {{ $project->project_code ? $project->project_code . ' - ' : '' }}{{ $project->project_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2 md:col-span-3">
                <button type="submit"
                        class="inline-flex flex-1 items-center justify-center rounded-lg bg-[#10212F] px-4 py-2.5 text-sm font-medium text-white hover:opacity-95">
                    Filter
                </button>
                <a href="{{ route('incoming-materials.index') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Dispatch</th>
                        <th class="px-4 py-3">Project</th>
                        <th class="px-4 py-3">Dispatch Date</th>
                        <th class="px-4 py-3">Expected</th>
                        <th class="px-4 py-3 text-right">Items</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($dispatches as $dispatch)
                        @php
                            $statusClass = match($dispatch->status) {
                                'Partially Received' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                'Received' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                default => 'bg-blue-50 text-blue-700 ring-blue-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 text-slate-500">
                                {{ $dispatches->firstItem() + $loop->index }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ $dispatch->dispatch_number }}</div>
                                @if ($dispatch->challan_number)
                                    <div class="mt-0.5 text-xs text-slate-500">Challan: {{ $dispatch->challan_number }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">{{ $dispatch->project_name }}</div>
                                @if ($dispatch->project?->project_code)
                                    <div class="mt-0.5 text-xs text-slate-500">{{ $dispatch->project->project_code }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                {{ optional($dispatch->dispatch_date)->format('d/m/Y') ?? $dispatch->dispatch_date }}
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                {{ optional($dispatch->expected_delivery_date)->format('d/m/Y') ?? ($dispatch->expected_delivery_date ?: '—') }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-slate-700">
                                {{ $dispatch->items->count() }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $statusClass }}">
                                    {{ $dispatch->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('incoming-materials.receive', $dispatch) }}"
                                   class="inline-flex items-center justify-center rounded-lg bg-[#10212F] px-3.5 py-2 text-xs font-semibold text-white hover:opacity-95">
                                    Receive Materials
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-14 text-center">
                                <div class="text-sm font-medium text-slate-700">No incoming materials pending.</div>
                                <div class="mt-1 text-xs text-slate-500">Dispatched material notes will appear here until fully accounted for.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($dispatches->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">
                {{ $dispatches->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
