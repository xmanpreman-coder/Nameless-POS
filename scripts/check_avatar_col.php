<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$hasCol = Illuminate\Support\Facades\Schema::hasColumn('users', 'avatar');
echo "users table has 'avatar' column: " . ($hasCol ? "YES" : "NO") . "\n";

if ($hasCol) {
    $users = Illuminate\Support\Facades\DB::table('users')->select('id', 'name', 'email', 'avatar')->get();
    echo "Users with avatar data:\n";
    foreach ($users as $u) {
        echo "  id={$u->id} name={$u->name} avatar={$u->avatar}\n";
    }
}
