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
            padding: 12px;
            margin-bottom: 12px;
            display: inline-block;
            width: calc(25% - 20px);
            margin-right: 15px;
            vertical-align: top;
            page-break-inside: avoid;
            text-align: center;
        }
        .barcode-name {
            font-size: 16px;
            color: #000;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .barcode-code {
            font-size: 14px;
            color: #000;
            margin-top: 8px;
            margin-bottom: 6px;
            font-weight: 600;
        }
        .barcode-price {
            font-size: 14px;
            color: #000;
            font-weight: 700;
            margin-top: 6px;
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
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn">Print</button>
        <button onclick="window.close()" class="btn">Close</button>
    </div>
    
    <div class="print-barcode">
        @php
            $labelParam = strtolower(request()->get('label', ''));
        @endphp

        @foreach($barcodeData as $data)
            @php
                // Decide which label and numeric value to show
                $label = $labelParam === 'sku' ? 'sku' : (strtolower($data['barcode_source'] ?? '') === 'sku' ? 'sku' : 'gtin');
                if ($label === 'sku') {
                    $value = $data['sku'] ?? $data['barcode_value'] ?? '';
                } else {
                    $value = $data['gtin'] ?? $data['barcode_value'] ?? $data['sku'] ?? '';
                }
            @endphp

            <div class="barcode-item">
                <p class="barcode-name">{{ $data['name'] ?? '' }}</p>

                <div class="text-center" style="background-color: #ffffff;">
                    {!! $data['barcode'] !!}
                </div>

                <p class="barcode-code">{{ $value }}</p>

                <p class="barcode-price">{{ format_currency($data['price'] ?? 0) }}</p>
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

