<?php

namespace App\Services;

use App\Models\User;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RankAdvancement;
use App\Models\DirectSponsorsTracker;
use App\Services\PointsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\MLMCommissionService;

class RankAdvancementService
{
    private PointsService $pointsService;

    public function __construct(PointsService $pointsService)
    {
        $this->pointsService = $pointsService;
    }

    /**
     * Track a new sponsorship and check if rank advancement is triggered
     * 
     * @param User $sponsor
     * @param User $newUser
     * @return bool Whether rank advancement was triggered
     */
    public function trackSponsorship(User $sponsor, User $newUser): bool
    {
        DB::beginTransaction();
        try {
            // Record the sponsorship
            DirectSponsorsTracker::create([
                'user_id' => $sponsor->id,
                'sponsored_user_id' => $newUser->id,
                'sponsored_at' => now(),
                'sponsored_user_rank_at_time' => $newUser->current_rank,
                'sponsored_user_package_id' => $newUser->rank_package_id,
                'counted_for_rank' => $newUser->current_rank,
            ]);

            Log::info('Sponsorship Tracked', [
                'sponsor_id' => $sponsor->id,
                'sponsor_rank' => $sponsor->current_rank,
                'new_user_id' => $newUser->id,
                'new_user_rank' => $newUser->current_rank,
            ]);

            // Check if advancement criteria met
            $advancementTriggered = $this->checkAndTriggerAdvancement($sponsor);

            DB::commit();
            return $advancementTriggered;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to track sponsorship', [
                'sponsor_id' => $sponsor->id,
                'new_user_id' => $newUser->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function checkAndTriggerAdvancement(User $user): bool
    {
        $currentPackage = $user->rankPackage;

        if (!$currentPackage) {
            Log::info('User has no rank package, cannot advance', ['user_id' => $user->id]);
            return false;
        }

        if (!$currentPackage->canAdvanceToNextRank()) {
            Log::info('User is at top rank, cannot advance', [
                'user_id' => $user->id,
                'current_rank' => $currentPackage->rank_name,
            ]);
            return false;
        }

        $directSponsorsCount = $user->getSameRankSponsorsCount();
        $currentPPV = $user->current_ppv;
        $currentGPV = $user->current_gpv;

        $requiredDirectsRecruit = $currentPackage->required_direct_sponsors;
        $pathAEligible = $directSponsorsCount >= $requiredDirectsRecruit;

        if ($pathAEligible) {
            Log::info('Rank Advancement: Path A (Recruitment) eligible', [
                'user_id' => $user->id,
                'directs' => $directSponsorsCount,
                'required' => $requiredDirectsRecruit,
            ]);

            return $this->advanceUserRank($user, $directSponsorsCount, 'recruitment');
        }

        if (!$currentPackage->rank_pv_enabled) {
            Log::info('Rank Advancement: PV-based disabled, Path B not available', [
                'user_id' => $user->id,
                'rank' => $currentPackage->rank_name,
            ]);
            return false;
        }

        $requiredDirectsPV = $currentPackage->required_sponsors_ppv_gpv;
        $requiredPPV = $currentPackage->ppv_required;
        $requiredGPV = $currentPackage->gpv_required;

        if ($directSponsorsCount < $requiredDirectsPV) {
            Log::info('Rank Advancement: Path B - Not enough direct sponsors', [
                'user_id' => $user->id,
                'directs' => $directSponsorsCount,
                'required_ppv_gpv' => $requiredDirectsPV,
            ]);
            return false;
        }

        if ($currentPPV < $requiredPPV) {
            Log::info('Rank Advancement: Path B - PPV requirement not met', [
                'user_id' => $user->id,
                'current_ppv' => $currentPPV,
                'required_ppv' => $requiredPPV,
            ]);
            return false;
        }

        if ($currentGPV < $requiredGPV) {
            Log::info('Rank Advancement: Path B - GPV requirement not met', [
                'user_id' => $user->id,
                'current_gpv' => $currentGPV,
                'required_gpv' => $requiredGPV,
            ]);
            return false;
        }

        Log::info('Rank Advancement: Path B (PV-based) eligible', [
            'user_id' => $user->id,
            'directs' => $directSponsorsCount,
            'ppv' => $currentPPV,
            'gpv' => $currentGPV,
        ]);

        return $this->advanceUserRank($user, $directSponsorsCount, 'pv_based');
    }

    /**
     * Backfill legacy sponsorships into direct_sponsors_tracker
     * This ensures all existing referrals are properly tracked going forward
     * 
     * @param User $user
     */
    private function backfillLegacySponsorships(User $user): void
    {
        // Get all direct referrals not yet tracked
        $legacyReferrals = User::where('sponsor_id', $user->id)
            ->whereNotIn('id', function ($query) use ($user) {
                $query->select('sponsored_user_id')
                    ->from('direct_sponsors_tracker')
                    ->where('user_id', $user->id);
            })
            ->get();

        foreach ($legacyReferrals as $referral) {
            try {
                DirectSponsorsTracker::create([
                    'user_id' => $user->id,
                    'sponsored_user_id' => $referral->id,
                    'sponsored_at' => $referral->created_at ?? now(),
                    'sponsored_user_rank_at_time' => $referral->current_rank,
                    'sponsored_user_package_id' => $referral->rank_package_id,
                    'counted_for_rank' => $referral->current_rank,
                ]);

                Log::info('Backfilled legacy sponsorship', [
                    'sponsor_id' => $user->id,
                    'referral_id' => $referral->id,
                    'referral_rank' => $referral->current_rank,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to backfill legacy sponsorship (may already exist)', [
                    'sponsor_id' => $user->id,
                    'referral_id' => $referral->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function advanceUserRank(
        User $user,
        int $sponsorsCount,
        string $advancementType = 'recruitment'
    ): bool {
        DB::beginTransaction();
        try {
            $currentPackage = $user->rankPackage;
            $nextPackage = $currentPackage->getNextRankPackage();

            if (!$nextPackage) {
                Log::error('Next rank package not found', [
                    'user_id' => $user->id,
                    'current_package_id' => $currentPackage->id,
                ]);
                DB::rollBack();
                return false;
            }

            $order = $this->createSystemFundedOrder($user, $nextPackage);

            if (!$order) {
                Log::error('Failed to create system-funded order', [
                    'user_id' => $user->id,
                    'package_id' => $nextPackage->id,
                ]);
                DB::rollBack();
                return false;
            }

            $user->update([
                'current_rank' => $nextPackage->rank_name,
                'rank_package_id' => $nextPackage->id,
                'rank_updated_at' => now(),
            ]);

            $user->activateNetwork();

            $this->pointsService->resetPPVGPVOnRankAdvancement($user);

            if ($advancementType === 'recruitment') {
                $advancementTypeDb = 'recruitment_based';
                $notes = "Rank advancement via recruitment path: {$sponsorsCount} same-rank sponsors";
            } else {
                $advancementTypeDb = 'pv_based';
                $notes = "Rank advancement via PV-based path: {$sponsorsCount} sponsors, {$user->current_ppv} PPV, {$user->current_gpv} GPV";
            }

            RankAdvancement::create([
                'user_id' => $user->id,
                'from_rank' => $currentPackage->rank_name,
                'to_rank' => $nextPackage->rank_name,
                'from_package_id' => $currentPackage->id,
                'to_package_id' => $nextPackage->id,
                'advancement_type' => $advancementTypeDb,
                'required_sponsors' => $advancementType === 'recruitment'
                    ? $currentPackage->required_direct_sponsors
                    : $currentPackage->required_sponsors_ppv_gpv,
                'sponsors_count' => $sponsorsCount,
                'system_paid_amount' => $nextPackage->rank_reward,
                'order_id' => $order->id,
                'notes' => $notes,
            ]);

            // CREDIT RANK REWARD TO USER WALLET (mlm_balance + withdrawable_balance)
            try {
                $wallet = $user->getOrCreateWallet();
                if ($wallet) {
                    $amount = (float) $nextPackage->rank_reward;
                    if ($amount > 0) {
                        $wallet->increment('mlm_balance', $amount);
                        $wallet->increment('withdrawable_balance', $amount);
                        $wallet->update(['last_transaction_at' => now()]);

                        // Activity log for rank reward
                        \App\Models\ActivityLog::createLog(
                            'mlm',
                            'rank_reward',
                            sprintf('%s received rank reward of ₱%s for advancing to %s', $user->username ?? $user->fullname ?? 'User', number_format($amount, 2), $nextPackage->rank_name),
                            'INFO',
                            $user->id,
                            [
                                'reward_amount' => $amount,
                                'to_rank' => $nextPackage->rank_name,
                                'order_id' => $order->id,
                                'package_id' => $nextPackage->id,
                            ],
                            null,
                            $order->id
                        );
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to credit rank reward to wallet', [
                    'user_id' => $user->id,
                    'package_id' => $nextPackage->id,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('Rank Advanced Successfully', [
                'user_id' => $user->id,
                'from_rank' => $currentPackage->rank_name,
                'to_rank' => $nextPackage->rank_name,
                'order_id' => $order->id,
                'sponsors_count' => $sponsorsCount,
            ]);

            // Send notification to user
            $this->sendRankAdvancementNotification($user, $currentPackage, $nextPackage, $order);

            DB::commit();

            // Trigger MLM commissions for the system-funded order so uplines
            // are rewarded on rank advancement as they are on regular checkouts.
            try {
                $mlmService = app(MLMCommissionService::class);
                $mlmService->processCommissions($order);
            } catch (\Exception $e) {
                Log::error('Failed to process MLM commissions after rank advancement', [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rank Advancement Failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Create a system-funded order for rank advancement
     * 
     * @param User $user
     * @param Package $package
     * @return Order|null
     */
    private function createSystemFundedOrder(User $user, Package $package): ?Order
    {
        try {
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'RANK-' . strtoupper(uniqid()),
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_method' => 'system_reward',
                'total_amount' => $package->rank_reward,
                'subtotal' => $package->rank_reward,
                'grand_total' => $package->rank_reward,
                'notes' => "System-funded rank advancement reward: {$package->rank_name}",
            ]);

            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'item_type' => 'package',
                'package_id' => $package->id,
                'product_id' => null,
                'quantity' => 1,
                'unit_price' => $package->rank_reward,
                'total_price' => $package->rank_reward,
            ]);

            Log::info('System-Funded Order Created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $user->id,
                'package_id' => $package->id,
                'amount' => $package->rank_reward,
            ]);

            return $order;
        } catch (\Exception $e) {
            Log::error('Failed to create system-funded order', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Send rank advancement notification
     * 
     * @param User $user
     * @param Package $fromPackage
     * @param Package $toPackage
     * @param Order $order
     */
    private function sendRankAdvancementNotification(User $user, Package $fromPackage, Package $toPackage, Order $order): void
    {
        // TODO: Implement notification (database + email)
        // $user->notify(new RankAdvancementNotification($fromPackage, $toPackage, $order));

        Log::info('Rank Advancement Notification Sent', [
            'user_id' => $user->id,
            'from_rank' => $fromPackage->rank_name,
            'to_rank' => $toPackage->rank_name,
        ]);
    }

    /**
     * Get user's rank advancement progress
     * 
     * @param User $user
     * @return array
     */
    public function getRankAdvancementProgress(User $user): array
    {
        $currentPackage = $user->rankPackage;

        if (!$currentPackage) {
            return [
                'current_rank' => 'Unranked',
                'can_advance' => false,
                'progress' => 0,
                'required' => 0,
                'remaining' => 0,
            ];
        }

        $sameRankSponsorsCount = $user->getSameRankSponsorsCount();
        $requiredSponsorsRecruit = $currentPackage->required_direct_sponsors;
        $remainingRecruit = max(0, $requiredSponsorsRecruit - $sameRankSponsorsCount);
        $progressRecruit = $requiredSponsorsRecruit > 0
            ? min(100, ($sameRankSponsorsCount / $requiredSponsorsRecruit) * 100)
            : 0;

        $requiredSponsorsPV = $currentPackage->required_sponsors_ppv_gpv;
        $remainingPV = max(0, $requiredSponsorsPV - $sameRankSponsorsCount);
        $progressPV = $requiredSponsorsPV > 0
            ? min(100, ($sameRankSponsorsCount / $requiredSponsorsPV) * 100)
            : 0;

        $currentPPV = $user->current_ppv;
        $requiredPPV = $currentPackage->ppv_required;
        $remainingPPV = max(0, $requiredPPV - $currentPPV);
        $progressPPV = $requiredPPV > 0
            ? min(100, ($currentPPV / $requiredPPV) * 100)
            : 0;

        $currentGPV = $user->current_gpv;
        $requiredGPV = $currentPackage->gpv_required;
        $remainingGPV = max(0, $requiredGPV - $currentGPV);
        $progressGPV = $requiredGPV > 0
            ? min(100, ($currentGPV / $requiredGPV) * 100)
            : 0;

        $pathAEligible = $sameRankSponsorsCount >= $requiredSponsorsRecruit;
        $pathBEligible = $currentPackage->rank_pv_enabled
            && $sameRankSponsorsCount >= $requiredSponsorsPV
            && $currentPPV >= $requiredPPV
            && $currentGPV >= $requiredGPV;

        return [
            'current_rank' => $currentPackage->rank_name,
            'current_rank_order' => $currentPackage->rank_order,
            'next_rank' => $currentPackage->nextRankPackage?->rank_name ?? 'Top Rank',
            'next_rank_package' => $currentPackage->nextRankPackage,
            'can_advance' => $currentPackage->canAdvanceToNextRank(),
            'rank_pv_enabled' => $currentPackage->rank_pv_enabled,
            'path_a' => [
                'sponsors_count' => $sameRankSponsorsCount,
                'required_sponsors' => $requiredSponsorsRecruit,
                'remaining_sponsors' => $remainingRecruit,
                'progress_percentage' => $progressRecruit,
                'is_eligible' => $pathAEligible,
            ],
            'path_b' => [
                'directs_ppv_gpv' => [
                    'current' => $sameRankSponsorsCount,
                    'required' => $requiredSponsorsPV,
                    'remaining' => $remainingPV,
                    'progress' => $progressPV,
                    'met' => $sameRankSponsorsCount >= $requiredSponsorsPV,
                ],
                'ppv' => [
                    'current' => $currentPPV,
                    'required' => $requiredPPV,
                    'remaining' => $remainingPPV,
                    'progress' => $progressPPV,
                    'met' => $currentPPV >= $requiredPPV,
                ],
                'gpv' => [
                    'current' => $currentGPV,
                    'required' => $requiredGPV,
                    'remaining' => $remainingGPV,
                    'progress' => $progressGPV,
                    'met' => $currentGPV >= $requiredGPV,
                ],
                'is_eligible' => $pathBEligible,
            ],
            'is_eligible' => $pathAEligible || $pathBEligible,
        ];
    }
}
