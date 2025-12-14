# Frontend Gawis Packages - Dynamic MLM Commission Values

## Overview
Modified the frontend index page "Gawis Packages" section to dynamically replace hardcoded commission amounts in feature labels with actual values from the database, while preserving the original feature text format.

## Design Philosophy
- **Preserve existing UI**: Keep the original feature labels and format intact
- **Dynamic values only**: Only replace the commission amounts (e.g., ₱1,200 → actual DB value)
- **Maintain labels**: Keep descriptive text like "Level 1 Commission", "Level 2 Commission", etc.

## Changes Made

### File Modified
- `resources/views/frontend/index.blade.php`

### Implementation Details

#### Before
- Features displayed text like: `"₱1,200 Level 1 Commission"`
- Commission amounts were hardcoded in `$package->meta_data['features']`
- Values didn't update when admin changed MLM settings

#### After
- Same feature format but with **dynamic commission amounts from database**
- Uses regex pattern matching to identify commission features
- Replaces only the currency amount while preserving the label text
- Example: `"₱1,200 Level 1 Commission"` → `"₱200.00 Level 1 Commission"` (if DB has ₱200)

### How It Works
For each feature in `meta_data['features']`:
1. Check if it matches pattern: `₱[amount] Level [X] Commission`
2. If matched, extract the level number
3. Look up the actual commission amount from `mlmSettings` for that level
4. Replace only the amount portion using `preg_replace`, keeping "Level X Commission" intact
5. Display the updated feature text

### Logic Flow
```php
@foreach ($package->meta_data['features'] as $feature)
    @php
        $displayFeature = $feature;
        if ($package->is_mlm_package && $package->mlmSettings->isNotEmpty()) {
            // Match: "₱1,200 Level 1 Commission"
            if (preg_match('/₱[\d,.]+ Level (\d+) Commission/', $feature, $matches)) {
                $level = (int)$matches[1];
                $setting = $package->mlmSettings->firstWhere('level', $level);
                if ($setting && $setting->is_active) {
                    // Replace: ₱1,200 → ₱200.00 (from DB)
                    $displayFeature = preg_replace('/₱[\d,.]+/', currency($setting->commission_amount), $feature, 1);
                }
            }
        }
    @endphp
    <li>{{ $displayFeature }}</li>
@endforeach
```

### Benefits
1. **Real-time accuracy**: Commission amounts always match actual MLM settings in database
2. **Admin control**: Changes in admin MLM settings automatically reflect on frontend
3. **Preserved UI/UX**: Maintains original feature format and labels users are familiar with
4. **No manual updates needed**: No need to update meta_data when commission rates change
5. **Active-only display**: Only shows commissions for active levels (respects `is_active` flag)

### Technical Notes
- Uses existing `mlmSettings` relationship already eager-loaded in `FrontendController`
- Uses existing `currency()` helper function for consistent formatting
- Uses regex pattern matching to identify and replace commission amounts
- Preserves non-commission features unchanged (e.g., descriptive text, other benefits)
- No database schema changes required

## Example Display

### Before Update (Hardcoded in meta_data):
```
✓ ₱1,200 Level 1 Commission
✓ ₱800 Level 2 Commission
✓ ₱300 Level 3 Commission
```

### After Update (Dynamic from DB):
If admin sets Level 1 = ₱200, Level 2 = ₱50, Level 3 = ₱50:
```
✓ ₱200.00 Level 1 Commission
✓ ₱50.00 Level 2 Commission
✓ ₱50.00 Level 3 Commission
```

**The labels stay the same, only amounts change!**

## Testing Recommendations
1. Visit the homepage and scroll to "Gawis Packages" section
2. Note current commission amounts displayed on package cards
3. Go to Admin → Packages → MLM Settings
4. Change commission amounts for any level
5. Refresh homepage and verify amounts updated
6. Toggle `is_active` off for a level and verify it no longer displays
7. Verify non-commission features (if any) remain unchanged

## Related Files
- Controller: `app/Http/Controllers/FrontendController.php` (already loads `mlmSettings`)
- Model: `app/Models/Package.php` (defines `mlmSettings()` relationship)
- Admin Settings: `app/Http/Controllers/Admin/AdminMlmSettingsController.php`
- Modal: "View Details" shows full MLM settings breakdown (lines 505-530 in index.blade.php)
