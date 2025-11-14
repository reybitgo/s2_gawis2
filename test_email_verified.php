<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Order;
use App\Models\Package;
use App\Jobs\ProcessMLMCommissions;
use App\Notifications\MLMCommissionEarned;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

echo "=== Test Case 10.2: Email to Verified Addresses ===\n\n";

// Step 1: Verify which test users have verified emails
echo "Step 1: Checking email verification status...\n";

$testUsernames = ['admin', 'member', 'member2', 'member3', 'member4'];
$verifiedUsers = [];
$unverifiedUsers = [];

echo str_repeat("-", 70) . "\n";
printf("%-15s | %-10s | %-20s | %-15s\n", "Username", "User ID", "Email", "Verified");
echo str_repeat("-", 70) . "\n";

foreach ($testUsernames as $username) {
    $user = User::where('username', $username)->first();
    if ($user) {
        $isVerified = $user->hasVerifiedEmail();
        printf("%-15s | %-10s | %-20s | %-15s\n",
            $username,
            $user->id,
            $user->email,
            $isVerified ? 'Yes ✅' : 'No ❌'
        );

        if ($isVerified) {
            $verifiedUsers[] = $user;
        } else {
            $unverifiedUsers[] = $user;
        }
    }
}
echo str_repeat("-", 70) . "\n\n";

echo "Verified users: " . count($verifiedUsers) . "\n";
echo "Unverified users: " . count($unverifiedUsers) . "\n\n";

// Step 2: Use Notification::fake() to intercept notifications
echo "Step 2: Setting up notification interception...\n";
Notification::fake();
echo "✅ Notification fake enabled\n\n";

// Step 3: Get test data
echo "Step 3: Loading test data...\n";
$buyer = User::where('username', 'member5')->first();
$package = Package::where('is_mlm_package', true)->first();

if (!$buyer || !$package) {
    echo "❌ ERROR: Test data not found\n";
    exit(1);
}

echo "✅ Test data loaded\n";
echo "   Buyer: {$buyer->username} (ID: {$buyer->id})\n";
echo "   Package: {$package->name} (₱{$package->price})\n\n";

// Step 4: Create test order
echo "Step 4: Creating test order...\n";

DB::beginTransaction();

try {
    $order = Order::create([
        'user_id' => $buyer->id,
        'order_number' => 'EMAIL-TEST-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
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

// Step 5: Process MLM commissions
echo "Step 5: Processing MLM commissions...\n";

try {
    $order->load('orderItems.package');
    ProcessMLMCommissions::dispatchSync($order);
    echo "✅ Commission processing completed\n\n";

} catch (\Exception $e) {
    echo "❌ ERROR: Commission processing failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 6: Verify notifications were sent (which would trigger emails)
echo "Step 6: Verifying notifications and emails...\n";

$totalEmailsSent = 0;
$emailsSentToVerified = 0;
$emailsSentToUnverified = 0;
$notificationsSent = [];

foreach ($testUsernames as $username) {
    $user = User::where('username', $username)->first();
    if (!$user) continue;

    // Check if notification was sent to this user
    try {
        Notification::assertSentTo(
            $user,
            MLMCommissionEarned::class,
            function ($notification, $channels) use ($user, &$totalEmailsSent, &$emailsSentToVerified, &$emailsSentToUnverified, &$notificationsSent) {
                // Check if mail channel is in the channels
                if (in_array('mail', $channels)) {
                    $totalEmailsSent++;
                    $notificationsSent[$user->username] = ['notification' => true, 'mail' => true];

                    if ($user->hasVerifiedEmail()) {
                        $emailsSentToVerified++;
                    } else {
                        $emailsSentToUnverified++;
                    }
                } else {
                    $notificationsSent[$user->username] = ['notification' => true, 'mail' => false];
                }
                return true;
            }
        );
    } catch (\Exception $e) {
        // Notification not sent to this user
        $notificationsSent[$user->username] = ['notification' => false, 'mail' => false];
    }
}

echo "Total notifications with mail channel: {$totalEmailsSent}\n";
echo "Emails to verified users: {$emailsSentToVerified}\n";
echo "Emails to unverified users: {$emailsSentToUnverified}\n\n";

// Step 7: Detailed email verification
echo "Step 7: Detailed email verification...\n";
echo str_repeat("-", 90) . "\n";
printf("%-15s | %-20s | %-15s | %-20s\n", "Username", "Email", "Verified", "Email Sent");
echo str_repeat("-", 90) . "\n";

foreach ($testUsernames as $username) {
    $user = User::where('username', $username)->first();
    if (!$user) continue;

    $emailSent = isset($notificationsSent[$username]) && $notificationsSent[$username]['mail'];
    $verified = $user->hasVerifiedEmail();
    $shouldHaveSent = $verified;
    $correct = ($emailSent === $shouldHaveSent);

    printf("%s %-13s | %-20s | %-15s | %-20s\n",
        $correct ? '✅' : '❌',
        $username,
        $user->email,
        $verified ? 'Yes' : 'No',
        $emailSent ? 'Yes' : 'No'
    );
}
echo str_repeat("-", 90) . "\n\n";

// Step 8: Final validation
echo "\n" . str_repeat("=", 70) . "\n";
echo "FINAL VALIDATION\n";
echo str_repeat("=", 70) . "\n";

$checks = [
    'Emails sent to verified users' => $emailsSentToVerified > 0,
    'No emails to unverified users' => $emailsSentToUnverified === 0,
    'Email count matches verified count' => $emailsSentToVerified === count($verifiedUsers),
    'At least 1 verified user exists' => count($verifiedUsers) > 0,
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
    echo "✅ TEST CASE 10.2: PASSED\n";
    exit(0);
} else {
    echo "❌ TEST CASE 10.2: FAILED\n";
    exit(1);
}
