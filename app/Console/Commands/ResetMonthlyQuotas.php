<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\MonthlyQuotaTracker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResetMonthlyQuotas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quota:reset-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset monthly quotas for all active users (run on 1st of each month)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Monthly Quota Reset ===');
        $this->info('Started at: ' . now()->toDateTimeString());
        $this->newLine();

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
                $this->line("Created tracker for: {$user->username} (Quota: {$tracker->required_quota} PV)");
            } else {
                // Update existing tracker's quota in case package changed
                $tracker->required_quota = $user->getMonthlyQuotaRequirement();
                $tracker->save();
                $updated++;
            }
        }

        $this->newLine();
        $this->info("Monthly quota reset completed!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Active users', $activeUsers->count()],
                ['New trackers created', $created],
                ['Existing trackers updated', $updated],
                ['Month', now()->format('F Y')],
            ]
        );

        Log::info('Monthly quota reset completed', [
            'active_users' => $activeUsers->count(),
            'new_trackers' => $created,
            'updated_trackers' => $updated,
            'year' => now()->year,
            'month' => now()->month,
        ]);

        return Command::SUCCESS;
    }
}
