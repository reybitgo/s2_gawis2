<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Phase 2 Rank-Aware Commission with Real Order Scenario\n";
echo str_repeat("=", 80) . "\n\n";

// 1. Find or create test users
echo "Step 1: Setting up test hierarchy...\n";

$sponsor = \App\Models\User::where('username', 'test_sponsor_phase2')->first();
if (!$sponsor) {
    $sponsor = \App\Models\User::create([
        'username' => 'test_sponsor_phase2',
        'email' => 'test_sponsor_phase2@example.com',
        'password' => bcrypt('password'),
        'fullname' => 'Test Sponsor',
        'current_rank' => 'Starter',
        'rank_package_id' => 1,
        'network_status' => 'active',
        'sponsor_id' => 1, // Admin as sponsor
    ]);
    
    // Create wallet if doesn't exist
    if (!\App\Models\Wallet::where('user_id', $sponsor->id)->exists()) {
        \App\Models\Wallet::create([
            'user_id' => $sponsor->id,
            'balance' => 0,
            'mlm_balance' => 0,
        ]);
    }
    
    echo "✓ Created test sponsor: {$sponsor->username} (Rank: {$sponsor->current_rank})\n";
} else {
    echo "✓ Using existing sponsor: {$sponsor->username} (Rank: {$sponsor->current_rank})\n";
}

$buyer = \App\Models\User::where('username', 'test_buyer_phase2')->first();
if (!$buyer) {
    $buyer = \App\Models\User::create([
        'username' => 'test_buyer_phase2',
        'email' => 'test_buyer_phase2@example.com',
        'password' => bcrypt('password'),
        'fullname' => 'Test Buyer',
        'sponsor_id' => $sponsor->id,
        'current_rank' => 'Starter',
        'rank_package_id' => 1,
        'network_status' => 'active',
    ]);
    
    // Create wallet if doesn't exist
    if (!\App\Models\Wallet::where('user_id', $buyer->id)->exists()) {
        \App\Models\Wallet::create([
            'user_id' => $buyer->id,
            'balance' => 5000,
            'mlm_balance' => 0,
        ]);
    }
    
    echo "✓ Created test buyer: {$buyer->username} (Rank: {$buyer->current_rank})\n";
} else {
    echo "✓ Using existing buyer: {$buyer->username} (Rank: {$buyer->current_rank})\n";
}

echo "\n";

// 2. Get Starter package
echo "Step 2: Getting package details...\n";
$package = \App\Models\Package::where('rank_name', 'Starter')->first();

if (!$package) {
    echo "✗ Starter package not found! Run setup_rank_packages.php first.\n";
    exit(1);
}

echo "✓ Package: {$package->name} (Rank: {$package->rank_name}, Price: ₱{$package->price})\n\n";

// 3. Record sponsor's initial balance
$initialBalance = $sponsor->wallet->mlm_balance;
echo "Step 3: Initial sponsor MLM balance: ₱" . number_format($initialBalance, 2) . "\n\n";

// 4. Create test order
echo "Step 4: Creating test order...\n";

$orderNumber = 'TEST-PHASE2-' . time();
$order = \App\Models\Order::create([
    'user_id' => $buyer->id,
    'order_number' => $orderNumber,
    'status' => 'confirmed',
    'payment_status' => 'paid',
    'payment_method' => 'wallet',
    'delivery_method' => 'office_pickup',
    'subtotal' => $package->price,
    'tax_amount' => 0,
    'total_amount' => $package->price,
    'tax_rate' => 0,
    'paid_at' => now(),
]);

echo "✓ Order created: {$order->order_number}\n";

// 5. Add package to order
\App\Models\OrderItem::create([
    'order_id' => $order->id,
    'package_id' => $package->id,
    'item_type' => 'package',
    'quantity' => 1,
    'unit_price' => $package->price,
    'total_price' => $package->price,
]);

echo "✓ Order item added: 1x {$package->name}\n\n";

// 6. Process MLM commissions with Phase 2 logic
echo "Step 5: Processing MLM commissions (Rank-Aware)...\n";

$mlmService = app(\App\Services\MLMCommissionService::class);
$result = $mlmService->processCommissions($order);

if (!$result) {
    echo "✗ MLM Commission processing failed!\n";
    exit(1);
}

echo "✓ MLM Commission processed successfully\n\n";

// 7. Check sponsor's new balance
$sponsor->wallet->refresh();
$newBalance = $sponsor->wallet->mlm_balance;
$commission = $newBalance - $initialBalance;

echo str_repeat("=", 80) . "\n";
echo "RESULTS\n";
echo str_repeat("=", 80) . "\n";
echo "Sponsor: {$sponsor->username} (Rank: {$sponsor->current_rank})\n";
echo "Buyer: {$buyer->username} (Rank: {$buyer->current_rank})\n";
echo "Package: {$package->name} (₱{$package->price})\n";
echo "Order: {$order->order_number}\n";
echo "\n";
echo "Initial MLM Balance: ₱" . number_format($initialBalance, 2) . "\n";
echo "New MLM Balance: ₱" . number_format($newBalance, 2) . "\n";
echo "Commission Earned: ₱" . number_format($commission, 2) . "\n";
echo "\n";

// 8. Verify rank-aware logic
$expectedCommission = 200.00; // Starter → Starter = Rule 3 (Same Rank) = ₱200.00

if (abs($commission - $expectedCommission) < 0.01) {
    echo "✓ Commission amount is CORRECT!\n";
    echo "✓ Expected: ₱" . number_format($expectedCommission, 2) . "\n";
    echo "✓ Received: ₱" . number_format($commission, 2) . "\n";
    echo "✓ Rule Applied: Rule 3 (Same Rank → Standard Rate)\n";
} else {
    echo "✗ Commission amount MISMATCH!\n";
    echo "Expected: ₱" . number_format($expectedCommission, 2) . "\n";
    echo "Received: ₱" . number_format($commission, 2) . "\n";
    echo "\nThis might indicate Phase 2 is not deployed correctly.\n";
}

echo "\n";
echo str_repeat("=", 80) . "\n";
echo "Check Laravel logs for 'Rank-Aware Commission Calculated' entries\n";
echo "Location: storage/logs/laravel.log\n";
echo str_repeat("=", 80) . "\n";

echo "\n✓ Phase 2 Test Completed!\n";
