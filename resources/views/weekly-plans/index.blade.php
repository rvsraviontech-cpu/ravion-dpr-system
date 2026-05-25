@extends('layouts.app')

@section('content')

<div class="flex justify-between mb-6">

    <h1 class="text-3xl font-bold">
        Weekly Plans
    </h1>

    <a href="/weekly-plans/create"
       class="bg-blue-600 text-white px-4 py-2 rounded">

        Create Weekly Plan

    </a>

</div>

<div class="bg-white rounded shadow p-6">

    <table class="w-full">

        <thead class="bg-gray-100">

            <tr>

                <th class="p-4 text-left">
                    Project
                </th>

                <th class="p-4 text-left">
                    Activity
                </th>

                <th class="p-4 text-left">
                    Engineer
                </th>

                <th class="p-4 text-left">
                    Week
                </th>

                <th class="p-4 text-left">
                    Quantity
                </th>

                <th class="p-4 text-left">
                    Status
                </th>

            </tr>

        </thead>

        <tbody>

            @foreach($weeklyPlans as $plan)

            <tr class="border-t">

                <td class="p-4">

                    {{ $plan->project->project_name }}

                </td>

                <td class="p-4">

                    {{ $plan->activity->activity_name ?? '-' }}

                </td>

                <td class="p-4">

                    {{ $plan->user->name ?? '-' }}

                </td>

                <td class="p-4">

                    {{ $plan->week_start_date }}
                    to
                    {{ $plan->week_end_date }}

                </td>

                <td class="p-4">

                    {{ $plan->planned_quantity }}
                    {{ $plan->unit }}

                </td>

                <td class="p-4">

                    {{ $plan->status }}

                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endsection