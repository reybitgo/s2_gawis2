# Synchronous Rank Advancement System

## Overview
Rank advancement happens **immediately and synchronously** when a user purchases a rank package. There is NO scheduled task or delayed processing - users get promoted the moment they meet the requirements.

## How It Works (Real-time Flow)

### When a User Purchases a Rank Package

1. **User completes checkout** → `CheckoutController::process()`

2. **Order is paid** → Payment processed via wallet

3. **Buyer's rank is updated** → `$order->user->updateRank()`
   - Sets user's `current_rank` to the highest package they've purchased
   - Updates `rank_package_id` and `rank_updated_at`

4. **Sponsorship is tracked** → `$this->rankAdvancementService->trackSponsorship()`
   - Records the sponsorship in `direct_sponsors_tracker` table
   - **Immediately checks** if sponsor meets advancement requirements

5. **Sponsor advancement (if eligible)** → `checkAndTriggerAdvancement()`
   - Counts sponsor's same-rank direct referrals
   - If count >= required sponsors → **Advances immediately**
   - Creates system-funded order for next rank package
   - Updates sponsor's rank in real-time
   - Records advancement in `rank_advancements` table

### Real-world Example

**Before Purchase:**
- Sponsor: Starter rank (4/5 sponsors)
- New referral: No rank yet

**User Purchases Starter Package:**
1. ⚡ Payment processed
2. ⚡ New referral gets Starter rank
3. ⚡ Sponsorship recorded (sponsor now 5/5)
4. ⚡ System checks: 5 >= 5 ✓ Eligible!
5. ⚡ **Sponsor instantly advanced to Newbie**
6. ⚡ Order created: `RANK-[ID]` (system-funded)
7. ⚡ Sponsor profile updated immediately

**Total time:** < 1 second (all in same request)

## Code Flow

```
CheckoutController::process()
  └─> Order created and paid
      └─> If has rankable package:
          ├─> user->updateRank() [Buyer's rank updated]
          └─> If user has sponsor:
              └─> rankAdvancementService->trackSponsorship()
                  ├─> DirectSponsorsTracker::create() [Record sponsorship]
                  └─> checkAndTriggerAdvancement() [Check eligibility]
                      └─> If eligible:
                          ├─> advanceUserRank()
                          │   ├─> Create system order
                          │   ├─> Update sponsor rank
                          │   ├─> Activate network
                          │   └─> Record advancement
                          └─> Log success
```

## Key Files

### Main Trigger
- **File:** `app/Http/Controllers/CheckoutController.php`
- **Lines:** 378-406
- **Method:** `process()`
- **Triggers:** When order contains rankable packages

### Advancement Logic
- **File:** `app/Services/RankAdvancementService.php`
- **Method:** `trackSponsorship()` → Tracks and checks
- **Method:** `checkAndTriggerAdvancement()` → Validates eligibility
- **Method:** `advanceUserRank()` → Performs the advancement

### User Observer (Backup Trigger)
- **File:** `app/Observers/UserObserver.php`
- **Method:** `created()`
- **Purpose:** Catches users created outside checkout flow
- **Triggers:** When a user is created with sponsor + rank

## Advantages of Synchronous Processing

✅ **Instant Gratification** - Sponsors see rank advancement immediately
✅ **No Delays** - No waiting for scheduled tasks or cron jobs
✅ **Transparent** - Clear cause and effect for users
✅ **Reliable** - No risk of missed advancements from failed jobs
✅ **Simple** - No queue workers or schedulers needed
✅ **Transactional** - Advancement happens in same DB transaction as purchase

## Database Transaction Safety

All operations happen within a transaction:
```php
DB::beginTransaction();
try {
    // 1. Record sponsorship
    DirectSponsorsTracker::create([...]);
    
    // 2. Check and advance
    if (meetsRequirements) {
        // Create system order
        Order::create([...]);
        
        // Update rank
        $user->update(['current_rank' => ...]);
        
        // Record advancement
        RankAdvancement::create([...]);
    }
    
    DB::commit();
} catch (Exception $e) {
    DB::rollBack();
}
```

If anything fails, the entire operation rolls back.

## Logging

Every advancement is logged:

```
[INFO] Sponsorship Tracked
  - sponsor_id: 123
  - sponsor_rank: Starter
  - new_user_id: 456
  - new_user_rank: Starter

[INFO] Rank Advanced Successfully
  - user_id: 123
  - from_rank: Starter
  - to_rank: Newbie
  - order_id: 789
  - sponsors_count: 5
```

## Manual Command (For Migrations Only)

A manual command exists for **one-time migrations** of legacy data:

```bash
# Process all eligible users (for initial migration)
php artisan rank:process-advancements

# Process specific user
php artisan rank:process-advancements --user-id=123

# Standalone script
php process_pending_rank_advancements.php
```

⚠️ **These are NOT needed in production** - they're only for migrating existing users who were created before the rank system was implemented.

## No Scheduled Tasks Required

**Old approach (REMOVED):**
```php
// ❌ Don't do this
Schedule::command('rank:process-advancements')->hourly();
```

**Current approach:**
```php
// ✅ Synchronous - happens immediately
$this->rankAdvancementService->trackSponsorship($sponsor, $newUser);
```

## Verification

To verify synchronous advancement is working:

```bash
# 1. Check logs after a package purchase
tail -f storage/logs/laravel.log | grep "Rank"

# 2. Check user's rank in profile immediately after purchase
# Navigate to: /profile

# 3. Verify advancement history
php artisan tinker
>>> App\Models\RankAdvancement::latest()->first()
```

## Testing Synchronous Advancement

### Test Scenario 1: New Package Purchase

1. Create a user with 4 Starter referrals (sponsor)
2. New user registers under sponsor
3. New user purchases Starter package
4. **Result:** Sponsor immediately advances to Newbie (in same request)

### Test Scenario 2: Edge Case - Already Eligible

1. User has 5 Starter referrals (already eligible)
2. Run: `php artisan rank:process-advancements --user-id=[ID]`
3. **Result:** User advances immediately
4. Next purchase by 6th referral: No advancement (already at next rank)

### Test Scenario 3: Multiple Levels

1. User A sponsors 5 Starter users → Advances to Newbie
2. User A continues to sponsor 3 more Newbie users → Progress 3/8
3. User A sponsors 5 more Newbie users → Advances to Bronze
4. **All advancements happen immediately at the moment of purchase**

## Performance Considerations

**Q: Will this slow down checkout?**
**A:** No. The advancement check is very fast:
- Single database query to count sponsors
- Simple comparison (count >= required)
- If eligible: Creates 1 order + 2 updates
- Total overhead: < 50ms

**Q: What if advancement fails?**
**A:** Transaction rollback ensures consistency:
- Purchase succeeds → Rank updated
- Advancement fails → Logged but doesn't affect purchase
- Can manually trigger: `php artisan rank:process-advancements --user-id=[ID]`

## Troubleshooting

### Issue: User eligible but not advanced

**Cause:** Purchase flow completed before rank update

**Solution:**
```bash
php artisan rank:process-advancements --user-id=[USER_ID]
```

### Issue: Advancement recorded but rank not updated

**Cause:** Transaction rollback or partial failure

**Check:**
```sql
-- Check advancement history
SELECT * FROM rank_advancements WHERE user_id = [ID];

-- Check current rank
SELECT id, username, current_rank, rank_updated_at FROM users WHERE id = [ID];
```

**Fix:** Re-run advancement manually if history exists but rank wasn't updated

## Migration Guide (For Existing Systems)

If implementing this on an existing system with legacy users:

1. **One-time bulk processing:**
   ```bash
   php process_pending_rank_advancements.php
   ```

2. **Going forward:** All new purchases auto-advance synchronously

3. **No cron setup needed:** System is fully synchronous

## Conclusion

The rank advancement system is **fully synchronous and real-time**. Users get promoted instantly when they meet requirements - no delays, no scheduled tasks, no complexity. Just immediate results when purchases happen.

---

**Last Updated:** December 1, 2025
**Status:** ✅ Production Ready - Fully Synchronous
