-- AccountMirror v2.0 - Vollständige Spiegelung
-- TKÜV & BNetzA konform

-- Haupt-Tabelle (erweitert)
CREATE TABLE IF NOT EXISTS `{PREFIX}mod_accountmirror_v2` (
  `mirrorid` INT(11) NOT NULL AUTO_INCREMENT,
  `userid` INT(11) NOT NULL COMMENT 'Quell-Account',
  `mirror_to` INT(11) NOT NULL COMMENT 'Ziel-Account',
  `begin` INT(14) NOT NULL COMMENT 'Start-Timestamp',
  `end` INT(14) NOT NULL COMMENT '0 = unbegrenzt',
  `reason` VARCHAR(255) NOT NULL COMMENT 'Grund (z.B. Gerichtsbeschluss)',
  `authority` VARCHAR(255) NULL COMMENT 'Behörde (z.B. StA München)',
  `file_number` VARCHAR(100) NULL COMMENT 'Aktenzeichen',
  
  -- Erweiterte Optionen
  `mirror_mode` ENUM('live', 'snapshot', 'backup') NOT NULL DEFAULT 'live' COMMENT 'Modus der Spiegelung',
  `bidirectional` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Bidirektionale Sync',
  `include_existing` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Bestehende Daten auch spiegeln',
  
  -- Was wird gespiegelt?
  `mirror_emails` TINYINT(1) NOT NULL DEFAULT 1,
  `mirror_folders` TINYINT(1) NOT NULL DEFAULT 1,
  `mirror_flags` TINYINT(1) NOT NULL DEFAULT 1,
  `mirror_webdisk` TINYINT(1) NOT NULL DEFAULT 0,
  `mirror_calendar` TINYINT(1) NOT NULL DEFAULT 0,
  `mirror_contacts` TINYINT(1) NOT NULL DEFAULT 0,
  `mirror_deletions` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Löschungen spiegeln (normalerweise NEIN)',
  
  -- Statistiken
  `mail_count` INT(11) NOT NULL DEFAULT 0,
  `webdisk_file_count` INT(11) NOT NULL DEFAULT 0,
  `calendar_event_count` INT(11) NOT NULL DEFAULT 0,
  `contact_count` INT(11) NOT NULL DEFAULT 0,
  `error_count` INT(11) NOT NULL DEFAULT 0,
  `last_sync` DATETIME NULL,
  
  -- Audit
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` INT(11) NOT NULL COMMENT 'Admin der Spiegelung anlegt',
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  
  PRIMARY KEY (`mirrorid`),
  KEY `userid` (`userid`),
  KEY `mirror_to` (`mirror_to`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='AccountMirror v2.0 - Vollständige Spiegelung (TKÜV-konform)';

-- Synchronisations-Log (detailliert)
CREATE TABLE IF NOT EXISTS `{PREFIX}mod_accountmirror_v2_sync_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mirrorid` INT(11) NOT NULL,
  `sync_type` ENUM('email', 'webdisk', 'calendar', 'contact', 'folder') NOT NULL,
  `source_id` INT(11) NOT NULL COMMENT 'ID des Quell-Objekts',
  `target_id` INT(11) NULL COMMENT 'ID des Ziel-Objekts',
  `action` ENUM('create', 'update', 'delete', 'move') NOT NULL,
  `status` ENUM('success', 'error', 'skipped') NOT NULL,
  `error_message` TEXT NULL,
  `synced_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` VARCHAR(45) NULL COMMENT 'IP bei Sync (TKÜV)',
  
  PRIMARY KEY (`id`),
  KEY `mirrorid` (`mirrorid`),
  KEY `sync_type` (`sync_type`),
  KEY `synced_at` (`synced_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Sync-Log für AccountMirror v2.0';

-- Folder-Mapping (Ordnerstruktur-Zuordnung)
CREATE TABLE IF NOT EXISTS `{PREFIX}mod_accountmirror_v2_folder_map` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `mirrorid` INT(11) NOT NULL,
  `source_folder_id` INT(11) NOT NULL,
  `source_folder_name` VARCHAR(255) NOT NULL,
  `target_folder_id` INT(11) NOT NULL,
  `target_folder_name` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `mirror_source_folder` (`mirrorid`, `source_folder_id`),
  KEY `mirrorid` (`mirrorid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Folder-Zuordnung für Spiegelung';

-- E-Mail-Mapping (welche Mail wurde wohin gespiegelt)
CREATE TABLE IF NOT EXISTS `{PREFIX}mod_accountmirror_v2_mail_map` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mirrorid` INT(11) NOT NULL,
  `source_mail_id` INT(11) NOT NULL,
  `target_mail_id` INT(11) NOT NULL,
  `source_folder_id` INT(11) NOT NULL,
  `target_folder_id` INT(11) NOT NULL,
  `flags_synced` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `mirror_source_mail` (`mirrorid`, `source_mail_id`),
  KEY `mirrorid` (`mirrorid`),
  KEY `target_mail_id` (`target_mail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Mail-Zuordnung für bidirektionale Sync';

-- Webdisk-Mapping
CREATE TABLE IF NOT EXISTS `{PREFIX}mod_accountmirror_v2_webdisk_map` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mirrorid` INT(11) NOT NULL,
  `source_file_id` INT(11) NOT NULL,
  `target_file_id` INT(11) NOT NULL,
  `source_path` VARCHAR(500) NOT NULL,
  `target_path` VARCHAR(500) NOT NULL,
  `file_hash` VARCHAR(64) NULL COMMENT 'SHA256 für Duplikat-Erkennung',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `mirror_source_file` (`mirrorid`, `source_file_id`),
  KEY `mirrorid` (`mirrorid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Webdisk-Zuordnung';

-- Kalender-Mapping
CREATE TABLE IF NOT EXISTS `{PREFIX}mod_accountmirror_v2_calendar_map` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mirrorid` INT(11) NOT NULL,
  `source_event_id` INT(11) NOT NULL,
  `target_event_id` INT(11) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `mirror_source_event` (`mirrorid`, `source_event_id`),
  KEY `mirrorid` (`mirrorid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Kalender-Zuordnung';

-- Kontakte-Mapping
CREATE TABLE IF NOT EXISTS `{PREFIX}mod_accountmirror_v2_contact_map` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mirrorid` INT(11) NOT NULL,
  `source_contact_id` INT(11) NOT NULL,
  `target_contact_id` INT(11) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `mirror_source_contact` (`mirrorid`, `source_contact_id`),
  KEY `mirrorid` (`mirrorid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Kontakte-Zuordnung';

-- Automatische Cleanup (Logs älter als 90 Tage)
DELIMITER //
CREATE EVENT IF NOT EXISTS `cleanup_accountmirror_v2_logs`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
  DELETE FROM `{PREFIX}mod_accountmirror_v2_sync_log`
  WHERE `synced_at` < DATE_SUB(NOW(), INTERVAL 90 DAY);
END//
DELIMITER ;
