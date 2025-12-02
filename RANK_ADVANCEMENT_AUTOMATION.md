# Rank Advancement Automation Guide

## Overview
Users are automatically promoted to the next rank when they meet the sponsorship requirements. The system checks eligibility and processes advancements without manual intervention.

## How It Works

### Automatic Triggers

1. **Real-time (Immediate)**
   - When a new ranked user registers with a sponsor
   - The `UserObserver` automatically tracks the sponsorship
   - Checks if the sponsor is now eligible for advancement
   - Advances the sponsor immediately if requirements are met

2. **Scheduled Processing (Hourly)**
   - A scheduled task runs every hour: `php artisan rank:process-advancements`
   - Checks ALL ranked users for eligibility
   - Processes any pending advancements
   - Configured in `routes/console.php`

### Advancement Requirements

To advance to the next rank, users must:
- Be at a rank below the top rank (Bronze)
- Have sponsored the required number of users at their current rank level

**Example:**
- **Starter → Newbie**: Requires 5 Starter-rank direct sponsors
- **Newbie → Bronze**: Requires 8 Newbie-rank direct sponsors

### What Happens During Advancement

When a user meets the requirements:

1. **System creates a reward order**
   - Order type: `system_reward`
   - Package: Next rank package
   - Status: `paid` (system-funded)
   - Order number: `RANK-[unique_id]`

2. **User rank is updated**
   - `current_rank` → New rank name
   - `rank_package_id` → New package ID
   - `rank_updated_at` → Current timestamp

3. **Network status activated**
   - User becomes eligible for MLM commissions

4. **Advancement history recorded**
   - Saved in `rank_advancements` table
   - Tracks: from/to ranks, sponsor count, system-paid amount

5. **Notifications sent**
   - Database notification
   - Email notification (TODO)

## Manual Commands

### Process All Users
```bash
php artisan rank:process-advancements
```
Checks all ranked users and processes eligible advancements.

### Process Specific User
```bash
php artisan rank:process-advancements --user-id=123
```
Checks and processes advancement for a single user.

### One-time Batch Processing
```bash
php process_pending_rank_advancements.php
```
Standalone script to process all pending advancements (useful for migrations).

## Scheduled Task Setup

### Cron Configuration

Add this to your crontab:
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

The schedule is defined in `routes/console.php`:
```php
Schedule::command('rank:process-advancements')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();
```

### Windows Task Scheduler

For development on Windows:
1. Open Task Scheduler
2. Create Basic Task
3. Trigger: Daily at startup
4. Action: Start a program
5. Program: `C:\path\to\php.exe`
6. Arguments: `C:\laragon\www\s2_gawis2\artisan schedule:run`
7. Repeat task every: 1 hour

## Verification

### Check If User Is Eligible
```bash
php artisan tinker
>>> $user = App\Models\User::find(123);
>>> $service = app(App\Services\RankAdvancementService::class);
>>> $progress = $service->getRankAdvancementProgress($user);
>>> $progress
```

### View User's Rank Progress
- Login as the user
- Go to Profile page
- View "My Rank" card
- Shows: Current rank, sponsors count, progress percentage

### Check Advancement History
```bash
php artisan tinker
>>> App\Models\RankAdvancement::where('user_id', 123)->get()
```

## Backward Compatibility

The system is fully backward compatible with existing users:

1. **Legacy Sponsorships**
   - Existing `sponsor_id` relationships are automatically counted
   - When advancement is triggered, they're backfilled into `direct_sponsors_tracker`

2. **Mixed Data Sources**
   - Counts BOTH tracked sponsorships AND legacy referrals
   - Ensures existing networks are preserved

3. **Migration Support**
   - Run `php process_pending_rank_advancements.php` once
   - Processes all eligible legacy users

## Test Users

Test users are created with pre-set sponsor counts:

- `test_starter_0`: 0/5 sponsors (0% progress)
- `test_starter_60`: 3/5 sponsors (60% progress)
- `test_starter_eligible`: 5/5 sponsors → **Auto-advanced to Newbie**
- `test_newbie`: 2/8 sponsors (25% progress)
- `test_bronze`: Top rank (no further advancement)

## Troubleshooting

### User shows 100% but not advanced

**Cause:** Advancement hasn't been processed yet

**Solution:**
```bash
php artisan rank:process-advancements --user-id=[USER_ID]
```

### Sponsor count seems wrong

**Cause:** Referrals might not have ranks assigned

**Solution:** Check that direct referrals have `current_rank` and `rank_package_id` set

### Advancement fails silently

**Cause:** Check application logs for errors

**Solution:**
```bash
tail -f storage/logs/laravel.log
```
Look for "Rank Advancement Failed" entries

## Logs

All advancement activities are logged:

- **Sponsorship tracked**: `Sponsorship Tracked`
- **Eligibility checked**: `Checking Rank Advancement Criteria`
- **Advancement succeeded**: `Rank Advanced Successfully`
- **Advancement failed**: `Rank Advancement Failed`
- **Order created**: `System-Funded Order Created`

## Performance Considerations

- **Hourly schedule**: Balances real-time needs with server load
- **Background processing**: Doesn't block user requests
- **Without overlapping**: Prevents multiple instances running simultaneously
- **Efficient queries**: Uses eager loading and indexed columns

## Future Enhancements

- [ ] Email notifications on rank advancement
- [ ] Admin dashboard for monitoring advancements
- [ ] Configurable advancement schedule (hourly, daily, etc.)
- [ ] Rank advancement history in user profile
- [ ] Bulk advancement processing with progress tracking

## Related Files

- Service: `app/Services/RankAdvancementService.php`
- Command: `app/Console/Commands/ProcessRankAdvancements.php`
- Observer: `app/Observers/UserObserver.php`
- Schedule: `routes/console.php`
- Migration script: `process_pending_rank_advancements.php`
- Models:
  - `app/Models/RankAdvancement.php`
  - `app/Models/DirectSponsorsTracker.php`
