<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DisableLivewire
{
    /**
     * Handle an incoming request.
     * If the request is a Livewire AJAX call (has X-Livewire header),
     * return a 404 to effectively disable Livewire endpoints at runtime.
     */
    public function handle(Request $request, Closure $next)
    {
        // Middleware intentionally left as a no-op to allow Livewire everywhere.
        // If you want to re-enable blocking in the future, add checks here.
        return $next($request);
    }
}
