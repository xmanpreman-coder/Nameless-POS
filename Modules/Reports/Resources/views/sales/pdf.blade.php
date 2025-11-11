<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>
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
        .summary {
            margin-top: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Sales Report</h2>
        <p>Period: {{ \Carbon\Carbon::parse($start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($end_date)->format('d M Y') }}</p>
    </div>
    
    <table>
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
</body>
</html>
