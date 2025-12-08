<?php
/**
 * ModernFrontend Admin Initialization
 * MUST be included in every admin/*.php file
 * Ensures consistent session handling and URL building
 */

if(!defined('B1GMAIL_INIT'))
	die('Access denied');

// Build consistent base URL with session ID
// Session management in b1gMail uses 'sid' URL parameter
$sid = isset($_REQUEST['sid']) ? $_REQUEST['sid'] : session_id();

// Build admin base from first /admin/ occurrence to avoid duplication
$scr = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
$pos = strpos($scr, '/admin/');
$adminBase = ($pos !== false) ? substr($scr, 0, $pos) . '/admin' : '/admin';

// Construct complete base URL with plugin and sid
$pageURL = $adminBase . '/plugin.page.php?plugin=ModernFrontendPlugin&sid=' . urlencode($sid);

// Assign to template (global $tpl must be available)
if(isset($tpl)) {
	$tpl->assign('pageURL', $pageURL);
}
?>
