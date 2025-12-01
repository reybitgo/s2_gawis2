<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Models\Wallet;
use App\Models\RankAdvancement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=== Setting Up Phase 4 Test Users ===\n\n";

// Get packages
$starter = Package::where('rank_name', 'Starter')->first();
$newbie = Package::where('rank_name', 'Newbie')->first();
$bronze = Package::where('rank_name', 'Bronze')->first();

if (!$starter || !$newbie || !$bronze) {
    echo "❌ ERROR: Rank packages not found!\n";
    echo "Please ensure Phase 1 packages are set up correctly.\n";
    exit(1);
}

echo "Found rank packages:\n";
echo "  - Starter (ID: {$starter->id}, requires {$starter->required_direct_sponsors} sponsors)\n";
echo "  - Newbie (ID: {$newbie->id}, requires {$newbie->required_direct_sponsors} sponsors)\n";
echo "  - Bronze (ID: {$bronze->id}, requires {$bronze->required_direct_sponsors} sponsors)\n\n";

// Clean up existing test users
echo "Cleaning up existing test users...\n";
DB::table('users')->where('username', 'LIKE', 'test_%')->orWhere('username', 'LIKE', 'referral_%')->delete();
echo "✓ Cleanup complete\n\n";

// Test User 1: Unranked User (no package purchased)
echo "1. Creating Unranked User...\n";
$unranked = User::create([
    'username' => 'test_unranked',
    'fullname' => 'Unranked Test User',
    'email' => 'test_unranked@test.com',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
]);
Wallet::create(['user_id' => $unranked->id, 'balance' => 0]);
echo "   ✓ Username: test_unranked | Password: password\n\n";

// Test User 2: Starter Rank (0% progress - no sponsors)
echo "2. Creating Starter User (0% progress)...\n";
$starterZero = User::create([
    'username' => 'test_starter_0',
    'fullname' => 'Starter Zero Progress',
    'email' => 'test_starter_0@test.com',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
    'current_rank' => 'Starter',
    'rank_package_id' => $starter->id,
    'rank_updated_at' => now()->subDays(5),
    'network_status' => 'active',
]);
Wallet::create(['user_id' => $starterZero->id, 'balance' => 0, 'withdrawable_balance' => 0]);
echo "   ✓ Username: test_starter_0 | Password: password\n\n";

// Test User 3: Starter Rank (60% progress - 3/5 sponsors)
echo "3. Creating Starter User (60% progress)...\n";
$starterSixty = User::create([
    'username' => 'test_starter_60',
    'fullname' => 'Starter Sixty Progress',
    'email' => 'test_starter_60@test.com',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
    'current_rank' => 'Starter',
    'rank_package_id' => $starter->id,
    'rank_updated_at' => now()->subDays(10),
    'network_status' => 'active',
]);
Wallet::create(['user_id' => $starterSixty->id, 'balance' => 500.00, 'withdrawable_balance' => 250.50]);

// Create 3 referrals for 60% progress
for ($i = 1; $i <= 3; $i++) {
    User::create([
        'username' => "referral_60_{$i}",
        'fullname' => "Referral {$i}",
        'email' => "referral_60_{$i}@test.com",
        'password' => Hash::make('password'),
        'sponsor_id' => $starterSixty->id,
        'current_rank' => 'Starter',
        'rank_package_id' => $starter->id,
    ]);
}
echo "   ✓ Username: test_starter_60 | Password: password | Income: ₱250.50 | Referrals: 3\n\n";

// Test User 4: Starter Rank (100% progress - 5/5 sponsors, eligible)
echo "4. Creating Starter User (100% eligible)...\n";
$starterEligible = User::create([
    'username' => 'test_starter_eligible',
    'fullname' => 'Starter Eligible',
    'email' => 'test_starter_eligible@test.com',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
    'current_rank' => 'Starter',
    'rank_package_id' => $starter->id,
    'rank_updated_at' => now()->subDays(15),
    'network_status' => 'active',
]);
Wallet::create(['user_id' => $starterEligible->id, 'balance' => 1500.00, 'withdrawable_balance' => 1234.56]);

// Create 5 referrals for 100% progress
for ($i = 1; $i <= 5; $i++) {
    User::create([
        'username' => "referral_eligible_{$i}",
        'fullname' => "Referral {$i}",
        'email' => "referral_eligible_{$i}@test.com",
        'password' => Hash::make('password'),
        'sponsor_id' => $starterEligible->id,
        'current_rank' => 'Starter',
        'rank_package_id' => $starter->id,
    ]);
}
echo "   ✓ Username: test_starter_eligible | Password: password | Income: ₱1,234.56 | Referrals: 5\n\n";

// Test User 5: Newbie Rank (with advancement history)
echo "5. Creating Newbie User (with history)...\n";
$newbieUser = User::create([
    'username' => 'test_newbie',
    'fullname' => 'Newbie Test User',
    'email' => 'test_newbie@test.com',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
    'current_rank' => 'Newbie',
    'rank_package_id' => $newbie->id,
    'rank_updated_at' => now()->subDays(3),
    'network_status' => 'active',
]);
Wallet::create(['user_id' => $newbieUser->id, 'balance' => 5000.00, 'withdrawable_balance' => 3456.78]);

// Create advancement history
RankAdvancement::create([
    'user_id' => $newbieUser->id,
    'from_rank' => null,
    'to_rank' => 'Starter',
    'from_package_id' => null,
    'to_package_id' => $starter->id,
    'advancement_type' => 'purchase',
    'created_at' => now()->subDays(20),
]);

RankAdvancement::create([
    'user_id' => $newbieUser->id,
    'from_rank' => 'Starter',
    'to_rank' => 'Newbie',
    'from_package_id' => $starter->id,
    'to_package_id' => $newbie->id,
    'advancement_type' => 'sponsorship_reward',
    'sponsors_count' => 5,
    'system_paid_amount' => 2500.00,
    'created_at' => now()->subDays(3),
]);

// Create 2 referrals for Newbie (2/8 progress)
for ($i = 1; $i <= 2; $i++) {
    User::create([
        'username' => "referral_newbie_{$i}",
        'fullname' => "Newbie Referral {$i}",
        'email' => "referral_newbie_{$i}@test.com",
        'password' => Hash::make('password'),
        'sponsor_id' => $newbieUser->id,
        'current_rank' => 'Newbie',
        'rank_package_id' => $newbie->id,
    ]);
}
echo "   ✓ Username: test_newbie | Password: password | Income: ₱3,456.78 | Referrals: 2 | History: 2\n\n";

// Test User 6: Bronze Rank (Top Rank - no next rank)
echo "6. Creating Bronze User (Top Rank)...\n";
$bronzeUser = User::create([
    'username' => 'test_bronze',
    'fullname' => 'Bronze Test User',
    'email' => 'test_bronze@test.com',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
    'current_rank' => 'Bronze',
    'rank_package_id' => $bronze->id,
    'rank_updated_at' => now()->subDays(1),
    'network_status' => 'active',
]);
Wallet::create(['user_id' => $bronzeUser->id, 'balance' => 20000.00, 'withdrawable_balance' => 12345.67]);

// Create advancement history (multiple advancements)
RankAdvancement::create([
    'user_id' => $bronzeUser->id,
    'from_rank' => null,
    'to_rank' => 'Starter',
    'from_package_id' => null,
    'to_package_id' => $starter->id,
    'advancement_type' => 'purchase',
    'created_at' => now()->subDays(30),
]);

RankAdvancement::create([
    'user_id' => $bronzeUser->id,
    'from_rank' => 'Starter',
    'to_rank' => 'Newbie',
    'from_package_id' => $starter->id,
    'to_package_id' => $newbie->id,
    'advancement_type' => 'sponsorship_reward',
    'sponsors_count' => 5,
    'system_paid_amount' => 2500.00,
    'created_at' => now()->subDays(10),
]);

RankAdvancement::create([
    'user_id' => $bronzeUser->id,
    'from_rank' => 'Newbie',
    'to_rank' => 'Bronze',
    'from_package_id' => $newbie->id,
    'to_package_id' => $bronze->id,
    'advancement_type' => 'sponsorship_reward',
    'sponsors_count' => 8,
    'system_paid_amount' => 5000.00,
    'created_at' => now()->subDays(1),
]);

echo "   ✓ Username: test_bronze | Password: password | Income: ₱12,345.67 | History: 3\n\n";

echo "=== Test Users Created Successfully ===\n\n";

echo "📋 Test User Summary:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "│ Username              │ Rank    │ Progress │ Income     │\n";
echo "├───────────────────────┼─────────┼──────────┼────────────┤\n";
echo "│ test_unranked         │ None    │ N/A      │ ₱0.00      │\n";
echo "│ test_starter_0        │ Starter │ 0/5 (0%) │ ₱0.00      │\n";
echo "│ test_starter_60       │ Starter │ 3/5 (60%)│ ₱250.50    │\n";
echo "│ test_starter_eligible │ Starter │ 5/5 ✓    │ ₱1,234.56  │\n";
echo "│ test_newbie           │ Newbie  │ 2/8 (25%)│ ₱3,456.78  │\n";
echo "│ test_bronze           │ Bronze  │ Top Rank │ ₱12,345.67 │\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "All passwords: password\n\n";

echo "📊 Test Data Statistics:\n";
echo "  - Total test users created: " . User::where('username', 'LIKE', 'test_%')->count() . "\n";
echo "  - Total referrals created: " . User::where('username', 'LIKE', 'referral_%')->count() . "\n";
echo "  - Total rank advancements: " . RankAdvancement::whereIn('user_id', function($query) {
    $query->select('id')->from('users')->where('username', 'LIKE', 'test_%');
})->count() . "\n\n";

echo "✨ Ready for Phase 4 Testing!\n\n";

echo "🚀 Quick Test Commands:\n";
echo "  1. Visit: http://127.0.0.1:8000/login\n";
echo "  2. Login with any test_* username\n";
echo "  3. View Profile to see rank information\n";
echo "  4. Login as admin to view User Management table\n\n";

echo "🧹 To cleanup test data later:\n";
echo "  php artisan tinker\n";
echo "  >>> App\Models\User::where('username', 'LIKE', 'test_%')->delete();\n";
echo "  >>> App\Models\User::where('username', 'LIKE', 'referral_%')->delete();\n\n";

echo "✓ Setup Complete!\n";
