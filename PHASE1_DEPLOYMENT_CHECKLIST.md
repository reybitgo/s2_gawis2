# Phase 1 Deployment Checklist

**Date:** ________________  
**Deployed By:** ________________  
**Server:** ________________

---

## Pre-Deployment

- [ ] Read `UNILEVEL_QUOTA_INSTALL.md` completely
- [ ] Database backup created
- [ ] Files backup created
- [ ] SSH access confirmed
- [ ] Maintenance mode enabled (optional): `php artisan down --retry=60`

---

## Files to Upload - New Files (3 migrations + 1 model + 1 maintenance page)

### Migrations (database/migrations/)
- [ ] `2025_11_16_090015_modify_points_awarded_to_decimal_in_products_table.php`
- [ ] `2025_11_16_090020_add_monthly_quota_to_packages_table.php`
- [ ] `2025_11_16_090022_create_monthly_quota_tracker_table.php`

### Views (resources/views/errors/)
- [ ] `503.blade.php` (custom maintenance page - friendly, reassuring)

### Models (app/Models/)
- [ ] `MonthlyQuotaTracker.php`

### Optional Test Scripts (root directory)
- [ ] `test_phase1_monthly_quota.php`
- [ ] `test_product_decimal_pv.php`

---

## Files to Upload - Modified Files (6 files)

### Models (app/Models/)
- [ ] `Product.php` (updated cast: points_awarded to decimal:2)
- [ ] `Package.php` (added fillable: monthly_quota_points, enforce_monthly_quota)
- [ ] `User.php` (added 6 new methods for quota tracking)

### Controllers (app/Http/Controllers/Admin/)
- [ ] `AdminProductController.php` (updated validation: numeric instead of integer)

### Views (resources/views/admin/products/)
- [ ] `edit.blade.php` (added step="0.01" for decimal PV input)
- [ ] `create.blade.php` (added step="0.01" for decimal PV input)

---

## Post-Upload Commands

- [ ] Set file permissions: `chmod 644 database/migrations/*.php`
- [ ] Clear config: `php artisan config:clear`
- [ ] Clear cache: `php artisan cache:clear`
- [ ] Clear routes: `php artisan route:clear`
- [ ] Clear views: `php artisan view:clear`
- [ ] Dump autoload: `composer dump-autoload`

---

## Database Migration

- [ ] Check migration status: `php artisan migrate:status`
- [ ] Run migrations: `php artisan migrate`
- [ ] Verify 3 new migrations completed:
  - [ ] modify_points_awarded_to_decimal_in_products_table
  - [ ] add_monthly_quota_to_packages_table
  - [ ] create_monthly_quota_tracker_table

---

## Database Verification

```bash
mysql -u USER -p DATABASE
```

- [ ] Check `products.points_awarded` is decimal(10,2)
  ```sql
  DESCRIBE products;
  ```
- [ ] Check `packages` has new columns: `monthly_quota_points`, `enforce_monthly_quota`
  ```sql
  DESCRIBE packages;
  ```
- [ ] Check `monthly_quota_tracker` table exists
  ```sql
  DESCRIBE monthly_quota_tracker;
  ```
- [ ] Check indexes on `monthly_quota_tracker`
  ```sql
  SHOW INDEX FROM monthly_quota_tracker;
  ```

---

## Application Testing

### Admin Panel Tests
- [ ] Login as admin
- [ ] Navigate to Products → Edit any product
- [ ] Verify "Points Awarded (PV)" field accepts decimals (try: 10.75)
- [ ] Save product with decimal PV
- [ ] Verify it saved correctly (re-open edit form)
- [ ] Navigate to Products → Create new product
- [ ] Test decimal PV input on create form

### Maintenance Page Test (Optional)
- [ ] Run: `php artisan down --retry=60`
- [ ] Open browser in incognito/private mode
- [ ] Visit your site's homepage
- [ ] Verify you see: "We're Making Things Better!" page (NOT standard Laravel page)
- [ ] Verify countdown timer works (counts down from 60)
- [ ] Verify animated progress bar shows
- [ ] Click "Check Status" button (should work)
- [ ] Wait for auto-refresh or press 'R' key
- [ ] Run: `php artisan up` to disable maintenance mode

### Backend Tests (SSH)
- [ ] Run: `php test_phase1_monthly_quota.php` (if uploaded)
- [ ] Verify all tests pass (7 tests)
- [ ] Run: `php test_product_decimal_pv.php` (if uploaded)
- [ ] Verify decimal values work

---

## Error Checking

- [ ] Check Laravel logs: `tail -n 100 storage/logs/laravel.log`
- [ ] No errors related to:
  - MonthlyQuotaTracker class not found
  - Column not found errors
  - Validation errors
  - Migration failures

---

## Post-Deployment

- [ ] Disable maintenance mode: `php artisan up`
- [ ] Monitor logs for 15 minutes: `tail -f storage/logs/laravel.log`
- [ ] Test existing features:
  - [ ] User registration works
  - [ ] Order placement works
  - [ ] Product browsing works
  - [ ] MLM bonus calculations work (unchanged)
- [ ] Delete test scripts (optional): `rm test_*.php`

---

## Data Configuration (Optional)

If you want to set initial quota values:

```bash
php artisan tinker
```

```php
// Example: Set quota for Starter package
$package = App\Models\Package::where('name', 'Starter')->first();
$package->monthly_quota_points = 100.00;
$package->enforce_monthly_quota = false; // Keep disabled for now
$package->save();

// Example: Set PV for products
$product = App\Models\Product::where('name', 'Biogen+')->first();
$product->points_awarded = 10.75;
$product->save();

exit
```

---

## Rollback (If Needed)

If anything goes wrong:

### Quick Rollback
```bash
# Rollback migrations
php artisan migrate:rollback --step=3

# Restore old files from backup
# (instructions in UNILEVEL_QUOTA_INSTALL.md)
```

### Database Restore
```bash
# Restore from backup
mysql -u USER -p DATABASE < backups/backup_before_phase1_*.sql
```

---

## Sign-Off

**Deployment Status:**
- [ ] SUCCESS - All checks passed
- [ ] PARTIAL - Some issues (document below)
- [ ] FAILED - Rollback completed

**Issues Encountered:**
```
(none)
```

**Production URL:** ________________________________

**Deployment Completed:** __________ (date/time)  
**Verified By:** __________  
**Ready for Phase 2:** YES [ ] / NO [ ]

---

## Notes

```
(add any notes about the deployment here)
```

---

**Next Step:** Review `UNILEVEL_QUOTA.md` Section "Phase 2: Points Tracking Service"
