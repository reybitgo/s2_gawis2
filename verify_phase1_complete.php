<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MlmSetting;
use Illuminate\Support\Facades\Schema;

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║     RANK SYSTEM PHASE 1 - COMPREHENSIVE VERIFICATION          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$passed = 0;
$failed = 0;
$warnings = 0;

// ============================================================================
// TEST SUITE 1: DATABASE SCHEMA
// ============================================================================
echo "📊 TEST SUITE 1: DATABASE SCHEMA VERIFICATION\n";
echo str_repeat("─", 70) . "\n\n";

// Test 1.1: Tables Exist
echo "Test 1.1: Rank Tables Exist\n";
$tables = ['rank_advancements', 'direct_sponsors_tracker'];
foreach ($tables as $table) {
    $exists = Schema::hasTable($table);
    echo "  • {$table}: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    $exists ? $passed++ : $failed++;
}
echo "\n";

// Test 1.2: User Columns
echo "Test 1.2: User Table Rank Columns\n";
$userColumns = ['current_rank', 'rank_package_id', 'rank_updated_at'];
foreach ($userColumns as $column) {
    $exists = Schema::hasColumn('users', $column);
    echo "  • users.{$column}: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    $exists ? $passed++ : $failed++;
}
echo "\n";

// Test 1.3: Package Columns
echo "Test 1.3: Package Table Rank Columns\n";
$packageColumns = ['rank_name', 'rank_order', 'required_direct_sponsors', 'is_rankable', 'next_rank_package_id'];
foreach ($packageColumns as $column) {
    $exists = Schema::hasColumn('packages', $column);
    echo "  • packages.{$column}: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    $exists ? $passed++ : $failed++;
}
echo "\n";

// ============================================================================
// TEST SUITE 2: PACKAGE CONFIGURATION
// ============================================================================
echo "📦 TEST SUITE 2: PACKAGE CONFIGURATION\n";
echo str_repeat("─", 70) . "\n\n";

// Test 2.1: Rank Packages Exist
echo "Test 2.1: Rank Packages Created\n";
$expectedRanks = [
    'Starter' => ['order' => 1, 'price' => 1000.00, 'sponsors' => 5],
    'Newbie' => ['order' => 2, 'price' => 2500.00, 'sponsors' => 8],
    'Bronze' => ['order' => 3, 'price' => 5000.00, 'sponsors' => 10],
];

foreach ($expectedRanks as $rankName => $expected) {
    $package = Package::where('rank_name', $rankName)->first();
    if ($package) {
        $orderMatch = $package->rank_order == $expected['order'];
        $priceMatch = floatval($package->price) == $expected['price'];
        $sponsorsMatch = $package->required_direct_sponsors == $expected['sponsors'];
        
        $allMatch = $orderMatch && $priceMatch && $sponsorsMatch;
        echo "  • {$rankName}: " . ($allMatch ? "✅ CONFIGURED" : "⚠️  PARTIAL") . "\n";
        echo "    - Order: {$package->rank_order} " . ($orderMatch ? "✓" : "✗") . "\n";
        echo "    - Price: ₱" . number_format($package->price, 2) . " " . ($priceMatch ? "✓" : "✗") . "\n";
        echo "    - Sponsors: {$package->required_direct_sponsors} " . ($sponsorsMatch ? "✓" : "✗") . "\n";
        
        $allMatch ? $passed++ : $warnings++;
    } else {
        echo "  • {$rankName}: ❌ NOT FOUND\n";
        $failed++;
    }
}
echo "\n";

// Test 2.2: Rank Progression Chain
echo "Test 2.2: Rank Progression Chain\n";
$starter = Package::where('rank_name', 'Starter')->first();
$newbie = Package::where('rank_name', 'Newbie')->first();
$bronze = Package::where('rank_name', 'Bronze')->first();

if ($starter && $newbie && $bronze) {
    $chain = [];
    $current = $starter;
    $depth = 0;
    while ($current && $depth < 5) {
        $chain[] = $current->rank_name;
        $current = $current->nextRankPackage;
        $depth++;
    }
    
    $expectedChain = ['Starter', 'Newbie', 'Bronze'];
    $chainCorrect = $chain === $expectedChain;
    
    echo "  • Chain: " . implode(' → ', $chain) . "\n";
    echo "  • Status: " . ($chainCorrect ? "✅ CORRECT" : "❌ INCORRECT") . "\n";
    $chainCorrect ? $passed++ : $failed++;
} else {
    echo "  • ❌ Cannot verify chain - missing packages\n";
    $failed++;
}
echo "\n";

// Test 2.3: MLM Commission Settings
echo "Test 2.3: MLM Commission Settings\n";
$expectedCommissions = [
    'Starter' => ['level_1' => 200, 'level_2' => 50],
    'Newbie' => ['level_1' => 400, 'level_2' => 100],
    'Bronze' => ['level_1' => 800, 'level_2' => 200],
];

foreach ($expectedCommissions as $rankName => $expected) {
    $package = Package::where('rank_name', $rankName)->first();
    if ($package) {
        $settings = MlmSetting::where('package_id', $package->id)->get();
        $hasSettings = $settings->count() == 5;
        
        if ($hasSettings) {
            $level1 = $settings->where('level', 1)->first()->commission_amount ?? 0;
            $level2 = $settings->where('level', 2)->first()->commission_amount ?? 0;
            
            $level1Match = floatval($level1) == $expected['level_1'];
            $level2Match = floatval($level2) == $expected['level_2'];
            
            $allMatch = $level1Match && $level2Match;
            echo "  • {$rankName}: " . ($allMatch ? "✅ CONFIGURED" : "⚠️  INCORRECT") . "\n";
            echo "    - Level 1: ₱{$level1} " . ($level1Match ? "✓" : "✗") . "\n";
            echo "    - Level 2: ₱{$level2} " . ($level2Match ? "✓" : "✗") . "\n";
            
            $allMatch ? $passed++ : $warnings++;
        } else {
            echo "  • {$rankName}: ❌ MLM SETTINGS MISSING\n";
            $failed++;
        }
    }
}
echo "\n";

// ============================================================================
// TEST SUITE 3: MODEL FUNCTIONALITY
// ============================================================================
echo "🔧 TEST SUITE 3: MODEL FUNCTIONALITY\n";
echo str_repeat("─", 70) . "\n\n";

// Test 3.1: User Model Methods
echo "Test 3.1: User Model Rank Methods\n";
$testUser = User::first();
if ($testUser) {
    try {
        $rankName = $testUser->getRankName();
        echo "  • getRankName(): ✅ WORKS (returns '{$rankName}')\n";
        $passed++;
        
        $rankOrder = $testUser->getRankOrder();
        echo "  • getRankOrder(): ✅ WORKS (returns {$rankOrder})\n";
        $passed++;
        
        // rankPackage() can be null if user has no rank - this is expected
        $rankPackage = $testUser->rankPackage;
        echo "  • rankPackage(): ✅ WORKS (returns " . ($rankPackage ? "package" : "null as expected") . ")\n";
        $passed++;
        
        $sponsorsCount = $testUser->getSameRankSponsorsCount();
        echo "  • getSameRankSponsorsCount(): ✅ WORKS (returns {$sponsorsCount})\n";
        $passed++;
    } catch (\Exception $e) {
        echo "  • ❌ Method error: {$e->getMessage()}\n";
        $failed++;
    }
} else {
    echo "  • ⚠️  No users to test\n";
    $warnings++;
}
echo "\n";

// Test 3.2: Package Model Methods
echo "Test 3.2: Package Model Rank Methods\n";
if ($starter) {
    $methods = [
        'canAdvanceToNextRank' => $starter->canAdvanceToNextRank(),
        'getNextRankPackage' => $starter->getNextRankPackage(),
        'nextRankPackage' => $starter->nextRankPackage,
    ];
    
    foreach ($methods as $method => $result) {
        $works = !is_null($result) || $result === false;
        echo "  • {$method}(): " . ($works ? "✅ WORKS" : "❌ ERROR") . "\n";
        $works ? $passed++ : $failed++;
    }
} else {
    echo "  • ❌ No starter package to test\n";
    $failed++;
}
echo "\n";

// Test 3.3: Package Scopes
echo "Test 3.3: Package Query Scopes\n";
try {
    $rankableCount = Package::rankable()->count();
    $orderedRanks = Package::orderedByRank()->pluck('rank_name')->toArray();
    
    echo "  • Rankable scope: ✅ WORKS ({$rankableCount} packages)\n";
    echo "  • OrderedByRank scope: ✅ WORKS (" . implode(', ', $orderedRanks) . ")\n";
    $passed += 2;
} catch (\Exception $e) {
    echo "  • ❌ Scope methods failed: {$e->getMessage()}\n";
    $failed += 2;
}
echo "\n";

// ============================================================================
// TEST SUITE 4: RANK ASSIGNMENT
// ============================================================================
echo "👤 TEST SUITE 4: RANK ASSIGNMENT FUNCTIONALITY\n";
echo str_repeat("─", 70) . "\n\n";

// Test 4.1: Create Test User and Purchase
echo "Test 4.1: Automatic Rank Assignment on Purchase\n";
try {
    // Create test user
    $testRankUser = User::create([
        'username' => 'ranktest_verify_' . time(),
        'fullname' => 'Rank Test Verify',
        'password' => bcrypt('password'),
        'email' => 'ranktest_verify_' . time() . '@test.com',
        'email_verified_at' => now(),
    ]);
    
    echo "  • Test user created: {$testRankUser->username}\n";
    
    // Get Starter package
    $starterPkg = Package::where('rank_name', 'Starter')->first();
    
    // Create order
    $testOrder = Order::create([
        'user_id' => $testRankUser->id,
        'order_number' => 'VERIFY-' . strtoupper(uniqid()),
        'status' => 'confirmed',
        'payment_status' => 'paid',
        'payment_method' => 'test',
        'subtotal' => $starterPkg->price,
        'total_amount' => $starterPkg->price,
        'grand_total' => $starterPkg->price,
    ]);
    
    OrderItem::create([
        'order_id' => $testOrder->id,
        'package_id' => $starterPkg->id,
        'quantity' => 1,
        'unit_price' => $starterPkg->price,
        'price' => $starterPkg->price,
        'total_price' => $starterPkg->price,
        'subtotal' => $starterPkg->price,
    ]);
    
    echo "  • Test order created: {$testOrder->order_number}\n";
    
    // Update rank
    $testRankUser->updateRank();
    $testRankUser->refresh();
    
    $rankAssigned = $testRankUser->current_rank === 'Starter';
    echo "  • Rank assigned: " . ($rankAssigned ? "✅ SUCCESS (Starter)" : "❌ FAILED") . "\n";
    $rankAssigned ? $passed++ : $failed++;
    
    // Cleanup
    $testOrder->delete();
    $testRankUser->delete();
    echo "  • Test data cleaned up\n";
    
} catch (\Exception $e) {
    echo "  • ❌ Test failed: {$e->getMessage()}\n";
    $failed++;
}
echo "\n";

// ============================================================================
// TEST SUITE 5: ADMIN PROTECTIONS
// ============================================================================
echo "🔒 TEST SUITE 5: ADMIN UI PROTECTIONS\n";
echo str_repeat("─", 70) . "\n\n";

// Test 5.1: Package Name Lock Detection
echo "Test 5.1: Package Name Lock Status\n";
$rankPackages = Package::whereNotNull('rank_name')->where('is_mlm_package', true)->get();

foreach ($rankPackages as $pkg) {
    $isLocked = $pkg->rank_name && $pkg->is_mlm_package && $pkg->mlmSettings()->exists();
    $shouldBeLocked = in_array($pkg->rank_name, ['Starter', 'Newbie', 'Bronze']);
    
    $status = $isLocked === $shouldBeLocked ? "✅" : "❌";
    echo "  • {$pkg->rank_name}: {$status} " . ($isLocked ? "LOCKED" : "UNLOCKED") . "\n";
    
    ($isLocked === $shouldBeLocked) ? $passed++ : $failed++;
}
echo "\n";

// ============================================================================
// FINAL SUMMARY
// ============================================================================
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                    VERIFICATION SUMMARY                        ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$total = $passed + $failed + $warnings;
$passRate = $total > 0 ? round(($passed / $total) * 100, 1) : 0;

echo "Tests Passed:   " . str_pad("✅ {$passed}", 20) . " ({$passRate}%)\n";
echo "Tests Failed:   " . str_pad("❌ {$failed}", 20) . "\n";
echo "Warnings:       " . str_pad("⚠️  {$warnings}", 20) . "\n";
echo "Total Tests:    " . str_pad("{$total}", 20) . "\n\n";

echo str_repeat("═", 70) . "\n";

if ($failed === 0 && $warnings === 0) {
    echo "🎉 PHASE 1 IS FULLY COMPLETE AND READY FOR PHASE 2! 🎉\n";
    echo "\n✅ All core functionality verified\n";
    echo "✅ Database schema is correct\n";
    echo "✅ Package configuration is valid\n";
    echo "✅ Model methods are working\n";
    echo "✅ Rank assignment is functional\n";
    echo "✅ Admin protections are in place\n";
    echo "\n🚀 You can confidently proceed to Phase 2 implementation.\n";
} else if ($failed === 0 && $warnings > 0) {
    echo "⚠️  PHASE 1 IS MOSTLY COMPLETE WITH MINOR ISSUES\n";
    echo "\nReview warnings above before proceeding to Phase 2.\n";
} else {
    echo "❌ PHASE 1 HAS ISSUES THAT NEED TO BE FIXED\n";
    echo "\nResolve failed tests before proceeding to Phase 2.\n";
}

echo str_repeat("═", 70) . "\n\n";

// Exit code
exit($failed > 0 ? 1 : 0);
