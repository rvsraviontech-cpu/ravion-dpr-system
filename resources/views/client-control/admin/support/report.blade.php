<!doctype html><html><head><meta charset="utf-8"><style>
@page { margin: 32px 25px 42px; } body{font-family:DejaVu Sans,sans-serif;font-size:9px;color:#172d49} h1{font-size:19px;margin:8px 0} h2{font-size:12px;margin-top:18px} .brand{font-size:19px;font-weight:bold;color:#204b75;border-bottom:2px solid #204b75;padding-bottom:8px} .meta{color:#526579;margin-bottom:12px} table{width:100%;border-collapse:collapse;table-layout:fixed} th,td{border:1px solid #d4dce5;padding:5px;vertical-align:top;word-wrap:break-word} th{background:#204b75;color:white;text-align:left} thead{display:table-header-group} td{white-space:pre-wrap} .register{font-size:7px} .footer{position:fixed;bottom:-25px;left:0;right:0;border-top:1px solid #cbd5e1;padding-top:5px;font-size:8px}
</style></head><body><div class="brand">RAVION | HOMES</div><h1>{{ $title }}</h1><div class="meta">Ravion Vertex Systems Pvt. Ltd. · Generated {{ $generated }} · {{ $by }}</div>
@foreach($sections as $section)
<h2>{{ $section['title'] }}</h2>
<table class="{{ $landscape?'register':'' }}"><thead><tr>
@foreach($section['headers'] as $header)
<th>{{ $header }}</th>
@endforeach
</tr></thead><tbody>
@forelse($section['rows'] as $row)
<tr>
@foreach($row as $value)
<td>{{ $value ?? '—' }}</td>
@endforeach
</tr>
@empty
<tr><td colspan="{{ count($section['headers']) }}">No records found.</td></tr>
@endforelse
</tbody></table>
@endforeach
<div class="footer">Confidential · Internal Management Report</div></body></html>