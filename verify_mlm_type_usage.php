<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Verifying 'mlm' type usage in activity_logs...\n";
echo str_repeat("=", 80) . "\n\n";

// Check different events under 'mlm' type
$events = DB::table('activity_logs')
    ->select('event', DB::raw('COUNT(*) as count'))
    ->where('type', 'mlm')
    ->groupBy('event')
    ->orderBy('count', 'desc')
    ->get();

echo "Events under type='mlm':\n";
foreach ($events as $event) {
    echo "- {$event->event}: {$event->count} records\n";
}

echo "\n" . str_repeat("=", 80) . "\n";

// Get sample commission records
echo "Sample MLM commission records (type='mlm'):\n\n";
$commissions = DB::table('activity_logs')
    ->where('type', 'mlm')
    ->where('event', 'commission_earned')
    ->orderBy('created_at', 'desc')
    ->limit(3)
    ->get(['user_id', 'event', 'message', 'created_at']);

foreach ($commissions as $comm) {
    echo "User ID: {$comm->user_id}\n";
    echo "Event: {$comm->event}\n";
    echo "Message: {$comm->message}\n";
    echo "Date: {$comm->created_at}\n";
    echo str_repeat("-", 80) . "\n";
}

echo "\n✓ Confirmed: Use type='mlm' for all MLM-related activity logs\n";
echo "✓ This includes: sponsorship, commissions, and other MLM events\n";
