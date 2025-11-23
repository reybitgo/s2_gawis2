<?php
/**
 * Monthly Quota Reset CRON Script
 * 
 * This script runs daily but only resets monthly quotas on the 1st of each month.
 * 
 * Hostinger CRON Setup:
 * Command: /usr/bin/php /home/u938213108/public_html/s2/crons/reset_monthly_quota.php
 * Schedule: Minute=1, Hour=0, Day=*, Month=*, Weekday=*
 * (Runs: Daily at 12:01 AM, but only executes on the 1st of each month)
 * 
 * Windows Task Scheduler (Local Development):
 * Action: C:\laragon\bin\php\php-8.x\php.exe
 * Arguments: C:\laragon\www\s2_gawis2\crons\reset_monthly_quota.php
 * Trigger: Daily, 12:01 AM
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\MonthlyQuotaTracker;
use Illuminate\Support\Facades\Log;

echo "=== Monthly Quota Reset CRON Job ===\n";
echo "Started at: " . now()->toDateTimeString() . "\n\n";

try {
    // Only run on the 1st day of the month
    $today = now()->day;
    if ($today !== 1) {
        echo "Quota reset only runs on the 1st of each month.\n";
        echo "Today is: " . now()->format('F j, Y') . " (Day {$today})\n";
        echo "Next reset: " . now()->startOfMonth()->addMonth()->format('F 1, Y') . "\n";
        exit(0);
    }
    $activeUsers = User::where('network_status', 'active')->get();
    $created = 0;
    $updated = 0;

    foreach ($activeUsers as $user) {
        $year = now()->year;
        $month = now()->month;

        // Create new tracker for current month
        $tracker = MonthlyQuotaTracker::firstOrCreate(
            [
                'user_id' => $user->id,
                'year' => $year,
                'month' => $month,
            ],
            [
                'total_pv_points' => 0,
                'required_quota' => $user->getMonthlyQuotaRequirement(),
                'quota_met' => false,
            ]
        );

        if ($tracker->wasRecentlyCreated) {
            $created++;
        } else {
            // Update existing tracker's quota in case package changed
            $tracker->required_quota = $user->getMonthlyQuotaRequirement();
            $tracker->save();
            $updated++;
        }
    }

    echo "SUCCESS!\n";
    echo "Active users: {$activeUsers->count()}\n";
    echo "New trackers created: {$created}\n";
    echo "Existing trackers updated: {$updated}\n";
    echo "Completed at: " . now()->toDateTimeString() . "\n";

    Log::info('Monthly quota reset completed via CRON', [
        'active_users' => $activeUsers->count(),
        'new_trackers' => $created,
        'updated_trackers' => $updated,
        'year' => now()->year,
        'month' => now()->month,
    ]);

    exit(0);

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    
    Log::error('Monthly quota reset CRON failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    
    exit(1);
}
