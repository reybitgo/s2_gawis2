<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\RankAdvancement;
use App\Services\RankAdvancementService;

echo "=== Verifying Rank Advancement Results ===\n\n";

$user = User::where('username', 'test_starter_eligible')->first();

if (!$user) {
    echo "❌ User not found!\n";
    exit(1);
}

echo "User: {$user->username} (ID: {$user->id})\n";
echo "Current Rank: {$user->current_rank}\n";
echo "Rank Package: {$user->rankPackage->name}\n";
echo "Rank Updated: {$user->rank_updated_at->format('M d, Y H:i:s')}\n\n";

// Get advancement progress
$rankService = app(RankAdvancementService::class);
$progress = $rankService->getRankAdvancementProgress($user);

echo "Rank Progress:\n";
echo "  Can Advance: " . ($progress['can_advance'] ? 'Yes' : 'No') . "\n";
echo "  Next Rank: {$progress['next_rank']}\n";
echo "  Sponsors: {$progress['sponsors_count']}/{$progress['required_sponsors']}\n";
echo "  Progress: {$progress['progress_percentage']}%\n";
echo "  Is Eligible: " . ($progress['is_eligible'] ? 'Yes' : 'No') . "\n\n";

// Get advancement history
echo "Advancement History:\n";
$advancements = RankAdvancement::where('user_id', $user->id)
    ->orderBy('created_at', 'desc')
    ->get();

if ($advancements->isEmpty()) {
    echo "  No advancement history found\n";
} else {
    foreach ($advancements as $advancement) {
        echo "  [{$advancement->created_at->format('M d, Y H:i:s')}]\n";
        echo "    From: " . ($advancement->from_rank ?? 'None') . "\n";
        echo "    To: {$advancement->to_rank}\n";
        echo "    Type: {$advancement->advancement_type}\n";
        echo "    Sponsors: {$advancement->sponsors_count}/{$advancement->required_sponsors}\n";
        echo "    System Paid: ₱" . number_format($advancement->system_paid_amount, 2) . "\n";
        echo "    Order: #{$advancement->order_id}\n\n";
    }
}

echo "✅ Verification complete!\n";
