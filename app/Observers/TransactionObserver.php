<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Services\NotificationService;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        // Check if transaction amount exceeds threshold
        NotificationService::notifyLargeTransaction($transaction);

        // Check for suspicious transaction patterns
        $this->checkSuspiciousActivity($transaction);
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        // If transaction was flagged or status changed to failed
        if ($transaction->wasChanged('status') && $transaction->status === 'failed') {
            NotificationService::notifySuspiciousActivity(
                $transaction->user,
                'Transaction failed',
                [
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'type' => $transaction->type,
                    'reason' => $transaction->notes ?? 'Unknown'
                ]
            );
        }
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        NotificationService::notifySuspiciousActivity(
            $transaction->user,
            'Transaction deleted',
            [
                'transaction_id' => $transaction->id,
                'amount' => $transaction->amount,
                'type' => $transaction->type,
                'deleted_by' => auth()->user()?->name ?? 'System'
            ]
        );
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Check for suspicious transaction activity
     */
    private function checkSuspiciousActivity(Transaction $transaction): void
    {
        $user = $transaction->user;

        // Check for rapid consecutive transactions
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        if ($recentTransactions > 3) {
            NotificationService::notifySuspiciousActivity(
                $user,
                'Rapid consecutive transactions',
                [
                    'transaction_count' => $recentTransactions,
                    'time_window' => '5 minutes',
                    'latest_amount' => $transaction->amount,
                    'latest_type' => $transaction->type
                ]
            );
        }

        // Check for round number amounts (potential testing)
        if ($transaction->amount >= 1000 && $transaction->amount % 1000 == 0) {
            NotificationService::notifySuspiciousActivity(
                $user,
                'Round number transaction detected',
                [
                    'amount' => $transaction->amount,
                    'type' => $transaction->type,
                    'note' => 'Potential testing or suspicious activity'
                ]
            );
        }

        // Check for transactions during unusual hours (midnight to 6 AM)
        $hour = now()->hour;
        if ($hour >= 0 && $hour <= 6 && $transaction->amount > 500) {
            NotificationService::notifySuspiciousActivity(
                $user,
                'Unusual hour transaction',
                [
                    'amount' => $transaction->amount,
                    'type' => $transaction->type,
                    'time' => now()->format('H:i:s'),
                    'note' => 'Large transaction during unusual hours'
                ]
            );
        }
    }
}
