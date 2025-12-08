<?php
/**
 * SubDomainManager Professional
 * Version 1.0 - Minimal Start
 * 
 * @author b1gMail Development
 * @version 1.0.0
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this file is not supported');

// Load DNS Helper
require_once(__DIR__ . '/subdomainmanager.dns.helper.php');

// Load EmailAdmin Integration Helper
require_once(__DIR__ . '/subdomainmanager.emailadmin.helper.php');

// Load KeyHelp Integration Helper
require_once(__DIR__ . '/subdomainmanager.keyhelp.helper.php');

/**
 * SubDomainManager Plugin
 */
class SubDomainManagerPlugin extends BMPlugin
{
	const VERSION = '1.0.0';
	
	/**
	 * Constructor
	 */
	function __construct()
	{
		// Plugin Info
		$this->type = BMPLUGIN_DEFAULT;
		$this->name = 'SubDomainManager';
		$this->author = 'b1gMail Team';
		$this->version = self::VERSION;
		$this->website = 'https://www.b1gmail.com/';
		$this->description = 'Subdomain-Verwaltung mit DynDNS';
		
		// Admin-Seiten
		$this->admin_pages = true;
		$this->admin_page_title = 'SubDomains';
		$this->admin_page_icon = 'favicon.png';
		
		// User-Seiten
		$this->user_pages = true;
		$this->user_page_title = 'Meine Subdomains';
	}
	
	/**
	 * User Prefs Handler - VOLLSTÄNDIG
	 */
	function UserPrefsPageHandler($action)
	{
		global $tpl, $db, $currentUser;
		
		if($action != 'subdomains')
			return false;
		
		$message = '';
		$messageType = '';
		$userID = $currentUser['id'];
		$userGroupID = $currentUser['gruppe'];
		
		// Subdomain erstellen
		if(isset($_POST['create_subdomain'])) {
			$subdomain = strtolower(trim($_POST['subdomain']));
			$parent_domain = trim($_POST['parent_domain']);
			$enable_dyndns = isset($_POST['enable_dyndns']) ? 1 : 0;
			
			// Validierung
			if(!preg_match('/^[a-z0-9-]+$/', $subdomain)) {
				$message = 'Ungültiger Subdomain-Name! Nur a-z, 0-9 und Bindestriche erlaubt.';
				$messageType = 'error';
			}
			// Blacklist-Check
			elseif($this->isBlacklisted($subdomain)) {
				$message = 'Dieser Subdomain-Name ist nicht erlaubt! Bitte wählen Sie einen anderen Namen.';
				$messageType = 'error';
			} else {
				$full_domain = $subdomain . '.' . $parent_domain;
				
				// Prüfe ob Grant existiert
				$res = $db->Query('SELECT * FROM {pre}sdm_grants WHERE domain=? AND group_id=?', $parent_domain, $userGroupID);
				if($res->RowCount() == 0) {
					$message = 'Keine Berechtigung für diese Domain!';
					$messageType = 'error';
				} else {
					$grant = $res->FetchArray(MYSQLI_ASSOC);
					$res->Free();
					
					// Prüfe Subdomain-Limit
					$countRes = $db->Query('SELECT COUNT(*) as cnt FROM {pre}sdm_subdomains WHERE user_id=? AND grant_id=?', $userID, $grant['id']);
					$countRow = $countRes->FetchArray(MYSQLI_ASSOC);
					$countRes->Free();
					
					if($countRow['cnt'] >= $grant['max_subdomains']) {
						$message = 'Limit erreicht! Max. ' . $grant['max_subdomains'] . ' Subdomains pro Domain.';
						$messageType = 'error';
					} else {
						// DynDNS Token generieren
						$dyndns_token = $enable_dyndns ? bin2hex(random_bytes(16)) : null;
						
						// Subdomain anlegen
						$db->Query('INSERT INTO {pre}sdm_subdomains 
							(grant_id, subdomain, full_domain, user_id, status, dyndns_enabled, dyndns_token, created_at, updated_at) 
							VALUES (?, ?, ?, ?, "active", ?, ?, NOW(), NOW())',
							$grant['id'], $subdomain, $full_domain, $userID, $enable_dyndns, $dyndns_token);
						
						$subdomainId = $db->InsertId();
					
					// DNS-Records erstellen (wenn konfiguriert)
					$dnsResult = $this->createDNSRecordsForSubdomain($subdomainId, $subdomain, $parent_domain);
					
					// Subdomain in EmailAdmin registrieren (für Email-Verwaltung)
					if(SubDomainManagerEmailAdminHelper::isEmailAdminActive()) {
						SubDomainManagerEmailAdminHelper::registerSubdomainInEmailAdmin($full_domain, $userID);
					}
					
					// KeyHelp Webspace erstellen (wenn in Grant aktiviert)
					$keyhelpResult = ['success' => false];
					if($grant['keyhelp_enabled'] == 1) {
						$keyhelpResult = $this->createKeyhelpWebspace($subdomainId, $full_domain, $userID);
					}
					
					$message = 'Subdomain erfolgreich erstellt: ' . $full_domain;
					if($dnsResult['success']) {
						$message .= ' (DNS-Records erstellt)';
					} elseif($dnsResult['error'] !== 'DNS API not configured') {
						$message .= ' (DNS-Fehler: ' . $dnsResult['error'] . ')';
					}
					if($keyhelpResult['success']) {
						$message .= ' (Webspace erstellt)';
					}
					$messageType = 'success';
					
					PutLog('SubDomainManager: User ' . $userID . ' created subdomain ' . $full_domain, PRIO_NOTE, __FILE__, __LINE__);
					}
				}
			}
		}
		
		// Subdomain löschen
		if(isset($_GET['delete_subdomain'])) {
			$subdomain_id = (int)$_GET['delete_subdomain'];
			
			// Prüfe ob Subdomain dem User gehört
			$res = $db->Query('SELECT s.*, g.domain FROM {pre}sdm_subdomains s LEFT JOIN {pre}sdm_grants g ON s.grant_id = g.id WHERE s.id=? AND s.user_id=?', $subdomain_id, $userID);
			if($res->RowCount() > 0) {
				$subdomain = $res->FetchArray(MYSQLI_ASSOC);
				$res->Free();
				
				// Status auf deleted setzen
				$db->Query('UPDATE {pre}sdm_subdomains SET status="deleted", updated_at=NOW() WHERE id=?', $subdomain_id);
				
				// DNS-Records löschen (wenn konfiguriert)
				$subdomainName = str_replace('.' . $subdomain['domain'], '', $subdomain['full_domain']);
				$this->deleteDNSRecordsForSubdomain($subdomainName, $subdomain['domain'], $subdomain['dns_record_id']);
				
				// Aus EmailAdmin entfernen (nur wenn keine Email-Accounts existieren)
				$emailCount = SubDomainManagerEmailAdminHelper::countEmailAccountsForSubdomain($subdomain['full_domain']);
				if($emailCount == 0) {
					SubDomainManagerEmailAdminHelper::unregisterSubdomainFromEmailAdmin($subdomain['full_domain']);
				}
				
				// KeyHelp Webspace löschen (wenn vorhanden)
				if(!empty($subdomain['keyhelp_domain_id'])) {
					$this->deleteKeyhelpWebspace($subdomain['keyhelp_domain_id']);
				}
				
				$message = 'Subdomain gelöscht: ' . $subdomain['full_domain'];
				if($emailCount > 0) {
					$message .= ' (WARNUNG: ' . $emailCount . ' Email-Account(s) existieren noch!)';
				}
				$messageType = 'success';
				
				PutLog('SubDomainManager: User ' . $userID . ' deleted subdomain ' . $subdomain['full_domain'], PRIO_NOTE, __FILE__, __LINE__);
			}
		}
		
		// Token regenerieren
		if(isset($_GET['regenerate_token'])) {
			$subdomain_id = (int)$_GET['regenerate_token'];
			
			$res = $db->Query('SELECT * FROM {pre}sdm_subdomains WHERE id=? AND user_id=?', $subdomain_id, $userID);
			if($res->RowCount() > 0) {
				$new_token = bin2hex(random_bytes(16));
				$db->Query('UPDATE {pre}sdm_subdomains SET dyndns_token=?, updated_at=NOW() WHERE id=?', $new_token, $subdomain_id);
				
				$message = 'DynDNS Token erneuert!';
				$messageType = 'success';
			}
			$res->Free();
		}
		
		// Verfügbare Grants für diesen User
		$availableGrants = [];
		$res = $db->Query('SELECT * FROM {pre}sdm_grants WHERE group_id=? ORDER BY domain ASC', $userGroupID);
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			// Zähle aktuelle Subdomains
			$countRes = $db->Query('SELECT COUNT(*) as cnt FROM {pre}sdm_subdomains WHERE user_id=? AND grant_id=? AND status!="deleted"', 
				$userID, $row['id']);
			$countRow = $countRes->FetchArray(MYSQLI_ASSOC);
			$countRes->Free();
			
			$row['current_subdomains'] = $countRow['cnt'];
			$row['can_create'] = ($countRow['cnt'] < $row['max_subdomains']);
			
			$availableGrants[] = $row;
		}
		$res->Free();
		
		// Meine Subdomains
		$mySubdomains = [];
		$res = $db->Query('SELECT s.*, g.domain as parent_domain 
			FROM {pre}sdm_subdomains s 
			LEFT JOIN {pre}sdm_grants g ON s.grant_id = g.id
			WHERE s.user_id=? AND s.status!="deleted"
			ORDER BY s.created_at DESC', $userID);
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$mySubdomains[] = $row;
		}
		$res->Free();
		
		// DynDNS Update URL
		$dyndns_base_url = $this->_getPref('dyndns_update_url') ?: 'https://yourdomain.com/dyndns/update';
		
		$tpl->assign('availableGrants', $availableGrants);
		$tpl->assign('mySubdomains', $mySubdomains);
		$tpl->assign('message', $message);
		$tpl->assign('messageType', $messageType);
		$tpl->assign('dyndns_base_url', $dyndns_base_url);
		$tpl->assign('pageContent', $this->_templatePath('sdm.user.subdomains.tpl'));
		$tpl->display('li/index.tpl');
		
		return true;
	}
	
	/**
	 * Installation - VOLLSTÄNDIGES SCHEMA
	 */
	function Install()
	{
		global $db;
		
		// 1. Domain-Freigaben (erweitert)
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}sdm_grants (
			id INT PRIMARY KEY AUTO_INCREMENT,
			domain VARCHAR(255) NOT NULL,
			group_id INT NOT NULL,
			max_subdomains INT DEFAULT 5,
			max_emails_per_subdomain INT DEFAULT 10,
			email_enabled TINYINT DEFAULT 1,
			dyndns_enabled TINYINT DEFAULT 0,
			keyhelp_enabled TINYINT DEFAULT 0,
			created_at DATETIME,
			updated_at DATETIME,
			INDEX(domain),
			INDEX(group_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
		
		// 2. Subdomains (erweitert)
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}sdm_subdomains (
			id INT PRIMARY KEY AUTO_INCREMENT,
			grant_id INT NOT NULL,
			subdomain VARCHAR(255) NOT NULL,
			full_domain VARCHAR(510) NOT NULL UNIQUE,
			user_id INT NOT NULL,
			status ENUM("pending","active","suspended","deleted") DEFAULT "pending",
			dyndns_enabled TINYINT DEFAULT 0,
			dyndns_token VARCHAR(64),
			dyndns_current_ip VARCHAR(45),
			dyndns_last_update DATETIME,
			keyhelp_enabled TINYINT DEFAULT 0,
			keyhelp_account_id INT,
			created_at DATETIME,
			updated_at DATETIME,
			INDEX(user_id),
			INDEX(grant_id),
			INDEX(status),
			INDEX(dyndns_token),
			FOREIGN KEY (grant_id) REFERENCES {pre}sdm_grants(id) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
		
		// 3. DynDNS Update Log
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}sdm_dyndns_log (
			id INT PRIMARY KEY AUTO_INCREMENT,
			subdomain_id INT NOT NULL,
			ip_address VARCHAR(45) NOT NULL,
			old_ip VARCHAR(45),
			update_source VARCHAR(100),
			user_agent TEXT,
			success TINYINT DEFAULT 1,
			error_message TEXT,
			update_time DATETIME,
			INDEX(subdomain_id),
			INDEX(update_time),
			FOREIGN KEY (subdomain_id) REFERENCES {pre}sdm_subdomains(id) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
		
		// 4. KeyHelp Accounts
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}sdm_keyhelp_accounts (
			id INT PRIMARY KEY AUTO_INCREMENT,
			subdomain_id INT NOT NULL UNIQUE,
			keyhelp_user_id INT,
			keyhelp_domain_id INT,
			username VARCHAR(255),
			ftp_user VARCHAR(255),
			ftp_password VARCHAR(255),
			webspace_path VARCHAR(500),
			ssl_enabled TINYINT DEFAULT 0,
			ssl_expires DATETIME,
			status ENUM("pending","active","suspended","error") DEFAULT "pending",
			created_at DATETIME,
			updated_at DATETIME,
			INDEX(subdomain_id),
			INDEX(status),
			FOREIGN KEY (subdomain_id) REFERENCES {pre}sdm_subdomains(id) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
		
		// 5. Email-Adressen
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}sdm_emails (
			id INT PRIMARY KEY AUTO_INCREMENT,
			subdomain_id INT NOT NULL,
			email_address VARCHAR(255) NOT NULL UNIQUE,
			email_id INT,
			quota_mb INT DEFAULT 100,
			is_catchall TINYINT DEFAULT 0,
			forward_to VARCHAR(255),
			status ENUM("active","suspended","deleted") DEFAULT "active",
			created_at DATETIME,
			updated_at DATETIME,
			INDEX(subdomain_id),
			INDEX(email_id),
			INDEX(status),
			FOREIGN KEY (subdomain_id) REFERENCES {pre}sdm_subdomains(id) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
		
		// 6. Permissions
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}sdm_permissions (
			id INT PRIMARY KEY AUTO_INCREMENT,
			permission_key VARCHAR(100) NOT NULL UNIQUE,
			permission_name VARCHAR(255) NOT NULL,
			permission_desc TEXT,
			is_default TINYINT DEFAULT 0,
			created_at DATETIME
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
		
		// 7. Gruppen-Permissions
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}sdm_group_permissions (
			id INT PRIMARY KEY AUTO_INCREMENT,
			group_id INT NOT NULL,
			permission_id INT NOT NULL,
			granted TINYINT DEFAULT 1,
			created_at DATETIME,
			UNIQUE KEY unique_group_perm (group_id, permission_id),
			INDEX(group_id),
			FOREIGN KEY (permission_id) REFERENCES {pre}sdm_permissions(id) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
		
		// 8. Settings (als Tabelle statt UserPrefs)
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}sdm_settings (
			id INT PRIMARY KEY AUTO_INCREMENT,
			setting_key VARCHAR(100) NOT NULL UNIQUE,
			setting_value TEXT,
			setting_type ENUM("string","int","bool","json") DEFAULT "string",
			updated_at DATETIME,
			updated_by INT
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
		
		// 9. Blacklist für verbotene Subdomain-Namen
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}sdm_blacklist (
			id INT PRIMARY KEY AUTO_INCREMENT,
			term VARCHAR(255) NOT NULL UNIQUE,
			reason VARCHAR(500),
			is_active TINYINT DEFAULT 1,
			created_at DATETIME,
			created_by INT,
			INDEX(is_active)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
		
		// Default Permissions anlegen
		$defaultPerms = [
			['create_subdomain', 'Subdomain erstellen', 'Benutzer darf neue Subdomains anlegen', 1],
			['delete_subdomain', 'Subdomain löschen', 'Benutzer darf eigene Subdomains löschen', 1],
			['manage_dyndns', 'DynDNS verwalten', 'Benutzer darf DynDNS aktivieren und nutzen', 1],
			['manage_emails', 'Emails verwalten', 'Benutzer darf Email-Adressen anlegen', 1],
			['request_keyhelp', 'KeyHelp Account anfordern', 'Benutzer darf KeyHelp Webspace anfordern', 0],
			['view_stats', 'Statistiken einsehen', 'Benutzer darf eigene Statistiken sehen', 1]
		];
		
		foreach($defaultPerms as $perm) {
			$db->Query('INSERT IGNORE INTO {pre}sdm_permissions (permission_key, permission_name, permission_desc, is_default, created_at) VALUES (?, ?, ?, ?, NOW())',
				$perm[0], $perm[1], $perm[2], $perm[3]);
		}
		
		// Default Settings
		$defaultSettings = [
			['resellerinterface_api_url', 'https://resellerinterface.com/api/v1', 'string'],
			['resellerinterface_api_key', '', 'string'],
			['keyhelp_api_url', '', 'string'],
			['keyhelp_api_key', '', 'string'],
			['dyndns_update_url', 'https://yourdomain.com/dyndns/update', 'string'],
			['dyndns_token_length', '32', 'int'],
			['max_dyndns_updates_per_hour', '60', 'int'],
			['email_quota_default_mb', '100', 'int'],
			['keyhelp_default_quota_mb', '1000', 'int']
		];
		
		foreach($defaultSettings as $setting) {
			$db->Query('INSERT IGNORE INTO {pre}sdm_settings (setting_key, setting_value, setting_type, updated_at) VALUES (?, ?, ?, NOW())',
				$setting[0], $setting[1], $setting[2]);
		}
		
		// Default Blacklist-Einträge
		$defaultBlacklist = [
			['sex', 'Unangemessener Inhalt'],
			['porn', 'Unangemessener Inhalt'],
			['vergewaltigung', 'Illegaler/schädlicher Inhalt'],
			['nazi', 'Hassrede/Extremismus'],
			['terror', 'Illegaler/schädlicher Inhalt'],
			['admin', 'System-reserviert'],
			['root', 'System-reserviert'],
			['system', 'System-reserviert'],
			['api', 'System-reserviert'],
			['www', 'System-reserviert'],
			['mail', 'System-reserviert'],
			['ftp', 'System-reserviert'],
			['smtp', 'System-reserviert'],
			['pop', 'System-reserviert'],
			['imap', 'System-reserviert'],
			['webmail', 'System-reserviert']
		];
		
		foreach($defaultBlacklist as $item) {
			$db->Query('INSERT IGNORE INTO {pre}sdm_blacklist (term, reason, is_active, created_at, created_by) VALUES (?, ?, 1, NOW(), 0)',
				$item[0], $item[1]);
		}
		
		PutLog('SubDomainManager: FULL SCHEMA installed (9 tables, permissions, settings, blacklist)', PRIO_NOTE, __FILE__, __LINE__);
		
		return true;
	}
	
	/**
	 * Deinstallation
	 */
	function Uninstall()
	{
		PutLog('SubDomainManager uninstalled', PRIO_NOTE, __FILE__, __LINE__);
		return true;
	}
	
	/**
	 * Admin-Handler
	 */
	function AdminHandler()
	{
		global $tpl, $db, $lang_admin;
		
		$action = $_REQUEST['action'] ?? 'dashboard';
		
		// KEINE Tabs mehr assignen - werden in Templates erstellt
		// Das verhindert die Icon-Fehler oben
		$tpl->assign('current_action', $action);
		$tpl->assign('sid', session_id());
		$tpl->assign('plugin_name', $this->internal_name);
		
		// Action Routing
		switch($action) {
			case 'grants':
				$this->handleGrants();
				break;
			case 'subdomains':
				$this->handleSubdomains();
				break;
			case 'dyndns':
				$this->handleDynDNS();
				break;
			case 'permissions':
				$this->handlePermissions();
				break;
			case 'blacklist':
				$this->handleBlacklist();
				break;
			case 'settings':
				$this->handleSettings();
				break;
			default:
				$this->handleDashboard();
		}
	}
	
	/**
	 * Dashboard
	 */
	private function handleDashboard()
	{
		global $tpl, $db;
		
		// Stats
		$stats = [];
		$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}sdm_subdomains');
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$stats['subdomains'] = $row['cnt'];
		$res->Free();
		
		$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}sdm_grants');
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$stats['grants'] = $row['cnt'];
		$res->Free();
		
		$tpl->assign('stats', $stats);
		$tpl->assign('plugin_name', $this->internal_name);
		$tpl->assign('plugin_display_name', $this->name);
		$tpl->assign('plugin_version', $this->version);
		$tpl->assign('page', $this->_templatePath('sdm.dashboard.tpl'));
	}
	
	/**
	 * Domain-Freigaben
	 */
	private function handleGrants()
	{
		global $tpl, $db, $bm_prefs;
		
		$message = '';
		$messageType = '';
		
		// Add Grant
		if(isset($_POST['add_grant'])) {
			$domain = trim($_POST['domain']);
			$group_id = (int)$_POST['group_id'];
			$max_subdomains = (int)$_POST['max_subdomains'];
			$max_emails = (int)($_POST['max_emails'] ?? 10);
			$email_enabled = isset($_POST['email_enabled']) ? 1 : 0;
			$dyndns_enabled = isset($_POST['dyndns_enabled']) ? 1 : 0;
			$keyhelp_enabled = isset($_POST['keyhelp_enabled']) ? 1 : 0;
			
			$db->Query('INSERT INTO {pre}sdm_grants (domain, group_id, max_subdomains, max_emails_per_subdomain, email_enabled, dyndns_enabled, keyhelp_enabled, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
				$domain, $group_id, $max_subdomains, $max_emails, $email_enabled, $dyndns_enabled, $keyhelp_enabled);
			
			$message = 'Domain-Freigabe erstellt!';
			$messageType = 'success';
		}
		
		// Update Grant
		if(isset($_POST['update_grant'])) {
			$grant_id = (int)$_POST['grant_id'];
			$domain = trim($_POST['domain']);
			$group_id = (int)$_POST['group_id'];
			$max_subdomains = (int)$_POST['max_subdomains'];
			$max_emails = (int)($_POST['max_emails'] ?? 10);
			$email_enabled = isset($_POST['email_enabled']) ? 1 : 0;
			$dyndns_enabled = isset($_POST['dyndns_enabled']) ? 1 : 0;
			$keyhelp_enabled = isset($_POST['keyhelp_enabled']) ? 1 : 0;
			
			$db->Query('UPDATE {pre}sdm_grants SET domain=?, group_id=?, max_subdomains=?, max_emails_per_subdomain=?, email_enabled=?, dyndns_enabled=?, keyhelp_enabled=?, updated_at=NOW() WHERE id=?',
				$domain, $group_id, $max_subdomains, $max_emails, $email_enabled, $dyndns_enabled, $keyhelp_enabled, $grant_id);
			
			$message = 'Domain-Freigabe aktualisiert!';
			$messageType = 'success';
		}
		
		// Delete Grant
		if(isset($_GET['delete'])) {
			$db->Query('DELETE FROM {pre}sdm_grants WHERE id=?', (int)$_GET['delete']);
			$message = 'Freigabe gelöscht!';
			$messageType = 'success';
		}
		
		// Edit Grant laden
		$editGrant = null;
		if(isset($_GET['edit'])) {
			$edit_id = (int)$_GET['edit'];
			$res = $db->Query('SELECT * FROM {pre}sdm_grants WHERE id=?', $edit_id);
			if($res->RowCount() > 0) {
				$editGrant = $res->FetchArray(MYSQLI_ASSOC);
			}
			$res->Free();
		}
		
		// Get Grants
		$grants = [];
		$res = $db->Query('SELECT g.* FROM {pre}sdm_grants g ORDER BY g.created_at DESC');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			// Gruppenname separat holen
			$grpRes = $db->Query('SELECT titel FROM {pre}gruppen WHERE id=?', $row['group_id']);
			if($grpRes->RowCount() > 0) {
				$grpRow = $grpRes->FetchArray(MYSQLI_ASSOC);
				$row['group_name'] = $grpRow['titel'];
			} else {
				$row['group_name'] = 'Gruppe #' . $row['group_id'];
			}
			$grpRes->Free();
			$grants[] = $row;
		}
		$res->Free();
		
		// Available Domains (from general settings)
		$availableDomains = [];
		if(!empty($bm_prefs['domains'])) {
			// Prüfe ob Array oder String
			if(is_array($bm_prefs['domains'])) {
				$availableDomains = $bm_prefs['domains'];
			} else {
				$availableDomains = explode(',', $bm_prefs['domains']);
			}
		}
		
		// Groups
		$groups = [];
		$res = $db->Query('SELECT id, titel as gruppe FROM {pre}gruppen ORDER BY titel ASC');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$groups[] = $row;
		}
		$res->Free();
		
		$tpl->assign('grants', $grants);
		$tpl->assign('availableDomains', $availableDomains);
		$tpl->assign('groups', $groups);
		$tpl->assign('editGrant', $editGrant);
		$tpl->assign('message', $message);
		$tpl->assign('messageType', $messageType);
		$tpl->assign('sid', session_id());
		$tpl->assign('page', $this->_templatePath('sdm.admin.grants.tpl'));
	}
	
	/**
	 * Einstellungen
	 */
	private function handleSettings()
	{
		global $tpl, $db;
		
		$message = '';
		$messageType = '';
		
		// Save Settings
		if(isset($_POST['save_settings'])) {
			$settings = [
				'resellerinterface_api_key' => $_POST['resellerinterface_api_key'] ?? '',
				'resellerinterface_api_url' => $_POST['resellerinterface_api_url'] ?? '',
				'keyhelp_api_url' => $_POST['keyhelp_api_url'] ?? '',
				'keyhelp_api_key' => $_POST['keyhelp_api_key'] ?? '',
				'dyndns_update_url' => $_POST['dyndns_update_url'] ?? ''
			];
			
			foreach($settings as $key => $value) {
				$this->_setPref($key, $value);
			}
			
			$message = 'Einstellungen gespeichert!';
			$messageType = 'success';
		}
		
		// Load Settings
		$settings = [
			'resellerinterface_api_key' => $this->_getPref('resellerinterface_api_key'),
			'resellerinterface_api_url' => $this->_getPref('resellerinterface_api_url') ?: 'https://resellerinterface.com/api/v1',
			'keyhelp_api_url' => $this->_getPref('keyhelp_api_url'),
			'keyhelp_api_key' => $this->_getPref('keyhelp_api_key'),
			'dyndns_update_url' => $this->_getPref('dyndns_update_url') ?: 'https://yourdomain.com/dyndns/update'
		];
		
		$tpl->assign('settings', $settings);
		$tpl->assign('message', $message);
		$tpl->assign('messageType', $messageType);
		$tpl->assign('page', $this->_templatePath('sdm.admin.settings.tpl'));
	}
	
	/**
	 * Subdomains-Übersicht mit Pagination und Suche
	 */
	private function handleSubdomains()
	{
		global $tpl, $db;
		
		// Pagination
		$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
		$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
		$offset = ($page - 1) * $perPage;
		
		// Suche
		$search = isset($_GET['search']) ? trim($_GET['search']) : '';
		
		// WHERE Bedingung
		$whereClause = '';
		$params = [];
		if($search) {
			$whereClause = "WHERE (s.subdomain LIKE ? OR s.full_domain LIKE ? OR u.email LIKE ?)";
			$searchTerm = '%' . $search . '%';
			$params = [$searchTerm, $searchTerm, $searchTerm];
		}
		
		// Zähle Gesamt-Anzahl
		$countQuery = "SELECT COUNT(*) as cnt FROM {pre}sdm_subdomains s LEFT JOIN {pre}users u ON s.user_id = u.id " . $whereClause;
		$countRes = $db->Query($countQuery, ...$params);
		$countRow = $countRes->FetchArray(MYSQLI_ASSOC);
		$totalCount = $countRow['cnt'];
		$countRes->Free();
		
		// Berechne Pagination
		$totalPages = ceil($totalCount / $perPage);
		
		// Get Subdomains mit Pagination
		$subdomains = [];
		$query = "
			SELECT s.*, u.email, g.domain as parent_domain
			FROM {pre}sdm_subdomains s
			LEFT JOIN {pre}users u ON s.user_id = u.id
			LEFT JOIN {pre}sdm_grants g ON s.grant_id = g.id
			{$whereClause}
			ORDER BY s.created_at DESC
			LIMIT {$perPage} OFFSET {$offset}
		";
		$res = $db->Query($query, ...$params);
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			// Zähle Emails für diese Subdomain
			$emailCountRes = $db->Query('SELECT COUNT(*) as cnt FROM {pre}sdm_emails WHERE subdomain_id=?', $row['id']);
			$emailCountRow = $emailCountRes->FetchArray(MYSQLI_ASSOC);
			$row['email_count'] = $emailCountRow['cnt'];
			$emailCountRes->Free();
			
			$subdomains[] = $row;
		}
		$res->Free();
		
		// Pagination-Daten für Template vorbereiten
		$paginationPages = [];
		$startPage = max(1, $page - 2);
		$endPage = min($totalPages, $page + 2);
		
		for($i = $startPage; $i <= $endPage; $i++) {
			$paginationPages[] = $i;
		}
		
		$tpl->assign('subdomains', $subdomains);
		$tpl->assign('totalCount', $totalCount);
		$tpl->assign('currentPage', $page);
		$tpl->assign('totalPages', $totalPages);
		$tpl->assign('perPage', $perPage);
		$tpl->assign('search', $search);
		$tpl->assign('prevPage', max(1, $page - 1));
		$tpl->assign('nextPage', min($totalPages, $page + 1));
		$tpl->assign('paginationPages', $paginationPages);
		$tpl->assign('showFirstPage', $page > 3);
		$tpl->assign('showLastPage', $page < $totalPages - 2);
		$tpl->assign('showStartDots', $page > 4);
		$tpl->assign('showEndDots', $page < $totalPages - 3);
		$tpl->assign('page', $this->_templatePath('sdm.admin.subdomains.tpl'));
	}
	
	/**
	 * DynDNS Monitor mit Pagination und Suche
	 */
	private function handleDynDNS()
	{
		global $tpl, $db;
		
		// Pagination
		$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
		$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
		$offset = ($page - 1) * $perPage;
		
		// Suche
		$search = isset($_GET['search']) ? trim($_GET['search']) : '';
		
		// WHERE Bedingung
		$whereClause = 'WHERE s.dyndns_enabled = 1';
		$params = [];
		if($search) {
			$whereClause .= " AND (s.subdomain LIKE ? OR s.full_domain LIKE ? OR u.email LIKE ?)";
			$searchTerm = '%' . $search . '%';
			$params = [$searchTerm, $searchTerm, $searchTerm];
		}
		
		// Zähle Gesamt-Anzahl
		$countQuery = "SELECT COUNT(*) as cnt FROM {pre}sdm_subdomains s LEFT JOIN {pre}users u ON s.user_id = u.id " . $whereClause;
		$countRes = $db->Query($countQuery, ...$params);
		$countRow = $countRes->FetchArray(MYSQLI_ASSOC);
		$totalCount = $countRow['cnt'];
		$countRes->Free();
		
		// Berechne Pagination
		$totalPages = ceil($totalCount / $perPage);
		
		// Get DynDNS-enabled Subdomains mit Pagination
		$dyndns_subdomains = [];
		$query = "
			SELECT s.*, u.email
			FROM {pre}sdm_subdomains s
			LEFT JOIN {pre}users u ON s.user_id = u.id
			{$whereClause}
			ORDER BY s.dyndns_last_update DESC
			LIMIT {$perPage} OFFSET {$offset}
		";
		$res = $db->Query($query, ...$params);
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			// Zähle letzte Updates
			$logCountRes = $db->Query('SELECT COUNT(*) as cnt FROM {pre}sdm_dyndns_log WHERE subdomain_id=?', $row['id']);
			$logCountRow = $logCountRes->FetchArray(MYSQLI_ASSOC);
			$row['update_count'] = $logCountRow['cnt'];
			$logCountRes->Free();
			
			$dyndns_subdomains[] = $row;
		}
		$res->Free();
		
		// Pagination-Daten vorbereiten
		$paginationPages = [];
		$startPage = max(1, $page - 2);
		$endPage = min($totalPages, $page + 2);
		for($i = $startPage; $i <= $endPage; $i++) {
			$paginationPages[] = $i;
		}
		
		$tpl->assign('dyndns_subdomains', $dyndns_subdomains);
		$tpl->assign('totalCount', $totalCount);
		$tpl->assign('currentPage', $page);
		$tpl->assign('totalPages', $totalPages);
		$tpl->assign('perPage', $perPage);
		$tpl->assign('search', $search);
		$tpl->assign('prevPage', max(1, $page - 1));
		$tpl->assign('nextPage', min($totalPages, $page + 1));
		$tpl->assign('paginationPages', $paginationPages);
		$tpl->assign('showFirstPage', $page > 3);
		$tpl->assign('showLastPage', $page < $totalPages - 2);
		$tpl->assign('showStartDots', $page > 4);
		$tpl->assign('showEndDots', $page < $totalPages - 3);
		$tpl->assign('page', $this->_templatePath('sdm.admin.dyndns.tpl'));
	}
	
	/**
	 * Permissions Manager
	 */
	private function handlePermissions()
	{
		global $tpl, $db;
		
		$message = '';
		$messageType = '';
		
		// Save Group Permissions
		if(isset($_POST['save_permissions'])) {
			$group_id = (int)$_POST['group_id'];
			$permissions = $_POST['permissions'] ?? [];
			
			// Erst alle löschen
			$db->Query('DELETE FROM {pre}sdm_group_permissions WHERE group_id=?', $group_id);
			
			// Dann neue setzen
			foreach($permissions as $perm_id => $granted) {
				if($granted) {
					$db->Query('INSERT INTO {pre}sdm_group_permissions (group_id, permission_id, granted, created_at) VALUES (?, ?, 1, NOW())',
						$group_id, $perm_id);
				}
			}
			
			$message = 'Permissions gespeichert!';
			$messageType = 'success';
		}
		
		// Get all Permissions
		$permissions = [];
		$res = $db->Query('SELECT * FROM {pre}sdm_permissions ORDER BY permission_name ASC');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$permissions[] = $row;
		}
		$res->Free();
		
		// Get Groups
		$groups = [];
		$res = $db->Query('SELECT id, titel as gruppe FROM {pre}gruppen ORDER BY titel ASC');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$groups[] = $row;
		}
		$res->Free();
		
		// Get current group permissions if group selected
		$currentPerms = [];
		if(isset($_GET['group_id'])) {
			$group_id = (int)$_GET['group_id'];
			$res = $db->Query('SELECT permission_id FROM {pre}sdm_group_permissions WHERE group_id=? AND granted=1', $group_id);
			while($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$currentPerms[$row['permission_id']] = true;
			}
			$res->Free();
			$tpl->assign('selected_group_id', $group_id);
		}
		
		$tpl->assign('permissions', $permissions);
		$tpl->assign('groups', $groups);
		$tpl->assign('currentPerms', $currentPerms);
		$tpl->assign('message', $message);
		$tpl->assign('messageType', $messageType);
		$tpl->assign('page', $this->_templatePath('sdm.admin.permissions.tpl'));
	}
	
	/**
	 * Blacklist-Verwaltung
	 */
	private function handleBlacklist()
	{
		global $tpl, $db;
		
		$message = '';
		$messageType = '';
		
		// Blacklist-Eintrag hinzufügen
		if(isset($_POST['add_blacklist'])) {
			$term = strtolower(trim($_POST['term']));
			$reason = trim($_POST['reason']);
			
			if(empty($term)) {
				$message = 'Bitte einen Begriff eingeben!';
				$messageType = 'error';
			} else {
				$db->Query('INSERT INTO {pre}sdm_blacklist (term, reason, is_active, created_at, created_by) VALUES (?, ?, 1, NOW(), 0)',
					$term, $reason);
				$message = 'Blacklist-Eintrag hinzugefügt: ' . $term;
				$messageType = 'success';
			}
		}
		
		// Blacklist-Eintrag aktualisieren
		if(isset($_POST['update_blacklist'])) {
			$item_id = (int)$_POST['item_id'];
			$term = strtolower(trim($_POST['term']));
			$reason = trim($_POST['reason']);
			$is_active = isset($_POST['is_active']) ? 1 : 0;
			
			if(empty($term)) {
				$message = 'Bitte einen Begriff eingeben!';
				$messageType = 'error';
			} else {
				$db->Query('UPDATE {pre}sdm_blacklist SET term=?, reason=?, is_active=? WHERE id=?',
					$term, $reason, $is_active, $item_id);
				$message = 'Blacklist-Eintrag aktualisiert!';
				$messageType = 'success';
			}
		}
		
		// Blacklist-Eintrag löschen
		if(isset($_GET['delete'])) {
			$db->Query('DELETE FROM {pre}sdm_blacklist WHERE id=?', (int)$_GET['delete']);
			$message = 'Blacklist-Eintrag gelöscht!';
			$messageType = 'success';
		}
		
		// Blacklist-Eintrag aktivieren/deaktivieren
		if(isset($_GET['toggle'])) {
			$id = (int)$_GET['toggle'];
			$db->Query('UPDATE {pre}sdm_blacklist SET is_active = 1 - is_active WHERE id=?', $id);
			$message = 'Status aktualisiert!';
			$messageType = 'success';
		}
		
		// Blacklist-Eintrag zum Bearbeiten laden
		$editItem = null;
		if(isset($_GET['edit'])) {
			$edit_id = (int)$_GET['edit'];
			$res = $db->Query('SELECT * FROM {pre}sdm_blacklist WHERE id=?', $edit_id);
			if($res->RowCount() > 0) {
				$editItem = $res->FetchArray(MYSQLI_ASSOC);
			}
			$res->Free();
		}
		
		// Alle Blacklist-Einträge laden
		$blacklist = [];
		$res = $db->Query('SELECT * FROM {pre}sdm_blacklist ORDER BY term ASC');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$blacklist[] = $row;
		}
		$res->Free();
		
		$tpl->assign('blacklist', $blacklist);
		$tpl->assign('editItem', $editItem);
		$tpl->assign('message', $message);
		$tpl->assign('messageType', $messageType);
		$tpl->assign('page', $this->_templatePath('sdm.admin.blacklist.tpl'));
	}
	
	/**
	 * Blacklist-Check für Subdomain-Namen
	 */
	private function isBlacklisted($subdomain)
	{
		global $db;
		
		// Exakter Match
		$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}sdm_blacklist WHERE term=? AND is_active=1', strtolower($subdomain));
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		if($row['cnt'] > 0) {
			return true;
		}
		
		// Teilstring-Check (enthält verbotene Begriffe)
		$res = $db->Query('SELECT term FROM {pre}sdm_blacklist WHERE is_active=1');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			if(strpos($subdomain, strtolower($row['term'])) !== false) {
				$res->Free();
				return true;
			}
		}
		$res->Free();
		
		return false;
	}
	
	/**
	 * DNS-Helper Instanz erstellen
	 */
	private function getDNSHelper()
	{
		$apiUrl = $this->_getPref('resellerinterface_api_url');
		$apiKey = $this->_getPref('resellerinterface_api_key');
		
		if(empty($apiUrl) || empty($apiKey)) {
			return null;
		}
		
		return new SubDomainManagerDNSHelper($apiUrl, $apiKey);
	}
	
	/**
	 * DNS-Records für Subdomain erstellen
	 * 
	 * @param int $subdomainId Subdomain-ID
	 * @param string $subdomain Subdomain-Name
	 * @param string $domain Domain
	 * @param string $ip IP-Adresse (optional)
	 * @return array ['success' => bool, 'records' => array, 'error' => string]
	 */
	private function createDNSRecordsForSubdomain($subdomainId, $subdomain, $domain, $ip = null)
	{
		global $db;
		
		$dns = $this->getDNSHelper();
		if(!$dns) {
			return ['success' => false, 'error' => 'DNS API not configured'];
		}
		
		// Default IP wenn keine angegeben
		if(!$ip) {
			$ip = $this->_getPref('default_subdomain_ip') ?: '127.0.0.1';
		}
		
		// Erstelle DNS-Records
		$result = $dns->createFullDNSSetup($domain, $subdomain, $ip, false);
		
		if($result['success']) {
			// Speichere DNS Record IDs in Datenbank
			if(isset($result['records']['A'])) {
				$db->Query('UPDATE {pre}sdm_subdomains SET dns_record_id=? WHERE id=?', 
					$result['records']['A'], $subdomainId);
			}
			
			PutLog('SubDomainManager: DNS records created for ' . $subdomain . '.' . $domain, PRIO_NOTE, __FILE__, __LINE__);
		} else {
			PutLog('SubDomainManager: DNS creation failed for ' . $subdomain . '.' . $domain . ': ' . $result['error'], PRIO_WARNING, __FILE__, __LINE__);
		}
		
		return $result;
	}
	
	/**
	 * DNS A-Record für Subdomain updaten (für DynDNS)
	 */
	private function updateDNSRecordForSubdomain($subdomainId, $newIp)
	{
		global $db;
		
		$dns = $this->getDNSHelper();
		if(!$dns) {
			return false;
		}
		
		// Lade Subdomain-Daten
		$res = $db->Query('SELECT dns_record_id FROM {pre}sdm_subdomains WHERE id=?', $subdomainId);
		if($res->RowCount() == 0) {
			$res->Free();
			return false;
		}
		$subdomain = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		if(empty($subdomain['dns_record_id'])) {
			return false;
		}
		
		// Update DNS-Record
		$success = $dns->updateARecord($subdomain['dns_record_id'], $newIp);
		
		if($success) {
			PutLog('SubDomainManager: DNS record updated for subdomain ID ' . $subdomainId . ' to IP ' . $newIp, PRIO_NOTE, __FILE__, __LINE__);
		} else {
			PutLog('SubDomainManager: DNS update failed for subdomain ID ' . $subdomainId . ': ' . $dns->getLastError(), PRIO_WARNING, __FILE__, __LINE__);
		}
		
		return $success;
	}
	
	/**
	 * DNS-Records für Subdomain löschen
	 */
	private function deleteDNSRecordsForSubdomain($subdomain, $domain, $dnsRecordId = null)
	{
		$dns = $this->getDNSHelper();
		if(!$dns) {
			return false;
		}
		
		$deletedCount = 0;
		
		// Wenn Record-ID bekannt, direkt löschen
		if($dnsRecordId) {
			if($dns->deleteRecord($dnsRecordId)) {
				$deletedCount++;
			}
		}
		
		// Zusätzlich alle Records für diese Subdomain löschen (Fallback)
		$deletedCount += $dns->deleteAllRecordsForSubdomain($domain, $subdomain);
		
		if($deletedCount > 0) {
			PutLog('SubDomainManager: ' . $deletedCount . ' DNS record(s) deleted for ' . $subdomain . '.' . $domain, PRIO_NOTE, __FILE__, __LINE__);
			return true;
		}
		
		return false;
	}
	
	/**
	 * KeyHelp-Helper Instanz erstellen
	 */
	private function getKeyhelpHelper()
	{
		$apiUrl = $this->_getPref('keyhelp_api_url');
		$apiKey = $this->_getPref('keyhelp_api_key');
		
		if(empty($apiUrl) || empty($apiKey)) {
			return null;
		}
		
		return new SubDomainManagerKeyhelpHelper($apiUrl, $apiKey);
	}
	
	/**
	 * Webspace für Subdomain in KeyHelp erstellen
	 * 
	 * @param int $subdomainId Subdomain-ID
	 * @param string $fullDomain Vollständige Domain
	 * @param int $userId User-ID
	 * @return array ['success' => bool, 'domain_id' => int, 'error' => string]
	 */
	private function createKeyhelpWebspace($subdomainId, $fullDomain, $userId)
	{
		global $db;
		
		$keyhelp = $this->getKeyhelpHelper();
		if(!$keyhelp) {
			return ['success' => false, 'error' => 'KeyHelp API not configured'];
		}
		
		// Generiere Credentials
		$username = 'sub_' . $subdomainId;
		$password = bin2hex(random_bytes(12));
		
		// Erstelle Webspace
		$result = $keyhelp->createCompleteSetup($fullDomain, $username, $password);
		
		if($result['success']) {
			// Speichere KeyHelp Domain-ID und Credentials
			$db->Query('
				UPDATE {pre}sdm_subdomains 
				SET keyhelp_domain_id=?, keyhelp_username=?, keyhelp_password=?
				WHERE id=?
			', $result['domain_id'], $username, $password, $subdomainId);
			
			PutLog('SubDomainManager: KeyHelp webspace created for ' . $fullDomain, PRIO_NOTE, __FILE__, __LINE__);
		} else {
			PutLog('SubDomainManager: KeyHelp creation failed for ' . $fullDomain . ': ' . $result['error'], PRIO_WARNING, __FILE__, __LINE__);
		}
		
		return $result;
	}
	
	/**
	 * Webspace für Subdomain aus KeyHelp löschen
	 */
	private function deleteKeyhelpWebspace($keyhelpDomainId)
	{
		$keyhelp = $this->getKeyhelpHelper();
		if(!$keyhelp || empty($keyhelpDomainId)) {
			return false;
		}
		
		$success = $keyhelp->deleteDomain($keyhelpDomainId);
		
		if($success) {
			PutLog('SubDomainManager: KeyHelp webspace deleted: Domain ID ' . $keyhelpDomainId, PRIO_NOTE, __FILE__, __LINE__);
		}
		
		return $success;
	}
}

/**
 * Plugin registrieren
 */
$plugins->registerPlugin('SubDomainManagerPlugin');
