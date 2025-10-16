-- ============================================================================
-- AUTO E-COMMERCE - Enhanced Database Schema with Normalization & New Tables
-- ============================================================================
-- Run this in phpMyAdmin SQL tab: http://localhost:8080
-- Database: auto_ecommerce_mariadb
-- This script creates normalized tables + additional business tables + seed data

SET NAMES utf8mb4;
USE `auto_ecommerce_mariadb`;

-- ============================================================================
-- PART 1: NORMALIZED VEHICLE STRUCTURE (Breaking down the large vehicles table)
-- ============================================================================

-- 1.1) Brands table (separated from vehicles)
CREATE TABLE IF NOT EXISTS `brands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL UNIQUE,
  `logo_url` varchar(255) DEFAULT NULL,
  `country_origin` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_brands_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.2) Models table (separated from vehicles)
CREATE TABLE IF NOT EXISTS `models` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `brand_id` bigint unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `body_type` enum('sedan','hatchback','suv','coupe','wagon','van','truck','convertible','other') DEFAULT NULL,
  `generation` varchar(50) DEFAULT NULL,
  `production_start` year DEFAULT NULL,
  `production_end` year DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `models_brand_slug_unique` (`brand_id`, `slug`),
  KEY `idx_models_brand` (`brand_id`),
  CONSTRAINT `fk_models_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.3) Vehicle Specifications (technical details separated)
CREATE TABLE IF NOT EXISTS `vehicle_specifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint unsigned NOT NULL,
  `engine` varchar(100) DEFAULT NULL,
  `engine_capacity` int DEFAULT NULL COMMENT 'in CC',
  `power_hp` int DEFAULT NULL,
  `power_kw` int DEFAULT NULL,
  `torque_nm` int DEFAULT NULL,
  `fuel_type` enum('gasoline','diesel','hybrid','electric','lpg','cng','other') DEFAULT NULL,
  `transmission` enum('manual','automatic','semi-automatic','cvt') DEFAULT NULL,
  `drivetrain` enum('fwd','rwd','awd','4wd') DEFAULT NULL,
  `cylinders` tinyint DEFAULT NULL,
  `valves` tinyint DEFAULT NULL,
  `top_speed_kmh` int DEFAULT NULL,
  `acceleration_0_100` decimal(4,2) DEFAULT NULL COMMENT 'seconds',
  `fuel_consumption_city` decimal(4,1) DEFAULT NULL COMMENT 'L/100km',
  `fuel_consumption_highway` decimal(4,1) DEFAULT NULL,
  `fuel_consumption_combined` decimal(4,1) DEFAULT NULL,
  `co2_emissions` int DEFAULT NULL COMMENT 'g/km',
  `euro_standard` varchar(10) DEFAULT NULL COMMENT 'Euro 5, Euro 6, etc',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vehicle_spec_unique` (`vehicle_id`),
  CONSTRAINT `fk_vehicle_spec` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.4) Vehicle Pricing (pricing history & offers separated)
CREATE TABLE IF NOT EXISTS `vehicle_pricing` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint unsigned NOT NULL,
  `purchase_price` decimal(10,2) DEFAULT NULL COMMENT 'What we paid',
  `listing_price` decimal(10,2) NOT NULL COMMENT 'Current asking price',
  `original_price` decimal(10,2) DEFAULT NULL COMMENT 'MSRP or first listing',
  `offer_price` decimal(10,2) DEFAULT NULL,
  `has_offer` tinyint(1) NOT NULL DEFAULT 0,
  `offer_type` enum('flash_sale','seasonal','clearance','negotiable','promotion') DEFAULT NULL,
  `offer_description` text DEFAULT NULL,
  `offer_starts_at` date DEFAULT NULL,
  `offer_expires_at` date DEFAULT NULL,
  `min_acceptable_price` decimal(10,2) DEFAULT NULL COMMENT 'Internal: lowest we can go',
  `price_negotiable` tinyint(1) NOT NULL DEFAULT 1,
  `vat_included` tinyint(1) NOT NULL DEFAULT 1,
  `currency` char(3) NOT NULL DEFAULT 'EUR',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vehicle_pricing_unique` (`vehicle_id`),
  CONSTRAINT `fk_vehicle_pricing` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.5) Vehicle Features (equipment & options)
CREATE TABLE IF NOT EXISTS `vehicle_features` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint unsigned NOT NULL,
  `feature_category` enum('safety','comfort','technology','exterior','interior','performance','other') NOT NULL,
  `feature_name` varchar(100) NOT NULL,
  `feature_value` varchar(255) DEFAULT NULL,
  `is_standard` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=standard, 0=optional',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vehicle_features` (`vehicle_id`, `feature_category`),
  CONSTRAINT `fk_vehicle_features` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PART 2: NEW BUSINESS TABLES (specific to auto e-commerce)
-- ============================================================================

-- 2.1) Vehicle Categories (for filtering/navigation)
CREATE TABLE IF NOT EXISTS `vehicle_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `order_index` int NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_category_parent` (`parent_id`),
  CONSTRAINT `fk_category_parent` FOREIGN KEY (`parent_id`) REFERENCES `vehicle_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.2) Vehicle-Category pivot
CREATE TABLE IF NOT EXISTS `vehicle_category_pivot` (
  `vehicle_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`vehicle_id`, `category_id`),
  CONSTRAINT `fk_vcp_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vcp_category` FOREIGN KEY (`category_id`) REFERENCES `vehicle_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.3) Service History (maintenance records)
CREATE TABLE IF NOT EXISTS `service_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint unsigned NOT NULL,
  `service_date` date NOT NULL,
  `mileage_at_service` int DEFAULT NULL,
  `service_type` enum('regular_maintenance','repair','inspection','tire_change','oil_change','other') NOT NULL,
  `service_description` text NOT NULL,
  `service_provider` varchar(255) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `documents` json DEFAULT NULL COMMENT 'Array of document URLs',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_service_vehicle` (`vehicle_id`),
  CONSTRAINT `fk_service_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.4) Appointments / Test Drives
CREATE TABLE IF NOT EXISTS `appointments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `appointment_type` enum('test_drive','inspection','consultation','delivery','other') NOT NULL DEFAULT 'test_drive',
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `duration_minutes` int NOT NULL DEFAULT 30,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled','no_show') NOT NULL DEFAULT 'pending',
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_appointment_vehicle` (`vehicle_id`),
  KEY `idx_appointment_user` (`user_id`),
  KEY `idx_appointment_date` (`appointment_date`, `status`),
  CONSTRAINT `fk_appointment_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_appointment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.5) Financing Options
CREATE TABLE IF NOT EXISTS `financing_options` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `provider` varchar(100) NOT NULL,
  `min_down_payment_percent` decimal(5,2) NOT NULL DEFAULT 10.00,
  `max_term_months` int NOT NULL DEFAULT 60,
  `interest_rate_min` decimal(5,2) NOT NULL,
  `interest_rate_max` decimal(5,2) NOT NULL,
  `min_amount` decimal(10,2) DEFAULT NULL,
  `max_amount` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.6) Financing Applications
CREATE TABLE IF NOT EXISTS `financing_applications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint unsigned NOT NULL,
  `financing_option_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `customer_cnp` varchar(13) DEFAULT NULL COMMENT 'Romanian ID',
  `requested_amount` decimal(10,2) NOT NULL,
  `down_payment` decimal(10,2) NOT NULL,
  `term_months` int NOT NULL,
  `monthly_income` decimal(10,2) DEFAULT NULL,
  `employment_status` enum('employed','self_employed','unemployed','retired','student') DEFAULT NULL,
  `status` enum('pending','under_review','approved','rejected','withdrawn') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `documents` json DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_financing_app_vehicle` (`vehicle_id`),
  KEY `idx_financing_app_status` (`status`),
  CONSTRAINT `fk_financing_app_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_financing_app_option` FOREIGN KEY (`financing_option_id`) REFERENCES `financing_options` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.7) Vehicle Reviews / Ratings
CREATE TABLE IF NOT EXISTS `vehicle_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `reviewer_name` varchar(255) NOT NULL,
  `reviewer_email` varchar(255) DEFAULT NULL,
  `rating` tinyint unsigned NOT NULL COMMENT '1-5 stars',
  `title` varchar(255) DEFAULT NULL,
  `review_text` text NOT NULL,
  `purchase_verified` tinyint(1) NOT NULL DEFAULT 0,
  `pros` text DEFAULT NULL,
  `cons` text DEFAULT NULL,
  `would_recommend` tinyint(1) DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `helpful_count` int NOT NULL DEFAULT 0,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_review_vehicle` (`vehicle_id`, `is_approved`),
  KEY `idx_review_rating` (`rating`),
  CONSTRAINT `fk_review_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_rating_range` CHECK (`rating` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.8) Trade-In Requests
CREATE TABLE IF NOT EXISTS `trade_in_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `interested_vehicle_id` bigint unsigned DEFAULT NULL COMMENT 'Vehicle they want to buy',
  `user_id` bigint unsigned DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `trade_brand` varchar(100) NOT NULL,
  `trade_model` varchar(100) NOT NULL,
  `trade_year` year NOT NULL,
  `trade_mileage` int NOT NULL,
  `trade_condition` enum('excellent','good','fair','poor') NOT NULL,
  `trade_vin` varchar(17) DEFAULT NULL,
  `trade_description` text DEFAULT NULL,
  `trade_images` json DEFAULT NULL,
  `estimated_value` decimal(10,2) DEFAULT NULL,
  `offered_value` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','evaluated','offer_sent','accepted','rejected','expired') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `evaluated_at` timestamp NULL DEFAULT NULL,
  `evaluated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_trade_in_status` (`status`),
  CONSTRAINT `fk_trade_in_vehicle` FOREIGN KEY (`interested_vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.9) Vehicle Documents
CREATE TABLE IF NOT EXISTS `vehicle_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint unsigned NOT NULL,
  `document_type` enum('registration','inspection','insurance','warranty','service','purchase','other') NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int DEFAULT NULL COMMENT 'bytes',
  `mime_type` varchar(100) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Show to customers?',
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vehicle_docs` (`vehicle_id`, `document_type`),
  CONSTRAINT `fk_vehicle_docs` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.10) Warranty Plans
CREATE TABLE IF NOT EXISTS `warranty_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `duration_months` int NOT NULL,
  `mileage_limit_km` int DEFAULT NULL,
  `coverage_details` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `is_transferable` tinyint(1) NOT NULL DEFAULT 0,
  `terms_conditions` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.11) Vehicle Warranty (which vehicles have which warranty)
CREATE TABLE IF NOT EXISTS `vehicle_warranties` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint unsigned NOT NULL,
  `warranty_plan_id` bigint unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `mileage_at_start` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vehicle_warranty` (`vehicle_id`),
  CONSTRAINT `fk_vehicle_warranty_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vehicle_warranty_plan` FOREIGN KEY (`warranty_plan_id`) REFERENCES `warranty_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.12) Newsletter Subscriptions
CREATE TABLE IF NOT EXISTS `newsletter_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL UNIQUE,
  `name` varchar(255) DEFAULT NULL,
  `preferences` json DEFAULT NULL COMMENT 'Categories of interest',
  `subscribed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unsubscribed_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `verification_token` varchar(100) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `newsletter_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.13) Price Alerts (users watching for price drops)
CREATE TABLE IF NOT EXISTS `price_alerts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `vehicle_id` bigint unsigned NOT NULL,
  `email` varchar(255) NOT NULL,
  `target_price` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_price_alert_vehicle` (`vehicle_id`, `is_active`),
  CONSTRAINT `fk_price_alert_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_price_alert_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.14) Comparison Lists (users comparing vehicles)
CREATE TABLE IF NOT EXISTS `vehicle_comparisons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL COMMENT 'For guests',
  `vehicle_ids` json NOT NULL COMMENT 'Array of vehicle IDs',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_comparison_user` (`user_id`),
  CONSTRAINT `fk_comparison_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PART 3: INDEXES & OPTIMIZATIONS
-- ============================================================================

-- Add useful indexes to existing vehicles table
CREATE INDEX IF NOT EXISTS `idx_vehicles_status` ON `vehicles` (`status`);
CREATE INDEX IF NOT EXISTS `idx_vehicles_brand_model` ON `vehicles` (`brand`, `model`);
CREATE INDEX IF NOT EXISTS `idx_vehicles_price` ON `vehicles` (`price`);
CREATE INDEX IF NOT EXISTS `idx_vehicles_year` ON `vehicles` (`year`);
CREATE INDEX IF NOT EXISTS `idx_vehicles_featured` ON `vehicles` (`featured`, `status`);

-- Add indexes to inquiries
CREATE INDEX IF NOT EXISTS `idx_inquiries_status` ON `inquiries` (`status`, `created_at`);
CREATE INDEX IF NOT EXISTS `idx_inquiries_vehicle` ON `inquiries` (`vehicle_id`);

-- Add indexes to contact_messages
CREATE INDEX IF NOT EXISTS `idx_contact_created` ON `contact_messages` (`created_at`);


