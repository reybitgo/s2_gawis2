# Rank System Phase 1 - Comprehensive Testing Guide

## Overview

This document provides a complete testing procedure to verify Phase 1 implementation before proceeding to Phase 2. Follow these tests sequentially to ensure the rank tracking foundation is solid.

---

## Table of Contents

1. [Pre-Testing Setup](#pre-testing-setup)
2. [Database Schema Verification](#database-schema-verification)
3. [Package Configuration Tests](#package-configuration-tests)
4. [User Rank Assignment Tests](#user-rank-assignment-tests)
5. [Model Functionality Tests](#model-functionality-tests)
6. [Admin UI Protection Tests](#admin-ui-protection-tests)
7. [Relationship Tests](#relationship-tests)
8. [Migration Rollback Tests](#migration-rollback-tests)
9. [Performance Tests](#performance-tests)
10. [Edge Case Tests](#edge-case-tests)
11. [Success Criteria Checklist](#success-criteria-checklist)

---

## Pre-Testing Setup

### Step 1: Fresh Database Installation

```bash
# Start with a clean slate
php artisan migrate:fresh --seed

# Verify migration status
php artisan migrate:status
```

**Expected Output:**
- All migrations should show as "Ran"
- Including the 5 new rank system migrations

### Step 2: Verify Seeders Ran

```bash
# Check packages created
php artisan tinker
>>> \App\Models\Package::where('is_rankable', true)->count()
# Should return: 3

>>> \App\Models\Package::where('is_rankable', true)->pluck('rank_name')
# Should return: ["Starter", "Newbie", "Bronze"]
```

### Step 3: Create Test Users

Create a test script: `setup_rank_test_users.php`

```php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "Creating test users for rank system testing...\n\n";

// Create 10 test users for hierarchy testing
for ($i = 1; $i <= 10; $i++) {
    $user = User::create([
        'username' => "ranktest{$i}",
        'fullname' => "Rank Test User {$i}",
        'email' => "ranktest{$i}@test.com",
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
    ]);
    
    echo "✅ Created user: {$user->username} (ID: {$user->id})\n";
}

echo "\n✅ Test users created successfully!\n";
```

Run it:
```bash
php setup_rank_test_users.php
```

---

## Database Schema Verification

### Test 1: Verify Tables Exist

```sql
-- Run in MySQL/phpMyAdmin or Adminer
SHOW TABLES LIKE '%rank%';
```

**Expected Tables:**
- `rank_advancements`
- `direct_sponsors_tracker`

### Test 2: Verify Users Table Columns

```sql
DESCRIBE users;
```

**Expected New Columns:**
- `current_rank` VARCHAR(100) NULL
- `rank_package_id` BIGINT UNSIGNED NULL
- `rank_updated_at` TIMESTAMP NULL

### Test 3: Verify Packages Table Columns

```sql
DESCRIBE packages;
```

**Expected New Columns:**
- `rank_name` VARCHAR(100) NULL
- `rank_order` INT UNSIGNED DEFAULT 1
- `required_direct_sponsors` INT UNSIGNED DEFAULT 0
- `is_rankable` BOOLEAN DEFAULT TRUE
- `next_rank_package_id` BIGINT UNSIGNED NULL

### Test 4: Verify Foreign Keys

```sql
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 's2_gawis2'
  AND TABLE_NAME IN ('users', 'packages', 'rank_advancements', 'direct_sponsors_tracker')
  AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, COLUMN_NAME;
```

**Expected Foreign Keys:**
- `users.rank_package_id` → `packages.id`
- `packages.next_rank_package_id` → `packages.id`
- `rank_advancements.user_id` → `users.id`
- `rank_advancements.from_package_id` → `packages.id`
- `rank_advancements.to_package_id` → `packages.id`
- `rank_advancements.order_id` → `orders.id`
- `direct_sponsors_tracker.user_id` → `users.id`
- `direct_sponsors_tracker.sponsored_user_id` → `users.id`
- `direct_sponsors_tracker.sponsored_user_package_id` → `packages.id`

### Test 5: Verify Indexes

```sql
SHOW INDEX FROM users WHERE Key_name LIKE '%rank%';
SHOW INDEX FROM packages WHERE Key_name LIKE '%rank%';
SHOW INDEX FROM rank_advancements;
SHOW INDEX FROM direct_sponsors_tracker;
```

**Expected Indexes:**
- Users: `idx_current_rank`, `idx_rank_package_id`
- Packages: `idx_rank_order`, `idx_rank_name`
- Rank Advancements: `idx_user_id_created_at`, `idx_advancement_type`, `idx_to_rank`
- Direct Sponsors Tracker: `idx_user_sponsor_rank`, `idx_user_counted_rank`, `unique_sponsorship`

---

## Package Configuration Tests

### Test 6: Verify Rank Package Creation

```bash
php artisan tinker
```

```php
use App\Models\Package;

// Test 6.1: Check Starter Package
$starter = Package::where('rank_name', 'Starter')->first();
dump([
    'name' => $starter->name,
    'rank_name' => $starter->rank_name,
    'rank_order' => $starter->rank_order,
    'price' => $starter->price,
    'required_direct_sponsors' => $starter->required_direct_sponsors,
    'is_rankable' => $starter->is_rankable,
    'is_mlm_package' => $starter->is_mlm_package,
    'next_rank' => $starter->nextRankPackage?->rank_name,
]);

// Test 6.2: Check Newbie Package
$newbie = Package::where('rank_name', 'Newbie')->first();
dump([
    'name' => $newbie->name,
    'rank_name' => $newbie->rank_name,
    'rank_order' => $newbie->rank_order,
    'price' => $newbie->price,
    'required_direct_sponsors' => $newbie->required_direct_sponsors,
    'next_rank' => $newbie->nextRankPackage?->rank_name,
]);

// Test 6.3: Check Bronze Package
$bronze = Package::where('rank_name', 'Bronze')->first();
dump([
    'name' => $bronze->name,
    'rank_name' => $bronze->rank_name,
    'rank_order' => $bronze->rank_order,
    'price' => $bronze->price,
    'required_direct_sponsors' => $bronze->required_direct_sponsors,
    'next_rank' => $bronze->nextRankPackage?->rank_name, // Should be NULL
]);

// Test 6.4: Verify rank progression chain
echo "Rank Progression Chain:\n";
$current = $starter;
while ($current) {
    echo "  {$current->rank_name} (Order {$current->rank_order})";
    $current = $current->nextRankPackage;
    echo $current ? " → " : " [END]\n";
}
```

**Expected Results:**

**Starter:**
- rank_order: 1
- price: 1000.00
- required_direct_sponsors: 5
- next_rank: "Newbie"

**Newbie:**
- rank_order: 2
- price: 2500.00
- required_direct_sponsors: 8
- next_rank: "Bronze"

**Bronze:**
- rank_order: 3
- price: 5000.00
- required_direct_sponsors: 10
- next_rank: NULL (top rank)

**Chain:**
```
Starter (Order 1) → Newbie (Order 2) → Bronze (Order 3) [END]
```

### Test 7: Verify MLM Settings for Rank Packages

```php
use App\Models\MlmSetting;

// Check Starter MLM settings
$starter = Package::where('rank_name', 'Starter')->first();
$starterSettings = MlmSetting::where('package_id', $starter->id)
    ->orderBy('level')
    ->get(['level', 'commission_amount']);
dump('Starter MLM Settings:', $starterSettings->toArray());

// Check Newbie MLM settings
$newbie = Package::where('rank_name', 'Newbie')->first();
$newbieSettings = MlmSetting::where('package_id', $newbie->id)
    ->orderBy('level')
    ->get(['level', 'commission_amount']);
dump('Newbie MLM Settings:', $newbieSettings->toArray());

// Check Bronze MLM settings
$bronze = Package::where('rank_name', 'Bronze')->first();
$bronzeSettings = MlmSetting::where('package_id', $bronze->id)
    ->orderBy('level')
    ->get(['level', 'commission_amount']);
dump('Bronze MLM Settings:', $bronzeSettings->toArray());
```

**Expected Results:**

**Starter:**
- Level 1: 200.00
- Levels 2-5: 50.00 each

**Newbie:**
- Level 1: 400.00
- Levels 2-5: 100.00 each

**Bronze:**
- Level 1: 800.00
- Levels 2-5: 200.00 each

---

## User Rank Assignment Tests

### Test 8: Test Automatic Rank Assignment on Package Purchase

Create test script: `test_rank_assignment.php`

```php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;

echo "=== Test 8: Automatic Rank Assignment ===\n\n";

$user = User::where('username', 'ranktest1')->first();
$starterPackage = Package::where('rank_name', 'Starter')->first();

echo "User before purchase:\n";
echo "  Username: {$user->username}\n";
echo "  Current Rank: " . ($user->current_rank ?? 'None') . "\n";
echo "  Rank Package ID: " . ($user->rank_package_id ?? 'None') . "\n\n";

// Create a paid order for Starter package
$order = Order::create([
    'user_id' => $user->id,
    'order_number' => 'TEST-' . strtoupper(uniqid()),
    'status' => 'confirmed',
    'payment_status' => 'paid',
    'payment_method' => 'test',
    'subtotal' => $starterPackage->price,
    'grand_total' => $starterPackage->price,
]);

OrderItem::create([
    'order_id' => $order->id,
    'package_id' => $starterPackage->id,
    'quantity' => 1,
    'price' => $starterPackage->price,
    'subtotal' => $starterPackage->price,
]);

echo "Created test order #{$order->order_number}\n\n";

// Update user rank
$user->updateRank();
$user->refresh();

echo "User after purchase:\n";
echo "  Username: {$user->username}\n";
echo "  Current Rank: " . ($user->current_rank ?? 'None') . "\n";
echo "  Rank Package ID: " . ($user->rank_package_id ?? 'None') . "\n";
echo "  Rank Updated At: " . ($user->rank_updated_at ?? 'None') . "\n\n";

if ($user->current_rank === 'Starter' && $user->rank_package_id === $starterPackage->id) {
    echo "✅ PASS: Rank automatically assigned to Starter\n";
} else {
    echo "❌ FAIL: Rank not assigned correctly\n";
}
```

Run it:
```bash
php test_rank_assignment.php
```

### Test 9: Test Rank Upgrade (Multiple Packages)

```php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;

echo "=== Test 9: Rank Upgrade ===\n\n";

$user = User::where('username', 'ranktest2')->first();

// First purchase: Starter
$starter = Package::where('rank_name', 'Starter')->first();
$order1 = Order::create([
    'user_id' => $user->id,
    'order_number' => 'TEST-' . strtoupper(uniqid()),
    'status' => 'confirmed',
    'payment_status' => 'paid',
    'payment_method' => 'test',
    'subtotal' => $starter->price,
    'grand_total' => $starter->price,
]);
OrderItem::create([
    'order_id' => $order1->id,
    'package_id' => $starter->id,
    'quantity' => 1,
    'price' => $starter->price,
    'subtotal' => $starter->price,
]);

$user->updateRank();
$user->refresh();
echo "After Starter purchase: {$user->current_rank}\n";

// Second purchase: Newbie (should upgrade)
$newbie = Package::where('rank_name', 'Newbie')->first();
$order2 = Order::create([
    'user_id' => $user->id,
    'order_number' => 'TEST-' . strtoupper(uniqid()),
    'status' => 'confirmed',
    'payment_status' => 'paid',
    'payment_method' => 'test',
    'subtotal' => $newbie->price,
    'grand_total' => $newbie->price,
]);
OrderItem::create([
    'order_id' => $order2->id,
    'package_id' => $newbie->id,
    'quantity' => 1,
    'price' => $newbie->price,
    'subtotal' => $newbie->price,
]);

$user->updateRank();
$user->refresh();
echo "After Newbie purchase: {$user->current_rank}\n";

// Third purchase: Bronze (should upgrade to top)
$bronze = Package::where('rank_name', 'Bronze')->first();
$order3 = Order::create([
    'user_id' => $user->id,
    'order_number' => 'TEST-' . strtoupper(uniqid()),
    'status' => 'confirmed',
    'payment_status' => 'paid',
    'payment_method' => 'test',
    'subtotal' => $bronze->price,
    'grand_total' => $bronze->price,
]);
OrderItem::create([
    'order_id' => $order3->id,
    'package_id' => $bronze->id,
    'quantity' => 1,
    'price' => $bronze->price,
    'subtotal' => $bronze->price,
]);

$user->updateRank();
$user->refresh();
echo "After Bronze purchase: {$user->current_rank}\n\n";

if ($user->current_rank === 'Bronze' && $user->rank_package_id === $bronze->id) {
    echo "✅ PASS: User upgraded through all ranks correctly\n";
} else {
    echo "❌ FAIL: Rank upgrade failed\n";
}
```

### Test 10: Test Non-Sequential Purchase (Buy Bronze Directly)

```php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;

echo "=== Test 10: Non-Sequential Purchase ===\n\n";

$user = User::where('username', 'ranktest3')->first();
$bronze = Package::where('rank_name', 'Bronze')->first();

echo "User directly purchases Bronze (skipping Starter and Newbie)...\n\n";

$order = Order::create([
    'user_id' => $user->id,
    'order_number' => 'TEST-' . strtoupper(uniqid()),
    'status' => 'confirmed',
    'payment_status' => 'paid',
    'payment_method' => 'test',
    'subtotal' => $bronze->price,
    'grand_total' => $bronze->price,
]);
OrderItem::create([
    'order_id' => $order->id,
    'package_id' => $bronze->id,
    'quantity' => 1,
    'price' => $bronze->price,
    'subtotal' => $bronze->price,
]);

$user->updateRank();
$user->refresh();

echo "User rank after direct Bronze purchase: {$user->current_rank}\n";
echo "Rank order: {$user->getRankOrder()}\n\n";

if ($user->current_rank === 'Bronze' && $user->getRankOrder() === 3) {
    echo "✅ PASS: User can directly purchase higher rank\n";
} else {
    echo "❌ FAIL: Non-sequential purchase failed\n";
}
```

---

## Model Functionality Tests

### Test 11: User Model Helper Methods

```bash
php artisan tinker
```

```php
use App\Models\User;
use App\Models\Package;

// Get a user with rank
$user = User::whereNotNull('current_rank')->first();

// Test 11.1: getRankName()
echo "getRankName(): " . $user->getRankName() . "\n";

// Test 11.2: getRankOrder()
echo "getRankOrder(): " . $user->getRankOrder() . "\n";

// Test 11.3: rankPackage() relationship
$rankPackage = $user->rankPackage;
echo "rankPackage: " . ($rankPackage ? $rankPackage->name : 'None') . "\n";

// Test 11.4: getHighestPackagePurchased()
$highestPackage = $user->getHighestPackagePurchased();
echo "Highest Package: " . ($highestPackage ? $highestPackage->name : 'None') . "\n";

// Test 11.5: getSameRankSponsorsCount()
echo "Same Rank Sponsors Count: " . $user->getSameRankSponsorsCount() . "\n";

// Test 11.6: rankAdvancements() relationship
echo "Rank Advancements Count: " . $user->rankAdvancements()->count() . "\n";

// Test 11.7: directSponsorsTracked() relationship
echo "Direct Sponsors Tracked: " . $user->directSponsorsTracked()->count() . "\n";
```

### Test 12: Package Model Helper Methods

```php
use App\Models\Package;

$starter = Package::where('rank_name', 'Starter')->first();
$bronze = Package::where('rank_name', 'Bronze')->first();

// Test 12.1: canAdvanceToNextRank()
echo "Starter can advance: " . ($starter->canAdvanceToNextRank() ? 'YES' : 'NO') . "\n";
echo "Bronze can advance: " . ($bronze->canAdvanceToNextRank() ? 'YES' : 'NO') . "\n";

// Test 12.2: getNextRankPackage()
$nextRank = $starter->getNextRankPackage();
echo "Starter next rank: " . ($nextRank ? $nextRank->rank_name : 'None') . "\n";

// Test 12.3: nextRankPackage() relationship
echo "Starter nextRankPackage: " . ($starter->nextRankPackage?->rank_name ?? 'None') . "\n";

// Test 12.4: previousRankPackages() relationship
$bronze = Package::where('rank_name', 'Bronze')->first();
$previousRanks = $bronze->previousRankPackages()->pluck('rank_name');
echo "Bronze previous ranks: " . $previousRanks->implode(', ') . "\n";

// Test 12.5: Rankable scope
$rankableCount = Package::rankable()->count();
echo "Rankable packages count: {$rankableCount}\n";

// Test 12.6: OrderedByRank scope
$orderedRanks = Package::orderedByRank()->pluck('rank_name');
echo "Ordered ranks: " . $orderedRanks->implode(' → ') . "\n";
```

**Expected Results:**
- Starter can advance: YES
- Bronze can advance: NO
- Starter next rank: Newbie
- Bronze previous ranks: Newbie
- Rankable packages count: 3
- Ordered ranks: Starter → Newbie → Bronze

---

## Admin UI Protection Tests

### Test 13: Package Name Protection in Admin

**Manual Test in Browser:**

1. Login as admin: `admin@ewallet.com` / `Admin123!@#`
2. Navigate to: **Admin → Packages → Edit Starter Package**
3. Verify the Package Name field shows:
   - ✅ Readonly attribute (grayed out, can't edit)
   - ✅ Lock icon (🔒) with warning message
   - ✅ Message: "Package name cannot be changed because it's associated with rank..."

4. Try to edit a non-rank package:
   - Should be **editable** (no readonly)
   - Should show badges indicating package status

5. Inspect HTML source:
```html
<!-- Should see: -->
<input type="text" class="form-control" id="name" name="name" 
       value="Starter Package" readonly required>
```

### Test 14: Controller Validation Test

Create test script: `test_admin_name_protection.php`

```php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "=== Test 14: Admin Name Protection ===\n\n";

// Login as admin
$admin = User::where('email', 'admin@ewallet.com')->first();
Auth::login($admin);

$starter = Package::where('rank_name', 'Starter')->first();

// Test 14.1: Check if package is locked
$isRankPackage = $starter->rank_name 
                && $starter->is_mlm_package 
                && $starter->mlmSettings()->exists();

echo "Starter Package Status:\n";
echo "  rank_name: {$starter->rank_name}\n";
echo "  is_mlm_package: " . ($starter->is_mlm_package ? 'true' : 'false') . "\n";
echo "  has MLM settings: " . ($starter->mlmSettings()->exists() ? 'true' : 'false') . "\n";
echo "  Is Locked: " . ($isRankPackage ? 'YES' : 'NO') . "\n\n";

if ($isRankPackage) {
    echo "✅ PASS: Starter Package is properly locked\n";
} else {
    echo "❌ FAIL: Starter Package should be locked\n";
}

// Test 14.2: Check non-rank package
$nonRankPackage = Package::whereNull('rank_name')->first();
if ($nonRankPackage) {
    $isLocked = $nonRankPackage->rank_name 
               && $nonRankPackage->is_mlm_package 
               && $nonRankPackage->mlmSettings()->exists();
    
    echo "\nNon-Rank Package Status:\n";
    echo "  Name: {$nonRankPackage->name}\n";
    echo "  Is Locked: " . ($isLocked ? 'YES' : 'NO') . "\n";
    
    if (!$isLocked) {
        echo "✅ PASS: Non-rank package is editable\n";
    } else {
        echo "❌ FAIL: Non-rank package should not be locked\n";
    }
}
```

---

## Relationship Tests

### Test 15: User → Package Relationship

```bash
php artisan tinker
```

```php
use App\Models\User;

$user = User::whereNotNull('current_rank')->first();

// Test eager loading
$userWithRank = User::with('rankPackage')->find($user->id);
echo "User: {$userWithRank->username}\n";
echo "Rank Package: {$userWithRank->rankPackage->name}\n";
echo "Rank Order: {$userWithRank->rankPackage->rank_order}\n";

// Test reverse relationship
$package = $userWithRank->rankPackage;
$usersWithThisRank = \App\Models\User::where('rank_package_id', $package->id)->count();
echo "Users with {$package->rank_name} rank: {$usersWithThisRank}\n";
```

### Test 16: Package → Package Relationship (Rank Chain)

```php
use App\Models\Package;

// Test forward chain
$starter = Package::with('nextRankPackage.nextRankPackage')->where('rank_name', 'Starter')->first();

echo "Rank Chain (Forward):\n";
$current = $starter;
$depth = 0;
while ($current && $depth < 10) {
    echo str_repeat('  ', $depth) . "→ {$current->rank_name} (Order: {$current->rank_order})\n";
    $current = $current->nextRankPackage;
    $depth++;
}

// Test backward chain
$bronze = Package::with('previousRankPackages')->where('rank_name', 'Bronze')->first();
echo "\nPackages that lead to Bronze:\n";
foreach ($bronze->previousRankPackages as $prev) {
    echo "  ← {$prev->rank_name}\n";
}
```

### Test 17: RankAdvancement Relationships

```php
use App\Models\RankAdvancement;
use App\Models\User;

// Create a test advancement
$user = User::where('username', 'ranktest1')->first();
$starter = \App\Models\Package::where('rank_name', 'Starter')->first();
$newbie = \App\Models\Package::where('rank_name', 'Newbie')->first();

$advancement = RankAdvancement::create([
    'user_id' => $user->id,
    'from_rank' => null,
    'to_rank' => 'Starter',
    'from_package_id' => null,
    'to_package_id' => $starter->id,
    'advancement_type' => 'purchase',
    'notes' => 'Test advancement',
]);

// Test relationships
$advancementWithRelations = RankAdvancement::with(['user', 'fromPackage', 'toPackage'])
    ->find($advancement->id);

echo "Advancement:\n";
echo "  User: {$advancementWithRelations->user->username}\n";
echo "  From: " . ($advancementWithRelations->fromPackage?->rank_name ?? 'None') . "\n";
echo "  To: {$advancementWithRelations->toPackage->rank_name}\n";
echo "  Type: {$advancementWithRelations->advancement_type}\n";
echo "  Is System Reward: " . ($advancementWithRelations->isSystemReward() ? 'YES' : 'NO') . "\n";
```

---

## Migration Rollback Tests

### Test 18: Safe Rollback

**⚠️ Warning: This will remove rank data. Only test in development environment.**

```bash
# Backup current state
php artisan db:seed --class=PackageSeeder

# Test rollback of last migration
php artisan migrate:rollback --step=1

# Verify table was dropped
php artisan tinker
>>> \Schema::hasTable('direct_sponsors_tracker')
# Should return: false

# Re-run migration
php artisan migrate

# Verify table recreated
>>> \Schema::hasTable('direct_sponsors_tracker')
# Should return: true

# Re-seed packages
php artisan db:seed --class=PackageSeeder
```

### Test 19: Full Rollback and Re-migration

```bash
# Rollback all 4 rank migrations
php artisan migrate:rollback --step=4

# Verify all rank tables dropped
php artisan tinker
>>> \Schema::hasTable('rank_advancements')
# Should return: false

>>> \Schema::hasColumn('users', 'current_rank')
# Should return: false

>>> \Schema::hasColumn('packages', 'rank_name')
# Should return: false

# Re-run all migrations
php artisan migrate

# Re-seed
php artisan db:seed --class=PackageSeeder

# Verify everything restored
>>> \Schema::hasTable('rank_advancements')
# Should return: true
```

---

## Performance Tests

### Test 20: Query Performance with Large Dataset

Create test script: `test_rank_performance.php`

```php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;
use Illuminate\Support\Facades\DB;

echo "=== Test 20: Query Performance ===\n\n";

// Test 20.1: Get users with ranks (with eager loading)
$start = microtime(true);
$usersWithRanks = User::with('rankPackage')
    ->whereNotNull('current_rank')
    ->limit(100)
    ->get();
$time1 = microtime(true) - $start;
echo "Query 1 - Users with ranks (eager): {$time1}s\n";
echo "  Query count: " . DB::getQueryLog() ? count(DB::getQueryLog()) : 'N/A' . "\n";

// Test 20.2: Get ranked packages (with relationships)
DB::enableQueryLog();
$start = microtime(true);
$packages = Package::with(['nextRankPackage', 'mlmSettings'])
    ->rankable()
    ->orderedByRank()
    ->get();
$time2 = microtime(true) - $start;
$queryCount = count(DB::getQueryLog());
DB::disableQueryLog();
echo "\nQuery 2 - Ranked packages: {$time2}s\n";
echo "  Query count: {$queryCount}\n";

// Test 20.3: Complex query - users and their rank progression potential
$start = microtime(true);
$complexQuery = User::select('users.*')
    ->whereNotNull('current_rank')
    ->with(['rankPackage.nextRankPackage', 'directSponsorsTracked'])
    ->limit(50)
    ->get();
$time3 = microtime(true) - $start;
echo "\nQuery 3 - Complex rank data: {$time3}s\n";

echo "\n";
if ($time1 < 0.1 && $time2 < 0.1 && $time3 < 0.5) {
    echo "✅ PASS: All queries completed in acceptable time\n";
} else {
    echo "⚠️  WARNING: Some queries are slow, consider adding indexes\n";
}
```

---

## Edge Case Tests

### Test 21: Null/Empty Data Handling

```php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== Test 21: Null Data Handling ===\n\n";

// Create user without any purchases
$newUser = User::create([
    'username' => 'nulltest',
    'fullname' => 'Null Test User',
    'password' => bcrypt('password'),
    'email' => 'nulltest@test.com',
    'email_verified_at' => now(),
]);

// Test methods with no rank data
echo "User with no rank:\n";
echo "  getRankName(): " . $newUser->getRankName() . "\n"; // Should: "Unranked"
echo "  getRankOrder(): " . $newUser->getRankOrder() . "\n"; // Should: 0
echo "  rankPackage: " . ($newUser->rankPackage ? 'EXISTS' : 'NULL') . "\n"; // Should: NULL
echo "  getHighestPackagePurchased(): " . ($newUser->getHighestPackagePurchased() ? 'EXISTS' : 'NULL') . "\n"; // Should: NULL
echo "  getSameRankSponsorsCount(): " . $newUser->getSameRankSponsorsCount() . "\n"; // Should: 0

echo "\n";
if ($newUser->getRankName() === 'Unranked' && $newUser->getRankOrder() === 0) {
    echo "✅ PASS: Null data handled gracefully\n";
} else {
    echo "❌ FAIL: Null data caused errors\n";
}

// Cleanup
$newUser->delete();
```

### Test 22: Circular Reference Prevention

```php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Package;

echo "=== Test 22: Circular Reference Prevention ===\n\n";

$starter = Package::where('rank_name', 'Starter')->first();
$newbie = Package::where('rank_name', 'Newbie')->first();

echo "Attempting to create circular reference...\n";
echo "Setting Newbie's next_rank to Starter (should not be allowed)\n\n";

try {
    // Try to create a circular reference
    $newbie->update(['next_rank_package_id' => $starter->id]);
    
    // Check if circular reference was created
    $current = $starter;
    $visited = [];
    $circular = false;
    
    while ($current && count($visited) < 10) {
        if (in_array($current->id, $visited)) {
            $circular = true;
            break;
        }
        $visited[] = $current->id;
        $current = $current->nextRankPackage;
    }
    
    if ($circular) {
        echo "⚠️  WARNING: Circular reference detected! This should be prevented.\n";
        // Restore proper reference
        $newbie->update(['next_rank_package_id' => Package::where('rank_name', 'Bronze')->first()->id]);
    } else {
        echo "✅ PASS: No circular reference (or prevented at application level)\n";
    }
} catch (\Exception $e) {
    echo "✅ PASS: Circular reference prevented by database constraint\n";
    echo "  Error: {$e->getMessage()}\n";
}
```

### Test 23: Duplicate Sponsorship Prevention

```php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\DirectSponsorsTracker;

echo "=== Test 23: Duplicate Sponsorship Prevention ===\n\n";

$sponsor = User::where('username', 'ranktest1')->first();
$sponsored = User::where('username', 'ranktest2')->first();

// Create first sponsorship
$tracker1 = DirectSponsorsTracker::create([
    'user_id' => $sponsor->id,
    'sponsored_user_id' => $sponsored->id,
    'sponsored_at' => now(),
    'sponsored_user_rank_at_time' => 'Starter',
]);

echo "Created first sponsorship record\n";

// Try to create duplicate
try {
    $tracker2 = DirectSponsorsTracker::create([
        'user_id' => $sponsor->id,
        'sponsored_user_id' => $sponsored->id,
        'sponsored_at' => now(),
        'sponsored_user_rank_at_time' => 'Newbie',
    ]);
    
    echo "❌ FAIL: Duplicate sponsorship was allowed\n";
    $tracker2->delete();
} catch (\Exception $e) {
    echo "✅ PASS: Duplicate sponsorship prevented by unique constraint\n";
    echo "  Error: " . substr($e->getMessage(), 0, 100) . "...\n";
}

// Cleanup
$tracker1->delete();
```

---

## Success Criteria Checklist

Use this checklist to verify Phase 1 is ready for Phase 2:

### Database Schema ✅
- [ ] `rank_advancements` table exists with all columns and indexes
- [ ] `direct_sponsors_tracker` table exists with all columns and indexes
- [ ] `users` table has 3 new rank columns
- [ ] `packages` table has 5 new rank columns
- [ ] All foreign keys are properly set up
- [ ] All indexes are created

### Package Configuration ✅
- [ ] 3 rankable packages created (Starter, Newbie, Bronze)
- [ ] Rank progression chain works (Starter → Newbie → Bronze)
- [ ] Each package has correct `rank_order` values (1, 2, 3)
- [ ] Each package has `required_direct_sponsors` configured
- [ ] Each package has MLM settings with appropriate commission rates
- [ ] Top rank (Bronze) has NULL `next_rank_package_id`

### Model Functionality ✅
- [ ] `User::getRankName()` returns correct values
- [ ] `User::getRankOrder()` returns correct values
- [ ] `User::getHighestPackagePurchased()` works correctly
- [ ] `User::updateRank()` updates rank based on purchases
- [ ] `User::getSameRankSponsorsCount()` works (returns 0 for now)
- [ ] `Package::canAdvanceToNextRank()` works correctly
- [ ] `Package::getNextRankPackage()` returns correct next rank
- [ ] All relationships load without errors

### Automatic Rank Assignment ✅
- [ ] User gets Starter rank after purchasing Starter package
- [ ] User upgrades to Newbie after purchasing Newbie package
- [ ] User upgrades to Bronze after purchasing Bronze package
- [ ] User can buy Bronze directly (non-sequential)
- [ ] `rank_updated_at` timestamp is set correctly

### Admin UI Protection ✅
- [ ] Starter Package name field is readonly in admin
- [ ] Newbie Package name field is readonly in admin
- [ ] Bronze Package name field is readonly in admin
- [ ] Lock icon and warning message displayed
- [ ] Non-rank packages remain editable
- [ ] Controller validation blocks name changes
- [ ] Attempted name changes are logged

### Edge Cases ✅
- [ ] Users without ranks show "Unranked"
- [ ] Null rank handling doesn't cause errors
- [ ] Duplicate sponsorship tracking prevented
- [ ] Circular package references prevented (if implemented)
- [ ] All queries perform acceptably (< 0.5s)

### Migration & Rollback ✅
- [ ] All 4 migrations run without errors
- [ ] Migrations can be rolled back successfully
- [ ] Re-running migrations works correctly
- [ ] Assign ranks migration handles existing users
- [ ] No data loss during rollback/re-migration

---

## Final Verification

Run the automated test script:

```bash
php test_rank_system_phase1.php
```

**Expected Final Output:**
```
=== Test Summary ===
✅ Phase 1 implementation is complete!
All database tables and columns are in place.
Rank system foundation is ready.

Phase 1 Test Completed!
```

---

## Troubleshooting Common Issues

### Issue 1: Migrations Fail

**Problem:** Foreign key constraint errors during migration

**Solution:**
```bash
# Drop all tables and re-migrate
php artisan db:wipe
php artisan migrate:fresh --seed
```

### Issue 2: Rank Not Assigned After Purchase

**Problem:** User's `current_rank` is still NULL after buying package

**Solutions:**
1. Verify package has `is_rankable = true` and `rank_name` set
2. Verify order has `payment_status = 'paid'`
3. Manually call `$user->updateRank()`
4. Check if `is_mlm_package` is true

### Issue 3: Package Name Still Editable

**Problem:** Admin can still change rank package names

**Solutions:**
1. Verify package has MLM settings: `$package->mlmSettings()->exists()`
2. Clear browser cache (Ctrl+Shift+R)
3. Check if `rank_name` and `is_mlm_package` are both set
4. Verify controller validation is in place

### Issue 4: Relationship Returns Null

**Problem:** `$user->rankPackage` returns null

**Solutions:**
1. Check if `rank_package_id` is set on user
2. Verify foreign key exists in database
3. Check if package was soft-deleted
4. Use eager loading: `User::with('rankPackage')->find($id)`

### Issue 5: Slow Queries

**Problem:** Rank queries taking > 1 second

**Solutions:**
1. Verify indexes are created:
   ```sql
   SHOW INDEX FROM users WHERE Key_name LIKE '%rank%';
   ```
2. Use eager loading:
   ```php
   User::with('rankPackage')->whereNotNull('current_rank')->get();
   ```
3. Add composite indexes if needed
4. Check for N+1 query problems

---

## Sign-Off

Once all tests pass, you can confidently proceed to Phase 2 implementation.

**Phase 1 Status:** [ ] Ready for Phase 2

**Tested By:** ________________

**Date:** ________________

**Notes:**
```
[Add any observations or issues encountered during testing]
```

---

## Next Steps: Phase 2 Preview

Phase 2 will implement:
1. **RankComparisonService** - Rank-based commission calculation
2. **MLMCommissionService** updates - Integration with rank comparison
3. **Testing scripts** - Verify rank-aware commissions work correctly

**Prerequisites for Phase 2:**
- ✅ All Phase 1 tests passing
- ✅ At least 2-3 users with different ranks
- ✅ Understanding of rank comparison rules documented in RANK.md
