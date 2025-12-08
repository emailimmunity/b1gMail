<?php
/**
 * Grommunio Status Admin Page
 * Zeigt Grommunio MAPI/EWS/EAS Status und Statistiken
 * 
 * Datum: 31. Oktober 2025
 */

if(!defined('B1GMAIL_DIR'))
	die('Direct access not permitted');

/**
 * Render Grommunio Status Page
 */
function renderGrommunioStatusPage(&$plugin, &$tpl)
{
	global $db, $lang_admin;
	
	// Load bridge
	require_once(B1GMAIL_DIR . 'serverlib/bridges/grommunio-bridge.inc.php');
	$bridge = new BMGrommunioBridge();
	
	// Handle user sync action
	if(isset($_REQUEST['do']) && $_REQUEST['do'] == 'sync_user' && isset($_REQUEST['userid'])) {
		$result = $bridge->syncUser($_REQUEST['userid']);
		$tpl->assign('sync_result', $result);
	}
	
	// Test connection
	$connection = $bridge->testConnection();
	$tpl->assign('grommunio_connection', $connection);
	
	// Get current stats
	$stats = $bridge->getStats();
	$tpl->assign('grommunio_stats', $stats);
	
	// Get historical stats (last 30 days)
	$historicalStats = array();
	$res = $db->Query('SELECT date, mapi_connections, ews_requests, eas_sync_requests,
		active_users, sync_operations, sync_errors
		FROM {pre}grommunio_stats 
		WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
		ORDER BY date DESC');
	while($row = $res->FetchArray(MYSQLI_ASSOC)) {
		$historicalStats[] = $row;
	}
	$res->Free();
	$tpl->assign('grommunio_historical', $historicalStats);
	
	// Get ActiveSync devices
	$devices = $bridge->getActiveDevices(20);
	$tpl->assign('grommunio_devices', $devices);
	
	// Get sync-enabled users
	$syncUsers = array();
	$res = $db->Query('SELECT u.id, u.email, u.vorname, u.nachname 
		FROM {pre}users u
		INNER JOIN {pre}gruppen g ON u.gruppe = g.id
		WHERE g.grommunio = \'yes\'
		ORDER BY u.email ASC
		LIMIT 50');
	while($row = $res->FetchArray(MYSQLI_ASSOC)) {
		$syncUsers[] = $row;
	}
	$res->Free();
	$tpl->assign('grommunio_sync_users', $syncUsers);
	
	// Config info
	$config = array(
		'api_url' => getenv('GROMMUNIO_API_URL') ?: 'https://192.168.178.144:8443/api/v1',
		'mapi_url' => getenv('GROMMUNIO_MAPI_URL') ?: 'https://192.168.178.144',
		'ews_url' => getenv('GROMMUNIO_EWS_URL') ?: 'https://192.168.178.144/EWS/Exchange.asmx',
		'eas_url' => getenv('GROMMUNIO_EAS_URL') ?: 'https://192.168.178.144/Microsoft-Server-ActiveSync'
	);
	$tpl->assign('grommunio_config', $config);
	
	// Return template path
	return $plugin->_templatePath('bms.admin.grommunio.tpl');
}

?>
