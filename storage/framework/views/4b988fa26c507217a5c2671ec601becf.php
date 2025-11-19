<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Nota - <?php echo e($sale->reference); ?></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * {
            font-size: 11px;
            line-height: 14px;
            font-family: 'Courier New', 'Ubuntu', sans-serif;
            margin: 0;
            padding: 0;
        }
        h2 {
            font-size: 13px;
            margin: 0;
        }
        td,
        th,
        tr,
        table {
            border-collapse: collapse;
        }
        tr {border-bottom: 1px dashed #ddd;}
        td,th {padding: 3px 0; margin: 0;}

        table {width: 100%; margin: 0;}
        tfoot tr th:first-child {text-align: left;}

        .centered {
            text-align: center;
            align-content: center;
        }
        small{font-size:10px;}
        
        body {
            width: 100%;
            max-width: 350px;
            margin: 0;
            padding: 2mm;
            background: white;
        }
        
        #receipt-data {
            width: 100%;
            max-width: 350px;
            margin: 0;
            padding: 0;
        }

        @media print {
            body, html {
                width: 100%;
                max-width: 350px;
                margin: 0 !important;
                padding: 2mm !important;
                background: white !important;
            }
            
            @page {
                size: auto;
                margin: 0;
                padding: 0;
            }
            
            * {
                font-size: 11px;
                line-height: 14px;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            td, th {
                padding: 2px 0 !important;
                margin: 0 !important;
            }
            
            h2 {
                font-size: 12px;
                margin: 2px 0 !important;
            }
            
            tr {
                page-break-inside: avoid;
            }
            
            table {
                page-break-inside: avoid;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            #receipt-data {
                page-break-inside: avoid;
                page-break-after: avoid;
            }
            
            .hidden-print {
                display: none !important;
            }
            .no-print {
                display: none !important;
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
    <?php if(session('error')): ?>
        <div style="background: #fff3cd; color: #856404; padding: 8px; margin-bottom: 10px; border-radius: 3px; border: 1px solid #ffc107;">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>
    <button onclick="window.print()" style="padding: 8px 15px; margin-right: 5px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer;">
        <i class="bi bi-printer"></i> Print
    </button>
    <button onclick="window.close()" style="padding: 8px 15px; background: #6c757d; color: white; border: none; border-radius: 3px; cursor: pointer;">
        <i class="bi bi-x"></i> Tutup
    </button>
</div>

<div style="max-width: 350px; margin: 0 auto; padding: 0;">
    <div id="receipt-data">
        <div class="centered">
            <h2 style="margin: 0 0 2px 0;"><?php echo e(settings()->company_name); ?></h2>
            <p style="font-size: 10px; line-height: 12px; margin: 0; padding: 0;">
                <?php echo e(settings()->company_email); ?>, <?php echo e(settings()->company_phone); ?><br><?php echo e(settings()->company_address); ?>

            </p>
        </div>
        <p style="font-size: 11px; line-height: 13px; margin: 3px 0; padding: 0;">
            Date: <?php echo e(\Carbon\Carbon::parse($sale->date)->format('d M, Y')); ?><br>
            Ref: <?php echo e($sale->reference); ?><br>
            <?php if($sale->customer_id): ?>
            Name: <?php echo e($sale->customer_name); ?>

            <?php endif; ?>
        </p>
        <table class="table-data">
            <tbody>
            <?php $__currentLoopData = $sale->saleDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $saleDetail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td colspan="2" style="padding: 2px 0;">
                        <?php echo e($saleDetail->product->product_name); ?><br>
                        <span style="font-size: 10px;">(<?php echo e($saleDetail->quantity); ?> x <?php echo e(format_currency($saleDetail->price)); ?>)</span>
                    </td>
                    <td style="text-align: right; padding: 2px 0;"><?php echo e(format_currency($saleDetail->sub_total)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($sale->tax_percentage): ?>
                <tr>
                    <th colspan="2" style="text-align: left; padding: 2px 0;">Tax (<?php echo e($sale->tax_percentage); ?>%)</th>
                    <th style="text-align: right; padding: 2px 0;"><?php echo e(format_currency($sale->tax_amount)); ?></th>
                </tr>
            <?php endif; ?>
            <?php if($sale->discount_percentage): ?>
                <tr>
                    <th colspan="2" style="text-align: left; padding: 2px 0;">Discount (<?php echo e($sale->discount_percentage); ?>%)</th>
                    <th style="text-align: right; padding: 2px 0;"><?php echo e(format_currency($sale->discount_amount)); ?></th>
                </tr>
            <?php endif; ?>
            <?php if($sale->shipping_amount): ?>
                <tr>
                    <th colspan="2" style="text-align: left; padding: 2px 0;">Shipping</th>
                    <th style="text-align: right; padding: 2px 0;"><?php echo e(format_currency($sale->shipping_amount)); ?></th>
                </tr>
            <?php endif; ?>
            <tr style="border-top: 1px solid #000; font-weight: bold;">
                <th colspan="2" style="text-align: left; padding: 2px 0;">Total</th>
                <th style="text-align: right; padding: 2px 0;"><?php echo e(format_currency($sale->total_amount)); ?></th>
            </tr>
            </tbody>
        </table>
        <table style="margin-top: 3px;">
            <tbody>
                <tr style="background-color: #e0e0e0;">
                    <td class="centered" style="padding: 3px; font-size: 10px;">
                        Paid: <?php echo e($sale->payment_method); ?>

                    </td>
                    <td class="centered" style="padding: 3px; font-size: 10px;">
                        <?php echo e(format_currency($sale->paid_amount)); ?>

                    </td>
                </tr>
                <tr style="border-bottom: 0;">
                    <td class="centered" colspan="2" style="padding: 3px 0;">
                        <div style="margin-top: 3px; margin-bottom: 0;">
                            <?php echo \Milon\Barcode\Facades\DNS1DFacade::getBarcodeSVG($sale->reference, 'C128', 0.8, 20, 'black', false); ?>

                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    // No auto-print in template
    // Print is handled by openPrintWindow() from parent window (main-js.blade.php)
    // This prevents double print dialogs
    
    // Auto close this window when parent closes or after print
    let afterPrintHandled = false;
    window.addEventListener('afterprint', function() {
        if (!afterPrintHandled) {
            afterPrintHandled = true;
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

<?php /**PATH D:\project warnet\Nameless\Modules/Sale\Resources/views/print-view.blade.php ENDPATH**/ ?>