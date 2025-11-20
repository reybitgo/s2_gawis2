# Phase 4: Admin Configuration Interface - QA Testing Guide

**Version:** 1.0  
**Date:** November 18, 2025  
**Phase:** 4 - Admin Configuration Interface  
**Test Environment:** Development/Staging

---

## Table of Contents

1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Test Environment Setup](#test-environment-setup)
4. [Test Scenarios](#test-scenarios)
    - [Dashboard Tests](#1-dashboard-tests)
    - [Package Quota Management Tests](#2-package-quota-management-tests)
    - [Reports Tests](#3-reports-tests)
    - [User Report Tests](#4-user-report-tests)
5. [Integration Tests](#integration-tests)
6. [Performance Tests](#performance-tests)
7. [Security Tests](#security-tests)
8. [Edge Cases & Error Handling](#edge-cases--error-handling)
9. [Browser Compatibility](#browser-compatibility)
10. [Regression Tests](#regression-tests)
11. [Test Results Template](#test-results-template)

---

## Overview

This document provides comprehensive testing procedures for Phase 4 of the Monthly Quota System. Phase 4 implements the admin interface for managing and monitoring the monthly quota system.

### Scope

Phase 4 includes:

-   Admin dashboard with statistics
-   Package quota configuration interface
-   Monthly compliance reports
-   Individual user quota reports

### Test Objectives

-   Verify all admin pages load correctly
-   Validate data accuracy and calculations
-   Test form submissions and validations
-   Ensure proper access controls
-   Verify database updates
-   Test error handling
-   Validate UI/UX consistency

---

## Prerequisites

### 1. System Requirements

-   ✅ Phases 1-3 must be completed and tested
-   ✅ Database migrations run successfully
-   ✅ At least one admin user account
-   ✅ Test data populated (users, packages, products, orders)
-   ✅ MonthlyQuotaService functional
-   ✅ UnilevelBonusService integrated with quota checks

### 2. Required Test Data

Before testing, ensure you have:

```sql
-- Verify you have:
-- At least 5 active users with sponsor hierarchy
SELECT COUNT(*) FROM users WHERE network_status = 'active';

-- At least 3 packages
SELECT COUNT(*) FROM packages;

-- At least 5 products with PV > 0
SELECT COUNT(*) FROM products WHERE points_awarded > 0;

-- Some monthly quota tracker records
SELECT COUNT(*) FROM monthly_quota_tracker;

-- Some test orders
SELECT COUNT(*) FROM orders WHERE payment_status = 'paid';
```

### 3. Admin Access

-   Admin username: `admin`
-   Admin email: `support@gawisherbal.com`
-   Ensure you can login to `/admin/dashboard`

### 4. Test Data Setup Script

Run this before testing (optional - creates fresh test data):

```bash
# Create test orders with PV for specific users
php artisan tinker

# Example setup
$user = User::find(9); // gawis6
$product = Product::where('points_awarded', '>', 0)->first();
$order = Order::create([
    'user_id' => $user->id,
    'order_number' => 'TEST-QA-' . time(),
    'payment_status' => 'paid',
    'payment_method' => 'wallet',
    'subtotal' => $product->price,
    'tax_amount' => 0,
    'total_amount' => $product->price,
    'delivery_method' => 'office_pickup',
]);

// Add order item
OrderItem::create([
    'order_id' => $order->id,
    'product_id' => $product->id,
    'item_type' => 'product',
    'quantity' => 5,
    'unit_price' => $product->price,
    'total_price' => $product->price * 5,
    'points_awarded_per_item' => $product->points_awarded,
    'total_points_awarded' => $product->points_awarded * 5,
]);

// Process quota points
$quotaService = app(App\Services\MonthlyQuotaService::class);
$quotaService->processOrderPoints($order);
```

---

## Test Environment Setup

### Step 1: Verify Routes

```bash
php artisan route:list --path=monthly-quota
```

**Expected Output:**

```
GET|HEAD  admin/monthly-quota ..................... admin.monthly-quota.index
GET|HEAD  admin/monthly-quota/packages ............ admin.monthly-quota.packages
POST      admin/monthly-quota/packages/{package}/update-quota ... admin.monthly-quota.packages.update-quota
GET|HEAD  admin/monthly-quota/reports ............. admin.monthly-quota.reports
GET|HEAD  admin/monthly-quota/reports/user/{user} . admin.monthly-quota.reports.user
```

### Step 2: Verify Controller Exists

```bash
php -l app/Http/Controllers/Admin/MonthlyQuotaController.php
```

**Expected:** `No syntax errors detected`

### Step 3: Verify Views Exist

```bash
ls resources/views/admin/monthly-quota/
```

**Expected Files:**

-   `index.blade.php`
-   `packages.blade.php`
-   `reports.blade.php`
-   `user-report.blade.php`

### Step 4: Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

---

## Test Scenarios

## 1. Dashboard Tests

### Test 1.1: Dashboard Access

**Test Case ID:** PHASE4-DASH-001  
**Priority:** High  
**Type:** Functional

**Steps:**

1. Login as admin
2. Navigate to `/admin/monthly-quota`

**Expected Results:**

-   ✅ Page loads successfully (HTTP 200)
-   ✅ Page title shows "Monthly Quota System"
-   ✅ No PHP errors or exceptions
-   ✅ Layout uses `@extends('layouts.admin')`
-   ✅ Navigation breadcrumb visible

**Pass/Fail:** [ ]

---

### Test 1.2: Statistics Cards Display

**Test Case ID:** PHASE4-DASH-002  
**Priority:** High  
**Type:** Functional

**Steps:**

1. Navigate to `/admin/monthly-quota`
2. Observe the 4 statistics cards

**Expected Results:**

Card 1 - Total Active Users:

-   ✅ Shows count of users with `network_status = 'active'`
-   ✅ Number format correct (e.g., 1,234 not 1234)
-   ✅ Card has blue background (bg-primary)

Card 2 - Quota Met This Month:

-   ✅ Shows count of current month trackers with `quota_met = true`
-   ✅ Card has green background (bg-success)

Card 3 - Quota Not Met:

-   ✅ Shows count of current month trackers with `quota_met = false`
-   ✅ Card has yellow background (bg-warning)

Card 4 - Compliance Rate:

-   ✅ Shows percentage: (quota_met / (quota_met + quota_not_met)) \* 100
-   ✅ Displays as percentage with % symbol
-   ✅ Card has cyan background (bg-info)

**Pass/Fail:** [ Pass ]

---

### Test 1.3: Configuration Status Panel

**Test Case ID:** PHASE4-DASH-003  
**Priority:** Medium  
**Type:** Functional

**Steps:**

1. Navigate to `/admin/monthly-quota`
2. Find "Configuration Status" card

**Expected Results:**

-   ✅ Shows "Products with PV" badge with count
-   ✅ Count matches `SELECT COUNT(*) FROM products WHERE points_awarded > 0`
-   ✅ Shows "Packages with Quota" badge with count
-   ✅ Count matches `SELECT COUNT(*) FROM packages WHERE enforce_monthly_quota = true`
-   ✅ Both badges have correct colors (primary, info)

**Pass/Fail:** [ Pass ]

---

### Test 1.4: Top Performers Table

**Test Case ID:** PHASE4-DASH-004  
**Priority:** Medium  
**Type:** Functional

**Steps:**

1. Navigate to `/admin/monthly-quota`
2. Locate "Top Performers (Month Year)" card
3. Review table contents

**Expected Results:**

-   ✅ Table shows max 10 users
-   ✅ Users ordered by `total_pv_points DESC`
-   ✅ Username column shows correct usernames
-   ✅ Username is clickable link to user report
-   ✅ Total PV shows 2 decimal places
-   ✅ Status shows "Met" (green badge) or "Not Met" (yellow badge)
-   ✅ If no data, shows "No data available" message

**Test Data Verification:**

```sql
SELECT u.username, mqt.total_pv_points, mqt.quota_met
FROM monthly_quota_tracker mqt
JOIN users u ON u.id = mqt.user_id
WHERE mqt.year = YEAR(CURDATE())
  AND mqt.month = MONTH(CURDATE())
ORDER BY mqt.total_pv_points DESC
LIMIT 10;
```

**Pass/Fail:** [ Pass ]

---

### Test 1.5: Action Buttons

**Test Case ID:** PHASE4-DASH-005  
**Priority:** High  
**Type:** Navigation

**Steps:**

1. Navigate to `/admin/monthly-quota`
2. Click each action button in header

**Expected Results:**

Button 1 - "Manage Product PV":

-   ✅ Redirects to `/admin/products`
-   ✅ Shows product list page
-   ✅ Products page loads successfully

Button 2 - "Manage Package Quotas":

-   ✅ Redirects to `/admin/monthly-quota/packages`
-   ✅ Shows package quota page
-   ✅ Page loads successfully

Button 3 - "View Reports":

-   ✅ Redirects to `/admin/monthly-quota/reports`
-   ✅ Shows reports page
-   ✅ Page loads successfully

**Pass/Fail:** [ Pass ]

---

### Test 1.6: How It Works Section

**Test Case ID:** PHASE4-DASH-006  
**Priority:** Low  
**Type:** UI/Content

**Steps:**

1. Scroll to bottom of dashboard
2. Read "How Monthly Quota System Works" section

**Expected Results:**

-   ✅ Section exists with card layout
-   ✅ Contains ordered list with 5 steps
-   ✅ Links to Products Management and Package Quotas work
-   ✅ Alert box with note about Product PV management visible
-   ✅ All text is readable and professional

**Pass/Fail:** [ Pass ]

---

### Test 1.7: Dashboard Data Accuracy

**Test Case ID:** PHASE4-DASH-007  
**Priority:** Critical  
**Type:** Data Validation

**Steps:**

1. Open database and count records manually
2. Compare with dashboard statistics

**Manual Query:**

```sql
-- Total Active Users
SELECT COUNT(*) as total_active
FROM users
WHERE network_status = 'active';

-- Quota Met This Month
SELECT COUNT(*) as quota_met
FROM monthly_quota_tracker
WHERE YEAR(created_at) = YEAR(CURDATE())
  AND MONTH(created_at) = MONTH(CURDATE())
  AND quota_met = 1;

-- Quota Not Met This Month
SELECT COUNT(*) as quota_not_met
FROM monthly_quota_tracker
WHERE YEAR(created_at) = YEAR(CURDATE())
  AND MONTH(created_at) = MONTH(CURDATE())
  AND quota_met = 0;

-- Products with PV
SELECT COUNT(*) as products_with_pv
FROM products
WHERE points_awarded > 0;

-- Packages with Quota Enforced
SELECT COUNT(*) as packages_with_quota
FROM packages
WHERE enforce_monthly_quota = 1;
```

**Expected Results:**

-   ✅ All counts match database exactly
-   ✅ Compliance rate calculation correct
-   ✅ No discrepancies

**Pass/Fail:** [ Pass ]

---

## 2. Package Quota Management Tests

### Test 2.1: Package List Display

**Test Case ID:** PHASE4-PKG-001  
**Priority:** High  
**Type:** Functional

**Steps:**

1. Navigate to `/admin/monthly-quota/packages`
2. Review table contents

**Expected Results:**

-   ✅ All packages displayed in table
-   ✅ Columns: ID, Package Name, Price, MLM Package, Current Quota, New Quota, Enforce, Actions
-   ✅ Package names correct
-   ✅ Prices formatted with ₱ symbol and 2 decimals
-   ✅ MLM Package shows "Yes" (green) or "No" (grey) badge
-   ✅ Current Quota shows PV value with "(Enforced)" or "(Disabled)"
-   ✅ Table is responsive

**Pass/Fail:** [ ]

---

### Test 2.2: Update Package Quota - Valid Data

**Test Case ID:** PHASE4-PKG-002  
**Priority:** Critical  
**Type:** Functional

**Steps:**

1. Navigate to `/admin/monthly-quota/packages`
2. Find any package (e.g., "Starter")
3. Change "New Quota" to `150.50`
4. Change "Enforce" to `Yes`
5. Click "Update" button

**Expected Results:**

-   ✅ Page reloads with success message
-   ✅ Success message shows: "Package quota updated successfully! {Package Name} now requires 150.50 PV monthly (Enforce: YES)."
-   ✅ Database updated: `SELECT monthly_quota_points, enforce_monthly_quota FROM packages WHERE name = 'Starter'`
-   ✅ Values: `monthly_quota_points = 150.50`, `enforce_monthly_quota = 1`
-   ✅ "Current Quota" column reflects new values
-   ✅ Activity log created

**Activity Log Verification:**

```sql
SELECT * FROM activity_log
WHERE description LIKE '%Updated package monthly quota%'
ORDER BY created_at DESC
LIMIT 1;
```

Expected properties:

-   `old_quota`: previous value
-   `new_quota`: 150.50
-   `old_enforce`: previous boolean
-   `new_enforce`: true

**Pass/Fail:** [ Pass ]

---

### Test 2.3: Update Package Quota - Decimal Values

**Test Case ID:** PHASE4-PKG-003  
**Priority:** High  
**Type:** Data Validation

**Steps:**

1. Test various decimal values:
    - `0.01` (minimum)
    - `0.50`
    - `1.25`
    - `100.99`
    - `9999.99` (maximum)

**Expected Results:**

-   ✅ All decimal values accepted
-   ✅ Values saved with 2 decimal precision
-   ✅ Display shows 2 decimal places (e.g., 1.25 not 1.3)
-   ✅ No rounding errors

**Pass/Fail:** [ Pass ]

---

### Test 2.4: Update Package Quota - Invalid Data

**Test Case ID:** PHASE4-PKG-004  
**Priority:** High  
**Type:** Negative Testing

**Steps:**
Test these invalid inputs:

1. **Negative value:** `-10`
2. **Too large:** `10000`
3. **Non-numeric:** `abc`
4. **Empty field:** (leave blank)

**Expected Results for Each:**

-   ✅ Form validation triggers
-   ✅ Error message displayed
-   ✅ Database NOT updated
-   ✅ User stays on form page
-   ✅ Previous values retained

**Validation Rules:**

-   `monthly_quota_points`: required, numeric, min:0, max:9999.99
-   `enforce_monthly_quota`: required, boolean

**Pass/Fail:** [ Pass ]

---

### Test 2.5: Enforce Toggle

**Test Case ID:** PHASE4-PKG-005  
**Priority:** High  
**Type:** Functional

**Steps:**

1. Select a package with `enforce_monthly_quota = false`
2. Change "Enforce" dropdown to "Yes"
3. Click "Update"
4. Verify in database
5. Change back to "No"
6. Click "Update"
7. Verify in database

**Expected Results:**

-   ✅ Toggling to "Yes" sets `enforce_monthly_quota = 1` in database
-   ✅ Toggling to "No" sets `enforce_monthly_quota = 0` in database
-   ✅ Success message reflects change
-   ✅ "Current Quota" badge updates accordingly
-   ✅ Activity log records both changes

**Pass/Fail:** [ Pass ]

---

### Test 2.6: Multiple Package Updates

**Test Case ID:** PHASE4-PKG-006  
**Priority:** Medium  
**Type:** Functional

**Steps:**

1. Update Package 1: 50 PV, Enforce: Yes
2. Update Package 2: 100 PV, Enforce: No
3. Update Package 3: 150 PV, Enforce: Yes

**Expected Results:**

-   ✅ Each update successful
-   ✅ No interference between updates
-   ✅ All values persisted correctly
-   ✅ 3 activity logs created

**Database Check:**

```sql
SELECT id, name, monthly_quota_points, enforce_monthly_quota
FROM packages
ORDER BY id;
```

**Pass/Fail:** [ Pass ]

---

### Test 2.7: Back Button Navigation

**Test Case ID:** PHASE4-PKG-007  
**Priority:** Low  
**Type:** Navigation

**Steps:**

1. Click "Back to Dashboard" button
2. Verify redirection

**Expected Results:**

-   ✅ Redirects to `/admin/monthly-quota`
-   ✅ Dashboard loads successfully

**Pass/Fail:** [ Pass ]

---

### Test 2.8: Rate Limiting

**Test Case ID:** PHASE4-PKG-008  
**Priority:** Medium  
**Type:** Security

**Steps:**

1. Update a package quota
2. Immediately update 30 times rapidly (within 1 minute)

**Expected Results:**

-   ✅ First 30 requests succeed
-   ✅ 31st request blocked with HTTP 429 (Too Many Requests)
-   ✅ Error message: "Too many requests"

**Throttle Rule:** `throttle:30,1` (30 requests per minute)

**Pass/Fail:** [ Pass ]

---

## 3. Reports Tests

### Test 3.1: Reports Page Load

**Test Case ID:** PHASE4-RPT-001  
**Priority:** High  
**Type:** Functional

**Steps:**

1. Navigate to `/admin/monthly-quota/reports`

**Expected Results:**

-   ✅ Page loads successfully
-   ✅ Default filters: Current month and year
-   ✅ Month dropdown shows all 12 months
-   ✅ Year dropdown shows current year and 2 previous years
-   ✅ Filter button visible
-   ✅ Summary cards display
-   ✅ Reports table displays

**Pass/Fail:** [ Pass ]

---

### Test 3.2: Summary Cards Accuracy

**Test Case ID:** PHASE4-RPT-002  
**Priority:** Critical  
**Type:** Data Validation

**Steps:**

1. Navigate to reports page
2. Note the displayed statistics
3. Verify with database queries

**Expected Cards:**

1. Total Users (primary/blue)
2. Quota Met (success/green)
3. Quota Not Met (warning/yellow)
4. Average PV (info/cyan)

**Verification Queries:**

```sql
SET @year = YEAR(CURDATE());
SET @month = MONTH(CURDATE());

-- Total Users
SELECT COUNT(*) as total_users
FROM monthly_quota_tracker
WHERE year = @year AND month = @month;

-- Quota Met
SELECT COUNT(*) as quota_met
FROM monthly_quota_tracker
WHERE year = @year AND month = @month AND quota_met = 1;

-- Quota Not Met
SELECT COUNT(*) as quota_not_met
FROM monthly_quota_tracker
WHERE year = @year AND month = @month AND quota_met = 0;

-- Average PV
SELECT AVG(total_pv_points) as avg_pv
FROM monthly_quota_tracker
WHERE year = @year AND month = @month;
```

**Expected Results:**

-   ✅ All card values match database exactly
-   ✅ Numbers formatted with commas
-   ✅ Average PV shows 2 decimal places

**Pass/Fail:** [ Pass ]

---

### Test 3.3: Reports Table Display

**Test Case ID:** PHASE4-RPT-003  
**Priority:** High  
**Type:** Functional

**Steps:**

1. Review the reports table

**Expected Results:**

Table Columns:

-   ✅ User ID
-   ✅ Username (clickable link)
-   ✅ Total PV (2 decimals)
-   ✅ Required Quota (2 decimals)
-   ✅ Progress (progress bar)
-   ✅ Status (badge: Met/Not Met)
-   ✅ Last Purchase (formatted date)
-   ✅ Actions (View button)

Table Data:

-   ✅ Sorted by `total_pv_points DESC`
-   ✅ Shows 50 records per page (pagination)
-   ✅ Progress bar fills correctly (width matches percentage)
-   ✅ Progress bar color: green if quota met, yellow if not
-   ✅ If no data: "No data available for this period"

**Pass/Fail:** [ Pass ]

---

### Test 3.4: Month/Year Filter

**Test Case ID:** PHASE4-RPT-004  
**Priority:** High  
**Type:** Functional

**Steps:**

1. Change month to "October"
2. Change year to "2024"
3. Click "Filter" button

**Expected Results:**

-   ✅ Page reloads with new parameters
-   ✅ URL contains: `?year=2024&month=10`
-   ✅ Summary cards update for selected period
-   ✅ Table shows data for October 2024
-   ✅ Dropdowns retain selected values
-   ✅ Card title shows "October 2024"

**Database Verification:**

```sql
SELECT * FROM monthly_quota_tracker
WHERE year = 2024 AND month = 10
ORDER BY total_pv_points DESC;
```

**Pass/Fail:** [ Pass ]

---

### Test 3.5: Pagination

**Test Case ID:** PHASE4-RPT-005  
**Priority:** Medium  
**Type:** Functional

**Prerequisites:** Have more than 50 tracker records

**Steps:**

1. Navigate to reports page
2. Scroll to pagination controls
3. Click "Next" or page "2"
4. Navigate between pages

**Expected Results:**

-   ✅ Shows 50 records per page
-   ✅ Pagination controls visible if > 50 records
-   ✅ Page navigation works correctly
-   ✅ Filter parameters preserved in pagination links
-   ✅ Current page highlighted
-   ✅ "Previous" and "Next" buttons work

**Pass/Fail:** [ Pass ]

---

### Test 3.6: Progress Bar Calculation

**Test Case ID:** PHASE4-RPT-006  
**Priority:** High  
**Type:** Data Validation

**Steps:**

1. Pick a user from reports table
2. Note their Total PV and Required Quota
3. Calculate manually: (Total PV / Required Quota) × 100
4. Compare with displayed progress bar

**Test Cases:**

-   User A: 50 PV / 100 quota = 50% (width: 50%)
-   User B: 150 PV / 100 quota = 150% capped at 100% (width: 100%)
-   User C: 0 PV / 100 quota = 0% (width: 0%)

**Expected Results:**

-   ✅ Progress percentage matches calculation
-   ✅ Width attribute matches percentage
-   ✅ Never exceeds 100% width
-   ✅ Text inside bar shows correct percentage
-   ✅ Color: green if ≥100%, yellow if <100%

**Pass/Fail:** [ ]

---

### Test 3.7: Username Link to User Report

**Test Case ID:** PHASE4-RPT-007  
**Priority:** High  
**Type:** Navigation

**Steps:**

1. Click on any username in the table
2. Verify redirection

**Expected Results:**

-   ✅ Redirects to `/admin/monthly-quota/reports/user/{user_id}`
-   ✅ User report page loads
-   ✅ Shows correct user's information

**Pass/Fail:** [ Pass ]

---

### Test 3.8: View Button

**Test Case ID:** PHASE4-RPT-008  
**Priority:** Medium  
**Type:** Navigation

**Steps:**

1. Click "View" button in Actions column
2. Verify redirection

**Expected Results:**

-   ✅ Same as Test 3.7
-   ✅ Redirects to user report page
-   ✅ Correct user data displayed

**Pass/Fail:** [ Pass ]

---

### Test 3.9: Empty State

**Test Case ID:** PHASE4-RPT-009  
**Priority:** Low  
**Type:** Edge Case

**Steps:**

1. Select a future month/year with no data
2. Click Filter

**Expected Results:**

-   ✅ Summary cards show 0 values
-   ✅ Average PV shows 0.00
-   ✅ Table shows "No data available for this period"
-   ✅ No pagination controls
-   ✅ No errors or blank page

**Pass/Fail:** [ Pass ]

---

## 4. User Report Tests

### Test 4.1: User Report Access

**Test Case ID:** PHASE4-USR-001  
**Priority:** High  
**Type:** Functional

**Steps:**

1. Navigate to `/admin/monthly-quota/reports/user/9` (or any valid user ID)

**Expected Results:**

-   ✅ Page loads successfully
-   ✅ Page title shows "User Quota Report: {username}"
-   ✅ No errors
-   ✅ "Back to Reports" button visible

**Pass/Fail:** [ Pass ]

---

### Test 4.2: User Information Card

**Test Case ID:** PHASE4-USR-002  
**Priority:** High  
**Type:** Data Display

**Steps:**

1. Navigate to a user report
2. Review "User Information" card

**Expected Results:**

Left Column:

-   ✅ User ID matches
-   ✅ Username correct
-   ✅ Email correct
-   ✅ Network Status badge (green=active, grey=inactive)

Right Column:

-   ✅ Package name displayed (or "N/A")
-   ✅ Monthly Quota Required shows PV value (or "N/A")
-   ✅ Quota Enforced badge (yellow=Yes, grey=No)
-   ✅ Qualifies for Bonus badge (green=Yes, red=No)

**Database Verification:**

```sql
SELECT u.id, u.username, u.email, u.network_status,
       p.name as package_name, p.monthly_quota_points, p.enforce_monthly_quota
FROM users u
LEFT JOIN orders o ON o.user_id = u.id AND o.payment_status = 'paid'
LEFT JOIN order_items oi ON oi.order_id = o.id
LEFT JOIN packages p ON p.id = oi.package_id AND p.is_mlm_package = 1
WHERE u.id = 9
LIMIT 1;
```

**Pass/Fail:** [ Pass ]

---

### Test 4.3: Current Month Status Card

**Test Case ID:** PHASE4-USR-003  
**Priority:** Critical  
**Type:** Data Validation

**Steps:**

1. Navigate to user report
2. Review "Current Month Status" card
3. Verify all displayed values

**Expected Results:**

Stats Row (4 columns):

-   ✅ PV Earned: Shows `total_pv_points` from tracker
-   ✅ Required Quota: Shows user's package quota
-   ✅ PV Remaining: Shows `max(0, required - earned)`
-   ✅ Progress: Shows percentage with 1 decimal

Progress Bar:

-   ✅ Width matches progress percentage (capped at 100%)
-   ✅ Color: green if quota met, yellow if not
-   ✅ Text inside shows percentage

Alert Box:

-   ✅ If quota met: Green success alert with checkmark icon
-   ✅ If quota not met: Yellow warning alert with exclamation icon
-   ✅ Message is contextually appropriate

Last Purchase:

-   ✅ Shows formatted datetime: "November 18, 2025 4:30 PM"
-   ✅ Or "N/A" if never purchased

**Manual Calculation Verification:**

```sql
SELECT
    total_pv_points as earned,
    required_quota,
    (required_quota - total_pv_points) as remaining,
    ROUND((total_pv_points / required_quota) * 100, 1) as progress_pct,
    quota_met
FROM monthly_quota_tracker
WHERE user_id = 9
  AND year = YEAR(CURDATE())
  AND month = MONTH(CURDATE());
```

**Pass/Fail:** [ Pass ]

---

### Test 4.4: Quota History Table

**Test Case ID:** PHASE4-USR-004  
**Priority:** High  
**Type:** Data Display

**Steps:**

1. Scroll to "Quota History" card
2. Review table contents

**Expected Results:**

-   ✅ Shows last 12 months of data
-   ✅ Ordered by year DESC, month DESC (most recent first)
-   ✅ Month column shows month name (not number)
-   ✅ Year column shows 4-digit year
-   ✅ Total PV shows 2 decimals
-   ✅ Required Quota shows 2 decimals
-   ✅ Progress bar displays correctly
-   ✅ Status badge (green=Met, yellow=Not Met)
-   ✅ If no history: "No history available"

**Database Verification:**

```sql
SELECT year, month, total_pv_points, required_quota, quota_met
FROM monthly_quota_tracker
WHERE user_id = 9
ORDER BY year DESC, month DESC
LIMIT 12;
```

**Pass/Fail:** [ Pass ]

---

### Test 4.5: Qualification Logic

**Test Case ID:** PHASE4-USR-005  
**Priority:** Critical  
**Type:** Business Logic

**Test Scenarios:**

**Scenario A: User with Quota Met**

-   Network Status: Active
-   Monthly PV: 120 / 100
-   Quota Enforced: Yes
-   Expected: "Qualifies for Bonus" = YES (green)

**Scenario B: User with Quota Not Met**

-   Network Status: Active
-   Monthly PV: 50 / 100
-   Quota Enforced: Yes
-   Expected: "Qualifies for Bonus" = NO (red)

**Scenario C: User Without Quota Enforcement**

-   Network Status: Active
-   Monthly PV: 0 / 100
-   Quota Enforced: No
-   Expected: "Qualifies for Bonus" = YES (green)

**Scenario D: Inactive User**

-   Network Status: Inactive
-   Monthly PV: 120 / 100
-   Quota Enforced: Yes
-   Expected: "Qualifies for Bonus" = NO (red)

**Expected Results:**

-   ✅ All scenarios display correct qualification status
-   ✅ Badge colors match expected
-   ✅ Logic matches `User::qualifiesForUnilevelBonus()` method

**Pass/Fail:** [ Pass ]

---

### Test 4.6: Invalid User ID

**Test Case ID:** PHASE4-USR-006  
**Priority:** Medium  
**Type:** Error Handling

**Steps:**

1. Navigate to `/admin/monthly-quota/reports/user/99999` (non-existent ID)

**Expected Results:**

-   ✅ Shows 404 error page or graceful error
-   ✅ Or redirects to reports page with error message
-   ✅ No PHP exceptions
-   ✅ No blank page

**Pass/Fail:** [ Pass ]

---

### Test 4.7: Back Button Navigation

**Test Case ID:** PHASE4-USR-007  
**Priority:** Low  
**Type:** Navigation

**Steps:**

1. Click "Back to Reports" button

**Expected Results:**

-   ✅ Redirects to `/admin/monthly-quota/reports`
-   ✅ Reports page loads successfully
-   ✅ Retains previous filter settings (if any)

**Pass/Fail:** [ Pass ]

---

### Test 4.8: User Without Package

**Test Case ID:** PHASE4-USR-008  
**Priority:** Medium  
**Type:** Edge Case

**Prerequisites:** Find or create a user who hasn't purchased any package

**Steps:**

1. Navigate to that user's report page

**Expected Results:**

-   ✅ Package: Shows "N/A"
-   ✅ Monthly Quota Required: Shows "N/A"
-   ✅ Quota Enforced: Shows "No" (grey badge)
-   ✅ No PHP errors
-   ✅ Current status card may show 0/0
-   ✅ History may be empty

**Pass/Fail:** [ Pass ]

---

## Integration Tests

### Test INT-001: End-to-End Flow

**Priority:** Critical  
**Type:** Integration

**Steps:**

1. Admin sets package quota to 100 PV (enforced)
2. User purchases products worth 50 PV
3. Check user report - should show 50/100 PV, not qualified
4. User purchases products worth 60 PV more (total 110 PV)
5. Check user report - should show 110/100 PV, qualified
6. User's downline makes a purchase
7. Verify admin can see updated stats on dashboard

**Expected Results:**

-   ✅ All steps complete successfully
-   ✅ Data flows correctly through system
-   ✅ Dashboard reflects changes
-   ✅ Reports show updated values
-   ✅ User report shows current status

**Pass/Fail:** [ Pass ]

---

### Test INT-002: Cross-Page Navigation

**Priority:** Medium  
**Type:** Integration

**Steps:**

1. Start at dashboard
2. Click "Manage Package Quotas"
3. Update a package
4. Click "View Reports"
5. Filter to specific month
6. Click username to view user report
7. Click "Back to Reports"
8. Verify filter retained

**Expected Results:**

-   ✅ All navigation works smoothly
-   ✅ No broken links
-   ✅ State preserved where expected
-   ✅ Breadcrumbs/navigation consistent

**Pass/Fail:** [ Pass ]

---

### Test INT-003: Activity Log Integration

**Priority:** Medium  
**Type:** Integration

**Steps:**

1. Update 3 different packages
2. Navigate to `/admin/logs` (if activity log page exists)
3. Or check database: `SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 10`

**Expected Results:**

-   ✅ 3 activity logs created
-   ✅ Each log has correct description
-   ✅ Properties contain old/new values
-   ✅ `causer_id` is admin user ID
-   ✅ `subject_id` is package ID
-   ✅ Timestamps accurate

**Pass/Fail:** [ Pass ]

---

## Performance Tests

### Test PERF-001: Dashboard Load Time

**Priority:** Medium  
**Type:** Performance

**Steps:**

1. Clear all caches
2. Measure page load time of dashboard

**Expected Results:**

-   ✅ Initial load < 2 seconds
-   ✅ Subsequent loads < 1 second (with caching)
-   ✅ Database queries optimized
-   ✅ No N+1 query issues

**Tools:** Browser DevTools Network tab, Laravel Debugbar

**Pass/Fail:** [ Pass ]

---

### Test PERF-002: Reports with Large Dataset

**Priority:** Medium  
**Type:** Performance

**Prerequisites:** 1000+ monthly quota tracker records

**Steps:**

1. Navigate to reports page
2. Measure load time
3. Test pagination performance

**Expected Results:**

-   ✅ Page load < 3 seconds
-   ✅ Pagination works smoothly
-   ✅ Only 50 records queried per page (LIMIT 50)
-   ✅ No full table scans

**Verify Query:**

```sql
EXPLAIN SELECT * FROM monthly_quota_tracker
WHERE year = 2025 AND month = 11
ORDER BY total_pv_points DESC
LIMIT 50 OFFSET 0;
```

Should use indexes, not full table scan.

**Pass/Fail:** [ Pass ]

---

### Test PERF-003: Concurrent Package Updates

**Priority:** Low  
**Type:** Performance

**Steps:**

1. Open 3 browser tabs
2. Update 3 different packages simultaneously
3. Verify all succeed

**Expected Results:**

-   ✅ All 3 updates succeed
-   ✅ No database lock issues
-   ✅ No race conditions
-   ✅ Activity logs for all 3

**Pass/Fail:** [ Pass ]

---

## Security Tests

### Test SEC-001: Admin Role Required

**Priority:** Critical  
**Type:** Security

**Steps:**

1. Logout admin
2. Login as regular member user
3. Try accessing `/admin/monthly-quota`

**Expected Results:**

-   ✅ Access denied (HTTP 403 or redirect)
-   ✅ Error message: "Unauthorized" or similar
-   ✅ Cannot access any admin quota pages
-   ✅ Routes protected by `role:admin` middleware

**Test All Routes:**

-   `/admin/monthly-quota`
-   `/admin/monthly-quota/packages`
-   `/admin/monthly-quota/reports`
-   `/admin/monthly-quota/reports/user/1`

**Pass/Fail:** [ Pass ]

---

### Test SEC-002: Unauthenticated Access

**Priority:** Critical  
**Type:** Security

**Steps:**

1. Logout completely
2. Try accessing admin quota pages directly via URL

**Expected Results:**

-   ✅ Redirected to login page
-   ✅ After login, redirected back to intended page (if remember redirect works)
-   ✅ Cannot access without authentication

**Pass/Fail:** [ Pass ]

---

### Test SEC-003: CSRF Protection

**Priority:** High  
**Type:** Security

**Steps:**

1. Inspect package update form
2. Remove `@csrf` token from form
3. Submit form

**Expected Results:**

-   ✅ Request rejected (HTTP 419 or 403)
-   ✅ Error: "CSRF token mismatch"
-   ✅ Database not updated

**Pass/Fail:** [ Pass ]

---

### Test SEC-004: SQL Injection Attempts

**Priority:** High  
**Type:** Security

**Steps:**
Try submitting these values in package quota field:

1. `'; DROP TABLE packages; --`
2. `1' OR '1'='1`
3. `<script>alert('xss')</script>`

**Expected Results:**

-   ✅ All attempts blocked by validation
-   ✅ No SQL executed
-   ✅ No XSS executed
-   ✅ Error messages shown
-   ✅ Database integrity maintained

**Pass/Fail:** [ Pass ]

---

### Test SEC-005: Direct Database Modification

**Priority:** Medium  
**Type:** Security

**Steps:**

1. Update package quota via form
2. Manually change database value
3. Check if activity log still reflects form submission only

**Expected Results:**

-   ✅ Manual DB changes don't create activity logs
-   ✅ Only form submissions logged
-   ✅ System relies on application logic, not DB triggers

**Pass/Fail:** [ Pass ]

---

## Edge Cases & Error Handling

### Test EDGE-001: Zero Quota Values

**Priority:** Medium  
**Type:** Edge Case

**Steps:**

1. Set package quota to `0.00`
2. Set enforce to "Yes"
3. Check user reports

**Expected Results:**

-   ✅ Accepted and saved
-   ✅ Users with 0 quota requirement always qualify (100% progress)
-   ✅ No division by zero errors
-   ✅ Progress shows 100%

**Pass/Fail:** [ Pass ]

---

### Test EDGE-002: Maximum Quota Values

**Priority:** Medium  
**Type:** Edge Case

**Steps:**

1. Set package quota to `9999.99`
2. Create user with 100 PV
3. Check progress

**Expected Results:**

-   ✅ Quota saved correctly
-   ✅ Progress shows 1% (100/9999.99)
-   ✅ No overflow errors
-   ✅ Display correct

**Pass/Fail:** [ Pass ]

---

### Test EDGE-003: Fractional PV Values

**Priority:** Medium  
**Type:** Edge Case

**Steps:**

1. User has 99.99 PV
2. Quota is 100.00 PV
3. Check status

**Expected Results:**

-   ✅ Shows as "Not Met"
-   ✅ Remaining: 0.01 PV
-   ✅ Progress: 99.99%
-   ✅ Proper decimal handling

**Pass/Fail:** [ Pass ]

---

### Test EDGE-004: Month/Year Boundaries

**Priority:** Medium  
**Type:** Edge Case

**Steps:**

1. Set system date to December 31, 2024, 11:59 PM
2. View reports for December 2024
3. Wait until January 1, 2025, 12:00 AM
4. View reports for January 2025

**Expected Results:**

-   ✅ December data shows correctly
-   ✅ January starts fresh (no carryover)
-   ✅ Data segregated properly by month
-   ✅ No timezone issues

**Pass/Fail:** [ Pass ]

---

### Test EDGE-005: User Without Tracker Record

**Priority:** Medium  
**Type:** Edge Case

**Steps:**

1. Find new user (just registered)
2. Check their user report

**Expected Results:**

-   ✅ Page loads without error
-   ✅ Current status shows 0/0 or creates tracker on-the-fly
-   ✅ History shows empty or "No history available"
-   ✅ No PHP exceptions

**Pass/Fail:** [ Pass ]

---

## Browser Compatibility

### Test BROWSER-001: Chrome/Edge (Chromium)

**Priority:** High  
**Type:** Compatibility

**Steps:**
Test all pages in Chrome/Edge latest version

**Expected Results:**

-   ✅ All pages render correctly
-   ✅ Forms work
-   ✅ Buttons clickable
-   ✅ Progress bars display
-   ✅ Badges show colors
-   ✅ No console errors

**Pass/Fail:** [ Pass ]

---

### Test BROWSER-002: Firefox

**Priority:** Medium  
**Type:** Compatibility

**Steps:**
Test all pages in Firefox latest version

**Expected Results:**

-   ✅ Same as Chrome test

**Pass/Fail:** [ Pass ]

---

### Test BROWSER-003: Safari (if available)

**Priority:** Low  
**Type:** Compatibility

**Steps:**
Test all pages in Safari

**Expected Results:**

-   ✅ Same as Chrome test

**Pass/Fail:** [ Pass ]

---

### Test BROWSER-004: Mobile Responsive

**Priority:** Medium  
**Type:** Responsive Design

**Steps:**

1. Open DevTools
2. Toggle device toolbar
3. Test on various screen sizes:
    - iPhone SE (375px)
    - iPad (768px)
    - iPad Pro (1024px)

**Expected Results:**

-   ✅ Tables scroll horizontally on small screens
-   ✅ Cards stack vertically on mobile
-   ✅ Buttons remain accessible
-   ✅ Text readable
-   ✅ Forms usable
-   ✅ Navigation works

**Pass/Fail:** [ Pass ]

---

## Regression Tests

### Test REG-001: Existing Product Management

**Priority:** High  
**Type:** Regression

**Steps:**

1. Navigate to `/admin/products`
2. Edit a product
3. Update "Points Awarded" field
4. Save

**Expected Results:**

-   ✅ Product edit page still works
-   ✅ Points awarded field accepts decimals
-   ✅ Save successful
-   ✅ No interference from Phase 4 changes

**Pass/Fail:** [ Pass ]

---

### Test REG-002: Existing Package Management

**Priority:** High  
**Type:** Regression

**Steps:**

1. Navigate to `/admin/packages`
2. Edit a package
3. Update basic fields (name, price, etc.)
4. Save

**Expected Results:**

-   ✅ Package edit still works
-   ✅ New quota fields visible but optional
-   ✅ Save successful
-   ✅ No required field errors for quota

**Pass/Fail:** [ Pass ]

---

### Test REG-003: Unilevel Bonus Distribution

**Priority:** Critical  
**Type:** Regression

**Steps:**

1. Create test order with products
2. Trigger Unilevel bonus distribution
3. Check logs

**Expected Results:**

-   ✅ Bonuses still distributed
-   ✅ Now respects quota requirements
-   ✅ Enhanced logs show skip reasons
-   ✅ Phase 3 integration still works

**Pass/Fail:** [ ]

---

### Test REG-004: Existing Admin Dashboard

**Priority:** Medium  
**Type:** Regression

**Steps:**

1. Navigate to `/admin/dashboard`

**Expected Results:**

-   ✅ Main admin dashboard unaffected
-   ✅ No broken links or styling
-   ✅ All existing features work

**Pass/Fail:** [ Pass ]

---

## Test Results Template

### Test Execution Summary

**Test Date:** **\*\***\_\_\_**\*\***  
**Tester Name:** **\*\***\_\_\_**\*\***  
**Environment:** Development / Staging / Production  
**Phase:** 4 - Admin Configuration Interface

### Overall Results

| Category          | Total Tests | Passed | Failed | Blocked | Pass Rate |
| ----------------- | ----------- | ------ | ------ | ------- | --------- |
| Dashboard Tests   | 7           |        |        |         |           |
| Package Tests     | 8           |        |        |         |           |
| Reports Tests     | 9           |        |        |         |           |
| User Report Tests | 8           |        |        |         |           |
| Integration Tests | 3           |        |        |         |           |
| Performance Tests | 3           |        |        |         |           |
| Security Tests    | 5           |        |        |         |           |
| Edge Cases        | 5           |        |        |         |           |
| Browser Compat    | 4           |        |        |         |           |
| Regression Tests  | 4           |        |        |         |           |
| **TOTAL**         | **56**      |        |        |         |           |

### Critical Issues Found

| Issue ID | Severity | Description | Status |
| -------- | -------- | ----------- | ------ |
|          |          |             |        |

### Recommendations

1.
2.
3.

### Sign-off

**QA Tester:** \***\*\*\*\*\***\_\***\*\*\*\*\***  
**Date:** \***\*\*\*\*\***\_\***\*\*\*\*\***

**Development Lead:** \***\*\*\*\*\***\_\***\*\*\*\*\***  
**Date:** \***\*\*\*\*\***\_\***\*\*\*\*\***

---

## Appendix A: Quick Test Data Setup

### Create Test Data Quickly

```bash
php artisan tinker
```

```php
use App\Models\User;
use App\Models\Package;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\MonthlyQuotaService;

// 1. Set package quotas
$starter = Package::where('name', 'Starter')->first();
$starter->monthly_quota_points = 100.00;
$starter->enforce_monthly_quota = true;
$starter->save();

// 2. Set product PV
$product = Product::first();
$product->points_awarded = 10.50;
$product->save();

// 3. Create test order for user
$user = User::find(9);
$order = Order::create([
    'user_id' => $user->id,
    'order_number' => 'TEST-QA-' . time(),
    'payment_status' => 'paid',
    'payment_method' => 'wallet',
    'subtotal' => $product->price * 5,
    'tax_amount' => 0,
    'total_amount' => $product->price * 5,
    'delivery_method' => 'office_pickup',
]);

OrderItem::create([
    'order_id' => $order->id,
    'product_id' => $product->id,
    'item_type' => 'product',
    'quantity' => 5,
    'unit_price' => $product->price,
    'total_price' => $product->price * 5,
    'points_awarded_per_item' => $product->points_awarded,
    'total_points_awarded' => $product->points_awarded * 5,
]);

// 4. Process quota points
$quotaService = app(MonthlyQuotaService::class);
$quotaService->processOrderPoints($order);

// 5. Verify
$status = $quotaService->getUserMonthlyStatus($user);
print_r($status);
```

---

## Appendix B: Database Queries for Manual Verification

### Check Package Quotas

```sql
SELECT id, name, price, is_mlm_package,
       monthly_quota_points, enforce_monthly_quota
FROM packages
ORDER BY id;
```

### Check Products with PV

```sql
SELECT id, name, slug, points_awarded, price, status
FROM products
WHERE points_awarded > 0
ORDER BY points_awarded DESC;
```

### Check Monthly Quota Trackers

```sql
SELECT mqt.id, mqt.user_id, u.username,
       mqt.year, mqt.month,
       mqt.total_pv_points, mqt.required_quota, mqt.quota_met,
       mqt.last_purchase_at
FROM monthly_quota_tracker mqt
JOIN users u ON u.id = mqt.user_id
WHERE mqt.year = YEAR(CURDATE())
  AND mqt.month = MONTH(CURDATE())
ORDER BY mqt.total_pv_points DESC;
```

### Check Activity Logs

```sql
SELECT id, log_name, description,
       causer_type, causer_id,
       subject_type, subject_id,
       properties, created_at
FROM activity_log
WHERE description LIKE '%quota%'
ORDER BY created_at DESC
LIMIT 10;
```

---

## Appendix C: Common Issues & Solutions

### Issue 1: "Class MonthlyQuotaController not found"

**Solution:**

```bash
composer dump-autoload
php artisan config:clear
```

### Issue 2: "View not found"

**Solution:**

```bash
php artisan view:clear
# Verify files exist
ls resources/views/admin/monthly-quota/
```

### Issue 3: "Route not found"

**Solution:**

```bash
php artisan route:clear
php artisan route:cache
php artisan route:list --path=monthly-quota
```

### Issue 4: "SQLSTATE error"

**Solution:**

```bash
# Check if migrations ran
php artisan migrate:status

# Check if tables exist
php artisan tinker
Schema::hasTable('monthly_quota_tracker');
```

### Issue 5: Stats showing 0 when should have data

**Solution:**

-   Check if MonthlyQuotaService is processing orders
-   Verify tracker records exist for current month
-   Check logs: `tail -50 storage/logs/laravel.log`

---

**END OF PHASE 4 TESTING GUIDE**

---

## Notes for QA Team

1. **Test in Order**: Complete Dashboard tests first, then Packages, then Reports
2. **Use Real Data**: Test with actual data when possible, not just test data
3. **Document Everything**: Screenshot any errors or unexpected behavior
4. **Check Logs**: Always check Laravel logs after each test section
5. **Database Backup**: Take backup before starting destructive tests
6. **Time Each Test**: Note if any page takes > 3 seconds to load
7. **Cross-Reference**: Verify data with manual database queries
8. **Report Issues**: Use detailed issue descriptions with steps to reproduce

### Priority Testing Order

1. **P0 (Must Pass)**: Dashboard Tests, Package Updates, Reports Display, Security Tests
2. **P1 (Should Pass)**: User Reports, Integration Tests, Data Validation
3. **P2 (Nice to Pass)**: Edge Cases, Browser Compatibility, Performance

### Estimated Testing Time

-   Full test suite: 4-6 hours
-   Quick smoke test: 30 minutes
-   Regression only: 1 hour

Good luck with testing! 🧪
