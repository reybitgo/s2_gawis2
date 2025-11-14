# Unilevel System Implementation Plan

## Overview

This document outlines the comprehensive implementation plan for the **Unilevel Bonus System** - a complementary compensation structure alongside the existing MLM Commission system. While the existing MLM system focuses on member acquisition through package purchases with fixed commission amounts, the Unilevel System provides ongoing passive income through repeat product purchases using the same fixed-amount structure.

**Key Similarity**: Both systems are nearly identical in structure:
- **MLM Commission**: Packages → `mlm_settings` table → fixed commission amounts per level
- **Unilevel Bonus**: Products → `unilevel_settings` table → fixed bonus amounts per level

The main difference is simply the trigger: packages activate MLM commissions, products activate Unilevel bonuses.

---

## System Architecture

### Core Concept

The Unilevel System provides a **5-level deep fixed-amount bonus structure** where active MLM members earn bonuses from product purchases made by their direct line of downlines. This complements the existing MLM commission system by providing ongoing passive income from consumable product repurchases.

**Important**: Both systems use the **same sponsor tree** (the `sponsor_id` relationship) and the **same fixed-amount structure**, but they trigger on different types of purchases (packages vs products).

### Key Differences: Packages vs Products

| Feature | Packages (Existing MLM) | Products (Unilevel) |
|---------|------------------------|---------------------|
| **Purpose** | One-time member activation | Repeat consumable purchases |
| **Price Range** | ₱1,000 - ₱5,000+ | ₱100 - ₱1,000 |
| **Compensation Trigger** | MLM Commission System | Unilevel Bonus System |
| **Payout Structure** | Fixed amounts per level | Fixed amounts per level |
| **Configuration** | `mlm_settings` table | `unilevel_settings` table |
| **Frequency** | One-time or rare | Regular/recurring |
| **Inventory** | Limited quantities | Higher quantities |
| **Target** | New member acquisition | Ongoing consumption & retention |

### Existing MLM Commission System (Packages)

Your current system:
- **Trigger**: Purchase of MLM packages (e.g., Starter Package - ₱1,000)
- **Commission Structure** (fixed amounts):
  - Level 1: ₱200 (direct sponsor)
  - Level 2: ₱50 (indirect)
  - Level 3: ₱50 (indirect)
  - Level 4: ₱50 (indirect)
  - Level 5: ₱50 (indirect)
  - **Total**: ₱400 per package sold (40% of ₱1,000)
- **Credits to**: `mlm_balance` (withdrawable)
- **Configuration**: When admin creates/edits a package, they configure MLM settings with 5 fixed amounts
- **Table**: `mlm_settings` (columns: `package_id`, `level`, `commission_amount`, `is_active`)

### New Unilevel Bonus System (Products)

What we're adding:
- **Trigger**: Purchase of consumable products (e.g., Herbal Tea - ₱500)
- **Bonus Structure** (fixed amounts, configurable per product):
  - Level 1: ₱20 (example for ₱500 product)
  - Level 2: ₱10 (example)
  - Level 3: ₱10 (example)
  - Level 4: ₱10 (example)
  - Level 5: ₱10 (example)
  - **Total**: ₱60 per product sold (12% of ₱500 in this example)
- **Credits to**: `mlm_balance` (withdrawable, same wallet as MLM commissions)
- **Configuration**: When admin creates/edits a product, they configure Unilevel settings with 5 fixed amounts
- **Table**: `unilevel_settings` (columns: `product_id`, `level`, `bonus_amount`, `is_active`)
- **Structure**: Identical to `mlm_settings`, but for products instead of packages

### Enhanced Wallet System (4-Balance Structure)

With the addition of the Unilevel System, we're implementing a **4-balance wallet architecture** for better fund tracking and withdrawal management:

#### Wallet Balance Structure (Updated 2025-10-11)

```
┌─────────────────────────────────────────────────────────────┐
│                        USER WALLET                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  1. mlm_balance           (Lifetime MLM Tracker)           │
│     └─ Earned from package purchases by downlines          │
│     └─ Display purposes ONLY - never deducted              │
│     └─ Tracks total MLM commissions ever earned            │
│                                                             │
│  2. unilevel_balance      (Lifetime Unilevel Tracker)      │
│     └─ Earned from product purchases by downlines          │
│     └─ Display purposes ONLY - never deducted              │
│     └─ Tracks total Unilevel bonuses ever earned           │
│                                                             │
│  3. withdrawable_balance  (Withdrawable Funds)             │
│     └─ AUTOMATICALLY credited when MLM/Unilevel earned     │
│     └─ ONLY balance that can be withdrawn to bank          │
│     └─ Can be used for purchases                           │
│     └─ No manual transfer needed!                          │
│                                                             │
│  4. purchase_balance      (Non-Withdrawable Funds)         │
│     └─ From deposits, refunds, transfers received          │
│     └─ Cannot be withdrawn (anti-money laundering)         │
│     └─ Can only be used for purchases                      │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

#### Balance Flow Diagram (Updated 2025-10-11)

```
EARNINGS:
  Package Purchase → MLM Commission Earned
                     ├─→ mlm_balance += amount (lifetime tracker ONLY)
                     └─→ withdrawable_balance += amount (INSTANTLY WITHDRAWABLE!)

  Product Purchase → Unilevel Bonus Earned
                     ├─→ unilevel_balance += amount (lifetime tracker ONLY)
                     └─→ withdrawable_balance += amount (INSTANTLY WITHDRAWABLE!)

  Deposits/Refunds → purchase_balance (non-withdrawable)

WITHDRAWALS:
  ONLY withdrawable_balance → Bank Account / E-Wallet

PURCHASES:
  Total Available = withdrawable_balance + purchase_balance
  NOTE: mlm_balance and unilevel_balance are NOT included (display only)
  Deduction Priority: purchase_balance → withdrawable_balance
  NOTE: mlm_balance and unilevel_balance are NEVER deducted
```

#### Key Rules (Updated 2025-10-11)

1. **Automatic Earning & Crediting**:
   - MLM commission earned → BOTH `mlm_balance` (tracker) AND `withdrawable_balance` (withdrawable) auto-incremented
   - Unilevel bonus earned → BOTH `unilevel_balance` (tracker) AND `withdrawable_balance` (withdrawable) auto-incremented
   - **No manual transfer needed!** Earnings are instantly withdrawable

2. **Balance Purposes**:
   - `mlm_balance`: Lifetime MLM earnings tracker (display/reporting ONLY, never deducted)
   - `unilevel_balance`: Lifetime Unilevel earnings tracker (display/reporting ONLY, never deducted)
   - `withdrawable_balance`: Actual withdrawable funds (the only balance you can cash out)
   - `purchase_balance`: Non-withdrawable deposit funds

3. **Withdrawal Rules**:
   - ONLY `withdrawable_balance` can be withdrawn
   - Minimum withdrawal: ₱500
   - Requires admin approval
   - Processing fee: 2-5% (configurable)

4. **Purchase Rules**:
   - ONLY 2 balances can be used for purchases: `withdrawable_balance` + `purchase_balance`
   - Deduction priority: `purchase_balance` (first) → `withdrawable_balance` (second)
   - `mlm_balance` and `unilevel_balance` are NEVER deducted (lifetime trackers only)
   - Total available for purchases = `withdrawable_balance + purchase_balance`

#### Benefits of This Structure

✅ **Instant Availability**: Earnings are immediately withdrawable, no waiting for transfers
✅ **Lifetime Tracking**: Separate balances track total MLM vs Unilevel earnings
✅ **Clear Audit Trail**: Know exactly how much earned from each income stream
✅ **User Friendly**: No manual transfer steps required
✅ **Transparent**: Members see both their lifetime earnings AND current withdrawable balance

### Integration Points

The Unilevel System integrates with existing systems:

1. **Cart System**: Products use the same CartService as packages
2. **Checkout Flow**: Products follow identical checkout process
3. **Order Management**: Products create orders with same 26-status lifecycle
4. **Enhanced Wallet System**: Unilevel bonuses credit to `unilevel_balance` (new)
5. **User Tree**: Uses existing `sponsor_id` relationship
6. **Active Status**: Leverages existing MLM member status tracking

---

## Phase 1: Product Management Foundation + Wallet Enhancement

**Status**: Not Started
**Estimated Development Time**: 8-10 hours
**Prerequisites**: None (builds on existing package system)

### Objectives

1. **Enhance wallet system** with 4-balance structure (mlm, unilevel, withdrawable, purchase)
2. Add automatic dual-crediting for earnings (tracker + withdrawable)
3. Create product entity separate from packages
4. Build admin product management interface
5. Establish product-to-unilevel bonus relationship
6. Set up product inventory tracking

### Database Schema

#### 1.0 Wallet Enhancement Migration

**File**: `database/migrations/YYYY_MM_DD_add_unilevel_and_withdrawable_balances_to_wallets.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('wallets', function (Blueprint $table) {
            // Add new balance columns
            $table->decimal('unilevel_balance', 10, 2)->default(0.00)->after('mlm_balance');
            $table->decimal('withdrawable_balance', 10, 2)->default(0.00)->after('unilevel_balance');

            // Add indexes for performance
            $table->index('mlm_balance');
            $table->index('unilevel_balance');
            $table->index('withdrawable_balance');
        });
    }

    public function down()
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn(['unilevel_balance', 'withdrawable_balance']);
        });
    }
};
```

**Result**: Wallets table now has:
- `mlm_balance` (existing - pending MLM commissions)
- `unilevel_balance` (new - pending Unilevel bonuses)
- `withdrawable_balance` (new - confirmed withdrawable funds)
- `purchase_balance` (existing - non-withdrawable deposit funds)

#### 1.1 Products Table Migration

```php
// database/migrations/YYYY_MM_DD_create_products_table.php

Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->decimal('price', 10, 2);
    $table->integer('points_awarded')->default(0);
    $table->integer('quantity_available')->nullable(); // null = unlimited
    $table->text('short_description')->nullable();
    $table->text('long_description')->nullable();
    $table->string('image_path')->nullable();
    $table->boolean('is_active')->default(true);
    $table->integer('sort_order')->default(0);
    $table->json('meta_data')->nullable();

    // Unilevel specific fields (optional, for summary display)
    $table->decimal('total_unilevel_bonus', 10, 2)->default(0); // Sum of all level bonuses (auto-calculated)

    // SKU and categorization
    $table->string('sku')->unique()->nullable();
    $table->string('category')->nullable();
    $table->integer('weight_grams')->nullable(); // For shipping calculation

    $table->timestamps();
    $table->softDeletes();

    $table->index(['is_active', 'sort_order']);
    $table->index('category');
});
```

#### 1.2 Unilevel Settings Table Migration

```php
// database/migrations/YYYY_MM_DD_create_unilevel_settings_table.php

Schema::create('unilevel_settings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
    $table->tinyInteger('level'); // 1-5
    $table->decimal('bonus_amount', 10, 2); // Fixed amount per level (e.g., ₱20, ₱10, etc.)
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->unique(['product_id', 'level']);
    $table->index(['product_id', 'is_active']);
});
```

**Note**: This structure is identical to `mlm_settings`, but for products instead of packages:
- `mlm_settings`: `package_id`, `level`, `commission_amount`, `is_active`
- `unilevel_settings`: `product_id`, `level`, `bonus_amount`, `is_active`

### Models

#### 1.3 Product Model

**File**: `app/Models/Product.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'points_awarded',
        'quantity_available',
        'short_description',
        'long_description',
        'image_path',
        'is_active',
        'sort_order',
        'meta_data',
        'total_unilevel_bonus',
        'sku',
        'category',
        'weight_grams',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'points_awarded' => 'integer',
        'quantity_available' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'meta_data' => 'array',
        'total_unilevel_bonus' => 'decimal:2',
        'weight_grams' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (!$product->slug) {
                $product->slug = Str::slug($product->name);
            }
            if (!$product->sku) {
                $product->sku = 'PROD-' . strtoupper(Str::random(8));
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && !$product->isDirty('slug')) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::saved(function ($product) {
            Cache::forget("product_{$product->id}");
        });

        static::deleted(function ($product) {
            Cache::forget("product_{$product->id}");
        });
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where(function($q) {
            $q->whereNull('quantity_available')
              ->orWhere('quantity_available', '>', 0);
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Relationships
    public function unilevelSettings(): HasMany
    {
        return $this->hasMany(UnilevelSetting::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Accessors
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getImageUrlAttribute()
    {
        return $this->image_path
            ? asset('storage/' . $this->image_path)
            : asset('images/product-placeholder.svg');
    }

    public function getFormattedPriceAttribute()
    {
        return currency($this->price);
    }

    // Business Logic
    public function isAvailable(): bool
    {
        return $this->is_active &&
               ($this->quantity_available === null || $this->quantity_available > 0);
    }

    public function canBeDeleted(): bool
    {
        return $this->orderItems()->count() === 0;
    }

    public function reduceQuantity(int $amount = 1): void
    {
        if ($this->quantity_available !== null) {
            $this->quantity_available = max(0, $this->quantity_available - $amount);
            $this->save();
        }
    }

    /**
     * Calculate total unilevel bonus for this product
     * Similar to how Package calculates total MLM commission
     */
    public function calculateTotalUnilevelBonus(): float
    {
        return $this->unilevelSettings()
            ->where('is_active', true)
            ->sum('bonus_amount');
    }

    /**
     * Update the cached total unilevel bonus
     * Should be called after unilevel settings are modified
     */
    public function updateTotalUnilevelBonus(): void
    {
        $this->update([
            'total_unilevel_bonus' => $this->calculateTotalUnilevelBonus()
        ]);
    }
}
```

#### 1.4 UnilevelSetting Model

**File**: `app/Models/UnilevelSetting.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnilevelSetting extends Model
{
    protected $fillable = [
        'product_id',
        'level',
        'bonus_amount',
        'is_active',
    ];

    protected $casts = [
        'level' => 'integer',
        'bonus_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get default unilevel structure for a product
     * Similar to MlmSetting default structure
     */
    public static function getDefaultStructure(float $productPrice): array
    {
        // Default example: Lower amounts than packages since products are consumable
        // Admin can customize these amounts per product
        return [
            ['level' => 1, 'bonus_amount' => 20.00], // Direct sponsor gets more
            ['level' => 2, 'bonus_amount' => 10.00],
            ['level' => 3, 'bonus_amount' => 10.00],
            ['level' => 4, 'bonus_amount' => 10.00],
            ['level' => 5, 'bonus_amount' => 10.00],
        ];
        // Total: ₱60 per product sold (example for ₱500 product = 12%)
    }
}
```

**Comparison with MlmSetting**:
Both models have identical structure, just different naming:
- `MlmSetting`: Has `package_id`, `commission_amount` for packages
- `UnilevelSetting`: Has `product_id`, `bonus_amount` for products

#### 1.5 Enhanced Wallet Model

**File**: `app/Models/Wallet.php` (Update existing model)

Add the following methods (Updated 2025-10-11):

```php
/**
 * Get total balance (withdrawable + purchase ONLY)
 * Note: mlm_balance and unilevel_balance excluded to prevent double-counting
 */
public function getTotalBalanceAttribute(): float
{
    return $this->withdrawable_balance + $this->purchase_balance;
}

/**
 * Get total lifetime earnings (MLM + Unilevel trackers)
 */
public function getLifetimeEarningsAttribute(): float
{
    return $this->mlm_balance + $this->unilevel_balance;
}

/**
 * Get lifetime MLM earnings (display only)
 */
public function getLifetimeMLMEarningsAttribute(): float
{
    return $this->mlm_balance;
}

/**
 * Get lifetime Unilevel earnings (display only)
 */
public function getLifetimeUnilevelEarningsAttribute(): float
{
    return $this->unilevel_balance;
}

/**
 * Get total available for withdrawal
 */
public function getAvailableForWithdrawalAttribute(): float
{
    return $this->withdrawable_balance;
}

/**
 * Add MLM commission (AUTOMATIC DUAL-CREDITING)
 * Credits BOTH mlm_balance (tracker) AND withdrawable_balance (withdrawable)
 * Called by MLMCommissionService when commission is earned
 */
public function addMLMCommission(float $amount, string $description, int $level, int $sourceOrderId): bool
{
    \DB::beginTransaction();
    try {
        // AUTOMATIC DUAL-CREDITING:
        // 1. Credit mlm_balance (lifetime earnings tracker)
        $this->increment('mlm_balance', $amount);

        // 2. Credit withdrawable_balance (instantly withdrawable!)
        $this->increment('withdrawable_balance', $amount);

        $this->update(['last_transaction_at' => now()]);

        Transaction::create([
            'user_id' => $this->user_id,
            'type' => 'mlm_commission',
            'amount' => $amount,
            'description' => $description,
            'status' => 'completed',
            'level' => $level,
            'source_order_id' => $sourceOrderId,
            'source_type' => 'mlm',
            'metadata' => json_encode([
                'level' => $level,
                'source_order_id' => $sourceOrderId,
                'credited_to' => 'mlm_balance+withdrawable_balance',
                'auto_credited' => true
            ])
        ]);

        \DB::commit();
        return true;
    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error('Failed to add MLM commission', [
            'wallet_id' => $this->id,
            'amount' => $amount,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

/**
 * Add Unilevel bonus (AUTOMATIC DUAL-CREDITING)
 * Credits BOTH unilevel_balance (tracker) AND withdrawable_balance (withdrawable)
 * Called by UnilevelBonusService when bonus is earned
 */
public function addUnilevelBonus(float $amount, string $description, int $level, int $sourceOrderId): bool
{
    \DB::beginTransaction();
    try {
        // AUTOMATIC DUAL-CREDITING:
        // 1. Credit unilevel_balance (lifetime earnings tracker)
        $this->increment('unilevel_balance', $amount);

        // 2. Credit withdrawable_balance (instantly withdrawable!)
        $this->increment('withdrawable_balance', $amount);

        $this->update(['last_transaction_at' => now()]);

        Transaction::create([
            'user_id' => $this->user_id,
            'type' => 'unilevel_bonus',
            'amount' => $amount,
            'description' => $description,
            'status' => 'completed',
            'level' => $level,
            'source_order_id' => $sourceOrderId,
            'source_type' => 'unilevel',
            'metadata' => json_encode([
                'level' => $level,
                'source_order_id' => $sourceOrderId,
                'credited_to' => 'unilevel_balance+withdrawable_balance',
                'auto_credited' => true
            ])
        ]);

        \DB::commit();
        return true;
    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error('Failed to add Unilevel bonus', [
            'wallet_id' => $this->id,
            'amount' => $amount,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

/**
 * Deduct from combined balance (for purchases)
 * Priority: purchase_balance → withdrawable_balance
 * Note: mlm_balance and unilevel_balance are NEVER deducted (lifetime trackers only)
 */
public function deductCombinedBalance(float $amount): bool
{
    if ($amount <= 0 || $amount > $this->total_balance) {
        return false;
    }

    \DB::beginTransaction();
    try {
        $remaining = $amount;
        $deductions = [];

        // 1. Deduct from purchase_balance first (non-withdrawable deposits)
        if ($remaining > 0 && $this->purchase_balance > 0) {
            $deduct = min($remaining, $this->purchase_balance);
            $this->decrement('purchase_balance', $deduct);
            $remaining -= $deduct;
            $deductions['purchase_balance'] = $deduct;
        }

        // 2. Then deduct from withdrawable_balance (withdrawable MLM/Unilevel earnings)
        if ($remaining > 0 && $this->withdrawable_balance > 0) {
            $deduct = min($remaining, $this->withdrawable_balance);
            $this->decrement('withdrawable_balance', $deduct);
            $remaining -= $deduct;
            $deductions['withdrawable_balance'] = $deduct;
        }

        // Note: mlm_balance and unilevel_balance are NEVER deducted
        // They are lifetime trackers for display purposes only

        $this->update(['last_transaction_at' => now()]);

        \Log::info('Combined balance deducted', [
            'wallet_id' => $this->id,
            'total_amount' => $amount,
            'deductions' => $deductions,
            'remaining_after' => $remaining
        ]);

        \DB::commit();
        return $remaining == 0;
    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error('Failed to deduct combined balance', [
            'wallet_id' => $this->id,
            'amount' => $amount,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

/**
 * Add to purchase balance (for deposits and refunds)
 */
public function addPurchaseBalance(float $amount): void
{
    $this->increment('purchase_balance', $amount);
    $this->update(['last_transaction_at' => now()]);
}
```

**Key Implementation Note**: mlm_balance and unilevel_balance are purely for tracking lifetime earnings and are NEVER deducted during purchases. The actual spendable/withdrawable funds are in withdrawable_balance (auto-credited when earnings occur).

### Admin Interface

#### 1.5 Admin Product Controller

**File**: `app/Http/Controllers/Admin/AdminProductController.php`

Key features:
- Full CRUD operations (list, create, edit, delete)
- Image upload handling
- Soft delete protection (products with orders cannot be hard deleted)
- Category management
- Bulk operations (activate/deactivate, category assignment)

#### 1.6 Admin Unilevel Settings Controller

**File**: `app/Http/Controllers/Admin/AdminUnilevelSettingsController.php`

Key features:
- Configure 5-level bonus structure per product (identical to MLM settings interface)
- Edit fixed bonus amounts for each of the 5 levels
- Preview total bonus calculation
- Bulk apply default structure to multiple products
- Validate total bonus doesn't exceed product price (optional warning)

### Routes

**File**: `routes/web.php`

```php
// Admin Product Management
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('products', AdminProductController::class);
    Route::post('products/bulk-action', [AdminProductController::class, 'bulkAction'])->name('products.bulk-action');

    // Unilevel Settings Management
    Route::get('products/{product}/unilevel-settings', [AdminUnilevelSettingsController::class, 'edit'])
        ->name('products.unilevel-settings.edit');
    Route::put('products/{product}/unilevel-settings', [AdminUnilevelSettingsController::class, 'update'])
        ->name('products.unilevel-settings.update');
    Route::post('products/unilevel-settings/apply-defaults', [AdminUnilevelSettingsController::class, 'applyDefaults'])
        ->name('products.unilevel-settings.apply-defaults');
});
```

### Seeders

#### 1.7 Product Seeder

**File**: `database/seeders/ProductSeeder.php`

Sample products with unilevel bonus settings:

1. **Herbal Tea Blend** - ₱250
   - Level 1: ₱10, Level 2-5: ₱5 each
   - Total bonus: ₱30 (12% of price)

2. **Vitamin Supplement** - ₱450
   - Level 1: ₱20, Level 2-5: ₱8 each
   - Total bonus: ₱52 (11.5% of price)

3. **Health Drink Mix** - ₱350
   - Level 1: ₱15, Level 2-5: ₱7 each
   - Total bonus: ₱43 (12.3% of price)

4. **Herbal Capsules** - ₱600
   - Level 1: ₱25, Level 2-5: ₱10 each
   - Total bonus: ₱65 (10.8% of price)

5. **Wellness Oil** - ₱500
   - Level 1: ₱20, Level 2-5: ₱10 each
   - Total bonus: ₱60 (12% of price)

Each product includes:
- 5-level unilevel bonus structure (fixed amounts per level)
- Product images and descriptions
- Categorization (supplements, beverages, topical, etc.)
- SKU generation
- Automatic unilevel_settings creation upon seeding

### Views

#### 1.9 Admin Product Views

**Directory**: `resources/views/admin/products/`

Views needed:
- `index.blade.php` - Product listing with filters and search
- `create.blade.php` - Create new product form
- `edit.blade.php` - Edit product form
- `show.blade.php` - Product details view
- `unilevel-settings.blade.php` - Configure unilevel bonus structure

UI Features:
- Responsive data table with pagination
- Image preview and upload
- Category filtering
- Status toggle (active/inactive)
- Bulk actions dropdown
- Unilevel bonus calculator preview

---

## Phase 2: Cart & Checkout Integration

**Status**: Not Started
**Estimated Development Time**: 6-8 hours
**Prerequisites**: Phase 1 complete

### Objectives

1. Integrate products into existing CartService
2. Enable mixed cart (packages + products)
3. Distinguish product orders from package orders
4. Maintain separate order tracking for unilevel bonus calculation

### Implementation Details

#### 2.1 CartService Enhancement

**File**: `app/Services/CartService.php`

Enhancements needed:
- Add support for product items (in addition to packages)
- Distinguish between `package_id` and `product_id` in cart items
- Calculate separate subtotals for packages vs products
- Handle product-specific inventory checks

Cart item structure:
```php
[
    'id' => 'package_1' or 'product_1',
    'type' => 'package' or 'product',
    'item_id' => 1, // actual package_id or product_id
    'name' => 'Product Name',
    'price' => 250.00,
    'quantity' => 2,
    'points' => 10,
    'image' => 'path/to/image.jpg',
    'subtotal' => 500.00,
]
```

#### 2.2 OrderItem Enhancement

**Migration**: Enhance `order_items` table to support products

```sql
ALTER TABLE order_items
    ADD COLUMN product_id BIGINT UNSIGNED NULL AFTER package_id,
    ADD COLUMN item_type ENUM('package', 'product') DEFAULT 'package' AFTER package_id,
    ADD FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL;
```

**Model Update**: `app/Models/OrderItem.php`

```php
protected $fillable = [
    'order_id',
    'package_id',
    'product_id',
    'item_type',
    'quantity',
    'price',
    'package_snapshot',
    'product_snapshot',
    'points_awarded',
    'subtotal',
];

public function product()
{
    return $this->belongsTo(Product::class);
}

public function getItem()
{
    return $this->item_type === 'package'
        ? $this->package
        : $this->product;
}

public function getSnapshot()
{
    return $this->item_type === 'package'
        ? $this->package_snapshot
        : $this->product_snapshot;
}
```

#### 2.3 Public Product Catalog

**Controller**: `app/Http/Controllers/ProductController.php`

**Routes**:
```php
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
```

**Views**: `resources/views/products/`
- `index.blade.php` - Product catalog with filtering
- `show.blade.php` - Individual product details

Features:
- Category filtering
- Price range filtering
- Search functionality
- "Add to Cart" button with real-time updates
- Display "In Cart" status like packages
- Show unilevel bonus structure (optional, for motivation)

#### 2.4 Cart Page Enhancement

**View**: `resources/views/cart/index.blade.php`

Enhancements:
- Group cart items by type (Packages section, Products section)
- Display separate subtotals
- Visual distinction between packages and products
- Show points separately for packages vs products

#### 2.5 Checkout Process

**Controller**: `app/Http/Controllers/CheckoutController.php`

No major changes needed - checkout flow remains identical:
1. Review cart (packages + products)
2. Enter/confirm delivery address
3. Accept terms & conditions
4. Pay with e-wallet
5. Order confirmation

**Order Processing**:
- Create single order with mixed order items
- Trigger both Binary MLM commission (for packages) and Unilevel bonus (for products)
- Reduce inventory for both packages and products

---

## Phase 3: Unilevel Tree Structure & Active Status

**Status**: Not Started
**Estimated Development Time**: 8-10 hours
**Prerequisites**: Phase 2 complete

### Objectives

1. Implement 5-level unilevel tree tracking
2. Define "active member" qualification criteria
3. Build unilevel downline query system
4. Create member genealogy view (unilevel perspective)

### Implementation Details

#### 3.1 Active Member Status

**Definition**: A member is "active" for unilevel bonuses if:
1. They have purchased at least one MLM package (activated in binary MLM)
2. Their account status is not suspended/banned
3. Optional: Made at least one product purchase in last 90 days (configurable)

**Database**: Add to `users` table

```sql
ALTER TABLE users
    ADD COLUMN mlm_status ENUM('inactive', 'active', 'suspended') DEFAULT 'inactive',
    ADD COLUMN last_product_purchase_at TIMESTAMP NULL,
    ADD COLUMN mlm_activated_at TIMESTAMP NULL;
```

**Model Update**: `app/Models/User.php`

```php
protected $casts = [
    'last_product_purchase_at' => 'datetime',
    'mlm_activated_at' => 'datetime',
];

public function isActiveForUnilevel(): bool
{
    // Must be MLM active
    if ($this->mlm_status !== 'active') {
        return false;
    }

    // Optional: Check recent product purchase (90-day rule)
    $requiresRecentPurchase = SystemSetting::get('unilevel_requires_recent_purchase', false);
    if ($requiresRecentPurchase) {
        $daysSinceLastPurchase = $this->last_product_purchase_at
            ? $this->last_product_purchase_at->diffInDays(now())
            : 999;

        $maxDays = SystemSetting::get('unilevel_active_days_requirement', 90);
        if ($daysSinceLastPurchase > $maxDays) {
            return false;
        }
    }

    return true;
}

public function activateMLM(): void
{
    $this->update([
        'mlm_status' => 'active',
        'mlm_activated_at' => now(),
    ]);
}
```

#### 3.2 Unilevel Downline Service

**File**: `app/Services/UnilevelTreeService.php`

```php
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class UnilevelTreeService
{
    /**
     * Get unilevel downline members up to specified level
     */
    public function getUnilevelDownline(User $sponsor, int $maxLevel = 5): Collection
    {
        $downlines = collect();
        $currentLevel = [$sponsor->id];

        for ($level = 1; $level <= $maxLevel; $level++) {
            // Get direct downlines of current level members
            $nextLevel = User::whereIn('sponsor_id', $currentLevel)
                ->get()
                ->map(function ($user) use ($level) {
                    $user->unilevel_level = $level;
                    return $user;
                });

            if ($nextLevel->isEmpty()) {
                break;
            }

            $downlines = $downlines->merge($nextLevel);
            $currentLevel = $nextLevel->pluck('id')->toArray();
        }

        return $downlines;
    }

    /**
     * Get active unilevel downline members (eligible for bonuses)
     */
    public function getActiveUnilevelDownline(User $sponsor, int $maxLevel = 5): Collection
    {
        return $this->getUnilevelDownline($sponsor, $maxLevel)
            ->filter(fn($user) => $user->isActiveForUnilevel());
    }

    /**
     * Get unilevel upline members (ancestors up to 5 levels)
     */
    public function getUnilevelUpline(User $member, int $maxLevel = 5): Collection
    {
        $uplines = collect();
        $currentUser = $member;

        for ($level = 1; $level <= $maxLevel; $level++) {
            if (!$currentUser->sponsor_id) {
                break;
            }

            $sponsor = User::find($currentUser->sponsor_id);
            if (!$sponsor) {
                break;
            }

            $sponsor->unilevel_level = $level;
            $uplines->push($sponsor);

            $currentUser = $sponsor;
        }

        return $uplines;
    }

    /**
     * Get active unilevel upline members (eligible to receive bonuses)
     */
    public function getActiveUnilevelUpline(User $member, int $maxLevel = 5): Collection
    {
        return $this->getUnilevelUpline($member, $maxLevel)
            ->filter(fn($user) => $user->isActiveForUnilevel());
    }

    /**
     * Count downlines per level
     */
    public function countDownlinePerLevel(User $sponsor, int $maxLevel = 5): array
    {
        $downlines = $this->getUnilevelDownline($sponsor, $maxLevel);
        $counts = [];

        for ($level = 1; $level <= $maxLevel; $level++) {
            $counts[$level] = $downlines->where('unilevel_level', $level)->count();
        }

        return $counts;
    }

    /**
     * Get unilevel statistics for a member
     */
    public function getUnilevelStats(User $member): array
    {
        $downlines = $this->getUnilevelDownline($member);
        $activeDownlines = $downlines->filter(fn($u) => $u->isActiveForUnilevel());

        return [
            'total_downlines' => $downlines->count(),
            'active_downlines' => $activeDownlines->count(),
            'downlines_per_level' => $this->countDownlinePerLevel($member),
            'total_product_sales' => $this->calculateTotalProductSales($downlines),
            'estimated_monthly_bonus' => $this->estimateMonthlyBonus($member),
        ];
    }

    private function calculateTotalProductSales(Collection $downlines): float
    {
        // Implementation: Sum all product purchases from downlines
        return 0.00; // Placeholder
    }

    private function estimateMonthlyBonus(User $member): float
    {
        // Implementation: Calculate average monthly bonus based on historical data
        return 0.00; // Placeholder
    }
}
```

#### 3.3 Member Genealogy View

**Controller**: `app/Http/Controllers/Member/UnilevelController.php`

**Routes**:
```php
Route::middleware(['auth', 'role:member'])->prefix('member')->name('member.')->group(function () {
    Route::get('/unilevel/dashboard', [UnilevelController::class, 'dashboard'])->name('unilevel.dashboard');
    Route::get('/unilevel/genealogy', [UnilevelController::class, 'genealogy'])->name('unilevel.genealogy');
    Route::get('/unilevel/bonuses', [UnilevelController::class, 'bonuses'])->name('unilevel.bonuses');
});
```

**Views**: `resources/views/member/unilevel/`
- `dashboard.blade.php` - Unilevel overview and statistics
- `genealogy.blade.php` - Visual tree of 5-level downlines
- `bonuses.blade.php` - Unilevel bonus history and earnings

Features:
- Interactive tree visualization (5 levels deep)
- Color-coded active/inactive members
- Per-level statistics (count, active count, total purchases)
- Searchable member list
- Earnings summary per level

---

## Phase 4: Unilevel Bonus Calculation & Distribution

**Status**: Not Started
**Estimated Development Time**: 12-16 hours
**Prerequisites**: Phase 3 complete

### Objectives

1. Calculate unilevel bonuses on product purchases
2. Distribute bonuses to active upline members (5 levels)
3. Record bonus transactions in wallet
4. Handle edge cases (inactive members, insufficient levels)
5. Integrate with existing transaction system

### Implementation Details

#### 4.1 Unilevel Bonus Service

**File**: `app/Services/UnilevelBonusService.php`

```php
<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Transaction;
use App\Models\UnilevelBonus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnilevelBonusService
{
    protected UnilevelTreeService $treeService;

    public function __construct(UnilevelTreeService $treeService)
    {
        $this->treeService = $treeService;
    }

    /**
     * Process unilevel bonuses for a completed order
     */
    public function processOrderBonuses(Order $order): array
    {
        // Only process for paid orders
        if (!$order->isPaid()) {
            return ['success' => false, 'message' => 'Order is not paid'];
        }

        // Get product items only (skip packages)
        $productItems = $order->orderItems()->where('item_type', 'product')->get();

        if ($productItems->isEmpty()) {
            return ['success' => true, 'message' => 'No products in order', 'bonuses' => []];
        }

        $buyer = $order->user;
        $totalBonusesDistributed = 0;
        $bonusRecords = [];

        DB::beginTransaction();
        try {
            foreach ($productItems as $orderItem) {
                $itemBonuses = $this->distributeProductBonuses($buyer, $orderItem, $order);
                $bonusRecords = array_merge($bonusRecords, $itemBonuses);
                $totalBonusesDistributed += array_sum(array_column($itemBonuses, 'amount'));
            }

            DB::commit();

            Log::info('Unilevel bonuses processed', [
                'order_id' => $order->id,
                'buyer_id' => $buyer->id,
                'total_bonuses' => $totalBonusesDistributed,
                'bonus_count' => count($bonusRecords),
            ]);

            return [
                'success' => true,
                'message' => 'Bonuses distributed successfully',
                'bonuses' => $bonusRecords,
                'total_amount' => $totalBonusesDistributed,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unilevel bonus distribution failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Distribute bonuses for a single product purchase
     */
    protected function distributeProductBonuses(User $buyer, OrderItem $orderItem, Order $order): array
    {
        $product = $orderItem->product;
        if (!$product) {
            return [];
        }

        // Get active upline members (max 5 levels)
        $uplineMembers = $this->treeService->getActiveUnilevelUpline($buyer, 5);

        if ($uplineMembers->isEmpty()) {
            return [];
        }

        $bonusRecords = [];
        $unilevelSettings = $product->unilevelSettings()
            ->where('is_active', true)
            ->orderBy('level')
            ->get()
            ->keyBy('level');

        foreach ($uplineMembers as $uplineMember) {
            $level = $uplineMember->unilevel_level;
            $setting = $unilevelSettings->get($level);

            if (!$setting) {
                continue;
            }

            // Get fixed bonus amount for this level
            $bonusAmount = $setting->bonus_amount;

            // Multiply by quantity purchased
            $totalBonus = $bonusAmount * $orderItem->quantity;

            // Create bonus record
            $bonus = $this->createBonusRecord(
                $uplineMember,
                $buyer,
                $order,
                $orderItem,
                $level,
                $totalBonus
            );

            $bonusRecords[] = [
                'bonus_id' => $bonus->id,
                'recipient_id' => $uplineMember->id,
                'recipient_name' => $uplineMember->username,
                'level' => $level,
                'amount' => $totalBonus,
            ];
        }

        return $bonusRecords;
    }

    /**
     * Create bonus record and credit wallet
     */
    protected function createBonusRecord(
        User $recipient,
        User $buyer,
        Order $order,
        OrderItem $orderItem,
        int $level,
        float $amount
    ): UnilevelBonus {
        // Create bonus record
        $bonus = UnilevelBonus::create([
            'recipient_id' => $recipient->id,
            'buyer_id' => $buyer->id,
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'product_id' => $orderItem->product_id,
            'level' => $level,
            'bonus_amount' => $amount,
            'status' => 'completed',
            'processed_at' => now(),
            'metadata' => [
                'product_name' => $orderItem->product->name,
                'product_price' => $orderItem->price,
                'quantity' => $orderItem->quantity,
                'order_number' => $order->order_number,
            ],
        ]);

        // Create transaction record
        Transaction::create([
            'user_id' => $recipient->id,
            'type' => 'unilevel_bonus',
            'amount' => $amount,
            'status' => 'completed',
            'description' => "Unilevel Bonus Level {$level} - {$orderItem->product->name}",
            'metadata' => [
                'bonus_id' => $bonus->id,
                'order_id' => $order->id,
                'buyer_id' => $buyer->id,
                'level' => $level,
                'product_id' => $orderItem->product_id,
            ],
        ]);

        // Credit wallet (AUTOMATIC dual-crediting: tracking + withdrawable)
        $wallet = $recipient->wallet;
        $wallet->addUnilevelBonus($amount);  // Credits both unilevel_balance AND withdrawable_balance!

        return $bonus;
    }

    /**
     * Calculate potential earnings for a member
     */
    public function calculatePotentialEarnings(User $member, float $averageProductPrice = 500): array
    {
        $downlines = $this->treeService->getActiveUnilevelDownline($member, 5);
        $downlinesPerLevel = $this->treeService->countDownlinePerLevel($member, 5);

        // Assume average 4% per level
        $earnings = [];
        for ($level = 1; $level <= 5; $level++) {
            $count = $downlinesPerLevel[$level] ?? 0;
            $bonusPerPurchase = $averageProductPrice * 0.04; // 4%
            $monthlyPurchases = 2; // Assume 2 purchases per member per month

            $earnings[$level] = [
                'downlines' => $count,
                'bonus_per_purchase' => $bonusPerPurchase,
                'estimated_monthly' => $count * $bonusPerPurchase * $monthlyPurchases,
            ];
        }

        return [
            'per_level' => $earnings,
            'total_monthly' => array_sum(array_column($earnings, 'estimated_monthly')),
        ];
    }
}
```

#### 4.2 Unilevel Bonus Model

**Migration**: `database/migrations/YYYY_MM_DD_create_unilevel_bonuses_table.php`

```php
Schema::create('unilevel_bonuses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
    $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
    $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
    $table->tinyInteger('level'); // 1-5
    $table->decimal('bonus_amount', 10, 2);
    $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
    $table->timestamp('processed_at')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();

    $table->index(['recipient_id', 'created_at']);
    $table->index(['buyer_id', 'created_at']);
    $table->index(['order_id']);
    $table->index(['level', 'status']);
});
```

**Model**: `app/Models/UnilevelBonus.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnilevelBonus extends Model
{
    protected $fillable = [
        'recipient_id',
        'buyer_id',
        'order_id',
        'order_item_id',
        'product_id',
        'level',
        'bonus_amount',
        'status',
        'processed_at',
        'metadata',
    ];

    protected $casts = [
        'level' => 'integer',
        'bonus_amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return currency($this->bonus_amount);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByRecipient($query, int $userId)
    {
        return $query->where('recipient_id', $userId);
    }

    public function scopeByLevel($query, int $level)
    {
        return $query->where('level', $level);
    }
}
```

#### 4.3 Transaction Type Enhancement

**Migration**: Add `unilevel_bonus` to transaction types

```php
// Update Transaction model
const TYPE_UNILEVEL_BONUS = 'unilevel_bonus';

// Update database enum
DB::statement("ALTER TABLE transactions MODIFY type ENUM(
    'top_up',
    'purchase',
    'commission',
    'withdrawal',
    'payment',
    'refund',
    'unilevel_bonus'
) NOT NULL");
```

**Transaction Types Summary**:
- `commission` - MLM commissions from package purchases → `mlm_balance` + `withdrawable_balance` (automatic)
- `unilevel_bonus` - Unilevel bonuses from product purchases → `unilevel_balance` + `withdrawable_balance` (automatic)
- `withdrawal` - Withdrawals from `withdrawable_balance` → bank account
- `payment` - Purchases using wallet funds
- `refund` - Refunds to `purchase_balance`

#### 4.4 Integration with Checkout

**Update**: `app/Http/Controllers/CheckoutController.php`

After successful payment and order completion:

```php
use App\Services\UnilevelBonusService;

public function processPayment(Request $request, WalletPaymentService $walletService, UnilevelBonusService $unilevelService)
{
    // ... existing payment logic ...

    if ($paymentResult['success']) {
        // Process MLM commission (for packages)
        if ($hasMLMPackages) {
            app(MLMCommissionService::class)->processOrderCommissions($order);
        }

        // Process Unilevel bonuses (for products)
        if ($hasProducts) {
            $unilevelService->processOrderBonuses($order);
        }

        // ... rest of logic ...
    }
}
```

#### 4.5 Admin Unilevel Management

**Controller**: `app/Http/Controllers/Admin/AdminUnilevelController.php`

**Routes**:
```php
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/unilevel/dashboard', [AdminUnilevelController::class, 'dashboard'])->name('unilevel.dashboard');
    Route::get('/unilevel/bonuses', [AdminUnilevelController::class, 'bonuses'])->name('unilevel.bonuses');
    Route::get('/unilevel/reports', [AdminUnilevelController::class, 'reports'])->name('unilevel.reports');
    Route::post('/unilevel/recalculate/{order}', [AdminUnilevelController::class, 'recalculate'])->name('unilevel.recalculate');
});
```

**Views**: `resources/views/admin/unilevel/`
- `dashboard.blade.php` - Overview of unilevel system
- `bonuses.blade.php` - All bonus transactions with filters
- `reports.blade.php` - Analytics and reporting

Features:
- Total bonuses distributed (daily, weekly, monthly)
- Top earners by level
- Product-wise bonus distribution
- Recalculate bonuses (for corrections)
- Export to CSV/Excel

---

## Phase 5: Testing, Analytics & Optimization

**Status**: Not Started
**Estimated Development Time**: 10-12 hours
**Prerequisites**: Phase 4 complete

### Objectives

1. Comprehensive testing of unilevel bonus calculation
2. Member dashboard enhancements
3. Analytics and reporting
4. Performance optimization
5. Documentation

### Testing Scenarios

#### 5.1 Functional Testing

**Test Case 1: Basic Unilevel Bonus Distribution**

Scenario:
- User A sponsors User B
- User B sponsors User C
- User C sponsors User D
- User D sponsors User E
- User E sponsors User F
- All users are MLM active
- User F purchases Product X (₱500) with configured bonuses: Level 1 = ₱20, Levels 2-5 = ₱10 each

Expected Result:
- User E receives ₱20 (Level 1, direct sponsor)
- User D receives ₱10 (Level 2)
- User C receives ₱10 (Level 3)
- User B receives ₱10 (Level 4)
- User A receives ₱10 (Level 5)
- Total bonuses: ₱60 (12% of ₱500)

**Test Case 2: Inactive Member Skipping**

Scenario:
- Same structure as Test Case 1
- User C is inactive (hasn't purchased any MLM package)
- User F purchases Product X (₱500) with bonuses: Level 1 = ₱20, Levels 2-5 = ₱10 each

Expected Result:
- User E receives ₱20 (Level 1)
- User D receives ₱10 (Level 2)
- User C receives ₱0 (INACTIVE, skipped)
- User B receives ₱0 (Level 4 from F, but C blocked the chain)
- User A receives ₱0 (Level 5 from F, but C blocked the chain)

**Test Case 3: Mixed Cart (Packages + Products)**

Scenario:
- User X purchases: 1x Starter Package (₱1,000) + 2x Herbal Tea (₱250 each)
- User X has 5 active upline members
- Herbal Tea bonuses: Level 1 = ₱10, Levels 2-5 = ₱5 each

Expected Result:
- MLM commission triggered for package: ₱200 + ₱50 + ₱50 + ₱50 + ₱50 = ₱400 total
- Unilevel bonus triggered for products: (₱10 + ₱5 + ₱5 + ₱5 + ₱5) × 2 teas = ₱60 total
- Both systems operate independently and credit to same `mlm_balance`

**Test Case 4: Multiple Products with Different Bonus Structures**

Scenario:
- Product A: ₱1,000, Level 1 = ₱50, Levels 2-5 = ₱15 each
- Product B: ₱500, Level 1 = ₱30, Levels 2-5 = ₱10 each
- User purchases 1x Product A + 1x Product B

Expected Result:
- Upline Level 1 receives ₱50 (from Product A) + ₱30 (from Product B) = ₱80
- Upline Level 2 receives ₱15 (from Product A) + ₱10 (from Product B) = ₱25
- And so on for remaining levels

**Test Case 5: Quantity Multiplier**

Scenario:
- Product X: ₱500, Level 1 = ₱20, Levels 2-5 = ₱10 each
- User purchases 5x Product X (₱2,500 total)

Expected Result:
- Upline Level 1 receives ₱20 × 5 = ₱100
- Upline Level 2 receives ₱10 × 5 = ₱50
- Upline Level 3 receives ₱10 × 5 = ₱50
- Upline Level 4 receives ₱10 × 5 = ₱50
- Upline Level 5 receives ₱10 × 5 = ₱50
- Total bonuses: ₱300

#### 5.2 Edge Cases

1. **Orphan User (No Sponsor)**: Should not trigger any unilevel bonuses
2. **Insufficient Upline Levels**: If user has only 2 upline members, only 2 bonuses distributed
3. **Deleted Product**: Bonus record preserved with product_snapshot
4. **Refunded Order**: Unilevel bonuses are NOT reversed (policy decision)
5. **Suspended Member**: Cannot receive bonuses until reactivated

#### 5.3 Performance Testing

- Test with 1,000+ simultaneous product purchases
- Measure bonus calculation time (should be < 500ms per order)
- Database query optimization (eager loading, caching)
- Queue bonus calculations for high-volume scenarios

### Analytics & Reporting

#### 5.4 Member Dashboard Enhancements

**File**: `resources/views/member/unilevel/dashboard.blade.php`

Widgets:
1. **Total Unilevel Earnings** (lifetime, this month, today)
2. **Active Downlines by Level** (visual chart)
3. **Recent Bonus Transactions** (last 10)
4. **Top Performing Products** (by bonus earnings)
5. **Earnings Trend** (last 6 months chart)
6. **Potential Monthly Earnings** (based on current downline activity)

#### 5.5 Admin Analytics

**File**: `resources/views/admin/unilevel/reports.blade.php`

Reports:
1. **Unilevel Performance Dashboard**
   - Total bonuses distributed (all-time, monthly, weekly)
   - Average bonus per member
   - Most purchased products
   - Top earners

2. **Product Performance Report**
   - Products by total bonus generated
   - Repeat purchase rate
   - Average quantity per order

3. **Member Activity Report**
   - Active vs inactive members
   - Downline distribution (how many members have 5 levels vs 1 level)
   - Average downline size

4. **Financial Report**
   - Total product revenue
   - Total bonuses paid out
   - Net margin (revenue - bonuses)
   - Bonus-to-revenue ratio

### Optimization

#### 5.6 Caching Strategy

Cache the following:
- Unilevel downline count per member (refresh daily)
- Product unilevel settings (refresh on update)
- Active member status (refresh on package purchase)

#### 5.7 Queue Jobs

**File**: `app/Jobs/ProcessUnilevelBonusesJob.php`

For high-volume scenarios, queue bonus processing:

```php
ProcessUnilevelBonusesJob::dispatch($order)->onQueue('bonuses');
```

Benefits:
- Non-blocking checkout process
- Handles failures gracefully
- Retry mechanism for failed calculations

#### 5.8 Database Indexing

Ensure proper indexes:
```sql
CREATE INDEX idx_users_sponsor_mlm ON users(sponsor_id, mlm_status);
CREATE INDEX idx_unilevel_bonuses_recipient ON unilevel_bonuses(recipient_id, created_at);
CREATE INDEX idx_order_items_product ON order_items(product_id, item_type);
CREATE INDEX idx_products_active ON products(is_active, is_unilevel_product);
```

### Documentation

#### 5.9 User Documentation

**File**: `UNILEVEL_USER_GUIDE.md`

Contents:
- What is the Unilevel System?
- How to earn Unilevel bonuses
- Active member requirements
- Product catalog and pricing
- How to track your earnings
- FAQs

#### 5.10 Developer Documentation

**File**: `UNILEVEL_DEVELOPER_GUIDE.md`

Contents:
- Architecture overview
- Database schema
- Service classes and methods
- Integration points
- Testing procedures
- Deployment checklist

---

## Implementation Checklist

### Phase 1: Product Management Foundation + Wallet Enhancement
- [ ] **Wallet Enhancement**:
  - [ ] Add unilevel_balance and withdrawable_balance to wallets table
  - [ ] Update Wallet model with 4-balance methods (addMLMCommission, addUnilevelBonus, etc.)
  - [ ] Update Transaction model (add unilevel_bonus type)
  - [ ] Test automatic dual-crediting (MLM/Unilevel → both tracker + withdrawable)
  - [ ] Test deductCombinedBalance() priority system
  - [ ] Update withdrawal controller to only withdraw from withdrawable_balance
- [ ] **Product Management**:
  - [ ] Create products table migration
  - [ ] Create unilevel_settings table migration
  - [ ] Create Product model with relationships
  - [ ] Create UnilevelSetting model
  - [ ] Create AdminProductController (CRUD)
  - [ ] Create AdminUnilevelSettingsController
  - [ ] Create admin product views (index, create, edit, show)
  - [ ] Create unilevel settings configuration view
  - [ ] Create ProductSeeder with sample data
  - [ ] Add routes for admin product management
  - [ ] Test product CRUD operations
  - [ ] Test unilevel bonus configuration

### Phase 2: Cart & Checkout Integration
- [ ] Enhance CartService to support products
- [ ] Update cart item structure (type, item_id)
- [ ] Migrate order_items table (add product_id, item_type)
- [ ] Update OrderItem model with product relationship
- [ ] Create ProductController (public catalog)
- [ ] Create product catalog view (index, show)
- [ ] Update cart view (separate packages/products)
- [ ] Update checkout flow to handle mixed cart
- [ ] Test adding products to cart
- [ ] Test mixed cart (packages + products)
- [ ] Test checkout with products only
- [ ] Test order creation with product items

### Phase 3: Unilevel Tree & Active Status
- [ ] Migrate users table (add mlm_status, mlm_activated_at)
- [ ] Update User model with isActiveForUnilevel() method
- [ ] Create UnilevelTreeService class
- [ ] Implement getUnilevelDownline() method
- [ ] Implement getActiveUnilevelUpline() method
- [ ] Create UnilevelController (member dashboard)
- [ ] Create unilevel dashboard view
- [ ] Create genealogy view (tree visualization)
- [ ] Test unilevel tree queries
- [ ] Test active member filtering
- [ ] Verify performance with large trees (1000+ members)

### Phase 4: Bonus Calculation & Distribution
- [ ] Create unilevel_bonuses table migration
- [ ] Create UnilevelBonus model
- [ ] Create UnilevelBonusService class
- [ ] Implement processOrderBonuses() method
- [ ] Implement distributeProductBonuses() method
- [ ] Add unilevel_bonus transaction type
- [ ] Integrate with CheckoutController
- [ ] Create AdminUnilevelController
- [ ] Create admin unilevel views (dashboard, bonuses, reports)
- [ ] Test bonus calculation (all test cases)
- [ ] Test wallet crediting
- [ ] Test transaction recording
- [ ] Verify bonus distribution accuracy

### Phase 5: Testing & Optimization
- [ ] Run all functional test cases
- [ ] Test edge cases
- [ ] Performance testing (1000+ orders)
- [ ] Implement caching strategy
- [ ] Create ProcessUnilevelBonusesJob
- [ ] Add database indexes
- [ ] Create member analytics dashboard
- [ ] Create admin reporting interface
- [ ] Write user documentation (UNILEVEL_USER_GUIDE.md)
- [ ] Write developer documentation (UNILEVEL_DEVELOPER_GUIDE.md)
- [ ] Create deployment checklist
- [ ] Final integration testing

---

## Configuration Settings

### System Settings

Add to `system_settings` table:

```php
[
    'key' => 'unilevel_enabled',
    'value' => true,
    'description' => 'Enable/disable unilevel bonus system',
],
[
    'key' => 'unilevel_requires_recent_purchase',
    'value' => false,
    'description' => 'Require recent product purchase to remain active',
],
[
    'key' => 'unilevel_active_days_requirement',
    'value' => 90,
    'description' => 'Days since last product purchase to remain active',
],
[
    'key' => 'unilevel_max_levels',
    'value' => 5,
    'description' => 'Maximum levels for unilevel bonus distribution',
],
```

### Withdrawal System Updates

With the new 4-balance structure, the withdrawal system must be updated:

**Before** (2-balance system):
- Members could withdraw from `mlm_balance` directly
- `purchase_balance` was non-withdrawable

**After** (4-balance system with automatic crediting):
- Earnings are AUTOMATICALLY credited to `withdrawable_balance` (instant!)
- Members can ONLY withdraw from `withdrawable_balance`
- No manual transfer needed - earnings are instantly withdrawable
- `purchase_balance` remains non-withdrawable

**Update Withdrawal Controller**:
```php
// app/Http/Controllers/Member/WithdrawalController.php

public function create()
{
    $wallet = auth()->user()->wallet;

    // Only show withdrawable_balance as available
    $availableForWithdrawal = $wallet->withdrawable_balance;

    return view('member.withdrawal.create', compact('wallet', 'availableForWithdrawal'));
}

public function store(Request $request)
{
    $wallet = auth()->user()->wallet;

    // Validate amount against withdrawable_balance only
    $request->validate([
        'amount' => [
            'required',
            'numeric',
            'min:500',
            "max:{$wallet->withdrawable_balance}"
        ]
    ]);

    // Process withdrawal from withdrawable_balance
    // ... existing withdrawal logic
}
```

**Key Change**: Since earnings are automatically added to `withdrawable_balance`, there's no waiting period or approval needed before withdrawal. The withdrawal approval system remains for the actual cash-out to bank.

### Product Categories

Suggested categories:
- Supplements
- Beverages
- Topical Products
- Herbal Remedies
- Wellness Kits
- Accessories

---

## Security Considerations

1. **Bonus Manipulation Prevention**
   - Validate upline relationships before distribution
   - Prevent self-referral bonuses
   - Audit trail for all bonus calculations

2. **Transaction Integrity**
   - Use database transactions for bonus distribution
   - Rollback on any failure
   - Log all bonus calculations

3. **Access Control**
   - Only active members view unilevel dashboard
   - Admin-only access to bonus recalculation
   - Rate limiting on bonus queries

4. **Data Privacy**
   - Members can only view their own downline
   - Anonymize buyer information in bonus records
   - GDPR compliance for genealogy data

---

## Future Enhancements (Post-Phase 5)

1. **Unilevel Rank System**
   - Bronze, Silver, Gold, Platinum ranks based on downline size and sales
   - Rank-based bonus multipliers (e.g., Platinum gets 5% instead of 4%)

2. **Product Subscription System**
   - Auto-ship recurring orders
   - Guaranteed monthly bonuses for subscription downlines

3. **Unilevel Leaderboard**
   - Top earners by month
   - Fastest growing teams
   - Most active downlines

4. **Mobile App Integration**
   - Real-time push notifications for bonuses
   - Mobile genealogy tree view
   - Product catalog in mobile app

5. **Gamification**
   - Achievement badges (e.g., "5 Levels Deep", "100 Active Downlines")
   - Bonus milestones with rewards
   - Team challenges and contests

---

## Deployment Checklist

### Pre-Deployment
- [ ] Run all migrations on staging environment
- [ ] Seed products and unilevel settings
- [ ] Run full test suite
- [ ] Verify bonus calculations manually
- [ ] Check database indexes
- [ ] Review security settings
- [ ] Backup production database

### Deployment
- [ ] Enable maintenance mode
- [ ] Run migrations on production
- [ ] Deploy new code
- [ ] Clear all caches
- [ ] Run seeders (products, settings)
- [ ] Verify cron jobs (if using queues)
- [ ] Disable maintenance mode

### Post-Deployment
- [ ] Test product catalog (public view)
- [ ] Test product purchase flow
- [ ] Verify bonus distribution on test order
- [ ] Monitor logs for errors
- [ ] Check wallet transactions
- [ ] Verify member dashboard loads correctly
- [ ] Announce new feature to users

---

## Support & Maintenance

### Monitoring

Monitor the following metrics:
- Bonus distribution success rate
- Average bonus processing time
- Failed bonus calculations
- Wallet balance accuracy
- Member activation rate

### Common Issues & Solutions

**Issue**: Bonuses not distributed
**Solution**: Check member active status, verify upline relationships, review transaction logs

**Issue**: Incorrect bonus amounts
**Solution**: Verify unilevel settings per product, check calculation logic, recalculate if needed

**Issue**: Slow bonus processing
**Solution**: Implement queue jobs, add database indexes, enable caching

**Issue**: Wallet balance mismatch
**Solution**: Run wallet reconciliation script, verify transaction history, check for duplicate bonuses

---

## Conclusion

The Unilevel System provides a powerful, scalable compensation structure that complements the existing Binary MLM system. By separating packages (one-time, expensive, binary-based) from products (recurring, consumable, unilevel-based), the platform offers members multiple income streams and encourages ongoing product consumption.

**Key Benefits:**
- **Passive Income**: Earn from downline repeat purchases
- **Simple Structure**: 5-level direct line (no placement strategy needed)
- **Recurring Revenue**: Products encourage monthly repurchases
- **Member Retention**: Active status requirement incentivizes engagement
- **Scalability**: Efficient database design supports large trees

**Next Steps:**
Begin with Phase 1 (Product Management Foundation) and proceed sequentially through each phase. Each phase is self-contained and can be tested independently before moving to the next.

---

**Document Version**: 1.0
**Last Updated**: 2025-10-10
**Author**: System Architect
**Status**: Ready for Implementation
