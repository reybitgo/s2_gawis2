# Rank System Phase 4 - Comprehensive Testing Guide

## Overview

This guide provides step-by-step instructions to thoroughly test Phase 4 (UI Integration - Display Ranks) before deploying to production or proceeding to Phase 5.

**Estimated Testing Time**: 45-60 minutes

## 🚀 Important: Synchronous Rank Advancement

**Rank advancement is SYNCHRONOUS** - it happens instantly when a user purchases a package. No scheduled tasks or cron jobs are needed. When a user's 5th referral purchases a Starter package, the sponsor is **immediately promoted** to Newbie in the same request.

See `SYNCHRONOUS_RANK_ADVANCEMENT.md` for complete details.

---

## Table of Contents

1. [Pre-Testing Setup](#pre-testing-setup)
2. [Test Environment Preparation](#test-environment-preparation)
3. [User Profile View Tests](#user-profile-view-tests)
4. [Admin User Table Tests](#admin-user-table-tests)
5. [Performance Tests](#performance-tests)
6. [Visual/UI Tests](#visualui-tests)
7. [Edge Case Tests](#edge-case-tests)
8. [Browser Compatibility Tests](#browser-compatibility-tests)
9. [Mobile Responsive Tests](#mobile-responsive-tests)
10. [Cleanup & Reporting](#cleanup--reporting)

---

## Pre-Testing Setup

### 1. Verify Phase 3 is Deployed

```bash
cd C:\laragon\www\s2_gawis2

# Check migrations
php artisan migrate:status | findstr "2025_11_27"

# Should show all 4 rank migrations as [X] Ran
```

### 2. Verify Rank Packages Exist

```bash
php check_rank_packages.php
```

**Expected Output**:
```
Package: Starter
  Rank Name: Starter
  Rank Order: 1
  Required Sponsors: 5
  Can Advance: Yes

Package: Newbie (or Professional)
  Rank Name: Newbie
  Rank Order: 2
  Required Sponsors: 8
  Can Advance: Yes

Package: Bronze (or Premium)
  Rank Name: Bronze
  Rank Order: 3
  Required Sponsors: 10
  Can Advance: No
```

### 3. Clear All Caches

```bash
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### 4. Start Local Server

```bash
php artisan serve
```

**Server should be running at**: `http://127.0.0.1:8000`

---

## Test Environment Preparation

### Migrate Legacy Users (If Applicable)

If you have existing users who purchased packages before Phase 4:

```bash
php migrate_legacy_rank_data.php
```

This one-time script:
- Finds users with purchased packages but no rank data
- Updates their `current_rank`, `rank_package_id`, `rank_updated_at`
- Creates rank advancement history records
- See `LEGACY_RANK_MIGRATION.md` for details

### Create Test Users with Different Scenarios

Run this script to create test data:

**File**: `setup_phase4_test_users.php` (provided in project root)

```php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Models\Wallet;
use App\Models\RankAdvancement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=== Setting Up Phase 4 Test Users ===\n\n";

// Get packages
$starter = Package::where('rank_name', 'Starter')->first();
$newbie = Package::where('rank_name', 'Newbie')->first();
$bronze = Package::where('rank_name', 'Bronze')->first();

if (!$starter || !$newbie || !$bronze) {
    echo "❌ ERROR: Rank packages not found!\n";
    exit(1);
}

// Test User 1: Unranked User (no package purchased)
echo "1. Creating Unranked User...\n";
$unranked = User::create([
    'username' => 'test_unranked',
    'fullname' => 'Unranked Test User',
    'email' => 'test_unranked@test.com',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
]);
Wallet::create(['user_id' => $unranked->id, 'balance' => 0]);
echo "   ✓ Username: test_unranked | Password: password\n\n";

// Test User 2: Starter Rank (0% progress - no sponsors)
echo "2. Creating Starter User (0% progress)...\n";
$starterZero = User::create([
    'username' => 'test_starter_0',
    'fullname' => 'Starter Zero Progress',
    'email' => 'test_starter_0@test.com',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
    'current_rank' => 'Starter',
    'rank_package_id' => $starter->id,
    'rank_updated_at' => now()->subDays(5),
    'network_status' => 'active',
]);
Wallet::create(['user_id' => $starterZero->id, 'balance' => 0, 'withdrawable_balance' => 0]);
echo "   ✓ Username: test_starter_0 | Password: password\n\n";

// Test User 3: Starter Rank (60% progress - 3/5 sponsors)
echo "3. Creating Starter User (60% progress)...\n";
$starterSixty = User::create([
    'username' => 'test_starter_60',
    'fullname' => 'Starter Sixty Progress',
    'email' => 'test_starter_60@test.com',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
    'current_rank' => 'Starter',
    'rank_package_id' => $starter->id,
    'rank_updated_at' => now()->subDays(10),
    'network_status' => 'active',
]);
Wallet::create(['user_id' => $starterSixty->id, 'balance' => 500.00, 'withdrawable_balance' => 250.50]);

// Create 3 referrals for 60% progress
for ($i = 1; $i <= 3; $i++) {
    User::create([
        'username' => "referral_60_{$i}",
        'fullname' => "Referral {$i}",
        'email' => "referral_60_{$i}@test.com",
        'password' => Hash::make('password'),
        'sponsor_id' => $starterSixty->id,
        'current_rank' => 'Starter',
        'rank_package_id' => $starter->id,
    ]);
}
echo "   ✓ Username: test_starter_60 | Password: password | Income: ₱250.50\n\n";

// Test User 4: Starter Rank (100% progress - 5/5 sponsors, eligible)
echo "4. Creating Starter User (100% eligible)...\n";
$starterEligible = User::create([
    'username' => 'test_starter_eligible',
    'fullname' => 'Starter Eligible',
    'email' => 'test_starter_eligible@test.com',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
    'current_rank' => 'Starter',
    'rank_package_id' => $starter->id,
    'rank_updated_at' => now()->subDays(15),
    'network_status' => 'active',
]);
Wallet::create(['user_id' => $starterEligible->id, 'balance' => 1500.00, 'withdrawable_balance' => 1234.56]);

// Create 5 referrals for 100% progress
for ($i = 1; $i <= 5; $i++) {
    User::create([
        'username' => "referral_eligible_{$i}",
        'fullname' => "Referral {$i}",
        'email' => "referral_eligible_{$i}@test.com",
        'password' => Hash::make('password'),
        'sponsor_id' => $starterEligible->id,
        'current_rank' => 'Starter',
        'rank_package_id' => $starter->id,
    ]);
}
echo "   ✓ Username: test_starter_eligible | Password: password | Income: ₱1,234.56\n\n";

// Test User 5: Newbie Rank (with advancement history)
echo "5. Creating Newbie User (with history)...\n";
$newbieUser = User::create([
    'username' => 'test_newbie',
    'fullname' => 'Newbie Test User',
    'email' => 'test_newbie@test.com',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
    'current_rank' => 'Newbie',
    'rank_package_id' => $newbie->id,
    'rank_updated_at' => now()->subDays(3),
    'network_status' => 'active',
]);
Wallet::create(['user_id' => $newbieUser->id, 'balance' => 5000.00, 'withdrawable_balance' => 3456.78]);

// Create advancement history
RankAdvancement::create([
    'user_id' => $newbieUser->id,
    'from_rank' => null,
    'to_rank' => 'Starter',
    'from_package_id' => null,
    'to_package_id' => $starter->id,
    'advancement_type' => 'purchase',
    'created_at' => now()->subDays(20),
]);

RankAdvancement::create([
    'user_id' => $newbieUser->id,
    'from_rank' => 'Starter',
    'to_rank' => 'Newbie',
    'from_package_id' => $starter->id,
    'to_package_id' => $newbie->id,
    'advancement_type' => 'sponsorship_reward',
    'sponsors_count' => 5,
    'system_paid_amount' => 2500.00,
    'created_at' => now()->subDays(3),
]);

// Create 2 referrals for Newbie (2/8 progress)
for ($i = 1; $i <= 2; $i++) {
    User::create([
        'username' => "referral_newbie_{$i}",
        'fullname' => "Newbie Referral {$i}",
        'email' => "referral_newbie_{$i}@test.com",
        'password' => Hash::make('password'),
        'sponsor_id' => $newbieUser->id,
        'current_rank' => 'Newbie',
        'rank_package_id' => $newbie->id,
    ]);
}
echo "   ✓ Username: test_newbie | Password: password | Income: ₱3,456.78\n\n";

// Test User 6: Bronze Rank (Top Rank - no next rank)
echo "6. Creating Bronze User (Top Rank)...\n";
$bronzeUser = User::create([
    'username' => 'test_bronze',
    'fullname' => 'Bronze Test User',
    'email' => 'test_bronze@test.com',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
    'current_rank' => 'Bronze',
    'rank_package_id' => $bronze->id,
    'rank_updated_at' => now()->subDays(1),
    'network_status' => 'active',
]);
Wallet::create(['user_id' => $bronzeUser->id, 'balance' => 20000.00, 'withdrawable_balance' => 12345.67]);

// Create advancement history (multiple advancements)
RankAdvancement::create([
    'user_id' => $bronzeUser->id,
    'from_rank' => null,
    'to_rank' => 'Starter',
    'from_package_id' => null,
    'to_package_id' => $starter->id,
    'advancement_type' => 'purchase',
    'created_at' => now()->subDays(30),
]);

RankAdvancement::create([
    'user_id' => $bronzeUser->id,
    'from_rank' => 'Starter',
    'to_rank' => 'Newbie',
    'from_package_id' => $starter->id,
    'to_package_id' => $newbie->id,
    'advancement_type' => 'sponsorship_reward',
    'sponsors_count' => 5,
    'system_paid_amount' => 2500.00,
    'created_at' => now()->subDays(10),
]);

RankAdvancement::create([
    'user_id' => $bronzeUser->id,
    'from_rank' => 'Newbie',
    'to_rank' => 'Bronze',
    'from_package_id' => $newbie->id,
    'to_package_id' => $bronze->id,
    'advancement_type' => 'sponsorship_reward',
    'sponsors_count' => 8,
    'system_paid_amount' => 5000.00,
    'created_at' => now()->subDays(1),
]);

echo "   ✓ Username: test_bronze | Password: password | Income: ₱12,345.67\n\n";

echo "=== Test Users Created Successfully ===\n\n";

echo "📋 Test User Summary:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1. test_unranked       - Unranked (no package)\n";
echo "2. test_starter_0      - Starter (0/5 = 0%)\n";
echo "3. test_starter_60     - Starter (3/5 = 60%) | ₱250.50\n";
echo "4. test_starter_eligible - Starter (5/5 = 100%) | ₱1,234.56\n";
echo "5. test_newbie         - Newbie (2/8 = 25%) | ₱3,456.78\n";
echo "6. test_bronze         - Bronze (Top Rank) | ₱12,345.67\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "All passwords: password\n\n";

echo "✓ Ready for Phase 4 Testing!\n";
```

Run the script:

```bash
php setup_phase4_test_users.php
```

**What the script does:**
- Creates 6 test users with different rank scenarios
- Creates 10 referral users for testing sponsor relationships
- Assigns 'member' role to all users (for wallet permissions)
- Links test users to an existing sponsor (if available)
- Sets up wallet balances with withdrawable amounts
- Creates rank advancement history for testing

**Note:** Test users will be linked to your most recently created user as sponsor (or no sponsor if none exist).

---

## User Profile View Tests

### Test 1: Unranked User Profile

**Steps**:
1. Navigate to: `http://127.0.0.1:8000/login`
2. Login with:
   - Username: `test_unranked`
   - Password: `password`
3. Navigate to: Profile (click user avatar → Profile)

**Expected Results**:
- [ ] Rank card is visible in right sidebar
- [ ] Header shows "My Rank" with star icon
- [ ] Rank badge displays "Unranked"
- [ ] Shows message: "Purchase a package to get ranked"
- [ ] No progress bar visible
- [ ] No "Next Rank" section
- [ ] No rank history table
- [ ] Wallet Operations menu visible in sidebar (user has member role)
- [ ] Wallet Information card shows ₱0.00 Total Available Balance

**Screenshot**: Capture for documentation

---

### Test 2: Starter Rank - 0% Progress

**Steps**:
1. Logout
2. Login with:
   - Username: `test_starter_0`
   - Password: `password`
3. Navigate to Profile

**Expected Results**:
- [ ] Rank badge displays "Starter" with info color (turquoise)
- [ ] Shows "Starter Package" below rank name
- [ ] Shows "Since: [date]" (should be ~5 days ago)
- [ ] "Next Rank: Newbie" displayed
- [ ] Progress bar shows: "0 / 5"
- [ ] Progress bar at 0% (empty)
- [ ] Progress bar is blue color
- [ ] Message shows: "Sponsor 5 more Starter-rank users to advance to Newbie"
- [ ] No rank history (user just started)
- [ ] Wallet Operations menu visible in sidebar
- [ ] Wallet shows ₱0.00 Total Available Balance with breakdown

**Screenshot**: Capture for documentation

---

### Test 3: Starter Rank - 60% Progress

**Steps**:
1. Logout
2. Login with:
   - Username: `test_starter_60`
   - Password: `password`
3. Navigate to Profile

**Expected Results**:
- [ ] Rank badge displays "Starter"
- [ ] Shows "Starter Package"
- [ ] Shows "Since: [date]" (should be ~10 days ago)
- [ ] "Next Rank: Newbie" displayed
- [ ] Progress bar shows: "3 / 5"
- [ ] Progress bar at 60% (visually ~60% filled)
- [ ] Progress bar text shows "60%"
- [ ] Progress bar is blue color (not green yet)
- [ ] Message shows: "Sponsor 2 more Starter-rank users to advance to Newbie"
- [ ] Wallet Operations menu visible in sidebar
- [ ] Wallet Information card shows:
  - Total Available Balance: ₱250.50
  - Breakdown: Withdrawable: ₱250.50 | Purchase: ₱0.00
  - MLM Earned: ₱500.00 (lifetime tracker)
  - Unilevel Earned: ₱0.00

**Screenshot**: Capture for documentation

---

### Test 4: Auto-Advanced User (Was Starter 100%, Now Newbie)

**Steps**:
1. Logout
2. Login with:
   - Username: `test_starter_eligible`
   - Password: `password`
3. Navigate to Profile

**Expected Results**:
- [ ] **Rank badge displays "Newbie"** (automatically advanced!)
- [ ] Shows appropriate package name (Professional Package)
- [ ] Shows "Since: [recent date]" (when advancement occurred)
- [ ] "Next Rank: Bronze" displayed
- [ ] Progress bar shows: "0 / 8" (starting fresh towards Bronze)
- [ ] Progress bar at 0%
- [ ] Progress bar is blue color
- [ ] Message shows: "Sponsor 8 more Newbie-rank users to advance to Bronze"
- [ ] Wallet Operations menu visible
- [ ] Wallet Information shows:
  - Total Available Balance: ₱1,234.56
  - Breakdown: Withdrawable: ₱1,234.56 | Purchase: ₱0.00
  - MLM Earned: ₱1,500.00 (lifetime tracker)
- [ ] **Rank History section visible**
- [ ] History shows advancement from Starter → Newbie
- [ ] Advancement type: "Reward" badge (blue)
- [ ] Shows: "Earned by sponsoring 5 users"

**Important Note**: This user was originally at 100% progress (5/5 sponsors). The system automatically advanced them to Newbie when we ran the processing script. This demonstrates **synchronous rank advancement** - in production, this happens instantly when the 5th referral purchases a package.

**Screenshot**: Capture for documentation

---

### Test 5: Newbie Rank with History

**Steps**:
1. Logout
2. Login with:
   - Username: `test_newbie`
   - Password: `password`
3. Navigate to Profile

**Expected Results**:
- [ ] Rank badge displays "Newbie"
- [ ] Shows appropriate package name
- [ ] Shows "Since: [date]" (should be ~3 days ago)
- [ ] "Next Rank: Bronze" displayed
- [ ] Progress bar shows: "2 / 8"
- [ ] Progress bar at 25% 
- [ ] Progress bar is blue
- [ ] Message shows: "Sponsor 6 more Newbie-rank users to advance to Bronze"
- [ ] Wallet shows: ₱3,456.78
- [ ] **Rank History section visible**
- [ ] History table shows 2 advancements:
   - Row 1: None → Starter | Purchase badge (cyan)
   - Row 2: Starter → Newbie | Reward badge (blue)
- [ ] Reward badge has tooltip showing "Earned by sponsoring 5 users"
- [ ] Dates are in order (most recent first)

**Screenshot**: Capture for documentation

---

### Test 6: Bronze Rank (Top Rank)

**Steps**:
1. Logout
2. Login with:
   - Username: `test_bronze`
   - Password: `password`
3. Navigate to Profile

**Expected Results**:
- [ ] Rank badge displays "Bronze"
- [ ] Shows appropriate package name
- [ ] Shows "Since: [date]" (should be ~1 day ago)
- [ ] **No "Next Rank" section** (already top rank)
- [ ] **No progress bar** (no next rank available)
- [ ] Blue info alert displayed
- [ ] Alert text: "Top Rank! You've reached the highest rank."
- [ ] Wallet shows: ₱12,345.67
- [ ] **Rank History section visible**
- [ ] History table shows 3 advancements:
   - Row 1: Newbie → Bronze | Reward (8 sponsors)
   - Row 2: Starter → Newbie | Reward (5 sponsors)
   - Row 3: None → Starter | Purchase
- [ ] All badges color-coded correctly
- [ ] Dates in descending order
- [ ] Wallet Operations menu visible
- [ ] Wallet shows: ₱12,345.67 Total Available Balance

**Screenshot**: Capture for documentation

---

### Test 6b: Verify Synchronous Advancement

**Purpose**: Confirm rank advancement happens immediately when requirements are met

**Steps**:
1. Check recent logs:
```bash
# View recent rank advancements
php artisan tinker
>>> App\Models\RankAdvancement::latest()->first()
```

**Expected Results**:
- [ ] Shows recent advancement records
- [ ] `advancement_type` is "sponsorship_reward"
- [ ] `sponsors_count` matches requirements (5, 8, or 10)
- [ ] `system_paid_amount` shows package price
- [ ] `created_at` timestamp shows when advancement occurred

**Manual Test** (Optional):
```bash
# Simulate a purchase triggering advancement
php test_synchronous_advancement.php
```

This demonstrates that when a user buys a package, their sponsor is checked and advanced immediately if eligible.

---

## Admin User Table Tests

### Test 7: Admin View - User Table

**Steps**:
1. Logout
2. Login as admin (use your admin account)
3. Navigate to: Admin → User Management
4. Locate the test users in the table

**Expected Results for Table Headers**:
- [ ] Column order: User | **Income** | **Rank** | Roles | Status | Created | Actions
- [ ] **Email column is GONE** (replaced by Income)
- [ ] Income column header visible
- [ ] Rank column header visible

**Expected Results for test_unranked**:
- [ ] Income shows: ₱0.00 (gray/muted color)
- [ ] Rank shows: "Unranked" badge (gray/secondary color)
- [ ] No package name below rank

**Expected Results for test_starter_0**:
- [ ] Income shows: ₱0.00 (gray color)
- [ ] Rank shows: "Starter" badge (info/turquoise color)
- [ ] Package name shows below: "Starter Package" (small, muted text)

**Expected Results for test_starter_60**:
- [ ] Income shows: **₱250.50** (green bold text)
- [ ] Rank shows: "Starter" badge (info color)
- [ ] Package name below badge

**Expected Results for test_starter_eligible**:
- [ ] Income shows: **₱1,234.56** (green bold text)
- [ ] Rank badge: "Starter"

**Expected Results for test_newbie**:
- [ ] Income shows: **₱3,456.78** (green bold text)
- [ ] Rank badge: "Newbie" (info color)
- [ ] Package name below

**Expected Results for test_bronze**:
- [ ] Income shows: **₱12,345.67** (green bold text)
- [ ] Rank badge: "Bronze" (info color)
- [ ] Package name below

**Screenshot**: Capture full table view

---

### Test 8: Rank Badge Tooltip

**Steps**:
1. In Admin → User Management
2. Hover mouse over any rank badge (e.g., "Starter", "Newbie")

**Expected Results**:
- [ ] Tooltip appears
- [ ] Tooltip shows: "Rank Order: X" (e.g., "Rank Order: 1" for Starter)
- [ ] Tooltip disappears when mouse moves away

---

## Performance Tests

### Test 9: Admin Table Query Performance

**Steps**:
1. Open Chrome DevTools (F12)
2. Go to Network tab
3. Navigate to Admin → User Management
4. Check the page load time

**Expected Results**:
- [ ] Page loads in < 500ms (local environment)
- [ ] No console errors in DevTools
- [ ] No JavaScript errors

**Database Query Check**:

```bash
php artisan tinker
```

```php
// Enable query log
DB::enableQueryLog();

// Load users with relationships
$users = App\Models\User::with(['roles', 'wallet', 'rankPackage'])->take(10)->get();

// View queries
$queries = DB::getQueryLog();
echo count($queries) . " queries executed\n";
foreach ($queries as $query) {
    echo $query['query'] . "\n";
    echo "Time: " . $query['time'] . "ms\n\n";
}
```

**Expected Results**:
- [ ] Total queries: 3 or less (for 10 users)
- [ ] Query 1: SELECT * FROM users
- [ ] Query 2: SELECT * FROM packages WHERE id IN (...)
- [ ] Query 3: SELECT * FROM wallets WHERE user_id IN (...)
- [ ] **NO N+1 queries** (no individual queries per user)
- [ ] Total time: < 50ms for all queries

---

### Test 10: Profile Page Query Performance

**Steps**:
1. Login as `test_newbie`
2. In tinker, test profile loading:

```php
DB::enableQueryLog();

$user = App\Models\User::where('username', 'test_newbie')->first();
$user->load(['rankPackage', 'rankAdvancements' => function($query) {
    $query->orderBy('created_at', 'desc')->limit(5);
}]);

$queries = DB::getQueryLog();
echo count($queries) . " queries\n";
```

**Expected Results**:
- [ ] Total queries: 3 or less
- [ ] Query 1: SELECT * FROM users WHERE username = ...
- [ ] Query 2: SELECT * FROM packages WHERE id = ...
- [ ] Query 3: SELECT * FROM rank_advancements WHERE user_id = ... LIMIT 5
- [ ] No additional queries
- [ ] Total time: < 30ms

---

## Visual/UI Tests

### Test 11: Rank Card Visual Design

**Steps**:
1. Login as `test_starter_60`
2. Navigate to Profile
3. Inspect rank card visually

**Expected Results**:
- [ ] Card has info-colored header (turquoise background)
- [ ] White text on header
- [ ] Star icon visible in header
- [ ] Rank badge icon visible (badge/medal icon)
- [ ] Text is readable (not too small)
- [ ] Progress bar has proper height (~20px)
- [ ] Progress bar percentage text is centered
- [ ] Spacing between elements is consistent
- [ ] Card fits well in sidebar (not too wide/tall)

---

### Test 12: Progress Bar Colors

**Steps**:
1. Test each progress state

**For 0-99% progress** (test_starter_60):
- [ ] Progress bar color: Blue (primary)
- [ ] Remaining part: Light gray

**For 100% eligible** (test_starter_eligible):
- [ ] Progress bar color: Green (success)
- [ ] Alert box: Green background

---

### Test 13: Badge Colors

**Steps**:
1. Check badge colors across different types

**In Profile - Rank History**:
- [ ] "Reward" badge: Blue/Primary
- [ ] "Purchase" badge: Cyan/Info
- [ ] "Admin" badge: Yellow/Warning
- [ ] "From" rank badge: Gray/Secondary
- [ ] "To" rank badge: Green/Success

**In Admin Table**:
- [ ] Ranked user badges: Info/Turquoise
- [ ] Unranked badge: Gray/Secondary

---

### Test 14: Icons Display

**Steps**:
1. Check all icons render correctly

**Profile View**:
- [ ] Star icon in card header (cil-star)
- [ ] Badge/medal icon next to rank name (cil-badge)
- [ ] All icons are SVG (not broken images)
- [ ] Icons have appropriate size

---

## Edge Case Tests

### Test 15: User with No Wallet

**Steps**:
1. Create test user without wallet:

```bash
php artisan tinker
```

```php
$user = App\Models\User::create([
    'username' => 'test_no_wallet',
    'fullname' => 'No Wallet User',
    'email' => 'test_no_wallet@test.com',
    'password' => Hash::make('password'),
]);
```

2. Login as admin
3. Go to Admin → User Management
4. Find `test_no_wallet` in table

**Expected Results**:
- [ ] Income column shows: ₱0.00 (gray)
- [ ] No error/exception displayed
- [ ] Page loads successfully

---

### Test 16: User with Multiple Advancements (>5)

**Steps**:
1. Create user with 7 advancements:

```php
$user = App\Models\User::create([
    'username' => 'test_many_adv',
    'fullname' => 'Many Advancements',
    'email' => 'test_many_adv@test.com',
    'password' => Hash::make('password'),
    'current_rank' => 'Bronze',
    'rank_package_id' => 3,
]);

// Create 7 advancements
for ($i = 1; $i <= 7; $i++) {
    App\Models\RankAdvancement::create([
        'user_id' => $user->id,
        'from_rank' => 'Test' . ($i-1),
        'to_rank' => 'Test' . $i,
        'advancement_type' => 'purchase',
        'created_at' => now()->subDays(14 - $i*2),
    ]);
}
```

2. Login as `test_many_adv`
3. Go to Profile

**Expected Results**:
- [ ] Rank History section visible
- [ ] **Only 5 advancements shown** (not all 7)
- [ ] Shows most recent 5 (latest dates)
- [ ] Counter shows: "Showing 5 of 7 advancements"
- [ ] No performance issues

---

### Test 17: User with Very Long Username

**Steps**:
1. Check admin table with long username

**Expected Results**:
- [ ] Long usernames don't break table layout
- [ ] Text wraps or truncates appropriately
- [ ] Table remains responsive

---

### Test 18: Zero Sponsor Requirement

**Steps**:
1. Temporarily modify a package:

```bash
php artisan tinker
```

```php
$pkg = App\Models\Package::where('rank_name', 'Starter')->first();
$pkg->update(['required_direct_sponsors' => 0]);
```

2. Login as `test_starter_0`
3. View profile

**Expected Results**:
- [ ] Progress shows: "0 / 0"
- [ ] Progress bar at 100%
- [ ] Shows eligible message (or handles gracefully)
- [ ] No division by zero errors

4. Restore original value:

```php
$pkg->update(['required_direct_sponsors' => 5]);
```

---

## Browser Compatibility Tests

### Test 19: Chrome

**Steps**:
1. Open in Google Chrome
2. Test profile view (login as `test_newbie`)
3. Test admin table

**Expected Results**:
- [ ] All elements render correctly
- [ ] Progress bar displays properly
- [ ] Icons load correctly
- [ ] No console errors

---

### Test 20: Firefox

**Steps**:
1. Open in Mozilla Firefox
2. Repeat profile and admin tests

**Expected Results**:
- [ ] All elements render correctly
- [ ] Progress bar displays properly
- [ ] Colors match Chrome
- [ ] No console errors

---

### Test 21: Edge

**Steps**:
1. Open in Microsoft Edge
2. Repeat profile and admin tests

**Expected Results**:
- [ ] All elements render correctly
- [ ] Progress bar displays properly
- [ ] No rendering issues

---

## Mobile Responsive Tests

### Test 22: Mobile Profile View

**Steps**:
1. Open Chrome DevTools (F12)
2. Toggle device toolbar (Ctrl+Shift+M)
3. Select "iPhone SE" or similar small device
4. Login as `test_starter_60`
5. Navigate to Profile

**Expected Results**:
- [ ] Rank card is visible
- [ ] Card stacks correctly in mobile layout
- [ ] Progress bar is readable
- [ ] Text doesn't overflow
- [ ] Rank history table scrolls horizontally if needed
- [ ] Touch targets are adequate size (>44px)

---

### Test 23: Mobile Admin Table

**Steps**:
1. Still in mobile view
2. Login as admin
3. Go to Admin → User Management

**Expected Results**:
- [ ] Table is scrollable horizontally
- [ ] Income column visible
- [ ] Rank column visible
- [ ] Text remains readable
- [ ] Badges don't wrap awkwardly

---

### Test 24: Tablet View (iPad)

**Steps**:
1. In DevTools, select "iPad"
2. Test profile and admin views

**Expected Results**:
- [ ] Layout adapts to tablet size
- [ ] Rank card visible in appropriate column
- [ ] Admin table fits within viewport (or scrolls)
- [ ] No elements cut off

---

## Cleanup & Reporting

### Test 25: Cleanup Test Data (Optional)

**After all tests complete**, you can clean up test users:

```bash
php artisan tinker
```

```php
// Delete test users
App\Models\User::where('username', 'LIKE', 'test_%')->delete();
App\Models\User::where('username', 'LIKE', 'referral_%')->delete();

echo "Test users cleaned up\n";
```

**Note**: Skip cleanup if you want to keep test data for Phase 5 testing.

---

## Test Results Summary

### Checklist Overview

**User Profile Tests** (6 tests):
- [ ] Test 1: Unranked user profile
- [ ] Test 2: Starter 0% progress
- [ ] Test 3: Starter 60% progress
- [ ] Test 4: Starter 100% eligible
- [ ] Test 5: Newbie with history
- [ ] Test 6: Bronze top rank

**Admin Table Tests** (2 tests):
- [ ] Test 7: Admin user table display
- [ ] Test 8: Rank badge tooltip

**Performance Tests** (2 tests):
- [ ] Test 9: Admin table queries
- [ ] Test 10: Profile page queries

**Visual/UI Tests** (4 tests):
- [ ] Test 11: Rank card design
- [ ] Test 12: Progress bar colors
- [ ] Test 13: Badge colors
- [ ] Test 14: Icons display

**Edge Case Tests** (4 tests):
- [ ] Test 15: User with no wallet
- [ ] Test 16: User with >5 advancements
- [ ] Test 17: Long username
- [ ] Test 18: Zero sponsor requirement

**Browser Compatibility Tests** (3 tests):
- [ ] Test 19: Chrome
- [ ] Test 20: Firefox
- [ ] Test 21: Edge

**Mobile Responsive Tests** (3 tests):
- [ ] Test 22: Mobile profile view
- [ ] Test 23: Mobile admin table
- [ ] Test 24: Tablet view

---

## Common Issues & Solutions

### Issue 1: Rank card not visible

**Solution**:
- Clear view cache: `php artisan view:clear`
- Check if user is loaded correctly in ProfileController
- Verify rankPackage relationship exists

### Issue 2: Progress bar shows NaN%

**Solution**:
- Check `required_direct_sponsors` is not 0 in database
- Verify `getSameRankSponsorsCount()` returns integer

### Issue 3: Income shows ₱0.00 for users with balance

**Solution**:
- Check wallet relationship loaded: `User::with('wallet')`
- Verify `withdrawable_balance` column exists in wallets table
- Check if using correct attribute: `$user->wallet->total_balance` or `$user->wallet->withdrawable_balance`
- Profile page should show: Total Available Balance (withdrawable + purchase)

### Issue 4: Rank history not showing

**Solution**:
- Check `rankAdvancements` relationship loaded in ProfileController
- Verify RankAdvancement records exist for user
- Check limit is set correctly (5 records)

### Issue 5: Icons not displaying

**Solution**:
- Check CoreUI assets are loaded correctly
- Verify SVG sprite path is correct
- Inspect browser console for 404 errors

### Issue 6: Wallet Operations menu not visible

**Solution**:
- Check user has 'member' role assigned
- Run: `php verify_test_user_roles.php`
- Ensure user has permissions: deposit_funds, transfer_funds, withdraw_funds, view_transactions
- If missing, assign role: `$user->assignRole('member')`

### Issue 7: User shows 100% progress instead of being advanced

**Solution**:
- This means synchronous advancement didn't trigger yet
- In production, advancement happens when package is purchased
- For testing, manually process: `php artisan rank:process-advancements --user-id=[ID]`
- Or run: `php process_pending_rank_advancements.php`

---

## Performance Benchmarks

**Acceptable Performance Thresholds**:

| Test | Acceptable | Excellent |
|------|-----------|-----------|
| Profile page load | < 500ms | < 300ms |
| Admin table (100 users) | < 800ms | < 400ms |
| Profile queries | ≤ 5 | ≤ 3 |
| Admin queries | ≤ 5 | ≤ 3 |
| Mobile profile load | < 1000ms | < 600ms |

---

## Sign-Off Checklist

Before proceeding to Phase 5, ensure:

- [ ] All 24 tests completed
- [ ] All critical tests passed (User Profile, Admin Table, Performance)
- [ ] No console errors in any browser
- [ ] Mobile responsive layouts work correctly
- [ ] Performance within acceptable thresholds
- [ ] Screenshots captured for documentation
- [ ] Any issues documented and resolved
- [ ] Test data created and verified
- [ ] Ready for production deployment (if needed)

---

## Test Report Template

```
PHASE 4 TESTING REPORT
Date: _____________
Tester: _____________
Environment: Local / Staging / Production

SUMMARY:
- Total Tests: 24
- Passed: ___
- Failed: ___
- Skipped: ___

CRITICAL TESTS:
✓ User profile rank display: PASS / FAIL
✓ Admin table income/rank columns: PASS / FAIL
✓ Performance benchmarks: PASS / FAIL
✓ Mobile responsive: PASS / FAIL

ISSUES FOUND:
1. ____________________
2. ____________________

RECOMMENDATIONS:
____________________
____________________

READY FOR PHASE 5: YES / NO
READY FOR PRODUCTION: YES / NO

Signature: _____________
```

---

## Additional Verification Scripts

### Verify Test User Roles
```bash
php verify_test_user_roles.php
```
Checks that test users have member role and wallet permissions.

### Verify Synchronous Advancement
```bash
php test_synchronous_advancement.php
```
Demonstrates immediate advancement when requirements are met.

### Process Pending Advancements
```bash
php process_pending_rank_advancements.php
```
Checks all ranked users and advances eligible ones (one-time for legacy data).

### Demo Instant Advancement
```bash
php demo_instant_advancement.php
```
Full demonstration of synchronous advancement with timing information.

---

## Production Deployment Notes

### No Scheduled Tasks Required

**Important:** Rank advancement is fully synchronous. You do NOT need to:
- ❌ Set up cron jobs
- ❌ Configure Laravel scheduler
- ❌ Run queue workers for rank advancement

The system automatically advances users **instantly** when they meet requirements during package purchase.

### What to Deploy

**Required Files:**
- All Phase 4 view updates (profile, admin table)
- RankAdvancementService (already in place)
- UserObserver updates (tracks sponsorships)
- CheckoutController updates (triggers advancement)

**Optional Files (for troubleshooting only):**
- ProcessRankAdvancements command
- Manual advancement scripts

**Configuration:**
- No additional configuration needed
- System works out of the box

### Post-Deployment Verification

```bash
# Check recent advancements in production
php artisan tinker
>>> App\Models\RankAdvancement::latest()->take(5)->get()

# Verify a specific user's rank
>>> App\Models\User::find(123)->current_rank

# Check advancement logs
tail -f storage/logs/laravel.log | grep "Rank"
```

---

## Key Documentation References

- **SYNCHRONOUS_RANK_ADVANCEMENT.md** - How instant advancement works
- **RANK_ADVANCEMENT_SUMMARY.md** - Complete system overview
- **LEGACY_RANK_MIGRATION.md** - Migrating existing users
- **RANK_ADVANCEMENT_AUTOMATION.md** - Technical documentation

---

## Next Steps

**If all tests pass**:
✅ Proceed to Phase 5 (Admin Configuration Interface)
✅ Deploy Phase 4 to production
✅ Migrate legacy users (if applicable): `php migrate_legacy_rank_data.php`
✅ Document any customizations made during testing
✅ Verify synchronous advancement is working in production

**If tests fail**:
❌ Review failed tests
❌ Fix identified issues
❌ Re-test affected areas
❌ Update documentation
❌ Check logs for detailed error messages

---

## Testing Checklist Summary

Before marking Phase 4 as complete:

- [ ] All 24 core tests passed
- [ ] Synchronous advancement verified (users advance instantly)
- [ ] Legacy users migrated (if applicable)
- [ ] Test users have member role and wallet permissions
- [ ] Wallet Information shows combined balance correctly
- [ ] Test users linked to sponsors properly
- [ ] No console errors in any browser
- [ ] Mobile responsive layouts work
- [ ] Performance within acceptable thresholds
- [ ] Documentation updated and accurate
- [ ] Ready for production deployment

---

**Testing Complete!** 🎉

This comprehensive guide ensures Phase 4 is thoroughly tested and production-ready. The synchronous rank advancement system provides instant gratification for users - they see their promotions the moment they earn them!

**Key Achievement:** Users are promoted **immediately** when they meet requirements - no delays, no scheduled tasks, just instant results! ⚡
