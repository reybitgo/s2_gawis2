# Phase 3 Troubleshooting Guide

## Quick Issue Diagnosis

```bash
# Run these commands to diagnose issues quickly
cd /home/yourusername/public_html

# 1. Check for PHP errors
tail -100 storage/logs/laravel.log | grep -i "error\|exception\|fatal"

# 2. Verify files deployed
ls -la app/Services/RankAdvancementService.php
ls -la app/Console/Commands/BackfillLegacySponsorships.php

# 3. Check Artisan command
php artisan list | grep rank

# 4. Test database connection
php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';"

# 5. Check package configuration
php check_rank_packages.php
```

---

## Issue #1: "Class RankAdvancementService not found"

### Symptoms
```
PHP Fatal error: Class 'App\Services\RankAdvancementService' not found
```

### Causes
1. File not uploaded correctly
2. Autoloader not updated
3. Namespace mismatch

### Solutions

**Solution 1: Verify File Exists**
```bash
ls -la app/Services/RankAdvancementService.php

# If not found, upload the file:
# - Via FTP: Upload RankAdvancementService.php to app/Services/
# - Via Git: git pull origin main
```

**Solution 2: Update Autoloader**
```bash
composer dump-autoload --optimize
php artisan config:clear
php artisan cache:clear
```

**Solution 3: Check Namespace**
```bash
head -3 app/Services/RankAdvancementService.php

# Should show:
# <?php
# namespace App\Services;
```

**Solution 4: Clear OPcache (if enabled)**
```bash
# Ask Hostinger to clear OPcache or wait 5 minutes
# Or restart PHP-FPM if you have access
```

**Verification**:
```bash
php artisan tinker --execute="echo class_exists('App\Services\RankAdvancementService') ? 'Found' : 'Not Found';"
```

---

## Issue #2: Artisan Command Not Found

### Symptoms
```bash
php artisan rank:backfill-legacy-sponsorships
# Command "rank:backfill-legacy-sponsorships" is not defined.
```

### Causes
1. Command file not uploaded
2. Command not registered
3. Cache issue

### Solutions

**Solution 1: Verify File Exists**
```bash
ls -la app/Console/Commands/BackfillLegacySponsorships.php

# If missing, upload the file
```

**Solution 2: Clear Command Cache**
```bash
php artisan config:clear
php artisan cache:clear
php artisan clear-compiled
composer dump-autoload
```

**Solution 3: Check File Permissions**
```bash
chmod 644 app/Console/Commands/BackfillLegacySponsorships.php
```

**Solution 4: Verify Command Registration**
```bash
# Check if command is in Console/Kernel.php
cat app/Console/Kernel.php | grep BackfillLegacySponsorships

# Laravel auto-discovers commands, so this shouldn't be needed
```

**Verification**:
```bash
php artisan list | grep rank
# Should show: rank:backfill-legacy-sponsorships
```

---

## Issue #3: Rank Advancement Not Triggering

### Symptoms
- User purchases package
- Rank updates
- But sponsor doesn't advance (even with 5/5 sponsors)

### Debug Steps

**Step 1: Check User's Progress**
```bash
php artisan tinker
```

```php
// Replace 5 with actual sponsor user ID
$sponsor = App\Models\User::find(5);

echo "Username: " . $sponsor->username . "\n";
echo "Current Rank: " . $sponsor->current_rank . "\n";
echo "Rank Package ID: " . $sponsor->rank_package_id . "\n";

// Check sponsor count
$count = $sponsor->getSameRankSponsorsCount();
echo "Same-Rank Sponsors: $count\n";

// Check requirement
$package = $sponsor->rankPackage;
if ($package) {
    echo "Required: " . $package->required_direct_sponsors . "\n";
    echo "Can Advance: " . ($package->canAdvanceToNextRank() ? 'Yes' : 'No') . "\n";
    echo "Next Package ID: " . $package->next_rank_package_id . "\n";
}

// Check progress
$service = new App\Services\RankAdvancementService();
$progress = $service->getRankAdvancementProgress($sponsor);
print_r($progress);
```

**Step 2: Check Downline Ranks**
```php
// Get all downline users
$downlines = App\Models\User::where('sponsor_id', $sponsor->id)->get();

echo "Total Direct Referrals: " . $downlines->count() . "\n\n";

foreach ($downlines as $downline) {
    echo "- {$downline->username}: {$downline->current_rank}\n";
}

// Count same rank
$sameRankCount = $downlines->where('current_rank', $sponsor->current_rank)->count();
echo "\nSame Rank Count: $sameRankCount\n";
```

**Step 3: Check Package Configuration**
```bash
php check_rank_packages.php

# Verify:
# - Starter has next_rank_package_id = 2 (Newbie)
# - Newbie has next_rank_package_id = 3 (Bronze)
# - Bronze has next_rank_package_id = NULL (top rank)
```

**Step 4: Check Logs**
```bash
tail -200 storage/logs/laravel.log | grep -A 5 "Checking Rank Advancement"

# Look for:
# - "can_advance": true/false
# - "total_same_rank_sponsors": X
# - "required_sponsors": Y
```

**Step 5: Manual Trigger Test**
```bash
php artisan tinker
```

```php
$sponsor = App\Models\User::find(5);
$service = new App\Services\RankAdvancementService();

// Manually check and trigger
$result = $service->checkAndTriggerAdvancement($sponsor);

if ($result) {
    echo "Advancement triggered!\n";
    echo "New rank: " . $sponsor->fresh()->current_rank . "\n";
} else {
    echo "Advancement not triggered.\n";
    echo "Check logs for details.\n";
}
```

### Common Causes

**Cause 1: Downlines Don't Have Ranks**
```php
// Check if downlines have ranks set
$downlines = App\Models\User::where('sponsor_id', 5)->get();
$withoutRank = $downlines->whereNull('current_rank');
echo "Downlines without rank: " . $withoutRank->count() . "\n";

// Solution: Run rank assignment
foreach ($withoutRank as $user) {
    $user->updateRank();
}
```

**Cause 2: Package Not Configured**
```sql
-- Check package config
SELECT id, name, rank_name, rank_order, required_direct_sponsors, next_rank_package_id
FROM packages
WHERE is_rankable = 1
ORDER BY rank_order;

-- If next_rank_package_id is NULL for non-top-rank:
UPDATE packages SET next_rank_package_id = 2 WHERE id = 1; -- Starter → Newbie
UPDATE packages SET next_rank_package_id = 3 WHERE id = 2; -- Newbie → Bronze
```

**Cause 3: Network Status Not Active**
```php
$sponsor = App\Models\User::find(5);
echo "Network Status: " . $sponsor->network_status . "\n";

// If not active:
$sponsor->activateNetwork();
```

---

## Issue #4: Database Errors During Order Creation

### Symptoms
```
SQLSTATE[HY000]: General error: 1364 Field 'total_amount' doesn't have a default value
SQLSTATE[HY000]: General error: 1364 Field 'unit_price' doesn't have a default value
```

### Causes
- Missing required fields in Order or OrderItem creation

### Solutions

**Solution 1: Verify RankAdvancementService Has All Fields**
```bash
grep -A 10 "Order::create" app/Services/RankAdvancementService.php

# Should include:
# 'total_amount' => $package->price,
# 'subtotal' => $package->price,
# 'grand_total' => $package->price,
```

```bash
grep -A 10 "OrderItem::create" app/Services/RankAdvancementService.php

# Should include:
# 'item_type' => 'package',
# 'unit_price' => $package->price,
# 'total_price' => $package->price,
```

**Solution 2: Check Database Schema**
```sql
DESCRIBE orders;
DESCRIBE order_items;

-- Verify fields exist and their default values
```

**Solution 3: Update Code if Missing Fields**

If fields are missing from `RankAdvancementService.php`, update the `createSystemFundedOrder()` method:

```php
// Order creation should have:
Order::create([
    'user_id' => $user->id,
    'order_number' => 'RANK-' . strtoupper(uniqid()),
    'status' => 'confirmed',
    'payment_status' => 'paid',
    'payment_method' => 'system_reward',
    'total_amount' => $package->price,    // REQUIRED
    'subtotal' => $package->price,
    'grand_total' => $package->price,
    'notes' => "System-funded rank advancement reward: {$package->rank_name}",
]);

// OrderItem creation should have:
OrderItem::create([
    'order_id' => $order->id,
    'item_type' => 'package',              // REQUIRED
    'package_id' => $package->id,
    'product_id' => null,
    'quantity' => 1,
    'unit_price' => $package->price,       // REQUIRED
    'total_price' => $package->price,      // REQUIRED
]);
```

Then clear cache:
```bash
php artisan config:clear
php artisan cache:clear
```

---

## Issue #5: Legacy Referrals Not Counted

### Symptoms
- User has existing referrals before Phase 3
- `getSameRankSponsorsCount()` returns 0 or wrong count

### Debug Steps

**Step 1: Verify Sponsor-Referral Relationships**
```sql
-- Check direct referrals
SELECT 
    u2.id as referral_id,
    u2.username,
    u2.current_rank,
    u2.sponsor_id
FROM users u1
JOIN users u2 ON u2.sponsor_id = u1.id
WHERE u1.id = 5  -- Replace with sponsor ID
ORDER BY u2.created_at;
```

**Step 2: Check Tracked Sponsorships**
```sql
-- Check what's already tracked
SELECT 
    dst.sponsored_user_id,
    u.username,
    dst.sponsored_user_rank_at_time,
    dst.counted_for_rank
FROM direct_sponsors_tracker dst
JOIN users u ON dst.sponsored_user_id = u.id
WHERE dst.user_id = 5  -- Replace with sponsor ID
ORDER BY dst.sponsored_at;
```

**Step 3: Test Count Method**
```bash
php artisan tinker
```

```php
$sponsor = App\Models\User::find(5);

// Count tracked
$trackedCount = $sponsor->directSponsorsTracked()
    ->where('counted_for_rank', $sponsor->current_rank)
    ->count();
echo "Tracked: $trackedCount\n";

// Count legacy
$legacyCount = App\Models\User::where('sponsor_id', $sponsor->id)
    ->where('current_rank', $sponsor->current_rank)
    ->whereNotIn('id', function($query) use ($sponsor) {
        $query->select('sponsored_user_id')
              ->from('direct_sponsors_tracker')
              ->where('user_id', $sponsor->id);
    })
    ->count();
echo "Legacy: $legacyCount\n";

// Total
$total = $sponsor->getSameRankSponsorsCount();
echo "Total: $total\n";
```

### Common Causes

**Cause 1: Referrals Don't Have Ranks**
```php
// Check referrals without ranks
$referrals = App\Models\User::where('sponsor_id', 5)
    ->whereNull('current_rank')
    ->get();

echo "Referrals without rank: " . $referrals->count() . "\n";

// Fix: Assign ranks
foreach ($referrals as $user) {
    $user->updateRank();
    echo "Updated {$user->username} to {$user->fresh()->current_rank}\n";
}
```

**Cause 2: Rank Mismatch**
```php
$sponsor = App\Models\User::find(5);
echo "Sponsor rank: {$sponsor->current_rank}\n\n";

$referrals = App\Models\User::where('sponsor_id', $sponsor->id)->get();
foreach ($referrals as $ref) {
    echo "- {$ref->username}: {$ref->current_rank}";
    if ($ref->current_rank === $sponsor->current_rank) {
        echo " ✓ MATCH";
    }
    echo "\n";
}
```

**Cause 3: Sponsor ID Not Set**
```sql
-- Check for orphaned users
SELECT id, username, sponsor_id 
FROM users 
WHERE sponsor_id IS NULL OR sponsor_id = 0
LIMIT 20;
```

---

## Issue #6: System-Funded Orders Show ₱0.00

### Symptoms
- Rank advancement happens
- Order created
- But `grand_total` shows 0.00 in some views

### Investigation

```sql
-- Check order details
SELECT 
    id,
    order_number,
    user_id,
    total_amount,
    subtotal,
    grand_total,
    payment_method,
    notes
FROM orders
WHERE payment_method = 'system_reward'
ORDER BY created_at DESC
LIMIT 10;

-- Check corresponding rank advancements
SELECT 
    ra.id,
    ra.user_id,
    ra.to_rank,
    ra.system_paid_amount,
    ra.order_id,
    o.grand_total as order_grand_total
FROM rank_advancements ra
LEFT JOIN orders o ON ra.order_id = o.id
WHERE ra.advancement_type = 'sponsorship_reward'
ORDER BY ra.created_at DESC
LIMIT 10;
```

### Analysis

**If `rank_advancements.system_paid_amount` is correct but `orders.grand_total` is 0**:
- This is a **cosmetic issue** in order creation
- Actual cost is tracked correctly in `rank_advancements` table
- MLM calculations are NOT affected

**If both are 0**:
- Bug in `createSystemFundedOrder()` method
- Check `RankAdvancementService.php` line ~270

### Solution

If orders show 0.00, update `RankAdvancementService.php`:

```php
// Ensure all amount fields are set
$order = Order::create([
    'user_id' => $user->id,
    'order_number' => 'RANK-' . strtoupper(uniqid()),
    'status' => 'confirmed',
    'payment_status' => 'paid',
    'payment_method' => 'system_reward',
    'total_amount' => $package->price,     // Must be set
    'subtotal' => $package->price,          // Must be set
    'grand_total' => $package->price,       // Must be set
    'notes' => "System-funded rank advancement reward: {$package->rank_name}",
]);
```

Then clear cache and test.

---

## Issue #7: Backfill Command Takes Too Long

### Symptoms
```bash
php artisan rank:backfill-legacy-sponsorships --check-advancements
# Runs for hours...
```

### Causes
- Large number of users
- Checking advancements for all is slow

### Solutions

**Solution 1: Run Without Advancement Check**
```bash
# Backfill only (faster)
php artisan rank:backfill-legacy-sponsorships

# Check advancements separately later
```

**Solution 2: Chunk Processing**

The command already uses chunking (100 users per batch), but you can adjust:

Edit `app/Console/Commands/BackfillLegacySponsorships.php`:

```php
// Change from:
User::whereNotNull('sponsor_id')->chunk(100, function($users) {

// To larger chunk size (if server can handle):
User::whereNotNull('sponsor_id')->chunk(500, function($users) {
```

**Solution 3: Run in Background (tmux/screen)**

```bash
# Start tmux session
tmux new -s backfill

# Run command
php artisan rank:backfill-legacy-sponsorships --check-advancements

# Detach: Ctrl+B then D
# Re-attach later: tmux attach -t backfill
```

**Solution 4: Monitor Progress**

```bash
# Watch progress in real-time (separate terminal)
watch -n 2 "mysql -u USER -p'PASS' DB -e 'SELECT COUNT(*) as tracked FROM direct_sponsors_tracker'"
```

---

## Issue #8: Permission Denied Errors

### Symptoms
```
file_put_contents(storage/logs/laravel.log): failed to open stream: Permission denied
```

### Solutions

```bash
cd /home/yourusername/public_html

# Fix storage permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Fix ownership (if needed)
chown -R yourusername:yourusername storage bootstrap/cache

# Verify
ls -la storage/
ls -la bootstrap/cache/
```

---

## Issue #9: High CPU/Memory Usage

### Symptoms
- Server slow after deployment
- High CPU usage
- Memory warnings

### Investigation

**Check query performance**:
```bash
php artisan tinker
```

```php
DB::enableQueryLog();

// Simulate rank check
$user = App\Models\User::find(5);
$count = $user->getSameRankSponsorsCount();

// View queries
$queries = DB::getQueryLog();
foreach ($queries as $query) {
    echo $query['query'] . "\n";
    echo "Time: " . $query['time'] . "ms\n\n";
}
```

**Check database indexes**:
```sql
SHOW INDEX FROM direct_sponsors_tracker;
SHOW INDEX FROM users;
SHOW INDEX FROM packages;
```

### Solutions

**Solution 1: Ensure Indexes Exist**
```sql
-- Add if missing
ALTER TABLE direct_sponsors_tracker 
ADD INDEX idx_user_rank (user_id, counted_for_rank);

ALTER TABLE users 
ADD INDEX idx_sponsor_rank (sponsor_id, current_rank);
```

**Solution 2: Optimize Queries**

If `getSameRankSponsorsCount()` is slow, consider caching:

```php
// In User model, add cache
public function getSameRankSponsorsCount(): int
{
    $cacheKey = "user_{$this->id}_rank_sponsors_count";
    
    return Cache::remember($cacheKey, 300, function() {
        // Existing logic...
    });
}

// Clear cache when sponsorship tracked
// In RankAdvancementService.trackSponsorship():
Cache::forget("user_{$sponsor->id}_rank_sponsors_count");
```

**Solution 3: Monitor Slow Queries**
```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;

-- Check after 1 hour
-- (ask Hostinger for slow query log location)
```

---

## Issue #10: Git Pull Conflicts

### Symptoms
```bash
git pull origin main
# error: Your local changes would be overwritten by merge
```

### Solutions

**Solution 1: Stash Local Changes**
```bash
git stash
git pull origin main
git stash pop

# Resolve any conflicts
```

**Solution 2: Hard Reset (⚠️ Loses local changes)**
```bash
# Backup first
cp -r public_html public_html_backup

# Reset
cd public_html
git fetch origin
git reset --hard origin/main
```

**Solution 3: Check What Would Be Overwritten**
```bash
git fetch origin
git diff HEAD origin/main --name-only

# Review files, then decide stash or reset
```

---

## Emergency Rollback Commands

```bash
# FULL ROLLBACK (code + database)

# 1. Code rollback
cd /home/yourusername/public_html
php artisan down
git reset --hard <previous_commit>
composer install --no-dev --optimize-autoloader
php artisan config:clear && php artisan cache:clear
php artisan up

# 2. Database rollback (ONLY if corrupted)
cd /home/yourusername
mysql -u USER -p DB_NAME < backups/backup_before_phase3_*.sql

# 3. Verify rollback
php artisan about
tail -50 public_html/storage/logs/laravel.log
```

---

## Getting Help

**Priority 1: Check Logs**
```bash
tail -200 storage/logs/laravel.log
```

**Priority 2: Search This Guide**
- Use Ctrl+F to search for error message
- Check similar symptoms

**Priority 3: Run Diagnostics**
```bash
# Quick health check
php artisan about
php artisan route:list | grep rank
php artisan tinker --execute="echo 'DB: OK';"
```

**Priority 4: Contact Support**
- Hostinger Support: Server/infrastructure issues
- Development Team: Code/logic issues

---

## Prevention Checklist

✅ **Before Every Deployment**:
- Backup database
- Backup code
- Deploy to staging first (if available)
- Test in off-peak hours

✅ **After Every Deployment**:
- Check logs for errors
- Verify files deployed correctly
- Run verification commands
- Monitor for 24 hours

✅ **Regular Maintenance**:
- Review logs weekly
- Check query performance monthly
- Update indexes if needed
- Monitor system costs

---

**Last Updated**: November 30, 2025
**Covers**: Phase 3 Deployment Issues
