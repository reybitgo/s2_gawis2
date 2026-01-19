<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PointsTracker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PointsService
{
    public function processOrderPoints(Order $order): void
    {
        $order->load('orderItems.product');

        DB::beginTransaction();
        try {
            foreach ($order->orderItems as $item) {
                if ($item->product && $item->product->points_awarded > 0) {
                    $points = $item->product->points_awarded * $item->quantity;

                    $this->creditPPV($order->user, $points, $item);
                    $this->creditGPVToUplines($order->user, $points, $item);
                }
            }

            DB::commit();

            Log::info('Points processed for order', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'buyer_id' => $order->user_id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process order points', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function creditPPV(User $user, float $points, OrderItem $item): void
    {
        $user->increment('current_ppv', $points);
        $user->update(['ppv_gpv_updated_at' => now()]);

        $this->recordPoints($user, $points, 0, $item, null, 'product_purchase');
    }

    public function creditGPVToUplines(User $user, float $points, OrderItem $item): void
    {
        $user->increment('current_gpv', $points);
        $this->recordPoints($user, $points, $points, $item, null, 'product_purchase');

        $currentUpline = $user->sponsor;

        while ($currentUpline) {
            $currentUpline->increment('current_gpv', $points);
            $this->recordPoints($currentUpline, 0, $points, $item, $user->id, 'product_purchase');
            $currentUpline = $currentUpline->sponsor;
        }
    }

    private function recordPoints(User $user, float $ppv, float $gpv, OrderItem $item, ?int $awardedToUserId = null, string $pointType = 'product_purchase'): void
    {
        PointsTracker::create([
            'user_id' => $user->id,
            'order_item_id' => $item->id,
            'ppv' => $ppv,
            'gpv' => $gpv,
            'earned_at' => now(),
            'awarded_to_user_id' => $awardedToUserId,
            'point_type' => $pointType,
            'rank_at_time' => $user->current_rank,
        ]);
    }

    public function resetPPVGPVOnRankAdvancement(User $user): void
    {
        $previousPPV = $user->current_ppv;
        $previousGPV = $user->current_gpv;
        $previousRank = $user->current_rank;

        $user->update([
            'current_ppv' => 0,
            'current_gpv' => 0,
            'ppv_gpv_updated_at' => now(),
        ]);

        PointsTracker::create([
            'user_id' => $user->id,
            'order_item_id' => null,
            'ppv' => -$previousPPV,
            'gpv' => -$previousGPV,
            'earned_at' => now(),
            'awarded_to_user_id' => $user->id,
            'point_type' => 'rank_advancement_reset',
            'rank_at_time' => $previousRank,
        ]);

        Log::info('PPV/GPV Reset on Rank Advancement', [
            'user_id' => $user->id,
            'previous_rank' => $previousRank,
            'previous_ppv' => $previousPPV,
            'previous_gpv' => $previousGPV,
            'reset_to' => [0, 0],
        ]);
    }
}
