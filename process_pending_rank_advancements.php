<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\RankAdvancementService;

echo "=== Processing Pending Rank Advancements ===\n\n";

$rankService = app(RankAdvancementService::class);

// Get all users who have a rank package
$rankedUsers = User::whereNotNull('current_rank')
    ->whereNotNull('rank_package_id')
    ->with('rankPackage')
    ->get();

echo "Found {$rankedUsers->count()} ranked users to check\n\n";

$advanced = 0;
$ineligible = 0;
$topRank = 0;

foreach ($rankedUsers as $user) {
    $progress = $rankService->getRankAdvancementProgress($user);
    
    echo "Checking User ID {$user->id} ({$user->username}):\n";
    echo "  Current Rank: {$progress['current_rank']}\n";
    echo "  Sponsors: {$progress['sponsors_count']}/{$progress['required_sponsors']}\n";
    echo "  Progress: {$progress['progress_percentage']}%\n";
    
    if (!$progress['can_advance']) {
        echo "  ℹ Already at top rank\n\n";
        $topRank++;
        continue;
    }
    
    if ($progress['is_eligible']) {
        echo "  ✓ ELIGIBLE for advancement to {$progress['next_rank']}\n";
        echo "  Processing advancement...\n";
        
        $result = $rankService->checkAndTriggerAdvancement($user);
        
        if ($result) {
            // Refresh user to get updated rank
            $user->refresh();
            echo "  ✅ ADVANCED to {$user->current_rank}!\n\n";
            $advanced++;
        } else {
            echo "  ❌ Advancement failed (check logs)\n\n";
        }
    } else {
        echo "  ⏳ Not eligible yet (needs {$progress['remaining_sponsors']} more sponsors)\n\n";
        $ineligible++;
    }
}

echo "=== Processing Complete ===\n\n";
echo "📊 Summary:\n";
echo "  ✅ Successfully advanced: {$advanced}\n";
echo "  ⏳ Not yet eligible: {$ineligible}\n";
echo "  🏆 Already at top rank: {$topRank}\n";
echo "  📋 Total checked: {$rankedUsers->count()}\n\n";

if ($advanced > 0) {
    echo "🎉 {$advanced} user(s) have been advanced to their next rank!\n";
    echo "They will now see their new rank in the profile page.\n\n";
} else {
    echo "ℹ No users were eligible for advancement at this time.\n\n";
}

echo "✨ Done!\n";
