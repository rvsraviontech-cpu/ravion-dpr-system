<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weekly Labour Payment Register</title>

    <style>
        @page {
    margin: 9mm 9mm 8mm 9mm;
}

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 7.2px;
            line-height: 1.2;
            color: #172033;
        }

        .header-table,
        .meta-table,
        .summary-table,
        .register-table,
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .logo-cell {
    width: 110px;
}

        .logo {
            width: 70px;
            height: auto;
            display: block;
        }

        .report-title {
    font-size: 15px;
    font-weight: 700;
    color: #102a4f;
    letter-spacing: .15px;
    text-align: center;
}

.report-subtitle {
    margin-top: 2px;
    color: #667085;
    font-size: 7px;
    text-align: center;
}

        .status {
            display: inline-block;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            padding: 3px 7px;
            border-radius: 10px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 6.6px;
            color: #102a4f;
        }

        .gold-line {
            height: 2px;
            margin: 4px 0 5px;
            background: #d99a00;
        }

        .meta-table td {
            border: 1px solid #d6dce5;
            padding: 4px 6px;
        }

        .meta-label {
            display: block;
            color: #667085;
            font-size: 6.1px;
            text-transform: uppercase;
            margin-bottom: 1px;
        }

        .meta-value {
            font-weight: 700;
            color: #172033;
        }

        .summary-table {
            margin-top: 5px;
            margin-bottom: 6px;
        }

        .summary-table td {
            border: 1px solid #d6dce5;
            padding: 4px 5px;
            text-align: center;
        }

        .summary-label {
            display: block;
            color: #667085;
            text-transform: uppercase;
            font-size: 5.8px;
        }

        .summary-value {
            display: block;
            margin-top: 1px;
            font-size: 8.4px;
            font-weight: 700;
            color: #102a4f;
        }

        .register-table {
            table-layout: fixed;
        }

        .register-table thead {
            display: table-header-group;
        }

        .register-table th {
            background: #102a4f;
            color: #ffffff;
            border: 1px solid #102a4f;
            padding: 4px 3px;
            font-size: 6.3px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .register-table td {
            border: 1px solid #cfd6df;
            padding: 3px 3px;
            vertical-align: middle;
            page-break-inside: avoid;
        }

        .register-table tr {
            page-break-inside: avoid;
        }

        .group-row td {
            background: #eaf1fa;
            color: #102a4f;
            font-weight: 700;
            padding: 3px 4px;
            text-transform: uppercase;
            letter-spacing: .15px;
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
            font-size: 5.9px;
            color: #667085;
        }

        .projects {
            font-size: 6.1px;
            color: #344054;
            line-height: 1.25;
        }

        .net {
            font-weight: 700;
            color: #102a4f;
        }

        .signature-cell {
            height: 24px;
        }

        .totals-row td {
            background: #f4f6f8;
            font-weight: 700;
            border-top: 1.5px solid #102a4f;
        }

        .notes {
    margin-top: 3px;
    font-size: 5.7px;
    line-height: 1.1;
    color: #667085;
    page-break-inside: avoid;
}

        .signature-table {
    width: 100%;
    margin-top: 4px;
    border-collapse: collapse;
    page-break-inside: avoid;
}

.signature-table tr,
.signature-table td {
    page-break-inside: avoid;
}

.signature-table td {
    width: 25%;
    padding: 8px 8px 0;
    text-align: center;
    vertical-align: bottom;
}

.signature-line {
    min-height: 18px;
    border-top: 1px solid #7b8794;
    padding-top: 2px;
    color: #475467;
    font-size: 6px;
    line-height: 1.15;
}

        .footer {
            position: fixed;
            bottom: -7mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 5.6px;
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

    $formatQty = function ($value) {
        return rtrim(
            rtrim(
                number_format((float) $value, 2, '.', ''),
                '0'
            ),
            '.'
        );
    };

    $money = function ($value) {
        return '₹' . number_format((float) $value, 2);
    };

    $groupedDetails = $register->details->groupBy(
        fn ($detail) =>
            $detail->labourGroup?->name
            ?? $detail->labour?->labourGroup?->name
            ?? 'Un-grouped Labour'
    );

    $rowNumber = 0;
@endphp

<table class="header-table">
    <tr>
        <td class="logo-cell">
            @if($logoData)
                <img src="{{ $logoData }}" class="logo" alt="Ravion">
            @endif
        </td>

        <td>
            <div class="report-title">WEEKLY LABOUR PAYMENT REGISTER</div>
            <div class="report-subtitle">
                Consolidated labour payment across all approved project attendance
            </div>
        </td>

        <td style="width:110px; text-align:right;">
            <span class="status">{{ $register->status }}</span>
        </td>
    </tr>
</table>

<div class="gold-line"></div>

<table class="meta-table">
    <tr>
        <td style="width:25%;">
            <span class="meta-label">Register Number</span>
            <span class="meta-value">{{ $register->register_number }}</span>
        </td>

        <td style="width:25%;">
            <span class="meta-label">Week</span>
            <span class="meta-value">
                {{ $register->week_start_date?->format('d M Y') }}
                -
                {{ $register->week_end_date?->format('d M Y') }}
            </span>
        </td>

        <td style="width:25%;">
            <span class="meta-label">Generated By</span>
            <span class="meta-value">{{ $register->generatedBy?->name ?? '-' }}</span>
        </td>

        <td style="width:25%;">
            <span class="meta-label">Generated At</span>
            <span class="meta-value">
                {{ $register->generated_at?->format('d M Y, h:i A') ?? '-' }}
            </span>
        </td>
    </tr>
</table>

<table class="summary-table">
    <tr>
        <td>
            <span class="summary-label">Labours</span>
            <span class="summary-value">{{ $register->total_labours }}</span>
        </td>

        <td>
            <span class="summary-label">Payable Days</span>
            <span class="summary-value">{{ $formatQty($register->total_payable_days) }}</span>
        </td>

        <td>
            <span class="summary-label">Normal Wages</span>
            <span class="summary-value">{{ $money($register->total_normal_wages) }}</span>
        </td>

        <td>
            <span class="summary-label">OT Wages</span>
            <span class="summary-value">{{ $money($register->total_ot_wages) }}</span>
        </td>

        <td>
            <span class="summary-label">Additions</span>
            <span class="summary-value">{{ $money($register->total_additions) }}</span>
        </td>

        <td>
            <span class="summary-label">Deductions</span>
            <span class="summary-value">{{ $money($register->total_deductions) }}</span>
        </td>

        <td>
            <span class="summary-label">Net Payable</span>
            <span class="summary-value">{{ $money($register->net_payable) }}</span>
        </td>
    </tr>
</table>

<table class="register-table">
    <thead>
        <tr>
            <th style="width:3%;">#</th>
            <th style="width:13%;">Labour</th>
            <th style="width:11%;">Designation</th>
            <th style="width:21%;">Projects Worked</th>
            <th style="width:5%;">Days</th>
            <th style="width:7%;">Rate</th>
            <th style="width:8%;">Normal</th>
            <th style="width:5%;">OT Hrs</th>
            <th style="width:7%;">OT Wage</th>
            <th style="width:6%;">Add.</th>
            <th style="width:6%;">Ded.</th>
            <th style="width:8%;">Net Payable</th>
            <th style="width:10%;">Signature</th>
        </tr>
    </thead>

    @foreach($groupedDetails as $groupName => $groupDetails)
        <tbody>
            <tr class="group-row">
                <td colspan="13">
                    {{ $groupName }} - {{ $groupDetails->count() }} labour{{ $groupDetails->count() === 1 ? '' : 's' }}
                </td>
            </tr>

            @foreach($groupDetails as $detail)
                @php
                    $rowNumber++;

                    $projectSummary = $detail->allocations
                        ->map(function ($allocation) use ($formatQty) {
                            return ($allocation->project_name ?: 'Unknown Project')
                                . ' (' . $formatQty($allocation->payable_days) . ')';
                        })
                        ->implode(' | ');
                @endphp

                <tr>
                    <td class="center">{{ $rowNumber }}</td>

                    <td>
                        <div class="labour-name">{{ $detail->labour?->full_name ?? '-' }}</div>
                        <div class="small">{{ $detail->labour?->labour_code ?? '' }}</div>
                    </td>

                    <td>
                        {{ $detail->designationRole?->name ?? '-' }}
                    </td>

                    <td class="projects">
                        {{ $projectSummary ?: '-' }}
                    </td>

                    <td class="right">
                        {{ $formatQty($detail->payable_days) }}
                    </td>

                    <td class="right">
                        {{ $money($detail->daily_wage_rate) }}
                    </td>

                    <td class="right">
                        {{ $money($detail->normal_wage) }}
                    </td>

                    <td class="right">
                        {{ $formatQty($detail->ot_hours) }}
                    </td>

                    <td class="right">
                        {{ $money($detail->ot_wage) }}
                    </td>

                    <td class="right">
                        {{ $money($detail->additions) }}
                    </td>

                    <td class="right">
                        {{ $money($detail->deductions) }}
                    </td>

                    <td class="right net">
                        {{ $money($detail->net_payable) }}
                    </td>

                    <td class="signature-cell"></td>
                </tr>
            @endforeach
        </tbody>
    @endforeach

    <tfoot>
        <tr class="totals-row">
            <td colspan="4">TOTAL</td>
            <td class="right">{{ $formatQty($register->total_payable_days) }}</td>
            <td></td>
            <td class="right">{{ $money($register->total_normal_wages) }}</td>
            <td class="right">{{ $formatQty($register->total_ot_hours) }}</td>
            <td class="right">{{ $money($register->total_ot_wages) }}</td>
            <td class="right">{{ $money($register->total_additions) }}</td>
            <td class="right">{{ $money($register->total_deductions) }}</td>
            <td class="right net">{{ $money($register->net_payable) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

<div class="notes">
    Project figures in brackets represent payable days allocated to that project.
    This register is labour-centric and consolidates payment across all projects for the week.
</div>

<table class="signature-table">
    <tr>
        <td>
            <div class="signature-line">
                Prepared By
                @if($register->generatedBy)
                    <br>{{ $register->generatedBy->name }}
                @endif
            </div>
        </td>

        <td>
            <div class="signature-line">
                Checked By
            </div>
        </td>

        <td>
            <div class="signature-line">
                Approved By
                @if($register->approvedBy)
                    <br>{{ $register->approvedBy->name }}
                @endif
            </div>
        </td>

        <td>
            <div class="signature-line">
                Payment Verified By
            </div>
        </td>
    </tr>
</table>

<div class="footer">
    Ravion ERP - Weekly Labour Payment Register - {{ $register->register_number }}
</div>

</body>
</html>
