<!DOCTYPE html>
<html>
<head>

    <title>DPR Report</title>

    <style>

        body {
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #000;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        h1, h2 {
            margin-bottom: 10px;
        }

    </style>

</head>

<body>

<h1>
    Daily Progress Report
</h1>

<p>
    <strong>Project:</strong>
    {{ $dpr->project->project_name }}
</p>

<p>
    <strong>Engineer:</strong>
    {{ $dpr->user->name }}
</p>

<p>
    <strong>Date:</strong>
    {{ $dpr->dpr_date }}
</p>

<p>
    <strong>Weather:</strong>
    {{ $dpr->weather }}
</p>

<p>
    <strong>Status:</strong>
    {{ $dpr->status }}
</p>

<h2>
    Work Progress Details
</h2>

<table>

    <thead>

        <tr>

            <th>Activity</th>

            <th>Contractor</th>

            <th>Quantity</th>

            <th>Remarks</th>

        </tr>

    </thead>

    <tbody>

        @foreach($dpr->workItems as $item)

        <tr>

            <td>
                {{ $item->activity->activity_name }}
            </td>

            <td>
                {{ $item->contractor->contractor_name }}
            </td>

            <td>
                {{ $item->quantity_completed }}
            </td>

            <td>
                {{ $item->work_remarks }}
            </td>

        </tr>

        @endforeach

    </tbody>

</table>
<h2>
    Labour Details
</h2>

@if($dpr->labours->count() > 0)

<table>

    <thead>

        <tr>

            <th>Labour Type</th>

            <th>Male</th>

            <th>Female</th>

            <th>Local</th>

            <th>Non Local</th>

            <th>Total</th>

        </tr>

    </thead>

    <tbody>

        @foreach($dpr->labours as $labour)

        <tr>

            <td>
                {{ $labour->labourType->labour_type_name }}
            </td>

            <td>
                {{ $labour->male_count }}
            </td>

            <td>
                {{ $labour->female_count }}
            </td>

            <td>
                {{ $labour->local_count }}
            </td>

            <td>
                {{ $labour->non_local_count }}
            </td>

            <td>
                {{ $labour->total_count }}
            </td>

        </tr>

        @endforeach

    </tbody>

</table>

@endif

<h2>
    Material Received
</h2>

@if($dpr->materialReceived->count() > 0)

<table>

    <thead>

        <tr>

            <th>Material</th>

            <th>Vendor</th>

            <th>Quantity</th>

            <th>Challan</th>

            <th>Bill</th>

        </tr>

    </thead>

    <tbody>

        @foreach($dpr->materialReceived as $received)

        <tr>

            <td>
                {{ $received->material->material_name }}
            </td>

            <td>
                {{ $received->vendor->vendor_name ?? '-' }}
            </td>

            <td>
                {{ $received->quantity_received }}
            </td>

            <td>
                {{ $received->challan_number }}
            </td>

            <td>
                {{ $received->bill_number }}
            </td>

        </tr>

        @endforeach

    </tbody>

</table>

@else

<p>
    No material receipts available.
</p>

@endif

<h2>
    Material Consumption
</h2>

@if($dpr->materials->count() > 0)

<table>

    <thead>

        <tr>

            <th>Material</th>

            <th>Quantity</th>

            <th>Unit</th>

        </tr>

    </thead>

    <tbody>

        @foreach($dpr->materials as $material)

        <tr>

            <td>
                {{ $material->material->material_name }}
            </td>

            <td>
                {{ $material->quantity_used }}
            </td>

            <td>
                {{ $material->material->unit }}
            </td>

        </tr>

        @endforeach

    </tbody>

</table>
@else

<p>
    No material consumption records available.
</p>

@endif


<h2>
    Material Required
</h2>

@if($dpr->materialRequired->count() > 0)

<table>

    <thead>

        <tr>

            <th>Material</th>

            <th>Quantity</th>

            <th>Required Date</th>

            <th>Priority</th>

            <th>Reason</th>

        </tr>

    </thead>

    <tbody>

        @foreach($dpr->materialRequired as $required)

        <tr>

            <td>
                {{ $required->material->material_name }}
            </td>

            <td>
                {{ $required->required_quantity }}
            </td>

            <td>
                {{ $required->required_date }}
            </td>

            <td>
                {{ $required->priority }}
            </td>

            <td>
                {{ $required->reason }}
            </td>

        </tr>

        @endforeach

    </tbody>

</table>
@else

<p>
    No material requirements recorded.
</p>

@endif


<h2>
    Machinery / Tools Used
</h2>

@if($dpr->machineryTools->count() > 0)

<table>

    <thead>

        <tr>

            <th>Machine</th>

            <th>Quantity</th>

            <th>Usage Hours</th>

            <th>Condition</th>

            <th>Remarks</th>

        </tr>

    </thead>

    <tbody>

        @foreach($dpr->machineryTools as $machine)

        <tr>

            <td>
                {{ $machine->machineryTool->machine_name }}
            </td>

            <td>
                {{ $machine->quantity }}
            </td>

            <td>
                {{ $machine->usage_hours }}
            </td>

            <td>
                {{ $machine->working_condition }}
            </td>

            <td>
                {{ $machine->remarks }}
            </td>

        </tr>

        @endforeach

    </tbody>

</table>
@else

<p>
    No material requirements recorded.
</p>

@endif

<h2>
    Issues / Delays
</h2>

@if($dpr->siteIssues->count() > 0)

<table>

    <thead>

        <tr>

            <th>Issue</th>

            <th>Activity</th>

            <th>Priority</th>

            <th>Status</th>

            <th>Responsible</th>

        </tr>

    </thead>

    <tbody>

        @foreach($dpr->siteIssues as $issue)

        <tr>

            <td>
                {{ $issue->issue_type }}
            </td>

            <td>
                {{ $issue->related_activity }}
            </td>

            <td>
                {{ $issue->priority }}
            </td>

            <td>
                {{ $issue->status }}
            </td>

            <td>
                {{ $issue->responsible_person }}
            </td>

        </tr>

        @endforeach

    </tbody>

</table>
@else

<p>
    No site issues reported.
</p>

@endif

<h2>
    Tomorrow Plan
</h2>

@if($dpr->tomorrowPlans->count() > 0)

<table>

    <thead>

        <tr>

            <th>Activity</th>

            <th>Planned Qty</th>

            <th>Unit</th>

            <th>Labour</th>

            <th>Materials</th>

            <th>Machinery</th>

        </tr>

    </thead>

    <tbody>

        @foreach($dpr->tomorrowPlans as $plan)

        <tr>

            <td>
                {{ $plan->activity->activity_name ?? '-' }}
            </td>

            <td>
                {{ $plan->planned_quantity }}
            </td>

            <td>
                {{ $plan->unit }}
            </td>

            <td>
                {{ $plan->planned_labour }}
            </td>

            <td>
                {{ $plan->materials_required }}
            </td>

            <td>
                {{ $plan->machinery_required }}
            </td>

        </tr>

        @endforeach

    </tbody>

</table>
@else

<p>
    No tomorrow plans recorded.
</p>

@endif

<h2>
    Site Progress Photos
</h2>

@if($dpr->photos->count() > 0)

    <table style="width: 100%; border: none;">

        <tr>

            @foreach($dpr->photos as $photo)

                <td style="border: none; padding: 10px;">

                    @php

                        $imagePath =
                            public_path(
                                'storage/' .
                                $photo->photo_path
                            );

                    @endphp

                    @if(file_exists($imagePath))

                        <img
                            src="{{ $imagePath }}"
                            width="220"
                            height="180"
                            style="object-fit: cover; border:1px solid #ccc;">

                    @endif

                </td>

            @endforeach

        </tr>

    </table>

@else

    <p>
        No site photos uploaded.
    </p>

@endif
<h2>
    General Remarks
</h2>

<p>
    {{ $dpr->remarks }}
</p>

</body>
</html>