@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-base text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:py-2.5 sm:text-sm';
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">DPR Register</h1>
            <p class="mt-1 text-sm text-gray-500">
                Review Daily Progress Reports and their approval status.
            </p>
        </div>

        <a href="{{ route('dprs.create') }}"
           class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white shadow-sm hover:bg-blue-700 sm:w-auto">
            + Create DPR
        </a>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="mb-6" x-data="{ filtersOpen: false }">
        <button type="button"
                @click="filtersOpen = !filtersOpen"
                class="mb-3 flex w-full items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 text-left shadow-sm lg:hidden">
            <div>
                <div class="text-sm font-bold text-gray-800">Filters</div>
                <div class="text-xs text-gray-500">Project, status, engineer and date range</div>
            </div>

            <svg class="h-5 w-5 text-gray-500 transition-transform"
                 :class="filtersOpen ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <form method="GET"
              action="{{ route('dprs.index') }}"
              x-show="filtersOpen"
              x-cloak
              class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm lg:hidden">

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Project</label>
                    <select name="project_id" class="{{ $inputClass }}">
                        <option value="">All Projects</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ (string) request('project_id') === (string) $project->id ? 'selected' : '' }}>
                                {{ $project->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Status</label>
                    <select name="status" class="{{ $inputClass }}">
                        <option value="">All Status</option>
                        <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>Approved</option>
                        <option value="Rejected" {{ request('status') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Engineer</label>
                    <select name="user_id" class="{{ $inputClass }}">
                        <option value="">All Engineers</option>
                        @foreach($engineers as $engineer)
                            <option value="{{ $engineer->id }}" {{ (string) request('user_id') === (string) $engineer->id ? 'selected' : '' }}>
                                {{ $engineer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">From Date</label>
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">To Date</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="{{ $inputClass }}">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white">
                        Apply
                    </button>

                    <a href="{{ route('dprs.index') }}" class="rounded-xl bg-gray-500 px-4 py-3 text-center text-sm font-semibold text-white">
                        Clear
                    </a>
                </div>
            </div>
        </form>

        <form method="GET"
              action="{{ route('dprs.index') }}"
              class="hidden rounded-xl border border-gray-200 bg-white p-5 shadow-sm lg:block">

            <div class="grid grid-cols-6 gap-4">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Project</label>
                    <select name="project_id" class="{{ $inputClass }}">
                        <option value="">All Projects</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ (string) request('project_id') === (string) $project->id ? 'selected' : '' }}>
                                {{ $project->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Status</label>
                    <select name="status" class="{{ $inputClass }}">
                        <option value="">All Status</option>
                        <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>Approved</option>
                        <option value="Rejected" {{ request('status') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Engineer</label>
                    <select name="user_id" class="{{ $inputClass }}">
                        <option value="">All Engineers</option>
                        @foreach($engineers as $engineer)
                            <option value="{{ $engineer->id }}" {{ (string) request('user_id') === (string) $engineer->id ? 'selected' : '' }}>
                                {{ $engineer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="{{ $inputClass }}">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">
                        Apply
                    </button>

                    <a href="{{ route('dprs.index') }}" class="rounded-lg bg-gray-500 px-4 py-2.5 text-sm font-semibold text-white">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Mobile DPR Cards --}}
    <div class="space-y-4 lg:hidden">
        @forelse($dprs as $dpr)
            @php
                $statusClass = match($dpr->status) {
                    'Approved' => 'bg-green-100 text-green-800',
                    'Rejected' => 'bg-red-100 text-red-800',
                    default => 'bg-yellow-100 text-yellow-800',
                };
            @endphp

            <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="bg-[#0F2A52] px-4 py-4 text-white">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-medium text-blue-100">
                                DPR #{{ $dpr->id }} · {{ $dpr->dpr_date }}
                            </div>
                            <h2 class="mt-1 truncate text-base font-bold">
                                {{ $dpr->project?->project_name ?? '-' }}
                            </h2>
                        </div>

                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold {{ $statusClass }}">
                            {{ $dpr->status }}
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-gray-50 px-3 py-3">
                            <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Engineer</div>
                            <div class="mt-1 truncate text-sm font-semibold text-gray-800">
                                {{ $dpr->user?->name ?? '-' }}
                            </div>
                        </div>

                        <div class="rounded-xl bg-gray-50 px-3 py-3">
                            <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Weather</div>
                            <div class="mt-1 truncate text-sm font-semibold text-gray-800">
                                {{ $dpr->weather ?: '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid {{ $dpr->status !== 'Approved' ? 'grid-cols-2' : 'grid-cols-1' }} gap-2">
                        <a href="{{ route('dprs.show', $dpr) }}"
                           class="rounded-xl bg-blue-600 px-4 py-3 text-center text-sm font-semibold text-white">
                            View DPR
                        </a>

                        @if($dpr->status !== 'Approved')
                            <a href="{{ route('dprs.edit', $dpr) }}"
                               class="rounded-xl bg-amber-500 px-4 py-3 text-center text-sm font-semibold text-white">
                                Edit
                            </a>
                        @endif
                    </div>

                    @if($dpr->status !== 'Approved')
                        <details class="mt-3 rounded-xl border border-red-100 bg-red-50">
                            <summary class="cursor-pointer px-4 py-3 text-center text-xs font-semibold text-red-700">
                                Delete DPR
                            </summary>

                            <form action="{{ route('dprs.destroy', $dpr) }}"
                                  method="POST"
                                  class="border-t border-red-100 p-3">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        onclick="return confirm('Delete this DPR?')"
                                        class="w-full rounded-lg bg-red-600 px-4 py-3 text-sm font-semibold text-white">
                                    Confirm Delete
                                </button>
                            </form>
                        </details>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white px-5 py-10 text-center shadow-sm">
                <div class="font-semibold text-gray-700">No DPRs found</div>
                <p class="mt-2 text-sm text-gray-500">Adjust the filters or create a new DPR.</p>
            </div>
        @endforelse

        @if(method_exists($dprs, 'hasPages') && $dprs->hasPages())
            <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 shadow-sm">
                {{ $dprs->links() }}
            </div>
        @endif
    </div>

    {{-- Desktop DPR Table --}}
    <div class="hidden overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm lg:block">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="p-4 text-left">ID</th>
                        <th class="p-4 text-left">Project</th>
                        <th class="p-4 text-left">Engineer</th>
                        <th class="p-4 text-left">Date</th>
                        <th class="p-4 text-left">Weather</th>
                        <th class="p-4 text-left">Status</th>
                        <th class="p-4 text-left">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse($dprs as $dpr)
                        @php
                            $statusClass = match($dpr->status) {
                                'Approved' => 'bg-green-100 text-green-800',
                                'Rejected' => 'bg-red-100 text-red-800',
                                default => 'bg-yellow-100 text-yellow-800',
                            };
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="p-4">{{ $dpr->id }}</td>
                            <td class="p-4 font-medium text-gray-800">{{ $dpr->project?->project_name ?? '-' }}</td>
                            <td class="p-4">{{ $dpr->user?->name ?? '-' }}</td>
                            <td class="whitespace-nowrap p-4">{{ $dpr->dpr_date }}</td>
                            <td class="p-4">{{ $dpr->weather ?: '-' }}</td>
                            <td class="p-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $dpr->status }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('dprs.show', $dpr) }}"
                                       class="rounded bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white">
                                        View
                                    </a>

                                    @if($dpr->status !== 'Approved')
                                        <a href="{{ route('dprs.edit', $dpr) }}"
                                           class="rounded bg-yellow-500 px-3 py-1.5 text-xs font-semibold text-white">
                                            Edit
                                        </a>

                                        <form action="{{ route('dprs.destroy', $dpr) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    onclick="return confirm('Delete this DPR?')"
                                                    class="rounded bg-red-600 px-3 py-1.5 text-xs font-semibold text-white">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500">
                                No DPRs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($dprs, 'hasPages') && $dprs->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $dprs->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
