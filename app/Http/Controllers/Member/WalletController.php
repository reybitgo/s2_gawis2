<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;
use App\Models\ActivityLog;
use App\Mail\DepositNotification;
use App\Mail\WithdrawalNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class WalletController extends Controller
{
    use AuthorizesRequests, \App\Http\Traits\HasPaginationLimit;

    public function deposit()
    {
        $this->authorize('deposit_funds');

        $paymentMethods = [
            'gcash_enabled' => \App\Models\SystemSetting::get('gcash_enabled', true),
            'gcash_number' => \App\Models\SystemSetting::get('gcash_number', ''),
            'gcash_name' => \App\Models\SystemSetting::get('gcash_name', ''),
            'maya_enabled' => \App\Models\SystemSetting::get('maya_enabled', true),
            'maya_number' => \App\Models\SystemSetting::get('maya_number', ''),
            'maya_name' => \App\Models\SystemSetting::get('maya_name', ''),
            'cash_enabled' => \App\Models\SystemSetting::get('cash_enabled', true),
            'others_enabled' => \App\Models\SystemSetting::get('others_enabled', true),
        ];

        return view('member.deposit', compact('paymentMethods'));
    }

    public function processDeposit(Request $request)
    {
        $this->authorize('deposit_funds');

        $request->validate([
            'amount' => 'required|numeric|min:1|max:10000',
            'payment_method' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        // Create transaction record
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount' => $request->amount,
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'description' => $request->description ?: 'Deposit via ' . $request->payment_method,
            'metadata' => [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        ]);

        // Log deposit request to activity log
        ActivityLog::logWalletTransaction(
            event: 'deposit_requested',
            message: sprintf(
                '%s requested deposit of ₱%s via %s',
                $user->username ?? $user->fullname ?? 'User',
                number_format($request->amount, 2),
                $request->payment_method
            ),
            transaction: $transaction,
            level: 'INFO'
        );

        // Send email notification to all admin users with verified emails
        $adminUsers = User::role('admin')->get();
        foreach ($adminUsers as $admin) {
            if ($admin->hasVerifiedEmail()) {
                try {
                    Mail::to($admin->email)->send(new DepositNotification($transaction, $user));
                    \Log::info('Deposit notification sent to admin', [
                        'admin_id' => $admin->id,
                        'admin_email' => $admin->email,
                        'transaction_id' => $transaction->id,
                        'user_id' => $user->id
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send deposit notification to admin', [
                        'admin_id' => $admin->id,
                        'admin_email' => $admin->email,
                        'transaction_id' => $transaction->id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                \Log::warning('Deposit notification skipped - Admin email not verified', [
                    'admin_id' => $admin->id,
                    'admin_email' => $admin->email ?? 'N/A',
                    'transaction_id' => $transaction->id,
                    'user_id' => $user->id
                ]);
            }
        }

        session()->flash('success', 'Deposit request of ₱' . number_format($request->amount, 2) . ' has been submitted for approval. Reference: ' . $transaction->reference_number);

        return redirect()->route('wallet.transactions');
    }

    public function transfer()
    {
        $this->authorize('transfer_funds');

        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();

        // Get transfer charge settings for JavaScript
        $transferSettings = [
            'charge_enabled' => \App\Models\SystemSetting::get('transfer_charge_enabled', false),
            'charge_type' => \App\Models\SystemSetting::get('transfer_charge_type', 'percentage'),
            'charge_value' => \App\Models\SystemSetting::get('transfer_charge_value', 0),
            'minimum_charge' => \App\Models\SystemSetting::get('transfer_minimum_charge', 0),
            'maximum_charge' => \App\Models\SystemSetting::get('transfer_maximum_charge', 999999),
        ];

        // Get frequent recipients (top 5 most frequently transferred to users)
        $frequentRecipients = \DB::table('transactions')
            ->join('users', function ($join) {
                $join->on('users.id', '=', \DB::raw('CAST(JSON_UNQUOTE(JSON_EXTRACT(transactions.metadata, "$.recipient_id")) AS UNSIGNED)'));
            })
            ->where('transactions.user_id', $user->id)
            ->where('transactions.type', 'transfer_out')
            ->where('transactions.status', 'approved')
            ->select(
                'users.id',
                'users.username',
                'users.email',
                'users.fullname',
                \DB::raw('COUNT(*) as transfer_count'),
                \DB::raw('MAX(transactions.created_at) as last_transfer_at')
            )
            ->groupBy('users.id', 'users.username', 'users.email', 'users.fullname')
            ->orderBy('transfer_count', 'desc')
            ->orderBy('last_transfer_at', 'desc')
            ->limit(5)
            ->get();

        return view('member.transfer', compact('wallet', 'transferSettings', 'frequentRecipients'));
    }

    public function processTransfer(Request $request)
    {
        $this->authorize('transfer_funds');

        $sender = Auth::user();

        // Remove transfer limits for admin (user ID 1)
        $maxAmount = ($sender->id === 1) ? PHP_INT_MAX : 10000;

        $request->validate([
            'recipient_identifier' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1|max:' . $maxAmount,
            'note' => 'nullable|string|max:255',
        ]);

        $senderWallet = $sender->getOrCreateWallet();

        // Find recipient by email or username
        $recipientIdentifier = $request->recipient_identifier;

        if (filter_var($recipientIdentifier, FILTER_VALIDATE_EMAIL)) {
            $recipient = \App\Models\User::where('email', $recipientIdentifier)->first();
        } else {
            $recipient = \App\Models\User::where('username', $recipientIdentifier)->first();
        }

        if (!$recipient) {
            return redirect()->back()->withErrors(['recipient_identifier' => 'Recipient not found. Please check the email or username.']);
        }

        // Check if trying to transfer to self
        if ($recipient->id === $sender->id) {
            return redirect()->back()->withErrors(['recipient_identifier' => 'You cannot transfer funds to yourself.']);
        }

        // Calculate transfer charge
        $transferAmount = $request->amount;
        $transferCharge = $this->calculateTransferCharge($transferAmount);
        $totalAmount = $transferAmount + $transferCharge;

        // Check if sender's wallet has sufficient PURCHASE balance (including charge) - only purchase balance can be transferred
        if ($senderWallet->purchase_balance < $totalAmount) {
            return redirect()->back()->withErrors(['amount' => 'Insufficient purchase balance. You need ' . currency($totalAmount) . ' (Transfer: ' . currency($transferAmount) . ' + Fee: ' . currency($transferCharge) . '). Your current purchase balance is ' . currency($senderWallet->purchase_balance) . '. Note: Only purchase balance can be transferred. Convert MLM balance to purchase balance first if needed.']);
        }

        // Check if sender's wallet is active
        if (!$senderWallet->is_active) {
            return redirect()->back()->withErrors(['general' => 'Your wallet is currently frozen. Please contact support.']);
        }

        try {
            \DB::transaction(function () use ($request, $sender, $recipient, $transferAmount, $transferCharge, $totalAmount) {
                // Lock both wallets in consistent order (by user ID) to prevent deadlock
                $lockOrder = [$sender->id, $recipient->id];
                sort($lockOrder);

                // Lock wallets for update to prevent race conditions
                $senderWallet = Wallet::where('user_id', $sender->id)->lockForUpdate()->first();
                $recipientWallet = Wallet::where('user_id', $recipient->id)->lockForUpdate()->first();

                if (!$recipientWallet) {
                    $recipientWallet = $recipient->getOrCreateWallet();
                }

                // Re-check purchase balance after locking (another transaction might have changed it)
                if ($senderWallet->purchase_balance < $totalAmount) {
                    throw new \Exception('Insufficient purchase balance after lock');
                }

                // Create outgoing transaction for sender
                $outgoingTransaction = Transaction::create([
                    'user_id' => $sender->id,
                    'type' => 'transfer_out',
                    'amount' => $transferAmount,
                    'status' => 'approved', // Transfers are instant
                    'payment_method' => 'internal',
                    'description' => 'Transfer to ' . ($recipient->username ?: $recipient->email) . ($request->note ? ' - ' . $request->note : ''),
                    'metadata' => [
                        'recipient_id' => $recipient->id,
                        'recipient_email' => $recipient->email,
                        'recipient_username' => $recipient->username,
                        'transfer_charge' => $transferCharge,
                        'total_amount' => $totalAmount,
                        'note' => $request->note,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]
                ]);

                // Create incoming transaction for recipient
                $incomingTransaction = Transaction::create([
                    'user_id' => $recipient->id,
                    'type' => 'transfer_in',
                    'amount' => $transferAmount,
                    'status' => 'approved',
                    'payment_method' => 'internal',
                    'description' => 'Transfer from ' . ($sender->username ?: $sender->email) . ($request->note ? ' - ' . $request->note : ''),
                    'metadata' => [
                        'sender_id' => $sender->id,
                        'sender_email' => $sender->email,
                        'sender_username' => $sender->username,
                        'note' => $request->note,
                        'related_transaction_id' => $outgoingTransaction->id,
                    ]
                ]);

                // Create transfer charge transaction if applicable
                if ($transferCharge > 0) {
                    Transaction::create([
                        'user_id' => $sender->id,
                        'type' => 'transfer_charge',
                        'amount' => $transferCharge,
                        'status' => 'approved',
                        'payment_method' => 'internal',
                        'description' => 'Transfer fee for transaction to ' . ($recipient->username ?: $recipient->email),
                        'metadata' => [
                            'related_transaction_id' => $outgoingTransaction->id,
                            'transfer_amount' => $transferAmount,
                            'charge_type' => \App\Models\SystemSetting::get('transfer_charge_type', 'percentage'),
                            'charge_value' => \App\Models\SystemSetting::get('transfer_charge_value', 0),
                        ]
                    ]);
                }

                // Update reference numbers to link transactions
                $outgoingTransaction->update([
                    'metadata' => array_merge($outgoingTransaction->metadata ?? [], [
                        'related_transaction_id' => $incomingTransaction->id
                    ])
                ]);

                // Update wallet balances - deduct both transfer amount and fee from purchase balance only
                $senderWallet->decrement('purchase_balance', $totalAmount);
                $senderWallet->update(['last_transaction_at' => now()]);

                $recipientWallet->addPurchaseBalance($transferAmount); // Recipient gets transfer as purchase balance

                // Log transfer operations
                ActivityLog::logWalletTransaction(
                    event: 'transfer_sent',
                    message: sprintf(
                        '%s transferred ₱%s to %s',
                        $sender->username ?? $sender->fullname ?? 'User',
                        number_format($transferAmount, 2),
                        $recipient->username ?? $recipient->fullname ?? 'User'
                    ),
                    transaction: $outgoingTransaction,
                    level: 'INFO'
                );

                ActivityLog::logWalletTransaction(
                    event: 'transfer_received',
                    message: sprintf(
                        '%s received ₱%s from %s',
                        $recipient->username ?? $recipient->fullname ?? 'User',
                        number_format($transferAmount, 2),
                        $sender->username ?? $sender->fullname ?? 'User'
                    ),
                    transaction: $incomingTransaction,
                    level: 'INFO'
                );
            });

            $message = 'Transfer of ₱' . number_format($transferAmount, 2) . ' to ' . ($recipient->username ?: $recipient->email) . ' completed successfully!';
            if ($transferCharge > 0) {
                $message .= ' (Transfer fee: ₱' . number_format($transferCharge, 2) . ' deducted)';
            }
            $message .= ' The funds have been transferred instantly.';
            session()->flash('success', $message);

            return redirect()->route('wallet.transfer');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['general' => 'Transfer failed. Please try again later.']);
        }
    }

    public function withdraw()
    {
        $this->authorize('withdraw_funds');

        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();

        // Calculate pending withdrawals to show available balance (only pending withdrawal amounts, fees are already deducted)
        $pendingWithdrawals = Transaction::where('user_id', $user->id)
            ->where('type', 'withdrawal')
            ->where('status', 'pending')
            ->sum('amount');

        // Withdrawable balance is the combined MLM and Unilevel earnings
        $availableBalance = $wallet->withdrawable_balance - $pendingWithdrawals;

        // Get payment method settings
        $paymentSettings = [
            'allow_others' => \App\Models\SystemSetting::get('others_enabled', true),
            'gcash_enabled' => \App\Models\SystemSetting::get('gcash_enabled', true),
            'maya_enabled' => \App\Models\SystemSetting::get('maya_enabled', true),
            'cash_enabled' => \App\Models\SystemSetting::get('cash_enabled', true),
        ];

        // Get withdrawal fee settings for JavaScript
        $withdrawalFeeSettings = [
            'fee_enabled' => \App\Models\SystemSetting::get('withdrawal_fee_enabled', false),
            'fee_type' => \App\Models\SystemSetting::get('withdrawal_fee_type', 'percentage'),
            'fee_value' => \App\Models\SystemSetting::get('withdrawal_fee_value', 0),
            'minimum_fee' => \App\Models\SystemSetting::get('withdrawal_minimum_fee', 0),
            'maximum_fee' => \App\Models\SystemSetting::get('withdrawal_maximum_fee', 999999),
        ];

        return view('member.withdraw', compact('wallet', 'paymentSettings', 'pendingWithdrawals', 'availableBalance', 'withdrawalFeeSettings'));
    }

    public function processWithdraw(Request $request)
    {
        $this->authorize('withdraw_funds');

        \Log::info('Withdrawal request started', [
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        $validationRules = [
            'amount' => 'required|numeric|min:1|max:10000',
            'payment_method' => 'required|string|max:255',
            'agree_terms' => 'required|accepted',
        ];

        // Determine the actual payment method (either from select or custom input)
        $paymentMethod = $request->payment_method;
        if ($paymentMethod === 'Others' && $request->custom_payment_method) {
            $paymentMethod = $request->custom_payment_method;
            $validationRules['custom_payment_method'] = 'required|string|max:255';
        }

        // Add conditional validation based on payment method
        if ($request->payment_method === 'Gcash') {
            $validationRules['gcash_number'] = 'required|string|max:11';
        } elseif ($request->payment_method === 'Maya') {
            $validationRules['maya_number'] = 'required|string|max:11';
        } elseif ($request->payment_method === 'Cash') {
            $validationRules['pickup_location'] = 'nullable|string|max:255'; // Optional - defaults to office address
        } elseif ($request->payment_method === 'Others') {
            $validationRules['payment_details'] = 'required|string|max:1000';
        }

        $request->validate($validationRules);

        // Auto-fill pickup location with admin's delivery address if not provided (for Cash method)
        if ($request->payment_method === 'Cash' && empty($request->pickup_location)) {
            // Get admin's delivery address
            $adminUser = \App\Models\User::role('admin')->first();
            $officeAddress = 'Main Office';
            if ($adminUser) {
                $addressParts = array_filter([
                    $adminUser->address,
                    $adminUser->address_2,
                    $adminUser->city,
                    $adminUser->state,
                    $adminUser->zip,
                ]);
                $officeAddress = !empty($addressParts) ? implode(', ', $addressParts) : 'Main Office';
            }
            $request->merge([
                'pickup_location' => $officeAddress
            ]);
        }

        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();

        // Calculate withdrawal fee
        $withdrawalAmount = $request->amount;
        $withdrawalFee = $this->calculateWithdrawalFee($withdrawalAmount);
        $totalAmount = $withdrawalAmount + $withdrawalFee;

        // Calculate total pending withdrawal requests for this user
        $pendingWithdrawals = Transaction::where('user_id', $user->id)
            ->where('type', 'withdrawal')
            ->where('status', 'pending')
            ->sum('amount');

        // Check available Withdrawable balance (MLM + Unilevel)
        $availableBalance = $wallet->withdrawable_balance - $pendingWithdrawals;

        if ($availableBalance < $totalAmount) {
            $errorMessage = 'Insufficient withdrawable balance. ';
            if ($withdrawalFee > 0) {
                $errorMessage .= 'Total required: ' . currency($totalAmount) . ' (Withdrawal: ' . currency($withdrawalAmount) . ' + Fee: ' . currency($withdrawalFee) . '). ';
            } else {
                $errorMessage .= 'Total required: ' . currency($withdrawalAmount) . '. ';
            }
            if ($pendingWithdrawals > 0) {
                $errorMessage .= 'You have ' . currency($pendingWithdrawals) . ' in pending withdrawals. ';
            }
            $errorMessage .= 'Your available withdrawable balance is ' . currency($availableBalance) . '.';

            return redirect()->back()->withErrors(['amount' => $errorMessage]);
        }

        // Check if user's wallet is active
        if (!$wallet->is_active) {
            return redirect()->back()->withErrors(['general' => 'Your wallet is currently frozen. Please contact support.']);
        }

        try {
            $transaction = null;

            \DB::transaction(function () use ($request, $user, $wallet, $withdrawalAmount, $withdrawalFee, $totalAmount, $paymentMethod, &$transaction) {
                // Build description based on payment method
                $description = 'Withdrawal via ' . $paymentMethod;
                if ($request->payment_method === 'Gcash') {
                    $description .= ' to ' . $request->gcash_number;
                } elseif ($request->payment_method === 'Maya') {
                    $description .= ' to ' . $request->maya_number;
                } elseif ($request->payment_method === 'Cash') {
                    $description .= ' - Pickup at: ' . $request->pickup_location;
                }

                // Build metadata based on payment method
                $metadata = [
                    'payment_method' => $paymentMethod,
                    'withdrawal_method' => strtolower($paymentMethod),
                    'withdrawal_fee' => $withdrawalFee,
                    'total_amount' => $totalAmount,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ];

                if ($request->payment_method === 'Gcash') {
                    $metadata['gcash_number'] = $request->gcash_number;
                } elseif ($request->payment_method === 'Maya') {
                    $metadata['maya_number'] = $request->maya_number;
                } elseif ($request->payment_method === 'Cash') {
                    $metadata['pickup_location'] = $request->pickup_location;
                } elseif ($request->payment_method === 'Others') {
                    $metadata['payment_details'] = $request->payment_details;
                }

                // Create withdrawal transaction
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdrawal',
                    'amount' => $withdrawalAmount,
                    'status' => 'pending', // Withdrawals need approval
                    'payment_method' => $paymentMethod,
                    'description' => $description,
                    'metadata' => $metadata,
                ]);

                // Create withdrawal fee transaction if applicable and deduct immediately
                if ($withdrawalFee > 0) {
                    Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'withdrawal_fee',
                        'amount' => $withdrawalFee,
                        'status' => 'approved', // Fee is deducted immediately upon submission
                        'payment_method' => 'internal',
                        'description' => 'Withdrawal processing fee for ' . $description,
                        'metadata' => [
                            'related_transaction_id' => $transaction->id,
                            'withdrawal_amount' => $withdrawalAmount,
                            'fee_type' => \App\Models\SystemSetting::get('withdrawal_fee_type', 'percentage'),
                            'fee_value' => \App\Models\SystemSetting::get('withdrawal_fee_value', 0),
                            'processed_at' => now(),
                            'auto_processed' => true,
                        ]
                    ]);

                    // Immediately deduct the fee from MLM balance only (purchase balance cannot be used for withdrawals)
                    $wallet->decrement('mlm_balance', $withdrawalFee);
                    $wallet->update(['last_transaction_at' => now()]);
                }

                // For manual processing, don't deduct withdrawal amount until approved
                // Just update last transaction time
                $wallet->update(['last_transaction_at' => now()]);

                // Log withdrawal request
                ActivityLog::logWalletTransaction(
                    event: 'withdrawal_requested',
                    message: sprintf(
                        '%s requested withdrawal of ₱%s via %s',
                        $user->username ?? $user->fullname ?? 'User',
                        number_format($withdrawalAmount, 2),
                        $paymentMethod
                    ),
                    transaction: $transaction,
                    level: 'INFO'
                );
            });

            // Send email notification to all admin users with verified emails
            $adminUsers = User::role('admin')->get();
            foreach ($adminUsers as $admin) {
                if ($admin->hasVerifiedEmail()) {
                    try {
                        Mail::to($admin->email)->send(new WithdrawalNotification($transaction, $user));
                        \Log::info('Withdrawal notification sent to admin', [
                            'admin_id' => $admin->id,
                            'admin_email' => $admin->email,
                            'transaction_id' => $transaction->id,
                            'user_id' => $user->id
                        ]);
                    } catch (\Exception $emailError) {
                        \Log::error('Failed to send withdrawal notification to admin', [
                            'admin_id' => $admin->id,
                            'admin_email' => $admin->email,
                            'transaction_id' => $transaction->id,
                            'error' => $emailError->getMessage()
                        ]);
                    }
                } else {
                    \Log::warning('Withdrawal notification skipped - Admin email not verified', [
                        'admin_id' => $admin->id,
                        'admin_email' => $admin->email ?? 'N/A',
                        'transaction_id' => $transaction->id,
                        'user_id' => $user->id
                    ]);
                }
            }

            $message = 'Withdrawal request of ₱' . number_format($withdrawalAmount, 2) . ' via ' . $paymentMethod . ' has been submitted for approval.';
            if ($withdrawalFee > 0) {
                $message .= ' Processing fee of ₱' . number_format($withdrawalFee, 2) . ' has been deducted from your wallet immediately.';
            }
            $message .= ' You will receive your withdrawal funds once the admin approves your request.';

            session()->flash('success', $message);

            return redirect()->route('wallet.transactions');
        } catch (\Exception $e) {
            \Log::error('Withdrawal request failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['general' => 'Withdrawal request failed. Please try again later.']);
        }
    }

    /**
     * Calculate transfer charge based on system settings
     */
    private function calculateTransferCharge($amount)
    {
        if (!\App\Models\SystemSetting::get('transfer_charge_enabled', false)) {
            return 0;
        }

        $chargeType = \App\Models\SystemSetting::get('transfer_charge_type', 'percentage');
        $chargeValue = \App\Models\SystemSetting::get('transfer_charge_value', 0);
        $minCharge = \App\Models\SystemSetting::get('transfer_minimum_charge', 0);
        $maxCharge = \App\Models\SystemSetting::get('transfer_maximum_charge', 0);

        if ($chargeType === 'percentage') {
            $charge = ($amount * $chargeValue) / 100;
        } else {
            $charge = $chargeValue;
        }

        // Apply minimum limit
        $charge = max($charge, $minCharge);

        // Apply maximum limit (0 means no limit)
        if ($maxCharge > 0) {
            $charge = min($charge, $maxCharge);
        }

        return round($charge, 2);
    }

    /**
     * Calculate withdrawal fee based on system settings
     */
    private function calculateWithdrawalFee($amount)
    {
        if (!\App\Models\SystemSetting::get('withdrawal_fee_enabled', false)) {
            return 0;
        }

        $feeType = \App\Models\SystemSetting::get('withdrawal_fee_type', 'percentage');
        $feeValue = \App\Models\SystemSetting::get('withdrawal_fee_value', 0);
        $minFee = \App\Models\SystemSetting::get('withdrawal_minimum_fee', 0);
        $maxFee = \App\Models\SystemSetting::get('withdrawal_maximum_fee', 0);

        if ($feeType === 'percentage') {
            $fee = ($amount * $feeValue) / 100;
        } else {
            $fee = $feeValue;
        }

        // Apply minimum limit
        $fee = max($fee, $minFee);

        // Apply maximum limit (0 means no limit)
        if ($maxFee > 0) {
            $fee = min($fee, $maxFee);
        }

        return round($fee, 2);
    }

    public function convert()
    {
        $this->authorize('transfer_funds');

        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();

        return view('member.convert', compact('wallet'));
    }

    public function processConvert(Request $request)
    {
        $this->authorize('transfer_funds');

        $request->validate([
            'amount' => 'required|numeric|min:1|max:10000',
        ]);

        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();
        $convertAmount = $request->amount;

        // Check if user has sufficient Withdrawable balance (MLM + Unilevel)
        if ($wallet->withdrawable_balance < $convertAmount) {
            return redirect()->back()->withErrors(['amount' => 'Insufficient Withdrawable Balance. You need ' . currency($convertAmount) . ' but you only have ' . currency($wallet->withdrawable_balance) . ' in your withdrawable balance.']);
        }

        // Check if wallet is active
        if (!$wallet->is_active) {
            return redirect()->back()->withErrors(['general' => 'Your wallet is currently frozen. Please contact support.']);
        }

        try {
            \DB::transaction(function () use ($request, $user, $wallet, $convertAmount) {
                // Lock wallet for update to prevent race conditions
                $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

                // Re-check balance after locking
                if ($wallet->withdrawable_balance < $convertAmount) {
                    throw new \Exception('Insufficient withdrawable balance after lock');
                }

                // Create balance conversion transaction
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'balance_conversion',
                    'amount' => $convertAmount,
                    'status' => 'approved', // Conversions are instant
                    'payment_method' => 'internal',
                    'description' => 'Converted Withdrawable Balance to Purchase Balance',
                    'metadata' => [
                        'from_balance' => 'withdrawable_balance',
                        'to_balance' => 'purchase_balance',
                        'withdrawable_balance_before' => $wallet->withdrawable_balance,
                        'purchase_balance_before' => $wallet->purchase_balance,
                        'withdrawable_balance_after' => $wallet->withdrawable_balance - $convertAmount,
                        'purchase_balance_after' => $wallet->purchase_balance + $convertAmount,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]
                ]);

                // Deduct from Withdrawable balance
                $wallet->decrement('withdrawable_balance', $convertAmount);

                // Add to Purchase balance
                $wallet->increment('purchase_balance', $convertAmount);

                // Update last transaction time
                $wallet->update(['last_transaction_at' => now()]);

                // Log conversion operation
                ActivityLog::logWalletTransaction(
                    event: 'balance_converted',
                    message: sprintf(
                        '%s converted ₱%s from Withdrawable Balance to Purchase Balance',
                        $user->username ?? $user->fullname ?? 'User',
                        number_format($convertAmount, 2)
                    ),
                    transaction: $transaction,
                    level: 'INFO'
                );
            });

            session()->flash('success', 'Successfully converted ' . currency($convertAmount) . ' from Withdrawable Balance to Purchase Balance.');

            return redirect()->route('wallet.convert');
        } catch (\Exception $e) {
            \Log::error('Balance conversion failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['general' => 'Conversion failed. Please try again later.']);
        }
    }

    public function transactions(Request $request)
    {
        $this->authorize('view_transactions');

        $perPage = $this->getPerPage($request, 20);

        // Refresh user to get latest data from database (prevents cache issues after transfers)
        $user = Auth::user()->fresh();

        $transactions = $user->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)->appends($request->query());

        // Force reload wallet from database to get latest balance
        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet) {
            $wallet = $user->getOrCreateWallet();
        }

        return view('member.transactions', compact('transactions', 'wallet', 'perPage'));
    }
}
