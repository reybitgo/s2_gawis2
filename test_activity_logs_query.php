<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing fixed activity_logs query...\n\n";

try {
    $results = DB::select("
        SELECT 
            al.user_id,
            u.username,
            al.type,
            al.message,
            al.created_at
        FROM activity_logs al
        JOIN users u ON al.user_id = u.id
        WHERE al.type = 'mlm'
        ORDER BY al.created_at DESC
        LIMIT 5
    ");
    
    echo "✓ Query executed successfully!\n";
    echo "Found " . count($results) . " MLM activity logs\n\n";
    
    if (count($results) > 0) {
        echo "Sample results:\n";
        echo str_repeat("-", 80) . "\n";
        foreach ($results as $log) {
            echo "User: " . $log->username . " | Type: " . $log->type . "\n";
            echo "Message: " . $log->message . "\n";
            echo "Date: " . $log->created_at . "\n";
            echo str_repeat("-", 80) . "\n";
        }
    } else {
        echo "No MLM activity logs found yet (this is normal if no MLM commissions processed)\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Query failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✓ All query fixes working correctly!\n";
