# Rank Advancement → MLM Commission Integration

## Purpose

Document the recent change that triggers MLM commission processing when a user is advanced in rank by the system (not only on purchases).

## Summary of Change

-   File modified: `app/Services/RankAdvancementService.php`
-   Behavior added: After a successful rank advancement (system-funded order created and DB commit), the service now resolves and calls `MLMCommissionService::processCommissions($order)` to distribute commissions to the upline the same way a regular checkout does.
-   The call is executed after `DB::commit()` and wrapped in a try/catch to log any errors without rolling back the rank advancement.

## Why

Previously MLM commissions were only processed during checkout flows. Rank advancements create a system-funded `Order` (RANK-\*) which should also trigger commission distribution to sponsors. This change aligns rank-advancement rewards with normal purchase flows.

## Code Location (key snippet)

In `app/Services/RankAdvancementService.php`, inside `advanceUserRank()` after the `DB::commit()` there is now a safe call:

```php
try {
    $mlmService = app(\App\Services\MLMCommissionService::class);
    $mlmService->processCommissions($order);
} catch (\Exception $e) {
    Log::error('Failed to process MLM commissions after rank advancement', [
        'order_id' => $order->id,
        'user_id' => $user->id,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
}
```

## Files Added / Edited

-   Edited: `app/Services/RankAdvancementService.php` — added the MLM trigger and an `use` import for `MLMCommissionService`.
-   Added (for testing): `demo_force_rank_advance.php` — finds a candidate user and forcibly calls `advanceUserRank()`; reports created order and any `mlm_commission` transactions.
-   Existing demo: `demo_instant_advancement.php` — unchanged, used earlier to exercise sponsorship tracking.
-   New doc file: `rankadvancementcommission.md` (this file).

## Test Runs & Observations

1. Linted `RankAdvancementService.php`:

    - Command: `php -l app/Services/RankAdvancementService.php`
    - Result: No syntax errors detected.

2. Ran `demo_instant_advancement.php` (original demo): advancement was not triggered in that run (output: "Advancement not triggered"), so no commission processing occurred.

3. Ran `demo_force_rank_advance.php` to force a rank advancement for an available user:
    - The script located a candidate user and called `advanceUserRank()`.
    - A system-funded order was created (example `RANK-6948E6566D18F`, ID 72).
    - No `mlm_commission` transactions were created for that specific order in that run.

Possible reasons for zero commissions observed in the test:

-   The package used for the rank advancement may not be flagged as an MLM-eligible package (`package->is_mlm_package` must be true).
-   Upline users may be inactive (`isNetworkActive()` false) and are skipped by `MLMCommissionService`.
-   Commission rules (rank-aware calculations) might yield zero for given upline/buyer ranks and levels.

## How to Test Manually

1. Ensure your environment is bootstrapped (Laravel app + DB):

```bash
# From project root
php artisan migrate --seed   # if you need seeded test data
```

2. Run the force-advance demo which will call `advanceUserRank()` directly:

```bash
php demo_force_rank_advance.php
```

3. If an order is created but no commissions are recorded, inspect the order and its order items:

```php
// Tinker example
php artisan tinker
>>> $order = \App\Models\Order::find(72);
>>> $order->orderItems->map(fn($i)=>[$i->package_id, $i->package->is_mlm_package]);
```

4. Manually invoke the commission processor for debugging (Tinker):

```php
php artisan tinker
>>> $order = \App\Models\Order::find(72);
>>> app(\App\Services\MLMCommissionService::class)->processCommissions($order);
```

Enable logging or temporarily add Log statements in `MLMCommissionService::processCommissions()` to see why uplines are skipped.

## Troubleshooting Checklist

-   Verify `Order` contains `orderItems` with `package->is_mlm_package === true`.
-   Verify upline users exist and `->isNetworkActive()` returns true.
-   Check `mlm_settings` for the package to ensure commission levels are configured and active.
-   Check `ActivityLog::logMLMCommission` and `Transaction` table for any partial records.
-   Inspect logs (storage/logs/laravel.log) for any "Failed to process MLM commissions" entries.

## Next Steps / Recommendations

-   Add a verbose debug flag or temporary logs inside `MLMCommissionService::processCommissions()` to produce debug-level traces when testing.
-   Consider queueing commission email notifications while keeping commission crediting synchronous, if needed for performance.
-   Add an automated test that creates an MLM-eligible rank package, ensures uplines are active, forces `advanceUserRank()`, and asserts `Transaction::where('source_order_id', $order->id)->exists()`.

## Notes

-   The added commission trigger runs after the DB commit to avoid accidental rollbacks if commission processing fails; failures are logged but do not revert the advancement.
-   This mirrors the checkout behavior where commissions are processed synchronously after payment is confirmed.

---

If you want, I can:

-   Run `app(MLMCommissionService::class)->processCommissions($order)` manually for order ID 72 and capture logs, or
-   Add temporary debug logging inside `MLMCommissionService` and re-run the forced-advance demo to capture detailed reasoning for skipped uplines.

Which action do you want next?
