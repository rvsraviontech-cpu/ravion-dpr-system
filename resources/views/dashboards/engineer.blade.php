@extends('layouts.app')

@section('content')

@php
    $todayDprStatus = $todayDpr?->status ?? null;

    $todayDprStatusNormalized = strtolower(trim((string) $todayDprStatus));

    $todayDprExists = (bool) $todayDpr;

    $todayDprAction = 'Create Today\'s DPR';
    $todayDprDescription = 'Start today\'s site reporting';
    $todayDprRoute = Route::has('dprs.create') ? route('dprs.create') : '#';

    if ($todayDprExists) {
        if (in_array($todayDprStatusNormalized, ['draft', 'rejected'], true)) {
            $todayDprAction = $todayDprStatusNormalized === 'rejected'
                ? 'Review & Update DPR'
                : 'Continue Today\'s DPR';

            $todayDprDescription = $todayDprStatusNormalized === 'rejected'
                ? 'PMO has returned this DPR for correction'
                : 'Continue completing today\'s site report';

            $todayDprRoute = Route::has('dprs.edit')
                ? route('dprs.edit', $todayDpr->id)
                : route('dprs.show', $todayDpr->id);
        } else {
            $todayDprAction = in_array($todayDprStatusNormalized, ['approved'], true)
                ? 'View Approved DPR'
                : 'View Today\'s DPR';

            $todayDprDescription = in_array($todayDprStatusNormalized, ['approved'], true)
                ? 'Today\'s DPR has been approved'
                : 'Today\'s DPR has already been created';

            $todayDprRoute = Route::has('dprs.show')
                ? route('dprs.show', $todayDpr->id)
                : '#';
        }
    }

    $todayDprBadgeClass = match ($todayDprStatusNormalized) {
        'approved' => 'bg-green-100 text-green-700',
        'rejected' => 'bg-red-100 text-red-700',
        'pending', 'submitted' => 'bg-amber-100 text-amber-700',
        'draft' => 'bg-blue-100 text-blue-700',
        default => 'bg-gray-100 text-gray-700',
    };

    $todayDprBadgeText = $todayDprExists
        ? ucfirst($todayDprStatusNormalized ?: 'Created')
        : 'Not Started';

    $assignedProjectCount = $assignedProjects?->count() ?? 0;
@endphp


{{-- =========================================================================
     SITE ENGINEER MOBILE / PWA DASHBOARD
     ========================================================================= --}}
<div class="lg:hidden space-y-5">

    {{-- Welcome --}}
    <section>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">
            Good Morning, {{ auth()->user()->name }}
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            {{ now()->format('l, d M Y') }}
        </p>
    </section>


    {{-- Assigned Projects --}}
    <section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                    Assigned Project{{ $assignedProjectCount === 1 ? '' : 's' }}
                </p>

                @if($assignedProjectCount === 1)
                    <p class="mt-1 text-base font-bold text-[#0F2A52]">
                        {{ $assignedProjects->first()->project_name ?? 'Assigned Project' }}
                    </p>
                @elseif($assignedProjectCount > 1)
                    <p class="mt-1 text-base font-bold text-[#0F2A52]">
                        {{ $assignedProjectCount }} Active Project Assignments
                    </p>
                @else
                    <p class="mt-1 text-base font-bold text-red-600">
                        No Project Assigned
                    </p>
                @endif
            </div>

            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-[#0F2A52]">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 21h16M6 21V4h12v17M9 8h1m4 0h1M9 12h1m4 0h1M9 16h1m4 0h1"/>
                </svg>
            </div>
        </div>

        @if($assignedProjectCount > 1)
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach($assignedProjects->take(3) as $project)
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600">
                        {{ $project->project_name ?? 'Project' }}
                    </span>
                @endforeach

                @if($assignedProjectCount > 3)
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600">
                        +{{ $assignedProjectCount - 3 }} more
                    </span>
                @endif
            </div>
        @endif
    </section>


    {{-- Today's DPR Primary CTA --}}
    <section>
        <a
            href="{{ $todayDprRoute }}"
            class="block rounded-2xl bg-[#0F2A52] p-5 text-white shadow-sm active:scale-[0.99]"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-100">
                            Today's DPR
                        </p>

                        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $todayDprBadgeClass }}">
                            {{ $todayDprBadgeText }}
                        </span>
                    </div>

                    <h2 class="mt-3 text-xl font-bold">
                        {{ $todayDprAction }}
                    </h2>

                    <p class="mt-1 text-sm text-blue-100">
                        {{ $todayDprDescription }}
                    </p>

                    @if($todayDpr?->project)
                        <p class="mt-3 truncate text-xs text-blue-200">
                            {{ $todayDpr->project->project_name }}
                        </p>
                    @endif
                </div>

                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5h6m-7 4h8m-8 4h8m-8 4h5M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                    </svg>
                </div>
            </div>

            <div class="mt-5 flex items-center justify-between border-t border-white/10 pt-4 text-sm font-semibold">
                <span>Open DPR</span>
                <span aria-hidden="true">→</span>
            </div>
        </a>
    </section>


    {{-- Daily Execution --}}
    <section>
        <div class="mb-3 flex items-end justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-900">
                    Daily Execution
                </h2>
                <p class="text-xs text-gray-500">
                    Record today's site activity
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">

            @if(Route::has('labour-attendances.create'))
                <a
                    href="{{ route('labour-attendances.create') }}"
                    class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm"
                >
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M12 12a4 4 0 100-8 4 4 0 000 8z"/>
                        </svg>
                    </div>

                    <p class="mt-3 text-sm font-bold text-gray-900">
                        Attendance
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Mark labour attendance
                    </p>
                </a>
            @endif

            @if(Route::has('work-done.create'))
                <a
                    href="{{ route('work-done.create') }}"
                    class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm"
                >
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-50 text-green-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>

                    <p class="mt-3 text-sm font-bold text-gray-900">
                        Work Done
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Record completed work
                    </p>
                </a>
            @endif

            @if(Route::has('labour-attendance-corrections.index'))
                <a
                    href="{{ route('labour-attendance-corrections.index') }}"
                    class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm"
                >
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 12a9 9 0 109-9M3 3v6h6M12 7v5l4 2"/>
                        </svg>
                    </div>

                    <p class="mt-3 text-sm font-bold text-gray-900">
                        Corrections
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Attendance corrections
                    </p>
                </a>
            @endif

            @if(Route::has('site-issues.create'))
                <a
                    href="{{ route('site-issues.create') }}"
                    class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm"
                >
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>

                    <p class="mt-3 text-sm font-bold text-gray-900">
                        Site Issues
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Report site problems
                    </p>
                </a>
            @endif

        </div>
    </section>


    {{-- Material Tracking --}}
    <section>
        <div class="mb-3">
            <h2 class="text-base font-bold text-gray-900">
                Material Tracking
            </h2>

            <p class="text-xs text-gray-500">
                Record site material movement
            </p>
        </div>

        <div class="grid grid-cols-3 gap-2">

            @if(Route::has('material-received.create'))
                <a
                    href="{{ route('material-received.create') }}"
                    class="rounded-2xl border border-gray-200 bg-white px-2 py-4 text-center shadow-sm"
                >
                    <div class="mx-auto flex h-9 w-9 items-center justify-center rounded-xl bg-green-50 text-green-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 3v12m0 0l-4-4m4 4l4-4M5 19h14"/>
                        </svg>
                    </div>

                    <p class="mt-2 text-xs font-bold text-gray-900">
                        Received
                    </p>
                </a>
            @endif

            @if(Route::has('material-consumed.create'))
                <a
                    href="{{ route('material-consumed.create') }}"
                    class="rounded-2xl border border-gray-200 bg-white px-2 py-4 text-center shadow-sm"
                >
                    <div class="mx-auto flex h-9 w-9 items-center justify-center rounded-xl bg-orange-50 text-orange-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 21V9m0 0l-4 4m4-4l4 4M5 5h14"/>
                        </svg>
                    </div>

                    <p class="mt-2 text-xs font-bold text-gray-900">
                        Consumed
                    </p>
                </a>
            @endif

            @if(Route::has('material-requirements.create'))
                <a
                    href="{{ route('material-requirements.create') }}"
                    class="rounded-2xl border border-gray-200 bg-white px-2 py-4 text-center shadow-sm"
                >
                    <div class="mx-auto flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v8m-4-4h8M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                        </svg>
                    </div>

                    <p class="mt-2 text-xs font-bold text-gray-900">
                        Required
                    </p>
                </a>
            @endif

        </div>
    </section>


    {{-- Site Operations --}}
    <section>
        <div class="mb-3">
            <h2 class="text-base font-bold text-gray-900">
                Site Operations
            </h2>

            <p class="text-xs text-gray-500">
                Planning, equipment and site records
            </p>
        </div>

        <div class="space-y-3">

            @if(Route::has('tomorrow-plans.create'))
                <a
                    href="{{ route('tomorrow-plans.create') }}"
                    class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-4 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/>
                            </svg>
                        </div>

                        <div>
                            <p class="text-sm font-bold text-gray-900">
                                Tomorrow Plan
                            </p>

                            <p class="text-xs text-gray-500">
                                Plan next-day activities
                            </p>
                        </div>
                    </div>

                    <span class="text-gray-400">›</span>
                </a>
            @endif

            @if(Route::has('machinery-tools.index'))
                <a
                    href="{{ route('machinery-tools.index') }}"
                    class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-4 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 text-gray-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M14.7 6.3a4 4 0 01-5 5L4 17v3h3l5.7-5.7a4 4 0 005-5z"/>
                            </svg>
                        </div>

                        <div>
                            <p class="text-sm font-bold text-gray-900">
                                Machinery & Equipment
                            </p>

                            <p class="text-xs text-gray-500">
                                View site machinery and tools
                            </p>
                        </div>
                    </div>

                    <span class="text-gray-400">›</span>
                </a>
            @endif

            @if(Route::has('dpr-photos.index'))
                <a
                    href="{{ route('dpr-photos.index') }}"
                    class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-4 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 7h4l2-2h6l2 2h4v12H3zM12 10a4 4 0 100 8 4 4 0 000-8z"/>
                            </svg>
                        </div>

                        <div>
                            <p class="text-sm font-bold text-gray-900">
                                Site Photos
                            </p>

                            <p class="text-xs text-gray-500">
                                Capture site progress
                            </p>
                        </div>
                    </div>

                    <span class="text-gray-400">›</span>
                </a>
            @else
                <a
                    href="{{ Route::has('dprs.create') ? route('dprs.create') : '#' }}"
                    class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-4 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 7h4l2-2h6l2 2h4v12H3zM12 10a4 4 0 100 8 4 4 0 000-8z"/>
                            </svg>
                        </div>

                        <div>
                            <p class="text-sm font-bold text-gray-900">
                                Site Photos
                            </p>

                            <p class="text-xs text-gray-500">
                                Available inside Create DPR
                            </p>
                        </div>
                    </div>

                    <span class="text-gray-400">›</span>
                </a>
            @endif

        </div>
    </section>


    {{-- Today's Status --}}
    <section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="mb-4">
            <h2 class="text-base font-bold text-gray-900">
                Today's Status
            </h2>

            <p class="text-xs text-gray-500">
                Quick site summary
            </p>
        </div>

        <div class="grid grid-cols-2 gap-3">

            <div class="rounded-xl bg-gray-50 p-3">
                <p class="text-xs text-gray-500">
                    DPR
                </p>

                <p class="mt-1 text-sm font-bold text-gray-900">
                    {{ $todayDprBadgeText }}
                </p>
            </div>

            <div class="rounded-xl bg-gray-50 p-3">
                <p class="text-xs text-gray-500">
                    Labour Today
                </p>

                <p class="mt-1 text-sm font-bold text-gray-900">
                    {{ number_format($labourToday ?? 0) }}
                </p>
            </div>

            <div class="rounded-xl bg-gray-50 p-3">
                <p class="text-xs text-gray-500">
                    Open Issues
                </p>

                <p class="mt-1 text-sm font-bold text-gray-900">
                    {{ number_format($openSiteIssues ?? 0) }}
                </p>
            </div>

            <div class="rounded-xl bg-gray-50 p-3">
                <p class="text-xs text-gray-500">
                    Pending Materials
                </p>

                <p class="mt-1 text-sm font-bold text-gray-900">
                    {{ number_format($pendingMaterialRequests ?? 0) }}
                </p>
            </div>

        </div>
    </section>


    {{-- Recent DPRs --}}
    @if(($recentDprs ?? collect())->count())
        <section>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900">
                    Recent DPRs
                </h2>

                @if(Route::has('dprs.index'))
                    <a
                        href="{{ route('dprs.index') }}"
                        class="text-xs font-semibold text-[#0F2A52]"
                    >
                        View All
                    </a>
                @endif
            </div>

            <div class="space-y-2">
                @foreach($recentDprs->take(3) as $dpr)
                    <a
                        href="{{ Route::has('dprs.show') ? route('dprs.show', $dpr->id) : '#' }}"
                        class="flex items-center justify-between gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-gray-900">
                                {{ $dpr->project->project_name ?? 'Project' }}
                            </p>

                            <p class="mt-1 text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($dpr->dpr_date)->format('d M Y') }}
                            </p>
                        </div>

                        <span class="shrink-0 rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-700">
                            {{ $dpr->status ?? 'Draft' }}
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

</div>


{{-- =========================================================================
     EXISTING DESKTOP ENGINEER DASHBOARD
     ========================================================================= --}}
<div class="hidden lg:block">

    @php
        $todayDprSubmitted = ($todayDprs ?? 0) > 0;
    @endphp

    <div class="flex justify-between items-center mb-6">

        <div>
            <h1 class="text-3xl font-bold">
                Good Morning, {{ auth()->user()->name }} 👋
            </h1>

            <p class="text-gray-600 mt-1">
                Engineer Dashboard • {{ now()->format('d-M-Y') }}
            </p>
        </div>

        <a href="{{ route('dprs.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
            + Create DPR
        </a>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

        <div class="bg-white rounded-lg shadow p-6 border-l-4 {{ $todayDprSubmitted ? 'border-green-500' : 'border-red-500' }}">
            <p class="text-gray-500 text-sm">Today's DPR</p>
            <h2 class="text-2xl font-bold mt-2 {{ $todayDprSubmitted ? 'text-green-600' : 'text-red-600' }}">
                {{ $todayDprSubmitted ? 'Submitted' : 'Pending' }}
            </h2>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
            <p class="text-gray-500 text-sm">Open Site Issues</p>
            <h2 class="text-3xl font-bold mt-2">{{ $openSiteIssues ?? 0 }}</h2>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <p class="text-gray-500 text-sm">Pending Materials</p>
            <h2 class="text-3xl font-bold mt-2">{{ $pendingMaterialRequests ?? 0 }}</h2>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <p class="text-gray-500 text-sm">Labour Today</p>
            <h2 class="text-3xl font-bold mt-2">{{ $labourToday ?? 0 }}</h2>
        </div>

    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-8">

        <h2 class="text-2xl font-bold mb-4">
            My Pending Actions
        </h2>

        <div class="space-y-3">

            @if(!$todayDprSubmitted)
                <div class="bg-red-50 text-red-700 p-4 rounded border border-red-200">
                    High Priority: Submit today’s DPR.
                </div>
            @else
                <div class="bg-green-50 text-green-700 p-4 rounded border border-green-200">
                    Today’s DPR has been submitted.
                </div>
            @endif

            @if(($openSiteIssues ?? 0) > 0)
                <div class="bg-orange-50 text-orange-700 p-4 rounded border border-orange-200">
                    {{ $openSiteIssues }} open site issue(s) need attention.
                </div>
            @endif

            @if(($pendingMaterialRequests ?? 0) > 0)
                <div class="bg-yellow-50 text-yellow-700 p-4 rounded border border-yellow-200">
                    {{ $pendingMaterialRequests }} material request(s) pending.
                </div>
            @endif

        </div>

    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-8">

        <div class="flex justify-between items-center">

            <div>
                <h2 class="text-2xl font-bold">
                    Daily Progress Report
                </h2>

                <p class="mt-2 {{ $todayDprSubmitted ? 'text-green-600' : 'text-red-600' }}">
                    {{ $todayDprSubmitted ? 'Today’s DPR has been submitted.' : 'Today’s DPR is not submitted yet.' }}
                </p>
            </div>

            @if(!$todayDprSubmitted)
                <a href="{{ route('dprs.create') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
                    Create DPR Now
                </a>
            @endif

        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

        <div class="bg-white rounded-lg shadow p-6">

            <h2 class="text-xl font-bold mb-4">
                Quick Actions
            </h2>

            <div class="grid grid-cols-1 gap-3">

                <a href="{{ route('dprs.create') }}" class="bg-blue-600 text-white px-4 py-3 rounded text-center">
                    + Create DPR
                </a>

                <a href="{{ route('labour-attendances.index') }}" class="bg-gray-100 px-4 py-3 rounded text-center">
                    Labour Attendance
                </a>

                <a href="{{ route('material-received.create') }}" class="bg-gray-100 px-4 py-3 rounded text-center">
                    Material Received
                </a>

                <a href="{{ route('material-consumed.create') }}" class="bg-gray-100 px-4 py-3 rounded text-center">
                    Material Consumed
                </a>

                <a href="{{ route('material-requirements.create') }}" class="bg-gray-100 px-4 py-3 rounded text-center">
                    Material Requirement
                </a>

                <a href="{{ route('site-issues.create') }}" class="bg-gray-100 px-4 py-3 rounded text-center">
                    Site Issue
                </a>

                <a href="{{ route('tomorrow-plans.create') }}" class="bg-gray-100 px-4 py-3 rounded text-center">
                    Tomorrow Plan
                </a>

            </div>

        </div>

        <div class="bg-white rounded-lg shadow p-6">

            <h2 class="text-xl font-bold mb-4">
                My Summary
            </h2>

            <div class="space-y-4">

                <div class="flex justify-between border-b pb-2">
                    <span>Total DPRs Submitted</span>
                    <strong>{{ $totalDprs ?? 0 }}</strong>
                </div>

                <div class="flex justify-between border-b pb-2">
                    <span>Today's DPRs</span>
                    <strong>{{ $todayDprs ?? 0 }}</strong>
                </div>

                <div class="flex justify-between border-b pb-2">
                    <span>Open Site Issues</span>
                    <strong>{{ $openSiteIssues ?? 0 }}</strong>
                </div>

                <div class="flex justify-between">
                    <span>Labour Today</span>
                    <strong>{{ $labourToday ?? 0 }}</strong>
                </div>

            </div>

        </div>

    </div>

    <div class="bg-white rounded shadow p-6">

        <h2 class="text-2xl font-bold mb-4">
            Recent DPRs
        </h2>

        <table class="w-full">

            <thead>
                <tr class="border-b bg-gray-50">
                    <th class="text-left p-3">Date</th>
                    <th class="text-left p-3">Project</th>
                    <th class="text-left p-3">Status</th>
                    <th class="text-left p-3">PMO Remarks</th>
                </tr>
            </thead>

            <tbody>

                @forelse($recentDprs as $dpr)

                    <tr class="border-b">

                        <td class="p-3">
                            {{ \Carbon\Carbon::parse($dpr->dpr_date)->format('d-m-Y') }}
                        </td>

                        <td class="p-3">
                            {{ $dpr->project->project_name ?? '-' }}
                        </td>

                        <td class="p-3">
                            <span class="px-3 py-1 rounded-full text-sm font-semibold
                                @if($dpr->status == 'Approved') bg-green-100 text-green-700
                                @elseif($dpr->status == 'Rejected') bg-red-100 text-red-700
                                @else bg-yellow-100 text-yellow-700
                                @endif">
                                {{ $dpr->status }}
                            </span>
                        </td>

                        <td class="p-3">
                            {{ $dpr->pmo_remarks ?? '-' }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="4" class="p-4 text-center text-gray-500">
                            No DPRs found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection
