<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Material Requirement - MR-{{ str_pad($materialRequirement->id, 4, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page { margin: 34px 38px 43px 38px; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; color: #1c2b36; font-size: 9px; line-height: 1.5; }
        .top { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .top td { vertical-align: top; }
        .brand { color: #10212f; font-size: 16px; font-weight: bold; letter-spacing: .3px; }
        .brand-sub { color: #657585; font-size: 8px; margin-top: 2px; }
        .document-label { color: #10212f; font-size: 15px; font-weight: bold; text-align: right; }
        .document-number { color: #627487; font-size: 9px; text-align: right; margin-top: 3px; }
        .accent { height: 4px; background: #10212f; margin-bottom: 17px; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .meta td { width: 33.333%; border: 1px solid #e0e6eb; padding: 9px 11px; vertical-align: top; }
        .label { font-size: 7px; color: #718092; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
        .value { font-size: 9px; font-weight: bold; color: #152838; overflow-wrap: break-word; }
        .section-head { width: 100%; border-collapse: collapse; margin: 17px 0 7px; }
        .section-head td { vertical-align: bottom; }
        .section-title { color: #10212f; font-size: 11px; font-weight: bold; }
        .section-count { text-align: right; color: #627487; font-size: 8px; }
        table.items { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .items th { background: #10212f; color: #ffffff; text-transform: uppercase; letter-spacing: .4px; padding: 9px 10px; font-size: 8px; text-align: left; }
        .items td { padding: 10px; border-bottom: 1px solid #e3e9ee; vertical-align: top; overflow-wrap: break-word; }
        .items tr { page-break-inside: avoid; }
        .items tbody tr:nth-child(even) { background: #f6f8fa; }
        .serial { width: 8%; color: #607284; }
        .material { width: 68%; }
        .quantity { width: 24%; text-align: right; }
        .material-name { font-size: 10px; color: #142839; font-weight: bold; }
        .material-spec { color: #465a6b; font-size: 9px; margin-top: 3px; }
        .material-detail { color: #718092; font-size: 8px; margin-top: 3px; }
        .quantity-number { font-size: 11px; font-weight: bold; color: #10212f; }
        .quantity-unit { font-size: 9px; color: #43586b; font-weight: bold; }
        .notes { border: 1px solid #e0e6eb; padding: 10px 12px; margin-top: 14px; page-break-inside: avoid; }
        .note-title { font-size: 8px; font-weight: bold; color: #10212f; margin-bottom: 4px; }
        .signatures { width: 100%; border-collapse: collapse; margin-top: 29px; page-break-inside: avoid; }
        .signatures td { width: 50%; padding-right: 30px; vertical-align: top; }
        .signature-line { border-top: 1px solid #aebbc6; padding-top: 6px; color: #617182; font-size: 8px; }
        .footer { position: fixed; bottom: -26px; left: 0; right: 0; border-top: 1px solid #dce4eb; padding-top: 7px; color: #8996a2; font-size: 7px; }
        .footer-right { float: right; }
    </style>
</head>
<body>
@php
    $mrNo = 'MR-' . str_pad($materialRequirement->id, 4, '0', STR_PAD_LEFT);
    $items = $materialRequirement->items ?? collect();
    $formatQty = static function ($value) {
        return rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');
    };
    $unitLabel = static function ($item) {
        $unit = $item->unit ?? $item->unitMaster ?? null;
        return $unit?->unit_code ?: ($unit?->symbol ?: ($unit?->unit_name ?: ($unit?->name ?: '-')));
    };
    $formatDate = static function ($value) {
        return $value ? \Carbon\Carbon::parse($value)->format('d/m/Y') : '-';
    };
@endphp
<table class="top">
    <tr>
        <td style="width:58%">
            <div class="brand">RAVION VERTEX SYSTEMS PVT LTD</div>
            <div class="brand-sub">RAVION ERP | Construction Operations</div>
        </td>
        <td style="width:42%">
            <div class="document-label">MATERIAL REQUISITION</div>
            <div class="document-number">Reference: {{ $mrNo }}</div>
        </td>
    </tr>
</table>
<div class="accent"></div>
<table class="meta">
    <tr>
        <td><div class="label">Project</div><div class="value">{{ $materialRequirement->project?->project_name ?? '-' }}</div></td>
        <td><div class="label">Project Block</div><div class="value">{{ $materialRequirement->block?->name ?? '-' }}</div></td>
        <td><div class="label">Required Date</div><div class="value">{{ $formatDate($materialRequirement->required_date) }}</div></td>
    </tr>
    <tr>
        <td><div class="label">Requested By</div><div class="value">{{ $materialRequirement->creator?->name ?? '-' }}</div></td>
        <td><div class="label">Status / Priority</div><div class="value">{{ $materialRequirement->status ?? '-' }} / {{ $materialRequirement->priority ?? '-' }}</div></td>
        <td><div class="label">Request Reference</div><div class="value">{{ $mrNo }}@if($materialRequirement->dpr_id) | DPR #{{ $materialRequirement->dpr_id }}@endif</div></td>
    </tr>
</table>
<table class="section-head"><tr>
    <td class="section-title">Materials Required</td>
    <td class="section-count">{{ $items->count() }} {{ $items->count() === 1 ? 'item' : 'items' }}</td>
</tr></table>
<table class="items">
    <thead><tr>
        <th class="serial">S.No</th>
        <th class="material">Material / Specification</th>
        <th class="quantity" style="text-align:right">Required Quantity</th>
    </tr></thead>
    <tbody>
    @forelse($items as $index => $item)
        @php
            $variant = trim((string) ($item->variant?->variant_name ?? ''));
            $masterSpec = trim((string) ($item->specification?->specification_name ?? ''));
            $customSpec = trim((string) ($item->specification_text ?? ''));
            $specs = [];
            foreach ([$variant, $masterSpec, $customSpec] as $part) {
                if ($part !== '' && !collect($specs)->contains(fn ($existing) => strcasecmp($existing, $part) === 0)) $specs[] = $part;
            }
            $details = [];
            if ($item->grade?->grade_name) $details[] = 'Grade: ' . $item->grade->grade_name;
            if ($item->brand?->brand_name) $details[] = 'Brand: ' . $item->brand->brand_name;
        @endphp
        <tr>
            <td class="serial">{{ $index + 1 }}</td>
            <td class="material">
                <div class="material-name">{{ $item->materialType?->material_type_name ?? $item->material?->material_name ?? '-' }}</div>
                @if(count($specs))<div class="material-spec">{{ implode(' | ', $specs) }}</div>@endif
                @if(count($details))<div class="material-detail">{{ implode('  |  ', $details) }}</div>@endif
                @if(trim((string) $item->remarks) !== '')<div class="material-detail">Remarks: {{ $item->remarks }}</div>@endif
            </td>
            <td class="quantity"><span class="quantity-number">{{ $formatQty($item->required_quantity) }}</span> <span class="quantity-unit">{{ $unitLabel($item) }}</span></td>
        </tr>
    @empty
        <tr><td colspan="3" style="text-align:center;color:#718092">No material items recorded.</td></tr>
    @endforelse
    </tbody>
</table>
@if(trim((string) ($materialRequirement->remarks ?? '')) !== '')
<div class="notes"><div class="note-title">General Remarks</div>{{ $materialRequirement->remarks }}</div>
@endif
<table class="signatures"><tr>
    <td><div class="signature-line">Requested by: {{ $materialRequirement->creator?->name ?? '-' }}</div></td>
    <td><div class="signature-line">@if($materialRequirement->approver && $materialRequirement->approved_at)Approved by: {{ $materialRequirement->approver->name }} ({{ $formatDate($materialRequirement->approved_at) }})@else Approval: {{ $materialRequirement->status ?? 'Pending' }} @endif</div></td>
</tr></table>
<div class="footer">Generated by Ravion ERP | {{ $mrNo }} <span class="footer-right">Internal material requirement document</span></div>
</body>
</html>
