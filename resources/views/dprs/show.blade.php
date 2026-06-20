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
            <th class="p-3 text-left border">Division</th>
            <th class="p-3 text-left border">Activity</th>
            <th class="p-3 text-left border">Block</th>
            <th class="p-3 text-left border">Floor</th>
            <th class="p-3 text-left border">Unit</th>
            <th class="p-3 text-left border">Room</th>
            <th class="p-3 text-left border">Sub-space</th>
            <th class="p-3 text-left border">Qty</th>
            <th class="p-3 text-left border">Remarks</th>
        </tr>
    </thead>

    <tbody>
        @foreach($dpr->workItems as $item)
            <tr class="border-t">
                <td class="p-3 border">
                    {{ $item->activityMapping?->division?->name ?? '-' }}
                </td>

                <td class="p-3 border">
                    {{ $item->activityMapping?->activity_name ?? $item->activity?->activity_name ?? '-' }}
                </td>

                <td class="p-3 border">
                    {{ $item->block?->name ?? '-' }}
                </td>

                <td class="p-3 border">
                    {{ $item->floor?->name ?? '-' }}
                </td>

                <td class="p-3 border">
                    {{ $item->unit?->name ?? '-' }}
                </td>

                <td class="p-3 border">
                    {{ $item->room?->name ?? '-' }}
                </td>

                <td class="p-3 border">
                    {{ $item->subspace?->name ?? '-' }}
                </td>

                <td class="p-3 border">
                    {{ $item->quantity_completed }}
                    {{ $item->activityMapping?->unit ?? '' }}
                </td>

                <td class="p-3 border">
                    {{ $item->remarks ?? '-' }}
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

<!-- Material Consumption -->

<div class="bg-white rounded shadow p-6 mb-6">

    <h2 class="text-2xl font-bold mb-6">
        Material Consumption
    </h2>

    @if($dpr->materials->count() > 0)

        <table class="w-full border">

            <thead class="bg-gray-200">

                <tr>

                    <th class="p-4 border text-left">
                        Material
                    </th>

                    <th class="p-4 border text-left">
                        Quantity
                    </th>

                    <th class="p-4 border text-left">
                        Unit
                    </th>

                </tr>

            </thead>

            <tbody>

                @foreach($dpr->materials as $material)

                <tr>

                    <td class="p-4 border">

                        {{ $material->material->material_name }}

                    </td>

                    <td class="p-4 border">

                        {{ $material->quantity_used }}

                    </td>

                    <td class="p-4 border">

                        {{ $material->material->unit }}

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

    @else

        <p class="text-gray-500">
            No material consumption entries available.
        </p>

    @endif

</div>

<!-- Material Received -->

<div class="bg-white rounded shadow p-6 mb-6">

    <h2 class="text-2xl font-bold mb-6">
        Material Received
    </h2>

    @if($dpr->materialReceived->count() > 0)

        <table class="w-full border">

            <thead class="bg-gray-200">

                <tr>

                    <th class="p-4 border text-left">
                        Material
                    </th>

                    <th class="p-4 border text-left">
                        Vendor
                    </th>

                    <th class="p-4 border text-left">
                        Quantity
                    </th>

                    <th class="p-4 border text-left">
                        Challan
                    </th>

                    <th class="p-4 border text-left">
                        Bill
                    </th>

                </tr>

            </thead>

            <tbody>

                @foreach($dpr->materialReceived as $received)

                <tr>

                    <td class="p-4 border">

                        {{ $received->material->material_name }}

                    </td>

                    <td class="p-4 border">

                        {{ $received->vendor->vendor_name ?? '-' }}

                    </td>

                    <td class="p-4 border">

                        {{ $received->quantity_received }}

                    </td>

                    <td class="p-4 border">

                        {{ $received->challan_number }}

                    </td>

                    <td class="p-4 border">

                        {{ $received->bill_number }}

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

    @else

        <p class="text-gray-500">
            No material receipts available.
        </p>

    @endif

</div>

<!-- Material Required -->

<div class="bg-white rounded shadow p-6 mb-6">

    <h2 class="text-2xl font-bold mb-6">
        Material Required
    </h2>

    @if($dpr->materialRequired->count() > 0)

        <table class="w-full border">

            <thead class="bg-gray-200">

                <tr>

                    <th class="p-4 border text-left">
                        Material
                    </th>

                    <th class="p-4 border text-left">
                        Quantity
                    </th>

                    <th class="p-4 border text-left">
                        Required Date
                    </th>

                    <th class="p-4 border text-left">
                        Priority
                    </th>

                    <th class="p-4 border text-left">
                        Reason
                    </th>

                </tr>

            </thead>

            <tbody>

                @foreach($dpr->materialRequired as $required)

                <tr>

                    <td class="p-4 border">

                        {{ $required->material->material_name }}

                    </td>

                    <td class="p-4 border">

                        {{ $required->required_quantity }}

                    </td>

                    <td class="p-4 border">

                        {{ $required->required_date }}

                    </td>

                    <td class="p-4 border">

                        {{ $required->priority }}

                    </td>

                    <td class="p-4 border">

                        {{ $required->reason }}

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

    @else

        <p class="text-gray-500">
            No material requirements available.
        </p>

    @endif

</div>

<!-- Machinery / Tools -->

<div class="bg-white rounded shadow p-6 mb-6">

    <h2 class="text-2xl font-bold mb-6">
        Machinery / Tools Used
    </h2>

    @if($dpr->machineryTools->count() > 0)

        <table class="w-full border">

            <thead class="bg-gray-200">

                <tr>

                    <th class="p-4 border text-left">
                        Machine
                    </th>

                    <th class="p-4 border text-left">
                        Quantity
                    </th>

                    <th class="p-4 border text-left">
                        Usage Hours
                    </th>

                    <th class="p-4 border text-left">
                        Condition
                    </th>

                    <th class="p-4 border text-left">
                        Remarks
                    </th>

                </tr>

            </thead>

            <tbody>

                @foreach($dpr->machineryTools as $machine)

                <tr>

                    <td class="p-4 border">

                        {{ $machine->machineryTool->machine_name }}

                    </td>

                    <td class="p-4 border">

                        {{ $machine->quantity }}

                    </td>

                    <td class="p-4 border">

                        {{ $machine->usage_hours }}

                    </td>

                    <td class="p-4 border">

                        {{ $machine->working_condition }}

                    </td>

                    <td class="p-4 border">

                        {{ $machine->remarks }}

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

    @else

        <p class="text-gray-500">
            No machinery usage available.
        </p>

    @endif

    <!-- Issues / Delays -->

<div class="bg-white rounded shadow p-6 mb-6">

    <h2 class="text-2xl font-bold mb-6">
        Issues / Delays
    </h2>

    @if($dpr->siteIssues->count() > 0)

        <table class="w-full border">

            <thead class="bg-gray-200">

                <tr>

                    <th class="p-4 border text-left">
                        Issue
                    </th>

                    <th class="p-4 border text-left">
                        Activity
                    </th>

                    <th class="p-4 border text-left">
                        Priority
                    </th>

                    <th class="p-4 border text-left">
                        Status
                    </th>

                    <th class="p-4 border text-left">
                        Responsible
                    </th>

                </tr>

            </thead>

            <tbody>

                @foreach($dpr->siteIssues as $issue)

                <tr>

                    <td class="p-4 border">

                        {{ $issue->issue_type }}

                    </td>

                    <td class="p-4 border">

                        {{ $issue->related_activity }}

                    </td>

                    <td class="p-4 border">

                        {{ $issue->priority }}

                    </td>

                    <td class="p-4 border">

                        {{ $issue->status }}

                    </td>

                    <td class="p-4 border">

                        {{ $issue->responsible_person }}

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

    @else

        <p class="text-gray-500">
            No site issues reported.
        </p>

    @endif

</div>

</div>

<!-- Tomorrow Plan -->

<div class="bg-white rounded shadow p-6 mb-6">

    <h2 class="text-2xl font-bold mb-6">
        Tomorrow Plan
    </h2>

    @if($dpr->tomorrowPlans->count() > 0)

        <table class="w-full border">

            <thead class="bg-gray-200">

                <tr>

                    <th class="p-4 border text-left">
                        Activity
                    </th>

                    <th class="p-4 border text-left">
                        Planned Qty
                    </th>

                    <th class="p-4 border text-left">
                        Unit
                    </th>

                    <th class="p-4 border text-left">
                        Labour
                    </th>

                    <th class="p-4 border text-left">
                        Materials
                    </th>

                    <th class="p-4 border text-left">
                        Machinery
                    </th>

                </tr>

            </thead>

            <tbody>

                @foreach($dpr->tomorrowPlans as $plan)

                <tr>

                    <td class="p-4 border">

                        {{ $plan->activity->activity_name ?? '-' }}

                    </td>

                    <td class="p-4 border">

                        {{ $plan->planned_quantity }}

                    </td>

                    <td class="p-4 border">

                        {{ $plan->unit }}

                    </td>

                    <td class="p-4 border">

                        {{ $plan->planned_labour }}

                    </td>

                    <td class="p-4 border">

                        {{ $plan->materials_required }}

                    </td>

                    <td class="p-4 border">

                        {{ $plan->machinery_required }}

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

    @else

        <p class="text-gray-500">
            No tomorrow plans available.
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
{{-- PMO REVIEW PANEL --}}
@if(in_array(auth()->user()->role->name, ['PMO', 'Admin', 'DGM']) && $dpr->status === 'Pending')

<div class="bg-white rounded shadow p-6 mb-6 border-l-4 border-blue-600">

    <h2 class="text-2xl font-bold mb-4">
        PMO Review Panel
    </h2>

    <div class="mb-4">
        <span class="font-semibold">Current Status:</span>

        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded">
            Pending Review
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <form method="POST"
              action="{{ route('dprs.approve', $dpr->id) }}">

            @csrf

            <label class="block font-semibold mb-2">
                Approval Remarks
            </label>

            <textarea name="pmo_remarks"
                      rows="4"
                      class="border rounded w-full p-3 mb-4"
                      placeholder="Enter approval remarks..."></textarea>

            <button type="submit"
                    onclick="return confirm('Approve this DPR?')"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded">
                Approve DPR
            </button>

        </form>

        <form method="POST"
              action="{{ route('dprs.reject', $dpr->id) }}">

            @csrf

            <label class="block font-semibold mb-2">
                Rejection Remarks <span class="text-red-600">*</span>
            </label>

            <textarea name="pmo_remarks"
                      rows="4"
                      class="border rounded w-full p-3 mb-4"
                      placeholder="Enter reason for rejection..."
                      required></textarea>

            <button type="submit"
                    onclick="return confirm('Reject this DPR?')"
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded">
                Reject DPR
            </button>

        </form>

    </div>

</div>

@endif


{{-- PMO REMARKS DISPLAY --}}
@if($dpr->pmo_remarks)

<div class="bg-white rounded shadow p-6 mb-6 border-l-4
    {{ $dpr->status === 'Approved' ? 'border-green-600' : 'border-red-600' }}">

    <h2 class="text-2xl font-bold mb-4">
        PMO Remarks
    </h2>

    <div class="mb-3">
        <span class="font-semibold">Status:</span>

        @if($dpr->status === 'Approved')
            <span class="bg-green-100 text-green-800 px-3 py-1 rounded">
                Approved
            </span>
        @elseif($dpr->status === 'Rejected')
            <span class="bg-red-100 text-red-800 px-3 py-1 rounded">
                Rejected
            </span>
        @else
            <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded">
                {{ $dpr->status }}
            </span>
        @endif
    </div>

    <p class="text-gray-700 whitespace-pre-line">
        {{ $dpr->pmo_remarks }}
    </p>

</div>

@endif

@endsection