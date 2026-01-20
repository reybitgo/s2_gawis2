# Deployment Quick Reference

## Critical Files for Deployment

### 1. Migration Files (MUST RUN IN ORDER)

```
1. 2026_01_20_084117_update_rank_advancements_enum_for_dual_path.php (CRITICAL - FIRST!)
2. 2026_01_19_154333_add_ppv_gpv_to_packages_table.php
3. 2026_01_19_154333_add_ppv_gpv_to_users_table.php
4. 2026_01_19_154333_create_points_tracker_table.php
5. 2026_01_19_174558_update_packages_with_ppv_gpv_defaults.php
6. 2026_01_19_205840_ensure_ppv_gpv_defaults_for_existing_data.php
7. 2026_01_19_211559_optimize_ppv_gpv_performance.php
```

### 2. Deployment Scripts

```bash
# Deploy to staging
./deploy.sh --staging

# Deploy to production
./deploy.sh --production

# Dry-run (no changes)
./deploy.sh --dry-run

# Rollback
./rollback.sh [TIMESTAMP]
# Or rollback latest
./rollback.sh
```

### 3. Key Commands

```bash
# Database backup
mysqldump -u user -p database > backup.sql

# Run migrations
php artisan migrate --force

# Run specific migration
php artisan migrate --path=database/migrations/FILE.php

# Rollback migrations
php artisan migrate:rollback

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Enable maintenance
php artisan down

# Disable maintenance
php artisan up

# Restart queue
php artisan queue:restart

# Recalculate GPV
php artisan ppv:recalculate-gpv --user_id
php artisan ppv:recalculate-gpv --force
```

### 4. Database Schema Changes Summary

**New Tables:**
- `points_tracker`

**New Columns (users):**
- `current_ppv` (decimal:2)
- `current_gpv` (decimal:2)
- `ppv_gpv_updated_at` (timestamp)

**New Columns (packages):**
- `required_sponsors_ppv_gpv` (integer)
- `ppv_required` (decimal:2)
- `gpv_required` (decimal:2)
- `rank_pv_enabled` (boolean)

**Modified Enum (rank_advancements):**
- Old: `['purchase', 'sponsorship_reward', 'admin_adjustment']`
- New: `['purchase', 'sponsorship_reward', 'admin_adjustment', 'recruitment_based', 'pv_based']`

### 5. Default PPV/GPV Values

| Rank | Sponsors | PPV | GPV | PV Enabled |
|-------|----------|------|------|------------|
| Starter | 4 | 0 | 0 | true |
| Newbie | 4 | 100 | 1000 | true |
| 1 Star | 4 | 300 | 5000 | true |
| 2 Star | 4 | 500 | 15000 | true |
| 3 Star | 4 | 800 | 40000 | true |
| 4 Star | 4 | 1200 | 100000 | true |
| 5 Star | 4 | 2000 | 250000 | true |

### 6. Pre-Deployment Checklist

- [ ] Staging environment tested
- [ ] Database backed up
- [ ] Code backed up (git tag)
- [ ] Team notified
- [ ] Maintenance window scheduled
- [ ] User announcement sent (24 hours before)

### 7. Deployment Steps

```bash
# 1. Backup database and code
./deploy.sh --dry-run  # Check configuration

# 2. Deploy to staging first
./deploy.sh --staging

# 3. Test staging thoroughly
# - Check dashboard
# - Test admin configuration
# - Test order processing
# - Test rank advancement

# 4. Schedule production maintenance (2 hours notice)

# 5. Deploy to production
./deploy.sh --production

# 6. Verify deployment
# - Check application loads
# - Test user login
# - Test admin access
# - Monitor logs

# 7. Send user announcement

# 8. Monitor first 24 hours
# - Check logs every hour
# - Monitor queue processing
# - Watch for errors
# - Respond to user feedback
```

### 8. Rollback Criteria

Rollback if:
- Database migration fails
- Application crashes on load
- Critical features broken
- High error rate (>5%)
- Data corruption detected

### 9. Monitoring Commands

```bash
# Check application logs
tail -f storage/logs/laravel.log

# Check queue status
php artisan queue:status

# Check database connections
mysql -u user -p -e "SHOW PROCESSLIST" database

# Check slow queries
tail -f /var/log/mysql/slow.log

# Check server load
top

# Check disk space
df -h
```

### 10. Troubleshooting

**Issue:** Enum migration fails
```bash
# Run separately first
php artisan migrate --path=database/migrations/2026_01_20_084117_update_rank_advancements_enum_for_dual_path.php
```

**Issue:** Points not crediting
```bash
# Check queue workers
php artisan queue:work --stop-when-empty

# Manually process order points
php artisan tinker
```
```php
$order = App\Models\Order::find(ORDER_ID);
app(App\Services\PointsService::class)->processOrderPoints($order);
exit;
```

**Issue:** Dashboard showing wrong progress
```bash
# Clear user cache
php artisan cache:forget "user_rank_progress_USER_ID"

# Recalculate GPV
php artisan ppv:recalculate-gpv USER_ID
```

---

## Contact Information

**Deployment Guide:** `rank_repeat_purchase_deploy.md`
**Implementation Guide:** `rank_repeat_purchase.md`
**Phase 8 Summary:** `rank_repeat_purchase_phase8.md`
**Phase 9 Summary:** `rank_repeat_purchase_phase9.md`
**Complete Status:** `rank_repeat_purchase_complete.md`

**Support:** support@your-company.com
**Development:** dev@your-company.com
**Emergency:** emergency@your-company.com

---

*Keep this reference card handy during deployment for quick access to critical commands and information.*
