<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Barcodes</title>
    <link rel="stylesheet" href="{{ public_path('b3/bootstrap.min.css') }}">
    <style>
        .barcode-item {
            border: 1px solid #dddddd;
            border-style: dashed;
            background-color: #ffffff;
            padding: 15px;
            margin-bottom: 10px;
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
        svg {
            background-color: #ffffff !important;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row">
        @foreach($barcodeData as $data)
            <div class="col-xs-3 barcode-item">
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
</div>
</body>
</html>

