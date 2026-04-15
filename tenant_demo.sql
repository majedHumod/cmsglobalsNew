-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 13, 2026 at 03:58 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tenant_demo`
--

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `articles`
--


-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--


-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'عام',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faqs`
--


-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `landing_pages`
--

CREATE TABLE `landing_pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `header_image` varchar(255) NOT NULL,
  `header_text_color` varchar(7) NOT NULL DEFAULT '#ffffff',
  `show_join_button` tinyint(1) NOT NULL DEFAULT 1,
  `join_button_text` varchar(50) DEFAULT NULL,
  `join_button_url` varchar(255) DEFAULT NULL,
  `join_button_color` varchar(7) NOT NULL DEFAULT '#3b82f6',
  `content` text NOT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `landing_pages`
--


-- --------------------------------------------------------

--
-- Table structure for table `meal_plans`
--

CREATE TABLE `meal_plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `meal_type` enum('breakfast','lunch','dinner','snack') NOT NULL,
  `calories` int(11) DEFAULT NULL,
  `protein` int(11) DEFAULT NULL,
  `carbs` int(11) DEFAULT NULL,
  `fats` int(11) DEFAULT NULL,
  `ingredients` text NOT NULL,
  `instructions` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `prep_time` int(11) DEFAULT NULL,
  `cook_time` int(11) DEFAULT NULL,
  `servings` int(11) NOT NULL DEFAULT 1,
  `difficulty` enum('easy','medium','hard') NOT NULL DEFAULT 'easy',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meal_plans`
--


-- --------------------------------------------------------

--
-- Table structure for table `membership_types`
--

CREATE TABLE `membership_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `duration_days` int(11) NOT NULL DEFAULT 30,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_protected` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `membership_types`
--

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--



-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 5),
(1, 'App\\Models\\User', 7),
(1, 'App\\Models\\User', 12),
(3, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 3),
(3, 'App\\Models\\User', 5),
(3, 'App\\Models\\User', 6),
(3, 'App\\Models\\User', 8),
(3, 'App\\Models\\User', 9),
(3, 'App\\Models\\User', 13),
(3, 'App\\Models\\User', 14),
(4, 'App\\Models\\User', 5),
(5, 'App\\Models\\User', 7),
(5, 'App\\Models\\User', 12);

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notes`
--


-- --------------------------------------------------------

--
-- Table structure for table `nutrition_discounts`
--

CREATE TABLE `nutrition_discounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `discount_percentage` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nutrition_discounts`
--


-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `excerpt` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `show_in_menu` tinyint(1) NOT NULL DEFAULT 0,
  `access_level` enum('public','authenticated','admin','user','page_manager','membership') DEFAULT 'public',
  `is_premium` tinyint(1) NOT NULL DEFAULT 0,
  `access_roles` text DEFAULT NULL,
  `required_membership_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`required_membership_types`)),
  `is_premium_content` tinyint(1) NOT NULL DEFAULT 0,
  `menu_order` int(11) NOT NULL DEFAULT 0,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `permission_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `level` enum('basic','intermediate','advanced','critical') NOT NULL DEFAULT 'basic',
  `conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`conditions`)),
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`, `permission_category_id`, `description`, `level`, `conditions`, `expires_at`, `is_system`, `sort_order`, `is_active`) VALUES
(1, 'create post', 'web', '2025-03-06 23:01:30', '2025-03-06 23:01:30', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(2, 'edit post', 'web', '2025-03-06 23:01:30', '2025-03-06 23:01:30', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(3, 'delete post', 'web', '2025-03-06 23:01:30', '2025-03-06 23:01:30', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(4, 'create pages', 'web', '2025-06-10 09:23:08', '2025-06-10 09:23:08', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(5, 'edit pages', 'web', '2025-06-10 09:23:08', '2025-06-10 09:23:08', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(6, 'delete pages', 'web', '2025-06-10 09:23:08', '2025-06-10 09:23:08', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(7, 'view pages', 'web', '2025-06-10 09:23:08', '2025-06-10 09:23:08', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(8, 'publish pages', 'web', '2025-06-10 09:23:08', '2025-06-10 09:23:08', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(9, 'create posts', 'web', '2025-06-11 16:46:56', '2025-06-11 16:46:56', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(10, 'edit posts', 'web', '2025-06-11 16:46:56', '2025-06-11 16:46:56', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(11, 'delete posts', 'web', '2025-06-11 16:46:56', '2025-06-11 16:46:56', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(12, 'view posts', 'web', '2025-06-11 16:46:56', '2025-06-11 16:46:56', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(13, 'manage users', 'web', '2025-06-11 16:46:56', '2025-06-11 16:46:56', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(14, 'manage roles', 'web', '2025-06-11 16:46:56', '2025-06-11 16:46:56', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(15, 'manage permissions', 'web', '2025-06-11 16:46:56', '2025-06-11 16:46:56', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(16, 'manage pages', 'web', '2025-06-11 16:46:56', '2025-06-11 16:46:56', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(17, 'manage membership-types', 'web', '2025-06-11 16:46:56', '2025-06-11 16:46:56', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(18, 'create membership-types', 'web', '2025-06-11 16:46:56', '2025-06-11 16:46:56', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(19, 'edit membership-types', 'web', '2025-06-11 16:46:56', '2025-06-11 16:46:56', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(20, 'delete membership-types', 'web', '2025-06-11 16:46:56', '2025-06-11 16:46:56', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(21, 'view membership-types', 'web', '2025-06-11 16:46:56', '2025-06-11 16:46:56', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(22, 'create notes', 'web', '2025-06-11 19:14:27', '2025-06-11 19:14:27', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(23, 'edit notes', 'web', '2025-06-11 19:14:28', '2025-06-11 19:14:28', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(24, 'delete notes', 'web', '2025-06-11 19:14:28', '2025-06-11 19:14:28', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(25, 'view notes', 'web', '2025-06-11 19:14:28', '2025-06-11 19:14:28', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(26, 'create meal-plans', 'web', '2025-06-11 19:14:28', '2025-06-11 19:14:28', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(27, 'edit meal-plans', 'web', '2025-06-11 19:14:28', '2025-06-11 19:14:28', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(28, 'delete meal-plans', 'web', '2025-06-11 19:14:28', '2025-06-11 19:14:28', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(29, 'view meal-plans', 'web', '2025-06-11 19:14:28', '2025-06-11 19:14:28', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(30, 'publish meal-plans', 'web', '2025-06-11 19:14:28', '2025-06-11 19:14:28', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(31, 'view advanced permissions', 'web', '2025-06-11 19:14:28', '2025-06-11 19:14:28', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(32, 'manage advanced permissions', 'web', '2025-06-11 19:14:28', '2025-06-11 19:14:28', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(33, 'grant permission overrides', 'web', '2025-06-11 19:14:28', '2025-06-11 19:14:28', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(34, 'revoke permission overrides', 'web', '2025-06-11 19:14:28', '2025-06-11 19:14:28', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(35, 'view permission reports', 'web', '2025-06-11 19:14:28', '2025-06-11 19:14:28', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1),
(36, 'manage permission groups', 'web', '2025-06-11 19:14:28', '2025-06-11 19:14:28', NULL, NULL, 'basic', NULL, NULL, 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `permission_categories`
--

CREATE TABLE `permission_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `permission_group_id` bigint(20) UNSIGNED NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permission_groups`
--

CREATE TABLE `permission_groups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#6366f1',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permission_groups`
--

INSERT INTO `permission_groups` (`id`, `name`, `slug`, `description`, `icon`, `color`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'majed2', 'majed2', 'try', 'users', '#6e0c0c', 1, 1, '2025-06-11 19:16:05', '2025-06-11 19:16:05'),
(2, 'المدراء', 'almdraaa', NULL, 'cog', '#f2df64', 5, 1, '2025-06-11 19:36:33', '2025-06-11 19:36:33');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2025-03-06 23:01:30', '2025-03-06 23:01:30'),
(2, 'serviceprovider', 'web', '2025-03-06 23:01:30', '2025-03-06 23:01:30'),
(3, 'user', 'web', '2025-03-06 23:01:30', '2025-03-06 23:01:30'),
(4, 'page_manager', 'web', '2025-06-10 09:23:08', '2025-06-10 09:23:08'),
(5, 'coach', 'web', '2025-09-10 20:31:34', '2025-09-10 20:31:34');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 1),
(4, 1),
(4, 4),
(5, 1),
(5, 4),
(6, 1),
(6, 4),
(7, 1),
(7, 3),
(7, 4),
(8, 1),
(8, 4),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(22, 3),
(23, 1),
(23, 3),
(24, 1),
(25, 1),
(25, 3),
(26, 1),
(26, 3),
(27, 1),
(27, 3),
(28, 1),
(29, 1),
(29, 3),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--


-- --------------------------------------------------------

--
-- Table structure for table `session_bookings`
--

CREATE TABLE `session_bookings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `training_session_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_reference` varchar(255) DEFAULT NULL,
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `session_bookings`
--

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `group` varchar(255) NOT NULL DEFAULT 'general',
  `type` varchar(255) NOT NULL DEFAULT 'string',
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `is_tenant_specific` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `key`, `value`, `group`, `type`, `description`, `is_public`, `is_tenant_specific`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'أكاديمية اللياقة البدنية 2', 'general', 'string', 'Site name', 1, 1, '2025-09-10 22:20:44', '2025-09-11 19:48:40'),
(2, 'site_description', 'أكاديمية متخصصة في اللياقة البدنية والتغذية الصحية مع برامج تدريبية متقدمة ومتابعة شخصية من خبراء معتمدين', 'general', 'string', 'Site description', 1, 1, '2025-09-10 22:20:44', '2025-09-11 19:48:40'),
(3, 'site_logo', 'logos/TCDN7t7KWyajmYKFp6uv.png', 'general', 'string', 'Site logo path', 1, 1, '2025-06-13 15:21:20', '2025-09-11 19:50:52'),
(4, 'site_favicon', 'favicons/sSj361J77RoaSsOPg8bs.png', 'general', 'string', 'Site favicon path', 1, 1, '2025-06-13 15:21:20', '2025-09-10 20:02:39'),
(5, 'primary_color', '#dc2626', 'general', 'string', 'Primary color', 1, 1, '2025-09-10 22:20:44', '2025-09-11 19:48:40'),
(6, 'secondary_color', '#16a34a', 'general', 'string', 'Secondary color', 1, 1, '2025-09-10 22:20:44', '2025-09-11 19:48:40'),
(7, 'footer_text', '© 2025 أكاديمية اللياقة البدنية. جميع الحقوق محفوظة. نحو حياة صحية أفضل.', 'general', 'string', 'Footer text', 1, 1, '2025-09-10 22:20:44', '2025-09-11 19:48:40'),
(8, 'contact_email', 'info@fitness-academy.com', 'contact', 'string', 'البريد الإلكتروني', 1, 1, '2025-09-10 22:20:44', '2025-09-10 22:20:44'),
(9, 'contact_phone', '+966541234567', 'contact', 'string', 'رقم الهاتف', 1, 1, '2025-09-10 22:20:44', '2025-09-10 22:20:44'),
(10, 'contact_whatsapp', '+966541234567', 'contact', 'string', 'رقم الواتساب', 1, 1, '2025-09-10 22:20:44', '2025-09-10 22:20:44'),
(11, 'contact_telegram', '@cmsglobal', 'contact', 'string', 'Telegram username', 1, 1, '2025-06-13 15:21:20', '2025-06-13 15:21:20'),
(12, 'contact_address', 'الرياض، حي الملقا، شارع الأمير محمد بن عبدالعزيز2', 'contact', 'string', 'العنوان', 1, 1, '2025-09-10 22:20:44', '2025-09-10 22:20:44'),
(13, 'contact_map_link', 'https://maps.google.com/?q=24.7136,46.6753', 'contact', 'string', 'Google Maps link', 1, 1, '2025-06-13 15:21:20', '2025-06-13 15:21:20'),
(14, 'social_facebook', 'https://facebook.com/fitness.academy.sa', 'social', 'string', 'فيسبوك', 1, 1, '2025-09-10 22:20:44', '2025-09-10 22:20:44'),
(15, 'social_twitter', 'https://twitter.com/fitnessacademysa', 'social', 'string', 'تويتر', 1, 1, '2025-09-10 22:20:44', '2025-09-10 22:20:44'),
(16, 'social_instagram', 'https://instagram.com/fitness.academy.sa', 'social', 'string', 'انستقرام', 1, 1, '2025-09-10 22:20:44', '2025-09-10 22:20:44'),
(17, 'social_linkedin', 'https://linkedin.com/company/cmsglobal', 'social', 'string', 'LinkedIn profile URL', 1, 1, '2025-06-13 15:21:20', '2025-06-13 15:21:20'),
(18, 'social_youtube', 'https://youtube.com/c/fitnessacademysa', 'social', 'string', 'يوتيوب', 1, 1, '2025-09-10 22:20:44', '2025-09-10 22:20:44'),
(19, 'app_android', 'https://play.google.com/store/apps/details?id=com.cmsglobal.app', 'app', 'string', 'Android app URL', 1, 1, '2025-06-13 15:21:20', '2025-06-13 15:26:43'),
(20, 'app_ios', 'https://apps.apple.com/app/cmsglobal/id123456789', 'app', 'string', 'iOS app URL', 1, 1, '2025-06-13 15:21:20', '2025-06-13 15:26:43'),
(21, 'maintenance_mode', '0', 'app', 'boolean', 'Maintenance mode', 1, 1, '2025-06-13 15:21:20', '2025-09-15 14:12:11'),
(22, 'maintenance_message', NULL, 'app', 'string', 'Maintenance message', 1, 1, '2025-06-13 15:21:20', '2025-09-15 14:12:11'),
(23, 'enable_registration', '0', 'app', 'boolean', 'Enable user registration', 1, 1, '2025-06-13 15:21:20', '2025-09-15 14:12:11'),
(24, 'default_locale', NULL, 'app', 'string', 'Default locale', 1, 1, '2025-06-13 15:21:20', '2025-09-15 14:12:11'),
(25, 'items_per_page', NULL, 'app', 'integer', 'Items per page', 1, 1, '2025-06-13 15:21:20', '2025-09-15 14:12:11'),
(27, 'training_sessions_title', 'مدربونا الخبراء 2333', 'homepage', 'string', 'Training sessions section title', 1, 1, '2025-09-15 14:10:23', '2025-09-15 14:20:34'),
(28, 'training_sessions_description', 'تعرف على مدربينا المعتمدين المتخصصين في إرشادك خلال رحلتك مع الدعم الشخصي والتعليمات الواعية وممارسات العافية الشاملة 2', 'homepage', 'string', 'Training sessions section description', 1, 1, '2025-09-15 14:10:24', '2025-09-15 14:11:23'),
(29, 'training_sessions_count', '4', 'homepage', 'integer', 'Number of training sessions to display', 1, 1, '2025-09-15 14:10:24', '2025-09-15 21:35:20'),
(30, 'training_sessions_enabled', '1', 'homepage', 'boolean', 'Enable training sessions section', 1, 1, '2025-09-15 14:10:24', '2025-09-15 21:35:20'),
(31, 'testimonials_title', 'ماذا يقول عملاؤنا 333', 'homepage', 'string', 'Testimonials section title', 1, 1, '2025-09-15 14:10:24', '2025-09-15 14:20:34'),
(32, 'testimonials_description', 'اكتشف تجارب عملائنا الحقيقية وكيف ساعدتهم خدماتنا في تحقيق أهدافهم وتحسين حياتهم بطرق مذهلة ومؤثرة. 3', 'homepage', 'string', 'Testimonials section description', 1, 1, '2025-09-15 14:10:24', '2025-09-15 14:11:23'),
(33, 'testimonials_count', '4', 'homepage', 'integer', 'Number of testimonials to display', 1, 1, '2025-09-15 14:10:24', '2025-09-15 21:15:51'),
(34, 'testimonials_enabled', '1', 'homepage', 'boolean', 'Enable testimonials section', 1, 1, '2025-09-15 14:10:24', '2025-09-15 21:35:20');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `story_content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `testimonials`
--

-- --------------------------------------------------------

--
-- Table structure for table `training_sessions`
--

CREATE TABLE `training_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `duration_hours` int(11) NOT NULL DEFAULT 1,
  `image` varchar(255) DEFAULT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `training_sessions`
--


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `current_team_id` bigint(20) UNSIGNED DEFAULT NULL,
  `profile_photo_path` varchar(2048) DEFAULT NULL,
  `membership_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `membership_expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `current_team_id`, `profile_photo_path`, `membership_type_id`, `membership_expires_at`, `created_at`, `updated_at`) VALUES
(1, 'bander', 'b@gmail.com', NULL, '$2y$12$Ny9V/IYECokJsB3or7G4KOFswwAPEU/tSG0GCH21su19YRBi5pkhy', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-06-30 06:59:20', '2025-03-06 22:54:30', '2025-03-06 22:54:30'),

-- --------------------------------------------------------

--
-- Table structure for table `user_memberships`
--

CREATE TABLE `user_memberships` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `membership_type_id` bigint(20) UNSIGNED NOT NULL,
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_reference` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_memberships`
--

INSERT INTO `user_memberships` (`id`, `user_id`, `membership_type_id`, `starts_at`, `expires_at`, `is_active`, `payment_status`, `payment_amount`, `payment_reference`, `notes`, `created_at`, `updated_at`) VALUES
(1, 5, 1, '2025-06-09 15:46:10', '2028-06-27 15:46:10', 1, 'paid', 0.00, NULL, NULL, NULL, NULL),
(2, 6, 2, '2025-06-19 20:51:35', '2026-06-30 20:51:35', 1, 'paid', 0.00, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `workouts`
--

CREATE TABLE `workouts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `duration` int(11) NOT NULL,
  `difficulty` enum('easy','medium','hard') NOT NULL DEFAULT 'easy',
  `video_url` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `workouts`
--

-- --------------------------------------------------------

--
-- Table structure for table `workout_schedules`
--

CREATE TABLE `workout_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `workout_id` bigint(20) UNSIGNED NOT NULL,
  `week_number` int(11) NOT NULL,
  `session_number` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `workout_schedules`
--

--
-- Indexes for dumped tables
--

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `articles_user_id_foreign` (`user_id`);

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
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `faqs_user_id_foreign` (`user_id`),
  ADD KEY `faqs_is_active_sort_order_index` (`is_active`,`sort_order`),
  ADD KEY `faqs_category_index` (`category`);

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
-- Indexes for table `landing_pages`
--
ALTER TABLE `landing_pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `landing_pages_user_id_foreign` (`user_id`),
  ADD KEY `landing_pages_is_active_index` (`is_active`);

--
-- Indexes for table `meal_plans`
--
ALTER TABLE `meal_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meal_plans_user_id_foreign` (`user_id`);

--
-- Indexes for table `membership_types`
--
ALTER TABLE `membership_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `membership_types_slug_unique` (`slug`),
  ADD KEY `membership_types_is_active_sort_order_index` (`is_active`,`sort_order`),
  ADD KEY `membership_types_slug_index` (`slug`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notes_user_id_foreign` (`user_id`);

--
-- Indexes for table `nutrition_discounts`
--
ALTER TABLE `nutrition_discounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pages_slug_unique` (`slug`),
  ADD KEY `pages_user_id_foreign` (`user_id`),
  ADD KEY `pages_is_published_published_at_index` (`is_published`,`published_at`),
  ADD KEY `pages_show_in_menu_menu_order_index` (`show_in_menu`,`menu_order`);

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
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`),
  ADD KEY `perm_cat_active_idx` (`permission_category_id`,`is_active`),
  ADD KEY `perm_level_active_idx` (`level`,`is_active`);

--
-- Indexes for table `permission_categories`
--
ALTER TABLE `permission_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `permission_categories_permission_group_id_foreign` (`permission_group_id`);

--
-- Indexes for table `permission_groups`
--
ALTER TABLE `permission_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_groups_slug_unique` (`slug`),
  ADD KEY `permission_groups_is_active_sort_order_index` (`is_active`,`sort_order`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

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
-- Indexes for table `session_bookings`
--
ALTER TABLE `session_bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_booking_unique` (`training_session_id`,`booking_date`,`booking_time`),
  ADD KEY `session_bookings_user_id_status_index` (`user_id`,`status`),
  ADD KEY `session_bookings_booking_date_booking_time_index` (`booking_date`,`booking_time`),
  ADD KEY `session_bookings_payment_status_status_index` (`payment_status`,`status`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `site_settings_key_unique` (`key`),
  ADD KEY `site_settings_group_index` (`group`),
  ADD KEY `site_settings_is_public_is_tenant_specific_index` (`is_public`,`is_tenant_specific`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `testimonials_user_id_foreign` (`user_id`),
  ADD KEY `testimonials_is_visible_sort_order_index` (`is_visible`,`sort_order`);

--
-- Indexes for table `training_sessions`
--
ALTER TABLE `training_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `training_sessions_user_id_foreign` (`user_id`),
  ADD KEY `training_sessions_is_visible_sort_order_index` (`is_visible`,`sort_order`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_membership_type_id_foreign` (`membership_type_id`);

--
-- Indexes for table `user_memberships`
--
ALTER TABLE `user_memberships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_memberships_membership_type_id_foreign` (`membership_type_id`),
  ADD KEY `user_memberships_user_id_is_active_index` (`user_id`,`is_active`),
  ADD KEY `user_memberships_expires_at_is_active_index` (`expires_at`,`is_active`),
  ADD KEY `user_memberships_payment_status_index` (`payment_status`);

--
-- Indexes for table `workouts`
--
ALTER TABLE `workouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `workouts_status_difficulty_index` (`status`,`difficulty`),
  ADD KEY `workouts_user_id_status_index` (`user_id`,`status`);

--
-- Indexes for table `workout_schedules`
--
ALTER TABLE `workout_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `workout_schedule_unique` (`workout_id`,`week_number`,`session_number`),
  ADD KEY `workout_schedules_week_number_session_number_index` (`week_number`,`session_number`),
  ADD KEY `workout_schedules_workout_id_status_index` (`workout_id`,`status`),
  ADD KEY `workout_schedules_user_id_status_index` (`user_id`,`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `landing_pages`
--
ALTER TABLE `landing_pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `meal_plans`
--
ALTER TABLE `meal_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `membership_types`
--
ALTER TABLE `membership_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `nutrition_discounts`
--
ALTER TABLE `nutrition_discounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `permission_categories`
--
ALTER TABLE `permission_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permission_groups`
--
ALTER TABLE `permission_groups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `session_bookings`
--
ALTER TABLE `session_bookings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `training_sessions`
--
ALTER TABLE `training_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_memberships`
--
ALTER TABLE `user_memberships`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `workouts`
--
ALTER TABLE `workouts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `workout_schedules`
--
ALTER TABLE `workout_schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `faqs`
--
ALTER TABLE `faqs`
  ADD CONSTRAINT `faqs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `landing_pages`
--
ALTER TABLE `landing_pages`
  ADD CONSTRAINT `landing_pages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `meal_plans`
--
ALTER TABLE `meal_plans`
  ADD CONSTRAINT `meal_plans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `pages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `permission_categories`
--
ALTER TABLE `permission_categories`
  ADD CONSTRAINT `permission_categories_permission_group_id_foreign` FOREIGN KEY (`permission_group_id`) REFERENCES `permission_groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `session_bookings`
--
ALTER TABLE `session_bookings`
  ADD CONSTRAINT `session_bookings_training_session_id_foreign` FOREIGN KEY (`training_session_id`) REFERENCES `training_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `session_bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `testimonials_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `training_sessions`
--
ALTER TABLE `training_sessions`
  ADD CONSTRAINT `training_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_membership_type_id_foreign` FOREIGN KEY (`membership_type_id`) REFERENCES `membership_types` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_memberships`
--
ALTER TABLE `user_memberships`
  ADD CONSTRAINT `user_memberships_membership_type_id_foreign` FOREIGN KEY (`membership_type_id`) REFERENCES `membership_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_memberships_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `workouts`
--
ALTER TABLE `workouts`
  ADD CONSTRAINT `workouts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `workout_schedules`
--
ALTER TABLE `workout_schedules`
  ADD CONSTRAINT `workout_schedules_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `workout_schedules_workout_id_foreign` FOREIGN KEY (`workout_id`) REFERENCES `workouts` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
