<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\PointsTracker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecalculateGPV extends Command
{
    protected $signature = 'ppv:recalculate-gpv {user_id?} {--force : Force recalculation for all users}';
    protected $description = 'Recalculate GPV for users from points_tracker history';

    public function handle(): int
    {
        $userId = $this->argument('user_id');
        $force = $this->option('force');

        if ($userId) {
            return $this->recalculateForUser($userId);
        }

        if ($force) {
            return $this->recalculateAll();
        }

        $this->info('Usage:');
        $this->line('  php artisan ppv:recalculate-gpv {user_id} - Recalculate for specific user');
        $this->line('  php artisan ppv:recalculate-gpv --force - Recalculate for all users');
        $this->line('  php artisan ppv:recalculate-gpv - Show this help');

        return Command::SUCCESS;
    }

    private function recalculateForUser(int $userId): int
    {
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return Command::FAILURE;
        }

        $this->info("Recalculating GPV for user {$user->id} ({$user->email})...");

        DB::beginTransaction();
        try {
            $totalGPV = PointsTracker::where('user_id', $userId)
                ->where('gpv', '>', 0)
                ->sum('gpv');

            $user->update([
                'current_gpv' => $totalGPV,
                'ppv_gpv_updated_at' => now(),
            ]);

            DB::commit();

            $this->info("GPV recalculated: {$totalGPV}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to recalculate GPV', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            $this->error("Failed to recalculate GPV: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    private function recalculateAll(): int
    {
        $this->warn('Recalculating GPV for all users...');
        $this->warn('This may take a while depending on data size');

        if (!$this->confirm('Do you wish to continue?')) {
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar(User::count());
        $bar->start();

        User::chunk(100, function ($users) use ($bar) {
            foreach ($users as $user) {
                try {
                    $totalGPV = PointsTracker::where('user_id', $user->id)
                        ->where('gpv', '>', 0)
                        ->sum('gpv');

                    $user->update([
                        'current_gpv' => $totalGPV,
                        'ppv_gpv_updated_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to recalculate GPV', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info('GPV recalculation completed for all users');

        return Command::SUCCESS;
    }
}
