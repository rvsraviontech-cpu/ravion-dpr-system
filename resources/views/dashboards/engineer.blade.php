@extends('layouts.app')

@section('content')

@php
    $todayDprSubmitted = ($todayDprs ?? 0) > 0;
@endphp

<div class="flex justify-between items-center mb-6">

    <div>
        <h1 class="text-3xl font-bold">
            Good Morning, {{ auth()->user()->name }} 👋
        </h1>

        <p class="text-gray-600 mt-1">
            Engineer Dashboard • {{ now()->format('d-M-Y') }}
        </p>
    </div>

    <a href="/dprs/create"
       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
        + Create DPR
    </a>

</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

    <div class="bg-white rounded-lg shadow p-6 border-l-4 {{ $todayDprSubmitted ? 'border-green-500' : 'border-red-500' }}">
        <p class="text-gray-500 text-sm">Today's DPR</p>
        <h2 class="text-2xl font-bold mt-2 {{ $todayDprSubmitted ? 'text-green-600' : 'text-red-600' }}">
            {{ $todayDprSubmitted ? 'Submitted' : 'Pending' }}
        </h2>
    </div>

    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
        <p class="text-gray-500 text-sm">Open Site Issues</p>
        <h2 class="text-3xl font-bold mt-2">{{ $openSiteIssues ?? 0 }}</h2>
    </div>

    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
        <p class="text-gray-500 text-sm">Pending Materials</p>
        <h2 class="text-3xl font-bold mt-2">{{ $pendingMaterialRequests ?? 0 }}</h2>
    </div>

    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
        <p class="text-gray-500 text-sm">Labour Today</p>
        <h2 class="text-3xl font-bold mt-2">{{ $labourToday ?? 0 }}</h2>
    </div>

</div>

<div class="bg-white rounded-lg shadow p-6 mb-8">

    <h2 class="text-2xl font-bold mb-4">
        My Pending Actions
    </h2>

    <div class="space-y-3">

        @if(!$todayDprSubmitted)
            <div class="bg-red-50 text-red-700 p-4 rounded border border-red-200">
                High Priority: Submit today’s DPR.
            </div>
        @else
            <div class="bg-green-50 text-green-700 p-4 rounded border border-green-200">
                Today’s DPR has been submitted.
            </div>
        @endif

        @if(($openSiteIssues ?? 0) > 0)
            <div class="bg-orange-50 text-orange-700 p-4 rounded border border-orange-200">
                {{ $openSiteIssues }} open site issue(s) need attention.
            </div>
        @endif

        @if(($pendingMaterialRequests ?? 0) > 0)
            <div class="bg-yellow-50 text-yellow-700 p-4 rounded border border-yellow-200">
                {{ $pendingMaterialRequests }} material request(s) pending.
            </div>
        @endif

    </div>

</div>

<div class="bg-white rounded-lg shadow p-6 mb-8">

    <div class="flex justify-between items-center">

        <div>
            <h2 class="text-2xl font-bold">
                Daily Progress Report
            </h2>

            <p class="mt-2 {{ $todayDprSubmitted ? 'text-green-600' : 'text-red-600' }}">
                {{ $todayDprSubmitted ? 'Today’s DPR has been submitted.' : 'Today’s DPR is not submitted yet.' }}
            </p>
        </div>

        @if(!$todayDprSubmitted)
            <a href="/dprs/create"
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
                Create DPR Now
            </a>
        @endif

    </div>

</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

    <div class="bg-white rounded-lg shadow p-6">

        <h2 class="text-xl font-bold mb-4">
            Quick Actions
        </h2>

        <div class="grid grid-cols-1 gap-3">

            <a href="/dprs/create" class="bg-blue-600 text-white px-4 py-3 rounded text-center">
                + Create DPR
            </a>

            <a href="/labour-reports/create" class="bg-gray-100 px-4 py-3 rounded text-center">
                Labour Reporting
            </a>

            <a href="/material-received/create" class="bg-gray-100 px-4 py-3 rounded text-center">
                Material Received
            </a>

            <a href="/material-consumed/create" class="bg-gray-100 px-4 py-3 rounded text-center">
                Material Consumed
            </a>

            <a href="/material-requirements/create" class="bg-gray-100 px-4 py-3 rounded text-center">
                Material Requirement
            </a>

            <a href="/site-issues/create" class="bg-gray-100 px-4 py-3 rounded text-center">
                Site Issue
            </a>

            <a href="/tomorrow-plans/create" class="bg-gray-100 px-4 py-3 rounded text-center">
                Tomorrow Plan
            </a>

        </div>

    </div>

    <div class="bg-white rounded-lg shadow p-6">

        <h2 class="text-xl font-bold mb-4">
            My Summary
        </h2>

        <div class="space-y-4">

            <div class="flex justify-between border-b pb-2">
                <span>Total DPRs Submitted</span>
                <strong>{{ $totalDprs ?? 0 }}</strong>
            </div>

            <div class="flex justify-between border-b pb-2">
                <span>Today's DPRs</span>
                <strong>{{ $todayDprs ?? 0 }}</strong>
            </div>

            <div class="flex justify-between border-b pb-2">
                <span>Open Site Issues</span>
                <strong>{{ $openSiteIssues ?? 0 }}</strong>
            </div>

            <div class="flex justify-between">
                <span>Labour Today</span>
                <strong>{{ $labourToday ?? 0 }}</strong>
            </div>

        </div>

    </div>

</div>

<div class="bg-white rounded shadow p-6">

    <h2 class="text-2xl font-bold mb-4">
        Recent DPRs
    </h2>

    <table class="w-full">

        <thead>
            <tr class="border-b bg-gray-50">
                <th class="text-left p-3">Date</th>
                <th class="text-left p-3">Project</th>
                <th class="text-left p-3">Status</th>
                <th class="text-left p-3">PMO Remarks</th>
            </tr>
        </thead>

        <tbody>

            @forelse($recentDprs as $dpr)

                <tr class="border-b">

                    <td class="p-3">
                        {{ \Carbon\Carbon::parse($dpr->dpr_date)->format('d-m-Y') }}
                    </td>

                    <td class="p-3">
                        {{ $dpr->project->project_name ?? '-' }}
                    </td>

                    <td class="p-3">
                        <span class="px-3 py-1 rounded-full text-sm font-semibold
                            @if($dpr->status == 'Approved') bg-green-100 text-green-700
                            @elseif($dpr->status == 'Rejected') bg-red-100 text-red-700
                            @else bg-yellow-100 text-yellow-700
                            @endif">
                            {{ $dpr->status }}
                        </span>
                    </td>

                    <td class="p-3">
                        {{ $dpr->pmo_remarks ?? '-' }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="4" class="p-4 text-center text-gray-500">
                        No DPRs found.
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>

@endsection