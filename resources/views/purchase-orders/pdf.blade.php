<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $purchaseOrder->po_number }}</title>

    <style>
        @page { margin: 26px 28px 34px 28px; }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9px;
            line-height: 1.35;
            color: #1f2937;
        }

        .company {
            font-size: 16px;
            font-weight: 700;
            color: #10212F;
        }

        .document-title {
            margin-top: 3px;
            font-size: 13px;
            font-weight: 700;
        }

        .muted { color: #6b7280; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td { vertical-align: top; }

        .header-right { text-align: right; }

        .rule {
            border-top: 2px solid #10212F;
            margin: 10px 0 12px 0;
        }

        .info-table {
            margin-bottom: 13px;
        }

        .info-table td {
            width: 50%;
            border: 1px solid #d1d5db;
            padding: 7px 8px;
            vertical-align: top;
        }

        .label {
            display: block;
            margin-bottom: 2px;
            font-size: 7px;
            color: #6b7280;
            text-transform: uppercase;
        }

        .value {
            font-size: 9px;
            font-weight: 600;
        }

        .section-title {
            margin: 0 0 6px 0;
            font-size: 10px;
            font-weight: 700;
        }

        .items-table {
            table-layout: fixed;
        }

        .items-table th {
            border: 1px solid #10212F;
            background: #10212F;
            color: #ffffff;
            padding: 7px 5px;
            font-size: 7px;
            text-transform: uppercase;
            text-align: left;
        }

        .items-table td {
            border: 1px solid #d1d5db;
            padding: 7px 5px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .right { text-align: right; }
        .center { text-align: center; }
        .nowrap { white-space: nowrap; }

        .col-num { width: 5%; }
        .col-product { width: 27%; }
        .col-spec { width: 17%; }
        .col-brand { width: 13%; }
        .col-qty { width: 8%; }
        .col-unit { width: 9%; }
        .col-remarks { width: 21%; }

        .terms-table {
            margin-top: 14px;
        }

        .terms-table td {
            width: 50%;
            border: 1px solid #d1d5db;
            padding: 7px 8px;
            vertical-align: top;
        }

        .term-heading {
            margin-bottom: 4px;
            font-size: 8px;
            font-weight: 700;
            color: #10212F;
        }

        .footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -18px;
            text-align: center;
            color: #9ca3af;
            font-size: 7px;
        }
    </style>
</head>

<body>
@php
    $qty = function ($value) {
        return rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');
    };

    $paymentTerms = trim((string) ($purchaseOrder->payment_terms ?? ''));

    // Do not print meaningless default terms such as "0 days".
    $showPaymentTerms = $paymentTerms !== ''
        && !in_array(strtolower($paymentTerms), ['0', '0 day', '0 days'], true);
@endphp

<table class="header-table">
    <tr>
        <td>
            <div class="company">RAVION VERTEX SYSTEMS PVT LTD</div>
            <div class="document-title">PURCHASE ORDER</div>
        </td>

        <td class="header-right">
            <div><strong>{{ $purchaseOrder->po_number }}</strong></div>
            <div class="muted">
                PO Date:
                {{ $purchaseOrder->po_date ? $purchaseOrder->po_date->format('d/m/Y') : '-' }}
            </div>
        </td>
    </tr>
</table>

<div class="rule"></div>

<table class="info-table">
    <tr>
        <td>
            <span class="label">Vendor</span>
            <span class="value">{{ $purchaseOrder->vendor_name }}</span>

            @if ($purchaseOrder->vendor_gst_number)
                <br>GST: {{ $purchaseOrder->vendor_gst_number }}
            @endif

            @if ($purchaseOrder->vendor_address)
                <br>{{ $purchaseOrder->vendor_address }}
            @endif

            @if ($purchaseOrder->vendor_mobile)
                <br>Phone: {{ $purchaseOrder->vendor_mobile }}
            @endif

            @if ($purchaseOrder->vendor_email)
                <br>{{ $purchaseOrder->vendor_email }}
            @endif
        </td>

        <td>
            <span class="label">Deliver To / Project</span>
            <span class="value">{{ $purchaseOrder->project_name }}</span>

            @if ($purchaseOrder->delivery_address)
                <br>{{ $purchaseOrder->delivery_address }}
            @endif

            @if ($purchaseOrder->expected_delivery_date)
                <br>
                <span class="muted">Expected Delivery:</span>
                {{ $purchaseOrder->expected_delivery_date->format('d/m/Y') }}
            @endif

            @if ($purchaseOrder->vendor_reference)
                <br>
                <span class="muted">Reference:</span>
                {{ $purchaseOrder->vendor_reference }}
            @endif
        </td>
    </tr>
</table>

<div class="section-title">Ordered Material List</div>

<table class="items-table">
    <thead>
        <tr>
            <th class="col-num center">#</th>
            <th class="col-product">Product</th>
            <th class="col-spec">Specification / Size</th>
            <th class="col-brand">Brand</th>
            <th class="col-qty right">Qty</th>
            <th class="col-unit">Unit</th>
            <th class="col-remarks">Remarks</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($purchaseOrder->items as $index => $item)
            <tr>
                <td class="center">{{ $index + 1 }}</td>

                <td>
                    <strong>{{ $item->product_name }}</strong>
                </td>

                <td>{{ $item->specification_text ?: '-' }}</td>

                <td>{{ $item->brand_name ?: '-' }}</td>

                <td class="right nowrap">
                    {{ $qty($item->ordered_quantity) }}
                </td>

                <td>
                    {{ $item->unit_code ?: $item->unit_name ?: '-' }}
                </td>

                <td>
                    {{ $item->remarks ?: '-' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@if (
    $showPaymentTerms
    || $purchaseOrder->delivery_terms
    || $purchaseOrder->freight_terms
    || $purchaseOrder->vendor_notes
    || $purchaseOrder->terms_conditions
)
    <table class="terms-table">
        <tr>
            <td>
                <div class="term-heading">Delivery / Order Terms</div>

                @if ($showPaymentTerms)
                    <div>
                        <strong>Payment:</strong>
                        {{ $paymentTerms }}
                    </div>
                @endif

                @if ($purchaseOrder->delivery_terms)
                    <div>
                        <strong>Delivery:</strong>
                        {{ $purchaseOrder->delivery_terms }}
                    </div>
                @endif

                @if ($purchaseOrder->freight_terms)
                    <div>
                        <strong>Freight / Transport:</strong>
                        {{ $purchaseOrder->freight_terms }}
                    </div>
                @endif
            </td>

            <td>
                <div class="term-heading">Notes / Instructions</div>

                @if ($purchaseOrder->vendor_notes)
                    <div>{!! nl2br(e($purchaseOrder->vendor_notes)) !!}</div>
                @endif

                @if ($purchaseOrder->terms_conditions)
                    <div>{!! nl2br(e($purchaseOrder->terms_conditions)) !!}</div>
                @endif
            </td>
        </tr>
    </table>
@endif

<div class="footer">
    {{ $purchaseOrder->po_number }} · Generated by Ravion ERP
</div>

</body>
</html>
