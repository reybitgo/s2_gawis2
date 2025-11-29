<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "===========================================\n";
echo "CHECKING ORDERS DATA\n";
echo "===========================================\n\n";

// Check orders
$totalOrders = Order::count();
$paidOrders = Order::where('payment_status', 'paid')->count();
echo "Total Orders: {$totalOrders}\n";
echo "Paid Orders: {$paidOrders}\n\n";

// Get all payment statuses
echo "Payment Statuses in DB:\n";
$statuses = Order::select('payment_status')->distinct()->get();
foreach ($statuses as $status) {
    $count = Order::where('payment_status', $status->payment_status)->count();
    echo "  - {$status->payment_status}: {$count} orders\n";
}

echo "\n";

// Check order_items
$totalOrderItems = DB::table('order_items')->count();
echo "Total Order Items: {$totalOrderItems}\n\n";

// Check if there are any orders with package_id 4 (Starter)
$starterOrders = DB::table('order_items')->where('package_id', 4)->count();
echo "Order items with Starter Package (ID 4): {$starterOrders}\n\n";

// Sample orders
echo "Sample Orders (first 5):\n";
$sampleOrders = Order::with('orderItems.package', 'user')->limit(5)->get();
foreach ($sampleOrders as $order) {
    echo "  Order #{$order->id}:\n";
    echo "    - User: " . ($order->user ? $order->user->username : 'N/A') . "\n";
    echo "    - Payment Status: {$order->payment_status}\n";
    echo "    - Items: " . $order->orderItems->count() . "\n";
    foreach ($order->orderItems as $item) {
        if ($item->package) {
            echo "      * Package: {$item->package->name} (ID: {$item->package->id})\n";
        }
    }
    echo "\n";
}

echo "===========================================\n";
