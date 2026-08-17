-- ============================================================================
-- Smart Tourism Information System - Database Schema
-- Target Engine: MySQL 8.0+
-- File: database.sql
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `tourism_db` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `tourism_db`;

-- Set SQL settings for standard compliance
SET FOREIGN_KEY_CHECKS = 0;

-- Drop tables if they already exist to allow clean re-import
DROP TABLE IF EXISTS `system_settings`;
DROP TABLE IF EXISTS `user_achievements`;
DROP TABLE IF EXISTS `deletion_request_items`;
DROP TABLE IF EXISTS `deletion_requests`;
DROP TABLE IF EXISTS `chat_messages`;
DROP TABLE IF EXISTS `chats`;
DROP TABLE IF EXISTS `gallery_media_tags`;
DROP TABLE IF EXISTS `gallery_media`;
DROP TABLE IF EXISTS `favorites`;
DROP TABLE IF EXISTS `review_images`;
DROP TABLE IF EXISTS `review_replies`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `event_tags`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `places`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `provinces`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- 1. TABLE: users
-- Core entity storing administrators, guides, editors, and registered tourists
-- ============================================================================
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NULL,
  `password_hash` VARCHAR(255) NULL,
  `avatar` VARCHAR(255) NULL,
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
  INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. TABLE: provinces
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
-- 3. TABLE: categories
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
-- 4. TABLE: places
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
-- 5. TABLE: events
-- Festivals, marathons, cultural shows, and tourism events
-- ============================================================================
CREATE TABLE `events` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `location` VARCHAR(255) NOT NULL,
  `place_id` INT UNSIGNED NULL,
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
  CONSTRAINT `fk_events_province` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_events_status` (`status`),
  INDEX `idx_events_start_date` (`start_date`),
  INDEX `idx_events_featured` (`featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. TABLE: event_tags
-- Tags associated with tourism events (1-to-Many normalized relationship)
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
-- 7. TABLE: reviews
-- Ratings and text reviews submitted by users for specific places
-- ============================================================================
CREATE TABLE `reviews` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `place_id` INT UNSIGNED NOT NULL,
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
  INDEX `idx_reviews_user_id` (`user_id`),
  INDEX `idx_reviews_place_id` (`place_id`),
  INDEX `idx_reviews_status` (`status`),
  INDEX `idx_reviews_rating` (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. TABLE: review_replies
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
-- 9. TABLE: review_images
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
-- 10. TABLE: favorites
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
-- 11. TABLE: gallery_media
-- Media library storing images and promotional videos
-- ============================================================================
CREATE TABLE `gallery_media` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `type` ENUM('image', 'video') NOT NULL DEFAULT 'image',
  `url` VARCHAR(255) NOT NULL,
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
-- 12. TABLE: gallery_media_tags
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
-- 13. TABLE: chats
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
-- 14. TABLE: chat_messages
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
-- 15. TABLE: deletion_requests
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
-- 16. TABLE: deletion_request_items
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
-- 17. TABLE: user_achievements
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
-- 18. TABLE: system_settings
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