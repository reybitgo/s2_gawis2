<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\ActivityLog;
use App\Mail\OrderPaymentConfirmed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class WalletPaymentService
{
    /**
     * Check if user has sufficient wallet balance for order
     */
    public function hasInsufficientBalance(User $user, float $amount): bool
    {
        $wallet = $user->wallet;

        if (!$wallet || !$wallet->is_active) {
            return true;
        }

        return $wallet->total_balance < $amount;
    }

    /**
     * Get user's current wallet balance
     */
    public function getBalance(User $user): float
    {
        $wallet = $user->wallet;
        return $wallet ? $wallet->total_balance : 0.00;
    }

    /**
     * Process wallet payment for an order
     */
    public function processPayment(Order $order): array
    {
        try {
            DB::beginTransaction();

            $user = $order->user;

            // Lock wallet row for update to prevent race conditions
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

            if (!$wallet) {
                $wallet = $user->getOrCreateWallet();
            }

            // Validate wallet state
            if (!$wallet->is_active) {
                throw new \Exception('Wallet is not active');
            }

            // Check sufficient balance with locked row
            if ($wallet->total_balance < $order->total_amount) {
                throw new \Exception('Insufficient wallet balance');
            }

            // Create transaction record
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'payment',
                'amount' => $order->total_amount,
                'status' => 'completed',
                'payment_method' => 'wallet',
                'description' => "Payment for order {$order->order_number}",
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'payment_type' => 'order_payment',
                    'wallet_balance_before' => $wallet->total_balance,
                    'mlm_balance_before' => $wallet->mlm_balance,
                    'purchase_balance_before' => $wallet->purchase_balance,
                ],
            ]);

            // Deduct amount from wallet (purchase balance first, then MLM)
            if (!$wallet->deductCombinedBalance($order->total_amount)) {
                throw new \Exception('Failed to deduct wallet balance');
            }

            // Refresh wallet to get updated balances
            $wallet->refresh();

            // Update transaction metadata with final balance
            $transaction->update([
                'metadata' => array_merge($transaction->metadata, [
                    'wallet_balance_after' => $wallet->total_balance,
                    'mlm_balance_after' => $wallet->mlm_balance,
                    'purchase_balance_after' => $wallet->purchase_balance,
                ])
            ]);

            // Mark order as paid
            $order->markAsPaid();

            // Update order metadata with payment info
            $orderMetadata = $order->metadata ?? [];
            $orderMetadata['payment'] = [
                'method' => 'wallet',
                'transaction_id' => $transaction->id,
                'transaction_reference' => $transaction->reference_number,
                'paid_at' => now()->toISOString(),
                'amount_paid' => $order->total_amount,
            ];
            $order->update(['metadata' => $orderMetadata]);

            // Credit points if configured
            $order->creditPoints();

            // Log order payment
            ActivityLog::logOrder(
                event: 'order_paid',
                message: sprintf('%s paid ₱%s for order #%s via e-wallet',
                    $user->username ?? $user->fullname ?? 'User',
                    number_format($order->total_amount, 2),
                    $order->order_number
                ),
                order: $order,
                level: 'INFO',
                additionalMetadata: [
                    'payment_method' => 'wallet',
                    'transaction_id' => $transaction->id,
                    'wallet_balance_after' => $wallet->total_balance,
                ]
            );

            DB::commit();

            // Send payment confirmation email
            $this->sendPaymentConfirmationEmail($order, 'wallet');

            Log::info('Wallet payment processed successfully', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'transaction_id' => $transaction->id,
                'amount' => $order->total_amount,
            ]);

            return [
                'success' => true,
                'message' => 'Payment processed successfully',
                'transaction' => $transaction,
                'remaining_balance' => $wallet->total_balance,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Wallet payment failed', [
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Record failed payment for fraud detection
            if (app()->bound(FraudDetectionService::class)) {
                $fraudService = app(FraudDetectionService::class);
                $request = request();
                if ($request) {
                    $fraudService->recordFailedPayment($order->user_id, $request->ip());
                }
            }

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode(),
            ];
        }
    }

    /**
     * Validate if wallet payment is possible for given amount
     */
    public function validatePayment(User $user, float $amount): array
    {
        $wallet = $user->wallet;

        if (!$wallet) {
            return [
                'valid' => false,
                'message' => 'Wallet not found. Please contact support.',
                'code' => 'NO_WALLET',
            ];
        }

        if (!$wallet->is_active) {
            return [
                'valid' => false,
                'message' => 'Wallet is not active. Please contact support.',
                'code' => 'WALLET_INACTIVE',
            ];
        }

        if ($wallet->total_balance < $amount) {
            return [
                'valid' => false,
                'message' => sprintf(
                    'Insufficient balance. Required: %s, Available: %s',
                    currency($amount),
                    currency($wallet->total_balance)
                ),
                'code' => 'INSUFFICIENT_BALANCE',
                'required_amount' => $amount,
                'available_balance' => $wallet->total_balance,
            ];
        }

        return [
            'valid' => true,
            'message' => 'Payment validation successful',
            'available_balance' => $wallet->total_balance,
            'remaining_after_payment' => $wallet->total_balance - $amount,
        ];
    }

    /**
     * Get payment summary for checkout display
     */
    public function getPaymentSummary(User $user, float $orderAmount): array
    {
        $wallet = $user->wallet;
        $currentBalance = $wallet ? $wallet->total_balance : 0;
        $validation = $this->validatePayment($user, $orderAmount);

        return [
            'current_balance' => $currentBalance,
            'formatted_balance' => currency($currentBalance),
            'order_amount' => $orderAmount,
            'formatted_order_amount' => currency($orderAmount),
            'remaining_balance' => $validation['valid'] ? $validation['remaining_after_payment'] : 0,
            'formatted_remaining_balance' => currency(
                $validation['valid'] ? $validation['remaining_after_payment'] : 0
            ),
            'can_pay' => $validation['valid'],
            'validation_message' => $validation['message'],
            'wallet_active' => $wallet ? $wallet->is_active : false,
        ];
    }

    /**
     * Refund payment to wallet (for order cancellations)
     */
    public function refundPayment(Order $order): array
    {
        try {
            DB::beginTransaction();

            $user = $order->user;

            // Lock wallet row for update to prevent race conditions
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

            if (!$wallet) {
                $wallet = $user->getOrCreateWallet();
            }

            // Validate order is paid and can be refunded
            if (!$order->isPaid()) {
                throw new \Exception('Order is not in paid status');
            }

            // Create refund transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'refund',
                'amount' => $order->total_amount,
                'status' => 'completed',
                'payment_method' => 'wallet',
                'description' => "Refund for cancelled order {$order->order_number}",
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'refund_type' => 'order_cancellation',
                    'wallet_balance_before' => $wallet->total_balance,
                    'mlm_balance_before' => $wallet->mlm_balance,
                    'purchase_balance_before' => $wallet->purchase_balance,
                ],
            ]);

            // Add refund amount to purchase balance
            $wallet->addPurchaseBalance($order->total_amount);

            // Refresh wallet to get updated balances
            $wallet->refresh();

            // Update transaction metadata with final balance
            $transaction->update([
                'metadata' => array_merge($transaction->metadata, [
                    'wallet_balance_after' => $wallet->total_balance,
                    'mlm_balance_after' => $wallet->mlm_balance,
                    'purchase_balance_after' => $wallet->purchase_balance,
                ])
            ]);

            // Update order payment status
            $order->update(['payment_status' => Order::PAYMENT_STATUS_REFUNDED]);

            // Log order refund
            ActivityLog::logOrder(
                event: 'order_refunded',
                message: sprintf('%s received refund of ₱%s for cancelled order #%s',
                    $user->username ?? $user->fullname ?? 'User',
                    number_format($order->total_amount, 2),
                    $order->order_number
                ),
                order: $order,
                level: 'INFO',
                additionalMetadata: [
                    'refund_method' => 'wallet',
                    'transaction_id' => $transaction->id,
                    'wallet_balance_after' => $wallet->total_balance,
                ]
            );

            DB::commit();

            Log::info('Wallet refund processed successfully', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'transaction_id' => $transaction->id,
                'amount' => $order->total_amount,
            ]);

            return [
                'success' => true,
                'message' => 'Refund processed successfully',
                'transaction' => $transaction,
                'new_balance' => $wallet->total_balance,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Wallet refund failed', [
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode(),
            ];
        }
    }

    /**
     * Send payment confirmation email to customer
     */
    private function sendPaymentConfirmationEmail(Order $order, string $paymentMethod): void
    {
        try {
            // Load the user relationship if not already loaded
            if (!$order->relationLoaded('user')) {
                $order->load('user');
            }

            $user = $order->user;

            // Check if user has verified email
            if ($user->hasVerifiedEmail()) {
                // Send payment confirmation email
                Mail::to($user->email)->send(
                    new OrderPaymentConfirmed($order, $paymentMethod)
                );

                Log::info('Payment confirmation email sent', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'recipient' => $user->email,
                    'payment_method' => $paymentMethod
                ]);
            } else {
                // User email not verified - skip sending and log
                Log::warning('Payment confirmation email skipped - User email not verified', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_id' => $user->id,
                    'user_name' => $user->fullname ?? $user->username,
                    'user_email' => $user->email ?? 'N/A',
                    'payment_method' => $paymentMethod
                ]);

                // Notify admins about this payment from unverified user
                $this->notifyAdminsAboutUnverifiedUser($order, $user, $paymentMethod);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation email', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'recipient' => $order->user->email ?? 'unknown',
                'payment_method' => $paymentMethod,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify admins about payment from unverified user
     */
    private function notifyAdminsAboutUnverifiedUser(Order $order, User $user, string $paymentMethod): void
    {
        try {
            $admins = User::role('admin')->get();

            foreach ($admins as $admin) {
                if ($admin->hasVerifiedEmail()) {
                    // Send email to admin
                    Mail::to($admin->email)->send(
                        new \App\Mail\UnverifiedUserOrderNotification($order, $user, 'paid')
                    );

                    Log::info('Admin notified about unverified user payment', [
                        'admin_id' => $admin->id,
                        'admin_email' => $admin->email,
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'unverified_user_id' => $user->id,
                        'unverified_user_name' => $user->fullname ?? $user->username,
                        'payment_method' => $paymentMethod
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify admins about unverified user payment', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}