<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Hash;

echo "=== Creating 5-Level Test Hierarchy ===\n\n";

// Check existing users
$admin = User::where('username', 'admin')->first();
$member = User::where('username', 'member')->first();

if (!$admin) {
    echo "❌ Admin user not found. Run: php artisan db:seed --class=DatabaseResetSeeder\n";
    exit(1);
}

echo "Existing users:\n";
echo "- admin (ID: {$admin->id})\n";
echo "- member (ID: {$member->id}, sponsor: admin)\n\n";

// Create test user hierarchy
// Admin → Member → Member2 → Member3 → Member4 → Member5 (buyer)

$testUsers = [
    [
        'username' => 'member2',
        'email' => 'member2@example.com',
        'sponsor' => 'member',
        'level_description' => 'Level 3'
    ],
    [
        'username' => 'member3',
        'email' => 'member3@example.com',
        'sponsor' => 'member2',
        'level_description' => 'Level 2'
    ],
    [
        'username' => 'member4',
        'email' => 'member4@example.com',
        'sponsor' => 'member3',
        'level_description' => 'Level 1 (Direct Sponsor)'
    ],
    [
        'username' => 'member5',
        'email' => 'member5@example.com',
        'sponsor' => 'member4',
        'level_description' => 'Buyer (Bottom of chain)'
    ],
];

echo "Creating test users...\n\n";

foreach ($testUsers as $userData) {
    // Check if user exists
    $existing = User::where('username', $userData['username'])->first();

    if ($existing) {
        echo "⚠️  User '{$userData['username']}' already exists (ID: {$existing->id})\n";
        continue;
    }

    // Get sponsor
    $sponsor = User::where('username', $userData['sponsor'])->first();

    if (!$sponsor) {
        echo "❌ Sponsor '{$userData['sponsor']}' not found for {$userData['username']}\n";
        continue;
    }

    // Create user
    $user = User::create([
        'username' => $userData['username'],
        'fullname' => ucfirst($userData['username']),
        'email' => $userData['email'],
        'password' => Hash::make('password'),
        'sponsor_id' => $sponsor->id,
        'referral_code' => 'REF' . strtoupper(substr(md5($userData['username']), 0, 8)),
        'email_verified_at' => now(), // Verify all test users
    ]);

    // Assign member role
    $user->assignRole('member');

    // Create wallet with initial balance (or update if exists)
    Wallet::updateOrCreate(
        ['user_id' => $user->id],
        [
            'mlm_balance' => 0.00,
            'purchase_balance' => 1000.00, // Give test users funds to purchase
            'is_active' => true,
        ]
    );

    echo "✅ Created user: {$user->username} (ID: {$user->id}) - {$userData['level_description']}\n";
    echo "   Sponsor: {$sponsor->username} (ID: {$sponsor->id})\n";
    echo "   Email: {$user->email} (✅ Verified)\n";
    echo "   Wallet: ₱1,000 purchase balance\n\n";
}

// Verify the complete chain
echo "\n" . str_repeat("=", 70) . "\n";
echo "FINAL HIERARCHY VERIFICATION\n";
echo str_repeat("=", 70) . "\n\n";

$buyer = User::where('username', 'member5')->first();

if (!$buyer) {
    echo "❌ Buyer (member5) not found\n";
    exit(1);
}

echo "Testing upline chain for: {$buyer->username} (ID: {$buyer->id})\n\n";

$current = $buyer;
$level = 0;

echo str_repeat("-", 70) . "\n";
printf("%-15s | %-10s | %-20s | %-15s\n", "Position", "User ID", "Username", "Level");
echo str_repeat("-", 70) . "\n";

printf("%-15s | %-10s | %-20s | %-15s\n",
    "Buyer",
    $current->id,
    $current->username,
    "-"
);

while ($current->sponsor && $level < 5) {
    $level++;
    $current = $current->sponsor;

    $levelDesc = $level === 1 ? "L1 (Direct)" : "L{$level}";

    printf("%-15s | %-10s | %-20s | %-15s\n",
        "Sponsor L{$level}",
        $current->id,
        $current->username,
        $levelDesc
    );
}

echo str_repeat("-", 70) . "\n";

echo "\n✅ Hierarchy created successfully!\n";
echo "Total upline levels: {$level}\n\n";

echo "Expected Commission Distribution (when member5 buys ₱1,000 package):\n";
echo "- member4 (L1): ₱200\n";
echo "- member3 (L2): ₱50\n";
echo "- member2 (L3): ₱50\n";
echo "- member  (L4): ₱50\n";
echo "- admin   (L5): ₱50\n";
echo "- Total: ₱400\n";
