<?php

$file = 'database/database.sqlite';
try {
    $pdo = new PDO("sqlite:$file");
    $stmt = $pdo->query("PRAGMA table_info(products)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasBrandId = false;
    foreach ($columns as $col) {
        echo "Column: " . $col['name'] . " (" . $col['type'] . ")\n";
        if ($col['name'] === 'brand_id') {
            $hasBrandId = true;
        }
    }
    
    if ($hasBrandId) {
        echo "\nbrand_id column EXISTS.\n";
    } else {
        echo "\nbrand_id column MISSING.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
