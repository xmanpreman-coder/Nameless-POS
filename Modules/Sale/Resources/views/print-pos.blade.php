<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', 'Liberation Mono', monospace;
            font-size: 10px;
            line-height: 12px;
            color: #000;
            background: white;
            margin: 0;
            padding: 0;
        }
        
        h2 {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        td, th, tr, table {
            border-collapse: collapse;
        }
        
        tr {
            border-bottom: 1px dashed #000;
        }
        
        td, th {
            padding: 2px 0;
            font-size: 9px;
            line-height: 11px;
        }

        table {
            width: 100%;
            margin-bottom: 4px;
        }
        
        tfoot tr th:first-child {
            text-align: left;
        }

        .centered {
            text-align: center;
            align-content: center;
        }
        
        small {
            font-size: 8px;
        }

        @media print {
            @page {
                size: 80mm auto; /* 80mm width, auto height for thermal paper */
                margin: 0mm; /* No margins for thermal printing */
                padding: 0mm;
            }
            
            * {
                font-size: 9px;
                line-height: 11px;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            body {
                margin: 0;
                padding: 2mm;
                background: white !important;
                -webkit-print-color-adjust: exact;
            }
            
            td, th {
                padding: 1px 0;
                font-size: 8px;
                line-height: 10px;
            }
            
            .hidden-print {
                display: none !important;
            }
            
            /* Remove problematic page break rules */
            tbody::after {
                content: none;
                display: none;
            }
            
            /* Prevent page breaks inside receipt */
            .receipt-container {
                page-break-inside: avoid;
                page-break-after: avoid;
                page-break-before: avoid;
            }
            
            table, tr, td, th {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

<div class="receipt-container" style="width:72mm;max-width:72mm;margin:0 auto;padding:2mm">
    <div id="receipt-data">
        <div class="centered">
            <h2 style="margin-bottom: 5px">{{ settings()->company_name }}</h2>

            <p style="font-size: 11px;line-height: 15px;margin-top: 0">
                {{ settings()->company_email }}, {{ settings()->company_phone }}
                <br>{{ settings()->company_address }}
            </p>
        </div>
        <p>
            Date: {{ \Carbon\Carbon::parse($sale->date)->format('d M, Y') }}<br>
            Reference: {{ $sale->reference }}<br>
            @if($sale->customer_id)
            Name: {{ $sale->customer_name }}
            @endif
        </p>
        <table class="table-data">
            <tbody>
            @foreach($sale->saleDetails as $saleDetail)
                <tr>
                    <td colspan="2">
                        {{ $saleDetail->product->product_name }}
                        ({{ $saleDetail->quantity }} x {{ format_currency($saleDetail->price) }})
                    </td>
                    <td style="text-align:right;vertical-align:bottom">{{ format_currency($saleDetail->sub_total) }}</td>
                </tr>
            @endforeach

            @if($sale->tax_percentage)
                <tr>
                    <th colspan="2" style="text-align:left">Tax ({{ $sale->tax_percentage }}%)</th>
                    <th style="text-align:right">{{ format_currency($sale->tax_amount) }}</th>
                </tr>
            @endif
            @if($sale->discount_percentage)
                <tr>
                    <th colspan="2" style="text-align:left">Discount ({{ $sale->discount_percentage }}%)</th>
                    <th style="text-align:right">{{ format_currency($sale->discount_amount) }}</th>
                </tr>
            @endif
            @if($sale->shipping_amount)
                <tr>
                    <th colspan="2" style="text-align:left">Shipping</th>
                    <th style="text-align:right">{{ format_currency($sale->shipping_amount) }}</th>
                </tr>
            @endif
            <tr>
                <th colspan="2" style="text-align:left">Grand Total</th>
                <th style="text-align:right">{{ format_currency($sale->total_amount) }}</th>
            </tr>
            </tbody>
        </table>
        <table>
            <tbody>
                <tr style="background-color:#ddd;">
                    <td class="centered" style="padding: 5px;">
                        Paid By: {{ $sale->payment_method }}
                    </td>
                    <td class="centered" style="padding: 5px;">
                        Amount: {{ format_currency($sale->paid_amount) }}
                    </td>
                </tr>
                <tr style="border-bottom: 0;">
                    <td class="centered" colspan="3">
                        <div style="margin-top: 10px;">
                            {!! \Milon\Barcode\Facades\DNS1DFacade::getBarcodeSVG($sale->reference, 'C128', 1, 25, 'black', false) !!}
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
