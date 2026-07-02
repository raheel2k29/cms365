<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quote {{ $quote->quote_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            width: 100%;
            margin-bottom: 40px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        .header table {
            width: 100%;
        }
        .company-details {
            font-size: 13px;
            color: #555;
        }
        .quote-title {
            font-size: 32px;
            font-weight: bold;
            color: #1e3a8a;
            text-align: right;
        }
        .customer-info {
            margin-bottom: 40px;
        }
        .customer-info table {
            width: 100%;
        }
        .customer-info th {
            text-align: left;
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .items-table th {
            background-color: #f8fafc;
            color: #475569;
            padding: 12px;
            text-align: left;
            font-size: 13px;
            border-bottom: 2px solid #cbd5e1;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
        }
        .text-right {
            text-align: right !important;
        }
        .totals-table {
            width: 300px;
            float: right;
            border-collapse: collapse;
        }
        .totals-table th, .totals-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        .totals-table .grand-total th, .totals-table .grand-total td {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
            border-bottom: none;
            border-top: 2px solid #1e3a8a;
        }
        .footer {
            position: absolute;
            bottom: 30px;
            width: 100%;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <h2 style="margin:0;color:#1e3a8a">Your Company Name</h2>
                    <div class="company-details">
                        123 Business Road<br>
                        City, State 12345<br>
                        contact@yourcompany.com<br>
                        (555) 123-4567
                    </div>
                </td>
                <td class="quote-title">
                    QUOTE
                    <div style="font-size: 14px; font-weight: normal; color: #64748b; margin-top: 5px;">
                        Date: {{ \Carbon\Carbon::now()->format('F j, Y') }}<br>
                        Quote #: {{ $quote->quote_number }}<br>
                        @if($quote->expires_at)
                        Expires: {{ \Carbon\Carbon::parse($quote->expires_at)->format('F j, Y') }}
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="customer-info">
        <table>
            <tr>
                <td style="width: 50%;">
                    <th>PREPARED FOR:</th>
                    <div>
                        <strong>{{ $quote->company->name ?? 'Customer Name' }}</strong><br>
                        @if($quote->contact)
                            Attention: {{ $quote->contact->name }}<br>
                        @endif
                        @if($quote->project_name)
                            Project: {{ $quote->project_name }}<br>
                        @endif
                        @if($quote->project_address)
                            Address: {{ $quote->project_address }}
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width:10%">Type</th>
                <th class="text-right" style="width:8%">Qty</th>
                <th style="width:15%">Vendor</th>
                <th style="width:37%">Description</th>
                <th style="width:10%">UOM</th>
                <th class="text-right" style="width:10%">Unit Price</th>
                <th class="text-right" style="width:10%">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $item)
            <tr>
                <td>{{ $item->type }}</td>
                <td class="text-right">{{ floatval($item->qty) }}</td>
                <td>{{ $item->vendor ? $item->vendor->name : '' }}</td>
                <td>{{ $item->description }}</td>
                <td>{{ $item->unit }}</td>
                <td class="text-right">${{ number_format($item->sell_price, 2) }}</td>
                <td class="text-right">${{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
            @if($quote->items->isEmpty())
            <tr>
                <td colspan="7" style="text-align:center;color:#94a3b8">No items included in this quote.</td>
            </tr>
            @endif
        </tbody>
    </table>

    <table class="totals-table">
        <tr class="grand-total">
            <th class="text-right">Total:</th>
            <td class="text-right">${{ number_format($quote->total_sell, 2) }}</td>
        </tr>
    </table>

    <div style="clear: both;"></div>

    <div class="footer">
        Thank you for your business! This quote is valid until {{ $quote->expires_at ? \Carbon\Carbon::parse($quote->expires_at)->format('F j, Y') : \Carbon\Carbon::parse($quote->created_at)->addDays(30)->format('F j, Y') }}.
    </div>

</body>
</html>
