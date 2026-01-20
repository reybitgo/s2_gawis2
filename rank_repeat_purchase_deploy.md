# Deployment Guide: Dual-Path Rank Advancement System

## Executive Summary

This guide provides step-by-step instructions to migrate your existing production recruitment-based ranking system to the new Dual-Path Rank Advancement System with PPV/GPV features.

**Estimated Downtime:** 5-15 minutes (depending on database size)
**Deployment Risk:** Low (with proper backup)
**Rollback Time:** 10-20 minutes

---

## Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [System Architecture Changes](#system-architecture-changes)
3. [Deployment Strategy](#deployment-strategy)
4. [Phase 1: Pre-Deployment Preparation](#phase-1-pre-deployment-preparation)
5. [Phase 2: Code Deployment](#phase-2-code-deployment)
6. [Phase 3: Database Migration](#phase-3-database-migration)
7. [Phase 4: Data Migration](#phase-4-data-migration)
8. [Phase 5: Post-Deployment Verification](#phase-5-post-deployment-verification)
9. [Phase 6: User Communication](#phase-6-user-communication)
10. [Rollback Procedure](#rollback-procedure)
11. [Monitoring & Troubleshooting](#monitoring--troubleshooting)

---

## Pre-Deployment Checklist

### Infrastructure Readiness

- [ ] **Staging Environment Available**
  - [ ] Clone production database to staging
  - [ ] Test deployment on staging first
  - [ ] Verify all functionality works

- [ ] **Backup Strategy Confirmed**
  - [ ] Database backup scheduled (before deployment)
  - [ ] Code backup (git tag)
  - [ ] File system backup (uploads, etc.)

- [ ] **Team Notification**
  - [ ] Development team notified
  - [ ] Support team briefed on changes
  - [ ] Maintenance window scheduled with stakeholders
  - [ ] User announcement prepared

### Code Readiness

- [ ] **All Migrations Reviewed**
  - [ ] New migrations tested locally
  - [ ] Migration order verified
  - [ ] Rollback migrations prepared

- [ ] **Tests Passing**
  - [ ] Unit tests: 22/22 passing
  - [ ] Feature tests: 3/3 passing
  - [ ] Integration tests completed

- [ ] **Documentation Updated**
  - [ ] AGENTS.md updated
  - [ ] User guide prepared
  - [ ] Admin training completed

### Production Readiness

- [ ] **Database Backups Created**
  - [ ] Full database dump: `mysqldump`
  - [ ] Backup verified (can restore)
  - [ ] Backup stored in secure location

- [ ] **Application Assets**
  - [ ] `npm run build` completed
  - [ ] Assets uploaded to CDN (if applicable)
  - [ ] Cache cleared

- [ ] **Configuration**
  - [ ] `.env` production settings verified
  - [ ] Queue workers configured
  - [ ] Scheduler running

---

## System Architecture Changes

### Existing System (Before)

```
Rank Advancement (Single Path):
  - Requirement: required_direct_sponsors (e.g., 2 per rank level)
  - Method: Recruitment-only
  - No PPV/GPV tracking
  - Points not tracked
```

### New System (After)

```
Rank Advancement (Dual Path):
  - Path A: Recruitment-based (required_direct_sponsors)
  - Path B: PV-based (required_sponsors_ppv_gpv + PPV + GPV)

New Features:
  - PPV: Personal Points Volume (user's purchases)
  - GPV: Group Points Volume (user + all downlines)
  - Points audit trail (points_tracker table)
  - Point deduction on cancellation
  - Automatic PPV/GPV reset on rank advancement
```

### Database Schema Changes

#### New Tables
- `points_tracker` - Audit trail for all point transactions

#### Modified Tables
- `packages` - Added PPV/GPV configuration
- `users` - Added PPV/GPV tracking
- `rank_advancements` - Updated advancement_type enum
- `orders` - Added points_credited flag

#### Enum Changes (CRITICAL)
**Existing:**
```php
['purchase', 'sponsorship_reward', 'admin_adjustment']
```

**New:**
```php
['purchase', 'sponsorship_reward', 'admin_adjustment', 'recruitment_based', 'pv_based']
```

**Impact:** Must update enum BEFORE any new rank advancements occur.

---

## Deployment Strategy

### Deployment Type: Blue-Green with Feature Flag

**Why Blue-Green?**
- Zero-downtime deployment possible
- Instant rollback capability
- Reduced risk

**Alternative: Maintenance Window**
- Simpler, less complex infrastructure
- Acceptable for small/medium applications
- Downtime: 5-15 minutes

### Recommended Approach: Staging-First + Maintenance Window

1. **Deploy to Staging**
   - Clone production database
   - Run migrations on staging
   - Test all functionality
   - Verify data integrity

2. **Schedule Production Maintenance**
   - Choose low-traffic period
   - Announce 2 hours in advance
   - Display maintenance page

3. **Deploy to Production**
   - Backup production database
   - Run migrations
   - Deploy code
   - Verify functionality

4. **Remove Maintenance Mode**
   - Enable live traffic
   - Monitor for errors

---

## Phase 1: Pre-Deployment Preparation

### Step 1.1: Create Production Backup

```bash
# SSH into production server
ssh user@your-server.com

# Navigate to application directory
cd /var/www/your-app

# Backup database
mysqldump -u your_db_user -p your_db_name \
  --single-transaction \
  --quick \
  --routines \
  --triggers \
  --events \
  > backup_before_deployment_$(date +%Y%m%d_%H%M%S).sql

# Backup application code
cd /var/www
tar -czf your-app_backup_$(date +%Y%m%d_%H%M%S).tar.gz your-app

# Verify backup file sizes
ls -lh backup_*.sql your-app_backup_*.tar.gz
```

### Step 1.2: Create Git Tag for Rollback

```bash
# On production server
cd /var/www/your-app
git tag pre-dual-path-$(date +%Y%m%d)
git push origin pre-dual-path-$(date +%Y%m%d)
```

### Step 1.3: Prepare Rollback Migration

**Create rollback migration file locally:**

```bash
php artisan make:migration rollback_dual_path_rank_advancement
```

**File:** `database/migrations/XXXX_XX_XX_XXXXXX_rollback_dual_path_rank_advancement.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This is for rollback - do nothing in up()
    }

    public function down(): void
    {
        // Rollback changes
        Schema::dropIfExists('points_tracker');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['current_ppv', 'current_gpv', 'ppv_gpv_updated_at']);
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'required_sponsors_ppv_gpv',
                'ppv_required',
                'gpv_required',
                'rank_pv_enabled'
            ]);
        });

        // Note: Reverting enum is complex - manually restore from backup if needed
    }
};
```

### Step 1.4: Test on Staging Environment

```bash
# Clone production to staging
ssh production-server "mysqldump your_db_name | gzip" | \
  ssh staging-server "gunzip | mysql staging_db_name"

# Deploy to staging
cd /var/www/your-app-staging
git pull origin main
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Test staging
# - Access dashboard
# - Check admin configuration
# - Test rank advancement
# - Verify point tracking
```

### Step 1.5: Prepare Maintenance Page

```bash
# Enable maintenance mode
php artisan down --message="System maintenance in progress. We'll be back soon."

# Or custom maintenance page
php artisan down --render="maintenance.blade"
```

---

## Phase 2: Code Deployment

### Step 2.1: Update Production Code

```bash
# SSH into production server
ssh user@your-server.com

# Navigate to application directory
cd /var/www/your-app

# Pull latest code
git fetch origin
git checkout main
git pull origin main

# Install dependencies (no-dev for production)
composer install --no-dev --optimize-autoloader

# Build frontend assets
npm run build

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 2.2: Verify Deployed Code

```bash
# Check git status
git log -1

# Verify files exist
ls -la app/Services/PointsService.php
ls -la app/Console/Commands/RecalculateGPV.php
ls -la app/Models/PointsTracker.php

# Check migrations
ls -la database/migrations/ | grep -E "ppv|gpv|points_tracker"
```

---

## Phase 3: Database Migration

### Step 3.1: Create Critical Enum Update Migration

**IMPORTANT:** This migration MUST be created and run FIRST before any PPV/GPV operations.

```bash
php artisan make:migration update_rank_advancements_enum_for_dual_path
```

**File:** `database/migrations/XXXX_XX_XX_XXXXXX_update_rank_advancements_enum_for_dual_path.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // IMPORTANT: Modify enum column to add new values
        DB::statement("ALTER TABLE rank_advancements MODIFY COLUMN advancement_type ENUM(
            'purchase',
            'sponsorship_reward',
            'admin_adjustment',
            'recruitment_based',
            'pv_based'
        ) DEFAULT 'purchase'");
    }

    public function down(): void
    {
        // Rollback: Revert enum to original values
        // Note: This will fail if any new values exist in data
        DB::statement("ALTER TABLE rank_advancements MODIFY COLUMN advancement_type ENUM(
            'purchase',
            'sponsorship_reward',
            'admin_adjustment'
        ) DEFAULT 'purchase'");
    }
};
```

### Step 3.2: Run Migrations in Order

```bash
# Run migrations step-by-step to catch errors early

# Step 1: Update enum first (CRITICAL)
php artisan migrate --path=database/migrations/XXXX_XX_XX_XXXXXX_update_rank_advancements_enum_for_dual_path.php

# Step 2: Add PPV/GPV to packages
php artisan migrate --path=database/migrations/2026_01_19_154333_add_ppv_gpv_to_packages_table.php

# Step 3: Add PPV/GPV to users
php artisan migrate --path=database/migrations/2026_01_19_154333_add_ppv_gpv_to_users_table.php

# Step 4: Create points_tracker table
php artisan migrate --path=database/migrations/2026_01_19_154333_create_points_tracker_table.php

# Step 5: Set default PPV/GPV values
php artisan migrate --path=database/migrations/2026_01_19_174558_update_packages_with_ppv_gpv_defaults.php

# Step 6: Ensure defaults for existing data
php artisan migrate --path=database/migrations/2026_01_19_205840_ensure_ppv_gpv_defaults_for_existing_data.php

# Step 7: Add performance indexes
php artisan migrate --path=database/migrations/2026_01_19_211559_optimize_ppv_gpv_performance.php

# Or run all at once (faster)
php artisan migrate --force
```

### Step 3.3: Verify Migration Success

```bash
# Check tables exist
mysql -u your_db_user -p -e "SHOW TABLES LIKE 'points_tracker'" your_db_name

# Check columns in packages table
mysql -u your_db_user -p -e "DESCRIBE packages" your_db_name | grep -E "ppv|gpv|rank_pv_enabled"

# Check columns in users table
mysql -u your_db_user -p -e "DESCRIBE users" your_db_name | grep -E "ppv|gpv"

# Check enum values
mysql -u your_db_user -p -e "SHOW COLUMNS FROM rank_advancements LIKE 'advancement_type'" your_db_name

# Verify data defaults
mysql -u your_db_user -p -e "
  SELECT rank_name, required_sponsors_ppv_gpv, ppv_required, gpv_required
  FROM packages
  WHERE is_rankable = 1
" your_db_name
```

### Step 3.4: Check Migration Rollback

```bash
# Test rollback capability (DO NOT RUN IN PRODUCTION)
php artisan migrate:rollback

# If successful, re-run migrations
php artisan migrate --force
```

---

## Phase 4: Data Migration

### Step 4.1: Initialize Existing Data

Existing users and orders need to be initialized with default values.

**Create data migration:**

```bash
php artisan make:migration initialize_existing_data_for_dual_path
```

**File:** `database/migrations/XXXX_XX_XX_XXXXXX_initialize_existing_data_for_dual_path.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Initialize PPV/GPV for existing users (if not set)
        DB::statement("
            UPDATE users
            SET current_ppv = COALESCE(current_ppv, 0),
                current_gpv = COALESCE(current_gpv, 0),
                ppv_gpv_updated_at = COALESCE(ppv_gpv_updated_at, NOW())
            WHERE current_ppv IS NULL
               OR current_gpv IS NULL
               OR ppv_gpv_updated_at IS NULL
        ");

        // Initialize points_credited for existing orders
        DB::statement("
            UPDATE orders
            SET points_credited = false
            WHERE points_credited IS NULL
        ");

        // Log migration summary
        $usersUpdated = DB::table('users')->count();
        $ordersUpdated = DB::table('orders')->count();

        DB::table('migration_logs')->insert([
            'migration_name' => 'initialize_existing_data_for_dual_path',
            'users_updated' => $usersUpdated,
            'orders_updated' => $ordersUpdated,
            'migrated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Rollback is not possible as we only initialized defaults
        // Data remains as-is
    }
};
```

**Run data migration:**

```bash
php artisan migrate --path=database/migrations/XXXX_XX_XX_XXXXXX_initialize_existing_data_for_dual_path.php
```

### Step 4.2: Backfill Historical Points (Optional)

**IMPORTANT:** This is OPTIONAL and resource-intensive. Only do this if you want historical point tracking.

**Option A: Do Not Backfill (Recommended)**
- Existing orders: No point tracking
- New orders: Full point tracking
- Pros: Fast, no data issues
- Cons: Historical data not reflected in PPV/GPV

**Option B: Backfill All Orders**
- All existing orders: Calculate and credit points
- Pros: Complete historical accuracy
- Cons: Time-consuming, may impact performance

**If choosing Option B, create a backfill job:**

```php
// app/Jobs/BackfillHistoricalPoints.php

namespace App\Jobs;

use App\Models\Order;
use App\Services\PointsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BackfillHistoricalPoints implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $batchSize = 100;

    public function handle(): void
    {
        $orders = Order::where('payment_status', 'paid')
            ->where('points_credited', false)
            ->where('status', '!=', 'cancelled')
            ->limit($this->batchSize)
            ->get();

        foreach ($orders as $order) {
            try {
                app(PointsService::class)->processOrderPoints($order);
                $order->update(['points_credited' => true]);
                Log::info("Backfilled points for order", ['order_id' => $order->id]);
            } catch (\Exception $e) {
                Log::error("Failed to backfill points", [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Dispatch next batch if more orders remain
        if ($orders->count() === $this->batchSize) {
            self::dispatch()->delay(now()->addMinutes(1));
        }
    }
}
```

**Trigger backfill (if chosen):**

```bash
php artisan tinker
```

```php
// In tinker
App\Jobs\BackfillHistoricalPoints::dispatch();
exit;
```

### Step 4.3: Verify Data Migration

```bash
# Check user initialization
mysql -u your_db_user -p -e "
  SELECT COUNT(*) as users_initialized
  FROM users
  WHERE current_ppv IS NOT NULL
    AND current_gpv IS NOT NULL
    AND ppv_gpv_updated_at IS NOT NULL
" your_db_name

# Check order initialization
mysql -u your_db_user -p -e "
  SELECT COUNT(*) as orders_initialized
  FROM orders
  WHERE points_credited IS NOT NULL
" your_db_name

# Check packages configuration
mysql -u your_db_user -p -e "
  SELECT rank_name, required_sponsors_ppv_gpv, ppv_required, gpv_required
  FROM packages
  WHERE is_rankable = 1
  ORDER BY rank_order
" your_db_name
```

---

## Phase 5: Post-Deployment Verification

### Step 5.1: Application Health Checks

```bash
# Check Laravel version
php artisan --version

# Check environment
php artisan env

# Check cache status
php artisan cache:status

# Check queue status
php artisan queue:status

# Check scheduled tasks
php artisan schedule:work --dry-run
```

### Step 5.2: Database Integrity Checks

```bash
# Check all new tables exist
mysql -u your_db_user -p -e "SHOW TABLES LIKE 'points_tracker'" your_db_name

# Check all new columns exist
mysql -u your_db_user -p -e "
  SELECT COUNT(*) as columns_check
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = 'your_db_name'
    AND TABLE_NAME IN ('users', 'packages')
    AND COLUMN_NAME IN ('current_ppv', 'current_gpv', 'ppv_gpv_updated_at',
                       'required_sponsors_ppv_gpv', 'ppv_required', 'gpv_required',
                       'rank_pv_enabled')
" your_db_name

# Verify enum updated
mysql -u your_db_user -p -e "
  SELECT COLUMN_TYPE
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = 'your_db_name'
    AND TABLE_NAME = 'rank_advancements'
    AND COLUMN_NAME = 'advancement_type'
" your_db_name
```

### Step 5.3: Functional Testing

**Test in production browser (or use Selenium/Puppeteer):**

1. **Dashboard Access**
   - Login as regular user
   - Navigate to `/dashboard`
   - Verify rank progress displays
   - Check both Path A and Path B progress bars

2. **Admin Configuration**
   - Login as admin
   - Navigate to `/admin/ranks/configure`
   - Verify PPV/GPV fields editable
   - Check default values display correctly

3. **Order Processing**
   - Create test order with products that have points
   - Verify points credited after payment
   - Check points_tracker table for entries

4. **Rank Advancement**
   - Test rank advancement via Path A
   - Test rank advancement via Path B
   - Verify PPV/GPV resets to 0

5. **Order Cancellation**
   - Cancel order with points
   - Verify points deducted
   - Check points_tracker for negative entries

### Step 5.4: Performance Checks

```bash
# Check database query performance
php artisan db:monitor --max=5

# Check slow queries
mysql -u your_db_user -p -e "SHOW PROCESSLIST" your_db_name

# Check server load
top

# Check disk space
df -h

# Check memory usage
free -m
```

### Step 5.5: Disable Maintenance Mode

```bash
# Disable maintenance mode
php artisan up

# Verify site is accessible
curl -I https://your-domain.com

# Check application logs
tail -f storage/logs/laravel.log
```

---

## Phase 6: User Communication

### Step 6.1: Pre-Deployment Announcement (24 hours before)

**Email Template:**

```
Subject: Scheduled System Maintenance - Dual-Path Rank Advancement

Dear [User Name],

We're excited to announce an upcoming system upgrade that will enhance your
rank advancement experience!

**What's New?**
- Two ways to advance your rank (Dual-Path System)
- Path A: Recruitment-based (existing system)
- Path B: Points-based (new! Earn points from purchases)
- Personal Points Volume (PPV) from your purchases
- Group Points Volume (GPV) from your entire team
- More flexibility in how you advance your rank

**Maintenance Schedule:**
- Date: [Date]
- Time: [Start Time] - [End Time] ([Timezone])
- Expected Downtime: 10-15 minutes

**What You Need to Do:**
- No action required
- Your existing rank and data are preserved
- After maintenance, log in to see your new progress dashboard

**Important Notes:**
- All existing data is safe
- Your current rank remains unchanged
- New features available after maintenance

Thank you for your patience as we improve our system!

Best regards,
[Your Company Name]
```

### Step 6.2: Post-Deployment Announcement (Immediately after)

**Email Template:**

```
Subject: System Upgrade Complete - New Dual-Path Rank Advancement!

Dear [User Name],

Great news! Our system upgrade is complete and the new Dual-Path Rank
Advancement system is now live!

**What's New:**
✅ Two advancement paths (Recruitment or Points)
✅ Track your Personal Points (PPV) from purchases
✅ Track your Group Points (GPV) from your team
✅ Visual progress dashboard for both paths
✅ More flexibility to advance your rank

**How to Use:**
1. Log in to your account
2. Visit the Dashboard
3. Check your rank progress under "Rank Advancement"
4. See both Path A (Recruitment) and Path B (Points) progress

**Key Changes:**
- Path A: Recruit [X] same-rank sponsors (existing method)
- Path B: Recruit [Y] sponsors + earn [Z] PPV + [W] GPV (new!)

**Learn More:**
- Visit our Knowledge Base: [Link]
- Watch our tutorial video: [Link]

**Need Help?**
- Contact Support: [Support Email]
- Live Chat: [Link]

Start earning points today and advance your rank the way that works best for you!

Best regards,
[Your Company Name]
```

### Step 6.3: Admin Communication

**Internal Memo:**

```
TO: All Staff
FROM: Development Team
DATE: [Date]
SUBJECT: Dual-Path Rank Advancement Deployment Complete

**Deployment Status:** SUCCESS ✅

**Changes Implemented:**
1. Dual-path rank advancement (Recruitment + PV-based)
2. PPV/GPV tracking system
3. Points audit trail
4. Admin configuration for PPV/GPV settings
5. Enhanced dashboard with progress tracking

**Admin Actions Required:**
- Review PPV/GPV defaults at /admin/ranks/configure
- Train support team on new features
- Monitor user feedback

**Support Team Knowledge:**
- Users now have 2 advancement paths
- Points earned from product purchases
- Points reset on rank advancement
- Orders cancelled deduct points automatically
- Admin can configure per-rank PPV/GPV requirements

**Common User Questions:**
Q: What happened to my existing rank?
A: Your rank is preserved. No changes to existing rank.

Q: What are PPV and GPV?
A: Personal Points (your purchases) and Group Points (team purchases).

Q: How do I earn points?
A: Purchase products with points value. Points awarded automatically.

Q: Which path is better?
A: Either path works! Choose based on your strengths (recruiting vs. team building).

**Monitoring:**
- Check logs: storage/logs/laravel.log
- Monitor errors: /horizon (if using)
- User feedback: Support tickets, email, live chat

**Rollback Information:**
- If issues arise, use pre-dual-path git tag
- Restore database from backup_before_deployment_*.sql
- Estimated rollback time: 10-20 minutes

**Questions?**
Contact Development Team: dev@your-company.com
```

---

## Rollback Procedure

### Step 1: Immediate Rollback (Critical Issues)

```bash
# 1. Enable maintenance mode immediately
php artisan down --message="System undergoing emergency maintenance"

# 2. Rollback code
cd /var/www/your-app
git checkout pre-dual-path-YYYYMMDD

# 3. Restore database
mysql -u your_db_user -p your_db_name < backup_before_deployment_YYYYMMDD_HHMMSS.sql

# 4. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 5. Restart queue workers
php artisan queue:restart

# 6. Disable maintenance mode
php artisan up

# 7. Verify application
curl -I https://your-domain.com
```

### Step 2: Selective Rollback (Specific Features)

**Option A: Rollback Only PPV/GPV Features**
- Keep code, disable features via configuration
- Set `rank_pv_enabled = false` for all packages
- Points system disabled but dual-path logic remains

**Option B: Rollback Only Database Changes**
- Keep code, restore database
- New code may throw errors without new schema
- Use only if schema changes are incompatible

### Step 3: Post-Rollback Verification

```bash
# Verify database schema
mysql -u your_db_user -p -e "DESCRIBE packages" your_db_name | grep -E "ppv|gpv"
# Should return nothing (columns removed)

# Verify application
php artisan tinker
```

```php
// In tinker
echo App\Models\User::count() . "\n";  // Should show user count
echo App\Models\Order::count() . "\n";  // Should show order count
exit;
```

### Step 4: Communicate Rollback

**Email Template:**

```
Subject: System Rollback - Service Restored

Dear [User Name],

We experienced technical difficulties with our recent system upgrade
and have temporarily rolled back to the previous version.

**What Happened:**
- The new Dual-Path Rank Advancement system encountered issues
- We've restored the previous recruitment-based system
- Your data is safe and unchanged

**Current Status:**
- Service restored
- Previous rank advancement system active
- All existing features working

**Next Steps:**
- We're investigating the issue
- Will reschedule deployment when resolved
- You'll receive another announcement

**What You Need to Do:**
- No action required
- Continue using the system as normal

We apologize for any inconvenience and appreciate your patience.

Best regards,
[Your Company Name]
```

---

## Monitoring & Troubleshooting

### Monitoring Checklist (First 24 Hours)

**Hour 1: Critical Monitoring**
- [ ] Check error logs every 10 minutes
- [ ] Monitor queue processing
- [ ] Verify order processing works
- [ ] Check database connection
- [ ] Test user login

**Hours 2-6: Active Monitoring**
- [ ] Review error logs hourly
- [ ] Monitor database performance
- [ ] Check queue backlog
- [ ] Verify point tracking
- [ ] Monitor rank advancement

**Hours 7-24: Periodic Monitoring**
- [ ] Review logs every 4 hours
- [ ] Check database growth
- [ ] Monitor point transactions
- [ ] Review user feedback

### Common Issues & Solutions

#### Issue 1: Migration Fails - Enum Error

**Symptom:**
```
SQLSTATE[HY000]: General error: 3780
Referencing column 'advancement_type' and referenced column 'advancement_type' in foreign key constraint are incompatible.
```

**Solution:**
```bash
# Run enum update migration separately first
php artisan migrate --path=database/migrations/XXXX_XX_XX_XXXXXX_update_rank_advancements_enum_for_dual_path.php
```

#### Issue 2: Points Not Crediting

**Symptom:** Orders processed but PPV/GPV not updating

**Diagnosis:**
```bash
# Check if ProcessMLMCommissions job is running
php artisan queue:work --stop-when-empty

# Check job logs
tail -f storage/logs/laravel.log | grep -i "process.*points"
```

**Solution:**
```bash
# Process order points manually
php artisan tinker
```

```php
$order = App\Models\Order::find(ORDER_ID);
app(App\Services\PointsService::class)->processOrderPoints($order);
exit;
```

#### Issue 3: GPV Not Updating for Uplines

**Symptom:** Buyer GPV updates but uplines not

**Diagnosis:**
```bash
# Check sponsor relationships
mysql -u your_db_user -p -e "
  SELECT u.id, u.email, u.sponsor_id, s.email as sponsor_email
  FROM users u
  LEFT JOIN users s ON u.sponsor_id = s.id
  WHERE u.id = [USER_ID]
" your_db_name
```

**Solution:**
- Verify sponsor relationships are correct
- Check for circular references
- Run: `php artisan ppv:recalculate-gpv [user_id]`

#### Issue 4: Dashboard Shows Wrong Progress

**Symptom:** Rank progress bars incorrect

**Diagnosis:**
```bash
# Check user's PPV/GPV
mysql -u your_db_user -p -e "
  SELECT current_rank, current_ppv, current_gpv, ppv_gpv_updated_at
  FROM users
  WHERE id = [USER_ID]
" your_db_name

# Check package requirements
mysql -u your_db_user -p -e "
  SELECT rank_name, required_sponsors_ppv_gpv, ppv_required, gpv_required
  FROM packages
  WHERE id = (SELECT rank_package_id FROM users WHERE id = [USER_ID])
" your_db_name
```

**Solution:**
```bash
# Recalculate GPV for user
php artisan ppv:recalculate-gpv [USER_ID]

# Clear dashboard cache
php artisan cache:forget "user_rank_progress_[USER_ID]"
```

#### Issue 5: High Database CPU After Deployment

**Symptom:** Database CPU spike, slow queries

**Diagnosis:**
```bash
# Check slow query log
tail -f /var/log/mysql/slow.log

# Check running queries
mysql -u your_db_user -p -e "SHOW FULL PROCESSLIST" your_db_name
```

**Solution:**
```bash
# Ensure indexes exist
mysql -u your_db_user -p -e "
  SHOW INDEX FROM points_tracker;
  SHOW INDEX FROM users;
" your_db_name

# If missing, run performance migration
php artisan migrate --path=database/migrations/2026_01_19_211559_optimize_ppv_gpv_performance.php
```

### Log Locations

```bash
# Laravel application logs
tail -f storage/logs/laravel.log

# Queue worker logs
tail -f storage/logs/queue.log

# Database logs (MySQL)
tail -f /var/log/mysql/error.log

# Web server logs (Nginx/Apache)
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

### Performance Monitoring Commands

```bash
# Check database connection count
mysql -u your_db_user -p -e "SHOW PROCESSLIST" your_db_name | wc -l

# Check database size
mysql -u your_db_user -p -e "
  SELECT
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
  FROM information_schema.tables
  WHERE table_schema = 'your_db_name'
  GROUP BY table_schema
" your_db_name

# Check points_tracker table growth
mysql -u your_db_user -p -e "
  SELECT
    COUNT(*) as total_records,
    MIN(earned_at) as first_record,
    MAX(earned_at) as last_record
  FROM points_tracker
" your_db_name

# Check average GPV per user
mysql -u your_db_user -p -e "
  SELECT
    AVG(current_gpv) as avg_gpv,
    MAX(current_gpv) as max_gpv,
    MIN(current_gpv) as min_gpv
  FROM users
  WHERE current_rank IS NOT NULL
" your_db_name
```

---

## Appendix

### A. Migration Order Summary

```bash
# CRITICAL: Run in this exact order
1. update_rank_advancements_enum_for_dual_path.php  # FIRST!
2. add_ppv_gpv_to_packages_table.php
3. add_ppv_gpv_to_users_table.php
4. create_points_tracker_table.php
5. update_packages_with_ppv_gpv_defaults.php
6. ensure_ppv_gpv_defaults_for_existing_data.php
7. optimize_ppv_gpv_performance.php
8. initialize_existing_data_for_dual_path.php  # Optional
```

### B. Environment Variables (No Changes Required)

No new `.env` variables needed. All configuration via admin panel.

### C. Queue Configuration

If using queue workers, ensure they're restarted:

```bash
php artisan queue:restart

# Or using systemd
sudo systemctl restart laravel-worker
```

### D. Scheduler Configuration

Ensure Laravel scheduler is running:

```bash
# Add to crontab
* * * * * cd /var/www/your-app && php artisan schedule:run >> /dev/null 2>&1

# Verify crontab
crontab -l
```

### E. Cache Configuration

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Recreate optimized caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### F. File Permissions

```bash
# Set correct permissions
cd /var/www/your-app

# Directories
find storage bootstrap/cache -type d -exec chmod 775 {} \;

# Files
find storage bootstrap/cache -type f -exec chmod 664 {} \;

# Set ownership
sudo chown -R www-data:www-data storage bootstrap/cache
```

---

## Deployment Checklist Summary

- [ ] Staging environment tested
- [ ] Production database backed up
- [ ] Code backup (git tag) created
- [ ] Team notified
- [ ] Maintenance window scheduled
- [ ] User announcement sent (24 hours before)
- [ ] Maintenance mode enabled
- [ ] Code deployed
- [ ] Migrations run successfully
- [ ] Data migration completed
- [ ] Caches cleared
- [ ] Queue workers restarted
- [ ] Functional tests passed
- [ ] Maintenance mode disabled
- [ ] Post-deployment announcement sent
- [ ] Monitoring active (first 24 hours)
- [ ] Documentation updated

---

## Success Criteria

Deployment is successful when:

✅ All migrations completed without errors
✅ Application loads without errors
✅ Dashboard displays rank progress correctly
✅ Admin configuration accessible
✅ New orders credit points correctly
✅ Order cancellations deduct points correctly
✅ Rank advancement works via both paths
✅ PPV/GPV reset on advancement works
✅ No database errors in logs
✅ No application errors in logs
✅ User feedback positive

---

## Contact Information

**Development Team:** dev@your-company.com
**Support Team:** support@your-company.com
**Emergency Contact:** emergency@your-company.com

**Documentation:**
- Implementation Guide: `rank_repeat_purchase.md`
- Phase 8 Summary: `rank_repeat_purchase_phase8.md`
- Phase 9 Summary: `rank_repeat_purchase_phase9.md`
- Complete Status: `rank_repeat_purchase_complete.md`

---

**Deployment Version:** 1.0.0
**Deployment Date:** [Date]
**Deployed By:** [Name]
**Deployment Status:** [Pending/In Progress/Complete]

---

*This deployment guide ensures a smooth transition to the Dual-Path Rank Advancement System with minimal disruption to users.*
