<?php
/**
 * Merge Script - Copy data dari database1.sqlite ke database.sqlite
 * Hanya copy data (users, products, categories, dll), tidak ubah schema
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$mainDb = 'D:/project warnet/Nameless/database/database.sqlite';
$oldDb = 'D:/project warnet/Nameless/database/database1.sqlite';

if (!file_exists($oldDb)) {
    die("Error: File database1.sqlite tidak ditemukan di: $oldDb\n");
}

if (!file_exists($mainDb)) {
    die("Error: File database.sqlite tidak ditemukan di: $mainDb\n");
}

try {
    // Buka koneksi ke main database
    $pdo = new PDO("sqlite:$mainDb");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to main database: $mainDb\n";
    echo "✓ Old database: $oldDb\n\n";
    
    // Attach old database
    $pdo->exec("ATTACH DATABASE '$oldDb' AS old_db");
    echo "✓ Attached old database\n\n";
    
    // Array tables untuk copy dengan mapping kolom
    $tables = [
        'users' => ['id', 'name', 'email', 'password', 'is_active', 'created_at', 'updated_at'],
        'categories' => ['id', 'category_code', 'category_name', 'created_at', 'updated_at'],
        'units' => ['id', 'name', 'short_name', 'created_at', 'updated_at'],
        'products' => ['id', 'category_id', 'product_name', 'product_code', 'product_quantity', 'product_cost', 'product_price', 'product_unit', 'product_stock_alert', 'product_order_tax', 'product_tax_type', 'created_at', 'updated_at'],
        'customers' => ['id', 'customer_name', 'customer_email', 'customer_phone', 'city', 'country', 'address', 'created_at', 'updated_at'],
        'suppliers' => ['id', 'supplier_name', 'supplier_email', 'supplier_phone', 'city', 'country', 'address', 'created_at', 'updated_at'],
    ];
    
    $totalImported = 0;
    $results = [];
    
    foreach ($tables as $table => $columns) {
        try {
            // Cek apakah tabel ada di old database
            $checkTable = $pdo->query("SELECT name FROM old_db.sqlite_master WHERE type='table' AND name='$table'");
            if (!$checkTable->fetch()) {
                echo "⚠ Table '$table' tidak ada di database1.sqlite - skip\n";
                continue;
            }
            
            // Ambil kolom yang ada di old database
            $tableInfo = $pdo->query("PRAGMA old_db.table_info($table)")->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP, 1);
            $availableColumns = array_keys($tableInfo);
            
            // Filter columns yang ada
            $validColumns = array_intersect($columns, $availableColumns);
            if (empty($validColumns)) {
                echo "⚠ Table '$table' tidak punya kolom yang valid - skip\n";
                continue;
            }
            
            $colString = implode(', ', $validColumns);
            
            // Count rows before
            $countBefore = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            
            // Copy data - gunakan INSERT OR IGNORE untuk handle duplicate keys
            $sql = "INSERT OR IGNORE INTO $table ($colString) SELECT $colString FROM old_db.$table";
            $affected = $pdo->exec($sql);
            
            // Count rows after
            $countAfter = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            
            $imported = $countAfter - $countBefore;
            $totalImported += $imported;
            
            $results[$table] = [
                'before' => $countBefore,
                'after' => $countAfter,
                'imported' => $imported,
                'columns' => count($validColumns) . '/' . count($columns)
            ];
            
            echo "✓ Table: $table\n";
            echo "  - Kolom: " . $results[$table]['columns'] . "\n";
            echo "  - Before: " . $countBefore . " rows\n";
            echo "  - After: " . $countAfter . " rows\n";
            echo "  - Imported: " . $imported . " rows\n\n";
            
        } catch (Exception $e) {
            echo "✗ Error processing table '$table': " . $e->getMessage() . "\n\n";
            $results[$table] = ['error' => $e->getMessage()];
        }
    }
    
    // Detach old database
    $pdo->exec("DETACH DATABASE old_db");
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "SUMMARY\n";
    echo str_repeat("=", 50) . "\n";
    echo "Total rows imported: $totalImported\n\n";
    
    echo "Details by table:\n";
    foreach ($results as $table => $result) {
        if (isset($result['error'])) {
            echo "  ✗ $table: ERROR - " . $result['error'] . "\n";
        } else {
            echo "  ✓ $table: " . $result['imported'] . " rows imported (total: " . $result['after'] . ")\n";
        }
    }
    
    echo "\n✓ Merge completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    die(1);
}
?>
