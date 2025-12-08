<?php
/**
 * ModernFrontend CMS - Content Editor
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this script is not allowed');

// Security: Plugin is called from admin framework, no additional checks needed

global $db, $tpl;

// Handle form submission
if(isset($_POST['save_content']))
{
	foreach($_POST['content'] as $section => $items)
	{
		foreach($items as $key => $languages)
		{
			// Check if content exists
			$res = $db->Query('SELECT id FROM {pre}mf_content WHERE section=? AND `key`=?', $section, $key);
			
			if($res->RowCount() > 0)
			{
				// Update existing
				$row = $res->FetchArray(MYSQLI_ASSOC);
				$db->Query('UPDATE {pre}mf_content SET content_de=?, content_en=?, updated_by=? WHERE id=?',
					$languages['de'] ?? '',
					$languages['en'] ?? '',
					$_SESSION['admin_id'],
					$row['id']
				);
			}
			else
			{
				// Insert new
				$db->Query('INSERT INTO {pre}mf_content(section, `key`, content_de, content_en, created_by, updated_by) VALUES(?,?,?,?,?,?)',
					$section,
					$key,
					$languages['de'] ?? '',
					$languages['en'] ?? '',
					$_SESSION['admin_id'],
					$_SESSION['admin_id']
				);
			}
			$res->Free();
		}
	}
	
	PutLog('ModernFrontend: Content updated by admin #' . $_SESSION['admin_id'],
		PRIO_NOTE,
		__FILE__,
		__LINE__);
	
	header('Location: ' . $_SERVER['REQUEST_URI'] . '&saved=1');
	exit();
}

// Load all content
$content = array();
$res = $db->Query('SELECT * FROM {pre}mf_content ORDER BY section, `key`');
while($row = $res->FetchArray(MYSQLI_ASSOC))
{
	if(!isset($content[$row['section']]))
		$content[$row['section']] = array();
	$content[$row['section']][$row['key']] = $row;
}
$res->Free();

// Content sections configuration
$sections = array(
	'hero' => array(
		'title' => 'Hero Section',
		'fields' => array(
			'title' => 'Hauptüberschrift',
			'subtitle' => 'Unterüberschrift',
			'cta_text' => 'Button-Text',
			'cta_url' => 'Button-Link',
			'meta_description' => 'SEO Meta-Beschreibung'
		)
	),
	'features' => array(
		'title' => 'Features Section',
		'fields' => array(
			'title' => 'Überschrift',
			'subtitle' => 'Unterüberschrift'
		)
	),
	'packages' => array(
		'title' => 'Packages Section',
		'fields' => array(
			'title' => 'Überschrift',
			'subtitle' => 'Unterüberschrift'
		)
	),
	'footer' => array(
		'title' => 'Footer',
		'fields' => array(
			'company_description' => 'Firmenbeschreibung',
			'contact_email' => 'Kontakt E-Mail',
			'contact_phone' => 'Kontakt Telefon'
		)
	)
);

$tpl->assign('content', $content);
$tpl->assign('sections', $sections);
$tpl->assign('saved', isset($_GET['saved']));
$tpl->assign('pageURL', 'admin/plugin.page.php?plugin=ModernFrontendPlugin');
$tpl->assign('page', MODERNFRONTEND_PATH . 'templates/admin/content-editor.tpl');
?>
