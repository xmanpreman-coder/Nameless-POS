<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Checking receipt_copies setting ===\n\n";

try {
    $printerSettings = \App\Models\PrinterSetting::first();
    if ($printerSettings) {
        echo "PrinterSetting found:\n";
        echo "  receipt_copies: " . $printerSettings->receipt_copies . "\n";
        echo "  ID: " . $printerSettings->id . "\n";
        echo "  name: " . ($printerSettings->name ?? 'N/A') . "\n";
    } else {
        echo "No PrinterSetting found in database.\n";
    }

    echo "\nThermalPrinterSetting records:\n";
    $thermals = \App\Models\ThermalPrinterSetting::all();
    if ($thermals->count() > 0) {
        foreach ($thermals as $t) {
            echo "  - " . $t->name . " (ID: " . $t->id . ", auto_cut: " . ($t->auto_cut ? 'YES' : 'NO') . ")\n";
        }
    } else {
        echo "  (No thermal printers configured)\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n✓ Done.\n";
exit(0);
