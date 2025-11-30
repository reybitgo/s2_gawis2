<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking activity_logs type values for MLM commissions...\n";
echo str_repeat("=", 80) . "\n\n";

// Get distinct types with 'mlm' in them
$types = DB::table('activity_logs')
    ->select('type')
    ->distinct()
    ->where('type', 'LIKE', '%mlm%')
    ->get();

echo "Distinct MLM-related types found:\n";
foreach ($types as $type) {
    $count = DB::table('activity_logs')->where('type', $type->type)->count();
    echo "- Type: '{$type->type}' (Count: {$count})\n";
}

echo "\n" . str_repeat("=", 80) . "\n";

// Get a sample MLM commission log
$sample = DB::table('activity_logs')
    ->where('type', 'LIKE', '%mlm%')
    ->orderBy('created_at', 'desc')
    ->first();

if ($sample) {
    echo "Sample MLM Activity Log:\n";
    echo "- ID: {$sample->id}\n";
    echo "- Type: '{$sample->type}'\n";
    echo "- Level: '{$sample->level}'\n";
    echo "- Event: '{$sample->event}'\n";
    echo "- Message: {$sample->message}\n";
    echo "- Created: {$sample->created_at}\n";
} else {
    echo "No MLM logs found\n";
}

echo "\n" . str_repeat("=", 80) . "\n";

// Check what the correct WHERE clause should be
echo "Testing queries:\n\n";

$query1 = DB::table('activity_logs')->where('type', 'mlm')->count();
echo "WHERE type = 'mlm': {$query1} records\n";

$query2 = DB::table('activity_logs')->where('type', 'mlm_commission')->count();
echo "WHERE type = 'mlm_commission': {$query2} records\n";

$query3 = DB::table('activity_logs')->where('type', 'LIKE', '%mlm%')->count();
echo "WHERE type LIKE '%mlm%': {$query3} records\n";

echo "\n✓ Correct type value identified!\n";
