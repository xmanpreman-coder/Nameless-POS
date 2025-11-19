<?php
/**
 * Check and fix permissions for printer routes
 */

$db = new PDO('sqlite:D:/project warnet/Nameless/database/database.sqlite');

echo "Checking permissions and roles...\n\n";

// Check if permission exists
$perm = $db->query("SELECT * FROM permissions WHERE name = 'access_settings'")->fetch();

if (!$perm) {
    echo "✓ Creating permission: access_settings\n";
    $db->exec("INSERT INTO permissions (name, guard_name) VALUES ('access_settings', 'web')");
} else {
    echo "✓ Permission access_settings already exists\n";
}

// Check if admin role has this permission
$adminRole = $db->query("SELECT id FROM roles WHERE name = 'admin'")->fetchColumn();

if ($adminRole) {
    echo "✓ Admin role exists (ID: $adminRole)\n";
    
    $accessSettingsPerm = $db->query("SELECT id FROM permissions WHERE name = 'access_settings'")->fetchColumn();
    
    if ($accessSettingsPerm) {
        $hasPermission = $db->query(
            "SELECT * FROM role_has_permissions WHERE role_id = ? AND permission_id = ?",
            [$adminRole, $accessSettingsPerm]
        )->fetch();
        
        if (!$hasPermission) {
            echo "✓ Assigning access_settings permission to admin role...\n";
            $db->exec("INSERT INTO role_has_permissions (role_id, permission_id) VALUES ($adminRole, $accessSettingsPerm)");
        } else {
            echo "✓ Admin role already has access_settings permission\n";
        }
    }
}

// Check admin user
$adminUser = $db->query("SELECT * FROM users WHERE email = 'super.admin@test.com'")->fetch();
if ($adminUser) {
    echo "✓ Admin user exists (ID: " . $adminUser['id'] . ")\n";
    
    $adminRole = $db->query("SELECT id FROM roles WHERE name = 'admin'")->fetchColumn();
    if ($adminRole) {
        $hasRole = $db->query(
            "SELECT * FROM model_has_roles WHERE model_id = ? AND role_id = ? AND model_type = 'App\\\\Models\\\\User'",
            [$adminUser['id'], $adminRole]
        )->fetch();
        
        if (!$hasRole) {
            echo "✓ Assigning admin role to user...\n";
            $db->exec("INSERT INTO model_has_roles (role_id, model_id, model_type) VALUES ($adminRole, " . $adminUser['id'] . ", 'App\\\\Models\\\\User')");
        } else {
            echo "✓ Admin user already has admin role\n";
        }
    }
}

echo "\n✅ Permissions verified and fixed!\n";
?>
