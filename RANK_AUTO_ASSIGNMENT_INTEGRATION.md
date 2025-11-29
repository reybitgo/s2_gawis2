# Automatic Rank Assignment Integration

## ⚠️ IMPORTANT: Phase 1 Limitation

**Current Situation:**
Phase 1 deployment includes database structure and models, BUT **new users who purchase packages after deployment will NOT automatically get ranks assigned**.

**Why?**
Phase 1 only includes:
- ✅ Database structure (tables, columns)
- ✅ Model methods (`updateRank()`)
- ✅ Migration to assign ranks to EXISTING users
- ❌ NO automatic trigger on new purchases

**Solution:**
Add automatic rank assignment integration (simple, 10 minutes)

---

## 🔧 Solution: Auto-Assign Ranks on Package Purchase

### Option 1: Using Observer (Recommended - Clean & Maintainable)

This is the **cleanest approach** - automatically triggers when order payment status changes.

#### Step 1: Create OrderObserver

Create file: `app/Observers/OrderObserver.php`

```php
<?php

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "updated" event.
     * Automatically assign rank when order is paid
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function updated(Order $order)
    {
        // Check if payment_status changed to 'paid'
        if ($order->isDirty('payment_status') && $order->payment_status === 'paid') {
            $this->assignRankToUser($order);
        }
    }

    /**
     * Handle the Order "created" event.
     * For orders created as already paid
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function created(Order $order)
    {
        // If order is created with payment_status already 'paid'
        if ($order->payment_status === 'paid') {
            $this->assignRankToUser($order);
        }
    }

    /**
     * Assign rank to user based on purchased package
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    private function assignRankToUser(Order $order)
    {
        try {
            $user = $order->user;
            
            if (!$user) {
                Log::warning('Order has no user', ['order_id' => $order->id]);
                return;
            }

            // Check if order contains MLM packages
            $hasMLMPackage = $order->orderItems()
                ->whereHas('package', function($q) {
                    $q->where('is_mlm_package', true)
                      ->where('is_rankable', true);
                })
                ->exists();

            if ($hasMLMPackage) {
                // Update user rank based on highest package
                $user->updateRank();
                
                Log::info('Rank automatically assigned after package purchase', [
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'new_rank' => $user->fresh()->current_rank,
                ]);
            }

        } catch (\Exception $e) {
            // Don't fail order processing if rank assignment fails
            Log::error('Failed to auto-assign rank', [
                'order_id' => $order->id,
                'user_id' => $order->user_id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
```

#### Step 2: Register Observer

Update file: `app/Providers/EventServiceProvider.php`

```php
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Models\Order;
use App\Observers\OrderObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The model observers for your application.
     *
     * @var array
     */
    protected $observers = [
        Order::class => [OrderObserver::class],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
```

**If `$observers` property doesn't exist, add this to boot() method:**

```php
public function boot(): void
{
    Order::observe(OrderObserver::class);
}
```

#### Step 3: Test the Integration

```bash
php artisan tinker
```

```php
// Create test user
$user = \App\Models\User::factory()->create([
    'username' => 'ranktest_auto',
    'email' => 'ranktest_auto@test.com',
]);

// Get Starter package
$starter = \App\Models\Package::where('rank_name', 'Starter')->first();

// Create paid order
$order = \App\Models\Order::create([
    'user_id' => $user->id,
    'order_number' => 'AUTO-TEST-' . time(),
    'status' => 'confirmed',
    'payment_status' => 'paid', // This triggers the observer
    'payment_method' => 'test',
    'subtotal' => $starter->price,
    'total_amount' => $starter->price,
    'grand_total' => $starter->price,
]);

\App\Models\OrderItem::create([
    'order_id' => $order->id,
    'package_id' => $starter->id,
    'quantity' => 1,
    'unit_price' => $starter->price,
    'price' => $starter->price,
    'total_price' => $starter->price,
    'subtotal' => $starter->price,
]);

// Refresh user and check rank
$user->refresh();
echo "User rank: " . $user->getRankName() . "\n";
// Expected: "Starter"

// Cleanup
$order->delete();
$user->delete();
```

---

### Option 2: Using Model Event (Alternative - Simpler)

Add this to your `Order` model: `app/Models/Order.php`

Add at the end of the class (before the closing brace):

```php
    /**
     * Boot method - handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Assign rank when order is paid
        static::updated(function ($order) {
            // Check if payment_status changed to 'paid'
            if ($order->isDirty('payment_status') && $order->payment_status === 'paid') {
                self::assignRankToUser($order);
            }
        });

        // Handle orders created as already paid
        static::created(function ($order) {
            if ($order->payment_status === 'paid') {
                self::assignRankToUser($order);
            }
        });
    }

    /**
     * Assign rank to user based on purchased package
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    private static function assignRankToUser($order)
    {
        try {
            $user = $order->user;
            
            if (!$user) {
                \Log::warning('Order has no user', ['order_id' => $order->id]);
                return;
            }

            // Check if order contains MLM packages
            $hasMLMPackage = $order->orderItems()
                ->whereHas('package', function($q) {
                    $q->where('is_mlm_package', true)
                      ->where('is_rankable', true);
                })
                ->exists();

            if ($hasMLMPackage) {
                // Update user rank
                $user->updateRank();
                
                \Log::info('Rank auto-assigned after purchase', [
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'new_rank' => $user->fresh()->current_rank,
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Failed to auto-assign rank', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
```

---

### Option 3: Manual Trigger (Quick Fix - Not Recommended)

If you want a quick temporary solution without observers:

Find where orders are marked as paid in your codebase and add:

```php
// After: $order->payment_status = 'paid';
// Or after: $order->update(['payment_status' => 'paid']);

// Add this:
if ($order->user) {
    $order->user->updateRank();
}
```

---

## 📋 Deployment Steps (with Auto-Assignment)

### Modified Deployment Process

**Add these steps to your deployment:**

1. **Upload OrderObserver** (if using Option 1)
   ```
   app/Observers/OrderObserver.php
   ```

2. **Update EventServiceProvider** (if using Option 1)
   ```
   app/Providers/EventServiceProvider.php
   ```

3. **OR Update Order Model** (if using Option 2)
   ```
   app/Models/Order.php
   ```

4. **Clear caches after upload**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan event:clear
   ```

5. **Test auto-assignment**
   - Create test user
   - Purchase package
   - Verify rank assigned automatically

---

## ✅ After Integration

### What Now Works:

1. **New user registers** → No rank (expected)
2. **User purchases Starter package** → Gets "Starter" rank ✅
3. **User purchases Newbie package** → Rank upgrades to "Newbie" ✅
4. **User purchases Bronze package** → Rank upgrades to "Bronze" ✅
5. **Payment status changes to 'paid'** → Rank assigned automatically ✅

### What Gets Logged:

```
[2025-11-28 10:30:15] local.INFO: Rank automatically assigned after package purchase
{
    "user_id": 123,
    "order_id": 456,
    "new_rank": "Starter"
}
```

---

## 🧪 Testing the Integration

### Test 1: New Purchase (Primary Test)

```bash
# In production after deployment
# Via admin dashboard or test account:

1. Create new user or login as test user
2. Purchase "Starter Package"
3. Complete payment (mark as paid)
4. Check user profile
   Expected: Rank shows "Starter"
```

### Test 2: Rank Upgrade

```bash
1. User has "Starter" rank
2. Purchase "Newbie Package"
3. Complete payment
4. Check user profile
   Expected: Rank upgraded to "Newbie"
```

### Test 3: Direct High Rank Purchase

```bash
1. New user (no rank)
2. Purchase "Bronze Package" directly
3. Complete payment
4. Check user profile
   Expected: Rank shows "Bronze" (skipping Starter/Newbie)
```

---

## 📊 Verification Queries

After deploying auto-assignment:

```sql
-- Check recent orders and ranks
SELECT 
    o.id as order_id,
    o.order_number,
    u.username,
    u.current_rank,
    o.payment_status,
    o.created_at
FROM orders o
JOIN users u ON o.user_id = u.id
WHERE o.created_at >= CURDATE()
ORDER BY o.created_at DESC
LIMIT 20;

-- Find users with paid orders but no rank (shouldn't happen after integration)
SELECT 
    u.id,
    u.username,
    u.current_rank,
    COUNT(o.id) as paid_orders
FROM users u
JOIN orders o ON u.id = o.user_id
WHERE o.payment_status = 'paid'
  AND o.created_at >= '2025-11-28' -- After deployment date
GROUP BY u.id
HAVING u.current_rank IS NULL AND paid_orders > 0;
-- Expected: 0 rows (empty result)
```

---

## 🚨 Rollback (If Needed)

If auto-assignment causes issues:

### Remove Observer (Option 1):
```php
// In EventServiceProvider.php
// Remove or comment out:
protected $observers = [
    // Order::class => [OrderObserver::class],
];
```

### Remove Model Events (Option 2):
```php
// In Order.php
// Remove the boot() method
```

Then clear caches:
```bash
php artisan config:clear
php artisan cache:clear
php artisan event:clear
```

**Ranks remain:** Existing ranks stay, but new purchases won't auto-assign.

---

## 📝 Updated Deployment Checklist

Add these items to your deployment checklist:

**Before Deployment:**
- [ ] Choose integration option (Observer or Model Event)
- [ ] Test auto-assignment in local environment
- [ ] Verify no conflicts with existing code

**During Deployment:**
- [ ] Upload Observer file (if using Option 1)
- [ ] Upload updated EventServiceProvider (if using Option 1)
- [ ] Upload updated Order model (if using Option 2)
- [ ] Clear event cache
- [ ] Test purchase flow

**After Deployment:**
- [ ] Create test purchase
- [ ] Verify rank auto-assigned
- [ ] Check logs for assignment confirmation
- [ ] Monitor for next 24 hours

---

## 💡 Recommendation

**Use Option 1 (Observer)** because:
- ✅ Clean separation of concerns
- ✅ Easy to maintain
- ✅ Easy to disable if needed
- ✅ Doesn't clutter Order model
- ✅ Standard Laravel pattern

**Deployment Time Impact:** +5 minutes (2 extra files to upload)

---

## 🎯 Final Answer to Your Question

**Q: Will new users get ranks assigned automatically after Phase 1 deployment?**

**A: NO** - unless you add the auto-assignment integration (this document).

**With Integration (Recommended):**
- ✅ New purchases automatically assign ranks
- ✅ Existing users already have ranks (from migration)
- ✅ System fully automated
- ✅ No manual intervention needed

**Without Integration (Phase 1 Only):**
- ❌ New purchases do NOT assign ranks automatically
- ✅ Existing users have ranks (from migration)
- ⚠️ Manual rank assignment needed for new purchases
- ⚠️ Or wait for Phase 3 full automation

---

## 📚 Files to Add for Auto-Assignment

**Option 1 (Observer):**
1. `app/Observers/OrderObserver.php` (NEW)
2. `app/Providers/EventServiceProvider.php` (UPDATE)

**Option 2 (Model Event):**
1. `app/Models/Order.php` (UPDATE)

**Total:** 1-2 files only

**Deploy with Phase 1?** YES - Recommended to avoid issues

---

## ✅ Conclusion

**You SHOULD deploy auto-assignment integration with Phase 1** to ensure:
- All new users who purchase packages get ranks automatically
- No manual intervention needed
- Complete automation from day 1
- No gap in functionality

**Estimated additional time:** 5-10 minutes

**Risk level:** 🟢 Very Low (observer pattern is safe and tested)

---

*Add this integration to your Phase 1 deployment for complete automation!*
