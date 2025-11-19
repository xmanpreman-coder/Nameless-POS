#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Printer Connection Test ===\n";

try {
    $printer = \App\Models\ThermalPrinterSetting::first();
    if (! $printer) {
        echo "No printer configured (check database).\n";
        exit(0);
    }

    echo "Printer: " . ($printer->name ?? '(unnamed)') . "\n";
    echo "Type: " . ($printer->connection_type ?? '(unknown)') . "\n\n";

    $result = $printer->testConnection();

    if (! is_array($result)) {
        echo "Unexpected test result (not array).\n";
        var_export($result);
        exit(1);
    }

    echo "Connection Status: " . ($result['status'] ?? 'unknown') . "\n";
    echo "Message: " . ($result['message'] ?? '') . "\n";

    if (! empty($result['printer'])) {
        echo "OS Printer Info:\n" . json_encode($result['printer'], JSON_PRETTY_PRINT) . "\n";
    }

} catch (Exception $e) {
    echo "Error running test: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n✓ Done.\n";
exit(0);
