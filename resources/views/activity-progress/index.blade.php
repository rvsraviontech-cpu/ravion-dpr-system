@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Activity Progress - {{ $project->project_name }}
</h1>

<div class="mb-6">
    <a
        href="{{ route('project-dashboard.show', $project) }}"
        class="text-blue-600 hover:underline"
    >
        ← Back to Project Dashboard
    </a>
</div>

<div class="bg-white rounded shadow overflow-hidden">

    <table class="w-full">

        <thead class="bg-gray-100">
            <tr>
                <th class="p-4 text-left">Activity</th>
                <th class="p-4 text-left">Planned Qty</th>
                <th class="p-4 text-left">Completed Qty</th>
                <th class="p-4 text-left">Balance Qty</th>
                <th class="p-4 text-left">Progress</th>
            </tr>
        </thead>

        <tbody>

            @forelse($activityProgress as $row)

                <tr class="border-t">

                    <td class="p-4">
                        {{ optional($row['activity'])->activity_name ?? 'Activity Not Found' }}
                    </td>

                    <td class="p-4">
                        {{ number_format($row['planned'], 2) }}
                    </td>

                    <td class="p-4">
                        {{ number_format($row['completed'], 2) }}
                    </td>

                    <td class="p-4">
                        {{ number_format($row['balance'], 2) }}
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
                        No Activity Progress Found
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>

@endsection