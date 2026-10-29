@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    PMO Dashboard
</h1>

<div class="grid grid-cols-3 gap-6 mb-8">

    <div class="grid grid-cols-3 gap-6 mb-8">

    <x-dashboard-card
        title="Pending DPRs"
        :value="$pendingDprs ?? ($counts['dpr_pending'] ?? 0)" />

    <x-dashboard-card
        title="Approved DPRs"
        :value="$approvedDprs ?? 0" />

    <x-dashboard-card
        title="Rejected DPRs"
        :value="$rejectedDprs ?? 0" />

</div>

</div>

<div class="bg-white rounded shadow p-6">

    <h2 class="text-2xl font-bold mb-4">
        Quick Actions
    </h2>

    <a href="/pmo/dprs"
       class="bg-blue-500 text-white px-5 py-3 rounded">

        Open DPR Review Queue

    </a>

</div>

@include('pmo.partials.work_done_approvals')

@endsection