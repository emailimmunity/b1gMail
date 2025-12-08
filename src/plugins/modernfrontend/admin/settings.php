<?php
/**
 * ModernFrontend CMS - Settings
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this script is not allowed');

// Security: Plugin is called from admin framework, no additional checks needed

global $db, $tpl;

// Handle form submission
if(isset($_POST['save_settings']))
{
	foreach($_POST['settings'] as $key => $value)
	{
		// Check if setting exists
		$res = $db->Query('SELECT id FROM {pre}mf_settings WHERE setting_key=?', $key);
		
		if($res->RowCount() > 0)
		{
			// Update existing
			$db->Query('UPDATE {pre}mf_settings SET setting_value=? WHERE setting_key=?', $value, $key);
		}
		else
		{
			// Insert new
			$type = 'text';
			if($value === '0' || $value === '1') $type = 'boolean';
			if(is_numeric($value) && $value == (int)$value) $type = 'number';
			
			$db->Query('INSERT INTO {pre}mf_settings(setting_key, setting_value, setting_type) VALUES(?,?,?)',
				$key, $value, $type
			);
		}
		$res->Free();
	}
	
	// Clear cache
	@unlink(B1GMAIL_DIR . 'cache/settings.cache');
	
	PutLog('ModernFrontend: Settings updated by admin #' . $_SESSION['admin_id'],
		PRIO_NOTE,
		__FILE__,
		__LINE__);
	
	header('Location: ' . $_SERVER['REQUEST_URI'] . '&saved=1');
	exit();
}

// Load settings
$settings = array();
$res = $db->Query('SELECT * FROM {pre}mf_settings ORDER BY setting_key');
while($row = $res->FetchArray(MYSQLI_ASSOC))
{
	$settings[$row['setting_key']] = $row;
}
$res->Free();

// Settings configuration
$settings_config = array(
	'general' => array(
		'title' => 'Allgemeine Einstellungen',
		'settings' => array(
			'plugin_enabled' => array('label' => 'Plugin aktiviert', 'type' => 'boolean', 'default' => '1'),
			'default_language' => array('label' => 'Standard-Sprache', 'type' => 'select', 'options' => array('de' => 'Deutsch', 'en' => 'English'), 'default' => 'de'),
			'maintenance_mode' => array('label' => 'Wartungsmodus', 'type' => 'boolean', 'default' => '0'),
			'replace_landing_page' => array('label' => 'Standard-Startseite ersetzen', 'type' => 'boolean', 'default' => '0')
		)
	),
	'analytics' => array(
		'title' => 'Analytics & Tracking',
		'settings' => array(
			'analytics_enabled' => array('label' => 'Analytics aktiviert', 'type' => 'boolean', 'default' => '1'),
			'track_ip_addresses' => array('label' => 'IP-Adressen speichern', 'type' => 'boolean', 'default' => '0'),
			'track_user_agents' => array('label' => 'User Agents speichern', 'type' => 'boolean', 'default' => '1'),
			'analytics_retention_days' => array('label' => 'Daten-Aufbewahrung (Tage)', 'type' => 'number', 'default' => '90')
		)
	),
	'features' => array(
		'title' => 'Features',
		'settings' => array(
			'ab_testing_enabled' => array('label' => 'A/B Testing aktiviert', 'type' => 'boolean', 'default' => '1'),
			'contact_forms_enabled' => array('label' => 'Kontaktformulare aktiviert', 'type' => 'boolean', 'default' => '1'),
			'media_library_enabled' => array('label' => 'Media Library aktiviert', 'type' => 'boolean', 'default' => '1'),
			'page_builder_enabled' => array('label' => 'Page Builder aktiviert', 'type' => 'boolean', 'default' => '1')
		)
	),
	'cache' => array(
		'title' => 'Cache & Performance',
		'settings' => array(
			'cache_enabled' => array('label' => 'Cache aktiviert', 'type' => 'boolean', 'default' => '1'),
			'cache_ttl' => array('label' => 'Cache TTL (Sekunden)', 'type' => 'number', 'default' => '3600'),
			'minify_css' => array('label' => 'CSS minifizieren', 'type' => 'boolean', 'default' => '0'),
			'minify_js' => array('label' => 'JavaScript minifizieren', 'type' => 'boolean', 'default' => '0')
		)
	),
	'seo' => array(
		'title' => 'SEO & Meta Tags',
		'settings' => array(
			'seo_title_suffix' => array('label' => 'Titel-Suffix', 'type' => 'text', 'default' => ' - aikQ Mail'),
			'seo_default_description' => array('label' => 'Standard Meta-Description', 'type' => 'textarea', 'default' => 'Professionelles E-Mail-Hosting'),
			'seo_og_image' => array('label' => 'Open Graph Bild URL', 'type' => 'text', 'default' => ''),
			'seo_sitemap_enabled' => array('label' => 'Sitemap generieren', 'type' => 'boolean', 'default' => '1')
		)
	)
);

$tpl->assign('settings', $settings);
$tpl->assign('settings_config', $settings_config);
$tpl->assign('saved', isset($_GET['saved']));
$tpl->assign('pageURL', 'admin/plugin.page.php?plugin=ModernFrontendPlugin');
$tpl->assign('page', MODERNFRONTEND_PATH . 'templates/admin/settings.tpl');
?>
