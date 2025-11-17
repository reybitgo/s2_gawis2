<?php

namespace App\Services;

use App\Models\Order;
use App\Models\UnilevelSetting;
use App\Models\User;
use App\Models\ActivityLog;
use App\Notifications\UnilevelBonusEarned;
use App\Services\MonthlyQuotaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnilevelBonusService
{
    /**
     * Process Unilevel bonuses for a completed order.
     *
     * @param Order $order
     * @return bool
     */
    public function processBonuses(Order $order): bool
    {
        $order->load('orderItems.product');

        $productOrderItems = $order->orderItems->filter(function ($orderItem) {
            return $orderItem->isProduct();
        });

        if ($productOrderItems->isEmpty()) {
            Log::info('Order does not contain any products for Unilevel bonus processing.', ['order_id' => $order->id]);
            return false;
        }

        DB::beginTransaction();
        try {
            $buyer = $order->user;
            $allBonusesDistributed = [];

            foreach ($productOrderItems as $orderItem) {
                $product = $orderItem->product;
                if (!$product) continue;

                $currentUser = $buyer->sponsor;
                $level = 1;
                $maxLevels = 5; // Or get from a central config if dynamic

                $bonusesDistributed = [];

                while ($currentUser && $level <= $maxLevels) {
                    // PHASE 3: Check both network active AND monthly quota
                    if (!$currentUser->qualifiesForUnilevelBonus()) {
                        // Enhanced logging for why user was skipped
                        $quotaService = app(MonthlyQuotaService::class);
                        $monthlyStatus = $quotaService->getUserMonthlyStatus($currentUser);
                        
                        Log::info('Upline Skipped - Unilevel Qualification Failed', [
                            'order_id' => $order->id,
                            'buyer_id' => $buyer->id,
                            'buyer_username' => $buyer->username,
                            'upline_id' => $currentUser->id,
                            'upline_username' => $currentUser->username,
                            'level' => $level,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'reason' => [
                                'is_network_active' => $currentUser->isNetworkActive(),
                                'meets_monthly_quota' => $currentUser->meetsMonthlyQuota(),
                                'monthly_pv' => $monthlyStatus['total_pv'],
                                'required_quota' => $monthlyStatus['required_quota'],
                                'remaining_pv' => $monthlyStatus['remaining_pv'],
                                'progress_percentage' => number_format($monthlyStatus['progress_percentage'], 2) . '%',
                            ],
                        ]);
                        
                        $currentUser = $currentUser->sponsor;
                        continue; // Skip to the next sponsor
                    }
                    
                    $setting = UnilevelSetting::where('product_id', $product->id)
                                            ->where('level', $level)
                                            ->where('is_active', true)
                                            ->first();

                    if ($setting && $setting->bonus_amount > 0) {
                        $totalBonus = $setting->bonus_amount * $orderItem->quantity;

                        $success = $this->creditBonus(
                            $currentUser,
                            $totalBonus,
                            $order,
                            $level,
                            $buyer,
                            $product
                        );

                        if ($success) {
                            $bonusesDistributed[] = [
                                'user_id' => $currentUser->id,
                                'user_name' => $currentUser->username,
                                'level' => $level,
                                'amount' => $totalBonus,
                                'product_id' => $product->id,
                                'product_name' => $product->name,
                                'quantity' => $orderItem->quantity
                            ];
                        }
                    }

                    $currentUser = $currentUser->sponsor;
                    $level++;
                }
                $allBonusesDistributed = array_merge($allBonusesDistributed, $bonusesDistributed);
            }

            Log::info('Unilevel Bonuses Distributed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'buyer_id' => $buyer->id,
                'commissions' => $allBonusesDistributed,
                'total_distributed' => array_sum(array_column($allBonusesDistributed, 'amount'))
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unilevel Bonus Distribution Failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Credit bonus to a user's wallet.
     */
    private function creditBonus(User $user, float $amount, Order $order, int $level, User $buyer, \App\Models\Product $product): bool
    {
        try {
            // PHASE 3: Check both network active AND monthly quota
            if (!$user->qualifiesForUnilevelBonus()) {
                $quotaService = app(MonthlyQuotaService::class);
                $monthlyStatus = $quotaService->getUserMonthlyStatus($user);
                
                Log::info('User does not qualify for unilevel bonus', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'level' => $level,
                    'order_id' => $order->id,
                    'buyer_id' => $buyer->id,
                    'product_id' => $product->id,
                    'reason' => [
                        'is_network_active' => $user->isNetworkActive(),
                        'meets_monthly_quota' => $user->meetsMonthlyQuota(),
                        'monthly_pv' => $monthlyStatus['total_pv'],
                        'required_quota' => $monthlyStatus['required_quota'],
                        'remaining_pv' => $monthlyStatus['remaining_pv'],
                        'progress_percentage' => number_format($monthlyStatus['progress_percentage'], 2) . '%',
                    ],
                ]);
                return false;
            }

            $wallet = $user->wallet;
            if (!$wallet) {
                Log::warning('User has no wallet for unilevel bonus', ['user_id' => $user->id]);
                return false;
            }

            $description = sprintf(
                'Level %d Unilevel Bonus from %s (Order #%s)',
                $level,
                $buyer->username,
                $order->order_number
            );

            $success = $wallet->addUnilevelBonus($amount, $description, $level, $order->id);

            if (!$success) {
                Log::error('Failed to add Unilevel bonus via wallet method', ['user_id' => $user->id, 'amount' => $amount]);
                return false;
            }

            // Log unilevel bonus to activity log (database)
            Log::info('Logging Unilevel Bonus', [
                'recipient' => $user->id,
                'amount' => $amount,
                'level' => $level,
                'buyer' => $buyer->id,
                'order' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
            ]);

            $log = ActivityLog::logUnilevelBonus(
                recipient: $user,
                amount: $amount,
                level: $level,
                buyer: $buyer,
                order: $order,
                productId: $product->id,
                productName: $product->name
            );

            Log::info('Activity Log Created', ['log_id' => $log->id]);

            Log::info('Unilevel Bonus Credited', [
                'recipient_id' => $user->id,
                'amount' => $amount,
                'level' => $level,
                'order_id' => $order->id,
                'product_name' => $product->name
            ]);

            // Send real-time notification
            $user->notify(new UnilevelBonusEarned($amount, $level, $buyer, $order, $product));

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to credit unilevel bonus', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
