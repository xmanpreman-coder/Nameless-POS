<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sale Details</title>
    <link rel="stylesheet" href="{{ public_path('b3/bootstrap.min.css') }}">
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            margin: 0;
            padding: 5px;
            background: white;
        }
        
        .receipt-container {
            width: 280px;
            max-width: 280px;
            margin: 0 auto;
            background: white;
            page-break-inside: avoid;
        }
        
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .company-info {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .invoice-info {
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        .items-table th,
        .items-table td {
            padding: 2px 4px;
            font-size: 11px;
            border: none;
        }
        
        .items-table th {
            border-bottom: 1px solid #000;
            font-weight: bold;
        }
        
        .total-section {
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        
        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        
        .grand-total {
            border-top: 1px solid #000;
            padding-top: 5px;
            font-weight: bold;
            font-size: 14px;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                margin: 0;
                padding: 0;
            }
            
            .receipt-container {
                width: 300px;
                max-width: 300px;
                margin: 0;
            }
            
            @page {
            size: 80mm auto;
            margin: 0;
        }
        }
        
        .btn {
            padding: 8px 15px;
            margin: 5px;
            cursor: pointer;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            font-size: 12px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="no-print" style="text-align: center; margin-bottom: 15px;">
    <button onclick="printSingle()" class="btn">Print</button>
    <button onclick="closeWindow()" class="btn">Close</button>
</div>

<script>
function printSingle() {
    window.print();
}

function closeWindow() {
    window.close();
}

// Handle print dialog events
window.addEventListener('beforeprint', function() {
    console.log('Print dialog opened');
});

window.addEventListener('afterprint', function() {
    console.log('Print dialog closed');
    // Don't auto close - let user decide
});

// Focus window when loaded
window.onload = function() {
    window.focus();
};

// Handle escape key to close
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        window.close();
    }
});
</script>

<div class="receipt-container">
    <!-- Company Info -->
    <div class="company-info">
        <div class="font-bold" style="font-size: 14px;">{{ settings()->company_name }}</div>
        <div>{{ settings()->company_address }}</div>
        <div>Tel: {{ settings()->company_phone }}</div>
        @if(settings()->company_email)
        <div>{{ settings()->company_email }}</div>
        @endif
    </div>

    <!-- Invoice Info -->
    <div class="invoice-info">
        <div class="text-center font-bold" style="font-size: 13px; margin-bottom: 5px;">SALES RECEIPT</div>
        <div style="display: flex; justify-content: space-between;">
            <span>Receipt#:</span>
            <span class="font-bold">{{ $sale->reference }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Date:</span>
            <span>{{ \Carbon\Carbon::parse($sale->date)->format('d/m/Y H:i') }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Customer:</span>
            <span>{{ $customer->customer_name ?? 'Walk-in Customer' }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Cashier:</span>
            <span>{{ $sale->user->name ?? 'System' }}</span>
        </div>
    </div>

    <!-- Items -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-left">Item</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleDetails as $item)
            <tr>
                <td class="text-left">
                    <div>{{ $item->product_name }}</div>
                    <small>{{ $item->product_code }}</small>
                </td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ format_currency($item->unit_price) }}</td>
                <td class="text-right">{{ format_currency($item->sub_total) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="total-section">
        <div class="total-line">
            <span>Subtotal:</span>
            <span>{{ format_currency($sale->total_amount + $sale->discount_amount - $sale->tax_amount - $sale->shipping_amount) }}</span>
        </div>
        
        @if($sale->discount_amount > 0)
        <div class="total-line">
            <span>Discount ({{ $sale->discount_percentage }}%):</span>
            <span>-{{ format_currency($sale->discount_amount) }}</span>
        </div>
        @endif
        
        @if($sale->tax_amount > 0)
        <div class="total-line">
            <span>Tax ({{ $sale->tax_percentage }}%):</span>
            <span>{{ format_currency($sale->tax_amount) }}</span>
        </div>
        @endif
        
        @if($sale->shipping_amount > 0)
        <div class="total-line">
            <span>Shipping:</span>
            <span>{{ format_currency($sale->shipping_amount) }}</span>
        </div>
        @endif
        
        <div class="total-line grand-total">
            <span>TOTAL:</span>
            <span>{{ format_currency($sale->total_amount) }}</span>
        </div>
        
        <div class="total-line">
            <span>Paid:</span>
            <span>{{ format_currency($sale->paid_amount) }}</span>
        </div>
        
        @if($sale->due_amount > 0)
        <div class="total-line">
            <span>Change:</span>
            <span>{{ format_currency($sale->paid_amount - $sale->total_amount) }}</span>
        </div>
        @endif
    </div>

    <!-- Footer -->
    <div style="text-align: center; margin-top: 15px; border-top: 1px dashed #000; padding-top: 10px;">
        <div style="font-size: 10px;">Thank you for your business!</div>
        <div style="font-size: 10px;">{{ settings()->company_name }}</div>
        <div style="font-size: 10px;">{{ date('Y') }}</div>
    </div>
</div>
<script>
    function closeWindow() {
        window.close();
    }
</script>
</body>
</html>