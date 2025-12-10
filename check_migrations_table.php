<?php

$file = 'database/database.sqlite';
try {
    $pdo = new PDO("sqlite:$file");
    $stmt = $pdo->query("SELECT migration FROM migrations ORDER BY id DESC LIMIT 10");
    $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Recent migrations:\n";
    foreach ($migrations as $m) {
        echo "- $m\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
