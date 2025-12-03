# Rank System Phase 5 - Quick Deployment Checklist

**Use this checklist during deployment to ensure nothing is missed.**

---

## Pre-Deployment (30 min before)

- [ ] Review full deployment guide
- [ ] Schedule maintenance window
- [ ] Notify team of deployment
- [ ] Prepare backup tools
- [ ] Test local environment one last time
- [ ] Verify admin credentials
- [ ] Check Hostinger access working

---

## Backup Phase (10 minutes)

### Database Backup
- [ ] Login to phpMyAdmin
- [ ] Export database to .sql.gz
- [ ] Download backup file
- [ ] Verify file size > 0
- [ ] Name: `database_backup_YYYYMMDD_HHMMSS.sql.gz`

### Files Backup
- [ ] Login to Hostinger File Manager
- [ ] Compress public_html folder
- [ ] Download backup .zip
- [ ] Verify archive size
- [ ] Name: `backup_YYYYMMDD_HHMMSS.zip`

### Backup Verification
- [ ] Both backups downloaded to local computer
- [ ] Files stored in safe location
- [ ] Can extract/open backup files

---

## File Deployment (15 minutes)

### Prepare Package
- [ ] Create phase5_deployment.zip locally
- [ ] Includes all Phase 5 files
- [ ] Verify zip structure correct

### Upload Files
- [ ] Login to Hostinger File Manager
- [ ] Navigate to public_html
- [ ] Upload phase5_deployment.zip
- [ ] Wait for upload complete (100%)
- [ ] Extract zip file
- [ ] Verify extraction successful
- [ ] Delete zip file

### Verify Upload
- [ ] Check AdminRankController.php exists
- [ ] Check resources/views/admin/ranks/ folder exists (3 files)
- [ ] Check sidebar.blade.php updated
- [ ] Check dashboard.blade.php updated
- [ ] Check routes/web.php updated
- [ ] Check package views updated

### Set Permissions (if needed)
- [ ] Directories: 755
- [ ] Files: 644
- [ ] Storage: 775
- [ ] Bootstrap/cache: 775

---

## Database Verification (5 minutes)

### Check Tables Exist
- [ ] Login to phpMyAdmin
- [ ] Select database
- [ ] Verify `rank_advancements` table exists
- [ ] Verify `packages` table has rank columns
- [ ] Verify `users` table has rank columns

### Check Columns
- [ ] packages: `is_rankable` column exists
- [ ] packages: `rank_name` column exists
- [ ] packages: `rank_order` column exists
- [ ] packages: `required_direct_sponsors` column exists
- [ ] packages: `next_rank_package_id` column exists
- [ ] users: `current_rank` column exists
- [ ] users: `rank_package_id` column exists
- [ ] users: `network_status` column exists

### Sample Data Check
- [ ] Run: `SELECT * FROM packages WHERE is_rankable = 1;`
- [ ] Run: `SELECT * FROM rank_advancements LIMIT 5;`
- [ ] Run: `SELECT * FROM users WHERE current_rank IS NOT NULL LIMIT 5;`

---

## Cache Management (5 minutes)

### Clear All Caches
- [ ] `php artisan cache:clear`
- [ ] `php artisan config:clear`
- [ ] `php artisan route:clear`
- [ ] `php artisan view:clear`
- [ ] `php artisan clear-compiled`

### Browser Cache
- [ ] Clear Cloudflare cache (if applicable)
- [ ] Clear browser cache (Ctrl+F5)
- [ ] Test in incognito window

---

## Testing Phase (15 minutes)

### Access Tests
- [ ] Homepage loads without errors
- [ ] Admin login works
- [ ] User login works

### Rank Dashboard
- [ ] Navigate: `/admin/ranks`
- [ ] Dashboard loads successfully
- [ ] Statistics cards display
- [ ] Rank packages table shows
- [ ] Chart renders (if data exists)
- [ ] No PHP/JavaScript errors

### Configuration Page
- [ ] Navigate: `/admin/ranks/configure`
- [ ] Form loads with packages
- [ ] All fields populated
- [ ] Change rank name test
- [ ] Click "Save Configuration"
- [ ] Success message appears
- [ ] Refresh page - changes saved

### Advancements Page
- [ ] Navigate: `/admin/ranks/advancements`
- [ ] Table loads with records
- [ ] Pagination works
- [ ] Per-page dropdown works
- [ ] Filter by type works
- [ ] Search by user works
- [ ] Clear filters works

### Sidebar Menu
- [ ] Check admin sidebar
- [ ] "Rank System" menu visible
- [ ] Star icon displays
- [ ] Expand menu
- [ ] 3 submenus visible: Dashboard, Configure, Advancements
- [ ] Click each submenu link
- [ ] All links work

### Package Management
- [ ] Navigate: `/admin/packages/create`
- [ ] "Rankable Package" checkbox visible
- [ ] Check checkbox
- [ ] Create package
- [ ] Package appears in rank configuration

### Package Edit
- [ ] Edit existing package
- [ ] "Rankable Package" toggle visible
- [ ] Test enable/disable (if no users)
- [ ] Test protection (if users have rank)
- [ ] Verify lock icon and warning message

### Manual Advancement
- [ ] Navigate: `/admin/users`
- [ ] Edit a user
- [ ] "Rank Management" card visible (left side)
- [ ] "Account Actions" card visible (right side)
- [ ] Click "Manual Rank Advance" button
- [ ] Modal opens
- [ ] Dropdown shows only higher ranks
- [ ] Select package and add notes
- [ ] Click "Advance Rank"
- [ ] Success message appears
- [ ] Rank updates in card
- [ ] Check advancement history - record added

### User Dashboard
- [ ] Logout from admin
- [ ] Login as regular user with rank
- [ ] Navigate: `/dashboard`
- [ ] Rank card visible below welcome
- [ ] Current rank badge shows
- [ ] Package info displays
- [ ] Next rank shows (if applicable)
- [ ] Progress bar displays
- [ ] No errors in console

### User Dashboard - No Rank
- [ ] Login as user without rank
- [ ] Navigate: `/dashboard`
- [ ] Rank card shows "No Rank Yet"
- [ ] Encouragement text displays
- [ ] No errors

---

## Performance Check (5 minutes)

### Page Load Times
- [ ] Dashboard: < 3 seconds
- [ ] Configure: < 2 seconds
- [ ] Advancements: < 3 seconds
- [ ] User dashboard: < 3 seconds

### Database Performance
- [ ] No slow query warnings
- [ ] Check phpMyAdmin processlist
- [ ] No long-running queries

### Error Logs
- [ ] Check: `storage/logs/laravel.log`
- [ ] No critical errors
- [ ] No warnings
- [ ] No rank-related errors

---

## Security Check (5 minutes)

### Access Control
- [ ] Non-admin CANNOT access `/admin/ranks`
- [ ] Redirect to login or 403 error
- [ ] Admin CAN access all rank pages

### CSRF Protection
- [ ] View page source on configure page
- [ ] `@csrf` token present in forms
- [ ] Forms submit successfully

### Data Exposure
- [ ] No sensitive data in HTML source
- [ ] No API keys visible
- [ ] No database credentials exposed

### SSL Certificate
- [ ] https:// active on all pages
- [ ] No mixed content warnings
- [ ] SSL certificate valid

---

## Post-Deployment (5 minutes)

### Documentation
- [ ] Mark deployment complete in tracker
- [ ] Document deployment time
- [ ] Note any issues encountered
- [ ] Update change log

### Communication
- [ ] Notify team deployment successful
- [ ] Share access URLs
- [ ] Provide quick demo (if needed)
- [ ] Share known issues list

### Monitoring Setup
- [ ] Set reminder to check logs in 1 hour
- [ ] Set reminder to check logs in 24 hours
- [ ] Monitor error tracking dashboard
- [ ] Watch support channels for feedback

---

## If Something Goes Wrong

### Quick Troubleshooting
1. [ ] Clear all caches again
2. [ ] Check error logs: `tail -50 storage/logs/laravel.log`
3. [ ] Verify files uploaded correctly
4. [ ] Check database connection
5. [ ] Test in different browser

### Emergency Rollback
1. [ ] Enable maintenance mode: `php artisan down`
2. [ ] Restore files from backup
3. [ ] Restore database from backup (if needed)
4. [ ] Clear all caches
5. [ ] Disable maintenance mode: `php artisan up`
6. [ ] Document what went wrong
7. [ ] Plan re-deployment

---

## Success Indicators

✅ **All checks passed above**  
✅ **No critical errors in logs**  
✅ **All features working as expected**  
✅ **Performance acceptable**  
✅ **Users can access system normally**  

---

## Time Estimates

| Phase | Estimated Time |
|-------|----------------|
| Backup | 10 minutes |
| File Upload | 15 minutes |
| Database Check | 5 minutes |
| Cache Clear | 5 minutes |
| Testing | 15 minutes |
| Performance/Security | 10 minutes |
| **Total** | **60 minutes** |

**Actual deployment time:** _____ minutes

---

## Notes & Issues Encountered

```
Document any issues here:

Issue 1:
- Problem: 
- Solution:
- Time to fix:

Issue 2:
- Problem:
- Solution:
- Time to fix:
```

---

## Sign-Off

**Deployment completed by:** ________________  
**Date:** ________________  
**Time started:** ________________  
**Time completed:** ________________  
**Status:** ☐ Success ☐ Partial ☐ Rolled Back  

**Verified by:** ________________  
**Date:** ________________  

---

**Deployment Status:** ⬜ NOT STARTED | ⬜ IN PROGRESS | ✅ COMPLETED

---

*Keep this checklist for your records and future reference.*
