<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing ThermalPrinterService with receipt_copies ===\n\n";

try {
    $service = new \App\Services\ThermalPrinterService();
    
    // Get first thermal printer
    $printer = \App\Models\ThermalPrinterSetting::first();
    if (!$printer) {
        echo "⚠️  No thermal printer found. Please configure one first.\n";
        exit(1);
    }
    
    $service->setPrinter($printer);
    echo "Printer: " . $printer->name . "\n";
    echo "Connection: " . $printer->connection_type . "\n\n";
    
    // Get first sale
    $sale = \Modules\Sale\Entities\Sale::first();
    if (!$sale) {
        echo "⚠️  No sales found. Please create a sale first.\n";
        exit(1);
    }
    
    echo "Test Sale: " . $sale->reference . "\n";
    echo "Total: " . format_currency($sale->total_amount) . "\n\n";
    
    // Check receipt_copies
    $setting = \App\Models\PrinterSetting::first();
    echo "Receipt Copies Setting: " . ($setting ? $setting->receipt_copies : 'default (1)') . "\n";
    
    // Test the service method exists and is callable
    $method = new \ReflectionMethod($service, 'getReceiptCopies');
    $method->setAccessible(true);
    $copies = $method->invoke($service);
    echo "Copies to print (from getReceiptCopies()): " . $copies . "\n\n";
    
    echo "✓ Service is ready to print $copies copy/copies.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

exit(0);
