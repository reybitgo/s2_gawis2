<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CustomAuthServiceProvider extends ServiceProvider
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
        // Custom authentication logic for username or email
        Fortify::authenticateUsing(function (Request $request) {
            $user = null;
            $login = $request->input('email'); // Fortify uses 'email' as input name

            // Check if input is email or username
            if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                // Login with email
                $user = User::where('email', $login)->first();
            } else {
                // Login with username
                $user = User::where('username', $login)->first();
            }

            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }

            return null;
        });

        // Use custom user creation action
        Fortify::createUsersUsing(\App\Actions\Fortify\CreateNewUser::class);

        // Conditionally enable email verification
        $this->configureEmailVerification();
    }

    /**
     * Configure email verification based on system settings
     */
    protected function configureEmailVerification()
    {
        // Override the default email verification behavior
        Fortify::verifyEmailView(function () {
            $emailVerificationEnabled = \App\Models\SystemSetting::get('email_verification_enabled', true);

            if (!$emailVerificationEnabled) {
                // If verification is disabled, redirect to dashboard
                return redirect()->route('dashboard');
            }

            return view('auth.verify-email');
        });
    }
}
