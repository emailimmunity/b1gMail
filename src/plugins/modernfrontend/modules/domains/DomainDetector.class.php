<?php
/**
 * ModernFrontend CMS - Domain Detector
 * Erkennt automatisch die aktuelle Domain und lädt Einstellungen
 */

class DomainDetector
{
	private $domainManager;
	private $currentDomain = null;
	private $currentHostname = null;
	
	public function __construct()
	{
		require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/domains/DomainManager.class.php');
		$this->domainManager = new DomainManager();
	}
	
	/**
	 * Aktuelle Domain erkennen
	 */
	public function detectDomain()
	{
		// Hostname ermitteln
		$this->currentHostname = $this->getCurrentHostname();
		
		// Domain aus Datenbank laden
		$this->currentDomain = $this->domainManager->getDomainByHostname($this->currentHostname);
		
		// Fallback auf localhost
		if(!$this->currentDomain && $this->currentHostname != 'localhost') {
			$this->currentDomain = $this->domainManager->getDomainByHostname('localhost');
		}
		
		return $this->currentDomain;
	}
	
	/**
	 * Aktuellen Hostname ermitteln
	 */
	private function getCurrentHostname()
	{
		// HTTP_HOST bevorzugen
		if(isset($_SERVER['HTTP_HOST'])) {
			$host = $_SERVER['HTTP_HOST'];
			
			// Port entfernen falls vorhanden
			if(strpos($host, ':') !== false) {
				$host = substr($host, 0, strpos($host, ':'));
			}
			
			return strtolower($host);
		}
		
		// Fallback auf SERVER_NAME
		if(isset($_SERVER['SERVER_NAME'])) {
			return strtolower($_SERVER['SERVER_NAME']);
		}
		
		// Fallback
		return 'localhost';
	}
	
	/**
	 * Aktuelle Domain abrufen
	 */
	public function getCurrentDomain()
	{
		if($this->currentDomain === null) {
			$this->detectDomain();
		}
		
		return $this->currentDomain;
	}
	
	/**
	 * Aktuellen Hostname abrufen
	 */
	public function getCurrentHostname()
	{
		if($this->currentHostname === null) {
			$this->currentHostname = $this->getCurrentHostname();
		}
		
		return $this->currentHostname;
	}
	
	/**
	 * Design-ID der aktuellen Domain
	 */
	public function getDesignId()
	{
		$domain = $this->getCurrentDomain();
		return $domain ? $domain['design_id'] : null;
	}
	
	/**
	 * Standard-Sprache der aktuellen Domain
	 */
	public function getDefaultLanguageId()
	{
		$domain = $this->getCurrentDomain();
		return $domain ? $domain['default_language_id'] : 1;
	}
	
	/**
	 * Standard-Sprachcode der aktuellen Domain
	 */
	public function getDefaultLanguageCode()
	{
		$domain = $this->getCurrentDomain();
		return $domain ? $domain['language_code'] : 'de';
	}
	
	/**
	 * Status der aktuellen Domain
	 */
	public function getDomainStatus()
	{
		$domain = $this->getCurrentDomain();
		return $domain ? $domain['status'] : 'inactive';
	}
	
	/**
	 * Prüfe ob Domain aktiv ist
	 */
	public function isDomainActive()
	{
		return $this->getDomainStatus() === 'active';
	}
	
	/**
	 * Prüfe ob Domain in Wartung ist
	 */
	public function isDomainInMaintenance()
	{
		return $this->getDomainStatus() === 'maintenance';
	}
	
	/**
	 * SSL aktiviert?
	 */
	public function isSslEnabled()
	{
		$domain = $this->getCurrentDomain();
		return $domain ? (bool)$domain['ssl_enabled'] : false;
	}
	
	/**
	 * Custom Config der Domain abrufen
	 */
	public function getCustomConfig()
	{
		$domain = $this->getCurrentDomain();
		if($domain && $domain['custom_config']) {
			return json_decode($domain['custom_config'], true);
		}
		return array();
	}
	
	/**
	 * Design-Settings abrufen
	 */
	public function getDesignSettings()
	{
		$domain = $this->getCurrentDomain();
		if($domain && isset($domain['design_settings'])) {
			return json_decode($domain['design_settings'], true);
		}
		return array();
	}
	
	/**
	 * Primary Color abrufen
	 */
	public function getPrimaryColor()
	{
		$domain = $this->getCurrentDomain();
		return $domain ? $domain['primary_color'] : '#76B82A';
	}
	
	/**
	 * Secondary Color abrufen
	 */
	public function getSecondaryColor()
	{
		$domain = $this->getCurrentDomain();
		return $domain ? $domain['secondary_color'] : '#333333';
	}
	
	/**
	 * Logo-Pfad abrufen
	 */
	public function getLogoPath()
	{
		$domain = $this->getCurrentDomain();
		return $domain ? $domain['logo_path'] : null;
	}
	
	/**
	 * Template-Pfad abrufen
	 */
	public function getTemplatePath()
	{
		$domain = $this->getCurrentDomain();
		return $domain ? $domain['template_path'] : 'designs/aikq-default/';
	}
	
	/**
	 * CSS-Datei abrufen
	 */
	public function getCssFile()
	{
		$domain = $this->getCurrentDomain();
		return $domain ? $domain['css_file'] : null;
	}
	
	/**
	 * Verfügbare Sprachen für aktuelle Domain
	 */
	public function getAvailableLanguages()
	{
		$domain = $this->getCurrentDomain();
		if(!$domain) {
			return array();
		}
		
		return $this->domainManager->getDomainLanguages($domain['id']);
	}
	
	/**
	 * Domain-Info als Array
	 */
	public function toArray()
	{
		return array(
			'hostname' => $this->getCurrentHostname(),
			'domain' => $this->getCurrentDomain(),
			'design_id' => $this->getDesignId(),
			'language_id' => $this->getDefaultLanguageId(),
			'language_code' => $this->getDefaultLanguageCode(),
			'status' => $this->getDomainStatus(),
			'is_active' => $this->isDomainActive(),
			'is_maintenance' => $this->isDomainInMaintenance(),
			'ssl_enabled' => $this->isSslEnabled(),
			'primary_color' => $this->getPrimaryColor(),
			'secondary_color' => $this->getSecondaryColor(),
			'logo_path' => $this->getLogoPath(),
			'template_path' => $this->getTemplatePath(),
			'css_file' => $this->getCssFile(),
			'custom_config' => $this->getCustomConfig(),
			'design_settings' => $this->getDesignSettings(),
			'available_languages' => $this->getAvailableLanguages()
		);
	}
}
?>
