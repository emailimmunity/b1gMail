<?php
/**
 * ModernFrontend CMS - Page Builder
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this script is not allowed');

// Security: Plugin is called from admin framework, no additional checks needed

global $db, $tpl;

// Handle page save
if(isset($_POST['save_page']))
{
	$page_id = isset($_POST['page_id']) ? (int)$_POST['page_id'] : 0;
	$slug = trim($_POST['slug']);
	$title_de = trim($_POST['title_de']);
	$title_en = trim($_POST['title_en']);
	$meta_description_de = trim($_POST['meta_description_de']);
	$meta_description_en = trim($_POST['meta_description_en']);
	$template = $_POST['template'];
	$status = $_POST['status'];
	
	if($page_id > 0)
	{
		// Update existing
		$db->Query('UPDATE {pre}mf_pages SET slug=?, title_de=?, title_en=?, meta_description_de=?, meta_description_en=?, template=?, status=? WHERE id=?',
			$slug, $title_de, $title_en, $meta_description_de, $meta_description_en, $template, $status, $page_id
		);
		
		if($status == 'published')
			$db->Query('UPDATE {pre}mf_pages SET published_at=NOW() WHERE id=? AND published_at IS NULL', $page_id);
		
		$success = 'Seite aktualisiert!';
	}
	else
	{
		// Create new
		$db->Query('INSERT INTO {pre}mf_pages(slug, title_de, title_en, meta_description_de, meta_description_en, template, status, created_by) VALUES(?,?,?,?,?,?,?,?)',
			$slug, $title_de, $title_en, $meta_description_de, $meta_description_en, $template, $status, $_SESSION['admin_id']
		);
		$page_id = $db->InsertId();
		$success = 'Seite erstellt!';
	}
	
	// Save sections if provided
	if(isset($_POST['sections']))
	{
		// Delete old sections
		$db->Query('DELETE FROM {pre}mf_sections WHERE page_id=?', $page_id);
		
		// Insert new sections
		foreach($_POST['sections'] as $index => $section)
		{
			$db->Query('INSERT INTO {pre}mf_sections(page_id, section_type, content, sort_order) VALUES(?,?,?,?)',
				$page_id,
				$section['type'],
				json_encode($section['content']),
				$index
			);
		}
	}
	
	PutLog('ModernFrontend: Page saved by admin #' . $_SESSION['admin_id'],
		PRIO_NOTE,
		__FILE__,
		__LINE__);
}

// Handle page delete
if(isset($_POST['delete_page']))
{
	$page_id = (int)$_POST['page_id'];
	$db->Query('DELETE FROM {pre}mf_pages WHERE id=?', $page_id);
	$db->Query('DELETE FROM {pre}mf_sections WHERE page_id=?', $page_id);
	$success = 'Seite gelöscht!';
}

// Get page to edit
$edit_page = null;
$sections = array();
if(isset($_GET['edit']))
{
	$page_id = (int)$_GET['edit'];
	if($page_id > 0)
	{
		$res = $db->Query('SELECT * FROM {pre}mf_pages WHERE id=?', $page_id);
		if($res->RowCount() == 1)
		{
			$edit_page = $res->FetchArray(MYSQLI_ASSOC);
			
			// Load sections
			$resS = $db->Query('SELECT * FROM {pre}mf_sections WHERE page_id=? ORDER BY sort_order ASC', $page_id);
			while($row = $resS->FetchArray(MYSQLI_ASSOC))
			{
				$row['content'] = json_decode($row['content'], true);
				$sections[] = $row;
			}
			$resS->Free();
		}
		$res->Free();
	}
	else
	{
		// New page
		$edit_page = array(
			'id' => 0,
			'slug' => '',
			'title_de' => '',
			'title_en' => '',
			'meta_description_de' => '',
			'meta_description_en' => '',
			'template' => 'default',
			'status' => 'draft'
		);
	}
}

// Load all pages
$pages = array();
$res = $db->Query('SELECT p.*, (SELECT COUNT(*) FROM {pre}mf_sections WHERE page_id=p.id) as section_count FROM {pre}mf_pages p ORDER BY p.created_at DESC');
while($row = $res->FetchArray(MYSQLI_ASSOC))
{
	$pages[] = $row;
}
$res->Free();

// Section types
$section_types = array(
	'hero' => array('name' => 'Hero Section', 'icon' => '🎯'),
	'text' => array('name' => 'Text Block', 'icon' => '📝'),
	'image' => array('name' => 'Bild', 'icon' => '🖼️'),
	'gallery' => array('name' => 'Galerie', 'icon' => '🏞️'),
	'features' => array('name' => 'Features Grid', 'icon' => '⭐'),
	'testimonials' => array('name' => 'Testimonials', 'icon' => '💬'),
	'cta' => array('name' => 'Call-to-Action', 'icon' => '🎯'),
	'faq' => array('name' => 'FAQ', 'icon' => '❓'),
	'contact' => array('name' => 'Kontaktformular', 'icon' => '📧')
);

$tpl->assign('pages', $pages);
$tpl->assign('edit_page', $edit_page);
$tpl->assign('sections', $sections);
$tpl->assign('section_types', $section_types);
$tpl->assign('success', isset($success) ? $success : null);
$tpl->assign('pageURL', 'admin/plugin.page.php?plugin=ModernFrontendPlugin');
$tpl->assign('page', MODERNFRONTEND_PATH . 'templates/admin/pagebuilder.tpl');
?>
