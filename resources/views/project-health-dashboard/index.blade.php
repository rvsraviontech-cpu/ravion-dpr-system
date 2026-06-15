@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Project Health Dashboard
</h1>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">

    <div class="bg-white p-5 rounded shadow">
        <div>Total Projects</div>
        <div class="text-3xl font-bold">
            {{ $summary['totalProjects'] }}
        </div>
    </div>

    <div class="bg-green-50 p-5 rounded shadow border-l-4 border-green-500">
        <div>Green Projects</div>
        <div class="text-3xl font-bold">
            {{ $summary['greenProjects'] }}
        </div>
    </div>

    <div class="bg-yellow-50 p-5 rounded shadow border-l-4 border-yellow-500">
        <div>Amber Projects</div>
        <div class="text-3xl font-bold">
            {{ $summary['amberProjects'] }}
        </div>
    </div>

    <div class="bg-red-50 p-5 rounded shadow border-l-4 border-red-500">
        <div>Red Projects</div>
        <div class="text-3xl font-bold">
            {{ $summary['redProjects'] }}
        </div>
    </div>

</div>

<div class="bg-white rounded shadow overflow-hidden">

    <table class="w-full">

        <thead class="bg-gray-100">
            <tr>
                <th class="p-4 text-left">Project</th>
                <th class="p-4 text-left">Target Qty</th>
                <th class="p-4 text-left">Completed Qty</th>
                <th class="p-4 text-left">Progress</th>
                <th class="p-4 text-left">Approved DPRs</th>
                <th class="p-4 text-left">Rejected DPRs</th>
                <th class="p-4 text-left">Labour Reports</th>
                <th class="p-4 text-left">Materials</th>
                <th class="p-4 text-left">Health</th>
                <th class="p-4 text-left">Remarks</th>
            </tr>
        </thead>

        <tbody>

            @forelse($projectHealth as $row)

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
                        {{ number_format($row['target'], 2) }}
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

                    <td class="p-4">
                        {{ $row['approved_dprs'] }}
                    </td>

                    <td class="p-4">
                        {{ $row['rejected_dprs'] }}
                    </td>

                    <td class="p-4">
                        {{ $row['labour_reports'] }}
                    </td>

                    <td class="p-4">
                        R: {{ $row['materials_received'] }}
                        <br>
                        C: {{ $row['materials_consumed'] }}
                    </td>

                    <td class="p-4">
                        @if($row['health'] == 'Green')
                            <span class="px-3 py-1 rounded bg-green-100 text-green-700 font-semibold">
                                Green
                            </span>
                        @elseif($row['health'] == 'Amber')
                            <span class="px-3 py-1 rounded bg-yellow-100 text-yellow-700 font-semibold">
                                Amber
                            </span>
                        @else
                            <span class="px-3 py-1 rounded bg-red-100 text-red-700 font-semibold">
                                Red
                            </span>
                        @endif
                    </td>

                    <td class="p-4">
                        {{ $row['remarks'] }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="10" class="p-4 text-center">
                        No Project Health Data Found
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>

@endsection