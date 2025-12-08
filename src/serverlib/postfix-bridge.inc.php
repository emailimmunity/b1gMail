<?php
/**
 * Postfix SMTP Bridge
 * Vollständige Integration für SMTP Stats und Queue Management
 */

class BMPostfixBridge
{
	private $server;
	private $port;
	
	public function __construct()
	{
		$this->server = defined('POSTFIX_SERVER') ? POSTFIX_SERVER : 'localhost';
		$this->port = defined('SMTP_PORT') ? SMTP_PORT : 25;
	}
	
	/**
	 * Test SMTP Connection
	 */
	public function testConnection()
	{
		$result = array(
			'success' => false,
			'message' => '',
			'banner' => ''
		);
		
		try {
			$connection = @fsockopen($this->server, $this->port, $errno, $errstr, 5);
			if($connection) {
				$banner = fgets($connection);
				$result['success'] = true;
				$result['banner'] = trim($banner);
				$result['message'] = 'Verbindung erfolgreich';
				fclose($connection);
			} else {
				$result['message'] = 'Verbindung fehlgeschlagen: ' . $errstr;
			}
		} catch(Exception $e) {
			$result['message'] = 'Fehler: ' . $e->getMessage();
		}
		
		return $result;
	}
	
	/**
	 * Get SMTP Stats from database
	 */
	public function getStats()
	{
		global $db;
		
		if(!isset($db)) {
			return array();
		}
		
		$stats = array(
			'today_sent' => 0,
			'today_received' => 0,
			'today_bounced' => 0,
			'today_deferred' => 0,
			'week_sent' => 0,
			'month_sent' => 0,
			'total_sent' => 0,
			'queue_active' => 0,
			'queue_deferred' => 0,
			'queue_hold' => 0
		);
		
		try {
			// Today's stats
			$res = $db->Query('SELECT 
				SUM(sent) as sent, 
				SUM(received) as received,
				SUM(bounced) as bounced,
				SUM(deferred) as deferred
				FROM {pre}smtp_stats WHERE date = CURDATE()');
			if($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$stats['today_sent'] = (int)$row['sent'];
				$stats['today_received'] = (int)$row['received'];
				$stats['today_bounced'] = (int)$row['bounced'];
				$stats['today_deferred'] = (int)$row['deferred'];
			}
			$res->Free();
			
			// Week stats
			$res = $db->Query('SELECT SUM(sent) as sent FROM {pre}smtp_stats 
				WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)');
			if($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$stats['week_sent'] = (int)$row['sent'];
			}
			$res->Free();
			
			// Month stats
			$res = $db->Query('SELECT SUM(sent) as sent FROM {pre}smtp_stats 
				WHERE YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())');
			if($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$stats['month_sent'] = (int)$row['sent'];
			}
			$res->Free();
			
			// Queue stats (from bms_queue)
			$res = $db->Query('SELECT COUNT(*) FROM {pre}bms_queue WHERE `status`="active"');
			list($stats['queue_active']) = $res->FetchArray(MYSQLI_NUM);
			$res->Free();
			
			$res = $db->Query('SELECT COUNT(*) FROM {pre}bms_queue WHERE `status`="deferred"');
			list($stats['queue_deferred']) = $res->FetchArray(MYSQLI_NUM);
			$res->Free();
			
			$res = $db->Query('SELECT COUNT(*) FROM {pre}bms_queue WHERE `status`="hold"');
			list($stats['queue_hold']) = $res->FetchArray(MYSQLI_NUM);
			$res->Free();
			
		} catch(Exception $e) {
			// Ignore database errors
		}
		
		return $stats;
	}
	
	/**
	 * Get Queue Messages
	 */
	public function getQueueMessages($limit = 50)
	{
		global $db;
		
		if(!isset($db)) {
			return array();
		}
		
		$messages = array();
		
		try {
			$res = $db->Query('SELECT * FROM {pre}bms_queue 
				ORDER BY `added` DESC LIMIT ?', $limit);
			while($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$messages[] = $row;
			}
			$res->Free();
		} catch(Exception $e) {
			// Ignore
		}
		
		return $messages;
	}
	
	/**
	 * Get Postfix Queue Status (via postqueue)
	 */
	public function getPostfixQueueStatus()
	{
		$result = array(
			'available' => false,
			'active' => 0,
			'deferred' => 0,
			'hold' => 0,
			'message' => ''
		);
		
		// Check if postqueue command is available
		$output = array();
		$returnVar = 0;
		@exec('which postqueue 2>&1', $output, $returnVar);
		
		if($returnVar !== 0) {
			$result['message'] = 'postqueue Befehl nicht verfügbar';
			return $result;
		}
		
		$result['available'] = true;
		
		// Get queue status
		$output = array();
		@exec('postqueue -p 2>&1', $output, $returnVar);
		
		if($returnVar === 0 && count($output) > 0) {
			// Parse output for queue counts
			$queueText = implode("\n", $output);
			
			// Count messages in different queues
			if(preg_match('/(\d+)\s+requests?/i', $queueText, $matches)) {
				$result['active'] = (int)$matches[1];
			}
			
			$result['message'] = 'Queue-Status abgerufen';
		} else {
			$result['message'] = 'Konnte Queue-Status nicht abrufen';
		}
		
		return $result;
	}
	
	/**
	 * Flush Queue
	 */
	public function flushQueue()
	{
		$result = array(
			'success' => false,
			'message' => ''
		);
		
		$output = array();
		$returnVar = 0;
		@exec('postqueue -f 2>&1', $output, $returnVar);
		
		if($returnVar === 0) {
			$result['success'] = true;
			$result['message'] = 'Queue wurde geflushed';
		} else {
			$result['message'] = 'Fehler beim Flushen der Queue: ' . implode(' ', $output);
		}
		
		return $result;
	}
	
	/**
	 * Delete message from queue
	 */
	public function deleteQueueMessage($queueId)
	{
		$result = array(
			'success' => false,
			'message' => ''
		);
		
		if(empty($queueId)) {
			$result['message'] = 'Queue-ID fehlt';
			return $result;
		}
		
		// Sanitize queue ID
		if(!preg_match('/^[A-F0-9]+$/', $queueId)) {
			$result['message'] = 'Ungültige Queue-ID';
			return $result;
		}
		
		$output = array();
		$returnVar = 0;
		@exec('postsuper -d ' . escapeshellarg($queueId) . ' 2>&1', $output, $returnVar);
		
		if($returnVar === 0) {
			$result['success'] = true;
			$result['message'] = 'Nachricht gelöscht';
		} else {
			$result['message'] = 'Fehler beim Löschen: ' . implode(' ', $output);
		}
		
		return $result;
	}
}

?>
