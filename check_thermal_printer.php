<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ThermalPrinterSetting;

$printer = ThermalPrinterSetting::first();

if ($printer) {
    echo "=== THERMAL PRINTER SETTING ===\n";
    echo "ID: {$printer->id}\n";
    echo "Name: {$printer->name}\n";
    echo "Brand: {$printer->brand}\n";
    echo "Model: {$printer->model}\n";
    echo "Connection Type: {$printer->connection_type}\n";
    echo "IP Address: {$printer->ip_address}\n";
    echo "Port: {$printer->port}\n";
    echo "Is Active: " . ($printer->is_active ? 'YES' : 'NO') . "\n";
    echo "Is Default: " . ($printer->is_default ? 'YES' : 'NO') . "\n";
    
    // Test connection
    echo "\n=== Testing Connection ===\n";
    $result = $printer->testConnection();
    echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "❌ No thermal printer settings found!\n";
}

echo "\n✅ Diagnostic complete\n";
