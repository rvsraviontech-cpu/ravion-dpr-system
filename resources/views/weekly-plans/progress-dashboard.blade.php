@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Weekly Plan Progress Dashboard
</h1>

<div class="bg-white rounded shadow p-6 overflow-auto">

    <table class="w-full border">

        <thead class="bg-gray-100">

            <tr>

                <th class="p-4 border text-left">
                    Project
                </th>

                <th class="p-4 border text-left">
                    Activity
                </th>

                <th class="p-4 border text-left">
                    Engineer
                </th>

                <th class="p-4 border text-left">
                    Weekly Target
                </th>

                <th class="p-4 border text-left">
                    Actual DPR Qty
                </th>

                <th class="p-4 border text-left">
                    Achievement %
                </th>

                <th class="p-4 border text-left">
                    Status
                </th>

            </tr>

        </thead>

        <tbody>

            @foreach($weeklyPlans as $plan)

                @php

                    $actualQty =
                        \App\Models\DprWorkDone::whereHas(
                            'dpr',
                            function($query) use ($plan)
                            {
                                $query->where(
                                    'project_id',
                                    $plan->project_id
                                );
                            }
                        )
                        ->where(
                            'activity_id',
                            $plan->activity_id
                        )
                        ->whereBetween(
                            'created_at',
                            [
                                $plan->week_start_date,
                                $plan->week_end_date
                            ]
                        )
                        ->sum('quantity');

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

                    $statusColor = 'text-red-600';

                    if($achievement >= 90)
                    {
                        $statusColor = 'text-green-600';
                    }
                    elseif($achievement >= 70)
                    {
                        $statusColor = 'text-yellow-600';
                    }

                @endphp

                <tr>

                    <td class="p-4 border">

                        {{ $plan->project->project_name }}

                    </td>

                    <td class="p-4 border">

                        {{ $plan->activity->activity_name ?? '-' }}

                    </td>

                    <td class="p-4 border">

                        {{ $plan->user->name ?? '-' }}

                    </td>

                    <td class="p-4 border">

                        {{ $plan->planned_quantity }}
                        {{ $plan->unit }}

                    </td>

                    <td class="p-4 border">

                        {{ $actualQty }}

                    </td>

                    <td class="p-4 border font-bold {{ $statusColor }}">

                        {{ $achievement }}%

                    </td>

                    <td class="p-4 border">

                        @if($achievement >= 90)

                            🟢 On Track

                        @elseif($achievement >= 70)

                            🟡 Attention

                        @else

                            🔴 Delayed

                        @endif

                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endsection