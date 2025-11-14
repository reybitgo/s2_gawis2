# Pagination Per-Page Selector Implementation Guide

## Overview
This guide outlines how to implement per-page selection across all paginated tables in the application for better UX.

## Already Completed

### ✅ Created Reusable Component
**File**: `resources/views/components/per-page-selector.blade.php`
- Dropdown selector with options: 10, 15, 25, 50, 100
- Auto-submit on change
- Preserves all query parameters
- Resets to page 1 when changing per_page

### ✅ Created Helper Trait
**File**: `app/Http/Traits/HasPaginationLimit.php`
- Method: `getPerPage(Request $request, int $default = 15): int`
- Validates per_page parameter
- Only allows: 10, 15, 25, 50, 100

## Implementation Steps for Each Controller

### Step 1: Add Trait to Controller

```php
use App\Http\Traits\HasPaginationLimit;

class YourController extends Controller
{
    use HasPaginationLimit;

    // ... rest of your code
}
```

### Step 2: Update Pagination Calls

**Before:**
```php
$items = Model::paginate(15);
```

**After:**
```php
$perPage = $this->getPerPage($request, 15); // 15 is the default
$items = Model::paginate($perPage)->appends($request->query());
```

### Step 3: Pass perPage to View

Add `$perPage` to the compact() or view data:

```php
return view('your.view', compact('items', 'perPage'));
```

### Step 4: Add Component to View

Add the component before or after the table, typically above pagination links:

```blade
<div class="d-flex justify-content-between align-items-center mb-3">
    <x-per-page-selector :perPage="$perPage" />
    <div>
        {{-- Other controls like search, filters --}}
    </div>
</div>

{{-- Your table here --}}

{{ $items->links() }}
```

## Files Requiring Updates

### Admin Controllers

#### 1. **AdminController.php**
**Location**: `app/Http/Controllers/Admin/AdminController.php`

**Methods to Update:**
- `walletManagement()` - Line 81 & 118
- `transactionApproval()` - Line 144
- `users()` - Line 254
- `viewLogs()` - Line 679

**Example for walletManagement():**
```php
public function walletManagement(Request $request)
{
    $this->authorize('wallet_management');

    $perPage = $this->getPerPage($request, 20);

    $wallets = User::with(['wallet', 'transactions' => function($query) {
        $query->latest()->limit(5);
    }])->whereHas('roles', function($query) {
        $query->where('name', 'member');
    })->paginate($perPage)->appends($request->query());

    // ... other code ...

    $allTransactions = $transactionsQuery->latest()
        ->paginate($perPage)
        ->appends($request->query());

    return view('admin.wallet-management', compact(
        'wallets',
        // ... other variables ...
        'perPage'  // ADD THIS
    ));
}
```

#### 2. **AdminPackageController.php**
**Location**: `app/Http/Controllers/Admin/AdminPackageController.php`

Look for `->paginate()` calls and update them.

#### 3. **AdminOrderController.php**
**Location**: `app/Http/Controllers/Admin/AdminOrderController.php`

Look for `->paginate()` calls and update them.

#### 4. **AdminReturnController.php**
**Location**: `app/Http/Controllers/Admin/AdminReturnController.php`

Look for `->paginate()` calls and update them.

### Member Controllers

#### 5. **WalletController.php**
**Location**: `app/Http/Controllers/Member/WalletController.php`

Look for `->paginate()` calls in transaction history methods.

#### 6. **OrderHistoryController.php**
**Location**: `app/Http/Controllers/OrderHistoryController.php`

Look for `->paginate()` calls and update them.

### Public Controllers

#### 7. **PackageController.php**
**Location**: `app/Http/Controllers/PackageController.php`

Look for `->paginate()` calls and update them.

## Views Requiring Updates

### Admin Views

1. **admin/logs.blade.php**
2. **admin/users.blade.php**
3. **admin/transaction-approval.blade.php**
4. **admin/wallet-management.blade.php**
5. **admin/packages/index.blade.php**
6. **admin/returns/index.blade.php**
7. **admin/orders/index.blade.php**

### Member Views

8. **member/transactions.blade.php**
9. **orders/index.blade.php**

### Public Views

10. **packages/index.blade.php**

## View Implementation Example

```blade
<!-- Before the table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Your Title</h5>
            <div class="d-flex align-items-center gap-3">
                <x-per-page-selector :perPage="$perPage" />
                {{-- Other controls --}}
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table">
            {{-- table content --}}
        </table>
    </div>
    <div class="card-footer">
        {{ $items->links() }}
    </div>
</div>
```

## Testing Checklist

- [ ] Per-page selector appears on all paginated pages
- [ ] Changing per_page updates the URL correctly
- [ ] Page resets to 1 when changing per_page
- [ ] All query parameters are preserved (filters, search, etc.)
- [ ] Selected value persists after page change
- [ ] Works correctly with filtering/searching
- [ ] Pagination links work correctly with selected per_page

## Best Practices

1. **Default Value**: Use 15 as default for most tables, 20 for compact lists
2. **Query Append**: Always use `->appends($request->query())` to preserve filters
3. **Consistent Placement**: Place selector in top-right of table header
4. **Mobile Responsive**: The component is already mobile-friendly
5. **Accessibility**: Component includes proper labels and ARIA attributes

## Quick Reference

**Add to Controller:**
```php
use App\Http\Traits\HasPaginationLimit;
$perPage = $this->getPerPage($request, 15);
$items = Model::paginate($perPage)->appends($request->query());
return view('view', compact('items', 'perPage'));
```

**Add to View:**
```blade
<x-per-page-selector :perPage="$perPage" />
```

## Notes

- The component automatically handles URL updates
- No JavaScript configuration needed
- Works with existing pagination links
- Fully compatible with Laravel's pagination
- Session storage not used (keeps URLs shareable)
