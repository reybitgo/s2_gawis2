# E-Commerce System Testing Workflow

## Overview

This document provides a comprehensive testing workflow for the Laravel 12 e-commerce system. The workflow covers the complete customer journey from package browsing to order completion, as well as administrative order management and status tracking.

**Testing Environment**: `http://coreui_laravel_deploy.test/`
**Current System Status**: Phase 6 Complete (Package Management + Cart + Checkout + Wallet Payment + Order Management + Admin Order Management)

---

## Pre-Testing Setup

### Database Preparation
```bash
# Ensure fresh test environment
php artisan migrate:fresh --seed

# Or quick reset preserving settings
php artisan db:seed --class=DatabaseResetSeeder
```

### Test User Accounts
- **Regular User**: Register a new user or use existing member account
- **Admin User**: Access admin panel with admin privileges
- **Wallet Balance**: Ensure test user has sufficient wallet balance (≥$500 recommended)

---

## Part 1: Customer Journey Testing

### Phase A: Package Discovery & Browsing
**Intent**: Test package catalog functionality, search capabilities, and user interface

#### Step A1: Browse Package Catalog
**URL**: `http://coreui_laravel_deploy.test/packages`
**Objective**: Verify package listing, search, and filtering functionality

**Test Actions**:
1. **Navigate to packages** - Click "Packages" in sidebar navigation
2. **Verify package display** - Confirm all packages show with images, prices, and points
3. **Test search functionality** - Search for specific package names
4. **Test sorting options** - Try all sorting: price low/high, points high/low, name A-Z
5. **Check responsive design** - Test on different screen sizes

**Expected Results**:
- Packages display in grid layout with proper information
- Search returns relevant results
- Sorting functions correctly
- Mobile-friendly responsive design
- Cart icon shows current item count

#### Step A2: View Individual Package Details
**URL**: `http://coreui_laravel_deploy.test/packages/{package-slug}`
**Objective**: Test package detail page functionality and cart integration

**Test Actions**:
1. **Click on any package** from the listing
2. **Review package information** - Check price, points, description, features
3. **Verify cart status** - Note "Add to Cart" button state
4. **Test image display** - Confirm package image or placeholder displays

**Expected Results**:
- Complete package information displayed
- Professional layout with all details visible
- "Add to Cart" button ready for interaction
- Breadcrumb navigation working

### Phase B: Shopping Cart Management
**Intent**: Test cart functionality, item management, and real-time updates

#### Step B1: Add Items to Cart
**Objective**: Verify add-to-cart functionality with real-time updates

**Test Actions**:
1. **Add first package** - Click "Add to Cart" on package detail page
2. **Verify button change** - Button should change to "In Cart" with checkmark
3. **Check cart count** - Header cart icon should show item count
4. **Add more packages** - Repeat with different packages
5. **Test quantity validation** - Try adding same package multiple times

**Expected Results**:
- Instant button state changes from "Add to Cart" to "In Cart"
- Cart count updates in header badge
- Success feedback/notifications
- Cart dropdown shows added items
- No page reloads during cart operations

#### Step B2: Cart Management Operations
**URL**: `http://coreui_laravel_deploy.test/cart`
**Objective**: Test cart page functionality and item management

**Test Actions**:
1. **Navigate to cart page** - Click cart icon or "View Cart" button
2. **Verify cart contents** - All added items should be listed
3. **Test quantity updates** - Increase/decrease quantities using +/- buttons
4. **Test item removal** - Remove individual items
5. **Check total calculations** - Verify subtotal, tax (if configured), and total
6. **Test clear cart** - Use clear cart function

**Expected Results**:
- All cart items display with correct information
- Quantity updates work with loading states
- Real-time total recalculation
- Tax calculation (if tax rate > 0%)
- Professional confirmation modals for removal/clear
- Responsive design for mobile/desktop

### Phase C: User Profile & Delivery Setup
**Intent**: Test profile-based delivery address management

#### Step C1: Configure Delivery Address
**URL**: `http://coreui_laravel_deploy.test/profile`
**Objective**: Set up delivery address in user profile for checkout pre-filling

**Test Actions**:
1. **Navigate to profile** - Go to user profile page
2. **Update delivery information**:
   - Full name, phone number
   - Complete address (street, city, state, zip)
   - Delivery instructions and time preferences
3. **Save profile changes** - Update profile with delivery information

**Expected Results**:
- Profile form accepts all delivery address fields
- Information saves successfully
- Validation works for required fields
- Profile displays updated information

### Phase D: Checkout Process
**Intent**: Test complete checkout flow with delivery options and payment processing

#### Step D1: Initiate Checkout
**URL**: `http://coreui_laravel_deploy.test/checkout`
**Objective**: Test checkout page functionality and delivery method selection

**Test Actions**:
1. **Navigate to checkout** - Click "Proceed to Checkout" from cart
2. **Review order summary** - Verify all items, quantities, and totals
3. **Test delivery method selection**:
   - Select "Office Pickup" - No additional form should appear
   - Select "Home Delivery" - Delivery address form should appear
4. **Verify delivery address pre-filling** - Form should pre-fill from profile
5. **Test address editing** - Modify address information inline
6. **Add customer notes** - Add special instructions

**Expected Results**:
- Order summary matches cart contents
- Delivery method selection works correctly
- Conditional delivery address form appears/hides properly
- Profile data pre-fills delivery form
- Inline editing updates both order and profile
- Customer notes field accepts input

#### Step D2: Payment Validation
**Objective**: Test wallet balance validation and payment readiness

**Test Actions**:
1. **Check wallet balance** - Verify sufficient funds display
2. **Review payment summary** - Check total amount and payment method
3. **Test terms acceptance** - Click terms and privacy policy links
4. **Verify pay button state** - Button should be disabled until terms accepted
5. **Accept terms and conditions** - Check the agreement checkbox

**Expected Results**:
- Wallet balance displays correctly
- Payment amount matches order total
- Terms/privacy modals open and display content
- "Pay Now" button enables only after terms acceptance and delivery info completion
- Clear payment summary with breakdown

#### Step D3: Complete Payment
**Objective**: Test wallet payment processing and order creation

**Test Actions**:
1. **Click "Pay Now"** - Process the payment
2. **Wait for processing** - Allow payment to complete
3. **Verify redirect** - Should redirect to order confirmation page
4. **Check wallet balance** - Balance should decrease by order amount
5. **Note order number** - Record the generated order number for tracking

**Expected Results**:
- Payment processes successfully
- Wallet balance deducted correctly
- Redirect to confirmation page
- Order number generated (ORD-YYYY-MM-DD-XXXX format)
- Order status shows as "Paid"

### Phase E: Order Confirmation & Management
**Intent**: Test post-purchase order management features

#### Step E1: Order Confirmation Review
**URL**: `http://coreui_laravel_deploy.test/checkout/confirmation/{order-id}`
**Objective**: Verify order confirmation page and initial order management

**Test Actions**:
1. **Review order details** - Check all order information
2. **Verify delivery information** - Confirm delivery method and address
3. **Check order status** - Should show "Paid" status
4. **Test action buttons** - Note available actions (cancel if still pending)
5. **Navigate to order history** - Click "View All Orders" link

**Expected Results**:
- Complete order information displayed
- Delivery method and address shown correctly
- Order status badge shows "Paid"
- Professional layout with clear information hierarchy
- Navigation to order history works

#### Step E2: Order History Access
**URL**: `http://coreui_laravel_deploy.test/orders`
**Objective**: Test order history interface and filtering capabilities

**Test Actions**:
1. **Review order statistics** - Check total orders, spending, points earned
2. **Locate recent order** - Find the just-placed order in the list
3. **Test filtering options**:
   - Filter by order status (Paid, Pending, etc.)
   - Filter by payment status
   - Filter by date range
4. **Test search functionality** - Search by order number
5. **Test pagination** - If multiple orders exist

**Expected Results**:
- Order statistics display correctly
- Recent order appears in list
- Filtering works for all criteria
- Search finds orders by number/notes
- Pagination functions properly

#### Step E3: Detailed Order View
**URL**: `http://coreui_laravel_deploy.test/orders/{order-id}`
**Objective**: Test individual order details and available actions

**Test Actions**:
1. **Click on order** from history list
2. **Review order information** - Verify all details are correct
3. **Check delivery information** - Confirm delivery method and address display
4. **Test available actions**:
   - Download invoice (for paid orders)
   - Reorder functionality
   - Cancel order (if still cancellable)
5. **Verify order timeline** - Check status progression

**Expected Results**:
- Complete order details display
- Delivery information shows correctly
- Action buttons work as expected
- PDF invoice generates successfully
- Reorder adds items back to cart
- Order cancellation works with refund processing

### Phase F: Order Cancellation Testing
**Intent**: Test order cancellation workflow and refund processing

#### Step F1: Test Order Cancellation (Optional)
**Objective**: Verify cancellation functionality and automatic refunds

**Test Actions**:
1. **Place a new test order** (if previous order not cancellable)
2. **Navigate to order details**
3. **Click "Cancel Order"** button
4. **Provide cancellation reason** - Select reason and add notes
5. **Confirm cancellation**
6. **Check wallet balance** - Verify refund processed
7. **Verify order status** - Should show "Cancelled"

**Expected Results**:
- Cancellation process works smoothly
- Automatic refund processes to wallet
- Order status updates to "Cancelled"
- Cancellation reason saved
- Email notifications sent (if configured)

---

## Part 2: Administrative Order Management Testing

### Phase G: Admin Order Overview
**Intent**: Test administrative order management interface and analytics

#### Step G1: Access Admin Order Management
**URL**: `http://coreui_laravel_deploy.test/admin/orders`
**Objective**: Test admin order dashboard and analytics

**Test Actions**:
1. **Navigate to admin orders** - Access admin panel and go to Orders section
2. **Review analytics dashboard** - Check order statistics and metrics
3. **Examine order list** - Verify all orders display with proper information
4. **Test filtering options** - Filter by status, payment status, date ranges
5. **Test search functionality** - Search by order number, customer name
6. **Check bulk operations** - Select multiple orders (if available)

**Expected Results**:
- Admin order dashboard loads successfully
- Analytics show accurate statistics
- All orders display with proper status badges
- Filtering and search work correctly
- Professional admin interface with proper navigation

#### Step G2: Admin Order Details
**URL**: `http://coreui_laravel_deploy.test/admin/orders/{order-id}`
**Objective**: Test detailed admin order view and management capabilities

**Test Actions**:
1. **Click on specific order** from admin order list
2. **Review comprehensive order information**:
   - Customer details with professional avatar
   - Order items and pricing breakdown
   - Delivery information and method
   - Payment and transaction details
3. **Check status management section** - View current status and next actions
4. **Review order timeline** - Check status history and changes
5. **Test admin notes** - Add internal admin notes

**Expected Results**:
- Complete order information displayed professionally
- Customer information with proper avatar design
- Clear delivery information display
- Status management with recommended actions
- Order timeline shows complete history
- Admin notes functionality works

### Phase H: Order Status Management
**Intent**: Test administrative order status updates and workflow management

#### Step H1: Test Status Transitions
**Objective**: Verify order status update functionality

**Test Actions**:
1. **Identify current order status** - Note the current status
2. **Check available next statuses** - View recommended next actions
3. **Update order status**:
   - For Office Pickup orders: progress through pickup workflow
   - For Home Delivery orders: progress through shipping workflow
4. **Add status change notes** - Include reason for status change
5. **Verify status history** - Check that change is logged

**Expected Results**:
- Status transitions work according to workflow rules
- Only appropriate next statuses are available
- Status changes log properly with notes
- Timeline updates with new status
- Email notifications sent (if configured)

#### Step H2: Test Delivery Method Workflows
**Objective**: Test both office pickup and home delivery workflows

**Office Pickup Workflow Test**:
1. **Paid** → **Processing** → **Confirmed** → **Packing** → **Ready for Pickup** → **Pickup Notified** → **Received in Office** → **Completed**

**Home Delivery Workflow Test**:
1. **Paid** → **Processing** → **Confirmed** → **Packing** → **Ready to Ship** → **Shipped** → **In Transit** → **Out for Delivery** → **Delivered** → **Completed**

**Test Actions for Each Workflow**:
1. **Create test orders** with different delivery methods
2. **Progress through each status** systematically
3. **Add appropriate notes** at each transition
4. **Verify status logic** - ensure only valid transitions allowed
5. **Test delivery information** - confirm addresses display correctly

**Expected Results**:
- Workflows follow proper progression
- Status transitions respect delivery method rules
- Delivery information displays appropriately for each method
- Status history maintains complete audit trail

### Phase I: Advanced Admin Features
**Intent**: Test advanced administrative features and reporting

#### Step I1: Test Order Analytics
**Objective**: Verify comprehensive analytics and reporting features

**Test Actions**:
1. **Review analytics dashboard** - Check all metrics and charts
2. **Test date range filtering** - Change date ranges for analytics
3. **Verify revenue tracking** - Check revenue calculations and trends
4. **Review status distribution** - Confirm status breakdown charts
5. **Check customer metrics** - Review customer purchase patterns
6. **Test export functionality** - Export reports if available

**Expected Results**:
- Analytics display accurate data
- Date filtering updates metrics correctly
- Revenue calculations are accurate
- Charts and visualizations work properly
- Customer insights provide valuable data

#### Step I2: Test Order Search and Filtering
**Objective**: Verify advanced search and filtering capabilities

**Test Actions**:
1. **Test order number search** - Search by specific order numbers
2. **Filter by customer** - Search orders by customer name/email
3. **Filter by status combinations** - Use multiple status filters
4. **Filter by date ranges** - Test various date combinations
5. **Filter by delivery method** - Separate office pickup from home delivery
6. **Test advanced search** - Combine multiple search criteria

**Expected Results**:
- All search criteria work accurately
- Filtering combines properly for complex queries
- Results update in real-time
- Search performance is acceptable
- Clear results with proper pagination

---

## Testing Checklist Summary

### Customer Journey ✓
- [ ] Package browsing and search functionality
- [ ] Individual package details and cart integration
- [ ] Add to cart with real-time updates
- [ ] Cart management and item operations
- [ ] Profile delivery address setup
- [ ] Checkout process with delivery options
- [ ] Wallet payment processing
- [ ] Order confirmation and details
- [ ] Order history and filtering
- [ ] Order actions (reorder, cancel, invoice)

### Administrative Management ✓
- [ ] Admin order dashboard and analytics
- [ ] Order list filtering and search
- [ ] Detailed order management interface
- [ ] Status management and transitions
- [ ] Office pickup workflow progression
- [ ] Home delivery workflow progression
- [ ] Order notes and internal comments
- [ ] Customer information management
- [ ] Advanced analytics and reporting
- [ ] Bulk operations and exports

### Integration Testing ✓
- [ ] Cart to order conversion
- [ ] Wallet payment integration
- [ ] Profile to checkout integration
- [ ] Order to history synchronization
- [ ] Admin to customer status visibility
- [ ] Email notifications (if configured)
- [ ] Mobile responsiveness
- [ ] Cross-browser compatibility

---

## Common Issues and Troubleshooting

### Customer Issues
1. **Cart items not adding**: Check JavaScript console for errors
2. **Payment failures**: Verify sufficient wallet balance
3. **Checkout form not submitting**: Ensure all required fields completed
4. **PDF invoice not generating**: Check order payment status

### Admin Issues
1. **Orders not loading**: Verify admin permissions and database connection
2. **Status updates failing**: Check status transition rules and validation
3. **Analytics not displaying**: Verify data exists and date ranges are correct
4. **Search not working**: Check database indexes and search parameters

### System Issues
1. **Session timeouts**: Extend session lifetime for testing
2. **Database locks**: Check for long-running transactions
3. **File permissions**: Verify storage directories are writable
4. **Memory limits**: Increase PHP memory for large order sets

---

## Post-Testing Validation

### Data Integrity Checks
- [ ] Order totals match cart calculations
- [ ] Inventory quantities updated correctly
- [ ] Wallet balances accurate after transactions
- [ ] Order status history complete and accurate
- [ ] Customer delivery addresses saved properly

### Performance Verification
- [ ] Page load times acceptable (< 3 seconds)
- [ ] AJAX operations responsive (< 1 second)
- [ ] Database queries optimized (check query log)
- [ ] Memory usage within limits
- [ ] No JavaScript errors in console

### Security Validation
- [ ] CSRF protection working on all forms
- [ ] Authorization checks preventing unauthorized access
- [ ] Input validation preventing malicious data
- [ ] File upload security (if applicable)
- [ ] Session security maintained throughout flow

---

**Testing Environment Requirements:**
- PHP 8.2+
- Laravel 12
- MySQL/PostgreSQL database
- Sufficient disk space for file uploads
- Valid SSL certificate for production testing

**Estimated Testing Time:**
- Customer Journey: 45-60 minutes
- Admin Management: 30-45 minutes
- Integration Testing: 15-30 minutes
- **Total: 90-135 minutes for complete workflow**

*Last Updated: September 30, 2025*
*Version: 1.0 - Phase 6 Complete*