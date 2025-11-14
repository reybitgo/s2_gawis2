### **QA Test Plan: Phase 2 - Cart & Checkout Integration Verification**

**Objective:** To verify that the system correctly handles the addition of products to the shopping cart, checkout process, and initial order management, in alignment with the `UNILEVEL_SYSTEM.md` specifications. This ensures that products and packages can coexist seamlessly in a single user transaction.

**Prerequisites:**

1.  **Products Exist:** At least 2-3 products must be created in the admin panel (`/admin/products`).
    *   One product should have a limited quantity (e.g., 10).
    *   Another product should have unlimited quantity (`quantity_available` is `null`).
    *   Each product must have Unilevel bonus settings configured.
2.  **Packages Exist:** At least one MLM package should be available for purchase.
3.  **Test User:** A non-admin user account must exist with sufficient funds in their `purchase_balance` or `withdrawable_balance` to complete test purchases.
4.  **Admin Access:** Access to the admin panel is required to check inventory levels and order details.

---

### **Test Case 1: Product Details Page**

*   **Objective:** Verify that the individual product page displays all required information correctly.
*   **Test Steps:**
    1.  Navigate to the public product catalog (`/products`).
    2.  Click the "View Details" button on any product.
    3.  Observe the product detail page (`/products/{product-slug}`).
*   **Expected Results:**
    *   The page loads without errors.
    *   The product's name, image, category, short description, and long description are displayed correctly.
    *   The product's price and the points awarded are clearly visible.
    *   A message indicating the total Unilevel bonus amount (e.g., "Earn Bonus: ₱60.00 distributed to upline") is displayed.
    *   An "Add to Cart" button is present and enabled if the product is in stock.
    *   If related products exist (in the same category), they are displayed at the bottom of the page.

---

### **Test Case 2: Cart Functionality with Mixed Items**

*   **Objective:** Verify that both products and packages can be added to the cart and that the cart view separates them correctly.
*   **Test Steps:**
    1.  Navigate to the product catalog (`/products`) and add a product to the cart.
    2.  Navigate to the package listing (`/packages`) and add a package to the cart.
    3.  Go to the Shopping Cart page (`/cart`).
*   **Expected Results:**
    *   The cart page loads with both items.
    *   The items are grouped under two distinct headings: **"MLM Packages"** and **"Unilevel Products"**.
    *   The "Order Summary" on the right side should display separate subtotals for packages and products (e.g., "Packages Subtotal" and "Products Subtotal") before showing the final combined total.
    *   The total item count and final price should reflect the sum of all items.

---

### **Test Case 3: Checkout with a Mixed Cart**

*   **Objective:** Verify that a user can successfully complete the checkout process with both products and packages in their cart.
*   **Test Steps:**
    1.  Follow the steps from **Test Case 2** to create a mixed cart.
    2.  From the cart page, click "Proceed to Checkout".
    3.  Complete the checkout form (select delivery method, accept terms).
    4.  Use the e-wallet to pay for the order.
    5.  After successful payment, you should be redirected to the order confirmation page.
*   **Expected Results:**
    *   The checkout process completes without any errors.
    *   The order confirmation page displays a summary of the order.
    *   The order details should correctly list both the package(s) and product(s) purchased.

---

### **Test Case 4: Inventory Management - Stock Reduction**

*   **Objective:** Verify that the system correctly reduces the stock quantity of items after a successful purchase.
*   **Test Steps:**
    1.  In the admin panel, note the current quantity of a limited-stock product and a limited-stock package.
    2.  As a test user, add one of each of these limited-stock items to your cart.
    3.  Complete the checkout process as in **Test Case 3**.
    4.  After the order is confirmed, return to the admin panel and check the inventory for those two items.
*   **Expected Results:**
    *   The `quantity_available` for both the purchased product and the purchased package should be reduced by 1.

---

### **Test Case 5: Inventory Management - Order Cancellation**

*   **Objective:** Verify that the system correctly restores the stock quantity of items after an order is canceled.
*   **Test Steps:**
    1.  Follow the steps from **Test Case 4** to place an order with limited-stock items.
    2.  Navigate to the user's "Order History" (`/orders`).
    3.  Find the newly created order and view its details.
    4.  If the option is available, cancel the order.
    5.  Return to the admin panel and check the inventory for the items from the canceled order.
*   **Expected Results:**
    *   The `quantity_available` for both the product and the package from the canceled order should be restored to their original values (increased by 1).

---

### **Test Case 6: Bonus/Commission Job Dispatching (Technical Verification)**

*   **Objective:** Verify that the correct background jobs are dispatched for processing commissions and bonuses. (This may require developer tools or access to logs).
*   **Test Steps:**
    1.  Clear the application logs.
    2.  Place an order containing both an MLM package and a Unilevel product.
    3.  After the order is confirmed, inspect the application logs (`storage/logs/laravel.log`).
*   **Expected Results:**
    *   A log entry with the message **"MLM Commission Processing Initiated"** should be present.
    *   A log entry with the message **"Unilevel Bonus Processing Initiated"** should also be present.
    *   This confirms that the checkout process is correctly triggering both systems.

---