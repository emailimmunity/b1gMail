<?php
/**
 * Grommunio Bridge
 * Verbindet b1gMail mit Grommunio Server (MAPI/EWS/EAS)
 * 
 * Datum: 31. Oktober 2025
 */

class BMGrommunioBridge
{
	private $config;
	
	public function __construct()
	{
		// Load config from environment or  config file
		$this->config = array(
			'api_url' => getenv('GROMMUNIO_API_URL') ?: 'https://192.168.178.144:8443/api/v1',
			'api_user' => getenv('GROMMUNIO_API_USER') ?: 'admin',
			'api_pass' => getenv('GROMMUNIO_API_PASS') ?: '1234',
			'mapi_url' => getenv('GROMMUNIO_MAPI_URL') ?: 'https://192.168.178.144',
			'ews_url' => getenv('GROMMUNIO_EWS_URL') ?: 'https://192.168.178.144/EWS/Exchange.asmx',
			'eas_url' => getenv('GROMMUNIO_EAS_URL') ?: 'https://192.168.178.144/Microsoft-Server-ActiveSync',
			'verify_ssl' => getenv('GROMMUNIO_VERIFY_SSL') ?: false
		);
	}
	
	/**
	 * Test connection to Grommunio
	 */
	public function testConnection()
	{
		$result = array(
			'connected' => false,
			'api_available' => false,
			'mapi_available' => false,
			'ews_available' => false,
			'eas_available' => false,
			'version' => 'Unknown',
			'error' => null
		);
		
		try {
			// Test API connection
			$ch = curl_init($this->config['api_url'] . '/system/dashboard');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERPWD, $this->config['api_user'] . ':' . $this->config['api_pass']);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->config['verify_ssl']);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if($httpCode == 200) {
				$result['connected'] = true;
				$result['api_available'] = true;
				
				$data = json_decode($response, true);
				if($data && isset($data['data']['version'])) {
					$result['version'] = 'Grommunio ' . $data['data']['version'];
				}
			}
			
			// Test MAPI (basic HTTP check)
			$ch = curl_init($this->config['mapi_url']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if($httpCode > 0 && $httpCode < 500) {
				$result['mapi_available'] = true;
				$result['ews_available'] = true;
				$result['eas_available'] = true;
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
			'mapi_connections_today' => 0,
			'mapi_requests_today' => 0,
			'ews_requests_today' => 0,
			'ews_errors_today' => 0,
			'eas_sync_requests_today' => 0,
			'eas_devices' => 0,
			'active_users' => 0,
			'total_domains' => 0,
			'api_calls_today' => 0,
			'sync_operations_today' => 0,
			'sync_errors_today' => 0,
			'storage_used_mb' => 0
		);
		
		// Get today's stats from database
		$res = $db->Query('SELECT * FROM {pre}grommunio_stats WHERE date = CURDATE()');
		if($res && $res->RowCount() > 0) {
			$row = $res->FetchArray(MYSQLI_ASSOC);
			$stats = array_merge($stats, $row);
			$res->Free();
		}
		
		// Get real-time data from Grommunio API
		try {
			$ch = curl_init($this->config['api_url'] . '/system/dashboard');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERPWD, $this->config['api_user'] . ':' . $this->config['api_pass']);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			
			$response = curl_exec($ch);
			curl_close($ch);
			
			if($response) {
				$data = json_decode($response, true);
				if($data && isset($data['data'])) {
					// Extract stats from API response
					if(isset($data['data']['users'])) $stats['active_users'] = $data['data']['users'];
					if(isset($data['data']['domains'])) $stats['total_domains'] = $data['data']['domains'];
				}
			}
		}
		catch(Exception $e) {
			// Ignore errors
		}
		
		return $stats;
	}
	
	/**
	 * Sync user from b1gMail to Grommunio
	 */
	public function syncUser($userId)
	{
		global $db;
		
		$result = array(
			'success' => false,
			'created' => false,
			'updated' => false,
			'error' => null
		);
		
		// Get user data
		$res = $db->Query('SELECT * FROM {pre}users WHERE id = ?', $userId);
		if($res->RowCount() == 0) {
			$result['error'] = 'User not found';
			return $result;
		}
		$user = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		try {
			// Check if user exists in Grommunio
			$ch = curl_init($this->config['api_url'] . '/users/' . $user['email']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERPWD, $this->config['api_user'] . ':' . $this->config['api_pass']);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			$userData = array(
				'username' => $user['email'],
				'properties' => array(
					'displayname' => $user['vorname'] . ' ' . $user['nachname'],
					'storagequotalimit' => 1024 * 1024 * 1024 // 1GB default
				)
			);
			
			if($httpCode == 404) {
				// Create new user
				$ch = curl_init($this->config['api_url'] . '/users');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
				curl_setopt($ch, CURLOPT_USERPWD, $this->config['api_user'] . ':' . $this->config['api_pass']);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				
				$response = curl_exec($ch);
				$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				
				if($httpCode == 200 || $httpCode == 201) {
					$result['success'] = true;
					$result['created'] = true;
				}
			}
			else if($httpCode == 200) {
				// Update existing user
				$ch = curl_init($this->config['api_url'] . '/users/' . $user['email']);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
				curl_setopt($ch, CURLOPT_USERPWD, $this->config['api_user'] . ':' . $this->config['api_pass']);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				
				$response = curl_exec($ch);
				$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				
				if($httpCode == 200) {
					$result['success'] = true;
					$result['updated'] = true;
				}
			}
		}
		catch(Exception $e) {
			$result['error'] = $e->getMessage();
		}
		
		// Log sync operation
		$db->Query('INSERT INTO {pre}grommunio_stats 
			(date, sync_operations, sync_errors) 
			VALUES (CURDATE(), 1, ?)
			ON DUPLICATE KEY UPDATE 
			sync_operations = sync_operations + 1,
			sync_errors = sync_errors + ?',
			$result['success'] ? 0 : 1,
			$result['success'] ? 0 : 1
		);
		
		return $result;
	}
	
	/**
	 * Get ActiveSync devices
	 */
	public function getActiveDevices($limit = 20)
	{
		$devices = array();
		
		try {
			$ch = curl_init($this->config['api_url'] . '/devices?limit=' . $limit);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERPWD, $this->config['api_user'] . ':' . $this->config['api_pass']);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			$response = curl_exec($ch);
			curl_close($ch);
			
			if($response) {
				$data = json_decode($response, true);
				if($data && isset($data['data'])) {
					$devices = $data['data'];
				}
			}
		}
		catch(Exception $e) {
			// Ignore errors
		}
		
		return $devices;
	}
	
	/**
	 * Update statistics
	 */
	public function updateStats()
	{
		global $db;
		
		$stats = $this->getStats();
		
		$db->Query('INSERT INTO {pre}grommunio_stats 
			(date, mapi_connections, mapi_requests, ews_requests, ews_errors,
			 eas_sync_requests, eas_devices, active_users, total_domains,
			 api_calls, sync_operations, sync_errors, storage_used_mb)
			VALUES (CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE
			mapi_connections = VALUES(mapi_connections),
			mapi_requests = VALUES(mapi_requests),
			ews_requests = VALUES(ews_requests),
			ews_errors = VALUES(ews_errors),
			eas_sync_requests = VALUES(eas_sync_requests),
			eas_devices = VALUES(eas_devices),
			active_users = VALUES(active_users),
			total_domains = VALUES(total_domains),
			api_calls = VALUES(api_calls),
			sync_operations = VALUES(sync_operations),
			sync_errors = VALUES(sync_errors),
			storage_used_mb = VALUES(storage_used_mb)',
			$stats['mapi_connections_today'],
			$stats['mapi_requests_today'],
			$stats['ews_requests_today'],
			$stats['ews_errors_today'],
			$stats['eas_sync_requests_today'],
			$stats['eas_devices'],
			$stats['active_users'],
			$stats['total_domains'],
			$stats['api_calls_today'],
			$stats['sync_operations_today'],
			$stats['sync_errors_today'],
			$stats['storage_used_mb']
		);
		
		return true;
	}
}

?>
