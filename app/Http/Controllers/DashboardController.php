<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();

        // Get recent transactions for the user
        $recentTransactions = $user->transactions()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get transaction statistics
        $totalDeposits = $user->transactions()
            ->where('type', 'deposit')
            ->where('status', 'approved')
            ->sum('amount');

        $totalWithdrawals = $user->transactions()
            ->where('type', 'withdrawal')
            ->where('status', 'approved')
            ->sum('amount');

        $pendingTransactions = $user->transactions()
            ->where('status', 'pending')
            ->count();

        $totalTransactions = $user->transactions()->count();

        // Get points statistics
        $totalPointsEarned = $user->orders()
            ->where('payment_status', 'paid')
            ->sum('points_awarded');

        $totalPointsCredited = $user->orders()
            ->where('payment_status', 'paid')
            ->where('points_credited', true)
            ->sum('points_awarded');

        $pendingPoints = $totalPointsEarned - $totalPointsCredited;

        // Get monthly transaction data for chart
        $monthlyTransactions = $user->transactions()
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(CASE WHEN type = "deposit" AND status = "approved" THEN amount ELSE 0 END) as deposits'),
                DB::raw('SUM(CASE WHEN type = "withdrawal" AND status = "approved" THEN amount ELSE 0 END) as withdrawals'),
                DB::raw('COUNT(*) as count')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        return view('dashboard', compact(
            'user',
            'wallet',
            'recentTransactions',
            'totalDeposits',
            'totalWithdrawals',
            'pendingTransactions',
            'totalTransactions',
            'totalPointsEarned',
            'totalPointsCredited',
            'pendingPoints',
            'monthlyTransactions'
        ));
    }
}