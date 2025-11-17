# Phase 2 Deployment Checklist

**Date:** ________________  
**Deployed By:** ________________  
**Server:** ________________

---

## Pre-Deployment

- [ ] Phase 1 is deployed and working
- [ ] Read `PHASE2_SUMMARY.md`
- [ ] Test Phase 2 on localhost: `php test_phase2_monthly_quota.php`
- [ ] All 8 tests passed on localhost
- [ ] No database migrations needed (service layer only)

---

## Files to Upload (2 Required + 1 Optional)

### New Files
- [ ] `app/Services/MonthlyQuotaService.php` (7.2 KB)
- [ ] `test_phase2_monthly_quota.php` (10.8 KB) [Optional]

### Modified Files
- [ ] `app/Http/Controllers/CheckoutController.php` (~300 bytes added)

**Total:** 2 required files

---

## Deployment Method

### Option A: Via FTP/SFTP
- [ ] Upload `app/Services/MonthlyQuotaService.php`
- [ ] Upload `app/Http/Controllers/CheckoutController.php` (replace existing)
- [ ] Upload `test_phase2_monthly_quota.php` (optional)

### Option B: Via Git
```bash
git add app/Services/MonthlyQuotaService.php
git add app/Http/Controllers/CheckoutController.php
git add test_phase2_monthly_quota.php
git commit -m "Phase 2: Monthly Quota Service & Checkout Integration"
git push origin main
```

---

## Post-Upload Commands (SSH)

- [ ] SSH into server: `ssh user@your-server.com`
- [ ] Navigate to project: `cd /path/to/project`
- [ ] Pull changes (if Git): `git pull origin main`
- [ ] Clear config: `php artisan config:clear`
- [ ] Clear cache: `php artisan cache:clear`
- [ ] Clear routes: `php artisan route:clear`
- [ ] Clear views: `php artisan view:clear`
- [ ] Dump autoload: `composer dump-autoload`

---

## Verification (No Migrations!)

### Check Service is Registered
```bash
php artisan tinker --execute="echo App\Services\MonthlyQuotaService::class;"
```
- [ ] Should output: `App\Services\MonthlyQuotaService`
- [ ] No "Class not found" errors

### Test Service (Optional)
```bash
php test_phase2_monthly_quota.php
```
- [ ] TEST 1: Check Initial Quota Status - PASSED ✓
- [ ] TEST 2: Create Test Order - PASSED ✓
- [ ] TEST 3: Process Order Points - PASSED ✓
- [ ] TEST 4: Verify Tracker Record - PASSED ✓
- [ ] TEST 5: getUserMonthlyStatus - PASSED ✓
- [ ] TEST 6: getUserQuotaHistory - PASSED ✓
- [ ] TEST 7: Order with No Products - PASSED ✓
- [ ] TEST 8: Order with 0 PV Products - PASSED ✓

### Check Laravel Logs
```bash
tail -f storage/logs/laravel.log
```
- [ ] No errors related to MonthlyQuotaService
- [ ] No "Class not found" errors

---

## Application Testing

### Test Checkout Flow
- [ ] Login as a test user
- [ ] Add products to cart (with PV > 0)
- [ ] Complete checkout
- [ ] Payment processes successfully
- [ ] Order created successfully

### Verify PV Tracking
```bash
# Check Laravel logs for PV processing
tail -100 storage/logs/laravel.log | grep "PV Points"
```
- [ ] Should see: "PV Points Added to Monthly Tracker"
- [ ] Should see: "Monthly Quota & Unilevel Bonus Processing Completed"

### Verify Database Update
```sql
mysql -u USER -p DATABASE

-- Check latest tracker update
SELECT * FROM monthly_quota_tracker 
WHERE user_id = [TEST_USER_ID]
ORDER BY updated_at DESC LIMIT 1;
```
- [ ] `total_pv_points` increased correctly
- [ ] `last_purchase_at` updated to recent time
- [ ] `quota_met` reflects current status

---

## Monitoring (15 Minutes)

- [ ] Monitor Laravel logs: `tail -f storage/logs/laravel.log`
- [ ] Place 2-3 test orders with products
- [ ] Verify PV tracking logs appear
- [ ] Check no errors in logs
- [ ] Verify checkout works normally

---

## Rollback (If Needed)

### Quick Rollback Commands
```bash
# Restore old CheckoutController
git checkout HEAD~1 app/Http/Controllers/CheckoutController.php

# Remove service file
rm app/Services/MonthlyQuotaService.php

# Clear caches
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

- [ ] Rollback completed (if needed)
- [ ] Old checkout flow working
- [ ] No errors after rollback

---

## Success Verification

### Logs Check
```bash
# Check for PV processing logs
grep "PV Points Added" storage/logs/laravel.log | tail -5
```
- [ ] Recent PV processing logs found
- [ ] No error logs related to quota service

### Database Check
```sql
-- Check recent tracker updates
SELECT 
    u.username,
    mqt.total_pv_points,
    mqt.required_quota,
    mqt.quota_met,
    mqt.updated_at
FROM monthly_quota_tracker mqt
JOIN users u ON u.id = mqt.user_id
WHERE mqt.year = YEAR(NOW())
AND mqt.month = MONTH(NOW())
ORDER BY mqt.updated_at DESC
LIMIT 10;
```
- [ ] Recent orders show PV updates
- [ ] Values look correct
- [ ] Timestamps are recent

### Application Check
- [ ] Checkout flow works normally
- [ ] Products can be purchased
- [ ] No errors on confirmation page
- [ ] Order history shows correctly

---

## Clean Up

- [ ] Delete test script: `rm test_phase2_monthly_quota.php` (optional)
- [ ] Remove test orders from database (optional)
- [ ] Archive deployment notes

---

## Sign-Off

**Deployment Status:**
- [ ] SUCCESS - All checks passed
- [ ] PARTIAL - Some issues (document below)
- [ ] FAILED - Rollback completed

**Issues Encountered:**
```
(none)
```

**Production URL:** ________________________________

**Deployment Completed:** __________ (date/time)  
**Verified By:** __________  
**Ready for Phase 3:** YES [ ] / NO [ ]

---

## Notes

```
(add any notes about the deployment here)
```

---

## Phase 3 Preparation

**What's Coming in Phase 3:**
- Update Unilevel bonus distribution logic
- Use `qualifiesForUnilevelBonus()` instead of `isNetworkActive()`
- Skip uplines who haven't met monthly quota
- Enhanced logging
- NO database changes
- 1 file modified only

**Estimated Deployment:** 10-15 minutes

---

**Phase 2 Deployment:** COMPLETE [ ] / IN PROGRESS [ ] / PENDING [ ]
