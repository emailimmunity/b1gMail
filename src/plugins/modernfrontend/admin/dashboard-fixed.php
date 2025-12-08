<?php
/**
 * ModernFrontend CMS - Dashboard Admin Page - FIXED SID
 */

// Security check
if(!defined('B1GMAIL_INIT'))
	die('Access denied');

// Get base URL for this plugin page WITH SESSION ID
// b1gMail Admin uses sid URL parameter for session management
$sid = isset($_REQUEST['sid']) ? $_REQUEST['sid'] : session_id();
$pageURL = 'plugin.page.php?plugin=ModernFrontendPlugin&sid=' . urlencode($sid);

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
try {
	$themeResult = $db->Query('SELECT setting_key, setting_value FROM {pre}mf_theme');
	while($themeRow = $themeResult->FetchArray(MYSQLI_ASSOC)) {
		if($themeRow['setting_key'] == 'primary_color') {
			$theme['primary_color'] = $themeRow['setting_value'];
		}
		if($themeRow['setting_key'] == 'site_title') {
			$theme['site_title'] = $themeRow['setting_value'];
		}
	}
} catch(Exception $e) {
	// Use defaults if table doesn't exist yet
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
