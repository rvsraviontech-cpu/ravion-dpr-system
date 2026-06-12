@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Monthly Plan Details</h1>
        <p class="text-gray-500 mt-1">View monthly planning target, resources and risks.</p>
    </div>

    <a href="{{ route('monthly-plans.index') }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow">
        Back
    </a>
</div>

<div class="bg-white rounded shadow p-6">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div><strong>Project:</strong><br>{{ $monthlyPlan->project?->project_name ?? '-' }}</div>
        <div><strong>Month:</strong><br>{{ DateTime::createFromFormat('!m', $monthlyPlan->plan_month)->format('F') }} {{ $monthlyPlan->plan_year }}</div>
        <div><strong>Status:</strong><br>{{ $monthlyPlan->status }}</div>

        <div><strong>Start Date:</strong><br>{{ $monthlyPlan->month_start_date ?? '-' }}</div>
        <div><strong>End Date:</strong><br>{{ $monthlyPlan->month_end_date ?? '-' }}</div>
        <div><strong>Engineer:</strong><br>{{ $monthlyPlan->user?->name ?? '-' }}</div>

        <div><strong>Activity:</strong><br>{{ $monthlyPlan->activity?->activity_name ?? '-' }}</div>
        <div><strong>Planned Quantity:</strong><br>{{ number_format($monthlyPlan->planned_quantity, 2) }}</div>
        <div><strong>Unit:</strong><br>{{ $monthlyPlan->unit ?? '-' }}</div>

        <div><strong>Planned Labour:</strong><br>{{ $monthlyPlan->planned_labour ?? 0 }}</div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">

        <div>
            <strong>Materials Required:</strong>
            <div class="border rounded p-3 mt-2 bg-gray-50">
                {{ $monthlyPlan->materials_required ?? '-' }}
            </div>
        </div>

        <div>
            <strong>Machinery Required:</strong>
            <div class="border rounded p-3 mt-2 bg-gray-50">
                {{ $monthlyPlan->machinery_required ?? '-' }}
            </div>
        </div>

        <div>
            <strong>Risks / Constraints:</strong>
            <div class="border rounded p-3 mt-2 bg-gray-50">
                {{ $monthlyPlan->risks_constraints ?? '-' }}
            </div>
        </div>

        <div>
            <strong>Remarks:</strong>
            <div class="border rounded p-3 mt-2 bg-gray-50">
                {{ $monthlyPlan->remarks ?? '-' }}
            </div>
        </div>

    </div>

</div>

@endsection