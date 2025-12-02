<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\RankAdvancementService;

class ProcessRankAdvancements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rank:process-advancements {--user-id= : Process specific user ID only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending rank advancements for eligible users';

    /**
     * Execute the console command.
     */
    public function handle(RankAdvancementService $rankService)
    {
        $this->info('=== Processing Pending Rank Advancements ===');
        $this->newLine();

        // Check if specific user requested
        $userId = $this->option('user-id');
        
        if ($userId) {
            return $this->processSingleUser($userId, $rankService);
        }

        return $this->processAllUsers($rankService);
    }

    /**
     * Process rank advancement for a single user
     */
    private function processSingleUser(int $userId, RankAdvancementService $rankService): int
    {
        $user = User::find($userId);

        if (!$user) {
            $this->error("User ID {$userId} not found");
            return 1;
        }

        if (!$user->current_rank || !$user->rank_package_id) {
            $this->warn("User {$user->username} has no rank package");
            return 1;
        }

        $progress = $rankService->getRankAdvancementProgress($user);
        
        $this->info("User: {$user->username} (ID: {$user->id})");
        $this->info("Current Rank: {$progress['current_rank']}");
        $this->info("Sponsors: {$progress['sponsors_count']}/{$progress['required_sponsors']} ({$progress['progress_percentage']}%)");

        if (!$progress['can_advance']) {
            $this->warn('User is already at top rank');
            return 0;
        }

        if ($progress['is_eligible']) {
            $this->info("✓ ELIGIBLE for advancement to {$progress['next_rank']}");
            $this->info('Processing advancement...');
            
            if ($rankService->checkAndTriggerAdvancement($user)) {
                $user->refresh();
                $this->info("✅ Successfully advanced to {$user->current_rank}!");
                return 0;
            } else {
                $this->error('❌ Advancement failed. Check logs for details.');
                return 1;
            }
        } else {
            $this->warn("Not eligible yet (needs {$progress['remaining_sponsors']} more sponsors)");
            return 0;
        }
    }

    /**
     * Process rank advancements for all eligible users
     */
    private function processAllUsers(RankAdvancementService $rankService): int
    {
        $rankedUsers = User::whereNotNull('current_rank')
            ->whereNotNull('rank_package_id')
            ->with('rankPackage')
            ->get();

        $this->info("Found {$rankedUsers->count()} ranked users to check");
        $this->newLine();

        $advanced = 0;
        $ineligible = 0;
        $topRank = 0;

        $bar = $this->output->createProgressBar($rankedUsers->count());
        $bar->start();

        foreach ($rankedUsers as $user) {
            $progress = $rankService->getRankAdvancementProgress($user);
            
            if (!$progress['can_advance']) {
                $topRank++;
                $bar->advance();
                continue;
            }
            
            if ($progress['is_eligible']) {
                if ($rankService->checkAndTriggerAdvancement($user)) {
                    $user->refresh();
                    $this->newLine();
                    $this->info("✅ {$user->username} advanced to {$user->current_rank}");
                    $advanced++;
                }
            } else {
                $ineligible++;
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('=== Processing Complete ===');
        $this->newLine();
        $this->table(
            ['Status', 'Count'],
            [
                ['✅ Successfully advanced', $advanced],
                ['⏳ Not yet eligible', $ineligible],
                ['🏆 Already at top rank', $topRank],
                ['📋 Total checked', $rankedUsers->count()],
            ]
        );

        if ($advanced > 0) {
            $this->info("🎉 {$advanced} user(s) have been advanced to their next rank!");
        } else {
            $this->comment('ℹ No users were eligible for advancement at this time.');
        }

        return 0;
    }
}
