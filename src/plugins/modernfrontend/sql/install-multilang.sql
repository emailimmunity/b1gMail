-- ============================================
-- ModernFrontend CMS - Multi-Domain & Multi-Lingual System
-- Installation SQL
-- ============================================

-- 1. DOMAINS TABELLE
CREATE TABLE IF NOT EXISTS `bm60_mf_domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL,
  `design_id` int(11) DEFAULT NULL,
  `default_language_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive','maintenance') DEFAULT 'active',
  `ssl_enabled` tinyint(1) DEFAULT 1,
  `custom_config` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain` (`domain`),
  KEY `status` (`status`),
  KEY `design_id` (`design_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. DESIGNS TABELLE
CREATE TABLE IF NOT EXISTS `bm60_mf_designs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `template_path` varchar(255) NOT NULL,
  `primary_color` varchar(7) DEFAULT '#76B82A',
  `secondary_color` varchar(7) DEFAULT '#333333',
  `logo_path` varchar(255) DEFAULT NULL,
  `css_file` varchar(255) DEFAULT NULL,
  `preview_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `settings` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. LANGUAGES TABELLE
CREATE TABLE IF NOT EXISTS `bm60_mf_languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(5) NOT NULL,
  `name` varchar(100) NOT NULL,
  `native_name` varchar(100) NOT NULL,
  `flag_icon` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `translation_provider` enum('google','deepl','manual') DEFAULT 'google',
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TRANSLATIONS TABELLE
CREATE TABLE IF NOT EXISTS `bm60_mf_translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL,
  `translation_key` varchar(255) NOT NULL,
  `translation_value` text NOT NULL,
  `section` varchar(100) DEFAULT 'general',
  `source_text` text,
  `auto_translated` tinyint(1) DEFAULT 0,
  `provider` enum('google','deepl','manual') DEFAULT 'manual',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lang_key` (`language_id`,`translation_key`,`section`),
  KEY `language_id` (`language_id`),
  KEY `section` (`section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. DOMAIN_LANGUAGES TABELLE (Mehrsprachigkeit pro Domain)
CREATE TABLE IF NOT EXISTS `bm60_mf_domain_languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `is_enabled` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain_lang` (`domain_id`,`language_id`),
  KEY `domain_id` (`domain_id`),
  KEY `language_id` (`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. API_CREDENTIALS TABELLE
CREATE TABLE IF NOT EXISTS `bm60_mf_api_credentials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider` enum('google_translate','deepl') NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `daily_quota` int(11) DEFAULT 500000,
  `usage_count` int(11) DEFAULT 0,
  `last_reset` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `provider` (`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DEFAULT DATA: 52+ Sprachen
-- ============================================

INSERT INTO `bm60_mf_languages` (`code`, `name`, `native_name`, `flag_icon`, `is_active`, `sort_order`) VALUES
('de', 'German', 'Deutsch', '🇩🇪', 1, 1),
('en', 'English', 'English', '🇬🇧', 1, 2),
('es', 'Spanish', 'Español', '🇪🇸', 1, 3),
('fr', 'French', 'Français', '🇫🇷', 1, 4),
('it', 'Italian', 'Italiano', '🇮🇹', 1, 5),
('pt', 'Portuguese', 'Português', '🇵🇹', 1, 6),
('nl', 'Dutch', 'Nederlands', '🇳🇱', 1, 7),
('pl', 'Polish', 'Polski', '🇵🇱', 1, 8),
('ru', 'Russian', 'Русский', '🇷🇺', 1, 9),
('ja', 'Japanese', '日本語', '🇯🇵', 1, 10),
('zh', 'Chinese', '中文', '🇨🇳', 1, 11),
('ko', 'Korean', '한국어', '🇰🇷', 1, 12),
('ar', 'Arabic', 'العربية', '🇸🇦', 1, 13),
('hi', 'Hindi', 'हिन्दी', '🇮🇳', 1, 14),
('tr', 'Turkish', 'Türkçe', '🇹🇷', 1, 15),
('sv', 'Swedish', 'Svenska', '🇸🇪', 1, 16),
('no', 'Norwegian', 'Norsk', '🇳🇴', 1, 17),
('da', 'Danish', 'Dansk', '🇩🇰', 1, 18),
('fi', 'Finnish', 'Suomi', '🇫🇮', 1, 19),
('cs', 'Czech', 'Čeština', '🇨🇿', 1, 20),
('sk', 'Slovak', 'Slovenčina', '🇸🇰', 1, 21),
('hu', 'Hungarian', 'Magyar', '🇭🇺', 1, 22),
('ro', 'Romanian', 'Română', '🇷🇴', 1, 23),
('bg', 'Bulgarian', 'Български', '🇧🇬', 1, 24),
('hr', 'Croatian', 'Hrvatski', '🇭🇷', 1, 25),
('sr', 'Serbian', 'Српски', '🇷🇸', 1, 26),
('uk', 'Ukrainian', 'Українська', '🇺🇦', 1, 27),
('el', 'Greek', 'Ελληνικά', '🇬🇷', 1, 28),
('he', 'Hebrew', 'עברית', '🇮🇱', 1, 29),
('th', 'Thai', 'ไทย', '🇹🇭', 1, 30),
('vi', 'Vietnamese', 'Tiếng Việt', '🇻🇳', 1, 31),
('id', 'Indonesian', 'Bahasa Indonesia', '🇮🇩', 1, 32),
('ms', 'Malay', 'Bahasa Melayu', '🇲🇾', 1, 33),
('tl', 'Filipino', 'Filipino', '🇵🇭', 1, 34),
('bn', 'Bengali', 'বাংলা', '🇧🇩', 1, 35),
('ur', 'Urdu', 'اردو', '🇵🇰', 1, 36),
('fa', 'Persian', 'فارسی', '🇮🇷', 1, 37),
('sw', 'Swahili', 'Kiswahili', '🇰🇪', 1, 38),
('af', 'Afrikaans', 'Afrikaans', '🇿🇦', 1, 39),
('sq', 'Albanian', 'Shqip', '🇦🇱', 1, 40),
('et', 'Estonian', 'Eesti', '🇪🇪', 1, 41),
('lv', 'Latvian', 'Latviešu', '🇱🇻', 1, 42),
('lt', 'Lithuanian', 'Lietuvių', '🇱🇹', 1, 43),
('sl', 'Slovenian', 'Slovenščina', '🇸🇮', 1, 44),
('is', 'Icelandic', 'Íslenska', '🇮🇸', 1, 45),
('ga', 'Irish', 'Gaeilge', '🇮🇪', 1, 46),
('ca', 'Catalan', 'Català', '🇪🇸', 1, 47),
('eu', 'Basque', 'Euskara', '🇪🇸', 1, 48),
('gl', 'Galician', 'Galego', '🇪🇸', 1, 49),
('cy', 'Welsh', 'Cymraeg', '🏴󐁧󐁢󐁷󐁬󐁳󐁿', 1, 50),
('mt', 'Maltese', 'Malti', '🇲🇹', 1, 51),
('lb', 'Luxembourgish', 'Lëtzebuergesch', '🇱🇺', 1, 52);

-- ============================================
-- DEFAULT DATA: Standard-Design
-- ============================================

INSERT INTO `bm60_mf_designs` (`name`, `description`, `template_path`, `primary_color`, `secondary_color`, `is_active`) VALUES
('aikQ Default', 'Standard aikQ Design', 'designs/aikq-default/', '#76B82A', '#333333', 1);

-- ============================================
-- BEISPIEL-DOMAIN (localhost)
-- ============================================

INSERT INTO `bm60_mf_domains` (`domain`, `design_id`, `default_language_id`, `status`) VALUES
('localhost', 1, 1, 'active');

INSERT INTO `bm60_mf_domain_languages` (`domain_id`, `language_id`, `is_default`, `is_enabled`) VALUES
(1, 1, 1, 1), -- Deutsch (default)
(1, 2, 0, 1); -- English
