<?php
/**
 * ModernFrontend CMS - Dashboard Admin Page
 */

// Security check
if(!defined('B1GMAIL_INIT'))
	die('Access denied');

// Get base URL for this plugin page WITH SESSION ID
// b1gMail Admin uses sid URL parameter for session management
// Take sid from current URL if present, otherwise use session_id()
$sid = isset($_REQUEST['sid']) ? $_REQUEST['sid'] : session_id();
$pageURL = 'plugin.page.php?plugin=ModernFrontendPlugin&sid=' . $sid;

// Get statistics (with error handling)
$stats = array(
	'content_count' => 0,
	'media_count' => 0,
	'pages_count' => 0,
	'pageviews_7d' => 0,
	'ab_tests_running' => 0,
	'unread_messages' => 0
);

try {
	$result = $db->Query('SELECT COUNT(*) as c FROM {pre}mf_content');
	if($row = $result->FetchArray()) $stats['content_count'] = $row['c'];
} catch(Exception $e) {}

try {
	$result = $db->Query('SELECT COUNT(*) as c FROM {pre}mf_media');
	if($row = $result->FetchArray()) $stats['media_count'] = $row['c'];
} catch(Exception $e) {}

try {
	$result = $db->Query('SELECT COUNT(*) as c FROM {pre}mf_pages');
	if($row = $result->FetchArray()) $stats['pages_count'] = $row['c'];
} catch(Exception $e) {}

try {
	$result = $db->Query('SELECT COUNT(*) as c FROM {pre}mf_analytics WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
	if($row = $result->FetchArray()) $stats['pageviews_7d'] = $row['c'];
} catch(Exception $e) {}

try {
	$result = $db->Query('SELECT COUNT(*) as c FROM {pre}mf_ab_tests WHERE is_active=1');
	if($row = $result->FetchArray()) $stats['ab_tests_running'] = $row['c'];
} catch(Exception $e) {}

try {
	$result = $db->Query('SELECT COUNT(*) as c FROM {pre}mf_contact_submissions WHERE status="new"');
	if($row = $result->FetchArray()) $stats['unread_messages'] = $row['c'];
} catch(Exception $e) {}

// Get recent activity (last 5 content updates)
$activity = array();
$result = $db->Query('SELECT * FROM {pre}mf_content ORDER BY updated_at DESC LIMIT 5');
while($row = $result->FetchArray(MYSQLI_ASSOC)) {
	$activity[] = $row;
}

// Get theme settings
$theme = array(
	'primary_color' => '#76B82A',
	'site_title' => 'aikQ Mail'
);
$themeResult = $db->Query('SELECT * FROM {pre}mf_theme LIMIT 1');
if($themeRow = $themeResult->FetchArray(MYSQLI_ASSOC)) {
	$theme['primary_color'] = $themeRow['primary_color'];
}

// Get settings
$settings = array(
	'analytics_enabled' => '1'
);
$settingsResult = $db->Query('SELECT * FROM {pre}mf_settings WHERE setting_key="analytics_enabled"');
if($settingRow = $settingsResult->FetchArray(MYSQLI_ASSOC)) {
	$settings['analytics_enabled'] = $settingRow['setting_value'];
}

// Assign to template
$tpl->assign('pageURL', $pageURL);
$tpl->assign('stats', $stats);
$tpl->assign('activity', $activity);
$tpl->assign('theme', $theme);
$tpl->assign('settings', $settings);

// Define version constant if not exists
if(!defined('MODERNFRONTEND_VERSION')) {
	define('MODERNFRONTEND_VERSION', '1.0.0');
}
?>
