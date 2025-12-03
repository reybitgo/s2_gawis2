# Rank System Phase 5 - Hostinger Deployment Guide

**Date:** December 2, 2025  
**Phase:** Phase 5 - Admin Configuration Interface  
**Server:** Hostinger Live Server  
**Deployment Type:** Production Deployment

---

## Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Backup Procedures](#backup-procedures)
3. [File Deployment](#file-deployment)
4. [Database Verification](#database-verification)
5. [Configuration Updates](#configuration-updates)
6. [Cache Management](#cache-management)
7. [Testing Procedures](#testing-procedures)
8. [Rollback Plan](#rollback-plan)
9. [Post-Deployment Verification](#post-deployment-verification)
10. [Troubleshooting](#troubleshooting)

---

## Pre-Deployment Checklist

### ✅ Before You Begin

-   [ ] All Phase 1-4 deployments completed and verified
-   [ ] Database has all rank-related tables and columns
-   [ ] Admin access credentials available
-   [ ] Hostinger File Manager or FTP access confirmed
-   [ ] SSH access available (if needed)
-   [ ] Maintenance window scheduled (recommended: 30 minutes)
-   [ ] Backup tools ready
-   [ ] Team notified of deployment

### ✅ Required Information

```
Server Details:
- Domain: your-domain.com
- Hostinger Panel: https://hpanel.hostinger.com/
- Database Name: _______
- Database User: _______
- FTP Host: _______
- FTP User: _______
```

### ✅ Files to Deploy

**New Files:**

```
app/Http/Controllers/Admin/
└── AdminRankController.php

resources/views/admin/ranks/
├── index.blade.php
├── configure.blade.php
└── advancements.blade.php

resources/views/admin/packages/
├── create.blade.php (updated)
└── edit.blade.php (updated)

resources/views/partials/
└── sidebar.blade.php (updated)

resources/views/
└── dashboard.blade.php (updated)
```

**Updated Files:**

```
routes/web.php (updated)
app/Http/Controllers/Admin/AdminPackageController.php (updated)
```

---

## Backup Procedures

### Step 1: Database Backup

**Via Hostinger cPanel phpMyAdmin:**

1. **Login to Hostinger cPanel**

    - Go to: https://hpanel.hostinger.com/
    - Navigate to your website
    - Click "Database" → "phpMyAdmin"

2. **Export Database**
    ```
    1. Select your database from left sidebar
    2. Click "Export" tab
    3. Select "Custom" method
    4. Check all tables
    5. Format: SQL
    6. Compression: gzip
    7. Click "Go"
    8. Save file: database_backup_YYYYMMDD_HHMMSS.sql.gz
    ```

**Via SSH (if available):**

```bash
# Connect to server
ssh your_username@your-server.com

# Navigate to project
cd domains/your-domain.com/public_html

# Backup database
php artisan db:backup
# OR
mysqldump -u database_user -p database_name > backups/backup_phase5_ranking_$(date +%Y%m%d_%H%M%S).sql
```

### Step 2: Files Backup

**Via Hostinger File Manager:**

1. **Navigate to public_html**

    - Open File Manager in Hostinger
    - Go to `domains/your-domain.com/public_html`

2. **Create Backup Archive**
    ```
    Right-click on public_html folder
    → Compress
    → Format: .zip
    → Name: backup_YYYYMMDD_HHMMSS.zip
    → Download to local computer
    ```

**Via FTP (FileZilla):**

```
1. Connect to server via FTP
2. Download entire /public_html directory
3. Save to local: C:\Backups\site_backup_YYYYMMDD\
```

**Backup Specific Directories:**

```
/app/Http/Controllers/Admin/
/resources/views/admin/
/resources/views/partials/
/routes/
```

### Step 3: Verify Backups

-   [ ] Database backup file exists and has size > 0
-   [ ] Files backup archive created successfully
-   [ ] Downloaded backups to local computer
-   [ ] Verified archive can be extracted

---

## File Deployment

### Method 1: Via Hostinger File Manager (Recommended)

#### Step 1: Prepare Files Locally

1. **Zip Phase 5 Files**

    ```
    Create a zip file with this structure:

    phase5_deployment.zip
    ├── app/
    │   └── Http/
    │       └── Controllers/
    │           └── Admin/
    │               └── AdminRankController.php
    │               └── AdminPackageController.php (updated)
    ├── resources/
    │   └── views/
    │       ├── admin/
    │       │   ├── ranks/
    │       │   │   ├── index.blade.php
    │       │   │   ├── configure.blade.php
    │       │   │   └── advancements.blade.php
    │       │   └── packages/
    │       │       ├── create.blade.php
    │       │       └── edit.blade.php
    │       ├── partials/
    │       │   └── sidebar.blade.php
    │       └── dashboard.blade.php
    └── routes/
        └── web.php
    ```

2. **Create Deployment Package**
    - Navigate to: `C:\laragon\www\s2_gawis2\`
    - Select the folders/files listed above
    - Right-click → Send to → Compressed (zipped) folder
    - Name: `phase5_deployment.zip`

#### Step 2: Upload to Hostinger

1. **Access File Manager**

    - Login to Hostinger cPanel
    - Open "File Manager"
    - Navigate to: `domains/your-domain.com/public_html`

2. **Upload ZIP**

    ```
    1. Click "Upload" button (top right)
    2. Select phase5_deployment.zip
    3. Wait for upload to complete
    4. Close upload dialog
    ```

3. **Extract Files**

    ```
    1. Right-click on phase5_deployment.zip
    2. Click "Extract"
    3. Extract to: /domains/your-domain.com/public_html/
    4. Confirm extraction
    5. Files will be placed in correct directories
    ```

4. **Verify Extraction**

    ```
    Check these paths exist:
    ✓ app/Http/Controllers/Admin/AdminRankController.php
    ✓ resources/views/admin/ranks/ (3 files)
    ✓ resources/views/admin/packages/ (updated files)
    ✓ resources/views/partials/sidebar.blade.php
    ✓ resources/views/dashboard.blade.php
    ✓ routes/web.php
    ```

5. **Delete ZIP File**
    ```
    Right-click phase5_deployment.zip → Delete
    ```

#### Step 3: Set Permissions

**Via File Manager:**

```
Select these directories:
- app/Http/Controllers/Admin/
- resources/views/admin/ranks/
- resources/views/partials/

Right-click → Permissions → Set to 755
```

**Via SSH:**

```bash
cd /home/username/domains/your-domain.com/public_html

# Set directory permissions
find app/Http/Controllers/Admin/ -type d -exec chmod 755 {} \;
find resources/views/admin/ranks/ -type d -exec chmod 755 {} \;

# Set file permissions
find app/Http/Controllers/Admin/ -type f -exec chmod 644 {} \;
find resources/views/admin/ranks/ -type f -exec chmod 644 {} \;
find resources/views/partials/ -type f -exec chmod 644 {} \;
```

### Method 2: Via FTP (FileZilla)

#### Step 1: Connect to Server

```
FTP Settings:
Host: ftp.your-domain.com
Username: your_ftp_username
Password: your_ftp_password
Port: 21
Encryption: Use explicit FTP over TLS
```

#### Step 2: Upload Files

1. **Navigate to public_html**

    ```
    Remote path: /domains/your-domain.com/public_html/
    ```

2. **Upload New Controller**

    ```
    Local: C:\laragon\www\s2_gawis2\app\Http\Controllers\Admin\AdminRankController.php
    Remote: /app/Http/Controllers/Admin/AdminRankController.php
    ```

3. **Upload New Views**

    ```
    Local: C:\laragon\www\s2_gawis2\resources\views\admin\ranks\
    Remote: /resources/views/admin/ranks/

    Upload all 3 files:
    - index.blade.php
    - configure.blade.php
    - advancements.blade.php
    ```

4. **Upload Updated Files**
    ```
    Upload and OVERWRITE:
    - app/Http/Controllers/Admin/AdminPackageController.php
    - resources/views/admin/packages/create.blade.php
    - resources/views/admin/packages/edit.blade.php
    - resources/views/partials/sidebar.blade.php
    - resources/views/dashboard.blade.php
    - routes/web.php
    ```

#### Step 3: Verify Upload

-   [ ] All files uploaded successfully (green checkmarks)
-   [ ] File sizes match local files
-   [ ] No upload errors in FileZilla log

### Method 3: Via Git (If Using Version Control)

```bash
# SSH into server
ssh username@your-domain.com

# Navigate to project
cd /home/username/domains/your-domain.com/public_html

# Pull latest changes
git pull origin main

# OR if using specific branch
git fetch origin
git checkout rank-phase-5
git pull origin rank-phase-5
```

---

## Database Verification

### Step 1: Check Required Tables

**Via phpMyAdmin:**

```sql
-- Check if tables exist
SHOW TABLES LIKE '%rank%';

-- Expected tables:
-- ✓ rank_advancements
-- ✓ packages (with rank columns)
-- ✓ users (with rank columns)
```

### Step 2: Verify Columns

**Check packages table:**

```sql
DESCRIBE packages;

-- Required columns:
-- ✓ is_rankable (tinyint/boolean)
-- ✓ rank_name (varchar)
-- ✓ rank_order (int)
-- ✓ required_direct_sponsors (int)
-- ✓ next_rank_package_id (bigint)
```

**Check users table:**

```sql
DESCRIBE users;

-- Required columns:
-- ✓ current_rank (varchar)
-- ✓ rank_package_id (bigint)
-- ✓ rank_updated_at (timestamp)
-- ✓ network_status (varchar)
-- ✓ network_activated_at (timestamp)
```

**Check rank_advancements table:**

```sql
DESCRIBE rank_advancements;

-- Required columns:
-- ✓ id
-- ✓ user_id
-- ✓ from_rank, to_rank
-- ✓ from_package_id, to_package_id
-- ✓ advancement_type
-- ✓ sponsors_count
-- ✓ required_sponsors
-- ✓ system_paid_amount
-- ✓ order_id
-- ✓ notes
-- ✓ created_at, updated_at
```

### Step 3: Check Sample Data

```sql
-- Check if any packages are rankable
SELECT id, name, is_rankable, rank_name, rank_order
FROM packages
WHERE is_rankable = 1;

-- Check if any users have ranks
SELECT id, username, current_rank, rank_package_id, rank_updated_at
FROM users
WHERE current_rank IS NOT NULL
LIMIT 10;

-- Check if advancement records exist
SELECT COUNT(*) as total_advancements
FROM rank_advancements;
```

### Step 4: If Columns Missing

**If Phase 1-4 migrations not run:**

```bash
# Via SSH
cd /home/username/domains/your-domain.com/public_html

# Run migrations
php artisan migrate --force

# Check migration status
php artisan migrate:status
```

**Or manually via phpMyAdmin:**

Run the missing migration SQL files in order:

1. Phase 1 migrations
2. Phase 2 migrations
3. Phase 3 migrations
4. Phase 4 migrations

---

## Configuration Updates

### Step 1: Verify Routes

**Check routes are registered:**

```bash
# Via SSH
cd /home/username/domains/your-domain.com/public_html

# List rank routes
php artisan route:list | grep ranks

# Expected routes:
# GET    /admin/ranks
# GET    /admin/ranks/configure
# POST   /admin/ranks/configure
# GET    /admin/ranks/advancements
# POST   /admin/ranks/manual-advance/{user}
```

**If routes not showing:**

```bash
# Clear route cache
php artisan route:clear

# Re-cache routes (optional, for performance)
php artisan route:cache
```

### Step 2: Verify Middleware

**Check admin middleware working:**

Test URL: `https://your-domain.com/admin/ranks`

**Expected Behavior:**

-   Non-logged users: Redirect to login
-   Logged regular users: 403 Forbidden or redirect
-   Admin users: Dashboard loads successfully

### Step 3: Environment Configuration

**Check .env file:**

```bash
# Via File Manager, edit .env file
# Ensure these settings:

APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**Never set `APP_DEBUG=true` in production!**

---

## Cache Management

### Step 1: Clear All Caches

**Via SSH:**

```bash
cd /home/username/domains/your-domain.com/public_html

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Clear compiled files
php artisan clear-compiled
```

**Via Hostinger cPanel:**

```
1. Go to "Advanced" → "Optimize Website"
2. Click "Clear Cache"
3. Select "All"
4. Confirm
```

**Via Browser (Clear CDN/Cloudflare cache if applicable):**

```
If using Cloudflare:
1. Login to Cloudflare
2. Select your domain
3. Go to "Caching"
4. Click "Purge Everything"
5. Confirm
```

### Step 2: Rebuild Optimized Caches

**For better performance (optional):**

```bash
cd /home/username/domains/your-domain.com/public_html

# Optimize autoloader
composer dump-autoload --optimize

# Cache routes (only if no closures in routes)
php artisan route:cache

# Cache config
php artisan config:cache

# Cache views
php artisan view:cache
```

### Step 3: Test Cache Clearing

```bash
# Verify caches cleared
php artisan cache:forget 'test_key'
php artisan config:clear
php artisan route:list | head -5
```

---

## Testing Procedures

### Phase 1: Access Testing

**Test 1.1: Admin Login**

```
1. Navigate to: https://your-domain.com/admin/login
2. Login as admin
3. Expected: Successful login to admin dashboard
```

**Test 1.2: Rank Menu Visibility**

```
1. Check admin sidebar
2. Expected: "Rank System" menu visible with star icon
3. Hover/click to expand
4. Expected: 3 submenu items visible:
   - Dashboard
   - Configure Ranks
   - Advancement History
```

### Phase 2: Dashboard Testing

**Test 2.1: Rank Dashboard**

```
1. Navigate to: https://your-domain.com/admin/ranks
2. Expected: Dashboard loads with 4 statistics cards
3. Verify statistics show actual data:
   - Ranked Users count
   - Total Advancements count
   - System Rewards count
   - System Paid amount
4. Check rank packages table displays
5. Verify chart renders (if data exists)
```

**Test 2.2: Empty State**

```
If no data exists:
- Cards show "0"
- Chart shows "No data available"
- Table shows "No users" in user count column
- No errors displayed
```

### Phase 3: Configuration Testing

**Test 3.1: Configuration Page Load**

```
1. Navigate to: https://your-domain.com/admin/ranks/configure
2. Expected: Form loads with all rankable packages
3. Verify fields populated:
   - Rank Name
   - Rank Order
   - Required Sponsors
   - Next Rank Package dropdown
   - Price (read-only)
```

**Test 3.2: Save Configuration**

```
1. Modify a rank name (e.g., change "Starter" to "Beginner")
2. Change required sponsors number
3. Click "Save Configuration"
4. Expected: Success message appears
5. Refresh page
6. Verify changes saved
```

**Test 3.3: Validation**

```
1. Clear a required field (rank name)
2. Click "Save Configuration"
3. Expected: Error message displays
4. Verify no partial save occurred
```

### Phase 4: Advancement History Testing

**Test 4.1: List Page Load**

```
1. Navigate to: https://your-domain.com/admin/ranks/advancements
2. Expected: Page loads with table
3. Verify pagination appears (if >15 records)
4. Check per-page dropdown shows options (5,10,15,20,25,50,100)
```

**Test 4.2: Filters**

```
1. Select "Advancement Type" → "Sponsorship Reward"
2. Click "Filter"
3. Expected: Only reward type advancements shown
4. Clear filters
5. Expected: All advancements shown again
```

**Test 4.3: Search**

```
1. Enter username in search box
2. Press Enter
3. Expected: Only matching users' advancements shown
4. Test with email search
5. Clear search
```

**Test 4.4: Pagination**

```
1. Change "Show" dropdown to "5 per page"
2. Expected: Page reloads with 5 records
3. Click "Next" page
4. Expected: Next 5 records shown
5. Verify all filters/search persist
```

### Phase 5: Package Management Testing

**Test 5.1: Create Package with Rank**

```
1. Navigate to: https://your-domain.com/admin/packages/create
2. Fill in package details
3. Check "Rankable Package" checkbox
4. Save package
5. Expected: Success message
6. Navigate to rank configuration
7. Expected: New package appears in list
```

**Test 5.2: Edit Package - Enable Rank**

```
1. Edit an existing non-rankable package
2. Check "Rankable Package" checkbox
3. Save
4. Expected: Success, package now in rank configuration
```

**Test 5.3: Edit Package - Disable Protection**

```
1. Find package with users having that rank
2. Edit package
3. Expected: "Rankable Package" checkbox is DISABLED
4. Expected: Warning shows: "Cannot disable - X users have this rank"
5. Try to save anyway
6. Expected: Rank status remains enabled (protection works)
```

### Phase 6: Manual Advancement Testing

**Test 6.1: Open Manual Advance Modal**

```
1. Navigate to: https://your-domain.com/admin/users
2. Find a user and click "Edit"
3. Locate "Rank Management" card (left side)
4. Click "Manual Rank Advance" button
5. Expected: Modal opens
```

**Test 6.2: Perform Manual Advancement**

```
1. In modal, select a package from dropdown
2. Enter notes: "Testing manual advancement"
3. Click "Advance Rank"
4. Expected: Success message appears
5. Verify user rank updated in "Rank Management" card
6. Check advancement history page
7. Expected: New record with type "Admin Adjustment"
```

**Test 6.3: Dropdown Filtering**

```
1. Edit user with Bronze rank
2. Open manual advance modal
3. Expected: Dropdown only shows Silver, Gold, etc. (higher ranks)
4. Expected: Starter, Newbie, Bronze NOT in dropdown
```

### Phase 7: User Dashboard Testing

**Test 7.1: Rank Display - With Rank**

```
1. Logout and login as regular user with rank
2. Navigate to: https://your-domain.com/dashboard
3. Expected: Rank card visible below welcome header
4. Verify displays:
   - Current rank badge
   - Package name and price
   - Achievement date
   - Next rank (if applicable)
   - Progress bar for sponsors
```

**Test 7.2: Rank Display - No Rank**

```
1. Login as user without rank
2. Navigate to: https://your-domain.com/dashboard
3. Expected: Rank card shows "No Rank Yet" badge
4. Expected: Encouragement text to purchase package
```

**Test 7.3: Rank Display - Top Rank**

```
1. Login as user with highest rank
2. Navigate to: https://your-domain.com/dashboard
3. Expected: Trophy icon with "Top Rank!" message
4. Expected: No "Next Rank" section
```

### Phase 8: Integration Testing

**Test 8.1: Rank Advancement Workflow**

```
Full workflow test:
1. Admin enables ranking on a package
2. Admin configures rank settings
3. User purchases the package
4. Verify user rank updates
5. User sponsors required number of users
6. Verify automatic advancement (if Phase 3 active)
7. Check advancement history records
8. Verify user dashboard shows updated rank
```

**Test 8.2: Cross-Feature Integration**

```
1. Create rank package
2. Set MLM commissions (if Phase 2 active)
3. Set monthly quota (if Phase 4 active)
4. Purchase package as user
5. Verify all systems work together:
   - Rank assigned
   - Commissions calculate with rank
   - Quota tracked
   - Dashboard shows all info
```

---

## Rollback Plan

### If Critical Issues Occur

**Step 1: Immediate Actions**

1. **Enable Maintenance Mode**

    ```bash
    # Via SSH
    php artisan down --message="System maintenance in progress"
    ```

    **Via .htaccess (if no SSH):**

    ```apache
    # Add to top of .htaccess
    RewriteEngine On
    RewriteCond %{REMOTE_ADDR} !^YOUR_IP_ADDRESS$
    RewriteRule ^(.*)$ - [R=503,L]
    ```

2. **Document Issues**
    ```
    - What is broken?
    - Error messages?
    - Which pages affected?
    - Steps to reproduce
    ```

**Step 2: Quick Fixes (Attempt First)**

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Check logs
tail -50 storage/logs/laravel.log

# Fix specific issue if identified
```

**Step 3: Rollback Files**

**Via File Manager:**

```
1. Delete Phase 5 files:
   - app/Http/Controllers/Admin/AdminRankController.php
   - resources/views/admin/ranks/ (entire folder)

2. Restore backup files:
   - Upload backup_YYYYMMDD.zip
   - Extract to public_html
   - Overwrite all files
```

**Via FTP:**

```
1. Connect to server
2. Delete new Phase 5 files
3. Upload backup files from local
4. Overwrite updated files with backup versions
```

**Step 4: Rollback Database (If Needed)**

⚠️ **WARNING: This will lose data created after backup!**

**Via phpMyAdmin:**

```
1. Login to phpMyAdmin
2. Select database
3. Click "Import"
4. Choose backup file: database_backup_YYYYMMDD.sql.gz
5. Format: SQL
6. Click "Go"
7. Wait for import to complete
```

**Via SSH:**

```bash
# Restore database backup
mysql -u username -p database_name < backup_YYYYMMDD.sql
```

**Step 5: Verify Rollback**

```
1. Clear all caches again
2. Test main pages load:
   - Homepage
   - Dashboard
   - Admin dashboard
   - Package pages
3. Verify no Phase 5 routes accessible
4. Check error logs cleared
```

**Step 6: Disable Maintenance Mode**

```bash
# Via SSH
php artisan up

# Via .htaccess
# Remove maintenance rewrite rules added earlier
```

**Step 7: Post-Rollback Communication**

```
1. Notify team of rollback
2. Document what went wrong
3. Plan fix for issues
4. Schedule re-deployment
```

---

## Post-Deployment Verification

### Verification Checklist

**Immediately After Deployment:**

-   [ ] Website homepage loads
-   [ ] Admin login works
-   [ ] User login works
-   [ ] No PHP errors in browser
-   [ ] No console errors in browser
-   [ ] All caches cleared successfully

**Rank System Verification:**

-   [ ] `/admin/ranks` dashboard loads
-   [ ] `/admin/ranks/configure` form loads
-   [ ] `/admin/ranks/advancements` list loads
-   [ ] Sidebar shows Rank System menu
-   [ ] User dashboard shows rank card
-   [ ] Package create/edit shows rankable toggle

**Database Verification:**

```sql
-- Check no errors in recent logs
SELECT * FROM activity_logs
WHERE created_at > NOW() - INTERVAL 1 HOUR
ORDER BY created_at DESC
LIMIT 20;

-- Verify rank data intact
SELECT COUNT(*) FROM rank_advancements;
SELECT COUNT(*) FROM packages WHERE is_rankable = 1;
SELECT COUNT(*) FROM users WHERE current_rank IS NOT NULL;
```

**Performance Check:**

-   [ ] Dashboard loads in < 3 seconds
-   [ ] Configuration page loads in < 2 seconds
-   [ ] Advancements page loads in < 3 seconds
-   [ ] No slow query warnings

**Security Check:**

-   [ ] Non-admin cannot access `/admin/ranks`
-   [ ] CSRF tokens present on forms
-   [ ] No sensitive data exposed in HTML
-   [ ] SSL certificate active (https://)

---

## Monitoring

### First 24 Hours After Deployment

**Check Every Hour:**

1. **Error Logs**

    ```bash
    # Via SSH
    tail -50 storage/logs/laravel.log

    # Look for:
    - PHP errors
    - Database errors
    - Permission errors
    - Rank system errors
    ```

2. **Database Performance**

    ```sql
    -- Check slow queries
    SHOW PROCESSLIST;

    -- Check table sizes
    SELECT table_name,
           ROUND((data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
    FROM information_schema.tables
    WHERE table_schema = 'your_database'
    ORDER BY (data_length + index_length) DESC;
    ```

3. **User Feedback**
    - Monitor support tickets
    - Check user reports
    - Watch for repeated issues

**Monitor These Metrics:**

```sql
-- Rank advancement activity
SELECT DATE(created_at) as date,
       advancement_type,
       COUNT(*) as count
FROM rank_advancements
WHERE created_at > NOW() - INTERVAL 7 DAY
GROUP BY DATE(created_at), advancement_type
ORDER BY date DESC;

-- Package enabling activity
SELECT DATE(updated_at) as date,
       COUNT(*) as packages_made_rankable
FROM packages
WHERE is_rankable = 1
  AND updated_at > NOW() - INTERVAL 7 DAY
GROUP BY DATE(updated_at)
ORDER BY date DESC;

-- Admin activity
SELECT DATE(created_at) as date,
       COUNT(*) as admin_actions
FROM activity_logs
WHERE subject_type LIKE '%Rank%'
  AND created_at > NOW() - INTERVAL 7 DAY
GROUP BY DATE(created_at)
ORDER BY date DESC;
```

### Set Up Alerts

**Error Monitoring:**

1. **Install Error Tracking (Optional)**

    - Sentry: https://sentry.io
    - Bugsnag: https://www.bugsnag.com
    - Or use Laravel Telescope for local monitoring

2. **Email Alerts for Critical Errors**
    ```php
    // In app/Exceptions/Handler.php
    // Configure email alerts for critical errors
    ```

**Performance Monitoring:**

-   Hostinger performance graphs (CPU, Memory, I/O)
-   Database query times
-   Page load times

---

## Troubleshooting

### Common Issues & Solutions

#### Issue 1: Routes Not Found (404 Error)

**Symptoms:**

-   `/admin/ranks` returns 404
-   Rank menu links don't work

**Solutions:**

```bash
# Clear route cache
php artisan route:clear

# Re-cache routes
php artisan route:cache

# Check routes registered
php artisan route:list | grep ranks

# If still not working, check web.php uploaded correctly
```

#### Issue 2: Class Not Found Error

**Symptoms:**

```
Class 'App\Http\Controllers\Admin\AdminRankController' not found
```

**Solutions:**

```bash
# Clear autoload cache
composer dump-autoload

# Check file exists
ls -la app/Http/Controllers/Admin/AdminRankController.php

# Check file permissions
chmod 644 app/Http/Controllers/Admin/AdminRankController.php

# If composer not available on Hostinger, re-upload the file
```

#### Issue 3: View Not Found Error

**Symptoms:**

```
View [admin.ranks.index] not found
```

**Solutions:**

```bash
# Clear view cache
php artisan view:clear

# Check views exist
ls -la resources/views/admin/ranks/

# Verify file permissions
chmod 644 resources/views/admin/ranks/*.blade.php

# Re-upload view files if missing
```

#### Issue 4: Permission Denied Errors

**Symptoms:**

```
Permission denied: /storage/logs/laravel.log
```

**Solutions:**

```bash
# Fix storage permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Fix ownership (if SSH available)
chown -R username:username storage
chown -R username:username bootstrap/cache
```

#### Issue 5: Database Connection Error

**Symptoms:**

```
SQLSTATE[HY000] [2002] Connection refused
```

**Solutions:**

```bash
# Check .env file
cat .env | grep DB_

# Verify database credentials
# Test connection in phpMyAdmin

# Clear config cache
php artisan config:clear

# Restart MySQL (if possible)
```

#### Issue 6: CSRF Token Mismatch

**Symptoms:**

```
TokenMismatchException: CSRF token mismatch
```

**Solutions:**

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Check session configuration in .env
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Clear browser cookies and try again
```

#### Issue 7: Rank Display Not Showing on Dashboard

**Symptoms:**

-   Dashboard loads but no rank card
-   User has rank but not displayed

**Solutions:**

```bash
# Clear view cache
php artisan view:clear

# Check user has rank data
mysql> SELECT id, username, current_rank, rank_package_id FROM users WHERE id = X;

# Verify dashboard.blade.php uploaded correctly
# Check browser console for JavaScript errors
```

#### Issue 8: Sidebar Rank Menu Not Showing

**Symptoms:**

-   Sidebar loads but no Rank System menu
-   Admin logged in but menu missing

**Solutions:**

```bash
# Clear view cache
php artisan view:clear

# Verify sidebar.blade.php updated
cat resources/views/partials/sidebar.blade.php | grep "Rank System"

# Check user role is 'admin'
mysql> SELECT id, username, role FROM users WHERE id = X;

# Re-upload sidebar.blade.php if needed
```

#### Issue 9: Package Edit - Rankable Toggle Not Saving

**Symptoms:**

-   Check/uncheck rankable toggle
-   After save, status doesn't change

**Solutions:**

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear

# Check AdminPackageController.php updated correctly
# Verify is_rankable column exists in packages table
mysql> DESCRIBE packages;

# Test with database direct update
mysql> UPDATE packages SET is_rankable = 1 WHERE id = X;
```

#### Issue 10: Slow Page Load Times

**Symptoms:**

-   Pages take >5 seconds to load
-   Timeout errors

**Solutions:**

```bash
# Enable query caching
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize

# Check database indexes
mysql> SHOW INDEX FROM rank_advancements;

# Consider adding indexes if missing
```

---

## Success Criteria

### Deployment Considered Successful When:

✅ **All Pages Load Without Errors**

-   Admin dashboard
-   Rank dashboard
-   Configure ranks
-   Advancement history
-   User dashboard
-   Package management

✅ **All Features Work**

-   Rank configuration saves
-   Manual advancement works
-   Package rankable toggle functions
-   Filters and pagination work
-   Sidebar menu displays
-   User dashboard shows rank

✅ **No Critical Errors**

-   No PHP errors in logs
-   No database errors
-   No permission errors
-   No CSRF errors

✅ **Performance Acceptable**

-   Pages load in < 5 seconds
-   Database queries efficient
-   No timeout errors

✅ **Data Integrity Maintained**

-   Existing data unchanged
-   No data loss occurred
-   Relationships intact
-   Counts accurate

---

## Quick Reference Commands

### Hostinger SSH Commands

```bash
# Connect to server
ssh username@your-domain.com

# Navigate to project
cd /home/username/domains/your-domain.com/public_html

# Clear all caches
php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear

# Check routes
php artisan route:list | grep ranks

# View logs
tail -50 storage/logs/laravel.log

# Check permissions
ls -la app/Http/Controllers/Admin/
ls -la resources/views/admin/ranks/

# Fix permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache

# Database backup
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Database check
mysql -u username -p database_name -e "SHOW TABLES;"
```

### Emergency Rollback Commands

```bash
# Enable maintenance mode
php artisan down

# Restore files
cp -r /backup/app/Http/Controllers/Admin/* app/Http/Controllers/Admin/
cp -r /backup/resources/views/admin/* resources/views/admin/
cp /backup/routes/web.php routes/web.php

# Restore database
mysql -u username -p database_name < backup_YYYYMMDD.sql

# Clear all caches
php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear

# Disable maintenance mode
php artisan up
```

---

## Support Contacts

### During Deployment

**Technical Issues:**

-   Developer: [Your Name/Email]
-   Server Support: Hostinger Live Chat (24/7)
-   Database: [DBA Contact if applicable]

**Hostinger Support:**

-   Live Chat: https://hpanel.hostinger.com/ (bottom right corner)
-   Email: support@hostinger.com
-   Phone: Check your regional number
-   Knowledge Base: https://support.hostinger.com/

**Emergency Escalation:**

-   [Your Manager/Lead Developer]
-   [Project Owner]

---

## Post-Deployment Tasks

### After Successful Deployment

1. **Update Documentation**

    - [ ] Mark Phase 5 as deployed in project tracker
    - [ ] Update deployment log with date/time
    - [ ] Document any issues encountered and solutions
    - [ ] Update system architecture diagrams

2. **Team Communication**

    - [ ] Notify team deployment complete
    - [ ] Share any known issues or workarounds
    - [ ] Schedule demo of new features (if needed)
    - [ ] Update training materials

3. **User Communication**

    - [ ] Announce new Rank System features (if applicable)
    - [ ] Update user guide/FAQ
    - [ ] Notify admins of new admin features
    - [ ] Provide training on manual advancement

4. **Monitoring Setup**

    - [ ] Configure error monitoring
    - [ ] Set up performance alerts
    - [ ] Schedule weekly review of rank metrics
    - [ ] Monitor user feedback channels

5. **Plan Next Phase**
    - [ ] Review Phase 5 success
    - [ ] Plan Phase 6 (if applicable)
    - [ ] Document lessons learned
    - [ ] Update deployment procedures based on experience

---

## Conclusion

This deployment guide provides comprehensive steps for safely deploying Rank System Phase 5 to your Hostinger production server.

**Key Reminders:**

✅ **Always backup before deployment**  
✅ **Test in staging first** (if available)  
✅ **Clear all caches after deployment**  
✅ **Monitor for 24 hours post-deployment**  
✅ **Have rollback plan ready**

**Phase 5 Features Deployed:**

-   ✅ Admin Rank Dashboard
-   ✅ Rank Configuration Interface
-   ✅ Advancement History Tracking
-   ✅ Manual Rank Advancement
-   ✅ Package Rankable Toggle
-   ✅ Sidebar Rank Menu
-   ✅ User Dashboard Rank Display

**Estimated Deployment Time:** 30-60 minutes

**Estimated Downtime:** 0-5 minutes (if maintenance mode used)

---

**Deployment Guide Version:** 1.0  
**Last Updated:** December 2, 2025  
**Phase:** 5 of 6  
**Status:** Ready for Production Deployment

---

_Good luck with your deployment! 🚀_

_For questions or issues, refer to the troubleshooting section or contact support._
