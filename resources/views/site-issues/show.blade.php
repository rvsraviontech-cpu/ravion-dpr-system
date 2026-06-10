@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Site Issue Details</h1>
        <p class="text-gray-500 mt-1">View issue, delay, escalation and resolution details.</p>
    </div>

    <a href="{{ route('site-issues.index') }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow">
        Back
    </a>
</div>

<div class="bg-white rounded shadow p-6">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div><strong>Project:</strong><br>{{ $siteIssue->project?->project_name ?? '-' }}</div>
        <div><strong>Issue Date:</strong><br>{{ $siteIssue->issue_date ?? '-' }}</div>
        <div><strong>Issue Type:</strong><br>{{ $siteIssue->issue_type }}</div>

        <div><strong>Title:</strong><br>{{ $siteIssue->title ?? '-' }}</div>
        <div><strong>Priority:</strong><br>{{ $siteIssue->priority }}</div>
        <div><strong>Status:</strong><br>{{ $siteIssue->status }}</div>

        <div><strong>Block:</strong><br>{{ $siteIssue->block?->name ?? '-' }}</div>
        <div><strong>Floor:</strong><br>{{ $siteIssue->floor?->name ?? '-' }}</div>
        <div><strong>Unit:</strong><br>{{ $siteIssue->unit?->name ?? '-' }}</div>

        <div><strong>Room:</strong><br>{{ $siteIssue->room?->name ?? '-' }}</div>
        <div><strong>Sub-Space:</strong><br>{{ $siteIssue->subspace?->name ?? '-' }}</div>
        <div><strong>Activity:</strong><br>{{ $siteIssue->activity?->activity_name ?? '-' }}</div>

        <div><strong>Responsible Person:</strong><br>{{ $siteIssue->responsible_person ?? '-' }}</div>
        <div><strong>Target Closure Date:</strong><br>{{ $siteIssue->target_closure_date ?? '-' }}</div>
        <div><strong>Actual Closure Date:</strong><br>{{ $siteIssue->actual_closure_date ?? '-' }}</div>

        <div><strong>Escalated to PMO:</strong><br>{{ $siteIssue->escalated_to_pmo ? 'Yes' : 'No' }}</div>
        <div><strong>Escalated to Management:</strong><br>{{ $siteIssue->escalated_to_management ? 'Yes' : 'No' }}</div>
        <div><strong>Created By:</strong><br>{{ $siteIssue->creator?->name ?? '-' }}</div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">

        <div>
            <strong>Description:</strong>
            <div class="border rounded p-3 mt-2 bg-gray-50">
                {{ $siteIssue->description ?? '-' }}
            </div>
        </div>

        <div>
            <strong>Root Cause:</strong>
            <div class="border rounded p-3 mt-2 bg-gray-50">
                {{ $siteIssue->root_cause ?? '-' }}
            </div>
        </div>

        <div>
            <strong>Resolution:</strong>
            <div class="border rounded p-3 mt-2 bg-gray-50">
                {{ $siteIssue->resolution ?? '-' }}
            </div>
        </div>

        <div>
            <strong>Remarks:</strong>
            <div class="border rounded p-3 mt-2 bg-gray-50">
                {{ $siteIssue->remarks ?? '-' }}
            </div>
        </div>

    </div>

</div>

@endsection