<?php
/**
 * ModernFrontend CMS - Theme Loader
 * Lädt Design-Assets (CSS, JS, Templates)
 */

class ThemeLoader
{
	private $designManager;
	private $domainDetector;
	private $currentDesign = null;
	private $baseUrl;
	
	public function __construct()
	{
		require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/designs/DesignManager.class.php');
		require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/domains/DomainDetector.class.php');
		
		$this->designManager = new DesignManager();
		$this->domainDetector = new DomainDetector();
		$this->baseUrl = B1GMAIL_REL . 'plugins/modernfrontend/';
	}
	
	/**
	 * Aktuelles Design laden
	 */
	public function loadDesign()
	{
		// Design-ID von aktueller Domain holen
		$designId = $this->domainDetector->getDesignId();
		
		if($designId) {
			$this->currentDesign = $this->designManager->getDesignById($designId);
		}
		
		// Fallback auf Default-Design
		if(!$this->currentDesign) {
			$this->currentDesign = $this->designManager->getDesignByName('aikQ Default');
		}
		
		return $this->currentDesign;
	}
	
	/**
	 * Aktuelles Design abrufen
	 */
	public function getCurrentDesign()
	{
		if($this->currentDesign === null) {
			$this->loadDesign();
		}
		
		return $this->currentDesign;
	}
	
	/**
	 * CSS-Dateien des Designs abrufen
	 */
	public function getCssFiles()
	{
		$design = $this->getCurrentDesign();
		if(!$design) {
			return array();
		}
		
		$files = array();
		
		// Haupt-CSS-Datei
		if($design['css_file']) {
			$files[] = $this->baseUrl . $design['template_path'] . $design['css_file'];
		}
		
		// Standard-CSS falls keine spezifische angegeben
		if(empty($files)) {
			$files[] = $this->baseUrl . $design['template_path'] . 'style.css';
		}
		
		return $files;
	}
	
	/**
	 * JS-Dateien des Designs abrufen
	 */
	public function getJsFiles()
	{
		$design = $this->getCurrentDesign();
		if(!$design) {
			return array();
		}
		
		$files = array();
		
		// Standard-JS
		$jsPath = B1GMAIL_DIR . 'plugins/modernfrontend/' . $design['template_path'] . 'script.js';
		if(file_exists($jsPath)) {
			$files[] = $this->baseUrl . $design['template_path'] . 'script.js';
		}
		
		return $files;
	}
	
	/**
	 * CSS-Tags generieren
	 */
	public function getCssTags()
	{
		$files = $this->getCssFiles();
		$tags = '';
		
		foreach($files as $file) {
			$tags .= '<link rel="stylesheet" href="' . htmlspecialchars($file) . '">' . "\n";
		}
		
		return $tags;
	}
	
	/**
	 * JS-Tags generieren
	 */
	public function getJsTags()
	{
		$files = $this->getJsFiles();
		$tags = '';
		
		foreach($files as $file) {
			$tags .= '<script src="' . htmlspecialchars($file) . '"></script>' . "\n";
		}
		
		return $tags;
	}
	
	/**
	 * Primary Color abrufen
	 */
	public function getPrimaryColor()
	{
		$design = $this->getCurrentDesign();
		return $design ? $design['primary_color'] : '#76B82A';
	}
	
	/**
	 * Secondary Color abrufen
	 */
	public function getSecondaryColor()
	{
		$design = $this->getCurrentDesign();
		return $design ? $design['secondary_color'] : '#333333';
	}
	
	/**
	 * Logo-Pfad abrufen
	 */
	public function getLogoPath()
	{
		$design = $this->getCurrentDesign();
		if($design && $design['logo_path']) {
			return $this->baseUrl . $design['logo_path'];
		}
		return null;
	}
	
	/**
	 * Template-Pfad abrufen
	 */
	public function getTemplatePath()
	{
		$design = $this->getCurrentDesign();
		return $design ? $design['template_path'] : 'designs/aikq-default/';
	}
	
	/**
	 * Inline-CSS für Farben generieren
	 */
	public function getInlineCss()
	{
		$primary = $this->getPrimaryColor();
		$secondary = $this->getSecondaryColor();
		
		$css = "<style>\n";
		$css .= ":root {\n";
		$css .= "  --primary-color: $primary;\n";
		$css .= "  --secondary-color: $secondary;\n";
		$css .= "}\n";
		$css .= ".btn-primary, .primary-bg { background-color: $primary !important; }\n";
		$css .= ".text-primary { color: $primary !important; }\n";
		$css .= ".btn-primary:hover { background-color: " . $this->darkenColor($primary, 10) . " !important; }\n";
		$css .= "</style>\n";
		
		return $css;
	}
	
	/**
	 * Farbe abdunkeln
	 */
	private function darkenColor($color, $percent)
	{
		$color = str_replace('#', '', $color);
		$r = hexdec(substr($color, 0, 2));
		$g = hexdec(substr($color, 2, 2));
		$b = hexdec(substr($color, 4, 2));
		
		$r = max(0, $r - ($r * $percent / 100));
		$g = max(0, $g - ($g * $percent / 100));
		$b = max(0, $b - ($b * $percent / 100));
		
		return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) 
				   . str_pad(dechex($g), 2, '0', STR_PAD_LEFT) 
				   . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
	}
	
	/**
	 * Design-Info für Template
	 */
	public function getDesignVars()
	{
		$design = $this->getCurrentDesign();
		
		return array(
			'design_name' => $design ? $design['name'] : 'Default',
			'design_path' => $this->getTemplatePath(),
			'primary_color' => $this->getPrimaryColor(),
			'secondary_color' => $this->getSecondaryColor(),
			'logo_path' => $this->getLogoPath(),
			'css_tags' => $this->getCssTags(),
			'js_tags' => $this->getJsTags(),
			'inline_css' => $this->getInlineCss()
		);
	}
	
	/**
	 * Design in Smarty registrieren
	 */
	public function registerToSmarty($tpl)
	{
		$vars = $this->getDesignVars();
		
		foreach($vars as $key => $value) {
			$tpl->assign($key, $value);
		}
	}
}
?>
