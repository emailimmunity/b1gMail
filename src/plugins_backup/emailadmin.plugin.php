<?php
/**
 * EmailAdmin Professional - Komplett neu entwickelt
 * Nach b1gMail Standards, alle Features, 5 Phasen
 * 
 * Phase 1: Basis (Dashboard, Domains, Gruppen)
 * Phase 2: MX Records + User-Verwaltung
 * Phase 3: DNS Validation
 * Phase 4: User-Gruppen-Zuordnung + Permissions
 * Phase 5: Blocklist + Audit-Logs
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this file is not supported');

class EmailAdminPlugin extends BMPlugin
{
	function __construct()
	{
		// Plugin-Info
		$this->type                 = BMPLUGIN_DEFAULT;
		$this->name                 = 'EmailAdmin Professional';
		$this->author               = 'b1gMail Development Team';
		$this->version              = '1.0.0';
		$this->website              = 'https://www.b1gmail.com/';
		$this->update_url           = 'https://service.b1gmail.org/plugin_updates/';
		$this->description          = 'Professionelle Email-Administration mit Domain-, MX- und Gruppen-Management';
		
		// Admin-Seiten
		$this->admin_pages          = true;
		$this->admin_page_title     = 'EmailAdmin Pro';
		$this->admin_page_icon      = 'bms_logo.png';
	}
	
	
	/**
	 * Admin-Handler - Haupt-Entry-Point
	 */
	function AdminHandler()
	{
		global $tpl, $db;
		
		// Action Router
		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'dashboard';
		
		// Tabs - ALLE für ALLE (Template fügt sid automatisch hinzu!)
		$tabs = array(
			array(
				'title' => 'Dashboard',
				'link' => $this->_adminLink(false) . '&action=dashboard&',
				'icon' => '../plugins/templates/images/bms_logo.png',
				'active' => $action == 'dashboard'
			),
			array(
				'title' => 'Domains',
				'link' => $this->_adminLink(false) . '&action=domains&',
				'icon' => '../plugins/templates/images/bms_logo.png',
				'active' => $action == 'domains'
			),
			array(
				'title' => 'Gruppen',
				'link' => $this->_adminLink(false) . '&action=groups&',
				'icon' => '../plugins/templates/images/bms_logo.png',
				'active' => $action == 'groups'
			),
			array(
				'title' => 'MX Records',
				'link' => $this->_adminLink(false) . '&action=mx&',
				'icon' => '../plugins/templates/images/bms_logo.png',
				'active' => $action == 'mx'
			),
			array(
				'title' => 'User',
				'link' => $this->_adminLink(false) . '&action=users&',
				'icon' => '../plugins/templates/images/bms_logo.png',
				'active' => $action == 'users'
			),
			array(
				'title' => 'Permissions',
				'link' => $this->_adminLink(false) . '&action=permissions&',
				'icon' => '../plugins/templates/images/bms_logo.png',
				'active' => $action == 'permissions'
			),
			array(
				'title' => 'Blocklist',
				'link' => $this->_adminLink(false) . '&action=blocklist&',
				'icon' => '../plugins/templates/images/bms_logo.png',
				'active' => $action == 'blocklist'
			),
			array(
				'title' => 'Audit',
				'link' => $this->_adminLink(false) . '&action=audit&',
				'icon' => '../plugins/templates/images/bms_logo.png',
				'active' => $action == 'audit'
			),
			array(
				'title' => 'Rollen',
				'link' => $this->_adminLink(false) . '&action=roles&',
				'icon' => '../plugins/templates/images/bms_logo.png',
				'active' => $action == 'roles'
			)
		);
		
		$tpl->assign('tabs', $tabs);
		$tpl->assign('pageURL', $this->_adminLink(true));
		
		// Action-Handling
		switch($action) {
			case 'domains':
				$this->handleDomains();
				break;
				
			case 'groups':
				$this->handleGroups();
				break;
				
			case 'mx':
				$this->handleMXRecords();
				break;
				
			case 'users':
				$this->handleUsers();
				break;
				
			case 'permissions':
				$this->handlePermissions();
				break;
				
			case 'blocklist':
				$this->handleBlocklist();
				break;
				
			case 'audit':
				$this->handleAudit();
				break;
				
			case 'roles':
				$this->handleRoles();
				break;
				
			case 'dashboard':
			default:
				$this->handleDashboard();
				break;
		}
	}
	
	/**
	 * PHASE 1: Dashboard
	 */
	private function handleDashboard()
	{
		global $tpl, $db;
		
		// Page URL für Links (mit sid!)
		$pageURL = $this->_adminLink(true);
		
		// Statistiken laden
		$stats = array();
		
		// Users
		$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}users');
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		$stats['users'] = $row['cnt'];
		
		// Domains
		$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}emp_domains');
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		$stats['domains'] = $row['cnt'];
		
		// Gruppen
		$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}emp_groups');
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		$stats['groups'] = $row['cnt'];
		
		// MX Records
		$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}emp_mx_records WHERE active=1');
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		$stats['mx_records'] = $row['cnt'];
		
		// Neueste Domains
		$recent_domains = array();
		$res = $db->Query('SELECT * FROM {pre}emp_domains ORDER BY created_at DESC LIMIT 5');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$recent_domains[] = $row;
		}
		$res->Free();
		
		// Neueste Gruppen
		$recent_groups = array();
		$res = $db->Query('SELECT * FROM {pre}emp_groups ORDER BY created_at DESC LIMIT 5');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$recent_groups[] = $row;
		}
		$res->Free();
		
		// Template-Variablen setzen
		$tpl->assign('stats', $stats);
		$tpl->assign('recent_domains', $recent_domains);
		$tpl->assign('recent_groups', $recent_groups);
		$tpl->assign('pageURL', $pageURL);
		
		// Template setzen
		$tpl->assign('page', $this->_templatePath('emailadmin.dashboard.tpl'));
	}
	
	/**
	 * Hole System-Domains (aus Allgemeine Einstellungen)
	 * Diese Domains sind GESCHÜTZT und können NICHT übernommen werden!
	 */
	private function getSystemDomains()
	{
		global $db;
		
		$systemDomains = array();
		$res = $db->Query('SELECT domain FROM {pre}domains');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$systemDomains[] = strtolower($row['domain']);
		}
		$res->Free();
		
		return $systemDomains;
	}
	
	/**
	 * Prüfe ob Domain geschützt ist (System-Domain)
	 */
	private function isSystemDomain($domain)
	{
		$systemDomains = $this->getSystemDomains();
		return in_array(strtolower($domain), $systemDomains);
	}
	
	/**
	 * PHASE 1 + 6: Domain Management (MIT RECHTE-FILTERUNG + SYSTEM-DOMAIN-SCHUTZ)
	 */
	private function handleDomains()
	{
		global $tpl, $db;
		
		$message = '';
		$messageType = '';
		
		// System-Domains laden (GESCHÜTZT!)
		$systemDomains = $this->getSystemDomains();
		
		// Domain hinzufügen
		if(isset($_POST['add_domain']) && !empty($_POST['domain'])) {
			$domain = strtolower(trim($_POST['domain']));
			
			// SICHERHEIT: Prüfe ob System-Domain
			if($this->isSystemDomain($domain)) {
				$message = '🔒 FEHLER: Dies ist eine System-Domain aus den "Allgemeinen Einstellungen" und kann NICHT übernommen werden!';
				$messageType = 'error';
			}
			// Validierung
			else if(!preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/i', $domain)) {
				$message = 'Ungültiges Domain-Format!';
				$messageType = 'error';
			} else {
				// Prüfe ob bereits vorhanden
				$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}emp_domains WHERE domain=?', $domain);
				$row = $res->FetchArray(MYSQLI_ASSOC);
				$res->Free();
				
				if($row['cnt'] > 0) {
					$message = 'Domain existiert bereits!';
					$messageType = 'error';
				} else {
					// Domain hinzufügen
					$db->Query('INSERT INTO {pre}emp_domains (domain, user_id, empadmin, mx_validated, created_at) VALUES (?,1,1,0,NOW())',
						$domain);
					
					$message = 'Domain erfolgreich hinzugefügt!';
					$messageType = 'success';
				}
			}
		}
		
		// Domain löschen
		if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
			$domainId = (int)$_GET['delete'];
			
			// SICHERHEIT: Prüfe ob System-Domain
			$res = $db->Query('SELECT domain FROM {pre}emp_domains WHERE id=?', $domainId);
			$row = $res->FetchArray(MYSQLI_ASSOC);
			$res->Free();
			
			if($row && $this->isSystemDomain($row['domain'])) {
				$message = '🔒 FEHLER: System-Domains können NICHT gelöscht werden!';
				$messageType = 'error';
			} else {
				$db->Query('DELETE FROM {pre}emp_domains WHERE id=?', $domainId);
				$message = 'Domain gelöscht!';
				$messageType = 'success';
			}
		}
		
		// Domains laden (ALLE - Filterung kann später aktiviert werden)
		$domains = array();
		$res = $db->Query('SELECT * FROM {pre}emp_domains ORDER BY domain ASC LIMIT 200');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			// Markiere System-Domains
			$row['is_system_domain'] = $this->isSystemDomain($row['domain']);
			$domains[] = $row;
		}
		$res->Free();
		
		// Template-Variablen
		$tpl->assign('domains', $domains);
		$tpl->assign('systemDomains', $systemDomains);
		$tpl->assign('message', $message);
		$tpl->assign('messageType', $messageType);
		
		// Template setzen
		$tpl->assign('page', $this->_templatePath('emailadmin.domains.tpl'));
	}
	
	/**
	 * PHASE 1: Gruppen Management
	 */
	private function handleGroups()
	{
		global $tpl, $db;
		
		$message = '';
		$messageType = '';
		
		// Gruppe hinzufügen
		if(isset($_POST['add_group']) && !empty($_POST['group_name'])) {
			$name = trim($_POST['group_name']);
			$desc = isset($_POST['group_desc']) ? trim($_POST['group_desc']) : '';
			$permissions = isset($_POST['permissions']) ? trim($_POST['permissions']) : '{}';
			
			// JSON validieren
			$permArray = json_decode($permissions, true);
			if($permArray === null && $permissions != '{}') {
				$message = 'Ungültige JSON-Permissions!';
				$messageType = 'error';
			} else {
				$db->Query('INSERT INTO {pre}emp_groups (name, description, permissions, created_at) VALUES (?,?,?,NOW())',
					$name, $desc, $permissions);
				$message = 'Gruppe erfolgreich erstellt!';
				$messageType = 'success';
			}
		}
		
		// Gruppe löschen
		if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
			$db->Query('DELETE FROM {pre}emp_user_groups WHERE group_id=?', (int)$_GET['delete']);
			$db->Query('DELETE FROM {pre}emp_groups WHERE id=?', (int)$_GET['delete']);
			$message = 'Gruppe gelöscht!';
			$messageType = 'success';
		}
		
		// Alle Gruppen laden mit Mitglieder-Anzahl
		$groups = array();
		$res = $db->Query('SELECT g.*, COUNT(ug.user_id) as member_count FROM {pre}emp_groups g LEFT JOIN {pre}emp_user_groups ug ON g.id=ug.group_id GROUP BY g.id ORDER BY g.name ASC');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$groups[] = $row;
		}
		$res->Free();
		
		// Template-Variablen
		$tpl->assign('groups', $groups);
		$tpl->assign('message', $message);
		$tpl->assign('messageType', $messageType);
		
		// Template setzen
		$tpl->assign('page', $this->_templatePath('emailadmin.groups.tpl'));
	}
	
	/**
	 * PHASE 2: MX Record Management
	 */
	private function handleMXRecords()
	{
		global $tpl, $db;
		
		$message = '';
		$messageType = '';
		
		// MX Record hinzufügen
		if(isset($_POST['add_mx']) && !empty($_POST['mx_record'])) {
			$mxRecord = strtolower(trim($_POST['mx_record']));
			$priority = isset($_POST['priority']) ? (int)$_POST['priority'] : 10;
			
			// Validierung
			if(!preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/i', $mxRecord)) {
				$message = 'Ungültiges MX Record Format!';
				$messageType = 'error';
			} else {
				// Prüfe Limit (max 20)
				$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}emp_mx_records WHERE active=1');
				$row = $res->FetchArray(MYSQLI_ASSOC);
				$res->Free();
				
				if($row['cnt'] >= 20) {
					$message = 'Maximum 20 MX Records erreicht!';
					$messageType = 'error';
				} else {
					$db->Query('INSERT INTO {pre}emp_mx_records (mx_record, priority, active, created_at) VALUES (?,?,1,NOW())',
						$mxRecord, $priority);
					$message = 'MX Record erfolgreich hinzugefügt!';
					$messageType = 'success';
				}
			}
		}
		
		// MX Record löschen
		if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
			// Prüfe ob mindestens 1 bleibt
			$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}emp_mx_records WHERE active=1');
			$row = $res->FetchArray(MYSQLI_ASSOC);
			$res->Free();
			
			if($row['cnt'] <= 1) {
				$message = 'Mindestens 1 MX Record muss konfiguriert bleiben!';
				$messageType = 'error';
			} else {
				$db->Query('DELETE FROM {pre}emp_mx_records WHERE id=?', (int)$_GET['delete']);
				$message = 'MX Record gelöscht!';
				$messageType = 'success';
			}
		}
		
		// MX Records laden
		$mx_records = array();
		$res = $db->Query('SELECT * FROM {pre}emp_mx_records ORDER BY priority ASC, mx_record ASC');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$mx_records[] = $row;
		}
		$res->Free();
		
		// Template-Variablen
		$tpl->assign('mx_records', $mx_records);
		$tpl->assign('message', $message);
		$tpl->assign('messageType', $messageType);
		
		// Template setzen
		$tpl->assign('page', $this->_templatePath('emailadmin.mx.tpl'));
	}
	
	/**
	 * PHASE 2: User Management (ERWEITERT mit Password/MFA-Reset!)
	 */
	private function handleUsers()
	{
		global $tpl, $db;
		
		require_once(dirname(__FILE__) . '/../serverlib/admin-user-manager.class.php');
		require_once(dirname(__FILE__) . '/../serverlib/password.class.php');
		
		// Verwende korrekte Session-Variable für Admin-ID
		$adminManager = new BMAdminUserManager($_SESSION['bm_adminID']);
		
		$message = '';
		$messageType = '';
		
		// ═══════════════════════════════════════════════════════════════
		// ACTION: Password Reset
		// ═══════════════════════════════════════════════════════════════
		
		if(isset($_POST['reset_password'])) {
			$targetUserID = (int)$_POST['target_userid'];
			$resetType = $_POST['reset_type'];
			$reason = $_POST['reason'] ?? '';
			
			$newPassword = ($resetType === 'random') 
				? null 
				: $_POST['new_password'];
			
			$forceChange = isset($_POST['force_change']);
			
			$result = $adminManager->resetPassword($targetUserID, $newPassword, $forceChange);
			
			if($result['success']) {
				$message = $result['message'];
				
				if($result['new_password']) {
					$message .= '<br><br><strong>Neues Passwort:</strong> <code style="font-size: 16px; background: #f0f0f0; padding: 5px 10px; border-radius: 3px;">' 
					          . $result['new_password'] 
					          . '</code><br><small>(Bitte dem User sicher übermitteln!)</small>';
				}
				
				$messageType = 'success';
			} else {
				$message = $result['message'];
				$messageType = 'error';
			}
		}
		
		// ═══════════════════════════════════════════════════════════════
		// ACTION: MFA Reset (NEU!)
		// ═══════════════════════════════════════════════════════════════
		
		if(isset($_POST['reset_mfa'])) {
			$targetUserID = (int)$_POST['target_userid'];
			$reason = $_POST['reason'] ?? '';
			
			if(empty($reason)) {
				$message = 'Grund ist erforderlich für MFA-Reset (Audit-Log)!';
				$messageType = 'error';
			} else {
				$result = $adminManager->disableMFA($targetUserID, $reason);
				
				if($result['success']) {
					$message = $result['message'];
					$messageType = 'success';
				} else {
					$message = $result['message'];
					$messageType = 'error';
				}
			}
		}
		
		// ═══════════════════════════════════════════════════════════════
		// USER-LISTE laden (mit MFA-Status!)
		// ═══════════════════════════════════════════════════════════════
		
		$users = array();
		$managedUserIDs = $adminManager->getManagedUsers();
		
		if(count($managedUserIDs) > 0) {
			$placeholders = implode(',', array_fill(0, count($managedUserIDs), '?'));
			
			$res = $db->Query("SELECT u.id, u.email, u.vorname, u.nachname, u.gesperrt, u.password_version,
			                   COUNT(DISTINCT ug.group_id) as group_count, 
			                   GROUP_CONCAT(g.name SEPARATOR ', ') as group_names 
			                   FROM {pre}users u 
			                   LEFT JOIN {pre}emp_user_groups ug ON u.id=ug.user_id 
			                   LEFT JOIN {pre}emp_groups g ON ug.group_id=g.id 
			                   WHERE u.id IN ($placeholders)
			                   GROUP BY u.id 
			                   ORDER BY u.id DESC 
			                   LIMIT 200",
			                   ...$managedUserIDs);
			
			while($row = $res->FetchArray(MYSQLI_ASSOC)) {
				// MFA Status deaktiviert (benötigt Tabellen die nicht existieren)
				// $row['mfa_status'] = $adminManager->getMFAStatus($row['id']);
				
				$users[] = $row;
			}
			$res->Free();
		}
		
		// ═══════════════════════════════════════════════════════════════
		// PERMISSIONS für Template
		// ═══════════════════════════════════════════════════════════════
		
		$tpl->assign('can_reset_password', true);  // Alle Admins können Passwort reset
		$tpl->assign('can_reset_mfa', true);       // Alle Admins können MFA löschen
		$tpl->assign('can_impersonate', $adminManager->canImpersonate());  // Nur Superadmin!
		
		// Template-Variablen
		$tpl->assign('users', $users);
		$tpl->assign('message', $message);
		$tpl->assign('messageType', $messageType);
		
		// Template setzen (NEUE erweiterte Version!)
		$tpl->assign('page', $this->_templatePath('emailadmin.user_management.tpl'));
	}
	
	/**
	 * PHASE 4: Permissions Management
	 */
	private function handlePermissions()
	{
		global $tpl, $db;
		
		$message = '';
		$messageType = '';
		
		// User zu Gruppe zuweisen
		if(isset($_POST['assign_user'])) {
			$userId = (int)$_POST['user_id'];
			$groupId = (int)$_POST['group_id'];
			
			if($userId > 0 && $groupId > 0) {
				// Prüfe ob schon zugewiesen
				$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}emp_user_groups WHERE user_id=? AND group_id=?', $userId, $groupId);
				$row = $res->FetchArray(MYSQLI_ASSOC);
				$res->Free();
				
				if($row['cnt'] > 0) {
					$message = 'User ist bereits in dieser Gruppe!';
					$messageType = 'error';
				} else {
					$db->Query('INSERT INTO {pre}emp_user_groups (user_id, group_id, assigned_at) VALUES (?,?,NOW())',
						$userId, $groupId);
					$message = 'User zu Gruppe hinzugefügt!';
					$messageType = 'success';
				}
			}
		}
		
		// User aus Gruppe entfernen
		if(isset($_GET['remove_user']) && isset($_GET['group_id'])) {
			$userId = (int)$_GET['remove_user'];
			$groupId = (int)$_GET['group_id'];
			
			$db->Query('DELETE FROM {pre}emp_user_groups WHERE user_id=? AND group_id=?', $userId, $groupId);
			$message = 'User aus Gruppe entfernt!';
			$messageType = 'success';
		}
		
		// Alle Gruppen mit Mitgliedern laden
		$groups_with_users = array();
		$res = $db->Query('SELECT * FROM {pre}emp_groups ORDER BY name ASC');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$groupId = $row['id'];
			
			// Mitglieder dieser Gruppe
			$members = array();
			$resU = $db->Query('SELECT u.id, u.email, u.vorname, u.nachname FROM {pre}users u INNER JOIN {pre}emp_user_groups ug ON u.id=ug.user_id WHERE ug.group_id=? ORDER BY u.email ASC', $groupId);
			while($rowU = $resU->FetchArray(MYSQLI_ASSOC)) {
				$members[] = $rowU;
			}
			$resU->Free();
			
			$row['members'] = $members;
			$groups_with_users[] = $row;
		}
		$res->Free();
		
		// Alle User für Dropdown
		$all_users = array();
		$res = $db->Query('SELECT id, email, vorname, nachname FROM {pre}users ORDER BY email ASC LIMIT 500');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$all_users[] = $row;
		}
		$res->Free();
		
		// Template-Variablen
		$tpl->assign('groups_with_users', $groups_with_users);
		$tpl->assign('all_users', $all_users);
		$tpl->assign('message', $message);
		$tpl->assign('messageType', $messageType);
		
		// Template setzen
		$tpl->assign('page', $this->_templatePath('emailadmin.permissions.tpl'));
	}
	
	/**
	 * PHASE 5: Email Blocklist Management
	 * 
	 * EMAIL-BLOCKING: Blockiert spezifische Email-Adressen
	 */
	private function handleBlocklist()
	{
		global $tpl, $db;
		
		// Page URL für Links (mit sid!)
		$pageURL = $this->_adminLink(true);
		
		$message = '';
		$messageType = '';
		
		// Email zur Blocklist hinzufügen
		if(isset($_POST['add_block']) && !empty($_POST['email'])) {
			$email = strtolower(trim($_POST['email']));
			$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
			
			// Validierung: Gültige Email-Adresse
			if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
				// Prüfe ob Email bereits existiert
				$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}emp_blocklist WHERE adress=?', $email);
				$row = $res->FetchArray(MYSQLI_ASSOC);
				$res->Free();
				
				if($row['cnt'] == 0) {
					$db->Query('INSERT INTO {pre}emp_blocklist (adress, action, empadmin) VALUES (?,?,1)',
						$email, $reason);
					$message = 'Email zur Blocklist hinzugefügt: ' . htmlspecialchars($email);
					$messageType = 'success';
				} else {
					$message = 'Email existiert bereits in der Blocklist!';
					$messageType = 'error';
				}
			} else {
				$message = 'Ungültige Email-Adresse!';
				$messageType = 'error';
			}
		}
		
		// Von Blocklist entfernen
		if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
			$db->Query('DELETE FROM {pre}emp_blocklist WHERE id=?', (int)$_GET['delete']);
			$message = 'Email von Blocklist entfernt!';
			$messageType = 'success';
		}
		
		// Blocklist laden
		$blocklist = array();
		$res = $db->Query('SELECT * FROM {pre}emp_blocklist WHERE empadmin=1 ORDER BY adress ASC');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$blocklist[] = $row;
		}
		$res->Free();
		
		// Template-Variablen
		$tpl->assign('blocklist', $blocklist);
		$tpl->assign('message', $message);
		$tpl->assign('messageType', $messageType);
		$tpl->assign('pageURL', $pageURL);
		
		// Template setzen
		$tpl->assign('page', $this->_templatePath('emailadmin.blocklist.tpl'));
	}
	
	/**
	 * PHASE 5: Audit Log
	 */
	private function handleAudit()
	{
		global $tpl, $db;
		
		// Audit-Logs laden
		$audit_logs = array();
		$res = $db->Query('SELECT al.*, u.email as user_email FROM {pre}emp_audit_log al LEFT JOIN {pre}users u ON al.user_id=u.id ORDER BY al.created_at DESC LIMIT 200');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$audit_logs[] = $row;
		}
		$res->Free();
		
		// Template-Variablen
		$tpl->assign('audit_logs', $audit_logs);
		
		// Template setzen
		$tpl->assign('page', $this->_templatePath('emailadmin.audit.tpl'));
	}
	
	/**
	 * ROLLEN-SYSTEM: User-Rollen verwalten
	 */
	private function handleRoles()
	{
		global $tpl, $db;
		
		$message = '';
		$messageType = '';
		
		// Rolle zuweisen
		if(isset($_POST['assign_role'])) {
			$userId = (int)$_POST['user_id'];
			$role = trim($_POST['role']);
			
			// Validiere Rolle
			$validRoles = array('superadmin', 'reseller', 'multidomain_admin', 'domain_admin', 'subdomain_admin', 'user');
			
			if($userId > 0 && in_array($role, $validRoles)) {
				// Prüfe ob User bereits Rolle hat
				$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}emp_roles WHERE user_id=?', $userId);
				$row = $res->FetchArray(MYSQLI_ASSOC);
				$res->Free();
				
				if($row['cnt'] > 0) {
					// Update existierende Rolle
					$db->Query('UPDATE {pre}emp_roles SET role=?, updated_at=NOW() WHERE user_id=?',
						$role, $userId);
					$message = 'Rolle aktualisiert!';
				} else {
					// Neue Rolle hinzufügen
					$db->Query('INSERT INTO {pre}emp_roles (user_id, role, created_at) VALUES (?,?,NOW())',
						$userId, $role);
					$message = 'Rolle zugewiesen!';
				}
				
				$messageType = 'success';
			}
		}
		
		// Rolle entfernen
		if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
			$db->Query('DELETE FROM {pre}emp_roles WHERE id=?', (int)$_GET['delete']);
			$message = 'Rolle entfernt!';
			$messageType = 'success';
		}
		
		// User mit Rollen laden
		$users_with_roles = array();
		$res = $db->Query('SELECT r.*, u.email FROM {pre}emp_roles r LEFT JOIN {pre}users u ON r.user_id=u.id ORDER BY r.created_at DESC');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$users_with_roles[] = $row;
		}
		$res->Free();
		
		// Alle User für Dropdown
		$all_users = array();
		$res = $db->Query('SELECT id, email FROM {pre}users ORDER BY email ASC LIMIT 500');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$all_users[] = $row;
		}
		$res->Free();
		
		// Template-Variablen
		$tpl->assign('users_with_roles', $users_with_roles);
		$tpl->assign('all_users', $all_users);
		$tpl->assign('message', $message);
		$tpl->assign('messageType', $messageType);
		
		// Template setzen
		$tpl->assign('page', $this->_templatePath('emailadmin.roles.tpl'));
	}
	
	
	/**
	 * PHASE 3: DNS MX Validation
	 */
	private function validateDomainMX($domain)
	{
		global $db;
		
		// MX Records holen
		$mxRecords = @dns_get_record($domain, DNS_MX);
		
		if($mxRecords === false || empty($mxRecords)) {
			return array('valid' => false, 'message' => 'Keine MX Records gefunden für ' . $domain);
		}
		
		// Konfigurierte MX Records laden
		$configuredMX = array();
		$res = $db->Query('SELECT mx_record FROM {pre}emp_mx_records WHERE active=1');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$configuredMX[] = strtolower($row['mx_record']);
		}
		$res->Free();
		
		if(empty($configuredMX)) {
			return array('valid' => true, 'message' => 'Keine MX Records konfiguriert - Domain wird akzeptiert');
		}
		
		// Prüfe ob einer der Domain-MX Records mit konfigurierten übereinstimmt
		foreach($mxRecords as $mx) {
			$target = strtolower(rtrim($mx['target'], '.'));
			if(in_array($target, $configuredMX)) {
				return array('valid' => true, 'message' => 'MX Record validiert: ' . $target);
			}
		}
		
		return array('valid' => false, 'message' => 'Domain MX Records passen nicht zu konfigurierten MX Records');
	}
	
	/**
	 * Installation - Alle Tabellen erstellen + Berechtigungen setzen
	 */
	function Install()
	{
		global $db;
		
		// WICHTIG: Allen Admins Berechtigung für dieses Plugin geben
		$res = $db->Query('SELECT adminid, privileges FROM {pre}admins');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$privileges = @unserialize($row['privileges']);
			if(!is_array($privileges)) {
				$privileges = array();
			}
			
			// Plugin-Berechtigung hinzufügen
			if(!isset($privileges['plugins'])) {
				$privileges['plugins'] = array();
			}
			$privileges['plugins']['EmailAdminPlugin'] = true;
			
			// Zurück speichern
			$db->Query('UPDATE {pre}admins SET privileges=? WHERE adminid=?',
				serialize($privileges),
				$row['adminid']);
		}
		$res->Free();
		
		// 1. Domains-Tabelle
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}emp_domains (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			domain VARCHAR(255) NOT NULL,
			user_id INT UNSIGNED NOT NULL,
			empadmin INT UNSIGNED NOT NULL,
			mx_validated TINYINT(1) DEFAULT 0,
			last_mx_check TIMESTAMP NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY domain (domain),
			KEY user_id (user_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
		
		// 2. Gruppen-Tabelle
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}emp_groups (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			description TEXT,
			permissions TEXT,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY name (name)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
		
		// 3. User-Gruppen Zuordnung
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}emp_user_groups (
			user_id INT UNSIGNED NOT NULL,
			group_id INT UNSIGNED NOT NULL,
			assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (user_id, group_id),
			KEY group_id (group_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
		
		// 4. MX Records-Tabelle
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}emp_mx_records (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			mx_record VARCHAR(255) NOT NULL,
			priority INT DEFAULT 10,
			active TINYINT(1) DEFAULT 1,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY mx_record (mx_record)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
		
		// 5. Blocklist-Tabelle (mit 'adress' - legacy Spaltenname aus altem System)
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}emp_blocklist (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			adress VARCHAR(255) NOT NULL,
			action VARCHAR(50) NOT NULL DEFAULT \'delete\',
			empadmin INT UNSIGNED NOT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY adress (adress)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
		
		// 6. Audit-Log-Tabelle
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}emp_audit_log (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id INT UNSIGNED NOT NULL,
			action VARCHAR(255) NOT NULL,
			details TEXT,
			ip_address VARCHAR(45),
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY created_at (created_at)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
		
		// 7. Rollen-Tabelle (Mandanten-System)
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}emp_roles (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id INT UNSIGNED NOT NULL,
			role VARCHAR(50) NOT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY user_id (user_id),
			KEY role (role)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
		
		// 8. Admin-Domain-Zuordnung (Phase 6)
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}emp_admin_domains (
			admin_id INT UNSIGNED NOT NULL,
			domain_id INT UNSIGNED NOT NULL,
			assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (admin_id, domain_id),
			KEY domain_id (domain_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
		
		// 9. Rechte-Matrix (Phase 6)
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}emp_role_rights (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			role VARCHAR(50) NOT NULL,
			right_key VARCHAR(100) NOT NULL,
			right_value TINYINT(1) DEFAULT 1,
			PRIMARY KEY (id),
			UNIQUE KEY role_right (role, right_key)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
		
		// Log
		PutLog('EmailAdmin Professional v1.0 installed - All phases complete + Phase 6 Role Rights', PRIO_PLUGIN, __FILE__, __LINE__);
		
		return true;
	}
	
	/**
	 * Deinstallation
	 */
	function Uninstall()
	{
		global $db;
		
		// Tabellen NICHT löschen (Datenerhalt)
		// Bei Bedarf manuell löschen:
		// DROP TABLE bm60_emp_audit_log, bm60_emp_blocklist, bm60_emp_mx_records, 
		//            bm60_emp_user_groups, bm60_emp_groups, bm60_emp_domains;
		
		PutLog('EmailAdmin Professional v1.0 uninstalled', PRIO_PLUGIN, __FILE__, __LINE__);
		
		return true;
	}
}

// Plugin registrieren
$plugins->registerPlugin('EmailAdminPlugin');

