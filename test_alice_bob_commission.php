<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Models\Order;
use App\Models\MlmSetting;

echo "=== Testing Alice and Bob Commission Scenario ===\n\n";

// Find Alice
$alice = User::where('username', 'alice_distributor')->first();
if (!$alice) {
    echo "❌ Alice not found. Please create alice_distributor first.\n";
    exit(1);
}

// Find Bob
$bob = User::where('username', 'bob_member')->first();
if (!$bob) {
    echo "❌ Bob not found. Please create bob_member first.\n";
    exit(1);
}

// Find Starter Package
$starterPackage = Package::where('name', 'LIKE', '%Starter%')
    ->where('is_mlm_package', true)
    ->first();

if (!$starterPackage) {
    echo "❌ Starter Package not found.\n";
    exit(1);
}

echo "📊 SCENARIO DETAILS:\n";
echo "-------------------\n";
echo "Alice (Sponsor): {$alice->username}\n";
echo "  - Current Rank: " . ($alice->current_rank ?? 'None') . "\n";
echo "  - Rank Package ID: " . ($alice->rank_package_id ?? 'None') . "\n";
echo "  - Network Status: {$alice->network_status}\n";
echo "  - Is Network Active: " . ($alice->isNetworkActive() ? 'YES' : 'NO') . "\n\n";

echo "Bob (Buyer): {$bob->username}\n";
echo "  - Sponsor: " . ($bob->sponsor->username ?? 'None') . "\n";
echo "  - Current Rank: " . ($bob->current_rank ?? 'None') . "\n";
echo "  - Rank Package ID: " . ($bob->rank_package_id ?? 'None') . "\n\n";

echo "Package: {$starterPackage->name}\n";
echo "  - Price: ₱" . number_format($starterPackage->price, 2) . "\n";
echo "  - Is MLM Package: " . ($starterPackage->is_mlm_package ? 'YES' : 'NO') . "\n";
echo "  - Rank Name: " . ($starterPackage->rank_name ?? 'None') . "\n";
echo "  - Rank Order: " . ($starterPackage->rank_order ?? 'None') . "\n\n";

// Check MLM Settings
$mlmSettings = $starterPackage->mlmSettings()->where('is_active', true)->orderBy('level')->get();
if ($mlmSettings->isEmpty()) {
    echo "❌ No MLM settings found for {$starterPackage->name}!\n";
    echo "Please configure MLM bonuses at /admin/packages/{$starterPackage->slug}/mlm\n";
    exit(1);
}

echo "💰 MLM COMMISSION STRUCTURE:\n";
echo "----------------------------\n";
$totalCommission = 0;
foreach ($mlmSettings as $setting) {
    echo "Level {$setting->level}: ₱" . number_format($setting->commission_amount, 2) . "\n";
    $totalCommission += $setting->commission_amount;
}
echo "Total Possible: ₱" . number_format($totalCommission, 2) . "\n\n";

// Verify sponsor relationship
if ($bob->sponsor_id !== $alice->id) {
    echo "⚠️  WARNING: Bob's sponsor is NOT Alice!\n";
    echo "Bob's sponsor_id: {$bob->sponsor_id}\n";
    echo "Alice's id: {$alice->id}\n";
    exit(1);
}

echo "✅ Sponsor relationship verified: Bob → Alice\n\n";

// Check Alice's rank package
if (!$alice->rankPackage) {
    echo "⚠️  WARNING: Alice has no rank package!\n";
    echo "Alice must purchase a package first to earn commissions.\n";
    exit(1);
}

echo "🎯 EXPECTED COMMISSION CALCULATION:\n";
echo "-----------------------------------\n";
echo "When Bob purchases {$starterPackage->name}:\n";
echo "1. Alice is Level 1 upline (direct sponsor)\n";
echo "2. Alice's Rank: " . ($alice->current_rank ?? 'None') . "\n";
echo "3. Bob's Rank: " . ($bob->current_rank ?? 'None') . "\n";

$aliceRankOrder = $alice->rankPackage->rank_order ?? 0;
$bobRankOrder = $bob->rankPackage->rank_order ?? 0;

if ($aliceRankOrder == $bobRankOrder) {
    echo "4. RULE 3: Same Rank → Alice gets standard rate\n";
    $level1Commission = MlmSetting::getCommissionForLevel($starterPackage->id, 1);
    echo "5. Expected Commission: ₱" . number_format($level1Commission, 2) . "\n";
} elseif ($aliceRankOrder > $bobRankOrder) {
    echo "4. RULE 1: Alice (higher rank) gets Bob's (lower) rate\n";
    $bobPackage = $bob->rankPackage;
    $commission = MlmSetting::getCommissionForLevel($bobPackage->id, 1);
    echo "5. Expected Commission: ₱" . number_format($commission, 2) . "\n";
} else {
    echo "4. RULE 2: Alice (lower rank) gets her own rate\n";
    $alicePackage = $alice->rankPackage;
    $commission = MlmSetting::getCommissionForLevel($alicePackage->id, 1);
    echo "5. Expected Commission: ₱" . number_format($commission, 2) . "\n";
}

echo "\n✅ All prerequisites verified!\n";
echo "Bob can now purchase the Starter Package and Alice should receive the commission.\n";
