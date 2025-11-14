# Order Return Process - Testing Workflow

## Overview
This document provides a step-by-step workflow for testing the complete order return process, from initial purchase through final refund.

---

## Prerequisites

### Test Accounts Required
1. **Customer Account** (Member role)
   - Email: customer@test.com
   - Password: password
   - Wallet Balance: $500+ (for testing)

2. **Admin Account** (Admin role)
   - Email: admin@test.com
   - Password: password

### System Configuration
- E-wallet payment must be enabled
- Return window set to 7 days (configurable in Order model)
- System settings properly configured

---

## Complete Testing Workflow

### Phase 1: Order Creation & Payment (Customer Side)

#### Step 1.1: Browse and Add to Cart
1. Login as **customer@test.com**
2. Navigate to `http://coreui_laravel_deploy.test/packages`
3. Select a package (e.g., "Professional Package")
4. Click "Add to Cart"
5. Verify cart shows the item
6. Click "View Cart"

**Expected Result:**
- Package appears in cart
- Cart count updates in header
- Cart totals display correctly

---

#### Step 1.2: Proceed to Checkout
1. From cart page, click "Proceed to Checkout"
2. Review order summary
3. Verify delivery address is filled
4. Select "E-Wallet" as payment method
5. Check wallet balance is sufficient
6. Accept Terms & Conditions
7. Add customer notes (optional): "Test order for return process"
8. Click "Place Order"

**Expected Result:**
- Order created successfully
- Redirected to order confirmation page
- Order number generated (e.g., ORD-2025-10-02-0001)

---

#### Step 1.3: Complete Payment
1. On confirmation page, review order details
2. Click "Pay with Wallet" button
3. Confirm payment in modal
4. Wait for payment processing

**Expected Result:**
- Payment deducted from wallet
- Order status changes from "pending" to "paid"
- Transaction created in wallet history
- Points awarded to customer
- **Cancellation button disappears** (paid orders cannot be cancelled)
- Admin receives notification of new paid order

**Verification Points:**
- ✅ Wallet balance decreased by order amount
- ✅ Order status: "paid"
- ✅ Payment status: "paid"
- ✅ No "Cancel Order" button visible
- ✅ Transaction reference ID displayed

---

### Phase 2: Order Fulfillment (Admin Side)

#### Step 2.1: View New Order
1. Logout customer, login as **admin@test.com**
2. Navigate to `http://coreui_laravel_deploy.test/admin/orders`
3. Find the newly paid order
4. Click "View" to see order details

**Expected Result:**
- Order appears in "Paid" status
- Customer details visible
- Payment information shown
- Order items listed

---

#### Step 2.2: Process Order Through Statuses
Progress the order through fulfillment stages:

1. Change status to **"Processing"**
   - Click "Update Status" dropdown
   - Select "Processing"
   - Add notes: "Order acknowledged, preparing items"
   - Click "Update Status"

2. Change status to **"Confirmed"**
   - Select "Confirmed"
   - Add notes: "Items verified and ready"

3. Change status to **"Packing"**
   - Select "Packing"
   - Add notes: "Items being packed"

4. Change status to **"Ready to Ship"** or **"Ready for Pickup"** (depending on delivery method)

5. Change status to **"Shipped"** (for home delivery)
   - Add tracking number: "TEST123456789"
   - Add courier name: "Test Courier"
   - Add notes: "Package handed to courier"

6. Change status to **"In Transit"**
   - Add notes: "Package on the way"

7. Change status to **"Delivered"**
   - Add notes: "Package delivered successfully"
   - **Important:** Set `delivered_at` timestamp (automatically set when status changes to delivered)

**Expected Result:**
- Order progresses through each status
- Status history records each change
- Timestamps recorded for each transition
- Customer receives email notifications (if email verified)

---

### Phase 3: Return Request (Customer Side)

#### Step 3.1: View Delivered Order
1. Logout admin, login as **customer@test.com**
2. Navigate to `http://coreui_laravel_deploy.test/orders`
3. Click on the delivered order
4. Verify order shows "Delivered" status

**Expected Result:**
- Order details page loads
- Status shows "Delivered"
- Delivery information displayed
- **Return request option visible** (within 7-day window)

---

#### Step 3.2: Initiate Return Request
1. On order details page, find "Request Return" section
2. Click "Request Return" button
3. Return request form appears with:
   - **Reason dropdown:**
     - Damaged product
     - Wrong item received
     - Not as described
     - Quality issue
     - No longer needed
     - Other
   - **Description field** (required, min 20 characters)
   - **Upload images** (optional, proof of damage/issue)

4. Fill out the form:
   - **Reason:** "Quality issue"
   - **Description:** "The package contents do not meet the quality standards advertised. The materials appear worn and the documentation is incomplete."
   - **Upload images:** (Optional) Upload 1-3 photos showing the issue

5. Click "Submit Return Request"

**Expected Result:**
- Form validates (description minimum 20 characters)
- Return request submitted successfully
- Order status changes to "return_requested"
- Success message: "Return request submitted. We'll review it within 24 hours."
- Admin receives notification of new return request
- Return request entry created in `return_requests` table

**Verification Points:**
- ✅ Return request visible on order details page
- ✅ Return status: "Pending"
- ✅ Images uploaded successfully (if provided)
- ✅ Cannot submit multiple return requests for same order

---

### Phase 4: Return Review & Approval (Admin Side)

#### Step 4.1: View Return Requests
1. Logout customer, login as **admin@test.com**
2. Navigate to `http://coreui_laravel_deploy.test/admin/returns` (or check notification)
3. See list of pending return requests
4. Click "Review" on the customer's return request

**Expected Result:**
- Return request details displayed
- Customer information shown
- Order details visible
- Return reason and description displayed
- Uploaded images viewable (if any)
- Admin can approve or reject

---

#### Step 4.2: Review Return Details
Examine the return request:
- **Customer:** customer@test.com
- **Order Number:** ORD-2025-10-02-0001
- **Reason:** Quality issue
- **Description:** "The package contents do not meet quality standards..."
- **Images:** Review uploaded proof images
- **Order Value:** $X.XX
- **Payment Method:** E-wallet

**Decision Criteria:**
- ✅ Valid reason provided
- ✅ Return within 7-day window
- ✅ Proof images show legitimate issue (if provided)
- ✅ Description detailed and reasonable

---

#### Step 4.3: Approve Return Request
1. Click "Approve Return" button
2. Modal appears requesting:
   - **Admin Response:** "We've reviewed your return request and approved it. Please follow the shipping instructions sent to your email."
   - **Return Shipping Instructions:** "Please ship the item to: [Admin Office Address]. Use tracking number and send us the tracking details. We'll process your refund once we receive and verify the returned item."

3. Click "Confirm Approval"

**Expected Result:**
- Return request status changes to "Approved"
- Order status changes to "return_approved"
- Admin response saved
- Customer receives email notification with:
  - Return approval confirmation
  - Return shipping instructions
  - Admin's response message
- `approved_at` timestamp recorded

**Alternative: Reject Return Request**
If return is invalid:
1. Click "Reject Return" button
2. Provide detailed rejection reason: "The return window has expired" or "Insufficient proof of damage"
3. Click "Confirm Rejection"

**Expected Result:**
- Return request status: "Rejected"
- Order status returns to "Delivered"
- Customer notified with rejection reason
- `rejected_at` timestamp recorded

---

### Phase 5: Customer Ships Item Back

#### Step 5.1: Customer Views Approval (Customer Side)
1. Logout admin, login as **customer@test.com**
2. Navigate to `http://coreui_laravel_deploy.test/orders`
3. Click on the order with approved return
4. View return status section

**Expected Result:**
- Return status shows "Approved"
- Admin response displayed
- Shipping instructions visible
- Prompt to ship item back

---

#### Step 5.2: Update Return Tracking (Customer Side)
1. On order details page, find "Return Tracking" section
2. Enter return tracking number: "RETURN-TEST-789"
3. Click "Update Tracking"

**Expected Result:**
- Return tracking number saved
- Order status changes to "return_in_transit"
- Admin notified of return shipment
- Customer sees confirmation: "Return tracking updated"

---

### Phase 6: Admin Receives & Processes Return

#### Step 6.1: Confirm Return Receipt (Admin Side)
1. Login as **admin@test.com**
2. Navigate to `http://coreui_laravel_deploy.test/admin/returns`
3. Find the return request with status "In Transit"
4. Verify physical item has been received
5. Inspect returned item for:
   - Condition matches description
   - All components present
   - Proof of damage/issue valid

**Expected Result:**
- Return request details show tracking number
- Order status: "return_in_transit"
- Ready to process final steps

---

#### Step 6.2: Confirm Receipt & Process Refund
1. Click "Confirm Receipt" button
2. Verify refund amount: $X.XX (original order total)
3. Click "Process Refund"

**Expected Result:**
- Order status changes to "return_received"
- Refund automatically processed
- Amount credited to customer's e-wallet
- Transaction created with type "refund"
- Order status changes to "refunded"
- Return request status changes to "completed"
- `refunded_at` timestamp recorded
- Customer receives refund confirmation email
- Admin sees success message: "Return received and refund processed"

**Verification Points:**
- ✅ Customer wallet balance increased by order amount
- ✅ Refund transaction appears in wallet history
- ✅ Transaction reference matches original order
- ✅ Order status: "refunded"
- ✅ Return request status: "completed"
- ✅ All timestamps properly recorded

---

### Phase 7: Final Verification (Customer Side)

#### Step 7.1: Verify Refund Received
1. Logout admin, login as **customer@test.com**
2. Navigate to `http://coreui_laravel_deploy.test/wallet/transactions`
3. Find the refund transaction
4. Check wallet balance

**Expected Result:**
- Refund transaction visible with:
  - Type: "Refund"
  - Amount: +$X.XX
  - Description: "Refund for Order #ORD-2025-10-02-0001"
  - Status: "Completed"
- Wallet balance restored to pre-purchase amount
- Transaction reference ID matches

---

#### Step 7.2: Verify Order Status
1. Navigate to `http://coreui_laravel_deploy.test/orders`
2. Click on the refunded order
3. Review complete timeline

**Expected Result:**
- Order status: "Refunded"
- Complete status history visible:
  - Pending → Paid → Processing → Confirmed → Packing → Ready to Ship → Shipped → In Transit → Delivered → Return Requested → Return Approved → Return In Transit → Return Received → Refunded
- Return request details displayed:
  - Status: "Completed"
  - Refund date shown
  - All timestamps visible
- Payment refunded badge displayed

---

## Edge Cases to Test

### Test Case 1: Return Window Expired
**Steps:**
1. Create and deliver an order
2. Manually set `delivered_at` to 8 days ago (in database)
3. Login as customer and try to request return

**Expected Result:**
- "Request Return" button disabled or not visible
- Message: "Return window expired (7 days from delivery)"

---

### Test Case 2: Multiple Return Requests
**Steps:**
1. Create and deliver an order
2. Submit return request
3. Try to submit another return request for same order

**Expected Result:**
- Error message: "Return request already submitted for this order"
- Second request blocked

---

### Test Case 3: Return for Unpaid Order
**Steps:**
1. Create order but don't pay
2. Try to access return request page

**Expected Result:**
- Return option not available
- Message: "Order must be delivered before return can be requested"

---

### Test Case 4: Admin Rejection Flow
**Steps:**
1. Follow normal flow through return request submission
2. Admin rejects with reason: "Images don't show damage"

**Expected Result:**
- Return request status: "Rejected"
- Order status returns to "Delivered"
- Customer sees rejection reason
- No refund processed
- Customer cannot re-submit return (or can, depending on business rules)

---

## Database Verification Queries

Run these SQL queries to verify data integrity:

```sql
-- Check order status history
SELECT * FROM order_status_histories
WHERE order_id = [ORDER_ID]
ORDER BY created_at ASC;

-- Check return request
SELECT * FROM return_requests
WHERE order_id = [ORDER_ID];

-- Check wallet transactions
SELECT * FROM transactions
WHERE user_id = [CUSTOMER_USER_ID]
AND type IN ('payment', 'refund')
ORDER BY created_at DESC;

-- Verify wallet balance
SELECT balance FROM wallets
WHERE user_id = [CUSTOMER_USER_ID];
```

---

## Success Criteria Checklist

### ✅ Phase 1: Order & Payment
- [ ] Order created successfully
- [ ] E-wallet payment processed
- [ ] Wallet balance decreased
- [ ] Order marked as paid
- [ ] Cancellation button removed for paid orders

### ✅ Phase 2: Fulfillment
- [ ] Order progresses through all statuses
- [ ] Status history recorded
- [ ] Delivered status set correctly
- [ ] `delivered_at` timestamp recorded

### ✅ Phase 3: Return Request
- [ ] Return option visible within 7 days
- [ ] Form validation works
- [ ] Images upload successfully
- [ ] Return request created
- [ ] Admin notified

### ✅ Phase 4: Admin Review
- [ ] Return request visible to admin
- [ ] Approval workflow works
- [ ] Rejection workflow works
- [ ] Admin response saved
- [ ] Customer notified of decision

### ✅ Phase 5: Return Shipment
- [ ] Customer can add tracking number
- [ ] Order status updates to "return_in_transit"
- [ ] Admin notified of shipment

### ✅ Phase 6: Refund Processing
- [ ] Admin can confirm receipt
- [ ] Refund automatically processed
- [ ] Wallet credited correctly
- [ ] Transaction created
- [ ] All timestamps recorded

### ✅ Phase 7: Final Verification
- [ ] Wallet balance restored
- [ ] Order status shows "refunded"
- [ ] Complete history visible
- [ ] All data consistent

---

## Common Issues & Troubleshooting

### Issue: Return button not appearing
**Solution:**
- Check if order status is "delivered"
- Verify `delivered_at` is within 7 days
- Check if return request already exists

### Issue: Refund not processing
**Solution:**
- Verify payment method is "e_wallet"
- Check wallet exists for user
- Verify original payment transaction exists
- Check for sufficient wallet balance (shouldn't be needed for credit)

### Issue: Admin not receiving notifications
**Solution:**
- Verify admin has verified email address
- Check email notification settings
- Review logs for email sending errors

---

## Notes for Testers

1. **Use distinct test data** for each test run to avoid confusion
2. **Take screenshots** at each major step for documentation
3. **Record timestamps** to verify processing speed
4. **Test both approval and rejection** paths
5. **Verify email notifications** at each step (if email is verified)
6. **Check database** directly to ensure data integrity
7. **Test with different return reasons** to ensure all work
8. **Try edge cases** like expired windows, multiple requests, etc.

---

## Expected Timeline

- **Order Creation to Payment:** 2-3 minutes
- **Fulfillment to Delivery:** 5-10 minutes (manual status updates)
- **Return Request Submission:** 2 minutes
- **Admin Review & Approval:** 2-3 minutes
- **Customer Ships Back:** 1 minute (add tracking)
- **Admin Receives & Refunds:** 1-2 minutes
- **Total Test Time:** ~15-20 minutes for complete flow

---

## Post-Testing Cleanup

After testing, clean up test data:

```sql
-- Delete test return request
DELETE FROM return_requests WHERE order_id = [TEST_ORDER_ID];

-- Delete test order status history
DELETE FROM order_status_histories WHERE order_id = [TEST_ORDER_ID];

-- Delete test order items
DELETE FROM order_items WHERE order_id = [TEST_ORDER_ID];

-- Delete test order
DELETE FROM orders WHERE id = [TEST_ORDER_ID];

-- Optionally: Reset customer wallet balance to original
UPDATE wallets SET balance = [ORIGINAL_BALANCE] WHERE user_id = [CUSTOMER_USER_ID];
```

Or use the database reset seeder:
```bash
php artisan db:seed --class=DatabaseResetSeeder
```

---

**Document Version:** 1.0
**Last Updated:** October 2, 2025
**Status:** Ready for Testing
