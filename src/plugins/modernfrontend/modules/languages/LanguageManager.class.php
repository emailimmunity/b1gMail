<?php
/**
 * ModernFrontend CMS - Language Manager
 * Verwaltet alle Sprach-Operationen
 */

class LanguageManager
{
	private $db;
	private $cache = array();
	
	public function __construct()
	{
		global $db;
		$this->db = $db;
	}
	
	/**
	 * Alle Sprachen abrufen
	 */
	public function getAllLanguages($activeOnly = false)
	{
		$sql = "SELECT * FROM {pre}mf_languages";
		
		if($activeOnly) {
			$sql .= " WHERE is_active = 1";
		}
		
		$sql .= " ORDER BY name ASC";
		
		$result = $this->db->Query($sql);
		$languages = array();
		
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$languages[] = $row;
		}
		
		return $languages;
	}
	
	/**
	 * Sprache per ID abrufen
	 */
	public function getLanguageById($id)
	{
		$id = intval($id);
		
		if(isset($this->cache['lang_' . $id])) {
			return $this->cache['lang_' . $id];
		}
		
		$result = $this->db->Query("SELECT * FROM {pre}mf_languages WHERE id = $id");
		
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$this->cache['lang_' . $id] = $row;
			return $row;
		}
		
		return false;
	}
	
	/**
	 * Sprache per Code abrufen (z.B. 'de', 'en')
	 */
	public function getLanguageByCode($code)
	{
		$code = $this->db->Escape(strtolower($code));
		
		if(isset($this->cache['code_' . $code])) {
			return $this->cache['code_' . $code];
		}
		
		$result = $this->db->Query("SELECT * FROM {pre}mf_languages WHERE code = '$code'");
		
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$this->cache['code_' . $code] = $row;
			return $row;
		}
		
		return false;
	}
	
	/**
	 * Sprachen für eine Domain abrufen
	 */
	public function getLanguagesForDomain($domainId)
	{
		$domainId = intval($domainId);
		
		$sql = "SELECT l.*, dl.is_default
				FROM {pre}mf_languages l
				INNER JOIN {pre}mf_domain_languages dl ON l.id = dl.language_id
				WHERE dl.domain_id = $domainId
				ORDER BY dl.is_default DESC, l.name ASC";
		
		$result = $this->db->Query($sql);
		$languages = array();
		
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$languages[] = $row;
		}
		
		return $languages;
	}
	
	/**
	 * Standard-Sprache für Domain abrufen
	 */
	public function getDefaultLanguageForDomain($domainId)
	{
		$domainId = intval($domainId);
		
		$sql = "SELECT l.*
				FROM {pre}mf_languages l
				INNER JOIN {pre}mf_domain_languages dl ON l.id = dl.language_id
				WHERE dl.domain_id = $domainId AND dl.is_default = 1
				LIMIT 1";
		
		$result = $this->db->Query($sql);
		
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			return $row;
		}
		
		return false;
	}
	
	/**
	 * Sprache zu Domain hinzufügen
	 */
	public function addLanguageToDomain($domainId, $languageId, $isDefault = false)
	{
		$domainId = intval($domainId);
		$languageId = intval($languageId);
		$isDefault = $isDefault ? 1 : 0;
		
		// Prüfe ob bereits existiert
		$check = $this->db->Query("SELECT id FROM {pre}mf_domain_languages 
									WHERE domain_id = $domainId AND language_id = $languageId");
		if($check->FetchArray()) {
			return array('success' => false, 'error' => 'Language already assigned to domain');
		}
		
		// Falls neue Default-Sprache, alte Default entfernen
		if($isDefault) {
			$this->db->Query("UPDATE {pre}mf_domain_languages SET is_default = 0 
							  WHERE domain_id = $domainId");
		}
		
		$sql = "INSERT INTO {pre}mf_domain_languages (domain_id, language_id, is_default)
				VALUES ($domainId, $languageId, $isDefault)";
		
		if($this->db->Query($sql)) {
			return array('success' => true);
		}
		
		return array('success' => false, 'error' => 'Database error');
	}
	
	/**
	 * Sprache von Domain entfernen
	 */
	public function removeLanguageFromDomain($domainId, $languageId)
	{
		$domainId = intval($domainId);
		$languageId = intval($languageId);
		
		// Prüfe ob es die Default-Sprache ist
		$check = $this->db->Query("SELECT is_default FROM {pre}mf_domain_languages 
									WHERE domain_id = $domainId AND language_id = $languageId");
		$row = $check->FetchArray(MYSQLI_ASSOC);
		
		if($row && $row['is_default'] == 1) {
			return array('success' => false, 'error' => 'Cannot remove default language');
		}
		
		if($this->db->Query("DELETE FROM {pre}mf_domain_languages 
							 WHERE domain_id = $domainId AND language_id = $languageId")) {
			return array('success' => true);
		}
		
		return array('success' => false, 'error' => 'Database error');
	}
	
	/**
	 * Standard-Sprache für Domain setzen
	 */
	public function setDefaultLanguageForDomain($domainId, $languageId)
	{
		$domainId = intval($domainId);
		$languageId = intval($languageId);
		
		// Prüfe ob Sprache überhaupt für Domain verfügbar
		$check = $this->db->Query("SELECT id FROM {pre}mf_domain_languages 
									WHERE domain_id = $domainId AND language_id = $languageId");
		if(!$check->FetchArray()) {
			return array('success' => false, 'error' => 'Language not assigned to domain');
		}
		
		// Alte Default entfernen
		$this->db->Query("UPDATE {pre}mf_domain_languages SET is_default = 0 
						  WHERE domain_id = $domainId");
		
		// Neue Default setzen
		if($this->db->Query("UPDATE {pre}mf_domain_languages SET is_default = 1 
							 WHERE domain_id = $domainId AND language_id = $languageId")) {
			return array('success' => true);
		}
		
		return array('success' => false, 'error' => 'Database error');
	}
	
	/**
	 * Sprache aktivieren/deaktivieren
	 */
	public function updateLanguageStatus($id, $isActive)
	{
		$id = intval($id);
		$isActive = $isActive ? 1 : 0;
		
		if($this->db->Query("UPDATE {pre}mf_languages SET is_active = $isActive WHERE id = $id")) {
			$this->clearCache();
			return array('success' => true);
		}
		
		return array('success' => false, 'error' => 'Database error');
	}
	
	/**
	 * Sprach-Flagge aktualisieren
	 */
	public function updateLanguageFlag($id, $flagIcon)
	{
		$id = intval($id);
		$flagIcon = $this->db->Escape($flagIcon);
		
		if($this->db->Query("UPDATE {pre}mf_languages SET flag_icon = '$flagIcon' WHERE id = $id")) {
			$this->clearCache();
			return array('success' => true);
		}
		
		return array('success' => false, 'error' => 'Database error');
	}
	
	/**
	 * Anzahl aktiver Sprachen
	 */
	public function getActiveLanguagesCount()
	{
		$result = $this->db->Query("SELECT COUNT(*) as count FROM {pre}mf_languages WHERE is_active = 1");
		$row = $result->FetchArray(MYSQLI_ASSOC);
		return intval($row['count']);
	}
	
	/**
	 * Anzahl Sprachen gesamt
	 */
	public function getTotalLanguagesCount()
	{
		$result = $this->db->Query("SELECT COUNT(*) as count FROM {pre}mf_languages");
		$row = $result->FetchArray(MYSQLI_ASSOC);
		return intval($row['count']);
	}
	
	/**
	 * Sprachen gruppiert nach Region
	 */
	public function getLanguagesByRegion()
	{
		$languages = $this->getAllLanguages();
		$regions = array(
			'Europe' => array('de', 'en', 'fr', 'es', 'it', 'pt', 'nl', 'pl', 'ru', 'sv', 'no', 'da', 'fi', 'cs', 'sk', 'hu', 'ro', 'bg', 'hr', 'sr', 'uk', 'el', 'et', 'lv', 'lt', 'sl', 'is', 'ga', 'ca', 'eu', 'gl', 'cy', 'mt', 'lb'),
			'Asia' => array('ja', 'zh', 'ko', 'hi', 'th', 'vi', 'id', 'ms', 'tl', 'bn', 'ur'),
			'Middle East' => array('ar', 'he', 'tr', 'fa'),
			'Africa' => array('sw', 'af')
		);
		
		$grouped = array();
		foreach($regions as $region => $codes) {
			$grouped[$region] = array();
			foreach($languages as $lang) {
				if(in_array($lang['code'], $codes)) {
					$grouped[$region][] = $lang;
				}
			}
		}
		
		return $grouped;
	}
	
	/**
	 * Meistgenutzte Sprachen (basierend auf Domain-Zuweisungen)
	 */
	public function getMostUsedLanguages($limit = 10)
	{
		$limit = intval($limit);
		
		$sql = "SELECT l.*, COUNT(dl.id) as usage_count
				FROM {pre}mf_languages l
				LEFT JOIN {pre}mf_domain_languages dl ON l.id = dl.language_id
				GROUP BY l.id
				HAVING usage_count > 0
				ORDER BY usage_count DESC, l.name ASC
				LIMIT $limit";
		
		$result = $this->db->Query($sql);
		$languages = array();
		
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$languages[] = $row;
		}
		
		return $languages;
	}
	
	/**
	 * Cache leeren
	 */
	private function clearCache()
	{
		$this->cache = array();
	}
}
?>
