<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Enforce2FA
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for guests or if not required
        if (!auth()->check()) {
            return $next($request);
        }

        // Check if 2FA is required globally
        $require2FA = SystemSetting::get('require_2fa', false);

        if (!$require2FA) {
            return $next($request);
        }

        $user = auth()->user();

        // Skip for admin users editing security settings to prevent lockout
        if ($request->routeIs('admin.system.settings') || $request->routeIs('admin.system.settings.update')) {
            return $next($request);
        }

        // Check if user has 2FA enabled
        if (!$user->two_factor_secret) {
            // Redirect to 2FA setup page
            return redirect()->route('profile.show')->with('error', 'Two-factor authentication is required. Please enable it in your profile.');
        }

        return $next($request);
    }
}
