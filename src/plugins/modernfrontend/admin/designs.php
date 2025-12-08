<?php
/**
 * ModernFrontend CMS - Design Verwaltung
 */

if(!defined('B1GMAIL_INIT'))
	die('Access denied');

// Security: Plugin is called from admin framework, no additional checks needed

// Load DesignManager
require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/designs/DesignManager.class.php');
$designManager = new DesignManager();

// Action handling
$action = isset($_REQUEST['do']) ? $_REQUEST['do'] : 'list';
$message = '';
$messageType = '';

// CREATE
if($action == 'create' && isset($_POST['submit'])) {
	$settings = array();
	if(isset($_POST['font_family'])) {
		$settings['font_family'] = $_POST['font_family'];
	}
	if(isset($_POST['border_radius'])) {
		$settings['border_radius'] = $_POST['border_radius'];
	}
	
	$result = $designManager->createDesign(array(
		'name' => $_POST['name'],
		'description' => $_POST['description'],
		'template_path' => $_POST['template_path'],
		'primary_color' => $_POST['primary_color'],
		'secondary_color' => $_POST['secondary_color'],
		'css_file' => 'style.css',
		'is_active' => isset($_POST['is_active']) ? 1 : 0,
		'settings' => $settings
	));
	
	if($result['success']) {
		$message = 'Design erfolgreich erstellt!';
		$messageType = 'success';
		$action = 'list';
	} else {
		$message = 'Fehler: ' . $result['error'];
		$messageType = 'error';
	}
}

// UPDATE
if($action == 'edit' && isset($_POST['submit'])) {
	$settings = array();
	if(isset($_POST['font_family'])) {
		$settings['font_family'] = $_POST['font_family'];
	}
	if(isset($_POST['border_radius'])) {
		$settings['border_radius'] = $_POST['border_radius'];
	}
	
	$result = $designManager->updateDesign($_POST['id'], array(
		'name' => $_POST['name'],
		'description' => $_POST['description'],
		'template_path' => $_POST['template_path'],
		'primary_color' => $_POST['primary_color'],
		'secondary_color' => $_POST['secondary_color'],
		'is_active' => isset($_POST['is_active']) ? 1 : 0,
		'settings' => $settings
	));
	
	if($result['success']) {
		$message = 'Design erfolgreich aktualisiert!';
		$messageType = 'success';
		$action = 'list';
	} else {
		$message = 'Fehler: ' . $result['error'];
		$messageType = 'error';
	}
}

// DELETE
if($action == 'delete' && isset($_GET['id'])) {
	$result = $designManager->deleteDesign($_GET['id']);
	
	if($result['success']) {
		$message = 'Design erfolgreich gelöscht!';
		$messageType = 'success';
	} else {
		$message = 'Fehler: ' . $result['error'];
		$messageType = 'error';
	}
	$action = 'list';
}

// DUPLICATE
if($action == 'duplicate' && isset($_GET['id'])) {
	$result = $designManager->duplicateDesign($_GET['id']);
	
	if($result['success']) {
		$message = 'Design erfolgreich dupliziert!';
		$messageType = 'success';
	} else {
		$message = 'Fehler: ' . $result['error'];
		$messageType = 'error';
	}
	$action = 'list';
}

// Daten für Template vorbereiten
$designs = $designManager->getAllDesigns(true);
$designsInUse = $designManager->getDesignsInUse();

// Usage-Count zu Designs hinzufügen
$usageMap = array();
foreach($designsInUse as $d) {
	$usageMap[$d['id']] = $d['usage_count'];
}

foreach($designs as &$design) {
	$design['usage_count'] = isset($usageMap[$design['id']]) ? $usageMap[$design['id']] : 0;
}

// Edit: Design laden
$editDesign = null;
if($action == 'edit' && isset($_GET['id'])) {
	$editDesign = $designManager->getDesignById($_GET['id']);
}

// An Template übergeben
$tpl->assign('action', $action);
$tpl->assign('designs', $designs);
$tpl->assign('editDesign', $editDesign);
$tpl->assign('message', $message);
$tpl->assign('messageType', $messageType);
$tpl->assign('designsCount', count($designs));
$tpl->assign('activeCount', count(array_filter($designs, function($d) { return $d['is_active'] == 1; })));
$tpl->assign('inUseCount', count($designsInUse));
?>
