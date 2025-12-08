<?php
/**
 * Admin 2FA User Management
 * Komplette Verwaltung von 2FA für alle User
 */

include('../serverlib/admin.inc.php');
RequestPrivileges(PRIVILEGES_ADMIN);

// Load 2FA classes
require_once B1GMAIL_DIR . 'serverlib/totp.class.php';

// AppPassword exists as apppassword.class.php (no hyphen)
if(file_exists(B1GMAIL_DIR . 'serverlib/apppassword.class.php')) {
	require_once B1GMAIL_DIR . 'serverlib/apppassword.class.php';
}

// WebAuthn - check both namespace and legacy locations
// Skip namespace-based WebAuthn if Composer dependencies not installed
if(file_exists(B1GMAIL_DIR . 'backup_security_plugin/webauthn.class.php')) {
	// Use legacy backup class (no Composer dependencies required)
	require_once B1GMAIL_DIR . 'backup_security_plugin/webauthn.class.php';
}
// TODO: Install web-auth/webauthn-lib via Composer to enable production WebAuthn
// composer require web-auth/webauthn-lib

// Yubikey - check backup location
if(file_exists(B1GMAIL_DIR . 'backup_security_plugin/yubikey.class.php')) {
	require_once B1GMAIL_DIR . 'backup_security_plugin/yubikey.class.php';
}

$tpl->assign('page', '2fa_management.tpl');
$tpl->assign('pageTitle', '2FA User Management');
$tpl->assign('title', '2FA User Management');

$message = '';
$error = '';

// ═══════════════════════════════════════════════════════════════
// ACTIONS
// ═══════════════════════════════════════════════════════════════

if(isset($_REQUEST['action']))
{
	$action = $_REQUEST['action'];
	
	// Reset 2FA for User
	if($action == 'reset_2fa' && isset($_REQUEST['userID']))
	{
		$userID = (int)$_REQUEST['userID'];
		
		try {
			// Disable MFA
			$db->Query('UPDATE {pre}users SET mfa_enabled=0, mfa_secret=NULL, mfa_backup_codes=NULL WHERE id=?', $userID);
			
			// Delete all 2FA methods
			$db->Query('DELETE FROM {pre}app_passwords WHERE userID=?', $userID);
			$db->Query('DELETE FROM {pre}webauthn_credentials WHERE userID=?', $userID);
			$db->Query('DELETE FROM {pre}yubikey WHERE userID=?', $userID);
			$db->Query('DELETE FROM {pre}totp_sessions WHERE userID=?', $userID);
			$db->Query('DELETE FROM {pre}security_sessions WHERE userID=?', $userID);
			
			PutLog('Admin reset 2FA for user #' . $userID, PRIO_NOTE, __FILE__, __LINE__);
			
			$message = 'SUCCESS: 2FA wurde für User #' . $userID . ' zurückgesetzt. User kann sich wieder normal einloggen.';
		} catch(Exception $e) {
			$error = 'ERROR: ' . $e->getMessage();
		}
	}
	
	// Delete specific credential
	if($action == 'delete_credential' && isset($_REQUEST['type']) && isset($_REQUEST['id']))
	{
		$type = $_REQUEST['type'];
		$credID = (int)$_REQUEST['id'];
		
		try {
			switch($type)
			{
				case 'app_password':
					$db->Query('DELETE FROM {pre}app_passwords WHERE id=?', $credID);
					$message = 'App-Password gelöscht';
					break;
					
				case 'webauthn':
					$db->Query('DELETE FROM {pre}webauthn_credentials WHERE id=?', $credID);
					$message = 'WebAuthn Credential gelöscht';
					break;
					
				case 'yubikey':
					$db->Query('DELETE FROM {pre}yubikey WHERE id=?', $credID);
					$message = 'Yubikey gelöscht';
					break;
			}
			
			PutLog('Admin deleted 2FA credential: ' . $type . ' #' . $credID, PRIO_NOTE, __FILE__, __LINE__);
		} catch(Exception $e) {
			$error = 'ERROR: ' . $e->getMessage();
		}
	}
}

// ═══════════════════════════════════════════════════════════════
// SEARCH & FILTER
// ═══════════════════════════════════════════════════════════════

$search = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : '';
$filter = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : 'all';
$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
$perPage = isset($_REQUEST['per_page']) ? (int)$_REQUEST['per_page'] : 50;
$offset = ($page - 1) * $perPage;

// Build WHERE clause
$where = array();
$params = array();

if($search != '')
{
	$where[] = '(email LIKE ? OR vorname LIKE ? OR nachname LIKE ?)';
	$params[] = '%' . $search . '%';
	$params[] = '%' . $search . '%';
	$params[] = '%' . $search . '%';
}

switch($filter)
{
	case 'mfa_enabled':
		$where[] = 'mfa_enabled=1';
		break;
		
	case 'mfa_disabled':
		$where[] = 'mfa_enabled=0';
		break;
		
	case 'has_app_passwords':
		$where[] = 'id IN (SELECT DISTINCT userID FROM {pre}app_passwords)';
		break;
		
	case 'has_webauthn':
		$where[] = 'id IN (SELECT DISTINCT userID FROM {pre}webauthn_credentials)';
		break;
		
	case 'has_yubikey':
		$where[] = 'id IN (SELECT DISTINCT userID FROM {pre}yubikey)';
		break;
}

$whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// ═══════════════════════════════════════════════════════════════
// GET USERS
// ═══════════════════════════════════════════════════════════════

$countResult = $db->Query('SELECT COUNT(*) as cnt FROM {pre}users ' . $whereSQL, ...$params);
$countRow = $countResult->FetchArray(MYSQLI_ASSOC);
$totalUsers = $countRow['cnt'];
$totalPages = ceil($totalUsers / $perPage);

$sql = 'SELECT 
		u.id,
		u.email,
		u.vorname,
		u.nachname,
		u.gesperrt,
		u.lastlogin
	FROM {pre}users u
	' . $whereSQL . ' 
	ORDER BY u.lastlogin DESC 
	LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset;

// Don't reset $params - reuse from count query
$result = $db->Query($sql, ...$params);

$users = array();
while($row = $result->FetchArray(MYSQLI_ASSOC))
{
	$userID = $row['id'];
	
	// Get 2FA methods - check from totp table
	$methods = array();
	
	// Check if user has TOTP enabled
	$totpCheck = $db->Query('SELECT enabled FROM {pre}totp WHERE userID=?', $userID);
	if($totpCheck->RowCount() > 0) {
		$totpRow = $totpCheck->FetchArray(MYSQLI_ASSOC);
		if($totpRow['enabled']) {
			$methods[] = 'TOTP';
		}
	}
	$totpCheck->Free();
	
	$appPassResult = $db->Query('SELECT COUNT(*) as cnt FROM {pre}app_passwords WHERE userID=?', $userID);
	if($appPassRow = $appPassResult->FetchArray(MYSQLI_ASSOC))
	{
		if($appPassRow['cnt'] > 0)
			$methods[] = 'App-Passwords (' . $appPassRow['cnt'] . ')';
	}
	
	$webauthnResult = $db->Query('SELECT COUNT(*) as cnt FROM {pre}webauthn_credentials WHERE userID=?', $userID);
	if($webauthnRow = $webauthnResult->FetchArray(MYSQLI_ASSOC))
	{
		if($webauthnRow['cnt'] > 0)
			$methods[] = 'WebAuthn (' . $webauthnRow['cnt'] . ')';
	}
	
	$yubikeyResult = $db->Query('SELECT COUNT(*) as cnt FROM {pre}yubikey WHERE userID=?', $userID);
	if($yubikeyRow = $yubikeyResult->FetchArray(MYSQLI_ASSOC))
	{
		if($yubikeyRow['cnt'] > 0)
			$methods[] = 'Yubikey (' . $yubikeyRow['cnt'] . ')';
	}
	
	$row['methods'] = $methods;
	$row['methods_count'] = count($methods);
	$row['has_2fa'] = !empty($methods);
	
	$users[] = $row;
}

// ═══════════════════════════════════════════════════════════════
// STATISTICS
// ═══════════════════════════════════════════════════════════════

$stats = array();

// Total users with MFA
$result = $db->Query('SELECT COUNT(*) as cnt FROM {pre}users WHERE mfa_enabled=1');
$row = $result->FetchArray(MYSQLI_ASSOC);
$stats['mfa_users'] = $row['cnt'];

// Total app passwords
$result = $db->Query('SELECT COUNT(*) as cnt FROM {pre}app_passwords');
$row = $result->FetchArray(MYSQLI_ASSOC);
$stats['app_passwords'] = $row['cnt'];

// Total WebAuthn credentials
$result = $db->Query('SELECT COUNT(*) as cnt FROM {pre}webauthn_credentials');
$row = $result->FetchArray(MYSQLI_ASSOC);
$stats['webauthn'] = $row['cnt'];

// Total Yubikeys
$result = $db->Query('SELECT COUNT(*) as cnt FROM {pre}yubikey');
$row = $result->FetchArray(MYSQLI_ASSOC);
$stats['yubikey'] = $row['cnt'];

// Total users
$result = $db->Query('SELECT COUNT(*) as cnt FROM {pre}users');
$row = $result->FetchArray(MYSQLI_ASSOC);
$stats['total_users'] = $row['cnt'];

$stats['mfa_percentage'] = $stats['total_users'] > 0 ? round(($stats['mfa_users'] / $stats['total_users']) * 100, 1) : 0;

// ═══════════════════════════════════════════════════════════════
// TEMPLATE VARS
// ═══════════════════════════════════════════════════════════════

$tpl->assign('users', $users);
$tpl->assign('stats', $stats);
$tpl->assign('search', $search);
$tpl->assign('filter', $filter);
$tpl->assign('current_page', $page);
$tpl->assign('per_page', $perPage);
$tpl->assign('total_pages', $totalPages);
$tpl->assign('total_users', $totalUsers);
$tpl->assign('message', $message);
$tpl->assign('error', $error);

// Pagination
$pagination = array();
for($i = 1; $i <= $totalPages; $i++)
{
	$pagination[] = array(
		'page' => $i,
		'current' => $i == $page
	);
}
$tpl->assign('pagination', $pagination);

$tpl->display('page.tpl');
