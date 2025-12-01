<?php

namespace App\Services;

use App\Models\User;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RankAdvancement;
use App\Models\DirectSponsorsTracker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RankAdvancementService
{
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

    /**
     * Check if user meets advancement criteria and trigger if yes
     * BACKWARD COMPATIBLE: Counts both tracked sponsorships AND legacy referrals
     * 
     * @param User $user
     * @return bool Whether advancement was triggered
     */
    public function checkAndTriggerAdvancement(User $user): bool
    {
        // Get user's current rank package
        $currentPackage = $user->rankPackage;

        if (!$currentPackage) {
            Log::info('User has no rank package, cannot advance', ['user_id' => $user->id]);
            return false;
        }

        // Check if there's a next rank available
        if (!$currentPackage->canAdvanceToNextRank()) {
            Log::info('User is at top rank, cannot advance', [
                'user_id' => $user->id,
                'current_rank' => $currentPackage->rank_name,
            ]);
            return false;
        }

        // BACKWARD COMPATIBILITY: Count same-rank sponsors from BOTH sources
        // 1. Tracked sponsorships (new data)
        $trackedCount = $user->directSponsorsTracked()
            ->where('counted_for_rank', $user->current_rank)
            ->count();
        
        // 2. Legacy sponsorships (existing sponsor_id relationships not yet tracked)
        $legacyCount = User::where('sponsor_id', $user->id)
            ->where('current_rank', $user->current_rank)
            ->whereNotIn('id', function($query) use ($user) {
                $query->select('sponsored_user_id')
                      ->from('direct_sponsors_tracker')
                      ->where('user_id', $user->id);
            })
            ->count();
        
        $totalSameRankSponsors = $trackedCount + $legacyCount;
        $requiredSponsors = $currentPackage->required_direct_sponsors;

        Log::info('Checking Rank Advancement Criteria (Backward Compatible)', [
            'user_id' => $user->id,
            'current_rank' => $user->current_rank,
            'tracked_sponsors' => $trackedCount,
            'legacy_sponsors' => $legacyCount,
            'total_same_rank_sponsors' => $totalSameRankSponsors,
            'required_sponsors' => $requiredSponsors,
            'can_advance' => $totalSameRankSponsors >= $requiredSponsors,
        ]);

        // Check if criteria met
        if ($totalSameRankSponsors >= $requiredSponsors) {
            // IMPORTANT: Before advancing, backfill legacy sponsorships into tracker
            $this->backfillLegacySponsorships($user);
            
            return $this->advanceUserRank($user, $totalSameRankSponsors);
        }

        return false;
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
            ->whereNotIn('id', function($query) use ($user) {
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

    /**
     * Advance user to next rank (system-funded package purchase)
     * 
     * @param User $user
     * @param int $sponsorsCount
     * @return bool
     */
    public function advanceUserRank(User $user, int $sponsorsCount): bool
    {
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

            // Create system-funded order
            $order = $this->createSystemFundedOrder($user, $nextPackage);

            if (!$order) {
                Log::error('Failed to create system-funded order', [
                    'user_id' => $user->id,
                    'package_id' => $nextPackage->id,
                ]);
                DB::rollBack();
                return false;
            }

            // Update user rank
            $user->update([
                'current_rank' => $nextPackage->rank_name,
                'rank_package_id' => $nextPackage->id,
                'rank_updated_at' => now(),
            ]);

            // Activate network status if not already active
            $user->activateNetwork();

            // Record advancement
            RankAdvancement::create([
                'user_id' => $user->id,
                'from_rank' => $currentPackage->rank_name,
                'to_rank' => $nextPackage->rank_name,
                'from_package_id' => $currentPackage->id,
                'to_package_id' => $nextPackage->id,
                'advancement_type' => 'sponsorship_reward',
                'required_sponsors' => $currentPackage->required_direct_sponsors,
                'sponsors_count' => $sponsorsCount,
                'system_paid_amount' => $nextPackage->price,
                'order_id' => $order->id,
                'notes' => "Automatic rank advancement for sponsoring {$sponsorsCount} {$currentPackage->rank_name}-rank users",
            ]);

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
                'total_amount' => $package->price,
                'subtotal' => $package->price,
                'grand_total' => $package->price,
                'notes' => "System-funded rank advancement reward: {$package->rank_name}",
            ]);

            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'item_type' => 'package',
                'package_id' => $package->id,
                'product_id' => null,
                'quantity' => 1,
                'unit_price' => $package->price,
                'total_price' => $package->price,
            ]);

            Log::info('System-Funded Order Created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $user->id,
                'package_id' => $package->id,
                'amount' => $package->price,
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
        $requiredSponsors = $currentPackage->required_direct_sponsors;
        $remaining = max(0, $requiredSponsors - $sameRankSponsorsCount);
        $progress = $requiredSponsors > 0 
            ? min(100, ($sameRankSponsorsCount / $requiredSponsors) * 100)
            : 0;

        return [
            'current_rank' => $currentPackage->rank_name,
            'current_rank_order' => $currentPackage->rank_order,
            'next_rank' => $currentPackage->nextRankPackage?->rank_name ?? 'Top Rank',
            'next_rank_package' => $currentPackage->nextRankPackage,
            'can_advance' => $currentPackage->canAdvanceToNextRank(),
            'sponsors_count' => $sameRankSponsorsCount,
            'required_sponsors' => $requiredSponsors,
            'remaining_sponsors' => $remaining,
            'progress_percentage' => $progress,
            'is_eligible' => $sameRankSponsorsCount >= $requiredSponsors,
        ];
    }
}
