# Phase 4 Deployment - Quick Reference Card

**PRINT THIS PAGE** - Keep handy during deployment

---

## ⏱️ Deployment Timeline

**Total Time:** 15-20 minutes  
**Downtime:** ~2 minutes (migration only)

1. Backup: 3-5 minutes
2. Upload Files: 5-7 minutes
3. Migrate Legacy Users: 2-3 minutes
4. Clear Caches: 1 minute
5. Verify: 5-7 minutes

---

## 📋 Pre-Flight Checklist

- [ ] All Phase 4 tests passed locally
- [ ] Database backup created
- [ ] Files backup created
- [ ] SSH access ready
- [ ] cPanel access ready
- [ ] FTP credentials ready

---

## 🔑 Critical Commands (SSH)

```bash
# 1. Connect to server
ssh u123456789@your-server-ip

# 2. Navigate to project
cd ~/domains/s2gawis2.com/public_html

# 3. Backup database
mysqldump -u DB_USER -p DB_NAME > ~/backup_$(date +%F).sql

# 4. Pull changes (if using Git)
git pull origin main

# 5. Migrate legacy users
php migrate_legacy_rank_data.php

# 6. Clear caches
php artisan view:clear
php artisan config:clear
php artisan cache:clear

# 7. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📁 Files to Upload (FTP Method)

If not using Git, upload these files:

```
resources/views/profile/show.blade.php
resources/views/admin/users.blade.php
app/Observers/UserObserver.php
migrate_legacy_rank_data.php
```

**Set permissions after upload:**
```bash
chmod 644 resources/views/profile/show.blade.php
chmod 644 resources/views/admin/users.blade.php
```

---

## ✅ Quick Verification

### 1. Check Site Status
```bash
curl -I https://yourdomain.com
# Should return: HTTP/2 200
```

### 2. Test Database
```bash
php artisan tinker
>>> DB::connection()->getPdo();
>>> App\Models\User::count()
>>> exit
```

### 3. Check Migration Status
```bash
php artisan migrate:status
# All Phase 3 migrations should show [X] Ran
```

### 4. View Recent Advancements
```bash
php artisan tinker
>>> App\Models\RankAdvancement::latest()->first()
>>> exit
```

---

## 🧪 Manual Testing URLs

**Open in browser and verify:**

1. **Profile Page (Ranked User):**
   ```
   https://yourdomain.com/login
   → Login as admin
   → Click Profile
   → Verify: Rank card displays
   ```

2. **Admin Users Table:**
   ```
   https://yourdomain.com/admin/users
   → Verify: Username (not fullname)
   → Verify: Income column shows amounts
   → Verify: Rank badges display
   ```

3. **Mobile View:**
   ```
   Open on phone OR
   Browser DevTools (F12) → Toggle Device Toolbar
   → Test profile and admin pages
   ```

---

## 🚨 Rollback (If Needed)

### Quick Rollback - Views Only
```bash
git checkout HEAD~1 resources/views/profile/show.blade.php
git checkout HEAD~1 resources/views/admin/users.blade.php
php artisan view:clear
```

### Full Rollback - Database
```bash
mysql -u DB_USER -p DB_NAME < ~/backup_YYYY-MM-DD.sql
```

---

## 🐛 Common Issues & Fixes

### Issue: Rank card not showing
```bash
php artisan view:clear
php artisan cache:clear
```

### Issue: "Class not found" error
```bash
composer dump-autoload
php artisan config:clear
```

### Issue: Database errors
```bash
# Check .env database credentials
cat .env | grep DB_
```

### Issue: Wallet shows ₱0.00
```bash
php artisan tinker
>>> $user = App\Models\User::with('wallet')->first();
>>> $user->wallet->withdrawable_balance
```

---

## 📊 Success Criteria

**Check these before calling deployment complete:**

- [ ] Site loads without errors
- [ ] Profile page shows rank card
- [ ] Admin table shows username (not fullname)
- [ ] Income column shows correct amounts
- [ ] Rank badges display with colors
- [ ] Wallet shows combined balance
- [ ] Mobile view works
- [ ] No errors in logs: `tail -f storage/logs/laravel.log`
- [ ] Legacy users migrated successfully

---

## 📞 Emergency Commands

### Enable Maintenance Mode
```bash
php artisan down --message="Brief maintenance - back shortly"
```

### Disable Maintenance Mode
```bash
php artisan up
```

### Check Error Logs
```bash
tail -n 100 storage/logs/laravel.log
```

### Clear All Caches (Nuclear Option)
```bash
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan optimize:clear
```

---

## 📈 Post-Deployment Monitoring

**Check every 6 hours for first 24 hours:**

```bash
# Watch logs
tail -f storage/logs/laravel.log

# Check for errors
grep "error" storage/logs/laravel.log | tail -n 20

# Monitor rank advancements
php artisan tinker
>>> App\Models\RankAdvancement::where('created_at', '>=', now()->subDay())->count()
```

---

## 🎯 Key Phase 4 Features

**What users will see:**

1. ✅ **Profile Page:**
   - Rank badge (Starter/Newbie/Bronze/Unranked)
   - Progress bar towards next rank
   - Sponsor count (X/Y required)
   - Rank advancement history
   - Combined wallet balance

2. ✅ **Admin Table:**
   - Username column (instead of fullname)
   - Income column (withdrawable balance)
   - Rank column with badges
   - Color-coded ranks

3. ✅ **Automatic Advancement:**
   - Instant promotion when requirements met
   - No scheduled tasks needed
   - Real-time updates during purchase

---

## 📚 Full Documentation

For detailed information, see:
- `RANK_PHASE4_PRODUCTION_DEPLOYMENT.md` (This full guide)
- `SYNCHRONOUS_RANK_ADVANCEMENT.md` (How advancement works)
- `RANK_PHASE4_TESTING_GUIDE.md` (Testing procedures)

---

**DEPLOYMENT READY!** 🚀

**Deployed By:** _____________  
**Date & Time:** _____________  
**Status:** ☐ Success ☐ Issues  

---

**Notes:**
