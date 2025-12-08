<?php
/**
 * Admin: Security & 2FA Management
 * Superadmin kann 2FA/App-Passwords/WebAuthn/Yubikey für alle User verwalten
 */

define('B1GMAIL_INIT', true);
$bgPage = 'security';

require_once('../serverlib/init.inc.php');
require_once('../serverlib/totp.class.php');
require_once('../serverlib/app-passwords.class.php');
require_once('../serverlib/webauthn.class.php');
require_once('../serverlib/yubikey.class.php');

// Admin check
if($_SESSION['admin_gruppe'] != ADMIN_GROUP_SUPERADMIN) {
	header('Location: start.php');
	exit;
}

// Actions
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'overview';
$userID = isset($_REQUEST['user_id']) ? (int)$_REQUEST['user_id'] : 0;
$message = '';

switch($action) {
	// Reset 2FA für User
	case 'reset_mfa':
		if($userID > 0) {
			BMTOTP::disableForUser($userID);
			$message = 'MFA/2FA wurde für User #' . $userID . ' zurückgesetzt.';
		}
		break;
		
	// Lösche alle App-Passwords für User
	case 'delete_app_passwords':
		if($userID > 0) {
			BMAppPasswords::deleteAllForUser($userID);
			$message = 'Alle App-Passwords für User #' . $userID . ' wurden gelöscht.';
		}
		break;
		
	// Lösche alle WebAuthn-Credentials für User
	case 'delete_webauthn':
		if($userID > 0) {
			BMWebAuthn::deleteAllForUser($userID);
			$message = 'Alle WebAuthn-Credentials für User #' . $userID . ' wurden gelöscht.';
		}
		break;
		
	// Lösche alle Yubikeys für User
	case 'delete_yubikeys':
		if($userID > 0) {
			BMYubikey::deleteAllForUser($userID);
			$message = 'Alle Yubikeys für User #' . $userID . ' wurden gelöscht.';
		}
		break;
}

// Statistiken
$mfaStats = BMTOTP::getStats();
$appPasswordStats = BMAppPasswords::getStats();
$webauthnStats = BMWebAuthn::getStats();

// Template-Variablen
$tpl->assign('pageTitle', 'Security & 2FA Management');
$tpl->assign('action', $action);
$tpl->assign('message', $message);
$tpl->assign('mfaStats', $mfaStats);
$tpl->assign('appPasswordStats', $appPasswordStats);
$tpl->assign('webauthnStats', $webauthnStats);

if($action == 'user_details' && $userID > 0) {
	// User-Details
	$result = $db->Query('SELECT id, email, vorname, nachname, mfa_enabled FROM {pre}users WHERE id=?', $userID);
	$user = $result->FetchArray(MYSQLI_ASSOC);
	
	$tpl->assign('user', $user);
	$tpl->assign('appPasswords', BMAppPasswords::getList($userID));
	$tpl->assign('webauthnCreds', BMWebAuthn::getUserCredentials($userID));
	$tpl->assign('yubikeys', BMYubikey::getUserKeys($userID));
}

if($action == 'user_list') {
	// User-Liste mit MFA-Status
	$tpl->assign('users', BMTOTP::getUserList(100, 0));
}

$tpl->display('admin/security-management.tpl');
