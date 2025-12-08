<?php
/**
 * ModernFrontend CMS - Domain Verwaltung
 */

if(!defined('B1GMAIL_INIT'))
	die('Access denied');

// Security: Plugin is called from admin framework, no additional checks needed

// Load DomainManager
require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/domains/DomainManager.class.php');
$domainManager = new DomainManager();

// Action handling
$action = isset($_REQUEST['do']) ? $_REQUEST['do'] : 'list';
$message = '';
$messageType = '';

// CREATE
if($action == 'create' && isset($_POST['submit'])) {
	$result = $domainManager->createDomain(array(
		'domain' => $_POST['domain'],
		'design_id' => $_POST['design_id'],
		'default_language_id' => $_POST['default_language_id'],
		'status' => $_POST['status'],
		'ssl_enabled' => isset($_POST['ssl_enabled']) ? 1 : 0
	));
	
	if($result['success']) {
		$message = 'Domain erfolgreich erstellt!';
		$messageType = 'success';
		$action = 'list';
	} else {
		$message = 'Fehler: ' . $result['error'];
		$messageType = 'error';
	}
}

// UPDATE
if($action == 'edit' && isset($_POST['submit'])) {
	$result = $domainManager->updateDomain($_POST['id'], array(
		'domain' => $_POST['domain'],
		'design_id' => $_POST['design_id'],
		'default_language_id' => $_POST['default_language_id'],
		'status' => $_POST['status'],
		'ssl_enabled' => isset($_POST['ssl_enabled']) ? 1 : 0
	));
	
	if($result['success']) {
		$message = 'Domain erfolgreich aktualisiert!';
		$messageType = 'success';
		$action = 'list';
	} else {
		$message = 'Fehler: ' . $result['error'];
		$messageType = 'error';
	}
}

// DELETE
if($action == 'delete' && isset($_GET['id'])) {
	$result = $domainManager->deleteDomain($_GET['id']);
	
	if($result['success']) {
		$message = 'Domain erfolgreich gelöscht!';
		$messageType = 'success';
	} else {
		$message = 'Fehler: ' . $result['error'];
		$messageType = 'error';
	}
	$action = 'list';
}

// Daten für Template vorbereiten
$domains = $domainManager->getAllDomains(true);

// Designs laden
$designs = array();
$result = $db->Query("SELECT id, name FROM {pre}mf_designs WHERE is_active = 1 ORDER BY name");
while($row = $result->FetchArray(MYSQLI_ASSOC)) {
	$designs[] = $row;
}

// Sprachen laden
$languages = array();
$result = $db->Query("SELECT id, name, native_name FROM {pre}mf_languages WHERE is_active = 1 ORDER BY sort_order");
while($row = $result->FetchArray(MYSQLI_ASSOC)) {
	$languages[] = $row;
}

// Edit: Domain laden
$editDomain = null;
if($action == 'edit' && isset($_GET['id'])) {
	$editDomain = $domainManager->getDomainById($_GET['id']);
}

// An Template übergeben
$tpl->assign('action', $action);
$tpl->assign('domains', $domains);
$tpl->assign('designs', $designs);
$tpl->assign('languages', $languages);
$tpl->assign('editDomain', $editDomain);
$tpl->assign('message', $message);
$tpl->assign('messageType', $messageType);
$tpl->assign('domainsCount', count($domains));
?>
