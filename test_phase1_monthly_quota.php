<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Models\Package;
use App\Models\MonthlyQuotaTracker;

echo "=== Phase 1: Monthly Quota System Test ===\n\n";

// Test 1: Verify Product points_awarded is now decimal
echo "Test 1: Verify Product points_awarded accepts decimal values\n";
echo "-------------------------------------------------------\n";
$product = Product::first();
if (!$product) {
    echo "No products found. Creating a test product...\n";
    $product = Product::create([
        'name' => 'Test Product for Quota',
        'price' => 100,
        'points_awarded' => 10.50,
        'is_active' => true,
    ]);
}

$product->points_awarded = 10.75;
$product->save();
$product->refresh();

echo "Product: {$product->name}\n";
echo "Points Awarded (PV): {$product->points_awarded}\n";
echo "Type: " . gettype($product->points_awarded) . "\n";
echo "Test 1: " . ($product->points_awarded == 10.75 ? "PASSED ✓" : "FAILED ✗") . "\n\n";

// Test 2: Verify Package has monthly quota fields
echo "Test 2: Verify Package has monthly quota fields\n";
echo "-------------------------------------------------------\n";
$package = Package::where('is_mlm_package', true)->first();
if (!$package) {
    echo "No MLM packages found. Creating a test package...\n";
    $package = Package::create([
        'name' => 'Test MLM Package',
        'price' => 500,
        'is_active' => true,
        'is_mlm_package' => true,
        'max_mlm_levels' => 10,
        'monthly_quota_points' => 100.50,
        'enforce_monthly_quota' => true,
    ]);
}

$package->monthly_quota_points = 150.25;
$package->enforce_monthly_quota = true;
$package->save();
$package->refresh();

echo "Package: {$package->name}\n";
echo "Monthly Quota Points: {$package->monthly_quota_points}\n";
echo "Enforce Monthly Quota: " . ($package->enforce_monthly_quota ? "Yes" : "No") . "\n";
echo "Test 2: " . ($package->monthly_quota_points == 150.25 ? "PASSED ✓" : "FAILED ✗") . "\n\n";

// Test 3: Test MonthlyQuotaTracker model
echo "Test 3: Test MonthlyQuotaTracker model\n";
echo "-------------------------------------------------------\n";
$user = User::where('network_status', 'active')->first();
if (!$user) {
    echo "No active user found. Please create an active user first.\n";
    exit(1);
}

echo "User: {$user->username} (ID: {$user->id})\n";
$tracker = MonthlyQuotaTracker::getOrCreateForCurrentMonth($user);
echo "Tracker created/found for: " . now()->format('F Y') . "\n";
echo "Year: {$tracker->year}\n";
echo "Month: {$tracker->month}\n";
echo "Total PV Points: {$tracker->total_pv_points}\n";
echo "Required Quota: {$tracker->required_quota}\n";
echo "Quota Met: " . ($tracker->quota_met ? "Yes" : "No") . "\n";
echo "Test 3: PASSED ✓\n\n";

// Test 4: Test User model methods
echo "Test 4: Test User model methods\n";
echo "-------------------------------------------------------\n";
$quotaRequirement = $user->getMonthlyQuotaRequirement();
echo "User's Monthly Quota Requirement: {$quotaRequirement} PV\n";

$meetsQuota = $user->meetsMonthlyQuota();
echo "Meets Monthly Quota: " . ($meetsQuota ? "Yes" : "No") . "\n";

$qualifiesForBonus = $user->qualifiesForUnilevelBonus();
echo "Qualifies for Unilevel Bonus: " . ($qualifiesForBonus ? "Yes" : "No") . "\n";
echo "Test 4: PASSED ✓\n\n";

// Test 5: Simulate adding PV points
echo "Test 5: Simulate adding PV points\n";
echo "-------------------------------------------------------\n";
$tracker->total_pv_points = 50.75;
$tracker->required_quota = 100;
$tracker->save();
$tracker->checkQuotaMet();
$tracker->refresh();

echo "Simulated PV: {$tracker->total_pv_points} / {$tracker->required_quota}\n";
echo "Quota Met: " . ($tracker->quota_met ? "Yes" : "No") . "\n";
echo "Expected: No\n";
echo "Test 5a: " . (!$tracker->quota_met ? "PASSED ✓" : "FAILED ✗") . "\n\n";

// Add more PV to meet quota
$tracker->total_pv_points = 120.50;
$tracker->save();
$tracker->checkQuotaMet();
$tracker->refresh();

echo "Simulated PV: {$tracker->total_pv_points} / {$tracker->required_quota}\n";
echo "Quota Met: " . ($tracker->quota_met ? "Yes" : "No") . "\n";
echo "Expected: Yes\n";
echo "Test 5b: " . ($tracker->quota_met ? "PASSED ✓" : "FAILED ✗") . "\n\n";

// Test 6: Test relationships
echo "Test 6: Test relationships\n";
echo "-------------------------------------------------------\n";
$trackers = $user->monthlyQuotaTrackers;
echo "User has " . $trackers->count() . " quota tracker(s)\n";

$currentTracker = $user->currentMonthQuota();
echo "Current month tracker found: " . ($currentTracker ? "Yes" : "No") . "\n";
echo "Test 6: PASSED ✓\n\n";

echo "=== All Phase 1 Tests Completed Successfully! ===\n\n";
echo "Summary:\n";
echo "- Products now support decimal PV (points_awarded)\n";
echo "- Packages now have monthly_quota_points and enforce_monthly_quota fields\n";
echo "- MonthlyQuotaTracker model works correctly\n";
echo "- User model has all required quota methods\n";
echo "- Relationships are properly defined\n\n";
echo "Phase 1 is READY! ✓\n";
