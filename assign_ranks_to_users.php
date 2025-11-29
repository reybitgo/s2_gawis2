<?php

/**
 * Standalone script to assign ranks to users based on their purchased packages
 * This can be run repeatedly for new users without needing to run migrations
 * 
 * Usage: php assign_ranks_to_users.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "===========================================\n";
echo "Assigning ranks to users based on packages\n";
echo "===========================================\n\n";

$totalUpdated = 0;
$totalSkipped = 0;
$startTime = microtime(true);

try {
    // Get all users who have purchased packages but don't have ranks yet or need update
    User::whereHas('orders', function($query) {
        $query->where('payment_status', 'paid')
              ->whereHas('orderItems.package');
    })->chunk(100, function($users) use (&$totalUpdated, &$totalSkipped) {
        foreach ($users as $user) {
            // Get highest-priced package purchased by user
            $highestPackage = Package::whereHas('orderItems.order', function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('payment_status', 'paid');
            })
            ->where('is_mlm_package', true)
            ->orderBy('price', 'desc')
            ->first();
            
            if ($highestPackage && $highestPackage->rank_name) {
                // Check if rank needs to be updated
                $needsUpdate = !$user->current_rank || 
                               $user->current_rank !== $highestPackage->rank_name ||
                               $user->rank_package_id !== $highestPackage->id;
                
                if ($needsUpdate) {
                    // Assign rank based on highest package
                    $user->update([
                        'current_rank' => $highestPackage->rank_name,
                        'rank_package_id' => $highestPackage->id,
                        'rank_updated_at' => now(),
                    ]);
                    
                    $totalUpdated++;
                    
                    echo "✓ Updated: {$user->username} -> {$highestPackage->rank_name} (Package: {$highestPackage->name})\n";
                    
                    if ($totalUpdated % 50 === 0) {
                        echo "\n--- Progress: {$totalUpdated} users updated ---\n\n";
                    }
                    
                    Log::info('Rank assigned to user', [
                        'user_id' => $user->id,
                        'username' => $user->username,
                        'rank' => $highestPackage->rank_name,
                        'package' => $highestPackage->name,
                    ]);
                } else {
                    $totalSkipped++;
                }
            } else {
                $totalSkipped++;
                echo "✗ Skipped: {$user->username} (No MLM package with rank found)\n";
            }
        }
    });
    
    $endTime = microtime(true);
    $executionTime = round($endTime - $startTime, 2);
    
    echo "\n===========================================\n";
    echo "Rank assignment completed!\n";
    echo "===========================================\n";
    echo "Total users updated: {$totalUpdated}\n";
    echo "Total users skipped: {$totalSkipped}\n";
    echo "Execution time: {$executionTime} seconds\n";
    echo "===========================================\n";
    
    Log::info('User rank assignment script completed', [
        'updated' => $totalUpdated,
        'skipped' => $totalSkipped,
        'execution_time' => $executionTime,
    ]);
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    Log::error('Rank assignment script failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    
    exit(1);
}
