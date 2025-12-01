# Phase 3 Quick Deployment Checklist

## Pre-Deployment (5 minutes)

```bash
# 1. Verify Phase 1 & 2 deployed
php artisan migrate:status | grep "2025_11_27"
# Must show ALL 4 as [X] Ran

# 2. Backup database
mysqldump -u DB_USER -p DB_NAME > backup_phase3_$(date +%Y%m%d_%H%M%S).sql

# 3. Backup code
tar -czf backup_code_phase3_$(date +%Y%m%d_%H%M%S).tar.gz public_html/

# 4. Verify rank packages exist
php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); echo App\Models\Package::whereNotNull('rank_name')->count();"
# Must show: 3 (or more)
```

## Deployment (10 minutes)

```bash
# 1. Enable maintenance mode (optional)
php artisan down --message="Upgrading rank system..."

# 2. Pull latest code
git pull origin main

# 3. Update dependencies
composer install --no-dev --optimize-autoloader

# 4. Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 5. Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Disable maintenance mode
php artisan up
```

## Verification (5 minutes)

```bash
# 1. Check files exist
ls -la app/Services/RankAdvancementService.php
ls -la app/Console/Commands/BackfillLegacySponsorships.php

# 2. Verify Artisan command
php artisan list | grep rank
# Should show: rank:backfill-legacy-sponsorships

# 3. Check package config
php check_rank_packages.php
# Verify: Starter→Newbie→Bronze with correct requirements

# 4. Test dry run
php artisan rank:backfill-legacy-sponsorships --dry-run
# Should show: Total backfilled: X, Total skipped: Y (no errors)

# 5. Check logs
tail -50 storage/logs/laravel.log
# Should show: No PHP errors
```

## Optional: Backfill Legacy Data

```bash
# Preview first
php artisan rank:backfill-legacy-sponsorships --dry-run

# If numbers look good, run for real
php artisan rank:backfill-legacy-sponsorships --check-advancements
```

## Monitoring (First 24 hours)

```bash
# Real-time log monitoring
tail -f storage/logs/laravel.log | grep "Rank"

# Check advancements (every 2-4 hours)
mysql -u USER -p -e "SELECT COUNT(*) as advancements FROM rank_advancements WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR);" DB_NAME
```

## Rollback (If Needed)

```bash
# Code rollback
git reset --hard <previous_commit_hash>
composer install --no-dev --optimize-autoloader
php artisan config:clear && php artisan cache:clear

# Database rollback (only if corrupted)
mysql -u USER -p DB_NAME < backup_phase3_YYYYMMDD_HHMMSS.sql
```

## Success Indicators

- ✅ No errors in `laravel.log`
- ✅ Artisan command appears in `php artisan list`
- ✅ Rank packages configured correctly
- ✅ Test order completes successfully
- ✅ Sponsorship tracking logs appear
- ✅ No user complaints

## Key Files Deployed

- `app/Services/RankAdvancementService.php` (NEW)
- `app/Console/Commands/BackfillLegacySponsorships.php` (NEW)
- `app/Http/Controllers/CheckoutController.php` (MODIFIED)
- `app/Models/User.php` (MODIFIED)

## Important Notes

- ⚠️ Phase 1 & 2 MUST be deployed first
- ⚠️ Backup before deployment (database + code)
- ⚠️ Deploy during off-peak hours
- ⚠️ Legacy backfill is OPTIONAL (system handles gradually)
- ⚠️ Monitor logs closely for first 24 hours

## Quick Test Commands

```bash
# Check user rank
php artisan tinker --execute="\$u=App\Models\User::find(5); echo \$u->getRankName();"

# Check sponsor count
php artisan tinker --execute="\$u=App\Models\User::find(5); echo \$u->getSameRankSponsorsCount();"

# Check advancements
mysql -u USER -p -e "SELECT * FROM rank_advancements ORDER BY created_at DESC LIMIT 5;" DB_NAME
```

---

**Deployment Time**: 15-30 minutes total
**Downtime**: 0-5 minutes (if using maintenance mode)
**Risk Level**: Low (no database migrations, backward compatible)
