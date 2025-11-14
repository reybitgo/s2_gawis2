# Comprehensive Plan for Return Process and Commission Management

This document outlines a phased approach to implement a robust return process management system. The primary goal is to ensure that commissions earned from an order are only withdrawable after a 7-day return window has passed, and to handle commission reversals for returned products.

## Phase 1: Database Schema Adjustments

This phase focuses on updating the database to support the new logic for holding and releasing commissions.

**Current Status:** <span style="color:orange">**Partially Implemented**</span>
- The `delivered_at` column **exists** on the `orders` table.
- The `is_withdrawable` and `withdrawable_at` columns **do not exist** on the `transactions` table.
- An alternative, `withdrawable_balance`, exists on the `wallets` table, but the logic to manage it over time is missing.

**Plan:**
1.  **Modify `transactions` table:**
    -   Add a new boolean column `is_withdrawable` with a default value of `false`.
    -   Add a new timestamp column `withdrawable_at` (nullable).
2.  **Modify `orders` table:**
    -   (Already implemented) Add a new timestamp column `delivered_at` (nullable).

## Phase 2: Commission Generation and Scheduling

This phase will adjust the commission generation logic and create a scheduled task to release commissions.

**Current Status:** <span style="color:red">**Not Implemented**</span>
- Commissions are currently made withdrawable instantly.
- No scheduled task or cron job exists to release commissions after a delay.

**Plan:**
1.  **Update Commission Service (`MLMCommissionService`):**
    -   Modify the service to set `is_withdrawable` to `false` and `withdrawable_at` to `delivered_at + 7 days` when commissions are created.
2.  **Create Cron Job Endpoint:**
    -   Define a new route in `routes/web.php`: `Route::get('/process-withdrawable-commission', [CommissionController::class, 'processWithdrawableCommissions']);`
    -   Create a `CommissionController` with a `processWithdrawableCommissions` method to release commissions where `withdrawable_at` is in the past.
    -   **Important:** Secure this endpoint to prevent unauthorized public access (e.g., with a secret token).
3.  **Set Up Cron Job:**
    -   In the hosting control panel, create a daily cron job to call the new endpoint.

## Phase 3: Update Wallet and Withdrawal System

This phase ensures that the user's wallet and the withdrawal functionality respect the new commission status.

**Current Status:** <span style="color:green">**Mostly Implemented**</span>
- The `Wallet` model already calculates a separate `withdrawable_balance`.
- The withdrawal logic correctly uses this balance.

**Plan:**
1.  **Update Wallet Balance Calculation:**
    -   The plan to segregate balances is already implemented. The main task is to ensure the new timed-release logic from Phase 2 correctly populates the `withdrawable_balance`.
2.  **Update Withdrawal Logic:**
    -   (Already implemented) The withdrawal process correctly checks against the "Available Balance".

## Phase 4: Implement Return and Commission Reversal Logic

This phase deals with the process of handling commissions when a product is returned.

**Current Status:** <span style="color:red">**Not Implemented**</span>
- The system can process refunds to a customer.
- However, it **does not** reverse the commissions that were paid to the upline for the returned product. This is a critical gap.

**Plan:**
1.  **Create a Commission Reversal Service:**
    -   When a return is approved and a refund is processed, this service must be triggered.
    -   The service will find all commissions associated with the original order.
    -   It will then create new debit transactions to reverse those commissions from the wallets of the users who received them.

## Phase 5: Frontend/UI Updates

This phase focuses on providing clear information to the user about their earnings.

**Current Status:** <span style="color:orange">**Partially Implemented**</span>
- The UI already shows a distinction between "Withdrawable Balance" and other balances.
- It **does not** have a concept of "Pending Balance" or show users when their pending funds will become available.

**Plan:**
1.  **Update User Dashboard/Wallet View:**
    -   (Already implemented) Display "Available for Withdrawal".
    -   Add a new section for "Pending Balance" that sums up commissions that are not yet withdrawable.
2.  **Update Transaction History:**
    -   For pending commissions, add a visual indicator and display the date they will become available (using the `withdrawable_at` field).

## Phase 6: Comprehensive Testing

This phase ensures the reliability and correctness of the new system.

**Current Status:** <span style="color:red">**Not Implemented**</span>
- There is a near-total lack of test coverage for commission, return, or wallet logic.

**Plan:**
1.  **Unit Tests:**
    -   Write unit tests for the `ProcessWithdrawableCommissions` logic.
    -   Write unit tests for the commission reversal service.
2.  **Feature Tests:**
    -   Create a feature test for the complete commission lifecycle: order -> pending commission -> scheduled job -> withdrawable commission.
    -   Create a feature test for the return scenario: order -> commission -> return approved -> commission reversed.
