# Phase 1 - Quick Command Reference

**Keep this open during deployment for easy copy-paste.**

---

## SSH Connection

```bash
ssh user@your-server.com
cd /path/to/your/laravel/project
```

---

## Pre-Deployment

### Create Backup Directory
```bash
mkdir -p backups
```

### Database Backup
```bash
# Replace with your credentials
mysqldump -u YOUR_DB_USER -p YOUR_DB_NAME > backups/backup_phase1_$(date +%Y%m%d_%H%M%S).sql

# Verify
ls -lh backups/
```

### Enable Maintenance Mode (Optional)
```bash
# Simple version (shows custom 503 maintenance page)
php artisan down --retry=60

# With secret bypass (allows you to access while in maintenance)
php artisan down --secret="bypass-token" --retry=60
# Then access via: https://your-domain.com/bypass-token
```

**What Users Will See:**
- Custom branded maintenance page (not standard Laravel page)
- Friendly message: "We're Making Things Better!"
- 60-second countdown with auto-refresh
- Animated progress bar
- "Check Status" button
- No error vibe - positive improvement message

---

## Post-Upload Commands

### Set File Permissions
```bash
# Migration files
chmod 644 database/migrations/2025_11_16_*.php

# Model file
chmod 644 app/Models/MonthlyQuotaTracker.php

# Controller and views
chmod 644 app/Http/Controllers/Admin/AdminProductController.php
chmod 644 resources/views/admin/products/*.blade.php

# Storage permissions (if needed)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Clear All Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
```

---

## Database Migration

### Check Migration Status
```bash
php artisan migrate:status
```

### Run Migrations
```bash
php artisan migrate
```

### Expected Output
```
INFO  Running migrations.

2025_11_16_090015_modify_points_awarded_to_decimal_in_products_table .... DONE
2025_11_16_090020_add_monthly_quota_to_packages_table .................. DONE
2025_11_16_090022_create_monthly_quota_tracker_table ................... DONE
```

---

## Database Verification

### Connect to MySQL
```bash
mysql -u YOUR_DB_USER -p YOUR_DB_NAME
```

### Check Products Table
```sql
DESCRIBE products;
-- Look for: points_awarded | decimal(10,2) | NO | 0.00

SELECT name, points_awarded FROM products LIMIT 5;
```

### Check Packages Table
```sql
DESCRIBE packages;
-- Look for: 
-- monthly_quota_points | decimal(10,2) | NO | 0.00
-- enforce_monthly_quota | tinyint(1) | NO | 0

SELECT name, monthly_quota_points, enforce_monthly_quota FROM packages;
```

### Check New Table
```sql
DESCRIBE monthly_quota_tracker;
-- Should show all columns

SHOW INDEX FROM monthly_quota_tracker;
-- Should show user_month_unique index

SELECT * FROM monthly_quota_tracker;
-- Should be empty initially
```

### Exit MySQL
```sql
EXIT;
```

---

## Testing

### Run Test Scripts (if uploaded)
```bash
# Full Phase 1 test
php test_phase1_monthly_quota.php

# Decimal PV test
php test_product_decimal_pv.php
```

### Quick Manual Test (Tinker)
```bash
php artisan tinker
```

```php
// Test Product decimal PV
$product = App\Models\Product::first();
$product->points_awarded = 15.75;
$product->save();
$product->fresh()->points_awarded; // Should return "15.75"

// Test Package quota fields
$package = App\Models\Package::first();
$package->monthly_quota_points = 100.50;
$package->enforce_monthly_quota = true;
$package->save();
$package->fresh(); // Check values

// Test User methods
$user = App\Models\User::first();
$user->getMonthlyQuotaRequirement(); // Should return float
$user->meetsMonthlyQuota(); // Should return boolean
$user->qualifiesForUnilevelBonus(); // Should return boolean

// Test MonthlyQuotaTracker
$tracker = App\Models\MonthlyQuotaTracker::getOrCreateForCurrentMonth($user);
$tracker; // Should show tracker object

exit
```

---

## Check Logs

### Laravel Logs
```bash
# Last 50 lines
tail -n 50 storage/logs/laravel.log

# Follow in real-time
tail -f storage/logs/laravel.log

# Search for errors
grep -i error storage/logs/laravel.log | tail -n 20
```

### Web Server Logs
```bash
# Apache
tail -n 50 /var/log/apache2/error.log

# Nginx
tail -n 50 /var/log/nginx/error.log
```

---

## Post-Deployment

### Test Maintenance Page (Optional)
```bash
# Enable maintenance mode to see custom page
php artisan down --retry=60

# Open browser and visit your site
# You should see: "We're Making Things Better!" page

# To bypass and still access (as admin)
php artisan down --secret="test123"
# Visit: https://your-domain.com/test123
```

### Disable Maintenance Mode
```bash
php artisan up
```

### Clear Caches Again
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Check Application Health
```bash
# Check if application responds
curl -I https://your-domain.com/admin/products

# Should return: HTTP/1.1 200 OK (or 302 redirect to login)
```

---

## Rollback (If Needed)

### Rollback Last 3 Migrations
```bash
php artisan migrate:rollback --step=3
```

### Check Rollback Status
```bash
php artisan migrate:status
```

### Restore Database from Backup
```bash
# Stop web server first (optional)
sudo systemctl stop apache2
# or
sudo systemctl stop nginx

# Restore
mysql -u YOUR_DB_USER -p YOUR_DB_NAME < backups/backup_phase1_YYYYMMDD_HHMMSS.sql

# Start web server
sudo systemctl start apache2
# or
sudo systemctl start nginx
```

### Restore Files from Backup
```bash
# Example for one file
cp backups/phase1_files_backup/Product.php app/Models/

# Or extract full backup archive
cd backups
tar -xzf phase1_files_backup_YYYYMMDD_HHMMSS.tar.gz
cd phase1_files_backup
cp -r * /path/to/your/project/
```

---

## Data Configuration

### Set Initial Quota Values (Optional)
```bash
php artisan tinker
```

```php
// Set quota for all MLM packages
App\Models\Package::where('is_mlm_package', true)->update([
    'monthly_quota_points' => 100.00,
    'enforce_monthly_quota' => false // Keep disabled initially
]);

// Set specific package
$starter = App\Models\Package::where('name', 'Starter')->first();
$starter->monthly_quota_points = 100.00;
$starter->enforce_monthly_quota = false;
$starter->save();

// Set PV for all products
App\Models\Product::where('is_active', true)->update([
    'points_awarded' => 10.00
]);

// Set specific product
$product = App\Models\Product::where('name', 'Biogen+')->first();
$product->points_awarded = 15.75;
$product->save();

exit
```

---

## Monitoring Commands

### Monitor Laravel Logs (Real-time)
```bash
tail -f storage/logs/laravel.log
```

### Monitor PHP Errors (if enabled)
```bash
tail -f /var/log/php-errors.log
```

### Check Disk Space
```bash
df -h
```

### Check Memory Usage
```bash
free -m
```

### Check Database Connections
```bash
mysql -u YOUR_DB_USER -p -e "SHOW PROCESSLIST;"
```

---

## Troubleshooting

### "Class MonthlyQuotaTracker not found"
```bash
composer dump-autoload
php artisan config:clear
```

### "Doctrine not found" (for migrations)
```bash
composer require doctrine/dbal
php artisan migrate
```

### "Column not found" errors
```bash
# Clear caches
php artisan config:clear
php artisan cache:clear

# Check database actually has column
mysql -u USER -p DATABASE -e "DESCRIBE products;"
```

### "Validation error" for decimal PV
```bash
# Check controller validation
grep "points_awarded" app/Http/Controllers/Admin/AdminProductController.php

# Should show: 'required|numeric|min:0|max:9999.99'
# NOT: 'required|integer|min:0'
```

### Application not responding
```bash
# Check web server status
sudo systemctl status apache2
# or
sudo systemctl status nginx

# Check PHP-FPM status (if using)
sudo systemctl status php8.1-fpm
# or
sudo systemctl status php8.2-fpm

# Restart if needed
sudo systemctl restart apache2
sudo systemctl restart php8.1-fpm
```

---

## Clean Up

### Remove Test Scripts (After Verification)
```bash
rm test_phase1_monthly_quota.php
rm test_product_decimal_pv.php
```

### Archive Backups
```bash
# Keep backups for 30 days, then delete
find backups/ -name "backup_phase1_*.sql" -mtime +30 -delete
```

---

## Quick Health Check

```bash
# One-liner to check everything
php artisan migrate:status && \
php artisan config:clear && \
php artisan cache:clear && \
tail -n 10 storage/logs/laravel.log && \
echo "Phase 1 health check complete!"
```

---

## Emergency Contacts

**Laravel Error Logs:**
```bash
tail -n 100 storage/logs/laravel.log
```

**Database Issues:**
```bash
mysql -u USER -p DATABASE
```

**File Permissions Issues:**
```bash
# Reset storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## Success Verification

Run all these to confirm success:

```bash
# 1. Check migrations
php artisan migrate:status | grep "2025_11_16"

# 2. Check logs for errors
tail -n 50 storage/logs/laravel.log | grep -i error

# 3. Test database
mysql -u USER -p DATABASE -e "SELECT COUNT(*) FROM monthly_quota_tracker;"

# 4. Test model
php artisan tinker --execute="echo App\Models\MonthlyQuotaTracker::class;"

# 5. Test application
curl -I https://your-domain.com/admin/products
```

If all return expected results → **Phase 1 is LIVE! ✓**

---

**Remember:**
- Always backup before deployment
- Test on staging first if available
- Monitor logs after deployment
- Keep rollback plan ready

---

**END OF QUICK COMMANDS**
