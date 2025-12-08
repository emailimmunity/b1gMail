<?php
/**
 * Postfix Bridge
 * Verbindet b1gMail mit Postfix SMTP Server
 * 
 * Datum: 31. Oktober 2025
 */

class BMPostfixBridge
{
	private $config;
	
	public function __construct()
	{
		$this->config = array(
			'host' => getenv('POSTFIX_HOST') ?: 'localhost',
			'smtp_port' => getenv('POSTFIX_SMTP_PORT') ?: 25,
			'submission_port' => getenv('POSTFIX_SUBMISSION_PORT') ?: 587,
			'smtps_port' => getenv('POSTFIX_SMTPS_PORT') ?: 465,
			'queue_dir' => getenv('POSTFIX_QUEUE_DIR') ?: '/var/spool/postfix',
			'admin_email' => getenv('POSTFIX_ADMIN_EMAIL') ?: 'postmaster@localhost'
		);
	}
	
	/**
	 * Test connection to Postfix
	 */
	public function testConnection()
	{
		$result = array(
			'connected' => false,
			'smtp_available' => false,
			'submission_available' => false,
			'smtps_available' => false,
			'queue_accessible' => false,
			'version' => 'Unknown',
			'error' => null
		);
		
		try {
			// Test SMTP port
			$socket = @fsockopen($this->config['host'], $this->config['smtp_port'], $errno, $errstr, 3);
			if($socket) {
				$result['smtp_available'] = true;
				$result['connected'] = true;
				
				// Read banner
				$banner = fgets($socket);
				if(preg_match('/220.*Postfix/', $banner)) {
					if(preg_match('/ESMTP Postfix\s*\((.*?)\)/', $banner, $matches)) {
						$result['version'] = 'Postfix ' . $matches[1];
					}
					else {
						$result['version'] = 'Postfix';
					}
				}
				fclose($socket);
			}
			
			// Test Submission port
			$socket = @fsockopen($this->config['host'], $this->config['submission_port'], $errno, $errstr, 3);
			if($socket) {
				$result['submission_available'] = true;
				fclose($socket);
			}
			
			// Test SMTPS port
			$socket = @fsockopen($this->config['host'], $this->config['smtps_port'], $errno, $errstr, 3);
			if($socket) {
				$result['smtps_available'] = true;
				fclose($socket);
			}
			
			// Check queue directory
			if(file_exists($this->config['queue_dir']) && is_readable($this->config['queue_dir'])) {
				$result['queue_accessible'] = true;
			}
		}
		catch(Exception $e) {
			$result['error'] = $e->getMessage();
		}
		
		return $result;
	}
	
	/**
	 * Get current statistics
	 */
	public function getStats()
	{
		global $db;
		
		$stats = array(
			'emails_sent_today' => 0,
			'emails_received_today' => 0,
			'emails_bounced_today' => 0,
			'emails_deferred_today' => 0,
			'emails_rejected_today' => 0,
			'emails_spam_today' => 0,
			'queue_size' => 0,
			'queue_age_avg_min' => 0,
			'delivery_time_avg_sec' => 0,
			'connections_total_today' => 0,
			'connections_rejected_today' => 0,
			'traffic_in_mb_today' => 0,
			'traffic_out_mb_today' => 0
		);
		
		// Get today's stats from database
		$res = $db->Query('SELECT * FROM {pre}postfix_stats WHERE date = CURDATE()');
		if($res && $res->RowCount() > 0) {
			$row = $res->FetchArray(MYSQLI_ASSOC);
			$stats = array_merge($stats, $row);
			$res->Free();
		}
		
		// Get real-time queue size
		$stats['queue_size'] = $this->getQueueSize();
		
		return $stats;
	}
	
	/**
	 * Get queue status
	 */
	public function getQueueStatus()
	{
		$status = array(
			'active' => 0,
			'deferred' => 0,
			'hold' => 0,
			'maildrop' => 0,
			'total' => 0
		);
		
		// Parse mailq output
		exec('mailq 2>&1', $output, $returnCode);
		
		if($returnCode == 0 && !empty($output)) {
			foreach($output as $line) {
				if(preg_match('/Mail queue is empty/', $line)) {
					return $status;
				}
				if(preg_match('/-- (\d+) Kbytes in (\d+) Request/', $line, $matches)) {
					$status['total'] = (int)$matches[2];
				}
			}
		}
		
		// Parse postqueue -p output for detailed info
		exec('postqueue -p 2>&1', $output, $returnCode);
		
		if($returnCode == 0 && !empty($output)) {
			foreach($output as $line) {
				if(preg_match('/^\s*\(/', $line)) {
					if(strpos($line, 'deferred') !== false) {
						$status['deferred']++;
					}
					else if(strpos($line, 'active') !== false) {
						$status['active']++;
					}
					else if(strpos($line, 'hold') !== false) {
						$status['hold']++;
					}
				}
			}
		}
		
		return $status;
	}
	
	/**
	 * Get queue size
	 */
	private function getQueueSize()
	{
		exec('mailq 2>&1 | tail -n 1', $output);
		
		if(!empty($output) && isset($output[0])) {
			if(preg_match('/(\d+) Request/', $output[0], $matches)) {
				return (int)$matches[1];
			}
		}
		
		return 0;
	}
	
	/**
	 * Get queue messages
	 */
	public function getQueueMessages($limit = 50)
	{
		$messages = array();
		
		exec('postqueue -p 2>&1', $output, $returnCode);
		
		if($returnCode != 0 || empty($output)) {
			return $messages;
		}
		
		$currentMessage = null;
		$count = 0;
		
		foreach($output as $line) {
			if($count >= $limit) break;
			
			// Queue ID line
			if(preg_match('/^([A-F0-9]+)\s+(\d+)\s+(.+)$/', $line, $matches)) {
				if($currentMessage) {
					$messages[] = $currentMessage;
					$count++;
				}
				
				$currentMessage = array(
					'queue_id' => $matches[1],
					'size' => (int)$matches[2],
					'time' => trim($matches[3]),
					'from' => '',
					'to' => array(),
					'status' => 'queued'
				);
			}
			// From line
			else if($currentMessage && preg_match('/^\s+(.+@.+)$/', $line, $matches)) {
				if(empty($currentMessage['from'])) {
					$currentMessage['from'] = trim($matches[1]);
				}
				else {
					$currentMessage['to'][] = trim($matches[1]);
				}
			}
			// Status line
			else if($currentMessage && preg_match('/^\s*\((.+)\)$/', $line, $matches)) {
				$currentMessage['status'] = trim($matches[1]);
			}
		}
		
		if($currentMessage) {
			$messages[] = $currentMessage;
		}
		
		return $messages;
	}
	
	/**
	 * Delete queue message
	 */
	public function deleteQueueMessage($queueId)
	{
		$result = array(
			'success' => false,
			'error' => null
		);
		
		// Sanitize queue ID
		if(!preg_match('/^[A-F0-9]+$/', $queueId)) {
			$result['error'] = 'Invalid queue ID';
			return $result;
		}
		
		exec('postsuper -d ' . escapeshellarg($queueId) . ' 2>&1', $output, $returnCode);
		
		if($returnCode == 0) {
			$result['success'] = true;
		}
		else {
			$result['error'] = implode("\n", $output);
		}
		
		return $result;
	}
	
	/**
	 * Flush queue
	 */
	public function flushQueue()
	{
		exec('postqueue -f 2>&1', $output, $returnCode);
		return $returnCode == 0;
	}
	
	/**
	 * Update statistics
	 */
	public function updateStats()
	{
		global $db;
		
		$stats = $this->getStats();
		
		// Parse mail.log for today's statistics
		// This is a simplified version - you'd need to implement proper log parsing
		
		$db->Query('INSERT INTO {pre}postfix_stats 
			(date, emails_sent, emails_received, emails_bounced, emails_deferred,
			 emails_rejected, emails_spam, queue_size, queue_age_avg_min,
			 delivery_time_avg_sec, connections_total, connections_rejected,
			 traffic_in_mb, traffic_out_mb)
			VALUES (CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE
			emails_sent = VALUES(emails_sent),
			emails_received = VALUES(emails_received),
			emails_bounced = VALUES(emails_bounced),
			emails_deferred = VALUES(emails_deferred),
			emails_rejected = VALUES(emails_rejected),
			emails_spam = VALUES(emails_spam),
			queue_size = VALUES(queue_size),
			queue_age_avg_min = VALUES(queue_age_avg_min),
			delivery_time_avg_sec = VALUES(delivery_time_avg_sec),
			connections_total = VALUES(connections_total),
			connections_rejected = VALUES(connections_rejected),
			traffic_in_mb = VALUES(traffic_in_mb),
			traffic_out_mb = VALUES(traffic_out_mb)',
			$stats['emails_sent_today'],
			$stats['emails_received_today'],
			$stats['emails_bounced_today'],
			$stats['emails_deferred_today'],
			$stats['emails_rejected_today'],
			$stats['emails_spam_today'],
			$stats['queue_size'],
			$stats['queue_age_avg_min'],
			$stats['delivery_time_avg_sec'],
			$stats['connections_total_today'],
			$stats['connections_rejected_today'],
			$stats['traffic_in_mb_today'],
			$stats['traffic_out_mb_today']
		);
		
		return true;
	}
}

?>
