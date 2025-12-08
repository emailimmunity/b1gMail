<?php
/**
 * SFTPGo Bridge
 * Verbindet b1gMail mit SFTPGo Server (SFTP/FTPS/S3/WebDAV)
 * 
 * Datum: 31. Oktober 2025
 */

class BMSFTPGoBridge
{
	private $config;
	
	public function __construct()
	{
		$this->config = array(
			'api_url' => getenv('SFTPGO_API_URL') ?: 'http://localhost:8080',
			'api_key' => getenv('SFTPGO_API_KEY') ?: '',
			'sftp_host' => getenv('SFTPGO_SFTP_HOST') ?: 'localhost',
			'sftp_port' => getenv('SFTPGO_SFTP_PORT') ?: 2022,
			'ftps_port' => getenv('SFTPGO_FTPS_PORT') ?: 2021,
			'webdav_url' => getenv('SFTPGO_WEBDAV_URL') ?: 'http://localhost:8090',
			's3_endpoint' => getenv('SFTPGO_S3_ENDPOINT') ?: 'http://localhost:9000'
		);
	}
	
	/**
	 * Test connection to SFTPGo
	 */
	public function testConnection()
	{
		$result = array(
			'api_connected' => false,
			'sftp_connected' => false,
			'ftps_connected' => false,
			'webdav_available' => false,
			's3_available' => false,
			'version' => 'Unknown',
			'error' => null
		);
		
		try {
			// Test API connection
			$ch = curl_init($this->config['api_url'] . '/api/v2/version');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'X-SFTPGO-API-KEY: ' . $this->config['api_key']
			));
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if($httpCode == 200 && $response) {
				$result['api_connected'] = true;
				$data = json_decode($response, true);
				if($data && isset($data['version'])) {
					$result['version'] = 'SFTPGo ' . $data['version'];
				}
			}
			
			// Test SFTP port
			$socket = @fsockopen($this->config['sftp_host'], $this->config['sftp_port'], $errno, $errstr, 3);
			if($socket) {
				$result['sftp_connected'] = true;
				fclose($socket);
			}
			
			// Test WebDAV
			$ch = curl_init($this->config['webdav_url']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 3);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if($httpCode > 0 && $httpCode < 500) {
				$result['webdav_available'] = true;
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
			'sftp_connections_today' => 0,
			'sftp_auth_failed_today' => 0,
			'ftps_connections_today' => 0,
			'ftps_auth_failed_today' => 0,
			's3_operations_today' => 0,
			's3_errors_today' => 0,
			'webdav_requests_today' => 0,
			'webdav_errors_today' => 0,
			'uploads_count_today' => 0,
			'downloads_count_today' => 0,
			'uploads_mb_today' => 0,
			'downloads_mb_today' => 0,
			'storage_used_mb' => 0,
			'active_users' => 0,
			'quota_exceeded_today' => 0
		);
		
		// Get today's stats from database
		$res = $db->Query('SELECT * FROM {pre}sftpgo_stats WHERE date = CURDATE()');
		if($res && $res->RowCount() > 0) {
			$row = $res->FetchArray(MYSQLI_ASSOC);
			$stats = array_merge($stats, $row);
			$res->Free();
		}
		
		// Get real-time data from SFTPGo API
		try {
			$ch = curl_init($this->config['api_url'] . '/api/v2/users');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'X-SFTPGO-API-KEY: ' . $this->config['api_key']
			));
			
			$response = curl_exec($ch);
			curl_close($ch);
			
			if($response) {
				$data = json_decode($response, true);
				if($data && is_array($data)) {
					$stats['active_users'] = count($data);
					
					// Calculate total storage
					$totalStorage = 0;
					foreach($data as $user) {
						if(isset($user['used_quota_size'])) {
							$totalStorage += $user['used_quota_size'];
						}
					}
					$stats['storage_used_mb'] = round($totalStorage / 1024 / 1024, 2);
				}
			}
		}
		catch(Exception $e) {
			// Ignore errors
		}
		
		return $stats;
	}
	
	/**
	 * Sync user from b1gMail to SFTPGo
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
			// Check if user exists
			$ch = curl_init($this->config['api_url'] . '/api/v2/users/' . $user['email']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'X-SFTPGO-API-KEY: ' . $this->config['api_key']
			));
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			$userData = array(
				'username' => $user['email'],
				'email' => $user['email'],
				'status' => 1,
				'permissions' => array(
					'/' => array('*')
				),
				'quota_size' => 1024 * 1024 * 1024, // 1GB default
				'home_dir' => '/data/users/' . $user['email']
			);
			
			if($httpCode == 404) {
				// Create new user
				$ch = curl_init($this->config['api_url'] . '/api/v2/users');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'X-SFTPGO-API-KEY: ' . $this->config['api_key']
				));
				
				$response = curl_exec($ch);
				$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				
				if($httpCode == 201) {
					$result['success'] = true;
					$result['created'] = true;
				}
			}
			else if($httpCode == 200) {
				// Update existing user
				$ch = curl_init($this->config['api_url'] . '/api/v2/users/' . $user['email']);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'X-SFTPGO-API-KEY: ' . $this->config['api_key']
				));
				
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
		
		return $result;
	}
	
	/**
	 * Get active connections
	 */
	public function getActiveConnections($limit = 20)
	{
		$connections = array();
		
		try {
			$ch = curl_init($this->config['api_url'] . '/api/v2/connections');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'X-SFTPGO-API-KEY: ' . $this->config['api_key']
			));
			
			$response = curl_exec($ch);
			curl_close($ch);
			
			if($response) {
				$data = json_decode($response, true);
				if($data && is_array($data)) {
					$connections = array_slice($data, 0, $limit);
				}
			}
		}
		catch(Exception $e) {
			// Ignore errors
		}
		
		return $connections;
	}
	
	/**
	 * Update statistics
	 */
	public function updateStats()
	{
		global $db;
		
		$stats = $this->getStats();
		
		$db->Query('INSERT INTO {pre}sftpgo_stats 
			(date, sftp_connections, sftp_auth_failed, ftps_connections, ftps_auth_failed,
			 s3_operations, s3_errors, webdav_requests, webdav_errors,
			 uploads_count, downloads_count, uploads_mb, downloads_mb,
			 storage_used_mb, active_users, quota_exceeded)
			VALUES (CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE
			sftp_connections = VALUES(sftp_connections),
			sftp_auth_failed = VALUES(sftp_auth_failed),
			ftps_connections = VALUES(ftps_connections),
			ftps_auth_failed = VALUES(ftps_auth_failed),
			s3_operations = VALUES(s3_operations),
			s3_errors = VALUES(s3_errors),
			webdav_requests = VALUES(webdav_requests),
			webdav_errors = VALUES(webdav_errors),
			uploads_count = VALUES(uploads_count),
			downloads_count = VALUES(downloads_count),
			uploads_mb = VALUES(uploads_mb),
			downloads_mb = VALUES(downloads_mb),
			storage_used_mb = VALUES(storage_used_mb),
			active_users = VALUES(active_users),
			quota_exceeded = VALUES(quota_exceeded)',
			$stats['sftp_connections_today'],
			$stats['sftp_auth_failed_today'],
			$stats['ftps_connections_today'],
			$stats['ftps_auth_failed_today'],
			$stats['s3_operations_today'],
			$stats['s3_errors_today'],
			$stats['webdav_requests_today'],
			$stats['webdav_errors_today'],
			$stats['uploads_count_today'],
			$stats['downloads_count_today'],
			$stats['uploads_mb_today'],
			$stats['downloads_mb_today'],
			$stats['storage_used_mb'],
			$stats['active_users'],
			$stats['quota_exceeded_today']
		);
		
		return true;
	}
}

?>
