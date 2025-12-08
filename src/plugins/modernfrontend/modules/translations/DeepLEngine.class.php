<?php
/**
 * ModernFrontend CMS - DeepL API Integration
 */

require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/translations/TranslationEngine.class.php');

class DeepLEngine extends TranslationEngine
{
	private $apiUrl = 'https://api-free.deepl.com/v2/translate';
	private $usePro = false;
	
	/**
	 * Konstruktor
	 */
	public function __construct($apiKey = null, $usePro = false)
	{
		parent::__construct($apiKey);
		$this->usePro = $usePro;
		
		if($usePro) {
			$this->apiUrl = 'https://api.deepl.com/v2/translate';
		}
	}
	
	/**
	 * Text übersetzen
	 */
	public function translate($text, $targetLang, $sourceLang = 'auto')
	{
		// Leer-Check
		if(empty($text)) {
			return '';
		}
		
		// Cache prüfen
		$cached = $this->getFromCache($text, $targetLang, $sourceLang);
		if($cached !== null) {
			return $cached;
		}
		
		// API-Key prüfen
		if(empty($this->apiKey)) {
			return '[DeepL API Key fehlt]';
		}
		
		// Sprach-Codes anpassen (DeepL verwendet Großbuchstaben)
		$targetLang = $this->mapLanguageCode($targetLang);
		
		// API-Request vorbereiten
		$params = array(
			'text' => $text,
			'target_lang' => $targetLang,
			'auth_key' => $this->apiKey
		);
		
		if($sourceLang != 'auto') {
			$params['source_lang'] = $this->mapLanguageCode($sourceLang);
		}
		
		// Request senden
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);
		
		// Fehler-Handling
		if($error) {
			return '[DeepL Fehler: ' . $error . ']';
		}
		
		if($httpCode == 403) {
			return '[DeepL: API Key ungültig]';
		}
		
		if($httpCode == 456) {
			return '[DeepL: Quota erreicht]';
		}
		
		if($httpCode != 200) {
			return '[DeepL HTTP ' . $httpCode . ']';
		}
		
		// Response parsen
		$data = json_decode($response, true);
		
		if(!isset($data['translations'][0]['text'])) {
			return '[DeepL: Ungültige Antwort]';
		}
		
		$translatedText = $data['translations'][0]['text'];
		
		// In Cache speichern
		$this->saveToCache($text, $translatedText, $targetLang, $sourceLang);
		
		return $translatedText;
	}
	
	/**
	 * Batch-Übersetzung
	 */
	public function translateBatch($texts, $targetLang, $sourceLang = 'auto')
	{
		// Leer-Check
		if(empty($texts)) {
			return array();
		}
		
		// API-Key prüfen
		if(empty($this->apiKey)) {
			$results = array();
			foreach($texts as $key => $text) {
				$results[$key] = '[DeepL API Key fehlt]';
			}
			return $results;
		}
		
		// Sprach-Codes anpassen
		$targetLang = $this->mapLanguageCode($targetLang);
		
		// Texte vorbereiten
		$uncachedTexts = array();
		$results = array();
		
		foreach($texts as $key => $text) {
			$cached = $this->getFromCache($text, $targetLang, $sourceLang);
			if($cached !== null) {
				$results[$key] = $cached;
			} else {
				$uncachedTexts[$key] = $text;
			}
		}
		
		// Wenn alles aus Cache, fertig
		if(empty($uncachedTexts)) {
			return $results;
		}
		
		// API-Request vorbereiten (Batch)
		$params = array(
			'target_lang' => $targetLang,
			'auth_key' => $this->apiKey
		);
		
		if($sourceLang != 'auto') {
			$params['source_lang'] = $this->mapLanguageCode($sourceLang);
		}
		
		// Mehrere 'text' Parameter
		foreach($uncachedTexts as $text) {
			$params['text'][] = $text;
		}
		
		// Request senden
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if($httpCode != 200) {
			// Fallback: Einzeln übersetzen
			foreach($uncachedTexts as $key => $text) {
				$results[$key] = $this->translate($text, $targetLang, $sourceLang);
			}
			return $results;
		}
		
		// Response parsen
		$data = json_decode($response, true);
		
		if(!isset($data['translations'])) {
			// Fallback: Einzeln übersetzen
			foreach($uncachedTexts as $key => $text) {
				$results[$key] = $this->translate($text, $targetLang, $sourceLang);
			}
			return $results;
		}
		
		// Ergebnisse zuordnen
		$i = 0;
		foreach($uncachedTexts as $key => $text) {
			if(isset($data['translations'][$i])) {
				$translatedText = $data['translations'][$i]['text'];
				$results[$key] = $translatedText;
				$this->saveToCache($text, $translatedText, $targetLang, $sourceLang);
			} else {
				$results[$key] = $text;
			}
			$i++;
		}
		
		return $results;
	}
	
	/**
	 * Unterstützte Sprachen
	 */
	public function getSupportedLanguages()
	{
		// DeepL unterstützte Sprachen (Stand 2025)
		return array(
			'ar', 'bg', 'cs', 'da', 'de', 'el', 'en', 'es', 'et', 'fi',
			'fr', 'hu', 'id', 'it', 'ja', 'ko', 'lt', 'lv', 'nb', 'nl',
			'pl', 'pt', 'ro', 'ru', 'sk', 'sl', 'sv', 'tr', 'uk', 'zh'
		);
	}
	
	/**
	 * API-Name
	 */
	public function getApiName()
	{
		return 'deepl';
	}
	
	/**
	 * Sprach-Code mappen (DeepL-Spezifika)
	 */
	private function mapLanguageCode($code)
	{
		$code = strtoupper($this->normalizeLanguageCode($code));
		
		// Spezial-Mappings
		$mappings = array(
			'EN' => 'EN-US',  // DeepL benötigt EN-US oder EN-GB
			'PT' => 'PT-PT',  // DeepL benötigt PT-PT oder PT-BR
			'ZH' => 'ZH'      // Chinesisch vereinfacht
		);
		
		return isset($mappings[$code]) ? $mappings[$code] : $code;
	}
	
	/**
	 * API-Status prüfen
	 */
	public function checkStatus()
	{
		$baseStatus = parent::checkStatus();
		
		if($baseStatus['status'] == 'error') {
			return $baseStatus;
		}
		
		// Usage-Statistik abrufen
		$usageUrl = $this->usePro 
			? 'https://api.deepl.com/v2/usage' 
			: 'https://api-free.deepl.com/v2/usage';
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $usageUrl . '?auth_key=' . $this->apiKey);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if($httpCode == 403) {
			return array(
				'status' => 'error',
				'message' => 'DeepL API Key ungültig'
			);
		}
		
		if($httpCode != 200) {
			return array(
				'status' => 'error',
				'message' => 'DeepL API nicht erreichbar (HTTP ' . $httpCode . ')'
			);
		}
		
		$data = json_decode($response, true);
		
		$characterCount = isset($data['character_count']) ? $data['character_count'] : 0;
		$characterLimit = isset($data['character_limit']) ? $data['character_limit'] : 0;
		$percentage = $characterLimit > 0 ? round(($characterCount / $characterLimit) * 100, 2) : 0;
		
		return array(
			'status' => 'ok',
			'message' => 'DeepL API funktioniert',
			'usage' => array(
				'characters_used' => $characterCount,
				'characters_limit' => $characterLimit,
				'percentage' => $percentage
			)
		);
	}
}
?>
