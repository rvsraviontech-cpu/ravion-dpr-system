<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Labour Wage Report</title>
<style>
@page{margin:10mm 9mm 9mm}
*{box-sizing:border-box}
body{margin:0;font-family:DejaVu Sans,sans-serif;font-size:7.4px;line-height:1.25;color:#172033}
table{width:100%;border-collapse:collapse}
.header-table td{border:none;vertical-align:middle;padding:0}
.logo{width:62px;height:auto;display:block}
.report-title{text-align:center;font-size:15px;font-weight:700;color:#102a4f}
.report-subtitle{text-align:center;margin-top:2px;font-size:7px;color:#667085}
.scope{text-align:right;width:92px}
.badge{display:inline-block;border:1px solid #cbd5e1;background:#f8fafc;padding:3px 7px;border-radius:10px;font-size:6.2px;font-weight:700;color:#102a4f;text-transform:uppercase}
.gold-line{height:2px;margin:4px 0 5px;background:#d99a00}
.meta td{border:1px solid #d6dce5;padding:4px 6px}
.label{display:block;color:#667085;font-size:5.7px;text-transform:uppercase}
.value{font-weight:700}
.summary{margin:5px 0 7px}
.summary td{border:1px solid #d6dce5;padding:4px;text-align:center;background:#f8fafc}
.summary .value{font-size:8.2px;color:#102a4f}
.report{table-layout:fixed;margin-bottom:8px}
.report thead{display:table-header-group}
.report th{border:1px solid #102a4f;background:#102a4f;color:#fff;padding:4px 3px;font-size:5.9px;text-transform:uppercase}
.report td{border:1px solid #cfd6df;padding:4px 3px;vertical-align:middle}
.engineer td{background:#eaf1fa;color:#102a4f;font-weight:700;padding:5px}
.total td{background:#eef2f6;font-weight:700;border-top:1.5px solid #102a4f}
.right{text-align:right}.center{text-align:center}
.page-break{page-break-before:always}
.footer{position:fixed;bottom:-6mm;left:0;right:0;text-align:center;font-size:5.3px;color:#98a2b3}
</style>
</head>
<body>
@php
$logoPath=public_path('images/ravion-logo.png');
$logoData=file_exists($logoPath)?'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)):null;
$scopeLabel=[
'all_projects'=>'All Projects',
'specific_project'=>'Specific Project',
'all_engineers'=>'All Engineers',
'specific_engineer'=>'Specific Engineer',
][$filters['scope']]??'Labour Wage Report';
$statusLabel=$filters['status']==='all_eligible'?'All Eligible':ucfirst($filters['status']);
$money=fn($v)=>'₹'.number_format((float)$v,2);
$qty=function($v){return rtrim(rtrim(number_format((float)$v,2,'.',''),'0'),'.');};
@endphp

<table class="header-table">
<tr>
<td style="width:92px">@if($logoData)<img src="{{ $logoData }}" class="logo" alt="Ravion">@endif</td>
<td>
<div class="report-title">LABOUR WAGE REPORT</div>
<div class="report-subtitle">Ravion ERP · {{ $scopeLabel }} · {{ $periodLabel }}</div>
</td>
<td class="scope"><span class="badge">{{ $scopeLabel }}</span></td>
</tr>
</table>
<div class="gold-line"></div>

<table class="meta">
<tr>
<td><span class="label">Report Period</span><span class="value">{{ $fromDate->format('d M Y') }} - {{ $toDate->format('d M Y') }}</span></td>
<td><span class="label">Period Type</span><span class="value">{{ $periodLabel }}</span></td>
<td><span class="label">Wage Sheet Status</span><span class="value">{{ $statusLabel }}</span></td>
<td><span class="label">Generated At</span><span class="value">{{ now()->format('d M Y, h:i A') }}</span></td>
</tr>
</table>

<table class="summary">
<tr>
<td><span class="label">Wage Sheets</span><span class="value">{{ $totals['wage_sheet_count'] }}</span></td>
<td><span class="label">Projects</span><span class="value">{{ $totals['project_count'] }}</span></td>
<td><span class="label">Unique Labour</span><span class="value">{{ $totals['labour_count'] }}</span></td>
<td><span class="label">Payable Days</span><span class="value">{{ $qty($totals['payable_days']) }}</span></td>
<td><span class="label">Normal Wages</span><span class="value">{{ $money($totals['normal_wages']) }}</span></td>
<td><span class="label">OT Amount</span><span class="value">{{ $money($totals['ot_amount']) }}</span></td>
<td><span class="label">Deductions</span><span class="value">{{ $money($totals['deductions']) }}</span></td>
<td><span class="label">Net Payable</span><span class="value">{{ $money($totals['net_payable']) }}</span></td>
</tr>
</table>
@if(in_array($filters['scope'],['all_engineers','specific_engineer'],true))
@foreach($engineerGroups as $group)

<table class="report">
<tbody>
<tr class="engineer">
<td colspan="10">
{{ $group['label'] }} ·
{{ $group['project_count'] }} project(s) ·
{{ $group['labour_count'] }} unique labour ·
{{ $qty($group['payable_days']) }} payable days ·
Net {{ $money($group['net_payable']) }}
</td>
</tr>
</tbody>
<thead>
<tr>
<th style="width:20%">Project</th>
<th style="width:7%">Weeks</th>
<th style="width:7%">Labour</th>
<th style="width:9%">Payable Days</th>
<th style="width:12%">Normal Wages</th>
<th style="width:8%">OT Hrs</th>
<th style="width:10%">OT Amount</th>
<th style="width:9%">Additions</th>
<th style="width:9%">Deductions</th>
<th style="width:12%">Net Payable</th>
</tr>
</thead>
<tbody>
@foreach($group['projects'] as $row)
<tr>
<td><strong>{{ $row['project_name'] }}</strong>@if($row['project_code'])<br><span style="font-size:5.8px;color:#667085">{{ $row['project_code'] }}</span>@endif</td>
<td class="right">{{ $row['wage_sheet_count'] }}</td>
<td class="right">{{ $row['labour_count'] }}</td>
<td class="right">{{ $qty($row['payable_days']) }}</td>
<td class="right">{{ $money($row['normal_wages']) }}</td>
<td class="right">{{ $qty($row['ot_hours']) }}</td>
<td class="right">{{ $money($row['ot_amount']) }}</td>
<td class="right">{{ $money($row['additions']) }}</td>
<td class="right">{{ $money($row['deductions']) }}</td>
<td class="right"><strong>{{ $money($row['net_payable']) }}</strong></td>
</tr>
@endforeach
<tr class="total">
<td>Engineer Total</td>
<td class="right">{{ $group['project_count'] }}</td>
<td class="right">{{ $group['labour_count'] }}</td>
<td class="right">{{ $qty($group['payable_days']) }}</td>
<td class="right">{{ $money($group['normal_wages']) }}</td>
<td></td>
<td class="right">{{ $money($group['ot_amount']) }}</td>
<td></td>
<td></td>
<td class="right">{{ $money($group['net_payable']) }}</td>
</tr>
</tbody>
</table>
@endforeach
@else
<table class="report">
<thead>
<tr>
<th style="width:18%">Project</th>
<th style="width:14%">Engineer(s)</th>
<th style="width:6%">Weeks</th>
<th style="width:6%">Labour</th>
<th style="width:9%">Payable Days</th>
<th style="width:11%">Normal Wages</th>
<th style="width:7%">OT Hrs</th>
<th style="width:9%">OT Amount</th>
<th style="width:8%">Additions</th>
<th style="width:8%">Deductions</th>
<th style="width:10%">Net Payable</th>
</tr>
</thead>
<tbody>
@foreach($projectRows as $row)
<tr>
<td><strong>{{ $row['project_name'] }}</strong>@if($row['project_code'])<br><span style="font-size:5.8px;color:#667085">{{ $row['project_code'] }}</span>@endif</td>
<td>{{ $row['engineer_names'] ?: 'Unassigned' }}</td>
<td class="right">{{ $row['wage_sheet_count'] }}</td>
<td class="right">{{ $row['labour_count'] }}</td>
<td class="right">{{ $qty($row['payable_days']) }}</td>
<td class="right">{{ $money($row['normal_wages']) }}</td>
<td class="right">{{ $qty($row['ot_hours']) }}</td>
<td class="right">{{ $money($row['ot_amount']) }}</td>
<td class="right">{{ $money($row['additions']) }}</td>
<td class="right">{{ $money($row['deductions']) }}</td>
<td class="right"><strong>{{ $money($row['net_payable']) }}</strong></td>
</tr>
@endforeach
<tr class="total">
<td colspan="2">Grand Total</td>
<td class="right">{{ $totals['wage_sheet_count'] }}</td>
<td class="right">{{ $totals['labour_count'] }}</td>
<td class="right">{{ $qty($totals['payable_days']) }}</td>
<td class="right">{{ $money($totals['normal_wages']) }}</td>
<td class="right">{{ $qty($totals['ot_hours']) }}</td>
<td class="right">{{ $money($totals['ot_amount']) }}</td>
<td class="right">{{ $money($totals['additions']) }}</td>
<td class="right">{{ $money($totals['deductions']) }}</td>
<td class="right">{{ $money($totals['net_payable']) }}</td>
</tr>
</tbody>
</table>
@endif

<div class="footer">
Ravion ERP · Labour Wage Report · {{ $fromDate->format('d M Y') }} - {{ $toDate->format('d M Y') }}
</div>
</body>
</html>
