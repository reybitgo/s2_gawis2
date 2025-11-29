# Rank System Phase 1 - Production Deployment Guide (Hostinger)

## ⚠️ CRITICAL: Production Deployment with Existing Users

This guide ensures **zero disruption** to your existing production system and users when deploying the Rank System Phase 1 features.

---

## Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Backup Strategy](#backup-strategy)
3. [Local Environment Verification](#local-environment-verification)
4. [Staging Environment Testing](#staging-environment-testing)
5. [Production Deployment Steps](#production-deployment-steps)
6. [Post-Deployment Verification](#post-deployment-verification)
7. [Rollback Procedures](#rollback-procedures)
8. [Common Issues and Solutions](#common-issues-and-solutions)

---

## Pre-Deployment Checklist

### ✅ Before You Start

- [ ] **Backup Complete** - Full database and files backup
- [ ] **Local Testing** - All tests pass in local environment
- [ ] **Staging Tested** - Deployed and tested in staging environment
- [ ] **Maintenance Window** - Scheduled low-traffic time
- [ ] **Rollback Plan** - Tested rollback procedure
- [ ] **Team Notification** - Stakeholders informed
- [ ] **User Communication** - Users notified if needed

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
- Database backup tool (phpMyAdmin, Adminer, or command line)
- FTP/SFTP client (FileZilla or Hostinger File Manager)
- Local development environment (Laragon)

---

## Backup Strategy

### CRITICAL: Triple Backup Before Deployment

#### Backup 1: Full Database Backup

**Via phpMyAdmin/Adminer:**
1. Login to your Hostinger control panel
2. Navigate to phpMyAdmin or Adminer
3. Select your database (e.g., `u123456789_gawis2`)
4. Click "Export"
5. Choose "Custom" export method
6. Select ALL tables
7. Format: SQL
8. Download the backup file

**Save as:** `gawis2_backup_YYYYMMDD_HHMM_before_rank.sql`

**Via SSH (if available):**
```bash
# Login to Hostinger via SSH
ssh u123456789@your-domain.com

# Navigate to your directory
cd domains/your-domain.com/public_html

# Create backup
mysqldump -u dbuser -p dbname > gawis2_backup_$(date +%Y%m%d_%H%M%S)_before_rank.sql

# Download to local machine (from your local terminal)
scp u123456789@your-domain.com:domains/your-domain.com/public_html/gawis2_backup_*.sql ./
```

#### Backup 2: Application Files Backup

**Via File Manager:**
1. In Hostinger control panel → File Manager
2. Navigate to `public_html` (or your Laravel root)
3. Right-click → Compress → Create ZIP
4. Download the ZIP file

**Save as:** `gawis2_files_YYYYMMDD_HHMM_before_rank.zip`

**Via SSH:**
```bash
# Create compressed backup
tar -czf gawis2_files_$(date +%Y%m%d_%H%M%S)_before_rank.tar.gz \
  --exclude='vendor' \
  --exclude='node_modules' \
  --exclude='storage/framework/cache' \
  --exclude='storage/framework/sessions' \
  --exclude='storage/logs' \
  .

# Download to local
scp u123456789@your-domain.com:domains/your-domain.com/public_html/gawis2_files_*.tar.gz ./
```

#### Backup 3: Critical Data Export

**Export critical tables separately (extra safety):**
```sql
-- In phpMyAdmin, run these exports separately
SELECT * FROM users ORDER BY id;                    -- Export as CSV
SELECT * FROM packages ORDER BY id;                 -- Export as CSV
SELECT * FROM orders WHERE payment_status = 'paid'; -- Export as CSV
SELECT * FROM mlm_settings ORDER BY package_id;     -- Export as CSV
```

**Save as CSV files:**
- `users_backup_YYYYMMDD.csv`
- `packages_backup_YYYYMMDD.csv`
- `orders_backup_YYYYMMDD.csv`
- `mlm_settings_backup_YYYYMMDD.csv`

#### Verify Backup Integrity

```bash
# Local verification (test restore on local)
mysql -u root local_test_db < gawis2_backup_YYYYMMDD_HHMM_before_rank.sql

# Check if all tables restored
mysql -u root -e "SHOW TABLES FROM local_test_db;"

# If successful, you have a valid backup
```

**✅ Backup Checklist:**
- [ ] Full SQL database dump downloaded
- [ ] Application files ZIP/TAR downloaded
- [ ] Critical data exported to CSV
- [ ] Backup verified by test restore
- [ ] Backups stored in 3 locations (local, cloud, external drive)

---

## Local Environment Verification

### Step 1: Fresh Database with Production Data

```bash
# In your local Laragon environment

# 1. Create fresh test database
mysql -u root -e "CREATE DATABASE gawis2_prod_test;"

# 2. Import production backup
mysql -u root gawis2_prod_test < gawis2_backup_YYYYMMDD_HHMM_before_rank.sql

# 3. Update .env to use test database
DB_DATABASE=gawis2_prod_test

# 4. Clear cache
php artisan config:clear
php artisan cache:clear
```

### Step 2: Run Rank Migrations on Production Copy

```bash
# This simulates production deployment

# Run ONLY the new rank migrations
php artisan migrate --path=database/migrations/2025_11_27_141155_add_rank_fields_to_users_table.php
php artisan migrate --path=database/migrations/2025_11_27_141211_add_rank_fields_to_packages_table.php
php artisan migrate --path=database/migrations/2025_11_27_141213_create_rank_advancements_table.php
php artisan migrate --path=database/migrations/2025_11_27_141215_create_direct_sponsors_tracker_table.php
```

### Step 3: Verify Existing Data Intact

```bash
# IMPORTANT: First, assign ranks to existing users who purchased packages
php assign_ranks_to_users.php
```

```bash
php artisan tinker
```

```php
// Verify user count unchanged
$userCountBefore = /* note from production */;
$userCountAfter = \App\Models\User::count();
echo "Users: Before={$userCountBefore}, After={$userCountAfter}\n";

// Verify package count unchanged (except new rank packages)
$packageCountBefore = /* note from production */;
$packageCountAfter = \App\Models\Package::count();
echo "Packages: Before={$packageCountBefore}, After={$packageCountAfter}\n";

// Verify orders intact
$orderCount = \App\Models\Order::where('payment_status', 'paid')->count();
echo "Paid Orders: {$orderCount}\n";

// Check if existing users got ranks assigned
$usersWithRanks = \App\Models\User::whereNotNull('current_rank')->count();
echo "Users with ranks assigned: {$usersWithRanks}\n";

// Verify existing MLM settings unchanged
$mlmSettingsCount = \App\Models\MlmSetting::count();
echo "MLM Settings: {$mlmSettingsCount}\n";
```

### Step 4: Test Existing Functionality

**Test these critical functions still work:**
```bash
# 1. User login
# 2. Package purchase flow
# 3. Order placement
# 4. MLM commission calculation (if you have test script)
# 5. Wallet operations
# 6. Admin dashboard
```

### Step 5: Run Verification Script

```bash
# Should pass 100%
php verify_phase1_complete.php
```

**✅ Local Verification Checklist:**
- [ ] Migrations run successfully on production copy
- [ ] All existing users intact
- [ ] All existing packages intact
- [ ] All existing orders intact
- [ ] Existing users with packages got ranks assigned
- [ ] New rank packages created
- [ ] Existing functionality works
- [ ] Verification script passes 100%

---

## Staging Environment Testing

### ⚠️ HIGHLY RECOMMENDED Before Production

If you have a staging/testing subdomain on Hostinger:

#### Step 1: Setup Staging

```bash
# In Hostinger File Manager or SSH
# Create subdomain: staging.your-domain.com

# Copy production files to staging
cp -r /domains/your-domain.com/public_html/* /domains/staging.your-domain.com/public_html/

# Create staging database
mysql -u dbuser -p -e "CREATE DATABASE staging_gawis2;"

# Import production data to staging
mysql -u dbuser -p staging_gawis2 < gawis2_backup_YYYYMMDD_HHMM_before_rank.sql

# Update staging .env
nano /domains/staging.your-domain.com/public_html/.env
# Change:
# DB_DATABASE=staging_gawis2
# APP_ENV=staging
# APP_URL=https://staging.your-domain.com
```

#### Step 2: Deploy to Staging

Follow the same deployment steps (see Production Deployment section below) but on staging environment.

#### Step 3: Test on Staging

1. **Access staging site:** `https://staging.your-domain.com`
2. **Test all existing features:**
   - User registration/login
   - Package purchases
   - Order flow
   - Admin dashboard
   - MLM commissions (if applicable)
3. **Test new rank features:**
   - User gets rank after package purchase
   - Admin can't change rank package names
   - Rank appears in user profile
4. **Performance test:**
   - Load times acceptable
   - No errors in logs
   - Database queries optimized

**✅ Staging Test Checklist:**
- [ ] Deployment completed on staging
- [ ] All existing features working
- [ ] New rank features working
- [ ] No errors in logs
- [ ] Performance acceptable
- [ ] Tested with real production data copy

---

## Production Deployment Steps

### 🚨 DEPLOYMENT DAY PROTOCOL

#### Pre-Deployment (15 minutes before)

1. **Notify users (optional):**
```
"System maintenance scheduled for [TIME]. Expected duration: 15 minutes.
All services will remain available during the upgrade."
```

2. **Enable Maintenance Mode:**

**Via SSH:**
```bash
cd /domains/your-domain.com/public_html
php artisan down --secret="upgrade-2025" --retry=60
```

**Note:** With `--secret`, you can bypass maintenance mode by visiting: `https://your-domain.com/upgrade-2025`

**Via File Manager:**
Create file: `public_html/storage/framework/down`
```json
{
    "retry": 60,
    "secret": "upgrade-2025"
}
```

3. **Final backup (live state):**
```bash
# Quick database snapshot
mysqldump -u dbuser -p dbname > final_backup_$(date +%Y%m%d_%H%M%S).sql
```

#### Deployment Phase 1: Upload Files (5 minutes)

**Upload these files to production:**

1. **Model Files:**
```
app/Models/User.php (updated)
app/Models/Package.php (updated)
app/Models/RankAdvancement.php (NEW)
app/Models/DirectSponsorsTracker.php (NEW)
```

2. **Migration Files:**
```
database/migrations/2025_11_27_141155_add_rank_fields_to_users_table.php
database/migrations/2025_11_27_141211_add_rank_fields_to_packages_table.php
database/migrations/2025_11_27_141213_create_rank_advancements_table.php
database/migrations/2025_11_27_141215_create_direct_sponsors_tracker_table.php
```

3. **Seeder Files:**
```
database/seeders/PackageSeeder.php (updated)
database/seeders/DatabaseSeeder.php (updated)
```

4. **View Files:**
```
resources/views/admin/packages/edit.blade.php (updated)
```

5. **Controller Files:**
```
app/Http/Controllers/Admin/AdminPackageController.php (updated)
```

**Via File Manager:**
- Upload files one by one, overwriting existing
- Verify file sizes match local versions

**Via FTP:**
```bash
# Use FileZilla or similar
# Connect to Hostinger FTP
# Upload files maintaining directory structure
```

**Via SSH (fastest):**
```bash
# From local machine, create deployment package
tar -czf rank_phase1_files.tar.gz \
  app/Models/User.php \
  app/Models/Package.php \
  app/Models/RankAdvancement.php \
  app/Models/DirectSponsorsTracker.php \
  database/migrations/2025_11_27_14*.php \
  database/seeders/PackageSeeder.php \
  database/seeders/DatabaseSeeder.php \
  resources/views/admin/packages/edit.blade.php \
  app/Http/Controllers/Admin/AdminPackageController.php

# Upload to server
scp rank_phase1_files.tar.gz u123456789@your-domain.com:domains/your-domain.com/public_html/

# SSH into server and extract
ssh u123456789@your-domain.com
cd domains/your-domain.com/public_html
tar -xzf rank_phase1_files.tar.gz
rm rank_phase1_files.tar.gz
```

#### Deployment Phase 2: Run Migrations (5 minutes)

**Via SSH:**
```bash
cd /domains/your-domain.com/public_html

# Clear all caches first
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Run migrations (CRITICAL STEP)
php artisan migrate --force

# Expected output:
# 2025_11_27_141155_add_rank_fields_to_users_table ............... DONE
# 2025_11_27_141211_add_rank_fields_to_packages_table ............ DONE
# 2025_11_27_141213_create_rank_advancements_table ............... DONE
# 2025_11_27_141215_create_direct_sponsors_tracker_table ......... DONE

# Check migration status
php artisan migrate:status
```

**Via File Manager (if no SSH):**

Use Hostinger's PHP Cron Job or Terminal feature:

1. In Hostinger Control Panel → Advanced → Cron Jobs
2. Create a one-time cron job:
```bash
cd /domains/your-domain.com/public_html && php artisan migrate --force
```
3. Set to run immediately
4. Check logs to verify completion

**⚠️ If migrations fail:**
```bash
# Check error logs
tail -f storage/logs/laravel.log

# Check migration status
php artisan migrate:status

# If stuck, see Rollback section
```

#### Deployment Phase 3: Seed Rank Packages (3 minutes)

**IMPORTANT:** Only run if this is a fresh rank system deployment. Skip if rank packages already exist.

```bash
# Check if rank packages exist
php artisan tinker
>>> \App\Models\Package::where('rank_name', 'Starter')->exists()

# If false, run seeder:
php artisan db:seed --class=PackageSeeder --force

# Expected output:
# ✅ Created Starter Package (Rank 1)
# ✅ Created Newbie Package (Rank 2)
# ✅ Created Bronze Package (Rank 3)
# ✅ Set rank progression
# ✅ Created MLM commission structures
```

**⚠️ CRITICAL:** If you already have packages named "Starter", "Newbie", or "Bronze", **update them instead of creating new ones:**

```bash
php artisan tinker
```

```php
// Update existing Starter package
$starter = \App\Models\Package::where('name', 'Starter Package')->first();
if ($starter) {
    $starter->update([
        'rank_name' => 'Starter',
        'rank_order' => 1,
        'required_direct_sponsors' => 5,
        'is_rankable' => true,
        'next_rank_package_id' => null, // Will set after creating others
    ]);
    echo "✅ Starter Package updated\n";
}

// Repeat for Newbie and Bronze...
```

#### Deployment Phase 4: Verify Installation (2 minutes)

```bash
# Quick verification via tinker
php artisan tinker
```

```php
// 1. Check tables exist
echo \Schema::hasTable('rank_advancements') ? "✅ rank_advancements\n" : "❌ MISSING\n";
echo \Schema::hasTable('direct_sponsors_tracker') ? "✅ direct_sponsors_tracker\n" : "❌ MISSING\n";

// 2. Check columns exist
echo \Schema::hasColumn('users', 'current_rank') ? "✅ users.current_rank\n" : "❌ MISSING\n";
echo \Schema::hasColumn('packages', 'rank_name') ? "✅ packages.rank_name\n" : "❌ MISSING\n";

// 3. Check rank packages
$rankPackages = \App\Models\Package::whereNotNull('rank_name')->pluck('rank_name');
echo "Rank Packages: " . $rankPackages->implode(', ') . "\n";

// 4. Check existing users got ranks
$usersWithRanks = \App\Models\User::whereNotNull('current_rank')->count();
$totalUsers = \App\Models\User::count();
echo "Users with ranks: {$usersWithRanks}/{$totalUsers}\n";

// 5. Check MLM settings intact
$mlmCount = \App\Models\MlmSetting::count();
echo "MLM Settings: {$mlmCount}\n";

// If all checks pass, exit tinker
exit
```

#### Deployment Phase 5: Clear Caches (1 minute)

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers if using queues
# php artisan queue:restart
```

#### Deployment Phase 6: Disable Maintenance Mode (immediate)

```bash
# Bring site back online
php artisan up
```

**Or via File Manager:**
Delete file: `public_html/storage/framework/down`

#### Deployment Phase 7: Immediate Verification (5 minutes)

**Test these immediately:**

1. **Homepage loads:** `https://your-domain.com`
2. **User login works:** Test with existing user account
3. **Admin dashboard loads:** `https://your-domain.com/admin`
4. **Existing package visible:** Check package list
5. **No errors in logs:**
```bash
tail -f storage/logs/laravel.log
# Should show no errors
```

**✅ Deployment Checklist:**
- [ ] Maintenance mode enabled
- [ ] Final backup taken
- [ ] Files uploaded successfully
- [ ] Migrations ran successfully (all 5 completed)
- [ ] Rank packages created/updated
- [ ] Verification checks passed
- [ ] Caches cleared and optimized
- [ ] Maintenance mode disabled
- [ ] Site accessible and working
- [ ] No errors in logs

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
✅ Admin Packages:  https://your-domain.com/admin/packages
```

**All should load without errors.**

#### 2. Database Integrity Check

**Via phpMyAdmin/Adminer:**
```sql
-- Check user count (should be unchanged)
SELECT COUNT(*) as total_users FROM users;

-- Check how many users got ranks assigned
SELECT 
    COUNT(*) as users_with_ranks,
    current_rank,
    COUNT(*) as count
FROM users 
WHERE current_rank IS NOT NULL 
GROUP BY current_rank;

-- Check new tables exist and are empty (initially)
SELECT COUNT(*) as rank_advancements FROM rank_advancements;
SELECT COUNT(*) as sponsor_tracking FROM direct_sponsors_tracker;

-- Verify packages structure
SELECT 
    id,
    name,
    rank_name,
    rank_order,
    required_direct_sponsors,
    is_rankable,
    next_rank_package_id
FROM packages
WHERE rank_name IS NOT NULL
ORDER BY rank_order;

-- Check existing MLM settings unchanged
SELECT package_id, level, commission_amount 
FROM mlm_settings 
ORDER BY package_id, level;
```

**Expected Results:**
- Total users unchanged from before deployment
- Users who purchased packages now have `current_rank`
- New tables exist (empty is okay)
- 3 rank packages configured (Starter, Newbie, Bronze)
- Existing MLM settings intact

#### 3. Feature Testing

**Test with a real admin account:**

1. **Edit Rank Package:**
   - Go to Admin → Packages → Edit "Starter Package"
   - Try to change the name (should be readonly with lock message)
   - ✅ Verify you cannot change the name

2. **View User Profile:**
   - Go to Admin → Users → View a user who purchased a package
   - ✅ Verify their rank shows (e.g., "Starter")

3. **Test New Package Purchase:**
   - Create a test user (or use existing test account)
   - Purchase the Starter package
   - ✅ Verify user's rank updates to "Starter"

#### 4. Log Review

**Check error logs:**
```bash
# Via SSH
tail -100 storage/logs/laravel.log | grep ERROR

# Or download and review in text editor
```

**No critical errors should appear.**

### Extended Monitoring (First 24 Hours)

#### Monitor These Metrics:

1. **User Activity:**
   - Are users logging in successfully? ✅
   - Are new registrations working? ✅
   - Are package purchases completing? ✅

2. **Database Performance:**
   - Query response times normal? ✅
   - No deadlocks or locks? ✅

3. **Error Rates:**
   - Check logs every 2 hours
   - Monitor for rank-related errors

4. **User Reports:**
   - Any complaints about broken features?
   - Any missing data reports?

#### Key Queries for Monitoring:

```sql
-- Users who purchased packages today
SELECT COUNT(*) as purchases_today
FROM orders 
WHERE DATE(created_at) = CURDATE() 
AND payment_status = 'paid';

-- Ranks assigned today
SELECT COUNT(*) as ranks_assigned_today
FROM users 
WHERE DATE(rank_updated_at) = CURDATE();

-- Check for users without ranks but with packages
SELECT u.id, u.username, u.current_rank, COUNT(o.id) as orders
FROM users u
LEFT JOIN orders o ON u.id = o.user_id AND o.payment_status = 'paid'
GROUP BY u.id
HAVING orders > 0 AND u.current_rank IS NULL;
```

**✅ Post-Deployment Verification Checklist:**
- [ ] All URLs accessible
- [ ] User count unchanged
- [ ] Existing users got ranks assigned
- [ ] New tables created
- [ ] Rank packages configured correctly
- [ ] Package name protection working
- [ ] Test purchase assigns rank correctly
- [ ] No errors in logs
- [ ] Performance normal
- [ ] 24-hour monitoring scheduled

---

## Rollback Procedures

### 🚨 WHEN TO ROLLBACK

Rollback immediately if:
- ❌ Critical errors preventing user access
- ❌ Data loss detected
- ❌ Performance severely degraded
- ❌ Payment processing broken
- ❌ More than 5% users reporting issues

### ROLLBACK METHOD 1: Quick Rollback (10 minutes)

**If migrations completed but issues found:**

```bash
# Enable maintenance mode
php artisan down

# Rollback ONLY the 4 rank migrations
php artisan migrate:rollback --step=4

# Verify rollback
php artisan migrate:status

# Clear caches
php artisan config:clear
php artisan cache:clear

# Test site
# If working, disable maintenance mode
php artisan up
```

**Expected result:** Rank columns and tables removed, system back to pre-deployment state.

### ROLLBACK METHOD 2: Full Database Restore (20 minutes)

**If severe data corruption:**

```bash
# Enable maintenance mode
php artisan down

# In phpMyAdmin or via SSH:
# 1. Drop current database (⚠️ CAREFUL!)
mysql -u dbuser -p -e "DROP DATABASE current_db;"

# 2. Recreate database
mysql -u dbuser -p -e "CREATE DATABASE current_db;"

# 3. Restore from backup
mysql -u dbuser -p current_db < gawis2_backup_YYYYMMDD_HHMM_before_rank.sql

# 4. Verify restoration
mysql -u dbuser -p current_db -e "SELECT COUNT(*) FROM users;"

# 5. Clear caches
php artisan config:clear
php artisan cache:clear

# 6. Test site functionality
php artisan up
```

### ROLLBACK METHOD 3: File Restore (if code issues)

**If model/controller code causing issues:**

```bash
# Restore from file backup
cd /domains/your-domain.com
mv public_html public_html_broken
unzip gawis2_files_YYYYMMDD_HHMM_before_rank.zip -d public_html

# Or via tar
tar -xzf gawis2_files_YYYYMMDD_HHMM_before_rank.tar.gz -C public_html

# Rollback migrations
cd public_html
php artisan migrate:rollback --step=4

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan up
```

### PARTIAL ROLLBACK: Keep Schema, Remove Rank Packages

**If you want to keep the structure but disable rank features:**

```sql
-- Remove rank packages (keep seeded data intact)
UPDATE packages 
SET rank_name = NULL, 
    rank_order = 1, 
    required_direct_sponsors = 0,
    is_rankable = 0,
    next_rank_package_id = NULL
WHERE rank_name IN ('Starter', 'Newbie', 'Bronze');

-- Clear user ranks (optional)
UPDATE users 
SET current_rank = NULL, 
    rank_package_id = NULL, 
    rank_updated_at = NULL
WHERE current_rank IS NOT NULL;
```

**This keeps tables but disables features.**

### Post-Rollback Verification

```bash
php artisan tinker
```

```php
// Verify user count
$users = \App\Models\User::count();
echo "Total users: {$users}\n";

// Verify orders intact
$orders = \App\Models\Order::where('payment_status', 'paid')->count();
echo "Paid orders: {$orders}\n";

// Check if rank tables removed (after Method 1)
echo \Schema::hasTable('rank_advancements') ? "⚠️ Still exists\n" : "✅ Removed\n";

// Check if rank columns removed (after Method 1)
echo \Schema::hasColumn('users', 'current_rank') ? "⚠️ Still exists\n" : "✅ Removed\n";
```

**✅ Rollback Checklist:**
- [ ] Maintenance mode enabled during rollback
- [ ] Backup verified before restore
- [ ] Database restored successfully
- [ ] User count matches pre-deployment
- [ ] Order count matches pre-deployment
- [ ] Site functionality working
- [ ] No errors in logs
- [ ] Users can login and purchase
- [ ] Maintenance mode disabled
- [ ] Users notified of temporary issue (if applicable)

---

## Common Issues and Solutions

### Issue 1: Migration Fails - Foreign Key Constraint Error

**Error:**
```
SQLSTATE[HY000]: General error: 1215 Cannot add foreign key constraint
```

**Solution:**
```bash
# Check if referenced table exists
php artisan tinker
>>> \Schema::hasTable('packages')
>>> \Schema::hasTable('users')

# If missing, run base migrations first
php artisan migrate

# Then run rank migrations
php artisan migrate --path=database/migrations/2025_11_27_141155_add_rank_fields_to_users_table.php
```

### Issue 2: Migration Fails - Column Already Exists

**Error:**
```
SQLSTATE[42S21]: Column already exists: 1060 Duplicate column name 'current_rank'
```

**Solution:**
```bash
# Check if columns already exist
php artisan tinker
>>> \Schema::hasColumn('users', 'current_rank')

# If true, migration already ran
# Check migration status
php artisan migrate:status

# Mark as completed without running
# (Only if you're sure it's already done)
```

### Issue 3: Existing Users Not Getting Ranks

**Symptom:**
```sql
SELECT COUNT(*) FROM users WHERE current_rank IS NULL AND id IN (
    SELECT DISTINCT user_id FROM orders WHERE payment_status = 'paid'
);
-- Returns more than 0
```

**Solution:**
```bash
# Manually assign ranks via tinker
php artisan tinker
```

```php
$users = \App\Models\User::whereHas('orders', function($q) {
    $q->where('payment_status', 'paid')->whereHas('orderItems.package');
})->whereNull('current_rank')->get();

foreach ($users as $user) {
    $user->updateRank();
    echo "Updated: {$user->username}\n";
}
```

### Issue 4: Package Name Still Editable in Admin

**Symptom:** Admin can change rank package names despite protection

**Solution:**
```bash
# Clear view cache
php artisan view:clear

# Verify controller file uploaded
# Check: app/Http/Controllers/Admin/AdminPackageController.php
# Should have validation code

# Verify view file uploaded
# Check: resources/views/admin/packages/edit.blade.php
# Should have readonly logic

# Re-upload files if needed
```

### Issue 5: Performance Degradation

**Symptom:** Site slower after deployment

**Solution:**
```bash
# Check for missing indexes
php artisan tinker
```

```php
// Manually add missing indexes if needed
\DB::statement('ALTER TABLE users ADD INDEX idx_current_rank (current_rank)');
\DB::statement('ALTER TABLE packages ADD INDEX idx_rank_order (rank_order)');
```

```bash
# Optimize tables
mysql -u dbuser -p -e "OPTIMIZE TABLE users, packages, rank_advancements, direct_sponsors_tracker;"

# Clear and rebuild caches
php artisan optimize:clear
php artisan optimize
```

### Issue 6: Duplicate Package Names After Seeding

**Symptom:** Multiple "Starter Package" entries

**Solution:**
```sql
-- Check for duplicates
SELECT name, COUNT(*) as count 
FROM packages 
GROUP BY name 
HAVING count > 1;

-- If duplicates found, identify the correct ones
SELECT id, name, rank_name, created_at 
FROM packages 
WHERE name LIKE '%Starter%' 
ORDER BY created_at;

-- Delete the duplicates (keep the one with rank_name)
-- ⚠️ CAREFUL - verify IDs first
DELETE FROM packages 
WHERE id IN (duplicate_ids_here) 
AND rank_name IS NULL;
```

---

## Emergency Contacts and Resources

### Critical Information

**Hostinger Support:**
- Live Chat: Available 24/7 in Hostinger panel
- Email: support@hostinger.com
- Phone: Check your Hostinger dashboard

**Database Access:**
- phpMyAdmin: `your-domain.com/phpmyadmin` or via Hostinger panel
- Adminer: (if installed) `your-domain.com/adminer.php`

**Backup Locations:**
- Primary: Local machine (`/backups/` folder)
- Secondary: Cloud storage (Google Drive, Dropbox)
- Tertiary: External hard drive

### Useful Commands Reference

```bash
# Check site status
curl -I https://your-domain.com

# Check database connection
php artisan tinker --execute="DB::connection()->getPdo();"

# View recent errors
tail -50 storage/logs/laravel.log

# Check disk space
df -h

# Check database size
mysql -u dbuser -p -e "SELECT table_schema 'Database', 
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) 'Size MB' 
    FROM information_schema.tables 
    WHERE table_schema = 'your_database' 
    GROUP BY table_schema;"
```

---

## Deployment Timeline Summary

### Estimated Total Time: 30-45 minutes

| Phase | Duration | Can Skip? |
|-------|----------|-----------|
| Pre-Deployment Backups | 10 min | ❌ No |
| Local Verification | 15 min | ⚠️ Recommended |
| Staging Testing | 30 min | ⚠️ Highly Recommended |
| Enable Maintenance Mode | 1 min | ⚠️ Recommended |
| Upload Files | 5 min | ❌ No |
| Run Migrations | 5 min | ❌ No |
| Seed Rank Packages | 3 min | ⚠️ If needed |
| Verification | 2 min | ❌ No |
| Clear Caches | 1 min | ❌ No |
| Disable Maintenance Mode | 1 min | ⚠️ Recommended |
| Post-Deployment Checks | 10 min | ❌ No |

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
- [ ] Read this guide completely
- [ ] Understand each step
- [ ] Tested in local environment
- [ ] Tested in staging environment (if available)
- [ ] All verification tests pass locally
- [ ] Rollback procedure understood and tested
- [ ] Team members informed
- [ ] Deployment window scheduled
- [ ] User notification prepared (if needed)

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
- [ ] Files uploaded successfully
- [ ] Migrations completed (all 5)
- [ ] Rank packages created/updated
- [ ] Verification checks passed
- [ ] Caches cleared
- [ ] Maintenance mode disabled
- [ ] Immediate verification completed

### Post-Deployment
- [ ] All URLs accessible
- [ ] No errors in logs
- [ ] User/package counts match
- [ ] Rank assignment working
- [ ] Package name protection working
- [ ] Test purchase successful
- [ ] Existing features working
- [ ] 24-hour monitoring scheduled
- [ ] Team notified of successful deployment

---

## Success Criteria

Your deployment is successful when:

✅ **Zero Data Loss**
- All existing users intact
- All existing orders intact
- All existing packages intact
- All existing MLM settings intact

✅ **New Features Working**
- Users get ranks when purchasing packages
- Admin can't modify rank package names
- Rank appears in user profiles
- Rank progression chain functional

✅ **Existing Features Working**
- User login works
- Package purchases work
- Order processing works
- MLM commissions calculate correctly
- Admin dashboard accessible

✅ **Performance Maintained**
- Page load times similar to pre-deployment
- Database queries optimized
- No new errors in logs

✅ **User Experience Unchanged**
- No user complaints
- No broken features reported
- No visible errors to users

---

## Post-Deployment Communication

### To Users (if needed):

```
Subject: System Upgrade Complete

Dear Users,

We've successfully completed a system upgrade to enhance your experience. 
All your data is safe and all features are working normally.

New Features:
- Enhanced rank tracking system
- Improved package management

If you experience any issues, please contact support.

Thank you for your patience.
```

### To Team:

```
Deployment Status: ✅ SUCCESSFUL

- Deployment completed: [TIME]
- Duration: [MINUTES]
- Issues encountered: None / [LIST]
- Rollback needed: No
- Current status: All systems operational

Monitoring: Active for next 24 hours
Next steps: Continue Phase 2 planning
```

---

## Need Help?

If you encounter issues during deployment:

1. **Don't panic** - You have backups
2. **Check the logs** - `storage/logs/laravel.log`
3. **Consult this guide** - See Common Issues section
4. **Rollback if needed** - Follow Rollback Procedures
5. **Contact support** - Hostinger or your development team

**Remember:** Your backups ensure you can always restore to pre-deployment state.

---

**Good luck with your deployment! 🚀**

*This guide was created specifically for deploying Rank System Phase 1 to production environments with existing users, ensuring zero disruption and maximum safety.*
