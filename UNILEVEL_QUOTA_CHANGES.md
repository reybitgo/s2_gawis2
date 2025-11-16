# Unilevel Quota System - Implementation Changes

## Summary of Modifications

This document tracks the adjustments made to the original `UNILEVEL_QUOTA.md` plan based on existing database schema and requirement for decimal precision.

---

## Key Changes Made

### 1. Modify Existing `points_awarded` Column to Decimal

**Original Plan**: Create new `point_value` column in products table  
**Updated Plan**: Modify existing `points_awarded` column from integer to decimal(10,2)

**Rationale**:
- Products table already has `points_awarded` column (currently integer)
- Originally intended for loyalty/rewards system
- Repurposed for PV (Personal Volume) tracking
- **CHANGE TO DECIMAL**: Need fractional PV values for flexible point allocation
- Allows values like 0.5, 1.25, 10.50 for more precise control

**Field Mapping**:
```
Database Column: points_awarded
Current Type: integer
New Type: decimal(10,2)
Purpose: PV points for monthly quota system
Usage: $product->points_awarded (returns float)
Supports: 0.5, 1.25, 10.50, etc.
Admin Edit: Available at /admin/products/{slug}/edit (already exists)
```

---

### 2. Data Type Consistency - Decimal for Precision

**Changed**: All PV-related fields use `decimal(10,2)` for fractional precision

**Affected Tables**:
- ⚠️ `products.points_awarded` - **MODIFY from integer to decimal(10,2)**
- ✅ `packages.monthly_quota_points` - New column: decimal(10,2)
- ✅ `monthly_quota_tracker.total_pv_points` - New column: decimal(10,2)
- ✅ `monthly_quota_tracker.required_quota` - New column: decimal(10,2)

**Rationale**:
- **Decimal precision needed** for flexible PV allocation
- Allows fractional values: 0.5, 1.25, 10.50, etc.
- Better granularity for point assignment
- Can reward smaller purchases with fractional PV
- Business requirement: More precise control over point values

---

### 3. Migration Count

**Original Plan**: 3 migrations
1. Add `point_value` to products
2. Add quota fields to packages
3. Create `monthly_quota_tracker` table

**Final Plan**: 3 migrations total
1. **MODIFY `points_awarded` in products** from integer to decimal(10,2) ✅
2. Add `monthly_quota_points` (decimal) and `enforce_monthly_quota` to packages ✅
3. Create `monthly_quota_tracker` table with decimal PV fields ✅

---

### 4. Code Changes Throughout Document

**Search & Replace Operations**:
- `point_value` → `points_awarded`
- All PV casts: Keep as `decimal:2`
- All PV migrations: Use `decimal('field', 10, 2)`
- Validation rules: `numeric|min:0|max:9999.99` (keep decimal validation)
- Display formatting: `number_format($pv, 2)` (show 2 decimal places)
- HTML inputs: `step="0.01"` (allow decimal input)

**Files Updated**:
- Phase 1: Database migrations
- Phase 1: Model updates (Product, Package, MonthlyQuotaTracker, User)
- Phase 2: MonthlyQuotaService
- Phase 2: Test scripts
- Phase 3: (No changes needed - uses model methods)
- Phase 4: Admin controller and views
- Phase 5: Member controller and views

---

## Implementation Checklist Updates

### Phase 1 Testing
**Original**:
- [ ] Verify `products` table has `point_value` column

**Updated**:
- [ ] Verify `products.points_awarded` changed from integer to decimal(10,2)
- [ ] Test decimal PV values: 0.5, 1.25, 10.50, etc.
- [ ] Verify fractional calculations work correctly

### Verification Commands
**Added**:
```bash
# Verify products table has points_awarded
$product = Product::first();
dump($product->points_awarded); // Should exist
```

---

## Benefits of Changes

1. **Code Reuse**: Leverages existing column instead of creating new one
2. **Decimal Precision**: Allows fractional PV values (0.5, 1.25, 10.50)
3. **Flexible Allocation**: Can assign small amounts of PV to lower-priced items
4. **Business Control**: More granular control over point distribution
5. **Future-Proof**: Decimal type supports various business scenarios

---

## Real-Time & CRON Changes

### Real-Time Processing
- All job dispatches use `dispatchSync()` for immediate execution
- Quota updates happen instantly when products are purchased
- Eligibility checks always query current database state
- No caching, no stale data

### CRON Job Setup - Two Options Available
**Option A: Direct PHP Script Execution (Traditional)**
- Create standalone PHP scripts in `crons/` folder
- Set up individual cron jobs for each task
- Simple, transparent, no Laravel Scheduler knowledge needed
- Detailed configuration for Hostinger hPanel, Windows Task Scheduler, Linux crontab

**Option B: Laravel Scheduler (Modern Laravel Way - Recommended for Learning)**
- Register tasks in `app/Console/Kernel.php`
- Single cron entry: `php artisan schedule:run` (runs every minute)
- Laravel decides which tasks to execute based on schedule
- Built-in testing with `schedule:list` and `schedule:run`
- Industry standard for Laravel applications

**Choose based on your comfort level**: Start with Option A if new to Laravel Scheduler, migrate to Option B later for better maintainability.

---

## Field Reference Table

| Field | Table | Current Type | New Type | Status |
|-------|-------|--------------|----------|--------|
| `points_awarded` | products | integer | decimal(10,2) | ⚠️ Modify |
| `monthly_quota_points` | packages | N/A | decimal(10,2) | ❌ New |
| `enforce_monthly_quota` | packages | N/A | boolean | ❌ New |
| `total_pv_points` | monthly_quota_tracker | N/A | decimal(10,2) | ❌ New |
| `required_quota` | monthly_quota_tracker | N/A | decimal(10,2) | ❌ New |
| `quota_met` | monthly_quota_tracker | N/A | boolean | ❌ New |

---

## Migration Files to Create

### Migration 1: Modify Products Points Column
```
File: database/migrations/YYYY_MM_DD_modify_points_awarded_to_decimal_in_products_table.php
Changes: points_awarded from integer to decimal(10,2)
Requires: doctrine/dbal package for column modification
```

### Migration 2: Add Monthly Quota to Packages
```
File: database/migrations/YYYY_MM_DD_add_monthly_quota_to_packages_table.php
Adds: monthly_quota_points (decimal 10,2), enforce_monthly_quota (boolean)
```

### Migration 3: Create Monthly Quota Tracker
```
File: database/migrations/YYYY_MM_DD_create_monthly_quota_tracker_table.php
Creates: monthly_quota_tracker table with decimal PV fields
```

---

## Testing Considerations

### Update Test Scripts
Replace all references to:
- `$product->point_value` with `$product->points_awarded`
- `Product::where('point_value', '>', 0)` with `Product::where('points_awarded', '>', 0)`
- Test decimal values: `$product->points_awarded = 10.5`

### Validation Rules
- Keep as `numeric|min:0|max:9999.99`
- Max value: 9999.99 (decimal)
- Step value: 0.01 (in HTML inputs for decimal entry)
- Display: Use `number_format($pv, 2)` for consistent formatting

---

## Documentation Updates

Both `UNILEVEL_QUOTA.md` and `UNILEVEL_QUOTA_SUMMARY.md` have been updated to reflect:
1. Modification of existing `points_awarded` column to decimal(10,2)
2. Decimal data types for all PV fields (support 0.5, 1.25, 10.50, etc.)
3. Migration count: 3 total (modify products, add to packages, create tracker)
4. Real-time processing approach
5. CRON job configuration requirements
6. Fractional PV support for flexible point allocation

---

## Next Steps

1. ✅ Review changes in `UNILEVEL_QUOTA.md`
2. ✅ Verify all code samples use `points_awarded`
3. ✅ Confirm data types are consistent (decimal 10,2)
4. ⏳ Install doctrine/dbal package (required for column modification)
5. ⏳ Create the 3 migration files
6. ⏳ Update model fillable/casts arrays (use 'decimal:2')
7. ⏳ Implement services and controllers
8. ⏳ Configure CRON jobs
9. ⏳ Test decimal PV values thoroughly (0.5, 1.25, 10.50, etc.)
10. ⏳ Test fractional calculations accuracy

---

**Document Version**: 2.0  
**Last Updated**: 2025-11-14  
**Status**: Ready for Implementation  
**Major Change**: Switched to decimal(10,2) for PV precision
