<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Services\RankAdvancementService;
use Illuminate\Support\Facades\DB;

echo "=== Testing Synchronous Rank Advancement ===\n\n";

// Get the Starter package
$starterPackage = Package::where('rank_name', 'Starter')->first();

if (!$starterPackage) {
    echo "❌ Starter package not found!\n";
    exit(1);
}

echo "📦 Package: {$starterPackage->name} (Rank: {$starterPackage->rank_name})\n";
echo "Required sponsors for advancement: {$starterPackage->required_direct_sponsors}\n\n";

// Find test_starter_60 (should have 3/5 sponsors)
$sponsor = User::where('username', 'test_starter_60')->first();

if (!$sponsor) {
    echo "❌ test_starter_60 not found!\n";
    exit(1);
}

echo "👤 Sponsor: {$sponsor->username} (ID: {$sponsor->id})\n";
echo "Current Rank: {$sponsor->current_rank}\n";

// Count current sponsors
$currentSponsorsCount = $sponsor->getSameRankSponsorsCount();
echo "Current Sponsors: {$currentSponsorsCount}/{$starterPackage->required_direct_sponsors}\n";
echo "Needed for advancement: " . ($starterPackage->required_direct_sponsors - $currentSponsorsCount) . "\n\n";

if ($currentSponsorsCount >= $starterPackage->required_direct_sponsors) {
    echo "⚠️  Sponsor is already eligible! Let's trigger advancement...\n\n";
    
    $rankService = app(RankAdvancementService::class);
    $result = $rankService->checkAndTriggerAdvancement($sponsor);
    
    if ($result) {
        $sponsor->refresh();
        echo "✅ ADVANCED! New rank: {$sponsor->current_rank}\n";
    } else {
        echo "❌ Advancement failed\n";
    }
    exit(0);
}

echo "=== Simulating New User Purchase ===\n\n";
echo "This demonstrates what happens when a user purchases a package:\n\n";

DB::beginTransaction();

try {
    // Create a test user with Starter rank
    $newUser = User::create([
        'username' => 'test_sync_' . time(),
        'fullname' => 'Sync Test User',
        'email' => 'sync_test_' . time() . '@test.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
        'sponsor_id' => $sponsor->id,
        'current_rank' => 'Starter',
        'rank_package_id' => $starterPackage->id,
        'network_status' => 'active',
    ]);
    
    echo "✓ New user created: {$newUser->username}\n";
    echo "  - Sponsor: {$sponsor->username}\n";
    echo "  - Rank: {$newUser->current_rank}\n\n";
    
    // Track sponsorship (THIS IS WHAT HAPPENS IN CHECKOUT)
    echo "🔄 Tracking sponsorship and checking advancement...\n";
    
    $rankService = app(RankAdvancementService::class);
    $advancementTriggered = $rankService->trackSponsorship($sponsor, $newUser);
    
    if ($advancementTriggered) {
        $sponsor->refresh();
        echo "✅ SYNCHRONOUS ADVANCEMENT SUCCESSFUL!\n\n";
        echo "Sponsor Details:\n";
        echo "  - Username: {$sponsor->username}\n";
        echo "  - Previous Rank: Starter\n";
        echo "  - NEW RANK: {$sponsor->current_rank} 🎉\n";
        echo "  - Rank Updated: {$sponsor->rank_updated_at->format('M d, Y H:i:s')}\n\n";
        
        echo "📋 This happened in the SAME REQUEST - no delays!\n";
        echo "Total sponsors after this purchase: {$sponsor->getSameRankSponsorsCount()}\n";
    } else {
        $newCount = $sponsor->getSameRankSponsorsCount();
        echo "⏳ Not eligible yet. Current count: {$newCount}/{$starterPackage->required_direct_sponsors}\n";
    }
    
    DB::rollBack(); // Rollback test data
    echo "\n✓ Test data rolled back (no permanent changes)\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ Error: {$e->getMessage()}\n";
}

echo "\n=== Test Complete ===\n";
echo "\nKey Takeaway:\n";
echo "When a user purchases a package, their sponsor's rank advancement\n";
echo "is checked and processed IMMEDIATELY in the same request.\n";
echo "No scheduled tasks, no delays - instant promotion! ⚡\n";
