-- ============================================================================
-- Smart Tourism Information System - Database Schema
-- Target Engine: MySQL 8.0+
-- File: database.sql
-- Contains: All 44 database tables used in the project
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `tourism_db` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `tourism_db`;

-- Set SQL settings for standard compliance
SET FOREIGN_KEY_CHECKS = 0;

-- Drop tables if they already exist to allow clean re-import
DROP TABLE IF EXISTS `business_promotions`;
DROP TABLE IF EXISTS `business_hours`;
DROP TABLE IF EXISTS `business_services`;
DROP TABLE IF EXISTS `business_images`;
DROP TABLE IF EXISTS `user_notification_settings`;
DROP TABLE IF EXISTS `push_subscriptions`;
DROP TABLE IF EXISTS `ai_messages`;
DROP TABLE IF EXISTS `ai_conversations`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `trip_itineraries`;
DROP TABLE IF EXISTS `trips`;
DROP TABLE IF EXISTS `blocked_ips`;
DROP TABLE IF EXISTS `security_alerts`;
DROP TABLE IF EXISTS `login_attempts`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `system_settings`;
DROP TABLE IF EXISTS `user_achievements`;
DROP TABLE IF EXISTS `deletion_request_items`;
DROP TABLE IF EXISTS `deletion_requests`;
DROP TABLE IF EXISTS `chat_messages`;
DROP TABLE IF EXISTS `chats`;
DROP TABLE IF EXISTS `gallery_likes`;
DROP TABLE IF EXISTS `gallery_comments`;
DROP TABLE IF EXISTS `gallery_media_tags`;
DROP TABLE IF EXISTS `gallery_media`;
DROP TABLE IF EXISTS `favorites`;
DROP TABLE IF EXISTS `review_images`;
DROP TABLE IF EXISTS `review_replies`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `event_tags`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `businesses`;
DROP TABLE IF EXISTS `places`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `provinces`;
DROP TABLE IF EXISTS `personal_access_tokens`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- 1. TABLE: users
-- Core entity storing administrators, guides, editors, business owners, and registered tourists
-- ============================================================================
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NULL,
  `password_hash` VARCHAR(255) NULL,
  `provider` VARCHAR(50) NULL,
  `provider_id` VARCHAR(150) NULL,
  `provider_email` VARCHAR(150) NULL,
  `email_verified_at` TIMESTAMP NULL,
  `avatar` LONGTEXT NULL,
  `role` ENUM('Super Admin', 'Admin', 'Guide / Editor', 'User') NOT NULL DEFAULT 'User',
  `status` ENUM('Active', 'Inactive', 'Suspended') NOT NULL DEFAULT 'Active',
  `location` VARCHAR(100) NULL,
  `verified` BOOLEAN NOT NULL DEFAULT FALSE,
  `two_factor_auth` BOOLEAN NOT NULL DEFAULT FALSE,
  `subscription` ENUM('Free', 'Basic', 'Premium') NOT NULL DEFAULT 'Free',
  `activity_level` ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Low',
  `bio` TEXT NULL,
  `last_active_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `uk_users_email` UNIQUE (`email`),
  INDEX `idx_users_role` (`role`),
  INDEX `idx_users_status` (`status`),
  INDEX `idx_users_provider_provider_id` (`provider`, `provider_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. TABLE: password_reset_tokens
-- Password reset tokens for authentication
-- ============================================================================
CREATE TABLE `password_reset_tokens` (
  `email` VARCHAR(150) PRIMARY KEY,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. TABLE: sessions
-- User web HTTP sessions
-- ============================================================================
CREATE TABLE `sessions` (
  `id` VARCHAR(255) PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  INDEX `idx_sessions_user_id` (`user_id`),
  INDEX `idx_sessions_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. TABLE: cache
-- Framework caching storage table
-- ============================================================================
CREATE TABLE `cache` (
  `key` VARCHAR(255) PRIMARY KEY,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` BIGINT NOT NULL,
  INDEX `idx_cache_expiration` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. TABLE: cache_locks
-- Atomic lock handling for caching operations
-- ============================================================================
CREATE TABLE `cache_locks` (
  `key` VARCHAR(255) PRIMARY KEY,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` BIGINT NOT NULL,
  INDEX `idx_cache_locks_expiration` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. TABLE: jobs
-- Queue jobs worker queue table
-- ============================================================================
CREATE TABLE `jobs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `attempts` SMALLINT UNSIGNED NOT NULL,
  `reserved_at` INT UNSIGNED NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  INDEX `idx_jobs_queue` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 7. TABLE: job_batches
-- Batched background queue job tracking
-- ============================================================================
CREATE TABLE `job_batches` (
  `id` VARCHAR(255) PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `total_jobs` INT NOT NULL,
  `pending_jobs` INT NOT NULL,
  `failed_jobs` INT NOT NULL,
  `failed_job_ids` LONGTEXT NOT NULL,
  `options` MEDIUMTEXT NULL,
  `cancelled_at` INT NULL,
  `created_at` INT NOT NULL,
  `finished_at` INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. TABLE: failed_jobs
-- Record of failed queue processing tasks
-- ============================================================================
CREATE TABLE `failed_jobs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` VARCHAR(255) NOT NULL,
  `connection` VARCHAR(255) NOT NULL,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `exception` LONGTEXT NOT NULL,
  `failed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `uk_failed_jobs_uuid` UNIQUE (`uuid`),
  INDEX `idx_failed_jobs` (`connection`, `queue`, `failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 9. TABLE: personal_access_tokens
-- Laravel Sanctum API authentication tokens
-- ============================================================================
CREATE TABLE `personal_access_tokens` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tokenable_type` VARCHAR(255) NOT NULL,
  `tokenable_id` BIGINT UNSIGNED NOT NULL,
  `name` TEXT NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `abilities` TEXT NULL,
  `last_used_at` TIMESTAMP NULL,
  `expires_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `uk_personal_access_tokens_token` UNIQUE (`token`),
  INDEX `idx_tokenable` (`tokenable_type`, `tokenable_id`),
  INDEX `idx_personal_access_tokens_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 10. TABLE: provinces
-- Geographic divisions, provinces, and capital cities in Cambodia
-- ============================================================================
CREATE TABLE `provinces` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `type` ENUM('Capital City', 'Province', 'Municipality') NOT NULL DEFAULT 'Province',
  `population` VARCHAR(50) NULL,
  `area` VARCHAR(50) NULL,
  `districts_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `communes_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `icon` VARCHAR(50) NULL,
  `description` TEXT NULL,
  `rating` DECIMAL(3, 2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `uk_provinces_name` UNIQUE (`name`),
  INDEX `idx_provinces_status` (`status`),
  INDEX `idx_provinces_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 11. TABLE: categories
-- Taxonomy classification for tourist attractions and places
-- ============================================================================
CREATE TABLE `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `color` VARCHAR(20) NOT NULL DEFAULT '#8B5CF6',
  `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `uk_categories_name` UNIQUE (`name`),
  INDEX `idx_categories_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 12. TABLE: places
-- Tourism locations, heritage sites, temples, resorts, and attractions
-- ============================================================================
CREATE TABLE `places` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `province_id` INT UNSIGNED NULL,
  `address` VARCHAR(255) NOT NULL,
  `coordinates` VARCHAR(100) NULL,
  `latitude` DECIMAL(10, 8) NULL,
  `longitude` DECIMAL(11, 8) NULL,
  `description` TEXT NULL,
  `best_time` VARCHAR(100) NULL,
  `duration` VARCHAR(50) NULL,
  `price` VARCHAR(50) NULL DEFAULT 'Free',
  `rating` DECIMAL(3, 2) NOT NULL DEFAULT 0.00,
  `reviews_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `visitors_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `image_url` VARCHAR(255) NULL,
  `is_featured` BOOLEAN NOT NULL DEFAULT FALSE,
  `status` ENUM('Active', 'Inactive', 'Pending') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_places_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_places_province` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_places_category_id` (`category_id`),
  INDEX `idx_places_province_id` (`province_id`),
  INDEX `idx_places_status` (`status`),
  INDEX `idx_places_is_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 13. TABLE: businesses
-- Local business listings, hotels, restaurants, and tourism vendors
-- ============================================================================
CREATE TABLE `businesses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `owner_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(180) NOT NULL,
  `description` LONGTEXT NULL,
  `category_id` INT UNSIGNED NULL,
  `province_id` INT UNSIGNED NULL,
  `address` VARCHAR(255) NULL,
  `latitude` DECIMAL(10, 7) NULL,
  `longitude` DECIMAL(10, 7) NULL,
  `phone` VARCHAR(50) NULL,
  `email` VARCHAR(150) NULL,
  `website` VARCHAR(255) NULL,
  `price_range` VARCHAR(30) NULL DEFAULT '$$',
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `verification_status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `verified_at` TIMESTAMP NULL,
  `verified_by` INT UNSIGNED NULL,
  `rejection_reason` TEXT NULL,
  `rating` DECIMAL(3, 2) NOT NULL DEFAULT 0.00,
  `review_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `uk_businesses_slug` UNIQUE (`slug`),
  CONSTRAINT `fk_businesses_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_businesses_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_businesses_province` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_businesses_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_businesses_owner_id` (`owner_id`),
  INDEX `idx_businesses_category_id` (`category_id`),
  INDEX `idx_businesses_province_id` (`province_id`),
  INDEX `idx_businesses_status` (`status`),
  INDEX `idx_businesses_verification_status` (`verification_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 14. TABLE: events
-- Festivals, marathons, cultural shows, and tourism events
-- ============================================================================
CREATE TABLE `events` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `location` VARCHAR(255) NOT NULL,
  `place_id` INT UNSIGNED NULL,
  `business_id` INT UNSIGNED NULL,
  `province_id` INT UNSIGNED NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NULL,
  `start_time` VARCHAR(20) NULL,
  `attendees_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `price` VARCHAR(50) NULL DEFAULT 'Free',
  `organizer` VARCHAR(150) NULL,
  `featured` BOOLEAN NOT NULL DEFAULT FALSE,
  `rating` DECIMAL(3, 2) NOT NULL DEFAULT 0.00,
  `image_url` VARCHAR(255) NULL,
  `status` ENUM('Upcoming', 'Ongoing', 'Completed', 'Cancelled', 'Scheduled') NOT NULL DEFAULT 'Upcoming',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_events_place` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_events_business` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_events_province` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_events_status` (`status`),
  INDEX `idx_events_start_date` (`start_date`),
  INDEX `idx_events_featured` (`featured`),
  INDEX `idx_events_business_id` (`business_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 15. TABLE: event_tags
-- Tags associated with tourism events
-- ============================================================================
CREATE TABLE `event_tags` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `event_id` INT UNSIGNED NOT NULL,
  `tag_name` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_event_tags_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `uk_event_tag` UNIQUE (`event_id`, `tag_name`),
  INDEX `idx_event_tags_event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 16. TABLE: reviews
-- Ratings and text reviews submitted by users for places or businesses
-- ============================================================================
CREATE TABLE `reviews` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `place_id` INT UNSIGNED NULL,
  `business_id` INT UNSIGNED NULL,
  `rating` TINYINT UNSIGNED NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `title` VARCHAR(150) NULL,
  `comment` TEXT NOT NULL,
  `likes_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `dislikes_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_verified` BOOLEAN NOT NULL DEFAULT FALSE,
  `status` ENUM('Approved', 'Pending', 'Rejected', 'Flagged') NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reviews_place` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reviews_business` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_reviews_user_id` (`user_id`),
  INDEX `idx_reviews_place_id` (`place_id`),
  INDEX `idx_reviews_business_id` (`business_id`),
  INDEX `idx_reviews_status` (`status`),
  INDEX `idx_reviews_rating` (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 17. TABLE: review_replies
-- Admin/Official response replies to user reviews
-- ============================================================================
CREATE TABLE `review_replies` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `review_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `comment` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_review_replies_review` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_review_replies_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_review_replies_review_id` (`review_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 18. TABLE: review_images
-- Photos attached to user reviews
-- ============================================================================
CREATE TABLE `review_images` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `review_id` INT UNSIGNED NOT NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_review_images_review` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_review_images_review_id` (`review_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 19. TABLE: favorites
-- Many-to-Many junction table connecting Users and Saved Places
-- ============================================================================
CREATE TABLE `favorites` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `place_id` INT UNSIGNED NOT NULL,
  `visited` BOOLEAN NOT NULL DEFAULT FALSE,
  `saved_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_favorites_place` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `uk_favorites_user_place` UNIQUE (`user_id`, `place_id`),
  INDEX `idx_favorites_user_id` (`user_id`),
  INDEX `idx_favorites_place_id` (`place_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 20. TABLE: gallery_media
-- Media library storing images and promotional videos
-- ============================================================================
CREATE TABLE `gallery_media` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `type` ENUM('image', 'video') NOT NULL DEFAULT 'image',
  `url` LONGTEXT NOT NULL,
  `category_id` INT UNSIGNED NULL,
  `place_id` INT UNSIGNED NULL,
  `file_size` VARCHAR(30) NULL,
  `dimensions` VARCHAR(30) NULL,
  `uploaded_by_user_id` INT UNSIGNED NULL,
  `views_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `likes_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('Published', 'Draft') NOT NULL DEFAULT 'Published',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_gallery_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_gallery_place` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_gallery_user` FOREIGN KEY (`uploaded_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_gallery_type` (`type`),
  INDEX `idx_gallery_status` (`status`),
  INDEX `idx_gallery_place_id` (`place_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 21. TABLE: gallery_media_tags
-- Tags linked to media items in gallery
-- ============================================================================
CREATE TABLE `gallery_media_tags` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `media_id` INT UNSIGNED NOT NULL,
  `tag_name` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_gallery_tags_media` FOREIGN KEY (`media_id`) REFERENCES `gallery_media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `uk_media_tag` UNIQUE (`media_id`, `tag_name`),
  INDEX `idx_gallery_tags_media_id` (`media_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 22. TABLE: gallery_comments
-- Comments left on gallery media items
-- ============================================================================
CREATE TABLE `gallery_comments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `gallery_media_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `parent_id` INT UNSIGNED NULL,
  `comment` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_gallery_comment_media` FOREIGN KEY (`gallery_media_id`) REFERENCES `gallery_media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_gallery_comment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_gallery_comment_parent` FOREIGN KEY (`parent_id`) REFERENCES `gallery_comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_gallery_comment_media_id` (`gallery_media_id`),
  INDEX `idx_gallery_comment_parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 23. TABLE: gallery_likes
-- User likes on gallery media items
-- ============================================================================
CREATE TABLE `gallery_likes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `gallery_media_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_gallery_like_media` FOREIGN KEY (`gallery_media_id`) REFERENCES `gallery_media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_gallery_like_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `uniq_gallery_media_user` UNIQUE (`gallery_media_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 24. TABLE: chats
-- Chat support sessions between users and System Admin / AI
-- ============================================================================
CREATE TABLE `chats` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `category` VARCHAR(100) NOT NULL DEFAULT 'Travel Planning',
  `priority` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
  `status` ENUM('active', 'closed', 'archived') NOT NULL DEFAULT 'active',
  `unread_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `last_message` TEXT NULL,
  `last_message_time` VARCHAR(30) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_chats_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_chats_user_id` (`user_id`),
  INDEX `idx_chats_status` (`status`),
  INDEX `idx_chats_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 25. TABLE: chat_messages
-- Messages within chat support conversations
-- ============================================================================
CREATE TABLE `chat_messages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `chat_id` INT UNSIGNED NOT NULL,
  `sender_type` ENUM('user', 'admin', 'ai') NOT NULL,
  `sender_user_id` INT UNSIGNED NULL,
  `message_text` TEXT NOT NULL,
  `is_read` BOOLEAN NOT NULL DEFAULT FALSE,
  `is_ai` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_chat_messages_chat` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_chat_messages_user` FOREIGN KEY (`sender_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_chat_messages_chat_id` (`chat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 26. TABLE: deletion_requests
-- User data deletion or listing removal requests
-- ============================================================================
CREATE TABLE `deletion_requests` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `request_type` ENUM('account', 'item') NOT NULL DEFAULT 'account',
  `reason` TEXT NOT NULL,
  `additional_info` TEXT NULL,
  `status` ENUM('pending', 'approved', 'rejected', 'archived') NOT NULL DEFAULT 'pending',
  `urgency` ENUM('critical', 'high', 'medium', 'low') NOT NULL DEFAULT 'low',
  `admin_notes` TEXT NULL,
  `processed_by_user_id` INT UNSIGNED NULL,
  `processed_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_deletion_requests_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_deletion_requests_processed_by` FOREIGN KEY (`processed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_deletion_requests_user_id` (`user_id`),
  INDEX `idx_deletion_requests_status` (`status`),
  INDEX `idx_deletion_requests_urgency` (`urgency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 27. TABLE: deletion_request_items
-- Specific items associated with an item-level deletion request
-- ============================================================================
CREATE TABLE `deletion_request_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `deletion_request_id` INT UNSIGNED NOT NULL,
  `item_type` VARCHAR(50) NOT NULL,
  `item_id` INT UNSIGNED NULL,
  `item_name` VARCHAR(150) NOT NULL,
  `category` VARCHAR(100) NULL,
  `date_added` DATE NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_deletion_items_request` FOREIGN KEY (`deletion_request_id`) REFERENCES `deletion_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_deletion_items_request_id` (`deletion_request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 28. TABLE: user_achievements
-- Badges and achievements earned by users
-- ============================================================================
CREATE TABLE `user_achievements` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `achievement_name` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) NULL,
  `icon` VARCHAR(50) NULL,
  `unlocked` BOOLEAN NOT NULL DEFAULT FALSE,
  `unlocked_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_achievements_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `uk_user_achievement` UNIQUE (`user_id`, `achievement_name`),
  INDEX `idx_user_achievements_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 29. TABLE: system_settings
-- Admin Panel global configuration options
-- ============================================================================
CREATE TABLE `system_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT NULL,
  `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
  `description` VARCHAR(255) NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `uk_system_settings_key` UNIQUE (`setting_key`),
  INDEX `idx_settings_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 30. TABLE: notifications
-- System and user notifications
-- ============================================================================
CREATE TABLE `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `type` VARCHAR(50) NOT NULL DEFAULT 'system',
  `category` VARCHAR(50) NOT NULL DEFAULT 'System',
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `link` VARCHAR(255) NULL,
  `read` BOOLEAN NOT NULL DEFAULT FALSE,
  `read_at` TIMESTAMP NULL,
  `data` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_notifications_user_read` (`user_id`, `read`),
  INDEX `idx_notifications_category` (`category`),
  INDEX `idx_notifications_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 31. TABLE: login_attempts
-- Tracks authentication attempts and security logs
-- ============================================================================
CREATE TABLE `login_attempts` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `success` BOOLEAN NOT NULL DEFAULT FALSE,
  `failure_reason` VARCHAR(255) NULL,
  `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_login_attempts_email` (`email`),
  INDEX `idx_login_attempts_ip_address` (`ip_address`),
  INDEX `idx_login_attempts_success` (`success`),
  INDEX `idx_login_attempts_attempted_at` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 32. TABLE: security_alerts
-- Triggered security alerts (e.g. failed login thresholds)
-- ============================================================================
CREATE TABLE `security_alerts` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `type` VARCHAR(50) NOT NULL DEFAULT 'failed_login_threshold',
  `email` VARCHAR(150) NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `attempts` INT NOT NULL DEFAULT 1,
  `message` TEXT NOT NULL,
  `is_read` BOOLEAN NOT NULL DEFAULT FALSE,
  `data` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_security_alerts_type` (`type`),
  INDEX `idx_security_alerts_email` (`email`),
  INDEX `idx_security_alerts_ip_address` (`ip_address`),
  INDEX `idx_security_alerts_is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 33. TABLE: blocked_ips
-- Firewall and IP ban table
-- ============================================================================
CREATE TABLE `blocked_ips` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ip_address` VARCHAR(45) NOT NULL,
  `reason` VARCHAR(255) NULL,
  `blocked_by` BIGINT UNSIGNED NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `blocked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `uk_blocked_ips_ip` UNIQUE (`ip_address`),
  INDEX `idx_blocked_ips_ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 34. TABLE: trips
-- User travel itineraries and trip plans
-- ============================================================================
CREATE TABLE `trips` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `destination` VARCHAR(150) NULL,
  `start_date` DATE NULL,
  `end_date` DATE NULL,
  `budget` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
  `travelers` INT UNSIGNED NOT NULL DEFAULT 1,
  `status` ENUM('planning', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'planning',
  `notes` TEXT NULL,
  `cover_image` LONGTEXT NULL,
  `is_public` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_trips_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_trips_user_id` (`user_id`),
  INDEX `idx_trips_status` (`status`),
  INDEX `idx_trips_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 35. TABLE: trip_itineraries
-- Day-by-day activities within a trip
-- ============================================================================
CREATE TABLE `trip_itineraries` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `trip_id` INT UNSIGNED NOT NULL,
  `place_id` INT UNSIGNED NULL,
  `day_number` INT UNSIGNED NOT NULL DEFAULT 1,
  `time_slot` VARCHAR(50) NULL,
  `activity` VARCHAR(255) NOT NULL,
  `estimated_cost` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `duration_minutes` INT UNSIGNED NULL,
  `notes` TEXT NULL,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_completed` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_trip_itineraries_trip` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_trip_itineraries_place` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_trip_itineraries_trip_id` (`trip_id`),
  INDEX `idx_trip_itineraries_place_id` (`place_id`),
  INDEX `idx_trip_itineraries_order` (`trip_id`, `day_number`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 36. TABLE: audit_logs
-- System activity and audit trail for user and admin actions
-- ============================================================================
CREATE TABLE `audit_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `user_name` VARCHAR(100) NULL,
  `user_role` VARCHAR(50) NULL,
  `action` VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(100) NULL,
  `entity_id` INT UNSIGNED NULL,
  `description` VARCHAR(255) NULL,
  `old_values` JSON NULL,
  `new_values` JSON NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_audit_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_audit_logs_user_id` (`user_id`),
  INDEX `idx_audit_logs_action` (`action`),
  INDEX `idx_audit_logs_created_at` (`created_at`),
  INDEX `idx_audit_logs_entity` (`entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 37. TABLE: ai_conversations
-- User AI Assistant chat sessions
-- ============================================================================
CREATE TABLE `ai_conversations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `session_id` VARCHAR(100) NOT NULL,
  `title` VARCHAR(150) NULL,
  `province` VARCHAR(100) NULL,
  `category` VARCHAR(100) NULL,
  `language` VARCHAR(10) NOT NULL DEFAULT 'en',
  `last_message_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `uk_ai_conversations_session` UNIQUE (`session_id`),
  CONSTRAINT `fk_ai_conversations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_ai_conversations_user_id` (`user_id`),
  INDEX `idx_ai_conversations_last_msg` (`last_message_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 38. TABLE: ai_messages
-- Messages within AI Assistant chat sessions
-- ============================================================================
CREATE TABLE `ai_messages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ai_conversation_id` INT UNSIGNED NOT NULL,
  `role` ENUM('user', 'assistant', 'system') NOT NULL DEFAULT 'user',
  `content` LONGTEXT NOT NULL,
  `metadata` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ai_messages_conversation` FOREIGN KEY (`ai_conversation_id`) REFERENCES `ai_conversations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_ai_messages_conversation_id` (`ai_conversation_id`),
  INDEX `idx_ai_messages_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 39. TABLE: push_subscriptions
-- WebPush device subscriptions for push notifications
-- ============================================================================
CREATE TABLE `push_subscriptions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `endpoint` TEXT NOT NULL,
  `public_key` TEXT NULL,
  `auth_token` TEXT NULL,
  `content_encoding` VARCHAR(30) NOT NULL DEFAULT 'aesgcm',
  `user_agent` VARCHAR(500) NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_push_subscriptions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_push_subscriptions_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 40. TABLE: user_notification_settings
-- User notification channel and preference settings
-- ============================================================================
CREATE TABLE `user_notification_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `push_enabled` BOOLEAN NOT NULL DEFAULT TRUE,
  `events_enabled` BOOLEAN NOT NULL DEFAULT TRUE,
  `messages_enabled` BOOLEAN NOT NULL DEFAULT TRUE,
  `system_enabled` BOOLEAN NOT NULL DEFAULT TRUE,
  `promotions_enabled` BOOLEAN NOT NULL DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `uk_user_notification_settings_user` UNIQUE (`user_id`),
  CONSTRAINT `fk_user_notification_settings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 41. TABLE: business_images
-- Photos associated with business listings
-- ============================================================================
CREATE TABLE `business_images` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `business_id` INT UNSIGNED NOT NULL,
  `image_url` LONGTEXT NOT NULL,
  `caption` VARCHAR(255) NULL,
  `is_cover` BOOLEAN NOT NULL DEFAULT FALSE,
  `display_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_business_images_business` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_business_images_business_id` (`business_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 42. TABLE: business_services
-- Services offered by business vendors
-- ============================================================================
CREATE TABLE `business_services` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `business_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `price` DECIMAL(10, 2) NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
  `duration_minutes` INT NULL,
  `is_available` BOOLEAN NOT NULL DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_business_services_business` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_business_services_business_id` (`business_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 43. TABLE: business_hours
-- Operating hours per day for business listings
-- ============================================================================
CREATE TABLE `business_hours` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `business_id` INT UNSIGNED NOT NULL,
  `day_of_week` ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NOT NULL,
  `open_time` TIME NULL,
  `close_time` TIME NULL,
  `is_closed` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_business_hours_business` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_business_hours` (`business_id`, `day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 44. TABLE: business_promotions
-- Promotional offers and discounts for businesses
-- ============================================================================
CREATE TABLE `business_promotions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `business_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `discount_percentage` DECIMAL(5, 2) NULL,
  `discount_amount` DECIMAL(10, 2) NULL,
  `promo_code` VARCHAR(50) NULL,
  `start_date` DATETIME NULL,
  `end_date` DATETIME NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `banner_url` LONGTEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_business_promotions_business` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_business_promotions_business_id` (`business_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;