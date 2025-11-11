<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase - {{ $purchase->reference }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
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
    <button onclick="closeWindow()" style="padding: 8px 15px; background: #6c757d; color: white; border: none; border-radius: 3px; cursor: pointer;">
        <i class="bi bi-x"></i> Tutup
    </button>
</div>
<script>
    function closeWindow() {
        if (window.opener) {
            window.close();
        } else {
            window.history.back();
        }
    }
    
    // Prevent double print dengan sessionStorage
    const printKey = 'print_purchase_' + window.location.pathname;
    let hasPrinted = false;
    
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
    
    let afterPrintHandled = false;
    window.addEventListener('afterprint', function() {
        if (!afterPrintHandled) {
            afterPrintHandled = true;
            sessionStorage.removeItem(printKey);
            setTimeout(function() {
                if (window.opener) {
                    window.close();
                }
            }, 1000);
        }
    });
</script>

<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12">
            <div style="text-align: center;margin-bottom: 25px;">
                <h4 style="margin-bottom: 20px;">
                    <span>Reference:</span> <strong>{{ $purchase->reference }}</strong>
                </h4>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-xs-4 mb-3 mb-md-0">
                            <h4 class="mb-2" style="border-bottom: 1px solid #dddddd;padding-bottom: 10px;">Company Info:</h4>
                            <div><strong>{{ settings()->company_name }}</strong></div>
                            <div>{{ settings()->company_address }}</div>
                            <div>Email: {{ settings()->company_email }}</div>
                            <div>Phone: {{ settings()->company_phone }}</div>
                        </div>

                        <div class="col-xs-4 mb-3 mb-md-0">
                            <h4 class="mb-2" style="border-bottom: 1px solid #dddddd;padding-bottom: 10px;">Supplier Info:</h4>
                            <div><strong>{{ $supplier->supplier_name }}</strong></div>
                            <div>{{ $supplier->address }}</div>
                            <div>Email: {{ $supplier->supplier_email }}</div>
                            <div>Phone: {{ $supplier->supplier_phone }}</div>
                        </div>

                        <div class="col-xs-4 mb-3 mb-md-0">
                            <h4 class="mb-2" style="border-bottom: 1px solid #dddddd;padding-bottom: 10px;">Invoice Info:</h4>
                            <div>Invoice: <strong>INV/{{ $purchase->reference }}</strong></div>
                            <div>Date: {{ \Carbon\Carbon::parse($purchase->date)->format('d M, Y') }}</div>
                            <div>Status: <strong>{{ $purchase->status }}</strong></div>
                            <div>Payment Status: <strong>{{ $purchase->payment_status }}</strong></div>
                        </div>
                    </div>

                    <div class="table-responsive-sm" style="margin-top: 30px;">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th class="align-middle">Product</th>
                                <th class="align-middle">Net Unit Price</th>
                                <th class="align-middle">Quantity</th>
                                <th class="align-middle">Discount</th>
                                <th class="align-middle">Tax</th>
                                <th class="align-middle">Sub Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($purchase->purchaseDetails as $item)
                                <tr>
                                    <td class="align-middle">
                                        {{ $item->product_name }} <br>
                                        <span class="badge badge-success">{{ $item->product_code }}</span>
                                    </td>
                                    <td class="align-middle">{{ format_currency($item->unit_price) }}</td>
                                    <td class="align-middle">{{ $item->quantity }}</td>
                                    <td class="align-middle">{{ format_currency($item->product_discount_amount) }}</td>
                                    <td class="align-middle">{{ format_currency($item->product_tax_amount) }}</td>
                                    <td class="align-middle">{{ format_currency($item->sub_total) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-xs-4 col-xs-offset-8">
                            <table class="table">
                                <tbody>
                                <tr>
                                    <td class="left"><strong>Discount ({{ $purchase->discount_percentage }}%)</strong></td>
                                    <td class="right">{{ format_currency($purchase->discount_amount) }}</td>
                                </tr>
                                <tr>
                                    <td class="left"><strong>Tax ({{ $purchase->tax_percentage }}%)</strong></td>
                                    <td class="right">{{ format_currency($purchase->tax_amount) }}</td>
                                </tr>
                                <tr>
                                    <td class="left"><strong>Shipping</strong></td>
                                    <td class="right">{{ format_currency($purchase->shipping_amount) }}</td>
                                </tr>
                                <tr>
                                    <td class="left"><strong>Grand Total</strong></td>
                                    <td class="right"><strong>{{ format_currency($purchase->total_amount) }}</strong></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

