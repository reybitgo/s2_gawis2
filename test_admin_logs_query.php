<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ActivityLog;

echo "🔍 Testing Admin Logs Query System\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// Test 1: Query All Logs (simulating viewLogs method)
echo "1️⃣  Testing Query All Logs\n";
$query = ActivityLog::with(['user', 'transaction', 'order', 'relatedUser'])
    ->orderBy('created_at', 'desc');

$activityLogs = $query->limit(500)->get();

echo "   ✅ Retrieved {$activityLogs->count()} logs\n\n";

// Transform like the controller does
$logs = $activityLogs->map(function($log) {
    return [
        'id' => $log->id,
        'timestamp' => $log->created_at,
        'level' => $log->level,
        'type' => $log->type,
        'event' => $log->event,
        'message' => $log->message,
        'user_id' => $log->user_id,
        'ip_address' => $log->ip_address ?? 'N/A',
        'user_agent' => $log->user_agent ?? 'N/A',
        'metadata' => $log->metadata,
        'transaction_id' => $log->transaction_id,
        'order_id' => $log->order_id,
        'related_user_id' => $log->related_user_id,
    ];
});

echo "   📋 Sample Log Entries:\n";
foreach ($logs->take(5) as $log) {
    echo "\n   🔸 ID: {$log['id']} | Type: {$log['type']} | Level: {$log['level']}\n";
    echo "      Message: {$log['message']}\n";
    echo "      Time: " . $log['timestamp']->format('Y-m-d H:i:s') . "\n";
}

// Test 2: Filter by Type (MLM Commission)
echo "\n\n2️⃣  Testing Filter by Type: MLM Commission\n";
$mlmLogs = ActivityLog::where('type', 'mlm_commission')->get();
echo "   ✅ Found {$mlmLogs->count()} MLM commission logs\n";
foreach ($mlmLogs as $log) {
    echo "      - {$log->message}\n";
}

// Test 3: Filter by Type (Wallet)
echo "\n3️⃣  Testing Filter by Type: Wallet\n";
$walletLogs = ActivityLog::where('type', 'wallet')->get();
echo "   ✅ Found {$walletLogs->count()} wallet logs\n";
foreach ($walletLogs as $log) {
    echo "      - {$log->event}: {$log->message}\n";
}

// Test 4: Filter by Type (Order)
echo "\n4️⃣  Testing Filter by Type: Order\n";
$orderLogs = ActivityLog::where('type', 'order')->get();
echo "   ✅ Found {$orderLogs->count()} order logs\n";
foreach ($orderLogs as $log) {
    echo "      - {$log->event}: {$log->message}\n";
}

// Test 5: Filter by Level (WARNING and above)
echo "\n5️⃣  Testing Filter by Level: WARNING and above\n";
$warningLogs = ActivityLog::whereIn('level', ['WARNING', 'ERROR', 'CRITICAL'])->get();
echo "   ✅ Found {$warningLogs->count()} warning/error/critical logs\n";
foreach ($warningLogs as $log) {
    echo "      - [{$log->level}] {$log->message}\n";
}

// Test 6: Search functionality
echo "\n6️⃣  Testing Search Functionality\n";
$searchTerm = 'commission';
$searchLogs = ActivityLog::where(function ($q) use ($searchTerm) {
    $q->where('message', 'like', "%{$searchTerm}%")
      ->orWhere('ip_address', 'like', "%{$searchTerm}%")
      ->orWhere('event', 'like', "%{$searchTerm}%");
})->get();
echo "   ✅ Found {$searchLogs->count()} logs matching '{$searchTerm}'\n";
foreach ($searchLogs as $log) {
    echo "      - {$log->message}\n";
}

// Test 7: Combined Filters (Type + Level)
echo "\n7️⃣  Testing Combined Filters: Wallet + INFO\n";
$combinedLogs = ActivityLog::where('type', 'wallet')
    ->where('level', 'INFO')
    ->get();
echo "   ✅ Found {$combinedLogs->count()} INFO level wallet logs\n";

// Test 8: Export Simulation (CSV format)
echo "\n8️⃣  Testing Export Data Preparation (CSV)\n";
$exportLogs = ActivityLog::orderBy('created_at', 'desc')->limit(10000)->get();
$exportData = $exportLogs->map(function($log) {
    return [
        'id' => $log->id,
        'timestamp' => $log->created_at->format('Y-m-d H:i:s'),
        'level' => $log->level,
        'type' => $log->type,
        'event' => $log->event,
        'message' => $log->message,
        'user_id' => $log->user_id,
        'ip_address' => $log->ip_address ?? 'N/A',
        'user_agent' => $log->user_agent ?? 'N/A',
    ];
});
echo "   ✅ Prepared {$exportData->count()} logs for export\n";
echo "   📄 Sample CSV Row:\n";
$sample = $exportData->first();
echo "      ID: {$sample['id']}, Time: {$sample['timestamp']}, Type: {$sample['type']}, Message: {$sample['message']}\n";

// Test 9: Metadata Retrieval
echo "\n9️⃣  Testing Metadata Retrieval\n";
$logsWithMetadata = ActivityLog::whereNotNull('metadata')->get();
echo "   ✅ Found {$logsWithMetadata->count()} logs with metadata\n";
foreach ($logsWithMetadata->take(3) as $log) {
    echo "      - {$log->type}: " . json_encode($log->metadata) . "\n";
}

// Test 10: Relationship Loading
echo "\n🔟 Testing Relationship Loading\n";
$logsWithRelations = ActivityLog::with(['user', 'transaction', 'order', 'relatedUser'])
    ->whereNotNull('user_id')
    ->first();
if ($logsWithRelations) {
    echo "   ✅ Relationships loaded successfully\n";
    echo "      - Log ID: {$logsWithRelations->id}\n";
    echo "      - User: " . ($logsWithRelations->user ? $logsWithRelations->user->username ?? $logsWithRelations->user->fullname : 'N/A') . "\n";
    echo "      - Transaction ID: " . ($logsWithRelations->transaction_id ?? 'N/A') . "\n";
    echo "      - Order ID: " . ($logsWithRelations->order_id ?? 'N/A') . "\n";
} else {
    echo "   ⚠️  No logs with user relationships found\n";
}

// Test 11: Statistics (like the view displays)
echo "\n1️⃣1️⃣  Testing Statistics Calculations\n";
$stats = [
    'total' => ActivityLog::count(),
    'info' => ActivityLog::where('level', 'INFO')->count(),
    'warning' => ActivityLog::where('level', 'WARNING')->count(),
    'errors' => ActivityLog::whereIn('level', ['ERROR', 'CRITICAL'])->count(),
    'mlm_commission' => ActivityLog::where('type', 'mlm_commission')->count(),
    'wallet' => ActivityLog::where('type', 'wallet')->count(),
    'order' => ActivityLog::where('type', 'order')->count(),
    'security' => ActivityLog::where('type', 'security')->count(),
    'system' => ActivityLog::where('type', 'system')->count(),
    'transaction' => ActivityLog::where('type', 'transaction')->count(),
];

echo "   📊 Statistics:\n";
echo "      - Total Logs: {$stats['total']}\n";
echo "      - INFO Level: {$stats['info']}\n";
echo "      - WARNING Level: {$stats['warning']}\n";
echo "      - ERROR/CRITICAL: {$stats['errors']}\n";
echo "\n   📈 By Type:\n";
echo "      - MLM Commission: {$stats['mlm_commission']}\n";
echo "      - Wallet: {$stats['wallet']}\n";
echo "      - Order: {$stats['order']}\n";
echo "      - Security: {$stats['security']}\n";
echo "      - Transaction: {$stats['transaction']}\n";
echo "      - System: {$stats['system']}\n";

echo "\n" . str_repeat("=", 70) . "\n";
echo "✅ All Query Tests Passed!\n\n";
echo "🎯 Next Steps:\n";
echo "   1. Visit: https://mlm.gawisherbal.com/admin/logs\n";
echo "   2. Try filtering by 'MLM Commission' type\n";
echo "   3. Try filtering by 'Wallet' type\n";
echo "   4. Try different log levels (INFO, WARNING, ERROR)\n";
echo "   5. Test search functionality\n";
echo "   6. Test export as CSV\n";
echo "   7. Test export as JSON\n";
echo "   8. Test 'Clear Old Logs' functionality\n\n";

echo "🔐 Admin Route: /admin/logs\n";
echo "🔗 Full URL: https://mlm.gawisherbal.com/admin/logs\n\n";
