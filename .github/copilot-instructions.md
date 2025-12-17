# Copilot Instructions for GAWIS2 MLM E-Commerce Platform

## 🏗️ Architecture Overview

This is a Laravel 12 MLM (multi-level marketing) e-commerce platform with complex commission distribution, wallet systems, and rank management.

### Core Subsystems

1. **MLM Commission Engine** (`app/Services/MLMCommissionService.php`)
   - Real-time 5-level upline commission distribution (₱200 L1, ₱50 L2-L5)
   - Triggered synchronously on order payment via `ProcessMLMCommissions::dispatchSync()`
   - Traverses sponsor chain, validates network status, creates transaction records
   - Multi-channel notifications: database + broadcast + conditional email (only verified users)

2. **E-Wallet System** (`app/Models/Wallet.php`)
   - Two balance types: `mlm_balance` (lifetime tracker) + `withdrawable_balance` (instantly usable)
   - MLM commissions auto-credit both balances
   - Used for checkout payment (`WalletPaymentService::processPayment()`)
   - Supports transfers, deposits, and audited withdrawals

3. **Order Lifecycle** (`app/Models/Order.php`)
   - 26-status workflow: pending → confirmed → packing → delivery (home/pickup) → completed/returned
   - Automatic rank advancement and MLM commission on "confirmed" status
   - Order items contain product/package with MLM eligibility flag
   - Refunds via return requests trigger wallet credits

4. **Rank System** (`app/Models/RankAdvancement.php`, `RankAdvancementService.php`)
   - Automatic advancement based on monthly quotas and network criteria
   - Rank-aware MLM commissions: higher ranks = bonus multipliers
   - Quarterly reset cycle with legacy migration support

5. **Unilevel Bonus System** (`app/Services/UnilevelBonusService.php`)
   - Per-product multi-level bonus structure (separate from MLM)
   - Monthly quota-based distribution (NOT real-time like MLM)
   - Calculated differently from MLM commissions

## 🔄 Critical Data Flows

### Order → Commission Distribution Flow
```
CheckoutController::checkout()
  └─> WalletPaymentService::processPayment()  // Deduct wallet
      └─> Order status = "confirmed", payment_status = "paid"
          └─> ProcessMLMCommissions::dispatchSync($order)  // SYNCHRONOUS
              └─> MLMCommissionService::processCommissions()
                  ├─> Load order items, validate MLM packages
                  ├─> Traverse User::sponsor chain (5 levels max)
                  ├─> For each upline: check isNetworkActive()
                  ├─> Wallet::increment('mlm_balance', commission)
                  ├─> Transaction::create([type='mlm_commission', level=$level, ...])
                  └─> User::notify(new MLMCommissionEarned(...))  // Database + Broadcast + Email
```

**Key Patterns:**
- Commission processing is **synchronous** (uses `dispatchSync()`, not queued)
- Network status check is CRITICAL: `$user->isNetworkActive()` blocks upline traversal
- Transactions table tracks `level` (1-5) and `source_order_id` for audit trail
- Email notifications respect `email_verified_at` (prevents spam/bounces)

### Wallet Payment for Orders
```
Wallet::deductCombinedBalance($amount)
  └─> First deduct from general_balance
      └─> If insufficient, deduct remainder from mlm_balance
          └─> Transaction::create([type='purchase', ...])
```

## 📋 Project-Specific Conventions

### Model Relationships
- `User::sponsor` - immediate upline (self-referential)
- `User::wallet()` - one-to-one, auto-created with `getOrCreateWallet()`
- `Order::orderItems()` - has many items with product/package
- `Order::statusHistories()` - audit trail of status changes

### Service Layer
Located in `app/Services/`, injected via constructor:
- `MLMCommissionService` - commission processing (requires `RankComparisonService`)
- `WalletPaymentService` - payment validation and execution
- `UnilevelBonusService` - separate bonus calculations (different business logic)
- `RankAdvancementService` - auto-advancement logic

Avoid direct model manipulation in controllers; use services for business logic.

### Database Transactions
Always use `DB::transaction()` for multi-step operations:
```php
DB::transaction(function () {
    $wallet->increment('mlm_balance', $amount);
    Transaction::create([...]);
    // Rollback on exception
});
```

### Logging
Use `ActivityLog::logMLMCommission()` for audit trail (separate from transactions):
```php
ActivityLog::logMLMCommission($recipient, $amount, $level, $buyer, $order, $packageId, $packageName);
```

### Status Constants
Order statuses are class constants: `Order::STATUS_CONFIRMED`, `Order::STATUS_PENDING`, etc.
Always use constants, not strings, to prevent typos.

## 🔌 Integration Points & External Dependencies

- **Laravel Fortify** - 2FA authentication
- **Spatie Laravel Permission** - role-based access (admin, member)
- **Laravel Echo/Pusher** - real-time notifications (optional, for broadcast channel)
- **Queue System** - Laravel Queue for async email sending (configured in `.env`)
- **Mail Service** - SMTP configuration for email notifications (`.env`: MAIL_*)

**Critical Integrations:**
- Commissions must validate email verification before sending emails
- Broadcast notifications require Echo configuration (optional feature)
- No queue worker needed: commission processing uses synchronous dispatch

## 🚀 Developer Workflows

### Starting Development
```bash
composer dev  # Starts: Laravel server + queue worker + log viewer + Vite
```

### Database Reset
Navigate to `/reset` in browser for seeded database with test users:
- Admin user (admin@admin.com / admin)
- Test members with sponsor relationships for MLM testing

### Testing Commission Flow
Use included test scripts:
```bash
php test_full_commission.php          # Full 5-level distribution
php test_real_mlm_scenario.php        # Real-world scenario
php verify_mlm_type_usage.php         # Check transaction types
```

### Debugging MLM Issues
```bash
php artisan debug:mlm-commission      # Commission system status
php artisan activity:logs             # View activity logs with filters
```

### Key Test Files (in root)
- `test_full_commission.php` - End-to-end 5-level commission test
- `create_test_hierarchy.php` - Create test user hierarchy
- `test_phase*.php` - Phase-specific testing
- `check_*.php` - Verification scripts

## ⚠️ Common Pitfalls & Best Practices

1. **Network Status Check Required**
   - Always validate `User::isNetworkActive()` before crediting commissions
   - Skipping this breaks MLM system; document when bypassing

2. **Wallet Auto-Creation**
   - Use `User::getOrCreateWallet()`, not direct `Wallet::create()`
   - Some users may have no wallet initially

3. **Email Verification Matters**
   - Only send emails to users with `email_verified_at` NOT NULL
   - Check this before queuing email notifications

4. **Order Item Relationships**
   - Always load with `Order::load('orderItems.package')` before processing
   - MLM packages flagged with `package->is_mlm_package` or `package->max_mlm_levels`

5. **Synchronous Commission Processing**
   - Commissions execute immediately (blocks checkout ~500ms for 5 levels)
   - No background queue needed; failure blocks order completion
   - On error: transaction rolls back, order incomplete, user retries

6. **Duplicate Prevention**
   - Check `Transaction::where('source_order_id', $order->id)->exists()` before processing
   - DB transaction rollback prevents duplicates on retry

## 📁 Key Files Reference

| Path | Purpose |
|------|---------|
| `app/Services/MLMCommissionService.php` | Core commission logic |
| `app/Jobs/ProcessMLMCommissions.php` | Sync commission job |
| `app/Models/Wallet.php` | Wallet balance management |
| `app/Models/Transaction.php` | Audit trail for all wallet ops |
| `app/Http/Controllers/CheckoutController.php` | Order payment entry point |
| `app/Services/WalletPaymentService.php` | Payment processing |
| `config/mlm-settings.php` | Commission configuration |
| `database/migrations/*mlm*` | MLM schema |

## 🛠️ Configuration Files
- `.env` - Mail settings (MAIL_*), database credentials
- `config/queue.php` - Async job configuration (usually 'sync' in dev)
- `config/mail.php` - Email sending configuration

---

**Last Updated**: December 2025 | Framework: Laravel 12 | PHP: 8.2+
