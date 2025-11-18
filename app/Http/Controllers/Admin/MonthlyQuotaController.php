<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Package;
use App\Models\User;
use App\Models\MonthlyQuotaTracker;
use App\Services\MonthlyQuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonthlyQuotaController extends Controller
{
    protected MonthlyQuotaService $quotaService;

    public function __construct(MonthlyQuotaService $quotaService)
    {
        $this->quotaService = $quotaService;
    }

    /**
     * Dashboard overview
     */
    public function index()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Statistics
        $stats = [
            'total_active_users' => User::where('network_status', 'active')->count(),
            'users_met_quota' => MonthlyQuotaTracker::where('year', $currentYear)
                ->where('month', $currentMonth)
                ->where('quota_met', true)
                ->count(),
            'users_not_met_quota' => MonthlyQuotaTracker::where('year', $currentYear)
                ->where('month', $currentMonth)
                ->where('quota_met', false)
                ->count(),
            'total_products_with_pv' => Product::where('points_awarded', '>', 0)->count(),
            'total_packages_with_quota' => Package::where('enforce_monthly_quota', true)->count(),
        ];

        $stats['quota_compliance_rate'] = $stats['users_met_quota'] + $stats['users_not_met_quota'] > 0
            ? round(($stats['users_met_quota'] / ($stats['users_met_quota'] + $stats['users_not_met_quota'])) * 100, 2)
            : 0;

        // Recent activity
        $recentTrackers = MonthlyQuotaTracker::with('user')
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->orderBy('total_pv_points', 'desc')
            ->take(10)
            ->get();

        return view('admin.monthly-quota.index', compact('stats', 'recentTrackers'));
    }

    /**
     * Manage package quotas
     */
    public function packages()
    {
        $packages = Package::orderBy('name')->get();

        return view('admin.monthly-quota.packages', compact('packages'));
    }

    /**
     * Update package monthly quota
     */
    public function updatePackageQuota(Request $request, Package $package)
    {
        $request->validate([
            'monthly_quota_points' => 'required|numeric|min:0|max:9999.99',
            'enforce_monthly_quota' => 'required|boolean',
        ]);

        $oldQuota = $package->monthly_quota_points;
        $oldEnforce = $package->enforce_monthly_quota;

        $package->monthly_quota_points = $request->monthly_quota_points;
        $package->enforce_monthly_quota = $request->enforce_monthly_quota;
        $package->save();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($package)
            ->withProperties([
                'old_quota' => $oldQuota,
                'new_quota' => $request->monthly_quota_points,
                'old_enforce' => $oldEnforce,
                'new_enforce' => $request->enforce_monthly_quota,
            ])
            ->log("Updated package monthly quota: {$package->name}");

        return back()->with('success', "Package quota updated successfully! {$package->name} now requires {$request->monthly_quota_points} PV monthly (Enforce: " . ($request->enforce_monthly_quota ? 'YES' : 'NO') . ").");
    }

    /**
     * Monthly quota reports
     */
    public function reports(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $trackers = MonthlyQuotaTracker::with('user')
            ->where('year', $year)
            ->where('month', $month)
            ->orderBy('total_pv_points', 'desc')
            ->paginate(50);

        $summary = [
            'total_users' => $trackers->total(),
            'quota_met' => MonthlyQuotaTracker::where('year', $year)->where('month', $month)->where('quota_met', true)->count(),
            'quota_not_met' => MonthlyQuotaTracker::where('year', $year)->where('month', $month)->where('quota_met', false)->count(),
            'total_pv' => MonthlyQuotaTracker::where('year', $year)->where('month', $month)->sum('total_pv_points'),
            'avg_pv' => MonthlyQuotaTracker::where('year', $year)->where('month', $month)->avg('total_pv_points'),
        ];

        return view('admin.monthly-quota.reports', compact('trackers', 'summary', 'year', 'month'));
    }

    /**
     * Individual user report
     */
    public function userReport(User $user)
    {
        $currentStatus = $this->quotaService->getUserMonthlyStatus($user);
        $history = $this->quotaService->getUserQuotaHistory($user, 12);

        // Get user's package
        $package = $user->orders()
            ->where('payment_status', 'paid')
            ->whereHas('orderItems.package', function($q) {
                $q->where('is_mlm_package', true);
            })
            ->first()
            ?->orderItems
            ?->first(fn($item) => $item->package && $item->package->is_mlm_package)
            ?->package;

        return view('admin.monthly-quota.user-report', compact('user', 'currentStatus', 'history', 'package'));
    }
}
