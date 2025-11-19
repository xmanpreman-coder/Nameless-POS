<?php
$db = new PDO('sqlite:D:/project warnet/Nameless/database/database.sqlite');

echo "Checking user_printer_preferences table...\n\n";

// Check table existence
$tableExists = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='user_printer_preferences'")->fetch();

if (!$tableExists) {
    echo "❌ Table does not exist - creating...\n";
    $db->exec("
        CREATE TABLE user_printer_preferences (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            thermal_printer_setting_id INTEGER,
            is_active BOOLEAN DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (thermal_printer_setting_id) REFERENCES thermal_printer_settings(id)
        )
    ");
    echo "✅ Table created\n";
} else {
    echo "✅ Table exists\n";
}

// Check columns
echo "\nColumns:\n";
$columns = $db->query("PRAGMA table_info(user_printer_preferences)")->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo "  • " . $col['name'] . " (" . $col['type'] . ")\n";
}

// Ensure thermal_printer_setting_id column exists
$hasThermalPrinterId = array_column($columns, 'name');
if (!in_array('thermal_printer_setting_id', $hasThermalPrinterId)) {
    echo "\n⚠️  Adding missing column: thermal_printer_setting_id\n";
    try {
        $db->exec("ALTER TABLE user_printer_preferences ADD COLUMN thermal_printer_setting_id INTEGER");
        echo "✅ Column added\n";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

// Check data
echo "\nData check:\n";
$count = $db->query("SELECT COUNT(*) FROM user_printer_preferences")->fetchColumn();
echo "  Total records: $count\n";

echo "\n✅ user_printer_preferences table fixed!\n";
?>
