<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Labour Wage Report</title>

<style>
    @page {
        margin: 10mm 9mm 9mm 9mm;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        font-family: DejaVu Sans, sans-serif;
        font-size: 7.2px;
        line-height: 1.25;
        color: #172033;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    .header-table td {
        border: none;
        vertical-align: middle;
        padding: 0;
    }

    .logo-cell {
        width: 92px;
    }

    .logo {
        width: 62px;
        height: auto;
        display: block;
    }

    .title-cell {
        text-align: center;
    }

    .report-title {
        font-size: 15px;
        font-weight: 700;
        color: #102a4f;
        letter-spacing: .2px;
    }

    .report-subtitle {
        margin-top: 2px;
        font-size: 7px;
        color: #667085;
    }

    .status-cell {
        width: 92px;
        text-align: right;
    }

    .scope-badge {
        display: inline-block;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        padding: 3px 7px;
        border-radius: 10px;
        font-size: 6.2px;
        font-weight: 700;
        color: #102a4f;
        text-transform: uppercase;
    }

    .gold-line {
        height: 2px;
        margin: 4px 0 5px;
        background: #d99a00;
    }

    .meta-table td {
        border: 1px solid #d6dce5;
        padding: 4px 6px;
        background: #ffffff;
    }

    .meta-label {
        display: block;
        color: #667085;
        font-size: 5.8px;
        text-transform: uppercase;
        margin-bottom: 1px;
    }

    .meta-value {
        font-weight: 700;
        color: #172033;
    }

    .summary-table {
        margin-top: 5px;
        margin-bottom: 7px;
    }

    .summary-table td {
        border: 1px solid #d6dce5;
        padding: 4px 5px;
        text-align: center;
        background: #f8fafc;
    }

    .summary-label {
        display: block;
        color: #667085;
        text-transform: uppercase;
        font-size: 5.5px;
    }

    .summary-value {
        display: block;
        margin-top: 1px;
        font-size: 8.3px;
        font-weight: 700;
        color: #102a4f;
    }

    .section-title {
        margin: 7px 0 4px;
        font-size: 9.3px;
        font-weight: 700;
        color: #102a4f;
    }

    .section-subtitle {
        margin-bottom: 4px;
        color: #667085;
        font-size: 6.2px;
    }

    .report-table {
        table-layout: fixed;
        margin-bottom: 7px;
    }

    .report-table thead {
        display: table-header-group;
    }

    .report-table th {
        border: 1px solid #102a4f;
        background: #102a4f;
        color: #ffffff;
        padding: 3px 2px;
        font-size: 5.9px;
        text-transform: uppercase;
        font-weight: 700;
    }

    .report-table td {
        border: 1px solid #cfd6df;
        padding: 3px 2px;
        vertical-align: middle;
    }

    .project-heading td,
    .engineer-heading td {
        background: #eaf1fa;
        color: #102a4f;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .12px;
        padding: 4px;
    }

    .week-heading td {
        background: #f5f7fa;
        color: #344054;
        font-weight: 700;
        padding: 3px;
    }

    .subtotal td {
        background: #f8fafc;
        font-weight: 700;
        border-top: 1.4px solid #93c5fd;
    }

    .grand-total td {
        background: #eef2f6;
        font-weight: 700;
        border-top: 1.6px solid #102a4f;
    }

    .right {
        text-align: right;
    }

    .center {
        text-align: center;
    }

    .labour-name {
        font-weight: 700;
        color: #172033;
    }

    .small {
        font-size: 5.6px;
        color: #667085;
    }

    .page-break {
        page-break-before: always;
    }

    .avoid-break {
        page-break-inside: avoid;
    }

    .footer {
        position: fixed;
        bottom: -6mm;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 5.3px;
        color: #98a2b3;
    }
</style>
</head>

<body>

@php
    $logoPath = public_path('images/ravion-logo.png');

    $logoData = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : null;

    $scopeLabel = [
        'all_projects' => 'All Projects',
        'specific_project' => 'Specific Project',
        'all_engineers' => 'All Engineers',
        'specific_engineer' => 'Specific Engineer',
    ][$filters['scope']] ?? 'Labour Wage Report';

    $statusLabel = $filters['status'] === 'all_eligible'
        ? 'All Eligible'
        : ucfirst($filters['status']);

    $money = function ($value) {
        return '₹' . number_format((float) $value, 2);
    };

    $qty = function ($value) {
        return rtrim(
            rtrim(
                number_format((float) $value, 2, '.', ''),
                '0'
            ),
            '.'
        );
    };
@endphp

<table class="header-table">
    <tr>
        <td class="logo-cell">
            @if($logoData)
                <img src="{{ $logoData }}" class="logo" alt="Ravion">
            @endif
        </td>

        <td class="title-cell">
            <div class="report-title">LABOUR WAGE REPORT</div>
            <div class="report-subtitle">
                Ravion ERP · {{ $scopeLabel }} · {{ $periodLabel }}
            </div>
        </td>

        <td class="status-cell">
            <span class="scope-badge">{{ $scopeLabel }}</span>
        </td>
    </tr>
</table>

<div class="gold-line"></div>

<table class="meta-table">
    <tr>
        <td style="width:25%;">
            <span class="meta-label">Report Period</span>
            <span class="meta-value">
                {{ $fromDate->format('d M Y') }}
                -
                {{ $toDate->format('d M Y') }}
            </span>
        </td>

        <td style="width:25%;">
            <span class="meta-label">Period Type</span>
            <span class="meta-value">{{ $periodLabel }}</span>
        </td>

        <td style="width:25%;">
            <span class="meta-label">Wage Sheet Status</span>
            <span class="meta-value">{{ $statusLabel }}</span>
        </td>

        <td style="width:25%;">
            <span class="meta-label">Generated At</span>
            <span class="meta-value">{{ now()->format('d M Y, h:i A') }}</span>
        </td>
    </tr>
</table>

<table class="summary-table">
    <tr>
        <td>
            <span class="summary-label">Wage Sheets</span>
            <span class="summary-value">{{ $totals['wage_sheet_count'] }}</span>
        </td>

        <td>
            <span class="summary-label">Projects</span>
            <span class="summary-value">{{ $totals['project_count'] }}</span>
        </td>

        <td>
            <span class="summary-label">Unique Labour</span>
            <span class="summary-value">{{ $totals['labour_count'] }}</span>
        </td>

        <td>
            <span class="summary-label">Payable Days</span>
            <span class="summary-value">{{ $qty($totals['payable_days']) }}</span>
        </td>

        <td>
            <span class="summary-label">Normal Wages</span>
            <span class="summary-value">{{ $money($totals['normal_wages']) }}</span>
        </td>

        <td>
            <span class="summary-label">OT Amount</span>
            <span class="summary-value">{{ $money($totals['ot_amount']) }}</span>
        </td>

        <td>
            <span class="summary-label">Deductions</span>
            <span class="summary-value">{{ $money($totals['deductions']) }}</span>
        </td>

        <td>
            <span class="summary-label">Net Payable</span>
            <span class="summary-value">{{ $money($totals['net_payable']) }}</span>
        </td>
    </tr>
</table>

@if(in_array($filters['scope'], ['all_engineers', 'specific_engineer'], true))

    @foreach($engineerGroups as $group)

        @if(!$loop->first)
            <div class="page-break"></div>
        @endif

        <div class="section-title">
            ENGINEER: {{ $group['label'] }}
        </div>

        <div class="section-subtitle">
            {{ $group['project_count'] }} project(s) ·
            {{ $group['labour_count'] }} unique labour ·
            {{ $qty($group['payable_days']) }} payable days ·
            Net {{ $money($group['net_payable']) }}
        </div>

        @foreach($group['projects'] as $projectRow)

            <table class="report-table">
                <tbody>
                    <tr class="project-heading">
                        <td colspan="10">
                            {{ $projectRow['project_name'] }}
                            @if($projectRow['project_code'])
                                · {{ $projectRow['project_code'] }}
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

            @foreach($projectRow['wage_sheets'] as $sheet)
                <table class="report-table">
                    <thead>
                        <tr>
                            <th style="width:3%;">#</th>
                            <th style="width:17%;">Labour</th>
                            <th style="width:14%;">Designation</th>
                            <th style="width:7%;">Days</th>
                            <th style="width:9%;">Daily Rate</th>
                            <th style="width:11%;">Normal Wage</th>
                            <th style="width:8%;">OT Hrs</th>
                            <th style="width:9%;">OT Rate</th>
                            <th style="width:10%;">OT Amount</th>
                            <th style="width:12%;">Net Payable</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr class="week-heading">
                            <td colspan="10">
                                {{ $sheet->wage_sheet_number }}
                                ·
                                {{ $sheet->week_start_date?->format('d M Y') }}
                                -
                                {{ $sheet->week_end_date?->format('d M Y') }}
                                ·
                                {{ strtoupper($sheet->status) }}
                            </td>
                        </tr>

                        @foreach($sheet->details as $detail)
                            <tr>
                                <td class="center">{{ $loop->iteration }}</td>

                                <td>
                                    <div class="labour-name">
                                        {{ $detail->labour?->full_name ?? '—' }}
                                    </div>
                                    <div class="small">
                                        {{ $detail->labour?->labour_code ?? '' }}
                                    </div>
                                </td>

                                <td>
                                    {{ $detail->designationRole?->name ?? '—' }}
                                </td>

                                <td class="right">
                                    {{ $qty($detail->payable_days) }}
                                </td>

                                <td class="right">
                                    {{ $money($detail->daily_wage_rate) }}
                                </td>

                                <td class="right">
                                    {{ $money($detail->normal_wage) }}
                                </td>

                                <td class="right">
                                    {{ $qty($detail->ot_hours) }}
                                </td>

                                <td class="right">
                                    {{ $money($detail->ot_hourly_rate) }}
                                </td>

                                <td class="right">
                                    {{ $money($detail->ot_wage) }}
                                </td>

                                <td class="right">
                                    {{ $money($detail->net_payable) }}
                                </td>
                            </tr>
                        @endforeach

                        <tr class="subtotal">
                            <td colspan="3">Week Total</td>

                            <td class="right">
                                {{ $qty($sheet->total_payable_days) }}
                            </td>

                            <td></td>

                            <td class="right">
                                {{ $money($sheet->total_normal_wages) }}
                            </td>

                            <td class="right">
                                {{ $qty($sheet->total_ot_hours) }}
                            </td>

                            <td></td>

                            <td class="right">
                                {{ $money($sheet->total_ot_wages) }}
                            </td>

                            <td class="right">
                                {{ $money($sheet->net_labour_wages) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endforeach

            <table class="report-table avoid-break">
                <tbody>
                    <tr class="grand-total">
                        <td colspan="2">
                            {{ $projectRow['project_name'] }} — Project Total
                        </td>

                        <td class="right">
                            Labour: {{ $projectRow['labour_count'] }}
                        </td>

                        <td class="right">
                            Days: {{ $qty($projectRow['payable_days']) }}
                        </td>

                        <td class="right">
                            Normal: {{ $money($projectRow['normal_wages']) }}
                        </td>

                        <td class="right">
                            OT: {{ $money($projectRow['ot_amount']) }}
                        </td>

                        <td class="right">
                            Ded.: {{ $money($projectRow['deductions']) }}
                        </td>

                        <td class="right">
                            Net: {{ $money($projectRow['net_payable']) }}
                        </td>
                    </tr>
                </tbody>
            </table>

        @endforeach

        <table class="report-table avoid-break">
            <tbody>
                <tr class="grand-total">
                    <td>Engineer Total — {{ $group['label'] }}</td>
                    <td class="right">Projects: {{ $group['project_count'] }}</td>
                    <td class="right">Labour: {{ $group['labour_count'] }}</td>
                    <td class="right">Days: {{ $qty($group['payable_days']) }}</td>
                    <td class="right">Normal: {{ $money($group['normal_wages']) }}</td>
                    <td class="right">OT: {{ $money($group['ot_amount']) }}</td>
                    <td class="right">Net: {{ $money($group['net_payable']) }}</td>
                </tr>
            </tbody>
        </table>

    @endforeach

@else

    @foreach($projectRows as $projectRow)

        @if(!$loop->first)
            <div class="page-break"></div>
        @endif

        <div class="section-title">
            {{ $projectRow['project_name'] }}
            @if($projectRow['project_code'])
                · {{ $projectRow['project_code'] }}
            @endif
        </div>

        <div class="section-subtitle">
            Engineer(s):
            {{ $projectRow['engineer_names'] ?: 'Unassigned' }}
            ·
            {{ $projectRow['wage_sheet_count'] }} wage sheet(s)
        </div>

        @foreach($projectRow['wage_sheets'] as $sheet)

            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width:3%;">#</th>
                        <th style="width:17%;">Labour</th>
                        <th style="width:14%;">Designation</th>
                        <th style="width:7%;">Days</th>
                        <th style="width:9%;">Daily Rate</th>
                        <th style="width:11%;">Normal Wage</th>
                        <th style="width:8%;">OT Hrs</th>
                        <th style="width:9%;">OT Rate</th>
                        <th style="width:10%;">OT Amount</th>
                        <th style="width:12%;">Net Payable</th>
                    </tr>
                </thead>

                <tbody>
                    <tr class="week-heading">
                        <td colspan="10">
                            {{ $sheet->wage_sheet_number }}
                            ·
                            {{ $sheet->week_start_date?->format('d M Y') }}
                            -
                            {{ $sheet->week_end_date?->format('d M Y') }}
                            ·
                            {{ strtoupper($sheet->status) }}
                        </td>
                    </tr>

                    @foreach($sheet->details as $detail)
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>

                            <td>
                                <div class="labour-name">
                                    {{ $detail->labour?->full_name ?? '—' }}
                                </div>

                                <div class="small">
                                    {{ $detail->labour?->labour_code ?? '' }}
                                </div>
                            </td>

                            <td>
                                {{ $detail->designationRole?->name ?? '—' }}
                            </td>

                            <td class="right">
                                {{ $qty($detail->payable_days) }}
                            </td>

                            <td class="right">
                                {{ $money($detail->daily_wage_rate) }}
                            </td>

                            <td class="right">
                                {{ $money($detail->normal_wage) }}
                            </td>

                            <td class="right">
                                {{ $qty($detail->ot_hours) }}
                            </td>

                            <td class="right">
                                {{ $money($detail->ot_hourly_rate) }}
                            </td>

                            <td class="right">
                                {{ $money($detail->ot_wage) }}
                            </td>

                            <td class="right">
                                {{ $money($detail->net_payable) }}
                            </td>
                        </tr>
                    @endforeach

                    <tr class="subtotal">
                        <td colspan="3">Week Total</td>

                        <td class="right">
                            {{ $qty($sheet->total_payable_days) }}
                        </td>

                        <td></td>

                        <td class="right">
                            {{ $money($sheet->total_normal_wages) }}
                        </td>

                        <td class="right">
                            {{ $qty($sheet->total_ot_hours) }}
                        </td>

                        <td></td>

                        <td class="right">
                            {{ $money($sheet->total_ot_wages) }}
                        </td>

                        <td class="right">
                            {{ $money($sheet->net_labour_wages) }}
                        </td>
                    </tr>
                </tbody>
            </table>

        @endforeach

        <table class="report-table avoid-break">
            <tbody>
                <tr class="grand-total">
                    <td colspan="2">
                        Project Total — {{ $projectRow['project_name'] }}
                    </td>

                    <td class="right">
                        Labour: {{ $projectRow['labour_count'] }}
                    </td>

                    <td class="right">
                        Days: {{ $qty($projectRow['payable_days']) }}
                    </td>

                    <td class="right">
                        Normal: {{ $money($projectRow['normal_wages']) }}
                    </td>

                    <td class="right">
                        OT: {{ $money($projectRow['ot_amount']) }}
                    </td>

                    <td class="right">
                        Add.: {{ $money($projectRow['additions']) }}
                    </td>

                    <td class="right">
                        Ded.: {{ $money($projectRow['deductions']) }}
                    </td>

                    <td class="right">
                        Net: {{ $money($projectRow['net_payable']) }}
                    </td>
                </tr>
            </tbody>
        </table>

    @endforeach

@endif

<div class="footer">
    Ravion ERP · Labour Wage Report · {{ $fromDate->format('d M Y') }} - {{ $toDate->format('d M Y') }}
</div>

</body>
</html>
