<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\MonthlyQuotaTracker;
use App\Notifications\QuotaReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendQuotaReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quota:send-reminders {--force : Send reminders even if not the 25th}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send monthly quota reminders to users who have not met their quota';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if it's the 25th of the month (unless --force is used)
        $today = now()->day;
        if (!$this->option('force') && $today < 20) {
            $this->info("Quota reminders are sent on or after the 20th of each month. Use --force to override.");
            return 0;
        }

        $this->info("Sending monthly quota reminders...");
        $this->newLine();

        $currentYear = now()->year;
        $currentMonth = now()->month;
        $monthName = now()->format('F');
        $daysRemaining = now()->endOfMonth()->diffInDays(now());

        // Get all network active users
        $activeUsers = User::where('network_status', 'active')->get();

        if ($activeUsers->isEmpty()) {
            $this->info("No active users found.");
            return 0;
        }

        $this->info("Found {$activeUsers->count()} active users.");
        $this->newLine();

        $remindersSent = 0;
        $alreadyQualified = 0;
        $noQuotaRequired = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($activeUsers->count());
        $progressBar->start();

        foreach ($activeUsers as $user) {
            try {
                // Get or create tracker for current month
                $tracker = MonthlyQuotaTracker::where('user_id', $user->id)
                    ->where('year', $currentYear)
                    ->where('month', $currentMonth)
                    ->first();

                if (!$tracker) {
                    $tracker = MonthlyQuotaTracker::getOrCreateForCurrentMonth($user);
                }

                // Skip if quota not enforced (requirement is 0)
                if ($tracker->required_quota <= 0) {
                    $noQuotaRequired++;
                    $progressBar->advance();
                    continue;
                }

                // Skip if user already met quota
                if ($tracker->quota_met) {
                    $alreadyQualified++;
                    $progressBar->advance();
                    continue;
                }

                // Send reminder notification
                $remainingPV = max(0, $tracker->required_quota - $tracker->total_pv_points);

                $user->notify(new QuotaReminderNotification(
                    $tracker->total_pv_points,
                    $tracker->required_quota,
                    $remainingPV,
                    $monthName,
                    $currentYear,
                    $daysRemaining
                ));

                $remindersSent++;

                Log::info('Quota Reminder Sent', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'current_pv' => $tracker->total_pv_points,
                    'required_quota' => $tracker->required_quota,
                    'remaining_pv' => $remainingPV,
                    'days_remaining' => $daysRemaining,
                ]);

            } catch (\Exception $e) {
                $errors++;
                Log::error('Failed to send quota reminder', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info("=== Quota Reminder Summary ===");
        $this->info("Total active users: {$activeUsers->count()}");
        $this->info("Reminders sent: {$remindersSent}");
        $this->info("Already qualified: {$alreadyQualified}");
        $this->info("No quota required: {$noQuotaRequired}");
        if ($errors > 0) {
            $this->error("Errors: {$errors}");
        }
        $this->newLine();

        Log::info('Quota Reminder Command Completed', [
            'total_users' => $activeUsers->count(),
            'reminders_sent' => $remindersSent,
            'already_qualified' => $alreadyQualified,
            'no_quota_required' => $noQuotaRequired,
            'errors' => $errors,
            'month' => $monthName,
            'year' => $currentYear,
        ]);

        return 0;
    }
}
