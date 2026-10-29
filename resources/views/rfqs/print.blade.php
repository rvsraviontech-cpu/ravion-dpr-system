<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Blank RFQ - {{ $rfq->rfq_number }} - {{ $vendor->vendor_name }}</title>
<style>
@page { size: A4 landscape; margin: 11mm 10mm; }
* { box-sizing:border-box; }
body { margin:0; color:#15283b; background:#eef2f7; font:12px Arial,Helvetica,sans-serif; }
.sheet { max-width:1120px; margin:20px auto; background:#fff; padding:24px; box-shadow:0 2px 16px #0001; }
.toolbar { max-width:1120px; margin:20px auto 0; display:flex; gap:12px; align-items:center; justify-content:space-between; }
button { background:#13283b; color:#fff; border:0; border-radius:6px; padding:11px 18px; cursor:pointer; font-weight:bold; }
.note { color:#52647b; font-size:12px; }
.head { display:grid; grid-template-columns:1fr 2fr 1fr; border:1px solid #d5dce5; align-items:center; min-height:65px; }
.brand { text-align:center; font-size:23px; font-weight:800; letter-spacing:.6px; }
.brand span { border-bottom:4px solid #e0a600; }
.heading { text-align:center; border-left:1px solid #d5dce5; border-right:1px solid #d5dce5; padding:10px; }
.heading small { font-size:9px; color:#6a7e95; letter-spacing:1px; }
.heading strong { display:block; margin-top:4px; font-size:15px; }
.ref { text-align:center; font-size:11px; }
.ref small { display:block; color:#6a7e95; font-size:9px; }
.ref strong { font-size:13px; }
.details { display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; margin:13px 0; }
.details div { border-bottom:1px solid #d5dce5; padding:5px 0 9px; }
.label { display:block; text-transform:uppercase; letter-spacing:.5px; color:#64748b; font-size:9px; margin-bottom:4px; }
h3 { font-size:13px; margin:18px 0 9px; }
table { border-collapse:collapse; width:100%; table-layout:fixed; }
th,td { border:1px solid #cfd8e3; padding:8px 5px; vertical-align:middle; }
th { background:#f1f5f9; font-size:9px; line-height:1.35; text-align:center; }
td { font-size:10px; height:35px; }
td.center { text-align:center; }
td.right { text-align:right; }
td.blank { background:#fff; }
.small { color:#62748a; font-size:9px; margin-top:3px; }
.lower { display:grid; grid-template-columns:1fr 280px; gap:20px; margin-top:16px; align-items:start; }
.charges td { height:29px; padding:6px 9px; }
.totals td { height:29px; padding:6px 9px; }
.totals tr:last-child { font-weight:bold; background:#f1f5f9; }
.footer { text-align:center; color:#64748b; font-size:9px; margin-top:25px; border-top:1px solid #d5dce5; padding-top:8px; }
@media print {
 body { background:white; }
 .toolbar { display:none; }
 .sheet { margin:0; padding:0; max-width:none; box-shadow:none; }
 thead { display:table-header-group; }
 tr { break-inside:avoid; }
 .lower { break-inside:avoid; }
}
@media screen and (max-width:750px) {
 .sheet { min-width:980px; }
 body { overflow-x:auto; }
}
</style>
</head>
<body>
<div class="toolbar">
 <span class="note">Blank vendor quotation form — use Print → Save as PDF, or print on paper.</span>
 <button type="button" onclick="window.print()">Print / Save as PDF</button>
</div>
<div class="sheet">
 <div class="head">
  <div class="brand"><span>RAVION</span></div>
  <div class="heading"><small>VENDOR PROCUREMENT PORTAL</small><strong>Request for Quotation</strong></div>
  <div class="ref"><small>RFQ REFERENCE</small><strong>{{ $rfq->rfq_number }}</strong></div>
 </div>
 <div class="details">
  <div><span class="label">Project</span><strong>{{ $project->project_name ?? '—' }}</strong></div>
  <div><span class="label">Vendor</span><strong>{{ $vendor->vendor_name }}</strong></div>
  <div><span class="label">Submission deadline</span><strong>{{ $rfq->submission_deadline ? \Illuminate\Support\Carbon::parse($rfq->submission_deadline)->format('d/m/Y h:i A') : '—' }}</strong></div>
 </div>
 <h3>Quotation line items</h3>
 <table>
  <colgroup>
   <col style="width:4%"><col style="width:17%"><col style="width:9%">
   <col style="width:10%"><col style="width:8%"><col style="width:9%">
   <col style="width:10%"><col style="width:10%"><col style="width:6%">
   <col style="width:9%"><col style="width:8%">
  </colgroup>
  <thead><tr>
   <th>Sl.No</th><th>Material Name</th><th>Requested Quantity</th>
   <th>MRP Rate Per Unit</th><th>Discount %</th><th>Discount Amount</th>
   <th>Net Rate Per Unit</th><th>Taxable Amount</th><th>GST %</th>
   <th>Net Rate Per Unit with GST</th><th>Amount with GST</th>
  </tr></thead>
  <tbody>
  @foreach($items as $index => $item)
   <tr>
    <td class="center">{{ $index + 1 }}</td>
    <td><strong>{{ $item->product_name }}</strong>@if($item->specification_text)<div class="small">{{ $item->specification_text }}</div>@endif</td>
    <td class="center">{{ rtrim(rtrim(number_format((float)$item->requested_quantity, 3, '.', ''), '0'), '.') }} {{ $item->unit_code ?? '' }}</td>
    @for($c = 0; $c < 8; $c++)<td class="blank">&nbsp;</td>@endfor
   </tr>
  @endforeach
  </tbody>
 </table>
 <div class="lower">
  <div>
   <h3>Additional Charges if applicable</h3>
   <table class="charges" style="max-width:420px">
    <tbody>
     <tr><td style="width:30px">1</td><td>Auto Charges (₹)</td><td style="width:120px">&nbsp;</td></tr>
     <tr><td>2</td><td>Hamali Charges (₹)</td><td>&nbsp;</td></tr>
     <tr><td>3</td><td>Loading Charges (₹)</td><td>&nbsp;</td></tr>
     <tr><td>4</td><td>Unloading Charges (₹)</td><td>&nbsp;</td></tr>
    </tbody>
   </table>
  </div>
  <div>
   <table class="totals"><tbody>
    <tr><td>Gross Product Amount</td><td style="width:95px">&nbsp;</td></tr>
    <tr><td>Total Discount Offered</td><td>&nbsp;</td></tr>
    <tr><td>Taxable Product Amount</td><td>&nbsp;</td></tr>
    <tr><td>Total GST</td><td>&nbsp;</td></tr>
    <tr><td>Total Amount with GST</td><td>&nbsp;</td></tr>
    <tr><td>Additional Charges</td><td>&nbsp;</td></tr>
    <tr><td>Net Total</td><td>&nbsp;</td></tr>
   </tbody></table>
  </div>
 </div>
 <div class="footer">Ravion Homes · Procurement Team · +91 98666 07039 · info@ravionhomes.com · ravionhomes.com</div>
</div>
</body>
</html>
