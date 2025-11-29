<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Order;
use App\Models\Package;

echo "=== Checking Existing Data ===\n\n";

// Check users
$totalUsers = User::count();
echo "1. Total Users: {$totalUsers}\n";

// Check orders
$totalOrders = Order::count();
$paidOrders = Order::where('payment_status', 'paid')->count();
echo "2. Total Orders: {$totalOrders}\n";
echo "   - Paid orders: {$paidOrders}\n";

// Check packages
$totalPackages = Package::count();
$mlmPackages = Package::where('is_mlm_package', true)->count();
$rankablePackages = Package::where('is_mlm_package', true)
    ->whereNotNull('rank_name')
    ->count();
echo "3. Total Packages: {$totalPackages}\n";
echo "   - MLM packages: {$mlmPackages}\n";
echo "   - Rankable packages: {$rankablePackages}\n\n";

// If there are paid orders, show details
if ($paidOrders > 0) {
    echo "Paid Orders Details:\n";
    echo str_repeat("-", 70) . "\n";
    
    $orders = Order::where('payment_status', 'paid')
        ->with(['user', 'orderItems.package'])
        ->get();
    
    foreach ($orders as $order) {
        echo "Order #{$order->order_number}\n";
        echo "  User: " . ($order->user ? $order->user->username : 'NULL') . "\n";
        echo "  Payment: {$order->payment_status}\n";
        echo "  Items: {$order->orderItems->count()}\n";
        
        foreach ($order->orderItems as $item) {
            if ($item->package) {
                echo "    - Package: {$item->package->name}\n";
                echo "      is_mlm_package: " . ($item->package->is_mlm_package ? 'true' : 'false') . "\n";
                echo "      rank_name: " . ($item->package->rank_name ?? 'NULL') . "\n";
            } else {
                echo "    - Product (not a package)\n";
            }
        }
        echo "\n";
    }
}

// Recommendation
echo str_repeat("=", 70) . "\n";
echo "RECOMMENDATION:\n\n";

if ($totalUsers == 0) {
    echo "✅ You have a clean database - perfect for deployment!\n";
    echo "   The migration will work when you have real users with purchases.\n";
} elseif ($paidOrders == 0) {
    echo "⚠️  You have users but no paid orders.\n";
    echo "   Options:\n";
    echo "   1. Create test orders to verify migration works\n";
    echo "   2. Deploy as-is (migration will work when real purchases happen)\n";
} else {
    echo "⚠️  You have paid orders but migration didn't assign ranks.\n";
    echo "   Check the order details above to see why.\n";
    echo "   Common issues:\n";
    echo "   - Orders contain products, not packages\n";
    echo "   - Packages have is_mlm_package = false\n";
    echo "   - Packages have no rank_name\n";
}
