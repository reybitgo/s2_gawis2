<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConditionalEmailVerification
{
    /**
     * Handle an incoming request.
     *
     * This middleware does NOT block users from accessing routes.
     * Email verification is optional - users can use the system without verified emails.
     * Email sending logic in the application checks hasVerifiedEmail() and skips sending when not verified.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Email verification is now optional - never block access
        // Users without verified emails can use all features
        // Email notifications are handled conditionally throughout the app
        return $next($request);
    }
}
