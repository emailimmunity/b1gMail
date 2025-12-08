<?php
/**
 * ModernFrontend CMS - Translation Manager
 * CRUD-Operationen für Übersetzungen und API-Verwaltung
 */

class TranslationManager
{
	private $db;
	private $cache = array();
	private $translationEngine = null;
	
	public function __construct()
	{
		global $db;
		$this->db = $db;
	}
	
	/**
	 * Übersetzungs-Engine initialisieren
	 */
	public function getEngine($provider = 'google_translate')
	{
		if($this->translationEngine !== null && $this->translationEngine->getApiName() == $provider) {
			return $this->translationEngine;
		}
		
		// API-Credentials laden
		$credentials = $this->getApiCredentials($provider);
		
		if(!$credentials) {
			return null;
		}
		
		// Engine laden
		if($provider == 'google_translate') {
			require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/translations/GoogleTranslateEngine.class.php');
			$this->translationEngine = new GoogleTranslateEngine($credentials['api_key']);
		}
		else if($provider == 'deepl') {
			require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/translations/DeepLEngine.class.php');
			$isPro = isset($credentials['settings']['is_pro']) ? $credentials['settings']['is_pro'] : false;
			$this->translationEngine = new DeepLEngine($credentials['api_key'], $isPro);
		}
		
		return $this->translationEngine;
	}
	
	/**
	 * Text übersetzen
	 */
	public function translate($text, $targetLang, $sourceLang = 'auto', $provider = null)
	{
		if($provider === null) {
			$provider = $this->getDefaultProvider();
		}
		
		$engine = $this->getEngine($provider);
		
		if(!$engine) {
			return '[Übersetzungs-Engine nicht verfügbar]';
		}
		
		return $engine->translate($text, $targetLang, $sourceLang);
	}
	
	/**
	 * Mehrere Texte übersetzen
	 */
	public function translateBatch($texts, $targetLang, $sourceLang = 'auto', $provider = null)
	{
		if($provider === null) {
			$provider = $this->getDefaultProvider();
		}
		
		$engine = $this->getEngine($provider);
		
		if(!$engine) {
			$results = array();
			foreach($texts as $key => $text) {
				$results[$key] = '[Übersetzungs-Engine nicht verfügbar]';
			}
			return $results;
		}
		
		return $engine->translateBatch($texts, $targetLang, $sourceLang);
	}
	
	/**
	 * Übersetzung aus DB abrufen
	 */
	public function getTranslation($id)
	{
		$id = (int)$id;
		
		$result = $this->db->Query("SELECT * FROM {pre}mf_translations WHERE id = $id");
		
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			return $row;
		}
		
		return null;
	}
	
	/**
	 * Übersetzung suchen
	 */
	public function findTranslation($originalText, $targetLang, $sourceLang = 'auto', $provider = null)
	{
		$originalTextEscaped = $this->db->Escape($originalText);
		$targetLangEscaped = $this->db->Escape($targetLang);
		$sourceLangEscaped = $this->db->Escape($sourceLang);
		
		$query = "SELECT * FROM {pre}mf_translations 
				  WHERE original_text = '$originalTextEscaped' 
				  AND target_language = '$targetLangEscaped' 
				  AND source_language = '$sourceLangEscaped'";
		
		if($provider !== null) {
			$providerEscaped = $this->db->Escape($provider);
			$query .= " AND api_provider = '$providerEscaped'";
		}
		
		$query .= " LIMIT 1";
		
		$result = $this->db->Query($query);
		
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			return $row;
		}
		
		return null;
	}
	
	/**
	 * Übersetzung aktualisieren
	 */
	public function updateTranslation($id, $translatedText)
	{
		$id = (int)$id;
		$translatedTextEscaped = $this->db->Escape($translatedText);
		
		$this->db->Query("UPDATE {pre}mf_translations 
						  SET translated_text = '$translatedTextEscaped',
						      updated_at = NOW()
						  WHERE id = $id");
		
		return array('success' => true);
	}
	
	/**
	 * Übersetzung löschen
	 */
	public function deleteTranslation($id)
	{
		$id = (int)$id;
		
		$this->db->Query("DELETE FROM {pre}mf_translations WHERE id = $id");
		
		return array('success' => true);
	}
	
	/**
	 * Alle Übersetzungen abrufen
	 */
	public function getAllTranslations($limit = 100, $offset = 0, $filters = array())
	{
		$limit = (int)$limit;
		$offset = (int)$offset;
		
		$query = "SELECT * FROM {pre}mf_translations WHERE 1=1";
		
		if(isset($filters['target_language'])) {
			$targetLang = $this->db->Escape($filters['target_language']);
			$query .= " AND target_language = '$targetLang'";
		}
		
		if(isset($filters['source_language'])) {
			$sourceLang = $this->db->Escape($filters['source_language']);
			$query .= " AND source_language = '$sourceLang'";
		}
		
		if(isset($filters['api_provider'])) {
			$provider = $this->db->Escape($filters['api_provider']);
			$query .= " AND api_provider = '$provider'";
		}
		
		if(isset($filters['search'])) {
			$search = $this->db->Escape($filters['search']);
			$query .= " AND (original_text LIKE '%$search%' OR translated_text LIKE '%$search%')";
		}
		
		$query .= " ORDER BY updated_at DESC LIMIT $limit OFFSET $offset";
		
		$result = $this->db->Query($query);
		$translations = array();
		
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$translations[] = $row;
		}
		
		return $translations;
	}
	
	/**
	 * Anzahl Übersetzungen
	 */
	public function getTranslationsCount($filters = array())
	{
		$query = "SELECT COUNT(*) as count FROM {pre}mf_translations WHERE 1=1";
		
		if(isset($filters['target_language'])) {
			$targetLang = $this->db->Escape($filters['target_language']);
			$query .= " AND target_language = '$targetLang'";
		}
		
		if(isset($filters['source_language'])) {
			$sourceLang = $this->db->Escape($filters['source_language']);
			$query .= " AND source_language = '$sourceLang'";
		}
		
		if(isset($filters['api_provider'])) {
			$provider = $this->db->Escape($filters['api_provider']);
			$query .= " AND api_provider = '$provider'";
		}
		
		$result = $this->db->Query($query);
		$row = $result->FetchArray(MYSQLI_ASSOC);
		
		return (int)$row['count'];
	}
	
	/**
	 * API-Credentials abrufen
	 */
	public function getApiCredentials($provider)
	{
		$providerEscaped = $this->db->Escape($provider);
		
		$result = $this->db->Query("SELECT * FROM {pre}mf_api_credentials 
									WHERE api_provider = '$providerEscaped' 
									AND is_active = 1 
									LIMIT 1");
		
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			if(!empty($row['settings'])) {
				$row['settings'] = json_decode($row['settings'], true);
			}
			return $row;
		}
		
		return null;
	}
	
	/**
	 * API-Credentials speichern/aktualisieren
	 */
	public function saveApiCredentials($provider, $apiKey, $settings = array())
	{
		$providerEscaped = $this->db->Escape($provider);
		$apiKeyEscaped = $this->db->Escape($apiKey);
		$settingsJson = json_encode($settings);
		$settingsEscaped = $this->db->Escape($settingsJson);
		
		// Check if exists
		$existing = $this->getApiCredentials($provider);
		
		if($existing) {
			// Update
			$this->db->Query("UPDATE {pre}mf_api_credentials 
							  SET api_key = '$apiKeyEscaped',
							      settings = '$settingsEscaped',
							      updated_at = NOW()
							  WHERE api_provider = '$providerEscaped'");
		} else {
			// Insert
			$this->db->Query("INSERT INTO {pre}mf_api_credentials 
							  (api_provider, api_key, settings, is_active, created_at, updated_at)
							  VALUES 
							  ('$providerEscaped', '$apiKeyEscaped', '$settingsEscaped', 1, NOW(), NOW())");
		}
		
		return array('success' => true);
	}
	
	/**
	 * API-Credentials Status ändern
	 */
	public function toggleApiCredentials($provider)
	{
		$providerEscaped = $this->db->Escape($provider);
		
		$this->db->Query("UPDATE {pre}mf_api_credentials 
						  SET is_active = 1 - is_active 
						  WHERE api_provider = '$providerEscaped'");
		
		return array('success' => true);
	}
	
	/**
	 * Standard-Provider abrufen
	 */
	public function getDefaultProvider()
	{
		// Erst DeepL prüfen
		$deepl = $this->getApiCredentials('deepl');
		if($deepl && $deepl['is_active']) {
			return 'deepl';
		}
		
		// Dann Google Translate
		$google = $this->getApiCredentials('google_translate');
		if($google && $google['is_active']) {
			return 'google_translate';
		}
		
		return 'google_translate'; // Fallback
	}
	
	/**
	 * Alle API-Provider abrufen
	 */
	public function getAllProviders()
	{
		$result = $this->db->Query("SELECT * FROM {pre}mf_api_credentials ORDER BY api_provider");
		$providers = array();
		
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			if(!empty($row['settings'])) {
				$row['settings'] = json_decode($row['settings'], true);
			}
			$providers[] = $row;
		}
		
		return $providers;
	}
	
	/**
	 * Statistiken
	 */
	public function getStatistics()
	{
		// Gesamt-Anzahl
		$totalResult = $this->db->Query("SELECT COUNT(*) as count FROM {pre}mf_translations");
		$totalRow = $totalResult->FetchArray(MYSQLI_ASSOC);
		
		// Pro Provider
		$providerResult = $this->db->Query("SELECT provider, COUNT(*) as count 
											FROM {pre}mf_translations 
											GROUP BY provider");
		$byProvider = array();
		while($row = $providerResult->FetchArray(MYSQLI_ASSOC)) {
			$byProvider[$row['provider']] = $row['count'];
		}
		
		// Pro Zielsprache
		$langResult = $this->db->Query("SELECT language_id, COUNT(*) as count 
										FROM {pre}mf_translations 
										GROUP BY language_id 
										ORDER BY count DESC 
										LIMIT 10");
		$byLanguage = array();
		while($row = $langResult->FetchArray(MYSQLI_ASSOC)) {
			$byLanguage[$row['language_id']] = $row['count'];
		}
		
		return array(
			'total' => (int)$totalRow['count'],
			'by_provider' => $byProvider,
			'by_language' => $byLanguage
		);
	}
	
	/**
	 * Cache löschen
	 */
	public function clearTranslationCache($targetLang = null)
	{
		if($targetLang) {
			$targetLangEscaped = $this->db->Escape($targetLang);
			$this->db->Query("DELETE FROM {pre}mf_translations WHERE target_language = '$targetLangEscaped'");
		} else {
			$this->db->Query("TRUNCATE TABLE {pre}mf_translations");
		}
		
		return array('success' => true);
	}
}
?>
