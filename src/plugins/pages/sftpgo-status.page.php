<?php
/**
 * SFTPGo Status Admin Page
 * Zeigt SFTPGo SFTP/FTPS/S3/WebDAV Status und Statistiken
 * 
 * Datum: 31. Oktober 2025
 */

if(!defined('B1GMAIL_DIR'))
	die('Direct access not permitted');

/**
 * Render SFTPGo Status Page
 */
function renderSFTPGoStatusPage(&$plugin, &$tpl)
{
	global $db, $lang_admin;
	
	// Load bridge
	require_once(B1GMAIL_DIR . 'serverlib/bridges/sftpgo-bridge.inc.php');
	$bridge = new BMSFTPGoBridge();
	
	// Handle user sync action
	if(isset($_REQUEST['do']) && $_REQUEST['do'] == 'sync_user' && isset($_REQUEST['userid'])) {
		$result = $bridge->syncUser($_REQUEST['userid']);
		$tpl->assign('sync_result', $result);
	}
	
	// Test connection
	$connection = $bridge->testConnection();
	$tpl->assign('sftpgo_connection', $connection);
	
	// Get current stats
	$stats = $bridge->getStats();
	$tpl->assign('sftpgo_stats', $stats);
	
	// Get historical stats (last 30 days)
	$historicalStats = array();
	$res = $db->Query('SELECT date, sftp_connections, ftps_connections, webdav_requests,
		uploads_count, downloads_count, uploads_mb, downloads_mb, storage_used_mb
		FROM {pre}sftpgo_stats 
		WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
		ORDER BY date DESC');
	while($row = $res->FetchArray(MYSQLI_ASSOC)) {
		$historicalStats[] = $row;
	}
	$res->Free();
	$tpl->assign('sftpgo_historical', $historicalStats);
	
	// Get active connections
	$connections = $bridge->getActiveConnections(20);
	$tpl->assign('sftpgo_connections', $connections);
	
	// Get SFTP-enabled users
	$sftpUsers = array();
	$res = $db->Query('SELECT u.id, u.email, u.vorname, u.nachname 
		FROM {pre}users u
		INNER JOIN {pre}gruppen g ON u.gruppe = g.id
		WHERE g.sftpgo = \'yes\'
		ORDER BY u.email ASC
		LIMIT 50');
	while($row = $res->FetchArray(MYSQLI_ASSOC)) {
		$sftpUsers[] = $row;
	}
	$res->Free();
	$tpl->assign('sftpgo_users', $sftpUsers);
	
	// Config info
	$config = array(
		'api_url' => getenv('SFTPGO_API_URL') ?: 'http://localhost:8080',
		'sftp_host' => getenv('SFTPGO_SFTP_HOST') ?: 'localhost',
		'sftp_port' => getenv('SFTPGO_SFTP_PORT') ?: 2022,
		'ftps_port' => getenv('SFTPGO_FTPS_PORT') ?: 2021,
		'webdav_url' => getenv('SFTPGO_WEBDAV_URL') ?: 'http://localhost:8090',
		's3_endpoint' => getenv('SFTPGO_S3_ENDPOINT') ?: 'http://localhost:9000'
	);
	$tpl->assign('sftpgo_config', $config);
	
	// Return template path
	return $plugin->_templatePath('bms.admin.sftpgo.tpl');
}

?>
