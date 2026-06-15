@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Project Progress Dashboard
</h1>

{{-- Summary Cards --}}

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500">Projects</div>
        <div class="text-3xl font-bold">
            {{ $summary['projects'] }}
        </div>
    </div>

    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500">Weekly Plans</div>
        <div class="text-3xl font-bold">
            {{ $summary['weeklyPlans'] }}
        </div>
    </div>

    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500">Monthly Plans</div>
        <div class="text-3xl font-bold">
            {{ $summary['monthlyPlans'] }}
        </div>
    </div>

    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500">DPR Entries</div>
        <div class="text-3xl font-bold">
            {{ $summary['dprs'] }}
        </div>
    </div>

    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500">Activity Mappings</div>
        <div class="text-3xl font-bold">
            {{ $summary['activityMappings'] }}
        </div>
    </div>

    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500">Material Received</div>
        <div class="text-3xl font-bold">
            {{ $summary['materialReceived'] }}
        </div>
    </div>

    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500">Material Consumed</div>
        <div class="text-3xl font-bold">
            {{ $summary['materialConsumed'] }}
        </div>
    </div>

</div>

{{-- Executive KPI Cards --}}

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

    <div class="bg-green-50 border border-green-200 p-5 rounded shadow">
        <div class="text-gray-600">Total Monthly Planned Qty</div>
        <div class="text-3xl font-bold text-green-700">
            {{ number_format($summary['totalMonthlyPlanned'], 2) }}
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 p-5 rounded shadow">
        <div class="text-gray-600">Total Weekly Planned Qty</div>
        <div class="text-3xl font-bold text-blue-700">
            {{ number_format($summary['totalWeeklyPlanned'], 2) }}
        </div>
    </div>

    <div class="bg-purple-50 border border-purple-200 p-5 rounded shadow">
        <div class="text-gray-600">Total Completed Qty</div>
        <div class="text-3xl font-bold text-purple-700">
            {{ number_format($summary['totalCompleted'], 2) }}
        </div>
    </div>

    <div class="bg-orange-50 border border-orange-200 p-5 rounded shadow">
        <div class="text-gray-600">Overall Progress</div>
        <div class="text-3xl font-bold text-orange-700">
            {{ $summary['overallProgress'] }}%
        </div>
    </div>

</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

    <div class="bg-white p-5 rounded shadow border-l-4 border-green-500">
        <div class="text-gray-600">Approved DPRs</div>
        <div class="text-3xl font-bold">
            {{ $summary['approvedDprs'] }}
        </div>
    </div>

    <div class="bg-white p-5 rounded shadow border-l-4 border-red-500">
        <div class="text-gray-600">Rejected DPRs</div>
        <div class="text-3xl font-bold">
            {{ $summary['rejectedDprs'] }}
        </div>
    </div>

    <div class="bg-white p-5 rounded shadow border-l-4 border-blue-500">
        <div class="text-gray-600">Verified Materials</div>
        <div class="text-3xl font-bold">
            {{ $summary['verifiedMaterials'] }}
        </div>
    </div>

    <div class="bg-white p-5 rounded shadow border-l-4 border-yellow-500">
        <div class="text-gray-600">Pending Activity Mapping</div>
        <div class="text-3xl font-bold">
            {{ $summary['pendingActivityMapping'] }}
        </div>
    </div>

</div>

{{-- Charts --}}

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-4">
            Project Progress %
        </h2>

        <canvas id="projectProgressChart"></canvas>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-4">
            Materials Overview
        </h2>

        <canvas id="materialChart"></canvas>
    </div>

</div>

{{-- Progress Table --}}

<div class="bg-white rounded shadow overflow-hidden mt-8">

    <div class="p-4 border-b">
        <h2 class="text-xl font-semibold">
            Project Progress Summary
        </h2>
    </div>

    <table class="w-full">

        <thead class="bg-gray-100">

            <tr>
                <th class="p-4 text-left">Project</th>
                <th class="p-4 text-left">Monthly Plan</th>
                <th class="p-4 text-left">Weekly Plan</th>
                <th class="p-4 text-left">Completed</th>
                <th class="p-4 text-left">Progress</th>
            </tr>

        </thead>

        <tbody>

            @forelse($projectProgress as $row)

                <tr class="border-t">

                    <td class="p-4">
                        <a
    href="{{ route('project-dashboard.show', $row['project']) }}"
    class="text-blue-600 hover:underline"
>
    {{ $row['project']->project_name }}
</a>
                    </td>

                    <td class="p-4">
                        {{ number_format($row['monthly_planned'], 2) }}
                    </td>

                    <td class="p-4">
                        {{ number_format($row['weekly_planned'], 2) }}
                    </td>

                    <td class="p-4">
                        {{ number_format($row['completed'], 2) }}
                    </td>

                    <td class="p-4">

                        <div class="w-full bg-gray-200 rounded-full h-4">

                            <div
                                class="bg-green-600 h-4 rounded-full"
                                style="width: {{ $row['progress'] }}%">
                            </div>

                        </div>

                        <div class="text-sm mt-1">
                            {{ $row['progress'] }}%
                        </div>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="5" class="p-4 text-center">
                        No Project Data Found
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const projectCanvas = document.getElementById('projectProgressChart');

    if (projectCanvas) {

        new Chart(projectCanvas, {

            type: 'bar',

            data: {

                labels: {!! json_encode($chartProjects ?? []) !!},

                datasets: [{

                    label: 'Progress %',

                    data: {!! json_encode($chartProgress ?? []) !!}

                }]
            }
        });
    }

    const materialCanvas = document.getElementById('materialChart');

    if (materialCanvas) {

        new Chart(materialCanvas, {

            type: 'pie',

            data: {

                labels: [
                    'Received',
                    'Consumed'
                ],

                datasets: [{

                    data: {!! json_encode($materialChart ?? [0,0]) !!}

                }]
            }
        });
    }

});

</script>

@endsection