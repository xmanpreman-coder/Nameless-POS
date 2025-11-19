<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$db = app('db');

// Get actual category IDs
$cats = $db->table('categories')->get();
foreach ($cats as $c) {
    echo $c->id . ': ' . $c->category_name . PHP_EOL;
}
