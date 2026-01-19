# GAWIS2 Ranking System Analysis Report

## Current State

### Rank Structure (7-Tier Hierarchy)

| Rank | Package | Price | Required Sponsors | Rank Reward | MLM Commission Levels (L1→L5) |
|------|---------|-------|-------------------|-------------|-------------------------------|
| **Starter** | Starter | ₱1,000 | 2 | ₱0.00 | ₱200, ₱50, ₱50, ₱50, ₱50 |
| **Newbie** | Newbie | ₱1,798 | 2 | ₱500.00 | ₱500, ₱250, ₱200, ₱150, ₱75 |
| **1 Star** | 1 Star | ₱3,798 | 2 | ₱1,000.00 | ₱1,000, ₱500, ₱400, ₱300, ₱150 |
| **2 Star** | 2 Star | ₱6,798 | 2 | ₱2,000.00 | ₱1,800, ₱900, ₱720, ₱540, ₱270 |
| **3 Star** | 3 Star | ₱12,798 | 2 | ₱4,000.00 | ₱3,400, ₱1,700, ₱1,360, ₱1,020, ₱510 |
| **4 Star** | 4 Star | ₱18,798 | 2 | ₱10,000.00 | ₱5,400, ₱2,800, ₱2,240, ₱1,680, ₱840 |
| **5 Star** | 5 Star | ₱48,798 | 2 (top rank) | ₱20,000.00 | ₱13,000, ₱6,600, ₱5,280, ₱3,960, ₱1,980 |

**Key Observations:**
- **Uniform advancement requirement:** All ranks require **2** direct sponsors of the same rank to advance
- **Exponential growth:** Package prices and MLM commissions increase significantly with each rank
- **Rank rewards:** Only Starter has no reward; all other ranks provide increasing cash rewards on advancement
- **5 Star is the top rank:** No further advancement possible

### Database Schema

**Users Table** (`users`):
- `current_rank` (string, nullable) - Current rank name
- `rank_package_id` (unsignedBigInteger, nullable) - FK to packages
- `rank_updated_at` (timestamp, nullable) - Last rank update time

**Packages Table** (`packages`):
- `rank_name` - Display name (Starter, Newbie, 1 Star, etc.)
- `rank_order` - Sort order (1-7)
- `required_direct_sponsors` - Sponsors needed to advance (currently all set to 2)
- `is_rankable` - Whether this package provides a rank
- `next_rank_package_id` - FK to next rank package
- `rank_reward` - Cash reward given on advancement

**Rank Advancements Table** (`rank_advancements`):
- Tracks all rank changes
- Records advancement type: `purchase`, `sponsorship_reward`, `admin_adjustment`

**Direct Sponsors Tracker** (`direct_sponsors_tracker`):
- Tracks sponsorships with timestamp
- Records sponsored user's rank at time of sponsorship
- `counted_for_rank` field indicates which rank this sponsorship counted towards

---

## How Next Rank is Achieved

### Advancement Requirements

**All ranks require 2 direct sponsors of the same rank to advance:**

| Current Rank | To Advance To | Required Sponsors | Reward |
|--------------|----------------|-------------------|--------|
| Starter → Newbie | Sponsor **2** Starter-rank users | ₱500.00 |
| Newbie → 1 Star | Sponsor **2** Newbie-rank users | ₱1,000.00 |
| 1 Star → 2 Star | Sponsor **2** 1 Star-rank users | ₱2,000.00 |
| 2 Star → 3 Star | Sponsor **2** 2 Star-rank users | ₱4,000.00 |
| 3 Star → 4 Star | Sponsor **2** 3 Star-rank users | ₱10,000.00 |
| 4 Star → 5 Star | Sponsor **2** 4 Star-rank users | ₱20,000.00 |

**5 Star is the top rank** - no further advancement available.

### Advancement Process

1. **Sponsorship Tracking** (`RankAdvancementService.php:24-59`)
   - When a new user purchases a package, their rank is set
   - Sponsorship is recorded in `direct_sponsors_tracker`
   - System checks if sponsor qualifies for rank advancement

2. **Advancement Criteria Check** (`RankAdvancementService.php:68-125`)
   - Counts same-rank sponsors from **both sources**:
     - **Tracked sponsorships** (new system)
     - **Legacy referrals** (existing sponsor_id relationships, backward compatible)
   - If `total_same_rank_sponsors >= required_direct_sponsors` (currently 2) → **ADVANCE**

3. **Automatic Advancement** (`RankAdvancementService.php:177-303`)
   - Creates **system-funded order** (user pays nothing)
   - Updates user's rank to next tier
   - **Credits rank reward** to wallet (both `mlm_balance` + `withdrawable_balance`)
   - Activates network status if not already active
   - Records advancement in `rank_advancements` table
   - Triggers MLM commissions for the system-funded order (uplines get rewarded)

4. **Admin Manual Override** (`AdminRankController.php:157-269`)
   - Admins can manually advance any user
   - Creates admin-funded order
   - Same wallet credit and tracking as automatic advancement

---

## Rank Comparison for MLM Commissions

### Commission Calculation Rules (`RankComparisonService.php:30-107`)

**Rule 1: Higher rank upline with lower rank buyer**
- Upline gets **buyer's lower commission rate** (prevents unfair advantage)

**Rule 2: Lower rank upline with higher rank buyer**
- Upline gets **their own lower commission rate** (motivation to rank up)

**Rule 3: Same rank**
- Standard commission applies

**Critical**: Both upline AND buyer MUST have ranks for rank-based commission to apply. If either lacks a rank package → **no commission**.

### Commission Progression Example

When a **5 Star** member purchases a package:
- L1 upline (any rank): ₱13,000 (unless rank comparison rules modify)
- L2 upline: ₱6,600
- L3 upline: ₱5,280
- L4 upline: ₱3,960
- L5 upline: ₱1,980

Total commission distributed: ₱30,820

When a **Starter** member purchases a package:
- L1 upline (any rank): ₱200 (unless rank comparison rules modify)
- L2 upline: ₱50
- L3 upline: ₱50
- L4 upline: ₱50
- L5 upline: ₱50

Total commission distributed: ₱400

---

## Key Components

### Services
- `RankAdvancementService` - Handles automatic advancement logic
- `RankComparisonService` - Determines effective commission rates based on rank

### Models
- `RankAdvancement` - Records rank history
- `DirectSponsorsTracker` - Tracks sponsor relationships
- `Package` - Defines rank tiers and requirements

### Admin Interface
- `/admin/ranks` - Dashboard with rank distribution
- `/admin/ranks/configure` - Configure rank requirements
- `/admin/ranks/advancements` - View advancement history
- Manual rank advancement per user

---

## Notable Features

1. **Backward Compatibility** - Counts legacy sponsorships alongside tracked ones
2. **System-Funded Advancement** - Users get free package upgrades
3. **Wallet Rewards** - Automatic cash credit on rank advancement (₱500 to ₱20,000)
4. **MLM Commissions Triggered** - Uplines earn from system-funded advancement orders
5. **Progress Tracking** - Dashboard shows advancement progress (sponsors vs. required)
6. **Uniform Requirements** - All ranks require 2 same-rank sponsors (easily configurable)
7. **Exponential Growth** - Both package prices and commissions scale dramatically with rank level
