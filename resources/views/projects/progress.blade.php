@extends('layouts.app')

@section('content')

<h1 class="text-4xl font-bold mb-8">
    Project Progress Dashboard
</h1>

<div class="bg-white rounded shadow p-6">

    <table class="w-full">

        <thead class="bg-gray-100">

            <tr>

                <th class="p-4 text-left">
                    Project
                </th>

                <th class="p-4 text-left">
                    Engineers
                </th>

                <th class="p-4 text-left">
                    DPR Count
                </th>

                <th class="p-4 text-left">
                    Total Quantity
                </th>

                <th class="p-4 text-left">
                    Last DPR Date
                </th>
                <th class="p-4 text-left">
                    Project Status
                </th>

            </tr>

        </thead>

        <tbody>

@foreach($projects as $project)

<tr class="border-t">

    <!-- Project -->

    <td class="p-4 font-semibold">
        {{ $project->project_name }}
    </td>

    <!-- Engineers -->

    <td class="p-4">

        @foreach($project->users as $user)

            <span class="bg-blue-100
                         text-blue-800
                         px-2 py-1
                         rounded
                         text-sm
                         mr-2">

                {{ $user->name }}

            </span>

        @endforeach

    </td>

    <!-- DPR Count -->

    <td class="p-4">
        {{ $project->dprs->count() }}
    </td>

    <!-- Total Quantity -->

    <td class="p-4">

        {{
            $project->dprs
                ->flatMap->workItems
                ->sum('quantity_completed')
        }}

    </td>

    <!-- Last DPR -->

    <td class="p-4">

        {{
            optional(
                $project->dprs
                    ->sortByDesc('dpr_date')
                    ->first()
            )->dpr_date ?? 'No DPR'
        }}

    </td>

    <!-- Project Status -->

    <td class="p-4">

        @php

            $latestDpr =
                $project->dprs
                    ->sortByDesc('dpr_date')
                    ->first();

        @endphp

        @if(!$latestDpr)

            <span class="bg-gray-200
                         text-gray-800
                         px-3 py-1
                         rounded">

                Not Started

            </span>

        @elseif(
            \Carbon\Carbon::parse($latestDpr->dpr_date)
                ->diffInDays(now()) <= 3
        )

            <span class="bg-green-200
                         text-green-800
                         px-3 py-1
                         rounded">

                Active

            </span>

        @elseif(
            \Carbon\Carbon::parse($latestDpr->dpr_date)
                ->diffInDays(now()) >= 7
        )

            <span class="bg-red-200
                         text-red-800
                         px-3 py-1
                         rounded">

                Delayed

            </span>

        @else

            <span class="bg-yellow-200
                         text-yellow-800
                         px-3 py-1
                         rounded">

                Moderate

            </span>

        @endif

    </td>

</tr>

@endforeach

</tbody>
        

    </table>

</div>

@endsection