<?php
/**
 * ModernFrontend CMS - Language Detector
 * Automatische Spracherkennung
 */

class LanguageDetector
{
	private $languageManager;
	private $domainDetector;
	private $currentLanguage = null;
	private $cookieName = 'mf_language';
	
	public function __construct()
	{
		require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/languages/LanguageManager.class.php');
		require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/domains/DomainDetector.class.php');
		
		$this->languageManager = new LanguageManager();
		$this->domainDetector = new DomainDetector();
	}
	
	/**
	 * Aktuelle Sprache erkennen
	 * Priorität: URL-Parameter > Cookie > Browser > Domain Default > Fallback
	 */
	public function detectLanguage()
	{
		// 1. URL-Parameter (?lang=de)
		if(isset($_GET['lang']) && !empty($_GET['lang'])) {
			$lang = $this->languageManager->getLanguageByCode($_GET['lang']);
			if($lang && $lang['is_active']) {
				$this->setLanguageCookie($lang['code']);
				$this->currentLanguage = $lang;
				return $lang;
			}
		}
		
		// 2. Cookie
		if(isset($_COOKIE[$this->cookieName]) && !empty($_COOKIE[$this->cookieName])) {
			$lang = $this->languageManager->getLanguageByCode($_COOKIE[$this->cookieName]);
			if($lang && $lang['is_active']) {
				$this->currentLanguage = $lang;
				return $lang;
			}
		}
		
		// 3. Session
		if(isset($_SESSION['mf_language']) && !empty($_SESSION['mf_language'])) {
			$lang = $this->languageManager->getLanguageByCode($_SESSION['mf_language']);
			if($lang && $lang['is_active']) {
				$this->currentLanguage = $lang;
				return $lang;
			}
		}
		
		// 4. Browser Accept-Language
		$browserLang = $this->detectBrowserLanguage();
		if($browserLang) {
			$lang = $this->languageManager->getLanguageByCode($browserLang);
			if($lang && $lang['is_active']) {
				$this->setLanguageCookie($lang['code']);
				$this->currentLanguage = $lang;
				return $lang;
			}
		}
		
		// 5. Domain Default Language
		$domain = $this->domainDetector->detectDomain();
		if($domain && isset($domain['language_id'])) {
			$lang = $this->languageManager->getLanguageById($domain['language_id']);
			if($lang && $lang['is_active']) {
				$this->setLanguageCookie($lang['code']);
				$this->currentLanguage = $lang;
				return $lang;
			}
		}
		
		// 6. Fallback auf Englisch
		$lang = $this->languageManager->getLanguageByCode('en');
		if($lang) {
			$this->setLanguageCookie($lang['code']);
			$this->currentLanguage = $lang;
			return $lang;
		}
		
		// 7. Erste verfügbare Sprache
		$languages = $this->languageManager->getAllLanguages(true);
		if(!empty($languages)) {
			$this->setLanguageCookie($languages[0]['code']);
			$this->currentLanguage = $languages[0];
			return $languages[0];
		}
		
		return null;
	}
	
	/**
	 * Browser-Sprache erkennen aus Accept-Language Header
	 */
	private function detectBrowserLanguage()
	{
		if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			return null;
		}
		
		$acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		
		// Parse Accept-Language: de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7
		preg_match_all('/([a-z]{2})(-[A-Z]{2})?(;q=([0-9.]+))?/', $acceptLang, $matches);
		
		if(!empty($matches[1])) {
			// Erste Sprache zurückgeben
			return strtolower($matches[1][0]);
		}
		
		return null;
	}
	
	/**
	 * Sprache setzen
	 */
	public function setLanguage($languageCode)
	{
		$lang = $this->languageManager->getLanguageByCode($languageCode);
		
		if($lang && $lang['is_active']) {
			$this->setLanguageCookie($lang['code']);
			$_SESSION['mf_language'] = $lang['code'];
			$this->currentLanguage = $lang;
			return true;
		}
		
		return false;
	}
	
	/**
	 * Cookie setzen (30 Tage)
	 */
	private function setLanguageCookie($languageCode)
	{
		if(!headers_sent()) {
			setcookie($this->cookieName, $languageCode, time() + (30 * 24 * 60 * 60), '/');
		}
	}
	
	/**
	 * Aktuelle Sprache abrufen
	 */
	public function getCurrentLanguage()
	{
		if($this->currentLanguage === null) {
			$this->detectLanguage();
		}
		
		return $this->currentLanguage;
	}
	
	/**
	 * Aktueller Sprach-Code
	 */
	public function getCurrentLanguageCode()
	{
		$lang = $this->getCurrentLanguage();
		return $lang ? $lang['code'] : 'en';
	}
	
	/**
	 * Aktueller Sprach-Name
	 */
	public function getCurrentLanguageName()
	{
		$lang = $this->getCurrentLanguage();
		return $lang ? $lang['name'] : 'English';
	}
	
	/**
	 * Aktuelle Sprach-ID
	 */
	public function getCurrentLanguageId()
	{
		$lang = $this->getCurrentLanguage();
		return $lang ? intval($lang['id']) : 1;
	}
	
	/**
	 * Verfügbare Sprachen für aktuelle Domain
	 */
	public function getAvailableLanguages()
	{
		$domain = $this->domainDetector->detectDomain();
		
		if($domain && isset($domain['id'])) {
			return $this->languageManager->getLanguagesForDomain($domain['id']);
		}
		
		// Fallback: Alle aktiven Sprachen
		return $this->languageManager->getAllLanguages(true);
	}
	
	/**
	 * Ist Sprache verfügbar?
	 */
	public function isLanguageAvailable($languageCode)
	{
		$available = $this->getAvailableLanguages();
		
		foreach($available as $lang) {
			if($lang['code'] == $languageCode) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * URL mit Sprach-Parameter
	 */
	public function getLanguageUrl($languageCode, $url = null)
	{
		if($url === null) {
			$url = $_SERVER['REQUEST_URI'];
		}
		
		// Remove existing lang parameter
		$url = preg_replace('/[?&]lang=[a-z]{2}/', '', $url);
		
		// Add new lang parameter
		$separator = (strpos($url, '?') !== false) ? '&' : '?';
		return $url . $separator . 'lang=' . $languageCode;
	}
	
	/**
	 * Sprach-Info für Template
	 */
	public function getLanguageVars()
	{
		$current = $this->getCurrentLanguage();
		$available = $this->getAvailableLanguages();
		
		return array(
			'current_language' => $current,
			'current_language_code' => $this->getCurrentLanguageCode(),
			'current_language_name' => $this->getCurrentLanguageName(),
			'current_language_id' => $this->getCurrentLanguageId(),
			'available_languages' => $available,
			'language_switcher_html' => $this->generateLanguageSwitcher()
		);
	}
	
	/**
	 * Language-Switcher HTML generieren
	 */
	public function generateLanguageSwitcher()
	{
		$available = $this->getAvailableLanguages();
		$current = $this->getCurrentLanguageCode();
		
		$html = '<div class="language-switcher">';
		$html .= '<select onchange="window.location.href=this.value;" class="language-select">';
		
		foreach($available as $lang) {
			$selected = ($lang['code'] == $current) ? ' selected' : '';
			$url = $this->getLanguageUrl($lang['code']);
			$html .= '<option value="' . htmlspecialchars($url) . '"' . $selected . '>';
			$html .= htmlspecialchars($lang['name']);
			$html .= '</option>';
		}
		
		$html .= '</select>';
		$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * Sprach-Variablen in Smarty registrieren
	 */
	public function registerToSmarty($tpl)
	{
		$vars = $this->getLanguageVars();
		
		foreach($vars as $key => $value) {
			$tpl->assign($key, $value);
		}
	}
	
	/**
	 * Als Array
	 */
	public function toArray()
	{
		return $this->getLanguageVars();
	}
}
?>
