<?php
/**
 * ModernFrontend CMS - Sprach-Verwaltung
 */

if(!defined('B1GMAIL_INIT'))
	die('Access denied');

// Security: Plugin is called from admin framework, no additional checks needed

// Load LanguageManager
require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/languages/LanguageManager.class.php');
$languageManager = new LanguageManager();

// Action handling
$action = isset($_REQUEST['do']) ? $_REQUEST['do'] : 'list';
$message = '';
$messageType = '';

// ACTIVATE/DEACTIVATE
if($action == 'toggle' && isset($_GET['id'])) {
	$lang = $languageManager->getLanguageById($_GET['id']);
	if($lang) {
		$newStatus = ($lang['is_active'] == 1) ? 0 : 1;
		$result = $languageManager->updateLanguageStatus($_GET['id'], $newStatus);
		
		if($result['success']) {
			$message = 'Sprach-Status erfolgreich geändert!';
			$messageType = 'success';
		} else {
			$message = 'Fehler: ' . $result['error'];
			$messageType = 'error';
		}
	}
	$action = 'list';
}

// UPDATE FLAG
if($action == 'update_flag' && isset($_POST['submit'])) {
	$result = $languageManager->updateLanguageFlag($_POST['id'], $_POST['flag_icon']);
	
	if($result['success']) {
		$message = 'Flagge erfolgreich aktualisiert!';
		$messageType = 'success';
	} else {
		$message = 'Fehler: ' . $result['error'];
		$messageType = 'error';
	}
	$action = 'list';
}

// Daten für Template vorbereiten
if($action == 'list') {
	$languages = $languageManager->getAllLanguages();
	$languagesByRegion = $languageManager->getLanguagesByRegion();
	$mostUsed = $languageManager->getMostUsedLanguages(10);
	
	// Usage-Count zu allen Sprachen hinzufügen
	$usageMap = array();
	foreach($mostUsed as $lang) {
		$usageMap[$lang['id']] = $lang['usage_count'];
	}
	
	foreach($languages as &$lang) {
		$lang['usage_count'] = isset($usageMap[$lang['id']]) ? $usageMap[$lang['id']] : 0;
	}
	
	$tpl->assign('languages', $languages);
	$tpl->assign('languagesByRegion', $languagesByRegion);
	$tpl->assign('mostUsed', $mostUsed);
	$tpl->assign('totalCount', $languageManager->getTotalLanguagesCount());
	$tpl->assign('activeCount', $languageManager->getActiveLanguagesCount());
}

// Edit: Sprache laden
$editLanguage = null;
if($action == 'edit' && isset($_GET['id'])) {
	$editLanguage = $languageManager->getLanguageById($_GET['id']);
}

// An Template übergeben
$tpl->assign('action', $action);
$tpl->assign('editLanguage', $editLanguage);
$tpl->assign('message', $message);
$tpl->assign('messageType', $messageType);
?>
