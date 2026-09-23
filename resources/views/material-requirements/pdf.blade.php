<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Material Requirement - MR-{{ str_pad($materialRequirement->id, 4, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page { margin: 28px 32px 34px 32px; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
        }
        .company {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: .2px;
            margin-bottom: 3px;
        }
        .subtitle {
            font-size: 9px;
            color: #6b7280;
            margin-bottom: 16px;
        }
        .title {
            font-size: 15px;
            font-weight: 700;
            margin: 0 0 8px 0;
        }
        .rule {
            border-top: 2px solid #374151;
            margin-bottom: 12px;
        }
        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .meta td {
            width: 50%;
            border: 1px solid #d1d5db;
            padding: 7px 9px;
            vertical-align: top;
        }
        .label {
            display: block;
            font-size: 7px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .value {
            font-size: 10px;
            font-weight: 700;
        }
        .section-title {
            font-size: 11px;
            font-weight: 700;
            margin: 0 0 6px 0;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .items th {
            background: #10212f;
            color: #fff;
            font-size: 8px;
            text-transform: uppercase;
            text-align: left;
            padding: 7px 6px;
            border: 1px solid #10212f;
        }
        .items td {
            padding: 7px 6px;
            border: 1px solid #d1d5db;
            vertical-align: top;
            line-height: 1.35;
        }
        .items .num { width: 5%; text-align: center; }
        .items .product { width: 18%; }
        .items .spec { width: 25%; }
        .items .grade { width: 12%; }
        .items .brand { width: 12%; }
        .items .qty { width: 8%; text-align: right; }
        .items .unit { width: 8%; }
        .items .remarks { width: 12%; }
        .spec-detail { color: #6b7280; font-size: 8px; margin-top: 3px; overflow-wrap: break-word; }
        .items td { overflow-wrap: break-word; }
        .summary {
            margin-top: 8px;
            text-align: right;
            font-weight: 700;
        }
        .footer {
            position: fixed;
            bottom: -18px;
            left: 0;
            right: 0;
            color: #9ca3af;
            font-size: 7px;
            text-align: center;
        }
    </style>
</head>
<body>
@php
    $mrNo = 'MR-' . str_pad($materialRequirement->id, 4, '0', STR_PAD_LEFT);
    $items = $materialRequirement->items ?? collect();

    $unitLabel = function ($item) {
        $unit = $item->unitMaster ?? null;
        if (!$unit) {
            return '-';
        }

        return $unit->unit_code
            ?? $unit->code
            ?? $unit->unit_symbol
            ?? $unit->symbol
            ?? $unit->unit_name
            ?? $unit->name
            ?? '-';
    };


@endphp

<div class="company">RAVION VERTEX SYSTEMS PVT LTD</div>
<div class="subtitle">Construction Materials Requirement</div>

<div class="title">MATERIAL REQUIREMENT - {{ $mrNo }}</div>
<div class="rule"></div>

<table class="meta">
    <tr>
        <td>
            <span class="label">Project</span>
            <span class="value">{{ $materialRequirement->project->project_name ?? $materialRequirement->project->name ?? '-' }}</span>
        </td>
        <td>
            <span class="label">Required Date</span>
            <span class="value">
                {{ $materialRequirement->required_date
                    ? \Carbon\Carbon::parse($materialRequirement->required_date)->format('d/m/Y')
                    : '-' }}
            </span>
        </td>
    </tr>
</table>

<div class="section-title">Material Required List</div>

<table class="items">
    <thead>
    <tr>
        <th class="num">#</th>
        <th class="product">Product</th>
        <th class="spec">Specification / Size</th>
        <th class="grade">Grade</th>
        <th class="brand">Brand</th>
        <th class="qty">Qty</th>
        <th class="unit">Unit</th>
        <th class="remarks">Remarks</th>
    </tr>
    </thead>
    <tbody>
    @forelse($items as $index => $item)
        <tr>
            <td class="num">{{ $index + 1 }}</td>
            <td class="product">
                <strong>{{ $item->materialType->material_type_name ?? $item->material->material_name ?? '-' }}</strong>
            </td>
            <td class="spec">
                @php
                    $masterSpec = trim((string) ($item->specification?->specification_name ?? ''));
                    $customSpec = trim((string) ($item->specification_text ?? ''));
                @endphp
                @if($masterSpec !== '')<strong>{{ $masterSpec }}</strong>@endif
                @if($customSpec !== '' && ($masterSpec === '' || strcasecmp($customSpec, $masterSpec) !== 0))
                    <div class="{{ $masterSpec !== '' ? 'spec-detail' : '' }}">@if($masterSpec !== '')Additional details: @endif{{ $customSpec }}</div>
                @elseif($masterSpec === '')
                    -
                @endif
            </td>
            <td class="grade">{{ $item->grade?->grade_name ?? '-' }}</td>
            <td class="brand">{{ $item->brand?->brand_name ?? '-' }}</td>
            <td class="qty">{{ rtrim(rtrim(number_format((float) $item->required_quantity, 3, '.', ''), '0'), '.') }}</td>
            <td class="unit">{{ $unitLabel($item) }}</td>
            <td class="remarks">{{ $item->remarks ?: '-' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="8" style="text-align:center; color:#6b7280;">No material items recorded.</td>
        </tr>
    @endforelse
    </tbody>
</table>

<div class="summary">Total Items: {{ $items->count() }}</div>

<div class="footer">
    Generated by Ravion ERP · {{ $mrNo }}
</div>
</body>
</html>
