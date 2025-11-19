<?php
/**
 * Complete Database Merge Script
 * Copy all data dari database1.sqlite ke database.sqlite
 * Mencakup semua tabel: users, products, customers, suppliers, sales, purchases, dll
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$mainDb = 'D:/project warnet/Nameless/database/database.sqlite';
$oldDb = 'D:/project warnet/Nameless/database/database1.sqlite';

if (!file_exists($oldDb)) {
    die("❌ Error: File database1.sqlite tidak ditemukan\n");
}

try {
    $pdo = new PDO("sqlite:$mainDb");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║         DATABASE MERGE - COMPLETE DATA IMPORT              ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n\n";
    
    // Attach old database
    $pdo->exec("ATTACH DATABASE '$oldDb' AS old_db");
    echo "✓ Connected to databases\n\n";
    
    // Get all tables from old database
    $oldTables = $pdo->query(
        "SELECT name FROM old_db.sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"
    )->fetchAll(PDO::FETCH_COLUMN);
    
    echo "🔍 Found " . count($oldTables) . " tables in old database\n\n";
    
    $totalImported = 0;
    $results = [];
    
    foreach ($oldTables as $table) {
        try {
            // Skip certain system tables
            if (in_array($table, ['migrations', 'failed_jobs', 'media'])) {
                echo "⊘ Skipping system table: $table\n";
                continue;
            }
            
            // Check if table exists in main DB
            $checkExist = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'");
            
            if (!$checkExist->fetch()) {
                // Table doesn't exist - create it from old DB
                echo "• Copying table structure: $table...";
                
                // Get create statement
                $createStmt = $pdo->query("SELECT sql FROM old_db.sqlite_master WHERE type='table' AND name='$table'")
                    ->fetchColumn();
                
                if ($createStmt) {
                    try {
                        $pdo->exec($createStmt);
                        echo " ✓ Created\n";
                    } catch (Exception $e) {
                        echo " (already exists)\n";
                    }
                }
            }
            
            // Get column names from old table
            $columns = $pdo->query("PRAGMA old_db.table_info($table)")
                ->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP, 1);
            
            if (empty($columns)) {
                echo "⚠ Table $table has no columns\n";
                continue;
            }
            
            $colNames = array_keys($columns);
            $colString = implode(', ', $colNames);
            
            // Count before
            $countBefore = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            
            // Copy data
            try {
                $sql = "INSERT OR IGNORE INTO $table ($colString) SELECT $colString FROM old_db.$table";
                $pdo->exec($sql);
            } catch (Exception $e) {
                // If columns don't match exactly, try with available columns
                $mainCols = $pdo->query("PRAGMA table_info($table)")
                    ->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP, 1);
                $mainColNames = array_keys($mainCols);
                
                $intersectCols = array_intersect($colNames, $mainColNames);
                if (!empty($intersectCols)) {
                    $intersectString = implode(', ', $intersectCols);
                    $sql = "INSERT OR IGNORE INTO $table ($intersectString) SELECT $intersectString FROM old_db.$table";
                    $pdo->exec($sql);
                }
            }
            
            // Count after
            $countAfter = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            $imported = $countAfter - $countBefore;
            $totalImported += $imported;
            
            $results[$table] = $imported;
            
            echo "✓ $table: " . $imported . " rows imported (total: " . $countAfter . ")\n";
            
        } catch (Exception $e) {
            echo "✗ Error with $table: " . $e->getMessage() . "\n";
            $results[$table] = 0;
        }
    }
    
    // Detach old database
    try {
        $pdo->exec("DETACH DATABASE old_db");
    } catch (Exception $e) {
        // Ignore detach errors
    }
    
    echo "\n╔════════════════════════════════════════════════════════════╗\n";
    echo "║                      SUMMARY                              ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n\n";
    
    $successCount = count(array_filter($results, fn($v) => $v > 0));
    echo "✓ Total tables imported: $successCount\n";
    echo "✓ Total rows imported: $totalImported\n\n";
    
    echo "Import Details:\n";
    foreach ($results as $table => $count) {
        if ($count > 0) {
            echo "  ✓ $table: $count rows\n";
        }
    }
    
    echo "\n✅ Merge completed successfully!\n";
    echo "🎉 Database ready for use!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    die(1);
}
?>
