<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

echo "🧪 Testing Activity Logging System\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Clear existing test logs
echo "🗑️  Clearing old test logs...\n";
ActivityLog::truncate();
echo "✅ Cleared\n\n";

// Get sample users, orders, transactions for realistic testing
$admin = User::role('admin')->first();
$member = User::role('member')->first();
$order = Order::first();
$transaction = Transaction::first();

echo "📝 Creating test activity logs...\n\n";

// Test 1: MLM Commission Logs
echo "1️⃣  Testing MLM Commission Logs\n";
try {
    $mlmLog = ActivityLog::create([
        'level' => 'INFO',
        'type' => 'mlm_commission',
        'event' => 'commission_earned',
        'message' => sprintf(
            '%s earned ₱500.00 Level 1 commission from %s\'s order #ORD-2025-10-09-0001',
            $member ? ($member->username ?? $member->fullname ?? 'TestUser') : 'TestUser',
            $admin ? ($admin->username ?? $admin->fullname ?? 'Buyer') : 'Buyer'
        ),
        'user_id' => $member ? $member->id : 1,
        'ip_address' => '192.168.1.100',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'order_id' => $order ? $order->id : null,
        'related_user_id' => $admin ? $admin->id : null,
        'metadata' => [
            'commission_amount' => 500.00,
            'commission_level' => 1,
            'buyer_id' => $admin ? $admin->id : 1,
            'buyer_name' => $admin ? ($admin->username ?? $admin->fullname) : 'Buyer',
            'package_name' => 'Premium Package'
        ]
    ]);
    echo "   ✅ MLM Commission log created (ID: {$mlmLog->id})\n";
} catch (\Exception $e) {
    echo "   ❌ Failed: " . $e->getMessage() . "\n";
}

// Test 2: Wallet Deposit Logs
echo "\n2️⃣  Testing Wallet Deposit Logs\n";
try {
    $depositLog = ActivityLog::create([
        'level' => 'INFO',
        'type' => 'wallet',
        'event' => 'deposit_requested',
        'message' => sprintf(
            '%s requested deposit of ₱1,000.00 via GCash',
            $member ? ($member->username ?? $member->fullname ?? 'TestUser') : 'TestUser'
        ),
        'user_id' => $member ? $member->id : 1,
        'ip_address' => '192.168.1.101',
        'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1)',
        'transaction_id' => $transaction ? $transaction->id : null,
        'metadata' => [
            'amount' => 1000.00,
            'payment_method' => 'gcash',
            'status' => 'pending'
        ]
    ]);
    echo "   ✅ Deposit request log created (ID: {$depositLog->id})\n";
} catch (\Exception $e) {
    echo "   ❌ Failed: " . $e->getMessage() . "\n";
}

// Test 3: Wallet Deposit Approved
echo "\n3️⃣  Testing Wallet Deposit Approval Logs\n";
try {
    $depositApprovedLog = ActivityLog::create([
        'level' => 'INFO',
        'type' => 'wallet',
        'event' => 'deposit_approved',
        'message' => sprintf(
            'Admin approved deposit of ₱1,000.00 for %s (Ref: TXN-2025-10-09-0001)',
            $member ? ($member->username ?? $member->fullname ?? 'TestUser') : 'TestUser'
        ),
        'user_id' => $admin ? $admin->id : 1,
        'ip_address' => '192.168.1.10',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'transaction_id' => $transaction ? $transaction->id : null,
        'metadata' => [
            'amount' => 1000.00,
            'approved_by' => $admin ? $admin->id : 1
        ]
    ]);
    echo "   ✅ Deposit approval log created (ID: {$depositApprovedLog->id})\n";
} catch (\Exception $e) {
    echo "   ❌ Failed: " . $e->getMessage() . "\n";
}

// Test 4: Wallet Withdrawal Logs
echo "\n4️⃣  Testing Wallet Withdrawal Logs\n";
try {
    $withdrawalLog = ActivityLog::create([
        'level' => 'INFO',
        'type' => 'wallet',
        'event' => 'withdrawal_requested',
        'message' => sprintf(
            '%s requested withdrawal of ₱500.00 via Bank Transfer',
            $member ? ($member->username ?? $member->fullname ?? 'TestUser') : 'TestUser'
        ),
        'user_id' => $member ? $member->id : 1,
        'ip_address' => '192.168.1.101',
        'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1)',
        'metadata' => [
            'amount' => 500.00,
            'payment_method' => 'bank_transfer',
            'status' => 'pending'
        ]
    ]);
    echo "   ✅ Withdrawal request log created (ID: {$withdrawalLog->id})\n";
} catch (\Exception $e) {
    echo "   ❌ Failed: " . $e->getMessage() . "\n";
}

// Test 5: Wallet Transfer Logs
echo "\n5️⃣  Testing Wallet Transfer Logs\n";
try {
    $transferLog = ActivityLog::create([
        'level' => 'INFO',
        'type' => 'wallet',
        'event' => 'transfer_sent',
        'message' => sprintf(
            '%s sent ₱200.00 to another user',
            $member ? ($member->username ?? $member->fullname ?? 'TestUser') : 'TestUser'
        ),
        'user_id' => $member ? $member->id : 1,
        'ip_address' => '192.168.1.101',
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
        'related_user_id' => $admin ? $admin->id : null,
        'metadata' => [
            'amount' => 200.00,
            'recipient_id' => $admin ? $admin->id : 2,
            'fee' => 0.00
        ]
    ]);
    echo "   ✅ Transfer sent log created (ID: {$transferLog->id})\n";
} catch (\Exception $e) {
    echo "   ❌ Failed: " . $e->getMessage() . "\n";
}

// Test 6: Order Payment Logs
echo "\n6️⃣  Testing Order Payment Logs\n";
try {
    $orderPaymentLog = ActivityLog::create([
        'level' => 'INFO',
        'type' => 'order',
        'event' => 'order_paid',
        'message' => sprintf(
            '%s paid ₱2,500.00 for order #ORD-2025-10-09-0002 via e-wallet',
            $member ? ($member->username ?? $member->fullname ?? 'TestUser') : 'TestUser'
        ),
        'user_id' => $member ? $member->id : 1,
        'ip_address' => '192.168.1.101',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'order_id' => $order ? $order->id : null,
        'metadata' => [
            'order_number' => 'ORD-2025-10-09-0002',
            'total_amount' => 2500.00,
            'payment_method' => 'wallet'
        ]
    ]);
    echo "   ✅ Order payment log created (ID: {$orderPaymentLog->id})\n";
} catch (\Exception $e) {
    echo "   ❌ Failed: " . $e->getMessage() . "\n";
}

// Test 7: Order Refund Logs
echo "\n7️⃣  Testing Order Refund Logs\n";
try {
    $refundLog = ActivityLog::create([
        'level' => 'INFO',
        'type' => 'order',
        'event' => 'order_refunded',
        'message' => sprintf(
            '%s received refund of ₱2,500.00 for cancelled order #ORD-2025-10-09-0003',
            $member ? ($member->username ?? $member->fullname ?? 'TestUser') : 'TestUser'
        ),
        'user_id' => $member ? $member->id : 1,
        'ip_address' => '192.168.1.101',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'order_id' => $order ? $order->id : null,
        'metadata' => [
            'order_number' => 'ORD-2025-10-09-0003',
            'refund_amount' => 2500.00,
            'refund_method' => 'wallet'
        ]
    ]);
    echo "   ✅ Order refund log created (ID: {$refundLog->id})\n";
} catch (\Exception $e) {
    echo "   ❌ Failed: " . $e->getMessage() . "\n";
}

// Test 8: Security Logs
echo "\n8️⃣  Testing Security Logs\n";
try {
    $securityLog = ActivityLog::create([
        'level' => 'WARNING',
        'type' => 'security',
        'event' => 'failed_login_attempt',
        'message' => 'Failed login attempt for user account',
        'user_id' => null,
        'ip_address' => '203.0.113.45',
        'user_agent' => 'curl/7.68.0',
        'metadata' => [
            'attempted_username' => 'testuser',
            'reason' => 'invalid_password'
        ]
    ]);
    echo "   ✅ Security log created (ID: {$securityLog->id})\n";
} catch (\Exception $e) {
    echo "   ❌ Failed: " . $e->getMessage() . "\n";
}

// Test 9: System Logs
echo "\n9️⃣  Testing System Logs\n";
try {
    $systemLog = ActivityLog::create([
        'level' => 'INFO',
        'type' => 'system',
        'event' => 'cache_cleared',
        'message' => 'System cache cleared successfully',
        'user_id' => $admin ? $admin->id : 1,
        'ip_address' => '192.168.1.10',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'metadata' => [
            'cache_type' => 'application',
            'cleared_by' => $admin ? $admin->id : 1
        ]
    ]);
    echo "   ✅ System log created (ID: {$systemLog->id})\n";
} catch (\Exception $e) {
    echo "   ❌ Failed: " . $e->getMessage() . "\n";
}

// Test 10: Transaction Logs
echo "\n🔟 Testing Transaction Logs\n";
try {
    $transactionLog = ActivityLog::create([
        'level' => 'INFO',
        'type' => 'transaction',
        'event' => 'transaction_completed',
        'message' => 'Transaction completed: ₱1,000.00 deposit',
        'user_id' => $member ? $member->id : 1,
        'ip_address' => '192.168.1.101',
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
        'transaction_id' => $transaction ? $transaction->id : null,
        'metadata' => [
            'amount' => 1000.00,
            'type' => 'deposit',
            'status' => 'completed'
        ]
    ]);
    echo "   ✅ Transaction log created (ID: {$transactionLog->id})\n";
} catch (\Exception $e) {
    echo "   ❌ Failed: " . $e->getMessage() . "\n";
}

// Test 11: Create logs with different severity levels
echo "\n🎨 Testing Different Severity Levels\n";
$levels = ['DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL'];
foreach ($levels as $level) {
    try {
        $log = ActivityLog::create([
            'level' => $level,
            'type' => 'system',
            'event' => 'severity_test',
            'message' => "Test {$level} level log message",
            'user_id' => $admin ? $admin->id : 1,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Script',
            'metadata' => ['test' => true, 'level' => $level]
        ]);
        echo "   ✅ {$level} level log created (ID: {$log->id})\n";
    } catch (\Exception $e) {
        echo "   ❌ {$level} Failed: " . $e->getMessage() . "\n";
    }
}

// Verify all logs were created
echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 Summary\n";
echo str_repeat("=", 50) . "\n\n";

$totalLogs = ActivityLog::count();
echo "✅ Total logs created: {$totalLogs}\n\n";

// Count by type
echo "📈 Logs by Type:\n";
$types = ['mlm_commission', 'wallet', 'order', 'security', 'transaction', 'system'];
foreach ($types as $type) {
    $count = ActivityLog::where('type', $type)->count();
    echo "   - " . str_pad(ucfirst(str_replace('_', ' ', $type)), 20) . ": {$count}\n";
}

// Count by level
echo "\n📊 Logs by Level:\n";
foreach ($levels as $level) {
    $count = ActivityLog::where('level', $level)->count();
    echo "   - " . str_pad($level, 10) . ": {$count}\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎉 Activity Logging System Test Complete!\n";
echo "\n👉 Visit: https://mlm.gawisherbal.com/admin/logs to view logs\n";
echo "👉 Try filtering by different types and levels\n";
echo "👉 Test export functionality (CSV/JSON)\n";
echo "\n";
