-- AccountMirror v2.0 - TKÜV/BNetzA Audit & Auskunftsverfahren
-- UNENDLICHE SPEICHERUNG (Gesetzliche Pflicht)

-- Überwachungsmaßnahmen Audit-Log (KEINE AUTOMATISCHE LÖSCHUNG!)
CREATE TABLE IF NOT EXISTS `{PREFIX}mod_accountmirror_v2_audit_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mirrorid` INT(11) NOT NULL,
  `event_type` ENUM('created', 'activated', 'deactivated', 'modified', 'deleted', 'accessed') NOT NULL,
  `event_details` TEXT NULL COMMENT 'JSON mit Details',
  `performed_by` INT(11) NOT NULL COMMENT 'Admin-ID',
  `performed_by_email` VARCHAR(255) NULL,
  `ip_address` VARCHAR(45) NOT NULL COMMENT 'IP des Admins (TKÜV-Pflicht)',
  `user_agent` VARCHAR(500) NULL,
  `timestamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `legal_reference` VARCHAR(500) NULL COMMENT 'Rechtsgrundlage (z.B. Gerichtsbeschluss)',
  
  PRIMARY KEY (`id`),
  KEY `mirrorid` (`mirrorid`),
  KEY `event_type` (`event_type`),
  KEY `timestamp` (`timestamp`),
  KEY `performed_by` (`performed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Audit-Log für Überwachungsmaßnahmen (UNENDLICH gespeichert - TKÜV § 5)';

-- Jahres-Auswertungen (automatisch generiert)
CREATE TABLE IF NOT EXISTS `{PREFIX}mod_accountmirror_v2_yearly_reports` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `year` INT(4) NOT NULL,
  `report_type` ENUM('annual_summary', 'tkuev_compliance', 'bnetza_report') NOT NULL,
  `report_data` LONGTEXT NOT NULL COMMENT 'JSON mit Auswertung',
  `generated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `generated_by` INT(11) NULL COMMENT 'Admin-ID oder NULL (automatisch)',
  `file_path` VARCHAR(500) NULL COMMENT 'Pfad zur PDF/Export-Datei',
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `year_type` (`year`, `report_type`),
  KEY `year` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Jährliche Auswertungen (TKÜV/BNetzA)';

-- Auskunftsersuchen von Behörden (TKÜV § 113 TKG)
CREATE TABLE IF NOT EXISTS `{PREFIX}mod_accountmirror_v2_information_requests` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `request_number` VARCHAR(100) NOT NULL COMMENT 'Ersuchen-Nummer (von Behörde)',
  `authority` VARCHAR(255) NOT NULL COMMENT 'Anfragende Behörde',
  `authority_contact` VARCHAR(255) NULL,
  `request_type` ENUM('bestandsdaten', 'verkehrsdaten', 'inhaltsdaten', 'vollumfaenglich') NOT NULL,
  `legal_basis` VARCHAR(500) NOT NULL COMMENT 'Rechtsgrundlage',
  `file_number` VARCHAR(100) NULL COMMENT 'Aktenzeichen',
  
  -- Betroffene Accounts
  `target_userid` INT(11) NULL,
  `target_email` VARCHAR(255) NULL,
  `mirrorid` INT(11) NULL COMMENT 'Falls zu Spiegelung gehörig',
  
  -- Zeitraum der Anfrage
  `request_period_from` DATETIME NULL,
  `request_period_to` DATETIME NULL,
  
  -- Status & Bearbeitung
  `status` ENUM('pending', 'in_progress', 'completed', 'rejected', 'partially_completed') NOT NULL DEFAULT 'pending',
  `priority` ENUM('normal', 'urgent', 'immediate') NOT NULL DEFAULT 'normal',
  `received_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deadline` DATETIME NULL,
  `completed_at` DATETIME NULL,
  `processed_by` INT(11) NULL COMMENT 'Admin der bearbeitet',
  
  -- Antwort/Ergebnis
  `response_data` LONGTEXT NULL COMMENT 'JSON mit Auskunft',
  `response_file_path` VARCHAR(500) NULL COMMENT 'Pfad zu Export-Datei',
  `notes` TEXT NULL,
  
  -- Audit
  `created_by` INT(11) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_number` (`request_number`),
  KEY `authority` (`authority`),
  KEY `target_userid` (`target_userid`),
  KEY `mirrorid` (`mirrorid`),
  KEY `status` (`status`),
  KEY `received_at` (`received_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Auskunftsersuchen von Ermittlungsbehörden (TKÜV § 113 TKG)';

-- Auskunftsersuchen Audit-Log
CREATE TABLE IF NOT EXISTS `{PREFIX}mod_accountmirror_v2_information_requests_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` INT(11) NOT NULL,
  `action` VARCHAR(100) NOT NULL COMMENT 'z.B. created, status_changed, data_exported',
  `details` TEXT NULL,
  `performed_by` INT(11) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `timestamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Audit-Log für Auskunftsersuchen';

-- Event für jährliche Auswertung (läuft am 31.12. um 23:00)
DELIMITER //
CREATE EVENT IF NOT EXISTS `generate_accountmirror_yearly_report`
ON SCHEDULE EVERY 1 YEAR
STARTS CONCAT(YEAR(CURDATE()), '-12-31 23:00:00')
DO
BEGIN
  DECLARE current_year INT;
  SET current_year = YEAR(CURDATE());
  
  -- Jahres-Auswertung in Tabelle speichern
  INSERT INTO `{PREFIX}mod_accountmirror_v2_yearly_reports` 
  (`year`, `report_type`, `report_data`, `generated_by`)
  SELECT 
    current_year,
    'annual_summary',
    JSON_OBJECT(
      'total_mirrorings', (SELECT COUNT(*) FROM `{PREFIX}mod_accountmirror_v2` 
                           WHERE YEAR(created_at) = current_year),
      'active_mirrorings', (SELECT COUNT(*) FROM `{PREFIX}mod_accountmirror_v2` 
                            WHERE active = 1 AND YEAR(created_at) = current_year),
      'total_syncs', (SELECT COUNT(*) FROM `{PREFIX}mod_accountmirror_v2_sync_log` 
                      WHERE YEAR(synced_at) = current_year),
      'total_mails', (SELECT SUM(mail_count) FROM `{PREFIX}mod_accountmirror_v2` 
                      WHERE YEAR(created_at) = current_year),
      'total_audit_events', (SELECT COUNT(*) FROM `{PREFIX}mod_accountmirror_v2_audit_log` 
                             WHERE YEAR(timestamp) = current_year),
      'total_information_requests', (SELECT COUNT(*) FROM `{PREFIX}mod_accountmirror_v2_information_requests` 
                                     WHERE YEAR(received_at) = current_year),
      'generated_at', NOW()
    ),
    NULL
  ON DUPLICATE KEY UPDATE report_data = VALUES(report_data);
  
END//
DELIMITER ;

-- Indizes für Performance
CREATE INDEX idx_audit_log_year ON `{PREFIX}mod_accountmirror_v2_audit_log` (YEAR(`timestamp`));
CREATE INDEX idx_sync_log_year ON `{PREFIX}mod_accountmirror_v2_sync_log` (YEAR(`synced_at`));
CREATE INDEX idx_requests_year ON `{PREFIX}mod_accountmirror_v2_information_requests` (YEAR(`received_at`));

-- ⚠️ WICHTIG: Audit-Logs werden NIEMALS automatisch gelöscht (TKÜV-Pflicht!)
-- Nur manuelle Archivierung nach gesetzlichen Aufbewahrungsfristen erlaubt
