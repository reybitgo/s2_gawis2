<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Schema;

class ApplyDatabaseConfig
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Apply database config overrides
            $this->applyDatabaseOverrides();
        } catch (\Exception $e) {
            // If database is not available or migration hasn't run, silently continue
            // This prevents errors during installation or migration
        }

        return $next($request);
    }

    /**
     * Apply database configuration overrides to runtime config
     */
    private function applyDatabaseOverrides(): void
    {
        // Check if SystemSetting table exists
        if (!Schema::hasTable('system_settings')) {
            return;
        }

        // Get all system settings that correspond to config values
        $configMappings = [
            'app_name' => 'app.name',
            'app_env' => 'app.env',
            'app_debug' => 'app.debug',
            'app_url' => 'app.url',
            'timezone' => 'app.timezone',
            'language' => 'app.locale',
            'fallback_language' => 'app.fallback_locale',
            // Security settings
            'session_timeout_minutes' => 'session.lifetime',
            'max_login_attempts' => 'auth.max_attempts',
            'lockout_duration' => 'auth.lockout_duration',
        ];

        foreach ($configMappings as $settingKey => $configKey) {
            $setting = SystemSetting::where('key', $settingKey)->first();

            if ($setting) {
                // Convert value based on type
                $value = $setting->value;
                if ($setting->type === 'boolean') {
                    $value = (bool) $value;
                } elseif ($setting->type === 'integer') {
                    $value = (int) $value;
                }

                // Override runtime config
                config([$configKey => $value]);
            }
        }

        // Special handling for session timeout
        $sessionTimeoutEnabled = SystemSetting::where('key', 'session_timeout')->first();
        $sessionTimeoutMinutes = SystemSetting::where('key', 'session_timeout_minutes')->first();

        if ($sessionTimeoutEnabled && $sessionTimeoutMinutes) {
            if ($sessionTimeoutEnabled->value) {
                // Session timeout is enabled, use the configured minutes
                config(['session.lifetime' => (int) $sessionTimeoutMinutes->value]);
                config(['session.expire_on_close' => false]);
            } else {
                // Session timeout is disabled, use a very long lifetime
                config(['session.lifetime' => 43200]); // 720 hours (30 days)
                config(['session.expire_on_close' => false]);
            }
        }
    }
}
