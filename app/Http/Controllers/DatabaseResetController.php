<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use App\Models\SystemSetting;

class DatabaseResetController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Middleware is applied in routes/web.php
    }

    /**
     * Reset the database to initial state.
     * This action requires admin privileges and confirmation.
     */
    public function reset(Request $request)
    {
        // Check if user has admin role
         

        // Check if confirmation parameter is provided
        if ($request->get('confirm') !== 'yes') {
            return $this->showConfirmationPage($request);
        }

        try {
            // Log the reset action
            Log::info('Database reset initiated', [
                'user_id' => Auth::id(),
                'user_email' => Auth::user() ? Auth::user()->email : null,
                'ip' => $request->ip(),
                'timestamp' => now()
            ]);

            // Clear all caches and logs before reset
            $this->clearSystemCaches();
            $this->clearLogs();

            // Run migrations to ensure all performance indexes are in place
            // $this->ensurePerformanceOptimizations();

            // Run the database reset seeder
            Artisan::call('db:seed', [
                '--class' => 'DatabaseResetSeeder',
            ]);

            // Get seeder output
            $output = Artisan::output();

            // Update reset count in system settings
            $this->updateResetCount();

            // Clear permission cache after reset
            Artisan::call('permission:cache-reset');

            Log::info('Database reset completed successfully', [
                'user_id' => Auth::id(),
                'output' => $output
            ]);

            // Log out the current user since their session may be invalidated
            if (Auth::check()) {
                Auth::logout();
            }

            // Invalidate the session and regenerate CSRF token
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Database reset completed successfully!',
                    'output' => nl2br(htmlspecialchars($output)),
                    'redirect' => route('login'),
                    'credentials' => [
                        'admin' => [
                            'username' => 'admin',
                            'email' => 'admin@gawisherbal.com',
                            'password' => 'Admin123!@#'
                        ],
                        'member' => [
                            'username' => 'member',
                            'email' => 'member@gawisherbal.com',
                            'password' => 'Member123!@#'
                        ]
                    ]
                ]);
            }

            // Store additional info for display
            $resetInfo = [
                'message' => 'Database reset completed successfully! All caches cleared and default users restored.',
                'credentials' => true
            ];

            return redirect()->route('login')
                ->with('success', $resetInfo['message'])
                ->with('reset_info', $resetInfo);

        } catch (\Exception $e) {
            Log::error('Database reset failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Database reset failed: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Database reset failed: ' . $e->getMessage());
        }
    }

    /**
     * Show confirmation page for database reset
     */
    private function showConfirmationPage(Request $request)
    {
        $lastReset = SystemSetting::get('last_reset_date');
        $resetCount = SystemSetting::get('reset_count', 0);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'requires_confirmation' => true,
                'message' => 'Database reset requires confirmation',
                'confirmation_url' => route('database.reset') . '?confirm=yes',
                'last_reset' => $lastReset,
                'reset_count' => $resetCount,
                'warning' => 'This action will permanently delete all data and restore default settings!'
            ]);
        }

        // For web requests, return confirmation view
        return view('admin.database-reset-confirm', [
            'last_reset' => $lastReset,
            'reset_count' => $resetCount
        ]);
    }

    /**
     * Update the reset count in system settings
     */
    private function updateResetCount()
    {
        try {
            $currentCount = SystemSetting::get('reset_count', 0);
            SystemSetting::set('reset_count', $currentCount + 1, 'integer', 'Number of times database has been reset');
            SystemSetting::set('last_reset_date', now()->toISOString(), 'string', 'Last database reset timestamp');
        } catch (\Exception $e) {
            // If updating fails, continue anyway as reset was successful
            Log::warning('Failed to update reset count', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get reset status information (for API or dashboard)
     */
    public function status(Request $request)
    {
        // Check admin access
        if (!Auth::user() || !Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $lastReset = SystemSetting::get('last_reset_date');
        $resetCount = SystemSetting::get('reset_count', 0);

        return response()->json([
            'success' => true,
            'data' => [
                'last_reset' => $lastReset,
                'reset_count' => $resetCount,
                'current_user' => Auth::user()->email,
                'can_reset' => Auth::user()->hasRole('admin')
            ]
        ]);
    }

    /**
     * Clear all system caches using optimize:clear
     */
    private function clearSystemCaches()
    {
        try {
            // Use optimize:clear to clear all caches at once
            Artisan::call('optimize:clear');

            Log::info('System caches cleared during database reset');
        } catch (\Exception $e) {
            Log::warning('Failed to clear some caches during reset', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clear all log files
     */
    private function clearLogs()
    {
        try {
            $logPath = storage_path('logs');

            if (File::exists($logPath)) {
                // Get all log files in the logs directory
                $logFiles = File::glob($logPath . '/*.log');

                $clearedFiles = [];
                foreach ($logFiles as $logFile) {
                    if (File::exists($logFile)) {
                        // Clear the log file by writing empty content
                        File::put($logFile, '');
                        $clearedFiles[] = basename($logFile);
                    }
                }

                Log::info('Log files cleared during database reset', [
                    'cleared_files' => $clearedFiles,
                    'total_files' => count($clearedFiles)
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear log files during reset', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Ensure all performance optimizations are in place
     * Includes: indexes, migrations, and cache setup
     */
    private function ensurePerformanceOptimizations()
    {
        try {
            Log::info('Ensuring performance optimizations are in place');

            // Use migrate:fresh to drop all tables and re-run all migrations for a clean slate
            // Artisan::call('migrate:fresh', ['--force' => true]);

            Log::info('Performance optimizations completed', [
                'migrations_output' => Artisan::output()
            ]);

        } catch (\Exception $e) {
            Log::warning('Failed to apply some performance optimizations during reset', [
                'error' => $e->getMessage()
            ]);
            // Don't throw - reset should continue even if optimizations fail
        }
    }

}