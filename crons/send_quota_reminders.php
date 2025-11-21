<?php
/**
 * Send Quota Reminders CRON Script
 * 
 * This script sends reminder emails to users who haven't met their monthly quota.
 * Only sends reminders between 20th-28th of the month.
 * 
 * Hostinger CRON Setup:
 * Command: /usr/bin/php /home/u938213108/public_html/s2/crons/send_quota_reminders.php
 * Schedule: Minute=0, Hour=9, Day=25, Month=*, Weekday=*
 * (Runs: 25th of every month at 9:00 AM)
 * 
 * Windows Task Scheduler (Local Development):
 * Action: C:\laragon\bin\php\php-8.x\php.exe
 * Arguments: C:\laragon\www\s2_gawis2\crons\send_quota_reminders.php
 * Trigger: Monthly, 25th day, 9:00 AM
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
    // Only send reminders between 20th-28th of month
    $today = now()->day;
    if ($today < 20 || $today > 28) {
        echo "Reminders only sent between 20th-28th of the month.\n";
        echo "Today is: {$today}th of " . now()->format('F Y') . "\n";
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
