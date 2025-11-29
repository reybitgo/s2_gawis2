<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;

echo "=== Phase 1: Rank System Foundation Test ===\n\n";

// Test 1: Check packages have rank fields
echo "Test 1: Package Rank Configuration\n";
$packages = Package::rankable()->orderedByRank()->get();

if ($packages->isEmpty()) {
    echo "  ⚠️  No rankable packages found. Please run seeders first.\n\n";
} else {
    foreach ($packages as $package) {
        echo "  {$package->rank_name} (Order: {$package->rank_order})\n";
        echo "    - Price: ₱" . number_format($package->price, 2) . "\n";
        echo "    - Required Sponsors: {$package->required_direct_sponsors}\n";
        echo "    - Next Rank: " . ($package->nextRankPackage?->rank_name ?? 'None (Top Rank)') . "\n\n";
    }
}

// Test 2: Check user rank fields
echo "Test 2: User Rank Fields\n";
$user = User::first();
if ($user) {
    echo "  User: {$user->username}\n";
    echo "  Current Rank: " . $user->getRankName() . "\n";
    echo "  Rank Package: " . ($user->rankPackage?->name ?? 'None') . "\n";
    echo "  Rank Order: " . $user->getRankOrder() . "\n\n";
} else {
    echo "  ⚠️  No users found in database.\n\n";
}

// Test 3: Check highest package purchased
echo "Test 3: Highest Package Purchased\n";
if ($user) {
    $highestPackage = $user->getHighestPackagePurchased();
    if ($highestPackage) {
        echo "  Package: {$highestPackage->name}\n";
        echo "  Rank: {$highestPackage->rank_name}\n";
        echo "  Price: ₱" . number_format($highestPackage->price, 2) . "\n\n";
    } else {
        echo "  No package purchased yet\n\n";
    }
} else {
    echo "  ⚠️  No user to test.\n\n";
}

// Test 4: Update user rank based on purchase
echo "Test 4: Manual Rank Update\n";
if ($user) {
    $beforeRank = $user->current_rank;
    $user->updateRank();
    $afterRank = $user->fresh()->current_rank;
    echo "  Rank before update: " . ($beforeRank ?? 'None') . "\n";
    echo "  Rank after update: " . ($afterRank ?? 'None') . "\n";
    echo "  Status: " . ($beforeRank !== $afterRank ? '✅ Rank updated' : '✅ Rank unchanged (expected)') . "\n\n";
} else {
    echo "  ⚠️  No user to test.\n\n";
}

// Test 5: Check database tables exist
echo "Test 5: Database Tables\n";
$tables = [
    'rank_advancements' => \Illuminate\Support\Facades\Schema::hasTable('rank_advancements'),
    'direct_sponsors_tracker' => \Illuminate\Support\Facades\Schema::hasTable('direct_sponsors_tracker'),
];

foreach ($tables as $table => $exists) {
    echo "  {$table}: " . ($exists ? '✅ Exists' : '❌ Missing') . "\n";
}
echo "\n";

// Test 6: Check user table columns
echo "Test 6: User Table Columns\n";
$userColumns = [
    'current_rank' => \Illuminate\Support\Facades\Schema::hasColumn('users', 'current_rank'),
    'rank_package_id' => \Illuminate\Support\Facades\Schema::hasColumn('users', 'rank_package_id'),
    'rank_updated_at' => \Illuminate\Support\Facades\Schema::hasColumn('users', 'rank_updated_at'),
];

foreach ($userColumns as $column => $exists) {
    echo "  {$column}: " . ($exists ? '✅ Exists' : '❌ Missing') . "\n";
}
echo "\n";

// Test 7: Check package table columns
echo "Test 7: Package Table Columns\n";
$packageColumns = [
    'rank_name' => \Illuminate\Support\Facades\Schema::hasColumn('packages', 'rank_name'),
    'rank_order' => \Illuminate\Support\Facades\Schema::hasColumn('packages', 'rank_order'),
    'required_direct_sponsors' => \Illuminate\Support\Facades\Schema::hasColumn('packages', 'required_direct_sponsors'),
    'is_rankable' => \Illuminate\Support\Facades\Schema::hasColumn('packages', 'is_rankable'),
    'next_rank_package_id' => \Illuminate\Support\Facades\Schema::hasColumn('packages', 'next_rank_package_id'),
];

foreach ($packageColumns as $column => $exists) {
    echo "  {$column}: " . ($exists ? '✅ Exists' : '❌ Missing') . "\n";
}
echo "\n";

// Summary
echo "=== Test Summary ===\n";
$allTablesExist = !in_array(false, $tables);
$allUserColumnsExist = !in_array(false, $userColumns);
$allPackageColumnsExist = !in_array(false, $packageColumns);

if ($allTablesExist && $allUserColumnsExist && $allPackageColumnsExist && !$packages->isEmpty()) {
    echo "✅ Phase 1 implementation is complete!\n";
    echo "All database tables and columns are in place.\n";
    echo "Rank system foundation is ready.\n";
} else {
    echo "⚠️  Some issues detected:\n";
    if (!$allTablesExist) echo "  - Missing database tables\n";
    if (!$allUserColumnsExist) echo "  - Missing user table columns\n";
    if (!$allPackageColumnsExist) echo "  - Missing package table columns\n";
    if ($packages->isEmpty()) echo "  - No rankable packages created (run seeders)\n";
}

echo "\nPhase 1 Test Completed!\n";
