<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <title>
        DPR - {{ $dpr->project?->project_name ?? 'Project' }} - {{ $dpr->dpr_date?->format('d-m-Y') }}
    </title>

    <style>
        @page {
            margin: 24px 26px 30px 26px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5px;
            line-height: 1.4;
            color: #1f2937;
        }

        h1, h2, h3, p {
            margin: 0;
        }

        .header {
            border: 1px solid #d1d5db;
            padding: 14px;
            margin-bottom: 12px;
        }

        .title {
            font-size: 19px;
            font-weight: bold;
            color: #111827;
        }

        .subtitle {
            margin-top: 3px;
            color: #6b7280;
            font-size: 9px;
        }

        .status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 8px;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-approved {
            background: #dcfce7;
            color: #166534;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .section {
            border: 1px solid #d1d5db;
            margin-bottom: 12px;
            page-break-inside: auto;
        }

        .section-title {
            background: #f3f4f6;
            border-bottom: 1px solid #d1d5db;
            padding: 8px 10px;
            font-size: 12px;
            font-weight: bold;
            color: #111827;
        }

        .section-body {
            padding: 10px;
        }

        .meta-table,
        .data-table,
        .summary-table,
        .photo-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            width: 25%;
            vertical-align: top;
            padding: 5px 8px;
            border: 1px solid #e5e7eb;
        }

        .label {
            display: block;
            font-size: 7.5px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .value {
            font-size: 9.5px;
            color: #111827;
            font-weight: bold;
        }

        .summary-table td {
            width: 12.5%;
            text-align: center;
            border: 1px solid #e5e7eb;
            padding: 7px 3px;
        }

        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
        }

        .summary-label {
            margin-top: 2px;
            font-size: 7px;
            text-transform: uppercase;
            color: #6b7280;
        }

        .data-table {
            margin-top: 4px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #d1d5db;
            padding: 5px 6px;
            vertical-align: top;
        }

        .data-table th {
            background: #f3f4f6;
            font-size: 7.5px;
            text-transform: uppercase;
            color: #4b5563;
            text-align: left;
        }

        .right {
            text-align: right !important;
        }

        .center {
            text-align: center !important;
        }

        .muted {
            color: #6b7280;
        }

        .small {
            font-size: 8px;
        }

        .work-card,
        .issue-card,
        .receipt-card {
            border: 1px solid #d1d5db;
            margin-bottom: 8px;
            padding: 8px;
            page-break-inside: avoid;
        }

        .work-title,
        .issue-title,
        .receipt-title {
            font-size: 10.5px;
            font-weight: bold;
            color: #111827;
        }

        .inline-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .inline-table td {
            width: 25%;
            border: 1px solid #e5e7eb;
            padding: 5px;
            vertical-align: top;
        }

        .note-box {
            margin-top: 6px;
            border: 1px solid #e5e7eb;
            background: #fafafa;
            padding: 6px;
        }

        .photo-table {
            margin-top: 7px;
        }

        .photo-table td {
            width: 25%;
            padding: 4px;
            vertical-align: top;
            border: none;
        }

        .photo {
            width: 100%;
            height: 105px;
            object-fit: cover;
            border: 1px solid #d1d5db;
        }

        .photo-caption {
            margin-top: 2px;
            font-size: 7px;
            color: #6b7280;
        }

        .priority {
            display: inline-block;
            padding: 2px 6px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-weight: bold;
            font-size: 7.5px;
        }

        .page-break {
            page-break-before: always;
        }

        .footer-note {
            margin-top: 10px;
            font-size: 7.5px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>

<body>

@php
    $attendanceDetails = $dpr->labourAttendances
        ->flatMap(fn ($attendance) => $attendance->details ?? collect());

    $uniqueLabourCount = $attendanceDetails
        ->pluck('labour_id')
        ->filter()
        ->unique()
        ->count();

    $presentCount = $attendanceDetails
    ->filter(function ($detail) {
        $code = strtoupper(trim((string) (
            $detail->attendanceStatus?->code ?? ''
        )));

        $name = strtoupper(trim((string) (
            $detail->attendanceStatus?->name ?? ''
        )));

        return in_array($code, ['P', 'PRESENT'], true)
            || $name === 'PRESENT';
    })
    ->count();

$absentCount = $attendanceDetails
    ->filter(function ($detail) {
        $code = strtoupper(trim((string) (
            $detail->attendanceStatus?->code ?? ''
        )));

        $name = strtoupper(trim((string) (
            $detail->attendanceStatus?->name ?? ''
        )));

        return in_array($code, ['A', 'ABSENT'], true)
            || $name === 'ABSENT';
    })
    ->count();

$halfDayCount = $attendanceDetails
    ->filter(function ($detail) {
        $code = strtoupper(trim((string) (
            $detail->attendanceStatus?->code ?? ''
        )));

        $name = strtoupper(trim((string) (
            $detail->attendanceStatus?->name ?? ''
        )));

        return in_array(
            $code,
            ['HD', 'HALF_DAY', 'HALFDAY'],
            true
        ) || str_contains($name, 'HALF');
    })
    ->count();

    $normalHours = round(
        (float) $attendanceDetails->sum('normal_hours'),
        2
    );

    $otHours = round(
        (float) $attendanceDetails->sum('ot_hours'),
        2
    );

    $formatQty = function ($value) {
        if ($value === null || $value === '') {
            return '0';
        }

        return rtrim(
            rtrim(
                number_format(
                    (float) $value,
                    3,
                    '.',
                    ''
                ),
                '0'
            ),
            '.'
        );
    };

    $photoPath = function ($relativePath) {
        if (!$relativePath) {
            return null;
        }

        $path = public_path(
            'storage/' . ltrim($relativePath, '/')
        );

        return file_exists($path)
            ? $path
            : null;
    };

    $statusClass = match($dpr->status) {
        'Approved' => 'status-approved',
        'Rejected' => 'status-rejected',
        default => 'status-pending',
    };
@endphp

{{-- HEADER --}}
<div class="header">

    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="vertical-align:top;">
                <div class="title">
                    Daily Progress Report
                </div>

                <div class="subtitle">
                    Ravion ERP - Construction Daily Execution Report
                </div>
            </td>

            <td style="text-align:right; vertical-align:top;">
                <span class="status {{ $statusClass }}">
                    {{ $dpr->status }}
                </span>

                <div class="subtitle" style="margin-top:5px;">
                    DPR #{{ $dpr->id }}
                </div>
            </td>
        </tr>
    </table>

    <table class="meta-table" style="margin-top:10px;">
        <tr>
            <td>
                <span class="label">Project</span>
                <span class="value">
                    {{ $dpr->project?->project_name ?? '-' }}
                </span>
            </td>

            <td>
                <span class="label">Engineer</span>
                <span class="value">
                    {{ $dpr->user?->name ?? '-' }}
                </span>
            </td>

            <td>
                <span class="label">DPR Date</span>
                <span class="value">
                    {{ $dpr->dpr_date?->format('d/m/Y') ?? '-' }}
                </span>
            </td>

            <td>
                <span class="label">Weather</span>
                <span class="value">
                    {{ $dpr->weather ?: '-' }}
                </span>
            </td>
        </tr>
    </table>
</div>

{{-- EXECUTION SUMMARY --}}
<div class="section">

    <div class="section-title">
        Daily Execution Summary
    </div>

    <div class="section-body">

        <table class="summary-table">
            <tr>
                <td>
                    <div class="summary-value">{{ $uniqueLabourCount }}</div>
                    <div class="summary-label">Labour</div>
                </td>

                <td>
                    <div class="summary-value">{{ $dpr->workDoneItems->count() }}</div>
                    <div class="summary-label">Work Done</div>
                </td>

                <td>
                    <div class="summary-value">{{ $dpr->materialReceipts->count() }}</div>
                    <div class="summary-label">Received</div>
                </td>

                <td>
                    <div class="summary-value">{{ $dpr->materialConsumptions->count() }}</div>
                    <div class="summary-label">Consumed</div>
                </td>

                <td>
                    <div class="summary-value">{{ $dpr->materialRequirements->count() }}</div>
                    <div class="summary-label">Required</div>
                </td>

                <td>
                    <div class="summary-value">{{ $dpr->siteIssues->count() }}</div>
                    <div class="summary-label">Issues</div>
                </td>

                <td>
                    <div class="summary-value">{{ $dpr->machineryTools->count() }}</div>
                    <div class="summary-label">Machinery</div>
                </td>

                <td>
                    <div class="summary-value">{{ $dpr->photos->count() }}</div>
                    <div class="summary-label">DPR Photos</div>
                </td>
            </tr>
        </table>
    </div>
</div>

{{-- LABOUR ATTENDANCE --}}
<div class="section">

    <div class="section-title">
        Labour Attendance
    </div>

    <div class="section-body">

        @if($dpr->labourAttendances->isNotEmpty())

            <table class="summary-table">
                <tr>
                    <td>
                        <div class="summary-value">{{ $uniqueLabourCount }}</div>
                        <div class="summary-label">Total</div>
                    </td>

                    <td>
                        <div class="summary-value">{{ $presentCount }}</div>
                        <div class="summary-label">Present</div>
                    </td>

                    <td>
                        <div class="summary-value">{{ $absentCount }}</div>
                        <div class="summary-label">Absent</div>
                    </td>

                    <td>
                        <div class="summary-value">{{ $halfDayCount }}</div>
                        <div class="summary-label">Half Day</div>
                    </td>

                    <td>
                        <div class="summary-value">{{ $normalHours }}</div>
                        <div class="summary-label">Normal Hrs</div>
                    </td>

                    <td>
                        <div class="summary-value">{{ $otHours }}</div>
                        <div class="summary-label">OT Hrs</div>
                    </td>

                    <td colspan="2">
                        <div class="summary-value">{{ $dpr->labourAttendances->count() }}</div>
                        <div class="summary-label">Attendance Sheets</div>
                    </td>
                </tr>
            </table>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Labour</th>
                        <th>Designation</th>
                        <th>Shift</th>
                        <th>Status</th>
                        <th class="right">Normal Hrs</th>
                        <th class="right">OT Hrs</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($dpr->labourAttendances as $attendance)
                        @foreach($attendance->details as $detail)
                            <tr>
                                <td>
                                    {{ $detail->labour?->full_name ?? '-' }}
                                </td>

                                <td>
                                    {{ $detail->designationRole?->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $attendance->shift?->shift_name ?? $attendance->shift?->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $detail->attendanceStatus?->name ?? $detail->attendanceStatus?->code ?? '-' }}
                                </td>

                                <td class="right">
                                    {{ $detail->normal_hours ?? 0 }}
                                </td>

                                <td class="right">
                                    {{ $detail->ot_hours ?? 0 }}
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>

        @else
            <p class="muted">No linked Labour Attendance.</p>
        @endif
    </div>
</div>

{{-- WORK DONE --}}
<div class="section">

    <div class="section-title">
        Work Done
    </div>

    <div class="section-body">

        @forelse($dpr->workDoneItems as $index => $item)

            <div class="work-card">

                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td>
                            <div class="small muted">
                                Work {{ $index + 1 }}
                            </div>

                            <div class="work-title">
                                {{ $item->activity_name ?? $item->activity?->activity_name ?? '-' }}
                            </div>

                            @if($item->activityMapping?->division?->name)
                                <div class="small muted">
                                    {{ $item->activityMapping->division->name }}
                                </div>
                            @endif
                        </td>

                        <td style="text-align:right; vertical-align:top; font-weight:bold;">
                            {{ $formatQty($item->quantity_completed) }}
                            {{ $item->unit ?? $item->activityMapping?->unit ?? $item->activity?->unit }}
                        </td>
                    </tr>
                </table>

                <table class="inline-table">
                    <tr>
                        <td>
                            <span class="label">Location</span>
                            {{ $item->location_path ?: '-' }}
                        </td>

                        <td>
                            <span class="label">Contractor</span>
                            {{ $item->contractor?->contractor_name ?? '-' }}
                        </td>

                        <td>
                            <span class="label">Status</span>
                            {{ $item->execution_status ?? '-' }}
                        </td>

                        <td>
                            <span class="label">Progress</span>
                            {{ $item->progress_percentage ?? 0 }}%
                        </td>
                    </tr>
                </table>

                @if($item->remarks)
                    <div class="note-box">
                        <span class="label">Remarks</span>
                        {{ $item->remarks }}
                    </div>
                @endif

                @if($item->photos->isNotEmpty())
                    <table class="photo-table">
                        @foreach($item->photos->chunk(4) as $row)
                            <tr>
                                @foreach($row as $photo)
                                    @php
                                        $path = $photoPath($photo->file_path);
                                    @endphp

                                    <td>
                                        @if($path)
                                            <img src="{{ $path }}"
                                                 class="photo">

                                            <div class="photo-caption">
                                                {{ $photo->caption ?: $photo->photo_type }}
                                            </div>
                                        @endif
                                    </td>
                                @endforeach

                                @for($i = $row->count(); $i < 4; $i++)
                                    <td></td>
                                @endfor
                            </tr>
                        @endforeach
                    </table>
                @endif
            </div>

        @empty
            <p class="muted">No linked Work Done records.</p>
        @endforelse
    </div>
</div>

{{-- MATERIAL RECEIVED --}}
<div class="section">

    <div class="section-title">
        Material Received
    </div>

    <div class="section-body">

        @forelse($dpr->materialReceipts as $receipt)

            <div class="receipt-card">

                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td>
                            <div class="receipt-title">
                                Receipt #{{ $receipt->id }}
                            </div>

                            <div class="small muted">
                                Vendor:
                                {{ $receipt->vendor?->vendor_name ?? $receipt->vendor_name ?? '-' }}
                            </div>
                        </td>

                        <td style="text-align:right; vertical-align:top;">
                            {{ $receipt->status ?? 'Recorded' }}
                        </td>
                    </tr>
                </table>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Brand</th>
                            <th>Specification</th>
                            <th>Grade</th>
                            <th class="right">Quantity</th>
                            <th>Unit</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($receipt->items as $item)
                            <tr>
                                <td>
                                    {{ $item->materialType?->material_type_name ?? $item->display_name ?? 'Material' }}
                                </td>

                                <td>
                                    {{ $item->brand?->brand_name ?? '-' }}
                                </td>

                                <td>
                                    {{ $item->specification?->specification_name ?? '-' }}
                                </td>

                                <td>
                                    {{ $item->grade?->grade_name ?? '-' }}
                                </td>

                                <td class="right">
                                    {{ $formatQty($item->quantity_received) }}
                                </td>

                                <td>
                                    {{ $item->unit?->unit_name ?? $item->unit?->name ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="center muted">
                                    No item rows available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($receipt->photos->isNotEmpty())
                    <table class="photo-table">
                        @foreach($receipt->photos->chunk(4) as $row)
                            <tr>
                                @foreach($row as $photo)
                                    @php
                                        $path = $photoPath($photo->file_path);
                                    @endphp

                                    <td>
                                        @if($path)
                                            <img src="{{ $path }}"
                                                 class="photo">

                                            <div class="photo-caption">
                                                {{ $photo->caption ?: $photo->photo_type }}
                                            </div>
                                        @endif
                                    </td>
                                @endforeach

                                @for($i = $row->count(); $i < 4; $i++)
                                    <td></td>
                                @endfor
                            </tr>
                        @endforeach
                    </table>
                @endif
            </div>

        @empty
            <p class="muted">No linked Material Received records.</p>
        @endforelse
    </div>
</div>

{{-- MATERIAL CONSUMED --}}
<div class="section">

    <div class="section-title">
        Material Consumed
    </div>

    <div class="section-body">

        @if($dpr->materialConsumptions->isNotEmpty())

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Record</th>
                        <th>Material</th>
                        <th class="right">Consumed</th>
                        <th class="right">Wastage</th>
                        <th>Unit</th>
                        <th>Remarks</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($dpr->materialConsumptions as $consumption)
                        @forelse($consumption->items as $item)
                            <tr>
                                <td>#{{ $consumption->id }}</td>

                                <td>
                                    {{ $item->display_name ?: ($item->materialType?->material_type_name ?? 'Material') }}
                                </td>

                                <td class="right">
                                    {{ $formatQty($item->quantity_consumed) }}
                                </td>

                                <td class="right">
                                    {{ $formatQty($item->wastage_quantity) }}
                                </td>

                                <td>
                                    {{ $item->unit?->unit_name ?? $item->unit?->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $item->remarks ?: ($consumption->remarks ?: '-') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td>#{{ $consumption->id }}</td>
                                <td>Material</td>
                                <td class="right">{{ $formatQty($consumption->quantity_consumed) }}</td>
                                <td class="right">{{ $formatQty($consumption->wastage_quantity) }}</td>
                                <td>{{ $consumption->unit ?? '-' }}</td>
                                <td>{{ $consumption->remarks ?? '-' }}</td>
                            </tr>
                        @endforelse
                    @endforeach
                </tbody>
            </table>

        @else
            <p class="muted">No linked Material Consumed records.</p>
        @endif
    </div>
</div>

{{-- MATERIAL REQUIRED --}}
<div class="section">

    <div class="section-title">
        Material Required
    </div>

    <div class="section-body">

        @if($dpr->materialRequirements->isNotEmpty())

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Requirement</th>
                        <th>Material</th>
                        <th class="right">Required Qty</th>
                        <th>Unit</th>
                        <th>Required Date</th>
                        <th>Priority</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($dpr->materialRequirements as $requirement)
                        @forelse($requirement->items as $item)
                            <tr>
                                <td>#{{ $requirement->id }}</td>

                                <td>
                                    {{ $item->display_name ?: ($item->materialType?->material_type_name ?? 'Material') }}
                                </td>

                                <td class="right">
                                    {{ $formatQty($item->required_quantity) }}
                                </td>

                                <td>
                                    {{ $item->unit?->unit_name ?? $item->unit?->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $requirement->required_date?->format('d/m/Y') ?? '-' }}
                                </td>

                                <td>
                                    {{ $requirement->priority ?? '-' }}
                                </td>

                                <td>
                                    {{ $requirement->status ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td>#{{ $requirement->id }}</td>
                                <td>{{ $requirement->material?->material_name ?? 'Material' }}</td>
                                <td class="right">{{ $formatQty($requirement->required_quantity) }}</td>
                                <td>{{ $requirement->unit ?? '-' }}</td>
                                <td>{{ $requirement->required_date?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $requirement->priority ?? '-' }}</td>
                                <td>{{ $requirement->status ?? '-' }}</td>
                            </tr>
                        @endforelse
                    @endforeach
                </tbody>
            </table>

        @else
            <p class="muted">No linked Material Required records.</p>
        @endif
    </div>
</div>

{{-- SITE ISSUES --}}
<div class="section">

    <div class="section-title">
        Site Issues
    </div>

    <div class="section-body">

        @forelse($dpr->siteIssues as $issue)

            <div class="issue-card">

                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td>
                            <div class="issue-title">
                                {{ $issue->title }}
                            </div>

                            <div class="small muted">
                                {{ $issue->issue_type }}
                            </div>
                        </td>

                        <td style="text-align:right; vertical-align:top;">
                            <span class="priority">
                                {{ $issue->priority }}
                            </span>

                            <span class="priority">
                                {{ $issue->status }}
                            </span>
                        </td>
                    </tr>
                </table>

                <table class="inline-table">
                    <tr>
                        <td>
                            <span class="label">Location</span>
                            {{ $issue->location_path ?: '-' }}
                        </td>

                        <td>
                            <span class="label">Activity</span>
                            {{ $issue->activity?->activity_name ?? '-' }}
                        </td>

                        <td>
                            <span class="label">Responsible</span>
                            {{ $issue->responsible_person ?: '-' }}
                        </td>

                        <td>
                            <span class="label">Target Closure</span>
                            {{ $issue->target_closure_date?->format('d/m/Y') ?? '-' }}
                        </td>
                    </tr>
                </table>

                @if($issue->description)
                    <div class="note-box">
                        <span class="label">Description</span>
                        {{ $issue->description }}
                    </div>
                @endif

                @if($issue->resolution)
                    <div class="note-box">
                        <span class="label">Resolution</span>
                        {{ $issue->resolution }}
                    </div>
                @endif

                @if($issue->photos->isNotEmpty())
                    <table class="photo-table">
                        @foreach($issue->photos->chunk(4) as $row)
                            <tr>
                                @foreach($row as $photo)
                                    @php
                                        $path = $photoPath($photo->file_path);
                                    @endphp

                                    <td>
                                        @if($path)
                                            <img src="{{ $path }}"
                                                 class="photo">

                                            <div class="photo-caption">
                                                {{ $photo->caption ?: $photo->photo_type }}
                                            </div>
                                        @endif
                                    </td>
                                @endforeach

                                @for($i = $row->count(); $i < 4; $i++)
                                    <td></td>
                                @endfor
                            </tr>
                        @endforeach
                    </table>
                @endif
            </div>

        @empty
            <p class="muted">No linked Site Issues.</p>
        @endforelse
    </div>
</div>

{{-- MACHINERY --}}
<div class="section">

    <div class="section-title">
        Machinery / Equipment Used
    </div>

    <div class="section-body">

        @if($dpr->machineryTools->isNotEmpty())

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Machine / Equipment</th>
                        <th class="right">Qty</th>
                        <th class="right">Usage Hrs</th>
                        <th>Condition</th>
                        <th>Remarks</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($dpr->machineryTools as $machine)
                        <tr>
                            <td>
                                {{ $machine->machineryTool?->machine_name ?? '-' }}
                            </td>

                            <td class="right">
                                {{ $machine->quantity }}
                            </td>

                            <td class="right">
                                {{ $machine->usage_hours }}
                            </td>

                            <td>
                                {{ $machine->working_condition }}
                            </td>

                            <td>
                                {{ $machine->remarks ?: '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        @else
            <p class="muted">No Machinery / Equipment recorded.</p>
        @endif
    </div>
</div>

{{-- DPR PHOTOS --}}
<div class="section">

    <div class="section-title">
        DPR Photos
    </div>

    <div class="section-body">

        @if($dpr->photos->isNotEmpty())

            <table class="photo-table">
                @foreach($dpr->photos->chunk(4) as $row)
                    <tr>
                        @foreach($row as $photo)
                            @php
                                $path = $photoPath($photo->photo_path);
                            @endphp

                            <td>
                                @if($path)
                                    <img src="{{ $path }}"
                                         class="photo">
                                @endif
                            </td>
                        @endforeach

                        @for($i = $row->count(); $i < 4; $i++)
                            <td></td>
                        @endfor
                    </tr>
                @endforeach
            </table>

        @else
            <p class="muted">No DPR-level photos uploaded.</p>
        @endif
    </div>
</div>

{{-- TOMORROW PLAN --}}
<div class="section">

    <div class="section-title">
        Tomorrow Plan
    </div>

    <div class="section-body">

        @if($dpr->tomorrowPlans->isNotEmpty())

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Activity</th>
                        <th>Location</th>
                        <th class="right">Planned Qty</th>
                        <th>Unit</th>
                        <th class="right">Labour</th>
                        <th>Priority</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($dpr->tomorrowPlans as $plan)

                        @php
                            $planLocation = collect([
                                $plan->block?->name,
                                $plan->floor?->name,
                                $plan->projectUnit?->name,
                                $plan->room?->name,
                                $plan->subspace?->name,
                            ])
                                ->filter()
                                ->implode(' -> ');
                        @endphp

                        <tr>
                            <td>
                                {{ $plan->activity?->activity_name ?? '-' }}
                            </td>

                            <td>
                                {{ $planLocation ?: '-' }}
                            </td>

                            <td class="right">
                                {{ $formatQty($plan->planned_quantity) }}
                            </td>

                            <td>
                                {{ $plan->unit ?? '-' }}
                            </td>

                            <td class="right">
                                {{ $plan->planned_labour ?? '-' }}
                            </td>

                            <td>
                                {{ $plan->priority ?? '-' }}
                            </td>

                            <td>
                                {{ $plan->status ?? '-' }}
                            </td>
                        </tr>

                    @endforeach
                </tbody>
            </table>

        @else
            <p class="muted">No Tomorrow Plan linked.</p>
        @endif
    </div>
</div>

{{-- REMARKS / PMO --}}
<div class="section">

    <div class="section-title">
        Remarks & Review
    </div>

    <div class="section-body">

        <table class="meta-table">
            <tr>
                <td style="width:50%;">
                    <span class="label">Engineer General Remarks</span>

                    <div style="font-weight:normal;">
                        {{ $dpr->remarks ?: '-' }}
                    </div>
                </td>

                <td style="width:50%;">
                    <span class="label">PMO Review Remarks</span>

                    <div style="font-weight:normal;">
                        {{ $dpr->pmo_remarks ?: '-' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="footer-note">
    Generated from Ravion ERP · DPR #{{ $dpr->id }}
</div>

</body>
</html>
