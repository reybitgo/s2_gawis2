# Phase 1 - File Manifest

**Purpose:** Detailed list of all files to upload with their exact paths and changes.

---

## Summary

- **New Files:** 5 (3 migrations + 1 model + 1 maintenance page)
- **Modified Files:** 6 (3 models + 1 controller + 2 views)
- **Optional Test Files:** 2
- **Total Files to Upload:** 11 required + 2 optional

---

## NEW FILES (Must Upload)

### 1. Migration: Modify products.points_awarded to decimal

**Path:** `database/migrations/2025_11_16_090015_modify_points_awarded_to_decimal_in_products_table.php`

**Size:** ~1.2 KB

**What it does:**
- Changes `products.points_awarded` from `integer` to `decimal(10,2)`
- Allows fractional PV values (e.g., 10.50, 25.75)
- Non-destructive: Existing integer values preserved (10 → 10.00)

**Up Migration:**
```php
ALTER TABLE products 
  MODIFY points_awarded DECIMAL(10, 2) NOT NULL DEFAULT 0.00;
```

**Down Migration (rollback):**
```php
ALTER TABLE products 
  MODIFY points_awarded INT(11) NOT NULL DEFAULT 0;
```

---

### 2. Migration: Add monthly quota fields to packages

**Path:** `database/migrations/2025_11_16_090020_add_monthly_quota_to_packages_table.php`

**Size:** ~1.1 KB

**What it does:**
- Adds `monthly_quota_points` column (decimal 10,2, default 0)
- Adds `enforce_monthly_quota` column (boolean, default false)
- Non-destructive: Only adds new columns

**Up Migration:**
```php
ALTER TABLE packages 
  ADD COLUMN monthly_quota_points DECIMAL(10, 2) NOT NULL DEFAULT 0.00 
    AFTER max_mlm_levels,
  ADD COLUMN enforce_monthly_quota TINYINT(1) NOT NULL DEFAULT 0 
    AFTER monthly_quota_points;
```

**Down Migration (rollback):**
```php
ALTER TABLE packages 
  DROP COLUMN monthly_quota_points,
  DROP COLUMN enforce_monthly_quota;
```

---

### 3. Migration: Create monthly_quota_tracker table

**Path:** `database/migrations/2025_11_16_090022_create_monthly_quota_tracker_table.php`

**Size:** ~1.8 KB

**What it does:**
- Creates new table `monthly_quota_tracker`
- Tracks monthly PV accumulation per user
- Composite unique index on (user_id, year, month)
- No impact on existing tables

**Columns:**
- `id` (bigint, primary key)
- `user_id` (bigint, foreign key to users)
- `year` (int, e.g., 2025)
- `month` (int, 1-12)
- `total_pv_points` (decimal 10,2, default 0)
- `required_quota` (decimal 10,2, default 0)
- `quota_met` (boolean, default false)
- `last_purchase_at` (timestamp, nullable)
- `created_at`, `updated_at` (timestamps)

**Indexes:**
- PRIMARY on `id`
- UNIQUE on `(user_id, year, month)` - prevents duplicate monthly records
- INDEX on `(user_id, year, month)` - quick lookups
- INDEX on `quota_met` - filtering qualified users

---

### 4. Custom Maintenance Page (503)

**Path:** `resources/views/errors/503.blade.php`

**Size:** ~9.2 KB

**What it does:**
- Custom maintenance mode page shown when site is down
- Friendly, reassuring message about improvements
- Auto-countdown timer (60 seconds)
- Auto-reload when countdown completes
- Status check button
- Shows what's being improved
- Matches existing error page design

**Features:**
- Animated progress bar
- Countdown timer with auto-refresh
- "Check Status" button
- Rotating settings icon animation
- Keyboard shortcut (Press 'R' to reload)
- Responsive design
- No error vibe - positive improvement message

**Why:** Replaces standard Laravel maintenance page with branded, reassuring page.

---

### 5. Model: MonthlyQuotaTracker

**Path:** `app/Models/MonthlyQuotaTracker.php`

**Size:** ~1.9 KB

**What it does:**
- Eloquent model for monthly_quota_tracker table
- Manages quota tracking per user per month

**Key Methods:**
- `getOrCreateForCurrentMonth(User $user)` - Get or create tracker for current month
- `checkQuotaMet()` - Check if quota threshold reached
- `user()` - Relationship to User model

**Relationships:**
- `belongsTo(User::class)`

---

## MODIFIED FILES (Must Upload)

### 6. Model: Product

**Path:** `app/Models/Product.php`

**Size:** ~6.5 KB

**Line Changed:** Line 35

**What Changed:**
```php
// BEFORE:
'points_awarded' => 'integer',

// AFTER:
'points_awarded' => 'decimal:2',
```

**Why:** Supports decimal PV values (10.75, 25.50, etc.)

**Impact:** None on existing functionality. Only changes how the value is cast when retrieved from database.

---

### 7. Model: Package

**Path:** `app/Models/Package.php`

**Size:** ~3.8 KB

**Lines Changed:** 
- Line 29-30 (fillable array)
- Line 42-43 (casts array)

**What Changed:**

**Fillable array (line 29-30):**
```php
// ADDED:
'monthly_quota_points',
'enforce_monthly_quota',
```

**Casts array (line 42-43):**
```php
// ADDED:
'monthly_quota_points' => 'decimal:2',
'enforce_monthly_quota' => 'boolean',
```

**Why:** Allows mass assignment and proper type casting for new quota fields.

**Impact:** None on existing functionality. Only adds support for new fields.

---

### 8. Model: User

**Path:** `app/Models/User.php`

**Size:** ~11.2 KB (increased from ~9.3 KB)

**Lines Added:** 281-367 (87 new lines)

**What Changed:**
- Added 6 new methods (no existing methods modified)

**New Methods:**

1. **`monthlyQuotaTrackers()`** (line 285-288)
   - Relationship to MonthlyQuotaTracker
   - Returns: `hasMany` relationship

2. **`currentMonthQuota()`** (line 293-299)
   - Get current month's quota tracker record
   - Returns: MonthlyQuotaTracker instance or null

3. **`getMonthlyQuotaRequirement()`** (line 304-322)
   - Get required monthly PV based on user's package
   - Returns: float (0 if no package or quota not enforced)

4. **`meetsMonthlyQuota()`** (line 327-337)
   - Check if user has met current month's quota
   - Creates tracker if not exists
   - Returns: boolean

5. **`qualifiesForUnilevelBonus()`** (line 342-367)
   - Combined check: network_active AND quota met
   - Returns: boolean
   - This will replace `isNetworkActive()` in Unilevel bonus distribution (Phase 3)

**Why:** Provides easy access to quota status for any user.

**Impact:** None on existing functionality. Only adds new capabilities. Existing code continues to work.

---

### 9. Controller: AdminProductController

**Path:** `app/Http/Controllers/Admin/AdminProductController.php`

**Size:** ~6.1 KB

**Lines Changed:** 
- Line 73 (store method validation)
- Line 129 (update method validation)

**What Changed:**

**Line 73 (store method):**
```php
// BEFORE:
'points_awarded' => 'required|integer|min:0',

// AFTER:
'points_awarded' => 'required|numeric|min:0|max:9999.99',
```

**Line 129 (update method):**
```php
// BEFORE:
'points_awarded' => 'required|integer|min:0',

// AFTER:
'points_awarded' => 'required|numeric|min:0|max:9999.99',
```

**Why:** Allows decimal input validation (accepts 10.75, 25.50, etc.)

**Impact:** Product forms now accept decimal PV values. Validation enforces max 9999.99.

---

### 10. View: Product Edit Form

**Path:** `resources/views/admin/products/edit.blade.php`

**Size:** ~8.3 KB

**Lines Changed:**
- Line 9 (card styling - added mb-4 for bottom padding)
- Line 72-74 (Points Awarded input field)

**What Changed:**

**Line 9 (card):**
```html
<!-- BEFORE: -->
<div class="card">

<!-- AFTER: -->
<div class="card mb-4">
```

**Lines 72-75 (input field):**
```html
<!-- BEFORE: -->
<label for="points_awarded" class="form-label">Points Awarded <span class="text-danger">*</span></label>
<input type="number" min="0" class="form-control..." />

<!-- AFTER: -->
<label for="points_awarded" class="form-label">Points Awarded (PV) <span class="text-danger">*</span></label>
<input type="number" step="0.01" min="0" max="9999.99" class="form-control..." />
<div class="form-text">Personal Volume points for monthly quota</div>
```

**Why:** 
- `step="0.01"` enables decimal input (10.75, 25.50)
- `max="9999.99"` enforces validation limit
- Helper text clarifies field purpose
- `mb-4` adds bottom padding to card

**Impact:** Better UX for admins entering decimal PV values.

---

### 11. View: Product Create Form

**Path:** `resources/views/admin/products/create.blade.php`

**Size:** ~8.0 KB

**Lines Changed:**
- Line 9 (card styling - added mb-4 for bottom padding)
- Line 64-66 (Points Awarded input field)

**What Changed:**

**Line 9 (card):**
```html
<!-- BEFORE: -->
<div class="card">

<!-- AFTER: -->
<div class="card mb-4">
```

**Lines 64-67 (input field):**
```html
<!-- BEFORE: -->
<label for="points_awarded" class="form-label">Points Awarded <span class="text-danger">*</span></label>
<input type="number" min="0" class="form-control..." />

<!-- AFTER: -->
<label for="points_awarded" class="form-label">Points Awarded (PV) <span class="text-danger">*</span></label>
<input type="number" step="0.01" min="0" max="9999.99" class="form-control..." />
<div class="form-text">Personal Volume points for monthly quota</div>
```

**Why:** Same as edit form - enables decimal input and improves UX.

**Impact:** Consistent decimal PV input across create and edit forms.

---

## OPTIONAL TEST FILES (For Verification)

### 12. Test Script: Phase 1 Full Test

**Path:** `test_phase1_monthly_quota.php`

**Size:** ~4.1 KB

**Purpose:** Comprehensive test of Phase 1 implementation

**Tests:**
1. Product accepts decimal PV values
2. Package has monthly quota fields
3. MonthlyQuotaTracker model works
4. User model methods work
5. Quota met/not met logic
6. Relationships work

**Usage:**
```bash
php test_phase1_monthly_quota.php
```

**Expected Output:** All 7 tests PASS ✓

---

### 13. Test Script: Product Decimal PV Test

**Path:** `test_product_decimal_pv.php`

**Size:** ~0.7 KB

**Purpose:** Verify decimal PV values work correctly

**Tests:** Multiple decimal values (10.50, 25.75, 99.99, 0.25, 150.00)

**Usage:**
```bash
php test_product_decimal_pv.php
```

**Expected Output:** All test values PASS ✓

---

## Upload Checklist

Copy this to your deployment notes:

```
[ ] database/migrations/2025_11_16_090015_modify_points_awarded_to_decimal_in_products_table.php
[ ] database/migrations/2025_11_16_090020_add_monthly_quota_to_packages_table.php
[ ] database/migrations/2025_11_16_090022_create_monthly_quota_tracker_table.php
[ ] resources/views/errors/503.blade.php (custom maintenance page)
[ ] app/Models/MonthlyQuotaTracker.php
[ ] app/Models/Product.php
[ ] app/Models/Package.php
[ ] app/Models/User.php
[ ] app/Http/Controllers/Admin/AdminProductController.php
[ ] resources/views/admin/products/edit.blade.php
[ ] resources/views/admin/products/create.blade.php
[ ] test_phase1_monthly_quota.php (optional)
[ ] test_product_decimal_pv.php (optional)
```

---

## File Sizes Reference

| File | Size | Type |
|------|------|------|
| Migration 1 | ~1.2 KB | New |
| Migration 2 | ~1.1 KB | New |
| Migration 3 | ~1.8 KB | New |
| 503.blade.php | ~9.2 KB | New |
| MonthlyQuotaTracker.php | ~1.9 KB | New |
| Product.php | ~6.5 KB | Modified |
| Package.php | ~3.8 KB | Modified |
| User.php | ~11.2 KB | Modified |
| AdminProductController.php | ~6.1 KB | Modified |
| edit.blade.php | ~8.3 KB | Modified |
| create.blade.php | ~8.0 KB | Modified |
| test_phase1_monthly_quota.php | ~4.1 KB | Optional |
| test_product_decimal_pv.php | ~0.7 KB | Optional |
| **TOTAL** | **~63.9 KB** | 11 required + 2 optional |

---

## Important Notes

1. **Upload Order:** Upload all files BEFORE running migrations
2. **No Deletions:** No existing files need to be deleted
3. **Permissions:** Set 644 for PHP files, 664 for views
4. **Backup First:** Always backup before uploading modified files
5. **Test Locally:** Ensure all files tested on localhost first

---

## Migration Order (Automatic)

When you run `php artisan migrate`, they execute in this order:

1. **First:** modify_points_awarded_to_decimal_in_products_table
2. **Second:** add_monthly_quota_to_packages_table  
3. **Third:** create_monthly_quota_tracker_table

This order is safe and has no dependencies.

---

## Verification Commands

After upload:

```bash
# Check files exist
ls -lh database/migrations/2025_11_16_*
ls -lh app/Models/MonthlyQuotaTracker.php

# Check file permissions
ls -l database/migrations/2025_11_16_*

# Check syntax (PHP lint)
php -l app/Models/MonthlyQuotaTracker.php
php -l app/Models/User.php
```

---

**END OF FILE MANIFEST**
