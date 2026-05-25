@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Admin Dashboard
</h1>

<div class="grid grid-cols-4 gap-6 mb-8">

    <x-dashboard-card
        title="Total Projects"
        :value="$totalProjects" />

    <x-dashboard-card
        title="Total Users"
        :value="$totalUsers" />

    <x-dashboard-card
        title="Total DPRs"
        :value="$totalDprs" />

    <x-dashboard-card
        title="Pending DPRs"
        :value="$pendingDprs" />

    <x-dashboard-card
        title="Delayed Projects"
        :value="$delayedProjects" />

    <x-dashboard-card
    title="Overdue Engineers"
    :value="$overdueEngineers" />

</div>

<div class="bg-white rounded shadow p-6 mt-8">

    <h2 class="text-2xl font-bold mb-6">
        Operational Alerts
    </h2>

    <div class="space-y-4">

        @if($pendingDprs > 0)

            <a href="/pmo/dprs"
        class="block bg-yellow-100
                border-l-4
                border-yellow-500
                p-4">

                <p class="font-semibold text-yellow-800">

                    {{ $pendingDprs }}
                    DPRs are awaiting PMO approval.

                </p>

        </a>

        @endif

        @if($delayedProjects > 0)

            <a href="/project-progress"
        class="block bg-yellow-100
                border-l-4
                border-yellow-500
                p-4">

                <p class="font-semibold text-red-800">

                    {{ $delayedProjects }}
                    projects are delayed.

                </p>

</a>

        @endif

        @if($overdueEngineers > 0)

            <a href="/engineer-productivity"
        class="block bg-yellow-100
                border-l-4
                border-yellow-500
                p-4">

                <p class="font-semibold text-orange-800">

                    {{ $overdueEngineers }}
                    engineers have overdue DPR reporting.

                </p>

</a>

        @endif

        @if(
            $pendingDprs == 0 &&
            $delayedProjects == 0 &&
            $overdueEngineers == 0
        )

            <div class="bg-green-100
                        border-l-4
                        border-green-500
                        p-4">

                <p class="font-semibold text-green-800">

                    All operations are running normally.

                </p>

            </div>

        @endif

    </div>

</div>

//Quick Actions & Summary

<div class="grid grid-cols-2 gap-6">

    <div class="bg-white rounded shadow p-6">

        <h2 class="text-2xl font-bold mb-4">
            Quick Actions
        </h2>

        <div class="space-y-3">

            <a href="/projects/create"
               class="block bg-blue-600 text-white px-4 py-2 rounded">

                Create Project

            </a>

            <a href="/users/create"
               class="block bg-green-600 text-white px-4 py-2 rounded">

                Create User

            </a>

            <a href="/activities/create"
               class="block bg-purple-600 text-white px-4 py-2 rounded">

                Create Activity

            </a>

        </div>

    </div>
    <div class="bg-white rounded shadow p-6">

    <h2 class="text-2xl font-bold mb-4">
        Organization Summary
    </h2>

    <table class="w-full">

        <tbody>

            <tr class="border-b">
                <td class="py-2">Admins</td>
                <td class="py-2 font-bold">{{ $totalAdmins }}</td>
            </tr>

            <tr class="border-b">
                <td class="py-2">Engineers</td>
                <td class="py-2 font-bold">{{ $totalEngineers }}</td>
            </tr>

            <tr class="border-b">
                <td class="py-2">PMOs</td>
                <td class="py-2 font-bold">{{ $totalPmos }}</td>
            </tr>

            <tr class="border-b">
                <td class="py-2">CEOs</td>
                <td class="py-2 font-bold">{{ $totalCeos }}</td>
            </tr>

            <tr>
                <td class="py-2">Accountants</td>
                <td class="py-2 font-bold">{{ $totalAccountants }}</td>
            </tr>

        </tbody>

    </table>

</div>

    <div class="bg-white rounded shadow p-6">

        <h2 class="text-2xl font-bold mb-4">
            Recent DPRs
        </h2>

        <table class="w-full">

            <thead class="bg-gray-100">

<tr>

    <th class="p-4 text-left">
        Project
    </th>

    <th class="p-4 text-left">
        Engineer
    </th>

    <th class="p-4 text-left">
        Date
    </th>

    <th class="p-4 text-left">
        Status
    </th>

</tr>

</thead>

            <tbody>

                @foreach($recentDprs as $dpr)

                <tr class="border-t">

    <td class="p-4">
        {{ $dpr->project->project_name }}
    </td>

    <td class="p-4">
        {{ $dpr->user->name }}
    </td>

    <td class="p-4">
        {{ $dpr->dpr_date }}
    </td>

    <td class="p-4">

        @if($dpr->status == 'Approved')

            <span class="bg-green-200 text-green-800 px-3 py-1 rounded">
                Approved
            </span>

        @elseif($dpr->status == 'Rejected')

            <span class="bg-red-200 text-red-800 px-3 py-1 rounded">
                Rejected
            </span>

        @else

            <span class="bg-yellow-200 text-yellow-800 px-3 py-1 rounded">
                Pending
            </span>

        @endif

    </td>

</tr>

                @endforeach

            </tbody>

        </table>

    </div>
    <div class="bg-white rounded shadow p-6 mt-8">

    <h2 class="text-2xl font-bold mb-6">
        Recent Activity Feed
    </h2>

    <div class="space-y-4">

        @foreach($recentActivities as $activity)

            <div class="border-b pb-4">

                <p class="font-semibold">

                    {{ $activity->user->name }}

                    @if($activity->status == 'Pending')

                        submitted a DPR

                    @elseif($activity->status == 'Approved')

                        received DPR approval

                    @elseif($activity->status == 'Rejected')

                        received DPR rejection

                    @endif

                    for project

                    <span class="text-blue-600">

                        {{ $activity->project->project_name }}

                    </span>

                </p>

                <p class="text-sm text-gray-500 mt-1">

                    DPR Date:
                    {{ $activity->dpr_date }}

                    •

                    Status:
                    {{ $activity->status }}

                </p>

            </div>

        @endforeach

    </div>

</div>

</div>

@endsection