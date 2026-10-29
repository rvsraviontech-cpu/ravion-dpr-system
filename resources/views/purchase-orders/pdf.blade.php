<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Purchase Order {{ $purchaseOrder->po_number }}</title>
<style>
@page { margin: 25px 27px 35px; }
* { box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; color:#172b3b; font-size:8px; line-height:1.42; }
table { border-collapse:collapse; width:100%; }
.header td { vertical-align:middle; }
.logo { width:125px; height:auto; display:block; }
.doc-title { font-size:18px; font-weight:bold; color:#10212F; letter-spacing:1px; }
.doc-sub { color:#637485; font-size:8px; }
.gold { border-top:3px solid #E1A00E; margin:9px 0 12px; }
.info { margin-bottom:12px; }
.info td { width:50%; border:1px solid #d5dee6; padding:9px; vertical-align:top; }
.info .head { color:#10212F; font-weight:bold; text-transform:uppercase; font-size:8px; margin-bottom:5px; }
.small { font-size:7px; color:#637485; }
.value { font-weight:bold; }
.meta { margin:0 0 12px; }
.meta th { text-align:left; color:#5b6d7d; font-weight:normal; font-size:7px; background:#f2f5f7; padding:6px; border:1px solid #d5dee6; }
.meta td { font-weight:bold; padding:6px; border:1px solid #d5dee6; }
.section { font-size:10px; font-weight:bold; color:#10212F; margin:12px 0 6px; }
.items { table-layout:fixed; }
.items thead { display:table-header-group; }
.items tr { page-break-inside:avoid; }
.items th { background:#10212F; color:#fff; text-align:left; padding:7px 4px; font-size:6.7px; border:1px solid #10212F; }
.items td { border:1px solid #d5dee6; padding:7px 4px; vertical-align:top; overflow-wrap:break-word; word-wrap:break-word; }
.items tbody tr:nth-child(even) { background:#f5f8fa; }
.right { text-align:right !important; } .center { text-align:center !important; }
.product { font-weight:bold; }
.remarks { color:#526677; font-size:7px; }



.terms { margin-top:13px; }
.terms td { border:1px solid #d5dee6; padding:9px; vertical-align:top; width:50%; }
.terms .head { font-size:8px; font-weight:bold; color:#10212F; margin-bottom:5px; }
.sign { margin-top:18px; width:100%; }
.sign td { width:50%; vertical-align:bottom; }
.sign .signline { width:190px; padding-top:5px; font-size:9px; }
.footer { position:fixed; bottom:-23px; left:0; right:0; border-top:1px solid #d5dee6; padding-top:5px; text-align:center; color:#7d8d9a; font-size:7px; }
.cancelled { color:#9b1c1c; font-size:14px; font-weight:bold; border:2px solid #9b1c1c; padding:5px 12px; display:inline-block; }
</style>
</head>
<body>
@php
    // Use the original Ravion brand artwork, not a text recreation.
    $logoPath = public_path('images/ravion-po-logo.png');
    $logoData = is_file($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : null;
    $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.');
    $spec = function ($item) {
        $parts = array_filter([
            trim((string) ($item->materialVariant?->variant_name ?? '')),
            trim((string) ($item->materialVariant?->size_dimension ?? '')),
            trim((string) ($item->specification_text ?? '')),
        ], fn ($v) => $v !== '');
        return collect($parts)->unique()->implode(' / ') ?: '-';
    };
    // Optional contact fields will be populated by the PO form/controller once enabled.
    $siteContactName = trim((string) ($purchaseOrder->delivery_contact_name ?? ''));
    $siteContactPhone = trim((string) ($purchaseOrder->delivery_contact_phone ?? ''));
@endphp
<table class="header"><tr>
<td style="width:53%">
@if($logoData)
<img class="logo" src="{{ $logoData }}" alt="Ravion original logo">
@else
<div style="color:#9b1c1c;font-size:8px;font-weight:bold">Ravion logo asset missing: public/images/ravion-po-logo.png</div>
@endif
<div style="font-size:8px;font-weight:bold;margin-top:3px">RAVION VERTEX SYSTEMS PRIVATE LIMITED</div>
</td>
<td style="width:47%;text-align:right">
<div class="doc-title">PURCHASE ORDER</div>
<div style="font-size:10px;font-weight:bold">{{ $purchaseOrder->po_number }}</div>
<div class="doc-sub">PO Date: {{ $purchaseOrder->po_date?->format('d/m/Y') ?? '-' }}</div>
@if($purchaseOrder->status === \App\Models\PurchaseOrder::STATUS_CANCELLED)<div class="cancelled">CANCELLED</div>@endif
</td></tr></table>
<div class="gold"></div>
<table class="info"><tr>
<td><div class="head">Supplier / Vendor</div>
<div class="value">{{ $purchaseOrder->vendor_name }}</div>
@if($purchaseOrder->vendor_address)<div>{{ $purchaseOrder->vendor_address }}</div>@endif
@if($purchaseOrder->vendor_gst_number)<div>GSTIN: {{ $purchaseOrder->vendor_gst_number }}</div>@endif
@if($purchaseOrder->vendor_contact_person)<div>Contact: {{ $purchaseOrder->vendor_contact_person }}</div>@endif
@if($purchaseOrder->vendor_mobile)<div>Phone: {{ $purchaseOrder->vendor_mobile }}</div>@endif
@if($purchaseOrder->vendor_email)<div>{{ $purchaseOrder->vendor_email }}</div>@endif
</td>
<td><div class="head">Deliver To / Project Site</div>
<div class="value">{{ $purchaseOrder->project_name }}</div>
@if($purchaseOrder->delivery_address)<div>{{ $purchaseOrder->delivery_address }}</div>@endif
@if($purchaseOrder->expected_delivery_date)<div>Expected Delivery: <b>{{ $purchaseOrder->expected_delivery_date->format('d/m/Y') }}</b></div>@endif
@if($siteContactName || $siteContactPhone)<div style="margin-top:6px"><b>Site Delivery Contact:</b> {{ $siteContactName ?: 'Site Contact' }}@if($siteContactPhone) &nbsp; | &nbsp; {{ $siteContactPhone }}@endif</div>@endif
@if($purchaseOrder->vendor_reference)<div>Vendor Reference: {{ $purchaseOrder->vendor_reference }}</div>@endif
</td></tr></table>
<div class="section">Material Schedule</div>
<table class="items">
<thead><tr>
<th style="width:5%" class="center">#</th>
<th style="width:31%">Material / Item Description</th>
<th style="width:29%">Variant / Size / Specification</th>
<th style="width:13%">Brand</th>
<th style="width:10%" class="right">Quantity</th>
<th style="width:12%">Unit</th>
</tr></thead>
<tbody>
@foreach($purchaseOrder->items as $i => $item)
<tr>
<td class="center">{{ $i + 1 }}</td>
<td><div class="product">{{ $item->product_name }}</div>@if($item->product_code)<span class="small">{{ $item->product_code }}</span>@endif @if($item->remarks)<div class="remarks">{{ $item->remarks }}</div>@endif</td>
<td>{{ $spec($item) }}</td>
<td>{{ $item->brand_name ?: '-' }}</td>
<td class="right"><b>{{ $qty($item->ordered_quantity) }}</b></td>
<td>{{ $item->unit_code ?: ($item->unit_name ?: '-') }}</td>
</tr>
@endforeach
</tbody></table>
@if($purchaseOrder->delivery_terms || $purchaseOrder->freight_terms || $purchaseOrder->vendor_notes || $purchaseOrder->terms_conditions)
<table class="terms"><tr><td>
<div class="head">Delivery Instructions</div>
@if($purchaseOrder->delivery_terms)<div><b>Delivery:</b> {{ $purchaseOrder->delivery_terms }}</div>@endif
@if($purchaseOrder->freight_terms)<div><b>Freight:</b> {{ $purchaseOrder->freight_terms }}</div>@endif
</td><td><div class="head">Instructions to Vendor</div>
@if($purchaseOrder->vendor_notes)<div>{!! nl2br(e($purchaseOrder->vendor_notes)) !!}</div>@endif
@if($purchaseOrder->terms_conditions)<div>{!! nl2br(e($purchaseOrder->terms_conditions)) !!}</div>@endif
</td></tr></table>
@endif
<table class="sign"><tr><td><span class="small">Please quote this PO number on delivery challans and dispatch communications.</span></td><td style="text-align:right"><div style="display:inline-block;text-align:center"><div class="signline">Issued electronically by<br><b>Ravion Procurement Team</b><br><span class="small">No physical signature required</span></div></div></td></tr></table>
<div class="footer">{{ $purchaseOrder->po_number }} &nbsp; | &nbsp; Ravion ERP &nbsp; | &nbsp; System-generated Purchase Order</div>
</body></html>
