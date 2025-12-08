<?php
/**
 * ModernFrontend CMS - Contact Forms
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this script is not allowed');

// Security: Plugin is called from admin framework, no additional checks needed

global $db, $tpl;

// Handle form save
if(isset($_POST['save_form']))
{
	$form_id = isset($_POST['form_id']) ? (int)$_POST['form_id'] : 0;
	$form_name = trim($_POST['form_name']);
	$form_key = trim($_POST['form_key']);
	$fields = isset($_POST['fields']) ? json_encode($_POST['fields']) : '[]';
	$notification_email = trim($_POST['notification_email']);
	$success_message_de = trim($_POST['success_message_de']);
	$success_message_en = trim($_POST['success_message_en']);
	
	if($form_id > 0)
	{
		// Update existing
		$db->Query('UPDATE {pre}mf_contact_forms SET form_name=?, fields=?, notification_email=?, success_message_de=?, success_message_en=? WHERE id=?',
			$form_name, $fields, $notification_email, $success_message_de, $success_message_en, $form_id
		);
		$success = 'Formular aktualisiert!';
	}
	else
	{
		// Create new
		$db->Query('INSERT INTO {pre}mf_contact_forms(form_name, form_key, fields, notification_email, success_message_de, success_message_en, status) VALUES(?,?,?,?,?,?,?)',
			$form_name, $form_key, $fields, $notification_email, $success_message_de, $success_message_en, 'active'
		);
		$success = 'Formular erstellt!';
	}
	
	PutLog('ModernFrontend: Contact form saved by admin #' . $_SESSION['admin_id'],
		PRIO_NOTE,
		__FILE__,
		__LINE__);
}

// Handle form delete
if(isset($_POST['delete_form']))
{
	$form_id = (int)$_POST['form_id'];
	$db->Query('DELETE FROM {pre}mf_contact_forms WHERE id=?', $form_id);
	$db->Query('DELETE FROM {pre}mf_contact_submissions WHERE form_id=?', $form_id);
	$success = 'Formular gelöscht!';
}

// Handle submission status change
if(isset($_POST['change_submission_status']))
{
	$submission_id = (int)$_POST['submission_id'];
	$new_status = $_POST['new_status'];
	if(in_array($new_status, array('new', 'read', 'replied', 'archived')))
	{
		$db->Query('UPDATE {pre}mf_contact_submissions SET status=? WHERE id=?', $new_status, $submission_id);
		$success = 'Status geändert!';
	}
}

// View submissions
if(isset($_GET['submissions']))
{
	$form_id = (int)$_GET['submissions'];
	
	// Get form info
	$res = $db->Query('SELECT * FROM {pre}mf_contact_forms WHERE id=?', $form_id);
	$form = $res->FetchArray(MYSQLI_ASSOC);
	$res->Free();
	
	// Get submissions
	$submissions = array();
	$res = $db->Query('SELECT * FROM {pre}mf_contact_submissions WHERE form_id=? ORDER BY created_at DESC', $form_id);
	while($row = $res->FetchArray(MYSQLI_ASSOC))
	{
		$row['form_data'] = json_decode($row['form_data'], true);
		$submissions[] = $row;
	}
	$res->Free();
	
	$tpl->assign('form', $form);
	$tpl->assign('submissions', $submissions);
	$tpl->assign('view', 'submissions');
	$tpl->assign('pageURL', 'admin/plugin.page.php?plugin=ModernFrontendPlugin');
	$tpl->assign('success', isset($success) ? $success : null);
	$tpl->assign('page', MODERNFRONTEND_PATH . 'templates/admin/forms.tpl');
	return;
}

// Get form to edit
$edit_form = null;
if(isset($_GET['edit']))
{
	$form_id = (int)$_GET['edit'];
	if($form_id > 0)
	{
		$res = $db->Query('SELECT * FROM {pre}mf_contact_forms WHERE id=?', $form_id);
		if($res->RowCount() == 1)
		{
			$edit_form = $res->FetchArray(MYSQLI_ASSOC);
			$edit_form['fields'] = json_decode($edit_form['fields'], true);
		}
		$res->Free();
	}
	else
	{
		// New form with default fields
		$edit_form = array(
			'id' => 0,
			'form_name' => '',
			'form_key' => '',
			'fields' => array(
				array('name' => 'name', 'type' => 'text', 'label_de' => 'Name', 'label_en' => 'Name', 'required' => true),
				array('name' => 'email', 'type' => 'email', 'label_de' => 'E-Mail', 'label_en' => 'Email', 'required' => true),
				array('name' => 'message', 'type' => 'textarea', 'label_de' => 'Nachricht', 'label_en' => 'Message', 'required' => true)
			),
			'notification_email' => '',
			'success_message_de' => 'Vielen Dank für Ihre Nachricht!',
			'success_message_en' => 'Thank you for your message!'
		);
	}
}

// Load all forms
$forms = array();
$res = $db->Query('SELECT f.*, (SELECT COUNT(*) FROM {pre}mf_contact_submissions WHERE form_id=f.id AND status="new") as unread_count FROM {pre}mf_contact_forms f ORDER BY f.form_name ASC');
while($row = $res->FetchArray(MYSQLI_ASSOC))
{
	$forms[] = $row;
}
$res->Free();

// Field types
$field_types = array(
	'text' => 'Text (einzeilig)',
	'email' => 'E-Mail',
	'tel' => 'Telefon',
	'textarea' => 'Text (mehrzeilig)',
	'select' => 'Dropdown',
	'checkbox' => 'Checkbox',
	'radio' => 'Radio-Buttons'
);

$tpl->assign('forms', $forms);
$tpl->assign('edit_form', $edit_form);
$tpl->assign('field_types', $field_types);
$tpl->assign('view', 'list');
$tpl->assign('success', isset($success) ? $success : null);
$tpl->assign('pageURL', 'admin/plugin.page.php?plugin=ModernFrontendPlugin');
$tpl->assign('page', MODERNFRONTEND_PATH . 'templates/admin/forms.tpl');
?>
