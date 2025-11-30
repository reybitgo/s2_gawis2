# Rank Phase 2 - Quick Deployment Reference

## ⚡ Quick Deploy Checklist (Hostinger)

### Prerequisites ✅
- [ ] Phase 1 deployed in production
- [ ] Rank tables exist (rank_advancements, direct_sponsors_tracker)
- [ ] Users have current_rank, rank_package_id columns
- [ ] Packages have rank_name, rank_order columns
- [ ] At least 3 rank packages configured

### Files to Deploy 📁
1. **NEW**: `app/Services/RankComparisonService.php` (~6KB)
2. **MODIFIED**: `app/Services/MLMCommissionService.php` (~11KB)

### Deployment Steps (15 minutes) 🚀

#### 1. Backup (5 min)
```bash
# Database
mysqldump -u dbuser -p dbname > backup_phase2_$(date +%Y%m%d_%H%M%S).sql

# Service file
cp app/Services/MLMCommissionService.php app/Services/MLMCommissionService.php.backup_$(date +%Y%m%d_%H%M%S)
```

#### 2. Enable Maintenance (1 min)
```bash
php artisan down --secret="phase2-upgrade" --retry=60
```

#### 3. Upload Files (2 min)
```bash
# Upload to app/Services/:
- RankComparisonService.php (NEW)
- MLMCommissionService.php (REPLACE)
```

#### 4. Clear Caches (1 min)
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
```

#### 5. Quick Test (2 min)
```bash
php artisan tinker
```
```php
app(\App\Services\RankComparisonService::class);
app(\App\Services\MLMCommissionService::class);
echo "✓ Services loaded\n";
exit
```

#### 6. Disable Maintenance (1 min)
```bash
php artisan up
```

#### 7. Monitor (5 min)
```bash
tail -f storage/logs/laravel.log | grep "Rank-Aware"
```

### Verification ✓

**Check logs show:**
- "Rank-Aware Commission Calculated"
- "Rule applied: Rule X"
- No errors

**Commission Rules:**
- Same rank → Standard rate
- Higher rank → Lower rate (buyer's rate)
- Lower rank → Own rate

### Quick Rollback ⏪

```bash
# If issues:
php artisan down
cp app/Services/MLMCommissionService.php.backup_* app/Services/MLMCommissionService.php
rm app/Services/RankComparisonService.php
php artisan cache:clear
php artisan up
```

### Support 📞
- Hostinger: 24/7 Live Chat
- Backup location: Local + Cloud
- Full guide: RANK_PHASE2_PRODUCTION_DEPLOYMENT_GUIDE.md

---

**Deployment Time**: 15 minutes  
**Downtime**: ~5 minutes (with maintenance mode)  
**Risk Level**: LOW (only service files, no migrations)  
**Rollback Time**: 5 minutes
