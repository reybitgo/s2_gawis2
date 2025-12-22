<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Transaction;
use App\Services\RankAdvancementService;
use Illuminate\Support\Facades\DB;

echo "=== DEMO: Force Rank Advancement (calls advanceUserRank) ===\n\n";

$rankService = app(RankAdvancementService::class);

// Find a candidate user whose current package has a next rank package
$candidate = null;
$checked = 0;
foreach (User::whereNotNull('rank_package_id')->limit(200)->get() as $u) {
    $checked++;
    $pkg = $u->rankPackage;
    if ($pkg && method_exists($pkg, 'getNextRankPackage') && $pkg->getNextRankPackage()) {
        $candidate = $u;
        break;
    }
}

if (!$candidate) {
    echo "No suitable user found (searched {$checked} users).\n";
    echo "Please ensure there is a user with a rank package that can advance.\n";
    exit(1);
}

echo "Found candidate user: {$candidate->username} (ID: {$candidate->id})\n";
echo "Current Rank: " . ($candidate->current_rank ?? 'N/A') . "\n";

// Use a sponsors count heuristic (use actual count if available)
$sponsorsCount = method_exists($candidate, 'getSameRankSponsorsCount') ? $candidate->getSameRankSponsorsCount() : 5;

echo "Attempting to force advance with sponsorsCount={$sponsorsCount}...\n";

$beforeTransactions = Transaction::where('created_at', '>=', now()->subMinutes(5))->count();

$success = $rankService->advanceUserRank($candidate, $sponsorsCount);

if ($success) {
    echo "advanceUserRank() returned: SUCCESS\n";
    $candidate->refresh();
    echo "New Rank: {$candidate->current_rank}\n";

    // Try to find the most recent system-funded order for this user
    try {
        $order = $candidate->orders()->where('payment_method', 'system_reward')->orderByDesc('id')->first();
    } catch (\Exception $e) {
        // Some DB schemas may not have `payment_method`; fallback to order_number prefix
        $order = $candidate->orders()->where('order_number', 'like', 'RANK-%')->orderByDesc('id')->first();
    }

    if ($order) {
        echo "System-funded order created: {$order->order_number} (ID: {$order->id})\n";

        // Check for MLM commission transactions linked to this order
        $txns = Transaction::where('type', 'mlm_commission')
            ->where('source_order_id', $order->id)
            ->orderBy('level')
            ->get();

        echo "Found " . $txns->count() . " MLM commission transaction(s) for order {$order->id}\n";
        foreach ($txns as $t) {
            $u = User::find($t->user_id);
            $uname = $u ? $u->username : 'N/A';
            echo " - Level {$t->level}: {$uname} -> ₱{$t->amount} (status: {$t->status})\n";
        }
    } else {
        echo "No system-funded order found for user (unexpected).\n";
    }
} else {
    echo "advanceUserRank() returned: FAILURE\n";
    echo "Check logs for details.\n";
}

echo "\nDone.\n";
