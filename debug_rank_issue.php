<?php

/**
 * Debug script to check why ranks aren't being assigned
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

echo "===========================================\n";
echo "DEBUG: Rank Assignment Issue\n";
echo "===========================================\n\n";

// 1. Check if Starter package exists and is configured correctly
echo "1. CHECKING STARTER PACKAGE:\n";
echo "----------------------------\n";
$starterPackage = Package::where('rank_name', 'Starter')->first();

if ($starterPackage) {
    echo "✓ Starter package found:\n";
    echo "  - ID: {$starterPackage->id}\n";
    echo "  - Name: {$starterPackage->name}\n";
    echo "  - Rank Name: {$starterPackage->rank_name}\n";
    echo "  - Is MLM Package: " . ($starterPackage->is_mlm_package ? 'YES' : 'NO') . "\n";
    echo "  - Price: ₱" . number_format($starterPackage->price, 2) . "\n";
    echo "  - Rank Order: {$starterPackage->rank_order}\n";
} else {
    echo "✗ Starter package NOT FOUND!\n";
    echo "  This is the problem - package needs 'rank_name' = 'Starter'\n\n";
    exit(1);
}

echo "\n";

// 2. Check total users
echo "2. CHECKING USERS:\n";
echo "------------------\n";
$totalUsers = User::count();
$usersWithRank = User::whereNotNull('current_rank')->count();
echo "  - Total users: {$totalUsers}\n";
echo "  - Users with rank: {$usersWithRank}\n";
echo "  - Users without rank: " . ($totalUsers - $usersWithRank) . "\n";

echo "\n";

// 3. Check orders for Starter package
echo "3. CHECKING ORDERS FOR STARTER PACKAGE:\n";
echo "---------------------------------------\n";
$paidOrders = Order::where('payment_status', 'paid')
    ->whereHas('orderItems', function($q) use ($starterPackage) {
        $q->where('package_id', $starterPackage->id);
    })
    ->count();
    
echo "  - Paid orders with Starter package: {$paidOrders}\n";

echo "\n";

// 4. Check unique users who bought Starter
echo "4. UNIQUE USERS WHO BOUGHT STARTER:\n";
echo "-----------------------------------\n";
$uniqueUsers = User::whereHas('orders', function($q) use ($starterPackage) {
    $q->where('payment_status', 'paid')
      ->whereHas('orderItems', function($oq) use ($starterPackage) {
          $oq->where('package_id', $starterPackage->id);
      });
})->count();

echo "  - Users who bought Starter: {$uniqueUsers}\n";

echo "\n";

// 5. Check sample users (first 5)
echo "5. SAMPLE USERS WHO SHOULD GET STARTER RANK:\n";
echo "---------------------------------------------\n";
$sampleUsers = User::whereHas('orders', function($q) use ($starterPackage) {
    $q->where('payment_status', 'paid')
      ->whereHas('orderItems', function($oq) use ($starterPackage) {
          $oq->where('package_id', $starterPackage->id);
      });
})->limit(5)->get();

foreach ($sampleUsers as $user) {
    echo "  User: {$user->username} (ID: {$user->id})\n";
    echo "    - Current rank: " . ($user->current_rank ?: 'NULL') . "\n";
    echo "    - Rank package ID: " . ($user->rank_package_id ?: 'NULL') . "\n";
    
    // Check what package they should get
    $highestPackage = Package::whereHas('orderItems.order', function($q) use ($user) {
        $q->where('user_id', $user->id)
          ->where('payment_status', 'paid');
    })
    ->where('is_mlm_package', true)
    ->orderBy('price', 'desc')
    ->first();
    
    if ($highestPackage) {
        echo "    - Highest package bought: {$highestPackage->name} (₱" . number_format($highestPackage->price, 2) . ")\n";
        echo "    - That package rank_name: " . ($highestPackage->rank_name ?: 'NULL') . "\n";
        echo "    - That package is_mlm_package: " . ($highestPackage->is_mlm_package ? 'YES' : 'NO') . "\n";
    } else {
        echo "    - ✗ NO MLM PACKAGE FOUND!\n";
    }
    
    echo "\n";
}

echo "\n";

// 6. Check if there are MLM packages without rank names
echo "6. ALL MLM PACKAGES:\n";
echo "--------------------\n";
$mlmPackages = Package::where('is_mlm_package', true)->get();
foreach ($mlmPackages as $pkg) {
    echo "  - {$pkg->name}: rank_name = " . ($pkg->rank_name ?: 'NULL') . "\n";
}

echo "\n===========================================\n";
echo "DIAGNOSIS:\n";
echo "===========================================\n";

if (!$starterPackage->is_mlm_package) {
    echo "⚠️  PROBLEM: Starter package has is_mlm_package = false\n";
    echo "   Solution: UPDATE packages SET is_mlm_package = 1 WHERE rank_name = 'Starter'\n\n";
}

if (!$starterPackage->rank_name) {
    echo "⚠️  PROBLEM: Starter package has no rank_name\n";
    echo "   Solution: UPDATE packages SET rank_name = 'Starter' WHERE id = {$starterPackage->id}\n\n";
}

if ($uniqueUsers > 0 && $usersWithRank == 0) {
    echo "⚠️  PROBLEM: {$uniqueUsers} users bought packages but none have ranks\n";
    echo "   This means the script hasn't been run or the query logic has issues\n\n";
}

echo "===========================================\n";
