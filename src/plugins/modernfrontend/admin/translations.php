<?php
/**
 * ModernFrontend CMS - Übersetzungs-Verwaltung
 */

if(!defined('B1GMAIL_INIT'))
	die('Access denied');

// Security: Plugin is called from admin framework, no additional checks needed

// Load TranslationManager
require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/translations/TranslationManager.class.php');
require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/languages/LanguageManager.class.php');

$translationManager = new TranslationManager();
$languageManager = new LanguageManager();

// Action handling
$action = isset($_REQUEST['do']) ? $_REQUEST['do'] : 'list';
$message = '';
$messageType = '';

// SAVE API CREDENTIALS
if($action == 'save_api' && isset($_POST['submit'])) {
	$provider = $_POST['provider'];
	$apiKey = $_POST['api_key'];
	$settings = array();
	
	if($provider == 'deepl' && isset($_POST['is_pro'])) {
		$settings['is_pro'] = ($_POST['is_pro'] == '1');
	}
	
	$result = $translationManager->saveApiCredentials($provider, $apiKey, $settings);
	
	if($result['success']) {
		$message = 'API-Credentials erfolgreich gespeichert!';
		$messageType = 'success';
	} else {
		$message = 'Fehler beim Speichern: ' . $result['error'];
		$messageType = 'error';
	}
	$action = 'api';
}

// TOGGLE API
if($action == 'toggle_api' && isset($_GET['provider'])) {
	$translationManager->toggleApiCredentials($_GET['provider']);
	$message = 'API-Status geändert!';
	$messageType = 'success';
	$action = 'api';
}

// TEST TRANSLATION
if($action == 'test' && isset($_POST['test_submit'])) {
	$text = $_POST['text'];
	$targetLang = $_POST['target_lang'];
	$sourceLang = isset($_POST['source_lang']) ? $_POST['source_lang'] : 'auto';
	$provider = $_POST['provider'];
	
	$translatedText = $translationManager->translate($text, $targetLang, $sourceLang, $provider);
	
	$tpl->assign('testResult', array(
		'original' => $text,
		'translated' => $translatedText,
		'target_lang' => $targetLang,
		'source_lang' => $sourceLang,
		'provider' => $provider
	));
	$action = 'test';
}

// UPDATE TRANSLATION
if($action == 'update' && isset($_POST['update_submit'])) {
	$translationManager->updateTranslation($_POST['id'], $_POST['translated_text']);
	$message = 'Übersetzung aktualisiert!';
	$messageType = 'success';
	$action = 'list';
}

// DELETE TRANSLATION
if($action == 'delete' && isset($_GET['id'])) {
	$translationManager->deleteTranslation($_GET['id']);
	$message = 'Übersetzung gelöscht!';
	$messageType = 'success';
	$action = 'list';
}

// CLEAR CACHE
if($action == 'clear_cache' && isset($_POST['clear_submit'])) {
	$targetLang = !empty($_POST['target_lang']) ? $_POST['target_lang'] : null;
	$translationManager->clearTranslationCache($targetLang);
	$message = 'Cache geleert!';
	$messageType = 'success';
	$action = 'list';
}

// Daten für Template
if($action == 'list') {
	$filters = array();
	if(isset($_GET['target_lang'])) $filters['target_language'] = $_GET['target_lang'];
	if(isset($_GET['provider'])) $filters['api_provider'] = $_GET['provider'];
	if(isset($_GET['search'])) $filters['search'] = $_GET['search'];
	
	$limit = 50;
	$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
	
	$translations = $translationManager->getAllTranslations($limit, $offset, $filters);
	$totalCount = $translationManager->getTranslationsCount($filters);
	$statistics = $translationManager->getStatistics();
	
	$tpl->assign('translations', $translations);
	$tpl->assign('totalCount', $totalCount);
	$tpl->assign('statistics', $statistics);
	$tpl->assign('filters', $filters);
	$tpl->assign('offset', $offset);
	$tpl->assign('limit', $limit);
}

if($action == 'api') {
	$providers = $translationManager->getAllProviders();
	$tpl->assign('providers', $providers);
	
	// Status Check
	$statusChecks = array();
	foreach($providers as $provider) {
		if($provider['is_active'] && !empty($provider['api_key'])) {
			$engine = $translationManager->getEngine($provider['provider']);
			if($engine) {
				$statusChecks[$provider['provider']] = $engine->checkStatus();
			}
		}
	}
	$tpl->assign('statusChecks', $statusChecks);
}

if($action == 'test') {
	$languages = $languageManager->getAllLanguages(true);
	$providers = $translationManager->getAllProviders();
	$tpl->assign('languages', $languages);
	$tpl->assign('providers', $providers);
}

if($action == 'edit' && isset($_GET['id'])) {
	$translation = $translationManager->getTranslation($_GET['id']);
	$tpl->assign('translation', $translation);
}

// Template-Variablen
$tpl->assign('action', $action);
$tpl->assign('message', $message);
$tpl->assign('messageType', $messageType);
?>
