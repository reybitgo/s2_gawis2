# Unilevel Quota System Toggle Feature

## Overview

A new toggle has been added to the Application Settings that allows administrators to globally enable or disable the Monthly Quota System for Unilevel bonuses. When disabled, the entire quota system is bypassed and all quota-related UI elements are hidden from users.

---

## Location

**Settings Page**: http://s2_gawis2.test/admin/application-settings

Look for the **"Unilevel Quota System"** card with a toggle switch.

---

## Features Implemented

### 1. Application Settings Toggle

**New Card Added**: "Unilevel Quota System"
- **Location**: `/admin/application-settings`
- **Toggle**: Enable/Disable Monthly Quota Requirement for Unilevel Bonuses
- **System Setting Key**: `unilevel_quota_enabled` (boolean)
- **Default Value**: `true` (enabled)

### 2. Automatic UI Hiding

When the toggle is **DISABLED**, the following UI elements are automatically hidden:

#### Admin Sidebar
- ✅ "Monthly Quota" menu group (including all sub-items)
  - Dashboard
  - Package Quotas
  - Quota Reports

#### Member Sidebar
- ✅ "My Quota" menu group (including all sub-items)
  - Current Month
  - Quota History

### 3. Route Protection

Direct access to quota pages is blocked when disabled:

**Admin Routes Protected**:
- `/admin/monthly-quota` → Redirects to admin dashboard
- `/admin/monthly-quota/packages` → Redirects to admin dashboard
- `/admin/monthly-quota/reports` → Redirects to admin dashboard
- `/admin/monthly-quota/reports/user/{user}` → Redirects to admin dashboard

**Member Routes Protected**:
- `/my-quota` → Redirects to dashboard
- `/my-quota/history` → Redirects to dashboard

**Error Message Shown**: "The Monthly Quota System is currently disabled. Enable it in Application Settings to access this feature."

### 4. Unilevel Bonus Logic Update

The `User::qualifiesForUnilevelBonus()` method now checks the system setting first:

```php
// If quota system is disabled globally
if (!SystemSetting::get('unilevel_quota_enabled', true)) {
    return true; // Only network_active status matters
}

// Otherwise, proceed with normal quota logic
```

**Behavior When Disabled**:
- Unilevel bonuses distributed based on **`network_active`** status only
- No quota checking performed
- Package `enforce_monthly_quota` setting is ignored
- Monthly PV accumulation still tracked (for future use)
- No impact on users earning bonuses

---

## How to Use

### Enabling the Quota System (Default)

1. Navigate to: `/admin/application-settings`
2. Scroll to **"Unilevel Quota System"** card
3. **Check** the toggle: "Enable Monthly Quota Requirement for Unilevel Bonuses"
4. Click **"Save Settings"**
5. Result:
   - Quota checking is active
   - Users must meet monthly quotas to earn Unilevel bonuses
   - All quota menus visible in sidebar
   - All quota pages accessible

### Disabling the Quota System

1. Navigate to: `/admin/application-settings`
2. Scroll to **"Unilevel Quota System"** card
3. **Uncheck** the toggle: "Enable Monthly Quota Requirement for Unilevel Bonuses"
4. Click **"Save Settings"**
5. Result:
   - Quota checking is bypassed
   - Users earn Unilevel bonuses based on active status only
   - All quota menus hidden from sidebar
   - Direct access to quota pages blocked
   - Warning message shown when accessing quota URLs

---

## Impact When Disabled

### ✅ What Still Works
- Unilevel bonus distribution (quota-free, active status only)
- MLM network structure
- Product purchases
- Points/PV tracking (background)
- Package activation
- All other system features

### ❌ What is Disabled/Hidden
- Quota checking for bonuses
- Admin quota dashboard
- Admin package quota configuration
- Admin quota reports
- Member quota status page
- Member quota history page
- Quota sidebar menus
- Quota reminders (if sent)

### 🔄 What Happens to Existing Data
- **Monthly quota trackers**: Preserved in database (not deleted)
- **Package quota settings**: Ignored but not changed
- **Historical quota data**: Accessible when system is re-enabled
- **User qualifications**: Reset to active-only logic

---

## Technical Details

### Database Changes

**New System Setting**:
```php
[
    'key' => 'unilevel_quota_enabled',
    'value' => '1',  // true (enabled by default)
    'type' => 'boolean',
    'description' => 'Whether the monthly quota system is enabled for Unilevel bonuses'
]
```

**Storage**: `system_settings` table

### Files Modified

1. **Controller**: `app/Http/Controllers/Admin/AdminSettingsController.php`
   - Added `unilevel_quota_enabled` to settings array
   - Added validation rule
   - Added save logic with SystemSetting::set()

2. **View**: `resources/views/admin/settings/index.blade.php`
   - Added "Unilevel Quota System" card
   - Added toggle switch with warning message

3. **Sidebar**: `resources/views/partials/sidebar.blade.php`
   - Added conditional display for admin quota menu
   - Added conditional display for member quota menu
   - Uses: `@if(\App\Models\SystemSetting::get('unilevel_quota_enabled', true))`

4. **Admin Controller**: `app/Http/Controllers/Admin/MonthlyQuotaController.php`
   - Added middleware to check system setting
   - Redirects to admin dashboard if disabled

5. **Member Controller**: `app/Http/Controllers/Member/MemberQuotaController.php`
   - Added middleware to check system setting
   - Redirects to member dashboard if disabled

6. **User Model**: `app/Models/User.php`
   - Updated `qualifiesForUnilevelBonus()` method
   - Checks system setting before quota logic
   - Returns true (qualified) if disabled

### Checking Setting Value

```php
// In code
$enabled = \App\Models\SystemSetting::get('unilevel_quota_enabled', true);

// In Blade views
@if(\App\Models\SystemSetting::get('unilevel_quota_enabled', true))
    <!-- Show quota-related content -->
@endif
```

---

## Testing Checklist

### Test Case 1: Enable Quota System (Default)
- [ ] Navigate to `/admin/application-settings`
- [ ] Toggle is **checked** by default
- [ ] Save settings
- [ ] Admin sidebar shows "Monthly Quota" menu
- [ ] Member sidebar shows "My Quota" menu
- [ ] Can access `/admin/monthly-quota`
- [ ] Can access `/my-quota`
- [ ] Unilevel bonuses check quota eligibility

### Test Case 2: Disable Quota System
- [ ] Navigate to `/admin/application-settings`
- [ ] **Uncheck** the toggle
- [ ] Save settings
- [ ] Admin sidebar hides "Monthly Quota" menu
- [ ] Member sidebar hides "My Quota" menu
- [ ] Direct access to `/admin/monthly-quota` redirects with error
- [ ] Direct access to `/my-quota` redirects with error
- [ ] Unilevel bonuses distributed without quota check

### Test Case 3: Toggle Persistence
- [ ] Disable quota system
- [ ] Logout and login again
- [ ] Setting remains disabled
- [ ] Re-enable quota system
- [ ] Logout and login again
- [ ] Setting remains enabled

### Test Case 4: Data Preservation
- [ ] Enable quota system
- [ ] Some users meet quota, some don't
- [ ] Note the data
- [ ] Disable quota system
- [ ] All users earn bonuses (active only)
- [ ] Re-enable quota system
- [ ] Previous quota data is still there
- [ ] Quota logic resumes correctly

---

## Benefits

### For Development
- **Easy Testing**: Quickly disable quota during development/testing
- **Gradual Rollout**: Enable for specific periods or phases
- **Emergency Disable**: Quickly bypass quota if issues arise
- **Backward Compatible**: Can revert to old behavior instantly

### For Business
- **Flexible Deployment**: Launch quota system when ready
- **A/B Testing**: Test impact of quota on user behavior
- **Seasonal Control**: Disable during promotions/events
- **User Feedback**: Gather feedback before permanent implementation

### For Users
- **Cleaner Interface**: No quota menus when not in use
- **Less Confusion**: Clear UI when quota not required
- **No Dead Links**: Pages redirect with helpful messages

---

## Rollback Plan

If the quota system causes issues, disable it immediately:

1. Go to `/admin/application-settings`
2. Uncheck "Enable Monthly Quota Requirement"
3. Save settings
4. **Immediate Effect**:
   - All bonuses distributed without quota check
   - All quota UI hidden
   - System reverts to active-only logic

**No code changes needed!**

---

## Future Enhancements

Potential additions to this feature:

1. **Scheduled Enable/Disable**: Set dates to auto-enable/disable
2. **Package-Level Override**: Enable quota for specific packages only
3. **Partial Quota**: Apply reduced quota when disabled (e.g., 50%)
4. **Notification**: Email admins when quota system is toggled
5. **Activity Log**: Track who enabled/disabled and when
6. **Grace Period**: Auto-disable quota for X days after enabling

---

## Support

**Questions or Issues?**

1. Check that all caches are cleared:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   php artisan route:clear
   ```

2. Verify system setting in database:
   ```sql
   SELECT * FROM system_settings WHERE `key` = 'unilevel_quota_enabled';
   ```

3. Check logs:
   ```bash
   tail -100 storage/logs/laravel.log
   ```

---

## Conclusion

The Unilevel Quota Toggle provides a powerful on/off switch for the entire quota system. When disabled, the application behaves as if the quota system was never implemented, ensuring backward compatibility and giving administrators full control over when quota requirements are enforced.

**Default State**: ENABLED (quota system active)
**Emergency Disable**: 2 clicks in Application Settings
**Data Safe**: All quota data preserved when toggling
**UI Clean**: All quota menus automatically hidden when disabled
