@extends('layouts.app')

@section('content')

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Admin Dashboard</h1>
    <p class="text-gray-500 mt-1">
        Ravion DPR execution control, planning, issue and approval overview.
    </p>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded shadow p-5 border-l-4 border-blue-600">
        <p class="text-gray-500 text-sm">Total Projects</p>
        <p class="text-3xl font-bold text-blue-700">{{ $totalProjects }}</p>
        <p class="text-xs text-gray-400 mt-1">Active: {{ $activeProjects }}</p>
    </div>

    <div class="bg-white rounded shadow p-5 border-l-4 border-green-600">
        <p class="text-gray-500 text-sm">DPR Submitted Today</p>
        <p class="text-3xl font-bold text-green-700">{{ $todayDprs }}</p>
        <p class="text-xs text-gray-400 mt-1">Total DPRs: {{ $totalDprs }}</p>
    </div>

    <div class="bg-white rounded shadow p-5 border-l-4 border-orange-500">
        <p class="text-gray-500 text-sm">Pending DPRs</p>
        <p class="text-3xl font-bold text-orange-600">{{ $pendingDprs }}</p>
        <p class="text-xs text-gray-400 mt-1">Approved: {{ $approvedDprs }}</p>
    </div>

    <div class="bg-white rounded shadow p-5 border-l-4 border-red-600">
        <p class="text-gray-500 text-sm">Critical Issues</p>
        <p class="text-3xl font-bold text-red-700">{{ $criticalSiteIssues }}</p>
        <p class="text-xs text-gray-400 mt-1">Open Issues: {{ $openSiteIssues }}</p>
    </div>

</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded shadow p-5">
        <p class="text-gray-500 text-sm">Tomorrow Plans</p>
        <p class="text-2xl font-bold text-blue-700">{{ $tomorrowPlans }}</p>
        <p class="text-xs text-gray-400 mt-1">Planned for tomorrow</p>
    </div>

    <div class="bg-white rounded shadow p-5">
        <p class="text-gray-500 text-sm">Plan Approval Pending</p>
        <p class="text-2xl font-bold text-orange-600">{{ $pendingTomorrowPlanApprovals }}</p>
        <p class="text-xs text-gray-400 mt-1">Submitted plans</p>
    </div>

    <div class="bg-white rounded shadow p-5">
        <p class="text-gray-500 text-sm">Material Shortage Items</p>
        <p class="text-2xl font-bold text-red-700">{{ $materialShortageItems }}</p>
        <p class="text-xs text-gray-400 mt-1">Approved open requirements</p>
    </div>

    <div class="bg-white rounded shadow p-5">
        <p class="text-gray-500 text-sm">Management Escalations</p>
        <p class="text-2xl font-bold text-red-700">{{ $managementEscalations }}</p>
        <p class="text-xs text-gray-400 mt-1">PMO: {{ $pmoEscalations }}</p>
    </div>

</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded shadow p-5">
        <p class="text-gray-500 text-sm">Total Users</p>
        <p class="text-2xl font-bold text-gray-800">{{ $totalUsers }}</p>
    </div>

    <div class="bg-white rounded shadow p-5">
        <p class="text-gray-500 text-sm">Engineers</p>
        <p class="text-2xl font-bold text-blue-700">{{ $totalEngineers }}</p>
    </div>

    <div class="bg-white rounded shadow p-5">
        <p class="text-gray-500 text-sm">PMO / DGM Users</p>
        <p class="text-2xl font-bold text-purple-700">{{ $totalPmos }}</p>
    </div>

    <div class="bg-white rounded shadow p-5">
        <p class="text-gray-500 text-sm">Accountants</p>
        <p class="text-2xl font-bold text-green-700">{{ $totalAccountants }}</p>
    </div>

</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

    <div class="bg-white rounded shadow p-5">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Execution Risk Summary</h2>

        <div class="space-y-4">

            <div class="flex justify-between items-center border-b pb-3">
                <span class="text-gray-600">Delayed Projects</span>
                <span class="font-bold text-red-700">{{ $delayedProjects }}</span>
            </div>

            <div class="flex justify-between items-center border-b pb-3">
                <span class="text-gray-600">Overdue Engineers</span>
                <span class="font-bold text-orange-700">{{ $overdueEngineers }}</span>
            </div>

            <div class="flex justify-between items-center border-b pb-3">
                <span class="text-gray-600">Open Site Issues</span>
                <span class="font-bold text-red-700">{{ $openSiteIssues }}</span>
            </div>

            <div class="flex justify-between items-center">
                <span class="text-gray-600">Material Shortages</span>
                <span class="font-bold text-red-700">{{ $materialShortageItems }}</span>
            </div>

        </div>
    </div>

    <div class="bg-white rounded shadow p-5">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Role Distribution</h2>

        <div class="grid grid-cols-2 gap-4">

            <div class="bg-gray-50 rounded p-4">
                <p class="text-sm text-gray-500">Admins</p>
                <p class="text-2xl font-bold">{{ $totalAdmins }}</p>
            </div>

            <div class="bg-gray-50 rounded p-4">
                <p class="text-sm text-gray-500">Engineers</p>
                <p class="text-2xl font-bold">{{ $totalEngineers }}</p>
            </div>

            <div class="bg-gray-50 rounded p-4">
                <p class="text-sm text-gray-500">PMO</p>
                <p class="text-2xl font-bold">{{ $totalPmos }}</p>
            </div>

            <div class="bg-gray-50 rounded p-4">
                <p class="text-sm text-gray-500">CEO</p>
                <p class="text-2xl font-bold">{{ $totalCeos }}</p>
            </div>

        </div>
    </div>

</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <div class="bg-white rounded shadow overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="font-bold text-gray-800">Recent DPRs</h2>
        </div>

        <div class="divide-y">
            @forelse($recentDprs as $dpr)
                <div class="p-4">
                    <p class="font-semibold">{{ $dpr->project?->project_name ?? '-' }}</p>
                    <p class="text-sm text-gray-500">
                        Date: {{ $dpr->dpr_date ?? '-' }} | Status: {{ $dpr->status ?? '-' }}
                    </p>
                </div>
            @empty
                <div class="p-4 text-gray-500">No recent DPRs found.</div>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="font-bold text-gray-800">Recent Site Issues</h2>
        </div>

        <div class="divide-y">
            @forelse($recentIssues as $issue)
                <div class="p-4">
                    <p class="font-semibold">{{ $issue->title ?? '-' }}</p>
                    <p class="text-sm text-gray-500">
                        {{ $issue->project?->project_name ?? '-' }} |
                        {{ $issue->priority }} |
                        {{ $issue->status }}
                    </p>
                </div>
            @empty
                <div class="p-4 text-gray-500">No recent issues found.</div>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="font-bold text-gray-800">Recent Tomorrow Plans</h2>
        </div>

        <div class="divide-y">
            @forelse($recentTomorrowPlans as $plan)
                <div class="p-4">
                    <p class="font-semibold">
                        {{ $plan->activity?->activity_name ?? '-' }}
                    </p>
                    <p class="text-sm text-gray-500">
                        {{ $plan->project?->project_name ?? '-' }} |
                        {{ $plan->planned_date ?? '-' }} |
                        {{ $plan->status }}
                    </p>
                </div>
            @empty
                <div class="p-4 text-gray-500">No recent plans found.</div>
            @endforelse
        </div>
    </div>

</div>

@endsection