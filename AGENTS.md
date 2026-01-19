# GAWIS2 Development Guidelines for AI Agents

This repository is a Laravel 12 MLM e-commerce platform with complex commission distribution, wallet systems, and rank management.

## Development Commands

### Essential Commands
```bash
# Start development environment (Laravel server + queue + logs + Vite)
composer dev

# Run all tests
composer test
# Or
php artisan test

# Run specific test
php artisan test --filter testMethodName
php artisan test --filter TestClassName

# Run tests in a specific file
php artisan test tests/Feature/YourTest.php

# Run only unit or feature tests
php artisan test --testsuite Unit
php artisan test --testsuite Feature

# Frontend build
npm run build    # Production build
npm run dev      # Vite dev server

# Code formatting (Laravel Pint)
./vendor/bin/pint

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## Code Style Guidelines

### PHP/Laravel Conventions

**Imports:**
- One class per use statement
- Group imports: external libraries first, then internal app classes
- Sort alphabetically within groups
- Always use fully qualified class names in type hints

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Services\WalletPaymentService;
```

**Naming Conventions:**
- Classes: `PascalCase` (e.g., `WalletPaymentService`)
- Methods: `camelCase` (e.g., `processPayment`)
- Variables: `camelCase` (e.g., `$userId`)
- Constants: `UPPER_SNAKE_CASE` (e.g., `STATUS_PENDING`)
- Database tables: `snake_case` (e.g., `mlm_settings`)
- Models: singular PascalCase (e.g., `User`, `Order`)

**Type Hints:**
- Always declare return types on methods (PHP 8.2+)
- Use specific types over generic `array` when possible
- Use `void` for methods with no return value
- Use `bool` instead of `boolean`
- Use `int` instead of `integer`
- Use `float` for decimals

```php
public function processPayment(Order $order): array
public function isNetworkActive(): bool
public function activateNetwork(): void
```

**Model Conventions:**
- Use class constants for status values
- Use `$fillable` for mass assignment (never `$guarded`)
- Always define `$casts` for datetime and decimal fields
- Use relationship type hints (`BelongsTo`, `HasMany`, etc.)

```php
protected $fillable = ['name', 'email'];

protected $casts = [
    'created_at' => 'datetime',
    'amount' => 'decimal:2',
];

public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

**Service Layer:**
- All business logic belongs in `app/Services/`
- Inject services via constructor, never use `app()` helper
- Use dependency injection for all external dependencies
- Services should be stateless

```php
class WalletPaymentService
{
    public function __construct(
        private NotificationService $notifications,
        private ActivityLog $activityLog
    ) {}
}
```

**Database Transactions:**
- Always use `DB::transaction()` for multi-step operations
- Lock rows when updating balances: `Wallet::lockForUpdate()->first()`
- Never nest transactions

```php
DB::transaction(function () {
    $wallet->increment('mlm_balance', $amount);
    Transaction::create([...]);
});
```

**Error Handling:**
- Throw descriptive exceptions: `throw new \Exception('Insufficient balance')`
- Use try-catch around external API calls
- Log errors before re-throwing
- Never expose internal details to users

**Controller Conventions:**
- Thin controllers - delegate to services
- Return proper response types (JSON for API, views for web)
- Validate requests using FormRequest classes
- Use route model binding

**Testing Conventions:**
- Feature tests in `tests/Feature/`, unit tests in `tests/Unit/`
- Use descriptive test names: `test_user_can_checkout_with_wallet()`
- Arrange-Act-Assert pattern
- Use factories for test data
- Always assert both success and failure paths

```php
public function test_user_can_checkout_with_wallet(): void
{
    // Arrange
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    // Act
    $response = $this->actingAs($user)
        ->post(route('checkout.process'), ['order_id' => $order->id]);

    // Assert
    $response->assertStatus(200);
    $this->assertDatabaseHas('orders', ['status' => 'confirmed']);
}
```

### Blade/HTML Conventions

- Use modern Tailwind classes (v4): `grow` instead of `flex-grow-1`
- Use `match()` expressions for conditionals
- Organize with sections and components
- Include meta tags in page head

### Frontend Conventions

- Use Vite for asset bundling
- Axios for HTTP requests
- Tailwind CSS v4 for styling

## Critical Business Logic

### Dual-Path Rank Advancement System

**Two Independent Advancement Paths:**
- **Path A (Recruitment-based):** Meet `required_direct_sponsors` (default 2)
- **Path B (PV-based):** Meet `required_sponsors_ppv_gpv` (default 4) + `ppv_required` + `gpv_required`
- First path to succeed wins the advancement

**Personal Points Volume (PPV):**
- Credits to user when they make purchases
- Based on `product.points_awarded * quantity`
- Resets to 0 on rank advancement
- Tracked in `points_tracker` table

**Group Points Volume (GPV):**
- Credits to buyer (as their own GPV) + ALL uplines
- Recursive upline credit (indefinite depth, no level limit)
- Resets to 0 on rank advancement
- Tracked in `points_tracker` table

**Point Processing:**
- Triggers on order completion (status = 'confirmed')
- PointsService::processOrderPoints() processes order items
- Calls PointsService::creditPPV() and PointsService::creditGPVToUplines()
- Wrapped in database transaction for atomicity

**Rank Advancement Criteria:**
- Path A: `same_rank_sponsors >= required_direct_sponsors`
- Path B: `rank_pv_enabled = true` AND `same_rank_sponsors >= required_sponsors_ppv_gpv` AND `current_ppv >= ppv_required` AND `current_gpv >= gpv_required`
- Both paths checked in RankAdvancementService::checkAndTriggerAdvancement()
- PPV/GPV reset synchronously on ANY rank advancement

### MLM Commission Distribution
- Triggered synchronously on order payment via `ProcessMLMCommissions::dispatchSync()`
- Distributes to 5 upline levels: L1 (₱200), L2-L5 (₱50 each)
- Must validate `User::isNetworkActive()` before crediting
- Creates `Transaction` records with `type='mlm_commission'`

### Wallet System
- Two balances: `mlm_balance` (lifetime) + `purchase_balance` (withdrawable)
- Auto-credit both on MLM commissions
- Deduct from `purchase_balance` first, then `mlm_balance`
- Always check `wallet->is_active` before operations

### Order Workflow
- Order statuses: pending → confirmed → packing → delivery → completed
- MLM commissions and rank advancement trigger on "confirmed" status
- Load relationships: `Order::load('orderItems.package')` before processing

## Common Pitfalls

1. **Always check network status**: Use `$user->isNetworkActive()` before crediting commissions
2. **Wallet auto-creation**: Use `$user->getOrCreateWallet()`, not direct creation
3. **Email verification**: Check `email_verified_at` before sending emails
4. **Synchronous processing**: Commissions execute immediately (~500ms for 5 levels)
5. **Duplicate prevention**: Check existing transactions before creating new ones
6. **Circular references**: Validate sponsor relationships before saving users

## Key Files

### Dual-Path Rank Advancement
- `app/Services/PointsService.php` - All point logic (PPV/GPV)
- `app/Services/RankAdvancementService.php` - Dual-path advancement
- `app/Models/PointsTracker.php` - Point audit trail
- `resources/views/dashboard.blade.php` - Progress display
- `resources/views/admin/ranks/configure.blade.php` - Admin configuration

**Default Configuration:**
- PPV thresholds: 0, 100, 300, 500, 800, 1200, 2000
- GPV thresholds: 0, 1000, 5000, 15000, 40000, 100000, 250000
- Sponsors: 2 (recruitment) vs 4 (PV-based)

### MLM Commission System
- `app/Services/MLMCommissionService.php` - Core commission logic
- `app/Services/WalletPaymentService.php` - Payment processing
- `app/Models/Wallet.php` - Balance management
- `app/Models/Order.php` - Order lifecycle
- `config/mlm-settings.php` - Commission configuration

## Testing Data

Access `/reset` to create seeded database with:
- Admin: admin@admin.com / admin
- Test members with sponsor relationships for MLM testing

## Running Linters

Always run Laravel Pint before committing:
```bash
./vendor/bin/pint
```

Then run tests to ensure nothing broke:
```bash
composer test
```

## Database

- Uses SQLite (`:memory:`) for tests
- Check `phpunit.xml` for test configuration
- Use factories and seeders for consistent test data

## Data Migration

**PPV/GPV Migration:**
- Migration `2026_01_19_205840_ensure_ppv_gpv_defaults_for_existing_data` sets defaults for existing data
- Packages: Default `ppv_required=0`, `gpv_required=0`, `required_sponsors_ppv_gpv=4`, `rank_pv_enabled=false`
- Users: Default `current_ppv=0`, `current_gpv=0`, `ppv_gpv_updated_at=NULL`
- All seeded packages have proper PPV/GPV values set
- All seeded users have PPV/GPV initialized to 0
