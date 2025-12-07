<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

echo "=== Checking Commission Status for Alice & Bob ===\n\n";

// Find users
$alice = User::where('username', 'alice_distributor')->first();
$bob = User::where('username', 'bob_member')->first();

if (!$alice || !$bob) {
    echo "❌ Users not found!\n";
    echo "Alice found: " . ($alice ? 'YES' : 'NO') . "\n";
    echo "Bob found: " . ($bob ? 'YES' : 'NO') . "\n";
    exit(1);
}

echo "✅ Users Found:\n";
echo "Alice ID: {$alice->id}\n";
echo "Bob ID: {$bob->id}\n";
echo "Bob's Sponsor ID: {$bob->sponsor_id}\n\n";

// Check Bob's orders
echo "📦 BOB'S ORDERS:\n";
echo "================\n";
$bobOrders = Order::where('user_id', $bob->id)
    ->with('orderItems.package')
    ->orderBy('created_at', 'desc')
    ->get();

if ($bobOrders->isEmpty()) {
    echo "❌ No orders found for Bob!\n\n";
} else {
    foreach ($bobOrders as $order) {
        echo "Order #{$order->order_number}\n";
        echo "  Status: {$order->status}\n";
        echo "  Payment Status: {$order->payment_status}\n";
        echo "  Total: ₱" . number_format($order->grand_total, 2) . "\n";
        echo "  Created: {$order->created_at}\n";
        echo "  Items:\n";
        foreach ($order->orderItems as $item) {
            if ($item->package) {
                echo "    - {$item->package->name} (₱" . number_format($item->price, 2) . ")\n";
                echo "      Is MLM Package: " . ($item->package->is_mlm_package ? 'YES' : 'NO') . "\n";
            }
        }
        echo "\n";
    }
}

// Check MLM Transactions from transactions table
echo "💰 MLM COMMISSIONS (From Transactions Table):\n";
echo "==============================================\n";
$mlmTransactions = Transaction::where('user_id', $alice->id)
    ->where('type', 'mlm_commission')
    ->orderBy('created_at', 'desc')
    ->get();

if ($mlmTransactions->isEmpty()) {
    echo "❌ No MLM commission transactions found for Alice!\n\n";
} else {
    foreach ($mlmTransactions as $txn) {
        echo "Transaction ID: {$txn->id}\n";
        echo "  Amount: ₱" . number_format($txn->amount, 2) . "\n";
        echo "  Status: {$txn->status}\n";
        echo "  Description: {$txn->description}\n";
        echo "  MLM Level: " . ($txn->mlm_level ?? 'N/A') . "\n";
        echo "  From User: " . ($txn->from_user_id ?? 'N/A') . "\n";
        echo "  Order ID: " . ($txn->related_order_id ?? 'N/A') . "\n";
        echo "  Created: {$txn->created_at}\n\n";
    }
}

// Check ALL transactions for Alice
echo "💳 ALL TRANSACTIONS (Alice's Wallet):\n";
echo "=====================================\n";
$allTransactions = Transaction::where('user_id', $alice->id)
    ->orderBy('created_at', 'desc')
    ->get();

if ($allTransactions->isEmpty()) {
    echo "❌ No transactions found for Alice!\n\n";
} else {
    foreach ($allTransactions as $txn) {
        echo "Transaction ID: {$txn->id}\n";
        echo "  Type: {$txn->type}\n";
        echo "  Amount: ₱" . number_format($txn->amount, 2) . "\n";
        echo "  Status: {$txn->status}\n";
        echo "  Description: {$txn->description}\n";
        echo "  Reference: " . ($txn->reference_number ?? 'N/A') . "\n";
        echo "  Created: {$txn->created_at}\n\n";
    }
}

// Check Alice's wallet balance
echo "👛 ALICE'S WALLET:\n";
echo "=================\n";
$aliceWallet = Wallet::where('user_id', $alice->id)->first();
if ($aliceWallet) {
    echo "Balance: ₱" . number_format($aliceWallet->balance, 2) . "\n";
    echo "Total Earned: ₱" . number_format($aliceWallet->total_earned, 2) . "\n";
    echo "Total Withdrawn: ₱" . number_format($aliceWallet->total_withdrawn, 2) . "\n\n";
} else {
    echo "❌ No wallet found for Alice!\n\n";
}

// Check Bob's wallet balance
echo "👛 BOB'S WALLET:\n";
echo "===============\n";
$bobWallet = Wallet::where('user_id', $bob->id)->first();
if ($bobWallet) {
    echo "Balance: ₱" . number_format($bobWallet->balance, 2) . "\n";
    echo "Total Earned: ₱" . number_format($bobWallet->total_earned, 2) . "\n";
    echo "Total Withdrawn: ₱" . number_format($bobWallet->total_withdrawn, 2) . "\n\n";
} else {
    echo "❌ No wallet found for Bob!\n\n";
}

// Check if commission processing was triggered
echo "🔍 COMMISSION PROCESSING CHECK:\n";
echo "================================\n";
$latestOrder = $bobOrders->first();
if ($latestOrder) {
    echo "Latest Order: #{$latestOrder->order_number}\n";
    echo "Status: {$latestOrder->status}\n";
    echo "Payment Status: {$latestOrder->payment_status}\n";
    
    // Check if order has MLM packages
    $hasMLMPackage = false;
    foreach ($latestOrder->orderItems as $item) {
        if ($item->package && $item->package->is_mlm_package) {
            $hasMLMPackage = true;
            break;
        }
    }
    
    echo "Has MLM Package: " . ($hasMLMPackage ? 'YES' : 'NO') . "\n";
    echo "Should Process Commission: " . ($latestOrder->payment_status === 'paid' && $hasMLMPackage ? 'YES' : 'NO') . "\n\n";
    
    if ($latestOrder->payment_status === 'paid' && $hasMLMPackage && $mlmTransactions->isEmpty()) {
        echo "⚠️  WARNING: Order is paid with MLM package but NO commissions found!\n";
        echo "This indicates a commission processing failure.\n\n";
    }
}

echo "✅ Database check complete!\n";
