<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\Package;

echo "=== Checking Order Details ===\n\n";

$order = Order::where('order_number', 'ORD-20251206-C52959A5')->first();

if (!$order) {
    echo "❌ Order not found!\n";
    exit(1);
}

echo "📦 ORDER DETAILS:\n";
echo "=================\n";
echo "Order Number: {$order->order_number}\n";
echo "User ID: {$order->user_id}\n";
echo "Status: {$order->status}\n";
echo "Payment Status: {$order->payment_status}\n";
echo "Payment Method: {$order->payment_method}\n";
echo "Subtotal: ₱" . number_format($order->subtotal ?? 0, 2) . "\n";
echo "Grand Total: ₱" . number_format($order->grand_total ?? 0, 2) . "\n";
echo "Notes: " . ($order->notes ?? 'None') . "\n";
echo "Created At: {$order->created_at}\n\n";

echo "📦 ORDER ITEMS:\n";
echo "===============\n";
foreach ($order->orderItems as $item) {
    echo "Item ID: {$item->id}\n";
    echo "  Item Type: {$item->item_type}\n";
    echo "  Package ID: " . ($item->package_id ?? 'N/A') . "\n";
    if ($item->package) {
        echo "  Package Name: {$item->package->name}\n";
        echo "  Package Price (DB): ₱" . number_format($item->package->price, 2) . "\n";
        echo "  Is MLM: " . ($item->package->is_mlm_package ? 'YES' : 'NO') . "\n";
    }
    echo "  Quantity: {$item->quantity}\n";
    echo "  Price (in order_items): ₱" . number_format($item->price ?? 0, 2) . "\n";
    echo "  Subtotal (in order_items): ₱" . number_format($item->subtotal ?? 0, 2) . "\n\n";
}

echo "🔍 PACKAGE CHECK:\n";
echo "=================\n";
$starterPackage = Package::find(1);
if ($starterPackage) {
    echo "Starter Package (ID: 1):\n";
    echo "  Name: {$starterPackage->name}\n";
    echo "  Price: ₱" . number_format($starterPackage->price, 2) . "\n";
    echo "  Is MLM: " . ($starterPackage->is_mlm_package ? 'YES' : 'NO') . "\n";
    echo "  MLM Settings Exist: " . ($starterPackage->mlmSettings()->exists() ? 'YES' : 'NO') . "\n";
}

echo "\n⚠️  DIAGNOSIS:\n";
echo "=============\n";
if ($order->grand_total == 0 || $order->subtotal == 0) {
    echo "❌ ORDER PRICE IS ZERO!\n";
    echo "This is why no commission was processed.\n";
    echo "Possible reasons:\n";
    echo "  1. Order was created with payment_method = 'system_reward' (rank advancement)\n";
    echo "  2. Order was created manually with wrong prices\n";
    echo "  3. Bug in order creation process\n";
    echo "  4. Package price was 0 when order was created\n\n";
    
    if ($order->payment_method === 'system_reward' || $order->payment_method === 'admin_adjustment') {
        echo "✅ This is a SYSTEM-FUNDED order (rank advancement or admin adjustment)\n";
        echo "   These orders do NOT trigger upline commissions - this is correct behavior!\n";
    } else {
        echo "❌ This should have been a regular paid order.\n";
        echo "   Something went wrong during order creation.\n";
    }
}
