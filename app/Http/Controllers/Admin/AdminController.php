<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\ActivityLog;
use App\Mail\DepositStatusNotification;
use App\Mail\WithdrawalStatusNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    use AuthorizesRequests, \App\Http\Traits\HasPaginationLimit;

    public function dashboard()
    {
        $userCount = User::count();
        $adminCount = User::role('admin')->count();
        $memberCount = User::role('member')->count();
        $roleCount = Role::count();
        $permissionCount = Permission::count();

        // Financial statistics
        $totalBalance = Wallet::sum('mlm_balance') + Wallet::sum('purchase_balance');
        $pendingTransactions = Transaction::where('status', 'pending')->count();
        $todayTransactions = Transaction::whereDate('created_at', today())->count();
        $approvedTransactions = Transaction::where('status', 'approved')->count();

        // Transaction statistics
        $totalDeposits = Transaction::where('type', 'deposit')->where('status', 'approved')->sum('amount');
        $totalWithdrawals = Transaction::where('type', 'withdrawal')->where('status', 'approved')->sum('amount');
        $monthlyVolume = Transaction::where('status', 'approved')
            ->whereMonth('approved_at', now()->month)
            ->sum('amount');

        // Recent activity
        $recentTransactions = Transaction::with('user')
            ->latest()
            ->limit(5)
            ->get();

        $recentUsers = User::latest()
            ->limit(5)
            ->get();

        $breadcrumbs = [
            ['title' => 'Admin Dashboard'],
        ];

        return view('admin.dashboard', compact(
            'userCount',
            'adminCount',
            'memberCount',
            'roleCount',
            'permissionCount',
            'totalBalance',
            'pendingTransactions',
            'todayTransactions',
            'approvedTransactions',
            'totalDeposits',
            'totalWithdrawals',
            'monthlyVolume',
            'recentTransactions',
            'recentUsers',
            'breadcrumbs'
        ));
    }

    public function walletManagement(Request $request)
    {
        $this->authorize('wallet_management');

        $perPage = $this->getPerPage($request, 20);

        $wallets = User::with(['wallet', 'transactions' => function($query) {
            $query->latest()->limit(5);
        }])->whereHas('roles', function($query) {
            $query->where('name', 'member');
        })->paginate($perPage)->appends($request->query());

        $totalBalance = Wallet::sum('mlm_balance') + Wallet::sum('purchase_balance');
        $totalWallets = Wallet::count();
        $activeWallets = Wallet::where('is_active', true)->count();
        $recentTransactions = Transaction::with('user')->latest()->limit(10)->get();

        $todayDeposits = Transaction::where('type', 'deposit')
            ->where('status', 'approved')
            ->whereDate('approved_at', today())
            ->sum('amount');

        $todayWithdrawals = Transaction::where('type', 'withdrawal')
            ->where('status', 'approved')
            ->whereDate('approved_at', today())
            ->sum('amount');

        $pendingTransactions = Transaction::where('status', 'pending')->count();
        $approvedTransactions = Transaction::where('status', 'approved')->count();
        $rejectedTransactions = Transaction::where('status', 'rejected')->count();

        // Get filter parameters
        $statusFilter = $request->get('status', 'all');
        $typeFilter = $request->get('type', 'all');

        // Build transaction query with filters
        $transactionsQuery = Transaction::with(['user', 'user.wallet']);

        if ($statusFilter !== 'all') {
            $transactionsQuery->where('status', $statusFilter);
        }

        if ($typeFilter !== 'all') {
            $transactionsQuery->where('type', $typeFilter);
        }

        // Get paginated transactions
        $allTransactions = $transactionsQuery->latest()->paginate($perPage)->appends($request->query());

        $breadcrumbs = [
            ['title' => 'Management'],
            ['title' => 'Wallet Management'],
        ];

        return view('admin.wallet-management', compact(
            'wallets',
            'totalBalance',
            'totalWallets',
            'activeWallets',
            'recentTransactions',
            'todayDeposits',
            'todayWithdrawals',
            'pendingTransactions',
            'approvedTransactions',
            'rejectedTransactions',
            'allTransactions',
            'statusFilter',
            'typeFilter',
            'perPage',
            'breadcrumbs'
        ));
    }

    public function transactionApproval(Request $request)
    {
        $this->authorize('transaction_approval');

        $perPage = $this->getPerPage($request, 20);

        $pendingTransactions = Transaction::with(['user', 'approver'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)->appends($request->query());

        $breadcrumbs = [
            ['title' => 'Management'],
            ['title' => 'Transaction Approval'],
        ];

        return view('admin.transaction-approval', compact('pendingTransactions', 'perPage', 'breadcrumbs'));
    }

    public function systemSettings()
    {
        $this->authorize('system_settings');

        // System configuration data
        $systemStats = [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'database_size' => $this->getDatabaseSize(),
            'total_users' => User::count(),
            'total_transactions' => Transaction::count(),
            'total_wallets' => Wallet::count(),
            'system_uptime' => $this->getSystemUptime(),
            'storage_used' => $this->getStorageUsed(),
            'cache_status' => $this->getCacheStatus(),
        ];

        // Security settings
        $securitySettings = [
            'two_factor_enabled' => User::whereNotNull('two_factor_secret')->count(),
            'email_verified_users' => User::whereNotNull('email_verified_at')->count(),
            'failed_login_attempts' => 0, // Would be from logs
            'active_sessions' => 0, // Would be from sessions table
        ];

        // Transaction limits and settings
        $transactionSettings = [
            'min_deposit' => 1.00,
            'max_deposit' => 10000.00,
            'min_withdrawal' => 1.00,
            'max_withdrawal' => 10000.00,
            'daily_limit' => 50000.00,
            'require_approval' => true,
        ];

        // Get system settings
        $settings = \App\Models\SystemSetting::all()->keyBy('key');

        // Payment methods settings
        $paymentSettings = [
            'gcash_enabled' => \App\Models\SystemSetting::get('gcash_enabled', true),
            'gcash_number' => \App\Models\SystemSetting::get('gcash_number', ''),
            'gcash_name' => \App\Models\SystemSetting::get('gcash_name', ''),
            'maya_enabled' => \App\Models\SystemSetting::get('maya_enabled', true),
            'maya_number' => \App\Models\SystemSetting::get('maya_number', ''),
            'maya_name' => \App\Models\SystemSetting::get('maya_name', ''),
            'cash_enabled' => \App\Models\SystemSetting::get('cash_enabled', true),
            'others_enabled' => \App\Models\SystemSetting::get('others_enabled', true),
        ];

        return view('admin.system-settings', compact(
            'systemStats',
            'securitySettings',
            'transactionSettings',
            'settings',
            'paymentSettings'
        ));
    }

    private function getDatabaseSize()
    {
        try {
            $result = \DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = DATABASE()");
            return isset($result[0]->size_mb) ? $result[0]->size_mb . ' MB' : 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    private function getSystemUptime()
    {
        try {
            if (function_exists('sys_getloadavg')) {
                return 'Available';
            }
            return 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    private function getStorageUsed()
    {
        try {
            $bytes = disk_free_space(storage_path());
            return $bytes ? round($bytes / 1024 / 1024 / 1024, 2) . ' GB free' : 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    private function getCacheStatus()
    {
        try {
            \Cache::put('test_key', 'test_value', 60);
            $status = \Cache::get('test_key') === 'test_value' ? 'Working' : 'Error';
            \Cache::forget('test_key');
            return $status;
        } catch (\Exception $e) {
            return 'Error';
        }
    }

    public function users(Request $request)
    {
        $perPage = $this->getPerPage($request, 15);
        $users = User::with(['roles', 'wallet', 'rankPackage'])->paginate($perPage)->appends($request->query());

        $breadcrumbs = [
            ['title' => 'Management'],
            ['title' => 'User Management'],
        ];

        return view('admin.users', compact('users', 'perPage', 'breadcrumbs'));
    }

    public function editUser(User $user)
    {
        return view('admin.users-edit', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'fullname' => 'nullable|string|max:255',
            'role' => 'required|exists:roles,name',
        ]);

        try {
            DB::beginTransaction();

            // Update user details
            $user->update([
                'username' => $request->username,
                'email' => $request->email,
                'fullname' => $request->fullname,
            ]);

            // Update role (remove all existing roles and assign new one)
            $user->syncRoles([$request->role]);

            DB::commit();

            return redirect()->route('admin.users')
                ->with('success', 'User updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating user: ' . $e->getMessage());
        }
    }

    public function suspendUser(User $user)
    {
        try {
            DB::beginTransaction();

            // Check if user is admin
            if ($user->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot suspend admin users'
                ], 403);
            }

            // Mark user as suspended
            $user->update(['suspended_at' => now()]);

            // Suspend user's wallet
            if ($user->wallet) {
                $user->wallet->update(['is_active' => false]);
            }

            // Terminate all active sessions for this user
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User account suspended successfully. They have been logged out and will not be able to login.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error suspending user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function activateUser(User $user)
    {
        try {
            DB::beginTransaction();

            // Remove suspension
            $user->update(['suspended_at' => null]);

            // Activate user's wallet
            if ($user->wallet) {
                $user->wallet->update(['is_active' => true]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User account activated successfully. They can now login.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error activating user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approveTransaction(Request $request, $id)
    {
        $this->authorize('transaction_approval');

        $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::with('user')->findOrFail($id);

            if ($transaction->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction is not pending approval'
                ], 400);
            }

            // Update transaction status
            $transaction->update([
                'status' => 'approved',
                'approved_by' => auth()->user()->id,
                'approved_at' => now(),
                'admin_notes' => $request->notes
            ]);

            // Update wallet balance for deposits
            if ($transaction->type === 'deposit') {
                $wallet = $transaction->user->getOrCreateWallet();
                $wallet->addBalance($transaction->amount);
            }

            // Update wallet balance for approved withdrawals
            if ($transaction->type === 'withdrawal') {
                $wallet = $transaction->user->getOrCreateWallet();
                // Deduct the withdrawal amount from MLM balance only (fee was already deducted upon submission)
                // IMPORTANT: Only MLM balance can be withdrawn (purchase balance from deposits/transfers cannot be withdrawn)
                $wallet->decrement('mlm_balance', $transaction->amount);
                $wallet->update(['last_transaction_at' => now()]);
            }

            // Note: For withdrawals, fee transactions are already auto-approved during request
            // Only the withdrawal amount is deducted upon approval

            // Log transaction approval
            ActivityLog::logWalletTransaction(
                event: $transaction->type === 'deposit' ? 'deposit_approved' : 'withdrawal_approved',
                message: sprintf('Admin approved %s of ₱%s for %s (Ref: %s)',
                    $transaction->type,
                    number_format($transaction->amount, 2),
                    $transaction->user->username ?? $transaction->user->fullname ?? 'User',
                    $transaction->reference_number
                ),
                transaction: $transaction,
                level: 'INFO'
            );

            DB::commit();

            // Send email notification to user based on transaction type (only if email verified)
            $user = $transaction->user;

            if ($user->hasVerifiedEmail()) {
                if ($transaction->type === 'deposit') {
                    Mail::to($user->email)->send(
                        new DepositStatusNotification($transaction, $user, 'approved', $request->notes)
                    );
                    \Log::info('Deposit approval notification sent', [
                        'transaction_id' => $transaction->id,
                        'user_id' => $user->id,
                        'user_email' => $user->email
                    ]);
                } elseif ($transaction->type === 'withdrawal') {
                    Mail::to($user->email)->send(
                        new WithdrawalStatusNotification($transaction, $user, 'approved', $request->notes)
                    );
                    \Log::info('Withdrawal approval notification sent', [
                        'transaction_id' => $transaction->id,
                        'user_id' => $user->id,
                        'user_email' => $user->email
                    ]);
                }
            } else {
                \Log::warning('Transaction approval notification skipped - User email not verified', [
                    'transaction_id' => $transaction->id,
                    'transaction_type' => $transaction->type,
                    'user_id' => $user->id,
                    'user_name' => $user->fullname ?? $user->username,
                    'user_email' => $user->email ?? 'N/A'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaction approved successfully and wallet balance updated',
                'transaction' => $transaction->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error approving transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rejectTransaction(Request $request, $id)
    {
        $this->authorize('transaction_approval');

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::with('user')->findOrFail($id);

            if ($transaction->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction is not pending approval'
                ], 400);
            }

            // Update transaction status
            $transaction->update([
                'status' => 'rejected',
                'approved_by' => auth()->user()->id,
                'approved_at' => now(),
                'admin_notes' => $request->reason
            ]);

            // For withdrawals, return ONLY the withdrawal amount to MLM balance (fee is non-refundable)
            if ($transaction->type === 'withdrawal') {
                $wallet = $transaction->user->getOrCreateWallet();

                // Return only the withdrawal amount to MLM balance - fee is non-refundable
                // IMPORTANT: Refund to MLM balance since withdrawal was requested from MLM balance
                $wallet->increment('mlm_balance', $transaction->amount);
                $wallet->update(['last_transaction_at' => now()]);

                // Note: Withdrawal fee remains deducted as it's a processing fee (non-refundable)
                // The fee transaction is already marked as 'approved' during withdrawal request
            }

            // Log transaction rejection
            ActivityLog::logWalletTransaction(
                event: $transaction->type === 'deposit' ? 'deposit_rejected' : 'withdrawal_rejected',
                message: sprintf('Admin rejected %s of ₱%s for %s (Ref: %s) - Reason: %s',
                    $transaction->type,
                    number_format($transaction->amount, 2),
                    $transaction->user->username ?? $transaction->user->fullname ?? 'User',
                    $transaction->reference_number,
                    $request->reason
                ),
                transaction: $transaction,
                level: 'WARNING'
            );

            DB::commit();

            // Send email notification to user based on transaction type (only if email verified)
            $user = $transaction->user;

            if ($user->hasVerifiedEmail()) {
                if ($transaction->type === 'deposit') {
                    Mail::to($user->email)->send(
                        new DepositStatusNotification($transaction, $user, 'rejected', $request->reason)
                    );
                    \Log::info('Deposit rejection notification sent', [
                        'transaction_id' => $transaction->id,
                        'user_id' => $user->id,
                        'user_email' => $user->email
                    ]);
                } elseif ($transaction->type === 'withdrawal') {
                    Mail::to($user->email)->send(
                        new WithdrawalStatusNotification($transaction, $user, 'rejected', $request->reason)
                    );
                    \Log::info('Withdrawal rejection notification sent', [
                        'transaction_id' => $transaction->id,
                        'user_id' => $user->id,
                        'user_email' => $user->email
                    ]);
                }
            } else {
                \Log::warning('Transaction rejection notification skipped - User email not verified', [
                    'transaction_id' => $transaction->id,
                    'transaction_type' => $transaction->type,
                    'user_id' => $user->id,
                    'user_name' => $user->fullname ?? $user->username,
                    'user_email' => $user->email ?? 'N/A'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaction rejected successfully' . ($transaction->type === 'withdrawal' ? ' and amount returned to wallet' : ''),
                'transaction' => $transaction->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkApproval(Request $request)
    {
        $this->authorize('transaction_approval');

        $request->validate([
            'transaction_ids' => 'required|array',
            'transaction_ids.*' => 'integer',
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:500'
        ]);

        $count = count($request->transaction_ids);
        $action = $request->action;

        // Simulate bulk processing
        foreach ($request->transaction_ids as $id) {
            // Process each transaction
        }

        return response()->json([
            'success' => true,
            'message' => ucfirst($action) . "d {$count} transactions successfully",
            'count' => $count
        ]);
    }

    public function blockTransaction(Request $request, $id)
    {
        $this->authorize('transaction_approval');

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        // Simulate transaction blocking and user account action
        return response()->json([
            'success' => true,
            'message' => 'Transaction blocked and user flagged for review',
            'transaction_id' => $id
        ]);
    }



    public function exportTransactionReport(Request $request)
    {
        $this->authorize('transaction_approval');

        $request->validate([
            'status' => 'nullable|in:pending,approved,rejected',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from'
        ]);

        // Simulate report generation
        $reportData = [
            'filename' => 'transaction_report_' . now()->format('Y_m_d_H_i_s') . '.csv',
            'url' => '/admin/reports/transactions/download/' . uniqid(),
            'generated_at' => now()->toISOString()
        ];

        return response()->json([
            'success' => true,
            'message' => 'Report generated successfully',
            'report' => $reportData
        ]);
    }

    public function viewLogs(Request $request)
    {
        $this->authorize('system_settings');

        // Get log type from request, default to 'all'
        $logType = $request->get('type', 'all');
        $search = $request->get('search', '');
        $level = $request->get('level', 'all');

        // Query real activity logs from database
        $query = ActivityLog::with(['user', 'transaction', 'order', 'relatedUser'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($logType !== 'all') {
            $query->where('type', $logType);
        }

        if ($level !== 'all') {
            $query->where('level', $level);
        }

        if (!empty($search)) {
            $query->search($search);
        }

        // Get paginated logs
        $perPage = $this->getPerPage($request, 15);
        $activityLogs = $query->paginate($perPage)->appends($request->query());

        return view('admin.logs', compact('activityLogs', 'logType', 'search', 'level', 'perPage'));
    }

    public function exportLogs(Request $request)
    {
        $this->authorize('system_settings');

        $request->validate([
            'format' => 'required|in:csv,json',
            'type' => 'nullable|string',
            'level' => 'nullable|string',
            'search' => 'nullable|string'
        ]);

        // Query real activity logs from database with same filters
        $query = ActivityLog::with(['user', 'transaction', 'order', 'relatedUser'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        $logType = $request->get('type', 'all');
        $level = $request->get('level', 'all');
        $search = $request->get('search', '');

        if ($logType !== 'all') {
            $query->where('type', $logType);
        }

        if ($level !== 'all') {
            $query->where('level', $level);
        }

        if (!empty($search)) {
            $query->search($search);
        }

        // Get logs (up to 10000 for export)
        $activityLogs = $query->limit(10000)->get();

        // Transform for export
        $logs = $activityLogs->map(function($log) {
            return [
                'id' => $log->id,
                'timestamp' => $log->created_at,
                'level' => $log->level,
                'type' => $log->type,
                'event' => $log->event,
                'message' => $log->message,
                'user_id' => $log->user_id,
                'ip_address' => $log->ip_address ?? 'N/A',
                'user_agent' => $log->user_agent ?? 'N/A',
                'metadata' => $log->metadata,
                'transaction_id' => $log->transaction_id,
                'order_id' => $log->order_id,
                'related_user_id' => $log->related_user_id,
            ];
        });

        $logs = $logs->values();
        $format = $request->get('format');
        $timestamp = now()->format('Y-m-d_H-i-s');

        if ($format === 'csv') {
            return $this->exportLogsAsCSV($logs, $timestamp);
        } else {
            return $this->exportLogsAsJSON($logs, $timestamp);
        }
    }

    private function exportLogsAsCSV($logs, $timestamp)
    {
        $filename = "system_logs_{$timestamp}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];

        return response()->stream(function () use ($logs) {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, [
                'ID',
                'Timestamp',
                'Level',
                'Type',
                'Message',
                'User ID',
                'IP Address',
                'User Agent'
            ]);

            // Add data rows
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log['id'],
                    $log['timestamp']->format('Y-m-d H:i:s'),
                    $log['level'],
                    $log['type'],
                    $log['message'],
                    $log['user_id'] ?? '',
                    $log['ip_address'],
                    $log['user_agent']
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function exportLogsAsJSON($logs, $timestamp)
    {
        $filename = "system_logs_{$timestamp}.json";

        // Format logs for JSON export
        $exportData = [
            'export_info' => [
                'exported_at' => now()->toISOString(),
                'total_records' => $logs->count(),
                'export_format' => 'json'
            ],
            'logs' => $logs->map(function ($log) {
                return [
                    'id' => $log['id'],
                    'timestamp' => $log['timestamp']->toISOString(),
                    'level' => $log['level'],
                    'type' => $log['type'],
                    'message' => $log['message'],
                    'user_id' => $log['user_id'],
                    'ip_address' => $log['ip_address'],
                    'user_agent' => $log['user_agent']
                ];
            })->values()
        ];

        return response()->json($exportData)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function clearOldLogs(Request $request)
    {
        $this->authorize('system_settings');

        $request->validate([
            'days' => 'nullable|integer|min:1|max:365'
        ]);

        $days = $request->get('days', 30);
        $cutoffDate = now()->subDays($days);

        // Delete old activity logs from database
        $deletedCount = ActivityLog::where('created_at', '<', $cutoffDate)->delete();

        return response()->json([
            'success' => true,
            'message' => "Successfully cleared {$deletedCount} log entries older than {$days} days.",
            'deleted_count' => $deletedCount,
            'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s')
        ]);
    }

    public function reports()
    {
        $this->authorize('system_settings');

        // Generate report statistics
        $stats = [
            'total_users' => User::count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)->count(),
            'total_transactions' => Transaction::count(),
            'total_volume' => Transaction::where('status', 'approved')->sum('amount'),
            'pending_transactions' => Transaction::where('status', 'pending')->count(),
            'approved_transactions' => Transaction::where('status', 'approved')->count(),
            'rejected_transactions' => Transaction::where('status', 'rejected')->count(),
        ];

        return view('admin.reports', compact('stats'));
    }

    public function generateReport(Request $request)
    {
        $this->authorize('system_settings');

        $request->validate([
            'report_type' => 'required|in:users,transactions,financial,security',
            'date_range' => 'required|in:today,week,month,quarter,year,custom',
            'format' => 'required|in:pdf,csv,excel',
            'date_from' => 'nullable|date|required_if:date_range,custom',
            'date_to' => 'nullable|date|after_or_equal:date_from|required_if:date_range,custom'
        ]);

        // Simulate report generation based on type
        $reportData = $this->generateReportData($request->report_type, $request->date_range, $request->date_from, $request->date_to);

        $filename = $this->generateReportFilename($request->report_type, $request->format);

        return response()->json([
            'success' => true,
            'message' => 'Report generated successfully',
            'report' => [
                'filename' => $filename,
                'download_url' => '/admin/reports/download/' . uniqid(),
                'generated_at' => now()->toISOString(),
                'size' => $this->calculateReportSize($reportData),
                'records' => count($reportData)
            ]
        ]);
    }

    private function generateReportData($type, $dateRange, $dateFrom = null, $dateTo = null)
    {
        // Simulate different report types
        switch ($type) {
            case 'users':
                return [
                    ['name' => 'John Doe', 'email' => 'john@example.com', 'created_at' => '2024-01-15', 'status' => 'Active'],
                    ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'created_at' => '2024-01-20', 'status' => 'Active'],
                    ['name' => 'Bob Johnson', 'email' => 'bob@example.com', 'created_at' => '2024-02-01', 'status' => 'Inactive'],
                ];
            case 'transactions':
                return [
                    ['id' => 1, 'type' => 'Deposit', 'amount' => 500.00, 'status' => 'Completed', 'date' => '2024-01-15'],
                    ['id' => 2, 'type' => 'Withdrawal', 'amount' => 150.00, 'status' => 'Completed', 'date' => '2024-01-16'],
                    ['id' => 3, 'type' => 'Transfer', 'amount' => 200.00, 'status' => 'Pending', 'date' => '2024-01-17'],
                ];
            case 'financial':
                return [
                    ['metric' => 'Total Volume', 'value' => 45678.90, 'change' => '+12.5%'],
                    ['metric' => 'Total Fees', 'value' => 228.39, 'change' => '+15.2%'],
                    ['metric' => 'Average Transaction', 'value' => 293.07, 'change' => '-2.1%'],
                ];
            case 'security':
                return [
                    ['event' => 'Failed Login', 'count' => 15, 'severity' => 'Medium', 'date' => '2024-01-15'],
                    ['event' => 'Suspicious Transaction', 'count' => 3, 'severity' => 'High', 'date' => '2024-01-16'],
                    ['event' => 'Account Lockout', 'count' => 2, 'severity' => 'Low', 'date' => '2024-01-17'],
                ];
            default:
                return [];
        }
    }

    private function generateReportFilename($type, $format)
    {
        $timestamp = now()->format('Y_m_d_H_i_s');
        return "{$type}_report_{$timestamp}.{$format}";
    }

    private function calculateReportSize($data)
    {
        // Simulate file size calculation
        $baseSize = count($data) * 150; // ~150 bytes per record
        return number_format($baseSize / 1024, 1) . ' KB';
    }

    public function downloadReport($reportId)
    {
        $this->authorize('system_settings');

        // In a real implementation, this would serve the actual file
        return response()->json([
            'success' => false,
            'message' => 'This is a demo implementation. In production, this would serve the actual report file.',
            'report_id' => $reportId
        ]);
    }

    public function updateSystemSettings(Request $request)
    {
        $this->authorize('system_settings');

        $request->validate([
            'email_verification_enabled' => 'boolean',
            'require_2fa' => 'boolean',
            'fraud_protection_enabled' => 'boolean',
            'maintenance_mode' => 'boolean',
            'session_timeout' => 'boolean',
            'session_timeout_minutes' => 'integer|min:5|max:1440',
            'max_login_attempts' => 'integer|min:1|max:10',
            'lockout_duration' => 'integer|min:1|max:1440',
            // Notification settings validation
            'notify_new_user' => 'boolean',
            'notify_large_transaction' => 'boolean',
            'notify_suspicious' => 'boolean',
            'admin_email' => 'nullable|email|max:255',
            'transaction_review_threshold' => 'numeric|min:0',
            // General settings validation
            'app_name' => 'nullable|string|max:255',
            'app_url' => 'nullable|url|max:255',
            'app_description' => 'nullable|string|max:1000',
            'timezone' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:10',
            'app_env' => 'nullable|in:local,staging,production',
            'fallback_language' => 'nullable|string|max:10',
            'app_debug' => 'boolean',
            // Wallet limits validation
            'min_deposit' => 'numeric|min:0',
            'max_deposit' => 'numeric|min:0',
            'min_withdrawal' => 'numeric|min:0',
            'max_withdrawal' => 'numeric|min:0',
            'transfer_fee_enabled' => 'boolean',
            'transfer_fee_type' => 'in:percentage,fixed',
            'transfer_fee_value' => 'numeric|min:0',
            'transfer_minimum_fee' => 'numeric|min:0',
            'transfer_maximum_fee' => 'numeric|min:0',
            'withdrawal_fee_enabled' => 'boolean',
            'withdrawal_fee_type' => 'in:percentage,fixed',
            'withdrawal_fee_value' => 'numeric|min:0',
            'withdrawal_minimum_fee' => 'numeric|min:0',
            'withdrawal_maximum_fee' => 'numeric|min:0',
            // Payment methods validation
            'gcash_enabled' => 'boolean',
            'gcash_number' => 'nullable|string|max:20',
            'gcash_name' => 'nullable|string|max:100',
            'maya_enabled' => 'boolean',
            'maya_number' => 'nullable|string|max:20',
            'maya_name' => 'nullable|string|max:100',
            'cash_enabled' => 'boolean',
            'others_enabled' => 'boolean',
        ]);

        // Update general settings
        if ($request->has('app_name')) {
            \App\Models\SystemSetting::set('app_name', $request->input('app_name', config('app.name')), 'string', 'Application name');
        }
        if ($request->has('app_url')) {
            \App\Models\SystemSetting::set('app_url', $request->input('app_url', config('app.url')), 'string', 'Application URL');
        }
        if ($request->has('app_description')) {
            \App\Models\SystemSetting::set('app_description', $request->input('app_description', 'Digital wallet platform'), 'string', 'Application description');
        }
        if ($request->has('timezone')) {
            \App\Models\SystemSetting::set('timezone', $request->input('timezone', config('app.timezone')), 'string', 'System timezone');
        }
        if ($request->has('language')) {
            \App\Models\SystemSetting::set('language', $request->input('language', config('app.locale')), 'string', 'Default language');
        }
        if ($request->has('app_env')) {
            \App\Models\SystemSetting::set('app_env', $request->input('app_env', config('app.env')), 'string', 'Application environment');
        }
        if ($request->has('fallback_language')) {
            \App\Models\SystemSetting::set('fallback_language', $request->input('fallback_language', config('app.fallback_locale')), 'string', 'Fallback language');
        }
        if ($request->has('app_debug')) {
            \App\Models\SystemSetting::set('app_debug', $request->boolean('app_debug'), 'boolean', 'Enable debug mode');
        }

        // Update security settings
        if ($request->has('email_verification_enabled')) {
            \App\Models\SystemSetting::set('email_verification_enabled', $request->boolean('email_verification_enabled'), 'boolean', 'Enable email verification for new users');
        }
        if ($request->has('require_2fa')) {
            \App\Models\SystemSetting::set('require_2fa', $request->boolean('require_2fa'), 'boolean', 'Require two-factor authentication for all users');
        }
        if ($request->has('fraud_protection_enabled')) {
            \App\Models\SystemSetting::set('fraud_protection_enabled', $request->boolean('fraud_protection_enabled'), 'boolean', 'Enable or disable the fraud protection system for checkouts.');
        }
        if ($request->has('session_timeout')) {
            \App\Models\SystemSetting::set('session_timeout', $request->boolean('session_timeout'), 'boolean', 'Enable automatic session timeout');
        }
        if ($request->has('session_timeout_minutes')) {
            \App\Models\SystemSetting::set('session_timeout_minutes', $request->input('session_timeout_minutes', 15), 'integer', 'Session timeout duration in minutes');
        }
        if ($request->has('max_login_attempts')) {
            \App\Models\SystemSetting::set('max_login_attempts', $request->input('max_login_attempts', 3), 'integer', 'Maximum login attempts before lockout');
        }
        if ($request->has('lockout_duration')) {
            \App\Models\SystemSetting::set('lockout_duration', $request->input('lockout_duration', 15), 'integer', 'Account lockout duration in minutes');
        }
        if ($request->has('maintenance_mode')) {
            \App\Models\SystemSetting::set('maintenance_mode', $request->boolean('maintenance_mode'), 'boolean', 'Enable maintenance mode');
        }

        // Update notification settings
        if ($request->has('notify_new_user')) {
            \App\Models\SystemSetting::set('notify_new_user', $request->boolean('notify_new_user'), 'boolean', 'Notify admins of new user registrations');
        }
        if ($request->has('notify_large_transaction')) {
            \App\Models\SystemSetting::set('notify_large_transaction', $request->boolean('notify_large_transaction'), 'boolean', 'Notify admins of large transactions');
        }
        if ($request->has('notify_suspicious')) {
            \App\Models\SystemSetting::set('notify_suspicious', $request->boolean('notify_suspicious'), 'boolean', 'Notify admins of suspicious activity');
        }
        if ($request->has('admin_email')) {
            \App\Models\SystemSetting::set('admin_email', $request->input('admin_email', 'admin@example.com'), 'string', 'Primary admin email for notifications');
        }
        if ($request->has('transaction_review_threshold')) {
            \App\Models\SystemSetting::set('transaction_review_threshold', $request->input('transaction_review_threshold', 1000), 'string', 'Transaction amount threshold for admin notifications');
        }

        // Update wallet limits settings
        if ($request->has('min_deposit')) {
            \App\Models\SystemSetting::set('min_deposit', $request->input('min_deposit', 1.00), 'string', 'Minimum deposit amount');
        }
        if ($request->has('max_deposit')) {
            \App\Models\SystemSetting::set('max_deposit', $request->input('max_deposit', 10000.00), 'string', 'Maximum deposit amount');
        }
        if ($request->has('min_withdrawal')) {
            \App\Models\SystemSetting::set('min_withdrawal', $request->input('min_withdrawal', 1.00), 'string', 'Minimum withdrawal amount');
        }
        if ($request->has('max_withdrawal')) {
            \App\Models\SystemSetting::set('max_withdrawal', $request->input('max_withdrawal', 10000.00), 'string', 'Maximum withdrawal amount');
        }

        // Update transfer fee settings (mapping to existing backend settings)
        if ($request->has('transfer_fee_enabled')) {
            \App\Models\SystemSetting::set('transfer_charge_enabled', $request->boolean('transfer_fee_enabled'), 'boolean', 'Enable transfer fees');
        }
        if ($request->has('transfer_fee_type')) {
            \App\Models\SystemSetting::set('transfer_charge_type', $request->input('transfer_fee_type', 'percentage'), 'string', 'Transfer fee type (percentage or fixed)');
        }
        if ($request->has('transfer_fee_value')) {
            \App\Models\SystemSetting::set('transfer_charge_value', $request->input('transfer_fee_value', 0), 'string', 'Transfer fee value');
        }
        if ($request->has('transfer_minimum_fee')) {
            \App\Models\SystemSetting::set('transfer_minimum_charge', $request->input('transfer_minimum_fee', 0), 'string', 'Minimum transfer fee');
        }
        if ($request->has('transfer_maximum_fee')) {
            \App\Models\SystemSetting::set('transfer_maximum_charge', $request->input('transfer_maximum_fee', 999999), 'string', 'Maximum transfer fee');
        }

        // Update withdrawal fee settings
        if ($request->has('withdrawal_fee_enabled')) {
            \App\Models\SystemSetting::set('withdrawal_fee_enabled', $request->boolean('withdrawal_fee_enabled'), 'boolean', 'Enable withdrawal fees');
        }
        if ($request->has('withdrawal_fee_type')) {
            \App\Models\SystemSetting::set('withdrawal_fee_type', $request->input('withdrawal_fee_type', 'percentage'), 'string', 'Withdrawal fee type (percentage or fixed)');
        }
        if ($request->has('withdrawal_fee_value')) {
            \App\Models\SystemSetting::set('withdrawal_fee_value', $request->input('withdrawal_fee_value', 0), 'string', 'Withdrawal fee value');
        }
        if ($request->has('withdrawal_minimum_fee')) {
            \App\Models\SystemSetting::set('withdrawal_minimum_fee', $request->input('withdrawal_minimum_fee', 0), 'string', 'Minimum withdrawal fee');
        }
        if ($request->has('withdrawal_maximum_fee')) {
            \App\Models\SystemSetting::set('withdrawal_maximum_fee', $request->input('withdrawal_maximum_fee', 999999), 'string', 'Maximum withdrawal fee');
        }

        // Update payment method settings
        if ($request->has('gcash_enabled')) {
            \App\Models\SystemSetting::set('gcash_enabled', $request->boolean('gcash_enabled'), 'boolean', 'Enable Gcash payment method');
        }
        if ($request->filled('gcash_number')) {
            \App\Models\SystemSetting::set('gcash_number', $request->input('gcash_number'), 'string', 'Gcash account number');
        }
        if ($request->filled('gcash_name')) {
            \App\Models\SystemSetting::set('gcash_name', $request->input('gcash_name'), 'string', 'Gcash account name');
        }
        if ($request->has('maya_enabled')) {
            \App\Models\SystemSetting::set('maya_enabled', $request->boolean('maya_enabled'), 'boolean', 'Enable Maya payment method');
        }
        if ($request->filled('maya_number')) {
            \App\Models\SystemSetting::set('maya_number', $request->input('maya_number'), 'string', 'Maya account number');
        }
        if ($request->filled('maya_name')) {
            \App\Models\SystemSetting::set('maya_name', $request->input('maya_name'), 'string', 'Maya account name');
        }
        if ($request->has('cash_enabled')) {
            \App\Models\SystemSetting::set('cash_enabled', $request->boolean('cash_enabled'), 'boolean', 'Enable Cash payment method');
        }
        if ($request->has('others_enabled')) {
            \App\Models\SystemSetting::set('others_enabled', $request->boolean('others_enabled'), 'boolean', 'Allow custom payment methods');
        }

        return response()->json([
            'success' => true,
            'message' => 'System settings updated successfully.'
        ]);
    }

    /**
     * Test notification system
     */
    public function testNotification(Request $request)
    {
        $this->authorize('system_settings');

        try {
            $success = \App\Services\NotificationService::testNotification();

            if ($success) {
                return response()->json(['success' => true, 'message' => 'Test notification sent successfully']);
            } else {
                return response()->json(['success' => false, 'message' => 'Failed to send test notification']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function getTransactionStats()
    {
        $this->authorize('transaction_approval');

        $pendingTransactions = Transaction::where('status', 'pending')->get();

        return response()->json([
            'success' => true,
            'pending_count' => $pendingTransactions->count(),
            'approved_today' => Transaction::where('status', 'approved')->whereDate('approved_at', today())->count(),
            'rejected_today' => Transaction::where('status', 'rejected')->whereDate('approved_at', today())->count(),
            'total_value' => number_format($pendingTransactions->sum('amount'), 2)
        ]);
    }

    public function getTransactionDetails($id)
    {
        $this->authorize('transaction_approval');

        $transaction = Transaction::with(['user', 'user.wallet'])->find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'transaction' => [
                'id' => $transaction->id,
                'reference_number' => $transaction->reference_number,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'payment_method' => $transaction->payment_method,
                'status' => $transaction->status,
                'description' => $transaction->description,
                'created_at' => $transaction->created_at->toISOString(),
                'user' => [
                    'id' => $transaction->user->id,
                    'username' => $transaction->user->username,
                    'fullname' => $transaction->user->fullname,
                    'email' => $transaction->user->email,
                    'wallet' => $transaction->user->wallet ? [
                        'mlm_balance' => $transaction->user->wallet->mlm_balance,
                        'purchase_balance' => $transaction->user->wallet->purchase_balance,
                        'total_balance' => $transaction->user->wallet->total_balance
                    ] : null
                ]
            ]
        ]);
    }

}