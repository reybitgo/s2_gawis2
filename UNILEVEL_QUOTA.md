# Unilevel Monthly Quota System Implementation Plan

## Overview

This document outlines a comprehensive, phased implementation plan for adding a **Monthly Personal Purchase Quota** requirement to the Unilevel Bonus system. Users must accumulate a specified number of points through personal product purchases each month to remain eligible for Unilevel bonuses from their downline's purchases.

### Key Requirements

1. **Points-Based System**: Each product has a point value (PV - Personal Volume)
2. **Monthly Tracking**: System tracks points accumulated within the current calendar month
3. **Package-Based Quotas**: Each starter package defines its own monthly quota requirement
4. **Dual Qualification**: Users must be both `network_active` AND meet monthly quota to earn bonuses
5. **Admin Flexibility**: Admin can configure product points and package quotas
6. **Real-Time Validation**: System validates quota status when distributing bonuses

---

## Phase 1: Database Schema & Models Setup

**Goal**: Create the foundational database structure for tracking product points and monthly quotas

**Estimated Time**: 2-3 hours

### 1.1 Database Migrations

#### Migration 1: Modify Products Table Points Column

**NOTE**: The `products` table already has a `points_awarded` column (integer) that we need to modify to decimal for better precision.

**File**: `database/migrations/YYYY_MM_DD_modify_points_awarded_to_decimal_in_products_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Change points_awarded from integer to decimal(10,2)
            $table->decimal('points_awarded', 10, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Revert back to integer if needed
            $table->integer('points_awarded')->default(0)->change();
        });
    }
};
```

**Existing column modification**:
- Current type: `integer`
- New type: `decimal(10, 2)`
- Purpose: PV (Personal Volume) points for quota tracking
- Reason: Allow fractional PV values (e.g., 0.5, 1.25, etc.) for more flexible point allocation

#### Migration 2: Add Monthly Quota to Packages Table
**File**: `database/migrations/YYYY_MM_DD_add_monthly_quota_to_packages_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('monthly_quota_points', 10, 2)->default(0)->after('max_mlm_levels')
                ->comment('Required monthly PV points to earn Unilevel bonuses');
            $table->boolean('enforce_monthly_quota')->default(false)->after('monthly_quota_points')
                ->comment('Enable/disable monthly quota requirement for this package');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['monthly_quota_points', 'enforce_monthly_quota']);
        });
    }
};
```

**NOTE**: Using decimal(10,2) type to match modified `points_awarded` column type.

#### Migration 3: Create Monthly Quota Tracking Table
**File**: `database/migrations/YYYY_MM_DD_create_monthly_quota_tracker_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_quota_tracker', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('year')->comment('Year (e.g., 2025)');
            $table->integer('month')->comment('Month (1-12)');
            $table->decimal('total_pv_points', 10, 2)->default(0)->comment('Total PV accumulated this month');
            $table->decimal('required_quota', 10, 2)->default(0)->comment('Required quota based on user\'s package');
            $table->boolean('quota_met')->default(false)->comment('Whether quota is met this month');
            $table->timestamp('last_purchase_at')->nullable()->comment('Last product purchase timestamp');
            $table->timestamps();

            // Composite unique index: one record per user per month
            $table->unique(['user_id', 'year', 'month'], 'user_month_unique');
            
            // Index for quick lookups
            $table->index(['user_id', 'year', 'month']);
            $table->index('quota_met');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_quota_tracker');
    }
};
```

**NOTE**: Using decimal(10,2) type for PV points to match modified `points_awarded` column type.

### 1.2 Model Updates

#### Update Product Model
**File**: `app/Models/Product.php`

**NOTE**: Update the cast type for `points_awarded` from integer to decimal.

Update existing configuration:
```php
protected $fillable = [
    // ... existing fields
    'points_awarded', // ✅ Already exists - use this for PV
];

protected $casts = [
    // ... existing casts
    'points_awarded' => 'decimal:2', // UPDATE: Change from 'integer' to 'decimal:2'
];
```

**Field Mapping**:
- Database column: `points_awarded` (decimal(10,2))
- Purpose: PV (Personal Volume) points for monthly quota
- Usage in code: `$product->points_awarded`
- Supports fractional values: 0.5, 1.25, 10.50, etc.

#### Update Package Model
**File**: `app/Models/Package.php`

Add to fillable array and cast:
```php
protected $fillable = [
    // ... existing fields
    'monthly_quota_points', // NEW: Add this
    'enforce_monthly_quota', // NEW: Add this
];

protected $casts = [
    // ... existing casts
    'monthly_quota_points' => 'decimal:2', // NEW: Add this
    'enforce_monthly_quota' => 'boolean', // NEW: Add this
];
```

**NOTE**: Using decimal:2 type for precise PV quota values.

#### Create MonthlyQuotaTracker Model
**File**: `app/Models/MonthlyQuotaTracker.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyQuotaTracker extends Model
{
    protected $table = 'monthly_quota_tracker';

    protected $fillable = [
        'user_id',
        'year',
        'month',
        'total_pv_points',
        'required_quota',
        'quota_met',
        'last_purchase_at',
    ];

    protected $casts = [
        'total_pv_points' => 'decimal:2',
        'required_quota' => 'decimal:2',
        'quota_met' => 'boolean',
        'last_purchase_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper: Get or create tracker for current month
    public static function getOrCreateForCurrentMonth(User $user): self
    {
        $year = now()->year;
        $month = now()->month;

        return self::firstOrCreate(
            [
                'user_id' => $user->id,
                'year' => $year,
                'month' => $month,
            ],
            [
                'total_pv_points' => 0,
                'required_quota' => $user->getMonthlyQuotaRequirement(),
                'quota_met' => false,
            ]
        );
    }

    // Helper: Check if quota is met
    public function checkQuotaMet(): bool
    {
        $this->quota_met = $this->total_pv_points >= $this->required_quota;
        $this->save();
        return $this->quota_met;
    }
}
```

### 1.3 User Model Relationship

**File**: `app/Models/User.php`

Add relationship method:
```php
/**
 * Get user's monthly quota tracker records
 */
public function monthlyQuotaTrackers()
{
    return $this->hasMany(MonthlyQuotaTracker::class);
}

/**
 * Get user's current month quota tracker
 */
public function currentMonthQuota()
{
    return $this->monthlyQuotaTrackers()
        ->where('year', now()->year)
        ->where('month', now()->month)
        ->first();
}

/**
 * Get the monthly quota requirement based on user's active package
 */
public function getMonthlyQuotaRequirement(): float
{
    // Get the user's first purchased MLM package
    $package = $this->orders()
        ->where('payment_status', 'paid')
        ->whereHas('orderItems.package', function($q) {
            $q->where('is_mlm_package', true);
        })
        ->first()
        ?->orderItems
        ?->first(fn($item) => $item->package && $item->package->is_mlm_package)
        ?->package;

    if (!$package || !$package->enforce_monthly_quota) {
        return 0;
    }

    return $package->monthly_quota_points ?? 0;
}

/**
 * Check if user meets monthly quota for Unilevel earnings
 */
public function meetsMonthlyQuota(): bool
{
    $tracker = $this->currentMonthQuota();
    
    if (!$tracker) {
        // No tracker yet, create one
        $tracker = MonthlyQuotaTracker::getOrCreateForCurrentMonth($this);
    }

    return $tracker->checkQuotaMet();
}

/**
 * Check if user qualifies for Unilevel bonuses (active + quota)
 */
public function qualifiesForUnilevelBonus(): bool
{
    // Must be network active
    if (!$this->isNetworkActive()) {
        return false;
    }

    // Check if user's package enforces monthly quota
    $package = $this->orders()
        ->where('payment_status', 'paid')
        ->whereHas('orderItems.package', function($q) {
            $q->where('is_mlm_package', true);
        })
        ->first()
        ?->orderItems
        ?->first(fn($item) => $item->package && $item->package->is_mlm_package)
        ?->package;

    // If no package or quota not enforced, only check active status
    if (!$package || !$package->enforce_monthly_quota) {
        return true; // Network active is enough
    }

    // If quota is enforced, check if met
    return $this->meetsMonthlyQuota();
}
```

### 1.4 Testing Phase 1

**Manual Testing Checklist**:
- [ ] Run migrations successfully (3 migrations total)
- [ ] Verify `products.points_awarded` column changed from integer to decimal(10,2)
- [ ] Verify `packages` table has NEW `monthly_quota_points` (decimal) and `enforce_monthly_quota` columns
- [ ] Verify `monthly_quota_tracker` table exists with decimal PV fields
- [ ] Test `MonthlyQuotaTracker::getOrCreateForCurrentMonth()` creates records correctly
- [ ] Test `User::getMonthlyQuotaRequirement()` returns correct quota (float)
- [ ] Test `User::meetsMonthlyQuota()` returns false when no purchases
- [ ] Test `User::qualifiesForUnilevelBonus()` combines active status + quota
- [ ] Test decimal PV values work correctly (e.g., 0.5, 1.25, 10.50)

**Database Verification Commands**:
```bash
# Run migrations (3 total: modify products, add to packages, create tracker)
php artisan migrate

# Check tables
php artisan tinker

# Verify products table has decimal points_awarded
$product = Product::first();
dump($product->points_awarded); // Should be decimal
$product->points_awarded = 10.5; // Test decimal value
$product->save();
dump($product->fresh()->points_awarded); // Should return 10.50

# Verify packages table has new fields
$package = Package::first();
dump($package->monthly_quota_points); // Should exist (decimal)
dump($package->enforce_monthly_quota); // Should exist (boolean)
$package->monthly_quota_points = 100.50;
$package->save();

# Test model creation
$user = User::find(1);
$tracker = MonthlyQuotaTracker::getOrCreateForCurrentMonth($user);
dump($tracker);

# Test quota methods
dump($user->getMonthlyQuotaRequirement()); // Should return float
dump($user->meetsMonthlyQuota());
dump($user->qualifiesForUnilevelBonus());
```

---

## Phase 2: Points Tracking Service

**Goal**: Create service to track and update PV points when users purchase products

**Estimated Time**: 2-3 hours

### 2.1 Create Monthly Quota Service

**File**: `app/Services/MonthlyQuotaService.php`

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\MonthlyQuotaTracker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MonthlyQuotaService
{
    /**
     * Process PV points from a completed order
     *
     * @param Order $order
     * @return bool
     */
    public function processOrderPoints(Order $order): bool
    {
        $order->load('orderItems.product');

        // Filter only product order items (not packages)
        $productOrderItems = $order->orderItems->filter(function ($orderItem) {
            return $orderItem->isProduct() && $orderItem->product;
        });

        if ($productOrderItems->isEmpty()) {
            Log::info('Order has no products for PV tracking', ['order_id' => $order->id]);
            return false;
        }

        DB::beginTransaction();
        try {
            $buyer = $order->user;
            $totalPV = 0;

            // Calculate total PV from all products
            foreach ($productOrderItems as $orderItem) {
                $product = $orderItem->product;
                $pvPoints = $product->points_awarded * $orderItem->quantity;
                $totalPV += $pvPoints;
            }

            if ($totalPV <= 0) {
                Log::info('Order has no PV points (products have 0 points_awarded)', [
                    'order_id' => $order->id,
                    'buyer_id' => $buyer->id
                ]);
                DB::commit();
                return false;
            }

            // Add PV to user's current month tracker
            $this->addPointsToUser($buyer, $totalPV, $order);

            Log::info('PV Points Added to Monthly Tracker', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'buyer_id' => $buyer->id,
                'buyer_username' => $buyer->username,
                'pv_added' => $totalPV,
                'products' => $productOrderItems->map(fn($item) => [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'points_awarded' => $item->product->points_awarded,
                    'total_pv' => $item->product->points_awarded * $item->quantity,
                ])->toArray()
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process order PV points', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Add PV points to user's current month tracker
     *
     * @param User $user
     * @param float $pvPoints
     * @param Order $order
     * @return MonthlyQuotaTracker
     */
    public function addPointsToUser(User $user, float $pvPoints, Order $order): MonthlyQuotaTracker
    {
        $tracker = MonthlyQuotaTracker::getOrCreateForCurrentMonth($user);

        $previousPV = $tracker->total_pv_points;
        $tracker->total_pv_points += $pvPoints;
        $tracker->last_purchase_at = now();
        
        // Update required quota in case package changed
        $tracker->required_quota = $user->getMonthlyQuotaRequirement();
        
        // Check if quota is now met
        $tracker->checkQuotaMet();

        Log::info('PV Points Updated', [
            'user_id' => $user->id,
            'user_username' => $user->username,
            'order_id' => $order->id,
            'pv_added' => $pvPoints,
            'previous_pv' => $previousPV,
            'new_total_pv' => $tracker->total_pv_points,
            'required_quota' => $tracker->required_quota,
            'quota_met' => $tracker->quota_met,
            'year_month' => $tracker->year . '-' . $tracker->month
        ]);

        return $tracker;
    }

    /**
     * Get user's current month PV status
     *
     * @param User $user
     * @return array
     */
    public function getUserMonthlyStatus(User $user): array
    {
        $tracker = MonthlyQuotaTracker::getOrCreateForCurrentMonth($user);

        return [
            'year' => $tracker->year,
            'month' => $tracker->month,
            'month_name' => now()->setMonth($tracker->month)->format('F'),
            'total_pv' => $tracker->total_pv_points,
            'required_quota' => $tracker->required_quota,
            'remaining_pv' => max(0, $tracker->required_quota - $tracker->total_pv_points),
            'quota_met' => $tracker->quota_met,
            'progress_percentage' => $tracker->required_quota > 0 
                ? min(100, ($tracker->total_pv_points / $tracker->required_quota) * 100)
                : 100,
            'last_purchase_at' => $tracker->last_purchase_at,
            'qualifies_for_bonus' => $user->qualifiesForUnilevelBonus(),
        ];
    }

    /**
     * Get user's monthly quota history
     *
     * @param User $user
     * @param int $months Number of months to retrieve
     * @return \Illuminate\Support\Collection
     */
    public function getUserQuotaHistory(User $user, int $months = 6)
    {
        return $user->monthlyQuotaTrackers()
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take($months)
            ->get()
            ->map(function($tracker) {
                return [
                    'year' => $tracker->year,
                    'month' => $tracker->month,
                    'month_name' => now()->setMonth($tracker->month)->format('F'),
                    'total_pv' => $tracker->total_pv_points,
                    'required_quota' => $tracker->required_quota,
                    'quota_met' => $tracker->quota_met,
                    'progress_percentage' => $tracker->required_quota > 0 
                        ? min(100, ($tracker->total_pv_points / $tracker->required_quota) * 100)
                        : 100,
                ];
            });
    }

    /**
     * Recalculate quota status for a specific month (admin utility)
     *
     * @param User $user
     * @param int $year
     * @param int $month
     * @return bool
     */
    public function recalculateMonthlyQuota(User $user, int $year, int $month): bool
    {
        $tracker = $user->monthlyQuotaTrackers()
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if (!$tracker) {
            return false;
        }

        // Recalculate total PV from orders
        $totalPV = $user->orders()
            ->where('payment_status', 'paid')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->with('orderItems.product')
            ->get()
            ->flatMap(function($order) {
                return $order->orderItems->filter(fn($item) => $item->isProduct() && $item->product);
            })
            ->sum(function($orderItem) {
                return $orderItem->product->points_awarded * $orderItem->quantity;
            });

        $tracker->total_pv_points = $totalPV;
        $tracker->checkQuotaMet();

        Log::info('Monthly quota recalculated', [
            'user_id' => $user->id,
            'year' => $year,
            'month' => $month,
            'recalculated_pv' => $totalPV,
            'quota_met' => $tracker->quota_met
        ]);

        return true;
    }
}
```

### 2.2 ~~Create Job for Processing Points~~ - NOT NEEDED

**NOTE**: We do NOT use Laravel Jobs/Queues for quota processing. The quota update happens **synchronously** (immediately) during checkout.

**Reason**: 
- No background processing needed
- Sequential execution ensures real-time updates
- Simpler debugging and error handling
- Immediate feedback to user
- No queue workers required

**Instead**: Call the service method directly in CheckoutController (see section 2.3 below)

### 2.3 Integrate into Checkout Flow (Direct Synchronous Call)

**File**: `app/Http/Controllers/CheckoutController.php`

**IMPORTANT**: Call service methods DIRECTLY (no jobs, no queues, no background processing)

Add after Unilevel bonus processing:
```php
// Process Unilevel bonuses immediately (sync) if order contains products
$hasProduct = $order->orderItems->contains(function($orderItem) {
    return $orderItem->isProduct();
});

if ($hasProduct) {
    // ADD THIS FIRST: Process monthly quota points BEFORE Unilevel bonuses
    // Direct service call - NO JOBS, NO QUEUES
    $quotaService = app(MonthlyQuotaService::class);
    $quotaService->processOrderPoints($order);
    
    // Then process Unilevel bonuses (will use updated quota status)
    // NOTE: If UnilevelBonusService also uses jobs, call it directly too
    $unilevelService = app(UnilevelBonusService::class);
    $unilevelService->processBonuses($order);

    $productNames = $order->orderItems
        ->filter(fn($item) => $item->isProduct())
        ->pluck('product.name')
        ->join(', ');

    Log::info('Unilevel Bonus & Monthly Quota Processing Completed', [
        'order_id' => $order->id,
        'order_number' => $order->order_number,
        'buyer_id' => $order->user_id,
        'products' => $productNames,
        'processing_mode' => 'synchronous_direct_call', // Real-time, no queues
    ]);
}
```

**Critical Notes**:
1. **NO JOBS OR QUEUES** - Direct method calls only
2. Process quota points BEFORE Unilevel bonuses
3. Sequential execution: quota update → then bonus distribution
4. Buyer's quota updates in real-time (same request)
5. If buyer is also someone's upline, their new quota status takes effect immediately
6. No queue workers needed
7. Simpler error handling and debugging

### 2.4 Testing Phase 2

**Test Script**: `test_monthly_quota_tracking.php`

```php
<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\MonthlyQuotaService;

echo "=== Monthly Quota Tracking Test ===\n\n";

$quotaService = new MonthlyQuotaService();

// Test 1: Get user and set product PV
$user = User::where('network_status', 'active')->first();
if (!$user) {
    die("No active user found. Create an active user first.\n");
}

echo "Testing with User: {$user->username} (ID: {$user->id})\n";
echo "Current Quota Requirement: " . $user->getMonthlyQuotaRequirement() . " PV\n\n";

// Test 2: Set product PV
$product = Product::first();
if (!$product) {
    die("No product found.\n");
}

$product->points_awarded = 50;
$product->save();
echo "Set Product '{$product->name}' PV to: 50\n\n";

// Test 3: Create test order with products
$order = Order::create([
    'user_id' => $user->id,
    'order_number' => 'TEST-' . time(),
    'payment_status' => 'paid',
    'payment_method' => 'gcash',
    'grand_total' => $product->price,
]);

OrderItem::create([
    'order_id' => $order->id,
    'product_id' => $product->id,
    'quantity' => 2,
    'price' => $product->price,
    'subtotal' => $product->price * 2,
]);

echo "Created Test Order: {$order->order_number}\n";
echo "Product Quantity: 2\n";
echo "Expected PV: 100 (50 x 2)\n\n";

// Test 4: Process order points
$success = $quotaService->processOrderPoints($order);
echo "Process Points Result: " . ($success ? "SUCCESS" : "FAILED") . "\n\n";

// Test 5: Check user's monthly status
$status = $quotaService->getUserMonthlyStatus($user);
echo "=== Monthly Status ===\n";
echo "Month: {$status['month_name']} {$status['year']}\n";
echo "Total PV: {$status['total_pv']}\n";
echo "Required Quota: {$status['required_quota']}\n";
echo "Remaining PV: {$status['remaining_pv']}\n";
echo "Quota Met: " . ($status['quota_met'] ? "YES" : "NO") . "\n";
echo "Progress: " . number_format($status['progress_percentage'], 2) . "%\n";
echo "Qualifies for Bonus: " . ($status['qualifies_for_bonus'] ? "YES" : "NO") . "\n\n";

// Test 6: Check qualification method
echo "=== Qualification Check ===\n";
echo "isNetworkActive(): " . ($user->isNetworkActive() ? "YES" : "NO") . "\n";
echo "meetsMonthlyQuota(): " . ($user->meetsMonthlyQuota() ? "YES" : "NO") . "\n";
echo "qualifiesForUnilevelBonus(): " . ($user->qualifiesForUnilevelBonus() ? "YES" : "NO") . "\n\n";

echo "Test completed!\n";
```

**Manual Testing Checklist**:
- [ ] Run test script successfully
- [ ] Verify `monthly_quota_tracker` record is created
- [ ] Verify PV points are calculated correctly (points_awarded × quantity)
- [ ] Verify `total_pv_points` is updated in tracker
- [ ] Verify `quota_met` is true when quota is reached
- [ ] Verify `qualifiesForUnilevelBonus()` returns correct result
- [ ] Test with multiple products in one order
- [ ] Test with products having different PV values
- [ ] Test with products having 0 PV (should skip)

---

## Phase 3: Update Unilevel Bonus Distribution Logic

**Goal**: Modify Unilevel bonus distribution to check monthly quota qualification

**Estimated Time**: 1-2 hours

### 3.1 Update UnilevelBonusService

**File**: `app/Services/UnilevelBonusService.php`

**Change**: Replace `isNetworkActive()` with `qualifiesForUnilevelBonus()`

```php
// BEFORE (line ~43):
if (!$currentUser->isNetworkActive()) {
    $currentUser = $currentUser->sponsor;
    continue; // Skip to the next sponsor
}

// AFTER:
if (!$currentUser->qualifiesForUnilevelBonus()) {
    Log::info('Upline does not qualify for Unilevel bonus (not active or quota not met)', [
        'upline_id' => $currentUser->id,
        'upline_username' => $currentUser->username,
        'level' => $level,
        'order_id' => $order->id,
        'is_active' => $currentUser->isNetworkActive(),
        'meets_quota' => $currentUser->meetsMonthlyQuota(),
    ]);
    
    $currentUser = $currentUser->sponsor;
    continue; // Skip to the next sponsor
}
```

**And in `creditBonus()` method (line ~118):**

```php
// BEFORE:
if (!$user->isNetworkActive()) {
    Log::info('User is not active, skipping unilevel bonus', ['user_id' => $user->id, 'level' => $level]);
    return false;
}

// AFTER:
if (!$user->qualifiesForUnilevelBonus()) {
    Log::info('User does not qualify for unilevel bonus', [
        'user_id' => $user->id,
        'username' => $user->username,
        'level' => $level,
        'is_active' => $user->isNetworkActive(),
        'meets_quota' => $user->meetsMonthlyQuota(),
        'monthly_status' => app(MonthlyQuotaService::class)->getUserMonthlyStatus($user),
    ]);
    return false;
}
```

### 3.2 Enhanced Logging

Add detailed logging to track why users don't qualify:

```php
// In the main loop where we skip unqualified users
if (!$currentUser->qualifiesForUnilevelBonus()) {
    $quotaService = app(MonthlyQuotaService::class);
    $monthlyStatus = $quotaService->getUserMonthlyStatus($currentUser);
    
    Log::info('Upline Skipped - Unilevel Qualification Failed', [
        'order_id' => $order->id,
        'buyer_id' => $buyer->id,
        'buyer_username' => $buyer->username,
        'upline_id' => $currentUser->id,
        'upline_username' => $currentUser->username,
        'level' => $level,
        'reason' => [
            'is_network_active' => $currentUser->isNetworkActive(),
            'meets_monthly_quota' => $currentUser->meetsMonthlyQuota(),
            'monthly_pv' => $monthlyStatus['total_pv'],
            'required_quota' => $monthlyStatus['required_quota'],
            'remaining_pv' => $monthlyStatus['remaining_pv'],
            'progress_percentage' => $monthlyStatus['progress_percentage'],
        ],
    ]);
    
    $currentUser = $currentUser->sponsor;
    continue;
}
```

### 3.3 Testing Phase 3

**Test Script**: `test_unilevel_with_quota.php`

```php
<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Models\Package;
use App\Services\MonthlyQuotaService;
use App\Services\UnilevelBonusService;

echo "=== Unilevel Bonus with Monthly Quota Test ===\n\n";

// Setup: Create 3-level hierarchy
// Level 0 (Buyer) -> Level 1 (Sponsor) -> Level 2 (Grand Sponsor)

$buyer = User::where('network_status', 'active')->first();
$sponsor = $buyer->sponsor;
$grandSponsor = $sponsor?->sponsor;

if (!$sponsor || !$grandSponsor) {
    die("Need at least 3-level hierarchy for testing.\n");
}

echo "=== Test Hierarchy ===\n";
echo "Buyer: {$buyer->username} (ID: {$buyer->id})\n";
echo "Level 1 (Sponsor): {$sponsor->username} (ID: {$sponsor->id})\n";
echo "Level 2 (Grand Sponsor): {$grandSponsor->username} (ID: {$grandSponsor->id})\n\n";

$quotaService = new MonthlyQuotaService();
$unilevelService = new UnilevelBonusService();

// Check current qualification status
echo "=== Current Qualification Status ===\n";
foreach ([$sponsor, $grandSponsor] as $i => $upline) {
    $level = $i + 1;
    $status = $quotaService->getUserMonthlyStatus($upline);
    echo "Level {$level} ({$upline->username}):\n";
    echo "  - Network Active: " . ($upline->isNetworkActive() ? "YES" : "NO") . "\n";
    echo "  - Monthly PV: {$status['total_pv']} / {$status['required_quota']}\n";
    echo "  - Quota Met: " . ($status['quota_met'] ? "YES" : "NO") . "\n";
    echo "  - Qualifies for Bonus: " . ($upline->qualifiesForUnilevelBonus() ? "YES" : "NO") . "\n\n";
}

// Test Scenario 1: Sponsor has NOT met quota
echo "=== Scenario 1: Sponsor Has NOT Met Quota ===\n";
$product = Product::where('points_awarded', '>', 0)->first();
if (!$product) {
    echo "No product with PV > 0 found. Setting first product PV to 50...\n";
    $product = Product::first();
    $product->points_awarded = 50;
    $product->save();
}

echo "Creating order for buyer with product: {$product->name}\n";
echo "Expected Result: Level 1 (Sponsor) should NOT earn if quota not met\n\n";

// Create order
$order = \App\Models\Order::create([
    'user_id' => $buyer->id,
    'order_number' => 'TEST-QUOTA-' . time(),
    'payment_status' => 'paid',
    'payment_method' => 'gcash',
    'grand_total' => $product->price,
]);

\App\Models\OrderItem::create([
    'order_id' => $order->id,
    'product_id' => $product->id,
    'quantity' => 1,
    'price' => $product->price,
    'subtotal' => $product->price,
]);

// Process Unilevel bonuses
$result = $unilevelService->processBonuses($order);
echo "Unilevel Processing Result: " . ($result ? "SUCCESS" : "FAILED/SKIPPED") . "\n";
echo "Check logs for detailed skip reasons.\n\n";

// Test Scenario 2: Give sponsor enough PV to meet quota
echo "=== Scenario 2: Give Sponsor Enough PV to Meet Quota ===\n";
$sponsorStatus = $quotaService->getUserMonthlyStatus($sponsor);
$remainingPV = $sponsorStatus['remaining_pv'];

echo "Sponsor needs {$remainingPV} more PV to meet quota.\n";
echo "Creating purchase for sponsor to meet quota...\n";

// Create sponsor's purchase
$sponsorOrder = \App\Models\Order::create([
    'user_id' => $sponsor->id,
    'order_number' => 'SPONSOR-PV-' . time(),
    'payment_status' => 'paid',
    'payment_method' => 'gcash',
    'grand_total' => $product->price * 10,
]);

$quantityNeeded = ceil($remainingPV / $product->points_awarded) + 1;

\App\Models\OrderItem::create([
    'order_id' => $sponsorOrder->id,
    'product_id' => $product->id,
    'quantity' => $quantityNeeded,
    'price' => $product->price,
    'subtotal' => $product->price * $quantityNeeded,
]);

// Process quota points for sponsor
$quotaService->processOrderPoints($sponsorOrder);

$newStatus = $quotaService->getUserMonthlyStatus($sponsor);
echo "Sponsor New Status:\n";
echo "  - Total PV: {$newStatus['total_pv']} / {$newStatus['required_quota']}\n";
echo "  - Quota Met: " . ($newStatus['quota_met'] ? "YES" : "NO") . "\n";
echo "  - Qualifies: " . ($sponsor->fresh()->qualifiesForUnilevelBonus() ? "YES" : "NO") . "\n\n";

// Now create another buyer purchase
echo "Creating another buyer purchase...\n";
$order2 = \App\Models\Order::create([
    'user_id' => $buyer->id,
    'order_number' => 'TEST-QUOTA-2-' . time(),
    'payment_status' => 'paid',
    'payment_method' => 'gcash',
    'grand_total' => $product->price,
]);

\App\Models\OrderItem::create([
    'order_id' => $order2->id,
    'product_id' => $product->id,
    'quantity' => 1,
    'price' => $product->price,
    'subtotal' => $product->price,
]);

$result2 = $unilevelService->processBonuses($order2);
echo "Unilevel Processing Result: " . ($result2 ? "SUCCESS" : "FAILED") . "\n";
echo "Expected: Sponsor should NOW earn bonus since quota is met.\n\n";

// Check sponsor's wallet
$sponsorWallet = $sponsor->fresh()->wallet;
echo "Sponsor's Wallet:\n";
echo "  - Unilevel Balance: ₱" . number_format($sponsorWallet->unilevel_balance, 2) . "\n\n";

echo "Test completed! Check logs for detailed information.\n";
```

**Manual Testing Checklist**:
- [ ] Upline without quota is skipped (check logs)
- [ ] Upline with quota met receives bonus
- [ ] Multiple levels work correctly
- [ ] Logs show detailed skip reasons (PV, quota status)
- [ ] Wallet balances are correct
- [ ] Activity logs are created for successful bonuses only

---

## Phase 4: Admin Interface for Configuration

**Goal**: Create admin pages for package quotas and quota reporting (Product PV already exists)

**Estimated Time**: 2-3 hours

**NOTE**: Product PV (points_awarded) configuration already exists at `/admin/products/{slug}/edit`

### 4.1 Admin Routes

**File**: `routes/web.php`

Add to admin routes group:
```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    // ... existing admin routes
    
    // Monthly Quota Management
    Route::prefix('admin/monthly-quota')->name('admin.monthly-quota.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\MonthlyQuotaController::class, 'index'])
            ->name('index');
        // NOTE: Product PV management already exists at /admin/products/{slug}/edit
        // No need for separate products management page
        Route::get('/packages', [App\Http\Controllers\Admin\MonthlyQuotaController::class, 'packages'])
            ->name('packages');
        Route::post('/packages/{package}/update-quota', [App\Http\Controllers\Admin\MonthlyQuotaController::class, 'updatePackageQuota'])
            ->name('packages.update-quota');
        Route::get('/reports', [App\Http\Controllers\Admin\MonthlyQuotaController::class, 'reports'])
            ->name('reports');
        Route::get('/reports/user/{user}', [App\Http\Controllers\Admin\MonthlyQuotaController::class, 'userReport'])
            ->name('reports.user');
    });
});
```

### 4.2 Admin Controller

**File**: `app/Http/Controllers/Admin/MonthlyQuotaController.php`

```php
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
     * NOTE: Product PV management is NOT needed here.
     * Admins can edit points_awarded directly in the existing product edit page:
     * /admin/products/{slug}/edit
     */

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
```

### 4.3 Admin Views

#### 4.3.1 Dashboard View
**File**: `resources/views/admin/monthly-quota/index.blade.php`

```blade
@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Monthly Quota System</h1>
        <div>
            <a href="{{ route('admin.products.index') }}" class="btn btn-primary">
                <i class="fas fa-box"></i> Manage Product PV (Existing)
            </a>
            <a href="{{ route('admin.monthly-quota.packages') }}" class="btn btn-info">
                <i class="fas fa-gift"></i> Manage Package Quotas
            </a>
            <a href="{{ route('admin.monthly-quota.reports') }}" class="btn btn-success">
                <i class="fas fa-chart-bar"></i> View Reports
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Active Users</h5>
                    <h2>{{ number_format($stats['total_active_users']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Quota Met This Month</h5>
                    <h2>{{ number_format($stats['users_met_quota']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Quota Not Met</h5>
                    <h2>{{ number_format($stats['users_not_met_quota']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Compliance Rate</h5>
                    <h2>{{ $stats['quota_compliance_rate'] }}%</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Summary -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Configuration Status</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Products with PV
                            <span class="badge bg-primary rounded-pill">{{ $stats['total_products_with_pv'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Packages with Quota
                            <span class="badge bg-info rounded-pill">{{ $stats['total_packages_with_quota'] }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Top Performers ({{ now()->format('F Y') }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Total PV</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTrackers as $tracker)
                                <tr>
                                    <td>{{ $tracker->user->username ?? 'N/A' }}</td>
                                    <td>{{ number_format($tracker->total_pv_points, 2) }}</td>
                                    <td>
                                        @if($tracker->quota_met)
                                            <span class="badge bg-success">Met</span>
                                        @else
                                            <span class="badge bg-warning">Not Met</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works -->
    <div class="card">
        <div class="card-header">
            <h5>How Monthly Quota System Works</h5>
        </div>
        <div class="card-body">
            <ol>
                <li><strong>Assign PV to Products:</strong> Each product is assigned a Point Value (PV).</li>
                <li><strong>Set Package Quotas:</strong> Each starter package defines a monthly PV quota requirement.</li>
                <li><strong>Track User Purchases:</strong> System tracks PV accumulated by users each month.</li>
                <li><strong>Validate Qualification:</strong> Users earn Unilevel bonuses only if they're active AND have met their monthly quota.</li>
                <li><strong>Monthly Reset:</strong> PV tracking resets at the beginning of each month.</li>
            </ol>
        </div>
    </div>
</div>
@endsection
```

#### 4.3.2 NOTE: Product PV Management Already Exists

**Product PV (points_awarded) can be edited at**: `/admin/products/{slug}/edit`

The existing product edit page already has a "Points Awarded" field that serves as the PV (Personal Volume) for the quota system.

**No need to create** `resources/views/admin/monthly-quota/products.blade.php`

**Admins can**:
- Navigate to Products → Edit any product
- Update the "Points Awarded" field (supports decimals: 0.01 to 9999.99)
- This value is used directly for monthly quota calculations

#### 4.3.3 Packages Management View
**File**: `resources/views/admin/monthly-quota/packages.blade.php`

```blade
@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Package Monthly Quotas</h1>
        <a href="{{ route('admin.monthly-quota.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5>Package Monthly Quota Requirements</h5>
            <p class="text-muted mb-0">Configure monthly PV quota for each starter package. Users must meet this quota to earn Unilevel bonuses.</p>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="20%">Package Name</th>
                            <th width="15%">Price</th>
                            <th width="10%">MLM Package</th>
                            <th width="15%">Current Quota</th>
                            <th width="15%">New Quota (PV)</th>
                            <th width="10%">Enforce</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packages as $package)
                        <tr>
                            <td>{{ $package->id }}</td>
                            <td>{{ $package->name }}</td>
                            <td>₱{{ number_format($package->price, 2) }}</td>
                            <td>
                                @if($package->is_mlm_package)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $package->enforce_monthly_quota ? 'bg-warning' : 'bg-secondary' }}">
                                    {{ number_format($package->monthly_quota_points, 2) }} PV
                                    @if($package->enforce_monthly_quota)
                                        (Enforced)
                                    @else
                                        (Disabled)
                                    @endif
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.monthly-quota.packages.update-quota', $package) }}" class="d-inline">
                                    @csrf
                                    <input type="number" name="monthly_quota_points" step="0.01" min="0" max="9999.99" 
                                           value="{{ $package->monthly_quota_points }}" class="form-control form-control-sm" 
                                           style="width: 100px; display: inline-block;" required>
                            </td>
                            <td>
                                    <select name="enforce_monthly_quota" class="form-select form-select-sm" required>
                                        <option value="0" {{ !$package->enforce_monthly_quota ? 'selected' : '' }}>No</option>
                                        <option value="1" {{ $package->enforce_monthly_quota ? 'selected' : '' }}>Yes</option>
                                    </select>
                            </td>
                            <td>
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-save"></i> Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Explanation -->
    <div class="card mt-4">
        <div class="card-header">
            <h5>How It Works</h5>
        </div>
        <div class="card-body">
            <ul>
                <li><strong>Monthly Quota Points:</strong> The PV amount users must accumulate each month</li>
                <li><strong>Enforce:</strong> When enabled, users MUST meet quota to earn Unilevel bonuses</li>
                <li><strong>When Disabled:</strong> Users only need to be "active" (purchased package) to earn bonuses</li>
                <li><strong>Flexibility:</strong> Different packages can have different requirements</li>
            </ul>
            
            <div class="alert alert-info mt-3">
                <strong>Example:</strong> If "Starter Package" has 100 PV monthly quota (enforced), 
                users who purchased this package must accumulate 100 PV through personal product purchases each month 
                to remain eligible for Unilevel bonuses from their downline.
            </div>
        </div>
    </div>
</div>
@endsection
```

### 4.4 Testing Phase 4

**Manual Testing Checklist**:
- [ ] Admin can access all monthly quota pages
- [ ] Product PV can be updated via existing `/admin/products/{slug}/edit` page
- [ ] Package quota update works and saves correctly
- [ ] Dashboard shows correct statistics
- [ ] Activity logs are created for configuration changes
- [ ] Validation works (min/max values, required fields)
- [ ] Changes reflect immediately in database
- [ ] UI is responsive and user-friendly

---

## Phase 5: Member Dashboard & Notifications

**Goal**: Create member-facing pages to view quota status and implement notifications

**Estimated Time**: 3-4 hours

### 5.1 Member Routes

**File**: `routes/web.php`

```php
Route::middleware(['auth'])->group(function () {
    // ... existing member routes
    
    // Monthly Quota Status
    Route::get('/my-quota', [App\Http\Controllers\Member\QuotaController::class, 'index'])
        ->name('member.quota.index');
    Route::get('/my-quota/history', [App\Http\Controllers\Member\QuotaController::class, 'history'])
        ->name('member.quota.history');
});
```

### 5.2 Member Controller

**File**: `app/Http/Controllers/Member/QuotaController.php`

```php
<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Services\MonthlyQuotaService;
use Illuminate\Http\Request;

class QuotaController extends Controller
{
    protected MonthlyQuotaService $quotaService;

    public function __construct(MonthlyQuotaService $quotaService)
    {
        $this->quotaService = $quotaService;
    }

    /**
     * Show current month quota status
     */
    public function index()
    {
        $user = auth()->user();
        $status = $this->quotaService->getUserMonthlyStatus($user);
        
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

        // Get recent PV-earning orders
        $recentOrders = $user->orders()
            ->where('payment_status', 'paid')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->whereHas('orderItems', function($q) {
                $q->whereNotNull('product_id')
                  ->whereHas('product', function($q2) {
                      $q2->where('points_awarded', '>', 0);
                  });
            })
            ->with(['orderItems.product'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function($order) {
                $pv = $order->orderItems
                    ->filter(fn($item) => $item->product && $item->product->points_awarded > 0)
                    ->sum(fn($item) => $item->product->points_awarded * $item->quantity);
                
                return [
                    'order_number' => $order->order_number,
                    'date' => $order->created_at,
                    'pv_earned' => $pv,
                    'products' => $order->orderItems
                        ->filter(fn($item) => $item->product)
                        ->map(fn($item) => $item->product->name)
                        ->join(', '),
                ];
            });

        return view('member.quota.index', compact('status', 'package', 'recentOrders'));
    }

    /**
     * Show quota history
     */
    public function history()
    {
        $user = auth()->user();
        $history = $this->quotaService->getUserQuotaHistory($user, 12);

        return view('member.quota.history', compact('history'));
    }
}
```

### 5.3 Member Views

#### 5.3.1 Current Month Status View
**File**: `resources/views/member/quota/index.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">My Monthly Quota Status</h1>

    <!-- Current Status Card -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ $status['month_name'] }} {{ $status['year'] }} - Quota Progress</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Progress: {{ number_format($status['total_pv'], 2) }} / {{ number_format($status['required_quota'], 2) }} PV</span>
                            <span class="fw-bold">{{ number_format($status['progress_percentage'], 1) }}%</span>
                        </div>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar {{ $status['quota_met'] ? 'bg-success' : 'bg-warning' }}" 
                                 role="progressbar" 
                                 style="width: {{ min(100, $status['progress_percentage']) }}%"
                                 aria-valuenow="{{ $status['progress_percentage'] }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ number_format($status['progress_percentage'], 1) }}%
                            </div>
                        </div>
                    </div>

                    <div class="row text-center mt-4">
                        <div class="col-md-4">
                            <h3 class="text-primary">{{ number_format($status['total_pv'], 2) }}</h3>
                            <p class="text-muted">PV Earned</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-{{ $status['quota_met'] ? 'success' : 'warning' }}">
                                {{ number_format($status['remaining_pv'], 2) }}
                            </h3>
                            <p class="text-muted">PV Remaining</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-info">{{ number_format($status['required_quota'], 2) }}</h3>
                            <p class="text-muted">Required Quota</p>
                        </div>
                    </div>

                    @if($status['quota_met'])
                    <div class="alert alert-success mt-4">
                        <i class="fas fa-check-circle"></i> <strong>Congratulations!</strong> 
                        You have met your monthly quota and are eligible to earn Unilevel bonuses!
                    </div>
                    @else
                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Quota Not Met</strong> 
                        You need {{ number_format($status['remaining_pv'], 2) }} more PV to qualify for Unilevel bonuses this month.
                    </div>
                    @endif

                    @if($status['last_purchase_at'])
                    <p class="text-muted mt-3">
                        Last Purchase: {{ $status['last_purchase_at']->format('M d, Y h:i A') }} 
                        ({{ $status['last_purchase_at']->diffForHumans() }})
                    </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Package Info -->
            @if($package)
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Your Package</h6>
                </div>
                <div class="card-body">
                    <h5>{{ $package->name }}</h5>
                    <p class="mb-2">
                        <strong>Monthly Quota:</strong> {{ number_format($package->monthly_quota_points, 2) }} PV
                    </p>
                    <p class="mb-0">
                        <strong>Quota Enforced:</strong> 
                        @if($package->enforce_monthly_quota)
                            <span class="badge bg-warning">Yes</span>
                        @else
                            <span class="badge bg-success">No</span>
                        @endif
                    </p>
                </div>
            </div>
            @endif

            <!-- Qualification Status -->
            <div class="card">
                <div class="card-header {{ $status['qualifies_for_bonus'] ? 'bg-success' : 'bg-danger' }} text-white">
                    <h6 class="mb-0">Bonus Qualification</h6>
                </div>
                <div class="card-body">
                    @if($status['qualifies_for_bonus'])
                        <p class="text-success mb-0">
                            <i class="fas fa-check-circle fa-3x mb-2"></i><br>
                            <strong>QUALIFIED</strong><br>
                            You can earn Unilevel bonuses!
                        </p>
                    @else
                        <p class="text-danger mb-0">
                            <i class="fas fa-times-circle fa-3x mb-2"></i><br>
                            <strong>NOT QUALIFIED</strong><br>
                            Meet your quota to qualify.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent PV-Earning Orders -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Recent PV-Earning Orders (This Month)</h5>
        </div>
        <div class="card-body">
            @if($recentOrders->count() > 0)
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Products</th>
                            <th>PV Earned</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                        <tr>
                            <td>{{ $order['order_number'] }}</td>
                            <td>{{ $order['date']->format('M d, Y') }}</td>
                            <td>{{ $order['products'] }}</td>
                            <td><span class="badge bg-success">+{{ number_format($order['pv_earned'], 2) }} PV</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-muted text-center">No PV-earning orders this month.</p>
            @endif
        </div>
        <div class="card-footer">
            <a href="{{ route('member.quota.history') }}" class="btn btn-sm btn-outline-primary">
                View Quota History
            </a>
        </div>
    </div>
</div>
@endsection
```

#### 5.3.2 Quota History View
**File**: `resources/views/member/quota/history.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Monthly Quota History</h1>
        <a href="{{ route('member.quota.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Current Status
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Last 12 Months</h5>
        </div>
        <div class="card-body">
            @if($history->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Total PV</th>
                            <th>Required Quota</th>
                            <th>Progress</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $record)
                        <tr>
                            <td>{{ $record['month_name'] }} {{ $record['year'] }}</td>
                            <td>{{ number_format($record['total_pv'], 2) }}</td>
                            <td>{{ number_format($record['required_quota'], 2) }}</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar {{ $record['quota_met'] ? 'bg-success' : 'bg-warning' }}" 
                                         role="progressbar" 
                                         style="width: {{ min(100, $record['progress_percentage']) }}%">
                                        {{ number_format($record['progress_percentage'], 1) }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($record['quota_met'])
                                    <span class="badge bg-success">Met</span>
                                @else
                                    <span class="badge bg-warning">Not Met</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-muted text-center">No quota history available.</p>
            @endif
        </div>
    </div>
</div>
@endsection
```

### 5.4 Email Notifications

**File**: `app/Notifications/MonthlyQuotaNotMet.php`

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MonthlyQuotaNotMet extends Notification
{
    use Queueable;

    protected array $status;

    public function __construct(array $status)
    {
        $this->status = $status;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Monthly Quota Reminder - ' . $this->status['month_name'] . ' ' . $this->status['year'])
            ->greeting('Hello ' . $notifiable->username . '!')
            ->line('This is a friendly reminder about your monthly quota status.')
            ->line('**Current Progress:** ' . number_format($this->status['total_pv'], 2) . ' / ' . number_format($this->status['required_quota'], 2) . ' PV')
            ->line('**Remaining:** ' . number_format($this->status['remaining_pv'], 2) . ' PV')
            ->line('You need to accumulate ' . number_format($this->status['remaining_pv'], 2) . ' more PV this month to qualify for Unilevel bonuses.')
            ->action('Shop Now', url('/products'))
            ->line('Purchase products to earn PV and meet your monthly quota!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'monthly_quota_reminder',
            'status' => $this->status,
        ];
    }
}
```

**File**: `app/Notifications/MonthlyQuotaMet.php`

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MonthlyQuotaMet extends Notification
{
    use Queueable;

    protected array $status;

    public function __construct(array $status)
    {
        $this->status = $status;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Congratulations! Monthly Quota Met - ' . $this->status['month_name'] . ' ' . $this->status['year'])
            ->greeting('Congratulations ' . $notifiable->username . '!')
            ->line('You have successfully met your monthly quota!')
            ->line('**Total PV Earned:** ' . number_format($this->status['total_pv'], 2) . ' PV')
            ->line('**Required Quota:** ' . number_format($this->status['required_quota'], 2) . ' PV')
            ->line('You are now qualified to earn Unilevel bonuses from your downline\'s purchases this month.')
            ->action('View My Quota', url('/my-quota'))
            ->line('Keep up the great work!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'monthly_quota_met',
            'status' => $this->status,
        ];
    }
}
```

### 5.5 Send Notification When Quota is Met

Update `MonthlyQuotaService::addPointsToUser()`:

```php
// After $tracker->checkQuotaMet();
$wasNotMet = !$previouslyMet;
$isNowMet = $tracker->quota_met;

if ($wasNotMet && $isNowMet) {
    // User just met quota, send congratulations notification
    $user->notify(new \App\Notifications\MonthlyQuotaMet($this->getUserMonthlyStatus($user)));
    
    Log::info('User met monthly quota - notification sent', [
        'user_id' => $user->id,
        'username' => $user->username,
    ]);
}
```

### 5.6 Testing Phase 5

**Manual Testing Checklist**:
- [ ] Member can view current quota status page
- [ ] Progress bar displays correctly
- [ ] Recent orders show correct PV values
- [ ] Quota history page displays past months
- [ ] Notification sent when quota is met
- [ ] Email notification received and formatted correctly
- [ ] Database notification created
- [ ] Links in notifications work correctly
- [ ] UI is responsive on mobile devices

---

## Phase 6: Scheduled Tasks & Automation

**Goal**: Implement automated monthly resets and reminder notifications

**Estimated Time**: 2-3 hours

### 6.1 Create Standalone CRON Scripts

**IMPORTANT**: For Hostinger, use direct PHP script execution:
```
/usr/bin/php /home/u938213108/public_html/s2/crons/reset_monthly_quota.php
```

**Why standalone PHP scripts?**
- ✅ Works perfectly with Hostinger's CRON system
- ✅ No web server overhead
- ✅ Direct execution via PHP CLI
- ✅ Easy to test via SSH
- ✅ No need for URL routes or security tokens

#### 6.1.1 Standalone PHP Script for Monthly Reset
**File**: `crons/reset_monthly_quota.php`

```php
<?php
/**
 * Monthly Quota Reset CRON Script
 * 
 * Hostinger CRON Setup:
 * /usr/bin/php /home/u938213108/public_html/s2/crons/reset_monthly_quota.php
 * Schedule: 1 0 1 * * (1st of month, 12:01 AM)
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\MonthlyQuotaTracker;
use Illuminate\Support\Facades\Log;

echo "=== Monthly Quota Reset CRON Job ===\n";
echo "Started at: " . now()->toDateTimeString() . "\n\n";

$activeUsers = User::where('network_status', 'active')->get();
$created = 0;

foreach ($activeUsers as $user) {
    $year = now()->year;
    $month = now()->month;

    // Create new tracker for current month
    $tracker = MonthlyQuotaTracker::firstOrCreate(
        [
            'user_id' => $user->id,
            'year' => $year,
            'month' => $month,
        ],
        [
            'total_pv_points' => 0,
            'required_quota' => $user->getMonthlyQuotaRequirement(),
            'quota_met' => false,
        ]
    );

    if ($tracker->wasRecentlyCreated) {
        $created++;
    }
}

echo "Active users: {$activeUsers->count()}\n";
echo "New trackers created: {$created}\n";
echo "Completed at: " . now()->toDateTimeString() . "\n";

Log::info('Monthly quota reset completed via CRON', [
    'active_users' => $activeUsers->count(),
    'new_trackers' => $created,
    'year' => now()->year,
    'month' => now()->month,
]);
```

#### 6.1.2 Option B: Laravel Console Command (Alternative)
**File**: `app/Console/Commands/ResetMonthlyQuotas.php`

```php
<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\MonthlyQuotaTracker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResetMonthlyQuotas extends Command
{
    protected $signature = 'quota:reset-monthly';
    protected $description = 'Reset monthly quotas for all active users (run on 1st of each month)';

    public function handle()
    {
        $this->info('Starting monthly quota reset...');

        $activeUsers = User::where('network_status', 'active')->get();
        $created = 0;

        foreach ($activeUsers as $user) {
            $year = now()->year;
            $month = now()->month;

            // Create new tracker for current month
            $tracker = MonthlyQuotaTracker::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'year' => $year,
                    'month' => $month,
                ],
                [
                    'total_pv_points' => 0,
                    'required_quota' => $user->getMonthlyQuotaRequirement(),
                    'quota_met' => false,
                ]
            );

            if ($tracker->wasRecentlyCreated) {
                $created++;
            }
        }

        $this->info("Monthly quota reset completed!");
        $this->info("Active users: {$activeUsers->count()}");
        $this->info("New trackers created: {$created}");

        Log::info('Monthly quota reset completed', [
            'active_users' => $activeUsers->count(),
            'new_trackers' => $created,
            'year' => now()->year,
            'month' => now()->month,
        ]);

        return Command::SUCCESS;
    }
}
```

#### 6.1.2 Standalone PHP Script for Quota Reminders
**File**: `crons/send_quota_reminders.php`

```php
<?php
/**
 * Send Quota Reminders CRON Script
 * 
 * Hostinger CRON Setup:
 * /usr/bin/php /home/u938213108/public_html/s2/crons/send_quota_reminders.php
 * Schedule: 0 9 25 * * (25th of month, 9:00 AM)
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MonthlyQuotaTracker;
use App\Notifications\MonthlyQuotaNotMet;
use App\Services\MonthlyQuotaService;
use Illuminate\Support\Facades\Log;

echo "=== Send Quota Reminders CRON Job ===\n";
echo "Started at: " . now()->toDateTimeString() . "\n\n";

// Only send reminders between 20th-28th of month
$today = now()->day;
if ($today < 20 || $today > 28) {
    echo "Reminders only sent between 20th-28th of the month.\n";
    echo "Today is: {$today}th\n";
    exit(0);
}

$year = now()->year;
$month = now()->month;
$quotaService = new MonthlyQuotaService();

// Get users who haven't met quota
$usersNotMet = MonthlyQuotaTracker::with('user')
    ->where('year', $year)
    ->where('month', $month)
    ->where('quota_met', false)
    ->where('required_quota', '>', 0)
    ->get();

$sent = 0;
$skipped = 0;

foreach ($usersNotMet as $tracker) {
    $user = $tracker->user;

    if (!$user || !$user->hasVerifiedEmail()) {
        $skipped++;
        continue;
    }

    try {
        $status = $quotaService->getUserMonthlyStatus($user);
        $user->notify(new MonthlyQuotaNotMet($status));
        $sent++;
        echo "Sent reminder to: {$user->username}\n";
    } catch (\Exception $e) {
        echo "Failed to send reminder to {$user->username}: " . $e->getMessage() . "\n";
        Log::error('Failed to send quota reminder', [
            'user_id' => $user->id,
            'error' => $e->getMessage(),
        ]);
    }
}

echo "\nReminders sent: {$sent}\n";
echo "Skipped (no email): {$skipped}\n";
echo "Completed at: " . now()->toDateTimeString() . "\n";

Log::info('Quota reminders sent via CRON', [
    'sent' => $sent,
    'skipped' => $skipped,
    'total_not_met' => $usersNotMet->count(),
]);
```

#### 6.1.4 Option B: Laravel Console Command for Reminders (Alternative)
**File**: `app/Console/Commands/SendQuotaReminders.php`

```php
<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\MonthlyQuotaTracker;
use App\Notifications\MonthlyQuotaNotMet;
use App\Services\MonthlyQuotaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendQuotaReminders extends Command
{
    protected $signature = 'quota:send-reminders {--force : Send reminders regardless of date}';
    protected $description = 'Send reminder notifications to users who haven\'t met their monthly quota';

    protected MonthlyQuotaService $quotaService;

    public function __construct(MonthlyQuotaService $quotaService)
    {
        parent::__construct();
        $this->quotaService = $quotaService;
    }

    public function handle()
    {
        // Only send reminders between 20th-28th of month (or if --force)
        $today = now()->day;
        if (!$this->option('force') && ($today < 20 || $today > 28)) {
            $this->info('Quota reminders are only sent between 20th-28th of the month.');
            return Command::SUCCESS;
        }

        $this->info('Sending quota reminders...');

        $year = now()->year;
        $month = now()->month;

        // Get users who haven't met quota
        $usersNotMet = MonthlyQuotaTracker::with('user')
            ->where('year', $year)
            ->where('month', $month)
            ->where('quota_met', false)
            ->where('required_quota', '>', 0) // Only users with quota requirement
            ->get();

        $sent = 0;
        $skipped = 0;

        foreach ($usersNotMet as $tracker) {
            $user = $tracker->user;

            if (!$user || !$user->hasVerifiedEmail()) {
                $skipped++;
                continue;
            }

            try {
                $status = $this->quotaService->getUserMonthlyStatus($user);
                $user->notify(new MonthlyQuotaNotMet($status));
                $sent++;

                $this->info("Sent reminder to: {$user->username}");

            } catch (\Exception $e) {
                $this->error("Failed to send reminder to {$user->username}: " . $e->getMessage());
                Log::error('Failed to send quota reminder', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("\nQuota reminders completed!");
        $this->info("Sent: {$sent}");
        $this->info("Skipped (no email): {$skipped}");

        Log::info('Quota reminders sent', [
            'sent' => $sent,
            'skipped' => $skipped,
            'total_not_met' => $usersNotMet->count(),
        ]);

        return Command::SUCCESS;
    }
}
```

### 6.2 CRON Job Setup - Two Options Available

**IMPORTANT**: You have **two approaches** to schedule these automated tasks. Choose based on your familiarity with Laravel:

---

#### **Option A: Direct PHP Script Execution (Traditional, Recommended for Beginners)**

**Best for**: Those familiar with traditional cron jobs and direct PHP execution.  
**Pros**: Simple, transparent, no Laravel Scheduler knowledge needed.  
**Cons**: Multiple cron entries needed (one per task).

This is the recommended approach for Hostinger and most shared hosting environments when starting out.

#### Hostinger hPanel CRON Setup

**Step 1: Access CRON Jobs**
1. Log into Hostinger hPanel
2. Navigate to: **Advanced → Cron Jobs**
3. Click: **"Create Cron Job"**

**Step 2: Task 1 - Reset Monthly Quotas**
```
Type: Common Settings
Command: /usr/bin/php /home/u938213108/public_html/s2/crons/reset_monthly_quota.php

Schedule:
  Minute: 1
  Hour: 0
  Day: 1
  Month: * (Every month)
  Weekday: * (Every day of week)
  
Email notification: (Optional - your email)
```

**Explanation**: Runs on the 1st of every month at 12:01 AM

**Step 3: Task 2 - Send Quota Reminders**
```
Type: Common Settings
Command: /usr/bin/php /home/u938213108/public_html/s2/crons/send_quota_reminders.php

Schedule:
  Minute: 0
  Hour: 9
  Day: 25
  Month: * (Every month)
  Weekday: * (Every day of week)
  
Email notification: (Optional - your email)
```

**Explanation**: Runs on the 25th of every month at 9:00 AM

**Important Notes:**
- Replace `/home/u938213108/public_html/s2/` with your actual path
- Path format: `/home/YOUR_USERNAME/public_html/YOUR_PROJECT/crons/script.php`
- PHP path is typically `/usr/bin/php` on Hostinger
- Scripts must be in `crons/` folder relative to project root

**Windows Task Scheduler** (Local Development):
```
Task 1: Reset Monthly Quotas
  Trigger: Monthly, 1st day, 12:01 AM
  Action: C:\laragon\bin\php\php-8.x\php.exe
  Arguments: C:\laragon\www\s2_gawis2\cron_reset_monthly_quota.php

Task 2: Send Reminders
  Trigger: Monthly, 25th day, 9:00 AM
  Action: C:\laragon\bin\php\php-8.x\php.exe
  Arguments: C:\laragon\www\s2_gawis2\cron_send_quota_reminders.php
```

**Linux VPS/Dedicated (crontab - SSH access)**:
```bash
# Edit crontab
crontab -e

# Reset quotas on 1st at 12:01 AM
1 0 1 * * /usr/bin/php /home/u938213108/public_html/s2/crons/reset_monthly_quota.php

# Send reminders on 25th at 9:00 AM
0 9 25 * * /usr/bin/php /home/u938213108/public_html/s2/crons/send_quota_reminders.php
```

---

#### **Option B: Laravel Scheduler (Modern Laravel Way - Recommended for Learning)**

**Best for**: Learning Laravel's ecosystem; managing multiple scheduled tasks elegantly.  
**Pros**: Single cron entry; all tasks in one place; built-in logging; easier to test; Laravel best practice.  
**Cons**: Requires understanding Laravel Scheduler; one extra layer of abstraction.

**Why this option is worth considering**: Since you're working in Laravel, this is the **"Laravel way"** and provides better long-term maintainability and learning value.

**Step 1: Register Commands in Laravel's Task Scheduler**

**File**: `app/Console/Kernel.php`

Update the `schedule()` method to add your scheduled tasks:

```php
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Reset monthly quotas on 1st of each month at 12:01 AM
        $schedule->command('quota:reset-monthly')
            ->monthlyOn(1, '00:01')
            ->timezone('Asia/Manila') // Set your timezone
            ->appendOutputTo(storage_path('logs/cron-quota-reset.log'));

        // Send quota reminders on 25th of each month at 9:00 AM
        $schedule->command('quota:send-reminders')
            ->monthlyOn(25, '09:00')
            ->timezone('Asia/Manila')
            ->appendOutputTo(storage_path('logs/cron-quota-reminders.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
```

**Key Methods Explained**:
- `->monthlyOn(day, time)` - Run on specific day of month at specific time
- `->timezone('Asia/Manila')` - Set timezone for scheduling
- `->appendOutputTo(path)` - Save command output to log file for debugging

**Other useful scheduling options** (for reference):
```php
// Examples of other scheduling patterns you can use:
$schedule->command('your:command')->everyMinute();
$schedule->command('your:command')->hourly();
$schedule->command('your:command')->daily();
$schedule->command('your:command')->dailyAt('13:00');
$schedule->command('your:command')->weekly();
$schedule->command('your:command')->weeklyOn(1, '8:00'); // Monday at 8am
$schedule->command('your:command')->monthly();
$schedule->command('your:command')->quarterly();
$schedule->command('your:command')->yearly();
```

**Step 2: Set Up Single CRON Job in Hostinger**

With Laravel Scheduler, you only need **ONE** cron job that runs every minute. Laravel then decides which tasks to execute based on your schedule.

**Add this in Hostinger hPanel (Advanced → Cron Jobs)**:

```
Command: cd /home/u938213108/public_html/s2 && /usr/bin/php artisan schedule:run >> /dev/null 2>&1

Schedule:
  Minute: * (Every minute)
  Hour: * (Every hour)
  Day: * (Every day)
  Month: * (Every month)
  Weekday: * (Every weekday)
```

**Important Notes**:
- This cron job runs every minute
- Laravel checks if any scheduled tasks should run
- If a task is scheduled for that time, Laravel executes it
- If no tasks are scheduled, nothing happens (very efficient)
- All scheduling logic is in your code, not in crontab

**For Local Development (Windows Task Scheduler)**:
```
Trigger: Every 1 minute (repeat indefinitely)
Action: C:\laragon\bin\php\php-8.x\php.exe
Arguments: C:\laragon\www\s2_gawis2\artisan schedule:run
```

**For Linux VPS/Dedicated (crontab)**:
```bash
# Edit crontab
crontab -e

# Add single Laravel Scheduler entry
* * * * * cd /home/u938213108/public_html/s2 && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

**Step 3: Test Locally Before Deploying**

Laravel provides excellent testing commands:

```bash
# Navigate to project
cd C:\laragon\www\s2_gawis2

# Test individual commands directly
php artisan quota:reset-monthly
php artisan quota:send-reminders --force

# See all scheduled tasks and their next run times
php artisan schedule:list

# Example output:
# 0 1 1 * * ................ quota:reset-monthly ........... Next Due: 1 month from now
# 0 9 25 * * ............... quota:send-reminders .......... Next Due: 10 days from now

# Run the scheduler manually (simulates what cron will do)
php artisan schedule:run

# Watch scheduler in real-time (keeps running, checks every minute)
php artisan schedule:work
```

**Step 4: Verify on Production (Hostinger)**

After setting up the cron job in Hostinger, verify it's working:

```bash
# SSH into your server
ssh u938213108@your-domain.com

# Navigate to project
cd /home/u938213108/public_html/s2

# Check scheduled tasks
php artisan schedule:list

# Manually trigger scheduler to test
php artisan schedule:run

# Check the log files
tail -f storage/logs/cron-quota-reset.log
tail -f storage/logs/cron-quota-reminders.log

# Check Laravel logs for any errors
tail -f storage/logs/laravel.log
```

**Step 5: Monitor Scheduler Execution**

You can verify the scheduler is running by checking logs:

```bash
# Check if scheduler is being called
grep "schedule:run" storage/logs/laravel.log

# Check quota reset logs
cat storage/logs/cron-quota-reset.log

# Check reminder logs  
cat storage/logs/cron-quota-reminders.log
```

---

### **Comparison: Option A vs Option B**

| Feature | Option A (Direct PHP Scripts) | Option B (Laravel Scheduler) |
|---------|------------------------------|------------------------------|
| **Setup Complexity** | Simple - just create scripts | Moderate - need to understand Scheduler |
| **CRON Entries** | 2 separate entries (one per task) | **1 single entry** (Laravel manages all) |
| **Adding More Tasks** | Create new script + new cron entry | Just add to `schedule()` method |
| **Testing** | Test each script individually | Use `schedule:list` and `schedule:run` |
| **Debugging** | Easier - direct execution | Check logs + understand scheduler flow |
| **Maintenance** | Manual updates to each cron | Update code only; cron stays the same |
| **Flexibility** | Direct control per task | Centralized in Kernel.php |
| **Logging** | Manual echo statements | Built-in log files per task |
| **Timezone Support** | Manual (server timezone) | Built-in timezone configuration |
| **Laravel Best Practice** | ❌ Not the Laravel way | ✅ **Recommended Laravel approach** |
| **Learning Value** | ❌ Limited | ✅ **High - learn Laravel ecosystem** |
| **Scalability** | Harder with many tasks | **Easy - unlimited tasks, one cron** |
| **Best For** | **Quick setup, beginners** | **Long-term projects, Laravel learning** |

### **Our Recommendation**

Since you mentioned you're **new to Laravel Scheduler** but **working in Laravel**, we suggest:

**Short Term (Now)**: Start with **Option A (Direct PHP Scripts)**
- Get it working quickly with familiar approach
- No learning curve - just traditional cron jobs
- Easy to understand and debug

**Long Term (Later)**: Migrate to **Option B (Laravel Scheduler)**
- Learn Laravel best practices
- Better maintainability as project grows
- Single cron entry for all tasks
- Easier to add more scheduled tasks in future

**Why Option B is worth learning**:
1. **Single cron entry** - Configure once, manage tasks in code
2. **Easy testing** - `php artisan schedule:list` shows what will run and when
3. **Built-in logging** - Each task gets its own log file
4. **Cleaner codebase** - All scheduling logic in one place (Kernel.php)
5. **Laravel ecosystem** - Integrates seamlessly with Laravel features
6. **Professional approach** - Industry standard for Laravel applications

**Migration Path**:
1. Implement Option A now (15 minutes)
2. Learn Laravel Scheduler basics (read SCHEDULER.md)
3. Test Option B locally with `schedule:work`
4. When comfortable, switch production to Option B
5. Remove individual cron jobs, keep only `schedule:run`

---

### 6.3 Testing CRON Scripts (Both Options)

**Before setting up CRON jobs, test the scripts manually:**

#### Via SSH (Hostinger Terminal)
```bash
# Navigate to project root
cd /home/u938213108/public_html/s2

# Test reset script
/usr/bin/php crons/reset_monthly_quota.php

# Test reminders script
/usr/bin/php crons/send_quota_reminders.php
```

#### Via Local Development (Laragon/XAMPP)
```bash
# Open terminal in project root
cd C:\laragon\www\s2_gawis2

# Test reset script
php crons/reset_monthly_quota.php

# Test reminders script  
php crons/send_quota_reminders.php
```

**Expected Output**:
```
=== Monthly Quota Reset CRON Job ===
Started at: 2025-11-15 12:01:00

Creating trackers for active users...
Active users: 25
New trackers created: 25
Completed at: 2025-11-15 12:01:05
```

**Verify in Database**:
```sql
SELECT * FROM monthly_quota_tracker 
WHERE year = 2025 AND month = 11 
ORDER BY created_at DESC LIMIT 10;
```

### 6.4 Verify CRON Setup on Hostinger

**After creating the CRON jobs in hPanel:**

**Check if CRON is running:**
```bash
# Linux
sudo service cron status

# View CRON logs
grep CRON /var/log/syslog

# View Laravel logs
tail -f storage/logs/laravel.log
```

**Test scripts manually via SSH:**
```bash
# Navigate to project
cd /home/u938213108/public_html/s2

# Test reset script
/usr/bin/php crons/reset_monthly_quota.php

# Test reminder script
/usr/bin/php crons/send_quota_reminders.php
```

### 6.5 Production Deployment Checklist

Before going live, ensure:
- [ ] CRON job is configured and running
- [ ] Test both commands manually first
- [ ] Verify CRON logs show successful execution
- [ ] Check Laravel logs after scheduled runs
- [ ] Set up monitoring alerts for failed tasks
- [ ] Document CRON setup for future reference

### 6.6 Testing Phase 6

**Test Scripts via SSH**:

```bash
# Navigate to project
cd /home/u938213108/public_html/s2

# Test monthly reset script
/usr/bin/php crons/reset_monthly_quota.php

# Test reminder script
/usr/bin/php crons/send_quota_reminders.php

# Check if scripts executed successfully (check logs)
tail -20 storage/logs/laravel.log

# Test CRON job syntax in Hostinger (add temporarily)
# Minute: */1 (every minute), Hour: *, Day: *, Month: *, Weekday: *
# Command: /usr/bin/php /home/u938213108/public_html/s2/crons/reset_monthly_quota.php
# Wait 1 minute, check database for new trackers
```

**Manual Testing Checklist**:
- [ ] Reset script creates new trackers for current month
- [ ] Reset script doesn't duplicate existing trackers
- [ ] Reminder script only sends to users who haven't met quota
- [ ] Reminder script skips users without verified email
- [ ] CRON jobs are added in Hostinger hPanel
- [ ] CRON jobs are scheduled correctly (1st and 25th of month)
- [ ] Scripts execute successfully (check logs)
- [ ] Logs are created for all operations
- [ ] Email notifications are received
- [ ] Database notifications are created
- [ ] Failed executions are logged with errors

**CRON Verification on Hostinger**:
```bash
# SSH into Hostinger
ssh u938213108@yourdomain.com

# Navigate to project
cd /home/u938213108/public_html/s2

# Check if CRON scripts are executable
ls -la crons/

# Test script execution manually
/usr/bin/php crons/reset_monthly_quota.php

# Check Laravel logs for execution
tail -50 storage/logs/laravel.log

# Verify database changes
# Connect to database and check monthly_quota_tracker table
```

---

## Phase 7: Reporting & Analytics (Optional Enhancement)

**Goal**: Advanced reporting and analytics dashboard for admins

**Estimated Time**: 2-3 hours

### 7.1 Enhanced Reports

- Quota compliance trends over time
- Top performers by PV
- Users at risk of not meeting quota
- Product PV performance analysis
- Monthly revenue vs PV correlation

### 7.2 Export Functionality

- CSV export of monthly quota data
- PDF reports for individual users
- Excel export with charts

### 7.3 Alert System

- Admin alerts when compliance drops below threshold
- Automated alerts for users approaching quota
- Weekly digest emails for admins

---

## Implementation Timeline

| Phase | Description | Estimated Time | Dependencies | Key Features |
|-------|-------------|----------------|--------------|-------------|
| Phase 1 | Database Schema & Models | 2-3 hours | None | Tables, indexes, relationships |
| Phase 2 | Real-Time Points Tracking | 2-3 hours | Phase 1 | Synchronous processing, instant updates |
| Phase 3 | Real-Time Unilevel Logic | 1-2 hours | Phase 2 | Live quota checks |
| Phase 4 | Admin Interface | 3-4 hours | Phase 3 | PV/quota configuration |
| Phase 5 | Member Dashboard | 3-4 hours | Phase 4 | Real-time status display |
| Phase 6 | CRON Jobs & Automation | 2-3 hours | Phase 5 | Monthly resets, reminders |
| Phase 7 | Advanced Reporting (Optional) | 2-3 hours | Phase 6 | Analytics, exports |

**Total Estimated Time**: 15-22 hours (without Phase 7)

**Critical Path**:
- Phases 1-3: Core real-time functionality (5-8 hours)
- Phases 4-5: User interfaces (6-8 hours)
- Phase 6: Automation setup + CRON configuration (2-3 hours)

---

## Testing Strategy

### Unit Tests
- `MonthlyQuotaTracker` model methods
- `User` quota-related methods
- `MonthlyQuotaService` calculations

### Integration Tests
- Order → PV tracking flow
- Unilevel bonus distribution with quota check
- Monthly reset automation

### End-to-End Tests
1. User purchases package → becomes active
2. User purchases products → PV accumulates
3. User meets quota → receives notification
4. Downline purchases → Upline earns bonus (if qualified)
5. New month → Quota resets
6. Reminders sent to non-qualified users

---

## Rollback Plan

Each phase can be rolled back independently:

1. **Phase 1 Rollback**: Drop migrations
2. **Phase 2 Rollback**: Remove service, remove job, restore checkout controller
3. **Phase 3 Rollback**: Restore `isNetworkActive()` check in UnilevelBonusService
4. **Phase 4 Rollback**: Remove admin routes and views
5. **Phase 5 Rollback**: Remove member routes and views
6. **Phase 6 Rollback**: Remove scheduled commands

---

## Success Criteria

- [ ] All migrations run successfully
- [ ] PV tracking works for all product purchases
- [ ] Quota status is calculated accurately
- [ ] Unilevel bonuses only distribute to qualified users
- [ ] Admin can configure all settings easily
- [ ] Members can view their quota status
- [ ] Notifications sent at appropriate times
- [ ] Monthly resets happen automatically
- [ ] System logs all operations
- [ ] No performance degradation
- [ ] All tests pass

---

## Post-Implementation Checklist

1. [ ] Database backup before deployment
2. [ ] Run migrations on production
3. [ ] Seed initial product PV values
4. [ ] Configure package quotas
5. [ ] **Configure CRON job on server** (CRITICAL)
6. [ ] Test CRON job is running every minute
7. [ ] Verify scheduled commands execute correctly
8. [ ] Test with real user accounts (real-time tracking)
9. [ ] Test synchronous job execution (no queue delays)
10. [ ] Monitor logs for errors (both Laravel and CRON logs)
11. [ ] Train admins on new features
12. [ ] Announce to members
13. [ ] Monitor first month performance
14. [ ] Verify monthly reset happens on 1st of next month
15. [ ] Verify reminders sent on 25th of month

---

## Support & Maintenance

### Monthly Tasks
- Review quota compliance rates
- Adjust product PV if needed
- Check scheduled task logs
- Review notification delivery rates

### Quarterly Tasks
- Analyze quota effectiveness
- Review and optimize package quotas
- Performance optimization if needed
- User feedback review

---

**END OF DOCUMENT**
