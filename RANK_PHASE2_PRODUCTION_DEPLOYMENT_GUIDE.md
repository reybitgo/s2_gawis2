# Rank System Phase 2 - Production Deployment Guide (Hostinger)

## ⚠️ CRITICAL: Phase 2 Deployment Prerequisites

This guide assumes **Phase 1 has been successfully deployed** to production. Phase 2 builds upon Phase 1's database structure and adds rank-aware commission logic.

---

## Table of Contents

1. [Prerequisites Checklist](#prerequisites-checklist)
2. [Pre-Deployment Verification](#pre-deployment-verification)
3. [Backup Strategy](#backup-strategy)
4. [Local Environment Testing](#local-environment-testing)
5. [Production Deployment Steps](#production-deployment-steps)
6. [Post-Deployment Verification](#post-deployment-verification)
7. [Testing in Production](#testing-in-production)
8. [Rollback Procedures](#rollback-procedures)
9. [Common Issues and Solutions](#common-issues-and-solutions)

---

## Prerequisites Checklist

### ✅ Phase 1 Must Be Deployed First

Before deploying Phase 2, verify Phase 1 is complete:

```bash
# Check Phase 1 migrations are deployed (4 migrations total)
php artisan migrate:status | grep "2025_11_27_14"

# Should show ALL 4 migrations as "Ran":
# 2025_11_27_141155_add_rank_fields_to_users_table ...................... [X] Ran
# 2025_11_27_141211_add_rank_fields_to_packages_table ................... [X] Ran
# 2025_11_27_141213_create_rank_advancements_table ....................... [X] Ran
# 2025_11_27_141215_create_direct_sponsors_tracker_table ................ [X] Ran

# Alternative check for direct_sponsors_tracker table:
php artisan tinker --execute="echo Schema::hasTable('direct_sponsors_tracker') ? 'OK' : 'MISSING';"
# Should show: OK

# Check rank packages configured
php artisan tinker --execute="echo App\Models\Package::whereNotNull('rank_name')->count() . ' rank packages found';"
# Should show: 3 rank packages found (or more)

# Check User model has rank methods
php artisan tinker --execute="echo method_exists(App\Models\User::first(), 'rankPackage') ? 'OK' : 'MISSING';"
# Should show: OK
```

If any of these fail, **deploy Phase 1 first** before proceeding.

### 📋 Required Information

```bash
# Hostinger Details (have these ready)
- Database Name: _____________
- Database User: _____________
- Database Host: _____________
- SSH Access: Yes/No
- File Manager Access: Yes/No
- Server Time: _____________
- Peak Traffic Hours: _____________
```

### 🔧 Tools Required

- SSH client (PuTTY, Terminal, or Hostinger File Manager)
- FTP/SFTP client (FileZilla or Hostinger File Manager)
- Local development environment (Laragon)
- Text editor for file comparison

---

## Pre-Deployment Verification

### Step 1: Verify Phase 1 Database Structure

**Via SSH or Hostinger phpMyAdmin:**

```sql
-- 1. Check rank tables exist
SHOW TABLES LIKE 'rank_%';
-- Expected: rank_advancements, (no matches from other tables)

SHOW TABLES LIKE 'direct_sponsors_tracker';
-- Expected: direct_sponsors_tracker

-- 2. Check user rank columns
DESCRIBE users;
-- Should see: current_rank, rank_package_id, rank_updated_at

-- 3. Check package rank columns
DESCRIBE packages;
-- Should see: rank_name, rank_order, required_direct_sponsors, is_rankable, next_rank_package_id

-- 4. Check rank packages configured
SELECT id, name, rank_name, rank_order, is_mlm_package 
FROM packages 
WHERE rank_name IS NOT NULL 
ORDER BY rank_order;
-- Should return at least 3 rows

-- 5. Check MLM settings exist
SELECT package_id, level, commission_amount 
FROM mlm_settings 
WHERE is_active = 1 
ORDER BY package_id, level;
-- Should show commission rates for all rank packages
```

**Expected Results:**
- ✅ All rank tables present
- ✅ All rank columns in users and packages tables
- ✅ At least 3 rank packages configured
- ✅ MLM commission settings for all ranks

If any checks fail, **deploy Phase 1 first**.

### Step 2: Verify Existing MLM Commission System

```sql
-- First, check if MLM balances exist (confirms MLM system is working)
SELECT u.username, w.mlm_balance 
FROM users u 
JOIN wallets w ON u.id = w.user_id 
WHERE w.mlm_balance > 0 
LIMIT 5;
-- Should show users with MLM earnings (e.g., 200.00, 500.00, etc.)

-- Check ALL transaction types in your system
SELECT DISTINCT type, COUNT(*) as count
FROM transactions
GROUP BY type
ORDER BY count DESC;
-- Note: Some systems may not record 'mlm_commission' as transactions
-- If you don't see 'mlm_commission' type, that's OK - check activity_logs instead

-- Alternative: Check activity_logs for MLM commission entries
SELECT 
    al.user_id,
    u.username,
    al.type,
    al.message,
    al.created_at
FROM activity_logs al
JOIN users u ON al.user_id = u.id
WHERE al.type = 'mlm'
ORDER BY al.created_at DESC
LIMIT 5;
-- Should show MLM commission activity logs

-- Check recent paid orders with MLM packages
SELECT 
    o.id,
    o.order_number,
    u.username,
    o.payment_status,
    o.created_at,
    p.name as package_name
FROM orders o
JOIN users u ON o.user_id = u.id
JOIN order_items oi ON o.id = oi.order_id
JOIN packages p ON oi.package_id = p.id
WHERE o.payment_status = 'paid'
AND p.is_mlm_package = 1
ORDER BY o.created_at DESC
LIMIT 5;
-- Should show recent paid MLM package orders
```

**Important Notes**:
- ✅ If users have `mlm_balance > 0`, your MLM system IS working
- ✅ Phase 2 only changes HOW commissions are calculated (rank-aware)
- ✅ Phase 2 does NOT change how commissions are recorded
- ⚠️ Your system may use `activity_logs` instead of `transactions` for MLM tracking

**What Phase 2 Changes**:
- ✅ Commission amounts will follow rank-based rules
- ✅ Higher ranks get lower rates for lower-rank buyers
- ✅ Lower ranks get their own rate (motivation to advance)
- ❌ Does NOT change transaction/log recording method

---

## Backup Strategy

### CRITICAL: Triple Backup Before Deployment

#### Backup 1: Full Database Backup

**Via phpMyAdmin/Adminer:**
1. Login to Hostinger control panel → phpMyAdmin
2. Select your database
3. Click "Export"
4. Choose "Custom" export method
5. Select ALL tables
6. Format: SQL
7. Download the backup file

**Save as:** `gawis2_backup_YYYYMMDD_HHMM_before_phase2.sql`

**Via SSH (if available):**
```bash
# Login to Hostinger via SSH
ssh u123456789@your-domain.com

# Navigate to your directory
cd domains/your-domain.com/public_html

# Create backup
mysqldump -u dbuser -p dbname > gawis2_backup_$(date +%Y%m%d_%H%M%S)_before_phase2.sql

# Download to local machine (from your local terminal)
scp u123456789@your-domain.com:domains/your-domain.com/public_html/gawis2_backup_*.sql ./
```

#### Backup 2: Critical Service Files

**Phase 2 modifies these files - BACK THEM UP:**

```bash
# Via File Manager:
1. Navigate to app/Services/
2. Right-click MLMCommissionService.php → Download
3. Save as: MLMCommissionService.php.backup_YYYYMMDD

# Via SSH:
cd domains/your-domain.com/public_html/app/Services
cp MLMCommissionService.php MLMCommissionService.php.backup_$(date +%Y%m%d_%H%M%S)

# Download backup
scp u123456789@your-domain.com:domains/your-domain.com/public_html/app/Services/MLMCommissionService.php.backup_* ./
```

#### Backup 3: Export MLM Commission Reference Data

**Export current MLM balances for comparison:**

```sql
-- Export current MLM wallet balances (for comparison)
SELECT 
    u.id,
    u.username,
    u.current_rank,
    w.mlm_balance,
    w.updated_at
FROM users u
JOIN wallets w ON u.id = w.user_id
WHERE w.mlm_balance > 0
ORDER BY w.mlm_balance DESC;
```

**Save as CSV:** `mlm_balances_before_phase2_YYYYMMDD.csv`

**Alternative: Export activity logs if your system uses them:**

```sql
-- Export recent MLM activity logs (if your system uses activity_logs)
SELECT 
    al.id,
    al.user_id,
    u.username,
    al.type,
    al.message,
    al.created_at
FROM activity_logs al
JOIN users u ON al.user_id = u.id
WHERE al.type = 'mlm'
AND al.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
ORDER BY al.created_at DESC;
```

**Save as CSV:** `mlm_activity_logs_before_phase2_YYYYMMDD.csv`

**Why?** To compare commission patterns before and after deployment.

**Note:** Your system appears to record MLM commissions in `activity_logs` and update wallet balances directly, rather than creating `transaction` records with type='mlm_commission'. This is perfectly fine - Phase 2 doesn't change this behavior.

#### Verify Backup Integrity

```bash
# Local verification (test restore on local)
mysql -u root local_test_db < gawis2_backup_YYYYMMDD_HHMM_before_phase2.sql

# If successful, you have a valid backup
```

**✅ Backup Checklist:**
- [ ] Full SQL database dump downloaded
- [ ] MLMCommissionService.php backed up
- [ ] Recent MLM commissions exported to CSV
- [ ] Backups verified by test restore
- [ ] Backups stored in 3 locations (local, cloud, external drive)

---

## Local Environment Testing

### Step 1: Fresh Database with Production Data

```bash
# In your local Laragon environment

# 1. Create fresh test database
mysql -u root -e "CREATE DATABASE gawis2_phase2_test;"

# 2. Import production backup
mysql -u root gawis2_phase2_test < gawis2_backup_YYYYMMDD_HHMM_before_phase2.sql

# 3. Update .env to use test database
DB_DATABASE=gawis2_phase2_test

# 4. Clear cache
php artisan config:clear
php artisan cache:clear
```

### Step 2: Deploy Phase 2 Files to Local Test

**Copy Phase 2 files to local test environment:**

```bash
# If you already have Phase 2 files from development, they're ready
# If not, ensure you have these files:
app/Services/RankComparisonService.php (NEW)
app/Services/MLMCommissionService.php (MODIFIED)
```

### Step 3: Run Verification Script

```bash
# Run the database verification script
php verify_database_for_testing.php
```

**Expected output:**
```
✓✓✓ DATABASE IS PERFECT FOR TESTING! ✓✓✓
```

If you get errors, fix them before proceeding.

### Step 4: Run Phase 2 Tests

```bash
# Run basic tests
php test_rank_aware_commission.php

# Expected output:
# ✓ Scenario 0a: No rank upline → 0.00 commission
# ✓ Scenario 0b: No rank buyer → 0.00 commission
# ✓ Scenario 1: Higher rank → Lower rate
# ✓ Scenario 2: Lower rank → Own rate
# ✓ Scenario 3: Same rank → Standard rate
# ✓ Scenario 4: Inactive users skipped
# Phase 2 Test Completed!

# Run detailed tests
php test_rank_aware_commission_detailed.php

# Expected output:
# Total test cases: 8
# Pass rate: 100%
# Status: ✓ ALL TESTS PASSED
```

### Step 5: Test Existing Functionality

**Option A: Use the provided test script (RECOMMENDED):**

```bash
php test_phase2_deployment_scenario.php
```

This will automatically create test users, simulate an order, and verify Phase 2 is working correctly.

**Option B: Manual testing via tinker:**

```bash
php artisan tinker
```

```php
// 1. Get Starter package
$package = \App\Models\Package::where('rank_name', 'Starter')->first();

// 2. Create test order
$buyer = \App\Models\User::first(); // Use any existing user with a sponsor
$orderNumber = 'TEST-PHASE2-' . time();

$order = \App\Models\Order::create([
    'user_id' => $buyer->id,
    'order_number' => $orderNumber,
    'status' => 'confirmed',
    'payment_status' => 'paid',
    'payment_method' => 'wallet',
    'delivery_method' => 'office_pickup',
    'subtotal' => $package->price,
    'tax_amount' => 0,
    'total_amount' => $package->price,
    'tax_rate' => 0,
    'paid_at' => now(),
]);

// 3. Add package to order
\App\Models\OrderItem::create([
    'order_id' => $order->id,
    'package_id' => $package->id,
    'item_type' => 'package',
    'quantity' => 1,
    'unit_price' => $package->price,
    'total_price' => $package->price,
]);

// 4. Get sponsor's initial balance
$sponsor = $buyer->sponsor;
$initialBalance = $sponsor->wallet->mlm_balance;

// 5. Process MLM commissions
$mlmService = app(\App\Services\MLMCommissionService::class);
$result = $mlmService->processCommissions($order);

echo $result ? "✓ MLM Commission processed with rank-aware logic\n" : "✗ Failed\n";

// 6. Check sponsor's new balance
$sponsor->wallet->refresh();
$newBalance = $sponsor->wallet->mlm_balance;
$commission = $newBalance - $initialBalance;

echo "Initial Balance: ₱" . number_format($initialBalance, 2) . "\n";
echo "New Balance: ₱" . number_format($newBalance, 2) . "\n";
echo "Commission: ₱" . number_format($commission, 2) . "\n";

// Should be ₱200.00 for Starter Level 1 commission (if both are Starter rank)
```

**✅ Local Testing Checklist:**
- [ ] Database verified with production data
- [ ] Phase 2 files deployed to local
- [ ] Verification script passes
- [ ] Basic tests pass (6/6)
- [ ] Detailed tests pass (8/8)
- [ ] Test order processes correctly
- [ ] MLM commissions calculate with rank-aware logic
- [ ] No errors in logs

---

## Production Deployment Steps

### 🚨 DEPLOYMENT DAY PROTOCOL

#### Pre-Deployment (15 minutes before)

1. **Notify users (optional):**
```
"System maintenance scheduled for [TIME]. Expected duration: 10 minutes.
MLM commission system will be enhanced with rank-based calculations.
All services will remain available during the upgrade."
```

2. **Enable Maintenance Mode:**

**Via SSH:**
```bash
cd /domains/your-domain.com/public_html
php artisan down --secret="phase2-upgrade" --retry=60
```

**Via File Manager:**
Create file: `public_html/storage/framework/down`
```json
{
    "retry": 60,
    "secret": "phase2-upgrade"
}
```

**Note:** With `--secret`, you can bypass maintenance mode by visiting: `https://your-domain.com/phase2-upgrade`

3. **Final backup (live state):**
```bash
# Quick database snapshot
mysqldump -u dbuser -p dbname > final_backup_phase2_$(date +%Y%m%d_%H%M%S).sql
```

#### Deployment Phase 1: Upload New Service File (2 minutes)

**Upload RankComparisonService.php:**

**Via File Manager:**
1. Navigate to `public_html/app/Services/`
2. Upload `RankComparisonService.php` (NEW FILE)
3. Verify file size matches local version

**Via FTP:**
```bash
# Use FileZilla or similar
# Connect to Hostinger FTP
# Upload: local/app/Services/RankComparisonService.php
# To: remote/app/Services/RankComparisonService.php
```

**Via SSH (fastest):**
```bash
# From local machine, create deployment package
tar -czf phase2_files.tar.gz \
  app/Services/RankComparisonService.php \
  app/Services/MLMCommissionService.php

# Upload to server
scp phase2_files.tar.gz u123456789@your-domain.com:domains/your-domain.com/public_html/

# SSH into server and extract
ssh u123456789@your-domain.com
cd domains/your-domain.com/public_html
tar -xzf phase2_files.tar.gz
rm phase2_files.tar.gz
```

#### Deployment Phase 2: Update MLMCommissionService (3 minutes)

**⚠️ CRITICAL: This replaces the core commission logic**

**Before uploading, verify you have the backup:**
```bash
ls -lh app/Services/MLMCommissionService.php.backup_*
```

**Via File Manager:**
1. Navigate to `public_html/app/Services/`
2. Verify `MLMCommissionService.php.backup_YYYYMMDD` exists
3. Upload the new `MLMCommissionService.php` (overwrites existing)
4. Verify file size changed (should be slightly larger)

**Via SSH:**
```bash
cd /domains/your-domain.com/public_html/app/Services

# Verify backup exists
ls -lh MLMCommissionService.php.backup_*

# Upload new version (already in phase2_files.tar.gz if using that method)
# Or manually copy:
# (Assuming you uploaded to /tmp/ first)
cp /tmp/MLMCommissionService.php ./MLMCommissionService.php

# Verify file changed
ls -lh MLMCommissionService.php
```

#### Deployment Phase 3: Clear All Caches (1 minute)

```bash
cd /domains/your-domain.com/public_html

# Clear all caches (CRITICAL - ensures new code is loaded)
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Rebuild caches for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# If using opcache, restart PHP-FPM (may require support ticket)
# Or wait 5 minutes for opcache to refresh automatically
```

**Note:** If you don't have SSH access, Hostinger may have a "Clear Cache" button in the control panel under "Advanced" → "PHP Configuration".

#### Deployment Phase 4: Verify Files Uploaded (2 minutes)

```bash
# Via SSH, verify files exist and have correct permissions
cd /domains/your-domain.com/public_html/app/Services

# Check RankComparisonService exists
ls -lh RankComparisonService.php
# Should show file size ~6KB

# Check MLMCommissionService was updated
stat MLMCommissionService.php
# Check "Modify" timestamp is recent (within last few minutes)

# Verify file permissions
chmod 644 RankComparisonService.php
chmod 644 MLMCommissionService.php
```

**Via File Manager:**
1. Navigate to `app/Services/`
2. Verify `RankComparisonService.php` exists
3. Verify `MLMCommissionService.php` has recent modification date

#### Deployment Phase 5: Quick Functionality Test (2 minutes)

**Test that PHP can load the new service:**

```bash
php artisan tinker
```

```php
// Test 1: Check RankComparisonService loads
$service = app(\App\Services\RankComparisonService::class);
echo $service ? "✓ RankComparisonService loaded\n" : "✗ Failed\n";

// Test 2: Check MLMCommissionService loads with dependency
$mlmService = app(\App\Services\MLMCommissionService::class);
echo $mlmService ? "✓ MLMCommissionService loaded\n" : "✗ Failed\n";

// Test 3: Quick commission calculation test
$starter = \App\Models\Package::where('rank_name', 'Starter')->first();
$newbie = \App\Models\Package::where('rank_name', 'Newbie')->first();

if ($starter && $newbie) {
    echo "✓ Rank packages found\n";
} else {
    echo "✗ Rank packages missing - check Phase 1 deployment\n";
}

exit
```

**Expected output:**
```
✓ RankComparisonService loaded
✓ MLMCommissionService loaded
✓ Rank packages found
```

**If you get errors, see Rollback section immediately.**

#### Deployment Phase 6: Disable Maintenance Mode (immediate)

```bash
# Bring site back online
php artisan up
```

**Or via File Manager:**
Delete file: `public_html/storage/framework/down`

#### Deployment Phase 7: Monitor Logs (5 minutes)

```bash
# Watch Laravel logs for errors
tail -f storage/logs/laravel.log

# Should see:
# - No errors
# - If orders are being placed, you should see "Rank-Aware Commission Calculated" logs
# - "Rule applied" showing which commission rule was used
```

**Via File Manager:**
1. Navigate to `storage/logs/`
2. Download `laravel.log`
3. Open in text editor
4. Search for errors (Ctrl+F: "ERROR")
5. Look for "Rank-Aware Commission" entries (shows Phase 2 is working)

**✅ Deployment Checklist:**
- [ ] Maintenance mode enabled
- [ ] Final backup taken
- [ ] RankComparisonService.php uploaded
- [ ] MLMCommissionService.php replaced (backup verified)
- [ ] All caches cleared
- [ ] File permissions correct
- [ ] Quick functionality test passed
- [ ] Maintenance mode disabled
- [ ] No errors in logs
- [ ] Site accessible and working

---

## Post-Deployment Verification

### Immediate Checks (0-15 minutes after deployment)

#### 1. System Health Check

**Access these URLs:**
```
✅ Homepage:        https://your-domain.com
✅ Login:           https://your-domain.com/login
✅ Admin:           https://your-domain.com/admin
✅ Packages:        https://your-domain.com/packages
```

**All should load without errors.**

#### 2. Verify Rank-Aware Commission Logic Active

**Check Laravel logs (MOST RELIABLE METHOD):**

```bash
# Via SSH
tail -50 storage/logs/laravel.log | grep "Rank-Aware"

# Should show entries like:
# [timestamp] local.INFO: Rank-Aware Commission Calculated (Active User)
# [timestamp] local.INFO: Rule applied: Rule 1: Higher Rank → Lower Rate
```

**Via phpMyAdmin/Adminer (if activity_logs used):**
```sql
-- Check recent MLM activity logs
SELECT * FROM activity_logs 
WHERE type = 'mlm'
AND created_at >= NOW() - INTERVAL 1 HOUR 
ORDER BY created_at DESC 
LIMIT 10;

-- Note: Rank-aware logic is in the SERVICE layer, not database
-- Database records will look the same, but AMOUNTS will differ
-- Check Laravel logs for "Rank-Aware Commission Calculated" confirmation
```

**What to Look For:**
- ✅ Users still receiving MLM commissions
- ✅ Wallet `mlm_balance` being updated
- ✅ Activity logs showing MLM commission entries
- ✅ **NEW**: Laravel logs showing "Rank-Aware Commission Calculated"
- ✅ **NEW**: Laravel logs showing which rule was applied (Rule 1, 2, or 3)

**Check Laravel logs:**
```bash
# Via SSH
tail -50 storage/logs/laravel.log | grep "Rank-Aware"

# Should show entries like:
# [timestamp] Rank-Aware Commission Calculated (Active User)
# [timestamp] Rule applied: Rule 1: Higher Rank → Lower Rate
```

#### 3. Test New Package Order (Real Test)

**Option A: Automated Test (RECOMMENDED)**

Run the provided test script to simulate a real order:

```bash
php test_phase2_deployment_scenario.php
```

This will:
- Create test users (or use existing)
- Simulate a package purchase
- Process MLM commissions
- Verify rank-aware logic
- Show before/after balances
- Confirm expected commission amounts

**Expected Output:**
```
✓ Order created successfully
✓ MLM Commission processed successfully
✓ Commission amount is CORRECT!
✓ Rule Applied: Rule 3 (Same Rank → Standard Rate)
```

**Option B: Manual Order via Frontend**

1. Login as a test user (or create new test user)
2. Ensure user has a sponsor with a different rank
3. Purchase a package through the website
4. Check if sponsor receives commission
5. **Verify commission amount matches rank-aware rules**

**Via Database Check (After Manual Order):**
```sql
-- Find the most recent order
SELECT id, user_id, order_number, total_amount, payment_status 
FROM orders 
ORDER BY created_at DESC 
LIMIT 1;

-- Check MLM commissions via activity_logs
SELECT 
    al.id,
    al.user_id,
    u.username,
    u.current_rank,
    al.message,
    al.created_at
FROM activity_logs al
JOIN users u ON al.user_id = u.id
WHERE al.type = 'mlm'
AND al.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
ORDER BY al.created_at DESC;

-- Check wallet balances
SELECT 
    u.id,
    u.username,
    u.current_rank,
    w.mlm_balance
FROM users u
JOIN wallets w ON u.id = w.user_id
WHERE w.mlm_balance > 0
ORDER BY w.updated_at DESC
LIMIT 5;
```

#### 4. Compare Commission Amounts

**Verify rank-aware rules are applied:**

```sql
-- Check recent commissions with ranks
SELECT 
    al.user_id,
    u.username as upline_username,
    u.current_rank as upline_rank,
    al.message,
    al.created_at
FROM activity_logs al
JOIN users u ON al.user_id = u.id
WHERE al.type = 'mlm'
AND al.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
ORDER BY al.created_at DESC
LIMIT 10;

-- Parse the message to see commission amounts
-- Example: "user earned ₱200.00 Level 1 commission from buyer's order"
```

**Expected behavior based on ranks:**
- Same rank → Standard rate (e.g., Starter → Starter = ₱200.00)
- Higher rank → Lower rank's rate (e.g., Newbie → Starter = ₱200.00, not ₱500.00)
- Lower rank → Own rate (e.g., Starter → Newbie = ₱200.00, not ₱500.00)

**Check wallet balances to verify commissions credited:**
```sql
-- Compare wallet balances before and after test order
SELECT 
    u.username,
    u.current_rank,
    w.mlm_balance,
    w.updated_at
FROM users u
JOIN wallets w ON u.id = w.user_id
WHERE u.id IN (SELECT user_id FROM activity_logs WHERE type = 'mlm' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR))
ORDER BY w.updated_at DESC;
```

#### 5. Log Review

**Check error logs:**
```bash
# Via SSH
tail -100 storage/logs/laravel.log | grep -i error

# Or download and review in text editor
```

**Look for:**
- ✅ No "RankComparisonService not found" errors
- ✅ No "Class does not exist" errors
- ✅ No "Missing rank package" errors (unless users truly don't have ranks)

### Extended Monitoring (First 24 Hours)

#### Monitor These Metrics:

1. **MLM Commission Processing:**
   - Are commissions still being distributed? ✅
   - Are amounts correct based on rank rules? ✅
   - Are users receiving notifications? ✅

2. **Database Performance:**
   - Query response times normal? ✅
   - No deadlocks or locks? ✅

3. **Error Rates:**
   - Check logs every 2 hours
   - Monitor for rank-related errors

4. **User Reports:**
   - Any complaints about commission amounts?
   - Any missing commissions?

#### Key Queries for Monitoring:

**Note:** Your system records MLM commissions in `activity_logs` and updates `wallets` directly.

```sql
-- MLM commissions distributed today (via activity_logs)
SELECT COUNT(*) as commissions_today
FROM activity_logs
WHERE type = 'mlm'
AND DATE(created_at) = CURDATE();

-- Recent MLM activity with details
SELECT 
    al.user_id,
    u.username,
    u.current_rank,
    al.message,
    al.created_at
FROM activity_logs al
JOIN users u ON al.user_id = u.id
WHERE al.type = 'mlm'
AND DATE(al.created_at) = CURDATE()
ORDER BY al.created_at DESC
LIMIT 20;

-- Check wallet MLM balances updated today
SELECT 
    u.id,
    u.username,
    u.current_rank,
    w.mlm_balance,
    w.updated_at
FROM users u
JOIN wallets w ON u.id = w.user_id
WHERE DATE(w.updated_at) = CURDATE()
AND w.mlm_balance > 0
ORDER BY w.updated_at DESC
LIMIT 20;

-- If your system also uses transactions table (optional check):
-- Note: Some systems may not record MLM in transactions
SELECT COUNT(*) as tx_count
FROM transactions
WHERE type = 'mlm'
AND DATE(created_at) = CURDATE();
```

**✅ Post-Deployment Verification Checklist:**
- [ ] All URLs accessible
- [ ] Rank-aware logs appearing
- [ ] Test order processed correctly
- [ ] Commission amounts follow rank rules
- [ ] No errors in logs
- [ ] Performance normal
- [ ] 24-hour monitoring scheduled

---

## Testing in Production

### Safe Production Testing (No Real Money)

**Option 1: Monitor Existing Orders**

Simply wait for real orders to come in and monitor the logs:

```bash
# Watch logs in real-time
tail -f storage/logs/laravel.log | grep "Rank-Aware"
```

**Option 2: Test User Order (Recommended)**

Create test users and a small-value test order:

```bash
php artisan tinker
```

```php
// NOTE: This creates actual test users and orders in production database.
// Use with caution or use the test script instead: php test_phase2_deployment_scenario.php

// 1. Get Starter package
$package = \App\Models\Package::where('rank_name', 'Starter')->first();

// 2. Find existing test users or use real users with caution
$sponsor = \App\Models\User::where('username', 'test_sponsor_phase2')->first();
$buyer = \App\Models\User::where('username', 'test_buyer_phase2')->first();

if (!$sponsor || !$buyer) {
    echo "⚠️ Test users not found. Run test_phase2_deployment_scenario.php first\n";
    echo "Or use existing users for manual testing.\n";
    exit;
}

// 3. Get initial balance
$initialBalance = $sponsor->wallet->mlm_balance;

// 4. Create test order
$orderNumber = 'TEST-PHASE2-PROD-' . time();
$order = \App\Models\Order::create([
    'user_id' => $buyer->id,
    'order_number' => $orderNumber,
    'status' => 'confirmed',
    'payment_status' => 'paid',
    'payment_method' => 'wallet',
    'delivery_method' => 'office_pickup',
    'subtotal' => $package->price,
    'tax_amount' => 0,
    'total_amount' => $package->price,
    'tax_rate' => 0,
    'paid_at' => now(),
]);

// 5. Add package to order
\App\Models\OrderItem::create([
    'order_id' => $order->id,
    'package_id' => $package->id,
    'item_type' => 'package',
    'quantity' => 1,
    'unit_price' => $package->price,
    'total_price' => $package->price,
]);

// 6. Process MLM commissions
$mlmService = app(\App\Services\MLMCommissionService::class);
$result = $mlmService->processCommissions($order);

echo $result ? "✓ Commission processed\n" : "✗ Failed\n";

// 7. Check sponsor's new balance
$sponsor->wallet->refresh();
$newBalance = $sponsor->wallet->mlm_balance;
$commission = $newBalance - $initialBalance;

echo "\nInitial Balance: ₱" . number_format($initialBalance, 2) . "\n";
echo "New Balance: ₱" . number_format($newBalance, 2) . "\n";
echo "Commission Earned: ₱" . number_format($commission, 2) . "\n";

// Expected: ₱200.00 for Starter → Starter (Rule 3: Same Rank)

// 8. Verify in logs
echo "\nCheck logs for:\n";
echo "- Rule applied: Rule 3: Same Rank → Standard\n";
echo "- Commission: 200.00\n";
echo "\nLog location: storage/logs/laravel.log\n";
echo "Search for: Rank-Aware Commission Calculated\n";

// Note: In production, monitor real orders instead of creating test orders
```

**Expected Results:**
```
✓ Commission processed
Sponsor commission: ₱200.00

Check logs for:
- Rule applied: Rule 1: Higher Rank → Lower Rate
- Commission: 200.00
```

**If commission is ₱500.00 instead of ₱200.00:**
- ❌ Phase 2 NOT deployed correctly
- The old logic is still running
- Check if MLMCommissionService.php was really replaced

---

## Rollback Procedures

### 🚨 WHEN TO ROLLBACK

Rollback immediately if:
- ❌ Commissions not being distributed at all
- ❌ Wrong commission amounts (not following rank rules)
- ❌ Errors in logs preventing order processing
- ❌ Site crashes or becomes inaccessible
- ❌ More than 5% users reporting issues

### ROLLBACK METHOD 1: Quick File Restore (5 minutes)

**If just deployed and issues found immediately:**

```bash
# Enable maintenance mode
php artisan down

# Restore original MLMCommissionService
cd /domains/your-domain.com/public_html/app/Services
cp MLMCommissionService.php.backup_YYYYMMDD MLMCommissionService.php

# Delete RankComparisonService (makes it clear Phase 2 is rolled back)
rm RankComparisonService.php

# Clear caches
cd /domains/your-domain.com/public_html
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache

# Test that site loads
php artisan tinker --execute="echo 'Site OK';"

# Disable maintenance mode
php artisan up
```

**Via File Manager:**
1. Navigate to `app/Services/`
2. Delete `RankComparisonService.php`
3. Rename `MLMCommissionService.php.backup_YYYYMMDD` to `MLMCommissionService.php`
4. Clear cache via control panel (if available)
5. Test site loads

**Expected result:** MLM commissions will use old logic (standard rates, no rank comparison).

### ROLLBACK METHOD 2: Full Database Restore (20 minutes)

**If database corruption (unlikely for Phase 2, but for safety):**

```bash
# Enable maintenance mode
php artisan down

# In phpMyAdmin or via SSH:
# 1. Drop current database (⚠️ CAREFUL!)
mysql -u dbuser -p -e "DROP DATABASE current_db;"

# 2. Recreate database
mysql -u dbuser -p -e "CREATE DATABASE current_db;"

# 3. Restore from backup
mysql -u dbuser -p current_db < gawis2_backup_YYYYMMDD_HHMM_before_phase2.sql

# 4. Restore original service files (Method 1 above)

# 5. Clear caches
php artisan config:clear
php artisan cache:clear

# 6. Test site functionality
php artisan up
```

### Post-Rollback Verification

```bash
php artisan tinker
```

```php
// Verify old logic is back
$mlmService = app(\App\Services\MLMCommissionService::class);
echo "MLMCommissionService loaded\n";

// Should NOT have RankComparisonService dependency anymore
// (Old constructor has no dependencies)

// Test a commission calculation
$commission = \App\Models\MlmSetting::getCommissionForLevel(1, 1);
echo "Standard commission for Starter Level 1: ₱" . number_format($commission, 2) . "\n";
// Should show ₱200.00

exit
```

**✅ Rollback Checklist:**
- [ ] Maintenance mode enabled during rollback
- [ ] Backup verified before restore
- [ ] Original files restored
- [ ] Caches cleared
- [ ] Site functionality working
- [ ] MLM commissions using standard logic
- [ ] No errors in logs
- [ ] Maintenance mode disabled
- [ ] Users notified of rollback (if applicable)

---

## Common Issues and Solutions

### Issue 1: RankComparisonService Not Found

**Error:**
```
Class 'App\Services\RankComparisonService' not found
```

**Solution:**
```bash
# Check if file was uploaded
ls -lh app/Services/RankComparisonService.php

# If missing, upload the file
# Via FTP or File Manager

# Clear caches
php artisan config:clear
php artisan cache:clear

# Verify file permissions
chmod 644 app/Services/RankComparisonService.php
```

### Issue 2: Old Commission Logic Still Running

**Symptom:** Newbie sponsor gets ₱500.00 when Starter buyer purchases (should be ₱200.00)

**Solution:**
```bash
# 1. Verify MLMCommissionService was actually replaced
cd app/Services
stat MLMCommissionService.php
# Check modification date - should be recent

# 2. Check file size
ls -lh MLMCommissionService.php
# Should be ~11KB (larger than before)

# 3. Check file content (first few lines)
head -30 MLMCommissionService.php
# Should see: "protected RankComparisonService $rankComparison;"

# If old file still there, re-upload and clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Restart PHP-FPM if possible (or wait 5 minutes for opcache)
```

### Issue 3: Both Users Must Have Ranks Error

**Symptom:** Some commissions return 0.00 with "Missing rank package" in logs

**This is EXPECTED behavior** if:
- Upline doesn't have a rank (never purchased a package)
- Buyer doesn't have a rank (shouldn't happen if they're buying a package)

**Solution:**

1. **Check if users without ranks need them:**
```sql
-- Find users with paid orders but no ranks
SELECT 
    u.id,
    u.username,
    u.current_rank,
    COUNT(o.id) as paid_orders
FROM users u
LEFT JOIN orders o ON u.id = o.user_id AND o.payment_status = 'paid'
GROUP BY u.id, u.username, u.current_rank
HAVING paid_orders > 0 AND u.current_rank IS NULL;
```

2. **If found, run rank assignment:**
```bash
# Run the assign ranks script
php assign_ranks_to_users.php

# This will assign ranks to users based on their purchased packages
```

3. **If still getting 0.00 for valid ranks:**
```bash
# Check if rank packages are properly configured
php artisan tinker --execute="
\$packages = App\Models\Package::whereNotNull('rank_name')->get(['id', 'name', 'rank_name']);
foreach (\$packages as \$pkg) {
    echo \$pkg->id . ': ' . \$pkg->rank_name . ' (' . \$pkg->name . ')' . PHP_EOL;
}
"
```

### Issue 4: Performance Degradation

**Symptom:** Site slower after deployment

**Phase 2 adds minimal overhead (just one additional method call), but if experiencing slowness:**

```bash
# 1. Check for missing indexes (shouldn't be needed, but verify)
php artisan tinker
```

```php
// Check if indexes exist
echo \Schema::hasIndex('users', 'users_rank_package_id_foreign') ? "✓\n" : "Missing\n";
echo \Schema::hasIndex('packages', 'packages_rank_order_index') ? "✓\n" : "Missing\n";
```

```bash
# 2. Optimize tables
mysql -u dbuser -p -e "OPTIMIZE TABLE users, packages, mlm_settings;"

# 3. Clear and rebuild caches
php artisan optimize:clear
php artisan optimize
```

### Issue 5: Logs Showing Wrong Rule Applied

**Symptom:** Logs show "Rule 2" but you expect "Rule 1"

**Verify rank order:**
```sql
-- Check rank order for packages
SELECT id, name, rank_name, rank_order 
FROM packages 
WHERE rank_name IS NOT NULL 
ORDER BY rank_order;

-- Ensure:
-- Starter = 1
-- Newbie = 2
-- Bronze = 3
-- (Higher number = higher rank)
```

**If rank_order is wrong:**
```sql
-- Fix rank order
UPDATE packages SET rank_order = 1 WHERE rank_name = 'Starter';
UPDATE packages SET rank_order = 2 WHERE rank_name = 'Newbie';
UPDATE packages SET rank_order = 3 WHERE rank_name = 'Bronze';
```

---

## Emergency Contacts and Resources

### Critical Information

**Hostinger Support:**
- Live Chat: Available 24/7 in Hostinger panel
- Email: support@hostinger.com
- Phone: Check your Hostinger dashboard

**Backup Locations:**
- Primary: Local machine (`/backups/` folder)
- Secondary: Cloud storage (Google Drive, Dropbox)
- Tertiary: External hard drive

### Useful Commands Reference

```bash
# Check deployment status
php artisan --version
php artisan list | grep cache

# View real-time logs
tail -f storage/logs/laravel.log

# Check PHP version
php -v

# Check file permissions
ls -la app/Services/

# Check database connection
php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';"
```

---

## Deployment Timeline Summary

### Estimated Total Time: 15-20 minutes

| Phase | Duration | Can Skip? |
|-------|----------|-----------|
| Pre-Deployment Backups | 5 min | ❌ No |
| Local Verification | 10 min | ⚠️ Recommended |
| Enable Maintenance Mode | 1 min | ⚠️ Recommended |
| Upload Files | 2 min | ❌ No |
| Clear Caches | 1 min | ❌ No |
| Quick Test | 2 min | ❌ No |
| Disable Maintenance Mode | 1 min | ⚠️ Recommended |
| Post-Deployment Checks | 5 min | ❌ No |

### Recommended Deployment Windows

**Best Times to Deploy (Low Traffic):**
- Weekdays: 2 AM - 5 AM (your server timezone)
- Weekends: 3 AM - 6 AM (your server timezone)

**Avoid Deploying:**
- During business hours (9 AM - 5 PM)
- During peak shopping hours (12 PM - 2 PM, 7 PM - 9 PM)
- On Mondays (busiest day for most sites)
- During promotional periods

---

## Final Pre-Deployment Checklist

Print this and check off each item:

### Before Deployment Day
- [ ] Phase 1 deployed and verified in production
- [ ] Read this guide completely
- [ ] Understand each step
- [ ] Tested Phase 2 in local environment
- [ ] All verification tests pass locally
- [ ] Rollback procedure understood
- [ ] Team members informed
- [ ] Deployment window scheduled

### Deployment Day Preparation
- [ ] Full backup completed (database + files)
- [ ] Backup verified by test restore
- [ ] Backup files stored in 3 locations
- [ ] SSH/FTP access verified
- [ ] Low traffic time confirmed
- [ ] All required files ready for upload
- [ ] Emergency rollback plan ready

### During Deployment
- [ ] Maintenance mode enabled
- [ ] Final live backup taken
- [ ] RankComparisonService.php uploaded
- [ ] MLMCommissionService.php replaced
- [ ] Caches cleared
- [ ] Quick functionality test passed
- [ ] Maintenance mode disabled
- [ ] Immediate verification completed

### Post-Deployment
- [ ] All URLs accessible
- [ ] Rank-aware logs appearing
- [ ] Test order processed correctly
- [ ] Commission amounts follow rank rules
- [ ] No errors in logs
- [ ] Existing features working
- [ ] Performance normal
- [ ] 24-hour monitoring scheduled
- [ ] Team notified of successful deployment

---

## Success Criteria

Your deployment is successful when:

✅ **Zero Disruption**
- All existing MLM commissions still working
- No orders failing
- No payment processing issues
- Site performance maintained

✅ **New Features Working**
- Rank-aware commission logs appearing
- Test order follows rank rules
- Higher rank → Lower rate (Rule 1)
- Lower rank → Own rate (Rule 2)
- Same rank → Standard rate (Rule 3)

✅ **No Errors**
- No "Class not found" errors
- No commission calculation errors
- No database errors
- Logs clean of critical errors

✅ **User Experience Unchanged**
- No user complaints
- No broken features reported
- Commissions being distributed
- No visible errors to users

---

## Post-Deployment Communication

### To Team:

```
Deployment Status: ✅ SUCCESSFUL

Phase 2: Rank-Aware MLM Commission System

- Deployment completed: [TIME]
- Duration: [MINUTES]
- Issues encountered: None / [LIST]
- Rollback needed: No
- Current status: All systems operational

New Features:
- MLM commissions now follow rank-based rules
- Higher ranks get lower rates for lower-rank buyers (fair play)
- Lower ranks motivated to advance (same rate regardless of buyer)
- Comprehensive logging for audit trail

Monitoring: Active for next 24 hours
Next steps: Monitor commission amounts and user feedback
```

---

## Need Help?

If you encounter issues during deployment:

1. **Don't panic** - You have backups
2. **Check the logs** - `storage/logs/laravel.log`
3. **Consult this guide** - See Common Issues section
4. **Rollback if needed** - Follow Rollback Procedures
5. **Contact support** - Hostinger or your development team

**Remember:** Rollback restores old commission logic immediately. No data is lost.

---

**Good luck with your deployment! 🚀**

*This guide was created specifically for deploying Rank System Phase 2 (Rank-Aware MLM Commission Calculation) to Hostinger production environments, ensuring zero disruption and maximum safety.*
