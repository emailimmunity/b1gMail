<?php
/**
 * Domain-Admin Dashboard
 * 
 * Admin-Interface für Domain-Admins (type=3)
 * - User der eigenen Domain verwalten
 * - Aliase verwalten
 * - Gruppen verwalten
 * - Statistiken
 */

if(!defined('B1GMAIL_INIT'))
{
	define('B1GMAIL_INIT', true);
	require_once('../serverlib/init.inc.php');
}

// Domain-Admin-Check
if(!isset($_SESSION['adminID']) || $_SESSION['bm_adminType'] != 3)
	die('Access denied - Domain Admin only!');

$adminID = $_SESSION['adminID'];

// Domain des Admins laden
$res = $db->Query('SELECT * FROM {pre}admins WHERE adminid=?', $adminID);
$adminRow = $res->FetchArray(MYSQLI_ASSOC);
$adminDomain = $adminRow['domain']; // z.B. "gtin.org"

/**
 * Prüfen ob User zur Domain gehört
 */
function canManageDomainUser($userID, $adminDomain)
{
	global $db;
	
	$res = $db->Query('SELECT email FROM {pre}users WHERE id=?', $userID);
	if($res->RowCount() == 0)
		return false;
	
	$row = $res->FetchArray(MYSQLI_ASSOC);
	$userDomain = substr(strrchr($row['email'], '@'), 1);
	
	return ($userDomain === $adminDomain);
}

// Actions
$action = $_REQUEST['action'] ?? 'dashboard';

switch($action)
{
	// === DASHBOARD ===
	case 'dashboard':
		// Statistiken laden
		$stats = array();
		
		// Anzahl User der Domain
		$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}users 
			WHERE email LIKE ?', '%@' . $adminDomain);
		list($stats['users']) = $res->FetchArray(MYSQLI_NUM);
		
		// Speicher-Nutzung
		$res = $db->Query('SELECT SUM(mailspace_used) as total FROM {pre}users 
			WHERE email LIKE ?', '%@' . $adminDomain);
		list($stats['storage_used']) = $res->FetchArray(MYSQLI_NUM);
		$stats['storage_used'] = $stats['storage_used'] ?? 0;
		
		// Anzahl Aliase
		$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}aliase a
			JOIN {pre}users u ON u.id = a.user
			WHERE u.email LIKE ?', '%@' . $adminDomain);
		list($stats['aliases']) = $res->FetchArray(MYSQLI_NUM);
		
		// Anzahl Gruppen
		$res = $db->Query('SELECT COUNT(DISTINCT gruppe) as cnt FROM {pre}users 
			WHERE email LIKE ?', '%@' . $adminDomain);
		list($stats['groups']) = $res->FetchArray(MYSQLI_NUM);
		
		// User-Liste
		$users = array();
		$res = $db->Query('SELECT u.*, g.name as group_name 
			FROM {pre}users u
			LEFT JOIN {pre}gruppen g ON g.id = u.gruppe
			WHERE u.email LIKE ?
			ORDER BY u.email
			LIMIT 50', '%@' . $adminDomain);
		while($row = $res->FetchArray(MYSQLI_ASSOC))
			$users[] = $row;
		
		$tpl->assign('stats', $stats);
		$tpl->assign('users', $users);
		$tpl->assign('adminDomain', $adminDomain);
		$tpl->assign('pageTitle', 'Domain-Verwaltung: ' . $adminDomain);
		$tpl->display('admin/domain-admin-dashboard.tpl');
		break;
	
	// === USER ERSTELLEN ===
	case 'create_user':
		if(isset($_POST['submit']))
		{
			$email = trim($_POST['email']);
			
			// Prüfen ob Email zur Domain gehört
			$emailDomain = substr(strrchr($email, '@'), 1);
			if($emailDomain !== $adminDomain)
			{
				$tpl->assign('error', 'Email muss zur Domain ' . $adminDomain . ' gehören!');
			}
			else
			{
				// User anlegen (vereinfacht)
				$password = $_POST['password'];
				$gruppe = (int)$_POST['gruppe'];
				
				// Password hashen
				require_once(B1GMAIL_DIR . 'serverlib/password.class.php');
				$hashResult = PasswordManager::hash($password, 'bcrypt');
				
				$db->Query('INSERT INTO {pre}users 
					(email, passwort, passwort_salt, password_version, gruppe, created_at)
					VALUES (?, ?, "", 2, ?, NOW())',
					$email, $hashResult['hash'], $gruppe);
				
				header('Location: domain-admin-dashboard.php?action=dashboard&success=created');
				exit;
			}
		}
		
		// Gruppen laden
		$groups = array();
		$res = $db->Query('SELECT * FROM {pre}gruppen ORDER BY name');
		while($row = $res->FetchArray(MYSQLI_ASSOC))
			$groups[] = $row;
		
		$tpl->assign('groups', $groups);
		$tpl->assign('adminDomain', $adminDomain);
		$tpl->assign('pageTitle', 'Neuer User');
		$tpl->display('admin/domain-admin-create-user.tpl');
		break;
	
	// === USER BEARBEITEN ===
	case 'edit_user':
		$userID = (int)$_REQUEST['id'];
		
		// Prüfen ob User zur Domain gehört
		if(!canManageDomainUser($userID, $adminDomain))
			die('Access denied - User gehört nicht zu Ihrer Domain!');
		
		$res = $db->Query('SELECT * FROM {pre}users WHERE id=?', $userID);
		$user = $res->FetchArray(MYSQLI_ASSOC);
		
		if(isset($_POST['submit']))
		{
			$gruppe = (int)$_POST['gruppe'];
			
			$db->Query('UPDATE {pre}users SET gruppe=? WHERE id=?', $gruppe, $userID);
			
			// Passwort ändern?
			if(!empty($_POST['password']))
			{
				require_once(B1GMAIL_DIR . 'serverlib/password.class.php');
				$hashResult = PasswordManager::hash($_POST['password'], 'bcrypt');
				
				$db->Query('UPDATE {pre}users SET passwort=?, password_version=2 WHERE id=?',
					$hashResult['hash'], $userID);
			}
			
			header('Location: domain-admin-dashboard.php?action=dashboard&success=updated');
			exit;
		}
		
		// Gruppen laden
		$groups = array();
		$res = $db->Query('SELECT * FROM {pre}gruppen ORDER BY name');
		while($row = $res->FetchArray(MYSQLI_ASSOC))
			$groups[] = $row;
		
		$tpl->assign('user', $user);
		$tpl->assign('groups', $groups);
		$tpl->assign('pageTitle', 'User bearbeiten: ' . $user['email']);
		$tpl->display('admin/domain-admin-edit-user.tpl');
		break;
	
	// === USER LÖSCHEN ===
	case 'delete_user':
		$userID = (int)$_REQUEST['id'];
		
		// Prüfen ob User zur Domain gehört
		if(!canManageDomainUser($userID, $adminDomain))
			die('Access denied');
		
		if(isset($_POST['confirm']))
		{
			$db->Query('UPDATE {pre}users SET gesperrt="yes" WHERE id=?', $userID);
			
			header('Location: domain-admin-dashboard.php?action=dashboard&success=deleted');
			exit;
		}
		
		$res = $db->Query('SELECT * FROM {pre}users WHERE id=?', $userID);
		$user = $res->FetchArray(MYSQLI_ASSOC);
		
		$tpl->assign('user', $user);
		$tpl->assign('pageTitle', 'User löschen');
		$tpl->display('admin/domain-admin-delete-user.tpl');
		break;
	
	// === ALIASE VERWALTEN ===
	case 'aliases':
		// Aliase der Domain laden
		$aliases = array();
		$res = $db->Query('SELECT a.*, u.email as user_email 
			FROM {pre}aliase a
			JOIN {pre}users u ON u.id = a.user
			WHERE u.email LIKE ?
			ORDER BY a.alias', '%@' . $adminDomain);
		while($row = $res->FetchArray(MYSQLI_ASSOC))
			$aliases[] = $row;
		
		$tpl->assign('aliases', $aliases);
		$tpl->assign('adminDomain', $adminDomain);
		$tpl->assign('pageTitle', 'Aliase verwalten');
		$tpl->display('admin/domain-admin-aliases.tpl');
		break;
	
	default:
		die('Unknown action');
}
