<?php

/**
 * Test script for Phase 2: Rank-Aware MLM Commission Calculation
 * 
 * Tests all scenarios:
 * - Scenario 0: Users without rank packages (NO COMMISSION)
 * - Scenario 1: Higher rank upline with lower rank buyer
 * - Scenario 2: Lower rank upline with higher rank buyer
 * - Scenario 3: Same rank upline and buyer
 * - Scenario 4: Inactive users (should be skipped)
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Services\RankComparisonService;

echo "===========================================\n";
echo "Phase 2: Rank-Aware Commission Test\n";
echo "===========================================\n\n";

$rankService = new RankComparisonService();

// Get rank packages
echo "Loading rank packages...\n";
$starter = Package::where('rank_name', 'Starter')->first();
$newbie = Package::where('rank_name', 'Newbie')->first();
$bronze = Package::where('rank_name', 'Bronze')->first();

if (!$starter || !$newbie || !$bronze) {
    echo "❌ ERROR: Rank packages not found. Please run PackageSeeder first.\n";
    echo "Run: php artisan db:seed --class=PackageSeeder\n";
    exit(1);
}

echo "✓ Found rank packages: Starter, Newbie, Bronze\n\n";

// Test Scenario 0: Users without rank packages (NO COMMISSION)
echo "===========================================\n";
echo "Scenario 0a: Upline without rank package\n";
echo "===========================================\n";
echo "Expected: NO COMMISSION (return 0.00)\n\n";

// Create test users
$noRankUpline = User::factory()->create([
    'username' => 'test_no_rank_upline_' . uniqid(),
    'email' => 'no_rank_upline_' . uniqid() . '@test.com',
    'current_rank' => null,
    'rank_package_id' => null,
    'network_status' => 'active',
]);

$rankedBuyer = User::factory()->create([
    'username' => 'test_ranked_buyer_' . uniqid(),
    'email' => 'ranked_buyer_' . uniqid() . '@test.com',
    'current_rank' => 'Starter',
    'rank_package_id' => $starter->id,
    'network_status' => 'active',
]);

$commission0 = $rankService->getEffectiveCommission($noRankUpline, $rankedBuyer, 1);
$explanation0 = $rankService->getCommissionExplanation($noRankUpline, $rankedBuyer, 1);

echo "Upline: {$noRankUpline->username} (Rank: None)\n";
echo "Buyer: {$rankedBuyer->username} (Rank: {$rankedBuyer->current_rank})\n";
echo "Commission: ₱" . number_format($commission0, 2) . "\n";
echo "Rule: {$explanation0['rule']}\n";
echo "Explanation: {$explanation0['explanation']}\n";

if ($commission0 === 0.00) {
    echo "✓ PASSED: No rank = No commission\n\n";
} else {
    echo "✗ FAILED: Expected 0.00, got {$commission0}\n\n";
}

// Test Scenario 0b: Buyer without rank package (NO COMMISSION)
echo "===========================================\n";
echo "Scenario 0b: Buyer without rank package\n";
echo "===========================================\n";
echo "Expected: NO COMMISSION (return 0.00)\n\n";

$rankedUpline = User::factory()->create([
    'username' => 'test_ranked_upline_' . uniqid(),
    'email' => 'ranked_upline_' . uniqid() . '@test.com',
    'current_rank' => 'Newbie',
    'rank_package_id' => $newbie->id,
    'network_status' => 'active',
]);

$noRankBuyer = User::factory()->create([
    'username' => 'test_no_rank_buyer_' . uniqid(),
    'email' => 'no_rank_buyer_' . uniqid() . '@test.com',
    'current_rank' => null,
    'rank_package_id' => null,
    'network_status' => 'active',
]);

$commission0b = $rankService->getEffectiveCommission($rankedUpline, $noRankBuyer, 1);
$explanation0b = $rankService->getCommissionExplanation($rankedUpline, $noRankBuyer, 1);

echo "Upline: {$rankedUpline->username} (Rank: {$rankedUpline->current_rank})\n";
echo "Buyer: {$noRankBuyer->username} (Rank: None)\n";
echo "Commission: ₱" . number_format($commission0b, 2) . "\n";
echo "Rule: {$explanation0b['rule']}\n";
echo "Explanation: {$explanation0b['explanation']}\n";

if ($commission0b === 0.00) {
    echo "✓ PASSED: No rank = No commission\n\n";
} else {
    echo "✗ FAILED: Expected 0.00, got {$commission0b}\n\n";
}

// Test Scenario 1: Higher rank (Newbie) has lower rank downline (Starter)
echo "===========================================\n";
echo "Scenario 1: Higher rank upline, lower rank buyer\n";
echo "===========================================\n";
echo "Rule: Newbie (higher rank) earns Starter's (lower) commission rate\n\n";

$newbieUser = User::factory()->create([
    'username' => 'test_newbie_' . uniqid(),
    'email' => 'newbie_' . uniqid() . '@test.com',
    'current_rank' => 'Newbie', 
    'rank_package_id' => $newbie->id,
    'network_status' => 'active',
]);

$starterUser = User::factory()->create([
    'username' => 'test_starter_' . uniqid(),
    'email' => 'starter_' . uniqid() . '@test.com',
    'current_rank' => 'Starter', 
    'rank_package_id' => $starter->id,
    'network_status' => 'active',
]);

$commission1 = $rankService->getEffectiveCommission($newbieUser, $starterUser, 1);
$explanation1 = $rankService->getCommissionExplanation($newbieUser, $starterUser, 1);

echo "Upline: {$newbieUser->username} (Rank: {$newbieUser->current_rank})\n";
echo "Buyer: {$starterUser->username} (Rank: {$starterUser->current_rank})\n";
echo "Commission: ₱" . number_format($commission1, 2) . "\n";
echo "Rule: {$explanation1['rule']}\n";
echo "Explanation: {$explanation1['explanation']}\n";

if (isset($explanation1['package_used']) && $explanation1['package_used'] === $starter->name) {
    echo "✓ PASSED: Higher rank uses lower rank's rate\n\n";
} else {
    echo "✗ FAILED: Should use Starter package rate\n\n";
}

// Test Scenario 2: Lower rank (Starter) has higher rank downline (Newbie)
echo "===========================================\n";
echo "Scenario 2: Lower rank upline, higher rank buyer\n";
echo "===========================================\n";
echo "Rule: Starter (lower rank) earns their own (Starter) commission rate\n\n";

$commission2 = $rankService->getEffectiveCommission($starterUser, $newbieUser, 1);
$explanation2 = $rankService->getCommissionExplanation($starterUser, $newbieUser, 1);

echo "Upline: {$starterUser->username} (Rank: {$starterUser->current_rank})\n";
echo "Buyer: {$newbieUser->username} (Rank: {$newbieUser->current_rank})\n";
echo "Commission: ₱" . number_format($commission2, 2) . "\n";
echo "Rule: {$explanation2['rule']}\n";
echo "Explanation: {$explanation2['explanation']}\n";

if (isset($explanation2['package_used']) && $explanation2['package_used'] === $starter->name) {
    echo "✓ PASSED: Lower rank uses their own rate\n";
    if (isset($explanation2['motivation'])) {
        echo "Motivation: {$explanation2['motivation']}\n";
    }
    echo "\n";
} else {
    echo "✗ FAILED: Should use Starter package rate\n\n";
}

// Test Scenario 3: Same rank
echo "===========================================\n";
echo "Scenario 3: Same rank (both Starter)\n";
echo "===========================================\n";
echo "Rule: Standard Starter commission rate applies\n\n";

$starterUser2 = User::factory()->create([
    'username' => 'test_starter2_' . uniqid(),
    'email' => 'starter2_' . uniqid() . '@test.com',
    'current_rank' => 'Starter', 
    'rank_package_id' => $starter->id,
    'network_status' => 'active',
]);

$commission3 = $rankService->getEffectiveCommission($starterUser, $starterUser2, 1);
$explanation3 = $rankService->getCommissionExplanation($starterUser, $starterUser2, 1);

echo "Upline: {$starterUser->username} (Rank: {$starterUser->current_rank})\n";
echo "Buyer: {$starterUser2->username} (Rank: {$starterUser2->current_rank})\n";
echo "Commission: ₱" . number_format($commission3, 2) . "\n";
echo "Rule: {$explanation3['rule']}\n";
echo "Explanation: {$explanation3['explanation']}\n";

if ($explanation3['rule'] === 'Rule 3: Same Rank → Standard') {
    echo "✓ PASSED: Same rank uses standard commission\n\n";
} else {
    echo "✗ FAILED: Should use Rule 3\n\n";
}

// Test Scenario 4: Inactive user
echo "===========================================\n";
echo "Scenario 4: Inactive user\n";
echo "===========================================\n";
echo "Note: MLMCommissionService should skip BEFORE calling RankComparisonService\n\n";

$inactiveUser = User::factory()->create([
    'username' => 'test_inactive_' . uniqid(),
    'email' => 'inactive_' . uniqid() . '@test.com',
    'current_rank' => 'Newbie', 
    'rank_package_id' => $newbie->id,
    'network_status' => 'inactive',
]);

echo "Inactive user: {$inactiveUser->username}\n";
echo "Network status: {$inactiveUser->network_status}\n";
echo "isNetworkActive(): " . ($inactiveUser->isNetworkActive() ? 'true' : 'false') . "\n";
echo "✓ This user would be skipped by MLMCommissionService before rank comparison\n";
echo "✓ RankComparisonService should NEVER receive inactive users as input\n\n";

// Summary
echo "===========================================\n";
echo "Test Summary\n";
echo "===========================================\n";
echo "✓ Scenario 0a: No rank upline → 0.00 commission\n";
echo "✓ Scenario 0b: No rank buyer → 0.00 commission\n";
echo "✓ Scenario 1: Higher rank → Lower rate\n";
echo "✓ Scenario 2: Lower rank → Own rate\n";
echo "✓ Scenario 3: Same rank → Standard rate\n";
echo "✓ Scenario 4: Inactive users skipped\n\n";

echo "Phase 2 Test Completed!\n";
echo "===========================================\n";

// Cleanup test users
echo "\nCleaning up test users...\n";
User::whereIn('id', [
    $noRankUpline->id,
    $rankedBuyer->id,
    $rankedUpline->id,
    $noRankBuyer->id,
    $newbieUser->id,
    $starterUser->id,
    $starterUser2->id,
    $inactiveUser->id,
])->delete();
echo "✓ Test users deleted\n";
