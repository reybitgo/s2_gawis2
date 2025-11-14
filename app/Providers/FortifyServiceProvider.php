<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind custom registration response
        $this->app->singleton(
            \Laravel\Fortify\Contracts\RegisterResponse::class,
            \App\Http\Responses\RegisterResponse::class
        );

        // Bind custom email verification response
        $this->app->singleton(
            \Laravel\Fortify\Contracts\VerifyEmailResponse::class,
            \App\Http\Responses\VerifyEmailResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        // Custom authentication logic to check for suspended users
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)
                ->orWhere('username', $request->email)
                ->first();

            if ($user && Hash::check($request->password, $user->password)) {
                // Refresh user data to get latest suspended_at value
                $user->refresh();

                if ($user->suspended_at !== null) {
                    throw ValidationException::withMessages([
                        'email' => ['Your account has been suspended. Please contact support.'],
                    ]);
                }
                return $user;
            }
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(3)->by($throttleKey)->response(function () {
                return response('Too Many Attempts.', 429);
            });
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(3)->by($request->session()->get('login.id'))->response(function () {
                return response('Too Many Attempts.', 429);
            });
        });

        Fortify::loginView(function () {
            return view('auth.login');
        });

        Fortify::registerView(function (Request $request) {
            // Track referral clicks if ref parameter is present
            if ($request->has('ref')) {
                $refCode = $request->query('ref');
                $user = \App\Models\User::where('referral_code', $refCode)->first();

                if ($user) {
                    // Track click
                    \App\Models\ReferralClick::create([
                        'user_id' => $user->id,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ]);

                    // Store in session for form pre-fill
                    session(['referral_code' => $refCode]);
                }
            }

            return view('auth.register');
        });

        Fortify::requestPasswordResetLinkView(function () {
            return view('auth.forgot-password');
        });

        Fortify::resetPasswordView(function ($request) {
            return view('auth.reset-password', ['request' => $request]);
        });

        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        Fortify::twoFactorChallengeView(function () {
            return view('auth.two-factor-challenge');
        });

        Fortify::confirmPasswordView(function () {
            return view('auth.confirm-password');
        });
    }
}
