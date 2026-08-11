@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';

    $roleName = auth()->user()->role?->name;

    $pageActivities = $workDoneHeaders
        ->getCollection()
        ->sum(fn ($header) => $header->items->count());

    $pageMaterials = $workDoneHeaders
        ->getCollection()
        ->sum(
            fn ($header) => $header->items->sum(
                fn ($item) => $item->materialConsumptions->count()
            )
        );

    $pagePhotos = $workDoneHeaders
        ->getCollection()
        ->sum(
            fn ($header) => $header->items->sum(
                fn ($item) => $item->photos->count()
            )
        );
@endphp

<div class="mx-auto max-w-full">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Daily Work Execution
            </h1>

            <p class="mt-1 text-gray-500">
                Review project-wise daily work activities, material usage and photographic evidence.
            </p>
        </div>

        <a href="{{ route('work-done.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 font-semibold text-white shadow-sm hover:bg-blue-700">
            + Add Work Execution
        </a>
    </div>

    {{-- Alerts --}}
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

    {{-- Summary --}}
    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                Register Entries
            </div>

            <div class="mt-2 text-3xl font-bold text-gray-800">
                {{ number_format($workDoneHeaders->total()) }}
            </div>

            <div class="mt-1 text-xs text-gray-500">
                Matching current filters
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                Activities
            </div>

            <div class="mt-2 text-3xl font-bold text-gray-800">
                {{ number_format($pageActivities) }}
            </div>

            <div class="mt-1 text-xs text-gray-500">
                On this page
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                Material Links
            </div>

            <div class="mt-2 text-3xl font-bold text-gray-800">
                {{ number_format($pageMaterials) }}
            </div>

            <div class="mt-1 text-xs text-gray-500">
                Material Consumed records
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                Photos
            </div>

            <div class="mt-2 text-3xl font-bold text-gray-800">
                {{ number_format($pagePhotos) }}
            </div>

            <div class="mt-1 text-xs text-gray-500">
                Work evidence
            </div>
        </div>

    </div>

    {{-- Filters --}}
    <form method="GET"
          action="{{ route('work-done.index') }}"
          class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">

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
                    Work Date
                </label>

                <input type="date"
                       name="work_date"
                       value="{{ request('work_date') }}"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Engineer
                </label>

                @if($roleName === 'Engineer')
                    <input type="text"
                           value="{{ auth()->user()->name }}"
                           class="{{ $inputClass }} bg-gray-100"
                           readonly>
                @else
                    <select name="user_id"
                            class="{{ $inputClass }}">
                        <option value="">All Engineers</option>

                        @foreach($engineers as $engineer)
                            <option value="{{ $engineer->id }}"
                                {{ (string) request('user_id') === (string) $engineer->id ? 'selected' : '' }}>
                                {{ $engineer->name }}
                            </option>
                        @endforeach
                    </select>
                @endif
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
                        Has Unlinked Activity
                    </option>

                    <option value="linked"
                        {{ request('dpr_link') === 'linked' ? 'selected' : '' }}>
                        Has Linked Activity
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
                       placeholder="Project, activity, contractor">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('work-done.index') }}"
                   class="rounded-lg bg-gray-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700">
                    Clear
                </a>
            </div>

        </div>
    </form>

    {{-- Register --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-5 py-4">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">
                        Work Execution Register
                    </h2>

                    <p class="text-sm text-gray-500">
                        One register row represents one Project + Date + Engineer header.
                    </p>
                </div>

                <div class="text-sm text-gray-500">
                    Showing
                    <span class="font-semibold text-gray-700">
                        {{ $workDoneHeaders->firstItem() ?? 0 }}–{{ $workDoneHeaders->lastItem() ?? 0 }}
                    </span>
                    of
                    <span class="font-semibold text-gray-700">
                        {{ $workDoneHeaders->total() }}
                    </span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">

            <table class="min-w-[1450px] w-full text-sm">

                <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="w-14 px-4 py-3 text-center">#</th>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Project</th>
                        <th class="px-4 py-3 text-left">Engineer</th>
                        <th class="px-4 py-3 text-center">Activities</th>
                        <th class="px-4 py-3 text-center">Materials</th>
                        <th class="px-4 py-3 text-center">Photos</th>
                        <th class="px-4 py-3 text-left">DPR Link</th>
                        <th class="px-4 py-3 text-left">Daily Status</th>
                        <th class="px-4 py-3 text-left">Remarks</th>
                        <th class="w-32 px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                    @forelse($workDoneHeaders as $index => $header)

                        @php
                            $activityCount = $header->items->count();

                            $materialCount = $header->items->sum(
                                fn ($item) => $item->materialConsumptions->count()
                            );

                            $photoCount = $header->items->sum(
                                fn ($item) => $item->photos->count()
                            );

                            $linkedCount = $header->items
                                ->whereNotNull('dpr_id')
                                ->count();

                            $unlinkedCount = $activityCount - $linkedCount;

                            $allLinked = $activityCount > 0 && $linkedCount === $activityCount;

                            $hasLinked = $linkedCount > 0;

                            $canEditHeader = $unlinkedCount > 0;
                            $canDeleteHeader = ! $hasLinked;
                        @endphp

                        <tr class="align-top hover:bg-gray-50">

                            <td class="px-4 py-4 text-center text-gray-500">
                                {{ ($workDoneHeaders->firstItem() ?? 1) + $index }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                <div class="font-semibold text-gray-800">
                                    {{ $header->work_date?->format('d/m/Y') ?? '-' }}
                                </div>

                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $header->work_date?->format('l') ?? '' }}
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <div class="font-semibold text-gray-800">
                                    {{ $header->project?->project_name ?? '-' }}
                                </div>

                                @if($header->project?->project_code)
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $header->project->project_code }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                {{ $header->engineer?->name ?? '-' }}
                            </td>

                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex min-w-9 items-center justify-center rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-800">
                                    {{ $activityCount }}
                                </span>
                            </td>

                            <td class="px-4 py-4 text-center">
                                <span class="font-semibold text-gray-800">
                                    {{ $materialCount }}
                                </span>
                            </td>

                            <td class="px-4 py-4 text-center">
                                <span class="font-semibold text-gray-800">
                                    {{ $photoCount }}
                                </span>
                            </td>

                            <td class="px-4 py-4">
                                @if($activityCount === 0)
                                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                        No Activities
                                    </span>
                                @elseif($allLinked)
                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                        All Linked · {{ $linkedCount }}/{{ $activityCount }}
                                    </span>
                                @elseif($hasLinked)
                                    <div class="space-y-1">
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                            Partially Linked · {{ $linkedCount }}/{{ $activityCount }}
                                        </span>

                                        <div class="text-xs text-gray-500">
                                            {{ $unlinkedCount }} activity{{ $unlinkedCount === 1 ? '' : 'ies' }} pending DPR link
                                        </div>
                                    </div>
                                @else
                                    <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">
                                        Not Linked
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                    {{ $header->status ?: 'Draft' }}
                                </span>
                            </td>

                            <td class="max-w-xs px-4 py-4">
                                @if($header->remarks)
                                    <div class="line-clamp-2 text-gray-700"
                                         title="{{ $header->remarks }}">
                                        {{ $header->remarks }}
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex flex-col gap-2">

                                    <a href="{{ route('work-done.show', $header) }}"
                                       class="rounded-lg bg-slate-700 px-3 py-2 text-center text-xs font-semibold text-white hover:bg-slate-800">
                                        View
                                    </a>

                                    @if($canEditHeader)
                                        <a href="{{ route('work-done.edit', $header) }}"
                                           class="rounded-lg bg-amber-500 px-3 py-2 text-center text-xs font-semibold text-white hover:bg-amber-600">
                                            Edit
                                        </a>
                                    @endif

                                    @if($canDeleteHeader)
                                        <form method="POST"
                                              action="{{ route('work-done.destroy', $header) }}">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    onclick="return confirm('Delete this Daily Work Execution entry and all its Work Activities?')"
                                                    class="w-full rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">
                                                Delete
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>

                        </tr>

                        {{-- Activity Preview --}}
                        @if($header->items->isNotEmpty())
                            <tr class="bg-gray-50/60">
                                <td></td>

                                <td colspan="10"
                                    class="px-4 py-3">

                                    <div class="flex flex-wrap gap-2">

                                        @foreach($header->items->take(6) as $item)
                                            <div class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs shadow-sm">

                                                <div class="font-semibold text-gray-800">
                                                    {{ $item->activity_name ?? 'Activity' }}
                                                </div>

                                                <div class="mt-1 text-gray-500">
                                                    @if($item->location_path)
                                                        {{ $item->location_path }} ·
                                                    @endif

                                                    {{ rtrim(rtrim(number_format((float) $item->quantity_completed, 3, '.', ''), '0'), '.') }}
                                                    {{ $item->unit ?? '' }}
                                                </div>

                                            </div>
                                        @endforeach

                                        @if($header->items->count() > 6)
                                            <div class="flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-600 shadow-sm">
                                                +{{ $header->items->count() - 6 }} more
                                            </div>
                                        @endif

                                    </div>

                                </td>
                            </tr>
                        @endif

                    @empty

                        <tr>
                            <td colspan="11"
                                class="px-6 py-14 text-center">

                                <div class="mx-auto max-w-md">
                                    <div class="text-lg font-semibold text-gray-700">
                                        No Daily Work Execution records found
                                    </div>

                                    <p class="mt-2 text-sm text-gray-500">
                                        Adjust the filters or create the first Work Execution entry.
                                    </p>

                                    <a href="{{ route('work-done.create') }}"
                                       class="mt-4 inline-flex rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                                        + Add Work Execution
                                    </a>
                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>

        </div>

        @if($workDoneHeaders->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $workDoneHeaders->links() }}
            </div>
        @endif

    </div>
</div>

@endsection
