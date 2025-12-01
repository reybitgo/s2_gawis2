<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Services\RankAdvancementService;
use Illuminate\Support\Facades\DB;

echo "=== Phase 3: Rank Advancement Test (LEGACY USERS) ===\n\n";

$rankService = new RankAdvancementService();

// Clean up test data
echo "Cleaning up any existing test data...\n";
DB::table('direct_sponsors_tracker')->where('user_id', '>', 1)->delete();
DB::table('rank_advancements')->where('user_id', '>', 1)->delete();
DB::table('users')->where('id', '>', 10)->delete();

// Get packages
$starter = Package::where('rank_name', 'Starter')->first();
$newbie = Package::where('rank_name', 'Newbie')->first();

if (!$starter || !$newbie) {
    echo "❌ ERROR: Rank packages not found! Please run setup_rank_packages.php first.\n";
    exit(1);
}

echo "Test Setup: Legacy User Scenario\n";
echo "Starter package requires {$starter->required_direct_sponsors} sponsors\n\n";

// Simulate legacy user who ALREADY has direct referrals
$legacySponsor = User::factory()->create([
    'username' => 'legacy_sponsor_' . time(),
    'fullname' => 'Legacy Sponsor',
    'email' => 'legacy_sponsor_' . time() . '@test.com',
    'password' => bcrypt('password'),
    'current_rank' => 'Starter',
    'rank_package_id' => $starter->id,
    'network_status' => 'active',
]);

echo "Created Legacy Sponsor: {$legacySponsor->username} (ID: {$legacySponsor->id})\n\n";

// Create N-1 legacy referrals (already exist, not tracked yet)
$requiredSponsors = $starter->required_direct_sponsors;

echo "Simulating {$requiredSponsors} EXISTING legacy referrals (before rank system deployed)...\n";
echo "(These will NOT be tracked in direct_sponsors_tracker yet)\n\n";

$legacyReferrals = [];
for ($i = 1; $i < $requiredSponsors; $i++) {
    $legacyReferral = User::factory()->create([
        'username' => "legacy_referral_{$i}_" . time(),
        'fullname' => "Legacy Referral {$i}",
        'email' => "legacy_referral_{$i}_" . time() . '@test.com',
        'password' => bcrypt('password'),
        'sponsor_id' => $legacySponsor->id,
        'current_rank' => 'Starter',
        'rank_package_id' => $starter->id,
        'network_status' => 'active',
    ]);
    $legacyReferrals[] = $legacyReferral;
    echo "  Legacy Referral #{$i}: {$legacyReferral->username} (ID: {$legacyReferral->id})\n";
}

echo "\n";
echo "Legacy sponsor now has " . ($requiredSponsors - 1) . " existing referrals (not tracked yet)\n";
echo "These referrals existed BEFORE rank system was deployed\n\n";

// Check current progress (should count legacy referrals)
$progressBefore = $rankService->getRankAdvancementProgress($legacySponsor->fresh());
$trackedCountBefore = $legacySponsor->directSponsorsTracked()->count();

echo "Progress Before New Referral:\n";
echo "  Tracked in direct_sponsors_tracker: {$trackedCountBefore}\n";
echo "  Total Same-Rank (including legacy): {$progressBefore['sponsors_count']}/{$progressBefore['required_sponsors']}\n";
echo "  Progress: " . number_format($progressBefore['progress_percentage'], 1) . "%\n";
echo "  Remaining: {$progressBefore['remaining_sponsors']}\n\n";

if ($progressBefore['sponsors_count'] !== ($requiredSponsors - 1)) {
    echo "❌ ERROR: Legacy referrals not counted correctly!\n";
    echo "   Expected: " . ($requiredSponsors - 1) . ", Got: {$progressBefore['sponsors_count']}\n";
    exit(1);
}

echo "✓ Legacy referrals are correctly counted!\n\n";

// Now add ONE MORE referral (should trigger advancement due to legacy count)
echo "Now adding ONE more referral (should trigger advancement)...\n";
$finalReferral = User::factory()->create([
    'username' => 'final_referral_' . time(),
    'fullname' => 'Final Referral',
    'email' => 'final_referral_' . time() . '@test.com',
    'password' => bcrypt('password'),
    'sponsor_id' => $legacySponsor->id,
    'current_rank' => 'Starter',
    'rank_package_id' => $starter->id,
    'network_status' => 'active',
]);

echo "New Referral: {$finalReferral->username} (ID: {$finalReferral->id})\n\n";

// Track the new sponsorship
$advanced = $rankService->trackSponsorship($legacySponsor, $finalReferral);

if ($advanced) {
    echo "★★★ LEGACY USER RANK ADVANCEMENT TRIGGERED! ★★★\n";
    echo "Sponsor advanced to: {$legacySponsor->fresh()->current_rank}\n\n";
    
    // Check if order was created
    $latestOrder = $legacySponsor->orders()->latest()->first();
    if ($latestOrder) {
        echo "System-funded order: {$latestOrder->order_number}\n";
        echo "Order amount: ₱" . number_format($latestOrder->grand_total, 2) . "\n";
        echo "Payment method: {$latestOrder->payment_method}\n\n";
    }
    
    // Verify legacy referrals were backfilled
    $trackedAfter = $legacySponsor->directSponsorsTracked()->count();
    echo "Legacy sponsorships backfilled: {$trackedAfter}\n";
    echo "(Was 1, now includes all legacy referrals)\n\n";
    
    // Check advancement record
    $advancement = $legacySponsor->rankAdvancements()->latest()->first();
    if ($advancement) {
        echo "Advancement details:\n";
        echo "  Type: {$advancement->advancement_type}\n";
        echo "  From: {$advancement->from_rank}\n";
        echo "  To: {$advancement->to_rank}\n";
        echo "  Total sponsors counted: {$advancement->sponsors_count}\n";
        echo "  System paid: ₱" . number_format($advancement->system_paid_amount, 2) . "\n";
    }
    
    echo "\n✓ TEST PASSED: Legacy user successfully advanced!\n";
    echo "✓ Backward compatibility working correctly!\n";
} else {
    echo "❌ TEST FAILED: ADVANCEMENT NOT TRIGGERED (this is a bug!)\n";
    echo "Expected: Legacy user should advance after adding final referral\n";
    
    $progressAfter = $rankService->getRankAdvancementProgress($legacySponsor->fresh());
    echo "\nDebug info:\n";
    echo "  Total sponsors: {$progressAfter['sponsors_count']}/{$progressAfter['required_sponsors']}\n";
    echo "  Is eligible: " . ($progressAfter['is_eligible'] ? 'Yes' : 'No') . "\n";
}

echo "\nLegacy User Test Completed!\n";
