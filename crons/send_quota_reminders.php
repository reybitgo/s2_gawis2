<?php
/**
 * Send Quota Reminders CRON Script
 * 
 * This script runs daily but only sends reminder emails on the 25th of each month.
 * Reminders are sent to users who haven't met their monthly quota.
 * 
 * Hostinger CRON Setup:
 * Command: /usr/bin/php /home/u938213108/public_html/s2/crons/send_quota_reminders.php
 * Schedule: Minute=0, Hour=9, Day=*, Month=*, Weekday=*
 * (Runs: Daily at 9:00 AM, but only sends reminders on the 25th of each month)
 * 
 * Windows Task Scheduler (Local Development):
 * Action: C:\laragon\bin\php\php-8.x\php.exe
 * Arguments: C:\laragon\www\s2_gawis2\crons\send_quota_reminders.php
 * Trigger: Daily, 9:00 AM
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MonthlyQuotaTracker;
use App\Notifications\QuotaReminderNotification;
use App\Services\MonthlyQuotaService;
use Illuminate\Support\Facades\Log;

echo "=== Send Quota Reminders CRON Job ===\n";
echo "Started at: " . now()->toDateTimeString() . "\n\n";

try {
    // Only send reminders on the 25th of the month
    $today = now()->day;
    if ($today !== 25) {
        echo "Quota reminders only sent on the 25th of each month.\n";
        echo "Today is: " . now()->format('F j, Y') . " (Day {$today})\n";
        echo "Next reminder: " . now()->startOfMonth()->addDays(24)->format('F 25, Y') . "\n";
        exit(0);
    }

    $year = now()->year;
    $month = now()->month;
    $quotaService = new MonthlyQuotaService();

    // Get users who haven't met quota
    $usersNotMet = MonthlyQuotaTracker::with('user')
        ->where('year', $year)
        ->where('month', $month)
        ->where('quota_met', false)
        ->where('required_quota', '>', 0) // Only users with quota requirement
        ->get();

    if ($usersNotMet->isEmpty()) {
        echo "No users need quota reminders this month.\n";
        echo "All users have either met their quota or have no quota requirement.\n";
        exit(0);
    }

    $sent = 0;
    $skipped = 0;
    $failed = 0;

    foreach ($usersNotMet as $tracker) {
        $user = $tracker->user;

        if (!$user) {
            $skipped++;
            continue;
        }

        if (!$user->email_verified_at) {
            echo "Skipped {$user->username}: No verified email\n";
            $skipped++;
            continue;
        }

        try {
            $status = $quotaService->getUserMonthlyStatus($user);
            $user->notify(new QuotaReminderNotification($status));
            $sent++;
            echo "Sent reminder to: {$user->username} ({$status['total_pv']}/{$status['required_quota']} PV)\n";
        } catch (\Exception $e) {
            $failed++;
            echo "Failed to send to {$user->username}: " . $e->getMessage() . "\n";
            Log::error('Failed to send quota reminder', [
                'user_id' => $user->id,
                'username' => $user->username,
                'error' => $e->getMessage(),
            ]);
        }
    }

    echo "\nSUCCESS!\n";
    echo "Users not met quota: {$usersNotMet->count()}\n";
    echo "Reminders sent: {$sent}\n";
    echo "Skipped (no verified email): {$skipped}\n";
    echo "Failed: {$failed}\n";
    echo "Completed at: " . now()->toDateTimeString() . "\n";

    Log::info('Quota reminders sent via CRON', [
        'sent' => $sent,
        'skipped' => $skipped,
        'failed' => $failed,
        'total_not_met' => $usersNotMet->count(),
        'year' => $year,
        'month' => $month,
    ]);

    exit(0);

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    
    Log::error('Quota reminders CRON failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    
    exit(1);
}
