<?php
/**
 * ModernFrontend CMS - Frontend Helper
 * Einfache Funktionen für Template-Verwendung
 */

if(!defined('B1GMAIL_INIT'))
	die('Access denied');

// ModernFrontend Instance laden
require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/ModernFrontend.class.php');
$mf = ModernFrontend::getInstance();

/**
 * Text übersetzen
 */
function mf_translate($text, $targetLang = null, $sourceLang = 'auto')
{
	global $mf;
	return $mf->translate($text, $targetLang, $sourceLang);
}

/**
 * Shorthand für Übersetzung
 */
function __mf($text, $targetLang = null)
{
	return mf_translate($text, $targetLang);
}

/**
 * Content-Array übersetzen
 */
function mf_translate_content($content, $targetLang = null, $sourceLang = 'auto')
{
	global $mf;
	return $mf->translateContent($content, $targetLang, $sourceLang);
}

/**
 * Aktuellen State abrufen
 */
function mf_get_state()
{
	global $mf;
	return $mf->getCurrentState();
}

/**
 * Aktuelle Domain
 */
function mf_get_domain()
{
	$state = mf_get_state();
	return $state['domain'];
}

/**
 * Aktuelles Design
 */
function mf_get_design()
{
	$state = mf_get_state();
	return $state['design'];
}

/**
 * Aktuelle Sprache
 */
function mf_get_language()
{
	$state = mf_get_state();
	return $state['language'];
}

/**
 * Sprach-Code abrufen
 */
function mf_get_language_code()
{
	$lang = mf_get_language();
	return $lang ? $lang['code'] : 'en';
}

/**
 * Design-Farbe abrufen
 */
function mf_get_primary_color()
{
	$design = mf_get_design();
	return $design ? $design['primary_color'] : '#76B82A';
}

function mf_get_secondary_color()
{
	$design = mf_get_design();
	return $design ? $design['secondary_color'] : '#5D9321';
}

/**
 * CSS URL abrufen
 */
function mf_get_css_url()
{
	global $mf;
	$design = mf_get_design();
	if(!$design) return '';
	
	return $mf->getThemeLoader()->getCSSUrl($design['folder']);
}

/**
 * JS URL abrufen
 */
function mf_get_js_url()
{
	global $mf;
	$design = mf_get_design();
	if(!$design) return '';
	
	return $mf->getThemeLoader()->getJSUrl($design['folder']);
}

/**
 * Language Switcher generieren
 */
function mf_language_switcher($style = 'dropdown', $includeFlags = true, $showFullName = false)
{
	require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/languages/LanguageSwitcher.class.php');
	$switcher = new LanguageSwitcher();
	
	return $switcher->render($style, $includeFlags, $showFullName);
}

/**
 * Debug-Informationen
 */
function mf_debug()
{
	global $mf;
	return $mf->getDebugInfo();
}

/**
 * System-Status
 */
function mf_status()
{
	global $mf;
	return $mf->checkSystemStatus();
}

// Branding API Integration: GetBrandingForDomain()
// Load domain-specific branding configuration
$brandingData = null;
if(function_exists('GetBrandingForDomain')) {
	$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
	$brandingData = GetBrandingForDomain($host);
}

// Smarty Template-Variablen registrieren (wenn Smarty vorhanden)
if(isset($tpl) && is_object($tpl)) {
	$mf->registerToSmarty($tpl);
	
	// Branding-Daten an Smarty übergeben
	if($brandingData !== null) {
		$tpl->assign('branding', $brandingData);
	}
}
?>
