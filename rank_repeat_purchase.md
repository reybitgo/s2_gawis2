# Rank Advancement with Repeat Purchase Points - Implementation Plan

## Executive Summary

Transform the current ranking system from a pure recruitment-based model to a **dual-path hybrid model** with TWO ways to achieve rank advancement:

**Path A: Recruitment-Only (Original)**

- Meet `required_direct_sponsors` count
- Same-rank sponsors trigger rank upgrade
- No PPV/GPV requirements

**Path B: PV-Based (New)**

- Meet `required_sponsors_ppv_gpv` (configurable, default 4)
- Meet `ppv_required` threshold (Personal Points Volume from own purchases)
- Meet `gpv_required` threshold (Group Points Volume from entire downline tree)
- **Auto-Reset on Advancement:** PPV and GPV reset to 0 after each rank upgrade (fair progression)

**Key Concept:** Both paths are valid - whichever happens FIRST triggers rank advancement

**Example Scenario:**

- User wants to advance from Starter to Newbie
- **Path A:** Recruit 16 Starter-rank sponsors → Advancement
- **Path B:** Recruit 4 Starter-rank sponsors + Earn 100 PPV + Achieve 1000 GPV → Advancement
- If user recruits 4 sponsors and achieves PPV/GPV before reaching 16 sponsors → **Advances via Path B**
- If user reaches 16 sponsors before achieving PPV/GPV → **Advances via Path A**

**New Flexibility Feature:**

- `required_direct_sponsors` - Original field for recruitment-based advancement (or commission eligibility)
- `required_sponsors_ppv_gpv` - NEW field, specifically configurable for PV-based rank advancement
- `rank_pv_enabled` - Enable/disable PV-based advancement per rank
- This allows strategic adjustment: different requirements for each path

**Auto-Reset Feature:**

- PPV and GPV automatically reset to 0 after each rank advancement
- Ensures fair progression: each rank requires fresh PPV/GPV accumulation
- Example: After reaching Newbie (100 PPV), must earn full 300 PPV for 1 Star (not just 200)

This maintains meritocracy through direct recruitment while rewarding repeat purchases and team building.

### Dual Advancement Path System

**Two Ways to Rank Up:**

**Path A: Recruitment-Only**

```
Requirement: required_direct_sponsors (e.g., 16)
Advancement Trigger: User recruits 16 same-rank direct sponsors
No PPV/GPV requirements needed
```

**Path B: PV-Based**

```
Requirement 1: required_sponsors_ppv_gpv (e.g., 4)
Requirement 2: ppv_required (e.g., 100 PPV)
Requirement 3: gpv_required (e.g., 1000 GPV)
Advancement Trigger: User meets ALL THREE requirements
```

**Which Path Wins?**

- Whichever path requirements are met FIRST
- User can potentially qualify for both paths simultaneously
- System checks both conditions, advances rank on first met criteria

**Example: User Progressing Starter → Newbie**

**Scenario 1: PV-Based Path Wins**

```
Current Status:
- Direct sponsors: 4 (meets Path B requirement 1)
- PPV: 100 (meets Path B requirement 2)
- GPV: 1000 (meets Path B requirement 3)

Result: RANK ADVANCEMENT via Path B!
```

**Scenario 2: Recruitment Path Wins**

```
Current Status:
- Direct sponsors: 16 (meets Path A requirement)
- PPV: 20 (insufficient for Path B)
- GPV: 100 (insufficient for Path B)

Result: RANK ADVANCEMENT via Path A!
```

**Scenario 3: User Qualifies for Both**

```
Current Status:
- Direct sponsors: 16 (meets both paths)
- PPV: 100 (meets Path B)
- GPV: 1000 (meets Path B)

Result: RANK ADVANCEMENT via first path met (typically checked in order)
```

**Why Two Paths?**

1. **User Choice:** Users who excel at recruiting can advance via Path A
2. **User Choice:** Users who excel at team building can advance via Path B
3. **Fairness:** Both paths are legitimate ways to achieve the same goal
4. **Incentives:** Encourages both recruitment AND team/consumption

### Sponsor Count Field Clarification

**Why Two Separate Sponsor Count Fields?**

**Scenario A: Easy Rank Up, Strict Commissions**

```
required_direct_sponsors = 16      (Current: for commission eligibility)
required_sponsors_ppv_gpv = 4      (New: easier rank advancement)
```

- Users can rank up with 4 sponsors
- But need 16 sponsors for certain commissions/bonuses

**Scenario B: Balanced System**

```
required_direct_sponsors = 4
required_sponsors_ppv_gpv = 4
```

- Consistent requirements across all mechanics
- Simpler to understand

**Scenario C: Progressive Difficulty**

```
Rank   | required_sponsors_ppv_gpv
--------|-------------------------
Starter | 2
Newbie  | 3
1 Star  | 4
2 Star  | 5
3 Star  | 6
4 Star  | 7
5 Star  | 8
```

- Lower ranks easier to achieve
- Higher ranks require more effort

3. **Implementation Impact:**
    - Existing `required_direct_sponsors` remains unchanged (backward compatible)
    - New `required_sponsors_ppv_gpv` added and used by rank advancement logic
    - Admin can adjust `required_sponsors_ppv_gpv` anytime via `/admin/ranks/configure`

---

## Phase 1: Database Schema Foundation

### 1.1 Add Point Volume Columns to Packages Table

**Migration:** `add_ppv_gpv_to_packages_table.php`

Add new columns to `packages` table:

```php
$table->unsignedInteger('required_sponsors_ppv_gpv')->default(4)->after('required_direct_sponsors')->comment('Minimum same-rank sponsors required for PPV/GPV advancement');
$table->decimal('ppv_required', 10, 2)->default(0)->after('required_sponsors_ppv_gpv');
$table->decimal('gpv_required', 10, 2)->default(0)->after('ppv_required');
$table->boolean('rank_pv_enabled')->default(true)->after('gpv_required')->comment('Enable PV-based rank advancement for this rank');
```

**Purpose:**

- `required_sponsors_ppv_gpv`: Configurable sponsor count for PPV/GPV advancement (independent from `required_direct_sponsors`)
- `ppv_required`, `gpv_required`: Point thresholds for each rank
- `rank_pv_enabled`: Enable/disable PV-based rank advancement per rank

**Default Values:**

- Starter: Sponsors 4, PPV 0, GPV 0 (entry level)
- Newbie: Sponsors 4, PPV 100, GPV 1000
- 1 Star: Sponsors 4, PPV 300, GPV 5000
- 2 Star: Sponsors 4, PPV 500, GPV 15000
- 3 Star: Sponsors 4, PPV 800, GPV 40000
- 4 Star: Sponsors 4, PPV 1200, GPV 100000
- 5 Star: Sponsors 4, PPV 2000, GPV 250000

**Note:** `required_direct_sponsors` remains unchanged (may serve commission eligibility or other mechanics)

### 1.2 Add Point Volume Columns to Users Table

**Migration:** `add_ppv_gpv_to_users_table.php`

Add new columns to `users` table:

```php
$table->decimal('current_ppv', 10, 2)->default(0)->after('rank_updated_at')->comment('Current Personal Points Volume');
$table->decimal('current_gpv', 10, 2)->default(0)->after('current_ppv')->comment('Current Group Points Volume');
$table->timestamp('ppv_gpv_updated_at')->nullable()->after('current_gpv')->comment('Last time PPV/GPV was calculated');
```

**Purpose:** Track current point volumes for each user

### 1.3 Add Points Tracking Table

**Migration:** `create_points_tracker_table.php`

Create new `points_tracker` table:

```php
$table->id();
$table->unsignedBigInteger('user_id');
$table->unsignedBigInteger('order_item_id');
$table->decimal('ppv', 10, 2)->default(0);
$table->decimal('gpv', 10, 2)->default(0);
$table->timestamp('earned_at')->useCurrent();
$table->unsignedBigInteger('awarded_to_user_id')->nullable()->comment('User who received credit for this');
$table->string('point_type', 50)->default('product_purchase')->comment('product_purchase, repeat_purchase, etc.');
$table->string('rank_at_time', 100)->nullable()->comment('User rank when points earned');

$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
$table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
$table->foreign('awarded_to_user_id')->references('id')->on('users')->onDelete('set null');

$table->index(['user_id', 'earned_at']);
$table->index('point_type');
```

**Purpose:** Historical tracking of all point transactions for audit and debugging

---

## Phase 2: Order Processing Integration

### 2.1 Track Points on Order Confirmation

Modify `CheckoutController.php` or `OrderService.php`:

When order payment is confirmed:

1. Calculate points for each order item (if product has `points_awarded`)
2. Credit PPV to buyer
3. Credit GPV to buyer AND all upline members (recursive, indefinite levels)
4. Record transactions in `points_tracker` table

```php
public function processOrderPoints(Order $order): void
{
    foreach ($order->orderItems as $item) {
        if ($item->product && $item->product->points_awarded > 0) {
            $points = $item->product->points_awarded * $item->quantity;

            // Credit PPV to buyer
            $this->creditPPV($order->user, $points, $item);

            // Credit GPV to buyer + all uplines (indefinite levels)
            $this->creditGPVToUplines($order->user, $points, $item);
        }
    }
}
```

### 2.2 Implement Point Credit Methods

Create new service or add to existing:

```php
// Credit Personal Points Volume
public function creditPPV(User $user, float $points, OrderItem $item): void
{
    // Update user's current PPV
    $user->increment('current_ppv', $points);
    $user->update(['ppv_gpv_updated_at' => now()]);

    // Record in tracker
    $this->recordGPV($user, $points, $item);
}

// Record GPV to tracker (helper method)
private function recordGPV(User $user, float $points, OrderItem $item, ?int $awardedToUserId = null): void
{
    PointsTracker::create([
        'user_id' => $user->id,
        'order_item_id' => $item->id,
        'ppv' => $points,
        'gpv' => 0,
        'earned_at' => now(),
        'awarded_to_user_id' => $awardedToUserId,
        'point_type' => 'product_purchase',
        'rank_at_time' => $user->current_rank,
    ]);
}

// Credit Group Points Volume to user + all uplines (indefinite levels)
public function creditGPVToUplines(User $user, float $points, OrderItem $item): void
{
    // Credit to buyer (self)
    $user->increment('current_gpv', $points);
    $this->recordGPV($user, $points, $item);

    // Credit to all uplines (recursive, indefinite levels)
    $currentUpline = $user->sponsor;

    while ($currentUpline) { // Continue through all upline levels, no limit
        $currentUpline->increment('current_gpv', $points);
        $this->recordGPV($currentUpline, $points, $item, $user->id);
        $currentUpline = $currentUpline->sponsor;
    }
}
```

**Purpose:** Ensure fair advancement by resetting points to 0 after each rank achievement.

**Implementation:**

```php
// Reset PPV and GPV to start fresh for next rank
public function resetPPVGPVOnRankAdvancement(User $user): void
{
    $previousPPV = $user->current_ppv;
    $previousGPV = $user->current_gpv;
    $previousRank = $user->current_rank;

    // Reset PPV and GPV to 0
    $user->update([
        'current_ppv' => 0,
        'current_gpv' => 0,
        'ppv_gpv_updated_at' => now(),
    ]);

    // Record reset in tracker for audit
    PointsTracker::create([
        'user_id' => $user->id,
        'order_item_id' => null,
        'ppv' => -$previousPPV,  // Negative to track reset
        'gpv' => -$previousGPV,    // Negative to track reset
        'earned_at' => now(),
        'awarded_to_user_id' => $user->id,
        'point_type' => 'rank_advancement_reset',
        'rank_at_time' => $previousRank,
    ]);

    Log::info('PPV/GPV Reset on Rank Advancement', [
        'user_id' => $user->id,
        'previous_rank' => $previousRank,
        'previous_ppv' => $previousPPV,
        'previous_gpv' => $previousGPV,
        'reset_to' => [0, 0],
    ]);
}
```

**Key Implementation Points:**

1. **Atomic Update:** PPV and GPV reset in single database query
2. **Negative Tracker Entries:** Records previous values as negative for audit trail
3. **Timestamp Updates:** Updates `ppv_gpv_updated_at` to track when reset occurred
4. **Point Type:** Uses `'rank_advancement_reset'` to distinguish from purchases
5. **No Return Value:** Void method, resets happen synchronously with advancement

**Where It's Called:**

- In `advanceUserRank()` method, immediately after user rank is updated
- Called with `$this->resetPPVGPVOnRankAdvancement($user);`
- Inside same DB transaction as rank update (ensures atomicity)

**Example Flow:**

```
1. User qualifies for advancement
2. DB::beginTransaction()
3. User rank updated: Starter → Newbie
4. resetPPVGPVOnRankAdvancement($user) called
   - current_ppv set to 0
   - current_gpv set to 0
   - PointsTracker created with negative entries
5. RankAdvancement record created
6. Wallet credited with rank reward
7. MLM commissions processed
8. DB::commit()
9. User sees fresh start for next rank
```

**Example Flow:**

1. User is Starter with 100 PPV, 1000 GPV (met Newbie requirements)
2. Rank advances to Newbie
3. PPV/GPV reset to 0
4. User now needs full 300 PPV, 5000 GPV to reach 1 Star
5. NOT just 200 PPV, 4000 GPV (which would be unfair)

### 2.4 PPV/GPV Reset on Rank Advancement - Synchronous Trigger

**Reset Mechanism:**

- Trigger: Happens IMMEDIATELY when rank advancement occurs (Path A or Path B)
- Synchronized with rank upgrade event
- Automatic - no manual intervention needed

**Reset Triggers:**

1. **Path A Advancement:** User meets `required_direct_sponsors` count
    - Example: Recruits 16 Starter-rank direct sponsors
    - Result: Rank advances → PPV/GPV reset to 0

2. **Path B Advancement:** User meets PV requirements (sponsors + PPV + GPV)
    - Example: Has 4 sponsors + 100 PPV + 1000 GPV
    - Result: Rank advances → PPV/GPV reset to 0

**Key Point:**

- Both paths trigger SAME reset mechanism
- Reset happens IMMEDIATELY at rank advancement

**Key Point:**

- Both paths trigger same reset mechanism
- Reset happens SYNCHRONOUSLY with rank advancement
- User earns fresh start for next rank level

**Example Flow:**

```
1. User is Starter, has 4 sponsors + 100 PPV + 1000 GPV
2. Path B requirements met → ADVANCEMENT TRIGGERED
3. User rank advances to Newbie (SYNCHRONOUS)
4. PPV resets to 0, GPV resets to 0 (IMMEDIATE)
5. User starts fresh for 1 Star requirements
```

**Why Synchronous Reset?**

1. **Fairness:** All users start fresh at new rank, regardless of when they advance
2. **Simplicity:** Reset tied to advancement event, no separate scheduling
3. **Consistency:** Reset always happens with rank upgrade
4. **Predictability:** Users know exactly when reset occurs (at advancement)

---

## Phase 3: Rank Advancement Logic Update

### 3.1 Update Required Sponsors for PPV/GPV Advancement

Modify `packages` table values:

- Set `required_sponsors_ppv_gpv` to 4 for all ranks (default for PPV/GPV advancement)
- `required_direct_sponsors` is NOT modified (stays at current value of 2, used for commission eligibility or other mechanics)

**SQL Update:**

```sql
UPDATE packages SET required_sponsors_ppv_gpv = 4 WHERE is_rankable = 1;
```

**Strategic Flexibility Example:**
You can now have different requirements:

- `required_direct_sponsors = 16` (for commission eligibility or bonuses)
- `required_sponsors_ppv_gpv = 4` (for PPV/GPV rank advancement)

### 3.2 Update RankAdvancementService - Dual Path Check

Modify `checkAndTriggerAdvancement()` method in `RankAdvancementService.php`:

**Dual-Path Logic:**

```php
public function checkAndTriggerAdvancement(User $user): bool
{
    $currentPackage = $user->rankPackage;

    if (!$currentPackage || !$currentPackage->canAdvanceToNextRank()) {
        return false;
    }

    $directSponsorsCount = $user->getSameRankSponsorsCount();
    $currentPPV = $user->current_ppv;
    $currentGPV = $user->current_gpv;

    // === PATH A: RECRUITMENT-ONLY CHECK ===
    $requiredDirectsRecruit = $currentPackage->required_direct_sponsors;
    $pathAEligible = $directSponsorsCount >= $requiredDirectsRecruit;

    if ($pathAEligible) {
        Log::info('Rank Advancement: Path A (Recruitment) eligible', [
            'user_id' => $user->id,
            'directs' => $directSponsorsCount,
            'required' => $requiredDirectsRecruit,
        ]);

        // Implementation: resetPPVGPVOnRankAdvancement() will be called
        // in advanceUserRank() method (see Phase 3.4)
        // No PPV/GPV requirements for Path A
        return $this->advanceUserRank($user, $directSponsorsCount, 'recruitment');
    }

    // === PATH B: PV-BASED CHECK (if enabled) ===
    if (!$currentPackage->rank_pv_enabled) {
        Log::info('Rank Advancement: PV-based disabled, Path B not available', [
            'user_id' => $user->id,
            'rank' => $currentPackage->rank_name,
        ]);
        return false;
    }

    $requiredDirectsPV = $currentPackage->required_sponsors_ppv_gpv;
    $requiredPPV = $currentPackage->ppv_required;
    $requiredGPV = $currentPackage->gpv_required;

    // Check 1: Minimum Direct Sponsors for PV-based (configurable)
    if ($directSponsorsCount < $requiredDirectsPV) {
        Log::info('Rank Advancement: Path B - Not enough direct sponsors', [
            'user_id' => $user->id,
            'directs' => $directSponsorsCount,
            'required_ppv_gpv' => $requiredDirectsPV,
        ]);
        return false;
    }

    // Check 2: Personal Points Volume (PPV)
    if ($currentPPV < $requiredPPV) {
        Log::info('Rank Advancement: Path B - PPV requirement not met', [
            'user_id' => $user->id,
            'current_ppv' => $currentPPV,
            'required_ppv' => $requiredPPV,
        ]);
        return false;
    }

    // Check 3: Group Points Volume (GPV)
    if ($currentGPV < $requiredGPV) {
        Log::info('Rank Advancement: Path B - GPV requirement not met', [
            'user_id' => $user->id,
            'current_gpv' => $currentGPV,
            'required_gpv' => $requiredGPV,
        ]);
        return false;
    }

    // Path B requirements met - advance rank
    Log::info('Rank Advancement: Path B (PV-based) eligible', [
        'user_id' => $user->id,
        'directs' => $directSponsorsCount,
        'ppv' => $currentPPV,
        'gpv' => $currentGPV,
    ]);
        return $this->advanceUserRank($user, $directSponsorsCount, 'pv_based');
}
```

**See Implementation:** `resetPPVGPVOnRankAdvancement()` method in Phase 2.3

**Key Points:**

1. **Path A Checked First:** Recruitment-only (original system)
2. **Path B Checked Second:** PV-based (new system, if enabled)
3. **Whichever Meets First Wins:** No priority - first to succeed triggers advancement
4. **Can Both Fail:** User may not meet either path requirements
5. **Can Both Succeed:** User may qualify for both (first check wins)

### 3.4 Update advanceUserRank Method with Synchronous Path Tracking & Reset

Modify `advanceUserRank()` method signature to track which path triggered advancement:

**New Signature:**

```php
public function advanceUserRank(
    User $user,
    int $sponsorsCount,
    string $advancementType = 'recruitment' // NEW: 'recruitment' or 'pv_based'
): bool
```

**Updated Implementation:**

```php
public function advanceUserRank(
    User $user,
    int $sponsorsCount,
    string $advancementType = 'recruitment'
): bool {
    DB::beginTransaction();
    try {
        $currentPackage = $user->rankPackage;
        $nextPackage = $currentPackage->getNextRankPackage();

        if (!$nextPackage) {
            Log::error('Next rank package not found', [
                'user_id' => $user->id,
                'current_package_id' => $currentPackage->id,
            ]);
            DB::rollBack();
            return false;
        }

        // Create system-funded order
        $order = $this->createSystemFundedOrder($user, $nextPackage);

        if (!$order) {
            Log::error('Failed to create system-funded order', [
                'user_id' => $user->id,
                'package_id' => $nextPackage->id,
            ]);
            DB::rollBack();
            return false;
        }

        // Store previous rank before updating
        $previousRank = $user->current_rank;

        // Update user rank
        $user->update([
            'current_rank' => $nextPackage->rank_name,
            'rank_package_id' => $nextPackage->id,
            'rank_updated_at' => now(),
        ]);

        // ACTIVATE NETWORK STATUS IF NOT ALREADY ACTIVE
        $user->activateNetwork();

         // RESET PPV AND GPV TO 0 FOR FRESH START TO NEXT RANK
        // Implementation: resetPPVGPVOnRankAdvancement() in Phase 2.3
        $this->resetPPVGPVOnRankAdvancement($user);

        // DETERMINE ADVANCEMENT TYPE FOR RECORDING
        if ($advancementType === 'recruitment') {
            $advancementTypeDb = 'recruitment_based';
            $notes = "Rank advancement via recruitment path: {$sponsorsCount} same-rank sponsors";
        } else {
            $advancementTypeDb = 'pv_based';
            $notes = "Rank advancement via PV-based path: {$sponsorsCount} sponsors, {$user->current_ppv} PPV, {$user->current_gpv} GPV";
        }

        // Record advancement
        RankAdvancement::create([
            'user_id' => $user->id,
            'from_rank' => $currentPackage->rank_name,
            'to_rank' => $nextPackage->rank_name,
            'from_package_id' => $currentPackage->id,
            'to_package_id' => $nextPackage->id,
            'advancement_type' => $advancementTypeDb,
            'required_sponsors' => $advancementType === 'recruitment'
                ? $currentPackage->required_direct_sponsors
                : $currentPackage->required_sponsors_ppv_gpv,
            'sponsors_count' => $sponsorsCount,
            'system_paid_amount' => $nextPackage->rank_reward,
            'order_id' => $order->id,
            'notes' => $notes,
        ]);

        // Continue with wallet credit, MLM commissions, notifications, etc.
        // ... (existing code continues)
```

**Key Points:**

1. **Path Tracking:** New parameter `$advancementType` to record which path was used
2. **Database Storage:** Different `advancement_type` values:
    - `recruitment_based` - Path A
    - `pv_based` - Path B
3. **Conditional Logic:** Different notes and sponsor requirements based on path
4. **Backward Compatible:** Default parameter value ensures existing code still works
5. **SYNCHRONOUS PPV/GPV RESET:** Reset happens IMMEDIATELY on rank advancement
6. **Both Paths Trigger Same Reset:** Whether Path A or Path B causes advancement, PPV/GPV resets
7. **Implementation:** See `resetPPVGPVOnRankAdvancement()` in Phase 2.3

### 3.5 Synchronous PPV/GPV Reset - How It Works

**Reset Mechanism:**

```php
// Called IMMEDIATELY after rank update in advanceUserRank()
$this->resetPPVGPVOnRankAdvancement($user);
```

**Trigger Conditions:**

```
Condition: Rank advancement occurs
Trigger Methods:
1. Path A: User meets required_direct_sponsors count
2. Path B: User meets required_sponsors_ppv_gpv + ppv_required + gpv_required

Result: BOTH TRIGGER SYNCHRONOUS RESET
```

**Reset Flow:**

```
1. User advances from Starter to Newbie
   ↓ (IMMEDIATE)
2. resetPPVGPVOnRankAdvancement() called
   ↓ (SYNCHRONOUS)
3. User's current_ppv set to 0
4. User's current_gpv set to 0
5. Negative entries recorded in points_tracker
   ↓
6. User starts fresh for 1 Star requirements
```

**Key Characteristics:**

1. **Immediate:** Reset happens at same time as rank advancement
2. **No Delay:** Not scheduled for later execution
3. **Event-Driven:** Tied to advancement event, not calendar date
4. **Consistent:** Every rank advancement has same reset behavior
5. **Path-Independent:** Works for both Path A and Path B

**Why Synchronous is Better:**

1. **Fairness:** All users start fresh at new rank immediately
2. **Clarity:** Reset occurs at predictable moment (advancement)
3. **Consistency:** No partial accumulation between ranks
4. **Simplicity:** One-time event tied to advancement
5. **User Experience:** Clear progression: "I reached Newbie! PPV/GPV reset to start fresh for 1 Star"

### 4.3 Update Dashboard Rank Progress

Modify `resources/views/dashboard.blade.php`:

Show three progress bars instead of one:

```php
<div class="card-body">
    <h5>{{ $progress['current_rank'] }} Rank Progress</h5>

    <div class="mb-3">
        <label>Direct Sponsors (PPV/GPV): {{ $progress['directs_ppv_gpv']['current'] }} / {{ $progress['directs_ppv_gpv']['required'] }}</label>
        <div class="progress">
            <div class="progress-bar {{ $progress['directs_ppv_gpv']['met'] ? 'bg-success' : 'bg-warning' }}"
                 style="width: {{ $progress['directs_ppv_gpv']['progress'] }}%">
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label>PPV: {{ $progress['ppv']['current'] }} / {{ $progress['ppv']['required'] }}</label>
        <div class="progress">
            <div class="progress-bar {{ $progress['ppv']['met'] ? 'bg-success' : 'bg-warning' }}"
                 style="width: {{ $progress['ppv']['progress'] }}%">
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label>GPV: {{ $progress['gpv']['current'] }} / {{ $progress['gpv']['required'] }}</label>
        <div class="progress">
            <div class="progress-bar {{ $progress['gpv']['met'] ? 'bg-success' : 'bg-warning' }}"
                 style="width: {{ $progress['gpv']['progress'] }}%">
            </div>
        </div>
    </div>

    @if($progress['is_eligible'])
        <div class="alert alert-success">
            ✓ You meet all requirements for {{ $progress['next_rank'] }} rank!
        </div>
    @endif
</div>
```

---

## Phase 3: Models and Relationships

### 5.1 Create PointsTracker Model

**File:** `app/Models/PointsTracker.php`

```php
class PointsTracker extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'order_item_id',
        'ppv',
        'gpv',
        'earned_at',
        'awarded_to_user_id',
        'point_type',
        'rank_at_time',
    ];

    protected $casts = [
        'ppv' => 'decimal:2',
        'gpv' => 'decimal:2',
        'earned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function awardedToUser()
    {
        return $this->belongsTo(User::class, 'awarded_to_user_id');
    }

    public function scopePPV($query)
    {
        return $query->where('ppv', '>', 0);
    }

    public function scopeGPV($query)
    {
        return $query->where('gpv', '>', 0);
    }
}
```

### 5.2 Update Package Model

Add new field to fillable and casts:

```php
protected $fillable = [
    // ... existing fields ...
    'required_sponsors_ppv_gpv',  // NEW: Sponsors required for PPV/GPV advancement
    'ppv_required',
    'gpv_required',
    'rank_pv_enabled',
];

protected $casts = [
    'ppv_required' => 'decimal:2',
    'gpv_required' => 'decimal:2',
    'rank_pv_enabled' => 'boolean',
];
```

### 5.3 Update User Model

Add new relationships:

```php
public function pointsTracker()
{
    return $this->hasMany(PointsTracker::class)->orderBy('earned_at', 'desc');
}

public function getCurrentPPVAttribute(): float
{
    return $this->attributes['current_ppv'] ?? 0;
}

public function getCurrentGPVAttribute(): float
{
    return $this->attributes['current_gpv'] ?? 0;
}
```

---

## Phase 5: Testing Strategy

### 6.1 Unit Tests

**Test:** `tests/Unit/RankAdvancementTest.php`

```php
public function test_rank_advancement_with_points()
{
    // Setup: User with 4 directs, PPV 100, GPV 1000
    $user = User::factory()->create(['current_rank' => 'Starter']);
    $user->update(['current_ppv' => 100, 'current_gpv' => 1000]);

    // Create 4 direct sponsors
    User::factory()->count(4)->create([
        'sponsor_id' => $user->id,
        'current_rank' => 'Starter',
    ]);

    // Act: Check advancement
    $service = new RankAdvancementService();
    $advanced = $service->checkAndTriggerAdvancement($user);

    // Assert: User advanced to Newbie
    $user->refresh();
    $this->assertTrue($advanced);
    $this->assertEquals('Newbie', $user->current_rank);
}
```

### 6.2 Integration Tests

**Test:** `tests/Feature/PointsOnOrderTest.php`

```php
public function test_product_purchase_creates_points()
{
    // Setup: Product with 50 points
    $product = Product::factory()->create(['points' => 50]);
    $user = User::factory()->create();
    $sponsor = User::factory()->create();

    $user->sponsor_id = $sponsor->id;
    $user->save();

    // Act: Purchase product
    $order = Order::factory()->create(['user_id' => $user->id, 'payment_status' => 'paid']);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
    ]);

    // Process points
    (new PointsService())->processOrderPoints($order);

    // Assert: PPV and GPV credited
    $user->refresh();
    $sponsor->refresh();

    $this->assertEquals(50, $user->current_ppv);
    $this->assertEquals(50, $user->current_gpv);
    $this->assertEquals(50, $sponsor->current_gpv);

    $this->assertDatabaseHas('points_tracker', [
        'user_id' => $user->id,
        'ppv' => 50,
    ]);
}
```

### 6.3 Manual Testing Scenarios

**Scenario 1: Path B (PV-Based) Advancement - Basic**

- User has 4 Starter directs (meets `required_sponsors_ppv_gpv`)
- User purchases 100 PPV worth of products (meets `ppv_required`)
- Downlines purchase 900 PPV worth of products (total GPV = 1000, meets `gpv_required`)
- **Result:** User advances to Newbie via Path B
- **Verification:** `rank_advancements.advancement_type` = 'pv_based'

**Scenario 2: Path A (Recruitment-Only) Advancement - Basic**

- User has 16 Starter directs (meets `required_direct_sponsors`)
- User has PPV 20, GPV 100 (insufficient for Path B)
- **Result:** User advances to Newbie via Path A
- **Verification:** `rank_advancements.advancement_type` = 'recruitment_based'

**Scenario 3: Path A Insufficient, Path B Insufficient**

- User has 3 Starter directs (insufficient for both paths)
- User has PPV 100, GPV 1000
- **Result:** User should NOT advance
- **Reason:** Neither path requirements met

**Scenario 4: Path A Meets First, Path B Later**

- User has 10 Starter directs (Path A requires 16, not met)
- User has PPV 100, GPV 1000 (Path B met)
- **Result:** User advances to Newbie via Path B
- **Why:** Path B checked after Path A fails

**Scenario 5: Path B Meets First, Path A Later**

- User has 16 Starter directs (Path A met)
- User has PPV 50, GPV 500 (Path B not met)
- **Result:** User advances to Newbie via Path A
- **Why:** Path A checked first and succeeds

**Scenario 6: Both Paths Met Simultaneously**

- User has 20 Starter directs (exceeds Path A's 16)
- User has PPV 100, GPV 1000 (Path B met)
- **Result:** User advances to Newbie via Path A
- **Why:** Path A checked first and succeeds

**Scenario 7: Path B with Auto-Reset Verification**

- User is Starter with 100 PPV, 1000 GPV (Path B met)
- Rank advances to Newbie, PPV/GPV reset to 0
- User now needs full 300 PPV, 5000 GPV for 1 Star
- User earns 200 PPV, 4000 GPV more
- **Result:** User should NOT advance to 1 Star (needs full 300 PPV, 5000 GPV)
- **Purpose:** Verify auto-reset prevents unfair advantage

**Scenario 8: Multi-Level GPV for Path B**

- User has 4 Starter directs
- Directs have their own downlines
- User earns 50 PPV personally
- Downlines (all levels) earn 950 PPV
- **Result:** User advances to Newbie via Path B (total GPV = 1000)
- **Purpose:** Verify GPV includes ALL indirect purchases

**Scenario 6: PPV/GPV Reset on Rank Advancement**

- User is Starter with 100 PPV, 1000 GPV (met Newbie requirements)
- User advances to Newbie
- PPV and GPV reset to 0
- User now needs full 300 PPV, 5000 GPV for 1 Star
- NOT just 200 PPV, 4000 GPV (unfair advantage if not reset)
- Reset ensures fresh start for each rank level

---

## Phase 6: Data Migration

### 7.1 Update Existing Package Records

Run database migration to set default PPV/GPV values:

**Migration:** `update_packages_with_default_points.php`

```php
public function up(): void
{
    DB::statement("UPDATE packages SET ppv_required = 0 WHERE rank_order = 1");
    DB::statement("UPDATE packages SET ppv_required = 100, gpv_required = 1000 WHERE rank_order = 2");
    DB::statement("UPDATE packages SET ppv_required = 300, gpv_required = 5000 WHERE rank_order = 3");
    DB::statement("UPDATE packages SET ppv_required = 500, gpv_required = 15000 WHERE rank_order = 4");
    DB::statement("UPDATE packages SET ppv_required = 800, gpv_required = 40000 WHERE rank_order = 5");
    DB::statement("UPDATE packages SET ppv_required = 1200, gpv_required = 100000 WHERE rank_order = 6");
    DB::statement("UPDATE packages SET ppv_required = 2000, gpv_required = 250000 WHERE rank_order = 7");
}
```

### 7.2 Update Required Sponsors for PPV/GPV

**Migration:** `update_required_sponsors_ppv_gpv.php`

```php
public function up(): void
{
    // Set required sponsors for PPV/GPV advancement (configurable)
    DB::statement("UPDATE packages SET required_sponsors_ppv_gpv = 4 WHERE is_rankable = 1");
}
```

**Note:** `required_direct_sponsors` remains unchanged (used for commission eligibility or other purposes)

### 7.3 Backward Compatibility

- Users with existing 16 directs will automatically qualify (they have more than 4)
- Existing rank advancements remain valid
- Points system is additive, not disruptive

---

## Phase 7: Documentation and Communication

### 8.1 Update AGENTS.md

Add new section:

```markdown
## Points-Based Rank Advancement

- PPV (Personal Points Volume): Points from user's own product purchases
- GPV (Group Points Volume): PPV of user + ALL indirect downlines
- Dual Sponsor Requirements:
    - `required_direct_sponsors`: For commission eligibility or bonuses (unchanged)
    - `required_sponsors_ppv_gpv`: For PPV/GPV rank advancement (configurable, default 4)
- Requirements per rank: required_sponsors_ppv_gpv directs + PPV threshold + GPV threshold
```

### 8.2 Create User Guide

Document for end users:

- How to earn points (product purchases)
- How to track progress (dashboard)
- Rank advancement requirements
- Benefits of higher ranks

### 8.3 Admin Training

Train administrators on:

- Configuring PPV/GPV thresholds per rank
- Monitoring points transactions
- Debugging rank advancement issues
- Manual rank adjustments

---

## Phase 8: Performance Optimization

### 9.1 GPV Calculation Caching

Since GPV requires recursive upline calculation (indefinite levels):

**Optimization Strategy:**

- Cache GPV values in `users.current_gpv`
- Increment/decrement on each purchase (don't recalculate from scratch)
- Add scheduled job to recalculate if needed

**Why Caching with Indefinite Levels?**

```
Without Caching (Every Check Recalculates):
- User A purchases product: 100 GPV to 50 uplines
- Each upline requires: 1 query × 50 levels = 50 queries
- Total: 50 queries per purchase

With Caching (Increment Only):
- User A purchases product: +100 GPV
- Each upline: 1 UPDATE query = 50 queries
- Total: 50 queries per purchase (massive performance gain)
```

**Benefits of Indefinite GPV Levels:**

1. **Deep Teams Rewarded:** Users with 20+ level downlines still get credit
2. **No Artificial Limits:** Natural network growth not constrained by 10-level ceiling
3. **Fair Compensation:** All uplines contribute equally, regardless of depth
4. **Team Building Incentive:** Building deep downlines increases everyone's earning potential

**Example: 50-Level Downline**

```
User structure:
Level 0: You (purchases 50 PPV)
Level 1: Your sponsor (+50 GPV)
Level 2: Level 1's sponsor (+50 GPV)
...
Level 49: Deep downline (+50 GPV)

Result: 50 users receive GPV credit
```

**Performance Considerations:**

- Recursive loops can slow with very deep teams
- Use caching (increment vs. recalculate) to maintain performance
- Consider depth limit only if performance becomes an issue
- Indexes on `users.sponsor_id` for fast upline traversal

### 9.2 Database Indexes

Ensure indexes exist:

```php
$table->index(['user_id', 'earned_at']); // Already in Phase 1.3
$table->index('point_type');           // Already in Phase 1.3
```

### 9.3 Bulk Updates

When processing orders with multiple items:

- Use bulk inserts for points_tracker
- Use single queries for PPV/GPV updates

---

## Phase 9: Edge Cases and Validation

### 10.1 Zero Points Products

- Products with `points = 0` don't affect PPV/GPV
- MLM packages (`is_mlm_package = 1`) typically have points = 0

### 10.2 Refunds and Cancellations

When an order is refunded:

- Deduct PPV from buyer
- Deduct GPV from buyer + all uplines
- Create negative entries in `points_tracker`

### 10.3 Rank Downgrade (Optional Future)

Consider if users should downgrade when ranks drop:

- Current design: PPV/GPV accumulate until next rank advancement, then reset
- Decision needed: Keep highest rank achieved vs. maintain rank requirements

### 10.4 Same-Rank Sponsor Requirement

**Important Clarification:**
Directs count is still based on **same-rank** sponsors, specifically for `required_sponsors_ppv_gpv`:

- Starter needs `required_sponsors_ppv_gpv` (default 4) Starter-rank directs
- Newbie needs `required_sponsors_ppv_gpv` (default 4) Newbie-rank directs
- Not just X directs of any rank

**Strategic Flexibility:**
You can configure `required_sponsors_ppv_gpv` differently per rank:

- Example: Starter requires 2, Newbie requires 4, 1 Star requires 5
- This allows progressive difficulty while maintaining flexibility

### 10.5 PPV/GPV Synchronous Reset - Rank Advancement Triggered

**How PPV/GPV Reset Works:**

**Synchronous Reset Mechanism:**

- Reset happens IMMEDIATELY when rank advancement occurs
- Triggered by advancement event, not by schedule
- Called SYNCHRONOUSLY within rank advancement transaction
- Both Path A and Path B trigger same reset logic

**Reset Trigger Flow:**

```
User advances to Newbie (at 14:30:00)
    ↓
Rank update completes
    ↓ (IMMEDIATE)
resetPPVGPVOnRankAdvancement() called
    ↓ (SYNCHRONOUS)
current_ppv set to 0
current_gpv set to 0
Negative entries in points_tracker
    ↓
Transaction committed
    ↓
Rank advancement successful
User starts fresh for next rank at 14:30:00
```

**What "Synchronous" Means:**

1. **Immediate:** Reset occurs instantly with rank advancement
2. **Atomic:** Both rank update and reset happen in same transaction
3. **Predictable:** Reset always happens at exact same time as rank change
4. **Consistent:** Every rank advancement has identical reset behavior

**Why Synchronous is Important:**

```
User Scenario:
1. At 14:00: Has 900 PPV, 8500 GPV
2. At 14:30: Completes Path B requirements
3. At 14:30: RANK ADVANCEMENT TRIGGERED
4. At 14:30: RANK UPDATE + PPV/GPV RESET (synchronous)
5. At 14:30: Starts at 0 PPV, 0 GPV for next rank
6. At 14:31: Can immediately start earning toward next rank
```

**Implementation Notes:**

**Implementation Notes:**

```php
// In advanceUserRank() - called after rank update
public function advanceUserRank(...): bool {
    DB::beginTransaction();
    try {
        // ... rank update logic ...

        // SYNCHRONOUS RESET - Happens immediately
        $this->resetPPVGPVOnRankAdvancement($user);

        // ... wallet credit, MLM commissions, notifications ...

        DB::commit();
        return true;
    }
}
```

**Key Benefit:**

- User immediately sees fresh start at new rank
- No waiting period for reset to occur
- Clear, predictable behavior for all users
- Same experience regardless of advancement path (Path A or Path B)

---

## Implementation Checklist

- [ ] Phase 1: Database schema migrations
    - [ ] 2.1: Use existing `points_awarded` field in products table
    - [ ] 2.2: Update ProductSeeder with `points_awarded` values
    - [ ] 2.3: Admin product edit page enhancement (display `points_awarded`)
- [ ] 2.1: Implement point credit methods
- [ ] 2.2: Implement PPV/GPV reset on rank advancement
- [ ] 2.3: Points expiration logic (optional)
- [ ] Phase 3: Rank advancement logic update
    - [ ] 4.1: Update required sponsors for PPV/GPV
    - [ ] 4.2: Update checkAndTriggerAdvancement method
    - [ ] 4.3: Update advanceUserRank method with reset call
- [ ] Phase 5: Admin interface enhancement
- [ ] Phase 6: Models and relationships
- [ ] Phase 7: Testing (unit + integration + manual)
- [ ] Phase 8: Data migration
- [ ] Phase 9: Documentation
- [ ] Phase 8: Performance optimization
- [ ] Phase 9: Edge case validation

---

## Summary of Changes

### From Current System:

- **Required Directs:** 2 per rank level (16 total) → **Configurable per rank**
- **Advancement Criteria:** Same-rank sponsor count only → **Dual-Path System**
- **Product Purchases:** Commission-only → **Commission + Points**

### To New System:

- **Dual-Path Advancement:** TWO independent ways to achieve rank:
    - **Path A (Recruitment-Only):** Meet `required_direct_sponsors` count
    - **Path B (PV-Based):** Meet `required_sponsors_ppv_gpv` + `ppv_required` + `gpv_required`
- **Whichever Happens First Triggers Advancement:** No priority, first to succeed wins
- **New Field:** `required_sponsors_ppv_gpv` - Separate configurable sponsor count for PV-based advancement
- **New Field:** `rank_pv_enabled` - Enable/disable PV-based advancement per rank
- **Two Sponsor Counters:**
    - `required_direct_sponsors` - For recruitment-only advancement (or commission eligibility)
    - `required_sponsors_ppv_gpv` - For PV-based rank advancement (new, default 4)
- **Path B Requirements:**
    - **Minimum Recruitment:** X direct sponsors for PV/GPV (configurable per rank)
    - **Personal Points (PPV):** Must purchase products worth X points
    - **Group Points (GPV):** User + all indirects must collectively achieve Y points
    - **All Three Required:** Must meet sponsor + PPV + GPV thresholds to advance via Path B
- **Auto-Reset on Advancement:** PPV and GPV reset to 0 when rank upgrades (fair progression)
- **Path Tracking:** `rank_advancements.advancement_type` records 'recruitment_based' or 'pv_based'

### Strategic Flexibility Examples:

**Option A: Aggressive Growth (Default)**

- `required_direct_sponsors = 2` (current, for commissions)
- `required_sponsors_ppv_gpv = 4` (new, for rank advancement)
- Easier to rank up, encourages team building

**Option B: Balanced Approach**

- `required_direct_sponsors = 4`
- `required_sponsors_ppv_gpv = 4`
- Equal requirements across all systems

**Option C: Progressive Difficulty**

- `required_direct_sponsors = 16` (keep existing)
- `required_sponsors_ppv_gpv = 4` (new, easier for rank advancement)
- Commission eligibility hard, rank advancement easy

**Option D: Progressive per Rank**

```
Rank   | required_directs_ppv_gpv
--------|-------------------------
Starter | 2
Newbie  | 3
1 Star  | 4
2 Star  | 5
3 Star  | 6
4 Star  | 7
5 Star  | 8 (top)
```

### Benefits:

1. **Dual-Path Flexibility:** Users can choose recruitment-heavy or team-building approach
2. **Lower Recruitment Barrier:** Path B reduces requirement to 4 vs Path A's 16
3. **Strategic Control:** Admin can adjust both path requirements independently via admin panel
4. **Encourages Consumption:** Repeat purchases earn PPV
5. **Rewards Team Building:** GPV incentivizes helping downlines succeed
6. **Maintains Meritocracy:** Both paths require active effort (sponsors or consumption)
7. **Flexible Configuration:** PPV/GPV thresholds and sponsor counts all configurable per rank
8. **Fair Progression:** Auto-reset of PPV/GPV on rank advancement prevents unfair advantages
9. **User Choice:** Recruiters can use Path A, team builders can use Path B
10. **No Forced Path:** Users advance whichever way suits their strengths

---

## Dual-Path System: Key Concepts

### What is Dual-Path?

Two **independent ways** to achieve the same goal (rank advancement):

**Path A: Recruitment-Only**

- Requirement: Meet `required_direct_sponsors` count (e.g., 16)
- Focus: Heavy recruitment
- No PPV/GPV requirements
- Example: "I'm great at recruiting, I'll get 16 direct sponsors"

**Path B: PV-Based (New)**

- Requirement 1: Meet `required_sponsors_ppv_gpv` count (e.g., 4)
- Requirement 2: Meet `ppv_required` threshold (e.g., 100)
- Requirement 3: Meet `gpv_required` threshold (e.g., 1000)
- Focus: Team building + personal consumption
- Example: "I'll recruit 4 people, buy products, and help my team buy products"

### Why Two Paths?

1. **Different Strengths:**
    - Recruiters excel at Path A
    - Team builders excel at Path B
    - Both paths are legitimate and valuable

2. **No Priority System:**
    - Path A and Path B are checked independently
    - Whichever meets requirements **first** triggers advancement
    - No penalty for choosing either path

3. **Strategic Flexibility:**
    - Admin can set different requirements for each path
    - Example: Path A = 16 sponsors (hard), Path B = 4 sponsors (easier)
    - Or: Path A = 2 sponsors (easy), Path B = 4 sponsors (moderate)

### Which Path is Right for Which User?

**User Type: Heavy Recruiter**

```
Strengths:
- Can recruit 16+ people quickly
- Focus on quantity over team development

Best Path: A (Recruitment-Only)
Why: No need to manage team PV or personal consumption
```

**User Type: Team Builder**

```
Strengths:
- Can motivate team to purchase products
- Leads by example through personal consumption
- Develops long-term team stability

Best Path: B (PV-Based)
Why: Leverages team's collective purchasing power
```

**User Type: Hybrid (Both)**

```
Strengths:
- Can recruit AND build team
- Can choose whichever path happens first

Best Path: Either
Why: May qualify for Path A first, or Path B first
Result: Advances via whichever succeeds first
```

### Can Paths Conflict?

**No.** They are complementary, not competitive:

**Scenario 1: User Pursues Path A**

```
- Has 15 sponsors (Path A: needs 16)
- Has 4 sponsors + 100 PPV + 1000 GPV (Path B: met)
- System checks: Path A fails (15 < 16)
- System checks: Path B succeeds (all met)
- Result: Advances via Path B
```

**Scenario 2: User Pursues Path B**

```
- Has 18 sponsors (Path A: needs 16)
- Has 2 sponsors + 50 PPV + 500 GPV (Path B: needs 4 + 100 + 1000)
- System checks: Path A succeeds (18 ≥ 16)
- System checks: Path B fails
- Result: Advances via Path A
```

**Scenario 3: User Meets Both**

```
- Has 20 sponsors (exceeds Path A)
- Has 4 sponsors + 100 PPV + 1000 GPV (meets Path B)
- System checks: Path A succeeds (20 ≥ 16)
- System checks: Path B succeeds (all met)
- Result: Advances via Path A (checked first)
```

### Path B Advancement Details

**How Path B Works:**

1. **Sponsorship Requirement:**
    - User recruits `required_sponsors_ppv_gpv` same-rank direct sponsors
    - Example: Starter needs 4 Starter-rank directs
    - Counted via `direct_sponsors_tracker` + legacy referrals

2. **Personal Points Volume (PPV):**
    - User purchases products with `points > 0`
    - Points accumulate in `users.current_ppv`
    - Tracks in `points_tracker` with `point_type = 'product_purchase'`

3. **Group Points Volume (GPV):**
    - User's PPV + ALL indirect downlines' PPV (indefinite levels)
    - Accumulates in `users.current_gpv`
    - Each purchase adds GPV to user AND all uplines (recursive, no level limit)

4. **Advancement Trigger:**
    - When ALL THREE requirements met:
        - Directs count ≥ `required_sponsors_ppv_gpv`
        - PPV ≥ `ppv_required`
        - GPV ≥ `gpv_required`
    - Rank advances to next level

5. **Auto-Reset:**
    - `current_ppv` resets to 0
    - `current_gpv` resets to 0
    - Previous values recorded in `points_tracker` as negative entries
    - Fresh start for next rank

### Configuration Summary

**Per Rank Configuration:**

```
Field                    | Starter | Newbie | 1 Star | 2 Star | 3 Star | 4 Star | 5 Star
-------------------------|---------|--------|---------|---------|---------|---------|--------
required_direct_sponsors | 2       | 2       | 2       | 2       | 2       | 2       | 2
required_sponsors_ppv_gpv | 4       | 4       | 4       | 4       | 4       | 4       | 4
ppv_required              | 0       | 100     | 300     | 500     | 800     | 1200    | 2000
gpv_required              | 0       | 1000    | 5000    | 15000   | 40000   | 100000  | 250000
rank_pv_enabled          | true    | true    | true    | true    | true    | true    | true
rank_reward              | ₱0.00   | ₱500.00 | ₱1,000.00 | ₱2,000.00 | ₱4,000.00 | ₱10,000.00 | ₱20,000.00
```

**Path A Example (Starter → Newbie):**

```
Requirement: 16 Starter-rank direct sponsors
Current: 15 Starter-rank sponsors
Needs: 1 more Starter sponsor
PPV/GPV: Not considered
```

**Path B Example (Starter → Newbie):**

```
Requirement 1: 4 Starter-rank direct sponsors
Requirement 2: 100 PPV (personal purchases)
Requirement 3: 1000 GPV (user + all indirects, indefinite levels)

Current:
- Directs: 4 ✓
- PPV: 100 ✓
- GPV: 1000 ✓ (includes purchases from unlimited downline levels)

Result: Rank advances via Path B!
```

**GPV Calculation Details:**

```
When User A (Starter) purchases product worth 50 points:
1. User A's PPV increases by: +50
2. User A's GPV increases by: +50

All uplines (indefinite) receive +50 GPV:
- Level 1: User A's sponsor (Newbie)
- Level 2: Level 1's sponsor (1 Star)
- Level 3: Level 2's sponsor (2 Star)
- Level 4: Level 3's sponsor (3 Star)
- ... (continues as long as upline exists)
- No limit to depth
```

### Admin Configuration Tips

**Scenario 1: Aggressive Growth**

```
Goal: Fast rank ups via PV-based
Config:
- required_direct_sponsors = 16 (keep existing)
- required_sponsors_ppv_gpv = 2 (lower than default 4)
Effect: Users can rank up with just 2 sponsors + PV
```

**Scenario 2: Balanced System**

```
Goal: Equal requirements
Config:
- required_direct_sponsors = 4
- required_sponsors_ppv_gpv = 4
Effect: Both paths require same sponsor count
```

**Scenario 3: Progressive Difficulty**

```
Goal: Higher ranks harder to achieve
Config:
- Rank 1: required_sponsors_ppv_gpv = 2
- Rank 2: required_sponsors_ppv_gpv = 3
- Rank 3: required_sponsors_ppv_gpv = 4
- ...
Effect: Each rank requires more effort
```

**Scenario 4: Disable Path B**

```
Goal: Recruitment-only advancement
Config:
- rank_pv_enabled = false (for specific rank)
Effect: Path B not available, only Path A works
```

---

## Next Steps

**After Review:**

### Configuration Decisions

1. **Confirm Dual-Path System** - Is the two-path approach approved? (Path A: Recruitment, Path B: PV-Based)
2. Confirm `required_direct_sponsors` value per rank (current 2, or new values?)
3. Confirm `required_sponsors_ppv_gpv` value per rank (default 4, or progressive 2→8?)
4. Confirm `rank_pv_enabled` setting per rank (all true, or some disabled?)
5. Confirm PPV thresholds per rank (current values in document OK?)
6. Confirm GPV thresholds per rank (current values in document OK?)
7. Confirm point values per product (10% of price, or fixed amounts?)
8. **CONFIRMED:** Synchronous PPV/GPV reset only
9. Confirm `rank_advancements.advancement_type` values (use 'recruitment_based' and 'pv_based')
10. Confirm indefinite GPV levels (all downlines receive credit, no depth limit)

### Approval Required

1. **Synchronous Reset Confirmed** - PPV/GPV reset happens IMMEDIATELY on rank advancement
2. **Both Paths Confirmed** - Dual-path system with Path A (Recruitment) and Path B (PV-Based)
3. **Indefinite GPV Levels** - GPV accrues from all downline levels (no 10-level limit)
4. Dual-path system design
5. Field naming: `required_direct_sponsors`, `required_sponsors_ppv_gpv`, `rank_pv_enabled`
6. Path tracking in `rank_advancements` table (`recruitment_based' or `pv_based')
7. Ready to proceed with Phase 1 implementation

---

**Document Status:** Complete and ready for review.
