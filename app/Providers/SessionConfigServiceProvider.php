<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\SystemSetting;

class SessionConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Update session configuration based on system settings
        // This runs after the database is available
        if ($this->app->runningInConsole() === false) {
            try {
                $sessionTimeoutMinutes = SystemSetting::get('session_timeout_minutes', 15);
                config(['session.lifetime' => (int) $sessionTimeoutMinutes]);

                // Also update the SESSION_LIFETIME environment for consistency
                if (function_exists('putenv')) {
                    putenv("SESSION_LIFETIME={$sessionTimeoutMinutes}");
                }
            } catch (\Exception $e) {
                // Silently fail if database is not available yet
                // This can happen during migrations or initial setup
            }
        }
    }
}
