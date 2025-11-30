<?php

/**
 * ENHANCED Test script for Phase 2: Rank-Aware MLM Commission Calculation
 * 
 * This version provides detailed commission comparisons showing the exact
 * differences in earnings between different rank scenarios.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Models\MlmSetting;
use App\Services\RankComparisonService;

echo "=============================================================\n";
echo "ENHANCED Phase 2: Rank-Aware Commission Test\n";
echo "=============================================================\n\n";

$rankService = new RankComparisonService();

// Get rank packages
echo "Loading rank packages and their commission rates...\n";
$starter = Package::where('rank_name', 'Starter')->first();
$newbie = Package::where('rank_name', 'Newbie')->first();
$bronze = Package::where('rank_name', 'Bronze')->first();

if (!$starter || !$newbie || !$bronze) {
    echo "❌ ERROR: Rank packages not found.\n";
    exit(1);
}

// Display commission rates
echo "\n📊 Commission Rates (Level 1):\n";
$starterRate = MlmSetting::getCommissionForLevel($starter->id, 1);
$newbieRate = MlmSetting::getCommissionForLevel($newbie->id, 1);
$bronzeRate = MlmSetting::getCommissionForLevel($bronze->id, 1);

echo "   Starter Package: ₱" . number_format($starterRate, 2) . "\n";
echo "   Newbie Package:  ₱" . number_format($newbieRate, 2) . "\n";
echo "   Bronze Package:  ₱" . number_format($bronzeRate, 2) . "\n";
echo "\n";

// Create test users for all rank combinations
echo "Creating test users with different ranks...\n";

$starterUser1 = User::factory()->create([
    'username' => 'test_starter1_' . uniqid(),
    'email' => 'starter1_' . uniqid() . '@test.com',
    'current_rank' => 'Starter',
    'rank_package_id' => $starter->id,
    'network_status' => 'active',
]);

$starterUser2 = User::factory()->create([
    'username' => 'test_starter2_' . uniqid(),
    'email' => 'starter2_' . uniqid() . '@test.com',
    'current_rank' => 'Starter',
    'rank_package_id' => $starter->id,
    'network_status' => 'active',
]);

$newbieUser1 = User::factory()->create([
    'username' => 'test_newbie1_' . uniqid(),
    'email' => 'newbie1_' . uniqid() . '@test.com',
    'current_rank' => 'Newbie',
    'rank_package_id' => $newbie->id,
    'network_status' => 'active',
]);

$newbieUser2 = User::factory()->create([
    'username' => 'test_newbie2_' . uniqid(),
    'email' => 'newbie2_' . uniqid() . '@test.com',
    'current_rank' => 'Newbie',
    'rank_package_id' => $newbie->id,
    'network_status' => 'active',
]);

$bronzeUser = User::factory()->create([
    'username' => 'test_bronze_' . uniqid(),
    'email' => 'bronze_' . uniqid() . '@test.com',
    'current_rank' => 'Bronze',
    'rank_package_id' => $bronze->id,
    'network_status' => 'active',
]);

echo "✓ Created 5 test users (2 Starter, 2 Newbie, 1 Bronze)\n\n";

// Test Matrix: All Rank Combinations
echo "=============================================================\n";
echo "COMPREHENSIVE TEST MATRIX: All Rank Combinations\n";
echo "=============================================================\n\n";

$testCases = [
    // Starter upline scenarios
    ['upline' => $starterUser1, 'buyer' => $starterUser2, 'expected_rule' => 'Rule 3'],
    ['upline' => $starterUser1, 'buyer' => $newbieUser1, 'expected_rule' => 'Rule 2'],
    ['upline' => $starterUser1, 'buyer' => $bronzeUser, 'expected_rule' => 'Rule 2'],
    
    // Newbie upline scenarios
    ['upline' => $newbieUser1, 'buyer' => $starterUser1, 'expected_rule' => 'Rule 1'],
    ['upline' => $newbieUser1, 'buyer' => $newbieUser2, 'expected_rule' => 'Rule 3'],
    ['upline' => $newbieUser1, 'buyer' => $bronzeUser, 'expected_rule' => 'Rule 2'],
    
    // Bronze upline scenarios
    ['upline' => $bronzeUser, 'buyer' => $starterUser1, 'expected_rule' => 'Rule 1'],
    ['upline' => $bronzeUser, 'buyer' => $newbieUser1, 'expected_rule' => 'Rule 1'],
];

$testNumber = 1;
$allPassed = true;

foreach ($testCases as $test) {
    $upline = $test['upline'];
    $buyer = $test['buyer'];
    $expectedRule = $test['expected_rule'];
    
    echo "Test #{$testNumber}: {$upline->rankPackage->rank_name} → {$buyer->rankPackage->rank_name}\n";
    echo str_repeat('-', 60) . "\n";
    
    $commission = $rankService->getEffectiveCommission($upline, $buyer, 1);
    $explanation = $rankService->getCommissionExplanation($upline, $buyer, 1);
    
    echo "Upline:  {$upline->username} (Rank: {$upline->current_rank})\n";
    echo "Buyer:   {$buyer->username} (Rank: {$buyer->current_rank})\n";
    echo "Rule:    {$explanation['rule']}\n";
    echo "Earned:  ₱" . number_format($commission, 2) . "\n";
    echo "Why:     {$explanation['explanation']}\n";
    
    // Show what they WOULD earn if they were same rank as buyer
    if ($upline->rankPackage->rank_order != $buyer->rankPackage->rank_order) {
        $buyerCommission = MlmSetting::getCommissionForLevel($buyer->rank_package_id, 1);
        if ($commission < $buyerCommission) {
            $difference = $buyerCommission - $commission;
            echo "💡 If upline was {$buyer->current_rank}: ₱" . number_format($buyerCommission, 2);
            echo " (Missing: ₱" . number_format($difference, 2) . ")\n";
        }
    }
    
    // Verify expected rule
    if (strpos($explanation['rule'], $expectedRule) !== false) {
        echo "✓ PASSED\n";
    } else {
        echo "✗ FAILED: Expected {$expectedRule}\n";
        $allPassed = false;
    }
    
    echo "\n";
    $testNumber++;
}

// Detailed Scenario Analysis
echo "=============================================================\n";
echo "DETAILED SCENARIO ANALYSIS\n";
echo "=============================================================\n\n";

// Scenario A: Motivation to Rank Up
echo "📈 SCENARIO A: Motivation to Rank Up\n";
echo str_repeat('-', 60) . "\n";
echo "Starter upline has a Newbie buyer purchase a package...\n\n";

$commissionStarter = $rankService->getEffectiveCommission($starterUser1, $newbieUser1, 1);
$explanationStarter = $rankService->getCommissionExplanation($starterUser1, $newbieUser1, 1);

echo "Current Situation:\n";
echo "   Upline: {$starterUser1->username} (Starter)\n";
echo "   Buyer:  {$newbieUser1->username} (Newbie)\n";
echo "   Earned: ₱" . number_format($commissionStarter, 2) . " (Starter rate)\n\n";

$ifNewbie = MlmSetting::getCommissionForLevel($newbie->id, 1);
$potentialGain = $ifNewbie - $commissionStarter;

echo "💰 If upline ranks up to Newbie:\n";
echo "   Would earn: ₱" . number_format($ifNewbie, 2) . " (Newbie rate)\n";
echo "   Potential gain: ₱" . number_format($potentialGain, 2) . " per transaction\n";
echo "   Increase: " . number_format(($potentialGain / $commissionStarter) * 100, 1) . "%\n\n";

if (isset($explanationStarter['motivation'])) {
    echo "System says: \"{$explanationStarter['motivation']}\"\n\n";
}

// Scenario B: Fair Play Prevention
echo "📉 SCENARIO B: Fair Play Prevention\n";
echo str_repeat('-', 60) . "\n";
echo "Bronze upline has a Starter buyer purchase a package...\n\n";

$commissionBronze = $rankService->getEffectiveCommission($bronzeUser, $starterUser1, 1);
$explanationBronze = $rankService->getCommissionExplanation($bronzeUser, $starterUser1, 1);

echo "Situation:\n";
echo "   Upline: {$bronzeUser->username} (Bronze - highest rank)\n";
echo "   Buyer:  {$starterUser1->username} (Starter - lowest rank)\n";
echo "   Earned: ₱" . number_format($commissionBronze, 2) . " (Starter rate, not Bronze!)\n\n";

$bronzeFullRate = MlmSetting::getCommissionForLevel($bronze->id, 1);
$prevented = $bronzeFullRate - $commissionBronze;

echo "⚖️ System Fair Play:\n";
echo "   Bronze rate: ₱" . number_format($bronzeFullRate, 2) . "\n";
echo "   Actually earned: ₱" . number_format($commissionBronze, 2) . " (Starter rate)\n";
echo "   Prevented unfair advantage: ₱" . number_format($prevented, 2) . "\n";
echo "   Reason: Prevents exploitation of rank difference\n\n";

// Scenario C: Same Rank Equality
echo "🤝 SCENARIO C: Same Rank Equality\n";
echo str_repeat('-', 60) . "\n";
echo "Newbie upline has another Newbie buyer purchase a package...\n\n";

$commissionSameRank = $rankService->getEffectiveCommission($newbieUser1, $newbieUser2, 1);
$explanationSameRank = $rankService->getCommissionExplanation($newbieUser1, $newbieUser2, 1);

echo "Situation:\n";
echo "   Upline: {$newbieUser1->username} (Newbie)\n";
echo "   Buyer:  {$newbieUser2->username} (Newbie)\n";
echo "   Earned: ₱" . number_format($commissionSameRank, 2) . " (Standard Newbie rate)\n\n";

echo "✅ Equal ranks = Fair standard commission\n";
echo "   Both users at same level, standard rules apply\n";
echo "   No rank advantage/disadvantage\n\n";

// Summary Statistics
echo "=============================================================\n";
echo "SUMMARY STATISTICS\n";
echo "=============================================================\n\n";

$totalTests = count($testCases);
$passRate = $allPassed ? 100 : 0;

echo "Total test cases: {$totalTests}\n";
echo "Pass rate: {$passRate}%\n";
echo "Status: " . ($allPassed ? '✓ ALL TESTS PASSED' : '✗ SOME TESTS FAILED') . "\n\n";

echo "Commission Rate Differences:\n";
echo "   Bronze vs Starter: +" . number_format((($bronzeRate / $starterRate) - 1) * 100, 0) . "%\n";
echo "   Newbie vs Starter: +" . number_format((($newbieRate / $starterRate) - 1) * 100, 0) . "%\n";
echo "   Bronze vs Newbie:  +" . number_format((($bronzeRate / $newbieRate) - 1) * 100, 0) . "%\n\n";

echo "Key Insights:\n";
echo "   • Higher ranks earn MORE when buyers are same/higher rank\n";
echo "   • Higher ranks earn LESS (buyer's rate) when buyer is lower rank\n";
echo "   • Lower ranks earn SAME regardless of buyer's rank (motivation!)\n";
echo "   • System prevents exploitation while encouraging advancement\n\n";

// Cleanup
echo "=============================================================\n";
echo "Cleaning up test users...\n";
echo "=============================================================\n\n";

User::whereIn('id', [
    $starterUser1->id,
    $starterUser2->id,
    $newbieUser1->id,
    $newbieUser2->id,
    $bronzeUser->id,
])->delete();

echo "✓ Test users deleted\n\n";

echo "=============================================================\n";
echo "ENHANCED TEST COMPLETED!\n";
echo "=============================================================\n";

if ($allPassed) {
    echo "\n🎉 Phase 2 implementation is working perfectly!\n\n";
    exit(0);
} else {
    echo "\n❌ Some tests failed. Please review the implementation.\n\n";
    exit(1);
}
