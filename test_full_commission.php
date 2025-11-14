<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Wallet;
use App\Models\Order;
use App\Models\Package;
use App\Models\Transaction;
use App\Services\MLMCommissionService;
use App\Jobs\ProcessMLMCommissions;
use Illuminate\Support\Facades\DB;

echo "=== Test Case 12.1: Full 5-Level Commission Distribution ===\n\n";

// Step 1: Verify test hierarchy exists
echo "Step 1: Verifying 5-level test hierarchy...\n";

$testUsernames = ['admin', 'member', 'member2', 'member3', 'member4', 'member5'];
$users = [];

foreach ($testUsernames as $username) {
    $user = User::where('username', $username)->first();
    if (!$user) {
        echo "❌ ERROR: User '{$username}' not found\n";
        exit(1);
    }
    $users[$username] = $user;
}

echo "✅ All test users found\n\n";

// Step 2: Record initial balances
echo "Step 2: Recording initial MLM balances...\n";
echo str_repeat("-", 70) . "\n";
printf("%-15s | %-10s | %-15s | %-15s\n", "Username", "User ID", "MLM Balance", "Purchase Bal");
echo str_repeat("-", 70) . "\n";

$initialBalances = [];
foreach ($testUsernames as $username) {
    $user = $users[$username];
    $wallet = $user->wallet;

    if (!$wallet) {
        echo "❌ ERROR: User '{$username}' has no wallet\n";
        exit(1);
    }

    $initialBalances[$username] = [
        'mlm' => (float)$wallet->mlm_balance,
        'purchase' => (float)$wallet->purchase_balance,
    ];

    printf("%-15s | %-10s | ₱%-14.2f | ₱%-14.2f\n",
        $username,
        $user->id,
        $wallet->mlm_balance,
        $wallet->purchase_balance
    );
}
echo str_repeat("-", 70) . "\n\n";

// Step 3: Verify Starter Package exists and is MLM package
echo "Step 3: Verifying Starter Package configuration...\n";

$package = Package::where('is_mlm_package', true)->first();

if (!$package) {
    echo "❌ ERROR: No MLM package found in database\n";
    exit(1);
}

echo "✅ MLM Package found:\n";
echo "   Name: {$package->name}\n";
echo "   Price: ₱" . number_format($package->price, 2) . "\n";
echo "   Max MLM Levels: {$package->max_mlm_levels}\n";
echo "   Is MLM Package: " . ($package->is_mlm_package ? 'Yes' : 'No') . "\n\n";

// Step 4: Check MLM settings
echo "Step 4: Verifying MLM commission settings...\n";
$mlmSettings = DB::table('mlm_settings')
    ->where('package_id', $package->id)
    ->where('is_active', true)
    ->orderBy('level')
    ->get();

if ($mlmSettings->isEmpty()) {
    echo "❌ ERROR: No MLM settings found for package\n";
    exit(1);
}

echo str_repeat("-", 50) . "\n";
printf("%-10s | %-20s\n", "Level", "Commission");
echo str_repeat("-", 50) . "\n";

foreach ($mlmSettings as $setting) {
    printf("%-10s | ₱%-19.2f\n", $setting->level, $setting->commission_amount);
}
echo str_repeat("-", 50) . "\n\n";

// Step 5: Make all upline members active (must have purchased a package to earn commissions)
echo "Step 5: Activating upline members (creating purchase history)...\n";

$uplineMembers = ['admin', 'member', 'member2', 'member3', 'member4'];
foreach ($uplineMembers as $username) {
    $user = $users[$username];

    // Check if already active
    if ($user->isActive()) {
        echo "   ✅ {$username} is already active\n";
        continue;
    }

    // Create a simple activation order
    $activationOrder = Order::create([
        'user_id' => $user->id,
        'order_number' => 'ACTIVATE-' . date('Ymd') . '-' . $user->id,
        'total_amount' => $package->price,
        'subtotal' => $package->price,
        'tax_amount' => 0,
        'status' => 'completed',
        'payment_status' => 'paid',
        'delivery_method' => 'office_pickup',
        'paid_at' => now(),
        'completed_at' => now(),
    ]);

    // Create order item for activation
    DB::table('order_items')->insert([
        'order_id' => $activationOrder->id,
        'package_id' => $package->id,
        'quantity' => 1,
        'unit_price' => $package->price,
        'total_price' => $package->price,
        'points_awarded_per_item' => 0,
        'total_points_awarded' => 0,
        'package_snapshot' => json_encode([
            'name' => $package->name,
            'price' => $package->price,
            'description' => 'Activation package',
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "   ✅ {$username} activated (Order: {$activationOrder->order_number})\n";
}

echo "\n";

// Step 6: Simulate purchase by member5
echo "Step 6: Simulating package purchase by member5...\n";

$buyer = $users['member5'];
$buyerWallet = $buyer->wallet;

// Check if buyer has sufficient balance
if ($buyerWallet->total_balance < $package->price) {
    echo "❌ ERROR: Buyer has insufficient balance\n";
    echo "   Required: ₱{$package->price}, Available: ₱{$buyerWallet->total_balance}\n";
    exit(1);
}

echo "✅ Buyer has sufficient balance: ₱{$buyerWallet->total_balance}\n\n";

// Create a test order
echo "Step 7: Creating test order...\n";

DB::beginTransaction();

try {
    $order = Order::create([
        'user_id' => $buyer->id,
        'order_number' => 'TEST-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
        'total_amount' => $package->price,
        'subtotal' => $package->price,
        'tax_amount' => 0,
        'status' => 'pending',
        'payment_status' => 'pending',
        'delivery_method' => 'office_pickup',
    ]);

    // Create order item
    DB::table('order_items')->insert([
        'order_id' => $order->id,
        'package_id' => $package->id,
        'quantity' => 1,
        'unit_price' => $package->price,
        'total_price' => $package->price,
        'points_awarded_per_item' => 0,
        'total_points_awarded' => 0,
        'package_snapshot' => json_encode([
            'name' => $package->name,
            'price' => $package->price,
            'description' => $package->description,
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Deduct from buyer's wallet
    $buyerWallet->deductCombinedBalance($package->price);

    // Mark order as paid
    $order->update([
        'payment_status' => 'paid',
        'status' => 'confirmed',
    ]);

    DB::commit();

    echo "✅ Test order created: {$order->order_number}\n";
    echo "   Order ID: {$order->id}\n";
    echo "   Amount: ₱{$order->total_amount}\n";
    echo "   Status: {$order->status}\n";
    echo "   Payment Status: {$order->payment_status}\n\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ ERROR: Failed to create order: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 8: Process MLM commissions
echo "Step 8: Processing MLM commissions (dispatchSync)...\n";

try {
    // Load order relationships
    $order->load('orderItems.package');

    // Process commissions synchronously
    ProcessMLMCommissions::dispatchSync($order);

    echo "✅ Commission processing completed\n\n";

} catch (\Exception $e) {
    echo "❌ ERROR: Commission processing failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

// Step 9: Record final balances
echo "Step 9: Recording final MLM balances...\n";
echo str_repeat("-", 90) . "\n";
printf("%-15s | %-15s | %-15s | %-15s | %-15s\n",
    "Username", "Initial MLM", "Final MLM", "Difference", "Expected");
echo str_repeat("-", 90) . "\n";

$expectedCommissions = [
    'member4' => 200.00, // L1
    'member3' => 50.00,  // L2
    'member2' => 50.00,  // L3
    'member' => 50.00,   // L4
    'admin' => 50.00,    // L5
    'member5' => 0.00,   // Buyer (no commission)
];

$actualTotal = 0;
$expectedTotal = 0;
$allCorrect = true;

foreach ($testUsernames as $username) {
    $user = $users[$username];
    $wallet = $user->wallet->fresh();

    $initialMLM = $initialBalances[$username]['mlm'];
    $finalMLM = (float)$wallet->mlm_balance;
    $difference = $finalMLM - $initialMLM;
    $expected = $expectedCommissions[$username];

    $actualTotal += $difference;
    $expectedTotal += $expected;

    $isCorrect = abs($difference - $expected) < 0.01; // Float comparison tolerance
    $mark = $isCorrect ? '✅' : '❌';

    if (!$isCorrect) {
        $allCorrect = false;
    }

    printf("%s %-13s | ₱%-14.2f | ₱%-14.2f | ₱%-14.2f | ₱%-14.2f\n",
        $mark,
        $username,
        $initialMLM,
        $finalMLM,
        $difference,
        $expected
    );
}
echo str_repeat("-", 90) . "\n";
printf("%-15s | %-15s | %-15s | ₱%-14.2f | ₱%-14.2f\n",
    "TOTALS", "", "", $actualTotal, $expectedTotal);
echo str_repeat("-", 90) . "\n\n";

// Step 10: Verify transactions
echo "Step 10: Verifying MLM commission transactions...\n";

$transactions = Transaction::where('type', 'mlm_commission')
    ->where('source_order_id', $order->id)
    ->orderBy('level')
    ->get();

echo "Found {$transactions->count()} MLM commission transactions\n\n";

if ($transactions->count() > 0) {
    echo str_repeat("-", 90) . "\n";
    printf("%-10s | %-15s | %-12s | %-15s | %-20s\n",
        "Level", "Username", "Amount", "Status", "Source Order");
    echo str_repeat("-", 90) . "\n";

    foreach ($transactions as $txn) {
        $user = User::find($txn->user_id);
        printf("%-10s | %-15s | ₱%-11.2f | %-15s | %-20s\n",
            $txn->level,
            $user ? $user->username : 'N/A',
            $txn->amount,
            $txn->status,
            $txn->source_order_id
        );
    }
    echo str_repeat("-", 90) . "\n\n";
}

// Step 11: Final validation
echo "\n" . str_repeat("=", 70) . "\n";
echo "FINAL VALIDATION\n";
echo str_repeat("=", 70) . "\n";

$checks = [
    'All commissions correct' => $allCorrect,
    'Total distributed = ₱400' => abs($actualTotal - 400.00) < 0.01,
    'Transaction count = 5' => $transactions->count() === 5,
    'All transactions completed' => $transactions->every(fn($t) => $t->status === 'completed'),
    'Company profit = ₱600' => abs(($package->price - $actualTotal) - 600.00) < 0.01,
];

$passedChecks = 0;
foreach ($checks as $checkName => $passed) {
    $mark = $passed ? '✅' : '❌';
    echo "{$mark} {$checkName}\n";
    if ($passed) $passedChecks++;
}

echo str_repeat("=", 70) . "\n";
echo "RESULT: {$passedChecks}/" . count($checks) . " checks passed\n";
echo str_repeat("=", 70) . "\n\n";

if ($passedChecks === count($checks)) {
    echo "✅ TEST CASE 12.1: PASSED\n";
    exit(0);
} else {
    echo "❌ TEST CASE 12.1: FAILED\n";
    exit(1);
}
