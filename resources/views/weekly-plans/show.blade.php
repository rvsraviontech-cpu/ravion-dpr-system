@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Weekly Plan Details</h1>
        <p class="text-gray-500 mt-1">View weekly planning target, resources and risks.</p>
    </div>

    <a href="{{ route('weekly-plans.index') }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow">
        Back
    </a>
</div>

<div class="bg-white rounded shadow p-6">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div><strong>Project:</strong><br>{{ $weeklyPlan->project?->project_name ?? '-' }}</div>
        <div><strong>Week Start Date:</strong><br>{{ $weeklyPlan->week_start_date ?? '-' }}</div>
        <div><strong>Week End Date:</strong><br>{{ $weeklyPlan->week_end_date ?? '-' }}</div>

        <div><strong>Activity:</strong><br>{{ $weeklyPlan->activity?->activity_name ?? '-' }}</div>
        <div><strong>Planned Quantity:</strong><br>{{ number_format($weeklyPlan->planned_quantity, 2) }}</div>
        <div><strong>Unit:</strong><br>{{ $weeklyPlan->unit ?? '-' }}</div>

        <div><strong>Planned Labour:</strong><br>{{ $weeklyPlan->planned_labour ?? 0 }}</div>
        <div><strong>Assigned Engineer:</strong><br>{{ $weeklyPlan->user?->name ?? '-' }}</div>
        <div><strong>Status:</strong><br>{{ $weeklyPlan->status }}</div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">

        <div>
            <strong>Materials Required:</strong>
            <div class="border rounded p-3 mt-2 bg-gray-50">
                {{ $weeklyPlan->materials_required ?? '-' }}
            </div>
        </div>

        <div>
            <strong>Machinery Required:</strong>
            <div class="border rounded p-3 mt-2 bg-gray-50">
                {{ $weeklyPlan->machinery_required ?? '-' }}
            </div>
        </div>

        <div>
            <strong>Risks / Constraints:</strong>
            <div class="border rounded p-3 mt-2 bg-gray-50">
                {{ $weeklyPlan->risks_constraints ?? '-' }}
            </div>
        </div>

        <div>
            <strong>Remarks:</strong>
            <div class="border rounded p-3 mt-2 bg-gray-50">
                {{ $weeklyPlan->remarks ?? '-' }}
            </div>
        </div>

    </div>

</div>

@endsection