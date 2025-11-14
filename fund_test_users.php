<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Wallet;

echo "=== Funding Test Users ===\n\n";

$testUsernames = ['admin', 'member', 'member2', 'member3', 'member4', 'member5'];

foreach ($testUsernames as $username) {
    $user = User::where('username', $username)->first();

    if (!$user) {
        echo "❌ User '{$username}' not found\n";
        continue;
    }

    $wallet = Wallet::updateOrCreate(
        ['user_id' => $user->id],
        [
            'mlm_balance' => 0.00,
            'purchase_balance' => 1000.00,
            'is_active' => true,
        ]
    );

    echo "✅ {$username} (ID: {$user->id}) - Wallet funded: ₱1,000 purchase balance\n";
}

echo "\n✅ All test users funded successfully!\n";
