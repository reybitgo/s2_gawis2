# Rank Repeat Purchase QA Test Guide

## Overview

This document provides a step-by-step QA testing guide for the **Repeat Purchase (PV-based) Rank Advancement** feature in GAWIS2. The dual-path rank advancement system allows users to advance through two independent paths:

- **Path A (Recruitment-based):** Direct sponsorship only
- **Path B (PV-based):** Direct sponsors + Personal Points Volume (PPV) + Group Points Volume (GPV)

This guide focuses exclusively on **Path B (PV-based)** advancement using idealized settings for quick feedback.

## Current System Configuration

### Products with Points
- **Collagen Beauty Drink** - ₱1,500.00 - **25 points**
- **Immune Booster Capsules** - ₱1,200.00 - **20 points**

### Default Rank Structure (Production Settings)

| Rank | Path A (Recruitment) | Path B (PV-based) |
|------|---------------------|-------------------|
| **Starter → Newbie** | 5 direct sponsors | 4 sponsors + 100 PPV + 1000 GPV |
| **Newbie → Bronze** | 8 direct sponsors | 6 sponsors + 300 PPV + 5000 GPV |
| **Bronze (Top Rank)** | - | - |

---

## QA Test Setup

### Step 1: Reset Database

```bash
# Navigate to project root
cd C:\laragon\www\s2_gawis2

# Reset database with seeded data
php artisan db:seed --class=DatabaseResetSeeder
```

**Expected Result:**
- Database is reset with fresh seeded data
- Admin account created: `admin@admin.com` / `admin`
- Test members with sponsor relationships

### Step 2: Apply Idealized Test Settings

For quick QA testing, we'll lower the thresholds to minimal values:

```sql
-- Update Starter Package to Newbie advancement (Test Mode)
UPDATE packages
SET
    required_direct_sponsors = 5,
    required_sponsors_ppv_gpv = 2,        -- Reduced from 4
    ppv_required = 10,                   -- Reduced from 100
    gpv_required = 20,                    -- Reduced from 1000
    rank_pv_enabled = 1
WHERE slug = 'starter-package';

-- Update Newbie Package to Bronze advancement (Test Mode)
UPDATE packages
SET
    required_direct_sponsors = 8,
    required_sponsors_ppv_gpv = 2,        -- Reduced from 6
    ppv_required = 10,                   -- Reduced from 300
    gpv_required = 20,                    -- Reduced from 5000
    rank_pv_enabled = 1
WHERE slug = 'newbie-package';

-- Update Bronze Package (Top Rank - no advancement)
UPDATE packages
SET
    required_direct_sponsors = 10,
    required_sponsors_ppv_gpv = 2,        -- Reduced from 8
    ppv_required = 10,                   -- Reduced from 500
    gpv_required = 20,                    -- Reduced from 15000
    rank_pv_enabled = 1
WHERE slug = 'bronze-package';
```

**Execute SQL via Tinker:**
```bash
php artisan tinker
```

Then paste and run the SQL commands above.

**Expected Result:**
- Path B thresholds reduced to: **2 sponsors + 10 PPV + 20 GPV**
- Path A remains unchanged (5+ sponsors)
- Easy to test with minimal purchases

### Step 3: Verify Test Settings

```bash
php artisan tinker
```

```php
// Check Starter Package configuration
$package = App\Models\Package::where('slug', 'starter-package')->first();
echo "Starter Package → Newbie:\n";
echo "Path A (Recruitment): {$package->required_direct_sponsors} sponsors\n";
echo "Path B (PV-based): {$package->required_sponsors_ppv_gpv} sponsors + {$package->ppv_required} PPV + {$package->gpv_required} GPV\n";
echo "PV-based enabled: " . ($package->rank_pv_enabled ? 'Yes' : 'No') . "\n";
```

**Expected Output:**
```
Starter Package → Newbie:
Path A (Recruitment): 5 sponsors
Path B (PV-based): 2 sponsors + 10 PPV + 20 GPV
PV-based enabled: Yes
```

---

## QA Test Scenarios

### Test Scenario 1: Basic PV-based Advancement (Starter → Newbie)

#### Prerequisites
- Fresh database with idealized settings applied
- Admin account available

#### Step 1: Create Test Account

1. Open browser and navigate to: `http://localhost:8000/register`
2. Fill in registration form:
   - **Username:** `testuser1`
   - **Email:** `testuser1@example.com`
   - **Password:** `password123`
   - **Sponsor Code:** Leave blank (no sponsor)
   - **Full Name:** Test User One
3. Submit registration

**Expected Result:**
- Account created successfully
- Redirected to dashboard
- User is **Unranked** (no package purchased yet)

#### Step 2: Activate Account by Purchasing Package

1. Navigate to: `http://localhost:8000/packages`
2. Select **Starter Package** (₱1,000.00)
3. Proceed to checkout
4. Complete payment using available method

**Expected Result:**
- Order created and confirmed
- User becomes **Starter** rank
- User network activated
- Wallet created

**Verification via Tinker:**
```php
$user = App\Models\User::where('username', 'testuser1')->first();
echo "Rank: {$user->current_rank}\n";
echo "Package: {$user->rankPackage->name}\n";
echo "Network Active: " . ($user->isNetworkActive() ? 'Yes' : 'No') . "\n";
echo "Current PPV: {$user->current_ppv}\n";
echo "Current GPV: {$user->current_gpv}\n";
echo "Same Rank Sponsors: " . $user->getSameRankSponsorsCount() . "\n";
```

#### Step 3: Create Two Direct Referrals (to meet sponsor requirement)

**Referral 1:**
1. Open Incognito/Private window
2. Navigate to: `http://localhost:8000/register`
3. Register with:
   - **Username:** `testuser2`
   - **Email:** `testuser2@example.com`
   - **Password:** `password123`
   - **Sponsor Code:** `testuser1`
   - **Full Name:** Test User Two
4. Submit and purchase **Starter Package**

**Referral 2:**
1. Another Incognito/Private window
2. Navigate to: `http://localhost:8000/register`
3. Register with:
   - **Username:** `testuser3`
   - **Email:** `testuser3@example.com`
   - **Password:** `password123`
   - **Sponsor Code:** `testuser1`
   - **Full Name:** Test User Three
4. Submit and purchase **Starter Package**

**Expected Result:**
- Both referrals successfully registered
- Both purchased Starter Package
- Both are now **Starter** rank (same rank as testuser1)
- testuser1 now has **2 same-rank sponsors**

**Verification via Tinker:**
```php
$user = App\Models\User::where('username', 'testuser1')->first();
echo "Same Rank Sponsors: " . $user->getSameRankSponsorsCount() . "\n";
echo "Required for Path B: 2\n";
```

#### Step 4: Purchase Products to Earn PPV/GPV

**For testuser1:**
1. Login as `testuser1`
2. Navigate to: `http://localhost:8000/products`
3. Add **1 x Collagen Beauty Drink** (25 points) to cart
4. Complete purchase
5. Add **1 x Immune Booster Capsules** (20 points) to cart
6. Complete purchase

**Expected Points Accumulation:**
- **PPV for testuser1:** 25 + 20 = **45 points** (exceeds 10 requirement)
- **GPV for testuser1:** 25 + 20 = **45 points** (exceeds 20 requirement)
- **Same Rank Sponsors:** 2 (meets requirement)

**Verification via Tinker:**
```php
$user = App\Models\User::where('username', 'testuser1')->first();
echo "Current PPV: {$user->current_ppv} (required: 10)\n";
echo "Current GPV: {$user->current_gpv} (required: 20)\n";
echo "Same Rank Sponsors: " . $user->getSameRankSponsorsCount() . " (required: 2)\n";

// Check if eligible for advancement
$service = app(App\Services\RankAdvancementService::class);
$progress = $service->getRankAdvancementProgress($user);
echo "\nPath A (Recruitment) Eligible: " . ($progress['path_a']['is_eligible'] ? 'Yes' : 'No') . "\n";
echo "Path B (PV-based) Eligible: " . ($progress['path_b']['is_eligible'] ? 'Yes' : 'No') . "\n";
```

**Expected Output:**
```
Current PPV: 45 (required: 10)
Current GPV: 45 (required: 20)
Same Rank Sponsors: 2 (required: 2)

Path A (Recruitment) Eligible: No
Path B (PV-based) Eligible: Yes
```

#### Step 5: Trigger Rank Advancement

The rank advancement should trigger automatically when order status changes to `confirmed`.

**Verify via Tinker (Manual Check):**
```php
$user = App\Models\User::where('username', 'testuser1')->first();
$service = app(App\Services\RankAdvancementService::class);
$advancementTriggered = $service->checkAndTriggerAdvancement($user);

if ($advancementTriggered) {
    echo "✅ RANK ADVANCEMENT TRIGGERED!\n";
    $user->refresh();
    echo "New Rank: {$user->current_rank}\n";
} else {
    echo "❌ Rank advancement not triggered\n";
    $progress = $service->getRankAdvancementProgress($user);
    print_r($progress);
}
```

**Expected Result:**
```
✅ RANK ADVANCEMENT TRIGGERED!
New Rank: Newbie
```

#### Step 6: Verify Advancement Records

**Check Rank Advancement Record:**
```php
$user = App\Models\User::where('username', 'testuser1')->first();
$advancement = App\Models\RankAdvancement::where('user_id', $user->id)->latest()->first();

echo "From Rank: {$advancement->from_rank}\n";
echo "To Rank: {$advancement->to_rank}\n";
echo "Advancement Type: {$advancement->advancement_type}\n";
echo "Sponsors Count: {$advancement->sponsors_count}\n";
echo "Notes: {$advancement->notes}\n";
```

**Expected Output:**
```
From Rank: Starter
To Rank: Newbie
Advancement Type: pv_based
Sponsors Count: 2
Notes: Rank advancement via PV-based path: 2 sponsors, 45 PPV, 45 GPV
```

**Check Points Reset:**
```php
$user = App\Models\User::where('username', 'testuser1')->first();
echo "Current PPV after advancement: {$user->current_ppv}\n";
echo "Current GPV after advancement: {$user->current_gpv}\n";

// Check points tracker
$resetRecord = App\Models\PointsTracker::where('user_id', $user->id)
    ->where('point_type', 'rank_advancement_reset')
    ->latest()
    ->first();
echo "\nPoints Reset Record:\n";
echo "PPV deducted: {$resetRecord->ppv}\n";
echo "GPV deducted: {$resetRecord->gpv}\n";
```

**Expected Output:**
```
Current PPV after advancement: 0
Current GPV after advancement: 0

Points Reset Record:
PPV deducted: -45
GPV deducted: -45
```

**Check Wallet (Rank Reward):**
```php
$user = App\Models\User::where('username', 'testuser1')->first();
$wallet = $user->wallet;

echo "MLM Balance: ₱" . number_format($wallet->mlm_balance, 2) . "\n";
echo "Withdrawable Balance: ₱" . number_format($wallet->withdrawable_balance, 2) . "\n";
```

**Expected Output:**
```
MLM Balance: ₱XXXX.XX (includes rank reward)
Withdrawable Balance: ₱XXXX.XX (includes rank reward)
```

---

### Test Scenario 2: Verify Points Credited to Uplines

#### Purpose
Verify that GPV is credited to ALL uplines recursively (no level limit).

#### Step 1: Check Upline GPV Credits

```php
// testuser1's GPV should also credit to uplines
$testuser1 = App\Models\User::where('username', 'testuser1')->first();

// Get all uplines
$uplines = [];
$current = $testuser1;
while ($current->sponsor) {
    $uplines[] = $current->sponsor;
    $current = $current->sponsor;
}

echo "Uplines who received GPV credits:\n";
foreach ($uplines as $upline) {
    $gpvCredits = App\Models\PointsTracker::where('user_id', $upline->id)
        ->where('awarded_to_user_id', $testuser1->id)
        ->where('point_type', 'product_purchase')
        ->sum('gpv');
    echo "{$upline->username} ({$upline->current_rank}): +{$gpvCredits} GPV\n";
}
```

**Expected Output:**
```
Uplines who received GPV credits:
<upline1> (<rank>): +45 GPV
<upline2> (<rank>): +45 GPV
...
```

---

### Test Scenario 3: Advancement via Recruitments (Path A - Control Test)

#### Purpose
Verify that Path A (recruitment-only) still works and takes priority when both paths are eligible.

#### Step 1: Create Additional Referrals

Create 3 more referrals under `testuser2` (currently Newbie rank):

**Referrals 3-5:**
1. Register `testuser4`, `testuser5`, `testuser6` under `testuser2`
2. Each purchases Starter Package

#### Step 2: Verify Path A Priority

```php
$user = App\Models\User::where('username', 'testuser2')->first();
$service = app(App\Services\RankAdvancementService::class);
$progress = $service->getRankAdvancementProgress($user);

echo "Same Rank Sponsors: {$progress['path_a']['sponsors_count']}\n";
echo "Required for Path A: {$progress['path_a']['required_sponsors']}\n";
echo "Path A Eligible: " . ($progress['path_a']['is_eligible'] ? 'Yes' : 'No') . "\n";
echo "Path B Eligible: " . ($progress['path_b']['is_eligible'] ? 'Yes' : 'No') . "\n";
```

**Expected Result:**
- When testuser2 reaches 8 sponsors (Path A requirement)
- Path A should take priority over Path B
- Advancement type should be `recruitment_based`

---

### Test Scenario 4: Test Points Reset on Advancement

#### Purpose
Verify that PPV and GPV reset to 0 on rank advancement.

#### Step 1: Track Points Before and After

```php
$user = App\Models\User::where('username', 'testuser1')->first();

// Record points before advancement
$ppvBefore = $user->current_ppv;
$gpvBefore = $user->current_gpv;
echo "Before Advancement - PPV: {$ppvBefore}, GPV: {$gpvBefore}\n";

// Trigger advancement manually (if not already triggered)
$service = app(App\Services\RankAdvancementService::class);
$service->checkAndTriggerAdvancement($user);

// Check points after
$user->refresh();
echo "After Advancement - PPV: {$user->current_ppv}, GPV: {$user->current_gpv}\n";

// Verify reset record
$resetRecord = App\Models\PointsTracker::where('user_id', $user->id)
    ->where('point_type', 'rank_advancement_reset')
    ->latest()
    ->first();
echo "\nReset Record Created: " . ($resetRecord ? 'Yes' : 'No') . "\n";
```

**Expected Output:**
```
Before Advancement - PPV: 45, GPV: 45
After Advancement - PPV: 0, GPV: 0

Reset Record Created: Yes
```

---

### Test Scenario 5: Test Multiple Advancements

#### Purpose
Verify that users can advance through multiple ranks using PV-based path.

#### Step 1: Advance from Newbie to Bronze

1. Login as `testuser1` (now Newbie rank)
2. Create 2 more referrals (total 4 same-rank sponsors)
3. Purchase products to earn 10 PPV and 20 GPV
4. Verify advancement to Bronze rank

#### Step 2: Verify Bronze is Top Rank

```php
$user = App\Models\User::where('username', 'testuser1')->first();
$package = $user->rankPackage;

echo "Current Rank: {$user->current_rank}\n";
echo "Can Advance: " . ($package->canAdvanceToNextRank() ? 'Yes' : 'No') . "\n";
echo "Next Package: " . ($package->getNextRankPackage() ? $package->getNextRankPackage()->name : 'None (Top Rank)') . "\n";
```

**Expected Output:**
```
Current Rank: Bronze
Can Advance: No
Next Package: None (Top Rank)
```

---

## Edge Case Testing

### Edge Case 1: Insufficient Sponsors

**Test:** User has enough PPV/GPV but not enough same-rank sponsors.

**Expected Result:**
- Rank advancement should NOT trigger
- Logs should show: "Rank Advancement: Path B - Not enough direct sponsors"

### Edge Case 2: Insufficient PPV

**Test:** User has enough sponsors and GPV but not enough PPV.

**Expected Result:**
- Rank advancement should NOT trigger
- Logs should show: "Rank Advancement: Path B - PPV requirement not met"

### Edge Case 3: Insufficient GPV

**Test:** User has enough sponsors and PPV but not enough GPV.

**Expected Result:**
- Rank advancement should NOT trigger
- Logs should show: "Rank Advancement: Path B - GPV requirement not met"

### Edge Case 4: PV-based Path Disabled

**Test:** Disable `rank_pv_enabled` on package.

```sql
UPDATE packages SET rank_pv_enabled = 0 WHERE slug = 'starter-package';
```

**Expected Result:**
- Path B should not be available
- Logs should show: "Rank Advancement: PV-based disabled, Path B not available"

---

## Verification Checklist

Use this checklist to verify the repeat purchase ranking is working correctly.

### Functional Requirements

- [ ] **Test Scenario 1:** PV-based advancement (Starter → Newbie) works
- [ ] **Test Scenario 2:** GPV credits to all uplines (recursive)
- [ ] **Test Scenario 3:** Path A takes priority when both paths eligible
- [ ] **Test Scenario 4:** Points reset to 0 on advancement
- [ ] **Test Scenario 5:** Multiple advancements work

### Edge Cases

- [ ] **Edge Case 1:** Insufficient sponsors blocks advancement
- [ ] **Edge Case 2:** Insufficient PPV blocks advancement
- [ ] **Edge Case 3:** Insufficient GPV blocks advancement
- [ ] **Edge Case 4:** Disabled PV path prevents Path B

### Data Integrity

- [ ] PPV and GPV track correctly in `points_tracker` table
- [ ] Reset records created on rank advancement
- [ ] `direct_sponsors_tracker` records sponsorships
- [ ] `rank_advancements` table logs all advancements
- [ ] Wallet credited with rank reward (if applicable)
- [ ] MLM commissions processed for advancement order

### UI/UX

- [ ] Dashboard displays correct PPV/GPV progress
- [ ] Dashboard shows advancement eligibility
- [ ] Notifications sent on rank advancement
- [ ] Activity logs created for advancement events

---

## Troubleshooting

### Issue: Rank advancement not triggering

**Check:**
1. Are all three conditions met? (sponsors + PPV + GPV)
2. Is `rank_pv_enabled = true` on the package?
3. Check logs: `storage/logs/laravel.log`

```bash
# Check rank advancement logs
tail -n 50 storage/logs/laravel.log | grep -i "rank advancement"
```

### Issue: Points not crediting

**Check:**
1. Is order status `confirmed`?
2. Do products have `points_awarded > 0`?
3. Check points tracking:

```php
$user = App\Models\User::where('username', 'testuser1')->first();
$points = App\Models\PointsTracker::where('user_id', $user->id)->latest()->get();
print_r($points->toArray());
```

### Issue: Same-rank sponsors not counting

**Check:**
1. Are referrals at the SAME rank as the sponsor?
2. Check `direct_sponsors_tracker` table:

```php
$user = App\Models\User::where('username', 'testuser1')->first();
$sponsors = App\Models\DirectSponsorsTracker::where('user_id', $user->id)->get();
print_r($sponsors->toArray());
```

---

## Reset to Production Settings

After QA testing, restore production settings:

```sql
-- Restore Starter Package
UPDATE packages
SET
    required_direct_sponsors = 5,
    required_sponsors_ppv_gpv = 4,
    ppv_required = 100,
    gpv_required = 1000,
    rank_pv_enabled = 1
WHERE slug = 'starter-package';

-- Restore Newbie Package
UPDATE packages
SET
    required_direct_sponsors = 8,
    required_sponsors_ppv_gpv = 6,
    ppv_required = 300,
    gpv_required = 5000,
    rank_pv_enabled = 1
WHERE slug = 'newbie-package';

-- Restore Bronze Package
UPDATE packages
SET
    required_direct_sponsors = 10,
    required_sponsors_ppv_gpv = 8,
    ppv_required = 500,
    gpv_required = 15000,
    rank_pv_enabled = 1
WHERE slug = 'bronze-package';
```

---

## Additional Testing Commands

### View All User Ranks and Progress

```php
$users = App\Models\User::whereNotNull('current_rank')->get();
$service = app(App\Services\RankAdvancementService::class);

foreach ($users as $user) {
    echo "\n=== {$user->username} ({$user->current_rank}) ===\n";
    $progress = $service->getRankAdvancementProgress($user);
    echo "PPV: {$user->current_ppv}, GPV: {$user->current_gpv}\n";
    echo "Same Rank Sponsors: {$progress['path_a']['sponsors_count']}\n";
    echo "Path A Eligible: " . ($progress['path_a']['is_eligible'] ? 'Yes' : 'No') . "\n";
    echo "Path B Eligible: " . ($progress['path_b']['is_eligible'] ? 'Yes' : 'No') . "\n";
}
```

### View All Rank Advancement Records

```php
$advancements = App\Models\RankAdvancement::with('user')->latest()->get();
foreach ($advancements as $adv) {
    echo "{$adv->user->username}: {$adv->from_rank} → {$adv->to_rank} ({$adv->advancement_type})\n";
}
```

### View Points Tracker for a User

```php
$user = App\Models\User::where('username', 'testuser1')->first();
$points = App\Models\PointsTracker::where('user_id', $user->id)
    ->latest()
    ->limit(10)
    ->get();

foreach ($points as $point) {
    $date = $point->earned_at->format('Y-m-d H:i:s');
    echo "{$date}: PPV {$point->ppv}, GPV {$point->gpv} [{$point->point_type}]\n";
}
```

---

## Summary

This QA test guide provides comprehensive testing scenarios for the **Repeat Purchase (PV-based) Rank Advancement** feature. The idealized settings (2 sponsors + 10 PPV + 20 GPV) allow for quick validation with minimal purchases.

**Key Testing Points:**
1. PV-based advancement works independently of recruitment-based path
2. PPV and GPV track and credit correctly
3. Points reset on rank advancement
4. GPV credits to all uplines recursively
5. Path A takes priority when both paths eligible

After successful testing, restore production settings before deploying to production.
