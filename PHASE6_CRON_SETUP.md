# Phase 6: CRON Job Setup Guide

## Overview

Phase 6 implements automated tasks for the Monthly Quota System:
1. **Reset Monthly Quotas** - Runs daily at 12:01 AM, but only executes on the 1st of each month
2. **Send Quota Reminders** - Runs daily at 9:00 AM, but only executes on the 25th of each month

---

## Quick Start

### For Hostinger Shared Hosting

**Step 1: Access CRON Jobs**
1. Login to Hostinger hPanel
2. Navigate to: **Advanced → Cron Jobs**
3. Click: **"Create Cron Job"**

**Step 2: Add Reset Monthly Quotas CRON**
```
Type: Common Settings
Command: /usr/bin/php /home/u938213108/public_html/s2/crons/reset_monthly_quota.php

Schedule:
  Minute: 1
  Hour: 0
  Day: * (Every day - script checks internally for 1st of month)
  Month: * (Every month)
  Weekday: * (Every day of week)
  
Email notification: (Optional - your email for logs)
```

**Step 3: Add Send Quota Reminders CRON**
```
Type: Common Settings
Command: /usr/bin/php /home/u938213108/public_html/s2/crons/send_quota_reminders.php

Schedule:
  Minute: 0
  Hour: 9
  Day: * (Every day - script checks internally for 25th of month)
  Month: * (Every month)
  Weekday: * (Every day of week)
  
Email notification: (Optional - your email for logs)
```

---

## Option A: Standalone PHP Scripts (Recommended)

### Advantages
- ✅ Simple and straightforward
- ✅ Works on all hosting environments
- ✅ No Laravel Scheduler knowledge needed
- ✅ Direct PHP CLI execution
- ✅ Easy to test via SSH

### Files Created
- `crons/reset_monthly_quota.php` - Monthly reset script
- `crons/send_quota_reminders.php` - Reminder notification script

### Hostinger CRON Syntax
```
# Task 1: Reset Monthly Quotas (1st of month, 12:01 AM)
/usr/bin/php /home/u938213108/public_html/s2/crons/reset_monthly_quota.php

# Task 2: Send Quota Reminders (25th of month, 9:00 AM)
/usr/bin/php /home/u938213108/public_html/s2/crons/send_quota_reminders.php
```

**Important Notes:**
- Replace `/home/u938213108/public_html/s2/` with your actual project path
- Path format: `/home/YOUR_USERNAME/public_html/YOUR_PROJECT/crons/script.php`
- PHP binary path is typically `/usr/bin/php` on Hostinger

---

## Option B: Laravel Console Commands (Alternative)

### Advantages
- ✅ Laravel best practices
- ✅ Better integration with Laravel ecosystem
- ✅ Can use `--force` flag for testing
- ✅ Easier to extend in future

### Commands Created
- `php artisan quota:reset-monthly` - Reset monthly quotas
- `php artisan quota:send-reminders` - Send reminder notifications

### Hostinger CRON Syntax
```
# Task 1: Reset Monthly Quotas (1st of month, 12:01 AM)
cd /home/u938213108/public_html/s2 && /usr/bin/php artisan quota:reset-monthly

# Task 2: Send Quota Reminders (25th of month, 9:00 AM)
cd /home/u938213108/public_html/s2 && /usr/bin/php artisan quota:send-reminders
```

---

## Testing Before Deployment

### Test Standalone PHP Scripts (Option A)

**Via SSH:**
```bash
# Connect to Hostinger via SSH
ssh u938213108@yourdomain.com

# Navigate to project
cd /home/u938213108/public_html/s2

# Test reset script
/usr/bin/php crons/reset_monthly_quota.php

# Test reminders script
/usr/bin/php crons/send_quota_reminders.php
```

**Local Testing (Windows - Laragon):**
```powershell
# Open PowerShell/CMD
cd C:\laragon\www\s2_gawis2

# Test reset script
php crons\reset_monthly_quota.php

# Test reminders script
php crons\send_quota_reminders.php
```

### Test Laravel Commands (Option B)

**Via SSH:**
```bash
cd /home/u938213108/public_html/s2

# Test reset command
php artisan quota:reset-monthly

# Test reminders command (use --force to bypass date check)
php artisan quota:send-reminders --force
```

**Local Testing (Windows - Laragon):**
```powershell
cd C:\laragon\www\s2_gawis2

# Test reset command
php artisan quota:reset-monthly

# Test reminders command
php artisan quota:send-reminders --force
```

---

## Verifying CRON Execution

### Check Laravel Logs
```bash
# Via SSH
tail -50 storage/logs/laravel.log

# Or download and view locally
```

**Look for log entries:**
```
[YYYY-MM-DD HH:MM:SS] local.INFO: Monthly quota reset completed via CRON
[YYYY-MM-DD HH:MM:SS] local.INFO: Quota reminders sent via CRON
```

### Check Hostinger CRON Logs
1. Go to hPanel → Advanced → Cron Jobs
2. Click on the CRON job
3. View "Last Execution" status
4. Check email notifications (if configured)

### Database Verification
```sql
-- Check if new trackers were created for current month
SELECT * FROM monthly_quota_tracker 
WHERE year = YEAR(NOW()) AND month = MONTH(NOW())
ORDER BY created_at DESC;

-- Check notification logs
SELECT * FROM notifications 
WHERE type LIKE '%quota%' 
ORDER BY created_at DESC 
LIMIT 10;
```

---

## CRON Schedule Reference

### Reset Monthly Quotas
- **When:** Runs daily at 12:01 AM, but only executes on the 1st of each month
- **Purpose:** Create new trackers for all active users, reset PV to 0
- **Impact:** Users start fresh each month
- **Why Daily:** Allows precise day-of-month detection and easier debugging

**CRON Expression:**
```
1 0 * * *
│ │ │ │ │
│ │ │ │ └─ Weekday (any)
│ │ │ └─── Month (any)
│ │ └───── Day (every day - script checks for 1st)
│ └─────── Hour (0 = midnight)
└───────── Minute (1)
```

### Send Quota Reminders
- **When:** Runs daily at 9:00 AM, but only executes on the 25th of each month
- **Purpose:** Remind users who haven't met quota
- **Impact:** Email + database notifications sent
- **Why Daily:** Allows precise day-of-month detection and easier debugging

**CRON Expression:**
```
0 9 * * *
│ │ │ │ │
│ │ │ │ └─ Weekday (any)
│ │ │ └─── Month (any)
│ │ └───── Day (every day - script checks for 25th)
│ └──────── Hour (9 = 9 AM)
└────────── Minute (0)
```

---

## Troubleshooting

### Issue: CRON not running

**Check 1: Path is correct**
```bash
# Verify PHP path
which php
# Usually: /usr/bin/php

# Verify project path
pwd
# Should show: /home/u938213108/public_html/s2
```

**Check 2: File permissions**
```bash
# Make scripts executable
chmod +x crons/reset_monthly_quota.php
chmod +x crons/send_quota_reminders.php
```

**Check 3: Test manually**
```bash
# Run script directly to see errors
/usr/bin/php crons/reset_monthly_quota.php
```

### Issue: No logs appearing

**Check Laravel log file:**
```bash
# View latest logs
tail -100 storage/logs/laravel.log

# Check file permissions
ls -la storage/logs/
# Should be writable by web server user
```

**Check email notifications:**
- Enable email notifications in Hostinger CRON settings
- Check spam folder for CRON emails

### Issue: Reminders not sending

**Check 1: Date restriction**
- Reminders only send on the 25th of month
- Script will exit early on other days (this is normal)
- Check logs to confirm script is running daily

**Check 2: Users have verified emails**
- Script skips users without `email_verified_at`

**Check 3: Users actually need reminders**
```sql
-- Check users who should get reminders
SELECT u.username, mqt.total_pv_points, mqt.required_quota, mqt.quota_met
FROM monthly_quota_tracker mqt
JOIN users u ON mqt.user_id = u.id
WHERE mqt.year = YEAR(NOW()) 
  AND mqt.month = MONTH(NOW())
  AND mqt.quota_met = FALSE
  AND mqt.required_quota > 0;
```

### Issue: Database connection errors

**Check .env file:**
```bash
# Verify database credentials
cat .env | grep DB_
```

**Check database is accessible:**
```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

---

## Windows Task Scheduler Setup (Local Development)

### Task 1: Reset Monthly Quotas

1. Open Task Scheduler (taskschd.msc)
2. Create Basic Task
   - Name: "Gawis Monthly Quota Reset"
   - Trigger: Monthly, 1st day, 12:01 AM
   - Action: Start a program
     - Program: `C:\laragon\bin\php\php-8.x\php.exe`
     - Arguments: `C:\laragon\www\s2_gawis2\crons\reset_monthly_quota.php`
     - Start in: `C:\laragon\www\s2_gawis2`

### Task 2: Send Quota Reminders

1. Open Task Scheduler
2. Create Basic Task
   - Name: "Gawis Quota Reminders"
   - Trigger: Monthly, 25th day, 9:00 AM
   - Action: Start a program
     - Program: `C:\laragon\bin\php\php-8.x\php.exe`
     - Arguments: `C:\laragon\www\s2_gawis2\crons\send_quota_reminders.php`
     - Start in: `C:\laragon\www\s2_gawis2`

---

## Production Deployment Checklist

Before going live:

- [ ] Test both scripts manually via SSH
- [ ] Verify Laravel logs show successful execution
- [ ] Check database trackers are created correctly
- [ ] Verify notifications are sent to test users
- [ ] Configure CRON jobs in Hostinger hPanel
- [ ] Enable email notifications in CRON settings
- [ ] Document CRON setup for team reference
- [ ] Set up monitoring alerts for failed tasks (optional)
- [ ] Test CRON execution for at least 2 months

---

## Monitoring & Maintenance

### Daily
- Not required

### Weekly
- Check Laravel logs for any CRON errors

### Monthly (After 1st)
- Verify new monthly_quota_tracker records created
- Check all active users have trackers for current month

### Monthly (After 25th)
- Verify reminder emails sent to users who need them
- Check notification logs in database

### Quarterly
- Review CRON execution history
- Optimize scripts if needed
- Update documentation if processes change

---

## Emergency Procedures

### Disable CRON temporarily
1. Login to Hostinger hPanel
2. Go to Advanced → Cron Jobs
3. Click "Pause" or "Delete" on the specific job

### Manual execution (if CRON missed)
```bash
# Connect via SSH
ssh u938213108@yourdomain.com

# Run missed tasks manually
cd /home/u938213108/public_html/s2
/usr/bin/php crons/reset_monthly_quota.php
/usr/bin/php crons/send_quota_reminders.php
```

### Rollback CRON changes
- Simply delete the CRON jobs from Hostinger hPanel
- Previous month's data remains intact
- No database rollback needed

---

## Support & Resources

**Hostinger Documentation:**
- https://support.hostinger.com/en/articles/1583279-how-to-set-up-a-cron-job

**Laravel Console Commands:**
- https://laravel.com/docs/11.x/artisan

**Project Files:**
- Console Commands: `app/Console/Commands/`
- CRON Scripts: `crons/`
- Notifications: `app/Notifications/`
- Service: `app/Services/MonthlyQuotaService.php`

**Logs:**
- Laravel Logs: `storage/logs/laravel.log`
- Hostinger CRON Logs: hPanel → Advanced → Cron Jobs

---

## Summary

**Recommendation:** Start with **Option A (Standalone PHP Scripts)** for simplicity and reliability on Hostinger shared hosting.

**Why?**
- ✅ Proven to work on Hostinger
- ✅ Easier to debug
- ✅ No Laravel Scheduler complexity
- ✅ Direct PHP execution

You can always migrate to Option B (Laravel Commands) later if needed.

**Next Steps:**
1. Test scripts locally first
2. Test scripts via SSH on production
3. Configure CRON jobs in Hostinger hPanel
4. Monitor first execution
5. Verify logs and database changes

---

**Document Version:** 1.0  
**Last Updated:** 2025-11-21  
**Phase:** Phase 6 - Automation & Scheduling
