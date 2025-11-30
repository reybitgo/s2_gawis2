<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\MlmSetting;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\ActivityLog;
use App\Notifications\MLMCommissionEarned;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MLMCommissionService
{
    /**
     * Rank comparison service for rank-aware MLM commissions
     */
    protected RankComparisonService $rankComparison;

    /**
     * Constructor
     */
    public function __construct(RankComparisonService $rankComparison)
    {
        $this->rankComparison = $rankComparison;
    }
    /**
     * Process MLM commissions for a completed order
     *
     * @param Order $order
     * @return bool
     */
    public function processCommissions(Order $order): bool
    {
        // Load order items with packages
        $order->load('orderItems.package');

        // Check if order contains any MLM packages
        $mlmOrderItems = $order->orderItems->filter(function($orderItem) {
            return $orderItem->package && $orderItem->package->is_mlm_package;
        });

        if ($mlmOrderItems->isEmpty()) {
            Log::info('Order does not have MLM packages', ['order_id' => $order->id]);
            return false;
        }

        DB::beginTransaction();
        try {
            $buyer = $order->user;
            $allCommissionsDistributed = [];

            // Process commissions for each MLM package in the order
            foreach ($mlmOrderItems as $orderItem) {
                $package = $orderItem->package;
                $currentUser = $buyer->sponsor; // Start with immediate sponsor
                $level = 1;
                $maxLevels = $package->max_mlm_levels ?? 5;

                $commissionsDistributed = [];

                // Traverse upline up to max levels
                while ($currentUser && $level <= $maxLevels) {
                    // CRITICAL: Check network active status BEFORE rank comparison
                    if (!$currentUser->isNetworkActive()) {
                        Log::info('Upline skipped: Not network active', [
                            'upline_id' => $currentUser->id,
                            'level' => $level,
                            'network_status' => $currentUser->network_status,
                        ]);
                        $currentUser = $currentUser->sponsor;
                        continue; // Skip to the next sponsor
                    }
                    
                    // Apply rank-aware commission calculation (only for active users)
                    $commission = $this->rankComparison->getEffectiveCommission(
                        $currentUser,  // upline (already verified as active)
                        $buyer,        // buyer
                        $level
                    );

                    $explanation = $this->rankComparison->getCommissionExplanation(
                        $currentUser,
                        $buyer,
                        $level
                    );

                    Log::info('Rank-Aware Commission Calculated (Active User)', [
                        'upline_id' => $currentUser->id,
                        'buyer_id' => $buyer->id,
                        'level' => $level,
                        'upline_network_status' => $currentUser->network_status, // Should be 'active'
                        'rule_applied' => $explanation['rule'],
                        'commission' => $commission,
                        'explanation' => $explanation['explanation'],
                    ]);

                    if ($commission > 0) {
                        // Multiply commission by quantity if ordering multiple of same package
                        $totalCommission = $commission * $orderItem->quantity;

                        // Credit commission to upline's MLM balance
                        $success = $this->creditCommission(
                            $currentUser,
                            $totalCommission,
                            $order,
                            $level,
                            $buyer,
                            $package
                        );

                        if ($success) {
                            $commissionsDistributed[] = [
                                'user_id' => $currentUser->id,
                                'user_name' => $currentUser->username ?? $currentUser->fullname ?? 'Unknown',
                                'level' => $level,
                                'amount' => $totalCommission,
                                'package_id' => $package->id,
                                'package_name' => $package->name,
                                'quantity' => $orderItem->quantity
                            ];

                            // Send real-time notification
                            $this->sendCommissionNotification($currentUser, $totalCommission, $level, $buyer, $order, $package);
                        }
                    }

                    // Move to next level upline
                    $currentUser = $currentUser->sponsor;
                    $level++;
                }

                $allCommissionsDistributed = array_merge($allCommissionsDistributed, $commissionsDistributed);
            }

            // Log commission distribution
            Log::info('MLM Commissions Distributed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'buyer_id' => $buyer->id,
                'buyer_name' => $buyer->username ?? $buyer->fullname ?? 'Unknown',
                'mlm_packages' => $mlmOrderItems->pluck('package.name')->toArray(),
                'commissions' => $allCommissionsDistributed,
                'total_distributed' => array_sum(array_column($allCommissionsDistributed, 'amount'))
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MLM Commission Distribution Failed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Credit commission to user's wallet (AUTOMATIC DUAL-CREDITING)
     * Credits BOTH mlm_balance (tracker) AND withdrawable_balance (withdrawable)
     *
     * @param User $user
     * @param float $amount
     * @param Order $order
     * @param int $level
     * @param User $buyer
     * @param \App\Models\Package $package
     * @return bool
     */
    private function creditCommission(User $user, float $amount, Order $order, int $level, User $buyer, $package = null): bool
    {
        try {
            // Check if user is active (has purchased a package)
            if (!$user->isNetworkActive()) {
                Log::info('User is not active (no package purchase), skipping commission', [
                    'user_id' => $user->id,
                    'user_name' => $user->username ?? $user->fullname ?? 'Unknown',
                    'level' => $level
                ]);
                return false;
            }

            $wallet = $user->wallet;

            if (!$wallet) {
                Log::warning('User has no wallet', ['user_id' => $user->id, 'user_name' => $user->username ?? $user->fullname ?? 'Unknown']);
                return false;
            }

            // AUTOMATIC DUAL-CREDITING using Wallet model method
            // This will credit BOTH mlm_balance (tracker) AND withdrawable_balance (withdrawable)
            $description = sprintf(
                'Level %d MLM Commission from %s (Order #%s)',
                $level,
                $buyer->username ?? $buyer->fullname ?? 'Unknown',
                $order->order_number
            );

            $success = $wallet->addMLMCommission($amount, $description, $level, $order->id);

            if (!$success) {
                Log::error('Failed to add MLM commission via wallet method', [
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'level' => $level
                ]);
                return false;
            }

            // Log commission to activity log (database)
            ActivityLog::logMLMCommission(
                recipient: $user,
                amount: $amount,
                level: $level,
                buyer: $buyer,
                order: $order,
                packageId: $package ? $package->id : null,
                packageName: $package ? $package->name : null
            );

            Log::info('MLM Commission Credited (Automatic Dual-Crediting)', [
                'recipient_id' => $user->id,
                'recipient_name' => $user->username ?? $user->fullname ?? 'Unknown',
                'amount' => $amount,
                'level' => $level,
                'order_id' => $order->id,
                'package_name' => $package ? $package->name : 'N/A',
                'credited_to' => 'mlm_balance+withdrawable_balance'
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to credit commission', [
                'user_id' => $user->id,
                'user_name' => $user->username ?? $user->fullname ?? 'Unknown',
                'amount' => $amount,
                'level' => $level,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send commission earned notification to upline member
     *
     * @param User $user
     * @param float $commission
     * @param int $level
     * @param User $buyer
     * @param Order $order
     * @param \App\Models\Package|null $package
     * @return void
     */
    private function sendCommissionNotification(User $user, float $commission, int $level, User $buyer, Order $order, $package = null): void
    {
        try {
            $user->notify(new MLMCommissionEarned($commission, $level, $buyer, $order));

            Log::info('MLM Commission Notification Sent', [
                'recipient_id' => $user->id,
                'recipient_name' => $user->username ?? $user->fullname ?? 'Unknown',
                'has_verified_email' => $user->hasVerifiedEmail(),
                'amount' => $commission,
                'level' => $level,
                'package_name' => $package ? $package->name : 'N/A'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send commission notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get upline tree for a user (up to specified levels)
     *
     * @param User $user
     * @param int $maxLevels
     * @return array
     */
    public function getUplineTree(User $user, int $maxLevels = 5): array
    {
        $tree = [];
        $currentUser = $user->sponsor;
        $level = 1;

        while ($currentUser && $level <= $maxLevels) {
            $tree[] = [
                'level' => $level,
                'user' => $currentUser,
                'user_id' => $currentUser->id,
                'user_name' => $currentUser->username ?? $currentUser->fullname ?? 'Unknown',
                'referral_code' => $currentUser->referral_code
            ];

            $currentUser = $currentUser->sponsor;
            $level++;
        }

        return $tree;
    }

    /**
     * Calculate total potential commission for a package
     *
     * @param int $packageId
     * @return float
     */
    public function calculateTotalCommission(int $packageId): float
    {
        return MlmSetting::getTotalCommission($packageId);
    }

    /**
     * Get commission breakdown for a specific order
     *
     * @param Order $order
     * @return array
     */
    public function getCommissionBreakdown(Order $order): array
    {
        // Load order items with packages
        $order->load('orderItems.package');

        // Check if order contains any MLM packages
        $mlmOrderItems = $order->orderItems->filter(function($orderItem) {
            return $orderItem->package && $orderItem->package->is_mlm_package;
        });

        if ($mlmOrderItems->isEmpty()) {
            return [];
        }

        $allBreakdowns = [];
        $buyer = $order->user;

        // Get breakdown for each MLM package in the order
        foreach ($mlmOrderItems as $orderItem) {
            $package = $orderItem->package;
            $currentUser = $buyer->sponsor;
            $level = 1;
            $maxLevels = $package->max_mlm_levels ?? 5;

            while ($currentUser && $level <= $maxLevels) {
                // Skip inactive users
                if (!$currentUser->isNetworkActive()) {
                    $currentUser = $currentUser->sponsor;
                    $level++;
                    continue;
                }

                // Use rank-aware commission calculation
                $commission = $this->rankComparison->getEffectiveCommission(
                    $currentUser,
                    $buyer,
                    $level
                );

                if ($commission > 0) {
                    $totalCommission = $commission * $orderItem->quantity;

                    $explanation = $this->rankComparison->getCommissionExplanation(
                        $currentUser,
                        $buyer,
                        $level
                    );

                    $allBreakdowns[] = [
                        'level' => $level,
                        'user_id' => $currentUser->id,
                        'user_name' => $currentUser->username ?? $currentUser->fullname ?? 'Unknown',
                        'referral_code' => $currentUser->referral_code,
                        'commission' => $totalCommission,
                        'package_id' => $package->id,
                        'package_name' => $package->name,
                        'quantity' => $orderItem->quantity,
                        'rank_rule' => $explanation['rule'] ?? null,
                        'upline_rank' => $explanation['upline_rank'] ?? null,
                        'buyer_rank' => $explanation['buyer_rank'] ?? null,
                    ];
                }

                $currentUser = $currentUser->sponsor;
                $level++;
            }
        }

        return $allBreakdowns;
    }
}
