@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">

    <h1 class="text-4xl font-bold">
        DPR Report
    </h1>

    <button onclick="window.print()"
            class="bg-blue-600 text-white px-5 py-2 rounded shadow">

        Print DPR

    </button>
<a href="/dprs/{{ $dpr->id }}/pdf"
   class="bg-green-600 text-white px-5 py-2 rounded shadow">

    Download PDF

</a>
</div>

<!-- Header Information -->

<div class="bg-white rounded shadow p-6 mb-6">

    <div class="grid grid-cols-2 gap-8">

        <div>

            <p class="mb-4">
                <span class="font-bold">
                    Project:
                </span>

                {{ $dpr->project->project_name }}
            </p>

            <p class="mb-4">
                <span class="font-bold">
                    Engineer:
                </span>

                {{ $dpr->user->name }}
            </p>

            <p class="mb-4">
                <span class="font-bold">
                    DPR Date:
                </span>

                {{ $dpr->dpr_date }}
            </p>

        </div>

        <div>

            <p class="mb-4">
                <span class="font-bold">
                    Weather:
                </span>

                {{ $dpr->weather }}
            </p>

            <p class="mb-4">

                <span class="font-bold">
                    Status:
                </span>

                @if($dpr->status == 'Approved')

                    <span class="bg-green-200 text-green-800 px-3 py-1 rounded">
                        Approved
                    </span>

                @elseif($dpr->status == 'Rejected')

                    <span class="bg-red-200 text-red-800 px-3 py-1 rounded">
                        Rejected
                    </span>

                @else

                    <span class="bg-yellow-200 text-yellow-800 px-3 py-1 rounded">
                        Pending
                    </span>

                @endif

            </p>

        </div>

    </div>

</div>

<!-- Work Items -->

<div class="bg-white rounded shadow p-6 mb-6">

    <h2 class="text-2xl font-bold mb-6">
        Work Progress Details
    </h2>

    <table class="w-full border">

        <thead class="bg-gray-200">

            <tr>

                <th class="p-4 text-left border">
                    Activity
                </th>

                <th class="p-4 text-left border">
                    Contractor
                </th>

                <th class="p-4 text-left border">
                    Quantity
                </th>

                <th class="p-4 text-left border">
                    Remarks
                </th>

            </tr>

        </thead>

        <tbody>

            @foreach($dpr->workItems as $item)

            <tr class="border-t">

                <td class="p-4 border">
                    {{ $item->activity->activity_name }}
                </td>

                <td class="p-4 border">
                    {{ $item->contractor->contractor_name }}
                </td>

                <td class="p-4 border">
                    {{ $item->quantity_completed }}
                </td>

                <td class="p-4 border">
                    {{ $item->work_remarks }}
                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>

<!-- Labour Details -->

<div class="bg-white rounded shadow p-6 mb-6">

    <h2 class="text-2xl font-bold mb-6">
        Labour Details
    </h2>

    @if($dpr->labours->count() > 0)

        <table class="w-full border">

            <thead class="bg-gray-200">

                <tr>

                    <th class="p-4 border text-left">
                        Labour Type
                    </th>

                    <th class="p-4 border text-left">
                        Male
                    </th>

                    <th class="p-4 border text-left">
                        Female
                    </th>

                    <th class="p-4 border text-left">
                        Local
                    </th>

                    <th class="p-4 border text-left">
                        Non Local
                    </th>

                    <th class="p-4 border text-left">
                        Total
                    </th>

                </tr>

            </thead>

            <tbody>

                @foreach($dpr->labours as $labour)

                <tr>

                    <td class="p-4 border">

                        {{ $labour->labourType->labour_type_name }}

                    </td>

                    <td class="p-4 border">
                        {{ $labour->male_count }}
                    </td>

                    <td class="p-4 border">
                        {{ $labour->female_count }}
                    </td>

                    <td class="p-4 border">
                        {{ $labour->local_count }}
                    </td>

                    <td class="p-4 border">
                        {{ $labour->non_local_count }}
                    </td>

                    <td class="p-4 border">
                        {{ $labour->total_count }}
                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

    @else

        <p class="text-gray-500">
            No labour entries available.
        </p>

    @endif

</div>
<!-- Site Photos -->

<div class="bg-white rounded shadow p-6 mb-6">

    <h2 class="text-2xl font-bold mb-6">
        Site Progress Photos
    </h2>

    @if($dpr->photos->count() > 0)

        <div class="grid grid-cols-3 gap-6">

            @foreach($dpr->photos as $photo)

                <div>

                    <a href="{{ asset('storage/' . $photo->photo_path) }}"
                       target="_blank">

                        <img src="{{ asset('storage/' . $photo->photo_path) }}"
                             class="rounded shadow border h-64 w-full object-cover">

                    </a>

                </div>

            @endforeach

        </div>

    @else

        <p class="text-gray-500">
            No site photos uploaded.
        </p>

    @endif

</div>
<!-- Summary -->

<div class="grid grid-cols-2 gap-6">

    <div class="bg-white rounded shadow p-6">

        <h2 class="text-2xl font-bold mb-4">
            DPR Summary
        </h2>

        <p class="mb-3">

            <span class="font-bold">
                Total Work Items:
            </span>

            {{ $dpr->workItems->count() }}

        </p>

        <p>

            <span class="font-bold">
                Total Quantity Entries:
            </span>

            {{ $dpr->workItems->sum('quantity_completed') }}

        </p>

    </div>

    <div class="bg-white rounded shadow p-6">

        <h2 class="text-2xl font-bold mb-4">
            General Remarks
        </h2>

        <p class="text-gray-700">
            {{ $dpr->remarks }}
        </p>

    </div>
    <div class="bg-white rounded shadow p-6 mt-6">

    <h2 class="text-2xl font-bold mb-4">
        PMO Review Remarks
    </h2>

    @if($dpr->pmo_remarks)

        <p class="text-gray-700">
            {{ $dpr->pmo_remarks }}
        </p>

    @else

        <p class="text-gray-500">
            No PMO remarks available.
        </p>

    @endif

</div>

</div>

@endsection