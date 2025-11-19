<?php
/**
 * Fix: Alter printer_settings table to have all required columns
 */

$db = new PDO('sqlite:D:/project warnet/Nameless/database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Fixing printer_settings table structure...\n\n";

try {
    // Get current columns
    $currentColsList = $db->query("PRAGMA table_info(printer_settings)")
        ->fetchAll(PDO::FETCH_ASSOC);
    
    $currentColNames = array_column($currentColsList, 'name');
    echo "Current columns: " . implode(', ', $currentColNames) . "\n\n";
    
    // Required columns
    $requiredCols = [
        'receipt_paper_size' => "VARCHAR(20) DEFAULT '80mm'",
        'auto_print_receipt' => "BOOLEAN DEFAULT 0",
        'default_receipt_printer' => "VARCHAR(255) DEFAULT NULL",
        'print_customer_copy' => "BOOLEAN DEFAULT 0",
        'receipt_copies' => "INTEGER DEFAULT 1",
        'thermal_printer_commands' => "TEXT DEFAULT NULL",
        'printer_profiles' => "TEXT DEFAULT NULL",
    ];
    
    // Add missing columns
    foreach ($requiredCols as $col => $type) {
        if (!in_array($col, $currentColNames)) {
            echo "✓ Adding column: $col\n";
            $db->exec("ALTER TABLE printer_settings ADD COLUMN $col $type");
        }
    }
    
    // Verify columns now exist
    echo "\nVerifying columns...\n";
    $currentCols = $db->query("PRAGMA table_info(printer_settings)")
        ->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($currentCols as $col) {
        echo "  ✓ " . $col['name'] . " (" . $col['type'] . ")\n";
    }
    
    // Ensure at least one record exists with defaults
    echo "\nChecking records...\n";
    $count = $db->query("SELECT COUNT(*) FROM printer_settings")->fetchColumn();
    
    if ($count == 0) {
        echo "✓ Inserting default settings...\n";
        $db->exec("
            INSERT INTO printer_settings (
                receipt_paper_size,
                auto_print_receipt,
                print_customer_copy,
                receipt_copies
            ) VALUES (
                '80mm',
                0,
                0,
                1
            )
        ");
    } else {
        // Update existing record with defaults if empty
        $db->exec("
            UPDATE printer_settings
            SET receipt_paper_size = COALESCE(NULLIF(receipt_paper_size, ''), '80mm'),
                receipt_copies = COALESCE(NULLIF(receipt_copies, 0), 1)
            WHERE receipt_paper_size IS NULL OR receipt_paper_size = ''
        ");
    }
    
    $records = $db->query("SELECT * FROM printer_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    echo "\nDefault settings:\n";
    echo "  ✓ Paper Size: " . ($records['receipt_paper_size'] ?? 'N/A') . "\n";
    echo "  ✓ Receipt Copies: " . ($records['receipt_copies'] ?? 'N/A') . "\n";
    echo "  ✓ Auto Print: " . (($records['auto_print_receipt'] ?? 0) ? 'Yes' : 'No') . "\n";
    
    echo "\n✅ Printer settings table fixed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    die(1);
}
?>
