<?php

$files = [
    'database/database.sqlite',
    'database/database.sqlite.bak',
    'database/database.sqlite.backup-2025-12-03'
];

foreach ($files as $file) {
    echo "Checking file: $file\n";
    if (!file_exists($file)) {
        echo "  File not found.\n";
        continue;
    }

    try {
        $pdo = new PDO("sqlite:$file");
        
        // Check products
        try {
            $stmt = $pdo->query("SELECT count(*) FROM products");
            echo "  Products: " . $stmt->fetchColumn() . "\n";
        } catch (Exception $e) {
            echo "  Products table error: " . $e->getMessage() . "\n";
        }

        // Check categories
        try {
            $stmt = $pdo->query("SELECT count(*) FROM categories");
            echo "  Categories: " . $stmt->fetchColumn() . "\n";
        } catch (Exception $e) {
            // Try product_categories if categories fails
             try {
                $stmt = $pdo->query("SELECT count(*) FROM product_categories");
                echo "  Product Categories: " . $stmt->fetchColumn() . "\n";
            } catch (Exception $e2) {
                echo "  Categories table error: " . $e->getMessage() . "\n";
            }
        }
        
    } catch (Exception $e) {
        echo "  Connection error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
