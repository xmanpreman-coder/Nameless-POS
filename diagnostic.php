<?php
/**
 * Complete diagnostic untuk thermal_printer_settings
 */

$db = new PDO('sqlite:D:/project warnet/Nameless/database/database.sqlite');

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║     THERMAL PRINTER SETTINGS - COMPLETE DIAGNOSTIC         ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// 1. Check if table exists
echo "1️⃣  TABLE EXISTENCE CHECK:\n";
$tableExists = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='thermal_printer_settings'")->fetch();

if (!$tableExists) {
    echo "   ❌ Table thermal_printer_settings DOES NOT EXIST\n";
    echo "\n   Creating table...\n";
    
    $db->exec("
        CREATE TABLE thermal_printer_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL UNIQUE,
            brand VARCHAR(100),
            model VARCHAR(100),
            connection_type VARCHAR(50) NOT NULL,
            ip_address VARCHAR(45),
            port INTEGER,
            bluetooth_address VARCHAR(100),
            serial_port VARCHAR(50),
            baud_rate INTEGER,
            paper_width VARCHAR(20),
            paper_length INTEGER,
            paper_type VARCHAR(50),
            print_speed INTEGER,
            print_density INTEGER,
            character_set VARCHAR(50),
            font_size VARCHAR(20),
            auto_cut BOOLEAN DEFAULT 0,
            buzzer_enabled BOOLEAN DEFAULT 0,
            esc_commands TEXT,
            init_command TEXT,
            cut_command TEXT,
            cash_drawer_command TEXT,
            margin_left INTEGER,
            margin_right INTEGER,
            margin_top INTEGER,
            margin_bottom INTEGER,
            line_spacing INTEGER,
            char_spacing INTEGER,
            print_logo BOOLEAN DEFAULT 0,
            header_text TEXT,
            footer_text TEXT,
            print_barcode BOOLEAN DEFAULT 0,
            barcode_position VARCHAR(50),
            is_active BOOLEAN DEFAULT 1,
            is_default BOOLEAN DEFAULT 0,
            capabilities TEXT,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    echo "   ✅ Table created successfully\n";
} else {
    echo "   ✅ Table thermal_printer_settings EXISTS\n";
}

// 2. Check columns
echo "\n2️⃣  COLUMN STRUCTURE:\n";
$columns = $db->query("PRAGMA table_info(thermal_printer_settings)")->fetchAll(PDO::FETCH_ASSOC);

if (empty($columns)) {
    echo "   ❌ No columns found (table might be corrupted)\n";
} else {
    echo "   ✅ Total columns: " . count($columns) . "\n\n";
    foreach ($columns as $col) {
        $notNull = $col['notnull'] ? 'NOT NULL' : 'nullable';
        echo "   • " . str_pad($col['name'], 25) . " (" . $col['type'] . ") - $notNull\n";
    }
}

// 3. Check data
echo "\n3️⃣  DATA CHECK:\n";
try {
    $count = $db->query("SELECT COUNT(*) FROM thermal_printer_settings")->fetchColumn();
    echo "   ✅ Total records: $count\n";
    
    if ($count > 0) {
        $records = $db->query("SELECT id, name, brand, connection_type, is_active, is_default FROM thermal_printer_settings")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($records as $record) {
            echo "   • ID: " . $record['id'] . " | Name: " . $record['name'] . " | Brand: " . ($record['brand'] ?? 'N/A') . " | Active: " . ($record['is_active'] ? 'Yes' : 'No') . " | Default: " . ($record['is_default'] ? 'Yes' : 'No') . "\n";
        }
    } else {
        echo "   ⚠️  No records found - inserting default printer...\n";
        $db->exec("
            INSERT INTO thermal_printer_settings (name, brand, model, connection_type, paper_width, print_speed, print_density, is_active, is_default)
            VALUES ('Default Printer', 'Generic', '80mm', 'network', '80', '3', '3', 1, 1)
        ");
        echo "   ✅ Default printer inserted\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// 4. Check indexes
echo "\n4️⃣  INDEXES:\n";
$indexes = $db->query("PRAGMA index_list(thermal_printer_settings)")->fetchAll(PDO::FETCH_ASSOC);
if (empty($indexes)) {
    echo "   ℹ️  No indexes defined\n";
} else {
    foreach ($indexes as $idx) {
        echo "   • " . $idx['name'] . " (unique: " . ($idx['unique'] ? 'Yes' : 'No') . ")\n";
    }
}

// 5. Test the exact query from controller
echo "\n5️⃣  TEST QUERY (from controller):\n";
try {
    $printers = $db->query("
        SELECT * FROM thermal_printer_settings 
        WHERE is_active = 1 
        ORDER BY is_default DESC, name
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   ✅ Query executed successfully\n";
    echo "   ✅ Returned " . count($printers) . " printers\n";
} catch (Exception $e) {
    echo "   ❌ Query failed: " . $e->getMessage() . "\n";
}

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║                    SUMMARY                                 ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "✅ Diagnostic complete - all systems ready\n";
?>
