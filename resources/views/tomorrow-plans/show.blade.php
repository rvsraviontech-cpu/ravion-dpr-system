@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Tomorrow Plan Details</h1>
        <p class="text-gray-500 mt-1">View complete tomorrow planning details.</p>
    </div>

    <a href="{{ route('tomorrow-plans.index') }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow">
        Back
    </a>
</div>

<div class="bg-white rounded shadow p-6">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div><strong>Project:</strong><br>{{ $tomorrowPlan->project?->project_name ?? '-' }}</div>
        <div><strong>Planned Date:</strong><br>{{ $tomorrowPlan->planned_date ?? '-' }}</div>
        <div><strong>Priority:</strong><br>{{ $tomorrowPlan->priority }}</div>

        <div><strong>Block:</strong><br>{{ $tomorrowPlan->block?->name ?? '-' }}</div>
        <div><strong>Floor:</strong><br>{{ $tomorrowPlan->floor?->name ?? '-' }}</div>
        <div><strong>Unit:</strong><br>{{ $tomorrowPlan->unit?->name ?? '-' }}</div>

        <div><strong>Room:</strong><br>{{ $tomorrowPlan->room?->name ?? '-' }}</div>
        <div><strong>Sub-Space:</strong><br>{{ $tomorrowPlan->subspace?->name ?? '-' }}</div>
        <div><strong>Activity:</strong><br>{{ $tomorrowPlan->activity?->activity_name ?? '-' }}</div>

        <div><strong>Contractor:</strong><br>{{ $tomorrowPlan->contractor?->contractor_name ?? '-' }}</div>
        <div><strong>Planned Qty:</strong><br>{{ $tomorrowPlan->planned_quantity }}</div>
        <div><strong>Unit:</strong><br>{{ $tomorrowPlan->unit ?? '-' }}</div>

        <div><strong>Total Labour:</strong><br>{{ $tomorrowPlan->planned_labour ?? 0 }}</div>
        <div><strong>Skilled Labour:</strong><br>{{ $tomorrowPlan->required_skilled_labour ?? 0 }}</div>
        <div><strong>Semi-Skilled Labour:</strong><br>{{ $tomorrowPlan->required_semiskilled_labour ?? 0 }}</div>

        <div><strong>Helpers:</strong><br>{{ $tomorrowPlan->required_helpers ?? 0 }}</div>
        <div><strong>Drawing Required:</strong><br>{{ $tomorrowPlan->drawing_required ? 'Yes' : 'No' }}</div>
        <div><strong>Client Approval Required:</strong><br>{{ $tomorrowPlan->client_approval_required ? 'Yes' : 'No' }}</div>

        <div><strong>Responsible Person:</strong><br>{{ $tomorrowPlan->responsible_person ?? '-' }}</div>
        <div><strong>Status:</strong><br>{{ $tomorrowPlan->status }}</div>
        <div><strong>Created By:</strong><br>{{ $tomorrowPlan->creator?->name ?? '-' }}</div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">

        <div>
            <strong>Materials Required:</strong>
            <div class="border rounded p-3 mt-2 bg-gray-50">
                {{ $tomorrowPlan->materials_required ?? '-' }}
            </div>
        </div>

        <div>
            <strong>Machinery Required:</strong>
            <div class="border rounded p-3 mt-2 bg-gray-50">
                {{ $tomorrowPlan->machinery_required ?? '-' }}
            </div>
        </div>

        <div>
            <strong>Risks / Constraints:</strong>
            <div class="border rounded p-3 mt-2 bg-gray-50">
                {{ $tomorrowPlan->risks_constraints ?? '-' }}
            </div>
        </div>

        <div>
            <strong>Remarks:</strong>
            <div class="border rounded p-3 mt-2 bg-gray-50">
                {{ $tomorrowPlan->remarks ?? '-' }}
            </div>
        </div>

    </div>

</div>

@endsection