<?php

$file = 'database/database.sqlite';
try {
    $pdo = new PDO("sqlite:$file");
    $migration = '2025_12_03_192218_add_foreign_key_to_brand_id_in_products_table';
    
    $stmt = $pdo->prepare("DELETE FROM migrations WHERE migration = ?");
    $stmt->execute([$migration]);
    
    echo "Deleted migration: $migration\n";
    echo "Rows affected: " . $stmt->rowCount() . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
