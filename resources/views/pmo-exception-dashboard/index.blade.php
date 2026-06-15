@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    PMO Exception Dashboard
</h1>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">

    <div class="bg-red-50 p-5 rounded shadow border-l-4 border-red-500">
        <div>Rejected DPRs</div>
        <div class="text-3xl font-bold">
            {{ $summary['rejectedDprs'] }}
        </div>
    </div>

    <div class="bg-yellow-50 p-5 rounded shadow border-l-4 border-yellow-500">
        <div>Pending Activity Mapping</div>
        <div class="text-3xl font-bold">
            {{ $summary['pendingActivityMappings'] }}
        </div>
    </div>

    <div class="bg-blue-50 p-5 rounded shadow border-l-4 border-blue-500">
        <div>Unplanned Activities</div>
        <div class="text-3xl font-bold">
            {{ $summary['unplannedActivities'] }}
        </div>
    </div>

    <div class="bg-purple-50 p-5 rounded shadow border-l-4 border-purple-500">
        <div>Planned Not Started</div>
        <div class="text-3xl font-bold">
            {{ $summary['plannedButNotStarted'] }}
        </div>
    </div>

</div>

{{-- Rejected DPRs --}}

<div class="bg-white rounded shadow mb-8 overflow-hidden">

    <div class="p-4 border-b">
        <h2 class="text-xl font-semibold">
            Rejected DPRs
        </h2>
    </div>

    <table class="w-full">

        <thead class="bg-gray-100">
            <tr>
                <th class="p-4 text-left">Project</th>
                <th class="p-4 text-left">DPR Date</th>
                <th class="p-4 text-left">Engineer</th>
                <th class="p-4 text-left">Status</th>
            </tr>
        </thead>

        <tbody>

            @forelse($rejectedDprs as $dpr)

                <tr class="border-t">
                    <td class="p-4">
                        {{ optional($dpr->project)->project_name }}
                    </td>

                    <td class="p-4">
                        {{ $dpr->dpr_date }}
                    </td>

                    <td class="p-4">
                        {{ optional($dpr->user)->name }}
                    </td>

                    <td class="p-4">
                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded">
                            Rejected
                        </span>
                    </td>
                </tr>

            @empty

                <tr>
                    <td colspan="4" class="p-4 text-center">
                        No Rejected DPRs
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>

{{-- Pending Activity Mapping --}}

<div class="bg-white rounded shadow mb-8 overflow-hidden">

    <div class="p-4 border-b">
        <h2 class="text-xl font-semibold">
            Pending Activity Mapping
        </h2>
    </div>

    <table class="w-full">

        <thead class="bg-gray-100">
            <tr>
                <th class="p-4 text-left">Project</th>
                <th class="p-4 text-left">Activity</th>
                <th class="p-4 text-left">Completed Qty</th>
            </tr>
        </thead>

        <tbody>

            @forelse($pendingActivityMappings as $item)

                <tr class="border-t">

                    <td class="p-4">
                        {{ optional(optional($item->dpr)->project)->project_name }}
                    </td>

                    <td class="p-4">
                        {{ optional($item->activity)->activity_name }}
                    </td>

                    <td class="p-4">
                        {{ $item->quantity_completed }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="3" class="p-4 text-center">
                        No Pending Activity Mappings
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>

{{-- Unplanned Activities --}}

<div class="bg-white rounded shadow mb-8 overflow-hidden">

    <div class="p-4 border-b">
        <h2 class="text-xl font-semibold">
            Unplanned Activities
        </h2>
    </div>

    <table class="w-full">

        <thead class="bg-gray-100">
            <tr>
                <th class="p-4 text-left">Project</th>
                <th class="p-4 text-left">Activity</th>
                <th class="p-4 text-left">Completed Qty</th>
            </tr>
        </thead>

        <tbody>

            @forelse($unplannedActivities as $row)

                <tr class="border-t">

                    <td class="p-4">
                        {{ $row['project']->project_name }}
                    </td>

                    <td class="p-4">
                        {{ optional($row['activity'])->activity_name }}
                    </td>

                    <td class="p-4">
                        {{ number_format($row['completed_qty'], 2) }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="3" class="p-4 text-center">
                        No Unplanned Activities
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>

{{-- Planned But Not Started --}}

<div class="bg-white rounded shadow overflow-hidden">

    <div class="p-4 border-b">
        <h2 class="text-xl font-semibold">
            Planned But Not Started
        </h2>
    </div>

    <table class="w-full">

        <thead class="bg-gray-100">
            <tr>
                <th class="p-4 text-left">Project</th>
                <th class="p-4 text-left">Activity</th>
                <th class="p-4 text-left">Planned Qty</th>
            </tr>
        </thead>

        <tbody>

            @forelse($plannedButNotStarted as $row)

                <tr class="border-t">

                    <td class="p-4">
                        {{ $row['project']->project_name }}
                    </td>

                    <td class="p-4">
                        {{ optional($row['activity'])->activity_name }}
                    </td>

                    <td class="p-4">
                        {{ number_format($row['planned_qty'], 2) }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="3" class="p-4 text-center">
                        No Planned Activities Pending
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>

@endsection