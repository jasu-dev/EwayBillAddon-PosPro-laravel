<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>E-Way Bill Invoice</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            padding: 10px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header-logo {
            font-size: 20px;
            font-weight: bold;
            color: #0056b3;
        }
        .header-address {
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            margin-bottom: 15px;
            color: #111;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 4px;
            vertical-align: top;
        }
        .bold-title {
            font-weight: bold;
            width: 30%;
        }
        .address-box-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .address-box {
            width: 48%;
            border: 1px solid #ddd;
            padding: 8px;
            background-color: #f9f9f9;
            border-radius: 4px;
            vertical-align: top;
        }
        .address-title {
            font-weight: bold;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
            margin-bottom: 5px;
            color: #0056b3;
            font-size: 11px;
        }
        .transport-box {
            width: 100%;
            border: 1px solid #ddd;
            padding: 8px;
            background-color: #f9f9f9;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .transport-title {
            font-weight: bold;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
            margin-bottom: 5px;
            color: #0056b3;
            font-size: 11px;
        }
        .transport-grid {
            width: 100%;
        }
        .transport-grid td {
            padding: 3px 6px;
            font-size: 10px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th {
            background-color: #0056b3;
            color: white;
            font-weight: bold;
            padding: 6px;
            text-align: center;
            border: 1px solid #0056b3;
            font-size: 10px;
        }
        .items-table td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 4px;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        .totals-label {
            font-weight: bold;
            background-color: #f9f9f9;
            width: 50%;
        }
        .grand-total-row {
            background-color: #0056b3;
            color: white;
            font-weight: bold;
        }
        .grand-total-row td {
            border: 1px solid #0056b3;
        }
        .signatures {
            width: 100%;
            margin-top: 50px;
        }
        .signature-box {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
        }
        .signature-line {
            border-top: 1px solid #999;
            width: 60%;
            margin: 0 auto 5px auto;
        }
        .footer-text {
            text-align: center;
            font-size: 9px;
            color: #777;
            margin-top: 50px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td class="header-logo" valign="middle">
                    {{ $eway_bill->business->companyName ?? '' }}
                </td>
                <td class="header-address">
                    <strong>{{ $eway_bill->business->companyName ?? '' }}</strong><br>
                    {{ $eway_bill->business->address ?? '' }}<br>
                    {{ __('Mobile') }}: {{ $eway_bill->business->phoneNumber ?? '' }} | {{ __('Email') }}: {{ $eway_bill->business->email ?? '' }}<br>
                    @if($eway_bill->business->vat_no)
                        {{ $eway_bill->business->vat_name ?: 'GSTIN' }}: {{ $eway_bill->business->vat_no }}
                    @endif
                </td>
            </tr>
        </table>

        <div class="title">{{ __('E-Way Bill Invoice') }}</div>

        <!-- Doc info -->
        <table class="info-table">
            <tr>
                <td style="width: 50%;">
                    <table style="width: 100%;">
                        <tr>
                            <td class="bold-title">{{ __('Invoice No') }}</td>
                            <td>: {{ $eway_bill->invoice_number }}</td>
                        </tr>
                        <tr>
                            <td class="bold-title">{{ __('Invoice Date') }}</td>
                            <td>: {{ formatted_date($eway_bill->invoice_date) }}</td>
                        </tr>
                        <tr>
                            <td class="bold-title">{{ __('Generated By') }}</td>
                            <td>: {{ $eway_bill->user->name }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%;">
                    <table style="width: 100%;">
                        <tr>
                            <td class="bold-title">{{ __('Supply Type') }}</td>
                            <td>: {{ $eway_bill->supply_type }} - {{ $eway_bill->sub_type }}</td>
                        </tr>
                        <tr>
                            <td class="bold-title">{{ __('Doc Type') }}</td>
                            <td>: {{ $eway_bill->document_type }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Consignor / Consignee addresses -->
        <table class="address-box-table" style="width: 100%;">
            <tr>
                <td class="address-box">
                    <div class="address-title">{{ __('From (Consignor)') }}</div>
                    <strong>{{ $eway_bill->from_name }}</strong><br>
                    @if($eway_bill->from_gstin)
                        {{ __('GSTIN') }}: {{ $eway_bill->from_gstin }}<br>
                    @endif
                    {{ __('Address') }}: {{ $eway_bill->from_address }}<br>
                    {{ __('PIN') }}: {{ $eway_bill->from_pincode }} | {{ __('State') }}: {{ $eway_bill->from_state }}
                </td>
                <td style="width: 4%;"></td>
                <td class="address-box">
                    <div class="address-title">{{ __('To (Consignee)') }}</div>
                    <strong>{{ $eway_bill->to_name }}</strong><br>
                    @if($eway_bill->to_gstin)
                        {{ __('GSTIN') }}: {{ $eway_bill->to_gstin }}<br>
                    @endif
                    {{ __('Address') }}: {{ $eway_bill->to_address }}<br>
                    {{ __('PIN') }}: {{ $eway_bill->to_pincode }} | {{ __('State') }}: {{ $eway_bill->to_state }}
                </td>
            </tr>
        </table>

        <!-- Transporter / Vehicle Details -->
        <div class="transport-box">
            <div class="transport-title">{{ __('Transportation & Vehicle details') }}</div>
            <table class="transport-grid">
                <tr>
                    <td><strong>{{ __('Transporter') }}:</strong> {{ $eway_bill->transporter_name ?: __('N/A') }}</td>
                    <td><strong>{{ __('Transporter ID') }}:</strong> {{ $eway_bill->transporter_id ?: __('N/A') }}</td>
                    <td><strong>{{ __('Transport Mode') }}:</strong> {{ $eway_bill->transport_mode }}</td>
                </tr>
                <tr>
                    <td><strong>{{ __('Distance') }}:</strong> {{ $eway_bill->distance }} KM</td>
                    <td><strong>{{ __('Vehicle Number') }}:</strong> {{ $eway_bill->vehicle_number ?: __('N/A') }}</td>
                    <td><strong>{{ __('Vehicle Type') }}:</strong> {{ $eway_bill->vehicle_type }}</td>
                </tr>
            </table>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 30px;">{{ __('SL') }}</th>
                    <th style="text-align: left;">{{ __('Item Description') }}</th>
                    <th>{{ __('HSN Code') }}</th>
                    <th>{{ __('Qty') }}</th>
                    <th style="text-align: right;">{{ __('Unit Price') }}</th>
                    <th style="text-align: right;">{{ __('Discount') }}</th>
                    <th>{{ __('Tax Rate') }}</th>
                    <th style="text-align: right;">{{ __('Tax Amount') }}</th>
                    <th style="text-align: right;">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($eway_bill->details as $detail)
                    <tr>
                        <td style="text-align: center;">{{ $loop->iteration }}</td>
                        <td>
                            {{ $detail->product->productName ?? '' }}
                            @if($detail->stock && $detail->stock->batch_no)
                                <span style="font-size: 8px; color:#777;">({{ $detail->stock->batch_no }})</span>
                            @endif
                        </td>
                        <td style="text-align: center;">{{ $detail->hsn_code ?: __('N/A') }}</td>
                        <td style="text-align: center;">{{ $detail->quantity }}</td>
                        <td style="text-align: right;">{{ currency_format($detail->price, currency: business_currency()) }}</td>
                        <td style="text-align: right;">{{ currency_format($detail->discount, currency: business_currency()) }}</td>
                        <td style="text-align: center;">{{ $detail->tax_percent }}%</td>
                        <td style="text-align: right;">{{ currency_format($detail->tax_amount, currency: business_currency()) }}</td>
                        <td style="text-align: right;">{{ currency_format($detail->total, currency: business_currency()) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary & Totals -->
        <table style="width: 100%;">
            <tr>
                <td style="width: 55%; vertical-align: top;">
                    @if($eway_bill->notes)
                        <div style="border: 1px solid #eee; padding: 6px; border-radius: 4px; background-color: #fafafa; font-size: 9px; margin-bottom: 10px;">
                            <strong>{{ __('Notes/Remarks') }}:</strong><br>
                            {{ $eway_bill->notes }}
                        </div>
                    @endif
                    <div style="font-size: 10px; color:#555;">
                        <strong>{{ __('Amount in Words') }}:</strong><br>
                        <span style="color:#0056b3; font-weight: bold;">{{ amountInWords($eway_bill->total_amount) }}</span>
                    </div>
                </td>
                <td style="width: 55%; vertical-align: top;">
                    <table class="totals-table">
                        <tr>
                            <td class="totals-label">{{ __('Subtotal') }}</td>
                            <td style="text-align: right;">{{ currency_format($eway_bill->sub_total, currency: business_currency()) }}</td>
                        </tr>
                        <tr>
                            <td class="totals-label">{{ __('Discount') }}</td>
                            <td style="text-align: right; color: red;">{{ currency_format($eway_bill->discount_amount, currency: business_currency()) }}</td>
                        </tr>
                        <tr>
                            <td class="totals-label">{{ __('Taxes') }}</td>
                            <td style="text-align: right;">{{ currency_format($eway_bill->tax_amount, currency: business_currency()) }}</td>
                        </tr>
                        <tr>
                            <td class="totals-label">{{ __('Freight/Shipping') }}</td>
                            <td style="text-align: right;">{{ currency_format($eway_bill->shipping_charge, currency: business_currency()) }}</td>
                        </tr>
                        <tr class="grand-total-row">
                            <td style="font-weight: bold; text-transform: uppercase;">{{ __('Total Value') }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ currency_format($eway_bill->total_amount, currency: business_currency()) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Signatures -->
        <table class="signatures">
            <tr>
                <td class="signature-box">
                    <div class="signature-line"></div>
                    <div style="font-size: 10px; font-weight: bold;">{{ __('Customer Signature') }}</div>
                </td>
                <td class="signature-box">
                    <div class="signature-line"></div>
                    <div style="font-size: 10px; font-weight: bold;">{{ __('Authorized Signature') }}</div>
                    <div style="font-size: 8px; color: #555;">{{ $eway_bill->business->companyName }}</div>
                </td>
            </tr>
        </table>

        <div class="footer-text">
            {{ get_option('general')['admin_footer_text'] ?? '' }} : {{ get_option('general')['admin_footer_link_text'] ?? '' }}
        </div>
    </div>
</body>
</html>
