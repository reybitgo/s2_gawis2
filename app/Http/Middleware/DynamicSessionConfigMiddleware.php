<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SystemSetting;

class DynamicSessionConfigMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Update session configuration based on system settings
        try {
            $sessionTimeoutEnabled = SystemSetting::get('session_timeout', true);
            $sessionTimeoutMinutes = SystemSetting::get('session_timeout_minutes', 15);

            if ($sessionTimeoutEnabled) {
                // Set session lifetime dynamically
                config(['session.lifetime' => (int) $sessionTimeoutMinutes]);

                // Also update the session manager's configuration
                $sessionManager = app('session');
                $sessionConfig = config('session');
                $sessionConfig['lifetime'] = (int) $sessionTimeoutMinutes;

                // Restart session with new configuration
                if ($sessionManager->isStarted()) {
                    $sessionManager->save();
                }
            }
        } catch (\Exception $e) {
            // Silently fail if database is not available
            // This prevents breaking the application during setup
        }

        return $next($request);
    }
}
