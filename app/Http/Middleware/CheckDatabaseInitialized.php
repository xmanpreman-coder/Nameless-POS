<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CheckDatabaseInitialized
{
    /**
     * Handle an incoming request.
     * Ensure database is initialized on first app run.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only check on first request (when storage marker doesn't exist)
        $markerFile = storage_path('.db-initialized');
        
        if (!file_exists($markerFile)) {
            try {
                // Run migrations silently
                Artisan::call('migrate', [
                    '--force' => true,
                    '--env' => 'production'
                ]);
                
                // Run seeder to create default admin user
                Artisan::call('db:seed', [
                    '--class' => 'DefaultAdminSeeder',
                    '--force' => true,
                    '--env' => 'production'
                ]);
                
                // Mark as initialized
                file_put_contents($markerFile, time());
                
                Log::info('[Database Init] First-run initialization completed');
            } catch (\Throwable $e) {
                Log::error('[Database Init] Error during initialization', [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $next($request);
    }
}
