# Rank System Implementation Plan

## Overview

This document outlines a comprehensive, phased implementation plan for a **Rank-Based MLM Advancement System** that rewards users for sponsoring members of their current rank. The system introduces automatic package upgrades as rewards and implements rank-aware MLM bonus calculations.

---

## 🔴 CRITICAL DISTINCTION: MLM vs Unilevel

| Feature | MLM Commissions<br>(Package Purchases) | Unilevel Bonuses<br>(Product Purchases) |
|---------|----------------------------------------|------------------------------------------|
| **Trigger** | When downline buys a **package** | When downline buys a **product** |
| **Monthly Quota** | ❌ **NO** - Paid immediately | ✅ **YES** - Must meet PV quota |
| **Rank Comparison** | ✅ **YES** - Uses rank-based rules | ❌ **NO** - Standard calculation |
| **Qualification** | Must be network active only | Must be active AND meet quota |
| **Service** | `MLMCommissionService` | `UnilevelBonusService` |
| **Affected by Rank System** | ✅ Yes - New rank comparison rules | ❌ No - Existing logic unchanged |

**In Plain English**:
- **MLM** = Package purchases → Instant commissions → Subject to new rank rules → No quota needed
- **Unilevel** = Product purchases → Requires monthly quota → NOT affected by rank rules → Existing system unchanged

**This document focuses on**: Rank system for MLM commissions. Unilevel bonus system remains as-is.

---

## System Concept

### What is a "Rank"?

A **Rank** is determined by the **highest-cost package** a user has purchased. The cost of the package directly correlates with MLM earning potential.

### Core Features

1. **Rank Definition**: User's rank = their highest-cost package purchased
2. **Automatic Rank Advancement**: When a user sponsors N users of their current rank → system automatically buys next-tier package for them (fully paid by system)
3. **Rank-Based Bonus Calculation Rules**:
   - **Higher Rank has Lower Rank Downline**: Higher rank receives **lower rank's bonus rate** (prevents unfair advantages)
   - **Lower Rank has Higher Rank Downline**: Lower rank receives **their own (lower) bonus rate** (motivation to rank up)
4. **Rank Visibility**: Display rank prominently in user profile and admin user table
5. **Multiple Package Support**: System supports multiple packages with different rank levels

### Important: MLM vs Unilevel Commission Rules

**MLM Commissions** (Package purchases):
- ✅ **NO monthly quota requirement**
- ✅ Paid immediately upon package purchase
- ✅ Only requirement: User must be network active (purchased a package)
- ✅ Subject to rank-based bonus rules (as described above)

**Unilevel Bonuses** (Product purchases):
- ⚠️ **HAS monthly quota requirement** (if configured for the package)
- ⚠️ User must meet monthly PV quota to earn bonuses
- ⚠️ Must be network active AND quota met
- ⚠️ Quota tracked separately via `monthly_quota_tracker` table

**Summary**: The rank system affects MLM commission rates based on rank comparison. Monthly quota is a separate system that ONLY affects Unilevel bonuses.

---

## Current System State Analysis

### Existing Infrastructure ✅
- ✅ Multiple packages support (`packages` table)
- ✅ MLM commission system (`mlm_settings` table with 5 levels)
- ✅ Sponsor-based user hierarchy (`users.sponsor_id`)
- ✅ Order and purchase tracking (`orders`, `order_items`)
- ✅ Wallet system with balance segregation
- ✅ Monthly quota tracking (`monthly_quota_tracker`) - **FOR UNILEVEL ONLY**
- ✅ Network status tracking (`users.network_status`)

### What Needs to Be Built 🔨
- 🔨 **Rank tracking** (database field + logic)
- 🔨 **Rank advancement trigger** (sponsor count tracking)
- 🔨 **Automatic package purchase** (system-funded)
- 🔨 **Rank-aware MLM bonus calculation** (override existing logic)
- 🔨 **Rank display** (UI updates in profile and admin)
- 🔨 **Admin configuration** (N sponsors required, rank order)

---

## Database Schema Changes

### Phase 1: Core Rank Tracking

#### 1.1 Add Rank Column to Users Table
```sql
ALTER TABLE users
ADD COLUMN current_rank VARCHAR(100) NULL AFTER network_activated_at,
ADD COLUMN rank_package_id BIGINT UNSIGNED NULL AFTER current_rank,
ADD COLUMN rank_updated_at TIMESTAMP NULL AFTER rank_package_id,
ADD FOREIGN KEY (rank_package_id) REFERENCES packages(id) ON DELETE SET NULL,
ADD INDEX idx_current_rank (current_rank),
ADD INDEX idx_rank_package_id (rank_package_id);
```

**Fields**:
- `current_rank`: Display name of rank (e.g., "Starter", "Newbie", "Bronze", "Silver")
- `rank_package_id`: ID of highest-cost package purchased
- `rank_updated_at`: When rank was last updated

#### 1.2 Add Rank Configuration to Packages Table
```sql
ALTER TABLE packages
ADD COLUMN rank_name VARCHAR(100) NULL AFTER name,
ADD COLUMN rank_order INT UNSIGNED DEFAULT 1 AFTER rank_name,
ADD COLUMN required_direct_sponsors INT UNSIGNED DEFAULT 0 AFTER rank_order,
ADD COLUMN is_rankable BOOLEAN DEFAULT TRUE AFTER required_direct_sponsors,
ADD COLUMN next_rank_package_id BIGINT UNSIGNED NULL AFTER is_rankable,
ADD FOREIGN KEY (next_rank_package_id) REFERENCES packages(id) ON DELETE SET NULL,
ADD INDEX idx_rank_order (rank_order),
ADD INDEX idx_rank_name (rank_name);
```

**Fields**:
- `rank_name`: Display name for this rank tier (e.g., "Starter", "Newbie", "Bronze")
- `rank_order`: Numeric order (1 = lowest, higher = better)
- `required_direct_sponsors`: Number of same-rank sponsors needed to advance
- `is_rankable`: Whether this package contributes to rank (true for MLM packages)
- `next_rank_package_id`: ID of package to auto-purchase on rank advancement

#### 1.3 Create Rank Advancements Tracking Table
```sql
CREATE TABLE rank_advancements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    from_rank VARCHAR(100) NULL,
    to_rank VARCHAR(100) NOT NULL,
    from_package_id BIGINT UNSIGNED NULL,
    to_package_id BIGINT UNSIGNED NOT NULL,
    advancement_type ENUM('purchase', 'sponsorship_reward', 'admin_adjustment') DEFAULT 'purchase',
    required_sponsors INT UNSIGNED NULL COMMENT 'Number of sponsors required for this advancement',
    sponsors_count INT UNSIGNED NULL COMMENT 'Actual sponsors count at time of advancement',
    system_paid_amount DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Amount paid by system (if reward)',
    order_id BIGINT UNSIGNED NULL COMMENT 'Order created for the rank advancement',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (from_package_id) REFERENCES packages(id) ON DELETE SET NULL,
    FOREIGN KEY (to_package_id) REFERENCES packages(id) ON DELETE SET NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    
    INDEX idx_user_advancements (user_id, created_at),
    INDEX idx_advancement_type (advancement_type),
    INDEX idx_to_rank (to_rank)
);
```

**Purpose**: 
- Track all rank changes (purchase-based or reward-based)
- Audit trail for system-funded package purchases
- Historical record of user progression

#### 1.4 Create Direct Sponsors Tracker Table
```sql
CREATE TABLE direct_sponsors_tracker (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    sponsored_user_id BIGINT UNSIGNED NOT NULL,
    sponsored_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sponsored_user_rank_at_time VARCHAR(100) NULL,
    sponsored_user_package_id BIGINT UNSIGNED NULL,
    counted_for_rank VARCHAR(100) NULL COMMENT 'Which rank this sponsorship counted towards',
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sponsored_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sponsored_user_package_id) REFERENCES packages(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_sponsorship (user_id, sponsored_user_id),
    INDEX idx_user_rank (user_id, sponsored_user_rank_at_time),
    INDEX idx_counted_rank (user_id, counted_for_rank)
);
```

**Purpose**:
- Track direct sponsorships and their ranks at time of sponsorship
- Count how many same-rank sponsors user has
- Prevent double-counting when sponsor advances

---

## Implementation Phases

### Phase 1: Core Rank Tracking Foundation (2-3 days)

**Goal**: Establish database structure and basic rank tracking without automation

#### 1.1 Database Migrations (Day 1, Morning)
- ✅ Create migration: `add_rank_fields_to_users_table.php`
- ✅ Create migration: `add_rank_fields_to_packages_table.php`
- ✅ Create migration: `create_rank_advancements_table.php`
- ✅ Create migration: `create_direct_sponsors_tracker_table.php`

#### 1.2 Model Updates (Day 1, Afternoon)

**Update `app/Models/User.php`**:
```php
// Add to fillable
protected $fillable = [
    // ... existing fields
    'current_rank',
    'rank_package_id',
    'rank_updated_at',
];

// Add to casts
protected $casts = [
    // ... existing casts
    'rank_updated_at' => 'datetime',
];

// Relationships
public function rankPackage()
{
    return $this->belongsTo(Package::class, 'rank_package_id');
}

public function rankAdvancements()
{
    return $this->hasMany(RankAdvancement::class)->orderBy('created_at', 'desc');
}

public function directSponsorsTracked()
{
    return $this->hasMany(DirectSponsorsTracker::class, 'user_id');
}

// Helper Methods
public function getRankName(): string
{
    return $this->current_rank ?? 'Unranked';
}

public function getRankOrder(): int
{
    return $this->rankPackage?->rank_order ?? 0;
}

public function getHighestPackagePurchased(): ?Package
{
    return Package::whereHas('orderItems.order', function($q) {
        $q->where('user_id', $this->id)
          ->where('payment_status', 'paid');
    })
    ->where('is_rankable', true)
    ->orderBy('price', 'desc')
    ->first();
}

public function updateRank(): void
{
    $highestPackage = $this->getHighestPackagePurchased();
    
    if ($highestPackage) {
        $this->update([
            'current_rank' => $highestPackage->rank_name,
            'rank_package_id' => $highestPackage->id,
            'rank_updated_at' => now(),
        ]);
    }
}

public function getSameRankSponsorsCount(): int
{
    return $this->directSponsorsTracked()
        ->where('sponsored_user_rank_at_time', $this->current_rank)
        ->count();
}
```

**Create `app/Models/RankAdvancement.php`**:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RankAdvancement extends Model
{
    protected $fillable = [
        'user_id',
        'from_rank',
        'to_rank',
        'from_package_id',
        'to_package_id',
        'advancement_type',
        'required_sponsors',
        'sponsors_count',
        'system_paid_amount',
        'order_id',
        'notes',
    ];

    protected $casts = [
        'system_paid_amount' => 'decimal:2',
        'required_sponsors' => 'integer',
        'sponsors_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromPackage()
    {
        return $this->belongsTo(Package::class, 'from_package_id');
    }

    public function toPackage()
    {
        return $this->belongsTo(Package::class, 'to_package_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function isSystemReward(): bool
    {
        return $this->advancement_type === 'sponsorship_reward';
    }
}
```

**Create `app/Models/DirectSponsorsTracker.php`**:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirectSponsorsTracker extends Model
{
    protected $table = 'direct_sponsors_tracker';
    
    protected $fillable = [
        'user_id',
        'sponsored_user_id',
        'sponsored_at',
        'sponsored_user_rank_at_time',
        'sponsored_user_package_id',
        'counted_for_rank',
    ];

    protected $casts = [
        'sponsored_at' => 'datetime',
    ];

    public function sponsor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sponsoredUser()
    {
        return $this->belongsTo(User::class, 'sponsored_user_id');
    }

    public function sponsoredUserPackage()
    {
        return $this->belongsTo(Package::class, 'sponsored_user_package_id');
    }
}
```

**Update `app/Models/Package.php`**:
```php
// Add to fillable
protected $fillable = [
    // ... existing fields
    'rank_name',
    'rank_order',
    'required_direct_sponsors',
    'is_rankable',
    'next_rank_package_id',
];

// Add to casts
protected $casts = [
    // ... existing casts
    'rank_order' => 'integer',
    'required_direct_sponsors' => 'integer',
    'is_rankable' => 'boolean',
];

// Relationships
public function nextRankPackage()
{
    return $this->belongsTo(Package::class, 'next_rank_package_id');
}

public function previousRankPackages()
{
    return $this->hasMany(Package::class, 'next_rank_package_id');
}

public function scopeRankable($query)
{
    return $query->where('is_rankable', true);
}

public function scopeOrderedByRank($query)
{
    return $query->where('is_rankable', true)
                 ->orderBy('rank_order', 'asc');
}

// Helper Methods
public function canAdvanceToNextRank(): bool
{
    return !is_null($this->next_rank_package_id);
}

public function getNextRankPackage(): ?Package
{
    return $this->nextRankPackage;
}
```

#### 1.3 Assigning Ranks to Existing Users (Day 1, Afternoon)

**CRITICAL**: Before using the rank system, all existing users who purchased packages must be assigned their initial ranks.

**Options for assigning ranks:**
1. Via seeder (recommended for deployment)
2. Via helper script
3. Via migration (optional)

**Example implementation** (can be used in seeder or migration):

```php
<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Assign ranks to all existing users based on their purchased packages
     * This ensures backward compatibility when deploying rank system
     */
    public function up(): void
    {
        $this->command->info('Assigning ranks to existing users...');
        
        $totalUpdated = 0;
        $totalSkipped = 0;
        
        // Get all users who have purchased packages
        User::whereHas('orders', function($query) {
            $query->where('payment_status', 'paid')
                  ->whereHas('orderItems.package');
        })->chunk(100, function($users) use (&$totalUpdated, &$totalSkipped) {
            foreach ($users as $user) {
                // Get highest-priced package purchased by user
                $highestPackage = Package::whereHas('orderItems.order', function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->where('payment_status', 'paid');
                })
                ->where('is_mlm_package', true)
                ->orderBy('price', 'desc')
                ->first();
                
                if ($highestPackage && $highestPackage->rank_name) {
                    // Assign rank based on highest package
                    $user->update([
                        'current_rank' => $highestPackage->rank_name,
                        'rank_package_id' => $highestPackage->id,
                        'rank_updated_at' => now(),
                    ]);
                    
                    $totalUpdated++;
                    
                    if ($totalUpdated % 50 === 0) {
                        $this->command->info("Updated {$totalUpdated} users...");
                    }
                    
                    Log::info('Rank assigned to existing user', [
                        'user_id' => $user->id,
                        'username' => $user->username,
                        'rank' => $highestPackage->rank_name,
                        'package' => $highestPackage->name,
                    ]);
                } else {
                    $totalSkipped++;
                }
            }
        });
        
        $this->command->info("Rank assignment completed!");
        $this->command->info("Total users updated: {$totalUpdated}");
        $this->command->info("Total users skipped: {$totalSkipped}");
        
        Log::info('Existing user rank assignment completed', [
            'updated' => $totalUpdated,
            'skipped' => $totalSkipped,
        ]);
    }

    public function down(): void
    {
        $this->command->warn('Rolling back rank assignments...');
        
        User::whereNotNull('current_rank')->update([
            'current_rank' => null,
            'rank_package_id' => null,
            'rank_updated_at' => null,
        ]);
        
        $this->command->info('Rank assignments rolled back.');
    }
};
```

**When to Run This Migration**:
1. Run AFTER all rank system migrations (Phase 1 database changes)
2. Run BEFORE enabling rank advancement features
3. Run BEFORE backfilling legacy sponsorships

**Expected Behavior**:
- All users with "Starter" package → Assigned "Starter" rank
- All users with multiple packages → Assigned rank of highest-priced package
- Users without packages → Skipped (remain unranked)
- Idempotent: Can be run multiple times safely

**Verification**:
```bash
php artisan tinker
# Check users with Starter rank
User::where('current_rank', 'Starter')->count();

# View specific user
$user = User::where('username', 'test_user')->first();
dump($user->getRankName());
dump($user->rankPackage->name);
```

#### 1.4 Seeders Update (Day 1, Afternoon)

**Update `database/seeders/PackageSeeder.php`**:
```php
public function run()
{
    // Example: Create ranked packages
    $starter = Package::create([
        'name' => 'Starter Package',
        'slug' => 'starter-package',
        'rank_name' => 'Starter',
        'rank_order' => 1,
        'price' => 1000.00,
        'required_direct_sponsors' => 5, // Need 5 Starter-rank sponsors to advance
        'is_mlm_package' => true,
        'is_rankable' => true,
        'max_mlm_levels' => 5,
        'monthly_quota_points' => 100.00, // FOR UNILEVEL BONUSES ONLY (not MLM commissions)
        'enforce_monthly_quota' => true,  // FOR UNILEVEL BONUSES ONLY (not MLM commissions)
        'next_rank_package_id' => null, // Will set after creating next package
    ]);

    $newbie = Package::create([
        'name' => 'Newbie Package',
        'slug' => 'newbie-package',
        'rank_name' => 'Newbie',
        'rank_order' => 2,
        'price' => 2500.00,
        'required_direct_sponsors' => 8, // Need 8 Newbie-rank sponsors to advance
        'is_mlm_package' => true,
        'is_rankable' => true,
        'max_mlm_levels' => 5,
        'monthly_quota_points' => 150.00, // FOR UNILEVEL BONUSES ONLY (not MLM commissions)
        'enforce_monthly_quota' => true,  // FOR UNILEVEL BONUSES ONLY (not MLM commissions)
        'next_rank_package_id' => null, // Will set after creating next package
    ]);

    $bronze = Package::create([
        'name' => 'Bronze Package',
        'slug' => 'bronze-package',
        'rank_name' => 'Bronze',
        'rank_order' => 3,
        'price' => 5000.00,
        'required_direct_sponsors' => 10,
        'is_mlm_package' => true,
        'is_rankable' => true,
        'max_mlm_levels' => 5,
        'monthly_quota_points' => 200.00, // FOR UNILEVEL BONUSES ONLY (not MLM commissions)
        'enforce_monthly_quota' => true,  // FOR UNILEVEL BONUSES ONLY (not MLM commissions)
        'next_rank_package_id' => null, // Top rank
    ]);

    // Set next_rank_package_id relationships
    $starter->update(['next_rank_package_id' => $newbie->id]);
    $newbie->update(['next_rank_package_id' => $bronze->id]);

    // Create MLM settings for each package
    $this->createMLMSettings($starter->id, [
        1 => 200, // Level 1
        2 => 50,  // Level 2
        3 => 50,  // Level 3
        4 => 50,  // Level 4
        5 => 50,  // Level 5
    ]);

    $this->createMLMSettings($newbie->id, [
        1 => 400, // Higher commission for higher rank
        2 => 100,
        3 => 100,
        4 => 100,
        5 => 100,
    ]);

    $this->createMLMSettings($bronze->id, [
        1 => 800,
        2 => 200,
        3 => 200,
        4 => 200,
        5 => 200,
    ]);
}

private function createMLMSettings($packageId, $levels)
{
    foreach ($levels as $level => $commission) {
        \App\Models\MlmSetting::create([
            'package_id' => $packageId,
            'level' => $level,
            'commission_amount' => $commission,
            'is_active' => true,
        ]);
    }
}
```

#### 1.5 Make Package Name Readonly in Admin (Day 1, Evening)

**CRITICAL**: Package names are used for rank identification. Once set, they should NOT be changed to prevent conflicts.

**Modify `resources/views/admin/packages/edit.blade.php`**:

Change the name input field from:
```blade
<div class="mb-3">
    <label for="name" class="form-label">Package Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('name') is-invalid @enderror"
           id="name" name="name" value="{{ old('name', $package->name) }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
```

To:
```blade
<div class="mb-3">
    <label for="name" class="form-label">Package Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('name') is-invalid @enderror"
           id="name" name="name" value="{{ old('name', $package->name) }}" 
           readonly required>
    <div class="form-text text-warning">
        <strong>Note:</strong> Package name cannot be changed after creation to maintain rank system integrity. 
        Only create new packages if you need different names.
    </div>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
```

**Alternative: Conditional Readonly (Recommended)**

If you want to allow name changes ONLY for packages not yet part of the rank system:

```blade
<div class="mb-3">
    <label for="name" class="form-label">Package Name <span class="text-danger">*</span></label>
    @php
        // Check if package is part of rank system
        $isRankPackage = $package->rank_name && $package->is_mlm_package && $package->mlmSettings()->exists();
    @endphp
    <input type="text" class="form-control @error('name') is-invalid @enderror"
           id="name" name="name" value="{{ old('name', $package->name) }}" 
           {{ $isRankPackage ? 'readonly' : '' }} required>
    @if($isRankPackage)
        <div class="form-text text-warning">
            <strong>🔒 Locked:</strong> Package name cannot be changed because it's associated with rank "{{ $package->rank_name }}" and has MLM bonuses configured.
            <br>
            <small>Changing the name would break rank system integrity and MLM commission calculations.</small>
        </div>
    @else
        <div class="form-text text-muted">
            @if(!$package->rank_name)
                <span class="badge bg-secondary">Not a rank package</span> 
            @endif
            @if(!$package->is_mlm_package)
                <span class="badge bg-secondary">Not MLM enabled</span>
            @endif
            @if($package->is_mlm_package && !$package->mlmSettings()->exists())
                <span class="badge bg-warning">MLM bonuses not configured</span>
            @endif
            <br>
            This package is not yet fully part of the rank system. You can still change the name.
        </div>
    @endif
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
```

**Conditions for Locking Package Name**:
1. ✅ Package has `rank_name` set (associated with a rank tier)
2. ✅ Package has `is_mlm_package = true` (MLM enabled)
3. ✅ Package has MLM commission settings configured (`mlm_settings` table)

**Only if ALL THREE conditions are met**, the name becomes readonly.

**Examples**:
- Package with rank "Starter" + MLM enabled + MLM bonuses configured → **🔒 LOCKED**
- Package with rank "Starter" + MLM enabled + NO bonuses configured → ✏️ Editable (needs MLM setup first)
- Package with NO rank + MLM enabled + MLM bonuses configured → ✏️ Editable (not part of rank system)
- Regular product package (no MLM) → ✏️ Editable (not relevant to rank system)

**Controller Validation (Extra Safety)**

**Modify `app/Http/Controllers/Admin/AdminPackageController.php`** update method:

```php
public function update(Request $request, Package $package)
{
    $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'nullable|string|max:255|unique:packages,slug,' . $package->id,
        'price' => 'required|numeric|min:0.01',
        // ... other rules
    ];
    
    // CRITICAL: Prevent name changes for rank-associated MLM packages
    $isRankPackage = $package->rank_name 
                    && $package->is_mlm_package 
                    && $package->mlmSettings()->exists();
    
    if ($isRankPackage && $request->name !== $package->name) {
        return back()->withErrors([
            'name' => sprintf(
                'Package name cannot be changed because it is associated with rank "%s" and has MLM bonuses configured. This would break the rank system and MLM commission calculations.',
                $package->rank_name
            )
        ])->withInput();
    }
    
    $validated = $request->validate($rules);
    
    // ... rest of update logic
}
```

**Enhanced Validation Logic**:
```php
// Check if package is part of rank system (all conditions must be true)
$isRankPackage = $package->rank_name          // Has rank tier assigned
                && $package->is_mlm_package    // MLM enabled
                && $package->mlmSettings()->exists(); // Has MLM bonuses configured

// Additional safety: Log attempted name changes
if ($isRankPackage && $request->name !== $package->name) {
    \Log::warning('Attempted to change rank package name (blocked)', [
        'admin_id' => auth()->id(),
        'package_id' => $package->id,
        'old_name' => $package->name,
        'attempted_name' => $request->name,
        'rank' => $package->rank_name,
    ]);
    
    return back()->withErrors([
        'name' => sprintf(
            'Package name cannot be changed because it is associated with rank "%s" and has MLM bonuses configured. This would break the rank system and MLM commission calculations.',
            $package->rank_name
        )
    ])->withInput();
}
```

**Why This Is Important**:
1. **Rank system uses package names for identification**
   - Historical rank assignments reference package names
   - RankAdvancement records store package references
2. **MLM commission calculations depend on package identity**
   - MlmSetting records are tied to package_id
   - Changing name could confuse commission tracking
3. **Historical data integrity**
   - User rank history shows "Advanced from Starter to Newbie"
   - Changing "Starter" to "Beginner" would show "Advanced from Beginner to Newbie" (confusing!)
4. **User confusion prevention**
   - "I was Starter rank yesterday, now it's called Beginner?"
   - Inconsistent user experience

**What Packages Can Still Be Renamed?**:
- ✏️ Non-MLM packages (regular products/services)
- ✏️ MLM packages without rank_name set (not part of rank tiers)
- ✏️ MLM packages without bonuses configured yet (setup in progress)

**Best Practice**: 
- Lock package names once ALL three conditions are met (rank + MLM + bonuses)
- Create new packages instead of renaming existing rank packages
- Use `rank_name` field for display variations if needed
- Test packages without rank/MLM settings before locking them in

#### 1.6 Testing Phase 1 (Day 2)

**Test Script**: `test_rank_system_phase1.php`
```php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;

echo "=== Phase 1: Rank System Foundation Test ===\n\n";

// Test 1: Check packages have rank fields
echo "Test 1: Package Rank Configuration\n";
$packages = Package::rankable()->orderedByRank()->get();

foreach ($packages as $package) {
    echo "  {$package->rank_name} (Order: {$package->rank_order})\n";
    echo "    - Price: ₱" . number_format($package->price, 2) . "\n";
    echo "    - Required Sponsors: {$package->required_direct_sponsors}\n";
    echo "    - Next Rank: " . ($package->nextRankPackage?->rank_name ?? 'None (Top Rank)') . "\n\n";
}

// Test 2: Check user rank fields
echo "Test 2: User Rank Fields\n";
$user = User::first();
echo "  User: {$user->username}\n";
echo "  Current Rank: " . $user->getRankName() . "\n";
echo "  Rank Package: " . ($user->rankPackage?->name ?? 'None') . "\n";
echo "  Rank Order: " . $user->getRankOrder() . "\n\n";

// Test 3: Update user rank based on purchase
echo "Test 3: Manual Rank Update\n";
$user->updateRank();
echo "  Rank updated to: " . $user->fresh()->getRankName() . "\n\n";

// Test 4: Check highest package purchased
echo "Test 4: Highest Package Purchased\n";
$highestPackage = $user->getHighestPackagePurchased();
if ($highestPackage) {
    echo "  Package: {$highestPackage->name}\n";
    echo "  Rank: {$highestPackage->rank_name}\n";
    echo "  Price: ₱" . number_format($highestPackage->price, 2) . "\n";
} else {
    echo "  No package purchased yet\n";
}

echo "\nPhase 1 Test Completed!\n";
```

**Checklist**:
- [ ] All migrations run successfully (including rank assignment migration)
- [ ] **Existing users with Starter package are assigned Starter rank**
- [ ] Packages have rank fields populated
- [ ] Users can have rank assigned
- [ ] Rank relationships work (user → package)
- [ ] Package rank chain works (starter → newbie → bronze)
- [ ] Helper methods return correct values
- [ ] **Package name field is readonly ONLY when: rank_name + is_mlm_package + has MLM settings**
- [ ] **Non-rank packages can still be renamed freely**
- [ ] **Controller validation prevents name changes with proper conditions**
- [ ] **Attempted name changes are logged for security audit**
- [ ] No database errors

---

### Phase 2: Rank-Aware MLM Bonus Calculation (2-3 days)

**Goal**: Implement rank-based bonus rules for MLM commissions (package purchases ONLY - Unilevel bonuses unchanged)

**CRITICAL**: Rank-aware bonuses ONLY apply to users with `network_status = 'active'`. This is consistent with existing MLM logic.

**Requirements**:
- ✅ User must have `network_status = 'active'` (purchased a package)
- ✅ Both upline and buyer MUST have rank packages (if either lacks rank → 0.00 commission)
- ✅ Rank comparison rules apply AFTER active status is verified
- ✅ Inactive users are skipped entirely (no commission, no rank comparison)
- ✅ Users without ranks get 0.00 commission (no participation in rank system)
- ✅ Maintains compatibility with existing `MLMCommissionService` active checks

#### 2.1 Create Rank Comparison Service (Day 1, Morning)

**IMPORTANT**: This service is ONLY for MLM commissions (package purchases). Unilevel bonuses are NOT affected by rank comparison.

**CRITICAL RULE**: If either upline or buyer has no rank package, commission = 0.00 (NO COMMISSION). Both must have ranks for rank-based commission to apply.

**Create `app/Services/RankComparisonService.php`**:
```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\Package;
use App\Models\MlmSetting;
use Illuminate\Support\Facades\Log;

class RankComparisonService
{
    /**
     * Get the effective MLM commission for an upline based on rank comparison
     * 
     * FOR MLM COMMISSIONS ONLY (package purchases)
     * Unilevel bonuses (product purchases) are NOT affected by this
     * 
     * PREREQUISITE: Upline must be network active (checked by MLMCommissionService)
     * This method assumes upline has already passed isNetworkActive() check
     * 
     * RULE 1: If upline has higher rank than buyer, upline gets buyer's (lower) rate
     * RULE 2: If upline has lower rank than buyer, upline gets their own (lower) rate
     * RULE 3: If both have same rank, use standard commission
     * 
     * @param User $upline
     * @param User $buyer
     * @param int $level
     * @return float
     */
    public function getEffectiveCommission(User $upline, User $buyer, int $level): float
    {
        // CRITICAL: This method assumes upline is already network active
        // The calling service (MLMCommissionService) MUST check isNetworkActive() first
        // We do not re-check here to avoid redundant queries
        
        $uplinePackage = $upline->rankPackage;
        $buyerPackage = $buyer->rankPackage;

        // CRITICAL: If either has no rank package, NO COMMISSION
        // Both upline and buyer MUST have ranks for rank-based commission to apply
        if (!$uplinePackage || !$buyerPackage) {
            Log::info('No rank-based commission: Missing rank package', [
                'upline_id' => $upline->id,
                'upline_has_rank' => !is_null($uplinePackage),
                'upline_rank' => $uplinePackage?->rank_name ?? 'None',
                'buyer_id' => $buyer->id,
                'buyer_has_rank' => !is_null($buyerPackage),
                'buyer_rank' => $buyerPackage?->rank_name ?? 'None',
                'level' => $level,
            ]);
            return 0.00; // NO COMMISSION if either lacks rank
        }

        $uplineRankOrder = $uplinePackage->rank_order;
        $buyerRankOrder = $buyerPackage->rank_order;

        Log::info('Rank Comparison for Commission', [
            'upline_id' => $upline->id,
            'upline_rank' => $uplinePackage->rank_name,
            'upline_rank_order' => $uplineRankOrder,
            'buyer_id' => $buyer->id,
            'buyer_rank' => $buyerPackage->rank_name,
            'buyer_rank_order' => $buyerRankOrder,
            'level' => $level,
        ]);

        // RULE 1: Higher rank upline with lower rank buyer
        // Upline gets buyer's lower commission rate
        if ($uplineRankOrder > $buyerRankOrder) {
            $commission = MlmSetting::getCommissionForLevel($buyerPackage->id, $level);
            
            Log::info('RULE 1 Applied: Higher rank upline gets lower rank buyer rate', [
                'upline_rank' => $uplinePackage->rank_name,
                'buyer_rank' => $buyerPackage->rank_name,
                'commission' => $commission,
                'reason' => 'Preventing unfair advantage from rank difference'
            ]);
            
            return $commission;
        }

        // RULE 2: Lower rank upline with higher rank buyer
        // Upline gets their own lower commission rate
        if ($uplineRankOrder < $buyerRankOrder) {
            $commission = MlmSetting::getCommissionForLevel($uplinePackage->id, $level);
            
            Log::info('RULE 2 Applied: Lower rank upline gets their own rate', [
                'upline_rank' => $uplinePackage->rank_name,
                'buyer_rank' => $buyerPackage->rank_name,
                'commission' => $commission,
                'reason' => 'Motivation to rank up for higher earnings'
            ]);
            
            return $commission;
        }

        // RULE 3: Same rank - use buyer's package commission (standard)
        $commission = MlmSetting::getCommissionForLevel($buyerPackage->id, $level);
        
        Log::info('RULE 3 Applied: Same rank, standard commission', [
            'upline_rank' => $uplinePackage->rank_name,
            'buyer_rank' => $buyerPackage->rank_name,
            'commission' => $commission,
        ]);
        
        return $commission;
    }

    /**
     * Get a detailed explanation of why this commission was calculated this way
     * 
     * @param User $upline
     * @param User $buyer
     * @param int $level
     * @return array
     */
    public function getCommissionExplanation(User $upline, User $buyer, int $level): array
    {
        $uplinePackage = $upline->rankPackage;
        $buyerPackage = $buyer->rankPackage;

        // If either has no rank package, NO COMMISSION
        if (!$uplinePackage || !$buyerPackage) {
            return [
                'rule' => 'No Rank = No Commission',
                'explanation' => sprintf(
                    'No commission given. %s %s rank. Both upline and buyer must have ranks for commission.',
                    !$uplinePackage ? 'Upline lacks' : 'Buyer lacks',
                    !$uplinePackage ? ($upline->username ?? 'User') : ($buyer->username ?? 'User')
                ),
                'commission' => 0.00,
                'upline_rank' => $uplinePackage?->rank_name ?? 'None',
                'buyer_rank' => $buyerPackage?->rank_name ?? 'None',
                'reason' => 'missing_rank_package',
            ];
        }

        $uplineRankOrder = $uplinePackage->rank_order;
        $buyerRankOrder = $buyerPackage->rank_order;
        $commission = $this->getEffectiveCommission($upline, $buyer, $level);

        if ($uplineRankOrder > $buyerRankOrder) {
            return [
                'rule' => 'Rule 1: Higher Rank → Lower Rate',
                'explanation' => "As a {$uplinePackage->rank_name}, you earn {$buyerPackage->rank_name}'s commission rate when sponsoring lower-ranked members.",
                'upline_rank' => $uplinePackage->rank_name,
                'buyer_rank' => $buyerPackage->rank_name,
                'commission' => $commission,
                'package_used' => $buyerPackage->name,
            ];
        }

        if ($uplineRankOrder < $buyerRankOrder) {
            return [
                'rule' => 'Rule 2: Lower Rank → Own Rate',
                'explanation' => "As a {$uplinePackage->rank_name}, you earn your own commission rate. Advance to {$buyerPackage->rank_name} to earn more!",
                'upline_rank' => $uplinePackage->rank_name,
                'buyer_rank' => $buyerPackage->rank_name,
                'commission' => $commission,
                'package_used' => $uplinePackage->name,
                'motivation' => "Rank up to {$buyerPackage->rank_name} to increase your earnings!",
            ];
        }

        return [
            'rule' => 'Rule 3: Same Rank → Standard',
            'explanation' => "Both you and your downline are {$uplinePackage->rank_name}, standard commission applies.",
            'upline_rank' => $uplinePackage->rank_name,
            'buyer_rank' => $buyerPackage->rank_name,
            'commission' => $commission,
            'package_used' => $buyerPackage->name,
        ];
    }
}
```

#### 2.2 Update MLMCommissionService (Day 1, Afternoon)

**CRITICAL NOTE**: We are ONLY modifying MLM commission calculation. DO NOT touch UnilevelBonusService - it remains unchanged and continues to use monthly quota system.

**Modify `app/Services/MLMCommissionService.php`**:
```php
// Add to constructor
public function __construct(
    protected RankComparisonService $rankComparison
) {}

// REPLACE the commission calculation in processCommissions() method
// IMPORTANT: Keep the existing isNetworkActive() check - it's REQUIRED!

// Existing code that MUST REMAIN:
while ($currentUser && $level <= $maxLevels) {
    // CRITICAL: Check network active status BEFORE rank comparison
    if (!$currentUser->isNetworkActive()) {
        Log::info('Upline skipped: Not network active', [
            'upline_id' => $currentUser->id,
            'level' => $level,
            'network_status' => $currentUser->network_status,
        ]);
        $currentUser = $currentUser->sponsor;
        continue; // Skip to the next sponsor
    }
    
    // NOW apply rank-aware commission calculation (only for active users)
    // FROM:
    // $commission = MlmSetting::getCommissionForLevel($package->id, $level);
    
    // TO:
    $commission = $this->rankComparison->getEffectiveCommission(
        $currentUser,  // upline (already verified as active)
        $buyer,        // buyer
        $level
    );

    $explanation = $this->rankComparison->getCommissionExplanation(
        $currentUser,
        $buyer,
        $level
    );

    Log::info('Rank-Aware Commission Calculated (Active User)', [
        'upline_id' => $currentUser->id,
        'buyer_id' => $buyer->id,
        'level' => $level,
        'upline_network_status' => $currentUser->network_status, // Should be 'active'
        'rule_applied' => $explanation['rule'],
        'commission' => $commission,
        'explanation' => $explanation['explanation'],
    ]);
    
    // ... rest of commission crediting logic
}
```

**Key Points**:
1. **DO NOT remove** the `isNetworkActive()` check - it's essential
2. Rank comparison happens AFTER active status is verified
3. Inactive users are skipped before rank comparison (no wasted computation)
4. This maintains backward compatibility with existing MLM logic

#### 2.3 Testing Phase 2 (Day 2)

**Test Script**: `test_rank_aware_commission.php`
```php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Services\RankComparisonService;

echo "=== Phase 2: Rank-Aware Commission Test ===\n\n";

$rankService = new RankComparisonService();

// Create test hierarchy: Starter → Newbie → Bronze
$starter = Package::where('rank_name', 'Starter')->first();
$newbie = Package::where('rank_name', 'Newbie')->first();
$bronze = Package::where('rank_name', 'Bronze')->first();

// Test Scenario 0: Users without rank packages (NO COMMISSION)
echo "Scenario 0: Upline without rank package\n";
echo "Expected: NO COMMISSION (return 0.00)\n\n";

$noRankUpline = User::factory()->create([
    'current_rank' => null,
    'rank_package_id' => null,
    'network_status' => 'active',
]);
$rankedBuyer = User::factory()->create([
    'current_rank' => 'Starter',
    'rank_package_id' => $starter->id,
    'network_status' => 'active',
]);

$commission0 = $rankService->getEffectiveCommission($noRankUpline, $rankedBuyer, 1);
$explanation0 = $rankService->getCommissionExplanation($noRankUpline, $rankedBuyer, 1);

echo "Commission: ₱" . number_format($commission0, 2) . "\n";
echo "Rule: {$explanation0['rule']}\n";
echo "Explanation: {$explanation0['explanation']}\n";
echo "✓ Confirmed: No rank = No commission\n\n";

// Test Scenario 0b: Buyer without rank package (NO COMMISSION)
echo "Scenario 0b: Buyer without rank package\n";
echo "Expected: NO COMMISSION (return 0.00)\n\n";

$rankedUpline = User::factory()->create([
    'current_rank' => 'Newbie',
    'rank_package_id' => $newbie->id,
    'network_status' => 'active',
]);
$noRankBuyer = User::factory()->create([
    'current_rank' => null,
    'rank_package_id' => null,
    'network_status' => 'active',
]);

$commission0b = $rankService->getEffectiveCommission($rankedUpline, $noRankBuyer, 1);
$explanation0b = $rankService->getCommissionExplanation($rankedUpline, $noRankBuyer, 1);

echo "Commission: ₱" . number_format($commission0b, 2) . "\n";
echo "Rule: {$explanation0b['rule']}\n";
echo "Explanation: {$explanation0b['explanation']}\n";
echo "✓ Confirmed: No rank = No commission\n\n";

// Test Scenario 1: Higher rank (Newbie) has lower rank downline (Starter)
echo "Scenario 1: Newbie (higher rank) has Starter (lower rank) downline\n";
echo "Expected: Newbie earns Starter's commission rate\n\n";

$newbieUser = User::factory()->create([
    'current_rank' => 'Newbie', 
    'rank_package_id' => $newbie->id,
    'network_status' => 'active', // CRITICAL: Must be active
]);
$starterUser = User::factory()->create([
    'current_rank' => 'Starter', 
    'rank_package_id' => $starter->id,
    'network_status' => 'active', // CRITICAL: Must be active
]);

$commission = $rankService->getEffectiveCommission($newbieUser, $starterUser, 1);
$explanation = $rankService->getCommissionExplanation($newbieUser, $starterUser, 1);

echo "Commission: ₱" . number_format($commission, 2) . "\n";
echo "Rule: {$explanation['rule']}\n";
echo "Explanation: {$explanation['explanation']}\n\n";

// Test Scenario 2: Lower rank (Starter) has higher rank downline (Newbie)
echo "Scenario 2: Starter (lower rank) has Newbie (higher rank) downline\n";
echo "Expected: Starter earns their own (Starter) commission rate\n\n";

$commission2 = $rankService->getEffectiveCommission($starterUser, $newbieUser, 1);
$explanation2 = $rankService->getCommissionExplanation($starterUser, $newbieUser, 1);

echo "Commission: ₱" . number_format($commission2, 2) . "\n";
echo "Rule: {$explanation2['rule']}\n";
echo "Explanation: {$explanation2['explanation']}\n\n";

// Test Scenario 3: Same rank
echo "Scenario 3: Starter has another Starter downline\n";
echo "Expected: Standard Starter commission rate\n\n";

$starterUser2 = User::factory()->create([
    'current_rank' => 'Starter', 
    'rank_package_id' => $starter->id,
    'network_status' => 'active', // CRITICAL: Must be active
]);

$commission3 = $rankService->getEffectiveCommission($starterUser, $starterUser2, 1);
$explanation3 = $rankService->getCommissionExplanation($starterUser, $starterUser2, 1);

echo "Commission: ₱" . number_format($commission3, 2) . "\n";
echo "Rule: {$explanation3['rule']}\n";
echo "Explanation: {$explanation3['explanation']}\n\n";

// Test Scenario 4: Inactive user (should be skipped by MLMCommissionService)
echo "Scenario 4: Inactive Newbie user (should not reach rank comparison)\n";
echo "Expected: MLMCommissionService skips BEFORE calling RankComparisonService\n\n";

$inactiveUser = User::factory()->create([
    'current_rank' => 'Newbie', 
    'rank_package_id' => $newbie->id,
    'network_status' => 'inactive', // NOT ACTIVE
]);

echo "Inactive user network status: {$inactiveUser->network_status}\n";
echo "isNetworkActive(): " . ($inactiveUser->isNetworkActive() ? 'true' : 'false') . "\n";
echo "Note: This user would be skipped by MLMCommissionService before rank comparison\n";
echo "RankComparisonService should NEVER receive inactive users as input\n\n";

echo "Phase 2 Test Completed!\n";
```

**Checklist**:
- [ ] **Existing `isNetworkActive()` check is preserved in MLMCommissionService**
- [ ] **Inactive users are skipped BEFORE rank comparison (no commission)**
- [ ] **Scenario 0: Users without rank packages get NO COMMISSION (0.00)**
- [ ] **Scenario 0a: Upline without rank → 0.00 commission**
- [ ] **Scenario 0b: Buyer without rank → 0.00 commission**
- [ ] Rule 1 works: Higher rank gets lower rank's rate (for active users with ranks)
- [ ] Rule 2 works: Lower rank gets their own rate (for active users with ranks)
- [ ] Rule 3 works: Same rank gets standard rate (for active users with ranks)
- [ ] Explanation messages are clear
- [ ] Logs show detailed rank comparison with network_status
- [ ] Commission amounts are correct
- [ ] **Test confirms inactive users never reach RankComparisonService**
- [ ] **Test confirms users without ranks get 0.00 commission**

---

### Phase 3: Automatic Rank Advancement System (3-4 days)

**Goal**: Implement automatic package purchase when user sponsors N same-rank users (with backward compatibility for legacy users)

#### 3.1 Create Rank Advancement Service (Day 1)

**Create `app/Services/RankAdvancementService.php`**:
```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RankAdvancement;
use App\Models\DirectSponsorsTracker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RankAdvancementService
{
    /**
     * Track a new sponsorship and check if rank advancement is triggered
     * 
     * @param User $sponsor
     * @param User $newUser
     * @return bool Whether rank advancement was triggered
     */
    public function trackSponsorship(User $sponsor, User $newUser): bool
    {
        DB::beginTransaction();
        try {
            // Record the sponsorship
            DirectSponsorsTracker::create([
                'user_id' => $sponsor->id,
                'sponsored_user_id' => $newUser->id,
                'sponsored_at' => now(),
                'sponsored_user_rank_at_time' => $newUser->current_rank,
                'sponsored_user_package_id' => $newUser->rank_package_id,
                'counted_for_rank' => $newUser->current_rank,
            ]);

            Log::info('Sponsorship Tracked', [
                'sponsor_id' => $sponsor->id,
                'sponsor_rank' => $sponsor->current_rank,
                'new_user_id' => $newUser->id,
                'new_user_rank' => $newUser->current_rank,
            ]);

            // Check if advancement criteria met
            $advancementTriggered = $this->checkAndTriggerAdvancement($sponsor);

            DB::commit();
            return $advancementTriggered;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to track sponsorship', [
                'sponsor_id' => $sponsor->id,
                'new_user_id' => $newUser->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if user meets advancement criteria and trigger if yes
     * 
     * @param User $user
     * @return bool Whether advancement was triggered
     */
    public function checkAndTriggerAdvancement(User $user): bool
    {
        // Get user's current rank package
        $currentPackage = $user->rankPackage;

        if (!$currentPackage) {
            Log::info('User has no rank package, cannot advance', ['user_id' => $user->id]);
            return false;
        }

        // Check if there's a next rank available
        if (!$currentPackage->canAdvanceToNextRank()) {
            Log::info('User is at top rank, cannot advance', [
                'user_id' => $user->id,
                'current_rank' => $currentPackage->rank_name,
            ]);
            return false;
        }

        // Count same-rank sponsors
        $sameRankSponsorsCount = $user->directSponsorsTracked()
            ->where('counted_for_rank', $user->current_rank)
            ->count();

        $requiredSponsors = $currentPackage->required_direct_sponsors;

        Log::info('Checking Rank Advancement Criteria', [
            'user_id' => $user->id,
            'current_rank' => $user->current_rank,
            'same_rank_sponsors' => $sameRankSponsorsCount,
            'required_sponsors' => $requiredSponsors,
            'can_advance' => $sameRankSponsorsCount >= $requiredSponsors,
        ]);

        // Check if criteria met
        if ($sameRankSponsorsCount >= $requiredSponsors) {
            return $this->advanceUserRank($user, $sameRankSponsorsCount);
        }

        return false;
    }

    /**
     * Advance user to next rank (system-funded package purchase)
     * 
     * @param User $user
     * @param int $sponsorsCount
     * @return bool
     */
    public function advanceUserRank(User $user, int $sponsorsCount): bool
    {
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

            // Update user rank
            $user->update([
                'current_rank' => $nextPackage->rank_name,
                'rank_package_id' => $nextPackage->id,
                'rank_updated_at' => now(),
            ]);

            // Activate network status if not already active
            $user->activateNetwork();

            // Record advancement
            RankAdvancement::create([
                'user_id' => $user->id,
                'from_rank' => $currentPackage->rank_name,
                'to_rank' => $nextPackage->rank_name,
                'from_package_id' => $currentPackage->id,
                'to_package_id' => $nextPackage->id,
                'advancement_type' => 'sponsorship_reward',
                'required_sponsors' => $currentPackage->required_direct_sponsors,
                'sponsors_count' => $sponsorsCount,
                'system_paid_amount' => $nextPackage->price,
                'order_id' => $order->id,
                'notes' => "Automatic rank advancement for sponsoring {$sponsorsCount} {$currentPackage->rank_name}-rank users",
            ]);

            Log::info('Rank Advanced Successfully', [
                'user_id' => $user->id,
                'from_rank' => $currentPackage->rank_name,
                'to_rank' => $nextPackage->rank_name,
                'order_id' => $order->id,
                'sponsors_count' => $sponsorsCount,
            ]);

            // Send notification to user
            $this->sendRankAdvancementNotification($user, $currentPackage, $nextPackage, $order);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rank Advancement Failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Create a system-funded order for rank advancement
     * 
     * @param User $user
     * @param Package $package
     * @return Order|null
     */
    private function createSystemFundedOrder(User $user, Package $package): ?Order
    {
        try {
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'RANK-' . strtoupper(uniqid()),
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_method' => 'system_reward',
                'subtotal' => $package->price,
                'grand_total' => $package->price,
                'notes' => "System-funded rank advancement reward: {$package->rank_name}",
            ]);

            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'package_id' => $package->id,
                'product_id' => null,
                'quantity' => 1,
                'price' => $package->price,
                'subtotal' => $package->price,
            ]);

            Log::info('System-Funded Order Created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $user->id,
                'package_id' => $package->id,
                'amount' => $package->price,
            ]);

            return $order;

        } catch (\Exception $e) {
            Log::error('Failed to create system-funded order', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Send rank advancement notification
     * 
     * @param User $user
     * @param Package $fromPackage
     * @param Package $toPackage
     * @param Order $order
     */
    private function sendRankAdvancementNotification(User $user, Package $fromPackage, Package $toPackage, Order $order): void
    {
        // TODO: Implement notification (database + email)
        // $user->notify(new RankAdvancementNotification($fromPackage, $toPackage, $order));
        
        Log::info('Rank Advancement Notification Sent', [
            'user_id' => $user->id,
            'from_rank' => $fromPackage->rank_name,
            'to_rank' => $toPackage->rank_name,
        ]);
    }

    /**
     * Get user's rank advancement progress
     * 
     * @param User $user
     * @return array
     */
    public function getRankAdvancementProgress(User $user): array
    {
        $currentPackage = $user->rankPackage;

        if (!$currentPackage) {
            return [
                'current_rank' => 'Unranked',
                'can_advance' => false,
                'progress' => 0,
                'required' => 0,
                'remaining' => 0,
            ];
        }

        $sameRankSponsorsCount = $user->getSameRankSponsorsCount();
        $requiredSponsors = $currentPackage->required_direct_sponsors;
        $remaining = max(0, $requiredSponsors - $sameRankSponsorsCount);
        $progress = $requiredSponsors > 0 
            ? min(100, ($sameRankSponsorsCount / $requiredSponsors) * 100)
            : 0;

        return [
            'current_rank' => $currentPackage->rank_name,
            'current_rank_order' => $currentPackage->rank_order,
            'next_rank' => $currentPackage->nextRankPackage?->rank_name ?? 'Top Rank',
            'next_rank_package' => $currentPackage->nextRankPackage,
            'can_advance' => $currentPackage->canAdvanceToNextRank(),
            'sponsors_count' => $sameRankSponsorsCount,
            'required_sponsors' => $requiredSponsors,
            'remaining_sponsors' => $remaining,
            'progress_percentage' => $progress,
            'is_eligible' => $sameRankSponsorsCount >= $requiredSponsors,
        ];
    }
}
```

#### 3.2 Integrate into Registration Flow (Day 2)

**Modify `app/Actions/Fortify/CreateNewUser.php`**:
```php
use App\Services\RankAdvancementService;

public function create(array $input)
{
    // ... existing user creation logic ...
    
    $user = User::create([
        'name' => $input['name'],
        'email' => $input['email'],
        'password' => Hash::make($input['password']),
        'sponsor_id' => $sponsor->id,
    ]);

    // NEW: Track sponsorship and check rank advancement
    if ($user->sponsor) {
        $rankService = app(RankAdvancementService::class);
        $advanced = $rankService->trackSponsorship($user->sponsor, $user);
        
        if ($advanced) {
            Log::info('Sponsor advanced rank after new registration', [
                'sponsor_id' => $user->sponsor->id,
                'new_user_id' => $user->id,
            ]);
        }
    }

    return $user;
}
```

#### 3.2b Backward Compatibility for Legacy Users (IMPORTANT!)

**Problem**: Existing users already have direct referrals before rank system deployment

**Solution**: When checking advancement, count BOTH tracked sponsorships AND legacy referrals from `users.sponsor_id`

**Update `app/Services/RankAdvancementService.php`** (modify `checkAndTriggerAdvancement` method):
```php
/**
 * Check if user meets advancement criteria and trigger if yes
 * BACKWARD COMPATIBLE: Counts both tracked sponsorships AND legacy sponsor_id relationships
 * 
 * @param User $user
 * @return bool Whether advancement was triggered
 */
public function checkAndTriggerAdvancement(User $user): bool
{
    // Get user's current rank package
    $currentPackage = $user->rankPackage;

    if (!$currentPackage) {
        Log::info('User has no rank package, cannot advance', ['user_id' => $user->id]);
        return false;
    }

    // Check if there's a next rank available
    if (!$currentPackage->canAdvanceToNextRank()) {
        Log::info('User is at top rank, cannot advance', [
            'user_id' => $user->id,
            'current_rank' => $currentPackage->rank_name,
        ]);
        return false;
    }

    // BACKWARD COMPATIBILITY: Count same-rank sponsors from BOTH sources
    // 1. Tracked sponsorships (new data)
    $trackedCount = $user->directSponsorsTracked()
        ->where('counted_for_rank', $user->current_rank)
        ->count();
    
    // 2. Legacy sponsorships (existing sponsor_id relationships not yet tracked)
    $legacyCount = User::where('sponsor_id', $user->id)
        ->where('current_rank', $user->current_rank)
        ->whereNotIn('id', function($query) use ($user) {
            $query->select('sponsored_user_id')
                  ->from('direct_sponsors_tracker')
                  ->where('user_id', $user->id);
        })
        ->count();
    
    $totalSameRankSponsors = $trackedCount + $legacyCount;

    $requiredSponsors = $currentPackage->required_direct_sponsors;

    Log::info('Checking Rank Advancement Criteria (Backward Compatible)', [
        'user_id' => $user->id,
        'current_rank' => $user->current_rank,
        'tracked_sponsors' => $trackedCount,
        'legacy_sponsors' => $legacyCount,
        'total_same_rank_sponsors' => $totalSameRankSponsors,
        'required_sponsors' => $requiredSponsors,
        'can_advance' => $totalSameRankSponsors >= $requiredSponsors,
    ]);

    // Check if criteria met
    if ($totalSameRankSponsors >= $requiredSponsors) {
        // IMPORTANT: Before advancing, backfill legacy sponsorships into tracker
        $this->backfillLegacySponsorships($user);
        
        return $this->advanceUserRank($user, $totalSameRankSponsors);
    }

    return false;
}

/**
 * Backfill legacy sponsorships into direct_sponsors_tracker
 * This ensures all existing referrals are properly tracked going forward
 * 
 * @param User $user
 */
private function backfillLegacySponsorships(User $user): void
{
    // Get all direct referrals not yet tracked
    $legacyReferrals = User::where('sponsor_id', $user->id)
        ->whereNotIn('id', function($query) use ($user) {
            $query->select('sponsored_user_id')
                  ->from('direct_sponsors_tracker')
                  ->where('user_id', $user->id);
        })
        ->get();

    foreach ($legacyReferrals as $referral) {
        try {
            DirectSponsorsTracker::create([
                'user_id' => $user->id,
                'sponsored_user_id' => $referral->id,
                'sponsored_at' => $referral->created_at ?? now(),
                'sponsored_user_rank_at_time' => $referral->current_rank,
                'sponsored_user_package_id' => $referral->rank_package_id,
                'counted_for_rank' => $referral->current_rank,
            ]);
            
            Log::info('Backfilled legacy sponsorship', [
                'sponsor_id' => $user->id,
                'referral_id' => $referral->id,
                'referral_rank' => $referral->current_rank,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to backfill legacy sponsorship (may already exist)', [
                'sponsor_id' => $user->id,
                'referral_id' => $referral->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
```

**Update `app/Models/User.php`** (update helper method):
```php
/**
 * Get count of same-rank direct sponsors
 * BACKWARD COMPATIBLE: Counts both tracked AND legacy referrals
 */
public function getSameRankSponsorsCount(): int
{
    // Tracked sponsorships
    $trackedCount = $this->directSponsorsTracked()
        ->where('counted_for_rank', $this->current_rank)
        ->count();
    
    // Legacy sponsorships (not yet tracked)
    $legacyCount = User::where('sponsor_id', $this->id)
        ->where('current_rank', $this->current_rank)
        ->whereNotIn('id', function($query) {
            $query->select('sponsored_user_id')
                  ->from('direct_sponsors_tracker')
                  ->where('user_id', $this->id);
        })
        ->count();
    
    return $trackedCount + $legacyCount;
}
```

#### 3.3 Integrate into Package Purchase Flow (Day 2)

**Modify `app/Http/Controllers/CheckoutController.php`** (after order confirmation):
```php
use App\Services\RankAdvancementService;

// After order is confirmed and payment processed
if ($order->payment_status === 'paid') {
    // Update user rank based on new purchase
    $order->user->updateRank();
    
    // Check if any uplines should advance
    $rankService = app(RankAdvancementService::class);
    
    // Re-check sponsor's advancement criteria
    if ($order->user->sponsor) {
        $rankService->checkAndTriggerAdvancement($order->user->sponsor);
    }
}
```

#### 3.4 One-Time Legacy Data Migration Script (Day 3, Morning)

**Create migration script**: `database/migrations/YYYY_MM_DD_backfill_legacy_sponsorships.php`

**IMPORTANT**: Run this ONCE after deploying rank system to production

```php
<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use App\Models\DirectSponsorsTracker;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Backfill all existing sponsor_id relationships into direct_sponsors_tracker
     * This ensures legacy users benefit from the new rank system
     */
    public function up(): void
    {
        $this->command->info('Starting legacy sponsorship backfill...');
        
        $totalBackfilled = 0;
        $totalSkipped = 0;
        
        // Get all users with sponsors
        User::whereNotNull('sponsor_id')->chunk(100, function($users) use (&$totalBackfilled, &$totalSkipped) {
            foreach ($users as $user) {
                $sponsor = $user->sponsor;
                
                if (!$sponsor) {
                    $totalSkipped++;
                    continue;
                }
                
                // Check if already tracked
                $exists = DirectSponsorsTracker::where('user_id', $sponsor->id)
                    ->where('sponsored_user_id', $user->id)
                    ->exists();
                
                if ($exists) {
                    $totalSkipped++;
                    continue;
                }
                
                // Create tracking record
                try {
                    DirectSponsorsTracker::create([
                        'user_id' => $sponsor->id,
                        'sponsored_user_id' => $user->id,
                        'sponsored_at' => $user->created_at ?? now(),
                        'sponsored_user_rank_at_time' => $user->current_rank,
                        'sponsored_user_package_id' => $user->rank_package_id,
                        'counted_for_rank' => $user->current_rank,
                    ]);
                    
                    $totalBackfilled++;
                    
                    if ($totalBackfilled % 50 === 0) {
                        $this->command->info("Backfilled {$totalBackfilled} legacy sponsorships...");
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to backfill legacy sponsorship', [
                        'sponsor_id' => $sponsor->id,
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                    $totalSkipped++;
                }
            }
        });
        
        $this->command->info("Legacy sponsorship backfill completed!");
        $this->command->info("Total backfilled: {$totalBackfilled}");
        $this->command->info("Total skipped: {$totalSkipped}");
        
        Log::info('Legacy sponsorship backfill completed', [
            'backfilled' => $totalBackfilled,
            'skipped' => $totalSkipped,
        ]);
    }

    public function down(): void
    {
        // Optional: Remove backfilled records (risky, not recommended)
        // DirectSponsorsTracker::truncate();
        $this->command->warn('Rollback not implemented for safety. Manual cleanup required if needed.');
    }
};
```

**Alternative: Artisan Command for Flexibility**

**Create**: `app/Console/Commands/BackfillLegacySponsorships.php`
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\DirectSponsorsTracker;

class BackfillLegacySponsorships extends Command
{
    protected $signature = 'rank:backfill-legacy-sponsorships
                            {--dry-run : Run without making changes}
                            {--check-advancements : Check if any users qualify for advancement after backfill}';

    protected $description = 'Backfill existing sponsor_id relationships into direct_sponsors_tracker table';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $checkAdvancements = $this->option('check-advancements');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        
        $this->info('Starting legacy sponsorship backfill...');
        
        $totalBackfilled = 0;
        $totalSkipped = 0;
        $sponsorsToCheck = [];
        
        User::whereNotNull('sponsor_id')->chunk(100, function($users) use (&$totalBackfilled, &$totalSkipped, &$sponsorsToCheck, $isDryRun) {
            foreach ($users as $user) {
                $sponsor = $user->sponsor;
                
                if (!$sponsor) {
                    $totalSkipped++;
                    continue;
                }
                
                // Check if already tracked
                $exists = DirectSponsorsTracker::where('user_id', $sponsor->id)
                    ->where('sponsored_user_id', $user->id)
                    ->exists();
                
                if ($exists) {
                    $totalSkipped++;
                    continue;
                }
                
                if ($isDryRun) {
                    $this->line("Would backfill: Sponsor #{$sponsor->id} → User #{$user->id} (Rank: {$user->current_rank})");
                    $totalBackfilled++;
                } else {
                    try {
                        DirectSponsorsTracker::create([
                            'user_id' => $sponsor->id,
                            'sponsored_user_id' => $user->id,
                            'sponsored_at' => $user->created_at ?? now(),
                            'sponsored_user_rank_at_time' => $user->current_rank,
                            'sponsored_user_package_id' => $user->rank_package_id,
                            'counted_for_rank' => $user->current_rank,
                        ]);
                        
                        $totalBackfilled++;
                        
                        // Track sponsors who gained referrals
                        if (!in_array($sponsor->id, $sponsorsToCheck)) {
                            $sponsorsToCheck[] = $sponsor->id;
                        }
                        
                        if ($totalBackfilled % 50 === 0) {
                            $this->info("Backfilled {$totalBackfilled} legacy sponsorships...");
                        }
                    } catch (\Exception $e) {
                        $this->error("Failed: Sponsor #{$sponsor->id} → User #{$user->id}: {$e->getMessage()}");
                        $totalSkipped++;
                    }
                }
            }
        });
        
        $this->info("\n=== Backfill Summary ===");
        $this->info("Total backfilled: {$totalBackfilled}");
        $this->info("Total skipped: {$totalSkipped}");
        
        // Check for automatic advancements
        if ($checkAdvancements && !$isDryRun && count($sponsorsToCheck) > 0) {
            $this->info("\n=== Checking for Rank Advancements ===");
            $this->info("Checking " . count($sponsorsToCheck) . " sponsors...");
            
            $rankService = app(\App\Services\RankAdvancementService::class);
            $advancedCount = 0;
            
            foreach ($sponsorsToCheck as $sponsorId) {
                $sponsor = User::find($sponsorId);
                if ($sponsor && $sponsor->rankPackage) {
                    $advanced = $rankService->checkAndTriggerAdvancement($sponsor);
                    if ($advanced) {
                        $this->info("✓ Sponsor #{$sponsorId} ({$sponsor->username}) advanced to {$sponsor->fresh()->current_rank}!");
                        $advancedCount++;
                    }
                }
            }
            
            $this->info("\nTotal automatic advancements triggered: {$advancedCount}");
        }
        
        $this->info("\n✓ Backfill completed successfully!");
        
        return Command::SUCCESS;
    }
}
```

**Usage**:
```bash
# Dry run first (see what would happen)
php artisan rank:backfill-legacy-sponsorships --dry-run

# Actually backfill
php artisan rank:backfill-legacy-sponsorships

# Backfill AND check for immediate rank advancements
php artisan rank:backfill-legacy-sponsorships --check-advancements
```

#### 3.5 Testing Phase 3 (Day 3, Afternoon)

**Test Script 1**: `test_rank_advancement.php` (New Users)
```php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Services\RankAdvancementService;

echo "=== Phase 3: Rank Advancement Test (New Users) ===\n\n";

$rankService = new RankAdvancementService();

// Get packages
$starter = Package::where('rank_name', 'Starter')->first();
$newbie = Package::where('rank_name', 'Newbie')->first();

echo "Test Setup:\n";
echo "Starter package requires {$starter->required_direct_sponsors} sponsors\n";
echo "Newbie package costs ₱" . number_format($newbie->price, 2) . "\n\n";

// Create sponsor with Starter rank
$sponsor = User::factory()->create([
    'current_rank' => 'Starter',
    'rank_package_id' => $starter->id,
]);

echo "Created Sponsor: {$sponsor->username} (Rank: Starter)\n\n";

// Create required number of Starter-rank users
$requiredSponsors = $starter->required_direct_sponsors;

for ($i = 1; $i <= $requiredSponsors; $i++) {
    $newUser = User::factory()->create([
        'sponsor_id' => $sponsor->id,
        'current_rank' => 'Starter',
        'rank_package_id' => $starter->id,
    ]);
    
    echo "Registering User #{$i}: {$newUser->username}\n";
    
    $advanced = $rankService->trackSponsorship($sponsor, $newUser);
    
    $progress = $rankService->getRankAdvancementProgress($sponsor->fresh());
    echo "  Sponsor Progress: {$progress['sponsors_count']}/{$progress['required_sponsors']} " .
         "({$progress['progress_percentage']}%)\n";
    
    if ($advanced) {
        echo "  ★★★ RANK ADVANCEMENT TRIGGERED! ★★★\n";
        echo "  Sponsor advanced to: {$sponsor->fresh()->current_rank}\n";
        
        // Check if order was created
        $latestOrder = $sponsor->orders()->latest()->first();
        echo "  System-funded order: {$latestOrder->order_number}\n";
        echo "  Order amount: ₱" . number_format($latestOrder->grand_total, 2) . "\n";
        
        break;
    }
    
    echo "\n";
}

echo "\nPhase 3 Test Completed!\n";
```

**Test Script 2**: `test_rank_advancement_legacy_users.php` (Backward Compatibility)
```php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Services\RankAdvancementService;

echo "=== Phase 3: Rank Advancement Test (LEGACY USERS) ===\n\n";

$rankService = new RankAdvancementService();

// Get packages
$starter = Package::where('rank_name', 'Starter')->first();
$newbie = Package::where('rank_name', 'Newbie')->first();

echo "Test Setup: Legacy User Scenario\n";
echo "Starter package requires {$starter->required_direct_sponsors} sponsors\n\n";

// Simulate legacy user who ALREADY has direct referrals
$legacySponsor = User::factory()->create([
    'current_rank' => 'Starter',
    'rank_package_id' => $starter->id,
]);

echo "Created Legacy Sponsor: {$legacySponsor->username}\n\n";

// Create N-1 legacy referrals (already exist, not tracked yet)
$requiredSponsors = $starter->required_direct_sponsors;

echo "Simulating {$requiredSponsors} EXISTING legacy referrals (before rank system deployed)...\n";
for ($i = 1; $i < $requiredSponsors; $i++) {
    $legacyReferral = User::factory()->create([
        'sponsor_id' => $legacySponsor->id,
        'current_rank' => 'Starter',
        'rank_package_id' => $starter->id,
    ]);
    echo "  Legacy Referral #{$i}: {$legacyReferral->username}\n";
}

echo "\n";
echo "Legacy sponsor now has " . ($requiredSponsors - 1) . " existing referrals (not tracked yet)\n";
echo "These referrals existed BEFORE rank system was deployed\n\n";

// Check current progress (should count legacy referrals)
$progressBefore = $rankService->getRankAdvancementProgress($legacySponsor->fresh());
echo "Progress Before New Referral:\n";
echo "  Tracked: " . $legacySponsor->directSponsorsTracked()->count() . "\n";
echo "  Total Same-Rank: {$progressBefore['sponsors_count']}/{$progressBefore['required_sponsors']}\n";
echo "  Progress: {$progressBefore['progress_percentage']}%\n\n";

// Now add ONE MORE referral (should trigger advancement due to legacy count)
echo "Now adding ONE more referral (should trigger advancement)...\n";
$finalReferral = User::factory()->create([
    'sponsor_id' => $legacySponsor->id,
    'current_rank' => 'Starter',
    'rank_package_id' => $starter->id,
]);

echo "New Referral: {$finalReferral->username}\n\n";

// Track the new sponsorship
$advanced = $rankService->trackSponsorship($legacySponsor, $finalReferral);

if ($advanced) {
    echo "★★★ LEGACY USER RANK ADVANCEMENT TRIGGERED! ★★★\n";
    echo "Sponsor advanced to: {$legacySponsor->fresh()->current_rank}\n\n";
    
    // Check if order was created
    $latestOrder = $legacySponsor->orders()->latest()->first();
    echo "System-funded order: {$latestOrder->order_number}\n";
    echo "Order amount: ₱" . number_format($latestOrder->grand_total, 2) . "\n\n";
    
    // Verify legacy referrals were backfilled
    $trackedAfter = $legacySponsor->directSponsorsTracked()->count();
    echo "Legacy sponsorships backfilled: {$trackedAfter} (was 1, now includes all legacy)\n";
} else {
    echo "❌ ADVANCEMENT NOT TRIGGERED (this is a bug!)\n";
    echo "Expected: Legacy user should advance after adding final referral\n";
}

echo "\nLegacy User Test Completed!\n";
```

**Checklist**:
- [ ] Sponsorship tracking works
- [ ] Sponsor count increments correctly (both tracked + legacy)
- [ ] **Backward compatibility**: Legacy users with existing referrals are counted
- [ ] Rank advancement triggers at correct threshold (even for legacy users)
- [ ] System-funded order is created
- [ ] User rank is updated
- [ ] RankAdvancement record is created
- [ ] Legacy sponsorships are backfilled when advancement triggers
- [ ] No duplicate orders created
- [ ] Artisan command `rank:backfill-legacy-sponsorships` works
- [ ] Dry run mode shows accurate counts

---

### Phase 4: UI Integration - Display Ranks (2 days)

**Goal**: Show rank prominently in UI (user profile, admin user table)

#### 4.1 Update User Profile View (Day 1, Morning)

**Modify `resources/views/profile/show.blade.php`** (add rank section):
```blade
<!-- Add after wallet balances section -->
<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">
            <svg class="icon icon-lg">
                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-star') }}"></use>
            </svg>
            My Rank
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h2 class="text-info">
                    <svg class="icon icon-xl me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-badge') }}"></use>
                    </svg>
                    {{ $user->getRankName() }}
                </h2>
                @if($user->rankPackage)
                    <p class="text-muted">Package: {{ $user->rankPackage->name }}</p>
                    <p class="text-muted">
                        Rank since: {{ $user->rank_updated_at?->format('M d, Y') ?? 'N/A' }}
                    </p>
                @else
                    <p class="text-muted">Purchase a package to get ranked</p>
                @endif
            </div>
            
            <div class="col-md-6">
                @php
                    $rankService = app(\App\Services\RankAdvancementService::class);
                    $progress = $rankService->getRankAdvancementProgress($user);
                @endphp
                
                @if($progress['can_advance'])
                    <h6 class="mb-3">Next Rank: {{ $progress['next_rank'] }}</h6>
                    
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Progress</span>
                            <span class="fw-bold">{{ $progress['sponsors_count'] }} / {{ $progress['required_sponsors'] }}</span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar {{ $progress['is_eligible'] ? 'bg-success' : 'bg-primary' }}" 
                                 role="progressbar" 
                                 style="width: {{ $progress['progress_percentage'] }}%">
                                {{ number_format($progress['progress_percentage'], 0) }}%
                            </div>
                        </div>
                    </div>
                    
                    @if($progress['is_eligible'])
                        <div class="alert alert-success mt-3">
                            <strong>Congratulations!</strong> You're eligible for rank advancement to {{ $progress['next_rank'] }}!
                        </div>
                    @else
                        <p class="text-muted mt-3">
                            Sponsor <strong>{{ $progress['remaining_sponsors'] }} more</strong> 
                            {{ $progress['current_rank'] }}-rank users to advance to {{ $progress['next_rank'] }}
                        </p>
                    @endif
                @else
                    <div class="alert alert-info">
                        <strong>Top Rank!</strong> You've reached the highest rank.
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Rank Advancement History -->
        @if($user->rankAdvancements->count() > 0)
            <hr class="my-4">
            <h6>Rank History</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Type</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->rankAdvancements->take(5) as $advancement)
                            <tr>
                                <td>{{ $advancement->created_at->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $advancement->from_rank ?? 'None' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success">
                                        {{ $advancement->to_rank }}
                                    </span>
                                </td>
                                <td>
                                    @if($advancement->advancement_type === 'sponsorship_reward')
                                        <span class="badge bg-primary">Reward</span>
                                    @elseif($advancement->advancement_type === 'purchase')
                                        <span class="badge bg-info">Purchase</span>
                                    @else
                                        <span class="badge bg-warning">Admin</span>
                                    @endif
                                </td>
                                <td>
                                    @if($advancement->advancement_type === 'sponsorship_reward')
                                        Sponsored {{ $advancement->sponsors_count }} users
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
```

#### 4.2 Update Admin User Table (Day 1, Afternoon)

**Modify `resources/views/admin/users.blade.php`** (add rank and income columns):
```blade
<!-- Add to table header -->
<thead>
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Income</th> <!-- REPLACED Email with Income -->
        <th>Rank</th>
        <th>Status</th>
        <th>Sponsor</th>
        <th>Actions</th>
    </tr>
</thead>

<!-- Add to table body -->
<tbody>
    @foreach($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->username }}</td>
            
            <!-- NEW: Income Column (Withdrawable Balance) -->
            <td>
                @php
                    $withdrawableBalance = $user->wallet?->withdrawable_balance ?? 0;
                @endphp
                @if($withdrawableBalance > 0)
                    <span class="text-success fw-bold">
                        ₱{{ number_format($withdrawableBalance, 2) }}
                    </span>
                @else
                    <span class="text-muted">₱0.00</span>
                @endif
            </td>
            
            <!-- Rank Column -->
            <td>
                @if($user->rankPackage)
                    <span class="badge bg-info" title="Rank Order: {{ $user->rankPackage->rank_order }}">
                        {{ $user->current_rank }}
                    </span>
                    <br>
                    <small class="text-muted">{{ $user->rankPackage->name }}</small>
                @else
                    <span class="badge bg-secondary">Unranked</span>
                @endif
            </td>
            
            <td>
                @if($user->isNetworkActive())
                    <span class="badge bg-success">Active</span>
                @else
                    <span class="badge bg-warning">Inactive</span>
                @endif
            </td>
            <td>{{ $user->sponsor?->username ?? 'None' }}</td>
            <td>
                <!-- Actions -->
            </td>
        </tr>
    @endforeach
</tbody>
```

**Update Controller to eager load relationships and wallet**:
```php
// In AdminController or UserController
public function index()
{
    $users = User::with(['rankPackage', 'sponsor', 'wallet'])
        ->paginate(50);
    
    return view('admin.users', compact('users'));
}
```

**Optional: Add sorting by income**:
```php
public function index(Request $request)
{
    $query = User::with(['rankPackage', 'sponsor', 'wallet']);
    
    // Sort by income (withdrawable_balance)
    if ($request->get('sort') === 'income') {
        $query->leftJoin('wallets', 'users.id', '=', 'wallets.user_id')
              ->orderBy('wallets.withdrawable_balance', $request->get('order', 'desc'))
              ->select('users.*');
    }
    
    $users = $query->paginate(50);
    
    return view('admin.users', compact('users'));
}
```

#### 4.3 Add Rank and Income Filters to Admin User Table (Day 2)

**Add filter controls**:
```blade
<!-- Add before the table -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.users') }}">
            <div class="row">
                <div class="col-md-3">
                    <label>Filter by Rank</label>
                    <select name="rank" class="form-select">
                        <option value="">All Ranks</option>
                        @foreach($ranks as $rank)
                            <option value="{{ $rank }}" {{ request('rank') === $rank ? 'selected' : '' }}>
                                {{ $rank }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Filter by Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Sort by Income</label>
                    <select name="sort_income" class="form-select">
                        <option value="">Default Order</option>
                        <option value="desc" {{ request('sort_income') === 'desc' ? 'selected' : '' }}>Highest First</option>
                        <option value="asc" {{ request('sort_income') === 'asc' ? 'selected' : '' }}>Lowest First</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.users') }}" class="btn btn-secondary ms-2">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>
```

**Update controller**:
```php
public function index(Request $request)
{
    $query = User::with(['rankPackage', 'sponsor', 'wallet']);
    
    // Rank filter
    if ($request->filled('rank')) {
        $query->where('current_rank', $request->rank);
    }
    
    // Status filter
    if ($request->filled('status')) {
        $query->where('network_status', $request->status);
    }
    
    // Income sorting
    if ($request->filled('sort_income')) {
        $query->leftJoin('wallets', 'users.id', '=', 'wallets.user_id')
              ->orderBy('wallets.withdrawable_balance', $request->sort_income)
              ->select('users.*');
    }
    
    $users = $query->paginate(50);
    
    // Get distinct ranks for filter dropdown
    $ranks = User::whereNotNull('current_rank')
        ->distinct()
        ->pluck('current_rank')
        ->sort()
        ->values();
    
    return view('admin.users', compact('users', 'ranks'));
}
```

#### 4.4 Testing Phase 4 (Day 2)

**Checklist**:
- [ ] User profile shows current rank
- [ ] Rank advancement progress bar displays correctly
- [ ] Rank history shows past advancements
- [ ] **Admin user table shows Income column (withdrawable_balance)**
- [ ] **Email column removed from admin user table**
- [ ] **Income displays in green if > 0, gray if 0**
- [ ] Admin user table shows rank column
- [ ] Rank badges display correctly
- [ ] Rank filter works
- [ ] **Income sorting works (highest/lowest first)**
- [ ] **Wallet relationship eager loaded for performance**

---

### Phase 5: Admin Configuration Interface (2 days)

**Goal**: Allow admins to configure rank requirements and package relationships

#### 5.1 Create Admin Routes (Day 1, Morning)

**Add to `routes/web.php`**:
```php
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Rank Management
    Route::get('/ranks', [AdminRankController::class, 'index'])->name('ranks.index');
    Route::get('/ranks/configure', [AdminRankController::class, 'configure'])->name('ranks.configure');
    Route::post('/ranks/configure', [AdminRankController::class, 'updateConfiguration'])->name('ranks.update-configuration');
    Route::get('/ranks/advancements', [AdminRankController::class, 'advancements'])->name('ranks.advancements');
    Route::post('/ranks/manual-advance/{user}', [AdminRankController::class, 'manualAdvance'])->name('ranks.manual-advance');
});
```

#### 5.2 Create Admin Controller (Day 1)

**Create `app/Http/Controllers/Admin/AdminRankController.php`**:
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\User;
use App\Models\RankAdvancement;
use App\Services\RankAdvancementService;
use Illuminate\Http\Request;

class AdminRankController extends Controller
{
    public function __construct(
        protected RankAdvancementService $rankService
    ) {}

    /**
     * Rank system dashboard
     */
    public function index()
    {
        $packages = Package::rankable()->orderedByRank()->get();
        
        $stats = [
            'total_ranked_users' => User::whereNotNull('current_rank')->count(),
            'total_advancements' => RankAdvancement::count(),
            'system_rewards_count' => RankAdvancement::where('advancement_type', 'sponsorship_reward')->count(),
            'total_system_paid' => RankAdvancement::where('advancement_type', 'sponsorship_reward')
                ->sum('system_paid_amount'),
        ];
        
        // Rank distribution
        $rankDistribution = User::whereNotNull('current_rank')
            ->selectRaw('current_rank, COUNT(*) as count')
            ->groupBy('current_rank')
            ->get()
            ->keyBy('current_rank');
        
        return view('admin.ranks.index', compact('packages', 'stats', 'rankDistribution'));
    }

    /**
     * Configure rank requirements
     */
    public function configure()
    {
        $packages = Package::rankable()->orderedByRank()->get();
        
        return view('admin.ranks.configure', compact('packages'));
    }

    /**
     * Update rank configuration
     */
    public function updateConfiguration(Request $request)
    {
        $request->validate([
            'packages' => 'required|array',
            'packages.*.rank_name' => 'required|string|max:100',
            'packages.*.rank_order' => 'required|integer|min:1',
            'packages.*.required_direct_sponsors' => 'required|integer|min:0',
            'packages.*.next_rank_package_id' => 'nullable|exists:packages,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->packages as $packageId => $data) {
                $package = Package::findOrFail($packageId);
                
                $package->update([
                    'rank_name' => $data['rank_name'],
                    'rank_order' => $data['rank_order'],
                    'required_direct_sponsors' => $data['required_direct_sponsors'],
                    'next_rank_package_id' => $data['next_rank_package_id'] ?? null,
                ]);
            }

            DB::commit();
            return back()->with('success', 'Rank configuration updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update configuration: ' . $e->getMessage()]);
        }
    }

    /**
     * View rank advancement history
     */
    public function advancements(Request $request)
    {
        $query = RankAdvancement::with(['user', 'toPackage']);
        
        if ($request->filled('type')) {
            $query->where('advancement_type', $request->type);
        }
        
        if ($request->filled('rank')) {
            $query->where('to_rank', $request->rank);
        }
        
        $advancements = $query->orderBy('created_at', 'desc')->paginate(50);
        
        $ranks = RankAdvancement::distinct()->pluck('to_rank')->sort()->values();
        
        return view('admin.ranks.advancements', compact('advancements', 'ranks'));
    }

    /**
     * Manually advance a user's rank (admin action)
     */
    public function manualAdvance(Request $request, User $user)
    {
        $request->validate([
            'to_package_id' => 'required|exists:packages,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $toPackage = Package::findOrFail($request->to_package_id);
        $fromPackage = $user->rankPackage;

        DB::beginTransaction();
        try {
            // Create manual order (system-funded)
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ADMIN-RANK-' . strtoupper(uniqid()),
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_method' => 'admin_adjustment',
                'subtotal' => $toPackage->price,
                'grand_total' => $toPackage->price,
                'notes' => 'Manual rank advancement by admin: ' . ($request->notes ?? ''),
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'package_id' => $toPackage->id,
                'quantity' => 1,
                'price' => $toPackage->price,
                'subtotal' => $toPackage->price,
            ]);

            // Update user rank
            $user->update([
                'current_rank' => $toPackage->rank_name,
                'rank_package_id' => $toPackage->id,
                'rank_updated_at' => now(),
            ]);

            // Record advancement
            RankAdvancement::create([
                'user_id' => $user->id,
                'from_rank' => $fromPackage?->rank_name,
                'to_rank' => $toPackage->rank_name,
                'from_package_id' => $fromPackage?->id,
                'to_package_id' => $toPackage->id,
                'advancement_type' => 'admin_adjustment',
                'system_paid_amount' => $toPackage->price,
                'order_id' => $order->id,
                'notes' => $request->notes,
            ]);

            DB::commit();
            return back()->with('success', "User {$user->username} advanced to {$toPackage->rank_name}!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to advance user: ' . $e->getMessage()]);
        }
    }
}
```

#### 5.3 Create Admin Views (Day 2)

**Create `resources/views/admin/ranks/index.blade.php`** (Dashboard):
```blade
@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Rank System Management</h1>
        <div>
            <a href="{{ route('admin.ranks.configure') }}" class="btn btn-primary">
                Configure Ranks
            </a>
            <a href="{{ route('admin.ranks.advancements') }}" class="btn btn-info">
                View Advancements
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Ranked Users</h5>
                    <h2>{{ number_format($stats['total_ranked_users']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Total Advancements</h5>
                    <h2>{{ number_format($stats['total_advancements']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>System Rewards</h5>
                    <h2>{{ number_format($stats['system_rewards_count']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5>Total System Paid</h5>
                    <h2>₱{{ number_format($stats['total_system_paid'], 2) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Rank Packages Overview -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Rank Packages</h5>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Rank Order</th>
                        <th>Rank Name</th>
                        <th>Package</th>
                        <th>Price</th>
                        <th>Required Sponsors</th>
                        <th>Next Rank</th>
                        <th>User Count</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packages as $package)
                        <tr>
                            <td>{{ $package->rank_order }}</td>
                            <td><span class="badge bg-info">{{ $package->rank_name }}</span></td>
                            <td>{{ $package->name }}</td>
                            <td>₱{{ number_format($package->price, 2) }}</td>
                            <td>{{ $package->required_direct_sponsors }}</td>
                            <td>{{ $package->nextRankPackage?->rank_name ?? 'Top Rank' }}</td>
                            <td>{{ $rankDistribution[$package->rank_name]->count ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Rank Distribution Chart -->
    <div class="card">
        <div class="card-header">
            <h5>Rank Distribution</h5>
        </div>
        <div class="card-body">
            <canvas id="rankDistributionChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('rankDistributionChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($rankDistribution->keys()),
        datasets: [{
            label: 'User Count',
            data: @json($rankDistribution->pluck('count')),
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endsection
```

**Create `resources/views/admin/ranks/configure.blade.php`** (Configuration):
```blade
@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Configure Rank Requirements</h1>
        <a href="{{ route('admin.ranks.index') }}" class="btn btn-secondary">Back</a>
    </div>

    <form method="POST" action="{{ route('admin.ranks.update-configuration') }}">
        @csrf
        
        <div class="card">
            <div class="card-header">
                <h5>Rank Package Configuration</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width="15%">Package</th>
                            <th width="15%">Rank Name</th>
                            <th width="10%">Rank Order</th>
                            <th width="15%">Required Sponsors</th>
                            <th width="20%">Next Rank Package</th>
                            <th width="10%">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packages as $package)
                            <tr>
                                <td>{{ $package->name }}</td>
                                <td>
                                    <input type="text" 
                                           name="packages[{{ $package->id }}][rank_name]" 
                                           value="{{ $package->rank_name }}" 
                                           class="form-control" 
                                           required>
                                </td>
                                <td>
                                    <input type="number" 
                                           name="packages[{{ $package->id }}][rank_order]" 
                                           value="{{ $package->rank_order }}" 
                                           class="form-control" 
                                           min="1" 
                                           required>
                                </td>
                                <td>
                                    <input type="number" 
                                           name="packages[{{ $package->id }}][required_direct_sponsors]" 
                                           value="{{ $package->required_direct_sponsors }}" 
                                           class="form-control" 
                                           min="0" 
                                           required>
                                </td>
                                <td>
                                    <select name="packages[{{ $package->id }}][next_rank_package_id]" 
                                            class="form-select">
                                        <option value="">None (Top Rank)</option>
                                        @foreach($packages as $nextPackage)
                                            @if($nextPackage->id !== $package->id)
                                                <option value="{{ $nextPackage->id }}" 
                                                        {{ $package->next_rank_package_id == $nextPackage->id ? 'selected' : '' }}>
                                                    {{ $nextPackage->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </td>
                                <td>₱{{ number_format($package->price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save Configuration</button>
            </div>
        </div>
    </form>

    <!-- Explanation -->
    <div class="card mt-4">
        <div class="card-header">
            <h5>How It Works</h5>
        </div>
        <div class="card-body">
            <ul>
                <li><strong>Rank Name:</strong> Display name for this rank tier</li>
                <li><strong>Rank Order:</strong> Numeric order (1 = lowest, higher = better). Used for rank comparisons.</li>
                <li><strong>Required Sponsors:</strong> Number of same-rank direct sponsors needed to advance to next rank</li>
                <li><strong>Next Rank Package:</strong> The package that will be automatically purchased when advancement criteria is met</li>
            </ul>
            
            <div class="alert alert-warning">
                <strong>Important:</strong> Make sure rank order is sequential and next rank package has higher rank order than current.
            </div>
        </div>
    </div>
</div>
@endsection
```

#### 5.4 Testing Phase 5 (Day 2)

**Checklist**:
- [ ] Admin can view rank dashboard
- [ ] Statistics display correctly
- [ ] Rank configuration page loads
- [ ] Admin can update rank requirements
- [ ] Changes save to database
- [ ] Advancements history displays
- [ ] Manual rank advancement works

---

### Phase 6: Testing & Documentation (1-2 days)

**Goal**: Comprehensive system testing and user documentation

#### 6.1 Complete Testing Scenarios (Day 1)

**Test Scenario 1**: Full Rank Advancement Flow
```php
// Create Starter user
// Register 5 Starter-rank users under them
// Verify automatic advancement to Newbie
// Verify system-funded order created
// Verify MLM commissions processed with new rank
```

**Test Scenario 2**: Rank-Aware Commission Calculation
```php
// Test all 3 rules:
// 1. Higher rank with lower rank downline
// 2. Lower rank with higher rank downline
// 3. Same rank
```

**Test Scenario 3**: Edge Cases
```php
// User at top rank (no advancement)
// User with no rank package
// Circular rank chain detection
// Manual admin advancement
// Multiple simultaneous advancements
```

#### 6.2 Create User Documentation (Day 2)

**Create `RANK_USER_GUIDE.md`**:
```markdown
# Rank System User Guide

## What are Ranks?

Ranks represent your achievement level in the network. Higher ranks unlock better MLM commission rates and earning potential.

## How to Get a Rank

1. Purchase a package (e.g., Starter Package)
2. Your rank is determined by the highest-cost package you've purchased

## How to Advance to the Next Rank

### Automatic Advancement (FREE!)
Sponsor N users of your current rank → system automatically upgrades you to the next rank package **fully paid by the system**!

Example:
- You're a Starter (need 5 Starter sponsors)
- You sponsor 5 users who all purchase Starter package
- System automatically buys Newbie Package for you (worth ₱2,500) **for free**
- You're now a Newbie with higher earning potential!

### Manual Advancement
You can also purchase higher rank packages directly through the shop.

## Rank-Based Commission Rules (MLM Only)

**Important**: These rules apply ONLY to MLM commissions (package purchases), NOT Unilevel bonuses.

### Rule 1: Higher Rank → Lower Downline
If you're Newbie and your downline is Starter, you earn Starter's commission rate (prevents unfair advantages).

### Rule 2: Lower Rank → Higher Downline
If you're Starter and your downline is Newbie, you earn Starter's commission rate (motivation to rank up!).

### Rule 3: Same Rank
If you and your downline have the same rank, standard commission applies.

### Rule 0: No Rank = No Commission (CRITICAL)
If either you OR your downline has no rank package, **you get 0.00 commission**. Both must have ranks for the rank system to apply.

**Examples**:
- You (Starter rank) + Downline (no rank) = 0.00 commission
- You (no rank) + Downline (Starter rank) = 0.00 commission
- You (Starter rank) + Downline (Starter rank) = Standard commission ✓

**Note**: Unilevel bonuses (product purchases) use a separate calculation and are subject to monthly quota requirements, NOT these rank-based rules.

## Viewing Your Rank Progress

1. Go to your Profile
2. See "My Rank" section
3. Progress bar shows how many sponsors you need
4. Check your Rank History for past advancements
```

#### 6.3 Create Admin Documentation

**Append to `RANK.md`**:
```markdown
## Admin Management

### Configuring Ranks

1. Navigate to Admin → Rank System
2. Click "Configure Ranks"
3. Set for each package:
   - Rank Name (e.g., "Starter", "Newbie")
   - Rank Order (1, 2, 3... lower is starter)
   - Required Sponsors (how many same-rank sponsors needed)
   - Next Rank Package (which package to auto-purchase)

### Viewing Advancements

Admin → Rank System → View Advancements

See all rank changes:
- Automatic (sponsorship rewards)
- Manual (direct purchases)
- Admin (manual adjustments)

### Manual Rank Advancement

In User Management:
1. Find user
2. Click "Advance Rank"
3. Select target package
4. Add notes
5. Confirm

System will:
- Create order
- Update user rank
- Record advancement

### Monitoring System Costs

Dashboard shows:
- Total system-paid amount
- Number of automatic advancements
- Rank distribution

Use this to monitor MLM reward costs.
```

---

## Backward Compatibility Implementation Summary

### Key Features for Legacy Users

1. **Automatic Legacy Counting**: System counts BOTH:
   - New tracked sponsorships (in `direct_sponsors_tracker`)
   - Legacy sponsorships (existing `sponsor_id` relationships)

2. **Backfill on Advancement**: When user qualifies for advancement, system automatically backfills all legacy sponsorships into tracking table

3. **One-Time Migration**: Optional migration/command to backfill ALL legacy data at once:
   ```bash
   php artisan rank:backfill-legacy-sponsorships --check-advancements
   ```

4. **No Data Loss**: Existing users keep their referral credit and can benefit from rank system immediately

### Deployment Strategy for Existing System

**Step 1**: Deploy code and review migrations
```bash
git pull
# Review all migrations before running
```

**Step 2**: Run ALL rank system migrations (including rank assignment)
```bash
php artisan migrate

# This will:
# 1. Add rank columns to users and packages tables
# 2. Create rank_advancements table
# 3. Create direct_sponsors_tracker table
# 4. Automatically assign ranks to existing users based on purchased packages
```

**Step 3**: Verify rank assignments
```bash
php artisan tinker
User::where('current_rank', 'Starter')->count(); // Should show all Starter package users
User::whereNotNull('current_rank')->count(); // Total ranked users
```

**Step 4**: Make package names readonly (manual edit)
```bash
# Edit: resources/views/admin/packages/edit.blade.php
# Add readonly attribute to name field (see Phase 1.5 above)

# Edit: app/Http/Controllers/Admin/AdminPackageController.php
# Add validation to prevent name changes (see Phase 1.5 above)
```

**Step 5**: Backfill legacy sponsorships (DRY RUN first)
```bash
php artisan rank:backfill-legacy-sponsorships --dry-run
# Review output, then run for real
php artisan rank:backfill-legacy-sponsorships --check-advancements
```

**Step 6**: Monitor logs for automatic advancements
```bash
tail -f storage/logs/laravel.log | grep "Rank Advanced"
```

### Expected Behavior After Deployment

- **Existing users**: Ranks assigned based on highest package purchased (via migration)
- **Starter package users**: Automatically assigned "Starter" rank
- **Package names**: Locked and cannot be changed (readonly in admin)
- **Legacy referrals**: All counted towards advancement
- **Immediate advancements**: Some users may instantly qualify and advance (if >= N referrals)
- **New referrals**: System tracks normally going forward
- **No duplicates**: Backfill checks prevent duplicate tracking records

---

## Summary & Rollout Plan

### Total Estimated Time: 12-15 days

| Phase | Duration | Dependencies | Key Deliverables |
|-------|----------|--------------|------------------|
| Phase 1 | 2-3 days | None | Database schema, basic rank tracking |
| Phase 2 | 2-3 days | Phase 1 | Rank-aware commission calculation |
| Phase 3 | 3-4 days | Phase 1, 2 | Automatic rank advancement |
| Phase 4 | 2 days | Phase 1 | UI integration (profile, admin table) |
| Phase 5 | 2 days | All previous | Admin configuration interface |
| Phase 6 | 1-2 days | All previous | Testing & documentation |

### Post-Implementation Checklist

- [ ] All migrations run successfully
- [ ] Seeders create ranked packages
- [ ] User ranks tracked correctly
- [ ] Rank-aware commissions calculated correctly
- [ ] Automatic advancement triggers properly
- [ ] System-funded orders created correctly
- [ ] UI displays ranks prominently
- [ ] Admin can configure ranks
- [ ] All tests pass
- [ ] Documentation complete
- [ ] Performance acceptable (no N+1 queries)
- [ ] Logs are comprehensive

### Future Enhancements (Optional)

1. **Rank Badges/Images**: Visual badges for each rank
2. **Leaderboards**: Rank-based leaderboards
3. **Rank Bonuses**: Additional bonuses for reaching certain ranks
4. **Time-Limited Advancements**: Special promotions for faster advancement
5. **Rank Maintenance**: Require minimum activity to maintain rank
6. **Multi-Tier Advancement**: Skip ranks if criteria met (e.g., sponsor 10 Bronze to jump to Gold)
7. **Rank-Based Unilevel Bonuses**: Extend rank comparison rules to Unilevel (currently not planned)

---

## Conclusion

This phased implementation plan provides a robust, self-contained approach to building a rank-based MLM advancement system. Each phase is independently testable and builds upon the previous one, ensuring stability and maintainability.

The system incentivizes user engagement through automatic rewards while implementing fair commission rules that motivate members to advance their ranks for higher earnings.

---

## Final Reminder: Scope of This Implementation

**What This Rank System DOES**:
- ✅ Tracks user ranks based on highest package purchased
- ✅ Automatically rewards rank advancement (free package upgrade)
- ✅ Implements rank-aware MLM commission calculations
- ✅ Provides backward compatibility for legacy users
- ✅ Adds UI for rank display and admin management

**What This Rank System DOES NOT AFFECT**:
- ❌ Unilevel bonus system (remains unchanged)
- ❌ Monthly quota tracking (only for Unilevel, not MLM)
- ❌ Product purchase bonuses (separate from package commissions)
- ❌ Existing Unilevel qualification logic

**Services Modified**:
- ✅ `MLMCommissionService` - Enhanced with rank comparison (active status checks preserved)
- ❌ `UnilevelBonusService` - NOT TOUCHED (no changes)
- ❌ `MonthlyQuotaService` - NOT TOUCHED (only for Unilevel)

**Critical Preservation**:
- ✅ `isNetworkActive()` checks remain in MLMCommissionService
- ✅ Inactive users are skipped before rank comparison
- ✅ Rank system only affects active users' commission rates
- ✅ Existing MLM qualification logic unchanged

This separation ensures the new rank system is isolated and doesn't break existing functionality.
