@extends('layouts.app')

@section('content')

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">
        Monthly Plan Progress Dashboard
    </h1>

    <p class="text-gray-500 mt-1">
        Monthly planning achievement and execution performance.
    </p>
</div>

@php

$totalPlans = $monthlyPlans->count();

$totalPlannedQty = $monthlyPlans->sum('planned_quantity');

$totalActualQty = 0;

$completedCount = 0;
$delayedCount = 0;

@endphp

@foreach($monthlyPlans as $plan)

    @php

        $actualQty =
            \App\Models\DprWorkItem::whereHas(
                'dpr',
                function($query) use ($plan)
                {
                    $query->where(
                        'project_id',
                        $plan->project_id
                    )
                    ->whereBetween(
                        'dpr_date',
                        [
                            $plan->month_start_date,
                            $plan->month_end_date
                        ]
                    );
                }
            )
            ->where(
                'activity_id',
                $plan->activity_id
            )
            ->sum('quantity_completed');

        $totalActualQty += $actualQty;

        $achievement =
            $plan->planned_quantity > 0
            ? (
                ($actualQty / $plan->planned_quantity) * 100
              )
            : 0;

        if($achievement >= 100)
        {
            $completedCount++;
        }

        if($achievement < 80)
        {
            $delayedCount++;
        }

    @endphp

@endforeach

@php

$overallAchievement =
    $totalPlannedQty > 0
    ? round(
        ($totalActualQty / $totalPlannedQty) * 100,
        2
      )
    : 0;

@endphp

<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">

    <div class="bg-white rounded shadow p-5 border-l-4 border-blue-600">
        <p class="text-gray-500 text-sm">Monthly Plans</p>
        <p class="text-3xl font-bold text-blue-700">
            {{ $totalPlans }}
        </p>
    </div>

    <div class="bg-white rounded shadow p-5 border-l-4 border-green-600">
        <p class="text-gray-500 text-sm">Planned Qty</p>
        <p class="text-3xl font-bold text-green-700">
            {{ number_format($totalPlannedQty, 2) }}
        </p>
    </div>

    <div class="bg-white rounded shadow p-5 border-l-4 border-indigo-600">
        <p class="text-gray-500 text-sm">Actual Qty</p>
        <p class="text-3xl font-bold text-indigo-700">
            {{ number_format($totalActualQty, 2) }}
        </p>
    </div>

    <div class="bg-white rounded shadow p-5 border-l-4 border-yellow-500">
        <p class="text-gray-500 text-sm">Achievement %</p>
        <p class="text-3xl font-bold text-yellow-700">
            {{ $overallAchievement }}%
        </p>
    </div>

    <div class="bg-white rounded shadow p-5 border-l-4 border-red-600">
        <p class="text-gray-500 text-sm">Delayed Activities</p>
        <p class="text-3xl font-bold text-red-700">
            {{ $delayedCount }}
        </p>
    </div>

</div>

<div class="bg-white rounded shadow overflow-hidden">

    <div class="overflow-x-auto">

        <table class="min-w-full text-sm">

            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">

                <tr>

                    <th class="p-4 text-left">Project</th>

                    <th class="p-4 text-left">Activity</th>

                    <th class="p-4 text-left">Engineer</th>

                    <th class="p-4 text-left">Monthly Target</th>

                    <th class="p-4 text-left">Actual DPR Qty</th>

                    <th class="p-4 text-left">Achievement %</th>

                    <th class="p-4 text-left">Status</th>

                </tr>

            </thead>

            <tbody>

                @foreach($monthlyPlans as $plan)

                    @php

                        $actualQty =
                            \App\Models\DprWorkItem::whereHas(
                                'dpr',
                                function($query) use ($plan)
                                {
                                    $query->where(
                                        'project_id',
                                        $plan->project_id
                                    )
                                    ->whereBetween(
                                        'dpr_date',
                                        [
                                            $plan->month_start_date,
                                            $plan->month_end_date
                                        ]
                                    );
                                }
                            )
                            ->where(
                                'activity_id',
                                $plan->activity_id
                            )
                            ->sum('quantity_completed');

                        $achievement =
                            $plan->planned_quantity > 0
                            ? round(
                                (
                                    $actualQty /
                                    $plan->planned_quantity
                                ) * 100,
                                2
                            )
                            : 0;

                        if($achievement >= 100)
                        {
                            $statusText = 'Ahead / Completed';
                            $statusClass =
                                'bg-green-100 text-green-800';
                        }
                        elseif($achievement >= 80)
                        {
                            $statusText = 'On Track';
                            $statusClass =
                                'bg-blue-100 text-blue-800';
                        }
                        elseif($achievement > 0)
                        {
                            $statusText = 'Behind';
                            $statusClass =
                                'bg-orange-100 text-orange-800';
                        }
                        else
                        {
                            $statusText = 'Not Started';
                            $statusClass =
                                'bg-red-100 text-red-800';
                        }

                    @endphp

                    <tr class="border-b">

                        <td class="p-4">
                            {{ $plan->project?->project_name ?? '-' }}
                        </td>

                        <td class="p-4">
                            {{ $plan->activity?->activity_name ?? '-' }}
                        </td>

                        <td class="p-4">
                            {{ $plan->user?->name ?? '-' }}
                        </td>

                        <td class="p-4">
                            {{ number_format($plan->planned_quantity,2) }}
                            {{ $plan->unit }}
                        </td>

                        <td class="p-4 font-bold">
                            {{ number_format($actualQty,2) }}
                        </td>

                        <td class="p-4 font-bold">
                            {{ $achievement }}%
                        </td>

                        <td class="p-4">
                            <span class="px-2 py-1 rounded text-xs {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>

</div>

@endsection