<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EWalletSecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = Auth::user();

        // Check if user is authenticated
        if (!$user) {
            return redirect()->route('login');
        }

        // Email verification is now optional - users can use the system without verified emails
        // Email notifications are handled conditionally throughout the app

        // Check for required permissions
        foreach ($permissions as $permission) {
            if (!$user->can($permission)) {
                abort(403, 'Unauthorized access to e-wallet feature.');
            }
        }

        // Log security-sensitive actions
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            \Log::info('E-wallet security action', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'action' => $request->route()?->getName(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()
            ]);
        }

        return $next($request);
    }
}