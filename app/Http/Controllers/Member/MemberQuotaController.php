<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Services\MonthlyQuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberQuotaController extends Controller
{
    protected $quotaService;

    public function __construct(MonthlyQuotaService $quotaService)
    {
        $this->quotaService = $quotaService;
        
        // Ensure quota system is enabled
        $this->middleware(function ($request, $next) {
            if (!\App\Models\SystemSetting::get('unilevel_quota_enabled', true)) {
                return redirect()->route('dashboard')
                    ->with('error', 'The Monthly Quota System is currently disabled.');
            }
            return $next($request);
        });
    }

    /**
     * Display current month quota status
     */
    public function index()
    {
        $user = Auth::user();
        $status = $this->quotaService->getUserMonthlyStatus($user);
        
        // Get recent orders with PV for current month
        $recentOrders = $user->orders()
            ->where('payment_status', 'paid')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->with(['orderItems.product'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function($order) {
                $pvEarned = $order->orderItems
                    ->filter(fn($item) => $item->isProduct() && $item->product)
                    ->sum(fn($item) => $item->product->points_awarded * $item->quantity);
                
                return [
                    'order_number' => $order->order_number,
                    'date' => $order->created_at,
                    'pv_earned' => $pvEarned,
                    'products' => $order->orderItems
                        ->filter(fn($item) => $item->isProduct() && $item->product)
                        ->map(fn($item) => [
                            'name' => $item->product->name,
                            'quantity' => $item->quantity,
                            'pv' => $item->product->points_awarded * $item->quantity,
                        ])
                ];
            })
            ->filter(fn($order) => $order['pv_earned'] > 0);

        // Calculate days remaining in month
        $daysRemaining = max(0, (int) now()->diffInDays(now()->endOfMonth(), false));

        return view('member.quota.index', compact('status', 'recentOrders', 'daysRemaining'));
    }

    /**
     * Display quota history
     */
    public function history()
    {
        $user = Auth::user();
        $history = $this->quotaService->getUserQuotaHistory($user, 12);

        return view('member.quota.history', compact('history'));
    }
}
