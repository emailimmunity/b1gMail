<?php
/**
 * ModernFrontend CMS - Main Orchestrator
 * Verbindet alle Module zu einem einheitlichen System
 */

class ModernFrontend
{
	private static $instance = null;
	private $db;
	
	// Module Instances
	private $domainDetector = null;
	private $domainManager = null;
	private $designManager = null;
	private $themeLoader = null;
	private $languageDetector = null;
	private $languageManager = null;
	private $translationManager = null;
	
	// Current State
	private $currentDomain = null;
	private $currentDesign = null;
	private $currentLanguage = null;
	
	/**
	 * Singleton Pattern
	 */
	public static function getInstance()
	{
		if(self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Constructor (private für Singleton)
	 */
	private function __construct()
	{
		global $db;
		$this->db = $db;
		
		// NICHT Auto-Initialize - muss manuell aufgerufen werden
		// $this->initialize();
	}
	
	/**
	 * System initialisieren
	 */
	public function initialize()
	{
		try {
			// 1. Domain erkennen
			$this->currentDomain = $this->getDomainDetector()->detectDomain();
			
			// 2. Design laden (basierend auf Domain)
			if($this->currentDomain) {
				$designId = $this->currentDomain['design_id'];
				if($designId) {
					$this->currentDesign = $this->getDesignManager()->getDesignById($designId);
				}
			}
			
			// 3. Sprache erkennen (basierend auf Domain + User-Präferenz)
			$this->currentLanguage = $this->getLanguageDetector()->detectLanguage();
			
			// 4. Theme laden
			if($this->currentDesign) {
				$this->getThemeLoader()->loadTheme($this->currentDesign['folder']);
			}
		} catch(Exception $e) {
			// Fehler beim Initialisieren - Module müssen manuell geladen werden
		}
		
		return array(
			'domain' => $this->currentDomain,
			'design' => $this->currentDesign,
			'language' => $this->currentLanguage
		);
	}
	
	/**
	 * Domain Detector (Lazy Loading)
	 */
	public function getDomainDetector()
	{
		if($this->domainDetector === null) {
			require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/domains/DomainDetector.class.php');
			$this->domainDetector = new DomainDetector();
		}
		return $this->domainDetector;
	}
	
	/**
	 * Domain Manager
	 */
	public function getDomainManager()
	{
		if($this->domainManager === null) {
			require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/domains/DomainManager.class.php');
			$this->domainManager = new DomainManager();
		}
		return $this->domainManager;
	}
	
	/**
	 * Design Manager
	 */
	public function getDesignManager()
	{
		if($this->designManager === null) {
			require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/designs/DesignManager.class.php');
			$this->designManager = new DesignManager();
		}
		return $this->designManager;
	}
	
	/**
	 * Theme Loader
	 */
	public function getThemeLoader()
	{
		if($this->themeLoader === null) {
			require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/designs/ThemeLoader.class.php');
			$this->themeLoader = new ThemeLoader();
		}
		return $this->themeLoader;
	}
	
	/**
	 * Language Detector
	 */
	public function getLanguageDetector()
	{
		if($this->languageDetector === null) {
			require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/languages/LanguageDetector.class.php');
			$this->languageDetector = new LanguageDetector();
		}
		return $this->languageDetector;
	}
	
	/**
	 * Language Manager
	 */
	public function getLanguageManager()
	{
		if($this->languageManager === null) {
			require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/languages/LanguageManager.class.php');
			$this->languageManager = new LanguageManager();
		}
		return $this->languageManager;
	}
	
	/**
	 * Translation Manager
	 */
	public function getTranslationManager()
	{
		if($this->translationManager === null) {
			require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/translations/TranslationManager.class.php');
			$this->translationManager = new TranslationManager();
		}
		return $this->translationManager;
	}
	
	/**
	 * Aktuellen State abrufen
	 */
	public function getCurrentState()
	{
		return array(
			'domain' => $this->currentDomain,
			'design' => $this->currentDesign,
			'language' => $this->currentLanguage
		);
	}
	
	/**
	 * Text übersetzen (vereinfachte API)
	 */
	public function translate($text, $targetLang = null, $sourceLang = 'auto')
	{
		if($targetLang === null) {
			$targetLang = $this->currentLanguage ? $this->currentLanguage['code'] : 'en';
		}
		
		return $this->getTranslationManager()->translate($text, $targetLang, $sourceLang);
	}
	
	/**
	 * Mehrere Texte übersetzen
	 */
	public function translateBatch($texts, $targetLang = null, $sourceLang = 'auto')
	{
		if($targetLang === null) {
			$targetLang = $this->currentLanguage ? $this->currentLanguage['code'] : 'en';
		}
		
		return $this->getTranslationManager()->translateBatch($texts, $targetLang, $sourceLang);
	}
	
	/**
	 * Content-Array übersetzen
	 */
	public function translateContent($content, $targetLang = null, $sourceLang = 'auto')
	{
		if(!is_array($content)) {
			return $this->translate($content, $targetLang, $sourceLang);
		}
		
		$translated = array();
		foreach($content as $key => $value) {
			if(is_array($value)) {
				$translated[$key] = $this->translateContent($value, $targetLang, $sourceLang);
			} else {
				$translated[$key] = $this->translate($value, $targetLang, $sourceLang);
			}
		}
		
		return $translated;
	}
	
	/**
	 * Smarty Template-Variablen registrieren
	 */
	public function registerToSmarty(&$tpl)
	{
		// Current State
		$tpl->assign('mf_domain', $this->currentDomain);
		$tpl->assign('mf_design', $this->currentDesign);
		$tpl->assign('mf_language', $this->currentLanguage);
		
		// Helper Functions
		$tpl->assign('mf', $this);
		
		// Language Switcher
		if($this->currentLanguage) {
			$languageSwitcher = $this->getLanguageDetector()->generateLanguageSwitcher();
			$tpl->assign('mf_language_switcher', $languageSwitcher);
		}
		
		// Design Assets with Branding API Fallback
		if($this->currentDesign) {
			$themeLoader = $this->getThemeLoader();
			$tpl->assign('mf_css_url', $themeLoader->getCSSUrl($this->currentDesign['folder']));
			$tpl->assign('mf_js_url', $themeLoader->getJSUrl($this->currentDesign['folder']));
			$tpl->assign('mf_primary_color', $this->currentDesign['primary_color']);
			$tpl->assign('mf_secondary_color', $this->currentDesign['secondary_color']);
		} 
		// Fallback: Use Branding API if no ModernFrontend design is configured
		elseif(function_exists('GetBrandingForDomain')) {
			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
			$brandingData = GetBrandingForDomain($host);
			
			// Use Branding API colors as fallback
			$tpl->assign('mf_primary_color', $brandingData['primary_color']);
			$tpl->assign('mf_secondary_color', $brandingData['secondary_color']);
		}
		
		return true;
	}
	
	/**
	 * Debug-Informationen
	 */
	public function getDebugInfo()
	{
		return array(
			'domain' => $this->currentDomain ? $this->currentDomain['hostname'] : 'none',
			'design' => $this->currentDesign ? $this->currentDesign['name'] : 'none',
			'language' => $this->currentLanguage ? $this->currentLanguage['name'] . ' (' . $this->currentLanguage['code'] . ')' : 'none',
			'modules_loaded' => array(
				'DomainDetector' => $this->domainDetector !== null,
				'DomainManager' => $this->domainManager !== null,
				'DesignManager' => $this->designManager !== null,
				'ThemeLoader' => $this->themeLoader !== null,
				'LanguageDetector' => $this->languageDetector !== null,
				'LanguageManager' => $this->languageManager !== null,
				'TranslationManager' => $this->translationManager !== null
			)
		);
	}
	
	/**
	 * System-Status prüfen
	 */
	public function checkSystemStatus()
	{
		$status = array(
			'overall' => 'ok',
			'modules' => array(),
			'database' => array(),
			'errors' => array()
		);
		
		// Check Database Tables
		$tables = array(
			'bm60_mf_domains',
			'bm60_mf_designs',
			'bm60_mf_languages',
			'bm60_mf_domain_languages',
			'bm60_mf_translations',
			'bm60_mf_api_credentials'
		);
		
		foreach($tables as $table) {
			$result = $this->db->Query("SELECT COUNT(*) as c FROM information_schema.tables 
										WHERE table_schema = DATABASE() 
										AND table_name = '$table'");
			$row = $result->FetchArray(MYSQLI_ASSOC);
			$status['database'][$table] = ($row['c'] > 0) ? 'ok' : 'missing';
			
			if($row['c'] == 0) {
				$status['overall'] = 'error';
				$status['errors'][] = "Table $table missing";
			}
		}
		
		// Check Modules
		$moduleFiles = array(
			'DomainManager' => 'modules/domains/DomainManager.class.php',
			'DomainDetector' => 'modules/domains/DomainDetector.class.php',
			'DesignManager' => 'modules/designs/DesignManager.class.php',
			'ThemeLoader' => 'modules/designs/ThemeLoader.class.php',
			'LanguageManager' => 'modules/languages/LanguageManager.class.php',
			'LanguageDetector' => 'modules/languages/LanguageDetector.class.php',
			'TranslationManager' => 'modules/translations/TranslationManager.class.php'
		);
		
		foreach($moduleFiles as $name => $file) {
			$path = B1GMAIL_DIR . 'plugins/modernfrontend/' . $file;
			$status['modules'][$name] = file_exists($path) ? 'ok' : 'missing';
			
			if(!file_exists($path)) {
				$status['overall'] = 'error';
				$status['errors'][] = "Module $name missing";
			}
		}
		
		return $status;
	}
}
?>
