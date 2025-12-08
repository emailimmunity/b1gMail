<?php
/**
 * ModernFrontend CMS - Domain Manager
 * Verwaltet alle Domain-Operationen (CRUD)
 */

class DomainManager
{
	private $db;
	private $cache = array();
	
	public function __construct()
	{
		global $db;
		$this->db = $db;
	}
	
	/**
	 * Alle Domains abrufen
	 */
	public function getAllDomains($includeInactive = false)
	{
		$sql = "SELECT d.*, 
				ds.name as design_name,
				l.name as language_name,
				l.code as language_code
				FROM {pre}mf_domains d
				LEFT JOIN {pre}mf_designs ds ON d.design_id = ds.id
				LEFT JOIN {pre}mf_languages l ON d.default_language_id = l.id";
		
		if(!$includeInactive) {
			$sql .= " WHERE d.status = 'active'";
		}
		
		$sql .= " ORDER BY d.domain ASC";
		
		$result = $this->db->Query($sql);
		$domains = array();
		
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$domains[] = $row;
		}
		
		return $domains;
	}
	
	/**
	 * Domain per ID abrufen
	 */
	public function getDomainById($id)
	{
		$id = intval($id);
		
		if(isset($this->cache['domain_' . $id])) {
			return $this->cache['domain_' . $id];
		}
		
		$result = $this->db->Query("SELECT d.*, 
				ds.name as design_name,
				l.name as language_name,
				l.code as language_code
				FROM {pre}mf_domains d
				LEFT JOIN {pre}mf_designs ds ON d.design_id = ds.id
				LEFT JOIN {pre}mf_languages l ON d.default_language_id = l.id
				WHERE d.id = $id");
		
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$this->cache['domain_' . $id] = $row;
			return $row;
		}
		
		return false;
	}
	
	/**
	 * Domain per Hostname abrufen
	 */
	public function getDomainByHostname($hostname)
	{
		$hostname = $this->db->Escape($hostname);
		
		if(isset($this->cache['hostname_' . $hostname])) {
			return $this->cache['hostname_' . $hostname];
		}
		
		$result = $this->db->Query("SELECT d.*, 
				ds.name as design_name,
				ds.template_path,
				ds.primary_color,
				ds.secondary_color,
				ds.logo_path,
				ds.css_file,
				ds.settings as design_settings,
				l.name as language_name,
				l.code as language_code
				FROM {pre}mf_domains d
				LEFT JOIN {pre}mf_designs ds ON d.design_id = ds.id
				LEFT JOIN {pre}mf_languages l ON d.default_language_id = l.id
				WHERE d.domain = '$hostname'
				AND d.status = 'active'");
		
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$this->cache['hostname_' . $hostname] = $row;
			return $row;
		}
		
		return false;
	}
	
	/**
	 * Domain erstellen
	 */
	public function createDomain($data)
	{
		$domain = $this->db->Escape($data['domain']);
		$design_id = isset($data['design_id']) ? intval($data['design_id']) : 'NULL';
		$default_language_id = isset($data['default_language_id']) ? intval($data['default_language_id']) : 1;
		$status = isset($data['status']) ? $this->db->Escape($data['status']) : 'active';
		$ssl_enabled = isset($data['ssl_enabled']) ? intval($data['ssl_enabled']) : 1;
		$custom_config = isset($data['custom_config']) ? "'" . $this->db->Escape($data['custom_config']) . "'" : 'NULL';
		
		// Prüfe ob Domain bereits existiert
		$check = $this->db->Query("SELECT id FROM {pre}mf_domains WHERE domain = '$domain'");
		if($check->FetchArray()) {
			return array('success' => false, 'error' => 'Domain already exists');
		}
		
		$sql = "INSERT INTO {pre}mf_domains 
				(domain, design_id, default_language_id, status, ssl_enabled, custom_config)
				VALUES 
				('$domain', $design_id, $default_language_id, '$status', $ssl_enabled, $custom_config)";
		
		if($this->db->Query($sql)) {
			$id = $this->db->InsertId();
			
			// Standard-Sprache zuweisen
			$this->db->Query("INSERT INTO {pre}mf_domain_languages 
					(domain_id, language_id, is_default, is_enabled)
					VALUES 
					($id, $default_language_id, 1, 1)");
			
			$this->clearCache();
			return array('success' => true, 'id' => $id);
		}
		
		return array('success' => false, 'error' => 'Database error');
	}
	
	/**
	 * Domain aktualisieren
	 */
	public function updateDomain($id, $data)
	{
		$id = intval($id);
		$updates = array();
		
		if(isset($data['domain'])) {
			$updates[] = "domain = '" . $this->db->Escape($data['domain']) . "'";
		}
		if(isset($data['design_id'])) {
			$updates[] = "design_id = " . intval($data['design_id']);
		}
		if(isset($data['default_language_id'])) {
			$updates[] = "default_language_id = " . intval($data['default_language_id']);
		}
		if(isset($data['status'])) {
			$updates[] = "status = '" . $this->db->Escape($data['status']) . "'";
		}
		if(isset($data['ssl_enabled'])) {
			$updates[] = "ssl_enabled = " . intval($data['ssl_enabled']);
		}
		if(isset($data['custom_config'])) {
			$updates[] = "custom_config = '" . $this->db->Escape($data['custom_config']) . "'";
		}
		
		if(empty($updates)) {
			return array('success' => false, 'error' => 'No data to update');
		}
		
		$sql = "UPDATE {pre}mf_domains SET " . implode(', ', $updates) . " WHERE id = $id";
		
		if($this->db->Query($sql)) {
			$this->clearCache();
			return array('success' => true);
		}
		
		return array('success' => false, 'error' => 'Database error');
	}
	
	/**
	 * Domain löschen
	 */
	public function deleteDomain($id)
	{
		$id = intval($id);
		
		// Lösche zuerst Domain-Sprachen-Zuordnungen
		$this->db->Query("DELETE FROM {pre}mf_domain_languages WHERE domain_id = $id");
		
		// Lösche Domain
		if($this->db->Query("DELETE FROM {pre}mf_domains WHERE id = $id")) {
			$this->clearCache();
			return array('success' => true);
		}
		
		return array('success' => false, 'error' => 'Database error');
	}
	
	/**
	 * Sprachen für Domain abrufen
	 */
	public function getDomainLanguages($domain_id)
	{
		$domain_id = intval($domain_id);
		
		$result = $this->db->Query("SELECT dl.*, l.code, l.name, l.native_name, l.flag_icon
				FROM {pre}mf_domain_languages dl
				JOIN {pre}mf_languages l ON dl.language_id = l.id
				WHERE dl.domain_id = $domain_id
				AND dl.is_enabled = 1
				ORDER BY dl.is_default DESC, l.sort_order ASC");
		
		$languages = array();
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$languages[] = $row;
		}
		
		return $languages;
	}
	
	/**
	 * Sprache zu Domain hinzufügen
	 */
	public function addLanguageToDomain($domain_id, $language_id, $is_default = false)
	{
		$domain_id = intval($domain_id);
		$language_id = intval($language_id);
		$is_default = $is_default ? 1 : 0;
		
		// Wenn Standard-Sprache, andere auf nicht-standard setzen
		if($is_default) {
			$this->db->Query("UPDATE {pre}mf_domain_languages 
					SET is_default = 0 
					WHERE domain_id = $domain_id");
		}
		
		$sql = "INSERT INTO {pre}mf_domain_languages 
				(domain_id, language_id, is_default, is_enabled)
				VALUES 
				($domain_id, $language_id, $is_default, 1)
				ON DUPLICATE KEY UPDATE 
				is_default = $is_default,
				is_enabled = 1";
		
		if($this->db->Query($sql)) {
			$this->clearCache();
			return array('success' => true);
		}
		
		return array('success' => false, 'error' => 'Database error');
	}
	
	/**
	 * Sprache von Domain entfernen
	 */
	public function removeLanguageFromDomain($domain_id, $language_id)
	{
		$domain_id = intval($domain_id);
		$language_id = intval($language_id);
		
		if($this->db->Query("DELETE FROM {pre}mf_domain_languages 
				WHERE domain_id = $domain_id AND language_id = $language_id")) {
			$this->clearCache();
			return array('success' => true);
		}
		
		return array('success' => false, 'error' => 'Database error');
	}
	
	/**
	 * Anzahl Domains
	 */
	public function getDomainsCount()
	{
		$result = $this->db->Query("SELECT COUNT(*) as count FROM {pre}mf_domains");
		$row = $result->FetchArray(MYSQLI_ASSOC);
		return intval($row['count']);
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
