@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Weekly Plan Progress Dashboard
</h1>

<div class="bg-white rounded shadow p-6 overflow-auto">

    <table class="w-full border">

        <thead class="bg-gray-100">
            <tr>
                <th class="p-4 border text-left">Project</th>
                <th class="p-4 border text-left">Activity</th>
                <th class="p-4 border text-left">Engineer</th>
                <th class="p-4 border text-left">Weekly Target</th>
                <th class="p-4 border text-left">Actual DPR Qty</th>
                <th class="p-4 border text-left">Achievement %</th>
                <th class="p-4 border text-left">Status</th>
            </tr>
        </thead>

        <tbody>

            @foreach($weeklyPlans as $plan)

                @php
                    $actualQty = \App\Models\DprWorkItem::whereHas('dpr', function($query) use ($plan) {
                            $query->where('project_id', $plan->project_id)
                                  ->whereBetween('dpr_date', [
                                      $plan->week_start_date,
                                      $plan->week_end_date
                                  ]);
                        })
                        ->where('activity_id', $plan->activity_id)
                        ->sum('quantity_completed');

                    $achievement = $plan->planned_quantity > 0
                        ? round(($actualQty / $plan->planned_quantity) * 100, 2)
                        : 0;

                    if ($achievement >= 100) {
                        $statusText = 'Ahead / Completed';
                        $statusClass = 'bg-green-100 text-green-800';
                    } elseif ($achievement >= 80) {
                        $statusText = 'On Track';
                        $statusClass = 'bg-blue-100 text-blue-800';
                    } elseif ($achievement > 0) {
                        $statusText = 'Behind';
                        $statusClass = 'bg-orange-100 text-orange-800';
                    } else {
                        $statusText = 'Not Started';
                        $statusClass = 'bg-red-100 text-red-800';
                    }
                @endphp

                <tr>
                    <td class="p-4 border">
                        {{ $plan->project?->project_name ?? '-' }}
                    </td>

                    <td class="p-4 border">
                        {{ $plan->activity?->activity_name ?? '-' }}
                    </td>

                    <td class="p-4 border">
                        {{ $plan->user?->name ?? '-' }}
                    </td>

                    <td class="p-4 border">
                        {{ number_format($plan->planned_quantity, 2) }}
                        {{ $plan->unit }}
                    </td>

                    <td class="p-4 border font-bold">
                        {{ number_format($actualQty, 2) }}
                    </td>

                    <td class="p-4 border font-bold">
                        {{ $achievement }}%
                    </td>

                    <td class="p-4 border">
                        <span class="px-2 py-1 rounded text-xs {{ $statusClass }}">
                            {{ $statusText }}
                        </span>
                    </td>
                </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endsection