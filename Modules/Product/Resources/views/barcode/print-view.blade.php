<!DOCTYPE html>
<html>
<head>
    <title>Print Barcode</title>
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .print-barcode, .print-barcode * {
                visibility: visible;
            }
            .print-barcode {
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
            padding: 20px;
        }
        .barcode-item {
            border: 1px solid #ddd;
            border-style: dashed;
            background-color: #ffffff;
            padding: 15px;
            margin-bottom: 10px;
            display: inline-block;
            width: calc(25% - 20px);
            margin-right: 15px;
            vertical-align: top;
            page-break-inside: avoid;
        }
        .barcode-name {
            font-size: 15px;
            color: #000;
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .barcode-code {
            font-size: 11px;
            color: #000;
            margin-bottom: 5px;
        }
        .barcode-price {
            font-size: 15px;
            color: #000;
            font-weight: bold;
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
        svg {
            background-color: #ffffff !important;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn">Print</button>
        <button onclick="window.close()" class="btn">Close</button>
    </div>
    
    <div class="print-barcode">
        @foreach($barcodeData as $data)
            <div class="barcode-item">
                <p class="barcode-name">
                    {{ $data['name'] }}
                </p>
                <div class="text-center" style="background-color: #ffffff;">
                    {!! $data['barcode'] !!}
                </div>
                <p class="barcode-code">
                    <strong>{{ strtoupper($data['barcode_source'] ?? 'GTIN') }}: {{ $data['barcode_value'] ?? ($data['gtin'] ?? $data['sku'] ?? '') }}</strong>
                </p>
                @if(isset($data['gtin']) && $data['gtin'])
                    <p class="barcode-code" style="color: #666;">
                        GTIN: {{ $data['gtin'] }}
                    </p>
                @endif
                @if(isset($data['sku']) && $data['sku'])
                    <p class="barcode-code" style="color: #666;">
                        SKU: {{ $data['sku'] }}
                    </p>
                @endif
                <p class="barcode-price">
                    Price: {{ format_currency($data['price']) }}
                </p>
            </div>
        @endforeach
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
        const printKey = 'print_barcode_' + window.location.pathname;
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
</body>
</html>

