# Rank System Phase 4 - Production Deployment Guide (Hostinger)

## Overview

This guide provides step-by-step instructions for deploying Phase 4 (UI Integration - Display Ranks) to your Hostinger production server.

**Deployment Time**: 15-20 minutes  
**Downtime**: ~2 minutes (during migration)  
**Risk Level**: Low (read-only UI changes + automatic advancement)

---

## Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Backup Everything](#backup-everything)
3. [Upload Files to Hostinger](#upload-files-to-hostinger)
4. [Database Migrations](#database-migrations)
5. [Migrate Legacy Users](#migrate-legacy-users)
6. [Clear Caches](#clear-caches)
7. [Verify Deployment](#verify-deployment)
8. [Post-Deployment Testing](#post-deployment-testing)
9. [Rollback Plan](#rollback-plan)
10. [Monitoring](#monitoring)

---

## Pre-Deployment Checklist

### ✅ Local Testing Complete

Ensure all Phase 4 tests have passed:

-   [ ] All 24 tests from `RANK_PHASE4_TESTING_GUIDE.md` passed
-   [ ] Profile page displays ranks correctly
-   [ ] Admin user table shows username, income, and rank
-   [ ] Wallet Information shows combined balance
-   [ ] Synchronous advancement verified
-   [ ] No console errors in any browser
-   [ ] Mobile responsive tested

### ✅ Files Ready for Deployment

Phase 4 modified files:

**Views (UI Changes):**

-   [ ] `resources/views/profile/show.blade.php` (Rank card & wallet info)
-   [ ] `resources/views/admin/users.blade.php` (Username, income, rank columns)
-   [ ] `resources/views/partials/sidebar.blade.php` (No changes, but verify if needed)

**Services (Already Deployed in Phase 3):**

-   [ ] `app/Services/RankAdvancementService.php` ✓ (from Phase 3)
-   [ ] `app/Observers/UserObserver.php` ✓ (updated for sponsorship tracking)
-   [ ] `app/Http/Controllers/CheckoutController.php` ✓ (from Phase 3)

**Models (Already Deployed in Phase 3):**

-   [ ] `app/Models/User.php` ✓ (rank methods)
-   [ ] `app/Models/Package.php` ✓ (rank methods)
-   [ ] `app/Models/Wallet.php` ✓ (balance methods)

**Commands (Optional - for troubleshooting only):**

-   [ ] `app/Console/Commands/ProcessRankAdvancements.php` (manual command)

**Migration Scripts (One-time use):**

-   [ ] `migrate_legacy_rank_data.php` (for existing users)

### ✅ Hostinger Access Ready

-   [ ] SSH access credentials ready
-   [ ] cPanel access ready
-   [ ] Database credentials ready
-   [ ] FTP/File Manager access ready

### ✅ Maintenance Mode Plan

Decide on maintenance approach:

-   **Option A**: No maintenance mode (recommended - UI changes only)
-   **Option B**: Brief maintenance mode during migration (~2 min)

---

## Backup Everything

### 1. Backup Database

**Via cPanel phpMyAdmin:**

1. Login to Hostinger cPanel
2. Open phpMyAdmin
3. Select your database (e.g., `u123456789_gawis2`)
4. Click "Export" tab
5. Choose "Quick" export method
6. Format: SQL
7. Click "Go"
8. Save file: `gawis2_backup_YYYY-MM-DD_pre_phase4.sql`

**Via SSH (Recommended):**

```bash
# SSH into Hostinger
ssh u123456789@your-server-ip

# Navigate to project root
cd domains/s2gawis2.com/public_html

# Create backup
php artisan db:backup
# Or manual mysqldump:
mysqldump -u database_user -p database_name > ~/backups/backup_phase3_ranking_$(date +%F).sql
```

### 2. Backup Files

**Via cPanel File Manager:**

1. Navigate to `public_html`
2. Select all files
3. Click "Compress"
4. Create: `backup_pre_phase4_YYYY-MM-DD.tar.gz`
5. Download to local computer

**Via SSH:**

```bash
# Create full backup
cd ~
tar -czf backup_pre_phase4_$(date +%F).tar.gz domains/s2gawis2.com/public_html

# Verify backup created
ls -lh backup_pre_phase4_*.tar.gz
```

### 3. Document Current State

**Capture current database state:**

```bash
# Via SSH
cd ~/domains/s2gawis2.com/public_html

# Check migration status
php artisan migrate:status

# Count users with ranks
php artisan tinker
>>> App\Models\User::whereNotNull('current_rank')->count()
>>> exit
```

**Take screenshots:**

-   [ ] Current admin users page
-   [ ] Current profile page (for a ranked user)
-   [ ] Current wallet information display

---

## Upload Files to Hostinger

### Method A: Via SSH & Git (Recommended)

**If your Hostinger has Git access:**

```bash
# SSH into Hostinger
ssh u123456789@your-server-ip

# Navigate to project
cd domains/s2gawis2.com/public_html

# Stash any local changes (if any)
git stash

# Pull latest changes (assuming Phase 4 is committed)
git pull origin main

# If you committed Phase 4 changes:
git fetch origin
git checkout main
git pull origin main
```

### Method B: Via FTP/File Manager

**Upload modified files only:**

1. **Connect via FTP** (FileZilla or cPanel File Manager)

2. **Upload View Files:**

    - Local: `resources/views/profile/show.blade.php`
    - Remote: `public_html/resources/views/profile/show.blade.php`

    - Local: `resources/views/admin/users.blade.php`
    - Remote: `public_html/resources/views/admin/users.blade.php`

3. **Upload UserObserver (if updated):**

    - Local: `app/Observers/UserObserver.php`
    - Remote: `public_html/app/Observers/UserObserver.php`

4. **Upload Migration Script (for legacy users):**

    - Local: `migrate_legacy_rank_data.php`
    - Remote: `public_html/migrate_legacy_rank_data.php`

5. **Upload Command (optional):**

    - Local: `app/Console/Commands/ProcessRankAdvancements.php`
    - Remote: `public_html/app/Console/Commands/ProcessRankAdvancements.php`

6. **Set Correct Permissions:**

    ```bash
    # Via SSH
    cd ~/domains/s2gawis2.com/public_html

    # Set file permissions
    find . -type f -exec chmod 644 {} \;
    find . -type d -exec chmod 755 {} \;

    # Set writable directories
    chmod -R 775 storage bootstrap/cache
    ```

---

## Database Migrations

### Phase 4 Note: No New Migrations Required

**Important:** Phase 4 is UI-only. All database migrations were completed in Phase 3.

**Verify Phase 3 migrations are applied:**

```bash
# SSH into Hostinger
ssh u123456789@your-server-ip
cd ~/domains/s2gawis2.com/public_html

# Check migration status
php artisan migrate:status
```

**Expected output - all should show [X] Ran:**

```
+------+------------------------------------------------------------+-------+
| Ran? | Migration                                                  | Batch |
+------+------------------------------------------------------------+-------+
| Yes  | 2025_11_27_000001_add_rank_fields_to_users_table           | X     |
| Yes  | 2025_11_27_000002_add_rank_fields_to_packages_table        | X     |
| Yes  | 2025_11_27_000003_create_rank_advancements_table           | X     |
| Yes  | 2025_11_27_000004_create_direct_sponsors_tracker_table     | X     |
+------+------------------------------------------------------------+-------+
```

**If any Phase 3 migrations are missing:**

```bash
# Run migrations (this should be done already in Phase 3)
php artisan migrate --force

# Verify
php artisan migrate:status
```

---

## Migrate Legacy Users

### Check for Legacy Users

**Identify users who need migration:**

```bash
# SSH into Hostinger
cd ~/domains/s2gawis2.com/public_html

# Check for users with packages but no rank
php artisan tinker
```

```php
// Count legacy users
$legacyCount = App\Models\User::whereNull('current_rank')
    ->whereHas('orders', function($q) {
        $q->where('payment_status', 'paid')
          ->whereHas('orderItems.package', function($q2) {
              $q2->where('is_rankable', true);
          });
    })
    ->count();

echo "Legacy users to migrate: {$legacyCount}\n";
exit
```

### Run Legacy Migration Script

**If legacy users exist:**

```bash
# Make script executable
chmod +x migrate_legacy_rank_data.php

# Run migration
php migrate_legacy_rank_data.php
```

**Expected Output:**

```
=== Migrating Legacy Rank Data for Phase 4 ===

Step 1: Finding legacy users with purchased packages...
Found 10 legacy users without rank data

Processing User ID: 1 (admin)...
  ✓ Updated to Starter rank (with history created)
Processing User ID: 2 (member)...
  ✓ Updated to Starter rank (with history created)
...

=== Migration Complete ===

📊 Summary:
  ✓ Users updated: 10
  ⚠ Users skipped: 0
  ✗ Errors: 0

✨ Legacy users have been migrated to Phase 4!
```

### Verify Migration

```bash
php artisan tinker
```

```php
// Check migrated users
$rankedUsers = App\Models\User::whereNotNull('current_rank')->count();
echo "Total ranked users: {$rankedUsers}\n";

// Check advancement history
$advancements = App\Models\RankAdvancement::count();
echo "Total rank advancements: {$advancements}\n";

exit
```

---

## Clear Caches

### Clear All Application Caches

```bash
# SSH into Hostinger
cd ~/domains/s2gawis2.com/public_html

# Clear all caches
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Clear OPcache (if available)

**Via cPanel:**

1. Go to "Select PHP Version"
2. Click "Extensions"
3. Toggle OPcache off then on

**Or via PHP script:**

Create temporary file: `clear_opcache.php`

```php
<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully!";
} else {
    echo "OPcache not available";
}
```

Visit: `https://yourdomain.com/clear_opcache.php`

Delete file after use.

---

## Verify Deployment

### 1. Check Application Status

```bash
# SSH into Hostinger
cd ~/domains/s2gawis2.com/public_html

# Check Laravel installation
php artisan about

# Check for any errors
tail -n 50 storage/logs/laravel.log
```

### 2. Verify Files Deployed

```bash
# Check view files
ls -l resources/views/profile/show.blade.php
ls -l resources/views/admin/users.blade.php

# Check modification dates
stat resources/views/profile/show.blade.php
```

### 3. Database Connection Test

```bash
php artisan tinker
```

```php
// Test database connection
DB::connection()->getPdo();
echo "Database connected!\n";

// Check users table
$userCount = App\Models\User::count();
echo "Total users: {$userCount}\n";

// Check packages
$packages = App\Models\Package::where('is_rankable', true)->get();
echo "Rankable packages: " . $packages->count() . "\n";

exit
```

---

## Post-Deployment Testing

### Test 1: Profile Page - Ranked User

1. **Login as a user with a rank**

    - URL: `https://yourdomain.com/login`
    - Use: admin or any ranked user credentials

2. **Navigate to Profile**

    - Click avatar → Profile
    - URL: `https://yourdomain.com/profile`

3. **Verify Rank Card Displays:**

    - [ ] "My Rank" header visible
    - [ ] Rank badge shows correct rank (Starter/Newbie/Bronze)
    - [ ] Package name shown below badge
    - [ ] "Since: [date]" displayed
    - [ ] Progress bar visible (if not top rank)
    - [ ] Progress shows correct sponsors count (X/Y)
    - [ ] "Next Rank" section displayed (if applicable)
    - [ ] No JavaScript errors in console (F12)

4. **Verify Wallet Information:**
    - [ ] Total Available Balance shows correctly
    - [ ] Breakdown: Withdrawable | Purchase displayed
    - [ ] MLM Earned shows lifetime earnings
    - [ ] Unilevel Earned shows (if applicable)

### Test 2: Admin Users Table

1. **Login as admin**

    - URL: `https://yourdomain.com/login`
    - Use: admin credentials

2. **Navigate to User Management**

    - URL: `https://yourdomain.com/admin/users`

3. **Verify Table Display:**

    - [ ] "User" column shows **username** (not fullname)
    - [ ] "Income" column shows withdrawable balance
    - [ ] Income is green/bold for users with balance
    - [ ] "Rank" column shows rank badges
    - [ ] Rank badges are color-coded (info/turquoise)
    - [ ] Package names shown below rank badges
    - [ ] "Unranked" badge for users without packages
    - [ ] Table loads without errors

4. **Verify Tooltips:**
    - [ ] Hover over rank badge
    - [ ] Tooltip shows "Rank Order: X"

### Test 3: Synchronous Advancement

**Verify automatic advancement is working:**

```bash
# SSH into Hostinger
cd ~/domains/s2gawis2.com/public_html

# Check recent advancements
php artisan tinker
```

```php
// Get recent rank advancements
$recent = App\Models\RankAdvancement::latest()->take(5)->get();

foreach ($recent as $adv) {
    echo "User {$adv->user_id}: {$adv->from_rank} → {$adv->to_rank}\n";
    echo "Type: {$adv->advancement_type}\n";
    echo "Date: {$adv->created_at}\n\n";
}

exit
```

**Expected:** Should see advancement records from legacy migration or recent purchases.

### Test 4: Real Purchase Flow (Optional but Recommended)

**Test end-to-end flow:**

1. Create a test user (or use existing):

    ```bash
    php artisan tinker
    ```

    ```php
    $sponsor = App\Models\User::where('current_rank', 'Starter')->first();
    echo "Sponsor: {$sponsor->username} (ID: {$sponsor->id})\n";
    echo "Current sponsors: {$sponsor->getSameRankSponsorsCount()}/5\n";
    exit
    ```

2. If sponsor has 4/5 sponsors, create 5th referral via registration
3. Have the new user purchase a Starter package
4. **Verify sponsor is instantly advanced:**
    - Check sponsor's profile → Should show Newbie rank
    - Check logs: `tail -f storage/logs/laravel.log | grep "Rank"`

### Test 5: Mobile Responsive

1. **Open on mobile device or DevTools (F12 → Toggle Device Toolbar)**
2. **Test Profile page:**
    - [ ] Rank card displays correctly
    - [ ] Text is readable (not too small)
    - [ ] Progress bar visible and proportional
    - [ ] No horizontal scrolling
3. **Test Admin table:**
    - [ ] Table scrolls horizontally if needed
    - [ ] All columns visible
    - [ ] Badges don't wrap awkwardly

---

## Rollback Plan

### If Critical Issues Occur

**Option 1: Restore View Files (Quick Fix)**

If only UI issues:

```bash
# SSH into Hostinger
cd ~/domains/s2gawis2.com/public_html

# Restore from Git (if using version control)
git checkout HEAD~1 resources/views/profile/show.blade.php
git checkout HEAD~1 resources/views/admin/users.blade.php

# Clear caches
php artisan view:clear
php artisan cache:clear
```

**Option 2: Full Database Rollback**

If database issues (unlikely for Phase 4):

```bash
# Restore database backup
mysql -u database_user -p database_name < ~/gawis2_backup_YYYY-MM-DD.sql

# Or via phpMyAdmin:
# 1. Login to phpMyAdmin
# 2. Select database
# 3. Click "Import"
# 4. Choose backup file
# 5. Click "Go"
```

**Option 3: Full File Rollback**

If major issues:

```bash
# Restore full backup
cd ~
tar -xzf backup_pre_phase4_YYYY-MM-DD.tar.gz
# This extracts to: domains/s2gawis2.com/public_html
```

### Rollback Verification

After rollback:

```bash
# Clear all caches
cd ~/domains/s2gawis2.com/public_html
php artisan view:clear
php artisan config:clear
php artisan cache:clear

# Verify site is accessible
curl -I https://yourdomain.com

# Check error logs
tail -n 50 storage/logs/laravel.log
```

---

## Monitoring

### Monitor for 24-48 Hours Post-Deployment

### 1. Application Logs

**Check regularly:**

```bash
# SSH into Hostinger
cd ~/domains/s2gawis2.com/public_html

# Watch logs in real-time
tail -f storage/logs/laravel.log

# Look for:
# - "Rank Advanced Successfully" (good - advancements working)
# - "Rank Advancement Failed" (investigate)
# - Any PHP errors or exceptions
```

### 2. Error Monitoring

**Watch for common issues:**

```bash
# Check for 500 errors
grep "500" storage/logs/laravel.log

# Check for database errors
grep "SQLSTATE" storage/logs/laravel.log

# Check for rank-related errors
grep -i "rank" storage/logs/laravel.log | grep -i "error"
```

### 3. User Activity Monitoring

**Track rank advancements:**

```bash
php artisan tinker
```

```php
// Check advancements in last 24 hours
$recent = App\Models\RankAdvancement::where('created_at', '>=', now()->subDay())->get();
echo "Advancements in last 24h: " . $recent->count() . "\n";

// Check for any failed advancements
// (Look for users at 100% but not advanced)
$users = App\Models\User::whereNotNull('current_rank')->get();
foreach ($users as $user) {
    $progress = app(App\Services\RankAdvancementService::class)
        ->getRankAdvancementProgress($user);

    if ($progress['is_eligible'] && $progress['can_advance']) {
        echo "⚠️  User {$user->username} is eligible but not advanced!\n";
    }
}

exit
```

### 4. Performance Monitoring

**Check page load times:**

```bash
# Test profile page
time curl -s https://yourdomain.com/profile > /dev/null

# Test admin users page
time curl -s https://yourdomain.com/admin/users > /dev/null

# Should complete in < 1 second each
```

### 5. Database Query Monitoring

**Check for slow queries:**

```bash
# Enable query log temporarily (if needed)
php artisan tinker
```

```php
DB::enableQueryLog();

// Load users page
$users = App\Models\User::with(['roles', 'wallet', 'rankPackage'])->paginate(25);

// Check queries
$queries = DB::getQueryLog();
echo "Total queries: " . count($queries) . "\n";

foreach ($queries as $query) {
    if ($query['time'] > 100) { // Queries taking > 100ms
        echo "Slow query: {$query['time']}ms\n";
        echo "{$query['query']}\n\n";
    }
}

exit
```

---

## Post-Deployment Checklist

**Mark items complete as you verify:**

### Critical Checks (Must Pass)

-   [ ] Site is accessible at main URL
-   [ ] No PHP errors on homepage
-   [ ] Users can login successfully
-   [ ] Profile page loads without errors
-   [ ] Admin users page loads without errors
-   [ ] Rank card displays correctly
-   [ ] Wallet information shows combined balance
-   [ ] Username displayed in admin table (not fullname)
-   [ ] Income column shows correct amounts
-   [ ] Rank column shows badges correctly

### Secondary Checks (Important)

-   [ ] Legacy users successfully migrated
-   [ ] All users have rank data (if they purchased packages)
-   [ ] Rank advancement history exists for migrated users
-   [ ] Mobile responsive works correctly
-   [ ] No JavaScript console errors
-   [ ] No database connection errors in logs
-   [ ] Tooltips work on hover

### Advanced Checks (Monitor Over Time)

-   [ ] Synchronous advancement triggers on purchase
-   [ ] New advancements logged correctly
-   [ ] No stuck users at 100% progress
-   [ ] Performance within acceptable thresholds
-   [ ] No memory leaks or slow queries
-   [ ] OPcache functioning correctly

---

## Troubleshooting Common Issues

### Issue 1: Rank Card Not Displaying

**Symptoms:** Profile page loads but no rank card visible

**Solutions:**

```bash
# Clear view cache
php artisan view:clear

# Check if view file exists
ls -l resources/views/profile/show.blade.php

# Check file permissions
chmod 644 resources/views/profile/show.blade.php

# Verify user has rank
php artisan tinker
>>> $user = App\Models\User::find(1);
>>> $user->current_rank
>>> $user->rankPackage
```

### Issue 2: "Class Not Found" Errors

**Symptoms:** 500 error with "Class 'App\Services\RankAdvancementService' not found"

**Solutions:**

```bash
# Regenerate autoload files
composer dump-autoload

# Clear config cache
php artisan config:clear

# Verify service file exists
ls -l app/Services/RankAdvancementService.php
```

### Issue 3: Database Connection Errors

**Symptoms:** "SQLSTATE[HY000] [2002] Connection refused"

**Solutions:**

```bash
# Check database credentials in .env
cat .env | grep DB_

# Test connection
php artisan tinker
>>> DB::connection()->getPdo();

# If fails, verify Hostinger database details:
# - DB_HOST (usually localhost)
# - DB_DATABASE (e.g., u123456789_gawis2)
# - DB_USERNAME
# - DB_PASSWORD
```

### Issue 4: Wallet Shows ₱0.00 for All Users

**Symptoms:** Income column shows ₱0.00 even for users with balance

**Solutions:**

```bash
# Check if wallet relationship loaded
php artisan tinker
>>> $user = App\Models\User::with('wallet')->first();
>>> $user->wallet
>>> $user->wallet->withdrawable_balance
>>> $user->wallet->total_balance

# If null, check column exists
>>> DB::select("SHOW COLUMNS FROM wallets LIKE 'withdrawable_balance'");
```

### Issue 5: Rank Badges Not Color-Coded

**Symptoms:** All badges are gray or same color

**Solutions:**

```bash
# Clear compiled views
php artisan view:clear

# Check CSS assets loaded
curl https://yourdomain.com/build/assets/app.css | grep "badge"

# Rebuild assets if using Vite
npm run build
```

---

## Success Criteria

**Phase 4 deployment is successful when:**

✅ All users can view their ranks in profile page  
✅ Ranked users see accurate progress towards next rank  
✅ Admin can view all users with income and rank columns  
✅ Username displayed instead of fullname in admin table  
✅ Wallet shows combined balance correctly  
✅ Legacy users migrated with proper rank data  
✅ Synchronous advancement works on package purchase  
✅ No PHP errors in production logs  
✅ Mobile responsive displays correctly  
✅ Performance is acceptable (pages load < 1 second)

---

## Next Steps After Deployment

### 1. Monitor for 24-48 Hours

-   Check logs daily: `tail -f storage/logs/laravel.log`
-   Monitor user feedback
-   Watch for any error notifications

### 2. Communicate with Users (Optional)

**Announcement template:**

> **System Update: Rank Display Now Live!**
>
> We've updated the platform with new rank display features:
>
> -   View your current rank and progress in your Profile
> -   See your earnings and rank at a glance
> -   Track your advancement towards the next rank
>
> Your rank is automatically updated when you meet requirements!

### 3. Prepare for Phase 5

If all goes well, begin planning Phase 5 (Admin Configuration Interface).

### 4. Document Any Issues

Keep a log of:

-   Any issues encountered
-   Solutions applied
-   Performance metrics
-   User feedback

---

## Support & References

### Documentation Files

-   **SYNCHRONOUS_RANK_ADVANCEMENT.md** - How instant advancement works
-   **RANK_ADVANCEMENT_SUMMARY.md** - Complete system overview
-   **LEGACY_RANK_MIGRATION.md** - Migrating existing users
-   **RANK_PHASE4_TESTING_GUIDE.md** - Testing procedures

### Verification Scripts

All located in project root:

-   `migrate_legacy_rank_data.php` - Migrate legacy users
-   `verify_test_user_roles.php` - Check user roles
-   `process_pending_rank_advancements.php` - Manual advancement processing

### Key Commands

```bash
# Check application status
php artisan about

# Check migrations
php artisan migrate:status

# View recent advancements
php artisan tinker
>>> App\Models\RankAdvancement::latest()->take(5)->get()

# Process stuck advancements (rare)
php artisan rank:process-advancements

# Check logs
tail -f storage/logs/laravel.log
```

---

## Deployment Completion Checklist

**Sign off when complete:**

-   [ ] Backups created (database + files)
-   [ ] Files uploaded/deployed successfully
-   [ ] Migrations verified (no new ones for Phase 4)
-   [ ] Legacy users migrated
-   [ ] Caches cleared
-   [ ] Profile page verified
-   [ ] Admin users table verified
-   [ ] Mobile responsive tested
-   [ ] No errors in logs
-   [ ] Synchronous advancement tested
-   [ ] Monitoring plan in place
-   [ ] Rollback plan documented
-   [ ] Success criteria met

**Deployed By:** ******\_\_\_******  
**Date:** ******\_\_\_******  
**Time:** ******\_\_\_******  
**Status:** Success / Issues Found  
**Notes:** ******\_\_\_******

---

## Emergency Contacts

**If critical issues occur:**

1. **Check logs immediately:**

    ```bash
    tail -n 100 storage/logs/laravel.log
    ```

2. **Enable maintenance mode (if needed):**

    ```bash
    php artisan down --message="Brief maintenance - back shortly"
    ```

3. **Apply hotfix or rollback**

4. **Disable maintenance mode:**
    ```bash
    php artisan up
    ```

---

**Deployment Guide Complete!** 🚀

This comprehensive guide ensures Phase 4 is safely deployed to your Hostinger production server with minimal risk and maximum confidence. The synchronous rank advancement system will provide instant promotions to your users!

**Key Achievement:** Users see their rank progression in real-time with automatic advancement when they earn it! ⚡
