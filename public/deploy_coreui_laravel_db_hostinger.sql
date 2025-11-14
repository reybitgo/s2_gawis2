-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 21, 2025 at 05:33 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gemini_gawis_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE PROCEDURE `check_circular_sponsor_reference` (IN `p_user_id` INT, IN `p_sponsor_id` INT)   BEGIN
                DECLARE v_current_id INT;
                DECLARE v_depth INT DEFAULT 0;
                DECLARE v_max_depth INT DEFAULT 100;

                -- Prevent self-sponsorship
                IF p_user_id = p_sponsor_id THEN
                    SIGNAL SQLSTATE "45000"
                        SET MESSAGE_TEXT = "A user cannot sponsor themselves.";
                END IF;

                -- Check for circular reference by walking up the sponsor chain
                SET v_current_id = p_sponsor_id;

                WHILE v_current_id IS NOT NULL AND v_depth < v_max_depth DO
                    -- If we encounter the user being updated, it's circular
                    IF v_current_id = p_user_id THEN
                        SIGNAL SQLSTATE "45000"
                            SET MESSAGE_TEXT = "Circular sponsor reference detected. The selected sponsor is already in your downline network.";
                    END IF;

                    -- Get the next sponsor in the chain
                    SELECT sponsor_id INTO v_current_id
                    FROM users
                    WHERE id = v_current_id;

                    SET v_depth = v_depth + 1;
                END WHILE;

                -- If we hit max depth, assume circular reference exists
                IF v_depth >= v_max_depth THEN
                    SIGNAL SQLSTATE "45000"
                        SET MESSAGE_TEXT = "Maximum sponsor chain depth exceeded. Possible circular reference.";
                END IF;
            END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `level` enum('DEBUG','INFO','WARNING','ERROR','CRITICAL') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INFO',
  `type` enum('security','transaction','mlm_commission','unilevel_bonus','mlm','wallet','system','order') COLLATE utf8mb4_unicode_ci NOT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `transaction_id` bigint UNSIGNED DEFAULT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `related_user_id` bigint UNSIGNED DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('gemini-gawis-cache-1c31ecdcf43a4c45335e125fdd661c66', 'i:1;', 1761067941),
('gemini-gawis-cache-1c31ecdcf43a4c45335e125fdd661c66:timer', 'i:1761067941;', 1761067941),
('gemini-gawis-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:2;', 1761067929),
('gemini-gawis-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1761067929;', 1761067929),
('gemini-gawis-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:8:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:17:\"wallet_management\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:20:\"transaction_approval\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:15:\"system_settings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:13:\"deposit_funds\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:14:\"transfer_funds\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:14:\"withdraw_funds\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:17:\"view_transactions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:14:\"profile_update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}}s:5:\"roles\";a:2:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:6:\"member\";s:1:\"c\";s:3:\"web\";}}}', 1761154284);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `package_id` bigint UNSIGNED NOT NULL,
  `action` enum('restock','sale','reservation','release','adjustment','return') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity_before` int UNSIGNED NOT NULL,
  `quantity_after` int UNSIGNED NOT NULL,
  `quantity_change` int NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Order number, reservation ID, etc.',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_09_18_121326_add_two_factor_columns_to_users_table', 1),
(5, '2025_09_18_124039_create_permission_tables', 1),
(6, '2025_09_18_142151_create_transactions_table', 1),
(7, '2025_09_18_142219_create_wallets_table', 1),
(8, '2025_09_18_173537_modify_users_table_add_username_rename_name', 1),
(9, '2025_09_18_174533_create_system_settings_table', 1),
(10, '2025_09_18_230930_update_transaction_types_enum', 1),
(11, '2025_09_18_235847_add_withdrawal_fee_to_transaction_types', 1),
(12, '2025_09_21_111847_update_transactions_table_enum_types', 1),
(13, '2025_09_27_015249_create_packages_table', 1),
(14, '2025_09_27_022220_add_soft_deletes_to_packages_table', 1),
(15, '2025_09_27_055840_create_sessions_table', 1),
(16, '2025_09_28_072147_create_orders_table', 1),
(17, '2025_09_28_072241_create_order_items_table', 1),
(18, '2025_09_28_121451_add_payment_and_refund_to_transaction_types', 1),
(19, '2025_09_28_121648_add_completed_status_to_transactions', 1),
(20, '2025_09_29_112626_enhance_orders_table_for_delivery_system', 1),
(21, '2025_09_29_112720_create_order_status_histories_table', 1),
(22, '2025_09_29_124056_add_delivery_address_to_users_table', 1),
(23, '2025_09_29_124751_add_delivery_address_json_to_orders_table', 1),
(24, '2025_09_30_102311_add_performance_indexes_to_tables', 1),
(25, '2025_09_30_162159_create_package_reservations_table', 1),
(26, '2025_09_30_162306_create_inventory_logs_table', 1),
(27, '2025_10_02_002843_create_return_requests_table', 1),
(28, '2025_10_02_002932_add_delivered_at_to_orders_table', 1),
(29, '2025_10_02_033648_add_return_statuses_to_orders_status_enum', 1),
(30, '2025_10_04_135126_create_mlm_settings_table', 1),
(31, '2025_10_04_135144_add_mlm_fields_to_users_table', 1),
(32, '2025_10_04_135156_add_mlm_fields_to_packages_table', 1),
(33, '2025_10_04_135212_add_segregated_balances_to_wallets_table', 1),
(34, '2025_10_04_174327_make_email_nullable_in_users_table', 1),
(35, '2025_10_06_172105_add_circular_reference_prevention_trigger_to_users_table', 1),
(36, '2025_10_06_173759_add_mlm_commission_type_to_transactions_table', 1),
(37, '2025_10_06_213614_create_referral_clicks_table', 1),
(38, '2025_10_07_105237_add_mlm_fields_to_transactions_table', 1),
(39, '2025_10_08_060430_add_suspended_at_to_users_table', 1),
(40, '2025_10_09_090034_migrate_old_balance_to_purchase_balance', 1),
(41, '2025_10_09_090518_drop_old_balance_columns_from_wallets_table', 1),
(42, '2025_10_09_150152_create_notifications_table', 1),
(43, '2025_10_09_174352_create_activity_logs_table', 1),
(44, '2025_10_10_103130_add_balance_conversion_type_to_transactions_table', 1),
(45, '2025_10_10_144506_add_payment_preferences_to_users_table', 1),
(46, '2025_10_10_215547_add_withdrawable_balance_to_wallets_table', 1),
(47, '2025_10_11_110153_add_unilevel_balance_to_wallets_table', 1),
(48, '2025_10_11_111137_create_products_table', 1),
(49, '2025_10_11_112234_create_unilevel_settings_table', 1),
(50, '2025_10_11_113932_add_unilevel_bonus_to_transactions_type_enum', 1),
(51, '2025_10_12_133000_fix_order_items_table_for_products', 1),
(52, '2025_10_12_194157_add_network_status_to_users_table', 1),
(53, '2025_10_14_083836_add_unilevel_bonus_to_activity_logs_type_enum', 1),
(54, '2025_10_14_093921_add_mlm_to_activity_logs_type_enum', 1);

-- --------------------------------------------------------

--
-- Table structure for table `mlm_settings`
--

CREATE TABLE `mlm_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `package_id` bigint UNSIGNED NOT NULL,
  `level` tinyint UNSIGNED NOT NULL,
  `commission_amount` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mlm_settings`
--

INSERT INTO `mlm_settings` (`id`, `package_id`, `level`, `commission_amount`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 200.00, 1, '2025-10-21 16:27:34', '2025-10-21 16:27:34'),
(2, 1, 2, 50.00, 1, '2025-10-21 16:27:34', '2025-10-21 16:27:34'),
(3, 1, 3, 50.00, 1, '2025-10-21 16:27:34', '2025-10-21 16:27:34'),
(4, 1, 4, 50.00, 1, '2025-10-21 16:27:34', '2025-10-21 16:27:34'),
(5, 1, 5, 50.00, 1, '2025-10-21 16:27:34', '2025-10-21 16:27:34');

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','paid','payment_failed','processing','confirmed','packing','ready_for_pickup','pickup_notified','received_in_office','ready_to_ship','shipped','out_for_delivery','delivered','delivery_failed','return_requested','return_approved','return_rejected','return_in_transit','return_received','completed','on_hold','cancelled','refunded','returned','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `delivered_at` timestamp NULL DEFAULT NULL,
  `delivery_method` enum('office_pickup','home_delivery') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'office_pickup',
  `delivery_address` json DEFAULT NULL,
  `tracking_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `courier_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pickup_date` timestamp NULL DEFAULT NULL,
  `pickup_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pickup_instructions` text COLLATE utf8mb4_unicode_ci,
  `estimated_delivery` timestamp NULL DEFAULT NULL,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `status_message` text COLLATE utf8mb4_unicode_ci,
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL,
  `tax_rate` decimal(5,4) NOT NULL DEFAULT '0.0000',
  `points_awarded` int NOT NULL DEFAULT '0',
  `points_credited` tinyint(1) NOT NULL DEFAULT '0',
  `metadata` json DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `customer_notes` text COLLATE utf8mb4_unicode_ci,
  `paid_at` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint UNSIGNED NOT NULL,
  `item_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'package',
  `order_id` bigint UNSIGNED NOT NULL,
  `package_id` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `points_awarded_per_item` int NOT NULL DEFAULT '0',
  `total_points_awarded` int NOT NULL DEFAULT '0',
  `package_snapshot` json DEFAULT NULL,
  `product_snapshot` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_status_histories`
--

CREATE TABLE `order_status_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `points_awarded` int NOT NULL DEFAULT '0',
  `quantity_available` int DEFAULT NULL,
  `short_description` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `long_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `meta_data` json DEFAULT NULL,
  `is_mlm_package` tinyint(1) NOT NULL DEFAULT '0',
  `max_mlm_levels` tinyint UNSIGNED NOT NULL DEFAULT '5',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `slug`, `price`, `points_awarded`, `quantity_available`, `short_description`, `long_description`, `image_path`, `is_active`, `sort_order`, `meta_data`, `is_mlm_package`, `max_mlm_levels`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Starter Pack', 'starter-pack', 1000.00, 1000, 1000, 'MLM Starter Package with 5-level commission structure', 'Join our Multi-Level Marketing program with the Starter Package. Earn commissions from your network across 5 levels: ₱200 from direct referrals (Level 1) and ₱50 from each of 4 indirect levels (Levels 2-5). Build your team and maximize your earnings potential!', 'packages/wSOMSjHRQ7w5JrpGjNraoAeVSZdKbRLbr1jJp3Le.png', 1, 0, '{\"features\": [\"MLM Business Opportunity\", \"5-Level Commission Structure\", \"Network Visualization\", \"Share Referral Links\", \"Withdrawable MLM Earnings\"], \"profit_margin\": \"60.00%\", \"company_profit\": 600, \"total_commission\": 400}', 1, 5, '2025-10-21 15:05:10', '2025-10-21 16:27:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `package_reservations`
--

CREATE TABLE `package_reservations` (
  `id` bigint UNSIGNED NOT NULL,
  `package_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `quantity` int UNSIGNED NOT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `status` enum('active','completed','expired','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Order number if completed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'wallet_management', 'web', '2025-10-21 08:12:12', '2025-10-21 08:12:12'),
(2, 'transaction_approval', 'web', '2025-10-21 08:12:12', '2025-10-21 08:12:12'),
(3, 'system_settings', 'web', '2025-10-21 08:12:12', '2025-10-21 08:12:12'),
(4, 'deposit_funds', 'web', '2025-10-21 08:12:12', '2025-10-21 08:12:12'),
(5, 'transfer_funds', 'web', '2025-10-21 08:12:12', '2025-10-21 08:12:12'),
(6, 'withdraw_funds', 'web', '2025-10-21 08:12:12', '2025-10-21 08:12:12'),
(7, 'view_transactions', 'web', '2025-10-21 08:12:12', '2025-10-21 08:12:12'),
(8, 'profile_update', 'web', '2025-10-21 08:12:12', '2025-10-21 08:12:12');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `points_awarded` int NOT NULL DEFAULT '0',
  `quantity_available` int DEFAULT NULL,
  `short_description` text COLLATE utf8mb4_unicode_ci,
  `long_description` text COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `meta_data` json DEFAULT NULL,
  `total_unilevel_bonus` decimal(10,2) NOT NULL DEFAULT '0.00',
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weight_grams` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `price`, `points_awarded`, `quantity_available`, `short_description`, `long_description`, `image_path`, `is_active`, `sort_order`, `meta_data`, `total_unilevel_bonus`, `sku`, `category`, `weight_grams`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Spirulina Tablets', 'spirulina-tablets', 500.00, 50, 1000, 'Boost your vitality with GAWIS Spirulina Tablets! Pure & organic, packed with protein and vitamins for natural wellness.', 'Elevate your daily wellness routine with GAWIS Spirulina Tablets – your natural powerhouse for a healthier you. Sourced from the purest organic spirulina, each tablet is a rich dietary supplement brimming with essential proteins and a wide spectrum of vitamins. Designed to support overall vitality, our Spirulina Tablets offer a convenient way to enrich your diet, promote energy, and contribute to your body\'s natural balance. Embrace the goodness of pure and organic ingredients with GAWIS, and take a significant step towards optimal health.', 'products/gAWjPj4tgFIBGJKMYkauaUNOwR3g8W6ws5ygUhz0.png', 1, 0, NULL, 75.00, 'PROD-ZAC0FFVH', 'Supplements', 50, '2025-10-21 16:21:50', '2025-10-21 16:22:51', NULL),
(2, 'MUSA Alka Drops', 'musa-alka-drops', 500.00, 50, 1000, 'Rebalance your body with GAWIS Musa Alka Drops! Naturally enhance your water for optimal hydration and alkalinity.', 'Discover the secret to enhanced hydration and balance with GAWIS Musa Alka Drops. In today\'s fast-paced world, maintaining your body\'s pH balance is more crucial than ever. Our premium Musa Alka Drops are formulated to effortlessly transform your ordinary drinking water into alkaline water, helping to neutralize acidity and support your body\'s natural equilibrium. Just a few drops can elevate your hydration experience, contributing to improved energy levels, better overall well-being, and a revitalized feeling from within. Make GAWIS Musa Alka Drops an essential part of your daily routine and experience the benefits of balanced alkalinity.', 'products/4kDkbi0qVwNIXzSCwpnySA4XfsOxZfuzvzTbY6WC.png', 1, 1, NULL, 100.00, 'PROD-BX7PONDA', 'Supplements', 100, '2025-10-21 16:43:04', '2025-10-21 16:44:06', NULL),
(3, 'Apros ni Ayat', 'apros-ni-ayat', 500.00, 50, 1000, 'Experience soothing relief with GAWIS Apros ni Ayat Therapeutic Oil. Pure essential oil for comfort and well-being.', 'Unwind and rejuvenate with GAWIS Apros ni Ayat Therapeutic Oil, your exquisite blend of pure essential oils crafted for ultimate comfort and well-being. Inspired by traditional wisdom, \"Apros ni Ayat\" translates to \"Touch of Love,\" embodying the gentle yet powerful relief this oil provides. Ideal for targeted massage, aromatherapy, or simply a moment of self-care, it helps soothe tired muscles, calm the mind, and promote a sense of tranquility. Each precious drop delivers a harmonious balance of nature\'s finest, inviting you to experience profound relaxation and natural revitalization. Embrace the touch of love with GAWIS Apros ni Ayat Therapeutic Oil and transform your daily routine into a sanctuary of peace.', 'products/pAyInhmNHNbWoTdIkuP529RnTIvWhbSlAF3g0jyk.png', 1, 2, NULL, 25.00, 'PROD-GTQMBIO3', 'Health & Wellness', NULL, '2025-10-21 16:48:41', '2025-10-21 16:49:14', NULL),
(4, 'Eye Drops', 'eye-drops', 500.00, 50, 1000, 'Soothe and refresh tired eyes with GAWIS Eyedrops. Fast-acting relief for dryness and irritation.', 'Give your eyes the gentle care they deserve with GAWIS Eyedrops. In a world of screens and environmental stressors, our eyes often bear the brunt, leading to dryness, irritation, and fatigue. GAWIS Eyedrops are formulated to provide immediate and lasting relief, rehydrating and lubricating your eyes to restore comfort and clarity. Whether you\'re dealing with prolonged screen time, environmental irritants, or simply dry eyes, our gentle yet effective formula works quickly to refresh and soothe. Trust GAWIS for clear, comfortable vision throughout your day.', 'products/1HOWdJNNIcHTWBI5Ezqx6rMYLYrlCp5XWJlmynqb.png', 1, 3, NULL, 50.00, 'PROD-J0SKGNQ8', 'Health & Wellness', 50, '2025-10-21 16:52:38', '2025-10-21 16:53:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `referral_clicks`
--

CREATE TABLE `referral_clicks` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `clicked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `registered` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `return_requests`
--

CREATE TABLE `return_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `reason` enum('damaged_product','wrong_item','not_as_described','quality_issue','no_longer_needed','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `images` json DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `admin_response` text COLLATE utf8mb4_unicode_ci,
  `return_tracking_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `refunded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2025-10-21 08:12:12', '2025-10-21 08:12:12'),
(2, 'member', 'web', '2025-10-21 08:12:12', '2025-10-21 08:12:12');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(4, 2),
(5, 2),
(6, 2),
(7, 2),
(8, 2);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('0dQMpbP5SENnQtZ6RLJ1gvjkrgiTDAMtOK2tOvYH', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'ZXlKcGRpSTZJa3hXTW1oMEswaFBWWEJXUm5aTVIzTnVlVXR4WjFFOVBTSXNJblpoYkhWbElqb2lPR2gxU1d4M05FUTVSbWhzTlVsS2JYRklTalJrVlVZelUyVkVWMVZMU0ZCblNrWkZaV0ZWYnpaNlMyRlBZbTFNVlhGWWVFWmxWbVF2ZFRodEszQnJlREpNTDIxU1ZtdElNMXBYTnpWWmFXVkpieXRrYjJzeU0wRTVZMWhvWlZST1ZFOVRUa05qVFdKTVMzUllXVkJzUlVGdFJVTlBiemQ0ZDAxRFlteHdaRXh4VkZWMWJHeFhXR3RNZDAxelkydHhkMmRqYjFkV1VUTnBlWG96UWpkdGVYUXlOVkk1YlRCUldHRnBNM2hOTTA0d01saFpVMlYwT1VKaGNEZHRVa0p5WkZGa1ZtSkJTVlY2UTB0UmFtUTBhVGxXSzBoWFpWZFJZbE5YZWxKNlFrMVNhVEkzVVRoR1lrWktjRmhQUW1JMFpuY3hVakkyTXpCVlVIWlFPRmxzTDJKRlJTdDVPVzVGWW5JelpIUnlRWGMyTmtKdWNFdzJiV2g1Ykd4Tk1XZHhVMjVZVHpCMGIzQnRSRU5yT1dkNVJ6azRZblo0YlU4NFdFUm1RV3BKYWxNaUxDSnRZV01pT2lKbVltWmtaVGszWXpnMk5tWXdOREk1TkdGbFlXVXdPRGhrTnpnd09XVXdOalpoT1RFM01qQXdZMlE1TTJWa1l6UXdNR1V5WmpFeVpEUTRNalJpWkdVeUlpd2lkR0ZuSWpvaUluMD0=', 1761067675),
('etxuhVBVpxaZf3khz20l5eT2LsMFRCdYr853eek1', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'ZXlKcGRpSTZJazFJVEdOc05EUldXVnB0UXlzMmNWTjNTa2w2UTJjOVBTSXNJblpoYkhWbElqb2lSVWgwU0VRemNIbFVUVmxtUmtjemRUUjVLMkoyWmtFMGJrSkpkMU5CYUZCSlNHazBSVXRhVFVOdlExWmpaMkY2WWpBMk9FMDBUelJQTVc5U1IyVjRRVFJGYjBKa01XNUtRMVY0Y2toV0t6VmtLMHd2VURkSFVtTTFTWGh2U21GbFVERTRjM2c0Y0VoQlIzVjFRbUZVUWpSSksweFNlbGxDTWxkV2JUaHpWWGxyWkhKTk1pdHlOQzloYVRocGRHazJibkJYZDNkQ2EybG9TMkZUZGxWcWJISTBLMmh6Y0VWa1pqZHZkWFZLU1hSQk1Yb3JPU3N4ZVRKRFJITllhV3RZVmxjelFrbFNRV05vZFRWd1FVNXVUSFI0Um0wdlQwVjRWVTVvY3pCd2EzRjFRMGx4YW1weFQyOTBaM1EzWVdGTlFqbFllR2ROTkVsMFFuTjJWMjFMTkV0cFNYUnNTVmQ2VmtsalMwSjNhM1ZwT1M5b1RIcFJTR1ZHZUdWRVdFd3dha2xITVdSemRrNUtNbEIyTDFoM1VHWjFXbmw0WTJKS2VIUkdaSE51ZDFoWWFtdGlVRkpWU0ZCNlFtVlpjek5FT0dOYWNWUkxjR1JuUWtGaWNYbzNablZRY1V4WGJYZDVjR1JzU0d0MUt6bEpVMFJMYkZsSFdXaDVSVGhrY1M5SFVFdEdVRzh2VkhwbGQwdFNVVXhRVWxCaWIwZzNlbkozVGpKaWVYcDVaRk5OYzJ4b1F6ZFFNbnBSTlVkbWQwOXZhWGxsYlV0dFRrczRUVU5rYmtsTFVDSXNJbTFoWXlJNklqWTFZalkzTnpBNVpUVTVaR0ZsTlRJNU5ERTVZVGxsTUdRNE1tWmhZVEF6TkRBeU1UZzNaakJoT0dZME5qTXdaRE0xWW1abFpUQTFZek5qTnpRek4yTWlMQ0owWVdjaU9pSWlmUT09', 1761067646),
('mdJugxyUJJLtf8ucMikwDtJU0cpZYnPDEVVEtvgT', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'ZXlKcGRpSTZJbmQxYVdnME5tTkZTRXRaZDFReWFEWmpPWEIwYUZFOVBTSXNJblpoYkhWbElqb2lUMHhIWmxOS2IwaEhLelZqUlZOQ2MzRnFRMGhOVlc1blRGSnFWbkZOY0VoeE5ERnNZWE52YzJzemJ6aGFPRVZ4TlZSbGREWmlla1ZrYVRSWk1sVllWRzR2ZVd4TVVsZEhaRkIwUTJKVFpFaHJWalZuZWsxeVEzbGhNR2gxVVhkR1UyTTJNbHA0VTFKUmJuVnVOVlk1VEVSRFMzTmlOamRzZEVoS1pXbFNibVEwTTJodlNFMVFNVEZESzFFeU9IZDBkR2MwV0Vwb2FrbEhNbTlMU0Vsd1VGWk1ha1ZEYmpKb2VUZEVhV012TVZodFJqQTFVRFpCVlhvd1UwUkhTM0ZuVEhSSllsTk1SRTVGV1hJdkwzcEZTalpETmxCSU9GSjBVR2xVUlZSQ2NuTk5kWFp4TUZWVUsyMUdTazFzYWtGQk9HcFVjWE5rV0c5SmVXMXNlbk50Wm0xTGEzcGxaWEpuVDAweWVIYzRVVzR4UWxod2FIUndNa1ZRYVZVcmR6Z3dUV1p3TkVnNFVHNXBjbE0yVEcxVVREbFBVMXBLWmxaSGRETlZORUoyTm5sUk1ERm9lbU5tZVZwSlVVODFjM2wyVlhoNlRHbDRZWFY1VFZZdk0yNWhkRzlyYXpNdlZsZEVlR3BwZVhKSmNXMVNObTlhSzA5V1JVZ3JRV3QxYVV4NGVXeHhNblpXZFc1U1IwaHJXbWM0WTFBclRIRkxRVDA5SWl3aWJXRmpJam9pTUdFMllXRTFaRGMxWmprNE16azJNMk15TmpreU5tUmlZV0ZtTUdFMU5XVTBZalE0WVRGa01EVmxaV1E1Tm1ZM05tUm1PRFEyWTJOaE4yWTNNMlExWVNJc0luUmhaeUk2SWlKOQ==', 1761065593),
('wEzrZPJvJVMBPVQvSo3ghrEz6wf0DCRVvIbsKtRT', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'ZXlKcGRpSTZJbTlMVjNkS2JrSkNiVE5KWkdOMGIwVXdiMHA1Y0VFOVBTSXNJblpoYkhWbElqb2lNSFIxYW5KR0swcElSbEpSVEhGWFltRlRVVFEzTHpZMmQxTkZNR3gwVWxVMFNIZGxWR3hYYVRSNlJrazFkMnhvYzFRd1FtdFJPREYyY2xJMVIyeFhhMHR3SzFvM1Ixa3dZM3B1ZFhKU1FYSm1PWEZWY1hSWVYyOVVSRzlKVkVWbFVITkNNM1UyZW5GNGFGUllOMGgyYURaMlMzbE9RemRCTUhrMVpYTmpRMWh1VWxCTGNuVXZiVXRXZERsVmRHcHhiVkZEVGt0aGRHRk9XR05TTHpNNU55dGtOVzg0WkVSMlZUSnlNbHBCVTFaVVdVMDNaMnhMWWxFMU5uUkxWRkZ6Ym5NeWVVcFdWeXM0V1VOcVUxcEdXWFIyV1dOM1IwWTFRbE56ZGswdlkzZzJiM0Z5VFZWVVR6SkdjblJJUTNkUWNVUnRZVGRaVEhKbFZqSmpja3hMTkVWcVMxZHhWbFZTWkVJeU1XUjNjRkZYWmtGSlUxaE9hamxQWlVzMGVGUlVUMFpaVkRNcldXUTJOMnhDWkU1dFlVRlJUbEJsTVVwU1NEZDNZa3hLTTFWa01FRlZUMEZDVkRKa2RWb3pibFk0YTNaU1VVdGFNMFI2U1RWQ1NrTk9lWE13VFdaMGVrMHdaSGxHVFVOSWJYVktlRVEwVW1KYVkwdHNLelpXVm5oWk9ERlNhazFpYTAwNGJubEdLMHR3ZW1sa2VrZExaejA5SWl3aWJXRmpJam9pWkdGaFl6YzBZakV6WVRFMll6RmhORFUwTnpZMlpqVXhaREUyWlROa1l6RmxObVUxWWpGaU5UYzNZalEwWTJNMVlUa3paamhoTkRKak1EWTBORGhrWkNJc0luUmhaeUk2SWlKOQ==', 1761067896),
('zlWpxcah8MtcQIRgd4aX4FCKPp1tdIrmva3uW0bE', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'ZXlKcGRpSTZJbk5GV2pabVJHcEtjMVU0UVRCNmVGWlBiR0pVT1hjOVBTSXNJblpoYkhWbElqb2lWM0YzWkROa1JubGpabmxDUkd4a1UweHhRVzFMTVRGcWMzbHRjRlJsVWpOdFIzWXlZMlpNYzIxcVJDdHlVMGd6YjNCWmVUQkpTbXBTWjBGemFXVXhSbXRHUkdocFJXOUZOMGc1YTI5VFFuaDJaM0oxTjI5UWVuQkRXSEJpWkZsT01FSnBUM1o0Tm5wSVJIUlphbkZJYjBGcGVtOW1UVlZLWW5aM1pGaHhNVGgzVFdKalJHZHJWRU54T1VZMFptRmtPRkZXUmt0VFVIQnFUWEp1Y21oWlF5c3hkbTl3UlZnNVkyVnVjR0V6TDI0M01YcDFXamhQUVZwVFUxWjBVRVZ6UVc4eU5qSlRlbTFVU1ZNMmNtdFBVVzVEV0VKeU5HaFlMMk14VkVNM1ZVcFZiSE5pYWxwTU0zQmpVVTVTV0ZObFFqVTNaRFZoVlRFeFNrRXdXQzlJTDA1SGFGcHhTM1YyUVZsSk1rc3JMelZWV1VWQ2IyUjFRWEF2TW0wclZEQmpNbWQyVFVwNEx6bEZTVEE5SWl3aWJXRmpJam9pTTJSak5URTRaREJtT0dRM01ESTBZemxrTnpGbU5XTXpNVEk1WVdObFpUbGhNemMyTUdVME5UZzRZelk1TWpnMU1USTRaVFJpWmpaaU1tRXdOelF3WWlJc0luUmhaeUk2SWlKOQ==', 1761065621);

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `key`, `value`, `type`, `description`, `created_at`, `updated_at`) VALUES
(1, 'app_name', 'Gawis iHerbal', 'string', 'Application name', '2025-10-21 08:12:14', '2025-10-21 08:12:14'),
(2, 'app_version', '1.0.0', 'string', 'Application version', '2025-10-21 08:12:14', '2025-10-21 08:12:14'),
(3, 'email_verification_enabled', '1', 'boolean', 'Enable email verification', '2025-10-21 08:12:14', '2025-10-21 08:12:14'),
(4, 'maintenance_mode', '0', 'boolean', 'Maintenance mode status', '2025-10-21 08:12:14', '2025-10-21 08:12:14'),
(5, 'tax_rate', '0', 'decimal', 'E-commerce tax rate (0.0 to 1.0)', '2025-10-21 08:12:14', '2025-10-21 08:12:14'),
(6, 'email_verification_required', '1', 'boolean', 'Require email verification after registration', '2025-10-21 08:12:14', '2025-10-21 08:12:14'),
(7, 'reset_count', '8', 'integer', 'Number of times database has been reset', '2025-10-21 08:12:15', '2025-10-21 16:29:58'),
(8, 'last_reset_date', '2025-10-21T16:29:58.441315Z', 'string', 'Last database reset timestamp', '2025-10-21 08:12:15', '2025-10-21 16:29:58');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `source_order_id` bigint UNSIGNED DEFAULT NULL,
  `type` enum('deposit','withdrawal','transfer','transfer_out','transfer_in','transfer_charge','withdrawal_fee','payment','refund','mlm_commission','balance_conversion','unilevel_bonus') COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_type` enum('mlm','unilevel') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `level` tinyint DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` enum('pending','approved','rejected','blocked','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `unilevel_settings`
--

CREATE TABLE `unilevel_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `level` tinyint NOT NULL,
  `bonus_amount` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `unilevel_settings`
--

INSERT INTO `unilevel_settings` (`id`, `product_id`, `level`, `bonus_amount`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 15.00, 1, '2025-10-21 16:22:12', '2025-10-21 16:22:51'),
(2, 1, 2, 15.00, 1, '2025-10-21 16:22:12', '2025-10-21 16:22:51'),
(3, 1, 3, 15.00, 1, '2025-10-21 16:22:12', '2025-10-21 16:22:51'),
(4, 1, 4, 15.00, 1, '2025-10-21 16:22:12', '2025-10-21 16:22:51'),
(5, 1, 5, 15.00, 1, '2025-10-21 16:22:12', '2025-10-21 16:22:51'),
(6, 2, 1, 20.00, 1, '2025-10-21 16:43:44', '2025-10-21 16:43:44'),
(7, 2, 2, 20.00, 1, '2025-10-21 16:43:44', '2025-10-21 16:44:06'),
(8, 2, 3, 20.00, 1, '2025-10-21 16:43:44', '2025-10-21 16:44:06'),
(9, 2, 4, 20.00, 1, '2025-10-21 16:43:44', '2025-10-21 16:44:06'),
(10, 2, 5, 20.00, 1, '2025-10-21 16:43:44', '2025-10-21 16:44:06'),
(11, 3, 1, 5.00, 1, '2025-10-21 16:48:50', '2025-10-21 16:49:14'),
(12, 3, 2, 5.00, 1, '2025-10-21 16:48:50', '2025-10-21 16:49:14'),
(13, 3, 3, 5.00, 1, '2025-10-21 16:48:50', '2025-10-21 16:49:14'),
(14, 3, 4, 5.00, 1, '2025-10-21 16:48:50', '2025-10-21 16:49:14'),
(15, 3, 5, 5.00, 1, '2025-10-21 16:48:50', '2025-10-21 16:49:14'),
(16, 4, 1, 10.00, 1, '2025-10-21 16:52:47', '2025-10-21 16:53:11'),
(17, 4, 2, 10.00, 1, '2025-10-21 16:52:47', '2025-10-21 16:52:47'),
(18, 4, 3, 10.00, 1, '2025-10-21 16:52:47', '2025-10-21 16:52:47'),
(19, 4, 4, 10.00, 1, '2025-10-21 16:52:47', '2025-10-21 16:52:47'),
(20, 4, 5, 10.00, 1, '2025-10-21 16:52:47', '2025-10-21 16:52:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `sponsor_id` bigint UNSIGNED DEFAULT NULL,
  `referral_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `network_status` enum('inactive','active','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive',
  `network_activated_at` timestamp NULL DEFAULT NULL,
  `last_product_purchase_at` timestamp NULL DEFAULT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_instructions` text COLLATE utf8mb4_unicode_ci,
  `delivery_time_preference` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'anytime',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `suspended_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payment_preference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gcash_number` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maya_number` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pickup_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `other_payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `other_payment_details` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `sponsor_id`, `referral_code`, `network_status`, `network_activated_at`, `last_product_purchase_at`, `username`, `fullname`, `email`, `phone`, `address`, `address_2`, `city`, `state`, `zip`, `delivery_instructions`, `delivery_time_preference`, `email_verified_at`, `suspended_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `created_at`, `updated_at`, `payment_preference`, `gcash_number`, `maya_number`, `pickup_location`, `other_payment_method`, `other_payment_details`) VALUES
(1, NULL, 'REFLFVGPEIS', 'inactive', NULL, NULL, 'admin', 'System Administrator', 'admin@gawisherbal.com', '+63 (947) 367-7436', '123 Herbal Street', NULL, 'Wellness City', 'HC', '12345', NULL, 'anytime', '2025-10-21 16:29:57', NULL, '$2y$12$9EcON3j9HnYV8hHE65XuZ.9gKLXQcn73.p.sdX08e8RIoo2gFadXO', NULL, NULL, NULL, NULL, '2025-10-21 16:29:57', '2025-10-21 16:29:57', NULL, NULL, NULL, NULL, NULL, NULL),
(2, 1, 'REFSJ9D32FQ', 'inactive', NULL, NULL, 'member', 'John Michael Santos', 'member@gawisherbal.com', '+63 (912) 456-7890', '456 Wellness Avenue', 'Unit 202', 'Health City', 'Metro Manila', '54321', 'Ring doorbell twice. Gate code: 1234', 'morning', '2025-10-21 16:29:58', NULL, '$2y$12$gJzXH3R2AgclcFKAH6JGye6bG/x8BCoxxrBCAbkIIzNfdCnpXNA4y', NULL, NULL, NULL, NULL, '2025-10-21 16:29:58', '2025-10-21 16:29:58', NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `before_users_insert_check_circular_sponsor` BEFORE INSERT ON `users` FOR EACH ROW BEGIN
                -- Only check if sponsor_id is not NULL
                IF NEW.sponsor_id IS NOT NULL THEN
                    -- For INSERT, we can't check against NEW.id since it doesn't exist yet
                    -- The model-level validation will handle this case
                    -- This trigger primarily protects against raw SQL updates

                    -- Still check for self-reference in case someone manually sets the ID
                    IF NEW.id IS NOT NULL AND NEW.id = NEW.sponsor_id THEN
                        SIGNAL SQLSTATE "45000"
                            SET MESSAGE_TEXT = "A user cannot sponsor themselves.";
                    END IF;
                END IF;
            END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_users_update_check_circular_sponsor` BEFORE UPDATE ON `users` FOR EACH ROW BEGIN
                -- Only check if sponsor_id is being changed and is not NULL
                IF NEW.sponsor_id IS NOT NULL AND (OLD.sponsor_id IS NULL OR NEW.sponsor_id != OLD.sponsor_id) THEN
                    CALL check_circular_sponsor_reference(NEW.id, NEW.sponsor_id);
                END IF;
            END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `mlm_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `unilevel_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `withdrawable_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `purchase_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_transaction_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `mlm_balance`, `unilevel_balance`, `withdrawable_balance`, `purchase_balance`, `is_active`, `last_transaction_at`, `created_at`, `updated_at`) VALUES
(1, 1, 0.00, 0.00, 0.00, 1000.00, 1, NULL, '2025-10-21 16:29:57', '2025-10-21 16:29:58'),
(2, 2, 0.00, 0.00, 0.00, 1000.00, 1, NULL, '2025-10-21 16:29:58', '2025-10-21 16:29:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_transaction_id_foreign` (`transaction_id`),
  ADD KEY `activity_logs_order_id_foreign` (`order_id`),
  ADD KEY `activity_logs_type_created_at_index` (`type`,`created_at`),
  ADD KEY `activity_logs_user_id_type_index` (`user_id`,`type`),
  ADD KEY `activity_logs_level_created_at_index` (`level`,`created_at`),
  ADD KEY `activity_logs_type_index` (`type`),
  ADD KEY `activity_logs_event_index` (`event`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_logs_user_id_foreign` (`user_id`),
  ADD KEY `inventory_logs_package_id_created_at_index` (`package_id`,`created_at`),
  ADD KEY `inventory_logs_action_created_at_index` (`action`,`created_at`),
  ADD KEY `inventory_logs_action_index` (`action`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mlm_settings`
--
ALTER TABLE `mlm_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mlm_settings_package_id_level_unique` (`package_id`,`level`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD KEY `orders_user_id_status_index` (`user_id`,`status`),
  ADD KEY `orders_status_created_at_index` (`status`,`created_at`),
  ADD KEY `orders_payment_status_index` (`payment_status`),
  ADD KEY `orders_order_number_index` (`order_number`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_user_id` (`user_id`),
  ADD KEY `idx_orders_created_at` (`created_at`),
  ADD KEY `idx_orders_order_number` (`order_number`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_index` (`order_id`),
  ADD KEY `order_items_package_id_index` (`package_id`),
  ADD KEY `order_items_order_id_package_id_index` (`order_id`,`package_id`),
  ADD KEY `idx_order_items_order_id` (`order_id`),
  ADD KEY `idx_order_items_package_id` (`package_id`),
  ADD KEY `order_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `order_status_histories`
--
ALTER TABLE `order_status_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_status_histories_order_id_created_at_index` (`order_id`,`created_at`),
  ADD KEY `order_status_histories_status_index` (`status`),
  ADD KEY `order_status_histories_changed_by_index` (`changed_by`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `packages_slug_unique` (`slug`),
  ADD KEY `packages_is_active_sort_order_index` (`is_active`,`sort_order`),
  ADD KEY `packages_slug_index` (`slug`),
  ADD KEY `idx_packages_is_active` (`is_active`),
  ADD KEY `idx_packages_slug` (`slug`);

--
-- Indexes for table `package_reservations`
--
ALTER TABLE `package_reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_reservations_package_id_foreign` (`package_id`),
  ADD KEY `package_reservations_user_id_foreign` (`user_id`),
  ADD KEY `package_reservations_expires_at_status_index` (`expires_at`,`status`),
  ADD KEY `package_reservations_session_id_index` (`session_id`),
  ADD KEY `package_reservations_expires_at_index` (`expires_at`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD UNIQUE KEY `products_sku_unique` (`sku`),
  ADD KEY `products_is_active_sort_order_index` (`is_active`,`sort_order`),
  ADD KEY `products_category_index` (`category`);

--
-- Indexes for table `referral_clicks`
--
ALTER TABLE `referral_clicks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `referral_clicks_user_id_clicked_at_index` (`user_id`,`clicked_at`);

--
-- Indexes for table `return_requests`
--
ALTER TABLE `return_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `return_requests_order_id_foreign` (`order_id`),
  ADD KEY `return_requests_user_id_foreign` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `system_settings_key_unique` (`key`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transactions_reference_number_unique` (`reference_number`),
  ADD KEY `transactions_approved_by_foreign` (`approved_by`),
  ADD KEY `idx_transactions_user_id` (`user_id`),
  ADD KEY `idx_transactions_status` (`status`),
  ADD KEY `idx_transactions_type` (`type`),
  ADD KEY `transactions_source_order_id_index` (`source_order_id`),
  ADD KEY `transactions_source_type_index` (`source_type`),
  ADD KEY `transactions_type_source_type_index` (`type`,`source_type`);

--
-- Indexes for table `unilevel_settings`
--
ALTER TABLE `unilevel_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unilevel_settings_product_id_level_unique` (`product_id`,`level`),
  ADD KEY `unilevel_settings_product_id_is_active_index` (`product_id`,`is_active`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_referral_code_unique` (`referral_code`),
  ADD KEY `users_sponsor_id_index` (`sponsor_id`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wallets_user_id_unique` (`user_id`),
  ADD KEY `wallets_withdrawable_balance_index` (`withdrawable_balance`),
  ADD KEY `wallets_unilevel_balance_index` (`unilevel_balance`),
  ADD KEY `wallets_mlm_balance_index` (`mlm_balance`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `mlm_settings`
--
ALTER TABLE `mlm_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_status_histories`
--
ALTER TABLE `order_status_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `package_reservations`
--
ALTER TABLE `package_reservations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `referral_clicks`
--
ALTER TABLE `referral_clicks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `return_requests`
--
ALTER TABLE `return_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `unilevel_settings`
--
ALTER TABLE `unilevel_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `activity_logs_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD CONSTRAINT `inventory_logs_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mlm_settings`
--
ALTER TABLE `mlm_settings`
  ADD CONSTRAINT `mlm_settings_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `order_status_histories`
--
ALTER TABLE `order_status_histories`
  ADD CONSTRAINT `order_status_histories_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `package_reservations`
--
ALTER TABLE `package_reservations`
  ADD CONSTRAINT `package_reservations_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `package_reservations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referral_clicks`
--
ALTER TABLE `referral_clicks`
  ADD CONSTRAINT `referral_clicks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `return_requests`
--
ALTER TABLE `return_requests`
  ADD CONSTRAINT `return_requests_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `return_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_source_order_id_foreign` FOREIGN KEY (`source_order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `unilevel_settings`
--
ALTER TABLE `unilevel_settings`
  ADD CONSTRAINT `unilevel_settings_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_sponsor_id_foreign` FOREIGN KEY (`sponsor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
