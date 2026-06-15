@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    {{ $project->project_name }} Dashboard
</h1>
<div class="mb-6">
    <a
        href="{{ route('activity-progress.index', $project) }}"
        class="bg-blue-600 text-white px-4 py-2 rounded"
    >
        View Activity Progress
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

    <div class="bg-green-50 p-5 rounded shadow">
        <div>Monthly Planned Qty</div>
        <div class="text-3xl font-bold">
            {{ number_format($monthlyPlanned, 2) }}
        </div>
    </div>

    <div class="bg-blue-50 p-5 rounded shadow">
        <div>Weekly Planned Qty</div>
        <div class="text-3xl font-bold">
            {{ number_format($weeklyPlanned, 2) }}
        </div>
    </div>

    <div class="bg-purple-50 p-5 rounded shadow">
        <div>Completed Qty</div>
        <div class="text-3xl font-bold">
            {{ number_format($completedQty, 2) }}
        </div>
    </div>

    <div class="bg-orange-50 p-5 rounded shadow">
        <div>Progress</div>
        <div class="text-3xl font-bold">
            {{ $progress }}%
        </div>
    </div>

</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">

    <div class="bg-white p-5 rounded shadow border-l-4 border-green-500">
        <div>Total Labour Reports</div>
        <div class="text-3xl font-bold">
            {{ $labourReportsCount }}
        </div>
    </div>

    <div class="bg-white p-5 rounded shadow border-l-4 border-blue-500">
        <div>Total Labour Strength</div>
        <div class="text-3xl font-bold">
            {{ $totalLabour }}
        </div>
    </div>

    <div class="bg-white p-5 rounded shadow border-l-4 border-yellow-500">
        <div>Balance Qty</div>
        <div class="text-3xl font-bold">
            {{ number_format($balanceQty, 2) }}
        </div>
    </div>

    <div class="bg-white p-5 rounded shadow border-l-4 border-purple-500">
        <div>Material Received</div>
        <div class="text-3xl font-bold">
            {{ $materialsReceived }}
        </div>
    </div>

    <div class="bg-white p-5 rounded shadow border-l-4 border-red-500">
        <div>Material Consumed</div>
        <div class="text-3xl font-bold">
            {{ $materialsConsumed }}
        </div>
    </div>

    <div class="bg-white p-5 rounded shadow border-l-4 border-orange-500">
        <div>Remaining %</div>
        <div class="text-3xl font-bold">
            {{ $remainingPercent }}%
        </div>
    </div>

</div>

<div class="bg-white rounded shadow p-6 mb-8">

    <h2 class="text-xl font-semibold mb-4">
        Progress
    </h2>

    <div class="w-full bg-gray-200 rounded-full h-6">

        <div
            class="bg-green-600 h-6 rounded-full"
            style="width: {{ min($progress,100) }}%">
        </div>

    </div>

</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

    <div class="bg-white rounded shadow overflow-hidden">

        <div class="p-4 border-b">
            <h2 class="font-semibold">
                Recent DPR Entries
            </h2>
        </div>

        <table class="w-full">

            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left">Date</th>
                    <th class="p-3 text-left">Weather</th>
                    <th class="p-3 text-left">Status</th>
                </tr>
            </thead>

            <tbody>

                @forelse($dprs as $dpr)

                    <tr class="border-t">
                        <td class="p-3">{{ $dpr->dpr_date }}</td>
                        <td class="p-3">{{ $dpr->weather }}</td>
                        <td class="p-3">{{ $dpr->status }}</td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="3" class="p-3 text-center">
                            No DPR Entries Found
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div class="bg-white rounded shadow overflow-hidden">

        <div class="p-4 border-b">
            <h2 class="font-semibold">
                Recent Labour Reports
            </h2>
        </div>

        <table class="w-full">

            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left">Date</th>
                    <th class="p-3 text-left">Contractor</th>
                    <th class="p-3 text-left">Total Labour</th>
                </tr>
            </thead>

            <tbody>

                @forelse($labourReports as $report)

                    <tr class="border-t">
                        <td class="p-3">{{ $report->entry_date }}</td>
                        <td class="p-3">
                            {{ optional($report->contractor)->contractor_name }}
                        </td>
                        <td class="p-3">
                            {{ $report->total_labour }}
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="3" class="p-3 text-center">
                            No Labour Reports Found
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection