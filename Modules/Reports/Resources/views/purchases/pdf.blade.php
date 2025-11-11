<!DOCTYPE html>
<html>
<head>
    <title>Purchases Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Purchases Report</h2>
        <p>Period: {{ \Carbon\Carbon::parse($start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($end_date)->format('d M Y') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Supplier</th>
                <th>Status</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Due</th>
                <th>Payment Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchases as $purchase)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($purchase->date)->format('d M Y') }}</td>
                    <td>{{ $purchase->reference }}</td>
                    <td>{{ $purchase->supplier_name }}</td>
                    <td>{{ $purchase->status }}</td>
                    <td>{{ format_currency($purchase->total_amount) }}</td>
                    <td>{{ format_currency($purchase->paid_amount) }}</td>
                    <td>{{ format_currency($purchase->due_amount) }}</td>
                    <td>{{ $purchase->payment_status }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Total</th>
                <th>{{ format_currency($purchases->sum('total_amount')) }}</th>
                <th>{{ format_currency($purchases->sum('paid_amount')) }}</th>
                <th>{{ format_currency($purchases->sum('due_amount')) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>

