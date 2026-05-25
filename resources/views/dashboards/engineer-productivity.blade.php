@extends('layouts.app')

@section('content')

<h1 class="text-4xl font-bold mb-8">
    Engineer Productivity Dashboard
</h1>

<div class="bg-white rounded shadow p-6">

    <table class="w-full">

        <thead class="bg-gray-100">

            <tr>

                <th class="p-4 text-left">
                    Engineer
                </th>

                <th class="p-4 text-left">
                    DPR Count
                </th>

                <th class="p-4 text-left">
                    Approved
                </th>

                <th class="p-4 text-left">
                    Rejected
                </th>

                <th class="p-4 text-left">
                    Total Quantity
                </th>
                <th class="p-4 text-left">
                    Last DPR
                </th>

                <th class="p-4 text-left">
                    Reporting Status
                </th>

            </tr>

        </thead>

        <tbody>

            @foreach($engineers as $engineer)

            <tr class="border-t">

                <td class="p-4 font-semibold">
                    {{ $engineer->name }}
                </td>

                <td class="p-4">
                    {{ $engineer->dprs->count() }}
                </td>

                <td class="p-4 text-green-700 font-bold">
                    {{
                        $engineer->dprs
                            ->where('status', 'Approved')
                            ->count()
                    }}
                </td>

                <td class="p-4 text-red-700 font-bold">
                    {{
                        $engineer->dprs
                            ->where('status', 'Rejected')
                            ->count()
                    }}
                </td>

                <td class="p-4">

                    {{
                        $engineer->dprs
                            ->flatMap->workItems
                            ->sum('quantity_completed')
                    }}

                </td>
                <td class="p-4">

    {{

        optional(
            $engineer->dprs
                ->sortByDesc('dpr_date')
                ->first()
        )->dpr_date ?? 'No DPR'

    }}

</td>
<td class="p-4">

@php

    $latestDpr =
        $engineer->dprs
            ->sortByDesc('dpr_date')
            ->first();

@endphp

@if(!$latestDpr)

    <span class="bg-gray-200
                 text-gray-800
                 px-3 py-1
                 rounded">

        No Reporting

    </span>

@elseif(
    \Carbon\Carbon::parse($latestDpr->dpr_date)
        ->diffInDays(now()) <= 2
)

    <span class="bg-green-200
                 text-green-800
                 px-3 py-1
                 rounded">

        Active

    </span>

@elseif(
    \Carbon\Carbon::parse($latestDpr->dpr_date)
        ->diffInDays(now()) <= 7
)

    <span class="bg-yellow-200
                 text-yellow-800
                 px-3 py-1
                 rounded">

        Warning

    </span>

@else

    <span class="bg-red-200
                 text-red-800
                 px-3 py-1
                 rounded">

        Overdue

    </span>

@endif

</td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endsection