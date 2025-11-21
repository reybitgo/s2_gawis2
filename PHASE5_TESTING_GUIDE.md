# Phase 5: Member Dashboard & Notifications - QA Testing Guide

## Overview
This guide provides comprehensive testing procedures for the Phase 5 Member Dashboard & Notifications features of the Monthly Quota System.

**Phase 5 Features:**
- Member quota status dashboard (Current Month)
- Quota history view (Last 12 months)
- Real-time progress tracking
- Visual progress indicators
- Email notifications (quota met & reminders)

---

## Prerequisites

### Before Starting Tests

1. **Ensure Previous Phases are Working:**
   - [ ] Phase 1: Database schema is in place
   - [ ] Phase 2: PV tracking is working
   - [ ] Phase 3: Unilevel distribution checks quota
   - [ ] Phase 4: Admin can configure quotas

2. **Test User Setup:**
   ```sql
   -- Create test users with different scenarios
   -- User 1: Has met quota
   -- User 2: Hasn't met quota (50% progress)
   -- User 3: New user (0% progress)
   -- User 4: No quota requirement (quota not enforced)
   ```

3. **Test Data Requirements:**
   - Products with PV values assigned (e.g., 10 PV, 25 PV, 50 PV)
   - Packages with monthly quota set (e.g., 100 PV)
   - Some packages with `enforce_monthly_quota = true`
   - Some packages with `enforce_monthly_quota = false`
   - Orders with products purchased in current month
   - Historical data for past months (optional but recommended)

4. **Access Requirements:**
   - Member login credentials (non-admin users)
   - Access to email testing tool (Mailtrap, MailHog, or real email)
   - Browser with developer tools

---

## Test Environment Setup

### Setup Test Scenarios

**Run this setup script to create test data:**

```php
<?php
// setup_phase5_test_data.php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\MonthlyQuotaService;

echo "=== Setting Up Phase 5 Test Data ===\n\n";

// 1. Setup products with PV
$products = [
    ['name' => 'Test Product A', 'points_awarded' => 10.00, 'price' => 500],
    ['name' => 'Test Product B', 'points_awarded' => 25.00, 'price' => 1000],
    ['name' => 'Test Product C', 'points_awarded' => 50.00, 'price' => 2000],
];

foreach ($products as $productData) {
    $product = Product::where('name', $productData['name'])->first();
    if (!$product) {
        $product = Product::create([
            'name' => $productData['name'],
            'slug' => \Illuminate\Support\Str::slug($productData['name']),
            'price' => $productData['price'],
            'stock_quantity' => 100,
            'points_awarded' => $productData['points_awarded'],
        ]);
    } else {
        $product->points_awarded = $productData['points_awarded'];
        $product->save();
    }
    echo "✓ Product: {$product->name} - {$product->points_awarded} PV\n";
}

// 2. Setup test package with quota
$package = Package::where('name', 'Test Starter Package')->first();
if (!$package) {
    $package = Package::create([
        'name' => 'Test Starter Package',
        'slug' => 'test-starter-package',
        'price' => 5000,
        'is_mlm_package' => true,
        'max_mlm_levels' => 5,
        'monthly_quota_points' => 100.00,
        'enforce_monthly_quota' => true,
    ]);
} else {
    $package->monthly_quota_points = 100.00;
    $package->enforce_monthly_quota = true;
    $package->save();
}
echo "✓ Package: {$package->name} - Quota: {$package->monthly_quota_points} PV\n\n";

// 3. Create test users with different quota statuses
$testUsers = [
    ['username' => 'quota_met_user', 'email' => 'quota_met@test.com', 'pv' => 120],
    ['username' => 'quota_half_user', 'email' => 'quota_half@test.com', 'pv' => 50],
    ['username' => 'quota_zero_user', 'email' => 'quota_zero@test.com', 'pv' => 0],
];

$quotaService = new MonthlyQuotaService();

foreach ($testUsers as $userData) {
    $user = User::where('username', $userData['username'])->first();
    
    if (!$user) {
        $user = User::create([
            'username' => $userData['username'],
            'email' => $userData['email'],
            'password' => bcrypt('password123'),
            'first_name' => 'Test',
            'last_name' => 'User',
            'sponsor_id' => 1, // Assuming admin ID 1
            'network_status' => 'active',
        ]);
        $user->assignRole('member');
        
        // Create package purchase order
        $packageOrder = Order::create([
            'user_id' => $user->id,
            'order_number' => 'PKG-' . time() . '-' . $user->id,
            'payment_status' => 'paid',
            'payment_method' => 'gcash',
            'grand_total' => $package->price,
        ]);
        
        OrderItem::create([
            'order_id' => $packageOrder->id,
            'package_id' => $package->id,
            'quantity' => 1,
            'price' => $package->price,
            'subtotal' => $package->price,
        ]);
    }
    
    // Add PV for current month
    if ($userData['pv'] > 0) {
        $productA = Product::where('name', 'Test Product A')->first();
        $quantity = ceil($userData['pv'] / $productA->points_awarded);
        
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'TEST-' . time() . '-' . $user->id,
            'payment_status' => 'paid',
            'payment_method' => 'gcash',
            'grand_total' => $productA->price * $quantity,
        ]);
        
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $productA->id,
            'quantity' => $quantity,
            'price' => $productA->price,
            'subtotal' => $productA->price * $quantity,
        ]);
        
        // Process quota points
        $quotaService->processOrderPoints($order);
    }
    
    $status = $quotaService->getUserMonthlyStatus($user);
    echo "✓ User: {$user->username} - PV: {$status['total_pv']}/{$status['required_quota']} - " . 
         ($status['quota_met'] ? 'MET' : 'NOT MET') . "\n";
}

echo "\n=== Setup Complete ===\n";
echo "Test user credentials: username / password123\n";
echo "- quota_met_user (120 PV - Quota Met)\n";
echo "- quota_half_user (50 PV - Half Way)\n";
echo "- quota_zero_user (0 PV - Not Started)\n";
```

---

## Test Cases

### Test Category 1: Current Month Status Page

#### TC-1.1: Navigation to Current Month Status
**Route:** `/my-quota` or via sidebar "My Quota → Current Month"

**Steps:**
1. Login as member user
2. Navigate to sidebar → "My Quota" menu
3. Click "Current Month"

**Expected Results:**
- [ ] Page loads successfully (HTTP 200)
- [ ] Page title displays "My Monthly Quota Status"
- [ ] Current month and year displayed (e.g., "November 2025")
- [ ] No errors in browser console

**Test Data Required:**
- Member user with active status

---

#### TC-1.2: Progress Bar Display - Quota Met
**Route:** `/my-quota`

**Steps:**
1. Login as `quota_met_user` (120 PV / 100 PV)
2. Navigate to "My Quota → Current Month"

**Expected Results:**
- [ ] Progress bar shows 100% (or 120% if exceeds)
- [ ] Progress bar is green color (bg-success)
- [ ] Text displays: "120 / 100 PV"
- [ ] Percentage displays: "120%"
- [ ] Success alert: "✓ Congratulations! You have met your monthly quota..."
- [ ] Status badge shows "QUALIFIED" or similar

**Visual Example:**
```
Progress: 120 / 100 PV

[==========================] 120%

PV Earned: 120    Remaining: 0    Required: 100

✓ Quota Met
Congratulations! You have met your monthly quota and are eligible to earn Unilevel bonuses!
```

---

#### TC-1.3: Progress Bar Display - Quota Not Met
**Route:** `/my-quota`

**Steps:**
1. Login as `quota_half_user` (50 PV / 100 PV)
2. Navigate to "My Quota → Current Month"

**Expected Results:**
- [ ] Progress bar shows 50%
- [ ] Progress bar is yellow/warning color (bg-warning)
- [ ] Text displays: "50 / 100 PV"
- [ ] Percentage displays: "50%"
- [ ] Warning alert: "⚠ Quota Not Met - You need 50 more PV..."
- [ ] Remaining PV calculated correctly: 50 PV
- [ ] Status shows "NOT QUALIFIED"

**Visual Example:**
```
Progress: 50 / 100 PV

[=============             ] 50%

PV Earned: 50     Remaining: 50     Required: 100

⚠ Quota Not Met
You need 50 more PV to qualify for Unilevel bonuses this month.
```

---

#### TC-1.4: Progress Bar Display - Zero Progress
**Route:** `/my-quota`

**Steps:**
1. Login as `quota_zero_user` (0 PV / 100 PV)
2. Navigate to "My Quota → Current Month"

**Expected Results:**
- [ ] Progress bar shows 0%
- [ ] Progress bar is empty or minimal
- [ ] Text displays: "0 / 100 PV"
- [ ] Percentage displays: "0%"
- [ ] Warning alert displayed
- [ ] Remaining PV shows: 100 PV
- [ ] Call to action: "Start purchasing to build your quota"

---

#### TC-1.5: Recent PV-Earning Orders Display
**Route:** `/my-quota`

**Steps:**
1. Login as user with recent purchases
2. Navigate to "My Quota → Current Month"
3. Scroll to "Recent Orders" section

**Expected Results:**
- [ ] Section title: "Recent PV-Earning Orders This Month"
- [ ] Orders displayed in reverse chronological (newest first)
- [ ] Each order shows:
  - Order number
  - Date (formatted readable)
  - PV earned from that order
  - Product names
- [ ] Maximum 10 orders displayed
- [ ] Only orders from current month displayed
- [ ] Only orders with PV > 0 displayed
- [ ] If no orders: "No PV-earning purchases this month"

**Visual Example:**
```
Recent PV-Earning Orders This Month:
- Order #12345 (Nov 20, 2025): Test Product A × 2 = +20 PV
- Order #12346 (Nov 15, 2025): Test Product B × 1 = +25 PV
- Order #12347 (Nov 10, 2025): Test Product C × 1 = +50 PV
Total: 95 PV
```

---

#### TC-1.6: Package Information Display
**Route:** `/my-quota`

**Steps:**
1. Login as member
2. Navigate to "My Quota → Current Month"
3. Locate package information section

**Expected Results:**
- [ ] User's current package name displayed
- [ ] Package quota requirement displayed
- [ ] Quota enforcement status displayed (Enabled/Disabled)
- [ ] If quota disabled: Message explaining no requirement

**Visual Example:**
```
Your Package: Test Starter Package
Monthly Requirement: 100 PV
Quota Enforcement: Enabled
```

---

#### TC-1.7: Last Purchase Timestamp
**Route:** `/my-quota`

**Steps:**
1. Login as user with recent purchase
2. Navigate to "My Quota → Current Month"

**Expected Results:**
- [ ] Last purchase date/time displayed
- [ ] Format: "Last purchase: November 20, 2025 at 2:30 PM" (human readable)
- [ ] If no purchases: "No purchases this month"
- [ ] Timestamp is accurate

---

#### TC-1.8: Responsive Design Check
**Route:** `/my-quota`

**Steps:**
1. Login as member
2. Navigate to "My Quota → Current Month"
3. Test on different screen sizes:
   - Desktop (1920x1080)
   - Tablet (768x1024)
   - Mobile (375x667)

**Expected Results:**
- [ ] Layout adjusts properly on all screen sizes
- [ ] Progress bar remains readable
- [ ] Stats cards stack on mobile
- [ ] No horizontal scrolling
- [ ] Text remains readable
- [ ] Buttons/links are tappable on mobile

---

### Test Category 2: Quota History Page

#### TC-2.1: Navigation to Quota History
**Route:** `/my-quota/history` or via sidebar "My Quota → Quota History"

**Steps:**
1. Login as member user
2. Navigate to sidebar → "My Quota" menu
3. Click "Quota History"

**Expected Results:**
- [ ] Page loads successfully (HTTP 200)
- [ ] Page title displays "My Quota History"
- [ ] Table or list of past months displayed
- [ ] No errors in browser console

---

#### TC-2.2: Historical Data Display
**Route:** `/my-quota/history`

**Steps:**
1. Login as user with historical quota data
2. Navigate to "My Quota → Quota History"

**Expected Results:**
- [ ] Last 12 months displayed (or less if user is new)
- [ ] Most recent month shown first (descending order)
- [ ] Each month shows:
  - Month name and year (e.g., "November 2025")
  - Total PV earned that month
  - Required quota for that month
  - Quota met status (Yes/No or ✓/✗)
  - Progress percentage
- [ ] Current month included in list
- [ ] Visual indicators (colors, badges) for met/not met

**Visual Example:**
```
| Month         | PV Earned | Required | Status     | Progress |
|---------------|-----------|----------|------------|----------|
| November 2025 | 120       | 100      | ✓ Met      | 120%     |
| October 2025  | 80        | 100      | ✗ Not Met  | 80%      |
| September 2025| 150       | 100      | ✓ Met      | 150%     |
```

---

#### TC-2.3: Empty History Display
**Route:** `/my-quota/history`

**Steps:**
1. Login as brand new user (just registered)
2. Navigate to "My Quota → Quota History"

**Expected Results:**
- [ ] Page loads without errors
- [ ] Message displayed: "No quota history available" or similar
- [ ] Suggestion to make purchases
- [ ] No error messages

---

#### TC-2.4: Visual Status Indicators
**Route:** `/my-quota/history`

**Steps:**
1. Login as user with mixed history (some met, some not)
2. Navigate to "My Quota → Quota History"
3. Review visual indicators

**Expected Results:**
- [ ] Met months: Green badge/checkmark (✓)
- [ ] Not met months: Red badge/cross (✗) or warning icon
- [ ] Progress bars or percentage colors match status
- [ ] Consistent styling throughout table
- [ ] Easy to distinguish at a glance

---

#### TC-2.5: Data Accuracy Verification
**Route:** `/my-quota/history`

**Steps:**
1. Login as test user with known data
2. Navigate to "My Quota → Quota History"
3. Verify each month's data against database

**Verification Query:**
```sql
SELECT 
    year, 
    month, 
    total_pv_points, 
    required_quota, 
    quota_met 
FROM monthly_quota_tracker 
WHERE user_id = [TEST_USER_ID] 
ORDER BY year DESC, month DESC;
```

**Expected Results:**
- [ ] PV values match database exactly
- [ ] Required quota matches database
- [ ] Quota met status matches database calculation
- [ ] No missing months (if data exists)
- [ ] No duplicate months

---

### Test Category 3: Real-Time Updates

#### TC-3.1: Purchase Updates Quota Status
**Route:** Test flow from shop → checkout → quota page

**Steps:**
1. Login as `quota_half_user` (50 PV / 100 PV)
2. Note current quota status: 50 PV
3. Add Product C (50 PV) to cart
4. Complete checkout and payment
5. Return to "My Quota → Current Month"

**Expected Results:**
- [ ] Progress bar updates to 100 PV (100%)
- [ ] Status changes to "Quota Met"
- [ ] Alert changes from warning to success
- [ ] Remaining PV shows 0
- [ ] New order appears in "Recent Orders"
- [ ] Last purchase timestamp updated
- [ ] No page refresh needed (or automatic refresh)

---

#### TC-3.2: Multiple Purchases in Same Session
**Route:** Multiple checkout flows

**Steps:**
1. Login as user with 0 PV
2. Purchase Product A (10 PV) → Check quota page → Shows 10 PV
3. Purchase Product B (25 PV) → Check quota page → Shows 35 PV
4. Purchase Product C (50 PV) → Check quota page → Shows 85 PV

**Expected Results:**
- [ ] Each purchase adds to total PV correctly
- [ ] Progress bar updates after each purchase
- [ ] Percentage recalculates accurately
- [ ] All orders appear in "Recent Orders"
- [ ] No duplicate PV counting
- [ ] Cumulative total is accurate

---

### Test Category 4: Email Notifications

#### TC-4.1: Quota Met Notification - Immediate Send
**Trigger:** User reaches quota for first time

**Setup:**
1. Ensure user has 90 PV / 100 PV
2. Configure mail settings (Mailtrap/MailHog)

**Steps:**
1. Login as test user
2. Purchase product that brings PV to 100+
3. Complete checkout
4. Check email inbox (Mailtrap/MailHog)

**Expected Results:**
- [ ] Email sent immediately after checkout
- [ ] Email received within 1 minute
- [ ] Subject: Contains "Quota Met" or "Congratulations"
- [ ] Body contains:
  - User's name/username
  - Month and year
  - Total PV achieved
  - Required quota amount
  - Congratulations message
  - Link to quota status page
- [ ] Email formatting is correct (HTML renders properly)
- [ ] No duplicate emails sent

**Email Content Example:**
```
Subject: Congratulations! You've Met Your November 2025 Quota

Hi Test User,

Great news! You've successfully met your monthly quota for November 2025!

Your Quota Status:
- PV Earned: 100
- Required Quota: 100
- Status: ✓ QUALIFIED

You are now eligible to earn Unilevel bonuses from your downline purchases!

View Your Status: [Link to /my-quota]

Keep up the great work!
```

---

#### TC-4.2: Quota Met Notification - Only Sent Once
**Trigger:** Verify no duplicate notifications

**Steps:**
1. User already met quota (has 120 PV / 100 PV)
2. User makes another purchase (adds more PV)
3. Check email inbox

**Expected Results:**
- [ ] No new "Quota Met" email sent
- [ ] System recognizes quota already met
- [ ] No spam notifications
- [ ] Log confirms notification was skipped

---

#### TC-4.3: Quota Reminder Notification - Scheduled
**Trigger:** CRON job on 25th of month

**Note:** This requires waiting or manually triggering the command

**Steps:**
1. Set system date to 25th of month (or run command manually)
2. Ensure test user has NOT met quota (e.g., 50 PV / 100 PV)
3. Run command: `php artisan quota:send-reminders --force`
4. Check email inbox

**Expected Results:**
- [ ] Email sent to users who haven't met quota
- [ ] Email NOT sent to users who met quota
- [ ] Subject: Contains "Reminder" or "Don't Forget"
- [ ] Body contains:
  - User's name
  - Current PV progress
  - Remaining PV needed
  - Days remaining in month
  - Call to action (shop link)
  - Link to quota status page
- [ ] Email formatting correct

**Email Content Example:**
```
Subject: Reminder: Complete Your November Quota

Hi Test User,

This is a friendly reminder about your monthly quota status for November 2025.

Current Progress:
- PV Earned: 50
- Required Quota: 100
- Remaining: 50 PV
- Days Left: 6 days

Don't miss out on earning Unilevel bonuses! Complete your quota by purchasing products.

Shop Now: [Link to /products]
View Status: [Link to /my-quota]
```

---

#### TC-4.4: Reminder Not Sent to Qualified Users
**Trigger:** CRON job on 25th of month

**Steps:**
1. Ensure user has MET quota (120 PV / 100 PV)
2. Run command: `php artisan quota:send-reminders --force`
3. Check email inbox

**Expected Results:**
- [ ] No reminder email sent to this user
- [ ] Log confirms user was skipped (quota already met)
- [ ] Only unqualified users receive reminders

---

#### TC-4.5: Email Link Click-Through
**Trigger:** Click links in notification emails

**Steps:**
1. Open "Quota Met" or "Reminder" email
2. Click link to quota status page
3. Verify redirect

**Expected Results:**
- [ ] Link works (not broken)
- [ ] Redirects to correct page (/my-quota)
- [ ] User is already authenticated (or redirected to login)
- [ ] Page loads correctly after click
- [ ] Analytics/tracking works (if implemented)

---

### Test Category 5: Edge Cases & Error Handling

#### TC-5.1: User with No Package
**Scenario:** User registered but hasn't purchased starter package

**Steps:**
1. Login as user without MLM package
2. Navigate to "My Quota → Current Month"

**Expected Results:**
- [ ] Page loads without errors
- [ ] Message displayed: "You don't have an active package" or similar
- [ ] No quota requirement shown (or shows 0)
- [ ] Suggestion to purchase package
- [ ] No broken UI elements

---

#### TC-5.2: Package with Quota Disabled
**Scenario:** User's package has `enforce_monthly_quota = false`

**Steps:**
1. Login as user with non-enforced quota package
2. Navigate to "My Quota → Current Month"

**Expected Results:**
- [ ] Page displays quota information
- [ ] Message: "Quota enforcement is disabled for your package"
- [ ] Shows: "You only need to be active to earn bonuses"
- [ ] Still tracks PV (optional - for reference)
- [ ] Status shows "QUALIFIED" (based on active status only)

---

#### TC-5.3: Quota Exceeds 100%
**Scenario:** User earns 200 PV when quota is 100 PV

**Steps:**
1. Create user with 200 PV / 100 PV
2. Navigate to "My Quota → Current Month"

**Expected Results:**
- [ ] Progress bar caps at 100% visually (no overflow)
- [ ] Percentage displays actual: "200%"
- [ ] Text shows: "200 / 100 PV"
- [ ] Remaining PV shows: "0 PV" (not negative)
- [ ] Status: "Quota Met"
- [ ] No UI breaking

---

#### TC-5.4: Month Transition Test
**Scenario:** Test behavior when month changes

**Note:** This is difficult to test naturally - may require date mocking

**Steps:**
1. User has quota data for October
2. System date changes to November 1st
3. CRON job runs to reset quotas
4. User navigates to "My Quota → Current Month"

**Expected Results:**
- [ ] Current month shows November (new month)
- [ ] PV resets to 0
- [ ] Required quota matches package setting
- [ ] Status: "Not Met" (starting fresh)
- [ ] October data moved to history
- [ ] October data still visible in "Quota History"

---

#### TC-5.5: Decimal PV Handling
**Scenario:** Products with fractional PV (e.g., 5.25, 10.50)

**Steps:**
1. Set product PV to 5.25
2. User purchases 3 units (should be 15.75 PV)
3. Navigate to "My Quota → Current Month"

**Expected Results:**
- [ ] PV displays with 2 decimals: "15.75"
- [ ] Calculations are accurate (no rounding errors)
- [ ] Progress percentage calculated correctly
- [ ] Database stores decimal values correctly
- [ ] No integer truncation

---

#### TC-5.6: Large PV Numbers
**Scenario:** User with extremely high PV (e.g., 9999.99)

**Steps:**
1. Create scenario with user having 9999.99 PV
2. Navigate to quota pages

**Expected Results:**
- [ ] Numbers display correctly (no overflow)
- [ ] Formatting includes commas: "9,999.99"
- [ ] Progress bar doesn't break UI
- [ ] Percentage calculation doesn't error
- [ ] Database handles max value (decimal 10,2)

---

#### TC-5.7: Concurrent User Sessions
**Scenario:** Same user logged in on multiple devices

**Steps:**
1. Login as same user on Browser A and Browser B
2. On Browser A: Make purchase
3. On Browser B: Refresh quota page

**Expected Results:**
- [ ] Both browsers show same data
- [ ] No session conflicts
- [ ] Purchase reflects on both browsers
- [ ] No duplicate PV counting
- [ ] Consistent data across sessions

---

#### TC-5.8: Unauthorized Access
**Scenario:** Access quota pages without login

**Steps:**
1. Logout completely
2. Manually navigate to `/my-quota`
3. Try to access `/my-quota/history`

**Expected Results:**
- [ ] Redirects to login page
- [ ] Does not display quota data
- [ ] After login, redirects back to intended page
- [ ] No unauthorized data exposure

---

#### TC-5.9: Admin Viewing Member Quota
**Scenario:** Admin views individual member's quota

**Steps:**
1. Login as admin
2. Navigate to "Admin → Monthly Quota → Reports"
3. Click on a specific user's quota report

**Expected Results:**
- [ ] Admin can view user's quota status
- [ ] Data is accurate
- [ ] Admin cannot edit directly on member view
- [ ] Links work correctly
- [ ] User privacy maintained (only admin can access)

---

### Test Category 6: Performance & Load Testing

#### TC-6.1: Page Load Performance
**Route:** All quota pages

**Steps:**
1. Open browser DevTools → Network tab
2. Login and navigate to quota pages
3. Measure load times

**Expected Results:**
- [ ] Current Month page loads in < 2 seconds
- [ ] History page loads in < 3 seconds
- [ ] No unnecessary database queries (check logs)
- [ ] Images/assets load properly
- [ ] No slow queries (> 1 second)

---

#### TC-6.2: Database Query Optimization
**Technical Test:** Check query efficiency

**Steps:**
1. Enable query logging: `DB::enableQueryLog()`
2. Load quota pages
3. Review queries: `DB::getQueryLog()`

**Expected Results:**
- [ ] No N+1 query problems
- [ ] Eager loading used for relationships
- [ ] Indexed columns used in WHERE clauses
- [ ] Total queries < 10 per page
- [ ] No duplicate queries

---

#### TC-6.3: Multiple Users Browsing Simultaneously
**Load Test:** Multiple users accessing quota pages

**Steps:**
1. Create 10-20 test users
2. Simulate concurrent access (use tool or manual)
3. Have all users navigate to quota pages simultaneously

**Expected Results:**
- [ ] Pages load for all users
- [ ] No timeout errors
- [ ] No mixed data (User A sees User B's data)
- [ ] Server remains responsive
- [ ] Database handles concurrent reads

---

### Test Category 7: UI/UX Testing

#### TC-7.1: Visual Consistency
**Route:** All quota pages

**Steps:**
1. Navigate through all quota pages
2. Compare with other pages in system

**Expected Results:**
- [ ] Color scheme matches application theme
- [ ] Font styles consistent
- [ ] Button styles match other pages
- [ ] Icons used appropriately
- [ ] Spacing/padding consistent
- [ ] Follows design system

---

#### TC-7.2: Accessibility Testing
**Route:** All quota pages

**Steps:**
1. Use browser accessibility checker
2. Test with screen reader (NVDA, JAWS)
3. Navigate using keyboard only (Tab key)

**Expected Results:**
- [ ] All interactive elements focusable
- [ ] Tab order is logical
- [ ] Alt text on images
- [ ] ARIA labels where needed
- [ ] Color contrast meets WCAG standards
- [ ] Screen reader announces content correctly

---

#### TC-7.3: User-Friendly Messages
**Route:** All quota pages

**Steps:**
1. Review all text and messages
2. Check for clarity and helpfulness

**Expected Results:**
- [ ] Language is clear and non-technical
- [ ] Error messages are helpful (not just "Error")
- [ ] Success messages are encouraging
- [ ] Instructions are easy to follow
- [ ] No jargon or confusing terms
- [ ] Tone is friendly and supportive

---

## Regression Testing Checklist

After implementing Phase 5, verify these existing features still work:

### Existing Features to Verify:
- [ ] User login/logout
- [ ] Product browsing and purchase
- [ ] Shopping cart functionality
- [ ] Checkout process
- [ ] Order history
- [ ] Wallet operations
- [ ] Genealogy views
- [ ] Admin dashboard
- [ ] Package management (Admin)
- [ ] Product management (Admin)
- [ ] Phase 4 admin quota pages

---

## Bug Reporting Template

When reporting issues, include:

```
**Bug ID:** PHASE5-XXX
**Test Case:** TC-X.X
**Severity:** Critical / High / Medium / Low
**Priority:** High / Medium / Low

**Steps to Reproduce:**
1. 
2. 
3. 

**Expected Result:**

**Actual Result:**

**Screenshots:**
[Attach screenshots]

**Environment:**
- Browser: [Chrome 120, Firefox 119, etc.]
- OS: [Windows 11, macOS, etc.]
- User Role: [Member, Admin]
- Test User: [username]

**Additional Notes:**
```

---

## Test Sign-Off Checklist

Before approving Phase 5 for production:

### Functional Testing:
- [ ] All test cases (TC-1.1 to TC-7.3) passed
- [ ] No critical or high-priority bugs remaining
- [ ] Edge cases handled gracefully
- [ ] Error handling works properly

### Data Accuracy:
- [ ] PV calculations are correct
- [ ] Progress percentages accurate
- [ ] Historical data displays correctly
- [ ] Real-time updates work

### User Experience:
- [ ] Pages load quickly (< 3 seconds)
- [ ] UI is responsive on all devices
- [ ] Navigation is intuitive
- [ ] Messages are clear and helpful

### Notifications:
- [ ] Quota met emails send correctly
- [ ] Reminder emails send correctly
- [ ] Email content is accurate
- [ ] Links in emails work

### Security:
- [ ] Unauthorized access blocked
- [ ] User data privacy maintained
- [ ] No data leakage between users
- [ ] SQL injection prevented

### Integration:
- [ ] Works with Phase 1-4 features
- [ ] Doesn't break existing functionality
- [ ] Admin and member views work together
- [ ] Database integrity maintained

---

## Known Limitations & Future Enhancements

**Current Limitations:**
- Notification history not stored (only sent via email)
- Cannot edit historical quota data
- Limited export functionality
- Mobile app not available

**Planned Enhancements (Phase 7+):**
- Advanced analytics and charts
- Push notifications (browser/mobile)
- Quota comparison with peers
- Achievement badges
- Export to PDF/Excel
- Notification preferences

---

## Testing Timeline Recommendation

**Week 1: Basic Functionality**
- Test Categories 1-2 (Current Month, History)
- Setup test data
- Verify navigation and display

**Week 2: Advanced Features**
- Test Categories 3-4 (Real-time updates, Notifications)
- Email testing
- Integration testing

**Week 3: Quality & Edge Cases**
- Test Categories 5-7 (Edge cases, Performance, UI/UX)
- Regression testing
- Bug fixes and retesting

**Week 4: Final Validation**
- End-to-end testing
- User acceptance testing
- Production readiness review
- Sign-off

---

## Support & Resources

**Documentation:**
- UNILEVEL_QUOTA.md - Full implementation plan
- UNILEVEL_QUOTA_SUMMARY.md - Executive summary
- PHASE4_TESTING_GUIDE.md - Phase 4 testing guide

**Test Queries:**
```sql
-- Check user's current month quota
SELECT * FROM monthly_quota_tracker 
WHERE user_id = ? AND year = ? AND month = ?;

-- Check product PV values
SELECT name, points_awarded FROM products 
WHERE points_awarded > 0;

-- Check package quotas
SELECT name, monthly_quota_points, enforce_monthly_quota 
FROM packages;

-- Check recent orders with PV
SELECT o.order_number, o.created_at, oi.quantity, p.points_awarded,
       (oi.quantity * p.points_awarded) as total_pv
FROM orders o
JOIN order_items oi ON o.id = oi.order_id
JOIN products p ON oi.product_id = p.id
WHERE o.user_id = ? AND o.payment_status = 'paid'
ORDER BY o.created_at DESC;
```

**Useful Commands:**
```bash
# Clear cache
php artisan cache:clear

# Run quota service manually
php artisan tinker
>>> $user = User::find(1);
>>> $service = new App\Services\MonthlyQuotaService();
>>> $service->getUserMonthlyStatus($user);

# Send test email
php artisan quota:send-reminders --force

# Check logs
tail -50 storage/logs/laravel.log
```

---

## Contact

For questions or issues during testing, contact:
- **Development Team:** [Contact Info]
- **Project Manager:** [Contact Info]
- **QA Lead:** [Contact Info]

---

**Document Version:** 1.0  
**Last Updated:** 2025-11-20  
**Phase:** Phase 5 - Member Dashboard & Notifications
