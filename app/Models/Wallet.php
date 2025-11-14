<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'is_active',
        'last_transaction_at',
        'mlm_balance',
        'unilevel_balance',
        'withdrawable_balance',
        'purchase_balance',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_transaction_at' => 'datetime',
        'mlm_balance' => 'decimal:2',
        'unilevel_balance' => 'decimal:2',
        'withdrawable_balance' => 'decimal:2',
        'purchase_balance' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function transactions()
    {
        return $this->hasMany(\App\Models\Transaction::class, 'user_id', 'user_id');
    }

    /**
     * Add balance (deprecated - use addPurchaseBalance or addMLMIncome instead)
     * This method is kept for backward compatibility with withdrawal rejections
     */
    public function addBalance($amount)
    {
        // For backward compatibility, add to purchase balance
        $this->addPurchaseBalance($amount);
    }

    /**
     * Subtract balance (deprecated - use deductCombinedBalance instead)
     * This method is kept for backward compatibility
     */
    public function subtractBalance($amount)
    {
        return $this->deductCombinedBalance($amount);
    }

    /**
     * Get total available balance (Withdrawable + Purchase)
     * Note: mlm_balance is excluded as it's a lifetime tracker only (already reflected in withdrawable_balance)
     */
    public function getTotalBalanceAttribute(): float
    {
        return (float) ($this->withdrawable_balance + $this->purchase_balance);
    }

    /**
     * Get total lifetime earnings (MLM tracker only)
     * For display purposes - shows total MLM commissions ever earned
     */
    public function getLifetimeMLMEarningsAttribute(): float
    {
        return (float) $this->mlm_balance;
    }

    /**
     * Get total lifetime Unilevel earnings (Unilevel tracker only)
     * For display purposes - shows total Unilevel bonuses ever earned
     */
    public function getLifetimeUnilevelEarningsAttribute(): float
    {
        return (float) $this->unilevel_balance;
    }

    /**
     * Get total lifetime earnings (MLM + Unilevel trackers combined)
     * For display purposes - shows all earnings from both compensation plans
     */
    public function getLifetimeEarningsAttribute(): float
    {
        return (float) ($this->mlm_balance + $this->unilevel_balance);
    }

    /**
     * Get available for withdrawal (withdrawable_balance only)
     * ONLY this balance can be withdrawn to bank
     */
    public function getAvailableForWithdrawalAttribute(): float
    {
        return (float) $this->withdrawable_balance;
    }

    /**
     * Add MLM commission (AUTOMATIC DUAL-CREDITING)
     * Credits BOTH mlm_balance (tracker) AND withdrawable_balance (withdrawable)
     *
     * @param float $amount Commission amount
     * @param string $description Transaction description
     * @param int $level Commission level (1-5)
     * @param int|null $sourceOrderId Source order ID that triggered the commission (nullable for tests)
     * @return bool Success status
     */
    public function addMLMCommission(float $amount, string $description, int $level, ?int $sourceOrderId = null): bool
    {
        \DB::beginTransaction();
        try {
            // AUTOMATIC DUAL-CREDITING:
            // 1. Credit mlm_balance (lifetime earnings tracker)
            $this->increment('mlm_balance', $amount);

            // 2. Credit withdrawable_balance (instantly withdrawable!)
            $this->increment('withdrawable_balance', $amount);

            $this->update(['last_transaction_at' => now()]);

            

            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to add MLM commission', [
                'wallet_id' => $this->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Legacy method - redirects to addMLMCommission
     * Kept for backward compatibility
     *
     * @deprecated Use addMLMCommission() instead
     */
    public function addMLMIncome(float $amount, string $description, int $level, ?int $sourceOrderId = null): bool
    {
        return $this->addMLMCommission($amount, $description, $level, $sourceOrderId);
    }

    /**
     * Add Unilevel bonus (AUTOMATIC DUAL-CREDITING)
     * Credits BOTH unilevel_balance (tracker) AND withdrawable_balance (withdrawable)
     *
     * @param float $amount Bonus amount
     * @param string $description Transaction description
     * @param int $level Unilevel level (1-5)
     * @param int|null $sourceOrderId Source order ID that triggered the bonus (nullable for tests)
     * @return bool Success status
     */
    public function addUnilevelBonus(float $amount, string $description, int $level, ?int $sourceOrderId = null): bool
    {
        \DB::beginTransaction();
        try {
            // AUTOMATIC DUAL-CREDITING:
            // 1. Credit unilevel_balance (lifetime earnings tracker)
            $this->increment('unilevel_balance', $amount);

            // 2. Credit withdrawable_balance (instantly withdrawable!)
            $this->increment('withdrawable_balance', $amount);

            $this->update(['last_transaction_at' => now()]);

            

            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to add Unilevel bonus', [
                'wallet_id' => $this->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Add purchase balance (deposits, transfers)
     */
    public function addPurchaseBalance(float $amount): void
    {
        $this->increment('purchase_balance', $amount);
        $this->update(['last_transaction_at' => now()]);
    }

    /**
     * Deduct from combined balance with priority system
     * Priority: purchase_balance → withdrawable_balance
     * Note: mlm_balance is never deducted (lifetime tracker only)
     * Used for package/product purchases
     *
     * @param float $amount Amount to deduct
     * @return bool Success status
     */
    public function deductCombinedBalance(float $amount): bool
    {
        if ($this->total_balance < $amount) {
            return false;
        }

        \DB::beginTransaction();
        try {
            $remaining = $amount;
            $deductions = [];

            // 1. Deduct from purchase_balance first (non-withdrawable deposits)
            if ($remaining > 0 && $this->purchase_balance > 0) {
                $deduct = min($this->purchase_balance, $remaining);
                $this->decrement('purchase_balance', $deduct);
                $remaining -= $deduct;
                $deductions['purchase_balance'] = $deduct;
            }

            // 2. Then deduct from withdrawable_balance (withdrawable MLM earnings + other withdrawable funds)
            if ($remaining > 0 && $this->withdrawable_balance > 0) {
                $deduct = min($this->withdrawable_balance, $remaining);
                $this->decrement('withdrawable_balance', $deduct);
                $remaining -= $deduct;
                $deductions['withdrawable_balance'] = $deduct;
            }

            // Note: mlm_balance is NEVER deducted - it's a lifetime tracker for display only
            // The actual withdrawable amount is already in withdrawable_balance

            $this->update(['last_transaction_at' => now()]);

            \Log::info('Combined balance deducted', [
                'wallet_id' => $this->id,
                'total_amount' => $amount,
                'deductions' => $deductions,
                'remaining_after' => $remaining
            ]);

            \DB::commit();
            return $remaining == 0;

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to deduct combined balance', [
                'wallet_id' => $this->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get comprehensive wallet balance summary
     */
    public function getBalanceSummary(): array
    {
        return [
            'mlm_balance' => (float) $this->mlm_balance,
            'unilevel_balance' => (float) $this->unilevel_balance,
            'withdrawable_balance' => (float) $this->withdrawable_balance,
            'purchase_balance' => (float) $this->purchase_balance,
            'total_balance' => $this->total_balance,
            'lifetime_mlm_earnings' => $this->lifetime_mlm_earnings,
            'lifetime_unilevel_earnings' => $this->lifetime_unilevel_earnings,
            'lifetime_earnings' => $this->lifetime_earnings,
            'available_for_withdrawal' => $this->available_for_withdrawal
        ];
    }

    /**
     * Legacy method - redirects to getBalanceSummary
     *
     * @deprecated Use getBalanceSummary() instead
     */
    public function getMLMBalanceSummary(): array
    {
        return $this->getBalanceSummary();
    }

    /**
     * Check if user can withdraw specific amount
     * ONLY withdrawable_balance can be withdrawn
     */
    public function canWithdraw(float $amount): bool
    {
        return $this->withdrawable_balance >= $amount;
    }
}
