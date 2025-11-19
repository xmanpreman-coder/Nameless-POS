<?php
/**
 * Fix: Ensure printer_settings table exists and has data
 */

$db = new PDO('sqlite:D:/project warnet/Nameless/database/database.sqlite');

echo "Checking printer_settings table...\n";

try {
    // Check if table exists
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='printer_settings'")->fetch();
    
    if (!$result) {
        echo "✓ Creating printer_settings table...\n";
        
        $db->exec("
            CREATE TABLE printer_settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                receipt_paper_size VARCHAR(20) DEFAULT '80mm',
                auto_print_receipt BOOLEAN DEFAULT 0,
                default_receipt_printer VARCHAR(255) DEFAULT NULL,
                print_customer_copy BOOLEAN DEFAULT 0,
                receipt_copies INTEGER DEFAULT 1,
                thermal_printer_commands TEXT DEFAULT NULL,
                printer_profiles JSON DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Insert default record
        $db->exec("INSERT INTO printer_settings (receipt_paper_size, receipt_copies) VALUES ('80mm', 1)");
        
        echo "✓ Table created with default settings\n";
    } else {
        echo "✓ Table already exists\n";
        
        // Ensure there's at least one record
        $count = $db->query("SELECT COUNT(*) FROM printer_settings")->fetchColumn();
        if ($count == 0) {
            echo "✓ Inserting default settings...\n";
            $db->exec("INSERT INTO printer_settings (receipt_paper_size, receipt_copies) VALUES ('80mm', 1)");
        }
    }
    
    // List all printer_settings records
    $records = $db->query("SELECT * FROM printer_settings")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nCurrent printer_settings:\n";
    foreach ($records as $record) {
        echo "  ID: " . $record['id'] . "\n";
        echo "  Paper Size: " . $record['receipt_paper_size'] . "\n";
        echo "  Auto Print: " . ($record['auto_print_receipt'] ? 'Yes' : 'No') . "\n";
        echo "  Receipt Copies: " . $record['receipt_copies'] . "\n";
    }
    
    echo "\n✅ Printer settings fixed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
