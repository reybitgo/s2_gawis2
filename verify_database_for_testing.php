<?php

/**
 * Comprehensive database verification script for Phase 2 testing
 * 
 * This script verifies that all necessary components are in place
 * before running rank-aware commission tests.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Models\MlmSetting;
use Illuminate\Support\Facades\Schema;

echo "===========================================\n";
echo "Database Verification for Phase 2 Testing\n";
echo "===========================================\n\n";

$allGood = true;
$issues = [];

// 1. Check rank tables exist
echo "1. Checking rank tables exist...\n";
$rankAdvancementsExists = Schema::hasTable('rank_advancements');
$directSponsorsExists = Schema::hasTable('direct_sponsors_tracker');

echo "   rank_advancements: " . ($rankAdvancementsExists ? '✓ EXISTS' : '❌ MISSING') . "\n";
echo "   direct_sponsors_tracker: " . ($directSponsorsExists ? '✓ EXISTS' : '❌ MISSING') . "\n\n";

if (!$rankAdvancementsExists || !$directSponsorsExists) {
    $allGood = false;
    $issues[] = 'Missing rank tables - run migrations first';
}

// 2. Check user rank columns
echo "2. Checking user rank columns...\n";
$userCurrentRank = Schema::hasColumn('users', 'current_rank');
$userRankPackageId = Schema::hasColumn('users', 'rank_package_id');
$userRankUpdatedAt = Schema::hasColumn('users', 'rank_updated_at');

echo "   users.current_rank: " . ($userCurrentRank ? '✓ EXISTS' : '❌ MISSING') . "\n";
echo "   users.rank_package_id: " . ($userRankPackageId ? '✓ EXISTS' : '❌ MISSING') . "\n";
echo "   users.rank_updated_at: " . ($userRankUpdatedAt ? '✓ EXISTS' : '❌ MISSING') . "\n\n";

if (!$userCurrentRank || !$userRankPackageId || !$userRankUpdatedAt) {
    $allGood = false;
    $issues[] = 'Missing user rank columns - run migrations first';
}

// 3. Check package rank columns
echo "3. Checking package rank columns...\n";
$packageRankName = Schema::hasColumn('packages', 'rank_name');
$packageRankOrder = Schema::hasColumn('packages', 'rank_order');
$packageRequiredSponsors = Schema::hasColumn('packages', 'required_direct_sponsors');
$packageIsRankable = Schema::hasColumn('packages', 'is_rankable');
$packageNextRank = Schema::hasColumn('packages', 'next_rank_package_id');

echo "   packages.rank_name: " . ($packageRankName ? '✓ EXISTS' : '❌ MISSING') . "\n";
echo "   packages.rank_order: " . ($packageRankOrder ? '✓ EXISTS' : '❌ MISSING') . "\n";
echo "   packages.required_direct_sponsors: " . ($packageRequiredSponsors ? '✓ EXISTS' : '❌ MISSING') . "\n";
echo "   packages.is_rankable: " . ($packageIsRankable ? '✓ EXISTS' : '❌ MISSING') . "\n";
echo "   packages.next_rank_package_id: " . ($packageNextRank ? '✓ EXISTS' : '❌ MISSING') . "\n\n";

if (!$packageRankName || !$packageRankOrder || !$packageRequiredSponsors || !$packageIsRankable || !$packageNextRank) {
    $allGood = false;
    $issues[] = 'Missing package rank columns - run migrations first';
}

// 4. Check rank packages configuration
echo "4. Checking rank packages configuration...\n";
$packages = Package::whereNotNull('rank_name')->orderBy('rank_order')->get();

if ($packages->count() > 0) {
    echo "   Found " . $packages->count() . " rank packages:\n\n";
    foreach ($packages as $pkg) {
        echo "   [" . $pkg->rank_order . "] " . $pkg->rank_name . " (" . $pkg->name . ")\n";
        echo "       Package ID: " . $pkg->id . "\n";
        echo "       MLM Package: " . ($pkg->is_mlm_package ? 'YES' : 'NO') . "\n";
        echo "       Requires: " . $pkg->required_direct_sponsors . " same-rank sponsors\n";
        echo "       Next Rank: " . ($pkg->next_rank_package_id ? "Package #" . $pkg->next_rank_package_id : "None (Top Rank)") . "\n\n";
    }
    
    if ($packages->count() < 3) {
        $allGood = false;
        $issues[] = "Need at least 3 rank packages, found " . $packages->count();
    }
} else {
    echo "   ❌ NO RANK PACKAGES FOUND!\n";
    echo "   Run: php setup_rank_packages.php\n\n";
    $allGood = false;
    $issues[] = 'No rank packages configured';
}

// 5. Check MLM commission settings
echo "5. Checking MLM commission settings...\n";
if ($packages->count() > 0) {
    foreach ($packages->take(3) as $pkg) {
        $mlmSettings = MlmSetting::where('package_id', $pkg->id)->where('is_active', true)->get();
        echo "   " . $pkg->rank_name . " (Package #" . $pkg->id . ") MLM Settings:\n";
        
        if ($mlmSettings->count() > 0) {
            foreach ($mlmSettings as $mlm) {
                echo "      Level " . $mlm->level . ": ₱" . number_format($mlm->commission_amount, 2) . " (Active)\n";
            }
        } else {
            echo "      ⚠️  No MLM settings found!\n";
            $allGood = false;
            $issues[] = "Missing MLM settings for " . $pkg->rank_name . " package";
        }
        echo "\n";
    }
} else {
    echo "   ⚠️  Skipped - no rank packages configured\n\n";
}

// 6. Check existing users with ranks
echo "6. Checking existing users with ranks...\n";
$usersWithRanks = User::whereNotNull('current_rank')->count();
$totalUsers = User::count();

echo "   Users with ranks: " . $usersWithRanks . "\n";
echo "   Total users: " . $totalUsers . "\n";
echo "   Users without ranks: " . ($totalUsers - $usersWithRanks) . "\n\n";

// 7. Check RankComparisonService
echo "7. Checking RankComparisonService...\n";
$rankServiceExists = class_exists('App\Services\RankComparisonService');
echo "   " . ($rankServiceExists ? '✓ RankComparisonService found' : '❌ RankComparisonService MISSING') . "\n\n";

if (!$rankServiceExists) {
    $allGood = false;
    $issues[] = 'RankComparisonService not found';
}

// 8. Check User model rank methods
echo "8. Checking User model rank methods...\n";
$user = User::first();
if ($user) {
    $hasRankPackage = method_exists($user, 'rankPackage');
    $hasGetRankName = method_exists($user, 'getRankName');
    $hasIsNetworkActive = method_exists($user, 'isNetworkActive');
    
    echo "   " . ($hasRankPackage ? '✓' : '❌') . " rankPackage() method\n";
    echo "   " . ($hasGetRankName ? '✓' : '❌') . " getRankName() method\n";
    echo "   " . ($hasIsNetworkActive ? '✓' : '❌') . " isNetworkActive() method\n\n";
    
    if (!$hasRankPackage || !$hasGetRankName || !$hasIsNetworkActive) {
        $allGood = false;
        $issues[] = 'Missing required User model methods';
    }
} else {
    echo "   ⚠️  No users found in database\n\n";
}

// 9. Test MlmSetting::getCommissionForLevel()
echo "9. Testing MlmSetting::getCommissionForLevel() method...\n";
try {
    if ($packages->count() > 0) {
        $firstPackage = $packages->first();
        $commission = MlmSetting::getCommissionForLevel($firstPackage->id, 1);
        echo "   ✓ Method works! " . $firstPackage->rank_name . " Level 1: ₱" . number_format($commission, 2) . "\n\n";
    } else {
        echo "   ⚠️  Skipped - no rank packages configured\n\n";
    }
} catch (Exception $e) {
    echo "   ❌ Method failed: " . $e->getMessage() . "\n\n";
    $allGood = false;
    $issues[] = 'MlmSetting::getCommissionForLevel() method failed';
}

// 10. Check Package model rank methods
echo "10. Checking Package model rank methods...\n";
if ($packages->count() > 0) {
    $pkg = $packages->first();
    $hasNextRankPackage = method_exists($pkg, 'nextRankPackage');
    $hasCanAdvance = method_exists($pkg, 'canAdvanceToNextRank');
    
    echo "   " . ($hasNextRankPackage ? '✓' : '❌') . " nextRankPackage() relationship\n";
    echo "   " . ($hasCanAdvance ? '✓' : '❌') . " canAdvanceToNextRank() method\n\n";
    
    if (!$hasNextRankPackage || !$hasCanAdvance) {
        $allGood = false;
        $issues[] = 'Missing required Package model methods';
    }
} else {
    echo "   ⚠️  Skipped - no rank packages configured\n\n";
}

// Final verdict
echo "===========================================\n";
echo "Verification Results\n";
echo "===========================================\n\n";

if ($allGood) {
    echo "✓✓✓ DATABASE IS PERFECT FOR TESTING! ✓✓✓\n\n";
    echo "You can now run:\n";
    echo "   php test_rank_aware_commission.php\n\n";
    exit(0);
} else {
    echo "❌ DATABASE NOT READY FOR TESTING!\n\n";
    echo "Issues found:\n";
    foreach ($issues as $issue) {
        echo "   - " . $issue . "\n";
    }
    echo "\nPlease fix the issues above before running tests.\n\n";
    exit(1);
}
