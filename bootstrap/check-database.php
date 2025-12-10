<?php
/**
 * Check and initialize database on first app run
 * Called automatically by the app on every request (safe - only runs once)
 */

try {
    $basePath = dirname(__DIR__);
    $dbPath = $basePath . '/database/database.sqlite';
    $dbDir = dirname($dbPath);
    
    // Ensure database directory exists
    if (!is_dir($dbDir)) {
        @mkdir($dbDir, 0755, true);
    }
    
    // Check if database is empty or doesn't exist
    $needsInit = !file_exists($dbPath) || filesize($dbPath) < 5000;
    
    if ($needsInit && !file_exists($basePath . '/storage/.db-initialized')) {
        // Run migrations
        $output = [];
        $exitCode = 0;
        
        $envOptions = [
            '--force',
            '--env=production',
        ];
        
        // Run migration
        exec("php {$basePath}/artisan migrate " . implode(' ', $envOptions), $output, $exitCode);
        
        if ($exitCode === 0) {
            // Run seeder to create default admin
            exec("php {$basePath}/artisan db:seed --class=DefaultAdminSeeder --force --env=production", $output, $exitCode);
            
            // Mark as initialized
            @mkdir($basePath . '/storage', 0755, true);
            file_put_contents($basePath . '/storage/.db-initialized', time());
            
            error_log('[Database Init] Initialization completed successfully');
        } else {
            error_log('[Database Init] Migration failed with exit code: ' . $exitCode);
        }
    }
} catch (\Throwable $e) {
    error_log('[Database Init] Error: ' . $e->getMessage());
}
