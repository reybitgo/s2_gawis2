# Rank System Phase 3 - Production Deployment Guide (Hostinger)

## ⚠️ CRITICAL: Phase 3 Deployment Prerequisites

This guide assumes **Phase 1 and Phase 2 have been successfully deployed** to production. Phase 3 builds upon the existing rank system infrastructure.

---

## Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Backup Strategy](#backup-strategy)
3. [Code Deployment Steps](#code-deployment-steps)
4. [Post-Deployment Verification](#post-deployment-verification)
5. [Testing in Production](#testing-in-production)
6. [Monitoring & Logging](#monitoring--logging)
7. [Rollback Procedures](#rollback-procedures)
8. [Common Issues & Solutions](#common-issues--solutions)
9. [Performance Considerations](#performance-considerations)

---

## Pre-Deployment Checklist

### ✅ Phase 1 & 2 Verification

**1. Verify Phase 1 Migrations Deployed**

Via Hostinger SSH or File Manager PHP terminal:

```bash
cd /home/yourusername/public_html
php artisan migrate:status | grep "2025_11_27"
```

**Expected Output** (ALL must show "Ran"):
```
[X] 2025_11_27_141155_add_rank_fields_to_users_table
[X] 2025_11_27_141211_add_rank_fields_to_packages_table
[X] 2025_11_27_141213_create_rank_advancements_table
[X] 2025_11_27_141215_create_direct_sponsors_tracker_table
```

**2. Verify Rank Packages Configured**

```bash
php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); echo App\Models\Package::whereNotNull('rank_name')->count() . ' rank packages found';"
```

**Expected**: `3 rank packages found` (or more)

**3. Verify RankComparisonService Exists (Phase 2)**

```bash
ls -la app/Services/RankComparisonService.php
```

**Expected**: File exists

**4. Check Current System Status**

```bash
# Check for pending migrations
php artisan migrate:status

# Check for any errors in logs
tail -50 storage/logs/laravel.log

# Verify database connection
php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB Connected';"
```

### ✅ Server Requirements

- **PHP Version**: 8.1+ ✅
- **Database**: MySQL 5.7+ or MariaDB 10.3+ ✅
- **Storage Space**: At least 500MB free ✅
- **SSH Access**: Required (or Hostinger File Manager) ✅
- **Composer**: Available ✅

### ✅ Maintenance Window Planning

**Recommended Deployment Time**:
- ⏰ **Off-peak hours** (e.g., 2 AM - 4 AM server time)
- 🕒 **Estimated Duration**: 15-30 minutes
- 👥 **Impact**: Minimal (no downtime if done correctly)

**User Communication**:
```
Dear Users,

We will be performing a system upgrade on [DATE] at [TIME].
This upgrade will add automatic rank advancement features.

Expected Duration: 15-30 minutes
Impact: Minimal to none

Thank you for your patience.
```

---

## Backup Strategy

### 1. Database Backup

**Via Hostinger phpMyAdmin**:
1. Login to phpMyAdmin
2. Select your database
3. Click "Export" tab
4. Choose "Quick" export method
5. Format: SQL
6. Click "Go"
7. Save file as: `backup_before_phase3_YYYYMMDD_HHMM.sql`

**Via SSH (Recommended)**:

```bash
cd /home/yourusername

# Create backup directory if not exists
mkdir -p backups

# Backup database
mysqldump -u your_db_user -p your_database_name > backups/backup_before_phase3_$(date +%Y%m%d_%H%M%S).sql

# Verify backup was created
ls -lh backups/backup_before_phase3_*
```

**Expected Output**: Backup file with size > 0 KB

### 2. Code Backup

**Via SSH**:

```bash
cd /home/yourusername

# Backup current codebase
tar -czf backups/code_before_phase3_$(date +%Y%m%d_%H%M%S).tar.gz public_html/

# Verify backup
ls -lh backups/code_before_phase3_*
```

**Via Hostinger File Manager**:
1. Right-click on `public_html` folder
2. Select "Compress"
3. Choose ZIP format
4. Name: `code_before_phase3_YYYYMMDD`
5. Download to local machine

### 3. Critical Files to Backup

```bash
# .env file (CRITICAL)
cp public_html/.env backups/.env.backup_$(date +%Y%m%d_%H%M%S)

# Storage logs (for comparison)
tar -czf backups/logs_before_phase3_$(date +%Y%m%d_%H%M%S).tar.gz public_html/storage/logs/
```

---

## Code Deployment Steps

### Method A: Git Deployment (Recommended)

**Step 1: Prepare Local Repository**

On your local machine:

```bash
cd C:\laragon\www\s2_gawis2

# Verify all changes are committed
git status

# Should show:
# On branch main
# nothing to commit, working tree clean

# If there are uncommitted changes, commit them:
git add app/Services/RankAdvancementService.php
git add app/Console/Commands/BackfillLegacySponsorships.php
git add app/Http/Controllers/CheckoutController.php
git add app/Models/User.php
git commit -m "Phase 3: Implement automatic rank advancement system

- Create RankAdvancementService for sponsorship tracking
- Integrate rank advancement into CheckoutController
- Add backward compatibility for legacy users
- Create Artisan command for backfilling legacy data
- Add comprehensive test scripts

Co-authored-by: factory-droid[bot] <138933559+factory-droid[bot]@users.noreply.github.com>"

# Push to repository
git push origin main
```

**Step 2: Deploy to Hostinger via SSH**

```bash
# Connect to Hostinger via SSH
ssh yourusername@yourdomain.com

# Navigate to project directory
cd /home/yourusername/public_html

# Enable maintenance mode (optional but recommended)
php artisan down --message="System upgrade in progress. We'll be back shortly!"

# Pull latest changes
git pull origin main

# Step 3: Update dependencies
composer install --no-dev --optimize-autoloader

# Step 4: Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Step 5: Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Step 6: Verify deployment
php artisan about

# Step 7: Disable maintenance mode
php artisan up
```

### Method B: Manual File Upload (Via FTP/File Manager)

**If Git is not available:**

**Step 1: Upload New Files**

Upload these files to Hostinger via FTP or File Manager:

```
app/Services/RankAdvancementService.php → /public_html/app/Services/
app/Console/Commands/BackfillLegacySponsorships.php → /public_html/app/Console/Commands/
```

**Step 2: Upload Modified Files**

```
app/Http/Controllers/CheckoutController.php → /public_html/app/Http/Controllers/
app/Models/User.php → /public_html/app/Models/
```

**Step 3: Upload Test Scripts (Optional)**

```
test_rank_advancement.php → /public_html/
test_rank_advancement_legacy_users.php → /public_html/
check_rank_packages.php → /public_html/
```

**Step 4: Clear Caches via SSH or File Manager Terminal**

```bash
cd /home/yourusername/public_html

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Post-Deployment Verification

### 1. Verify Files Deployed

```bash
cd /home/yourusername/public_html

# Check new service file exists
ls -la app/Services/RankAdvancementService.php

# Check Artisan command exists
ls -la app/Console/Commands/BackfillLegacySponsorships.php

# Check modified files updated (check timestamp)
ls -la app/Http/Controllers/CheckoutController.php
ls -la app/Models/User.php
```

**Expected**: All files should exist with recent timestamps

### 2. Verify Rank Package Configuration

```bash
php check_rank_packages.php
```

**Expected Output**:
```
=== Rank Package Configuration ===

Package: Starter
  Rank Name: Starter
  Rank Order: 1
  Price: ₱1,000.00
  Required Sponsors: 5
  Next Rank Package ID: 2
  Next Rank: Newbie
  Can Advance: Yes

Package: Professional Package
  Rank Name: Newbie
  Rank Order: 2
  Price: ₱2,500.00
  Required Sponsors: 8
  Next Rank Package ID: 3
  Next Rank: Bronze
  Can Advance: Yes

Package: Premium Package
  Rank Name: Bronze
  Rank Order: 3
  Price: ₱5,000.00
  Required Sponsors: 10
  Next Rank Package ID: NULL
  Next Rank: None (Top Rank)
  Can Advance: No
```

**If packages are not configured correctly**, see [Troubleshooting](#common-issues--solutions).

### 3. Verify Artisan Command Registered

```bash
php artisan list | grep rank
```

**Expected Output**:
```
rank:backfill-legacy-sponsorships    Backfill existing sponsor_id relationships into direct_sponsors_tracker table
```

### 4. Check for Errors

```bash
# Check Laravel logs
tail -50 storage/logs/laravel.log

# Should not show any PHP errors or exceptions
```

### 5. Verify Database Connectivity

```bash
php artisan tinker --execute="echo App\Models\DirectSponsorsTracker::count() . ' sponsorships tracked';"
```

**Expected**: Number (0 or more) with no errors

---

## Testing in Production

### Test 1: Dry Run Legacy Backfill (Non-Destructive)

```bash
php artisan rank:backfill-legacy-sponsorships --dry-run
```

**Expected Output**:
```
DRY RUN MODE - No changes will be made
Starting legacy sponsorship backfill...

Would backfill: Sponsor #5 (john_doe) → User #10 (jane_smith, Rank: Starter)
Would backfill: Sponsor #5 (john_doe) → User #11 (bob_jones, Rank: Starter)
...

=== Backfill Summary ===
Total backfilled: 25
Total skipped: 10
```

**Analysis**:
- **Total backfilled**: Legacy referrals that will be tracked
- **Total skipped**: Already tracked or invalid

**If numbers look correct**, proceed to actual backfill.

### Test 2: Backfill Legacy Sponsorships (Optional)

**⚠️ IMPORTANT**: Only run this if you want to immediately backfill ALL legacy data. Otherwise, the system will backfill gradually as users advance.

```bash
# Backfill without checking for immediate advancements
php artisan rank:backfill-legacy-sponsorships

# OR backfill and check for immediate advancements
php artisan rank:backfill-legacy-sponsorships --check-advancements
```

**Expected Output**:
```
Starting legacy sponsorship backfill...
Backfilled 50 legacy sponsorships...
Backfilled 100 legacy sponsorships...

=== Backfill Summary ===
Total backfilled: 147
Total skipped: 23

=== Checking for Rank Advancements ===
Checking 45 sponsors...
[Progress bar]
✓ Sponsor #5 (john_doe) advanced to Newbie!
✓ Sponsor #12 (jane_smith) advanced to Newbie!

Total automatic advancements triggered: 2

✓ Backfill completed successfully!
```

**What Happens**:
- All existing referrals are tracked in `direct_sponsors_tracker`
- Users who meet advancement criteria are immediately advanced
- System-funded orders created for qualified users
- Rank advancements logged

### Test 3: Verify Rank Advancement Service

**Create a test order** (via admin or test account):

1. Login as admin
2. Create a test user (or use existing)
3. Purchase a Starter package for the test user
4. Check logs for rank update:

```bash
tail -f storage/logs/laravel.log | grep "Rank"
```

**Expected Log Output**:
```
[timestamp] local.INFO: User rank updated after package purchase {"user_id":123,"username":"test_user","new_rank":"Starter","order_id":456}
[timestamp] local.INFO: Sponsorship Tracked {"sponsor_id":5,"sponsor_rank":"Starter","new_user_id":123,"new_user_rank":"Starter"}
[timestamp] local.INFO: Checking Rank Advancement Criteria (Backward Compatible) {"user_id":5,"current_rank":"Starter","tracked_sponsors":3,"legacy_sponsors":1,"total_same_rank_sponsors":4,"required_sponsors":5,"can_advance":false}
```

**If advancement criteria met (5/5)**:
```
[timestamp] local.INFO: Rank Advanced Successfully {"user_id":5,"from_rank":"Starter","to_rank":"Newbie","order_id":457,"sponsors_count":5}
[timestamp] local.INFO: System-Funded Order Created {"order_id":457,"order_number":"RANK-ABC123","user_id":5,"package_id":2,"amount":2500}
```

### Test 4: Verify CheckoutController Integration

**Place a test order**:

1. Login to production site (use test account)
2. Add Starter package to cart
3. Complete checkout
4. Verify:
   - Order confirmed
   - Rank updated (if first rankable package)
   - Sponsorship tracked (check logs)

**Check database**:
```bash
php artisan tinker
```

```php
// Check user's rank
$user = App\Models\User::where('username', 'test_user')->first();
echo $user->current_rank; // Should show "Starter"

// Check sponsorships tracked
$sponsor = $user->sponsor;
if ($sponsor) {
    echo $sponsor->directSponsorsTracked()->count(); // Should increment
}
```

---

## Monitoring & Logging

### 1. Real-Time Log Monitoring

**Watch for rank-related events**:

```bash
# SSH into Hostinger
ssh yourusername@yourdomain.com

cd /home/yourusername/public_html

# Monitor rank advancements in real-time
tail -f storage/logs/laravel.log | grep "Rank"
```

**Key Events to Watch**:
- `User rank updated after package purchase`
- `Sponsorship Tracked`
- `Checking Rank Advancement Criteria`
- `Rank Advanced Successfully`
- `System-Funded Order Created`

### 2. Database Monitoring Queries

**Via phpMyAdmin or SSH (mysql)**:

```sql
-- Check recent rank advancements
SELECT 
    u.username,
    ra.from_rank,
    ra.to_rank,
    ra.advancement_type,
    ra.sponsors_count,
    ra.system_paid_amount,
    ra.created_at
FROM rank_advancements ra
JOIN users u ON ra.user_id = u.id
ORDER BY ra.created_at DESC
LIMIT 20;

-- Check users close to advancement
SELECT 
    u.username,
    u.current_rank,
    p.required_direct_sponsors,
    (SELECT COUNT(*) FROM direct_sponsors_tracker dst 
     WHERE dst.user_id = u.id 
     AND dst.counted_for_rank = u.current_rank) as tracked_sponsors,
    (SELECT COUNT(*) FROM users u2 
     WHERE u2.sponsor_id = u.id 
     AND u2.current_rank = u.current_rank
     AND u2.id NOT IN (
         SELECT sponsored_user_id 
         FROM direct_sponsors_tracker 
         WHERE user_id = u.id
     )) as legacy_sponsors
FROM users u
JOIN packages p ON u.rank_package_id = p.id
WHERE p.next_rank_package_id IS NOT NULL
HAVING (tracked_sponsors + legacy_sponsors) >= (p.required_direct_sponsors - 1)
ORDER BY (tracked_sponsors + legacy_sponsors) DESC
LIMIT 20;

-- Check system cost (total paid by system)
SELECT 
    to_rank,
    COUNT(*) as advancements,
    SUM(system_paid_amount) as total_cost
FROM rank_advancements
WHERE advancement_type = 'sponsorship_reward'
GROUP BY to_rank;

-- Check daily advancement activity
SELECT 
    DATE(created_at) as date,
    COUNT(*) as advancements,
    SUM(system_paid_amount) as daily_cost
FROM rank_advancements
WHERE advancement_type = 'sponsorship_reward'
GROUP BY DATE(created_at)
ORDER BY date DESC
LIMIT 30;
```

### 3. Performance Monitoring

**Check query performance**:

```bash
# Enable query logging (temporarily)
php artisan tinker
```

```php
// Enable query log
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

**Expected Query Time**: < 100ms for most operations

### 4. Error Alerting

**Setup email alerts for critical errors** (optional):

Edit `config/logging.php` to add email channel:

```php
'channels' => [
    'email' => [
        'driver' => 'monolog',
        'level' => 'critical',
        'handler' => StreamHandler::class,
        'with' => [
            'stream' => 'php://stderr',
        ],
    ],
],
```

Or use Hostinger's built-in error email notifications.

---

## Rollback Procedures

### Scenario 1: Code Issues Detected

**Step 1: Restore from Git**

```bash
cd /home/yourusername/public_html

# Enable maintenance mode
php artisan down

# View commit history
git log --oneline -10

# Rollback to previous commit (before Phase 3)
git reset --hard <previous_commit_hash>

# Example:
# git reset --hard d48a81a

# Clear and rebuild caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Disable maintenance mode
php artisan up
```

**Step 2: Verify Rollback**

```bash
# Check if RankAdvancementService removed
ls -la app/Services/RankAdvancementService.php
# Should show: No such file or directory

# Check logs
tail -50 storage/logs/laravel.log
```

### Scenario 2: Database Corruption

**⚠️ CRITICAL: Only if database is corrupted**

```bash
cd /home/yourusername

# Restore database backup
mysql -u your_db_user -p your_database_name < backups/backup_before_phase3_YYYYMMDD_HHMM.sql

# Verify restoration
mysql -u your_db_user -p -e "SELECT COUNT(*) FROM rank_advancements;" your_database_name
```

### Scenario 3: Partial Rollback (Keep Data, Revert Code)

**If you want to keep advancement data but revert code**:

```bash
cd /home/yourusername/public_html

# Rollback ONLY CheckoutController changes
git checkout <previous_commit> -- app/Http/Controllers/CheckoutController.php

# Remove RankAdvancementService
rm app/Services/RankAdvancementService.php
rm app/Console/Commands/BackfillLegacySponsorships.php

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rank advancement data remains in database
# System simply won't trigger new advancements
```

---

## Common Issues & Solutions

### Issue 1: "Class RankAdvancementService not found"

**Cause**: Autoloader not updated

**Solution**:
```bash
cd /home/yourusername/public_html
composer dump-autoload
php artisan config:clear
php artisan route:clear
```

### Issue 2: "Field 'total_amount' doesn't have a default value"

**Cause**: Order creation missing required fields

**Solution**: Already fixed in `RankAdvancementService.php`. If error persists:

```bash
# Check if latest code deployed
grep "total_amount" app/Services/RankAdvancementService.php

# Should show:
# 'total_amount' => $package->price,
```

If missing, re-upload the correct `RankAdvancementService.php` file.

### Issue 3: Rank Advancement Not Triggering

**Debug Steps**:

```bash
# 1. Check package configuration
php check_rank_packages.php

# 2. Check user's progress
php artisan tinker
```

```php
$user = App\Models\User::where('username', 'test_user')->first();

// Check rank
echo "Current rank: " . $user->current_rank . "\n";
echo "Rank package ID: " . $user->rank_package_id . "\n";

// Check sponsor count
echo "Same-rank sponsors: " . $user->getSameRankSponsorsCount() . "\n";

// Check required
$package = $user->rankPackage;
if ($package) {
    echo "Required sponsors: " . $package->required_direct_sponsors . "\n";
    echo "Can advance: " . ($package->canAdvanceToNextRank() ? 'Yes' : 'No') . "\n";
}

// Check service
$service = new App\Services\RankAdvancementService();
$progress = $service->getRankAdvancementProgress($user);
print_r($progress);
```

### Issue 4: Legacy Sponsorships Not Counted

**Verify backward compatibility**:

```bash
php artisan tinker
```

```php
$sponsor = App\Models\User::find(5);

// Count tracked
$tracked = $sponsor->directSponsorsTracked()
    ->where('counted_for_rank', $sponsor->current_rank)
    ->count();
echo "Tracked: $tracked\n";

// Count legacy
$legacy = App\Models\User::where('sponsor_id', $sponsor->id)
    ->where('current_rank', $sponsor->current_rank)
    ->whereNotIn('id', function($query) use ($sponsor) {
        $query->select('sponsored_user_id')
              ->from('direct_sponsors_tracker')
              ->where('user_id', $sponsor->id);
    })
    ->count();
echo "Legacy: $legacy\n";

// Total
echo "Total: " . ($tracked + $legacy) . "\n";
```

If legacy count is wrong, check that referrals have correct `current_rank` set.

### Issue 5: System-Funded Orders Show ₱0.00

**This is a cosmetic issue** in some views. The actual amount is tracked correctly.

**Verify**:
```sql
SELECT 
    order_number,
    grand_total,
    payment_method,
    notes
FROM orders
WHERE payment_method = 'system_reward'
ORDER BY created_at DESC
LIMIT 10;
```

Check `rank_advancements.system_paid_amount` for accurate tracking:
```sql
SELECT 
    user_id,
    to_rank,
    system_paid_amount,
    order_id
FROM rank_advancements
WHERE advancement_type = 'sponsorship_reward'
ORDER BY created_at DESC
LIMIT 10;
```

### Issue 6: Artisan Command Not Found

**Check command registration**:

```bash
php artisan list | grep rank
```

If not showing:

```bash
# Clear cached commands
php artisan config:clear
php artisan clear-compiled

# Rebuild autoloader
composer dump-autoload

# Try again
php artisan list | grep rank
```

### Issue 7: Permission Denied Errors

**Fix file permissions**:

```bash
cd /home/yourusername/public_html

# Set correct permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Storage and bootstrap/cache need write permissions
chmod -R 775 storage bootstrap/cache
chown -R yourusername:yourusername storage bootstrap/cache
```

---

## Performance Considerations

### 1. Database Indexing

**Verify indexes exist** (should be from Phase 1 migrations):

```sql
-- Check direct_sponsors_tracker indexes
SHOW INDEX FROM direct_sponsors_tracker;

-- Should have:
-- Index on (user_id, counted_for_rank)
-- Index on sponsored_user_id
```

**If missing**:
```sql
ALTER TABLE direct_sponsors_tracker 
ADD INDEX idx_user_rank (user_id, counted_for_rank);

ALTER TABLE direct_sponsors_tracker 
ADD INDEX idx_sponsored_user (sponsored_user_id);
```

### 2. Query Optimization

**Monitor slow queries**:

```sql
-- Enable slow query log (temporary)
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1; -- Log queries > 1 second

-- Check slow query log location
SHOW VARIABLES LIKE 'slow_query_log_file';
```

**Review after 24 hours**:
```bash
# Check for slow queries
tail -100 /path/to/slow-query.log
```

### 3. Caching Strategy

**Laravel cache optimization**:

```bash
# After deployment, always run:
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Consider enabling OPcache (ask Hostinger support)
```

### 4. Background Processing (Future Enhancement)

**For high-traffic sites**, consider moving rank advancement to queue:

```php
// Instead of synchronous in CheckoutController:
ProcessRankAdvancement::dispatch($sponsor, $newUser);

// Create job:
php artisan make:job ProcessRankAdvancement
```

Currently, Phase 3 processes synchronously (immediate), which is fine for most sites.

---

## Post-Deployment Monitoring Schedule

### First 24 Hours

**Every 2 hours**:
- ✅ Check error logs: `tail -100 storage/logs/laravel.log`
- ✅ Verify rank advancements: Check `rank_advancements` table
- ✅ Monitor server resources: CPU, Memory, Disk

**Queries to run**:
```sql
-- Advancements in last 24 hours
SELECT COUNT(*) FROM rank_advancements 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- System cost last 24 hours
SELECT SUM(system_paid_amount) FROM rank_advancements 
WHERE advancement_type = 'sponsorship_reward' 
AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- Errors in last 2 hours
SELECT * FROM activity_logs 
WHERE type = 'error' 
AND created_at >= DATE_SUB(NOW(), INTERVAL 2 HOUR);
```

### First Week

**Daily checks**:
- ✅ Review rank advancement patterns
- ✅ Verify no database locks or deadlocks
- ✅ Check user feedback/complaints
- ✅ Monitor system-funded order costs

### Monthly

**Performance review**:
- ✅ Total rank advancements
- ✅ Total system cost
- ✅ Average time to advancement per rank
- ✅ Database growth rate
- ✅ Query performance metrics

---

## Success Criteria

Phase 3 deployment is considered **successful** when:

- ✅ All code deployed without errors
- ✅ No PHP errors in logs (first 24 hours)
- ✅ At least 1 successful rank advancement recorded (or legacy backfill completed)
- ✅ CheckoutController integration working (orders tracked → ranks updated)
- ✅ Backward compatibility verified (legacy referrals counted)
- ✅ Artisan command executes without errors
- ✅ Database queries performant (< 100ms avg)
- ✅ No user complaints about checkout process
- ✅ System-funded orders created correctly
- ✅ Rank advancement history accurately logged

---

## Support Contacts

**If issues arise during deployment**:

1. **Check logs first**: `storage/logs/laravel.log`
2. **Review this guide**: Search for specific error messages
3. **Hostinger Support**: For server/infrastructure issues
4. **Development Team**: For code-related issues

**Useful Hostinger Support Articles**:
- SSH Access: https://support.hostinger.com/en/articles/1583245-how-to-connect-to-ssh
- Database Management: https://support.hostinger.com/en/articles/1583258-how-to-access-phpmyadmin
- File Manager: https://support.hostinger.com/en/articles/1583262-how-to-use-file-manager

---

## Appendix A: Quick Command Reference

```bash
# ===== DEPLOYMENT =====
php artisan down                          # Enable maintenance mode
git pull origin main                      # Pull latest code
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up                            # Disable maintenance mode

# ===== VERIFICATION =====
php artisan migrate:status                # Check migrations
php artisan list | grep rank              # Verify command exists
php check_rank_packages.php               # Check package config
tail -50 storage/logs/laravel.log         # Check logs

# ===== BACKFILL =====
php artisan rank:backfill-legacy-sponsorships --dry-run
php artisan rank:backfill-legacy-sponsorships --check-advancements

# ===== MONITORING =====
tail -f storage/logs/laravel.log | grep "Rank"  # Real-time monitoring
php artisan tinker                        # Database queries

# ===== ROLLBACK =====
git log --oneline -10                     # View commits
git reset --hard <commit_hash>            # Rollback code
mysql -u user -p db < backup.sql          # Restore database
```

---

## Appendix B: Database Queries for Monitoring

```sql
-- Recent rank advancements
SELECT u.username, ra.from_rank, ra.to_rank, ra.created_at
FROM rank_advancements ra
JOIN users u ON ra.user_id = u.id
ORDER BY ra.created_at DESC LIMIT 20;

-- Users 1 sponsor away from advancement
SELECT u.username, u.current_rank, 
       p.required_direct_sponsors,
       (SELECT COUNT(*) FROM direct_sponsors_tracker dst 
        WHERE dst.user_id = u.id 
        AND dst.counted_for_rank = u.current_rank) as current_count
FROM users u
JOIN packages p ON u.rank_package_id = p.id
WHERE p.next_rank_package_id IS NOT NULL
HAVING current_count >= (p.required_direct_sponsors - 1);

-- System cost by rank
SELECT to_rank, COUNT(*) as count, SUM(system_paid_amount) as total
FROM rank_advancements
WHERE advancement_type = 'sponsorship_reward'
GROUP BY to_rank;

-- Daily advancement activity
SELECT DATE(created_at) as date, COUNT(*) as advancements
FROM rank_advancements
WHERE advancement_type = 'sponsorship_reward'
AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(created_at)
ORDER BY date DESC;

-- Check for errors in orders
SELECT * FROM orders 
WHERE payment_method = 'system_reward' 
AND (grand_total = 0 OR grand_total IS NULL)
LIMIT 20;
```

---

## Conclusion

This comprehensive guide covers all aspects of deploying Phase 3 (Automatic Rank Advancement System) to production on Hostinger.

**Remember**:
- ✅ Always backup before deployment
- ✅ Test in staging first (if available)
- ✅ Deploy during off-peak hours
- ✅ Monitor closely for first 24 hours
- ✅ Have rollback plan ready

**Status**: Ready for production deployment

**Estimated Deployment Time**: 15-30 minutes

**Expected Downtime**: 0 minutes (with maintenance mode: 2-5 minutes)

---

**Deployment Date**: ______________

**Deployed By**: ______________

**Deployment Status**: ☐ Success ☐ Partial ☐ Rolled Back

**Notes**:
_______________________________________
_______________________________________
_______________________________________
