<?php
/**
 * ModernFrontend CMS - Design Manager
 * Verwaltet alle Design-Operationen (CRUD)
 */

class DesignManager
{
	private $db;
	private $cache = array();
	
	public function __construct()
	{
		global $db;
		$this->db = $db;
	}
	
	/**
	 * Alle Designs abrufen
	 */
	public function getAllDesigns($includeInactive = false)
	{
		$sql = "SELECT * FROM {pre}mf_designs";
		
		if(!$includeInactive) {
			$sql .= " WHERE is_active = 1";
		}
		
		$sql .= " ORDER BY name ASC";
		
		$result = $this->db->Query($sql);
		$designs = array();
		
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			// Parse JSON settings
			if($row['settings']) {
				$row['settings_parsed'] = json_decode($row['settings'], true);
			}
			$designs[] = $row;
		}
		
		return $designs;
	}
	
	/**
	 * Design per ID abrufen
	 */
	public function getDesignById($id)
	{
		$id = intval($id);
		
		if(isset($this->cache['design_' . $id])) {
			return $this->cache['design_' . $id];
		}
		
		$result = $this->db->Query("SELECT * FROM {pre}mf_designs WHERE id = $id");
		
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			// Parse JSON settings
			if($row['settings']) {
				$row['settings_parsed'] = json_decode($row['settings'], true);
			}
			$this->cache['design_' . $id] = $row;
			return $row;
		}
		
		return false;
	}
	
	/**
	 * Design per Name abrufen
	 */
	public function getDesignByName($name)
	{
		$name = $this->db->Escape($name);
		
		$result = $this->db->Query("SELECT * FROM {pre}mf_designs WHERE name = '$name'");
		
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			// Parse JSON settings
			if($row['settings']) {
				$row['settings_parsed'] = json_decode($row['settings'], true);
			}
			return $row;
		}
		
		return false;
	}
	
	/**
	 * Design erstellen
	 */
	public function createDesign($data)
	{
		$name = $this->db->Escape($data['name']);
		$description = isset($data['description']) ? "'" . $this->db->Escape($data['description']) . "'" : 'NULL';
		$template_path = $this->db->Escape($data['template_path']);
		$primary_color = isset($data['primary_color']) ? $this->db->Escape($data['primary_color']) : '#76B82A';
		$secondary_color = isset($data['secondary_color']) ? $this->db->Escape($data['secondary_color']) : '#333333';
		$logo_path = isset($data['logo_path']) ? "'" . $this->db->Escape($data['logo_path']) . "'" : 'NULL';
		$css_file = isset($data['css_file']) ? "'" . $this->db->Escape($data['css_file']) . "'" : 'NULL';
		$preview_image = isset($data['preview_image']) ? "'" . $this->db->Escape($data['preview_image']) . "'" : 'NULL';
		$is_active = isset($data['is_active']) ? intval($data['is_active']) : 1;
		$settings = isset($data['settings']) ? "'" . $this->db->Escape(json_encode($data['settings'])) . "'" : 'NULL';
		
		// Prüfe ob Name bereits existiert
		$check = $this->db->Query("SELECT id FROM {pre}mf_designs WHERE name = '$name'");
		if($check->FetchArray()) {
			return array('success' => false, 'error' => 'Design name already exists');
		}
		
		$sql = "INSERT INTO {pre}mf_designs 
				(name, description, template_path, primary_color, secondary_color, logo_path, css_file, preview_image, is_active, settings)
				VALUES 
				('$name', $description, '$template_path', '$primary_color', '$secondary_color', $logo_path, $css_file, $preview_image, $is_active, $settings)";
		
		if($this->db->Query($sql)) {
			$id = $this->db->InsertId();
			$this->clearCache();
			return array('success' => true, 'id' => $id);
		}
		
		return array('success' => false, 'error' => 'Database error');
	}
	
	/**
	 * Design aktualisieren
	 */
	public function updateDesign($id, $data)
	{
		$id = intval($id);
		$updates = array();
		
		if(isset($data['name'])) {
			$updates[] = "name = '" . $this->db->Escape($data['name']) . "'";
		}
		if(isset($data['description'])) {
			$updates[] = "description = '" . $this->db->Escape($data['description']) . "'";
		}
		if(isset($data['template_path'])) {
			$updates[] = "template_path = '" . $this->db->Escape($data['template_path']) . "'";
		}
		if(isset($data['primary_color'])) {
			$updates[] = "primary_color = '" . $this->db->Escape($data['primary_color']) . "'";
		}
		if(isset($data['secondary_color'])) {
			$updates[] = "secondary_color = '" . $this->db->Escape($data['secondary_color']) . "'";
		}
		if(isset($data['logo_path'])) {
			$updates[] = "logo_path = '" . $this->db->Escape($data['logo_path']) . "'";
		}
		if(isset($data['css_file'])) {
			$updates[] = "css_file = '" . $this->db->Escape($data['css_file']) . "'";
		}
		if(isset($data['preview_image'])) {
			$updates[] = "preview_image = '" . $this->db->Escape($data['preview_image']) . "'";
		}
		if(isset($data['is_active'])) {
			$updates[] = "is_active = " . intval($data['is_active']);
		}
		if(isset($data['settings'])) {
			$updates[] = "settings = '" . $this->db->Escape(json_encode($data['settings'])) . "'";
		}
		
		if(empty($updates)) {
			return array('success' => false, 'error' => 'No data to update');
		}
		
		$sql = "UPDATE {pre}mf_designs SET " . implode(', ', $updates) . " WHERE id = $id";
		
		if($this->db->Query($sql)) {
			$this->clearCache();
			return array('success' => true);
		}
		
		return array('success' => false, 'error' => 'Database error');
	}
	
	/**
	 * Design löschen
	 */
	public function deleteDesign($id)
	{
		$id = intval($id);
		
		// Prüfe ob Design in Verwendung
		$check = $this->db->Query("SELECT COUNT(*) as count FROM {pre}mf_domains WHERE design_id = $id");
		$row = $check->FetchArray(MYSQLI_ASSOC);
		
		if($row['count'] > 0) {
			return array('success' => false, 'error' => 'Design is in use by ' . $row['count'] . ' domain(s)');
		}
		
		if($this->db->Query("DELETE FROM {pre}mf_designs WHERE id = $id")) {
			$this->clearCache();
			return array('success' => true);
		}
		
		return array('success' => false, 'error' => 'Database error');
	}
	
	/**
	 * Design duplizieren
	 */
	public function duplicateDesign($id)
	{
		$design = $this->getDesignById($id);
		if(!$design) {
			return array('success' => false, 'error' => 'Design not found');
		}
		
		// Neuer Name
		$newName = $design['name'] . ' (Copy)';
		$counter = 1;
		while($this->getDesignByName($newName)) {
			$counter++;
			$newName = $design['name'] . ' (Copy ' . $counter . ')';
		}
		
		// Erstelle Kopie
		$newDesign = array(
			'name' => $newName,
			'description' => $design['description'],
			'template_path' => $design['template_path'],
			'primary_color' => $design['primary_color'],
			'secondary_color' => $design['secondary_color'],
			'logo_path' => $design['logo_path'],
			'css_file' => $design['css_file'],
			'preview_image' => $design['preview_image'],
			'is_active' => 0, // Inaktiv
			'settings' => $design['settings_parsed']
		);
		
		return $this->createDesign($newDesign);
	}
	
	/**
	 * Anzahl Designs
	 */
	public function getDesignsCount()
	{
		$result = $this->db->Query("SELECT COUNT(*) as count FROM {pre}mf_designs");
		$row = $result->FetchArray(MYSQLI_ASSOC);
		return intval($row['count']);
	}
	
	/**
	 * Designs die in Verwendung sind
	 */
	public function getDesignsInUse()
	{
		$sql = "SELECT d.*, COUNT(dm.id) as usage_count
				FROM {pre}mf_designs d
				LEFT JOIN {pre}mf_domains dm ON d.id = dm.design_id
				GROUP BY d.id
				HAVING usage_count > 0
				ORDER BY usage_count DESC";
		
		$result = $this->db->Query($sql);
		$designs = array();
		
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$designs[] = $row;
		}
		
		return $designs;
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
