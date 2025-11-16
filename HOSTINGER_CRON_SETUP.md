# Hostinger CRON Job Setup Guide

## Monthly Quota System - Choose Your Approach

This guide shows you how to set up the monthly quota CRON jobs on Hostinger shared hosting. You have **two options**:

- **Option A: Direct PHP Script Execution** (Traditional, easier for beginners)
- **Option B: Laravel Scheduler** (Modern Laravel way, better long-term)

---

## Which Option Should You Choose?

### Option A: Direct PHP Script Execution
**Best for**: Those familiar with traditional cron jobs; quick setup  
**Pros**: Simple, transparent, no Laravel knowledge needed  
**Cons**: Multiple cron entries; harder to manage many tasks  

### Option B: Laravel Scheduler  
**Best for**: Learning Laravel best practices; long-term projects  
**Pros**: Single cron entry; easier to add more tasks; industry standard  
**Cons**: Need to understand Laravel Scheduler basics  

**Our Recommendation**: Start with **Option A** if you're new to Laravel Scheduler. You can migrate to Option B later once comfortable.

---

## Option A: Direct PHP Script Execution (Traditional Approach)

This approach uses standalone PHP scripts executed directly by cron.

### Prerequisites

1. ✅ CRON scripts created in `crons/` folder (Phase 6)
   - `crons/reset_monthly_quota.php`
   - `crons/send_quota_reminders.php`
2. ✅ Scripts uploaded to Hostinger
3. ✅ Application deployed to Hostinger

---

## Step 1: Upload CRON Scripts

Make sure these files exist on your Hostinger server:

```
/home/u938213108/public_html/s2/
  └── crons/
      ├── reset_monthly_quota.php
      └── send_quota_reminders.php
```

**Check via SSH (if available):**
```bash
cd /home/u938213108/public_html/s2
ls -la crons/
```

---

## Step 2: Test Scripts via SSH (Optional but Recommended)

If you have SSH access, test the scripts manually first:

```bash
# Navigate to project
cd /home/u938213108/public_html/s2

# Test reset script
/usr/bin/php crons/reset_monthly_quota.php

# Test reminders script
/usr/bin/php crons/send_quota_reminders.php
```

**Expected Output:**
```
=== Monthly Quota Reset CRON Job ===
Started at: 2025-11-15 12:01:00

Creating trackers for active users...
Active users: 25
New trackers created: 25
Completed at: 2025-11-15 12:01:05
```

**Check Laravel logs:**
```bash
tail -50 storage/logs/laravel.log
```

---

## Step 4: Access Hostinger hPanel

1. Log in to Hostinger account
2. Select your hosting plan
3. Click "Manage"
4. Go to **Advanced** section → **Cron Jobs**

---

## Step 5: Create CRON Job #1 - Monthly Reset

Click **"Create Cron Job"** button

### Configuration:

**Type:** Common Settings

**Command:**
```
/usr/bin/php /home/u938213108/public_html/s2/crons/reset_monthly_quota.php
```

**IMPORTANT**: Replace `/home/u938213108/public_html/s2/` with your actual path:
- Find your path in hPanel → Files → File Manager
- Common format: `/home/YOUR_USERNAME/public_html/YOUR_PROJECT/`

**Schedule:**
- **Minute:** `1`
- **Hour:** `0` (midnight)
- **Day:** `1` (1st of month)
- **Month:** `*` (every month)
- **Weekday:** `*` (any day)

**Email Notification:** (Optional)
- Enter your email if you want to receive execution reports

**Click "Create"**

---

## Step 6: Create CRON Job #2 - Send Reminders

Click **"Create Cron Job"** button again

### Configuration:

**Type:** Common Settings

**Command:**
```
/usr/bin/php /home/u938213108/public_html/s2/crons/send_quota_reminders.php
```

**IMPORTANT**: Replace `/home/u938213108/public_html/s2/` with your actual path.

**Schedule:**
- **Minute:** `0`
- **Hour:** `9` (9:00 AM)
- **Day:** `25` (25th of month)
- **Month:** `*` (every month)
- **Weekday:** `*` (any day)

**Email Notification:** (Optional)
- Enter your email if you want to receive execution reports

**Click "Create"**

---

## Step 7: Verify CRON Jobs

After creating both jobs, you should see them listed in the Cron Jobs section:

| Command | Schedule | Next Run |
|---------|----------|----------|
| /usr/bin/php ...reset_monthly_quota.php | 1 0 1 * * | Dec 1, 2025 12:01 AM |
| /usr/bin/php ...send_quota_reminders.php | 0 9 25 * * | Nov 25, 2025 9:00 AM |

---

## Understanding Hostinger CRON Schedule Format

| Field | Values | Description |
|-------|--------|-------------|
| Minute | 0-59 | Which minute of the hour |
| Hour | 0-23 | Which hour (24-hour format, 0 = midnight) |
| Day | 1-31 | Which day of the month |
| Month | 1-12 or * | Which month (* = every month) |
| Weekday | 0-6 or * | Which day of week (0=Sunday, * = every day) |

**Examples:**

- `0 0 1 * *` = 1st of every month at 12:00 AM
- `1 0 1 * *` = 1st of every month at 12:01 AM
- `0 9 25 * *` = 25th of every month at 9:00 AM
- `30 14 * * 1` = Every Monday at 2:30 PM
- `0 */6 * * *` = Every 6 hours

---

## Monitoring CRON Execution

### Check Laravel Logs

After CRON runs, check:
```
storage/logs/laravel.log
```

Look for:
```
[2025-11-01 00:01:05] local.INFO: Monthly quota reset completed via CRON {"active_users":150,"new_trackers":150}
```

### Enable Email Notifications

In Hostinger hPanel, enable email notifications for each CRON job to receive:
- Execution confirmation
- Any error output
- Execution time

---

## Testing Before Month End

**Don't wait until month end to test!** You can trigger scripts manually:

### Option 1: SSH (Recommended)
```bash
# Navigate to project
cd /home/u938213108/public_html/s2

# Test reset script
/usr/bin/php crons/reset_monthly_quota.php

# Test reminders script
/usr/bin/php crons/send_quota_reminders.php

# Check logs
tail -50 storage/logs/laravel.log
```

### Option 2: Hostinger "Run Now" Button
Some Hostinger plans allow you to click **"Run Now"** button next to each CRON job in hPanel.

### Option 3: Temporary Test CRON
Create a temporary CRON job that runs every minute:
```
Command: /usr/bin/php /home/u938213108/public_html/s2/crons/reset_monthly_quota.php
Schedule: Minute=*, Hour=*, Day=*, Month=*, Weekday=*
```

Wait 1 minute, check logs, then **delete this test CRON**.

---

## Finding Your PHP Path (If Needed)

If `/usr/bin/php` doesn't work, find the correct PHP path:

### Via SSH:
```bash
which php
# Usually outputs: /usr/bin/php
```

### Via Hostinger hPanel:
1. Go to Advanced → PHP Configuration
2. Check PHP version being used
3. Common paths:
   - `/usr/bin/php` (most common)
   - `/usr/local/bin/php`
   - `/opt/alt/php81/usr/bin/php` (if using specific PHP version like 8.1)

### Test Different Paths:
```bash
/usr/bin/php --version
/usr/local/bin/php --version
/opt/alt/php81/usr/bin/php --version
```

Use the path that returns a version number.

---

## Troubleshooting

### CRON Job Not Running

1. **Check CRON is active:**
   - Go to hPanel → Cron Jobs
   - Verify status is "Active"

2. **Check Next Run Time:**
   - Make sure schedule is set correctly
   - Remember: Hour=0 is midnight

3. **Test URL manually:**
   - Visit the URL in browser
   - Check for errors

### Script Not Executing

**Check script exists:**
```bash
ls -la /home/u938213108/public_html/s2/crons/
```

**Check PHP path:**
```bash
which php
/usr/bin/php --version
```

**Check script permissions:**
```bash
chmod 644 crons/reset_monthly_quota.php
chmod 644 crons/send_quota_reminders.php
```

### Getting Errors in Logs

- Check `storage/logs/laravel.log` for errors
- Verify database connection in `.env`
- Check file/folder permissions (755 for folders, 644 for files)

### Email Notifications Not Received

- Check spam folder
- Verify email address in CRON job settings
- Some Hostinger plans may not support CRON emails

---

## Security Best Practices

1. ✅ **Set proper file permissions** (644 for PHP files, 755 for folders)
2. ✅ **Keep .env secure** with database credentials
3. ✅ **Monitor logs** regularly for errors
4. ✅ **Test scripts** before adding to CRON
5. ✅ **Backup database** before major CRON changes

---

## Option B: Laravel Scheduler (Modern Laravel Approach)

This approach uses Laravel's built-in task scheduler - all tasks managed in code, only one cron entry needed.

### Prerequisites

1. ✅ Laravel Console Commands created (Phase 6):
   - `app/Console/Commands/ResetMonthlyQuotas.php`
   - `app/Console/Commands/SendQuotaReminders.php`
2. ✅ Commands registered in `app/Console/Kernel.php`
3. ✅ Application deployed to Hostinger

---

### Step 1: Register Commands in Kernel

Make sure your `app/Console/Kernel.php` has the scheduled tasks:

```php
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Reset monthly quotas on 1st of each month at 12:01 AM
        $schedule->command('quota:reset-monthly')
            ->monthlyOn(1, '00:01')
            ->timezone('Asia/Manila')
            ->appendOutputTo(storage_path('logs/cron-quota-reset.log'));

        // Send quota reminders on 25th of each month at 9:00 AM
        $schedule->command('quota:send-reminders')
            ->monthlyOn(25, '09:00')
            ->timezone('Asia/Manila')
            ->appendOutputTo(storage_path('logs/cron-quota-reminders.log'));
    }
}
```

---

### Step 2: Test Scheduler Locally

Before deploying, test on your local machine:

```bash
# Navigate to project
cd C:\laragon\www\s2_gawis2

# Test individual commands
php artisan quota:reset-monthly
php artisan quota:send-reminders --force

# View all scheduled tasks
php artisan schedule:list

# Example output:
# 0 1 1 * * ................ quota:reset-monthly ........... Next Due: 1 month from now
# 0 9 25 * * ............... quota:send-reminders .......... Next Due: 10 days from now

# Run scheduler manually (simulates what cron will do)
php artisan schedule:run

# Watch scheduler in real-time (keeps running)
php artisan schedule:work
```

---

### Step 3: Upload to Hostinger

Make sure these files are uploaded:
- `app/Console/Commands/ResetMonthlyQuotas.php`
- `app/Console/Commands/SendQuotaReminders.php`
- `app/Console/Kernel.php` (updated with schedule)

---

### Step 4: Test on Hostinger via SSH

```bash
# Navigate to project
cd /home/u938213108/public_html/s2

# Test individual commands
php artisan quota:reset-monthly
php artisan quota:send-reminders --force

# View scheduled tasks
php artisan schedule:list

# Run scheduler manually
php artisan schedule:run
```

---

### Step 5: Create Single CRON Job in Hostinger hPanel

1. Log in to Hostinger hPanel
2. Go to **Advanced** → **Cron Jobs**
3. Click **"Create Cron Job"**

**Configuration:**

**Type:** Common Settings

**Command:**
```
cd /home/u938213108/public_html/s2 && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

**IMPORTANT**: 
- Replace `/home/u938213108/public_html/s2` with your actual path
- The `cd` command is required to set the working directory
- `>> /dev/null 2>&1` suppresses output (Laravel logs to files instead)

**Schedule:**
- **Minute:** `*` (every minute)
- **Hour:** `*` (every hour)
- **Day:** `*` (every day)
- **Month:** `*` (every month)
- **Weekday:** `*` (every weekday)

**Email Notification:** Leave blank (Laravel handles logging)

**Click "Create"**

**That's it!** Only **ONE** cron job needed. Laravel will check every minute which tasks should run.

---

### Step 6: Verify Laravel Scheduler is Working

After setting up the cron job, verify it's working:

**Check Laravel logs:**
```bash
# SSH into server
ssh u938213108@your-domain.com
cd /home/u938213108/public_html/s2

# Check if scheduler is being called
tail -f storage/logs/laravel.log

# Check specific task logs
tail -f storage/logs/cron-quota-reset.log
tail -f storage/logs/cron-quota-reminders.log
```

**Manual verification:**
```bash
# Run scheduler manually to test
php artisan schedule:run

# View what's scheduled
php artisan schedule:list
```

---

### Understanding Laravel Scheduler

**How it works:**
1. Cron runs `php artisan schedule:run` every minute
2. Laravel checks which tasks are due based on your `schedule()` configuration
3. If a task is scheduled for that time, Laravel executes it
4. If no tasks are due, nothing happens (very efficient)
5. All scheduling logic is in your code, not in crontab

**Benefits:**
- ✅ **Single cron entry** - Add unlimited tasks without touching cron
- ✅ **Easy testing** - `schedule:list` shows all tasks and next run times
- ✅ **Built-in logging** - Each task logs to its own file
- ✅ **Timezone support** - Set timezone per task
- ✅ **Code-based** - All configuration in version control
- ✅ **Laravel best practice** - Industry standard approach

**Common schedule methods:**
```php
->everyMinute()              // Every minute
->hourly()                   // Every hour at :00
->daily()                    // Every day at midnight
->dailyAt('13:00')          // Every day at 1:00 PM
->weekly()                   // Every week on Sunday at midnight
->weeklyOn(1, '8:00')       // Every Monday at 8:00 AM
->monthly()                  // First day of month at midnight
->monthlyOn(4, '15:30')     // 4th day of month at 3:30 PM
->quarterly()                // First day of quarter
->yearly()                   // First day of year
```

---

### Monitoring Laravel Scheduler

**Check if scheduler is running:**
```bash
# View Laravel logs
tail -100 storage/logs/laravel.log

# Check for scheduler execution
grep "schedule:run" storage/logs/laravel.log

# View task-specific logs
cat storage/logs/cron-quota-reset.log
cat storage/logs/cron-quota-reminders.log
```

**Verify cron is calling scheduler:**
```bash
# Check system cron logs (if available)
grep CRON /var/log/syslog
```

---

### Troubleshooting Laravel Scheduler

**Scheduler not running:**
1. Check cron job is active in hPanel
2. Verify command path is correct
3. Test manually: `php artisan schedule:run`

**Tasks not executing:**
1. Check schedule configuration in `Kernel.php`
2. Verify timezone setting
3. Run `php artisan schedule:list` to see next run times

**Getting errors:**
1. Check `storage/logs/laravel.log`
2. Check task-specific log files
3. Run commands manually to see errors:
   ```bash
   php artisan quota:reset-monthly
   php artisan quota:send-reminders --force
   ```

---

### Migrating from Option A to Option B

If you started with direct PHP scripts (Option A) and want to switch:

1. ✅ Ensure `app/Console/Kernel.php` is configured
2. ✅ Test scheduler locally: `php artisan schedule:list`
3. ✅ Delete the 2 existing cron jobs in hPanel (Option A entries)
4. ✅ Create single new cron job (Option B entry)
5. ✅ Wait 1 minute and check logs to verify
6. ✅ Keep the `crons/*.php` scripts as backup (optional)

---

## Summary

### Option A: Direct PHP Script Execution
**Best for**: Quick setup, traditional approach  

✅ Easy to set up in hPanel  
✅ Works on all Hostinger plans  
✅ No web server overhead  
✅ No URL routes or security tokens needed  
✅ Easy to test via SSH  

**Two CRON jobs needed:**
1. Reset Monthly Quotas: 1st of month, 12:01 AM
   - `/usr/bin/php .../crons/reset_monthly_quota.php`
2. Send Reminders: 25th of month, 9:00 AM
   - `/usr/bin/php .../crons/send_quota_reminders.php`

**Setup time:** ~5-10 minutes

### Option B: Laravel Scheduler
**Best for**: Laravel best practices, long-term maintainability  

✅ **Only ONE cron job needed**  
✅ Add unlimited tasks without touching cron  
✅ Easy testing with `schedule:list` and `schedule:run`  
✅ Built-in logging per task  
✅ Timezone support  
✅ Industry standard for Laravel  

**One CRON job needed:**
- Schedule: Every minute (`* * * * *`)
- Command: `cd /path/to/project && php artisan schedule:run`

**Setup time:** ~10-15 minutes (plus learning curve)

---

## Next Steps

After setting up CRON jobs:

1. ✅ Test both scripts via SSH or "Run Now" button
2. ✅ Wait for first CRON execution (or trigger manually)
3. ✅ Check `storage/logs/laravel.log` for confirmation
4. ✅ Verify database changes in `monthly_quota_tracker` table
5. ✅ Verify email notifications (if enabled)
6. ✅ Monitor for first full month cycle

**All done!** Your monthly quota system will now run automatically using direct PHP script execution. 🎉
