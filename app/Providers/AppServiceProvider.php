<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Transaction;
use App\Observers\UserObserver;
use App\Observers\TransactionObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure pagination to use Bootstrap styling
        Paginator::useBootstrap();

        // Register model observers for notifications
        User::observe(UserObserver::class);
        Transaction::observe(TransactionObserver::class);
    }
}
