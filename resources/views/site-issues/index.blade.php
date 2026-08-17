@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-base text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:py-2 sm:text-sm';
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">
                Site Issues Register
            </h1>

            <p class="mt-1 text-gray-500">
                Track site issues, delays, risks, responsibility, escalation and closure status.
            </p>
        </div>

        <a href="{{ route('site-issues.create') }}"
           class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white shadow-sm hover:bg-blue-700 sm:w-auto sm:py-2.5">
            + Add Site Issue
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

    <div class="mb-6" x-data="{ filtersOpen: false }">
        <button type="button"
                @click="filtersOpen = !filtersOpen"
                class="mb-3 flex w-full items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 text-left shadow-sm lg:hidden">
            <div>
                <div class="text-sm font-bold text-gray-800">Filters</div>
                <div class="text-xs text-gray-500">Date, project, priority, status and DPR link</div>
            </div>
            <svg class="h-5 w-5 text-gray-500 transition-transform"
                 :class="filtersOpen ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="filtersOpen" x-cloak class="lg:hidden">
    <form method="GET"
          action="{{ route('site-issues.index') }}"
          class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-7">

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Issue Date
                </label>

                <input type="date"
                       name="issue_date"
                       value="{{ request('issue_date') }}"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Project
                </label>

                <select name="project_id"
                        class="{{ $inputClass }}">
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
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Priority
                </label>

                <select name="priority"
                        class="{{ $inputClass }}">
                    <option value="">All Priorities</option>

                    @foreach($priorities as $priority)
                        <option value="{{ $priority }}"
                            {{ request('priority') === $priority ? 'selected' : '' }}>
                            {{ $priority }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Status
                </label>

                <select name="status"
                        class="{{ $inputClass }}">
                    <option value="">All Statuses</option>

                    @foreach($statuses as $status)
                        <option value="{{ $status }}"
                            {{ request('status') === $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    DPR Link
                </label>

                <select name="dpr_link"
                        class="{{ $inputClass }}">
                    <option value="">All</option>

                    <option value="unlinked"
                        {{ request('dpr_link') === 'unlinked' ? 'selected' : '' }}>
                        Not Linked
                    </option>

                    <option value="linked"
                        {{ request('dpr_link') === 'linked' ? 'selected' : '' }}>
                        Linked
                    </option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Search
                </label>

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="{{ $inputClass }}"
                       placeholder="Title, project, activity">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="w-full rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 sm:w-auto sm:py-2.5">
                    Filter
                </button>

                <a href="{{ route('site-issues.index') }}"
                   class="w-full rounded-lg bg-gray-600 px-5 py-3 text-center text-sm font-semibold text-white hover:bg-gray-700 sm:w-auto sm:py-2.5">
                    Clear
                </a>
            </div>

        </div>
    </form>
        </div>

        <div class="hidden lg:block">
    <form method="GET"
          action="{{ route('site-issues.index') }}"
          class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-7">

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Issue Date
                </label>

                <input type="date"
                       name="issue_date"
                       value="{{ request('issue_date') }}"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Project
                </label>

                <select name="project_id"
                        class="{{ $inputClass }}">
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
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Priority
                </label>

                <select name="priority"
                        class="{{ $inputClass }}">
                    <option value="">All Priorities</option>

                    @foreach($priorities as $priority)
                        <option value="{{ $priority }}"
                            {{ request('priority') === $priority ? 'selected' : '' }}>
                            {{ $priority }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Status
                </label>

                <select name="status"
                        class="{{ $inputClass }}">
                    <option value="">All Statuses</option>

                    @foreach($statuses as $status)
                        <option value="{{ $status }}"
                            {{ request('status') === $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    DPR Link
                </label>

                <select name="dpr_link"
                        class="{{ $inputClass }}">
                    <option value="">All</option>

                    <option value="unlinked"
                        {{ request('dpr_link') === 'unlinked' ? 'selected' : '' }}>
                        Not Linked
                    </option>

                    <option value="linked"
                        {{ request('dpr_link') === 'linked' ? 'selected' : '' }}>
                        Linked
                    </option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Search
                </label>

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="{{ $inputClass }}"
                       placeholder="Title, project, activity">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('site-issues.index') }}"
                   class="rounded-lg bg-gray-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700">
                    Clear
                </a>
            </div>

        </div>
    </form>
        </div>
    </div>

    {{-- Mobile Site Issues Register --}}
    <div class="space-y-4 lg:hidden">
        <div class="flex items-end justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Site Issues</h2>
                <p class="text-xs text-gray-500">{{ $siteIssues->total() }} matching issue{{ $siteIssues->total() === 1 ? '' : 's' }}</p>
            </div>
            <div class="text-xs text-gray-500">
                {{ $siteIssues->firstItem() ?? 0 }}–{{ $siteIssues->lastItem() ?? 0 }}
            </div>
        </div>

        @forelse($siteIssues as $index => $issue)
            @php
                $priorityClass = match($issue->priority) {
                    'Low' => 'bg-gray-100 text-gray-700',
                    'Medium' => 'bg-blue-100 text-blue-800',
                    'High' => 'bg-orange-100 text-orange-800',
                    'Critical' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-700',
                };

                $statusClass = match($issue->status) {
                    'Open' => 'bg-red-100 text-red-800',
                    'Assigned' => 'bg-blue-100 text-blue-800',
                    'In Progress' => 'bg-orange-100 text-orange-800',
                    'Resolved' => 'bg-green-100 text-green-800',
                    'Verified' => 'bg-emerald-100 text-emerald-800',
                    'Closed' => 'bg-slate-200 text-slate-800',
                    default => 'bg-gray-100 text-gray-700',
                };

                $isOverdue =
                    $issue->target_closure_date
                    && ! in_array($issue->status, ['Resolved', 'Verified', 'Closed'], true)
                    && $issue->target_closure_date->isPast();
            @endphp

            <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="bg-[#0F2A52] px-4 py-4 text-white">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-medium text-blue-100">
                                {{ $issue->issue_date?->format('d M Y') ?? '-' }}
                                @if($issue->issue_date) · {{ $issue->issue_date->format('D') }} @endif
                            </div>
                            <div class="mt-1 truncate text-sm font-semibold text-blue-100">
                                {{ $issue->project?->project_name ?? '-' }}
                            </div>
                            <h3 class="mt-2 text-base font-bold leading-5">{{ $issue->title }}</h3>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusClass }}">
                            {{ $issue->status }}
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $priorityClass }}">
                            {{ $issue->priority }} Priority
                        </span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                            {{ $issue->issue_type }}
                        </span>
                        @if($issue->escalated_to_management)
                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">Management</span>
                        @elseif($issue->escalated_to_pmo)
                            <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-800">PMO</span>
                        @endif
                    </div>

                    @if($issue->activity)
                        <div class="mt-3 rounded-xl bg-gray-50 px-3 py-3">
                            <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Related Activity</div>
                            <div class="mt-1 text-sm font-semibold text-gray-800">{{ $issue->activity->activity_name }}</div>
                        </div>
                    @endif

                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-gray-200 px-3 py-3">
                            <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Responsible</div>
                            <div class="mt-1 text-sm font-semibold text-gray-800">{{ $issue->responsible_person ?: '-' }}</div>
                        </div>
                        <div class="rounded-xl border {{ $isOverdue ? 'border-red-200 bg-red-50' : 'border-gray-200' }} px-3 py-3">
                            <div class="text-[10px] font-bold uppercase tracking-wide {{ $isOverdue ? 'text-red-600' : 'text-gray-500' }}">Target Closure</div>
                            <div class="mt-1 text-sm font-semibold {{ $isOverdue ? 'text-red-700' : 'text-gray-800' }}">
                                {{ $issue->target_closure_date?->format('d M Y') ?? '-' }}
                            </div>
                            @if($isOverdue)
                                <div class="mt-1 text-xs font-bold text-red-600">Overdue</div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3">
                        @if($issue->is_dpr_linked)
                            <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                Linked to DPR #{{ $issue->dpr_id }}
                            </span>
                        @else
                            <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">
                                Not Linked to DPR
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a href="{{ route('site-issues.show', $issue) }}"
                           class="rounded-xl bg-[#0F2A52] px-4 py-3 text-center text-sm font-semibold text-white">
                            View
                        </a>
                        <a href="{{ route('site-issues.edit', $issue) }}"
                           class="rounded-xl bg-amber-500 px-4 py-3 text-center text-sm font-semibold text-white">
                            Edit
                        </a>
                    </div>

                    @if(!$issue->is_dpr_linked)
                        <details class="mt-3">
                            <summary class="cursor-pointer text-center text-xs font-semibold text-red-600">More Actions</summary>
                            <form method="POST" action="{{ route('site-issues.destroy', $issue) }}" class="mt-3">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Delete this Site Issue and its photos?')"
                                        class="w-full rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                                    Delete Site Issue
                                </button>
                            </form>
                        </details>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white px-5 py-10 text-center shadow-sm">
                <div class="text-base font-semibold text-gray-700">No Site Issues found</div>
                <p class="mt-2 text-sm text-gray-500">Adjust the filters or report the first site issue.</p>
                <a href="{{ route('site-issues.create') }}"
                   class="mt-4 inline-flex rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white">
                    + Add Site Issue
                </a>
            </div>
        @endforelse

        @if($siteIssues->hasPages())
            <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 shadow-sm">
                {{ $siteIssues->links() }}
            </div>
        @endif
    </div>

    <div class="hidden lg:block overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-5 py-4">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">
                        Site Issues
                    </h2>

                    <p class="text-sm text-gray-500">
                        One row represents one independently tracked issue.
                    </p>
                </div>

                <div class="text-sm text-gray-500">
                    Showing
                    <span class="font-semibold text-gray-700">
                        {{ $siteIssues->firstItem() ?? 0 }}–{{ $siteIssues->lastItem() ?? 0 }}
                    </span>
                    of
                    <span class="font-semibold text-gray-700">
                        {{ $siteIssues->total() }}
                    </span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">

            <table class="min-w-[1150px] w-full text-sm">

                <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="w-14 px-4 py-3 text-center">#</th>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Project</th>
                        <th class="px-4 py-3 text-left">Issue</th>
                        <th class="px-4 py-3 text-left">Type / Priority</th>
                        <th class="px-4 py-3 text-left">Responsible</th>
                        <th class="px-4 py-3 text-left">Target</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Escalation</th>
                        <th class="w-32 px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                    @forelse($siteIssues as $index => $issue)

                        @php
                            $priorityClass = match($issue->priority) {
                                'Low' => 'bg-gray-100 text-gray-700',
                                'Medium' => 'bg-blue-100 text-blue-800',
                                'High' => 'bg-orange-100 text-orange-800',
                                'Critical' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-700',
                            };

                            $statusClass = match($issue->status) {
                                'Open' => 'bg-red-100 text-red-800',
                                'Assigned' => 'bg-blue-100 text-blue-800',
                                'In Progress' => 'bg-orange-100 text-orange-800',
                                'Resolved' => 'bg-green-100 text-green-800',
                                'Verified' => 'bg-emerald-100 text-emerald-800',
                                'Closed' => 'bg-slate-200 text-slate-800',
                                default => 'bg-gray-100 text-gray-700',
                            };

                            $isOverdue =
                                $issue->target_closure_date
                                && ! in_array(
                                    $issue->status,
                                    ['Resolved', 'Verified', 'Closed'],
                                    true
                                )
                                && $issue->target_closure_date->isPast();
                        @endphp

                        <tr class="align-top hover:bg-gray-50">

                            <td class="px-4 py-4 text-center text-gray-500">
                                {{ ($siteIssues->firstItem() ?? 1) + $index }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                <div class="font-semibold text-gray-800">
                                    {{ $issue->issue_date?->format('d/m/Y') ?? '-' }}
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <div class="font-semibold text-gray-800">
                                    {{ $issue->project?->project_name ?? '-' }}
                                </div>
                            </td>

                            <td class="max-w-sm px-4 py-4">
                                <div class="font-semibold text-gray-800">
                                    {{ $issue->title }}
                                </div>

                                @if($issue->activity)
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $issue->activity->activity_name }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                <div class="text-sm font-medium text-gray-800">
                                    {{ $issue->issue_type }}
                                </div>

                                <div class="mt-2">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $priorityClass }}">
                                        {{ $issue->priority }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                {{ $issue->responsible_person ?: '-' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                <div class="font-medium text-gray-800">
                                    {{ $issue->target_closure_date?->format('d/m/Y') ?? '-' }}
                                </div>

                                @if($isOverdue)
                                    <div class="mt-1 text-xs font-semibold text-red-600">
                                        Overdue
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $issue->status }}
                                </span>
                            </td>

                            <td class="px-4 py-4">
                                @if($issue->escalated_to_management)
                                    <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                        Management
                                    </span>
                                @elseif($issue->escalated_to_pmo)
                                    <span class="inline-flex rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-800">
                                        PMO
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex flex-col gap-2">

                                    <a href="{{ route('site-issues.show', $issue) }}"
                                       class="rounded-lg bg-slate-700 px-3 py-2 text-center text-xs font-semibold text-white hover:bg-slate-800">
                                        View
                                    </a>

                                    <a href="{{ route('site-issues.edit', $issue) }}"
                                       class="rounded-lg bg-amber-500 px-3 py-2 text-center text-xs font-semibold text-white hover:bg-amber-600">
                                        Edit
                                    </a>

                                    @if(!$issue->is_dpr_linked)
                                        <form method="POST"
                                              action="{{ route('site-issues.destroy', $issue) }}">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    onclick="return confirm('Delete this Site Issue and its photos?')"
                                                    class="w-full rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">
                                                Delete
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="10"
                                class="px-6 py-14 text-center">

                                <div class="mx-auto max-w-md">
                                    <div class="text-lg font-semibold text-gray-700">
                                        No Site Issues found
                                    </div>

                                    <p class="mt-2 text-sm text-gray-500">
                                        Adjust the filters or report the first site issue.
                                    </p>

                                    <a href="{{ route('site-issues.create') }}"
                                       class="mt-4 inline-flex rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                                        + Add Site Issue
                                    </a>
                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>

        </div>

        @if($siteIssues->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $siteIssues->links() }}
            </div>
        @endif

    </div>
</div>

@endsection
