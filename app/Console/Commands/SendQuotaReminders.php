<?php

namespace App\Console\Commands;

use App\Models\MonthlyQuotaTracker;
use App\Notifications\QuotaReminderNotification;
use App\Services\MonthlyQuotaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendQuotaReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quota:send-reminders {--force : Send reminders regardless of date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder notifications to users who haven\'t met their monthly quota';

    protected MonthlyQuotaService $quotaService;

    /**
     * Create a new command instance.
     */
    public function __construct(MonthlyQuotaService $quotaService)
    {
        parent::__construct();
        $this->quotaService = $quotaService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Only send reminders between 20th-28th of month (or if --force)
        $today = now()->day;
        if (!$this->option('force') && ($today < 20 || $today > 28)) {
            $this->warn('Quota reminders are only sent between 20th-28th of the month.');
            $this->info("Today is: {$today}th of " . now()->format('F Y'));
            return Command::SUCCESS;
        }

        $this->info('=== Send Quota Reminders ===');
        $this->info('Started at: ' . now()->toDateTimeString());
        $this->newLine();

        $year = now()->year;
        $month = now()->month;

        // Get users who haven't met quota
        $usersNotMet = MonthlyQuotaTracker::with('user')
            ->where('year', $year)
            ->where('month', $month)
            ->where('quota_met', false)
            ->where('required_quota', '>', 0) // Only users with quota requirement
            ->get();

        if ($usersNotMet->isEmpty()) {
            $this->info('No users need quota reminders this month.');
            $this->info('All users have either met their quota or have no quota requirement.');
            return Command::SUCCESS;
        }

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        $this->withProgressBar($usersNotMet, function ($tracker) use (&$sent, &$skipped, &$failed) {
            $user = $tracker->user;

            if (!$user) {
                $skipped++;
                return;
            }

            if (!$user->email_verified_at) {
                $skipped++;
                return;
            }

            try {
                $status = $this->quotaService->getUserMonthlyStatus($user);
                $user->notify(new QuotaReminderNotification($status));
                $sent++;
            } catch (\Exception $e) {
                $failed++;
                Log::error('Failed to send quota reminder', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'error' => $e->getMessage(),
                ]);
            }
        });

        $this->newLine(2);
        $this->info('Quota reminders completed!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Users not met quota', $usersNotMet->count()],
                ['Reminders sent', $sent],
                ['Skipped (no verified email)', $skipped],
                ['Failed', $failed],
            ]
        );

        Log::info('Quota reminders sent', [
            'sent' => $sent,
            'skipped' => $skipped,
            'failed' => $failed,
            'total_not_met' => $usersNotMet->count(),
            'year' => $year,
            'month' => $month,
        ]);

        return Command::SUCCESS;
    }
}
