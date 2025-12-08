-- ============================================
-- ModernFrontend CMS Plugin - Database Schema
-- Version: 1.0.0
-- ============================================

-- Content Management
CREATE TABLE IF NOT EXISTS `bm60_mf_content` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `section` VARCHAR(100) NOT NULL COMMENT 'Section identifier (hero, features, etc.)',
  `key` VARCHAR(100) NOT NULL COMMENT 'Content key (title, subtitle, etc.)',
  `content_de` TEXT NULL COMMENT 'German content',
  `content_en` TEXT NULL COMMENT 'English content',
  `content_type` ENUM('text', 'html', 'markdown', 'json') DEFAULT 'text',
  `meta_data` TEXT NULL COMMENT 'JSON metadata',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` INT(11) NULL,
  `updated_by` INT(11) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `section_key` (`section`, `key`),
  INDEX `idx_section` (`section`),
  INDEX `idx_content_type` (`content_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Content storage for all editable texts';

-- Media Library
CREATE TABLE IF NOT EXISTS `bm60_mf_media` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `filename` VARCHAR(255) NOT NULL,
  `original_filename` VARCHAR(255) NOT NULL,
  `filepath` VARCHAR(500) NOT NULL,
  `mime_type` VARCHAR(100) NOT NULL,
  `file_size` INT(11) NOT NULL COMMENT 'Size in bytes',
  `width` INT(11) NULL COMMENT 'Image width',
  `height` INT(11) NULL COMMENT 'Image height',
  `alt_text` VARCHAR(255) NULL,
  `title` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `folder_id` INT(11) NULL COMMENT 'Organization folder',
  `tags` TEXT NULL COMMENT 'JSON array of tags',
  `usage_count` INT(11) DEFAULT 0,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `uploaded_by` INT(11) NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_mime_type` (`mime_type`),
  INDEX `idx_folder` (`folder_id`),
  INDEX `idx_uploaded_at` (`uploaded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Media library for images, videos, files';

-- Media Folders
CREATE TABLE IF NOT EXISTS `bm60_mf_media_folders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `parent_id` INT(11) NULL,
  `sort_order` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_parent` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Folder structure for media organization';

-- Custom Pages
CREATE TABLE IF NOT EXISTS `bm60_mf_pages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(100) NOT NULL COMMENT 'URL slug',
  `title_de` VARCHAR(255) NOT NULL,
  `title_en` VARCHAR(255) NULL,
  `meta_description_de` TEXT NULL,
  `meta_description_en` TEXT NULL,
  `meta_keywords` TEXT NULL COMMENT 'JSON array',
  `template` VARCHAR(100) DEFAULT 'default',
  `status` ENUM('draft', 'published', 'archived') DEFAULT 'draft',
  `published_at` TIMESTAMP NULL,
  `sort_order` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  INDEX `idx_status` (`status`),
  INDEX `idx_published` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Custom pages created via page builder';

-- Page Builder Sections
CREATE TABLE IF NOT EXISTS `bm60_mf_sections` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `page_id` INT(11) NOT NULL,
  `section_type` VARCHAR(50) NOT NULL COMMENT 'hero, features, testimonials, etc.',
  `content` TEXT NOT NULL COMMENT 'JSON configuration',
  `sort_order` INT(11) DEFAULT 0,
  `is_visible` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_page` (`page_id`),
  INDEX `idx_type` (`section_type`),
  INDEX `idx_sort` (`sort_order`),
  FOREIGN KEY (`page_id`) REFERENCES `bm60_mf_pages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Page builder sections';

-- Theme Settings
CREATE TABLE IF NOT EXISTS `bm60_mf_theme` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT NOT NULL,
  `setting_type` VARCHAR(50) DEFAULT 'text' COMMENT 'text, color, json, image',
  `setting_group` VARCHAR(50) NULL COMMENT 'colors, typography, layout',
  `description` VARCHAR(255) NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  INDEX `idx_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Theme customization settings';

-- Analytics
CREATE TABLE IF NOT EXISTS `bm60_mf_analytics` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `event_type` VARCHAR(50) NOT NULL COMMENT 'pageview, click, conversion, etc.',
  `event_data` TEXT NULL COMMENT 'JSON event data',
  `page_url` VARCHAR(500) NULL,
  `referrer` VARCHAR(500) NULL,
  `user_agent` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_id` INT(11) NULL,
  `session_id` VARCHAR(100) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_event_type` (`event_type`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Analytics event tracking';

-- A/B Testing
CREATE TABLE IF NOT EXISTS `bm60_mf_ab_tests` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `test_name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `page_id` INT(11) NULL,
  `variant_a` TEXT NOT NULL COMMENT 'JSON config',
  `variant_b` TEXT NOT NULL COMMENT 'JSON config',
  `traffic_split` INT(11) DEFAULT 50 COMMENT 'Percentage for B',
  `status` ENUM('draft', 'running', 'paused', 'completed') DEFAULT 'draft',
  `start_date` TIMESTAMP NULL,
  `end_date` TIMESTAMP NULL,
  `winner` ENUM('a', 'b', 'none') NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_page` (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='A/B testing configurations';

-- A/B Test Results
CREATE TABLE IF NOT EXISTS `bm60_mf_ab_results` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `test_id` INT(11) NOT NULL,
  `variant` ENUM('a', 'b') NOT NULL,
  `user_id` INT(11) NULL,
  `session_id` VARCHAR(100) NOT NULL,
  `converted` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_test` (`test_id`),
  INDEX `idx_variant` (`variant`),
  INDEX `idx_converted` (`converted`),
  FOREIGN KEY (`test_id`) REFERENCES `bm60_mf_ab_tests`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='A/B test participation and results';

-- Email Templates
CREATE TABLE IF NOT EXISTS `bm60_mf_email_templates` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `template_name` VARCHAR(100) NOT NULL,
  `template_key` VARCHAR(100) NOT NULL COMMENT 'Unique identifier',
  `subject_de` VARCHAR(255) NOT NULL,
  `subject_en` VARCHAR(255) NULL,
  `body_html_de` TEXT NOT NULL,
  `body_html_en` TEXT NULL,
  `body_text_de` TEXT NULL,
  `body_text_en` TEXT NULL,
  `variables` TEXT NULL COMMENT 'JSON array of available variables',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_key` (`template_key`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customizable email templates';

-- Contact Forms
CREATE TABLE IF NOT EXISTS `bm60_mf_contact_forms` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `form_name` VARCHAR(100) NOT NULL,
  `form_key` VARCHAR(100) NOT NULL,
  `fields` TEXT NOT NULL COMMENT 'JSON field configuration',
  `notification_email` VARCHAR(255) NULL,
  `success_message_de` TEXT NULL,
  `success_message_en` TEXT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `form_key` (`form_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contact form configurations';

-- Contact Form Submissions
CREATE TABLE IF NOT EXISTS `bm60_mf_contact_submissions` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `form_id` INT(11) NOT NULL,
  `form_data` TEXT NOT NULL COMMENT 'JSON submission data',
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `status` ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_form` (`form_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`),
  FOREIGN KEY (`form_id`) REFERENCES `bm60_mf_contact_forms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contact form submissions';

-- Plugin Settings
CREATE TABLE IF NOT EXISTS `bm60_mf_settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT NOT NULL,
  `setting_type` VARCHAR(50) DEFAULT 'text',
  `is_public` TINYINT(1) DEFAULT 0 COMMENT 'Available in frontend',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='General plugin settings';

-- ============================================
-- Default Data
-- ============================================

-- Default Theme Settings
INSERT INTO `bm60_mf_theme` (`setting_key`, `setting_value`, `setting_type`, `setting_group`) VALUES
('primary_color', '#76B82A', 'color', 'colors'),
('primary_dark', '#5D9321', 'color', 'colors'),
('primary_light', '#8FC744', 'color', 'colors'),
('secondary_color', '#2C3E50', 'color', 'colors'),
('accent_color', '#3498DB', 'color', 'colors'),
('font_primary', 'Inter', 'text', 'typography'),
('font_heading', 'Poppins', 'text', 'typography'),
('logo_url', '', 'image', 'branding'),
('favicon_url', '', 'image', 'branding'),
('site_title', 'aikQ Mail', 'text', 'branding');

-- Default Plugin Settings
INSERT INTO `bm60_mf_settings` (`setting_key`, `setting_value`, `setting_type`, `is_public`) VALUES
('plugin_enabled', '1', 'boolean', 0),
('default_language', 'de', 'text', 1),
('maintenance_mode', '0', 'boolean', 1),
('analytics_enabled', '1', 'boolean', 0),
('ab_testing_enabled', '1', 'boolean', 0),
('cache_enabled', '1', 'boolean', 0),
('cache_ttl', '3600', 'number', 0);

-- Default Landing Page
INSERT INTO `bm60_mf_pages` (`slug`, `title_de`, `title_en`, `meta_description_de`, `template`, `status`, `published_at`) VALUES
('home', 'Startseite', 'Home', 'Professionelles E-Mail-Hosting mit aikQ', 'landing', 'published', NOW());

-- Default Hero Section
INSERT INTO `bm60_mf_content` (`section`, `key`, `content_de`, `content_en`, `content_type`) VALUES
('hero', 'title', 'Professionelles E-Mail-Hosting', 'Professional Email Hosting', 'text'),
('hero', 'subtitle', 'Sicher, schnell und zuverlässig', 'Secure, fast and reliable', 'text'),
('hero', 'cta_text', 'Kostenlos registrieren', 'Sign up free', 'text'),
('hero', 'cta_url', '/register', '/register', 'text');

-- Default Contact Form
INSERT INTO `bm60_mf_contact_forms` (`form_name`, `form_key`, `fields`, `notification_email`, `success_message_de`, `status`) VALUES
('Kontaktformular', 'contact_default', 
'[{"name":"name","type":"text","required":true,"label_de":"Name","label_en":"Name"},{"name":"email","type":"email","required":true,"label_de":"E-Mail","label_en":"Email"},{"name":"message","type":"textarea","required":true,"label_de":"Nachricht","label_en":"Message"}]',
'', 'Vielen Dank für Ihre Nachricht!', 'active');

-- ============================================
-- Indexes for Performance
-- ============================================

-- Additional composite indexes for common queries
ALTER TABLE `bm60_mf_analytics` ADD INDEX `idx_event_date` (`event_type`, `created_at`);
ALTER TABLE `bm60_mf_content` ADD INDEX `idx_section_type` (`section`, `content_type`);
ALTER TABLE `bm60_mf_media` ADD INDEX `idx_folder_type` (`folder_id`, `mime_type`);

-- ============================================
-- Installation Complete
-- ============================================
