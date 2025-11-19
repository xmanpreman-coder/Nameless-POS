<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $sale->reference }}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', 'Liberation Mono', monospace;
            font-size: 9px;
            line-height: 10px;
            color: #000;
            background: white;
            width: 100%;
            margin: 0;
            padding: 0;
        }
        
        .thermal-receipt {
            width: 72mm; /* 80mm paper with 4mm margin each side */
            max-width: 72mm;
            margin: 0;
            padding: 1.5mm;
            background: white;
            font-size: 9px;
            line-height: 10px;
        }
        
        .center { text-align: center; }
        .left { text-align: left; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        
        .company-header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 4px;
        }
        
        .company-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .company-info {
            font-size: 9px;
            line-height: 10px;
        }
        
        .receipt-title {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            margin: 6px 0;
        }
        
        .info-section {
            margin-bottom: 4px;
            font-size: 9px;
            line-height: 10px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1px;
        }
        
        .separator {
            border-top: 1px dashed #000;
            margin: 4px 0;
        }
        
        .items-header {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            margin-bottom: 2px;
        }
        
        .item-row {
            margin-bottom: 1px;
            font-size: 9px;
            line-height: 10px;
        }
        
        .item-name {
            font-size: 9px;
            margin-bottom: 1px;
        }
        
        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 8px;
        }
        
        .totals-section {
            border-top: 1px dashed #000;
            padding-top: 3px;
            margin-top: 3px;
            font-size: 9px;
            page-break-inside: avoid;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1px;
        }
        
        .grand-total {
            border-top: 1px solid #000;
            padding-top: 2px;
            margin-top: 2px;
            font-weight: bold;
            font-size: 10px;
        }
        
        .payment-section {
            border-top: 1px dashed #000;
            padding-top: 3px;
            margin-top: 3px;
            page-break-inside: avoid;
            page-break-before: avoid;
            break-inside: avoid;
        }
        
        .footer {
            text-align: center;
            margin-top: 8px;
            border-top: 1px dashed #000;
            padding-top: 4px;
            font-size: 8px;
        }
        
        /* Print mode - explicitly set page to thermal size */
        @media print {
            body, html {
                width: 80mm !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }
            
            @page {
                size: 80mm auto;
                margin: 0;
                padding: 0;
            }
            
            .thermal-receipt {
                width: 80mm !important;
                max-width: 80mm !important;
                margin: 0 !important;
                padding: 0 !important;
                page-break-after: avoid !important;
            }
            
            /* Hide all non-print elements */
            #print-guide,
            #debug-info,
            .no-print {
                display: none !important;
                height: 0 !important;
                width: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            /* Aggressive space reduction for items */
            .item-row {
                margin-bottom: 0px !important;
                page-break-inside: avoid !important;
            }
            
            /* Prevent separators from breaking content */
            .separator {
                margin: 1px 0 !important;
            }
            
            /* Ensure totals stay together */
            .totals-section,
            .payment-section {
                page-break-inside: avoid !important;
                page-break-before: avoid !important;
                break-inside: avoid !important;
            }
        }
        
        .barcode {
            text-align: center;
            margin: 6px 0;
        }
        
        @media print {
            @page {
                size: 80mm auto; /* Auto height for thermal paper */
                margin: 0mm; /* No margins for thermal printing */
                padding: 0mm;
            }
            
            /* Thermal printer optimizations from manual */
            @supports (-webkit-appearance: none) {
                body {
                    -webkit-print-color-adjust: exact;
                }
            }
            
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            body {
                margin: 0;
                padding: 0;
                background: white !important;
                -webkit-print-color-adjust: exact;
            }
            
            .thermal-receipt {
                width: 80mm;
                max-width: 80mm;
                margin: 0;
                padding: 1.5mm;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
                page-break-before: avoid !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            /* Prevent page breaks inside important sections */
            .company-header,
            .info-section,
            .item-row,
            .totals-section,
            .payment-section {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align: center; margin-bottom: 10px;">
    <button onclick="window.thermalPrint()" style="background: #28a745; color: white; border: none; padding: 8px 16px; margin: 4px; border-radius: 4px; cursor: pointer; font-weight: bold;">🖨️ Thermal Print</button>
    <button onclick="window.print()" style="background: #007bff; color: white; border: none; padding: 8px 16px; margin: 4px; border-radius: 4px; cursor: pointer;">📄 Standard Print</button>
    <button onclick="window.ThermalTest.runAllTests()" style="background: #ffc107; color: black; border: none; padding: 8px 16px; margin: 4px; border-radius: 4px; cursor: pointer;">🔍 Test Printer</button>
    <button onclick="window.ThermalTest.toggleDebugMode()" style="background: #dc3545; color: white; border: none; padding: 8px 16px; margin: 4px; border-radius: 4px; cursor: pointer;">🔧 Debug Mode</button>
    <button onclick="togglePrintGuide()" style="background: #17a2b8; color: white; border: none; padding: 8px 16px; margin: 4px; border-radius: 4px; cursor: pointer;">ℹ️ Print Guide</button>
    <button onclick="window.close()" style="background: #6c757d; color: white; border: none; padding: 8px 16px; margin: 4px; border-radius: 4px; cursor: pointer;">❌ Close</button>
</div>

<!-- Print Guide Overlay -->
<div id="print-guide" class="no-print" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; padding: 20px; overflow-y: auto;">
    <div style="background: white; max-width: 600px; margin: 30px auto; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <button onclick="togglePrintGuide()" style="float: right; background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">&times;</button>
        <h3 style="margin-top: 0; border-bottom: 2px solid #007bff; padding-bottom: 10px;">🖨️ How to Print This Receipt</h3>
        
        <h4 style="margin-top: 20px; color: #28a745;">For Thermal Printer (Recommended)</h4>
        <p><strong>Server ESC/POS Print (Best):</strong></p>
        <ul style="margin: 10px 0;">
            <li>Close this window and go back to the Sales page.</li>
            <li>Click <strong>Thermal Print → Use Default Printer</strong>.</li>
            <li>Receipt prints directly to configured thermal printer (no browser dialog).</li>
            <li>Correct width (80mm) and no extra page breaks.</li>
        </ul>
        
        <p><strong>Browser Print (This Window):</strong></p>
        <ol style="margin: 10px 0;">
            <li>Click <strong>🖨️ Thermal Print</strong> button above.</li>
            <li>In the print dialog that opens, go to <strong>More settings</strong> or <strong>Advanced</strong>.</li>
            <li><strong>Set Paper Size:</strong>
                <ul style="margin: 5px 0;">
                    <li><strong>Width:</strong> 80 mm</li>
                    <li><strong>Height:</strong> Auto or 200 mm</li>
                </ul>
            </li>
            <li><strong>Set Margins:</strong> None (or 0 mm all sides)</li>
            <li><strong>Scale:</strong> 100% (do NOT use "Fit to page")</li>
            <li>Preview should show narrow receipt. Click <strong>Print</strong>.</li>
        </ol>
        
        <h4 style="margin-top: 20px; color: #007bff;">For PDF Save</h4>
        <ol style="margin: 10px 0;">
            <li>Click <strong>📄 Standard Print</strong> button above.</li>
            <li>In print dialog, select <strong>"Save as PDF"</strong> destination.</li>
            <li>Follow the same Paper Size and Margins settings as above (80mm width, No margins).</li>
            <li>Click <strong>Save</strong>.</li>
            <li>PDF will be saved with proper thermal receipt dimensions.</li>
        </ol>
        
        <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">
        <p style="font-size: 12px; color: #666;">
            <strong>Tip:</strong> If the receipt is still split across 2 pages, your paper size setting didn't apply correctly. Try again and make sure <strong>80mm width</strong> is set before clicking Print/Save.
        </p>
        
        <div style="text-align: center; margin-top: 20px;">
            <button onclick="togglePrintGuide()" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px;">Close Guide</button>
        </div>
    </div>
</div>

<!-- Debug Info Panel -->
<div id="debug-info" class="no-print" style="display: none; background: #f8f9fa; border: 1px solid #dee2e6; margin: 10px; padding: 10px; font-family: monospace; font-size: 12px;">
    <div style="font-weight: bold; margin-bottom: 8px;">🔧 Debug Information</div>
    <div id="debug-content">
        <div>📏 Container Width: <span id="debug-width">-</span></div>
        <div>🔤 Font Family: <span id="debug-font">-</span></div>
        <div>📄 Page Size: <span id="debug-pagesize">-</span></div>
        <div>🖨️ ESC Commands: <span id="debug-esc">-</span></div>
        <div>⚡ Status: <span id="debug-status">Loading...</span></div>
    </div>
    <div style="margin-top: 8px;">
        <small>💡 Tip: Use Ctrl+Alt+T for quick test, Ctrl+Alt+D for debug toggle</small>
    </div>
</div>

<div class="thermal-receipt">
    <!-- Company Header -->
    <div class="company-header">
        <div class="company-name">{{ settings()->company_name }}</div>
        <div class="company-info">
            {{ settings()->company_address }}<br>
            Tel: {{ settings()->company_phone }}<br>
            @if(settings()->company_email)
            {{ settings()->company_email }}
            @endif
        </div>
    </div>
    
    <!-- Receipt Title -->
    <div class="receipt-title">SALES RECEIPT</div>
    
    <!-- Transaction Info -->
    <div class="info-section">
        <div class="info-row">
            <span>Receipt#:</span>
            <span class="bold">{{ $sale->reference }}</span>
        </div>
        <div class="info-row">
            <span>Date:</span>
            <span>{{ \Carbon\Carbon::parse($sale->date)->format('d/m/y H:i') }}</span>
        </div>
        @if($customer)
        <div class="info-row">
            <span>Customer:</span>
            <span>{{ $customer->customer_name }}</span>
        </div>
        @endif
        <div class="info-row">
            <span>Cashier:</span>
            <span>{{ $sale->user->name ?? 'System' }}</span>
        </div>
    </div>
    
    <div class="separator"></div>
    
    <!-- Items Header -->
    <div class="items-header">
        <span style="width: 60%;">Item</span>
        <span style="width: 15%; text-align: center;">Qty</span>
        <span style="width: 25%; text-align: right;">Amount</span>
    </div>
    
    <!-- Items List -->
    @foreach($sale->saleDetails as $item)
    <div class="item-row">
        <div class="item-name">{{ $item->product_name }}</div>
        <div class="item-details">
            <span>{{ format_currency($item->unit_price) }} x {{ $item->quantity }}</span>
            <span class="bold">{{ format_currency($item->sub_total) }}</span>
        </div>
    </div>
    @endforeach
    
    <!-- Totals Section -->
    <div class="totals-section">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>{{ format_currency($sale->total_amount + $sale->discount_amount - $sale->tax_amount - $sale->shipping_amount) }}</span>
        </div>
        
        @if($sale->discount_amount > 0)
        <div class="total-row">
            <span>Discount ({{ $sale->discount_percentage }}%):</span>
            <span>-{{ format_currency($sale->discount_amount) }}</span>
        </div>
        @endif
        
        @if($sale->tax_amount > 0)
        <div class="total-row">
            <span>Tax ({{ $sale->tax_percentage }}%):</span>
            <span>{{ format_currency($sale->tax_amount) }}</span>
        </div>
        @endif
        
        @if($sale->shipping_amount > 0)
        <div class="total-row">
            <span>Shipping:</span>
            <span>{{ format_currency($sale->shipping_amount) }}</span>
        </div>
        @endif
        
        <div class="total-row grand-total">
            <span>TOTAL:</span>
            <span>{{ format_currency($sale->total_amount) }}</span>
        </div>
    </div>
    
    <!-- Payment Section -->
    <div class="payment-section">
        <div class="total-row">
            <span>Payment ({{ $sale->payment_method }}):</span>
            <span>{{ format_currency($sale->paid_amount) }}</span>
        </div>
        
        @if($sale->paid_amount > $sale->total_amount)
        <div class="total-row">
            <span>Change:</span>
            <span>{{ format_currency($sale->paid_amount - $sale->total_amount) }}</span>
        </div>
        @endif
    </div>
    
    <!-- Barcode -->
    @if(class_exists('\Milon\Barcode\Facades\DNS1DFacade'))
    <div class="barcode">
        {!! \Milon\Barcode\Facades\DNS1DFacade::getBarcodeSVG($sale->reference, 'C128', 1, 20, 'black', false) !!}
    </div>
    @endif
    
    <!-- Footer -->
    <div class="footer">
        <div>Thank you for your business!</div>
        <div>{{ settings()->company_name }}</div>
        <div>{{ now()->format('Y') }}</div>
    </div>
</div>

<script src="{{ asset('js/thermal-printer-commands.js') }}"></script>
<script src="{{ asset('js/thermal-printer-test.js') }}"></script>
<script>
// Thermal printer optimization berdasarkan manual
const ThermalPrintOptimizer = {
    
    // Inject ESC commands untuk printer driver
    injectESCCommands: function() {
        // Berdasarkan halaman 9-11 manual, set optimal commands
        const escCommands = [
            '\x1B\x40',        // ESC @ - Initialize printer
            '\x1B\x32',        // ESC 2 - Default line spacing
            '\x1B\x4D\x00',    // ESC M - Font A (12x24)
            '\x1B\x21\x00',    // ESC ! - Normal print mode
            '\x1B\x20\x00',    // ESC SP - Minimal character spacing
            '\x1B\x33\x14',    // ESC 3 - Set line spacing to 20
            '\x1B\x61\x00'     // ESC a - Left justify
        ];
        
        // Create hidden div dengan commands untuk driver
        const commandDiv = document.createElement('div');
        commandDiv.style.display = 'none';
        commandDiv.className = 'esc-commands';
        commandDiv.innerHTML = '<!--THERMAL_INIT:' + btoa(escCommands.join('')) + '-->';
        document.body.appendChild(commandDiv);
        
        console.log('ESC commands injected for thermal printer');
    },
    
    // Optimize CSS untuk 80mm thermal
    optimizeForThermal: function() {
        const style = document.createElement('style');
        style.innerHTML = `
            /* Override untuk thermal printing */
            @media print {
                .thermal-receipt {
                    width: 72mm !important;
                    max-width: 72mm !important;
                    font-size: 9px !important;
                    line-height: 11px !important;
                    page-break-inside: avoid !important;
                }
                
                /* Kompres spacing untuk menghemat kertas */
                .info-row, .total-row, .item-details {
                    margin-bottom: 0.5px !important;
                }
                
                .separator {
                    margin: 2px 0 !important;
                }
                
                /* Font monospace untuk konsistensi */
                * {
                    font-family: 'Courier New', 'Liberation Mono', monospace !important;
                }
                
                /* Prevent page breaks di tengah content */
                .company-header,
                .info-section,
                .totals-section,
                .payment-section {
                    page-break-inside: avoid !important;
                }
            }
        `;
        document.head.appendChild(style);
    },
    
    // Print dengan optimasi thermal
    thermalPrint: function() {
        this.injectESCCommands();
        this.optimizeForThermal();
        
        // Small delay untuk commands processing
        setTimeout(function() {
            window.print();
        }, 800);
    }
};

// Initialize saat page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Thermal print template loaded');
    ThermalPrintOptimizer.injectESCCommands();
    ThermalPrintOptimizer.optimizeForThermal();
    
    // Update debug info
    updateDebugInfo();
    
    // Auto print setelah optimization (optional)
    // setTimeout(function() {
    //     ThermalPrintOptimizer.thermalPrint();
    // }, 1000);
});

// Handle print events
window.addEventListener('beforeprint', function() {
    console.log('Printing thermal receipt with ESC commands...');
    
    // Last minute optimization
    ThermalPrintOptimizer.optimizeForThermal();
    
    // Inject cut command untuk setelah print
    const cutDiv = document.createElement('div');
    cutDiv.style.display = 'none';
    cutDiv.innerHTML = '<!--THERMAL_CUT:' + btoa('\x1B\x69') + '-->';
    document.body.appendChild(cutDiv);
});

window.addEventListener('afterprint', function() {
    console.log('Thermal print completed');
    // Optional: Auto close window
    // setTimeout(() => window.close(), 2000);
});

// Handle escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        window.close();
    }
    
    // Manual print trigger (P key)
    if (e.key === 'p' || e.key === 'P') {
        e.preventDefault();
        ThermalPrintOptimizer.thermalPrint();
    }
});

// Update debug info panel
function updateDebugInfo() {
    const receipt = document.querySelector('.thermal-receipt');
    const escDiv = document.querySelector('.esc-commands');
    
    if (receipt) {
        const styles = window.getComputedStyle(receipt);
        document.getElementById('debug-width').textContent = styles.width;
        document.getElementById('debug-font').textContent = styles.fontFamily.split(',')[0];
        document.getElementById('debug-pagesize').textContent = '80mm x auto';
        document.getElementById('debug-esc').textContent = escDiv ? 'Injected' : 'Missing';
        document.getElementById('debug-status').textContent = 'Ready';
    }
}

// Toggle debug panel
function toggleDebugPanel() {
    const debugInfo = document.getElementById('debug-info');
    if (debugInfo.style.display === 'none') {
        debugInfo.style.display = 'block';
        updateDebugInfo();
    } else {
        debugInfo.style.display = 'none';
    }
}

// Override ThermalTest toggle untuk show/hide debug panel
const originalToggleDebug = window.ThermalTest ? window.ThermalTest.toggleDebugMode : null;
if (window.ThermalTest) {
    window.ThermalTest.toggleDebugMode = function() {
        const result = originalToggleDebug.call(this);
        toggleDebugPanel();
        return result;
    };
}

// Expose untuk manual trigger dari button
window.thermalPrint = function() {
    ThermalPrintOptimizer.thermalPrint();
};

// Toggle print guide overlay
function togglePrintGuide() {
    const guide = document.getElementById('print-guide');
    if (guide) {
        guide.style.display = guide.style.display === 'none' ? 'block' : 'none';
    }
}

</script>

</body>
</html>