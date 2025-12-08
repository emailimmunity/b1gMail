<?php
/**
 * Zentrale Protokoll-Konfiguration (Hybrid)
 * Copyright (c) 2025 b1gMail Project
 * 
 * Priorität:
 * 1. Umgebungsvariablen (Docker/Kubernetes)
 * 2. Lokale Config-Datei  
 * 3. Datenbank
 * 4. Defaults (hier definiert)
 */

// Defaults für alle Protokolle
$protocolDefaults = array(
	// CYRUS IMAP/POP3/JMAP
	'cyrus_enabled' => false,
	'cyrus_server' => 'localhost',
	'cyrus_imap_port' => 993,
	'cyrus_pop3_port' => 995,
	'cyrus_admin_user' => 'cyrus',
	'cyrus_admin_pass' => '',
	'cyrus_jmap_url' => 'https://localhost:8080/jmap/',
	
	// POSTFIX (SMTP)
	'postfix_server' => 'localhost',
	'smtp_port' => 25,
	'submission_port' => 587,
	'smtps_port' => 465,
	
	// SABREDAV (CalDAV/CardDAV)
	'sabredav_enabled' => false,
	'sabredav_base_url' => 'https://localhost',
	'caldav_enabled' => false,
	'carddav_enabled' => false,
	'sabredav_webdav_enabled' => false,
	
	// GROMMUNIO (MAPI/EWS/EAS)
	'grommunio_enabled' => false,
	'grommunio_server' => 'localhost',
	'grommunio_admin_api' => 'http://127.0.0.1:8080/api/v1',
	'grommunio_admin_user' => 'admin',
	'grommunio_admin_pass' => '',
	'grommunio_mapi_url' => 'https://localhost/mapi',
	'grommunio_ews_url' => 'https://localhost/EWS/Exchange.asmx',
	'grommunio_eas_url' => 'https://localhost/Microsoft-Server-ActiveSync',
	'grommunio_autodiscover_url' => 'https://localhost/Autodiscover/Autodiscover.xml',
	
	// SFTPGO (SFTP/FTPS/S3/WebDAV)
	'sftpgo_enabled' => false,
	'sftpgo_server' => 'localhost',
	'sftpgo_sftp_port' => 2022,
	'sftpgo_ftps_port' => 990,
	'sftpgo_admin_api' => 'http://127.0.0.1:8080/api/v2',
	'sftpgo_admin_user' => 'admin',
	'sftpgo_admin_pass' => '',
	'sftpgo_s3_enabled' => false,
	'sftpgo_s3_endpoint' => 'https://localhost:8333',
	'sftpgo_webdav_enabled' => false,
	'sftpgo_webdav_endpoint' => 'https://localhost/dav',
);

// Try to load local config file (overrides defaults)
$localConfigFile = dirname(__FILE__) . '/protocols-config.local.php';
if(file_exists($localConfigFile)) {
	include($localConfigFile);
	if(isset($protocolsConfigLocal) && is_array($protocolsConfigLocal)) {
		$protocolsConfig = array_merge($protocolDefaults, $protocolsConfigLocal);
	} else {
		$protocolsConfig = $protocolDefaults;
	}
} else {
	$protocolsConfig = $protocolDefaults;
}

// Lade lokale Config wenn vorhanden (alternative Pfade)
$localConfigFiles = array(
	'/etc/b1gmail/protocols.local.conf.php',
	dirname(dirname(__FILE__)) . '/config/protocols.local.conf.php',
	dirname(__FILE__) . '/protocols.local.conf.php',
);

foreach ($localConfigFiles as $file) {
	if (file_exists($file)) {
		include_once($file);
		// $protocolsConfig wird in der Datei überschrieben/erweitert
		break;
	}
}

/**
 * Get protocol config value
 * Priorität: ENV > Datei > DB > Default
 */
function getProtocolConfig($key, $envVar = null, $default = null) {
	global $protocolsConfig, $db;
	
	// 1. Priorität: Umgebungsvariable
	if ($envVar && ($envValue = getenv($envVar)) !== false) {
		return $envValue;
	}
	
	// 2. Priorität: Lokale Config-Datei
	if (isset($protocolsConfig[$key])) {
		return $protocolsConfig[$key];
	}
	
	// 3. Priorität: Datenbank
	if (isset($db) && is_object($db)) {
		try {
			$res = $db->Query('SELECT config_value FROM {pre}protocols_config WHERE config_key=?', $key);
			if($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$value = $row['config_value'];
				$res->Free();
				return $value;
			}
			$res->Free();
		} catch(Exception $e) {
			// DB nicht verfügbar - ignorieren
		}
	}
	
	// 4. Priorität: Default-Wert
	if ($default !== null) {
		return $default;
	}
	
	return null;
}

/**
 * Set protocol config value (in database)
 */
function setProtocolConfig($key, $value, $type = 'string') {
	global $db;
	
	if (!isset($db) || !is_object($db)) {
		return false;
	}
	
	try {
		// Check if exists
		$res = $db->Query('SELECT id FROM {pre}protocols_config WHERE config_key=?', $key);
		$exists = ($res->FetchArray(MYSQLI_ASSOC) !== false);
		$res->Free();
		
		if ($exists) {
			// Update
			$db->Query('UPDATE {pre}protocols_config SET config_value=?, config_type=?, updated_at=NOW() WHERE config_key=?',
				$value, $type, $key);
		} else {
			// Insert
			$db->Query('INSERT INTO {pre}protocols_config (config_key, config_value, config_type, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())',
				$key, $value, $type);
		}
		
		return true;
	} catch(Exception $e) {
		return false;
	}
}

/**
 * Get all config sources
 */
function getConfigSources() {
	$sources = array();
	
	// Check environment variables
	$envVars = array_filter($_ENV, function($key) {
		return strpos($key, 'CYRUS_') === 0 || 
		       strpos($key, 'POSTFIX_') === 0 ||
		       strpos($key, 'SABREDAV_') === 0 ||
		       strpos($key, 'CALDAV_') === 0 ||
		       strpos($key, 'CARDDAV_') === 0 ||
		       strpos($key, 'GROMMUNIO_') === 0 ||
		       strpos($key, 'SFTPGO_') === 0 ||
		       strpos($key, 'SMTP_') === 0;
	}, ARRAY_FILTER_USE_KEY);
	
	if (count($envVars) > 0) {
		$sources['env'] = array(
			'name' => 'Umgebungsvariablen',
			'count' => count($envVars),
			'editable' => false
		);
	}
	
	// Check local config file
	global $protocolsConfig, $protocolDefaults;
	if ($protocolsConfig !== $protocolDefaults) {
		$sources['file'] = array(
			'name' => 'Lokale Config-Datei',
			'count' => count(array_diff_assoc($protocolsConfig, $protocolDefaults)),
			'editable' => true
		);
	}
	
	// Check database
	global $db;
	if (isset($db) && is_object($db)) {
		try {
			$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}protocols_config');
			if ($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$count = (int)$row['cnt'];
				if ($count > 0) {
					$sources['db'] = array(
						'name' => 'Datenbank',
						'count' => $count,
						'editable' => true
					);
				}
			}
			$res->Free();
		} catch(Exception $e) {
			// DB nicht verfügbar
		}
	}
	
	return $sources;
}

// Define constants für Backward-Compatibility
// CYRUS
define('CYRUS_ENABLED', getProtocolConfig('cyrus_enabled', 'CYRUS_ENABLED', false));
define('CYRUS_SERVER', getProtocolConfig('cyrus_server', 'CYRUS_SERVER', 'localhost'));
define('CYRUS_IMAP_PORT', getProtocolConfig('cyrus_imap_port', 'CYRUS_IMAP_PORT', 993));
define('CYRUS_POP3_PORT', getProtocolConfig('cyrus_pop3_port', 'CYRUS_POP3_PORT', 995));
define('CYRUS_ADMIN_USER', getProtocolConfig('cyrus_admin_user', 'CYRUS_ADMIN_USER', 'cyrus'));
define('CYRUS_ADMIN_PASS', getProtocolConfig('cyrus_admin_pass', 'CYRUS_ADMIN_PASS', ''));
define('CYRUS_JMAP_URL', getProtocolConfig('cyrus_jmap_url', 'CYRUS_JMAP_URL', 'https://localhost:8080/jmap/'));

// POSTFIX
define('POSTFIX_SERVER', getProtocolConfig('postfix_server', 'POSTFIX_SERVER', 'localhost'));
define('SMTP_PORT', getProtocolConfig('smtp_port', 'SMTP_PORT', 25));
define('SUBMISSION_PORT', getProtocolConfig('submission_port', 'SUBMISSION_PORT', 587));
define('SMTPS_PORT', getProtocolConfig('smtps_port', 'SMTPS_PORT', 465));

// SABREDAV
define('SABREDAV_ENABLED', getProtocolConfig('sabredav_enabled', 'SABREDAV_ENABLED', false));
define('SABREDAV_BASE_URL', getProtocolConfig('sabredav_base_url', 'SABREDAV_BASE_URL', 'https://localhost'));
define('CALDAV_ENABLED', getProtocolConfig('caldav_enabled', 'CALDAV_ENABLED', false));
define('CARDDAV_ENABLED', getProtocolConfig('carddav_enabled', 'CARDDAV_ENABLED', false));
define('SABREDAV_WEBDAV_ENABLED', getProtocolConfig('sabredav_webdav_enabled', 'SABREDAV_WEBDAV_ENABLED', false));

// GROMMUNIO
define('GROMMUNIO_ENABLED', getProtocolConfig('grommunio_enabled', 'GROMMUNIO_ENABLED', false));
define('GROMMUNIO_SERVER', getProtocolConfig('grommunio_server', 'GROMMUNIO_SERVER', 'localhost'));
define('GROMMUNIO_ADMIN_API', getProtocolConfig('grommunio_admin_api', 'GROMMUNIO_ADMIN_API', 'http://127.0.0.1:8080/api/v1'));
define('GROMMUNIO_ADMIN_USER', getProtocolConfig('grommunio_admin_user', 'GROMMUNIO_ADMIN_USER', 'admin'));
define('GROMMUNIO_ADMIN_PASS', getProtocolConfig('grommunio_admin_pass', 'GROMMUNIO_ADMIN_PASS', ''));

// SFTPGO
define('SFTPGO_ENABLED', getProtocolConfig('sftpgo_enabled', 'SFTPGO_ENABLED', false));
define('SFTPGO_SERVER', getProtocolConfig('sftpgo_server', 'SFTPGO_SERVER', 'localhost'));
define('SFTPGO_SFTP_PORT', getProtocolConfig('sftpgo_sftp_port', 'SFTPGO_SFTP_PORT', 2022));
define('SFTPGO_FTPS_PORT', getProtocolConfig('sftpgo_ftps_port', 'SFTPGO_FTPS_PORT', 990));
define('SFTPGO_ADMIN_API', getProtocolConfig('sftpgo_admin_api', 'SFTPGO_ADMIN_API', 'http://127.0.0.1:8080/api/v2'));
define('SFTPGO_ADMIN_USER', getProtocolConfig('sftpgo_admin_user', 'SFTPGO_ADMIN_USER', 'admin'));
define('SFTPGO_ADMIN_PASS', getProtocolConfig('sftpgo_admin_pass', 'SFTPGO_ADMIN_PASS', ''));

?>
