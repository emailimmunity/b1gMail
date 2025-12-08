<?php
/**
 * ModernFrontend CMS - Translation Engine
 * Abstrakte Basis-Klasse für Übersetzungs-APIs
 */

abstract class TranslationEngine
{
	protected $db;
	protected $apiKey;
	protected $cache = array();
	protected $useCache = true;
	
	public function __construct($apiKey = null)
	{
		global $db;
		$this->db = $db;
		$this->apiKey = $apiKey;
	}
	
	/**
	 * Text übersetzen (abstrakt - muss implementiert werden)
	 */
	abstract public function translate($text, $targetLang, $sourceLang = 'auto');
	
	/**
	 * Mehrere Texte übersetzen
	 */
	public function translateBatch($texts, $targetLang, $sourceLang = 'auto')
	{
		$results = array();
		
		foreach($texts as $key => $text) {
			$results[$key] = $this->translate($text, $targetLang, $sourceLang);
		}
		
		return $results;
	}
	
	/**
	 * Unterstützte Sprachen abrufen (abstrakt)
	 */
	abstract public function getSupportedLanguages();
	
	/**
	 * API-Name abrufen (abstrakt)
	 */
	abstract public function getApiName();
	
	/**
	 * Cache aktivieren/deaktivieren
	 */
	public function setUseCache($useCache)
	{
		$this->useCache = (bool)$useCache;
	}
	
	/**
	 * Übersetzung aus Cache holen
	 */
	protected function getFromCache($text, $targetLang, $sourceLang)
	{
		if(!$this->useCache) {
			return null;
		}
		
		$hash = $this->getCacheKey($text, $targetLang, $sourceLang);
		
		// Memory-Cache
		if(isset($this->cache[$hash])) {
			return $this->cache[$hash];
		}
		
		// DB-Cache
		$textEscaped = $this->db->Escape($text);
		$targetLangEscaped = $this->db->Escape($targetLang);
		$sourceLangEscaped = $this->db->Escape($sourceLang);
		$apiName = $this->db->Escape($this->getApiName());
		
		$result = $this->db->Query("SELECT translated_text FROM {pre}mf_translations 
									WHERE original_text = '$textEscaped' 
									AND target_language = '$targetLangEscaped' 
									AND source_language = '$sourceLangEscaped'
									AND api_provider = '$apiName'
									LIMIT 1");
		
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$this->cache[$hash] = $row['translated_text'];
			return $row['translated_text'];
		}
		
		return null;
	}
	
	/**
	 * Übersetzung in Cache speichern
	 */
	protected function saveToCache($text, $translatedText, $targetLang, $sourceLang)
	{
		if(!$this->useCache) {
			return;
		}
		
		$hash = $this->getCacheKey($text, $targetLang, $sourceLang);
		
		// Memory-Cache
		$this->cache[$hash] = $translatedText;
		
		// DB-Cache
		$textEscaped = $this->db->Escape($text);
		$translatedTextEscaped = $this->db->Escape($translatedText);
		$targetLangEscaped = $this->db->Escape($targetLang);
		$sourceLangEscaped = $this->db->Escape($sourceLang);
		$apiName = $this->db->Escape($this->getApiName());
		
		// INSERT or UPDATE
		$this->db->Query("INSERT INTO {pre}mf_translations 
						  (original_text, translated_text, source_language, target_language, api_provider, created_at, updated_at)
						  VALUES 
						  ('$textEscaped', '$translatedTextEscaped', '$sourceLangEscaped', '$targetLangEscaped', '$apiName', NOW(), NOW())
						  ON DUPLICATE KEY UPDATE 
						  translated_text = '$translatedTextEscaped',
						  updated_at = NOW()");
	}
	
	/**
	 * Cache-Key generieren
	 */
	protected function getCacheKey($text, $targetLang, $sourceLang)
	{
		return md5($text . '|' . $targetLang . '|' . $sourceLang . '|' . $this->getApiName());
	}
	
	/**
	 * Cache leeren
	 */
	public function clearCache()
	{
		$this->cache = array();
	}
	
	/**
	 * Sprach-Code normalisieren
	 */
	protected function normalizeLanguageCode($code)
	{
		// Entferne Region-Teil (z.B. de-DE -> de)
		if(strpos($code, '-') !== false) {
			$parts = explode('-', $code);
			$code = $parts[0];
		}
		
		return strtolower($code);
	}
	
	/**
	 * API-Status prüfen
	 */
	public function checkStatus()
	{
		if(empty($this->apiKey)) {
			return array(
				'status' => 'error',
				'message' => 'API-Key nicht konfiguriert'
			);
		}
		
		return array(
			'status' => 'ok',
			'message' => 'API einsatzbereit'
		);
	}
}
?>
