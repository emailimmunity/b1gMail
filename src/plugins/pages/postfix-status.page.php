<?php
/**
 * Postfix Status Admin Page
 * Zeigt Postfix SMTP Gateway Status und Statistiken
 * 
 * Datum: 31. Oktober 2025
 */

if(!defined('B1GMAIL_DIR'))
	die('Direct access not permitted');

/**
 * Render Postfix Status Page
 */
function renderPostfixStatusPage(&$plugin, &$tpl)
{
	global $db, $lang_admin;
	
	// Load bridge
	require_once(B1GMAIL_DIR . 'serverlib/bridges/postfix-bridge.inc.php');
	$bridge = new BMPostfixBridge();
	
	// Handle queue actions
	if(isset($_REQUEST['do'])) {
		if($_REQUEST['do'] == 'flush_queue') {
			$result = $bridge->flushQueue();
			$tpl->assign('action_result', array('success' => $result, 'message' => 'Queue flushed'));
		}
		else if($_REQUEST['do'] == 'delete_message' && isset($_REQUEST['queue_id'])) {
			$result = $bridge->deleteQueueMessage($_REQUEST['queue_id']);
			$tpl->assign('action_result', $result);
		}
	}
	
	// Test connection
	$connection = $bridge->testConnection();
	$tpl->assign('postfix_connection', $connection);
	
	// Get current stats
	$stats = $bridge->getStats();
	$tpl->assign('postfix_stats', $stats);
	
	// Get historical stats (last 30 days)
	$historicalStats = array();
	$res = $db->Query('SELECT date, emails_sent, emails_received, emails_bounced,
		emails_rejected, queue_size, traffic_in_mb, traffic_out_mb
		FROM {pre}postfix_stats 
		WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
		ORDER BY date DESC');
	while($row = $res->FetchArray(MYSQLI_ASSOC)) {
		$historicalStats[] = $row;
	}
	$res->Free();
	$tpl->assign('postfix_historical', $historicalStats);
	
	// Get queue status
	$queueStatus = $bridge->getQueueStatus();
	$tpl->assign('postfix_queue_status', $queueStatus);
	
	// Get queue messages
	$queueMessages = $bridge->getQueueMessages(50);
	$tpl->assign('postfix_queue_messages', $queueMessages);
	
	// Config info
	$config = array(
		'host' => getenv('POSTFIX_HOST') ?: 'localhost',
		'smtp_port' => getenv('POSTFIX_SMTP_PORT') ?: 25,
		'submission_port' => getenv('POSTFIX_SUBMISSION_PORT') ?: 587,
		'smtps_port' => getenv('POSTFIX_SMTPS_PORT') ?: 465,
		'queue_dir' => getenv('POSTFIX_QUEUE_DIR') ?: '/var/spool/postfix'
	);
	$tpl->assign('postfix_config', $config);
	
	// Return template path
	return $plugin->_templatePath('bms.admin.postfix.tpl');
}

?>
