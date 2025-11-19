<?php
// Quick test to verify PrinterManager integration

require_once 'vendor/autoload.php';

echo "=== Printer Integration Test ===\n\n";

// Check if classes exist
echo "1. Checking classes:\n";
echo "   Mike42 Library: " . (class_exists('\\Mike42\\Escpos\\Printer') ? "✓ YES\n" : "✗ NO\n");
echo "   PrinterManager: " . (class_exists('\\App\\Services\\PrinterManager') ? "✓ YES\n" : "✗ NO\n");
echo "   ThermalPrinterService: " . (class_exists('\\App\\Services\\ThermalPrinterService') ? "✓ YES\n" : "✗ NO\n");
echo "   ThermalPrinterSetting: " . (class_exists('\\App\\Models\\ThermalPrinterSetting') ? "✓ YES\n" : "✗ NO\n\n");

// Check config
echo "2. Checking printer config:\n";
if (function_exists('config')) {
    echo "   Config function available\n";
} else {
    echo "   Config function NOT available (need Laravel bootstrap)\n";
}

echo "\n✓ Basic integration verification complete!\n";
echo "   To run full tests, use php artisan tinker or the Thermal Printer UI.\n";
