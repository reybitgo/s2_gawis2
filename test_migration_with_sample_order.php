<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;

echo "=== Testing Migration with Sample Order ===\n\n";

// Get admin user (or create test user)
$user = User::where('email', 'admin@ewallet.com')->first();

if (!$user) {
    echo "❌ Admin user not found. Creating test user...\n";
    $user = User::create([
        'username' => 'migration_test_user',
        'fullname' => 'Migration Test',
        'email' => 'migration_test@test.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);
    echo "✅ Created test user: {$user->username}\n\n";
} else {
    echo "Using user: {$user->username}\n\n";
}

// Get Starter package
$starter = Package::where('rank_name', 'Starter')->first();

if (!$starter) {
    echo "❌ Starter package not found. Run seeder first.\n";
    exit(1);
}

// Create a test order
echo "Creating test order...\n";
$order = Order::create([
    'user_id' => $user->id,
    'order_number' => 'MIGRATION-TEST-' . time(),
    'status' => 'confirmed',
    'payment_status' => 'paid',
    'payment_method' => 'test',
    'subtotal' => $starter->price,
    'total_amount' => $starter->price,
    'grand_total' => $starter->price,
]);

OrderItem::create([
    'order_id' => $order->id,
    'package_id' => $starter->id,
    'quantity' => 1,
    'unit_price' => $starter->price,
    'price' => $starter->price,
    'total_price' => $starter->price,
    'subtotal' => $starter->price,
]);

echo "✅ Created order #{$order->order_number}\n";
echo "   Package: {$starter->name}\n";
echo "   User rank before: " . ($user->current_rank ?? 'NULL') . "\n\n";

// Now run the rank assignment migration logic manually
echo "Running rank assignment logic...\n";
$user->updateRank();
$user->refresh();

echo "✅ Rank assignment completed!\n";
echo "   User rank after: " . ($user->current_rank ?? 'NULL') . "\n\n";

// Verify
if ($user->current_rank === 'Starter') {
    echo "✅ SUCCESS! Migration logic works correctly!\n";
    echo "   User got assigned 'Starter' rank as expected.\n\n";
} else {
    echo "❌ FAILED! User should have 'Starter' rank but has: " . ($user->current_rank ?? 'NULL') . "\n\n";
}

// Cleanup
echo "Cleaning up test data...\n";
$order->orderItems()->delete();
$order->delete();

if ($user->username === 'migration_test_user') {
    $user->delete();
    echo "✅ Deleted test user\n";
} else {
    // Reset admin user rank for clean state
    $user->update([
        'current_rank' => null,
        'rank_package_id' => null,
        'rank_updated_at' => null,
    ]);
    echo "✅ Reset user rank\n";
}

echo "\n✅ Test completed and cleaned up!\n";
