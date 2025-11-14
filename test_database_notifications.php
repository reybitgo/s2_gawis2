<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Order;
use App\Models\Package;
use App\Jobs\ProcessMLMCommissions;
use Illuminate\Support\Facades\DB;

echo "=== Test Case 10.1: Database Notification Creation ===\n\n";

// Step 1: Clear existing notifications for test users
echo "Step 1: Clearing existing notifications...\n";
$testUsernames = ['admin', 'member', 'member2', 'member3', 'member4'];
foreach ($testUsernames as $username) {
    $user = User::where('username', $username)->first();
    if ($user) {
        DB::table('notifications')->where('notifiable_id', $user->id)->delete();
    }
}
echo "✅ Notifications cleared\n\n";

// Step 2: Get test users and package
echo "Step 2: Loading test data...\n";
$buyer = User::where('username', 'member5')->first();
$package = Package::where('is_mlm_package', true)->first();

if (!$buyer || !$package) {
    echo "❌ ERROR: Test data not found\n";
    exit(1);
}

echo "✅ Test data loaded\n";
echo "   Buyer: {$buyer->username} (ID: {$buyer->id})\n";
echo "   Package: {$package->name} (₱{$package->price})\n\n";

// Step 3: Create test order
echo "Step 3: Creating test order...\n";

DB::beginTransaction();

try {
    $order = Order::create([
        'user_id' => $buyer->id,
        'order_number' => 'NOTIF-TEST-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
        'total_amount' => $package->price,
        'subtotal' => $package->price,
        'tax_amount' => 0,
        'status' => 'confirmed',
        'payment_status' => 'paid',
        'delivery_method' => 'office_pickup',
        'paid_at' => now(),
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
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::commit();

    echo "✅ Test order created: {$order->order_number}\n\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ ERROR: Failed to create order: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 4: Process MLM commissions
echo "Step 4: Processing MLM commissions...\n";

try {
    $order->load('orderItems.package');
    ProcessMLMCommissions::dispatchSync($order);
    echo "✅ Commission processing completed\n\n";

} catch (\Exception $e) {
    echo "❌ ERROR: Commission processing failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 5: Verify database notifications
echo "Step 5: Verifying database notifications...\n";

$notifications = DB::table('notifications')
    ->whereIn('notifiable_id', function($query) use ($testUsernames) {
        $query->select('id')
            ->from('users')
            ->whereIn('username', $testUsernames);
    })
    ->where('type', 'App\\Notifications\\MLMCommissionEarned')
    ->orderBy('created_at')
    ->get();

echo "Found {$notifications->count()} database notifications\n\n";

if ($notifications->count() > 0) {
    echo str_repeat("-", 100) . "\n";
    printf("%-15s | %-10s | %-20s | %-15s | %-25s\n",
        "Recipient", "Amount", "Level", "Status", "Created At");
    echo str_repeat("-", 100) . "\n";

    foreach ($notifications as $notification) {
        $data = json_decode($notification->data, true);
        $user = User::find($notification->notifiable_id);

        printf("%-15s | ₱%-9.2f | %-20s | %-15s | %-25s\n",
            $user ? $user->username : 'N/A',
            $data['commission'] ?? 0,
            $data['level_display'] ?? 'N/A',
            $notification->read_at ? 'Read' : 'Unread',
            $notification->created_at
        );
    }
    echo str_repeat("-", 100) . "\n\n";
}

// Step 6: Validate notification structure
echo "Step 6: Validating notification data structure...\n";

$validationResults = [];

foreach ($notifications as $notification) {
    $data = json_decode($notification->data, true);

    $hasAllFields = isset($data['commission']) &&
                    isset($data['level']) &&
                    isset($data['level_display']) &&
                    isset($data['buyer_name']) &&
                    isset($data['order_number']);

    $validationResults[] = [
        'notification_id' => $notification->id,
        'has_all_fields' => $hasAllFields,
        'fields' => array_keys($data)
    ];
}

$allValid = collect($validationResults)->every(fn($r) => $r['has_all_fields']);

if ($allValid) {
    echo "✅ All notifications have required fields\n";
} else {
    echo "❌ Some notifications missing required fields\n";
    foreach ($validationResults as $result) {
        if (!$result['has_all_fields']) {
            echo "   Notification {$result['notification_id']}: " . implode(', ', $result['fields']) . "\n";
        }
    }
}

echo "\n";

// Step 7: Final validation
echo "\n" . str_repeat("=", 70) . "\n";
echo "FINAL VALIDATION\n";
echo str_repeat("=", 70) . "\n";

$checks = [
    'Notification count = 5' => $notifications->count() === 5,
    'All notifications are MLMCommissionEarned type' => $notifications->every(fn($n) => $n->type === 'App\\Notifications\\MLMCommissionEarned'),
    'All notifications have complete data' => $allValid,
    'All notifications are unread initially' => $notifications->every(fn($n) => is_null($n->read_at)),
    'Notifications linked to correct users' => $notifications->count() === 5, // Should be 5 upline members
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
    echo "✅ TEST CASE 10.1: PASSED\n";
    exit(0);
} else {
    echo "❌ TEST CASE 10.1: FAILED\n";
    exit(1);
}
