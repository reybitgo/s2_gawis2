<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\DirectSponsorsTracker;
use App\Services\RankAdvancementService;

class BackfillLegacySponsorships extends Command
{
    protected $signature = 'rank:backfill-legacy-sponsorships
                            {--dry-run : Run without making changes}
                            {--check-advancements : Check if any users qualify for advancement after backfill}';

    protected $description = 'Backfill existing sponsor_id relationships into direct_sponsors_tracker table';

    protected RankAdvancementService $rankService;

    public function __construct(RankAdvancementService $rankService)
    {
        parent::__construct();
        $this->rankService = $rankService;
    }

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $checkAdvancements = $this->option('check-advancements');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        
        $this->info('Starting legacy sponsorship backfill...');
        
        $totalBackfilled = 0;
        $totalSkipped = 0;
        $sponsorsToCheck = [];
        
        User::whereNotNull('sponsor_id')->chunk(100, function($users) use (&$totalBackfilled, &$totalSkipped, &$sponsorsToCheck, $isDryRun) {
            foreach ($users as $user) {
                $sponsor = $user->sponsor;
                
                if (!$sponsor) {
                    $totalSkipped++;
                    continue;
                }
                
                // Check if already tracked
                $exists = DirectSponsorsTracker::where('user_id', $sponsor->id)
                    ->where('sponsored_user_id', $user->id)
                    ->exists();
                
                if ($exists) {
                    $totalSkipped++;
                    continue;
                }
                
                if ($isDryRun) {
                    $this->line("Would backfill: Sponsor #{$sponsor->id} ({$sponsor->username}) → User #{$user->id} ({$user->username}, Rank: {$user->current_rank})");
                    $totalBackfilled++;
                } else {
                    try {
                        DirectSponsorsTracker::create([
                            'user_id' => $sponsor->id,
                            'sponsored_user_id' => $user->id,
                            'sponsored_at' => $user->created_at ?? now(),
                            'sponsored_user_rank_at_time' => $user->current_rank,
                            'sponsored_user_package_id' => $user->rank_package_id,
                            'counted_for_rank' => $user->current_rank,
                        ]);
                        
                        $totalBackfilled++;
                        
                        // Track sponsors who gained referrals
                        if (!in_array($sponsor->id, $sponsorsToCheck)) {
                            $sponsorsToCheck[] = $sponsor->id;
                        }
                        
                        if ($totalBackfilled % 50 === 0) {
                            $this->info("Backfilled {$totalBackfilled} legacy sponsorships...");
                        }
                    } catch (\Exception $e) {
                        $this->error("Failed: Sponsor #{$sponsor->id} ({$sponsor->username}) → User #{$user->id} ({$user->username}): {$e->getMessage()}");
                        $totalSkipped++;
                    }
                }
            }
        });
        
        $this->info("\n=== Backfill Summary ===");
        $this->info("Total backfilled: {$totalBackfilled}");
        $this->info("Total skipped: {$totalSkipped}");
        
        // Check for automatic advancements
        if ($checkAdvancements && !$isDryRun && count($sponsorsToCheck) > 0) {
            $this->info("\n=== Checking for Rank Advancements ===");
            $this->info("Checking " . count($sponsorsToCheck) . " sponsors...");
            
            $advancedCount = 0;
            
            $bar = $this->output->createProgressBar(count($sponsorsToCheck));
            $bar->start();
            
            foreach ($sponsorsToCheck as $sponsorId) {
                $sponsor = User::find($sponsorId);
                if ($sponsor && $sponsor->rankPackage) {
                    $advanced = $this->rankService->checkAndTriggerAdvancement($sponsor);
                    if ($advanced) {
                        $this->newLine();
                        $this->info("✓ Sponsor #{$sponsorId} ({$sponsor->username}) advanced to {$sponsor->fresh()->current_rank}!");
                        $advancedCount++;
                    }
                }
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine(2);
            
            $this->info("Total automatic advancements triggered: {$advancedCount}");
        }
        
        $this->newLine();
        $this->info('✓ Backfill completed successfully!');
        
        return Command::SUCCESS;
    }
}
