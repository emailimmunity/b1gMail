<?php
/**
 * ModernFrontend CMS - Email Templates
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this script is not allowed');

// Security: Plugin is called from admin framework, no additional checks needed

global $db, $tpl;

// Handle template save
if(isset($_POST['save_template']))
{
	$template_id = isset($_POST['template_id']) ? (int)$_POST['template_id'] : 0;
	$template_name = trim($_POST['template_name']);
	$template_key = trim($_POST['template_key']);
	$subject_de = trim($_POST['subject_de']);
	$subject_en = trim($_POST['subject_en']);
	$body_html_de = $_POST['body_html_de'];
	$body_html_en = $_POST['body_html_en'];
	$body_text_de = trim($_POST['body_text_de']);
	$body_text_en = trim($_POST['body_text_en']);
	$variables = isset($_POST['variables']) ? json_encode($_POST['variables']) : '[]';
	
	if($template_id > 0)
	{
		// Update existing
		$db->Query('UPDATE {pre}mf_email_templates SET template_name=?, subject_de=?, subject_en=?, body_html_de=?, body_html_en=?, body_text_de=?, body_text_en=?, variables=? WHERE id=?',
			$template_name, $subject_de, $subject_en, $body_html_de, $body_html_en, $body_text_de, $body_text_en, $variables, $template_id
		);
		$success = 'Template aktualisiert!';
	}
	else
	{
		// Create new
		$db->Query('INSERT INTO {pre}mf_email_templates(template_name, template_key, subject_de, subject_en, body_html_de, body_html_en, body_text_de, body_text_en, variables, status) VALUES(?,?,?,?,?,?,?,?,?,?)',
			$template_name, $template_key, $subject_de, $subject_en, $body_html_de, $body_html_en, $body_text_de, $body_text_en, $variables, 'active'
		);
		$success = 'Template erstellt!';
	}
	
	PutLog('ModernFrontend: Email template saved by admin #' . $_SESSION['admin_id'],
		PRIO_NOTE,
		__FILE__,
		__LINE__);
}

// Handle template delete
if(isset($_POST['delete_template']))
{
	$template_id = (int)$_POST['template_id'];
	$db->Query('DELETE FROM {pre}mf_email_templates WHERE id=?', $template_id);
	$success = 'Template gelöscht!';
}

// Handle status change
if(isset($_POST['change_status']))
{
	$template_id = (int)$_POST['template_id'];
	$new_status = $_POST['new_status'];
	if(in_array($new_status, array('active', 'inactive')))
	{
		$db->Query('UPDATE {pre}mf_email_templates SET status=? WHERE id=?', $new_status, $template_id);
		$success = 'Status geändert!';
	}
}

// Get template to edit
$edit_template = null;
if(isset($_GET['edit']))
{
	$template_id = (int)$_GET['edit'];
	$res = $db->Query('SELECT * FROM {pre}mf_email_templates WHERE id=?', $template_id);
	if($res->RowCount() == 1)
	{
		$edit_template = $res->FetchArray(MYSQLI_ASSOC);
		$edit_template['variables'] = json_decode($edit_template['variables'], true);
	}
	$res->Free();
}

// Load all templates
$templates = array();
$res = $db->Query('SELECT * FROM {pre}mf_email_templates ORDER BY template_name ASC');
while($row = $res->FetchArray(MYSQLI_ASSOC))
{
	$row['variables'] = json_decode($row['variables'], true);
	$templates[] = $row;
}
$res->Free();

// Default variables
$default_variables = array(
	'{user_name}' => 'Name des Benutzers',
	'{user_email}' => 'E-Mail-Adresse',
	'{site_name}' => 'Seiten-Name',
	'{site_url}' => 'Seiten-URL',
	'{date}' => 'Aktuelles Datum',
	'{package_name}' => 'Paket-Name',
	'{package_price}' => 'Paket-Preis'
);

$tpl->assign('templates', $templates);
$tpl->assign('edit_template', $edit_template);
$tpl->assign('default_variables', $default_variables);
$tpl->assign('success', isset($success) ? $success : null);
$tpl->assign('pageURL', 'admin/plugin.page.php?plugin=ModernFrontendPlugin');
$tpl->assign('page', MODERNFRONTEND_PATH . 'templates/admin/emails.tpl');
?>
