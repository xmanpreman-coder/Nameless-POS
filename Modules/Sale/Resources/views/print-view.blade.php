<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Nota - {{ $sale->reference }}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * {
            font-size: 12px;
            line-height: 18px;
            font-family: 'Ubuntu', sans-serif;
        }
        h2 {
            font-size: 16px;
        }
        td,
        th,
        tr,
        table {
            border-collapse: collapse;
        }
        tr {border-bottom: 1px dashed #ddd;}
        td,th {padding: 7px 0;width: 50%;}

        table {width: 100%;}
        tfoot tr th:first-child {text-align: left;}

        .centered {
            text-align: center;
            align-content: center;
        }
        small{font-size:11px;}

        @media print {
            * {
                font-size:12px;
                line-height: 20px;
            }
            td,th {padding: 5px 0;}
            .hidden-print {
                display: none !important;
            }
            .no-print {
                display: none !important;
            }
            tbody::after {
                content: '';
                display: block;
                page-break-after: always;
                page-break-inside: auto;
                page-break-before: avoid;
            }
        }
        .print-actions {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background: white;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

<div class="print-actions no-print">
    @if(session('error'))
        <div style="background: #fff3cd; color: #856404; padding: 8px; margin-bottom: 10px; border-radius: 3px; border: 1px solid #ffc107;">
            {{ session('error') }}
        </div>
    @endif
    <button onclick="window.print()" style="padding: 8px 15px; margin-right: 5px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer;">
        <i class="bi bi-printer"></i> Print
    </button>
    <button onclick="window.close()" style="padding: 8px 15px; background: #6c757d; color: white; border: none; border-radius: 3px; cursor: pointer;">
        <i class="bi bi-x"></i> Tutup
    </button>
</div>

<div style="max-width:400px;margin:0 auto">
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
            Name: {{ $sale->customer_name ?? 'Walk-in Customer' }}
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

<script>
    // Auto print saat window load - hanya sekali dengan flag di sessionStorage
    const printKey = 'print_' + window.location.pathname + '_' + (new URLSearchParams(window.location.search).get('id') || '');
    let hasPrinted = false;
    
    // Hanya auto-print jika dibuka dari popup dan belum pernah print
    if (window.opener) {
        const stored = sessionStorage.getItem(printKey);
        if (!stored) {
            sessionStorage.setItem(printKey, 'true');
            hasPrinted = true;
            window.addEventListener('load', function() {
                setTimeout(function() {
                    window.print();
                }, 500);
            });
        }
    }
    
    // Detect saat print dialog ditutup - hanya sekali
    let afterPrintHandled = false;
    window.addEventListener('afterprint', function() {
        if (!afterPrintHandled) {
            afterPrintHandled = true;
            // Clear sessionStorage setelah print
            sessionStorage.removeItem(printKey);
            // Tutup window setelah print selesai (jika dibuka dari popup)
            setTimeout(function() {
                if (window.opener) {
                    window.close();
                }
            }, 1000);
        }
    });
    
    // Prevent duplicate print jika user cancel dan focus kembali
    window.addEventListener('focus', function() {
        if (sessionStorage.getItem(printKey) === 'true') {
            return;
        }
    });
</script>

</body>
</html>

