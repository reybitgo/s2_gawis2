# Rank Configuration Mobile Input Width Fix

## Issue
Input fields in the "Rank Package Configuration" table on the admin ranks configure page had inconsistent widths on mobile devices compared to desktop mode. Since the table is responsive and scrollable horizontally, the input fields needed proper minimum widths to maintain usability on mobile.

## Solution
Added custom CSS styling to ensure proper input field widths in both desktop and mobile modes:

### Changes Made

**File:** `resources/views/admin/ranks/configure.blade.php`

1. **Added CSS styles** using `@push('styles')` directive:
   - Set minimum table width (900px) to ensure horizontal scrolling on mobile
   - Applied consistent minimum widths to all input fields and select dropdowns
   - Default text inputs and selects: 150px minimum width
   - Number inputs: 100px minimum width
   - **Rank Name input: 200px minimum width** (shows at least first word for better UX)
   - **Next Rank Package select: 250px minimum width** (displays package names clearly)
   - Prevented table cells from shrinking below content size with `white-space: nowrap`
   - Allowed small helper text to wrap properly

2. **Applied class** `rank-config-table` to the configuration table for targeted styling

### CSS Specifications

```css
.rank-config-table {
    min-width: 900px; /* Ensures table scrolls horizontally on mobile */
}

.rank-config-table td input.form-control,
.rank-config-table td select.form-select {
    min-width: 150px; /* Consistent minimum width for all inputs */
    width: 100%;
}

.rank-config-table td input[type="number"] {
    min-width: 100px; /* Smaller width for number inputs */
}

/* Wider inputs for better visibility on mobile */
.rank-config-table td input[name*="[rank_name]"] {
    min-width: 200px; /* Rank Name - shows at least first word */
}

.rank-config-table td select[name*="[next_rank_package_id]"] {
    min-width: 250px; /* Next Rank Package - displays package names clearly */
}

.rank-config-table td,
.rank-config-table th {
    white-space: nowrap; /* Prevent cell content from wrapping */
}

.rank-config-table td small {
    white-space: normal; /* Allow helper text to wrap */
    display: block;
    margin-top: 4px;
}
```

## Benefits

1. **Consistent UX:** Input fields now have the same proper width on both mobile and desktop
2. **Better Usability:** Adequate input field width makes data entry easier on mobile devices
3. **Enhanced Visibility:** Users can see at least the first word in Rank Name field while typing
4. **Clear Package Selection:** Next Rank Package dropdown shows full package names for easier selection
5. **Responsive Design:** Table scrolls horizontally on mobile while maintaining proper input dimensions
6. **Visual Clarity:** Prevents inputs from being cramped or too narrow on smaller screens

## Testing

Test the fix by:
1. Visit: https://s2.gawisherbal.com/admin/ranks/configure
2. View on mobile device or use browser DevTools mobile emulation
3. Verify input fields have proper width and are usable
4. Test horizontal scrolling on mobile
5. Verify desktop view remains unchanged

## Implementation Date
December 10, 2025

## Update History
- **Initial implementation:** Added basic responsive styling with minimum widths
- **Enhancement:** Increased Rank Name input (200px) and Next Rank Package select (250px) widths for improved visibility and UX on mobile devices
