<?php

namespace App\Services;

use App\Models\User;
use App\Models\Package;
use App\Models\MlmSetting;
use Illuminate\Support\Facades\Log;

class RankComparisonService
{
    /**
     * Get the effective MLM commission for an upline based on rank comparison
     * 
     * FOR MLM COMMISSIONS ONLY (package purchases)
     * Unilevel bonuses (product purchases) are NOT affected by this
     * 
     * PREREQUISITE: Upline must be network active (checked by MLMCommissionService)
     * This method assumes upline has already passed isNetworkActive() check
     * 
     * RULE 1: If upline has higher rank than buyer, upline gets buyer's (lower) rate
     * RULE 2: If upline has lower rank than buyer, upline gets their own (lower) rate
     * RULE 3: If both have same rank, use standard commission
     * 
     * @param User $upline
     * @param User $buyer
     * @param int $level
     * @return float
     */
    public function getEffectiveCommission(User $upline, User $buyer, int $level): float
    {
        // CRITICAL: This method assumes upline is already network active
        // The calling service (MLMCommissionService) MUST check isNetworkActive() first
        // We do not re-check here to avoid redundant queries
        
        $uplinePackage = $upline->rankPackage;
        $buyerPackage = $buyer->rankPackage;

        // CRITICAL: If either has no rank package, NO COMMISSION
        // Both upline and buyer MUST have ranks for rank-based commission to apply
        if (!$uplinePackage || !$buyerPackage) {
            Log::info('No rank-based commission: Missing rank package', [
                'upline_id' => $upline->id,
                'upline_has_rank' => !is_null($uplinePackage),
                'upline_rank' => $uplinePackage?->rank_name ?? 'None',
                'buyer_id' => $buyer->id,
                'buyer_has_rank' => !is_null($buyerPackage),
                'buyer_rank' => $buyerPackage?->rank_name ?? 'None',
                'level' => $level,
            ]);
            return 0.00; // NO COMMISSION if either lacks rank
        }

        $uplineRankOrder = $uplinePackage->rank_order;
        $buyerRankOrder = $buyerPackage->rank_order;

        Log::info('Rank Comparison for Commission', [
            'upline_id' => $upline->id,
            'upline_rank' => $uplinePackage->rank_name,
            'upline_rank_order' => $uplineRankOrder,
            'buyer_id' => $buyer->id,
            'buyer_rank' => $buyerPackage->rank_name,
            'buyer_rank_order' => $buyerRankOrder,
            'level' => $level,
        ]);

        // RULE 1: Higher rank upline with lower rank buyer
        // Upline gets buyer's lower commission rate
        if ($uplineRankOrder > $buyerRankOrder) {
            $commission = MlmSetting::getCommissionForLevel($buyerPackage->id, $level);
            
            Log::info('RULE 1 Applied: Higher rank upline gets lower rank buyer rate', [
                'upline_rank' => $uplinePackage->rank_name,
                'buyer_rank' => $buyerPackage->rank_name,
                'commission' => $commission,
                'reason' => 'Preventing unfair advantage from rank difference'
            ]);
            
            return $commission;
        }

        // RULE 2: Lower rank upline with higher rank buyer
        // Upline gets their own lower commission rate
        if ($uplineRankOrder < $buyerRankOrder) {
            $commission = MlmSetting::getCommissionForLevel($uplinePackage->id, $level);
            
            Log::info('RULE 2 Applied: Lower rank upline gets their own rate', [
                'upline_rank' => $uplinePackage->rank_name,
                'buyer_rank' => $buyerPackage->rank_name,
                'commission' => $commission,
                'reason' => 'Motivation to rank up for higher earnings'
            ]);
            
            return $commission;
        }

        // RULE 3: Same rank - use buyer's package commission (standard)
        $commission = MlmSetting::getCommissionForLevel($buyerPackage->id, $level);
        
        Log::info('RULE 3 Applied: Same rank, standard commission', [
            'upline_rank' => $uplinePackage->rank_name,
            'buyer_rank' => $buyerPackage->rank_name,
            'commission' => $commission,
        ]);
        
        return $commission;
    }

    /**
     * Get a detailed explanation of why this commission was calculated this way
     * 
     * @param User $upline
     * @param User $buyer
     * @param int $level
     * @return array
     */
    public function getCommissionExplanation(User $upline, User $buyer, int $level): array
    {
        $uplinePackage = $upline->rankPackage;
        $buyerPackage = $buyer->rankPackage;

        // If either has no rank package, NO COMMISSION
        if (!$uplinePackage || !$buyerPackage) {
            return [
                'rule' => 'No Rank = No Commission',
                'explanation' => sprintf(
                    'No commission given. %s %s rank. Both upline and buyer must have ranks for commission.',
                    !$uplinePackage ? 'Upline lacks' : 'Buyer lacks',
                    !$uplinePackage ? ($upline->username ?? 'User') : ($buyer->username ?? 'User')
                ),
                'commission' => 0.00,
                'upline_rank' => $uplinePackage?->rank_name ?? 'None',
                'buyer_rank' => $buyerPackage?->rank_name ?? 'None',
                'reason' => 'missing_rank_package',
            ];
        }

        $uplineRankOrder = $uplinePackage->rank_order;
        $buyerRankOrder = $buyerPackage->rank_order;
        $commission = $this->getEffectiveCommission($upline, $buyer, $level);

        if ($uplineRankOrder > $buyerRankOrder) {
            return [
                'rule' => 'Rule 1: Higher Rank → Lower Rate',
                'explanation' => "As a {$uplinePackage->rank_name}, you earn {$buyerPackage->rank_name}'s commission rate when sponsoring lower-ranked members.",
                'upline_rank' => $uplinePackage->rank_name,
                'buyer_rank' => $buyerPackage->rank_name,
                'commission' => $commission,
                'package_used' => $buyerPackage->name,
            ];
        }

        if ($uplineRankOrder < $buyerRankOrder) {
            return [
                'rule' => 'Rule 2: Lower Rank → Own Rate',
                'explanation' => "As a {$uplinePackage->rank_name}, you earn your own commission rate. Advance to {$buyerPackage->rank_name} to earn more!",
                'upline_rank' => $uplinePackage->rank_name,
                'buyer_rank' => $buyerPackage->rank_name,
                'commission' => $commission,
                'package_used' => $uplinePackage->name,
                'motivation' => "Rank up to {$buyerPackage->rank_name} to increase your earnings!",
            ];
        }

        return [
            'rule' => 'Rule 3: Same Rank → Standard',
            'explanation' => "Both you and your downline are {$uplinePackage->rank_name}, standard commission applies.",
            'upline_rank' => $uplinePackage->rank_name,
            'buyer_rank' => $buyerPackage->rank_name,
            'commission' => $commission,
            'package_used' => $buyerPackage->name,
        ];
    }
}
