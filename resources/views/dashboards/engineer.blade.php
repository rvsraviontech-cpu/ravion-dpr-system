@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Engineer Dashboard
</h1>

<div class="grid grid-cols-2 gap-6 mb-8">

    <div class="grid grid-cols-2 gap-6 mb-8">

    <x-dashboard-card
        title="Total DPRs"
        :value="$totalDprs" />

    <x-dashboard-card
        title="Today's DPRs"
        :value="$todayDprs" />

</div>

</div>

<div class="mb-6">

    <a href="/dprs/create"
       class="bg-blue-500 text-white px-5 py-3 rounded">

        Create DPR

    </a>

</div>

<div class="bg-white rounded shadow p-6">

    <h2 class="text-2xl font-bold mb-4">
        Recent DPRs
    </h2>

    <table class="w-full">

        <thead>

            <tr class="border-b">

                <th class="text-left p-3">
                    Date
                </th>

                <th class="text-left p-3">
                    Project
                </th>

            </tr>

        </thead>

        <tbody>

            @foreach($recentDprs as $dpr)

            <tr class="border-b">

                <td class="p-3">
                    {{ $dpr->dpr_date }}
                </td>

                <td class="p-3">
                    {{ $dpr->project->project_name }}
                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endsection