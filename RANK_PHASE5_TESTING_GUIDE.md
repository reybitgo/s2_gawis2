# Rank System Phase 5: Comprehensive Testing Guide

**Date:** December 2, 2025  
**Phase:** Phase 5 Testing  
**Document Version:** 1.0

---

## Table of Contents

1. [Pre-Testing Requirements](#pre-testing-requirements)
2. [Test Environment Setup](#test-environment-setup)
3. [Testing Scope](#testing-scope)
4. [Test Scenarios](#test-scenarios)
5. [Detailed Test Cases](#detailed-test-cases)
6. [Security Testing](#security-testing)
7. [Performance Testing](#performance-testing)
8. [UI/UX Testing](#uiux-testing)
9. [Integration Testing](#integration-testing)
10. [Regression Testing](#regression-testing)
11. [Test Data Setup](#test-data-setup)
12. [Expected Results](#expected-results)
13. [Bug Reporting](#bug-reporting)
14. [Test Sign-Off](#test-sign-off)

---

## Pre-Testing Requirements

### 1. Environment Checklist

Before starting tests, verify the following:

```bash
# Check PHP version (>= 8.0)
php --version

# Check database connection
php artisan db:show

# Verify all migrations are current
php artisan migrate:status

# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Check application status
php artisan about
```

### 2. Required Data

Ensure the following data exists:

- ✅ Admin user account with role 'admin'
- ✅ At least 5 rankable packages configured
- ✅ Multiple users with different ranks
- ✅ Historical rank advancement records
- ✅ Sample orders with rank packages

### 3. Browser Setup

**Recommended Browsers:**
- Chrome (latest)
- Firefox (latest)
- Edge (latest)
- Safari (for Mac users)

**Browser Extensions:**
- Browser console enabled
- Network tab available
- Storage inspector enabled

### 4. Required Access

- ✅ Admin credentials
- ✅ Database access (for verification)
- ✅ Laravel logs access (`storage/logs/laravel.log`)
- ✅ Server console access

---

## Test Environment Setup

### Step 1: Create Fresh Test Data

Run the following script to set up test data:

```php
<?php
// File: setup_phase5_test_data.php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RankAdvancement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

DB::beginTransaction();

try {
    echo "🔧 Setting up Phase 5 test data...\n\n";

    // 1. Create admin user (if not exists)
    $admin = User::firstOrCreate(
        ['email' => 'admin@test.com'],
        [
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]
    );
    echo "✅ Admin user created: {$admin->email}\n";

    // 2. Create rankable packages
    $packages = [
        [
            'name' => 'Starter Pack',
            'price' => 1000,
            'is_rankable' => true,
            'rank_name' => 'Starter',
            'rank_order' => 1,
            'required_direct_sponsors' => 5,
            'pv' => 10,
        ],
        [
            'name' => 'Newbie Pack',
            'price' => 2500,
            'is_rankable' => true,
            'rank_name' => 'Newbie',
            'rank_order' => 2,
            'required_direct_sponsors' => 8,
            'pv' => 25,
        ],
        [
            'name' => 'Bronze Pack',
            'price' => 5000,
            'is_rankable' => true,
            'rank_name' => 'Bronze',
            'rank_order' => 3,
            'required_direct_sponsors' => 10,
            'pv' => 50,
        ],
        [
            'name' => 'Silver Pack',
            'price' => 10000,
            'is_rankable' => true,
            'rank_name' => 'Silver',
            'rank_order' => 4,
            'required_direct_sponsors' => 15,
            'pv' => 100,
        ],
        [
            'name' => 'Gold Pack',
            'price' => 25000,
            'is_rankable' => true,
            'rank_name' => 'Gold',
            'rank_order' => 5,
            'required_direct_sponsors' => 20,
            'pv' => 250,
        ],
    ];

    $createdPackages = [];
    foreach ($packages as $packageData) {
        $package = Package::firstOrCreate(
            ['name' => $packageData['name']],
            $packageData
        );
        $createdPackages[] = $package;
        echo "✅ Package created: {$package->name} ({$package->rank_name})\n";
    }

    // Set next_rank_package_id for progression
    for ($i = 0; $i < count($createdPackages) - 1; $i++) {
        $createdPackages[$i]->next_rank_package_id = $createdPackages[$i + 1]->id;
        $createdPackages[$i]->save();
    }
    echo "✅ Rank progression chain configured\n\n";

    // 3. Create test users with different ranks
    $testUsers = [
        ['username' => 'user_starter', 'rank' => 'Starter', 'package' => 0],
        ['username' => 'user_newbie', 'rank' => 'Newbie', 'package' => 1],
        ['username' => 'user_bronze', 'rank' => 'Bronze', 'package' => 2],
        ['username' => 'user_silver', 'rank' => 'Silver', 'package' => 3],
        ['username' => 'user_gold', 'rank' => 'Gold', 'package' => 4],
        ['username' => 'user_norank1', 'rank' => null, 'package' => null],
        ['username' => 'user_norank2', 'rank' => null, 'package' => null],
    ];

    $users = [];
    foreach ($testUsers as $userData) {
        $user = User::firstOrCreate(
            ['username' => $userData['username']],
            [
                'email' => $userData['username'] . '@test.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'email_verified_at' => now(),
                'current_rank' => $userData['rank'],
                'rank_package_id' => $userData['package'] !== null ? $createdPackages[$userData['package']]->id : null,
            ]
        );
        $users[] = $user;
        echo "✅ User created: {$user->username} (Rank: " . ($user->current_rank ?? 'None') . ")\n";
    }
    echo "\n";

    // 4. Create historical rank advancements
    $advancementTypes = ['reward', 'purchase', 'admin'];
    $advancementCount = 0;

    foreach ($users as $index => $user) {
        if ($user->current_rank) {
            // Create 1-3 advancement records per ranked user
            $numAdvancements = rand(1, 3);
            
            for ($i = 0; $i < $numAdvancements; $i++) {
                $type = $advancementTypes[array_rand($advancementTypes)];
                
                // Create an order first
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => 'ORD-TEST-' . time() . '-' . $advancementCount,
                    'subtotal' => $createdPackages[$index % count($createdPackages)]->price,
                    'total' => $createdPackages[$index % count($createdPackages)]->price,
                    'status' => 'completed',
                    'payment_method' => $type === 'reward' ? 'ewallet' : 'gcash',
                    'payment_status' => 'paid',
                    'paid_at' => now()->subDays(rand(1, 30)),
                ]);

                OrderItem::create([
                    'order_id' => $order->id,
                    'package_id' => $createdPackages[$index % count($createdPackages)]->id,
                    'quantity' => 1,
                    'price' => $createdPackages[$index % count($createdPackages)]->price,
                ]);

                RankAdvancement::create([
                    'user_id' => $user->id,
                    'from_rank' => $i > 0 ? $createdPackages[($index - 1) % count($createdPackages)]->rank_name : null,
                    'to_rank' => $user->current_rank,
                    'from_package_id' => $i > 0 ? $createdPackages[($index - 1) % count($createdPackages)]->id : null,
                    'to_package_id' => $user->rank_package_id,
                    'type' => $type,
                    'order_id' => $order->id,
                    'qualified_sponsors_count' => rand(5, 20),
                    'system_paid' => $type === 'reward' ? $createdPackages[$index % count($createdPackages)]->price : 0,
                    'notes' => $type === 'admin' ? 'Manual advancement for testing purposes' : null,
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);

                $advancementCount++;
            }
        }
    }
    echo "✅ Created {$advancementCount} historical rank advancements\n\n";

    DB::commit();
    echo "✅ Phase 5 test data setup complete!\n\n";
    echo "📊 Summary:\n";
    echo "   - Packages: " . count($createdPackages) . "\n";
    echo "   - Users: " . count($users) . "\n";
    echo "   - Advancements: {$advancementCount}\n\n";
    echo "🔑 Admin Login:\n";
    echo "   Email: admin@test.com\n";
    echo "   Password: password\n\n";

} catch (Exception $e) {
    DB::rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
```

Run the script:

```bash
php setup_phase5_test_data.php
```

### Step 2: Verify Test Data

```sql
-- Check packages
SELECT id, name, rank_name, rank_order, required_direct_sponsors, next_rank_package_id 
FROM packages 
WHERE is_rankable = 1;

-- Check users with ranks
SELECT id, username, email, current_rank, rank_package_id 
FROM users;

-- Check rank advancements
SELECT id, user_id, from_rank, to_rank, type, system_paid, created_at 
FROM rank_advancements 
ORDER BY created_at DESC 
LIMIT 10;
```

---

## Testing Scope

### Features to Test

1. **Rank Dashboard** (AdminRankController@index)
   - Statistics display
   - Rank packages table
   - Chart visualization
   - Navigation links

2. **Rank Configuration** (AdminRankController@configure & updateConfiguration)
   - Form display
   - Field editing
   - Validation
   - Save functionality

3. **Advancement History** (AdminRankController@advancements)
   - List display
   - Filtering
   - Search
   - Pagination

4. **Manual Advancement** (AdminRankController@manualAdvance)
   - User selection
   - Package selection
   - Order creation
   - Advancement recording

5. **Security**
   - Authentication
   - Authorization
   - CSRF protection
   - Input validation

---

## Test Scenarios

### Scenario 1: Admin Access Control

**Objective:** Verify that only admin users can access rank management pages

**Priority:** High  
**Type:** Security

### Scenario 2: Dashboard Data Display

**Objective:** Verify dashboard shows accurate statistics and visualizations

**Priority:** High  
**Type:** Functional

### Scenario 3: Configuration Management

**Objective:** Verify rank configuration can be updated successfully

**Priority:** High  
**Type:** Functional

### Scenario 4: Advancement Tracking

**Objective:** Verify advancement history displays correctly with filters

**Priority:** High  
**Type:** Functional

### Scenario 5: Manual User Advancement

**Objective:** Verify admin can manually advance user ranks

**Priority:** High  
**Type:** Functional

---

## Detailed Test Cases

### Test Case 1.1: Non-Admin Access Restriction

**Scenario:** Verify non-admin users cannot access rank pages

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Log out if currently logged in | Redirected to login page | ⬜ |
| 2 | Log in as regular user (not admin) | Successfully logged in | ⬜ |
| 3 | Navigate to `/admin/ranks` | Redirected to error/unauthorized page | ⬜ |
| 4 | Navigate to `/admin/ranks/configure` | Redirected to error/unauthorized page | ⬜ |
| 5 | Navigate to `/admin/ranks/advancements` | Redirected to error/unauthorized page | ⬜ |

**Test Data:**
- Regular user: `user_starter@test.com` / `password`

**Expected Result:** All pages should deny access with 403 or redirect to unauthorized page

---

### Test Case 1.2: Admin Access Grant

**Scenario:** Verify admin users can access all rank pages

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Log out if currently logged in | Redirected to login page | ⬜ |
| 2 | Log in as admin user | Successfully logged in | ⬜ |
| 3 | Navigate to `/admin/ranks` | Dashboard loads successfully | ⬜ |
| 4 | Click "Configure Ranks" button | Configure page loads | ⬜ |
| 5 | Navigate to `/admin/ranks/advancements` | Advancements page loads | ⬜ |

**Test Data:**
- Admin user: `admin@test.com` / `password`

**Expected Result:** All pages load successfully with no errors

---

### Test Case 2.1: Dashboard Statistics Display

**Scenario:** Verify dashboard displays correct statistics

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Log in as admin | Successfully logged in | ⬜ |
| 2 | Navigate to `/admin/ranks` | Dashboard loads | ⬜ |
| 3 | Locate "Ranked Users" card | Card displays total count | ⬜ |
| 4 | Locate "Total Advancements" card | Card displays advancement count | ⬜ |
| 5 | Locate "System Rewards" card | Card displays reward count | ⬜ |
| 6 | Locate "System Paid" card | Card displays total amount | ⬜ |

**Verification Query:**

```sql
-- Verify Ranked Users count
SELECT COUNT(*) FROM users WHERE current_rank IS NOT NULL;

-- Verify Total Advancements
SELECT COUNT(*) FROM rank_advancements;

-- Verify System Rewards count
SELECT COUNT(*) FROM rank_advancements WHERE type = 'reward';

-- Verify System Paid amount
SELECT SUM(system_paid) FROM rank_advancements WHERE type = 'reward';
```

**Expected Result:** All statistics match database query results

---

### Test Case 2.2: Rank Packages Table

**Scenario:** Verify rank packages table displays correctly

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | On dashboard, locate rank packages table | Table displays | ⬜ |
| 2 | Verify table has columns: Rank, Package, Price, Order, Sponsors, Users | All columns present | ⬜ |
| 3 | Check if all rankable packages are listed | All packages shown | ⬜ |
| 4 | Verify user count per rank | Counts match database | ⬜ |
| 5 | Check sorting (by rank_order) | Packages sorted correctly | ⬜ |

**Verification Query:**

```sql
SELECT 
    p.rank_name,
    p.name,
    p.price,
    p.rank_order,
    p.required_direct_sponsors,
    COUNT(u.id) as user_count
FROM packages p
LEFT JOIN users u ON u.rank_package_id = p.id
WHERE p.is_rankable = 1
GROUP BY p.id
ORDER BY p.rank_order;
```

**Expected Result:** Table data matches query results

---

### Test Case 2.3: Chart Visualization

**Scenario:** Verify rank distribution chart renders correctly

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | On dashboard, scroll to chart section | Chart canvas visible | ⬜ |
| 2 | Wait for chart to load | Bar chart displays | ⬜ |
| 3 | Verify X-axis shows rank names | All ranks labeled | ⬜ |
| 4 | Verify Y-axis shows user counts | Counts are numeric | ⬜ |
| 5 | Hover over bars | Tooltips appear with exact counts | ⬜ |
| 6 | Check chart colors | Different colors for each rank | ⬜ |

**Browser Console Check:**
- Open browser console (F12)
- Look for Chart.js logs
- No JavaScript errors

**Expected Result:** Chart displays without errors and shows accurate data

---

### Test Case 2.4: Empty State Handling

**Scenario:** Verify dashboard handles empty state gracefully

**Note:** This requires temporarily clearing data. Skip if data is needed.

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Clear rank advancements (optional test) | Test environment ready | ⬜ |
| 2 | Navigate to dashboard | Dashboard loads | ⬜ |
| 3 | Check statistics cards | Show 0 or "No data" | ⬜ |
| 4 | Check chart area | Shows "No data available" message | ⬜ |
| 5 | Check packages table | Shows "No users" in user count column | ⬜ |

**Expected Result:** No errors, appropriate empty state messages

---

### Test Case 3.1: Configuration Form Load

**Scenario:** Verify configuration form loads with current values

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Navigate to `/admin/ranks/configure` | Configuration page loads | ⬜ |
| 2 | Verify form contains all rankable packages | All packages in form | ⬜ |
| 3 | Check "Rank Name" fields | Pre-filled with current values | ⬜ |
| 4 | Check "Rank Order" fields | Pre-filled with current values | ⬜ |
| 5 | Check "Required Sponsors" fields | Pre-filled with current values | ⬜ |
| 6 | Check "Next Rank Package" dropdowns | Current selection shown | ⬜ |
| 7 | Check "Price" fields | Display only, not editable | ⬜ |

**Expected Result:** Form loads with all current configuration values

---

### Test Case 3.2: Configuration Update - Valid Data

**Scenario:** Verify configuration saves with valid data

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | On configure page, modify first package's rank name | Field updated | ⬜ |
| 2 | Change "Starter" to "Beginner" | Value changes | ⬜ |
| 3 | Modify required sponsors from 5 to 6 | Value changes | ⬜ |
| 4 | Click "Save Configuration" button | Form submits | ⬜ |
| 5 | Check for success message | Green success alert appears | ⬜ |
| 6 | Refresh page | New values retained | ⬜ |

**Verification Query:**

```sql
SELECT rank_name, required_direct_sponsors 
FROM packages 
WHERE name = 'Starter Pack';
```

**Expected Result:** Changes saved to database, success message shown

---

### Test Case 3.3: Configuration Update - Invalid Data

**Scenario:** Verify validation prevents invalid configuration

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | On configure page, clear rank name field | Field empty | ⬜ |
| 2 | Click "Save Configuration" | Form validation triggers | ⬜ |
| 3 | Check for error message | Red error alert appears | ⬜ |
| 4 | Verify error mentions "required" | Error message clear | ⬜ |
| 5 | Enter negative number in rank order | Field shows negative | ⬜ |
| 6 | Click "Save Configuration" | Validation error for min value | ⬜ |
| 7 | Enter negative sponsors count | Field shows negative | ⬜ |
| 8 | Click "Save Configuration" | Validation error for min value | ⬜ |

**Expected Result:** All invalid inputs rejected with clear error messages

---

### Test Case 3.4: Configuration Transaction Rollback

**Scenario:** Verify transaction rollback on partial failure

**Note:** This test requires database manipulation or code modification

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Modify multiple package configurations | All fields updated | ⬜ |
| 2 | Make last package have duplicate rank_order | Potential conflict | ⬜ |
| 3 | Click "Save Configuration" | Error occurs | ⬜ |
| 4 | Check database | No partial updates saved | ⬜ |
| 5 | Refresh form | Original values still present | ⬜ |

**Expected Result:** Either all changes save or none (transaction integrity)

---

### Test Case 3.5: Next Rank Package Selection

**Scenario:** Verify next rank package dropdown works correctly

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | On configure page, locate first package | Row found | ⬜ |
| 2 | Click "Next Rank Package" dropdown | Dropdown opens | ⬜ |
| 3 | Verify dropdown shows other packages | Options available | ⬜ |
| 4 | Select a next rank package | Selection made | ⬜ |
| 5 | Click "Save Configuration" | Form saves | ⬜ |
| 6 | Verify next_rank_package_id updated | Database reflects change | ⬜ |

**Verification Query:**

```sql
SELECT id, name, rank_name, next_rank_package_id 
FROM packages 
WHERE is_rankable = 1;
```

**Expected Result:** Next rank package correctly assigned

---

### Test Case 4.1: Advancement List Display

**Scenario:** Verify advancement history displays all records

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Navigate to `/admin/ranks/advancements` | Page loads | ⬜ |
| 2 | Verify table headers present | Columns: Date, User, From, To, Type, Sponsors, System Paid, Actions | ⬜ |
| 3 | Check if advancements listed | Records displayed | ⬜ |
| 4 | Verify pagination appears | Pagination controls at bottom | ⬜ |
| 5 | Verify record count per page | Shows 15 records (default) | ⬜ |

**Expected Result:** All advancement records displayed with pagination

---

### Test Case 4.2: Type Filter

**Scenario:** Verify filtering by advancement type works

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | On advancements page, locate type filter | Dropdown found | ⬜ |
| 2 | Select "Sponsorship Reward" | Filter applied | ⬜ |
| 3 | Verify only reward type shown | All rows have "Sponsorship Reward" badge | ⬜ |
| 4 | Select "Direct Purchase" | Filter updated | ⬜ |
| 5 | Verify only purchase type shown | All rows have "Direct Purchase" badge | ⬜ |
| 6 | Select "Admin Adjustment" | Filter updated | ⬜ |
| 7 | Verify only admin type shown | All rows have "Admin Adjustment" badge | ⬜ |
| 8 | Select "All Types" | Filter cleared | ⬜ |
| 9 | Verify all types shown | All advancement types visible | ⬜ |

**Expected Result:** Filter accurately shows only selected type

---

### Test Case 4.3: Rank Filter

**Scenario:** Verify filtering by rank works

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | On advancements page, locate rank filter | Dropdown found | ⬜ |
| 2 | Verify dropdown contains all ranks | All rank names listed | ⬜ |
| 3 | Select a specific rank (e.g., "Bronze") | Filter applied | ⬜ |
| 4 | Verify only Bronze advancements shown | All "To" columns show Bronze | ⬜ |
| 5 | Select different rank | Filter updated | ⬜ |
| 6 | Select "All Ranks" | Filter cleared | ⬜ |

**Expected Result:** Filter shows only advancements to selected rank

---

### Test Case 4.4: User Search

**Scenario:** Verify search by username/email works

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | On advancements page, locate search box | Input field found | ⬜ |
| 2 | Enter partial username (e.g., "user_") | Text entered | ⬜ |
| 3 | Press Enter or click search | Search applied | ⬜ |
| 4 | Verify only matching users shown | Results filtered | ⬜ |
| 5 | Clear search box | Field empty | ⬜ |
| 6 | Enter email (e.g., "test.com") | Text entered | ⬜ |
| 7 | Press Enter | Search applied | ⬜ |
| 8 | Verify results match email domain | Results filtered | ⬜ |
| 9 | Enter non-existent user | Text entered | ⬜ |
| 10 | Verify "No advancements found" message | Empty state shown | ⬜ |

**Expected Result:** Search filters by username or email accurately

---

### Test Case 4.5: Combined Filters

**Scenario:** Verify multiple filters work together

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Select type "Sponsorship Reward" | Filter applied | ⬜ |
| 2 | Select rank "Bronze" | Both filters active | ⬜ |
| 3 | Verify results match both criteria | Only Bronze rewards shown | ⬜ |
| 4 | Add user search | Third filter applied | ⬜ |
| 5 | Verify all three filters active | Results narrow further | ⬜ |
| 6 | Click "Clear Filters" button | All filters removed | ⬜ |
| 7 | Verify all advancements shown again | Full list restored | ⬜ |

**Expected Result:** Multiple filters combine with AND logic

---

### Test Case 4.6: Pagination Navigation

**Scenario:** Verify pagination controls work correctly

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | On advancements page with multiple pages | Pagination visible | ⬜ |
| 2 | Click "Next" or page 2 | Navigate to page 2 | ⬜ |
| 3 | Verify different records shown | New records loaded | ⬜ |
| 4 | Verify page 2 is highlighted | Active page indicator | ⬜ |
| 5 | Click "Previous" or page 1 | Navigate back | ⬜ |
| 6 | Verify original records shown | First page restored | ⬜ |
| 7 | Click last page number | Navigate to last page | ⬜ |
| 8 | Verify "Next" disabled/hidden | Cannot go beyond last | ⬜ |

**Expected Result:** Pagination navigates correctly through all pages

---

### Test Case 4.7: Advancement Details

**Scenario:** Verify advancement details display correctly

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | On advancements page, locate a record | Record found | ⬜ |
| 2 | Verify date/time displayed | Readable format shown | ⬜ |
| 3 | Verify user information | Username and email shown | ⬜ |
| 4 | Check from/to rank badges | Badges colored appropriately | ⬜ |
| 5 | Verify type badge color coding | Correct color per type | ⬜ |
| 6 | Check sponsors count | Number displayed | ⬜ |
| 7 | Check system paid amount | Currency format correct | ⬜ |
| 8 | Hover over notes icon (if present) | Tooltip shows notes | ⬜ |

**Expected Result:** All advancement details accurate and well-formatted

---

### Test Case 4.8: Action Links

**Scenario:** Verify action links work correctly

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Locate advancement with order_id | Record found | ⬜ |
| 2 | Click "View Order" link | Redirects to order details page | ⬜ |
| 3 | Verify order details match | Correct order shown | ⬜ |
| 4 | Go back to advancements page | Page reloaded | ⬜ |
| 5 | Click "View User" link | Redirects to user profile/edit page | ⬜ |
| 6 | Verify user details match | Correct user shown | ⬜ |

**Expected Result:** All action links navigate to correct pages

---

### Test Case 5.1: Manual Advance - Basic Flow

**Scenario:** Verify manual advancement works for eligible user

**URL:** `/admin/users/edit/{user_id}` (e.g., http://s2_gawis2.test/admin/users/edit/5)

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Navigate to Admin → Users | User list loads | ⬜ |
| 2 | Find user "user_norank1" (or any test user) | User found | ⬜ |
| 3 | Click "Edit" button on the user row | User edit page loads | ⬜ |
| 4 | Locate "Rank Management" card on right sidebar | Card visible with current rank info | ⬜ |
| 5 | Click "Manual Rank Advance" button | Modal opens | ⬜ |
| 6 | Select target package from dropdown (e.g., "Starter Pack") | Package selected | ⬜ |
| 7 | Enter notes: "Testing manual advancement" | Notes entered | ⬜ |
| 8 | Click "Advance Rank" button | Form submits | ⬜ |
| 9 | Check for success message | Success alert appears | ⬜ |
| 10 | Verify user rank updated in Rank Management card | User now shows new rank | ⬜ |

**Verification Query:**

```sql
SELECT id, username, current_rank, rank_package_id 
FROM users 
WHERE username = 'user_norank1';

SELECT * FROM rank_advancements 
WHERE user_id = (SELECT id FROM users WHERE username = 'user_norank1')
ORDER BY created_at DESC LIMIT 1;
```

**Expected Result:** User rank updated, advancement recorded with type 'admin_adjustment'

**Note:** The Manual Rank Advance feature is now accessible from the user edit page at `/admin/users/edit/{user_id}` in the "Rank Management" card on the right sidebar.

---

### Test Case 5.2: Manual Advance - Order Creation

**Scenario:** Verify order is created for manual advancement

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Perform manual advancement (as above) | Advancement successful | ⬜ |
| 2 | Query orders table for new order | Order created | ⬜ |
| 3 | Verify order belongs to advanced user | user_id matches | ⬜ |
| 4 | Check payment method | Should be 'ewallet' | ⬜ |
| 5 | Check payment status | Should be 'paid' | ⬜ |
| 6 | Check order items | Package matches selected | ⬜ |
| 7 | Verify order total | Matches package price | ⬜ |

**Verification Query:**

```sql
SELECT o.*, oi.package_id, oi.price 
FROM orders o
JOIN order_items oi ON o.id = oi.order_id
WHERE o.user_id = (SELECT id FROM users WHERE username = 'user_norank1')
ORDER BY o.created_at DESC LIMIT 1;
```

**Expected Result:** Order created correctly with all details

---

### Test Case 5.3: Manual Advance - Network Activation

**Scenario:** Verify network status activated for first purchase

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Find user with network_active = false | User identified | ⬜ |
| 2 | Perform manual advancement | Advancement successful | ⬜ |
| 3 | Check user's network_active status | Should be true | ⬜ |
| 4 | Verify activation timestamp | activated_at set | ⬜ |

**Verification Query:**

```sql
SELECT id, username, network_active, activated_at 
FROM users 
WHERE username = 'user_norank2';
```

**Expected Result:** Network activated on first manual advancement

---

### Test Case 5.4: Manual Advance - Dropdown Filtering

**Scenario:** Verify dropdown only shows packages with higher ranks

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Find user already at Bronze rank (rank_order = 3) | User identified | ⬜ |
| 2 | Navigate to their edit page | Edit page loads | ⬜ |
| 3 | Click "Manual Rank Advance" button | Modal opens | ⬜ |
| 4 | Check package dropdown options | Dropdown opens | ⬜ |
| 5 | Verify only packages with rank_order > 3 shown | Only Silver, Gold, etc. visible | ⬜ |
| 6 | Verify Starter, Newbie, Bronze NOT in dropdown | Lower/same ranks excluded | ⬜ |

**Expected Result:** Dropdown automatically filters to show only valid advancement options

**Note:** The UI prevents selecting same or lower ranks. Manual advancement to same rank is prevented by the dropdown filtering, not backend validation.

---

## Security Testing

### Test Case S1: CSRF Token Validation

**Scenario:** Verify CSRF protection on form submissions

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | View configuration form page source | HTML loaded | ⬜ |
| 2 | Locate `@csrf` token in form | Token present | ⬜ |
| 3 | Submit form without CSRF token (using Postman) | Request rejected | ⬜ |
| 4 | Verify 419 error or redirect | CSRF validation works | ⬜ |

**Expected Result:** All POST requests require valid CSRF token

---

### Test Case S2: SQL Injection Prevention

**Scenario:** Verify protection against SQL injection

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | On advancements search, enter: `'; DROP TABLE users; --` | Input entered | ⬜ |
| 2 | Submit search | Search processes | ⬜ |
| 3 | Verify no database error | Query safe | ⬜ |
| 4 | Check database tables intact | All tables exist | ⬜ |
| 5 | Verify search returns no results or safe results | Protected | ⬜ |

**Expected Result:** SQL injection attempts safely handled by Eloquent ORM

---

### Test Case S3: XSS Prevention

**Scenario:** Verify protection against XSS attacks

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | On configure form, enter: `<script>alert('XSS')</script>` in rank name | Input entered | ⬜ |
| 2 | Save configuration | Form submits | ⬜ |
| 3 | Reload page | Page displays | ⬜ |
| 4 | Verify script not executed | No alert popup | ⬜ |
| 5 | Check page source | Script tags escaped | ⬜ |

**Expected Result:** XSS attempts escaped/sanitized by Blade templates

---

### Test Case S4: Authorization Bypass Attempt

**Scenario:** Verify cannot bypass role-based access control

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Log in as regular user | Login successful | ⬜ |
| 2 | Manually navigate to `/admin/ranks` via URL | Access denied | ⬜ |
| 3 | Try POST to `/admin/ranks/configure` with curl | 403 or redirect | ⬜ |
| 4 | Attempt to advance rank via direct API call | Unauthorized | ⬜ |

**Expected Result:** All unauthorized access attempts blocked

---

## Performance Testing

### Test Case P1: Dashboard Load Time

**Scenario:** Measure dashboard page load performance

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Clear browser cache | Cache cleared | ⬜ |
| 2 | Open DevTools Network tab | Network panel open | ⬜ |
| 3 | Navigate to `/admin/ranks` | Page loads | ⬜ |
| 4 | Check "Load" time in network panel | Time recorded | ⬜ |
| 5 | Verify load time < 2 seconds | Performance acceptable | ⬜ |

**Target:** < 2 seconds total load time

**Expected Result:** Dashboard loads efficiently

---

### Test Case P2: Query Optimization

**Scenario:** Verify efficient database queries

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Enable Laravel Debugbar or query logging | Logging active | ⬜ |
| 2 | Navigate to dashboard | Page loads | ⬜ |
| 3 | Count database queries | Query count recorded | ⬜ |
| 4 | Verify < 10 queries for dashboard | Efficient querying | ⬜ |
| 5 | Check for N+1 query problems | No N+1 issues | ⬜ |

**Target:** < 10 queries per page

**Expected Result:** Queries optimized with eager loading

---

### Test Case P3: Large Dataset Handling

**Scenario:** Test performance with large dataset

**Note:** Requires generating additional test data

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Generate 1000 rank advancements | Data created | ⬜ |
| 2 | Navigate to advancements page | Page loads | ⬜ |
| 3 | Verify pagination limits results | Only 15 per page | ⬜ |
| 4 | Check load time < 3 seconds | Performance acceptable | ⬜ |
| 5 | Apply filters | Filtering responsive | ⬜ |
| 6 | Navigate through pages | Pagination smooth | ⬜ |

**Expected Result:** Large datasets handled efficiently with pagination

---

## UI/UX Testing

### Test Case U1: Responsive Design - Mobile

**Scenario:** Verify mobile responsiveness

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Open Chrome DevTools | DevTools open | ⬜ |
| 2 | Toggle device toolbar (Ctrl+Shift+M) | Mobile view active | ⬜ |
| 3 | Select iPhone 12 Pro | Screen size set | ⬜ |
| 4 | Navigate to dashboard | Page renders | ⬜ |
| 5 | Verify cards stack vertically | Responsive layout | ⬜ |
| 6 | Check table scrolls horizontally | Table accessible | ⬜ |
| 7 | Test navigation menu | Menu functional | ⬜ |
| 8 | Try configuration form | Form usable | ⬜ |

**Expected Result:** All pages usable on mobile devices

---

### Test Case U2: Responsive Design - Tablet

**Scenario:** Verify tablet responsiveness

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Set device to iPad (768px) | Screen size set | ⬜ |
| 2 | Navigate through all rank pages | Pages render | ⬜ |
| 3 | Verify layout adapts appropriately | Responsive behavior | ⬜ |
| 4 | Test all interactive elements | Functional | ⬜ |

**Expected Result:** Optimized layout for tablet devices

---

### Test Case U3: Browser Compatibility

**Scenario:** Test cross-browser compatibility

| Browser | Dashboard | Configure | Advancements | Pass/Fail |
|---------|-----------|-----------|--------------|-----------|
| Chrome (latest) | ⬜ | ⬜ | ⬜ | ⬜ |
| Firefox (latest) | ⬜ | ⬜ | ⬜ | ⬜ |
| Edge (latest) | ⬜ | ⬜ | ⬜ | ⬜ |
| Safari (latest) | ⬜ | ⬜ | ⬜ | ⬜ |

**Expected Result:** Consistent functionality across all browsers

---

### Test Case U4: Accessibility - Keyboard Navigation

**Scenario:** Verify keyboard navigation works

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Navigate to dashboard | Page loads | ⬜ |
| 2 | Press Tab key repeatedly | Focus moves through elements | ⬜ |
| 3 | Verify focus visible on all buttons/links | Focus indicators present | ⬜ |
| 4 | Press Enter on "Configure" button | Navigate to configure page | ⬜ |
| 5 | Tab through form fields | All fields accessible | ⬜ |
| 6 | Submit form using Enter key | Form submits | ⬜ |

**Expected Result:** Full keyboard navigation support

---

### Test Case U5: Accessibility - Screen Reader

**Scenario:** Verify screen reader compatibility

**Note:** Requires screen reader software (NVDA, JAWS, or VoiceOver)

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Enable screen reader | Screen reader active | ⬜ |
| 2 | Navigate to dashboard | Page announced | ⬜ |
| 3 | Verify headings read correctly | Proper heading hierarchy | ⬜ |
| 4 | Check table announced properly | Row/column context provided | ⬜ |
| 5 | Verify form labels associated | Fields properly labeled | ⬜ |
| 6 | Check button purposes clear | Descriptive button text | ⬜ |

**Expected Result:** All content accessible to screen readers

---

### Test Case U6: Color Contrast

**Scenario:** Verify sufficient color contrast for readability

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Use browser color contrast checker | Tool ready | ⬜ |
| 2 | Check statistics cards text | Contrast ratio ≥ 4.5:1 | ⬜ |
| 3 | Check table text | Contrast ratio ≥ 4.5:1 | ⬜ |
| 4 | Check badge text | Contrast ratio ≥ 4.5:1 | ⬜ |
| 5 | Check button text | Contrast ratio ≥ 4.5:1 | ⬜ |
| 6 | Check form labels | Contrast ratio ≥ 4.5:1 | ⬜ |

**Expected Result:** WCAG AA compliance for text contrast

---

## Integration Testing

### Test Case I1: Integration with User Management

**Scenario:** Verify rank changes reflect in user management

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Perform manual rank advancement | Advancement successful | ⬜ |
| 2 | Navigate to user management page | Page loads | ⬜ |
| 3 | Find advanced user | User located | ⬜ |
| 4 | Verify rank badge shows new rank | Badge updated | ⬜ |
| 5 | Edit user details | Edit page loads | ⬜ |
| 6 | Verify rank information displayed | Rank shown correctly | ⬜ |

**Expected Result:** Rank data consistent across all user views

---

### Test Case I2: Integration with Order Management

**Scenario:** Verify advancement orders appear correctly

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | From advancements page, click "View Order" | Order page loads | ⬜ |
| 2 | Verify order details match advancement | Data consistent | ⬜ |
| 3 | Check order items include rank package | Package present | ⬜ |
| 4 | Navigate to order list page | List loads | ⬜ |
| 5 | Find advancement order | Order listed | ⬜ |
| 6 | Verify order marked appropriately | Status clear | ⬜ |

**Expected Result:** Orders linked correctly to advancements

---

### Test Case I3: Integration with Commission System

**Scenario:** Verify rank configuration affects commissions

**Note:** Requires Phase 2 commission system

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | Update rank order in configuration | Configuration saved | ⬜ |
| 2 | Create test order triggering commissions | Order processed | ⬜ |
| 3 | Check commission calculations | Commissions calculated | ⬜ |
| 4 | Verify rank order respected | Correct commission tiers | ⬜ |

**Expected Result:** Rank configuration properly integrated with commissions

---

### Test Case I4: Integration with Automatic Advancement

**Scenario:** Verify manual and automatic advancements coexist

**Note:** Requires Phase 3 automatic advancement

| Step | Action | Expected Result | Pass/Fail |
|------|--------|----------------|-----------|
| 1 | View advancements page | Page loads | ⬜ |
| 2 | Verify both automatic and manual advancements shown | Mixed types visible | ⬜ |
| 3 | Filter by type "Sponsorship Reward" | Auto advancements shown | ⬜ |
| 4 | Filter by type "Admin Adjustment" | Manual advancements shown | ⬜ |
| 5 | Verify system paid only for rewards | Admin type has 0 system_paid | ⬜ |

**Expected Result:** Both advancement types tracked and distinguished

---

## Regression Testing

### Test Case R1: Previous Phase Features

**Scenario:** Verify Phase 1-4 features still work

| Feature | Test Action | Status | Pass/Fail |
|---------|-------------|--------|-----------|
| Rank Tracking (Phase 1) | Check user rank in database | ⬜ | ⬜ |
| Commission Calculation (Phase 2) | Process order with commissions | ⬜ | ⬜ |
| Auto Advancement (Phase 3) | Sponsor enough users for advancement | ⬜ | ⬜ |
| User UI Display (Phase 4) | View rank on user dashboard | ⬜ | ⬜ |

**Expected Result:** No regression in previous features

---

### Test Case R2: Existing Admin Functions

**Scenario:** Verify other admin functions unaffected

| Function | Test Action | Status | Pass/Fail |
|----------|-------------|--------|-----------|
| User Management | List/edit users | ⬜ | ⬜ |
| Order Management | List/view orders | ⬜ | ⬜ |
| Product Management | List/edit products | ⬜ | ⬜ |
| Commission Reports | View commission reports | ⬜ | ⬜ |

**Expected Result:** All existing admin features functional

---

## Test Data Setup

### Quick Test Data Script

Run this script to generate comprehensive test data:

```bash
php setup_phase5_test_data.php
```

### Manual Test Data Creation

If script fails, create manually:

```sql
-- Create admin user
INSERT INTO users (username, email, password, role, email_verified_at, created_at, updated_at)
VALUES ('admin', 'admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW(), NOW(), NOW());

-- Create rank packages
INSERT INTO packages (name, price, is_rankable, rank_name, rank_order, required_direct_sponsors, pv, created_at, updated_at)
VALUES 
('Starter Pack', 1000, 1, 'Starter', 1, 5, 10, NOW(), NOW()),
('Newbie Pack', 2500, 1, 'Newbie', 2, 8, 25, NOW(), NOW()),
('Bronze Pack', 5000, 1, 'Bronze', 3, 10, 50, NOW(), NOW());

-- Create test users with ranks
INSERT INTO users (username, email, password, role, current_rank, rank_package_id, email_verified_at, created_at, updated_at)
SELECT 
    CONCAT('user_test_', n),
    CONCAT('user_test_', n, '@test.com'),
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'user',
    CASE 
        WHEN n % 3 = 0 THEN 'Starter'
        WHEN n % 3 = 1 THEN 'Newbie'
        ELSE 'Bronze'
    END,
    (SELECT id FROM packages WHERE rank_name = (CASE 
        WHEN n % 3 = 0 THEN 'Starter'
        WHEN n % 3 = 1 THEN 'Newbie'
        ELSE 'Bronze'
    END) LIMIT 1),
    NOW(),
    NOW(),
    NOW()
FROM (SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) numbers;
```

---

## Expected Results

### Dashboard Expected Results

**Statistics Cards:**
- Ranked Users: Count of users where current_rank IS NOT NULL
- Total Advancements: Count from rank_advancements table
- System Rewards: Count where type = 'reward'
- System Paid: SUM of system_paid where type = 'reward'

**Chart:**
- X-axis: Rank names in rank_order
- Y-axis: User count per rank
- Bars: Different colors, heights match counts

**Table:**
- All rankable packages listed
- Sorted by rank_order ASC
- User counts match actual database counts

---

### Configuration Expected Results

**Form Display:**
- All fields populated from database
- Next rank dropdowns show available options
- Price fields read-only

**Save Success:**
- Success message: "Rank configuration updated successfully!"
- Database values updated
- No transaction errors

**Save Failure:**
- Error messages displayed clearly
- No partial updates (transaction rollback)
- Form retains entered values

---

### Advancements Expected Results

**List Display:**
- Paginated at 15 records per page
- Most recent first (ORDER BY created_at DESC)
- All columns populated correctly

**Filters:**
- Type filter: Shows only matching type
- Rank filter: Shows only matching to_rank
- User search: Matches username or email (LIKE query)
- Combined filters: AND logic

**Empty States:**
- "No advancements found" when no results
- Filters show "Clear Filters" button when active

---

### Manual Advancement Expected Results

**Successful Advancement:**
- User rank updated in users table
- Order created with correct details
- OrderItem created linking to package
- RankAdvancement record created with type = 'admin'
- Success message shown
- network_active set to true (if first purchase)

**Failed Advancement:**
- Error message shown
- No database changes
- Original state preserved

---

## Bug Reporting

### Bug Report Template

When you find a bug, document it using this template:

```markdown
# Bug Report

**Bug ID:** RANK-P5-XXX
**Date Found:** YYYY-MM-DD
**Severity:** Critical / High / Medium / Low
**Priority:** P0 / P1 / P2 / P3

## Summary
Brief description of the bug

## Steps to Reproduce
1. Step 1
2. Step 2
3. Step 3

## Expected Result
What should happen

## Actual Result
What actually happens

## Environment
- Browser: [Chrome 120]
- OS: [Windows 11]
- PHP Version: [8.1.0]
- Laravel Version: [10.x]

## Screenshots
[Attach screenshots if applicable]

## Database State
[Relevant query results]

## Logs
[Relevant log entries]

## Workaround
[If any temporary workaround exists]

## Additional Notes
[Any other relevant information]
```

### Severity Levels

**Critical:** System crash, data loss, security vulnerability  
**High:** Major feature broken, no workaround  
**Medium:** Feature partially broken, workaround exists  
**Low:** Minor issue, cosmetic problem

---

## Test Sign-Off

### Test Execution Summary

**Tester Name:** _________________  
**Test Date:** _________________  
**Environment:** _________________

| Test Category | Total Cases | Passed | Failed | Skipped | Pass Rate |
|---------------|-------------|--------|--------|---------|-----------|
| Access Control | | | | | |
| Dashboard | | | | | |
| Configuration | | | | | |
| Advancements | | | | | |
| Manual Advance | | | | | |
| Security | | | | | |
| Performance | | | | | |
| UI/UX | | | | | |
| Integration | | | | | |
| Regression | | | | | |
| **TOTAL** | | | | | |

### Critical Issues Found

List any critical or high-severity issues:

1. _________________
2. _________________
3. _________________

### Recommendations

List any recommendations for improvements:

1. _________________
2. _________________
3. _________________

### Sign-Off

**Phase 5 Testing Status:** ✅ PASS / ❌ FAIL

**Tester Signature:** _________________  
**Date:** _________________

**Reviewer Signature:** _________________  
**Date:** _________________

---

## Appendix A: Troubleshooting Guide

### Chart Not Displaying

**Symptoms:** Blank space where chart should be

**Solutions:**
1. Check browser console for JavaScript errors
2. Verify Chart.js CDN is loading: `https://cdn.jsdelivr.net/npm/chart.js`
3. Check if data array is empty
4. Verify canvas element exists: `<canvas id="rankChart">`

### 404 Not Found Errors

**Symptoms:** Routes return 404

**Solutions:**
1. Clear route cache: `php artisan route:clear`
2. Verify routes defined in `routes/web.php`
3. Check route middleware configuration
4. Verify controller exists and is namespaced correctly

### 403 Forbidden Errors

**Symptoms:** Access denied to admin pages

**Solutions:**
1. Verify user has 'admin' role
2. Check middleware on routes
3. Clear config cache: `php artisan config:clear`
4. Verify middleware is registered in `app/Http/Kernel.php`

### Database Errors

**Symptoms:** SQL errors or migration issues

**Solutions:**
1. Run migrations: `php artisan migrate`
2. Check database connection: `php artisan db:show`
3. Verify table structures match models
4. Check foreign key constraints

### Form Submission Failures

**Symptoms:** Forms don't save or return errors

**Solutions:**
1. Check CSRF token present in form
2. Verify validation rules in controller
3. Check Laravel logs: `storage/logs/laravel.log`
4. Enable query logging to see SQL errors
5. Check transaction rollback messages

### Performance Issues

**Symptoms:** Slow page loads

**Solutions:**
1. Enable query logging to identify N+1 problems
2. Add eager loading: `with(['relationship'])`
3. Implement caching for statistics
4. Check database indexes
5. Optimize large queries with pagination

---

## Appendix B: SQL Verification Queries

### Verify Statistics

```sql
-- Ranked Users
SELECT COUNT(*) AS ranked_users 
FROM users 
WHERE current_rank IS NOT NULL;

-- Total Advancements
SELECT COUNT(*) AS total_advancements 
FROM rank_advancements;

-- System Rewards
SELECT COUNT(*) AS system_rewards 
FROM rank_advancements 
WHERE type = 'reward';

-- System Paid
SELECT SUM(system_paid) AS total_system_paid 
FROM rank_advancements 
WHERE type = 'reward';
```

### Verify Configuration

```sql
-- Rank Packages
SELECT 
    id,
    name,
    rank_name,
    rank_order,
    required_direct_sponsors,
    next_rank_package_id,
    price
FROM packages 
WHERE is_rankable = 1 
ORDER BY rank_order;
```

### Verify Advancements

```sql
-- Recent Advancements
SELECT 
    ra.id,
    u.username,
    u.email,
    ra.from_rank,
    ra.to_rank,
    ra.type,
    ra.qualified_sponsors_count,
    ra.system_paid,
    ra.created_at
FROM rank_advancements ra
JOIN users u ON ra.user_id = u.id
ORDER BY ra.created_at DESC
LIMIT 20;
```

### Verify Manual Advancement

```sql
-- Check Specific Advancement
SELECT 
    ra.*,
    o.order_number,
    o.payment_method,
    o.payment_status,
    oi.package_id,
    oi.price
FROM rank_advancements ra
JOIN orders o ON ra.order_id = o.id
JOIN order_items oi ON o.id = oi.order_id
WHERE ra.user_id = ? -- Replace with user ID
ORDER BY ra.created_at DESC;
```

---

## Appendix C: Performance Benchmarks

### Target Performance Metrics

| Metric | Target | Acceptable | Action Required |
|--------|--------|------------|-----------------|
| Dashboard Load | < 1s | < 2s | > 2s |
| Configuration Load | < 0.5s | < 1s | > 1s |
| Configuration Save | < 1s | < 2s | > 2s |
| Advancements Load | < 1s | < 2s | > 2s |
| Filter Application | < 0.5s | < 1s | > 1s |
| Manual Advance | < 2s | < 3s | > 3s |

### Query Count Targets

| Page | Target Queries | Max Acceptable |
|------|----------------|----------------|
| Dashboard | < 5 | < 10 |
| Configuration | < 3 | < 5 |
| Advancements | < 5 | < 10 |
| Manual Advance | < 7 | < 15 |

---

## Appendix D: Test Automation Ideas

### Future Automation Opportunities

**PHPUnit Tests:**
```php
// Example: Test dashboard statistics
public function test_dashboard_shows_correct_statistics()
{
    // Arrange
    $this->actingAs($this->adminUser);
    
    // Act
    $response = $this->get(route('admin.ranks.index'));
    
    // Assert
    $response->assertStatus(200);
    $response->assertViewHas('rankedUsersCount');
    $response->assertViewHas('totalAdvancements');
}
```

**Browser Tests (Laravel Dusk):**
```php
public function test_admin_can_update_configuration()
{
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->adminUser)
                ->visit('/admin/ranks/configure')
                ->type('packages[0][rank_name]', 'Updated Name')
                ->press('Save Configuration')
                ->assertSee('updated successfully');
    });
}
```

---

**Document End**

*This testing guide provides comprehensive coverage of all Phase 5 features. Execute tests systematically and document all results for complete validation of the admin configuration interface.*

*For questions or issues during testing, refer to RANK_PHASE5_COMPLETION_SUMMARY.md for implementation details.*

---

**Testing Guide Version:** 1.0  
**Last Updated:** December 2, 2025  
**Next Review:** After testing completion
