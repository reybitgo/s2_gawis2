<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Order;
use App\Models\MonthlyQuotaTracker;
use App\Services\MonthlyQuotaService;

echo "=== Verifying Phase 5 Quota Tracking ===\n\n";

$service = new MonthlyQuotaService();

$testUsers = ['quota_met_user', 'quota_half_user', 'quota_zero_user'];

foreach ($testUsers as $username) {
    echo "Checking: $username\n";
    echo str_repeat("-", 50) . "\n";
    
    $user = User::where('username', $username)->first();
    if (!$user) {
        echo "  ❌ User not found\n\n";
        continue;
    }
    
    // 1. Check user's package
    $package = $user->orders()
        ->where('payment_status', 'paid')
        ->whereHas('orderItems.package', function($q) {
            $q->where('is_mlm_package', true);
        })
        ->first()
        ?->orderItems
        ?->first(fn($item) => $item->package && $item->package->is_mlm_package)
        ?->package;
    
    echo "  Package: " . ($package ? $package->name : "NONE") . "\n";
    if ($package) {
        echo "    - Monthly Quota: {$package->monthly_quota_points} PV\n";
        echo "    - Enforced: " . ($package->enforce_monthly_quota ? "YES" : "NO") . "\n";
    }
    
    // 2. Check quota requirement
    $requirement = $user->getMonthlyQuotaRequirement();
    echo "  Quota Requirement: $requirement PV\n";
    
    // 3. Check orders with products
    $orders = Order::where('user_id', $user->id)
        ->where('payment_status', 'paid')
        ->whereYear('created_at', now()->year)
        ->whereMonth('created_at', now()->month)
        ->with('orderItems.product')
        ->get();
    
    echo "  Orders this month: {$orders->count()}\n";
    
    $totalCalculatedPV = 0;
    foreach ($orders as $order) {
        $orderPV = $order->orderItems
            ->filter(fn($item) => $item->isProduct() && $item->product)
            ->sum(fn($item) => $item->product->points_awarded * $item->quantity);
        
        if ($orderPV > 0) {
            echo "    - Order {$order->order_number}: {$orderPV} PV\n";
            $totalCalculatedPV += $orderPV;
        }
    }
    echo "  Total Calculated PV: $totalCalculatedPV\n";
    
    // 4. Check monthly_quota_tracker
    $tracker = MonthlyQuotaTracker::where('user_id', $user->id)
        ->where('year', now()->year)
        ->where('month', now()->month)
        ->first();
    
    if ($tracker) {
        echo "  Tracker Record:\n";
        echo "    - Total PV: {$tracker->total_pv_points}\n";
        echo "    - Required: {$tracker->required_quota}\n";
        echo "    - Quota Met: " . ($tracker->quota_met ? "YES" : "NO") . "\n";
    } else {
        echo "  ❌ No tracker record found for current month\n";
    }
    
    // 5. Check service status
    $status = $service->getUserMonthlyStatus($user);
    echo "  Service Status:\n";
    echo "    - Total PV: {$status['total_pv']}\n";
    echo "    - Required: {$status['required_quota']}\n";
    echo "    - Remaining: {$status['remaining_pv']}\n";
    echo "    - Quota Met: " . ($status['quota_met'] ? "YES" : "NO") . "\n";
    echo "    - Qualifies for Bonus: " . ($status['qualifies_for_bonus'] ? "YES" : "NO") . "\n";
    
    echo "\n";
}

echo "=== Diagnosis Complete ===\n";
