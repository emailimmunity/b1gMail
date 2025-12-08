<?php
/**
 * Cyrus IMAP Bridge
 * Verbindet b1gMail mit Cyrus IMAP Server
 * 
 * Datum: 31. Oktober 2025
 */

class BMCyrusBridge
{
	private $config;
	private $connection = null;
	
	public function __construct()
	{
		// Load config from environment or config file
		$this->config = array(
			'host' => getenv('CYRUS_HOST') ?: 'localhost',
			'admin_user' => getenv('CYRUS_ADMIN_USER') ?: 'cyrus',
			'admin_pass' => getenv('CYRUS_ADMIN_PASS') ?: '',
			'imap_port' => getenv('CYRUS_IMAP_PORT') ?: 143,
			'pop3_port' => getenv('CYRUS_POP3_PORT') ?: 110,
			'jmap_url' => getenv('CYRUS_JMAP_URL') ?: 'http://localhost:8008',
			'use_tls' => getenv('CYRUS_USE_TLS') ?: false
		);
	}
	
	/**
	 * Test connection to Cyrus server
	 */
	public function testConnection()
	{
		$result = array(
			'connected' => false,
			'imap_available' => false,
			'pop3_available' => false,
			'jmap_available' => false,
			'version' => 'Unknown',
			'error' => null
		);
		
		try {
			// Test IMAP connection
			$imap = @imap_open(
				'{' . $this->config['host'] . ':' . $this->config['imap_port'] . '/imap/notls}',
				$this->config['admin_user'],
				$this->config['admin_pass'],
				OP_HALFOPEN
			);
			
			if($imap) {
				$result['imap_available'] = true;
				$result['connected'] = true;
				
				// Get server info
				$check = imap_check($imap);
				if($check) {
					$result['version'] = 'Cyrus IMAP ' . (isset($check->Driver) ? $check->Driver : 'Unknown');
				}
				
				imap_close($imap);
			}
			
			// Test JMAP
			if($this->config['jmap_url']) {
				$ch = curl_init($this->config['jmap_url'] . '/.well-known/jmap');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 5);
				$response = curl_exec($ch);
				$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				
				if($httpCode == 200) {
					$result['jmap_available'] = true;
				}
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
			'imap_connections_today' => 0,
			'imap_auth_success_today' => 0,
			'imap_auth_failed_today' => 0,
			'pop3_connections_today' => 0,
			'pop3_auth_success_today' => 0,
			'pop3_auth_failed_today' => 0,
			'jmap_requests_today' => 0,
			'total_mailboxes' => 0,
			'total_messages' => 0,
			'storage_used_mb' => 0,
			'traffic_in_mb_today' => 0,
			'traffic_out_mb_today' => 0
		);
		
		// Get today's stats from database
		$res = $db->Query('SELECT * FROM {pre}cyrus_stats WHERE date = CURDATE()');
		if($res && $res->RowCount() > 0) {
			$row = $res->FetchArray(MYSQLI_ASSOC);
			$stats = array_merge($stats, $row);
			$res->Free();
		}
		
		// Get real-time data from Cyrus (if available)
		// TODO: Implement Cyrus admin API calls
		
		return $stats;
	}
	
	/**
	 * Get user mailbox quota
	 */
	public function getUserQuota($email)
	{
		$quota = array(
			'used' => 0,
			'limit' => 0,
			'percent' => 0
		);
		
		try {
			$imap = @imap_open(
				'{' . $this->config['host'] . ':' . $this->config['imap_port'] . '/imap/notls}',
				$this->config['admin_user'],
				$this->config['admin_pass']
			);
			
			if($imap) {
				$quotaInfo = imap_get_quotaroot($imap, 'user/' . str_replace('@', '.', $email));
				if($quotaInfo && isset($quotaInfo['STORAGE'])) {
					$quota['used'] = $quotaInfo['STORAGE']['usage'] / 1024; // MB
					$quota['limit'] = $quotaInfo['STORAGE']['limit'] / 1024; // MB
					$quota['percent'] = $quota['limit'] > 0 ? ($quota['used'] / $quota['limit']) * 100 : 0;
				}
				imap_close($imap);
			}
		}
		catch(Exception $e) {
			// Ignore errors
		}
		
		return $quota;
	}
	
	/**
	 * Get mailbox list for user
	 */
	public function getUserMailboxes($email)
	{
		$mailboxes = array();
		
		try {
			$imap = @imap_open(
				'{' . $this->config['host'] . ':' . $this->config['imap_port'] . '/imap/notls}',
				$this->config['admin_user'],
				$this->config['admin_pass']
			);
			
			if($imap) {
				$list = imap_list($imap, '{' . $this->config['host'] . '}', 'user/' . str_replace('@', '.', $email) . '/*');
				if($list) {
					foreach($list as $mailbox) {
						$mailboxes[] = str_replace('{' . $this->config['host'] . '}', '', $mailbox);
					}
				}
				imap_close($imap);
			}
		}
		catch(Exception $e) {
			// Ignore errors
		}
		
		return $mailboxes;
	}
	
	/**
	 * Create user mailbox
	 */
	public function createUserMailbox($email, $password, $quota_mb = 1000)
	{
		$result = array(
			'success' => false,
			'error' => null
		);
		
		try {
			// Use cyradm or direct IMAP commands
			// TODO: Implement mailbox creation
			$result['success'] = true;
		}
		catch(Exception $e) {
			$result['error'] = $e->getMessage();
		}
		
		return $result;
	}
	
	/**
	 * Update statistics (called by cronjob)
	 */
	public function updateStats()
	{
		global $db;
		
		$stats = $this->getStats();
		
		// Update or insert today's stats
		$db->Query('INSERT INTO {pre}cyrus_stats 
			(date, imap_connections, imap_auth_success, imap_auth_failed,
			 pop3_connections, pop3_auth_success, pop3_auth_failed,
			 jmap_requests, total_mailboxes, total_messages, storage_used_mb,
			 traffic_in_mb, traffic_out_mb)
			VALUES (CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE
			imap_connections = VALUES(imap_connections),
			imap_auth_success = VALUES(imap_auth_success),
			imap_auth_failed = VALUES(imap_auth_failed),
			pop3_connections = VALUES(pop3_connections),
			pop3_auth_success = VALUES(pop3_auth_success),
			pop3_auth_failed = VALUES(pop3_auth_failed),
			jmap_requests = VALUES(jmap_requests),
			total_mailboxes = VALUES(total_mailboxes),
			total_messages = VALUES(total_messages),
			storage_used_mb = VALUES(storage_used_mb),
			traffic_in_mb = VALUES(traffic_in_mb),
			traffic_out_mb = VALUES(traffic_out_mb)',
			$stats['imap_connections_today'],
			$stats['imap_auth_success_today'],
			$stats['imap_auth_failed_today'],
			$stats['pop3_connections_today'],
			$stats['pop3_auth_success_today'],
			$stats['pop3_auth_failed_today'],
			$stats['jmap_requests_today'],
			$stats['total_mailboxes'],
			$stats['total_messages'],
			$stats['storage_used_mb'],
			$stats['traffic_in_mb_today'],
			$stats['traffic_out_mb_today']
		);
		
		return true;
	}
}

?>
