@extends('layouts.app')

@section('content')
@php
    $filtersActive = request()->filled('project_id')
        || request()->filled('photo_date')
        || request()->filled('category')
        || request()->filled('status')
        || request()->filled('dpr_link')
        || request()->filled('search');
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Site Photos</h1>
            <p class="mt-2 text-sm text-slate-600">
                Daily site photo register. Open an entry to view photographs and complete details.
            </p>
        </div>

        <a href="{{ route('site-photos.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
            + Add Site Photos
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filters --}}
    <section class="mb-5 rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="font-semibold text-slate-900">Filter Site Photos</h2>
                <p class="mt-1 text-sm text-slate-500">Project, date, category, status, DPR linkage or search.</p>
            </div>

            @if($filtersActive)
                <a href="{{ route('site-photos.index') }}"
                   class="shrink-0 text-sm font-semibold text-blue-600 hover:text-blue-800">
                    Clear
                </a>
            @endif
        </div>

        <form method="GET" action="{{ route('site-photos.index') }}" class="p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
                <div>
                    <label for="project_id" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Project</label>
                    <select id="project_id" name="project_id"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Projects</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" @selected((string) request('project_id') === (string) $project->id)>
                                {{ $project->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="photo_date" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Photo Date</label>
                    <input id="photo_date" name="photo_date" type="date" value="{{ request('photo_date') }}"
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="category" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Category</label>
                    <select id="category" name="category"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Status</label>
                    <select id="status" name="status"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="dpr_link" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">DPR</label>
                    <select id="dpr_link" name="dpr_link"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Entries</option>
                        <option value="linked" @selected(request('dpr_link') === 'linked')>Linked</option>
                        <option value="unlinked" @selected(request('dpr_link') === 'unlinked')>Not Linked</option>
                    </select>
                </div>

                <div>
                    <label for="search" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Search</label>
                    <input id="search" name="search" type="text" value="{{ request('search') }}"
                           placeholder="Title, remarks, activity..."
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                    Apply Filters
                </button>

                @if($filtersActive)
                    <a href="{{ route('site-photos.index') }}"
                       class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </section>

    <div class="mb-3 text-sm text-slate-600">
        @if($sitePhotoEntries->total())
            Showing <span class="font-semibold text-slate-900">{{ $sitePhotoEntries->firstItem() }}</span>–
            <span class="font-semibold text-slate-900">{{ $sitePhotoEntries->lastItem() }}</span>
            of <span class="font-semibold text-slate-900">{{ $sitePhotoEntries->total() }}</span> entries
        @else
            No site photo entries found
        @endif
    </div>

    {{-- Compact mobile register --}}
    <div class="space-y-3 lg:hidden">
        @forelse($sitePhotoEntries as $entry)
            @php
                $statusClass = $entry->status === 'Submitted'
                    ? 'bg-emerald-100 text-emerald-800'
                    : 'bg-amber-100 text-amber-800';
            @endphp

            <article class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                {{ $entry->photo_date?->format('d M Y') ?? '—' }}
                            </div>
                            <div class="mt-1 truncate font-bold text-slate-900">
                                {{ $entry->project?->project_name ?? 'Unknown Project' }}
                            </div>
                        </div>

                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                            {{ $entry->status }}
                        </span>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                            {{ $entry->category }}
                        </span>

                        <a href="{{ route('site-photos.show', $entry) }}#photographic-evidence"
                           class="inline-flex items-center gap-1.5 rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700 hover:bg-violet-100"
                           aria-label="View {{ $entry->photos_count }} photographs">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-4 w-4" aria-hidden="true">
                                <rect x="3" y="5" width="18" height="14" rx="2" stroke-width="1.8"></rect>
                                <circle cx="8.5" cy="10" r="1.5" stroke-width="1.8"></circle>
                                <path d="M4.5 17l4.5-4 3.2 2.8 2.5-2.2 4.8 3.4" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            {{ $entry->photos_count }}
                        </a>
                    </div>

                    <div class="mt-3 text-sm leading-5 text-slate-700">
                        <span class="font-medium text-slate-500">Location:</span>
                        {{ $entry->location_path ?: 'General site' }}
                    </div>

                    <div class="mt-2 text-sm leading-5 text-slate-700">
                        <span class="font-medium text-slate-500">Work:</span>
                        {{ $entry->workActivity?->name ?? 'General site photography' }}
                    </div>

                    <div class="mt-3 flex items-center justify-between gap-3 border-t border-slate-100 pt-3 text-xs">
                        <span class="text-slate-500">
                            By <span class="font-semibold text-slate-700">{{ $entry->reporter?->name ?? '—' }}</span>
                        </span>
                        <span class="{{ $entry->dpr_id ? 'font-semibold text-blue-700' : 'text-slate-500' }}">
                            {{ $entry->dpr_id ? 'DPR #' . $entry->dpr_id : 'DPR: Not Linked' }}
                        </span>
                    </div>
                </div>

                <div class="grid {{ is_null($entry->dpr_id) ? 'grid-cols-2' : 'grid-cols-1' }} gap-px overflow-hidden rounded-b-xl border-t border-slate-100 bg-slate-100">
                    <a href="{{ route('site-photos.show', $entry) }}"
                       class="bg-white px-3 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        View
                    </a>

                    @if(is_null($entry->dpr_id))
                        <a href="{{ route('site-photos.edit', $entry) }}"
                           class="bg-white px-3 py-2.5 text-center text-sm font-semibold text-blue-700 hover:bg-blue-50">
                            Edit
                        </a>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                <div class="font-semibold text-slate-800">No site photos found</div>
                <p class="mt-2 text-sm text-slate-500">
                    {{ $filtersActive ? 'No entries match the selected filters.' : 'Start recording daily visual evidence from the site.' }}
                </p>
                <a href="{{ route('site-photos.create') }}"
                   class="mt-5 inline-flex rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    + Add Site Photos
                </a>
            </div>
        @endforelse
    </div>

    {{-- Compact desktop register: intentionally no horizontal scrolling --}}
    <section class="hidden lg:block overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full table-fixed divide-y divide-slate-200">
            <colgroup>
                <col class="w-[10%]">
                <col class="w-[14%]">
                <col class="w-[24%]">
                <col class="w-[21%]">
                <col class="w-[10%]">
                <col class="w-[7%]">
                <col class="w-[8%]">
                <col class="w-[6%]">
            </colgroup>

            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Date</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Project</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Location</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Category / Work</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Reporter</th>
                    <th class="px-2 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-600">Photos</th>
                    <th class="px-2 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Status</th>
                    <th class="px-2 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($sitePhotoEntries as $entry)
                    @php
                        $statusClass = $entry->status === 'Submitted'
                            ? 'bg-emerald-100 text-emerald-800'
                            : 'bg-amber-100 text-amber-800';
                    @endphp

                    <tr class="align-middle hover:bg-slate-50/70">
                        <td class="px-3 py-3 text-sm font-semibold text-slate-800">
                            {{ $entry->photo_date?->format('d M Y') ?? '—' }}
                        </td>

                        <td class="px-3 py-3">
                            <div class="truncate text-sm font-semibold text-slate-900" title="{{ $entry->project?->project_name }}">
                                {{ $entry->project?->project_name ?? 'Unknown Project' }}
                            </div>
                            @if($entry->dpr_id)
                                <div class="mt-1 text-xs font-semibold text-blue-700">DPR #{{ $entry->dpr_id }}</div>
                            @else
                                <div class="mt-1 text-xs text-slate-400">Not Linked</div>
                            @endif
                        </td>

                        <td class="px-3 py-3">
                            <div class="line-clamp-2 break-words text-sm leading-5 text-slate-700"
                                 title="{{ $entry->location_path ?: 'General site / No specific location' }}">
                                {{ $entry->location_path ?: 'General site / No specific location' }}
                            </div>
                        </td>

                        <td class="px-3 py-3">
                            <div class="truncate text-xs font-semibold text-blue-700" title="{{ $entry->category }}">
                                {{ $entry->category }}
                            </div>
                            <div class="mt-1 line-clamp-2 break-words text-sm font-medium leading-5 text-slate-800"
                                 title="{{ $entry->workActivity?->name ?? 'General site photography' }}">
                                {{ $entry->workActivity?->name ?? 'General site photography' }}
                            </div>
                        </td>

                        <td class="px-3 py-3">
                            <div class="truncate text-sm text-slate-700" title="{{ $entry->reporter?->name }}">
                                {{ $entry->reporter?->name ?? '—' }}
                            </div>
                        </td>

                        <td class="px-2 py-3 text-center">
                            <a href="{{ route('site-photos.show', $entry) }}#photographic-evidence"
                               class="inline-flex items-center justify-center gap-1 rounded-lg bg-violet-50 px-2 py-1.5 text-xs font-semibold text-violet-700 hover:bg-violet-100"
                               title="View {{ $entry->photos_count }} photographs"
                               aria-label="View {{ $entry->photos_count }} photographs">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-4 w-4 shrink-0" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="14" rx="2" stroke-width="1.8"></rect>
                                    <circle cx="8.5" cy="10" r="1.5" stroke-width="1.8"></circle>
                                    <path d="M4.5 17l4.5-4 3.2 2.8 2.5-2.2 4.8 3.4" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                {{ $entry->photos_count }}
                            </a>
                        </td>

                        <td class="px-2 py-3">
                            <span class="inline-flex max-w-full rounded-full px-2 py-1 text-[11px] font-semibold {{ $statusClass }}">
                                {{ $entry->status }}
                            </span>
                        </td>

                        <td class="px-2 py-3">
                            <div class="flex flex-col items-end gap-1">
                                <a href="{{ route('site-photos.show', $entry) }}"
                                   class="text-xs font-semibold text-slate-700 hover:text-slate-950">
                                    View
                                </a>

                                @if(is_null($entry->dpr_id))
                                    <a href="{{ route('site-photos.edit', $entry) }}"
                                       class="text-xs font-semibold text-blue-700 hover:text-blue-900">
                                        Edit
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-14 text-center">
                            <div class="font-semibold text-slate-800">No site photos found</div>
                            <p class="mt-2 text-sm text-slate-500">
                                {{ $filtersActive ? 'No entries match the selected filters.' : 'Start recording daily visual evidence from the site.' }}
                            </p>
                            <a href="{{ route('site-photos.create') }}"
                               class="mt-5 inline-flex rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                                + Add Site Photos
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @if($sitePhotoEntries->hasPages())
        <div class="mt-6">
            {{ $sitePhotoEntries->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
