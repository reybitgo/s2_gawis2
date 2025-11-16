# Unilevel Monthly Quota System - Phase 1 Installation Guide

**Version:** 1.0  
**Date:** November 16, 2025  
**Status:** Phase 1 - Database & Models Foundation

---

## Overview

This guide provides step-by-step instructions for deploying Phase 1 of the Unilevel Monthly Quota System to your live production server. Phase 1 establishes the foundational database schema and models required for tracking monthly Personal Volume (PV) quotas.

---

## Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Phase 1 Files Summary](#phase-1-files-summary)
3. [Backup Procedures](#backup-procedures)
4. [Deployment Steps](#deployment-steps)
5. [Database Migration](#database-migration)
6. [Verification & Testing](#verification--testing)
7. [Rollback Procedures](#rollback-procedures)
8. [Troubleshooting](#troubleshooting)
9. [Phase 2 Preparation](#phase-2-preparation)

---

## Pre-Deployment Checklist

Before deploying to production, ensure:

- [ ] All Phase 1 files are tested on localhost
- [ ] Database backup is created (see [Backup Procedures](#backup-procedures))
- [ ] SSH access to live server is available
- [ ] PHP version >= 8.1 (check: `php -v`)
- [ ] Laravel application is in maintenance mode (optional but recommended)
- [ ] You have a rollback plan ready
- [ ] No active users are performing critical transactions (if putting in maintenance)

**Recommended: Enable Maintenance Mode**
```bash
# Laravel 11/12 syntax (shows custom friendly maintenance page)
php artisan down --retry=60

# Or with secret bypass token (allows you to access site)
php artisan down --secret="your-secret-token" --retry=60
# Then access via: https://your-domain.com/your-secret-token
```

**What Users Will See:**

When maintenance mode is enabled, users see a custom branded page with:
- ⚙️ Rotating settings icon (animated)
- 📢 Friendly headline: "We're Making Things Better!"
- ✅ Blue alert: "What's happening?" - Explains upgrade in progress
- 🛡️ Green alert: "Your data is completely safe" - Reassures users
- ⏱️ Info alert: "When will we be back?" - Shows 60-second countdown
- 🎯 "What's New?" section - Lists improvements
- 🔄 "Check if We're Back" button (primary action)
- ✓ "Check Status" button (status check via AJAX)
- 📊 Animated progress bar at bottom
- ⌨️ Keyboard shortcut: Press 'R' to reload

**No error vibe** - The page emphasizes improvement and reassurance, not problems.

---

## Phase 1 Files Summary

### Files Created (New)

| File Path | Type | Purpose |
|-----------|------|---------|
| `database/migrations/2025_11_16_090015_modify_points_awarded_to_decimal_in_products_table.php` | Migration | Changes `products.points_awarded` from integer to decimal(10,2) |
| `database/migrations/2025_11_16_090020_add_monthly_quota_to_packages_table.php` | Migration | Adds `monthly_quota_points` and `enforce_monthly_quota` to packages |
| `database/migrations/2025_11_16_090022_create_monthly_quota_tracker_table.php` | Migration | Creates `monthly_quota_tracker` table for tracking monthly PV |
| `resources/views/errors/503.blade.php` | View | Custom maintenance page (friendly, reassuring message) |
| `app/Models/MonthlyQuotaTracker.php` | Model | New model for quota tracking |
| `test_phase1_monthly_quota.php` | Test Script | Verification script (optional, for testing) |
| `test_product_decimal_pv.php` | Test Script | Verification script (optional, for testing) |

### Files Modified (Updated)

| File Path | Changes Made |
|-----------|--------------|
| `app/Models/Product.php` | Changed `points_awarded` cast from `integer` to `decimal:2` |
| `app/Models/Package.php` | Added `monthly_quota_points` and `enforce_monthly_quota` to fillable/casts |
| `app/Models/User.php` | Added 6 new methods for quota tracking (see details below) |
| `app/Http/Controllers/Admin/AdminProductController.php` | Updated validation: `points_awarded` from `integer` to `numeric\|min:0\|max:9999.99` |
| `resources/views/admin/products/edit.blade.php` | Added decimal support (step="0.01") and helper text for PV field |
| `resources/views/admin/products/create.blade.php` | Added decimal support (step="0.01") and helper text for PV field |

### User Model New Methods

Added to `app/Models/User.php`:

1. `monthlyQuotaTrackers()` - Relationship to MonthlyQuotaTracker
2. `currentMonthQuota()` - Get current month's quota tracker
3. `getMonthlyQuotaRequirement()` - Returns required monthly PV based on package
4. `meetsMonthlyQuota()` - Checks if user has met current month's quota
5. `qualifiesForUnilevelBonus()` - Combined check: network_active AND quota met
6. (No changes to existing methods)

---

## Backup Procedures

### 1. Database Backup

**Via SSH (mysqldump):**
```bash
# Navigate to your project directory
cd /path/to/your/project

# Create backup directory if not exists
mkdir -p backups

# Backup database (replace DB credentials)
mysqldump -u YOUR_DB_USER -p YOUR_DB_NAME > backups/backup_before_phase1_$(date +%Y%m%d_%H%M%S).sql

# Verify backup was created
ls -lh backups/
```

**Via cPanel/phpMyAdmin:**
1. Login to cPanel
2. Open phpMyAdmin
3. Select your database
4. Click "Export" tab
5. Choose "Quick" export method
6. Click "Go"
7. Save the `.sql` file with name: `backup_before_phase1_YYYYMMDD.sql`

### 2. Files Backup

**Backup modified files only:**
```bash
# Create backup directory
mkdir -p backups/phase1_files_backup

# Backup modified files (adjust paths as needed)
cp app/Models/Product.php backups/phase1_files_backup/
cp app/Models/Package.php backups/phase1_files_backup/
cp app/Models/User.php backups/phase1_files_backup/
cp app/Http/Controllers/Admin/AdminProductController.php backups/phase1_files_backup/
cp resources/views/admin/products/edit.blade.php backups/phase1_files_backup/
cp resources/views/admin/products/create.blade.php backups/phase1_files_backup/

# Create archive
tar -czf backups/phase1_files_backup_$(date +%Y%m%d_%H%M%S).tar.gz backups/phase1_files_backup/
```

---

## Deployment Steps

### Step 1: Upload Files to Live Server

**Method A: Via FTP/SFTP (FileZilla, WinSCP, etc.)**

Upload the following files from your localhost to the exact same paths on live server:

**New Files:**
```
database/migrations/2025_11_16_090015_modify_points_awarded_to_decimal_in_products_table.php
database/migrations/2025_11_16_090020_add_monthly_quota_to_packages_table.php
database/migrations/2025_11_16_090022_create_monthly_quota_tracker_table.php
app/Models/MonthlyQuotaTracker.php
```

**Modified Files:**
```
app/Models/Product.php
app/Models/Package.php
app/Models/User.php
app/Http/Controllers/Admin/AdminProductController.php
resources/views/admin/products/edit.blade.php
resources/views/admin/products/create.blade.php
```

**Optional Test Scripts (for verification after deployment):**
```
test_phase1_monthly_quota.php
test_product_decimal_pv.php
```

**Method B: Via Git (if using version control)**

```bash
# On your local machine, commit changes
git add .
git commit -m "Phase 1: Unilevel Monthly Quota System - Database & Models

- Modified products.points_awarded to decimal(10,2)
- Added monthly_quota_points and enforce_monthly_quota to packages
- Created monthly_quota_tracker table
- Added MonthlyQuotaTracker model
- Updated User model with quota tracking methods
- Updated product forms to support decimal PV values"

# Push to repository
git push origin main

# SSH into live server
ssh user@your-server.com

# Navigate to project directory
cd /path/to/your/project

# Pull latest changes
git pull origin main
```

### Step 2: Set Proper File Permissions

```bash
# SSH into your server
ssh user@your-server.com
cd /path/to/your/project

# Set permissions for new files
chmod 644 database/migrations/2025_11_16_*.php
chmod 644 app/Models/MonthlyQuotaTracker.php

# Ensure Laravel can write to storage and cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
# (Replace www-data with your web server user if different)
```

### Step 3: Clear Laravel Caches

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild optimized class loader
composer dump-autoload
```

---

## Database Migration

### Step 4: Run Migrations

**IMPORTANT:** This step modifies your database structure. Ensure backup is complete before proceeding.

```bash
# SSH into your server
ssh user@your-server.com
cd /path/to/your/project

# Check migration status (see what will be run)
php artisan migrate:status

# Run migrations
php artisan migrate

# Expected output:
# INFO  Running migrations.
# 
# 2025_11_16_090015_modify_points_awarded_to_decimal_in_products_table .... DONE
# 2025_11_16_090020_add_monthly_quota_to_packages_table .................. DONE
# 2025_11_16_090022_create_monthly_quota_tracker_table ................... DONE
```

**If using Laravel Forge or Envoyer:**
- Navigate to your site's deployment section
- Enable "Run Migrations" option
- Deploy the changes

**What the migrations do:**

1. **Migration 1:** Modifies `products.points_awarded` column
   - Changes from: `integer`
   - Changes to: `decimal(10, 2)`
   - Action: Existing integer values are preserved (e.g., 10 becomes 10.00)
   - Risk: **Low** - Non-destructive column type change

2. **Migration 2:** Adds columns to `packages` table
   - Adds: `monthly_quota_points` (decimal 10,2, default 0)
   - Adds: `enforce_monthly_quota` (boolean, default false)
   - Action: Adds new columns, existing data untouched
   - Risk: **Low** - Only adding columns, no data modification

3. **Migration 3:** Creates new `monthly_quota_tracker` table
   - Columns: id, user_id, year, month, total_pv_points, required_quota, quota_met, last_purchase_at, timestamps
   - Indexes: Composite unique on (user_id, year, month)
   - Action: Creates new table, no existing data affected
   - Risk: **None** - New table creation

---

## Verification & Testing

### Step 5: Verify Database Changes

```bash
# Option 1: Using MySQL CLI
mysql -u YOUR_DB_USER -p YOUR_DB_NAME

# Check products table
DESCRIBE products;
# Verify: points_awarded is now decimal(10,2)

# Check packages table
DESCRIBE packages;
# Verify: monthly_quota_points (decimal) and enforce_monthly_quota (tinyint) exist

# Check new table
DESCRIBE monthly_quota_tracker;
# Verify: Table exists with all expected columns

# Check indexes
SHOW INDEX FROM monthly_quota_tracker;
# Verify: user_month_unique composite index exists

# Exit MySQL
EXIT;
```

**Expected Results:**

**products.points_awarded:**
```
Field            | Type          | Null | Default
points_awarded   | decimal(10,2) | NO   | 0.00
```

**packages (new columns):**
```
Field                    | Type          | Null | Default
monthly_quota_points     | decimal(10,2) | NO   | 0.00
enforce_monthly_quota    | tinyint(1)    | NO   | 0
```

**monthly_quota_tracker table:**
```
Field              | Type          | Null | Key
id                 | bigint(20)    | NO   | PRI
user_id            | bigint(20)    | NO   | MUL
year               | int(11)       | NO   | MUL
month              | int(11)       | NO   | 
total_pv_points    | decimal(10,2) | NO   | 
required_quota     | decimal(10,2) | NO   | 
quota_met          | tinyint(1)    | NO   | MUL
last_purchase_at   | timestamp     | YES  | 
created_at         | timestamp     | YES  | 
updated_at         | timestamp     | YES  | 
```

### Step 6: Test via Application UI

**Test 1: Product Edit Form**

1. Login as admin
2. Navigate to: `https://your-domain.com/admin/products`
3. Click "Edit" on any product
4. Verify "Points Awarded (PV)" field:
   - Should allow decimal input (e.g., 10.75, 25.50)
   - Should show helper text: "Personal Volume points for monthly quota"
5. Enter a decimal value like `15.75`
6. Click "Update Product"
7. Verify: Value is saved correctly (check database or re-open edit form)

**Test 2: Product Create Form**

1. Navigate to: `https://your-domain.com/admin/products/create`
2. Fill in all required fields
3. Enter decimal PV value (e.g., `20.50`)
4. Submit form
5. Verify: Product created with correct decimal PV

### Step 7: Run Verification Script (Optional)

If you uploaded the test scripts:

```bash
# SSH into server
cd /path/to/your/project

# Run Phase 1 test
php test_phase1_monthly_quota.php

# Expected output: All tests should PASS
# Test 1: PASSED ✓
# Test 2: PASSED ✓
# Test 3: PASSED ✓
# Test 4: PASSED ✓
# Test 5a: PASSED ✓
# Test 5b: PASSED ✓
# Test 6: PASSED ✓
# Phase 1 is READY! ✓

# Run decimal PV test
php test_product_decimal_pv.php

# Expected: All decimal values should pass
```

### Step 8: Check Laravel Logs

```bash
# Check for any errors during migration
tail -n 100 storage/logs/laravel.log

# If no errors, you should see migration success logs
# If errors exist, review and address before proceeding
```

---

## Rollback Procedures

### If Something Goes Wrong

**Option 1: Rollback Migrations (Recommended)**

```bash
# Rollback the last 3 migrations
php artisan migrate:rollback --step=3

# Verify rollback
php artisan migrate:status

# Restore old files from backup
cd backups/phase1_files_backup/
cp Product.php ../../app/Models/
cp Package.php ../../app/Models/
cp User.php ../../app/Models/
cp AdminProductController.php ../../app/Http/Controllers/Admin/
# ... etc
```

**Option 2: Restore Database Backup**

```bash
# Restore database from backup
mysql -u YOUR_DB_USER -p YOUR_DB_NAME < backups/backup_before_phase1_YYYYMMDD_HHMMSS.sql

# Verify restoration
mysql -u YOUR_DB_USER -p YOUR_DB_NAME -e "DESCRIBE products;"
# Check if points_awarded is back to integer

# Restore old files from backup (same as Option 1)
```

**Option 3: Manual Rollback (if migrations fail)**

```sql
-- Connect to MySQL
mysql -u YOUR_DB_USER -p YOUR_DB_NAME

-- Rollback Migration 3 (drop table)
DROP TABLE IF EXISTS monthly_quota_tracker;

-- Rollback Migration 2 (remove columns)
ALTER TABLE packages 
  DROP COLUMN monthly_quota_points,
  DROP COLUMN enforce_monthly_quota;

-- Rollback Migration 1 (change column back)
ALTER TABLE products 
  MODIFY points_awarded INT(11) NOT NULL DEFAULT 0;

-- Remove migration records
DELETE FROM migrations 
WHERE migration IN (
  '2025_11_16_090015_modify_points_awarded_to_decimal_in_products_table',
  '2025_11_16_090020_add_monthly_quota_to_packages_table',
  '2025_11_16_090022_create_monthly_quota_tracker_table'
);

EXIT;
```

Then restore old files from backup.

---

## Troubleshooting

### Common Issues

#### Issue 1: Migration fails with "Doctrine not found"

**Cause:** Laravel needs doctrine/dbal for column modifications.

**Solution:**
```bash
composer require doctrine/dbal
php artisan migrate
```

#### Issue 2: "Column not found" error in application

**Cause:** Cache not cleared after migration.

**Solution:**
```bash
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

#### Issue 3: Decimal values not saving correctly

**Cause:** Database column still shows as integer.

**Verification:**
```sql
DESCRIBE products;
-- Check if points_awarded is decimal(10,2)
```

**Solution:** Re-run migration or manually alter column:
```sql
ALTER TABLE products 
  MODIFY points_awarded DECIMAL(10, 2) NOT NULL DEFAULT 0.00;
```

#### Issue 4: "Class MonthlyQuotaTracker not found"

**Cause:** Autoload not updated.

**Solution:**
```bash
composer dump-autoload
php artisan config:clear
```

#### Issue 5: Validation error "points_awarded must be integer"

**Cause:** Controller validation not updated.

**Verification:** Check `AdminProductController.php` line 73 and 129:
```php
// Should be:
'points_awarded' => 'required|numeric|min:0|max:9999.99',

// NOT:
'points_awarded' => 'required|integer|min:0',
```

**Solution:** Re-upload corrected controller file.

---

## Post-Deployment Tasks

### Step 9: Update Existing Data (Optional)

If you want to set initial quota values for existing packages:

```bash
# SSH into server
cd /path/to/your/project

# Run tinker
php artisan tinker
```

```php
// Set quota for specific package (example)
$package = App\Models\Package::where('name', 'Starter')->first();
$package->monthly_quota_points = 100.00;
$package->enforce_monthly_quota = true;
$package->save();

// Or bulk update all MLM packages
App\Models\Package::where('is_mlm_package', true)->update([
    'monthly_quota_points' => 100.00,
    'enforce_monthly_quota' => false // Start with disabled, enable later
]);

exit
```

### Step 10: Disable Maintenance Mode

```bash
php artisan up
```

### Step 11: Monitor Application

- Check error logs: `tail -f storage/logs/laravel.log`
- Monitor user activity for any issues
- Test product creation/editing as admin
- Verify no broken functionality

---

## Phase 1 Completion Checklist

Verify all items before proceeding to Phase 2:

- [ ] All files uploaded successfully
- [ ] All 3 migrations ran successfully (`php artisan migrate:status`)
- [ ] Database columns verified (products, packages, monthly_quota_tracker)
- [ ] Product edit form accepts decimal PV values
- [ ] Product create form accepts decimal PV values
- [ ] No errors in `storage/logs/laravel.log`
- [ ] Application is responsive (maintenance mode disabled)
- [ ] Backup files are safely stored
- [ ] Test scripts ran successfully (optional)
- [ ] Existing functionality not broken (orders, users, products work normally)

---

## Phase 2 Preparation

### What's Coming in Phase 2

Phase 2 will implement the **MonthlyQuotaService** and integrate it with the checkout process to automatically track PV points when users purchase products.

**Phase 2 Components:**

1. `app/Services/MonthlyQuotaService.php` - Core service for PV tracking
2. Integration with `CheckoutController.php` - Process PV after order completion
3. Background job for quota processing (optional)
4. Admin API endpoints for quota management
5. Testing scripts

**Phase 2 Requirements:**

- Phase 1 must be 100% complete and verified
- No migrations will run in Phase 2 (only service layer additions)
- Zero downtime deployment possible

**Estimated Timeline:**
- Phase 2 Development: 2-3 hours
- Phase 2 Deployment: 15-30 minutes (file upload only, no migrations)

### Before Starting Phase 2

1. Ensure Phase 1 is stable (monitor for 24-48 hours)
2. Confirm no issues with decimal PV values in production
3. Review Phase 2 plan in `UNILEVEL_QUOTA.md`
4. Decide on quota values for each package
5. Plan user communication about new monthly quota requirements

---

## Support & Documentation

### Related Documentation

- **Full Implementation Plan:** `UNILEVEL_QUOTA.md`
- **Summary:** `UNILEVEL_QUOTA_SUMMARY.md` (if exists)
- **Changes Log:** `UNILEVEL_QUOTA_CHANGES.md` (if exists)

### Important Notes

1. **No Impact on Existing Features:** Phase 1 only adds new database fields and models. It does NOT change any existing business logic. Your current Unilevel bonus system continues to work exactly as before.

2. **Quota Enforcement is Optional:** The `enforce_monthly_quota` flag is set to `false` by default for all packages. You control when to enable quota requirements.

3. **Backward Compatibility:** All new User model methods check if quota is enforced before applying restrictions. Users without quotas are unaffected.

4. **Decimal Precision:** PV values support 2 decimal places (e.g., 10.75, 99.99). This allows flexibility in point assignment.

---

## Emergency Contacts

**Technical Issues:**
- Laravel Logs: `storage/logs/laravel.log`
- Web Server Logs: `/var/log/apache2/error.log` or `/var/log/nginx/error.log`
- MySQL Logs: `/var/log/mysql/error.log`

**Database Issues:**
- Always have recent backup before troubleshooting
- Test fixes on localhost first
- Use transactions when running manual SQL

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2025-11-16 | Initial Phase 1 installation guide |

---

## Sign-Off

**Deployed By:** ___________________  
**Date:** ___________________  
**Verified By:** ___________________  
**Production URL:** ___________________  

---

**END OF PHASE 1 INSTALLATION GUIDE**

Ready for Phase 2: YES [ ] / NO [ ]  
Issues Encountered: ___________________  
Notes: ___________________
