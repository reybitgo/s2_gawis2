# MLM System Testing Documentation

## Overview

This document provides comprehensive testing procedures for the Multi-Level Marketing (MLM) system implementation. Each phase includes detailed test cases, expected results, and validation criteria.

---

## Phase 1: Core MLM Package & Sponsor-Based Registration

**Status**: Ready for Testing
**Estimated Testing Time**: 2-3 hours
**Prerequisites**: Database reset seeder has been run

---

## Test Environment Setup

### Initial Setup

```bash
# 1. Reset the database
php artisan db:seed --class=DatabaseResetSeeder

# 2. Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Start the development server
php artisan serve
```

### Test Accounts

-   **Admin**: admin / Admin123!@# (email: admin@gawisherbal.com)
-   **Member**: member / Member123!@# (email: member@gawisherbal.com)

**Note**: Users can now login using either username or email. Email is optional during registration.

### Default URLs

-   Application: `http://coreui_laravel_deploy.test/` or `http://localhost:8000/`
-   Admin Login: `/login`
-   Public Registration: `/register`
-   Member Registration (Logged-in): `/register-member`
-   Admin Packages: `/admin/packages`
-   MLM Settings: `/admin/packages/starter-package/mlm-settings`

---

## Test Suite 1: Database Schema & Migrations

### Test Case 1.1: Verify MLM Tables Exist

**Objective**: Ensure all MLM-related database tables and columns exist

**Steps**:

1. Connect to MySQL database
2. Run the following SQL queries:

```sql
-- Check mlm_settings table exists
DESCRIBE mlm_settings;

-- Check users table has MLM columns
SHOW COLUMNS FROM users WHERE Field IN ('sponsor_id', 'referral_code');

-- Check packages table has MLM columns
SHOW COLUMNS FROM packages WHERE Field IN ('is_mlm_package', 'max_mlm_levels');

-- Check wallets table has segregated balances
SHOW COLUMNS FROM wallets WHERE Field IN ('mlm_balance', 'purchase_balance');
```

**Expected Results**:

-   ✅ `mlm_settings` table exists with columns: `id`, `package_id`, `level`, `commission_amount`, `is_active`, `created_at`, `updated_at`
-   ✅ `users` table has `sponsor_id` (bigint, nullable) and `referral_code` (varchar(20), unique)
-   ✅ `packages` table has `is_mlm_package` (boolean) and `max_mlm_levels` (tinyint)
-   ✅ `wallets` table has `mlm_balance` (decimal) and `purchase_balance` (decimal)

**Pass Criteria**: All tables and columns exist with correct data types

---

### Test Case 1.2: Verify MLM Settings Data

**Objective**: Ensure MLM commission structure is seeded correctly

**Steps**:

1. Run SQL query:

```sql
SELECT
    ms.level,
    ms.commission_amount,
    ms.is_active,
    p.name as package_name,
    p.price as package_price
FROM mlm_settings ms
JOIN packages p ON ms.package_id = p.id
WHERE p.slug = 'starter-package'
ORDER BY ms.level;
```

**Expected Results**:

```
level | commission_amount | is_active | package_name    | package_price
------|-------------------|-----------|-----------------|---------------
1     | 200.00           | 1         | Starter Package | 1000.00
2     | 50.00            | 1         | Starter Package | 1000.00
3     | 50.00            | 1         | Starter Package | 1000.00
4     | 50.00            | 1         | Starter Package | 1000.00
5     | 50.00            | 1         | Starter Package | 1000.00
```

**Pass Criteria**:

-   ✅ Exactly 5 MLM settings records exist
-   ✅ Level 1 commission = ₱200
-   ✅ Levels 2-5 commission = ₱50 each
-   ✅ All levels are active
-   ✅ Total commission = ₱400 (40% of ₱1,000)

---

### Test Case 1.3: Verify User MLM Relationships

**Objective**: Ensure default users have proper sponsor relationships and referral codes

**Steps**:

1. Run SQL query:

```sql
SELECT
    id,
    username,
    email,
    sponsor_id,
    referral_code,
    email_verified_at,
    CASE
        WHEN sponsor_id IS NULL THEN 'No Sponsor'
        ELSE CONCAT('Sponsored by User #', sponsor_id)
    END as sponsor_info
FROM users
WHERE username IN ('admin', 'member')
ORDER BY id;
```

**Expected Results**:

-   **Admin (ID: 1)**:

    -   `sponsor_id` = NULL
    -   `referral_code` = REF[8 uppercase alphanumeric characters] (e.g., REFX448MDSM)
    -   `email` = 'admin@gawisherbal.com' (or could be NULL if optional)
    -   `email_verified_at` = timestamp or NULL
    -   sponsor_info = "No Sponsor"

-   **Member (ID: 2)**:
    -   `sponsor_id` = 1
    -   `referral_code` = REF[8 uppercase alphanumeric characters] (e.g., REFMR8QHLWU)
    -   `email` = 'member@gawisherbal.com' (or could be NULL if optional)
    -   `email_verified_at` = timestamp or NULL
    -   sponsor_info = "Sponsored by User #1"

**Pass Criteria**:

-   ✅ Admin has no sponsor (sponsor_id = NULL)
-   ✅ Member is sponsored by Admin (sponsor_id = 1)
-   ✅ Both users have unique referral codes starting with "REF"
-   ✅ Referral codes are exactly 11 characters (REF + 8 chars)
-   ✅ Email can be NULL (optional field)

---

### Test Case 1.4: Verify Wallet Segregated Balances

**Objective**: Ensure wallets have MLM and purchase balances properly set

**Steps**:

1. Run SQL query:

```sql
SELECT
    u.username,
    u.email,
    w.mlm_balance,
    w.purchase_balance,
    (w.mlm_balance + w.purchase_balance) as total_available
FROM users u
JOIN wallets w ON u.id = w.user_id
WHERE u.email IN ('admin@gawisherbal.com', 'member@gawisherbal.com')
ORDER BY u.id;
```

**Expected Results**:

```
username | email                    | mlm_balance | purchase_balance | total_available
---------|--------------------------|-------------|------------------|----------------
admin    | admin@gawisherbal.com   | 0.00        | 1000.00         | 1000.00
member   | member@gawisherbal.com  | 0.00        | 1000.00         | 1000.00
```

**Pass Criteria**:

-   ✅ Both users have ₱0 MLM balance (no commissions earned yet)
-   ✅ Both users have ₱1,000 purchase balance (can buy Starter Package)
-   ✅ Total available = ₱1,000

---

## Test Suite 2: User Registration with Sponsor System

**IMPORTANT NOTE**: Email is now **OPTIONAL** during registration. Users can register with or without an email address. If an email is provided, email verification will be required (based on system settings). If no email is provided, users can still access the system and add an email later via their profile.

---

### Test Case 2.1: Registration Without Sponsor (Default to Admin)

**Objective**: Verify new users default to Admin sponsor when no sponsor provided

**Steps**:

1. Open browser and navigate to registration page: `/register`
2. Fill in registration form:
    - **Full Name**: John Doe
    - **Username**: johndoe
    - **Email**: johndoe@test.com (OPTIONAL - can be left blank)
    - **Sponsor Name**: (leave blank)
    - **Password**: Test123!@#
    - **Confirm Password**: Test123!@#
    - Check "I agree to terms" checkbox
3. Click "Create Account"
4. Verify success (redirected to dashboard or email verification if email was provided)
5. Check database:

```sql
SELECT id, username, email, sponsor_id, referral_code, email_verified_at
FROM users
WHERE username = 'johndoe';
```

**Expected Results**:

-   ✅ User successfully registered
-   ✅ `sponsor_id` = 1 (Admin)
-   ✅ `referral_code` generated automatically (format: REF[8 chars])
-   ✅ Small text shows "Leave blank to be assigned to default sponsor (Admin)"
-   ✅ Email placeholder shows "Email (Optional)"
-   ✅ Helper text below email: "Email is optional. If provided, you will need to verify it. You can add it later in your profile."
-   ✅ If email was provided: `email_verified_at` = NULL (pending verification)
-   ✅ If email was blank: `email` = NULL, `email_verified_at` = NULL

**Pass Criteria**:

-   ✅ New user created with Admin as default sponsor
-   ✅ Unique referral code generated
-   ✅ No validation errors
-   ✅ Email can be omitted without errors

**Screenshot Required**: ✅ Registration form showing optional email field

---

### Test Case 2.2: Registration With Valid Sponsor Username (No Email)

**Objective**: Verify users can register with sponsor's username without providing email

**Steps**:

1. Navigate to `/register`
2. Fill in registration form:
    - **Full Name**: Jane Smith
    - **Username**: janesmith
    - **Email**: (leave blank)
    - **Sponsor Name**: `admin` (enter admin's username)
    - **Password**: Test123!@#
    - **Confirm Password**: Test123!@#
    - Check terms checkbox
3. Click "Create Account"
4. Verify database:

```sql
SELECT id, username, email, sponsor_id, referral_code, email_verified_at
FROM users
WHERE username = 'janesmith';
```

**Expected Results**:

-   ✅ User successfully registered
-   ✅ `sponsor_id` = 1 (Admin found by username)
-   ✅ Referral code generated
-   ✅ `email` = NULL
-   ✅ `email_verified_at` = NULL
-   ✅ User can login immediately without email verification

**Pass Criteria**:

-   ✅ Sponsor correctly identified by username
-   ✅ sponsor_id matches admin user
-   ✅ Registration succeeds without email

**Screenshot Required**: ✅ Registration form with no email, sponsor username filled

---

### Test Case 2.3: Registration With Valid Sponsor Referral Code

**Objective**: Verify users can register using sponsor's referral code

**Steps**:

1. Get admin's referral code from database:

```sql
SELECT referral_code FROM users WHERE email = 'admin@gawisherbal.com';
```

2. Navigate to `/register`
3. Fill in registration form:
    - **Full Name**: Bob Wilson
    - **Username**: bobwilson
    - **Email**: bobwilson@test.com
    - **Sponsor Name**: [Paste admin's referral code, e.g., REFX448MDSM]
    - **Password**: Test123!@#
    - **Confirm Password**: Test123!@#
    - Check terms checkbox
4. Click "Create Account"
5. Verify database:

```sql
SELECT id, username, sponsor_id
FROM users
WHERE email = 'bobwilson@test.com';
```

**Expected Results**:

-   ✅ User successfully registered
-   ✅ `sponsor_id` = 1 (Admin found by referral code)

**Pass Criteria**:

-   ✅ Sponsor correctly identified by referral code
-   ✅ sponsor_id matches admin user

**Screenshot Required**: ✅ Registration form with referral code

---

### Test Case 2.4: Registration With Referral Code in URL

**Objective**: Verify referral code from URL auto-fills sponsor field

**Steps**:

1. Get member's referral code:

```sql
SELECT referral_code FROM users WHERE email = 'member@gawisherbal.com';
```

2. Navigate to `/register?ref=[MEMBER_REFERRAL_CODE]` (e.g., `/register?ref=REFMR8QHLWU`)
3. **Observe** the sponsor field:
    - Should be auto-filled with referral code
    - Blue info alert should appear: "Referral Code Applied: [CODE]"
4. Fill in remaining fields:
    - **Full Name**: Alice Cooper
    - **Username**: alicecooper
    - **Email**: alicecooper@test.com
    - **Password**: Test123!@#
    - **Confirm Password**: Test123!@#
    - Check terms checkbox
5. Click "Create Account"
6. Verify database:

```sql
SELECT id, username, sponsor_id
FROM users
WHERE email = 'alicecooper@test.com';
```

**Expected Results**:

-   ✅ Sponsor field auto-filled with referral code from URL
-   ✅ Blue alert displays: "Referral Code Applied: [CODE]"
-   ✅ User successfully registered
-   ✅ `sponsor_id` = 2 (Member user)

**Pass Criteria**:

-   ✅ Referral code correctly parsed from URL query parameter
-   ✅ Sponsor field auto-populated
-   ✅ Visual confirmation shown to user
-   ✅ sponsor_id matches member user (not admin)

**Screenshot Required**:

-   ✅ Registration page with referral code alert
-   ✅ Auto-filled sponsor field

---

### Test Case 2.5: Registration With Invalid Sponsor Name (With Email)

**Objective**: Verify system handles invalid sponsor gracefully with email verification

**Steps**:

1. Navigate to `/register`
2. Fill in registration form:
    - **Full Name**: Charlie Brown
    - **Username**: charliebrown
    - **Email**: charliebrown@test.com
    - **Sponsor Name**: `nonexistentuser` (invalid)
    - **Password**: Test123!@#
    - **Confirm Password**: Test123!@#
    - Check terms checkbox
3. Click "Create Account"
4. Verify database:

```sql
SELECT id, username, email, sponsor_id, email_verified_at
FROM users
WHERE username = 'charliebrown';
```

**Expected Results**:

-   ✅ Registration **fails** with validation error
-   ✅ Error message displays: "The sponsor 'nonexistentuser' could not be found. Please check the username, referral code, or full name."
-   ✅ Error appears under sponsor name field with red styling
-   ✅ User is **NOT** created in database (query returns no rows)
-   ✅ Form retains entered values (except passwords for security)
-   ✅ System does **NOT** silently default to admin sponsor
-   ✅ No email verification sent (since user not created)

**Pass Criteria**:

-   ✅ Invalid sponsor name causes registration failure
-   ✅ Clear validation error message guides user to fix the issue
-   ✅ No user created with invalid sponsor
-   ✅ User can correct sponsor name and retry registration
-   ✅ Email verification only triggered after successful registration

**Screenshot Required**:

-   ✅ Validation error message under sponsor field
-   ✅ Form showing error state with retained values
-   ✅ Database query showing no user created

---

### Test Case 2.6: Profile Email Management with Automatic Verification

**Objective**: Verify users can add/update email in profile with automatic verification email sending

**Steps**:

1. Register a new user WITHOUT email:
    - Username: `testuser`
    - Email: (leave blank)
    - Password: Test123!@#
2. Login as `testuser`
3. Navigate to `/profile`
4. Observe email field:
    - Should show "Email Address (Optional)"
    - Should have placeholder: "your.email@example.com"
    - Should show helper text: "Add an email to receive notifications. A verification email will be sent automatically."
    - **No "Verify Email" button** (no email present)
5. Add email: `testuser@example.com`
6. Click "Save Changes"
7. Observe success message and profile page after save:
    - Success message: "Email address updated successfully. A verification email has been sent to testuser@example.com."
    - Top alert shows: "Email address is not verified. Email verification is optional."
    - Email field shows warning icon and text: "Your email address is not verified. Email verification is optional."
    - **No "Verify Email" button** - verification email already sent automatically
8. Check email inbox for verification email (automatic)
9. Check database:

```sql
SELECT username, email, email_verified_at
FROM users
WHERE username = 'testuser';
```

**Expected Results**:

-   ✅ Email field clearly marked as optional
-   ✅ Helper text indicates automatic verification: "A verification email will be sent automatically"
-   ✅ Email successfully updated
-   ✅ `email_verified_at` = NULL (not verified yet)
-   ✅ Success message: "Email address updated successfully. A verification email has been sent to testuser@example.com."
-   ✅ **Verification email sent automatically** - no manual button needed
-   ✅ **No "Verify Email" button** displayed (all automatic)
-   ✅ Alert message shows: "Your email address is not verified. Email verification is optional."

**Additional Test - After Email Verification**: 10. Click verification link from email (sent automatically) 11. Return to `/profile` 12. Observe email section: - Top alert changed to: "Email Verified" (green success alert) - Email field shows green checkmark and text: "Your email address is verified." - Still **no "Verify Email" button** (not needed) 13. Check database:

```sql
SELECT username, email, email_verified_at
FROM users
WHERE username = 'testuser';
```

14. Expected: `email_verified_at` has timestamp (not NULL)

**Additional Test - Email Update (Change Email)**: 15. Change email from `testuser@example.com` to `newemail@example.com` 16. Click "Save Changes" 17. Verify: - Success message: "Email address updated successfully. A verification email has been sent to newemail@example.com." - **New verification email sent automatically** to new address - `email_verified_at` reset to NULL (new email unverified) - Alert shows unverified status again

**Pass Criteria**:

-   ✅ Users can add email after registration
-   ✅ **Verification email sent automatically** when email is added
-   ✅ **Verification email sent automatically** when email is changed
-   ✅ **No manual "Verify Email" button** - all automatic
-   ✅ Alert message changes based on verification status
-   ✅ Clear user feedback provided at each step
-   ✅ Success messages confirm email was sent

**Screenshot Required**:

-   ✅ Profile page with no email (showing automatic helper text)
-   ✅ Success message after adding email (showing verification email sent)
-   ✅ Profile page showing unverified status (no button, just status indicators)
-   ✅ Profile page after email verification (showing verified status)

---

### Test Case 2.7: Profile Email Removal

**Objective**: Verify users can remove email from their profile

**Steps**:

1. Login as user with email (e.g., `testuser@example.com`)
2. Navigate to `/profile`
3. Clear the email field (make it blank)
4. Click "Save Changes"
5. Verify database:

```sql
SELECT username, email, email_verified_at
FROM users
WHERE username = 'testuser';
```

**Expected Results**:

-   ✅ Email successfully removed
-   ✅ `email` = NULL
-   ✅ `email_verified_at` = NULL
-   ✅ Success message: "Email address removed successfully."
-   ✅ User can still login with username

**Pass Criteria**:

-   ✅ Email removal works correctly
-   ✅ User account remains functional without email

**Screenshot Required**: ✅ Profile after email removal

---

### Test Case 2.8: Member Registration (Logged-in User Registers Another User)

**Objective**: Verify logged-in users can register new members with flexible sponsor assignment

**Steps**:

1. Login as any user (e.g., `admin` / `Admin123!@#`)
2. Navigate to dashboard sidebar
3. Locate "Register New Member" link under "Member Actions" section
4. Click to navigate to `/register-member`
5. Verify **Sponsor Name/Username field** is displayed at the top of the form
6. Verify field is pre-filled with logged-in user's username (e.g., `admin`)
7. Verify helper text shows: "Default: Admin (admin). You can change this to assign a different sponsor."
8. Fill in registration form (using default sponsor):
    - **Sponsor Name/Username**: admin (leave as default)
    - **Full Name**: David Miller
    - **Username**: davidmiller
    - **Email**: davidmiller@test.com (OPTIONAL - can be left blank)
    - **Password**: Test123!@#
    - **Confirm Password**: Test123!@#
    - Check terms checkbox
9. Click "Register New Member"
10. Verify success message appears with new member's details
11. Check database:

```sql
SELECT id, username, email, sponsor_id, referral_code
FROM users
WHERE username = 'davidmiller';
```

**Expected Results**:

-   ✅ "Register New Member" link visible in sidebar under "Member Actions" section
-   ✅ Page displays with sidebar and header (consistent admin layout)
-   ✅ **Sponsor Name/Username field** editable and pre-filled with logged-in user's username
-   ✅ Helper text shows default sponsor with full name and username
-   ✅ User successfully registered
-   ✅ `sponsor_id` = ID of logged-in user (e.g., 1 if admin) when using default
-   ✅ `referral_code` generated automatically (format: REF[8 chars])
-   ✅ Success message: "Member 'David Miller' has been registered successfully! Username: davidmiller"
-   ✅ Email is optional (can be left blank)
-   ✅ If email provided: `email_verified_at` = NULL (pending verification)
-   ✅ If email blank: `email` = NULL, `email_verified_at` = NULL
-   ✅ Form remains on same page ready for another registration
-   ✅ No "Back to Dashboard" button (uses sidebar navigation)

**Pass Criteria**:

-   ✅ Sidebar link accessible to all logged-in users
-   ✅ Sponsor field editable (can be changed to any valid username)
-   ✅ Sponsor defaults to logged-in user when field is empty or unchanged
-   ✅ New user created successfully
-   ✅ Unique referral code generated
-   ✅ No validation errors
-   ✅ Email remains optional
-   ✅ Success message displays clearly
-   ✅ Page maintains consistent admin layout with sidebar/header

**Screenshot Required**:

-   ✅ Sidebar showing "Register New Member" link
-   ✅ Registration form with editable sponsor field
-   ✅ Success message after registration

---

### Test Case 2.9: Multiple Members Registration by Same Sponsor

**Objective**: Verify a single user can register multiple downline members

**Steps**:

1. Login as `member` user
2. Navigate to `/register-member`
3. Register first downline member:
    - **Full Name**: Test Member 1
    - **Username**: testmember1
    - **Email**: (leave blank)
    - **Password**: Test123!@#
4. Verify success message appears
5. Register second downline member (form should be ready):
    - **Full Name**: Test Member 2
    - **Username**: testmember2
    - **Email**: testmember2@test.com
    - **Password**: Test123!@#
6. Register third downline member:
    - **Full Name**: Test Member 3
    - **Username**: testmember3
    - **Email**: (leave blank)
    - **Password**: Test123!@#
7. Check database:

```sql
SELECT id, username, email, sponsor_id, referral_code
FROM users
WHERE username IN ('testmember1', 'testmember2', 'testmember3')
ORDER BY id;
```

**Expected Results**:

-   ✅ All 3 members successfully registered
-   ✅ All have `sponsor_id` = 2 (member user's ID)
-   ✅ Each has unique `referral_code`
-   ✅ Mix of with/without email works correctly
-   ✅ Success message appears after each registration
-   ✅ Form remains accessible for next registration

**Pass Criteria**:

-   ✅ Multiple registrations by same sponsor work smoothly
-   ✅ All sponsor relationships correct
-   ✅ Workflow efficient for bulk registration

**Screenshot Required**: ✅ Database query showing all 3 members with correct sponsor_id

---

### Test Case 2.10: Member Registration with Sponsor Override

**Objective**: Verify user can change the sponsor to register member under a different upline

**Steps**:

1. Login as `admin` user
2. Navigate to `/register-member`
3. Verify sponsor field is pre-filled with `admin`
4. **Change sponsor field** to `member` (another existing user)
5. Fill in registration form:
    - **Sponsor Name/Username**: member (changed from admin)
    - **Full Name**: Sarah Johnson
    - **Username**: sarahjohnson
    - **Email**: (leave blank)
    - **Password**: Test123!@#
    - **Confirm Password**: Test123!@#
    - Check terms checkbox
6. Click "Register New Member"
7. Verify success message appears
8. Check database:

```sql
SELECT u.id, u.username, u.sponsor_id, s.username as sponsor_username
FROM users u
LEFT JOIN users s ON u.sponsor_id = s.id
WHERE u.username = 'sarahjohnson';
```

**Expected Results**:

-   ✅ Sponsor field is editable (not locked)
-   ✅ Changed sponsor value is accepted
-   ✅ User successfully registered
-   ✅ `sponsor_id` = ID of 'member' user (NOT admin)
-   ✅ Database shows correct sponsor relationship
-   ✅ Success message displays

**Pass Criteria**:

-   ✅ Sponsor override works correctly
-   ✅ Sponsor assignment follows the changed value
-   ✅ No errors when using different sponsor
-   ✅ Allows flexibility for network building strategies

**Screenshot Required**:

-   ✅ Form with changed sponsor field
-   ✅ Database query showing correct sponsor_id

---

### Test Case 2.11: Invalid Sponsor Name Validation

**Objective**: Verify system shows validation error for invalid sponsor names

**Steps**:

1. Navigate to `/register` (public registration)
2. Fill in registration form:
    - **Full Name**: Test User
    - **Username**: testuser123
    - **Email**: (leave blank)
    - **Sponsor Name/Username**: invalidusername123 (non-existent sponsor)
    - **Password**: Test123!@#
    - **Confirm Password**: Test123!@#
    - Check terms checkbox
3. Click "Register"
4. Verify validation error is displayed

**Expected Results**:

-   ✅ Registration fails with validation error
-   ✅ Error message displays: "The sponsor 'invalidusername123' could not be found. Please check the username, referral code, or full name."
-   ✅ Error appears under sponsor name field
-   ✅ User is NOT created in database
-   ✅ Form retains entered values (except password)
-   ✅ System does NOT silently default to admin sponsor

**Pass Criteria**:

-   ✅ Invalid sponsor name is properly validated
-   ✅ Clear error message guides user to correct the issue
-   ✅ No user created with invalid sponsor
-   ✅ Same validation applies to `/register-member` route

**Screenshot Required**:

-   ✅ Validation error message displayed
-   ✅ Form with error state

---

### Test Case 2.12: Empty Sponsor Name Defaults to Admin

**Objective**: Verify empty sponsor field defaults to admin sponsor

**Steps**:

1. Navigate to `/register`
2. Fill in registration form:
    - **Full Name**: Default Sponsor Test
    - **Username**: defaultsponsortest
    - **Email**: (leave blank)
    - **Sponsor Name/Username**: (leave completely blank)
    - **Password**: Test123!@#
    - **Confirm Password**: Test123!@#
    - Check terms checkbox
3. Click "Register"
4. Verify user is created successfully
5. Check database:

```sql
SELECT u.id, u.username, u.sponsor_id, s.username as sponsor_username
FROM users u
LEFT JOIN users s ON u.sponsor_id = s.id
WHERE u.username = 'defaultsponsortest';
```

**Expected Results**:

-   ✅ User successfully registered
-   ✅ `sponsor_id` = ID of admin user (email: admin@gawisherbal.com)
-   ✅ Database shows sponsor_username = 'admin'
-   ✅ Success message displays
-   ✅ No validation errors

**Pass Criteria**:

-   ✅ Empty sponsor field triggers admin default
-   ✅ Default behavior only applies when field is blank/empty
-   ✅ Admin fallback works correctly

**Screenshot Required**: ✅ Database query showing admin as sponsor

---

### Test Case 2.13: Member Registration Access Control

**Objective**: Verify only logged-in users can access member registration

**Steps**:

1. Logout completely
2. Attempt to access `/register-member` directly via URL
3. Verify redirect behavior

**Expected Results**:

-   ✅ Redirected to login page
-   ✅ Cannot access registration page without authentication
-   ✅ After login, can access `/register-member` successfully

**Pass Criteria**:

-   ✅ Proper authentication required
-   ✅ No unauthorized access possible

**Screenshot Required**: ✅ Redirect to login when accessing while logged out

---

## Test Suite 3: Admin MLM Settings Interface

### Test Case 3.1: Access MLM Settings Page

**Objective**: Verify admin can access MLM settings for Starter Package

**Steps**:

1. Login as admin: `admin@gawisherbal.com` / `Admin123!@#`
2. Navigate to `/admin/packages`
3. Locate "Starter Package" in the list
4. Click "Edit" button or navigate directly to `/admin/packages/starter-package/mlm-settings`

**Expected Results**:

-   ✅ MLM Settings page loads successfully
-   ✅ Page title: "MLM Settings: Starter Package"
-   ✅ Subtitle: "Configure 5-level commission structure"
-   ✅ "Back to Packages" button visible
-   ✅ Package price displayed: ₱1,000.00

**Pass Criteria**:

-   ✅ Page accessible without errors
-   ✅ Proper authorization (admin role required)

**Screenshot Required**: ✅ Full MLM Settings page view

---

### Test Case 3.2: Verify Default MLM Commission Values

**Objective**: Ensure MLM settings display correctly with seeded data

**Steps**:

1. On MLM Settings page, verify the commission table displays:

**Expected Table Display**:

| Level       | Description      | Commission (₱) | Percentage | Active |
| ----------- | ---------------- | -------------- | ---------- | ------ |
| **Level 1** | Direct Referrals | 200.00         | 20.00%     | ☑️     |
| **Level 2** | Indirect Level 2 | 50.00          | 5.00%      | ☑️     |
| **Level 3** | Indirect Level 3 | 50.00          | 5.00%      | ☑️     |
| **Level 4** | Indirect Level 4 | 50.00          | 5.00%      | ☑️     |
| **Level 5** | Indirect Level 5 | 50.00          | 5.00%      | ☑️     |

**Table Footer**:

-   Total MLM Commission: ₱400.00 (40.00%)
-   Company Profit (60% target): ₱600.00 (60.00%)

**Sidebar Summary**:

-   Package Price: ₱1,000.00
-   Total Commission: ₱400.00 (40.00% of price)
-   Company Profit: ₱600.00 (60.00% margin)

**Expected Results**:

-   ✅ All 5 levels displayed
-   ✅ Level 1 shows "Direct Referrals" badge (green)
-   ✅ Levels 2-5 show "Indirect Level X" badges (blue)
-   ✅ Commission amounts match database values
-   ✅ Percentages calculated correctly
-   ✅ All levels marked as active (checkboxes checked)
-   ✅ Total commission = ₱400 (40%)
-   ✅ Company profit = ₱600 (60%)
-   ✅ Warning message: "Total commission should not exceed 40% of package price"

**Pass Criteria**:

-   ✅ All values accurate
-   ✅ Calculations correct
-   ✅ Visual hierarchy clear

**Screenshot Required**:

-   ✅ Commission table with all 5 levels
-   ✅ Table footer with totals
-   ✅ Sidebar summary

---

### Test Case 3.3: Update MLM Commission Amounts (Valid)

**Objective**: Verify admin can update commission structure within valid limits

**Steps**:

1. On MLM Settings page, modify commission values:
    - **Level 1**: Change from 200.00 to **250.00**
    - **Level 2**: Change from 50.00 to **30.00**
    - **Level 3**: Keep at 50.00
    - **Level 4**: Keep at 50.00
    - **Level 5**: Change from 50.00 to **20.00**
2. **Observe real-time calculations**:
    - Total should update to ₱400.00 (250 + 30 + 50 + 50 + 20)
    - Company profit should update to ₱600.00
    - Percentages should recalculate
3. Click "Save MLM Settings"
4. Verify success message appears
5. Refresh page and verify changes persisted
6. Check database:

```sql
SELECT level, commission_amount
FROM mlm_settings
WHERE package_id = (SELECT id FROM packages WHERE slug = 'starter-package')
ORDER BY level;
```

**Expected Results**:

-   ✅ Real-time calculation updates as you type
-   ✅ Total commission: ₱400.00 (40%)
-   ✅ Company profit: ₱600.00 (60%)
-   ✅ No validation errors (within 40% limit)
-   ✅ Success message: "MLM settings updated successfully!"
-   ✅ Database reflects new values
-   ✅ Sidebar summary updates in real-time

**Pass Criteria**:

-   ✅ Changes saved successfully
-   ✅ Real-time calculations accurate
-   ✅ Database updated correctly

**Screenshot Required**:

-   ✅ Before update (original values)
-   ✅ During update (showing real-time calculation)
-   ✅ Success message after save
-   ✅ After page refresh (persistence check)

---

### Test Case 3.4: Attempt Invalid Update (Exceeds 40% Limit)

**Objective**: Verify validation prevents commission total from exceeding 40% limit

**Steps**:

1. On MLM Settings page, modify commission values to exceed limit:
    - **Level 1**: Change to **300.00**
    - **Level 2**: Change to **100.00**
    - **Level 3**: Keep at 50.00
    - **Level 4**: Keep at 50.00
    - **Level 5**: Keep at 50.00
2. **Observe visual feedback**:
    - Total should show ₱550.00 (55%)
    - Total row should turn red (table-danger)
    - Total amount should display in red text
3. Click "Save MLM Settings"
4. Verify error message appears

**Expected Results**:

-   ✅ Real-time visual warning (red highlighting)
-   ✅ Total commission: ₱550.00 (55%)
-   ✅ Company profit: ₱450.00 (45%)
-   ✅ Error message displayed: "Total MLM commission (₱550.00) exceeds 40% of package price (₱400.00)"
-   ✅ Changes NOT saved to database
-   ✅ Form returns with original values or input values preserved

**Pass Criteria**:

-   ✅ Validation works correctly
-   ✅ Clear error message
-   ✅ Database remains unchanged
-   ✅ Visual feedback helps user understand issue

**Screenshot Required**:

-   ✅ Form with excessive values (red highlighting)
-   ✅ Error message after save attempt

---

### Test Case 3.5: Toggle Commission Level Active/Inactive

**Objective**: Verify admin can enable/disable specific commission levels

**Steps**:

1. On MLM Settings page, uncheck the "Active" checkbox for **Level 5**
2. **Observe**: Total should recalculate to exclude Level 5
    - New total: ₱350.00 (if using default values: 200 + 50 + 50 + 50)
3. Click "Save MLM Settings"
4. Verify success message
5. Check database:

```sql
SELECT level, commission_amount, is_active
FROM mlm_settings
WHERE package_id = (SELECT id FROM packages WHERE slug = 'starter-package')
AND level = 5;
```

6. Refresh page and verify Level 5 checkbox is unchecked

**Expected Results**:

-   ✅ Checkbox toggle works smoothly
-   ✅ Total recalculates when toggling (optional feature)
-   ✅ Success message: "MLM settings updated successfully!"
-   ✅ Database `is_active` = 0 for Level 5
-   ✅ After refresh, Level 5 remains unchecked

**Pass Criteria**:

-   ✅ Toggle functionality works
-   ✅ Database updated correctly
-   ✅ Changes persist after page refresh

**Screenshot Required**:

-   ✅ Level 5 checkbox unchecked before save
-   ✅ After refresh showing persisted state

---

### Test Case 3.6: Verify Real-Time Calculation Accuracy

**Objective**: Ensure JavaScript calculations match server-side calculations

**Test Data**: Try multiple scenarios:

**Scenario A**: Equal distribution

-   Level 1: 80.00
-   Level 2: 80.00
-   Level 3: 80.00
-   Level 4: 80.00
-   Level 5: 80.00
-   **Expected Total**: ₱400.00 (40%)
-   **Expected Profit**: ₱600.00 (60%)

**Scenario B**: Front-loaded commissions

-   Level 1: 300.00
-   Level 2: 25.00
-   Level 3: 25.00
-   Level 4: 25.00
-   Level 5: 25.00
-   **Expected Total**: ₱400.00 (40%)
-   **Expected Profit**: ₱600.00 (60%)

**Scenario C**: Minimal commissions

-   Level 1: 100.00
-   Level 2: 10.00
-   Level 3: 10.00
-   Level 4: 10.00
-   Level 5: 10.00
-   **Expected Total**: ₱140.00 (14%)
-   **Expected Profit**: ₱860.00 (86%)

**Steps for Each Scenario**:

1. Enter commission values
2. **Verify real-time updates**:
    - Total commission amount
    - Total commission percentage
    - Company profit amount
    - Company profit percentage
    - Individual level percentages
    - Sidebar summary values
3. Click "Save" and verify server accepts the values
4. Refresh and verify persistence

**Pass Criteria**:

-   ✅ All calculations accurate to 2 decimal places
-   ✅ Real-time updates responsive (no lag)
-   ✅ Server-side validation matches client-side
-   ✅ All scenarios save successfully

**Screenshot Required**: ✅ One scenario showing calculations

---

## Test Suite 4: Package Management Integration

### Test Case 4.1: Verify Starter Package MLM Properties

**Objective**: Ensure Starter Package has correct MLM flags

**Steps**:

1. Login as admin
2. Navigate to `/admin/packages`
3. Find "Starter Package" in the list
4. Click "Edit" to view package details
5. Verify package properties

**Expected Results**:

-   ✅ Package name: "Starter Package"
-   ✅ Price: ₱1,000.00
-   ✅ `is_mlm_package` = true (or checkbox checked)
-   ✅ `max_mlm_levels` = 5
-   ✅ Metadata includes:
    -   `total_commission`: 400.00
    -   `company_profit`: 600.00
    -   `profit_margin`: "60%"

**Pass Criteria**:

-   ✅ MLM properties correctly set
-   ✅ Metadata accurate

**Screenshot Required**: ✅ Package edit page showing MLM properties

---

### Test Case 4.2: Access MLM Settings from Package List

**Objective**: Verify easy navigation to MLM settings from package management

**Steps**:

1. Navigate to `/admin/packages`
2. Locate "Starter Package"
3. Look for a link/button to access MLM settings (may need to add UI element)
4. Click to navigate to MLM settings

**Expected Results**:

-   ✅ Clear link/button available (e.g., "MLM Settings" button)
-   ✅ Navigates to `/admin/packages/starter-package/mlm-settings`

**Pass Criteria**:

-   ✅ Easy access from package list

**Note**: If no UI element exists, this is a UX improvement recommendation

**Screenshot Required**: ✅ Package list showing navigation element

---

## Test Suite 5: User Experience & UI/UX Validation

### Test Case 5.1: Registration Form UX

**Objective**: Verify registration form provides clear guidance

**Checklist**:

-   ✅ Sponsor field clearly labeled
-   ✅ Placeholder text helpful: "Sponsor Name or Referral Code (Optional)"
-   ✅ Helper text visible: "Leave blank to be assigned to default sponsor (Admin)"
-   ✅ Referral code alert appears when URL has ?ref parameter
-   ✅ Referral code alert is visually distinct (blue/info styling)
-   ✅ All form fields properly aligned
-   ✅ Form responsive on mobile devices
-   ✅ Icons display correctly for all fields
-   ✅ Validation messages clear and helpful

**Pass Criteria**:

-   ✅ Form intuitive for new users
-   ✅ No confusing or missing instructions

**Screenshot Required**:

-   ✅ Desktop view
-   ✅ Mobile view
-   ✅ With referral code alert

---

### Test Case 5.2: MLM Settings Page UX

**Objective**: Verify MLM settings interface is professional and intuitive

**Checklist**:

-   ✅ Page layout clean and organized
-   ✅ Table readable with proper spacing
-   ✅ Commission inputs easy to modify
-   ✅ Percentage displays update in real-time
-   ✅ Badge colors appropriate (green for direct, blue for indirect)
-   ✅ Warning message clearly visible
-   ✅ Sidebar summary helpful and not cluttered
-   ✅ Save button prominent
-   ✅ Back button easily accessible
-   ✅ Responsive design works on tablet/mobile
-   ✅ Number inputs allow decimals
-   ✅ Active/Inactive toggles easy to use

**Pass Criteria**:

-   ✅ Professional appearance
-   ✅ Intuitive workflow
-   ✅ No usability issues

**Screenshot Required**:

-   ✅ Full page view (desktop)
-   ✅ Tablet view
-   ✅ Mobile view

---

### Test Case 5.3: Error Handling & User Feedback

**Objective**: Verify system provides appropriate feedback

**Test Scenarios**:

**A. Successful Save**:

-   Action: Update MLM settings with valid values
-   Expected: Green success alert, message stays visible
-   ✅ Pass/Fail: **\_\_\_**

**B. Validation Error**:

-   Action: Try to save with total > 40%
-   Expected: Red error alert, specific error message, input values preserved
-   ✅ Pass/Fail: **\_\_\_**

**C. Database Error** (simulate by modifying database connection):

-   Action: Try to save with database offline
-   Expected: Error message explaining issue
-   ✅ Pass/Fail: **\_\_\_**

**D. Unauthorized Access**:

-   Action: Logout and try to access `/admin/packages/starter-package/mlm-settings`
-   Expected: Redirect to login page
-   ✅ Pass/Fail: **\_\_\_**

**E. Non-MLM Package**:

-   Action: Try to access MLM settings for non-MLM package (if one exists)
-   Expected: Redirect with error message
-   ✅ Pass/Fail: **\_\_\_**

**Pass Criteria**:

-   ✅ All scenarios handled gracefully
-   ✅ User always knows what happened

---

## Test Suite 6: Data Integrity & Edge Cases

### Test Case 6.1: Referral Code Uniqueness

**Objective**: Ensure referral codes are always unique

**Steps**:

1. Create 10 new users via registration
2. Extract all referral codes:

```sql
SELECT referral_code, COUNT(*) as count
FROM users
GROUP BY referral_code
HAVING count > 1;
```

**Expected Results**:

-   ✅ Query returns 0 rows (no duplicates)
-   ✅ All referral codes follow REF[8 chars] format
-   ✅ All codes are uppercase

**Pass Criteria**:

-   ✅ 100% uniqueness guaranteed

---

### Test Case 6.2: Sponsor Relationship Integrity

**Objective**: Verify sponsor relationships cannot be circular

**Steps**:

1. Attempt to create circular reference manually in database:

```sql
-- This should fail due to foreign key constraints
UPDATE users SET sponsor_id = 2 WHERE id = 1;
UPDATE users SET sponsor_id = 1 WHERE id = 2;
```

**Expected Results**:

-   ✅ Database prevents circular references
-   ✅ Foreign key constraint works correctly

**Pass Criteria**:

-   ✅ Circular references impossible

---

### Test Case 6.3: Wallet Balance Segregation Integrity

**Objective**: Ensure MLM and purchase balances remain separate

**Prerequisites**:

-   ✅ Migration `2025_10_06_173759_add_mlm_commission_type_to_transactions_table.php` must be run
-   ✅ `mlm_commission` transaction type added to transactions table enum

**Steps**:

1. Create test transaction to add to MLM balance:

```sql
-- Note: Use user_id, not wallet_id (transactions table uses user_id)
INSERT INTO transactions (user_id, type, amount, description, status, metadata, created_at, updated_at)
VALUES (1, 'mlm_commission', 100.00, 'Test MLM Income', 'completed', '{"level":1}', NOW(), NOW());

-- Update wallet by user_id for consistency
UPDATE wallets SET mlm_balance = mlm_balance + 100.00 WHERE user_id = 1;
```

2. Verify balances:

```sql
SELECT
    user_id,
    mlm_balance,
    purchase_balance,
    (mlm_balance + purchase_balance) as total_available
FROM wallets WHERE user_id = 1;
```

3. Verify transaction was recorded:

```sql
SELECT id, user_id, type, amount, description, status
FROM transactions
WHERE user_id = 1 AND type = 'mlm_commission'
ORDER BY id DESC LIMIT 1;
```

**Expected Results**:

-   ✅ `mlm_balance` increased by 100.00 (now 100.00)
-   ✅ `purchase_balance` unchanged (remains 1000.00)
-   ✅ `total_available` = 1100.00 (mlm_balance + purchase_balance)
-   ✅ Transaction recorded with type `mlm_commission`

**Pass Criteria**:

-   ✅ Balances properly segregated
-   ✅ No cross-contamination between MLM and purchase balances
-   ✅ Transaction correctly linked to user via `user_id`
-   ✅ MLM commission transaction type properly stored

**Important Notes**:

-   **Column Name**: Transactions table uses `user_id`, NOT `wallet_id`
-   **Transaction Type**: `mlm_commission` must be in the type enum
-   **Wallet Lookup**: Wallets can be queried by `user_id` or `id`
-   **1:1 Relationship**: Each user has exactly one wallet

---

### Test Case 6.4: Commission Structure Update Impact

**Objective**: Verify changing MLM settings doesn't affect existing data

**Steps**:

1. Note current MLM settings
2. Update Level 1 commission from ₱200 to ₱250
3. Verify package metadata updated
4. Check that no other data changed:

```sql
-- Ensure no historical commissions were modified
SELECT COUNT(*) FROM transactions WHERE type = 'mlm_commission';
-- Should be 0 since no commissions have been paid yet
```

**Expected Results**:

-   ✅ MLM settings updated
-   ✅ Package metadata updated
-   ✅ No impact on existing transactions (if any)

**Pass Criteria**:

-   ✅ Updates only affect future commissions
-   ✅ Historical data preserved

---

## Test Suite 7: Security & Authorization

### Test Case 7.1: MLM Settings Access Control

**Objective**: Verify only admins can access MLM settings

**Test Matrix**:

| User Type     | Action                                                | Expected Result           |
| ------------- | ----------------------------------------------------- | ------------------------- |
| Not Logged In | Access `/admin/packages/starter-package/mlm-settings` | Redirect to login         |
| Member User   | Access MLM settings URL                               | 403 Forbidden or redirect |
| Admin User    | Access MLM settings URL                               | ✅ Page loads             |
| Not Logged In | POST to update endpoint                               | 401/403 error             |
| Member User   | POST to update endpoint                               | 403 Forbidden             |
| Admin User    | POST to update endpoint                               | ✅ Update succeeds        |

**Steps for Each**:

1. Login as specified user (or don't login)
2. Navigate to MLM settings page
3. Verify access result

**Pass Criteria**:

-   ✅ All unauthorized access blocked
-   ✅ Only admins can view and modify

**Screenshot Required**: ✅ Access denied page for member user

---

### Test Case 7.2: CSRF Protection

**Objective**: Verify CSRF tokens protect update endpoints

**Steps**:

1. Open browser developer tools
2. Navigate to MLM settings page
3. Inspect form HTML
4. Verify `@csrf` token present
5. Try to submit form via curl without token:

```bash
curl -X PUT http://coreui_laravel_deploy.test/admin/packages/starter-package/mlm-settings \
  -d "settings[1][level]=1&settings[1][commission_amount]=200"
```

**Expected Results**:

-   ✅ CSRF token present in form
-   ✅ Curl request fails with 419 error (CSRF token mismatch)

**Pass Criteria**:

-   ✅ CSRF protection active

---

### Test Case 7.3: SQL Injection Prevention

**Objective**: Verify input sanitization prevents SQL injection

**Steps**:

1. On MLM settings page, try malicious input:
    - Commission amount: `200'; DROP TABLE mlm_settings;--`
2. Click "Save"
3. Check database:

```sql
SHOW TABLES LIKE 'mlm_settings';
```

**Expected Results**:

-   ✅ Validation error (non-numeric input)
-   ✅ Table still exists
-   ✅ No SQL injection successful

**Pass Criteria**:

-   ✅ Input validation prevents injection

---

## Test Suite 8: Performance & Scalability

### Test Case 8.1: Page Load Performance

**Objective**: Verify MLM settings page loads quickly

**Steps**:

1. Open browser developer tools (Network tab)
2. Navigate to MLM settings page
3. Measure total load time

**Expected Results**:

-   ✅ Page loads in < 2 seconds (first load)
-   ✅ Page loads in < 1 second (cached)
-   ✅ No excessive database queries (check Laravel Debugbar if installed)

**Pass Criteria**:

-   ✅ Acceptable performance

---

### Test Case 8.2: Real-Time Calculation Performance

**Objective**: Verify JavaScript calculations don't lag

**Steps**:

1. On MLM settings page, rapidly change commission values
2. Observe calculation updates

**Expected Results**:

-   ✅ Updates appear instant (< 100ms delay)
-   ✅ No UI freezing or lag
-   ✅ All values update simultaneously

**Pass Criteria**:

-   ✅ Smooth user experience

---

## Test Suite 9: Browser Compatibility

### Test Case 9.1: Cross-Browser Testing

**Objective**: Verify system works across major browsers

**Test Matrix**:

| Browser | Version | Registration | MLM Settings | Pass/Fail |
| ------- | ------- | ------------ | ------------ | --------- |
| Chrome  | Latest  | ⬜           | ⬜           | ⬜        |
| Firefox | Latest  | ⬜           | ⬜           | ⬜        |
| Safari  | Latest  | ⬜           | ⬜           | ⬜        |
| Edge    | Latest  | ⬜           | ⬜           | ⬜        |

**For Each Browser**:

1. Test user registration with referral code
2. Test MLM settings page functionality
3. Verify real-time calculations work
4. Check for console errors

**Pass Criteria**:

-   ✅ All features work in all browsers
-   ✅ No critical console errors

---

## Test Suite 10: Mobile Responsiveness

### Test Case 10.1: Mobile Registration

**Objective**: Verify registration works on mobile devices

**Steps**:

1. Open site on mobile device or use browser dev tools mobile emulation
2. Navigate to `/register`
3. Fill out registration form
4. Submit

**Checklist**:

-   ✅ All fields visible and accessible
-   ✅ Sponsor field not cut off
-   ✅ Keyboard doesn't obscure submit button
-   ✅ Form submission works
-   ✅ Success/error messages visible

**Pass Criteria**:

-   ✅ Full functionality on mobile

**Screenshot Required**: ✅ Mobile registration view

---

### Test Case 10.2: Mobile MLM Settings (Tablet)

**Objective**: Verify MLM settings usable on tablet

**Steps**:

1. Open site on tablet (or iPad emulation)
2. Navigate to MLM settings
3. Try to update commission values

**Checklist**:

-   ✅ Table readable (may scroll horizontally)
-   ✅ Input fields accessible
-   ✅ Sidebar visible or collapses appropriately
-   ✅ Save button accessible
-   ✅ Real-time calculations work

**Pass Criteria**:

-   ✅ Usable on tablet devices

**Screenshot Required**: ✅ Tablet view of MLM settings

---

## Summary & Sign-Off

### Phase 1 Test Results Summary

**Total Test Cases**: 48+ (updated to include optional email, member registration, and sponsor validation)
**Tests Passed**: **\_\_\_**
**Tests Failed**: **\_\_\_**
**Critical Issues**: **\_\_\_**
**Minor Issues**: **\_\_\_**
**Recommendations**: **\_\_\_**

### Key Changes from Original Plan:

1. **Optional Email Registration**: Users can now register without an email address
2. **Email Verification**: Only triggered if email is provided during registration
3. **Email Verification Fix**: Fortify email verification enabled with custom logic for users without email
4. **Profile Email Management**: Users can add, update, or remove email from their profile
5. **Login Flexibility**: Users can login with username or email
6. **Database Changes**: `email` column is now nullable with application-level uniqueness validation
7. **Member Registration**: Logged-in users can register new members via `/register-member`
8. **Flexible Sponsor Assignment**: Editable sponsor field pre-filled with logged-in user (positioned after email)
9. **Sponsor Validation**: Invalid sponsor names show validation errors (not silently defaulted to admin)
10. **Sidebar Navigation**: "Register New Member" link added to Member Actions section for easy access
11. **Sponsor Override**: Users can register members under different sponsors for strategic network building

---

### Critical Issues Found

(List any critical bugs that prevent system functionality)

1. ***
2. ***
3. ***

---

### Minor Issues / UX Improvements

(List non-critical issues or suggested improvements)

1. ***
2. ***
3. ***

---

### Sign-Off

**Tester Name**: ****\*\*\*\*****\_\_\_****\*\*\*\*****
**Date**: ****\*\*\*\*****\_\_\_****\*\*\*\*****
**Status**: ⬜ Approved for Production ⬜ Requires Fixes ⬜ Needs Re-testing

**Developer Notes**:

---

---

---

---

## Appendix A: Test Data Reference

### Default Users After Database Reset

| ID  | Username | Email                  | Role   | Sponsor | Referral Code | Purchase Balance |
| --- | -------- | ---------------------- | ------ | ------- | ------------- | ---------------- |
| 1   | admin    | admin@gawisherbal.com  | admin  | None    | REF[8chars]   | ₱1,000           |
| 2   | member   | member@gawisherbal.com | member | Admin   | REF[8chars]   | ₱1,000           |

### Default MLM Settings

| Level     | Commission  | Percentage | Description        |
| --------- | ----------- | ---------- | ------------------ |
| 1         | ₱200.00     | 20%        | Direct Referrals   |
| 2         | ₱50.00      | 5%         | 2nd Level Indirect |
| 3         | ₱50.00      | 5%         | 3rd Level Indirect |
| 4         | ₱50.00      | 5%         | 4th Level Indirect |
| 5         | ₱50.00      | 5%         | 5th Level Indirect |
| **Total** | **₱400.00** | **40%**    | -                  |

### Starter Package Details

-   **Name**: Starter Package
-   **Slug**: starter-package
-   **Price**: ₱1,000.00
-   **MLM Enabled**: Yes
-   **Max Levels**: 5
-   **Company Profit**: ₱600.00 (60%)

---

## Appendix B: SQL Queries for Testing

### Check All MLM Data

```sql
-- View all MLM settings with package info
SELECT
    p.name as package_name,
    p.price,
    ms.level,
    ms.commission_amount,
    ms.is_active,
    CONCAT(ROUND((ms.commission_amount / p.price) * 100, 2), '%') as percentage
FROM mlm_settings ms
JOIN packages p ON ms.package_id = p.id
ORDER BY p.id, ms.level;
```

### View Sponsor Network

```sql
-- View user sponsor relationships (tree structure)
SELECT
    u1.id,
    u1.username,
    u1.referral_code,
    u1.sponsor_id,
    u2.username as sponsor_username,
    u2.referral_code as sponsor_ref_code
FROM users u1
LEFT JOIN users u2 ON u1.sponsor_id = u2.id
ORDER BY u1.id;
```

### Check Wallet Balances

```sql
-- View all wallet balances
SELECT
    u.username,
    w.mlm_balance,
    w.purchase_balance,
    (w.mlm_balance + w.purchase_balance) as total
FROM users u
JOIN wallets w ON u.id = w.user_id
ORDER BY u.id;
```

---

**End of Phase 1 Testing Documentation**

---

## Next Steps After Phase 1

After completing Phase 1 testing:

1. Address all critical issues found
2. Consider UX improvements from feedback
3. Proceed to Phase 2: Referral Link System implementation
4. Continue testing with Phase 2 test cases (see below)

---

# Phase 2: Referral Link System & Auto-Fill Sponsor

**Status**: Ready for Testing
**Estimated Testing Time**: 2-3 hours
**Prerequisites**:

-   Phase 1 tests completed successfully
-   Database reset seeder has been run
-   At least 2 test users exist (Admin and one Member)

---

## Test Suite 7: Database Schema - Referral Clicks

### Test Case 7.1: Verify Referral Clicks Table Exists

**Objective**: Ensure referral_clicks table exists with correct schema

**Steps**:

1. Connect to MySQL database
2. Run the following SQL query:

```sql
DESCRIBE referral_clicks;
```

**Expected Results**:

-   ✅ Table exists with columns:
    -   `id` (bigint, primary key, auto_increment)
    -   `user_id` (bigint, foreign key to users)
    -   `ip_address` (varchar(45), nullable)
    -   `user_agent` (text, nullable)
    -   `clicked_at` (timestamp)
    -   `registered` (boolean, default false)
    -   `created_at` (timestamp)
    -   `updated_at` (timestamp)

**Pass Criteria**: All columns exist with correct data types and constraints

---

### Test Case 7.2: Verify Referral Clicks Indexes

**Objective**: Ensure proper indexes exist for query optimization

**Steps**:

1. Run SQL query:

```sql
SHOW INDEXES FROM referral_clicks;
```

**Expected Results**:

-   ✅ Primary key index on `id`
-   ✅ Foreign key index on `user_id`
-   ✅ Composite index on `(user_id, clicked_at)`

**Pass Criteria**: All expected indexes exist

---

## Test Suite 8: Referral Dashboard & Link Generation

### Test Case 8.1: Access Referral Dashboard

**Objective**: Verify referral dashboard is accessible to logged-in users

**Steps**:

1. Login as `member` user
2. Navigate to sidebar → "Member Actions" → "My Referral Link"
3. Verify URL is `/referral`
4. Check page loads successfully

**Expected Results**:

-   ✅ Page loads without errors
-   ✅ "My Referral Link" heading displays
-   ✅ Page shows referral code section
-   ✅ Page shows referral link section
-   ✅ Page shows QR code section
-   ✅ Page shows social share buttons
-   ✅ Page shows statistics cards

**Pass Criteria**: Dashboard displays all required sections

---

### Test Case 8.2: Verify Referral Code Display

**Objective**: Ensure user's unique referral code is displayed correctly

**Steps**:

1. On referral dashboard, locate "Your Unique Referral Code" section
2. Note the referral code displayed
3. Verify code format matches pattern `REF[A-Z0-9]{8}`

**Expected Results**:

-   ✅ Referral code displays in readonly input field
-   ✅ Code format is `REFXXXXXXXX` (3 letters + 8 alphanumeric characters)
-   ✅ "Copy Code" button is present next to input

**Pass Criteria**: Referral code displays with correct format

---

### Test Case 8.3: Verify Referral Link Display

**Objective**: Ensure shareable referral link is generated correctly

**Steps**:

1. On referral dashboard, locate "Your Referral Link" section
2. Check the link format
3. Verify link includes referral code parameter

**Expected Results**:

-   ✅ Referral link displays in readonly input field
-   ✅ Link format is `http://domain.com/register?ref=REFXXXXXXXX`
-   ✅ Referral code in link matches user's referral code
-   ✅ "Copy Link" button is present

**Pass Criteria**: Link generates correctly with ref parameter

---

### Test Case 8.4: Test Copy to Clipboard (Referral Code)

**Objective**: Verify copy to clipboard functionality works for referral code

**Steps**:

1. Click "Copy Code" button next to referral code
2. Observe toast notification
3. Paste into a text editor (Ctrl+V or Cmd+V)

**Expected Results**:

-   ✅ Toast notification appears with message "Referral code copied!"
-   ✅ Toast has green/success styling
-   ✅ Pasted text matches the displayed referral code exactly
-   ✅ Toast auto-dismisses after 3 seconds

**Pass Criteria**: Referral code copies successfully to clipboard

---

### Test Case 8.5: Test Copy to Clipboard (Referral Link)

**Objective**: Verify copy to clipboard functionality works for referral link

**Steps**:

1. Click "Copy Link" button next to referral link
2. Observe toast notification
3. Paste into a text editor

**Expected Results**:

-   ✅ Toast notification appears with message "Referral link copied!"
-   ✅ Toast has green/success styling
-   ✅ Pasted text matches the displayed referral link exactly
-   ✅ Link is a valid URL with ref parameter

**Pass Criteria**: Referral link copies successfully to clipboard

---

### Test Case 8.6: Verify QR Code Generation

**Objective**: Ensure QR code generates correctly for referral link

**Steps**:

1. On referral dashboard, locate QR code section
2. Verify QR code image displays
3. Use a QR code scanner app on mobile device
4. Scan the QR code

**Expected Results**:

-   ✅ QR code image displays (150x150 pixels)
-   ✅ QR code is scannable
-   ✅ Scanned URL matches the referral link
-   ✅ Caption "Scan to register with your referral" displays below QR code

**Pass Criteria**: QR code generates and scans to correct referral link

---

### Test Case 8.7: Verify Social Media Share Buttons

**Objective**: Ensure social sharing buttons work correctly

**Steps**:

1. On referral dashboard, locate social share buttons section
2. Check all buttons are present: Facebook, WhatsApp, Messenger, Twitter
3. Right-click each button and copy link address (don't actually open)
4. Verify each link contains the referral link URL

**Expected Results**:

-   ✅ 4 social share buttons display
-   ✅ Facebook button links to `https://www.facebook.com/sharer/sharer.php?u=...`
-   ✅ WhatsApp button links to `https://wa.me/?text=...`
-   ✅ Messenger button links to `https://www.messenger.com/t/?link=...`
-   ✅ Twitter button links to `https://twitter.com/intent/tweet?text=...`
-   ✅ All links contain URL-encoded referral link

**Pass Criteria**: All social share buttons generate correct sharing URLs

---

### Test Case 8.8: Verify Referral Statistics Display

**Objective**: Ensure referral statistics display correctly

**Steps**:

1. On referral dashboard, locate the three statistics cards
2. Verify each card displays:
    - Total Link Clicks
    - Direct Referrals
    - Conversion Rate

**Expected Results**:

-   ✅ Three statistics cards display in a row
-   ✅ "Total Link Clicks" card shows a number (default: 0)
-   ✅ "Direct Referrals" card shows a number (default: 0)
-   ✅ "Conversion Rate" card shows percentage with 1 decimal (default: 0.0%)
-   ✅ Numbers are styled prominently (large heading size)

**Pass Criteria**: All three statistics display with correct formatting

---

## Test Suite 9: Referral Click Tracking

### Test Case 9.1: Track Referral Click (New Session)

**Objective**: Verify referral clicks are tracked when clicking referral link

**Steps**:

1. Copy the `member` user's referral link from dashboard
2. Open an incognito/private browser window
3. Paste the referral link and press Enter
4. Note the current time
5. Check database for new referral click record

**SQL Query**:

```sql
SELECT * FROM referral_clicks
WHERE user_id = (SELECT id FROM users WHERE username = 'member')
ORDER BY clicked_at DESC
LIMIT 1;
```

**Expected Results**:

-   ✅ Registration page loads with ref parameter in URL
-   ✅ New record exists in `referral_clicks` table
-   ✅ `user_id` matches the member user's ID
-   ✅ `ip_address` is populated
-   ✅ `user_agent` is populated
-   ✅ `clicked_at` timestamp is recent (within last minute)
-   ✅ `registered` is false (default)

**Pass Criteria**: Referral click is tracked in database

---

### Test Case 9.2: Test Referral Code Session Storage

**Objective**: Verify referral code is stored in session

**Steps**:

1. In incognito window (from Test 9.1), verify still on registration page
2. Open browser DevTools → Application/Storage → Session Storage
3. Look for session data containing referral code

**Alternative Method** (if session storage isn't visible):

1. Inspect the sponsor field on registration form
2. Check if it's pre-filled with referral code

**Expected Results**:

-   ✅ Sponsor field is pre-filled with referral code (e.g., `REFXXXXXXXX`)
-   ✅ Sponsor field has `readonly` attribute
-   ✅ Success alert displays: "Referral Code Applied: REFXXXXXXXX"
-   ✅ Alert has green checkmark icon

**Pass Criteria**: Referral code is stored and pre-fills sponsor field

---

### Test Case 9.3: Test Multiple Clicks (Same IP)

**Objective**: Verify multiple clicks from same IP are tracked separately

**Steps**:

1. From incognito window, navigate away from site
2. Click the same referral link again
3. Check database for click records

**SQL Query**:

```sql
SELECT COUNT(*) as click_count
FROM referral_clicks
WHERE user_id = (SELECT id FROM users WHERE username = 'member')
AND clicked_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE);
```

**Expected Results**:

-   ✅ Multiple click records exist
-   ✅ Each click has its own record
-   ✅ All clicks from same IP show same IP address
-   ✅ Dashboard statistics update (Total Link Clicks increments)

**Pass Criteria**: Multiple clicks are tracked individually

---

### Test Case 9.4: Test Referral Statistics Update

**Objective**: Verify dashboard statistics reflect actual clicks

**Steps**:

1. Login as `member` user (in normal browser)
2. Navigate to referral dashboard
3. Check "Total Link Clicks" statistic
4. Compare with database count

**SQL Query**:

```sql
SELECT COUNT(*) FROM referral_clicks
WHERE user_id = (SELECT id FROM users WHERE username = 'member');
```

**Expected Results**:

-   ✅ Dashboard "Total Link Clicks" matches database count
-   ✅ "Direct Referrals" shows count of users with member as sponsor
-   ✅ "Conversion Rate" calculates correctly (referrals / clicks \* 100)

**Pass Criteria**: Statistics are accurate and update in real-time

---

## Test Suite 10: Auto-Fill Sponsor on Registration

### Test Case 10.1: Test Sponsor Field Pre-Fill

**Objective**: Verify sponsor field auto-fills from referral link

**Steps**:

1. Get `member` user's referral link
2. Open incognito window
3. Click referral link
4. Examine registration form

**Expected Results**:

-   ✅ Sponsor field displays referral code
-   ✅ Field has `readonly` attribute (cannot be edited)
-   ✅ Green success alert displays above field
-   ✅ Alert text: "Referral Code Applied: REFXXXXXXXX"
-   ✅ Alert includes green checkmark icon

**Pass Criteria**: Sponsor field is pre-filled and readonly

---

### Test Case 10.2: Test Registration with Referral Code

**Objective**: Verify new user registration with referral code works correctly

**Steps**:

1. On registration page with pre-filled sponsor (from Test 10.1)
2. Fill in registration form:
    - Full Name: `Test Referral User`
    - Username: `testreferral`
    - Email: `testreferral@example.com` (optional)
    - Password: `Test123!@#`
    - Confirm Password: `Test123!@#`
    - Accept Terms: ✓
3. Click "Create Account"
4. Wait for registration to complete

**Expected Results**:

-   ✅ Registration succeeds without errors
-   ✅ User is redirected to dashboard
-   ✅ Success message displays (check profile page)
-   ✅ User can see dashboard content

**Pass Criteria**: Registration completes successfully with referral code

---

### Test Case 10.3: Verify New User Sponsor Assignment

**Objective**: Ensure new user is assigned correct sponsor

**Steps**:

1. After registration (from Test 10.2), check database

**SQL Query**:

```sql
SELECT
    u.username,
    u.sponsor_id,
    s.username as sponsor_username,
    u.referral_code
FROM users u
LEFT JOIN users s ON u.sponsor_id = s.id
WHERE u.username = 'testreferral';
```

**Expected Results**:

-   ✅ `testreferral` user exists
-   ✅ `sponsor_id` matches `member` user's ID
-   ✅ `sponsor_username` shows `member`
-   ✅ New user has unique `referral_code` (auto-generated)

**Pass Criteria**: New user is correctly assigned to sponsor

---

### Test Case 10.4: Verify Referral Click Marked as Registered

**Objective**: Ensure referral click is marked as converted

**Steps**:

1. Check `referral_clicks` table for the click that led to registration

**SQL Query**:

```sql
SELECT * FROM referral_clicks
WHERE user_id = (SELECT id FROM users WHERE username = 'member')
AND registered = true
ORDER BY clicked_at DESC
LIMIT 1;
```

**Expected Results**:

-   ✅ At least one record has `registered = true`
-   ✅ Most recent registered click corresponds to `testreferral` registration
-   ✅ IP address matches the registration IP
-   ✅ Timestamp aligns with registration time

**Pass Criteria**: Referral click is marked as registered

---

### Test Case 10.5: Verify Sponsor Statistics Update

**Objective**: Ensure sponsor's statistics update after successful referral

**Steps**:

1. Login as `member` user
2. Navigate to referral dashboard
3. Check statistics

**Expected Results**:

-   ✅ "Direct Referrals" count incremented by 1
-   ✅ "Total Link Clicks" remains same or higher
-   ✅ "Conversion Rate" percentage updated

**Pass Criteria**: Sponsor's statistics reflect new referral

---

## Test Suite 11: Registration Without Referral Code

### Test Case 11.1: Test Direct Registration (No Ref Parameter)

**Objective**: Verify registration works without referral code

**Steps**:

1. Open incognito window
2. Navigate directly to `/register` (no ref parameter)
3. Fill registration form:
    - Full Name: `Test Direct User`
    - Username: `testdirect`
    - Email: `testdirect@example.com`
    - Password: `Test123!@#`
    - Leave sponsor field blank
4. Submit registration

**Expected Results**:

-   ✅ Registration succeeds
-   ✅ User is created in database
-   ✅ No error about missing sponsor

**Pass Criteria**: Registration without referral code works

---

### Test Case 11.2: Verify Default Sponsor Assignment

**Objective**: Ensure users without sponsor get assigned to admin

**Steps**:

1. Check database for newly created user

**SQL Query**:

```sql
SELECT
    u.username,
    u.sponsor_id,
    s.username as sponsor_username
FROM users u
LEFT JOIN users s ON u.sponsor_id = s.id
WHERE u.username = 'testdirect';
```

**Expected Results**:

-   ✅ User exists
-   ✅ `sponsor_id` matches admin user's ID
-   ✅ `sponsor_username` shows `admin`

**Pass Criteria**: User defaulted to admin sponsor

---

### Test Case 11.3: Test Manual Sponsor Entry (Valid)

**Objective**: Verify user can manually enter valid sponsor

**Steps**:

1. Open incognito window
2. Go to `/register`
3. Fill form with sponsor field = `member` (username)
4. Complete registration

**Expected Results**:

-   ✅ Registration succeeds
-   ✅ User is assigned to `member` as sponsor
-   ✅ No referral click is marked as registered

**Pass Criteria**: Manual sponsor entry works

---

### Test Case 11.4: Test Manual Sponsor Entry (Invalid)

**Objective**: Verify validation error for invalid sponsor

**Steps**:

1. Open incognito window
2. Go to `/register`
3. Fill form with sponsor field = `nonexistentuser`
4. Submit registration

**Expected Results**:

-   ✅ Registration fails with validation error
-   ✅ Error message: "The sponsor 'nonexistentuser' could not be found..."
-   ✅ Form retains entered data (except password)
-   ✅ User remains on registration page

**Pass Criteria**: Invalid sponsor is rejected with clear error

---

## Test Suite 12: Sidebar Navigation & UI

### Test Case -1.1: Verify Sidebar Link Exists

**Objective**: Ensure "My Referral Link" appears in sidebar

**Steps**:

1. Login as any user (member or admin)
2. Check sidebar navigation
3. Locate "Member Actions" section
4. Look for "My Referral Link" menu item

**Expected Results**:

-   ✅ "Member Actions" section exists in sidebar
-   ✅ "My Referral Link" menu item displays
-   ✅ Item has share icon (cil-share-alt)
-   ✅ Item appears below "Register New Member"

**Pass Criteria**: Sidebar link is visible and properly positioned

---

### Test Case 11.2: Test Active State Highlighting

**Objective**: Verify active state when on referral page

**Steps**:

1. Navigate to `/referral`
2. Check sidebar "My Referral Link" item

**Expected Results**:

-   ✅ "My Referral Link" has `active` class
-   ✅ Link is highlighted/styled differently from other links
-   ✅ Active state is visually clear

**Pass Criteria**: Active state displays correctly

---

### Test Case -1.3: Test Navigation Click

**Objective**: Verify clicking sidebar link navigates correctly

**Steps**:

1. From dashboard, click "My Referral Link" in sidebar
2. Check URL and page content

**Expected Results**:

-   ✅ URL changes to `/referral`
-   ✅ Referral dashboard loads
-   ✅ No console errors
-   ✅ Navigation completes smoothly

**Pass Criteria**: Navigation works without errors

---

## Test Suite 13: Edge Cases & Error Handling

### Test Case -1.1: Test Expired Session

**Objective**: Verify behavior when session expires

**Steps**:

1. Click referral link in incognito window
2. Wait for session to expire (or clear session storage manually)
3. Try to register

**Expected Results**:

-   ✅ Registration still works
-   ✅ Sponsor field may be empty (session cleared)
-   ✅ User gets assigned to default admin sponsor
-   ✅ No JavaScript errors

**Pass Criteria**: Expired session doesn't break registration

---

### Test Case -1.2: Test Invalid Referral Code in URL

**Objective**: Verify handling of invalid referral code with server-side validation

**Steps**:

1. Manually construct URL: `/register?ref=INVALIDCODE123`
2. Navigate to this URL
3. Check registration form
4. Attempt to submit registration with the invalid code

**Expected Results - Initial Load**:

-   ✅ Page loads normally
-   ✅ No referral click is tracked (code doesn't exist in database)
-   ✅ Sponsor field IS pre-filled with `INVALIDCODE123` (from URL parameter)
-   ✅ Sponsor field is readonly (same behavior as valid codes)
-   ✅ Success alert displays: "Referral Code Applied: INVALIDCODE123"
-   ✅ No error messages on initial load

**Expected Results - Form Submission**:

-   ✅ Registration fails with validation error
-   ✅ Error message displays: "The sponsor 'INVALIDCODE123' could not be found. Please check the username, referral code, or full name."
-   ✅ Form retains all entered data (except password)
-   ✅ User remains on registration page
-   ✅ User can see the validation error clearly

**Expected Results - Alternative Action**:

-   ✅ User can navigate back and use a different URL without the invalid ref parameter
-   ✅ User can manually register with a valid sponsor or leave blank for default admin sponsor

**Pass Criteria**: Invalid referral code is validated on submission, providing clear feedback to the user

**Security Note**: This approach is preferred because:
1. It validates on server-side (secure and authoritative)
2. Provides clear error messaging to users
3. Prevents silent failures
4. Allows users to understand what went wrong

---

### Test Case -1.3: Test Referral Click Without User Agent

**Objective**: Verify tracking works without user agent (rare case)

**Steps**:

1. Use curl or API tool to access referral link
2. Check if click is tracked

**Command**:

```bash
curl -I "http://localhost:8000/register?ref=MEMBER_REFERRAL_CODE"
```

**Expected Results**:

-   ✅ Click is tracked in database
-   ✅ `user_agent` field is NULL or empty
-   ✅ `ip_address` is populated
-   ✅ No errors in logs

**Pass Criteria**: Tracking works without user agent

---

### Test Case -1.4: Test Concurrent Referral Clicks

**Objective**: Verify system handles multiple simultaneous clicks

**Steps**:

1. Open 5 browser tabs/windows
2. Simultaneously click referral link in all tabs
3. Check database for duplicate tracking

**Expected Results**:

-   ✅ All clicks are tracked (5 records)
-   ✅ No database locking errors
-   ✅ Each record has correct timestamp
-   ✅ Statistics update correctly

**Pass Criteria**: Concurrent clicks are handled properly

---

### Test Case -1.5: Test Very Long User Agent String

**Objective**: Ensure long user agent strings don't break tracking

**Steps**:

1. Use browser extension to modify user agent to very long string (>1000 chars)
2. Click referral link
3. Check if tracking succeeds

**Expected Results**:

-   ✅ Click is tracked
-   ✅ User agent is stored (may be truncated if TEXT field has limit)
-   ✅ No database errors
-   ✅ Registration proceeds normally

**Pass Criteria**: Long user agent doesn't cause errors

---

## Test Suite 14: Security & Data Integrity

### Test Case -1.1: Test SQL Injection in Referral Code

**Objective**: Verify referral code is protected against SQL injection through parameterized queries and validation

**Steps**:

1. Construct malicious URL: `/register?ref=' OR '1'='1`
2. Navigate to URL
3. Check registration form
4. Attempt to submit registration with the malicious code

**Expected Results - Initial Load**:

-   ✅ Page loads normally
-   ✅ No SQL injection occurs during page load
-   ✅ No referral click is tracked (malicious code doesn't match any user)
-   ✅ Sponsor field IS pre-filled with `' OR '1'='1` (treated as literal string)
-   ✅ Sponsor field is readonly
-   ✅ Success alert displays: "Referral Code Applied: ' OR '1'='1"
-   ✅ No database corruption or unauthorized data access

**Expected Results - Form Submission**:

-   ✅ Registration fails with validation error
-   ✅ Error message displays: "The sponsor '' OR '1'='1' could not be found. Please check the username, referral code, or full name."
-   ✅ No SQL injection executed
-   ✅ Database queries use parameterized statements (Laravel's Eloquent ORM)
-   ✅ Malicious input treated as literal string value
-   ✅ Form retains all entered data (except password)
-   ✅ User remains on registration page

**Expected Results - Database Security**:

-   ✅ Database lookup: `WHERE referral_code = "' OR '1'='1"` (parameterized)
-   ✅ No records returned (no user has this as their referral code)
-   ✅ No SQL injection vulnerability exploited
-   ✅ No unauthorized data access
-   ✅ No database errors in logs

**Pass Criteria**: SQL injection is completely prevented through multiple layers of defense

**Security Analysis**:
1. **Parameterized Queries**: Laravel's Eloquent ORM uses prepared statements
2. **Input Sanitization**: Special characters treated as literal strings, not SQL commands
3. **Validation Layer**: Invalid sponsor triggers validation error before any database modification
4. **No Silent Failures**: User receives clear error message
5. **Defense in Depth**: Multiple security layers working together

**Technical Details**:
- Laravel's query builder automatically escapes and parameterizes all user input
- The string `' OR '1'='1` is passed as a bound parameter, not concatenated into SQL
- Actual query: `SELECT * FROM users WHERE referral_code = ?` with parameter `[' OR '1'='1]`
- This prevents any SQL injection regardless of input content

---

### Test Case -1.2: Test XSS in Referral Code Display

**Objective**: Verify referral code output is escaped

**Steps**:

1. Attempt to create user with malicious referral code (if possible)
2. Display referral link containing special characters
3. Check if HTML is rendered or escaped

**Expected Results**:

-   ✅ Special characters are escaped in HTML
-   ✅ No JavaScript execution occurs
-   ✅ No XSS vulnerability

**Pass Criteria**: XSS is prevented through output escaping

---

### Test Case -1.3: Test CSRF Protection on Copy Actions

**Objective**: Ensure copy actions don't expose CSRF tokens

**Steps**:

1. Open referral dashboard
2. Check page source for CSRF tokens
3. Test copy functionality

**Expected Results**:

-   ✅ Copy operations are client-side only
-   ✅ No CSRF token in copied content
-   ✅ No sensitive data exposed through clipboard

**Pass Criteria**: No security data leaks through copy

---

### Test Case -1.4: Test Referral Code Uniqueness

**Objective**: Verify all referral codes are unique

**Steps**:

1. Create 10 new test users
2. Check database for duplicate referral codes

**SQL Query**:

```sql
SELECT referral_code, COUNT(*) as count
FROM users
GROUP BY referral_code
HAVING count > 1;
```

**Expected Results**:

-   ✅ Query returns 0 rows (no duplicates)
-   ✅ All users have unique referral codes
-   ✅ All codes follow format `REF[A-Z0-9]{8}`

**Pass Criteria**: All referral codes are unique

---

### Test Case -1.5: Test Unauthorized Access to Others' Referral Stats

**Objective**: Ensure users can only see their own referral data

**Steps**:

1. Login as `member` user
2. Note member's user ID
3. Try to access referral dashboard via direct manipulation
4. Check if user can see other users' statistics

**Expected Results**:

-   ✅ User can only see their own referral link
-   ✅ User can only see their own statistics
-   ✅ No way to view others' referral codes
-   ✅ No API endpoints expose other users' referral data

**Pass Criteria**: Referral data is properly isolated per user

---

## Test Suite 15: Performance & Scalability

### Test Case -1.1: Test Dashboard Load Time

**Objective**: Verify referral dashboard loads quickly

**Steps**:

1. Open browser DevTools → Network tab
2. Navigate to `/referral`
3. Measure page load time

**Expected Results**:

-   ✅ Page loads in < 2 seconds
-   ✅ QR code generates without delay
-   ✅ No slow database queries
-   ✅ Statistics calculate quickly

**Pass Criteria**: Dashboard loads within acceptable time

---

### Test Case -1.2: Test Large Click History Performance

**Objective**: Verify performance with many referral clicks

**Steps**:

1. Manually insert 1000+ referral clicks for a user
2. Load referral dashboard
3. Check query performance

**SQL to Insert Test Data**:

```sql
INSERT INTO referral_clicks (user_id, ip_address, user_agent, clicked_at, registered)
SELECT
    (SELECT id FROM users WHERE username = 'member'),
    CONCAT('192.168.1.', FLOOR(RAND() * 255)),
    'Test User Agent',
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 365) DAY),
    RAND() > 0.8
FROM
    (SELECT 1 UNION SELECT 2 UNION SELECT 3) t1,
    (SELECT 1 UNION SELECT 2 UNION SELECT 3) t2,
    (SELECT 1 UNION SELECT 2 UNION SELECT 3) t3,
    (SELECT 1 UNION SELECT 2 UNION SELECT 3) t4
LIMIT 1000;
```

**Expected Results**:

-   ✅ Statistics still load quickly (< 3 seconds)
-   ✅ Conversion rate calculates correctly
-   ✅ No timeout errors
-   ✅ Database indexes are used efficiently

**Pass Criteria**: Performance remains acceptable with large dataset

---

### Test Case -1.3: Test Concurrent Dashboard Access

**Objective**: Verify multiple users can access dashboard simultaneously

**Steps**:

1. Open 10 browser windows/tabs
2. Login as different users in each
3. Navigate to `/referral` in all tabs simultaneously
4. Check for errors or conflicts

**Expected Results**:

-   ✅ All dashboards load successfully
-   ✅ No database locking issues
-   ✅ Each user sees their own data
-   ✅ No data mixing between users

**Pass Criteria**: Concurrent access works smoothly

---

## Test Suite 16: Mobile & Responsive Design

### Test Case -1.1: Test Mobile Dashboard Layout

**Objective**: Verify referral dashboard is mobile-friendly

**Steps**:

1. Open Chrome DevTools → Device Toolbar
2. Select mobile device (e.g., iPhone 12 Pro)
3. Navigate to `/referral`
4. Check layout and usability

**Expected Results**:

-   ✅ Dashboard is responsive
-   ✅ QR code displays correctly
-   ✅ All buttons are tappable
-   ✅ Statistics cards stack vertically
-   ✅ Text is readable without zooming
-   ✅ Copy buttons work on touch devices

**Pass Criteria**: Dashboard is fully functional on mobile

---

### Test Case -1.2: Test QR Code Scanning from Mobile

**Objective**: Verify QR code scans correctly on mobile devices

**Steps**:

1. Display referral dashboard on desktop
2. Use mobile phone camera/QR scanner app
3. Scan QR code from screen

**Expected Results**:

-   ✅ QR code scans successfully
-   ✅ Mobile browser opens registration page
-   ✅ Referral code is in URL
-   ✅ Sponsor field pre-fills on mobile

**Pass Criteria**: Mobile QR scanning works end-to-end

---

### Test Case -1.3: Test Social Share on Mobile

**Objective**: Verify social sharing works on mobile browsers

**Steps**:

1. Open referral dashboard on mobile browser
2. Tap Facebook share button
3. Check if Facebook app/page opens

**Expected Results**:

-   ✅ Social app opens (or web version)
-   ✅ Referral link is included in share
-   ✅ Share text is appropriate
-   ✅ User can complete share action

**Pass Criteria**: Social sharing functional on mobile

---

## Test Suite 16: Integration Testing

### Test Case -1.1: Test End-to-End Referral Flow

**Objective**: Complete full referral cycle from link to registration

**Steps**:

1. Login as `member`, get referral link
2. Share link via any method
3. Open link in incognito window
4. Complete registration with pre-filled sponsor
5. Verify new user appears in member's referrals
6. Check all statistics update correctly

**Expected Results**:

-   ✅ Click tracked
-   ✅ Registration succeeds
-   ✅ Sponsor assigned correctly
-   ✅ Click marked as registered
-   ✅ Statistics update immediately
-   ✅ New user receives unique referral code

**Pass Criteria**: Complete flow works without intervention

---

### Test Case -1.2: Test Member Registration After Referral Click

**Objective**: Verify member registration works with referral tracking

**Steps**:

1. Login as `admin`, get referral link
2. Click link in incognito window
3. Login as different member user
4. Navigate to `/register-member`
5. Register a new member

**Expected Results**:

-   ✅ Member registration page works
-   ✅ New member is registered successfully
-   ✅ New member's sponsor is the logged-in member (not admin)
-   ✅ Original referral click may or may not be marked (depends on session)

**Pass Criteria**: Member registration doesn't interfere with referral system

---

### Test Case -1.3: Test Referral Statistics After Multiple Registrations

**Objective**: Verify statistics accuracy with multiple referrals

**Steps**:

1. Register 5 new users using member's referral link
2. Register 3 users without referral link
3. Check member's statistics

**Expected Results**:

-   ✅ "Direct Referrals" shows 5
-   ✅ "Total Link Clicks" shows at least 5 (may be higher if clicks without registration)
-   ✅ "Conversion Rate" calculates correctly
-   ✅ Database counts match dashboard display

**Pass Criteria**: Statistics are accurate across multiple registrations

---

## Phase 2 Test Summary

### Critical Tests (Must Pass)

1. ✅ Test 7.1: Referral clicks table exists
2. ✅ Test 8.1: Referral dashboard accessible
3. ✅ Test 9.1: Referral clicks tracked
4. ✅ Test 10.2: Registration with referral code
5. ✅ Test 10.3: Sponsor assigned correctly
6. ✅ Test 10.4: Click marked as registered
7. ✅ Test 14.4: Referral codes are unique
8. ✅ Test 17.1: End-to-end flow works

### High Priority Tests

-   Test 8.4, 8.5: Copy to clipboard
-   Test 8.6: QR code generation
-   Test 8.7: Social share buttons
-   Test 11.4: Invalid sponsor validation
-   Test 14.1: SQL injection prevention
-   Test 14.5: Data isolation

### Medium Priority Tests

-   Test 8.8: Statistics display
-   Test 12.1-12.3: Sidebar navigation
-   Test 13.1-13.5: Edge cases
-   Test 15.1-15.3: Performance

### Optional Tests

-   Test 16.1-16.3: Mobile/responsive
-   Test 17.2-17.3: Integration scenarios

---

## Known Issues & Limitations

### Current Limitations

1. Referral clicks track by IP address - same user from same IP will create multiple records
2. No fraud detection for suspicious click patterns
3. QR code generation is client-side only (requires JavaScript)
4. Social share buttons open in new window (may be blocked by popup blockers)

### Future Enhancements

1. Add referral click analytics dashboard
2. Implement fraud detection (e.g., click rate limiting)
3. Add email notification when someone uses your referral
4. Create referral leaderboard
5. Add referral rewards/incentives

---

## Troubleshooting Guide

### Issue: QR Code Not Displaying

**Solution**:

1. Check browser console for JavaScript errors
2. Verify qrcodejs library is loaded
3. Check network tab for CDN availability

### Issue: Referral Code Not Pre-Filling

**Solution**:

1. Check browser allows session storage
2. Verify referral code in URL is valid
3. Check FortifyServiceProvider tracking code
4. Clear session and try again

### Issue: Statistics Not Updating

**Solution**:

1. Hard refresh page (Ctrl+Shift+R)
2. Check database query is correct
3. Verify relationship methods in User model
4. Check for caching issues

### Issue: Copy to Clipboard Not Working

**Solution**:

1. Check browser supports clipboard API
2. Verify HTTPS connection (required for clipboard)
3. Try in different browser
4. Check for JavaScript errors

---

## Next Steps After Phase 2

After completing Phase 2 testing:

1. Address all critical and high-priority issues found
2. Document any workarounds for known limitations
3. Prepare for Phase 3: Real-Time MLM Commission Distribution Engine
4. Review Phase 3 requirements and test cases

---

---

# Phase 3: Real-Time MLM Commission Distribution Engine

**Status**: ✅ Completed (2025-10-07)
**Testing Status**: Ready for Comprehensive Testing
**Estimated Testing Time**: 4-6 hours
**Prerequisites**:
- Phase 1 and Phase 2 completed and tested
- Database with at least 6 users in a 5-level upline chain
- Email service configured (optional, for email notification testing)

---

## Phase 3 Overview

Phase 3 implements the core MLM commission distribution engine that automatically calculates and distributes commissions to upline members when a Starter Package is purchased. The system includes:

- **Automatic Commission Calculation**: Based on 5-level MLM settings
- **Upline Traversal**: Walks up sponsor chain to distribute commissions
- **Multi-Channel Notifications**: Database, Broadcast, and Email (conditional)
- **Synchronous Processing**: Immediate commission distribution during checkout (using dispatchSync)
- **Transaction Audit Trail**: Complete tracking with metadata
- **Real-Time UI Updates**: Live balance updates and toast notifications

---

## Test Environment Setup for Phase 3

### Prerequisites Check

```bash
# 1. Verify migrations are up to date
php artisan migrate:status | grep mlm_fields_to_transactions

# 2. (Optional) Start log viewer to monitor commission processing
php artisan pail --timeout=0

# 3. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Create Test User Hierarchy

Create a 5-level upline chain for testing:

**Upline Structure**:
```
Level 5: Admin (sponsor_id: null)
    └─> Level 4: Member (sponsor_id: Admin) [Default user from /reset]
        └─> Level 3: Member2 (sponsor_id: Member)
            └─> Level 2: Member3 (sponsor_id: Member2)
                └─> Level 1: Member4 (sponsor_id: Member3)
                    └─> Buyer: Member5 (sponsor_id: Member4)
```

**Expected Commission Distribution** when Member5 purchases Starter Package (₱1,000):
- **Member4** (L1 - Direct Sponsor): ₱200
- **Member3** (L2): ₱50
- **Member2** (L3): ₱50
- **Member** (L4): ₱50
- **Admin** (L5): ₱50
- **Total Distributed**: ₱400
- **Company Profit**: ₱600

---

## Test Suite 7: Database Schema for Commission Tracking

### Test Case 7.1: Verify Transaction Table MLM Fields

**Objective**: Ensure transactions table has all MLM tracking fields

**Steps**:

```sql
-- Check for MLM tracking columns
DESCRIBE transactions;

-- Verify indexes exist
SHOW INDEXES FROM transactions WHERE Key_name LIKE '%source%';
```

**Expected Results**:

✅ `transactions` table has the following columns:
- `level` (TINYINT, nullable) - Stores MLM level (1-5)
- `source_order_id` (BIGINT UNSIGNED, nullable) - Links to orders.id
- `source_type` (ENUM: 'mlm', 'deposit', 'transfer', 'purchase', 'withdrawal', 'refund')

✅ Indexes exist:
- `transactions_source_order_id_index`
- `transactions_source_type_index`
- `transactions_type_source_type_index`

✅ Foreign key constraint exists on `source_order_id` → `orders(id)` with ON DELETE SET NULL

**Pass Criteria**: All fields and indexes present with correct data types

---

### Test Case 7.2: Verify Transaction Model Enhancements

**Objective**: Test Transaction model helper methods

**Steps**:

```php
// Run in php artisan tinker
use App\Models\Transaction;
use App\Models\User;

// Create a test MLM commission transaction
$user = User::first();
$transaction = Transaction::create([
    'user_id' => $user->id,
    'type' => 'mlm_commission',
    'source_type' => 'mlm',
    'level' => 1,
    'source_order_id' => 1,
    'amount' => 200.00,
    'description' => 'Test MLM Commission',
    'status' => 'completed',
    'metadata' => ['test' => true]
]);

// Test helper methods
$transaction->isMLMCommission(); // Should return true
$transaction->mlm_level; // Should return 1
$transaction->sourceOrder; // Should return Order instance or null
```

**Expected Results**:

✅ Transaction created successfully with MLM fields
✅ `isMLMCommission()` returns `true`
✅ `mlm_level` attribute accessor works
✅ `sourceOrder` relationship loads correctly

**Pass Criteria**: All model methods work as expected

---

## Test Suite 8: MLM Commission Service

### Test Case 8.1: Service Layer Instantiation

**Objective**: Verify MLMCommissionService can be instantiated

**Steps**:

```php
// Run in php artisan tinker
use App\Services\MLMCommissionService;

$service = app(MLMCommissionService::class);

// Check available methods
get_class_methods($service);
```

**Expected Results**:

✅ Service instantiates without errors
✅ Methods available: `processCommissions`, `getUplineTree`, `calculateTotalCommission`, `getCommissionBreakdown`

**Pass Criteria**: Service accessible via Laravel container

---

### Test Case 8.2: Upline Tree Retrieval

**Objective**: Test upline traversal logic

**Steps**:

```php
// Run in php artisan tinker
use App\Services\MLMCommissionService;
use App\Models\User;

$service = app(MLMCommissionService::class);

// Get a user with upline chain (e.g., Member5)
$buyer = User::where('username', 'testbuyer')->first();

// Get upline tree
$uplineTree = $service->getUplineTree($buyer, 5);

// Display results
foreach ($uplineTree as $node) {
    echo "Level {$node['level']}: {$node['user_name']} (ID: {$node['user_id']})\n";
}
```

**Expected Results**:

✅ Returns array of upline users
✅ Maximum 5 levels returned
✅ Each node contains: `level`, `user`, `user_id`, `user_name`, `referral_code`
✅ Levels increment correctly (1, 2, 3, 4, 5)
✅ Stops at users with no sponsor

**Pass Criteria**: Upline tree correctly traverses sponsor chain

---

### Test Case 8.3: Commission Breakdown Calculation

**Objective**: Test commission calculation without processing

**Steps**:

```php
// Run in php artisan tinker
use App\Services\MLMCommissionService;
use App\Models\Order;

$service = app(MLMCommissionService::class);

// Get an order for Starter Package
$order = Order::whereHas('package', function($q) {
    $q->where('is_mlm_package', true);
})->first();

// Get commission breakdown
$breakdown = $service->getCommissionBreakdown($order);

// Display
foreach ($breakdown as $item) {
    echo "Level {$item['level']}: {$item['user_name']} - ₱{$item['commission']}\n";
}

// Verify total
$total = array_sum(array_column($breakdown, 'commission'));
echo "\nTotal: ₱{$total}\n";
```

**Expected Results**:

✅ Breakdown shows up to 5 levels
✅ Level 1 commission: ₱200
✅ Levels 2-5 commission: ₱50 each
✅ Total commission: ₱400 (or less if upline chain is shorter)
✅ Only returns levels where upline exists

**Pass Criteria**: Calculations match MLM settings

---

## Test Suite 9: Wallet Model MLM Enhancements

### Test Case 9.1: MLM Balance Methods

**Objective**: Test Wallet model MLM-specific methods

**Steps**:

```php
// Run in php artisan tinker
use App\Models\User;

$user = User::first();
$wallet = $user->wallet;

// Test balance getters
echo "MLM Balance: ₱" . number_format($wallet->mlm_balance, 2) . "\n";
echo "Purchase Balance: ₱" . number_format($wallet->purchase_balance, 2) . "\n";
echo "Total Balance: ₱" . number_format($wallet->total_balance, 2) . "\n";
echo "Withdrawable Balance: ₱" . number_format($wallet->withdrawable_balance, 2) . "\n";

// Test MLM balance summary
$summary = $wallet->getMLMBalanceSummary();
print_r($summary);

// Test withdrawal check
$canWithdraw = $wallet->canWithdraw(100);
echo "Can withdraw ₱100: " . ($canWithdraw ? 'Yes' : 'No') . "\n";
```

**Expected Results**:

✅ All balance attributes return correct values
✅ `total_balance` = `mlm_balance` + `purchase_balance`
✅ `withdrawable_balance` = `mlm_balance`
✅ `getMLMBalanceSummary()` returns array with all balances
✅ `canWithdraw()` returns true only if MLM balance is sufficient

**Pass Criteria**: All methods work correctly

---

### Test Case 9.2: Combined Balance Deduction

**Objective**: Test deductCombinedBalance() priority logic

**Steps**:

```php
// Run in php artisan tinker
use App\Models\User;
use Illuminate\Support\Facades\DB;

$user = User::first();
$wallet = $user->wallet;

// Set initial balances
DB::table('wallets')->where('id', $wallet->id)->update([
    'purchase_balance' => 300.00,
    'mlm_balance' => 200.00
]);

$wallet->refresh();

echo "Before: Purchase=₱{$wallet->purchase_balance}, MLM=₱{$wallet->mlm_balance}\n";

// Deduct ₱400 (should take ₱300 from purchase, ₱100 from MLM)
$result = $wallet->deductCombinedBalance(400);

$wallet->refresh();

echo "After: Purchase=₱{$wallet->purchase_balance}, MLM=₱{$wallet->mlm_balance}\n";
echo "Result: " . ($result ? 'Success' : 'Failed') . "\n";
```

**Expected Results**:

✅ Method returns `true`
✅ Purchase balance deducted first: ₱300 → ₱0
✅ Remaining deducted from MLM balance: ₱200 → ₱100
✅ Total deducted: ₱400

**Pass Criteria**: Deduction priority works as expected (purchase first, then MLM)

---

## Test Suite 10: Multi-Channel Notifications

### Test Case 10.1: Database Notification Creation

**Objective**: Verify database notifications are created for all upline members

**Steps**:

1. Complete a Starter Package purchase
2. Wait for commission processing to complete
3. Query notifications table:

```sql
SELECT
    n.id,
    n.notifiable_id,
    u.username,
    n.type,
    JSON_EXTRACT(n.data, '$.commission') as commission,
    JSON_EXTRACT(n.data, '$.level') as level,
    JSON_EXTRACT(n.data, '$.message') as message,
    n.read_at,
    n.created_at
FROM notifications n
JOIN users u ON n.notifiable_id = u.id
WHERE n.type = 'App\\\\Notifications\\\\MLMCommissionEarned'
ORDER BY n.created_at DESC
LIMIT 10;
```

**Expected Results**:

✅ 5 notifications created (one for each upline member)
✅ `type` = 'App\Notifications\MLMCommissionEarned'
✅ `data` field contains: `commission`, `level`, `buyer_name`, `order_number`, `message`
✅ `read_at` is NULL (unread)
✅ Message format: "You earned ₱X from [Buyer]'s purchase! (Level N)"

**Pass Criteria**: All upline members have database notifications

---

### Test Case 10.2: Email Notification - Verified Email

**Objective**: Test conditional email sending to verified email addresses

**Steps**:

1. Ensure at least one upline member has verified email:

```sql
UPDATE users SET email_verified_at = NOW() WHERE username = 'user1';
```

2. Complete Starter Package purchase
3. Check mail logs or Mailtrap/MailHog inbox
4. Verify email content

**Expected Results**:

✅ Email sent ONLY to upline members with `email_verified_at` NOT NULL
✅ Email subject: "🎉 New MLM Commission Earned!"
✅ Email contains:
  - Greeting with upline member's name
  - Commission amount (₱200 or ₱50)
  - Level designation (1st Level Direct Referral or Nth Level Indirect)
  - Buyer's name
  - Order number
  - Package name
  - "View Dashboard" button/link
  - Professional HTML formatting

**Pass Criteria**: Emails sent only to verified addresses with correct content

---

### Test Case 10.3: Email Notification - Unverified Email

**Objective**: Verify emails are NOT sent to unverified addresses

**Steps**:

1. Ensure at least one upline member has unverified email:

```sql
UPDATE users SET email_verified_at = NULL WHERE username = 'user2';
```

2. Complete Starter Package purchase
3. Check mail logs
4. Check database notifications for user2

**Expected Results**:

✅ NO email sent to user2
✅ Database notification still created for user2
✅ Broadcast notification still sent (if configured)
✅ Log entry: "MLM Commission Notification Sent" with `has_verified_email: false`

**Pass Criteria**: No email sent, but other notification channels work

---

### Test Case 10.4: Broadcast Notification (Optional)

**Objective**: Test real-time broadcast notifications

**Prerequisites**: Laravel Echo + Pusher/WebSocket configured

**Steps**:

1. Open browser with upline member logged in
2. Open browser console and check for Echo connection
3. Complete Starter Package purchase from another browser/account
4. Watch for real-time toast notification

**Expected Results**:

✅ Toast notification appears without page refresh
✅ Toast message: "You earned ₱X from [Buyer]'s purchase! (Level N)"
✅ Toast has success styling (green background)
✅ Toast auto-dismisses after 5 seconds

**Pass Criteria**: Real-time notification received (if broadcasting enabled)

---

## Test Suite 11: MLM Balance Widget

### Test Case 11.1: Widget Display

**Objective**: Verify MLM balance widget appears on dashboard

**Steps**:

1. Login as any user
2. Navigate to `/dashboard`
3. Locate "MLM Earnings" card
4. Verify all elements are visible

**Expected Results**:

✅ "MLM Earnings" card with success (green) header
✅ "Withdrawable" badge displayed
✅ MLM Balance shows current balance
✅ Purchase Balance shows current balance
✅ Total Balance calculates correctly
✅ "Withdraw" button links to `/withdrawals/create`

**Pass Criteria**: Widget displays with correct data

---

### Test Case 11.2: Live Balance Update (Optional)

**Objective**: Test real-time balance updates

**Prerequisites**: Laravel Echo configured

**Steps**:

1. Login as upline member (e.g., Member4)
2. Keep dashboard open
3. From another browser, complete purchase as Member5 (sponsored by Member4)
4. Watch MLM balance widget on Member4's dashboard

**Expected Results**:

✅ MLM balance updates without page refresh
✅ Pulse animation plays (green flash)
✅ New balance displays correctly (+₱200 for Level 1)
✅ Total balance updates accordingly
✅ Toast notification appears

**Pass Criteria**: Live updates work without page refresh

---

### Test Case 11.2: Network Stats Panel

**Objective**: Verify MLM Network Stats panel

**Steps**:

1. Login as user with referrals
2. Check "MLM Network Stats" panel on dashboard
3. Verify statistics

**Expected Results**:

✅ "Direct Referrals" count matches `users.sponsor_id` count
✅ "Total Earnings" matches `wallets.mlm_balance`
✅ "My Referral Link" button links to `/referral`
✅ "Register Member" button links to `/register-member`

**Pass Criteria**: Statistics are accurate

---

## Test Suite 12: Commission Calculation Accuracy

### Test Case 12.1: Full 5-Level Chain

**Objective**: Test commission distribution with complete upline

**Setup**:
- Create 5-level upline chain + buyer (6 users total)
- Ensure all users have wallets with ₱0 MLM balance

**Steps**:

1. Record initial balances:

```sql
SELECT u.username, w.mlm_balance
FROM users u
JOIN wallets w ON u.id = w.user_id
WHERE u.username IN ('admin', 'member', 'member2', 'member3', 'member4', 'member5')
ORDER BY u.id;
```

2. Login as Member5, purchase Starter Package
3. Wait for commission processing
4. Record final balances

**Expected Results**:

```
Member4 (L1):  ₱0 → ₱200  (+₱200)
Member3 (L2):  ₱0 → ₱50   (+₱50)
Member2 (L3):  ₱0 → ₱50   (+₱50)
Member (L4):   ₱0 → ₱50   (+₱50)
Admin (L5):    ₱0 → ₱50   (+₱50)
Member5:       (No commission - buyer doesn't earn from own purchase)
```

✅ Total distributed: ₱400
✅ Company profit: ₱600 (₱1,000 - ₱400)

**Pass Criteria**: All commissions distributed correctly

---

### Test Case 12.2: Partial Upline Chain (3 Levels)

**Objective**: Test commission distribution with incomplete upline

**Setup**:
- User with only 3 levels of upline (not full 5)

**Steps**:

1. Create upline: Member2 → Member3 → Member4 → Member5
2. Member5 purchases Starter Package
3. Check commission distribution

**Expected Results**:

```
Member4 (L1):  +₱200
Member3 (L2):  +₱50
Member2 (L3):  +₱50
(No L4 or L5 commissions - upline ends)
```

✅ Total distributed: ₱300 (not ₱400)
✅ Remaining ₱100 stays with company

**Pass Criteria**: System handles incomplete upline gracefully

---

### Test Case 12.3: Orphaned User (No Sponsor)

**Objective**: Test purchase by user with no sponsor

**Steps**:

1. Create user with `sponsor_id = NULL`
2. User purchases Starter Package
3. Check transaction logs

**Expected Results**:

✅ No commissions distributed (no upline)
✅ Full ₱1,000 is company profit
✅ No error in logs
✅ Job completes successfully
✅ Log: "Order does not have upline for commission distribution"

**Pass Criteria**: System handles orphaned users without errors

---

### Test Case 12.4: Non-MLM Package Purchase

**Objective**: Verify commissions NOT distributed for regular packages

**Steps**:

1. Create regular package with `is_mlm_package = false`
2. User purchases this package
3. Check for commission job dispatch

**Expected Results**:

✅ ProcessMLMCommissions job NOT dispatched
✅ No commission transactions created
✅ Log: "Order does not have MLM package" (if job somehow triggered)

**Pass Criteria**: Non-MLM purchases don't trigger commissions

---

## Test Suite 13: Transaction Audit Trail

### Test Case 13.1: Transaction Metadata

**Objective**: Verify transaction records contain complete metadata

**Steps**:

```sql
SELECT
    t.id,
    t.user_id,
    t.type,
    t.source_type,
    t.level,
    t.amount,
    t.description,
    t.metadata,
    t.created_at
FROM transactions
WHERE type = 'mlm_commission'
ORDER BY created_at DESC
LIMIT 5;
```

**Expected Results**:

✅ Each transaction has complete metadata:
```json
{
  "buyer_id": 123,
  "buyer_name": "Member5",
  "package_id": 1,
  "package_name": "Starter Package",
  "order_number": "ORD-2025-10-07-0001",
  "commission_level": 1
}
```

✅ Description format: "Level N MLM Commission from [Buyer] (Order #XXX)"

**Pass Criteria**: All metadata fields populated correctly

---

### Test Case 13.2: Transaction Timeline

**Objective**: Verify commission transactions created in correct order

**Steps**:

```sql
SELECT
    t.id,
    t.level,
    t.amount,
    t.created_at,
    TIMESTAMPDIFF(MICROSECOND, LAG(t.created_at) OVER (ORDER BY t.created_at), t.created_at) as time_diff_us
FROM transactions
WHERE type = 'mlm_commission'
  AND source_order_id = (SELECT MAX(id) FROM orders)
ORDER BY t.created_at ASC;
```

**Expected Results**:

✅ All 5 transactions created within milliseconds of each other
✅ Level order may vary (async processing)
✅ All transactions have same `source_order_id`
✅ Total time < 1 second for all commissions

**Pass Criteria**: Transaction timing is reasonable

---

### Test Case 13.3: Source Order Linkage

**Objective**: Test foreign key relationship between transactions and orders

**Steps**:

```sql
SELECT
    t.id as transaction_id,
    t.level,
    t.amount,
    o.id as order_id,
    o.order_number,
    o.total_amount,
    p.name as package_name
FROM transactions t
JOIN orders o ON t.source_order_id = o.id
JOIN packages p ON o.package_id = p.id
WHERE t.type = 'mlm_commission'
ORDER BY t.created_at DESC
LIMIT 10;
```

**Expected Results**:

✅ All MLM commission transactions link to valid orders
✅ Linked order has `package.is_mlm_package = true`
✅ Transaction `level` values are 1-5
✅ Sum of transaction amounts ≤ order total amount

**Pass Criteria**: Referential integrity maintained

---

## Test Suite 14: Error Handling & Edge Cases

### Test Case 14.1: Insufficient Wallet Balance (Buyer)

**Objective**: Test commission when buyer has insufficient funds

**Steps**:

1. Set Member5's wallet balance to ₱500 (less than ₱1,000)
2. Attempt to purchase Starter Package
3. Check if commission job is dispatched

**Expected Results**:

✅ Payment fails at checkout
✅ Order not created
✅ ProcessMLMCommissions job NOT dispatched
✅ No commissions distributed
✅ User sees error: "Insufficient wallet balance"

**Pass Criteria**: Commission job not triggered on failed payment

---

### Test Case 14.2: Missing Wallet (Upline Member)

**Objective**: Test commission distribution when upline member has no wallet

**Steps**:

1. Delete wallet for one upline member:

```sql
DELETE FROM wallets WHERE user_id = (SELECT id FROM users WHERE username = 'user3');
```

2. Complete Starter Package purchase
3. Check logs and transactions

**Expected Results**:

✅ Job completes with warnings
✅ Commissions distributed to users WITH wallets
✅ Skipped user logged: "User has no wallet"
✅ Other upline members still receive commissions
✅ Total distributed < ₱400 (one level skipped)

**Pass Criteria**: System gracefully handles missing wallets

---

### Test Case 14.3: Circular Sponsorship (Should Be Prevented)

**Objective**: Verify circular sponsorship cannot occur

**Steps**:

```php
// Run in tinker
use App\Models\User;

$user1 = User::where('username', 'user1')->first();
$user2 = User::where('username', 'user2')->first();

// Try to create circular reference
try {
    $user1->sponsor_id = $user2->id;
    $user1->save();

    $user2->sponsor_id = $user1->id;
    $user2->save();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

**Expected Results**:

✅ Exception thrown: "Circular sponsor reference detected"
✅ Database trigger prevents update
✅ Model validation prevents save
✅ Users remain with original sponsors

**Pass Criteria**: Circular references blocked at multiple levels

---

### Test Case 14.4: Duplicate Commission Prevention

**Objective**: Ensure same order doesn't trigger commissions twice

**Steps**:

1. Complete a Starter Package purchase
2. Wait for commissions to be distributed
3. Manually dispatch job again for same order:

```php
// In tinker
use App\Jobs\ProcessMLMCommissions;
use App\Models\Order;

$order = Order::latest()->first();
ProcessMLMCommissions::dispatch($order);
```

4. Check transaction count for this order

**Expected Results**:

✅ Job runs again but logic prevents duplicate transactions
✅ OR job checks for existing commissions and skips
✅ Transaction count for order remains at 5 (not 10)

**Pass Criteria**: No duplicate commissions created

---

## Test Suite 15: Performance & Load Testing

### Test Case 15.1: Single Order Processing Time

**Objective**: Measure commission processing duration

**Steps**:

1. Monitor logs with timestamps
2. Complete Starter Package purchase
3. Measure time from "Job Started" to "Job Completed"

```bash
tail -f storage/logs/laravel.log | grep -E "MLM Commission Job (Started|Completed)"
```

**Expected Results**:

✅ Processing time < 1 second for 5-level chain
✅ Database transaction commits successfully
✅ No timeout errors

**Performance Benchmarks**:
- Excellent: < 500ms
- Good: 500ms - 1s
- Acceptable: 1s - 2s
- Poor: > 2s (investigate bottlenecks)

**Pass Criteria**: Processing completes within acceptable time

---

### Test Case 15.2: Concurrent Orders

**Objective**: Test multiple simultaneous purchases

**Steps**:

1. Create 3-5 test buyers
2. Simultaneously (within 5 seconds) purchase Starter Package from each
3. Monitor queue worker
4. Verify all commissions distributed correctly

**Expected Results**:

✅ All jobs processed successfully
✅ No race conditions
✅ Each order generates correct commission count
✅ Total transactions = 5 × number_of_orders
✅ No database deadlocks

**Pass Criteria**: System handles concurrent purchases without errors

---

---

## Test Suite 16: Integration Testing

### Test Case 16.1: End-to-End Purchase Flow

**Objective**: Complete purchase flow from cart to commission distribution

**Steps**:

1. **Setup**: Login as Member5 (5-level upline)
2. **Add to Cart**: Add Starter Package to cart
3. **Verify Cart**: Check cart shows ₱1,000 total
4. **Checkout**: Proceed to checkout
5. **Payment**: Complete wallet payment
6. **Confirmation**: Verify order confirmation page
7. **Wait**: Allow 5-10 seconds for commission processing
8. **Verify Commissions**: Check all upline members received commissions
9. **Verify Notifications**: Check database notifications created
10. **Verify Emails**: Check emails sent (to verified addresses only)

**Expected Results**:

✅ All steps complete without errors
✅ Order created with status "confirmed"
✅ Payment deducted from buyer's wallet
✅ Commission processing completes synchronously (before redirect)
✅ 5 transactions created (or fewer if upline < 5 levels)
✅ All upline wallets updated correctly
✅ Notifications sent via all channels

**Pass Criteria**: Complete flow works end-to-end

---

### Test Case 16.2: Commission Display in Member Dashboard

**Objective**: Verify upline members can see earnings

**Steps**:

1. Complete purchase as Member5
2. Login as Member4 (Level 1 sponsor)
3. Navigate to dashboard
4. Check MLM balance widget
5. Navigate to transactions page
6. Check for MLM commission transaction

**Expected Results**:

✅ Dashboard shows updated MLM balance (+₱200)
✅ MLM balance widget displays correctly
✅ Network stats show 1 direct referral (Member5)
✅ Transactions page shows MLM commission entry
✅ Transaction description clear and informative

**Pass Criteria**: Earnings visible in member interface

---

## Test Suite 18: Admin Monitoring & Reports

### Test Case 18.1: Commission Transaction Report

**Objective**: Generate admin report of all MLM commissions

**Steps**:

```sql
-- Admin Report: MLM Commissions Summary
SELECT
    DATE(t.created_at) as date,
    COUNT(*) as commission_count,
    SUM(t.amount) as total_commissions,
    COUNT(DISTINCT t.source_order_id) as orders_processed,
    AVG(t.amount) as avg_commission
FROM transactions t
WHERE t.type = 'mlm_commission'
  AND t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(t.created_at)
ORDER BY date DESC;
```

**Expected Results**:

✅ Report shows daily commission totals
✅ Commission count = orders_processed × average_upline_depth
✅ Total commissions ≤ (orders × ₱400)

**Pass Criteria**: Report data is accurate

---

### Test Case 18.2: Top Earners Report

**Objective**: Identify top MLM earners

**Steps**:

```sql
-- Top 10 MLM Earners (All Time)
SELECT
    u.id,
    u.username,
    u.name,
    w.mlm_balance as current_balance,
    COALESCE(SUM(t.amount), 0) as total_earned,
    COUNT(t.id) as commission_count
FROM users u
JOIN wallets w ON u.id = w.user_id
LEFT JOIN transactions t ON u.id = t.user_id AND t.type = 'mlm_commission'
GROUP BY u.id, u.username, u.name, w.mlm_balance
ORDER BY total_earned DESC
LIMIT 10;
```

**Expected Results**:

✅ Users with most referrals appear at top
✅ `total_earned` ≥ `current_balance` (some may have withdrawn)
✅ Level 1 sponsors earn most (₱200 per referral purchase)

**Pass Criteria**: Report identifies top performers

---

## Troubleshooting Guide - Phase 3

### Issue: Commissions Not Distributed

**Symptoms**: Order completes but no commission transactions created

**Diagnostic Steps**:

```bash
# 1. Check application logs
tail -100 storage/logs/laravel.log | grep "MLM Commission"

# 2. Check if package is MLM-enabled
SELECT id, name, is_mlm_package, max_mlm_levels FROM packages WHERE id = ?;

# 3. Check MLM settings exist
SELECT * FROM mlm_settings WHERE package_id = ? AND is_active = true;
```

**Possible Solutions**:

1. **Package not MLM**: Verify `packages.is_mlm_package = true`

2. **No MLM settings**: Create commission settings in `mlm_settings` table

3. **Database error**: Check logs for SQL errors, verify migrations

4. **Transaction rollback**: Check for exceptions during commission processing

---

### Issue: Email Notifications Not Sent

**Symptoms**: Commissions distributed but no emails received

**Diagnostic Steps**:

```sql
-- Check email verification status
SELECT
    u.username,
    u.email,
    u.email_verified_at,
    w.mlm_balance
FROM users u
JOIN wallets w ON u.id = w.user_id
WHERE w.mlm_balance > 0;
```

**Possible Solutions**:

1. **Email not verified**:
   ```sql
   UPDATE users SET email_verified_at = NOW() WHERE username = 'user1';
   ```

2. **Mail configuration**: Check `.env` for SMTP settings

3. **Check mail logs**: `tail -f storage/logs/laravel.log | grep "mail"`

4. **Notification queued**: Check if notification is queued but not sent (mail queue issue)

---

### Issue: Duplicate Commissions

**Symptoms**: Upline members receive multiple commissions for one order

**Diagnostic Steps**:

```sql
-- Check for duplicates
SELECT
    source_order_id,
    level,
    COUNT(*) as count
FROM transactions
WHERE type = 'mlm_commission'
GROUP BY source_order_id, level
HAVING COUNT(*) > 1;
```

**Possible Solutions**:

1. Add unique constraint or logic to prevent duplicates
2. Check if commission processing is being called multiple times
3. Review checkout controller for duplicate `dispatchSync()` calls
4. Add idempotency check in MLMCommissionService

---

### Issue: Wrong Commission Amounts

**Symptoms**: Commissions don't match expected values

**Diagnostic Steps**:

```sql
-- Verify MLM settings
SELECT * FROM mlm_settings WHERE package_id = 1 ORDER BY level;

-- Check actual commissions distributed
SELECT level, amount FROM transactions
WHERE type = 'mlm_commission'
  AND source_order_id = [ORDER_ID]
ORDER BY level;
```

**Possible Solutions**:

1. Verify MLM settings in admin panel
2. Check if settings are marked `is_active = true`
3. Clear config cache: `php artisan config:clear`

---

### Issue: Real-Time UI Not Updating

**Symptoms**: Balance updates but dashboard doesn't refresh

**Diagnostic Steps**:

1. Check browser console for JavaScript errors
2. Verify Laravel Echo is loaded
3. Check Pusher/WebSocket connection
4. Test broadcast configuration

**Possible Solutions**:

1. **Broadcasting not configured**: Set up Pusher or Soketi
2. **Echo not initialized**: Check `resources/js/bootstrap.js`
3. **Channel authorization**: Verify broadcast routes
4. **Fallback**: Page refresh still shows updated balance

---

## Phase 3 Testing Summary

### Critical Test Cases (Must Pass)

1. ✅ Test Case 8.2: Upline Tree Retrieval
2. ✅ Test Case 10.2: Job Execution Success
3. ✅ Test Case 11.1: Database Notification Creation
4. ✅ Test Case 11.2: Email to Verified Addresses
5. ✅ Test Case 11.3: No Email to Unverified
6. ✅ Test Case 13.1: Full 5-Level Commission Distribution
7. ✅ Test Case 14.1: Transaction Metadata Completeness
8. ✅ Test Case 17.1: End-to-End Purchase Flow

### High Priority Test Cases

1. ✅ Test Case 10.3: Job Retry Logic
2. ✅ Test Case 13.2: Partial Upline Chain
3. ✅ Test Case 13.3: Orphaned User
4. ✅ Test Case 15.2: Missing Wallet Handling
5. ✅ Test Case 16.1: Performance Benchmarking

### Optional Test Cases (If Broadcasting Enabled)

1. ⏳ Test Case 11.4: Broadcast Notifications
2. ⏳ Test Case 12.2: Live Balance Updates

---

## Next Steps After Phase 3

After completing Phase 3 testing:

1. ✅ **Document Issues**: Log all bugs, inconsistencies, and performance issues
2. ✅ **Fix Critical Bugs**: Address any blocking issues immediately
3. ✅ **Performance Tuning**: Optimize slow queries or processes
4. ✅ **Email Configuration**: Set up production email service
5. ✅ **Broadcasting Setup**: Configure Laravel Echo for real-time updates (optional)
6. 📋 **Prepare for Phase 4**: Withdrawal System with MLM Balance Restriction
7. 📋 **Review Phase 4 Requirements**: Study withdrawal flow and approval process

---

**Phase 3 Testing Complete!** 🎉

The MLM Commission Distribution Engine is now fully functional and ready for production use (after addressing any issues found during testing).

---

## Phase 4: Withdrawal System & Payment Preferences Testing

**Status**: Ready for Testing
**Estimated Testing Time**: 3-4 hours
**Prerequisites**: Phase 3 complete, database seeded with test data

---

## Test Suite 18: Payment Preferences System

### Test Case 18.1: Gcash Payment Preference

**Objective**: Verify Gcash number validation and storage

**Steps**:
1. Login as member user
2. Navigate to `/profile`
3. Scroll to "Payment Preferences" card
4. Select "Gcash" from dropdown
5. Enter valid Gcash number: `09171234567`
6. Click "Save Payment Preferences"

**Expected Results**:
- ✅ Success message displayed
- ✅ Gcash number saved to database
- ✅ Field remains populated on page reload
- ✅ `payment_preference` set to "Gcash" in users table

**Validation Tests**:
- ❌ Invalid format `9171234567` (missing 0) → Validation error
- ❌ Invalid format `0917123456` (10 digits) → Validation error
- ❌ Invalid format `09171234567X` (12 chars) → Validation error
- ❌ Invalid prefix `08171234567` → Validation error

---

### Test Case 18.2: Maya Payment Preference

**Objective**: Verify Maya number validation and storage

**Steps**:
1. From same profile page
2. Select "Maya" from dropdown
3. Enter valid Maya number: `09281234567`
4. Click "Save Payment Preferences"

**Expected Results**:
- ✅ Success message displayed
- ✅ Maya number saved to database
- ✅ `payment_preference` updated to "Maya"
- ✅ **Previous Gcash number still retained in database**
- ✅ Field remains populated on page reload

**Multi-Method Retention Test**:
1. Switch back to "Gcash" in dropdown
2. **Expected**: Previously saved Gcash number still displayed
3. Switch to "Maya" again
4. **Expected**: Maya number still displayed

---

### Test Case 18.3: Cash Pickup with Admin Office Address

**Objective**: Verify cash pickup location defaults to admin's delivery address

**Steps**:
1. Login as admin user
2. Navigate to `/profile`
3. Fill in Delivery Address:
   - Address: `123 Main St`
   - Address Line 2: `Suite 100`
   - City: `Manila`
   - State: `NCR`
   - Zip: `1000`
4. Save Delivery Address
5. Logout and login as member
6. Navigate to `/profile` → Payment Preferences
7. Select "Cash" from dropdown
8. **Leave pickup location blank**
9. Click "Save Payment Preferences"

**Expected Results**:
- ✅ Success message displayed
- ✅ `pickup_location` in database = `123 Main St, Suite 100, Manila, NCR, 1000`
- ✅ Matches admin's delivery address exactly
- ✅ Field displays admin's full address on page reload

**Fallback Test** (if admin has no address):
- **Expected**: `pickup_location` = `Main Office`

---

### Test Case 18.4: Others Payment Method

**Objective**: Verify custom payment method storage

**Steps**:
1. From member profile
2. Select "Others" from dropdown
3. Enter payment method name: `Bank Transfer`
4. Enter payment details:
   ```
   Bank: BDO
   Account Number: 1234567890
   Account Name: John Doe
   ```
5. Click "Save Payment Preferences"

**Expected Results**:
- ✅ Success message displayed
- ✅ `other_payment_method` = `Bank Transfer`
- ✅ `other_payment_details` = full text entered
- ✅ `payment_preference` = `Others`
- ✅ Both fields remain populated on page reload

**Validation Tests**:
- ❌ Empty payment method name → Validation error
- ❌ Empty payment details → Validation error
- ❌ Payment details > 1000 characters → Validation error

---

## Test Suite 19: Admin Office Address Integration

### Test Case 19.1: Office Address in Checkout

**Objective**: Verify admin's delivery address displays in checkout office pickup

**Steps**:
1. Ensure admin has delivery address configured (from Test Case 18.3)
2. Login as member
3. Add package to cart
4. Navigate to `/checkout`
5. Locate "Office Pickup" delivery method
6. Check displayed address

**Expected Results**:
- ✅ Address displays: `123 Main St, Suite 100, Manila, NCR, 1000`
- ✅ Matches admin's configured delivery address
- ✅ No hardcoded "Main Office" text

---

### Test Case 19.2: Office Address in Withdrawal Cash Pickup

**Objective**: Verify admin's office address in withdrawal form

**Steps**:
1. Navigate to `/wallet/withdraw`
2. Select "Cash" as payment method
3. Check pickup location placeholder and help text

**Expected Results**:
- ✅ Placeholder: "Leave blank to use admin's office address"
- ✅ Help text mentions automatic office address
- ✅ If left blank, admin's address used automatically

---

## Test Suite 20: Profile Management Enhancements

### Test Case 20.1: Readonly Username Field

**Objective**: Verify username cannot be changed after registration

**Steps**:
1. Login as member
2. Navigate to `/profile`
3. Locate username field
4. Attempt to click and edit username

**Expected Results**:
- ✅ Username field has `readonly` attribute
- ✅ Field appears grayed out (browser default)
- ✅ Cannot type or change value
- ✅ No `required` attribute (readonly fields don't need validation)
- ✅ Form submission doesn't validate username

**Database Test**:
1. Inspect users table: `SELECT username FROM users WHERE id = [member_id]`
2. Submit profile form (email update only)
3. Re-check users table
4. **Expected**: Username unchanged

---

### Test Case 20.2: Error Notification Display

**Objective**: Verify no duplicate error notifications on profile page

**Steps**:
1. Navigate to `/profile`
2. In Profile Information section, enter invalid email: `invalid-email`
3. Click "Save Changes"
4. Count error notifications displayed

**Expected Results**:
- ✅ Only ONE error notification displayed
- ✅ Error shows inline under email field (red text)
- ✅ NO global "Please correct the following issues" banner
- ✅ Clean, professional error display

---

### Test Case 20.3: Smart Form Routing

**Objective**: Verify profile controller routes to correct handler based on form

**Steps**:
1. **Test Profile Info Update**:
   - Update email in Profile Information card
   - Submit form
   - **Expected**: Calls `update()` method, validates only email

2. **Test Delivery Address Update**:
   - Update city in Delivery Address card
   - Submit form
   - **Expected**: Calls `updateDeliveryAddress()`, validates address fields

3. **Test Payment Preferences Update**:
   - Update Gcash number in Payment Preferences card
   - Submit form
   - **Expected**: Calls `updatePaymentPreferences()`, validates Gcash number

**Validation Conflict Test**:
- Submit payment preferences form
- **Expected**: No username validation errors
- **Expected**: No delivery address validation errors
- Form validation isolated to submitted form only

---

## Test Suite 21: Withdrawal Auto-Fill Integration

### Test Case 21.1: Gcash Auto-Fill in Withdrawal

**Objective**: Verify saved Gcash number auto-fills withdrawal form

**Steps**:
1. Ensure member has Gcash saved in payment preferences: `09171234567`
2. Navigate to `/wallet/withdraw`
3. Select "Gcash" as payment method
4. Check Gcash number field value

**Expected Results**:
- ✅ Field auto-filled with `09171234567`
- ✅ User can edit/override value if needed
- ✅ Saves time and reduces data entry errors

---

### Test Case 21.2: Maya Auto-Fill in Withdrawal

**Objective**: Verify saved Maya number auto-fills withdrawal form

**Steps**:
1. Ensure member has Maya saved: `09281234567`
2. Navigate to `/wallet/withdraw`
3. Select "Maya" as payment method
4. Check Maya number field value

**Expected Results**:
- ✅ Field auto-filled with `09281234567`
- ✅ Correct number displayed (not Gcash number)
- ✅ User can modify if needed

---

### Test Case 21.3: Cash Pickup Auto-Fill in Withdrawal

**Objective**: Verify admin's office address auto-fills cash pickup location

**Steps**:
1. Navigate to `/wallet/withdraw`
2. Select "Cash" as payment method
3. Check pickup location field
4. Leave field blank or use existing value
5. Submit withdrawal request
6. Check database: `withdrawal_requests` table

**Expected Results**:
- ✅ If left blank: Admin's full delivery address used
- ✅ If filled: User's custom location used
- ✅ Placeholder text indicates auto-fill behavior
- ✅ `account_details` JSON contains pickup location

---

## Test Suite 22: Transfer Fee Deduction System

### Test Case 22.1: Transfer Fee Calculation

**Objective**: Verify transfer fee is correctly calculated and deducted

**Steps**:
1. Navigate to admin panel → System Settings
2. Check/set transfer fee percentage (e.g., 2%)
3. Login as member with MLM balance ₱1,000
4. Navigate to `/wallet/withdraw`
5. Enter withdrawal amount: ₱500
6. Select any payment method
7. Fill in required fields
8. Submit withdrawal request

**Expected Results**:
- ✅ Transfer fee calculated: ₱500 × 2% = ₱10
- ✅ Net amount: ₱500 - ₱10 = ₱490
- ✅ Display shows: "Fee: ₱10, Net: ₱490"
- ✅ Success message shows net amount
- ✅ Database `withdrawal_requests` table:
  - `amount` = 500.00
  - `fee` = 10.00
  - `net_amount` = 490.00

---

### Test Case 22.2: Transfer Fee with Different Percentages

**Objective**: Verify fee calculation adjusts to configured percentage

**Test Scenarios**:

| Withdrawal Amount | Fee % | Expected Fee | Expected Net |
|-------------------|-------|--------------|--------------|
| ₱1,000 | 2% | ₱20 | ₱980 |
| ₱1,000 | 5% | ₱50 | ₱950 |
| ₱1,000 | 0% | ₱0 | ₱1,000 |
| ₱500 | 3% | ₱15 | ₱485 |

**Expected Results for Each**:
- ✅ Fee calculated correctly
- ✅ Net amount = amount - fee
- ✅ Database values match expected

---

## Test Suite 23: Dual Balance Withdrawal Support

### Test Case 23.1: MLM Balance Withdrawal

**Objective**: Verify withdrawal from MLM balance

**Steps**:
1. Check member's wallet: `mlm_balance` = ₱500
2. Navigate to `/wallet/withdraw`
3. Select "MLM Balance" (or ensure withdrawal from MLM)
4. Enter amount: ₱200
5. Complete withdrawal request
6. Check database after admin approval

**Expected Results**:
- ✅ `mlm_balance` decremented by ₱200
- ✅ Transaction record created with `type` = 'withdrawal'
- ✅ `source_type` = 'mlm' or similar identifier
- ✅ Purchase balance unchanged

---

### Test Case 23.2: Purchase Balance Withdrawal

**Objective**: Verify withdrawal from purchase balance

**Steps**:
1. Check member's wallet: `purchase_balance` = ₱300
2. Navigate to `/wallet/withdraw`
3. Select "Purchase Balance" withdrawal option
4. Enter amount: ₱100
5. Complete withdrawal request
6. Check database after admin approval

**Expected Results**:
- ✅ `purchase_balance` decremented by ₱100
- ✅ Transaction record created
- ✅ `source_type` indicates purchase balance
- ✅ MLM balance unchanged

---

## Phase 4 Testing Summary

### Critical Test Cases (Must Pass)

1. ✅ Test Case 18.1: Gcash Payment Preference
2. ✅ Test Case 18.2: Maya Payment Preference (with retention)
3. ✅ Test Case 18.3: Cash Pickup with Admin Address
4. ✅ Test Case 19.1: Office Address in Checkout
5. ✅ Test Case 20.1: Readonly Username Field
6. ✅ Test Case 20.2: No Duplicate Error Notifications
7. ✅ Test Case 20.3: Smart Form Routing
8. ✅ Test Case 21.1-21.3: Withdrawal Auto-Fill
9. ✅ Test Case 22.1: Transfer Fee Calculation
10. ✅ Test Case 23.1-23.2: Dual Balance Withdrawal

### High Priority Test Cases

1. ✅ Test Case 18.4: Others Payment Method
2. ✅ Test Case 19.2: Office Address in Withdrawal
3. ✅ Test Case 22.2: Transfer Fee Percentages

### Optional Test Cases

1. ⏳ Performance testing with multiple concurrent withdrawals
2. ⏳ Edge case: Admin changes office address after member saves cash preference
3. ⏳ Edge case: Very long custom payment details (approaching 1000 char limit)

---

## Next Steps After Phase 4

After completing Phase 4 testing:

1. ✅ **Document Issues**: Log all bugs and inconsistencies
2. ✅ **Fix Critical Bugs**: Address blocking issues immediately
3. ✅ **User Acceptance Testing**: Have real users test payment preferences and withdrawals
4. ✅ **Security Review**: Ensure payment data is properly secured
5. ✅ **Admin Training**: Train admins on withdrawal approval workflow
6. 📋 **Prepare for Phase 5**: Profitability Analysis & Sustainability Dashboard
7. 📋 **Review Phase 5 Requirements**: Study analytics and reporting requirements

---

**Phase 4 Testing Complete!** 🎉

The Withdrawal System with Payment Preferences is now fully functional and ready for production use.
