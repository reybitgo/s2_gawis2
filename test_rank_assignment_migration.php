<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;

echo "=== Testing Rank Assignment Migration ===\n\n";

// Test 1: Check if rank columns exist
echo "Test 1: Check if rank columns exist\n";
$hasColumns = \Schema::hasColumn('users', 'current_rank') 
           && \Schema::hasColumn('users', 'rank_package_id');
echo "  Columns exist: " . ($hasColumns ? "✅ YES" : "❌ NO") . "\n\n";

if (!$hasColumns) {
    echo "❌ ERROR: Rank columns don't exist. Run migrations first:\n";
    echo "   php artisan migrate\n";
    exit(1);
}

// Test 2: Check if rank packages exist
echo "Test 2: Check if rank packages exist\n";
$rankPackages = Package::whereNotNull('rank_name')
    ->where('is_mlm_package', true)
    ->get(['id', 'name', 'rank_name', 'price']);

if ($rankPackages->isEmpty()) {
    echo "  ❌ NO rank packages found!\n";
    echo "  You need to run: php artisan db:seed --class=PackageSeeder\n\n";
    exit(1);
} else {
    echo "  ✅ Found {$rankPackages->count()} rank packages:\n";
    foreach ($rankPackages as $pkg) {
        echo "     - {$pkg->name} (rank: {$pkg->rank_name}, price: ₱{$pkg->price})\n";
    }
    echo "\n";
}

// Test 3: Find users with paid orders containing packages
echo "Test 3: Users with paid package orders\n";
$usersWithPackages = User::whereHas('orders', function($query) {
    $query->where('payment_status', 'paid')
          ->whereHas('orderItems.package');
})->get();

echo "  Total users with paid orders: {$usersWithPackages->count()}\n";

if ($usersWithPackages->isEmpty()) {
    echo "  ⚠️  No users have purchased packages yet.\n";
    echo "  Migration will run but won't assign any ranks.\n\n";
    exit(0);
}

// Test 4: Check which users should get ranks
echo "\nTest 4: Analyzing which users should get ranks\n";
echo str_repeat("-", 70) . "\n";

$shouldGetRanks = 0;
$alreadyHaveRanks = 0;
$cantGetRanks = 0;

foreach ($usersWithPackages as $user) {
    // Find highest MLM package purchased
    $highestPackage = Package::whereHas('orderItems.order', function($q) use ($user) {
        $q->where('user_id', $user->id)
          ->where('payment_status', 'paid');
    })
    ->where('is_mlm_package', true)
    ->orderBy('price', 'desc')
    ->first();
    
    echo "User: {$user->username} (ID: {$user->id})\n";
    echo "  Current rank: " . ($user->current_rank ?? 'NULL') . "\n";
    
    if ($highestPackage && $highestPackage->rank_name) {
        echo "  Highest package: {$highestPackage->name} (₱{$highestPackage->price})\n";
        echo "  Should get rank: {$highestPackage->rank_name}\n";
        
        if ($user->current_rank) {
            echo "  Status: ✅ Already has rank\n";
            $alreadyHaveRanks++;
        } else {
            echo "  Status: ⚠️  SHOULD GET rank but doesn't have one yet\n";
            $shouldGetRanks++;
        }
    } else {
        if ($highestPackage) {
            echo "  Highest package: {$highestPackage->name} (no rank_name)\n";
            echo "  Status: ❌ Package has no rank configured\n";
        } else {
            echo "  Highest package: NONE (no MLM packages found)\n";
            echo "  Status: ❌ No MLM packages purchased\n";
        }
        $cantGetRanks++;
    }
    echo "\n";
}

// Summary
echo str_repeat("=", 70) . "\n";
echo "SUMMARY:\n";
echo "  Users that SHOULD get ranks: {$shouldGetRanks}\n";
echo "  Users that ALREADY have ranks: {$alreadyHaveRanks}\n";
echo "  Users that CAN'T get ranks: {$cantGetRanks}\n";
echo "\n";

// Test 5: Check why migration might not be working
if ($shouldGetRanks > 0) {
    echo "❌ ISSUE FOUND: {$shouldGetRanks} user(s) should have ranks but don't!\n\n";
    echo "Possible reasons:\n";
    echo "1. Migration didn't run - Check: php artisan migrate:status\n";
    echo "2. Migration ran but failed silently\n";
    echo "3. Packages don't have rank_name set\n";
    echo "4. is_mlm_package is false on packages\n\n";
    
    echo "To manually fix, run:\n";
    echo "  php artisan migrate:rollback --step=1\n";
    echo "  php artisan migrate\n";
    echo "\nOr run this script to manually assign ranks:\n";
    echo "  php test_manual_rank_assignment.php\n";
} elseif ($alreadyHaveRanks > 0) {
    echo "✅ All users with packages already have ranks assigned!\n";
    echo "Migration has already run successfully.\n";
} else {
    echo "⚠️  No users need rank assignment (no MLM packages purchased).\n";
}
