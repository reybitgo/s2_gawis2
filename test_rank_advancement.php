<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Services\RankAdvancementService;
use Illuminate\Support\Facades\DB;

echo "=== Phase 3: Rank Advancement Test (New Users) ===\n\n";

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

echo "Test Setup:\n";
echo "Starter package requires {$starter->required_direct_sponsors} sponsors\n";
echo "Newbie package costs ₱" . number_format($newbie->price, 2) . "\n\n";

// Create sponsor with Starter rank
$sponsor = User::factory()->create([
    'username' => 'test_sponsor_' . time(),
    'fullname' => 'Test Sponsor',
    'email' => 'test_sponsor_' . time() . '@test.com',
    'password' => bcrypt('password'),
    'current_rank' => 'Starter',
    'rank_package_id' => $starter->id,
    'network_status' => 'active',
]);

echo "Created Sponsor: {$sponsor->username} (ID: {$sponsor->id}, Rank: Starter)\n\n";

// Create required number of Starter-rank users
$requiredSponsors = $starter->required_direct_sponsors;

echo "Creating {$requiredSponsors} Starter-rank users...\n";

for ($i = 1; $i <= $requiredSponsors; $i++) {
    $newUser = User::factory()->create([
        'username' => 'test_referral_' . $i . '_' . time(),
        'fullname' => "Test Referral {$i}",
        'email' => "test_referral_{$i}_" . time() . '@test.com',
        'password' => bcrypt('password'),
        'sponsor_id' => $sponsor->id,
        'current_rank' => 'Starter',
        'rank_package_id' => $starter->id,
        'network_status' => 'active',
    ]);
    
    echo "  [{$i}/{$requiredSponsors}] Registering User: {$newUser->username} (ID: {$newUser->id})\n";
    
    $advanced = $rankService->trackSponsorship($sponsor, $newUser);
    
    $progress = $rankService->getRankAdvancementProgress($sponsor->fresh());
    echo "      Progress: {$progress['sponsors_count']}/{$progress['required_sponsors']} " .
         "(" . number_format($progress['progress_percentage'], 1) . "%)\n";
    
    if ($advanced) {
        echo "\n";
        echo "      ★★★ RANK ADVANCEMENT TRIGGERED! ★★★\n";
        echo "      Sponsor advanced to: {$sponsor->fresh()->current_rank}\n";
        
        // Check if order was created
        $latestOrder = $sponsor->orders()->latest()->first();
        if ($latestOrder) {
            echo "      System-funded order: {$latestOrder->order_number}\n";
            echo "      Order amount: ₱" . number_format($latestOrder->grand_total, 2) . "\n";
            echo "      Payment method: {$latestOrder->payment_method}\n";
        }
        
        // Check rank advancement record
        $advancement = $sponsor->rankAdvancements()->latest()->first();
        if ($advancement) {
            echo "      Advancement type: {$advancement->advancement_type}\n";
            echo "      From rank: {$advancement->from_rank}\n";
            echo "      To rank: {$advancement->to_rank}\n";
            echo "      Sponsors count: {$advancement->sponsors_count}\n";
            echo "      System paid: ₱" . number_format($advancement->system_paid_amount, 2) . "\n";
        }
        
        break;
    }
    
    echo "\n";
}

echo "\n=== Test Results ===\n";
echo "Final sponsor rank: {$sponsor->fresh()->current_rank}\n";
echo "Total direct sponsorships tracked: " . $sponsor->directSponsorsTracked()->count() . "\n";
echo "Total rank advancements: " . $sponsor->rankAdvancements()->count() . "\n";
echo "Total orders: " . $sponsor->orders()->count() . "\n";

if ($sponsor->fresh()->current_rank === 'Newbie') {
    echo "\n✓ TEST PASSED: Sponsor successfully advanced to Newbie rank!\n";
} else {
    echo "\n❌ TEST FAILED: Sponsor did not advance to Newbie rank.\n";
}

echo "\nPhase 3 Test Completed!\n";
