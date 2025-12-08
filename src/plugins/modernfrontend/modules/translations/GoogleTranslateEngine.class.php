<?php
/**
 * ModernFrontend CMS - Google Translate API Integration
 */

require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/translations/TranslationEngine.class.php');

class GoogleTranslateEngine extends TranslationEngine
{
	private $apiUrl = 'https://translation.googleapis.com/language/translate/v2';
	
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
			return '[Google Translate API Key fehlt]';
		}
		
		// Sprach-Codes normalisieren
		$targetLang = $this->normalizeLanguageCode($targetLang);
		if($sourceLang != 'auto') {
			$sourceLang = $this->normalizeLanguageCode($sourceLang);
		}
		
		// API-Request vorbereiten
		$params = array(
			'q' => $text,
			'target' => $targetLang,
			'key' => $this->apiKey,
			'format' => 'text'
		);
		
		if($sourceLang != 'auto') {
			$params['source'] = $sourceLang;
		}
		
		// Request senden
		$url = $this->apiUrl . '?' . http_build_query($params);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);
		
		// Fehler-Handling
		if($error) {
			return '[Google Translate Fehler: ' . $error . ']';
		}
		
		if($httpCode != 200) {
			return '[Google Translate HTTP ' . $httpCode . ']';
		}
		
		// Response parsen
		$data = json_decode($response, true);
		
		if(!isset($data['data']['translations'][0]['translatedText'])) {
			return '[Google Translate: Ungültige Antwort]';
		}
		
		$translatedText = $data['data']['translations'][0]['translatedText'];
		
		// HTML-Entities dekodieren (Google gibt manchmal HTML-kodiert zurück)
		$translatedText = html_entity_decode($translatedText, ENT_QUOTES, 'UTF-8');
		
		// In Cache speichern
		$this->saveToCache($text, $translatedText, $targetLang, $sourceLang);
		
		return $translatedText;
	}
	
	/**
	 * Unterstützte Sprachen
	 */
	public function getSupportedLanguages()
	{
		if(empty($this->apiKey)) {
			return array();
		}
		
		$url = 'https://translation.googleapis.com/language/translate/v2/languages?key=' . $this->apiKey;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		
		$response = curl_exec($ch);
		curl_close($ch);
		
		$data = json_decode($response, true);
		
		if(!isset($data['data']['languages'])) {
			return array();
		}
		
		$languages = array();
		foreach($data['data']['languages'] as $lang) {
			$languages[] = $lang['language'];
		}
		
		return $languages;
	}
	
	/**
	 * API-Name
	 */
	public function getApiName()
	{
		return 'google_translate';
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
		
		// Test-Übersetzung
		$testResult = $this->translate('Hello', 'de', 'en');
		
		if(strpos($testResult, '[Google Translate') !== false) {
			return array(
				'status' => 'error',
				'message' => 'Google Translate API nicht erreichbar: ' . $testResult
			);
		}
		
		return array(
			'status' => 'ok',
			'message' => 'Google Translate API funktioniert',
			'test_result' => $testResult
		);
	}
	
	/**
	 * Batch-Übersetzung optimiert
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
				$results[$key] = '[Google Translate API Key fehlt]';
			}
			return $results;
		}
		
		// Sprach-Codes normalisieren
		$targetLang = $this->normalizeLanguageCode($targetLang);
		if($sourceLang != 'auto') {
			$sourceLang = $this->normalizeLanguageCode($sourceLang);
		}
		
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
			'target' => $targetLang,
			'key' => $this->apiKey,
			'format' => 'text'
		);
		
		if($sourceLang != 'auto') {
			$params['source'] = $sourceLang;
		}
		
		// Mehrere 'q' Parameter
		foreach($uncachedTexts as $text) {
			$params['q'][] = $text;
		}
		
		$url = $this->apiUrl;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
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
		
		if(!isset($data['data']['translations'])) {
			// Fallback: Einzeln übersetzen
			foreach($uncachedTexts as $key => $text) {
				$results[$key] = $this->translate($text, $targetLang, $sourceLang);
			}
			return $results;
		}
		
		// Ergebnisse zuordnen
		$i = 0;
		foreach($uncachedTexts as $key => $text) {
			if(isset($data['data']['translations'][$i])) {
				$translatedText = html_entity_decode($data['data']['translations'][$i]['translatedText'], ENT_QUOTES, 'UTF-8');
				$results[$key] = $translatedText;
				$this->saveToCache($text, $translatedText, $targetLang, $sourceLang);
			} else {
				$results[$key] = $text;
			}
			$i++;
		}
		
		return $results;
	}
}
?>
