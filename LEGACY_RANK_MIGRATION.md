# Legacy Rank Data Migration Guide

## Overview
This guide explains how to migrate existing users who purchased rank packages before Phase 4 (Rank UI Integration) to ensure their rank information is properly displayed in the system.

## Problem
Users who purchased rank packages during Phase 1-3 had their purchases recorded in the `orders` and `order_items` tables, but their rank information wasn't stored in the `users` table fields:
- `current_rank`
- `rank_package_id`
- `rank_updated_at`

Additionally, these users didn't have entries in the `rank_advancements` table to track their rank history.

## Solution
The `migrate_legacy_rank_data.php` script automatically:

1. **Finds legacy users** - Identifies users with purchased rank packages but missing rank data
2. **Updates user records** - Sets `current_rank`, `rank_package_id`, and `rank_updated_at` based on their highest-priced package
3. **Creates rank history** - Generates `rank_advancements` records for tracking
4. **Preserves purchase dates** - Uses the original order date as `rank_updated_at`

## Usage

### Running the Migration

```bash
php migrate_legacy_rank_data.php
```

### Expected Output

```
=== Migrating Legacy Rank Data for Phase 4 ===

Step 1: Finding legacy users with purchased packages...
Found 10 legacy users without rank data

Processing User ID: 1 (admin)...
  ✓ Updated to Starter rank (with history created)
Processing User ID: 2 (member)...
  ✓ Updated to Starter rank (with history created)
...

=== Migration Complete ===

📊 Summary:
  ✓ Users updated: 10
  ⚠ Users skipped: 0
  ✗ Errors: 0
```

## What Gets Updated

### Users Table
- `current_rank` → Set to highest rank package purchased
- `rank_package_id` → Set to highest-priced package ID
- `rank_updated_at` → Set to date of package purchase

### Rank Advancements Table
New record created with:
- `from_rank` → null (initial rank)
- `to_rank` → Package rank name
- `from_package_id` → null
- `to_package_id` → Package ID
- `advancement_type` → 'purchase'
- `created_at` → Original purchase date

## Safety Features

1. **Transaction-based** - Each user update is wrapped in a database transaction
2. **Error handling** - Errors are caught and logged without stopping the entire migration
3. **Idempotent** - Safe to run multiple times (skips already-migrated users)
4. **Read-only detection** - Only finds users with `current_rank = NULL`

## When to Run

Run this migration script:
- **After deploying Phase 4** - To ensure existing users see their ranks
- **One-time only** - Unless you reset rank data for testing
- **Before testing Phase 4 UI** - So legacy users have proper rank displays

## Verification

After running the migration, verify:

```bash
# Check updated users
php artisan tinker
>>> App\Models\User::whereNotNull('current_rank')->count()

# View a specific user's rank
>>> $user = App\Models\User::find(1);
>>> $user->current_rank
>>> $user->rankPackage->name

# Check rank advancement history
>>> App\Models\RankAdvancement::where('advancement_type', 'purchase')->count()
```

## Rollback

If needed, to reset migrated data:

```sql
-- Reset user rank data
UPDATE users 
SET current_rank = NULL, rank_package_id = NULL, rank_updated_at = NULL 
WHERE id IN (1,2,3,4,5,6,7,8,9,10);

-- Delete created advancement records
DELETE FROM rank_advancements 
WHERE advancement_type = 'purchase' 
AND from_rank IS NULL;
```

## Migration Results (Dec 1, 2025)

✅ Successfully migrated 10 legacy users:
- admin (ID: 1) → Starter
- member (ID: 2) → Starter  
- gawis (ID: 3) → Starter
- gawis1-7 (IDs: 4-10) → Starter

All users can now see their rank information in:
- User Profile page
- Admin User Management table
- Rank Progress indicators
- MLM Genealogy views

## Notes

- **Highest package priority** - If a user purchased multiple packages, the highest-priced one determines their rank
- **Purchase date preserved** - The original order date is used for `rank_updated_at`
- **Automatic activation** - Users with paid orders are assumed to be network active
- **Safe re-run** - Script checks for existing rank data and skips already-migrated users

## Related Files

- Migration script: `migrate_legacy_rank_data.php`
- Test user setup: `setup_phase4_test_users.php`
- Phase 4 documentation: `PHASE4_TESTING_GUIDE.md`
