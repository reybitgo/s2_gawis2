<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;
use App\Models\User;

echo "=== Cleaning Up Test Data ===\n\n";

// Find test orders (those with VERIFY- prefix or with 0 items)
$testOrders = Order::where('order_number', 'like', 'VERIFY-%')
    ->orWhere('order_number', 'like', 'TEST-%')
    ->orWhere('order_number', 'like', 'RANK-%')
    ->orWhere('order_number', 'like', 'AUTO-%')
    ->get();

echo "Found {$testOrders->count()} test orders to clean up:\n";

foreach ($testOrders as $order) {
    $itemCount = $order->orderItems()->count();
    echo "  - Order #{$order->order_number} (items: {$itemCount})\n";
}

if ($testOrders->count() > 0) {
    echo "\nDeleting test orders...\n";
    
    foreach ($testOrders as $order) {
        $order->orderItems()->delete(); // Delete items first
        $order->delete(); // Then delete order
    }
    
    echo "✅ Deleted {$testOrders->count()} test orders\n\n";
} else {
    echo "\n✅ No test orders to clean up\n\n";
}

// Find test users
$testUsers = User::where('username', 'like', 'ranktest%')
    ->orWhere('email', 'like', 'ranktest%')
    ->get();

echo "Found {$testUsers->count()} test users to clean up:\n";

foreach ($testUsers as $user) {
    echo "  - {$user->username} ({$user->email})\n";
}

if ($testUsers->count() > 0) {
    echo "\nDeleting test users...\n";
    
    foreach ($testUsers as $user) {
        $user->delete();
    }
    
    echo "✅ Deleted {$testUsers->count()} test users\n\n";
} else {
    echo "\n✅ No test users to clean up\n\n";
}

// Summary
echo str_repeat("=", 70) . "\n";
echo "CLEANUP COMPLETE!\n\n";

$remainingUsers = User::count();
$remainingOrders = Order::count();

echo "Remaining data:\n";
echo "  Users: {$remainingUsers}\n";
echo "  Orders: {$remainingOrders}\n\n";

if ($remainingUsers == 0 && $remainingOrders == 0) {
    echo "✅ Database is clean - ready for deployment!\n";
    echo "   The rank assignment migration is working correctly.\n";
    echo "   It will assign ranks when real users purchase packages.\n";
} else {
    echo "ℹ️  You have real user data.\n";
    echo "   Run: php test_rank_assignment_migration.php\n";
    echo "   To verify migration will work with this data.\n";
}
