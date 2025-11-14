# Complete Order Return Process - Testing Guide

**Document Version:** 1.0
**Last Updated:** October 2, 2025
**Status:** Ready for Testing

---

## Table of Contents

1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Test Scenario 1: Successful Return with Refund](#test-scenario-1-successful-return-with-refund)
4. [Test Scenario 2: Rejected Return Request](#test-scenario-2-rejected-return-request)
5. [Test Scenario 3: Return Window Expired](#test-scenario-3-return-window-expired)
6. [Verification Checklist](#verification-checklist)
7. [Common Issues & Troubleshooting](#common-issues--troubleshooting)

---

## Overview

This guide provides step-by-step instructions for testing the complete order return process, from initial order placement through delivery, return request submission, admin review, and final refund processing.

### Process Flow
```
Order Placement → Payment → Order Fulfillment → Delivery (with timestamp) →
Return Request (within 7 days) → Admin Review → Approval/Rejection →
Return Shipment → Refund Processing
```

---

## Prerequisites

### Required Test Accounts

**Customer Account (Member Role):**
- Email: `customer@test.com`
- Password: `password`
- **Required:** Wallet balance of at least $100 for testing

**Admin Account (Admin Role):**
- Email: `admin@test.com`
- Password: `password`

### System Requirements

1. **E-Wallet Payment Enabled**
2. **Application Settings Configured:**
   - Tax rate set (or 0%)
   - Navigate to: `http://coreui_laravel_deploy.test/admin/application-settings`

3. **Test Packages Available:**
   - Navigate to: `http://coreui_laravel_deploy.test/admin/packages`
   - Ensure at least one package is active and in stock

### Before You Begin

1. **Check Customer Wallet Balance:**
   ```
   Login as customer@test.com
   Navigate to Dashboard
   Verify wallet balance ≥ $100
   ```

2. **Check Available Packages:**
   ```
   Navigate to: http://coreui_laravel_deploy.test/packages
   Verify packages are visible and have stock
   ```

3. **Check System Date/Time:**
   - Ensure your system date is correct (affects return window calculation)

---

## Test Scenario 1: Successful Return with Refund

### Phase 1: Order Placement & Payment

#### Step 1.1: Create Order as Customer

1. **Login as Customer:**
   - Navigate to: `http://coreui_laravel_deploy.test/login`
   - Email: `customer@test.com`
   - Password: `password`

2. **Browse Packages:**
   - Navigate to: `http://coreui_laravel_deploy.test/packages`
   - Select any package (e.g., "Professional Package")
   - Click **"Add to Cart"**

3. **Verify Cart:**
   - Button changes to **"In Cart"** ✓
   - Cart icon in header shows count (1)
   - Click **"View Cart"** or navigate to: `http://coreui_laravel_deploy.test/cart`

4. **Proceed to Checkout:**
   - Click **"Proceed to Checkout"**
   - Review order summary
   - Verify delivery address is pre-filled from profile
   - **Important:** Select delivery method: **"Home Delivery"** (recommended for testing)

5. **Complete Checkout:**
   - Accept Terms & Conditions (check the box)
   - Add customer notes (optional): "Test order for return process"
   - Click **"Place Order"**

**Expected Result:**
- Redirected to: `http://coreui_laravel_deploy.test/checkout/confirmation/{order-id}`
- Order number displayed (e.g., ORD-20251002-ABC123)
- Order status: **"Pending Payment"**

#### Step 1.2: Process Payment

1. **On Confirmation Page:**
   - Note your **Order Number** (you'll need this later)
   - Click **"Pay with Wallet"** button

2. **Confirm Payment:**
   - Wallet balance should be sufficient
   - Click **"Confirm Payment"** in the modal

**Expected Result:**
- ✅ Payment processed successfully
- ✅ Order status changes to **"Paid"**
- ✅ Wallet balance decreased by order amount
- ✅ **"Cancel Order" button disappears** (paid orders cannot be cancelled)
- ✅ Transaction created in wallet history

**Screenshot Checkpoint:** Take a screenshot of the confirmation page showing "Paid" status.

---

### Phase 2: Admin Order Fulfillment

#### Step 2.1: Login as Admin and View Order

1. **Logout Customer:**
   - Click profile → Logout

2. **Login as Admin:**
   - Email: `admin@test.com`
   - Password: `password`

3. **Navigate to Orders:**
   - Navigate to: `http://coreui_laravel_deploy.test/admin/orders`
   - Find your test order (use order number or filter by "Paid" status)
   - Click **"View"** to open order details

**Expected Result:**
- Order details page loads
- Current status: **"Paid"**
- Customer information visible
- Order items listed with package details

#### Step 2.2: Progress Order Through Statuses

Follow these steps to move the order through fulfillment stages:

**2.2.1: Set to "Processing"**
- Click **"Update Status"** button (or use quick action button)
- Select: **"Processing Order"**
- Add notes: "Order acknowledged, preparing items"
- Check **"Notify customer"** (if desired)
- Click **"Update Status"** → Confirm

**2.2.2: Set to "Confirmed"**
- Update Status → **"Order Confirmed"**
- Notes: "Items verified and ready"
- Click **"Update Status"** → Confirm

**2.2.3: Set to "Packing"**
- Update Status → **"Packing Items"**
- Notes: "Items being packed for shipment"
- Click **"Update Status"** → Confirm

**2.2.4: Set to "Ready to Ship"**
- Update Status → **"Ready for Shipment"**
- Notes: "Package ready for courier pickup"
- Click **"Update Status"** → Confirm

**2.2.5: Set to "Shipped"**
- Update Status → **"Shipped"**
- Notes: "Package handed to courier"
- Add tracking info if the modal appears:
  - Tracking Number: `TEST-TRACK-123456`
  - Courier Name: `Test Courier Service`
- Click **"Update Status"** → Confirm

**2.2.6: Set to "Out for Delivery"**
- Update Status → **"Out for Delivery"**
- Notes: "Package on the way to customer"
- Click **"Update Status"** → Confirm

---

#### Step 2.3: Mark as Delivered (CRITICAL STEP)

**🚨 This is the most important step for testing returns!**

1. **Update Status to "Delivered":**
   - Click **"Update Status"** button
   - Select: **"Delivered"**
   - **Notice:** A new field appears: **"Delivery Date & Time"**

2. **Set Delivery Timestamp:**
   - The field shows current date/time by default
   - **For testing:** Keep the current date/time
   - **Alternative:** Manually adjust to specific date/time if needed

   ```
   Example: 2025-10-02T14:30
   ```

3. **Complete Status Update:**
   - Add notes: "Package delivered to customer"
   - Check **"Notify customer"**
   - Click **"Update Status"** → Confirm

**Expected Result:**
- ✅ Order status: **"Delivered"**
- ✅ `delivered_at` timestamp recorded in database
- ✅ Status history shows delivery timestamp
- ✅ **7-day return window starts NOW**

**Screenshot Checkpoint:** Take screenshot showing order status as "Delivered" with timestamp.

---

### Phase 3: Customer Return Request

#### Step 3.1: View Delivered Order as Customer

1. **Logout Admin and Login as Customer:**
   - Logout admin account
   - Login as: `customer@test.com`

2. **Navigate to Order History:**
   - Go to: `http://coreui_laravel_deploy.test/orders`
   - Find your test order (should show "Delivered" status)
   - Click on the order to view details

**Expected Result:**
- Order details page loads
- Status shows: **"Delivered"**
- Delivery information displayed
- **"Return Request" section visible** (new card below order details)

#### Step 3.2: Submit Return Request

1. **Locate Return Request Section:**
   - Scroll down to **"Return Request"** card
   - See alert: "You have 7 days remaining to request a return"
   - Click **"Request Return"** button

2. **Fill Return Request Form (in modal):**

   **Reason for Return:**
   - Select: **"Quality Issue"**

   **Detailed Description:**
   ```
   The package contents do not meet the quality standards as advertised.
   The materials appear worn and used, and the documentation included is
   incomplete. Several pages are missing from the manual, and the product
   shows signs of prior use despite being advertised as new.
   ```
   *(Note: Description must be at least 20 characters)*

   **Proof Images (Optional but Recommended):**
   - Click **"Choose Files"**
   - Upload 1-3 images showing the issue
   - Maximum 2MB per image
   - Accepted formats: JPG, PNG, GIF

3. **Review Information:**
   - Verify reason is selected
   - Verify description is detailed (≥ 20 characters)
   - Verify images uploaded (if applicable)

4. **Submit Request:**
   - Click **"Submit Return Request"** button

**Expected Result:**
- ✅ Modal closes
- ✅ Success message: "Return request submitted successfully. We'll review it within 24 hours."
- ✅ Page reloads
- ✅ Return Request card now shows:
  - Return Status: **"Pending Review"** (yellow badge)
  - Reason: **"Quality Issue"**
  - Description displayed
  - Images displayed (if uploaded)

**Screenshot Checkpoint:** Take screenshot of return request status showing "Pending Review".

---

### Phase 4: Admin Review & Decision

#### Step 4.1: View Return Requests

1. **Logout Customer and Login as Admin**

2. **Navigate to Return Requests:**
   - Go to: `http://coreui_laravel_deploy.test/admin/returns`
   - See list of all return requests
   - Find your test return request (use order number to identify)

**Expected Result:**
- Return requests list page loads
- Your test request visible with status **"Pending Review"**
- Order number, customer name, and reason displayed

#### Step 4.2: Review Return Details

1. **Click on Return Request:**
   - Click **"Review"** or the return request row
   - Return details page loads

2. **Review Information:**
   - **Customer:** customer@test.com
   - **Order Number:** ORD-20251002-XXXXXX
   - **Reason:** Quality Issue
   - **Description:** (full description displayed)
   - **Images:** View uploaded images (if any)
   - **Order Value:** $XX.XX
   - **Payment Method:** E-Wallet

3. **Evaluate Request:**
   - ✅ Valid reason provided
   - ✅ Return within 7-day window
   - ✅ Detailed description
   - ✅ Proof images show legitimate issue (if uploaded)

---

#### Step 4.3: Approve Return Request

**For Scenario 1, we will APPROVE the return.**

1. **Click "Approve Return" Button**

2. **Fill Admin Response (in modal):**
   ```
   We've reviewed your return request and approved it based on the quality
   issues you've reported. Please ship the item back to our office using a
   trackable shipping method. Once we receive and verify the returned item,
   we'll process your full refund to your e-wallet.

   Return Address:
   [Your Company Name]
   [Address Line 1]
   [City, State ZIP]

   Please include your order number (ORD-20251002-XXXXXX) in the package.
   ```

3. **Confirm Approval:**
   - Review your response
   - Click **"Confirm Approval"**

**Expected Result:**
- ✅ Success message: "Return request approved successfully. Customer has been notified."
- ✅ Return status changes to: **"Approved"** (blue badge)
- ✅ Order status changes to: **"Return Approved"**
- ✅ `approved_at` timestamp recorded
- ✅ Admin response visible in return details
- ✅ Customer receives email notification (if email verified)

**Screenshot Checkpoint:** Take screenshot showing approved return request.

---

### Phase 5: Customer Ships Item Back

#### Step 5.1: View Approval (Customer Side)

1. **Logout Admin and Login as Customer**

2. **Navigate to Order:**
   - Go to: `http://coreui_laravel_deploy.test/orders`
   - Click on your test order

3. **View Return Status:**
   - Scroll to **"Return Request"** section
   - See status: **"Approved"** (blue badge)
   - Read admin response with shipping instructions
   - See alert: "Return Approved! Please ship the item back..."

**Expected Result:**
- Return status shows **"Approved"**
- Admin response displayed
- Shipping instructions visible
- Return tracking form displayed

#### Step 5.2: Submit Return Tracking Number

**Simulate customer shipping the item back:**

1. **Locate Tracking Form:**
   - In the Return Request section
   - Field: **"Return Tracking Number"**

2. **Enter Tracking Number:**
   - Input: `RETURN-TEST-789456`
   - Click **"Submit Tracking Number"**

**Expected Result:**
- ✅ Success message: "Return tracking number updated successfully."
- ✅ Order status changes to: **"Return In Transit"**
- ✅ Tracking number saved and displayed: `RETURN-TEST-789456`
- ✅ Admin notified of return shipment
- ✅ Tracking form replaced with tracking number display

**Screenshot Checkpoint:** Take screenshot showing tracking number submitted.

---

### Phase 6: Admin Receives Return & Processes Refund

#### Step 6.1: View Return Shipment (Admin Side)

1. **Logout Customer and Login as Admin**

2. **Navigate to Returns:**
   - Go to: `http://coreui_laravel_deploy.test/admin/returns`
   - Find your test return request
   - Notice status or tracking indicator

3. **View Return Details:**
   - Click to view return details
   - See tracking number: `RETURN-TEST-789456`
   - Order status: **"Return In Transit"**

**Expected Result:**
- Return request shows tracking number
- Order status indicates return is in transit

#### Step 6.2: Confirm Receipt & Process Refund

**Simulate receiving the physical item:**

1. **Click "Confirm Receipt" Button**
   - Or similar action button for processing the return

2. **Review Refund Details:**
   - Verify refund amount matches original order total
   - Order Total: $XX.XX
   - Refund Amount: $XX.XX

3. **Click "Process Refund"**
   - Confirm the action

**Expected Result:**
- ✅ Success message: "Return received and refund processed successfully."
- ✅ Order status changes to: **"Refunded"**
- ✅ Return request status: **"Completed"** (green badge)
- ✅ Refund transaction created in database
- ✅ Customer wallet credited with refund amount
- ✅ `refunded_at` timestamp recorded
- ✅ Customer notified (if email verified)

**Screenshot Checkpoint:** Take screenshot showing completed refund.

---

### Phase 7: Final Verification (Customer Side)

#### Step 7.1: Verify Refund Received

1. **Logout Admin and Login as Customer**

2. **Check Wallet Balance:**
   - Navigate to: `http://coreui_laravel_deploy.test/wallet/transactions`
   - Or view Dashboard wallet widget

**Expected Result:**
- ✅ Wallet balance increased by order amount
- ✅ Wallet balance = Original Balance (before order)

#### Step 7.2: View Refund Transaction

1. **Navigate to Transactions:**
   - Go to: `http://coreui_laravel_deploy.test/wallet/transactions`

2. **Find Refund Transaction:**
   - Should be at or near the top
   - **Type:** Refund
   - **Amount:** +$XX.XX (green/positive)
   - **Description:** "Refund for Order #ORD-20251002-XXXXXX"
   - **Status:** Completed

**Expected Result:**
- ✅ Refund transaction visible
- ✅ Amount matches original order total
- ✅ Transaction reference matches order
- ✅ Status: Completed

#### Step 7.3: Verify Order Status

1. **Navigate to Order History:**
   - Go to: `http://coreui_laravel_deploy.test/orders`
   - Click on your test order

2. **Review Complete Timeline:**
   - Order Status: **"Refunded"**
   - Payment Status: **"Refunded"**
   - Return Request section shows:
     - Status: **"Completed"** (green badge)
     - All details preserved
     - Refund date shown

**Expected Result:**
- ✅ Order marked as refunded
- ✅ Complete status history visible
- ✅ Return request shows completed
- ✅ All timestamps recorded correctly

**Screenshot Checkpoint:** Take final screenshot showing refunded order with complete history.

---

## Test Scenario 2: Rejected Return Request

### Overview
Test the rejection workflow to ensure customers are properly notified when returns are denied.

### Setup: Create Second Test Order

1. **Follow Phase 1 & Phase 2** from Scenario 1:
   - Create new order as customer
   - Pay with e-wallet
   - Admin progresses order to "Delivered" with timestamp

### Phase 3: Customer Submits Return Request

1. **Login as Customer**
2. **Navigate to delivered order**
3. **Submit return request:**
   - Reason: **"No Longer Needed"**
   - Description: "I changed my mind about this purchase."
   - No images uploaded

### Phase 4: Admin Rejects Return

1. **Login as Admin**
2. **Navigate to:** `http://coreui_laravel_deploy.test/admin/returns`
3. **View return request details**
4. **Click "Reject Return" Button**

5. **Fill Rejection Reason:**
   ```
   Unfortunately, we cannot approve your return request. According to our
   return policy, "No Longer Needed" or "Changed Mind" reasons are only
   accepted for unpaid orders. Since your order has been paid and delivered,
   returns are only accepted for quality issues, damaged products, or
   incorrect items.

   If you believe there is a quality issue with the product, please submit
   a new return request with detailed description and proof images.
   ```

6. **Confirm Rejection**

**Expected Result:**
- ✅ Return request status: **"Rejected"** (red badge)
- ✅ Order status reverts to: **"Delivered"**
- ✅ `rejected_at` timestamp recorded
- ✅ Customer notified with rejection reason
- ✅ No refund processed

### Phase 5: Customer Views Rejection

1. **Logout Admin, Login as Customer**
2. **Navigate to order details**
3. **View Return Request section**

**Expected Result:**
- ✅ Return status: **"Rejected"**
- ✅ Rejection reason displayed
- ✅ No refund issued
- ✅ Wallet balance unchanged

---

## Test Scenario 3: Return Window Expired

### Overview
Verify that customers cannot request returns after the 7-day window.

### Setup: Create Order with Old Delivery Date

**Option A: Use Database** (Recommended)
```sql
-- Find your test order
SELECT id, order_number, delivered_at FROM orders
WHERE order_number = 'ORD-20251002-XXXXXX';

-- Set delivered_at to 8 days ago
UPDATE orders
SET delivered_at = DATE_SUB(NOW(), INTERVAL 8 DAY)
WHERE id = [ORDER_ID];
```

**Option B: Use Tinker**
```bash
php artisan tinker
```
```php
$order = Order::where('order_number', 'ORD-20251002-XXXXXX')->first();
$order->delivered_at = now()->subDays(8);
$order->save();
```

### Test Return Request

1. **Login as Customer**
2. **Navigate to order details**

**Expected Result:**
- ✅ **"Return Request" section NOT visible** or disabled
- ✅ Message displayed: "Return window expired (7 days from delivery)"
- ✅ No "Request Return" button available
- ✅ Attempting direct access to return form should show error

---

## Verification Checklist

### ✅ Phase 1: Order & Payment
- [ ] Order created successfully
- [ ] E-wallet payment processed
- [ ] Wallet balance decreased correctly
- [ ] Order marked as "Paid"
- [ ] Cancel button removed for paid orders
- [ ] Payment transaction recorded

### ✅ Phase 2: Order Fulfillment
- [ ] Order progresses through all statuses
- [ ] Each status change recorded in history
- [ ] Status timeline displays correctly
- [ ] Delivery timestamp field appears when status = "Delivered"
- [ ] `delivered_at` timestamp saved to database
- [ ] 7-day return window starts from delivery timestamp

### ✅ Phase 3: Return Request (Customer)
- [ ] Return request option visible within 7 days
- [ ] Form validation works (min 20 characters for description)
- [ ] Images upload successfully (if provided)
- [ ] Return request created in database
- [ ] Order status changes to "Return Requested"
- [ ] Admin notified of new return request

### ✅ Phase 4: Admin Review
- [ ] Return requests list page loads
- [ ] Return details display correctly
- [ ] Approval workflow functions
- [ ] Rejection workflow functions
- [ ] Admin response saved
- [ ] Customer notified of decision
- [ ] Order status updates based on decision

### ✅ Phase 5: Return Shipment
- [ ] Customer can add tracking number
- [ ] Order status updates to "Return In Transit"
- [ ] Tracking number displayed correctly
- [ ] Admin notified of shipment

### ✅ Phase 6: Refund Processing
- [ ] Admin can confirm receipt
- [ ] Refund automatically processed
- [ ] Wallet credited correctly
- [ ] Refund transaction created
- [ ] Order status: "Refunded"
- [ ] Return status: "Completed"
- [ ] All timestamps recorded

### ✅ Phase 7: Final Verification
- [ ] Wallet balance restored to pre-purchase amount
- [ ] Order shows "Refunded" status
- [ ] Complete history visible
- [ ] All data consistent across tables

### ✅ Edge Cases
- [ ] Return window expiration works
- [ ] Multiple return requests blocked
- [ ] Cannot return unpaid orders
- [ ] Rejection workflow complete
- [ ] Return on office pickup orders (if applicable)

---

## Common Issues & Troubleshooting

### Issue 1: Return Request Button Not Appearing

**Possible Causes:**
- Order not delivered yet
- Delivery timestamp (`delivered_at`) not set
- Return window expired (>7 days)
- Return request already exists

**Solution:**
```sql
-- Check delivery status
SELECT id, order_number, status, delivered_at FROM orders WHERE id = [ORDER_ID];

-- Check for existing return request
SELECT * FROM return_requests WHERE order_id = [ORDER_ID];
```

**Fix:**
- Ensure order status is "Delivered"
- Ensure `delivered_at` is set and within 7 days
- Check for duplicate return requests

---

### Issue 2: Refund Not Processing

**Possible Causes:**
- Payment method is not e-wallet
- Original payment transaction not found
- Wallet not found for user

**Solution:**
```sql
-- Check payment method
SELECT id, order_number, payment_status, metadata FROM orders WHERE id = [ORDER_ID];

-- Check for payment transaction
SELECT * FROM transactions
WHERE user_id = [USER_ID]
AND type = 'payment'
AND JSON_EXTRACT(metadata, '$.order_id') = [ORDER_ID];

-- Check wallet exists
SELECT * FROM wallets WHERE user_id = [USER_ID];
```

**Fix:**
- Verify payment method is e-wallet
- Ensure original payment transaction exists
- Create wallet if missing

---

### Issue 3: Admin Not Receiving Notifications

**Possible Causes:**
- Admin email not verified
- Email notification settings disabled
- Mail queue not processing

**Solution:**
1. Check admin email verification status
2. Verify system email settings
3. Check logs for email errors: `storage/logs/laravel.log`

---

### Issue 4: Images Not Uploading

**Possible Causes:**
- File size too large (>2MB)
- Invalid file format
- Storage directory permissions

**Solution:**
1. Check file size ≤ 2MB
2. Use JPG, PNG, or GIF format
3. Verify storage directory exists and is writable:
   ```bash
   php artisan storage:link
   chmod -R 775 storage/app/public
   ```

---

### Issue 5: Database Errors

**Possible Causes:**
- Migrations not run
- Foreign key constraints
- Column not found

**Solution:**
```bash
# Check migration status
php artisan migrate:status

# Run migrations
php artisan migrate

# Fresh start (CAUTION: Deletes all data)
php artisan migrate:fresh --seed
```

---

## Database Verification Queries

Use these queries to verify data integrity throughout the testing process:

### Check Order Status History
```sql
SELECT
    osh.id,
    osh.order_id,
    osh.status,
    osh.notes,
    osh.changed_by,
    osh.created_at
FROM order_status_histories osh
WHERE osh.order_id = [ORDER_ID]
ORDER BY osh.created_at ASC;
```

### Check Return Request
```sql
SELECT
    rr.id,
    rr.order_id,
    o.order_number,
    rr.reason,
    rr.status,
    rr.approved_at,
    rr.rejected_at,
    rr.refunded_at,
    rr.return_tracking_number
FROM return_requests rr
JOIN orders o ON rr.order_id = o.id
WHERE rr.order_id = [ORDER_ID];
```

### Check Wallet Transactions
```sql
SELECT
    t.id,
    t.type,
    t.amount,
    t.status,
    t.description,
    t.created_at,
    JSON_EXTRACT(t.metadata, '$.order_id') as order_id
FROM transactions t
WHERE t.user_id = [USER_ID]
AND t.type IN ('payment', 'refund')
ORDER BY t.created_at DESC;
```

### Verify Wallet Balance
```sql
SELECT
    w.id,
    w.user_id,
    u.email,
    w.balance,
    w.updated_at
FROM wallets w
JOIN users u ON w.user_id = u.id
WHERE w.user_id = [USER_ID];
```

### Check Complete Order Timeline
```sql
SELECT
    o.id,
    o.order_number,
    o.status,
    o.payment_status,
    o.delivered_at,
    o.created_at,
    rr.status as return_status,
    rr.approved_at,
    rr.refunded_at
FROM orders o
LEFT JOIN return_requests rr ON o.id = rr.order_id
WHERE o.id = [ORDER_ID];
```

---

## Test Data Recording Template

Use this template to record your test results:

```
===============================================
ORDER RETURN PROCESS TEST - SCENARIO 1
===============================================

Test Date: _______________
Tester Name: ______________

PHASE 1: ORDER & PAYMENT
✓ Order Number: ____________________
✓ Order Total: $_________
✓ Initial Wallet Balance: $_________
✓ Wallet Balance After Payment: $_________
✓ Payment Transaction ID: ___________

PHASE 2: FULFILLMENT
✓ Delivery Date/Time: ________________
✓ delivered_at Timestamp: ____________

PHASE 3: RETURN REQUEST
✓ Return Request ID: _______
✓ Submission Time: _________
✓ Days Remaining: __________

PHASE 4: ADMIN REVIEW
✓ Review Date: ____________
✓ Decision: [APPROVED / REJECTED]
✓ Admin Response: __________________

PHASE 5: RETURN SHIPMENT
✓ Tracking Number: _________________
✓ Submission Time: _________________

PHASE 6: REFUND
✓ Refund Date: ___________
✓ Refund Amount: $_________
✓ Refund Transaction ID: ___________

PHASE 7: VERIFICATION
✓ Final Wallet Balance: $_________
✓ Balance Match: [YES / NO]
✓ Order Status: ___________
✓ Return Status: ___________

ISSUES ENCOUNTERED:
_________________________________
_________________________________
_________________________________

NOTES:
_________________________________
_________________________________
_________________________________
```

---

## Expected Timeline

- **Order Placement → Payment:** 2-3 minutes
- **Admin Fulfillment → Delivery:** 5-10 minutes
- **Return Request Submission:** 2 minutes
- **Admin Review & Decision:** 2-3 minutes
- **Customer Ships Back (add tracking):** 1 minute
- **Admin Receipt & Refund:** 1-2 minutes
- **Final Verification:** 1-2 minutes

**Total Test Time per Scenario:** ~15-25 minutes

---

## Post-Testing Cleanup

After completing all tests, clean up test data:

### Option 1: Database Cleanup (Specific Order)
```sql
-- Replace [ORDER_ID] with your test order ID

-- Delete return request
DELETE FROM return_requests WHERE order_id = [ORDER_ID];

-- Delete order status history
DELETE FROM order_status_histories WHERE order_id = [ORDER_ID];

-- Delete order items
DELETE FROM order_items WHERE order_id = [ORDER_ID];

-- Delete order
DELETE FROM orders WHERE id = [ORDER_ID];

-- Optionally reset customer wallet
UPDATE wallets SET balance = [ORIGINAL_BALANCE] WHERE user_id = [CUSTOMER_USER_ID];
```

### Option 2: Full Database Reset
```bash
php artisan migrate:fresh --seed
```

**⚠️ Warning:** This deletes ALL data and resets the database.

---

## Additional Resources

- **Implementation Guide:** `RETURN_PROCESS_IMPLEMENTATION.md`
- **Testing Workflow:** `RETURN_PROCESS_TESTING_WORKFLOW.md`
- **Order Return Policy:** `ORDER_RETURN.md`
- **Application Settings:** `/admin/application-settings`
- **System Logs:** `storage/logs/laravel.log`

---

## Support & Feedback

If you encounter any issues during testing:

1. Check the **Common Issues & Troubleshooting** section above
2. Review database verification queries
3. Check system logs: `storage/logs/laravel.log`
4. Document the issue with:
   - Step where error occurred
   - Error message (if any)
   - Screenshot
   - Database state (run verification queries)

---

**Document Version:** 1.0
**Last Updated:** October 2, 2025
**Status:** Ready for Testing
