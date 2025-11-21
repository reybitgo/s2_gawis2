<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\MonthlyQuotaTracker;
use App\Notifications\QuotaMetNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MonthlyQuotaService
{
    /**
     * Process PV points from a completed order
     *
     * @param Order $order
     * @return bool
     */
    public function processOrderPoints(Order $order): bool
    {
        $order->load('orderItems.product');

        // Filter only product order items (not packages)
        $productOrderItems = $order->orderItems->filter(function ($orderItem) {
            return $orderItem->isProduct() && $orderItem->product;
        });

        if ($productOrderItems->isEmpty()) {
            Log::info('Order has no products for PV tracking', ['order_id' => $order->id]);
            return false;
        }

        DB::beginTransaction();
        try {
            $buyer = $order->user;
            $totalPV = 0;

            // Calculate total PV from all products
            foreach ($productOrderItems as $orderItem) {
                $product = $orderItem->product;
                $pvPoints = $product->points_awarded * $orderItem->quantity;
                $totalPV += $pvPoints;
            }

            if ($totalPV <= 0) {
                Log::info('Order has no PV points (products have 0 points_awarded)', [
                    'order_id' => $order->id,
                    'buyer_id' => $buyer->id
                ]);
                DB::commit();
                return false;
            }

            // Add PV to user's current month tracker
            $this->addPointsToUser($buyer, $totalPV, $order);

            Log::info('PV Points Added to Monthly Tracker', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'buyer_id' => $buyer->id,
                'buyer_username' => $buyer->username,
                'pv_added' => $totalPV,
                'products' => $productOrderItems->map(fn($item) => [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'points_awarded' => $item->product->points_awarded,
                    'total_pv' => $item->product->points_awarded * $item->quantity,
                ])->toArray()
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process order PV points', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Add PV points to user's current month tracker
     *
     * @param User $user
     * @param float $pvPoints
     * @param Order $order
     * @return MonthlyQuotaTracker
     */
    public function addPointsToUser(User $user, float $pvPoints, Order $order): MonthlyQuotaTracker
    {
        $tracker = MonthlyQuotaTracker::getOrCreateForCurrentMonth($user);

        $previousPV = $tracker->total_pv_points;
        $wasQuotaMet = $tracker->quota_met;
        
        $tracker->total_pv_points += $pvPoints;
        $tracker->last_purchase_at = now();
        
        // Update required quota in case package changed
        $tracker->required_quota = $user->getMonthlyQuotaRequirement();
        
        // Check if quota is now met
        $tracker->checkQuotaMet();

        // Send notification if quota just became met
        if (!$wasQuotaMet && $tracker->quota_met && $tracker->required_quota > 0) {
            $user->notify(new QuotaMetNotification($this->getUserMonthlyStatus($user)));

            Log::info('Quota Met Notification Sent', [
                'user_id' => $user->id,
                'user_username' => $user->username,
                'total_pv' => $tracker->total_pv_points,
                'required_quota' => $tracker->required_quota,
            ]);
        }

        Log::info('PV Points Updated', [
            'user_id' => $user->id,
            'user_username' => $user->username,
            'order_id' => $order->id,
            'pv_added' => $pvPoints,
            'previous_pv' => $previousPV,
            'new_total_pv' => $tracker->total_pv_points,
            'required_quota' => $tracker->required_quota,
            'quota_met' => $tracker->quota_met,
            'year_month' => $tracker->year . '-' . $tracker->month
        ]);

        return $tracker;
    }

    /**
     * Get user's current month PV status
     *
     * @param User $user
     * @return array
     */
    public function getUserMonthlyStatus(User $user): array
    {
        $tracker = MonthlyQuotaTracker::getOrCreateForCurrentMonth($user);

        return [
            'year' => $tracker->year,
            'month' => $tracker->month,
            'month_name' => now()->setMonth($tracker->month)->format('F'),
            'total_pv' => $tracker->total_pv_points,
            'required_quota' => $tracker->required_quota,
            'remaining_pv' => max(0, $tracker->required_quota - $tracker->total_pv_points),
            'quota_met' => $tracker->quota_met,
            'progress_percentage' => $tracker->required_quota > 0 
                ? min(100, ($tracker->total_pv_points / $tracker->required_quota) * 100)
                : 100,
            'last_purchase_at' => $tracker->last_purchase_at,
            'qualifies_for_bonus' => $user->qualifiesForUnilevelBonus(),
        ];
    }

    /**
     * Get user's monthly quota history
     *
     * @param User $user
     * @param int $months Number of months to retrieve
     * @return \Illuminate\Support\Collection
     */
    public function getUserQuotaHistory(User $user, int $months = 6)
    {
        return $user->monthlyQuotaTrackers()
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take($months)
            ->get()
            ->map(function($tracker) {
                return [
                    'year' => $tracker->year,
                    'month' => $tracker->month,
                    'month_name' => now()->setMonth($tracker->month)->format('F'),
                    'total_pv' => $tracker->total_pv_points,
                    'required_quota' => $tracker->required_quota,
                    'quota_met' => $tracker->quota_met,
                    'progress_percentage' => $tracker->required_quota > 0 
                        ? min(100, ($tracker->total_pv_points / $tracker->required_quota) * 100)
                        : 100,
                ];
            });
    }

    /**
     * Recalculate quota status for a specific month (admin utility)
     *
     * @param User $user
     * @param int $year
     * @param int $month
     * @return bool
     */
    public function recalculateMonthlyQuota(User $user, int $year, int $month): bool
    {
        $tracker = $user->monthlyQuotaTrackers()
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if (!$tracker) {
            return false;
        }

        // Recalculate total PV from orders
        $totalPV = $user->orders()
            ->where('payment_status', 'paid')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->with('orderItems.product')
            ->get()
            ->flatMap(function($order) {
                return $order->orderItems->filter(fn($item) => $item->isProduct() && $item->product);
            })
            ->sum(function($orderItem) {
                return $orderItem->product->points_awarded * $orderItem->quantity;
            });

        $tracker->total_pv_points = $totalPV;
        $tracker->checkQuotaMet();

        Log::info('Monthly quota recalculated', [
            'user_id' => $user->id,
            'year' => $year,
            'month' => $month,
            'recalculated_pv' => $totalPV,
            'quota_met' => $tracker->quota_met
        ]);

        return true;
    }
}
