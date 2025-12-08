<?php
/**
 * Modern Statistics Dashboard
 * Vereinheitlichtes Dashboard für alle Protokolle
 * 
 * Datum: 31. Oktober 2025
 */

if(!defined('B1GMAIL_DIR'))
	die('Direct access not permitted');

/**
 * Render Modern Stats Dashboard
 */
function renderModernStatsDashboard(&$plugin, &$tpl)
{
	global $db, $lang_admin;
	
	// Load all bridges
	require_once(B1GMAIL_DIR . 'serverlib/bridges/cyrus-bridge.inc.php');
	require_once(B1GMAIL_DIR . 'serverlib/bridges/grommunio-bridge.inc.php');
	require_once(B1GMAIL_DIR . 'serverlib/bridges/sftpgo-bridge.inc.php');
	require_once(B1GMAIL_DIR . 'serverlib/bridges/postfix-bridge.inc.php');
	
	$cyrusBridge = new BMCyrusBridge();
	$grommunioBridge = new BMGrommunioBridge();
	$sftpgoBridge = new BMSFTPGoBridge();
	$postfixBridge = new BMPostfixBridge();
	
	// Get all current stats
	$allStats = array(
		'cyrus' => $cyrusBridge->getStats(),
		'grommunio' => $grommunioBridge->getStats(),
		'sftpgo' => $sftpgoBridge->getStats(),
		'postfix' => $postfixBridge->getStats()
	);
	$tpl->assign('all_stats', $allStats);
	
	// Get connection status
	$connections = array(
		'cyrus' => $cyrusBridge->testConnection(),
		'grommunio' => $grommunioBridge->testConnection(),
		'sftpgo' => $sftpgoBridge->testConnection(),
		'postfix' => $postfixBridge->testConnection()
	);
	$tpl->assign('connections', $connections);
	
	// Prepare chart data (last 30 days)
	$chartData = array(
		'labels' => array(),
		'cyrus_connections' => array(),
		'grommunio_connections' => array(),
		'sftpgo_connections' => array(),
		'postfix_sent' => array()
	);
	
	$res = $db->Query('SELECT date FROM {pre}cyrus_stats 
		WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
		ORDER BY date ASC');
	
	while($row = $res->FetchArray(MYSQLI_ASSOC)) {
		$date = $row['date'];
		$chartData['labels'][] = date('d.m', strtotime($date));
		
		// Cyrus data
		$cyrusRow = $db->Query('SELECT imap_connections + pop3_connections as total 
			FROM {pre}cyrus_stats WHERE date = ?', $date)->FetchArray(MYSQLI_ASSOC);
		$chartData['cyrus_connections'][] = $cyrusRow ? $cyrusRow['total'] : 0;
		
		// Grommunio data
		$grommunioRow = $db->Query('SELECT mapi_connections 
			FROM {pre}grommunio_stats WHERE date = ?', $date)->FetchArray(MYSQLI_ASSOC);
		$chartData['grommunio_connections'][] = $grommunioRow ? $grommunioRow['mapi_connections'] : 0;
		
		// SFTPGo data
		$sftpgoRow = $db->Query('SELECT sftp_connections + ftps_connections as total 
			FROM {pre}sftpgo_stats WHERE date = ?', $date)->FetchArray(MYSQLI_ASSOC);
		$chartData['sftpgo_connections'][] = $sftpgoRow ? $sftpgoRow['total'] : 0;
		
		// Postfix data
		$postfixRow = $db->Query('SELECT emails_sent 
			FROM {pre}postfix_stats WHERE date = ?', $date)->FetchArray(MYSQLI_ASSOC);
		$chartData['postfix_sent'][] = $postfixRow ? $postfixRow['emails_sent'] : 0;
	}
	$res->Free();
	
	$tpl->assign('chart_data', $chartData);
	
	// Summary stats
	$summary = array(
		'total_connections_today' => 
			($allStats['cyrus']['imap_connections_today'] ?? 0) +
			($allStats['cyrus']['pop3_connections_today'] ?? 0) +
			($allStats['grommunio']['mapi_connections_today'] ?? 0) +
			($allStats['sftpgo']['sftp_connections_today'] ?? 0),
		'total_emails_today' =>
			($allStats['postfix']['emails_sent_today'] ?? 0) +
			($allStats['postfix']['emails_received_today'] ?? 0),
		'total_storage_mb' =>
			($allStats['cyrus']['storage_used_mb'] ?? 0) +
			($allStats['grommunio']['storage_used_mb'] ?? 0) +
			($allStats['sftpgo']['storage_used_mb'] ?? 0),
		'total_traffic_mb_today' =>
			($allStats['cyrus']['traffic_in_mb_today'] ?? 0) +
			($allStats['cyrus']['traffic_out_mb_today'] ?? 0) +
			($allStats['postfix']['traffic_in_mb_today'] ?? 0) +
			($allStats['postfix']['traffic_out_mb_today'] ?? 0) +
			($allStats['sftpgo']['uploads_mb_today'] ?? 0) +
			($allStats['sftpgo']['downloads_mb_today'] ?? 0)
	);
	$tpl->assign('summary', $summary);
	
	// Return template path
	return $plugin->_templatePath('bms.admin.stats.modern.tpl');
}

?>
