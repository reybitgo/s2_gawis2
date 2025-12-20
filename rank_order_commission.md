# Rank-Order Commission: Allow system-funded rank orders to trigger rank-up diff commissions

## Overview

This document defines a phased, testable plan to allow system-funded rank advancement orders (payment_method = `system_reward`) to trigger one-time commission payouts to uplines equal to the RATE DIFFERENCE for each eligible MLM level. The payout rule differs from standard order commissions: only the delta between the new rank's commission rate and the downline's previous rank rate is credited (if positive).

## Goals

-   When a user advances from `from_rank` (package A) to `to_rank` (package B) via a system-funded order, traverse their upline and credit each network-active eligible upline the positive difference per level: max(0, rate_B(level) - rate_A(level)).
-   Keep behavior opt-in via config flag `mlm.rank_up_commissions_enabled` (default: false) so deployments can roll out safely.
-   Ensure atomic, well-logged operation that is easy to test in isolation.

## Phases (self-contained, testable)

Phase 1 — Design & API (safe, reviewable)

-   Create a new method on `app/Services/MLMCommissionService.php`: `processRankUpgradeCommissions(Order $order, Package $fromPackage, Package $toPackage, User $upgradedUser): bool`.
-   This method will:
    -   Load the upgraded user and order details.
    -   Traverse upline up to `max_mlm_levels` (from package settings).
    -   For each network-active upline, compute per-level rates with `MlmSetting::getCommissionForLevel($packageId, $level)` for both `fromPackage` and `toPackage`.
    -   Compute `diff = max(0, rate_to - rate_from)`.
    -   Multiply diff by order item quantity and base (package price) as appropriate (consistent with existing `creditCommission()` behavior — use `$wallet->addMLMCommission()` for dual-crediting).
    -   Log entries and ActivityLog::logMLMCommission with a `context` or `note` indicating `rank_upgrade_diff`.
    -   Return boolean success.

Implementation (Phase 1) — Code to add to `app/Services/MLMCommissionService.php`

```php
// Add near other public methods in MLMCommissionService
public function processRankUpgradeCommissions(Order $order, $fromPackage, $toPackage, $upgradedUser): bool
{
    // Feature toggle
    if (!config('mlm.rank_up_commissions_enabled', false)) {
        Log::info('Rank-up commissions disabled by config', ['order_id' => $order->id]);
        return false;
    }

    // Load order items for package used for rank purchase
    $order->load('orderItems.package');

    // Only consider MLM packages present in the order
    $mlmItems = $order->orderItems->filter(fn($it) => $it->package && $it->package->is_mlm_package);
    if ($mlmItems->isEmpty()) {
        Log::info('Rank-up order contains no MLM package items', ['order_id' => $order->id]);
        return false;
    }

    DB::beginTransaction();
    try {
        $buyer = $upgradedUser; // buyer is the user whose rank was upgraded

        foreach ($mlmItems as $orderItem) {
            $package = $orderItem->package;
            $level = 1;
            $maxLevels = $package->max_mlm_levels ?? 5;
            $currentUpline = $buyer->sponsor;

            while ($currentUpline && $level <= $maxLevels) {
                if (!$currentUpline->isNetworkActive()) {
                    $currentUpline = $currentUpline->sponsor;
                    $level++;
                    continue;
                }

                // Rates for this level
                $rateFrom = MlmSetting::getCommissionForLevel($fromPackage->id, $level) ?? 0.0;
                $rateTo = MlmSetting::getCommissionForLevel($toPackage->id, $level) ?? 0.0;

                $diffRate = max(0, $rateTo - $rateFrom);

                if ($diffRate > 0) {
                    // Typically commission values are absolute amounts (not percentages) returned by MlmSetting
                    // If MlmSetting returns percentage, convert using price. In this codebase MlmSetting returns amount.
                    $amount = $diffRate * $orderItem->quantity;

                    // Use existing crediting method for dual-crediting
                    $this->creditCommission(
                        $currentUpline,
                        $amount,
                        $order,
                        $level,
                        $buyer,
                        $package
                    );

                    ActivityLog::logMLMCommission(
                        recipient: $currentUpline,
                        amount: $amount,
                        level: $level,
                        buyer: $buyer,
                        order: $order,
                        packageId: $package->id,
                        packageName: $package->name
                    );

                    Log::info('Rank-up difference commission credited', [
                        'upgraded_user' => $buyer->id,
                        'upline' => $currentUpline->id,
                        'level' => $level,
                        'amount' => $amount,
                        'diff_rate' => $diffRate,
                        'from_package_id' => $fromPackage->id,
                        'to_package_id' => $toPackage->id,
                    ]);
                }

                $currentUpline = $currentUpline->sponsor;
                $level++;
            }
        }

        DB::commit();
        return true;
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to process rank-up commissions', ['error' => $e->getMessage(), 'order_id' => $order->id]);
        return false;
    }
}
```

Notes & decisions (Phase 1)

-   `MlmSetting::getCommissionForLevel()` is already used in `RankComparisonService` and returns the commission (amount) for a given package and level. The snippet above uses it directly to compute deltas.
-   We use `$this->creditCommission()` to ensure consistent wallet behavior (automatic dual-crediting + ActivityLog).
-   Config toggle `mlm.rank_up_commissions_enabled` ensures we can enable gradually.

Phase 2 — Integration into Rank Advancement Flow (safe, isolated)

-   After a successful rank advancement (the system-funded order is created, user updated to new rank, and `RankAdvancement` record created), call the new `processRankUpgradeCommissions()`.
-   Best practice: call the rank-up commission processing AFTER RankAdvancement commit to avoid nested transactions and to ensure the upgraded user record and order are visible.

Implementation (Phase 2) — Patch for `app/Services/RankAdvancementService.php`

Replace the end of `advanceUserRank()` where it records the advancement and sends notification. Add a post-commit hook (call after DB::commit). Example approach:

```php
// After DB::commit();
// Dispatch rank-up commission processing synchronously (immediate)
try {
    if (config('mlm.rank_up_commissions_enabled', false)) {
        $mlmService = app(\App\Services\MLMCommissionService::class);
        // Process rank-up commissions: $order is the system-funded order created earlier
        $mlmService->processRankUpgradeCommissions($order, $currentPackage, $nextPackage, $user);
    }
} catch (\Exception $e) {
    Log::error('Failed to dispatch rank-up commission processing', ['user_id' => $user->id, 'error' => $e->getMessage()]);
}
```

Testing note: Because processing is synchronous and runs after commit, it will see the updated rank on the upgraded user.

Phase 3 — Tests & Repro script (isolated)

-   Add an integration test script `test_rank_upgrade_commission.php` in project root (non-PHPUnit quick script) that:
    1. Loads app bootstrap.
    2. Creates a sponsor user (upline) and give them a higher-ranked package (e.g., `Newbie`).
    3. Creates a downline user with `Starter` package and set `sponsor_id` to sponsor's id.
    4. Seed the sponsor's 15 same-rank tracked sponsored users so the next sponsorship triggers `trackSponsorship()` to advance the downline.
    5. Create the 16th referral and call `RankAdvancementService::trackSponsorship()` manually if needed.
    6. Inspect `transactions` / `activity_logs` for new entries for `upline` with `type=mlm_commission` and a `note` indicating `rank_upgrade_diff`.

Sample test script (Phase 3)

```php
<?php
// test_rank_upgrade_commission.php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Services\RankAdvancementService;
use App\Services\MLMCommissionService;
use App\Models\Transaction;

// 1. Find packages
$starter = Package::where('rank_name', 'Starter')->first();
$newbie = Package::where('rank_name', 'Newbie')->first();

// 2. Create upline sponsor
$upline = User::factory()->create();
// assign upline's rank/package (Newbie)
$upline->update(['rank_package_id' => $newbie->id, 'current_rank' => $newbie->rank_name]);
$upline->getOrCreateWallet();
$upline->activateNetwork();

// 3. Create the downline who will advance
$downline = User::factory()->create(['sponsor_id' => $upline->id, 'rank_package_id' => $starter->id, 'current_rank' => $starter->rank_name]);
$downline->getOrCreateWallet();
$downline->activateNetwork();

// 4. Seed 15 same-rank tracked sponsored users
$rankService = app(RankAdvancementService::class);
for ($i=0; $i<15; $i++) {
    $ref = User::factory()->create(['sponsor_id' => $downline->id, 'rank_package_id' => $starter->id, 'current_rank' => $starter->rank_name]);
    $rankService->trackSponsorship($downline, $ref);
}

// 5. Create 16th referral -> should trigger advancement
$ref16 = User::factory()->create(['sponsor_id' => $downline->id, 'rank_package_id' => $starter->id, 'current_rank' => $starter->rank_name]);
$rankService->trackSponsorship($downline, $ref16);

// 6. Inspect transactions for upline
$tx = Transaction::where('user_id', $upline->id)->where('type', 'mlm_commission')->orderBy('created_at', 'desc')->get();
echo "Upline transactions: " . $tx->count() . "\n";
foreach ($tx as $t) {
    echo $t->id . ' ' . $t->amount . ' ' . $t->meta['note'] ?? '' . "\n";
}
```

Phase 4 — Logging, activity & auditability (self-contained)

-   Ensure `ActivityLog::logMLMCommission` records a `context` or `notes` field that includes `rank_upgrade_diff` and `from_package_id`, `to_package_id`, `diff_rate`.
-   Update the `creditCommission()` call path (if needed) to accept an optional `meta` parameter to store `rank_upgrade_diff` context in `transactions`.

Suggested `creditCommission` extension (small opt-in change):

```php
private function creditCommission(User $user, float $amount, Order $order, int $level, User $buyer, $package = null, array $meta = []): bool
{
    // ...existing checks
    $success = $wallet->addMLMCommission($amount, $description, $level, $order->id, $meta);
    // ... rest unchanged
}
```

And update `Wallet::addMLMCommission()` to accept and store meta in `transactions.metadata`.

Phase 5 — Config, feature flag and graceful fallback

-   Add `config/mlm.php` or extend existing to include `rank_up_commissions_enabled` (default false).
-   In code, guard new behavior with the config check.

Phase 6 — Rollout & verification

-   Run the `test_rank_upgrade_commission.php` script with `mlm.rank_up_commissions_enabled` enabled.
-   Verify `transactions`/`activity_log` entries for uplines reflect only the positive difference.
-   Validate wallet balances (mlm_balance + withdrawable_balance) increased for uplines.
-   Optionally add a unit test or PHPUnit integration test covering the scenario.

## Edge Cases & Considerations

-   If `MlmSetting::getCommissionForLevel()` returns percentages rather than absolute amounts, the implementation must convert to an amount using package price: `amount = diffPercentage * packagePrice`.
-   Duplicate prevention: ensure `Transaction::where('source_order_id', $order->id)` is used to avoid double-crediting if rank-up commission is retried.
-   Network active checks: keep the existing rule that upline must be `isNetworkActive()`.
-   If multiple packages exist in the order, sum diffs across items (mirrors current commission behavior).

## Backward Compatibility

-   Default off via config.
-   Existing path for regular order commissions unchanged.

## Files to modify (summary)

-   `app/Services/MLMCommissionService.php` — Add `processRankUpgradeCommissions()`.
-   `app/Services/RankAdvancementService.php` — After successful rank advancement commit, call the new method.
-   `app/Models/Wallet.php` — Optional: accept `meta` in `addMLMCommission()` to store `rank_upgrade_diff` context.
-   `config/mlm.php` — Add `rank_up_commissions_enabled` toggle.
-   `test_rank_upgrade_commission.php` — Test script.

## How to enable and test locally

1. Set config (for quick test): edit `.env` add `MLM_RANK_UP_COMMISSIONS_ENABLED=true` and map to config, or set `config('mlm.rank_up_commissions_enabled', true)` in runtime test script.
2. Run the reproducion script:

```bash
php test_rank_upgrade_commission.php
```

3. Inspect affected tables:

-   `transactions` for type `mlm_commission` and `metadata`/note `rank_upgrade_diff`.
-   `activity_log` entries for `rank_upgrade_diff` notes.

## Security & Safety

-   All money changes go through existing wallet functions which have auditing.
-   Keep rank-up commissions off by default to avoid surprises.

## Open questions for review

-   Confirm whether `MlmSetting::getCommissionForLevel()` returns absolute amounts or percentages. If percentages, update the code to multiply by `package` price.
-   Confirm required meta fields for `transactions` and `activity_log` so that audit trail is sufficient.

## Appendix — Full code diffs (conceptual)

The key diffs are included inline above. When you approve the design I will open a small PR that implements these methods and a test script, run the reproduction script locally, and update the todo list statuses accordingly.

---

Document created for implementation review.
