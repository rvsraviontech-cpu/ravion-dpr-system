<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>
        Labour Attendance Register - {{ $periodLabel }}
    </title>

    <style>
        @page {
            size: A4 landscape;
            margin: 7mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            line-height: 1.2;
            color: #111827;
        }

        h1 {
            margin: 0;
            font-size: 15px;
            color: #0f172a;
        }

        .subtitle {
            margin-top: 2px;
            font-size: 8px;
            color: #475569;
        }

        .meta {
            margin-top: 5px;
            font-size: 7px;
            color: #475569;
        }

        .overall-summary {
            margin-top: 6px;
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            font-size: 7px;
        }

        .overall-summary span {
            display: inline-block;
            margin-right: 14px;
        }

        .project-block {
            margin-top: 7px;
        }

        .project-heading {
            width: 100%;
            border-collapse: collapse;
        }

        .project-heading td {
            padding: 4px 6px;
            border: 1px solid #93c5fd;
            background: #eff6ff;
        }

        .project-name {
            font-size: 9px;
            font-weight: 700;
            color: #1e3a8a;
        }

        .project-code {
            margin-top: 1px;
            font-size: 6px;
            color: #2563eb;
        }

        .project-summary {
            text-align: right;
            font-size: 6.5px;
            white-space: nowrap;
        }

        .register {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .register th,
        .register td {
            border: 1px solid #d1d5db;
            padding: 3px 2px;
            vertical-align: middle;
        }

        .register th {
            background: #f3f4f6;
            font-size: 6px;
            font-weight: 700;
            text-align: center;
        }

        .labour-column {
            width: 21%;
            text-align: left;
            padding-left: 5px !important;
        }

        .day-column {
            text-align: center;
        }

        .summary-column {
            width: 4.5%;
            text-align: center;
        }

        .hours-column {
            width: 6%;
            text-align: right;
            padding-right: 4px !important;
        }

        .day-number {
            display: block;
            font-size: 6.5px;
            font-weight: 700;
        }

        .weekday {
            display: block;
            margin-top: 1px;
            font-size: 4.5px;
            font-weight: 400;
            color: #64748b;
        }

        .sunday {
            background: #fef2f2 !important;
            color: #dc2626;
        }

        .labour-name {
            font-size: 7px;
            font-weight: 700;
        }

        .designation {
            margin-top: 1px;
            font-size: 5.5px;
            color: #64748b;
        }

        .status {
            display: inline-block;
            min-width: 13px;
            padding: 1px 2px;
            border-radius: 2px;
            font-size: 6px;
            font-weight: 700;
            text-align: center;
        }

        .p {
            border: 1px solid #86efac;
            background: #dcfce7;
            color: #15803d;
        }

        .a {
            border: 1px solid #fca5a5;
            background: #fee2e2;
            color: #b91c1c;
        }

        .hd {
            border: 1px solid #fcd34d;
            background: #fef3c7;
            color: #b45309;
        }

        .l {
            border: 1px solid #93c5fd;
            background: #dbeafe;
            color: #1d4ed8;
        }

        .wo {
            border: 1px solid #d1d5db;
            background: #f3f4f6;
            color: #4b5563;
        }

        .h {
            border: 1px solid #c4b5fd;
            background: #ede9fe;
            color: #6d28d9;
        }

        .present-total {
            background: #f0fdf4;
            color: #15803d;
            font-weight: 700;
        }

        .absent-total {
            background: #fef2f2;
            color: #b91c1c;
            font-weight: 700;
        }

        .half-total {
            background: #fffbeb;
            color: #b45309;
            font-weight: 700;
        }

        .leave-total {
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 700;
        }

        .normal-total {
            background: #f8fafc;
            font-weight: 700;
        }

        .ot-total {
            background: #f5f3ff;
            color: #6d28d9;
            font-weight: 700;
        }

        .project-total td {
            border-top: 1.5px solid #64748b;
            background: #f1f5f9;
            font-weight: 700;
        }

        .grand-total {
            margin-top: 7px;
            padding: 5px 6px;
            border: 1px solid #64748b;
            background: #e2e8f0;
            font-size: 7px;
            font-weight: 700;
        }

        .legend {
            margin-top: 6px;
            padding-top: 4px;
            border-top: 1px solid #cbd5e1;
            font-size: 6px;
            color: #475569;
        }

        .generated {
            margin-top: 4px;
            text-align: right;
            font-size: 5.5px;
            color: #64748b;
        }

        .no-data {
            margin-top: 10px;
            padding: 12px;
            border: 1px solid #cbd5e1;
            text-align: center;
            color: #64748b;
        }
    </style>
</head>

<body>

    <h1>
        Ravion ERP - Labour Attendance Register
    </h1>

    <div class="subtitle">
        {{ $periodLabel }}
    </div>

    <div class="meta">
        Register Type:
        <strong>
            {{ ($filters['period_type'] ?? 'monthly') === 'weekly'
                ? 'Weekly'
                : 'Monthly' }}
        </strong>

        &nbsp;&nbsp; | &nbsp;&nbsp;

        Period:
        <strong>
            {{ $periodStart->format('d M Y') }}
            -
            {{ $periodEnd->format('d M Y') }}
        </strong>

        &nbsp;&nbsp; | &nbsp;&nbsp;

        Workflow:
        <strong>
            {{ ($filters['approved_only'] ?? true)
                ? 'Approved Attendance Only'
                : 'All Active Attendance' }}
        </strong>
    </div>

    <div class="overall-summary">
        <span>
            Total Labour:
            <strong>{{ $summary['total_labour'] }}</strong>
        </span>

        <span style="color:#15803d;">
            Present:
            <strong>{{ $summary['present'] }}</strong>
        </span>

        <span style="color:#b91c1c;">
            Absent:
            <strong>{{ $summary['absent'] }}</strong>
        </span>

        <span style="color:#b45309;">
            Half Day:
            <strong>{{ $summary['half_day'] }}</strong>
        </span>

        <span style="color:#1d4ed8;">
            Leave:
            <strong>{{ $summary['leave'] }}</strong>
        </span>

        <span>
            Normal:
            <strong>
                {{ number_format(
                    (float) $summary['normal_hours'],
                    2
                ) }}
            </strong>
        </span>

        <span style="color:#6d28d9;">
            OT:
            <strong>
                {{ number_format(
                    (float) $summary['ot_hours'],
                    2
                ) }}
            </strong>
        </span>
    </div>

    @forelse($projectGroups as $projectGroup)

        <div class="project-block">

            <table class="project-heading">
                <tr>
                    <td>
                        <div class="project-name">
                            {{ $projectGroup['project_name'] }}
                        </div>

                        @if($projectGroup['project_code'])
                            <div class="project-code">
                                {{ $projectGroup['project_code'] }}
                            </div>
                        @endif
                    </td>

                    <td class="project-summary">
                        Labour:
                        {{ $projectGroup['summary']['total_labour'] }}

                        &nbsp; | &nbsp;

                        P:
                        {{ $projectGroup['summary']['present'] }}

                        &nbsp; | &nbsp;

                        A:
                        {{ $projectGroup['summary']['absent'] }}

                        &nbsp; | &nbsp;

                        Normal:
                        {{ number_format(
                            (float) $projectGroup['summary']['normal_hours'],
                            2
                        ) }}

                        &nbsp; | &nbsp;

                        OT:
                        {{ number_format(
                            (float) $projectGroup['summary']['ot_hours'],
                            2
                        ) }}
                    </td>
                </tr>
            </table>

            <table class="register">
                <thead>
                    <tr>
                        <th class="labour-column">
                            Labour / Designation
                        </th>

                        @foreach($dateColumns as $dateColumn)
                            <th class="day-column {{ $dateColumn['is_sunday'] ? 'sunday' : '' }}">
                                <span class="day-number">
                                    {{ $dateColumn['day_number'] }}
                                </span>

                                <span class="weekday">
                                    {{ $dateColumn['weekday_short'] }}
                                </span>
                            </th>
                        @endforeach

                        <th class="summary-column present-total">
                            P
                        </th>

                        <th class="summary-column absent-total">
                            A
                        </th>

                        <th class="summary-column half-total">
                            HD
                        </th>

                        <th class="summary-column leave-total">
                            L
                        </th>

                        <th class="hours-column normal-total">
                            Normal
                        </th>

                        <th class="hours-column ot-total">
                            OT
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($projectGroup['rows'] as $row)

                        <tr>
                            <td class="labour-column">
                                <div class="labour-name">
                                    {{ $row['labour_name'] }}
                                </div>

                                <div class="designation">
                                    {{ $row['designation'] }}
                                </div>
                            </td>

                            @foreach($dateColumns as $dateColumn)
                                @php
                                    $dayEntry =
                                        $row['days'][
                                            $dateColumn['key']
                                        ] ?? null;

                                    $code =
                                        $dayEntry['code']
                                        ?? null;

                                    $statusClass = match($code) {
                                        'P' => 'p',
                                        'A' => 'a',
                                        'HD' => 'hd',
                                        'L' => 'l',
                                        'WO' => 'wo',
                                        'H' => 'h',
                                        default => '',
                                    };
                                @endphp

                                <td class="day-column">
                                    @if($dayEntry)
                                        <span class="status {{ $statusClass }}">
                                            {{ $code }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                            @endforeach

                            <td class="summary-column present-total">
                                {{ $row['totals']['present'] }}
                            </td>

                            <td class="summary-column absent-total">
                                {{ $row['totals']['absent'] }}
                            </td>

                            <td class="summary-column half-total">
                                {{ $row['totals']['half_day'] }}
                            </td>

                            <td class="summary-column leave-total">
                                {{ $row['totals']['leave'] }}
                            </td>

                            <td class="hours-column normal-total">
                                {{ number_format(
                                    (float) $row['totals']['normal_hours'],
                                    2
                                ) }}
                            </td>

                            <td class="hours-column ot-total">
                                {{ number_format(
                                    (float) $row['totals']['ot_hours'],
                                    2
                                ) }}
                            </td>
                        </tr>

                    @endforeach

                    <tr class="project-total">
                        <td class="labour-column">
                            Project Totals -
                            {{ $projectGroup['summary']['total_labour'] }}
                            Labour
                        </td>

                        @foreach($dateColumns as $dateColumn)
                            <td class="day-column"></td>
                        @endforeach

                        <td class="summary-column present-total">
                            {{ $projectGroup['summary']['present'] }}
                        </td>

                        <td class="summary-column absent-total">
                            {{ $projectGroup['summary']['absent'] }}
                        </td>

                        <td class="summary-column half-total">
                            {{ $projectGroup['summary']['half_day'] }}
                        </td>

                        <td class="summary-column leave-total">
                            {{ $projectGroup['summary']['leave'] }}
                        </td>

                        <td class="hours-column normal-total">
                            {{ number_format(
                                (float) $projectGroup['summary']['normal_hours'],
                                2
                            ) }}
                        </td>

                        <td class="hours-column ot-total">
                            {{ number_format(
                                (float) $projectGroup['summary']['ot_hours'],
                                2
                            ) }}
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

    @empty

        <div class="no-data">
            No labour attendance records were found for
            {{ $periodLabel }}.
        </div>

    @endforelse

    @if($projectGroups->isNotEmpty())
        <div class="grand-total">
            Grand Total
            &nbsp; | &nbsp;

            Labour:
            {{ $summary['total_labour'] }}

            &nbsp; | &nbsp;

            P:
            {{ $summary['present'] }}

            &nbsp; | &nbsp;

            A:
            {{ $summary['absent'] }}

            &nbsp; | &nbsp;

            HD:
            {{ $summary['half_day'] }}

            &nbsp; | &nbsp;

            L:
            {{ $summary['leave'] }}

            &nbsp; | &nbsp;

            Normal:
            {{ number_format(
                (float) $summary['normal_hours'],
                2
            ) }}

            &nbsp; | &nbsp;

            OT:
            {{ number_format(
                (float) $summary['ot_hours'],
                2
            ) }}
        </div>
    @endif

    <div class="legend">
        <strong>Codes:</strong>
        P = Present,
        A = Absent,
        HD = Half Day,
        L = Leave,
        WO = Weekly Off,
        H = Holiday.
        Sunday is highlighted, but actual Sunday attendance is shown.
    </div>

    <div class="generated">
        Generated from Ravion ERP on
        {{ now()->format('d M Y h:i A') }}
    </div>

</body>
</html>
