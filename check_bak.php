<?php

$file = 'database/database.sqlite.bak';
echo "Checking file: $file\n";

if (!file_exists($file)) {
    echo "File not found.\n";
    exit;
}

try {
    $pdo = new PDO("sqlite:$file");
    
    $tables = ['products', 'categories', 'brands', 'sales', 'users'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT count(*) FROM $table");
            echo "  $table: " . $stmt->fetchColumn() . "\n";
        } catch (Exception $e) {
            echo "  $table: error (" . $e->getMessage() . ")\n";
        }
    }
    
} catch (Exception $e) {
    echo "Connection error: " . $e->getMessage() . "\n";
}
