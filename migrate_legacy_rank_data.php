<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Models\Order;
use App\Models\RankAdvancement;
use Illuminate\Support\Facades\DB;

echo "=== Migrating Legacy Rank Data for Phase 4 ===\n\n";

// Find all users who purchased rank packages but don't have rank data set
echo "Step 1: Finding legacy users with purchased packages...\n";

$legacyUsers = User::whereNull('current_rank')
    ->whereHas('orders', function($q) {
        $q->where('payment_status', 'paid')
          ->whereHas('orderItems.package', function($q2) {
              $q2->where('is_rankable', true);
          });
    })
    ->get();

echo "Found {$legacyUsers->count()} legacy users without rank data\n\n";

if ($legacyUsers->isEmpty()) {
    echo "✓ No legacy users to migrate. All users are up to date!\n";
    exit(0);
}

// Process each legacy user
$updated = 0;
$skipped = 0;
$errors = 0;

foreach ($legacyUsers as $user) {
    echo "Processing User ID: {$user->id} ({$user->username})...\n";
    
    try {
        DB::beginTransaction();
        
        // Get user's highest-priced rank package purchased
        $highestPackage = Package::whereHas('orderItems.order', function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->where('payment_status', 'paid');
        })
        ->where('is_rankable', true)
        ->orderBy('price', 'desc')
        ->first();
        
        if (!$highestPackage) {
            echo "  ⚠ No rank package found - skipping\n";
            $skipped++;
            DB::rollBack();
            continue;
        }
        
        // Get the earliest paid order with this package to set rank_updated_at
        $firstOrder = Order::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->whereHas('orderItems.package', function($q) use ($highestPackage) {
                $q->where('package_id', $highestPackage->id);
            })
            ->orderBy('created_at', 'asc')
            ->first();
        
        $rankUpdatedAt = $firstOrder ? $firstOrder->created_at : now();
        
        // Update user's rank information
        $user->update([
            'current_rank' => $highestPackage->rank_name,
            'rank_package_id' => $highestPackage->id,
            'rank_updated_at' => $rankUpdatedAt,
        ]);
        
        // Check if rank advancement history exists
        $hasHistory = RankAdvancement::where('user_id', $user->id)
            ->where('to_package_id', $highestPackage->id)
            ->exists();
        
        if (!$hasHistory) {
            // Create rank advancement history for legacy user
            RankAdvancement::create([
                'user_id' => $user->id,
                'from_rank' => null,
                'to_rank' => $highestPackage->rank_name,
                'from_package_id' => null,
                'to_package_id' => $highestPackage->id,
                'advancement_type' => 'purchase',
                'created_at' => $rankUpdatedAt,
                'updated_at' => $rankUpdatedAt,
            ]);
            echo "  ✓ Updated to {$highestPackage->rank_name} rank (with history created)\n";
        } else {
            echo "  ✓ Updated to {$highestPackage->rank_name} rank (history already exists)\n";
        }
        
        $updated++;
        DB::commit();
        
    } catch (\Exception $e) {
        DB::rollBack();
        echo "  ✗ Error: {$e->getMessage()}\n";
        $errors++;
    }
}

echo "\n=== Migration Complete ===\n\n";
echo "📊 Summary:\n";
echo "  ✓ Users updated: {$updated}\n";
echo "  ⚠ Users skipped: {$skipped}\n";
echo "  ✗ Errors: {$errors}\n\n";

// Show updated users
if ($updated > 0) {
    echo "📋 Updated Users:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "│ ID   │ Username              │ Rank          │\n";
    echo "├──────┼───────────────────────┼───────────────┤\n";
    
    $updatedUsers = User::whereNotNull('current_rank')
        ->whereIn('id', $legacyUsers->pluck('id'))
        ->get();
    
    foreach ($updatedUsers as $user) {
        printf("│ %-4s │ %-21s │ %-13s │\n", 
            $user->id, 
            substr($user->username, 0, 21), 
            $user->current_rank
        );
    }
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
}

echo "✨ Legacy users have been migrated to Phase 4!\n";
echo "Users can now see their rank information in the UI.\n\n";
