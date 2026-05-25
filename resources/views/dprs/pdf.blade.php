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
<!--
<h2>
    Site Progress Photos
</h2>

@if($dpr->photos->count() > 0)

    <table style="border: none; margin-top: 20px;">

        <tr>

            @foreach($dpr->photos as $photo)

                <td style="border: none; padding: 10px;">

                    <img src="{{ storage_path('app/public/' . $photo->photo_path) }}"
                         width="220"
                         height="180">

                </td>

            @endforeach

        </tr>

    </table>

@else

    <p>
        No site photos uploaded.
    </p>

@endif -->
<h2>
    General Remarks
</h2>

<p>
    {{ $dpr->remarks }}
</p>

</body>
</html>