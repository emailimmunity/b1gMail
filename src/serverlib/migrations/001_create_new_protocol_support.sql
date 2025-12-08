-- ============================================================================
-- MIGRATION 001: Neue Protokoll-Unterstützung
-- ============================================================================
-- Datum: 31. Oktober 2025
-- Beschreibung: Erweitert b1gMail um Cyrus, Grommunio, SFTPGo, Postfix
-- Autor: Automatische Migration
-- ============================================================================

-- ============================================================================
-- TEIL 1: GRUPPEN-SYSTEM ERWEITERN
-- ============================================================================

-- 1.1 Neue Protokoll-Spalten zu bm60_gruppen hinzufügen
ALTER TABLE bm60_gruppen
    ADD COLUMN cyrus ENUM('yes','no') DEFAULT 'no' COMMENT 'Cyrus IMAP/POP3/JMAP aktiviert' AFTER smtp,
    ADD COLUMN grommunio ENUM('yes','no') DEFAULT 'no' COMMENT 'Grommunio MAPI/EWS/EAS aktiviert' AFTER cyrus,
    ADD COLUMN sftpgo ENUM('yes','no') DEFAULT 'no' COMMENT 'SFTPGo SFTP/S3/WebDAV aktiviert' AFTER grommunio,
    ADD COLUMN postfix ENUM('yes','no') DEFAULT 'yes' COMMENT 'Postfix SMTP Gateway aktiviert' AFTER sftpgo;

-- 1.2 Daten-Migration: Alte Flags zu neuen Flags
-- User mit IMAP → Cyrus aktivieren
UPDATE bm60_gruppen 
SET cyrus = 'yes' 
WHERE imap = 'yes';

-- User mit SMTP → Postfix aktivieren (Default ist schon 'yes')

-- 1.3 Alte Spalten umbenennen (NICHT löschen für Rollback!)
ALTER TABLE bm60_gruppen
    CHANGE pop3 pop3_legacy ENUM('yes','no') DEFAULT 'no' COMMENT 'LEGACY - Wird nicht mehr verwendet',
    CHANGE imap imap_legacy ENUM('yes','no') DEFAULT 'no' COMMENT 'LEGACY - Wird nicht mehr verwendet',
    CHANGE smtp smtp_legacy ENUM('yes','no') DEFAULT 'yes' COMMENT 'LEGACY - Wird nicht mehr verwendet';

-- ============================================================================
-- TEIL 2: STATISTIKEN-TABELLEN ERSTELLEN
-- ============================================================================

-- 2.1 Cyrus Statistiken
CREATE TABLE IF NOT EXISTS bm60_cyrus_stats (
    id INT(11) NOT NULL AUTO_INCREMENT,
    date DATE NOT NULL,
    imap_connections INT(11) DEFAULT 0 COMMENT 'IMAP Verbindungen',
    imap_auth_success INT(11) DEFAULT 0 COMMENT 'IMAP erfolgreiche Logins',
    imap_auth_failed INT(11) DEFAULT 0 COMMENT 'IMAP fehlgeschlagene Logins',
    pop3_connections INT(11) DEFAULT 0 COMMENT 'POP3 Verbindungen',
    pop3_auth_success INT(11) DEFAULT 0 COMMENT 'POP3 erfolgreiche Logins',
    pop3_auth_failed INT(11) DEFAULT 0 COMMENT 'POP3 fehlgeschlagene Logins',
    jmap_requests INT(11) DEFAULT 0 COMMENT 'JMAP API Requests',
    jmap_errors INT(11) DEFAULT 0 COMMENT 'JMAP Fehler',
    total_mailboxes INT(11) DEFAULT 0 COMMENT 'Anzahl Mailboxen',
    total_messages BIGINT(20) DEFAULT 0 COMMENT 'Anzahl Nachrichten',
    storage_used_mb BIGINT(20) DEFAULT 0 COMMENT 'Speicher belegt (MB)',
    traffic_in_mb BIGINT(20) DEFAULT 0 COMMENT 'Traffic eingehend (MB)',
    traffic_out_mb BIGINT(20) DEFAULT 0 COMMENT 'Traffic ausgehend (MB)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY date_idx (date),
    KEY created_idx (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Cyrus IMAP/POP3/JMAP Statistiken';

-- 2.2 Grommunio Statistiken
CREATE TABLE IF NOT EXISTS bm60_grommunio_stats (
    id INT(11) NOT NULL AUTO_INCREMENT,
    date DATE NOT NULL,
    mapi_connections INT(11) DEFAULT 0 COMMENT 'MAPI Verbindungen',
    mapi_requests INT(11) DEFAULT 0 COMMENT 'MAPI Requests',
    ews_requests INT(11) DEFAULT 0 COMMENT 'EWS Requests',
    ews_errors INT(11) DEFAULT 0 COMMENT 'EWS Fehler',
    eas_sync_requests INT(11) DEFAULT 0 COMMENT 'ActiveSync Sync Requests',
    eas_devices INT(11) DEFAULT 0 COMMENT 'ActiveSync Geräte',
    active_users INT(11) DEFAULT 0 COMMENT 'Aktive User',
    total_domains INT(11) DEFAULT 0 COMMENT 'Anzahl Domains',
    api_calls INT(11) DEFAULT 0 COMMENT 'Admin API Calls',
    sync_operations INT(11) DEFAULT 0 COMMENT 'Sync-Operationen (b1gMail→Grommunio)',
    sync_errors INT(11) DEFAULT 0 COMMENT 'Sync-Fehler',
    storage_used_mb BIGINT(20) DEFAULT 0 COMMENT 'Speicher belegt (MB)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY date_idx (date),
    KEY created_idx (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Grommunio MAPI/EWS/EAS Statistiken';

-- 2.3 SFTPGo Statistiken
CREATE TABLE IF NOT EXISTS bm60_sftpgo_stats (
    id INT(11) NOT NULL AUTO_INCREMENT,
    date DATE NOT NULL,
    sftp_connections INT(11) DEFAULT 0 COMMENT 'SFTP Verbindungen',
    sftp_auth_failed INT(11) DEFAULT 0 COMMENT 'SFTP fehlgeschlagene Logins',
    ftps_connections INT(11) DEFAULT 0 COMMENT 'FTPS Verbindungen',
    ftps_auth_failed INT(11) DEFAULT 0 COMMENT 'FTPS fehlgeschlagene Logins',
    s3_operations INT(11) DEFAULT 0 COMMENT 'S3 API Operationen',
    s3_errors INT(11) DEFAULT 0 COMMENT 'S3 Fehler',
    webdav_requests INT(11) DEFAULT 0 COMMENT 'WebDAV Requests',
    webdav_errors INT(11) DEFAULT 0 COMMENT 'WebDAV Fehler',
    uploads_count INT(11) DEFAULT 0 COMMENT 'Anzahl Uploads',
    downloads_count INT(11) DEFAULT 0 COMMENT 'Anzahl Downloads',
    uploads_mb BIGINT(20) DEFAULT 0 COMMENT 'Upload-Volumen (MB)',
    downloads_mb BIGINT(20) DEFAULT 0 COMMENT 'Download-Volumen (MB)',
    storage_used_mb BIGINT(20) DEFAULT 0 COMMENT 'Speicher belegt (MB)',
    active_users INT(11) DEFAULT 0 COMMENT 'Aktive User',
    quota_exceeded INT(11) DEFAULT 0 COMMENT 'Quota-Überschreitungen',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY date_idx (date),
    KEY created_idx (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SFTPGo SFTP/FTPS/S3/WebDAV Statistiken';

-- 2.4 Postfix Statistiken
CREATE TABLE IF NOT EXISTS bm60_postfix_stats (
    id INT(11) NOT NULL AUTO_INCREMENT,
    date DATE NOT NULL,
    emails_sent INT(11) DEFAULT 0 COMMENT 'Versendete E-Mails',
    emails_received INT(11) DEFAULT 0 COMMENT 'Empfangene E-Mails',
    emails_bounced INT(11) DEFAULT 0 COMMENT 'Bounced E-Mails',
    emails_deferred INT(11) DEFAULT 0 COMMENT 'Verzögerte E-Mails',
    emails_rejected INT(11) DEFAULT 0 COMMENT 'Abgelehnte E-Mails',
    emails_spam INT(11) DEFAULT 0 COMMENT 'Als Spam erkannte E-Mails',
    queue_size INT(11) DEFAULT 0 COMMENT 'Queue-Größe',
    queue_age_avg_min INT(11) DEFAULT 0 COMMENT 'Durchschnittl. Queue-Alter (Min)',
    delivery_time_avg_sec INT(11) DEFAULT 0 COMMENT 'Durchschnittl. Zustellzeit (Sek)',
    connections_total INT(11) DEFAULT 0 COMMENT 'Verbindungen gesamt',
    connections_rejected INT(11) DEFAULT 0 COMMENT 'Abgelehnte Verbindungen',
    traffic_in_mb BIGINT(20) DEFAULT 0 COMMENT 'Traffic eingehend (MB)',
    traffic_out_mb BIGINT(20) DEFAULT 0 COMMENT 'Traffic ausgehend (MB)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY date_idx (date),
    KEY created_idx (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Postfix SMTP Gateway Statistiken';

-- ============================================================================
-- TEIL 3: LEGACY STATS ARCHIVIEREN
-- ============================================================================

-- 3.1 Alte Stats-Tabelle umbenennen (Archivierung)
RENAME TABLE bm60_bms_stats TO bm60_bms_stats_legacy_20251031;

-- 3.2 Notiz-Tabelle für Archiv
CREATE TABLE IF NOT EXISTS bm60_migration_notes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    migration_name VARCHAR(100) NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO bm60_migration_notes (migration_name, notes) VALUES
('001_create_new_protocol_support', 
 'Legacy bm60_bms_stats wurde zu bm60_bms_stats_legacy_20251031 umbenannt. Kann nach 90 Tagen gelöscht werden.');

-- ============================================================================
-- TEIL 4: GRUPPEN-OPTIONEN ERWEITERN
-- ============================================================================

-- 4.1 Neue Cyrus-Optionen
-- Diese werden via RegisterGroupOption() im Plugin registriert
-- Hier nur Dokumentation was kommt:
-- - cyrus_quota_mb (INT)
-- - cyrus_jmap_enabled (yes/no)
-- - cyrus_sieve_enabled (yes/no)
-- - cyrus_murder_enabled (yes/no)

-- 4.2 Neue Grommunio-Optionen
-- - grommunio_mapi_enabled (yes/no)
-- - grommunio_ews_enabled (yes/no)
-- - grommunio_eas_enabled (yes/no)
-- - grommunio_sync_realtime (yes/no)

-- 4.3 Neue SFTPGo-Optionen
-- - sftpgo_quota_mb (INT)
-- - sftpgo_s3_enabled (yes/no)
-- - sftpgo_webdav_enabled (yes/no)
-- - sftpgo_public_keys_enabled (yes/no)

-- 4.4 Alte Optionen als deprecated markieren (werden später entfernt)
-- - minpop3 → DEPRECATED
-- - require_weblogin → DEPRECATED
-- - weblogin_interval → DEPRECATED

-- ============================================================================
-- TEIL 5: NEUE PREFS-FELDER
-- ============================================================================

-- 5.1 Cyrus-Prefs zu bm60_bms_prefs hinzufügen
ALTER TABLE bm60_bms_prefs
    ADD COLUMN user_cyrus_imap_server VARCHAR(255) DEFAULT 'localhost' AFTER user_imapssl,
    ADD COLUMN user_cyrus_imap_port INT(11) DEFAULT 143 AFTER user_cyrus_imap_server,
    ADD COLUMN user_cyrus_imap_ssl TINYINT(1) DEFAULT 0 AFTER user_cyrus_imap_port,
    ADD COLUMN user_cyrus_pop3_server VARCHAR(255) DEFAULT 'localhost' AFTER user_cyrus_imap_ssl,
    ADD COLUMN user_cyrus_pop3_port INT(11) DEFAULT 110 AFTER user_cyrus_pop3_server,
    ADD COLUMN user_cyrus_pop3_ssl TINYINT(1) DEFAULT 0 AFTER user_cyrus_pop3_port,
    ADD COLUMN user_cyrus_jmap_url VARCHAR(255) DEFAULT '' AFTER user_cyrus_pop3_ssl;

-- 5.2 Grommunio-Prefs zu bm60_bms_prefs hinzufügen
ALTER TABLE bm60_bms_prefs
    ADD COLUMN user_grommunio_mapi_url VARCHAR(255) DEFAULT '' AFTER user_cyrus_jmap_url,
    ADD COLUMN user_grommunio_ews_url VARCHAR(255) DEFAULT '' AFTER user_grommunio_mapi_url,
    ADD COLUMN user_grommunio_eas_url VARCHAR(255) DEFAULT '' AFTER user_grommunio_ews_url,
    ADD COLUMN user_grommunio_autodiscover_url VARCHAR(255) DEFAULT '' AFTER user_grommunio_eas_url;

-- 5.3 SFTPGo-Prefs zu bm60_bms_prefs hinzufügen
ALTER TABLE bm60_bms_prefs
    ADD COLUMN user_sftpgo_server VARCHAR(255) DEFAULT 'localhost' AFTER user_grommunio_autodiscover_url,
    ADD COLUMN user_sftpgo_sftp_port INT(11) DEFAULT 2022 AFTER user_sftpgo_server,
    ADD COLUMN user_sftpgo_ftps_port INT(11) DEFAULT 2021 AFTER user_sftpgo_sftp_port,
    ADD COLUMN user_sftpgo_webdav_url VARCHAR(255) DEFAULT '' AFTER user_sftpgo_ftps_port,
    ADD COLUMN user_sftpgo_s3_endpoint VARCHAR(255) DEFAULT '' AFTER user_sftpgo_webdav_url;

-- 5.4 Postfix-Prefs
ALTER TABLE bm60_bms_prefs
    ADD COLUMN user_postfix_smtp_server VARCHAR(255) DEFAULT 'localhost' AFTER user_sftpgo_s3_endpoint,
    ADD COLUMN user_postfix_smtp_port INT(11) DEFAULT 587 AFTER user_postfix_smtp_server,
    ADD COLUMN user_postfix_smtp_ssl TINYINT(1) DEFAULT 1 AFTER user_postfix_smtp_port;

-- 5.5 Alte Prefs-Felder umbenennen (NICHT löschen)
ALTER TABLE bm60_bms_prefs
    CHANGE user_pop3server user_pop3server_legacy VARCHAR(255) DEFAULT 'localhost',
    CHANGE user_pop3port user_pop3port_legacy INT(11) DEFAULT 110,
    CHANGE user_pop3ssl user_pop3ssl_legacy TINYINT(1) DEFAULT 0,
    CHANGE user_imapserver user_imapserver_legacy VARCHAR(255) DEFAULT 'localhost',
    CHANGE user_imapport user_imapport_legacy INT(11) DEFAULT 143,
    CHANGE user_imapssl user_imapssl_legacy TINYINT(1) DEFAULT 0,
    CHANGE user_smtpserver user_smtpserver_legacy VARCHAR(255) DEFAULT 'localhost',
    CHANGE user_smtpport user_smtpport_legacy INT(11) DEFAULT 25,
    CHANGE user_smtpssl user_smtpssl_legacy TINYINT(1) DEFAULT 0;

-- ============================================================================
-- TEIL 6: DEFAULT-WERTE SETZEN
-- ============================================================================

-- 6.1 Cyrus-Defaults (aus Umgebungsvariablen oder Config)
UPDATE bm60_bms_prefs SET
    user_cyrus_imap_server = 'localhost',
    user_cyrus_imap_port = 143,
    user_cyrus_pop3_server = 'localhost',
    user_cyrus_pop3_port = 110,
    user_cyrus_jmap_url = 'http://localhost:8008';

-- 6.2 Postfix-Defaults
UPDATE bm60_bms_prefs SET
    user_postfix_smtp_server = 'localhost',
    user_postfix_smtp_port = 587,
    user_postfix_smtp_ssl = 1;

-- ============================================================================
-- TEIL 7: INDICES OPTIMIEREN
-- ============================================================================

-- Indices für schnelle Statistik-Abfragen
ALTER TABLE bm60_cyrus_stats ADD INDEX date_connections (date, imap_connections);
ALTER TABLE bm60_grommunio_stats ADD INDEX date_mapi (date, mapi_connections);
ALTER TABLE bm60_sftpgo_stats ADD INDEX date_sftp (date, sftp_connections);
ALTER TABLE bm60_postfix_stats ADD INDEX date_sent (date, emails_sent);

-- ============================================================================
-- MIGRATION ABGESCHLOSSEN
-- ============================================================================

INSERT INTO bm60_migration_notes (migration_name, notes) VALUES
('001_create_new_protocol_support_complete', 
 CONCAT('Migration erfolgreich abgeschlossen am ', NOW(), 
        '. Neue Protokolle: Cyrus, Grommunio, SFTPGo, Postfix. ',
        'Legacy-Felder umbenannt (nicht gelöscht für Rollback).'));

-- ============================================================================
-- ROLLBACK-ANWEISUNGEN (falls nötig)
-- ============================================================================
/*
-- Rollback Gruppen:
ALTER TABLE bm60_gruppen
    CHANGE pop3_legacy pop3 ENUM('yes','no'),
    CHANGE imap_legacy imap ENUM('yes','no'),
    CHANGE smtp_legacy smtp ENUM('yes','no'),
    DROP COLUMN cyrus,
    DROP COLUMN grommunio,
    DROP COLUMN sftpgo,
    DROP COLUMN postfix;

-- Rollback Stats:
RENAME TABLE bm60_bms_stats_legacy_20251031 TO bm60_bms_stats;
DROP TABLE IF EXISTS bm60_cyrus_stats;
DROP TABLE IF EXISTS bm60_grommunio_stats;
DROP TABLE IF EXISTS bm60_sftpgo_stats;
DROP TABLE IF EXISTS bm60_postfix_stats;

-- Rollback Prefs:
ALTER TABLE bm60_bms_prefs
    CHANGE user_pop3server_legacy user_pop3server VARCHAR(255),
    CHANGE user_pop3port_legacy user_pop3port INT(11),
    -- ... etc für alle Felder
    DROP COLUMN user_cyrus_imap_server,
    -- ... etc für alle neuen Felder
*/
