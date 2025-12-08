<?php
/**
 * ModernFrontend CMS - Theme Editor
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this script is not allowed');

// Security: Plugin is called from admin framework, no additional checks needed

global $db, $tpl;

// Handle form submission
if(isset($_POST['save_theme']))
{
	foreach($_POST['theme'] as $key => $value)
	{
		// Check if setting exists
		$res = $db->Query('SELECT id FROM {pre}mf_theme WHERE setting_key=?', $key);
		
		if($res->RowCount() > 0)
		{
			// Update existing
			$db->Query('UPDATE {pre}mf_theme SET setting_value=? WHERE setting_key=?', $value, $key);
		}
		else
		{
			// Insert new (determine type from key)
			$type = 'text';
			if(strpos($key, 'color') !== false) $type = 'color';
			if(strpos($key, 'logo') !== false || strpos($key, 'favicon') !== false) $type = 'image';
			
			$db->Query('INSERT INTO {pre}mf_theme(setting_key, setting_value, setting_type) VALUES(?,?,?)',
				$key, $value, $type
			);
		}
		$res->Free();
	}
	
	// Clear cache
	@unlink(B1GMAIL_DIR . 'cache/theme.cache');
	
	PutLog('ModernFrontend: Theme updated by admin #' . $_SESSION['admin_id'],
		PRIO_NOTE,
		__FILE__,
		__LINE__);
	
	header('Location: ' . $_SERVER['REQUEST_URI'] . '&saved=1');
	exit();
}

// Load theme settings
$theme = array();
$res = $db->Query('SELECT * FROM {pre}mf_theme ORDER BY setting_group, setting_key');
while($row = $res->FetchArray(MYSQLI_ASSOC))
{
	$theme[$row['setting_key']] = $row;
}
$res->Free();

// Theme configuration
$theme_config = array(
	'colors' => array(
		'title' => 'Farben',
		'settings' => array(
			'primary_color' => array('label' => 'Primärfarbe (aikQ Grün)', 'type' => 'color', 'default' => '#76B82A'),
			'primary_dark' => array('label' => 'Primärfarbe Dunkel', 'type' => 'color', 'default' => '#5D9321'),
			'primary_light' => array('label' => 'Primärfarbe Hell', 'type' => 'color', 'default' => '#8FC744'),
			'secondary_color' => array('label' => 'Sekundärfarbe', 'type' => 'color', 'default' => '#2C3E50'),
			'accent_color' => array('label' => 'Akzentfarbe', 'type' => 'color', 'default' => '#3498DB')
		)
	),
	'typography' => array(
		'title' => 'Typografie',
		'settings' => array(
			'font_primary' => array('label' => 'Primäre Schriftart', 'type' => 'select', 'options' => array('Inter', 'Roboto', 'Open Sans', 'Lato', 'Montserrat'), 'default' => 'Inter'),
			'font_heading' => array('label' => 'Überschriften-Schriftart', 'type' => 'select', 'options' => array('Poppins', 'Montserrat', 'Raleway', 'Oswald', 'Playfair Display'), 'default' => 'Poppins')
		)
	),
	'branding' => array(
		'title' => 'Branding',
		'settings' => array(
			'site_title' => array('label' => 'Seitentitel', 'type' => 'text', 'default' => 'aikQ Mail'),
			'logo_url' => array('label' => 'Logo URL', 'type' => 'text', 'default' => '', 'help' => 'Relativer oder absoluter Pfad zum Logo'),
			'favicon_url' => array('label' => 'Favicon URL', 'type' => 'text', 'default' => '', 'help' => 'Relativer oder absoluter Pfad zum Favicon')
		)
	)
);

$tpl->assign('theme', $theme);
$tpl->assign('theme_config', $theme_config);
$tpl->assign('saved', isset($_GET['saved']));
$tpl->assign('pageURL', 'admin/plugin.page.php?plugin=ModernFrontendPlugin');
$tpl->assign('page', MODERNFRONTEND_PATH . 'templates/admin/theme-editor.tpl');
?>
