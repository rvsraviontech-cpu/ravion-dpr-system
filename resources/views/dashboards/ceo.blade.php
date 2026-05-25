@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    CEO Dashboard
</h1>

<div class="grid grid-cols-4 gap-6 mb-8">

    <div class="bg-white p-6 rounded shadow">

        <h2 class="text-gray-500">
            Total Projects
        </h2>

        <p class="text-4xl font-bold mt-3">
            {{ $totalProjects }}
        </p>

    </div>

    <div class="bg-white p-6 rounded shadow">

        <h2 class="text-gray-500">
            Total DPRs
        </h2>

        <p class="text-4xl font-bold mt-3">
            {{ $totalDprs }}
        </p>

    </div>

    <div class="bg-white p-6 rounded shadow">

        <h2 class="text-gray-500">
            Approved DPRs
        </h2>

        <p class="text-4xl font-bold mt-3">
            {{ $approvedDprs }}
        </p>

    </div>

    <div class="bg-white p-6 rounded shadow">

        <h2 class="text-gray-500">
            Pending DPRs
        </h2>

        <p class="text-4xl font-bold mt-3">
            {{ $pendingDprs }}
        </p>

    </div>

</div>

<div class="bg-white rounded shadow p-6">

    <h2 class="text-2xl font-bold mb-4">
        Executive Summary
    </h2>

    <p class="text-gray-600">
        CEO analytics dashboard is working properly.
    </p>

</div>

@endsection