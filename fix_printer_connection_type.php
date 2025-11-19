<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ThermalPrinterSetting;
use Illuminate\Support\Facades\DB;

// Fix the default printer data
$printer = ThermalPrinterSetting::first();

if ($printer && $printer->connection_type === 'network') {
    // Update connection type
    $printer->connection_type = 'ethernet';
    $printer->ip_address = '192.168.1.100';
    $printer->port = 9100;
    $printer->save();
    
    echo "✅ Updated Default Printer:\n";
    echo "- Connection Type: {$printer->connection_type}\n";
    echo "- IP Address: {$printer->ip_address}\n";
    echo "- Port: {$printer->port}\n";
    
    // Test again
    $result = $printer->testConnection();
    echo "\nTest Result:\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "No printer found or already fixed\n";
}

echo "\n✅ Fix complete\n";
