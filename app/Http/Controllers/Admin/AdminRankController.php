<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\User;
use App\Models\RankAdvancement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\RankAdvancementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminRankController extends Controller
{
    public function __construct(
        protected RankAdvancementService $rankService
    ) {}

    /**
     * Rank system dashboard
     */
    public function index()
    {
        $packages = Package::rankable()
            ->orderBy('rank_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $stats = [
            'total_ranked_users' => User::whereNotNull('current_rank')->count(),
            'total_advancements' => RankAdvancement::count(),
            'system_rewards_count' => RankAdvancement::where('advancement_type', 'sponsorship_reward')->count(),
            'total_system_paid' => RankAdvancement::where('advancement_type', 'sponsorship_reward')
                ->sum('system_paid_amount'),
        ];

        // Rank distribution
        $rankDistribution = User::whereNotNull('current_rank')
            ->selectRaw('current_rank, COUNT(*) as count')
            ->groupBy('current_rank')
            ->get()
            ->keyBy('current_rank');

        $breadcrumbs = [
            ['title' => 'Admin Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Rank System'],
        ];

        return view('admin.ranks.index', compact('packages', 'stats', 'rankDistribution', 'breadcrumbs'));
    }

    /**
     * Configure rank requirements
     */
    public function configure()
    {
        $packages = Package::rankable()
            ->orderBy('rank_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $breadcrumbs = [
            ['title' => 'Admin Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Rank System', 'url' => route('admin.ranks.index')],
            ['title' => 'Configure'],
        ];

        return view('admin.ranks.configure', compact('packages', 'breadcrumbs'));
    }

    /**
     * Update rank configuration
     */
    public function updateConfiguration(Request $request)
    {
        $request->validate([
            'packages' => 'required|array',
            'packages.*.rank_name' => 'required|string|max:100',
            'packages.*.rank_order' => 'required|integer|min:1',
            'packages.*.required_direct_sponsors' => 'required|integer|min:0',
            'packages.*.required_sponsors_ppv_gpv' => 'required|integer|min:0',
            'packages.*.ppv_required' => 'required|numeric|min:0',
            'packages.*.gpv_required' => 'required|numeric|min:0',
            'packages.*.rank_pv_enabled' => 'nullable|boolean',
            'packages.*.rank_reward' => 'nullable|numeric|min:0',
            'packages.*.next_rank_package_id' => 'nullable|exists:packages,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->packages as $packageId => $data) {
                $package = Package::findOrFail($packageId);

                $package->update([
                    'rank_name' => $data['rank_name'],
                    'rank_order' => $data['rank_order'],
                    'required_direct_sponsors' => $data['required_direct_sponsors'],
                    'required_sponsors_ppv_gpv' => $data['required_sponsors_ppv_gpv'],
                    'ppv_required' => $data['ppv_required'],
                    'gpv_required' => $data['gpv_required'],
                    'rank_pv_enabled' => $data['rank_pv_enabled'] ?? true,
                    'next_rank_package_id' => $data['next_rank_package_id'] ?? null,
                    'rank_reward' => $data['rank_reward'] ?? 0,
                ]);
            }

            DB::commit();
            return back()->with('success', 'Rank configuration updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update rank configuration', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->id(),
            ]);
            return back()->withErrors(['error' => 'Failed to update configuration: ' . $e->getMessage()]);
        }
    }

    /**
     * View rank advancement history
     */
    public function advancements(Request $request)
    {
        $query = RankAdvancement::with(['user', 'toPackage']);

        if ($request->filled('type')) {
            $query->where('advancement_type', $request->type);
        }

        if ($request->filled('rank')) {
            $query->where('to_rank', $request->rank);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Per page option with validation
        $perPage = $request->input('per_page', 15);
        $perPage = in_array($perPage, [5, 10, 15, 20, 25, 50, 100]) ? $perPage : 15;

        $advancements = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        $ranks = RankAdvancement::distinct()->pluck('to_rank')->sort()->values();

        $breadcrumbs = [
            ['title' => 'Admin Dashboard', 'url' => route('admin.dashboard')],
            ['title' => 'Rank System', 'url' => route('admin.ranks.index')],
            ['title' => 'Advancements'],
        ];

        return view('admin.ranks.advancements', compact('advancements', 'ranks', 'breadcrumbs'));
    }

    /**
     * Manually advance a user's rank (admin action)
     */
    public function manualAdvance(Request $request, User $user)
    {
        $request->validate([
            'to_package_id' => 'required|exists:packages,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $toPackage = Package::findOrFail($request->to_package_id);
        $fromPackage = $user->rankPackage;

        DB::beginTransaction();
        try {
            // Create manual order (system-funded)
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ADMIN-RANK-' . strtoupper(uniqid()),
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_method' => 'admin_adjustment',
                'subtotal' => $toPackage->rank_reward,
                'grand_total' => $toPackage->rank_reward,
                'notes' => 'Manual rank advancement by admin: ' . ($request->notes ?? ''),
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'package_id' => $toPackage->id,
                'product_id' => null,
                'quantity' => 1,
                'price' => $toPackage->rank_reward,
                'subtotal' => $toPackage->rank_reward,
            ]);

            // Update user rank
            $user->update([
                'current_rank' => $toPackage->rank_name,
                'rank_package_id' => $toPackage->id,
                'rank_updated_at' => now(),
            ]);

            // Activate network if not already active
            if (!$user->isNetworkActive()) {
                $user->activateNetwork();
            }

            // Record advancement
            RankAdvancement::create([
                'user_id' => $user->id,
                'from_rank' => $fromPackage?->rank_name,
                'to_rank' => $toPackage->rank_name,
                'from_package_id' => $fromPackage?->id,
                'to_package_id' => $toPackage->id,
                'advancement_type' => 'admin_adjustment',
                'system_paid_amount' => $toPackage->rank_reward,
                'order_id' => $order->id,
                'notes' => $request->notes,
            ]);

            // CREDIT RANK REWARD TO USER WALLET (mlm_balance + withdrawable_balance)
            try {
                $wallet = $user->getOrCreateWallet();
                if ($wallet) {
                    $amount = (float) $toPackage->rank_reward;
                    if ($amount > 0) {
                        $wallet->increment('mlm_balance', $amount);
                        $wallet->increment('withdrawable_balance', $amount);
                        $wallet->update(['last_transaction_at' => now()]);

                        \App\Models\ActivityLog::createLog(
                            'mlm',
                            'rank_reward',
                            sprintf('%s received rank reward of ₱%s for manual advancement to %s', $user->username ?? $user->fullname ?? 'User', number_format($amount, 2), $toPackage->rank_name),
                            'INFO',
                            $user->id,
                            [
                                'reward_amount' => $amount,
                                'to_rank' => $toPackage->rank_name,
                                'order_id' => $order->id,
                                'package_id' => $toPackage->id,
                            ],
                            null,
                            $order->id
                        );
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to credit manual rank reward to wallet', [
                    'user_id' => $user->id,
                    'package_id' => $toPackage->id,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('Manual rank advancement by admin', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'from_rank' => $fromPackage?->rank_name ?? 'None',
                'to_rank' => $toPackage->rank_name,
                'order_id' => $order->id,
            ]);

            DB::commit();
            return back()->with('success', "User {$user->username} advanced to {$toPackage->rank_name}!");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to manually advance user rank', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Failed to advance user: ' . $e->getMessage()]);
        }
    }
}
