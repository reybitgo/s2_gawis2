# 🚀 Rank Phase 1 Deployment - Quick Checklist

## Print This and Check Off During Deployment

---

## ✅ PRE-DEPLOYMENT (Day Before)

-   [ ] **Full database backup completed**

    -   File: `gawis2_backup_YYYYMMDD_HHMM_before_rank.sql`
    -   Size: **\_\_\_** MB
    -   Location: **\*\***\_\_\_\_**\*\***

-   [ ] **Application files backup completed**

    -   File: `gawis2_files_YYYYMMDD_HHMM_before_rank.zip`
    -   Size: **\_\_\_** MB
    -   Location: **\*\***\_\_\_\_**\*\***

-   [ ] **Tested in local environment**

    -   All 30 tests passed: YES / NO
    -   Command: `php verify_phase1_complete.php`

-   [ ] **Deployment window scheduled**

    -   Date: **\*\***\_\_\_\_**\*\***
    -   Time: **\*\***\_\_\_\_**\*\*** (low traffic period)
    -   Duration: 30-45 minutes

-   [ ] **Team notified**
    -   Developers: ✅
    -   Support team: ✅
    -   Stakeholders: ✅

---

## 🔧 DEPLOYMENT DAY

### Step 1: Pre-Deployment (5 min)

-   [ ] Enable maintenance mode
    ```bash
    php artisan down --secret="123" --retry=60
    ```
-   [ ] Take final live backup
-   [ ] Note current stats:
    -   Users: **\_\_\_**
    -   Packages: **\_\_\_**
    -   Orders: **\_\_\_**

### Step 2: Upload Files (5 min)

-   [ ] Upload Model files

    -   `app/Models/User.php`
    -   `app/Models/Package.php`
    -   `app/Models/RankAdvancement.php` (NEW)
    -   `app/Models/DirectSponsorsTracker.php` (NEW)

-   [ ] Upload Migration files

    -   `database/migrations/2025_11_27_141155_*.php`
    -   `database/migrations/2025_11_27_141211_*.php`
    -   `database/migrations/2025_11_27_141213_*.php`
    -   `database/migrations/2025_11_27_141215_*.php`

-   [ ] Upload Seeder files

    -   `database/seeders/PackageSeeder.php`
    -   `database/seeders/DatabaseSeeder.php`

-   [ ] Upload View file

    -   `resources/views/admin/packages/edit.blade.php`

-   [ ] Upload Controller file
    -   `app/Http/Controllers/Admin/AdminPackageController.php`

### Step 3: Run Migrations (5 min)

-   [ ] Clear caches first

    ```bash
    php artisan config:clear
    php artisan cache:clear
    php artisan view:clear
    ```

-   [ ] Run migrations

    ```bash
    php artisan migrate --force
    ```

-   [ ] Verify all 4 migrations completed
    -   `add_rank_fields_to_users_table` ✅
    -   `add_rank_fields_to_packages_table` ✅
    -   `create_rank_advancements_table` ✅
    -   `create_direct_sponsors_tracker_table` ✅

### Step 4: Seed Rank Packages (3 min)

-   [ ] Check if rank packages already exist

    ```bash
    php artisan tinker
    >>> \App\Models\Package::where('rank_name', 'Starter')->exists()
    ```

-   [ ] If false, run seeder:

    ```bash
    php artisan db:seed --class=PackageSeeder --force
    ```

-   [ ] Verify 3 packages created:
    -   Starter (₱1,000) ✅
    -   Newbie (₱2,500) ✅
    -   Bronze (₱5,000) ✅

### Step 5: Quick Verification (2 min)

```bash
php artisan tinker
```

-   [ ] Tables exist

    ```php
    echo \Schema::hasTable('rank_advancements') ? "✅\n" : "❌\n";
    echo \Schema::hasTable('direct_sponsors_tracker') ? "✅\n" : "❌\n";
    ```

-   [ ] Columns exist

    ```php
    echo \Schema::hasColumn('users', 'current_rank') ? "✅\n" : "❌\n";
    echo \Schema::hasColumn('packages', 'rank_name') ? "✅\n" : "❌\n";
    ```

-   [ ] Data integrity
    ```php
    echo "Users: " . \App\Models\User::count() . "\n";
    echo "Packages: " . \App\Models\Package::count() . "\n";
    echo "Users with ranks: " . \App\Models\User::whereNotNull('current_rank')->count() . "\n";
    ```

### Step 6: Clear Caches (1 min)

-   [ ] Clear all caches

    ```bash
    php artisan config:clear
    php artisan cache:clear
    php artisan view:clear
    php artisan route:clear
    ```

-   [ ] Optimize for production
    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

### Step 7: Disable Maintenance Mode (immediate)

-   [ ] Bring site back online
    ```bash
    php artisan up
    ```

---

## ✅ POST-DEPLOYMENT CHECKS (10 min)

### Immediate Verification

-   [ ] Homepage loads: `https://your-domain.com`
-   [ ] User login works
-   [ ] Admin dashboard loads
-   [ ] No errors in logs:
    ```bash
    tail -50 storage/logs/laravel.log
    ```

### Feature Tests

-   [ ] Admin → Packages → Edit "Starter Package"

    -   Name field is readonly: YES / NO
    -   Lock message shows: YES / NO

-   [ ] Test new purchase
    -   Create test user
    -   Purchase Starter package
    -   User gets "Starter" rank: YES / NO

### Data Verification (via phpMyAdmin)

```sql
-- User count unchanged
SELECT COUNT(*) FROM users;
-- Result: _______ (should match pre-deployment)

-- Users with ranks
SELECT COUNT(*) FROM users WHERE current_rank IS NOT NULL;
-- Result: _______

-- Rank packages
SELECT rank_name, rank_order FROM packages WHERE rank_name IS NOT NULL;
-- Should show: Starter(1), Newbie(2), Bronze(3)
```

---

## 📊 DEPLOYMENT SUMMARY

**Deployment Date:** **\*\***\_\_\_\_**\*\***  
**Start Time:** **\*\***\_\_\_\_**\*\***  
**End Time:** **\*\***\_\_\_\_**\*\***  
**Total Duration:** **\_\_\_** minutes

**Issues Encountered:**

-   [ ] None
-   [ ] Minor issues (list below)
-   [ ] Major issues (see rollback section)

**Issue Details:**

```
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________
```

**Resolution:**

```
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________
```

**Final Status:**

-   [ ] ✅ Successful - All systems operational
-   [ ] ⚠️ Partial - Some issues, monitoring
-   [ ] ❌ Failed - Rolled back

**Deployed By:** **\*\***\_\_\_\_**\*\***  
**Verified By:** **\*\***\_\_\_\_**\*\***  
**Approved By:** **\*\***\_\_\_\_**\*\***

---

## 🚨 EMERGENCY ROLLBACK (If Needed)

### Quick Rollback (10 min)

```bash
# Enable maintenance mode
php artisan down

# Rollback migrations
php artisan migrate:rollback --step=4

# Clear caches
php artisan config:clear
php artisan cache:clear

# Test and bring back online
php artisan up
```

### Full Rollback (20 min)

```bash
# Enable maintenance mode
php artisan down

# Restore database from backup
mysql -u dbuser -p dbname < gawis2_backup_YYYYMMDD_HHMM_before_rank.sql

# Clear caches
php artisan config:clear
php artisan cache:clear

# Bring back online
php artisan up
```

---

## 📞 EMERGENCY CONTACTS

**Hostinger Support:**

-   Live Chat: Hostinger Panel → Support
-   Email: support@hostinger.com

**Development Team:**

-   Name: **\*\***\_\_\_\_**\*\***
-   Phone: **\*\***\_\_\_\_**\*\***
-   Email: **\*\***\_\_\_\_**\*\***

**Backup Location:**

-   Primary: **\*\***\_\_\_\_**\*\***
-   Secondary: **\*\***\_\_\_\_**\*\***

---

## ✅ DEPLOYMENT COMPLETE

**Signature:** **\*\***\_\_\_\_**\*\***  
**Date:** **\*\***\_\_\_\_**\*\***  
**Time:** **\*\***\_\_\_\_**\*\***

**Notes:**

```
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________
```

---

**Keep this checklist with your backups for reference.**
