@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Audit Log Details
</h1>

<div class="mb-4">
    <a href="{{ route('audit-logs.index') }}"
       class="text-blue-600 hover:underline">
        ← Back to Audit Trail
    </a>
</div>

<div class="bg-white rounded shadow p-6">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

        <div>
            <strong>Date:</strong>
            {{ $auditLog->created_at->format('d-m-Y H:i:s') }}
        </div>

        <div>
            <strong>User:</strong>
            {{ optional($auditLog->user)->name ?? 'System' }}
        </div>

        <div>
            <strong>Module:</strong>
            {{ $auditLog->module }}
        </div>

        <div>
            <strong>Action:</strong>
            {{ $auditLog->action }}
        </div>

        <div>
            <strong>Record Type:</strong>
            {{ $auditLog->record_type ?? '-' }}
        </div>

        <div>
            <strong>Record ID:</strong>
            {{ $auditLog->record_id ?? '-' }}
        </div>

        <div>
            <strong>IP Address:</strong>
            {{ $auditLog->ip_address ?? '-' }}
        </div>

        <div>
            <strong>User Agent:</strong>
            {{ $auditLog->user_agent ?? '-' }}
        </div>

    </div>

    <div class="mb-6">
        <strong>Description:</strong>
        <div class="mt-2">
            {{ $auditLog->description ?? '-' }}
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div>
            <h2 class="text-xl font-semibold mb-2">
                Old Values
            </h2>

            <pre class="bg-gray-100 p-4 rounded overflow-auto text-sm">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
        </div>

        <div>
            <h2 class="text-xl font-semibold mb-2">
                New Values
            </h2>

            <pre class="bg-gray-100 p-4 rounded overflow-auto text-sm">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
        </div>

    </div>

</div>

@endsection