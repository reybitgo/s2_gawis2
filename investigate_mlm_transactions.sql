-- Investigation Queries for MLM Commission System
-- Run these in phpMyAdmin to understand your transaction recording

-- Query 1: Check ALL transaction types in your system
SELECT DISTINCT type, COUNT(*) as count
FROM transactions
GROUP BY type
ORDER BY count DESC;
-- This shows what transaction types exist in your database

-- Query 2: Check if there are ANY mlm_commission transactions (regardless of date)
SELECT COUNT(*) as total_mlm_transactions
FROM transactions
WHERE type = 'mlm_commission';
-- If this returns 0, MLM commissions might not be recording transactions

-- Query 3: Find the most recent transactions for users with MLM balance
SELECT 
    t.id,
    t.user_id,
    u.username,
    t.type,
    t.amount,
    t.created_at,
    w.mlm_balance
FROM users u
JOIN wallets w ON u.id = w.user_id
LEFT JOIN transactions t ON t.user_id = u.id
WHERE w.mlm_balance > 0
ORDER BY t.created_at DESC
LIMIT 20;
-- This shows what transactions these users have

-- Query 4: Check when MLM balances were last updated
SELECT 
    u.id,
    u.username,
    w.mlm_balance,
    w.updated_at as wallet_updated
FROM users u
JOIN wallets w ON u.id = w.user_id
WHERE w.mlm_balance > 0
ORDER BY w.updated_at DESC
LIMIT 10;
-- This shows when wallets with MLM balance were last modified

-- Query 5: Check activity_logs for MLM commission entries
SELECT 
    al.id,
    al.user_id,
    u.username,
    al.type,
    al.description,
    al.created_at
FROM activity_logs al
JOIN users u ON al.user_id = u.id
WHERE al.type LIKE '%mlm%'
ORDER BY al.created_at DESC
LIMIT 10;
-- This checks if MLM commissions are logged in activity_logs instead

-- Query 6: Check all transactions for a specific user with MLM balance
-- Replace 'gawis19' with an actual username
SELECT 
    t.id,
    t.type,
    t.amount,
    t.description,
    t.reference,
    t.created_at
FROM transactions t
JOIN users u ON t.user_id = u.id
WHERE u.username = 'gawis19'
ORDER BY t.created_at DESC;
-- This shows ALL transactions for a user who has MLM balance

-- Query 7: Check if transactions table has 'mlm' in description
SELECT 
    t.id,
    t.user_id,
    u.username,
    t.type,
    t.amount,
    t.description,
    t.created_at
FROM transactions t
JOIN users u ON t.user_id = u.id
WHERE t.description LIKE '%mlm%' OR t.description LIKE '%commission%'
ORDER BY t.created_at DESC
LIMIT 10;
-- This checks if MLM info is in description instead of type

-- Query 8: Find orders that should have triggered MLM commissions
SELECT 
    o.id,
    o.order_number,
    o.user_id,
    u.username,
    o.payment_status,
    o.created_at,
    oi.package_id,
    p.name as package_name,
    p.is_mlm_package
FROM orders o
JOIN users u ON o.user_id = u.id
JOIN order_items oi ON o.id = oi.order_id
JOIN packages p ON oi.package_id = p.id
WHERE o.payment_status = 'paid'
AND p.is_mlm_package = 1
ORDER BY o.created_at DESC
LIMIT 10;
-- This shows recent paid orders with MLM packages
