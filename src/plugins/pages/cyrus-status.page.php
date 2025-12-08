<?php
/**
 * Cyrus Status Admin Page
 * Zeigt Cyrus IMAP/POP3/JMAP Status und Statistiken
 * 
 * Datum: 31. Oktober 2025
 */

if(!defined('B1GMAIL_DIR'))
	die('Direct access not permitted');

/**
 * Render Cyrus Status Page
 */
function renderCyrusStatusPage(&$plugin, &$tpl)
{
	global $db, $lang_admin;
	
	// Load bridge
	require_once(B1GMAIL_DIR . 'serverlib/bridges/cyrus-bridge.inc.php');
	$bridge = new BMCyrusBridge();
	
	// Test connection
	$connection = $bridge->testConnection();
	$tpl->assign('cyrus_connection', $connection);
	
	// Get current stats
	$stats = $bridge->getStats();
	$tpl->assign('cyrus_stats', $stats);
	
	// Get historical stats (last 30 days)
	$historicalStats = array();
	$res = $db->Query('SELECT date, imap_connections, pop3_connections, jmap_requests, 
		traffic_in_mb, traffic_out_mb, storage_used_mb
		FROM {pre}cyrus_stats 
		WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
		ORDER BY date DESC');
	while($row = $res->FetchArray(MYSQLI_ASSOC)) {
		$historicalStats[] = $row;
	}
	$res->Free();
	$tpl->assign('cyrus_historical', $historicalStats);
	
	// Get top users by quota
	$topUsers = array();
	$res = $db->Query('SELECT u.id, u.email, u.vorname, u.nachname 
		FROM {pre}users u
		INNER JOIN {pre}gruppen g ON u.gruppe = g.id
		WHERE g.cyrus = \'yes\'
		ORDER BY u.id DESC
		LIMIT 10');
	while($row = $res->FetchArray(MYSQLI_ASSOC)) {
		$quota = $bridge->getUserQuota($row['email']);
		$topUsers[] = array_merge($row, array('quota' => $quota));
	}
	$res->Free();
	$tpl->assign('cyrus_top_users', $topUsers);
	
	// Config info
	$config = array(
		'host' => getenv('CYRUS_HOST') ?: 'localhost',
		'imap_port' => getenv('CYRUS_IMAP_PORT') ?: 143,
		'pop3_port' => getenv('CYRUS_POP3_PORT') ?: 110,
		'jmap_url' => getenv('CYRUS_JMAP_URL') ?: 'http://localhost:8008'
	);
	$tpl->assign('cyrus_config', $config);
	
	// Return template path
	return $plugin->_templatePath('bms.admin.cyrus.tpl');
}

?>
