<?php
$db = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');

$users = $db->query('SELECT id, name, avatar FROM users WHERE id > 0 LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);

echo "=== USERS AVATAR CHECK ===\n";
foreach ($users as $u) {
    $avatar = $u['avatar'] ?? 'NULL';
    $exists = '';
    if ($avatar && $avatar !== 'NULL') {
        $path = __DIR__ . '/storage/app/public/' . $avatar;
        $exists = file_exists($path) ? '✓ EXISTS' : '✗ NOT FOUND';
    }
    echo "ID: {$u['id']}, Name: {$u['name']}, Avatar: {$avatar} {$exists}\n";
}
?>
