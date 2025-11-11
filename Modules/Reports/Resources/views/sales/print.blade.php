<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .print-table, .print-table * {
                visibility: visible;
            }
            .print-table {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .print-table {
            width: 100%;
            border-collapse: collapse;
        }
        .print-table th, .print-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .print-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .print-table tfoot th {
            background-color: #e0e0e0;
        }
        .no-print {
            margin-bottom: 20px;
        }
        .btn {
            padding: 10px 20px;
            margin: 5px;
            cursor: pointer;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn">Print</button>
        <button onclick="closeWindow()" class="btn">Close</button>
    </div>
    
    <div class="header">
        <h2>Sales Report</h2>
        <p>Period: {{ \Carbon\Carbon::parse($start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($end_date)->format('d M Y') }}</p>
    </div>
    
    <table class="print-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Customer</th>
                <th>Status</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Due</th>
                <th>Payment Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}</td>
                    <td>{{ $sale->reference }}</td>
                    <td>{{ $sale->customer_name }}</td>
                    <td>{{ $sale->status }}</td>
                    <td>{{ format_currency($sale->total_amount) }}</td>
                    <td>{{ format_currency($sale->paid_amount) }}</td>
                    <td>{{ format_currency($sale->due_amount) }}</td>
                    <td>{{ $sale->payment_status }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Total</th>
                <th>{{ format_currency($sales->sum('total_amount')) }}</th>
                <th>{{ format_currency($sales->sum('paid_amount')) }}</th>
                <th>{{ format_currency($sales->sum('due_amount')) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
    
    <script>
        // Prevent double print dengan sessionStorage
        const printKey = 'print_sales_' + window.location.search;
        let hasPrinted = false;
        
        function closeWindow() {
            if (window.opener) {
                window.close();
            } else {
                // Jika tidak dari popup, redirect ke halaman sebelumnya atau home
                window.history.back();
            }
        }
        
        if (window.opener) {
            // Hanya auto-print jika dibuka dari popup dan belum pernah print
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
        
        // Clear sessionStorage setelah print selesai
        let afterPrintHandled = false;
        window.addEventListener('afterprint', function() {
            if (!afterPrintHandled) {
                afterPrintHandled = true;
                sessionStorage.removeItem(printKey);
                // Auto close setelah print (jika dari popup)
                setTimeout(function() {
                    if (window.opener) {
                        window.close();
                    }
                }, 1000);
            }
        });
    </script>
</body>
</html>

