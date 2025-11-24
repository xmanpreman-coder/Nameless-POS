<?php
/**
 * Test External Scanner Endpoint
 * Run: php test_barcode_endpoint.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;

// Test configuration
$testConfigs = [
    ['name' => 'localhost:8000', 'url' => 'http://localhost:8000/api/scanner/scan'],
    ['name' => '127.0.0.1:8000', 'url' => 'http://127.0.0.1:8000/api/scanner/scan'],
];

// Test barcodes
$testBarcodes = ['TEST123', 'TEST_BARCODE_001', '8992017009981'];

echo "\n✅ Testing External Scanner Endpoint\n";
echo str_repeat("=", 60) . "\n\n";

foreach ($testConfigs as $config) {
    echo "Testing: {$config['name']}\n";
    echo "-" . str_repeat("-", 58) . "\n";
    
    foreach ($testBarcodes as $barcode) {
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $config['url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode(['barcode' => $barcode]),
            CURLOPT_TIMEOUT => 5,
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        
        curl_close($curl);
        
        if ($error) {
            echo "  ❌ Barcode '$barcode': ERROR - $error\n";
        } else {
            echo "  ✓ Barcode '$barcode': HTTP $httpCode\n";
            if ($response) {
                $decoded = json_decode($response, true);
                if ($decoded) {
                    echo "    Response: " . json_encode($decoded) . "\n";
                }
            }
        }
    }
    
    echo "\n";
}

echo str_repeat("=", 60) . "\n";
echo "✅ Test Complete\n";
echo "\nTips for Flutter:\n";
echo "1. Use 'localhost' or '127.0.0.1' if HP connected via USB debugging\n";
echo "2. Use PC's actual IP (e.g., 192.168.1.x) if HP on same WiFi network\n";
echo "3. Check Network: ipconfig (Windows) or ifconfig (Linux/Mac)\n";
echo "\n";
