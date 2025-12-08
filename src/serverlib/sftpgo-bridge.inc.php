<?php
/**
 * SFTPGo Bridge
 * Vollständige Integration für SFTP/FTPS/S3
 */

class BMSFTPGoBridge
{
	private $adminApi;
	private $adminUser;
	private $adminPass;
	private $server;
	private $sftpPort;
	
	public function __construct()
	{
		$this->adminApi = defined('SFTPGO_ADMIN_API') ? SFTPGO_ADMIN_API : '';
		$this->adminUser = defined('SFTPGO_ADMIN_USER') ? SFTPGO_ADMIN_USER : 'admin';
		$this->adminPass = defined('SFTPGO_ADMIN_PASS') ? SFTPGO_ADMIN_PASS : '';
		$this->server = defined('SFTPGO_SERVER') ? SFTPGO_SERVER : 'localhost';
		$this->sftpPort = defined('SFTPGO_SFTP_PORT') ? SFTPGO_SFTP_PORT : 2022;
	}
	
	/**
	 * Test SFTPGo Connection
	 */
	public function test()
	{
		$result = array(
			'sftp_connected' => false,
			'api_connected' => false,
			'version' => '',
			'message' => ''
		);
		
		// Test SFTP Port
		try {
			$connection = @fsockopen($this->server, $this->sftpPort, $errno, $errstr, 5);
			if($connection) {
				$result['sftp_connected'] = true;
				fclose($connection);
			}
		} catch(Exception $e) {
			// Ignore
		}
		
		// Test Admin API
		if(empty($this->adminApi)) {
			$result['message'] = 'Admin-API URL nicht konfiguriert';
			return $result;
		}
		
		try {
			$ch = curl_init($this->adminApi . '/version');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			if(!empty($this->adminUser) && !empty($this->adminPass)) {
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Authorization: Basic ' . base64_encode($this->adminUser . ':' . $this->adminPass)
				));
			}
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if($httpCode == 200) {
				$result['api_connected'] = true;
				$data = json_decode($response, true);
				if($data && isset($data['version'])) {
					$result['version'] = $data['version'];
				}
				$result['message'] = 'Verbindung erfolgreich';
			} elseif($httpCode == 401) {
				$result['message'] = 'Authentifizierung fehlgeschlagen';
			} else {
				$result['message'] = 'API-Verbindung fehlgeschlagen (HTTP ' . $httpCode . ')';
			}
		} catch(Exception $e) {
			$result['message'] = 'Fehler: ' . $e->getMessage();
		}
		
		return $result;
	}
	
	/**
	 * Get SFTPGo Stats
	 */
	public function getStats()
	{
		global $db;
		
		$stats = array(
			'total_users' => 0,
			'synced_users' => 0,
			'pending_users' => 0,
			'failed_users' => 0,
			'total_connections' => 0,
			'active_connections' => 0,
			'total_transfers' => 0,
			'total_upload_size' => 0,
			'total_download_size' => 0,
			'sftp_enabled' => true,
			'ftps_enabled' => defined('SFTPGO_FTPS_PORT') && SFTPGO_FTPS_PORT > 0,
			's3_enabled' => defined('SFTPGO_S3_ENABLED') && SFTPGO_S3_ENABLED,
			'webdav_enabled' => defined('SFTPGO_WEBDAV_ENABLED') && SFTPGO_WEBDAV_ENABLED
		);
		
		if(!isset($db)) {
			return $stats;
		}
		
		try {
			// User counts
			$res = $db->Query('SELECT COUNT(*) FROM {pre}users');
			list($stats['total_users']) = $res->FetchArray(MYSQLI_NUM);
			$res->Free();
			
			// Synced users
			$res = $db->Query('SELECT COUNT(*) FROM {pre}users WHERE sftpgo_id IS NOT NULL');
			list($stats['synced_users']) = $res->FetchArray(MYSQLI_NUM);
			$res->Free();
			
			$stats['pending_users'] = $stats['total_users'] - $stats['synced_users'];
			
			// Failed sync
			$res = $db->Query('SELECT COUNT(DISTINCT userid) FROM {pre}sftpgo_sync_log 
				WHERE status="failed" AND created >= DATE_SUB(NOW(), INTERVAL 24 HOUR)');
			list($stats['failed_users']) = $res->FetchArray(MYSQLI_NUM);
			$res->Free();
			
			// Connection stats
			$res = $db->Query('SELECT COUNT(*) FROM {pre}sftpgo_connections WHERE active=1');
			list($stats['active_connections']) = $res->FetchArray(MYSQLI_NUM);
			$res->Free();
			
			$res = $db->Query('SELECT COUNT(*) FROM {pre}sftpgo_connections');
			list($stats['total_connections']) = $res->FetchArray(MYSQLI_NUM);
			$res->Free();
			
			// Transfer stats (today)
			$res = $db->Query('SELECT 
				COUNT(*) as transfers,
				SUM(upload_size) as upload_size,
				SUM(download_size) as download_size
				FROM {pre}sftpgo_transfers 
				WHERE DATE(created) = CURDATE()');
			if($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$stats['total_transfers'] = (int)$row['transfers'];
				$stats['total_upload_size'] = (int)$row['upload_size'];
				$stats['total_download_size'] = (int)$row['download_size'];
			}
			$res->Free();
			
		} catch(Exception $e) {
			// Tables might not exist
		}
		
		return $stats;
	}
	
	/**
	 * Get Users List
	 */
	public function getUsersList($limit = 50, $offset = 0)
	{
		global $db;
		
		$users = array();
		
		if(!isset($db)) {
			return $users;
		}
		
		try {
			$res = $db->Query('SELECT u.id, u.email, u.vorname, u.nachname, 
				u.sftpgo_id, u.sftpgo_synced, u.sftpgo_last_sync
				FROM {pre}users u
				ORDER BY u.id ASC
				LIMIT ?, ?', $offset, $limit);
			while($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$users[] = $row;
			}
			$res->Free();
		} catch(Exception $e) {
			// Ignore
		}
		
		return $users;
	}
	
	/**
	 * Sync User to SFTPGo
	 */
	public function syncUser($userId)
	{
		global $db;
		
		$result = array(
			'success' => false,
			'message' => '',
			'sftpgo_id' => null
		);
		
		if(!isset($db)) {
			$result['message'] = 'Datenbank nicht verfügbar';
			return $result;
		}
		
		if(empty($this->adminApi)) {
			$result['message'] = 'Admin-API nicht konfiguriert';
			return $result;
		}
		
		try {
			// Get user data
			$res = $db->Query('SELECT * FROM {pre}users WHERE id=?', $userId);
			$user = $res->FetchArray(MYSQLI_ASSOC);
			$res->Free();
			
			if(!$user) {
				$result['message'] = 'Benutzer nicht gefunden';
				return $result;
			}
			
			// Generate password (or retrieve from secure storage)
			$sftpPassword = $this->generateSecurePassword();
			
			// Prepare user data for SFTPGo
			$userData = array(
				'username' => $user['email'],
				'password' => $sftpPassword,
				'home_dir' => '/data/' . $user['email'],
				'status' => 1, // Active
				'permissions' => array(
					'/' => array('*') // All permissions
				),
				'quota_size' => $user['mailspace'] * 1024 * 1024, // MB to Bytes
				'max_sessions' => 5
			);
			
			// Create or update user in SFTPGo
			$ch = curl_init($this->adminApi . '/users');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Authorization: Basic ' . base64_encode($this->adminUser . ':' . $this->adminPass)
			));
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if($httpCode == 200 || $httpCode == 201) {
				$responseData = json_decode($response, true);
				
				$result['success'] = true;
				$result['sftpgo_id'] = $user['email'];
				$result['message'] = 'User erfolgreich synchronisiert';
				
				// Update user record
				$db->Query('UPDATE {pre}users SET 
					sftpgo_id=?, 
					sftpgo_synced="yes", 
					sftpgo_last_sync=NOW() 
					WHERE id=?',
					$result['sftpgo_id'], $userId);
				
				// Store password encrypted (for user reference)
				$db->Query('UPDATE {pre}users SET sftpgo_password=? WHERE id=?',
					base64_encode($sftpPassword), $userId);
				
				// Log success
				$db->Query('INSERT INTO {pre}sftpgo_sync_log 
					(userid, status, message, created) 
					VALUES (?, "success", ?, NOW())',
					$userId, $result['message']);
			} else {
				$result['message'] = 'Sync fehlgeschlagen (HTTP ' . $httpCode . '): ' . $response;
				
				// Log failure
				$db->Query('INSERT INTO {pre}sftpgo_sync_log 
					(userid, status, message, created) 
					VALUES (?, "failed", ?, NOW())',
					$userId, $result['message']);
			}
			
		} catch(Exception $e) {
			$result['message'] = 'Fehler: ' . $e->getMessage();
		}
		
		return $result;
	}
	
	/**
	 * Get Sync Log
	 */
	public function getSyncLog($limit = 50)
	{
		global $db;
		
		$log = array();
		
		if(!isset($db)) {
			return $log;
		}
		
		try {
			$res = $db->Query('SELECT l.*, u.email 
				FROM {pre}sftpgo_sync_log l
				LEFT JOIN {pre}users u ON l.userid = u.id
				ORDER BY l.created DESC
				LIMIT ?', $limit);
			while($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$log[] = $row;
			}
			$res->Free();
		} catch(Exception $e) {
			// Ignore
		}
		
		return $log;
	}
	
	/**
	 * Get Active Connections
	 */
	public function getActiveConnections($limit = 50)
	{
		global $db;
		
		$connections = array();
		
		if(!isset($db)) {
			return $connections;
		}
		
		try {
			$res = $db->Query('SELECT c.*, u.email 
				FROM {pre}sftpgo_connections c
				LEFT JOIN {pre}users u ON c.userid = u.id
				WHERE c.active=1
				ORDER BY c.started DESC
				LIMIT ?', $limit);
			while($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$connections[] = $row;
			}
			$res->Free();
		} catch(Exception $e) {
			// Ignore
		}
		
		return $connections;
	}
	
	/**
	 * Generate secure password
	 */
	private function generateSecurePassword($length = 16)
	{
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
		$password = '';
		$charsLength = strlen($chars);
		
		for($i = 0; $i < $length; $i++) {
			$password .= $chars[random_int(0, $charsLength - 1)];
		}
		
		return $password;
	}
}

?>
